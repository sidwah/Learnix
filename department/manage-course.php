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
$course_id = $_GET['course_id'] ?? 0;

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
?>

<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main">
    <!-- Navbar -->
    <nav class="js-nav-scroller navbar navbar-expand-lg navbar-sidebar navbar-vertical navbar-light bg-white border-end">
        <?php include '../includes/department/sidebar.php'; ?>
    </nav>
    <!-- End Navbar -->

    <!-- Content -->
    <div class="navbar-sidebar-aside-content content-space-1 content-space-md-2 px-lg-5 px-xl-5">
        <!-- Page Header -->
        <div class="page-header mb-3">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="courses.php">Courses</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Manage Course</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">Manage Course</h1>
                    <div class="mt-2">
                        <span class="badge bg-soft-<?php echo $course['approval_status'] === 'approved' ? 'success' : ($course['approval_status'] === 'rejected' ? 'danger' : 'warning'); ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $course['approval_status'])); ?>
                        </span>
                        <span class="text-muted ms-2"><?php echo $course['category_name']; ?> &bull; <?php echo $course['course_level']; ?></span>
                    </div>
                </div>
                <div class="col-sm-auto">
                    <div class="btn-group">
                        <a href="courses.php" class="btn btn-outline-secondary">
                            <i class="bi-arrow-left me-1"></i> Back to Courses
                        </a>
                        <a href="course-analytics.php?course_id=<?php echo $course_id; ?>" class="btn btn-outline-primary">
                            <i class="bi-graph-up me-1"></i> View Analytics
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row">
            <!-- Course Information -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-header-title">Course Information</h4>
                    </div>
                    <div class="card-body">
                        <form id="courseInfoForm">
                            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                            
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="courseTitle" class="form-label">Course Title</label>
                                    <input type="text" class="form-control" id="courseTitle" name="title" 
                                           value="<?php echo htmlspecialchars($course['title']); ?>" readonly>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="courseCategory" class="form-label">Category</label>
                                    <input type="text" class="form-control" id="courseCategory" 
                                           value="<?php echo htmlspecialchars($course['category_name']); ?>" readonly>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="courseLevel" class="form-label">Course Level</label>
                                    <input type="text" class="form-control" id="courseLevel" 
                                           value="<?php echo htmlspecialchars($course['course_level']); ?>" readonly>
                                </div>
                                
                                <div class="col-md-12">
                                    <label for="courseDescription" class="form-label">Short Description</label>
                                    <textarea class="form-control" id="courseDescription" name="short_description" rows="3" readonly><?php echo htmlspecialchars($course['short_description']); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="alert alert-info mt-3">
                                <i class="bi-info-circle me-1"></i>
                                Course content editing is handled through the <a href="initiate-course.php?course_id=<?php echo $course_id; ?>">course creation interface</a>.
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Instructor Management -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-header-title">Instructors</h4>
                    </div>
                    <div class="card-body">
                        <!-- Current Instructors -->
                        <div class="mb-4">
                            <h6 class="mb-3">Current Instructors</h6>
                            <div id="currentInstructorsList">
                                <?php if (empty($current_instructors)): ?>
                                    <p class="text-muted text-center py-3">No instructors assigned</p>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($current_instructors as $instructor): ?>
                                            <div class="list-group-item px-0">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-xs me-2">
                                                            <span class="avatar-initial bg-soft-primary text-primary rounded-circle">
                                                                <?php echo substr($instructor['first_name'], 0, 1) . substr($instructor['last_name'], 0, 1); ?>
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <div class="fw-medium"><?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?></div>
                                                            <small class="text-muted"><?php echo $instructor['is_primary'] ? 'Primary Instructor' : 'Co-instructor'; ?></small>
                                                        </div>
                                                    </div>
                                                    <div class="btn-group btn-group-sm">
                                                        <?php if (!$instructor['is_primary']): ?>
                                                            <button type="button" class="btn btn-outline-primary btn-sm" 
                                                                onclick="makeInstructorPrimary(<?php echo $course_id; ?>, <?php echo $instructor['instructor_id']; ?>)">
                                                                Make Primary
                                                            </button>
                                                        <?php endif; ?>
                                                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                                            onclick="removeInstructor(<?php echo $course_id; ?>, <?php echo $instructor['instructor_id']; ?>)">
                                                            Remove
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Add Instructor -->
                        <div>
                            <h6 class="mb-3">Add Instructor</h6>
                            <div class="row g-2 align-items-end">
                                <div class="col">
                                    <select class="form-select" id="availableInstructors">
                                        <option value="">Choose an instructor...</option>
                                        <?php foreach ($available_instructors as $instructor): ?>
                                            <option value="<?php echo $instructor['instructor_id']; ?>">
                                                <?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name'] . ' - ' . $instructor['email']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-primary" id="addInstructorBtn">
                                        <i class="bi-plus me-1"></i> Add
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Course Actions -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h4 class="card-header-title">Course Actions</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="initiate-course.php?course_id=<?php echo $course_id; ?>" class="btn btn-outline-primary">
                                <i class="bi-pencil me-1"></i> Edit Content
                            </a>
                            
                            <?php if ($course['approval_status'] === 'submitted_for_review' || $course['approval_status'] === 'under_review'): ?>
                                <button type="button" class="btn btn-outline-success" onclick="approveCourse(<?php echo $course_id; ?>)">
                                    <i class="bi-check-circle me-1"></i> Approve Course
                                </button>
                                <button type="button" class="btn btn-outline-warning" onclick="requestRevisions(<?php echo $course_id; ?>)">
                                    <i class="bi-arrow-counterclockwise me-1"></i> Request Revisions
                                </button>
                                <button type="button" class="btn btn-outline-danger" onclick="rejectCourse(<?php echo $course_id; ?>)">
                                    <i class="bi-x-circle me-1"></i> Reject Course
                                </button>
                            <?php endif; ?>
                            
                            <?php if ($course['status'] === 'Published'): ?>
                                <button type="button" class="btn btn-outline-warning" onclick="unpublishCourse(<?php echo $course_id; ?>)">
                                    <i class="bi-eye-slash me-1"></i> Unpublish
                                </button>
                            <?php endif; ?>
                            
                            <button type="button" class="btn btn-outline-danger" onclick="archiveCourse(<?php echo $course_id; ?>)">
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

