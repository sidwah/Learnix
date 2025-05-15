
<?php 
// department/manage-course.php
include '../includes/department/header.php';
require_once '../backend/config.php';

// Check if user is logged in and has department head role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    header('Location: courses.php');
    exit;
}

// Get course ID
$course_id = filter_input(INPUT_GET, 'course_id', FILTER_VALIDATE_INT) ?: 0;

if (!$course_id) {
    header('Location: courses.php');
    exit;
}

// Get user's department
$dept_query = "SELECT ds.department_id 
               FROM department_staff ds 
               WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' AND ds.deleted_at IS NULL
               LIMIT 1";
$dept_stmt = $conn->prepare($dept_query);
$dept_stmt->bind_param("i", $_SESSION['user_id']);
$dept_stmt->execute();
$dept_result = $dept_stmt->get_result();

if ($dept_result->num_rows === 0) {
    header('Location: courses.php');
    exit;
}

$department = $dept_result->fetch_assoc();
$department_id = $department['department_id'];

// Verify course belongs to department and get details
$course_query = "SELECT 
                    c.*,
                    cat.name as category_name,
                    sub.name as subcategory_name
                FROM courses c
                JOIN subcategories sub ON c.subcategory_id = sub.subcategory_id
                JOIN categories cat ON sub.category_id = cat.category_id
                WHERE c.course_id = ? AND c.department_id = ? AND c.deleted_at IS NULL";

$course_stmt = $conn->prepare($course_query);
$course_stmt->bind_param("ii", $course_id, $department_id);
$course_stmt->execute();
$course_result = $course_stmt->get_result();

if ($course_result->num_rows === 0) {
    header('Location: courses.php');
    exit;
}

$course = $course_result->fetch_assoc();

// Get current instructors
$instructors_query = "SELECT 
                         u.user_id,
                         u.first_name,
                         u.last_name,
                         u.email,
                         u.profile_pic,
                         i.instructor_id,
                         ci.is_primary,
                         ci.assigned_at
                     FROM course_instructors ci
                     JOIN instructors i ON ci.instructor_id = i.instructor_id
                     JOIN users u ON i.user_id = u.user_id
                     WHERE ci.course_id = ? AND ci.deleted_at IS NULL
                     ORDER BY ci.is_primary DESC, u.first_name";

$inst_stmt = $conn->prepare($instructors_query);
$inst_stmt->bind_param("i", $course_id);
$inst_stmt->execute();
$inst_result = $inst_stmt->get_result();

$current_instructors = [];
while ($instructor = $inst_result->fetch_assoc()) {
    $current_instructors[] = $instructor;
}

// Get available instructors in department
$available_query = "SELECT 
                       u.user_id,
                       u.first_name,
                       u.last_name,
                       u.email,
                       i.instructor_id
                   FROM department_instructors di
                   JOIN instructors i ON di.instructor_id = i.instructor_id
                   JOIN users u ON i.user_id = u.user_id
                   WHERE di.department_id = ? 
                       AND di.status = 'active' 
                       AND di.deleted_at IS NULL
                       AND i.instructor_id NOT IN (
                           SELECT instructor_id 
                           FROM course_instructors 
                           WHERE course_id = ? AND deleted_at IS NULL
                       )
                   ORDER BY u.first_name";

$avail_stmt = $conn->prepare($available_query);
$avail_stmt->bind_param("ii", $department_id, $course_id);
$avail_stmt->execute();
$avail_result = $avail_stmt->get_result();

$available_instructors = [];
while ($instructor = $avail_result->fetch_assoc()) {
    $available_instructors[] = $instructor;
}

// Helper function to get status badge class and icon
function getStatusBadge($status) {
    $badges = [
        'pending' => ['bg-soft-warning', 'bi-hourglass-split', 'Pending'],
        'revisions_requested' => ['bg-soft-info', 'bi-pencil-square', 'Revisions Requested'],
        'submitted_for_review' => ['bg-soft-primary', 'bi-file-earmark-check', 'Submitted for Review'],
        'under_review' => ['bg-soft-primary', 'bi-search', 'Under Review'],
        'approved' => ['bg-soft-success', 'bi-check-circle', 'Approved'],
        'rejected' => ['bg-soft-danger', 'bi-x-circle', 'Rejected']
    ];
    
    $default = ['bg-soft-secondary', 'bi-question-circle', ucfirst(str_replace('_', ' ', $status))];
    return $badges[$status] ?? $default;
}

