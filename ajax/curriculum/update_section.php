<?php
//ajax/curriculum/update_section.php
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
$instructor_id = $_SESSION['instructor_id'];
$user_id = $_SESSION['user_id'];

// Validate title
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Section title cannot be empty']);
    exit;
}
// Verify that the section belongs to a course assigned to the current instructor
$stmt = $conn->prepare("
    SELECT
        cs.section_id,
        cs.course_id,
        ci.instructor_id
    FROM
        course_sections cs
    JOIN
        courses c ON cs.course_id = c.course_id
    JOIN
        course_instructors ci ON c.course_id = ci.course_id
    WHERE
        cs.section_id = ? AND
        ci.instructor_id = ? AND
        ci.deleted_at IS NULL
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
// Update section with user tracking
$stmt = $conn->prepare("UPDATE course_sections SET title = ?, updated_by = ?, updated_at = NOW() WHERE section_id = ?");
$stmt->bind_param("sii", $title, $user_id, $section_id);
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