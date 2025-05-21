<?php
// Authentication check
require_once '../../backend/auth/admin/admin-auth-check.php';
require_once '../../backend/config.php';

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
    'message' => 'Failed to approve financial terms'
];

// Get and validate input
$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
$instructor_share = isset($_POST['instructor_share']) ? floatval($_POST['instructor_share']) : 0;
$feedback = isset($_POST['feedback']) ? trim($_POST['feedback']) : '';

// Debug log
error_log("Received parameters: course_id={$course_id}, instructor_share={$instructor_share}");

// Validate inputs
if ($course_id <= 0) {
    $response['message'] = 'Invalid course ID';
    echo json_encode($response);
    exit;
}

// Validate instructor share
if ($instructor_share <= 0 || $instructor_share > 100) {
    $response['message'] = 'Invalid instructor share percentage';
    echo json_encode($response);
    exit;
}

// Process the request
try {
    // Get current course details
    $query = "SELECT c.course_id, c.title, c.financial_approval_date, c.department_id, 
                     d.name as department_name
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
    
    // Check if already financially approved
    if ($course['financial_approval_date'] !== NULL) {
        throw new Exception('Course has already been financially approved');
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    // Update course with financial approval date
    $update_sql = "UPDATE courses SET financial_approval_date = NOW(), updated_at = NOW() WHERE course_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $course_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update course financial approval: ' . $stmt->error);
    }
    
    // Add entry to course_financial_history
    $history_sql = "INSERT INTO course_financial_history (course_id, instructor_share, change_date, change_reason) 
                    VALUES (?, ?, NOW(), ?)";
    $stmt = $conn->prepare($history_sql);
    $stmt->bind_param("ids", $course_id, $instructor_share, $feedback);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to record financial history: ' . $stmt->error);
    }
    
    // Log the activity in user_activity_logs
    $admin_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Fallback to ID 1 if not set
    
    $activity_details = [
        'course_id' => $course_id,
        'course_title' => $course['title'],
        'department' => $course['department_name'],
        'instructor_share' => $instructor_share,
        'platform_share' => 100 - $instructor_share,
        'feedback' => $feedback
    ];
    
    $log_details = json_encode($activity_details);
    
    $log_query = "INSERT INTO user_activity_logs (user_id, activity_type, activity_details, ip_address) 
                  VALUES (?, 'course_financial_approval', ?, ?)";
    $stmt = $conn->prepare($log_query);
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt->bind_param("iss", $admin_id, $log_details, $ip);
    $stmt->execute();
    
    // Find department head to notify
    $dept_head_query = "SELECT u.user_id, u.email, u.first_name, u.last_name
                         FROM users u
                         JOIN department_staff ds ON u.user_id = ds.user_id
                         WHERE ds.department_id = ? 
                         AND ds.role = 'head'
                         AND ds.status = 'active' 
                         AND ds.deleted_at IS NULL
                         AND u.deleted_at IS NULL";
    
    $stmt = $conn->prepare($dept_head_query);
    $stmt->bind_param("i", $course['department_id']);
    $stmt->execute();
    $dept_head_result = $stmt->get_result();
    
    if ($dept_head_result && $dept_head_result->num_rows > 0) {
        $dept_head = $dept_head_result->fetch_assoc();
        
        // Create in-app notification for department head
        $notification_title = "Course Financial Terms Approved";
        $notification_message = "Financial terms for course \"{$course['title']}\" have been approved. " .
                                "Instructor share: {$instructor_share}%, Platform share: " . (100 - $instructor_share) . "%. " .
                                "You can now assign instructors to this course.";
        
        if (!empty($feedback)) {
            $notification_message .= " Admin comments: \"" . $feedback . "\"";
        }
        
        $notification_query = "INSERT INTO user_notifications 
                              (user_id, type, title, message, related_id, related_type) 
                              VALUES (?, 'financial_approval', ?, ?, ?, 'course')";
        $stmt = $conn->prepare($notification_query);
        $stmt->bind_param("issi", $dept_head['user_id'], $notification_title, $notification_message, $course_id);
        $stmt->execute();
        
        // Send email notification
        $to = $dept_head['email'];
        $subject = "Learnix - Course Financial Terms Approved";
        
        // HTML email message
        $message = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Course Financial Terms Approved</title>
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
                    padding: 6px 12px;
                    border-radius: 4px;
                    font-weight: 500;
                    font-size: 14px;
                    color: #ffffff;
                }
                
                .revenue-info {
                    background-color: #f0f7ff;
                    border-radius: 6px;
                    padding: 15px;
                    margin: 20px 0;
                }
                
                .revenue-row {
                    display: flex;
                    justify-content: space-between;
                    padding: 8px 0;
                    border-bottom: 1px solid #e0e0e0;
                }
                
                .revenue-row:last-child {
                    border-bottom: none;
                }
                
                .feedback-box {
                    background-color: #f7f7f7;
                    border-left: 4px solid #3a66db;
                    padding: 15px;
                    margin-top: 20px;
                    border-radius: 0 4px 4px 0;
                }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="email-header">
                    <h1>Learnix</h1>
                </div>
                
                <div class="email-body">
                    <h2>Course Financial Terms Approved</h2>
                    
                    <p>Hello ' . htmlspecialchars($dept_head['first_name']) . ',</p>
                    
                    <p>The financial terms for course <span class="course-title">"' . htmlspecialchars($course['title']) . '"</span> have been approved by an administrator.</p>
                    
                    <div class="revenue-info">
                        <div class="revenue-row">
                            <strong>Instructor Share:</strong>
                            <span>' . $instructor_share . '%</span>
                        </div>
                        <div class="revenue-row">
                            <strong>Platform Share:</strong>
                            <span>' . (100 - $instructor_share) . '%</span>
                        </div>
                    </div>
                    
                    <p>You can now assign instructors to this course and proceed with content development.</p>
        ';
        
        if (!empty($feedback)) {
            $message .= '
                    <div class="feedback-box">
                        <h4 style="margin-top: 0;">Admin Comments</h4>
                        <p style="margin-bottom: 0;">' . nl2br(htmlspecialchars($feedback)) . '</p>
                    </div>
            ';
        }
        
        $message .= '
                    <p>Please log in to your account to view more details and take necessary actions.</p>
                </div>
                
                <div class="email-footer">
                    <p>© 2025 Learnix. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        // Set email headers
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Learnix <no-reply@learnix.com>" . "\r\n";
        
        // Send email
        mail($to, $subject, $message, $headers);
    }
    
    // Commit transaction
    $conn->commit();
    
    // Set success response
    $response = [
        'status' => 'success',
        'message' => 'Financial terms approved successfully: Instructor share ' . $instructor_share . '%, Platform share ' . (100 - $instructor_share) . '%'
    ];
    
} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();
    $response['message'] = $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;