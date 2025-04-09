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
$error_messages = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $required_fields = ['full_name', 'email', 'phone', 'role', 'password'];
        $errors = [];
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        
        // Validate email format
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        
        // Validate phone format (basic validation)
        if (!preg_match('/^[0-9]{10,15}$/', $_POST['phone'])) {
            $errors[] = 'Invalid phone number format';
        }
        
        // Check if email already exists
        $stmt = $dbh->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$_POST['email']]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Email already exists';
        }
        
        if (!empty($errors)) {
            $error_messages = $errors;
        } else {
            // Hash password
            $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
            // Insert user
            $stmt = $dbh->prepare("
                INSERT INTO users (full_name, email, phone, role, password)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $_POST['full_name'],
                $_POST['email'],
                $_POST['phone'],
                $_POST['role'],
                $password_hash
            ]);
            
            $success_message = 'User created successfully';
            
            // Clear form data
            $_POST = [];
        }
    } catch (PDOException $e) {
        error_log("Error creating user: " . $e->getMessage());
        $error_messages[] = 'Error creating user: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User - Progress Kit</title>
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

        <!-- Add User Content -->
        <div class="content-wrapper">
            <div class="content-header">
                <h1>Add New User</h1>
                <a href="users.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Users
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

                    <?php if (!empty($error_messages)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                <?php foreach ($error_messages as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="add_user.php" class="needs-validation" novalidate>
                        <div class="form-group mb-3">
                            <label for="fullName" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="fullName" name="full_name" 
                                   value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
                            <div class="invalid-feedback">Please enter full name.</div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" 
                                   pattern="[0-9]{10,15}" required>
                            <div class="invalid-feedback">Please enter a valid phone number (10-15 digits).</div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="role" class="form-label">Role *</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                <option value="super_user" <?php echo (isset($_POST['role']) && $_POST['role'] === 'super_user') ? 'selected' : ''; ?>>Super User</option>
                                <option value="user" <?php echo (isset($_POST['role']) && $_POST['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                            </select>
                            <div class="invalid-feedback">Please select a role.</div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="password" class="form-label">Password *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="invalid-feedback">Please enter a password.</div>
                        </div>

                        <div class="form-actions d-flex justify-content-end mt-4">
                            <a href="users.php" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Add User</button>
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