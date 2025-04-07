<?php
session_start();
include('../includes/config.php');

if(strlen($_SESSION['odmsaid'])==0) {   
    header('location:../index.php');
}

// Handle POST request for profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $adminId = $_SESSION['odmsaid'];

    try {
        // Split full name into first and last name
        $nameParts = explode(' ', $fullName, 2);
        $firstName = $nameParts[0];
        $lastName = isset($nameParts[1]) ? $nameParts[1] : '';

        // Update profile
        $sql = "UPDATE tbladmin SET FirstName = :firstName, LastName = :lastName, Email = :email, Phone = :phone WHERE ID = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':firstName', $firstName, PDO::PARAM_STR);
        $query->bindParam(':lastName', $lastName, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':phone', $phone, PDO::PARAM_STR);
        $query->bindParam(':id', $adminId, PDO::PARAM_INT);
        $query->execute();

        echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully']);
        exit();
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        exit();
    }
}

// Fetch current profile data
try {
    $sql = "SELECT FirstName, LastName, Email, Phone FROM tbladmin WHERE ID = :id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id', $_SESSION['odmsaid'], PDO::PARAM_INT);
    $query->execute();
    $profile = $query->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching profile data: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile - EventPro Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="light-mode">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-logo">
            <img src="../assets/images/progress-kit-logo.png" alt="Progress Kit">
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li>
                    <a href="dashboard.php">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="users.php">
                        <i class="fas fa-users"></i>
                        <span>User Management</span>
                    </a>
                </li>
                <li>
                    <a href="events.php">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Event Management</span>
                    </a>
                </li>
                <li>
                    <a href="registrations.php">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Registrations</span>
                    </a>
                </li>
                <li>
                    <a href="announcements.php">
                        <i class="fas fa-bullhorn"></i>
                        <span>Announcements</span>
                    </a>
                </li>
                <li>
                    <a href="reports.php">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports & Analytics</span>
                    </a>
                </li>
                <li class="active">
                    <a href="settings.php">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="sidebar-footer">
            <button id="theme-toggle" class="btn btn-icon">
                <i class="fas fa-moon"></i>
                <span>Dark Mode</span>
            </button>
            <a href="../logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <header class="top-nav">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search...">
            </div>
            <div class="user-info">
                <div class="notifications">
                    <i class="fas fa-bell"></i>
                    <span class="badge">3</span>
                </div>
                <div class="user-profile">
                    <img src="../assets/images/admin-avatar.jpg" alt="Admin">
                    <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                </div>
            </div>
        </header>

        <!-- Profile Content -->
        <div class="content-wrapper">
            <div class="content-header">
                <h1>Update Profile</h1>
                <a href="settings.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Settings
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form id="profileForm" class="needs-validation" novalidate>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="fullName" class="form-label">Full Name *</label>
                                <input type="text" class="form-control form-control-lg" id="fullName" name="fullName" 
                                       value="<?php echo htmlspecialchars($profile['FirstName'] . ' ' . $profile['LastName']); ?>" required>
                                <div class="invalid-feedback">Please enter your full name</div>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control form-control-lg" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($profile['Email']); ?>" required>
                                <div class="invalid-feedback">Please enter a valid email address</div>
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control form-control-lg" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($profile['Phone']); ?>">
                            </div>
                        </div>

                        <div class="form-actions mt-4">
                            <div class="d-flex justify-content-end gap-3">
                                <a href="settings.php" class="btn btn-lg btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-lg btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
        $(document).ready(function() {
            let formChanged = false;
            const originalFormData = $('#profileForm').serialize();

            // Track form changes
            $('#profileForm input').on('change', function() {
                formChanged = true;
            });

            // Handle form submission
            $('#profileForm').on('submit', function(e) {
                e.preventDefault();
                
                if (!this.checkValidity()) {
                    e.stopPropagation();
                    $(this).addClass('was-validated');
                    return;
                }

                const formData = new FormData(this);
                
                $.ajax({
                    url: 'update_profile.php',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        try {
                            const data = typeof response === 'string' ? JSON.parse(response) : response;
                            if (data.status === 'success') {
                                alert('Profile updated successfully');
                                formChanged = false;
                                window.location.href = 'settings.php';
                            } else {
                                alert('Error: ' + data.message);
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            alert('Error processing server response');
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Error: ' + error);
                    }
                });
            });

            // Warn before leaving if changes are unsaved
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