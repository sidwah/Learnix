<?php
// Path: ajax/department/course_review_action.php
require '../../backend/session_start.php';
require_once '../../backend/config.php';
require_once 'notification_helper.php'; // Include our new notification helper

// Check if user is signed in as department head
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get parameters
$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';
$comments = isset($_POST['comments']) ? trim($_POST['comments']) : '';
$review_notes = isset($_POST['review_notes']) ? trim($_POST['review_notes']) : '';

// Validate parameters
if ($course_id === 0 || empty($action)) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

// Validate action type
$valid_actions = ['approve', 'request_revisions', 'reject'];
if (!in_array($action, $valid_actions)) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
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
    echo json_encode(['success' => false, 'message' => 'Department access error']);
    exit;
}

$department = $dept_result->fetch_assoc();
$department_id = $department['department_id'];

// Check if the course belongs to the department head's department and get course info
$course_query = "SELECT c.*, i.instructor_id, u.email as instructor_email, 
                 CONCAT(u.first_name, ' ', u.last_name) as instructor_name
                 FROM courses c
                 JOIN course_instructors ci ON c.course_id = ci.course_id AND ci.is_primary = 1
                 JOIN instructors i ON ci.instructor_id = i.instructor_id
                 JOIN users u ON i.user_id = u.user_id
                 WHERE c.course_id = ? AND c.department_id = ? AND c.deleted_at IS NULL";
$stmt = $conn->prepare($course_query);
$stmt->bind_param("ii", $course_id, $department_id);
$stmt->execute();
$course_result = $stmt->get_result();

if ($course_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to review this course']);
    exit;
}

$course_data = $course_result->fetch_assoc();
$current_status = $course_data['approval_status'];
$instructor_email = $course_data['instructor_email'];
$instructor_name = $course_data['instructor_name'];
$instructor_id = $course_data['instructor_id'];
$course_title = $course_data['title'];

// Begin transaction
$conn->begin_transaction();

try {
    // Set the new status based on the action
    $new_status = '';
    $notification_type = '';
    $notification_message = '';
    
    switch ($action) {
        case 'approve':
            $new_status = 'approved';
            $notification_type = 'course_approved';
            $notification_message = "Your course \"$course_title\" has been approved by the department.";
            break;
            
        case 'request_revisions':
            $new_status = 'revisions_requested';
            $notification_type = 'course_revision';
            $notification_message = "Revisions requested for your course \"$course_title\".";
            
            // Comments are required for revision requests
            if (empty($comments)) {
                throw new Exception('You must provide feedback when requesting revisions');
            }
            break;
            
        case 'reject':
            $new_status = 'rejected';
            $notification_type = 'course_rejected';
            $notification_message = "Your course \"$course_title\" has been rejected.";
            
            // Comments are required for rejections
            if (empty($comments)) {
                throw new Exception('You must provide a reason when rejecting a course');
            }
            break;
    }
    
    // Update course status
    $update_query = "UPDATE courses SET approval_status = ? WHERE course_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("si", $new_status, $course_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception('Failed to update course status');
    }
    
    // Record review history
    $history_query = "INSERT INTO course_review_history (course_id, reviewed_by, previous_status, new_status, comments, review_date) 
                     VALUES (?, ?, ?, ?, ?, NOW())";
    $history_stmt = $conn->prepare($history_query);
    $history_stmt->bind_param("iisss", $course_id, $_SESSION['user_id'], $current_status, $new_status, $comments);
    
    if (!$history_stmt->execute()) {
        throw new Exception('Failed to record review history');
    }
    
    // Save review notes if provided
    if (!empty($review_notes)) {
        // Check if the table exists
        $table_check_query = "SHOW TABLES LIKE 'course_review_notes'";
        $table_check_result = $conn->query($table_check_query);
        
        if ($table_check_result->num_rows === 0) {
            // Create the table if it doesn't exist
            $create_table_query = "CREATE TABLE `course_review_notes` (
                                  `note_id` int NOT NULL AUTO_INCREMENT,
                                  `course_id` int NOT NULL,
                                  `reviewer_id` int NOT NULL,
                                  `notes` text NOT NULL,
                                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                  PRIMARY KEY (`note_id`),
                                  UNIQUE KEY `course_reviewer` (`course_id`, `reviewer_id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            
            if (!$conn->query($create_table_query)) {
                // Just log this error, don't throw exception
                error_log("Failed to create notes table: " . $conn->error);
            }
        }
        
        // Now try to insert or update notes
        $notes_query = "INSERT INTO course_review_notes (course_id, reviewer_id, notes) 
                       VALUES (?, ?, ?) 
                       ON DUPLICATE KEY UPDATE notes = VALUES(notes), updated_at = NOW()";
        $notes_stmt = $conn->prepare($notes_query);
        $notes_stmt->bind_param("iis", $course_id, $_SESSION['user_id'], $review_notes);
        
        if (!$notes_stmt->execute()) {
            // Just log this error, don't throw exception since notes are optional
            error_log("Failed to save review notes for course ID: $course_id");
        }
    }
    
    // Create notification for the instructor
    $notification_data = [
        'user_id' => $course_data['instructor_id'],
        'type' => $notification_type,
        'course_id' => $course_id,
        'course_title' => $course_title,
        'message' => $notification_message,
        'comments' => $comments,
        'reviewer_name' => $_SESSION['first_name'] . ' ' . $_SESSION['last_name'],
        'instructor_email' => $instructor_email,
        'instructor_name' => $instructor_name
    ];
    
    // Send notification using our helper
    sendCourseReviewNotification($conn, $notification_data);
    
    // Commit the transaction
    $conn->commit();
    
    // Prepare success message
    $message = '';
    switch ($action) {
        case 'approve':
            $message = 'Course has been approved successfully!';
            break;
        case 'request_revisions':
            $message = 'Revision request has been sent to the instructor.';
            break;
        case 'reject':
            $message = 'Course has been rejected.';
            break;
    }
    
    echo json_encode(['success' => true, 'message' => $message]);
    
} catch (Exception $e) {
    // Rollback the transaction
    $conn->rollback();
    
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    // Close statements
    if (isset($update_stmt)) $update_stmt->close();
    if (isset($history_stmt)) $history_stmt->close();
    if (isset($notes_stmt)) $notes_stmt->close();
}
?>