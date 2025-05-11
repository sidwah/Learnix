 
<?php
// backend/department/get_courses.php
// session_start();
require_once '../../backend/config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in and has department head role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get user's department
$dept_query = "SELECT ds.department_id 
               FROM department_staff ds 
               WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' AND ds.deleted_at IS NULL
               LIMIT 1";
$dept_stmt = $conn->prepare($dept_query);
$dept_stmt->bind_param("i", $_SESSION['user_id']);
$dept_stmt->execute();
$dept_result = $dept_stmt->get_result();

if ($dept_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Department not found']);
    exit;
}

$department = $dept_result->fetch_assoc();
$department_id = $department['department_id'];

// Get filter parameters
$status = $_GET['status'] ?? '';
$category = $_GET['category'] ?? '';
$level = $_GET['level'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
$offset = ($page - 1) * $limit;

// Build query
$where_conditions = ["c.department_id = ?"];
$params = [$department_id];
$param_types = "i";

// Add search condition
if (!empty($search)) {
    $where_conditions[] = "c.title LIKE ?";
    $params[] = "%$search%";
    $param_types .= "s";
}

// Add status condition
if (!empty($status)) {
    if ($status === 'pending') {
        $where_conditions[] = "c.approval_status IN ('pending', 'revisions_requested')";
    } else {
        $where_conditions[] = "c.status = ?";
        $params[] = $status;
        $param_types .= "s";
    }
}

// Add category condition
if (!empty($category)) {
    $where_conditions[] = "cat.name = ?";
    $params[] = $category;
    $param_types .= "s";
}

// Add level condition
if (!empty($level)) {
    $where_conditions[] = "c.course_level = ?";
    $params[] = $level;
    $param_types .= "s";
}

// Build ORDER BY clause
$order_by = "c.created_at DESC";
switch ($sort) {
    case 'oldest':
        $order_by = "c.created_at ASC";
        break;
    case 'name':
        $order_by = "c.title ASC";
        break;
    case 'updated':
        $order_by = "c.updated_at DESC";
        break;
}

// Get total count
$count_sql = "SELECT COUNT(*) as total
              FROM courses c
              JOIN subcategories sub ON c.subcategory_id = sub.subcategory_id
              JOIN categories cat ON sub.category_id = cat.category_id
              WHERE " . implode(" AND ", $where_conditions);

$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param($param_types, ...$params);
$count_stmt->execute();
$total_result = $count_stmt->get_result();
$total_count = $total_result->fetch_assoc()['total'];

// Get courses
$sql = "SELECT 
            c.*,
            cat.name as category_name,
            sub.name as subcategory_name,
            (SELECT COUNT(*) FROM enrollments WHERE course_id = c.course_id AND status = 'Active') as student_count,
            (SELECT AVG(rating) FROM course_ratings WHERE course_id = c.course_id) as average_rating
        FROM courses c
        JOIN subcategories sub ON c.subcategory_id = sub.subcategory_id
        JOIN categories cat ON sub.category_id = cat.category_id
        WHERE " . implode(" AND ", $where_conditions) . "
        ORDER BY $order_by
        LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$param_types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$courses = [];
while ($row = $result->fetch_assoc()) {
    // Get instructors for each course
    $instructors_query = "SELECT 
                             u.user_id,
                             u.first_name,
                             u.last_name,
                             u.profile_pic,
                             ci.is_primary
                         FROM course_instructors ci
                         JOIN instructors i ON ci.instructor_id = i.instructor_id
                         JOIN users u ON i.user_id = u.user_id
                         WHERE ci.course_id = ? AND ci.deleted_at IS NULL";
    
    $inst_stmt = $conn->prepare($instructors_query);
    $inst_stmt->bind_param("i", $row['course_id']);
    $inst_stmt->execute();
    $inst_result = $inst_stmt->get_result();
    
    $instructors = [];
    while ($instructor = $inst_result->fetch_assoc()) {
        $instructors[] = $instructor;
    }
    
    $row['instructors'] = $instructors;
    $courses[] = $row;
}

echo json_encode([
    'success' => true,
    'courses' => $courses,
    'total' => $total_count,
    'pages' => ceil($total_count / $limit),
    'current_page' => $page
]);

// $conn->close();
?>