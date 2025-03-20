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
                                    <button type="button" class="btn btn-primary"><a href="courses.php" class="text-white">All Courses</a></button>
                                </div>
                                <div class="btn-group mb-3 ms-1">
                                    <button type="button" class="btn btn-light"><a href="courses-publish.php">Published</a></button>
                                    <button type="button" class="btn btn-light"><a href="courses-draft.php">Draft</a></button>
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

                            <div class="row">
                                <?php
                                if ($result->num_rows > 0) {
                                    while ($course = $result->fetch_assoc()) {
                                        // Get the properly formatted image path
                                        $image_path = displayCourseImage($course['thumbnail']);
                                ?>
                                        <div class="col-md-6 col-xxl-3 mb-4 course-item">
                                            <div class="card d-block course-card h-100">
                                                <img class="card-img-top course-thumbnail" src="<?php echo htmlspecialchars($image_path); ?>" alt="Course thumbnail for <?php echo htmlspecialchars($course['title']); ?>">
                                                <div class="card-img-overlay">
                                                    <div class="badge <?php echo $course['status'] == 'Published' ? 'bg-success' : 'bg-secondary text-light'; ?> p-1">
                                                        <?php echo htmlspecialchars($course['status']); ?>
                                                    </div>
                                                </div>
                                                <div class="card-body position-relative d-flex flex-column">
                                                    <h4 class="mt-0 course-title">
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
                                                        <span class="text-muted">
                                                            <strong>Price:</strong> $<?php echo number_format($course['price'], 2); ?> <br>
                                                            <strong>Level:</strong> <?php echo htmlspecialchars($course['course_level']); ?>
                                                        </span>
                                                    </div>

                                                    <div class="d-flex justify-content-between mt-auto action-buttons">
                                                        <button class="btn btn-sm btn-outline-primary edit-course"
                                                            data-course-id="<?php echo $course['course_id']; ?>"
                                                            data-course-title="<?php echo htmlspecialchars($course['title']); ?>"
                                                            data-course-description="<?php echo htmlspecialchars($course['short_description'] ?? ''); ?>"
                                                            data-course-full-description="<?php echo htmlspecialchars($course['full_description'] ?? ''); ?>"
                                                            data-course-price="<?php echo $course['price']; ?>"
                                                            data-course-level="<?php echo htmlspecialchars($course['course_level']); ?>"
                                                            data-course-status="<?php echo htmlspecialchars($course['status']); ?>"
                                                            data-course-thumbnail="<?php echo htmlspecialchars($course['thumbnail']); ?>"
                                                            data-course-subcategory="<?php echo $course['subcategory_id']; ?>"
                                                            data-course-access-level="<?php echo htmlspecialchars($course['access_level'] ?? 'Public'); ?>"
                                                            data-course-certificate="<?php echo $course['certificate_enabled'] ? '1' : '0'; ?>"
                                                            data-course-created="<?php echo htmlspecialchars($course['created_at']); ?>"
                                                            data-course-updated="<?php echo htmlspecialchars($course['updated_at']); ?>"
                                                            data-course-approval="<?php echo htmlspecialchars($course['approval_status'] ?? 'Pending'); ?>">
                                                            <i class="mdi mdi-pencil"></i> Edit
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger delete-course"
                                                            data-course-id="<?php echo $course['course_id']; ?>"
                                                            data-course-title="<?php echo htmlspecialchars($course['title']); ?>">
                                                            <i class="mdi mdi-delete"></i> Delete
                                                        </button>
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

                            <!-- Course Edit Modal -->
                            <div class="modal fade" id="courseEditModal" tabindex="-1" aria-labelledby="courseEditModalLabel" data-bs-backdrop="static" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-xl">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title" id="courseEditModalLabel">Edit Course</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="courseEditForm">
                                                <input type="hidden" id="modalCourseId" name="course_id">
                                                <input type="hidden" id="modalCourseCategory" name="category_id">

                                                <ul class="nav nav-tabs mb-4" id="courseEditTabs" role="tablist">
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link active" id="basic-info-tab" data-bs-toggle="tab" data-bs-target="#basic-info-content" type="button" role="tab" aria-controls="basic-info-content" aria-selected="true">Basic Info</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link" id="details-tab" data-bs-toggle="tab" data-bs-target="#details-content" type="button" role="tab" aria-controls="details-content" aria-selected="false">Course Details</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link" id="requirements-tab" data-bs-toggle="tab" data-bs-target="#requirements-content" type="button" role="tab" aria-controls="requirements-content" aria-selected="false">Requirements & Outcomes</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link" id="sections-tab" data-bs-toggle="tab" data-bs-target="#sections-content" type="button" role="tab" aria-controls="sections-content" aria-selected="false">Sections</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link" id="tags-tab" data-bs-toggle="tab" data-bs-target="#tags-content" type="button" role="tab" aria-controls="tags-content" aria-selected="false">Tags</button>
                                                    </li>
                                                </ul>

                                                <div class="tab-content" id="courseEditTabContent">
                                                    <!-- Basic Info Tab -->
                                                    <div class="tab-pane fade show active" id="basic-info-content" role="tabpanel" aria-labelledby="basic-info-tab">
                                                        <div class="row">
                                                            <div class="col-md-4 mb-3">
                                                                <label class="form-label">Current Thumbnail</label>
                                                                <img id="modalCourseThumbnail" class="img-fluid rounded shadow-sm" src="" alt="Course Thumbnail">
                                                                <div class="mt-3">
                                                                    <label for="courseThumbnailUpload" class="form-label">Upload New Thumbnail</label>
                                                                    <input type="file" class="form-control" id="courseThumbnailUpload" name="thumbnail" accept="image/*">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-8">
                                                                <div class="row">
                                                                    <div class="col-md-8 mb-3">
                                                                        <label for="modalCourseTitle" class="form-label">Course Title</label>
                                                                        <input type="text" class="form-control" id="modalCourseTitle" name="title" required>
                                                                    </div>
                                                                    <div class="col-md-4 mb-3">
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
                                                                    <div class="col-md-4 mb-3">
                                                                        <label for="modalCoursePrice" class="form-label">Price</label>
                                                                        <div class="input-group">
                                                                            <span class="input-group-text">$</span>
                                                                            <input type="number" step="0.01" class="form-control" id="modalCoursePrice" name="price" min="0">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4 mb-3">
                                                                        <label for="modalCourseLevel" class="form-label">Course Level</label>
                                                                        <select class="form-select" id="modalCourseLevel" name="course_level">
                                                                            <option value="Beginner">Beginner</option>
                                                                            <option value="Intermediate">Intermediate</option>
                                                                            <option value="Advanced">Advanced</option>
                                                                            <option value="All Levels">All Levels</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-4 mb-3">
                                                                        <label for="modalCourseAccessLevel" class="form-label">Access Level</label>
                                                                        <select class="form-select" id="modalCourseAccessLevel" name="access_level">
                                                                            <option value="Public">Public</option>
                                                                            <option value="Restricted">Restricted</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="form-check form-switch mb-3">
                                                                    <input class="form-check-input" type="checkbox" id="modalCourseCertificateEnabled" name="certificate_enabled">
                                                                    <label class="form-check-label" for="modalCourseCertificateEnabled">Enable Certificate</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Course Details Tab -->
                                                    <div class="tab-pane fade" id="details-content" role="tabpanel" aria-labelledby="details-tab">
                                                        <div class="mb-3">
                                                            <label for="modalCourseFullDescription" class="form-label">Full Description</label>
                                                            <textarea class="form-control" id="modalCourseFullDescription" name="full_description" rows="8"></textarea>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label for="modalCourseSubcategory" class="form-label">Subcategory</label>
                                                                <select class="form-select" id="modalCourseSubcategory" name="subcategory_id" required>
                                                                    <option value="" disabled selected>Select Subcategory</option>
                                                                    <!-- Subcategories will be loaded via AJAX -->
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Approval Status</label>
                                                                <input type="text" class="form-control" id="modalCourseApprovalStatus" readonly>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Created At</label>
                                                                <input type="text" class="form-control" id="modalCourseCreatedAt" readonly>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Last Updated</label>
                                                                <input type="text" class="form-control" id="modalCourseUpdatedAt" readonly>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Requirements & Outcomes Tab -->
                                                    <div class="tab-pane fade" id="requirements-content" role="tabpanel" aria-labelledby="requirements-tab">
                                                        <div class="mb-4">
                                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                                <h5>Course Requirements</h5>
                                                                <button type="button" class="btn btn-sm btn-primary" id="addRequirementBtn">Add Requirement</button>
                                                            </div>
                                                            <div id="requirementsContainer">
                                                                <!-- Requirements will be added dynamically -->
                                                            </div>
                                                        </div>
                                                        <hr>
                                                        <div class="mt-4">
                                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                                <h5>Learning Outcomes</h5>
                                                                <button type="button" class="btn btn-sm btn-primary" id="addOutcomeBtn">Add Outcome</button>
                                                            </div>
                                                            <div id="outcomesContainer">
                                                                <!-- Outcomes will be added dynamically -->
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Sections Tab -->
                                                    <div class="tab-pane fade" id="sections-content" role="tabpanel" aria-labelledby="sections-tab">
                                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                                            <h5>Course Sections</h5>
                                                            <button type="button" class="btn btn-sm btn-primary" id="manageSectionsBtn">Manage Sections</button>
                                                        </div>
                                                        <div class="table-responsive">
                                                            <table class="table table-hover">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Title</th>
                                                                        <th>Position</th>
                                                                        <th>Topics</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="sectionsTableBody">
                                                                    <!-- Sections will be added dynamically -->
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        <div class="alert alert-info mt-3">
                                                            <i class="mdi mdi-information-outline"></i> To manage section content and topics, click on "Manage Sections" button.
                                                        </div>
                                                    </div>

                                                    <!-- Tags Tab -->
                                                    <div class="tab-pane fade" id="tags-content" role="tabpanel" aria-labelledby="tags-tab">
                                                        <div class="mb-3">
                                                            <label class="form-label">Course Tags</label>
                                                            <select class="form-select" id="courseTagsSelect" multiple name="tags[]">
                                                                <!-- Tags will be loaded via AJAX -->
                                                            </select>
                                                            <div class="form-text">Select multiple tags that best describe your course (up to 5).</div>
                                                        </div>
                                                        <div id="selectedTagsContainer" class="mt-3">
                                                            <!-- Selected tags will appear here -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary" id="publishCourse">Publish</button>
                                            <button type="button" class="btn btn-success" id="saveCourseChanges">Save Changes</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete Confirmation Modal -->
                            <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">Confirm Deletion</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete the course: <strong id="deleteCourseName"></strong>?</p>
                                            <p class="text-danger"><i class="mdi mdi-alert"></i> This action cannot be undone!</p>
                                            <input type="hidden" id="deleteCourseId">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="button" class="btn btn-danger" id="confirmDelete">Delete Course</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                            // Publish button click handler
                            document.addEventListener('DOMContentLoaded', function() {
                                document.getElementById('publishCourse').addEventListener('click', function() {
                                    // Set status to Published
                                    document.getElementById('modalCourseStatus').value = 'Published';

                                    // Then trigger save
                                    document.getElementById('saveCourseChanges').click();
                                });

                                // Search functionality
                                document.getElementById('courseSearch').addEventListener('keyup', function() {
                                    let filter = this.value.toLowerCase();
                                    let courseItems = document.querySelectorAll('.course-item');

                                    courseItems.forEach(item => {
                                        let title = item.querySelector('.text-title').textContent.toLowerCase();
                                        if (title.includes(filter)) {
                                            item.style.display = 'block';
                                        } else {
                                            item.style.display = 'none';
                                        }
                                    });
                                });

                                // Edit course button click event
                                const editButtons = document.querySelectorAll('.edit-course');
                                const courseEditModal = new bootstrap.Modal(document.getElementById('courseEditModal'));

                                // Load subcategories with category mapping
                                const subcategoryMap = {}; // Will store subcategory to category mapping

                                // Function to load all subcategories
                                function loadAllSubcategories() {
                                    const subcategorySelect = document.getElementById('modalCourseSubcategory');
                                    subcategorySelect.innerHTML = '<option value="" disabled selected>Select Subcategory</option>';

                                    // AJAX call to fetch all subcategories
                                    fetch('../backend/courses/get_all_subcategories.php')
                                        .then(response => response.json())
                                        .then(subcategories => {
                                            if (subcategories && subcategories.length > 0) {
                                                // Group subcategories by category
                                                const categoryGroups = {};

                                                subcategories.forEach(subcategory => {
                                                    // Store the mapping for later use
                                                    subcategoryMap[subcategory.subcategory_id] = subcategory.category_id;

                                                    // Group by category
                                                    if (!categoryGroups[subcategory.category_name]) {
                                                        categoryGroups[subcategory.category_name] = [];
                                                    }
                                                    categoryGroups[subcategory.category_name].push(subcategory);
                                                });

                                                // Add subcategories grouped by category
                                                Object.keys(categoryGroups).sort().forEach(categoryName => {
                                                    // Create an optgroup
                                                    const optgroup = document.createElement('optgroup');
                                                    optgroup.label = categoryName;

                                                    // Add subcategories to the optgroup
                                                    categoryGroups[categoryName].forEach(subcategory => {
                                                        const option = document.createElement('option');
                                                        option.value = subcategory.subcategory_id;
                                                        option.textContent = subcategory.name;
                                                        optgroup.appendChild(option);
                                                    });

                                                    subcategorySelect.appendChild(optgroup);
                                                });
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Error fetching subcategories:', error);
                                            // Fallback data
                                            const fallbackData = [{
                                                    subcategory_id: 1,
                                                    name: 'Web Development',
                                                    category_id: 1,
                                                    category_name: 'Technology'
                                                },
                                                {
                                                    subcategory_id: 2,
                                                    name: 'Cybersecurity',
                                                    category_id: 1,
                                                    category_name: 'Technology'
                                                },
                                                {
                                                    subcategory_id: 3,
                                                    name: 'Artificial Intelligence',
                                                    category_id: 1,
                                                    category_name: 'Technology'
                                                },
                                                {
                                                    subcategory_id: 4,
                                                    name: 'Data Science',
                                                    category_id: 1,
                                                    category_name: 'Technology'
                                                },
                                                {
                                                    subcategory_id: 5,
                                                    name: 'Entrepreneurship',
                                                    category_id: 2,
                                                    category_name: 'Business'
                                                },
                                                {
                                                    subcategory_id: 6,
                                                    name: 'Project Management',
                                                    category_id: 2,
                                                    category_name: 'Business'
                                                },
                                                {
                                                    subcategory_id: 7,
                                                    name: 'E-Commerce',
                                                    category_id: 2,
                                                    category_name: 'Business'
                                                },
                                                {
                                                    subcategory_id: 8,
                                                    name: 'Nutrition',
                                                    category_id: 3,
                                                    category_name: 'Health'
                                                },
                                                {
                                                    subcategory_id: 9,
                                                    name: 'Mental Health',
                                                    category_id: 3,
                                                    category_name: 'Health'
                                                }
                                            ];

                                            // Group subcategories by category
                                            const categoryGroups = {};

                                            fallbackData.forEach(subcategory => {
                                                // Store the mapping for later use
                                                subcategoryMap[subcategory.subcategory_id] = subcategory.category_id;

                                                // Group by category
                                                if (!categoryGroups[subcategory.category_name]) {
                                                    categoryGroups[subcategory.category_name] = [];
                                                }
                                                categoryGroups[subcategory.category_name].push(subcategory);
                                            });

                                            // Add subcategories grouped by category
                                            Object.keys(categoryGroups).sort().forEach(categoryName => {
                                                // Create an optgroup
                                                const optgroup = document.createElement('optgroup');
                                                optgroup.label = categoryName;

                                                // Add subcategories to the optgroup
                                                categoryGroups[categoryName].forEach(subcategory => {
                                                    const option = document.createElement('option');
                                                    option.value = subcategory.subcategory_id;
                                                    option.textContent = subcategory.name;
                                                    optgroup.appendChild(option);
                                                });

                                                subcategorySelect.appendChild(optgroup);
                                            });
                                        });
                                }

                                // Event listener for subcategory change - update category
                                document.getElementById('modalCourseSubcategory').addEventListener('change', function() {
                                    const subcategoryId = parseInt(this.value);
                                    const categoryId = subcategoryMap[subcategoryId];

                                    if (categoryId) {
                                        document.getElementById('modalCourseCategory').value = categoryId;
                                    }
                                });

                                // Function to initialize requirements container
                                function initializeRequirements(courseId) {
                                    const container = document.getElementById('requirementsContainer');
                                    container.innerHTML = '';

                                    // AJAX call to fetch requirements
                                    fetch(`../backend/courses/get_course_requirements.php?course_id=${courseId}`)
                                        .then(response => response.json())
                                        .then(requirements => {
                                            if (requirements && requirements.length > 0) {
                                                requirements.forEach(req => {
                                                    addRequirementField(req.requirement_text, container);
                                                });
                                            } else {
                                                // Add an empty requirement field if none exist
                                                addRequirementField('', container);
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Error fetching requirements:', error);
                                            // Fallback for demonstration - add a placeholder requirement and some sample data
                                            addRequirementField('Basic knowledge of programming', container);
                                            addRequirementField('Computer with internet access', container);
                                        });
                                }

                                // Function to initialize learning outcomes container
                                function initializeOutcomes(courseId) {
                                    const container = document.getElementById('outcomesContainer');
                                    container.innerHTML = '';

                                    // AJAX call to fetch outcomes
                                    fetch(`../backend/courses/get_course_outcomes.php?course_id=${courseId}`)
                                        .then(response => response.json())
                                        .then(outcomes => {
                                            if (outcomes && outcomes.length > 0) {
                                                outcomes.forEach(outcome => {
                                                    addOutcomeField(outcome.outcome_text, container);
                                                });
                                            } else {
                                                // Add an empty outcome field if none exist
                                                addOutcomeField('', container);
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Error fetching outcomes:', error);
                                            // Fallback for demonstration - add a placeholder outcome and some sample data
                                            addOutcomeField('Build responsive websites', container);
                                            addOutcomeField('Understand core programming concepts', container);
                                        });
                                }

                                // Function to add a requirement field
                                function addRequirementField(value = '', container = null) {
                                    if (!container) container = document.getElementById('requirementsContainer');
                                    const index = container.children.length;

                                    const requirementGroup = document.createElement('div');
                                    requirementGroup.className = 'input-group mb-2 requirement-group';
                                    requirementGroup.innerHTML = `
            <input type="text" class="form-control" name="requirements[${index}]" value="${value}" placeholder="Enter a course requirement">
            <button class="btn btn-outline-danger remove-requirement" type="button">
                <i class="mdi mdi-delete"></i>
            </button>
        `;

                                    container.appendChild(requirementGroup);

                                    // Add event listener to remove button
                                    requirementGroup.querySelector('.remove-requirement').addEventListener('click', function() {
                                        requirementGroup.remove();
                                    });
                                }

                                // Function to add an outcome field
                                function addOutcomeField(value = '', container = null) {
                                    if (!container) container = document.getElementById('outcomesContainer');
                                    const index = container.children.length;

                                    const outcomeGroup = document.createElement('div');
                                    outcomeGroup.className = 'input-group mb-2 outcome-group';
                                    outcomeGroup.innerHTML = `
            <input type="text" class="form-control" name="outcomes[${index}]" value="${value}" placeholder="Enter a learning outcome">
            <button class="btn btn-outline-danger remove-outcome" type="button">
                <i class="mdi mdi-delete"></i>
            </button>
        `;

                                    container.appendChild(outcomeGroup);

                                    // Add event listener to remove button
                                    // Add event listener to remove button
                                    outcomeGroup.querySelector('.remove-outcome').addEventListener('click', function() {
                                        outcomeGroup.remove();
                                    });
                                }

                                // Add event listener to "Add Requirement" button
                                document.getElementById('addRequirementBtn').addEventListener('click', function() {
                                    addRequirementField();
                                });

                                // Add event listener to "Add Outcome" button
                                document.getElementById('addOutcomeBtn').addEventListener('click', function() {
                                    addOutcomeField();
                                });

                                // Function to initialize sections table
                                function initializeSectionsTable(courseId) {
                                    const tableBody = document.getElementById('sectionsTableBody');
                                    tableBody.innerHTML = '';

                                    // AJAX call to fetch sections
                                    fetch(`../backend/courses/get_course_sections.php?course_id=${courseId}`)
                                        .then(response => response.json())
                                        .then(sections => {
                                            if (sections && sections.length > 0) {
                                                sections.forEach(section => {
                                                    const row = document.createElement('tr');
                                                    row.innerHTML = `
                            <td>${section.title}</td>
                            <td>${section.position || 0}</td>
                            <td>${section.topic_count || 0} topic${section.topic_count !== 1 ? 's' : ''}</td>
                        `;
                                                    tableBody.appendChild(row);
                                                });
                                            } else {
                                                tableBody.innerHTML = `
                        <tr>
                            <td colspan="3" class="text-center">No sections found for this course.</td>
                        </tr>
                    `;
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Error fetching sections:', error);
                                            // Fallback for demonstration - add a placeholder row
                                            tableBody.innerHTML = `
                    <tr>
                        <td colspan="3" class="text-center">Sections will be displayed here.</td>
                    </tr>
                `;
                                        });
                                }

                                // Manage Sections button click handler
                                document.getElementById('manageSectionsBtn').addEventListener('click', function() {
                                    const courseId = document.getElementById('modalCourseId').value;
                                    if (!courseId) return;

                                    // You would typically redirect to a section management page
                                    // For now, just show a message
                                    if (typeof Swal !== 'undefined') {
                                        Swal.fire({
                                            title: 'Section Management',
                                            text: 'You will be redirected to the section management page.',
                                            icon: 'info',
                                            showCancelButton: true,
                                            confirmButtonText: 'Go to Section Management',
                                            cancelButtonText: 'Stay Here'
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                // Redirect to section management page
                                                window.location.href = `manage_sections.php?course_id=${courseId}`;
                                            }
                                        });
                                    } else {
                                        if (confirm('Would you like to go to the section management page?')) {
                                            // Redirect to section management page
                                            window.location.href = `manage_sections.php?course_id=${courseId}`;
                                        }
                                    }
                                });

                                // Function to load tags
                                function loadTags() {
                                    const tagSelect = document.getElementById('courseTagsSelect');

                                    // Clear existing options
                                    tagSelect.innerHTML = '';

                                    // AJAX call to fetch tags
                                    fetch('../backend/courses/get_tags.php')
                                        .then(response => {
                                            if (!response.ok) {
                                                throw new Error(`HTTP error! Status: ${response.status}`);
                                            }
                                            return response.json();
                                        })
                                        .then(tags => {
                                            if (tags && tags.length > 0) {
                                                console.log(`Loaded ${tags.length} tags`);
                                                tags.forEach(tag => {
                                                    const option = document.createElement('option');
                                                    option.value = tag.tag_id;
                                                    option.textContent = tag.tag_name;
                                                    tagSelect.appendChild(option);
                                                });

                                                // Initialize select2 after loading tags if available
                                                initializeSelect2();
                                            } else {
                                                console.log('No tags found');
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Error fetching tags:', error);
                                            // Fallback for demonstration - add placeholder tags
                                            console.log('Using fallback tags');
                                            const demoTags = [{
                                                    tag_id: 1,
                                                    tag_name: 'Programming'
                                                },
                                                {
                                                    tag_id: 2,
                                                    tag_name: 'Python'
                                                },
                                                {
                                                    tag_id: 3,
                                                    tag_name: 'Web Development'
                                                },
                                                {
                                                    tag_id: 4,
                                                    tag_name: 'JavaScript'
                                                },
                                                {
                                                    tag_id: 5,
                                                    tag_name: 'PHP'
                                                },
                                                {
                                                    tag_id: 6,
                                                    tag_name: 'Cybersecurity'
                                                },
                                                {
                                                    tag_id: 7,
                                                    tag_name: 'AI'
                                                },
                                                {
                                                    tag_id: 8,
                                                    tag_name: 'Machine Learning'
                                                }
                                            ];

                                            demoTags.forEach(tag => {
                                                const option = document.createElement('option');
                                                option.value = tag.tag_id;
                                                option.textContent = tag.tag_name;
                                                tagSelect.appendChild(option);
                                            });

                                            // Initialize select2 after loading fallback tags
                                            initializeSelect2();
                                        });
                                }

                                // Initialize select2
                                function initializeSelect2() {
                                    if (typeof $.fn.select2 !== 'undefined') {
                                        try {
                                            console.log('Initializing Select2 for tags');
                                            // Destroy any existing select2 instance
                                            if ($('#courseTagsSelect').data('select2')) {
                                                $('#courseTagsSelect').select2('destroy');
                                            }

                                            // Initialize select2
                                            $('#courseTagsSelect').select2({
                                                placeholder: 'Select tags',
                                                maximumSelectionLength: 5,
                                                width: '100%',
                                                dropdownParent: $('#courseEditModal')
                                            });
                                            console.log('Select2 initialized successfully');
                                        } catch (e) {
                                            console.error('Error initializing Select2:', e);
                                        }
                                    } else {
                                        console.log('Select2 is not available');
                                    }
                                }

                                // Function to get course details
                                function getCourseDetails(courseId) {
                                    // AJAX call to fetch full course details
                                    fetch(`../backend/courses/get_course_details.php?course_id=${courseId}`)
                                        .then(response => {
                                            if (!response.ok) {
                                                throw new Error(`HTTP error! Status: ${response.status}`);
                                            }
                                            return response.json();
                                        })
                                        .then(data => {
                                            console.log("Course details received:", data);

                                            if (data.error) {
                                                console.error("Error in response:", data.error);
                                                return;
                                            }

                                            // Populate additional fields with the data
                                            document.getElementById('modalCourseFullDescription').value = data.full_description || '';
                                            document.getElementById('modalCourseAccessLevel').value = data.access_level || 'Public';

                                            // Set certificate checkbox
                                            const certificateCheckbox = document.getElementById('modalCourseCertificateEnabled');
                                            certificateCheckbox.checked = data.certificate_enabled == 1;

                                            document.getElementById('modalCourseCreatedAt').value = data.created_at_formatted || data.created_at || '';
                                            document.getElementById('modalCourseUpdatedAt').value = data.updated_at_formatted || data.updated_at || '';
                                            document.getElementById('modalCourseApprovalStatus').value = data.approval_status || 'Pending';

                                            // Set subcategory and handle the category (which is hidden)
                                            if (data.subcategory_id) {
                                                // Wait for subcategories to be loaded
                                                const waitForSubcategories = setInterval(() => {
                                                    const subcategorySelect = document.getElementById('modalCourseSubcategory');
                                                    if (subcategorySelect.options.length > 1) {
                                                        clearInterval(waitForSubcategories);

                                                        // Set subcategory
                                                        subcategorySelect.value = data.subcategory_id;

                                                        // Set the hidden category field
                                                        if (data.category_id) {
                                                            document.getElementById('modalCourseCategory').value = data.category_id;
                                                        } else if (subcategoryMap[data.subcategory_id]) {
                                                            // If category_id wasn't returned but we have a mapping
                                                            document.getElementById('modalCourseCategory').value = subcategoryMap[data.subcategory_id];
                                                        }
                                                    }
                                                }, 100);

                                                // Kill the interval after 3 seconds to prevent infinite loops
                                                setTimeout(() => clearInterval(waitForSubcategories), 3000);
                                            }

                                            // Set tags
                                            if (data.tags && Array.isArray(data.tags)) {
                                                console.log("Setting tags:", data.tags);

                                                // Wait for tags to be fully loaded and Select2 initialized
                                                const waitForTags = setInterval(() => {
                                                    const tagSelect = document.getElementById('courseTagsSelect');

                                                    if (tagSelect.options.length > 0) {
                                                        clearInterval(waitForTags);

                                                        setTimeout(() => {
                                                            if (typeof $.fn.select2 !== 'undefined') {
                                                                try {
                                                                    console.log("Using Select2 to set tags");
                                                                    // Clear previous selections
                                                                    $('#courseTagsSelect').val(null).trigger('change');

                                                                    // Set new values
                                                                    $('#courseTagsSelect').val(data.tags).trigger('change');
                                                                } catch (e) {
                                                                    console.error("Error setting tags with Select2:", e);
                                                                    setTagsManually();
                                                                }
                                                            } else {
                                                                console.log("Select2 not available, setting tags manually");
                                                                setTagsManually();
                                                            }

                                                            function setTagsManually() {
                                                                // Regular select element - clear previous selections
                                                                const tagSelect = document.getElementById('courseTagsSelect');
                                                                for (let i = 0; i < tagSelect.options.length; i++) {
                                                                    tagSelect.options[i].selected = false;
                                                                }

                                                                // Set new selections
                                                                data.tags.forEach(tagId => {
                                                                    const option = tagSelect.querySelector(`option[value="${tagId}"]`);
                                                                    if (option) {
                                                                        option.selected = true;
                                                                        console.log(`Selected tag: ${option.textContent}`);
                                                                    }
                                                                });
                                                            }
                                                        }, 300);
                                                    }
                                                }, 100);

                                                // Kill the interval after 5 seconds to prevent infinite loops
                                                setTimeout(() => clearInterval(waitForTags), 5000);
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Error fetching course details:', error);
                                        });
                                }

                                editButtons.forEach(button => {
                                    button.addEventListener('click', function(e) {
                                        e.stopPropagation(); // Prevent event bubbling

                                        // Function to get the correct image path
                                        function getImagePath(thumbnail) {
                                            const uploadPaths = [
                                                '../uploads/thumbnails/',
                                                '../uploads/',
                                                'uploads/thumbnails/',
                                                'uploads/'
                                            ];

                                            // Default image
                                            let defaultImage = '../assets/images/default-course.jpg';

                                            // If thumbnail is empty, return default
                                            if (!thumbnail) return defaultImage;

                                            // First try the direct path that's used in the page
                                            const directPath = document.querySelector(`img[alt="Course thumbnail for ${button.dataset.courseTitle}"]`).src;
                                            if (directPath) return directPath;

                                            // Otherwise try the possible paths
                                            for (let path of uploadPaths) {
                                                let fullPath = path + thumbnail;
                                                return fullPath; // Return the first path (this is simplified)
                                            }

                                            return defaultImage;
                                        }

                                        // Populate modal with course data
                                        const courseId = this.dataset.courseId;
                                        document.getElementById('modalCourseId').value = courseId;
                                        document.getElementById('modalCourseTitle').value = this.dataset.courseTitle;
                                        document.getElementById('modalCourseDescription').value = this.dataset.courseDescription;
                                        document.getElementById('modalCoursePrice').value = this.dataset.coursePrice;
                                        document.getElementById('modalCourseLevel').value = this.dataset.courseLevel;
                                        document.getElementById('modalCourseStatus').value = this.dataset.courseStatus;

                                        // Set certificate checkbox
                                        const certificateEnabled = this.dataset.courseCertificate === '1';
                                        document.getElementById('modalCourseCertificateEnabled').checked = certificateEnabled;

                                        // Set thumbnail image
                                        const thumbnailImg = document.getElementById('modalCourseThumbnail');
                                        thumbnailImg.src = getImagePath(this.dataset.courseThumbnail);
                                        thumbnailImg.alt = `Thumbnail for ${this.dataset.courseTitle}`;

                                        // Load all data
                                        loadAllSubcategories();
                                        loadTags();
                                        initializeRequirements(courseId);
                                        initializeOutcomes(courseId);
                                        initializeSectionsTable(courseId);
                                        getCourseDetails(courseId);

                                        // Show the first tab
                                        document.getElementById('basic-info-tab').click();

                                        // Show the modal
                                        courseEditModal.show();
                                    });
                                });

                                // Delete course button click event
                                const deleteButtons = document.querySelectorAll('.delete-course');
                                const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));

                                deleteButtons.forEach(button => {
                                    button.addEventListener('click', function(e) {
                                        e.stopPropagation(); // Prevent event bubbling

                                        // Set delete confirmation modal data
                                        document.getElementById('deleteCourseName').textContent = this.dataset.courseTitle;
                                        document.getElementById('deleteCourseId').value = this.dataset.courseId;

                                        // Show delete confirmation modal
                                        deleteModal.show();
                                    });
                                });

                                // Function to collect form data from all tabs
                                function collectFormData() {
                                    const formData = new FormData(document.getElementById('courseEditForm'));

                                    // Add certificate_enabled as 0 or 1
                                    const certificateEnabled = document.getElementById('modalCourseCertificateEnabled').checked ? 1 : 0;
                                    formData.set('certificate_enabled', certificateEnabled);

                                    // Collect requirements
                                    const requirementGroups = document.querySelectorAll('.requirement-group input');
                                    const requirements = [];
                                    requirementGroups.forEach(input => {
                                        if (input.value.trim() !== '') {
                                            requirements.push(input.value.trim());
                                        }
                                    });
                                    formData.delete('requirements[]'); // Remove any existing entries
                                    requirements.forEach(req => {
                                        formData.append('requirements[]', req);
                                    });

                                    // Collect learning outcomes
                                    const outcomeGroups = document.querySelectorAll('.outcome-group input');
                                    const outcomes = [];
                                    outcomeGroups.forEach(input => {
                                        if (input.value.trim() !== '') {
                                            outcomes.push(input.value.trim());
                                        }
                                    });
                                    formData.delete('outcomes[]'); // Remove any existing entries
                                    outcomes.forEach(outcome => {
                                        formData.append('outcomes[]', outcome);
                                    });

                                    // Collect tags
                                    const tagSelect = document.getElementById('courseTagsSelect');
                                    const selectedTags = Array.from(tagSelect.selectedOptions).map(option => option.value);
                                    formData.delete('tags[]'); // Remove any existing entries
                                    selectedTags.forEach(tag => {
                                        formData.append('tags[]', tag);
                                    });

                                    return formData;
                                }

                                // Save course changes button click
                                document.getElementById('saveCourseChanges').addEventListener('click', function() {
                                    // Validate form
                                    const form = document.getElementById('courseEditForm');
                                    if (!form.checkValidity()) {
                                        form.reportValidity();
                                        return;
                                    }

                                    // Show loading state
                                    const saveButton = this;
                                    const originalText = saveButton.innerHTML;
                                    saveButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
                                    saveButton.disabled = true;

                                    // Get form data from all tabs
                                    const formData = collectFormData();

                                    // AJAX request to update the course
                                    fetch('../backend/courses/update_course.php', {
                                            method: 'POST',
                                            body: formData
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                // Show success message
                                                if (typeof Swal !== 'undefined') {
                                                    Swal.fire({
                                                        title: 'Success!',
                                                        text: 'Course updated successfully!',
                                                        icon: 'success',
                                                        confirmButtonText: 'OK'
                                                    }).then(() => {
                                                        // Reload page
                                                        window.location.reload();
                                                    });
                                                } else {
                                                    alert('Course updated successfully!');
                                                    window.location.reload();
                                                }
                                            } else {
                                                if (typeof Swal !== 'undefined') {
                                                    Swal.fire({
                                                        title: 'Error!',
                                                        text: data.message || 'Failed to update course.',
                                                        icon: 'error',
                                                        confirmButtonText: 'OK'
                                                    });
                                                } else {
                                                    alert('Error: ' + (data.message || 'Failed to update course.'));
                                                }
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Error:', error);
                                            if (typeof Swal !== 'undefined') {
                                                Swal.fire({
                                                    title: 'Error!',
                                                    text: 'An unexpected error occurred. Please try again.',
                                                    icon: 'error',
                                                    confirmButtonText: 'OK'
                                                });
                                            } else {
                                                alert('An unexpected error occurred. Please try again.');
                                            }
                                        })
                                        .finally(() => {
                                            // Reset button state
                                            saveButton.innerHTML = originalText;
                                            saveButton.disabled = false;
                                        });
                                });

                                // Confirm delete button click
                                document.getElementById('confirmDelete').addEventListener('click', function() {
                                    const courseId = document.getElementById('deleteCourseId').value;

                                    // Show loading state
                                    const deleteButton = this;
                                    const originalText = deleteButton.innerHTML;
                                    deleteButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...';
                                    deleteButton.disabled = true;

                                    // AJAX request to delete the course
                                    fetch('../backend/courses/delete_course.php', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                            },
                                            body: JSON.stringify({
                                                course_id: courseId
                                            })
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                // Close modal
                                                bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal')).hide();

                                                // Remove the course card from the page
                                                document.querySelector(`.delete-course[data-course-id="${courseId}"]`).closest('.course-item').remove();

                                                // Show success message
                                                if (typeof Swal !== 'undefined') {
                                                    Swal.fire({
                                                        title: 'Deleted!',
                                                        text: data.message || 'Course deleted successfully!',
                                                        icon: 'success',
                                                        confirmButtonText: 'OK'
                                                    });
                                                } else {
                                                    alert(data.message || 'Course deleted successfully!');
                                                }
                                            } else {
                                                if (typeof Swal !== 'undefined') {
                                                    Swal.fire({
                                                        title: 'Error!',
                                                        text: data.message || 'Failed to delete course.',
                                                        icon: 'error',
                                                        confirmButtonText: 'OK'
                                                    });
                                                } else {
                                                    alert('Error: ' + (data.message || 'Failed to delete course.'));
                                                }
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Error:', error);
                                            if (typeof Swal !== 'undefined') {
                                                Swal.fire({
                                                    title: 'Error!',
                                                    text: 'An unexpected error occurred. Please try again.',
                                                    icon: 'error',
                                                    confirmButtonText: 'OK'
                                                });
                                            } else {
                                                alert('An unexpected error occurred. Please try again.');
                                            }
                                        })
                                        .finally(() => {
                                            // Reset button state
                                            deleteButton.innerHTML = originalText;
                                            deleteButton.disabled = false;
                                        });
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