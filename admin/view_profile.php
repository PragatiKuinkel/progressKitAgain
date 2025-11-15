<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Check if config file exists
if (!file_exists('../includes/dbconnection.php')) {
    die("Error: Database configuration file not found. Please check if includes/dbconnection.php exists.");
}

include('../includes/dbconnection.php');

// Check if user is logged in
if (!isset($_SESSION['odmsaid']) || strlen($_SESSION['odmsaid']) == 0) {   
    header('location:../index.php');
    exit();
}

// Initialize variables
$profile = [];
$error = null;

// Handle POST request for profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate input
        if (!isset($_POST['fullName']) || !isset($_POST['email'])) {
            throw new Exception('Required fields are missing');
        }

        $fullName = trim($_POST['fullName']);
        $email = trim($_POST['email']);
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $adminId = $_SESSION['odmsaid'];

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        // Update profile
        $sql = "UPDATE users SET full_name = :fullName, email = :email, phone = :phone WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':fullName', $fullName, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':phone', $phone, PDO::PARAM_STR);
        $query->bindParam(':id', $adminId, PDO::PARAM_INT);
        
        if (!$query->execute()) {
            throw new Exception('Failed to update profile');
        }

        echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully']);
        exit();
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit();
    }
}

// Fetch current profile data
try {
    if (!isset($dbh)) {
        throw new Exception('Database connection not established');
    }

    $sql = "SELECT full_name, email, phone FROM users WHERE id = :id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id', $_SESSION['odmsaid'], PDO::PARAM_INT);
    
    if (!$query->execute()) {
        throw new Exception('Failed to fetch profile data');
    }

    $profile = $query->fetch(PDO::FETCH_ASSOC);
    
    if (!$profile) {
        throw new Exception('Profile not found');
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Profile - EventPro Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-form {
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

        .error-message {
            color: var(--danger-color);
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            background-color: rgba(var(--danger-color-rgb), 0.1);
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

        <!-- Profile Content -->
        <div class="content-wrapper">
            <div class="content-header">
                <h1>View Profile</h1>
                <a href="settings.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Settings
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form id="profileForm" class="profile-form needs-validation" novalidate>
                        <div class="form-group">
                            <label for="fullName" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="fullName" name="fullName" 
                                   value="<?php echo isset($profile['full_name']) ? htmlspecialchars($profile['full_name']) : ''; ?>" required>
                            <div class="invalid-feedback">Please enter your full name</div>
                        </div>

                        <div class="form-group">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo isset($profile['email']) ? htmlspecialchars($profile['email']) : ''; ?>" required>
                            <div class="invalid-feedback">Please enter a valid email address</div>
                        </div>

                        <div class="form-group">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo isset($profile['phone']) ? htmlspecialchars($profile['phone']) : ''; ?>">
                        </div>

                        <div class="form-actions">
                            <a href="settings.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
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
            let formChanged = false;
            const originalFormData = $('#profileForm').serialize();

            // Track form changes
            $('#profileForm input').on('change', function() {
                formChanged = true;
            });

            // Handle form submission
            $('#profileForm').on('submit', function(e) {
                e.preventDefault();
                
                if (!this.checkValidity()) {
                    e.stopPropagation();
                    $(this).addClass('was-validated');
                    return;
                }

                const formData = new FormData(this);
                
                $.ajax({
                    url: 'view_profile.php',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        try {
                            const data = typeof response === 'string' ? JSON.parse(response) : response;
                            if (data.status === 'success') {
                                alert('Profile updated successfully');
                                formChanged = false;
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

            // Warn before leaving if changes are unsaved
            window.addEventListener('beforeunload', function(e) {
                if (formChanged) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        });
    </script>
</body>
</html> 