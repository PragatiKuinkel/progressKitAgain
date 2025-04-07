<!-- Sidebar Header -->
<div class="sidebar-header">
    <div class="admin-profile">
        <div class="profile-icon">
            <i class="fas fa-user-shield"></i>
        </div>
        <div class="admin-name">
            <?php
            // TODO: Replace with actual admin name from database
            // $adminName = "John Doe"; // Example: Fetch from database
            // echo htmlspecialchars($adminName);
            echo "–";
            ?>
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
            <a href="announcements.php">
                <i class="fas fa-bullhorn"></i>
                <span>Announcements</span>
            </a>
        </li>
        <li>
            <a href="messages.php">
                <i class="fas fa-envelope"></i>
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
    <a href="../logout.php" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
    </a>
</div> 