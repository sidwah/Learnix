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
     WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)) as active_students
FROM users
WHERE role = 'student' AND deleted_at IS NULL";
$studentResult = mysqli_query($conn, $studentQuery);
$studentStats = mysqli_fetch_assoc($studentResult);

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

// Convert data to JSON for chart
$enrollmentChartData = json_encode($monthlyEnrollmentData);
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
              <a class="dropdown-item" href="#">Export Data</a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex flex-column align-items-start gap-1">
              <h2 class="mb-0"><?php echo number_format($studentStats['total_students']); ?></h2>
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
                  <h5 class="mb-0"><?php echo number_format($studentStats['active_students']); ?></h5>
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
                  <h5 class="mb-0"><?php echo number_format($studentStats['new_students_month']); ?></h5>
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
            <p class="mb-0">Active Enrollments</p>
            <p class="mb-0 text-success"><?php echo number_format($enrollmentStats['active_enrollments']); ?> (<?php echo round(($enrollmentStats['active_enrollments'] / max(1, $enrollmentStats['total_enrollments'])) * 100); ?>%)</p>
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
              <a class="dropdown-item" href="#">Export Data</a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex flex-column align-items-start gap-1">
              <h2 class="mb-0"><?php echo round($completionRateStats['avg_completion_percentage']); ?>%</h2>
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
                  <h5 class="mb-0"><?php echo number_format($completionRateStats['fully_completed']); ?></h5>
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
                  <h5 class="mb-0"><?php echo number_format($completionRateStats['total_enrollments']); ?></h5>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- Charts and Tables -->
  <div class="row">
    <!-- Enrollment Trends -->
    <div class="col-lg-6 mb-4">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title m-0 me-2">Student Enrollment Trends</h5>
          <div class="dropdown">
            <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
              <a class="dropdown-item" href="#">View Detailed Report</a>
              <a class="dropdown-item" href="#">Export Data</a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <canvas id="enrollmentTrendsChart" height="300"></canvas>
        </div>
      </div>
    </div>

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
              <a class="dropdown-item" href="#">Export Data</a>
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
              <tbody>
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
  </div>
