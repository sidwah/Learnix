<?php
// Include necessary files
require_once '../../backend/config.php';
session_start();

// Check if user is logged in and is an instructor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Get instructor_id from user_id
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT instructor_id FROM instructors WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Instructor not found']);
    exit;
}

$instructor = $result->fetch_assoc();
$instructor_id = $instructor['instructor_id'];

// Validate input data
if (!isset($_POST['course_id']) || empty($_POST['course_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Course ID is required']);
    exit;
}

$course_id = intval($_POST['course_id']);

// FIXED: Check if course exists and instructor has access to it using course_instructors junction table
$stmt = $conn->prepare("
    SELECT ci.course_id 
    FROM course_instructors ci
    WHERE ci.course_id = ? 
    AND ci.instructor_id = ?
    AND ci.deleted_at IS NULL
");
$stmt->bind_param("ii", $course_id, $instructor_id);
$stmt->execute();
$access_result = $stmt->get_result();

if ($access_result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Course not found or not authorized']);
    exit;
}
$stmt->close();

// Now get the course details
$stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course_result = $stmt->get_result();

if ($course_result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Course not found']);
    exit;
}

$course = $course_result->fetch_assoc();
$stmt->close();

// Initialize validation issues array
$validation_issues = [];

// Check basic course info
if (empty($course['title'])) {
    $validation_issues[] = [
        'section' => 'Basic Info',
        'issue' => 'Course title is missing',
        'step' => 1
    ];
}

if (empty($course['short_description'])) {
    $validation_issues[] = [
        'section' => 'Basic Info',
        'issue' => 'Short description is missing',
        'step' => 1
    ];
}

if (empty($course['subcategory_id'])) {
    $validation_issues[] = [
        'section' => 'Basic Info',
        'issue' => 'Category/Subcategory is not selected',
        'step' => 1
    ];
}

if (empty($course['thumbnail'])) {
    $validation_issues[] = [
        'section' => 'Basic Info',
        'issue' => 'Course thumbnail is missing',
        'step' => 1
    ];
}

// Check full description
if (empty($course['full_description'])) {
    $validation_issues[] = [
        'section' => 'Description',
        'issue' => 'Course description is missing',
        'step' => 2
    ];
}

// Check outcomes
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM course_learning_outcomes WHERE course_id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$outcomes_result = $stmt->get_result();
$outcomes_count = $outcomes_result->fetch_assoc()['count'];

if ($outcomes_count == 0) {
    $validation_issues[] = [
        'section' => 'Learning Outcomes',
        'issue' => 'No learning outcomes have been added',
        'step' => 3
    ];
}

// Check requirements
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM course_requirements WHERE course_id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$requirements_result = $stmt->get_result();
$requirements_count = $requirements_result->fetch_assoc()['count'];

if ($requirements_count == 0) {
    $validation_issues[] = [
        'section' => 'Requirements',
        'issue' => 'No course requirements have been added',
        'step' => 3
    ];
}

// Check curriculum
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM course_sections WHERE course_id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$sections_result = $stmt->get_result();
$sections_count = $sections_result->fetch_assoc()['count'];

if ($sections_count == 0) {
    $validation_issues[] = [
        'section' => 'Curriculum',
        'issue' => 'No course sections have been added',
        'step' => 6
    ];
} else {
    // Check if sections have topics
    $stmt = $conn->prepare("
        SELECT s.section_id, s.title, COUNT(t.topic_id) as topic_count 
        FROM course_sections s 
        LEFT JOIN section_topics t ON s.section_id = t.section_id 
        WHERE s.course_id = ? 
        GROUP BY s.section_id
    ");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $sections_topics_result = $stmt->get_result();
    
    while ($section = $sections_topics_result->fetch_assoc()) {
        if ($section['topic_count'] == 0) {
            $validation_issues[] = [
                'section' => 'Curriculum',
                'issue' => "Section '{$section['title']}' has no topics",
                'step' => 6,
                'section_id' => $section['section_id']
            ];
        }
    }
    
    // Check if topics have content
    $stmt = $conn->prepare("
        SELECT t.topic_id, t.title, s.title as section_title, COUNT(c.content_id) as content_count 
        FROM section_topics t 
        JOIN course_sections s ON t.section_id = s.section_id 
        LEFT JOIN topic_content c ON t.topic_id = c.topic_id 
        WHERE s.course_id = ? 
        GROUP BY t.topic_id
    ");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $topics_content_result = $stmt->get_result();
    
    while ($topic = $topics_content_result->fetch_assoc()) {
        if ($topic['content_count'] == 0) {
            $validation_issues[] = [
                'section' => 'Curriculum',
                'issue' => "Topic '{$topic['title']}' in section '{$topic['section_title']}' has no content",
                'step' => 6,
                'topic_id' => $topic['topic_id']
            ];
        }
    }
}

// Check if pricing is set
if ($course['price'] === null) {
    $validation_issues[] = [
        'section' => 'Pricing',
        'issue' => 'Course price is not set',
        'step' => 4
    ];
}

// Store validation results in database
$validation_results = json_encode([
    'issues' => $validation_issues,
    'valid' => empty($validation_issues)
]);

$stmt = $conn->prepare("
    INSERT INTO content_validation_logs (course_id, validation_type, validation_results, validation_date, validated_by) 
    VALUES (?, 'Automatic', ?, NOW(), ?)
");
$stmt->bind_param("isi", $course_id, $validation_results, $user_id);
$stmt->execute();

// Return validation results
echo json_encode([
    'status' => 'success',
    'valid' => empty($validation_issues),
    'issues' => $validation_issues,
    'issue_count' => count($validation_issues)
]);

$stmt->close();
$conn->close();
?>