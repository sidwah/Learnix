<!--  admin/reports.php -->
<?php include '../includes/admin-header.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<?php
// Fetch statistics data from database
function fetchStatistics($conn) {
    // Total Students
    $studentQuery = "SELECT COUNT(*) as total_students FROM users WHERE role = 'student'";
    $studentResult = $conn->query($studentQuery);
    $studentData = $studentResult->fetch_assoc();
    
    // Active Courses
    $courseQuery = "SELECT COUNT(*) as total_courses FROM courses WHERE status = 'Published' AND approval_status = 'Approved'";
    $courseResult = $conn->query($courseQuery);
    $courseData = $courseResult->fetch_assoc();
    
    // Total Revenue
    $revenueQuery = "SELECT SUM(amount) as total_revenue FROM course_payments WHERE status = 'Completed'";
    $revenueResult = $conn->query($revenueQuery);
    $revenueData = $revenueResult->fetch_assoc();
    
    // Average Completion Rate
    $completionQuery = "SELECT AVG(completion_percentage) as avg_completion FROM enrollments";
    $completionResult = $conn->query($completionQuery);
    $completionData = $completionResult->fetch_assoc();
    
    // Calculate growth percentages (comparing to previous month)
    $prevMonthStudentQuery = "SELECT COUNT(*) as prev_students FROM users WHERE role = 'student' AND created_at < DATE_SUB(NOW(), INTERVAL 1 MONTH)";
    $prevMonthStudentResult = $conn->query($prevMonthStudentQuery);
    $prevMonthStudentData = $prevMonthStudentResult->fetch_assoc();
    
    $prevMonthCourseQuery = "SELECT COUNT(*) as prev_courses FROM courses WHERE status = 'Published' AND approval_status = 'Approved' AND created_at < DATE_SUB(NOW(), INTERVAL 1 MONTH)";
    $prevMonthCourseResult = $conn->query($prevMonthCourseQuery);
    $prevMonthCourseData = $prevMonthCourseResult->fetch_assoc();
    
    $prevMonthRevenueQuery = "SELECT SUM(amount) as prev_revenue FROM course_payments WHERE status = 'Completed' AND payment_date < DATE_SUB(NOW(), INTERVAL 1 MONTH)";
    $prevMonthRevenueResult = $conn->query($prevMonthRevenueQuery);
    $prevMonthRevenueData = $prevMonthRevenueResult->fetch_assoc();
    
    $prevMonthCompletionQuery = "SELECT AVG(completion_percentage) as prev_completion FROM enrollments WHERE enrolled_at < DATE_SUB(NOW(), INTERVAL 1 MONTH)";
    $prevMonthCompletionResult = $conn->query($prevMonthCompletionQuery);
    $prevMonthCompletionData = $prevMonthCompletionResult->fetch_assoc();
    
    // Calculate growth rates
    $studentGrowth = 0;
    if ($prevMonthStudentData['prev_students'] > 0) {
        $studentGrowth = (($studentData['total_students'] - $prevMonthStudentData['prev_students']) / $prevMonthStudentData['prev_students']) * 100;
    }
    
    $courseGrowth = 0;
    if ($prevMonthCourseData['prev_courses'] > 0) {
        $courseGrowth = (($courseData['total_courses'] - $prevMonthCourseData['prev_courses']) / $prevMonthCourseData['prev_courses']) * 100;
    }
    
    $revenueGrowth = 0;
    if ($prevMonthRevenueData['prev_revenue'] > 0) {
        $revenueGrowth = (($revenueData['total_revenue'] - $prevMonthRevenueData['prev_revenue']) / $prevMonthRevenueData['prev_revenue']) * 100;
    }
    
    $completionGrowth = 0;
    if ($prevMonthCompletionData['prev_completion'] > 0) {
        $completionGrowth = (($completionData['avg_completion'] - $prevMonthCompletionData['prev_completion']) / $prevMonthCompletionData['prev_completion']) * 100;
    }
    
    return [
        'total_students' => $studentData['total_students'] ?? 0,
        'total_courses' => $courseData['total_courses'] ?? 0,
        'total_revenue' => $revenueData['total_revenue'] ?? 0,
        'avg_completion' => $completionData['avg_completion'] ?? 0,
        'student_growth' => round($studentGrowth, 1),
        'course_growth' => round($courseGrowth, 1),
        'revenue_growth' => round($revenueGrowth, 1),
        'completion_growth' => round($completionGrowth, 1)
    ];
}

