<?php
require_once '../../config.php'; // Database connection
header('Content-Type: application/json'); // Ensure JSON response

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    $conn = new mysqli('localhost', 'root', 'root', 'learnix_db');

    if ($conn->connect_error) {
        error_log("Database Connection Error: " . $conn->connect_error);
        echo json_encode(["status" => "error", "message" => "Database connection failed."]);
        exit;
    }

    try {
        // Check if the email exists and is unverified
        $stmt = $conn->prepare("SELECT u.user_id, u.first_name, u.is_verified 
                              FROM users u 
                              JOIN instructors i ON u.user_id = i.user_id 
                              WHERE LOWER(u.email) = LOWER(?) AND u.role = 'instructor'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($userId, $firstName, $isVerified);
        
        if ($stmt->fetch()) {
            $stmt->close();
            
            // If user is already verified, return an appropriate message
            if ($isVerified == 1) {
                echo json_encode(["status" => "error", "message" => "Your account is already verified. Please sign in."]);
                exit;
            }
            
            // Check for excessive verification attempts
            $attemptsStmt = $conn->prepare("SELECT attempts, last_attempt FROM instructor_verification_attempts WHERE email = ?");
            $attemptsStmt->bind_param("s", $email);
            $attemptsStmt->execute();
            $attemptsStmt->store_result();
            
            if ($attemptsStmt->num_rows > 0) {
                $attemptsStmt->bind_result($attempts, $lastAttempt);
                $attemptsStmt->fetch();
                $attemptsStmt->close();
                
                $lastAttemptTime = strtotime($lastAttempt);
                $currentTime = time();
                $timeDifference = $currentTime - $lastAttemptTime;
                
                // If last attempt was less than 2 minutes ago and they've attempted more than 3 times
                if ($timeDifference < 120 && $attempts >= 3) {
                    echo json_encode([
                        "status" => "error", 
                        "message" => "Too many verification attempts. Please wait before trying again."
                    ]);
                    exit;
                }
                
                // Update attempts counter
                $updateStmt = $conn->prepare("UPDATE instructor_verification_attempts SET attempts = attempts + 1, last_attempt = NOW() WHERE email = ?");
                $updateStmt->bind_param("s", $email);
                $updateStmt->execute();
                $updateStmt->close();
            } else {
                // Create new attempts record
                $insertStmt = $conn->prepare("INSERT INTO instructor_verification_attempts (email, attempts, last_attempt) VALUES (?, 1, NOW())");
                $insertStmt->bind_param("s", $email);
                $insertStmt->execute();
                $insertStmt->close();
            }
            
            // Generate new verification code
            $verificationCode = str_pad(rand(0, 99999), 5, "0", STR_PAD_LEFT);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            
            // Insert a new verification token in user_verification table
            $tokenStmt = $conn->prepare("INSERT INTO user_verification (user_id, token, expires_at, verified) 
                                      VALUES (?, ?, ?, 0) 
                                      ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at), verified = 0");
            $tokenStmt->bind_param("iss", $userId, $verificationCode, $expiresAt);
            
            if ($tokenStmt->execute()) {
                $tokenStmt->close();
                
                if (sendVerificationEmail($email, $firstName, $verificationCode)) {
                    echo json_encode(["status" => "success", "message" => "Verification code sent to your email."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Failed to send verification email."]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to generate verification code."]);
            }
        } else {
            $stmt->close();
            echo json_encode(["status" => "error", "message" => "No instructor account found with this email."]);
        }
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        echo json_encode(["status" => "error", "message" => "An error occurred. Please try again later."]);
    } finally {
        $conn->close();
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}

// Function to send verification email
function sendVerificationEmail($email, $firstName, $verificationCode) {
    $mail = new PHPMailer(true);
    
    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'barrock.sidwah@st.rmu.edu.gh'; // Your Gmail
        $mail->Password = 'mtltujmsmmlkkxtv'; // Your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        
        // Email Details
        $mail->setFrom('no-reply@learnix.com', 'Learnix');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Your Learnix Verification Code';
        
        // HTML email body with professional formatting
        $mail->Body = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Verify Your Learnix Account</title>
            <style>
                @import url(\'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap\');
                
                body {
                    font-family: \'Poppins\', Arial, sans-serif;
                    line-height: 1.6;
                    color: #333333;
                    margin: 0;
                    padding: 0;
                    background-color: #f9f9f9;
                }
                
                .email-container {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #ffffff;
                    border-radius: 8px;
                    overflow: hidden;
                    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                }
                
                .email-header {
                    background: linear-gradient(135deg, #3a66db 0%, #5f85e5 100%);
                    padding: 30px;
                    text-align: center;
                }
                
                .email-body {
                    padding: 30px;
                }
                
                .email-footer {
                    background-color: #f5f5f5;
                    padding: 20px;
                    text-align: center;
                    font-size: 12px;
                    color: #666666;
                }
                
                h1 {
                    color: #ffffff;
                    margin: 0;
                    font-size: 24px;
                    font-weight: 600;
                }
                
                h2 {
                    color: #3a66db;
                    margin-top: 0;
                    font-size: 20px;
                    font-weight: 500;
                }
                
                p {
                    margin: 16px 0;
                    font-size: 15px;
                }
                
                .code-container {
                    background-color: #f5f7fa;
                    border-radius: 6px;
                    padding: 20px;
                    text-align: center;
                    margin: 24px 0;
                    border: 1px dashed #d1d9e6;
                }
                
                .verification-code {
                    font-family: monospace;
                    font-size: 32px;
                    letter-spacing: 5px;
                    color: #3a66db;
                    font-weight: 600;
                }
                
                .expiry-alert {
                    background-color: #fff8e1;
                    border-left: 4px solid #ffc107;
                    padding: 12px 15px;
                    margin: 24px 0;
                    font-size: 14px;
                    color: #856404;
                }
                
                .support-note {
                    font-size: 14px;
                    color: #666666;
                    font-style: italic;
                    margin-top: 24px;
                }
                
                .social-icons {
                    margin-top: 20px;
                }
                
                .social-icons a {
                    display: inline-block;
                    margin: 0 10px;
                    color: #3a66db;
                    text-decoration: none;
                }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="email-header">
                    <h1>Learnix</h1>
                </div>
                
                <div class="email-body">
                    <h2>Hi ' . $firstName . ',</h2>
                    
                    <p>Thank you for registering as an instructor on Learnix. To complete your registration, please use the verification code below:</p>
                    
                    <div class="code-container">
                        <div class="verification-code">' . $verificationCode . '</div>
                    </div>
                    
                    <div class="expiry-alert">
                        <strong>⏱️ Time Sensitive:</strong> This verification code will expire in 15 minutes.
                    </div>
                    
                    <p>If you did not request this verification code, please disregard this email.</p>
                    
                    <p class="support-note">For any questions, please contact our support team at <a href="mailto:support@learnix.com">support@learnix.com</a></p>
                </div>
                
                <div class="email-footer">
                    <p>&copy; ' . date('Y') . ' Learnix. All rights reserved.</p>
                    <p>Our address: 123 Education Lane, Learning City, ED 12345</p>
                    <div class="social-icons">
                        <a href="#">Twitter</a> | 
                        <a href="#">Facebook</a> | 
                        <a href="#">Instagram</a>
                    </div>
                </div>
            </div>
        </body>
        </html>';
        
        // Plain text alternative
        $mail->AltBody = "Hi {$firstName},\n\nYour Learnix verification code is: {$verificationCode}\n\nThis code will expire in 15 minutes.\n\nIf you did not request this code, please ignore this email.\n\nBest regards,\nThe Learnix Team";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>