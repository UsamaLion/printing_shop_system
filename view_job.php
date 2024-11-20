
<?php
require_once 'config.php';
include 'includes/header.php';

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["user_id"])){
    header("location: login.php");
    exit;
}

// Check if job ID is set
if(isset($_GET['id']) && !empty(trim($_GET['id']))){
    $job_id = trim($_GET['id']);
    
    // Fetch job details
    $job_query = "SELECT j.*, c.Name as ClientName, jt.JobTypeName, jt.Fields as JobTypeFields, js.StatusName, u.Name as DesignerName
                  FROM Jobs j
                  JOIN Clients c ON j.ClientID = c.ClientID
                  JOIN JobTypes jt ON j.JobTypeID = jt.JobTypeID
                  JOIN JobStatus js ON j.StatusID = js.StatusID
                  JOIN Users u ON j.DesignerID = u.UserID
                  WHERE j.JobID = ?";
    
    if($stmt = mysqli_prepare($conn, $job_query)){
        mysqli_stmt_bind_param($stmt, "i", $job_id);
        
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);
    
            if(mysqli_num_rows($result) == 1){
                $job = mysqli_fetch_array($result, MYSQLI_ASSOC);
                $job_type_fields = json_decode($job['JobTypeFields'], true);
                $custom_fields = json_decode($job['CustomFields'], true);
            } else {
                header("location: jobs.php");
                exit();
            }
            
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    
    mysqli_stmt_close($stmt);
    
    // Fetch job history
    $history_query = "SELECT jh.*, js.StatusName, u.Name as ChangedByName
                      FROM JobHistory jh
                      JOIN JobStatus js ON jh.StatusID = js.StatusID
                      JOIN Users u ON jh.ChangedBy = u.UserID
                      WHERE jh.JobID = ?
                      ORDER BY jh.ChangedDate DESC";
    
    if($stmt = mysqli_prepare($conn, $history_query)){
        mysqli_stmt_bind_param($stmt, "i", $job_id);
        
        if(mysqli_stmt_execute($stmt)){
            $history_result = mysqli_stmt_get_result($stmt);
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    
    mysqli_stmt_close($stmt);
} else {
    header("location: jobs.php");
    exit();
}
?>

<h2>View Job</h2>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Job Details</h5>
        <p><strong>Job ID:</strong> <?php echo $job['JobID']; ?></p>
        <p><strong>Job Type:</strong> <?php echo $job['JobTypeName']; ?></p>
        <p><strong>Client:</strong> <?php echo $job['ClientName']; ?></p>
        <p><strong>Designer:</strong> <?php echo $job['DesignerName']; ?></p>
        <p><strong>Description:</strong> <?php echo $job['Description']; ?></p>
        <p><strong>Rate:</strong> $<?php echo $job['Rate']; ?></p>
        <p><strong>Status:</strong> <?php echo $job['StatusName']; ?></p>
        <p><strong>Created Date:</strong> <?php echo $job['CreatedDate']; ?></p>
        <p><strong>Completed Date:</strong> <?php echo $job['CompletedDate'] ? $job['CompletedDate'] : 'N/A'; ?></p>
        <p><strong>Payment Status:</strong> <?php echo $job['PaymentStatus']; ?></p>
        
        <h6>Custom Fields:</h6>
        <?php
        foreach ($job_type_fields as $field => $type) {
            echo "<p><strong>" . ucfirst($field) . ":</strong> ";
            if ($type == 'boolean') {
                echo $custom_fields[$field] ? 'Yes' : 'No';
            } else {
                echo $custom_fields[$field];
            }
            echo "</p>";
        }
        ?>
    </div>
</div>

<h3 class="mt-4">Job History</h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Date</th>
            <th>Status</th>
            <th>Changed By</th>
            <th>Notes</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (mysqli_num_rows($history_result) > 0) {
            while($row = mysqli_fetch_assoc($history_result)) {
                echo "<tr>";
                echo "<td>" . $row['ChangedDate'] . "</td>";
                echo "<td>" . $row['StatusName'] . "</td>";
                echo "<td>" . $row['ChangedByName'] . "</td>";
                echo "<td>" . $row['Notes'] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No history found</td></tr>";
        }
        ?>
    </tbody>
</table>

<a href="edit_job.php?id=<?php echo $job_id; ?>" class="btn btn-primary">Edit Job</a>
<a href="jobs.php" class="btn btn-secondary">Back to Jobs</a>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
