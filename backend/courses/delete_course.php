<?php
/**
 * File: delete_course.php
 * Description: Handles course deletion from the course management page
 * Location: ../backend/courses/
 */

// Include database connection
require_once '../config.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON data
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Check if course_id is provided
if (!isset($data['course_id']) || empty($data['course_id'])) {
    echo json_encode(['success' => false, 'message' => 'Course ID is required']);
    exit;
}

// Check if instructor is logged in
session_start();
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

// Get instructor ID
$user_id = $_SESSION['user_id'];
$instructor_query = "SELECT instructor_id FROM instructors WHERE user_id = ?";
$stmt = $conn->prepare($instructor_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$instructor_result = $stmt->get_result();

if ($instructor_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Instructor not found']);
    exit;
}

$instructor_row = $instructor_result->fetch_assoc();
$instructor_id = $instructor_row['instructor_id'];
$stmt->close();

// Sanitize input
$course_id = intval($data['course_id']);

// Check if the course belongs to the instructor
$ownership_query = "SELECT title, thumbnail FROM courses WHERE course_id = ? AND instructor_id = ?";
$stmt = $conn->prepare($ownership_query);
$stmt->bind_param("ii", $course_id, $instructor_id);
$stmt->execute();
$ownership_result = $stmt->get_result();

if ($ownership_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this course']);
    exit;
}

// Store course info for later use
$course_info = $ownership_result->fetch_assoc();
$course_title = $course_info['title'];
$thumbnail = $course_info['thumbnail'];

$stmt->close();

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Delete dependent records first
    
    // Delete course requirements
    $sql = "DELETE FROM course_requirements WHERE course_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $stmt->close();
    
    // Delete learning outcomes
    $sql = "DELETE FROM course_learning_outcomes WHERE course_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $stmt->close();
    
    // Delete tag mappings
    $sql = "DELETE FROM course_tag_mapping WHERE course_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $stmt->close();
    
    // Get all section IDs
    $sql = "SELECT section_id FROM course_sections WHERE course_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $sections_result = $stmt->get_result();
    $section_ids = [];
    
    while ($section = $sections_result->fetch_assoc()) {
        $section_ids[] = $section['section_id'];
    }
    $stmt->close();
    
    // Delete sections content and topics
    if (!empty($section_ids)) {
        foreach ($section_ids as $section_id) {
            // Get topic IDs
            $sql = "SELECT topic_id FROM section_topics WHERE section_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $section_id);
            $stmt->execute();
            $topic_result = $stmt->get_result();
            $topic_ids = [];
            
            while ($topic = $topic_result->fetch_assoc()) {
                $topic_ids[] = $topic['topic_id'];
            }
            $stmt->close();
            
            // Delete topic content and resources
            if (!empty($topic_ids)) {
                foreach ($topic_ids as $topic_id) {
                    // Delete topic content
                    $sql = "DELETE FROM topic_content WHERE topic_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $topic_id);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Delete topic resources
                    $sql = "DELETE FROM topic_resources WHERE topic_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $topic_id);
                    $stmt->execute();
                    $stmt->close();
                }
            }
            
            // Delete topics
            $sql = "DELETE FROM section_topics WHERE section_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $section_id);
            $stmt->execute();
            $stmt->close();
            
            // Delete section quizzes
            $sql = "DELETE FROM section_quizzes WHERE section_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $section_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    // Delete sections
    $sql = "DELETE FROM course_sections WHERE course_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $stmt->close();
    
    // Finally, delete the course
    $sql = "DELETE FROM courses WHERE course_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        throw new Exception("Failed to delete the course");
    }
    $stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    // Delete thumbnail file if it exists
    if (!empty($thumbnail)) {
        $thumbnail_paths = [
            "../../uploads/thumbnails/{$thumbnail}",
            "../../uploads/{$thumbnail}"
        ];
        
        foreach ($thumbnail_paths as $path) {
            if (file_exists($path)) {
                unlink($path);
                break;
            }
        }
    }
    
    // Return success response
    echo json_encode([
        'success' => true, 
        'message' => "Course '{$course_title}' has been successfully deleted",
        'course_id' => $course_id
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Log error
    error_log('Error deleting course: ' . $e->getMessage());
    
    // Return error message
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to delete course: ' . $e->getMessage()
    ]);
} finally {
    // Close connection
    $conn->close();
}
?>