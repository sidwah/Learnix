<?php
// Authentication check
require_once '../backend/auth/admin/admin-auth-check.php';

// Set page title
$pageTitle = "Reports Overview - Admin | Learnix";

include_once '../includes/admin/header.php';

// <!-- Menu -->
include_once '../includes/admin/sidebar.php';
// <!-- / Menu -->

// <!-- Navbar -->
include_once '../includes/admin/navbar.php';
// <!-- / Navbar -->

// Get data from database
require_once '../backend/config.php';

// Get current date
$currentDate = date('Y-m-d');
$currentMonth = date('Y-m');
$currentYear = date('Y');

// Get revenue settings
$revenueSettingsQuery = "SELECT setting_name, setting_value FROM revenue_settings";
$revenueSettingsResult = mysqli_query($conn, $revenueSettingsQuery);
$revenueSettings = [];
while ($setting = mysqli_fetch_assoc($revenueSettingsResult)) {
    $revenueSettings[$setting['setting_name']] = $setting['setting_value'];
}

// Total users
$userQuery = "SELECT 
                COUNT(*) as total_users,
                SUM(CASE WHEN role = 'student' THEN 1 ELSE 0 END) as student_count,
                SUM(CASE WHEN role = 'instructor' THEN 1 ELSE 0 END) as instructor_count,
                SUM(CASE WHEN role = 'department_head' THEN 1 ELSE 0 END) as dept_head_count,
                SUM(CASE WHEN role = 'department_secretary' THEN 1 ELSE 0 END) as dept_sec_count,
                SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_count,
                SUM(CASE WHEN MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE()) THEN 1 ELSE 0 END) as new_users_month
              FROM users 
              WHERE deleted_at IS NULL";
$userResult = mysqli_query($conn, $userQuery);
$userStats = mysqli_fetch_assoc($userResult);

// Course statistics
$courseQuery = "SELECT 
                 COUNT(*) as total_courses,
                 SUM(CASE WHEN status = 'Published' THEN 1 ELSE 0 END) as published_courses,
                 SUM(CASE WHEN status = 'Draft' THEN 1 ELSE 0 END) as draft_courses,
                 SUM(CASE WHEN financial_approval_date IS NOT NULL THEN 1 ELSE 0 END) as financially_approved,
                 SUM(CASE WHEN financial_approval_date IS NULL THEN 1 ELSE 0 END) as pending_financial,
                 COUNT(DISTINCT department_id) as departments_with_courses
               FROM courses
               WHERE deleted_at IS NULL";
$courseResult = mysqli_query($conn, $courseQuery);
$courseStats = mysqli_fetch_assoc($courseResult);

// Enrollment statistics
$enrollmentQuery = "SELECT 
                     COUNT(*) as total_enrollments,
                     SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active_enrollments,
                     SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_enrollments,
                     SUM(CASE WHEN status = 'Expired' THEN 1 ELSE 0 END) as expired_enrollments,
                     SUM(CASE WHEN DATE(enrolled_at) = CURRENT_DATE() THEN 1 ELSE 0 END) as enrollments_today,
                     SUM(CASE WHEN DATE(enrolled_at) >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as enrollments_week,
                     SUM(CASE WHEN DATE(enrolled_at) >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as enrollments_month
                   FROM enrollments
                   WHERE deleted_at IS NULL";
$enrollmentResult = mysqli_query($conn, $enrollmentQuery);
$enrollmentStats = mysqli_fetch_assoc($enrollmentResult);

// Calculate course completion rate more accurately
$completionRateQuery = "SELECT 
                          AVG(completion_percentage) as avg_completion_percentage,
                          SUM(CASE WHEN completion_percentage = 100 THEN 1 ELSE 0 END) as fully_completed,
                          COUNT(*) as total_enrollments
                        FROM enrollments
                        WHERE deleted_at IS NULL";
$completionRateResult = mysqli_query($conn, $completionRateQuery);
$completionRateStats = mysqli_fetch_assoc($completionRateResult);

// Revenue statistics
$revenueQuery = "SELECT 
                  SUM(amount) as total_revenue,
                  SUM(CASE WHEN DATE(payment_date) = CURRENT_DATE() THEN amount ELSE 0 END) as revenue_today,
                  SUM(CASE WHEN DATE(payment_date) >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY) THEN amount ELSE 0 END) as revenue_week,
                  SUM(CASE WHEN DATE(payment_date) >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) THEN amount ELSE 0 END) as revenue_month,
                  SUM(CASE WHEN YEAR(payment_date) = YEAR(CURRENT_DATE()) THEN amount ELSE 0 END) as revenue_year,
                  COUNT(*) as total_transactions,
                  AVG(amount) as average_transaction
                FROM course_payments
                WHERE status = 'Completed' AND deleted_at IS NULL";
$revenueResult = mysqli_query($conn, $revenueQuery);
$revenueStats = mysqli_fetch_assoc($revenueResult);

// Instructor earnings - use revenue settings for calculations
$instructorSharePercentage = isset($revenueSettings['instructor_split']) ? $revenueSettings['instructor_split'] : 40; // Default to 40%
$earningsQuery = "SELECT 
                   SUM(instructor_share) as total_earnings,
                   SUM(CASE WHEN status = 'Available' THEN instructor_share ELSE 0 END) as available_earnings,
                   SUM(CASE WHEN status = 'Withdrawn' THEN instructor_share ELSE 0 END) as withdrawn_earnings,
                   SUM(CASE WHEN status = 'Pending' THEN instructor_share ELSE 0 END) as pending_earnings,
                   COUNT(DISTINCT instructor_id) as earning_instructors
                 FROM instructor_earnings
                 WHERE deleted_at IS NULL";
