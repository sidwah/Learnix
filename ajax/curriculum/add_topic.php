<?php
//ajax/curriculum/add_topic.php
require '../../backend/session_start.php';
require '../../backend/config.php';

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Validate required input
if (!isset($_POST['section_id']) || !isset($_POST['title'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$section_id = intval($_POST['section_id']);
$title = trim($_POST['title']);
$is_previewable = isset($_POST['is_previewable']) ? intval($_POST['is_previewable']) : 0;
$instructor_id = $_SESSION['instructor_id'];
$user_id = $_SESSION['user_id'];

// Validate title
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Topic title cannot be empty']);
    exit;
}

// Verify that the section belongs to a course assigned to the current instructor
$stmt = $conn->prepare("
    SELECT cs.course_id 
    FROM course_sections cs
    JOIN course_instructors ci ON cs.course_id = ci.course_id
    WHERE cs.section_id = ?
    AND ci.instructor_id = ? 
    AND ci.deleted_at IS NULL
");
$stmt->bind_param("ii", $section_id, $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
$section_data = $result->fetch_assoc();
$stmt->close();

if (!$section_data) {
    echo json_encode(['success' => false, 'message' => 'Section not found or not authorized']);
    exit;
}

// Get the maximum position to add this topic at the end
$stmt = $conn->prepare("SELECT MAX(position) as max_position FROM section_topics WHERE section_id = ?");
$stmt->bind_param("i", $section_id);
$stmt->execute();
$result = $stmt->get_result();
$max_position = $result->fetch_assoc()['max_position'] ?? 0;
$new_position = $max_position + 1;
$stmt->close();

// Check if the created_by column exists in the section_topics table
$column_check = $conn->query("SHOW COLUMNS FROM section_topics LIKE 'created_by'");
$created_by_exists = $column_check->num_rows > 0;

// Insert new topic (with created_by if the column exists)
if ($created_by_exists) {
    $stmt = $conn->prepare("
        INSERT INTO section_topics (section_id, title, position, created_at, is_previewable, created_by) 
        VALUES (?, ?, ?, NOW(), ?, ?)
    ");
    $stmt->bind_param("isiii", $section_id, $title, $new_position, $is_previewable, $user_id);
} else {
    $stmt = $conn->prepare("
        INSERT INTO section_topics (section_id, title, position, created_at, is_previewable) 
        VALUES (?, ?, ?, NOW(), ?)
    ");
    $stmt->bind_param("isii", $section_id, $title, $new_position, $is_previewable);
}

$success = $stmt->execute();
$topic_id = $stmt->insert_id;
$stmt->close();

if ($success) {
    // Update course last modified timestamp
    $stmt = $conn->prepare("UPDATE courses SET updated_at = NOW() WHERE course_id = ?");
    $stmt->bind_param("i", $section_data['course_id']);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Topic added successfully',
        'topic_id' => $topic_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}
?>