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
    <title>Post Announcement - Progress Kit</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
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
                    <img src="../assets/images/admin-avatar.jpg" alt="Admin">
                    <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                </div>
            </div>
        </header>

        <!-- Post Announcement Content -->
        <div class="content-wrapper">
            <div class="content-header">
                <h1>Post Announcement</h1>
                <a href="announcements.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Announcements
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <?php if ($success_message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($success_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error_message): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="post_announcement.php" class="needs-validation" novalidate>
                        <div class="form-group mb-3">
                            <label for="description" class="form-label">Announcement Description *</label>
                            <textarea class="form-control" id="description" name="description" rows="5" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            <div class="invalid-feedback">Please enter an announcement description.</div>
                        </div>

                        <div class="form-actions d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">Post Announcement</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function() {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html> 