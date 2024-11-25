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

// Fetch all users
$users = [];
$error_message = "";

$sql = "SELECT user_id, username, role FROM users ORDER BY user_id ASC";
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
} else {
    $error_message = "Error fetching users: " . $conn->error;
}

$conn->close();

// Initialize message variables
$success_message = "";
$display_error_message = "";

// Check for success or error messages in the URL
if (isset($_GET['msg'])) {
    $success_message = htmlspecialchars($_GET['msg']);
}

if (isset($_GET['error'])) {
    $display_error_message = htmlspecialchars($_GET['error']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Users - Inventory Management System</title>
    <link rel="stylesheet" href="../css/users.css" />
</head>
<body>
    <div class="header">
        <div class="welcome">
            Welcome! (Role: <strong><?php echo htmlspecialchars($_SESSION['role']); ?></strong>)
        </div>
        <form action="logout.php" method="POST">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>

    <div class="content">
        <h2>Manage Users</h2>

        <!-- Display Error Message if Any -->
        <?php if (!empty($error_message)): ?>
            <div style="color: red; margin-bottom: 20px;">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Display Success Message -->
        <?php if (!empty($success_message)): ?>
            <div style="color: green; margin-bottom: 20px;">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <!-- Display Error Message -->
        <?php if (!empty($display_error_message)): ?>
            <div style="color: red; margin-bottom: 20px;">
                <?php echo $display_error_message; ?>
            </div>
        <?php endif; ?>


        <!-- Add New User Button -->
        <a href="add_user.php" class="btn">Add New User</a>

        <!-- Users Table -->
        <?php if (!empty($users)): ?>
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td>
                                <a href="edit_user.php?id=<?php echo htmlspecialchars($user['user_id']); ?>" class="btn btn-edit">Edit</a>
                                <!-- Prevent admin from deleting themselves -->
                                <?php if ($user['username'] !== $_SESSION['username']): ?>
                                    <a href="delete_user.php?id=<?php echo htmlspecialchars($user['user_id']); ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                <?php else: ?>
                                    <span style="color: gray;">Cannot delete self</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No users found.</p>
        <?php endif; ?>

        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>
</body>
</html>
