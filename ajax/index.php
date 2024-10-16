<?php
include(dirname(dirname(__FILE__)) . '/config.php');

$case = $_GET['case'];
switch($case) {
	case 'LoginProcessHandler':
		LoginProcessHandler();
		break;
	case 'AttendanceProcessHandler':
		AttendanceProcessHandler();
		break;
	case 'LoadingAttendance':
		LoadingAttendance();
		break;
	case 'LoadingSalaries':
		LoadingSalaries();
		break;
	case 'LoadingEmployees':
		LoadingEmployees();
		break;
	case 'AssignPayheadsToEmployee':
		AssignPayheadsToEmployee();
		break;
	case 'InsertUpdateHolidays':
		InsertUpdateHolidays();
		break;
	case 'GetHolidayByID':
		GetHolidayByID();
		break;
	case 'DeleteHolidayByID':
		DeleteHolidayByID();
		break;
	case 'LoadingHolidays':
		LoadingHolidays();
		break;
	case 'InsertUpdatePayheads':
		InsertUpdatePayheads();
		break;
	case 'GetPayheadByID':
		GetPayheadByID();
		break;
	case 'DeletePayheadByID':
		DeletePayheadByID();
		break;
	case 'LoadingPayheads':
		LoadingPayheads();
		break;
	case 'GetAllPayheadsExceptEmployeeHave':
		GetAllPayheadsExceptEmployeeHave();
		break;
	case 'GetEmployeePayheadsByID':
		GetEmployeePayheadsByID();
		break;
	case 'GetEmployeeByID':
		GetEmployeeByID();
		break;
	case 'DeleteEmployeeByID':
		DeleteEmployeeByID();
		break;
	case 'EditEmployeeDetailsByID':
		EditEmployeeDetailsByID();
		break;
	case 'GeneratePaySlip':
		GeneratePaySlip();
		break;
	case 'SendPaySlipByMail':
		SendPaySlipByMail();
		break;
	case 'EditProfileByID':
		EditProfileByID();
		break;
	case 'EditLoginDataByID':
		EditLoginDataByID();
		break;
	case 'LoadingAllLeaves':
		LoadingAllLeaves();
		break;
	case 'LoadingMyLeaves':
		LoadingMyLeaves();
		break;
	case 'ApplyLeaveToAdminApproval':
		ApplyLeaveToAdminApproval();
		break;
	case 'ApproveLeaveApplication':
		ApproveLeaveApplication();
		break;
	case 'RejectLeaveApplication':
		RejectLeaveApplication();
		break;
		case 'PunchInAttendance':
		PunchInAttendance();
		break;
	default:
		echo '404! Page Not Found.';
		break;
}

