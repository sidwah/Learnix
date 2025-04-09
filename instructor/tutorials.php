<?php
require '../backend/session_start.php'; // Ensure session is started

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    // Log unauthorized access attempt for security auditing
    error_log("Unauthorized access attempt detected: " . json_encode($_SERVER));

    // Redirect unauthorized users to a custom unauthorized access page or login page
    header('Location: landing.php');
    exit;
}
?>




<!DOCTYPE html>
    <html lang="en">

    
    <head>
        <meta charset="utf-8" />
        <title>Instructor | Learnix - Empowering Education</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="An intuitive instructor dashboard to manage courses, track student progress, and enhance the learning experience." name="description" />
        <meta content="Learnix Development Team" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">

        <!-- third party css -->
        <link href="assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
        <!-- third party css end -->

        <!-- App css -->
        <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style"/>

    </head>

    <body class="loading" data-layout-color="light" data-leftbar-theme="dark" data-layout-mode="fluid" data-rightbar-onstart="true">
        <!-- Begin page -->
        <div class="wrapper">
            <!-- ========== Left Sidebar Start ========== -->
            <?php 
                include '../includes/instructor-sidebar.php'; 
            ?>
                        
            <!-- Left Sidebar End -->

            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->

            <div class="content-page">
                <div class="content">
                    <!-- Topbar Start -->
                    <?php 
                        include '../includes/instructor-topnavbar.php'; 
                    ?>
                    <!-- end Topbar -->
                    
                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title">Tutorials</h4>
                                </div>
                            </div>
                        </div>
                        <!-- end page title -->
<!-- inside <div class="container-fluid"> -->

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <h4 class="header-title mb-3">Instructor Onboarding Tutorials</h4>

                <div class="row">
                    <!-- Tutorial Card 1 -->
                    <div class="col-md-6 col-xl-4">
                        <div class="card border shadow-none mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Creating Your First Course</h5>
                                <p class="card-text">
                                    Learn how to set up a course title, description, category, and structure your modules and lessons.
                                </p>
                                <ol>
                                    <li>Go to "Courses" in the sidebar</li>
                                    <li>Click "Create New Course"</li>
                                    <li>Fill out the course details and save</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <!-- Tutorial Card 2 -->
                    <div class="col-md-6 col-xl-4">
                        <div class="card border shadow-none mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Uploading Lessons</h5>
                                <p class="card-text">
                                    Use the drag-and-drop uploader to add videos, documents, text content, or external links.
                                </p>
                                <ol>
                                    <li>Select a course you've created</li>
                                    <li>Click "Add Module" > "Add Topic"</li>
                                    <li>Choose content type and upload your material</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <!-- Tutorial Card 3 -->
                    <div class="col-md-6 col-xl-4">
                        <div class="card border shadow-none mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Adding a Quiz</h5>
                                <p class="card-text">
                                    Include multiple-choice or true/false quizzes at the end of modules.
                                </p>
                                <ol>
                                    <li>While adding a topic, select "Quiz" as the type</li>
                                    <li>Input your questions and answer options</li>
                                    <li>Save and preview the quiz</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <!-- Tutorial Card 4 -->
                    <div class="col-md-6 col-xl-4">
                        <div class="card border shadow-none mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Tracking Student Progress</h5>
                                <p class="card-text">
                                    Monitor enrolled students and how they're progressing through your course.
                                </p>
                                <ol>
                                    <li>Click on your course analytics</li>
                                    <li>View completion rates and quiz scores</li>
                                    <li>Download progress reports</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <!-- Tutorial Card 5 -->
                    <div class="col-md-6 col-xl-4">
                        <div class="card border shadow-none mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Managing Your Earnings</h5>
                                <p class="card-text">
                                    View your balance, track sales, and request withdrawals through Stripe.
                                </p>
                                <ol>
                                    <li>Open the "Earnings" tab in the sidebar</li>
                                    <li>Check your available balance</li>
                                    <li>Click "Withdraw" if your balance is above the threshold</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <!-- You can add more tutorial sections here -->
                </div>

            </div>
        </div>
    </div>
</div>



                    </div>
                    <!-- container -->

                </div>
                <!-- content -->

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

            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->


        </div>
        <!-- END wrapper -->

    <?php include '../includes/instructor-darkmode.php'; ?>


        <!-- bundle -->
        <script src="assets/js/vendor.min.js"></script>
        <script src="assets/js/app.min.js"></script>

        <!-- third party js -->
        <script src="assets/js/vendor/apexcharts.min.js"></script>
        <script src="assets/js/vendor/jquery-jvectormap-1.2.2.min.js"></script>
        <script src="assets/js/vendor/jquery-jvectormap-world-mill-en.js"></script>
        <!-- third party js ends -->

        <!-- demo app -->
        <script src="assets/js/pages/demo.dashboard.js"></script>
        <!-- end demo js-->
    </body>


</html>