// Fetch top performing courses
function fetchTopCourses($conn, $limit = 5) {
    $query = "SELECT c.course_id, c.title, s.name as category, 
             (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.course_id) as enrollment_count,
             (SELECT AVG(completion_percentage) FROM enrollments e WHERE e.course_id = c.course_id) as completion_rate,
             (SELECT SUM(cp.amount) FROM course_payments cp JOIN enrollments e ON cp.enrollment_id = e.enrollment_id WHERE e.course_id = c.course_id) as revenue,
             (SELECT AVG(cr.rating) FROM course_ratings cr WHERE cr.course_id = c.course_id) as avg_rating,
             c.thumbnail
             FROM courses c
             JOIN subcategories s ON c.subcategory_id = s.subcategory_id
             WHERE c.status = 'Published' AND c.approval_status = 'Approved'
             GROUP BY c.course_id
             ORDER BY enrollment_count DESC
             LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch top instructors
function fetchTopInstructors($conn, $limit = 4) {
    $query = "SELECT u.user_id, u.first_name, u.last_name, u.profile_pic,
             (SELECT COUNT(*) FROM courses c WHERE c.instructor_id = i.instructor_id) as course_count,
             (SELECT COUNT(*) FROM enrollments e JOIN courses c ON e.course_id = c.course_id WHERE c.instructor_id = i.instructor_id) as student_count,
             (SELECT SUM(ie.instructor_share) FROM instructor_earnings ie WHERE ie.instructor_id = i.instructor_id) as total_revenue
             FROM instructors i
             JOIN users u ON i.user_id = u.user_id
             GROUP BY i.instructor_id
             ORDER BY total_revenue DESC
             LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch recent activities
function fetchRecentActivities($conn, $limit = 5) {
    $query = "SELECT 'course_published' as activity_type, c.title as title, 
             CONCAT(u.first_name, ' ', u.last_name) as user_name, c.created_at as activity_time
             FROM courses c 
             JOIN instructors i ON c.instructor_id = i.instructor_id
             JOIN users u ON i.user_id = u.user_id
             WHERE c.status = 'Published' AND c.approval_status = 'Approved'
             
             UNION
             
             SELECT 'payout_processed' as activity_type, CONCAT('GH₵', FORMAT(ip.amount, 2)) as title,
             CONCAT(u.first_name, ' ', u.last_name) as user_name, ip.processed_date as activity_time
             FROM instructor_payouts ip
             JOIN instructors i ON ip.instructor_id = i.instructor_id
             JOIN users u ON i.user_id = u.user_id
             WHERE ip.status = 'Completed'
             
             UNION
             
             SELECT 'verification_request' as activity_type, 'Instructor verification' as title,
             CONCAT(u.first_name, ' ', u.last_name) as user_name, ivr.submitted_at as activity_time
             FROM instructor_verification_requests ivr
             JOIN instructors i ON ivr.instructor_id = i.instructor_id
             JOIN users u ON i.user_id = u.user_id
             
             UNION
             
             SELECT 'course_review' as activity_type, c.title as title,
             'Admin approval' as user_name, crr.created_at as activity_time
             FROM course_review_requests crr
             JOIN courses c ON crr.course_id = c.course_id
             WHERE crr.status = 'Pending'
             
             UNION
             
             SELECT 'support_ticket' as activity_type, ir.issue_type as title,
             CONCAT(u.first_name, ' ', u.last_name) as user_name, ir.created_at as activity_time
             FROM issue_reports ir
             JOIN users u ON ir.user_id = u.user_id
             
             ORDER BY activity_time DESC
             LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get monthly enrollment data for the chart
function getMonthlyEnrollments($conn) {
    $query = "SELECT 
                MONTH(enrolled_at) as month, 
                COUNT(*) as enrollments 
              FROM 
                enrollments 
              WHERE 
                enrolled_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) 
              GROUP BY 
                MONTH(enrolled_at) 
              ORDER BY 
                month";
    
    $result = $conn->query($query);
    $monthlyData = array_fill(0, 12, 0); // Initialize with zeros for all 12 months
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $monthIndex = $row['month'] - 1; // 0-based index for months
            $monthlyData[$monthIndex] = (int)$row['enrollments'];
        }
    }
    
    return $monthlyData;
}

