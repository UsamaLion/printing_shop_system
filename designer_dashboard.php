
<?php
require_once 'config.php';
include 'includes/header.php';

// Check if the user is logged in and is a designer
if(!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Designer"){
    header("location: login.php");
    exit;
}

$designer_id = $_SESSION["user_id"];

// Fetch job status overview for the designer
$job_status_query = "SELECT js.StatusName, COUNT(*) as Count 
                     FROM Jobs j 
                     JOIN JobStatus js ON j.StatusID = js.StatusID 
                     WHERE j.DesignerID = $designer_id
                     GROUP BY j.StatusID";
$job_status_result = mysqli_query($conn, $job_status_query);

// Fetch recent jobs for the designer
$recent_jobs_query = "SELECT j.JobID, j.Description, c.Name as ClientName, js.StatusName, j.CreatedDate
                      FROM Jobs j
                      JOIN Clients c ON j.ClientID = c.ClientID
                      JOIN JobStatus js ON j.StatusID = js.StatusID
                      WHERE j.DesignerID = $designer_id
                      ORDER BY j.CreatedDate DESC
                      LIMIT 5";
$recent_jobs_result = mysqli_query($conn, $recent_jobs_query);

// Fetch jobs that need revision
$revision_jobs_query = "SELECT j.JobID, j.Description, c.Name as ClientName, js.StatusName, j.CreatedDate
                        FROM Jobs j
                        JOIN Clients c ON j.ClientID = c.ClientID
                        JOIN JobStatus js ON j.StatusID = js.StatusID
                        WHERE j.DesignerID = $designer_id AND js.StatusName = 'Revision Needed'
                        ORDER BY j.CreatedDate DESC";
$revision_jobs_result = mysqli_query($conn, $revision_jobs_query);

// Fetch job statuses for the dropdown
$status_query = "SELECT StatusID, StatusName FROM JobStatus";
$status_result = mysqli_query($conn, $status_query);
?>

<h2 class="mb-4">Designer Dashboard</h2>

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
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                Recent Jobs
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Job ID</th>
                                <th>Description</th>
                                <th>Client</th>
                                <th>Status</th>
                                <th>Created Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($row = mysqli_fetch_assoc($recent_jobs_result)) {
                                echo "<tr>";
                                echo "<td>" . $row['JobID'] . "</td>";
                                echo "<td>" . $row['Description'] . "</td>";
                                echo "<td>" . $row['ClientName'] . "</td>";
                                echo "<td>" . $row['StatusName'] . "</td>";
                                echo "<td>" . $row['CreatedDate'] . "</td>";
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

<h3 class="mb-3">Jobs Needing Revision</h3>
<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="thead-light">
            <tr>
                <th>Job ID</th>
                <th>Client</th>
                <th>Description</th>
                <th>Created Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($revision_jobs_result) > 0) {
                while($row = mysqli_fetch_assoc($revision_jobs_result)) {
                    echo "<tr>";
                    echo "<td>" . $row['JobID'] . "</td>";
                    echo "<td>" . $row['ClientName'] . "</td>";
                    echo "<td>" . $row['Description'] . "</td>";
                    echo "<td>" . $row['CreatedDate'] . "</td>";
                    echo "<td>
                            <a href='edit_job.php?id=" . $row['JobID'] . "' class='btn btn-sm btn-primary'>Edit Job</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No jobs needing revision</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<h3 class="mb-3">Your Assigned Jobs</h3>
<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="thead-light">
            <tr>
                <th>Job ID</th>
                <th>Client</th>
                <th>Job Type</th>
                <th>Description</th>
                <th>Current Status</th>
                <th>Created Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $assigned_jobs_query = "SELECT j.JobID, j.Description, c.Name as ClientName, jt.JobTypeName, js.StatusName, j.CreatedDate
                                    FROM Jobs j
                                    JOIN Clients c ON j.ClientID = c.ClientID
                                    JOIN JobTypes jt ON j.JobTypeID = jt.JobTypeID
                                    JOIN JobStatus js ON j.StatusID = js.StatusID
                                    WHERE j.DesignerID = $designer_id
                                    ORDER BY j.CreatedDate DESC";
            $assigned_jobs_result = mysqli_query($conn, $assigned_jobs_query);

            if (mysqli_num_rows($assigned_jobs_result) > 0) {
                while($row = mysqli_fetch_assoc($assigned_jobs_result)) {
                    echo "<tr>";
                    echo "<td>" . $row['JobID'] . "</td>";
                    echo "<td>" . $row['ClientName'] . "</td>";
                    echo "<td>" . $row['JobTypeName'] . "</td>";
                    echo "<td>" . $row['Description'] . "</td>";
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
                echo "<tr><td colspan='7'>No assigned jobs found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <h3>Quick Links</h3>
        <a href="create_job.php" class="btn btn-primary">Create New Job</a>
        <a href="jobs.php" class="btn btn-primary">View All Jobs</a>
    </div>
</div>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
