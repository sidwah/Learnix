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
$feedback = isset($_POST['feedback']) ? trim($_POST['feedback']) : '';

// Debug log
error_log("Received parameters: course_id={$course_id}, course_status={$course_status}");

// Validate inputs
if ($course_id <= 0) {
    $response['message'] = 'Invalid course ID';
    echo json_encode($response);
    exit;
}

// Validate at least one status is being updated
if ($course_status === null || $course_status === '') {
    $response['message'] = 'No status changes specified';
    echo json_encode($response);
    exit;
}

// Validate course status value if provided
if (!in_array($course_status, ['Draft', 'Published'])) {
    $response['message'] = 'Invalid course status value: "' . htmlspecialchars($course_status) . '". Valid values are: Draft, Published';
    echo json_encode($response);
    exit;
}

// Process the request
try {
    // Get current course details
    $query = "SELECT c.course_id, c.title, c.status, c.financial_approval_date, c.department_id,
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
    $instructor_ids = $course['instructor_ids'] ? explode(',', $course['instructor_ids']) : [];
    
    // Check if course is financially approved before allowing status change
    if ($course['financial_approval_date'] === NULL) {
        throw new Exception('Course must be financially approved before changing publication status');
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    // Update the course status
    $update_sql = "UPDATE courses SET status = ?, updated_at = NOW() WHERE course_id = ?";
    $stmt = $conn->prepare($update_sql);
    
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . $conn->error);
    }
    
    $stmt->bind_param("si", $course_status, $course_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update course status: ' . $stmt->error);
    }
    
    // Log the activity in user_activity_logs
    $admin_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Fallback to ID 1 if not set
    
    $activity_details = [
        'course_status' => [
            'previous' => $current_course_status,
            'new' => $course_status
        ],
        'course_id' => $course_id,
        'course_title' => $course['title'],
        'feedback' => $feedback
    ];
    
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
                
                $notification_message .= $course_status === 'Published' ? "published" : "unpublished";
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
                
                // Send email notification (similar to previous implementation)
                // Email sending code would go here
            }
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    // Set success response
    $response = [
        'status' => 'success',
        'message' => 'Course status changed to ' . $course_status
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