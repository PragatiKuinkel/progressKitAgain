<?php
session_start();
require_once '../includes/dbconnection.php';

try {
    $stmt = $dbh->prepare("
        SELECT m.*, u.full_name, u.role as sender_role
        FROM messages m 
        JOIN users u ON m.sender_id = u.id 
        ORDER BY m.created_at DESC
    ");
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'messages' => $messages]);
} catch (PDOException $e) {
    error_log("Error fetching messages: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}