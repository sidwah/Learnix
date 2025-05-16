<?php
//ajax/curriculum/add_section.php
require '../../backend/session_start.php';
require '../../backend/config.php';

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Validate required input
if (!isset($_POST['course_id']) || !isset($_POST['title'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$course_id = intval($_POST['course_id']);
$title = trim($_POST['title']);
$instructor_id = $_SESSION['instructor_id'];
$user_id = $_SESSION['user_id']; // Assuming user_id is stored in session

// Validate title
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Section title cannot be empty']);
    exit;
}

// Verify that the course is associated with the current instructor using course_instructors table
$stmt = $conn->prepare("
    SELECT ci.course_id 
    FROM course_instructors ci 
    WHERE ci.course_id = ? 
    AND ci.instructor_id = ? 
    AND ci.deleted_at IS NULL
");
$stmt->bind_param("ii", $course_id, $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
$authorized = $result->num_rows > 0;
$stmt->close();

if (!$authorized) {
    echo json_encode(['success' => false, 'message' => 'Course not found or not authorized']);
    exit;
}

// Get the maximum position to add this section at the end
$stmt = $conn->prepare("SELECT MAX(position) as max_position FROM course_sections WHERE course_id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
$max_position = $result->fetch_assoc()['max_position'] ?? 0;
$new_position = $max_position + 1;
$stmt->close();

// Insert new section with created_by field
$stmt = $conn->prepare("INSERT INTO course_sections (course_id, title, position, created_at, created_by) VALUES (?, ?, ?, NOW(), ?)");
$stmt->bind_param("isii", $course_id, $title, $new_position, $user_id);
$success = $stmt->execute();
$section_id = $stmt->insert_id;
$stmt->close();

if ($success) {
    // Update course last modified timestamp
    $stmt = $conn->prepare("UPDATE courses SET updated_at = NOW() WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Section added successfully',
        'section_id' => $section_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}
?>