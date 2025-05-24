<?php
// Authentication check
require_once '../../backend/auth/admin/admin-auth-check.php';
require_once '../../backend/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../backend/PHPMailer/src/Exception.php';
require '../../backend/PHPMailer/src/PHPMailer.php';
require '../../backend/PHPMailer/src/SMTP.php';

// Ensure we're processing a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Initialize response array
$response = [
    'status' => 'error',
    'message' => 'Failed to process financial approval'
];

// Get and validate input
$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';
$instructor_share = isset($_POST['instructor_share']) ? floatval($_POST['instructor_share']) : 0;
$feedback = isset($_POST['feedback']) ? trim($_POST['feedback']) : '';

// Debug log
error_log("Received parameters: course_id={$course_id}, action={$action}, instructor_share={$instructor_share}");

// Validate inputs
if ($course_id <= 0) {
    $response['message'] = 'Invalid course ID';
    echo json_encode($response);
    exit;
}

// Validate action
if (!in_array($action, ['approve', 'reject'])) {
    $response['message'] = 'Invalid action specified';
    echo json_encode($response);
    exit;
}

// For approval, validate instructor share
if ($action === 'approve' && ($instructor_share <= 0 || $instructor_share > 100)) {
    $response['message'] = 'Invalid instructor share percentage. Must be between 1 and 100.';
    echo json_encode($response);
    exit;
}

