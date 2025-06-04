<?php
// Authentication check
require_once '../backend/auth/admin/admin-auth-check.php';

// Database connection
require_once '../backend/config.php';

// Set page title
$pageTitle = "Dashboard | Learnix Admin Panel";

include_once '../includes/admin/header.php';
include_once '../includes/admin/sidebar.php';
include_once '../includes/admin/navbar.php';

// Fetch key metrics for the dashboard
$currentMonth = date('m');
$currentYear = date('Y');

// Total active users
$usersQuery = "SELECT 
    (SELECT COUNT(*) FROM users WHERE role = 'student' AND status = 'active' AND deleted_at IS NULL) as total_students,
    (SELECT COUNT(*) FROM users WHERE role = 'instructor' AND status = 'active' AND deleted_at IS NULL) as total_instructors,
    (SELECT COUNT(*) FROM users WHERE status = 'active' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND deleted_at IS NULL) as new_users_month";
$usersResult = mysqli_query($conn, $usersQuery);
$usersData = mysqli_fetch_assoc($usersResult);

// Course statistics
$coursesQuery = "SELECT 
    (SELECT COUNT(*) FROM courses WHERE deleted_at IS NULL) as total_courses,
    (SELECT COUNT(*) FROM courses WHERE status = 'Published' AND deleted_at IS NULL) as published_courses,
    (SELECT COUNT(*) FROM courses WHERE approval_status = 'pending' AND deleted_at IS NULL) as pending_review,
    (SELECT COUNT(*) FROM courses WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND deleted_at IS NULL) as new_courses_month";
$coursesResult = mysqli_query($conn, $coursesQuery);
$coursesData = mysqli_fetch_assoc($coursesResult);

// Revenue data
$revenueQuery = "SELECT 
    IFNULL(SUM(amount), 0) as total_revenue,
    (SELECT IFNULL(SUM(amount), 0) FROM course_payments WHERE MONTH(payment_date) = $currentMonth AND YEAR(payment_date) = $currentYear AND status = 'Completed') as current_month_revenue,
    (SELECT IFNULL(SUM(amount), 0) FROM course_payments WHERE MONTH(payment_date) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(payment_date) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND status = 'Completed') as previous_month_revenue
    FROM course_payments WHERE status = 'Completed'";
$revenueResult = mysqli_query($conn, $revenueQuery);
$revenueData = mysqli_fetch_assoc($revenueResult);

// Calculate revenue growth percentage
$revenueGrowth = 0;
if ($revenueData['previous_month_revenue'] > 0) {
  $revenueGrowth = (($revenueData['current_month_revenue'] - $revenueData['previous_month_revenue']) / $revenueData['previous_month_revenue']) * 100;
}

// Get monthly enrollments for the chart (last 6 months)
$enrollmentChartQuery = "SELECT 
    DATE_FORMAT(enrolled_at, '%b') AS month,
    COUNT(*) AS count,
    MONTH(enrolled_at) AS month_num
FROM enrollments
WHERE enrolled_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
GROUP BY month, month_num
ORDER BY month_num ASC";

$enrollmentChartResult = mysqli_query($conn, $enrollmentChartQuery);

$monthLabels = [];
$enrollmentCounts = [];
while ($row = mysqli_fetch_assoc($enrollmentChartResult)) {
  $monthLabels[] = $row['month'];
  $enrollmentCounts[] = $row['count'];
}
$chartLabels = json_encode($monthLabels);
$chartData = json_encode($enrollmentCounts);

// Get top-performing courses
$topCoursesQuery = "SELECT c.course_id, c.title, c.thumbnail, c.short_description, 
    COUNT(e.enrollment_id) as enrollment_count,
    AVG(cr.rating) as avg_rating
    FROM courses c
    LEFT JOIN enrollments e ON c.course_id = e.course_id
    LEFT JOIN course_ratings cr ON c.course_id = cr.course_id
    WHERE c.status = 'Published' AND c.deleted_at IS NULL
    GROUP BY c.course_id
    ORDER BY enrollment_count DESC, avg_rating DESC
    LIMIT 5";
$topCoursesResult = mysqli_query($conn, $topCoursesQuery);

