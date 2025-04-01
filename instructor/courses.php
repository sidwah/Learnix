<?php
require '../backend/session_start.php'; // Ensure session is started
require '../backend/config.php';

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    // Log unauthorized access attempt for security auditing
    error_log("Unauthorized access attempt detected: " . json_encode($_SERVER));

    // Redirect unauthorized users to a custom unauthorized access page or login page
    header('Location: landing.php');
    exit;
}

$search_keyword = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';

$sql = "SELECT c.*, 
    (SELECT COUNT(*) FROM course_sections cs WHERE cs.course_id = c.course_id) AS section_count
    FROM courses c 
    WHERE c.instructor_id = ? AND c.title LIKE ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $instructor_id, $search_keyword);
$stmt->execute();
$result = $stmt->get_result();

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
    <link href="assets/css/vendor/dataTables.bootstrap5.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/vendor/responsive.bootstrap5.css" rel="stylesheet" type="text/css" />
    <!-- third party css end -->

    <!-- App css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

    <style>
        .action-icon {
            font-size: 1.2rem;
            display: inline-block;
            padding: 0 3px;
            color: #98a6ad;
            transition: all 0.3s ease;
        }
        .action-icon:hover {
            color: #3bafda;
            transform: scale(1.2);
        }
        .action-icon.text-danger:hover {
            color: #f1556c;
        }
        .course-thumbnail {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 4px;
        }
        .table-responsive {
            padding: 0 10px;
        }
        #courses-datatable_wrapper {
            padding-top: 10px;
        }
        .badge {
            font-weight: 500;
            padding: 0.35em 0.5em;
        }
        /* Ellipsis for long text */
        .course-title {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 200px;
            display: block;
        }
        .course-description {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 250px;
            display: block;
        }
        /* Disable expand/collapse feature */
        table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before,
        table.dataTable.dtr-inline.collapsed>tbody>tr>th.dtr-control:before {
            display: none !important;
        }
        table.dataTable>tbody>tr.child ul.dtr-details {
            display: none !important;
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
                <div class="container-fluid">
                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Learnix</a></li>
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Instructor</a></li>
                                        <li class="breadcrumb-item active">Courses</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Courses</h4>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    <div class="row mb-2">
                        <?php
                        // Assuming database connection is already established
                        if (!isset($_SESSION['user_id'])) {
                            die("User not logged in.");
                        }
                        $user_id = $_SESSION['user_id'];
                        // Fetch instructor ID using user ID
                        $query = "SELECT instructor_id FROM instructors WHERE user_id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $stmt->store_result();
                        if ($stmt->num_rows > 0) {
                            $stmt->bind_result($instructor_id);
                            $stmt->fetch();
                            $stmt->close();
                            // Modify query to include section count
                            $sql = "SELECT c.*, 
                                    (SELECT COUNT(*) FROM course_sections cs WHERE cs.course_id = c.course_id) AS section_count,
                                    s.name AS subcategory_name
                                    FROM courses c
                                    LEFT JOIN subcategories s ON c.subcategory_id = s.subcategory_id
                                    WHERE c.instructor_id = ?
                                    ORDER BY c.updated_at DESC";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $instructor_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                        } else {
                            die("Instructor not found.");
                        }
                        // Function to handle image display
                        function displayCourseImage($thumbnail)
                        {
                            // Define allowed image extensions
                            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                            // Get file extension
                            $ext = strtolower(pathinfo($thumbnail, PATHINFO_EXTENSION));
                            // Define upload paths
                            $upload_paths = [
                                '../uploads/thumbnails/',
                                '../uploads/',
                                'uploads/thumbnails/',
                                'uploads/'
                            ];
                            // Default placeholder image
                            $default_image = '../assets/images/default-course.jpg';
                            // Check if extension is allowed
                            if (!in_array($ext, $allowed_extensions)) {
                                return $default_image;
                            }
                            // Try different paths
                            foreach ($upload_paths as $path) {
                                $full_path = $path . $thumbnail;
                                if (file_exists($full_path)) {
                                    return $full_path;
                                }
                            }
                            // If no image found, return default
                            return $default_image;
                        }
                        ?>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row mb-2">
                                            <div class="col-sm-5">
                                                <button id="createNewCourseBtn" class="btn btn-danger mb-2">
                                                    <i class="mdi mdi-plus-circle me-2"></i> Add New Course
                                                </button>
                                            </div>
                                            <div class="col-sm-7">
                                                <!-- <div class="text-sm-end">
                                                    <div class="input-group">
                                                        <input type="text" id="courseSearch" class="form-control" placeholder="Search courses...">
                                                        <button class="btn btn-light" type="button">
                                                            <i class="mdi mdi-magnify"></i>
                                                        </button>
                                                    </div>
                                                </div> -->
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-centered w-100 dt-responsive nowrap" id="courses-datatable">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Course</th>
                                                        <th>Category</th>
                                                        <th>Status</th>
                                                        <th>Sections</th>
                                                        <th>Created Date</th>
                                                        <th style="width: 120px;">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($course = $result->fetch_assoc()) : ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <?php if (!empty($course['thumbnail'])): ?>
                                                                <img src="<?php echo displayCourseImage($course['thumbnail']); ?>" 
                                                                     alt="course-thumbnail" class="course-thumbnail me-3">
                                                                <?php else: ?>
                                                                <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                                                    <i class="mdi mdi-book-open-page-variant text-muted"></i>
                                                                </div>
                                                                <?php endif; ?>
                                                                <div style="min-width: 0;"> <!-- Added min-width: 0 to allow text truncation -->
                                                                    <h5 class="m-0 font-16 course-title" title="<?php echo htmlspecialchars($course['title']); ?>">
                                                                        <?php echo htmlspecialchars($course['title']); ?>
                                                                    </h5>
                                                                    <p class="mb-0 text-muted course-description" title="<?php echo htmlspecialchars($course['short_description']); ?>">
                                                                        <?php echo htmlspecialchars($course['short_description']); ?>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <?php echo htmlspecialchars($course['subcategory_name'] ?: 'Uncategorized'); ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-<?php 
                                                                echo match ($course['status']) {
                                                                    'Draft' => 'secondary',
                                                                    'Published' => 'success',
                                                                    'Pending' => 'warning',
                                                                    default => 'info'
                                                                };
                                                            ?>">
                                                                <?php echo htmlspecialchars($course['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php 
                                                            $section_count = $course['section_count'] ?? 0;
                                                            echo $section_count . ' ' . ($section_count == 1 ? 'Section' : 'Sections');
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <?php echo date('M d, Y', strtotime($course['created_at'])); ?>
                                                        </td>
                                                        <td class="table-action">
                                                            <a href="course-creator.php?course_id=<?php echo $course['course_id']; ?>" 
                                                               class="action-icon" title="Edit">
                                                               <i class="mdi mdi-square-edit-outline"></i>
                                                            </a>
                                                            <?php if ($course['status'] === 'Draft'): ?>
                                                            <a href="javascript:void(0);" 
                                                               onclick="prepareCourseForPublication(<?php echo $course['course_id']; ?>)" 
                                                               class="action-icon" title="Publish">
                                                               <i class="mdi mdi-publish"></i>
                                                            </a>
                                                            <?php endif; ?>
                                                            <a href="javascript:void(0);" 
                                                               class="action-icon text-danger" title="Delete">
                                                               <i class="mdi mdi-delete"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>

                                        <?php if ($result->num_rows === 0): ?>
                                        <div class="text-center py-5">
                                            <i class="mdi mdi-book-multiple-outline display-4 text-muted mb-3"></i>
                                            <h4>No Courses Found</h4>
                                            <p class="text-muted">Get started by creating your first course</p>
                                            <button id="createFirstCourseBtn" class="btn btn-primary mt-2">
                                                <i class="mdi mdi-plus-circle me-1"></i> Create Course
                                            </button>
                                        </div>
                                        <?php endif; ?>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col -->
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const createButtons = document.querySelectorAll('#createNewCourseBtn, #createFirstCourseBtn');

                                function createOverlay(message = null) {
                                    const overlay = document.createElement('div');
                                    overlay.id = 'pageOverlay';
                                    overlay.style.position = 'fixed';
                                    overlay.style.top = '0';
                                    overlay.style.left = '0';
                                    overlay.style.width = '100%';
                                    overlay.style.height = '100%';
                                    overlay.style.backgroundColor = 'rgba(255, 255, 255, 0.7)';
                                    overlay.style.backdropFilter = 'blur(5px)';
                                    overlay.style.zIndex = '9998';
                                    overlay.style.display = 'flex';
                                    overlay.style.flexDirection = 'column';
                                    overlay.style.justifyContent = 'center';
                                    overlay.style.alignItems = 'center';
                                    overlay.style.gap = '15px';

                                    // Add loading spinner
                                    const spinner = document.createElement('div');
                                    spinner.className = 'spinner-border text-primary';
                                    spinner.setAttribute('role', 'status');
                                    spinner.style.width = '3rem';
                                    spinner.style.height = '3rem';
                                    spinner.innerHTML = '<span class="visually-hidden">Loading...</span>';
                                    overlay.appendChild(spinner);

                                    // Add message if provided
                                    if (message) {
                                        const messageElement = document.createElement('div');
                                        messageElement.className = 'fw-semibold fs-5 text-primary';
                                        messageElement.textContent = message;
                                        overlay.appendChild(messageElement);
                                    }

                                    document.body.appendChild(overlay);
                                    return overlay;
                                }

                                function removeOverlay() {
                                    const overlay = document.getElementById('pageOverlay');
                                    if (overlay) {
                                        document.body.removeChild(overlay);
                                    }
                                }

                                function showAlert(type, message) {
                                    const alertDiv = document.createElement('div');
                                    alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
                                    alertDiv.setAttribute('role', 'alert');
                                    alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
                                    alertDiv.style.position = 'fixed';
                                    alertDiv.style.top = '20px';
                                    alertDiv.style.left = '50%';
                                    alertDiv.style.transform = 'translateX(-50%)';
                                    alertDiv.style.zIndex = '9999';
                                    alertDiv.style.minWidth = '300px';
                                    alertDiv.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
                                    document.body.appendChild(alertDiv);

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

                                function createNewCourse(e) {
                                    e.preventDefault();

                                    // Create loading overlay with message
                                    const overlay = createOverlay('Preparing new course...');

                                    fetch('../ajax/courses/start_course.php', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-Requested-With': 'XMLHttpRequest'
                                            }
                                        })
                                        .then(async (response) => {
                                            return new Promise((resolve) => {
                                                setTimeout(async () => {
                                                    removeOverlay();

                                                    if (!response.ok) {
                                                        const errorText = await response.text();
                                                        console.error('Full error response:', errorText);
                                                        throw new Error(`HTTP error! status: ${response.status}`);
                                                    }

                                                    const responseText = await response.text();
                                                    console.log('Raw response:', responseText);

                                                    try {
                                                        resolve(JSON.parse(responseText));
                                                    } catch (jsonError) {
                                                        console.error('JSON parsing error:', jsonError);
                                                        console.error('Problematic response text:', responseText);
                                                        throw new Error('Failed to parse JSON response');
                                                    }
                                                }, 2000); // 2-second delay before removing overlay
                                            });
                                        })
                                        .then(data => {
                                            if (data.success) {
                                                window.location.href = data.redirect;
                                            } else {
                                                console.error('Course creation error:', data);
                                                showAlert('danger', data.message || 'Failed to create course');
                                            }
                                        })
                                        .catch(error => {
                                            removeOverlay();
                                            console.error('Course creation error:', error);
                                            showAlert('danger', 'An error occurred while creating the course. Please check browser console for details.');
                                        });
                                }

                                createButtons.forEach(button => {
                                    button.addEventListener('click', createNewCourse);
                                });

                                // Initialize DataTable with responsive disabled
                                $('#courses-datatable').DataTable({
                                    "order": [[4, "desc"]], // Default sort by created date
                                    "responsive": false, // Disable responsive feature
                                    "columnDefs": [
                                        { "orderable": false, "targets": [5] } // Disable sorting for action column
                                    ],
                                    "language": {
                                        "paginate": {
                                            "previous": "<i class='mdi mdi-chevron-left'>",
                                            "next": "<i class='mdi mdi-chevron-right'>"
                                        }
                                    },
                                    "drawCallback": function() {
                                        $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
                                    }
                                });
                            });

                            function prepareCourseForPublication(courseId) {
                                // Your existing publish function implementation
                                console.log('Preparing to publish course:', courseId);
                                // Add your publish logic here
                            }
                        </script>
                        <?php
                        $stmt->close();
                        $conn->close();
                        ?>
                        <!-- end row-->
                    </div>
                    <!-- end row-->
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

    <!-- bundle -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.min.js"></script>

    <!-- third party js -->
    <script src="assets/js/vendor/jquery.dataTables.min.js"></script>
    <script src="assets/js/vendor/dataTables.bootstrap5.js"></script>
    <script src="assets/js/vendor/dataTables.responsive.min.js"></script>
    <script src="assets/js/vendor/responsive.bootstrap5.min.js"></script>
    <script src="assets/js/vendor/dataTables.checkboxes.min.js"></script>
    <!-- third party js ends -->

    <!-- demo app -->
    <script src="assets/js/pages/demo.datatable-init.js"></script>
    <!-- end demo js-->

    <!-- Custom script for course management -->
    <script>
        // Enhanced course management functions
        function deleteCourse(courseId) {
            if (confirm('Are you sure you want to delete this course? This action cannot be undone.')) {
                fetch(`../ajax/courses/delete_course.php?course_id=${courseId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('success', 'Course deleted successfully');
                        // Refresh the table or remove the row
                        $('#courses-datatable').DataTable().ajax.reload();
                    } else {
                        showAlert('danger', data.message || 'Failed to delete course');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('danger', 'An error occurred while deleting the course');
                });
            }
        }

        // Initialize delete buttons
        document.addEventListener('DOMContentLoaded', function() {
            $(document).on('click', '.delete-course', function() {
                const courseId = $(this).data('course-id');
                deleteCourse(courseId);
            });
        });
    </script>
    
</body>
</html>