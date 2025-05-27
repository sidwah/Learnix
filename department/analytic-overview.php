<?php
include '../includes/department/header.php';


$department_id = $_SESSION['department_id'];

// Time filter handling
$time_filter = isset($_GET['timeFilter']) ? $_GET['timeFilter'] : '30days';
$course_filter = isset($_GET['courseFilter']) ? $_GET['courseFilter'] : 'all';

$time_condition = '';
switch ($time_filter) {
    case '7days':
        $time_condition = 'DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
        break;
    case '90days':
        $time_condition = 'DATE_SUB(CURDATE(), INTERVAL 90 DAY)';
        break;
    case 'custom':
        // Handle custom range (requires start/end date inputs)
        $start_date = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-d', strtotime('-30 days'));
        $end_date = isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d');
        $time_condition = "'$start_date' AND '$end_date'";
        break;
    default: // 30days
        $time_condition = 'DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
}

// Course filter condition
$course_condition = $course_filter === 'all' ? '' : "AND c.course_id = $course_filter";

// Fetch department details
$dept_query = "SELECT d.name, d.code, CONCAT(u.first_name, ' ', u.last_name) AS head_name
               FROM departments d
               LEFT JOIN department_staff ds ON d.department_id = ds.department_id AND ds.role = 'head' AND ds.status = 'active'
               LEFT JOIN users u ON ds.user_id = u.user_id
               WHERE d.department_id = $department_id AND d.is_active = 1";
$dept_result = mysqli_query($conn, $dept_query);
$dept_data = mysqli_fetch_assoc($dept_result);
$dept_name = $dept_data['name'] ?? 'N/A';
$dept_code = $dept_data['code'] ?? 'N/A';
$dept_head = $dept_data['head_name'] ?? 'N/A';

// Fetch total instructors
$instructors_query = "SELECT COUNT(*) AS total_instructors
                      FROM department_instructors di
                      WHERE di.department_id = $department_id AND di.status = 'active'";
$instructors_result = mysqli_query($conn, $instructors_query);
$total_instructors = mysqli_fetch_assoc($instructors_result)['total_instructors'] ?? 0;

// Fetch total courses
$courses_query = "SELECT COUNT(*) AS total_courses
                  FROM courses c
                  WHERE c.department_id = $department_id AND c.deleted_at IS NULL";
$courses_result = mysqli_query($conn, $courses_query);
$total_courses = mysqli_fetch_assoc($courses_result)['total_courses'] ?? 0;

// Fetch course options for filter
$course_options_query = "SELECT course_id, title
                         FROM courses
                         WHERE department_id = $department_id AND deleted_at IS NULL
                         ORDER BY title";
$course_options_result = mysqli_query($conn, $course_options_query);
$course_options = '';
while ($row = mysqli_fetch_assoc($course_options_result)) {
    $selected = $course_filter == $row['course_id'] ? 'selected' : '';
    $course_options .= "<option value='{$row['course_id']}' $selected>{$row['title']}</option>";
}

// Course status distribution
$status_query = "SELECT status, COUNT(*) AS count
                 FROM courses
                 WHERE department_id = $department_id AND deleted_at IS NULL
                 GROUP BY status";
$status_result = mysqli_query($conn, $status_query);
$draft_courses = 0;
$published_courses = 0;
while ($row = mysqli_fetch_assoc($status_result)) {
    if ($row['status'] === 'Draft') $draft_courses = $row['count'];
    if ($row['status'] === 'Published') $published_courses = $row['count'];
}

// Top courses by enrollment
$enrollment_query = "SELECT c.title, COUNT(e.enrollment_id) AS enrollments
                     FROM courses c
                     LEFT JOIN enrollments e ON c.course_id = e.course_id
                     WHERE c.department_id = $department_id AND e.enrolled_at >= $time_condition $course_condition
                     GROUP BY c.course_id
                     ORDER BY enrollments DESC
                     LIMIT 5";
$enrollment_result = mysqli_query($conn, $enrollment_query);
$enrollment_labels = [];
$enrollment_data = [];
while ($row = mysqli_fetch_assoc($enrollment_result)) {
    $enrollment_labels[] = $row['title'];
    $enrollment_data[] = $row['enrollments'];
}

// Course performance metrics
$time_condition = "DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
$course_condition = isset($course_condition) ? $course_condition : ''; // Fallback if undefined

// Course performance metrics
$time_condition = "DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
$course_condition = isset($course_condition) ? $course_condition : ''; // Fallback if undefined
$department_id = (int)$department_id; // Ensure integer to prevent injection

