
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
    <title>Instructor | Learnix - FAQ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Find answers to frequently asked questions about managing courses, students, analytics, and system features." />
    <meta name="author" content="Learnix Team" />
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <!-- Third-party CSS -->
    <link href="assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <!-- App CSS -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style"/>
    <!-- Custom CSS for FAQ -->
    <style>
        .faq-card { 
            border-radius: 10px; 
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); 
            transition: transform 0.3s ease-in-out; 
        }
        .faq-card:hover { 
            transform: translateY(-5px); 
        }
        .accordion-button { 
            border-radius: 8px; 
            font-weight: 500; 
        }
        .accordion-button:not(.collapsed) { 
            background-color: #f8f9fa; 
            color: #007bff; 
        }
        .form-control { 
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
        @media (max-width: 768px) {
            .faq-card { 
                margin-bottom: 20px; 
            }
            .action-btn { 
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
                                    <a href="#" class="btn btn-primary action-btn">Contact Support</a>
                                </div>
                                <h4 class="page-title">Frequently Asked Questions</h4>
                            </div>
                        </div>
                    </div>
                    <!-- End Page Title -->

                    <!-- Search Bar -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card faq-card">
                                <div class="card-body">
                                    <h5 class="card-title">Search FAQs</h5>
                                    <form id="searchForm">
                                        <div class="mb-3">
                                            <input type="text" class="form-control" id="faqSearch" placeholder="Search for a question..." aria-label="Search FAQs">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ List -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card faq-card">
                                <div class="card-body">
                                    <h5 class="card-title">FAQs</h5>
                                    <div class="accordion" id="faqAccordion">
                                        <!-- Course Management -->
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="headingCourse">
                                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCourse" aria-expanded="true" aria-controls="collapseCourse">
                                                    Course Management
                                                </button>
                                            </h2>
                                            <div id="collapseCourse" class="accordion-collapse collapse show" aria-labelledby="headingCourse" data-bs-parent="#faqAccordion">
                                                <div class="accordion-body">
                                                    <div class="faq-item">
                                                        <h6>How do I create a new course?</h6>
                                                        <p>Go to the Course Management page, click "Create New Course," and fill out the form with the course title, description, category, and status. Save to add it to your course list.</p>
                                                    </div>
                                                    <div class="faq-item">
                                                        <h6>Can I edit a published course?</h6>
                                                        <p>Yes, on the Course Management page, click "Edit" next to the course. Update the details and save. Note that some changes may require re-approval.</p>
                                                    </div>
                                                    <div class="faq-item">
                                                        <h6>How do I archive a course?</h6>
                                                        <p>In Course Management, edit the course and set its status to "Archived." Archived courses are no longer visible to students but can be reactivated later.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Analytics -->
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="headingAnalytics">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAnalytics" aria-expanded="false" aria-controls="collapseAnalytics">
                                                    Analytics
                                                </button>
                                            </h2>
                                            <div id="collapseAnalytics" class="accordion-collapse collapse" aria-labelledby="headingAnalytics" data-bs-parent="#faqAccordion">
                                                <div class="accordion-body">
                                                    <div class="faq-item">
                                                        <h6>How do I track course performance?</h6>
                                                        <p>Visit the Course Performance page under the Performance section to view completion rates, enrollments, and progress metrics. Use filters to narrow down by course or time period.</p>
                                                    </div>
                                                    <div class="faq-item">
                                                        <h6>What is the Custom Report feature?</h6>
                                                        <p>The Custom Report page lets you generate tailored reports by selecting metrics (e.g., completion rate, revenue), courses, and time periods. You can visualize data as charts or tables and export them.</p>
                                                    </div>
                                                    <div class="faq-item">
                                                        <h6>Why are my analytics empty?</h6>
                                                        <p>Ensure you’ve selected the correct course and time period. If no data appears, it may be due to low enrollment or recent course creation. Contact support if the issue persists.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Notifications -->
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="headingNotifications">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNotifications" aria-expanded="false" aria-controls="collapseNotifications">
                                                    Notifications
                                                </button>
                                            </h2>
                                            <div id="collapseNotifications" class="accordion-collapse collapse" aria-labelledby="headingNotifications" data-bs-parent="#faqAccordion">
                                                <div class="accordion-body">
                                                    <div class="faq-item">
                                                        <h6>How do I view new notifications?</h6>
                                                        <p>Go to the Notifications page to see alerts about enrollments, student submissions, and system updates. Filter by type or status to focus on specific notifications.</p>
                                                    </div>
                                                    <div class="faq-item">
                                                        <h6>Can I clear old notifications?</h6>
                                                        <p>Yes, on the Notifications page, click "Clear All" to remove all notifications or delete individual ones using the "Delete" button.</p>
                                                    </div>
                                                    <div class="faq-item">
                                                        <h6>Why am I not receiving notifications?</h6>
                                                        <p>Check your notification settings in the system preferences. Ensure you’re subscribed to relevant alerts (e.g., course or student notifications). Contact support if issues persist.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- System Settings -->
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="headingSettings">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSettings" aria-expanded="false" aria-controls="collapseSettings">
                                                    System Settings
                                                </button>
                                            </h2>
                                            <div id="collapseSettings" class="accordion-collapse collapse" aria-labelledby="headingSettings" data-bs-parent="#faqAccordion">
                                                <div class="accordion-body">
                                                    <div class="faq-item">
                                                        <h6>How do I enable dark mode?</h6>
                                                        <p>Toggle the dark mode switch in the top navbar or go to your account settings to enable it permanently.</p>
                                                    </div>
                                                    <div class="faq-item">
                                                        <h6>Can I change my notification preferences?</h6>
                                                        <p>Yes, in your account settings, adjust which types of notifications (e.g., course, student) you want to receive via email or in the dashboard.</p>
                                                    </div>
                                                    <div class="faq-item">
                                                        <h6>How do I contact support?</h6>
                                                        <p>Click the "Contact Support" button on this page or in your account settings to submit a support request.</p>
                                                    </div>
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
                            <div class="card faq-card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Actionable Insights</h5>
                                    <ul class="list-unstyled insights-list">
                                        <li><i class="mdi mdi-book-open-page-variant me-2"></i> Explore the Course Management page to create or update courses.</li>
                                        <li><i class="mdi mdi-chart-bar me-2"></i> Use the Custom Report feature to analyze specific metrics.</li>
                                        <li><i class="mdi mdi-help-circle-outline me-2"></i> Contact support for personalized assistance.</li>
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
    <!-- Custom JS for FAQ Search -->
    <script>
        function filterFAQs() {
            const searchInput = document.querySelector('#faqSearch').value.toLowerCase();
            const faqItems = document.querySelectorAll('.faq-item');
            const accordionItems = document.querySelectorAll('.accordion-item');

            let anyVisible = false;

            faqItems.forEach(item => {
                const question = item.querySelector('h6').textContent.toLowerCase();
                const answer = item.querySelector('p').textContent.toLowerCase();
                const matches = question.includes(searchInput) || answer.includes(searchInput);
                item.style.display = matches ? '' : 'none';

                // Check if any item in the accordion is visible
                const accordionBody = item.closest('.accordion-body');
                const visibleItems = accordionBody.querySelectorAll('.faq-item:not([style*="display: none"])').length > 0;
                const accordionItem = accordionBody.closest('.accordion-item');
                accordionItem.style.display = visibleItems ? '' : 'none';

                if (visibleItems) anyVisible = true;
            });

            // Show all accordions if search is empty
            if (!searchInput) {
                accordionItems.forEach(item => {
                    item.style.display = '';
                    item.querySelectorAll('.faq-item').forEach(faq => {
                        faq.style.display = '';
                    });
                });
            }

            // Expand all visible accordions
            accordionItems.forEach(item => {
                if (item.style.display !== 'none') {
                    const collapse = item.querySelector('.accordion-collapse');
                    collapse.classList.add('show');
                }
            });
        }

        // Attach event listener to search input
        document.querySelector('#faqSearch').addEventListener('input', filterFAQs);

        // Ensure first accordion is open on page load
        document.querySelector('#collapseCourse').classList.add('show');
    </script>
</body>
</html>
