
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
    
    // Process form submission
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $address = trim($_POST['address']);
        $primary_mobile = trim($_POST['primary_mobile']);
        $secondary_mobile = trim($_POST['secondary_mobile']);
        
        // Validate input
        if(empty($name) || empty($primary_mobile)) {
            $error = "Name and Primary Mobile are required fields.";
        } else {
            // Update client
            $update_query = "UPDATE Clients SET Name = ?, Email = ?, Address = ?, PrimaryMobile = ?, SecondaryMobile = ? WHERE ClientID = ?";
            
            if($stmt = mysqli_prepare($conn, $update_query)){
                mysqli_stmt_bind_param($stmt, "sssssi", $name, $email, $address, $primary_mobile, $secondary_mobile, $client_id);
                
                if(mysqli_stmt_execute($stmt)){
                    $success = "Client updated successfully.";
                    
                    // Refresh client details
                    $result = mysqli_query($conn, "SELECT * FROM Clients WHERE ClientID = $client_id");
                    $client = mysqli_fetch_array($result, MYSQLI_ASSOC);
                } else{
                    $error = "Error: " . mysqli_error($conn);
                }
                
                mysqli_stmt_close($stmt);
            }
        }
    }
} else {
    header("location: clients.php");
    exit();
}
?>

<h2>Edit Client</h2>

<?php
if(!empty($error)){
    echo '<div class="alert alert-danger">' . $error . '</div>';
}
if(!empty($success)){
    echo '<div class="alert alert-success">' . $success . '</div>';
}
?>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=$client_id"; ?>" method="post">
    <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" class="form-control" value="<?php echo $client['Name']; ?>" required>
    </div>
    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control" value="<?php echo $client['Email']; ?>">
    </div>
    <div class="form-group">
        <label>Address</label>
        <textarea name="address" class="form-control"><?php echo $client['Address']; ?></textarea>
    </div>
    <div class="form-group">
        <label>Primary Mobile</label>
        <input type="tel" name="primary_mobile" class="form-control" value="<?php echo $client['PrimaryMobile']; ?>" required>
    </div>
    <div class="form-group">
        <label>Secondary Mobile</label>
        <input type="tel" name="secondary_mobile" class="form-control" value="<?php echo $client['SecondaryMobile']; ?>">
    </div>
    <div class="form-group">
        <input type="submit" class="btn btn-primary" value="Update Client">
        <a href="clients.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
