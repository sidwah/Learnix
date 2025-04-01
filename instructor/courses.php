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
(SELECT COUNT(*) FROM course_sections cs WHERE cs.course_id = c.course_id) AS section_count
FROM courses c
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
                        <style>
                            .course-card {
                                transition: transform 0.3s ease, box-shadow 0.3s ease;
                            }

                            .course-card:hover {
                                transform: scale(1.02);
                                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
                            }

                            .course-thumbnail {
                                height: 200px;
                                object-fit: cover;
                                object-position: center;
                            }

                            .action-buttons .btn {
                                transition: all 0.2s ease;
                            }

                            .action-buttons .btn:hover {
                                transform: translateY(-2px);
                            }

                            .course-title {
                                min-height: 48px;
                                display: -webkit-box;
                                /* -webkit-line-clamp: 2; */
                                -webkit-box-orient: vertical;
                                overflow: hidden;
                            }

                            /* For select2 in modal */
                            .select2-container {
                                z-index: 9999;
                            }
                        </style>
                        <div class="container-fluid">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <input type="text" id="courseSearch" class="form-control" placeholder="Search courses by name...">
                                </div>
                            </div>

                            <!-- Courses Section -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="page-title-box">
                                        <div class="page-title-right">
                                            <button id="createNewCourseBtn" class="btn btn-primary">
                                                <i class="mdi mdi-plus-circle me-1"></i> Create New Course
                                            </button>
                                        </div>
                                        <h4 class="page-title">My Courses</h4>
                                    </div>
                                </div>
                            </div>

                            <!-- Existing Courses Grid -->
                            <div class="row">
                                <?php
                                // Fetch instructor's courses
                                $courses_query = $conn->prepare("
        SELECT 
            c.course_id, 
            c.title, 
            c.short_description, 
            c.thumbnail, 
            c.status, 
            c.created_at,
            s.name AS subcategory_name
        FROM courses c
        LEFT JOIN subcategories s ON c.subcategory_id = s.subcategory_id
        WHERE c.instructor_id = ?
        ORDER BY c.created_at DESC
    ");
                                $courses_query->bind_param("i", $_SESSION['instructor_id']);
                                $courses_query->execute();
                                $courses_result = $courses_query->get_result();

                                // Display courses
                                while ($course = $courses_result->fetch_assoc()) {
                                ?>
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-img-top position-relative">
                                                <?php if (!empty($course['thumbnail'])): ?>
                                                    <img src="../uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>"
                                                        class="img-fluid" alt="Course Thumbnail">
                                                <?php else: ?>
                                                    <div class="bg-light text-center py-5">
                                                        <i class="mdi mdi-image-off-outline display-4 text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <span class="badge bg-<?php
                                                                        echo match ($course['status']) {
                                                                            'Draft' => 'secondary',
                                                                            'Published' => 'success',
                                                                            'Pending' => 'warning',
                                                                            default => 'info'
                                                                        };
                                                                        ?> position-absolute top-0 end-0 m-2">
                                                    <?php echo htmlspecialchars($course['status']); ?>
                                                </span>
                                            </div>
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                                                <p class="card-text text-muted">
                                                    <?php echo htmlspecialchars($course['short_description']); ?>
                                                </p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars($course['subcategory_name'] ?: 'Uncategorized'); ?>
                                                    </small>
                                                    <div class="btn-group">
                                                        <a href="course-creator.php?course_id=<?php echo $course['course_id']; ?>"
                                                            class="btn btn-sm btn-outline-primary">
                                                            <i class="mdi mdi-pencil me-1"></i> Edit
                                                        </a>
                                                        <?php if ($course['status'] === 'Draft'): ?>
                                                            <button class="btn btn-sm btn-outline-success"
                                                                onclick="prepareCourseForPublication(<?php echo $course['course_id']; ?>)">
                                                                <i class="mdi mdi-publish me-1"></i> Publish
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-footer text-muted">
                                                <small>Created: <?php echo date('M d, Y', strtotime($course['created_at'])); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                }
                                $courses_query->close();
                                ?>
                            </div>

                            <!-- No Courses Placeholder -->
                            <?php if ($courses_result->num_rows === 0): ?>
                                <div class="text-center py-5">
                                    <i class="mdi mdi-book-multiple-outline display-4 text-muted mb-3"></i>
                                    <h4>No Courses Yet</h4>
                                    <p class="text-muted">Start creating courses to share your knowledge!</p>
                                    <button id="createFirstCourseBtn" class="btn btn-primary mt-2">
                                        <i class="mdi mdi-plus-circle me-1"></i> Create Your First Course
                                    </button>
                                </div>
                            <?php endif; ?>

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
    <script src="assets/js/vendor/apexcharts.min.js"></script>
    <script src="assets/js/vendor/jquery-jvectormap-1.2.2.min.js"></script>
    <script src="assets/js/vendor/jquery-jvectormap-world-mill-en.js"></script>
    <!-- third party js ends -->

    <!-- demo app -->
    <script src="assets/js/pages/demo.dashboard.js"></script>
    <!-- end demo js-->
</body>

<!-- Mirrored from coderthemes.com/hyper/saas/index.php by HTTrack Website Copier/3.x [XR&CO'2014], Fri, 29 Jul 2022 10:20:07 GMT -->

</html>