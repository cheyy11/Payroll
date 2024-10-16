<?php require_once(dirname(__FILE__) . '/config.php'); 
if (!isset($_SESSION['Admin_ID']) || $_SESSION['Login_Type'] != 'admin') {
    header('location:' . BASE_URL);
} ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Attendance - Payroll</title>

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>plugins/datatables/dataTables.bootstrap.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>plugins/datatables/jquery.dataTables_themeroller.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/AdminLTE.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/skins/_all-skins.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">


    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    	<style type="text/css">
    		.small-button {
    padding: 0.25rem 0.5rem; /* Adjust padding as needed */
    font-size: 1.2rem; /* Adjust font size if needed */
}

    	</style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        <?php require_once(dirname(__FILE__) . '/partials/topnav.php'); ?>
        <?php require_once(dirname(__FILE__) . '/partials/sidenav.php'); ?>

        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    Attendance
                </h1>
                <ol class="breadcrumb">
                    <li><a href="<?php echo BASE_URL; ?>"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Attendance</li>
                </ol>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box">
                            <div class="box-header">
                                <h3 class="box-title">Employee Attendance</h3>
                            </div>
                            <div class="box-body">
                                <div class="table-responsive">
                                    <table id="attendance" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>DATE</th>
                                                <th>EMP CODE</th>
                                                <th>NAME</th>
                                                <th colspan="2">ACTION TIME</th>
                                                <th>DESCRIPTION</th>
                                                <th>WORK HOURS</th>
                                                 <th>WORK HOURS</th>
                                            </tr>
                                            <tr>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th>PUNCH-IN</th>
                                                <th>PUNCH-OUT</th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                      
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <footer class="main-footer">
            <strong> &copy; <?php echo date("Y");?> Payroll Management System </strong> 
        </footer>
    </div>

    <script src="<?php echo BASE_URL; ?>plugins/jQuery/jquery-2.2.3.min.js"></script>
    <script src="<?php echo BASE_URL; ?>bootstrap/js/bootstrap.min.js"></script>
    <script src="<?php echo BASE_URL; ?>plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="<?php echo BASE_URL; ?>plugins/datatables/dataTables.bootstrap.min.js"></script>
    <script src="<?php echo BASE_URL; ?>plugins/bootstrap-notify/bootstrap-notify.min.js"></script>
    <script src="<?php echo BASE_URL; ?>dist/js/app.min.js"></script>
    <script type="text/javascript">var baseurl = '<?php echo BASE_URL; ?>';</script>
    <script src="<?php echo BASE_URL; ?>dist/js/script.js?rand=<?php echo rand(); ?>"></script>
    <script type="text/javascript">
    	$('#attendance').on('click', '.delete-btn', function() {
    var emp_code = $(this).data('id'); // Employee code

    console.log(emp_code); // Debugging line

    if (confirm('Are you sure you want to delete attendance records for this employee?')) {
        $.ajax({
            url: baseurl + 'delete_attendance.php',
            type: 'POST',
            data: {
                emp_code: emp_code // Only sending emp_code
            },
            success: function(response) {
                console.log(response); // Debugging line
                var result = JSON.parse(response);
                if (result.status === 'success') {
                    $('#attendance').DataTable().ajax.reload(); // Reload the table
                    $.notify({
                        message: 'Attendance records deleted successfully'
                    }, {
                        type: 'success'
                    });
                } else {
                    $.notify({
                        message: 'Failed to delete records: ' + result.message
                    }, {
                        type: 'danger'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText); // Log any AJAX errors to console
            }
        });
    }
});

    </script>
</body>
</html>
