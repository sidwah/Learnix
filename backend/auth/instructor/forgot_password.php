<?php
session_start();

header("Content-Type: application/json"); // Ensure JSON response format

include_once '../../config.php';

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

// Function to generate random verification code
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
        $mail->setFrom('no-reply@learnix.com', 'Learnix Instructor Portal');
        $mail->addAddress($email);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Learnix Instructor Password Reset';
        $mail->Body = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Instructor Password Reset</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .verification-code { 
            background-color: #f4f4f4; 
            padding: 15px; 
            text-align: center; 
            font-size: 24px; 
            letter-spacing: 5px; 
            font-weight: bold; 
            color: #3a66db; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Instructor Password Reset</h2>
        <p>You have requested to reset your password for the Learnix Instructor Portal.</p>
        
        <div class="verification-code">' . $verificationCode . '</div>
        
        <p>This verification code will expire in 10 minutes. If you did not request this password reset, please contact our support team.</p>
        
        <p>Best regards,<br>Learnix Support Team</p>
    </div>
</body>
</html>';

        $mail->AltBody = "Your password reset code is: {$verificationCode}. This code will expire in 10 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error for instructor reset: " . $mail->ErrorInfo);
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

    $email = trim($_POST['email'] ?? '');

    if (!empty($email)) {
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["status" => "error", "message" => "Invalid email format"]);
            exit;
        }
        
        // Check if user exists and is an instructor
        $sql = "SELECT user_id FROM users WHERE email = ? AND role = 'instructor'";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (!$row = mysqli_fetch_assoc($result)) {
            // Changed: Now directly informing the user that the email is not registered as an instructor
            echo json_encode(["status" => "error", "message" => "Email not registered as an instructor"]);
            exit;
        }
        
        $userId = $row['user_id'];
        
        // Check account lockout
        $lockCheckSql = "SELECT * FROM instructor_reset_lockouts WHERE email = ? AND lockout_until > NOW()";
        $lockCheckStmt = mysqli_prepare($conn, $lockCheckSql);
        mysqli_stmt_bind_param($lockCheckStmt, "s", $email);
        mysqli_stmt_execute($lockCheckStmt);
        $lockCheckResult = mysqli_stmt_get_result($lockCheckStmt);
        
        if (mysqli_fetch_assoc($lockCheckResult)) {
            echo json_encode(["status" => "error", "message" => "Too many attempts. Please try again later."]);
            exit;
        }
        
        // Generate verification code
        $verificationCode = generateVerificationCode();
        $expiryTime = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        // Delete any existing reset codes
        $deleteSQL = "DELETE FROM instructor_reset_codes WHERE email = ?";
        $deleteStmt = mysqli_prepare($conn, $deleteSQL);
        mysqli_stmt_bind_param($deleteStmt, "s", $email);
        mysqli_stmt_execute($deleteStmt);
        
        // Insert new verification code
        $insertSQL = "INSERT INTO instructor_reset_codes (email, code, expiry_time) VALUES (?, ?, ?)";
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
?>