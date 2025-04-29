
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
    <title>Instructor | Learnix - Contact Support</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Submit a support request or find contact information for assistance with Learnix." />
    <meta name="author" content="Learnix Team" />
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <!-- Third-party CSS -->
    <link href="assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <!-- App CSS -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style"/>
    <!-- Custom CSS for Contact Support -->
    <style>
        .support-card { 
            border-radius: 10px; 
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); 
            transition: transform 0.3s ease-in-out; 
        }
        .support-card:hover { 
            transform: translateY(-5px); 
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
        .contact-info i { 
            font-size: 20px; 
            margin-right: 10px; 
        }
        .quick-links a { 
            display: block; 
            margin-bottom: 10px; 
        }
        .alert-dismissible { 
            border-radius: 8px; 
        }
        @media (max-width: 768px) {
            .support-card { 
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
                                    <a href="instructor-faq.php" class="btn btn-outline-primary action-btn">View FAQs</a>
                                </div>
                                <h4 class="page-title">Contact Support</h4>
                            </div>
                        </div>
                    </div>
                    <!-- End Page Title -->

                    <!-- Support Form -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card support-card">
                                <div class="card-body">
                                    <h5 class="card-title">Submit a Support Request</h5>
                                    <form id="supportForm">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Name</label>
                                            <input type="text" class="form-control" id="name" placeholder="Your name" aria-label="Name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" placeholder="Your email" aria-label="Email" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="subject" class="form-label">Subject</label>
                                            <input type="text" class="form-control" id="subject" placeholder="Subject of your request" aria-label="Subject" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="category" class="form-label">Category</label>
                                            <select class="form-select" id="category" aria-label="Category" required>
                                                <option value="" disabled selected>Select a category</option>
                                                <option value="technical">Technical Issue</option>
                                                <option value="account">Account Issue</option>
                                                <option value="course">Course Management</option>
                                                <option value="analytics">Analytics</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="message" class="form-label">Message</label>
                                            <textarea class="form-control" id="message" rows="5" placeholder="Describe your issue or request" aria-label="Message" required></textarea>
                                        </div>
                                        <button type="button" class="btn btn-primary action-btn" onclick="submitSupportForm()">Submit Request</button>
                                    </form>
                                    <div id="formFeedback" class="mt-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card support-card">
                                <div class="card-body">
                                    <h5 class="card-title">Contact Information</h5>
                                    <div class="contact-info">
                                        <p><i class="mdi mdi-email"></i> Email: <a href="mailto:support@learnix.com">support@learnix.com</a></p>
                                        <p><i class="mdi mdi-phone"></i> Phone: +1 (800) 555-1234</p>
                                        <p><i class="mdi mdi-clock"></i> Support Hours: Monday - Friday, 9:00 AM - 5:00 PM (EST)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card support-card">
                                <div class="card-body">
                                    <h5 class="card-title">Quick Links</h5>
                                    <div class="quick-links">
                                        <a href="instructor-faq.php" class="text-primary">View Frequently Asked Questions</a>
                                        <a href="instructor-tutorials.php" class="text-primary">Explore Tutorials</a>
                                        <a href="instructor-notifications.php" class="text-primary">Check Notifications</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actionable Insights -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card support-card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Actionable Insights</h5>
                                    <ul class="list-unstyled insights-list">
                                        <li><i class="mdi mdi-help-circle-outline me-2"></i> Check the FAQ page for quick answers to common questions.</li>
                                        <li><i class="mdi mdi-video me-2"></i> Watch tutorials to learn new features.</li>
                                        <li><i class="mdi mdi-email me-2"></i> Submit detailed support requests for personalized help.</li>
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
    <!-- Custom JS for Form Handling -->
    <script>
        function submitSupportForm() {
            const form = document.querySelector('#supportForm');
            const feedback = document.querySelector('#formFeedback');
            const name = document.querySelector('#name').value.trim();
            const email = document.querySelector('#email').value.trim();
            const subject = document.querySelector('#subject').value.trim();
            const category = document.querySelector('#category').value;
            const message = document.querySelector('#message').value.trim();

            // Reset feedback
            feedback.innerHTML = '';

            // Validation
            if (!name || !email || !subject || !category || !message) {
                feedback.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Please fill out all required fields.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                return;
            }

            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                feedback.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Please enter a valid email address.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                return;
            }

            // Simulate submission
            feedback.innerHTML = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Thank you, ${name}! Your support request has been submitted. We'll get back to you at ${email} within 24-48 hours.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;

            // Clear form
            form.reset();
        }
    </script>
</body>
</html>
