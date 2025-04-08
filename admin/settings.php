<?php
session_start();
include('../includes/config.php');

if(strlen($_SESSION['odmsaid'])==0) {   
    header('location:../index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - EventPro Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="light-mode">
    <!-- Sidebar -->
    <div class="sidebar">
        <?php include 'includes/sidebar.php'; ?>
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

        <!-- Settings Content -->
        <div class="content-wrapper">
            <div class="content-header">
                <h1>Settings</h1>
            </div>

            <div class="settings-grid">
                <a href="view_profile.php" class="settings-card">
                    <div class="settings-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="settings-content">
                        <h3>View Profile</h3>
                        <p>View and edit your profile information</p>
                    </div>
                    <i class="fas fa-chevron-right settings-arrow"></i>
                </a>

                <a href="change_password.php" class="settings-card">
                    <div class="settings-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <div class="settings-content">
                        <h3>Change Password</h3>
                        <p>Update your account password</p>
                    </div>
                    <i class="fas fa-chevron-right settings-arrow"></i>
                </a>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>
</html> 