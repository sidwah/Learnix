<?php
// IMPORTANT: Place this at the very top of your file - before any other code
ob_start(); // Start output buffering to capture any unwanted output

// No whitespace or output before this point!
require '../../backend/session_start.php';
require '../../backend/config.php';

// Function to return clean JSON response
function returnJSON($data) {
    // Discard any previous output that might corrupt our JSON
    ob_clean();
    
    // Set proper JSON headers
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    
    // Return the JSON data - ensure it's properly encoded
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Log to a file instead of outputting to the response
function logToFile($message) {
    error_log("[TAGS DEBUG] " . $message);
}

try {
    // Start debugging
    logToFile("Request received: " . json_encode($_POST));
    
    // Check authentication
    if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
        returnJSON(['success' => false, 'message' => 'Unauthorized access']);
    }
    
    // Validate input
    if (!isset($_POST['course_id']) || !isset($_POST['tags'])) {
        returnJSON(['success' => false, 'message' => 'Missing required parameters']);
    }
    
    $course_id = intval($_POST['course_id']);
    $tags = $_POST['tags'];
    logToFile("Course ID: $course_id, Tags: " . json_encode($tags));
    
    // Ensure tags is an array and sanitize
    if (!is_array($tags)) {
        returnJSON(['success' => false, 'message' => 'Invalid tags format']);
    }
    
    // Get instructor ID
    $user_id = $_SESSION['user_id'];
    logToFile("User ID: $user_id");
    
    $stmt = $conn->prepare("SELECT instructor_id FROM instructors WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        returnJSON(['success' => false, 'message' => 'Instructor not found']);
    }
    
    $instructor = $result->fetch_assoc();
    $instructor_id = $instructor['instructor_id'];
    $stmt->close();
    logToFile("Instructor ID: $instructor_id");
    
    // Verify course belongs to instructor
    $stmt = $conn->prepare("
        SELECT c.course_id
        FROM courses c
        JOIN course_instructors ci ON c.course_id = ci.course_id
        WHERE c.course_id = ? AND ci.instructor_id = ? AND c.deleted_at IS NULL
    ");
    $stmt->bind_param("ii", $course_id, $instructor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        returnJSON(['success' => false, 'message' => 'Course not found or not authorized']);
    }
    $stmt->close();
    
    // Start transaction
    $conn->begin_transaction();
    
    // Delete existing tag mappings
    $stmt = $conn->prepare("DELETE FROM course_tag_mapping WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $stmt->close();
    logToFile("Deleted existing tags");
    
    // Insert new tag mappings
    if (!empty($tags)) {
        $insertCount = 0;
        $stmt = $conn->prepare("INSERT INTO course_tag_mapping (course_id, tag_id) VALUES (?, ?)");
        foreach ($tags as $tag_id) {
            $tag_id = intval($tag_id);
            if ($tag_id > 0) {
                $stmt->bind_param("ii", $course_id, $tag_id);
                $stmt->execute();
                $insertCount++;
            }
        }
        $stmt->close();
        logToFile("Inserted $insertCount new tags");
    }
    
    // Update course timestamp
    $stmt = $conn->prepare("UPDATE courses SET updated_at = NOW() WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $stmt->close();
    
    // Commit the transaction
    $conn->commit();
    logToFile("Transaction committed successfully");
    
    // Return success with simple structure
    returnJSON(['success' => true, 'message' => 'Tags saved successfully']);
    
} catch (Exception $e) {
    // Rollback on error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollback();
    }
    logToFile("ERROR: " . $e->getMessage());
    returnJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>