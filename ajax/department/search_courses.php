<?php
// ajax/department/search_courses.php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../backend/config.php';
require_once '../../includes/department/courses_functions.php';
require_once '../../includes/department/course_card.php';
require_once '../../includes/department/course_table_row.php';

// Check if user is logged in and has department head role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get department ID for the user
$dept_query = "SELECT ds.department_id 
               FROM department_staff ds 
               WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' AND ds.deleted_at IS NULL
               LIMIT 1";
$dept_stmt = $conn->prepare($dept_query);
$dept_stmt->bind_param("i", $_SESSION['user_id']);
$dept_stmt->execute();
$dept_result = $dept_stmt->get_result();

if ($dept_result->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No active department found for user']);
    exit;
}

$department = $dept_result->fetch_assoc();
$department_id = $department['department_id'];

// Get search parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$level = isset($_GET['level']) ? trim($_GET['level']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? min(max(1, (int)$_GET['limit']), 100) : 20;
$view = isset($_GET['view']) ? ($_GET['view'] === 'cards' ? 'cards' : 'table') : 'table';

// Calculate offset
$offset = ($page - 1) * $limit;

// Build filters for getCoursesByDepartment
$filters = [
    'search' => $search,
    'status' => $status,
    'category' => $category,
    'level' => $level,
    'sort' => $sort,
    'limit' => $limit,
    'offset' => $offset
];

// Get total count for pagination
$total_count_query = "SELECT COUNT(*) as total 
                     FROM courses c
                     JOIN subcategories sub ON c.subcategory_id = sub.subcategory_id
                     JOIN categories cat ON sub.category_id = cat.category_id
                     WHERE c.department_id = ? AND c.deleted_at IS NULL";
$params = [$department_id];
$param_types = "i";

if (!empty($filters['search'])) {
    $total_count_query .= " AND (c.title LIKE ? OR c.short_description LIKE ?)";
    $params[] = "%{$filters['search']}%";
    $params[] = "%{$filters['search']}%";
    $param_types .= "ss";
}

if (!empty($filters['status'])) {
    if ($filters['status'] === 'pending') {
        $total_count_query .= " AND c.approval_status IN ('pending', 'revisions_requested')";
    } else {
        $total_count_query .= " AND c.status = ?";
        $params[] = $filters['status'];
        $param_types .= "s";
    }
}

if (!empty($filters['category'])) {
    $total_count_query .= " AND cat.name = ?";
    $params[] = $filters['category'];
    $param_types .= "s";
}

if (!empty($filters['level'])) {
    $total_count_query .= " AND c.course_level = ?";
    $params[] = $filters['level'];
    $param_types .= "s";
}

$total_stmt = $conn->prepare($total_count_query);
if (!$total_stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}

$total_stmt->bind_param($param_types, ...$params);
$total_stmt->execute();
$total_count = $total_stmt->get_result()->fetch_assoc()['total'];

// Calculate pagination data
$total_pages = max(1, ceil($total_count / $limit));
$current_page = min($page, $total_pages); // Ensure current_page doesn't exceed total_pages
$showing_start = $total_count === 0 ? 0 : ($offset + 1);
$showing_end = min($offset + $limit, $total_count);

// Get courses
$courses = getCoursesByDepartment($department_id, $filters);

// Highlight search terms in course data
if (!empty($search)) {
    $search_pattern = '/(' . preg_quote($search, '/') . ')/i';
    foreach ($courses as &$course) {
        // Highlight specific fields
        if (isset($course['title'])) {
            $course['title'] = preg_replace_callback(
                $search_pattern,
                function ($matches) {
                    return '<span class="highlight">' . htmlspecialchars($matches[0]) . '</span>';
                },
                htmlspecialchars($course['title'])
            );
        }
        if (isset($course['category_name'])) {
            $course['category_name'] = preg_replace_callback(
                $search_pattern,
                function ($matches) {
                    return '<span class="highlight">' . htmlspecialchars($matches[0]) . '</span>';
                },
                htmlspecialchars($course['category_name'])
            );
        }
        if (isset($course['course_level'])) {
            $course['course_level'] = preg_replace_callback(
                $search_pattern,
                function ($matches) {
                    return '<span class="highlight">' . htmlspecialchars($matches[0]) . '</span>';
                },
                htmlspecialchars($course['course_level'])
            );
        }
    }
    unset($course); // Unset reference
}

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

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'html' => $html,
    'count' => count($courses),
    'total_count' => $total_count,
    'current_page' => $current_page,
    'total_pages' => $total_pages,
    'per_page' => $limit,
    'showing_start' => $showing_start,
    'showing_end' => $showing_end,
    'message' => count($courses) === 0 ? 'No courses found matching your criteria.' : null
]);

// Close database connection
$conn->close();
exit;
?>