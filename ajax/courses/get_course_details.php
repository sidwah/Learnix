<?php
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

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is authorized
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if course_id is provided
if (!isset($_GET['course_id']) || !is_numeric($_GET['course_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid course ID']);
    exit;
}

$course_id = intval($_GET['course_id']);
$user_id = $_SESSION['user_id'];

// Get instructor_id for the current user
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

// Fetch course details with department name and instructor role
$stmt = $conn->prepare("
    SELECT c.*, 
        s.name AS subcategory_name,
        d.name AS department_name,
        CASE WHEN ci.is_primary = 1 THEN 'Primary' ELSE 'Co-instructor' END AS instructor_role,
        (SELECT COUNT(*) FROM course_sections cs WHERE cs.course_id = c.course_id) AS section_count,
        (SELECT COUNT(*) FROM course_sections cs 
         JOIN section_topics st ON cs.section_id = st.section_id 
         WHERE cs.course_id = c.course_id) AS topic_count,
        (SELECT COUNT(*) FROM course_sections cs 
         JOIN section_quizzes sq ON cs.section_id = sq.section_id 
         WHERE cs.course_id = c.course_id) AS quiz_count
    FROM courses c
    JOIN course_instructors ci ON c.course_id = ci.course_id
    LEFT JOIN subcategories s ON c.subcategory_id = s.subcategory_id
    LEFT JOIN departments d ON c.department_id = d.department_id
    WHERE c.course_id = ? AND ci.instructor_id = ? AND c.deleted_at IS NULL
");
$stmt->bind_param("ii", $course_id, $instructor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Course not found or you do not have permission to view it']);
    exit;
}

$course = $result->fetch_assoc();

// Log the course view action
logCourseActivity($conn, $course_id, $instructor_id, 'view', 'course', $course_id);

// Safely format dates
$course['created_at'] = !empty($course['created_at']) ? date('Y-m-d H:i:s', strtotime($course['created_at'])) : null;
$course['updated_at'] = !empty($course['updated_at']) ? date('Y-m-d H:i:s', strtotime($course['updated_at'])) : null;

// Get learning outcomes
$stmt = $conn->prepare("
    SELECT outcome_text 
    FROM course_learning_outcomes 
    WHERE course_id = ?
");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$outcomeResult = $stmt->get_result();
$course['learning_outcomes'] = [];
while ($outcome = $outcomeResult->fetch_assoc()) {
    $course['learning_outcomes'][] = $outcome['outcome_text'];
}

// Get requirements
$stmt = $conn->prepare("
    SELECT requirement_text 
    FROM course_requirements 
    WHERE course_id = ?
");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$requirementResult = $stmt->get_result();
$course['requirements'] = [];
while ($requirement = $requirementResult->fetch_assoc()) {
    $course['requirements'][] = $requirement['requirement_text'];
}

// Get sections with topics and quizzes
$stmt = $conn->prepare("
    SELECT cs.section_id, cs.title AS section_title, cs.position AS section_position
    FROM course_sections cs
    WHERE cs.course_id = ? AND cs.deleted_at IS NULL
    ORDER BY cs.position
");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$sectionResult = $stmt->get_result();
$course['sections'] = [];

while ($section = $sectionResult->fetch_assoc()) {
    $sectionId = $section['section_id'];
    
    // Get topics for this section
    $topicStmt = $conn->prepare("
        SELECT topic_id, title, position
        FROM section_topics
        WHERE section_id = ? AND deleted_at IS NULL
        ORDER BY position
    ");
    $topicStmt->bind_param("i", $sectionId);
    $topicStmt->execute();
    $topicResult = $topicStmt->get_result();
    $section['topics'] = [];
    
    while ($topic = $topicResult->fetch_assoc()) {
        $section['topics'][] = $topic;
    }
    
    // Get quizzes for this section
    $quizStmt = $conn->prepare("
        SELECT quiz_id, quiz_title, pass_mark
        FROM section_quizzes
        WHERE section_id = ? AND deleted_at IS NULL
    ");
    $quizStmt->bind_param("i", $sectionId);
    $quizStmt->execute();
    $quizResult = $quizStmt->get_result();
    $section['quizzes'] = [];
    
    while ($quiz = $quizResult->fetch_assoc()) {
        $section['quizzes'][] = $quiz;
    }
    
    $course['sections'][] = $section;
}

// Use the step-based progress calculation for consistency with the table view
$course['completion_percentage'] = 0;
if (isset($course['creation_step'])) {
    // Assuming 6 steps total: Basic info, Requirements, Structure, Content, Settings, Review
    $totalSteps = 6;
    $currentStep = min(max(1, intval($course['creation_step'])), $totalSteps);
    $course['completion_percentage'] = round(($currentStep / $totalSteps) * 100);
}

// Get any review notes if available
$stmt = $conn->prepare("
    SELECT review_notes 
    FROM course_review_requests 
    WHERE course_id = ? 
    ORDER BY updated_at DESC
    LIMIT 1
");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$reviewResult = $stmt->get_result();
if ($reviewResult->num_rows > 0) {
    $reviewNotes = $reviewResult->fetch_assoc();
    $course['review_notes'] = $reviewNotes['review_notes'];
} else {
    $course['review_notes'] = null;
}

// Convert NULL values to empty strings to avoid JSON issues
array_walk_recursive($course, function(&$value) {
    if (is_null($value)) {
        $value = '';
    }
});

echo json_encode(['success' => true, 'course' => $course], JSON_PARTIAL_OUTPUT_ON_ERROR);
$stmt->close();
$conn->close();
?>