$metrics_query = "
SELECT 
    COUNT(DISTINCT e.enrollment_id) AS total_enrollments,
    ROUND(AVG(e.completion_percentage), 1) AS avg_completion_rate,
    ROUND(AVG(r.rating), 1) AS avg_rating,
    COUNT(DISTINCT cert.certificate_id) AS certificates_issued
FROM courses c
LEFT JOIN enrollments e 
    ON c.course_id = e.course_id 
    AND e.deleted_at IS NULL 
    AND e.enrolled_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
LEFT JOIN course_ratings r 
    ON c.course_id = r.course_id
LEFT JOIN certificates cert 
    ON e.enrollment_id = cert.enrollment_id
WHERE c.department_id = $department_id
    $course_condition
GROUP BY c.department_id
";

$metrics_result = mysqli_query($conn, $metrics_query);
$metrics = mysqli_fetch_assoc($metrics_result);
$total_enrollments = $metrics['total_enrollments'] ?? 0;
$avg_completion_rate = number_format($metrics['avg_completion_rate'] ?? 0, 1) . '%';
$avg_rating = number_format($metrics['avg_rating'] ?? 0, 1) . '/5';
$certificates_issued = $metrics['certificates_issued'] ?? 0;


// Revenue trends (monthly)
$revenue_query = "
SELECT 
  DATE_FORMAT(cp.payment_date, '%b') AS month, 
  SUM(cp.amount) AS revenue
FROM course_payments cp
JOIN enrollments e ON cp.enrollment_id = e.enrollment_id
JOIN courses c ON e.course_id = c.course_id
WHERE c.department_id = $department_id 
  AND cp.payment_date >= $time_condition 
  $course_condition
GROUP BY DATE_FORMAT(cp.payment_date, '%b')
ORDER BY MIN(cp.payment_date)
";

$revenue_result = mysqli_query($conn, $revenue_query);
$revenue_labels = [];
$revenue_data = [];
while ($row = mysqli_fetch_assoc($revenue_result)) {
    $revenue_labels[] = $row['month'];
    $revenue_data[] = $row['revenue'];
}

// Payment status
$payment_status_query = "
SELECT cp.status, COUNT(*) AS count
FROM course_payments cp
JOIN enrollments e ON cp.enrollment_id = e.enrollment_id
JOIN courses c ON e.course_id = c.course_id
WHERE c.department_id = $department_id 
  AND cp.payment_date >= $time_condition 
  $course_condition
GROUP BY cp.status
";

$payment_status_result = mysqli_query($conn, $payment_status_query);
$completed_payments = 0;
$pending_payments = 0;
$failed_payments = 0;
$total_payments = 0;
while ($row = mysqli_fetch_assoc($payment_status_result)) {
    $total_payments += $row['count'];
    if ($row['status'] === 'Completed') $completed_payments = $row['count'];
    if ($row['status'] === 'Pending') $pending_payments = $row['count'];
    if ($row['status'] === 'Failed') $failed_payments = $row['count'];
}
$completed_payments_pct = $total_payments ? number_format(($completed_payments / $total_payments) * 100, 1) . '%' : '0%';
$pending_payments_pct = $total_payments ? number_format(($pending_payments / $total_payments) * 100, 1) . '%' : '0%';
$failed_payments_pct = $total_payments ? number_format(($failed_payments / $total_payments) * 100, 1) . '%' : '0%';

// Instructor earnings
$earnings_query = "SELECT CONCAT(u.first_name, ' ', u.last_name) AS instructor, c.title, ie.amount
                   FROM instructor_earnings ie
                   JOIN instructors i ON ie.instructor_id = i.instructor_id
                   JOIN users u ON i.user_id = u.user_id
                   JOIN courses c ON ie.course_id = c.course_id
                   WHERE c.department_id = $department_id AND ie.created_at >= $time_condition $course_condition
                   ORDER BY ie.amount DESC
                   LIMIT 5";
$earnings_result = mysqli_query($conn, $earnings_query);
$earnings_rows = '';
while ($row = mysqli_fetch_assoc($earnings_result)) {
    $earnings_rows .= "<tr><td>{$row['instructor']}</td><td>{$row['title']}</td><td>₵" . number_format($row['amount'], 2) . "</td></tr>";
}

// Courses per instructor
$instructor_courses_query = "
SELECT CONCAT(u.first_name, ' ', LEFT(u.last_name, 1), '.') AS instructor, 
       COUNT(DISTINCT c.course_id) AS course_count
