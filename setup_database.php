
<?php
$servername = "localhost";
$username = "root";
$password = "";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Read SQL file
$sql = file_get_contents('database_setup.sql');

// Execute multi query
if ($conn->multi_query($sql) === TRUE) {
    echo "Database setup completed successfully";
} else {
    echo "Error setting up database: " . $conn->error;
}

$conn->close();
?>
