
<?php
require_once 'config.php';
include 'includes/header.php';

// Check if the user is logged in and is an admin, if not then redirect to login page
if(!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin"){
    header("location: login.php");
    exit;
}

$error = '';
$success = '';

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
    
    // Fetch roles
    $roles_query = "SELECT RoleID, RoleName FROM Roles";
    $roles_result = mysqli_query($conn, $roles_query);
    
    // Process form submission
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $role_id = $_POST['role'];
        $contact_details = trim($_POST['contact_details']);
        
        // Validate input
        if(empty($name) || empty($email) || empty($role_id)) {
            $error = "Name, Email, and Role are required fields.";
        } else {
            // Check if email already exists (excluding the current user)
            $check_email = "SELECT UserID FROM Users WHERE Email = ? AND UserID != ?";
            if($stmt = mysqli_prepare($conn, $check_email)){
                mysqli_stmt_bind_param($stmt, "si", $email, $user_id);
                if(mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    if(mysqli_stmt_num_rows($stmt) > 0){
                        $error = "This email is already registered to another user.";
                    }
                }
                mysqli_stmt_close($stmt);
            }
            
            if(empty($error)){
                // Update user
                $update_query = "UPDATE Users SET Name = ?, Email = ?, RoleID = ?, ContactDetails = ? WHERE UserID = ?";
                
                if($stmt = mysqli_prepare($conn, $update_query)){
                    mysqli_stmt_bind_param($stmt, "ssisi", $name, $email, $role_id, $contact_details, $user_id);
                    
                    if(mysqli_stmt_execute($stmt)){
                        $success = "User updated successfully.";
                        
                        // Refresh user details
                        $result = mysqli_query($conn, "SELECT u.*, r.RoleName FROM Users u JOIN Roles r ON u.RoleID = r.RoleID WHERE u.UserID = $user_id");
                        $user = mysqli_fetch_array($result, MYSQLI_ASSOC);
                    } else{
                        $error = "Error: " . mysqli_error($conn);
                    }
                    
                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
} else {
    header("location: users.php");
    exit();
}
?>

<h2>Edit User</h2>

<?php
if(!empty($error)){
    echo '<div class="alert alert-danger">' . $error . '</div>';
}
if(!empty($success)){
    echo '<div class="alert alert-success">' . $success . '</div>';
}
?>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=$user_id"; ?>" method="post">
    <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" class="form-control" value="<?php echo $user['Name']; ?>" required>
    </div>
    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control" value="<?php echo $user['Email']; ?>" required>
    </div>
    <div class="form-group">
        <label>Role</label>
        <select name="role" class="form-control" required>
            <?php
            mysqli_data_seek($roles_result, 0);
            while ($row = mysqli_fetch_assoc($roles_result)) {
                $selected = ($row['RoleID'] == $user['RoleID']) ? 'selected' : '';
                echo "<option value='" . $row['RoleID'] . "' $selected>" . $row['RoleName'] . "</option>";
            }
            ?>
        </select>
    </div>
    <div class="form-group">
        <label>Contact Details</label>
        <textarea name="contact_details" class="form-control"><?php echo $user['ContactDetails']; ?></textarea>
    </div>
    <div class="form-group">
        <input type="submit" class="btn btn-primary" value="Update User">
        <a href="users.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
