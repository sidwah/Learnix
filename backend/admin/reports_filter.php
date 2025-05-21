<?php
require_once '../config.php';
header('Content-Type: application/json');

// Authentication check
require_once '../auth/admin/admin-auth-check.php';

// Get period and section from POST/GET
$period = isset($_POST['period']) ? $_POST['period'] : 'all';
$section = isset($_GET['section']) ? $_GET['section'] : 'all';
$isExport = isset($_GET['export']) && $_GET['export'] === 'csv';

// Define date filters
$currentDate = date('Y-m-d');
$whereClause = " WHERE deleted_at IS NULL";
$enrollmentDateClause = "";
$revenueDateClause = "";
$newUsersDateClause = "MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";

switch ($period) {
    case 'year':
        $whereClause .= " AND YEAR(created_at) = YEAR(CURRENT_DATE())";
        $enrollmentDateClause = " AND YEAR(enrolled_at) = YEAR(CURRENT_DATE())";
        $revenueDateClause = " AND YEAR(payment_date) = YEAR(CURRENT_DATE())";
        $newUsersDateClause = "YEAR(created_at) = YEAR(CURRENT_DATE())";
        break;
    case 'month':
        $whereClause .= " AND YEAR(created_at) = YEAR(CURRENT_DATE()) AND MONTH(created_at) = MONTH(CURRENT_DATE())";
        $enrollmentDateClause = " AND YEAR(enrolled_at) = YEAR(CURRENT_DATE()) AND MONTH(enrolled_at) = MONTH(CURRENT_DATE())";
        $revenueDateClause = " AND YEAR(payment_date) = YEAR(CURRENT_DATE()) AND MONTH(payment_date) = MONTH(CURRENT_DATE())";
        $newUsersDateClause = "YEAR(created_at) = YEAR(CURRENT_DATE()) AND MONTH(created_at) = MONTH(CURRENT_DATE())";
        break;
    case 'week':
        $whereClause .= " AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)";
        $enrollmentDateClause = " AND enrolled_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)";
        $revenueDateClause = " AND payment_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)";
        $newUsersDateClause = "created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)";
        break;
    case 'all':
    default:
        // No additional date filters
        break;
}

// Initialize response
$response = [];

// Fetch data based on section
if ($section === 'users' || $section === 'all') {
    $userQuery = "SELECT 
                    COUNT(*) as total_users,
                    SUM(CASE WHEN role = 'student' THEN 1 ELSE 0 END) as student_count,
                    SUM(CASE WHEN role = 'instructor' THEN 1 ELSE 0 END) as instructor_count,
                    SUM(CASE WHEN role = 'department_head' THEN 1 ELSE 0 END) as dept_head_count,
                    SUM(CASE WHEN role = 'department_secretary' THEN 1 ELSE 0 END) as dept_sec_count,
                    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_count,
                    SUM(CASE WHEN $newUsersDateClause THEN 1 ELSE 0 END) as new_users_month
                  FROM users 
                  $whereClause";
    $userResult = mysqli_query($conn, $userQuery);
    $response['userStats'] = mysqli_fetch_assoc($userResult);
}

if ($section === 'courses' || $section === 'all') {
    $courseQuery = "SELECT 
                     COUNT(*) as total_courses,
                     SUM(CASE WHEN status = 'Published' THEN 1 ELSE 0 END) as published_courses,
                     SUM(CASE WHEN status = 'Draft' THEN 1 ELSE 0 END) as draft_courses,
                     SUM(CASE WHEN financial_approval_date IS NOT NULL THEN 1 ELSE 0 END) as financially_approved,
                     SUM(CASE WHEN financial_approval_date IS NULL THEN 1 ELSE 0 END) as pending_financial,
                     COUNT(DISTINCT department_id) as departments_with_courses
                   FROM courses
                   $whereClause";
    $courseResult = mysqli_query($conn, $courseQuery);
    $response['courseStats'] = mysqli_fetch_assoc($courseResult);
}

