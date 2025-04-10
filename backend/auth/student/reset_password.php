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
function generateVerificationCode() {
    return str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT);
}

// Function to send reset password email
function sendResetEmail($email, $verificationCode) {
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

        // Current time for the notification
        $date = date("F j, Y, g:i a");

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Learnix Password Reset Successful';
        $mail->Body = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Successful</title>
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
            background: linear-gradient(135deg, #4CAF50 0%, #8BC34A 100%);
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
            color: #4CAF50;
            margin-top: 0;
            font-size: 20px;
            font-weight: 500;
        }
        
        p {
            margin: 16px 0;
            font-size: 15px;
        }
        
        .success-container {
            background-color: #f1f8e9;
            border-radius: 6px;
            padding: 20px;
            text-align: center;
            margin: 24px 0;
            border: 1px dashed #4CAF50;
        }
        
        .success-icon {
            font-size: 48px;
            color: #4CAF50;
            margin-bottom: 15px;
        }
        
        .security-alert {
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
            color: #4CAF50;
            text-decoration: none;
        }
        
        .login-button {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 4px;
            font-weight: 500;
            margin: 20px 0;
            font-size: 16px;
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
            <h2>Password Reset Successful</h2>
            
            <p>Hello,</p>
            
            <div class="success-container">
                <div class="success-icon">✅</div>
                <p>Your password has been successfully reset on <strong>' . $date . '</strong></p>
            </div>
            
            <p>You can now login to your Learnix account using your new password.</p>
            
            <div style="text-align: center;">
                <a href="https://learnix.com/login" class="login-button">Login to Your Account</a>
            </div>
            
            <div class="security-alert">
                <strong>⚠️ Security Notice:</strong> If you did not reset your password, please contact our support team immediately as your account may have been compromised.
            </div>
            
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
        $mail->AltBody = "Your password has been successfully reset on $date. You can now login to your Learnix account with your new password. If you did not reset your password, please contact our support team immediately.";

        $mail->send();
        return true;
    }  catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

// Function to check if the new password is the same as the old one
function isPasswordSame($userId, $newPassword) {
    global $conn;
    
    // Get the current password hash from the database
    $sql = "SELECT password_hash FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $currentPasswordHash = $row['password_hash'];
        
        // Check if the new password matches the current one
        if (password_verify($newPassword, $currentPasswordHash)) {
            return true; // Passwords are the same
        }
    }
    
    return false; // Passwords are different
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
        $result = mysqli_stmt_get_result($stmt);
        
        if (!$row = mysqli_fetch_assoc($result)) {
            // For security reasons, still return success but don't send email
            // This prevents email enumeration attacks
            echo json_encode(["status" => "success", "message" => "If your email exists in our system, you will receive a verification code"]);
            exit;
        }
        
        $userId = $row['user_id'];
        
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
        
        // Check if we have the new password (for the reset phase)
        if (isset($_POST['password']) && isset($_POST['confirm_password'])) {
            $password = trim($_POST['password']);
            $confirmPassword = trim($_POST['confirm_password']);
            
            // Validate password length
            if (strlen($password) < 8) {
                echo json_encode(["status" => "error", "message" => "Password must be at least 8 characters long"]);
                exit;
            }
            
            // Validate passwords match
            if ($password !== $confirmPassword) {
                echo json_encode(["status" => "error", "message" => "Passwords do not match"]);
                exit;
            }
            
            // Check if the new password is the same as the current one
            if (isPasswordSame($userId, $password)) {
                echo json_encode(["status" => "error", "message" => "New password cannot be the same as the current password"]);
                exit;
            }
            
            // Hash the new password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            // Update the user's password
            $updateSql = "UPDATE users SET password_hash = ? WHERE user_id = ?";
            $updateStmt = mysqli_prepare($conn, $updateSql);
            mysqli_stmt_bind_param($updateStmt, "si", $passwordHash, $userId);
            
            if (mysqli_stmt_execute($updateStmt)) {
                // Password updated successfully
                
                // Clear any reset codes
                $clearCodesSql = "DELETE FROM student_reset_codes WHERE email = ?";
                $clearCodesStmt = mysqli_prepare($conn, $clearCodesSql);
                mysqli_stmt_bind_param($clearCodesStmt, "s", $email);
                mysqli_stmt_execute($clearCodesStmt);
                
                // Send success email
                if (sendResetEmail($email, "")) {
                    echo json_encode([
                        "status" => "success", 
                        "message" => "Password has been successfully reset"
                    ]);
                } else {
                    echo json_encode(["status" => "success", "message" => "Password has been reset, but notification email could not be sent"]);
                }
                exit;
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to update password"]);
                exit;
            }
        }
        
        // If we're here, this is the initial request for a verification code
        
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
                    "message" => "Verification Successful"
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
?>