$earningsResult = mysqli_query($conn, $earningsQuery);
$earningsStats = mysqli_fetch_assoc($earningsResult);

// Department statistics
$departmentQuery = "SELECT 
                     COUNT(*) as total_departments,
                     (SELECT COUNT(*) FROM departments WHERE deleted_at IS NULL AND is_active = 1) as active_departments,
                     (SELECT COUNT(*) FROM department_staff WHERE deleted_at IS NULL AND status = 'active' AND role = 'head') as active_heads,
                     (SELECT COUNT(*) FROM department_staff WHERE deleted_at IS NULL AND status = 'active' AND role = 'secretary') as active_secretaries
                   FROM departments
                   WHERE deleted_at IS NULL";
$departmentResult = mysqli_query($conn, $departmentQuery);
$departmentStats = mysqli_fetch_assoc($departmentResult);

// Top courses by enrollment - with financial approval and publication status
$topCoursesQuery = "SELECT c.course_id, c.title, c.status, c.financial_approval_date,
                     c.approval_status, d.name as department_name,
                     COUNT(e.enrollment_id) as enrollment_count,
                     SUM(cp.amount) as revenue
                    FROM courses c
                    JOIN departments d ON c.department_id = d.department_id
                    LEFT JOIN enrollments e ON c.course_id = e.course_id AND e.deleted_at IS NULL
                    LEFT JOIN course_payments cp ON e.enrollment_id = cp.enrollment_id AND cp.status = 'Completed' AND cp.deleted_at IS NULL
                    WHERE c.deleted_at IS NULL
                    GROUP BY c.course_id
                    ORDER BY enrollment_count DESC
                    LIMIT 5";
$topCoursesResult = mysqli_query($conn, $topCoursesQuery);
$topCourses = [];
while ($course = mysqli_fetch_assoc($topCoursesResult)) {
    // Add an indicator for properly financially approved published courses
    $course['is_properly_published'] = ($course['status'] == 'Published' && $course['financial_approval_date'] !== null && $course['approval_status'] == 'approved');
    $topCourses[] = $course;
}

// Top instructors by revenue
$topInstructorsQuery = "SELECT i.instructor_id, CONCAT(u.first_name, ' ', u.last_name) as instructor_name, 
                         COUNT(DISTINCT c.course_id) as course_count,
                         COUNT(DISTINCT e.enrollment_id) as student_count,
                         SUM(ie.instructor_share) as total_earnings
                        FROM instructors i
                        JOIN users u ON i.user_id = u.user_id
                        LEFT JOIN course_instructors ci ON i.instructor_id = ci.instructor_id AND ci.deleted_at IS NULL
                        LEFT JOIN courses c ON ci.course_id = c.course_id AND c.deleted_at IS NULL
                        LEFT JOIN enrollments e ON c.course_id = e.course_id AND e.deleted_at IS NULL
                        LEFT JOIN instructor_earnings ie ON i.instructor_id = ie.instructor_id AND ie.deleted_at IS NULL
                        WHERE i.deleted_at IS NULL AND u.deleted_at IS NULL
                        GROUP BY i.instructor_id
                        ORDER BY total_earnings DESC
                        LIMIT 5";
$topInstructorsResult = mysqli_query($conn, $topInstructorsQuery);
$topInstructors = [];
while ($instructor = mysqli_fetch_assoc($topInstructorsResult)) {
    $topInstructors[] = $instructor;
}

// Monthly enrollment data for chart
$monthlyEnrollmentQuery = "SELECT 
                            DATE_FORMAT(enrolled_at, '%Y-%m') as month,
                            COUNT(*) as enrollment_count
                           FROM enrollments
                           WHERE deleted_at IS NULL AND enrolled_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 11 MONTH)
                           GROUP BY DATE_FORMAT(enrolled_at, '%Y-%m')
                           ORDER BY month ASC";
$monthlyEnrollmentResult = mysqli_query($conn, $monthlyEnrollmentQuery);
$monthlyEnrollmentData = [];
while ($month = mysqli_fetch_assoc($monthlyEnrollmentResult)) {
    $monthlyEnrollmentData[] = $month;
}

// Monthly revenue data for chart
$monthlyRevenueQuery = "SELECT 
                         DATE_FORMAT(payment_date, '%Y-%m') as month,
                         SUM(amount) as revenue
                        FROM course_payments
                        WHERE status = 'Completed' AND deleted_at IS NULL AND payment_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 11 MONTH)
                        GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
                        ORDER BY month ASC";
$monthlyRevenueResult = mysqli_query($conn, $monthlyRevenueQuery);
$monthlyRevenueData = [];
while ($month = mysqli_fetch_assoc($monthlyRevenueResult)) {
    $monthlyRevenueData[] = $month;
}

// Category statistics
$categoryQuery = "SELECT c.category_id, c.name,
                   COUNT(DISTINCT s.subcategory_id) as subcategory_count,
                   COUNT(DISTINCT co.course_id) as course_count
                  FROM categories c
                  LEFT JOIN subcategories s ON c.category_id = s.category_id AND s.deleted_at IS NULL
                  LEFT JOIN courses co ON s.subcategory_id = co.subcategory_id AND co.deleted_at IS NULL
                  WHERE c.deleted_at IS NULL
                  GROUP BY c.category_id
                  ORDER BY course_count DESC
                  LIMIT 5";