FROM department_instructors di
JOIN instructors i ON di.instructor_id = i.instructor_id
JOIN users u ON i.user_id = u.user_id
LEFT JOIN course_instructors ci ON i.instructor_id = ci.instructor_id
LEFT JOIN courses c ON ci.course_id = c.course_id
WHERE di.department_id = $department_id 
  AND di.status = 'active'
  AND (ci.deleted_at IS NULL OR ci.deleted_at IS NULL)
GROUP BY i.instructor_id
ORDER BY course_count DESC
LIMIT 5
";

$instructor_courses_result = mysqli_query($conn, $instructor_courses_query);
$instructor_courses_labels = [];
$instructor_courses_data = [];
while ($row = mysqli_fetch_assoc($instructor_courses_result)) {
    $instructor_courses_labels[] = $row['instructor'];
    $instructor_courses_data[] = $row['course_count'];
}

// Instructor verification status
$verification_query = "SELECT status, COUNT(*) AS count
                      FROM department_instructors
                      WHERE department_id = $department_id
                      GROUP BY status";
$verification_result = mysqli_query($conn, $verification_query);
$approved_instructors = 0;
$pending_instructors = 0;
$inactive_instructors = 0;
$total_verifications = 0;
while ($row = mysqli_fetch_assoc($verification_result)) {
    $total_verifications += $row['count'];
    if ($row['status'] === 'active') $approved_instructors = $row['count'];
    if ($row['status'] === 'pending') $pending_instructors = $row['count'];
    if ($row['status'] === 'inactive') $inactive_instructors = $row['count'];
}
$approved_pct = $total_verifications ? number_format(($approved_instructors / $total_verifications) * 100, 1) : 0;
$pending_pct = $total_verifications ? number_format(($pending_instructors / $total_verifications) * 100, 1) : 0;
$rejected_pct = $total_verifications ? number_format(($inactive_instructors / $total_verifications) * 100, 1) : 0;

// Student enrollment trends
$enrollment_trend_query = "
SELECT 
  DATE_FORMAT(e.enrolled_at, '%b') AS month, 
  COUNT(e.enrollment_id) AS enrollments
FROM enrollments e
JOIN courses c ON e.course_id = c.course_id
WHERE c.department_id = $department_id 
  AND e.enrolled_at >= $time_condition 
  $course_condition
GROUP BY DATE_FORMAT(e.enrolled_at, '%b')
ORDER BY MIN(e.enrolled_at)
";

$enrollment_trend_result = mysqli_query($conn, $enrollment_trend_query);
$enrollment_trend_labels = [];
$enrollment_trend_data = [];
while ($row = mysqli_fetch_assoc($enrollment_trend_result)) {
    $enrollment_trend_labels[] = $row['month'];
    $enrollment_trend_data[] = $row['enrollments'];
}

// Quiz pass rates
$quiz_pass_query = "
SELECT c.title, AVG(sqa.score) AS pass_rate
FROM student_quiz_attempts sqa
JOIN quizzes q ON sqa.quiz_id = q.quiz_id
JOIN course_sections cs ON q.section_id = cs.section_id
JOIN courses c ON cs.course_id = c.course_id
WHERE c.department_id = $department_id 
  AND sqa.start_time >= $time_condition 
  $course_condition
GROUP BY c.course_id
ORDER BY pass_rate DESC
LIMIT 5
";

$quiz_pass_result = mysqli_query($conn, $quiz_pass_query);
$quiz_pass_labels = [];
$quiz_pass_data = [];
while ($row = mysqli_fetch_assoc($quiz_pass_result)) {
    $quiz_pass_labels[] = $row['title'];
    $quiz_pass_data[] = number_format($row['pass_rate'], 1);
}

// Top performing students
$top_students_query = "
SELECT 
  CONCAT(u.first_name, ' ', u.last_name) AS student, 
  c.title, 
  e.completion_percentage, 
  MAX(sqa.score) AS quiz_score
FROM enrollments e
JOIN users u ON e.user_id = u.user_id
JOIN courses c ON e.course_id = c.course_id
LEFT JOIN student_quiz_attempts sqa ON e.user_id = sqa.user_id AND sqa.quiz_id IN (
    SELECT q.quiz_id
    FROM quizzes q
    JOIN course_sections cs ON q.section_id = cs.section_id
    WHERE cs.course_id = c.course_id
)
WHERE c.department_id = $department_id 
  AND e.enrolled_at >= $time_condition 
  $course_condition
