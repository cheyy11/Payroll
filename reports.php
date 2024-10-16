<?php
require(dirname(__FILE__) . '/config.php');

$emp_code = $_GET['emp_code']; // Get the employee code from the URL

$sql = "SELECT * FROM `" . DB_PREFIX . "employees` WHERE `emp_code` = '$emp_code'";
$result = mysqli_query($db, $sql);
$employee = mysqli_fetch_assoc($result);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Details</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #e9ecef;
            font-family: 'Arial', sans-serif;
        }
        .container {
            margin-top: 50px;
            margin-bottom: 50px;
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #343a40;
            font-weight: bold;
        }
        .detail-card {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            padding: 30px;
            transition: transform 0.2s;
        }
        .detail-card:hover {
            transform: scale(1.02);
        }
        .detail-card img {
            max-width: 100%;
            border-radius: 5px;
            margin-top: 20px;
        }
        .detail {
            margin-bottom: 15px;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            font-size: 1.1em;
            color: #495057;
        }
        .detail:last-child {
            border-bottom: none;
        }
        .detail strong {
            color: #007bff;
        }
        .btn-custom {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            transition: background-color 0.3s;
        }
        .btn-custom:hover {
            background-color: #0056b3;
        }
        footer {
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
        }
    </style>
</head>
<body>

<div class="container">
    <?php if ($employee): ?>
        <div class="detail-card">
            <h2>Employee Details</h2>
            <div class="detail"><strong>Employee Code:</strong> <?= $employee['emp_code'] ?></div>
            <div class="detail"><strong>Password:</strong> <?= $employee['emp_password'] ?></div>
            <div class="detail"><strong>First Name:</strong> <?= $employee['first_name'] ?></div>
            <div class="detail"><strong>Last Name:</strong> <?= $employee['last_name'] ?></div>
            <div class="detail"><strong>Date of Birth:</strong> <?= $employee['dob'] ?></div>
            <div class="detail"><strong>Gender:</strong> <?= $employee['gender'] ?></div>
            <div class="detail"><strong>Marital Status:</strong> <?= $employee['merital_status'] ?></div>
            <div class="detail"><strong>Nationality:</strong> <?= $employee['nationality'] ?></div>
            <div class="detail"><strong>Address:</strong> <?= $employee['address'] ?></div>
            <div class="detail"><strong>City:</strong> <?= $employee['city'] ?></div>
            <div class="detail"><strong>State:</strong> <?= $employee['state'] ?></div>
            <div class="detail"><strong>Country:</strong> <?= $employee['country'] ?></div>
            <div class="detail"><strong>Email:</strong> <?= $employee['email'] ?></div>
            <div class="detail"><strong>Mobile:</strong> <?= $employee['mobile'] ?></div>
            <div class="detail"><strong>Telephone:</strong> <?= $employee['telephone'] ?></div>
            <div class="detail"><strong>Identity Document:</strong> <?= $employee['identity_doc'] ?></div>
            <div class="detail"><strong>Identity Number:</strong> <?= $employee['identity_no'] ?></div>
            <div class="detail"><strong>Employee Type:</strong> <?= $employee['emp_type'] ?></div>
            <div class="detail"><strong>Joining Date:</strong> <?= $employee['joining_date'] ?></div>
            <div class="detail"><strong>Department:</strong> <?= $employee['department'] ?></div>
            <div class="detail"><strong>Bank Name:</strong> <?= $employee['bank_name'] ?></div>
            <div class="detail"><strong>Account Number:</strong> <?= $employee['account_no'] ?></div>
            <div class="detail"><strong>Created:</strong> <?= $employee['created'] ?></div>


      
        </div>
    <?php else: ?>
        <h2>Employee not found.</h2>
    <?php endif; ?>
</div>

<footer>
    <p>&copy; <?= date('Y') ?>All Rights Reserved.</p>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
