<?php
require_once '../config.php';
header('Content-Type: application/json');

// Authentication check
require_once '../auth/admin/admin-auth-check.php';

// Get period and section from POST/GET
$period = isset($_POST['period']) ? $_POST['period'] : 'all';
$section = isset($_GET['section']) ? $_GET['section'] : 'all_students';
$isExport = isset($_GET['export']) && $_GET['export'] === 'csv';

// Define date filters
$whereClause = " WHERE deleted_at IS NULL";
$userWhereClause = " WHERE role = 'student' AND deleted_at IS NULL";
$enrollmentDateClause = "";
$activityDateClause = "";
$newUsersDateClause = "MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";

switch ($period) {
    case 'year':
        $userWhereClause .= " AND YEAR(created_at) = YEAR(CURRENT_DATE())";
        $enrollmentDateClause = " AND YEAR(e.enrolled_at) = YEAR(CURRENT_DATE())";
        $activityDateClause = " AND YEAR(created_at) = YEAR(CURRENT_DATE())";
        $newUsersDateClause = "YEAR(created_at) = YEAR(CURRENT_DATE())";
        break;
        
    case 'month':
        $userWhereClause .= " AND YEAR(created_at) = YEAR(CURRENT_DATE()) AND MONTH(created_at) = MONTH(CURRENT_DATE())";
        $enrollmentDateClause = " AND YEAR(e.enrolled_at) = YEAR(CURRENT_DATE()) AND MONTH(e.enrolled_at) = MONTH(CURRENT_DATE())";
        $activityDateClause = " AND YEAR(created_at) = YEAR(CURRENT_DATE()) AND MONTH(created_at) = MONTH(CURRENT_DATE())";
        $newUsersDateClause = "YEAR(created_at) = YEAR(CURRENT_DATE()) AND MONTH(created_at) = MONTH(CURRENT_DATE())";
        break;
        
    case 'week':
        $userWhereClause .= " AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)";
        $enrollmentDateClause = " AND e.enrolled_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)";
        $activityDateClause = " AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)";
        $newUsersDateClause = "created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)";
        break;
        
    case 'all':
    default:
        // No additional date filters
        break;
}

// Initialize response
$response = [];

// Student statistics - always include this
$studentQuery = "SELECT
    COUNT(*) as total_students,
    SUM(CASE WHEN $newUsersDateClause THEN 1 ELSE 0 END) as new_students_month,
    (SELECT COUNT(DISTINCT user_id) 
     FROM user_activity_logs 
     WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
     AND user_id IN (SELECT user_id FROM users WHERE role = 'student' AND deleted_at IS NULL)
     $activityDateClause) as active_students
FROM users
$userWhereClause";
$studentResult = mysqli_query($conn, $studentQuery);
$response['studentStats'] = mysqli_fetch_assoc($studentResult);

