<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/dbconnection.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_announcement'])) {
    $announcement = trim($_POST['announcement']);
    $admin_id = $_SESSION['user_id'];
    
    if (!empty($announcement)) {
        try {
            $stmt = $dbh->prepare("
                INSERT INTO announcements (admin_id, content, created_at)
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$admin_id, $announcement]);
            $success = "Announcement posted successfully!";
        } catch (PDOException $e) {
            error_log("Error posting announcement: " . $e->getMessage());
            $error = "Error posting announcement. Please try again.";
        }
    } else {
        $error = "Please enter an announcement.";
    }
}

// Get recent announcements
try {
    $stmt = $dbh->prepare("
        SELECT a.*, u.full_name as admin_name
        FROM announcements a
        JOIN users u ON a.admin_id = u.id
        ORDER BY a.created_at DESC
        LIMIT 10
    ");
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
    <title>Announcements - EventPro Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .announcement-card {
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin: 20px;
            padding: 20px;
        }

        .announcement-form {
            margin-bottom: 30px;
        }

        .announcement-form textarea {
            width: 100%;
            padding: 15px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 1em;
            resize: vertical;
            min-height: 150px;
        }

        .announcement-form button {
            padding: 10px 20px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .announcement-form button:hover {
            background: var(--primary-hover);
        }

        .announcement-list {
            margin-top: 30px;
        }

        .announcement-item {
            background: var(--light-bg);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .announcement-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.9em;
            color: var(--text-muted);
        }

        .announcement-content {
            margin-bottom: 10px;
            white-space: pre-wrap;
        }

        .announcement-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .delete-btn {
            color: var(--danger-color);
            cursor: pointer;
            transition: color 0.3s;
        }

        .delete-btn:hover {
            color: var(--danger-hover);
        }

        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: var(--success-color);
            color: white;
        }

        .alert-error {
            background-color: var(--danger-color);
            color: white;
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
        <div class="content-wrapper">
            <div class="content-header">
                <h1>Announcements</h1>
            </div>

            <!-- Announcement Form -->
            <div class="announcement-card announcement-form">
                <h3>Create New Announcement</h3>
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <textarea name="announcement" placeholder="Type your announcement here..." required></textarea>
                    <button type="submit" name="post_announcement">
                        <i class="fas fa-paper-plane"></i> Post Announcement
                    </button>
                </form>
            </div>

            <!-- Recent Announcements -->
            <div class="announcement-card announcement-list">
                <h3>Recent Announcements</h3>
                <?php if (empty($announcements)): ?>
                    <p>No announcements yet.</p>
                <?php else: ?>
                    <?php foreach ($announcements as $announcement): ?>
                        <div class="announcement-item">
                            <div class="announcement-header">
                                <span>Posted by <?php echo htmlspecialchars($announcement['admin_name']); ?></span>
                                <span><?php echo date('M j, Y g:i A', strtotime($announcement['created_at'])); ?></span>
                            </div>
                            <div class="announcement-content">
                                <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                            </div>
                            <div class="announcement-actions">
                                <button class="delete-btn delete-announcement" data-id="<?php echo $announcement['id']; ?>">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Delete Announcement
            $('.delete-announcement').on('click', function() {
                if (confirm('Are you sure you want to delete this announcement?')) {
                    const announcementId = $(this).data('id');
                    $.post('delete_announcement.php', { id: announcementId }, function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Error deleting announcement');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html> 