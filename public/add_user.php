<?php
// Start the session
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Include the database connection
include_once '../includes/db_connect.php';

// Initialize variables
$username = $password = $confirm_password = $role = "";
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form data
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    // Validate inputs
    if (empty($username)) {
        $errors[] = "Username is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    if (!in_array($role, ['admin', 'technician'])) {
        $errors[] = "Invalid role selected.";
    }

    // Check if username already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Username already exists.";
        }
        $stmt->close();
    } else {
        $errors[] = "Database error: " . $conn->error;
    }

    // If no errors, insert the new user into the database
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sss", $username, $hashed_password, $role);
            if ($stmt->execute()) {
                // Redirect to manage users page after successful insertion
                header("Location: manage_users.php?msg=User+added+successfully");
                exit();
            } else {
                $errors[] = "Error adding user: " . $stmt->error;
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
    <title>Add New User - Inventory Management System</title>
    <link rel="stylesheet" href="../css/users.css" />
</head>
<body>
    <div class="container">
        <h2>Add New User</h2>

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

        <!-- Add User Form -->
        <form method="POST" action="add_user.php">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>

            <div class="form-group">
                <label for="role">Role:</label>
                <select name="role" id="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="technician" <?php echo ($role === 'technician') ? 'selected' : ''; ?>>Technician</option>
                    <option value="admin" <?php echo ($role === 'admin') ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>

            <button type="submit" class="submit-btn">Add User</button>
        </form>

        <a href="manage_users.php" class="back-link">← Back to Manage Users</a>
    </div>
</body>
</html>
