<?php
// department/instructor-performance.php
include '../includes/department/header.php';

// Ensure database connection is available from header
if (!isset($conn)) {
    die("Database connection not established.");
}

// Get department ID from session
$department_id = $_SESSION['department_id'];

// Fetch instructors in the department
$instructors_query = "
    SELECT i.instructor_id, u.first_name, u.last_name, u.email, di.status
    FROM department_instructors di
    JOIN instructors i ON di.instructor_id = i.instructor_id
    JOIN users u ON i.user_id = u.user_id
    WHERE di.department_id = ? AND di.deleted_at IS NULL
";
$instructors_stmt = $conn->prepare($instructors_query);
$instructors_stmt->bind_param("i", $department_id);
$instructors_stmt->execute();
$instructors_result = $instructors_stmt->get_result();

// Initialize array to store instructor performance data
$instructor_performance = [];
$dashboard_stats = [
    'total_instructors' => 0,
    'active_instructors' => 0,
    'total_students' => 0,
    'total_courses' => 0,
    'avg_completion_rate' => 0,
    'avg_rating' => 0,
    'total_earnings' => 0,
    'top_performer' => [
        'instructor' => ['first_name' => '', 'last_name' => ''],
        'avg_completion_rate' => 0,
        'avg_rating' => 0,
        'total_students' => 0,
        'total_earnings' => 0
    ]
];

$total_completion_sum = 0;
$total_rating_sum = 0;
$instructor_count = 0;

while ($instructor = $instructors_result->fetch_assoc()) {
    $instructor_id = $instructor['instructor_id'];
    $dashboard_stats['total_instructors']++;

    if ($instructor['status'] === 'Active') {
        $dashboard_stats['active_instructors']++;
    }

    // Fetch courses taught by the instructor
    $courses_query = "
        SELECT c.course_id, c.title, ca.total_students, ca.completion_rate, ca.average_rating
        FROM course_instructors ci
        JOIN courses c ON ci.course_id = c.course_id
        LEFT JOIN course_analytics ca ON c.course_id = ca.course_id
        WHERE ci.instructor_id = ? AND ci.deleted_at IS NULL
    ";
    $courses_stmt = $conn->prepare($courses_query);
    $courses_stmt->bind_param("i", $instructor_id);
    $courses_stmt->execute();
    $courses_result = $courses_stmt->get_result();

    $courses = [];
    $total_students = 0;
    $avg_completion_rate = 0;
    $avg_rating = 0;
    $course_count = 0;

    while ($course = $courses_result->fetch_assoc()) {
        $courses[] = $course;
        $total_students += $course['total_students'] ?? 0;
        $avg_completion_rate += $course['completion_rate'] ?? 0;
        $avg_rating += $course['average_rating'] ?? 0;
        $course_count++;
        $dashboard_stats['total_courses']++;
    }

    // Calculate averages
    $avg_completion_rate = $course_count > 0 ? round($avg_completion_rate / $course_count, 2) : 0;
    $avg_rating = $course_count > 0 ? round($avg_rating / $course_count, 2) : 0;

    $dashboard_stats['total_students'] += $total_students;
    $total_completion_sum += $avg_completion_rate;
    $total_rating_sum += $avg_rating;
    $instructor_count++;

    // Fetch earnings
    $earnings_query = "
        SELECT SUM(instructor_share) as total_earnings
        FROM instructor_earnings
        WHERE instructor_id = ? AND status = 'Available'
    ";
    $earnings_stmt = $conn->prepare($earnings_query);
    $earnings_stmt->bind_param("i", $instructor_id);
    $earnings_stmt->execute();
    $earnings_result = $earnings_stmt->get_result();
    $earnings = $earnings_result->fetch_assoc();
    $total_earnings = $earnings['total_earnings'] ?? 0;
    $dashboard_stats['total_earnings'] += $total_earnings;

    // Fetch recent activity
    $activity_query = "
        SELECT action_type, performed_at
        FROM course_activity_logs
        WHERE instructor_id = ?
        ORDER BY performed_at DESC
        LIMIT 5
    ";
    $activity_stmt = $conn->prepare($activity_query);
    $activity_stmt->bind_param("i", $instructor_id);
    $activity_stmt->execute();
    $activity_result = $activity_stmt->get_result();

    $activities = [];
    while ($activity = $activity_result->fetch_assoc()) {
        $activities[] = $activity;
    }

    // Store instructor performance data
    $instructor_data = [
        'instructor' => $instructor,
        'courses' => $courses,
        'total_students' => $total_students,
        'avg_completion_rate' => $avg_completion_rate,
        'avg_rating' => $avg_rating,
        'total_earnings' => $total_earnings,
        'activities' => $activities
    ];

    $instructor_performance[] = $instructor_data;

    // Check if this is the top performer
    if (
        $avg_completion_rate > $dashboard_stats['top_performer']['avg_completion_rate']
    ) {
        $dashboard_stats['top_performer'] = $instructor_data;
    }

    // Clean up
    $courses_stmt->close();
    $earnings_stmt->close();
    $activity_stmt->close();
}

