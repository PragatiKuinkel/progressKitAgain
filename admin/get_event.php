<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Database connection
require_once '../includes/dbconnection.php';

// Check if event ID is provided
if (!isset($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Event ID is required']);
    exit();
}

$eventId = $_GET['id'];

try {
    // Fetch event details with organizer information
    $stmt = $dbh->prepare("
        SELECT e.*, u.full_name as organizer_name, u.role as organizer_role
        FROM events e
        LEFT JOIN users u ON e.organizer_id = u.id
        WHERE e.id = ?
    ");
    $stmt->execute([$eventId]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Event not found']);
        exit();
    }

    // Format date for datetime-local input
    $event['event_date'] = date('Y-m-d\TH:i', strtotime($event['event_date']));

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'event' => $event]);
} catch (PDOException $e) {
    error_log("Error fetching event: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error fetching event details']);
} 