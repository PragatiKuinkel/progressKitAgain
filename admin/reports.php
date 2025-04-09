<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Initialize variables for future database integration
$totalUsers = '-';
$totalEvents = '-';
$completedEvents = '-';
$upcomingEvents = '-';

// Include database connection
require_once '../includes/dbconnection.php';

try {
    // Total users
    $stmt = $dbh->prepare("SELECT COUNT(*) AS total_users FROM users");
    $stmt->execute();
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];
} catch (PDOException $e) {
    error_log("Reports error (users count): " . $e->getMessage());
    // Keep the default value if query fails
}

// TODO: Uncomment and implement other database queries when ready
/*
try {
    // Total events
    $stmt = $dbh->prepare("SELECT COUNT(*) AS total_events FROM events");
    $stmt->execute();
    $totalEvents = $stmt->fetch(PDO::FETCH_ASSOC)['total_events'];

    // Completed events
    $stmt = $dbh->prepare("SELECT COUNT(*) AS completed_events FROM events WHERE event_date < NOW()");
    $stmt->execute();
    $completedEvents = $stmt->fetch(PDO::FETCH_ASSOC)['completed_events'];

    // Upcoming events
    $stmt = $dbh->prepare("SELECT COUNT(*) AS upcoming_events FROM events WHERE event_date > NOW()");
    $stmt->execute();
    $upcomingEvents = $stmt->fetch(PDO::FETCH_ASSOC)['upcoming_events'];
} catch (PDOException $e) {
    error_log("Reports error: " . $e->getMessage());
}
*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - EventPro Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.css">
    <style>
        .reports-content {
            padding: 20px;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 1.8rem;
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .breadcrumb {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .stat-info h3 {
            font-size: 1rem;
            color: var(--text-muted);
            margin-bottom: 5px;
        }

        .stat-info p {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-color);
        }

        .charts-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: var(--white);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .chart-title {
            font-size: 1.2rem;
            color: var(--text-color);
            margin-bottom: 20px;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        .table-section {
            background: var(--white);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .table-title {
            font-size: 1.2rem;
            color: var(--text-color);
            margin-bottom: 20px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .table th {
            background-color: var(--light-bg);
            font-weight: 600;
        }

        .table tr:hover {
            background-color: var(--light-bg);
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .charts-section {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="light-mode">
    <!-- Sidebar -->
    <div class="sidebar">
        <?php include 'includes/sidebar.php'; ?>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="reports-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Reports & Analytics</h1>
            </div>

            <!-- Overview Section -->
            <div class="stats-grid">
                <!-- Total Registered Users -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Registered Users</h3>
                        <p><?php echo htmlspecialchars($totalUsers); ?></p>
                    </div>
                </div>

                <!-- Total Events -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Events</h3>
                        <p><?php echo htmlspecialchars($totalEvents); ?></p>
                    </div>
                </div>

                <!-- Completed Events -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Completed Events</h3>
                        <p><?php echo htmlspecialchars($completedEvents); ?></p>
                    </div>
                </div>

                <!-- Upcoming Events -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Upcoming Events</h3>
                        <p><?php echo htmlspecialchars($upcomingEvents); ?></p>
                    </div>
                </div>
            </div>

            <!-- Graphical Data Section -->
            <div class="charts-section">
                <!-- Events Over Time Chart -->
                <div class="chart-card">
                    <h3 class="chart-title">Events Over Time</h3>
                    <div class="chart-container">
                        <!-- TODO: Implement Chart.js for Events Over Time -->
                        <canvas id="eventsChart"></canvas>
                    </div>
                </div>

                <!-- User Roles Distribution Chart -->
                <div class="chart-card">
                    <h3 class="chart-title">User Roles Distribution</h3>
                    <div class="chart-container">
                        <!-- TODO: Implement Chart.js for User Roles Distribution -->
                        <canvas id="rolesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="table-section">
                <h3 class="table-title">Event Details</h3>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Event Name</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Organizer</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- TODO: Implement dynamic data from database -->
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 20px;">
                                    Data will be loaded from the database
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script>
        // TODO: Implement Chart.js initialization when data is available
        document.addEventListener('DOMContentLoaded', function() {
            // Sample data for demonstration
            const eventsCtx = document.getElementById('eventsChart').getContext('2d');
            const rolesCtx = document.getElementById('rolesChart').getContext('2d');

            // Events Over Time Chart
            new Chart(eventsCtx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Events',
                        data: [12, 19, 3, 5, 2, 3],
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // User Roles Distribution Chart
            new Chart(rolesCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Admin', 'Organizer', 'Attendee'],
                    datasets: [{
                        data: [30, 50, 100],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.5)',
                            'rgba(54, 162, 235, 0.5)',
                            'rgba(255, 206, 86, 0.5)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        });
    </script>
</body>
</html> 