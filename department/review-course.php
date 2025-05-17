<?php
// Path: department/review-course.php
include '../includes/department/header.php';
require_once '../backend/config.php';

// Check if user is logged in as department head
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    header('Location: ../admin/login.php');
    exit;
}

// Get course ID from URL
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// If no course ID is provided, redirect to courses page
if ($course_id === 0) {
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

// Check if the course belongs to the department head's department
$course_query = "SELECT cs.*, cs.course_id, cs.title, cs.short_description, cs.full_description, 
                 cs.status, cs.approval_status, cs.thumbnail, cs.price, cs.course_level, cs.certificate_enabled 
                 FROM courses cs 
                 WHERE cs.course_id = ? AND cs.department_id = ? AND cs.deleted_at IS NULL";
$course_stmt = $conn->prepare($course_query);
$course_stmt->bind_param("ii", $course_id, $department_id);
$course_stmt->execute();
$course_result = $course_stmt->get_result();

if ($course_result->num_rows === 0) {
    header('Location: courses.php');
    exit;
}

$course = $course_result->fetch_assoc();

// Set page title
$page_title = "Review: " . htmlspecialchars($course['title']);

// Get the primary instructor
$instructor_query = "SELECT u.user_id, u.first_name, u.last_name, u.username, u.email, u.profile_pic, 
                     ci.is_primary 
                     FROM course_instructors ci
                     JOIN instructors i ON ci.instructor_id = i.instructor_id
                     JOIN users u ON i.user_id = u.user_id
                     WHERE ci.course_id = ? AND ci.deleted_at IS NULL
                     ORDER BY ci.is_primary DESC
                     LIMIT 1";
$instructor_stmt = $conn->prepare($instructor_query);
$instructor_stmt->bind_param("i", $course_id);
$instructor_stmt->execute();
$instructor_result = $instructor_stmt->get_result();
$primary_instructor = $instructor_result->fetch_assoc();

// Get review history
$review_history_query = "SELECT crh.*, u.first_name, u.last_name 
                        FROM course_review_history crh
                        JOIN users u ON crh.reviewed_by = u.user_id
                        WHERE crh.course_id = ?
                        ORDER BY crh.review_date DESC
                        LIMIT 10";
$history_stmt = $conn->prepare($review_history_query);
$history_stmt->bind_param("i", $course_id);
$history_stmt->execute();
$history_result = $history_stmt->get_result();
$review_history = [];
while ($history = $history_result->fetch_assoc()) {
    $review_history[] = $history;
}
?>



    <!-- Custom CSS for review mode -->
    <style>
        .review-banner {
            background-color: #3e7bfa;
            color: white;
            padding: 8px 15px;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .course-content-nav .nav-link.active {
            background-color: #3e7bfa;
            color: white !important;
        }

        .content-viewer {
            min-height: 400px;
            border: 1px solid #e2e8f0;
            border-radius: 0.25rem;
            padding: 20px;
        }

        .resource-item {
            border-left: 3px solid #3e7bfa;
        }

        .course-section {
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 15px;
        }

        .course-section:last-child {
            border-bottom: none;
        }

        .topic-item {
            cursor: pointer;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 5px;
            transition: all 0.2s;
        }

        .topic-item:hover {
            background-color: #f0f4ff;
        }

        .topic-item.active {
            background-color: #e6ecff;
            border-left: 3px solid #3e7bfa;
        }

        .quiz-item {
            border-left: 3px solid #3e7bfa;
        }

        /* Video container and alert styles */
        .video-container {
            border-radius: 8px;
            overflow: hidden;
            background-color: #f8f9fa;
            padding: 15px;
        }

        /* Decision buttons */
        .decision-btn {
            width: 100%;
            margin-bottom: 10px;
            font-weight: 500;
        }

        /* Review status indicator */
        .review-status {
            padding: 8px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .review-status.pending {
            background-color: rgba(255, 193, 7, 0.2);
            border-left: 3px solid #ffc107;
        }

        .review-status.revisions {
            background-color: rgba(255, 123, 0, 0.2);
            border-left: 3px solid #ff7b00;
        }

        .review-status.under-review {
            background-color: rgba(13, 110, 253, 0.2);
            border-left: 3px solid #0d6efd;
        }

        .review-status.approved {
            background-color: rgba(25, 135, 84, 0.2);
            border-left: 3px solid #198754;
        }

        .review-status.rejected {
            background-color: rgba(220, 53, 69, 0.2);
            border-left: 3px solid #dc3545;
        }

        /* Review history */
        .history-item {
            padding: 10px 15px;
            border-left: 3px solid #3e7bfa;
            margin-bottom: 10px;
            background-color: #f8f9fa;
        }

        /* Review form */
        .review-form {
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
            margin-top: 15px;
        }
    </style>


<body class="loading" data-layout-color="light" data-leftbar-theme="dark" data-layout-mode="fluid">
    <!-- Review banner -->
    <div class="review-banner">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <strong>REVIEW MODE:</strong> Evaluate course content before approval
                </div>
                <div class="col-md-4 text-end">
                    <a href="courses.php" class="btn btn-light btn-sm">
                        <i class="mdi mdi-arrow-left"></i> Back to Courses
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Begin page -->
    <div class="wrapper mt-3">
        <div class="container-fluid">
            <!-- Course Header -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h2><?php echo htmlspecialchars($course['title']); ?></h2>
                                    <p class="text-muted"><?php echo htmlspecialchars($course['short_description']); ?></p>
                                    <div class="mt-3">
                                        <!-- Primary instructor info -->
                                        <div class="d-flex align-items-center mb-3">
                                            <img src="<?php echo !empty($primary_instructor['profile_pic']) ? 
                                                    '../uploads/profile/' . $primary_instructor['profile_pic'] : 
                                                    '../assets/images/users/default.png'; ?>" 
                                                 class="rounded-circle me-2" width="40" height="40" alt="Instructor">
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($primary_instructor['first_name'] . ' ' . $primary_instructor['last_name']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($primary_instructor['email']); ?></small>
                                            </div>
                                        </div>
                                        
                                        <!-- Course metadata -->
                                        <div class="mb-2">
                                            <h5 class="text-primary">
                                                <?php if ($course['price'] > 0): ?>
                                                    ₵<?php echo number_format($course['price'], 2); ?>
                                                <?php else: ?>
                                                    Free
                                                <?php endif; ?>
                                            </h5>
                                        </div>
                                        <span class="badge bg-primary me-1">
                                            <?php echo htmlspecialchars($course['course_level']); ?>
                                        </span>
                                        <?php if ($course['certificate_enabled']): ?>
                                            <span class="badge bg-success">Certificate Available</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <?php if ($course['thumbnail']): ?>
                                        <img src="../uploads/thumbnails/<?php echo $course['thumbnail']; ?>"
                                            alt="Course Thumbnail" class="img-fluid rounded" style="max-height: 180px;">
                                    <?php else: ?>
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                            style="height: 180px; width: 100%;">
                                            <span class="text-muted">No thumbnail</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="row">
                <!-- Course Navigation Sidebar and Review Controls -->
                <div class="col-md-4 col-lg-3">
                    <!-- Review Status Card -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="mb-3">Review Status</h5>
                            
                            <?php
                            // Display status based on approval_status
                            $status_class = '';
                            $status_icon = '';
                            $status_text = '';
                            
                            switch($course['approval_status']) {
                                case 'pending':
                                    $status_class = 'pending';
                                    $status_icon = 'mdi-clock-outline';
                                    $status_text = 'Pending Review';
                                    break;
                                case 'revisions_requested':
                                    $status_class = 'revisions';
                                    $status_icon = 'mdi-file-document-edit-outline';
                                    $status_text = 'Revisions Requested';
                                    break;
                                case 'submitted_for_review':
                                    $status_class = 'under-review';
                                    $status_icon = 'mdi-eye-outline';
                                    $status_text = 'Submitted for Review';
                                    break;
                                case 'under_review':
                                    $status_class = 'under-review';
                                    $status_icon = 'mdi-magnify';
                                    $status_text = 'Under Review';
                                    break;
                                case 'approved':
                                    $status_class = 'approved';
                                    $status_icon = 'mdi-check-circle-outline';
                                    $status_text = 'Approved';
                                    break;
                                case 'rejected':
                                    $status_class = 'rejected';
                                    $status_icon = 'mdi-close-circle-outline';
                                    $status_text = 'Rejected';
                                    break;
                                default:
                                    $status_class = 'pending';
                                    $status_icon = 'mdi-help-circle-outline';
                                    $status_text = 'Unknown Status';
                            }
                            ?>
                            
                            <div class="review-status <?php echo $status_class; ?>">
                                <div class="d-flex align-items-center">
                                    <i class="mdi <?php echo $status_icon; ?> me-2"></i>
                                    <strong><?php echo $status_text; ?></strong>
                                </div>
                            </div>
                            
                            <!-- Decision buttons -->
                            <div class="review-actions">
                                <button class="btn btn-success decision-btn" id="approveBtn" data-bs-toggle="modal" data-bs-target="#approveModal">
                                    <i class="mdi mdi-check-circle-outline me-1"></i> Approve Course
                                </button>
                                
                                <button class="btn btn-warning decision-btn" id="revisionsBtn" data-bs-toggle="modal" data-bs-target="#revisionsModal">
                                    <i class="mdi mdi-file-document-edit-outline me-1"></i> Request Revisions
                                </button>
                                
                                <button class="btn btn-danger decision-btn" id="rejectBtn" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                    <i class="mdi mdi-close-circle-outline me-1"></i> Reject Course
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Course Navigation Card -->
                    <div class="card">
                        <div class="card-body">
                            <!-- Course Navigation Tabs -->
                            <ul class="nav nav-pills nav-fill bg-light rounded mb-3 course-content-nav" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#curriculum-tab" role="tab">
                                        <i class="mdi mdi-book-outline me-1"></i> Curriculum
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" data-bs-toggle="tab" href="#overview-tab" role="tab">
                                        <i class="mdi mdi-information-outline me-1"></i> Overview
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" data-bs-toggle="tab" href="#history-tab" role="tab">
                                        <i class="mdi mdi-history me-1"></i> History
                                    </a>
                                </li>
                            </ul>

                            <!-- Tab Content -->
                            <div class="tab-content">
                                <!-- Curriculum Tab -->
                                <div class="tab-pane fade show active" id="curriculum-tab" role="tabpanel">
                                    <?php
                                    // Fetch sections for this course
                                    $sections_query = "SELECT * FROM course_sections WHERE course_id = ? ORDER BY position ASC";
                                    $stmt = $conn->prepare($sections_query);
                                    $stmt->bind_param("i", $course_id);
                                    $stmt->execute();
                                    $sections_result = $stmt->get_result();

                                    if ($sections_result->num_rows === 0) {
                                        echo '<div class="alert alert-info">No content has been added to this course yet.</div>';
                                    } else {
                                        while ($section = $sections_result->fetch_assoc()):
                                    ?>
                                            <div class="course-section">
                                                <h5><?php echo htmlspecialchars($section['title']); ?></h5>

                                                <div class="topics-list">
                                                    <?php
                                                    // Fetch topics for this section
                                                    $topics_query = "SELECT * FROM section_topics WHERE section_id = ? ORDER BY position ASC";
                                                    $topics_stmt = $conn->prepare($topics_query);
                                                    $topics_stmt->bind_param("i", $section['section_id']);
                                                    $topics_stmt->execute();
                                                    $topics_result = $topics_stmt->get_result();

                                                    // Fetch quizzes for this section
                                                    $quizzes_query = "SELECT * FROM section_quizzes WHERE section_id = ?";
                                                    $quizzes_stmt = $conn->prepare($quizzes_query);
                                                    $quizzes_stmt->bind_param("i", $section['section_id']);
                                                    $quizzes_stmt->execute();
                                                    $quizzes_result = $quizzes_stmt->get_result();

                                                    $has_content = false;

                                                    // Output topics
                                                    while ($topic = $topics_result->fetch_assoc()):
                                                        $has_content = true;
                                                    ?>
                                                        <div class="topic-item" data-topic-id="<?php echo $topic['topic_id']; ?>">
                                                            <div class="d-flex align-items-center">
                                                                <i class="mdi mdi-file-document-outline me-2"></i>
                                                                <div>
                                                                    <?php echo htmlspecialchars($topic['title']); ?>
                                                                    <?php if ($topic['is_previewable']): ?>
                                                                        <span class="badge bg-success ms-1">Preview</span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php
                                                    endwhile;

                                                    // Output quizzes
                                                    while ($quiz = $quizzes_result->fetch_assoc()):
                                                        $has_content = true;
                                                    ?>
                                                        <div class="topic-item quiz-item" data-quiz-id="<?php echo $quiz['quiz_id']; ?>">
                                                            <div class="d-flex align-items-center">
                                                                <i class="mdi mdi-help-circle-outline me-2 text-primary"></i>
                                                                <div>
                                                                    <?php echo htmlspecialchars($quiz['quiz_title']); ?>
                                                                    <span class="badge bg-primary ms-1">Quiz</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php
                                                    endwhile;

                                                    // Close statement for topics and quizzes
                                                    $topics_stmt->close();
                                                    $quizzes_stmt->close();

                                                    if (!$has_content) {
                                                        echo '<div class="alert alert-warning">No content in this section yet.</div>';
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                    <?php
                                        endwhile;
                                    }
                                    $stmt->close();
                                    ?>
                                </div>

                                <!-- Overview Tab -->
                                <div class="tab-pane fade" id="overview-tab" role="tabpanel">
                                    <div class="overview-content">
                                        <h5>About This Course</h5>
                                        <div class="mb-3">
                                            <?php echo $course['full_description'] ?? 'No description available.'; ?>
                                        </div>

                                        <h5>What You'll Learn</h5>
                                        <ul class="list-group mb-3">
                                            <?php
                                            // Fetch learning outcomes
                                            $outcomes_query = "SELECT * FROM course_learning_outcomes WHERE course_id = ?";
                                            $stmt = $conn->prepare($outcomes_query);
                                            $stmt->bind_param("i", $course_id);
                                            $stmt->execute();
                                            $outcomes_result = $stmt->get_result();

                                            if ($outcomes_result->num_rows === 0) {
                                                echo '<li class="list-group-item">No learning outcomes specified.</li>';
                                            } else {
                                                while ($outcome = $outcomes_result->fetch_assoc()) {
                                                    echo '<li class="list-group-item">';
                                                    echo '<i class="mdi mdi-check-circle text-success me-2"></i>';
                                                    echo htmlspecialchars($outcome['outcome_text']);
                                                    echo '</li>';
                                                }
                                            }
                                            $stmt->close();
                                            ?>
                                        </ul>

                                        <h5>Prerequisites</h5>
                                        <ul class="list-group mb-3">
                                            <?php
                                            // Fetch course requirements
                                            $requirements_query = "SELECT * FROM course_requirements WHERE course_id = ?";
                                            $stmt = $conn->prepare($requirements_query);
                                            $stmt->bind_param("i", $course_id);
                                            $stmt->execute();
                                            $requirements_result = $stmt->get_result();

                                            if ($requirements_result->num_rows === 0) {
                                                echo '<li class="list-group-item">No prerequisites specified.</li>';
                                            } else {
                                                while ($requirement = $requirements_result->fetch_assoc()) {
                                                    echo '<li class="list-group-item">';
                                                    echo '<i class="mdi mdi-circle-medium text-primary me-2"></i>';
                                                    echo htmlspecialchars($requirement['requirement_text']);
                                                    echo '</li>';
                                                }
                                            }
                                            $stmt->close();
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                                
                                <!-- History Tab -->
                                <div class="tab-pane fade" id="history-tab" role="tabpanel">
                                    <div class="review-history">
                                        <h5>Review History</h5>
                                        
                                        <?php if (empty($review_history)): ?>
                                            <div class="alert alert-info">
                                                <p class="mb-0">No previous reviews found.</p>
                                            </div>
                                        <?php else: ?>
                                            <?php foreach ($review_history as $history): ?>
                                                <div class="history-item">
                                                    <div class="d-flex justify-content-between">
                                                        <strong>
                                                            <?php echo htmlspecialchars($history['first_name'] . ' ' . $history['last_name']); ?>
                                                        </strong>
                                                        <small class="text-muted">
                                                            <?php echo date('M d, Y g:i A', strtotime($history['review_date'])); ?>
                                                        </small>
                                                    </div>
                                                    <div class="mt-1">
                                                        Changed status from 
                                                        <span class="badge bg-secondary">
                                                            <?php echo ucwords(str_replace('_', ' ', $history['previous_status'])); ?>
                                                        </span>
                                                        to
                                                        <span class="badge bg-primary">
                                                            <?php echo ucwords(str_replace('_', ' ', $history['new_status'])); ?>
                                                        </span>
                                                    </div>
                                                    <?php if (!empty($history['comments'])): ?>
                                                        <div class="mt-2">
                                                            <p class="mb-0"><?php echo htmlspecialchars($history['comments']); ?></p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content Display Area -->
                <div class="col-md-8 col-lg-9">
                    <div class="card">
                        <div class="card-body">
                            <div id="content-viewer" class="content-viewer">
                                <div class="text-center py-5">
                                    <i class="mdi mdi-eye-outline" style="font-size: 64px; color: #3e7bfa;"></i>
                                    <h4 class="mt-3">Review Course Content</h4>
                                    <p class="text-muted">Select a topic from the curriculum to begin reviewing.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Review Notes (always visible) -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0">Review Notes</h5>
                        </div>
                        <div class="card-body">
                            <form id="reviewNotesForm">
                                <div class="mb-3">
                                    <label for="reviewNotes" class="form-label">Add notes for your review (optional)</label>
                                    <textarea class="form-control" id="reviewNotes" rows="3" placeholder="Enter your notes about this course content..."></textarea>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-primary" id="saveNotesBtn">
                                        <i class="mdi mdi-content-save-outline me-1"></i> Save Notes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals for Review Decisions -->
    
    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Approve Course</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="approveForm">
                    <div class="modal-body">
                        <p>You are about to approve this course. This will make it available for publishing.</p>
                        <div class="mb-3">
                            <label for="approveComments" class="form-label">Comments (optional)</label>
                            <textarea class="form-control" id="approveComments" name="comments" rows="3"></textarea>
                        </div>
                        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                        <input type="hidden" name="action" value="approve">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="mdi mdi-check-circle me-1"></i> Approve Course
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Revisions Modal -->
    <div class="modal fade" id="revisionsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Request Revisions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="revisionsForm">
                    <div class="modal-body">
                        <p>Please provide detailed feedback on what revisions are needed.</p>
                        <div class="mb-3">
                            <label for="revisionComments" class="form-label">Revision Comments (required)</label>
                            <textarea class="form-control" id="revisionComments" name="comments" rows="5" required></textarea>
                        </div>
                        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                        <input type="hidden" name="action" value="request_revisions">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="mdi mdi-file-document-edit-outline me-1"></i> Request Revisions
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
   <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Reject Course</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="rejectForm">
                    <div class="modal-body">
                        <p>You are about to reject this course. This should be used when the course does not meet department standards.</p>
                        <div class="alert alert-warning">
                            <i class="mdi mdi-alert-circle-outline me-1"></i> 
                            Rejection should only be used when the course has significant issues that cannot be addressed through revisions.
                        </div>
                        <div class="mb-3">
                            <label for="rejectComments" class="form-label">Rejection Reason (required)</label>
                            <textarea class="form-control" id="rejectComments" name="comments" rows="5" required></textarea>
                        </div>
                        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                        <input type="hidden" name="action" value="reject">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="mdi mdi-close-circle me-1"></i> Reject Course
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php include '../includes/department/footer.php'; ?>

    <script>
        $(document).ready(function() {
            // Handle topic selection
            $('.topic-item').click(function() {
                // Remove active class from all topics
                $('.topic-item').removeClass('active');

                // Add active class to selected topic
                $(this).addClass('active');

                // Show loading in content viewer
                $('#content-viewer').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading content...</p>
                </div>
            `);

                // Determine if this is a topic or quiz
                const topicId = $(this).data('topic-id');
                const quizId = $(this).data('quiz-id');

                if (topicId) {
                    // Load topic content
                    $.ajax({
                        url: '../ajax/department/load_topic_content.php',
                        type: 'GET',
                        data: {
                            topic_id: topicId,
                            course_id: <?php echo $course_id; ?>
                        },
                        success: function(response) {
                            $('#content-viewer').html(response);
                        },
                        error: function() {
                            $('#content-viewer').html(`
                            <div class="alert alert-danger">
                                <h5 class="alert-heading">Error Loading Content</h5>
                                <p class="mb-0">There was a problem loading this topic's content.</p>
                            </div>
                        `);
                        }
                    });
                } else if (quizId) {
                    // Load quiz preview
                    $.ajax({
                        url: '../ajax/department/load_quiz_preview.php',
                        type: 'GET',
                        data: {
                            quiz_id: quizId,
                            course_id: <?php echo $course_id; ?>
                        },
                        success: function(response) {
                            $('#content-viewer').html(response);
                        },
                        error: function() {
                            $('#content-viewer').html(`
                            <div class="alert alert-danger">
                                <h5 class="alert-heading">Error Loading Quiz</h5>
                                <p class="mb-0">There was a problem loading this quiz preview.</p>
                            </div>
                        `);
                        }
                    });
                }
            });
            
            // Save Review Notes
            $('#saveNotesBtn').click(function() {
                const notes = $('#reviewNotes').val();
                
                $.ajax({
                    url: '../ajax/department/save_review_notes.php',
                    type: 'POST',
                    data: {
                        course_id: <?php echo $course_id; ?>,
                        notes: notes
                    },
                    success: function(response) {
                        try {
                            const data = JSON.parse(response);
                            if (data.success) {
                                // Show success message
                                alert('Notes saved successfully!');
                            } else {
                                // Show error message
                                alert('Error: ' + data.message);
                            }
                        } catch (e) {
                            alert('Error processing response');
                            console.error(e);
                        }
                    },
                    error: function() {
                        alert('Error saving notes');
                    }
                });
            });
            
            // Handle Form Submissions
            $('#approveForm').submit(function(e) {
                e.preventDefault();
                submitReviewDecision(this, 'approve');
            });
            
            $('#revisionsForm').submit(function(e) {
                e.preventDefault();
                submitReviewDecision(this, 'request_revisions');
            });
            
            $('#rejectForm').submit(function(e) {
                e.preventDefault();
                submitReviewDecision(this, 'reject');
            });
            
            function submitReviewDecision(form, action) {
                const formData = new FormData(form);
                
                // Add any review notes
                formData.append('review_notes', $('#reviewNotes').val());
                
                $.ajax({
                    url: '../ajax/department/course_review_action.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        try {
                            const data = JSON.parse(response);
                            if (data.success) {
                                // Close the modal
                                $(form).closest('.modal').modal('hide');
                                
                                // Show success message
                                alert(data.message);
                                
                                // Redirect to courses page
                                window.location.href = 'courses.php';
                            } else {
                                // Show error message
                                alert('Error: ' + data.message);
                            }
                        } catch (e) {
                            alert('Error processing response');
                            console.error(e);
                        }
                    },
                    error: function() {
                        alert('Error submitting review decision');
                    }
                });
            }
        });
    </script>
