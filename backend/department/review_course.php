<?php
require_once '../config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

header('Content-Type: application/json');

// Check if user is logged in and has proper role
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $course_id = isset($input['course_id']) ? (int)$input['course_id'] : 0;
    $action = isset($input['action']) ? $input['action'] : '';
    
    if (!$course_id || !$action) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Verify user has access to this course
    $access_query = "SELECT c.*, d.department_id, d.name as department_name,
                            GROUP_CONCAT(u.email SEPARATOR ',') as instructor_emails,
                            GROUP_CONCAT(CONCAT(u.first_name, ' ', u.last_name) SEPARATOR ', ') as instructor_names
                     FROM courses c
                     INNER JOIN departments d ON c.department_id = d.department_id
                     INNER JOIN department_staff ds ON d.department_id = ds.department_id 
                     LEFT JOIN course_instructors ci ON c.course_id = ci.course_id AND ci.deleted_at IS NULL
                     LEFT JOIN instructors i ON ci.instructor_id = i.instructor_id AND i.deleted_at IS NULL
                     LEFT JOIN users u ON i.user_id = u.user_id AND u.deleted_at IS NULL
                     WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' 
                     AND ds.deleted_at IS NULL AND c.course_id = ? AND c.deleted_at IS NULL
                     GROUP BY c.course_id";
    
    $access_stmt = $conn->prepare($access_query);
    $access_stmt->bind_param("ii", $user_id, $course_id);
    $access_stmt->execute();
    $course_result = $access_stmt->get_result();
    
    if ($course_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Access denied or course not found']);
        exit();
    }
    
    $course = $course_result->fetch_assoc();
    
    // Get reviewer name
    $reviewer_query = "SELECT first_name, last_name FROM users WHERE user_id = ?";
    $reviewer_stmt = $conn->prepare($reviewer_query);
    $reviewer_stmt->bind_param("i", $user_id);
    $reviewer_stmt->execute();
    $reviewer_result = $reviewer_stmt->get_result();
    $reviewer = $reviewer_result->fetch_assoc();
    $reviewer_name = $reviewer['first_name'] . ' ' . $reviewer['last_name'];
    
    $conn->begin_transaction();
    
    switch ($action) {
        case 'approve':
            $comments = isset($input['comments']) ? $input['comments'] : '';
            
            // Update course status
            $update_query = "UPDATE courses SET approval_status = 'approved', updated_at = NOW() WHERE course_id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("i", $course_id);
            $update_stmt->execute();
            
            // Add to review history
            $history_query = "INSERT INTO course_review_history (course_id, reviewed_by, previous_status, new_status, comments) VALUES (?, ?, 'under_review', 'approved', ?)";
            $history_stmt = $conn->prepare($history_query);
            $history_stmt->bind_param("iis", $course_id, $user_id, $comments);
            $history_stmt->execute();
            
            // Send notification to instructors
            if ($course['instructor_emails']) {
                $emails = explode(',', $course['instructor_emails']);
                foreach ($emails as $email) {
                    sendCourseApprovalEmail(trim($email), $course, $reviewer_name, $comments);
                }
            }
            
            // Add system notification
            addCourseNotification($course_id, 'approved', $reviewer_name);
            
            $response_message = 'Course approved successfully';
            break;
            
        case 'request_revisions':
            $feedback = isset($input['feedback']) ? $input['feedback'] : '';
            $priority = isset($input['priority']) ? $input['priority'] : 'medium';
            
            if (empty($feedback)) {
                throw new Exception('Feedback is required for revision requests');
            }
            
            // Update course status
            $update_query = "UPDATE courses SET approval_status = 'revisions_requested', updated_at = NOW() WHERE course_id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("i", $course_id);
            $update_stmt->execute();
            
            // Add to review history
            $comments = "Priority: " . ucfirst($priority) . "\n\n" . $feedback;
            $history_query = "INSERT INTO course_review_history (course_id, reviewed_by, previous_status, new_status, comments) VALUES (?, ?, 'under_review', 'revisions_requested', ?)";
            $history_stmt = $conn->prepare($history_query);
            $history_stmt->bind_param("iis", $course_id, $user_id, $comments);
            $history_stmt->execute();
            
            // Send notification to instructors
            if ($course['instructor_emails']) {
                $emails = explode(',', $course['instructor_emails']);
                foreach ($emails as $email) {
                    sendRevisionRequestEmail(trim($email), $course, $reviewer_name, $feedback, $priority);
                }
            }
            
            // Add system notification
            addCourseNotification($course_id, 'revisions_requested', $reviewer_name, $feedback);
            
            $response_message = 'Revision request sent successfully';
            break;
            
        case 'reject':
            $reason = isset($input['reason']) ? $input['reason'] : '';
            
            if (empty($reason)) {
                throw new Exception('Reason is required for course rejection');
            }
            
            // Update course status
            $update_query = "UPDATE courses SET approval_status = 'rejected', updated_at = NOW() WHERE course_id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("i", $course_id);
            $update_stmt->execute();
            
            // Add to review history
            $history_query = "INSERT INTO course_review_history (course_id, reviewed_by, previous_status, new_status, comments) VALUES (?, ?, 'under_review', 'rejected', ?)";
            $history_stmt = $conn->prepare($history_query);
            $history_stmt->bind_param("iis", $course_id, $user_id, $reason);
            $history_stmt->execute();
            
            // Send notification to instructors
            if ($course['instructor_emails']) {
                $emails = explode(',', $course['instructor_emails']);
                foreach ($emails as $email) {
                    sendCourseRejectionEmail(trim($email), $course, $reviewer_name, $reason);
                }
            }
            
            // Add system notification
            addCourseNotification($course_id, 'rejected', $reviewer_name, $reason);
            
            $response_message = 'Course rejected successfully';
            break;
            
        default:
            throw new Exception('Invalid action specified');
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => $response_message
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    error_log("Error in course review: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function sendCourseApprovalEmail($email, $course, $reviewer_name, $comments) {
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
        
        $mail->setFrom('no-reply@learnix.com', 'Learnix');
        $mail->addAddress($email);
        
        $mail->isHTML(true);
        $mail->Subject = 'Course Approved - ' . $course['title'];
        
        $mail->Body = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Course Approved</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: white; padding: 30px; border: 1px solid #dee2e6; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; border-radius: 0 0 8px 8px; }
                .btn { display: inline-block; padding: 12px 24px; background: #28a745; color: white; text-decoration: none; border-radius: 4px; margin: 10px 0; }
                .alert { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 4px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🎉 Course Approved!</h1>
                </div>
                <div class="content">
                    <h2>Congratulations!</h2>
                    <p>Your course "<strong>' . htmlspecialchars($course['title']) . '</strong>" has been approved by ' . htmlspecialchars($reviewer_name) . '.</p>
                    
                    <div class="alert">
                        <strong>What happens next?</strong><br>
                        Your course is now approved and can be published to make it available to students.
                    </div>
                    
                    ' . (!empty($comments) ? '<h3>Reviewer Comments:</h3><p>' . nl2br(htmlspecialchars($comments)) . '</p>' : '') . '
                    
                    <p>You can now:</p>
                    <ul>
                        <li>Publish your course to make it available to students</li>
                        <li>Continue making improvements to your content</li>
                        <li>Monitor student enrollment and engagement</li>
                    </ul>
                    
                    <a href="' . $GLOBALS['base_url'] . '/instructor/courses.php" class="btn">View Your Courses</a>
                </div>
                <div class="footer">
                    <p>&copy; 2025 Learnix. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>';
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail Error (Approval): " . $mail->ErrorInfo);
        return false;
    }
}

function sendRevisionRequestEmail($email, $course, $reviewer_name, $feedback, $priority) {
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
        
        $mail->setFrom('no-reply@learnix.com', 'Learnix');
        $mail->addAddress($email);
        
        $mail->isHTML(true);
        $mail->Subject = 'Revision Request - ' . $course['title'];
        
        $priorityColor = $priority === 'high' ? '#dc3545' : ($priority === 'medium' ? '#ffc107' : '#6c757d');
        
        $mail->Body = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Revision Request</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: white; padding: 30px; border: 1px solid #dee2e6; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; border-radius: 0 0 8px 8px; }
                .btn { display: inline-block; padding: 12px 24px; background: #ffc107; color: white; text-decoration: none; border-radius: 4px; margin: 10px 0; }
                .priority { background: ' . $priorityColor . '; color: white; padding: 5px 10px; border-radius: 3px; font-size: 12px; }
                .feedback-box { background: #f8f9fa; border-left: 4px solid #ffc107; padding: 15px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>📝 Revision Request</h1>
                </div>
                <div class="content">
                    <h2>Course Revision Required</h2>
                    <p>Your course "<strong>' . htmlspecialchars($course['title']) . '</strong>" requires revisions before it can be approved.</p>
                    
                    <p><strong>Reviewed by:</strong> ' . htmlspecialchars($reviewer_name) . '</p>
                    <p><strong>Priority Level:</strong> <span class="priority">' . strtoupper($priority) . '</span></p>
                    
                    <div class="feedback-box">
                        <h3>Feedback:</h3>
                        <p>' . nl2br(htmlspecialchars($feedback)) . '</p>
                    </div>
                    
                    <p><strong>Next Steps:</strong></p>
                    <ul>
                        <li>Review the feedback carefully</li>
                        <li>Make the requested changes to your course</li>
                        <li>Resubmit your course for review</li>
                    </ul>
                    
                    <a href="' . $GLOBALS['base_url'] . '/instructor/courses.php" class="btn">Edit Your Course</a>
                </div>
                <div class="footer">
                    <p>&copy; 2025 Learnix. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>';
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail Error (Revision): " . $mail->ErrorInfo);
        return false;
    }
}

function sendCourseRejectionEmail($email, $course, $reviewer_name, $reason) {
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
        
        $mail->setFrom('no-reply@learnix.com', 'Learnix');
        $mail->addAddress($email);
        
        $mail->isHTML(true);
        $mail->Subject = 'Course Rejected - ' . $course['title'];
        
        $mail->Body = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Course Rejected</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: white; padding: 30px; border: 1px solid #dee2e6; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; border-radius: 0 0 8px 8px; }
                .btn { display: inline-block; padding: 12px 24px; background: #dc3545; color: white; text-decoration: none; border-radius: 4px; margin: 10px 0; }
                .reason-box { background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 15px 0; }
                .alert { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 4px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>❌ Course Rejected</h1>
                </div>
                <div class="content">
                    <h2>Course Not Approved</h2>
                    <p>Unfortunately, your course "<strong>' . htmlspecialchars($course['title']) . '</strong>" has been rejected by ' . htmlspecialchars($reviewer_name) . '.</p>
                    
                    <div class="reason-box">
                        <h3>Rejection Reason:</h3>
                        <p>' . nl2br(htmlspecialchars($reason)) . '</p>
                    </div>
                    
                    <div class="alert">
                        <strong>What this means:</strong><br>
                        Your course requires significant changes before it can be reconsidered for approval. Please review the feedback carefully and make substantial improvements.
                    </div>
                    
                    <p><strong>Next Steps:</strong></p>
                    <ul>
                        <li>Carefully review the rejection reason</li>
                        <li>Make significant improvements to your course</li>
                        <li>Consider reaching out to your department head for guidance</li>
                        <li>Resubmit when you have addressed all concerns</li>
                    </ul>
                    
                    <a href="' . $GLOBALS['base_url'] . '/instructor/courses.php" class="btn">View Your Courses</a>
                </div>
                <div class="footer">
                    <p>&copy; 2025 Learnix. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>';
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail Error (Rejection): " . $mail->ErrorInfo);
        return false;
    }
}

function addCourseNotification($course_id, $status, $reviewer_name, $additional_info = '') {
    global $conn;
    
    // Get instructor user IDs for this course
    $instructor_query = "SELECT DISTINCT u.user_id 
                        FROM course_instructors ci
                        INNER JOIN instructors i ON ci.instructor_id = i.instructor_id
                        INNER JOIN users u ON i.user_id = u.user_id
                        WHERE ci.course_id = ? AND ci.deleted_at IS NULL AND i.deleted_at IS NULL";
    
    $instructor_stmt = $conn->prepare($instructor_query);
    $instructor_stmt->bind_param("i", $course_id);
    $instructor_stmt->execute();
    $instructor_result = $instructor_stmt->get_result();
    
    $titles = [
        'approved' => 'Course Approved',
        'revisions_requested' => 'Revisions Requested',
        'rejected' => 'Course Rejected'
    ];
    
    $messages = [
        'approved' => 'Your course has been approved by ' . $reviewer_name . ' and is ready for publishing.',
        'revisions_requested' => 'Your course requires revisions. Please review the feedback from ' . $reviewer_name . '.',
        'rejected' => 'Your course has been rejected by ' . $reviewer_name . '. Please review the feedback and make necessary changes.'
    ];
    
    while ($instructor = $instructor_result->fetch_assoc()) {
        $notification_query = "INSERT INTO user_notifications (user_id, type, title, message, related_id, related_type) 
                              VALUES (?, 'course_review', ?, ?, ?, 'course')";
        $notification_stmt = $conn->prepare($notification_query);
        $notification_stmt->bind_param("issi", 
            $instructor['user_id'], 
            $titles[$status], 
            $messages[$status], 
            $course_id
        );
        $notification_stmt->execute();
    }
}
?>