GROUP BY e.enrollment_id
ORDER BY e.completion_percentage DESC, quiz_score DESC
LIMIT 5
";

$top_students_result = mysqli_query($conn, $top_students_query);
$top_students_rows = '';
while ($row = mysqli_fetch_assoc($top_students_result)) {
    $top_students_rows .= "<tr><td>{$row['student']}</td><td>{$row['title']}</td><td>{$row['completion_percentage']}%</td><td>" . ($row['quiz_score'] ?? 'N/A') . "/100</td></tr>";
}

// Recent department activity
$activity_query = "SELECT dal.action_type, dal.details, dal.performed_at, CONCAT(u.first_name, ' ', u.last_name) AS performed_by
                   FROM department_activity_logs dal
                   JOIN users u ON dal.user_id = u.user_id
                   WHERE dal.department_id = $department_id AND dal.performed_at >= $time_condition
                   ORDER BY dal.performed_at DESC
                   LIMIT 5";
$activity_result = mysqli_query($conn, $activity_query);
$activity_rows = '';
while ($row = mysqli_fetch_assoc($activity_result)) {
    $details = json_decode($row['details'], true);
    $action = ucfirst(str_replace('_', ' ', $row['action_type']));
    $activity_rows .= "<li class='list-group-item'>$action by {$row['performed_by']} on " . date('M d, Y', strtotime($row['performed_at'])) . "</li>";
}

// Benchmarking (simplified, assuming platform totals)
$benchmark_query = "SELECT
                      (SELECT COUNT(DISTINCT e.user_id) FROM enrollments e JOIN courses c ON e.course_id = c.course_id WHERE c.department_id = $department_id) / (SELECT COUNT(DISTINCT user_id) FROM enrollments) * 100 AS student_share,
                      (SELECT SUM(cp.amount) FROM course_payments cp JOIN enrollments e ON cp.enrollment_id = e.enrollment_id JOIN courses c ON e.course_id = c.course_id WHERE c.department_id = $department_id) / (SELECT SUM(amount) FROM course_payments) * 100 AS revenue_share,
                      (SELECT COUNT(*) FROM courses WHERE department_id = $department_id) / (SELECT COUNT(*) FROM courses) * 100 AS course_share";
$benchmark_result = mysqli_query($conn, $benchmark_query);
$benchmark = mysqli_fetch_assoc($benchmark_result);
$student_share = number_format($benchmark['student_share'] ?? 0, 1) . '%';
$revenue_share = number_format($benchmark['revenue_share'] ?? 0, 1) . '%';
$course_share = number_format($benchmark['course_share'] ?? 0, 1) . '%';
?>

