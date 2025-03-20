<?php
/**
 * File: get_course_details.php
 * Description: Fetches comprehensive course details for editing
 * Location: ../backend/courses/
 */

// Include database connection
require_once '../config.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

// Check if course_id is provided
if (!isset($_GET['course_id']) || empty($_GET['course_id'])) {
    echo json_encode(['error' => 'Course ID is required']);
    exit;
}

// Sanitize input
$course_id = intval($_GET['course_id']);

try {
    // Main course details
    $sql = "SELECT c.*, 
                   cat.category_id 
            FROM courses c
            LEFT JOIN subcategories s ON c.subcategory_id = s.subcategory_id
            LEFT JOIN categories cat ON s.category_id = cat.category_id
            WHERE c.course_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['error' => 'Course not found']);
        exit;
    }
    
    $courseData = $result->fetch_assoc();
    $stmt->close();
    
    // Format dates for display
    $courseData['created_at_formatted'] = date('M d, Y h:i A', strtotime($courseData['created_at']));
    $courseData['updated_at_formatted'] = date('M d, Y h:i A', strtotime($courseData['updated_at']));
    
    // Get course tags
    $sql = "SELECT tag_id FROM course_tag_mapping WHERE course_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $tags = [];
    while ($row = $result->fetch_assoc()) {
        $tags[] = $row['tag_id'];
    }
    $stmt->close();
    
    // Get course sections
    $sql = "SELECT section_id, title, position FROM course_sections WHERE course_id = ? ORDER BY position ASC, section_id ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $sections = [];
    while ($row = $result->fetch_assoc()) {
        // Count topics per section
        $topicSql = "SELECT COUNT(*) as topic_count FROM section_topics WHERE section_id = ?";
        $topicStmt = $conn->prepare($topicSql);
        $topicStmt->bind_param("i", $row['section_id']);
        $topicStmt->execute();
        $topicResult = $topicStmt->get_result();
        $topicCount = $topicResult->fetch_assoc()['topic_count'];
        $topicStmt->close();
        
        $sections[] = [
            'section_id' => $row['section_id'],
            'title' => $row['title'],
            'position' => $row['position'],
            'topic_count' => $topicCount
        ];
    }
    $stmt->close();
    
    // Get requirements
    $sql = "SELECT requirement_id, requirement_text FROM course_requirements WHERE course_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $requirements = [];
    while ($row = $result->fetch_assoc()) {
        $requirements[] = [
            'requirement_id' => $row['requirement_id'],
            'requirement_text' => $row['requirement_text']
        ];
    }
    $stmt->close();
    
    // Get learning outcomes
    $sql = "SELECT outcome_id, outcome_text FROM course_learning_outcomes WHERE course_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $outcomes = [];
    while ($row = $result->fetch_assoc()) {
        $outcomes[] = [
            'outcome_id' => $row['outcome_id'],
            'outcome_text' => $row['outcome_text']
        ];
    }
    $stmt->close();
    
    // Combine all data
    $responseData = $courseData;
    $responseData['tags'] = $tags;
    $responseData['sections'] = $sections;
    $responseData['requirements'] = $requirements;
    $responseData['outcomes'] = $outcomes;
    
    // Return course details as JSON
    echo json_encode($responseData);
    
} catch (Exception $e) {
    // Log error
    error_log('Error fetching course details: ' . $e->getMessage());
    
    // Return error message
    echo json_encode(['error' => 'Failed to fetch course details: ' . $e->getMessage()]);
} finally {
    // Close connection if it exists
    if (isset($conn)) {
        $conn->close();
    }
}
?>