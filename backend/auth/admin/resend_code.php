<?php
require_once '../../config.php'; // Database connection file

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = trim($data['email'] ?? '');

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        exit(json_encode(["status" => "error", "message" => "Invalid email address."]));
    }

    // Database connection
    $conn = new mysqli('localhost', 'root', 'root', 'learnix_db');
    if ($conn->connect_error) {
        exit(json_encode(["status" => "error", "message" => "Database connection failed."]));
    }

    // Check if user exists and is not verified
    $stmt = $conn->prepare("SELECT user_id, role FROM users WHERE LOWER(email) = LOWER(?) AND is_verified = 0");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($userId, $role);

    if ($stmt->fetch()) {
        $stmt->close();

        // Generate a new verification code
        $newCode = str_pad(rand(0, 99999), 5, "0", STR_PAD_LEFT);
        $newExpiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // Update verification code
        $stmt = $conn->prepare("UPDATE user_verification SET token = ?, expires_at = ?, verified = 0 WHERE user_id = ?");
        $stmt->bind_param("ssi", $newCode, $newExpiresAt, $userId);
        if (!$stmt->execute()) {
            exit(json_encode(["status" => "error", "message" => "Failed to update verification code."]));
        }
        $stmt->close();

        // Send verification email
        if (sendVerificationEmail($email, $newCode)) {
            exit(json_encode(["status" => "success", "message" => "Verification code resent."]));
        } else {
            exit(json_encode(["status" => "error", "message" => "Failed to send verification email."]));
        }
    } else {
        exit(json_encode(["status" => "error", "message" => "Invalid or already verified email."]));
    }

    // $conn->close();
} else {
    exit(json_encode(["status" => "error", "message" => "Invalid request method."]));
}

// Function to send verification email
function sendVerificationEmail($email, $verificationCode) {
    $mail = new PHPMailer(true);

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'barrock.sidwah@st.rmu.edu.gh'; // Your email
        $mail->Password = 'mtltujmsmmlkkxtv'; // Your App Password
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        // Email Details
       $mail->setFrom('no-reply@learnix.com', 'Learnix');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Your New Learnix Verification Code';
        $mail->Body = "<p>Your new verification code is: <strong>$verificationCode</strong></p>
                       <p>This code will expire in 15 minutes. Please use it to verify your account.</p>";

        // Send email
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>
