<?php
//ajax/content/delete_resource.php
require '../../backend/session_start.php';
require '../../backend/config.php';

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Validate required input
if (!isset($_POST['topic_id']) || !isset($_POST['resource_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$topic_id = intval($_POST['topic_id']);
$resource_id = intval($_POST['resource_id']);
$instructor_id = $_SESSION['instructor_id'];

// Verify that the topic belongs to a section of a course assigned to the current instructor
$stmt = $conn->prepare("
    SELECT 
        st.section_id, 
        cs.course_id
    FROM 
        section_topics st
    JOIN 
        course_sections cs ON st.section_id = cs.section_id
    JOIN 
        course_instructors ci ON cs.course_id = ci.course_id
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

// Get resource file path for deletion
$stmt = $conn->prepare("SELECT resource_path FROM topic_resources WHERE resource_id = ? AND topic_id = ?");
$stmt->bind_param("ii", $resource_id, $topic_id);
$stmt->execute();
$result = $stmt->get_result();
$resource = $result->fetch_assoc();
$stmt->close();

if (!$resource) {
    echo json_encode(['success' => false, 'message' => 'Resource not found or not authorized']);
    exit;
}

// Delete the resource from database
$stmt = $conn->prepare("DELETE FROM topic_resources WHERE resource_id = ? AND topic_id = ?");
$stmt->bind_param("ii", $resource_id, $topic_id);
$success = $stmt->execute();
$stmt->close();

if ($success) {
    // Delete the file from storage
    $file_path = '../../uploads/resources/' . $resource['resource_path'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
    
    // Update course last modified timestamp
    $stmt = $conn->prepare("UPDATE courses SET updated_at = NOW() WHERE course_id = ?");
    $stmt->bind_param("i", $topic_data['course_id']);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Resource deleted successfully'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}
?>