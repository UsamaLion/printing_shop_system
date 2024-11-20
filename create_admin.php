
<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validate input
    if(empty($name) || empty($email) || empty($password)) {
        $error = "Name, Email, and Password are required fields.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new admin user
        $insert_query = "INSERT INTO Users (Name, Email, Password, RoleID, ContactDetails) VALUES (?, ?, ?, 1, ?)";
        
        if($stmt = mysqli_prepare($conn, $insert_query)){
            $contact_details = json_encode(array("phone" => ""));
            mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $hashed_password, $contact_details);
            
            if(mysqli_stmt_execute($stmt)){
                $success = "New admin user added successfully. Password hash: " . $hashed_password;
            } else{
                $error = "Error: " . mysqli_error($conn);
            }
            
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin User</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Create Admin User</h2>
        
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
                <input type="submit" class="btn btn-primary" value="Create Admin User">
            </div>
        </form>
    </div>
</body>
</html>

<?php
mysqli_close($conn);
?>