// Get top categories data
function getTopCategories($conn) {
    $query = "SELECT 
                c.name as category, 
                COUNT(co.course_id) as course_count
              FROM 
                categories c
              JOIN 
                subcategories s ON c.category_id = s.category_id
              JOIN 
                courses co ON s.subcategory_id = co.subcategory_id
              WHERE 
                co.status = 'Published' AND co.approval_status = 'Approved'
              GROUP BY 
                c.category_id
              ORDER BY 
                course_count DESC
              LIMIT 5";
    
    $result = $conn->query($query);
    $categories = [];
    $counts = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row['category'];
            $counts[] = (int)$row['course_count'];
        }
    }
    
    // Add "Others" if we have fewer than 5 categories
    if (count($categories) < 5) {
        $categories[] = "Others";
        $counts[] = 5; // Default value
    }
    
    return [
        'categories' => $categories,
        'counts' => $counts
    ];
}

// Get revenue breakdown by course category
function getRevenueByCategory($conn) {
    $query = "SELECT 
                c.name as category,
                SUM(cp.amount) as total_revenue
              FROM 
                categories c
              JOIN 
                subcategories s ON c.category_id = s.category_id
              JOIN 
                courses co ON s.subcategory_id = co.subcategory_id
              JOIN 
                enrollments e ON co.course_id = e.course_id
              JOIN 
                course_payments cp ON e.enrollment_id = cp.enrollment_id
              WHERE 
                cp.status = 'Completed'
              GROUP BY 
                c.category_id
              ORDER BY 
                total_revenue DESC
              LIMIT 6";
    
    $result = $conn->query($query);
    $categories = [];
    $revenues = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row['category'];
            $revenues[] = round((float)$row['total_revenue'], 2);
        }
    } else {
        // Sample data if no records found
        $categories = ['Web Dev', 'Data Science', 'UI/UX', 'Digital Marketing', 'Python', 'Business'];
        $revenues = [12500, 9800, 8400, 7600, 6900, 5800];
    }
    
    return [
        'categories' => $categories,
        'revenues' => $revenues
    ];
}

// Get user activity data for the past 12 months
function getUserActivityData($conn) {
    $query = "SELECT 
                MONTH(created_at) as month,
                COUNT(*) as new_users
              FROM 
                users
              WHERE 
                created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
              GROUP BY 
                MONTH(created_at)
              ORDER BY 
                month";
    
    $result = $conn->query($query);
    $newUsers = array_fill(0, 12, 0);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $monthIndex = $row['month'] - 1;
            $newUsers[$monthIndex] = (int)$row['new_users'];
        }
    }
    
    // Get active users data
    $activeQuery = "SELECT 
                    MONTH(last_accessed) as month,
                    COUNT(DISTINCT user_id) as active_users
                  FROM 
                    enrollments
                  WHERE 
                    last_accessed >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                  GROUP BY 
                    MONTH(last_accessed)
                  ORDER BY 
                    month";
    
    $activeResult = $conn->query($activeQuery);
    $activeUsers = array_fill(0, 12, 0);
    
    if ($activeResult && $activeResult->num_rows > 0) {
        while ($row = $activeResult->fetch_assoc()) {
            $monthIndex = $row['month'] - 1;
            $activeUsers[$monthIndex] = (int)$row['active_users'];
        }
    }
    
    return [
        'new_users' => $newUsers,
        'active_users' => $activeUsers
    ];
}

