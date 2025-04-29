
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
    <title>Instructor | Learnix - Custom Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Generate custom reports with flexible metrics, interactive visualizations, and export options to analyze course performance." />
    <meta name="author" content="Learnix Team" />
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <!-- Third-party CSS -->
    <link href="assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <!-- App CSS -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style"/>
    <!-- Custom CSS for Custom Report -->
    <style>
        .report-card { 
            border-radius: 10px; 
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); 
            transition: transform 0.3s ease-in-out; 
        }
        .report-card:hover { 
            transform: translateY(-5px); 
        }
        .chart-container { 
            min-height: 300px; 
            padding: 20px; 
        }
        .filter-btn, .export-btn { 
            border-radius: 20px; 
            padding: 8px 16px; 
            font-size: 14px; 
        }
        .form-select { 
            border-radius: 8px; 
        }
        .insights-list li { 
            margin-bottom: 10px; 
        }
        .saved-report-card { 
            cursor: pointer; 
            transition: background-color 0.3s; 
        }
        .saved-report-card:hover { 
            background-color: #f8f9fa; 
        }
        @media (max-width: 768px) {
            .report-card { 
                margin-bottom: 20px; 
            }
            .filter-btn, .export-btn { 
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
                                    <button class="btn btn-primary export-btn" onclick="exportReport('png')">Export as PNG</button>
                                    <button class="btn btn-outline-secondary export-btn ms-2" onclick="exportReport('csv')">Export as CSV</button>
                                </div>
                                <h4 class="page-title">Custom Report</h4>
                            </div>
                        </div>
                    </div>
                    <!-- End Page Title -->

                    <!-- Report Builder -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card report-card">
                                <div class="card-body">
                                    <h5 class="card-title">Build Your Report</h5>
                                    <form id="reportForm">
                                        <div class="row">
                                            <!-- Metrics Selection -->
                                            <div class="col-md-4 mb-3">
                                                <label for="metrics" class="form-label">Select Metrics</label>
                                                <select class="form-select" id="metrics" multiple aria-label="Select metrics">
                                                    <option value="completion_rate">Completion Rate</option>
                                                    <option value="avg_score">Average Quiz Score</option>
                                                    <option value="engagement_score">Engagement Score</option>
                                                    <option value="revenue">Revenue</option>
                                                    <option value="enrollments">Enrollments</option>
                                                </select>
                                            </div>
                                            <!-- Course Selection -->
                                            <div class="col-md-4 mb-3">
                                                <label for="courses" class="form-label">Select Courses</label>
                                                <select class="form-select" id="courses" multiple aria-label="Select courses">
                                                    <option value="all">All Courses</option>
                                                    <option value="intro_programming">Intro to Programming</option>
                                                    <option value="data_science">Data Science</option>
                                                </select>
                                            </div>
                                            <!-- Time Period -->
                                            <div class="col-md-4 mb-3">
                                                <label for="time_period" class="form-label">Time Period</label>
                                                <select class="form-select" id="time_period" aria-label="Select time period">
                                                    <option value="7_days">Last 7 Days</option>
                                                    <option value="30_days">Last 30 Days</option>
                                                    <option value="semester">This Semester</option>
                                                    <option value="custom">Custom Range</option>
                                                </select>
                                            </div>
                                        </div>
                                        <!-- Visualization Type -->
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="visualization" class="form-label">Visualization Type</label>
                                                <select class="form-select" id="visualization" aria-label="Select visualization type">
                                                    <option value="bar">Bar Chart</option>
                                                    <option value="line">Line Chart</option>
                                                    <option value="pie">Pie Chart</option>
                                                    <option value="table">Table</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3 d-flex align-items-end">
                                                <button type="button" class="btn btn-primary w-100" onclick="generateReport()">Generate Report</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Report Preview -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card report-card">
                                <div class="card-body">
                                    <h5 class="card-title">Report Preview</h5>
                                    <div id="report-preview" class="chart-container">
                                        <div id="report-chart" style="height: 300px;"></div>
                                        <div id="report-table" class="table-responsive d-none">
                                            <table class="table table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Metric</th>
                                                        <th>Value</th>
                                                        <th>Course</th>
                                                        <th>Time Period</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="report-table-body">
                                                    <!-- Dynamically populated -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Saved Reports -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card report-card">
                                <div class="card-body">
                                    <h5 class="card-title">Saved Reports</h5>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="card saved-report-card">
                                                <div class="card-body">
                                                    <h6>Engagement Report - Last 30 Days</h6>
                                                    <p class="text-muted">Generated: April 20, 2025</p>
                                                    <a href="#" class="btn btn-sm btn-outline-primary">View</a>
                                                    <a href="#" class="btn btn-sm btn-outline-danger ms-2">Delete</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card saved-report-card">
                                                <div class="card-body">
                                                    <h6>Revenue Report - This Semester</h6>
                                                    <p class="text-muted">Generated: April 15, 2025</p>
                                                    <a href="#" class="btn btn-sm btn-outline-primary">View</a>
                                                    <a href="#" class="btn btn-sm btn-outline-danger ms-2">Delete</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actionable Insights -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card report-card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Actionable Insights</h5>
                                    <ul class="list-unstyled insights-list">
                                        <li><i class="mdi mdi-chart-line me-2"></i> Add revenue metrics to track course profitability.</li>
                                        <li><i class="mdi mdi-filter-outline me-2"></i> Use custom date ranges for more granular analysis.</li>
                                        <li><i class="mdi mdi-content-save-outline me-2"></i> Save frequently used reports for quick access.</li>
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
    <!-- Use CDN for ApexCharts to ensure stable version -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.2/dist/apexcharts.min.js"></script>
    <script src="assets/js/vendor/jquery-jvectormap-1.2.2.min.js"></script>
    <script src="assets/js/vendor/jquery-jvectormap-world-mill-en.js"></script>
    <!-- PapaParse for CSV Export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.3.2/papaparse.min.js"></script>
    <!-- Custom JS for Report Simulation -->
    <script>
        let currentChart = null;

        // Mock data for simulation
        const mockData = {
            'intro_programming': {
                '7_days': {
                    completion_rate: 80,
                    avg_score: 85,
                    engagement_score: 70,
                    revenue: 2000,
                    enrollments: 50
                },
                '30_days': {
                    completion_rate: 78,
                    avg_score: 82,
                    engagement_score: 65,
                    revenue: 8000,
                    enrollments: 80
                },
                'semester': {
                    completion_rate: 75,
                    avg_score: 80,
                    engagement_score: 60,
                    revenue: 12000,
                    enrollments: 120
                }
            },
            'data_science': {
                '7_days': {
                    completion_rate: 70,
                    avg_score: 78,
                    engagement_score: 60,
                    revenue: 1500,
                    enrollments: 30
                },
                '30_days': {
                    completion_rate: 68,
                    avg_score: 75,
                    engagement_score: 55,
                    revenue: 4450,
                    enrollments: 40
                },
                'semester': {
                    completion_rate: 65,
                    avg_score: 72,
                    engagement_score: 50,
                    revenue: 9000,
                    enrollments: 90
                }
            },
            'all': {
                '7_days': {
                    completion_rate: 75,
                    avg_score: 82,
                    engagement_score: 65,
                    revenue: 3500,
                    enrollments: 80
                },
                '30_days': {
                    completion_rate: 73,
                    avg_score: 79,
                    engagement_score: 60,
                    revenue: 12450,
                    enrollments: 120
                },
                'semester': {
                    completion_rate: 70,
                    avg_score: 76,
                    engagement_score: 55,
                    revenue: 21000,
                    enrollments: 210
                }
            }
        };

        function generateReport() {
            const metrics = Array.from(document.querySelector('#metrics').selectedOptions).map(opt => opt.value);
            const courses = Array.from(document.querySelector('#courses').selectedOptions).map(opt => opt.value);
            const timePeriod = document.querySelector('#time_period').value;
            const visualization = document.querySelector('#visualization').value;

            if (metrics.length === 0 || courses.length === 0) {
                alert('Please select at least one metric and one course.');
                return;
            }

            // Toggle chart/table visibility
            const chartContainer = document.querySelector('#report-chart');
            const tableContainer = document.querySelector('#report-table');
            if (visualization === 'table') {
                chartContainer.classList.add('d-none');
                tableContainer.classList.remove('d-none');
            } else {
                chartContainer.classList.remove('d-none');
                tableContainer.classList.add('d-none');
            }

            // Generate data for selected courses
            const reportData = courses.map(course => ({
                course,
                metrics: metrics.map(metric => ({
                    name: metric,
                    value: mockData[course][timePeriod][metric] || 0,
                    time: timePeriod.replace('_', ' ')
                }))
            }));

            // Generate chart
            if (visualization !== 'table') {
                // Destroy previous chart if exists
                if (currentChart) {
                    currentChart.destroy();
                }

                const chartOptions = {
                    chart: { 
                        type: visualization === 'pie' ? 'pie' : visualization, 
                        height: 300, 
                        animations: { enabled: true } 
                    },
                    series: visualization === 'pie' ? 
                        metrics.map(metric => reportData[0].metrics.find(m => m.name === metric).value) :
                        metrics.map(metric => ({
                            name: metric.replace('_', ' ').toUpperCase(),
                            data: courses.map(course => reportData.find(r => r.course === course).metrics.find(m => m.name === metric).value)
                        })),
                    xaxis: visualization !== 'pie' ? { 
                        categories: courses.map(c => c.replace('_', ' ').toUpperCase()),
                        title: { text: 'Courses' }
                    } : undefined,
                    ...(visualization === 'pie' ? { labels: metrics.map(m => m.replace('_', ' ').toUpperCase()) } : {}),
                    colors: ['#28a745', '#007bff', '#ffc107', '#dc3545', '#17a2b8'],
                    dataLabels: { enabled: visualization !== 'line' },
                    tooltip: { enabled: true },
                    stroke: { curve: visualization === 'line' ? 'smooth' : 'straight' },
                    legend: { position: visualization === 'pie' ? 'bottom' : 'top' }
                };

                console.log('Chart Options:', chartOptions); // Debug logging

                currentChart = new ApexCharts(chartContainer, chartOptions);
                currentChart.render();
            } else {
                // Generate table
                const tbody = document.querySelector('#report-table-body');
                tbody.innerHTML = '';
                reportData.forEach(report => {
                    report.metrics.forEach(metric => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${metric.name.replace('_', ' ').toUpperCase()}</td>
                            <td>${metric.name === 'revenue' ? '$' + metric.value.toLocaleString() : metric.value}</td>
                            <td>${report.course.replace('_', ' ').toUpperCase()}</td>
                            <td>${metric.time.toUpperCase()}</td>
                        `;
                        tbody.appendChild(row);
                    });
                });
            }
        }

        function exportReport(format) {
            const metrics = Array.from(document.querySelector('#metrics').selectedOptions).map(opt => opt.value);
            const courses = Array.from(document.querySelector('#courses').selectedOptions).map(opt => opt.value);
            const timePeriod = document.querySelector('#time_period').value;
            const visualization = document.querySelector('#visualization').value;

            if (!currentChart && visualization !== 'table') {
                alert('Please generate a report first.');
                return;
            }

            if (format === 'png' && visualization !== 'table') {
                currentChart.dataURI().then(({ imgURI }) => {
                    const link = document.createElement('a');
                    link.href = imgURI;
                    link.download = 'report.png';
                    link.click();
                });
            } else if (format === 'csv') {
                const reportData = courses.map(course => {
                    const metricsData = metrics.reduce((acc, metric) => {
                        acc[metric.replace('_', ' ').toUpperCase()] = mockData[course][timePeriod][metric] || 0;
                        return acc;
                    }, {});
                    return {
                        Course: course.replace('_', ' ').toUpperCase(),
                        Time_Period: timePeriod.replace('_', ' ').toUpperCase(),
                        ...metricsData
                    };
                });

                const csv = Papa.unparse(reportData);
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'report.csv';
                link.click();
            }
        }
    </script>
</body>
</html>