if ($section === 'enrollments' || $section === 'all') {
    $enrollmentQuery = "SELECT 
                         COUNT(*) as total_enrollments,
                         SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active_enrollments,
                         SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_enrollments,
                         SUM(CASE WHEN status = 'Expired' THEN 1 ELSE 0 END) as expired_enrollments,
                         SUM(CASE WHEN DATE(enrolled_at) = CURRENT_DATE() THEN 1 ELSE 0 END) as enrollments_today,
                         SUM(CASE WHEN DATE(enrolled_at) >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as enrollments_week,
                         SUM(CASE WHEN DATE(enrolled_at) >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as enrollments_month
                       FROM enrollments
                       WHERE deleted_at IS NULL $enrollmentDateClause";
    $enrollmentResult = mysqli_query($conn, $enrollmentQuery);
    $response['enrollmentStats'] = mysqli_fetch_assoc($enrollmentResult);
}

if ($section === 'revenue' || $section === 'all') {
    $revenueQuery = "SELECT 
                      SUM(amount) as total_revenue,
                      SUM(CASE WHEN DATE(payment_date) = CURRENT_DATE() THEN amount ELSE 0 END) as revenue_today,
                      SUM(CASE WHEN DATE(payment_date) >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY) THEN amount ELSE 0 END) as revenue_week,
                      SUM(CASE WHEN DATE(payment_date) >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) THEN amount ELSE 0 END) as revenue_month,
                      SUM(CASE WHEN YEAR(payment_date) = YEAR(CURRENT_DATE()) THEN amount ELSE 0 END) as revenue_year,
                      COUNT(*) as total_transactions,
                      AVG(amount) as average_transaction
                    FROM course_payments
                    WHERE status = 'Completed' AND deleted_at IS NULL $revenueDateClause";
    $revenueResult = mysqli_query($conn, $revenueQuery);
    $response['revenueStats'] = mysqli_fetch_assoc($revenueResult);
}

// Only fetch chart data for full export or UI updates
if ($section === 'all') {
    $enrollmentDateFilter = $period === 'all' ? " AND enrolled_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 11 MONTH)" : $enrollmentDateClause;
    $monthlyEnrollmentQuery = "SELECT 
                                DATE_FORMAT(enrolled_at, '%Y-%m') as month,
                                COUNT(*) as enrollment_count
                               FROM enrollments
                               WHERE deleted_at IS NULL $enrollmentDateFilter
                               GROUP BY DATE_FORMAT(enrolled_at, '%Y-%m')
                               ORDER BY month ASC";
    $monthlyEnrollmentResult = mysqli_query($conn, $monthlyEnrollmentQuery);
    $response['monthlyEnrollmentData'] = [];
    while ($month = mysqli_fetch_assoc($monthlyEnrollmentResult)) {
        $response['monthlyEnrollmentData'][] = $month;
    }

    $revenueDateFilter = $period === 'all' ? " AND payment_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 11 MONTH)" : $revenueDateClause;
    $monthlyRevenueQuery = "SELECT 
                             DATE_FORMAT(payment_date, '%Y-%m') as month,
                             SUM(amount) as revenue
                            FROM course_payments
                            WHERE status = 'Completed' AND deleted_at IS NULL $revenueDateFilter
                            GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
                            ORDER BY month ASC";
    $monthlyRevenueResult = mysqli_query($conn, $monthlyRevenueQuery);
    $response['monthlyRevenueData'] = [];
    while ($month = mysqli_fetch_assoc($monthlyRevenueResult)) {
        $response['monthlyRevenueData'][] = $month;
    }
}

// Convert stats to floats for consistency
foreach (['userStats', 'courseStats', 'enrollmentStats', 'revenueStats'] as $key) {
    if (isset($response[$key])) {
        $response[$key] = array_map('floatval', $response[$key]);
    }
}

// Output JSON
echo json_encode($response);
mysqli_close($conn);
?>