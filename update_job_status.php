
<?php
require_once 'config.php';
require_once 'includes/notifications.php';
session_start();

// Check if the user is logged in
if(!isset($_SESSION["user_id"])){
    header("location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['job_id']) && isset($_POST['new_status'])) {
    $job_id = $_POST['job_id'];
    $new_status_id = $_POST['new_status'];
    $user_id = $_SESSION["user_id"];
    $revision_comments = isset($_POST['revision_comments']) ? $_POST['revision_comments'] : '';

    // Get the new status name
    $status_query = "SELECT StatusName FROM JobStatus WHERE StatusID = ?";
    if($status_stmt = mysqli_prepare($conn, $status_query)){
        mysqli_stmt_bind_param($status_stmt, "i", $new_status_id);
        mysqli_stmt_execute($status_stmt);
        $status_result = mysqli_stmt_get_result($status_stmt);
        $status_row = mysqli_fetch_assoc($status_result);
        $new_status_name = $status_row['StatusName'];
        mysqli_stmt_close($status_stmt);
    }

    // Update job status
    $update_query = "UPDATE Jobs SET StatusID = ? WHERE JobID = ?";
    
    if($stmt = mysqli_prepare($conn, $update_query)){
        mysqli_stmt_bind_param($stmt, "ii", $new_status_id, $job_id);
        
        if(mysqli_stmt_execute($stmt)){
            // Create notification message
            $notification_message = "Job #$job_id status updated to $new_status_name";
            
            if ($new_status_name == 'Revision Needed') {
                $notification_message .= ". Revision comments: $revision_comments";
            }
            
            // Get the designer's ID
            $job_query = "SELECT DesignerID FROM Jobs WHERE JobID = ?";
            if($job_stmt = mysqli_prepare($conn, $job_query)){
                mysqli_stmt_bind_param($job_stmt, "i", $job_id);
                mysqli_stmt_execute($job_stmt);
                $job_result = mysqli_stmt_get_result($job_stmt);
                $job_row = mysqli_fetch_assoc($job_result);
                $designer_id = $job_row['DesignerID'];
                mysqli_stmt_close($job_stmt);
                
                // Notify the designer
                addNotification($conn, $designer_id, $notification_message);
            }
            
            // Notify the admin (assuming admin has UserID 1)
            addNotification($conn, 1, $notification_message);
            
            $_SESSION['success_message'] = "Job status updated successfully.";
        } else {
            $_SESSION['error_message'] = "Error updating job status.";
        }
        
        mysqli_stmt_close($stmt);
    }

    // Redirect back to the appropriate dashboard
    if ($_SESSION["role"] == "Designer") {
        header("location: designer_dashboard.php");
    } elseif ($_SESSION["role"] == "Printing Press") {
        header("location: printing_press_dashboard.php");
    } else {
        header("location: jobs.php");
    }
    exit;
} else {
    header("location: index.php");
    exit;
}

mysqli_close($conn);
?>