function LoginProcessHandler() {
	$result = array();
	global $db;

	$code = addslashes($_POST['code']);
    $password = addslashes($_POST['password']);
    if ( !empty($code) && !empty($password) ) {
	    $adminCheck = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "admin` WHERE `admin_code` = '$code' AND `admin_password` = '" . sha1($password) . "' LIMIT 0, 1");
	    if ( $adminCheck ) {
	        if ( mysqli_num_rows($adminCheck) == 1 ) {
	            $adminData = mysqli_fetch_assoc($adminCheck);
	            $_SESSION['Admin_ID'] = $adminData['admin_id'];
	            $_SESSION['Login_Type'] = 'admin';
	            $result['result'] = BASE_URL . 'attendance/';
			    		$result['code'] = 0;
	        } else {
	        	$empCheck = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "employees` WHERE `emp_code` = '$code' AND `emp_password` = '" . sha1($password) . "' LIMIT 0, 1");
			    if ( $empCheck ) {
			        if ( mysqli_num_rows($empCheck) == 1 ) {
			        	$empData = mysqli_fetch_assoc($empCheck);
			            $_SESSION['Admin_ID'] = $empData['emp_id'];
			            $_SESSION['Login_Type'] = 'emp';
			            $result['result'] = BASE_URL . 'profile/';
			    		$result['code'] = 0;
			        } else {
			        	$result['result'] = 'Invalid Login Details.';
			        	$result['code'] = 1;
			        }
			    } else {
			    	$result['result'] = 'Something went wrong, please try again.';
		    		$result['code'] = 2;
			    }
	        }
	    } else {
	    	$result['result'] = 'Something went wrong, please try again.';
		    $result['code'] = 2;
	    }
	} else {
		$result['result'] = 'Login Details should not be blank.';
		$result['code'] = 3;
	}

    echo json_encode($result);
}
function LoadingAttendance() {
    global $db;
    $requestData = $_REQUEST;
    $columns = array(
        0 => 'attendance_date',
        1 => 'emp_code',
        2 => 'first_name',
        3 => 'last_name',
        4 => 'punch_in_time',
        5 => 'punch_out_time',
        6 => 'description', // Combined description column
        7 => 'work_hours'
    );

    // SQL to get all attendance records
    $sql  = "SELECT `emp`.`emp_code`, `emp`.`first_name`, `emp`.`last_name`, 
              `att`.`attendance_date`, `att`.`action_name`, `att`.`action_time`, `att`.`emp_desc`
              FROM `" . DB_PREFIX . "employees` AS `emp` 
              JOIN `" . DB_PREFIX . "attendance` AS `att` ON `emp`.`emp_code` = `att`.`emp_code`";

    if (!empty($requestData['search']['value'])) {
        $sql .= " WHERE (`att`.`attendance_date` LIKE '" . $requestData['search']['value'] . "%' 
                OR CONCAT(TRIM(`emp`.`first_name`), ' ', TRIM(`emp`.`last_name`)) LIKE '" . $requestData['search']['value'] . "%')";
    }

    $query = mysqli_query($db, $sql);
    $totalData = mysqli_num_rows($query);
    $totalFiltered = $totalData;

    $sql .= " ORDER BY " . $columns[$requestData['order'][0]['column']] . " " . $requestData['order'][0]['dir'] . " 
              LIMIT " . $requestData['start'] . " ," . $requestData['length'] . "";

    $query = mysqli_query($db, $sql);

    $data = array();
    $i = 1 + $requestData['start'];

    // Array to hold attendance data
    $attendanceData = [];

    while ($row = mysqli_fetch_assoc($query)) {
        // Using attendance_date as the unique key
        $key = $row['emp_code'] . $row['attendance_date'];

        if (!isset($attendanceData[$key])) {
            $attendanceData[$key] = [
                'attendance_date' => date('d/m/Y', strtotime($row['attendance_date'])),
                'emp_code' => $row['emp_code'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'punch_in_time' => null,
                'punch_out_time' => null,
                'descriptions' => [],  // Array to hold descriptions
                'work_hours' => '0 hours',
            ];
        }

        // Assign punch in/out times and descriptions
        if ($row['action_name'] == 'punchin') {
            $attendanceData[$key]['punch_in_time'] = date('h:i A', strtotime($row['action_time']));
            $attendanceData[$key]['descriptions'][] = 'Punch In: ' . $row['emp_desc'];  // Add punch-in description
        } elseif ($row['action_name'] == 'punchout') {
            $attendanceData[$key]['punch_out_time'] = date('h:i A', strtotime($row['action_time']));
            $attendanceData[$key]['descriptions'][] = 'Punch Out: ' . $row['emp_desc'];  // Add punch-out description
        }
    }
 foreach ($attendanceData as &$item) {
        // Calculate work hours
        if ($item['punch_in_time'] && $item['punch_out_time']) {
            $punchIn = strtotime($item['punch_in_time']);
            $punchOut = strtotime($item['punch_out_time']);
            $workDuration = $punchOut - $punchIn; // Calculate the duration in seconds
            
            // Convert seconds to hours and minutes
            $hours = floor($workDuration / 3600);
            $minutes = floor(($workDuration % 3600) / 60);
            $item['work_hours'] = "{$hours} hours {$minutes} minutes";
        } else {
            $item['work_hours'] = '0 hours'; // Default value if no complete punch in/out
        }

      $nestedData = array();
$nestedData[] = $item['attendance_date'];
$nestedData[] = $item['emp_code'];
$nestedData[] = $item['first_name'] . ' ' . $item['last_name']; // Changed from link to plain text
$nestedData[] = $item['punch_in_time'] ? date('h:i A', strtotime($item['punch_in_time'])) : '-';
$nestedData[] = $item['punch_out_time'] ? date('h:i A', strtotime($item['punch_out_time'])) : '-';
$nestedData[] = !empty($item['descriptions']) ? implode('<br>', $item['descriptions']) : '-';  // Join descriptions
$nestedData[] = $item['work_hours'];
        
        // Use Bootstrap's trash icon
        $nestedData[] = '<button class="btn btn-danger small-button delete-btn" data-id="' . $item['emp_code'] . '" data-date="' . $item['attendance_date'] . '">
                            <i class="bi bi-trash"></i> <!-- Use bi-trash for Bootstrap Icons -->
                         </button>';

        $data[] = $nestedData;
    }

    $json_data = array(
        "draw"            => intval($requestData['draw']),
        "recordsTotal"    => intval($totalData),
        "recordsFiltered" => intval($totalFiltered),
        "data"            => $data
    );

    echo json_encode($json_data);
}
function LoadingSalaries() { 
    global $db;
    $requestData = $_REQUEST;
    
    if ($_SESSION['Login_Type'] == 'admin') {
        $columns = array(
            0 => 'emp_code',
            1 => 'first_name',
            2 => 'last_name',
            3 => 'pay_month',
            4 => 'earning_total',
            5 => 'net_salary'
        );

        // SQL to fetch data grouped by month without aggregation
        $sql = "SELECT emp.emp_code, emp.first_name, emp.last_name, salary.pay_month, salary.earning_total, salary.net_salary 
                FROM `" . DB_PREFIX . "salaries` AS `salary`
                JOIN `" . DB_PREFIX . "employees` AS `emp` ON emp.emp_code = salary.emp_code
                GROUP BY salary.pay_month, emp.emp_code
                ORDER BY salary.pay_month, emp.emp_code";

        $query = mysqli_query($db, $sql);
        $totalData = mysqli_num_rows($query);
        $totalFiltered = $totalData;

        // Building the filtered SQL with search
        $sql  = "SELECT emp.emp_code, emp.first_name, emp.last_name, salary.pay_month, salary.earning_total, salary.net_salary 
                 FROM `" . DB_PREFIX . "salaries` AS `salary`
                 JOIN `" . DB_PREFIX . "employees` AS `emp` ON emp.emp_code = salary.emp_code
                 WHERE 1=1
                 GROUP BY salary.pay_month, emp.emp_code";

        if (!empty($requestData['search']['value'])) {
            $searchValue = mysqli_real_escape_string($db, $requestData['search']['value']);
            $sql .= " AND (salary.emp_code LIKE '$searchValue%' ";
            $sql .= " OR CONCAT(TRIM(emp.first_name), ' ', TRIM(emp.last_name)) LIKE '$searchValue%' ";
            $sql .= " OR salary.pay_month LIKE '$searchValue%' ";
            $sql .= " OR salary.earning_total LIKE '$searchValue%' ";
            $sql .= " OR salary.net_salary LIKE '$searchValue%')";
        }

        // Add ordering and limit if needed
        // Example:
        // $sql .= " ORDER BY " . $columns[$requestData['order'][0]['column']] . " " . $requestData['order'][0]['dir'];
        // $sql .= " LIMIT " . intval($requestData['start']) . ", " . intval($requestData['length']);

        $query = mysqli_query($db, $sql);
        $totalFiltered = mysqli_num_rows($query);

        $data = array();
        $i = 1 + intval($requestData['start']);
        while ($row = mysqli_fetch_assoc($query)) {
            $nestedData = array();
            $nestedData[] = htmlspecialchars($row['emp_code']);
          $nestedData[] = htmlspecialchars($row["first_name"] . ' ' . $row["last_name"]); // Changed from link to plain text
            $nestedData[] = htmlspecialchars($row['pay_month']);
            $nestedData[] = number_format($row['earning_total'], 2, '.', ',');
            $nestedData[] = number_format($row['net_salary'], 2, '.', ',');
            $nestedData[] = '<a href="' . BASE_URL . 'salary_view.php?emp_code=' . $row['emp_code'] . '&pay_month=' . $row['pay_month'] . '" class="btn btn-success small-button">
                    <i class="bi bi-eye"></i> 
                </a>';
            $data[] = $nestedData;
            $i++;
        }

    } else {
        $columns = array(
            0 => 'pay_month',
            1 => 'earning_total',
            2 => 'net_salary'
        );

        $empData = GetDataByIDAndType($_SESSION['Admin_ID'], $_SESSION['Login_Type']);
        $emp_code = mysqli_real_escape_string($db, $empData['emp_code']);

        // SQL to fetch data grouped by month without aggregation for specific employee
        $sql = "SELECT emp.emp_code, emp.first_name, emp.last_name, salary.pay_month, salary.earning_total, salary.net_salary 
                FROM `" . DB_PREFIX . "salaries` AS `salary`
                JOIN `" . DB_PREFIX . "employees` AS `emp` ON emp.emp_code = salary.emp_code
                WHERE salary.emp_code = '$emp_code'
                GROUP BY salary.pay_month, emp.emp_code
                ORDER BY salary.pay_month";

        $query = mysqli_query($db, $sql);
        $totalData = mysqli_num_rows($query);
        $totalFiltered = $totalData;

        // Building the filtered SQL with search
        $sql  = "SELECT emp.emp_code, emp.first_name, emp.last_name, salary.pay_month, salary.earning_total, salary.net_salary 
                 FROM `" . DB_PREFIX . "salaries` AS `salary`
                 JOIN `" . DB_PREFIX . "employees` AS `emp` ON emp.emp_code = salary.emp_code
                 WHERE salary.emp_code = '$emp_code'
                 GROUP BY salary.pay_month, emp.emp_code";

        if (!empty($requestData['search']['value'])) {
            $searchValue = mysqli_real_escape_string($db, $requestData['search']['value']);
            $sql .= " AND (salary.emp_code LIKE '$searchValue%' ";
            $sql .= " OR salary.pay_month LIKE '$searchValue%' ";
            $sql .= " OR salary.earning_total LIKE '$searchValue%' ";
            $sql .= " OR salary.net_salary LIKE '$searchValue%')";
        }

        // Add ordering and limit if needed
        // Example:
        // $sql .= " ORDER BY " . $columns[$requestData['order'][0]['column']] . " " . $requestData['order'][0]['dir'];
        // $sql .= " LIMIT " . intval($requestData['start']) . ", " . intval($requestData['length']);

        $query = mysqli_query($db, $sql);
        $totalFiltered = mysqli_num_rows($query);

        $data = array();
        $i = 1 + intval($requestData['start']);
        while ($row = mysqli_fetch_assoc($query)) {
            $nestedData = array();
            $nestedData[] = htmlspecialchars($row['pay_month']);
            $nestedData[] = number_format($row['earning_total'], 2, '.', ',');
            $nestedData[] = number_format($row['net_salary'], 2, '.', ',');
            $nestedData[] = '<a href="' . BASE_URL . 'salary_view.php?emp_code=' . $emp_code . '&pay_month=' . $row['pay_month'] . '" class="btn btn-success small-button">
                    <i class="bi bi-eye"></i> 
                </a>';
            $data[] = $nestedData;
            $i++;
        }
    }

    $json_data = array(
        "draw"            => isset($requestData['draw']) ? intval($requestData['draw']) : 0,
        "recordsTotal"    => intval($totalData),
        "recordsFiltered" => intval($totalFiltered),
        "data"            => $data
    );

    echo json_encode($json_data);
}

