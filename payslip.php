<?php
// Include your DB connection and other required files here
require_once('config.php');

// Fetch and display salary details for the given emp_code and pay_month
if (isset($_GET['emp_code']) && isset($_GET['pay_month'])) {
    $emp_code = mysqli_real_escape_string($db, $_GET['emp_code']);
    $pay_month = mysqli_real_escape_string($db, $_GET['pay_month']);
    
    // Fetch salary details
    $sql = "SELECT * FROM `" . DB_PREFIX . "salaries` WHERE emp_code = '$emp_code' AND pay_month = '$pay_month'";
    $result = mysqli_query($db, $sql);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // Display salary info
        echo "<h1>Salary Details for " . $row['emp_code'] . "</h1>";
        echo "<p>Month: " . $row['pay_month'] . "</p>";
        echo "<p>Earnings: " . number_format($row['earning_total'], 2, '.', ',') . "</p>";
        echo "<p>Net Salary: " . number_format($row['net_salary'], 2, '.', ',') . "</p>";
        
        // Fetch attendance data for the given month
        $start_date = date('Y-m-01', strtotime($pay_month));
        $end_date = date('Y-m-t', strtotime($pay_month));  // Last day of the month

        // Query to fetch punchin and punchout data from attendance table
        $attendance_sql = "SELECT action_time, action_name 
                           FROM `" . DB_PREFIX . "attendance`
                           WHERE emp_code = '$emp_code'
                           AND action_time BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'
                           ORDER BY action_time ASC";
        $attendance_result = mysqli_query($db, $attendance_sql);

        $total_hours_worked = 0;
        $punchin_time = null;

        // Loop through the attendance records
        while ($attendance_row = mysqli_fetch_assoc($attendance_result)) {
            $action_time = strtotime($attendance_row['action_time']);
            $action_name = $attendance_row['action_name'];

            if ($action_name == 'punchin') {
                $punchin_time = $action_time;
            } elseif ($action_name == 'punchout' && $punchin_time) {
                // Calculate time difference between punchin and punchout
                $hours_worked = ($action_time - $punchin_time) / 3600;  // Convert seconds to hours
                $total_hours_worked += $hours_worked;
                $punchin_time = null;  // Reset punchin time
            }
        }

        // Display the total hours worked
        echo "<p>Total Hours Worked: " . number_format($total_hours_worked, 2) . " hours</p>";
    } else {
        echo "<p>No salary details found.</p>";
    }
} else {
    echo "<p>Invalid request.</p>";
}
?>