$statusBadge = getStatusBadge($course['approval_status']);
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<style>
    .custom-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.custom-overlay .spinner-border {
    width: 2rem;
    height: 2rem;
}

.custom-overlay .text-white {
    font-size: 1rem;
    font-weight: 200;
}
</style>
<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main">
    <!-- Toast -->
    <div id="liveToast" class="position-fixed toast hide" role="alert" aria-live="assertive" aria-atomic="true" style="top: 20px; right: 20px; z-index: 1000;">
        <div class="toast-header">
            <div class="d-flex align-items-center flex-grow-1">
                <div class="flex-shrink-0" id="toastIcon">
                    <!-- Icon will be inserted dynamically -->
                </div>
                <div class="flex-grow-1 ms-3">
                    <h5 class="mb-0">System Notification</h5>
                    <small class="ms-auto">Just Now</small>
                </div>
                <div class="text-end">
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
        <div class="toast-body" id="toastMessage">
            Notification message will appear here
        </div>
    </div>
    <!-- End Toast -->

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-sm">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="confirmationModalLabel">Confirmation Required</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="confirmationModalBody">
                    Are you sure you want to proceed with this action?
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary rounded-pill" id="confirmActionBtn">Confirm</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Confirmation Modal -->

    <!-- Comments Modal -->
    <div class="modal fade" id="commentsModal" tabindex="-1" aria-labelledby="commentsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-sm">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="commentsModalLabel">Additional Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="commentsTextarea" class="form-label" id="commentsLabel">Please provide your comments:</label>
                        <textarea class="form-control border-0 bg-light shadow-none" id="commentsTextarea" rows="4" style="resize: none;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary rounded-pill" id="submitCommentsBtn">Submit</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Comments Modal -->
    
    <!-- Navbar -->
    <nav class="js-nav-scroller navbar navbar-expand-lg navbar-sidebar navbar-vertical navbar-light bg-white border-end">
        <?php include '../includes/department/sidebar.php'; ?>
    </nav>
    <!-- End Navbar -->

    <!-- Content -->
    <div class="navbar-sidebar-aside-content content-space-1 content-space-md-2 px-lg-5 px-xl-5">
        <!-- Page Header -->
        <div class="page-header mb-4">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter fs-sm">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="courses.php">
                                    <i class="bi-grid-fill me-1"></i> Courses
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Manage Course</li>
                        </ol>
                    </nav>
                    
                    <div class="d-flex align-items-center mt-2">
                        <h1 class="page-header-title mb-0"><?php echo htmlspecialchars($course['title']); ?></h1>
                    </div>
                    
                    <div class="mt-2 d-flex align-items-center flex-wrap gap-2">
                        <span class=" <?php echo $statusBadge[0]; ?> rounded-pill fs-sm px-3 py-2">
                            <i class="bi <?php echo $statusBadge[1]; ?> me-1"></i> <?php echo $statusBadge[2]; ?>
                        </span>
                        
                        <span class=" bg-soft-dark rounded-pill fs-sm px-3 py-2">
                            <i class="bi-folder me-1"></i> <?php echo htmlspecialchars($course['category_name']); ?>
                        </span>
                        
                        <span class=" bg-soft-primary rounded-pill fs-sm px-3 py-2">
                            <i class="bi-bar-chart me-1"></i> <?php echo htmlspecialchars($course['course_level']); ?>
                        </span>
                        
                        <?php if ($course['certificate_enabled']): ?>
                        <span class="bg-soft-info rounded-pill fs-sm px-3 py-2">
                            <i class="bi-patch-check me-1"></i> Certificate
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-sm-auto mt-3 mt-sm-0">
                    <div class="btn-group">
                        <a href="courses.php" class="btn btn-outline-secondary rounded-pill">
                            <i class="bi-arrow-left me-1"></i> Back
                        </a>
                        <a href="course-analytics.php?course_id=<?php echo $course_id; ?>" class="btn btn-primary rounded-pill">
                            <i class="bi-graph-up me-1"></i> Analytics
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Test Dropdown for Debugging -->
        <!-- <div class="mb-4">
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="testDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    Test Dropdown
                </button>
                <ul class="dropdown-menu" aria-labelledby="testDropdown">
                    <li><a class="dropdown-item" href="#">Action</a></li>
                    <li><a class="dropdown-item" href="#">Another action</a></li>
                </ul>
            </div>
        </div> -->

        <div class="row g-4">
            <!-- Course Information -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-transparent border-0 pt-4 pb-0">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-sm avatar-soft-primary avatar-circle">
                                    <span class="avatar-initials">
                                        <i class="bi-book"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4 class="card-header-title mb-0">Course Information</h4>
                                <span class="badge bg-soft-secondary fs-sm mt-1">ID: <?php echo $course_id; ?></span>
                            </div>
                            <div class="flex-shrink-0">
                                <?php if($course['thumbnail']): ?>
                                <div class="avatar avatar-lg">
                                    <img class="avatar-img rounded" src="../Uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>" alt="Course Thumbnail">
                                </div>
                                <?php else: ?>
                                <div class="avatar avatar-lg avatar-soft-dark">
                                    <span class="avatar-initials"><i class="bi-image"></i></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="courseInfoForm">
                            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                            
                            <div class="row g-4">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="courseTitle" class="form-label fw-medium">Course Title</label>
                                        <input type="text" class="form-control bg-light border-0" id="courseTitle" name="title" 
                                               value="<?php echo htmlspecialchars($course['title']); ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="courseCategory" class="form-label fw-medium">Category</label>
                                        <input type="text" class="form-control bg-light border-0" id="courseCategory" 
                                               value="<?php echo htmlspecialchars($course['category_name']); ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="courseLevel" class="form-label fw-medium">Course Level</label>
                                        <input type="text" class="form-control bg-light border-0" id="courseLevel" 
                                               value="<?php echo htmlspecialchars($course['course_level']); ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="courseDescription" class="form-label fw-medium">Short Description</label>
                                        <textarea class="form-control bg-light border-0" id="courseDescription" name="short_description" rows="3" readonly><?php echo htmlspecialchars($course['short_description']); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </form>
                        
                        <div class="alert bg-soft-primary border-0 rounded-3 mt-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi-info-circle-fill text-primary fs-4"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="alert-heading mb-1">Course Management</h5>
                                    <p class="mb-0">Course content editing is handled through the <a href="initiate-course.php?course_id=<?php echo $course_id; ?>" class="alert-link">course creation interface</a>.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Course Stats -->
                        <div class="row g-3 mt-2">
                            <div class="col-sm-6 col-md-4">
                                <div class="card bg-soft-success border-0">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="avatar avatar-sm avatar-circle avatar-soft-success">
                                                    <span class="avatar-initials"><i class="bi-people-fill"></i></span>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="card-subtitle mb-1">Enrollments</h6>
                                                <h4 class="card-title mb-0">0</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-sm-6 col-md-4">
                                <div class="card bg-soft-warning border-0">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="avatar avatar-sm avatar-circle avatar-soft-warning">
                                                    <span class="avatar-initials"><i class="bi-star-fill"></i></span>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="card-subtitle mb-1">Rating</h6>
                                                <h4 class="card-title mb-0">0.0</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-sm-6 col-md-4">
                                <div class="card bg-soft-info border-0">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="avatar avatar-sm avatar-circle avatar-soft-info">
                                                    <span class="avatar-initials"><i class="bi-mortarboard-fill"></i></span>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="card-subtitle mb-1">Completion</h6>
                                                <h4 class="card-title mb-0">0%</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Instructor Management -->
            <div class="col-lg-4">
                <!-- Instructors Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-transparent border-0 pt-4 pb-0">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-sm avatar-soft-primary avatar-circle">
                                    <span class="avatar-initials">
                                        <i class="bi-person-badge"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4 class="card-header-title mb-0">Instructors</h4>
                                <p class="text-muted small mt-1 mb-0">Manage course instructors</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <!-- Current Instructors -->
                        <div class="mb-4">
                            <h6 class="fw-medium mb-3 text-uppercase fs-xs"><i class="bi-people-fill me-2"></i>Current Instructors</h6>
                            <div id="currentInstructorsList">
                                <?php if (empty($current_instructors)): ?>
                                    <div class="text-center py-4 my-3 bg-soft-secondary rounded-3">
                                        <i class="bi-person-x fs-1 text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No instructors assigned</p>
                                    </div>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($current_instructors as $instructor): ?>
                                            <div class="list-group-item px-0 py-3 border-0">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-circle me-3">
                                                            <?php if($instructor['profile_pic'] && $instructor['profile_pic'] !== 'default.png' && file_exists('../Uploads/profiles/' . $instructor['profile_pic'])): ?>
                                                                <img src="../Uploads/profiles/<?php echo htmlspecialchars($instructor['profile_pic']); ?>" alt="Profile" class="avatar-img">
                                                            <?php else: ?>
                                                                <span class="avatar-soft-<?php echo $instructor['is_primary'] ? 'primary' : 'dark'; ?> avatar-initials">
                                                                    <?php echo substr($instructor['first_name'], 0, 1) . substr($instructor['last_name'], 0, 1); ?>
                                                                </span>
                                                            <?php endif; ?>
                                                            <?php if($instructor['is_primary']): ?>
                                                                <span class="avatar-status avatar-sm-status avatar-status-success"></span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div>
                                                            <h5 class="mb-0 fs-sm"><?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?></h5>
                                                            <p class="text-muted mb-0 fs-xs"><?php echo $instructor['is_primary'] ? 'Primary Instructor' : 'Co-instructor'; ?></p>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="dropdown">
                                                            <button type="button" class="btn btn-ghost-secondary btn-icon btn-sm rounded-circle" id="dropdown-<?php echo $instructor['instructor_id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <i class="bi-three-dots-vertical"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-end border-0 shadow-sm" aria-labelledby="dropdown-<?php echo $instructor['instructor_id']; ?>">
                                                                <?php if (!$instructor['is_primary']): ?>
                                                                    <button type="button" class="dropdown-item" 
                                                                        onclick="makeInstructorPrimary(<?php echo $course_id; ?>, <?php echo $instructor['instructor_id']; ?>)">
                                                                        <i class="bi-star me-2"></i> Make Primary
                                                                    </button>
                                                                <?php endif; ?>
                                                                <button type="button" class="dropdown-item text-danger" 
                                                                    onclick="confirmRemoveInstructor(<?php echo $course_id; ?>, <?php echo $instructor['instructor_id']; ?>)">
                                                                    <i class="bi-trash me-2"></i> Remove
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Add Instructor -->
                        <div class="py-2">
                            <h6 class="fw-medium mb-3 text-uppercase fs-xs"><i class="bi-person-plus-fill me-2"></i>Add Instructor</h6>
                            <div class="row g-2 align-items-center">
                                <div class="col">
                                    <select class="form-select form-select-sm border-0 bg-light shadow-none" id="availableInstructors">
                                        <option value="">Choose an instructor...</option>
                                        <?php foreach ($available_instructors as $instructor): ?>
                                            <option value="<?php echo $instructor['instructor_id']; ?>">
                                                <?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" id="addInstructorBtn">
                                        <i class="bi-plus-lg me-1"></i> Add
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Course Actions -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-transparent border-0 pt-4 pb-0">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-sm avatar-soft-primary avatar-circle">
                                    <span class="avatar-initials">
                                        <i class="bi-gear"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4 class="card-header-title mb-0">Course Actions</h4>
                                <p class="text-muted small mt-1 mb-0">Manage course status</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="initiate-course.php?course_id=<?php echo $course_id; ?>" class="btn btn-outline-primary btn-transition rounded-pill">
                                <i class="bi-pencil me-1"></i> Edit Content
                            </a>
                            
                            <?php if ($course['approval_status'] === 'submitted_for_review' || $course['approval_status'] === 'under_review'): ?>
                                <div class="row g-2 mt-1">
                                    <div class="col">
                                        <button type="button" class="btn btn-outline-success btn-transition w-100 rounded-pill" onclick="confirmApproveCourse(<?php echo $course_id; ?>)">
                                            <i class="bi-check-circle me-1"></i> Approve
                                        </button>
                                    </div>
                                    <div class="col">
                                        <button type="button" class="btn btn-outline-warning btn-transition w-100 rounded-pill" onclick="openRevisionsModal(<?php echo $course_id; ?>)">
                                            <i class="bi-arrow-counterclockwise me-1"></i> Revisions
                                        </button>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-danger btn-transition rounded-pill" onclick="openRejectModal(<?php echo $course_id; ?>)">
                                    <i class="bi-x-circle me-1"></i> Reject Course
                                </button>
                            <?php endif; ?>
                            
                            <?php if ($course['status'] === 'Published'): ?>
                                <button type="button" class="btn btn-outline-warning btn-transition rounded-pill" onclick="confirmUnpublishCourse(<?php echo $course_id; ?>)">
                                    <i class="bi-eye-slash me-1"></i> Unpublish
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="d-grid">
                            <button type="button" class="btn btn-soft-danger btn-transition rounded-pill" onclick="confirmArchiveCourse(<?php echo $course_id; ?>)">
                                <i class="bi-archive me-1"></i> Archive Course
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Content -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- JavaScript for instructor management, course actions, and UI initialization -->
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
    }

    // Remove Loading Overlay
    function removeOverlay() {
    const overlay = document.querySelector('.custom-overlay');
    if (overlay) {
        overlay.remove();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const courseId = <?php echo json_encode($course_id); ?>;

    // Initialize Bootstrap components
    const toastEl = document.getElementById('liveToast');
    const toast = new bootstrap.Toast(toastEl, {
        delay: 5000,
        animation: true
    });

    const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
    const commentsModal = new bootstrap.Modal(document.getElementById('commentsModal'));

    // Initialize dropdowns with retry mechanism
    function initializeDropdowns(attempt = 1, maxAttempts = 3) {
        const dropdowns = document.querySelectorAll('[data-bs-toggle="dropdown"]');
        if (dropdowns.length === 0 && attempt <= maxAttempts) {
            console.warn(`No dropdowns found, retrying (${attempt}/${maxAttempts})`);
            setTimeout(() => initializeDropdowns(attempt + 1, maxAttempts), 100);
            return;
        }

        dropdowns.forEach(dropdown => {
            try {
                new bootstrap.Dropdown(dropdown);
                console.log('Dropdown initialized:', dropdown.id);
            } catch (e) {
                console.warn('Failed to initialize dropdown:', dropdown.id, e.message);
            }
        });
    }
    initializeDropdowns();

    // Initialize js-nav-scroller with fallback
    function initializeNavScroller(attempt = 1, maxAttempts = 5) {
        const nav = document.querySelector('.js-nav-scroller');
        if (!nav) {
            console.warn('Nav scroller container not found');
            return;
        }

        let target = nav.querySelector('.nav-link.active');
        if (!target) {
            // Fallback: Set active class on Courses link for course-related pages
            target = nav.querySelector('a[href="courses.php"]');
            if (target) {
                target.classList.add('active');
                console.log('Fallback: Set active class on Courses link');
            } else {
                console.warn('No fallback nav link found for js-nav-scroller');
            }
        }

        if (target) {
            try {
                // Replace with actual NavScroller initialization
                console.log('NavScroller initialized for:', target);
                // Example: new NavScroller({ nav: '.js-nav-scroller', target: '.nav-link.active' });
                // Add actual initialization here based on your library
            } catch (e) {
                console.error('NavScroller initialization failed:', e.message);
            }
        } else if (attempt < maxAttempts) {
            // Retry after a delay if target is still missing
            setTimeout(() => initializeNavScroller(attempt + 1, maxAttempts), 100);
        } else {
            console.warn('Max attempts reached: No active nav link found for js-nav-scroller');
        }
    }

    // Delay scroller initialization to ensure sidebar active link is set
    setTimeout(initializeNavScroller, 100);

    // Add instructor handler
    document.getElementById('addInstructorBtn').addEventListener('click', function() {
        const instructorSelect = document.getElementById('availableInstructors');
        const instructorId = instructorSelect.value;

        if (!instructorId) {
            showError('Please select an instructor to add.');
            instructorSelect.classList.add('is-invalid');
            return;
        } else {
            instructorSelect.classList.remove('is-invalid');
        }

        const formData = new FormData();
        formData.append('action', 'assign_instructor');
        formData.append('course_id', courseId);
        formData.append('instructor_id', instructorId);
        formData.append('is_primary', '0');

        // Show loading overlay
        showOverlay('Adding Instructor...');
        const originalBtnContent = this.innerHTML;
        this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...';
        this.disabled = true;

        fetch('../backend/department/manage_instructors.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            removeOverlay();
            if (data.success) {
                showSuccess(data.message);
                setTimeout(() => {
                    window.location.reload();
                    initializeDropdowns(); // Re-initialize dropdowns after reload
                }, 1000);
            } else {
                showError(data.message);
                this.innerHTML = originalBtnContent;
                this.disabled = false;
            }
        })
        .catch(error => {
            removeOverlay();
            showError('Error adding instructor: ' + error.message);
            this.innerHTML = originalBtnContent;
            this.disabled = false;
        });
    });

    // Remove invalid class when instructor is selected
    document.getElementById('availableInstructors').addEventListener('change', function() {
        if (this.value) {
            this.classList.remove('is-invalid');
        }
    });
});

