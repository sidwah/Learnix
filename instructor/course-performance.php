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
    <title>Instructor | Learnix - Course Performance</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Analyze course performance, track student progress, and gain insights with intuitive dashboards and visualizations." />
    <meta name="author" content="Learnix Team" />
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <!-- Third-party CSS -->
    <link href="assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <!-- App CSS -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style"/>
    <!-- Custom CSS for Course Performance -->
    <style>
        .performance-card { 
            border-radius: 10px; 
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); 
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
        @media (max-width: 768px) {
            .performance-card { 
                margin-bottom: 20px; 
            }
            .filter-btn { 
                width: 100%; 
                margin-bottom: 10px; 
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
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            Filter by Course
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                                            <li><a class="dropdown-item" href="#">All Courses</a></li>
                                            <li><a class="dropdown-item" href="#">Course 1: Intro to Programming</a></li>
                                            <li><a class="dropdown-item" href="#">Course 2: Data Science</a></li>
                                        </ul>
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
                                    <h3 class="mt-3 mb-2">78%</h3>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 78%" aria-valuenow="78" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <p class="text-muted mt-2">Up 5% from last month</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card performance-card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Average Grade</h5>
                                    <h3 class="mt-3 mb-2">82/100</h3>
                                    <div class="progress">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: 82%" aria-valuenow="82" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <p class="text-muted mt-2">Stable from last month</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card performance-card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Engagement Score</h5>
                                    <h3 class="mt-3 mb-2">65%</h3>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: 65%" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <p class="text-muted mt-2">Down 3% from last month</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card performance-card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Active Students</h5>
                                    <h3 class="mt-3 mb-2">120</h3>
                                    <div class="progress">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 90%" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <p class="text-muted mt-2">10 new students this week</p>
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
                                                    <th>Student Name</th>
                                                    <th>Course</th>
                                                    <th>Progress</th>
                                                    <th>Average Grade</th>
                                                    <th>Engagement</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr data-student='{"name":"Jane Doe","course":"Intro to Programming","progress":"85%","grade":"88/100","engagement":"High","details":"Completed all modules, excels in assignments, active in discussions."}'>
                                                    <td>Jane Doe</td>
                                                    <td>Intro to Programming</td>
                                                    <td><div class="progress progress-bar"><div class="progress-bar bg-success" style="width: 85%"></div></div></td>
                                                    <td>88/100</td>
                                                    <td>High</td>
                                                    <td><button class="btn btn-sm btn-outline-primary view-details" data-bs-toggle="modal" data-bs-target="#studentDetailsModal">View Details</button></td>
                                                </tr>
                                                <tr data-student='{"name":"John Smith","course":"Data Science","progress":"60%","grade":"75/100","engagement":"Medium","details":"Missed two assignments, needs support with advanced topics."}'>
                                                    <td>John Smith</td>
                                                    <td>Data Science</td>
                                                    <td><div class="progress progress-bar"><div class="progress-bar bg-warning" style="width: 60%"></div></div></td>
                                                    <td>75/100</td>
                                                    <td>Medium</td>
                                                    <td><button class="btn btn-sm btn-outline-primary view-details" data-bs-toggle="modal" data-bs-target="#studentDetailsModal">View Details</button></td>
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
                            <div class="card performance-card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Actionable Insights</h5>
                                    <ul class="list-unstyled">
                                        <li><i class="mdi mdi-alert-circle-outline me-2"></i> 5 students are behind on assignments in "Intro to Programming."</li>
                                        <li><i class="mdi mdi-star-outline me-2"></i> Engagement in "Data Science" has dropped; consider adding interactive quizzes.</li>
                                        <li><i class="mdi mdi-check-circle-outline me-2"></i> 80% of students completed the latest module on time.</li>
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
    <!-- Custom JS for Charts and Modal -->
    <script>
        // Student Progress Chart
        var progressOptions = {
            chart: { type: 'bar', height: 300, animations: { enabled: true } },
            series: [{ name: 'Progress', data: [85, 60, 92, 45, 78] }],
            xaxis: { categories: ['Jane Doe', 'John Smith', 'Alice Brown', 'Bob Wilson', 'Emma Davis'] },
            colors: ['#28a745'],
            dataLabels: { enabled: false },
            tooltip: { enabled: true }
        };
        var progressChart = new ApexCharts(document.querySelector("#student-progress-chart"), progressOptions);
        progressChart.render();

        // Assessment Performance Chart
        var assessmentOptions = {
            chart: { type: 'line', height: 300, animations: { enabled: true } },
            series: [{ name: 'Average Score', data: [70, 75, 80, 85, 90] }],
            xaxis: { categories: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'] },
            colors: ['#007bff'],
            dataLabels: { enabled: false },
            tooltip: { enabled: true }
        };
        var assessmentChart = new ApexCharts(document.querySelector("#assessment-performance-chart"), assessmentOptions);
        assessmentChart.render();

        // Handle View Details Button Click
        document.querySelectorAll('.view-details').forEach(button => {
            button.addEventListener('click', function () {
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
    </script>
</body>
</html>