
<?php
require_once 'config.php';
include 'includes/header.php';
?>

<h1>Welcome to Printing Shop Management System</h1>
<p>This system allows you to manage printing jobs, clients, and users efficiently.</p>

<?php if(!isset($_SESSION['user_id'])): ?>
    <p>Please <a href="login.php">login</a> to access the system.</p>
<?php else: ?>
    <h2>Quick Links</h2>
    <ul>
        <li><a href="jobs.php">Manage Jobs</a></li>
        <li><a href="clients.php">Manage Clients</a></li>
        <?php if($_SESSION['role'] == 'Admin'): ?>
            <li><a href="users.php">Manage Users</a></li>
        <?php endif; ?>
    </ul>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
