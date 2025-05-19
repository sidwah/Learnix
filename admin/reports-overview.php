<?php
// Authentication check
require_once '../backend/auth/admin/admin-auth-check.php';

// Set page title
$pageTitle = "Overview Reports - Admin | Learnix";

// Simulated data (replace with actual queries)
$metrics = [
    'total_students' => 1200,
    'total_instructors' => 50,
    'total_courses' => 75,
    'total_enrollments' => 3500,
    'active_students' => 900,
    'pending_instructors' => 5,
    'avg_completion_rate' => 72
];

$enrollment_trends = [
    'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    'data' => [200, 250, 300, 280, 320, 400, 450, 430, 470, 500, 520, 550]
];

$department_distribution = [
    'labels' => ['Computer Science', 'Cybersecurity', 'Web Development', 'Data Science', 'Unassigned'],
    'data' => [400, 200, 150, 100, 50]
];

$top_courses = [
    [
        'id' => 1,
        'title' => 'Python Programming',
        'thumbnail' => 'python.jpg',
        'enrollments' => 800,
        'completion_rate' => 85
    ],
    [
        'id' => 2,
        'title' => 'Data Science 101',
        'thumbnail' => 'data-science.jpg',
        'enrollments' => 600,
        'completion_rate' => 78
    ],
    [
        'id' => 3,
        'title' => 'Network Security',
        'thumbnail' => 'network.jpg',
        'enrollments' => 450,
        'completion_rate' => 70
    ],
    [
        'id' => 4,
        'title' => 'Machine Learning',
        'thumbnail' => 'ml.jpg',
        'enrollments' => 400,
        'completion_rate' => 82
    ],
    [
        'id' => 5,
        'title' => 'Web Design Fundamentals',
        'thumbnail' => 'web-design.jpg',
        'enrollments' => 350,
        'completion_rate' => 65
    ]
];

include_once '../includes/admin/header.php';
include_once '../includes/admin/sidebar.php';
include_once '../includes/admin/navbar.php';
?>

<!-- Custom CSS -->
<style>
  .dashboard-card {
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #f5f7fa 0%, #e4e7eb 100%);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
  }

  .card-icon {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    color: #fff;
  }

  .icon-students {
    background: #a3bffa; /* Soft Blue for Total Students */
  }

  .icon-instructors {
    background: #bef7c8; /* Soft Green for Total Instructors */
  }

  .icon-courses {
    background: #fce8a7; /* Soft Yellow for Total Courses */
  }

  .icon-enrollments {
    background: #d6bcfa; /* Soft Purple for Total Enrollments */
  }

  .chart-container {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  }

  .table-container {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  }

  .course-thumbnail {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
  }

  .filter-dropdown .dropdown-menu {
    min-width: 200px;
  }

  .filter-dropdown .dropdown-item:hover {
    background-color: #f1f3f5;
  }

  canvas {
    max-height: 300px;
  }

  @media (max-width: 768px) {
    .dashboard-card {
      margin-bottom: 20px;
    }
    .chart-container, .table-container {
      padding: 15px;
    }
    canvas {
      max-height: 250px;
    }
  }
</style>

