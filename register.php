<?php
session_start();
$errors = [];
$success = false;

// Database connection (replace with your actual database credentials)
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'event_management';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    $errors[] = "Database connection failed: " . $e->getMessage();
}

// Check if admin already exists
function adminExists($pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';

    // Validation
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($phone)) {
        $errors[] = "Phone number is required";
    } elseif (!preg_match("/^[0-9]{10,15}$/", $phone)) {
        $errors[] = "Invalid phone number format";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    if (empty($role)) {
        $errors[] = "Please select a role";
    } elseif ($role === 'admin' && adminExists($pdo)) {
        $errors[] = "Admin role already exists. Only one admin is allowed.";
    }

    // If no errors, process registration
    if (empty($errors)) {
        try {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user into database
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$full_name, $email, $phone, $hashed_password, $role]);
            
            // Set success message in session
            $_SESSION['registration_success'] = true;
            $_SESSION['registered_email'] = $email;
            
            // Redirect to login page
            header('Location: login.php');
            exit();
            
        } catch(PDOException $e) {
            $errors[] = "Registration failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management System - Registration</title>
    <link rel="stylesheet" href="assets/css/register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="register-container">
        <h2>Event Management System</h2>
        <h3>User Registration</h3>
        
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="full_name">
                    <i class="fas fa-user"></i> Full Name
                </label>
                <input type="text" id="full_name" name="full_name" required 
                       value="<?php echo htmlspecialchars($full_name ?? ''); ?>"
                       placeholder="Enter your full name">
            </div>

            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> Email Address
                </label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo htmlspecialchars($email ?? ''); ?>"
                       placeholder="Enter your email">
            </div>

            <div class="form-group">
                <label for="phone">
                    <i class="fas fa-phone"></i> Phone Number
                </label>
                <input type="tel" id="phone" name="phone" required 
                       value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                       placeholder="Enter your phone number">
            </div>

            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> Password
                </label>
                <input type="password" id="password" name="password" required
                       placeholder="Enter your password">
            </div>

            <div class="form-group">
                <label for="confirm_password">
                    <i class="fas fa-lock"></i> Confirm Password
                </label>
                <input type="password" id="confirm_password" name="confirm_password" required
                       placeholder="Confirm your password">
            </div>

            <div class="form-group">
                <label for="role">
                    <i class="fas fa-user-tag"></i> User Role
                </label>
                <select id="role" name="role" required>
                    <option value="">Select Role</option>
                    <option value="admin" <?php echo ($role === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    <option value="super_user" <?php echo ($role === 'super_user') ? 'selected' : ''; ?>>Super User</option>
                    <option value="user" <?php echo ($role === 'user') ? 'selected' : ''; ?>>User</option>
                </select>
            </div>

            <button type="submit" class="register-btn">
                <i class="fas fa-user-plus"></i> Register
            </button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login here</a>
        </div>
    </div>
</body>
</html>
