<?php
require '../backend/session_start.php';

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    // Log unauthorized access attempt for security auditing
    error_log("Unauthorized access attempt detected: " . json_encode($_SERVER));

    // Redirect unauthorized users to a custom unauthorized access page or login page
    header('Location: landing.php');
    exit;
}

require_once '../backend/config.php';

// Get instructor ID from the session
$instructor_id = $_SESSION['user_id'];

// Get course filter (if provided)
$course_filter = isset($_GET['course']) ? intval($_GET['course']) : 0;
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Get all instructor's courses for filter dropdown
$courses_query = "SELECT course_id, title FROM courses WHERE instructor_id = ? AND status = 'Published'";
$stmt = $conn->prepare($courses_query);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$courses_result = $stmt->get_result();
$courses = [];
while ($course = $courses_result->fetch_assoc()) {
    $courses[] = $course;
}

// Get total student count for summary
$count_query = "SELECT COUNT(DISTINCT u.user_id) as total 
               FROM users u
               JOIN enrollments e ON u.user_id = e.user_id
               JOIN courses c ON e.course_id = c.course_id
               WHERE c.instructor_id = ?";
$stmt = $conn->prepare($count_query);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_students = $row['total'];

// Get active student count
$active_query = "SELECT COUNT(DISTINCT u.user_id) as active 
                FROM users u
                JOIN enrollments e ON u.user_id = e.user_id
                JOIN courses c ON e.course_id = c.course_id
                WHERE c.instructor_id = ? 
                AND e.status = 'Active'
                AND e.last_accessed >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$stmt = $conn->prepare($active_query);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$active_students = $row['active'];

// Get average course completion percentage
$completion_query = "SELECT AVG(e.completion_percentage) as avg_completion 
                    FROM enrollments e
                    JOIN courses c ON e.course_id = c.course_id
                    WHERE c.instructor_id = ?";
$stmt = $conn->prepare($completion_query);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$avg_completion = number_format($row['avg_completion'] ?? 0, 1);

// Main query to get all data for datatable (we'll fetch with AJAX)
// This is just to check if at least one student exists
$check_query = "SELECT EXISTS(
                SELECT 1 FROM users u
                JOIN enrollments e ON u.user_id = e.user_id
                JOIN courses c ON e.course_id = c.course_id
                WHERE c.instructor_id = ?
                ) as has_students";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$has_students = $row['has_students'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>All Students | Learnix - Empowering Education</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Manage all students enrolled in your courses" name="description" />
    <meta content="Learnix Development Team" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- third party css -->
    <link href="assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/vendor/dataTables.bootstrap5.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/vendor/responsive.bootstrap5.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/vendor/buttons.bootstrap5.css" rel="stylesheet" type="text/css" />
    <!-- third party css end -->

    <!-- App css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style"/>
    
    <style>
        .avatar-sm {
            width: 36px;
            height: 36px;
            object-fit: cover;
            border-radius: 50%;
        }
        .progress {
            height: 8px;
        }
        .student-name {
            display: inline-block;
            vertical-align: middle;
        }
        .dropdown-menu .dropdown-item {
            padding: 0.4rem 1.2rem;
        }
        .activity-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        .active-now {
            background-color: #0acf97;
        }
        .active-recent {
            background-color: #ffbc00;
        }
        .inactive {
            background-color: #fa5c7c;
        }
        .badge-courses {
            font-size: 0.7rem;
            font-weight: 600;
        }
        .btn-action {
            padding: 0.2rem 0.5rem;
            font-size: 0.75rem;
        }
    </style>
</head>

