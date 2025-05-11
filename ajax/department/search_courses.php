<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../backend/config.php';
require_once '../../includes/department/courses_functions.php'; // Add this line
require_once '../../includes/department/course_card.php';
require_once '../../includes/department/course_table_row.php';

// Check if user is logged in and has department head role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get search parameters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$category = $_GET['category'] ?? '';
$level = $_GET['level'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 100) : 20;
$view = $_GET['view'] ?? 'cards';

// Create a new set of query parameters that will be passed to the backend
$query_params = array_merge($_GET, [
    'search' => $search,
    'status' => $status,
    'category' => $category,
    'level' => $level,
    'sort' => $sort,
    'page' => $page,
    'limit' => $limit
]);

// Save current $_GET
$original_get = $_GET;
$_GET = $query_params;

// Execute backend get_courses.php and capture the output
ob_start();
include '../../backend/department/get_courses.php';
$backend_output = ob_get_clean();

// Restore original $_GET
$_GET = $original_get;

// Parse the backend response
$backend_data = json_decode($backend_output, true);

if (!$backend_data || !$backend_data['success']) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to load courses']);
    exit;
}

$courses = $backend_data['courses'] ?? [];

// Generate HTML based on view type
$html = '';
if ($view === 'cards') {
    foreach ($courses as $course) {
        $html .= renderCourseCard($course);
    }
} else {
    foreach ($courses as $course) {
        $html .= renderCourseTableRow($course);
    }
}

// Highlight search terms in HTML if search is active
if (!empty($search)) {
    $html = preg_replace_callback('/(' . preg_quote($search, '/') . ')/i', function($matches) {
        return '<span class="highlight">' . $matches[0] . '</span>';
    }, $html);
}

// Return complete response with all pagination data
echo json_encode([
    'success' => true,
    'html' => $html,
    'count' => count($courses),
    'total_count' => $backend_data['total_count'] ?? 0,
    'current_page' => $backend_data['current_page'] ?? 1,
    'total_pages' => $backend_data['total_pages'] ?? 1,
    'per_page' => $backend_data['per_page'] ?? 20,
    'showing_start' => $backend_data['showing_start'] ?? 1,
    'showing_end' => $backend_data['showing_end'] ?? count($courses),
    'message' => count($courses) === 0 ? 'No courses found matching your criteria.' : null
]);
?>