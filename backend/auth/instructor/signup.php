<?php
require_once '../../config.php'; // Database connection file

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture input values
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validate input
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        exit("All fields are required.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        exit("Invalid email address.");
    }

    // Hash the password
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    // Database connection
    $conn = new mysqli('localhost', 'root', 'root', 'learnix_db');
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        exit("Server error. Please try again later.");
    }

    try {
        // Check if email already exists for instructor role
        $stmt = $conn->prepare("SELECT user_id, is_verified FROM users WHERE LOWER(email) = LOWER(?) AND role = 'instructor'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($existingUserId, $isVerified);
        
        if ($stmt->fetch()) {
            $stmt->close();
            if ($isVerified == 0) {
                exit("unverified"); // Prompt for verification
            }
            exit("Email already exists. Please try another email.");
        }
        $stmt->close();

        // Generate a unique username
        $usernameBase = strtolower($firstName . '.' . $lastName);
        $uniqueUsername = $usernameBase;

        $stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
        while (true) {
            $stmt->bind_param("s", $uniqueUsername);
            $stmt->execute();
            if (!$stmt->fetch()) break; // Username is unique
            $uniqueUsername = $usernameBase . rand(1000, 9999);
        }
        $stmt->close();

        // Insert into `users` table
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, username, email, password_hash, role, is_verified) VALUES (?, ?, ?, ?, ?, 'instructor', 0)");
        $stmt->bind_param("sssss", $firstName, $lastName, $uniqueUsername, $email, $passwordHash);
        $stmt->execute();
        $userId = $stmt->insert_id;
        $stmt->close();

        // Insert into `instructors` table
        $stmt = $conn->prepare("INSERT INTO instructors (user_id) VALUES (?)");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();

        // Generate verification code
        $verificationCode = str_pad(rand(0, 99999), 5, "0", STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // Insert into `user_verification` table
        $stmt = $conn->prepare("INSERT INTO user_verification (user_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $userId, $verificationCode, $expiresAt);
        if ($stmt->execute()) {
            if (sendVerificationEmail($email, $verificationCode)) {
                exit("verification_sent");
            } else {
                exit("Failed to send verification email.");
            }
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        exit("Server error. Please try again later.");
    } finally {
        $conn->close();
    }
}

// Function to send verification email - Updated with enhanced template
function sendVerificationEmail($email, $verificationCode) {
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'barrock.sidwah@st.rmu.edu.gh';
        $mail->Password = 'mtltujmsmmlkkxtv'; // Use a secure method instead
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Sender and recipient details
        $mail->setFrom('no-reply@learnix.com', 'Learnix');
        $mail->addAddress($email);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Learnix Account Verification';
        $mail->Body = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Account Verification</title>
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
            
            .email-header img {
                max-width: 150px;
                height: auto;
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
            
            @media screen and (max-width: 600px) {
                .email-container {
                    width: 100%;
                    border-radius: 0;
                }
                
                .email-header, .email-body, .email-footer {
                    padding: 20px;
                }
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="email-header">
                <h1>Learnix</h1>
            </div>
            
            <div class="email-body">
                <h2>Account Verification</h2>
                
                <p>Hello,</p>
                
                <p>Thank you for registering as an instructor with Learnix. To continue with the verification process, please use the verification code below:</p>
                
                <div class="code-container">
                    <div class="verification-code">' . $verificationCode . '</div>
                </div>
                
                <div class="expiry-alert">
                    <strong>⏱️ Time Sensitive:</strong> This verification code will expire in 15 minutes.
                </div>
                
                <p>If you did not register for a Learnix account, please disregard this email or contact our support team immediately if you believe someone may be trying to use your email address.</p>
                
                <p class="support-note">For any questions, please contact our support team at <a href="mailto:support@learnix.com">support@learnix.com</a></p>
            </div>
            
            <div class="email-footer">
                <p>&copy; 2025 Learnix. All rights reserved.</p>
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

        // Alternative plain text body for email clients that don't support HTML
        $mail->AltBody = "You have registered for a Learnix instructor account. Your verification code is: $verificationCode. This code will expire in 15 minutes. If you did not request this registration, please ignore this email or contact support.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>