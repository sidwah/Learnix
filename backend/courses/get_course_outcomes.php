<?php
/**
 * File: get_course_outcomes.php
 * Description: Fetches learning outcomes for a specific course
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
    // Prepare SQL to fetch learning outcomes
    $sql = "SELECT outcome_id, course_id, outcome_text 
            FROM course_learning_outcomes 
            WHERE course_id = ?
            ORDER BY outcome_id ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $outcomes = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $outcomes[] = [
                'outcome_id' => $row['outcome_id'],
                'course_id' => $row['course_id'],
                'outcome_text' => $row['outcome_text']
            ];
        }
    }
    
    // Return outcomes as JSON
    echo json_encode($outcomes);
    
} catch (Exception $e) {
    // Log error
    error_log('Error fetching course outcomes: ' . $e->getMessage());
    
    // Return error message
    echo json_encode(['error' => 'Failed to fetch course outcomes']);
} finally {
    // Close statement and connection if they exist
    if (isset($stmt)) {
        $stmt->close();
    }
}
?>