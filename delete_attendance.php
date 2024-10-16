<?php 
require_once('config.php'); // Include your config file

if (isset($_POST['emp_code'])) { // Remove attendance_date from condition
    $emp_code = $_POST['emp_code'];

    // Prepare the SQL delete statement to remove all attendance records for the given emp_code
    $sql = "DELETE FROM `" . DB_PREFIX . "attendance` WHERE `emp_code` = ?";

    if ($stmt = $db->prepare($sql)) {
        $stmt->bind_param('s', $emp_code); // Bind only emp_code
        
        if ($stmt->execute()) {
            // Check if any rows were affected
            if ($stmt->affected_rows > 0) {
                echo json_encode(['status' => 'success', 'message' => 'All records for the employee deleted successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No records found to delete for this employee.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to execute delete query: ' . $stmt->error]);
        }
        
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare SQL statement: ' . $db->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
