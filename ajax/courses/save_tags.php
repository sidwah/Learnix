<?php
//ajax/courses/save_tags.php
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

// Validate required input
if (!isset($_POST['course_id']) || !isset($_POST['tags'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$course_id = intval($_POST['course_id']);
$tags = $_POST['tags'];

// Ensure tags is an array
if (!is_array($tags)) {
    echo json_encode(['success' => false, 'message' => 'Invalid tags format']);
    exit;
}

// Verify that the course belongs to the current instructor using junction table
$stmt = $conn->prepare("
    SELECT c.course_id
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

// Get existing tags for change tracking
$existingTags = [];
$stmt = $conn->prepare("
    SELECT t.tag_id, t.tag_name 
    FROM tags t
    JOIN course_tag_mapping ctm ON t.tag_id = ctm.tag_id
    WHERE ctm.course_id = ? AND t.deleted_at IS NULL
");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $existingTags[$row['tag_id']] = $row['tag_name'];
}
$stmt->close();

// Get tag names for the new tags list
$newTagNames = [];
if (!empty($tags)) {
    $placeholders = implode(',', array_fill(0, count($tags), '?'));
    $types = str_repeat('i', count($tags));
    
    $query = "SELECT tag_id, tag_name FROM tags WHERE tag_id IN ($placeholders) AND deleted_at IS NULL";
    $stmt = $conn->prepare($query);
    
    // Dynamically bind parameters
    $params = array_values($tags);
    $bindParams = array_merge([$types], $params);
    $ref = new ReflectionClass('mysqli_stmt');
    $method = $ref->getMethod('bind_param');
    $method->invokeArgs($stmt, $bindParams);
    
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $newTagNames[$row['tag_id']] = $row['tag_name'];
    }
    $stmt->close();
}

// Start transaction
$conn->begin_transaction();

try {
    // Delete existing tag mappings
    $stmt = $conn->prepare("DELETE FROM course_tag_mapping WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $stmt->close();
    
    // Insert new tag mappings
    if (!empty($tags)) {
        $stmt = $conn->prepare("INSERT INTO course_tag_mapping (course_id, tag_id) VALUES (?, ?)");
        foreach ($tags as $tag_id) {
            $tag_id = intval($tag_id);
            if ($tag_id > 0) {
                $stmt->bind_param("ii", $course_id, $tag_id);
                $stmt->execute();
            }
        }
        $stmt->close();
    }
    
    // Update course last modified timestamp
    $stmt = $conn->prepare("UPDATE courses SET updated_at = NOW() WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $stmt->close();
    
    // Calculate tags added and removed
    $addedTags = [];
    $removedTags = [];
    
    foreach ($newTagNames as $id => $name) {
        if (!isset($existingTags[$id])) {
            $addedTags[$id] = $name;
        }
    }
    
    foreach ($existingTags as $id => $name) {
        if (!isset($newTagNames[$id])) {
            $removedTags[$id] = $name;
        }
    }
    
    // Log changes if there were any
    if (!empty($addedTags) || !empty($removedTags)) {
        $changeDetails = [
            'added_tags' => $addedTags,
            'removed_tags' => $removedTags,
            'tag_count_before' => count($existingTags),
            'tag_count_after' => count($newTagNames)
        ];
        
        logCourseActivity($conn, $course_id, $instructor_id, 'update', 'course_tags', $course_id, $changeDetails);
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Course tags saved successfully']);
    
} catch (Exception $e) {
    // Rollback in case of error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>