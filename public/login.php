<?php
// Start the session
session_start();

// Check if the user is already logged in
if (isset($_SESSION['token'])) {
    header("Location: dashboard.php");
    exit();
}

// Initialize variables
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Basic validation
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        // Define API base URL
        $apiBaseURL = 'http://localhost/orbital/api';

        // Prepare data for API
        $data = [
            'username' => $username,
            'password' => $password
        ];

        // Initialize cURL
        $ch = curl_init("$apiBaseURL/users.php");

        // Set cURL options
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Set headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        // Execute cURL request
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Decode the response
        $result = json_decode($response, true);

        // Handle the response
        if ($http_code == 200 && isset($result['token'])) {
            // Store the token and role in session
            $_SESSION['token'] = $result['token'];
            $_SESSION['role'] = $result['role'];
            $_SESSION['username'] = $username;

            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            // Set error message
            $error = isset($result['message']) ? $result['message'] : "Login failed.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Inventory Management System</title>
    <link rel="stylesheet" href="../css/base.css"/>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php
            if (!empty($error)) {
                echo '<div class="error">'.htmlspecialchars($error).'</div>';
            }
        ?>
        <form method="POST" action="login.php">
            <div class="input-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required autofocus>
            </div>
            <div class="input-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit" class="submit-btn">Login</button>
        </form>
    </div>
</body>
</html>
