<?php
session_start();
include('includes/config.php');
if(strlen($_SESSION['odmsaid'])==0)
{   
    header('location:index.php');
}
else{
    $aid=$_SESSION['odmsaid'];
    $sql="SELECT * from  tbladmin where ID=:aid";
    $query = $dbh -> prepare($sql);
    $query->bindParam(':aid',$aid,PDO::PARAM_STR);
    $query->execute();
    $results=$query->fetchAll(PDO::FETCH_OBJ);
    $cnt=1;
    if($query->rowCount() > 0)
    { 
        foreach($results as $row)
        { 
            $fullname = $row->FirstName . " " . $row->LastName;
            $email = $row->Email;
            $phone = $row->Phone;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Profile</title>
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
                                    <h4 class="card-title">View Profile</h4>
                                    <form class="forms-sample" id="profileForm">
                                        <div class="form-group">
                                            <label for="fullname">Full Name</label>
                                            <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlentities($fullname);?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlentities($email);?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="phone">Phone</label>
                                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlentities($phone);?>" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary mr-2">Save Changes</button>
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
            let formChanged = false;
            const originalFormData = {
                fullname: $('#fullname').val(),
                email: $('#email').val(),
                phone: $('#phone').val()
            };

            // Track form changes
            $('#profileForm input').on('input', function() {
                formChanged = true;
            });

            // Handle form submission
            $('#profileForm').on('submit', function(e) {
                e.preventDefault();
                if (!formChanged) {
                    alert('No changes made to save.');
                    return;
                }

                $.ajax({
                    url: 'update_profile.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        const result = JSON.parse(response);
                        if (result.status === 'success') {
                            alert('Profile updated successfully!');
                            formChanged = false;
                            originalFormData.fullname = $('#fullname').val();
                            originalFormData.email = $('#email').val();
                            originalFormData.phone = $('#phone').val();
                        } else {
                            alert('Error: ' + result.message);
                        }
                    },
                    error: function() {
                        alert('An error occurred while updating the profile.');
                    }
                });
            });

            // Handle page leave confirmation
            window.addEventListener('beforeunload', function(e) {
                if (formChanged) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        });
    </script>
</body>
</html> 