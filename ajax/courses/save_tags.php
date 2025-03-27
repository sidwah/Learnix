<?php
require '../../backend/session_start.php';
require '../../backend/config.php';

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Validate required input
if (!isset($_POST['course_id']) || !isset($_POST['tags'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$course_id = intval($_POST['course_id']);
$tags = $_POST['tags'];

// Ensure tags is an array
if (!is_array($tags)) {
    echo json_encode(['success' => false, 'message' => 'Invalid tags format']);
    exit;
}

// Verify that the course belongs to the current instructor
$stmt = $conn->prepare("SELECT instructor_id FROM courses WHERE course_id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();
$stmt->close();

if (!$course || $course['instructor_id'] != $_SESSION['instructor_id']) {
    echo json_encode(['success' => false, 'message' => 'Course not found or not authorized']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Delete existing tag mappings
    $stmt = $conn->prepare("DELETE FROM course_tag_mapping WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $stmt->close();
    
    // Insert new tag mappings
    if (!empty($tags)) {
        $stmt = $conn->prepare("INSERT INTO course_tag_mapping (course_id, tag_id) VALUES (?, ?)");
        foreach ($tags as $tag_id) {
            $tag_id = intval($tag_id);
            if ($tag_id > 0) {
                $stmt->bind_param("ii", $course_id, $tag_id);
                $stmt->execute();
            }
        }
        $stmt->close();
    }
    
    // Update course last modified timestamp
    $stmt = $conn->prepare("UPDATE courses SET updated_at = NOW() WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Course tags saved successfully']);
    
} catch (Exception $e) {
    // Rollback in case of error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>