// Enrollment statistics - always include this
$enrollmentQuery = "SELECT
    COUNT(*) as total_enrollments,
    SUM(CASE WHEN e.status = 'Active' THEN 1 ELSE 0 END) as active_enrollments,
    SUM(CASE WHEN e.status = 'Completed' THEN 1 ELSE 0 END) as completed_enrollments,
    SUM(CASE WHEN DATE(e.enrolled_at) = CURRENT_DATE() THEN 1 ELSE 0 END) as enrollments_today,
    SUM(CASE WHEN DATE(e.enrolled_at) >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as enrollments_week,
    SUM(CASE WHEN DATE(e.enrolled_at) >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as enrollments_month
FROM enrollments e
JOIN users u ON e.user_id = u.user_id
WHERE u.role = 'student' AND e.deleted_at IS NULL AND u.deleted_at IS NULL
$enrollmentDateClause";
$enrollmentResult = mysqli_query($conn, $enrollmentQuery);
$response['enrollmentStats'] = mysqli_fetch_assoc($enrollmentResult);

// Completion rate - always include this
$completionRateQuery = "SELECT 
    AVG(completion_percentage) as avg_completion_percentage,
    SUM(CASE WHEN completion_percentage = 100 THEN 1 ELSE 0 END) as fully_completed,
    COUNT(*) as total_enrollments
FROM enrollments e
JOIN users u ON e.user_id = u.user_id
WHERE u.role = 'student' AND e.deleted_at IS NULL AND u.deleted_at IS NULL
$enrollmentDateClause";
$completionRateResult = mysqli_query($conn, $completionRateQuery);
$response['completionRateStats'] = mysqli_fetch_assoc($completionRateResult);

// Student retention metrics
$retentionQuery = "SELECT 
    COUNT(DISTINCT e.user_id) as returning_students,
    (SELECT COUNT(DISTINCT user_id) FROM enrollments WHERE deleted_at IS NULL) as total_enrolled_students
FROM enrollments e
WHERE e.user_id IN (
    SELECT user_id
    FROM enrollments
    GROUP BY user_id
    HAVING COUNT(*) > 1
) AND e.deleted_at IS NULL
$enrollmentDateClause";
$retentionResult = mysqli_query($conn, $retentionQuery);
$retentionStats = mysqli_fetch_assoc($retentionResult);
$retentionStats['retention_rate'] = ($retentionStats['returning_students'] / max(1, $retentionStats['total_enrolled_students'])) * 100;
$response['retentionStats'] = $retentionStats;

// Average completion time
$completionTimeQuery = "SELECT 
    AVG(DATEDIFF(
        CASE WHEN e.status = 'Completed' THEN e.last_accessed ELSE CURRENT_DATE() END,
        e.enrolled_at
    )) as avg_completion_days
FROM enrollments e
WHERE e.deleted_at IS NULL AND e.completion_percentage > 0
$enrollmentDateClause";
$completionTimeResult = mysqli_query($conn, $completionTimeQuery);
$response['completionTimeStats'] = mysqli_fetch_assoc($completionTimeResult);

// Top students by completions
$topStudentsQuery = "SELECT 
    u.user_id, CONCAT(u.first_name, ' ', u.last_name) as student_name,
    COUNT(e.enrollment_id) as enrollment_count,
    SUM(CASE WHEN e.completion_percentage = 100 THEN 1 ELSE 0 END) as completed_courses,
    AVG(e.completion_percentage) as avg_completion
FROM users u
LEFT JOIN enrollments e ON u.user_id = e.user_id AND e.deleted_at IS NULL
WHERE u.role = 'student' AND u.deleted_at IS NULL
$enrollmentDateClause
GROUP BY u.user_id
ORDER BY completed_courses DESC, avg_completion DESC
LIMIT 5";
$topStudentsResult = mysqli_query($conn, $topStudentsQuery);
$response['topStudents'] = [];
while ($student = mysqli_fetch_assoc($topStudentsResult)) {
    $response['topStudents'][] = $student;
}

// Monthly student enrollment data for chart
$monthlyEnrollmentQuery = "SELECT 
    DATE_FORMAT(e.enrolled_at, '%Y-%m') as month,
    COUNT(*) as enrollment_count
FROM enrollments e
JOIN users u ON e.user_id = u.user_id
WHERE u.role = 'student' AND e.deleted_at IS NULL AND u.deleted_at IS NULL
AND e.enrolled_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 11 MONTH)
$enrollmentDateClause
GROUP BY DATE_FORMAT(e.enrolled_at, '%Y-%m')
ORDER BY month ASC";
$monthlyEnrollmentResult = mysqli_query($conn, $monthlyEnrollmentQuery);
$response['monthlyEnrollmentData'] = [];
while ($month = mysqli_fetch_assoc($monthlyEnrollmentResult)) {
    $response['monthlyEnrollmentData'][] = $month;
}

// Popular course categories
$categoryPreferencesQuery = "SELECT 
    c.name as category_name,
    COUNT(e.enrollment_id) as enrollment_count
FROM categories c
JOIN subcategories s ON c.category_id = s.category_id
JOIN courses co ON s.subcategory_id = co.subcategory_id
JOIN enrollments e ON co.course_id = e.course_id
WHERE c.deleted_at IS NULL AND s.deleted_at IS NULL 
    AND co.deleted_at IS NULL AND e.deleted_at IS NULL
$enrollmentDateClause
GROUP BY c.category_id
ORDER BY enrollment_count DESC
LIMIT 5";
$categoryPreferencesResult = mysqli_query($conn, $categoryPreferencesQuery);
$response['categoryPreferences'] = [];
while ($category = mysqli_fetch_assoc($categoryPreferencesResult)) {
    $response['categoryPreferences'][] = $category;
}

// Convert stats to floats for consistency
$numericFields = ['studentStats', 'enrollmentStats', 'completionRateStats', 'retentionStats', 'completionTimeStats'];
foreach ($numericFields as $key) {
    if (isset($response[$key]) && is_array($response[$key])) {
        $response[$key] = array_map(function($value) {
            return is_numeric($value) ? floatval($value) : $value;
        }, $response[$key]);
    }
}

// Output JSON response
echo json_encode($response);
mysqli_close($conn);
?>