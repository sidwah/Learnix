<?php
// ajax/department/load_courses.php
session_start();
require_once '../../backend/config.php';
require_once '../../includes/department/courses_functions.php';

// Check if user is logged in and has department head role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    // Call backend to get courses
    $query_params = http_build_query($_GET);
    $response = include '../../backend/department/get_courses.php';
    
    // Return response as is
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to load courses: ' . $e->getMessage()]);
}
?>