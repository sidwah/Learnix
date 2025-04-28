<?php
require_once '../../backend/config.php';
require '../../backend/session_start.php';

// Check if user is signed in as instructor
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$instructor_id = $_SESSION['instructor_id'];

try {
    // Connect to database
    $conn = new mysqli('localhost', 'root', 'root', 'learnix_db');
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Get courses for this instructor
    $stmt = $conn->prepare("SELECT course_id, title FROM courses WHERE instructor_id = ?");
    $stmt->bind_param("i", $instructor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
    
    echo json_encode($courses);
    
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'Failed to fetch courses']);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>