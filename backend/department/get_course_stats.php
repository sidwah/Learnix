<?php
// backend/department/get_course_stats.php
session_start();
require_once '../config.php';
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

// Get course statistics
$stats_query = "SELECT 
                   COUNT(*) as total_courses,
                   SUM(CASE WHEN c.status = 'Published' THEN 1 ELSE 0 END) as published_courses,
                   SUM(CASE WHEN c.status = 'Draft' OR c.approval_status = 'pending' THEN 1 ELSE 0 END) as draft_pending_courses,
                   SUM(CASE WHEN c.approval_status = 'under_review' THEN 1 ELSE 0 END) as under_review_courses,
                   SUM(CASE WHEN c.approval_status = 'rejected' THEN 1 ELSE 0 END) as rejected_courses
               FROM courses c
               WHERE c.department_id = ? AND c.deleted_at IS NULL";

$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("i", $department_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();

// Calculate percentages
$total = $stats['total_courses'];
$published_percentage = $total > 0 ? round(($stats['published_courses'] / $total) * 100) : 0;
$pending_percentage = $total > 0 ? round(($stats['draft_pending_courses'] / $total) * 100) : 0;
$review_percentage = $total > 0 ? round(($stats['under_review_courses'] / $total) * 100) : 0;

// Get additional metrics
$metrics_query = "SELECT 
                     SUM(e.student_count) as total_students,
                     AVG(cr.average_rating) as avg_rating,
                     SUM(e.total_revenue) as total_revenue
                 FROM (
                     SELECT 
                         c.course_id,
                         COUNT(en.enrollment_id) as student_count,
                         SUM(cp.amount) as total_revenue
                     FROM courses c
                     LEFT JOIN enrollments en ON c.course_id = en.course_id AND en.status = 'Active'
                     LEFT JOIN course_payments cp ON en.enrollment_id = cp.enrollment_id AND cp.status = 'Completed'
                     WHERE c.department_id = ? AND c.deleted_at IS NULL
                     GROUP BY c.course_id
                 ) e
                 LEFT JOIN (
                     SELECT course_id, AVG(rating) as average_rating
                     FROM course_ratings
                     GROUP BY course_id
                 ) cr ON e.course_id = cr.course_id";

$metrics_stmt = $conn->prepare($metrics_query);
$metrics_stmt->bind_param("i", $department_id);
$metrics_stmt->execute();
$metrics_result = $metrics_stmt->get_result();
$metrics = $metrics_result->fetch_assoc();

// Prepare response
$response = [
    'success' => true,
    'stats' => [
        'total_courses' => (int)$stats['total_courses'],
        'published_courses' => (int)$stats['published_courses'],
        'draft_pending_courses' => (int)$stats['draft_pending_courses'],
        'under_review_courses' => (int)$stats['under_review_courses'],
        'rejected_courses' => (int)$stats['rejected_courses'],
        'published_percentage' => $published_percentage,
        'pending_percentage' => $pending_percentage,
        'review_percentage' => $review_percentage,
        'total_students' => (int)($metrics['total_students'] ?? 0),
        'average_rating' => round($metrics['avg_rating'] ?? 0, 1),
        'total_revenue' => (float)($metrics['total_revenue'] ?? 0)
    ]
];

echo json_encode($response);

$conn->close();
?>