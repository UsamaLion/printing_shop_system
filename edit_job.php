
<?php
require_once 'config.php';
include 'includes/header.php';

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["user_id"])){
    header("location: login.php");
    exit;
}

$error = '';
$success = '';

// Check if job ID is set
if(isset($_GET['id']) && !empty(trim($_GET['id']))){
    $job_id = trim($_GET['id']);
    
    // Fetch job details
    $job_query = "SELECT j.*, c.Name as ClientName, jt.JobTypeName, jt.Fields as JobTypeFields, u.Name as DesignerName
                  FROM Jobs j
                  JOIN Clients c ON j.ClientID = c.ClientID
                  JOIN JobTypes jt ON j.JobTypeID = jt.JobTypeID
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
    
    // Fetch job statuses
    $status_query = "SELECT StatusID, StatusName FROM JobStatus";
    $status_result = mysqli_query($conn, $status_query);
    
    // Process form submission
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $description = $_POST['description'];
        $rate = $_POST['rate'];
        $status_id = $_POST['status'];
        $payment_status = $_POST['payment_status'];
        
        // Prepare custom fields data
        $custom_fields = array();
        foreach ($job_type_fields as $field => $type) {
            if (isset($_POST[$field])) {
                $custom_fields[$field] = $_POST[$field];
            }
        }
        $custom_fields_json = json_encode($custom_fields);
        
        // Update job
        $update_query = "UPDATE Jobs SET Description = ?, Rate = ?, StatusID = ?, PaymentStatus = ?, CustomFields = ? WHERE JobID = ?";
        
        if($stmt = mysqli_prepare($conn, $update_query)){
            mysqli_stmt_bind_param($stmt, "sdissi", $description, $rate, $status_id, $payment_status, $custom_fields_json, $job_id);
            
            if(mysqli_stmt_execute($stmt)){
                $success = "Job updated successfully.";
                
                // Refresh job details
                $result = mysqli_query($conn, $job_query);
                $job = mysqli_fetch_array($result, MYSQLI_ASSOC);
                $custom_fields = json_decode($job['CustomFields'], true);
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
            
            mysqli_stmt_close($stmt);
        }
    }
} else {
    header("location: jobs.php");
    exit();
}
?>

<h2>Edit Job</h2>

<?php
if(!empty($error)){
    echo '<div class="alert alert-danger">' . $error . '</div>';
}
if(!empty($success)){
    echo '<div class="alert alert-success">' . $success . '</div>';
}
?>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=$job_id"; ?>" method="post">
    <div class="form-group">
        <label>Job Type</label>
        <input type="text" class="form-control" value="<?php echo $job['JobTypeName']; ?>" readonly>
    </div>
    <div class="form-group">
        <label>Client</label>
        <input type="text" class="form-control" value="<?php echo $job['ClientName']; ?>" readonly>
    </div>
    <div class="form-group">
        <label>Designer</label>
        <input type="text" class="form-control" value="<?php echo $job['DesignerName']; ?>" readonly>
    </div>
    <?php
    foreach ($job_type_fields as $field => $type) {
        echo '<div class="form-group">';
        echo '<label>' . ucfirst($field) . '</label>';
        switch ($type) {
            case 'string':
            case 'int':
            case 'decimal':
                echo '<input type="text" name="' . $field . '" class="form-control" value="' . (isset($custom_fields[$field]) ? htmlspecialchars($custom_fields[$field]) : '') . '" required>';
                break;
            case 'text':
                echo '<textarea name="' . $field . '" class="form-control" required>' . (isset($custom_fields[$field]) ? htmlspecialchars($custom_fields[$field]) : '') . '</textarea>';
                break;
            case 'boolean':
                echo '<select name="' . $field . '" class="form-control" required>';
                echo '<option value="1"' . (isset($custom_fields[$field]) && $custom_fields[$field] == '1' ? ' selected' : '') . '>Yes</option>';
                echo '<option value="0"' . (isset($custom_fields[$field]) && $custom_fields[$field] == '0' ? ' selected' : '') . '>No</option>';
                echo '</select>';
                break;
        }
        echo '</div>';
    }
    ?>
    <div class="form-group">
        <label>Description</label>
        <textarea name="description" class="form-control" required><?php echo $job['Description']; ?></textarea>
    </div>
    <div class="form-group">
        <label>Rate</label>
        <input type="number" name="rate" class="form-control" step="0.01" value="<?php echo $job['Rate']; ?>" required>
    </div>
    <div class="form-group">
        <label>Status</label>
        <select name="status" class="form-control" required>
            <?php
            mysqli_data_seek($status_result, 0);
            while ($row = mysqli_fetch_assoc($status_result)) {
                $selected = ($row['StatusID'] == $job['StatusID']) ? 'selected' : '';
                echo "<option value='" . $row['StatusID'] . "' $selected>" . $row['StatusName'] . "</option>";
            }
            ?>
        </select>
    </div>
    <div class="form-group">
        <label>Payment Status</label>
        <select name="payment_status" class="form-control" required>
            <option value="Pending" <?php echo ($job['PaymentStatus'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
            <option value="Received" <?php echo ($job['PaymentStatus'] == 'Received') ? 'selected' : ''; ?>>Received</option>
        </select>
    </div>
    <div class="form-group">
        <input type="submit" class="btn btn-primary" value="Update Job">
        <a href="jobs.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
