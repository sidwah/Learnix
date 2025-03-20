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
                        <div class="col-sm-4">
                            <a href="create-course.php" class="btn btn-success rounded-pill mb-3">
                                <i class="mdi mdi-plus"></i> Create Course
                            </a>
                        </div>
                        <div class="col-sm-8">
                            <div class="text-sm-end">
                                <div class="btn-group mb-3">
                                    <button type="button" class="btn btn-light"><a href="courses.php" >All Courses</a></button>
                                </div>
                                <div class="btn-group mb-3 ms-1">
                                    <button type="button" class="btn btn-light"><a href="courses-publish.php">Published</a></button>
                                    <button type="button" class="btn btn-primary"><a href="courses-draft.php" class="text-white">Draft</a></button>
                                </div>
                                <div class="btn-group mb-3 ms-2 d-none d-sm-inline-block">
                                    <button type="button" class="btn btn-secondary"><i class="dripicons-view-apps"></i></button>
                                </div>

                            </div>
                        </div><!-- end col-->
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
            WHERE c.instructor_id = ? AND c.status='Draft' ORDER BY c.updated_at DESC";
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
                                cursor: pointer;
                                transform: scale(1.03);
                                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
                            }

                            .course-thumbnail {
                                height: 200px;
                                object-fit: cover;
                                object-position: center;
                            }
                        </style>
                        <div class="container-fluid">
                        <div class="row mb-3">
                                <div class="col-md-6">
                                    <input type="text" id="courseSearch" class="form-control" placeholder="Search courses by name...">
                                </div>
                            </div>
                            <script>
                                document.getElementById('courseSearch').addEventListener('keyup', function() {
                                    let filter = this.value.toLowerCase();
                                    let courses = document.querySelectorAll('.course-card');

                                    courses.forEach(course => {
                                        let title = course.querySelector('.text-title').textContent.toLowerCase();
                                        if (title.includes(filter)) {
                                            course.parentElement.style.display = 'block';
                                        } else {
                                            course.parentElement.style.display = 'none';
                                        }
                                    });
                                });
                            </script>
                            <div class="row">
                                <?php
                                if ($result->num_rows > 0) {
                                    while ($course = $result->fetch_assoc()) {
                                        // Get the properly formatted image path
                                        $image_path = displayCourseImage($course['thumbnail']);
                                ?>
                                        <div class="col-md-6 col-xxl-3">
                                            <div class="card d-block course-card" data-bs-toggle="modal" data-bs-backdrop="static" data-bs-target="#courseEditModal"
                                                data-course-id="<?php echo $course['course_id']; ?>"
                                                data-course-title="<?php echo htmlspecialchars($course['title']); ?>"
                                                data-course-description="<?php echo htmlspecialchars($course['short_description']); ?>"
                                                data-course-price="<?php echo $course['price']; ?>"
                                                data-course-level="<?php echo htmlspecialchars($course['course_level']); ?>"
                                                data-course-status="<?php echo htmlspecialchars($course['status']); ?>"
                                                data-course-thumbnail="<?php echo htmlspecialchars($course['thumbnail']); ?>">

                                                <img class="card-img-top course-thumbnail" src="<?php echo htmlspecialchars($image_path); ?>" alt="Course thumbnail for <?php echo htmlspecialchars($course['title']); ?>">

                                                <div class="card-img-overlay">
                                                    <div class="badge <?php echo $course['status'] == 'Published' ? 'bg-success' : 'bg-secondary text-light'; ?> p-1">
                                                        <?php echo htmlspecialchars($course['status']); ?>
                                                    </div>
                                                </div>

                                                <div class="card-body position-relative">
                                                    <h4 class="mt-0">
                                                        <span class="text-title">
                                                            <?php echo htmlspecialchars($course['title']); ?>
                                                        </span>
                                                    </h4>

                                                    <p class="mb-3">
                                                        <span class="pe-2 text-nowrap">
                                                            <i class="mdi mdi-format-list-bulleted-type"></i>
                                                            <b><?php echo $course['section_count']; ?></b> <small>Sections</small>
                                                        </span>
                                                        <span class="text-nowrap">
                                                            <i class="mdi mdi-comment-multiple-outline"></i>
                                                            <b>0</b> <small>Comments</small>
                                                        </span>
                                                    </p>

                                                    <div class="mb-3">
                                                        <span class="text-muted space-between">
                                                            Price: $<?php echo number_format($course['price'], 2); ?> <br>
                                                            Level: <?php echo htmlspecialchars($course['course_level']); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                <?php
                                    }
                                } else {
                                    echo "<div class='col-12 text-center'><p>No courses found.</p></div>";
                                }
                                ?>
                            </div>

                            <!-- Course Edit Modal (Previous modal code remains the same) -->
                            <div class="modal fade" id="courseEditModal" tabindex="-1" aria-labelledby="courseEditModalLabel" data-bs-backdrop="static" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="courseEditModalLabel">Edit Course</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Current Thumbnail</label>
                                                    <img id="modalCourseThumbnail" class="img-fluid" src="" alt="Course Thumbnail">
                                                </div>
                                                <div class="col-md-8">
                                                    <form id="courseEditForm">
                                                        <input type="hidden" id="modalCourseId" name="course_id">

                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label for="modalCourseTitle" class="form-label">Course Title</label>
                                                                <input type="text" class="form-control" id="modalCourseTitle" name="title">
                                                            </div>

                                                            <div class="col-md-6 mb-3">
                                                                <label for="modalCourseStatus" class="form-label">Status</label>
                                                                <select class="form-select" id="modalCourseStatus" name="status">
                                                                    <option value="Draft">Draft</option>
                                                                    <option value="Published">Published</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="modalCourseDescription" class="form-label">Short Description</label>
                                                            <textarea class="form-control" id="modalCourseDescription" name="short_description" rows="3"></textarea>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label for="modalCoursePrice" class="form-label">Price</label>
                                                                <input type="number" step="0.01" class="form-control" id="modalCoursePrice" name="price">
                                                            </div>

                                                            <div class="col-md-6 mb-3">
                                                                <label for="modalCourseLevel" class="form-label">Course Level</label>
                                                                <select class="form-select" id="modalCourseLevel" name="course_level">
                                                                    <option value="Beginner">Beginner</option>
                                                                    <option value="Intermediate">Intermediate</option>
                                                                    <option value="Advanced">Advanced</option>
                                                                    <option value="All Levels">All Levels</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="button" class="btn btn-primary" id="saveCourseChanges">Save Changes</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script> -->
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                // Add click event to all course cards
                                const courseCards = document.querySelectorAll('.course-card');
                                const courseEditModal = document.getElementById('courseEditModal');

                                courseCards.forEach(card => {
                                    card.addEventListener('click', function() {
                                        // Function to get the correct image path
                                        function getImagePath(thumbnail) {
                                            const uploadPaths = [
                                                '../uploads/thumbnails/',
                                                '../uploads/',
                                                'uploads/thumbnails/',
                                                'uploads/'
                                            ];

                                            for (let path of uploadPaths) {
                                                let fullPath = path + thumbnail;
                                                // You might want to use fetch or another method to check if the image exists
                                                // For now, we'll assume the path is correct
                                                return fullPath;
                                            }

                                            // Fallback to default image
                                            return '../assets/images/default-course.jpg';
                                        }

                                        // Populate modal with course data
                                        document.getElementById('modalCourseId').value = this.dataset.courseId;
                                        document.getElementById('modalCourseTitle').value = this.dataset.courseTitle;
                                        document.getElementById('modalCourseDescription').value = this.dataset.courseDescription;
                                        document.getElementById('modalCoursePrice').value = this.dataset.coursePrice;
                                        document.getElementById('modalCourseLevel').value = this.dataset.courseLevel;
                                        document.getElementById('modalCourseStatus').value = this.dataset.courseStatus;

                                        // Set thumbnail image
                                        const thumbnailImg = document.getElementById('modalCourseThumbnail');
                                        thumbnailImg.src = getImagePath(this.dataset.courseThumbnail);
                                        thumbnailImg.alt = `Thumbnail for ${this.dataset.courseTitle}`;
                                    });
                                });

                                // Placeholder for save changes button
                                document.getElementById('saveCourseChanges').addEventListener('click', function() {
                                    // You can implement the save logic here later
                                    console.log('Save changes clicked');
                                    // Typically, you'd send an AJAX request to update the course
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


</html>