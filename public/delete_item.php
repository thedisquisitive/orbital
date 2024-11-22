<?php
// Start the session
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Include the database connection
include_once '../includes/db_connect.php';

// Retrieve the item ID from the URL
$item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if item ID is valid
if ($item_id > 0) {
    // Prepare the DELETE statement
    $stmt = $conn->prepare("DELETE FROM items WHERE item_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $item_id);
        if ($stmt->execute()) {
            // Check if any row was deleted
            if ($stmt->affected_rows > 0) {
                // Redirect to dashboard with success message
                header("Location: dashboard.php?msg=Item+deleted+successfully");
                exit();
            } else {
                // Redirect with error message
                header("Location: dashboard.php?error=Item+not+found");
                exit();
            }
        } else {
            // Redirect with error message
            header("Location: dashboard.php?error=Failed+to+delete+item");
            exit();
        }
        $stmt->close();
    } else {
        // Redirect with error message
        header("Location: dashboard.php?error=Database+error");
        exit();
    }
} else {
    // Redirect with error message
    header("Location: dashboard.php?error=Invalid+item+ID");
    exit();
}

$conn->close();
?>
