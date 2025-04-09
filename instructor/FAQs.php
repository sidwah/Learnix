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
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

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
                                <h4 class="page-title">FAQs</h4>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->
                    <!-- Instructor FAQs Start -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="accordion custom-accordion" id="instructor-faqs">
                                        <!-- Q1 -->
                                        <div class="card mb-0">
                                            <div class="card-header" id="faq1">
                                                <h5 class="m-0">
                                                    <a class="custom-accordion-title d-block py-1"
                                                        data-bs-toggle="collapse" href="#collapse1"
                                                        aria-expanded="true" aria-controls="collapse1">
                                                        Q. How do I create a course on Learnix? <i class="mdi mdi-chevron-down accordion-arrow"></i>
                                                    </a>
                                                </h5>
                                            </div>
                                            <div id="collapse1" class="collapse show" aria-labelledby="faq1" data-bs-parent="#instructor-faqs">
                                                <div class="card-body">
                                                    Go to your dashboard, click on “Courses” > “Create New Course.” Fill in the course details, upload content, and set pricing if needed.
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Q2 -->
                                        <div class="card mb-0">
                                            <div class="card-header" id="faq2">
                                                <h5 class="m-0">
                                                    <a class="custom-accordion-title collapsed d-block py-1"
                                                        data-bs-toggle="collapse" href="#collapse2"
                                                        aria-expanded="false" aria-controls="collapse2">
                                                        Q. Can I edit a course after publishing it? <i class="mdi mdi-chevron-down accordion-arrow"></i>
                                                    </a>
                                                </h5>
                                            </div>
                                            <div id="collapse2" class="collapse" aria-labelledby="faq2" data-bs-parent="#instructor-faqs">
                                                <div class="card-body">
                                                    Yes, you can edit course content, titles, and pricing from your course management page at any time.
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Q3 -->
                                        <div class="card mb-0">
                                            <div class="card-header" id="faq3">
                                                <h5 class="m-0">
                                                    <a class="custom-accordion-title collapsed d-block py-1"
                                                        data-bs-toggle="collapse" href="#collapse3"
                                                        aria-expanded="false" aria-controls="collapse3">
                                                        Q. How do I track student progress? <i class="mdi mdi-chevron-down accordion-arrow"></i>
                                                    </a>
                                                </h5>
                                            </div>
                                            <div id="collapse3" class="collapse" aria-labelledby="faq3" data-bs-parent="#instructor-faqs">
                                                <div class="card-body">
                                                    Go to “Students” under your dashboard to view their course completion status, quiz scores, and engagement analytics.
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Q4 -->
                                        <div class="card mb-0">
                                            <div class="card-header" id="faq4">
                                                <h5 class="m-0">
                                                    <a class="custom-accordion-title collapsed d-block py-1"
                                                        data-bs-toggle="collapse" href="#collapse4"
                                                        aria-expanded="false" aria-controls="collapse4">
                                                        Q. How do I get paid for my courses? <i class="mdi mdi-chevron-down accordion-arrow"></i>
                                                    </a>
                                                </h5>
                                            </div>
                                            <div id="collapse4" class="collapse" aria-labelledby="faq4" data-bs-parent="#instructor-faqs">
                                                <div class="card-body">
                                                    Payments are made through Stripe. Earnings can be withdrawn once your balance exceeds the set threshold. Visit “Earnings” for more details.
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Q5 -->
                                        <div class="card mb-0">
                                            <div class="card-header" id="faq5">
                                                <h5 class="m-0">
                                                    <a class="custom-accordion-title collapsed d-block py-1"
                                                        data-bs-toggle="collapse" href="#collapse5"
                                                        aria-expanded="false" aria-controls="collapse5">
                                                        Q. Do courses need admin approval before going live? <i class="mdi mdi-chevron-down accordion-arrow"></i>
                                                    </a>
                                                </h5>
                                            </div>
                                            <div id="collapse5" class="collapse" aria-labelledby="faq5" data-bs-parent="#instructor-faqs">
                                                <div class="card-body">
                                                    If you’re a verified instructor, your course goes live immediately. If not, it will be reviewed by an admin first.
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Q6 -->
                                        <div class="card mb-0">
                                            <div class="card-header" id="faq6">
                                                <h5 class="m-0">
                                                    <a class="custom-accordion-title collapsed d-block py-1"
                                                        data-bs-toggle="collapse" href="#collapse6"
                                                        aria-expanded="false" aria-controls="collapse6">
                                                        Q. How can I get verified as an instructor? <i class="mdi mdi-chevron-down accordion-arrow"></i>
                                                    </a>
                                                </h5>
                                            </div>
                                            <div id="collapse6" class="collapse" aria-labelledby="faq6" data-bs-parent="#instructor-faqs">
                                                <div class="card-body">
                                                    Go to your profile settings and submit the required documents under “Verification.” The admin team will review and approve within 48 hours.
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Q7 -->
                                        <div class="card mb-0">
                                            <div class="card-header" id="faq7">
                                                <h5 class="m-0">
                                                    <a class="custom-accordion-title collapsed d-block py-1"
                                                        data-bs-toggle="collapse" href="#collapse7"
                                                        aria-expanded="false" aria-controls="collapse7">
                                                        Q. What kind of content can I upload? <i class="mdi mdi-chevron-down accordion-arrow"></i>
                                                    </a>
                                                </h5>
                                            </div>
                                            <div id="collapse7" class="collapse" aria-labelledby="faq7" data-bs-parent="#instructor-faqs">
                                                <div class="card-body">
                                                    You can upload videos, PDFs, documents, text lessons, and quizzes. Use the curriculum builder to organize your content effectively.
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Q8 -->
                                        <div class="card mb-0">
                                            <div class="card-header" id="faq8">
                                                <h5 class="m-0">
                                                    <a class="custom-accordion-title collapsed d-block py-1"
                                                        data-bs-toggle="collapse" href="#collapse8"
                                                        aria-expanded="false" aria-controls="collapse8">
                                                        Q. Who do I contact if I need support? <i class="mdi mdi-chevron-down accordion-arrow"></i>
                                                    </a>
                                                </h5>
                                            </div>
                                            <div id="collapse8" class="collapse" aria-labelledby="faq8" data-bs-parent="#instructor-faqs">
                                                <div class="card-body">
                                                    You can reach out through the “Support” section in the sidebar or email us at support@learnix.com.
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Instructor FAQs End -->

                </div>
                <!-- container -->

            </div>
            <!-- content -->

            <!-- Footer Start -->
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