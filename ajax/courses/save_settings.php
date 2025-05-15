<?php
//ajax/courses/save_settings.php
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
if (!isset($_POST['course_id']) || !isset($_POST['price']) || !isset($_POST['access_level'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$course_id = intval($_POST['course_id']);
$price = floatval($_POST['price']);
$certificate_enabled = isset($_POST['certificate_enabled']) ? intval($_POST['certificate_enabled']) : 0;
$access_level = $_POST['access_level'];
$visibility = isset($_POST['visibility']) ? $_POST['visibility'] : 'Public';
$access_password = isset($_POST['access_password']) ? $_POST['access_password'] : null;
$enrollment_limit = isset($_POST['enrollment_limit']) && !empty($_POST['enrollment_limit']) ? intval($_POST['enrollment_limit']) : null;
$estimated_duration = isset($_POST['estimated_duration']) ? $_POST['estimated_duration'] : null;
$enrollment_start = isset($_POST['enrollment_start']) && !empty($_POST['enrollment_start']) ? $_POST['enrollment_start'] : null;
$enrollment_end = isset($_POST['enrollment_end']) && !empty($_POST['enrollment_end']) ? $_POST['enrollment_end'] : null;

// Validate inputs
if ($price < 0) {
    echo json_encode(['success' => false, 'message' => 'Price cannot be negative']);
    exit;
}

// If price is between 0.01 and 0.99, reject it
if ($price > 0 && $price < 0.99) {
    echo json_encode(['success' => false, 'message' => 'Minimum price for paid courses is $0.99']);
    exit;
}

// Validate access level
$valid_access_levels = ['Public', 'Restricted'];
if (!in_array($access_level, $valid_access_levels)) {
    echo json_encode(['success' => false, 'message' => 'Invalid access level']);
    exit;
}

// Validate visibility
$valid_visibility = ['Public', 'Private', 'Password Protected', 'Coming Soon'];
if (!in_array($visibility, $valid_visibility)) {
    echo json_encode(['success' => false, 'message' => 'Invalid visibility setting']);
    exit;
}

// Require password if visibility is set to Password Protected
if ($visibility === 'Password Protected' && empty($access_password)) {
    echo json_encode(['success' => false, 'message' => 'Password is required for password-protected courses']);
    exit;
}

// Verify that the course belongs to the current instructor using junction table
$stmt = $conn->prepare("
    SELECT c.course_id, c.price, c.certificate_enabled, c.access_level
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

// Get current course settings for tracking changes
$stmt = $conn->prepare("
    SELECT enrollment_limit, enrollment_period_start, enrollment_period_end, 
           visibility, access_password, estimated_duration
    FROM course_settings 
    WHERE course_id = ?
");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
$currentSettings = $result->fetch_assoc();
$stmt->close();

// Start transaction
$conn->begin_transaction();

try {
    // Track changes for logging
    $changes = [];
    
    // Check for changes in the courses table
    if ($course['price'] != $price) {
        $changes['price'] = ['old' => $course['price'], 'new' => $price];
    }
    if ($course['certificate_enabled'] != $certificate_enabled) {
        $changes['certificate_enabled'] = ['old' => $course['certificate_enabled'], 'new' => $certificate_enabled];
    }
    if ($course['access_level'] != $access_level) {
        $changes['access_level'] = ['old' => $course['access_level'], 'new' => $access_level];
    }
    
    // Update course table
    $stmt = $conn->prepare("UPDATE courses SET 
                            price = ?, 
                            certificate_enabled = ?, 
                            access_level = ?,
                            updated_at = NOW() 
                            WHERE course_id = ?");
                            
    $stmt->bind_param("disi", $price, $certificate_enabled, $access_level, $course_id);
    $stmt->execute();
    $stmt->close();
    
    // Check if course settings already exist
    $stmt = $conn->prepare("SELECT setting_id FROM course_settings WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_settings = $result->fetch_assoc();
    $stmt->close();
    
    // Track changes in settings if they exist
    if ($currentSettings) {
        if ($currentSettings['enrollment_limit'] != $enrollment_limit) {
            $changes['enrollment_limit'] = ['old' => $currentSettings['enrollment_limit'], 'new' => $enrollment_limit];
        }
        if ($currentSettings['enrollment_period_start'] != $enrollment_start) {
            $changes['enrollment_period_start'] = ['old' => $currentSettings['enrollment_period_start'], 'new' => $enrollment_start];
        }
        if ($currentSettings['enrollment_period_end'] != $enrollment_end) {
            $changes['enrollment_period_end'] = ['old' => $currentSettings['enrollment_period_end'], 'new' => $enrollment_end];
        }
        if ($currentSettings['visibility'] != $visibility) {
            $changes['visibility'] = ['old' => $currentSettings['visibility'], 'new' => $visibility];
        }
        if ($currentSettings['estimated_duration'] != $estimated_duration) {
            $changes['estimated_duration'] = ['old' => $currentSettings['estimated_duration'], 'new' => $estimated_duration];
        }
        
        // Don't log password changes for security
    }
    
    if ($existing_settings) {
        // Update existing settings
        $stmt = $conn->prepare("UPDATE course_settings SET 
                                enrollment_limit = ?, 
                                enrollment_period_start = ?, 
                                enrollment_period_end = ?,
                                visibility = ?,
                                access_password = ?,
                                estimated_duration = ?,
                                last_updated = NOW() 
                                WHERE course_id = ?");
                                
        $stmt->bind_param("isssssi", $enrollment_limit, $enrollment_start, $enrollment_end, $visibility, $access_password, $estimated_duration, $course_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // Insert new settings
        $stmt = $conn->prepare("INSERT INTO course_settings (
                                course_id, 
                                enrollment_limit,
                                enrollment_period_start,
                                enrollment_period_end,
                                visibility,
                                access_password,
                                estimated_duration,
                                last_updated
                                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                                
        $stmt->bind_param("iisssss", $course_id, $enrollment_limit, $enrollment_start, $enrollment_end, $visibility, $access_password, $estimated_duration);
        $stmt->execute();
        $stmt->close();
        
        // Log that settings were created for the first time
        $changes['settings_created'] = true;
    }
    
    // Log the activity if there were changes
    if (!empty($changes)) {
        logCourseActivity($conn, $course_id, $instructor_id, 'update', 'course_settings', $course_id, $changes);
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Course settings saved successfully']);
    
} catch (Exception $e) {
    // Rollback in case of error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>