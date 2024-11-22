<?php
// Start the session
session_start();

// Check if the user is already logged in
if (isset($_SESSION['token'])) {
    // Optionally, you can verify the token's validity here
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management System</title>
    <link rel="stylesheet" href="../css/base.css" />
</head>
<body>
    <div class="container">
        <h1>Welcome to the Orbital Inventory Management System</h1>
        <p>
            Manage your inventory efficiently and effectively. Track items, categories, and user roles seamlessly with our intuitive system.
        </p>
        <a href="login.php" class="btn">Login</a>
        <a href="register.php" class="btn">Register</a>
    </div>
    <br>
    <div class="footer">
        &copy; <?php echo date("Y"); ?> Orbital Inventory Management System. All rights reserved.
    </div>
</body>
</html>
