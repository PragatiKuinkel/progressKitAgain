<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Database connection
require_once '../includes/dbconnection.php';

// Get dashboard statistics
try {
    // Total users by role
    $stmt = $dbh->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    $userStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Total events
    $stmt = $dbh->query("SELECT COUNT(*) as total_events FROM events");
    $totalEvents = $stmt->fetch(PDO::FETCH_ASSOC)['total_events'];

    // Upcoming events
    $stmt = $dbh->query("SELECT COUNT(*) as upcoming_events FROM events WHERE event_date > NOW()");
    $upcomingEvents = $stmt->fetch(PDO::FETCH_ASSOC)['upcoming_events'];

    // Completed events
    $stmt = $dbh->query("SELECT COUNT(*) as completed_events FROM events WHERE event_date < NOW()");
    $completedEvents = $stmt->fetch(PDO::FETCH_ASSOC)['completed_events'];
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
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <!-- Card 1: Total Users -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Users</h3>
                        <p>
                            <?php
                            // TODO: Database integration for total users
                            // $sql = "SELECT COUNT(*) as total_users FROM users";
                            // $stmt = $dbh->prepare($sql);
                            // $stmt->execute();
                            // $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            // echo $result['total_users'];
                            echo "–";
                            ?>
                        </p>
                    </div>
                </div>

                <!-- Card 2: Upcoming Events -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Upcoming Events</h3>
                        <p>
                            <?php
                            // TODO: Database integration for upcoming events
                            // $sql = "SELECT COUNT(*) as upcoming_events FROM events WHERE event_date > NOW()";
                            // $stmt = $dbh->prepare($sql);
                            // $stmt->execute();
                            // $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            // echo $result['upcoming_events'];
                            echo "–";
                            ?>
                        </p>
                    </div>
                </div>

                <!-- Card 3: Completed Events -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Completed Events</h3>
                        <p>
                            <?php
                            // TODO: Database integration for completed events
                            // $sql = "SELECT COUNT(*) as completed_events FROM events WHERE event_date < NOW()";
                            // $stmt = $dbh->prepare($sql);
                            // $stmt->execute();
                            // $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            // echo $result['completed_events'];
                            echo "–";
                            ?>
                        </p>
                    </div>
                </div>

                <!-- Card 4: Total Events -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Events</h3>
                        <p>
                            <?php
                            // TODO: Database integration for total events
                            // $sql = "SELECT COUNT(*) as total_events FROM events";
                            // $stmt = $dbh->prepare($sql);
                            // $stmt->execute();
                            // $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            // echo $result['total_events'];
                            echo "–";
                            ?>
                        </p>
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
        // Settings dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const settingsToggle = document.querySelector('.settings-toggle');
            const settingsDropdown = document.querySelector('.settings-dropdown');
            const settingsArrow = document.querySelector('.settings-arrow');

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
    </style>
</body>
</html> 