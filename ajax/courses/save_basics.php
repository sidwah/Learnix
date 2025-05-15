<?php
//ajax/courses/save_basics.php
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

// Validate required input
if (!isset($_POST['course_id']) || !isset($_POST['title']) || !isset($_POST['short_description']) || 
    !isset($_POST['subcategory_id']) || !isset($_POST['course_level'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$course_id = intval($_POST['course_id']);
$title = trim($_POST['title']);
$short_description = trim($_POST['short_description']);
$subcategory_id = intval($_POST['subcategory_id']);
$course_level = $_POST['course_level'];

// Validate inputs
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Course title cannot be empty']);
    exit;
}

if (empty($short_description)) {
    echo json_encode(['success' => false, 'message' => 'Short description cannot be empty']);
    exit;
}

if ($subcategory_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Please select a valid subcategory']);
    exit;
}

// Validate course level
$valid_levels = ['Beginner', 'Intermediate', 'Advanced', 'All Levels'];
if (!in_array($course_level, $valid_levels)) {
    echo json_encode(['success' => false, 'message' => 'Please select a valid course level']);
    exit;
}

// Verify that the course belongs to the current instructor using junction table
$stmt = $conn->prepare("
    SELECT c.course_id, c.title, c.short_description, c.subcategory_id, c.course_level 
    FROM courses c
    JOIN course_instructors ci ON c.course_id = ci.course_id
    WHERE c.course_id = ? AND ci.instructor_id = ? AND c.deleted_at IS NULL
");
$stmt->bind_param("ii", $course_id, $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
$originalCourse = $result->fetch_assoc();
$stmt->close();

if (!$originalCourse) {
    echo json_encode(['success' => false, 'message' => 'Course not found or not authorized']);
    exit;
}

// Start transaction to ensure both update and logging succeed
$conn->begin_transaction();

try {
    // Update the course basic information
    $stmt = $conn->prepare("UPDATE courses SET 
                            title = ?, 
                            short_description = ?, 
                            subcategory_id = ?, 
                            course_level = ?,
                            updated_at = NOW() 
                            WHERE course_id = ?");
                            
    $stmt->bind_param("ssisi", $title, $short_description, $subcategory_id, $course_level, $course_id);
    $success = $stmt->execute();
    $stmt->close();
    
    // Log the changes
    $changes = [];
    if ($originalCourse['title'] != $title) {
        $changes['title'] = ['old' => $originalCourse['title'], 'new' => $title];
    }
    if ($originalCourse['short_description'] != $short_description) {
        $changes['short_description'] = ['old' => $originalCourse['short_description'], 'new' => $short_description];
    }
    if ($originalCourse['subcategory_id'] != $subcategory_id) {
        $changes['subcategory_id'] = ['old' => $originalCourse['subcategory_id'], 'new' => $subcategory_id];
    }
    if ($originalCourse['course_level'] != $course_level) {
        $changes['course_level'] = ['old' => $originalCourse['course_level'], 'new' => $course_level];
    }
    
    if (!empty($changes)) {
        logCourseActivity($conn, $course_id, $instructor_id, 'update', 'course_basics', $course_id, $changes);
    }
    
    // Commit the transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Course basics saved successfully']);
} catch (Exception $e) {
    // Roll back the transaction if something failed
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>