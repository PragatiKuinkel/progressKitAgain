<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize admin name with default value
$adminName = '-';

// Check if user is logged in and has an ID
if (isset($_SESSION['user_id'])) {
    try {
        // Include database connection using correct path
        require_once __DIR__ . '/../../includes/dbconnection.php';
        
        // Fetch admin name from database
        $stmt = $dbh->prepare("SELECT full_name FROM users WHERE id = :id");
        $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result && isset($result['full_name'])) {
            $adminName = htmlspecialchars($result['full_name']);
        }
    } catch (PDOException $e) {
        error_log("Error fetching admin name: " . $e->getMessage());
        // Keep default value if there's an error
    }
}
?>
<!-- Sidebar Header -->
<div class="sidebar-header">
    <div class="admin-profile">
        <div class="profile-icon">
            <i class="fas fa-user-shield"></i>
        </div>
        <div class="admin-name">
            <?php echo $adminName; ?>
        </div>
    </div>
</div>

<!-- Sidebar Navigation -->
<nav class="sidebar-nav">
    <ul>
        <li>
            <a href="dashboard.php">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="users.php" class="nav-link">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
        </li>
        <li>
            <a href="post_announcement.php" class="nav-link">
                <i class="fas fa-bullhorn"></i>
                <span>Post Announcement</span>
            </a>
        </li>
        <li>
            <a href="events.php">
                <i class="fas fa-calendar-alt"></i>
                <span>Event Management</span>
            </a>
        </li>
        <li>
            <a href="messages.php">
                <i class="fas fa-comments"></i>
                <span>Messages</span>
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

<!-- Sidebar Footer -->
<div class="sidebar-footer">
    <!-- Dark/Light Mode Toggle (Commented Out) -->
    <!--
    <button id="theme-toggle" class="btn btn-icon">
        <i class="fas fa-moon"></i>
        <span>Dark Mode</span>
    </button>
    -->
    <a href="../logout.php" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
    </a>
</div> 