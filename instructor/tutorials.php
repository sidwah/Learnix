
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
    <title>Instructor | Learnix - Tutorials</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Access step-by-step guides and video tutorials to master course creation, analytics, and other Learnix features." />
    <meta name="author" content="Learnix Team" />
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <!-- Third-party CSS -->
    <link href="assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <!-- App CSS -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style"/>
    <!-- Custom CSS for Tutorials -->
    <style>
        .tutorial-card { 
            border-radius: 10px; 
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); 
            transition: transform 0.3s ease-in-out; 
        }
        .tutorial-card:hover { 
            transform: translateY(-5px); 
        }
        .tutorial-item { 
            margin-bottom: 20px; 
            border: 1px solid #e9ecef; 
            border-radius: 8px; 
            overflow: hidden; 
        }
        .tutorial-header { 
            padding: 15px; 
            cursor: pointer; 
            background-color: #f8f9fa; 
        }
        .tutorial-header:hover { 
            background-color: #f1f3f5; 
        }
        .tutorial-body { 
            padding: 15px; 
            display: none; 
        }
        .tutorial-body.show { 
            display: block; 
        }
        .form-control, .form-select { 
            border-radius: 8px; 
        }
        .insights-list li { 
            margin-bottom: 10px; 
        }
        .action-btn { 
            border-radius: 20px; 
            padding: 8px 16px; 
            font-size: 14px; 
        }
        .video-placeholder { 
            background-color: #e9ecef; 
            height: 200px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            border-radius: 8px; 
            margin-bottom: 15px; 
        }
        @media (max-width: 768px) {
            .tutorial-card { 
                margin-bottom: 20px; 
            }
            .action-btn { 
                width: 100%; 
                margin-bottom: 10px; 
            }
            .video-placeholder { 
                height: 150px; 
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
                                    <a href="#" class="btn btn-primary action-btn">Contact Support</a>
                                </div>
                                <h4 class="page-title">Tutorials</h4>
                            </div>
                        </div>
                    </div>
                    <!-- End Page Title -->

                    <!-- Filters -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card tutorial-card">
                                <div class="card-body">
                                    <h5 class="card-title">Find Tutorials</h5>
                                    <form id="filterForm">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="searchInput" class="form-label">Search</label>
                                                <input type="text" class="form-control" id="searchInput" placeholder="Search tutorials..." aria-label="Search tutorials">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="categoryFilter" class="form-label">Category</label>
                                                <select class="form-select" id="categoryFilter" aria-label="Filter by category">
                                                    <option value="all">All Categories</option>
                                                    <option value="course_creation">Course Creation</option>
                                                    <option value="analytics">Analytics</option>
                                                    <option value="student_management">Student Management</option>
                                                    <option value="notifications">Notifications</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3 d-flex align-items-end">
                                                <button type="button" class="btn btn-primary w-100" onclick="filterTutorials()">Apply Filters</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tutorial List -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card tutorial-card">
                                <div class="card-body">
                                    <h5 class="card-title">Available Tutorials</h5>
                                    <div id="tutorialList">
                                        <!-- Course Creation -->
                                        <div class="tutorial-item" data-category="course_creation">
                                            <div class="tutorial-header" onclick="toggleTutorial(this)">
                                                <h6 class="mb-0">How to Create a New Course</h6>
                                                <small class="text-muted">Category: Course Creation</small>
                                            </div>
                                            <div class="tutorial-body">
                                                <div class="video-placeholder">
                                                    <p class="text-muted">Video: Creating a Course (2:30)</p>
                                                </div>
                                                <h6>Steps:</h6>
                                                <ol>
                                                    <li>Navigate to the Course Management page.</li>
                                                    <li>Click "Create New Course" to open the form.</li>
                                                    <li>Enter the course title, description, category, and status.</li>
                                                    <li>Click "Create Course" to save.</li>
                                                </ol>
                                                <p>Tip: Set the status to "Draft" to refine your course before publishing.</p>
                                            </div>
                                        </div>
                                        <div class="tutorial-item" data-category="course_creation">
                                            <div class="tutorial-header" onclick="toggleTutorial(this)">
                                                <h6 class="mb-0">How to Add Content to a Course</h6>
                                                <small class="text-muted">Category: Course Creation</small>
                                            </div>
                                            <div class="tutorial-body">
                                                <div class="video-placeholder">
                                                    <p class="text-muted">Video: Adding Content (3:15)</p>
                                                </div>
                                                <h6>Steps:</h6>
                                                <ol>
                                                    <li>Go to Course Management and select your course.</li>
                                                    <li>Click "Edit" and navigate to the content section.</li>
                                                    <li>Upload videos, quizzes, or documents as needed.</li>
                                                    <li>Save changes to update the course.</li>
                                                </ol>
                                                <p>Tip: Use engaging visuals to enhance student learning.</p>
                                            </div>
                                        </div>
                                        <!-- Analytics -->
                                        <div class="tutorial-item" data-category="analytics">
                                            <div class="tutorial-header" onclick="toggleTutorial(this)">
                                                <h6 class="mb-0">How to Generate a Custom Report</h6>
                                                <small class="text-muted">Category: Analytics</small>
                                            </div>
                                            <div class="tutorial-body">
                                                <div class="video-placeholder">
                                                    <p class="text-muted">Video: Custom Reports (4:00)</p>
                                                </div>
                                                <h6>Steps:</h6>
                                                <ol>
                                                    <li>Go to the Custom Report page under Performance.</li>
                                                    <li>Select metrics (e.g., completion rate), courses, and time period.</li>
                                                    <li>Choose a visualization type (e.g., bar chart).</li>
                                                    <li>Click "Generate Report" to view results.</li>
                                                </ol>
                                                <p>Tip: Export reports as CSV or PNG for sharing.</p>
                                            </div>
                                        </div>
                                        <div class="tutorial-item" data-category="analytics">
                                            <div class="tutorial-header" onclick="toggleTutorial(this)">
                                                <h6 class="mb-0">How to Track Student Engagement</h6>
                                                <small class="text-muted">Category: Analytics</small>
                                            </div>
                                            <div class="tutorial-body">
                                                <div class="video-placeholder">
                                                    <p class="text-muted">Video: Tracking Engagement (3:45)</p>
                                                </div>
                                                <h6>Steps:</h6>
                                                <ol>
                                                    <li>Visit the Student Engagement page under Performance.</li>
                                                    <li>Use filters to select a course or time period.</li>
                                                    <li>View engagement scores and activity trends.</li>
                                                    <li>Check actionable insights for improvement ideas.</li>
                                                </ol>
                                                <p>Tip: Engage low-performing students with personalized feedback.</p>
                                            </div>
                                        </div>
                                        <!-- Student Management -->
                                        <div class="tutorial-item" data-category="student_management">
                                            <div class="tutorial-header" onclick="toggleTutorial(this)">
                                                <h6 class="mb-0">How to Grade Student Submissions</h6>
                                                <small class="text-muted">Category: Student Management</small>
                                            </div>
                                            <div class="tutorial-body">
                                                <div class="video-placeholder">
                                                    <p class="text-muted">Video: Grading Submissions (2:50)</p>
                                                </div>
                                                <h6>Steps:</h6>
                                                <ol>
                                                    <li>Go to the Student Management page (if available).</li>
                                                    <li>Select a course and view submitted assignments.</li>
                                                    <li>Enter grades and feedback for each submission.</li>
                                                    <li>Save to notify students of their results.</li>
                                                </ol>
                                                <p>Tip: Provide detailed feedback to encourage improvement.</p>
                                            </div>
                                        </div>
                                        <!-- Notifications -->
                                        <div class="tutorial-item" data-category="notifications">
                                            <div class="tutorial-header" onclick="toggleTutorial(this)">
                                                <h6 class="mb-0">How to Manage Notifications</h6>
                                                <small class="text-muted">Category: Notifications</small>
                                            </div>
                                            <div class="tutorial-body">
                                                <div class="video-placeholder">
                                                    <p class="text-muted">Video: Managing Notifications (2:20)</p>
                                                </div>
                                                <h6>Steps:</h6>
                                                <ol>
                                                    <li>Navigate to the Notifications page.</li>
                                                    <li>Filter by type (e.g., Course, Student) or status (Unread, Read).</li>
                                                    <li>Mark notifications as read/unread or delete them.</li>
                                                    <li>Use "Clear All" to remove old notifications.</li>
                                                </ol>
                                                <p>Tip: Regularly check notifications to stay updated.</p>
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
                            <div class="card tutorial-card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Actionable Insights</h5>
                                    <ul class="list-unstyled insights-list">
                                        <li><i class="mdi mdi-video me-2"></i> Watch tutorials to master new Learnix features.</li>
                                        <li><i class="mdi mdi-book-open-page-variant me-2"></i> Visit Course Management to apply what you’ve learned.</li>
                                        <li><i class="mdi mdi-help-circle-outline me-2"></i> Contact support to request new tutorials.</li>
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
    <script src="assets/js/vendor/jquery-jvectormap-1.2.2.min.js"></script>
    <script src="assets/js/vendor/jquery-jvectormap-world-mill-en.js"></script>
    <!-- Custom JS for Tutorial Filtering and Toggling -->
    <script>
        function toggleTutorial(header) {
            const body = header.nextElementSibling;
            body.classList.toggle('show');
        }

        function filterTutorials() {
            const searchInput = document.querySelector('#searchInput').value.toLowerCase();
            const categoryFilter = document.querySelector('#categoryFilter').value;
            const tutorialItems = document.querySelectorAll('.tutorial-item');

            let anyVisible = false;

            tutorialItems.forEach(item => {
                const title = item.querySelector('h6').textContent.toLowerCase();
                const description = item.querySelector('.tutorial-body p')?.textContent.toLowerCase() || '';
                const category = item.getAttribute('data-category');
                const matchesSearch = title.includes(searchInput) || description.includes(searchInput);
                const matchesCategory = categoryFilter === 'all' || category === categoryFilter;

                if (matchesSearch && matchesCategory) {
                    item.style.display = '';
                    anyVisible = true;
                } else {
                    item.style.display = 'none';
                }
            });

            const tutorialList = document.querySelector('#tutorialList');
            if (!anyVisible) {
                tutorialList.innerHTML = '<p class="text-muted">No tutorials found.</p>';
            } else if (!searchInput && categoryFilter === 'all') {
                // Reset to original content if filters are cleared
                tutorialList.innerHTML = '';
                tutorialItems.forEach(item => tutorialList.appendChild(item));
            }
        }

        // Attach event listeners
        document.querySelector('#searchInput').addEventListener('input', filterTutorials);
        document.querySelector('#categoryFilter').addEventListener('change', filterTutorials);

        // Initial render
        filterTutorials();
    </script>
</body>
</html>
