<?php
// Authentication check
require_once '../backend/auth/admin/admin-auth-check.php';

// Set page title
$pageTitle = "Instructors Reports - Admin | Learnix";

include_once '../includes/admin/header.php';
include_once '../includes/admin/sidebar.php';
include_once '../includes/admin/navbar.php';

// Get data from database
require_once '../backend/config.php';

// Get current date
$currentDate = date('Y-m-d');

// Total instructors
$instructorQuery = "SELECT
    COUNT(*) as total_instructors,
    SUM(CASE WHEN MONTH(u.created_at) = MONTH(CURRENT_DATE()) AND YEAR(u.created_at) = YEAR(CURRENT_DATE()) THEN 1 ELSE 0 END) as new_instructors_month,
    (SELECT COUNT(DISTINCT user_id) 
     FROM user_activity_logs 
     WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) 
     AND user_id IN (SELECT user_id FROM users WHERE role = 'instructor' AND deleted_at IS NULL)) as active_instructors
FROM users u
WHERE u.role = 'instructor' AND u.deleted_at IS NULL";
$instructorResult = mysqli_query($conn, $instructorQuery);
$instructorStats = mysqli_fetch_assoc($instructorResult);

// Course statistics
$courseQuery = "SELECT
    COUNT(DISTINCT c.course_id) as total_courses,
    SUM(CASE WHEN c.status = 'Published' THEN 1 ELSE 0 END) as published_courses,
    SUM(CASE WHEN c.status = 'Draft' THEN 1 ELSE 0 END) as draft_courses,
    SUM(CASE WHEN MONTH(c.created_at) = MONTH(CURRENT_DATE()) AND YEAR(c.created_at) = YEAR(CURRENT_DATE()) THEN 1 ELSE 0 END) as new_courses_month,
    COUNT(DISTINCT ci.instructor_id) as instructors_with_courses
FROM courses c
JOIN course_instructors ci ON c.course_id = ci.course_id AND ci.deleted_at IS NULL
WHERE c.deleted_at IS NULL";
$courseResult = mysqli_query($conn, $courseQuery);
$courseStats = mysqli_fetch_assoc($courseResult);

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
JOIN instructor_earnings ie ON cp.payment_id = ie.payment_id AND ie.deleted_at IS NULL
WHERE cp.status = 'Completed' AND cp.deleted_at IS NULL";
$revenueResult = mysqli_query($conn, $revenueQuery);
$revenueStats = mysqli_fetch_assoc($revenueResult);
$revenueStats['avg_earning_per_instructor'] = $revenueStats['earning_instructors'] > 0
    ? $revenueStats['total_earnings'] / $revenueStats['earning_instructors']
    : 0;

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
$departmentStats = mysqli_fetch_assoc($departmentResult);


// Top instructors by revenue
$topInstructorsQuery = "SELECT
    i.instructor_id,
    CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
    COUNT(DISTINCT c.course_id) as course_count,
    COUNT(DISTINCT e.enrollment_id) as student_count,
    SUM(ie.instructor_share) as total_earnings,
    COALESCE(AVG(r.rating), 0) as avg_rating  -- Use COALESCE to replace NULL with 0
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
$topInstructors = [];
while ($instructor = mysqli_fetch_assoc($topInstructorsResult)) {
    $topInstructors[] = $instructor;
}

// Monthly course creation data for chart
$monthlyCourseQuery = "SELECT
    DATE_FORMAT(c.created_at, '%Y-%m') as month,
    COUNT(*) as course_count
FROM courses c
JOIN course_instructors ci ON c.course_id = ci.course_id AND ci.deleted_at IS NULL
WHERE c.deleted_at IS NULL
AND c.created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 11 MONTH)
GROUP BY DATE_FORMAT(c.created_at, '%Y-%m')
ORDER BY month ASC";
$monthlyCourseResult = mysqli_query($conn, $monthlyCourseQuery);
$monthlyCourseData = [];
while ($month = mysqli_fetch_assoc($monthlyCourseResult)) {
    $monthlyCourseData[] = $month;
}

