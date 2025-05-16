<?php
//ajax/courses/submit_for_review.php - UPDATED for institutional LMS
require_once '../../backend/config.php';
require_once '../../backend/session_start.php';

/**
 * Logs instructor activity on courses for accountability
 * 
 * @param mysqli $conn Database connection
 * @param int $course_id The course ID
 * @param int $instructor_id The instructor making the change
 * @param string $action_type Type of action (create, update, delete, etc)
 * @param string $entity_type What is being changed (course, section, topic, quiz)
 * @param int $entity_id ID of specific entity being changed (optional)
 * @param array $change_details Details of changes made (optional)
 * @return bool Success status
 */
function logCourseActivity($conn, $course_id, $instructor_id, $action_type, $entity_type, $entity_id = null, $change_details = null) {
    // Same function implementation as original...
    $tableCheck = $conn->query("SHOW TABLES LIKE 'course_activity_logs'");
    
    if ($tableCheck->num_rows == 0) {
        $conn->query("
            CREATE TABLE IF NOT EXISTS `course_activity_logs` (
              `log_id` int NOT NULL AUTO_INCREMENT,
              `course_id` int NOT NULL,
              `instructor_id` int NOT NULL,
              `action_type` enum('create','update','delete','submit_review','comment','view') NOT NULL,
              `entity_type` varchar(50) NOT NULL COMMENT 'course, section, topic, quiz, etc.',
              `entity_id` int DEFAULT NULL,
              `change_details` json DEFAULT NULL COMMENT 'Before/after values in JSON',
              `performed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`log_id`),
              KEY `idx_course_logs_course` (`course_id`),
              KEY `idx_course_logs_instructor` (`instructor_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }
    
    $stmt = $conn->prepare("
        INSERT INTO course_activity_logs 
        (course_id, instructor_id, action_type, entity_type, entity_id, change_details)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $json_details = $change_details ? json_encode($change_details) : null;
    $stmt->bind_param("iissss", $course_id, $instructor_id, $action_type, $entity_type, $entity_id, $json_details);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Check if user is signed in and is an instructor
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get instructor_id for the current user
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT instructor_id FROM instructors WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Instructor not found']);
    exit;
}

$instructor = $result->fetch_assoc();
$instructor_id = $instructor['instructor_id'];
$stmt->close();

// Get course_id from POST
$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;

// Validate course access using junction table - FIXED to match institutional pattern
$stmt = $conn->prepare("
    SELECT c.course_id, c.title, c.approval_status, ci.is_primary 
    FROM courses c
    JOIN course_instructors ci ON c.course_id = ci.course_id
    WHERE c.course_id = ? AND ci.instructor_id = ? AND c.deleted_at IS NULL AND ci.deleted_at IS NULL
");
$stmt->bind_param("ii", $course_id, $instructor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Course not found or you do not have permission']);
    exit;
}

$course = $result->fetch_assoc();
$course_title = $course['title'];
$prev_approval_status = $course['approval_status'];
$is_primary = (bool)$course['is_primary'];

// ADDED: Check if instructor is primary for this course (institutional requirement)
if (!$is_primary) {
    echo json_encode(['success' => false, 'message' => 'Only the primary instructor can submit a course for review']);
    exit;
}

// Begin transaction
$conn->begin_transaction();

try {
    // Update course status to pending review
    $stmt = $conn->prepare("UPDATE courses SET status = 'Draft', approval_status = 'submitted_for_review', updated_at = NOW() WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $result = $stmt->execute();

    if (!$result) {
        throw new Exception("Failed to update course status");
    }

    // Create a review request
    $current_date = date('Y-m-d H:i:s');
    $notes = "Instructor has requested review for course publication.";

    $stmt = $conn->prepare("
        INSERT INTO course_review_requests 
        (course_id, requested_by, request_notes, status, created_at) 
        VALUES (?, ?, ?, 'Pending', ?)
    ");
    $stmt->bind_param("iiss", $course_id, $instructor_id, $notes, $current_date);
    $result = $stmt->execute();

    if (!$result) {
        throw new Exception("Failed to create review request");
    }

    // UPDATED: Find department heads AND secretaries to notify (institutional workflow)
    $stmt = $conn->prepare("
        SELECT u.user_id, u.email, ds.role 
        FROM users u
        JOIN department_staff ds ON u.user_id = ds.user_id
        JOIN courses c ON c.department_id = ds.department_id
        WHERE c.course_id = ? AND ds.role IN ('head', 'secretary') AND ds.status = 'active' AND ds.deleted_at IS NULL
    ");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $department_staff = $stmt->get_result();
    
    // Generate notifications for department staff
    while ($staff = $department_staff->fetch_assoc()) {
        // Build appropriate notification based on role
        $notification_title = "Course Review Request: " . $course_title;
        $notification_message = "A new course has been submitted for review by an instructor.";
        
        // Insert notification into user_notifications table
        $stmt = $conn->prepare("
            INSERT INTO user_notifications (user_id, type, title, message, related_id, related_type)
            VALUES (?, 'course_review', ?, ?, ?, 'course')
        ");
        $stmt->bind_param("issi", $staff['user_id'], $notification_title, $notification_message, $course_id);
        $stmt->execute();
    }

    // Log the activity
    $changeDetails = [
        'approval_status' => [
            'old' => $prev_approval_status,
            'new' => 'submitted_for_review'
        ],
        'status' => [
            'new' => 'Draft' // CHANGED to Draft (institutional workflow)
        ],
        'requested_at' => $current_date
    ];
    
    logCourseActivity($conn, $course_id, $instructor_id, 'submit_review', 'course', $course_id, $changeDetails);

    // Log the submission for auditing
    error_log("Course review requested by instructor ID " . $instructor_id .
        " for course ID " . $course_id . " (" . $course_title . ")");

    // Commit the transaction
    $conn->commit();

    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Course submitted for review successfully. Department staff will be notified.',
        'course_title' => $course_title
    ]);
} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();

    // Error response
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'error' => $conn->error
    ]);
}
?>