// Toast utility functions
function showSuccess(message) {
    const toastEl = document.getElementById('liveToast');
    const iconEl = document.getElementById('toastIcon');
    const messageEl = document.getElementById('toastMessage');

    toastEl.classList.remove('bg-danger', 'text-white');
    toastEl.classList.add('bg-soft-success');
    iconEl.innerHTML = '<i class="bi-check-circle-fill text-success fs-4 me-2"></i>';
    messageEl.textContent = message;

    const toast = bootstrap.Toast.getInstance(toastEl) || new bootstrap.Toast(toastEl);
    toast.show();
}

function showError(message) {
    const toastEl = document.getElementById('liveToast');
    const iconEl = document.getElementById('toastIcon');
    const messageEl = document.getElementById('toastMessage');

    toastEl.classList.remove('bg-soft-success');
    toastEl.classList.add('bg-danger', 'text-white');
    iconEl.innerHTML = '<i class="bi-exclamation-circle-fill text-white fs-4 me-2"></i>';
    messageEl.textContent = message;

    const toast = bootstrap.Toast.getInstance(toastEl) || new bootstrap.Toast(toastEl);
    toast.show();
}

// Confirmation modal functions
function showConfirmationModal(title, message, confirmBtnText, confirmBtnClass, callback) {
    const modalEl = document.getElementById('confirmationModal');
    const titleEl = document.getElementById('confirmationModalLabel');
    const bodyEl = document.getElementById('confirmationModalBody');
    const confirmBtn = document.getElementById('confirmActionBtn');

    titleEl.textContent = title;
    bodyEl.textContent = message;
    confirmBtn.textContent = confirmBtnText || 'Confirm';
    confirmBtn.className = 'btn rounded-pill ' + (confirmBtnClass || 'btn-primary');

    confirmBtn.onclick = function() {
        confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
        confirmBtn.disabled = true;

        setTimeout(() => {
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();
            callback();
        }, 300);
    };

    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.show();
}

