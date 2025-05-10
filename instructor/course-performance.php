
<?php
require '../backend/session_start.php'; // Ensure session is started

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    error_log("Unauthorized access attempt detected: " . json_encode($_SERVER));
    header('Location: landing.php');
    exit;
}

// Include the database configuration
require_once '../backend/config.php';

// Get instructor ID from session
$instructor_id = $_SESSION['instructor_id'];

// ===== FETCH INSTRUCTOR COURSES =====
$courses = [];
$sql = "SELECT c.course_id, c.title 
        FROM courses c
        JOIN course_instructors ci ON c.course_id = ci.course_id 
        WHERE ci.instructor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$courses_result = $stmt->get_result();

while ($course = $courses_result->fetch_assoc()) {
    $courses[] = $course;
}
$stmt->close();

// ===== FETCH PERFORMANCE METRICS =====
// Total students
$sql = "SELECT COUNT(*) as total_students 
        FROM enrollments e 
        JOIN courses c ON e.course_id = c.course_id 
        JOIN course_instructors ci ON c.course_id = ci.course_id 
        WHERE ci.instructor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_students = $row['total_students'];
$stmt->close();

// Average completion rate
$sql = "SELECT AVG(e.completion_percentage) as avg_completion_rate 
        FROM enrollments e 
        JOIN courses c ON e.course_id = c.course_id 
        JOIN course_instructors ci ON c.course_id = ci.course_id 
        WHERE ci.instructor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$avg_completion_rate = round($row['avg_completion_rate'] ?? 0, 1);
$stmt->close();

// Average grade from student quiz attempts
$sql = "SELECT AVG(sqa.score) as avg_grade
        FROM student_quiz_attempts sqa
        JOIN section_quizzes sq ON sqa.quiz_id = sq.quiz_id
        JOIN section_topics st ON sq.topic_id = st.topic_id
        JOIN course_sections cs ON st.section_id = cs.section_id
        JOIN courses c ON cs.course_id = c.course_id
        JOIN course_instructors ci ON c.course_id = ci.course_id
        WHERE ci.instructor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$avg_grade = round($row['avg_grade'] ?? 0, 1);
$stmt->close();

// Engagement score (custom metric based on recent activity)
$sql = "SELECT COUNT(*) as active_students
        FROM enrollments e
        JOIN courses c ON e.course_id = c.course_id
        JOIN course_instructors ci ON c.course_id = ci.course_id
        WHERE ci.instructor_id = ? AND e.last_accessed >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$active_students = $row['active_students'];
$engagement_score = $total_students > 0 ? round(($active_students / $total_students) * 100, 1) : 0;
$stmt->close();

// ===== FETCH STUDENT PROGRESS DATA =====
// Get pagination parameters
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Get sorting parameters
$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$sortOrder = isset($_GET['order']) ? $_GET['order'] : 'asc';

// Validate sort column and order to prevent SQL injection
$validColumns = ['name', 'course', 'progress', 'grade', 'engagement'];
$sortColumn = in_array($sortColumn, $validColumns) ? $sortColumn : 'name';
$sortOrder = ($sortOrder === 'desc') ? 'DESC' : 'ASC';

// SQL order by clause based on selected column
$orderBy = '';
switch ($sortColumn) {
    case 'name':
        $orderBy = "ORDER BY u.first_name $sortOrder, u.last_name $sortOrder";
        break;
    case 'course':
        $orderBy = "ORDER BY c.title $sortOrder";
        break;
    case 'progress':
        $orderBy = "ORDER BY e.completion_percentage $sortOrder";
        break;
    case 'grade':
        $orderBy = "ORDER BY avg_score $sortOrder";
        break;
    case 'engagement':
        $orderBy = "ORDER BY last_active $sortOrder";
        break;
    default:
        $orderBy = "ORDER BY u.first_name ASC, u.last_name ASC";
}

