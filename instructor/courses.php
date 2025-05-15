<?php
// instructor/courses.php
require '../backend/session_start.php'; // Ensure session is started
require '../backend/config.php';

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    header('Location: landing.php');
    exit;
}

// Get instructor ID from session
$user_id = $_SESSION['user_id'] ?? 0;
if (!$user_id) {
    die("User not properly authenticated.");
}

// Get instructor ID from database
$query = "SELECT instructor_id FROM instructors WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($instructor_id);
    $stmt->fetch();
} else {
    die("Instructor record not found.");
}
$stmt->close();

// Fetch all courses assigned to this instructor
$courses_sql = "SELECT 
    c.course_id, 
    c.title, 
    c.short_description, 
    c.thumbnail, 
    c.status, 
    c.approval_status, 
    c.creation_step,
    d.name AS department_name,
    s.name AS subcategory_name,
    (SELECT COUNT(*) FROM course_sections cs WHERE cs.course_id = c.course_id) AS section_count,
    (SELECT COUNT(*) FROM section_topics st JOIN course_sections cs ON st.section_id = cs.section_id WHERE cs.course_id = c.course_id) AS topic_count,
    (SELECT COUNT(*) FROM section_quizzes sq JOIN course_sections cs ON sq.section_id = cs.section_id WHERE cs.course_id = c.course_id) AS quiz_count,
    CASE WHEN ci.is_primary = 1 THEN 'Primary' ELSE 'Co-instructor' END AS instructor_role
FROM 
    courses c
JOIN 
    course_instructors ci ON c.course_id = ci.course_id
LEFT JOIN 
    subcategories s ON c.subcategory_id = s.subcategory_id
LEFT JOIN 
    departments d ON c.department_id = d.department_id
WHERE 
    ci.instructor_id = ? 
    AND c.deleted_at IS NULL
ORDER BY 
    c.updated_at DESC";

$stmt = $conn->prepare($courses_sql);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$courses_result = $stmt->get_result();

// Store all courses in an array
$courses = [];
while ($course = $courses_result->fetch_assoc()) {
    $courses[] = $course;
}
$stmt->close();

// Count courses by status for metrics
$total_courses = count($courses);
$draft_courses = 0;
$review_courses = 0;
$approved_courses = 0;

foreach ($courses as $course) {
    if ($course['status'] == 'Draft') {
        $draft_courses++;
    }
    
    if ($course['approval_status'] == 'submitted_for_review' || $course['approval_status'] == 'under_review') {
        $review_courses++;
    }
    
    if ($course['approval_status'] == 'approved') {
        $approved_courses++;
    }
}

// Function to handle course thumbnails
function displayCourseImage($thumbnail) {
    $default_image = '../assets/images/default-course.jpg';
    if (empty($thumbnail)) {
        return $default_image;
    }
    
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($thumbnail, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_extensions)) {
        return $default_image;
    }
    
    $upload_paths = ['../Uploads/thumbnails/', '../Uploads/', 'Uploads/thumbnails/', 'Uploads/'];
    foreach ($upload_paths as $path) {
        $full_path = $path . $thumbnail;
        if (file_exists($full_path)) {
            return $full_path;
        }
    }
    
    return $default_image;
}

// Function to get approval status badge HTML
function getApprovalStatusBadge($status) {
    switch ($status) {
        case 'pending':
            return '<span class="badge bg-secondary">Pending</span>';
        case 'revisions_requested':
            return '<span class="badge bg-warning">Revisions Requested</span>';
        case 'submitted_for_review':
            return '<span class="badge bg-info">Submitted for Review</span>';
        case 'under_review':
            return '<span class="badge bg-primary">Under Review</span>';
        case 'approved':
            return '<span class="badge bg-success">Approved</span>';
        case 'rejected':
            return '<span class="badge bg-danger">Rejected</span>';
        default:
            return '<span class="badge bg-secondary">Pending</span>';
    }
}

