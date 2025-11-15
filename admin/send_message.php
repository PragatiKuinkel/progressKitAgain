<?php
global $dbh;
session_start();
require_once '../includes/dbconnection.php';

if ($_POST) {
    $message = $_POST['message'] ?? '';

    if (!empty($message) && isset($_SESSION['user_id'])) {
        try {
            $stmt = $dbh->prepare("INSERT INTO messages (sender_id, sender_name, message) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $_SESSION['full_name'], $message]);
            echo json_encode(['success' => true]);
            exit();
        } catch (PDOException $e) {
            error_log("Error sending message: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error occurred.', 'error' => $e->getMessage(), 'session' => print_r($_SESSION, true)]);
            exit();
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input or session expired.']);
        exit();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}