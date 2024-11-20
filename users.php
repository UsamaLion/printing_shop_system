
<?php
require_once 'config.php';
include 'includes/header.php';

// Check if the user is logged in and is an admin, if not then redirect to login page
if(!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin"){
    header("location: login.php");
    exit;
}

// Fetch users from the database
$sql = "SELECT u.UserID, u.Name, u.Email, r.RoleName 
        FROM Users u
        JOIN Roles r ON u.RoleID = r.RoleID
        ORDER BY u.Name ASC";
$result = mysqli_query($conn, $sql);
?>

<h2>User Management</h2>
<a href="create_user.php" class="btn btn-primary mb-3">Add New User</a>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>User ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . $row['UserID'] . "</td>";
                echo "<td>" . $row['Name'] . "</td>";
                echo "<td>" . $row['Email'] . "</td>";
                echo "<td>" . $row['RoleName'] . "</td>";
                echo "<td>
                        <a href='edit_user.php?id=".$row['UserID']."' class='btn btn-sm btn-info'>Edit</a>
                        <a href='view_user.php?id=".$row['UserID']."' class='btn btn-sm btn-primary'>View</a>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No users found</td></tr>";
        }
        ?>
    </tbody>
</table>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
