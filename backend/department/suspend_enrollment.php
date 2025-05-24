<?php
header('Content-Type: application/json');
require_once '../config.php';

// Include PHPMailer for email functionality
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

// Check if user is logged in and has proper role
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['enrollment_id']) || !isset($input['action'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit();
    }
    
    $enrollment_id = (int)$input['enrollment_id'];
    $action = $input['action'];
    $user_id = $_SESSION['user_id'];
    $suspension_reason = isset($input['reason']) ? $input['reason'] : 'No specific reason provided';
    
    try {
        // Verify that this enrollment belongs to the department head's department
        $verify_query = "SELECT e.enrollment_id, e.user_id, e.status, c.title as course_title, 
                               u.first_name, u.last_name, u.email,
                               d.department_id, d.name as department_name,
                               dh.first_name as head_first_name, dh.last_name as head_last_name,
                               dh.email as head_email
                        FROM enrollments e
                        INNER JOIN courses c ON e.course_id = c.course_id
                        INNER JOIN users u ON e.user_id = u.user_id
                        INNER JOIN departments d ON c.department_id = d.department_id
                        INNER JOIN department_staff ds ON d.department_id = ds.department_id
                        INNER JOIN users dh ON ds.user_id = dh.user_id
                        WHERE e.enrollment_id = ? 
                        AND ds.user_id = ? 
                        AND ds.role = 'head' 
                        AND ds.status = 'active' 
                        AND ds.deleted_at IS NULL
                        AND e.deleted_at IS NULL";
        
        $verify_stmt = $conn->prepare($verify_query);
        $verify_stmt->bind_param("ii", $enrollment_id, $user_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        
        if ($verify_result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Enrollment not found or access denied']);
            exit();
        }
        
        $enrollment_data = $verify_result->fetch_assoc();
        
        if ($action === 'suspend') {
            // Update enrollment status to suspended
            $update_query = "UPDATE enrollments SET status = 'Suspended' WHERE enrollment_id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("i", $enrollment_id);
            
            if ($update_stmt->execute()) {
                // Log the activity
                $log_query = "INSERT INTO user_activity_logs (user_id, activity_type, activity_details, ip_address, user_agent) 
                             VALUES (?, 'enrollment_suspend', ?, ?, ?)";
                $log_stmt = $conn->prepare($log_query);
                $activity_details = "Suspended enrollment for {$enrollment_data['first_name']} {$enrollment_data['last_name']} in course '{$enrollment_data['course_title']}'";
                $ip_address = $_SERVER['REMOTE_ADDR'];
                $user_agent = $_SERVER['HTTP_USER_AGENT'];
                $log_stmt->bind_param("isss", $user_id, $activity_details, $ip_address, $user_agent);
                $log_stmt->execute();
                
                // Send in-app notification to student
                $notification_query = "INSERT INTO user_notifications (user_id, type, title, message, related_id, related_type) 
                                      VALUES (?, 'enrollment_suspended', 'Enrollment Suspended', ?, ?, 'enrollment')";
                $notification_stmt = $conn->prepare($notification_query);
                $notification_message = "Your enrollment in '{$enrollment_data['course_title']}' has been suspended by the {$enrollment_data['department_name']} department. Reason: {$suspension_reason}. Please contact your department for more information.";
                $notification_stmt->bind_param("isi", $enrollment_data['user_id'], $notification_message, $enrollment_id);
                $notification_stmt->execute();
                
                // Send email notification
                $email_sent = sendSuspensionEmail(
                    $enrollment_data['email'],
                    $enrollment_data['first_name'],
                    $enrollment_data['course_title'],
                    $enrollment_data['department_name'],
                    $enrollment_data['head_first_name'] . ' ' . $enrollment_data['head_last_name'],
                    $enrollment_data['head_email'],
                    $suspension_reason
                );
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Enrollment suspended successfully' . ($email_sent ? ' and email notification sent' : ' (email notification failed)'),
                    'student_name' => $enrollment_data['first_name'] . ' ' . $enrollment_data['last_name'],
                    'email_sent' => $email_sent
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to suspend enrollment']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
        
    } catch (Exception $e) {
        error_log("Error suspending enrollment: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while processing the request']);
    }
} else {
   echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

// Function to send enrollment suspension email
function sendSuspensionEmail($studentEmail, $studentName, $courseTitle, $departmentName, $headName, $headEmail, $reason) {
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration (using same settings as instructor registration)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'barrock.sidwah@st.rmu.edu.gh'; // Use your SMTP credentials
        $mail->Password = 'mtltujmsmmlkkxtv'; // Use your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Sender and recipient details
        $mail->setFrom('no-reply@learnix.com', 'Learnix Academic System');
        $mail->addAddress($studentEmail, $studentName);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Course Enrollment Suspension - ' . $courseTitle;
        $mail->Body = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Enrollment Suspension Notice</title>
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
                background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
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
                color: #dc3545;
                margin-top: 0;
                font-size: 20px;
                font-weight: 500;
            }
            
            p {
                margin: 16px 0;
                font-size: 15px;
            }
            
            .suspension-notice {
                background-color: #fff5f5;
                border-left: 4px solid #dc3545;
                padding: 15px 20px;
                margin: 24px 0;
                border-radius: 4px;
            }
            
            .course-details {
                background-color: #f8f9fa;
                border-radius: 6px;
                padding: 20px;
                margin: 24px 0;
            }
            
            .reason-box {
                background-color: #fff8e1;
                border-left: 4px solid #ffc107;
                padding: 15px 20px;
                margin: 24px 0;
                border-radius: 4px;
            }
            
            .contact-info {
                background-color: #e3f2fd;
                border-left: 4px solid #2196f3;
                padding: 15px 20px;
                margin: 24px 0;
                border-radius: 4px;
            }
            
            .important-note {
                font-size: 14px;
                color: #666666;
                font-style: italic;
                margin-top: 24px;
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
                <h1>Learnix Academic System</h1>
            </div>
            
            <div class="email-body">
                <h2>Course Enrollment Suspension Notice</h2>
                
                <p>Dear ' . htmlspecialchars($studentName) . ',</p>
                
                <div class="suspension-notice">
                    <strong>⚠️ Important Notice:</strong> Your enrollment in the course listed below has been suspended by the department administration.
                </div>
                
                <div class="course-details">
                    <strong>Course Information:</strong><br>
                    <strong>Course:</strong> ' . htmlspecialchars($courseTitle) . '<br>
                    <strong>Department:</strong> ' . htmlspecialchars($departmentName) . '<br>
                    <strong>Action Date:</strong> ' . date('F j, Y \a\t g:i A') . '
                </div>
                
                <div class="reason-box">
                    <strong>Reason for Suspension:</strong><br>
                    ' . htmlspecialchars($reason) . '
                </div>
                
                <p><strong>What this means:</strong></p>
                <ul>
                    <li>You will no longer have access to course materials</li>
                    <li>Your progress in the course has been temporarily halted</li>
                    <li>Any certificates or completion records will be on hold</li>
                    <li>You may be able to appeal this decision</li>
                </ul>
                
                <div class="contact-info">
                    <strong>📞 Need to discuss this suspension?</strong><br>
                    Please contact your Department Head:<br>
                    <strong>' . htmlspecialchars($headName) . '</strong><br>
                    Email: <a href="mailto:' . htmlspecialchars($headEmail) . '">' . htmlspecialchars($headEmail) . '</a>
                </div>
                
                <p>If you believe this suspension was made in error or if you have questions about the reinstatement process, please contact your department head as soon as possible.</p>
                
                <p class="important-note">This is an automated message from the Learnix Academic System. Please do not reply directly to this email.</p>
            </div>
            
            <div class="email-footer">
                <p>&copy; 2025 Learnix Academic System. All rights reserved.</p>
                <p>This message was sent regarding your enrollment status.</p>
            </div>
        </div>
    </body>
    </html>';

        // Alternative plain text body
        $mail->AltBody = "Dear $studentName,\n\nYour enrollment in '$courseTitle' has been suspended by the $departmentName department.\n\nReason: $reason\n\nPlease contact your Department Head $headName at $headEmail for more information.\n\nBest regards,\nLearnix Academic System";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>