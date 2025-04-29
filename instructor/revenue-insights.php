
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
    <title>Instructor | Learnix - Revenue Insights</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Track course revenue, analyze trends, and maximize earnings with interactive dashboards and actionable insights." />
    <meta name="author" content="Learnix Team" />
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <!-- Third-party CSS -->
    <link href="assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <!-- App CSS -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style"/>
    <!-- Custom CSS for Revenue Insights -->
    <style>
        .revenue-card { 
            border-radius: 10px; 
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); 
            transition: transform 0.3s ease-in-out; 
        }
        .revenue-card:hover { 
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
            .revenue-card { 
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
                                <h4 class="page-title">Revenue Insights</h4>
                            </div>
                        </div>
                    </div>
                    <!-- End Page Title -->

                    <!-- Revenue Metrics -->
                    <div class="row">
                        <!-- Total Revenue -->
                        <div class="col-xl-3 col-md-6">
                            <div class="card revenue-card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Total Revenue</h5>
                                    <h3 class="mt-3 mb-2">$12,450</h3>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <p class="text-muted mt-2">Up 10% from last month</p>
                                </div>
                            </div>
                        </div>
                        <!-- Avg. Revenue per Student -->
                        <div class="col-xl-3 col-md-6">
                            <div class="card revenue-card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Avg. Revenue/Student</h5>
                                    <h3 class="mt-3 mb-2">$103</h3>
                                    <div class="progress">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: 70%" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <p class="text-muted mt-2">Stable from last month</p>
                                </div>
                            </div>
                        </div>
                        <!-- Enrollment Revenue -->
                        <div class="col-xl-3 col-md-6">
                            <div class="card revenue-card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Enrollment Revenue</h5>
                                    <h3 class="mt-3 mb-2">$9,800</h3>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: 78%" aria-valuenow="78" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <p class="text-muted mt-2">Up 5% from last month</p>
                                </div>
                            </div>
                        </div>
                        <!-- Revenue Growth Rate -->
                        <div class="col-xl-3 col-md-6">
                            <div class="	card revenue-card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Growth Rate</h5>
                                    <h3 class="mt-3 mb-2">12%</h3>
                                    <div class="progress">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 90%" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <p class="text-muted mt-2">Positive trend this quarter</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div class="row">
                        <!-- Revenue Trend Chart -->
                        <div class="col-xl-6">
                            <div class="card revenue-card">
                                <div class="card-body chart-container">
                                    <h5 class="card-title">Revenue Trend</h5>
                                    <div id="revenue-trend-chart" style="height: 300px;"></div>
                                </div>
                            </div>
                        </div>
                        <!-- Course Revenue Breakdown Chart -->
                        <div class="col-xl-6">
                            <div class="card revenue-card">
                                <div class="card-body chart-container">
                                    <h5 class="card-title">Course Revenue Breakdown</h5>
                                    <div id="course-revenue-chart" style="height: 300px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue Details Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card revenue-card">
                                <div class="card-body">
                                    <h5 class="card-title">Revenue Details</h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Course</th>
                                                    <th>Enrollments</th>
                                                    <th>Revenue</th>
                                                    <th>Avg. Revenue/Student</th>
                                                    <th>Payment Type</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Intro to Programming</td>
                                                    <td>80</td>
                                                    <td>$8,000</td>
                                                    <td>$100</td>
                                                    <td>One-Time</td>
                                                    <td><a href="#" class="btn btn-sm btn-outline-primary">View Details</a></td>
                                                </tr>
                                                <tr>
                                                    <td>Data Science</td>
                                                    <td>40</td>
                                                    <td>$4,450</td>
                                                    <td>$111</td>
                                                    <td>Subscription</td>
                                                    <td><a href="#" class="btn btn-sm btn-outline-primary">View Details</a></td>
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
                            <div class="card revenue-card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Actionable Insights</h5>
                                    <ul class="list-unstyled insights-list">
                                        <li><i class="mdi mdi-trending-up me-2"></i> "Intro to Programming" has high enrollment; consider offering a premium version.</li>
                                        <li><i class="mdi mdi-bullhorn-outline me-2"></i> Promote "Data Science" to increase subscriptions.</li>
                                        <li><i class="mdi mdi-check-circle-outline me-2"></i> Revenue growth is strong; explore course bundles for upselling.</li>
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
        // Revenue Trend Chart (Line)
        var trendOptions = {
            chart: { type: 'line', height: 300, animations: { enabled: true } },
            series: [{ name: 'Revenue', data: [2000, 2500, 3000, 3500, 4000] }],
            xaxis: { categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May'] },
            colors: ['#28a745'],
            dataLabels: { enabled: false },
            tooltip: { enabled: true },
            stroke: { curve: 'smooth' }
        };
        var trendChart = new ApexCharts(document.querySelector("#revenue-trend-chart"), trendOptions);
        trendChart.render();

        // Course Revenue Breakdown Chart (Pie)
        var breakdownOptions = {
            chart: { type: 'pie', height: 300 },
            series: [8000, 4450],
            labels: ['Intro to Programming', 'Data Science'],
            colors: ['#007bff', '#ffc107'],
            dataLabels: { enabled: true },
            tooltip: { enabled: true },
            legend: { position: 'bottom' }
        };
        var breakdownChart = new ApexCharts(document.querySelector("#course-revenue-chart"), breakdownOptions);
        breakdownChart.render();
    </script>
</body>
</html>
