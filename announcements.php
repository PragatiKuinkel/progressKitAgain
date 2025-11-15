<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'includes/dbconnection.php';

// Fetch announcements
try {
    $stmt = $dbh->prepare("SELECT * FROM announcements ORDER BY created_at DESC");
    $stmt->execute();
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching announcements: " . $e->getMessage());
    $announcements = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements - Progress Kit</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
                    <img src="assets/images/user-avatar.jpg" alt="User">
                    <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                </div>
            </div>
        </header>

        <!-- Announcements Content -->
        <div class="content-wrapper">
            <div class="content-header">
                <h1>Announcements</h1>
            </div>

            <div class="announcements-container">
                <?php if (empty($announcements)): ?>
                    <div class="alert alert-info">
                        No announcements available.
                    </div>
                <?php else: ?>
                    <?php foreach ($announcements as $announcement): ?>
                        <div class="card announcement-card">
                            <div class="card-body">
                                <div class="announcement-content">
                                    <p class="announcement-text"><?php echo nl2br(htmlspecialchars($announcement['description'])); ?></p>
                                    <div class="announcement-meta">
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i>
                                            <?php echo date('F j, Y g:i A', strtotime($announcement['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 