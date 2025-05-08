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

// Function to generate random verification code
function generateVerificationCode() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
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

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Learnix Admin Password Reset';
        $mail->Body = "<p>You have requested to reset your password.</p>
        <p>Your verification code is: <strong>$verificationCode</strong></p>
        <p>Please enter this code in the verification form to continue with password reset.</p>
        <p>This code will expire in 10 minutes.</p>
        <p>If you did not request this password reset, please ignore this email or contact support.</p>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

// Handle resend code request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['resend']) && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email format"]);
        exit;
    }
    
    // Check if user exists and is an admin
    $sql = "SELECT user_id FROM users WHERE email = ? AND role = 'admin'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) === 0) {
        echo json_encode(["status" => "error", "message" => "Email not found in our records"]);
        exit;
    }
    
    // Check if account is locked for reset attempts
    $lockCheckSql = "SELECT * FROM admin_reset_lockouts WHERE email = ? AND lockout_until > NOW()";
    $lockCheckStmt = mysqli_prepare($conn, $lockCheckSql);
    mysqli_stmt_bind_param($lockCheckStmt, "s", $email);
    mysqli_stmt_execute($lockCheckStmt);
    $lockCheckResult = mysqli_stmt_get_result($lockCheckStmt);
    
    if (mysqli_fetch_assoc($lockCheckResult)) {
        echo json_encode(["status" => "error", "message" => "Too many attempts. Please try again later."]);
        exit;
    }
    
    // Generate a new verification code
    $verificationCode = generateVerificationCode();
    $expiryTime = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    
    // Delete any existing reset codes for this user
    $deleteSQL = "DELETE FROM admin_reset_codes WHERE email = ?";
    $deleteStmt = mysqli_prepare($conn, $deleteSQL);
    mysqli_stmt_bind_param($deleteStmt, "s", $email);
    mysqli_stmt_execute($deleteStmt);
    
    // Insert new verification code
    $insertSQL = "INSERT INTO admin_reset_codes (email, code, expiry_time) VALUES (?, ?, ?)";
    $insertStmt = mysqli_prepare($conn, $insertSQL);
    mysqli_stmt_bind_param($insertStmt, "sss", $email, $verificationCode, $expiryTime);
    
    if (mysqli_stmt_execute($insertStmt)) {
        // Send verification email
        if (sendResetEmail($email, $verificationCode)) {
            echo json_encode(["status" => "success", "message" => "Reset code sent"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to send reset email"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to generate reset code"]);
    }
    
    exit;
}

// Main forgot password process
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
        
        // Check if user exists and is an admin
        $sql = "SELECT user_id FROM users WHERE email = ? AND role = 'admin'";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) === 0) {
            // For security reasons, still return success but don't send email
            // This prevents email enumeration attacks
            echo json_encode(["status" => "success", "message" => "If your email exists in our system, you will receive a reset code"]);
            exit;
        }
        
        // Check if account is locked for reset attempts
        $lockCheckSql = "SELECT * FROM admin_reset_lockouts WHERE email = ? AND lockout_until > NOW()";
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
        $deleteSQL = "DELETE FROM admin_reset_codes WHERE email = ?";
        $deleteStmt = mysqli_prepare($conn, $deleteSQL);
        mysqli_stmt_bind_param($deleteStmt, "s", $email);
        mysqli_stmt_execute($deleteStmt);
        
        // Insert new verification code
        $insertSQL = "INSERT INTO admin_reset_codes (email, code, expiry_time) VALUES (?, ?, ?)";
        $insertStmt = mysqli_prepare($conn, $insertSQL);
        mysqli_stmt_bind_param($insertStmt, "sss", $email, $verificationCode, $expiryTime);
        
        if (mysqli_stmt_execute($insertStmt)) {
            // Send reset email
            if (sendResetEmail($email, $verificationCode)) {
                echo json_encode([
                    "status" => "success", 
                    "message" => "Reset code sent to your email"
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to send reset email"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to generate reset code"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Email is required"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

mysqli_close($conn);
?>