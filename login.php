<?php
require_once 'config.php';
include 'includes/header.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $sql = "SELECT UserID, Name, Password, RoleID FROM Users WHERE Email = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            
            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $id, $name, $hashed_password, $role_id);
                if (mysqli_stmt_fetch($stmt)) {
                    // Debug information
                    echo "Debug: Entered password: " . $password . "<br>";
                    echo "Debug: Hashed password from DB: " . $hashed_password . "<br>";
                    echo "Debug: Password verification result: " . (password_verify($password, $hashed_password) ? 'true' : 'false') . "<br>";
                    
                    if (password_verify($password, $hashed_password)) {
                        $_SESSION["user_id"] = $id;
                        $_SESSION["name"] = $name;
                        $_SESSION["role_id"] = $role_id;
                        
                        $role_query = "SELECT RoleName FROM Roles WHERE RoleID = ?";
                        if ($role_stmt = mysqli_prepare($conn, $role_query)) {
                            mysqli_stmt_bind_param($role_stmt, "i", $role_id);
                            if (mysqli_stmt_execute($role_stmt)) {
                                mysqli_stmt_bind_result($role_stmt, $role_name);
                                if (mysqli_stmt_fetch($role_stmt)) {
                                    $_SESSION["role"] = $role_name;
                                }
                            }
                            mysqli_stmt_close($role_stmt);
                        }
                        
                        header("location: index.php");
                    } else {
                        $error = "Invalid email or password.";
                        error_log("Login attempt failed for email: $email. Password verification failed.");
                    }
                }
            } else {
                $error = "Invalid email or password.";
                error_log("Login attempt failed for email: $email. User not found.");
            }
        } else {
            $error = "Oops! Something went wrong. Please try again later.";
            error_log("Login query execution failed: " . mysqli_error($conn));
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="mb-4">Login</h2>
        <?php
        if(!empty($error)){
            echo '<div class="alert alert-danger">' . $error . '</div>';
        }        
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>    
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
        </form>
    </div>
</div>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>