function LoadingEmployees() {
	global $db;
	$requestData = $_REQUEST;
	$columns = array(
		0 => 'emp_code',
		1 => 'photo',
		2 => 'first_name',
		3 => 'last_name',
		4 => 'email',
		5 => 'mobile',
		6 => 'identity_doc',
		7 => 'identity_no',
		8 => 'dob',
		9 => 'joining_date',
		10 => 'blood_group',
		11 => 'emp_type'
	);

	$sql  = "SELECT `emp_id` ";
	$sql .= " FROM `" . DB_PREFIX . "employees`";
	$query = mysqli_query($db, $sql);
	$totalData = mysqli_num_rows($query);
	$totalFiltered = $totalData;

	$sql  = "SELECT *";
	$sql .= " FROM `" . DB_PREFIX . "employees` WHERE 1 = 1";
	if ( !empty($requestData['search']['value']) ) {
		$sql .= " AND (`emp_id` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR CONCAT(TRIM(`first_name`), ' ', TRIM(`last_name`)) LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `email` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `mobile` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR CONCAT(TRIM(`identity_doc`), ' - ', TRIM(`identity_no`)) LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `dob` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `joining_date` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `blood_group` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `emp_type` LIKE '" . $requestData['search']['value'] . "%')";
	}
	$query = mysqli_query($db, $sql);
	$totalFiltered = mysqli_num_rows($query);
	$sql .= " ORDER BY " . $columns[$requestData['order'][0]['column']] . " " . $requestData['order'][0]['dir'] . " LIMIT " . $requestData['start'] . " ," . $requestData['length'] . "";
	$query = mysqli_query($db, $sql);

	$data = array();
	$i = 1 + $requestData['start'];
	while ( $row = mysqli_fetch_assoc($query) ) {
		$nestedData = array();
		$nestedData[] = $row["emp_code"];
		$nestedData[] = '<img width="50" src="' . REG_URL . 'photos/' . $row["photo"] . '" alt="' . $row["emp_code"] . '" />';
		           $nestedData[] = '<a target="_blank" href="' . REG_URL . '../reports.php?emp_code=' . $row["emp_code"] . '">' . $row["first_name"] . ' ' . $row["last_name"] . '</a>';
		$nestedData[] = $row["email"];
		$nestedData[] = $row["mobile"];
		$nestedData[] = $row["identity_doc"] . ' - ' . $row["identity_no"];
		$nestedData[] = $row["dob"];
		$nestedData[] = $row["joining_date"];
		$nestedData[] = strtoupper($row["blood_group"]);
		$nestedData[] = ucwords($row["emp_type"]);
		$data[] = $nestedData;
		$i++;
	}
	$json_data = array(
		"draw"            => intval($requestData['draw']),
		"recordsTotal"    => intval($totalData),
		"recordsFiltered" => intval($totalFiltered),
		"data"            => $data
	);

	echo json_encode($json_data);
}
function AssignPayheadsToEmployee() {
    $result = array();
    global $db;

    $payheads = $_POST['selected_payheads'];
    $default_salary = $_POST['pay_amounts'];
    $emp_code = $_POST['empcode'];
    $pay_month = date('F d, Y', strtotime('now'));

    // Check if the employee already has payheads assigned
    $checkSQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "pay_structure` WHERE `emp_code` = '$emp_code'");
    if ($checkSQL) {
        if (!empty($payheads) && !empty($emp_code)) {
            // Delete old entries if reassigning
            mysqli_query($db, "DELETE FROM `" . DB_PREFIX . "pay_structure` WHERE `emp_code` = '$emp_code'");

            // Assign new payheads
            $total_earnings = 0;
            $total_deductions = 0;

            foreach ($payheads as $payhead) {
                $salary_amount = !empty($default_salary[$payhead]) ? $default_salary[$payhead] : 0;

                // Fetch payhead details
                $payhead_query = mysqli_query($db, "SELECT payhead_name, payhead_type FROM `" . DB_PREFIX . "payheads` WHERE payhead_id = '$payhead'");
                if ($payhead_row = mysqli_fetch_assoc($payhead_query)) {
                    $payhead_name = $payhead_row['payhead_name'];
                    $payhead_type = $payhead_row['payhead_type'];

                    // Insert into pay_structure
                    mysqli_query($db, "INSERT INTO `" . DB_PREFIX . "pay_structure`(`emp_code`, `payhead_id`, `default_salary`) VALUES ('$emp_code', $payhead, $salary_amount)");

                    // Calculate total salary based on type
                    if ($payhead_type == 'earnings') {
                        $total_earnings += $salary_amount;
                    } elseif ($payhead_type == 'deductions') {
                        $total_deductions += $salary_amount;
                    }

                    // Insert into salaries table
                    mysqli_query($db, "INSERT INTO `" . DB_PREFIX . "salaries`(`emp_code`, `payhead_name`, `pay_amount`, `pay_type`, `pay_month`, `generate_date`) VALUES ('$emp_code', '$payhead_name', '$salary_amount', '$payhead_type', '$pay_month', NOW())");
                }
            }

            // Calculate final totals
            $net_salary = $total_earnings - $total_deductions;

            // Update the summary in the salaries table
            $existingSalaryCheck = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "salaries` WHERE `emp_code` = '$emp_code' AND `pay_month` = '$pay_month'");
            if (mysqli_num_rows($existingSalaryCheck) == 0) {
                // Insert new summary record
                mysqli_query($db, "INSERT INTO `" . DB_PREFIX . "salaries`(`emp_code`, `pay_month`, `earning_total`, `deduction_total`, `net_salary`) VALUES ('$emp_code', '$pay_month', $total_earnings, $total_deductions, $net_salary)");
            } else {
                // Update existing summary record
                mysqli_query($db, "UPDATE `" . DB_PREFIX . "salaries` SET `earning_total` = $total_earnings, `deduction_total` = $total_deductions, `net_salary` = $net_salary WHERE `emp_code` = '$emp_code' AND `pay_month` = '$pay_month'");
            }

            $result['result'] = 'Payheads and salary successfully assigned to employee.';
            $result['code'] = 0;
        } else {
            $result['result'] = 'Please select payheads and employee to assign.';
            $result['code'] = 2;
        }
    } else {
        $result['result'] = 'Something went wrong, please try again.';
        $result['code'] = 1;
    }

    echo json_encode($result);
}


function InsertUpdateHolidays() {
	$result = array();
	global $db;

	$holiday_title = stripslashes($_POST['holiday_title']);
    $holiday_desc = stripslashes($_POST['holiday_desc']);
    $holiday_date = stripslashes($_POST['holiday_date']);
    $holiday_type = stripslashes($_POST['holiday_type']);
    if ( !empty($holiday_title) && !empty($holiday_desc) && !empty($holiday_date) && !empty($holiday_type) ) {
	    if ( !empty($_POST['holiday_id']) ) {
	    	$holiday_id = addslashes($_POST['holiday_id']);
	    	$updateHoliday = mysqli_query($db, "UPDATE `" . DB_PREFIX . "holidays` SET `holiday_title` = '$holiday_title', `holiday_desc` = '$holiday_desc', `holiday_date` = '$holiday_date', `holiday_type` = '$holiday_type' WHERE `holiday_id` = $holiday_id");
		    if ( $updateHoliday ) {
		        $result['result'] = 'Holiday record has been successfully updated.';
		        $result['code'] = 0;
		    } else {
		    	$result['result'] = 'Something went wrong, please try again.';
		    	$result['code'] = 1;
		    }
	    } else {
	    	$insertHoliday = mysqli_query($db, "INSERT INTO `" . DB_PREFIX . "holidays`(`holiday_title`, `holiday_desc`, `holiday_date`, `holiday_type`) VALUES ('$holiday_title', '$holiday_desc', '$holiday_date', '$holiday_type')");
		    if ( $insertHoliday ) {
		        $result['result'] = 'Holiday record has been successfully inserted.';
		        $result['code'] = 0;
		    } else {
		    	$result['result'] = 'Something went wrong, please try again.';
		    	$result['code'] = 1;
		    }
		}
	} else {
		$result['result'] = 'Holiday details should not be blank.';
		$result['code'] = 2;
	}

	echo json_encode($result);
}

function GetHolidayByID() {
	$result = array();
	global $db;

	$id = $_POST['id'];
	$holiSQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "holidays` WHERE `holiday_id` = $id LIMIT 0, 1");
	if ( $holiSQL ) {
		if ( mysqli_num_rows($holiSQL) == 1 ) {
			$result['result'] = mysqli_fetch_assoc($holiSQL);
			$result['code'] = 0;
		} else {
			$result['result'] = 'Holiday record is not found.';
			$result['code'] = 1;
		}
	} else {
		$result['result'] = 'Something went wrong, please try again.';
		$result['code'] = 2;
	}

	echo json_encode($result);
}

function DeleteHolidayByID() {
	$result = array();
	global $db;

	$id = $_POST['id'];
	$holiSQL = mysqli_query($db, "DELETE FROM `" . DB_PREFIX . "holidays` WHERE `holiday_id` = $id");
	if ( $holiSQL ) {
		$result['result'] = 'Holiday record is successfully deleted.';
		$result['code'] = 0;
	} else {
		$result['result'] = 'Something went wrong, please try again.';
		$result['code'] = 1;
	}

	echo json_encode($result);
}

function LoadingHolidays() {
	global $db;
	$requestData = $_REQUEST;
	$columns = array(
		0 => 'holiday_id',
		1 => 'holiday_title',
		2 => 'holiday_desc',
		3 => 'holiday_date',
		4 => 'holiday_type',
	);

	$sql  = "SELECT `holiday_id` ";
	$sql .= " FROM `" . DB_PREFIX . "holidays`";
	$query = mysqli_query($db, $sql);
	$totalData = mysqli_num_rows($query);
	$totalFiltered = $totalData;

	$sql  = "SELECT *";
	$sql .= " FROM `" . DB_PREFIX . "holidays` WHERE 1 = 1";
	if ( !empty($requestData['search']['value']) ) {
		$sql .= " AND (`holiday_id` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `holiday_title` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `holiday_desc` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `holiday_date` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `holiday_type` LIKE '" . $requestData['search']['value'] . "%')";
	}
	$query = mysqli_query($db, $sql);
	$totalFiltered = mysqli_num_rows($query);
	$sql .= " ORDER BY " . $columns[$requestData['order'][0]['column']] . " " . $requestData['order'][0]['dir'] . " LIMIT " . $requestData['start'] . " ," . $requestData['length'] . "";
	$query = mysqli_query($db, $sql);

	$data = array();
	$i = 1 + $requestData['start'];
	while ( $row = mysqli_fetch_assoc($query) ) {
		$nestedData = array();
		$nestedData[] = $row["holiday_id"];
		$nestedData[] = $row["holiday_title"];
		$nestedData[] = $row["holiday_desc"];
		$nestedData[] = date('d-m-Y', strtotime($row["holiday_date"]));
		if ( $row["holiday_type"] == 'compulsory' ) {
			$nestedData[] = '<span class="label label-success">' . ucwords($row["holiday_type"]) . '</span>';
		} else {
			$nestedData[] = '<span class="label label-danger">' . ucwords($row["holiday_type"]) . '</span>';
		}
		$data[] = $nestedData;
		$i++;
	}
	$json_data = array(
		"draw"            => intval($requestData['draw']),
		"recordsTotal"    => intval($totalData),
		"recordsFiltered" => intval($totalFiltered),
		"data"            => $data
	);

	echo json_encode($json_data);
}