// Function to send financial status email using PHPMailer
function sendFinancialStatusEmail($email, $firstName, $courseTitle, $departmentName, $action, $instructorShare = 0, $feedback = '') {
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
        $mail->setFrom('no-reply@learnix.com', 'Learnix');
        $mail->addAddress($email);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Learnix - Course Financial Status Update';
        
        $platformShare = 100 - $instructorShare;
        $isApproved = ($action === 'approve');
        
        $mail->Body = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Course Financial Status Update</title>
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
                
                .email-header h1 {
                    color: #ffffff;
                    margin: 0;
                    font-size: 24px;
                    font-weight: 600;
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
                
                .course-title {
                    font-weight: 600;
                    color: #333;
                }
                
                .status-badge {
                    display: inline-block;
                    padding: 8px 16px;
                    border-radius: 6px;
                    font-weight: 600;
                    font-size: 14px;
                    color: #ffffff;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
                
                .approved-badge {
                    background: linear-gradient(135deg, #28a745, #20c997);
                }
                
                .rejected-badge {
                    background: linear-gradient(135deg, #dc3545, #e74c3c);
                }
                
                .feedback-box {
                    background-color: #f8f9fa;
                    border-left: 4px solid #3a66db;
                    padding: 20px;
                    margin: 24px 0;
                    border-radius: 0 6px 6px 0;
                }
                
                .financial-info {
                    background: linear-gradient(135deg, #e8f5e8, #f0f9ff);
                    border: 1px solid #d1ecf1;
                    border-radius: 8px;
                    padding: 20px;
                    margin: 24px 0;
                }
                
                .revenue-row {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 8px 0;
                    border-bottom: 1px solid #e9ecef;
                }
                
                .revenue-row:last-child {
                    border-bottom: none;
                }
                
                .rejection-info {
                    background: linear-gradient(135deg, #ffeaea, #fff5f5);
                    border: 1px solid #f5c6cb;
                    border-radius: 8px;
                    padding: 20px;
                    margin: 24px 0;
                }
                
                .alert-icon {
                    font-size: 18px;
                    margin-right: 8px;
                }
                
                .next-steps {
                    background-color: #f8f9fa;
                    border-radius: 6px;
                    padding: 16px;
                    margin: 20px 0;
                }
                
                .next-steps h4 {
                    margin-top: 0;
                    color: #495057;
                }
                
                .next-steps ul {
                    margin-bottom: 0;
                    padding-left: 20px;
                }
                
                .next-steps li {
                    margin: 8px 0;
                }
                
                @media screen and (max-width: 600px) {
                    .email-container {
                        width: 100%;
                        border-radius: 0;
                    }
                    
                    .email-header, .email-body, .email-footer {
                        padding: 20px;
                    }
                    
                    .revenue-row {
                        flex-direction: column;
                        align-items: flex-start;
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
                    <h2>Course Financial Status Update</h2>
                    
                    <p>Hello ' . htmlspecialchars($firstName) . ',</p>
                    
                    <p>This is to inform you that the financial review for your course has been completed.</p>
                    
                    <p><strong>Course:</strong> <span class="course-title">"' . htmlspecialchars($courseTitle) . '"</span></p>
                    <p><strong>Department:</strong> ' . htmlspecialchars($departmentName) . '</p>';
        
        if ($isApproved) {
            $mail->Body .= '
                    <div class="financial-info">
                        <h3 style="margin-top: 0; color: #28a745;">
                            <span class="alert-icon">✅</span>Financial Terms Approved
                        </h3>
                        <p style="margin-bottom: 16px;">Congratulations! Your course has been <strong>financially approved</strong> with the following revenue distribution:</p>
                        
                        <div class="revenue-row">
                            <strong>Instructor Share:</strong>
                            <span style="font-weight: 600; color: #28a745;">' . $instructorShare . '%</span>
                        </div>
                        <div class="revenue-row">
                            <strong>Platform Share:</strong>
                            <span style="font-weight: 600; color: #6c757d;">' . $platformShare . '%</span>
                        </div>
                        
                        <div style="text-align: center; margin-top: 16px;">
                            <span class="status-badge approved-badge">Approved</span>
                        </div>
                    </div>
                    
                    <div class="next-steps">
                        <h4>📋 Next Steps</h4>
                        <ul>
                            <li>Assign instructors to your course</li>
                            <li>Begin content development and curriculum design</li>
                            <li>Upload course materials and resources</li>
                            <li>Submit for content review when ready</li>
                        </ul>
                    </div>';
        } else {
            $mail->Body .= '
                    <div class="rejection-info">
                        <h3 style="margin-top: 0; color: #dc3545;">
                            <span class="alert-icon">❌</span>Financial Terms Rejected
                        </h3>
                        <p style="margin-bottom: 16px;">Unfortunately, your course has been <strong>rejected</strong> on financial grounds.</p>
                        
                        <div style="text-align: center; margin-top: 16px;">
                            <span class="status-badge rejected-badge">Rejected</span>
                        </div>
                    </div>
                    
                    <div class="next-steps">
                        <h4>🔄 What You Can Do</h4>
                        <ul>
                            <li>Review the feedback provided below</li>
                            <li>Make necessary adjustments to your course proposal</li>
                            <li>Resubmit your course for financial review</li>
                            <li>Contact support if you need clarification</li>
                        </ul>
                    </div>';
        }
        
        if (!empty($feedback)) {
            $mail->Body .= '
                    <div class="feedback-box">
                        <h4 style="margin-top: 0; color: #3a66db;">
                            <span class="alert-icon">💬</span>Admin Feedback
                        </h4>
                        <p style="margin-bottom: 0; font-style: italic;">"' . nl2br(htmlspecialchars($feedback)) . '"</p>
                    </div>';
        }
        
        $mail->Body .= '
                    <p>Please log in to your department dashboard to view more details and take any necessary actions.</p>
                    
                    <p style="font-size: 14px; color: #666666; font-style: italic; margin-top: 24px;">
                        For any questions or concerns, please contact our support team at 
                        <a href="mailto:support@learnix.com" style="color: #3a66db;">support@learnix.com</a>
                    </p>
                </div>
                
                <div class="email-footer">
                    <p>&copy; 2025 Learnix. All rights reserved.</p>
                    <p>Building the future of education, one course at a time.</p>
                    <div style="margin-top: 15px;">
                        <a href="#" style="color: #3a66db; text-decoration: none; margin: 0 10px;">Twitter</a>
                        <a href="#" style="color: #3a66db; text-decoration: none; margin: 0 10px;">LinkedIn</a>
                        <a href="#" style="color: #3a66db; text-decoration: none; margin: 0 10px;">Facebook</a>
                    </div>
                </div>
            </div>
        </body>
        </html>';

        // Alternative plain text body
        $statusText = $isApproved ? 'APPROVED' : 'REJECTED';
        $mail->AltBody = "Course Financial Status Update\n\n" .
                        "Hello {$firstName},\n\n" .
                        "Course: \"{$courseTitle}\"\n" .
                        "Department: {$departmentName}\n\n" .
                        "Status: {$statusText}\n\n";
        
        if ($isApproved) {
            $mail->AltBody .= "Revenue Distribution:\n" .
                             "- Instructor Share: {$instructorShare}%\n" .
                             "- Platform Share: {$platformShare}%\n\n" .
                             "You can now assign instructors and proceed with course development.\n\n";
        } else {
            $mail->AltBody .= "Your course has been rejected on financial grounds.\n" .
                             "Please review the feedback and make necessary adjustments.\n\n";
        }
        
        if (!empty($feedback)) {
            $mail->AltBody .= "Admin Feedback: {$feedback}\n\n";
        }
        
        $mail->AltBody .= "Please log in to your dashboard for more details.\n\n" .
                         "Best regards,\nLearnix Team";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

// Process the request
try {
    // Get current course details
    $query = "SELECT c.course_id, c.title, c.financial_approval_date, c.department_id, d.name as department_name
              FROM courses c
              LEFT JOIN departments d ON c.department_id = d.department_id
              WHERE c.course_id = ? AND c.deleted_at IS NULL";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Course not found');
    }
    
    $course = $result->fetch_assoc();
    
    // Check if course is already financially approved
    if ($course['financial_approval_date'] !== null && $action === 'approve') {
        throw new Exception('Course is already financially approved');
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    if ($action === 'approve') {
        // Set financial approval date
        $update_sql = "UPDATE courses SET financial_approval_date = NOW() WHERE course_id = ?";
        $stmt = $conn->prepare($update_sql);
        
        if (!$stmt) {
            throw new Exception('Error preparing statement: ' . $conn->error);
        }
        
        $stmt->bind_param("i", $course_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update course financial approval: ' . $stmt->error);
        }
        
        // Add entry to course_financial_history
        $history_sql = "INSERT INTO course_financial_history (course_id, instructor_share, change_date, change_reason) 
                        VALUES (?, ?, NOW(), ?)";
        $stmt = $conn->prepare($history_sql);
        
        if (!$stmt) {
            throw new Exception('Error preparing history statement: ' . $conn->error);
        }
        
        $reason = empty($feedback) ? "Financial approval" : $feedback;
        $stmt->bind_param("ids", $course_id, $instructor_share, $reason);
        $stmt->execute();
    } else {
        // For rejection, just add entry to course_financial_history with 0 instructor share
        $history_sql = "INSERT INTO course_financial_history (course_id, instructor_share, change_date, change_reason) 
                        VALUES (?, 0, NOW(), ?)";
        $stmt = $conn->prepare($history_sql);
        
        if (!$stmt) {
            throw new Exception('Error preparing history statement: ' . $conn->error);
        }
        
        $reason = empty($feedback) ? "Financial rejection" : $feedback;
        $stmt->bind_param("is", $course_id, $reason);
        $stmt->execute();
    }
    
    // Log the activity in user_activity_logs
    $admin_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Fallback to ID 1 if not set
    
    $activity_details = [
        'course_id' => $course_id,
        'course_title' => $course['title'],
        'action' => $action,
        'feedback' => $feedback,
    ];
    
    if ($action === 'approve') {
        $activity_details['instructor_share'] = $instructor_share;
    }
    
    $log_details = json_encode($activity_details);
    
    $log_query = "INSERT INTO user_activity_logs (user_id, activity_type, activity_details, ip_address) 
                  VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($log_query);
    $activity_type = "course_financial_" . $action;
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt->bind_param("isss", $admin_id, $activity_type, $log_details, $ip);
    $stmt->execute();
    
    // Get department head to notify them
    $dept_head_query = "SELECT u.user_id, u.email, u.first_name, u.last_name 
                         FROM users u
                         JOIN department_staff ds ON u.user_id = ds.user_id
                         WHERE ds.department_id = ? AND ds.role = 'head' AND ds.status = 'active' AND ds.deleted_at IS NULL
                         LIMIT 1";
    $stmt = $conn->prepare($dept_head_query);
    $stmt->bind_param("i", $course['department_id']);
    $stmt->execute();
    $dept_head_result = $stmt->get_result();
    
    $email_sent = false;
    
    if ($dept_head_result && $dept_head_result->num_rows > 0) {
        $dept_head = $dept_head_result->fetch_assoc();
        
        // Create in-app notification for department head
        $notification_title = "Course Financial Status Update";
        $notification_message = "The course \"{$course['title']}\" has been ";
        
        if ($action === 'approve') {
            $notification_message .= "financially approved with an instructor revenue share of {$instructor_share}%.";
        } else {
            $notification_message .= "rejected on financial grounds.";
        }
        
        if (!empty($feedback)) {
            $notification_message .= " Feedback: \"" . $feedback . "\"";
        }
        
        $notification_query = "INSERT INTO user_notifications 
                              (user_id, type, title, message, related_id, related_type) 
                              VALUES (?, 'course_update', ?, ?, ?, 'course')";
        $stmt = $conn->prepare($notification_query);
        $stmt->bind_param("issi", $dept_head['user_id'], $notification_title, $notification_message, $course_id);
        $stmt->execute();
        
        // Send email notification using PHPMailer
        $email_sent = sendFinancialStatusEmail(
            $dept_head['email'],
            $dept_head['first_name'],
            $course['title'],
            $course['department_name'],
            $action,
            $instructor_share,
            $feedback
        );
    }
    
    // Commit transaction
    $conn->commit();
    
    // Set success response
    if ($action === 'approve') {
        $response = [
            'status' => 'success',
            'message' => 'Course has been financially approved with ' . $instructor_share . '% instructor share.',
            'email_sent' => $email_sent
        ];
    } else {
        $response = [
            'status' => 'success',
            'message' => 'Course has been rejected on financial grounds.',
            'email_sent' => $email_sent
        ];
    }
    
} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();
    $response['message'] = $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>