// Comments modal functions
function showCommentsModal(title, label, submitBtnText, submitBtnClass, callback) {
    const modalEl = document.getElementById('commentsModal');
    const titleEl = document.getElementById('commentsModalLabel');
    const labelEl = document.getElementById('commentsLabel');
    const textareaEl = document.getElementById('commentsTextarea');
    const submitBtn = document.getElementById('submitCommentsBtn');

    titleEl.textContent = title;
    labelEl.textContent = label;
    textareaEl.value = '';
    submitBtn.textContent = submitBtnText || 'Submit';
    submitBtn.className = 'btn rounded-pill ' + (submitBtnClass || 'btn-primary');

    modalEl.addEventListener('shown.bs.modal', () => textareaEl.focus(), { once: true });

    submitBtn.onclick = function() {
        const comments = textareaEl.value.trim();
        if (!comments) {
            textareaEl.classList.add('is-invalid');
            textareaEl.focus();
            return;
        }

        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
        submitBtn.disabled = true;

        setTimeout(() => {
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();
            callback(comments);
            submitBtn.innerHTML = submitBtnText || 'Submit';
            submitBtn.disabled = false;
        }, 300);
    };

    textareaEl.addEventListener('input', function() {
        if (this.value.trim()) {
            this.classList.remove('is-invalid');
        }
    });

    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.show();
}

