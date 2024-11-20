
<?php
require_once 'config.php';

// Function to run SQL queries from a file
function runSQLFile($conn, $filename) {
    $sql = file_get_contents($filename);
    if ($conn->multi_query($sql)) {
        do {
            // Consume all results
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
    }
    if ($conn->errno) {
        echo "Error executing SQL: " . $conn->error . "<br>";
        return false;
    }
    return true;
}

// Check if the database exists
$db_selected = mysqli_select_db($conn, DB_NAME);
if (!$db_selected) {
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
    if (mysqli_query($conn, $sql)) {
        echo "Database created successfully<br>";
    } else {
        echo "Error creating database: " . mysqli_error($conn) . "<br>";
        exit;
    }
}

// Select the database
mysqli_select_db($conn, DB_NAME);

// Run the SQL file
if (runSQLFile($conn, 'final_database.sql')) {
    echo "Database setup completed successfully<br>";
} else {
    echo "Error setting up database<br>";
    exit;
}

echo "Installation completed successfully. You can now <a href='login.php'>login</a> with the following credentials:<br>";
echo "Email: admin@printingshop.com<br>";
echo "Password: admin123<br>";
echo "<strong>Please change the admin password after your first login.</strong>";

mysqli_close($conn);
?>
