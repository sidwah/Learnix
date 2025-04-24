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
    
    return [
        'total_students' => $studentData['total_students'] ?? 0,
        'total_courses' => $courseData['total_courses'] ?? 0,
        'total_revenue' => $revenueData['total_revenue'] ?? 0,
        'avg_completion' => $completionData['avg_completion'] ?? 0
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
             
             SELECT 'payout_processed' as activity_type, CONCAT('$', FORMAT(ip.amount, 2)) as title,
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

// Get database connection
try {
    require_once '../backend/config.php';
    $stats = fetchStatistics($conn);
    $topCourses = fetchTopCourses($conn);
    $topInstructors = fetchTopInstructors($conn);
    $recentActivities = fetchRecentActivities($conn);
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    $stats = [
        'total_students' => 1254,
        'total_courses' => 328,
        'total_revenue' => 48295,
        'avg_completion' => 68.7
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
                                <h2 class="card-title text-primary">1,254</h2>
                                <div class="text-success">
                                    <i class="bi-graph-up me-1"></i> +12.5%
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
                                <h2 class="card-title text-primary">328</h2>
                                <div class="text-success">
                                    <i class="bi-graph-up me-1"></i> +5.3%
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
                                <h2 class="card-title text-primary">$48,295</h2>
                                <div class="text-success">
                                    <i class="bi-graph-up me-1"></i> +18.2%
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
                                <h2 class="card-title text-primary">68.7%</h2>
                                <div class="text-danger">
                                    <i class="bi-graph-down me-1"></i> -2.4%
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
                                <td>$12,580</td>
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
                                <td>$9,345</td>
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
                                <td>$8,720</td>
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
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <img class="avatar avatar-sm avatar-4x3" src="../uploads/thumbnails/default.jpg" alt="Course Image">
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <a class="text-body" href="#">Digital Marketing Pro</a>
                                        </div>
                                    </div>
                                </td>
                                <td>Marketing</td>
                                <td>165</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">68%</span>
                                        <div class="progress table-progress">
                                            <div class="progress-bar bg-warning" style="width: 68%" role="progressbar" aria-valuenow="68" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>$7,540</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">4.5</span>
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
                                            <a class="text-body" href="#">Python for Data Analysis</a>
                                        </div>
                                    </div>
                                </td>
                                <td>Data Science</td>
                                <td>142</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">79%</span>
                                        <div class="progress table-progress">
                                            <div class="progress-bar bg-success" style="width: 79%" role="progressbar" aria-valuenow="79" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>$6,950</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">4.6</span>
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
                                        <td>$28,450</td>
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
                                        <td>$21,980</td>
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
                                        <td>$18,620</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <img class="avatar avatar-sm avatar-circle" src="../uploads/instructor-profile/default.png" alt="Instructor">
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h5 class="mb-0">Michael Chen</h5>
                                                </div>
                                            </div>
                                        </td>
                                        <td>6</td>
                                        <td>283</td>
                                        <td>$15,840</td>
                                    </tr>
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
                                <p class="mb-0">$12,560 to Sarah Williams</p>
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
                    <li class="list-group-item list-group-timeline-item">
                        <span class="list-group-timeline-badge"></span>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1">Course review request</h5>
                                <p class="mb-0">"Python for Machine Learning" awaiting admin approval</p>
                            </div>
                            <small class="text-muted">8 hours ago</small>
                        </div>
                    </li>
                    <li class="list-group-item list-group-timeline-item">
                        <span class="list-group-timeline-badge"></span>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1">New support ticket</h5>
                                <p class="mb-0">Student reported issue with video playback</p>
                            </div>
                            <small class="text-muted">Yesterday</small>
                        </div>
                    </li>
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
                    data: [30, 40, 45, 50, 49, 60, 70, 91, 125, 150, 160, 180]
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
                series: [35, 25, 20, 15, 5],
                labels: ['Development', 'Business', 'Design', 'Marketing', 'Others'],
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
                    name: 'Revenue',
                    data: [12500, 9800, 8400, 7600, 6900, 5800]
                }],
                xaxis: {
                    categories: ['Web Dev', 'Data Science', 'UI/UX', 'Digital Marketing', 'Python', 'Business']
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
                        data: [45, 52, 38, 24, 33, 26, 21, 20, 6, 8, 15, 10]
                    },
                    {
                        name: 'Active Users',
                        data: [87, 57, 74, 99, 75, 38, 62, 47, 82, 56, 45, 47]
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
                labels: ['North America', 'Europe', 'Asia', 'South America', 'Africa', 'Other'],
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