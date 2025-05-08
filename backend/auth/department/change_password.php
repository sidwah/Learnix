<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../phpmailer/src/Exception.php';
require '../../phpmailer/src/PHPMailer.php';
require '../../phpmailer/src/SMTP.php';

require '../../config.php';  

// Start session
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['currentPassword'];
    $new_password = $_POST['newPassword'];

    // Fetch the current password hash and last update timestamp from the database
    $stmt = $conn->prepare("SELECT password_hash, email, first_name, last_name, updated_at FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($stored_password_hash, $email, $firstName, $lastName, $updated_at);
    $stmt->fetch();
    $stmt->close();

    // Check if user exists
    if (empty($stored_password_hash)) {
        echo "No matching user found.";
        exit;
    }

    // Restrict password changes within 5 minutes (300 seconds)
    if (!empty($updated_at) && (time() - strtotime($updated_at)) < 300) {
        echo "You must wait at least 5 minutes before changing your password again.";
        exit;
    }

    // Verify the current password
    if (!password_verify($current_password, $stored_password_hash)) {
        echo "Current password is incorrect.";
        exit;
    }

    // Prevent reusing the previous password
    if (password_verify($new_password, $stored_password_hash)) {
        echo "You cannot use your previous password.";
        exit;
    }

    // Validate password complexity
    if (strlen($new_password) < 8) {
        echo "Password must be at least 8 characters long.";
        exit;
    }

    // Hash the new password
    $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);

    // Update the password in the database along with the new timestamp
    $update_stmt = $conn->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE user_id = ?");
    $update_stmt->bind_param("si", $new_password_hash, $user_id);

    if ($update_stmt->execute()) {
        echo "Password updated successfully.";

        // Initialize PHPMailer
        $mail = new PHPMailer(true);

        try {
            // SMTP Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'barrock.sidwah@st.rmu.edu.gh';
            $mail->Password = 'mtltujmsmmlkkxtv'; // Use your App Password here
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;

            // Email settings
            $mail->setFrom('no-reply@example.com', 'Learnix');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Your Password Has Been Changed';
            $mail->Body = "
            <html>
            <head>
                <title>Password Change Notification</title>
            </head>
            <body>
                <h2>Hi {$firstName} {$lastName},</h2>
                <p>We wanted to let you know that your password was successfully changed.</p>
                <p>If you did not make this change, please contact our support team immediately to secure your account.</p>
                <p>Thank you for using Learnix!</p>
                <p>Best regards,</p>
                <p>The Learnix Team</p>
            </body>
            </html>";
            $mail->send();
            echo "Notification email sent.";
        } catch (Exception $e) {
            echo "Email could not be sent. Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Error updating password: " . $update_stmt->error;
    }

    $update_stmt->close();
    $conn->close();
}
?>
