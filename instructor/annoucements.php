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
    <script>
        // Disable Dropzone auto-discovery globally
        // This must be before any other scripts load
        window.Dropzone = {
            autoDiscover: false
        };
    </script>
    <title>Instructor | Learnix - Create and Manage Courses</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Intuitive dashboard for instructors to create, manage courses, track student progress, and engage learners effectively." />
    <meta name="author" content="Learnix Team" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <script>
        // This script prevents the flash by applying the theme immediately
        document.addEventListener('DOMContentLoaded', function() {
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
        });
    </script>
    <!-- Dropzone CSS and JS -->
    <link href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" rel="stylesheet" type="text/css" />
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>

    <!-- third party css -->
    <link href="assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <!-- third party css end -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">


    <!-- App css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

    <style>
        /* Custom styling for the file upload area */
        .file-upload-area {
            border: 2px dashed #ddd;
            border-radius: 6px;
            padding: 30px;
            text-align: center;
            background-color: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-upload-area:hover {
            background-color: #e9ecef;
            border-color: #aaa;
        }

        .file-upload-area .dz-message {
            margin: 0;
        }

        .file-upload-area .dz-message i {
            font-size: 48px;
            color: #6c757d;
            margin-bottom: 10px;
        }

        /* Fix for Dropzone preview */
        .dz-preview {
            margin: 10px !important;
        }
    </style>

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
                <!-- Start Content-->
                <div class="container-fluid">

                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <h4 class="page-title">Announcements</h4>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    <!-- Announcement Stats Cards -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="knob-chart" dir="ltr">
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <h3 class="mt-0 text-dark">12</h3>
                                                    <p class="text-muted mb-0">Total Announcements</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="avatar-sm">
                                            <span class="avatar-title bg-soft-primary rounded">
                                                <i class="bi bi-megaphone-fill font-20 "></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="knob-chart" dir="ltr">
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <h3 class="mt-0 text-dark">89%</h3>
                                                    <p class="text-muted mb-0">Read Rate</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="avatar-sm">
                                            <span class="avatar-title bg-soft-success rounded">
                                                <i class="mdi mdi-eye-outline font-20"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="knob-chart" dir="ltr">
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <h3 class="mt-0 text-dark">3</h3>
                                                    <p class="text-muted mb-0">Pending Scheduled</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="avatar-sm">
                                            <span class="avatar-title bg-soft-info rounded">
                                                <i class="mdi mdi-calendar-clock font-20"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="knob-chart" dir="ltr">
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <h3 class="mt-0 text-dark">75%</h3>
                                                    <p class="text-muted mb-0">Email Open Rate</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="avatar-sm">
                                            <span class="avatar-title bg-soft-warning rounded">
                                                <i class="mdi mdi-email-open-outline font-20 text-warning"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Announcement Controls -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                                        <div>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create-announcement-modal">
                                                <i class="mdi mdi-plus-circle me-1"></i> Create Announcement
                                            </button>
                                        </div>
                                        <div class="d-flex align-items-center mt-2 mt-sm-0">
                                            <div class="me-2">
                                                <select class="form-select" id="announcement-status-filter">
                                                    <option value="all">All Status</option>
                                                    <option value="published">Published</option>
                                                    <option value="draft">Draft</option>
                                                    <option value="scheduled">Scheduled</option>
                                                    <option value="archived">Archived</option>
                                                </select>
                                            </div>
                                            <div>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" placeholder="Search announcements..." id="announcement-search">
                                                    <button class="btn btn-soft-secondary" type="button">
                                                        <i class="mdi mdi-magnify"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Single Button trigger modal -->
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#announcementsGuideModal">
                                    Announcements Guide
                                </button>

                                <!-- Announcements Guide Modal -->
                                <div id="announcementsGuideModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-body p-4">
                                                <div class="text-center mb-4">
                                                    <i class="dripicons-information h1 text-primary"></i>
                                                    <h4 class="mt-2">Announcements Management Guide</h4>
                                                </div>

                                                <!-- My Announcements Section -->
                                                <div class="alert alert-success mb-4">
                                                    <div class="d-flex">
                                                        <i class="dripicons-checklist h3 mt-1 me-3"></i>
                                                        <div>
                                                            <h5>My Announcements</h5>
                                                            <p class="mb-2">Your personal announcement workspace with full control.</p>
                                                            <ul class="ps-3">
                                                                <li><strong>Status:</strong> Published, Draft, Scheduled, Archived</li>
                                                                <li><strong>Importance:</strong> Low, Medium, High, Critical</li>
                                                                <li><strong>Analytics:</strong> View tracking for each announcement</li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Course Announcements Section -->
                                                <div class="alert alert-info mb-4">
                                                    <div class="d-flex">
                                                        <i class="dripicons-user-group h3 mt-1 me-3"></i>
                                                        <div>
                                                            <h5>Course Announcements</h5>
                                                            <p class="mb-2">View announcements from colleagues in shared courses.</p>
                                                            <ul class="ps-3">
                                                                <li>See author information for each announcement</li>
                                                                <li>Filter by specific courses</li>
                                                                <li>View details and mark as read</li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- System Announcements Section -->
                                                <div class="alert alert-warning">
                                                    <div class="d-flex">
                                                        <i class="dripicons-bell h3 mt-1 me-3"></i>
                                                        <div>
                                                            <h5>System Announcements</h5>
                                                            <p class="mb-2">Important platform-wide messages from administrators.</p>
                                                            <ul class="ps-3">
                                                                <li>Color-coded by importance level</li>
                                                                <li>Badge shows unread count</li>
                                                                <li>Preview content before opening</li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="text-center mt-3">
                                                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Got It!</button>
                                                </div>
                                            </div>
                                        </div><!-- /.modal-content -->
                                    </div><!-- /.modal-dialog -->
                                </div><!-- /.modal -->
                            </div>
                        </div>
                    </div>


                    <!-- Announcement Tabs -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <ul class="nav nav-tabs nav-bordered">
                                        <li class="nav-item">
                                            <a href="#my-announcements" data-bs-toggle="tab" aria-expanded="true" class="nav-link active">
                                                My Announcements
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#announcement-templates" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                                                Announcement Templates
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#system-announcements" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                                                System Announcements
                                                <span class="badge bg-danger ms-1">0</span>
                                            </a>
                                        </li>
                                    </ul>
                                    <div class="tab-content">
                                        <!-- My Announcements Tab -->
                                        <div class="tab-pane show active" id="my-announcements">
                                            <div class="table-responsive mt-3">
                                                <table class="table table-hover table-centered mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Title</th>
                                                            <th>Status</th>
                                                            <th>Importance</th>
                                                            <th>Target Audience</th>
                                                            <th>Date</th>
                                                            <th>Metrics</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="my-announcements-list">
                                                        <!-- Empty state message -->
                                                        <tr id="empty-my-announcements">
                                                            <td colspan="7" class="text-center py-4">
                                                                <div class="text-center">
                                                                    <i class="mdi mdi-bell-outline text-muted" style="font-size: 48px;"></i>
                                                                    <h5 class="mt-2">You haven't created any announcements yet</h5>
                                                                    <p class="text-muted">Keep your students informed by creating your first announcement</p>
                                                                    <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#create-announcement-modal">
                                                                        Create Announcement
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <!-- Example announcement (Will be populated by JavaScript) -->
                                                        <!-- 
                                    <tr style="display: none;">
                                        <td>
                                            <a href="#" class="text-body fw-bold">Midterm Exam Schedule</a>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">Published</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger">Critical</span>
                                        </td>
                                        <td>Web Development 101</td>
                                        <td>Apr 25, 2025</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress me-1" style="height: 8px; width: 60px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: 70%"></div>
                                                </div>
                                                <span>70%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group dropdown">
                                                <a href="javascript: void(0);" class="dropdown-toggle arrow-none btn btn-light btn-sm" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="mdi mdi-dots-horizontal"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" href="#"><i class="mdi mdi-pencil me-1"></i>Edit</a>
                                                    <a class="dropdown-item" href="#"><i class="mdi mdi-content-copy me-1"></i>Duplicate</a>
                                                    <a class="dropdown-item" href="#"><i class="mdi mdi-archive me-1"></i>Archive</a>
                                                    <a class="dropdown-item" href="#"><i class="mdi mdi-delete me-1"></i>Delete</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    -->

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- Announcement Templates Tab -->
                                        <div class="tab-pane" id="announcement-templates">
                                            <div class="row mt-3">
                                                <div class="col-12">
                                                    <button class="btn btn-primary mb-3" id="create-template-btn">
                                                        <i class="mdi mdi-plus-circle me-1"></i> Create Template
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-hover table-centered mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Template Name</th>
                                                            <th>Description</th>
                                                            <th>Created Date</th>
                                                            <th>Last Used</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="templates-list">
                                                        <!-- Empty state message -->
                                                        <tr id="empty-templates">
                                                            <td colspan="5" class="text-center py-4">
                                                                <div>
                                                                    <i class="mdi mdi-file-document-outline text-muted" style="font-size: 48px;"></i>
                                                                    <h5 class="mt-2">No templates found</h5>
                                                                    <p class="text-muted">Create templates to save time when creating similar announcements</p>
                                                                    <button type="button" class="btn btn-primary mt-2" id="empty-create-template-btn">
                                                                        Create Template
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <!-- Templates will be populated here -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- System Announcements Tab -->
                                        <div class="tab-pane" id="system-announcements">
                                            <div class="mt-3">
                                                <!-- System announcement cards instead of table for better visuals -->
                                                <div class="row" id="system-announcements-container">
                                                    <!-- Example system announcement card -->
                                                    <div class="col-md-6">
                                                        <div class="card border-start border-danger border-3">
                                                            <div class="card-body">
                                                                <div class="d-flex align-items-start mb-3">
                                                                    <div class="badge bg-danger me-2">Critical</div>
                                                                    <h5 class="card-title mb-0">System Maintenance Upcoming</h5>
                                                                    <div class="dropdown ms-auto">
                                                                        <a href="#" class="dropdown-toggle arrow-none text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                                            <i class="mdi mdi-dots-vertical font-18"></i>
                                                                        </a>
                                                                        <div class="dropdown-menu dropdown-menu-end">
                                                                            <a class="dropdown-item" href="#"><i class="mdi mdi-check-circle me-1"></i>Mark as read</a>
                                                                            <a class="dropdown-item" href="#"><i class="mdi mdi-bell-off-outline me-1"></i>Dismiss</a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <p class="card-text">The LMS will be undergoing maintenance on April 30, 2025, from 2:00 AM to 5:00 AM EDT. During this time, the system may be unavailable.</p>
                                                                <div class="d-flex align-items-center text-muted">
                                                                    <small><i class="mdi mdi-account me-1"></i> From: System Administrator</small>
                                                                    <small class="ms-3"><i class="mdi mdi-calendar me-1"></i> Apr 25, 2025</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="card border-start border-info border-3">
                                                            <div class="card-body">
                                                                <div class="d-flex align-items-start mb-3">
                                                                    <div class="badge bg-info me-2">Info</div>
                                                                    <h5 class="card-title mb-0">New Features Added</h5>
                                                                    <div class="dropdown ms-auto">
                                                                        <a href="#" class="dropdown-toggle arrow-none text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                                            <i class="mdi mdi-dots-vertical font-18"></i>
                                                                        </a>
                                                                        <div class="dropdown-menu dropdown-menu-end">
                                                                            <a class="dropdown-item" href="#"><i class="mdi mdi-check-circle me-1"></i>Mark as read</a>
                                                                            <a class="dropdown-item" href="#"><i class="mdi mdi-bell-off-outline me-1"></i>Dismiss</a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <p class="card-text">We've added new features to the announcement system. Now you can schedule announcements and attach files to them.</p>
                                                                <div class="d-flex align-items-center text-muted">
                                                                    <small><i class="mdi mdi-account me-1"></i> From: System Administrator</small>
                                                                    <small class="ms-3"><i class="mdi mdi-calendar me-1"></i> Apr 24, 2025</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Empty state (hidden when there are announcements) -->
                                                    <div class="col-12 d-none" id="empty-system-announcements">
                                                        <div class="text-center py-5">
                                                            <i class="mdi mdi-bell-off-outline text-muted" style="font-size: 48px;"></i>
                                                            <h5 class="mt-2">No system announcements</h5>
                                                            <p class="text-muted">There are no system-wide announcements at this time</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- container -->

                <!-- Create Announcement Modal -->
                <div class="modal fade" id="create-announcement-modal" tabindex="-1" role="dialog" aria-labelledby="create-announcement-modal-label" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="create-announcement-modal-label">Create Announcement</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="announcement-form">
                                    <div class="mb-3">
                                        <label for="announcement-title" class="form-label">Title</label>
                                        <input type="text" class="form-control" id="announcement-title" required placeholder="Enter announcement title">
                                    </div>

                                    <div class="mb-3">
                                        <label for="announcement-content" class="form-label">Content</label>
                                        <textarea class="form-control" id="announcement-content" rows="5" placeholder="Enter announcement content"></textarea>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="announcement-importance" class="form-label">Importance Level</label>
                                            <select class="form-select" id="announcement-importance">
                                                <option value="Low">Low</option>
                                                <option value="Medium" selected>Medium</option>
                                                <option value="High">High</option>
                                                <option value="Critical">Critical</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="announcement-status" class="form-label">Status</label>
                                            <select class="form-select" id="announcement-status">
                                                <option value="Draft">Save as Draft</option>
                                                <option value="Published" selected>Publish Immediately</option>
                                                <option value="Scheduled">Schedule for Later</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Scheduled options (initially hidden) -->
                                    <div class="mb-3" id="scheduled-options" style="display: none;">
                                        <label for="scheduled-date" class="form-label">Schedule Date and Time</label>
                                        <input type="datetime-local" class="form-control" id="scheduled-date">
                                        <small class="text-muted">Select when this announcement should be published</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="announcement-expires" class="form-label">Expiration (Optional)</label>
                                        <input type="date" class="form-control" id="announcement-expires">
                                        <small class="text-muted">Leave blank if announcement should not expire</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Target Audience</label>
                                        <div class="mt-2">
                                            <div class="form-check mb-2">
                                                <input type="radio" id="target-course-specific" name="target-type" class="form-check-input" checked>
                                                <label class="form-check-label" for="target-course-specific">Course-specific</label>
                                            </div>
                                            <div id="course-specific-options">
                                                <select class="form-select mb-2" id="target-course">
                                                    <option value="">Select a course</option>
                                                    <!-- Course options will be populated dynamically -->
                                                </select>
                                            </div>

                                            <div class="form-check mb-2">
                                                <input type="radio" id="target-all-courses" name="target-type" class="form-check-input">
                                                <label class="form-check-label" for="target-all-courses">All My Courses</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Delivery Channels</label>
                                        <div class="mt-2">
                                            <div class="form-check form-check-inline">
                                                <input type="checkbox" id="channel-app" class="form-check-input" checked disabled>
                                                <label class="form-check-label" for="channel-app">In-App</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input type="checkbox" id="channel-email" class="form-check-input">
                                                <label class="form-check-label" for="channel-email">Email</label>
                                            </div>
                                        </div>
                                        <small class="text-muted">In-App notifications are always enabled. Email notifications are sent based on importance and user preferences.</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="announcement-attachments" class="form-label">Attachments (Optional)</label>
                                        <div class="attachment-upload-container">
                                            <div class="d-flex align-items-center mb-2">
                                                <button type="button" class="btn btn-primary me-2" id="attachment-upload-btn">
                                                    <i class="mdi mdi-file-upload-outline me-1"></i> Add Files
                                                </button>
                                                <input type="file" id="attachment-file-input" multiple style="display: none" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                                <span class="text-muted" id="attachment-count">0/5 files uploaded</span>
                                            </div>
                                            <p class="text-muted small">Max file size: 10MB. Allowed formats: PDF, DOC, DOCX, JPG, PNG</p>
                                        </div>
                                        <div id="attachment-preview" class="mt-3">
                                            <div class="row" id="attachment-files-container">
                                                <!-- Attachment previews will be shown here -->
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="save-announcement">Save Announcement</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- container -->

                <!-- Delete Confirmation Modal -->
                <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Confirm Deletion</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to delete this announcement? This action cannot be undone.</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Archive Confirmation Modal -->
                <div class="modal fade" id="archiveConfirmationModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Confirm Archive</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to archive this announcement? Archived announcements can be restored later.</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-warning" id="confirmArchiveBtn">Archive</button>
                            </div>
                        </div>
                    </div>
                </div>

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

    <script src="https://cdn.tiny.cloud/1/4fnlr08nx5aczp8z0vkgtm2sblkj0y9qywi9iox6hs7ghxgv/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>




    <!-- bundle -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.min.js"></script>

    <!-- third party js -->
    <script src="assets/js/vendor/apexcharts.min.js"></script>
    <script src="assets/js/vendor/jquery-jvectormap-1.2.2.min.js"></script>
    <script src="assets/js/vendor/jquery-jvectormap-world-mill-en.js"></script>
    <!-- third party js ends -->

    <script>
        // Make sure the empty-create-template-btn works
        $(document).on('click', '#empty-create-template-btn', function() {
            currentTemplateId = null;
            $('#template-modal-label').text('Create Template');
            $('#template-title').val('');

            if (typeof tinymce !== 'undefined' && tinymce.get('template-content')) {
                tinymce.get('template-content').setContent('');
            } else {
                $('#template-content').val('');
            }

            $('#template-modal').modal('show');
        });
        
       // Unified dropdown handler for all dropdowns
$(document).on('click', '.dropdown-toggle, .action-dropdown .dropdown-toggle', function(e) {
    e.preventDefault();
    e.stopPropagation(); // Prevent event bubbling to document click handler
    
    const $dropdown = $(this).closest('.dropdown, .action-dropdown');
    const $dropdownMenu = $dropdown.find('.dropdown-menu');
    
    // Close all other dropdowns
    $('.dropdown-menu.show').not($dropdownMenu).removeClass('show');
    
    // Toggle the current dropdown
    $dropdownMenu.toggleClass('show');
});

        $(document).ready(function() {
            // Global variable for announcement actions
            let currentAnnouncementId = null;

            // Format file size function
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            // Initialize TinyMCE for rich text editing
            if (typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: '#announcement-content',
                    height: 300,
                    menubar: false,
                    plugins: ['lists', 'link', 'image', 'code', 'table'],
                    toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter ' +
                        'alignright alignjustify | bullist numlist | link image | table | removeformat',
                });
            }

            // Handle scheduled options visibility
            $('#announcement-status').on('change', function() {
                if ($(this).val() === 'Scheduled') {
                    $('#scheduled-options').slideDown();
                    const tomorrow = new Date();
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    tomorrow.setHours(9, 0, 0, 0);
                    $('#scheduled-date').val(tomorrow.toISOString().slice(0, 16));
                } else {
                    $('#scheduled-options').slideUp();
                }
            });

            // Handle target audience options
            $('input[name="target-type"]').on('change', function() {
                if (this.id === 'target-course-specific') {
                    $('#course-specific-options').slideDown();
                } else {
                    $('#course-specific-options').slideUp();
                }
            });

            // Function to clear hardcoded content
            function clearHardcodedContent() {
                // Clear hardcoded stats in a more precise way
                $('.col-xl-3 .card .mt-0.text-dark').each(function(index) {
                    if (index % 2 === 0) { // for 1st and 3rd items
                        $(this).text('0');
                    } else { // for 2nd and 4th items
                        $(this).text('0%');
                    }
                });

                // Clear system announcement badge
                $('.nav-tabs .nav-item:nth-child(3) .badge').text('0');

                // Clear hardcoded system announcements
                $('#system-announcements-container .col-md-6').not('#empty-system-announcements').remove();

                // Show empty system announcement state
                $('#empty-system-announcements').removeClass('d-none');
            }

            // Fetch courses for the dropdown
            function fetchCourses() {
                $.ajax({
                    url: '../ajax/instructors/fetch_instructor_courses.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(courses) {
                        console.log("Courses loaded:", courses);
                        const courseSelect = $('#target-course');
                        courseSelect.empty().append('<option value="">Select a course</option>');

                        const courseFilter = $('#course-filter');
                        courseFilter.empty().append('<option value="all">All Courses</option>');

                        if (courses && courses.length > 0) {
                            courses.forEach(course => {
                                courseSelect.append(`<option value="${course.course_id}">${course.title}</option>`);
                                courseFilter.append(`<option value="${course.course_id}">${course.title}</option>`);
                            });
                        } else {
                            console.log('No courses found or empty response');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading courses:', xhr);
                        showAlert('error', 'Failed to load courses. Please try again.');
                    }
                });
            }

            // Load announcement stats
            function loadAnnouncementStats() {
                $.ajax({
                    url: '../ajax/instructors/get_announcement_stats.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(stats) {
                        console.log("Stats loaded:", stats);
                        if (stats.error) {
                            console.error('Error loading stats:', stats.error);
                            return;
                        }

                        // Update stats cards with real data
                        $('.col-xl-3:nth-child(1) .card .mt-0.text-dark').text(stats.total_announcements || 0);
                        $('.col-xl-3:nth-child(2) .card .mt-0.text-dark').text((stats.read_rate || 0) + '%');
                        $('.col-xl-3:nth-child(3) .card .mt-0.text-dark').text(stats.scheduled_count || 0);
                        $('.col-xl-3:nth-child(4) .card .mt-0.text-dark').text((stats.email_open_rate || 0) + '%');
                    },
                    error: function(xhr) {
                        console.error('Failed to load announcement stats:', xhr);
                        // Handle potential malformed JSON in the response
                        if (xhr.status === 200 && xhr.responseText) {
                            try {
                                // Try to extract just the JSON part of the response
                                const jsonStartPos = xhr.responseText.indexOf('{');
                                if (jsonStartPos >= 0) {
                                    const jsonStr = xhr.responseText.substring(jsonStartPos);
                                    const stats = JSON.parse(jsonStr);

                                    // Update stats cards with extracted data
                                    $('.col-xl-3:nth-child(1) .card .mt-0.text-dark').text(stats.total_announcements || 0);
                                    $('.col-xl-3:nth-child(2) .card .mt-0.text-dark').text((stats.read_rate || 0) + '%');
                                    $('.col-xl-3:nth-child(3) .card .mt-0.text-dark').text(stats.scheduled_count || 0);
                                    $('.col-xl-3:nth-child(4) .card .mt-0.text-dark').text((stats.email_open_rate || 0) + '%');
                                }
                            } catch (e) {
                                console.error('Error parsing stats data:', e);
                            }
                        }
                    }
                });
            }
            // File Upload Management
            const MAX_FILES = 5;
            const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
            const ALLOWED_TYPES = ['.pdf', '.doc', '.docx', '.jpg', '.jpeg', '.png'];
            const uploadedFiles = [];

            $('#attachment-upload-btn').on('click', function() {
                $('#attachment-file-input').click();
            });

            $('#attachment-file-input').on('change', function(e) {
                Array.from(e.target.files || []).forEach(file => {
                    if (uploadedFiles.length >= MAX_FILES) {
                        showAlert('error', `Maximum ${MAX_FILES} files allowed`);
                        return;
                    }
                    if (file.size > MAX_FILE_SIZE) {
                        showAlert('error', `File ${file.name} exceeds maximum size of 10MB`);
                        return;
                    }
                    const fileExt = '.' + file.name.split('.').pop().toLowerCase();
                    if (!ALLOWED_TYPES.includes(fileExt)) {
                        showAlert('error', `File type ${fileExt} not allowed`);
                        return;
                    }

                    const fileId = 'file-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
                    uploadedFiles.push({
                        id: fileId,
                        file: file,
                        name: file.name,
                        size: file.size
                    });
                    addFilePreview(fileId, file.name, file.size);
                    updateFileCounter();
                });
                this.value = '';
            });

            function addFilePreview(fileId, fileName, fileSize) {
                const filePreview = `
            <div class="col-md-4 mb-2" id="attachment-${fileId}">
                <div class="card border">
                    <div class="card-body p-2">
                        <div class="d-flex align-items-start">
                            <div class="me-2">
                                <i class="mdi mdi-file-document-outline font-24 text-primary"></i>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <h5 class="font-14 mt-0 text-truncate mb-1">${fileName}</h5>
                                <p class="text-muted font-13 mb-0 text-truncate">${formatFileSize(fileSize)}</p>
                            </div>
                            <div class="dropdown">
                                <a href="#" class="dropdown-toggle arrow-none text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="mdi mdi-dots-vertical font-18"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="#" onclick="removeAttachment('${fileId}')">
                                        <i class="mdi mdi-delete-outline me-1"></i>Remove
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
                $("#attachment-files-container").append(filePreview);
            }

            function updateFileCounter() {
                $('#attachment-count').text(`${uploadedFiles.length}/${MAX_FILES} files uploaded`);
                $('#attachment-upload-btn').prop('disabled', uploadedFiles.length >= MAX_FILES);
            }

            window.removeAttachment = function(fileId) {
                $(`#attachment-${fileId}`).remove();
                const index = uploadedFiles.findIndex(f => f.id === fileId);
                if (index !== -1) uploadedFiles.splice(index, 1);
                updateFileCounter();
            };

            // Save announcement
            $('#save-announcement').on('click', function() {
                const title = $('#announcement-title').val();
                let content = typeof tinymce !== 'undefined' && tinymce.get('announcement-content') ?
                    tinymce.get('announcement-content').getContent() : $('#announcement-content').val();
                const importance = $('#announcement-importance').val();
                const status = $('#announcement-status').val();
                const scheduledDate = $('#scheduled-date').val();
                const expiration = $('#announcement-expires').val();
                const targetType = $('input[name="target-type"]:checked').attr('id');
                const courseId = $('#target-course').val();
                const emailNotification = $('#channel-email').is(':checked');
                const announcementId = $('#edit-announcement-id').val() || null;

                if (!title.trim()) return showAlert('error', 'Please enter an announcement title');
                if (!content.trim()) return showAlert('error', 'Please enter announcement content');
                if (status === 'Scheduled' && !scheduledDate) return showAlert('error', 'Please select a schedule date and time');
                if (targetType === 'target-course-specific' && !courseId) return showAlert('error', 'Please select a target course');

                const formData = new FormData();
                formData.append('title', title);
                formData.append('content', content);
                formData.append('importance', importance);
                formData.append('status', status);
                formData.append('scheduled_date', scheduledDate || '');
                formData.append('expiration_date', expiration || '');
                formData.append('target_type', targetType === 'target-course-specific' ? 'course' : 'all_courses');
                formData.append('course_id', targetType === 'target-course-specific' ? courseId : '');
                formData.append('email_notification', emailNotification ? '1' : '0');

                // If we have an announcement ID, this is an edit
                if (announcementId) {
                    formData.append('announcement_id', announcementId);
                }

                // Handle file uploads
                uploadedFiles.forEach(fileObj => {
                    if (fileObj.existingFile) {
                        // This is an existing file, just pass the ID
                        formData.append('existing_files[]', fileObj.attachment_id);
                    } else {
                        // This is a new file to upload
                        formData.append('files[]', fileObj.file);
                    }
                });

                showOverlay(announcementId ? 'Updating announcement...' : 'Saving announcement...');
                $.ajax({
                    url: announcementId ?
                        '../ajax/instructors/update_announcement.php' : '../ajax/instructors/create_announcement.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        removeOverlay();
                        try {
                            const result = typeof response === 'string' ? JSON.parse(response) : response;
                            if (result.success) {
                                showAlert('success', announcementId ?
                                    'Announcement successfully updated!' :
                                    'Announcement successfully created!');
                                $('#create-announcement-modal').modal('hide');
                                resetAnnouncementForm();
                                loadAnnouncements();
                                loadAnnouncementStats();
                            } else {
                                showAlert('error', result.message || 'Error ' +
                                    (announcementId ? 'updating' : 'creating') + ' announcement');
                            }
                        } catch (e) {
                            showAlert('error', 'Invalid response from server');
                            console.error('Error parsing response:', e, response);
                        }
                    },
                    error: function(xhr) {
                        removeOverlay();
                        showAlert('error', 'Server error. Please try again later.');
                        console.error('AJAX error:', xhr);
                    }
                });
            });

            function resetAnnouncementForm() {
                $('#announcement-title').val('');
                if (typeof tinymce !== 'undefined' && tinymce.get('announcement-content')) {
                    tinymce.get('announcement-content').setContent('');
                } else {
                    $('#announcement-content').val('');
                }
                $('#announcement-importance').val('Medium');
                $('#announcement-status').val('Published');
                $('#scheduled-options').hide();
                $('#scheduled-date').val('');
                $('#announcement-expires').val('');
                $('#target-course-specific').prop('checked', true);
                $('#course-specific-options').show();
                $('#target-course').val('');
                $('#channel-email').prop('checked', false);
                uploadedFiles.length = 0;
                $('#attachment-files-container').empty();
                updateFileCounter();
            }

           // Close dropdowns when clicking outside
