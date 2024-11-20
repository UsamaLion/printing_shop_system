
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

// Check if client ID is set
if(isset($_GET['id']) && !empty(trim($_GET['id']))){
    $client_id = trim($_GET['id']);
    
    // Fetch client details
    $client_query = "SELECT Name FROM Clients WHERE ClientID = ?";
    if($stmt = mysqli_prepare($conn, $client_query)){
        mysqli_stmt_bind_param($stmt, "i", $client_id);
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);
            if(mysqli_num_rows($result) == 1){
                $client = mysqli_fetch_array($result, MYSQLI_ASSOC);
            } else {
                header("location: account_book.php");
                exit();
            }
        }
        mysqli_stmt_close($stmt);
    }

    // Fetch client's job details
    $jobs_query = "
        SELECT 
            j.JobID,
            jt.JobTypeName,
            j.Description,
            j.Rate,
            j.CreatedDate,
            j.CompletedDate,
            j.PaymentStatus
        FROM 
            Jobs j
        JOIN 
            JobTypes jt ON j.JobTypeID = jt.JobTypeID
        WHERE 
            j.ClientID = ?
        ORDER BY 
            j.CreatedDate DESC
    ";
    
    if($stmt = mysqli_prepare($conn, $jobs_query)){
        mysqli_stmt_bind_param($stmt, "i", $client_id);
        mysqli_stmt_execute($stmt);
        $jobs_result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
    }

} else {
    header("location: account_book.php");
    exit();
}
?>

<h2 class="mb-4">Payment Details for <?php echo $client['Name']; ?></h2>

<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="thead-light">
            <tr>
                <th>Job ID</th>
                <th>Job Type</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Created Date</th>
                <th>Completed Date</th>
                <th>Payment Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total_amount = 0;
            $total_paid = 0;
            $total_pending = 0;

            if (mysqli_num_rows($jobs_result) > 0) {
                while($row = mysqli_fetch_assoc($jobs_result)) {
                    $total_amount += $row['Rate'];
                    if ($row['PaymentStatus'] == 'Received') {
                        $total_paid += $row['Rate'];
                    } else {
                        $total_pending += $row['Rate'];
                    }

                    echo "<tr>";
                    echo "<td>" . $row['JobID'] . "</td>";
                    echo "<td>" . $row['JobTypeName'] . "</td>";
                    echo "<td>" . $row['Description'] . "</td>";
                    echo "<td>" . formatCurrency($row['Rate']) . "</td>";
                    echo "<td>" . $row['CreatedDate'] . "</td>";
                    echo "<td>" . ($row['CompletedDate'] ? $row['CompletedDate'] : 'N/A') . "</td>";
                    echo "<td>" . $row['PaymentStatus'] . "</td>";
                    echo "<td>
                            <select class='form-control payment-status' data-job-id='" . $row['JobID'] . "'>
                                <option value='Pending'" . ($row['PaymentStatus'] == 'Pending' ? ' selected' : '') . ">Pending</option>
                                <option value='Received'" . ($row['PaymentStatus'] == 'Received' ? ' selected' : '') . ">Received</option>
                            </select>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='8'>No jobs found for this client</td></tr>";
            }
            ?>
        </tbody>
        <tfoot>
            <tr class="font-weight-bold">
                <td colspan="3">Total</td>
                <td><?php echo formatCurrency($total_amount); ?></td>
                <td colspan="4"></td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="mt-4">
    <h3>Summary</h3>
    <p>Total Amount: <?php echo formatCurrency($total_amount); ?></p>
    <p>Total Paid: <?php echo formatCurrency($total_paid); ?></p>
    <p>Total Pending: <?php echo formatCurrency($total_pending); ?></p>
</div>

<a href="account_book.php" class="btn btn-primary mt-3">Back to Account Book</a>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('.payment-status').change(function() {
        var jobId = $(this).data('job-id');
        var newStatus = $(this).val();
        
        $.ajax({
            url: 'update_payment_status.php',
            method: 'POST',
            data: {
                job_id: jobId,
                payment_status: newStatus
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Payment status updated successfully');
                    location.reload(); // Reload the page to reflect the changes
                } else {
                    alert('Error updating payment status: ' + response.message);
                }
            },
            error: function() {
                alert('Error communicating with the server');
            }
        });
    });
});
</script>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
