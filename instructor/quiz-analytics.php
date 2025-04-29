
<?php
require '../backend/session_start.php'; // Ensure session is started

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    error_log("Unauthorized access attempt detected: " . json_encode($_SERVER));
    header('Location: landing.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Instructor | Learnix - Quiz Analytics</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Analyze quiz performance with detailed metrics, interactive charts, and actionable insights to enhance student learning." />
    <meta name="author" content="Learnix Team" />
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <!-- Third-party CSS -->
    <link href="assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <!-- App CSS -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style"/>
    <!-- Custom CSS for Quiz Analytics -->
    <style>
        .analytics-card { 
            border-radius: 10px; 
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); 
            transition: transform 0.3s ease-in-out; 
        }
        .analytics-card:hover { 
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
        .insights-list li { 
            margin-bottom: 10px; 
        }
        @media (max-width: 768px) {
            .analytics-card { 
                margin-bottom: 20px; 
            }
            .filter-btn { 
                width: 100%; 
                margin-bottom: 10px; 
            }
            .table-responsive { 
                overflow-x: auto; 
            }
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
                                    <div class="btn-group">
                                        <button class="btn btn-outline-secondary dropdown-toggle filter-btn" type="button" id="courseFilter" data-bs-toggle="dropdown" aria-expanded="false">
                                            Filter by Course
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="courseFilter">
                                            <li><a class="dropdown-item" href="#">All Courses</a></li>
                                            <li><a class="dropdown-item" href="#">Intro to Programming</a></li>
                                            <li><a class="dropdown-item" href="#">Data Science</a></li>
                                        </ul>
                                        <button class="btn btn-outline-secondary dropdown-toggle filter-btn ms-2" type="button" id="quizFilter" data-bs-toggle="dropdown" aria-expanded="false">
                                            Filter by Quiz
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="quizFilter">
                                            <li><a class="dropdown-item" href="#">All Quizzes</a></li>
                                            <li><a class="dropdown-item" href="#">Quiz 1: Basics</a></li>
                                            <li><a class="dropdown-item" href="#">Quiz 2: Advanced</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <h4 class="page-title">Quiz Analytics</h4>
                            </div>
                        </div>
                    </div>
                    <!-- End Page Title -->

                    <!-- Quiz Metrics -->
                    <div class="row">
                        <!-- Average Score -->
                        <div class="col-xl-3 col-md-6">
                            <div class="card analytics-card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Average Score</h5>
                                    <h3 class="mt-3 mb-2">78/100</h3>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 78%" aria-valuenow="78" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <p class="text-muted mt-2">Up 3% from last quiz</p>
                                </div>
                            </div>
                        </div>
                        <!-- Completion Rate -->
                        <div class="col-xl-3 col-md-6">
                            <div class="card analytics-card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Completion Rate</h5>
                                    <h3 class="mt-3 mb-2">85%</h3>
                                    <div class="progress">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <p class="text-muted mt-2">Stable from last quiz</p>
                                </div>
                            </div>
                        </div>
                        <!-- Average Time Taken -->
                        <div class="col-xl-3 col-md-6">
                            <div class="card analytics-card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Avg. Time Taken</h5>
                                    <h3 class="mt-3 mb-2">12 min</h3>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: 60%" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <p class="text-muted mt-2">Down 1 min from last quiz</p>
                                </div>
                            </div>
                        </div>
                        <!-- Question Difficulty -->
                        <div class="col-xl-3 col-md-6">
                            <div class="card analytics-card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Question Difficulty</h5>
                                    <h3 class="mt-3 mb-2">Medium</h3>
                                    <div class="progress">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <p class="text-muted mt-2">Based on correct answer rate</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div class="row">
                        <!-- Score Distribution Chart -->
                        <div class="col-xl-6">
                            <div class="card analytics-card">
                                <div class="card-body chart-container">
                                    <h5 class="card-title">Score Distribution</h5>
                                    <div id="score-distribution-chart" style="height: 300px;"></div>
                                </div>
                            </div>
                        </div>
                        <!-- Question Performance Chart -->
                        <div class="col-xl-6">
                            <div class="card analytics-card">
                                <div class="card-body chart-container">
                                    <h5 class="card-title">Question Performance</h5>
                                    <div id="question-performance-chart" style="height: 300px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Student Results Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card analytics-card">
                                <div class="card-body">
                                    <h5 class="card-title">Student Results</h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Student Name</th>
                                                    <th>Course</th>
                                                    <th>Quiz</th>
                                                    <th>Score</th>
                                                    <th>Time Taken</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Jane Doe</td>
                                                    <td>Intro to Programming</td>
                                                    <td>Quiz 1: Basics</td>
                                                    <td>85/100</td>
                                                    <td>10 min</td>
                                                    <td><a href="#" class="btn btn-sm btn-outline-primary">View Answers</a></td>
                                                </tr>
                                                <tr>
                                                    <td>John Smith</td>
                                                    <td>Data Science</td>
                                                    <td>Quiz 2: Advanced</td>
                                                    <td>70/100</td>
                                                    <td>15 min</td>
                                                    <td><a href="#" class="btn btn-sm btn-outline-primary">View Answers</a></td>
                                                </tr>
                                                <!-- More rows dynamically populated -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actionable Insights -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card analytics-card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Actionable Insights</h5>
                                    <ul class="list-unstyled insights-list">
                                        <li><i class="mdi mdi-alert-circle-outline me-2"></i> Question 3 in "Quiz 1" has a low correct answer rate; consider revising it.</li>
                                        <li><i class="mdi mdi-email-outline me-2"></i> 4 students scored below 60; send them review materials.</li>
                                        <li><i class="mdi mdi-check-circle-outline me-2"></i> High completion rate for "Quiz 2"; maintain similar format.</li>
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
                            © Learnix. <script>document.write(new Date().getFullYear())</script> All rights reserved.
                        </div>
                    </div>
                </div>
            </footer>
            <!-- End Footer -->
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
    <!-- Custom JS for Charts -->
    <script>
        // Score Distribution Chart (Histogram)
        var scoreOptions = {
            chart: { type: 'histogram', height: 300, animations: { enabled: true } },
            series: [{ name: 'Scores', data: [{ x: 0, y: 2 }, { x: 20, y: 5 }, { x: 40, y: 10 }, { x: 60, y: 15 }, { x: 80, y: 8 }, { x: 100, y: 3 }] }],
            xaxis: { title: { text: 'Score Range' } },
            yaxis: { title: { text: 'Number of Students' } },
            colors: ['#28a745'],
            dataLabels: { enabled: false },
            tooltip: { enabled: true }
        };
        var scoreChart = new ApexCharts(document.querySelector("#score-distribution-chart"), scoreOptions);
        scoreChart.render();

        // Question Performance Chart (Bar)
        var questionOptions = {
            chart: { type: 'bar', height: 300, animations: { enabled: true } },
            series: [{ name: 'Correct Answers', data: [85, 60, 45, 90, 75] }],
            xaxis: { categories: ['Q1', 'Q2', 'Q3', 'Q4', 'Q5'] },
            colors: ['#007bff'],
            dataLabels: { enabled: false },
            tooltip: { enabled: true }
        };
        var questionChart = new ApexCharts(document.querySelector("#question-performance-chart"), questionOptions);
        questionChart.render();
    </script>
</body>
</html>