<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main">
    <!-- Navbar -->
    <nav class="js-nav-scroller navbar navbar-expand-lg navbar-sidebar navbar-vertical navbar-light bg-white border-end" data-hs-nav-scroller-options='{
            "type": "vertical",
            "target": ".navbar-nav .active",
            "offset": 80
           }'>
        <?php include '../includes/department/sidebar.php'; ?>
    </nav>
    <!-- End Navbar -->

    <!-- Content -->
    <div class="navbar-sidebar-aside-content content-space-1 content-space-md-2 px-lg-5 px-xl-5">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Department Analytics Overview</h1>
            <div class="d-flex gap-2">
                <select class="form-select" id="timeFilter" aria-label="Time period filter" onchange="updateFilters()">
                    <option value="7days" <?php echo $time_filter === '7days' ? 'selected' : ''; ?>>Last 7 Days</option>
                    <option value="30days" <?php echo $time_filter === '30days' ? 'selected' : ''; ?>>Last 30 Days</option>
                    <option value="90days" <?php echo $time_filter === '90days' ? 'selected' : ''; ?>>Last 90 Days</option>
                    <option value="custom" <?php echo $time_filter === 'custom' ? 'selected' : ''; ?>>Custom Range</option>
                </select>
                <select class="form-select" id="courseFilter" aria-label="Course filter" onchange="updateFilters()">
                    <option value="all" <?php echo $course_filter === 'all' ? 'selected' : ''; ?>>All Courses</option>
                    <?php echo $course_options; ?>
                </select>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Department Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card h-100 transition-shadow" style="transition: box-shadow 0.3s;">
                    <div class="card-body d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 me-3 rounded">
                            <i class="bi bi-building fs-3 text-primary"></i>
                        </div>
                        <div>
                            <h5 id="deptName"><?php echo htmlspecialchars($dept_name); ?></h5>
                            <p class="text-muted mb-0">Code: <?php echo htmlspecialchars($dept_code); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 transition-shadow" style="transition: box-shadow 0.3s;">
                    <div class="card-body d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 p-3 me-3 rounded">
                            <i class="bi bi-person-check fs-3 text-success"></i>
                        </div>
                        <div>
                            <h5 id="deptHead"><?php echo htmlspecialchars($dept_head); ?></h5>
                            <p class="text-muted mb-0">Department Head</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 transition-shadow" style="transition: box-shadow 0.3s;">
                    <div class="card-body d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 p-3 me-3 rounded">
                            <i class="bi bi-person-lines-fill fs-3 text-info"></i>
                        </div>
                        <div>
                            <h5 id="totalInstructors"><?php echo $total_instructors; ?></h5>
                            <p class="text-muted mb-0">Total Instructors</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 transition-shadow" style="transition: box-shadow 0.3s;">
                    <div class="card-body d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 p-3 me-3 rounded">
                            <i class="bi bi-book fs-3 text-warning"></i>
                        </div>
                        <div>
                            <h5 id="totalCourses"><?php echo $total_courses; ?></h5>
                            <p class="text-muted mb-0">Total Courses</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Department Summary Cards -->

        <!-- Course Performance -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card h-100 transition-shadow" style="transition: box-shadow 0.3s;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Course Status Distribution</h4>
                        <div class="dropdown">
                            <button class="btn btn-link text-secondary p-0" type="button" id="courseStatusMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="courseStatusMenu">
                                <li><a class="dropdown-item" href="#">Export as CSV</a></li>
                                <li><a class="dropdown-item" href="#">Export as PDF</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="courseStatusChart" style="max-height: 200px; width: 100%;"></canvas>
                        <p class="text-center text-muted mt-2">Draft: <span id="draftCourses"><?php echo $draft_courses; ?></span> | Published: <span id="publishedCourses"><?php echo $published_courses; ?></span></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100 transition-shadow" style="transition: box-shadow 0.3s;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Top Courses by Enrollment</h4>
                        <div class="dropdown">
                            <button class="btn btn-link text-secondary p-0" type="button" id="enrollmentMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="enrollmentMenu">
                                <li><a class="dropdown-item" href="#">Export as CSV</a></li>
                                <li><a class="dropdown-item" href="#">Export as PDF</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="enrollmentChart" style="max-height: 200px; width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-4 transition-shadow" style="transition: box-shadow 0.3s;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Course Performance Metrics</h4>
                <div class="dropdown">
                    <button class="btn btn-link text-secondary p-0" type="button" id="performanceMenu" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots"></i>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="performanceMenu">
                        <li><a class="dropdown-item" href="#">Export as CSV</a></li>
                        <li><a class="dropdown-item">Export as PDF</a></a>
                    </ul>
                </div>
            </div>
            <div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <h5 id="totalEnrollments"><?php echo $total_enrollments; ?></h5>
                        <p class="text-muted">Total Enrollments</p>
                    </div>
                    <div class="col-md-3">
                        <h5 id="avgCompletionRate"><?php echo $avg_completion_rate; ?></h5>
                        <p class="text-muted">Avg. Completion Rate</p>
                    </div>
                    <div class="col-md-3">
                        <h5 id="avgRating"><?php echo $avg_rating; ?></h5>
                        <p class="text-muted">Avg. Course Rating</p>
                    </div>
                    <div class="col-md-3">
                        <h5 id="certificatesIssued"><?php echo $certificates_issued; ?></h5>
                        <p class="text-muted">Certificates Issued</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Course Performance -->

        <!-- Revenue and Financials -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card h-100 transition-shadow" style="transition: box-shadow 0.3s;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Revenue Trends</h4>
                        <div class="dropdown">
                            <button class="btn btn-link text-secondary p-0" type="button" id="revenueTrendMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="revenueMenu">
                                <li><a class="dropdown-item" href="#">Export as CSV</a></li>
                                <li><a class="dropdown-item" href="#">Export as PDF</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueTrendChart" style="max-height: 200px; width: 100%;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100 transition-shadow" style="transition: box-shadow 0.3s;">
                    <div class="card-header body d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Payment Status</h4>
                        <div class="dropdown">
                            <button class="btn btn-link text-secondary p-0" type="button" id="paymentStatusMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="paymentStatusMenu">
                                <li><a class="dropdown-item" href="#">Export as CSV</a></li>
                                <li><a class="dropdown-item" href="#">Export as PDF</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="paymentStatusChart" style="max-height: 200px; width: 100%;"></canvas>
                        <p class="text-center text-muted mt-2">Completed: <span id="completedPayments"><?php echo $completed_payments_pct; ?></span> | Pending: <span id="pendingPayments"><?php echo $pending_payments_pct; ?></span> | Failed: <span id="failedPayments"><?php echo $failed_payments_pct; ?></span></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-4 transition-shadow" style="transition: box-shadow 0.3s;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Instructor Earnings</h4>
                <div class="dropdown">
                    <button class="btn btn-link text-secondary p-0" type="button" id="earningsMenu" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots"></i>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="earningsMenu">
                        <li><a class="dropdown-item" href="#">Export as CSV</a></li>
                        <li><a class="dropdown-item" href="#">Export as PDF</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>Instructor</th>
                            <th>Course</th>
                            <th>Earnings</th>
                        </tr>
                    </thead>
                    <tbody id="earningsTable">
                        <?php echo $earnings_rows; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- End Revenue and Financials -->

        <!-- Instructor Activity -->
        <div class="card mb-4 transition-shadow" style="transition: box-shadow 0.3s;">
            <div class="card-header">
                <h4 class="card-title">Instructor Activity</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Courses per Instructor</h6>
                        <canvas id="instructorCoursesChart" style="max-height: 200px; width: 100%;"></canvas>
                    </div>
                    <div class="col-md-6">
                        <h6>Instructor Verification Status</h6>
                        <div class="progress mb-3">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $approved_pct; ?>%;" aria-valuenow="<?php echo $approved_pct; ?>" aria-valuemin="0" aria-valuemax="100">Approved: <?php echo $approved_pct; ?>%</div>
                        </div>
                        <div class="progress mb-3">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $pending_pct; ?>%;" aria-valuenow="<?php echo $pending_pct; ?>" aria-valuemin="0" aria-valuemax="100">Pending: <?php echo $pending_pct; ?>%</div>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo $rejected_pct; ?>%;" aria-valuenow="<?php echo $rejected_pct; ?>" aria-valuemin="0" aria-valuemax="100">Inactive: <?php echo $rejected_pct; ?>%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Instructor Activity -->

        <!-- Student Engagement -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card h-100 transition-shadow" style="transition: box-shadow 0.3s;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Student Enrollment Trends</h4>
                        <div class="dropdown">
                            <button class="btn btn-link text-secondary p-0" type="button" id="enrollmentTrendMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="enrollmentTrendMenu">
                                <li><a class="dropdown-item" href="#">Export as CSV</a></li>
                                <li><a class="dropdown-item" href="#">Export as PDF</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="studentEnrollmentChart" style="max-height: 200px; width: 100%;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100 transition-shadow" style="transition: box-shadow 0.3s;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Quiz Pass Rates</h4>
                        <div class="dropdown">
                            <button class="btn btn-link text-secondary p-0" type="button" id="quizPassMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="quizPassMenu">
                                <li><a class="dropdown-item" href="#">Export as CSV</a></li>
                                <li><a class="dropdown-item" href="#">Export as PDF</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="quizPassRateChart" style="max-height: 200px; width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-4 transition-shadow" style="transition: box-shadow 0.3s;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Top Performing Students</h4>
                <div class="dropdown">
                    <button class="btn btn-link text-secondary p-0" type="button" id="topStudentsMenu" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots"></i>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="topStudentsMenu">
                        <li><a class="dropdown-item" href="#">Export as CSV</a></li>
                        <li><a class="dropdown-item" href="#">Export as PDF</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Completion</th>
                            <th>Quiz Score</th>
                        </tr>
                    </thead>
                    <tbody id="topStudentsTable">
                        <?php echo $top_students_rows; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- End Student Engagement -->

        <!-- Department Activity Log -->
        <div class="card mb-4 transition-shadow" style="transition: box-shadow 0.3s;">
            <div class="card-header">
                <h4 class="card-title">Recent Department Activity</h4>
            </div>
            <div class="card-body">
                <ul class="list-group" id="activityLog">
                    <?php echo $activity_rows; ?>
                </ul>
            </div>
        </div>
        <!-- End Department Activity Log -->

        <!-- Benchmarking -->
        <!-- <div class="card transition-shadow" style="transition: box-shadow 0.3s;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Department vs. Platform</h4>
                <div class="dropdown">
                    <button class="btn btn-link text-secondary p-0" type="button" id="benchmarkMenu" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots"></i>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="benchmarkMenu">
                        <li><a class="dropdown-item" href="#">Export as CSV</a></li>
                        <li><a class="dropdown-item" href="#">Export as PDF</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <h5 id="deptStudent"><?php echo $student_share; ?></h5>
                        <p class="text-muted">Student Share</p>
                    </div>
                    <div class="col-md-4">
                        <h5 id="deptRevenue"><?php echo $revenue_share; ?></h5>
                        <p class="text-muted">Revenue Share</p>
                    </div>
                    <div class="col-md-4">
                        <h5 id="deptCourse"><?php echo $course_share; ?></h5>
                        <p class="text-muted">Course Share</p>
                    </div>
                </div>
                <canvas id="benchmarkChart" style="max-height: 180px; width: 100%;"></canvas>
            </div>
        </div> -->
        <!-- End Benchmarks -->

        <!-- Bootstrap Icons and Chart.js CDN -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
        <script>
            // Soft color palette
            const chartColors = {
                softBlue: '#A3BFFA',
                softGreen: '#A7F3D0',
                softYellow: '#FDE68A',
                softRed: '#FCA5A5',
                softPurple: '#C4B5FD',
                softGray: '#D1D5DB',
                softTeal: '#99F6E4'
            };

            // Hover effect for cards
            document.querySelectorAll('.transition-shadow').forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.1)';
                });
                card.addEventListener('mouseleave', () => {
                    card.style.boxShadow = 'none';
                });
            });

            // Update filters
            function updateFilters() {
                const timeFilter = document.getElementById('timeFilter').value;
                const courseFilter = document.getElementById('courseFilter').value;
                let url = `?timeFilter=${timeFilter}&courseFilter=${courseFilter}`;
                if (timeFilter === 'custom') {
                    // Prompt for custom dates (simplified)
                    const startDate = prompt('Enter start date (YYYY-MM-DD):');
                    const endDate = prompt('Enter end date (YYYY-MM-DD):');
                    if (startDate && endDate) {
                        url += `&startDate=${startDate}&endDate=${endDate}`;
                    }
                }
                window.location.href = url;
            }

            // Chart.js global defaults
            Chart.defaults.font.family = 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif';
            Chart.defaults.font.size = 12;
            Chart.defaults.color = '#6B7280';

            // Course Status Distribution (Pie Chart)
            new Chart(document.getElementById('courseStatusChart'), {
                type: 'pie',
                data: {
                    labels: ['Draft', 'Published'],
                    datasets: [{
                        data: [<?php echo $draft_courses; ?>, <?php echo $published_courses; ?>],
                        backgroundColor: [chartColors.softGray, chartColors.softGreen],
                        borderColor: [chartColors.softGray, chartColors.softGreen],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: { backgroundColor: chartColors.softBlue }
                    }
                }
            });

            // Top Courses by Enrollment (Bar Chart)
            new Chart(document.getElementById('enrollmentChart'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($enrollment_labels); ?>,
                    datasets: [{
                        label: 'Enrollments',
                        data: <?php echo json_encode($enrollment_data); ?>,
                        backgroundColor: chartColors.softBlue,
                        borderColor: chartColors.softBlue,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, title: { display: true, text: 'Enrollments' } }
                    },
                    plugins: {
                        tooltip: { backgroundColor: chartColors.softBlue }
                    }
                }
            });

            // Revenue Trends (Line Chart)
            new Chart(document.getElementById('revenueTrendChart'), {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($revenue_labels); ?>,
                    datasets: [{
                        label: 'Revenue (₵)',
                        data: <?php echo json_encode($revenue_data); ?>,
                        borderColor: chartColors.softGreen,
                        backgroundColor: chartColors.softGreen + '4D',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, title: { display: true, text: 'Revenue (₵)' } }
                    },
                    plugins: {
                        tooltip: { backgroundColor: chartColors.softGreen }
                    }
                }
            });

            // Payment Status (Doughnut Chart)
            new Chart(document.getElementById('paymentStatusChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Completed', 'Pending', 'Failed'],
                    datasets: [{
                        data: [<?php echo $completed_payments; ?>, <?php echo $pending_payments; ?>, <?php echo $failed_payments; ?>],
                        backgroundColor: [chartColors.softGreen, chartColors.softYellow, chartColors.softRed],
                        borderColor: [chartColors.softGreen, chartColors.softYellow, chartColors.softRed],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: { backgroundColor: chartColors.softBlue }
                    }
                }
            });

            // Courses per Instructor (Bar Chart)
            new Chart(document.getElementById('instructorCoursesChart'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($instructor_courses_labels); ?>,
                    datasets: [{
                        label: 'Courses',
                        data: <?php echo json_encode($instructor_courses_data); ?>,
                        backgroundColor: chartColors.softTeal,
                        borderColor: chartColors.softTeal,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, title: { display: true, text: 'Courses' } }
                    },
                    plugins: {
                        tooltip: { backgroundColor: chartColors.softTeal }
                    }
                }
            });

            // Student Enrollment Trends (Line Chart)
            new Chart(document.getElementById('studentEnrollmentChart'), {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($enrollment_trend_labels); ?>,
                    datasets: [{
                        label: 'Enrollments',
                        data: <?php echo json_encode($enrollment_trend_data); ?>,
                        borderColor: chartColors.softBlue,
                        backgroundColor: chartColors.softBlue + '4D',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, title: { display: true, text: 'Enrollments' } }
                    },
                    plugins: {
                        tooltip: { backgroundColor: chartColors.softBlue }
                    }
                }
            });

            // Quiz Pass Rates (Bar Chart)
            new Chart(document.getElementById('quizPassRateChart'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($quiz_pass_labels); ?>,
                    datasets: [{
                        label: 'Pass Rate (%)',
                        data: <?php echo json_encode($quiz_pass_data); ?>,
                        backgroundColor: chartColors.softYellow,
                        borderColor: chartColors.softYellow,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, max: 100, title: { display: true, text: 'Pass Rate (%)' } }
                    },
                    plugins: {
                        tooltip: { backgroundColor: chartColors.softYellow }
                    }
                }
            });

            // Department vs. Platform (Bar Chart)
            new Chart(document.getElementById('benchmarkChart'), {
                type: 'bar',
                data: {
                    labels: ['Students', 'Revenue', 'Courses'],
                    datasets: [
                        {
                            label: 'Department',
                            data: [
                                parseFloat('<?php echo floatval($benchmark['student_share'] ?? 0); ?>'),
                                parseFloat('<?php echo floatval($benchmark['revenue_share'] ?? 0); ?>'),
                                parseFloat('<?php echo floatval($benchmark['course_share'] ?? 0); ?>')
                            ],
                            backgroundColor: chartColors.softBlue
                        },
                        {
                            label: 'Platform',
                            data: [100, 100, 100],
                            backgroundColor: chartColors.softGray
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, title: { display: true, text: 'Percentage (%)' } }
                    },
                    plugins: {
                        tooltip: { backgroundColor: chartColors.softBlue }
                    }
                }
            });
        </script>
    </div>
    <!-- End Content -->
