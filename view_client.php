
<?php
require_once 'config.php';
include 'includes/header.php';

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["user_id"])){
    header("location: login.php");
    exit;
}

// Check if client ID is set
if(isset($_GET['id']) && !empty(trim($_GET['id']))){
    $client_id = trim($_GET['id']);
    
    // Fetch client details
    $client_query = "SELECT * FROM Clients WHERE ClientID = ?";
    
    if($stmt = mysqli_prepare($conn, $client_query)){
        mysqli_stmt_bind_param($stmt, "i", $client_id);
        
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);
    
            if(mysqli_num_rows($result) == 1){
                $client = mysqli_fetch_array($result, MYSQLI_ASSOC);
            } else {
                header("location: clients.php");
                exit();
            }
            
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    
    mysqli_stmt_close($stmt);
    
    // Fetch client's jobs
    $jobs_query = "SELECT j.JobID, jt.JobTypeName, j.Description, j.CreatedDate, j.CompletedDate, j.PaymentStatus, js.StatusName
                   FROM Jobs j
                   JOIN JobTypes jt ON j.JobTypeID = jt.JobTypeID
                   JOIN JobStatus js ON j.StatusID = js.StatusID
                   WHERE j.ClientID = ?
                   ORDER BY j.CreatedDate DESC";
    
    if($stmt = mysqli_prepare($conn, $jobs_query)){
        mysqli_stmt_bind_param($stmt, "i", $client_id);
        
        if(mysqli_stmt_execute($stmt)){
            $jobs_result = mysqli_stmt_get_result($stmt);
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    
    mysqli_stmt_close($stmt);
} else {
    header("location: clients.php");
    exit();
}
?>

<h2>View Client</h2>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Client Details</h5>
        <p><strong>Client ID:</strong> <?php echo $client['ClientID']; ?></p>
        <p><strong>Name:</strong> <?php echo $client['Name']; ?></p>
        <p><strong>Email:</strong> <?php echo $client['Email']; ?></p>
        <p><strong>Address:</strong> <?php echo $client['Address']; ?></p>
        <p><strong>Primary Mobile:</strong> <?php echo $client['PrimaryMobile']; ?></p>
        <p><strong>Secondary Mobile:</strong> <?php echo $client['SecondaryMobile']; ?></p>
    </div>
</div>

<h3 class="mt-4">Client's Jobs</h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Job ID</th>
            <th>Job Type</th>
            <th>Description</th>
            <th>Created Date</th>
            <th>Completed Date</th>
            <th>Status</th>
            <th>Payment Status</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (mysqli_num_rows($jobs_result) > 0) {
            while($row = mysqli_fetch_assoc($jobs_result)) {
                echo "<tr>";
                echo "<td>" . $row['JobID'] . "</td>";
                echo "<td>" . $row['JobTypeName'] . "</td>";
                echo "<td>" . $row['Description'] . "</td>";
                echo "<td>" . $row['CreatedDate'] . "</td>";
                echo "<td>" . ($row['CompletedDate'] ? $row['CompletedDate'] : 'N/A') . "</td>";
                echo "<td>" . $row['StatusName'] . "</td>";
                echo "<td>" . $row['PaymentStatus'] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No jobs found for this client</td></tr>";
        }
        ?>
    </tbody>
</table>

<a href="edit_client.php?id=<?php echo $client_id; ?>" class="btn btn-primary">Edit Client</a>
<a href="clients.php" class="btn btn-secondary">Back to Clients</a>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
