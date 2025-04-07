<?php
session_start();
require_once '../includes/dbconnection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $announcement_text = trim($_POST['announcement_text']);
    $admin_id = $_SESSION['user_id'];

    if (!empty($announcement_text)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO announcements (admin_id, announcement_text) VALUES (?, ?)");
            $stmt->execute([$admin_id, $announcement_text]);
            $success_message = "Announcement posted successfully!";
        } catch (PDOException $e) {
            $error_message = "Error posting announcement: " . $e->getMessage();
        }
    } else {
        $error_message = "Please enter an announcement text.";
    }
}

// Fetch existing announcements
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
    <title>Announcements - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1>Announcements</h1>
            </div>

            <div class="content-body">
                <!-- Announcement Form -->
                <div class="card">
                    <div class="card-header">
                        <h2>Post New Announcement</h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success_message)): ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php endif; ?>
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="announcement_text">Announcement Text</label>
                                <textarea id="announcement_text" name="announcement_text" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Post Announcement</button>
                        </form>
                    </div>
                </div>

                <!-- Existing Announcements -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h2>Recent Announcements</h2>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($announcements)): ?>
                            <div class="announcements-list">
                                <?php foreach ($announcements as $announcement): ?>
                                    <div class="announcement-item">
                                        <div class="announcement-header">
                                            <span class="admin-name"><?php echo htmlspecialchars($announcement['admin_name']); ?></span>
                                            <span class="timestamp"><?php echo date('F j, Y g:i A', strtotime($announcement['created_at'])); ?></span>
                                        </div>
                                        <div class="announcement-text">
                                            <?php echo nl2br(htmlspecialchars($announcement['announcement_text'])); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>No announcements yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>
</html> 