// Get recent activities
$recentActivitiesQuery = "SELECT 'enrollment' as type, e.enrolled_at as date, 
    CONCAT(u.first_name, ' ', u.last_name) as user_name, 
    c.title as content_title,
    u.user_id, c.course_id
    FROM enrollments e
    JOIN users u ON e.user_id = u.user_id
    JOIN courses c ON e.course_id = c.course_id
    WHERE e.deleted_at IS NULL
    UNION
    SELECT 'course_review' as type, crr.created_at as date,
    CONCAT(u.first_name, ' ', u.last_name) as user_name,
    c.title as content_title,
    u.user_id, c.course_id
    FROM course_review_requests crr
    JOIN users u ON crr.requested_by = u.user_id
    JOIN courses c ON crr.course_id = c.course_id
    UNION
    SELECT 'new_course' as type, c.created_at as date,
    CONCAT(u.first_name, ' ', u.last_name) as user_name,
    c.title as content_title,
    u.user_id, c.course_id
    FROM courses c
    JOIN users u ON u.user_id = (SELECT i.user_id FROM instructors i JOIN course_instructors ci ON i.instructor_id = ci.instructor_id WHERE ci.course_id = c.course_id AND ci.is_primary = 1 LIMIT 1)
    WHERE c.deleted_at IS NULL
    ORDER BY date DESC
    LIMIT 8";
$recentActivitiesResult = mysqli_query($conn, $recentActivitiesQuery);

// Function to format numbers with K, M suffixes
function formatNumber($num)
{
  if ($num > 999999) {
    return round($num / 1000000, 1) . 'M';
  } else if ($num > 999) {
    return round($num / 1000, 1) . 'K';
  } else {
    return $num;
  }
}
?>