// Monthly instructor earnings data for chart
$monthlyEarningsQuery = "SELECT
    DATE_FORMAT(ie.created_at, '%Y-%m') as month,
    SUM(ie.instructor_share) as earnings
FROM instructor_earnings ie
WHERE ie.deleted_at IS NULL
AND ie.created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 11 MONTH)
GROUP BY DATE_FORMAT(ie.created_at, '%Y-%m')
ORDER BY month ASC";
$monthlyEarningsResult = mysqli_query($conn, $monthlyEarningsQuery);
$monthlyEarningsData = [];
while ($month = mysqli_fetch_assoc($monthlyEarningsResult)) {
    $monthlyEarningsData[] = $month;
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
$departmentDistribution = [];
while ($department = mysqli_fetch_assoc($departmentDistributionResult)) {
    $departmentDistribution[] = $department;
}

// Convert data to JSON for charts
$courseChartData = json_encode($monthlyCourseData);
$earningsChartData = json_encode($monthlyEarningsData);
$departmentDistributionData = json_encode($departmentDistribution);
?>

<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Admin /</span> Instructors Reports
    </h4>

    <!-- Report Date Range -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-1">Instructor Performance Overview</h5>
                    <p class="text-muted mb-md-0">Key instructor metrics as of <?php echo date('F d, Y'); ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="btn-group" role="group" aria-label="Report Period">
                        <button type="button" class="btn btn-outline-primary btn-sm active" data-period="all">All Time</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-period="year">This Year</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-period="month">This Month</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-period="week">This Week</button>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm ms-2" id="export-report">Export Report</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="row">
        <!-- Instructors Stats -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Instructors</h5>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="../admin/instructors.php">View All Instructors</a>
                            <a class="dropdown-item" href="#" data-export="instructors">Export Data</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex flex-column align-items-start gap-1">
                            <h2 class="mb-0" id="total-instructors"><?php echo number_format($instructorStats['total_instructors']); ?></h2>
                            <span class="text-muted">Total Instructors</span>
                        </div>
                        <div class="avatar me-1">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="bx bx-chalkboard-teacher bx-md"></i>
                            </span>
                        </div>
                    </div>
                    <ul class="p-0 m-0">
                        <li class="d-flex mb-2">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="bx bx-user-check"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <p class="mb-0">Active Instructors</p>
                                </div>
                                <div>
                                    <h5 class="mb-0" id="active-instructors"><?php echo number_format($instructorStats['active_instructors']); ?></h5>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-2">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="bx bx-user-plus"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <p class="mb-0">New This Month</p>
                                </div>
                                <div>
                                    <h5 class="mb-0" id="new-instructors"><?php echo number_format($instructorStats['new_instructors_month']); ?></h5>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Course Stats -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Courses</h5>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="../admin/courses.php">View All Courses</a>
                            <a class="dropdown-item" href="#" data-export="instructor_courses">Export Data</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex flex-column align-items-start gap-1">
                            <h2 class="mb-0" id="total-courses"><?php echo number_format($courseStats['total_courses']); ?></h2>
                            <span class="text-muted">Total Courses</span>
                        </div>
                        <div class="avatar me-1">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="bx bx-book bx-md"></i>
                            </span>
                        </div>
                    </div>
                    <ul class="p-0 m-0">
                        <li class="d-flex mb-2">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="bx bx-globe"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <p class="mb-0">Published</p>
                                </div>
                                <div>
                                    <h5 class="mb-0" id="published-courses"><?php echo number_format($courseStats['published_courses']); ?></h5>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-2">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="bx bx-edit"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <p class="mb-0">Draft</p>
                                </div>
                                <div>
                                    <h5 class="mb-0" id="draft-courses"><?php echo number_format($courseStats['draft_courses']); ?></h5>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div class="d-flex justify-content-between mt-3">
                        <p class="mb-0">Instructors with courses</p>
                        <p class="mb-0 text-success" id="instructors-with-courses"><?php echo number_format($courseStats['instructors_with_courses']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Earnings Stats -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Earnings</h5>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="#">View Financial Report</a>
                            <a class="dropdown-item" href="#" data-export="earnings">Export Data</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex flex-column align-items-start gap-1">
                            <h2 class="mb-0" id="total-earnings">₵<?php echo number_format($revenueStats['total_earnings'], 2); ?></h2>
                            <span class="text-muted">Total Instructor Earnings</span>
                        </div>
                        <div class="avatar me-1">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="bx bx-money bx-md"></i>
                            </span>
                        </div>
                    </div>
                    <ul class="p-0 m-0">
                        <li class="d-flex mb-2">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="bx bx-wallet"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <p class="mb-0">Available for Withdrawal</p>
                                </div>
                                <div>
                                    <h5 class="mb-0" id="available-earnings">₵<?php echo number_format($revenueStats['available_earnings'], 2); ?></h5>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-2">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="bx bx-time"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <p class="mb-0">Pending</p>
                                </div>
                                <div>
                                    <h5 class="mb-0" id="pending-earnings">₵<?php echo number_format($revenueStats['pending_earnings'], 2); ?></h5>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div class="d-flex justify-content-between mt-3">
                        <p class="mb-0">Revenue Split</p>
                        <p class="mb-0 text-info" id="revenue-split"><?php echo $revenueStats['instructor_split']; ?>% to instructors</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Associations Stats -->
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Department Associations</h5>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="../admin/departments.php">View Departments</a>
                            <a class="dropdown-item" href="#" data-export="departments">Export Data</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex flex-column align-items-start gap-1">
                            <h2 class="mb-0" id="departments-count"><?php echo number_format($departmentStats['total_departments']); ?></h2>
                            <span class="text-muted">Active Departments</span>
                        </div>
                        <div class="avatar me-1">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="bx bx-building-house bx-md"></i>
                            </span>
                        </div>
                    </div>
                    <ul class="p-0 m-0">
                        <li class="d-flex mb-2">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="bx bx-user-pin"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <p class="mb-0">Instructors in Departments</p>
                                </div>
                                <div>
                                    <h5 class="mb-0" id="instructors-in-departments"><?php echo number_format($departmentStats['instructors_in_departments']); ?></h5>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-2">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="bx bx-network-chart"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <p class="mb-0">Avg Departments/Instructor</p>
                                </div>
                                <div>
                                    <h5 class="mb-0" id="avg-departments"><?php echo number_format($departmentStats['avg_departments_per_instructor'], 1); ?></h5>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="col-xl-8 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Monthly New Courses</h5>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="#">View Detailed Report</a>
                            <a class="dropdown-item" href="#" data-export="course_trends">Export Data</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="courseTrendsChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Tables -->
    <div class="row">
        <!-- Top Instructors -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Top Earning Instructors</h5>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="../admin/instructors.php">View All Instructors</a>
                            <a class="dropdown-item" href="#" data-export="top_instructors">Export Data</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Instructor</th>
                                    <th>Courses</th>
                                    <th>Students</th>
                                    <th>Earnings</th>
                                    <th>Rating</th>
                                </tr>
                            </thead>
                            <tbody id="top-instructors-table">
                                <?php foreach ($topInstructors as $instructor): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-3">
                                                    <div class="avatar-initial rounded-circle bg-label-primary">
                                                        <?php
                                                        $initials = explode(' ', $instructor['instructor_name']);
                                                        echo substr($initials[0], 0, 1) . (isset($initials[1]) ? substr($initials[1], 0, 1) : '');
                                                        ?>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <h6 class="mb-0 text-nowrap"><?php echo htmlspecialchars($instructor['instructor_name']); ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo number_format($instructor['course_count']); ?></td>
                                        <td><?php echo number_format($instructor['student_count']); ?></td>
                                        <td>₵<?php echo number_format($instructor['total_earnings'], 2); ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="me-1"><?php echo ($instructor['avg_rating'] !== null) ? number_format($instructor['avg_rating'], 1) : '0.0'; ?></span>
                                                <i class="bx bxs-star text-warning"></i>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instructor Earnings Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Monthly Instructor Earnings</h5>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="#">View Detailed Report</a>
                            <a class="dropdown-item" href="#" data-export="earnings_trends">Export Data</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="earningsTrendsChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Distribution -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Department Distribution</h5>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="#">View All Departments</a>
                            <a class="dropdown-item" href="#" data-export="department_distribution">Export Data</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="departmentDistributionChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- / Content -->

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize charts
        let courseChart, earningsChart, departmentChart;

        // Function to initialize or update course trends chart
        function initializeCourseChart(courseData) {
            const monthLabels = [];
            const courseCounts = [];

            // Process course data
            courseData.forEach(item => {
                const date = new Date(item.month + '-01');
                monthLabels.push(date.toLocaleDateString('default', {
                    month: 'short',
                    year: 'numeric'
                }));
                courseCounts.push(parseInt(item.course_count));
            });

            // Destroy existing chart if it exists
            if (courseChart) courseChart.destroy();

            // Course Trends Chart
            const courseCtx = document.getElementById('courseTrendsChart').getContext('2d');
            courseChart = new Chart(courseCtx, {
                type: 'bar',
                data: {
                    labels: monthLabels,
                    datasets: [{
                        label: 'New Courses',
                        data: courseCounts,
                        backgroundColor: 'rgba(40, 167, 69, 0.7)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                precision: 0
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    }
                }
            });
        }

        // Function to initialize or update earnings trends chart
        function initializeEarningsChart(earningsData) {
            const monthLabels = [];
            const earningsAmounts = [];

            // Process earnings data
            earningsData.forEach(item => {
                const date = new Date(item.month + '-01');
                monthLabels.push(date.toLocaleDateString('default', {
                    month: 'short',
                    year: 'numeric'
                }));
                earningsAmounts.push(parseFloat(item.earnings));
            });

            // Destroy existing chart if it exists
            if (earningsChart) earningsChart.destroy();

            // Earnings Trends Chart
            const earningsCtx = document.getElementById('earningsTrendsChart').getContext('2d');
            earningsChart = new Chart(earningsCtx, {
                type: 'line',
                data: {
                    labels: monthLabels,
                    datasets: [{
                        label: 'Instructor Earnings (₵)',
                        data: earningsAmounts,
                        fill: true,
                        backgroundColor: 'rgba(105, 108, 255, 0.1)',
                        borderColor: 'rgba(105, 108, 255, 1)',
                        tension: 0.4,
                        pointBackgroundColor: 'rgba(105, 108, 255, 1)',
                        pointBorderColor: '#fff',
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return '₵' + value.toLocaleString();
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) label += ': ';
                                    if (context.parsed.y !== null) {
                                        label += '₵' + context.parsed.y.toLocaleString();
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Function to initialize or update department distribution chart
      function initializeDepartmentChart(departmentData) {
    const departmentLabels = [];
    const instructorCounts = [];
    const backgroundColors = [
        'rgba(105, 108, 255, 0.7)',
        'rgba(40, 167, 69, 0.7)',
        'rgba(255, 193, 7, 0.7)',
        'rgba(220, 53, 69, 0.7)',
        'rgba(23, 162, 184, 0.7)'
    ];

    // Process department data
    departmentData.forEach((item, index) => {
        departmentLabels.push(item.department_name);
        instructorCounts.push(parseInt(item.instructor_count));
    });

    // Destroy existing chart if it exists
    if (departmentChart) departmentChart.destroy();

    // Department Distribution Chart
    const departmentCtx = document.getElementById('departmentDistributionChart').getContext('2d');
    departmentChart = new Chart(departmentCtx, {
        type: 'bar', // Changed from 'horizontalBar' to 'bar'
        data: {
            labels: departmentLabels,
            datasets: [{
                label: 'Instructors per Department',
                data: instructorCounts,
                backgroundColor: backgroundColors,
                borderColor: 'rgba(255, 255, 255, 0.7)',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y', // Makes the bar chart horizontal
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        precision: 0
                    }
                },
                y: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let value = context.parsed.x;
                            return `${context.label}: ${value} instructor${value !== 1 ? 's' : ''}`;
                        }
                    }
                }
            }
        }
    });
}

        // Function to update UI with new data
        function updateUI(data) {
            // Update Instructors Stats
            if (data.instructorStats) {
                document.getElementById('total-instructors').textContent = data.instructorStats.total_instructors.toLocaleString();
                document.getElementById('active-instructors').textContent = data.instructorStats.active_instructors.toLocaleString();
                document.getElementById('new-instructors').textContent = data.instructorStats.new_instructors_month.toLocaleString();
            }

            // Update Course Stats
            if (data.courseStats) {
                document.getElementById('total-courses').textContent = data.courseStats.total_courses.toLocaleString();
                document.getElementById('published-courses').textContent = data.courseStats.published_courses.toLocaleString();
                document.getElementById('draft-courses').textContent = data.courseStats.draft_courses.toLocaleString();
                document.getElementById('instructors-with-courses').textContent = data.courseStats.instructors_with_courses.toLocaleString();
            }

            // Update Earnings Stats
            if (data.revenueStats) {
                document.getElementById('total-earnings').textContent = '₵' + data.revenueStats.total_earnings.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                document.getElementById('available-earnings').textContent = '₵' + data.revenueStats.available_earnings.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                document.getElementById('pending-earnings').textContent = '₵' + data.revenueStats.pending_earnings.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                document.getElementById('revenue-split').textContent = data.revenueStats.instructor_split + '% to instructors';
            }

            // Update Department Stats
            if (data.departmentStats) {
                document.getElementById('departments-count').textContent = data.departmentStats.total_departments.toLocaleString();
                document.getElementById('instructors-in-departments').textContent = data.departmentStats.instructors_in_departments.toLocaleString();
                document.getElementById('avg-departments').textContent = data.departmentStats.avg_departments_per_instructor.toLocaleString(undefined, {
                    minimumFractionDigits: 1,
                    maximumFractionDigits: 1
                });
            }

            // Update Top Instructors Table
            if (data.topInstructors && data.topInstructors.length > 0) {
                const tableBody = document.getElementById('top-instructors-table');
                if (tableBody) {
                    tableBody.innerHTML = '';

                    data.topInstructors.forEach(instructor => {
                        const row = document.createElement('tr');
                        const initials = getInitials(instructor.instructor_name);

                        row.innerHTML = `
           <td>
             <div class="d-flex align-items-center">
               <div class="avatar avatar-sm me-3">
                 <div class="avatar-initial rounded-circle bg-label-primary">
                   ${initials}
                 </div>
               </div>
               <div class="d-flex flex-column">
                 <h6 class="mb-0 text-nowrap">${instructor.instructor_name}</h6>
               </div>
             </div>
           </td>
           <td>${parseInt(instructor.course_count).toLocaleString()}</td>
           <td>${parseInt(instructor.student_count).toLocaleString()}</td>
           <td>₵${parseFloat(instructor.total_earnings).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
          <td>
    <div class="d-flex align-items-center">
      <span class="me-1">${parseFloat(instructor.avg_rating || 0).toLocaleString(undefined, {minimumFractionDigits: 1, maximumFractionDigits: 1})}</span>
      <i class="bx bxs-star text-warning"></i>
    </div>
  </td>
         `;
                        tableBody.appendChild(row);
                    });
                }
            }

            // Update charts
            if (data.monthlyCourseData) {
                initializeCourseChart(data.monthlyCourseData);
            }

            if (data.monthlyEarningsData) {
                initializeEarningsChart(data.monthlyEarningsData);
            }

            if (data.departmentDistribution) {
                initializeDepartmentChart(data.departmentDistribution);
            }
        }

        // Helper function to get initials from a name
        function getInitials(name) {
            const initials = name.split(' ');
            return (initials[0] ? initials[0].charAt(0) : '') +
                (initials[1] ? initials[1].charAt(0) : '');
        }

        // Function to fetch filtered data
   function fetchFilteredData(period) {
    showLoadingIndicator();

    fetch('../backend/admin/instructor_reports_filter.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `period=${encodeURIComponent(period)}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text(); // Get raw text first
    })
    .then(text => {
        try {
            const data = JSON.parse(text); // Attempt to parse as JSON
            if (data.error) {
                console.error('Server Error:', data.error);
                return;
            }
            hideLoadingIndicator();
            updateUI(data);
        } catch (e) {
            console.error('Failed to parse JSON:', e);
            console.log('Raw response text:', text); // Log the problematic response
            hideLoadingIndicator();
        }
    })
    .catch(error => {
        hideLoadingIndicator();
        console.error('Fetch error:', error);
    });
}

        // Function to export data as CSV
 function exportData(period, section = 'all_instructors') {
    showLoadingIndicator();

    fetch(`../backend/admin/instructor_reports_filter.php?export=csv&section=${section}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `period=${encodeURIComponent(period)}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
    })
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.error) {
                console.error('Server Error:', data.error);
                hideLoadingIndicator();
                return;
            }
            // Rest of the export logic...
            hideLoadingIndicator();
        } catch (e) {
            console.error('Failed to parse JSON:', e);
            console.log('Raw response text:', text);
            hideLoadingIndicator();
        }
    })
    .catch(error => {
        hideLoadingIndicator();
        console.error('Export error:', error);
    });
}


        // Loading indicator functions
        function showLoadingIndicator() {
            // Check if overlay already exists
            if (document.querySelector('.loading-overlay')) return;

            const overlay = document.createElement('div');
            overlay.className = 'loading-overlay';
            overlay.innerHTML = `
     <div class="spinner-border text-primary" role="status">
       <span class="visually-hidden">Loading...</span>
     </div>
     <div class="loading-text">Loading data...</div>
   `;
            document.body.appendChild(overlay);

            // Add style if not already in document
            if (!document.getElementById('loading-style')) {
                const style = document.createElement('style');
                style.id = 'loading-style';
                style.textContent = `
       .loading-overlay {
         position: fixed;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         background-color: rgba(0, 0, 0, 0.5);
         display: flex;
         flex-direction: column;
         justify-content: center;
         align-items: center;
         z-index: 9999;
       }
       .loading-text {
         color: white;
         margin-top: 10px;
         font-weight: bold;
       }
     `;
                document.head.appendChild(style);
            }
        }

        function hideLoadingIndicator() {
            const overlay = document.querySelector('.loading-overlay');
            if (overlay) {
                overlay.remove();
            }
        }

        // Period Filter Buttons
        let currentPeriod = 'all';
        document.querySelectorAll('[data-period]').forEach(button => {
            button.addEventListener('click', function() {
                document.querySelectorAll('[data-period]').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                currentPeriod = this.getAttribute('data-period');
                fetchFilteredData(currentPeriod);
            });
        });

        // Main Export Report Button
        document.getElementById('export-report').addEventListener('click', () => {
            exportData(currentPeriod, 'all_instructors');
        });

        // Dropdown Export Data Links
        document.querySelectorAll('[data-export]').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const section = this.getAttribute('data-export');
                exportData(currentPeriod, section);
            });
        });

        // Initialize charts
        initializeCourseChart(<?php echo $courseChartData; ?>);
        initializeEarningsChart(<?php echo $earningsChartData; ?>);
        initializeDepartmentChart(<?php echo $departmentDistributionData; ?>);
    });
</script>
<?php include_once '../includes/admin/footer.php'; ?>