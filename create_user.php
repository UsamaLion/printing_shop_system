
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

// Fetch roles
$roles_query = "SELECT RoleID, RoleName FROM Roles";
$roles_result = mysqli_query($conn, $roles_query);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role_id = $_POST['role'];
    $contact_details = trim($_POST['contact_details']);
    
    // Validate input
    if(empty($name) || empty($email) || empty($password) || empty($role_id)) {
        $error = "Name, Email, Password, and Role are required fields.";
    } else {
        // Check if email already exists
        $check_email = "SELECT UserID FROM Users WHERE Email = ?";
        if($stmt = mysqli_prepare($conn, $check_email)){
            mysqli_stmt_bind_param($stmt, "s", $email);
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) > 0){
                    $error = "This email is already registered.";
                }
            }
            mysqli_stmt_close($stmt);
        }
        
        if(empty($error)){
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $insert_query = "INSERT INTO Users (Name, Email, Password, RoleID, ContactDetails) VALUES (?, ?, ?, ?, ?)";
            
            if($stmt = mysqli_prepare($conn, $insert_query)){
                mysqli_stmt_bind_param($stmt, "sssis", $name, $email, $hashed_password, $role_id, $contact_details);
                
                if(mysqli_stmt_execute($stmt)){
                    $success = "New user added successfully.";
                } else{
                    $error = "Error: " . mysqli_error($conn);
                }
                
                mysqli_stmt_close($stmt);
            }
        }
    }
}
?>

<h2>Add New User</h2>

<?php
if(!empty($error)){
    echo '<div class="alert alert-danger">' . $error . '</div>';
}
if(!empty($success)){
    echo '<div class="alert alert-success">' . $success . '</div>';
}
?>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Role</label>
        <select name="role" class="form-control" required>
            <?php
            while ($row = mysqli_fetch_assoc($roles_result)) {
                echo "<option value='" . $row['RoleID'] . "'>" . $row['RoleName'] . "</option>";
            }
            ?>
        </select>
    </div>
    <div class="form-group">
        <label>Contact Details</label>
        <textarea name="contact_details" class="form-control"></textarea>
    </div>
    <div class="form-group">
        <input type="submit" class="btn btn-primary" value="Add User">
        <a href="users.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
