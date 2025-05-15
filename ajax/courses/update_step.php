<?php
//ajax/courses/update_step.php
require '../../backend/session_start.php';
require '../../backend/config.php';

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
    // Check if the course_activity_logs table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'course_activity_logs'");
    
    // If table doesn't exist, create it
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

// Check if the user is signed in and has the 'instructor' role
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

// Validate input
if (!isset($_POST['course_id']) || !isset($_POST['step'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$course_id = intval($_POST['course_id']);
$step = intval($_POST['step']);

// Validate the step value
if ($step < 1 || $step > 6) {
    echo json_encode(['success' => false, 'message' => 'Invalid step value']);
    exit;
}

// Verify that the course belongs to the current instructor using junction table
$stmt = $conn->prepare("
    SELECT c.course_id, c.creation_step
    FROM courses c
    JOIN course_instructors ci ON c.course_id = ci.course_id
    WHERE c.course_id = ? AND ci.instructor_id = ? AND c.deleted_at IS NULL
");
$stmt->bind_param("ii", $course_id, $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();
$stmt->close();

if (!$course) {
    echo json_encode(['success' => false, 'message' => 'Course not found or not authorized']);
    exit;
}

// Store original step for change tracking
$original_step = $course['creation_step'];

// Begin transaction
$conn->begin_transaction();

try {
    // Update the course creation_step
    $stmt = $conn->prepare("UPDATE courses SET creation_step = ?, updated_at = NOW() WHERE course_id = ?");
    $stmt->bind_param("ii", $step, $course_id);
    $success = $stmt->execute();
    $stmt->close();
    
    if (!$success) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    // Log the step change if it's different
    if ($original_step != $step) {
        $stepNames = [
            1 => 'Basic Info',
            2 => 'Requirements',
            3 => 'Structure',
            4 => 'Content',
            5 => 'Settings',
            6 => 'Review'
        ];
        
        $changeDetails = [
            'creation_step' => [
                'old' => $original_step,
                'new' => $step
            ],
            'step_name' => $stepNames[$step] ?? 'Unknown',
            'progress' => round(($step / 6) * 100) . '%'
        ];
        
        logCourseActivity($conn, $course_id, $instructor_id, 'update', 'course_progress', $course_id, $changeDetails);
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Step updated successfully']);
    
} catch (Exception $e) {
    // Rollback in case of error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>