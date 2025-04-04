<?php
session_start();
include('includes/config.php');

if(strlen($_SESSION['odmsaid'])==0) {   
    echo json_encode(['status' => 'error', 'message' => 'Session expired']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aid = $_SESSION['odmsaid'];
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];

    try {
        // First verify current password
        $sql = "SELECT Password FROM tbladmin WHERE ID = :aid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':aid', $aid, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_OBJ);

        if (password_verify($currentPassword, $result->Password)) {
            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update the password
            $sql = "UPDATE tbladmin SET Password = :password WHERE ID = :aid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            $query->bindParam(':aid', $aid, PDO::PARAM_STR);
            
            if ($query->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Password changed successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update password']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?> 