$categoryResult = mysqli_query($conn, $categoryQuery);
$categoryStats = [];
while ($category = mysqli_fetch_assoc($categoryResult)) {
    $categoryStats[] = $category;
}

// Get average course rating
$avgRatingQuery = "SELECT AVG(rating) as avg_rating FROM course_ratings";
$avgRatingResult = mysqli_query($conn, $avgRatingQuery);
$avgRatingData = mysqli_fetch_assoc($avgRatingResult);
$avgRating = round($avgRatingData['avg_rating'] ?? 0, 1);

// Convert data to JSON for charts
$enrollmentChartData = json_encode($monthlyEnrollmentData);
$revenueChartData = json_encode($monthlyRevenueData);
?>

<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Admin /</span> Reports Overview
  </h4>

  <!-- Report Date Range -->
  <div class="card mb-4">
    <div class="card-body">
      <div class="row align-items-center">
        <div class="col-md-6">
          <h5 class="mb-1">Platform Performance Overview</h5>
          <p class="text-muted mb-md-0">Key metrics and statistics as of <?php echo date('F d, Y'); ?></p>
        </div>
        <div class="col-md-6 text-md-end">
          <div class="btn-group" role="group" aria-label="Report Period">
            <button type="button" class="btn btn-outline-primary btn-sm active" data-period="all">All Time</button>
            <button type="button" class="btn btn-outline-primary btn-sm" data-period="year">This Year</button>
            <button type="button" class="btn btn-outline-primary btn-sm" data-period="month">This Month</button>
            <button type="button" class="btn btn-outline-primary btn-sm" data-period="week">This Week</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Stats Overview -->
  <div class="row">
    <!-- Users Stats -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title m-0 me-2">Users</h5>
          <div class="dropdown">
            <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
              <a class="dropdown-item" href="../admin/users.php">View All Users</a>
              <a class="dropdown-item" href="../admin/instructors.php">View Instructors</a>
              <a class="dropdown-item" href="../admin/learners.php">View Students</a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex flex-column align-items-start gap-1">
              <h2 class="mb-0"><?php echo number_format($userStats['total_users']); ?></h2>
              <span class="text-muted">Total Users</span>
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
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="bx bx-user"></i>
                </span>
              </div>
              <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                <div class="me-2">
                  <p class="mb-0">Students</p>
                </div>
                <div>
                  <h5 class="mb-0"><?php echo number_format($userStats['student_count']); ?></h5>
                </div>
              </div>
            </li>
            <li class="d-flex mb-2">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-success">
                  <i class="bx bx-chalkboard"></i>
                </span>
              </div>
              <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                <div class="me-2">
                  <p class="mb-0">Instructors</p>
                </div>
                <div>
                  <h5 class="mb-0"><?php echo number_format($userStats['instructor_count']); ?></h5>
                </div>
              </div>
            </li>
            <li class="d-flex mb-2">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-info">
                  <i class="bx bx-building"></i>
                </span>
              </div>
              <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                <div class="me-2">
                  <p class="mb-0">Department Admins</p>
                </div>
                <div>
                  <h5 class="mb-0"><?php echo number_format($userStats['dept_head_count'] + $userStats['dept_sec_count']); ?></h5>
                </div>
              </div>
            </li>
          </ul>
          <div class="d-flex justify-content-between mt-3">
            <p class="mb-0 new-user-label">New users this month</p>
            <p class="mb-0 text-success">+<?php echo number_format($userStats['new_users_month']); ?></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Enrollment Stats -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title m-0 me-2">Enrollments</h5>
          <div class="dropdown">
            <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
              <a class="dropdown-item" href="#">View Enrollment Report</a>
              <a class="dropdown-item" href="#">Export Data</a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex flex-column align-items-start gap-1">
              <h2 class="mb-0"><?php echo number_format($enrollmentStats['total_enrollments']); ?></h2>
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
                  <h5 class="mb-0"><?php echo number_format($enrollmentStats['enrollments_today']); ?></h5>
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
                  <h5 class="mb-0"><?php echo number_format($enrollmentStats['enrollments_week']); ?></h5>
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
                  <h5 class="mb-0"><?php echo number_format($enrollmentStats['enrollments_month']); ?></h5>
                </div>
              </div>
            </li>
          </ul>
          <div class="d-flex justify-content-between mt-3">
            <p class="mb-0">Active enrollments</p>
            <p class="mb-0 text-success"><?php echo number_format($enrollmentStats['active_enrollments']); ?> (<?php echo round(($enrollmentStats['active_enrollments'] / $enrollmentStats['total_enrollments']) * 100); ?>%)</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Course Stats -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title m-0 me-2">Courses</h5>
          <div class="dropdown">
            <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
              <a class="dropdown-item" href="../admin/courses.php">View All Courses</a>
              <a class="dropdown-item" href="../admin/categories.php">Manage Categories</a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex flex-column align-items-start gap-1">
              <h2 class="mb-0"><?php echo number_format($courseStats['total_courses']); ?></h2>
              <span class="text-muted">Total Courses</span>
            </div>
            <div class="avatar me-1">
              <span class="avatar-initial rounded bg-label-info">
                <i class="bx bx-book-alt bx-md"></i>
              </span>
            </div>
          </div>
          <ul class="p-0 m-0">
            <li class="d-flex mb-2">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-success">
                  <i class="bx bx-globe"></i>
                </span>
              </div>
              <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                <div class="me-2">
                  <p class="mb-0">Published</p>
                </div>
                <div>
                  <h5 class="mb-0"><?php echo number_format($courseStats['published_courses']); ?></h5>
                </div>
              </div>
            </li>
            <li class="d-flex mb-2">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-secondary">
                  <i class="bx bx-edit"></i>
                </span>
              </div>
              <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                <div class="me-2">
                  <p class="mb-0">Draft</p>
                </div>
                <div>
                  <h5 class="mb-0"><?php echo number_format($courseStats['draft_courses']); ?></h5>
                </div>
              </div>
            </li>
            <li class="d-flex mb-2">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="bx bx-money"></i>
                </span>
              </div>
              <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                <div class="me-2">
                  <p class="mb-0">Financially Approved</p>
                </div>
                <div>
                  <h5 class="mb-0"><?php echo number_format($courseStats['financially_approved']); ?></h5>
                </div>
              </div>
            </li>
          </ul>
          <div class="d-flex justify-content-between mt-3">
            <p class="mb-0">Pending financial approval</p>
            <p class="mb-0 text-warning"><?php echo number_format($courseStats['pending_financial']); ?></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Revenue Stats -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title m-0 me-2">Revenue</h5>
          <div class="dropdown">
            <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
              <a class="dropdown-item" href="#">View Financial Report</a>
              <a class="dropdown-item" href="#">Export Data</a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex flex-column align-items-start gap-1">
              <h2 class="mb-0">₵<?php echo number_format($revenueStats['total_revenue'], 2); ?></h2>
              <span class="text-muted">Total Revenue</span>
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
                  <i class="bx bx-time"></i>
                </span>
              </div>
              <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                <div class="me-2">
                  <p class="mb-0">Today</p>
                </div>
                <div>
                  <h5 class="mb-0">₵<?php echo number_format($revenueStats['revenue_today'], 2); ?></h5>
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
                  <h5 class="mb-0">₵<?php echo number_format($revenueStats['revenue_week'], 2); ?></h5>
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
                  <h5 class="mb-0">₵<?php echo number_format($revenueStats['revenue_month'], 2); ?></h5>
                </div>
              </div>
            </li>
          </ul>
          <div class="d-flex justify-content-between mt-3">
            <p class="mb-0">Average transaction</p>
            <p class="mb-0 text-info">₵<?php echo number_format($revenueStats['average_transaction'], 2); ?></p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Charts -->
  <div class="row">
    <!-- Enrollment Trends -->
    <div class="col-lg-6 mb-4">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title m-0 me-2">Enrollment Trends</h5>
          <div class="dropdown">
            <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
              <a class="dropdown-item" href="#">View Detailed Report</a>
              <a class="dropdown-item" href="#">Export Chart</a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <canvas id="enrollmentTrendsChart" height="300"></canvas>
        </div>
      </div>
    </div>

    <!-- Revenue Trends -->
    <div class="col-lg-6 mb-4">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title m-0 me-2">Revenue Trends</h5>
          <div class="dropdown">
            <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
              <a class="dropdown-item" href="#">View Detailed Report</a>
              <a class="dropdown-item" href="#">Export Chart</a>
           </div>
         </div>
       </div>
       <div class="card-body">
         <canvas id="revenueTrendsChart" height="300"></canvas>
       </div>
     </div>
   </div>
 </div>

 <!-- Top Performers -->
 <div class="row">
   <!-- Top Courses -->
   <div class="col-xl-6 mb-4">
     <div class="card h-100">
       <div class="card-header d-flex align-items-center justify-content-between">
         <h5 class="card-title m-0 me-2">Top Performing Courses</h5>
         <div class="dropdown">
           <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
             <i class="bx bx-dots-vertical-rounded"></i>
           </button>
           <div class="dropdown-menu dropdown-menu-end">
             <a class="dropdown-item" href="#">View All Courses</a>
             <a class="dropdown-item" href="#">Export Data</a>
           </div>
         </div>
       </div>
       <div class="card-body">
         <div class="table-responsive">
           <table class="table table-hover">
             <thead>
               <tr>
                 <th>Course</th>
                 <th>Department</th>
                 <th>Enrollments</th>
                 <th>Revenue</th>
                 <th>Status</th>
               </tr>
             </thead>
             <tbody>
               <?php foreach ($topCourses as $course): ?>
               <tr>
                 <td>
                   <div class="d-flex align-items-center">
                     <div class="d-flex flex-column">
                       <h6 class="mb-0 text-truncate" style="max-width: 200px;"><?php echo htmlspecialchars($course['title']); ?></h6>
                     </div>
                   </div>
                 </td>
                 <td><?php echo htmlspecialchars($course['department_name']); ?></td>
                 <td>
                   <div class="d-flex align-items-center">
                     <span><?php echo number_format($course['enrollment_count']); ?></span>
                   </div>
                 </td>
                 <td>₵<?php echo number_format($course['revenue'] ?? 0, 2); ?></td>
                 <td>
                   <?php if ($course['is_properly_published']): ?>
                     <span class="badge bg-success">Properly Published</span>
                   <?php elseif ($course['status'] == 'Published' && $course['financial_approval_date'] === null): ?>
                     <span class="badge bg-warning">Published (No Financial Approval)</span>
                   <?php elseif ($course['status'] == 'Published' && $course['approval_status'] !== 'approved'): ?>
                     <span class="badge bg-warning">Published (Not Fully Approved)</span>
                   <?php else: ?>
                     <span class="badge bg-secondary"><?php echo $course['status']; ?></span>
                   <?php endif; ?>
                 </td>
               </tr>
               <?php endforeach; ?>
             </tbody>
           </table>
         </div>
       </div>
     </div>
   </div>

   <!-- Top Instructors -->
   <div class="col-xl-6 mb-4">
     <div class="card h-100">
       <div class="card-header d-flex align-items-center justify-content-between">
         <h5 class="card-title m-0 me-2">Top Earning Instructors</h5>
         <div class="dropdown">
           <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
             <i class="bx bx-dots-vertical-rounded"></i>
           </button>
           <div class="dropdown-menu dropdown-menu-end">
             <a class="dropdown-item" href="#">View All Instructors</a>
             <a class="dropdown-item" href="#">Export Data</a>
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
               </tr>
             </thead>
             <tbody>
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
                 <td>₵<?php echo number_format($instructor['total_earnings'] ?? 0, 2); ?></td>
               </tr>
               <?php endforeach; ?>
             </tbody>
           </table>
         </div>
       </div>
     </div>
   </div>
 </div>

 <!-- Department & Categories -->
 <div class="row">
   <!-- Department Statistics -->
   <div class="col-xl-6 mb-4">
     <div class="card h-100">
       <div class="card-header d-flex align-items-center justify-content-between">
         <h5 class="card-title m-0 me-2">Department Statistics</h5>
         <div class="dropdown">
           <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
             <i class="bx bx-dots-vertical-rounded"></i>
           </button>
           <div class="dropdown-menu dropdown-menu-end">
             <a class="dropdown-item" href="#">View Departments</a>
             <a class="dropdown-item" href="#">Export Data</a>
           </div>
         </div>
       </div>
       <div class="card-body">
         <div class="row mb-4">
           <div class="col-md-6 mb-3 mb-md-0">
             <div class="card shadow-none border text-center h-100">
               <div class="card-body">
                 <div class="avatar avatar-md mx-auto mb-3">
                   <span class="avatar-initial rounded-circle bg-label-info">
                     <i class="bx bx-building"></i>
                   </span>
                 </div>
                 <h4 class="mb-1"><?php echo number_format($departmentStats['total_departments']); ?></h4>
                 <p class="text-muted mb-0">Total Departments</p>
               </div>
             </div>
           </div>
           <div class="col-md-6">
             <div class="card shadow-none border text-center h-100">
               <div class="card-body">
                 <div class="avatar avatar-md mx-auto mb-3">
                   <span class="avatar-initial rounded-circle bg-label-success">
                     <i class="bx bx-user-check"></i>
                   </span>
                 </div>
                 <h4 class="mb-1"><?php echo number_format($departmentStats['active_departments']); ?></h4>
                 <p class="text-muted mb-0">Active Departments</p>
               </div>
             </div>
           </div>
         </div>
         <div class="row">
           <div class="col-md-6 mb-3 mb-md-0">
             <div class="card shadow-none border text-center h-100">
               <div class="card-body">
                 <div class="avatar avatar-md mx-auto mb-3">
                   <span class="avatar-initial rounded-circle bg-label-primary">
                     <i class="bx bx-user-voice"></i>
                   </span>
                 </div>
                 <h4 class="mb-1"><?php echo number_format($departmentStats['active_heads']); ?></h4>
                 <p class="text-muted mb-0">Department Heads</p>
               </div>
             </div>
           </div>
           <div class="col-md-6">
             <div class="card shadow-none border text-center h-100">
               <div class="card-body">
                 <div class="avatar avatar-md mx-auto mb-3">
                   <span class="avatar-initial rounded-circle bg-label-warning">
                     <i class="bx bx-user-pin"></i>
                   </span>
                 </div>
                 <h4 class="mb-1"><?php echo number_format($departmentStats['active_secretaries']); ?></h4>
                 <p class="text-muted mb-0">Department Secretaries</p>
               </div>
             </div>
           </div>
         </div>
       </div>
     </div>
   </div>

   <!-- Top Categories -->
   <div class="col-xl-6 mb-4">
     <div class="card h-100">
       <div class="card-header d-flex align-items-center justify-content-between">
         <h5 class="card-title m-0 me-2">Popular Categories</h5>
         <div class="dropdown">
           <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
             <i class="bx bx-dots-vertical-rounded"></i>
           </button>
           <div class="dropdown-menu dropdown-menu-end">
             <a class="dropdown-item" href="#">View All Categories</a>
             <a class="dropdown-item" href="#">Export Data</a>
           </div>
         </div>
       </div>
       <div class="card-body">
         <?php if (!empty($categoryStats)): ?>
           <?php foreach ($categoryStats as $category): ?>
             <div class="d-flex justify-content-between align-items-center mb-3">
               <div class="d-flex align-items-center">
                 <div class="avatar me-3">
                   <span class="avatar-initial rounded bg-label-primary">
                     <?php echo substr($category['name'], 0, 1); ?>
                   </span>
                 </div>
                 <div>
                   <h6 class="mb-0"><?php echo htmlspecialchars($category['name']); ?></h6>
                   <small class="text-muted"><?php echo number_format($category['subcategory_count']); ?> subcategories</small>
                 </div>
               </div>
               <div>
                 <h6 class="mb-0"><?php echo number_format($category['course_count']); ?> courses</h6>
                 <div class="progress" style="width: 120px; height: 5px">
                   <div
                     class="progress-bar bg-primary"
                     style="width: <?php echo min(100, ($category['course_count'] / max(1, $courseStats['total_courses'])) * 100); ?>%"
                     role="progressbar"
                     aria-valuenow="<?php echo min(100, ($category['course_count'] / max(1, $courseStats['total_courses'])) * 100); ?>"
                     aria-valuemin="0"
                     aria-valuemax="100"
                   ></div>
                 </div>
               </div>
             </div>
           <?php endforeach; ?>
         <?php else: ?>
           <div class="text-center p-4">
             <p class="mb-0 text-muted">No categories available.</p>
           </div>
         <?php endif; ?>
       </div>
     </div>
   </div>
 </div>

 <!-- System Overview -->
 <div class="row">
   <div class="col-12 mb-4">
     <div class="card">
       <div class="card-header d-flex justify-content-between align-items-center">
         <h5 class="card-title">System Overview</h5>
         <div class="dropdown">
           <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
             <i class="bx bx-dots-vertical-rounded"></i>
           </button>
           <div class="dropdown-menu dropdown-menu-end">
             <a class="dropdown-item" href="#">Refresh Data</a>
             <a class="dropdown-item" href="#">Export Report</a>
           </div>
         </div>
       </div>
       <div class="card-body">
         <div class="row">
           <div class="col-md-3 col-6 mb-4">
             <div class="d-flex align-items-center">
               <div class="badge rounded-pill bg-label-primary me-3 p-2">
                 <i class="bx bx-money text-primary"></i>
               </div>
               <div class="card-info">
                 <h5 class="mb-0">₵<?php echo number_format($revenueStats['total_revenue'], 2); ?></h5>
                 <small>Total Revenue</small>
               </div>
             </div>
           </div>
           <div class="col-md-3 col-6 mb-4">
             <div class="d-flex align-items-center">
               <div class="badge rounded-pill bg-label-success me-3 p-2">
                 <i class="bx bx-book-open text-success"></i>
               </div>
               <div class="card-info">
                 <h5 class="mb-0"><?php echo number_format($courseStats['total_courses']); ?></h5>
                 <small>Total Courses</small>
               </div>
             </div>
           </div>
           <div class="col-md-3 col-6 mb-4">
             <div class="d-flex align-items-center">
               <div class="badge rounded-pill bg-label-info me-3 p-2">
                 <i class="bx bx-user text-info"></i>
               </div>
               <div class="card-info">
                 <h5 class="mb-0"><?php echo number_format($userStats['total_users']); ?></h5>
                 <small>Total Users</small>
               </div>
             </div>
           </div>
           <div class="col-md-3 col-6 mb-4">
             <div class="d-flex align-items-center">
               <div class="badge rounded-pill bg-label-warning me-3 p-2">
                 <i class="bx bx-user-check text-warning"></i>
               </div>
               <div class="card-info">
                 <h5 class="mb-0"><?php echo number_format($enrollmentStats['total_enrollments']); ?></h5>
                 <small>Total Enrollments</small>
               </div>
             </div>
           </div>
         </div>

         <div class="divider divider-dashed my-3">
           <div class="divider-text">Platform Health</div>
         </div>

         <div class="row">
           <div class="col-md-4 mb-3">
             <div class="card shadow-none bg-label-primary">
               <div class="card-body p-3">
                 <div class="d-flex justify-content-between align-items-center">
                   <div>
                     <h5 class="mb-0">Course Completion Rate</h5>
                     <small>Based on all enrollments</small>
                   </div>
                   <div class="display-6 fw-bold">
                     <?php 
                       // Use the more accurate completion rate from the query
                       $completionRate = isset($completionRateStats['avg_completion_percentage']) 
                         ? round($completionRateStats['avg_completion_percentage']) 
                         : 0;
                       echo $completionRate . '%';
                     ?>
                   </div>
                 </div>
               </div>
             </div>
           </div>

           <div class="col-md-4 mb-3">
             <div class="card shadow-none bg-label-success">
               <div class="card-body p-3">
                 <div class="d-flex justify-content-between align-items-center">
                   <div>
                     <h5 class="mb-0">Average Course Rating</h5>
                     <small>Based on student reviews</small>
                   </div>
                   <div class="display-6 fw-bold">
                     <?php echo $avgRating; ?>
                   </div>
                 </div>
               </div>
             </div>
           </div>

           <div class="col-md-4 mb-3">
             <div class="card shadow-none bg-label-warning">
               <div class="card-body p-3">
                 <div class="d-flex justify-content-between align-items-center">
                   <div>
                     <h5 class="mb-0">Instructor Earnings</h5>
                     <small>Instructor split: <?php echo $instructorSharePercentage; ?>%</small>
                   </div>
                   <div class="display-6 fw-bold">
                     ₵<?php echo number_format($earningsStats['total_earnings'] ?? 0, 0); ?>
                   </div>
                 </div>
               </div>
             </div>
           </div>
         </div>
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
  let enrollmentChart, revenueChart;

  // Function to initialize or update charts
  function initializeCharts(enrollmentData, revenueData) {
    const monthLabels = [];
    const enrollmentCounts = [];
    const revenueAmounts = [];

    // Process enrollment data
    enrollmentData.forEach(item => {
      const date = new Date(item.month + '-01');
      monthLabels.push(date.toLocaleDateString('default', { month: 'short', year: 'numeric' }));
      enrollmentCounts.push(parseInt(item.enrollment_count));
    });

    // Process revenue data
    revenueData.forEach(item => {
      revenueAmounts.push(parseFloat(item.revenue));
    });

    // Destroy existing charts if they exist
    if (enrollmentChart) enrollmentChart.destroy();
    if (revenueChart) revenueChart.destroy();

    // Enrollment Trends Chart
    const enrollmentCtx = document.getElementById('enrollmentTrendsChart').getContext('2d');
    enrollmentChart = new Chart(enrollmentCtx, {
      type: 'line',
      data: {
        labels: monthLabels,
        datasets: [{
          label: 'Enrollments',
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

    // Revenue Trends Chart
    const revenueCtx = document.getElementById('revenueTrendsChart').getContext('2d');
    revenueChart = new Chart(revenueCtx, {
      type: 'line',
      data: {
        labels: monthLabels,
        datasets: [{
          label: 'Revenue (₵)',
          data: revenueAmounts,
          fill: true,
          backgroundColor: 'rgba(40, 167, 69, 0.1)',
          borderColor: 'rgba(40, 167, 69, 1)',
          tension: 0.4,
          pointBackgroundColor: 'rgba(40, 167, 69, 1)',
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
            grid: { color: 'rgba(0, 0, 0, 0.1)' },
            ticks: {
              callback: function(value) { return '₵' + value.toLocaleString(); }
            }
          },
          x: { grid: { display: false } }
        },
        plugins: {
          legend: { display: true, position: 'top' },
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

  // Function to update UI with new data
  function updateUI(data) {
    // Update Users Stats
    document.querySelector('.card-body .mb-0').textContent = data.userStats.total_users.toLocaleString();
    document.querySelector('.card-body ul li:nth-child(1) h5').textContent = data.userStats.student_count.toLocaleString();
    document.querySelector('.card-body ul li:nth-child(2) h5').textContent = data.userStats.instructor_count.toLocaleString();
    document.querySelector('.card-body ul li:nth-child(3) h5').textContent = (data.userStats.dept_head_count + data.userStats.dept_sec_count).toLocaleString();
    document.querySelector('.new-user-label + p').textContent = '+' + data.userStats.new_users_month.toLocaleString();

    // Update Enrollments Stats
    document.querySelector('.col-xl-3:nth-child(2) .card-body .mb-0').textContent = data.enrollmentStats.total_enrollments.toLocaleString();
    document.querySelector('.col-xl-3:nth-child(2) .card-body ul li:nth-child(1) h5').textContent = data.enrollmentStats.enrollments_today.toLocaleString();
    document.querySelector('.col-xl-3:nth-child(2) .card-body ul li:nth-child(2) h5').textContent = data.enrollmentStats.enrollments_week.toLocaleString();
    document.querySelector('.col-xl-3:nth-child(2) .card-body ul li:nth-child(3) h5').textContent = data.enrollmentStats.enrollments_month.toLocaleString();
    document.querySelector('.col-xl-3:nth-child(2) .card-body .mt-3 p:last-child').textContent = 
      `${data.enrollmentStats.active_enrollments.toLocaleString()} (${Math.round((data.enrollmentStats.active_enrollments / data.enrollmentStats.total_enrollments) * 100)}%)`;

    // Update Courses Stats
    document.querySelector('.col-xl-3:nth-child(3) .card-body .mb-0').textContent = data.courseStats.total_courses.toLocaleString();
    document.querySelector('.col-xl-3:nth-child(3) .card-body ul li:nth-child(1) h5').textContent = data.courseStats.published_courses.toLocaleString();
    document.querySelector('.col-xl-3:nth-child(3) .card-body ul li:nth-child(2) h5').textContent = data.courseStats.draft_courses.toLocaleString();
    document.querySelector('.col-xl-3:nth-child(3) .card-body ul li:nth-child(3) h5').textContent = data.courseStats.financially_approved.toLocaleString();
    document.querySelector('.col-xl-3:nth-child(3) .card-body .mt-3 p:last-child').textContent = data.courseStats.pending_financial.toLocaleString();

    // Update Revenue Stats
    document.querySelector('.col-xl-3:nth-child(4) .card-body .mb-0').textContent = '₵' + data.revenueStats.total_revenue.toLocaleString('en-US', { minimumFractionDigits: 2 });
    document.querySelector('.col-xl-3:nth-child(4) .card-body ul li:nth-child(1) h5').textContent = '₵' + data.revenueStats.revenue_today.toLocaleString('en-US', { minimumFractionDigits: 2 });
    document.querySelector('.col-xl-3:nth-child(4) .card-body ul li:nth-child(2) h5').textContent = '₵' + data.revenueStats.revenue_week.toLocaleString('en-US', { minimumFractionDigits: 2 });
    document.querySelector('.col-xl-3:nth-child(4) .card-body ul li:nth-child(3) h5').textContent = '₵' + data.revenueStats.revenue_month.toLocaleString('en-US', { minimumFractionDigits: 2 });
    document.querySelector('.col-xl-3:nth-child(4) .card-body .mt-3 p:last-child').textContent = '₵' + data.revenueStats.average_transaction.toLocaleString('en-US', { minimumFractionDigits: 2 });

    // Update charts
    initializeCharts(data.monthlyEnrollmentData, data.monthlyRevenueData);
  }

  // Function to fetch filtered data
  function fetchFilteredData(period) {
    fetch('../backend/admin/reports_filter.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'period=' + encodeURIComponent(period)
    })
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        console.error('Error:', data.error);
        return;
      }
      updateUI(data);
    })
    .catch(error => console.error('Fetch error:', error));
  }

  // Function to export data as CSV
  function exportData(period, section = 'all') {
    fetch(`../backend/admin/reports_filter.php?export=csv&section=${section}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'period=' + encodeURIComponent(period)
    })
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        console.error('Error:', data.error);
        return;
      }

      // Create CSV content based on section
      let csv = 'Category,Metric,Value\n';
      if (section === 'users') {
        csv += `Users,Total Users,${data.userStats.total_users}\n`;
        csv += `Users,Students,${data.userStats.student_count}\n`;
        csv += `Users,Instructors,${data.userStats.instructor_count}\n`;
        csv += `Users,Department Admins,${data.userStats.dept_head_count + data.userStats.dept_sec_count}\n`;
        csv += `Users,New Users This Month,${data.userStats.new_users_month}\n`;
      } else if (section === 'enrollments') {
        csv += `Enrollments,Total Enrollments,${data.enrollmentStats.total_enrollments}\n`;
        csv += `Enrollments,Enrollments Today,${data.enrollmentStats.enrollments_today}\n`;
        csv += `Enrollments,Enrollments This Week,${data.enrollmentStats.enrollments_week}\n`;
        csv += `Enrollments,Enrollments This Month,${data.enrollmentStats.enrollments_month}\n`;
        csv += `Enrollments,Active Enrollments,${data.enrollmentStats.active_enrollments}\n`;
      } else if (section === 'courses') {
        csv += `Courses,Total Courses,${data.courseStats.total_courses}\n`;
        csv += `Courses,Published Courses,${data.courseStats.published_courses}\n`;
        csv += `Courses,Draft Courses,${data.courseStats.draft_courses}\n`;
        csv += `Courses,Financially Approved,${data.courseStats.financially_approved}\n`;
        csv += `Courses,Pending Financial Approval,${data.courseStats.pending_financial}\n`;
      } else if (section === 'revenue') {
        csv += `Revenue,Total Revenue,${data.revenueStats.total_revenue}\n`;
        csv += `Revenue,Revenue Today,${data.revenueStats.revenue_today}\n`;
        csv += `Revenue,Revenue This Week,${data.revenueStats.revenue_week}\n`;
        csv += `Revenue,Revenue This Month,${data.revenueStats.revenue_month}\n`;
        csv += `Revenue,Average Transaction,${data.revenueStats.average_transaction}\n`;
      } else {
        // Full export (same as main Export CSV button)
        csv += `Users,Total Users,${data.userStats.total_users}\n`;
        csv += `Users,Students,${data.userStats.student_count}\n`;
        csv += `Users,Instructors,${data.userStats.instructor_count}\n`;
        csv += `Users,Department Admins,${data.userStats.dept_head_count + data.userStats.dept_sec_count}\n`;
        csv += `Users,New Users This Month,${data.userStats.new_users_month}\n`;
        csv += `Enrollments,Total Enrollments,${data.enrollmentStats.total_enrollments}\n`;
        csv += `Enrollments,Enrollments Today,${data.enrollmentStats.enrollments_today}\n`;
        csv += `Enrollments,Enrollments This Week,${data.enrollmentStats.enrollments_week}\n`;
        csv += `Enrollments,Enrollments This Month,${data.enrollmentStats.enrollments_month}\n`;
        csv += `Enrollments,Active Enrollments,${data.enrollmentStats.active_enrollments}\n`;
        csv += `Courses,Total Courses,${data.courseStats.total_courses}\n`;
        csv += `Courses,Published Courses,${data.courseStats.published_courses}\n`;
        csv += `Courses,Draft Courses,${data.courseStats.draft_courses}\n`;
        csv += `Courses,Financially Approved,${data.courseStats.financially_approved}\n`;
        csv += `Courses,Pending Financial Approval,${data.courseStats.pending_financial}\n`;
        csv += `Revenue,Total Revenue,${data.revenueStats.total_revenue}\n`;
        csv += `Revenue,Revenue Today,${data.revenueStats.revenue_today}\n`;
        csv += `Revenue,Revenue This Week,${data.revenueStats.revenue_week}\n`;
        csv += `Revenue,Revenue This Month,${data.revenueStats.revenue_month}\n`;
        csv += `Revenue,Average Transaction,${data.revenueStats.average_transaction}\n`;
      }

      // Create and trigger download
      const blob = new Blob([csv], { type: 'text/csv' });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.setAttribute('href', url);
      a.setAttribute('download', `reports_${section}_${period}_${new Date().toISOString().split('T')[0]}.csv`);
      a.click();
      window.URL.revokeObjectURL(url);
    })
    .catch(error => console.error('Export error:', error));
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

  // Export Button (Main Export CSV)
  const exportButton = document.createElement('button');
  exportButton.className = 'btn btn-primary btn-sm ms-2';
  exportButton.textContent = 'Export CSV';
  exportButton.addEventListener('click', () => exportData(currentPeriod, 'all'));
  document.querySelector('.card-body .row.align-items-center .col-md-6.text-md-end').appendChild(exportButton);

  // Dropdown Export Data Links
  document.querySelectorAll('.dropdown-menu a.dropdown-item').forEach(item => {
    if (item.textContent.trim() === 'Export Data') {
      item.addEventListener('click', function(e) {
        e.preventDefault();
        const card = this.closest('.card');
        let section;
        if (card.querySelector('.card-title').textContent.includes('Users')) section = 'users';
        else if (card.querySelector('.card-title').textContent.includes('Enrollments')) section = 'enrollments';
        else if (card.querySelector('.card-title').textContent.includes('Courses')) section = 'courses';
        else if (card.querySelector('.card-title').textContent.includes('Revenue')) section = 'revenue';
        if (section) {
          exportData(currentPeriod, section);
        }
      });
    }
  });

  // Initial data load
  initializeCharts(<?php echo $enrollmentChartData; ?>, <?php echo $revenueChartData; ?>);
});
</script>

<?php include_once '../includes/admin/footer.php'; ?>