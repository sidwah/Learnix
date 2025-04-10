<?php
session_start();

header("Content-Type: application/json"); // Ensure JSON response format

include '../../config.php';

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

// Function to generate random verification code (5 digits for students)
function generateVerificationCode()
{
    return str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT);
}

// Function to send reset password email
function sendResetEmail($email, $verificationCode)
{
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
        $mail->Subject = 'Learnix Password Reset';
        $mail->Body = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Password Reset</title>
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
                <h2>Password Reset Requestt</h2>
                
                <p>Hello,</p>
                
                <p>We received a request to reset your password for your Learnix account. To continue with the password reset process, please use the verification code below:</p>
                
                <div class="code-container">
                    <div class="verification-code">' . $verificationCode . '</div>
                </div>
                
                <div class="expiry-alert">
                    <strong>⏱️ Time Sensitive:</strong> This verification code will expire in 10 minutes.
                </div>
                
                <p>If you did not request a password reset, please disregard this email or contact our support team immediately if you believe someone may be trying to access your account.</p>
                
                <p class="support-note">For any questions, please contact our support team at <a href="mailto:support@learnix.com">support@learnix.com</a></p>
            </div>
            
            <div class="email-footer">
                <p>&copy; 2025 Learnix. All rights reserved.</p>
                <p>Our address: Learnix, East Legon, Accra, Ghana</p>
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
        $mail->AltBody = "You have requested to reset your password. Your verification code is: $verificationCode. This code will expire in 10 minutes. If you did not request this password reset, please ignore this email or contact support.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

// Handle forgot password request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if required fields exist in POST request
    if (!isset($_POST['email'])) {
        echo json_encode(["status" => "error", "message" => "Email is required"]);
        exit;
    }

    $email = trim($_POST['email']);

    if (!empty($email)) {
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["status" => "error", "message" => "Invalid email format"]);
            exit;
        }

        // Check if user exists and is a student
        $sql = "SELECT user_id FROM users WHERE email = ? AND role = 'student'";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) === 0) {
            // For security reasons, still return success but don't send email
            // This prevents email enumeration attacks
            echo json_encode(["status" => "success", "message" => "If your email exists in our system, you will receive a verification code"]);
            exit;
        }

        // Check if account is locked for reset attempts
        $lockCheckSql = "SELECT * FROM student_reset_lockouts WHERE email = ? AND lockout_until > NOW()";
        $lockCheckStmt = mysqli_prepare($conn, $lockCheckSql);
        mysqli_stmt_bind_param($lockCheckStmt, "s", $email);
        mysqli_stmt_execute($lockCheckStmt);
        $lockCheckResult = mysqli_stmt_get_result($lockCheckStmt);

        if (mysqli_fetch_assoc($lockCheckResult)) {
            echo json_encode(["status" => "error", "message" => "Too many attempts. Please try again later."]);
            exit;
        }

        // Generate verification code for password reset
        $verificationCode = generateVerificationCode();
        $expiryTime = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // Store the verification code in database
        // First, delete any existing reset codes for this user
        $deleteSQL = "DELETE FROM student_reset_codes WHERE email = ?";
        $deleteStmt = mysqli_prepare($conn, $deleteSQL);
        mysqli_stmt_bind_param($deleteStmt, "s", $email);
        mysqli_stmt_execute($deleteStmt);

        // Insert new verification code
        $insertSQL = "INSERT INTO student_reset_codes (email, code, expiry_time) VALUES (?, ?, ?)";
        $insertStmt = mysqli_prepare($conn, $insertSQL);
        mysqli_stmt_bind_param($insertStmt, "sss", $email, $verificationCode, $expiryTime);

        if (mysqli_stmt_execute($insertStmt)) {
            // Send reset email
            if (sendResetEmail($email, $verificationCode)) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Verification code sent to your email"
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to send verification email"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to generate verification code"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Email is required"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

mysqli_close($conn);
