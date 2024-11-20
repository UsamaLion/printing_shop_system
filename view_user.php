
<?php
require_once 'config.php';
include 'includes/header.php';

// Check if the user is logged in and is an admin, if not then redirect to login page
if(!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin"){
    header("location: login.php");
    exit;
}

// Check if user ID is set
if(isset($_GET['id']) && !empty(trim($_GET['id']))){
    $user_id = trim($_GET['id']);
    
    // Fetch user details
    $user_query = "SELECT u.*, r.RoleName FROM Users u JOIN Roles r ON u.RoleID = r.RoleID WHERE u.UserID = ?";
    
    if($stmt = mysqli_prepare($conn, $user_query)){
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);
    
            if(mysqli_num_rows($result) == 1){
                $user = mysqli_fetch_array($result, MYSQLI_ASSOC);
            } else {
                header("location: users.php");
                exit();
            }
            
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    
    mysqli_stmt_close($stmt);
    
    // Fetch user's jobs (if Designer)
    if($user['RoleName'] == 'Designer'){
        $jobs_query = "SELECT j.JobID, jt.JobTypeName, j.Description, j.CreatedDate, j.CompletedDate, j.PaymentStatus, js.StatusName
                       FROM Jobs j
                       JOIN JobTypes jt ON j.JobTypeID = jt.JobTypeID
                       JOIN JobStatus js ON j.StatusID = js.StatusID
                       WHERE j.DesignerID = ?
                       ORDER BY j.CreatedDate DESC";
        
        if($stmt = mysqli_prepare($conn, $jobs_query)){
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            
            if(mysqli_stmt_execute($stmt)){
                $jobs_result = mysqli_stmt_get_result($stmt);
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        
        mysqli_stmt_close($stmt);
    }
} else {
    header("location: users.php");
    exit();
}
?>

<h2>View User</h2>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">User Details</h5>
        <p><strong>User ID:</strong> <?php echo $user['UserID']; ?></p>
        <p><strong>Name:</strong> <?php echo $user['Name']; ?></p>
        <p><strong>Email:</strong> <?php echo $user['Email']; ?></p>
        <p><strong>Role:</strong> <?php echo $user['RoleName']; ?></p>
        <p><strong>Contact Details:</strong> <?php echo $user['ContactDetails']; ?></p>
    </div>
</div>

<?php if($user['RoleName'] == 'Designer'): ?>
<h3 class="mt-4">User's Jobs</h3>
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
        if (isset($jobs_result) && mysqli_num_rows($jobs_result) > 0) {
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
            echo "<tr><td colspan='7'>No jobs found for this user</td></tr>";
        }
        ?>
    </tbody>
</table>
<?php endif; ?>

<a href="edit_user.php?id=<?php echo $user_id; ?>" class="btn btn-primary">Edit User</a>
<a href="users.php" class="btn btn-secondary">Back to Users</a>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
