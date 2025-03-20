<?php
// signin.php - Enhanced with advanced security, attempt tracking and better user feedback
session_start();
require_once '../../config.php'; // Database connection

// Set response content type
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

// Get the submitted form data
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Basic validation
if (empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Email and password are required.']);
    exit;
}

// Check for account lockout due to too many failed attempts
$ip = $_SERVER['REMOTE_ADDR'];
$maxAttempts = 5; // Maximum attempts allowed
$lockoutDuration = 15; // Minutes

// Create login_attempts table if it doesn't exist
try {
    $createTable = $conn->prepare("CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        email VARCHAR(255) NOT NULL,
        success TINYINT(1) NOT NULL DEFAULT 0,
        attempt_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX (ip_address),
        INDEX (email),
        INDEX (attempt_time)
    )");
    $createTable->execute();
    
    // Get failed attempts count
    $checkLockout = $conn->prepare("SELECT COUNT(*) AS attempts FROM login_attempts 
        WHERE ip_address = ? AND email = ? AND success = 0 
        AND attempt_time > DATE_SUB(NOW(), INTERVAL ? MINUTE)");
    $checkLockout->bind_param("ssi", $ip, $email, $lockoutDuration);
    $checkLockout->execute();
    $checkLockout->bind_result($failedAttempts);
    $checkLockout->fetch();
    $checkLockout->close();
    
    $attemptsLeft = $maxAttempts - $failedAttempts;
    
    if ($failedAttempts >= $maxAttempts) {
        // Calculate time remaining in lockout
        $timeQuery = $conn->prepare("SELECT TIMESTAMPDIFF(MINUTE, MAX(attempt_time), DATE_ADD(MAX(attempt_time), INTERVAL ? MINUTE)) as remain 
            FROM login_attempts WHERE ip_address = ? AND email = ? AND success = 0");
        $timeQuery->bind_param("iss", $lockoutDuration, $ip, $email);
        $timeQuery->execute();
        $timeQuery->bind_result($remainingMinutes);
        $timeQuery->fetch();
        $timeQuery->close();
        
        echo json_encode([
            'status' => 'error', 
            'message' => "Account temporarily locked due to too many failed attempts. Try again in {$remainingMinutes} minutes.",
            'lockout' => true,
            'remaining_minutes' => $remainingMinutes
        ]);
        exit;
    }
} catch (Exception $e) {
    error_log("Error checking login attempts: " . $e->getMessage());
}

// Query the database to check if the email exists
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = 'student'");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // User exists, fetch user data
    $user = $result->fetch_assoc();

    // Log this login attempt
    try {
        $logAttempt = $conn->prepare("INSERT INTO login_attempts (ip_address, email, success, attempt_time) VALUES (?, ?, ?, NOW())");
        $success = 0; // Default to failure, update on success
        $logAttempt->bind_param("ssi", $ip, $email, $success);
        $logAttempt->execute();
        $attemptId = $conn->insert_id;
        $logAttempt->close();
    } catch (Exception $e) {
        error_log("Error logging login attempt: " . $e->getMessage());
    }

    // Check account status
    if ($user['status'] !== 'active') {
        echo json_encode([
            'status' => 'error', 
            'message' => 'This account has been suspended or banned.',
            'attempts_left' => $attemptsLeft
        ]);
        exit;
    }

    // Verify the password
    if (password_verify($password, $user['password_hash'])) {
        // Update login attempt to success
        try {
            if (isset($attemptId)) {
                $updateAttempt = $conn->prepare("UPDATE login_attempts SET success = 1 WHERE id = ?");
                $updateAttempt->bind_param("i", $attemptId);
                $updateAttempt->execute();
                $updateAttempt->close();
            }
        } catch (Exception $e) {
            error_log("Error updating login attempt: " . $e->getMessage());
        }

        // Check if account is verified
        if ($user['is_verified'] == 0) {
            // Generate and send verification code immediately for better UX
            require_once 'resend_code_internal.php';
            $verificationResult = sendVerificationCodeToEmail($email, $conn);
            
            $responseData = [
                'status' => 'error', 
                'message' => 'Account not verified. Please verify your email.',
                'verification_required' => true,
                'email' => $email
            ];
            
            if ($verificationResult['status'] === 'success') {
                $responseData['code_sent'] = true;
            } else {
                $responseData['code_sent'] = false;
                $responseData['code_error'] = $verificationResult['message'];
            }
            
            echo json_encode($responseData);
            exit;
        }

        // Successful login, create a session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['signin'] = true;
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Set last login timestamp
        $updateLogin = $conn->prepare("UPDATE users SET updated_at = NOW() WHERE user_id = ?");
        $updateLogin->bind_param("i", $user['user_id']);
        $updateLogin->execute();
        $updateLogin->close();

        // Send a success response
        echo json_encode([
            'status' => 'success', 
            'message' => 'Login successful', 
            'user' => [
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name']
            ]
        ]);
        exit;
    } else {
        // Invalid password
        echo json_encode([
            'status' => 'error', 
            'message' => 'Invalid email or password. Please try again.', 
            'attempts_left' => $attemptsLeft,
            'total_attempts' => $maxAttempts
        ]);
        exit;
    }
} else {
    // Email not found - for security it's better not to specify whether email or password was wrong
    echo json_encode([
        'status' => 'error', 
        'message' => 'Invalid email or password. Please try again.', 
        // 'attempts_left' => $attemptsLeft,
        // 'total_attempts' => $maxAttempts
    ]);
    exit;
}
?>