function InsertUpdatePayheads() {
	$result = array();
	global $db;

	$payhead_name = stripslashes($_POST['payhead_name']);
    $payhead_desc = stripslashes($_POST['payhead_desc']);
    $payhead_type = stripslashes($_POST['payhead_type']);
    if ( !empty($payhead_name) && !empty($payhead_desc) && !empty($payhead_type) ) {
	    if ( !empty($_POST['payhead_id']) ) {
	    	$payhead_id = addslashes($_POST['payhead_id']);
	    	$updatePayhead = mysqli_query($db, "UPDATE `" . DB_PREFIX . "payheads` SET `payhead_name` = '$payhead_name', `payhead_desc` = '$payhead_desc', `payhead_type` = '$payhead_type' WHERE `payhead_id` = $payhead_id");
		    if ( $updatePayhead ) {
		        $result['result'] = 'Payhead record has been successfully updated.';
		        $result['code'] = 0;
		    } else {
		    	$result['result'] = 'Something went wrong, please try again.';
		    	$result['code'] = 1;
		    }
	    } else {
	    	$insertPayhead = mysqli_query($db, "INSERT INTO `" . DB_PREFIX . "payheads`(`payhead_name`, `payhead_desc`, `payhead_type`) VALUES ('$payhead_name', '$payhead_desc', '$payhead_type')");
		    if ( $insertPayhead ) {
		        $result['result'] = 'Payhead record has been successfully inserted.';
		        $result['code'] = 0;
		    } else {
		    	$result['result'] = 'Something went wrong, please try again.';
		    	$result['code'] = 1;
		    }
		}
	} else {
		$result['result'] = 'Payhead details should not be blank.';
		$result['code'] = 2;
	}

	echo json_encode($result);
}

function GetPayheadByID() {
	$result = array();
	global $db;

	$id = $_POST['id'];
	$holiSQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "payheads` WHERE `payhead_id` = $id LIMIT 0, 1");
	if ( $holiSQL ) {
		if ( mysqli_num_rows($holiSQL) == 1 ) {
			$result['result'] = mysqli_fetch_assoc($holiSQL);
			$result['code'] = 0;
		} else {
			$result['result'] = 'Payhead record is not found.';
			$result['code'] = 1;
		}
	} else {
		$result['result'] = 'Something went wrong, please try again.';
		$result['code'] = 2;
	}

	echo json_encode($result);
}

function DeletePayheadByID() {
	$result = array();
	global $db;

	$id = $_POST['id'];
	$holiSQL = mysqli_query($db, "DELETE FROM `" . DB_PREFIX . "payheads` WHERE `payhead_id` = $id");
	if ( $holiSQL ) {
		$result['result'] = 'Payhead record is successfully deleted.';
		$result['code'] = 0;
	} else {
		$result['result'] = 'Something went wrong, please try again.';
		$result['code'] = 1;
	}

	echo json_encode($result);
}
function LoadingPayheads() {
    global $db;
    $requestData = $_REQUEST;
    $columns = array(
        0 => 'payhead_id', // Ensure the ID is included
        1 => 'payhead_name',
        2 => 'payhead_desc',
        3 => 'payhead_type'
    );

    // Count total records
    $sql  = "SELECT `payhead_id` FROM `" . DB_PREFIX . "payheads`";
    $query = mysqli_query($db, $sql);
    $totalData = mysqli_num_rows($query);
    $totalFiltered = $totalData;

    // Prepare SQL for filtering
    $sql  = "SELECT * FROM `" . DB_PREFIX . "payheads` WHERE 1 = 1";
    if (!empty($requestData['search']['value'])) {
        $sql .= " AND (`payhead_id` LIKE '" . $requestData['search']['value'] . "%'";
        $sql .= " OR `payhead_name` LIKE '" . $requestData['search']['value'] . "%'";
        $sql .= " OR `payhead_desc` LIKE '" . $requestData['search']['value'] . "%'";
        $sql .= " OR `payhead_type` LIKE '" . $requestData['search']['value'] . "%')";
    }
    $query = mysqli_query($db, $sql);
    $totalFiltered = mysqli_num_rows($query);

    // Ordering and limiting results
    $sql .= " ORDER BY " . $columns[$requestData['order'][0]['column']] . " " . $requestData['order'][0]['dir'] . " LIMIT " . $requestData['start'] . " ," . $requestData['length'] . "";
    $query = mysqli_query($db, $sql);

    // Prepare data to return
    $data = array();
    $i = 0; // Row index for data array
    while ($row = mysqli_fetch_assoc($query)) {
        $nestedData = array();
        $nestedData[] = $row["payhead_id"]; // Correctly add payhead_id
        $nestedData[] = $row["payhead_name"];
        $nestedData[] = $row["payhead_desc"];
        
        // Adding formatting for payhead_type
        if ($row["payhead_type"] == 'earnings') {
            $nestedData[] = '<span class="label label-success">' . ucwords($row["payhead_type"]) . '</span>';
        } else {
            $nestedData[] = '<span class="label label-danger">' . ucwords($row["payhead_type"]) . '</span>';
        }
        // Add action buttons with payhead_id
        $nestedData[] = '<button class="btn btn-success btn-xs editPayheads" data-id="' . $row["payhead_id"] . '"><i class="fa fa-edit"></i></button>' .
                        '<button class="btn btn-danger btn-xs deletePayheads" data-id="' . $row["payhead_id"] . '"><i class="fa fa-trash"></i></button>';

        $data[] = $nestedData;
        $i++;
    }

    $json_data = array(
        "draw"            => intval($requestData['draw']),
        "recordsTotal"    => intval($totalData),
        "recordsFiltered" => intval($totalFiltered),
        "data"            => $data
    );

    echo json_encode($json_data);
}

function GetAllPayheadsExceptEmployeeHave() {
	$result = array();
	global $db;

	$emp_code = $_POST['emp_code'];
	$salarySQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "payheads` WHERE `payhead_id` NOT IN (SELECT `payhead_id` FROM `" . DB_PREFIX . "pay_structure` WHERE `emp_code` = '$emp_code')");
	if ( $salarySQL ) {
		if ( mysqli_num_rows($salarySQL) > 0 ) {
			while ( $data = mysqli_fetch_assoc($salarySQL) ) {
				$result['result'][] = $data;
			}
			$result['code'] = 0;
		} else {
			$result['result'] = 'Salary record is not found.';
			$result['code'] = 1;
		}
	} else {
		$result['result'] = 'Something went wrong, please try again.';
		$result['code'] = 2;
	}

	echo json_encode($result);
}

function GetEmployeePayheadsByID() {
	$result = array();
	global $db;

	$emp_code = $_POST['emp_code'];
	$salarySQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "pay_structure` AS `pay`, `" . DB_PREFIX . "payheads` AS `head` WHERE `head`.`payhead_id` = `pay`.`payhead_id` AND `pay`.`emp_code` = '$emp_code'");
	if ( $salarySQL ) {
		if ( mysqli_num_rows($salarySQL) > 0 ) {
			while ( $data = mysqli_fetch_assoc($salarySQL) ) {
				$result['result'][] = $data;
			}
			$result['code'] = 0;
		} else {
			$result['result'] = 'Salary record is not found.';
			$result['code'] = 1;
		}
	} else {
		$result['result'] = 'Something went wrong, please try again.';
		$result['code'] = 2;
	}

	echo json_encode($result);
}

function GetEmployeeByID() {
	$result = array();
	global $db;

	$emp_code = $_POST['emp_code'];
	$empSQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "employees` WHERE `emp_code` = '$emp_code' LIMIT 0, 1");
	if ( $empSQL ) {
		if ( mysqli_num_rows($empSQL) == 1 ) {
			$result['result'] = mysqli_fetch_assoc($empSQL);
			$result['code'] = 0;
		} else {
			$result['result'] = 'Employee record is not found.';
			$result['code'] = 1;
		}
	} else {
		$result['result'] = 'Something went wrong, please try again.';
		$result['code'] = 2;
	}

	echo json_encode($result);
}

