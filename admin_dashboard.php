
<?php
require_once 'config.php';
include 'includes/header.php';

// Check if the user is logged in and is an admin
if(!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin"){
    header("location: login.php");
    exit;
}

// Function to format currency
function formatCurrency($amount) {
    return 'PKR ' . number_format($amount, 2);
}

// Fetch summary statistics
$stats_query = "
    SELECT 
        (SELECT COUNT(*) FROM Jobs) AS total_jobs,
        (SELECT COUNT(*) FROM Jobs WHERE PaymentStatus = 'Pending') AS pending_payments,
        (SELECT COUNT(*) FROM Jobs WHERE StatusID = (SELECT StatusID FROM JobStatus WHERE StatusName = 'Completed')) AS completed_jobs,
        (SELECT SUM(Rate) FROM Jobs) AS total_revenue,
        (SELECT SUM(Rate) FROM Jobs WHERE PaymentStatus = 'Received') AS received_payments,
        (SELECT COUNT(*) FROM Clients) AS total_clients,
        (SELECT COUNT(*) FROM Users WHERE RoleID = (SELECT RoleID FROM Roles WHERE RoleName = 'Designer')) AS total_designers
";

$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Fetch recent jobs
$recent_jobs_query = "
    SELECT j.JobID, j.Description, c.Name AS ClientName, js.StatusName, j.CreatedDate
    FROM Jobs j
    JOIN Clients c ON j.ClientID = c.ClientID
    JOIN JobStatus js ON j.StatusID = js.StatusID
    ORDER BY j.CreatedDate DESC
    LIMIT 5
";

$recent_jobs_result = mysqli_query($conn, $recent_jobs_query);

?>

<h2 class="mb-4">Admin Dashboard</h2>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Jobs</h5>
                <p class="card-text"><?php echo $stats['total_jobs']; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Pending Payments</h5>
                <p class="card-text"><?php echo $stats['pending_payments']; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Completed Jobs</h5>
                <p class="card-text"><?php echo $stats['completed_jobs']; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Revenue</h5>
                <p class="card-text"><?php echo formatCurrency($stats['total_revenue']); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Recent Jobs</h5>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Job ID</th>
                            <th>Client</th>
                            <th>Status</th>
                            <th>Created Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($job = mysqli_fetch_assoc($recent_jobs_result)): ?>
                        <tr>
                            <td><?php echo $job['JobID']; ?></td>
                            <td><?php echo $job['ClientName']; ?></td>
                            <td><?php echo $job['StatusName']; ?></td>
                            <td><?php echo $job['CreatedDate']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Quick Stats</h5>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">Total Clients: <?php echo $stats['total_clients']; ?></li>
                    <li class="list-group-item">Total Designers: <?php echo $stats['total_designers']; ?></li>
                    <li class="list-group-item">Received Payments: <?php echo formatCurrency($stats['received_payments']); ?></li>
                    <li class="list-group-item">Pending Payments: <?php echo formatCurrency($stats['total_revenue'] - $stats['received_payments']); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Quick Links</h5>
                <a href="jobs.php" class="btn btn-primary mr-2">Manage Jobs</a>
                <a href="clients.php" class="btn btn-primary mr-2">Manage Clients</a>
                <a href="users.php" class="btn btn-primary mr-2">Manage Users</a>
                <a href="account_book.php" class="btn btn-primary">View Account Book</a>
            </div>
        </div>
    </div>
</div>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
