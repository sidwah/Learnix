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

        .course-row {
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .course-row:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        /* Disable expand/collapse feature */
        table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before,
        table.dataTable.dtr-inline.collapsed>tbody>tr>th.dtr-control:before {
            display: none !important;
        }

        table.dataTable>tbody>tr.child ul.dtr-details {
            display: none !important;
        }
        
        /* Custom overlay styles */
        .custom-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(5px);
            z-index: 9998;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 15px;
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
                                                        <tr class="course-row" data-course-id="<?php echo $course['course_id']; ?>">
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
                                                                        class="action-icon text-danger delete-course"
                                                                        data-course-id="<?php echo $course['course_id']; ?>"
                                                                        data-course-title="<?php echo htmlspecialchars($course['title']); ?>"
                                                                        title="Delete">
                                                                        <i class="mdi mdi-delete"></i>
                                                                    </a>
                                                                <?php endif; ?>

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

                        <!-- Delete Course Confirmation Modal -->
                        <div class="modal fade" id="deleteCourseModal" tabindex="-1" aria-labelledby="deleteCourseModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-scrollable ">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title" id="deleteCourseModalLabel">Confirm Deletion</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Are you sure you want to delete this course? This action cannot be undone.</p>
                                        <p class="mb-0"><strong>Course:</strong> <span id="courseToDelete"></span></p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="button" class="btn btn-danger" id="confirmDeleteCourse">Delete Course</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                            
                            document.addEventListener('DOMContentLoaded', function() {
                                const createButtons = document.querySelectorAll('#createNewCourseBtn, #createFirstCourseBtn');

                                // Centralized utility functions for overlay and notifications
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
                                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        ${message ? `<div class="fw-semibold fs-5 text-primary">${message}</div>` : ''}
                                    `;

                                    document.body.appendChild(overlay);
                                    return overlay;
                                }

                                function removeOverlay() {
                                    const overlay = document.querySelector('.custom-overlay');
                                    if (overlay) {
                                        overlay.remove();
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

                                function createNewCourse(e) {
                                    e.preventDefault();

                                    // Create loading overlay with message
                                    showOverlay('Preparing new course...');

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
                                    "order": [
                                        [4, "desc"]
                                    ], // Default sort by created date
                                    "responsive": false, // Disable responsive feature
                                    "columnDefs": [{
                                            "orderable": false,
                                            "targets": [5]
                                        } // Disable sorting for action column
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
                                
                                // Course deletion functionality
                                let courseIdToDelete = null;

                                function showDeleteCourseModal(courseId, courseTitle) {
                                    courseIdToDelete = courseId;
                                    document.getElementById('courseToDelete').textContent = courseTitle || 'This course';
                                    const deleteModal = new bootstrap.Modal(document.getElementById('deleteCourseModal'));
                                    deleteModal.show();
                                }

                                function deleteCourse(courseId) {
                                    showOverlay('Deleting course...');
                                    
                                    fetch(`../ajax/courses/delete_course.php`, {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-Requested-With': 'XMLHttpRequest'
                                        },
                                        body: JSON.stringify({ course_id: courseId })
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        removeOverlay();
                                        if (data.success) {
                                            // Show success notification
                                            showAlert('success', 'Course deleted successfully');
                                            
                                            // Remove the row from the DataTable
                                            const table = $('#courses-datatable').DataTable();
                                            table.row($(`tr[data-course-id="${courseId}"]`)).remove().draw();
                                        } else {
                                            showAlert('danger', data.message || 'Failed to delete course');
                                        }
                                    })
                                    .catch(error => {
                                        removeOverlay();
                                        console.error('Error:', error);
                                        showAlert('danger', 'An error occurred while deleting the course');
                                    });
                                }
                                
                                // Attach event handlers for delete functionality
                                $(document).on('click', '.delete-course', function(e) {
                                    e.preventDefault();
                                    const courseId = $(this).data('course-id');
                                    const courseTitle = $(this).data('course-title');
                                    showDeleteCourseModal(courseId, courseTitle);
                                });
                                
                                document.getElementById('confirmDeleteCourse').addEventListener('click', function() {
                                    if (courseIdToDelete) {
                                        deleteCourse(courseIdToDelete);
                                        // Close the modal
                                        const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteCourseModal'));
                                        deleteModal.hide();
                                    }
                                });
                            });
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
                <!-- Course Preview Modal -->
                <div id="coursePreviewModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="coursePreviewModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="coursePreviewModalLabel">Course Preview</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center" id="coursePreviewLoader">
                                    <div class="spinner-border text-primary m-2" role="status"></div>
                                    <p>Loading course details...</p>
                                </div>
                                <div id="coursePreviewContent" style="display: none;">
                                    <!-- Basic Info Section -->
                                    <div class="row">
                                        <div class="col-md-4">
                                            <img id="previewThumbnail" src="" alt="Course Thumbnail" class="img-fluid rounded">
                                        </div>
                                        <div class="col-md-8">
                                            <h4 id="previewTitle" class="mt-0"></h4>
                                            <p id="previewShortDescription" class="text-muted"></p>
                                            <div class="badge bg-primary me-1" id="previewLevel"></div>
                                            <div class="badge bg-info me-1" id="previewCategory"></div>
                                            <div class="badge bg-success me-1" id="previewPrice"></div>
                                        </div>
                                    </div>

                                    <!-- Full Description Section -->
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <h5>Full Description</h5>
                                            <div id="previewFullDescription" class="border rounded p-3 bg-light"></div>
                                        </div>
                                    </div>

                                    <!-- Learning Outcomes Section -->
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <h5>Learning Outcomes</h5>
                                            <ul id="previewLearningOutcomes" class="list-group">
                                                <!-- Learning outcomes will be inserted here -->
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Requirements Section -->
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <h5>Requirements</h5>
                                            <ul id="previewRequirements" class="list-group">
                                                <!-- Requirements will be inserted here -->
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Course Structure Section -->
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <h5>Course Structure</h5>
                                            <div id="previewSectionsAccordion" class="accordion">
                                                <!-- Sections and topics will be inserted here -->
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Statistics Section -->
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <h5>Course Statistics</h5>
                                            <ul class="list-unstyled">
                                                <li><i class="mdi mdi-book-open-variant me-1"></i> <span id="previewSections">0 Sections</span></li>
                                                <li><i class="mdi mdi-bookmark-outline me-1"></i> <span id="previewTopics">0 Topics</span></li>
                                                <li><i class="mdi mdi-help-circle-outline me-1"></i> <span id="previewQuizzes">0 Quizzes</span></li>
                                                <li><i class="mdi mdi-calendar me-1"></i> <span id="previewCreated">Created on: </span></li>
                                                <li><i class="mdi mdi-update me-1"></i> <span id="previewUpdated">Last updated: </span></li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h5>Status</h5>
                                            <div id="previewStatusBadge" class="badge bg-secondary mb-1">Draft</div>
                                            <p id="previewStatusMessage" class="text-muted small"></p>
                                            <div id="previewCompletionStatus" class="mt-2">
                                                <h6>Completion Status</h6>
                                                <div class="progress">
                                                    <div id="previewCompletionBar" class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <a href="#" id="previewEditButton" class="btn btn-primary">Edit Course</a>
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

    <!-- Custom script for course preview modal -->
    <script>
        // Course preview modal functionality
        document.addEventListener('DOMContentLoaded', function() {
            const previewModal = new bootstrap.Modal(document.getElementById('coursePreviewModal'));

            // Attach click event to course rows
            $(document).on('click', '.course-row', function(e) {
                // Don't open preview if clicking on action buttons
                if (e.target.closest('.table-action') || e.target.closest('.action-icon')) {
                    return;
                }

                const courseId = $(this).data('course-id');
                openCoursePreview(courseId);
            });

            function openCoursePreview(courseId) {
                // Show loader, hide content
                $('#coursePreviewLoader').show();
                $('#coursePreviewContent').hide();

                // Open modal
                previewModal.show();

                // Fetch course details via AJAX
                fetch(`../ajax/courses/get_course_details.php?course_id=${courseId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            populateCoursePreview(data.course);
                        } else {
                            showPreviewError(data.message || 'Failed to load course details');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showPreviewError('An error occurred while loading course details');
                    });
            }

            function populateCoursePreview(course) {
                // Update Edit Course button URL
                $('#previewEditButton').attr('href', `course-creator.php?course_id=${course.course_id}`);

                // Set thumbnail - use icon placeholder if no image available
                if (course.thumbnail) {
                    // Show actual thumbnail image
                    $('#previewThumbnail').replaceWith(
                        `<img id="previewThumbnail" src="../uploads/thumbnails/${course.thumbnail}" 
             alt="Course Thumbnail" class="img-fluid rounded">`
                    );
                } else {
                    // Show icon placeholder instead of image
                    $('#previewThumbnail').replaceWith(
                        `<div id="previewThumbnail" class="bg-light rounded d-flex align-items-center justify-content-center" 
             style="height: 200px; width: 100%;">
            <i class="mdi mdi-book-open-page-variant text-muted" style="font-size: 48px;"></i>
         </div>`
                    );
                }

                // Set basic info
                $('#previewTitle').text(course.title);
                $('#previewShortDescription').text(course.short_description);
                $('#previewLevel').text(course.course_level);
                $('#previewCategory').text(course.subcategory_name || 'Uncategorized');

                // Set price
                const priceDisplay = parseFloat(course.price) > 0 ?
                    "$" + parseFloat(course.price).toFixed(2) :
                    'Free';
                $('#previewPrice').text(priceDisplay);

                // Set full description (truncate if too long)
                const fullDescription = course.full_description || 'No description provided';
                const maxLength = 500;
                let displayDescription = fullDescription;

                if (fullDescription.length > maxLength) {
                    displayDescription = fullDescription.substring(0, maxLength) + '... <a href="#" class="show-more">Show more</a>';
                }

                $('#previewFullDescription').html(displayDescription);

                // Attach show more handler
                $('#previewFullDescription').on('click', '.show-more', function(e) {
                    e.preventDefault();
                    $('#previewFullDescription').html(fullDescription);
                });

                // Set learning outcomes
                const $outcomesList = $('#previewLearningOutcomes');
                $outcomesList.empty();

                if (course.learning_outcomes && course.learning_outcomes.length > 0) {
                    course.learning_outcomes.forEach(outcome => {
                        $outcomesList.append(`<li class="list-group-item"><i class="mdi mdi-check-circle text-success me-2"></i>${outcome}</li>`);
                    });
                } else {
                    $outcomesList.append('<li class="list-group-item text-muted">No learning outcomes defined</li>');
                }

                // Set requirements
                const $requirementsList = $('#previewRequirements');
                $requirementsList.empty();

                if (course.requirements && course.requirements.length > 0) {
                    course.requirements.forEach(requirement => {
                        $requirementsList.append(`<li class="list-group-item"><i class="mdi mdi-arrow-right-bold text-primary me-2"></i>${requirement}</li>`);
                    });
                } else {
                    $requirementsList.append('<li class="list-group-item text-muted">No prerequisites defined</li>');
                }

                // Set course structure (sections and topics)
                const $accordionContainer = $('#previewSectionsAccordion');
                $accordionContainer.empty();

                if (course.sections && course.sections.length > 0) {
                    course.sections.forEach((section, index) => {
                        const sectionId = `section-${section.section_id}`;
                        const isFirstSection = index === 0;

                        let accordionItem = `
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading-${sectionId}">
                        <button class="accordion-button ${isFirstSection ? '' : 'collapsed'}" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#collapse-${sectionId}"
                                aria-expanded="${isFirstSection ? 'true' : 'false'}" aria-controls="collapse-${sectionId}">
                            ${section.section_title}
                        </button>
                    </h2>
                    <div id="collapse-${sectionId}" class="accordion-collapse collapse ${isFirstSection ? 'show' : ''}"
                         aria-labelledby="heading-${sectionId}" data-bs-parent="#previewSectionsAccordion">
                        <div class="accordion-body">
                            <h6 class="mb-2">Topics</h6>
                            <ul class="list-group mb-3">
            `;

                        if (section.topics && section.topics.length > 0) {
                            section.topics.forEach(topic => {
                                accordionItem += `
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="mdi mdi-file-document-outline me-2"></i>${topic.title}</span>
                        </li>
                    `;
                            });
                        } else {
                            accordionItem += `<li class="list-group-item text-muted">No topics in this section</li>`;
                        }

                        accordionItem += `</ul>`;

                        // Add quizzes if any
                        if (section.quizzes && section.quizzes.length > 0) {
                            accordionItem += `<h6 class="mb-2">Quizzes</h6><ul class="list-group">`;

                            section.quizzes.forEach(quiz => {
                                accordionItem += `
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="mdi mdi-help-circle-outline me-2"></i>${quiz.quiz_title}</span>
                            <span class="badge bg-info">Pass: ${quiz.pass_mark}%</span>
                        </li>
                    `;
                            });

                            accordionItem += `</ul>`;
                        }

                        accordionItem += `
                        </div>
                    </div>
                </div>
            `;

                        $accordionContainer.append(accordionItem);
                    });
                } else {
                    $accordionContainer.append('<div class="alert alert-info">No sections have been created for this course yet.</div>');
                }

                // Set statistics
                $('#previewSections').text(`${course.section_count} ${course.section_count === 1 ? 'Section' : 'Sections'}`);
                $('#previewTopics').text(`${course.topic_count} ${course.topic_count === 1 ? 'Topic' : 'Topics'}`);
                $('#previewQuizzes').text(`${course.quiz_count} ${course.quiz_count === 1 ? 'Quiz' : 'Quizzes'}`);
                $('#previewCreated').text(`Created on: ${new Date(course.created_at).toLocaleDateString()}`);
                $('#previewUpdated').text(`Last updated: ${new Date(course.updated_at).toLocaleDateString()}`);

                // Set status badge
                const statusBadge = $('#previewStatusBadge');
                statusBadge.removeClass('bg-secondary bg-success bg-warning');
                statusBadge.text(course.status);

                let statusClass = 'bg-secondary';
                let statusMessage = '';

                switch (course.status) {
                    case 'Draft':
                        statusClass = 'bg-secondary';
                        statusMessage = 'This course is still in draft mode and not visible to students.';
                        break;
                    case 'Published':
                        statusClass = 'bg-success';
                        statusMessage = 'This course is published and available to students.';
                        break;
                    case 'Pending':
                        statusClass = 'bg-warning';
                        statusMessage = 'This course is pending approval from administrators.';
                        break;
                }

                statusBadge.addClass(statusClass);
                $('#previewStatusMessage').text(statusMessage);

                // Set completion progress
                const completionPercentage = Math.round(course.completion_percentage);
                $('#previewCompletionBar')
                    .css('width', `${completionPercentage}%`)
                    .attr('aria-valuenow', completionPercentage)
                    .text(`${completionPercentage}%`);

                // Determine progress bar color based on completion
                const $progressBar = $('#previewCompletionBar');
                $progressBar.removeClass('bg-danger bg-warning bg-info bg-success');

                if (completionPercentage < 25) {
                    $progressBar.addClass('bg-danger');
                } else if (completionPercentage < 50) {
                    $progressBar.addClass('bg-warning');
                } else if (completionPercentage < 75) {
                    $progressBar.addClass('bg-info');
                } else {
                    $progressBar.addClass('bg-success');
                }

                // Hide loader, show content
                $('#coursePreviewLoader').hide();
                $('#coursePreviewContent').show();
            }

            function showPreviewError(message) {
                // Create error display in the modal
                $('#coursePreviewLoader').hide();
                $('#coursePreviewContent').html(`
                    <div class="alert alert-danger">
                        <i class="mdi mdi-alert-circle-outline me-2"></i>
                        ${message}
                    </div>
                `).show();
            }
        });
    </script>
</body>
</html>