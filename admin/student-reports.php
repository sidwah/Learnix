<?php
// Authentication check
require_once '../backend/auth/admin/admin-auth-check.php';

// Set page title
$pageTitle = "Students Reports - Admin | Learnix";

include_once '../includes/admin/header.php';
include_once '../includes/admin/sidebar.php';
include_once '../includes/admin/navbar.php';

// Get data from database
require_once '../backend/config.php';

// Get current date
$currentDate = date('Y-m-d');

// Total students
$studentQuery = "SELECT
    COUNT(*) as total_students,
    SUM(CASE WHEN MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE()) THEN 1 ELSE 0 END) as new_students_month,
    (SELECT COUNT(DISTINCT user_id) 
     FROM user_activity_logs 
     WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) 
     AND user_id IN (SELECT user_id FROM users WHERE role = 'student' AND deleted_at IS NULL)) as active_students
FROM users
WHERE role = 'student' AND deleted_at IS NULL";
$studentResult = mysqli_query($conn, $studentQuery);
$studentStats = mysqli_fetch_assoc($studentResult);

// Enrollments
$enrollmentQuery = "SELECT
    COUNT(*) as total_enrollments,
    SUM(CASE WHEN e.status = 'Active' THEN 1 ELSE 0 END) as active_enrollments,
    SUM(CASE WHEN e.status = 'Completed' THEN 1 ELSE 0 END) as completed_enrollments,
    SUM(CASE WHEN DATE(e.enrolled_at) = CURRENT_DATE() THEN 1 ELSE 0 END) as enrollments_today,
    SUM(CASE WHEN DATE(e.enrolled_at) >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as enrollments_week,
    SUM(CASE WHEN DATE(e.enrolled_at) >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as enrollments_month
FROM enrollments e
JOIN users u ON e.user_id = u.user_id
WHERE u.role = 'student' AND e.deleted_at IS NULL AND u.deleted_at IS NULL";
$enrollmentResult = mysqli_query($conn, $enrollmentQuery);
$enrollmentStats = mysqli_fetch_assoc($enrollmentResult);

// Completion rate
$completionRateQuery = "SELECT 
    AVG(completion_percentage) as avg_completion_percentage,
    SUM(CASE WHEN completion_percentage = 100 THEN 1 ELSE 0 END) as fully_completed,
    COUNT(*) as total_enrollments
FROM enrollments e
JOIN users u ON e.user_id = u.user_id
WHERE u.role = 'student' AND e.deleted_at IS NULL AND u.deleted_at IS NULL";
$completionRateResult = mysqli_query($conn, $completionRateQuery);
$completionRateStats = mysqli_fetch_assoc($completionRateResult);

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
) AND e.deleted_at IS NULL";
$retentionResult = mysqli_query($conn, $retentionQuery);
$retentionStats = mysqli_fetch_assoc($retentionResult);
$retentionRate = ($retentionStats['returning_students'] / max(1, $retentionStats['total_enrolled_students'])) * 100;

// Average completion time
$completionTimeQuery = "SELECT 
    AVG(DATEDIFF(
        CASE WHEN e.status = 'Completed' THEN e.last_accessed ELSE CURRENT_DATE() END,
        e.enrolled_at
    )) as avg_completion_days
FROM enrollments e
WHERE e.deleted_at IS NULL AND e.completion_percentage > 0";
$completionTimeResult = mysqli_query($conn, $completionTimeQuery);
$completionTimeStats = mysqli_fetch_assoc($completionTimeResult);

// Top students by completions
$topStudentsQuery = "SELECT 
    u.user_id, CONCAT(u.first_name, ' ', u.last_name) as student_name,
    COUNT(e.enrollment_id) as enrollment_count,
    SUM(CASE WHEN e.completion_percentage = 100 THEN 1 ELSE 0 END) as completed_courses,
    AVG(e.completion_percentage) as avg_completion
FROM users u
LEFT JOIN enrollments e ON u.user_id = e.user_id AND e.deleted_at IS NULL
WHERE u.role = 'student' AND u.deleted_at IS NULL
GROUP BY u.user_id
ORDER BY completed_courses DESC, avg_completion DESC
LIMIT 5";
$topStudentsResult = mysqli_query($conn, $topStudentsQuery);
$topStudents = [];
while ($student = mysqli_fetch_assoc($topStudentsResult)) {
    $topStudents[] = $student;
}

// Monthly student enrollment data for chart
$monthlyEnrollmentQuery = "SELECT 
    DATE_FORMAT(e.enrolled_at, '%Y-%m') as month,
    COUNT(*) as enrollment_count
FROM enrollments e
JOIN users u ON e.user_id = u.user_id
WHERE u.role = 'student' AND e.deleted_at IS NULL AND u.deleted_at IS NULL
AND e.enrolled_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 11 MONTH)
GROUP BY DATE_FORMAT(e.enrolled_at, '%Y-%m')
ORDER BY month ASC";
$monthlyEnrollmentResult = mysqli_query($conn, $monthlyEnrollmentQuery);
$monthlyEnrollmentData = [];
while ($month = mysqli_fetch_assoc($monthlyEnrollmentResult)) {
    $monthlyEnrollmentData[] = $month;
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
GROUP BY c.category_id
ORDER BY enrollment_count DESC
LIMIT 5";
$categoryPreferencesResult = mysqli_query($conn, $categoryPreferencesQuery);
$categoryPreferences = [];
while ($category = mysqli_fetch_assoc($categoryPreferencesResult)) {
    $categoryPreferences[] = $category;
}

// Convert data to JSON for charts
$enrollmentChartData = json_encode($monthlyEnrollmentData);
$categoryPreferencesData = json_encode($categoryPreferences);
?>

<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Admin /</span> Students Reports
  </h4>

  <!-- Report Date Range -->
  <div class="card mb-4">
    <div class="card-body">
      <div class="row align-items-center">
        <div class="col-md-6">
          <h5 class="mb-1">Student Performance Overview</h5>
          <p class="text-muted mb-md-0">Key student metrics as of <?php echo date('F d, Y'); ?></p>
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
    <!-- Students Stats -->
    <div class="col-xl-4 col-md-6 mb-4">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title m-0 me-2">Students</h5>
          <div class="dropdown">
            <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
              <a class="dropdown-item" href="../admin/learners.php">View All Students</a>
              <a class="dropdown-item" href="#" data-export="students">Export Data</a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex flex-column align-items-start gap-1">
              <h2 class="mb-0" id="total-students"><?php echo number_format($studentStats['total_students']); ?></h2>
              <span class="text-muted">Total Students</span>
            </div>
            <div class="avatar me-1">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="bx bx-user-circle bx-md"></i>
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
                  <p class="mb-0">Active Students</p>
                </div>
                <div>
                  <h5 class="mb-0" id="active-students"><?php echo number_format($studentStats['active_students']); ?></h5>
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
                  <h5 class="mb-0" id="new-students"><?php echo number_format($studentStats['new_students_month']); ?></h5>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Enrollment Stats -->
    <div class="col-xl-4 col-md-6 mb-4">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title m-0 me-2">Enrollments</h5>
          <div class="dropdown">
            <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
              <a class="dropdown-item" href="#">View Enrollment Report</a>
              <a class="dropdown-item" href="#" data-export="student_enrollments">Export Data</a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex flex-column align-items-start gap-1">
              <h2 class="mb-0" id="total-enrollments"><?php echo number_format($enrollmentStats['total_enrollments']); ?></h2>
              <span class="text-muted">Total Enrollments</span>
            </div>
            <div class="avatar me-1">
              <span class="avatar-initial rounded bg-label-success">
                <i class="bx bx-user-plus bx-md"></i>
              </span>
            </div>
          </div>
          <ul class="p-0 m-0">
            <li class="d-flex mb-2">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="bx bx-time"></i>
                </span>
              </div>
              <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                <div class="me-2">
                  <p class="mb-0">Today</p>
                </div>
                <div>
                  <h5 class="mb-0" id="enrollments-today"><?php echo number_format($enrollmentStats['enrollments_today']); ?></h5>
                </div>
              </div>
            </li>
            <li class="d-flex mb-2">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-success">
                  <i class="bx bx-calendar-week"></i>
                </span>
              </div>
              <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                <div class="me-2">
                  <p class="mb-0">This Week</p>
                </div>
                <div>
                  <h5 class="mb-0" id="enrollments-week"><?php echo number_format($enrollmentStats['enrollments_week']); ?></h5>
                </div>
              </div>
            </li>
            <li class="d-flex mb-2">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-warning">
                  <i class="bx bx-calendar"></i>
                </span>
              </div>
              <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                <div class="me-2">
                  <p class="mb-0">This Month</p>
                </div>
                <div>
                  <h5 class="mb-0" id="enrollments-month"><?php echo number_format($enrollmentStats['enrollments_month']); ?></h5>
                </div>
              </div>
            </li>
          </ul>
          <div class="d-flex justify-content-between mt-3">
            <p class="mb-0">Active Enrollments</p>
            <p class="mb-0 text-success" id="active-enrollments-percent">
              <?php echo number_format($enrollmentStats['active_enrollments']); ?> 
              (<?php echo round(($enrollmentStats['active_enrollments'] / max(1, $enrollmentStats['total_enrollments'])) * 100); ?>%)
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Completion Stats -->
    <div class="col-xl-4 col-md-6 mb-4">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title m-0 me-2">Course Completion</h5>
          <div class="dropdown">
            <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
              <a class="dropdown-item" href="#">View Completion Report</a>
              <a class="dropdown-item" href="#" data-export="completion">Export Data</a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex flex-column align-items-start gap-1">
              <h2 class="mb-0" id="avg-completion"><?php echo round($completionRateStats['avg_completion_percentage']); ?>%</h2>
              <span class="text-muted">Avg. Completion Rate</span>
            </div>
            <div class="avatar me-1">
              <span class="avatar-initial rounded bg-label-info">
                <i class="bx bx-check-circle bx-md"></i>
              </span>
            </div>
          </div>
          <ul class="p-0 m-0">
            <li class="d-flex mb-2">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-success">
                  <i class="bx bx-trophy"></i>
                </span>
              </div>
              <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                <div class="me-2">
                  <p class="mb-0">Fully Completed</p>
                </div>
                <div>
                  <h5 class="mb-0" id="fully-completed"><?php echo number_format($completionRateStats['fully_completed']); ?></h5>
                </div>
              </div>
            </li>
            <li class="d-flex mb-2">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="bx bx-book"></i>
                </span>
              </div>
              <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                <div class="me-2">
                  <p class="mb-0">Total Enrollments</p>
                </div>
                <div>
                  <h5 class="mb-0" id="completions-total"><?php echo number_format($completionRateStats['total_enrollments']); ?></h5>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- Second Row - Student Engagement -->
  <div class="row">
    <!-- Student Engagement Metrics -->
    <div class="col-xl-4 col-md-6 mb-4">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title m-0 me-2">Student Engagement</h5>
          <div class="dropdown">
            <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
              <a class="dropdown-item" href="#">View Engagement Report</a>
              <a class="dropdown-item" href="#" data-export="engagement">Export Data</a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex flex-column align-items-start gap-1">
              <h2 class="mb-0" id="student-retention-rate"><?php echo round($retentionRate); ?>%</h2>
              <span class="text-muted">Student Retention Rate</span>
            </div>
            <div class="avatar me-1">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="bx bx-line-chart bx-md"></i>
              </span>
            </div>
          </div>
          <ul class="p-0 m-0">
            <li class="d-flex mb-2">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-info">
                  <i class="bx bx-calendar-check"></i>
                </span>
              </div>
              <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                <div class="me-2">
                  <p class="mb-0">Avg. Completion Time</p>
                </div>
                <div>
                  <h5 class="mb-0" id="avg-completion-time"><?php echo round($completionTimeStats['avg_completion_days']); ?> days</h5>
                </div>
              </div>
            </li>
            <li class="d-flex mb-2">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-success">
                  <i class="bx bx-user-voice"></i>
                </span>
              </div>
              <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                <div class="me-2">
                  <p class="mb-0">Return Students</p>
                </div>
                <div>
                  <h5 class="mb-0" id="returning-students"><?php echo number_format($retentionStats['returning_students']); ?></h5>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Monthly Course Enrollments -->
    <div class="col-xl-8 mb-4">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title m-0 me-2">Student Enrollment Trends</h5>
          <div class="dropdown">
            <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
              <a class="dropdown-item" href="#">View Detailed Report</a>
              <a class="dropdown-item" href="#" data-export="enrollment_trends">Export Data</a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <canvas id="enrollmentTrendsChart" height="300"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Charts and Tables -->
  <div class="row">
    <!-- Top Students -->
    <div class="col-lg-6 mb-4">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title m-0 me-2">Top Performing Students</h5>
          <div class="dropdown">
            <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
              <a class="dropdown-item" href="../admin/learners.php">View All Students</a>
              <a class="dropdown-item" href="#" data-export="top_students">Export Data</a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Student</th>
                  <th>Enrollments</th>
                  <th>Completed</th>
                  <th>Avg. Completion</th>
                </tr>
              </thead>
              <tbody id="top-students-table">
                <?php foreach ($topStudents as $student): ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="avatar avatar-sm me-3">
                        <div class="avatar-initial rounded-circle bg-label-primary">
                          <?php 
                            $initials = explode(' ', $student['student_name']);
                            echo substr($initials[0], 0, 1) . (isset($initials[1]) ? substr($initials[1], 0, 1) : '');
                          ?>
                        </div>
                      </div>
                      <div class="d-flex flex-column">
                        <h6 class="mb-0 text-nowrap"><?php echo htmlspecialchars($student['student_name']); ?></h6>
                      </div>
                    </div>
                  </td>
                  <td><?php echo number_format($student['enrollment_count']); ?></td>
                  <td><?php echo number_format($student['completed_courses']); ?></td>
                  <td><?php echo round($student['avg_completion']); ?>%</td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Popular Categories -->
    <div class="col-lg-6 mb-4">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title m-0 me-2">Popular Course Categories</h5>
          <div class="dropdown">
            <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
              <a class="dropdown-item" href="../admin/categories.php">View All Categories</a>
              <a class="dropdown-item" href="#" data-export="category_preferences">Export Data</a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <canvas id="categoryPreferencesChart" height="300"></canvas>
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
 let enrollmentChart, categoryChart;

 // Function to initialize or update enrollment chart
 function initializeEnrollmentChart(enrollmentData) {
   const monthLabels = [];
   const enrollmentCounts = [];

   // Process enrollment data
   enrollmentData.forEach(item => {
     const date = new Date(item.month + '-01');
     monthLabels.push(date.toLocaleDateString('default', { month: 'short', year: 'numeric' }));
     enrollmentCounts.push(parseInt(item.enrollment_count));
   });

   // Destroy existing chart if it exists
   if (enrollmentChart) enrollmentChart.destroy();

   // Enrollment Trends Chart
   const enrollmentCtx = document.getElementById('enrollmentTrendsChart').getContext('2d');
   enrollmentChart = new Chart(enrollmentCtx, {
     type: 'line',
     data: {
       labels: monthLabels,
       datasets: [{
         label: 'Student Enrollments',
         data: enrollmentCounts,
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
         y: { beginAtZero: true, grid: { color: 'rgba(0, 0, 0, 0.1)' } },
         x: { grid: { display: false } }
       },
       plugins: {
         legend: { display: true, position: 'top' },
         tooltip: { mode: 'index', intersect: false }
       }
     }
   });
 }

 // Function to initialize or update category chart
 function initializeCategoryChart(categoryData) {
   const categoryLabels = [];
   const enrollmentCounts = [];
   const backgroundColors = [
     'rgba(105, 108, 255, 0.7)',
     'rgba(40, 167, 69, 0.7)',
     'rgba(255, 193, 7, 0.7)',
     'rgba(220, 53, 69, 0.7)',
     'rgba(23, 162, 184, 0.7)'
   ];

   // Process category data
   categoryData.forEach((item, index) => {
     categoryLabels.push(item.category_name);
     enrollmentCounts.push(parseInt(item.enrollment_count));
   });

   // Destroy existing chart if it exists
   if (categoryChart) categoryChart.destroy();

   // Category Preferences Chart
   const categoryCtx = document.getElementById('categoryPreferencesChart').getContext('2d');
   categoryChart = new Chart(categoryCtx, {
     type: 'doughnut',
     data: {
       labels: categoryLabels,
       datasets: [{
         data: enrollmentCounts,
         backgroundColor: backgroundColors,
         borderWidth: 1
       }]
     },
     options: {
       responsive: true,
       maintainAspectRatio: false,
       plugins: {
         legend: {
           display: true,
           position: 'right',
           labels: {
             boxWidth: 15,
             padding: 15
           }
         },
         tooltip: {
           callbacks: {
             label: function(context) {
               let value = context.raw;
               let total = context.dataset.data.reduce((a, b) => a + b, 0);
               let percentage = Math.round((value / total) * 100);
               return `${context.label}: ${value.toLocaleString()} (${percentage}%)`;
             }
           }
         }
       },
       cutout: '60%'
     }
   });
 }

 // Function to update UI with new data
 function updateUI(data) {
   // Update Students Stats
   if (data.studentStats) {
     document.getElementById('total-students').textContent = data.studentStats.total_students.toLocaleString();
     document.getElementById('active-students').textContent = data.studentStats.active_students.toLocaleString();
     document.getElementById('new-students').textContent = data.studentStats.new_students_month.toLocaleString();
   }

   // Update Enrollments Stats
   if (data.enrollmentStats) {
     document.getElementById('total-enrollments').textContent = data.enrollmentStats.total_enrollments.toLocaleString();
     document.getElementById('enrollments-today').textContent = data.enrollmentStats.enrollments_today.toLocaleString();
     document.getElementById('enrollments-week').textContent = data.enrollmentStats.enrollments_week.toLocaleString();
     document.getElementById('enrollments-month').textContent = data.enrollmentStats.enrollments_month.toLocaleString();

     const activePercentage = Math.round((data.enrollmentStats.active_enrollments / Math.max(1, data.enrollmentStats.total_enrollments)) * 100);
     document.getElementById('active-enrollments-percent').textContent = 
       `${data.enrollmentStats.active_enrollments.toLocaleString()} (${activePercentage}%)`;
   }

   // Update Completion Stats
   if (data.completionRateStats) {
     document.getElementById('avg-completion').textContent = `${Math.round(data.completionRateStats.avg_completion_percentage)}%`;
     document.getElementById('fully-completed').textContent = data.completionRateStats.fully_completed.toLocaleString();
     document.getElementById('completions-total').textContent = data.completionRateStats.total_enrollments.toLocaleString();
   }

   // Update Engagement Stats
   if (data.retentionStats) {
     document.getElementById('student-retention-rate').textContent = `${Math.round(data.retentionStats.retention_rate)}%`;
     document.getElementById('returning-students').textContent = data.retentionStats.returning_students.toLocaleString();
     if (data.completionTimeStats) {
       document.getElementById('avg-completion-time').textContent = `${Math.round(data.completionTimeStats.avg_completion_days)} days`;
     }
   }

   // Update Top Students Table
   if (data.topStudents && data.topStudents.length > 0) {
     const tableBody = document.getElementById('top-students-table');
     if (tableBody) {
       tableBody.innerHTML = '';
       
       data.topStudents.forEach(student => {
         const row = document.createElement('tr');
         const initials = getInitials(student.student_name);
         
         row.innerHTML = `
           <td>
             <div class="d-flex align-items-center">
               <div class="avatar avatar-sm me-3">
                 <div class="avatar-initial rounded-circle bg-label-primary">
                   ${initials}
                 </div>
               </div>
               <div class="d-flex flex-column">
                 <h6 class="mb-0 text-nowrap">${student.student_name}</h6>
               </div>
             </div>
           </td>
           <td>${parseInt(student.enrollment_count).toLocaleString()}</td>
           <td>${parseInt(student.completed_courses).toLocaleString()}</td>
           <td>${Math.round(student.avg_completion)}%</td>
         `;
         tableBody.appendChild(row);
       });
     }
   }

   // Update charts
   if (data.monthlyEnrollmentData) {
     initializeEnrollmentChart(data.monthlyEnrollmentData);
   }
   
   if (data.categoryPreferences) {
     initializeCategoryChart(data.categoryPreferences);
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
   
   fetch('../backend/admin/student_reports_filter.php', {
     method: 'POST',
     headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
     body: `period=${encodeURIComponent(period)}`
   })
   .then(response => response.json())
   .then(data => {
     hideLoadingIndicator();
     
     if (data.error) {
       console.error('Error:', data.error);
       return;
     }
     
     updateUI(data);
   })
   .catch(error => {
     hideLoadingIndicator();
     console.error('Fetch error:', error);
   });
 }

 // Function to export data as CSV
 function exportData(period, section = 'all_students') {
   showLoadingIndicator();
   
   fetch(`../backend/admin/student_reports_filter.php?export=csv&section=${section}`, {
     method: 'POST',
     headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
     body: `period=${encodeURIComponent(period)}`
   })
   .then(response => response.json())
   .then(data => {
     hideLoadingIndicator();
     
     if (data.error) {
       console.error('Error:', data.error);
       return;
     }

     // Create CSV content
     let csv = '';
     let filename = '';
     
     switch(section) {
       case 'students':
         csv = 'Metric,Value\n';
         csv += `Total Students,${data.studentStats.total_students}\n`;
         csv += `Active Students,${data.studentStats.active_students}\n`;
         csv += `New Students This Month,${data.studentStats.new_students_month}\n`;
         filename = 'students_stats';
         break;
         
       case 'student_enrollments':
         csv = 'Metric,Value\n';
         csv += `Total Enrollments,${data.enrollmentStats.total_enrollments}\n`;
         csv += `Active Enrollments,${data.enrollmentStats.active_enrollments}\n`;
         csv += `Completed Enrollments,${data.enrollmentStats.completed_enrollments}\n`;
         csv += `Enrollments Today,${data.enrollmentStats.enrollments_today}\n`;
         csv += `Enrollments This Week,${data.enrollmentStats.enrollments_week}\n`;
         csv += `Enrollments This Month,${data.enrollmentStats.enrollments_month}\n`;
         filename = 'enrollment_stats';
         break;
         
       case 'completion':
         csv = 'Metric,Value\n';
         csv += `Average Completion Rate,${Math.round(data.completionRateStats.avg_completion_percentage)}%\n`;
         csv += `Fully Completed Courses,${data.completionRateStats.fully_completed}\n`;
         csv += `Total Enrollments,${data.completionRateStats.total_enrollments}\n`;
         filename = 'completion_stats';
         break;
         
       case 'engagement':
         csv = 'Metric,Value\n';
         csv += `Student Retention Rate,${Math.round(data.retentionStats.retention_rate)}%\n`;
         csv += `Returning Students,${data.retentionStats.returning_students}\n`;
         csv += `Total Enrolled Students,${data.retentionStats.total_enrolled_students}\n`;
         csv += `Average Completion Time,${Math.round(data.completionTimeStats.avg_completion_days)} days\n`;
         filename = 'engagement_stats';
         break;
         
       case 'top_students':
         csv = 'Student Name,Enrollments,Completed Courses,Average Completion Rate\n';
         data.topStudents.forEach(student => {
           csv += `"${student.student_name}",${student.enrollment_count},${student.completed_courses},${Math.round(student.avg_completion)}%\n`;
         });
         filename = 'top_students';
         break;
         
       case 'enrollment_trends':
         csv = 'Month,Enrollment Count\n';
         data.monthlyEnrollmentData.forEach(item => {
           csv += `${item.month},${item.enrollment_count}\n`;
         });
         filename = 'enrollment_trends';
         break;
         
       case 'category_preferences':
         csv = 'Category Name,Enrollment Count\n';
         data.categoryPreferences.forEach(item => {
           csv += `"${item.category_name}",${item.enrollment_count}\n`;
         });
         filename = 'category_preferences';
         break;
         
       case 'all_students':
       default:
         csv = 'Category,Metric,Value\n';
         // Students
         csv += `Students,Total Students,${data.studentStats.total_students}\n`;
         csv += `Students,Active Students,${data.studentStats.active_students}\n`;
         csv += `Students,New Students This Month,${data.studentStats.new_students_month}\n`;
         
         // Enrollments
         csv += `Enrollments,Total Enrollments,${data.enrollmentStats.total_enrollments}\n`;
         csv += `Enrollments,Active Enrollments,${data.enrollmentStats.active_enrollments}\n`;
         csv += `Enrollments,Completed Enrollments,${data.enrollmentStats.completed_enrollments}\n`;
         csv += `Enrollments,Enrollments Today,${data.enrollmentStats.enrollments_today}\n`;
         csv += `Enrollments,Enrollments This Week,${data.enrollmentStats.enrollments_week}\n`;
         csv += `Enrollments,Enrollments This Month,${data.enrollmentStats.enrollments_month}\n`;
         
         // Completion
         csv += `Completion,Average Completion Rate,${Math.round(data.completionRateStats.avg_completion_percentage)}%\n`;
         csv += `Completion,Fully Completed Courses,${data.completionRateStats.fully_completed}\n`;
         csv += `Completion,Total Enrollments,${data.completionRateStats.total_enrollments}\n`;
         
         // Engagement
         csv += `Engagement,Student Retention Rate,${Math.round(data.retentionStats.retention_rate)}%\n`;
         csv += `Engagement,Returning Students,${data.retentionStats.returning_students}\n`;
         csv += `Engagement,Average Completion Time,${Math.round(data.completionTimeStats.avg_completion_days)} days\n`;
         
         filename = 'student_reports_complete';
         break;
     }

     // Create and trigger download
     const blob = new Blob([csv], { type: 'text/csv' });
     const url = window.URL.createObjectURL(blob);
     const a = document.createElement('a');
     a.setAttribute('href', url);
     a.setAttribute('download', `${filename}_${period}_${new Date().toISOString().split('T')[0]}.csv`);
     a.click();
     window.URL.revokeObjectURL(url);
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
   exportData(currentPeriod, 'all_students');
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
 initializeEnrollmentChart(<?php echo $enrollmentChartData; ?>);
 initializeCategoryChart(<?php echo $categoryPreferencesData; ?>);
});
</script>

<?php include_once '../includes/admin/footer.php'; ?>