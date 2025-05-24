<?php
header('Content-Type: application/json');
require_once '../config.php';

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
    
    if (!isset($input['enrollment_id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing enrollment ID']);
        exit();
    }
    
    $enrollment_id = (int)$input['enrollment_id'];
    $custom_subject = $input['subject'] ?? '';
    $custom_message = $input['message'] ?? '';
    $include_progress = $input['include_progress'] ?? true;
    $include_course_link = $input['include_course_link'] ?? true;
    $user_id = $_SESSION['user_id'];
    
    try {
        // Get enrollment details
        $enrollment_query = "SELECT e.enrollment_id, e.user_id, e.completion_percentage, e.enrolled_at, e.last_accessed,
                                   c.title as course_title, c.course_id,
                                   u.first_name, u.last_name, u.email,
                                   d.department_id, d.name as department_name,
                                   dept_head.first_name as head_first_name, dept_head.last_name as head_last_name,
                                   -- Calculate actual progress
                                   COALESCE(
                                       (SELECT 
                                           (COUNT(CASE WHEN prog.completion_status = 'Completed' THEN 1 END) * 100.0) / 
                                           NULLIF(COUNT(st.topic_id), 0)
                                       FROM course_sections cs
                                       LEFT JOIN section_topics st ON cs.section_id = st.section_id  
                                       LEFT JOIN progress prog ON st.topic_id = prog.topic_id AND prog.enrollment_id = e.enrollment_id AND prog.deleted_at IS NULL
                                       WHERE cs.course_id = c.course_id AND cs.deleted_at IS NULL
                                       ), 0
                                   ) as actual_progress,
                                   -- Get completed topics count
                                   (SELECT COUNT(CASE WHEN prog.completion_status = 'Completed' THEN 1 END)
                                    FROM course_sections cs
                                    LEFT JOIN section_topics st ON cs.section_id = st.section_id  
                                    LEFT JOIN progress prog ON st.topic_id = prog.topic_id AND prog.enrollment_id = e.enrollment_id AND prog.deleted_at IS NULL
                                    WHERE cs.course_id = c.course_id AND cs.deleted_at IS NULL
                                   ) as completed_topics,
                                   -- Get total topics count
                                   (SELECT COUNT(st.topic_id)
                                    FROM course_sections cs
                                    LEFT JOIN section_topics st ON cs.section_id = st.section_id  
                                    WHERE cs.course_id = c.course_id AND cs.deleted_at IS NULL
                                   ) as total_topics
                            FROM enrollments e
                            INNER JOIN courses c ON e.course_id = c.course_id
                            INNER JOIN users u ON e.user_id = u.user_id
                            INNER JOIN departments d ON c.department_id = d.department_id
                            INNER JOIN department_staff ds ON d.department_id = ds.department_id
                            INNER JOIN users dept_head ON ds.user_id = dept_head.user_id
                            WHERE e.enrollment_id = ? 
                            AND ds.user_id = ? 
                            AND ds.role = 'head' 
                            AND ds.status = 'active' 
                            AND ds.deleted_at IS NULL
                            AND e.deleted_at IS NULL";
        
        $enrollment_stmt = $conn->prepare($enrollment_query);
        $enrollment_stmt->bind_param("ii", $enrollment_id, $user_id);
        $enrollment_stmt->execute();
        $enrollment_result = $enrollment_stmt->get_result();
        
        if ($enrollment_result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Enrollment not found or access denied']);
            exit();
        }
        
        $enrollment_data = $enrollment_result->fetch_assoc();
        
        // Add custom fields to enrollment data
        $enrollment_data['custom_subject'] = $custom_subject;
        $enrollment_data['custom_message'] = $custom_message;
        $enrollment_data['include_progress'] = $include_progress;
        $enrollment_data['include_course_link'] = $include_course_link;
        
        // Send reminder email
        if (sendCustomReminderEmail($enrollment_data)) {
            // Log the activity
            $log_query = "INSERT INTO user_activity_logs (user_id, activity_type, activity_details, ip_address, user_agent) 
                         VALUES (?, 'reminder_sent', ?, ?, ?)";
            $log_stmt = $conn->prepare($log_query);
            $activity_details = "Sent custom progress reminder to {$enrollment_data['first_name']} {$enrollment_data['last_name']} for course '{$enrollment_data['course_title']}'";
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $log_stmt->bind_param("isss", $user_id, $activity_details, $ip_address, $user_agent);
            $log_stmt->execute();
            
            // Add in-app notification for student
            $notification_query = "INSERT INTO user_notifications (user_id, type, title, message, related_id, related_type) 
                                  VALUES (?, 'progress_reminder', 'Course Progress Reminder', ?, ?, 'enrollment')";
            $notification_stmt = $conn->prepare($notification_query);
            $notification_message = "You received a progress reminder from your department head about '{$enrollment_data['course_title']}'.";
            $notification_stmt->bind_param("isi", $enrollment_data['user_id'], $notification_message, $enrollment_id);
            $notification_stmt->execute();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Reminder email sent successfully',
                'student_name' => $enrollment_data['first_name'] . ' ' . $enrollment_data['last_name']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send reminder email']);
        }
        
    } catch (Exception $e) {
        error_log("Error sending reminder: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while sending the reminder']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

function sendCustomReminderEmail($data) {
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
        $mail->setFrom('no-reply@learnix.com', 'Learnix - ' . $data['department_name']);
        $mail->addAddress($data['email']);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = $data['custom_subject'];
        
        $progress_percentage = round($data['actual_progress'], 1);
        $days_since_enrollment = floor((time() - strtotime($data['enrolled_at'])) / 86400);
        $last_accessed = $data['last_accessed'] ? date('F j, Y', strtotime($data['last_accessed'])) : 'Not yet accessed';
        
        // Convert custom message to HTML (preserve line breaks)
        $formatted_message = nl2br(htmlspecialchars($data['custom_message']));
        
        // Build progress section if enabled
        $progress_section = '';
        if ($data['include_progress']) {
            $progress_section = '
                <div class="progress-container">
                    <h3 style="margin-top: 0; color: #3a66db;">Your Current Progress</h3>
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                    <p style="margin-bottom: 0; font-size: 18px; font-weight: 600;">' . $progress_percentage . '% Complete</p>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number">' . $data['completed_topics'] . '</div>
                        <div class="stat-label">Topics Completed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">' . ($data['total_topics'] - $data['completed_topics']) . '</div>
                        <div class="stat-label">Topics Remaining</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">' . $days_since_enrollment . '</div>
                        <div class="stat-label">Days Enrolled</div>
                    </div>
                </div>
                
                <p>Last accessed: <strong>' . $last_accessed . '</strong></p>';
        }
        
        // Build course link section if enabled
        $course_link_section = '';
        if ($data['include_course_link']) {
            $course_link_section = '
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . $GLOBALS['base_url'] . '/student/course-content.php?course_id=' . $data['course_id'] . '" class="cta-button">
                        Continue Learning →
                    </a>
                </div>';
        }
        
        $mail->Body = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Course Progress Reminder</title>
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
                
                .progress-container {
                    background-color: #f5f7fa;
                    border-radius: 6px;
                    padding: 20px;
                    text-align: center;
                    margin: 24px 0;
                    border: 1px solid #d1d9e6;
                }
                
                .progress-bar {
                    width: 100%;
                    height: 20px;
                    background-color: #e9ecef;
                    border-radius: 10px;
                    overflow: hidden;
                    margin: 15px 0;
                }
                
                .progress-fill {
                    height: 100%;
                    background: linear-gradient(135deg, #3a66db 0%, #5f85e5 100%);
                    width: ' . $progress_percentage . '%;
                    border-radius: 10px;
                    transition: width 0.3s ease;
                }
                
                .stats-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                    gap: 15px;
                    margin: 20px 0;
                }
                
                .stat-card {
                    background-color: #f8f9fa;
                    padding: 15px;
                    border-radius: 8px;
                    text-align: center;
                }
                
                .stat-number {
                    font-size: 24px;
                    font-weight: 600;
                    color: #3a66db;
                    margin-bottom: 5px;
                }
                
                .stat-label {
                    font-size: 12px;
                    color: #666;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
                
                .cta-button {
                    display: inline-block;
                    background: linear-gradient(135deg, #3a66db 0%, #5f85e5 100%);
                    color: white;
                    text-decoration: none;
                    padding: 15px 30px;
                    border-radius: 8px;
                    font-weight: 600;
                    margin: 20px 0;
                    text-align: center;
                }
                
                .custom-message {
                    background-color: #f8f9fa;
                    border-left: 4px solid #3a66db;
                    padding: 20px;
                    margin: 20px 0;
                    border-radius: 0 8px 8px 0;
                }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="email-header">
                    <h1>' . htmlspecialchars($data['course_title']) . '</h1>
                </div>
                
                <div class="email-body">
                    <div class="custom-message">
                        ' . $formatted_message . '
                    </div>
                    
                    ' . $progress_section . '
                    
                    ' . $course_link_section . '
                    
                    <p style="font-size: 14px; color: #666; margin-top: 30px;">
                        This message was sent by ' . $data['head_first_name'] . ' ' . $data['head_last_name'] . ' from the ' . $data['department_name'] . ' department.
                    </p>
                </div>
                
                <div class="email-footer">
                    <p>&copy; 2025 Learnix. All rights reserved.</p>
                    <p>Keep learning, keep growing!</p>
                </div>
            </div>
        </body>
        </html>';

        $mail->AltBody = strip_tags($data['custom_message']) . "\n\nCourse: {$data['course_title']}\nProgress: {$progress_percentage}% complete\n\nVisit our platform to continue learning.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>