function DeleteEmployeeByID() {
	$result = array();
	global $db;

	$emp_code = $_POST['emp_code'];
	$empSQL = mysqli_query($db, "DELETE FROM `" . DB_PREFIX . "employees` WHERE `emp_code` = '$emp_code'");
	if ( $empSQL ) {
		$result['result'] = 'Employee record is successfully deleted.';
		$result['code'] = 0;
	} else {
		$result['result'] = 'Something went wrong, please try again.';
		$result['code'] = 1;
	}

	echo json_encode($result);
}

function EditEmployeeDetailsByID() {
    $result = array();
    global $db;

    $emp_id = stripslashes($_POST['emp_id']);
    $first_name = stripslashes($_POST['first_name']);
    $last_name = stripslashes($_POST['last_name']);
    $dob = stripslashes($_POST['dob']);
    $gender = stripslashes($_POST['gender']);
    $merital_status = stripslashes($_POST['merital_status']);
    $nationality = stripslashes($_POST['nationality']);
    $address = stripslashes($_POST['address']);
    $city = stripslashes($_POST['city']);
    $state = stripslashes($_POST['state']);
    $country = stripslashes($_POST['country']);
    $email = stripslashes($_POST['email']);
    $mobile = stripslashes($_POST['mobile']);
    $telephone = stripslashes($_POST['telephone']);
    $identity_doc = stripslashes($_POST['identity_doc']);
    $identity_no = stripslashes($_POST['identity_no']);
    $emp_type = stripslashes($_POST['emp_type']);
    $joining_date = stripslashes($_POST['joining_date']);
    $department = stripslashes($_POST['department']);
    $bank_name = stripslashes($_POST['bank_name']);
    $account_no = stripslashes($_POST['account_no']);

    // Check mandatory fields
    if (!empty($first_name) && !empty($last_name) && !empty($dob) && !empty($gender) && !empty($merital_status) && !empty($nationality) && !empty($address) && !empty($city) && !empty($state) && !empty($country) && !empty($email) && !empty($mobile) && !empty($identity_doc) && !empty($identity_no) && !empty($emp_type) && !empty($joining_date) && !empty($department) && !empty($bank_name) && !empty($account_no)) {
        // Update the employee details
        $updateEmp = mysqli_query($db, "UPDATE `" . DB_PREFIX . "employees` SET `first_name` = '$first_name', `last_name` = '$last_name', `dob` = '$dob', `gender` = '$gender', `merital_status` = '$merital_status', `nationality` = '$nationality', `address` = '$address', `city` = '$city', `state` = '$state', `country` = '$country', `email` = '$email', `mobile` = '$mobile', `telephone` = '$telephone', `identity_doc` = '$identity_doc', `identity_no` = '$identity_no', `emp_type` = '$emp_type', `joining_date` = '$joining_date', `department` = '$department', `bank_name` = '$bank_name', `account_no` = '$account_no' WHERE `emp_id` = $emp_id");
        
        if ($updateEmp) {
            $result['result'] = 'Employee details have been successfully updated.';
            $result['code'] = 0;
        } else {
            $result['result'] = 'Something went wrong, please try again.';
            $result['code'] = 1;
        }
    } else {
        $result['result'] = 'All fields are mandatory except Telephone.';
        $result['code'] = 2;
    }

    echo json_encode($result);
}

