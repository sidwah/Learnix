<?php
require '../backend/session_start.php';

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    // Log unauthorized access attempt for security auditing
    error_log("Unauthorized access attempt detected: " . json_encode($_SERVER));

    // Redirect unauthorized users to a custom unauthorized access page or login page
    header('Location: landing.php');
    exit;
}

require_once '../backend/config.php';

// Get instructor ID from the session
$instructor_id = $_SESSION['user_id'];

// Get student ID from URL parameter
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;

if ($student_id <= 0) {
    // Invalid student ID, redirect to all students page
    header('Location: all-students.php');
    exit;
}

// Verify that the student is enrolled in at least one of the instructor's courses
$verify_query = "SELECT EXISTS(
                    SELECT 1 FROM enrollments e
                    JOIN courses c ON e.course_id = c.course_id
                    WHERE e.user_id = ? AND c.instructor_id = ?
                ) as valid_student";
$stmt = $conn->prepare($verify_query);
$stmt->bind_param("ii", $student_id, $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row['valid_student']) {
    // Student not found or not enrolled in instructor's courses
    header('Location: all-students.php');
    exit;
}

// Get student information
$student_query = "SELECT 
                    u.user_id, 
                    u.first_name, 
                    u.last_name, 
                    u.email, 
                    u.profile_pic,
                    u.created_at as join_date,
                    COUNT(DISTINCT e.course_id) as enrolled_courses,
                    ROUND(AVG(e.completion_percentage), 1) as avg_completion
                FROM users u
                JOIN enrollments e ON u.user_id = e.user_id
                JOIN courses c ON e.course_id = c.course_id
                WHERE u.user_id = ? AND c.instructor_id = ?
                GROUP BY u.user_id";