// Function to calculate completion percentage
function getStepProgressPercentage($step) {
    // Assuming 6 steps total: Basic info, Requirements, Structure, Content, Settings, Review
    $totalSteps = 6;
    $currentStep = min(max(1, intval($step)), $totalSteps);
    return round(($currentStep / $totalSteps) * 100);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Instructor | Learnix - My Courses</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Instructor dashboard to manage assigned courses" />
    <meta name="author" content="Learnix Team" />
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <link href="assets/css/vendor/dataTables.bootstrap5.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/vendor/responsive.bootstrap5.css" rel="stylesheet" type="text/css" />
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
        .course-thumbnail {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 4px;
        }
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
        }
        .progress-sm {
            height: 5px;
        }
        .workflow-icon {
            font-size: 2rem;
            color: #3bafda;
        }
        .workflow-step {
            position: relative;
            padding-bottom: 15px;
        }
        .workflow-step:not(:last-child):after {
            content: '';
            position: absolute;
            top: 40px;
            left: 50%;
            height: 30px;
            border-left: 2px dashed #e3eaef;
        }
        .info-card {
            border-left: 4px solid #3bafda;
        }
        .department-badge {
            background-color: #eef2f7;
            color: #6c757d;
            border-radius: 4px;
            padding: 0.25em 0.5em;
            font-size: 0.75em;
            font-weight: 600;
        }
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
    <div class="wrapper">
        <?php include '../includes/instructor-sidebar.php'; ?>
        
        <div class="content-page">
            <div class="content">
                <?php include '../includes/instructor-topnavbar.php'; ?>
                
                <div class="container-fluid">
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
                                <h4 class="page-title">My Courses</h4>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Info Alert -->
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info info-card">
                                <i class="mdi mdi-information-outline me-2"></i>
                                <strong>Course Assignment:</strong> As an instructor, you can only manage courses assigned to you by department heads. If you need a new course, please contact your department head.
                            </div>
                        </div>
                    </div>
                    
                    <!-- Course Workflow -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="header-title">Course Development Workflow</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col workflow-step">
                                            <div class="workflow-icon mb-2">
                                                <i class="mdi mdi-briefcase-outline"></i>
                                            </div>
                                            <h5>Assignment</h5>
                                            <p class="text-muted mb-0">Department head assigns you to a course</p>
                                        </div>
                                        <div class="col workflow-step">
                                            <div class="workflow-icon mb-2">
                                                <i class="mdi mdi-pencil-outline"></i>
                                            </div>
                                            <h5>Development</h5>
                                            <p class="text-muted mb-0">Create content, quizzes, and materials</p>
                                        </div>
                                        <div class="col workflow-step">
                                            <div class="workflow-icon mb-2">
                                                <i class="mdi mdi-send-outline"></i>
                                            </div>
                                            <h5>Submission</h5>
                                            <p class="text-muted mb-0">Submit completed course for review</p>
                                        </div>
                                        <div class="col workflow-step">
                                            <div class="workflow-icon mb-2">
                                                <i class="mdi mdi-clipboard-check-outline"></i>
                                            </div>
                                            <h5>Review</h5>
                                            <p class="text-muted mb-0">Department head reviews and provides feedback</p>
                                        </div>
                                        <div class="col workflow-step">
                                            <div class="workflow-icon mb-2">
                                                <i class="mdi mdi-check-circle-outline"></i>
                                            </div>
                                            <h5>Approval</h5>
                                            <p class="text-muted mb-0">Course is approved and published to students</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Summary Cards -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card widget-inline">
                                <div class="card-body p-0">
                                    <div class="row g-0">
                                        <div class="col-sm-6 col-xl-3">
                                            <div class="card shadow-none m-0">
                                                <div class="card-body text-center">
                                                    <i class="dripicons-view-list text-muted" style="font-size: 24px;"></i>
                                                    <h3><span><?php echo $total_courses; ?></span></h3>
                                                    <p class="text-muted font-15 mb-0">Total Assigned Courses</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-xl-3">
                                            <div class="card shadow-none m-0 border-start">
                                                <div class="card-body text-center">
                                                    <i class="dripicons-pencil text-muted" style="font-size: 24px;"></i>
                                                    <h3><span><?php echo $draft_courses; ?></span></h3>
                                                    <p class="text-muted font-15 mb-0">Draft Courses</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-xl-3">
                                            <div class="card shadow-none m-0 border-start">
                                                <div class="card-body text-center">
                                                    <i class="dripicons-hourglass text-muted" style="font-size: 24px;"></i>
                                                    <h3><span><?php echo $review_courses; ?></span></h3>
                                                    <p class="text-muted font-15 mb-0">In Review</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-xl-3">
                                            <div class="card shadow-none m-0 border-start">
                                                <div class="card-body text-center">
                                                    <i class="dripicons-checkmark text-muted" style="font-size: 24px;"></i>
                                                    <h3><span><?php echo $approved_courses; ?></span></h3>
                                                    <p class="text-muted font-15 mb-0">Approved Courses</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Course Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <table class="table table-centered table-hover" id="courses-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Course</th>
                                                <th>Department/Category</th>
                                                <th>Role</th>
                                                <th>Status</th>
                                                <th>Progress</th>
                                                <th style="width: 120px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($courses) > 0): ?>
                                                <?php foreach ($courses as $course): ?>
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
                                                                <div style="min-width: 0;">
                                                                    <h5 class="m-0 font-16 course-title" title="<?php echo htmlspecialchars($course['title']); ?>">
                                                                        <?php echo htmlspecialchars($course['title']); ?>
                                                                    </h5>
                                                                    <p class="mb-0 text-muted course-description" title="<?php echo htmlspecialchars($course['short_description'] ?? ''); ?>">
                                                                        <?php echo htmlspecialchars($course['short_description'] ?? 'No description provided'); ?>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="department-badge mb-1 d-inline-block">
                                                                <?php echo htmlspecialchars($course['department_name'] ?? 'Unassigned'); ?>
                                                            </span>
                                                            <br>
                                                            <small class="text-muted">
                                                                <?php echo htmlspecialchars($course['subcategory_name'] ?? 'Uncategorized'); ?>
                                                            </small>
                                                        </td>
                                                        <td>
                                                            <span class="badge <?php echo $course['instructor_role'] === 'Primary' ? 'bg-primary' : 'bg-info'; ?>">
                                                                <?php echo $course['instructor_role']; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php echo getApprovalStatusBadge($course['approval_status']); ?>
                                                            <?php if ($course['status'] === 'Published'): ?>
                                                                <br><span class="badge bg-success mt-1">Published</span>
                                                            <?php else: ?>
                                                                <br><span class="badge bg-secondary mt-1">Draft</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php 
                                                            $progressPercentage = getStepProgressPercentage($course['creation_step']);
                                                            $progressClass = 'bg-danger';
                                                            if ($progressPercentage > 33) $progressClass = 'bg-warning';
                                                            if ($progressPercentage > 66) $progressClass = 'bg-success';
                                                            if ($course['approval_status'] === 'approved') $progressClass = 'bg-success';
                                                            ?>
                                                            <div class="progress progress-sm">
                                                                <div class="progress-bar <?php echo $progressClass; ?>" 
                                                                     role="progressbar" 
                                                                     style="width: <?php echo $progressPercentage; ?>%" 
                                                                     aria-valuenow="<?php echo $progressPercentage; ?>" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="100">
                                                                </div>
                                                            </div>
                                                            <small class="text-muted"><?php echo $progressPercentage; ?>% complete</small>
                                                        </td>
                                                        <td class="table-action">
                                                            <a href="course-creator.php?course_id=<?php echo $course['course_id']; ?>"
                                                               class="action-icon" title="Edit Course">
                                                                <i class="mdi mdi-square-edit-outline"></i>
                                                            </a>
                                                            
                                                            <?php if ($course['approval_status'] === 'pending' || $course['approval_status'] === 'revisions_requested'): ?>
                                                                <?php if ($course['instructor_role'] === 'Primary'): ?>
                                                                    <a href="javascript:void(0);"
                                                                       class="action-icon text-primary submit-for-review"
                                                                       data-course-id="<?php echo $course['course_id']; ?>"
                                                                       data-course-title="<?php echo htmlspecialchars($course['title']); ?>"
                                                                       title="Submit for Review">
                                                                        <i class="mdi mdi-send"></i>
                                                                    </a>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                            
                                                            <a href="javascript:void(0);"
                                                               class="action-icon preview-course"
                                                               data-course-id="<?php echo $course['course_id']; ?>"
                                                               title="Preview Course">
                                                                <i class="mdi mdi-eye-outline"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center">
                                                        <div class="py-4">
                                                            <i class="mdi mdi-book-multiple-outline display-4 text-muted mb-3"></i>
                                                            <h4>No Courses Assigned</h4>
                                                            <p class="text-muted">You haven't been assigned to any courses yet. Please contact your department head.</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit for Review Modal -->
                    <div class="modal fade" id="submitReviewModal" tabindex="-1" aria-labelledby="submitReviewModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="submitReviewModalLabel">Submit Course for Review</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="text-center mb-4">
                                        <i class="mdi mdi-send-circle-outline text-primary" style="font-size: 3rem;"></i>
                                    </div>
                                    <p>You are about to submit this course for review by your department head:</p>
                                    <p class="mb-3"><strong>Course:</strong> <span id="courseToSubmit"></span></p>
                                    
                                    <div class="alert alert-info">
                                        <i class="mdi mdi-information-outline me-2"></i>
                                        After submission, you can still edit the course while waiting for review.
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="reviewNotes" class="form-label">Additional Notes (Optional)</label>
                                        <textarea class="form-control" id="reviewNotes" rows="3" placeholder="Add any notes for the reviewer"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary" id="confirmSubmitReview">Submit for Review</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
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
                                        <!-- Content will be loaded dynamically -->
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                    <a href="#" id="previewEditButton" class="btn btn-primary">Edit Course</a>
                                    <button type="button" id="previewSubmitButton" class="btn btn-success">Submit for Review</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        <?php include '../includes/instructor-footer.php'; ?>
        
        <?php include '../includes/instructor-darkmode.php'; ?>
        
    <script src="assets/js/vendor.min.js"></script>
