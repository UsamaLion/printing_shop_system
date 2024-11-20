
<?php
require_once 'config.php';
include 'includes/header.php';

// Check if the user is logged in and is a printing press staff
if(!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Printing Press"){
    header("location: login.php");
    exit;
}

$printing_press_id = $_SESSION["user_id"];

// Fetch jobs for the printing press
$jobs_query = "SELECT j.JobID, j.Description, c.Name as ClientName, jt.JobTypeName, js.StatusName, j.CreatedDate, u.Name as DesignerName
               FROM Jobs j
               JOIN Clients c ON j.ClientID = c.ClientID
               JOIN JobTypes jt ON j.JobTypeID = jt.JobTypeID
               JOIN JobStatus js ON j.StatusID = js.StatusID
               JOIN Users u ON j.DesignerID = u.UserID
               WHERE js.StatusName IN ('Sent for Printing', 'Printing In Progress')
               ORDER BY j.CreatedDate DESC";

$jobs_result = mysqli_query($conn, $jobs_query);

// Fetch job statuses for the dropdown
$status_query = "SELECT StatusID, StatusName FROM JobStatus WHERE StatusName IN ('Printing In Progress', 'Completed')";
$status_result = mysqli_query($conn, $status_query);

// Fetch job status overview
$job_status_query = "SELECT js.StatusName, COUNT(*) as Count 
                     FROM Jobs j 
                     JOIN JobStatus js ON j.StatusID = js.StatusID 
                     WHERE js.StatusName IN ('Sent for Printing', 'Printing In Progress', 'Completed')
                     GROUP BY j.StatusID";
$job_status_result = mysqli_query($conn, $job_status_query);
?>

<h2 class="mb-4">Printing Press Dashboard</h2>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                Job Status Overview
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($row = mysqli_fetch_assoc($job_status_result)) {
                                echo "<tr>";
                                echo "<td>" . $row['StatusName'] . "</td>";
                                echo "<td>" . $row['Count'] . "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<h3 class="mb-3">Jobs for Printing</h3>
<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="thead-light">
            <tr>
                <th>Job ID</th>
                <th>Client</th>
                <th>Job Type</th>
                <th>Description</th>
                <th>Designer</th>
                <th>Current Status</th>
                <th>Created Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($jobs_result) > 0) {
                while($row = mysqli_fetch_assoc($jobs_result)) {
                    echo "<tr>";
                    echo "<td>" . $row['JobID'] . "</td>";
                    echo "<td>" . $row['ClientName'] . "</td>";
                    echo "<td>" . $row['JobTypeName'] . "</td>";
                    echo "<td>" . $row['Description'] . "</td>";
                    echo "<td>" . $row['DesignerName'] . "</td>";
                    echo "<td>" . $row['StatusName'] . "</td>";
                    echo "<td>" . $row['CreatedDate'] . "</td>";
                    echo "<td>
                            <form action='update_job_status.php' method='post' class='form-inline'>
                                <input type='hidden' name='job_id' value='" . $row['JobID'] . "'>
                                <select name='new_status' class='form-control form-control-sm mr-2'>";
                                mysqli_data_seek($status_result, 0);
                                while ($status_row = mysqli_fetch_assoc($status_result)) {
                                    $selected = ($status_row['StatusName'] == $row['StatusName']) ? 'selected' : '';
                                    echo "<option value='" . $status_row['StatusID'] . "' $selected>" . $status_row['StatusName'] . "</option>";
                                }
                    echo "      </select>
                                <button type='submit' class='btn btn-sm btn-primary'>Update</button>
                            </form>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='8'>No jobs for printing found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <h3>Quick Links</h3>
        <a href="jobs.php?status=printing" class="btn btn-primary">View Jobs for Printing</a>
        <a href="completed_jobs.php" class="btn btn-primary">View Completed Jobs</a>
    </div>
</div>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
