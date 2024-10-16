<?php 
// Include your DB connection and other required files here
require_once('config.php');

// Function to format seconds into "X hours and Y mins"
function formatTime($seconds) {
    $seconds = round($seconds / 60) * 60; // Rounding to the nearest minute
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    
    $hours_text = $hours . ' hour' . ($hours != 1 ? 's' : '');
    $minutes_text = $minutes . ' min' . ($minutes != 1 ? 's' : '');
    
    return "{$hours_text} and {$minutes_text}";
}

// Fetch and display salary details for the given emp_code and pay_month
if (isset($_GET['emp_code']) && isset($_GET['pay_month'])) {
    $emp_code = mysqli_real_escape_string($db, $_GET['emp_code']);
    $pay_month = mysqli_real_escape_string($db, $_GET['pay_month']);
    
    // Fetch salary details
    $sql = "SELECT * FROM `" . DB_PREFIX . "salaries` WHERE emp_code = '$emp_code' AND pay_month = '$pay_month'";
    $result = mysqli_query($db, $sql);

    // Initialize variables
    $total_earnings = 0;
    $deduction_total = 0;

    // Prepare HTML output
    ob_start();
    ?>
    
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <title>Salary for <?php echo $pay_month; ?> - Payroll</title>
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/AdminLTE.css">
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/skins/_all-skins.min.css">

        <style>
            @media print {
                .print-button {
                    display: none; /* Hide the button when printing */
                }
            }
            .earnings-deductions {
                display: flex; /* Use flexbox to align items */
                justify-content: space-between; /* Space between earnings and deductions */
                align-items: flex-start; /* Align items to the start */
                margin-top: 20px;
            }
            .earnings, .deductions {
                width: 45%; /* Set width for each section */
                border: 1px solid #ccc;
                padding: 20px;
            }
            .divider {
                width: 2px; /* Width of the vertical line */
                background-color: #000; /* Color of the line */
                height: 100%; /* Full height */
                margin: 0 20px; /* Spacing around the line */
            }
            .value {
                border: 1px solid #ccc;
                padding: 10px;
                margin: 10px 0; /* Margin for spacing */
            }
            .total {
                font-weight: bold;
            }
        </style>
    </head>
    <body class="hold-transition skin-blue sidebar-mini">
        <div class="wrapper">
                
        <?php require_once(dirname(__FILE__) . '/partials/topnav.php'); ?>
        <?php require_once(dirname(__FILE__) . '/partials/sidenav.php'); ?>
            <div class="content-wrapper">
                <section class="content-header">
                    <h1>Salary for <?php echo $pay_month; ?></h1>
                </section>
                <section class="content">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="box">
                                <div class="box-body">
                                    <?php if ($row = mysqli_fetch_assoc($result)) { ?>
                                        <h2>Payslip</h2>
                                        <p>Month: <?php echo $row['pay_month']; ?></p>
                                        <p>Earnings: <?php echo number_format($row['earning_total'], 2, '.', ','); ?></p>
                                        
                                        <div class="earnings-deductions">
                                            <div class="earnings">
                                                <h4>Earnings Breakdown:</h4>
                                                <ul>
                                                <?php
                                                // Fetch earnings
                                                $earning_sql = "SELECT payhead_name, pay_amount 
                                                                FROM `" . DB_PREFIX . "salaries`
                                                                WHERE emp_code = '$emp_code' 
                                                                AND pay_month = '$pay_month' 
                                                                AND pay_type = 'earnings'";
                                                $earning_result = mysqli_query($db, $earning_sql);
                                                
                                                while ($earning_row = mysqli_fetch_assoc($earning_result)) {
                                                    echo "<li class='value'>" . htmlspecialchars($earning_row['payhead_name']) . ": " . number_format($earning_row['pay_amount'], 2, '.', ',') . "</li>";
                                                    $total_earnings += $earning_row['pay_amount'];
                                                }
                                                ?>
                                                </ul>
                                                <p class="total">Total Earnings: <?php echo number_format($total_earnings, 2, '.', ','); ?></p>
                                            </div>

                                            <div class="divider"></div>

                                            <div class="deductions">
                                                <h4>Deductions:</h4>
                                                <ul>
                                                <?php
                                                // Deductions Section
                                                $deduction_sql = "SELECT payhead_name, pay_amount 
                                                                  FROM `" . DB_PREFIX . "salaries`
                                                                  WHERE emp_code = '$emp_code' 
                                                                  AND pay_month = '$pay_month' 
                                                                  AND pay_type = 'deductions'";
                                                $deduction_result = mysqli_query($db, $deduction_sql);
                                                
                                                while ($deduction_row = mysqli_fetch_assoc($deduction_result)) {
                                                    echo "<li class='value'>" . htmlspecialchars($deduction_row['payhead_name']) . ": " . number_format($deduction_row['pay_amount'], 2, '.', ',') . "</li>";
                                                    $deduction_total += $deduction_row['pay_amount'];
                                                }
                                                ?>
                                                </ul>
                                                <p class="total">Total Deductions: <?php echo number_format($deduction_total, 2, '.', ','); ?></p>
                                            </div>
                                        </div>

                                        <h3>Total Time Worked:</h3>
                                        <?php
                                        // Fetch attendance data
                                        $start_date = date('Y-m-01', strtotime($pay_month));
                                        $end_date = date('Y-m-t', strtotime($pay_month));
                                        
                                        $attendance_sql = "SELECT action_time, action_name 
                                                           FROM `" . DB_PREFIX . "attendance`
                                                           WHERE emp_code = '$emp_code'
                                                           AND action_time BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'
                                                           ORDER BY action_time ASC";
                                        $attendance_result = mysqli_query($db, $attendance_sql);

                                        $total_seconds_worked = 0;
                                        $punchin_time = null;

                                        while ($attendance_row = mysqli_fetch_assoc($attendance_result)) {
                                            $action_time = strtotime($attendance_row['action_time']);
                                            $action_name = $attendance_row['action_name'];

                                            if ($action_name == 'punchin') {
                                                $punchin_time = $action_time;
                                            } elseif ($action_name == 'punchout' && $punchin_time) {
                                                // Calculate worked seconds and accumulate
                                                $seconds_worked = $action_time - $punchin_time;
                                                $total_seconds_worked += $seconds_worked;
                                                $punchin_time = null;
                                            }
                                        }

                                        $formatted_total_time = formatTime($total_seconds_worked);
                                        echo "<p>$formatted_total_time</p>";

                                        // Fetch approved leaves
                                        $current_month = date('Y-m');
                                        $leave_sql = "SELECT COUNT(*) AS total_leaves 
                                                      FROM `" . DB_PREFIX . "leaves`
                                                      WHERE emp_code = '$emp_code' 
                                                      AND leave_status = 'approve' 
                                                      AND STR_TO_DATE(leave_dates, '%m/%d/%Y') BETWEEN '$current_month-01' AND '$current_month-31'";
                                        $leave_result = mysqli_query($db, $leave_sql);
                                        
                                        if ($leave_row = mysqli_fetch_assoc($leave_result)) {
                                            echo "<p>Total Approved Leaves: " . $leave_row['total_leaves'] . "</p>";
                                        } else {
                                            echo "<p>No approved leaves found for October.</p>";
                                        }
                                    } else {
                                        echo "<p>No salary details found.</p>";
                                    }
                                    ?>
                                </div>

                                <div class="box-footer">
                                    <h3 style="color: royalblue;">Net Salary: <?php echo number_format($row['earning_total'] - $deduction_total, 2, '.', ','); ?></h3>
                                     <br> <br>
                                    <button class="btn btn-primary print-button" onclick="window.print()">Print</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <script src="<?php echo BASE_URL; ?>plugins/jQuery/jquery-2.2.3.min.js"></script>
        <script src="<?php echo BASE_URL; ?>bootstrap/js/bootstrap.min.js"></script>
        <script src="<?php echo BASE_URL; ?>dist/js/app.min.js"></script>
    </body>
    </html>
    
    <?php
    echo ob_get_clean(); // Output the generated HTML
} else {
    echo "<p>Required parameters not provided.</p>";
}
?>