<script src="assets/js/app.min.js"></script>

<!-- Add DataTables scripts -->
<script src="assets/js/vendor/jquery.dataTables.min.js"></script>
<script src="assets/js/vendor/dataTables.bootstrap5.js"></script>
<script src="assets/js/vendor/dataTables.responsive.min.js"></script>
<script src="assets/js/vendor/responsive.bootstrap5.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize DataTable with fallback
        if (typeof $.fn.DataTable === 'function') {
            $('#courses-table').DataTable({
                "paging": true,
                "ordering": true,
                "info": true,
                "searching": true,
                "language": {
                    "paginate": {
                        "previous": "<i class='mdi mdi-chevron-left'>",
                        "next": "<i class='mdi mdi-chevron-right'>"
                    },
                    "emptyTable": "No courses available"
                },
                "drawCallback": function() {
                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
                }
            });
        } else {
            console.log("DataTables not loaded - using standard table functionality");
        }
        
        // Utility functions for overlays and alerts
        function showOverlay(message = null) {
            const existingOverlay = document.querySelector('.custom-overlay');
            if (existingOverlay) {
                existingOverlay.remove();
            }
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
        
        // Submit for review functionality
        let courseIdToSubmit = null;
        
        function showSubmitReviewModal(courseId, courseTitle) {
            courseIdToSubmit = courseId;
            document.getElementById('courseToSubmit').textContent = courseTitle || 'This course';
            document.getElementById('reviewNotes').value = '';
            const submitModal = new bootstrap.Modal(document.getElementById('submitReviewModal'));
            submitModal.show();
        }
        
        function submitCourseForReview(courseId, notes) {
            showOverlay('Submitting course for review...');
            fetch(`../ajax/courses/submit_for_review.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ 
                    course_id: courseId,
                    notes: notes 
                })
            })
            .then(response => response.json())
            .then(data => {
                removeOverlay();
                if (data.success) {
                    showAlert('success', 'Course successfully submitted for review!');
                    // Refresh the page after a delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showAlert('danger', data.message || 'Failed to submit course for review');
                }
            })
            .catch(error => {
                removeOverlay();
                console.error('Error:', error);
                showAlert('danger', 'An error occurred while submitting the course for review');
            });
        }
        
        // Event handlers for submit review
        $(document).on('click', '.submit-for-review', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const courseId = $(this).data('course-id');
            const courseTitle = $(this).data('course-title');
            showSubmitReviewModal(courseId, courseTitle);
        });
        
        document.getElementById('confirmSubmitReview').addEventListener('click', function() {
            if (courseIdToSubmit) {
                const notes = document.getElementById('reviewNotes').value;
                submitCourseForReview(courseIdToSubmit, notes);
                const submitModal = bootstrap.Modal.getInstance(document.getElementById('submitReviewModal'));
                submitModal.hide();
            }
        });
        
        // Course preview functionality
        const previewModal = new bootstrap.Modal(document.getElementById('coursePreviewModal'));
        
        $(document).on('click', '.preview-course', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const courseId = $(this).data('course-id');
            openCoursePreview(courseId);
        });
        
        $(document).on('click', '.course-row', function(e) {
            if (e.target.closest('.table-action') || e.target.closest('.action-icon')) {
                return;
            }
            const courseId = $(this).data('course-id');
            openCoursePreview(courseId);
        });
        
        // Helper function to safely escape HTML
        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
        
        // Course preview functionality - updated version
        function openCoursePreview(courseId) {
            $('#coursePreviewLoader').show();
            $('#coursePreviewContent').hide();
            previewModal.show();
            
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
            // Set edit button link
            $('#previewEditButton').attr('href', `course-creator.php?course_id=${course.course_id}`);
            
            // Show/hide submit button based on status and role
            if ((course.approval_status === 'pending' || course.approval_status === 'revisions_requested') && 
                course.instructor_role === 'Primary') {
                $('#previewSubmitButton').show();
                $('#previewSubmitButton').data('course-id', course.course_id);
                $('#previewSubmitButton').data('course-title', course.title);
            } else {
                $('#previewSubmitButton').hide();
            }
            
            // Safely handle dates
            const createdDate = course.created_at ? new Date(course.created_at).toLocaleDateString() : 'N/A';
            const updatedDate = course.updated_at ? new Date(course.updated_at).toLocaleDateString() : 'N/A';
            
            // Safely get completion percentage
            const completionPercentage = Math.round(parseFloat(course.completion_percentage || 0));
            
            // Safely handle creation step
            const creationStep = parseInt(course.creation_step || 0);
            
            // Build preview content HTML with proper escaping
            let previewHtml = `
                <div class="row">
                    <div class="col-md-4">
                        ${course.thumbnail ? 
                            `<img src="../Uploads/thumbnails/${escapeHtml(course.thumbnail)}" alt="Course Thumbnail" class="img-fluid rounded">` : 
                            `<div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 200px; width: 100%;">
                                <i class="mdi mdi-book-open-page-variant text-muted" style="font-size: 48px;"></i>
                             </div>`
                        }
                    </div>
                    <div class="col-md-8">
                        <h4 class="mt-0">${escapeHtml(course.title || 'Untitled Course')}</h4>
                        <p class="text-muted">${escapeHtml(course.short_description || 'No description provided')}</p>
                        <div class="department-badge mb-2">${escapeHtml(course.department_name || 'Unassigned Department')}</div>
                        <div class="badge bg-primary me-1">${escapeHtml(course.course_level || 'All Levels')}</div>
                        <div class="badge bg-info me-1">${escapeHtml(course.subcategory_name || 'Uncategorized')}</div>
                        <div class="badge bg-success me-1">${course.price ? "₵" + parseFloat(course.price).toFixed(2) : 'Free'}</div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="alert alert-secondary">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="mdi mdi-information-outline text-dark fs-3"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mt-0">Review Status</h5>
                                    <div class="badge ${getStatusClass(course.approval_status)} mb-2">
                                        ${formatStatus(course.approval_status || 'pending')}
                                    </div>
                                    <p class="mb-1">${getStatusMessage(course.approval_status || 'pending')}</p>
                                    <div class="text-muted">${escapeHtml(getFeedbackMessage(course))}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h5>Course Statistics</h5>
                        <ul class="list-unstyled">
                            <li><i class="mdi mdi-book-open-variant me-1"></i> ${parseInt(course.section_count || 0)} ${parseInt(course.section_count) === 1 ? 'Section' : 'Sections'}</li>
                            <li><i class="mdi mdi-bookmark-outline me-1"></i> ${parseInt(course.topic_count || 0)} ${parseInt(course.topic_count) === 1 ? 'Topic' : 'Topics'}</li>
                            <li><i class="mdi mdi-help-circle-outline me-1"></i> ${parseInt(course.quiz_count || 0)} ${parseInt(course.quiz_count) === 1 ? 'Quiz' : 'Quizzes'}</li>
                            <li><i class="mdi mdi-calendar me-1"></i> Created on: ${createdDate}</li>
                            <li><i class="mdi mdi-update me-1"></i> Last updated: ${updatedDate}</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5>Completion Status</h5>
                        <div class="progress mb-2">
                            <div class="progress-bar ${getProgressClass(completionPercentage)}" 
                                 role="progressbar" 
                                 style="width: ${completionPercentage}%" 
                                 aria-valuenow="${completionPercentage}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                 ${completionPercentage}%
                            </div>
                        </div>
                        <div class="text-muted small">
                            <ul>
                                <li>Basic Information: ${creationStep >= 1 ? 'Complete' : 'Not Complete'}</li>
                                <li>Learning Outcomes & Requirements: ${creationStep >= 2 ? 'Complete' : 'Not Complete'}</li>
                                <li>Course Structure: ${creationStep >= 3 ? 'Complete' : 'Not Complete'}</li>
                                <li>Content: ${creationStep >= 4 ? 'Complete' : 'Not Complete'}</li>
                                <li>Settings: ${creationStep >= 5 ? 'Complete' : 'Not Complete'}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            `;
            
            // Update preview content and display
            $('#coursePreviewContent').html(previewHtml);
            $('#coursePreviewLoader').hide();
            $('#coursePreviewContent').show();
        }
        
        // Helper functions for preview
        function getStatusClass(status) {
            switch (status) {
                case 'pending': return 'bg-secondary';
                case 'revisions_requested': return 'bg-warning';
                case 'submitted_for_review': return 'bg-info';
                case 'under_review': return 'bg-primary';
                case 'approved': return 'bg-success';
                case 'rejected': return 'bg-danger';
                default: return 'bg-secondary';
            }
        }
        
        function formatStatus(status) {
            if (!status) return 'PENDING';
            return String(status).replace(/_/g, ' ').toUpperCase();
        }
        
        function getStatusMessage(status) {
            switch (status) {
                case 'pending': return 'This course is still pending submission for review.';
                case 'revisions_requested': return 'Revisions have been requested by the department head.';
                case 'submitted_for_review': return 'This course has been submitted for review.';
                case 'under_review': return 'This course is currently being reviewed.';
                case 'approved': return 'This course has been approved!';
                case 'rejected': return 'This course has been rejected.';
                default: return 'Status information unavailable.';
            }
        }
        
        function getFeedbackMessage(course) {
            if (!course || !course.approval_status) return '';
            
            switch (course.approval_status) {
                case 'pending': 
                    return 'Submit this course for review when you feel it is ready.';
                case 'revisions_requested': 
                    return course.review_notes || 'Please make the necessary changes and resubmit.';
                case 'submitted_for_review': 
                    return 'The department head will review your submission shortly.';
                case 'under_review': 
                    return 'The department head is reviewing your course.';
                case 'approved': 
                    return course.review_notes || 'Congratulations! Your course has been approved.';
                case 'rejected': 
                    return course.review_notes || 'Please contact your department head for more information.';
                default: 
                    return '';
            }
        }
        
        function getProgressClass(percentage) {
            if (isNaN(percentage)) percentage = 0;
            if (percentage < 25) return 'bg-danger';
            if (percentage < 50) return 'bg-warning';
            if (percentage < 75) return 'bg-info';
            return 'bg-success';
        }
        
        function showPreviewError(message) {
            $('#coursePreviewLoader').hide();
            $('#coursePreviewContent').html(`
                <div class="alert alert-danger">
                    <i class="mdi mdi-alert-circle-outline me-2"></i>
                    ${escapeHtml(message)}
                </div>
            `).show();
        }
        
        // Handle submit button in preview modal
        $('#previewSubmitButton').on('click', function() {
            const courseId = $(this).data('course-id');
            const courseTitle = $(this).data('course-title');
            previewModal.hide();
            showSubmitReviewModal(courseId, courseTitle);
        });
    });
</script>
    </body>
</html>