</div>
<!-- / Content -->

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Initialize chart
  let enrollmentChart;

  // Function to initialize or update chart
  function initializeChart(enrollmentData) {
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

  // Function to update UI with new data
  function updateUI(data) {
    // Update Students Stats
    document.querySelector('.card-body .mb-0').textContent = data.studentStats.total_students.toLocaleString();
    document.querySelector('.card-body ul li:nth-child(1) h5').textContent = data.studentStats.active_students.toLocaleString();
    document.querySelector('.card-body ul li:nth-child(2) h5').textContent = data.studentStats.new_students_month.toLocaleString();

    // Update Enrollments Stats
    document.querySelector('.col-xl-4:nth-child(2) .card-body .mb-0').textContent = data.enrollmentStats.total_enrollments.toLocaleString();
    document.querySelector('.col-xl-4:nth-child(2) .card-body ul li:nth-child(1) h5').textContent = data.enrollmentStats.enrollments_today.toLocaleString();
    document.querySelector('.col-xl-4:nth-child(2) .card-body ul li:nth-child(2) h5').textContent = data.enrollmentStats.enrollments_week.toLocaleString();
    document.querySelector('.col-xl-4:nth-child(2) .card-body ul li:nth-child(3) h5').textContent = data.enrollmentStats.enrollments_month.toLocaleString();
    document.querySelector('.col-xl-4:nth-child(2) .card-body .mt-3 p:last-child').textContent = 
      `${data.enrollmentStats.active_enrollments.toLocaleString()} (${Math.round((data.enrollmentStats.active_enrollments / max(1, data.enrollmentStats.total_enrollments)) * 100)}%)`;

    // Update Completion Stats
    document.querySelector('.col-xl-4:nth-child(3) .card-body .mb-0').textContent = Math.round(data.completionRateStats.avg_completion_percentage) + '%';
    document.querySelector('.col-xl-4:nth-child(3) .card-body ul li:nth-child(1) h5').textContent = data.completionRateStats.fully_completed.toLocaleString();
    document.querySelector('.col-xl-4:nth-child(3) .card-body ul li:nth-child(2) h5').textContent = data.completionRateStats.total_enrollments.toLocaleString();

    // Update chart
    initializeChart(data.monthlyEnrollmentData);
  }

  // Function to fetch filtered data
  function fetchFilteredData(period) {
    fetch('../backend/admin/reports_filter.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `period=${encodeURIComponent(period)}&section=students`
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
  function exportData(period, section = 'students') {
    fetch(`../backend/admin/reports_filter.php?export=csv&section=${section}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `period=${encodeURIComponent(period)}`
    })
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        console.error('Error:', data.error);
        return;
      }

      // Create CSV content
      let csv = 'Category,Metric,Value\n';
      if (section === 'students') {
        csv += `Students,Total Students,${data.studentStats.total_students}\n`;
        csv += `Students,Active Students,${data.studentStats.active_students}\n`;
        csv += `Students,New Students This Month,${data.studentStats.new_students_month}\n`;
      } else if (section === 'student_enrollments') {
        csv += `Enrollments,Total Enrollments,${data.enrollmentStats.total_enrollments}\n`;
        csv += `Enrollments,Enrollments Today,${data.enrollmentStats.enrollments_today}\n`;
        csv += `Enrollments,Enrollments This Week,${data.enrollmentStats.enrollments_week}\n`;
        csv += `Enrollments,Enrollments This Month,${data.enrollmentStats.enrollments_month}\n`;
        csv += `Enrollments,Active Enrollments,${data.enrollmentStats.active_enrollments}\n`;
      } else if (section === 'completion') {
        csv += `Completion,Average Completion Rate,${Math.round(data.completionRateStats.avg_completion_percentage)}%\n`;
        csv += `Completion,Fully Completed Courses,${data.completionRateStats.fully_completed}\n`;
        csv += `Completion,Total Enrollments,${data.completionRateStats.total_enrollments}\n`;
      } else if (section === 'top_students') {
        csv = 'Student Name,Enrollments,Completed Courses,Average Completion\n';
        data.topStudents.forEach(student => {
          csv += `"${student.student_name}",${student.enrollment_count},${student.completed_courses},${Math.round(student.avg_completion)}%\n`;
        });
      }

      // Create and trigger download
      const blob = new Blob([csv], { type: 'text/csv' });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.setAttribute('href', url);
      a.setAttribute('download', `student_reports_${section}_${period}_${new Date().toISOString().split('T')[0]}.csv`);
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
  exportButton.addEventListener('click', () => exportData(currentPeriod, 'students'));
  document.querySelector('.card-body .row.align-items-center .col-md-6.text-md-end').appendChild(exportButton);

  // Dropdown Export Data Links
  document.querySelectorAll('.dropdown-menu a.dropdown-item').forEach(item => {
    if (item.textContent.trim() === 'Export Data') {
      item.addEventListener('click', function(e) {
        e.preventDefault();
        const card = this.closest('.card');
        let section;
        if (card.querySelector('.card-title').textContent.includes('Students')) section = 'students';
        else if (card.querySelector('.card-title').textContent.includes('Enrollments')) section = 'student_enrollments';
        else if (card.querySelector('.card-title').textContent.includes('Completion')) section = 'completion';
        else if (card.querySelector('.card-title').textContent.includes('Top Performing Students')) section = 'top_students';
        if (section) {
          exportData(currentPeriod, section);
        }
      });
    }
  });

  // Initial data load
  initializeChart(<?php echo $enrollmentChartData; ?>);
});
</script>

<?php include_once '../includes/admin/footer.php'; ?>