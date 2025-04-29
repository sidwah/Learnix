
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
    <title>Instructor | Learnix - Student Engagement</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Monitor student engagement with interactive dashboards, track participation, and boost interaction with actionable insights." />
    <meta name="author" content="Learnix Team" />
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <!-- Third-party CSS -->
    <link href="assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <!-- App CSS -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style"/>
    <!-- Custom CSS for Student Engagement -->
    <style>
        .engagement-card { 
            border-radius: 10px; 
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); 
            transition: transform 0.3s ease-in-out; 
        }
        .engagement-card:hover { 
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
            .engagement-card { 
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
                                        <button class="btn btn-outline-secondary dropdown-toggle filter-btn ms-2" type="button" id="timeFilter" data-bs-toggle="dropdown" aria-expanded="false">
                                            Filter by Time
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="timeFilter">
                                            <li><a class="dropdown-item" href="#">Last 7 Days</a></li>
                                            <li><a class="dropdown-item" href="#">Last 30 Days</a></li>
                                            <li><a class="dropdown-item" href="#">This Semester</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <h4 class="page-title">Student Engagement</h4>
                            </div>
                        </div>
                    </div>
                    <!-- End Page Title -->

                    <!-- Engagement Overview -->
                    <div class="row">
                        <!-- Participation Rate -->
                        <div class="col-xl-3 col-md-6">
                            <div class="card engagement-card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Participation Rate</h5>
                                    <h3 class="mt-3 mb-2">72%</h3>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 72%" aria-valuenow="72" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <p class="text-muted mt-2">Up 4% from last week</p>
                                </div>
                            </div>
                        </div>
                        <!-- Average Time Spent -->
                        <div class="col-xl-3 col-md-6">
                            <div class="card engagement-card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Avg. Time Spent</h5>
                                    <h3 class="mt-3 mb-2">3.5 hrs</h3>
                                    <div class="progress">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: 65%" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <p class="text-muted mt-2">Stable from last week</p>
                                </div>
                            </div>
                        </div>
                        <!-- Discussion Activity -->
                        <div class="col-xl-3 col-md-6">
                            <div class="card engagement-card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Discussion Posts</h5>
                                    <h3 class="mt-3 mb-2">245</h3>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: 60%" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <p class="text-muted mt-2">Down 2% from last week</p>
                                </div>
                            </div>
                        </div>
                        <!-- Quiz Interactions -->
                        <div class="col-xl-3 col-md-6">
                            <div class="card engagement-card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Quiz Interactions</h5>
                                    <h3 class="mt-3 mb-2">180</h3>
                                    <div class="progress">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <p class="text-muted mt-2">Up 10% from last week</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div class="row">
                        <!-- Engagement Trend Chart -->
                        <div class="col-xl-6">
                            <div class="card engagement-card">
                                <div class="card-body chart-container">
                                    <h5 class="card-title">Engagement Trend</h5>
                                    <div id="engagement-trend-chart" style="height: 300px;"></div>
                                </div>
                            </div>
                        </div>
                        <!-- Activity Breakdown Chart -->
                        <div class="col-xl-6">
                            <div class="card engagement-card">
                                <div class="card-body chart-container">
                                    <h5 class="card-title">Activity Breakdown</h5>
                                    <div id="activity-breakdown-chart" style="height: 300px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Student Engagement Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card engagement-card">
                                <div class="card-body">
                                    <h5 class="card-title">Student Engagement Details</h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Student Name</th>
                                                    <th>Course</th>
                                                    <th>Participation</th>
                                                    <th>Time Spent</th>
                                                    <th>Discussion Posts</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Jane Doe</td>
                                                    <td>Intro to Programming</td>
                                                    <td><div class="progress progress-bar"><div class="progress-bar bg-success" style="width: 80%"></div></div></td>
                                                    <td>4.2 hrs</td>
                                                    <td>12</td>
                                                    <td><a href="#" class="btn btn-sm btn-outline-primary">View Profile</a></td>
                                                </tr>
                                                <tr>
                                                    <td>John Smith</td>
                                                    <td>Data Science</td>
                                                    <td><div class="progress progress-bar"><div class="progress-bar bg-warning" style="width: 55%"></div></div></td>
                                                    <td>2.8 hrs</td>
                                                    <td>5</td>
                                                    <td><a href="#" class="btn btn-sm btn-outline-primary">View Profile</a></td>
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
                            <div class="card engagement-card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Actionable Insights</h5>
                                    <ul class="list-unstyled insights-list">
                                        <li><i class="mdi mdi-bell-outline me-2"></i> Send reminders to 3 students with low participation in "Data Science."</li>
                                        <li><i class="mdi mdi-forum-outline me-2"></i> Encourage discussion by posting a new prompt in "Intro to Programming."</li>
                                        <li><i class="mdi mdi-check-circle-outline me-2"></i> High quiz interaction in "Data Science"; consider adding more quizzes.</li>
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
        // Engagement Trend Chart (Line)
        var trendOptions = {
            chart: { type: 'line', height: 300, animations: { enabled: true } },
            series: [{ name: 'Engagement Score', data: [60, 65, 70, 68, 72] }],
            xaxis: { categories: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'] },
            colors: ['#007bff'],
            dataLabels: { enabled: false },
            tooltip: { enabled: true },
            stroke: { curve: 'smooth' }
        };
        var trendChart = new ApexCharts(document.querySelector("#engagement-trend-chart"), trendOptions);
        trendChart.render();

        // Activity Breakdown Chart (Donut)
        var breakdownOptions = {
            chart: { type: 'donut', height: 300 },
            series: [45, 30, 15, 10],
            labels: ['Video Views', 'Quiz Attempts', 'Discussion Posts', 'Assignments'],
            colors: ['#28a745', '#007bff', '#ffc107', '#dc3545'],
            dataLabels: { enabled: true },
            tooltip: { enabled: true },
            legend: { position: 'bottom' }
        };
        var breakdownChart = new ApexCharts(document.querySelector("#activity-breakdown-chart"), breakdownOptions);
        breakdownChart.render();
    </script>
</body>
</html>
