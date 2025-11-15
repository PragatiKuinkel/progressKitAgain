<?php
session_start();
include('../includes/config.php');

if(strlen($_SESSION['odmsaid'])==0) {   
    header('location:../index.php');
}

// Handle POST request for password update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];
    $adminId = $_SESSION['odmsaid'];

    try {
        // Verify current password
        $sql = "SELECT Password FROM tbladmin WHERE ID = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $adminId, PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if (!password_verify($currentPassword, $result['Password'])) {
            echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect']);
            exit();
        }

        if ($newPassword !== $confirmPassword) {
            echo json_encode(['status' => 'error', 'message' => 'New passwords do not match']);
            exit();
        }

        // Validate new password
        if (strlen($newPassword) < 8) {
            echo json_encode(['status' => 'error', 'message' => 'New password must be at least 8 characters long']);
            exit();
        }

        if (!preg_match('/[A-Z]/', $newPassword)) {
            echo json_encode(['status' => 'error', 'message' => 'New password must contain at least one uppercase letter']);
            exit();
        }

        if (!preg_match('/[a-z]/', $newPassword)) {
            echo json_encode(['status' => 'error', 'message' => 'New password must contain at least one lowercase letter']);
            exit();
        }

        if (!preg_match('/[0-9]/', $newPassword)) {
            echo json_encode(['status' => 'error', 'message' => 'New password must contain at least one number']);
            exit();
        }

        if (!preg_match('/[^A-Za-z0-9]/', $newPassword)) {
            echo json_encode(['status' => 'error', 'message' => 'New password must contain at least one special character']);
            exit();
        }

        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE tbladmin SET Password = :password WHERE ID = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $query->bindParam(':id', $adminId, PDO::PARAM_INT);
        $query->execute();

        echo json_encode(['status' => 'success', 'message' => 'Password updated successfully']);
        exit();
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - EventPro Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .password-form {
            max-width: 800px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(var(--primary-color-rgb), 0.1);
        }

        .invalid-feedback {
            display: none;
            color: var(--danger-color);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .was-validated .form-control:invalid ~ .invalid-feedback {
            display: block;
        }

        .form-actions {
            margin-top: 2rem;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            color: white;
            border: none;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .password-requirements {
            margin-top: 1rem;
            padding: 1rem;
            background-color: var(--light-bg);
            border-radius: 4px;
        }

        .password-requirements h4 {
            margin-top: 0;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .password-requirements ul {
            margin: 0;
            padding-left: 1.5rem;
        }

        .password-requirements li {
            margin-bottom: 0.25rem;
            color: var(--text-muted);
        }

        .password-requirements li.valid {
            color: var(--success-color);
        }

        .password-requirements li.valid::before {
            content: "✓";
            margin-right: 0.5rem;
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

        <!-- Password Change Content -->
        <div class="content-wrapper">
            <div class="content-header">
                <h1>Change Password</h1>
                <a href="settings.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Settings
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form id="passwordForm" class="password-form needs-validation" novalidate>
                        <div class="form-group">
                            <label for="currentPassword" class="form-label">Current Password *</label>
                            <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                            <div class="invalid-feedback">Please enter your current password</div>
                        </div>

                        <div class="form-group">
                            <label for="newPassword" class="form-label">New Password *</label>
                            <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                            <div class="invalid-feedback">Please enter a new password</div>
                        </div>

                        <div class="form-group">
                            <label for="confirmPassword" class="form-label">Confirm New Password *</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                            <div class="invalid-feedback">Please confirm your new password</div>
                        </div>

                        <div class="password-requirements">
                            <h4>Password Requirements:</h4>
                            <ul>
                                <li id="length">At least 8 characters long</li>
                                <li id="uppercase">Contains at least one uppercase letter</li>
                                <li id="lowercase">Contains at least one lowercase letter</li>
                                <li id="number">Contains at least one number</li>
                                <li id="special">Contains at least one special character</li>
                            </ul>
                        </div>

                        <div class="form-actions">
                            <a href="settings.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Change Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
        $(document).ready(function() {
            // Password validation
            function validatePassword(password) {
                const requirements = {
                    length: password.length >= 8,
                    uppercase: /[A-Z]/.test(password),
                    lowercase: /[a-z]/.test(password),
                    number: /[0-9]/.test(password),
                    special: /[^A-Za-z0-9]/.test(password)
                };

                Object.keys(requirements).forEach(key => {
                    const element = $(`#${key}`);
                    if (requirements[key]) {
                        element.addClass('valid');
                    } else {
                        element.removeClass('valid');
                    }
                });

                return Object.values(requirements).every(Boolean);
            }

            // Handle form submission
            $('#passwordForm').on('submit', function(e) {
                e.preventDefault();
                
                if (!this.checkValidity()) {
                    e.stopPropagation();
                    $(this).addClass('was-validated');
                    return;
                }

                const newPassword = $('#newPassword').val();
                const confirmPassword = $('#confirmPassword').val();

                if (!validatePassword(newPassword)) {
                    alert('Please ensure your new password meets all requirements');
                    return;
                }

                if (newPassword !== confirmPassword) {
                    alert('New passwords do not match');
                    return;
                }

                const formData = new FormData(this);
                
                $.ajax({
                    url: 'change_password.php',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        try {
                            const data = typeof response === 'string' ? JSON.parse(response) : response;
                            if (data.status === 'success') {
                                alert('Password changed successfully');
                                window.location.href = 'settings.php';
                            } else {
                                alert('Error: ' + data.message);
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            alert('Error processing server response');
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Error: ' + error);
                    }
                });
            });

            // Real-time password validation
            $('#newPassword').on('input', function() {
                validatePassword($(this).val());
            });
        });
    </script>
</body>
</html> 