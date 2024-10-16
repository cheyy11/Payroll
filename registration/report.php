<?php
require(dirname(__FILE__) . '/config.php');

// Check if emp_id is passed in the URL
if (isset($_GET['emp_id'])) {
    $emp_id = intval($_GET['emp_id']); // Sanitize the input

    // Fetch employee data based on emp_id
    $query = "SELECT * FROM `" . DB_PREFIX . "employees` WHERE `emp_id` = $emp_id";
    $result = mysqli_query($db, $query);

    if (!$result) {
        die('Query failed: ' . mysqli_error($db));
    }

    // Fetch the employee details
    $employee = mysqli_fetch_assoc($result);
} else {
    die('No employee ID specified.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Information</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f9f9f9;
            font-family: Arial, sans-serif;
        }

        .box {
            margin: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .box-header {
            background-color: #007bff;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 24px;
            border-bottom: 5px solid #0056b3;
        }

        .box-body {
            padding: 20px;
            background: white;
        }

        .divider {
            border-bottom: 2px solid #007bff;
            margin: 20px 0;
        }

        .row {
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .row:hover {
            background-color: #e9ecef;
            transform: scale(1.01);
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
        }

        .img-responsive {
            border-radius: 50%;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .img-responsive:hover {
            transform: scale(1.05);
        }
/* Custom styles for the navbar */
		.navbar {
			padding: 15px; /* Increase padding */
			font-size: 30px; /* Increase font size */
		}
		.navbar-nav .nav-link {
			padding: 10px 15px; /* Increase padding for links */
		}
        @media print {
            button {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="box">
	<!-- Navbar -->
	<nav class="navbar navbar-expand-lg navbar-light bg-light">
		<div class="collapse navbar-collapse" id="navbarNav">
					<a class="nav-link" href="<?php echo BASE_URL; ?>../index.php" title="Login">
						<i class="fas fa-sign-in-alt"></i> Login
					</a>
		</div>
	</nav>

    <div class="box-header with-border">
        <h3 class="box-title">Employee Information</h3>
    </div>

    <div class="box-body">
        <?php echo isset($_SESSION['success']) ? $_SESSION['success'] : ''; ?>
          <div class="row">
            <label class="col-sm-3">Username</label>
             <div class="col-sm-9">
                <p><?php echo ucwords($employee['emp_code']); ?></p>
            </div>
        </div>
        <div class="row">
            <label class="col-sm-3">Full Name</label>
            <div class="col-sm-9">
                <p><?php echo ucwords($employee['first_name']); ?> <?php echo ucwords($employee['last_name']); ?></p>
            </div>
        </div>
        <div class="row">
            <label class="col-sm-3">DOB</label>
            <div class="col-sm-9">
                <p><?php echo $employee['dob']; ?></p>
            </div>
        </div>
        <div class="row">
            <label class="col-sm-3">Gender</label>
            <div class="col-sm-9">
                <p><?php echo ucwords($employee['gender']); ?></p>
            </div>
        </div>
        <div class="row">
            <label class="col-sm-3">Marital Status</label>
            <div class="col-sm-9">
                <p><?php echo ucwords($employee['merital_status']); ?></p>
            </div>
        </div>
        <div class="row">
            <label class="col-sm-3">Nationality</label>
            <div class="col-sm-9">
                <p><?php echo ucwords($employee['nationality']); ?></p>
            </div>
        </div>
        <div class="divider"></div>
        <div class="row">
            <label class="col-sm-3">Address</label>
            <div class="col-sm-9">
                <p><?php echo ucwords($employee['address']); ?></p>
            </div>
        </div>
        <div class="row">
            <label class="col-sm-3">City</label>
            <div class="col-sm-9">
                <p><?php echo ucwords($employee['city']); ?></p>
            </div>
        </div>
        <div class="row">
            <label class="col-sm-3">State</label>
            <div class="col-sm-9">
                <p><?php echo ucwords($employee['state']); ?></p>
            </div>
        </div>
        <div class="row">
            <label class="col-sm-3">Country</label>
            <div class="col-sm-9">
                <p><?php echo ucwords($employee['country']); ?></p>
            </div>
        </div>
        <div class="row">
            <label class="col-sm-3">Email Id</label>
            <div class="col-sm-9">
                <p><?php echo $employee['email']; ?></p>
            </div>
        </div>
        <div class="row">
            <label class="col-sm-3">Mobile No</label>
            <div class="col-sm-9">
                <p><?php echo $employee['mobile']; ?></p>
            </div>
        </div>
        <div class="row">
            <label class="col-sm-3">Telephone No</label>
            <div class="col-sm-9">
                <p><?php echo $employee['telephone'] ? $employee['telephone'] : 'N/A'; ?></p>
            </div>
        </div>
        <div class="row">
            <label class="col-sm-3">Identification</label>
            <div class="col-sm-9">
                <p><?php echo ucwords($employee['identity_doc']); ?></p>
            </div>
        </div>
        <div class="row">
            <label class="col-sm-3">Id No</label>
            <div class="col-sm-9">
                <p><?php echo $employee['identity_no']; ?></p>
            </div>
        </div>
        <div class="divider"></div>
        <div class="row">
            <label class="col-sm-3">Emp. Type</label>
            <div class="col-sm-9">
                <p><?php echo ucwords($employee['emp_type']); ?></p>
            </div>
        </div>
        <div class="row">
            <label class="col-sm-3">Joining Date</label>
            <div class="col-sm-9">
                <p><?php echo $employee['joining_date']; ?></p>
            </div>
        </div>

<script src="<?php echo BASE_URL; ?>plugins/jQuery/jquery-2.2.3.min.js"></script>
<script src="<?php echo BASE_URL; ?>bootstrap/js/bootstrap.min.js"></script>
</body>
</html>

<?php unset($_SESSION['success']); ?>