// Instructor management functions
function makeInstructorPrimary(courseId, instructorId) {
    const formData = new FormData();
    formData.append('action', 'assign_instructor');
    formData.append('course_id', courseId);
    formData.append('instructor_id', instructorId);
    formData.append('is_primary', '1');

    showOverlay('Making Instructor Primary...');

    fetch('../backend/department/manage_instructors.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        removeOverlay();
        if (data.success) {
            showSuccess(data.message);
            setTimeout(() => {
                window.location.reload();
                initializeDropdowns();
            }, 1000);
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        removeOverlay();
        showError('Error updating instructor role: ' + error.message);
    });
}

function confirmRemoveInstructor(courseId, instructorId) {
    showConfirmationModal(
        'Remove Instructor', 
        'Are you sure you want to remove this instructor from the course? This action cannot be undone.',
        'Remove', 
        'btn-danger',
        function() {
            removeInstructor(courseId, instructorId);
        }
    );
}

function removeInstructor(courseId, instructorId) {
    const formData = new FormData();
    formData.append('action', 'remove_instructor');
    formData.append('course_id', courseId);
    formData.append('instructor_id', instructorId);

    showOverlay('Removing Instructor...');

    fetch('../backend/department/manage_instructors.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        removeOverlay();
        if (data.success) {
            showSuccess(data.message);
            setTimeout(() => {
                window.location.reload();
                initializeDropdowns();
            }, 1000);
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        removeOverlay();
        showError('Error removing instructor: ' + error.message);
    });
}

