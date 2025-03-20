<?php
require_once '../config.php'; // Database connection
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
    exit;
}

$userId = $_SESSION['user_id'];

$conn = new mysqli('localhost', 'root', 'root', 'learnix_db');
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

// Query to get the instructor's full name
$query = "SELECT CONCAT(users.first_name, ' ', users.last_name) AS full_name 
          FROM users 
          WHERE users.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($fullName);

if ($stmt->fetch()) {
    echo json_encode(["status" => "success", "full_name" => $fullName]);
} else {
    echo json_encode(["status" => "error", "message" => "Instructor not found"]);
}

$stmt->close();
$conn->close();
?>
