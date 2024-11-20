
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

// Initialize filter variables
$filter_date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01'); // First day of current month
$filter_date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d'); // Today

// Prepare the base query for job statistics
$job_stats_query = "
    SELECT 
        COUNT(*) AS total_jobs,
        SUM(CASE WHEN j.StatusID = (SELECT StatusID FROM JobStatus WHERE StatusName = 'Completed') THEN 1 ELSE 0 END) AS completed_jobs,
        SUM(CASE WHEN j.PaymentStatus = 'Received' THEN j.Rate ELSE 0 END) AS total_revenue,
        SUM(CASE WHEN j.PaymentStatus = 'Pending' THEN j.Rate ELSE 0 END) AS pending_revenue,
        AVG(j.Rate) AS average_job_value
    FROM 
        Jobs j
    WHERE 
        j.CreatedDate BETWEEN ? AND ?
";

$stmt = mysqli_prepare($conn, $job_stats_query);
mysqli_stmt_bind_param($stmt, "ss", $filter_date_from, $filter_date_to);
mysqli_stmt_execute($stmt);
$job_stats_result = mysqli_stmt_get_result($stmt);
$job_stats = mysqli_fetch_assoc($job_stats_result);

// Query for top clients
$top_clients_query = "
    SELECT 
        c.Name AS ClientName,
        COUNT(j.JobID) AS TotalJobs,
        SUM(j.Rate) AS TotalRevenue
    FROM 
        Clients c
    JOIN 
        Jobs j ON c.ClientID = j.ClientID
    WHERE 
        j.CreatedDate BETWEEN ? AND ?
    GROUP BY 
        c.ClientID
    ORDER BY 
        TotalRevenue DESC
    LIMIT 5
";

$stmt = mysqli_prepare($conn, $top_clients_query);
mysqli_stmt_bind_param($stmt, "ss", $filter_date_from, $filter_date_to);
mysqli_stmt_execute($stmt);
$top_clients_result = mysqli_stmt_get_result($stmt);

// Query for job type distribution
$job_type_query = "
    SELECT 
        jt.JobTypeName,
        COUNT(j.JobID) AS JobCount
    FROM 
        JobTypes jt
    LEFT JOIN 
        Jobs j ON jt.JobTypeID = j.JobTypeID AND j.CreatedDate BETWEEN ? AND ?
    GROUP BY 
        jt.JobTypeID
    ORDER BY 
        JobCount DESC
";

$stmt = mysqli_prepare($conn, $job_type_query);
mysqli_stmt_bind_param($stmt, "ss", $filter_date_from, $filter_date_to);
mysqli_stmt_execute($stmt);
$job_type_result = mysqli_stmt_get_result($stmt);

?>

<h2 class="mb-4">Reports</h2>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="mb-4">
    <div class="form-row">
        <div class="col-md-4 mb-3">
            <label for="date_from">From Date</label>
            <input type="date" id="date_from" name="date_from" class="form-control" value="<?php echo $filter_date_from; ?>">
        </div>
        <div class="col-md-4 mb-3">
            <label for="date_to">To Date</label>
            <input type="date" id="date_to" name="date_to" class="form-control" value="<?php echo $filter_date_to; ?>">
        </div>
        <div class="col-md-4 mb-3">
            <label>&nbsp;</label>
            <button type="submit" class="btn btn-primary btn-block">Apply Filter</button>
        </div>
    </div>
</form>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Job Statistics</h5>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">Total Jobs: <?php echo $job_stats['total_jobs']; ?></li>
                    <li class="list-group-item">Completed Jobs: <?php echo $job_stats['completed_jobs']; ?></li>
                    <li class="list-group-item">Total Revenue: <?php echo formatCurrency($job_stats['total_revenue']); ?></li>
                    <li class="list-group-item">Pending Revenue: <?php echo formatCurrency($job_stats['pending_revenue']); ?></li>
                    <li class="list-group-item">Average Job Value: <?php echo formatCurrency($job_stats['average_job_value']); ?></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Top 5 Clients</h5>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Client Name</th>
                            <th>Total Jobs</th>
                            <th>Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($client = mysqli_fetch_assoc($top_clients_result)): ?>
                        <tr>
                            <td><?php echo $client['ClientName']; ?></td>
                            <td><?php echo $client['TotalJobs']; ?></td>
                            <td><?php echo formatCurrency($client['TotalRevenue']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Job Type Distribution</h5>
                <canvas id="jobTypeChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Prepare data for the chart
    var jobTypeData = {
        labels: [<?php
            mysqli_data_seek($job_type_result, 0);
            while ($row = mysqli_fetch_assoc($job_type_result)) {
                echo "'" . $row['JobTypeName'] . "',";
            }
        ?>],
        datasets: [{
            data: [<?php
                mysqli_data_seek($job_type_result, 0);
                while ($row = mysqli_fetch_assoc($job_type_result)) {
                    echo $row['JobCount'] . ",";
                }
            ?>],
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)',
                'rgba(255, 159, 64, 0.8)'
            ]
        }]
    };

    // Create the chart
    var ctx = document.getElementById('jobTypeChart').getContext('2d');
    var jobTypeChart = new Chart(ctx, {
        type: 'pie',
        data: jobTypeData,
        options: {
            responsive: true,
            title: {
                display: true,
                text: 'Job Type Distribution'
            }
        }
    });
</script>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