function GeneratePaySlip() {
	global $mpdf, $db;
	$result = array();

	$emp_code = $_POST['emp_code'];
    $pay_month = $_POST['pay_month'];
    $earnings_heads = $_POST['earnings_heads'];
    $earnings_amounts = $_POST['earnings_amounts'];
    $deductions_heads = $_POST['deductions_heads'];
    $deductions_amounts = $_POST['deductions_amounts'];
    if ( !empty($emp_code) && !empty($pay_month) ) {
	    for ( $i = 0; $i < count($earnings_heads); $i++ ) {
	    	$checkSalSQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "salaries` WHERE `emp_code` = '$emp_code' AND `payhead_name` = '" . $earnings_heads[$i] . "' AND `pay_month` = '$pay_month' AND `pay_type` = 'earnings' LIMIT 0, 1");
	    	if ( $checkSalSQL ) {
	    		if ( mysqli_num_rows($checkSalSQL) == 0 ) {
	    			mysqli_query($db, "INSERT INTO `" . DB_PREFIX . "salaries`(`emp_code`, `payhead_name`, `pay_amount`, `earning_total`, `deduction_total`, `net_salary`, `pay_type`, `pay_month`, `generate_date`) VALUES ('$emp_code', '" . $earnings_heads[$i] . "', " . number_format($earnings_amounts[$i], 2, '.', '') . ", " . number_format(array_sum($earnings_amounts), 2, '.', '') . ", " . number_format(array_sum($deductions_amounts), 2, '.', '') . ", " . number_format((array_sum($earnings_amounts) - array_sum($deductions_amounts)), 2, '.', '') . ", 'earnings', '$pay_month', '" . date('Y-m-d H:i:s') . "')");
	    		}
	    	}
	    }
	    for ( $i = 0; $i < count($deductions_heads); $i++ ) {
	    	$checkSalSQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "salaries` WHERE `emp_code` = '$emp_code' AND `payhead_name` = '" . $deductions_heads[$i] . "' AND `pay_month` = '$pay_month' AND `pay_type` = 'deductions' LIMIT 0, 1");
	    	if ( $checkSalSQL ) {
	    		if ( mysqli_num_rows($checkSalSQL) == 0 ) {
	    			mysqli_query($db, "INSERT INTO `" . DB_PREFIX . "salaries`(`emp_code`, `payhead_name`, `pay_amount`, `earning_total`, `deduction_total`, `net_salary`, `pay_type`, `pay_month`, `generate_date`) VALUES ('$emp_code', '" . $deductions_heads[$i] . "', " . number_format($deductions_amounts[$i], 2, '.', '') . ", " . number_format(array_sum($earnings_amounts), 2, '.', '') . ", " . number_format(array_sum($deductions_amounts), 2, '.', '') . ", " . number_format((array_sum($earnings_amounts) - array_sum($deductions_amounts)), 2, '.', '') . ", 'deductions', '$pay_month', '" . date('Y-m-d H:i:s') . "')");
	    		}
	    	}
	    }
	    $empData = GetEmployeeDataByEmpCode($emp_code);
	    $empSalary = GetEmployeeSalaryByEmpCodeAndMonth($emp_code, $pay_month);
	    $empLeave = GetEmployeeLWPDataByEmpCodeAndMonth($emp_code, $pay_month);
	    $totalEarnings = 0;
		$totalDeductions = 0;
		$html = '<style>
		@page{margin:20px 20px;font-family:Arial;font-size:14px;}
    	.div_half{float:left;margin:0 0 30px 0;width:50%;}
    	.logo{width:250px;padding:0;}
    	.com_title{text-align:center;font-size:16px;margin:0;}
    	.reg_no{text-align:center;font-size:12px;margin:5px 0;}
    	.subject{text-align:center;font-size:20px;font-weight:bold;}
    	.emp_info{width:100%;margin:0 0 30px 0;}
    	.table{border:1px solid #ccc;margin:0 0 30px 0;}
    	.salary_info{width:100%;margin:0;}
    	.salary_info th,.salary_info td{border:1px solid #ccc;margin:0;padding:5px;vertical-align:middle;}
    	.net_payable{margin:0;color:#050;}
    	.in_word{text-align:right;font-size:12px;margin:5px 0;}
    	.signature{margin:0 0 30px 0;}
    	.signature strong{font-size:12px;padding:5px 0 0 0;border-top:1px solid #000;}
    	.com_info{font-size:12px;text-align:center;margin:0 0 30px 0;}
    	.noseal{text-align:center;font-size:11px;}
	    </style>';
	    $html .= '<div class="div_half">';
	    $html .= '<img class="logo" src="' . BASE_URL . 'dist/img/logo.png" alt="Wisely Online Services Private Limited" />';
	    $html .= '</div>';
	    $html .= '<div class="div_half">';
	    $html .= '<h2 class="com_title">Wisely Online Services Private Limited</h2>';
	    $html .= '<p class="reg_no">Registration Number: 063838, Bangalore</p>';
	    $html .= '</div>';

	    $html .= '<p class="subject">Salary Slip for ' . $pay_month . '</p>';

	    $html .= '<table class="emp_info">';
	    $html .= '<tr>';
	    $html .= '<td width="25%">Employee Code</td>';
	    $html .= '<td width="25%">: ' . strtoupper($emp_code) . '</td>';
	    $html .= '<td width="25%">Bank Name</td>';
	    $html .= '<td width="25%">: ' . ucwords($empData['bank_name']) . '</td>';
	    $html .= '</tr>';

	    $html .= '<tr>';
	    $html .= '<td>Employee Name</td>';
	    $html .= '<td>: ' . ucwords($empData['first_name'] . ' ' . $empData['last_name']) . '</td>';
	    $html .= '<td>Bank Account</td>';
	    $html .= '<td>: ' . $empData['account_no'] . '</td>';
	    $html .= '</tr>';

	   $html .= '<tr>';
	    $html .= '<td>Designation</td>';
	    $html .= '<td>: ' . ucwords($empData['designation']) . '</td>';
	    $html .= '<td>IFSC Code</td>';
	    $html .= '<td>: ' . strtoupper($empData['ifsc_code']) . '</td>';
	    $html .= '</tr>';

	    $html .= '<tr>';
	    $html .= '<td>Gender</td>';
	    $html .= '<td>: ' . ucwords($empData['gender']) . '</td>';
	    $html .= '<td>PAN</td>';
	    $html .= '<td>: ' . strtoupper($empData['pan_no']) . '</td>';
	    $html .= '</tr>';

	    $html .= '<tr>';
	    $html .= '<td>Location</td>';
	    $html .= '<td>: ' . ucwords($empData['city']) . '</td>';
	    $html .= '<td>PF Account</td>';
	    $html .= '<td>: ' . strtoupper($empData['pf_account']) . '</td>';
	    $html .= '</tr>';

	    $html .= '<tr>';
	    $html .= '<td>Department</td>';
	    $html .= '<td>: ' . ucwords($empData['department']) . '</td>';
	    $html .= '<td>Payable/Working Days</td>';
	    $html .= '<td>: ' . ($empLeave['workingDays'] - $empLeave['withoutPay']) . '/' . $empLeave['workingDays'] . ' Days</td>';
	    $html .= '</tr>';

	    $html .= '<tr>';
	    $html .= '<td>Date of Joining</td>';
	    $html .= '<td>: ' . date('d-m-Y', strtotime($empData['joining_date'])) . '</td>';
	    $html .= '<td>Taken/Remaining Leaves</td>';
	    $html .= '<td>: ' . $empLeave['payLeaves'] . '/' . ($empLeave['totalLeaves'] - $empLeave['payLeaves']) . ' Days</td>';
	    $html .= '</tr>';
	    $html .= '</table>';

		$html .= '<table class="table" cellspacing="0" cellpadding="0" width="100%">';
			$html .= '<thead>';
				$html .= '<tr>';
					$html .= '<th width="50%" valign="top">';
						$html .= '<table class="salary_info" cellspacing="0">';
							$html .= '<tr>';
								$html .= '<th align="left">Earnings</th>';
								$html .= '<th width="110" align="right">Amount (Rs.)</th>';
							$html .= '</tr>';
						$html .= '</table>';
					$html .= '</th>';
					$html .= '<th width="50%" valign="top">';
						$html .= '<table class="salary_info" cellspacing="0">';
							$html .= '<tr>';
								$html .= '<th align="left">Deductions</th>';
								$html .= '<th width="110" align="right">Amount (Rs.)</th>';
							$html .= '</tr>';
						$html .= '</table>';
					$html .= '</th>';
				$html .= '</tr>';
			$html .= '</thead>';

			if ( !empty($empSalary) ) {
				$html .= '<tr>';
					$html .= '<td width="50%" valign="top">';
						$html .= '<table class="salary_info" cellspacing="0">';
						foreach ( $empSalary as $salary ) {
							if ( $salary['pay_type'] == 'earnings' ) {
								$totalEarnings += $salary['pay_amount'];
								$html .= '<tr>';
									$html .= '<td align="left">';
										$html .= $salary['payhead_name'];
									$html .= '</td>';
									$html .= '<td width="110" align="right">';
										$html .= number_format($salary['pay_amount'], 2, '.', ',');
									$html .= '</td>';
								$html .= '</tr>';
							}
						}
						$html .= '</table>';
					$html .= '</td>';

					$html .= '<td width="50%" valign="top">';
						$html .= '<table class="salary_info" cellspacing="0">';
						foreach ( $empSalary as $salary ) {
							if ( $salary['pay_type'] == 'deductions' ) {
								$totalDeductions += $salary['pay_amount'];
								$html .= '<tr>';
									$html .= '<td align="left">';
										$html .= $salary['payhead_name'];
									$html .= '</td>';
									$html .= '<td width="110" align="right">';
										$html .= number_format($salary['pay_amount'], 2, '.', ',');
									$html .= '</td>';
								$html .= '</tr>';
							}
						}
						$html .= '</table>';
					$html .= '</td>';
				$html .= '</tr>';
			} else {
				$html .= '<tr>';
					$html .= '<td colspan="2" width="100%">No payheads are assigned for this employee</td>';
				$html .= '</tr>';
			}

			$html .= '<tr>';
				$html .= '<td width="50%" valign="top">';
					$html .= '<table class="salary_info" cellspacing="0">';
						$html .= '<tr>';
							$html .= '<td align="left">';
								$html .= '<strong>Total Earnings</strong>';
							$html .= '</td>';
							$html .= '<td width="110" align="right">';
								$html .= '<strong>' . number_format($totalEarnings, 2, '.', ',') . '</strong>';
							$html .= '</td>';
						$html .= '</tr>';
					$html .= '</table>';
				$html .= '</td>';
				$html .= '<td width="50%" valign="top">';
					$html .= '<table class="salary_info" cellspacing="0">';
						$html .= '<tr>';
							$html .= '<td align="left">';
								$html .= '<strong>Total Deductions</strong>';
							$html .= '</td>';
							$html .= '<td width="110" align="right">';
								$html .= '<strong>' . number_format($totalDeductions, 2, '.', ',') . '</strong>';
							$html .= '</td>';
						$html .= '</tr>';
					$html .= '</table>';
				$html .= '</td>';
			$html .= '</tr>';
		$html .= '</table>';

		$html .= '<div class="div_half">';
			$html .= '<h3 class="net_payable">';
				$html .= 'Net Salary Payable: Rs.' . number_format(($totalEarnings - $totalDeductions), 2, '.', ',');
			$html .= '</h3>';
		$html .= '</div>';
		$html .= '<div class="div_half">';
			$html .= '<h3 class="net_payable">';
				$html .= '<p class="in_word">(In words: ' . ucfirst(ConvertNumberToWords(($totalEarnings - $totalDeductions))) . ')</p>';
			$html .= '</h3>';
		$html .= '</div>';

		$html .= '<div class="signature">';
			$html .= '<table class="emp_info">';
				$html .= '<thead>';
					$html .= '<tr>';
						$html .= '<td>Date: ' . date('d-m-Y') . '</td>';
						$html .= '<th width="200">';
							$html .= '<img width="100" src="' . BASE_URL . 'dist/img/signature.png" alt="Nani Gopal Paul" /><br />';
							$html .= '<strong>Nani Gopal Paul, Director</strong>';
						$html .= '</th>';
					$html .= '</tr>';
				$html .= '</thead>';
			$html .= '</table>';
		$html .= '</div>';

		$html .= '<p class="com_info">';
			$html .= 'No. 15, 20th Main, 100 Feet Road,<br/>';
			$html .= '1st Phase, 2nd Stage, BTM Layout,<br/>';
			$html .= 'Bangalore, 560076,<br/>';
			$html .= 'Karnataka, INDIA<br/>';
			$html .= 'www.wisely.co';
		$html .= '</p>';
		$html .= '<p class="noseal"><small>Note: This is an electronically generated copy & therefore doesn’t require seal.</small></p>';

	    $mpdf->WriteHTML($html);
	    $pay_month = str_replace(', ', '-', $pay_month);
	    $payslip_path = dirname(dirname(__FILE__)) . '/payslips/';
	    if ( ! file_exists($payslip_path . $emp_code . '/') ) {
	    	mkdir($payslip_path . $emp_code, 0777);
	    }
	    if ( ! file_exists($payslip_path . $emp_code . '/' . $pay_month . '/') ) {
	    	mkdir($payslip_path . $emp_code . '/' . $pay_month, 0777);
	    }
		$mpdf->Output($payslip_path . $emp_code . '/' . $pay_month . '/' . $pay_month . '.pdf', 'F');
    	$result['code'] = 0;
    	$_SESSION['PaySlipMsg'] = $pay_month . ' PaySlip has been successfully generated for ' . $emp_code . '.';
    } else {
    	$result['code'] = 1;
    	$result['result'] = 'Something went wrong, please try again.';
    }

	echo json_encode($result);
}

function SendPaySlipByMail() {
	$result = array();
	global $db;

	$emp_code = $_POST['emp_code'];
	$month 	  = $_POST['month'];
	$empData  = GetEmployeeDataByEmpCode($emp_code);
	if ( $empData ) {
		$empName  = $empData['first_name'] . ' ' . $empData['last_name'];
		$empEmail = $empData['email'];
		$subject  = 'PaySlip for ' . $month;
		$message  = '<p>Hi ' . $empData['first_name'] . '</p>';
		$message .= '<p>Here is your attached Salary Slip for the period of ' . $month . '.</p>';
		$message .= '<hr/>';
		$message .= '<p>Thank You,<br/>Wisely Online Services Private Limited</p>';
		$attachment[0]['src'] = dirname(dirname(__FILE__)) . '/payslips/' . $emp_code . '/' . str_replace(', ', '-', $month) . '/' . str_replace(', ', '-', $month) . '.pdf';
		$attachment[0]['name'] = str_replace(', ', '-', $month);
		$send = Send_Mail($subject, $message, $empName, $empEmail, FALSE, FALSE, FALSE, FALSE, $attachment);
		if ( $send == 0 ) {
			$result['code'] = 0;
			$result['result'] = 'PaySlip for ' . $month . ' has been successfully send to ' . $empName;
		} else {
			$result['code'] = 1;
			$result['result'] = 'PaySlip is not send, please try again.';
		}
	} else {
		$result['code'] = 2;
		$result['result'] = 'No such employee found.';
	}

	echo json_encode($result);
}

function EditProfileByID() {
    $result = array();
    global $db;

    if ($_SESSION['Login_Type'] == 'admin') {
        // Admin profile update
        $admin_id = $_SESSION['Admin_ID'];
        $admin_name = addslashes($_POST['admin_name']);
        $admin_email = addslashes($_POST['admin_email']);

        if (!empty($admin_name) && !empty($admin_email)) {
            $editSQL = mysqli_query($db, "UPDATE `" . DB_PREFIX . "admin` SET `admin_name` = '$admin_name', `admin_email` = '$admin_email' WHERE `admin_id` = $admin_id");
            if ($editSQL) {
                $result['code'] = 0;
                $result['result'] = 'Profile data has been successfully updated.';
            } else {
                $result['code'] = 1;
                $result['result'] = 'Something went wrong, please try again.';
            }
        } else {
            $result['code'] = 2;
            $result['result'] = 'All fields are mandatory.';
        }
    } else {
        // Employee profile update
        $emp_id = stripslashes($_SESSION['Admin_ID']);
        $first_name = stripslashes($_POST['first_name']);
        $last_name = stripslashes($_POST['last_name']);
        $dob = stripslashes($_POST['dob']);
        $gender = stripslashes($_POST['gender']);
        $merital_status = stripslashes($_POST['merital_status']);
        $nationality = stripslashes($_POST['nationality']);
        $address = stripslashes($_POST['address']);
        $city = stripslashes($_POST['city']);
        $state = stripslashes($_POST['state']);
        $country = stripslashes($_POST['country']);
        $email = stripslashes($_POST['email']);
        $mobile = stripslashes($_POST['mobile']);
        $telephone = stripslashes($_POST['telephone']);
        $identity_doc = stripslashes($_POST['identity_doc']);
        $identity_no = stripslashes($_POST['identity_no']);
        $emp_type = stripslashes($_POST['emp_type']);
        $joining_date = stripslashes($_POST['joining_date']);
        $department = stripslashes($_POST['department']);
        $bank_name = stripslashes($_POST['bank_name']);  // New field
        $account_no = stripslashes($_POST['account_no']); // New field

        if (!empty($first_name) && !empty($last_name) && !empty($dob) && !empty($gender) && !empty($merital_status) && !empty($nationality) && !empty($address) && !empty($city) && !empty($state) && !empty($country) && !empty($email) && !empty($mobile) && !empty($identity_doc) && !empty($identity_no) && !empty($emp_type) && !empty($joining_date) && !empty($department) && !empty($bank_name) && !empty($account_no)) {
            $updateEmp = mysqli_query($db, "UPDATE `" . DB_PREFIX . "employees` SET `first_name` = '$first_name', `last_name` = '$last_name', `dob` = '$dob', `gender` = '$gender', `merital_status` = '$merital_status', `nationality` = '$nationality', `address` = '$address', `city` = '$city', `state` = '$state', `country` = '$country', `email` = '$email', `mobile` = '$mobile', `telephone` = '$telephone', `identity_doc` = '$identity_doc', `identity_no` = '$identity_no', `emp_type` = '$emp_type', `joining_date` = '$joining_date', `department` = '$department', `bank_name` = '$bank_name', `account_no` = '$account_no' WHERE `emp_id` = $emp_id");
            
            if ($updateEmp) {
                $result['result'] = 'Profile data has been successfully updated.';
                $result['code'] = 0;
            } else {
                $result['result'] = 'Something went wrong, please try again.';
                $result['code'] = 1;
            }
        } else {
            $result['result'] = 'All fields are mandatory except Telephone.';
            $result['code'] = 2;
        }
    }

    echo json_encode($result);
}


function EditLoginDataByID() {
	$result = array();
	global $db;

	if ( $_SESSION['Login_Type'] == 'admin' ) {
		$admin_id = $_SESSION['Admin_ID'];
		$admin_code = addslashes($_POST['admin_code']);
		$admin_password = addslashes($_POST['admin_password']);
		$admin_password_conf = addslashes($_POST['admin_password_conf']);
		if ( !empty($admin_code) && !empty($admin_password) && !empty($admin_password_conf) ) {
			if ( $admin_password == $admin_password_conf ) {
				$editSQL = mysqli_query($db, "UPDATE `" . DB_PREFIX . "admin` SET `admin_code` = '$admin_code', `admin_password` = '" . sha1($admin_password) . "' WHERE `admin_id` = $admin_id");
				if ( $editSQL ) {
					$result['code'] = 0;
					$result['result'] = 'Login data has been successfully updated.';
				} else {
					$result['code'] = 1;
					$result['result'] = 'Something went wrong, please try again.';
				}
			} else {
				$result['code'] = 2;
				$result['result'] = 'Confirm password does not match.';
			}
		} else {
			$result['code'] = 3;
			$result['result'] = 'All fields are mandatory.';
		}
	} else {
		$emp_id = $_SESSION['Admin_ID'];
		$old_password = addslashes($_POST['old_password']);
		$new_password = addslashes($_POST['new_password']);
		$password_conf = addslashes($_POST['password_conf']);
		if ( !empty($old_password) && !empty($new_password) && !empty($password_conf) ) {
			$checkPassSQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "employees` WHERE `emp_id` = $emp_id");
			if ( $checkPassSQL ) {
				if ( mysqli_num_rows($checkPassSQL) == 1 ) {
					$passData = mysqli_fetch_assoc($checkPassSQL);
					if ( sha1($old_password) == $passData['emp_password'] ) {
						if ( $new_password == $password_conf ) {
							$editSQL = mysqli_query($db, "UPDATE `" . DB_PREFIX . "employees` SET `emp_password` = '" . sha1($new_password) . "' WHERE `emp_id` = $emp_id");
							if ( $editSQL ) {
								$result['code'] = 0;
								$result['result'] = 'Password has been successfully updated.';
							} else {
								$result['code'] = 1;
								$result['result'] = 'Something went wrong, please try again.';
							}
						} else {
							$result['code'] = 2;
							$result['result'] = 'Confirm password does not match.';
						}
					} else {
						$result['code'] = 3;
						$result['result'] = 'Entered wrong existing password.';
					}
				} else {
					$result['code'] = 4;
					$result['result'] = 'No such employee found.';
				}
			} else {
				$result['code'] = 5;
				$result['result'] = 'Something went wrong, please try again.';
			}
		} else {
			$result['code'] = 6;
			$result['result'] = 'All fields are mandatory.';
		}
	}

	echo json_encode($result);
}

function LoadingAllLeaves() {
	global $db;
	$empData = GetDataByIDAndType($_SESSION['Admin_ID'], $_SESSION['Login_Type']);
	$requestData = $_REQUEST;
	$columns = array(
		0 => 'leave_id',
		1 => 'emp_code',
		3 => 'leave_dates',
		4 => 'leave_message',
		5 => 'leave_type',
		6 => 'leave_status'
	);

	$sql  = "SELECT `leave_id` ";
	$sql .= " FROM `" . DB_PREFIX . "leaves`";
	$query = mysqli_query($db, $sql);
	$totalData = mysqli_num_rows($query);
	$totalFiltered = $totalData;

	$sql  = "SELECT *";
	$sql .= " FROM `" . DB_PREFIX . "leaves` WHERE 1=1";
	if ( !empty($requestData['search']['value']) ) {
		$sql .= " AND (`leave_id` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `emp_code` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `leave_dates` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `leave_message` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `leave_type` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `leave_status` LIKE '" . $requestData['search']['value'] . "%')";
	}
	$query = mysqli_query($db, $sql);
	$totalFiltered = mysqli_num_rows($query);
	$sql .= " ORDER BY " . $columns[$requestData['order'][0]['column']] . " " . $requestData['order'][0]['dir'] . " LIMIT " . $requestData['start'] . " ," . $requestData['length'] . "";
	$query = mysqli_query($db, $sql);

	$data = array();
	$i = 1 + $requestData['start'];
	while ( $row = mysqli_fetch_assoc($query) ) {
		$nestedData = array();
		$nestedData[] = $row["leave_id"];
	$nestedData[] = htmlspecialchars($row["emp_code"]); // Changed from link to plain text
		$nestedData[] = $row["leave_type"];
		$nestedData[] = $row["leave_message"];
		$nestedData[] = $row["leave_dates"];
		if ( $row["leave_status"] == 'pending' ) {
			$nestedData[] = '<span class="label label-warning">' . ucwords($row["leave_status"]) . '</span>';
		} elseif ( $row['leave_status'] == 'approve' ) {
			$nestedData[] = '<span class="label label-success">' . ucwords($row["leave_status"]) . 'd</span>';
		} elseif ( $row['leave_status'] == 'reject' ) {
			$nestedData[] = '<span class="label label-danger">' . ucwords($row["leave_status"]) . 'ed</span>';
		}
		$data[] = $nestedData;
		$i++;
	}
	$json_data = array(
		"draw"            => intval($requestData['draw']),
		"recordsTotal"    => intval($totalData),
		"recordsFiltered" => intval($totalFiltered),
		"data"            => $data
	);

	echo json_encode($json_data);
}

function LoadingMyLeaves() {
	global $db;
	$empData = GetDataByIDAndType($_SESSION['Admin_ID'], $_SESSION['Login_Type']);
	$requestData = $_REQUEST;
	$columns = array(
		0 => 'leave_id',
		2 => 'leave_type',
		3 => 'leave_message',
		4 => 'leave_dates',
		5 => 'leave_status'
	);

	$sql  = "SELECT `leave_id` ";
	$sql .= " FROM `" . DB_PREFIX . "leaves` WHERE `emp_code` = '" . $empData['emp_code'] . "'";
	$query = mysqli_query($db, $sql);
	$totalData = mysqli_num_rows($query);
	$totalFiltered = $totalData;

	$sql  = "SELECT *";
	$sql .= " FROM `" . DB_PREFIX . "leaves` WHERE `emp_code` = '" . $empData['emp_code'] . "'";
	if ( !empty($requestData['search']['value']) ) {
		$sql .= " AND (`leave_id` LIKE '" . $requestData['search']['value'] . "%'";

		$sql .= " OR `leave_dates` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `leave_message` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `leave_type` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `leave_status` LIKE '" . $requestData['search']['value'] . "%')";
	}
	$query = mysqli_query($db, $sql);
	$totalFiltered = mysqli_num_rows($query);
	$sql .= " ORDER BY " . $columns[$requestData['order'][0]['column']] . " " . $requestData['order'][0]['dir'] . " LIMIT " . $requestData['start'] . " ," . $requestData['length'] . "";
	$query = mysqli_query($db, $sql);

	$data = array();
	$i = 1 + $requestData['start'];
	while ( $row = mysqli_fetch_assoc($query) ) {
		$nestedData = array();
		$nestedData[] = $row["leave_id"];
		$nestedData[] = $row["leave_type"];
		
		$nestedData[] = $row["leave_message"];
		$nestedData[] = $row["leave_dates"];
		if ( $row["leave_status"] == 'pending' ) {
			$nestedData[] = '<span class="label label-warning">' . ucwords($row["leave_status"]) . '</span>';
		} elseif ( $row['leave_status'] == 'approve' ) {
			$nestedData[] = '<span class="label label-success">' . ucwords($row["leave_status"]) . 'd</span>';
		} elseif ( $row['leave_status'] == 'reject' ) {
			$nestedData[] = '<span class="label label-danger">' . ucwords($row["leave_status"]) . 'ed</span>';
		}
		$data[] = $nestedData;
		$i++;
	}
	$json_data = array(
		"draw"            => intval($requestData['draw']),
		"recordsTotal"    => intval($totalData),
		"recordsFiltered" => intval($totalFiltered),
		"data"            => $data
	);

	echo json_encode($json_data);
}
function ApplyLeaveToAdminApproval() {
    $result = array();
    global $db;

    $adminData = GetAdminData(1);
    $empData   = GetDataByIDAndType($_SESSION['Admin_ID'], $_SESSION['Login_Type']);
    
    $leave_dates   = addslashes($_POST['leave_dates']);
    $leave_message = addslashes($_POST['leave_message']);
    $leave_type    = addslashes($_POST['leave_type']);
    
    if (!empty($leave_dates) && !empty($leave_message) && !empty($leave_type)) {
        $AppliedDates = '';
        $dates = explode(',', $leave_dates);  // Handle both single and multiple dates
        foreach ($dates as $date) {
            $checkLeaveSQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "leaves` WHERE `leave_dates` LIKE '%$date%' AND `emp_code` = '" . $empData['emp_code'] . "'");
            if ($checkLeaveSQL) {
                if (mysqli_num_rows($checkLeaveSQL) > 0) {
                    $AppliedDates .= $date . ', ';
                }
            }
        }

        if (empty($AppliedDates)) {
            // Proceed to insert leave application
            $leaveSQL = mysqli_query($db, "INSERT INTO `" . DB_PREFIX . "leaves` (`emp_code`, `leave_dates`, `leave_message`, `leave_type`, `apply_date`) VALUES('" . $empData['emp_code'] . "', '$leave_dates', '$leave_message', '$leave_type', '" . date('Y-m-d H:i:s') . "')");
            if ($leaveSQL) {
                $result['code'] = 0;
                $result['result'] = 'Leave Application has been successfully recorded.';
            } else {
                $result['code'] = 1;
                $result['result'] = 'Something went wrong, please try again.';
            }
        } else {
            // Remove the trailing comma and space from $AppliedDates
            $alreadyDates = substr($AppliedDates, 0, -2);
            $result['code'] = 2;
            $result['result'] = 'You have already applied for leave on ' . $alreadyDates . '. Please change the leave dates.';
        }
    } else {
        $result['code'] = 3;
        $result['result'] = 'All fields are mandatory.';
    }

    echo json_encode($result);
}
function ApproveLeaveApplication() {
    $result = array();
    global $db;

    $leaveId = $_REQUEST['id'];
    $update = mysqli_query($db, "UPDATE `" . DB_PREFIX . "leaves` SET `leave_status` = 'approve' WHERE `leave_id` = $leaveId");

    if ($update) {
        $result['code'] = 0;
        $result['result'] = 'Leave Application is successfully approved.';
    } else {
        $result['code'] = 1;
        $result['result'] = 'Something went wrong, please try again.';
    }

    echo json_encode($result);
}

