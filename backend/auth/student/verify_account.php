<?php
// verify_account.php - Improved with security and clear messaging
require_once '../../config.php'; // Database connection

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
    exit;
}

// Get JSON data
$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data['email'] ?? '');
$code = trim($data['code'] ?? '');

if (empty($email) || empty($code)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Email and verification code are required.'
    ]);
    exit;
}

// Check rate limiting for verification attempts (IP-based)
$ip = $_SERVER['REMOTE_ADDR'];
$maxAttempts = 5;
$lockoutPeriod = 15; // Minutes

try {
    // Create attempts table if needed
    $conn->query("
        CREATE TABLE IF NOT EXISTS verification_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            email VARCHAR(255) NOT NULL,
            attempt_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX (ip_address),
            INDEX (email),
            INDEX (attempt_time)
        )
    ");

    // Check for too many attempts
    $checkAttempts = $conn->prepare("
        SELECT COUNT(*) FROM verification_attempts 
        WHERE ip_address = ? AND email = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL ? MINUTE)
    ");
    $checkAttempts->bind_param("ssi", $ip, $email, $lockoutPeriod);
    $checkAttempts->execute();
    $checkAttempts->bind_result($attemptCount);
    $checkAttempts->fetch();
    $checkAttempts->close();

    if ($attemptCount >= $maxAttempts) {
        echo json_encode([
            'status' => 'error',
            'message' => "Too many verification attempts. Please try again after {$lockoutPeriod} minutes.",
            'lockout' => true,
            'attempts' => $attemptCount,
            'max_attempts' => $maxAttempts
        ]);
        exit;
    }

    // Log this attempt
    $logAttempt = $conn->prepare("INSERT INTO verification_attempts (ip_address, email, attempt_time) VALUES (?, ?, NOW())");
    $logAttempt->bind_param("ss", $ip, $email);
    $logAttempt->execute();
    $logAttempt->close();
} catch (Exception $e) {
    error_log("Error handling verification rate limiting: " . $e->getMessage());
    // Continue with verification even if rate limiting fails
}

// Get user ID from email
$stmt = $conn->prepare("SELECT user_id, is_verified FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User not found.'
    ]);
    exit;
}

$user = $result->fetch_assoc();
$userId = $user['user_id'];
$isAlreadyVerified = $user['is_verified'];

if ($isAlreadyVerified) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Account is already verified. Please sign in.'
    ]);
    exit;
}

// Check verification code
$stmt = $conn->prepare("
    SELECT verification_id FROM user_verification 
    WHERE user_id = ? AND token = ? AND expires_at > NOW()
");
$stmt->bind_param("is", $userId, $code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $attemptsLeft = $maxAttempts - $attemptCount;
    echo json_encode([
        'status' => 'error',
        'message' => "Invalid or expired verification code. Please try again. ({$attemptsLeft} attempts left)",
        'attempts_left' => $attemptsLeft,
        'total_attempts' => $maxAttempts
    ]);
    exit;
}

// Begin transaction to ensure data consistency
$conn->begin_transaction();

try {
    // Update user as verified and set status to active
    $stmt = $conn->prepare("UPDATE users SET is_verified = 1, status = 'active' WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    if (!$stmt->execute()) {
        throw new Exception("Failed to update user verification status or account status");
    }

    // Mark verification record as verified
    $stmt = $conn->prepare("UPDATE user_verification SET verified = 1 WHERE user_id = ? AND token = ?");
    $stmt->bind_param("is", $userId, $code);
    if (!$stmt->execute()) {
        throw new Exception("Failed to update verification record");
    }

    // Clean up old verification records for this user
    $stmt = $conn->prepare("DELETE FROM user_verification WHERE user_id = ? AND verified = 0");
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    // Also clean up old verification attempts for this user
    $stmt = $conn->prepare("DELETE FROM verification_attempts WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    // Insert Notification
    $notificationSql = "INSERT INTO user_notifications (user_id, title, type, message, created_at, is_read) 
                        VALUES (?, ?, ?, ?, NOW(), 0)";
    $notificationStmt = $conn->prepare($notificationSql);

    $title = 'Verification Successful';
    $type = 'Account Verification';
    $message = 'Your Learnix account has been successfully verified. Welcome aboard!';

    $notificationStmt->bind_param("isss", $userId, $title, $type, $message);

    if (!$notificationStmt->execute()) {
        error_log("Failed to insert verification notification: " . $conn->error);
    }
    $notificationStmt->close();

    // Get user details to personalize the success message
    $stmt = $conn->prepare("SELECT first_name FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($firstName);
    $stmt->fetch();
    $stmt->close();

    echo json_encode([
        'status' => 'success',
        'message' => $firstName ? "Thank you {$firstName}! Your account has been verified successfully!" : "Account verified successfully!",
        'verified' => true
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();

    error_log("Verification error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to verify account: ' . $e->getMessage()
    ]);
}