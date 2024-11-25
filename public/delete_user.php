<?php
// Start the session
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Include the database connection
include_once '../includes/db_connect.php';

// Get the user ID from the URL
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch the username of the user to be deleted
$stmt = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($username);
    if ($stmt->fetch()) {
        $stmt->close();

        // Prevent admin from deleting themselves
        if ($username === $_SESSION['username']) {
            // Redirect with error message
            header("Location: manage_users.php?error=You+cannot+delete+your+own+account");
            exit();
        }

        // Proceed to delete the user
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                // Redirect to manage users page after successful deletion
                header("Location: manage_users.php?msg=User+deleted+successfully");
                exit();
            } else {
                // Redirect with error message
                header("Location: manage_users.php?error=Error+deleting+user");
                exit();
            }
            $stmt->close();
        } else {
            // Redirect with error message
            header("Location: manage_users.php?error=Database+error");
            exit();
        }
    } else {
        $stmt->close();
        // Redirect with error message
        header("Location: manage_users.php?error=User+not+found");
        exit();
    }
} else {
    // Redirect with error message
    header("Location: manage_users.php?error=Database+error");
    exit();
}

$conn->close();
?>