<body class="loading" data-layout-color="light" data-leftbar-theme="dark" data-layout-mode="fluid" data-rightbar-onstart="true">
    <!-- Begin page -->
    <div class="wrapper">
        <!-- ========== Left Sidebar Start ========== -->
        <?php include '../includes/instructor-sidebar.php'; ?>
        <!-- Left Sidebar End -->

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="content-page">
            <div class="content">
                <!-- Topbar Start -->
                <?php include '../includes/instructor-topnavbar.php'; ?>
                <!-- end Topbar -->
                
                <!-- Start Content-->
                <div class="container-fluid">
                
                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                        <li class="breadcrumb-item active">All Students</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">All Students</h4>
                            </div>
                        </div>
                    </div>     
                    <!-- end page title -->

                    <!-- Stats row -->
                    <div class="row">
                        <div class="col-xl-4 col-lg-4">
                            <div class="card tilebox-one">
                                <div class="card-body">
                                    <i class="uil uil-users-alt float-end text-muted"></i>
                                    <h6 class="text-uppercase mt-0">Total Students</h6>
                                    <h2 class="my-2"><?php echo $total_students; ?></h2>
                                    <p class="mb-0 text-muted">
                                        <span class="text-nowrap">Enrolled across your courses</span>
                                    </p>
                                </div> <!-- end card-body-->
                            </div> <!-- end card-->
                        </div> <!-- end col-->

                        <div class="col-xl-4 col-lg-4">
                            <div class="card tilebox-one">
                                <div class="card-body">
                                    <i class="uil uil-user-check float-end text-muted"></i>
                                    <h6 class="text-uppercase mt-0">Active Students</h6>
                                    <h2 class="my-2"><?php echo $active_students; ?></h2>
                                    <p class="mb-0 text-muted">
                                        <span class="text-nowrap">Active in the last 30 days</span>
                                    </p>
                                </div> <!-- end card-body-->
                            </div> <!-- end card-->
                        </div> <!-- end col-->

                        <div class="col-xl-4 col-lg-4">
                            <div class="card tilebox-one">
                                <div class="card-body">
                                    <i class="uil uil-chart-line float-end text-muted"></i>
                                    <h6 class="text-uppercase mt-0">Avg. Completion</h6>
                                    <h2 class="my-2"><?php echo $avg_completion; ?>%</h2>
                                    <p class="mb-0 text-muted">
                                        <span class="text-nowrap">Average course completion rate</span>
                                    </p>
                                </div> <!-- end card-body-->
                            </div> <!-- end card-->
                        </div> <!-- end col-->
                    </div> <!-- end row -->

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <!-- Filter options -->
                                    <div class="row mb-3">
                                        <div class="col-md-4 mb-2">
                                            <label for="course-filter" class="form-label">Filter by Course</label>
                                            <select class="form-select" id="course-filter">
                                                <option value="0">All Courses</option>
                                                <?php foreach ($courses as $course): ?>
                                                <option value="<?php echo $course['course_id']; ?>"><?php echo htmlspecialchars($course['title']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-4 mb-2">
                                            <label for="status-filter" class="form-label">Enrollment Status</label>
                                            <select class="form-select" id="status-filter">
                                                <option value="">All Statuses</option>
                                                <option value="Active">Active</option>
                                                <option value="Completed">Completed</option>
                                                <option value="Expired">Expired</option>
                                                <option value="Suspended">Suspended</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4 mb-2">
                                            <label for="activity-filter" class="form-label">Activity Level</label>
                                            <select class="form-select" id="activity-filter">
                                                <option value="">All Activity Levels</option>
                                                <option value="active-now">Active (7 days)</option>
                                                <option value="active-recent">Recently Active (30 days)</option>
                                                <option value="inactive">Inactive (30+ days)</option>
                                            </select>
                                        </div>
                                    </div>
                                
                                    <div class="table-responsive">
                                        <table id="students-datatable" class="table table-centered table-striped dt-responsive nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>Student</th>
                                                    <th>Enrolled Courses</th>
                                                    <th>Enrollment Date</th>
                                                    <th>Last Activity</th>
                                                    <th>Progress</th>
                                                    <th>Quiz Avg.</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Data will be loaded via AJAX -->
                                                <?php if (!$has_students): ?>
                                                <tr>
                                                    <td colspan="8" class="text-center">No students found. Once students enroll in your courses, they will appear here.</td>
                                                </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div> <!-- end card body-->
                            </div> <!-- end card -->
                        </div><!-- end col-->
                    </div> <!-- end row-->
                    
                </div> <!-- container -->

            </div> <!-- content -->

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

    <!-- bundle -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.min.js"></script>

    <!-- third party js -->
    <script src="assets/js/vendor/jquery.dataTables.min.js"></script>
    <script src="assets/js/vendor/dataTables.bootstrap5.js"></script>
    <script src="assets/js/vendor/dataTables.responsive.min.js"></script>
    <script src="assets/js/vendor/responsive.bootstrap5.min.js"></script>
    <script src="assets/js/vendor/dataTables.buttons.min.js"></script>
    <script src="assets/js/vendor/buttons.bootstrap5.min.js"></script>
    <script src="assets/js/vendor/buttons.html5.min.js"></script>
    <script src="assets/js/vendor/buttons.flash.min.js"></script>
    <script src="assets/js/vendor/buttons.print.min.js"></script>
    <!-- third party js ends -->

    <!-- Loading overlay -->
    <script>
        // Show Loading Overlay
        function showOverlay(message = null) {
            // Remove any existing overlay
            const existingOverlay = document.querySelector('.custom-overlay');
            if (existingOverlay) {
                existingOverlay.remove();
            }

            // Create new overlay
            const overlay = document.createElement('div');
            overlay.className = 'custom-overlay';
            overlay.innerHTML = `
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            ${message ? `<div class="text-white ms-3">${message}</div>` : ''}
        `;

            document.body.appendChild(overlay);
            
            // Add styles
            overlay.style.position = 'fixed';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.width = '100%';
            overlay.style.height = '100%';
            overlay.style.backgroundColor = 'rgba(0,0,0,0.5)';
            overlay.style.display = 'flex';
            overlay.style.justifyContent = 'center';
            overlay.style.alignItems = 'center';
            overlay.style.zIndex = '9999';
        }

        // Remove Loading Overlay
        function removeOverlay() {
            const overlay = document.querySelector('.custom-overlay');
            if (overlay) {
                overlay.remove();
            }
        }
        
        // Show alert notification function
        function showAlert(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
            alertDiv.setAttribute('role', 'alert');
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            // Position the alert
            alertDiv.style.position = 'fixed';
            alertDiv.style.top = '20px';
            alertDiv.style.left = '50%';
            alertDiv.style.transform = 'translateX(-50%)';
            alertDiv.style.zIndex = '9999';
            alertDiv.style.minWidth = '300px';
            alertDiv.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
            document.body.appendChild(alertDiv);
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.classList.remove('show');
                    setTimeout(() => {
                        if (alertDiv.parentNode) {
                            alertDiv.parentNode.removeChild(alertDiv);
                        }
                    }, 300);
                }
            }, 5000);
        }
    </script>

    <!-- Students DataTable -->
    <script>
        $(document).ready(function() {
            // Format date in a readable format
            function formatDate(dateString) {
                if (!dateString) return 'Never';
                
                const date = new Date(dateString);
                const now = new Date();
                const diffTime = Math.abs(now - date);
                const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
                
                if (diffDays === 0) {
                    return 'Today';
                } else if (diffDays === 1) {
                    return 'Yesterday';
                } else if (diffDays < 7) {
                    return diffDays + ' days ago';
                } else if (diffDays < 30) {
                    return Math.floor(diffDays / 7) + ' weeks ago';
                } else {
                    const options = { year: 'numeric', month: 'short', day: 'numeric' };
                    return date.toLocaleDateString('en-US', options);
                }
            }
            
            // Format the activity status with indicator
            function formatActivity(lastActivity) {
                if (!lastActivity) return '<span class="activity-indicator inactive"></span> Inactive';
                
                const activityDate = new Date(lastActivity);
                const now = new Date();
                const diffTime = Math.abs(now - activityDate);
                const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
                
                if (diffDays <= 7) {
                    return '<span class="activity-indicator active-now"></span> Active';
                } else if (diffDays <= 30) {
                    return '<span class="activity-indicator active-recent"></span> Recent';
                } else {
                    return '<span class="activity-indicator inactive"></span> Inactive';
                }
            }
            
            // Check if we have students before initializing DataTable with AJAX
            <?php if ($has_students): ?>
            // Initialize DataTable with AJAX loading
            var table = $('#students-datatable').DataTable({
                processing: true,
                serverSide: false, // Change to false for initial implementation
                ajax: {
                    url: '../ajax/instructors/get_students.php',
                    type: 'POST',
                    data: function(d) {
    // Get values from the filters
    var courseVal = $('#course-filter').val();
    var statusVal = $('#status-filter').val();
    var activityVal = $('#activity-filter').val();
    
    // Ensure we have proper data types
    d.course = courseVal ? parseInt(courseVal) : 0;
    d.status = statusVal ? String(statusVal).trim() : '';
    d.activity = activityVal ? String(activityVal).trim() : '';
    
    // Log to console and debug panel
    console.log('Sending filters to server:', {
        course: d.course,
        status: d.status,
        activity: d.activity
    });
    
    $('#debug-content').html(
        `Sending to server:\n` +
        `course=${d.course} (${typeof d.course})\n` +
        `status=${d.status} (${typeof d.status})\n` +
        `activity=${d.activity} (${typeof d.activity})`
    );
},
                    error: function(xhr, error, thrown) {
                        console.error('AJAX error:', error, thrown);
                        showAlert('danger', 'Error loading student data. Please refresh the page.');
                    }
                },
                columns: [
                    { 
                        data: null,
                        render: function(data, type, row) {
                            return `
                                <div class="d-flex align-items-center">
                                    <img src="${row.profile_pic ? '../uploads/profile/' + row.profile_pic : 'assets/images/users/default.png'}" 
                                         alt="Profile" class="avatar-sm me-2">
                                    <div>
                                        <h5 class="m-0 student-name">${row.first_name} ${row.last_name}</h5>
                                        <small class="text-muted">${row.email}</small>
                                    </div>
                                </div>
                            `;
                        }
                    },
                    { data: 'enrolled_courses' },
                    { 
                        data: 'enrolled_at',
                        render: function(data) {
                            return formatDate(data);
                        }
                    },
                    { 
                        data: 'last_activity',
                        render: function(data) {
                            return formatActivity(data);
                        }
                    },
                    { 
                        data: 'avg_completion',
                        render: function(data) {
                            const progress = parseFloat(data) || 0;
                            const progressClass = progress < 30 ? 'bg-danger' : 
                                                progress < 70 ? 'bg-warning' : 'bg-success';
                            
                            return `
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar ${progressClass}" role="progressbar" 
                                         style="width: ${progress}%;" aria-valuenow="${progress}" 
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted">${progress.toFixed(1)}% complete</small>
                            `;
                        }
                    },
                    { 
                        data: 'quiz_avg',
                        render: function(data) {
                            const score = parseFloat(data) || 0;
                            return score.toFixed(1) + '%';
                        }
                    },
                    { 
                        data: 'status',
                        render: function(data) {
                            let badgeClass = '';
                            switch(data) {
                                case 'Active': badgeClass = 'bg-success'; break;
                                case 'Completed': badgeClass = 'bg-info'; break;
                                case 'Expired': badgeClass = 'bg-warning'; break;
                                case 'Suspended': badgeClass = 'bg-danger'; break;
                                default: badgeClass = 'bg-secondary';
                            }
                            return `<span class="badge ${badgeClass}">${data}</span>`;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="btn-group">
                                    <a href="track-progress.php?student_id=${row.user_id}" class="btn btn-sm btn-primary btn-action">
                                        <i class="uil uil-chart-line"></i> Track
                                    </a>
                                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="visually-hidden">Toggle Dropdown</span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="javascript:void(0);" onclick="sendMessage(${row.user_id})">
                                            <i class="uil uil-envelope me-1"></i> Message</a>
                                        </li>
                                        <li><a class="dropdown-item" href="javascript:void(0);" onclick="exportProgress(${row.user_id})">
                                            <i class="uil uil-file-download me-1"></i> Export Data</a>
                                        </li>
                                    </ul>
                                </div>
                            `;
                        }
                    }
                ],
                dom: 'Bfrtip',
                buttons: [
                    { 
                        extend: 'copy', 
                        text: '<i class="uil uil-copy me-1"></i> Copy',
                        className: 'btn btn-light btn-sm' 
                    },
                    { 
                        extend: 'excel', 
                        text: '<i class="uil uil-file-excel me-1"></i> Excel',
                        className: 'btn btn-light btn-sm' 
                    },
                    { 
                        extend: 'pdf', 
                        text: '<i class="uil uil-file-pdf me-1"></i> PDF',
                        className: 'btn btn-light btn-sm' 
                    },
                    { 
                        extend: 'print', 
                        text: '<i class="uil uil-print me-1"></i> Print',
                        className: 'btn btn-light btn-sm' 
                    }
                ],
                order: [[2, 'desc']], // Sort by enrollment date by default
                pageLength: 10,
                lengthChange: false,
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    },
                    emptyTable: "No students found for your courses"
                },
                drawCallback: function() {
                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
                }
            });
            
            // Filter handlers
            $('#course-filter, #status-filter, #activity-filter').on('change', function() {
                console.log('Filter changed:', this.id, 'New value:', $(this).val());
                showOverlay('Loading students...');
                table.ajax.reload(function() {
                    removeOverlay();
                });
            });
            
            // Search box functionality
            $('#students-datatable_filter input').unbind();
            $('#students-datatable_filter input').bind('keyup', function(e) {
                if(e.keyCode == 13) {
                    table.search(this.value).draw();
                }
            });
            <?php else: ?>
            // Initialize a simple DataTable without AJAX if no students found
            $('#students-datatable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    { 
                        extend: 'copy', 
                        text: '<i class="uil uil-copy me-1"></i> Copy',
                        className: 'btn btn-light btn-sm' 
                    },
                    { 
                        extend: 'excel', 
                        text: '<i class="uil uil-file-excel me-1"></i> Excel',
                        className: 'btn btn-light btn-sm' 
                    },
                    { 
                        extend: 'pdf', 
                        text: '<i class="uil uil-file-pdf me-1"></i> PDF',
                        className: 'btn btn-light btn-sm' 
                    },
                    { 
                        extend: 'print', 
                        text: '<i class="uil uil-print me-1"></i> Print',
                        className: 'btn btn-light btn-sm' 
                    }
                ],
                language: {
                    emptyTable: "No students found. Once students enroll in your courses, they will appear here."
                }
            });
            <?php endif; ?>
        });
        
        // Function to send message to student
        function sendMessage(userId) {
            // Show modal for messaging
            // This will be implemented with a Bootstrap modal
            // For now, we'll just show an alert
            showAlert('success', 'Messaging functionality will be implemented soon!');
        }
        
        // Function to export student progress data
        function exportProgress(userId) {
            showOverlay('Preparing student data...');
            
            // Simulate AJAX call
            setTimeout(function() {
                removeOverlay();
                showAlert('success', 'Student progress data has been exported!');
            }, 1500);
            
            // In production, this would be an actual AJAX call to a PHP script
            // that generates and serves the export file
        }
    </script>
</body>
</html>