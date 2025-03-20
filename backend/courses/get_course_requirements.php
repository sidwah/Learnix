<?php
/**
 * File: get_course_requirements.php
 * Description: Fetches course requirements for a specific course
 * Location: ../backend/courses/
 */

// Include database connection
require_once '../../config.php';

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
    // Prepare SQL to fetch requirements
    $sql = "SELECT requirement_id, course_id, requirement_text 
            FROM course_requirements 
            WHERE course_id = ?
            ORDER BY requirement_id ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $requirements = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $requirements[] = [
                'requirement_id' => $row['requirement_id'],
                'course_id' => $row['course_id'],
                'requirement_text' => $row['requirement_text']
            ];
        }
    }
    
    // Return requirements as JSON
    echo json_encode($requirements);
    
} catch (Exception $e) {
    // Log error
    error_log('Error fetching course requirements: ' . $e->getMessage());
    
    // Return error message
    echo json_encode(['error' => 'Failed to fetch course requirements']);
} finally {
    // Close statement and connection if they exist
    if (isset($stmt)) {
        $stmt->close();
    }
}
?>