// Count total students for pagination
$sql = "SELECT COUNT(*) as total 
        FROM enrollments e
        JOIN users u ON e.user_id = u.user_id
        JOIN courses c ON e.course_id = c.course_id
        JOIN course_instructors ci ON c.course_id = ci.course_id
        WHERE ci.instructor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$totalStudents = $row['total'];
$totalPages = ceil($totalStudents / $perPage);
$stmt->close();

// Fetch students with pagination and sorting
$sql = "SELECT u.first_name, u.last_name, c.title as course_title, 
        e.completion_percentage, e.last_accessed, st.title as current_topic,
        (SELECT AVG(sqa.score) 
         FROM student_quiz_attempts sqa 
         JOIN section_quizzes sq ON sqa.quiz_id = sq.quiz_id
         JOIN section_topics st ON sq.topic_id = st.topic_id
         JOIN course_sections cs ON st.section_id = cs.section_id
         WHERE sqa.user_id = u.user_id AND cs.course_id = c.course_id
        ) as avg_score,
        DATEDIFF(NOW(), e.last_accessed) as last_active
        FROM enrollments e
        JOIN users u ON e.user_id = u.user_id
        JOIN courses c ON e.course_id = c.course_id
        JOIN course_instructors ci ON c.course_id = ci.course_id
        LEFT JOIN section_topics st ON e.current_topic_id = st.topic_id
        WHERE ci.instructor_id = ?
        $orderBy
        LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $instructor_id, $offset, $perPage);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
$student_progress_data = [];
$student_names = [];
$progress_values = [];

while ($row = $result->fetch_assoc()) {
    // Calculate engagement level
    $engagement_level = "Low";
    if ($row['last_accessed']) {
        $days_since_access = $row['last_active'];
        
        if ($days_since_access <= 7) {
            $engagement_level = "High";
        } else if ($days_since_access <= 14) {
            $engagement_level = "Medium";
        }
    }
    
    $student = [
        'name' => $row['first_name'] . ' ' . $row['last_name'],
        'course' => $row['course_title'],
        'progress' => round($row['completion_percentage'], 1),
        'grade' => round($row['avg_score'] ?? 0, 1),
        'engagement' => $engagement_level,
        'current_topic' => $row['current_topic'] ?? 'N/A',
        'last_accessed' => $row['last_accessed'] ?? 'Never'
    ];
    
    $students[] = $student;
    
    // For chart data (limit to first 5)
    if (count($student_names) < 5) {
        $student_names[] = $student['name'];
        $progress_values[] = $student['progress'];
    }
}
$stmt->close();

// ===== FETCH QUIZ ANALYTICS =====
$sql = "SELECT sq.quiz_title, 
        COUNT(DISTINCT sqa.attempt_id) as attempt_count,
        AVG(sqa.score) as avg_score,
        SUM(CASE WHEN sqa.passed = 1 THEN 1 ELSE 0 END) / 
            NULLIF(COUNT(DISTINCT sqa.attempt_id), 0) * 100 as pass_rate
        FROM section_quizzes sq
        JOIN student_quiz_attempts sqa ON sq.quiz_id = sqa.quiz_id
        JOIN section_topics st ON sq.topic_id = st.topic_id
        JOIN course_sections cs ON st.section_id = cs.section_id
        JOIN courses c ON cs.course_id = c.course_id
        JOIN course_instructors ci ON c.course_id = ci.course_id
        WHERE ci.instructor_id = ?
        GROUP BY sq.quiz_id
        ORDER BY attempt_count DESC
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result = $stmt->get_result();

$quizzes = [];
$quiz_names = [];
$avg_scores = [];
$pass_rates = [];

while ($row = $result->fetch_assoc()) {
    $quizzes[] = [
        'name' => $row['quiz_title'],
        'attempts' => $row['attempt_count'],
        'avgScore' => round($row['avg_score'], 1),
        'passRate' => round($row['pass_rate'] ?? 0, 1)
    ];
    
    $quiz_names[] = $row['quiz_title'];
    $avg_scores[] = round($row['avg_score'], 1);
    $pass_rates[] = round($row['pass_rate'] ?? 0, 1);
}
$stmt->close();

