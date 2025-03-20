<?php
// resend_code.php - Public endpoint to request a new verification code
require_once '../../config.php'; // Database connection

// Include the internal code sending function
require_once 'resend_code_internal.php';

header('Content-Type: application/json'); // Set content type header

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data['email'] ?? '');

if (empty($email)) {
    echo json_encode(["status" => "error", "message" => "Email is required."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "message" => "Invalid email address."]);
    exit;
}

// Process the request and send verification code
$result = sendVerificationCodeToEmail($email, $conn);
echo json_encode($result);
?>