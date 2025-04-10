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
        <title>Instructor | Learnix - Create and Manage Courses</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Intuitive dashboard for instructors to create, manage courses, track student progress, and engage learners effectively." />
        <meta name="author" content="Learnix Team" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">

         <!-- CRITICAL: Add this script before any CSS loads to prevent flash -->
    <script>
    // This script prevents the flash by applying the theme immediately
    (function() {
        // Try to get saved settings
        var savedSettings = localStorage.getItem('hyperAppSettings');
        
        if (savedSettings) {
            try {
                var settings = JSON.parse(savedSettings);
                
                // Apply critical theme settings before page renders
                if (settings.isDarkMode) {
                    document.documentElement.setAttribute('data-theme', 'dark');
                    document.body.setAttribute('data-layout-color', 'dark');
                } else {
                    document.documentElement.setAttribute('data-theme', 'light');
                    document.body.setAttribute('data-layout-color', 'light');
                }
                
                // Apply other layout attributes
                if (settings.layoutMode) {
                    document.body.setAttribute('data-layout-mode', settings.layoutMode);
                }
                
                if (settings.leftbarTheme) {
                    document.body.setAttribute('data-leftbar-theme', settings.leftbarTheme);
                }
                
                if (settings.leftbarCompactMode) {
                    document.body.setAttribute('data-leftbar-compact-mode', settings.leftbarCompactMode);
                }
            } catch (e) {
                console.error('Error applying early theme settings:', e);
            }
        }
    })();
    </script>

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
                                    <h4 class="page-title">Annoucements</h4>
                                </div>
                            </div>
                        </div>
                        <!-- end page title -->



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