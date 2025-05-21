<?php
require_once '../config.php';
header('Content-Type: application/json');

// At the top of instructor_reports_filter.php
ini_set('display_errors', 0); // Hide errors in production
error_reporting(E_ALL); // Log errors instead

// Authentication check
require_once '../auth/admin/admin-auth-check.php';

// Get period and section from POST/GET
$period = isset($_POST['period']) ? $_POST['period'] : 'all';
$section = isset($_GET['section']) ? $_GET['section'] : 'all_instructors';
$isExport = isset($_GET['export']) && $_GET['export'] === 'csv';

// Define date filters
$whereClause = " WHERE deleted_at IS NULL";
$userWhereClause = " WHERE role = 'instructor' AND deleted_at IS NULL";
$courseWhereClause = " WHERE c.deleted_at IS NULL";
$earningsWhereClause = " WHERE ie.deleted_at IS NULL";
$activityDateClause = "";
$newUsersDateClause = "MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";

switch ($period) {
    case 'year':
        $userWhereClause .= " AND YEAR(created_at) = YEAR(CURRENT_DATE())";
        $courseWhereClause .= " AND YEAR(c.created_at) = YEAR(CURRENT_DATE())";
        $earningsWhereClause .= " AND YEAR(ie.created_at) = YEAR(CURRENT_DATE())";
        $activityDateClause = " AND YEAR(created_at) = YEAR(CURRENT_DATE())";
        $newUsersDateClause = "YEAR(created_at) = YEAR(CURRENT_DATE())";
        break;
        
    case 'month':
        $userWhereClause .= " AND YEAR(created_at) = YEAR(CURRENT_DATE()) AND MONTH(created_at) = MONTH(CURRENT_DATE())";
        $courseWhereClause .= " AND YEAR(c.created_at) = YEAR(CURRENT_DATE()) AND MONTH(c.created_at) = MONTH(CURRENT_DATE())";
        $earningsWhereClause .= " AND YEAR(ie.created_at) = YEAR(CURRENT_DATE()) AND MONTH(ie.created_at) = MONTH(CURRENT_DATE())";
        $activityDateClause = " AND YEAR(created_at) = YEAR(CURRENT_DATE()) AND MONTH(created_at) = MONTH(CURRENT_DATE())";
        $newUsersDateClause = "YEAR(created_at) = YEAR(CURRENT_DATE()) AND MONTH(created_at) = MONTH(CURRENT_DATE())";
        break;
        
    case 'week':
        $userWhereClause .= " AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)";
        $courseWhereClause .= " AND c.created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)";
        $earningsWhereClause .= " AND ie.created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)";
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

// Instructor statistics
$instructorQuery = "SELECT
    COUNT(*) as total_instructors,
    SUM(CASE WHEN $newUsersDateClause THEN 1 ELSE 0 END) as new_instructors_month,
    (SELECT COUNT(DISTINCT user_id) 
     FROM user_activity_logs 
     WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
     AND user_id IN (SELECT user_id FROM users WHERE role = 'instructor' AND deleted_at IS NULL)
     $activityDateClause) as active_instructors
FROM users
$userWhereClause";
$instructorResult = mysqli_query($conn, $instructorQuery);
$response['instructorStats'] = mysqli_fetch_assoc($instructorResult);

// Course statistics
$courseQuery = "SELECT
    COUNT(DISTINCT c.course_id) as total_courses,
    SUM(CASE WHEN c.status = 'Published' THEN 1 ELSE 0 END) as published_courses,
    SUM(CASE WHEN c.status = 'Draft' THEN 1 ELSE 0 END) as draft_courses,
    SUM(CASE WHEN MONTH(c.created_at) = MONTH(CURRENT_DATE()) AND YEAR(c.created_at) = YEAR(CURRENT_DATE()) THEN 1 ELSE 0 END) as new_courses_month,
    COUNT(DISTINCT ci.instructor_id) as instructors_with_courses
FROM courses c
JOIN course_instructors ci ON c.course_id = ci.course_id AND ci.deleted_at IS NULL
$courseWhereClause";
$courseResult = mysqli_query($conn, $courseQuery);
$response['courseStats'] = mysqli_fetch_assoc($courseResult);

// Revenue and earnings statistics
$revenueQuery = "SELECT
    SUM(cp.amount) as total_revenue,
    SUM(ie.instructor_share) as total_earnings,
    SUM(CASE WHEN ie.status = 'Available' THEN ie.instructor_share ELSE 0 END) as available_earnings,
    SUM(CASE WHEN ie.status = 'Withdrawn' THEN ie.instructor_share ELSE 0 END) as withdrawn_earnings,
    SUM(CASE WHEN ie.status = 'Pending' THEN ie.instructor_share ELSE 0 END) as pending_earnings,
    COUNT(DISTINCT ie.instructor_id) as earning_instructors,
    (SELECT setting_value FROM revenue_settings WHERE setting_name = 'instructor_split') as instructor_split
FROM course_payments cp
JOIN enrollments e ON cp.enrollment_id = e.enrollment_id AND e.deleted_at IS NULL
JOIN courses c ON e.course_id = c.course_id AND c.deleted_at IS NULL
JOIN instructor_earnings ie ON cp.payment_id = ie.payment_id
WHERE cp.status = 'Completed' AND cp.deleted_at IS NULL
$earningsWhereClause";
$revenueResult = mysqli_query($conn, $revenueQuery);
$revenueStats = mysqli_fetch_assoc($revenueResult);
$revenueStats['avg_earning_per_instructor'] = $revenueStats['earning_instructors'] > 0 
    ? $revenueStats['total_earnings'] / $revenueStats['earning_instructors'] 
    : 0;
$response['revenueStats'] = $revenueStats;

// Department association statistics
$departmentQuery = "SELECT
    COUNT(DISTINCT d.department_id) as total_departments,
    COUNT(DISTINCT di.instructor_id) as instructors_in_departments,
    AVG(depart_count) as avg_departments_per_instructor
FROM departments d
JOIN department_instructors di ON d.department_id = di.department_id AND di.deleted_at IS NULL
JOIN (
    SELECT instructor_id, COUNT(DISTINCT department_id) as depart_count
    FROM department_instructors
    WHERE deleted_at IS NULL
    GROUP BY instructor_id
) as dept_counts ON di.instructor_id = dept_counts.instructor_id
WHERE d.deleted_at IS NULL";
$departmentResult = mysqli_query($conn, $departmentQuery);
$response['departmentStats'] = mysqli_fetch_assoc($departmentResult);

// Top instructors by revenue
$topInstructorsQuery = "SELECT
    i.instructor_id,
    CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
    COUNT(DISTINCT c.course_id) as course_count,
    COUNT(DISTINCT e.enrollment_id) as student_count,
    SUM(ie.instructor_share) as total_earnings,
    AVG(r.rating) as avg_rating
FROM instructors i
JOIN users u ON i.user_id = u.user_id AND u.deleted_at IS NULL
LEFT JOIN course_instructors ci ON i.instructor_id = ci.instructor_id AND ci.deleted_at IS NULL
LEFT JOIN courses c ON ci.course_id = c.course_id AND c.deleted_at IS NULL
LEFT JOIN enrollments e ON c.course_id = e.course_id AND e.deleted_at IS NULL
LEFT JOIN instructor_earnings ie ON i.instructor_id = ie.instructor_id AND ie.deleted_at IS NULL
LEFT JOIN course_ratings r ON c.course_id = r.course_id
WHERE i.deleted_at IS NULL
GROUP BY i.instructor_id
ORDER BY total_earnings DESC
LIMIT 5";
$topInstructorsResult = mysqli_query($conn, $topInstructorsQuery);
$response['topInstructors'] = [];
while ($instructor = mysqli_fetch_assoc($topInstructorsResult)) {
    $response['topInstructors'][] = $instructor;
}

// Monthly course creation data for chart
$courseTimeFilter = $period === 'all' ? " AND c.created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 11 MONTH)" : $courseWhereClause;
$monthlyCourseQuery = "SELECT
    DATE_FORMAT(c.created_at, '%Y-%m') as month,
    COUNT(*) as course_count
FROM courses c
JOIN course_instructors ci ON c.course_id = ci.course_id AND ci.deleted_at IS NULL
WHERE c.deleted_at IS NULL
$courseTimeFilter
GROUP BY DATE_FORMAT(c.created_at, '%Y-%m')
ORDER BY month ASC";
$monthlyCourseResult = mysqli_query($conn, $monthlyCourseQuery);
$response['monthlyCourseData'] = [];
while ($month = mysqli_fetch_assoc($monthlyCourseResult)) {
    $response['monthlyCourseData'][] = $month;
}

// Monthly instructor earnings data for chart
$earningsTimeFilter = $period === 'all' ? " AND ie.created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 11 MONTH)" : $earningsWhereClause;
$monthlyEarningsQuery = "SELECT
     DATE_FORMAT(ie.created_at, '%Y-%m') as month,
   SUM(ie.instructor_share) as earnings
FROM instructor_earnings ie
WHERE ie.deleted_at IS NULL
$earningsTimeFilter
GROUP BY DATE_FORMAT(ie.created_at, '%Y-%m')
ORDER BY month ASC";
$monthlyEarningsResult = mysqli_query($conn, $monthlyEarningsQuery);
$response['monthlyEarningsData'] = [];
while ($month = mysqli_fetch_assoc($monthlyEarningsResult)) {
   $response['monthlyEarningsData'][] = $month;
}

// Department distribution data
$departmentDistributionQuery = "SELECT
   d.name as department_name,
   COUNT(DISTINCT di.instructor_id) as instructor_count
FROM departments d
JOIN department_instructors di ON d.department_id = di.department_id AND di.deleted_at IS NULL
WHERE d.deleted_at IS NULL
GROUP BY d.department_id
ORDER BY instructor_count DESC
LIMIT 5";
$departmentDistributionResult = mysqli_query($conn, $departmentDistributionQuery);
$response['departmentDistribution'] = [];
while ($department = mysqli_fetch_assoc($departmentDistributionResult)) {
   $response['departmentDistribution'][] = $department;
}

// Convert stats to floats for consistency
$numericFields = ['instructorStats', 'courseStats', 'revenueStats', 'departmentStats'];
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