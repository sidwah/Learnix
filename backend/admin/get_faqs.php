<?php
require_once '../config.php';
header('Content-Type: application/json');

// Authentication check
require_once '../auth/admin/admin-auth-check.php';

// Initialize response array
$response = array(
    'success' => false,
    'message' => '',
    'faqs' => array(),
    'stats' => array(
        'total' => 0,
        'active' => 0,
        'student' => 0,
        'instructor' => 0,
        'all_users' => 0
    )
);

// Process filters if provided
$where_conditions = array("deleted_at IS NULL");
$params = array();
$param_types = "";

// Status filter
if (isset($_GET['status']) && in_array($_GET['status'], ['active', 'inactive'])) {
    $where_conditions[] = "status = ?";
    $params[] = $_GET['status'];
    $param_types .= "s";
}

// Visibility filter
if (isset($_GET['visibility'])) {
    $visibility = $_GET['visibility'];
    if ($visibility === 'all') {
        $where_conditions[] = "role_visibility = 'all'";
    } elseif (in_array($visibility, ['student', 'instructor', 'department_head', 'admin'])) {
        $where_conditions[] = "(role_visibility = 'all' OR role_visibility LIKE ?)";
        $params[] = "%$visibility%";
        $param_types .= "s";
    }
}

// Search term
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = $_GET['search'];
    $where_conditions[] = "(question LIKE ? OR answer LIKE ? OR category LIKE ?)";
    $params[] = "%$search_term%";
    $params[] = "%$search_term%";
    $params[] = "%$search_term%";
    $param_types .= "sss";
}

// Build where clause
$where_clause = implode(" AND ", $where_conditions);

// Get FAQs with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = isset($_GET['per_page']) ? max(5, intval($_GET['per_page'])) : 10;
$offset = ($page - 1) * $per_page;

// Prepare the count query for total results
$count_sql = "SELECT COUNT(*) as total FROM faqs WHERE $where_clause";
$count_stmt = $conn->prepare($count_sql);

if (!empty($param_types)) {
    $count_stmt->bind_param($param_types, ...$params);
}

$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$total_results = $count_row['total'];
$count_stmt->close();

// Prepare main query
$sql = "SELECT id, category, question, answer, role_visibility, status, created_at, last_updated 
        FROM faqs 
        WHERE $where_clause 
        ORDER BY id DESC 
        LIMIT ?, ?";

// Add pagination parameters
$params[] = $offset;
$params[] = $per_page;
$param_types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    $faqs = array();
    while ($row = $result->fetch_assoc()) {
        $faqs[] = $row;
    }
    $response['success'] = true;
    $response['faqs'] = $faqs;
    $response['pagination'] = array(
        'total' => $total_results,
        'per_page' => $per_page,
        'current_page' => $page,
        'total_pages' => ceil($total_results / $per_page)
    );
} else {
    $response['message'] = 'Error fetching FAQs: ' . $stmt->error;
}

$stmt->close();

// Get statistics
$stats_sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN role_visibility = 'all' OR role_visibility LIKE '%student%' THEN 1 ELSE 0 END) as student,
                SUM(CASE WHEN role_visibility = 'all' OR role_visibility LIKE '%instructor%' THEN 1 ELSE 0 END) as instructor,
                SUM(CASE WHEN role_visibility = 'all' THEN 1 ELSE 0 END) as all_users
              FROM faqs 
              WHERE deleted_at IS NULL";

$stats_result = $conn->query($stats_sql);
if ($stats_result && $stats_row = $stats_result->fetch_assoc()) {
    $response['stats'] = array(
        'total' => intval($stats_row['total']),
        'active' => intval($stats_row['active']),
        'student' => intval($stats_row['student']),
        'instructor' => intval($stats_row['instructor']),
        'all_users' => intval($stats_row['all_users'])
    );
}

// Send the response
echo json_encode($response);