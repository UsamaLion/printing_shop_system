
<?php
require_once 'config.php';
include 'includes/header.php';

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["user_id"])){
    header("location: login.php");
    exit;
}

// Initialize search variables
$search_query = "";
$search_status = "";
$search_job_type = "";
$search_client = "";
$search_date_from = "";
$search_date_to = "";

// Process search form submission
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['search'])) {
    $search_query = trim($_GET['search_query']);
    $search_status = $_GET['search_status'];
    $search_job_type = $_GET['search_job_type'];
    $search_client = $_GET['search_client'];
    $search_date_from = $_GET['search_date_from'];
    $search_date_to = $_GET['search_date_to'];
}

// Pagination settings
$results_per_page = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $results_per_page;

// Prepare the base query
$sql = "SELECT j.JobID, j.Description, c.Name as ClientName, jt.JobTypeName, js.StatusName, j.Rate, j.CreatedDate, j.CompletedDate, j.PaymentStatus 
        FROM Jobs j
        JOIN Clients c ON j.ClientID = c.ClientID
        JOIN JobTypes jt ON j.JobTypeID = jt.JobTypeID
        JOIN JobStatus js ON j.StatusID = js.StatusID
        WHERE 1=1";

// Add search conditions
if (!empty($search_query)) {
    $sql .= " AND (j.Description LIKE '%$search_query%' OR c.Name LIKE '%$search_query%' OR jt.JobTypeName LIKE '%$search_query%')";
}
if (!empty($search_status)) {
    $sql .= " AND js.StatusID = '$search_status'";
}
if (!empty($search_job_type)) {
    $sql .= " AND jt.JobTypeID = '$search_job_type'";
}
if (!empty($search_client)) {
    $sql .= " AND c.ClientID = '$search_client'";
}
if (!empty($search_date_from)) {
    $sql .= " AND j.CreatedDate >= '$search_date_from'";
}
if (!empty($search_date_to)) {
    $sql .= " AND j.CreatedDate <= '$search_date_to'";
}

// Count total results for pagination
$count_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM ($sql) as count_table");
$count_row = mysqli_fetch_assoc($count_result);
$total_results = $count_row['total'];
$total_pages = ceil($total_results / $results_per_page);

$sql .= " ORDER BY j.CreatedDate DESC LIMIT $offset, $results_per_page";

$result = mysqli_query($conn, $sql);

// Fetch job statuses for the dropdown
$status_query = "SELECT StatusID, StatusName FROM JobStatus";
$status_result = mysqli_query($conn, $status_query);

// Fetch job types for the dropdown
$job_types_query = "SELECT JobTypeID, JobTypeName FROM JobTypes";
$job_types_result = mysqli_query($conn, $job_types_query);

// Fetch clients for the dropdown
$clients_query = "SELECT ClientID, Name FROM Clients";
$clients_result = mysqli_query($conn, $clients_query);

?>

<h2 class="mb-4">Jobs Management</h2>
<div class="row mb-3">
    <div class="col-md-6">
        <a href="create_job.php" class="btn btn-primary">Create New Job</a>
    </div>
</div>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="mb-4">
    <div class="form-row">
        <div class="col-md-3 mb-3">
            <input type="text" name="search_query" class="form-control" placeholder="Search jobs..." value="<?php echo $search_query; ?>">
        </div>
        <div class="col-md-3 mb-3">
            <select name="search_status" class="form-control">
                <option value="">All Statuses</option>
                <?php
                mysqli_data_seek($status_result, 0);
                while ($status_row = mysqli_fetch_assoc($status_result)) {
                    $selected = ($status_row['StatusID'] == $search_status) ? 'selected' : '';
                    echo "<option value='" . $status_row['StatusID'] . "' $selected>" . $status_row['StatusName'] . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-3 mb-3">
            <select name="search_job_type" class="form-control">
                <option value="">All Job Types</option>
                <?php
                mysqli_data_seek($job_types_result, 0);
                while ($job_type_row = mysqli_fetch_assoc($job_types_result)) {
                    $selected = ($job_type_row['JobTypeID'] == $search_job_type) ? 'selected' : '';
                    echo "<option value='" . $job_type_row['JobTypeID'] . "' $selected>" . $job_type_row['JobTypeName'] . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-3 mb-3">
            <select name="search_client" class="form-control">
                <option value="">All Clients</option>
                <?php
                mysqli_data_seek($clients_result, 0);
                while ($client_row = mysqli_fetch_assoc($clients_result)) {
                    $selected = ($client_row['ClientID'] == $search_client) ? 'selected' : '';
                    echo "<option value='" . $client_row['ClientID'] . "' $selected>" . $client_row['Name'] . "</option>";
                }
                ?>
            </select>
        </div>
    </div>
    <div class="form-row">
        <div class="col-md-3 mb-3">
            <input type="date" name="search_date_from" class="form-control" placeholder="From Date" value="<?php echo $search_date_from; ?>">
        </div>
        <div class="col-md-3 mb-3">
            <input type="date" name="search_date_to" class="form-control" placeholder="To Date" value="<?php echo $search_date_to; ?>">
        </div>
        <div class="col-md-3">
            <button type="submit" name="search" class="btn btn-primary">Search</button>
        </div>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="thead-light">
            <tr>
                <th>Job ID</th>
                <th>Client</th>
                <th>Job Type</th>
                <th>Description</th>
                <th>Status</th>
                <th>Rate</th>
                <th>Created Date</th>
                <th>Completed Date</th>
                <th>Payment Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . $row['JobID'] . "</td>";
                    echo "<td>" . $row['ClientName'] . "</td>";
                    echo "<td>" . $row['JobTypeName'] . "</td>";
                    echo "<td>" . $row['Description'] . "</td>";
                    echo "<td>" . $row['StatusName'] . "</td>";
                    echo "<td>$" . $row['Rate'] . "</td>";
                    echo "<td>" . $row['CreatedDate'] . "</td>";
                    echo "<td>" . ($row['CompletedDate'] ? $row['CompletedDate'] : 'N/A') . "</td>";
                    echo "<td>" . $row['PaymentStatus'] . "</td>";
                    echo "<td>
                            <a href='edit_job.php?id=".$row['JobID']."' class='btn btn-sm btn-info'>Edit</a>
                            <a href='view_job.php?id=".$row['JobID']."' class='btn btn-sm btn-primary'>View</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='10'>No jobs found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
        <?php
        $search_params = http_build_query(array_filter([
            'search_query' => $search_query,
            'search_status' => $search_status,
            'search_job_type' => $search_job_type,
            'search_client' => $search_client,
            'search_date_from' => $search_date_from,
            'search_date_to' => $search_date_to,
            'search' => isset($_GET['search']) ? $_GET['search'] : null
        ]));

        for ($i = 1; $i <= $total_pages; $i++) {
            $active = $i == $page ? 'active' : '';
            echo "<li class='page-item $active'><a class='page-link' href='?page=$i&$search_params'>$i</a></li>";
        }
        ?>
    </ul>
</nav>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
