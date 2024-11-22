<?php
$servername = "localhost";
$username = "root";
$password = ""; // Update if necessary
$dbname = "inventory_db";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed"]));
}
?>
