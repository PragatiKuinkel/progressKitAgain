<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'includes/dbconnection.php';

$errors = [];
$success_message = '';

// Check for registration success message
if (isset($_SESSION['registration_success']) && $_SESSION['registration_success']) {
    $success_message = "Registration successful! Please login with your credentials.";
    $prefilled_email = $_SESSION['registered_email'] ?? '';
    unset($_SESSION['registration_success']);
    unset($_SESSION['registered_email']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;

    // Validation
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    }

    if (empty($errors)) {
        try {
            // Prepare and execute the query
            $stmt = $dbh->prepare("SELECT id, full_name, email, password, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['odmsaid'] = $user['id'];

                // Set remember me cookie if checked
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expiry = time() + (30 * 24 * 60 * 60); // 30 days
                    
                    // Store token in database
                    $stmt = $dbh->prepare("UPDATE users SET remember_token = ?, token_expiry = ? WHERE id = ?");
                    $stmt->execute([$token, date('Y-m-d H:i:s', $expiry), $user['id']]);
                    
                    // Set cookie
                    setcookie('remember_token', $token, $expiry, '/', '', true, true);
                }

                // Debug information
                error_log("User logged in: " . $user['email'] . " with role: " . $user['role']);

                // Redirect based on role
                switch ($user['role']) {
                    case 'admin':
                        header('Location: admin/dashboard.php');
                        exit();
                    case 'super_user':
                        header('Location: superuser/dashboard.php');
                        exit();
                    default:
                        header('Location: user/dashboard.php');
                        exit();
                }
            } else {
                $errors[] = "Invalid email or password";
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $errors[] = "Login failed. Please try again later.";
        }
    }
}

// If we reach here, either login failed or it's a GET request
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management System - Login</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h2>Event Management System</h2>
                <h3>Welcome Back</h3>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <p><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="success-message">
                    <p><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?></p>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="login-form">
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? $prefilled_email ?? ''); ?>"
                           placeholder="Enter your email">
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Enter your password">
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" id="remember">
                        <span>Remember Me</span>
                    </label>
                    <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
                </div>

                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <div class="register-link">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </div>
    </div>
</body>
</html>
