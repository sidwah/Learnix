<?php
// backend/department/contact-secretary.php

require_once '../config.php';
require_once '../auth/department/department-auth-check.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../PHPMailer/src/Exception.php';
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Sanitize input
    $subject = mysqli_real_escape_string($conn, trim($input['subject'] ?? ''));
    $body = mysqli_real_escape_string($conn, trim($input['body'] ?? ''));
    $ccMyself = $input['cc_myself'] ?? false;
    
    // Validate input
    if (empty($subject) || empty($body)) {
        echo json_encode([
            'success' => false,
            'message' => 'Subject and message body are required.'
        ]);
        exit;
    }
    
    // Get department head's details and department
    $currentUserId = $_SESSION['user_id'];
    $deptQuery = "SELECT ds.department_id, d.name as department_name, u.first_name, u.last_name, u.email as head_email
                  FROM department_staff ds 
                  JOIN departments d ON ds.department_id = d.department_id 
                  JOIN users u ON ds.user_id = u.user_id
                  WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' AND ds.deleted_at IS NULL";
    $deptStmt = $conn->prepare($deptQuery);
    $deptStmt->bind_param("i", $currentUserId);
    $deptStmt->execute();
    $deptResult = $deptStmt->get_result();
    
    if ($deptResult->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'You are not authorized to contact secretaries.'
        ]);
        exit;
    }
    
    $deptRow = $deptResult->fetch_assoc();
    $departmentId = $deptRow['department_id'];
    $departmentName = $deptRow['department_name'];
    $headName = $deptRow['first_name'] . ' ' . $deptRow['last_name'];
    $headEmail = $deptRow['head_email'];
    
    // Get current secretary details
    $secretaryQuery = "SELECT u.user_id, u.first_name, u.last_name, u.email 
                      FROM department_staff ds 
                      JOIN users u ON ds.user_id = u.user_id 
                      WHERE ds.department_id = ? AND ds.role = 'secretary' AND ds.status = 'active' AND ds.deleted_at IS NULL";
    $secretaryStmt = $conn->prepare($secretaryQuery);
    $secretaryStmt->bind_param("i", $departmentId);
    $secretaryStmt->execute();
    $secretaryResult = $secretaryStmt->get_result();
    
    if ($secretaryResult->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'No active secretary found to contact.'
        ]);
        exit;
    }
    
    $secretary = $secretaryResult->fetch_assoc();
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Send email to secretary
        $emailSent = sendMessageToSecretary(
            $secretary['email'], 
            $secretary['first_name'], 
            $secretary['last_name'],
            $headName,
            $headEmail,
            $departmentName,
            $subject,
            $body,
            $ccMyself
        );
        
        // Log communication activity
        $activityQuery = "INSERT INTO department_activity_logs (department_id, user_id, action_type, details) 
                          VALUES (?, ?, 'secretary_contact', ?)";
        $activityStmt = $conn->prepare($activityQuery);
        $details = json_encode([
            'secretary_user_id' => $secretary['user_id'],
            'secretary_email' => $secretary['email'],
            'subject' => $subject,
            'contact_time' => date('Y-m-d H:i:s'),
            'cc_myself' => $ccMyself
        ]);
        $activityStmt->bind_param("iis", $departmentId, $currentUserId, $details);
        $activityStmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        if ($emailSent) {
            echo json_encode([
                'success' => true,
                'message' => 'Message sent successfully to the secretary.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to send message. Please try again later.'
            ]);
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send message: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}

// Send message email to secretary
function sendMessageToSecretary($secretaryEmail, $secretaryFirstName, $secretaryLastName, $headName, $headEmail, $departmentName, $subject, $body, $ccMyself) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'barrock.sidwah@st.rmu.edu.gh';
        $mail->Password = 'mtltujmsmmlkkxtv';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Set sender as the department head
        $mail->setFrom('no-reply@learnix.edu', "Learnix - $headName");
        $mail->addReplyTo($headEmail, $headName);
        
        // Add secretary as recipient
        $mail->addAddress($secretaryEmail, "$secretaryFirstName $secretaryLastName");
        
        // CC department head if requested
        if ($ccMyself) {
            $mail->addCC($headEmail, $headName);
        }

        $mail->isHTML(true);
        $mail->Subject = "[$departmentName Department] $subject";
        
        // Format the message body
        $formattedBody = nl2br(htmlspecialchars($body));
        
        $mail->Body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Poppins', Arial, sans-serif; line-height: 1.6; color: #333333; margin: 0; padding: 0; background-color: #f9f9f9; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); }
        .email-header { background: linear-gradient(135deg, #3a66db 0%, #5f85e5 100%); padding: 20px 30px; text-align: center; }
        .email-body { padding: 30px; }
        .email-footer { background-color: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #666666; }
        h1 { color: #ffffff; margin: 0; font-size: 20px; font-weight: 600; }
        h2 { color: #3a66db; margin-top: 0; font-size: 18px; font-weight: 500; }
        p { margin: 16px 0; font-size: 15px; }
        .message-content { background-color: #f8f9fa; border-left: 4px solid #3a66db; padding: 20px; margin: 20px 0; border-radius: 4px; }
        .sender-info { background-color: #e3f2fd; padding: 15px; border-radius: 4px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Message from Department Head</h1>
        </div>
        <div class="email-body">
            <h2>$subject</h2>
            <p>Dear $secretaryFirstName $secretaryLastName,</p>
            <p>You have received a message from your department head regarding the <strong>$departmentName</strong> department.</p>
            
            <div class="message-content">
                $formattedBody
            </div>
            
            <div class="sender-info">
                <strong>From:</strong> $headName<br>
                <strong>Department:</strong> $departmentName<br>
                <strong>Reply to:</strong> <a href="mailto:$headEmail">$headEmail</a>
            </div>
            
            <p>Please respond directly to this email or contact your department head if you have any questions.</p>
        </div>
        <div class="email-footer">
            <p>&copy; 2025 Learnix. All rights reserved.</p>
            <p>This message was sent through the Learnix department communication system.</p>
        </div>
    </div>
</body>
</html>
HTML;

        // Plain text alternative
        $mail->AltBody = "
Message from Department Head - $departmentName Department

Subject: $subject

Dear $secretaryFirstName $secretaryLastName,

$body

---
From: $headName
Department: $departmentName
Reply to: $headEmail

Please respond directly to this email or contact your department head if you have any questions.
";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Failed to send message to secretary: " . $mail->ErrorInfo);
        return false;
    }
}
?>