<?php
session_start();
include('includes/config.php');

if(strlen($_SESSION['odmsaid'])==0) {   
    echo json_encode(['status' => 'error', 'message' => 'Session expired']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aid = $_SESSION['odmsaid'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    // Split fullname into first and last name
    $nameParts = explode(' ', $fullname, 2);
    $firstName = $nameParts[0];
    $lastName = isset($nameParts[1]) ? $nameParts[1] : '';

    try {
        $sql = "UPDATE tbladmin SET FirstName = :firstName, LastName = :lastName, Email = :email, Phone = :phone WHERE ID = :aid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':firstName', $firstName, PDO::PARAM_STR);
        $query->bindParam(':lastName', $lastName, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':phone', $phone, PDO::PARAM_STR);
        $query->bindParam(':aid', $aid, PDO::PARAM_STR);
        
        if ($query->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update profile']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?> 