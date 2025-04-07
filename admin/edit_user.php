<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/dbconnection.php';

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $user_id = (int)($_POST['user_id'] ?? 0);
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? 'user';

    // Validation
    if (empty($user_id)) {
        $errors[] = "Invalid user ID";
    }

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
    }

    if (!in_array($role, ['admin', 'super_user', 'user'])) {
        $errors[] = "Invalid role selected";
    }

    // Check if email already exists for another user
    if (empty($errors)) {
        try {
            $stmt = $dbh->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user_id]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Email already exists";
            }
        } catch (PDOException $e) {
            error_log("Email check error: " . $e->getMessage());
            $errors[] = "Error checking email availability";
        }
    }

    // Check if trying to change admin role
    if (empty($errors)) {
        try {
            $stmt = $dbh->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $current_role = $stmt->fetchColumn();

            if ($current_role === 'admin' && $role !== 'admin') {
                // Count remaining admins
                $stmt = $dbh->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
                $admin_count = $stmt->fetchColumn();

                if ($admin_count <= 1) {
                    $errors[] = "Cannot remove admin role from the last admin user";
                }
            }
        } catch (PDOException $e) {
            error_log("Role check error: " . $e->getMessage());
            $errors[] = "Error checking user role";
        }
    }

    // If no errors, update user
    if (empty($errors)) {
        try {
            // Update user
            $stmt = $dbh->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, role = ? WHERE id = ?");
            $stmt->execute([$full_name, $email, $phone, $role, $user_id]);

            $success_message = "User updated successfully";
        } catch (PDOException $e) {
            error_log("Update user error: " . $e->getMessage());
            $errors[] = "Error updating user";
        }
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'success' => empty($errors),
    'message' => empty($errors) ? $success_message : implode("\n", $errors)
]); 