// If no quiz data found, add default values to prevent empty chart
if (empty($quiz_names)) {
    $quiz_names = ['No Quiz Data'];
    $avg_scores = [0];
    $pass_rates = [0];
}

// ===== GENERATE INSIGHTS =====
$insights = [];

// Low engagement students insight
$sql = "SELECT COUNT(*) as count 
        FROM enrollments e
        JOIN courses c ON e.course_id = c.course_id
        JOIN course_instructors ci ON c.course_id = ci.course_id
        WHERE ci.instructor_id = ? AND e.last_accessed < DATE_SUB(NOW(), INTERVAL 14 DAY)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$low_engagement_count = $row['count'];
$stmt->close();

if ($low_engagement_count > 0) {
    $insights[] = "$low_engagement_count students have low engagement. Consider reaching out.";
}

// Course with lowest completion rate
$sql = "SELECT c.title, AVG(e.completion_percentage) as avg_completion
        FROM courses c
        JOIN course_instructors ci ON c.course_id = ci.course_id
        JOIN enrollments e ON c.course_id = e.course_id
        WHERE ci.instructor_id = ?
        GROUP BY c.course_id
        ORDER BY avg_completion ASC
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $insights[] = "\"" . $row['title'] . "\" has the lowest completion rate (" . round($row['avg_completion'], 1) . "%). Consider reviewing content.";
}
$stmt->close();

// Quiz with lowest pass rate insight
if (count($quizzes) > 0) {
    $lowest_pass_rate = $quizzes[0];
    foreach ($quizzes as $quiz) {
        if ($quiz['passRate'] < $lowest_pass_rate['passRate']) {
            $lowest_pass_rate = $quiz;
        }
    }
    
    if ($lowest_pass_rate['passRate'] < 70) {
        $insights[] = "\"" . $lowest_pass_rate['name'] . "\" has " . $lowest_pass_rate['passRate'] . "% pass rate. Students may need additional support.";
    }
}

// Helper function to generate pagination links
function getPaginationLink($page, $sort = null, $order = null) {
    $link = '?page=' . $page;
    if ($sort) {
        $link .= '&sort=' . $sort;
    }
    if ($order) {
        $link .= '&order=' . $order;
    }
    return $link;
}

// Helper function to generate sort links
function getSortLink($column, $currentSort, $currentOrder) {
    $newOrder = ($currentSort === $column && $currentOrder === 'asc') ? 'desc' : 'asc';
    return '?sort=' . $column . '&order=' . $newOrder . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Instructor | Learnix - Course Performance</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Analyze course performance, track student progress, and gain insights with intuitive dashboards and visualizations." />
    <meta name="author" content="Learnix Team" />
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <!-- Third-party CSS -->
    <link href="assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <!-- App CSS -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    
    <!-- Custom CSS for Course Performance -->
    <style>
        .performance-card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out;
        }

        .performance-card:hover {
            transform: translateY(-5px);
        }

        .chart-container {
            min-height: 300px;
            padding: 20px;
        }

        .filter-btn {
            border-radius: 20px;
            padding: 8px 16px;
            font-size: 14px;
        }

        .progress-bar {
            height: 8px;
            border-radius: 4px;
        }

        .sortable {
            cursor: pointer;
        }
        
        .sortable::after {
            content: "↕";
            margin-left: 5px;
            opacity: 0.5;
        }
        
        .sort-asc::after {
            content: "↑";
            opacity: 1;
        }
        
        .sort-desc::after {
            content: "↓";
            opacity: 1;
        }
        
        .pagination {
            justify-content: center;
        }

        @media (max-width: 768px) {
            .performance-card {
                margin-bottom: 20px;
            }

            .filter-btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }
        
        /* Loading overlay */
        .custom-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            color: white;
        }
    </style>