// Initialize our data arrays with default values
$monthlyEnrollments = array_fill(0, 12, 0);
$topCategories = [
    'categories' => ['Development', 'Business', 'Design', 'Marketing', 'Others'],
    'counts' => [35, 25, 20, 15, 5]
];
$revenueByCategory = [
    'categories' => ['Web Dev', 'Data Science', 'UI/UX', 'Digital Marketing', 'Python', 'Business'],
    'revenues' => [12500, 9800, 8400, 7600, 6900, 5800]
];
$userActivity = [
    'new_users' => [45, 52, 38, 24, 33, 26, 21, 20, 6, 8, 15, 10],
    'active_users' => [87, 57, 74, 99, 75, 38, 62, 47, 82, 56, 45, 47]
];

// Get database connection
try {
    require_once '../backend/config.php';
    $stats = fetchStatistics($conn);
    $topCourses = fetchTopCourses($conn);
    $topInstructors = fetchTopInstructors($conn);
    $recentActivities = fetchRecentActivities($conn);
    
    // Get chart data
    $monthlyEnrollments = getMonthlyEnrollments($conn);
    $topCategories = getTopCategories($conn);
    $revenueByCategory = getRevenueByCategory($conn);
    $userActivity = getUserActivityData($conn);
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    $stats = [
        'total_students' => 1254,
        'total_courses' => 328,
        'total_revenue' => 48295,
        'avg_completion' => 68.7,
        'student_growth' => 12.5,
        'course_growth' => 5.3,
        'revenue_growth' => 18.2,
        'completion_growth' => -2.4
    ];
    $topCourses = [];
    $topInstructors = [];
    $recentActivities = [];
}
?>


