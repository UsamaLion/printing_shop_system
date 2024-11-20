
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

// Fetch summary of all clients' payments
$summary_query = "
    SELECT 
        c.ClientID,
        c.Name AS ClientName,
        COUNT(j.JobID) AS TotalJobs,
        SUM(j.Rate) AS TotalAmount,
        SUM(CASE WHEN j.PaymentStatus = 'Received' THEN j.Rate ELSE 0 END) AS PaidAmount,
        SUM(CASE WHEN j.PaymentStatus = 'Pending' THEN j.Rate ELSE 0 END) AS PendingAmount
    FROM 
        Clients c
    LEFT JOIN 
        Jobs j ON c.ClientID = j.ClientID
    GROUP BY 
        c.ClientID
    ORDER BY 
        c.Name
";

$summary_result = mysqli_query($conn, $summary_query);

// Calculate totals
$total_jobs = 0;
$total_amount = 0;
$total_paid = 0;
$total_pending = 0;

// Prepare data for CSV export
$csv_data = array();
$csv_data[] = array('Client Name', 'Total Jobs', 'Total Amount', 'Paid Amount', 'Pending Amount');

?>

<h2 class="mb-4">Account Book</h2>

<div class="mb-3">
    <a href="export_csv.php" class="btn btn-success">Export to CSV</a>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="thead-light">
            <tr>
                <th>Client Name</th>
                <th>Total Jobs</th>
                <th>Total Amount</th>
                <th>Paid Amount</th>
                <th>Pending Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($summary_result) > 0) {
                while($row = mysqli_fetch_assoc($summary_result)) {
                    $total_jobs += $row['TotalJobs'];
                    $total_amount += $row['TotalAmount'];
                    $total_paid += $row['PaidAmount'];
                    $total_pending += $row['PendingAmount'];

                    echo "<tr>";
                    echo "<td>" . $row['ClientName'] . "</td>";
                    echo "<td>" . $row['TotalJobs'] . "</td>";
                    echo "<td>" . formatCurrency($row['TotalAmount']) . "</td>";
                    echo "<td>" . formatCurrency($row['PaidAmount']) . "</td>";
                    echo "<td>" . formatCurrency($row['PendingAmount']) . "</td>";
                    echo "<td>
                            <a href='client_payments.php?id=".$row['ClientID']."' class='btn btn-sm btn-info'>View Details</a>
                          </td>";
                    echo "</tr>";

                    // Add row to CSV data
                    $csv_data[] = array(
                        $row['ClientName'],
                        $row['TotalJobs'],
                        $row['TotalAmount'],
                        $row['PaidAmount'],
                        $row['PendingAmount']
                    );
                }
            } else {
                echo "<tr><td colspan='6'>No client data found</td></tr>";
            }
            ?>
        </tbody>
        <tfoot>
            <tr class="font-weight-bold">
                <td>Total</td>
                <td><?php echo $total_jobs; ?></td>
                <td><?php echo formatCurrency($total_amount); ?></td>
                <td><?php echo formatCurrency($total_paid); ?></td>
                <td><?php echo formatCurrency($total_pending); ?></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="mt-4">
    <h3>Summary</h3>
    <p>Total Revenue: <?php echo formatCurrency($total_amount); ?></p>
    <p>Total Received: <?php echo formatCurrency($total_paid); ?></p>
    <p>Total Pending: <?php echo formatCurrency($total_pending); ?></p>
</div>

<?php
// Store CSV data in session for export
$_SESSION['csv_data'] = $csv_data;

mysqli_close($conn);
include 'includes/footer.php';
?>
