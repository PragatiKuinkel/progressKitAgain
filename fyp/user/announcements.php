<?php
session_start();
require_once '../includes/dbconnection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Fetch announcements
try {
    $stmt = $pdo->prepare("
        SELECT a.*, u.full_name as admin_name 
        FROM announcements a 
        JOIN users u ON a.admin_id = u.id 
        ORDER BY a.created_at DESC
    ");
    $stmt->execute();
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error fetching announcements: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements - User Panel</title>
    <link rel="stylesheet" href="../assets/css/user.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="user-container">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1>Announcements</h1>
            </div>

            <div class="content-body">
                <div class="card">
                    <div class="card-header">
                        <h2>Latest Announcements</h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>

                        <?php if (!empty($announcements)): ?>
                            <div class="announcements-list">
                                <?php foreach ($announcements as $announcement): ?>
                                    <div class="announcement-item">
                                        <div class="announcement-header">
                                            <span class="admin-name">
                                                <i class="fas fa-user-shield"></i>
                                                <?php echo htmlspecialchars($announcement['admin_name']); ?>
                                            </span>
                                            <span class="timestamp">
                                                <i class="far fa-clock"></i>
                                                <?php echo date('F j, Y g:i A', strtotime($announcement['created_at'])); ?>
                                            </span>
                                        </div>
                                        <div class="announcement-text">
                                            <?php echo nl2br(htmlspecialchars($announcement['announcement_text'])); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-bullhorn"></i>
                                <p>No announcements available at the moment.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/user.js"></script>
</body>
</html> 