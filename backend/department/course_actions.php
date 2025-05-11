<?php
// backend/department/course_actions.php
// session_start();
require_once '../config.php';

// Check if user is logged in and has department head role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get user's department
$dept_query = "SELECT ds.department_id 
               FROM department_staff ds 
               WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' AND ds.deleted_at IS NULL
               LIMIT 1";
$dept_stmt = $conn->prepare($dept_query);
$dept_stmt->bind_param("i", $_SESSION['user_id']);
$dept_stmt->execute();
$dept_result = $dept_stmt->get_result();

if ($dept_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Department not found']);
    exit;
}

$department = $dept_result->fetch_assoc();
$department_id = $department['department_id'];

// Get action and course data
$action = $_POST['action'] ?? '';
$course_id = $_POST['course_id'] ?? 0;

if (!$action || !$course_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Verify course belongs to department
$verify_query = "SELECT c.course_id, c.title, c.status, c.approval_status 
                 FROM courses c
                 WHERE c.course_id = ? AND c.department_id = ? AND c.deleted_at IS NULL";
$verify_stmt = $conn->prepare($verify_query);
$verify_stmt->bind_param("ii", $course_id, $department_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Course not found']);
    exit;
}

$course = $verify_result->fetch_assoc();

try {
    $conn->begin_transaction();
    
    switch ($action) {
        case 'archive':
            // Archive course (soft delete)
            $archive_sql = "UPDATE courses 
                           SET deleted_at = CURRENT_TIMESTAMP,
                               status = 'Archived'
                           WHERE course_id = ?";
            $archive_stmt = $conn->prepare($archive_sql);
            $archive_stmt->bind_param("i", $course_id);
            $archive_stmt->execute();
            
            $message = "Course '{$course['title']}' has been archived successfully.";
            break;
            
        case 'approve':
            if (!in_array($course['approval_status'], ['submitted_for_review', 'under_review'])) {
                throw new Exception('Course must be submitted for review to approve');
            }
            
            $approve_sql = "UPDATE courses 
                           SET approval_status = 'approved',
                               status = 'Published'
                           WHERE course_id = ?";
            $approve_stmt = $conn->prepare($approve_sql);
            $approve_stmt->bind_param("i", $course_id);
            $approve_stmt->execute();
            
            // Log the approval
            $log_sql = "INSERT INTO course_review_history 
                       (course_id, reviewed_by, previous_status, new_status, comments, review_date)
                       VALUES (?, ?, ?, 'approved', 'Course approved by department head', CURRENT_TIMESTAMP)";
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->bind_param("iis", $course_id, $_SESSION['user_id'], $course['approval_status']);
            $log_stmt->execute();
            
            $message = "Course '{$course['title']}' has been approved and published.";
            break;
            
        case 'request_revisions':
            $comments = $_POST['comments'] ?? '';
            if (empty($comments)) {
                throw new Exception('Comments are required when requesting revisions');
            }
            
            if (!in_array($course['approval_status'], ['submitted_for_review', 'under_review'])) {
                throw new Exception('Course must be submitted for review to request revisions');
            }
            
            $revisions_sql = "UPDATE courses 
                             SET approval_status = 'revisions_requested',
                                 status = 'Draft'
                             WHERE course_id = ?";
            $revisions_stmt = $conn->prepare($revisions_sql);
            $revisions_stmt->bind_param("i", $course_id);
            $revisions_stmt->execute();
            
            // Log the revision request
            $log_sql = "INSERT INTO course_review_history 
                       (course_id, reviewed_by, previous_status, new_status, comments, review_date)
                       VALUES (?, ?, ?, 'revisions_requested', ?, CURRENT_TIMESTAMP)";
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->bind_param("iiss", $course_id, $_SESSION['user_id'], $course['approval_status'], $comments);
            $log_stmt->execute();
            
            // Notify instructors
            // TODO: Add notification system
            
            $message = "Revision request sent for course '{$course['title']}'.";
            break;
            
        case 'reject':
            $comments = $_POST['comments'] ?? '';
            if (empty($comments)) {
                throw new Exception('Comments are required when rejecting a course');
            }
            
            if (!in_array($course['approval_status'], ['submitted_for_review', 'under_review'])) {
                throw new Exception('Course must be submitted for review to reject');
            }
            
            $reject_sql = "UPDATE courses 
                          SET approval_status = 'rejected',
                              status = 'Draft'
                          WHERE course_id = ?";
            $reject_stmt = $conn->prepare($reject_sql);
            $reject_stmt->bind_param("i", $course_id);
            $reject_stmt->execute();
            
            // Log the rejection
            $log_sql = "INSERT INTO course_review_history 
                       (course_id, reviewed_by, previous_status, new_status, comments, review_date)
                       VALUES (?, ?, ?, 'rejected', ?, CURRENT_TIMESTAMP)";
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->bind_param("iiss", $course_id, $_SESSION['user_id'], $course['approval_status'], $comments);
            $log_stmt->execute();
            
            $message = "Course '{$course['title']}' has been rejected.";
            break;
            
        case 'unpublish':
            if ($course['status'] !== 'Published') {
                throw new Exception('Only published courses can be unpublished');
            }
            
            $unpublish_sql = "UPDATE courses 
                             SET status = 'Draft',
                                 approval_status = 'pending'
                             WHERE course_id = ?";
            $unpublish_stmt = $conn->prepare($unpublish_sql);
            $unpublish_stmt->bind_param("i", $course_id);
            $unpublish_stmt->execute();
            
            $message = "Course '{$course['title']}' has been unpublished.";
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
    // Log activity
    $activity_sql = "INSERT INTO user_activity_logs 
                    (user_id, activity_type, activity_details, ip_address, user_agent) 
                    VALUES (?, 'course_action', ?, ?, ?)";
    $activity_stmt = $conn->prepare($activity_sql);
    $activity_details = "Performed action '$action' on course: {$course['title']}";
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $activity_stmt->bind_param("isss", $_SESSION['user_id'], $activity_details, $ip_address, $user_agent);
    $activity_stmt->execute();
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'course_id' => $course_id,
        'action' => $action
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>