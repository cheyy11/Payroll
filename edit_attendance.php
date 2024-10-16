<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once('config.php'); // Include your config file


if (isset($_POST['emp_code'], $_POST['attendance_date'], $_POST['action_name'])) { 
    // Check for necessary POST parameters
    $emp_code = $_POST['emp_code'];
    $attendance_date = $_POST['attendance_date'];
    $action_name = $_POST['action_name'];
    $punch_in = $_POST['punch_in'] ?? null; // Use null if not provided
    $punch_out = $_POST['punch_out'] ?? null;

    // Prepare the SQL update statement based on action_name
    $sql = "";
    if ($action_name === 'punchin') {
        $sql = "UPDATE `" . DB_PREFIX . "attendance` 
                SET `punch_in` = ? 
                WHERE `emp_code` = ? AND `attendance_date` = ?";
    } elseif ($action_name === 'punchout') {
        $sql = "UPDATE `" . DB_PREFIX . "attendance` 
                SET `punch_out` = ? 
                WHERE `emp_code` = ? AND `attendance_date` = ?";
    } elseif ($action_name === 'both') {
        $sql = "UPDATE `" . DB_PREFIX . "attendance` 
                SET `punch_in` = ?, `punch_out` = ? 
                WHERE `emp_code` = ? AND `attendance_date` = ?";
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action name.']);
        exit;
    }

    // Prepare and execute the SQL statement
    if ($stmt = $db->prepare($sql)) {
        if ($action_name === 'both') {
            $stmt->bind_param('ssss', $punch_in, $emp_code, $attendance_date);
        } elseif ($action_name === 'punchin') {
            $stmt->bind_param('sss', $punch_in, $emp_code, $attendance_date);
        } else {
            $stmt->bind_param('sss', $punch_out, $emp_code, $attendance_date);
        }

        if ($stmt->execute()) {
            // Check if any rows were affected
            if ($stmt->affected_rows > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Attendance updated successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No attendance records found to update.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to execute update query: ' . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare SQL statement: ' . $db->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>
