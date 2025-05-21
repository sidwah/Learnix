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
    'message' => 'Failed to update course status'
];

// Get and validate input
$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
$course_status = isset($_POST['course_status']) ? $_POST['course_status'] : null;
$approval_status = isset($_POST['approval_status']) ? $_POST['approval_status'] : null;
$feedback = isset($_POST['feedback']) ? trim($_POST['feedback']) : '';

// Debug log
error_log("Received parameters: course_id={$course_id}, course_status={$course_status}, approval_status={$approval_status}");

// Validate inputs
if ($course_id <= 0) {
    $response['message'] = 'Invalid course ID';
    echo json_encode($response);
    exit;
}

// Validate at least one status is being updated
if (($course_status === null || $course_status === '') && 
    ($approval_status === null || $approval_status === '')) {
    $response['message'] = 'No status changes specified';
    echo json_encode($response);
    exit;
}

// Validate course status value if provided
if ($course_status !== null && $course_status !== '' && 
    !in_array($course_status, ['Draft', 'Published'])) {
    $response['message'] = 'Invalid course status value: "' . htmlspecialchars($course_status) . '". Valid values are: Draft, Published';
    echo json_encode($response);
    exit;
}

// Validate approval status value if provided
$valid_approval_statuses = ['pending', 'revisions_requested', 'submitted_for_review', 'under_review', 'approved', 'rejected'];
if ($approval_status !== null && $approval_status !== '' && 
    !in_array($approval_status, $valid_approval_statuses)) {
    $response['message'] = 'Invalid approval status value: "' . htmlspecialchars($approval_status) . '". Valid values are: ' . implode(', ', $valid_approval_statuses);
    echo json_encode($response);
    exit;
}

