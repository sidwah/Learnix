<?php
// ajax/department/filter_courses.php
session_start();
require_once '../../backend/config.php';

// Check if user is logged in and has department head role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get filter parameters
$filters = [
    'status' => $_GET['status'] ?? '',
    'category' => $_GET['category'] ?? '',
    'level' => $_GET['level'] ?? '',
    'search' => $_GET['search'] ?? '',
    'sort' => $_GET['sort'] ?? 'newest',
    'view' => $_GET['view'] ?? 'cards'
];

// Forward to search_courses.php with filters
$query_string = http_build_query($filters);
header("Location: search_courses.php?{$query_string}");
exit;
?>