<!-- JavaScript for instructor management and course actions -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const courseId = <?php echo $course_id; ?>;
        
        // Add instructor handler
        document.getElementById('addInstructorBtn').addEventListener('click', function() {
            const instructorId = document.getElementById('availableInstructors').value;
            
            if (!instructorId) {
                showError('Please select an instructor');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'assign_instructor');
            formData.append('course_id', courseId);
            formData.append('instructor_id', instructorId);
            formData.append('is_primary', '0');
            
            fetch('../backend/department/manage_instructors.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess(data.message);
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showError(data.message);
                }
            })
            .catch(error => {
                showError('Error adding instructor: ' + error.message);
            });
        });
    });
    
    // Instructor management functions
    function makeInstructorPrimary(courseId, instructorId) {
        const formData = new FormData();
        formData.append('action', 'assign_instructor');
        formData.append('course_id', courseId);
        formData.append('instructor_id', instructorId);
        formData.append('is_primary', '1');
        
        fetch('../backend/department/manage_instructors.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess(data.message);
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            showError('Error updating instructor role: ' + error.message);
        });
    }
    
    function removeInstructor(courseId, instructorId) {
        if (!confirm('Are you sure you want to remove this instructor?')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'remove_instructor');
        formData.append('course_id', courseId);
        formData.append('instructor_id', instructorId);
        
        fetch('../backend/department/manage_instructors.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess(data.message);
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            showError('Error removing instructor: ' + error.message);
        });
    }
    
    // Course action functions
    function approveCourse(courseId) {
        if (!confirm('Are you sure you want to approve this course?')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'approve');
        formData.append('course_id', courseId);
        
        fetch('../ajax/department/course_action_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess(data.message);
                setTimeout(() => window.location.href = 'courses.php', 1500);
            } else {
                showError(data.message);
            }
        });
    }
    
    function requestRevisions(courseId) {
        const comments = prompt('Please provide revision comments:');
        if (!comments) return;
        
        const formData = new FormData();
        formData.append('action', 'request_revisions');
        formData.append('course_id', courseId);
        formData.append('comments', comments);
        
        fetch('../ajax/department/course_action_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess(data.message);
                setTimeout(() => window.location.href = 'courses.php', 1500);
            } else {
                showError(data.message);
            }
        });
    }
    
    function rejectCourse(courseId) {
        const comments = prompt('Please provide rejection reason:');
        if (!comments) return;
        
        const formData = new FormData();
        formData.append('action', 'reject');
        formData.append('course_id', courseId);
        formData.append('comments', comments);
        
        fetch('../ajax/department/course_action_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess(data.message);
                setTimeout(() => window.location.href = 'courses.php', 1500);
            } else {
                showError(data.message);
            }
        });
    }
    
    function unpublishCourse(courseId) {
        if (!confirm('Are you sure you want to unpublish this course?')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'unpublish');
        formData.append('course_id', courseId);
        
        fetch('../ajax/department/course_action_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess(data.message);
                setTimeout(() => window.location.href = 'courses.php', 1500);
            } else {
                showError(data.message);
            }
        });
    }
    
    function archiveCourse(courseId) {
        if (!confirm('Are you sure you want to archive this course? This action can be undone.')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'archive');
        formData.append('course_id', courseId);
        
        fetch('../ajax/department/course_action_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess(data.message);
                setTimeout(() => window.location.href = 'courses.php', 1500);
            } else {
                showError(data.message);
            }
        });
    }
    
    // Utility functions
    function showSuccess(message) {
        // Use your notification system here
        alert('Success: ' + message);
    }
    
    function showError(message) {
        // Use your notification system here
        alert('Error: ' + message);
    }
</script>

<?php 
$conn->close();
include '../includes/department/footer.php'; 
?>