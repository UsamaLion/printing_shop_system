
<?php
session_start();

// Check if the user is logged in and is an admin
if(!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin"){
    header("location: login.php");
    exit;
}

// Check if CSV data exists in the session
if(!isset($_SESSION['csv_data']) || empty($_SESSION['csv_data'])){
    header("location: account_book.php");
    exit;
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="account_book_export.csv"');

// Open the output stream
$output = fopen('php://output', 'w');

// Output CSV data
foreach ($_SESSION['csv_data'] as $row) {
    fputcsv($output, $row);
}

// Close the output stream
fclose($output);

// Clear the CSV data from the session
unset($_SESSION['csv_data']);

exit;
?>
