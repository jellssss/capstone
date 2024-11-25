<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

if (isset($_GET['id'])) {
    $employee_id = intval($_GET['id']);
    
    // Update the employee status to 'Archived'
    $stmt = $conn->prepare("UPDATE empreco SET status = 'Archived' WHERE Employee_ID = ?");
    $stmt->bind_param("i", $employee_id);
    
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error: ' . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>
