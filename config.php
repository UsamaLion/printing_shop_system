
<?php
// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'printing_shop_db');

// Attempt to connect to MySQL database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($conn === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// Other global settings
define('SITE_URL', 'http://localhost/printing_shop_system');
define('UPLOAD_DIR', __DIR__ . '/uploads/');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to format currency
// function formatCurrency($amount) {
//     return 'PKR ' . number_format($amount, 2);
// }

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
}

// Function to redirect with a message
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: $url");
    exit();
}

// Function to display flash messages
function displayFlashMessages() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'] ?? 'success';
        echo "<div class='alert alert-$type'>" . $_SESSION['flash_message'] . "</div>";
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}
?>
