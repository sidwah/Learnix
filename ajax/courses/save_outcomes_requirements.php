<?php
//ajax/courses/save_outcomes_requirements.php
// Set proper headers first
header('Content-Type: application/json');

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

// Initialize response array
$response = ['success' => false, 'message' => ''];

try {
    // 1. Authentication and Authorization Check
    if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
        throw new Exception('Unauthorized access', 403);
    }

    // Get instructor_id for the current user
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT instructor_id FROM instructors WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Instructor not found', 403);
    }

    $instructor = $result->fetch_assoc();
    $instructor_id = $instructor['instructor_id'];
    $stmt->close();

    // 2. Request Method Validation
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method', 405);
    }

    // 3. Content-Type Check (handle both form-data and JSON)
    $contentType = isset($_SERVER['CONTENT_TYPE']) ? trim($_SERVER['CONTENT_TYPE']) : '';
    if (strpos($contentType, 'application/json') !== false) {
        $json = file_get_contents('php://input');
        $_POST = json_decode($json, true);
    }

    // 4. Required Parameters Validation
    $requiredParams = ['course_id', 'outcomes', 'requirements'];
    foreach ($requiredParams as $param) {
        if (!isset($_POST[$param])) {
            throw new Exception("Missing required parameter: $param", 400);
        }
    }

    // 5. Data Type Validation
    $course_id = filter_var($_POST['course_id'], FILTER_VALIDATE_INT);
    if ($course_id === false || $course_id <= 0) {
        throw new Exception('Invalid course ID', 400);
    }

    if (!is_array($_POST['outcomes']) || !is_array($_POST['requirements'])) {
        throw new Exception('Outcomes and requirements must be arrays', 400);
    }

    // 6. Course Ownership Verification using junction table
    $stmt = $conn->prepare("
        SELECT c.course_id 
        FROM courses c
        JOIN course_instructors ci ON c.course_id = ci.course_id
        WHERE c.course_id = ? AND ci.instructor_id = ? AND c.deleted_at IS NULL
    ");
    if (!$stmt) {
        throw new Exception('Database preparation failed', 500);
    }
    
    $stmt->bind_param("ii", $course_id, $instructor_id);
    if (!$stmt->execute()) {
        throw new Exception('Database query failed', 500);
    }
    
    $result = $stmt->get_result();
    $course = $result->fetch_assoc();
    $stmt->close();

    if (!$course) {
        throw new Exception('Course not found or not authorized', 404);
    }

    // Get existing outcomes and requirements for change tracking
    $existingOutcomes = [];
    $existingRequirements = [];
    
    $stmt = $conn->prepare("SELECT outcome_text FROM course_learning_outcomes WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $existingOutcomes[] = $row['outcome_text'];
    }
    $stmt->close();
    
    $stmt = $conn->prepare("SELECT requirement_text FROM course_requirements WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $existingRequirements[] = $row['requirement_text'];
    }
    $stmt->close();

    // 7. Database Transaction
    $conn->begin_transaction();

    try {
        // Delete existing outcomes
        $stmt = $conn->prepare("DELETE FROM course_learning_outcomes WHERE course_id = ?");
        if (!$stmt || !$stmt->bind_param("i", $course_id) || !$stmt->execute()) {
            throw new Exception('Failed to delete existing outcomes', 500);
        }
        $stmt->close();

        // Insert new outcomes
        $newOutcomes = [];
        if (!empty($_POST['outcomes'])) {
            $stmt = $conn->prepare("INSERT INTO course_learning_outcomes (course_id, outcome_text) VALUES (?, ?)");
            if (!$stmt) {
                throw new Exception('Failed to prepare outcomes statement', 500);
            }
            
            foreach ($_POST['outcomes'] as $outcome) {
                $outcome_text = trim($outcome);
                if (!empty($outcome_text)) {
                    if (!$stmt->bind_param("is", $course_id, $outcome_text) || !$stmt->execute()) {
                        throw new Exception('Failed to insert outcome: ' . htmlspecialchars($outcome_text), 500);
                    }
                    $newOutcomes[] = $outcome_text;
                }
            }
            $stmt->close();
        }

        // Delete existing requirements
        $stmt = $conn->prepare("DELETE FROM course_requirements WHERE course_id = ?");
        if (!$stmt || !$stmt->bind_param("i", $course_id) || !$stmt->execute()) {
            throw new Exception('Failed to delete existing requirements', 500);
        }
        $stmt->close();

        // Insert new requirements
        $newRequirements = [];
        if (!empty($_POST['requirements'])) {
            $stmt = $conn->prepare("INSERT INTO course_requirements (course_id, requirement_text) VALUES (?, ?)");
            if (!$stmt) {
                throw new Exception('Failed to prepare requirements statement', 500);
            }
            
            foreach ($_POST['requirements'] as $requirement) {
                $requirement_text = trim($requirement);
                if (!empty($requirement_text)) {
                    if (!$stmt->bind_param("is", $course_id, $requirement_text) || !$stmt->execute()) {
                        throw new Exception('Failed to insert requirement: ' . htmlspecialchars($requirement_text), 500);
                    }
                    $newRequirements[] = $requirement_text;
                }
            }
            $stmt->close();
        }

        // Update course timestamp
        $stmt = $conn->prepare("UPDATE courses SET updated_at = NOW() WHERE course_id = ?");
        if (!$stmt || !$stmt->bind_param("i", $course_id) || !$stmt->execute()) {
            throw new Exception('Failed to update course timestamp', 500);
        }
        $stmt->close();

        // Log the changes
        $changeDetails = [
            'outcomes' => [
                'before_count' => count($existingOutcomes),
                'after_count' => count($newOutcomes)
            ],
            'requirements' => [
                'before_count' => count($existingRequirements),
                'after_count' => count($newRequirements)
            ]
        ];
        
        logCourseActivity($conn, $course_id, $instructor_id, 'update', 'course_outcomes_requirements', $course_id, $changeDetails);

        $conn->commit();
        
        $response = [
            'success' => true,
            'message' => 'Course outcomes and requirements saved successfully'
        ];

    } catch (Exception $e) {
        $conn->rollback();
        throw $e; // Re-throw to outer catch block
    }

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 400);
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>