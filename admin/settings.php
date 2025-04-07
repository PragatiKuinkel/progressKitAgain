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
    <style>
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .settings-card {
            display: flex;
            align-items: center;
            padding: 20px;
            background: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-decoration: none;
            color: var(--text-color);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .settings-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .settings-icon {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            margin-right: 20px;
            font-size: 1.5rem;
        }

        .settings-content {
            flex: 1;
        }

        .settings-content h3 {
            margin: 0 0 5px 0;
            font-size: 1.2rem;
        }

        .settings-content p {
            margin: 0;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .settings-arrow {
            color: var(--text-muted);
            font-size: 1.2rem;
        }

        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
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

        <!-- Settings Content -->
        <div class="content-wrapper">
            <div class="content-header">
                <h1>Settings</h1>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
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