<?php
session_start();
header('Content-Type: application/json');
include '../../config.php';

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

// Function to generate random reset code
function generateResetCode() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Function to send reset email
function sendResetEmail($email, $firstName, $resetCode, $role) {
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'barrock.sidwah@st.rmu.edu.gh';
        $mail->Password = 'mtltujmsmmlkkxtv'; // Use a secure method
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Sender and recipient
        $mail->setFrom('no-reply@learnix.com', 'Learnix');
        $mail->addAddress($email);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Learnix Password Reset Code';
        
        // Role-specific greeting
        $roleTitle = ($role == 'department_head') ? 'Department Head' : 'Department Secretary';
        
        $mail->Body = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Password Reset</title>
            <style>
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .header {
                    background-color: #4a6cf7;
                    padding: 20px;
                    text-align: center;
                    border-radius: 8px 8px 0 0;
                }
                .header h1 {
                    color: white;
                    margin: 0;
                    font-size: 24px;
                }
                .content {
                    background-color: #ffffff;
                    padding: 30px;
                    border-left: 1px solid #e6e6e6;
                    border-right: 1px solid #e6e6e6;
                }
                .reset-code {
                    background-color: #f7f9fc;
                    border: 1px solid #e6e6e6;
                    border-radius: 6px;
                    font-size: 32px;
                    font-weight: bold;
                    letter-spacing: 5px;
                    color: #333;
                    padding: 15px;
                    margin: 20px 0;
                    text-align: center;
                }
                .info {
                    margin-bottom: 20px;
                    font-size: 16px;
                }
                .expire-warning {
                    color: #f44336;
                    font-size: 14px;
                    font-style: italic;
                }
                .footer {
                    background-color: #f7f9fc;
                    padding: 15px;
                    text-align: center;
                    font-size: 12px;
                    color: #666;
                    border-radius: 0 0 8px 8px;
                    border: 1px solid #e6e6e6;
                    border-top: none;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>LEARNIX</h1>
                </div>
                <div class='content'>
                    <p class='info'>Hello $firstName,</p>
                    <p class='info'>You've requested to reset your password for your Learnix $roleTitle account. Please use the code below to verify your identity:</p>
                    
                    <div class='reset-code'>$resetCode</div>
                    
                    <p class='info'>Enter this code on the password reset verification screen to proceed with creating a new password.</p>
                    
                    <p class='expire-warning'>This reset code will expire in 10 minutes.</p>
                    
                    <p class='info'>If you did not request this password reset, please disregard this email or contact our support team immediately if you believe your account has been compromised.</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Learnix Learning Platform. All rights reserved.</p>
                    <p>This is an automated message, please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->AltBody = "Your password reset code is: $resetCode\n\nThis code will expire in 10 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

// Make sure necessary tables exist
try {
    // Create reset codes table if it doesn't exist
    $createTable = $conn->prepare("CREATE TABLE IF NOT EXISTS department_reset_codes (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        code VARCHAR(6) NOT NULL,
        expiry_time DATETIME NOT NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        KEY email (email),
        KEY code (code),
        KEY expiry_time (expiry_time)
    )");
    $createTable->execute();
    
    // Create reset attempts tracking table
    $createAttemptsTable = $conn->prepare("CREATE TABLE IF NOT EXISTS department_reset_attempts (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        attempts INT NOT NULL DEFAULT 0,
        last_attempt DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $createAttemptsTable->execute();
    
    // Create reset lockouts table
    $createLockoutTable = $conn->prepare("CREATE TABLE IF NOT EXISTS department_reset_lockouts (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        lockout_until DATETIME NOT NULL,
        attempts INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $createLockoutTable->execute();
} catch (Exception $e) {
    error_log("Error creating tables: " . $e->getMessage());
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Get email from POST data
$email = trim($_POST['email'] ?? '');

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
    exit;
}

// Check for account lockout
$checkLockout = $conn->prepare("SELECT lockout_until FROM department_reset_lockouts 
                               WHERE email = ? AND lockout_until > NOW()");
$checkLockout->bind_param("s", $email);
$checkLockout->execute();
$lockoutResult = $checkLockout->get_result();

if ($lockoutResult->num_rows > 0) {
    $lockoutData = $lockoutResult->fetch_assoc();
    $remainingTime = strtotime($lockoutData['lockout_until']) - time();
    $remainingMinutes = ceil($remainingTime / 60);
    
    echo json_encode([
        'status' => 'error',
        'message' => "Password reset is temporarily locked due to too many failed attempts. Please try again in {$remainingMinutes} minutes.",
        'lockout' => true,
        'remaining_minutes' => $remainingMinutes
    ]);
    exit;
}

// Check if the email exists and is associated with a department role
$checkUser = $conn->prepare("SELECT u.user_id, u.first_name, u.role 
                           FROM users u 
                           JOIN department_staff d ON u.user_id = d.user_id 
                           WHERE u.email = ? AND (u.role = 'department_head' OR u.role = 'department_secretary')");
$checkUser->bind_param("s", $email);
$checkUser->execute();
$userResult = $checkUser->get_result();

if ($userResult->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Email not found in our records']);
    exit;
}

// User exists, generate reset code
$userData = $userResult->fetch_assoc();
$resetCode = generateResetCode();
$expiryTime = date('Y-m-d H:i:s', strtotime('+10 minutes'));

// Delete any existing reset codes for this email
$deleteOldCodes = $conn->prepare("DELETE FROM department_reset_codes WHERE email = ?");
$deleteOldCodes->bind_param("s", $email);
$deleteOldCodes->execute();

// Insert new reset code
$insertCode = $conn->prepare("INSERT INTO department_reset_codes (email, code, expiry_time) 
                           VALUES (?, ?, ?)");
$insertCode->bind_param("sss", $email, $resetCode, $expiryTime);
$insertCode->execute();

// Send the reset code email
if (sendResetEmail($email, $userData['first_name'], $resetCode, $userData['role'])) {
    echo json_encode(['status' => 'success', 'message' => 'Password reset code sent']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to send reset code']);
}

$conn->close();
?>