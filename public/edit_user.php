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

// Initialize variables
$username = $role = "";
$errors = [];

// Fetch existing user data
$stmt = $conn->prepare("SELECT username, role FROM users WHERE user_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($username, $role);
    if (!$stmt->fetch()) {
        $errors[] = "User not found.";
    }
    $stmt->close();
} else {
    $errors[] = "Database error: " . $conn->error;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form data
    $role = $_POST['role'];

    // Validate inputs
    if (!in_array($role, ['admin', 'technician'])) {
        $errors[] = "Invalid role selected.";
    }

    // Prevent admin from demoting themselves
    if ($username === $_SESSION['username'] && $role !== 'admin') {
        $errors[] = "You cannot change your own role.";
    }

    // If no errors, update the user's role in the database
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE user_id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $role, $user_id);
            if ($stmt->execute()) {
                // Redirect to manage users page after successful update
                header("Location: manage_users.php?msg=User+updated+successfully");
                exit();
            } else {
                $errors[] = "Error updating user: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit User - Inventory Management System</title>
    <link rel="stylesheet" href="../css/users.css" />
</head>
<body>
    <div class="container">
        <h2>Edit User</h2>

        <!-- Display Errors -->
        <?php if (!empty($errors)): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Edit User Form -->
        <?php if (!empty($username)): ?>
            <form method="POST" action="edit_user.php?id=<?php echo htmlspecialchars($user_id); ?>">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="role">Role:</label>
                    <select name="role" id="role" required>
                        <option value="">-- Select Role --</option>
                        <option value="technician" <?php echo ($role === 'technician') ? 'selected' : ''; ?>>Technician</option>
                        <option value="admin" <?php echo ($role === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>

                <button type="submit" class="submit-btn">Update User</button>
            </form>
        <?php endif; ?>

        <a href="manage_users.php" class="back-link">← Back to Manage Users</a>
    </div>
</body>
</html>