// Course action functions
function confirmApproveCourse(courseId) {
    showConfirmationModal(
        'Approve Course', 
        'Are you sure you want to approve this course? Once approved, it can be published by the instructor.',
        'Approve',
        'btn-success',
        function() {
            approveCourse(courseId);
        }
    );
}

function approveCourse(courseId) {
    const formData = new FormData();
    formData.append('action', 'approve');
    formData.append('course_id', courseId);

    showOverlay('Approving Course...');

    fetch('../ajax/department/course_action_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        removeOverlay();
        if (data.success) {
            showSuccess(data.message);
            setTimeout(() => window.location.href = 'courses.php', 1500);
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        removeOverlay();
        showError('Error approving course: ' + error.message);
    });
}

function openRevisionsModal(courseId) {
    showCommentsModal(
        'Request Revisions', 
        'Please provide specific feedback on what needs to be revised:',
        'Request Revisions',
        'btn-warning',
        function(comments) {
            requestRevisions(courseId, comments);
        }
    );
}

function requestRevisions(courseId, comments) {
    const formData = new FormData();
    formData.append('action', 'request_revisions');
    formData.append('course_id', courseId);
    formData.append('comments', comments);

    showOverlay('Requesting Revisions...');

    fetch('../ajax/department/course_action_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        removeOverlay();
        if (data.success) {
            showSuccess(data.message);
            setTimeout(() => window.location.href = 'courses.php', 1500);
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        removeOverlay();
        showError('Error requesting revisions: ' + error.message);
    });
}