// Calculate overall averages
$dashboard_stats['avg_completion_rate'] = $instructor_count > 0 ? round($total_completion_sum / $instructor_count, 2) : 0;
$dashboard_stats['avg_rating'] = $instructor_count > 0 ? round($total_rating_sum / $instructor_count, 2) : 0;

$instructors_stmt->close();
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
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row align-items-center mb-4">
                <div class="col">
                    <h1 class="h2 mb-0">
                        <i class="bi bi-graph-up text-primary me-2"></i>
                        Instructor Performance Dashboard
                    </h1>
                    <p class="text-muted mb-0"><?php echo htmlspecialchars($_SESSION['department_name']); ?></p>
                </div>
                <div class="col-auto">
                    <span class="badge bg-light text-dark">
                        <i class="bi bi-clock me-1"></i>
                        Last updated: <?php echo date('M d, Y H:i'); ?>
                    </span>
                </div>
            </div>

            <!-- Dashboard Stats Cards -->
            <div class="row g-3 mb-4">
                <!-- Total Instructors -->
                <div class="col-sm-6 col-lg-4">
                    <div class="card h-100 card-hover-shadow">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-lg avatar-soft-primary">
                                        <span class="avatar-initials">
                                            <i class="bi bi-people fs-4"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h3 class="card-title mb-1"><?php echo $dashboard_stats['total_instructors']; ?></h3>
                                    <p class="card-text text-muted mb-0">Total Instructors</p>
                                    <small class="text-success">
                                        <i class="bi bi-check-circle me-1"></i>
                                        <?php echo $dashboard_stats['active_instructors']; ?> Active
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Students -->
                <div class="col-sm-6 col-lg-4">
                    <div class="card h-100 card-hover-shadow">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-lg avatar-soft-info">
                                        <span class="avatar-initials">
                                            <i class="bi bi-mortarboard fs-4"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h3 class="card-title mb-1"><?php echo number_format($dashboard_stats['total_students']); ?></h3>
                                    <p class="card-text text-muted mb-0">Total Students</p>
                                    <small class="text-info">
                                        <i class="bi bi-journal-bookmark me-1"></i>
                                        <?php echo $dashboard_stats['total_courses']; ?> Courses
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Average Performance -->
                <div class="col-sm-6 col-lg-4">
                    <div class="card h-100 card-hover-shadow">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-lg avatar-soft-success">
                                        <span class="avatar-initials">
                                            <i class="bi bi-graph-up-arrow fs-4"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h3 class="card-title mb-1"><?php echo $dashboard_stats['avg_completion_rate']; ?>%</h3>
                                    <p class="card-text text-muted mb-0">Avg. Completion</p>
                                    <small class="text-warning">
                                        <i class="bi bi-star-fill me-1"></i>
                                        <?php echo $dashboard_stats['avg_rating']; ?>/5.0 Rating
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Earnings -->
                <!-- <div class="col-sm-6 col-lg-3">
                    <div class="card h-100 card-hover-shadow">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-lg avatar-soft-warning">
                                        <span class="avatar-initials">
                                            <i class="bi bi-currency-dollar fs-4"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h3 class="card-title mb-1">GHS <?php //echo number_format($dashboard_stats['total_earnings'], 0); ?></h3>
                                    <p class="card-text text-muted mb-0">Total Earnings</p>
                                    <small class="text-success">
                                        <i class="bi bi-trending-up me-1"></i>
                                        Department Revenue
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->
            </div>

            <!-- Top Performer Card -->
            <?php if (
                isset($dashboard_stats['top_performer']) &&
                is_array($dashboard_stats['top_performer']) &&
                isset($dashboard_stats['top_performer']['instructor'])
            ): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-gradient-primary text-white">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h4 class="text-white">
                                            <i class="bi bi-trophy me-2"></i>
                                            Top Performer of the Month
                                        </h4>
                                        <h3 class="text-white mb-2">
                                            <?php echo htmlspecialchars($dashboard_stats['top_performer']['instructor']['first_name'] . ' ' . $dashboard_stats['top_performer']['instructor']['last_name']); ?>
                                        </h3>
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <p class="text-white-70 mb-1">Completion Rate</p>
                                                <h5 class="text-white"><?php echo $dashboard_stats['top_performer']['avg_completion_rate']; ?>%</h5>
                                            </div>
                                            <div class="col-sm-3">
                                                <p class="text-white-70 mb-1">Rating</p>
                                                <h5 class="text-white"><?php echo $dashboard_stats['top_performer']['avg_rating']; ?>/5.0</h5>
                                            </div>
                                            <div class="col-sm-3">
                                                <p class="text-white-70 mb-1">Students</p>
                                                <h5 class="text-white"><?php echo $dashboard_stats['top_performer']['total_students']; ?></h5>
                                            </div>
                                            <div class="col-sm-3">
                                                <p class="text-white-70 mb-1">Earnings</p>
                                                <h5 class="text-white">GHS <?php echo number_format($dashboard_stats['top_performer']['total_earnings'], 0); ?></h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto d-none d-lg-block">
                                        <i class="bi bi-award display-4 text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Charts Row -->
            <div class="row g-3 mb-4">
                <!-- Completion Rate Chart -->
                <div class="col-lg-8">
                    <div class="card h-100">
                        <div class="card-header border-bottom">
                            <h4 class="card-title mb-0">
                                <i class="bi bi-bar-chart me-2"></i>
                                Instructor Performance Comparison
                            </h4>
                        </div>
                        <div class="card-body">
                            <canvas id="performanceChart" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Rating Distribution -->
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header border-bottom">
                            <h4 class="card-title mb-0">
                                <i class="bi bi-pie-chart me-2"></i>
                                Rating Distribution
                            </h4>
                        </div>
                        <div class="card-body d-flex align-items-center justify-content-center">
                            <canvas id="ratingChart" style="height: 250px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instructor Details Table -->
            <div class="card">
                <div class="card-header border-bottom">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title mb-0">
                                <i class="bi bi-table me-2"></i>
                                Detailed Performance Analysis
                            </h4>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-download me-1"></i>
                                Export Report
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($instructor_performance)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <h5 class="mt-3 text-muted">No instructors found</h5>
                            <p class="text-muted">No instructors are currently assigned to this department.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th>Instructor</th>
                                        <th>Students</th>
                                        <th>Earnings</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($instructor_performance as $perf): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-soft-primary avatar-circle me-3">
                                                        <span class="avatar-initials">
                                                            <?php echo strtoupper(substr($perf['instructor']['first_name'], 0, 1) . substr($perf['instructor']['last_name'], 0, 1)); ?>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($perf['instructor']['first_name'] . ' ' . $perf['instructor']['last_name']); ?></h6>
                                                        <small class="text-muted"><?php echo htmlspecialchars($perf['instructor']['email']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <h6 class="mb-0"><?php echo $perf['total_students']; ?></h6>
                                                    <small class="text-muted"><?php echo count($perf['courses']); ?> courses</small>
                                                </div>
                                            </td>
                                            <td>
                                                <h6 class="text-success mb-0">GHS <?php echo number_format($perf['total_earnings'], 2); ?></h6>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-ghost-secondary btn-sm view-details-btn" title="View Details" data-bs-toggle="modal" data-bs-target="#instructorModal" data-instructor-id="<?php echo $perf['instructor']['instructor_id']; ?>">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Instructor Details Modal -->
<div class="modal fade" id="instructorModal" tabindex="-1" aria-labelledby="instructorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="instructorModalLabel">Instructor Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Instructor Information</h6>
                        <p><strong>Name:</strong> <span id="modal-instructor-name"></span></p>
                        <p><strong>Email:</strong> <span id="modal-instructor-email"></span></p>
                        <p><strong>Status:</strong> <span id="modal-instructor-status"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Performance Metrics</h6>
                        <p><strong>Completion Rate:</strong> <span id="modal-completion-rate"></span>%</p>
                        <p><strong>Rating:</strong> <span id="modal-rating"></span>/5.0</p>
                    </div>
                </div>
                <hr>
                <h6>Recent Activity</h6>
                <div id="modal-recent-activity">
                    <p class="text-muted">Loading activities...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- End Instructor Details Modal -->
    <!-- End Content -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- Chart.js Script -->
<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.35.3/dist/apexcharts.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Declare variables outside the conditional to avoid redeclaration
        let performanceCtx, ratingCtx;
        <?php if (!empty($instructor_performance)): ?>
            // Performance Comparison Chart
            performanceCtx = document.getElementById('performanceChart').getContext('2d');
            const performanceLabels = <?php
                                        $labels = array_map(function ($perf) {
                                            return $perf['instructor']['first_name'] . ' ' . substr($perf['instructor']['last_name'], 0, 1) . '.';
                                        }, $instructor_performance);
                                        echo json_encode($labels);
                                        ?>;

            const completionData = <?php
                                    echo json_encode(array_map(function ($perf) {
                                        return $perf['avg_completion_rate'];
                                    }, $instructor_performance));
                                    ?>;

            const ratingData = <?php
                                echo json_encode(array_map(function ($perf) {
                                    return $perf['avg_rating'] * 20;
                                }, $instructor_performance)); // Scale to 100
                                ?>;

            // Check if we have valid data before creating the chart
            if (performanceLabels.length > 0 && completionData.length > 0) {
                new Chart(performanceCtx, {
                    type: 'bar',
                    data: {
                        labels: performanceLabels,
                        datasets: [{
                            label: 'Completion Rate (%)',
                            data: completionData,
                            backgroundColor: 'rgba(54, 162, 235, 0.8)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1,
                            borderRadius: 4
                        }, {
                            label: 'Rating (scaled)',
                            data: ratingData,
                            backgroundColor: 'rgba(255, 206, 86, 0.8)',
                            borderColor: 'rgba(255, 206, 86, 1)',
                            borderWidth: 1,
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                display: true
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        if (context.datasetIndex === 1) {
                                            return 'Rating: ' + (context.parsed.y / 20).toFixed(1) + '/5.0';
                                        }
                                        return context.dataset.label + ': ' + context.parsed.y + '%';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                display: true,
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                display: true,
                                beginAtZero: true,
                                max: 100,
                                grid: {
                                    display: true,
                                    color: 'rgba(0, 0, 0, 0.1)'
                                },
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    },
                                    stepSize: 20
                                }
                            }
                        }
                    }
                });
            } else {
                // Display a message if no data is available
                performanceCtx.canvas.parentNode.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100"><p class="text-muted">No performance data available</p></div>';
            }

            // Rating Distribution Pie Chart
            ratingCtx = document.getElementById('ratingChart').getContext('2d');
            const ratings = <?php echo json_encode(array_map(function ($perf) {
                                return $perf['avg_rating'];
                            }, $instructor_performance)); ?>;

            // Check if we have valid rating data
            if (ratings.length > 0) {
                // Group ratings into ranges
                let excellent = 0,
                    good = 0,
                    average = 0,
                    poor = 0;
                ratings.forEach(rating => {
                    const numRating = parseFloat(rating) || 0;
                    if (numRating >= 4.5) excellent++;
                    else if (numRating >= 3.5) good++;
                    else if (numRating >= 2.5) average++;
                    else poor++;
                });

                new Chart(ratingCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Excellent (4.5+)', 'Good (3.5-4.4)', 'Average (2.5-3.4)', 'Poor (<2.5)'],
                        datasets: [{
                            data: [excellent, good, average, poor],
                            backgroundColor: [
                                'rgba(40, 167, 69, 0.8)',
                                'rgba(54, 162, 235, 0.8)',
                                'rgba(255, 193, 7, 0.8)',
                                'rgba(220, 53, 69, 0.8)'
                            ],
                            borderColor: [
                                'rgba(40, 167, 69, 1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 193, 7, 1)',
                                'rgba(220, 53, 69, 1)'
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                        return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                // Display a message if no rating data is available
                ratingCtx.canvas.parentNode.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100"><p class="text-muted">No rating data available</p></div>';
            }
        <?php else: ?>
            // Handle case when no instructor performance data exists
            performanceCtx = document.getElementById('performanceChart');
            ratingCtx = document.getElementById('ratingChart');

            if (performanceCtx) {
                performanceCtx.parentNode.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100"><p class="text-muted">No instructor data available</p></div>';
            }

            if (ratingCtx) {
                ratingCtx.parentNode.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100"><p class="text-muted">No rating data available</p></div>';
            }
        <?php endif; ?>

         // Modal data population
    const viewButtons = document.querySelectorAll('.view-details-btn');
    const instructorData = <?php echo json_encode($instructor_performance); ?>;

    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const instructorId = this.getAttribute('data-instructor-id');
            const instructor = instructorData.find(perf => perf.instructor.instructor_id == instructorId);

            if (instructor) {
                // Populate modal fields
                document.getElementById('modal-instructor-name').textContent = 
                    `${instructor.instructor.first_name} ${instructor.instructor.last_name}`;
                document.getElementById('modal-instructor-email').textContent = 
                    instructor.instructor.email;
                document.getElementById('modal-instructor-status').innerHTML = 
                    `<span class="badge ${instructor.instructor.status === 'Active' ? 'bg-success' : 'bg-secondary'}">
                        <i class="bi bi-${instructor.instructor.status === 'Active' ? 'check-circle' : 'pause-circle'} me-1"></i>
                        ${instructor.instructor.status}
                    </span>`;
                document.getElementById('modal-completion-rate').textContent = 
                    instructor.avg_completion_rate;
                document.getElementById('modal-rating').textContent = 
                    instructor.avg_rating;

                // Populate recent activities
                const activityContainer = document.getElementById('modal-recent-activity');
                if (instructor.activities.length === 0) {
                    activityContainer.innerHTML = '<p class="text-muted"><i class="bi bi-clock me-1"></i>No recent activity</p>';
                } else {
                    activityContainer.innerHTML = instructor.activities.map(activity => `
                        <div class="mb-2">
                            <strong>${activity.action_type}</strong><br>
                            <small class="text-muted">${new Date(activity.performed_at).toLocaleString('en-US', {
                                month: 'short',
                                day: '2-digit',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            })}</small>
                        </div>
                    `).join('<hr class="my-2">');
                }
            }
        });
    });
    });
</script>

<style>
    .card-hover-shadow {
        transition: all 0.3s ease;
    }

    .card-hover-shadow:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    }

    .text-white-70 {
        color: rgba(255, 255, 255, 0.7) !important;
    }

    .avatar-soft-primary {
        background-color: rgba(0, 123, 255, 0.1);
        color: #007bff;
    }

    .avatar-soft-info {
        background-color: rgba(23, 162, 184, 0.1);
        color: #17a2b8;
    }

    .avatar-soft-success {
        background-color: rgba(40, 167, 69, 0.1);
        color: #28a745;
    }

    .avatar-soft-warning {
        background-color: rgba(255, 193, 7, 0.1);
        color: #ffc107;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.025);
    }

    .btn-ghost-secondary {
        color: #6c757d;
        background-color: transparent;
        border-color: transparent;
    }

    .btn-ghost-secondary:hover {
        color: #495057;
        background-color: rgba(108, 117, 125, 0.1);
        border-color: transparent;
    }
</style>

<?php include '../includes/department/footer.php'; ?>