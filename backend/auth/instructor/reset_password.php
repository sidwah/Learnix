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

// Function to send password reset confirmation email
function sendResetConfirmationEmail($email, $firstName)
{
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'barrock.sidwah@st.rmu.edu.gh';
        $mail->Password = 'mtltujmsmmlkkxtv';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Sender and recipient details
        $mail->setFrom('no-reply@learnix.com', 'Learnix Instructor Portal');
        $mail->addAddress($email);

        // Current time for the notification
        $date = date("F j, Y, g:i a");

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Learnix Instructor Password Reset Successful';
        $mail->Body = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Password Reset Successful</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .container { background-color: #f4f4f4; padding: 20px; border-radius: 8px; }
        .success-message { color: #4CAF50; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Password Reset Confirmation</h2>
        <p>Hello ' . htmlspecialchars($firstName) . ',</p>
        <p>Your password for the Learnix Instructor Portal was successfully reset on <span class="success-message">' . $date . '</span>.</p>
        <p>If you did not make this change, please contact our support team immediately.</p>
        <p>Best regards,<br>Learnix Support Team</p>
    </div>
</body>
</html>';

        $mail->AltBody = "Your password for the Learnix Instructor Portal was reset on $date. If you did not make this change, please contact support.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Password Reset Confirmation Email Error: " . $mail->ErrorInfo);
        return false;
    }
}

// Validate session and reset request security
function validateResetRequest($conn)
{
    // Check if reset was initiated and verified
    if (!isset($_SESSION['reset_verified']) || !$_SESSION['reset_verified']) {
        return ['status' => false, 'message' => 'Unauthorized reset attempt'];
    }

    // Check session timeout (15 minutes)
    $currentTime = time();
    if (!isset($_SESSION['reset_time']) || ($currentTime - $_SESSION['reset_time'] > 900)) {
        return ['status' => false, 'message' => 'Reset session expired'];
    }

    // Verify user exists
    if (!isset($_SESSION['reset_user_id']) || !isset($_SESSION['reset_email'])) {
        return ['status' => false, 'message' => 'Invalid reset session'];
    }

    return ['status' => true];
}

// Handle password reset request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate session
    $sessionValidation = validateResetRequest($conn);
    if (!$sessionValidation['status']) {
        echo json_encode([
            'status' => 'error',
            'message' => $sessionValidation['message']
        ]);
        exit;
    }

    $userId = $_SESSION['reset_user_id'];
    $email = $_SESSION['reset_email'];

    // Get POST parameters
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    // Validate input
    if (empty($password) || empty($confirmPassword)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'All fields are required'
        ]);
        exit;
    }

    // Validate password match
    if ($password !== $confirmPassword) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Passwords do not match'
        ]);
        exit;
    }

    // Validate password strength
    if (strlen($password) < 8) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Password must be at least 8 characters long'
        ]);
        exit;
    }

    // Fetch current password hash to prevent reuse
    $checkCurrentPwdStmt = $conn->prepare("SELECT password_hash FROM users WHERE user_id = ?");
    $checkCurrentPwdStmt->bind_param("i", $userId);
    $checkCurrentPwdStmt->execute();
    $result = $checkCurrentPwdStmt->get_result();
    $user = $result->fetch_assoc();

    // Check if new password is the same as current password
    if (password_verify($password, $user['password_hash'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'New password cannot be the same as the current password'
        ]);
        exit;
    }

    // Hash new password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Update password
        $updateStmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        $updateStmt->bind_param("si", $hashedPassword, $userId);
        $updateStmt->execute();

        // Delete any existing reset codes for this email
        $deleteCodeStmt = $conn->prepare("DELETE FROM instructor_reset_codes WHERE email = ?");
        $deleteCodeStmt->bind_param("s", $email);
        $deleteCodeStmt->execute();

        // Fetch first name for personalized email
        $nameStmt = $conn->prepare("SELECT first_name FROM users WHERE user_id = ?");
        $nameStmt->bind_param("i", $userId);
        $nameStmt->execute();
        $nameResult = $nameStmt->get_result();
        $userData = $nameResult->fetch_assoc();

        // Send confirmation email
        $emailSent = sendResetConfirmationEmail($email, $userData['first_name']);

        // Commit transaction
        $conn->commit();

        // Insert notification after successful password reset
// Insert notification after successful password reset
$notificationSql = "
    INSERT INTO user_notifications (user_id, title, type, message, created_at, is_read)
    VALUES (?, ?, ?, ?, NOW(), 0)
";
$notificationStmt = $conn->prepare($notificationSql);

$title = 'Password Reset Successful'; // ✅ NEW
$type = 'Password Reset';
$message = 'Your instructor account password was successfully reset. If this wasn\'t you, contact support immediately.';

// Notice 4 parameters now
$notificationStmt->bind_param("isss", $userId, $title, $type, $message);

if (!$notificationStmt->execute()) {
    error_log("Failed to insert instructor password reset notification: " . $conn->error);
}

$notificationStmt->close();



        // Clear reset session
        unset($_SESSION['reset_verified']);
        unset($_SESSION['reset_user_id']);
        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_time']);

        echo json_encode([
            'status' => 'success',
            'message' => 'Password reset successfully',
            'email_sent' => $emailSent
        ]);
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();

        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to reset password: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}

mysqli_close($conn);