<!-- Content wrapper -->
<div class="content-wrapper">
  <!-- Content -->
  <div class="container-xxl flex-grow-1 container-p-y">
    <!-- Toast Notifications -->
    <div class="bs-toast toast toast-placement-ex m-2 fade bg-danger top-0 end-0" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000" id="errorToast" style="z-index: 9999; position: fixed;">
      <div class="toast-header">
        <i class="bx bx-bell me-2"></i>
        <div class="me-auto fw-semibold">Error</div>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body" id="errorToastMessage"></div>
    </div>

    <div class="bs-toast toast toast-placement-ex m-2 fade bg-success top-0 end-0" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000" id="successToast" style="z-index: 9999; position: fixed;">
      <div class="toast-header">
        <i class="bx bx-check me-2"></i>
        <div class="me-auto fw-semibold">Success</div>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body" id="successToastMessage"></div>
    </div>
    <!-- /Toast Notifications -->

    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="fw-bold">Overview Reports</h4>
      <div class="filter-dropdown">
        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="timeRangeFilter" data-bs-toggle="dropdown" aria-expanded="false">
          Last 30 Days
        </button>
        <ul class="dropdown-menu" aria-labelledby="timeRangeFilter">
          <li><a class="dropdown-item" href="javascript:void(0);" onclick="updateFilter('last30days')">Last 30 Days</a></li>
          <li><a class="dropdown-item" href="javascript:void(0);" onclick="updateFilter('thisMonth')">This Month</a></li>
          <li><a class="dropdown-item" href="javascript:void(0);" onclick="updateFilter('thisYear')">This Year</a></li>
          <li><a class="dropdown-item" href="javascript:void(0);" onclick="updateFilter('allTime')">All Time</a></li>
        </ul>
      </div>
    </div>

    <!-- Metrics Cards -->
    <div class="row mb-4">
      <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
        <div class="card dashboard-card">
          <div class="card-body d-flex align-items-center">
            <div class="card-icon icon-students me-3">
              <i class="bx bx-user fs-3"></i>
            </div>
            <div>
              <h6 class="card-title mb-1">Total Students</h6>
              <h4 class="fw-bold mb-1"><?php echo number_format($metrics['total_students']); ?></h4>
              <small class="text-muted">Active: <?php echo number_format($metrics['active_students']); ?></small>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
        <div class="card dashboard-card">
          <div class="card-body d-flex align-items-center">
            <div class="card-icon icon-instructors me-3">
              <i class="bx bx-chalkboard fs-3"></i>
            </div>
            <div>
              <h6 class="card-title mb-1">Total Instructors</h6>
              <h4 class="fw-bold mb-1"><?php echo number_format($metrics['total_instructors']); ?></h4>
              <small class="text-muted">Pending: <?php echo number_format($metrics['pending_instructors']); ?></small>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
        <div class="card dashboard-card">
          <div class="card-body d-flex align-items-center">
            <div class="card-icon icon-courses me-3">
              <i class="bx bx-book fs-3"></i>
            </div>
            <div>
              <h6 class="card-title mb-1">Total Courses</h6>
              <h4 class="fw-bold mb-1"><?php echo number_format($metrics['total_courses']); ?></h4>
              <small class="text-muted">Active Courses</small>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
        <div class="card dashboard-card">
          <div class="card-body d-flex align-items-center">
            <div class="card-icon icon-enrollments me-3">
              <i class="bx bx-line-chart fs-3"></i>
            </div>
            <div>
              <h6 class="card-title mb-1">Total Enrollments</h6>
              <h4 class="fw-bold mb-1"><?php echo number_format($metrics['total_enrollments']); ?></h4>
              <small class="text-muted">Avg. Completion: <?php echo $metrics['avg_completion_rate']; ?>%</small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts -->
    <div class="row mb-4">
      <div class="col-lg-6 col-md-12 mb-4">
        <div class="chart-container">
          <h6 class="fw-bold mb-3">Enrollment Trends</h6>
          <canvas id="enrollmentTrendsChart" aria-label="Enrollment trends over time"></canvas>
        </div>
      </div>
      <div class="col-lg-6 col-md-12 mb-4">
        <div class="chart-container">
          <h6 class="fw-bold mb-3">Active Students by Department</h6>
          <canvas id="departmentDistributionChart" aria-label="Active students by department"></canvas>
        </div>
      </div>
    </div>

    <!-- Top Courses Table -->
    <div class="row">
      <div class="col-12">
        <div class="table-container">
          <h6 class="fw-bold mb-3">Top Courses by Enrollment</h6>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Course</th>
                  <th>Enrollments</th>
                  <th>Completion Rate</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($top_courses as $course) { ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-center">
                        <img src="../Uploads/course-thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>" 
                             alt="<?php echo htmlspecialchars($course['title']); ?>" 
                             class="course-thumbnail me-3" 
                             aria-label="<?php echo htmlspecialchars($course['title']); ?>" />
                        <span><?php echo htmlspecialchars($course['title']); ?></span>
                      </div>
                    </td>
                    <td><?php echo number_format($course['enrollments']); ?></td>
                    <td><?php echo $course['completion_rate']; ?>%</td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- / Content -->

<?php include_once '../includes/admin/footer.php'; ?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

<script>
  // Show toast function
  function showToast(type, message) {
    const toastEl = document.getElementById(type + 'Toast');
    const messageEl = document.getElementById(type + 'ToastMessage');
    if (toastEl && messageEl) {
      messageEl.textContent = message;
      const toast = new bootstrap.Toast(toastEl);
      toast.show();
    }
  }

  // Initialize Enrollment Trends Chart
  const enrollmentTrendsCtx = document.getElementById('enrollmentTrendsChart').getContext('2d');
  const enrollmentTrendsChart = new Chart(enrollmentTrendsCtx, {
    type: 'line',
    data: {
      labels: <?php echo json_encode($enrollment_trends['labels']); ?>,
      datasets: [{
        label: 'Enrollments',
        data: <?php echo json_encode($enrollment_trends['data']); ?>,
        borderColor: '#696cff',
        backgroundColor: 'rgba(105, 108, 255, 0.1)',
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#fff',
        pointBorderColor: '#696cff',
        pointHoverBackgroundColor: '#696cff',
        pointHoverBorderColor: '#fff'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: 'Number of Enrollments'
          }
        },
        x: {
          title: {
            display: true,
            text: 'Month'
          }
        }
      },
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          backgroundColor: '#2d2f3a',
          titleColor: '#fff',
          bodyColor: '#fff'
        }
      }
    }
  });

  // Initialize Department Distribution Chart
  const departmentDistributionCtx = document.getElementById('departmentDistributionChart').getContext('2d');
  const departmentDistributionChart = new Chart(departmentDistributionCtx, {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($department_distribution['labels']); ?>,
      datasets: [{
        label: 'Active Students',
        data: <?php echo json_encode($department_distribution['data']); ?>,
        backgroundColor: [
          '#696cff',
          '#03c3ec',
          '#71dd37',
          '#ffab00',
          '#ff3e1d'
        ],
        borderColor: '#fff',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: 'Number of Students'
          }
        },
        x: {
          title: {
            display: true,
            text: 'Department'
          }
        }
      },
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          backgroundColor: '#2d2f3a',
          titleColor: '#fff',
          bodyColor: '#fff'
        }
      }
    }
  });

  // Time range filter update
  function updateFilter(range) {
    document.getElementById('timeRangeFilter').textContent = {
      'last30days': 'Last 30 Days',
      'thisMonth': 'This Month',
      'thisYear': 'This Year',
      'allTime': 'All Time'
    }[range];
    showToast('success', `Data updated for ${range.replace(/([A-Z])/g, ' $1').toLowerCase()}`);
    
    // Simulate data update (replace with AJAX)
    console.log(`Updating data for ${range}`);
    // Example AJAX:
    // fetch('../backend/update_reports.php', {
    //   method: 'POST',
    //   headers: { 'Content-Type': 'application/json' },
    //   body: JSON.stringify({ time_range: range })
    // }).then(response => response.json()).then(data => {
    //   updateCharts(data);
    //   showToast('success', `Data updated for ${range}`);
    // }).catch(error => {
    //   showToast('error', 'Error updating data.');
    // });
  }

  // Placeholder for showOverlay and removeOverlay
  function showOverlay(message) {
    console.log(`Showing overlay: ${message}`);
  }

  function removeOverlay() {
    console.log('Removing overlay');
  }
</script>