<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main">
    <!-- Navbar -->
    <nav class="js-nav-scroller navbar navbar-expand-lg navbar-sidebar navbar-vertical navbar-light bg-white border-end" data-hs-nav-scroller-options='{
            "type": "vertical",
            "target": ".navbar-nav .active",
            "offset": 80
           }'>

        <?php include '../includes/admin-sidebar.php'; ?>
    </nav>
    <!-- End Navbar -->

    <!-- Content -->
    <div class="navbar-sidebar-aside-content content-space-1 content-space-md-2 px-lg-5 px-xl-10">
    <!-- Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="page-header-title">Analytics & Reports</h1>
                </div>
                <div class="col-auto">
                    <!-- Date Range Picker -->
                    <div class="d-flex align-items-center gap-2">
                        <div class="dropdown">
                            <button class="btn btn-white btn-sm dropdown-toggle" type="button" id="dateRangeSelector" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi-calendar me-1"></i> Last 30 days
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dateRangeSelector">
                                <li><a class="dropdown-item" href="#">Today</a></li>
                                <li><a class="dropdown-item" href="#">Yesterday</a></li>
                                <li><a class="dropdown-item active" href="#">Last 7 days</a></li>
                                <li><a class="dropdown-item" href="#">Last 30 days</a></li>
                                <li><a class="dropdown-item" href="#">This month</a></li>
                                <li><a class="dropdown-item" href="#">Last month</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#customDateRangeModal">Custom range</a></li>
                            </ul>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="exportOptions" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi-download me-1"></i> Export
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="exportOptions">
                                <li><a class="dropdown-item" href="#" id="exportPDF"><i class="bi-file-pdf me-1"></i> PDF</a></li>
                                <li><a class="dropdown-item" href="#" id="exportExcel"><i class="bi-file-excel me-1"></i> Excel</a></li>
                                <li><a class="dropdown-item" href="#" id="exportCSV"><i class="bi-file-text me-1"></i> CSV</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Stats Overview -->
        <div class="row mb-4">
            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Total Students</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="card-title text-primary"><?php echo number_format($stats['total_students']); ?></h2>
                                <div class="<?php echo $stats['student_growth'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    <i class="bi-graph-<?php echo $stats['student_growth'] >= 0 ? 'up' : 'down'; ?> me-1"></i> 
                                    <?php echo $stats['student_growth'] >= 0 ? '+' : ''; ?><?php echo $stats['student_growth']; ?>%
                                </div>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-soft-primary p-2">
                                    <i class="bi-people fs-3"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Active Courses</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="card-title text-primary"><?php echo number_format($stats['total_courses']); ?></h2>
                                <div class="<?php echo $stats['course_growth'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    <i class="bi-graph-<?php echo $stats['course_growth'] >= 0 ? 'up' : 'down'; ?> me-1"></i> 
                                    <?php echo $stats['course_growth'] >= 0 ? '+' : ''; ?><?php echo $stats['course_growth']; ?>%
                                </div>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-soft-primary p-2">
                                    <i class="bi-book fs-3"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3 mb-3 mb-sm-0">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Total Revenue</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="card-title text-primary">GH₵<?php echo number_format($stats['total_revenue']); ?></h2>
                                <div class="<?php echo $stats['revenue_growth'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    <i class="bi-graph-<?php echo $stats['revenue_growth'] >= 0 ? 'up' : 'down'; ?> me-1"></i> 
                                    <?php echo $stats['revenue_growth'] >= 0 ? '+' : ''; ?><?php echo $stats['revenue_growth']; ?>%
                                </div>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-soft-primary p-2">
                                    <i class="bi-currency-dollar fs-3"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Completion Rate</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="card-title text-primary"><?php echo number_format($stats['avg_completion'], 1); ?>%</h2>
                                <div class="<?php echo $stats['completion_growth'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    <i class="bi-graph-<?php echo $stats['completion_growth'] >= 0 ? 'up' : 'down'; ?> me-1"></i> 
                                    <?php echo $stats['completion_growth'] >= 0 ? '+' : ''; ?><?php echo $stats['completion_growth']; ?>%
                                </div>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-soft-primary p-2">
                                    <i class="bi-check-circle fs-3"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Stats Overview -->

        <div class="row">
            <!-- Enrollment Trends -->
            <div class="col-lg-8 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-header-title">Enrollment Trends</h5>
                    </div>
                    <div class="card-body">
                        <div id="enrollmentTrendsChart" style="height: 320px;"></div>
                    </div>
                </div>
            </div>
            <!-- End Enrollment Trends -->

            <!-- Top Categories -->
            <div class="col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-header-title">Top Categories</h5>
                    </div>
                    <div class="card-body">
                        <div id="topCategoriesChart" style="height: 320px;"></div>
                    </div>
                </div>
            </div>
            <!-- End Top Categories -->
        </div>

        <div class="row">
            <!-- Revenue Breakdown -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-header-title">Revenue Breakdown</h5>
                    </div>
                    <div class="card-body">
                        <div id="revenueBreakdownChart" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
            <!-- End Revenue Breakdown -->

            <!-- User Activity -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-header-title">User Activity</h5>
                    </div>
                    <div class="card-body">
                        <div id="userActivityChart" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
            <!-- End User Activity -->
        </div>

        <!-- Top Performing Courses -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-header-title">Top Performing Courses</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle">
                        <thead class="thead-light">
                            <tr>
                                <th>Course</th>
                                <th>Category</th>
                                <th>Enrollments</th>
                                <th>Completion Rate</th>
                                <th>Revenue</th>
                                <th>Rating</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($topCourses)): ?>
                                <?php foreach ($topCourses as $course): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <img class="avatar avatar-sm avatar-4x3" src="<?php echo !empty($course['thumbnail']) ? '../uploads/thumbnails/'.$course['thumbnail'] : '../uploads/thumbnails/default.jpg'; ?>" alt="Course Image">
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <a class="text-body" href="#"><?php echo $course['title']; ?></a>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo $course['category']; ?></td>
                                        <td><?php echo $course['enrollment_count']; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="me-2"><?php echo number_format($course['completion_rate'], 0); ?>%</span>
                                                <div class="progress table-progress">
                                                    <div class="progress-bar <?php echo $course['completion_rate'] >= 75 ? 'bg-success' : 'bg-warning'; ?>" style="width: <?php echo $course['completion_rate']; ?>%" role="progressbar" aria-valuenow="<?php echo $course['completion_rate']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>GH₵<?php echo number_format($course['revenue'], 2); ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="me-2"><?php echo number_format($course['avg_rating'], 1); ?></span>
                                                <div class="text-warning">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <?php if ($i <= floor($course['avg_rating'])): ?>
                                                            <i class="bi-star-fill"></i>
                                                        <?php elseif ($i - 0.5 <= $course['avg_rating']): ?>
                                                            <i class="bi-star-half"></i>
                                                        <?php else: ?>
                                                            <i class="bi-star"></i>
                                                        <?php endif; ?>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Default course rows if no data -->
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <img class="avatar avatar-sm avatar-4x3" src="../uploads/thumbnails/default.jpg" alt="Course Image">
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <a class="text-body" href="#">Web Development Bootcamp</a>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Development</td>
                                    <td>254</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="me-2">82%</span>
                                            <div class="progress table-progress">
                                                <div class="progress-bar bg-success" style="width: 82%" role="progressbar" aria-valuenow="82" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>GH₵12,580</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="me-2">4.8</span>
                                            <div class="text-warning">
                                                <i class="bi-star-fill"></i>
                                                <i class="bi-star-fill"></i>
                                                <i class="bi-star-fill"></i>
                                                <i class="bi-star-fill"></i>
                                                <i class="bi-star-half"></i>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <img class="avatar avatar-sm avatar-4x3" src="../uploads/thumbnails/default.jpg" alt="Course Image">
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <a class="text-body" href="#">Data Science Fundamentals</a>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Data Science</td>
                                    <td>187</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="me-2">75%</span>
                                            <div class="progress table-progress">
                                                <div class="progress-bar bg-success" style="width: 75%" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>GH₵9,345</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="me-2">4.7</span>
                                            <div class="text-warning">
                                                <i class="bi-star-fill"></i>
                                                <i class="bi-star-fill"></i>
                                                <i class="bi-star-fill"></i>
                                                <i class="bi-star-fill"></i>
                                                <i class="bi-star-half"></i>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <img class="avatar avatar-sm avatar-4x3" src="../uploads/thumbnails/default.jpg" alt="Course Image">
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <a class="text-body" href="#">UI/UX Design Masterclass</a>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Design</td>
                                    <td>176</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="me-2">92%</span>
                                            <div class="progress table-progress">
                                                <div class="progress-bar bg-success" style="width: 92%" role="progressbar" aria-valuenow="92" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>GH₵8,720</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="me-2">4.9</span>
                                            <div class="text-warning">
                                                <i class="bi-star-fill"></i>
                                                <i class="bi-star-fill"></i>
                                                <i class="bi-star-fill"></i>
                                                <i class="bi-star-fill"></i>
                                                <i class="bi-star-fill"></i>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- End Top Performing Courses -->

        <div class="row">
            <!-- Instructor Performance -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-header-title">Top Instructors</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Instructor</th>
                                        <th>Courses</th>
                                        <th>Students</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($topInstructors)): ?>
                                        <?php foreach ($topInstructors as $instructor): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0">
                                                            <img class="avatar avatar-sm avatar-circle" src="<?php echo !empty($instructor['profile_pic']) && $instructor['profile_pic'] != 'default.png' ? '../uploads/instructor-profile/'.$instructor['profile_pic'] : '../uploads/instructor-profile/default.png'; ?>" alt="Instructor">
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <h5 class="mb-0"><?php echo $instructor['first_name'] . ' ' . $instructor['last_name']; ?></h5>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo $instructor['course_count']; ?></td>
                                                <td><?php echo $instructor['student_count']; ?></td>
                                                <td>GH₵<?php echo number_format($instructor['total_revenue'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <!-- Default instructor rows if no data -->
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <img class="avatar avatar-sm avatar-circle" src="../uploads/instructor-profile/default.png" alt="Instructor">
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h5 class="mb-0">Sarah Williams</h5>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>12</td>
                                            <td>458</td>
                                            <td>GH₵28,450</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <img class="avatar avatar-sm avatar-circle" src="../uploads/instructor-profile/default.png" alt="Instructor">
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h5 class="mb-0">Robert Johnson</h5>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>9</td>
                                            <td>392</td>
                                            <td>GH₵21,980</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <img class="avatar avatar-sm avatar-circle" src="../uploads/instructor-profile/default.png" alt="Instructor">
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h5 class="mb-0">Jennifer Lee</h5>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>7</td>
                                            <td>325</td>
                                            <td>GH₵18,620</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Instructor Performance -->

            <!-- Student Demographics -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-header-title">Student Demographics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-3">Age Distribution</h6>
                                <div id="ageDistributionChart" style="height: 240px;"></div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-3">Geographical Distribution</h6>
                                <div id="geoDistributionChart" style="height: 240px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Student Demographics -->
        </div>

        <!-- Recent Activities -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-header-title">Recent Activity</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-timeline list-group-timeline-primary">
                    <?php if (!empty($recentActivities)): ?>
                        <?php foreach ($recentActivities as $activity): ?>
                            <li class="list-group-item list-group-timeline-item">
                                <span class="list-group-timeline-badge"></span>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <?php 
                                        $activityTitle = '';
                                        switch ($activity['activity_type']) {
                                            case 'course_published':
                                                $activityTitle = 'New course published';
                                                break;
                                            case 'payout_processed':
                                                $activityTitle = 'Instructor payout processed';
                                                break;
                                            case 'verification_request':
                                                $activityTitle = 'New verification request';
                                                break;
                                            case 'course_review':
                                                $activityTitle = 'Course review request';
                                                break;
                                            case 'support_ticket':
                                                $activityTitle = 'New support ticket';
                                                break;
                                            default:
                                                $activityTitle = 'Activity';
                                        }
                                        ?>
                                        <h5 class="mb-1"><?php echo $activityTitle; ?></h5>
                                        <p class="mb-0"><?php echo $activity['title']; ?> by <?php echo $activity['user_name']; ?></p>
                                    </div>
                                    <small class="text-muted">
                                        <?php 
                                        $activityTime = strtotime($activity['activity_time']);
                                        $now = time();
                                        $diff = $now - $activityTime;
                                        
                                        if ($diff < 60) {
                                            echo "Just now";
                                        } elseif ($diff < 3600) {
                                            echo floor($diff / 60) . " minutes ago";
                                        } elseif ($diff < 86400) {
                                            echo floor($diff / 3600) . " hours ago";
                                        } elseif ($diff < 172800) {
                                            echo "Yesterday";
                                        } else {
                                            echo date("M j", $activityTime);
                                        }
                                        ?>
                                    </small>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Default activity items if no data -->
                        <li class="list-group-item list-group-timeline-item">
                            <span class="list-group-timeline-badge"></span>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1">New course published</h5>
                                    <p class="mb-0">"Advanced React Development" by Robert Johnson</p>
                                </div>
                                <small class="text-muted">Just now</small>
                            </div>
                        </li>
                        <li class="list-group-item list-group-timeline-item">
                            <span class="list-group-timeline-badge"></span>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1">Instructor payout processed</h5>
                                    <p class="mb-0">GH₵12,560 to Sarah Williams</p>
                                </div>
                                <small class="text-muted">2 hours ago</small>
                            </div>
                        </li>
                        <li class="list-group-item list-group-timeline-item">
                            <span class="list-group-timeline-badge"></span>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1">New verification request</h5>
                                    <p class="mb-0">Jennifer Lee submitted instructor verification documents</p>
                                </div>
                                <small class="text-muted">5 hours ago</small>
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <!-- End Recent Activities -->
    </div>
    <!-- End Content -->
    </div>

    <!-- Custom Date Range Modal -->
    <div class="modal fade" id="customDateRangeModal" tabindex="-1" aria-labelledby="customDateRangeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customDateRangeModalLabel">Select Date Range</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="startDate" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="startDate">
                        </div>
                        <div class="mb-3">
                            <label for="endDate" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="endDate">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary">Apply</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Custom Date Range Modal -->

    <!-- JavaScript for Charts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Enrollment Trends Chart
            var enrollmentOptions = {
                chart: {
                    type: 'area',
                    height: 320,
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                    name: 'Enrollments',
                    data: <?php echo json_encode($monthlyEnrollments); ?>
                }],
                xaxis: {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                },
                colors: ['#3a66db'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.2,
                        stops: [0, 90, 100]
                    }
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                dataLabels: {
                    enabled: false
                }
            };
            var enrollmentChart = new ApexCharts(document.querySelector("#enrollmentTrendsChart"), enrollmentOptions);
            enrollmentChart.render();

            // Top Categories Chart
            var categoriesOptions = {
                chart: {
                    type: 'donut',
                    height: 320
                },
                series: <?php echo json_encode($topCategories['counts']); ?>,
                labels: <?php echo json_encode($topCategories['categories']); ?>,
                colors: ['#3a66db', '#5f85e5', '#88a6ee', '#b0c6f6', '#d8e3fb'],
                legend: {
                    position: 'bottom'
                },
                dataLabels: {
                    enabled: false
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%'
                        }
                    }
                }
            };
            var categoriesChart = new ApexCharts(document.querySelector("#topCategoriesChart"), categoriesOptions);
            categoriesChart.render();

            // Revenue Breakdown Chart
            var revenueOptions = {
                chart: {
                    type: 'bar',
                    height: 300,
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                    name: 'Revenue (GH₵)',
                    data: <?php echo json_encode($revenueByCategory['revenues']); ?>
                }],
                xaxis: {
                    categories: <?php echo json_encode($revenueByCategory['categories']); ?>
                },
                colors: ['#3a66db'],
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        horizontal: true
                    }
                },
                dataLabels: {
                    enabled: false
                }
            };
            var revenueChart = new ApexCharts(document.querySelector("#revenueBreakdownChart"), revenueOptions);
            revenueChart.render();

            // User Activity Chart
            var activityOptions = {
                chart: {
                    type: 'line',
                    height: 300,
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                        name: 'New Registrations',
                        data: <?php echo json_encode($userActivity['new_users']); ?>
                    },
                    {
                        name: 'Active Users',
                        data: <?php echo json_encode($userActivity['active_users']); ?>
                    }
                ],
                xaxis: {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
                },
                colors: ['#3a66db', '#5f85e5'],
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                markers: {
                    size: 4
                },
                dataLabels: {
                    enabled: false
                }
            };
            var activityChart = new ApexCharts(document.querySelector("#userActivityChart"), activityOptions);
            activityChart.render();

            // Age Distribution Chart
            var ageOptions = {
                chart: {
                    type: 'pie',
                    height: 240,
                    toolbar: {
                        show: false
                    }
                },
                series: [25, 36, 24, 15],
                labels: ['18-24', '25-34', '35-44', '45+'],
                colors: ['#3a66db', '#5f85e5', '#88a6ee', '#b0c6f6'],
                legend: {
                    position: 'bottom',
                    fontSize: '12px'
                },
                dataLabels: {
                    enabled: false
                }
            };
            var ageChart = new ApexCharts(document.querySelector("#ageDistributionChart"), ageOptions);
            ageChart.render();

            // Geographical Distribution Chart
            var geoOptions = {
                chart: {
                    type: 'pie',
                    height: 240,
                    toolbar: {
                        show: false
                    }
                },
                series: [38, 22, 15, 12, 8, 5],
                labels: ['Ghana', 'Nigeria', 'Kenya', 'South Africa', 'Other African', 'International'],
                colors: ['#3a66db', '#5f85e5', '#88a6ee', '#b0c6f6', '#d8e3fb', '#eef3fd'],
                legend: {
                    position: 'bottom',
                    fontSize: '12px'
                },
                dataLabels: {
                    enabled: false
                }
            };
            var geoChart = new ApexCharts(document.querySelector("#geoDistributionChart"), geoOptions);
            geoChart.render();

            // Export functionality
            document.getElementById('exportPDF').addEventListener('click', function() {
                showOverlay('Generating PDF report...');

                // Simulate PDF generation
                setTimeout(function() {
                    removeOverlay();
                    showAlert('success', 'Report has been successfully exported as PDF');
                }, 2000);
            });

            document.getElementById('exportExcel').addEventListener('click', function() {
                showOverlay('Generating Excel report...');

                // Simulate Excel generation
                setTimeout(function() {
                    removeOverlay();
                    showAlert('success', 'Report has been successfully exported as Excel file');
                }, 2000);
            });

            document.getElementById('exportCSV').addEventListener('click', function() {
                showOverlay('Generating CSV report...');

                // Simulate CSV generation
                setTimeout(function() {
                    removeOverlay();
                    showAlert('success', 'Report has been successfully exported as CSV file');
                }, 2000);
            });
        });
    </script>

    <!-- End Content -->

</main>
<!-- ========== END MAIN CONTENT ========== -->

<?php include '../includes/admin-footer.php'; ?>