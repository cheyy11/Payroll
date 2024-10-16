<?php 
require(dirname(__FILE__) . '/config.php'); 
global $userData;

// Set the timezone to Philippine Time
date_default_timezone_set('Asia/Manila');

// Fetch attendance records for the current day
$attendanceSQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "attendance` WHERE `emp_code` = '" . $userData['emp_code'] . "' AND DATE(`attendance_date`) = CURDATE() ORDER BY `attendance_date` ASC");
$punch_in_time = $punch_out_time = null; // Initialize Punch In/Out times

if ($attendanceSQL) {
    while ($attendanceDATA = mysqli_fetch_assoc($attendanceSQL)) {
        if ($attendanceDATA['action_name'] == 'punchin') {
            $punch_in_time = $attendanceDATA['attendance_date'] . ' ' . $attendanceDATA['action_time']; // Get Punch In date and time
        } elseif ($attendanceDATA['action_name'] == 'punchout') {
            $punch_out_time = $attendanceDATA['attendance_date'] . ' ' . $attendanceDATA['action_time']; // Get Punch Out date and time
        }
    }
}

// Determine the attendance status based on records
$attendanceStatus = '';
if ($punch_in_time && $punch_out_time) {
    $attendanceStatus = 'both'; // Both actions have been recorded
} elseif ($punch_in_time) {
    $attendanceStatus = 'punchin'; // Only Punch In recorded
} elseif ($punch_out_time) {
    $attendanceStatus = 'punchout'; // Only Punch Out recorded
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action_name'];
    $emp_code = $userData['emp_code'];
    $attendance_date = date('Y-m-d'); // Store the date
    $action_time = date('H:i:s'); // Store the action time (time only)
    $emp_desc = $_POST['notes']; // Get the notes from the form

    // Insert new record for both Punch In and Punch Out with action time and notes
    $stmt = mysqli_prepare($db, "INSERT INTO `" . DB_PREFIX . "attendance` (`emp_code`, `attendance_date`, `action_name`, `action_time`, `emp_desc`) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssss", $emp_code, $attendance_date, $action, $action_time, $emp_desc);

    // Execute the query and check for errors
    if (mysqli_stmt_execute($stmt)) {
      header("Location: /payroll/submit_attendance.php");
exit;
    } else {
        echo "Error: " . mysqli_error($db);
    }

    // Close statement
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <title>Attendance - Payroll</title>

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/AdminLTE.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/skins/_all-skins.min.css">

    <script src="<?php echo BASE_URL; ?>plugins/jQuery/jquery-2.2.3.min.js"></script>
    <script src="<?php echo BASE_URL; ?>bootstrap/js/bootstrap.min.js"></script>
    <script src="<?php echo BASE_URL; ?>dist/js/app.min.js"></script>
    <style>
        /* Styling the clock for a digital look */
        #live-clock {
            font-family: 'Courier New', Courier, monospace;
            font-size: 48px;
            color: #002583; /* Green for digital clock effect */
            letter-spacing: 2px;
            background-color: black;
            padding: 10px;
            border-radius: 10px;
            display: inline-block;
            width: 350px; /* Increased width for better fit */
            text-align: center;
            box-shadow: 0 0 15px rgba(0, 37, 131, 1); /* Glow effect */
        }

        /* Form Design */
        .form-container {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            max-width: 600px;
            text-align: center;
        }

        .form-group label {
            font-size: 18px;
            color: #002583;
            margin-bottom: 5px;
            display: block;
        }

        .btn-primary {
            background-color:#002583;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 18px;
            cursor: pointer;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .btn-primary:hover {
            background-color: #45a049;
        }

        /* Descriptive Text */
        .description {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
        }

        /* Footer Styling */
        .main-footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            width: 100%;
            bottom: 0;
        }

        /* Center alignment */
        .text-center {
            text-align: center;
        }
    </style>

    <script>
        function startClock() {
            setInterval(function() {
                const now = new Date();
                const utc = now.getTime() + now.getTimezoneOffset() * 60000;
                const philTime = new Date(utc + 3600000 * 8); // Philippine timezone (UTC +8)

                const hours = philTime.getHours();
                const minutes = philTime.getMinutes();
                const seconds = philTime.getSeconds();
                
                // Format hours, minutes, and seconds to 12-hour format with AM/PM
                const ampm = hours >= 12 ? 'PM' : 'AM';
                const formattedHours = hours % 12 || 12; // Convert 0 to 12 for midnight
                const formattedMinutes = ('0' + minutes).slice(-2); // Always show two digits for minutes
                const formattedSeconds = ('0' + seconds).slice(-2); // Always show two digits for seconds

                // Format date
                const day = ('0' + philTime.getDate()).slice(-2);
                const month = ('0' + (philTime.getMonth() + 1)).slice(-2); // Months are 0-indexed
                const year = philTime.getFullYear();
                const formattedDate = `${month}/${day}/${year}`;

                // Update clock display
                document.getElementById('live-clock').innerHTML = 
                    `${formattedDate} ${formattedHours}:${formattedMinutes}:${formattedSeconds} ${ampm}`;
            }, 1000); // Update every second
        }

        window.onload = startClock;
    </script>
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        <?php require_once(dirname(__FILE__) . '/partials/topnav.php'); ?>
        <?php require_once(dirname(__FILE__) . '/partials/sidenav.php'); ?>

        <div class="content-wrapper">
            <section class="content-header">
                <h1>Attendance</h1>
                <ol class="breadcrumb">
                    <li><a href="<?php echo BASE_URL; ?>"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Attendance</li>
                </ol>
            </section>

            <section class="content">
                <div class="form-container">
                    <!-- Descriptive Text -->
                    <p class="description">
                        Please use this form to Punch In or Punch Out for your attendance.
                    </p>

                    <!-- Live Digital Clock -->
                    <div class="form-group text-center">
                        <p><strong>Current Philippine Time:</strong></p>
                        <div id="live-clock"></div>
                    </div>

                    <!-- Attendance Form -->
                  <!-- Attendance Form -->
<form method="POST" class="employee" role="form" id="attendance-form">
    <div class="form-group">
        <label for="action_name">Attendance Action</label>
        <select name="action_name" id="action_name" class="form-control">
            <option value="" disabled selected>Select Action</option> <!-- Default option -->
            <option value="punchin" <?= ($attendanceStatus === 'punchin' || $attendanceStatus === 'both') ? 'disabled' : '' ?>>Punch In</option>
            <option value="punchout" <?= ($attendanceStatus === 'punchout' || $attendanceStatus === 'both') ? 'disabled' : '' ?>>Punch Out</option>
        </select>
    </div>


                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Enter any notes here..."></textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </section>
        </div>

    </div>
</body>
</html>
