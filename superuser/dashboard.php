<?php
session_start();

// Check if user is logged in and is a superuser
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_user') {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/dbconnection.php';

// Initialize variables
$errors = [];
$success_message = '';
$user_id = $_SESSION['user_id'];

// Get dashboard statistics
try {
    // Total users (excluding admins)
    $stmt = $dbh->prepare("SELECT COUNT(*) FROM users WHERE role != 'admin'");
    $stmt->execute();
    $total_users = $stmt->fetchColumn();

    // Total events created by superuser
    $stmt = $dbh->prepare("SELECT COUNT(*) FROM events WHERE created_by = ?");
    $stmt->execute([$user_id]);
    $total_events = $stmt->fetchColumn();

    // Upcoming events
    $stmt = $dbh->prepare("SELECT COUNT(*) FROM events WHERE created_by = ? AND event_date > NOW()");
    $stmt->execute([$user_id]);
    $upcoming_events = $stmt->fetchColumn();

    // Get recent events
    $stmt = $dbh->prepare("SELECT * FROM events WHERE created_by = ? ORDER BY event_date DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $recent_events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recent registrations
    $stmt = $dbh->prepare("
        SELECT r.*, e.title as event_title, u.full_name as user_name 
        FROM registrations r 
        JOIN events e ON r.event_id = e.id 
        JOIN users u ON r.user_id = u.id 
        WHERE e.created_by = ? 
        ORDER BY r.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $recent_registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get user engagement metrics
    $stmt = $dbh->prepare("
        SELECT 
            COUNT(DISTINCT r.user_id) as total_participants,
            AVG(r.rating) as avg_rating,
            COUNT(r.feedback) as total_feedback
        FROM registrations r
        JOIN events e ON r.event_id = e.id
        WHERE e.created_by = ?
    ");
    $stmt->execute([$user_id]);
    $engagement_metrics = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $errors[] = "Error fetching dashboard statistics";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Superuser Dashboard - EventPro</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.css">
</head>
<body class="light-mode">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-logo">
            <img src="../assets/images/progress-kit-logo.png" alt="Progress Kit">
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li class="active">
                    <a href="dashboard.php">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
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
                <li>
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
                    <img src="../assets/images/user-avatar.jpg" alt="Superuser">
                    <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                </div>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <h1>Dashboard Overview</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Users</h3>
                        <p><?php echo $total_users; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Events</h3>
                        <p><?php echo $total_events; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Upcoming Events</h3>
                        <p><?php echo $upcoming_events; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Average Rating</h3>
                        <p><?php echo number_format($engagement_metrics['avg_rating'] ?? 0, 1); ?>/5</p>
                    </div>
                </div>
            </div>

            <!-- Recent Events -->
            <div class="section">
                <h2>Recent Events</h2>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Event Title</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Registrations</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_events as $event): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($event['title']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($event['event_date'])); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $event['status']; ?>">
                                            <?php echo ucfirst($event['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $stmt = $dbh->prepare("SELECT COUNT(*) FROM registrations WHERE event_id = ?");
                                        $stmt->execute([$event['id']]);
                                        echo $stmt->fetchColumn();
                                        ?>
                                    </td>
                                    <td>
                                        <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="view_event.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Registrations -->
            <div class="section">
                <h2>Recent Registrations</h2>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Event</th>
                                <th>Registration Date</th>
                                <th>Status</th>
                                <th>Rating</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_registrations as $registration): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($registration['user_name']); ?></td>
                                    <td><?php echo htmlspecialchars($registration['event_title']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($registration['created_at'])); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $registration['status']; ?>">
                                            <?php echo ucfirst($registration['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($registration['rating']): ?>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $registration['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                            <?php endfor; ?>
                                        <?php else: ?>
                                            <span class="text-muted">No rating</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Engagement Metrics -->
            <div class="section">
                <h2>Engagement Metrics</h2>
                <div class="metrics-grid">
                    <div class="metric-card">
                        <h3>Total Participants</h3>
                        <p><?php echo $engagement_metrics['total_participants'] ?? 0; ?></p>
                    </div>
                    <div class="metric-card">
                        <h3>Average Rating</h3>
                        <p><?php echo number_format($engagement_metrics['avg_rating'] ?? 0, 1); ?>/5</p>
                    </div>
                    <div class="metric-card">
                        <h3>Total Feedback</h3>
                        <p><?php echo $engagement_metrics['total_feedback'] ?? 0; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html> 