$(document).on('click', function(e) {
    if (!$(e.target).closest('.dropdown, .action-dropdown').length) {
        $('.dropdown-menu').removeClass('show');
    }
});

            // Action handlers
            $(document).on('click', '.edit-announcement', function(e) {
                e.preventDefault();
                editAnnouncement($(this).data('id'));
            });

            $(document).on('click', '.duplicate-announcement', function(e) {
                e.preventDefault();
                duplicateAnnouncement($(this).data('id'));
            });

            $(document).on('click', '.archive-announcement', function(e) {
                e.preventDefault();
                archiveAnnouncement($(this).data('id'));
            });

            $(document).on('click', '.delete-announcement', function(e) {
                e.preventDefault();
                deleteAnnouncement($(this).data('id'));
            });

            // Delete Announcement Flow
            window.deleteAnnouncement = function(announcementId) {
                currentAnnouncementId = announcementId;
                $('#deleteConfirmationModal').modal('show');
            };

            $('#confirmDeleteBtn').on('click', function() {
                $('#deleteConfirmationModal').modal('hide');
                if (currentAnnouncementId) {
                    showOverlay('Deleting announcement...');
                    $.ajax({
                        url: '../ajax/instructors/delete_announcement.php',
                        type: 'POST',
                        data: {
                            announcement_id: currentAnnouncementId
                        },
                        success: function(response) {
                            removeOverlay();
                            try {
                                const result = typeof response === 'string' ? JSON.parse(response) : response;
                                if (result.success) {
                                    showAlert('success', 'Announcement deleted successfully');
                                    loadAnnouncements();
                                    loadAnnouncementStats();
                                } else {
                                    showAlert('error', result.message || 'Error deleting announcement');
                                }
                            } catch (e) {
                                showAlert('error', 'Invalid response from server');
                                console.error('Error parsing response:', e);
                            }
                        },
                        error: function(xhr) {
                            removeOverlay();
                            showAlert('error', 'Server error. Please try again later.');
                            console.error('Delete AJAX error:', xhr);
                        }
                    });
                }
                currentAnnouncementId = null;
            });

            // Archive Announcement Flow
            window.archiveAnnouncement = function(announcementId) {
                currentAnnouncementId = announcementId;
                $('#archiveConfirmationModal').modal('show');
            };

            $('#confirmArchiveBtn').on('click', function() {
                $('#archiveConfirmationModal').modal('hide');
                if (currentAnnouncementId) {
                    showOverlay('Archiving announcement...');
                    $.ajax({
                        url: '../ajax/instructors/archive_announcement.php',
                        type: 'POST',
                        data: {
                            announcement_id: currentAnnouncementId
                        },
                        success: function(response) {
                            removeOverlay();
                            try {
                                const result = typeof response === 'string' ? JSON.parse(response) : response;
                                if (result.success) {
                                    showAlert('success', 'Announcement archived successfully');
                                    loadAnnouncements();
                                    loadAnnouncementStats();
                                } else {
                                    showAlert('error', result.message || 'Error archiving announcement');
                                }
                            } catch (e) {
                                showAlert('error', 'Invalid response from server');
                                console.error('Error parsing response:', e);
                            }
                        },
                        error: function(xhr) {
                            removeOverlay();
                            showAlert('error', 'Server error. Please try again later.');
                            console.error('Archive AJAX error:', xhr);
                        }
                    });
                }
                currentAnnouncementId = null;
            });

            // Clear the current ID when modal is dismissed
            $('#deleteConfirmationModal, #archiveConfirmationModal').on('hidden.bs.modal', function() {
                currentAnnouncementId = null;
            });

            window.editAnnouncement = function(announcementId) {
                showOverlay('Loading announcement data...');
                $.ajax({
                    url: '../ajax/instructors/get_announcement.php',
                    type: 'GET',
                    data: {
                        announcement_id: announcementId
                    },
                    dataType: 'json',
                    success: function(data) {
                        removeOverlay();
                        if (data.success) {
                            // Populate the form with the announcement data
                            $('#announcement-title').val(data.announcement.title);

                            // Set TinyMCE content
                            if (typeof tinymce !== 'undefined' && tinymce.get('announcement-content')) {
                                tinymce.get('announcement-content').setContent(data.announcement.content);
                            } else {
                                $('#announcement-content').val(data.announcement.content);
                            }

                            // Set other form fields
                            $('#announcement-importance').val(data.announcement.importance);
                            $('#announcement-status').val(data.announcement.status);

                            if (data.announcement.status === 'Scheduled') {
                                $('#scheduled-options').show();
                                const scheduledDate = new Date(data.announcement.scheduled_at);
                                $('#scheduled-date').val(scheduledDate.toISOString().slice(0, 16));
                            }

                            if (data.announcement.expires_at) {
                                const expiryDate = new Date(data.announcement.expires_at);
                                $('#announcement-expires').val(expiryDate.toISOString().slice(0, 10));
                            }

                            // Set targeting options
                            if (data.announcement.course_id) {
                                $('#target-course-specific').prop('checked', true);
                                $('#course-specific-options').show();
                                $('#target-course').val(data.announcement.course_id);
                            } else {
                                $('#target-all-courses').prop('checked', true);
                                $('#course-specific-options').hide();
                            }

                            // Show attachments if any
                            if (data.attachments && data.attachments.length > 0) {
                                data.attachments.forEach(function(attachment) {
                                    const fileId = 'file-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
                                    uploadedFiles.push({
                                        id: fileId,
                                        existingFile: true,
                                        attachment_id: attachment.attachment_id,
                                        name: attachment.file_name,
                                        size: attachment.file_size
                                    });
                                    addFilePreview(fileId, attachment.file_name, attachment.file_size);
                                });
                                updateFileCounter();
                            }

                            // Add a hidden input for the announcement ID
                            if ($('#edit-announcement-id').length === 0) {
                                $('<input>').attr({
                                    type: 'hidden',
                                    id: 'edit-announcement-id',
                                    value: announcementId
                                }).appendTo('#announcement-form');
                            } else {
                                $('#edit-announcement-id').val(announcementId);
                            }

                            // Update the modal title and button text
                            $('#create-announcement-modal-label').text('Edit Announcement');
                            $('#save-announcement').text('Update Announcement');

                            // Show the modal
                            $('#create-announcement-modal').modal('show');
                        } else {
                            showAlert('error', data.message || 'Failed to load announcement data');
                        }
                    },
                    error: function(xhr) {
                        removeOverlay();
                        showAlert('error', 'Server error. Please try again later.');
                        console.error('Error loading announcement data:', xhr);
                    }
                });
            };

            window.duplicateAnnouncement = function(announcementId) {
                showOverlay('Duplicating announcement...');
                $.ajax({
                    url: '../ajax/instructors/get_announcement.php',
                    type: 'GET',
                    data: {
                        announcement_id: announcementId
                    },
                    dataType: 'json',
                    success: function(data) {
                        removeOverlay();
                        if (data.success) {
                            // Populate the form with the announcement data but change the title
                            $('#announcement-title').val(data.announcement.title + ' (Copy)');

                            // Set TinyMCE content
                            if (typeof tinymce !== 'undefined' && tinymce.get('announcement-content')) {
                                tinymce.get('announcement-content').setContent(data.announcement.content);
                            } else {
                                $('#announcement-content').val(data.announcement.content);
                            }

                            // Set other form fields but default to Draft status
                            $('#announcement-importance').val(data.announcement.importance);
                            $('#announcement-status').val('Draft');
                            $('#scheduled-options').hide();

                            if (data.announcement.expires_at) {
                                const expiryDate = new Date(data.announcement.expires_at);
                                $('#announcement-expires').val(expiryDate.toISOString().slice(0, 10));
                            }

                            // Set targeting options
                            if (data.announcement.course_id) {
                                $('#target-course-specific').prop('checked', true);
                                $('#course-specific-options').show();
                                $('#target-course').val(data.announcement.course_id);
                            } else {
                                $('#target-all-courses').prop('checked', true);
                                $('#course-specific-options').hide();
                            }

                            // Remove any existing hidden input for edit mode
                            $('#edit-announcement-id').remove();

                            // Update the modal title and button text
                            $('#create-announcement-modal-label').text('Create Announcement');
                            $('#save-announcement').text('Save Announcement');

                            // Show the modal
                            $('#create-announcement-modal').modal('show');
                        } else {
                            showAlert('error', data.message || 'Failed to duplicate announcement');
                        }
                    },
                    error: function(xhr) {
                        removeOverlay();
                        showAlert('error', 'Server error. Please try again later.');
                        console.error('Error duplicating announcement:', xhr);
                    }
                });
            };

            // Load announcements
            function loadAnnouncements() {
                showOverlay('Loading announcements...');
                $.ajax({
                    url: '../ajax/instructors/load_announcements.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        console.log("Announcements loaded:", data);
                        removeOverlay();

                        // My Announcements
                        $('#my-announcements-list').empty();
                        if (data.myAnnouncements && data.myAnnouncements.length > 0) {
                            data.myAnnouncements.forEach(addAnnouncementToList);
                        } else {
                            $('#my-announcements-list').append(createEmptyState('my-announcements', 'bell-outline',
                                'You haven\'t created any announcements yet',
                                'Keep your students informed by creating your first announcement', true));
                        }

                        // Course Announcements
                        $('#course-announcements-list').empty();
                        if (data.courseAnnouncements && data.courseAnnouncements.length > 0) {
                            data.courseAnnouncements.forEach(addCourseAnnouncementToList);
                        } else {
                            $('#course-announcements-list').append(createEmptyState('course-announcements', 'information-outline',
                                'No course announcements found',
                                'There are no announcements for your courses yet'));
                        }

                        // System Announcements
                        // Remove existing system announcements (except empty state)
                        $('#system-announcements-container').find('.col-md-6:not(#empty-system-announcements)').remove();

                        if (data.systemAnnouncements && data.systemAnnouncements.length > 0) {
                            // Update badge count
                            $('.nav-tabs .nav-item:nth-child(3) .badge').text(data.systemAnnouncements.length);
                            $('#empty-system-announcements').addClass('d-none');
                            data.systemAnnouncements.forEach(addSystemAnnouncementCard);
                        } else {
                            $('.nav-tabs .nav-item:nth-child(3) .badge').text('0');
                            $('#empty-system-announcements').removeClass('d-none');
                        }
                    },
                    error: function(xhr) {
                        removeOverlay();
                        showAlert('error', 'Failed to load announcements. Please try again.');
                        console.error('Error loading announcements:', xhr);
                    }
                });
            }

            function createEmptyState(id, icon, title, message, showButton = false) {
                return `
            <tr id="empty-${id}">
                <td colspan="${id === 'my-announcements' ? 7 : 6}" class="text-center py-4">
                    <div class="text-center">
                        <i class="mdi mdi-${icon} text-muted" style="font-size: 48px;"></i>
                        <h5 class="mt-2">${title}</h5>
                        <p class="text-muted">${message}</p>
                        ${showButton ? '<button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#create-announcement-modal">Create Announcement</button>' : ''}
                    </div>
                </td>
            </tr>`;
            }

            function addAnnouncementToList(announcement) {
                const statusBadge = getStatusBadge(announcement.status);
                const importanceBadge = getImportanceBadge(announcement.importance);
                const dateStr = formatDate(announcement.created_at);
                const readPercentage = announcement.read_percentage || 0;

                const row = $(`
            <tr>
                <td><a href="#" class="text-body fw-bold">${announcement.title}</a></td>
                <td>${statusBadge}</td>
                <td>${importanceBadge}</td>
                <td>${announcement.course_title || 'All My Courses'}</td>
                <td>${dateStr}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="progress me-1" style="height: 8px; width: 60px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: ${readPercentage}%"></div>
                        </div>
                        <span>${readPercentage}%</span>
                    </div>
                </td>
                <td>
                    <div class="btn-group dropdown">
                        <a href="#" class="dropdown-toggle arrow-none btn btn-light btn-sm">
        <i class="mdi mdi-dots-horizontal"></i>
    </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item edit-announcement" href="#" data-id="${announcement.announcement_id}">
                                <i class="mdi mdi-pencil me-1"></i>Edit
                            </a>
                            <a class="dropdown-item duplicate-announcement" href="#" data-id="${announcement.announcement_id}">
                                <i class="mdi mdi-content-copy me-1"></i>Duplicate
                            </a>
                            <a class="dropdown-item archive-announcement" href="#" data-id="${announcement.announcement_id}">
                                <i class="mdi mdi-archive me-1"></i>Archive
                            </a>
                            <a class="dropdown-item delete-announcement" href="#" data-id="${announcement.announcement_id}">
                                <i class="mdi mdi-delete me-1"></i>Delete
                            </a>
                        </div>
                    </div>
                </td>
            </tr>
        `);
                $('#my-announcements-list').append(row);
            }

            function addCourseAnnouncementToList(announcement) {
                const importanceBadge = getImportanceBadge(announcement.importance);
                const dateStr = formatDate(announcement.created_at);

                const row = $(`
            <tr>
                <td><a href="#" class="text-body fw-bold">${announcement.title}</a></td>
                <td>${announcement.first_name} ${announcement.last_name}</td>
                <td>${announcement.course_title}</td>
                <td>${importanceBadge}</td>
                <td>${dateStr}</td>
                <td>
                    <div class="btn-group dropdown">
                        <a href="#" class="dropdown-toggle arrow-none btn btn-light btn-sm" data-bs-toggle="dropdown">
                            <i class="mdi mdi-dots-horizontal"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="#" onclick="viewAnnouncementDetails(${announcement.announcement_id})">
                                <i class="mdi mdi-eye me-1"></i>View Details
                            </a>
                            <a class="dropdown-item" href="#" onclick="markAsRead(${announcement.announcement_id})">
                                <i class="mdi mdi-check me-1"></i>Mark as Read
                            </a>
                        </div>
                    </div>
                </td>
            </tr>
        `);
                $('#course-announcements-list').append(row);
            }

            function addSystemAnnouncementCard(announcement) {
                const borderClass = getBorderClass(announcement.importance);
                const badgeClass = getBadgeClass(announcement.importance);
                const dateStr = formatDate(announcement.created_at);

                const card = $(`
            <div class="col-md-6">
                <div class="card border-start ${borderClass} border-3">
                    <div class="card-body">
                        <div class="d-flex align-items-start mb-3">
                            <div class="badge ${badgeClass} me-2">${announcement.importance}</div>
                            <h5 class="card-title mb-0">${announcement.title}</h5>
                            <div class="dropdown ms-auto">
                                <a href="#" class="dropdown-toggle arrow-none text-muted" data-bs-toggle="dropdown">
                                    <i class="mdi mdi-dots-vertical font-18"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="#" onclick="markSystemAnnouncementAsRead(${announcement.announcement_id})">
                                        <i class="mdi mdi-check-circle me-1"></i>Mark as read
                                    </a>
                                    <a class="dropdown-item" href="#" onclick="dismissSystemAnnouncement(${announcement.announcement_id})">
                                        <i class="mdi mdi-bell-off-outline me-1"></i>Dismiss
                                    </a>
                                </div>
                            </div>
                        </div>
                        <p class="card-text">${announcement.content.substring(0, 200)}${announcement.content.length > 200 ? '...' : ''}</p>
                        <div class="d-flex align-items-center text-muted">
                            <small><i class="mdi mdi-account me-1"></i> From: ${announcement.first_name} ${announcement.last_name}</small>
                            <small class="ms-3"><i class="mdi mdi-calendar me-1"></i> ${dateStr}</small>
                        </div>
                    </div>
                </div>
            </div>
        `);
                $('#system-announcements-container').prepend(card);
            }

            // Helper functions
            function getStatusBadge(status) {
                switch (status) {
                    case 'Published':
                        return '<span class="badge bg-success">Published</span>';
                    case 'Draft':
                        return '<span class="badge bg-secondary">Draft</span>';
                    case 'Scheduled':
                        return '<span class="badge bg-info">Scheduled</span>';
                    case 'Archived':
                        return '<span class="badge bg-dark">Archived</span>';
                    default:
                        return '<span class="badge bg-secondary">Draft</span>';
                }
            }

            function getImportanceBadge(importance) {
                switch (importance) {
                    case 'Low':
                        return '<span class="badge bg-success">Low</span>';
                    case 'Medium':
                        return '<span class="badge bg-warning">Medium</span>';
                    case 'High':
                    case 'Critical':
                        return '<span class="badge bg-danger">' + importance + '</span>';
                    default:
                        return '<span class="badge bg-warning">Medium</span>';
                }
            }

            function getBorderClass(importance) {
                switch (importance) {
                    case 'Low':
                        return 'border-success';
                    case 'Medium':
                        return 'border-info';
                    case 'High':
                        return 'border-warning';
                    case 'Critical':
                        return 'border-danger';
                    default:
                        return 'border-info';
                }
            }

            function getBadgeClass(importance) {
                switch (importance) {
                    case 'Low':
                        return 'bg-success';
                    case 'Medium':
                        return 'bg-info';
                    case 'High':
                        return 'bg-warning';
                    case 'Critical':
                        return 'bg-danger';
                    default:
                        return 'bg-info';
                }
            }

            function formatDate(dateString) {
                const date = new Date(dateString);
                return date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            }

            // Filter announcements
            $('#announcement-status-filter').on('change', function() {
                const selectedStatus = $(this).val();
                showOverlay('Filtering announcements...');
                $.ajax({
                    url: '../ajax/instructors/filter_announcements.php',
                    type: 'GET',
                    data: {
                        status: selectedStatus
                    },
                    dataType: 'json',
                    success: function(data) {
                        removeOverlay();
                        $('#my-announcements-list').empty();
                        if (data.myAnnouncements && data.myAnnouncements.length > 0) {
                            data.myAnnouncements.forEach(addAnnouncementToList);
                        } else {
                            $('#my-announcements-list').append(createEmptyState('my-announcements', 'filter-outline',
                                'No announcements match your filter',
                                'Try changing your filter criteria'));
                        }
                    },
                    error: function(xhr) {
                        removeOverlay();
                        showAlert('error', 'Failed to filter announcements. Please try again.');
                        console.error('Error filtering announcements:', xhr);
                    }
                });
            });

            // Filter course announcements
            $('#course-filter').on('change', function() {
                const selectedCourseId = $(this).val();
                showOverlay('Filtering course announcements...');
                $.ajax({
                    url: '../ajax/instructors/filter_course_announcements.php',
                    type: 'GET',
                    data: {
                        course_id: selectedCourseId
                    },
                    dataType: 'json',
                    success: function(data) {
                        removeOverlay();
                        $('#course-announcements-list').empty();
                        if (data.courseAnnouncements && data.courseAnnouncements.length > 0) {
                            data.courseAnnouncements.forEach(addCourseAnnouncementToList);
                        } else {
                            $('#course-announcements-list').append(createEmptyState('course-announcements', 'filter-outline',
                                'No announcements match your filter',
                                'Try selecting a different course'));
                        }
                    },
                    error: function(xhr) {
                        removeOverlay();
                        showAlert('error', 'Failed to filter course announcements. Please try again.');
                        console.error('Error filtering course announcements:', xhr);
                    }
                });
            });

            // Search functionality with debounce
            let searchTimeout = null;
            $('#announcement-search').on('keyup', function() {
                const searchTerm = $(this).val().trim();

                // Clear the previous timeout
                if (searchTimeout) {
                    clearTimeout(searchTimeout);
                }

                // Set a new timeout to prevent excessive requests
                searchTimeout = setTimeout(function() {
                    if (searchTerm.length >= 2) {
                        searchAnnouncements(searchTerm);
                    } else if (searchTerm.length === 0) {
                        // If search cleared, reload all announcements
                        loadAnnouncements();
                    }
                }, 500); // 500ms delay
            });

            // Keep the button click handler as well
            $('#announcement-search').next('button').on('click', function() {
                const searchTerm = $('#announcement-search').val().trim();
                if (searchTerm) searchAnnouncements(searchTerm);
            });


            function searchAnnouncements(searchTerm) {
                showOverlay('Searching announcements...');
                $.ajax({
                    url: '../ajax/instructors/search_announcements.php',
                    type: 'GET',
                    data: {
                        search: searchTerm
                    },
                    dataType: 'json',
                    success: function(data) {
                        removeOverlay();

                        // My Announcements
                        $('#my-announcements-list').empty();
                        if (data.myAnnouncements && data.myAnnouncements.length > 0) {
                            data.myAnnouncements.forEach(addAnnouncementToList);
                        } else {
                            $('#my-announcements-list').append(createEmptyState('my-announcements', 'magnify',
                                'No announcements match your search',
                                'Try different keywords'));
                        }

                        // Course Announcements
                        $('#course-announcements-list').empty();
                        if (data.courseAnnouncements && data.courseAnnouncements.length > 0) {
                            data.courseAnnouncements.forEach(addCourseAnnouncementToList);
                        } else {
                            $('#course-announcements-list').append(createEmptyState('course-announcements', 'magnify',
                                'No course announcements match your search',
                                'Try different keywords'));
                        }
                    },
                    error: function(xhr) {
                        removeOverlay();
                        showAlert('error', 'Failed to search announcements. Please try again.');
                        console.error('Error searching announcements:', xhr);
                    }
                });
            }

            // Tab selection
            $('.nav-tabs a').on('click', function(e) {
                e.preventDefault();
                $(this).tab('show');

                // Load data for the selected tab if needed
                const tabId = $(this).attr('href');
                if (tabId === '#announcement-templates' && $('#templates-list tr').length <= 1) {
                    loadTemplates();
                }
            });

            // Template functionality
            let currentTemplateId = null;

            // Load templates
            // Load templates
            function loadTemplates() {
                showOverlay('Loading templates...');
                $.ajax({
                    url: '../ajax/instructors/load_announcement_templates.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        removeOverlay();
                        $('#templates-list').empty(); // Clear the list first

                        // Add the empty state row back to the table (it will be hidden if not needed)
                        if ($('#empty-templates').length === 0) {
                            $('#templates-list').append(`
                    <tr id="empty-templates">
                        <td colspan="5" class="text-center py-4">
                            <div>
                                <i class="mdi mdi-file-document-outline text-muted" style="font-size: 48px;"></i>
                                <h5 class="mt-2">No templates found</h5>
                                <p class="text-muted">Create templates to save time when creating similar announcements</p>
                                <button type="button" class="btn btn-primary mt-2" id="empty-create-template-btn">
                                    Create Template
                                </button>
                            </div>
                        </td>
                    </tr>
                `);
                        }

                        if (data.success && data.templates && data.templates.length > 0) {
                            // Hide the empty state and add templates
                            $('#empty-templates').hide();
                            data.templates.forEach(function(template) {
                                addTemplateToList(template);
                            });
                        } else {
                            // Show the empty state if no templates
                            $('#empty-templates').show();
                        }
                    },
                    error: function(xhr) {
                        removeOverlay();
                        showAlert('error', 'Failed to load templates. Please try again.');
                        console.error('Error loading templates:', xhr);
                    }
                });
            }
            // Add template to list
            function addTemplateToList(template) {
                const row = $(`
        <tr>
            <td><a href="#" class="text-body fw-bold template-title" data-id="${template.template_id}">${template.title}</a></td>
            <td>${template.content_preview || 'No description'}</td>
            <td>${template.created_at_formatted}</td>
            <td>${template.last_used_formatted}</td>
            <td>
                <div class="action-dropdown">
                    <a href="#" class="dropdown-toggle arrow-none btn btn-light btn-sm">
                        <i class="mdi mdi-dots-horizontal"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item edit-template" href="#" data-id="${template.template_id}">
                            <i class="mdi mdi-pencil me-1"></i>Edit
                        </a>
                        <a class="dropdown-item use-template" href="#" data-id="${template.template_id}">
                            <i class="mdi mdi-file-document-edit-outline me-1"></i>Use as Announcement
                        </a>
                        <a class="dropdown-item delete-template" href="#" data-id="${template.template_id}">
                            <i class="mdi mdi-delete me-1"></i>Delete
                        </a>
                    </div>
                </div>
            </td>
        </tr>
    `);
                $('#templates-list').append(row);
            }
            // Template Modal HTML
            function addTemplateModal() {
                if ($('#template-modal').length === 0) {
                    const modal = `
            <div class="modal fade" id="template-modal" tabindex="-1" role="dialog" aria-labelledby="template-modal-label" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="template-modal-label">Create Template</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="template-form">
                                <div class="mb-3">
                                    <label for="template-title" class="form-label">Template Name</label>
                                    <input type="text" class="form-control" id="template-title" required placeholder="Enter template name">
                                </div>
                                <div class="mb-3">
                                    <label for="template-content" class="form-label">Content</label>
                                    <textarea class="form-control" id="template-content" rows="5" placeholder="Enter template content"></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="save-template">Save Template</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
                    $('body').append(modal);

                    // Initialize TinyMCE for the template content
                    if (typeof tinymce !== 'undefined') {
                        tinymce.init({
                            selector: '#template-content',
                            height: 300,
                            menubar: false,
                            plugins: ['lists', 'link', 'image', 'code', 'table'],
                            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter ' +
                                'alignright alignjustify | bullist numlist | link image | table | removeformat',
                        });
                    }
                }
            }

            // Template Modal Events
            function initTemplateEvents() {
                // Create template button
                $('#create-template-btn, #empty-create-template-btn').on('click', function() {
                    currentTemplateId = null;
                    $('#template-modal-label').text('Create Template');
                    $('#template-title').val('');

                    if (typeof tinymce !== 'undefined' && tinymce.get('template-content')) {
                        tinymce.get('template-content').setContent('');
                    } else {
                        $('#template-content').val('');
                    }

                    $('#template-modal').modal('show');
                });

                // Save template
                $(document).on('click', '#save-template', function() {
                    const title = $('#template-title').val();
                    let content = typeof tinymce !== 'undefined' && tinymce.get('template-content') ?
                        tinymce.get('template-content').getContent() : $('#template-content').val();

                    if (!title.trim()) return showAlert('error', 'Please enter a template name');
                    if (!content.trim()) return showAlert('error', 'Please enter template content');

                    const formData = new FormData();
                    formData.append('title', title);
                    formData.append('content', content);

                    if (currentTemplateId) {
                        formData.append('template_id', currentTemplateId);
                    }

                    showOverlay('Saving template...');
                    $.ajax({
                        url: '../ajax/instructors/save_announcement_templates.php',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            removeOverlay();
                            try {
                                const result = typeof response === 'string' ? JSON.parse(response) : response;
                                if (result.success) {
                                    showAlert('success', currentTemplateId ?
                                        'Template updated successfully!' :
                                        'Template created successfully!');
                                    $('#template-modal').modal('hide');
                                    loadTemplates();
                                } else {
                                    showAlert('error', result.message || 'Error saving template');
                                }
                            } catch (e) {
                                showAlert('error', 'Invalid response from server');
                                console.error('Error parsing response:', e, response);
                            }
                        },
                        error: function(xhr) {
                            removeOverlay();
                            showAlert('error', 'Server error. Please try again later.');
                            console.error('AJAX error:', xhr);
                        }
                    });
                });

                // Edit template
                $(document).on('click', '.edit-template', function(e) {
                    e.preventDefault();
                    const templateId = $(this).data('id');
                    editTemplate(templateId);
                });

                // Use template
                $(document).on('click', '.use-template, .template-title', function(e) {
                    e.preventDefault();
                    const templateId = $(this).data('id');
                    useTemplate(templateId);
                });

                // Delete template
                $(document).on('click', '.delete-template', function(e) {
                    e.preventDefault();
                    const templateId = $(this).data('id');
                    deleteTemplate(templateId);
                });
            }

            // Edit template
            function editTemplate(templateId) {
                showOverlay('Loading template...');
                $.ajax({
                    url: '../ajax/instructors/get_announcement_templates.php',
                    type: 'GET',
                    data: {
                        template_id: templateId
                    },
                    dataType: 'json',
                    success: function(data) {
                        removeOverlay();
                        if (data.success) {
                            currentTemplateId = templateId;
                            $('#template-modal-label').text('Edit Template');
                            $('#template-title').val(data.template.title);

                            if (typeof tinymce !== 'undefined' && tinymce.get('template-content')) {
                                tinymce.get('template-content').setContent(data.template.content);
                            } else {
                                $('#template-content').val(data.template.content);
                            }

                            $('#template-modal').modal('show');
                        } else {
                            showAlert('error', data.message || 'Failed to load template');
                        }
                    },
                    error: function(xhr) {
                        removeOverlay();
                        showAlert('error', 'Server error. Please try again later.');
                        console.error('Error loading template:', xhr);
                    }
                });
            }

            // Use template for new announcement
            function useTemplate(templateId) {
                showOverlay('Loading template...');
                $.ajax({
                    url: '../ajax/instructors/get_announcement_templates.php',
                    type: 'GET',
                    data: {
                        template_id: templateId
                    },
                    dataType: 'json',
                    success: function(data) {
                        removeOverlay();
                        if (data.success) {
                            // Fill the announcement form with template data
                            $('#announcement-title').val(data.template.title);

                            if (typeof tinymce !== 'undefined' && tinymce.get('announcement-content')) {
                                tinymce.get('announcement-content').setContent(data.template.content);
                            } else {
                                $('#announcement-content').val(data.template.content);
                            }

                            // Switch to the "My Announcements" tab
                            $('.nav-tabs a[href="#my-announcements"]').tab('show');

                            // Open the announcement modal
                            $('#create-announcement-modal').modal('show');

                            // Clear any edit mode state
                            $('#edit-announcement-id').remove();
                            $('#create-announcement-modal-label').text('Create Announcement');
                            $('#save-announcement').text('Save Announcement');
                        } else {
                            showAlert('error', data.message || 'Failed to load template');
                        }
                    },
                    error: function(xhr) {
                        removeOverlay();
                        showAlert('error', 'Server error. Please try again later.');
                        console.error('Error loading template:', xhr);
                    }
                });
            }

            // Delete template
            function deleteTemplate(templateId) {
                if (confirm('Are you sure you want to delete this template?')) {
                    showOverlay('Deleting template...');
                    $.ajax({
                        url: '../ajax/instructors/delete_announcement_templates.php',
                        type: 'POST',
                        data: {
                            template_id: templateId
                        },
                        dataType: 'json',
                        success: function(data) {
                            removeOverlay();
                            if (data.success) {
                                showAlert('success', 'Template deleted successfully');
                                loadTemplates();
                            } else {
                                showAlert('error', data.message || 'Failed to delete template');
                            }
                        },
                        error: function(xhr) {
                            removeOverlay();
                            showAlert('error', 'Server error. Please try again later.');
                            console.error('Error deleting template:', xhr);
                        }
                    });
                }
            }

            // Initialize the page - add to your existing initialization function
            function initializeAnnouncementPage() {
                // First clear hardcoded content
                clearHardcodedContent();

                // Add the template modal
                addTemplateModal();

                // Initialize template events
                initTemplateEvents();

                // Then load real data
                fetchCourses();
                loadAnnouncements();
                loadAnnouncementStats();
                loadTemplates(); // Add this line
                updateFileCounter();
            }


            // Additional utility functions for system announcements
            window.viewAnnouncementDetails = function(announcementId) {
                console.log('View announcement details:', announcementId);
                // Implementation would go here
            };

            window.markAsRead = function(announcementId) {
                console.log('Mark announcement as read:', announcementId);
                // Implementation would go here
            };

            window.markSystemAnnouncementAsRead = function(announcementId) {
                console.log('Mark system announcement as read:', announcementId);
                // Implementation would go here
            };

            window.dismissSystemAnnouncement = function(announcementId) {
                console.log('Dismiss system announcement:', announcementId);
                // Implementation would go here
            };

            // Modal events
            $('#create-announcement-modal').on('shown.bs.modal', function() {
                $('#announcement-title').focus();
                if (typeof tinymce !== 'undefined' && !tinymce.get('announcement-content')) {
                    tinymce.init({
                        selector: '#announcement-content',
                        height: 300,
                        menubar: false,
                        plugins: ['lists', 'link', 'image', 'code', 'table'],
                        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter ' +
                            'alignright alignjustify | bullist numlist | link image | table | removeformat',
                    });
                }
            });

            $('#create-announcement-modal').on('hidden.bs.modal', function() {
                resetAnnouncementForm();
            });

            // Alert and overlay functions
            function showAlert(type, message) {
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const alertDiv = $(`
           <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
               ${message}
               <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
           </div>
       `).css({
                    'position': 'fixed',
                    'top': '20px',
                    'left': '50%',
                    'transform': 'translateX(-50%)',
                    'z-index': '9999',
                    'min-width': '300px',
                    'box-shadow': '0 4px 8px rgba(0,0,0,0.1)'
                });

                $('body').append(alertDiv);
                setTimeout(() => {
                    alertDiv.removeClass('show');
                    setTimeout(() => alertDiv.remove(), 300);
                }, 5000);
            }

            function showOverlay(message) {
                const overlay = $(`
           <div class="custom-overlay">
               <div class="spinner-border text-primary" role="status">
                   <span class="visually-hidden">Loading...</span>
               </div>
               ${message ? `<div class="text-white ms-3">${message}</div>` : ''}
           </div>
       `).css({
                    'position': 'fixed',
                    'top': '0',
                    'left': '0',
                    'width': '100%',
                    'height': '100%',
                    'background-color': 'rgba(0,0,0,0.5)',
                    'display': 'flex',
                    'justify-content': 'center',
                    'align-items': 'center',
                    'z-index': '9999'
                });

                $('body').append(overlay);
            }

            function removeOverlay() {
                $('.custom-overlay').remove();
            }

            // Initialize the page
            initializeAnnouncementPage();
        });
    </script>

</body>

</html>