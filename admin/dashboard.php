<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Database connection
require_once '../includes/dbconnection.php';

// Initialize variables
$totalUsers = '-';
$totalEvents = '-';
$upcomingEvents = '-';
$completedEvents = '-';
$error = null;

// Get dashboard statistics
try {
    // Total users
    $stmt = $dbh->prepare("SELECT COUNT(*) AS total_users FROM users");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalUsers = $result['total_users'] ?? '-';

    // Total users by role
    $stmt = $dbh->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    $userStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Total events
    $stmt = $dbh->query("SELECT COUNT(*) as total_events FROM events");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalEvents = $result['total_events'] ?? '-';

    // Upcoming events
    $stmt = $dbh->query("SELECT COUNT(*) as upcoming_events FROM events WHERE event_date > NOW()");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $upcomingEvents = $result['upcoming_events'] ?? '-';

    // Completed events
    $stmt = $dbh->query("SELECT COUNT(*) as completed_events FROM events WHERE event_date < NOW()");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $completedEvents = $result['completed_events'] ?? '-';
} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $error = "Error fetching dashboard statistics: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EventPro</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.css">
</head>
<body class="light-mode">
    <!-- Sidebar -->
    <div class="sidebar">
        <?php include 'includes/sidebar.php'; ?>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <h1>Dashboard Overview</h1>
            
            <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <!-- Card 1: Total Users -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Users</h3>
                        <p><?php echo htmlspecialchars($totalUsers); ?></p>
                    </div>
                </div>

                <!-- Card 2: Upcoming Events -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Upcoming Events</h3>
                        <p><?php echo htmlspecialchars($upcomingEvents); ?></p>
                    </div>
                </div>

                <!-- Card 3: Completed Events -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Completed Events</h3>
                        <p><?php echo htmlspecialchars($completedEvents); ?></p>
                    </div>
                </div>

                <!-- Card 4: Total Events -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Events</h3>
                        <p><?php echo htmlspecialchars($totalEvents); ?></p>
                    </div>
                </div>
            </div>

            <!-- Reports Section -->
            <div class="reports-section">
                <h2>Reports & Analytics</h2>
                <div class="reports-grid">
                    <!-- Report cards will be dynamically loaded -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Only initialize settings dropdown if elements exist
            const settingsToggle = document.querySelector('.settings-toggle');
            const settingsDropdown = document.querySelector('.settings-dropdown');
            const settingsArrow = document.querySelector('.settings-arrow');

            if (settingsToggle && settingsDropdown && settingsArrow) {
                settingsToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    settingsDropdown.classList.toggle('show');
                    settingsArrow.classList.toggle('rotate');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('.settings-menu')) {
                        settingsDropdown.classList.remove('show');
                        settingsArrow.classList.remove('rotate');
                    }
                });
            }
        });
    </script>
    <style>
        /* Settings dropdown styles */
        .settings-menu {
            position: relative;
        }

        .settings-dropdown {
            display: none;
            position: absolute;
            left: 100%;
            top: 0;
            background: var(--sidebar-bg);
            min-width: 200px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-radius: 4px;
            z-index: 1000;
        }

        .settings-dropdown.show {
            display: block;
        }

        .settings-dropdown li {
            padding: 0;
        }

        .settings-dropdown a {
            padding: 10px 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-color);
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .settings-dropdown a:hover {
            background-color: var(--hover-bg);
        }

        .settings-arrow {
            margin-left: auto;
            transition: transform 0.3s;
        }

        .settings-arrow.rotate {
            transform: rotate(180deg);
        }

        @media (max-width: 768px) {
            .settings-dropdown {
                position: static;
                width: 100%;
            }
        }

        /* Alert styles */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
    </style>
</body>
</html> 