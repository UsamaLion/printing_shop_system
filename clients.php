
<?php
require_once 'config.php';
include 'includes/header.php';

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["user_id"])){
    header("location: login.php");
    exit;
}

// Initialize search variable
$search_query = "";

// Process search form submission
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['search'])) {
    $search_query = trim($_GET['search_query']);
}

// Prepare the base query
$sql = "SELECT * FROM Clients WHERE 1=1";

// Add search condition
if (!empty($search_query)) {
    $sql .= " AND (Name LIKE '%$search_query%' OR Email LIKE '%$search_query%' OR PrimaryMobile LIKE '%$search_query%')";
}

$sql .= " ORDER BY Name ASC";

$result = mysqli_query($conn, $sql);
?>

<h2 class="mb-4">Client Management</h2>
<div class="row mb-3">
    <div class="col-md-6">
        <a href="create_client.php" class="btn btn-primary">Add New Client</a>
    </div>
</div>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="mb-4">
    <div class="form-row">
        <div class="col-md-4 mb-3">
            <input type="text" name="search_query" class="form-control" placeholder="Search clients..." value="<?php echo $search_query; ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" name="search" class="btn btn-primary">Search</button>
        </div>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="thead-light">
            <tr>
                <th>Client ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Primary Mobile</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . $row['ClientID'] . "</td>";
                    echo "<td>" . $row['Name'] . "</td>";
                    echo "<td>" . $row['Email'] . "</td>";
                    echo "<td>" . $row['PrimaryMobile'] . "</td>";
                    echo "<td>
                            <a href='edit_client.php?id=".$row['ClientID']."' class='btn btn-sm btn-info'>Edit</a>
                            <a href='view_client.php?id=".$row['ClientID']."' class='btn btn-sm btn-primary'>View</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No clients found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
