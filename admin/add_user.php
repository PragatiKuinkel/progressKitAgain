<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Database connection
require_once '../includes/dbconnection.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        // Validate required fields
        $required_fields = ['full_name', 'email', 'role', 'password'];
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
        
        // Check if email already exists
        $stmt = $dbh->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$_POST['email']]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Email already exists';
        }
        
        if (!empty($errors)) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit();
        }
        
        // Hash password
        $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        // Insert user
        $stmt = $dbh->prepare("
            INSERT INTO users (full_name, email, role, password)
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_POST['full_name'],
            $_POST['email'],
            $_POST['role'],
            $password_hash
        ]);
        
        echo json_encode(['success' => true, 'message' => 'User created successfully']);
    } catch (PDOException $e) {
        error_log("Error creating user: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error creating user']);
    }
    exit();
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
        <div class="sidebar-logo">
            <img src="../assets/images/progress-kit-logo.png" alt="Progress Kit">
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li>
                    <a href="dashboard.php">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="active">
                    <a href="users.php">
                        <i class="fas fa-users"></i>
                        <span>User Management</span>
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
                    <form id="addUserForm" class="needs-validation" novalidate>
                        <!-- Basic Information Section -->
                        <div class="form-section mb-4">
                            <h3 class="section-title">User Information</h3>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="fullName" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control form-control-lg" id="fullName" name="full_name" required>
                                    <div class="invalid-feedback">Please enter the full name</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control form-control-lg" id="email" name="email" required>
                                    <div class="invalid-feedback">Please enter a valid email address</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="role" class="form-label">Role *</label>
                                    <select class="form-select form-select-lg" id="role" name="role" required>
                                        <option value="">Select Role</option>
                                        <option value="admin">Admin</option>
                                        <option value="superuser">Superuser</option>
                                        <option value="user">User</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a role</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="password" class="form-label">Password *</label>
                                    <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                                    <div class="invalid-feedback">Please enter a password</div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions mt-4">
                            <div class="d-flex justify-content-end gap-3">
                                <a href="users.php" class="btn btn-lg btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-lg btn-primary">Add User</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm User Creation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to add this user?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmAdd">Yes, Add User</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            console.log('Document ready');
            
            // Form validation
            const form = document.getElementById('addUserForm');
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                console.log('Form submitted');
                
                if (!form.checkValidity()) {
                    event.stopPropagation();
                    form.classList.add('was-validated');
                    console.log('Form validation failed');
                    return;
                }

                // Show confirmation modal
                $('#confirmationModal').modal('show');
            });

            // Handle confirmation
            $('#confirmAdd').on('click', function() {
                console.log('Confirm add clicked');
                const formData = new FormData($('#addUserForm')[0]);
                
                // Log form data for debugging
                for (let [key, value] of formData.entries()) {
                    console.log(key + ': ' + value);
                }
                
                $.ajax({
                    url: 'add_user.php',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log('Server response:', response);
                        try {
                            const data = typeof response === 'string' ? JSON.parse(response) : response;
                            if (data.success) {
                                window.location.href = 'users.php?success=1';
                            } else {
                                alert('Error: ' + (data.message || 'Unknown error occurred'));
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            alert('Error processing server response');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', {xhr, status, error});
                        alert('Error: ' + error);
                    }
                });
            });
        });
    </script>
</body>
</html> 