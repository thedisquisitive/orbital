<?php
include_once '../includes/headers.php';
include_once '../includes/db_connect.php';

// Optional: Implement authentication if only admins can register new users
// For simplicity, we'll allow open registration. For production, secure this endpoint.

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    // Get the raw POST data
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Validate input
    if (
        !isset($data['username']) || empty($data['username']) ||
        !isset($data['password']) || empty($data['password']) ||
        !isset($data['role']) || empty($data['role'])
    ) {
        http_response_code(400);
        echo json_encode(["message" => "Username, password, and role are required"]);
        exit;
    }

    $username = $data['username'];
    $password = $data['password'];
    $role = $data['role'];

    // Validate role
    $allowed_roles = ['admin', 'technician'];
    if (!in_array($role, $allowed_roles)) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid role specified"]);
        exit;
    }

    // Check if username already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        http_response_code(409); // Conflict
        echo json_encode(["message" => "Username already exists"]);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert the new user into the database
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $hashed_password, $role);

    if ($stmt->execute()) {
        http_response_code(201); // Created
        echo json_encode(["message" => "User registered successfully"]);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(["message" => "Failed to register user"]);
    }

    $stmt->close();
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["message" => "Method not allowed"]);
}

$conn->close();
?>
