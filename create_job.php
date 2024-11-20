
<?php
require_once 'config.php';
include 'includes/header.php';

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["user_id"])){
    header("location: login.php");
    exit;
}

$error = '';
$success = '';

// Fetch job types
$job_types_query = "SELECT JobTypeID, JobTypeName, Fields FROM JobTypes";
$job_types_result = mysqli_query($conn, $job_types_query);

// Fetch clients
$clients_query = "SELECT ClientID, Name FROM Clients";
$clients_result = mysqli_query($conn, $clients_query);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $job_type_id = $_POST['job_type'];
    $client_id = $_POST['client'];
    $description = $_POST['description'];
    $rate = $_POST['rate'];
    
    // Fetch the fields for the selected job type
    $fields_query = "SELECT Fields FROM JobTypes WHERE JobTypeID = ?";
    if ($stmt = mysqli_prepare($conn, $fields_query)) {
        mysqli_stmt_bind_param($stmt, "i", $job_type_id);
        mysqli_stmt_execute($stmt);
        $fields_result = mysqli_stmt_get_result($stmt);
        $fields_row = mysqli_fetch_assoc($fields_result);
        $fields = json_decode($fields_row['Fields'], true);
        mysqli_stmt_close($stmt);
    }
    
    // Prepare custom fields data
    $custom_fields = array();
    foreach ($fields as $field => $type) {
        if (isset($_POST[$field])) {
            $custom_fields[$field] = $_POST[$field];
        }
    }
    $custom_fields_json = json_encode($custom_fields);
    
    // Insert new job
    $insert_query = "INSERT INTO Jobs (JobTypeID, ClientID, DesignerID, StatusID, Rate, Description, CustomFields, CreatedDate) VALUES (?, ?, ?, 1, ?, ?, ?, NOW())";
    
    if ($stmt = mysqli_prepare($conn, $insert_query)) {
        mysqli_stmt_bind_param($stmt, "iiidss", $job_type_id, $client_id, $_SESSION['user_id'], $rate, $description, $custom_fields_json);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "New job created successfully.";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
        
        mysqli_stmt_close($stmt);
    }
}
?>

<h2>Create New Job</h2>

<?php
if(!empty($error)){
    echo '<div class="alert alert-danger">' . $error . '</div>';
}
if(!empty($success)){
    echo '<div class="alert alert-success">' . $success . '</div>';
}
?>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="createJobForm">
    <div class="form-group">
        <label>Job Type</label>
        <select name="job_type" id="jobType" class="form-control" required>
            <option value="">Select Job Type</option>
            <?php
            mysqli_data_seek($job_types_result, 0);
            while ($row = mysqli_fetch_assoc($job_types_result)) {
                echo "<option value='" . $row['JobTypeID'] . "' data-fields='" . htmlspecialchars($row['Fields']) . "'>" . $row['JobTypeName'] . "</option>";
            }
            ?>
        </select>
    </div>
    <div class="form-group">
        <label>Client</label>
        <select name="client" class="form-control" required>
            <?php
            while ($row = mysqli_fetch_assoc($clients_result)) {
                echo "<option value='" . $row['ClientID'] . "'>" . $row['Name'] . "</option>";
            }
            ?>
        </select>
    </div>
    <div id="customFields"></div>
    <div class="form-group">
        <label>Description</label>
        <textarea name="description" class="form-control" required></textarea>
    </div>
    <div class="form-group">
        <label>Rate</label>
        <input type="number" name="rate" class="form-control" step="0.01" required>
    </div>
    <div class="form-group">
        <input type="submit" class="btn btn-primary" value="Create Job">
    </div>
</form>

<script>
document.getElementById('jobType').addEventListener('change', function() {
    var selectedOption = this.options[this.selectedIndex];
    var fields = JSON.parse(selectedOption.getAttribute('data-fields'));
    var customFieldsDiv = document.getElementById('customFields');
    customFieldsDiv.innerHTML = '';
    
    for (var field in fields) {
        var fieldType = fields[field];
        var fieldHtml = '<div class="form-group">';
        fieldHtml += '<label>' + field + '</label>';
        
        switch(fieldType) {
            case 'string':
            case 'int':
            case 'decimal':
                fieldHtml += '<input type="text" name="' + field + '" class="form-control" required>';
                break;
            case 'text':
                fieldHtml += '<textarea name="' + field + '" class="form-control" required></textarea>';
                break;
            case 'boolean':
                fieldHtml += '<select name="' + field + '" class="form-control" required>';
                fieldHtml += '<option value="1">Yes</option>';
                fieldHtml += '<option value="0">No</option>';
                fieldHtml += '</select>';
                break;
        }
        
        fieldHtml += '</div>';
        customFieldsDiv.innerHTML += fieldHtml;
    }
});
</script>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
