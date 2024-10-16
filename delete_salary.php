<?php
include('db_connection.php'); // Adjust this path to your actual database connection file

if (isset($_POST['emp_code']) && isset($_POST['pay_month'])) {
    $emp_code = $_POST['emp_code'];
    $pay_month = $_POST['pay_month'];

    $query = "DELETE FROM `" . DB_PREFIX . "salaries` WHERE `emp_code` = '$emp_code' AND `pay_month` = '$pay_month'";
    if (mysqli_query($db, $query)) {
        echo json_encode(array('status' => 'success'));
    } else {
        echo json_encode(array('status' => 'error', 'message' => mysqli_error($db)));
    }
} else {
    echo json_encode(array('status' => 'invalid'));
}