</head>

<body class="loading" data-layout-color="light" data-leftbar-theme="dark" data-layout-mode="fluid" data-rightbar-onstart="true">
    <div class="wrapper">
        <!-- Sidebar -->
        <?php include '../includes/instructor-sidebar.php'; ?>
        <!-- End Sidebar -->

        <div class="content-page">
            <div class="content">
                <!-- Topbar -->
                <?php include '../includes/instructor-topnavbar.php'; ?>
                <!-- End Topbar -->

                <!-- Start Content -->
                <div class="container-fluid">
                    <!-- Page Title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <div class="d-flex">
                                        <!-- Course Filter -->
                                        <select id="courseFilter" class="form-select me-2">
                                            <option value="all">All Courses</option>
                                            <?php foreach ($courses as $course): ?>
                                                <option value="<?= $course['course_id'] ?>"><?= htmlspecialchars($course['title']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        
                                        <!-- Time Frame Filter -->
                                        <select id="timeFrameFilter" class="form-select">
                                            <option value="weekly">Weekly</option>
                                            <option value="monthly" selected>Monthly</option>
                                            <option value="quarterly">Quarterly</option>
                                            <option value="yearly">Yearly</option>
                                        </select>
                                    </div>
                                </div>
                                <h4 class="page-title">Course Performance</h4>
                            </div>
                        </div>
                    </div>
                    <!-- End Page Title -->

                    <!-- Performance Overview -->
                    <div class="row">
                        <!-- Key Metrics Cards -->
                        <div class="col-xl-3 col-md-6">
                            <div class="card performance-card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Completion Rate</h5>
                                    <h3 id="completion-rate-value" class="mt-3 mb-2"><?= $avg_completion_rate ?>%</h3>
                                    <div class="progress">
                                        <div id="completion-rate-progress" class="progress-bar bg-success" role="progressbar" 
                                             style="width: <?= $avg_completion_rate ?>%" 
                                             aria-valuenow="<?= $avg_completion_rate ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <p class="text-muted mt-2">Overall completion rate</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card performance-card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Average Grade</h5>
                                    <h3 id="average-grade-value" class="mt-3 mb-2"><?= $avg_grade ?>/100</h3>
                                    <div class="progress">
                                        <div id="average-grade-progress" class="progress-bar bg-info" role="progressbar" 
                                             style="width: <?= $avg_grade ?>%" 
                                             aria-valuenow="<?= $avg_grade ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <p class="text-muted mt-2">Based on quiz performance</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card performance-card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Engagement Score</h5>
                                    <h3 id="engagement-score-value" class="mt-3 mb-2"><?= $engagement_score ?>%</h3>
                                    <div class="progress">
                                        <div id="engagement-score-progress" class="progress-bar bg-warning" role="progressbar" 
                                             style="width: <?= $engagement_score ?>%" 
                                             aria-valuenow="<?= $engagement_score ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <p class="text-muted mt-2">Based on recent student activity</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card performance-card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Active Students</h5>
                                    <h3 id="active-students-value" class="mt-3 mb-2"><?= $total_students ?></h3>
                                    <div class="progress">
                                        <div id="active-students-progress" class="progress-bar bg-primary" role="progressbar" 
                                             style="width: 100%" 
                                             aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <p class="text-muted mt-2">Total enrolled students</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div class="row">
                        <!-- Student Progress Chart -->
                        <div class="col-xl-6">
                            <div class="card performance-card">
                                <div class="card-body chart-container">
                                    <h5 class="card-title">Student Progress</h5>
                                    <div id="student-progress-chart" style="height: 300px;"></div>
                                </div>
                            </div>
                        </div>
                        <!-- Assessment Performance Chart -->
                        <div class="col-xl-6">
                            <div class="card performance-card">
                                <div class="card-body chart-container">
                                    <h5 class="card-title">Assessment Performance</h5>
                                    <div id="assessment-performance-chart" style="height: 300px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Student Performance Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card performance-card">
                                <div class="card-body">
                                    <h5 class="card-title">Student Performance</h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th class="sortable <?= $sortColumn === 'name' ? ($sortOrder === 'asc' ? 'sort-asc' : 'sort-desc') : '' ?>">
                                                        <a href="<?= getSortLink('name', $sortColumn, $sortOrder) ?>" class="text-dark">
                                                            Student Name
                                                        </a>
                                                    </th>
                                                    <th class="sortable <?= $sortColumn === 'course' ? ($sortOrder === 'asc' ? 'sort-asc' : 'sort-desc') : '' ?>">
                                                        <a href="<?= getSortLink('course', $sortColumn, $sortOrder) ?>" class="text-dark">
                                                            Course
                                                        </a>
                                                    </th>
                                                    <th class="sortable <?= $sortColumn === 'progress' ? ($sortOrder === 'asc' ? 'sort-asc' : 'sort-desc') : '' ?>">
                                                        <a href="<?= getSortLink('progress', $sortColumn, $sortOrder) ?>" class="text-dark">
                                                            Progress
                                                        </a>
                                                    </th>
                                                    <th class="sortable <?= $sortColumn === 'grade' ? ($sortOrder === 'asc' ? 'sort-asc' : 'sort-desc') : '' ?>">
                                                        <a href="<?= getSortLink('grade', $sortColumn, $sortOrder) ?>" class="text-dark">
                                                            Average Grade
                                                        </a>
                                                    </th>
                                                    <th class="sortable <?= $sortColumn === 'engagement' ? ($sortOrder === 'asc' ? 'sort-asc' : 'sort-desc') : '' ?>">
                                                        <a href="<?= getSortLink('engagement', $sortColumn, $sortOrder) ?>" class="text-dark">
                                                            Engagement
                                                        </a>
                                                    </th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($students)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center">No student data available</td>
                                                </tr>
                                                <?php else: ?>
                                                    <?php foreach ($students as $student): ?>
                                                    <tr data-student='<?= json_encode([
                                                        "name" => $student['name'],
                                                        "course" => $student['course'],
                                                        "progress" => $student['progress'] . "%",
                                                        "grade" => $student['grade'] . "/100",
                                                        "engagement" => $student['engagement'],
                                                        "details" => "Currently on " . $student['current_topic'] . ". Last active: " . 
                                                                     date('M d, Y', strtotime($student['last_accessed']))
                                                    ]) ?>'>
                                                        <td><?= htmlspecialchars($student['name']) ?></td>
                                                        <td><?= htmlspecialchars($student['course']) ?></td>
                                                        <td>
                                                            <div class="progress progress-bar">
                                                                <div class="progress-bar <?= $student['progress'] >= 75 ? 'bg-success' : 
                                                                                          ($student['progress'] >= 50 ? 'bg-info' : 
                                                                                          ($student['progress'] >= 25 ? 'bg-warning' : 'bg-danger')) ?>" 
                                                                     style="width: <?= $student['progress'] ?>%"></div>
                                                            </div>
                                                        </td>
                                                        <td><?= $student['grade'] ?>/100</td>
                                                        <td>
                                                            <span class="badge <?= $student['engagement'] === 'High' ? 'bg-success' : 
                                                                                  ($student['engagement'] === 'Medium' ? 'bg-warning' : 'bg-danger') ?>">
                                                                <?= $student['engagement'] ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-sm btn-outline-primary view-details" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#studentDetailsModal">View Details</button>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <!-- Pagination -->
                                    <?php if ($totalPages > 1): ?>
                                    <nav aria-label="Page navigation" class="mt-4">
                                        <ul class="pagination justify-content-center">
                                            <!-- Previous button -->
                                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                                <a class="page-link" href="<?= getPaginationLink($page - 1, $sortColumn, $sortOrder) ?>" aria-label="Previous">
                                                    <span aria-hidden="true">«</span>
                                                </a>
                                            </li>
                                            
                                            <!-- Page numbers -->
                                            <?php
                                            $startPage = max(1, $page - 2);
                                            $endPage = min($totalPages, $startPage + 4);
                                            if ($endPage - $startPage < 4) {
                                                $startPage = max(1, $endPage - 4);
                                            }
                                            
                                            for ($i = $startPage; $i <= $endPage; $i++): 
                                            ?>
                                                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                                    <a class="page-link" href="<?= getPaginationLink($i, $sortColumn, $sortOrder) ?>"><?= $i ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            
                                            <!-- Next button -->
                                            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                                <a class="page-link" href="<?= getPaginationLink($page + 1, $sortColumn, $sortOrder) ?>" aria-label="Next">
                                                    <span aria-hidden="true">»</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actionable Insights -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card performance-card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Actionable Insights</h5>
                                    <ul class="list-unstyled">
                                        <?php if (empty($insights)): ?>
                                            <li><i class="mdi mdi-check-circle-outline me-2"></i> No actionable insights available at this time.</li>
                                        <?php else: ?>
                                            <?php foreach ($insights as $insight): ?>
                                                <li><i class="mdi mdi-alert-circle-outline me-2"></i> <?= htmlspecialchars($insight) ?></li>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </ul>
                                    <a href="#" class="btn btn-light mt-3">View All Insights</a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- End Container -->

            </div>
            <!-- End Content -->

            <!-- Footer -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                            © Learnix. <script>
                                document.write(new Date().getFullYear())
                            </script> All rights reserved.
                        </div>
                    </div>
                </div>
            </footer>
            <!-- End Footer -->

            <!-- Student Details Modal -->
            <div class="modal fade" id="studentDetailsModal" tabindex="-1" aria-labelledby="studentDetailsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="studentDetailsModalLabel">Student Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <h6 id="modal-student-name"></h6>
                            <p><strong>Course:</strong> <span id="modal-course"></span></p>
                            <p><strong>Progress:</strong> <span id="modal-progress"></span></p>
                            <p><strong>Average Grade:</strong> <span id="modal-grade"></span></p>
                            <p><strong>Engagement:</strong> <span id="modal-engagement"></span></p>
                            <p><strong>Details:</strong> <span id="modal-details"></span></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Modal -->

        </div>
        <!-- End Content Page -->
    </div>
    <!-- End Wrapper -->

    <!-- Dark Mode -->
    <?php include '../includes/instructor-darkmode.php'; ?>

    <!-- Scripts -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.min.js"></script>
    <script src="assets/js/vendor/apexcharts.min.js"></script>
    <script src="assets/js/vendor/jquery-jvectormap-1.2.2.min.js"></script>
    <script src="assets/js/vendor/jquery-jvectormap-world-mill-en.js"></script>
    
    <!-- Custom JS for Charts and Data Handling -->
    <script>
        // Show Loading Overlay
        function showOverlay(message = null) {
            // Remove any existing overlay
            const existingOverlay = document.querySelector('.custom-overlay');
            if (existingOverlay) {
                existingOverlay.remove();
            }

            // Create new overlay
            const overlay = document.createElement('div');
            overlay.className = 'custom-overlay';
            overlay.innerHTML = `
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                ${message ? `<div class="text-white ms-3">${message}</div>` : ''}
            `;

            document.body.appendChild(overlay);
        }

        // Remove Loading Overlay
        function removeOverlay() {
            const overlay = document.querySelector('.custom-overlay');
            if (overlay) {
                overlay.remove();
            }
        }

        // Show alert notification function
        function showAlert(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
            alertDiv.setAttribute('role', 'alert');
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            // Position the alert
            alertDiv.style.position = 'fixed';
            alertDiv.style.top = '20px';
            alertDiv.style.left = '50%';
            alertDiv.style.transform = 'translateX(-50%)';
            alertDiv.style.zIndex = '9999';
            alertDiv.style.minWidth = '300px';
            alertDiv.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
            document.body.appendChild(alertDiv);
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.classList.remove('show');
                    setTimeout(() => {
                        if (alertDiv.parentNode) {
                            alertDiv.parentNode.removeChild(alertDiv);
                        }
                    }, 300);
                }
            }, 5000);
        }
        
        // Initialize charts and event handlers
        document.addEventListener('DOMContentLoaded', function() {
            // Student Progress Chart
            const studentNames = <?= json_encode($student_names) ?>;
            const progressValues = <?= json_encode($progress_values) ?>;
            
            const progressOptions = {
                chart: { type: 'bar', height: 300, animations: { enabled: true } },
                series: [{ name: 'Progress (%)', data: progressValues }],
                xaxis: { categories: studentNames },
                colors: ['#28a745'],
                dataLabels: { enabled: false },
                tooltip: { enabled: true }
            };
            const progressChart = new ApexCharts(document.querySelector("#student-progress-chart"), progressOptions);
            progressChart.render();

            // Assessment Performance Chart
            const quizNames = <?= json_encode($quiz_names) ?>;
            const avgScores = <?= json_encode($avg_scores) ?>;
            const passRates = <?= json_encode($pass_rates) ?>;
            
            const assessmentOptions = {
                chart: { type: 'line', height: 300, animations: { enabled: true } },
                series: [
                    { name: 'Avg. Score (%)', data: avgScores },
                    { name: 'Pass Rate (%)', data: passRates }
                ],
                xaxis: { categories: quizNames },
                colors: ['#007bff', '#28a745'],
                dataLabels: { enabled: false },
                tooltip: { enabled: true }
            };
            const assessmentChart = new ApexCharts(document.querySelector("#assessment-performance-chart"), assessmentOptions);
            assessmentChart.render();

            // Handle View Details Button Click
            document.querySelectorAll('.view-details').forEach(button => {
                button.addEventListener('click', function() {
                    const row = this.closest('tr');
                    const studentData = JSON.parse(row.dataset.student);

                    // Populate modal fields
                    document.getElementById('modal-student-name').textContent = studentData.name;
                    document.getElementById('modal-course').textContent = studentData.course;
                    document.getElementById('modal-progress').textContent = studentData.progress;
                    document.getElementById('modal-grade').textContent = studentData.grade;
                    document.getElementById('modal-engagement').textContent = studentData.engagement;
                    document.getElementById('modal-details').textContent = studentData.details;
                });
            });
            
            // Course filter change handler
            document.getElementById('courseFilter').addEventListener('change', function() {
                filterAndReload();
            });
            
            // Time frame filter change handler
            document.getElementById('timeFrameFilter').addEventListener('change', function() {
                filterAndReload();
            });
            
            // Function to handle filter changes and page reload
            function filterAndReload() {
                showOverlay("Applying filters...");
                
                const courseId = document.getElementById('courseFilter').value;
                const timeFrame = document.getElementById('timeFrameFilter').value;
                
                // Create form data for AJAX request
                const formData = new FormData();
                formData.append('course_id', courseId);
                formData.append('time_frame', timeFrame);
                
                // Send AJAX request to the filter handler in ajax/instructors
                fetch('../ajax/instructors/course_performance_filter.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload the page with new URL parameters
                        window.location.href = 'course_performance.php' + data.redirect_query;
                    } else {
                        showAlert('danger', data.message || 'Error applying filters');
                        removeOverlay();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('danger', 'Failed to apply filters. Please try again.');
                    removeOverlay();
                });
            }
            
            // Set initial filter values from URL if present
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('course_id')) {
                document.getElementById('courseFilter').value = urlParams.get('course_id');
            }
            if (urlParams.has('time_frame')) {
                document.getElementById('timeFrameFilter').value = urlParams.get('time_frame');
            }
        });
    </script>
</body>

</html>
