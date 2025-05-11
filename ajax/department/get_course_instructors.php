<?php
// ajax/department/get_course_instructors.php
session_start();
require_once '../../backend/config.php';

// Check if user is logged in and has department head role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$course_id = $_GET['course_id'] ?? 0;
$instructor_action = $_GET['instructor_action'] ?? 'get_current';

// Forward to backend manage_instructors.php
$_GET['action'] = $instructor_action === 'get_current' ? 'get_course_instructors' : 'get_available_instructors';
$_GET['course_id'] = $course_id;

ob_start();
include '../../backend/department/manage_instructors.php';
$output = ob_get_clean();

// Output the result
echo $output;
?>