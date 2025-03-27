<?php
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

// Validate title
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Section title cannot be empty']);
    exit;
}

// Verify that the section belongs to a course owned by the current instructor
$stmt = $conn->prepare("
    SELECT cs.course_id, c.instructor_id 
    FROM course_sections cs
    JOIN courses c ON cs.course_id = c.course_id
    WHERE cs.section_id = ?
");
$stmt->bind_param("i", $section_id);
$stmt->execute();
$result = $stmt->get_result();
$section_data = $result->fetch_assoc();
$stmt->close();

if (!$section_data || $section_data['instructor_id'] != $_SESSION['instructor_id']) {
    echo json_encode(['success' => false, 'message' => 'Section not found or not authorized']);
    exit;
}

// Update section
$stmt = $conn->prepare("UPDATE course_sections SET title = ? WHERE section_id = ?");
$stmt->bind_param("si", $title, $section_id);
$success = $stmt->execute();
$stmt->close();

if ($success) {
    // Update course last modified timestamp
    $stmt = $conn->prepare("UPDATE courses SET updated_at = NOW() WHERE course_id = ?");
    $stmt->bind_param("i", $section_data['course_id']);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Section updated successfully'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}
?>