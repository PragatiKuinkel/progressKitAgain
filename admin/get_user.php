<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/dbconnection.php';

$errors = [];
$user_data = null;

if (isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];

    try {
        $stmt = $dbh->prepare("SELECT id, full_name, email, phone, role, status FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user_data) {
            $errors[] = "User not found";
        }
    } catch (PDOException $e) {
        error_log("Get user error: " . $e->getMessage());
        $errors[] = "Error fetching user data";
    }
} else {
    $errors[] = "User ID not provided";
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'success' => empty($errors),
    'data' => $user_data,
    'message' => empty($errors) ? '' : implode("\n", $errors)
]); 