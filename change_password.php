<?php
session_start();
include('includes/config.php');
if(strlen($_SESSION['odmsaid'])==0) {   
    header('location:index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container-scroller">
        <?php include_once('includes/header.php');?>
        <div class="container-fluid page-body-wrapper">
            <?php include_once('includes/sidebar.php');?>
            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="row">
                        <div class="col-12 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Change Password</h4>
                                    <form class="forms-sample" id="passwordForm">
                                        <div class="form-group">
                                            <label for="currentPassword">Current Password</label>
                                            <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="newPassword">New Password</label>
                                            <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                                            <small class="text-muted">Password must be at least 8 characters long and contain at least one number, one uppercase letter, and one special character.</small>
                                        </div>
                                        <div class="form-group">
                                            <label for="confirmPassword">Confirm New Password</label>
                                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary mr-2">Change Password</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="assets/js/off-canvas.js"></script>
    <script src="assets/js/hoverable-collapse.js"></script>
    <script src="assets/js/misc.js"></script>
    <script>
        $(document).ready(function() {
            $('#passwordForm').on('submit', function(e) {
                e.preventDefault();
                
                const currentPassword = $('#currentPassword').val();
                const newPassword = $('#newPassword').val();
                const confirmPassword = $('#confirmPassword').val();

                // Password validation
                const passwordRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
                
                if (!passwordRegex.test(newPassword)) {
                    alert('New password must be at least 8 characters long and contain at least one number, one uppercase letter, and one special character.');
                    return;
                }

                if (newPassword !== confirmPassword) {
                    alert('New passwords do not match.');
                    return;
                }

                $.ajax({
                    url: 'update_password.php',
                    type: 'POST',
                    data: {
                        currentPassword: currentPassword,
                        newPassword: newPassword
                    },
                    success: function(response) {
                        const result = JSON.parse(response);
                        if (result.status === 'success') {
                            alert('Password changed successfully!');
                            $('#passwordForm')[0].reset();
                        } else {
                            alert('Error: ' + result.message);
                        }
                    },
                    error: function() {
                        alert('An error occurred while changing the password.');
                    }
                });
            });
        });
    </script>
</body>
</html> 