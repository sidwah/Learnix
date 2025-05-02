<?php
// File: ajax/instructors/course_performance_filter.php
require '../../backend/session_start.php';
require_once '../../backend/config.php';

// Check if user is authenticated as instructor
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Process filter request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get filter parameters
    $course_id = isset($_POST['course_id']) ? $_POST['course_id'] : 'all';
    $time_frame = isset($_POST['time_frame']) ? $_POST['time_frame'] : 'monthly';
    
    // Get current sorting and pagination parameters if they exist
    $sort = isset($_GET['sort']) ? $_GET['sort'] : '';
    $order = isset($_GET['order']) ? $_GET['order'] : '';
    
    // Build query string for redirect
    $query = "?";
    if ($course_id !== 'all') {
        $query .= "course_id=" . $course_id . "&";
    }
    if ($time_frame !== 'monthly') {
        $query .= "time_frame=" . $time_frame . "&";
    }
    if (!empty($sort)) {
        $query .= "sort=" . $sort . "&";
    }
    if (!empty($order)) {
        $query .= "order=" . $order . "&";
    }
    
    // Remove trailing '&' if it exists
    $query = rtrim($query, "&");
    
    // If query is just "?", return empty string
    if ($query === "?") {
        $query = "";
    }
    
    // Return success with redirect query string
    echo json_encode([
        'success' => true,
        'redirect_query' => $query
    ]);
    exit;
}

// Handle invalid request method
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Invalid request method']);
?>