function RejectLeaveApplication() {
    $result = array();
    global $db;

    $leaveId = $_REQUEST['id'];
    $update = mysqli_query($db, "UPDATE `" . DB_PREFIX . "leaves` SET `leave_status` = 'reject' WHERE `leave_id` = $leaveId");

    if ($update) {
        $result['code'] = 0;
        $result['result'] = 'Leave Application is successfully rejected.';
    } else {
        $result['code'] = 1;
        $result['result'] = 'Something went wrong, please try again.';
    }

    echo json_encode($result);
}

function DeleteSalaryRecord() {
    $result = array();
    global $db;

    // Retrieve `emp_code` and `pay_month` from request
    $emp_code = $_POST['emp_code'];
    $pay_month = $_POST['pay_month'];

    // Check if emp_code and pay_month are provided
    if (!empty($emp_code) && !empty($pay_month)) {
        // Prepare SQL query to delete the salary record
        $deleteSQL = mysqli_query($db, "DELETE FROM `" . DB_PREFIX . "salaries` WHERE `emp_code` = '$emp_code' AND `pay_month` = '$pay_month'");

        // Check if the query was successful
        if ($deleteSQL) {
            if (mysqli_affected_rows($db) > 0) {
                $result['code'] = 0;
                $result['result'] = 'Salary record successfully deleted.';
            } else {
                $result['code'] = 1;
                $result['result'] = 'No matching salary record found to delete.';
            }
        } else {
            $result['code'] = 2;
            $result['result'] = 'Error deleting the salary record, please try again.';
        }
    } else {
        $result['code'] = 3;
        $result['result'] = 'Invalid parameters, please provide both emp_code and pay_month.';
    }

    // Return the result as JSON
    echo json_encode($result);
}