// Process the request
try {
    // Get current course details
    $query = "SELECT c.course_id, c.title, c.status, c.approval_status, c.financial_approval_date, c.department_id,
                     GROUP_CONCAT(DISTINCT ci.instructor_id) as instructor_ids
              FROM courses c
              LEFT JOIN course_instructors ci ON c.course_id = ci.course_id AND ci.deleted_at IS NULL
              WHERE c.course_id = ? AND c.deleted_at IS NULL
              GROUP BY c.course_id";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Course not found');
    }
    
    $course = $result->fetch_assoc();
    $current_course_status = $course['status'];
    $current_approval_status = $course['approval_status'];
    $instructor_ids = $course['instructor_ids'] ? explode(',', $course['instructor_ids']) : [];
    
    // Start transaction
    $conn->begin_transaction();
    
    // Build update query based on what's changing
    $update_fields = [];
    $params = [];
    $types = "";
    
    if ($course_status !== null && $course_status !== '') {
        $update_fields[] = "status = ?";
        $params[] = $course_status;
        $types .= "s";
    }
    
    if ($approval_status !== null && $approval_status !== '') {
        $update_fields[] = "approval_status = ?";
        $params[] = $approval_status;
        $types .= "s";
    }
    
    if (!empty($update_fields)) {
        $update_fields[] = "updated_at = NOW()";
        
        $update_sql = "UPDATE courses SET " . implode(", ", $update_fields) . " WHERE course_id = ?";
        $params[] = $course_id;
        $types .= "i";
        
        $stmt = $conn->prepare($update_sql);
        
        if (!$stmt) {
            throw new Exception('Error preparing statement: ' . $conn->error);
        }
        
        $stmt->bind_param($types, ...$params);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update course status: ' . $stmt->error);
        }
    }
    
    // Log the status change in course_review_history
    if ($approval_status !== null && $approval_status !== '' && $approval_status !== $current_approval_status) {
        $admin_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Fallback to ID 1 if not set
        
        $log_query = "INSERT INTO course_review_history 
                      (course_id, reviewed_by, previous_status, new_status, comments) 
                      VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($log_query);
        
        if (!$stmt) {
            throw new Exception('Error preparing history statement: ' . $conn->error);
        }
        
        $stmt->bind_param("iisss", $course_id, $admin_id, $current_approval_status, $approval_status, $feedback);
        $stmt->execute();
    }
    
    // Log the activity in user_activity_logs
    $admin_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Fallback to ID 1 if not set
    
    $activity_details = [];
    if ($course_status !== null && $course_status !== '') {
        $activity_details['course_status'] = [
            'previous' => $current_course_status,
            'new' => $course_status
        ];
    }
    
    if ($approval_status !== null && $approval_status !== '') {
        $activity_details['approval_status'] = [
            'previous' => $current_approval_status,
            'new' => $approval_status
        ];
    }
    
    $activity_details['course_id'] = $course_id;
    $activity_details['course_title'] = $course['title'];
    $activity_details['feedback'] = $feedback;
    
    $log_details = json_encode($activity_details);
    
    $log_query = "INSERT INTO user_activity_logs (user_id, activity_type, activity_details, ip_address) 
                  VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($log_query);
    $activity_type = "course_status_changed";
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt->bind_param("isss", $admin_id, $activity_type, $log_details, $ip);
    $stmt->execute();
    
    // Get instructor details to notify them
    if (!empty($instructor_ids)) {
        $instructor_ids_str = implode(',', $instructor_ids);
        
        $instructor_query = "SELECT u.user_id, u.email, u.first_name, u.last_name
                             FROM users u
                             JOIN instructors i ON u.user_id = i.user_id
                             WHERE i.instructor_id IN ($instructor_ids_str)
                             AND u.deleted_at IS NULL";
        
        $instructor_result = $conn->query($instructor_query);
        
        if ($instructor_result && $instructor_result->num_rows > 0) {
            while ($instructor = $instructor_result->fetch_assoc()) {
                // Create in-app notification for each instructor
                $notification_title = "Course Status Update";
                $notification_message = "Your course \"{$course['title']}\" has been ";
                
                if ($course_status !== null && $course_status !== '' && $course_status !== $current_course_status) {
                    $notification_message .= $course_status === 'Published' ? "published" : "unpublished";
                }
                
                if ($approval_status !== null && $approval_status !== '' && $approval_status !== $current_approval_status) {
                    if ($course_status !== null && $course_status !== '' && $course_status !== $current_course_status) {
                        $notification_message .= " and ";
                    }
                    
                    switch ($approval_status) {
                        case 'approved':
                            $notification_message .= "approved";
                            break;
                        case 'rejected':
                            $notification_message .= "rejected";
                            break;
                        case 'revisions_requested':
                            $notification_message .= "returned for revisions";
                            break;
                        case 'under_review':
                            $notification_message .= "moved to review status";
                            break;
                        default:
                            $notification_message .= "updated to " . str_replace('_', ' ', $approval_status);
                            break;
                    }
                }
                
                $notification_message .= " by an administrator.";
                
                if (!empty($feedback)) {
                    $notification_message .= " Feedback: \"" . $feedback . "\"";
                }
                
                $notification_query = "INSERT INTO user_notifications 
                                      (user_id, type, title, message, related_id, related_type) 
                                      VALUES (?, 'course_update', ?, ?, ?, 'course')";
                $stmt = $conn->prepare($notification_query);
                $stmt->bind_param("issi", $instructor['user_id'], $notification_title, $notification_message, $course_id);
                $stmt->execute();
                
                // Send email notification
                $to = $instructor['email'];
                $subject = "Learnix - Course Status Update";
                
                // HTML email message
                $message = '
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Course Status Update</title>
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
                            <h2>Course Status Update</h2>
                            
                            <p>Hello ' . htmlspecialchars($instructor['first_name']) . ',</p>
                            
                            <p>This is to inform you that your course <span class="course-title">"' . htmlspecialchars($course['title']) . '"</span> has been updated by an administrator.</p>
                ';
                
                if ($course_status !== null && $course_status !== '' && $course_status !== $current_course_status) {
                    $status_color = $course_status === 'Published' ? '#28a745' : '#6c757d';
                    $message .= '
                            <p>Course Status: <span class="status-badge" style="background-color: ' . $status_color . ';">' . $course_status . '</span></p>
                    ';
                }
                
                if ($approval_status !== null && $approval_status !== '' && $approval_status !== $current_approval_status) {
                    $approval_color = '#3a66db'; // Default blue
                    
                    switch ($approval_status) {
                        case 'approved':
                            $approval_color = '#28a745'; // Green
                            break;
                        case 'rejected':
                            $approval_color = '#dc3545'; // Red
                            break;
                        case 'revisions_requested':
                            $approval_color = '#ffc107'; // Yellow
                            break;
                    }
                    
                    $approval_display = ucfirst(str_replace('_', ' ', $approval_status));
                    
                    $message .= '
                            <p>Approval Status: <span class="status-badge" style="background-color: ' . $approval_color . ';">' . $approval_display . '</span></p>
                    ';
                }
                
                if (!empty($feedback)) {
                    $message .= '
                            <div class="feedback-box">
                                <h4 style="margin-top: 0;">Feedback</h4>
                                <p style="margin-bottom: 0;">' . nl2br(htmlspecialchars($feedback)) . '</p>
                            </div>
                    ';
                }
                
                $message .= '
                            <p>Please log in to your account to view more details and take any necessary actions.</p>
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
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    // Set success response
    $status_message = [];
    if ($course_status !== null && $course_status !== '') {
        $status_message[] = "course status changed to " . $course_status;
    }
    if ($approval_status !== null && $approval_status !== '') {
        $status_message[] = "approval status changed to " . ucfirst(str_replace('_', ' ', $approval_status));
    }
    
    $response = [
        'status' => 'success',
        'message' => 'Course updated successfully: ' . implode(' and ', $status_message)
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