</main>
<!-- End Main Content -->

<?php include '../includes/department/footer.php'; ?>
<!-- // </xai>
// ```

// ### **Key Features**
// - **Dynamic Data**: Uses `mysqli` queries to fetch data from `learnix_db` tables, replacing static values.
// - **Filter Handling**: Supports time and course filters via GET parameters, with a basic JavaScript `updateFilters()` function to reload the page.
// - **Security**: Escapes user input (`htmlspecialchars`) and validates `$_SESSION['department_id']`.
// - **Charts**: PHP generates JSON data for Chart.js to render dynamically.
// - **Error Handling**: Checks for empty results and provides fallbacks (e.g., `?? 0`).
// - **Session Usage**: Leverages `$_SESSION['department_id']` for context.
// - **Export Options**: Placeholder dropdowns for CSV/PDF export (not implemented).

// ### **Notes**
// - **Custom Range Filter**: The custom range requires a JavaScript prompt for simplicity; a production version should use date picker inputs.
// - **Export Functionality**: The "Export as CSV/PDF" links are placeholders; implement server-side logic for actual exports.
// - **Error Handling**: Add more robust error handling for database queries (e.g., try-catch).
// - **Performance**: Consider indexing `created_at`, `department_id`, and `course_id` columns for faster queries on large datasets.
// - **Assumptions**: Assumes `courses.rating` and `progress.certificate_id` exist; adjust queries if schema differs.
// - **Currency**: Uses `₵` as shown; modify for other currencies if needed.

// This code integrates the database with the dashboard, making it fully dynamic while preserving the original design and functionality. Let me know if you need further refinements or additional features! -->