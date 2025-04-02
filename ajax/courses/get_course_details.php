<?php
require '../../backend/session_start.php';
require '../../backend/config.php';

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

// Fetch course details
$stmt = $conn->prepare("
    SELECT c.*, 
        s.name AS subcategory_name,
        (SELECT COUNT(*) FROM course_sections cs WHERE cs.course_id = c.course_id) AS section_count,
        (SELECT COUNT(*) FROM course_sections cs 
         JOIN section_topics st ON cs.section_id = st.section_id 
         WHERE cs.course_id = c.course_id) AS topic_count,
        (SELECT COUNT(*) FROM course_sections cs 
         JOIN section_quizzes sq ON cs.section_id = sq.section_id 
         WHERE cs.course_id = c.course_id) AS quiz_count
    FROM courses c
    LEFT JOIN subcategories s ON c.subcategory_id = s.subcategory_id
    WHERE c.course_id = ? AND c.instructor_id = ?
");
$stmt->bind_param("ii", $course_id, $instructor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Course not found or you do not have permission to view it']);
    exit;
}

$course = $result->fetch_assoc();

// Format dates for better client-side display
$course['created_at'] = date('Y-m-d H:i:s', strtotime($course['created_at']));
$course['updated_at'] = date('Y-m-d H:i:s', strtotime($course['updated_at']));

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
    WHERE cs.course_id = ?
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
        WHERE section_id = ?
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
        WHERE section_id = ?
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

// Calculate completion percentage based on required steps
$completionChecks = [
    'basic_info' => !empty($course['title']) && !empty($course['short_description']) && !empty($course['thumbnail']),
    'description' => !empty($course['full_description']),
    'outcomes' => count($course['learning_outcomes']) > 0,
    'requirements' => count($course['requirements']) > 0,
    'sections' => $course['section_count'] > 0,
    'topics' => $course['topic_count'] > 0
];

$completedSteps = count(array_filter($completionChecks));
$totalSteps = count($completionChecks);
$course['completion_percentage'] = ($completedSteps / $totalSteps) * 100;

echo json_encode(['success' => true, 'course' => $course]);
$stmt->close();
$conn->close();
?>