$stmt = $conn->prepare($student_query);
$stmt->bind_param("ii", $student_id, $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Get student's courses
$courses_query = "SELECT 
                    c.course_id,
                    c.title,
                    c.thumbnail,
                    e.enrolled_at,
                    e.last_accessed,
                    e.completion_percentage,
                    e.status
                FROM enrollments e
                JOIN courses c ON e.course_id = c.course_id
                WHERE e.user_id = ? AND c.instructor_id = ?
                ORDER BY e.enrolled_at DESC";
$stmt = $conn->prepare($courses_query);
$stmt->bind_param("ii", $student_id, $instructor_id);
$stmt->execute();
$courses_result = $stmt->get_result();
$courses = [];
while ($course = $courses_result->fetch_assoc()) {
    $courses[] = $course;
}

// Get student's quiz performance (all quizzes)
$quiz_query = "SELECT 
                sq.quiz_id,
                sq.quiz_title,
                c.title as course_title,
                c.course_id,
                MAX(sqa.attempt_number) as attempts,
                MAX(sqa.score) as highest_score,
                AVG(sqa.score) as avg_score,
                SUM(sqa.time_spent) as total_time_spent,
                MAX(sqa.end_time) as last_attempt_date
            FROM student_quiz_attempts sqa
            JOIN section_quizzes sq ON sqa.quiz_id = sq.quiz_id
            JOIN course_sections cs ON sq.section_id = cs.section_id
            JOIN courses c ON cs.course_id = c.course_id
            WHERE sqa.user_id = ? AND c.instructor_id = ?
            GROUP BY sq.quiz_id
            ORDER BY last_attempt_date DESC";
$stmt = $conn->prepare($quiz_query);
$stmt->bind_param("ii", $student_id, $instructor_id);
$stmt->execute();
$quizzes_result = $stmt->get_result();
$quizzes = [];
while ($quiz = $quizzes_result->fetch_assoc()) {
    $quizzes[] = $quiz;
}

// Get section completion data for the student
$sections_query = "SELECT 
                  c.title as course_name,
                  c.course_id,
                  COUNT(DISTINCT cs.section_id) as total_sections,
                  SUM(CASE 
                        WHEN (SELECT COUNT(p.progress_id) 
                              FROM progress p 
                              JOIN enrollments en ON p.enrollment_id = en.enrollment_id
                              JOIN section_topics st ON p.topic_id = st.topic_id
                              WHERE en.user_id = ? 
                              AND en.course_id = c.course_id
                              AND st.section_id = cs.section_id
                              AND p.completion_status = 'Completed') = 
                             (SELECT COUNT(st.topic_id) 
                              FROM section_topics st 
                              WHERE st.section_id = cs.section_id)
                        THEN 1 
                        ELSE 0 
                      END) as completed_sections
                FROM courses c
                JOIN course_sections cs ON c.course_id = cs.course_id
                WHERE c.instructor_id = ? 
                AND c.course_id IN (SELECT course_id FROM enrollments WHERE user_id = ?)
                GROUP BY c.course_id
                ORDER BY c.title";
$stmt = $conn->prepare($sections_query);
$stmt->bind_param("iii", $student_id, $instructor_id, $student_id);
$stmt->execute();
$sections_result = $stmt->get_result();
$sections_data = [];
$total_sections = 0;
$completed_sections = 0;
while ($section = $sections_result->fetch_assoc()) {
    $sections_data[] = $section;
    $total_sections += $section['total_sections'];
    $completed_sections += $section['completed_sections'];
}

// Calculate total time spent on learning
$time_query = "SELECT SUM(time_spent) as total_time 
               FROM student_learning_sessions 
               WHERE user_id = ? AND course_id IN (SELECT course_id FROM courses WHERE instructor_id = ?)";
$stmt = $conn->prepare($time_query);
$stmt->bind_param("ii", $student_id, $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
$time_data = $result->fetch_assoc();
$total_time_spent = $time_data['total_time'] ?? 0;

// Format dates for display
function formatDate($dateString) {
    if (!$dateString) return 'Never';
    
    $date = new DateTime($dateString);
    return $date->format('M d, Y');
}

// Format time duration
function formatDuration($minutes) {
    if (!$minutes) return '0 min';
    
    if ($minutes < 60) {
        return $minutes . ' min';
    } else {
        $hours = floor($minutes / 60);
        $min = $minutes % 60;
        return $hours . 'h ' . $min . 'm';
    }
}

// Check if we have empty data
$has_quizzes = count($quizzes) > 0;
$has_courses = count($courses) > 0;
$has_sections_data = count($sections_data) > 0;

// Calculate section completion percentage
$section_completion_percent = $total_sections > 0 ? round(($completed_sections / $total_sections) * 100, 1) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Student Progress | Learnix - Empowering Education</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Track detailed student learning progress" name="description" />
    <meta name="author" content="Learnix Team" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- third party css -->
    <link href="assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/vendor/dataTables.bootstrap5.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/vendor/responsive.bootstrap5.css" rel="stylesheet" type="text/css" />
    <!-- third party css end -->

    <!-- App css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style"/>
    
    <style>
        .progress {
            height: 8px;
        }
        .student-profile-header {
            background: linear-gradient(to right, #1a2942, #121a2f);
            color: #fff;
            border-radius: 0.25rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .profile-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #fff;
        }
        .chart-container {
            height: 300px;
        }
        .course-card .course-progress {
            height: 6px;
            width: 100%;
            margin-top: 0.5rem;
            background-color: #e3eaef;
            border-radius: 3px;
        }
        .course-card .progress-bar {
            height: 100%;
            border-radius: 3px;
        }
        #download-link {
            display: none;
        }
        .donut-completion {
            position: relative;
            text-align: center;
        }
        .completion-center {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 24px;
            font-weight: bold;
        }
        .completion-label {
            font-size: 14px;
            color: #6c757d;
            display: block;
        }
    </style>
</head>

<body class="loading" data-layout-color="light" data-leftbar-theme="dark" data-layout-mode="fluid" data-rightbar-onstart="true">
    <!-- Begin page -->
    <div class="wrapper">
        <!-- ========== Left Sidebar Start ========== -->
        <?php include '../includes/instructor-sidebar.php'; ?>
        <!-- Left Sidebar End -->

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="content-page">
            <div class="content">
                <!-- Topbar Start -->
                <?php include '../includes/instructor-topnavbar.php'; ?>
                <!-- end Topbar -->
                
                <!-- Start Content-->
                <div class="container-fluid">
                
                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="all-students.php">All Students</a></li>
                                        <li class="breadcrumb-item active">Student Progress</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Student Progress</h4>
                            </div>
                        </div>
                    </div>     
                    <!-- end page title -->

                    <!-- Student profile header -->
                    <div class="student-profile-header">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <img src="<?php echo $student['profile_pic'] ? '../uploads/profile/' . $student['profile_pic'] : 'assets/images/users/default.png'; ?>" 
                                     class="profile-img" alt="Student Profile">
                            </div>
                            <div class="col">
                                <h2 class="m-0"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h2>
                                <p class="mb-1"><?php echo htmlspecialchars($student['email']); ?></p>
                                <p class="mb-0">Joined: <?php echo formatDate($student['join_date']); ?></p>
                            </div>
                            <div class="col-md-auto mt-3 mt-md-0">
                                <div class="text-md-end">
                                    <button type="button" class="btn btn-light me-1" id="message-student">
                                        <i class="uil uil-envelope-alt me-1"></i> Message
                                    </button>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-primary" id="generate-report">
                                            <i class="uil uil-file-download me-1"></i> Generate Report
                                        </button>
                                        <a href="#" id="download-link" class="btn btn-success" download="student_progress_report.pdf">
                                            <i class="uil uil-download-alt me-1"></i> Download Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats cards -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="text-muted fw-normal mt-0 text-truncate">Courses Enrolled</h5>
                                            <h3 class="my-2"><?php echo $student['enrolled_courses']; ?></h3>
                                        </div>
                                        <div class="avatar-sm">
                                            <span class="avatar-title bg-soft-primary rounded">
                                                <i class="uil uil-book-open text-success font-24"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="text-muted fw-normal mt-0 text-truncate">Overall Progress</h5>
                                            <h3 class="my-2"><?php echo $student['avg_completion']; ?>%</h3>
                                        </div>
                                        <div class="avatar-sm">
                                            <span class="avatar-title bg-soft-info rounded">
                                                <i class="uil uil-chart-line text-info font-24"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="text-muted fw-normal mt-0 text-truncate">Quiz Performance</h5>
                                            <?php
                                            // Calculate average quiz score
                                            $avg_score = 0;
                                            $quiz_count = count($quizzes);
                                            if ($quiz_count > 0) {
                                                $total_score = 0;
                                                foreach ($quizzes as $quiz) {
                                                    $total_score += $quiz['highest_score'];
                                                }
                                                $avg_score = round($total_score / $quiz_count, 1);
                                            }
                                            ?>
                                            <h3 class="my-2"><?php echo $avg_score; ?>%</h3>
                                        </div>
                                        <div class="avatar-sm">
                                            <span class="avatar-title bg-soft-success rounded">
                                                <i class="uil uil-check-square text-success font-24"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="text-muted fw-normal mt-0 text-truncate">Total Time Spent</h5>
                                            <h3 class="my-2"><?php echo formatDuration($total_time_spent); ?></h3>
                                        </div>
                                        <div class="avatar-sm">
                                            <span class="avatar-title bg-soft-warning rounded">
                                                <i class="uil uil-clock-eight text-warning font-24"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <div class="row">
                        <!-- Left Column - Course Progress -->
                        <div class="col-xl-8">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h4 class="header-title mb-3">Course Progress</h4>
                                        <div class="dropdown">
                                            <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="mdi mdi-dots-vertical"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a href="javascript:void(0);" class="dropdown-item" id="export-courses-pdf">Export as PDF</a>
                                                <a href="javascript:void(0);" class="dropdown-item" id="export-courses-csv">Export as CSV</a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <?php if ($has_courses): ?>
                                            <?php foreach ($courses as $course): ?>
                                                <div class="col-md-6 col-xxl-4">
                                                    <!-- project card -->
                                                    <div class="card d-block mb-3">
                                                        <!-- project-thumbnail -->
                                                        <img class="card-img-top" src="<?php echo $course['thumbnail'] ? '../uploads/thumbnails/' . $course['thumbnail'] : 'assets/images/courses/default.jpg'; ?>" alt="course thumbnail" style="height: 140px; object-fit: cover;">
                                                        <div class="card-img-overlay">
                                                            <div class="badge bg-<?php echo $course['status'] == 'Active' ? 'success' : ($course['status'] == 'Completed' ? 'info' : 'warning'); ?> text-light p-1"><?php echo $course['status']; ?></div>
                                                        </div>
                                                        
                                                        <div class="card-body position-relative">
                                                            <!-- course title-->
                                                            <h5 class="mt-0">
                                                                <a href="javascript:void(0);" class="text-title"><?php echo htmlspecialchars($course['title']); ?></a>
                                                            </h5>
                                                            
                                                            <!-- course detail-->
                                                            <p class="mb-3">
                                                                <span class="text-nowrap">
                                                                    <i class="mdi mdi-calendar"></i>
                                                                    <b>Enrolled: </b> <?php echo formatDate($course['enrolled_at']); ?>
                                                                </span>
                                                            </p>
                                                            
                                                            <!-- course progress-->
                                                            <p class="mb-2 fw-bold">Progress <span class="float-end"><?php echo number_format($course['completion_percentage'], 1); ?>%</span></p>
                                                            <div class="progress progress-sm">
                                                                <div class="progress-bar bg-<?php echo $course['completion_percentage'] < 30 ? 'danger' : ($course['completion_percentage'] < 70 ? 'warning' : 'success'); ?>" role="progressbar" 
                                                                     aria-valuenow="<?php echo $course['completion_percentage']; ?>" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="100" 
                                                                     style="width: <?php echo $course['completion_percentage']; ?>%;">
                                                                </div><!-- /.progress-bar -->
                                                            </div><!-- /.progress -->
                                                        </div> <!-- end card-body-->
                                                    </div> <!-- end card-->
                                                </div> <!-- end col -->
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="col-12">
                                                <div class="alert alert-info">
                                                    <i class="mdi mdi-information-outline mr-2"></i>
                                                    No course enrollments found for this student.
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Column - Section Completion -->
                        <div class="col-xl-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h4 class="header-title mb-3">Section Completion</h4>
                                        <div class="dropdown">
                                            <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="mdi mdi-dots-vertical"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a href="javascript:void(0);" class="dropdown-item">View Details</a>
                                                <a href="javascript:void(0);" class="dropdown-item">Export Report</a>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if ($has_sections_data): ?>
                                    <div class="donut-completion my-4" style="height: 280px;">
                                        <div id="sections-donut-chart" class="apex-charts"></div>
                                        <div class="completion-center">
                                            <?php echo $section_completion_percent; ?>%
                                            <span class="completion-label">Completed</span>
                                        </div>
                                    </div>
                                    
                                    <div class="row text-center mt-2 py-2">
                                        <div class="col-6">
                                            <div class="my-2 my-sm-0">
                                                <i class="mdi mdi-check-circle-outline text-success mt-3 h3"></i>
                                                <h3 class="fw-normal">
                                                    <span><?php echo $completed_sections; ?>/<?php echo $total_sections; ?></span>
                                                </h3>
                                                <p class="text-muted mb-0">Sections Completed</p>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="my-2 my-sm-0">
                                                <i class="mdi mdi-book-open-variant text-primary mt-3 h3"></i>
                                                <h3 class="fw-normal">
                                                    <span><?php echo count($sections_data); ?></span>
                                                </h3>
                                                <p class="text-muted mb-0">Courses With Content</p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="mdi mdi-information-outline mr-2"></i>
                                        No section data available for this student.
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quiz Performance -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h4 class="header-title mb-3">Quiz Performance</h4>
                                        <div class="dropdown">
                                            <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="mdi mdi-dots-vertical"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a href="javascript:void(0);" class="dropdown-item" id="export-quizzes-pdf">Export as PDF</a>
                                                <a href="javascript:void(0);" class="dropdown-item" id="export-quizzes-csv">Export as CSV</a>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if ($has_quizzes): ?>
                                    <div class="table-responsive">
                                        <table class="table table-centered table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Quiz</th>
                                                    <th>Course</th>
                                                    <th>Attempts</th>
                                                    <th>Highest Score</th>
                                                    <th>Average Score</th>
                                                    <th>Time Spent</th>
                                                    <th>Last Attempted</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($quizzes as $quiz): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($quiz['quiz_title']); ?></td>
                                                    <td><?php echo htmlspecialchars($quiz['course_title']); ?></td>
                                                    <td><?php echo $quiz['attempts']; ?></td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="progress" style="width: 100px; height: 4px;">
                                                                <div class="progress-bar bg-<?php echo $quiz['highest_score'] < 60 ? 'danger' : ($quiz['highest_score'] < 80 ? 'warning' : 'success'); ?>" role="progressbar" 
                                                                     style="width: <?php echo $quiz['highest_score']; ?>%;" 
                                                                     aria-valuenow="<?php echo $quiz['highest_score']; ?>" 
                                                                     aria-valuemin="0" aria-valuemax="100"></div>
                                                            </div>
                                                            <span class="ms-2"><?php echo number_format($quiz['highest_score'], 1); ?>%</span>
                                                        </div>
                                                    </td>
                                                    <td><?php echo number_format($quiz['avg_score'], 1); ?>%</td>
                                                    <td><?php echo formatDuration($quiz['total_time_spent']); ?></td>
                                                    <td><?php echo formatDate($quiz['last_attempt_date']); ?></td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-outline-info view-results" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#quiz-results-modal"
                                                                data-quiz-id="<?php echo $quiz['quiz_id']; ?>"
                                                                data-quiz-title="<?php echo htmlspecialchars($quiz['quiz_title']); ?>"
                                                                data-student-id="<?php echo $student_id; ?>">
                                                            <i class="uil uil-eye"></i> View
                                                        </button>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="mdi mdi-information-outline mr-2"></i>
                                        No quiz attempts found for this student.
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div> <!-- container -->

            </div> <!-- content -->

            <!-- Footer Start -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                        © Learnix. <script>document.write(new Date().getFullYear())</script> All rights reserved.
                        </div>
                    </div>
                </div>
            </footer>
            <!-- end Footer -->

        </div>
        <!-- End Page content -->
    </div>
    <!-- END wrapper -->

    <?php include '../includes/instructor-darkmode.php'; ?>

    <!-- Quiz Results Modal -->
    <div class="modal fade" id="quiz-results-modal" tabindex="-1" aria-labelledby="quizResultsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quizResultsModalLabel">Quiz Results</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="quiz-results-loading" class="text-center p-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading quiz results...</p>
                    </div>
                    <div id="quiz-results-content" class="d-none">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 id="modal-quiz-title" class="text-primary"></h5>
                                <p class="mb-1">Student: <span id="modal-student-name"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></span></p>
                                <p class="mb-3">Course: <span id="modal-course-title"></span></p>
                            </div>
                            <div class="col-md-6">
                                <div class="text-md-end">
                                    <p class="mb-1">Highest Score: <span id="modal-highest-score" class="text-success fw-bold"></span></p>
                                    <p class="mb-1">Average Score: <span id="modal-avg-score"></span></p>
                                    <p class="mb-1">Attempts: <span id="modal-attempts"></span></p>
                                </div>
                            </div>
                        </div>
                        
                        <div id="quiz-attempt-list" class="mt-3">
                            <h6>Quiz Attempts</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-centered table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Attempt #</th>
                                            <th>Date</th>
                                            <th>Score</th>
                                            <th>Time Spent</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="quiz-attempts-tbody">
                                        <!-- Attempts will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div id="question-breakdown" class="mt-4">
                            <h6>Question Performance</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-centered table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Question</th>
                                            <th>Type</th>
                                            <th>Correct Responses</th>
                                            <th>Incorrect Responses</th>
                                            <th>Success Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody id="questions-tbody">
                                        <!-- Questions will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id="quiz-results-error" class="alert alert-danger d-none">
                        <i class="mdi mdi-alert-circle-outline me-2"></i>
                        Error loading quiz results. Please try again.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="export-modal-results">Export Results</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading overlay -->
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
            
            // Add styles
            overlay.style.position = 'fixed';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.width = '100%';
            overlay.style.height = '100%';
            overlay.style.backgroundColor = 'rgba(0,0,0,0.5)';
            overlay.style.display = 'flex';
            overlay.style.justifyContent = 'center';
            overlay.style.alignItems = 'center';
            overlay.style.zIndex = '9999';
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
    </script>

    <!-- bundle -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.min.js"></script>

    <!-- third party js -->
    <script src="assets/js/vendor/apexcharts.min.js"></script>
    <script src="assets/js/vendor/jquery.dataTables.min.js"></script>
    <script src="assets/js/vendor/dataTables.bootstrap5.js"></script>
    <script src="assets/js/vendor/jspdf.umd.min.js"></script>
    <script src="assets/js/vendor/html2canvas.min.js"></script>
    <!-- third party js ends -->

    <!-- Charts and report generation -->
    <script>
        $(document).ready(function() {
            // Action buttons
            $('#message-student').click(function() {
                showAlert('success', 'Messaging functionality will be implemented soon!');
            });
            
            $('#generate-report').click(function() {
                showOverlay('Generating student progress report...');
                
                // Generate report using jsPDF
                setTimeout(function() {
                    try {
                        generatePDF();
                        $('#generate-report').hide();
                        $('#download-link').show();
                        removeOverlay();
                        showAlert('success', 'Progress report has been generated! Click Download Report to save it.');
                    } catch (error) {
                        console.error('PDF generation error:', error);
                        removeOverlay();
                        showAlert('danger', 'Error generating report. Please try again.');
                    }
                }, 1500);
            });
            
            <?php if ($has_sections_data): ?>
            // Section completion donut chart
            var sectionsOptions = {
                chart: {
                    type: 'donut',
                    height: 280
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '75%'
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                },
                legend: {
                    show: false
                },
                colors: ['#0acf97', '#f3f4f7'],
                series: [<?php echo $completed_sections; ?>, <?php echo $total_sections - $completed_sections; ?>],
                labels: ['Completed', 'Remaining'],
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function (val) {
                            return val + " sections";
                        }
                    }
                }
            };

            var sectionsChart = new ApexCharts(document.querySelector("#sections-donut-chart"), sectionsOptions);
            sectionsChart.render();
            <?php endif; ?>
            
            // View quiz results button click
            $('.view-results').on('click', function() {
                const quizId = $(this).data('quiz-id');
                const quizTitle = $(this).data('quiz-title');
                const studentId = $(this).data('student-id');
                
                // Reset modal
                $('#quiz-results-loading').removeClass('d-none');
                $('#quiz-results-content').addClass('d-none');
                $('#quiz-results-error').addClass('d-none');
                
                // Set quiz title in modal
                $('#modal-quiz-title').text(quizTitle);
                
                // Simulate loading quiz results
                setTimeout(function() {
                    // In a real implementation, you would fetch this data from an AJAX endpoint
                    const mockAttempts = [
                        { attempt_number: 3, date: '<?php echo date('Y-m-d H:i:s', strtotime('-2 days')); ?>', score: 92.5, time_spent: 15, status: 'Passed' },
                        { attempt_number: 2, date: '<?php echo date('Y-m-d H:i:s', strtotime('-5 days')); ?>', score: 78.0, time_spent: 12, status: 'Passed' },
                        { attempt_number: 1, date: '<?php echo date('Y-m-d H:i:s', strtotime('-7 days')); ?>', score: 65.0, time_spent: 20, status: 'Failed' }
                    ];
                    
                    const mockQuestions = [
                        { question: 'What is the capital of France?', type: 'Multiple Choice', correct: 3, incorrect: 0, success_rate: 100 },
                        { question: 'Name two programming languages.', type: 'Short Answer', correct: 2, incorrect: 1, success_rate: 67 },
                        { question: 'Explain the difference between HTML and CSS.', type: 'Essay', correct: 1, incorrect: 2, success_rate: 33 },
                        { question: 'Match the following terms with their definitions.', type: 'Matching', correct: 2, incorrect: 1, success_rate: 67 }
                    ];
                    
                    // Fill in mock data
                    $('#modal-course-title').text('Web Development Fundamentals');
                    $('#modal-highest-score').text('92.5%');
                    $('#modal-avg-score').text('78.5%');
                    $('#modal-attempts').text('3');
                    
                    // Render attempts table
                    let attemptsHtml = '';
                    mockAttempts.forEach(attempt => {
                        attemptsHtml += `
                            <tr>
                                <td>${attempt.attempt_number}</td>
                                <td>${new Date(attempt.date).toLocaleString()}</td>
                                <td>${attempt.score}%</td>
                                <td>${attempt.time_spent} min</td>
                                <td><span class="badge bg-${attempt.status === 'Passed' ? 'success' : 'danger'}">${attempt.status}</span></td>
                            </tr>
                        `;
                    });
                    $('#quiz-attempts-tbody').html(attemptsHtml);
                    
                    // Render questions table
                    let questionsHtml = '';
                    mockQuestions.forEach(question => {
                        questionsHtml += `
                            <tr>
                                <td>${question.question}</td>
                                <td>${question.type}</td>
                                <td>${question.correct}</td>
                                <td>${question.incorrect}</td>
                                <td>
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar bg-${question.success_rate < 50 ? 'danger' : (question.success_rate < 75 ? 'warning' : 'success')}" 
                                             role="progressbar" 
                                             style="width: ${question.success_rate}%;"
                                             aria-valuenow="${question.success_rate}"
                                             aria-valuemin="0" 
                                             aria-valuemax="100"></div>
                                    </div>
                                    <small class="mt-1 d-block text-center">${question.success_rate}%</small>
                                </td>
                            </tr>
                        `;
                    });
                    $('#questions-tbody').html(questionsHtml);
                    
                    // Show content
                    $('#quiz-results-loading').addClass('d-none');
                    $('#quiz-results-content').removeClass('d-none');
                }, 1000);
            });
            
            // Export modal results
            $('#export-modal-results').on('click', function() {
                showAlert('success', 'Quiz results exported successfully!');
                $('#quiz-results-modal').modal('hide');
            });
            
            // Function to generate PDF report
            function generatePDF() {
                const { jsPDF } = window.jspdf;
                
                // Create a new PDF document
                const doc = new jsPDF('p', 'mm', 'a4');
                
                // Add title
                doc.setFontSize(22);
                doc.setTextColor(40, 40, 40);
                doc.text('Student Progress Report', 105, 20, { align: 'center' });
                
                // Add student info
                doc.setFontSize(14);
                doc.setTextColor(70, 70, 70);
                doc.text(`${<?php echo json_encode(htmlspecialchars($student['first_name'] . ' ' . $student['last_name'])); ?>}`, 105, 30, { align: 'center' });
                
                doc.setFontSize(12);
                doc.setTextColor(100, 100, 100);
                doc.text(`Email: ${<?php echo json_encode(htmlspecialchars($student['email'])); ?>}`, 105, 38, { align: 'center' });
                doc.text(`Generated on: ${new Date().toLocaleDateString()}`, 105, 45, { align: 'center' });
                
                // Add summary info
                doc.setFontSize(16);
                doc.setTextColor(50, 50, 50);
                doc.text('Progress Summary', 20, 60);
                
                doc.setFontSize(12);
                doc.setTextColor(80, 80, 80);
                doc.text(`Courses Enrolled: ${<?php echo $student['enrolled_courses']; ?>}`, 20, 70);
                doc.text(`Overall Progress: ${<?php echo $student['avg_completion']; ?>}%`, 20, 78);
                doc.text(`Quiz Performance: ${<?php echo $avg_score; ?>}%`, 20, 86);
                doc.text(`Total Time Spent: ${<?php echo json_encode(formatDuration($total_time_spent)); ?>}`, 20, 94);
                doc.text(`Section Completion: ${<?php echo $section_completion_percent; ?>}%`, 20, 102);
                
                // Add course information table
                doc.setFontSize(16);
                doc.text('Enrolled Courses', 20, 118);
                
                doc.setFontSize(10);
                doc.setTextColor(80, 80, 80);
                
                // Course table headers
                const courseHeaders = ['Course', 'Status', 'Progress', 'Enrolled On'];
                let courseData = [];
                
                <?php if ($has_courses): ?>
                // Add course data
                <?php foreach ($courses as $course): ?>
                courseData.push([
                    <?php echo json_encode(htmlspecialchars($course['title'])); ?>, 
                    <?php echo json_encode($course['status']); ?>,
                    <?php echo json_encode(number_format($course['completion_percentage'], 1) . '%'); ?>,
                    <?php echo json_encode(formatDate($course['enrolled_at'])); ?>
                ]);
                <?php endforeach; ?>
                <?php endif; ?>
                
                if (courseData.length > 0) {
                    doc.autoTable({
                        startY: 123,
                        head: [courseHeaders],
                        body: courseData,
                        theme: 'grid',
                        headStyles: { fillColor: [26, 41, 66] },
                        columnStyles: {
                            0: { cellWidth: 70 },
                            1: { cellWidth: 30 },
                            2: { cellWidth: 30 },
                            3: { cellWidth: 40 }
                        }
                    });
                } else {
                    doc.text('No course data available', 20, 123);
                }
                
                // Add quiz information
                let finalY = doc.lastAutoTable ? doc.lastAutoTable.finalY + 15 : 170;
                
                doc.setFontSize(16);
                doc.setTextColor(50, 50, 50);
                doc.text('Quiz Performance', 20, finalY);
                
                // Quiz table headers
                const quizHeaders = ['Quiz', 'Course', 'Highest Score', 'Attempts'];
                let quizData = [];
                
                <?php if ($has_quizzes): ?>
                // Add quiz data
                <?php foreach ($quizzes as $quiz): ?>
                quizData.push([
                    <?php echo json_encode(htmlspecialchars($quiz['quiz_title'])); ?>, 
                    <?php echo json_encode(htmlspecialchars($quiz['course_title'])); ?>,
                    <?php echo json_encode(number_format($quiz['highest_score'], 1) . '%'); ?>,
                    <?php echo $quiz['attempts']; ?>
                ]);
                <?php endforeach; ?>
                <?php endif; ?>
                
                if (quizData.length > 0) {
                    doc.autoTable({
                        startY: finalY + 5,
                        head: [quizHeaders],
                        body: quizData,
                        theme: 'grid',
                        headStyles: { fillColor: [26, 41, 66] },
                        columnStyles: {
                            0: { cellWidth: 60 },
                            1: { cellWidth: 60 },
                            2: { cellWidth: 30 },
                            3: { cellWidth: 20 }
                        }
                    });
                } else {
                    doc.text('No quiz data available', 20, finalY + 10);
                }
                
                // Add footer
                doc.setFontSize(10);
                doc.setTextColor(150, 150, 150);
                doc.text('Generated by Learnix Learning Management System', 105, 285, { align: 'center' });
                
                // Create download link
                const pdfOutput = doc.output('blob');
                const pdfUrl = URL.createObjectURL(pdfOutput);
                $('#download-link').attr('href', pdfUrl);
                
                return pdfOutput;
            }
            
            // Handle download link click
            $('#download-link').click(function() {
                // Remove the blob URL after download starts
                setTimeout(function() {
                    URL.revokeObjectURL($('#download-link').attr('href'));
                    $('#download-link').hide();
                    $('#generate-report').show();
                }, 100);
            });
        });
    </script>
</body>
</html>