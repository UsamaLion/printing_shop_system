
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $primary_mobile = trim($_POST['primary_mobile']);
    $secondary_mobile = trim($_POST['secondary_mobile']);
    
    // Validate input
    if(empty($name) || empty($primary_mobile)) {
        $error = "Name and Primary Mobile are required fields.";
    } else {
        // Insert new client
        $insert_query = "INSERT INTO Clients (Name, Email, Address, PrimaryMobile, SecondaryMobile) VALUES (?, ?, ?, ?, ?)";
        
        if($stmt = mysqli_prepare($conn, $insert_query)){
            mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $address, $primary_mobile, $secondary_mobile);
            
            if(mysqli_stmt_execute($stmt)){
                $success = "New client added successfully.";
            } else{
                $error = "Error: " . mysqli_error($conn);
            }
            
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<h2>Add New Client</h2>

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
        <input type="email" name="email" class="form-control">
    </div>
    <div class="form-group">
        <label>Address</label>
        <textarea name="address" class="form-control"></textarea>
    </div>
    <div class="form-group">
        <label>Primary Mobile</label>
        <input type="tel" name="primary_mobile" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Secondary Mobile</label>
        <input type="tel" name="secondary_mobile" class="form-control">
    </div>
    <div class="form-group">
        <input type="submit" class="btn btn-primary" value="Add Client">
        <a href="clients.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
