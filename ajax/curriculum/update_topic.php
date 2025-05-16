<?php
//ajax/curriculum/update_topic.php
require '../../backend/session_start.php';
require '../../backend/config.php';
// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}
// Validate required input
if (!isset($_POST['topic_id']) || !isset($_POST['title'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}
$topic_id = intval($_POST['topic_id']);
$title = trim($_POST['title']);
$is_previewable = isset($_POST['is_previewable']) ? intval($_POST['is_previewable']) : 0;
$instructor_id = $_SESSION['instructor_id'];
$user_id = $_SESSION['user_id'];

// Validate title
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Topic title cannot be empty']);
    exit;
}
// Verify that the topic belongs to a section of a course assigned to the current instructor
$stmt = $conn->prepare("
    SELECT
        st.topic_id,
        st.section_id,
        cs.course_id,
        ci.instructor_id
    FROM
        section_topics st
    JOIN
        course_sections cs ON st.section_id = cs.section_id
    JOIN
        courses c ON cs.course_id = c.course_id
    JOIN
        course_instructors ci ON c.course_id = ci.course_id
    WHERE
        st.topic_id = ? AND
        ci.instructor_id = ? AND
        ci.deleted_at IS NULL
");
$stmt->bind_param("ii", $topic_id, $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
$topic_data = $result->fetch_assoc();
$stmt->close();
if (!$topic_data) {
    echo json_encode(['success' => false, 'message' => 'Topic not found or not authorized']);
    exit;
}
// Update topic with user tracking
$stmt = $conn->prepare("UPDATE section_topics SET title = ?, is_previewable = ?, updated_at = NOW(), updated_by = ? WHERE topic_id = ?");
$stmt->bind_param("siii", $title, $is_previewable, $user_id, $topic_id);
$success = $stmt->execute();
$stmt->close();
if ($success) {
    // Update course last modified timestamp
    $stmt = $conn->prepare("UPDATE courses SET updated_at = NOW() WHERE course_id = ?");
    $stmt->bind_param("i", $topic_data['course_id']);
    $stmt->execute();
    $stmt->close();
    echo json_encode([
        'success' => true,
        'message' => 'Topic updated successfully'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}
?>