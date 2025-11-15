<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Database connection
require_once '../includes/dbconnection.php';

// Initialize message variables
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate input
        if (empty($_POST['description'])) {
            $error_message = 'Please enter an announcement description.';
        } else {
            // Test database connection
            if (!$dbh) {
                throw new Exception("Database connection failed");
            }

            // Insert announcement
            $stmt = $dbh->prepare("INSERT INTO announcements (description) VALUES (?)");
            
            if (!$stmt) {
                throw new Exception("Failed to prepare statement: " . implode(" ", $dbh->errorInfo()));
            }

            $result = $stmt->execute([$_POST['description']]);
            
            if (!$result) {
                throw new Exception("Failed to execute statement: " . implode(" ", $stmt->errorInfo()));
            }
            
            $success_message = 'Announcement posted successfully!';
        }
    } catch (Exception $e) {
        error_log("Error posting announcement: " . $e->getMessage());
        $error_message = 'Error posting announcement: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Announcement - EventPro Admin</title>
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
                <h1>Post Announcement</h1>
            </div>

            <!-- Announcement Form -->
            <div class="announcement-card announcement-form">
                <?php if ($success_message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>

                <form method="POST" action="post_announcement.php">
                    <textarea name="description" placeholder="Type your announcement here..." required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    <button type="submit">
                        <i class="fas fa-paper-plane"></i> Post Announcement
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html> 