<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">
  <!-- Welcome Section -->
  <div class="row">
    <div class="col-12 mb-4">
      <div class="card">
        <div class="d-flex align-items-end row">
          <div class="col-md-8">
            <div class="card-body">
              <h4 class="card-title text-primary">Welcome to Learnix Admin Dashboard 🎓</h4>
              <p class="mb-4">
                Here's a summary of your learning platform's performance. You have <span class="fw-bold"><?php echo $coursesData['pending_review']; ?></span> courses pending review and <span class="fw-bold"><?php echo $usersData['new_users_month']; ?></span> new users this month.
              </p>

              <?php if ($coursesData['pending_review'] > 0): ?>
                <a href="reports-overview.php" class="btn btn-sm btn-primary">View Report</a>
              <?php endif; ?>
            </div>
          </div>
          <div class="col-md-4 text-center text-md-right">
            <div class="card-body pb-0 px-0 px-md-4">
              <img src="assets/img/illustrations/education-analytics.svg" height="140" alt="Admin Dashboard" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Stats Cards -->
  <div class="row">
    <!-- Students Card -->
    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="card-info">
              <p class="card-text text-muted mb-0">Total Students</p>
              <div class="d-flex align-items-end mb-2">
                <h4 class="card-title mb-0 me-2"><?php echo formatNumber($usersData['total_students']); ?></h4>
                <?php if ($usersData['new_users_month'] > 0): ?>
                  <small class="text-success">(+<?php echo $usersData['new_users_month']; ?> new)</small>
                <?php endif; ?>
              </div>
            </div>
            <div class="avatar avatar-stats bg-light-primary">
              <i class="bx bx-user-circle text-primary display-6"></i>
            </div>
          </div>
          <a href="students-list.php" class="stretched-link text-muted">View all students</a>
        </div>
      </div>
    </div>

    <!-- Instructors Card -->
    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="card-info">
              <p class="card-text text-muted mb-0">Instructors</p>
              <div class="d-flex align-items-end mb-2">
                <h4 class="card-title mb-0 me-2"><?php echo formatNumber($usersData['total_instructors']); ?></h4>
              </div>
            </div>
            <div class="avatar avatar-stats bg-light-info">
              <i class="bx bx-chalkboard text-info display-6"></i>
            </div>
          </div>
          <a href="instructors-list.php" class="stretched-link text-muted">Manage instructors</a>
        </div>
      </div>
    </div>

    <!-- Courses Card -->
    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="card-info">
              <p class="card-text text-muted mb-0">Active Courses</p>
              <div class="d-flex align-items-end mb-2">
                <h4 class="card-title mb-0 me-2"><?php echo formatNumber($coursesData['published_courses']); ?></h4>
                <?php if ($coursesData['new_courses_month'] > 0): ?>
                  <small class="text-success">(+<?php echo $coursesData['new_courses_month']; ?> new)</small>
                <?php endif; ?>
              </div>
            </div>
            <div class="avatar avatar-stats bg-light-success">
              <i class="bx bx-book-open text-success display-6"></i>
            </div>
          </div>
          <a href="courses-list.php" class="stretched-link text-muted">View all courses</a>
        </div>
      </div>
    </div>

    <!-- Revenue Card -->
    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="card-info">
              <p class="card-text text-muted mb-0">Monthly Revenue</p>
              <div class="d-flex align-items-end mb-2">
                <h4 class="card-title mb-0 me-2">₵<?php echo number_format($revenueData['current_month_revenue'], 0); ?></h4>
                <?php if ($revenueGrowth != 0): ?>
                  <small class="<?php echo $revenueGrowth >= 0 ? 'text-success' : 'text-danger'; ?>">
                    <i class="bx <?php echo $revenueGrowth >= 0 ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt'; ?>"></i>
                    <?php echo abs(round($revenueGrowth)); ?>%
                  </small>
                <?php endif; ?>
              </div>
            </div>
            <div class="avatar avatar-stats bg-light-warning">
              <span class="menu-icon text-warning display-6">₵</span>
            </div>
          </div>
          <a href="finance-transactions.php" class="stretched-link text-muted">View financial details</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Charts & Activity Row -->
  <div class="row">
    <!-- Enrollment Chart -->
    <div class="col-lg-8 mb-4">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Enrollment Trends</h5>
          <div class="dropdown">
            <button type="button" class="btn btn-sm btn-outline-secondary " data-bs-toggle="dropdown" disabled>
              Last 6 Months
            </button>
          </div>
        </div>
        <div class="card-body">
          <canvas id="enrollmentChart" class="chartjs" height="300"></canvas>
        </div>
      </div>
    </div>

    <!-- Quick Stats & Actions -->
    <div class="col-lg-4 mb-4">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title mb-0">Platform Insights</h5>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3 pb-1 border-bottom">
            <div>
              <h6 class="mb-0">Course Completion Rate</h6>
              <small class="text-muted">Average across all courses</small>
            </div>
            <h5 class="text-success mb-0">68%</h5>
          </div>

          <div class="d-flex justify-content-between align-items-center mb-3 pb-1 border-bottom">
            <div>
              <h6 class="mb-0">Average Course Rating</h6>
              <small class="text-muted">Based on student feedback</small>
            </div>
            <h5 class="text-warning mb-0">4.6/5</h5>
          </div>

          <div class="d-flex justify-content-between align-items-center mb-3 pb-1 border-bottom">
            <div>
              <h6 class="mb-0">Pending Reviews</h6>
              <small class="text-muted">Courses awaiting approval</small>
            </div>
            <h5 class="text-primary mb-0"><?php echo $coursesData['pending_review']; ?></h5>
          </div>

          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h6 class="mb-0">Active Instructors</h6>
              <small class="text-muted">Who published content this month</small>
            </div>
            <h5 class="text-info mb-0">24</h5>
          </div>

          <div class="d-grid gap-2 mt-4">
            <a href="reports-overview.php" class="btn btn-primary">View Detailed Reports</a>
            <a href="invite-instructor.php" class="btn btn-outline-primary">Invite Instructor</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Activity & Top Courses Row -->
  <div class="row">
    <!-- Top Performing Courses -->
    <div class="col-md-6 mb-4">
      <div class="card ">
        <div class="card-header d-flex justify-content-between">
          <h5 class="card-title mb-0">Top Performing Courses</h5>
          <a href="courses-list.php?sort=performance" class="text-muted">View All</a>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-borderless">
              <thead class="border-bottom">
                <tr>
                  <th class="text-muted ps-4">Course</th>
                  <th class="text-center text-muted">Students</th>
                  <th class="text-center text-muted">Rating</th>
                </tr>
              </thead>
              <tbody>
                <?php if (mysqli_num_rows($topCoursesResult) > 0): ?>
                  <?php while ($course = mysqli_fetch_assoc($topCoursesResult)): ?>
                    <tr>
                      <td>
                        <div class="d-flex align-items-center ps-3">
                          <div class="avatar avatar-md me-2">
                            <div class="avatar-initial rounded bg-label-primary">
                              <i class="bx bx-book-open"></i>
                            </div>
                          </div>
                          <div class="d-flex flex-column">
                            <h6 class="mb-0 text-truncate" style="max-width: 200px;"><?php echo $course['title']; ?></h6>
                          </div>
                        </div>
                      </td>
                      <td class="text-center"><?php echo $course['enrollment_count']; ?></td>
                      <td class="text-center">
                        <div class="d-flex justify-content-center align-items-center">
                          <span class="me-1"><?php echo number_format($course['avg_rating'] ?? 0, 1); ?></span>
                          <i class="bx bxs-star text-warning"></i>
                        </div>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="3" class="text-center py-3">No courses found</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activities -->
    <div class="col-md-6 mb-4">
      <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center bg-light">
          <h5 class="card-title mb-0 text-dark">Recent Activities</h5>
          <a href="activity-logs.php" class="btn btn-link text-primary">View All</a>
        </div>
        <div class="card-body p-0">
          <div class="timeline-vertical py-2 px-3">
            <?php if (mysqli_num_rows($recentActivitiesResult) > 0): ?>
              <?php while ($activity = mysqli_fetch_assoc($recentActivitiesResult)): ?>
                <div class="timeline-item ps-3 border-start border-2 border-light pb-2 position-relative">
                  <span class="timeline-indicator-advanced position-absolute <?php
                                                                              echo $activity['type'] == 'enrollment' ? 'bg-success' : ($activity['type'] == 'course_review' ? 'bg-warning' : 'bg-primary');
                                                                              ?> text-white rounded-circle">
                    <i class="bx <?php
                                  echo $activity['type'] == 'enrollment' ? 'bx-user-plus' : ($activity['type'] == 'course_review' ? 'bx-revision' : 'bx-book');
                                  ?> fs-6"></i>
                  </span>
                  <div class="timeline-event ps-4 pb-1">
                    <div class="timeline-header d-flex justify-content-between align-items-center mb-1">
                      <h6 class="mb-0 fw-bold text-dark">
                        <?php
                        if ($activity['type'] == 'enrollment') {
                          echo 'New Enrollment';
                        } else if ($activity['type'] == 'course_review') {
                          echo 'Course Review Request';
                        } else {
                          echo 'New Course Added';
                        }
                        ?>
                      </h6>
                      <small class="text-muted"><?php echo date('M d, H:i', strtotime($activity['date'])); ?></small>
                    </div>
                    <p class="mb-0 text-dark">
                      <span class="fw-semibold"><?php echo htmlspecialchars($activity['user_name']); ?></span>
                      <?php
                      if ($activity['type'] == 'enrollment') {
                        echo 'enrolled in';
                      } else if ($activity['type'] == 'course_review') {
                        echo 'requested review for';
                      } else {
                        echo 'created';
                      }
                      ?>
                      <a href="courses.php?id=<?php echo $activity['course_id']; ?>" class="text-primary fw-semibold text-decoration-none">
                        <?php echo htmlspecialchars($activity['content_title']); ?>
                      </a>
                    </p>
                  </div>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <div class="text-center py-3 text-muted">
                <p>No recent activities</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Inline CSS for enhanced styling -->
      <style>
        .timeline-vertical {
          position: relative;
          margin-left: 1rem;
        }

        .timeline-item {
          position: relative;
          margin-bottom: 1rem;
        }

        .timeline-indicator-advanced {
          width: 1.5rem;
          height: 1.5rem;
          display: flex;
          align-items: center;
          justify-content: center;
          left: -0.75rem;
          top: 0.25rem;
          transition: transform 0.2s ease;
        }

        .timeline-item:hover .timeline-indicator-advanced {
          transform: scale(1.1);
        }

        .card {
          border: none;
          border-radius: 0.5rem;
          transition: box-shadow 0.3s ease;
        }

        .card:hover {
          box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .text-primary {
          color: #007bff !important;
        }

        .text-primary:hover {
          color: #0056b3 !important;
        }

        .timeline-event {
          margin-top: -0.1rem;
        }
      </style>
    </div>
  </div>

  <!-- Quick Access Row -->
  <div class="row">
    <div class="col-12 mb-4">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-light">
          <h5 class="card-title mb-0">Quick Access</h5>
        </div>
        <div class="card-body py-4">
          <div class="row g-4">
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
              <a href="create-departments.php" class="text-decoration-none" aria-label="Create a new department">
                <div class="card shadow-none  h-100 text-center quick-access-card">
                  <div class="card-body p-3">
                    <div class="avatar avatar-md mx-auto mb-3 bg-label-info rounded-circle">
                      <i class="bx bx-building fs-3"></i>
                    </div>
                    <h6 class="mb-0 fw-semibold">New Department</h6>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
              <a href="instructors-invite.php" class="text-decoration-none" aria-label="Invite a new instructor">
                <div class="card shadow-none  h-100 text-center quick-access-card">
                  <div class="card-body p-3">
                    <div class="avatar avatar-md mx-auto mb-3 bg-label-success rounded-circle">
                      <i class="bx bx-user-voice fs-3"></i>
                    </div>
                    <h6 class="mb-0 fw-semibold">Invite Instructor</h6>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
              <a href="students-progress.php" class="text-decoration-none" aria-label="Track student progress">
                <div class="card shadow-none  h-100 text-center quick-access-card">
                  <div class="card-body p-3">
                    <div class="avatar avatar-md mx-auto mb-3 bg-label-warning rounded-circle">
                      <i class="bx bx-user fs-3"></i>
                    </div>
                    <h6 class="mb-0 fw-semibold">Track Student</h6>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
              <a href="general-settings.php" class="text-decoration-none" aria-label="Manage general settings">
                <div class="card shadow-none  h-100 text-center quick-access-card">
                  <div class="card-body p-3">
                    <div class="avatar avatar-md mx-auto mb-3 bg-label-primary rounded-circle">
                      <i class="bx bx-cog fs-3"></i>
                    </div>
                    <h6 class="mb-0 fw-semibold">Settings</h6>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
              <a href="assign-department-head.php" class="text-decoration-none" aria-label="Assign a new head of department">
                <div class="card shadow-none  h-100 text-center quick-access-card">
                  <div class="card-body p-3">
                    <div class="avatar avatar-md mx-auto mb-3 bg-label-danger rounded-circle">
                      <i class="bx bx-user-check fs-3"></i>
                    </div>
                    <h6 class="mb-0 fw-semibold">New Head of Dept</h6>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
              <a href="finance-reports.php" class="text-decoration-none" aria-label="View financial reports">
                <div class="card shadow-none h-100 text-center quick-access-card">
                  <div class="card-body p-3">
                    <div class="avatar avatar-md mx-auto mb-3 bg-label-secondary rounded-circle">
                      <i class="bx bx-line-chart fs-3"></i>
                    </div>
                    <h6 class="mb-0 fw-semibold">Financial Reports</h6>
                  </div>
                </div>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Inline CSS for enhanced styling -->
  <style>
    .quick-access-card {
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      border-radius: 0.5rem;
      cursor: pointer;
    }

    .quick-access-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .avatar {
      width: 3rem;
      height: 3rem;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .card-body h6 {
      font-size: 0.95rem;
      color: #333;
    }

    .bg-label-primary {
      background-color: #e7f0ff !important;
      color: #4b84ff !important;
    }

    .bg-label-info {
      background-color: #e6f7fa !important;
      color: #2ca8c2 !important;
    }

    .bg-label-success {
      background-color: #e8f8ed !important;
      color: #2e8b57 !important;
    }

    .bg-label-warning {
      background-color: #fff4e5 !important;
      color: #ff8c00 !important;
    }

    .bg-label-danger {
      background-color: #ffe6e6 !important;
      color: #e63939 !important;
    }

    .bg-label-secondary {
      background-color: #f0f0f0 !important;
      color: #666 !important;
    }

    @media (max-width: 576px) {
      .avatar {
        width: 2.5rem;
        height: 2.5rem;
      }

      .card-body h6 {
        font-size: 0.85rem;
      }
    }
  </style>
</div>
<!-- / Content -->

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>

<!-- Initialize Charts -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Enrollment Chart
    const ctx = document.getElementById('enrollmentChart').getContext('2d');
    const enrollmentChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: <?php echo $chartLabels; ?>,
        datasets: [{
          label: 'New Enrollments',
          data: <?php echo $chartData; ?>,
          backgroundColor: 'rgba(105, 108, 255, 0.1)',
          borderColor: 'rgba(105, 108, 255, 1)',
          borderWidth: 2,
          tension: 0.4,
          fill: true,
          pointStyle: 'circle',
          pointRadius: 5,
          pointHoverRadius: 7
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
            display: false
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.7)',
            padding: 10,
            caretSize: 5,
            displayColors: false
          }
        },
        interaction: {
          intersect: false,
          mode: 'index',
        }
      }
    });
  });
</script>

<?php include_once '../includes/admin/footer.php'; ?>