function openRejectModal(courseId) {
    showCommentsModal(
        'Reject Course', 
        'Please provide reasons for rejecting this course:',
        'Reject Course',
        'btn-danger',
        function(comments) {
            rejectCourse(courseId, comments);
        }
    );
}

function rejectCourse(courseId, comments) {
    const formData = new FormData();
    formData.append('action', 'reject');
    formData.append('course_id', courseId);
    formData.append('comments', comments);

    showOverlay('Rejecting Course...');

    fetch('../ajax/department/course_action_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        removeOverlay();
        if (data.success) {
            showSuccess(data.message);
            setTimeout(() => window.location.href = 'courses.php', 1500);
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        removeOverlay();
        showError('Error rejecting course: ' + error.message);
    });
}

function confirmUnpublishCourse(courseId) {
    showConfirmationModal(
        'Unpublish Course', 
        'Are you sure you want to unpublish this course? Students will no longer be able to access it.',
        'Unpublish',
        'btn-warning',
        function() {
            unpublishCourse(courseId);
        }
    );
}

function unpublishCourse(courseId) {
    const formData = new FormData();
    formData.append('action', 'unpublish');
    formData.append('course_id', courseId);

    showOverlay('Unpublishing Course...');

    fetch('../ajax/department/course_action_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        removeOverlay();
        if (data.goals) {
            showSuccess(data.message);
            setTimeout(() => window.location.href = 'courses.php', 1500);
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        removeOverlay();
        showError('Error unpublishing course: ' + error.message);
    });
}

function confirmArchiveCourse(courseId) {
    showConfirmationModal(
        'Archive Course', 
        'Are you sure you want to archive this course? This action can be undone later by an administrator.',
        'Archive',
        'btn-danger',
        function() {
            archiveCourse(courseId);
        }
    );
}

function archiveCourse(courseId) {
    const formData = new FormData();
    formData.append('action', 'archive');
    formData.append('course_id', courseId);

    showOverlay('Archiving Course...');

    fetch('../ajax/department/course_action_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        removeOverlay();
        if (data.success) {
            showSuccess(data.message);
            setTimeout(() => window.location.href = 'courses.php', 1500);
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        removeOverlay();
        showError('Error archiving course: ' + error.message);
    });
}
</script>

<?php 
$conn->close();
include '../includes/department/footer.php'; 
?>