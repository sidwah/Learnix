<?php
require '../backend/session_start.php'; // Ensure session is started

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    // Log unauthorized access attempt for security auditing
    error_log("Unauthorized access attempt detected: " . json_encode($_SERVER));

    // Redirect unauthorized users to a custom unauthorized access page or login page
    header('Location: landing.php');
    exit;
}

// Connect to the database
require_once '../backend/config.php';

// Verify required parameters
if (!isset($_GET['course_id']) || !isset($_GET['section_id'])) {
    header('Location: dashboard.php');
    exit;
}

$course_id = intval($_GET['course_id']);
$section_id = intval($_GET['section_id']);

// Verify the instructor has access to this course
$instructor_id = $_SESSION['instructor_id'];
$course_check_query = "SELECT c.* FROM courses c 
                       WHERE c.course_id = ? AND c.instructor_id = ?";
$stmt = $conn->prepare($course_check_query);
$stmt->bind_param("ii", $course_id, $instructor_id);
$stmt->execute();
$course_result = $stmt->get_result();

if ($course_result->num_rows === 0) {
    // Course not found or not owned by this instructor
    header('Location: dashboard.php');
    exit;
}

$course = $course_result->fetch_assoc();
$stmt->close();

// Verify the section belongs to this course
$section_check_query = "SELECT * FROM course_sections 
                       WHERE section_id = ? AND course_id = ?";
$stmt = $conn->prepare($section_check_query);
$stmt->bind_param("ii", $section_id, $course_id);
$stmt->execute();
$section_result = $stmt->get_result();

if ($section_result->num_rows === 0) {
    // Section not found or not part of this course
    header('Location: course-creator.php?course_id=' . $course_id . '&step=6');
    exit;
}

$section = $section_result->fetch_assoc();
$stmt->close();

// Check if there's an existing quiz for this section
$quiz_query = "SELECT * FROM section_quizzes WHERE section_id = ?";
$stmt = $conn->prepare($quiz_query);
$stmt->bind_param("i", $section_id);
$stmt->execute();
$quiz_result = $stmt->get_result();
$quiz = null;
$quiz_id = null;

if ($quiz_result->num_rows > 0) {
    $quiz = $quiz_result->fetch_assoc();
    $quiz_id = $quiz['quiz_id'];
}
$stmt->close();

// Set page title
$page_title = $quiz ? 'Edit Quiz' : 'Create Quiz';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title><?php echo $page_title; ?> | Learnix - Empowering Education</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Create and manage quizzes for your courses" name="description" />
    <meta content="Learnix Development Team" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- third party css -->
    <link href="assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <!-- third party css end -->

    <!-- App css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style"/>

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
                                        <li class="breadcrumb-item"><a href="course-creator.php?course_id=<?php echo $course_id; ?>&step=6">Course Builder</a></li>
                                        <li class="breadcrumb-item active"><?php echo $page_title; ?></li>
                                    </ol>
                                </div>
                                <h4 class="page-title"><?php echo $page_title; ?></h4>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h5 class="mb-0">For Section: <?php echo htmlspecialchars($section['title']); ?></h5>
                                        <a href="course-creator.php?course_id=<?php echo $course_id; ?>&step=6" class="btn btn-secondary">
                                            <i class="mdi mdi-arrow-left"></i> Back to Curriculum
                                        </a>
                                    </div>
                                    <p class="text-muted">
                                        Create a quiz to test your students' knowledge and understanding of the course material.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quiz settings form -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Quiz Settings</h4>
                                </div>
                                <div class="card-body">
                                    <form id="quizSettingsForm">
                                        <input type="hidden" id="courseId" value="<?php echo $course_id; ?>">
                                        <input type="hidden" id="sectionId" value="<?php echo $section_id; ?>">
                                        <input type="hidden" id="quizId" value="<?php echo $quiz_id; ?>">

                                        <div class="mb-3">
                                            <label for="quizTitle" class="form-label">Quiz Title <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="quizTitle" name="quizTitle" required
                                                value="<?php echo $quiz ? htmlspecialchars($quiz['quiz_title']) : ''; ?>"
                                                placeholder="e.g., Module 1 Assessment">
                                            <div class="invalid-feedback">Please enter a quiz title.</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="quizInstructions" class="form-label">Instructions for Students</label>
                                            <textarea class="form-control" id="quizInstructions" name="quizInstructions" rows="3"
                                                placeholder="e.g., Read each question carefully and select the best answer."><?php echo $quiz ? htmlspecialchars($quiz['instruction']) : ''; ?></textarea>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="passMark" class="form-label">Pass Mark (%) <span class="text-danger">*</span></label>
                                                    <input type="number" class="form-control" id="passMark" name="passMark" required
                                                        min="0" max="100" step="1"
                                                        value="<?php echo $quiz ? $quiz['pass_mark'] : '70'; ?>"
                                                        placeholder="e.g., 70">
                                                    <div class="form-text">Minimum percentage required to pass the quiz.</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="timeLimit" class="form-label">Time Limit (minutes)</label>
                                                    <input type="number" class="form-control" id="timeLimit" name="timeLimit"
                                                        min="0" step="1"
                                                        value="<?php echo $quiz && $quiz['time_limit'] ? $quiz['time_limit'] : ''; ?>"
                                                        placeholder="Leave blank for no time limit">
                                                    <div class="form-text">Set a time limit or leave blank for unlimited time.</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="attemptsAllowed" class="form-label">Attempts Allowed</label>
                                                    <input type="number" class="form-control" id="attemptsAllowed" name="attemptsAllowed"
                                                        min="1" step="1"
                                                        value="<?php echo $quiz ? $quiz['attempts_allowed'] : '1'; ?>"
                                                        placeholder="e.g., 1">
                                                    <div class="form-text">Number of times a student can take this quiz.</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3 mt-4">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="randomizeQuestions" name="randomizeQuestions"
                                                            <?php echo $quiz && $quiz['randomize_questions'] ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="randomizeQuestions">Randomize Questions</label>
                                                    </div>
                                                    <div class="form-text">Questions will appear in random order for each student.</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3 mt-2">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="showCorrectAnswers" name="showCorrectAnswers"
                                                            <?php echo $quiz && $quiz['show_correct_answers'] ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="showCorrectAnswers">Show Correct Answers After Submission</label>
                                                    </div>
                                                    <div class="form-text">Let students see the correct answers after submitting.</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3 mt-2">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="shuffleAnswers" name="shuffleAnswers"
                                                            <?php echo $quiz && $quiz['shuffle_answers'] ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="shuffleAnswers">Shuffle Answer Options</label>
                                                    </div>
                                                    <div class="form-text">Answer options will appear in random order.</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3 mt-2">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="isRequired" name="isRequired"
                                                    <?php echo !$quiz || $quiz['is_required'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="isRequired">Required for Course Completion</label>
                                            </div>
                                            <div class="form-text">Students must pass this quiz to complete the course.</div>
                                        </div>

                                        <div class="text-end">
                                            <button type="button" id="saveQuizSettingsBtn" class="btn btn-primary">
                                                <i class="mdi mdi-content-save"></i> Save Quiz Settings
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Questions Management Section -->
                    <div class="row" id="questionsSection" style="<?php echo $quiz_id ? '' : 'display: none;'; ?>">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h4 class="card-title mb-0">Questions</h4>
                                    <div>
                                        <button type="button" id="addQuestionBtn" class="btn btn-primary">
                                            <i class="mdi mdi-plus-circle"></i> Add Question
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="questionsList">
                                        <!-- Questions will be loaded here -->
                                        <?php if ($quiz_id): ?>
                                        <div class="text-center py-3" id="questionsLoading">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <p class="mt-2">Loading questions...</p>
                                        </div>
                                        <?php else: ?>
                                        <div class="text-center py-5" id="noQuestionsMessage">
                                            <div class="mb-3">
                                                <i class="mdi mdi-help-circle-outline" style="font-size: 64px; color: #adb5bd;"></i>
                                            </div>
                                            <h5>No Questions Added Yet</h5>
                                            <p class="text-muted">Save your quiz settings first, then add questions using the button above.</p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- container -->

            </div>
            <!-- content -->

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

    <!-- Question Types Modal -->
    <div class="modal fade" id="questionTypesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Question Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 question-type-card" data-type="multiple_choice">
                                <div class="card-body text-center">
                                    <i class="mdi mdi-format-list-bulleted-type" style="font-size: 36px;"></i>
                                    <h5 class="mt-2">Multiple Choice</h5>
                                    <p class="text-muted small mb-0">Choose one correct answer from several options</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 question-type-card" data-type="true_false">
                                <div class="card-body text-center">
                                    <i class="mdi mdi-toggle-switch-outline" style="font-size: 36px;"></i>
                                    <h5 class="mt-2">True/False</h5>
                                    <p class="text-muted small mb-0">Simple true or false statement</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Question Editor Modal -->
    <div class="modal fade" id="questionEditorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="questionEditorTitle">Add New Question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="questionEditorContent">
                        <!-- Question editor will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this question? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- bundle -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.min.js"></script>

    <!-- Show alert notification function -->
    <script>
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

        // Create and apply page overlay for loading effect with optional message
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

            // Add a loading spinner
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
        }

        // Remove overlay
        function removeOverlay() {
            const overlay = document.getElementById('pageOverlay');
            if (overlay) {
                document.body.removeChild(overlay);
            }
        }
    </script>

    <!-- Quiz Builder Script -->
    <script>
// This JavaScript code should be added to your quiz-builder.php file

$(document).ready(function() {
    // Load questions if quiz exists
    if ($("#quizId").val()) {
        loadQuestions();
    }

    // Save Quiz Settings
    $("#saveQuizSettingsBtn").click(function() {
        const quizSettingsForm = document.getElementById('quizSettingsForm');
        
        // Basic form validation
        if (!$("#quizTitle").val().trim()) {
            $("#quizTitle").addClass('is-invalid');
            return;
        } else {
            $("#quizTitle").removeClass('is-invalid');
        }
        
        if (!$("#passMark").val().trim() || parseInt($("#passMark").val()) < 0 || parseInt($("#passMark").val()) > 100) {
            $("#passMark").addClass('is-invalid');
            return;
        } else {
            $("#passMark").removeClass('is-invalid');
        }

        // Collect form data
        const formData = {
            course_id: $("#courseId").val(),
            section_id: $("#sectionId").val(),
            quiz_id: $("#quizId").val() || null,
            quiz_title: $("#quizTitle").val(),
            instruction: $("#quizInstructions").val(),
            pass_mark: $("#passMark").val(),
            time_limit: $("#timeLimit").val() || null,
            attempts_allowed: $("#attemptsAllowed").val() || 1,
            randomize_questions: $("#randomizeQuestions").is(':checked') ? 1 : 0,
            show_correct_answers: $("#showCorrectAnswers").is(':checked') ? 1 : 0,
            shuffle_answers: $("#shuffleAnswers").is(':checked') ? 1 : 0,
            is_required: $("#isRequired").is(':checked') ? 1 : 0
        };

        // Show loading overlay
        createOverlay('Saving quiz settings...');

        // Send AJAX request
        $.ajax({
            url: '../ajax/assessments/save_quiz.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(result) {
                // If jQuery has already parsed the result, we don't need to parse it again
                if (result && typeof result === 'object') {
                    if (result.success) {
                        // Update quiz ID if new quiz
                        if (!$("#quizId").val() && result.quiz_id) {
                            $("#quizId").val(result.quiz_id);
                            // Show questions section
                            $("#questionsSection").show();
                            // Replace no questions message
                            $("#noQuestionsMessage").html(`
                                <div class="mb-3">
                                    <i class="mdi mdi-help-circle-outline" style="font-size: 64px; color: #adb5bd;"></i>
                                </div>
                                <h5>No Questions Added Yet</h5>
                                <p class="text-muted">Start adding questions using the button above.</p>
                            `);
                        }
                        
                        showAlert('success', 'Quiz settings saved successfully!');
                    } else {
                        showAlert('danger', 'Error: ' + (result.message || 'Failed to save quiz settings.'));
                    }
                } else {
                    // Handle case where result is a string that needs parsing
                    try {
                        const parsedResult = typeof result === 'string' ? JSON.parse(result) : result;
                        
                        if (parsedResult.success) {
                            // Update quiz ID if new quiz
                            if (!$("#quizId").val() && parsedResult.quiz_id) {
                                $("#quizId").val(parsedResult.quiz_id);
                                // Show questions section
                                $("#questionsSection").show();
                                // Replace no questions message
                                $("#noQuestionsMessage").html(`
                                    <div class="mb-3">
                                        <i class="mdi mdi-help-circle-outline" style="font-size: 64px; color: #adb5bd;"></i>
                                    </div>
                                    <h5>No Questions Added Yet</h5>
                                    <p class="text-muted">Start adding questions using the button above.</p>
                                `);
                            }
                            
                            showAlert('success', 'Quiz settings saved successfully!');
                        } else {
                            showAlert('danger', 'Error: ' + (parsedResult.message || 'Failed to save quiz settings.'));
                        }
                    } catch (e) {
                        console.error('Error handling response', e);
                        console.error('Response:', result);
                        showAlert('danger', 'Error processing server response. Check browser console for details.');
                    }
                }
                
                // Remove loading overlay
                removeOverlay();
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                showAlert('danger', 'Network error. Please try again.');
                removeOverlay();
            }
        });
    });

    // Add Question Button
    $("#addQuestionBtn").click(function() {
        // Show question types modal
        $("#questionTypesModal").modal('show');
    });

    // Question Type Selection
    $(".question-type-card").click(function() {
        const questionType = $(this).data('type');
        $("#questionTypesModal").modal('hide');
        
        // Show loading overlay
        createOverlay('Loading question editor...');
        
        // Load question editor based on type
        $.ajax({
            url: '../includes/quiz-builder/question-types.php',
            type: 'GET',
            data: {
                type: questionType,
                quiz_id: $("#quizId").val()
            },
            success: function(response) {
                $("#questionEditorContent").html(response);
                $("#questionEditorTitle").text(`Add New ${questionType === 'multiple_choice' ? 'Multiple Choice' : 'True/False'} Question`);
                $("#questionEditorModal").modal('show');
                removeOverlay();
            },
            error: function() {
                showAlert('danger', 'Failed to load question editor.');
                removeOverlay();
            }
        });
    });

    // Load questions for the quiz
    function loadQuestions() {
        $.ajax({
            url: '../includes/quiz-builder/question-list.php',
            type: 'GET',
            data: {
                quiz_id: $("#quizId").val()
            },
            dataType: 'html',
            success: function(response) {
                $("#questionsList").html(response);
            },
            error: function() {
                $("#questionsList").html(`
                    <div class="alert alert-danger">
                        Failed to load questions. Please refresh the page and try again.
                    </div>
                `);
            }
        });
    }

    // Handle edit question (delegated event)
    $(document).on('click', '.edit-question-btn', function() {
        const questionId = $(this).data('question-id');
        const questionType = $(this).data('question-type');
        
        // Show loading overlay
        createOverlay('Loading question editor...');
        
        // Load question editor for editing
        $.ajax({
            url: '../includes/quiz-builder/question-types.php',
            type: 'GET',
            data: {
                type: questionType,
                quiz_id: $("#quizId").val(),
                question_id: questionId
            },
            success: function(response) {
                $("#questionEditorContent").html(response);
                $("#questionEditorTitle").text(`Edit ${questionType === 'Multiple Choice' ? 'Multiple Choice' : 'True/False'} Question`);
                $("#questionEditorModal").modal('show');
                removeOverlay();
            },
            error: function() {
                showAlert('danger', 'Failed to load question editor.');
                removeOverlay();
            }
        });
    });

    // Handle delete question (delegated event)
    $(document).on('click', '.delete-question-btn', function() {
        const questionId = $(this).data('question-id');
        
        // Store question ID for deletion
        $("#confirmDeleteBtn").data('question-id', questionId);
        
        // Show confirmation modal
        $("#deleteConfirmModal").modal('show');
    });

    // Confirm Delete Question
    $("#confirmDeleteBtn").click(function() {
        const questionId = $(this).data('question-id');
        
        // Show loading overlay
        createOverlay('Deleting question...');
        
        // Send AJAX request to delete question
        $.ajax({
            url: '../ajax/assessments/delete_question.php',
            type: 'POST',
            data: {
                question_id: questionId
            },
            dataType: 'json',
            success: function(result) {
                // Check if the result is already an object
                if (result && typeof result === 'object') {
                    if (result.success) {
                        // Hide confirmation modal
                        $("#deleteConfirmModal").modal('hide');
                        
                        // Reload questions list
                        loadQuestions();
                        
                        showAlert('success', 'Question deleted successfully.');
                    } else {
                        showAlert('danger', 'Error: ' + (result.message || 'Failed to delete question.'));
                    }
                } else {
                    // Try to parse result if it's a string
                    try {
                        const parsedResult = typeof result === 'string' ? JSON.parse(result) : result;
                        
                        if (parsedResult.success) {
                            // Hide confirmation modal
                            $("#deleteConfirmModal").modal('hide');
                            
                            // Reload questions list
                            loadQuestions();
                            
                            showAlert('success', 'Question deleted successfully.');
                        } else {
                            showAlert('danger', 'Error: ' + (parsedResult.message || 'Failed to delete question.'));
                        }
                    } catch (e) {
                        console.error('Error handling response', e);
                        console.error('Response:', result);
                        showAlert('danger', 'Error processing server response. Check browser console for details.');
                    }
                }
                
                // Remove loading overlay
                removeOverlay();
            },
            error: function() {
                showAlert('danger', 'Network error. Please try again.');
                removeOverlay();
            }
        });
    });

    // Handle preview question (delegated event)
    $(document).on('click', '.preview-question-btn', function() {
        const questionId = $(this).data('question-id');
        
        // Show loading overlay
        createOverlay('Loading question preview...');
        
        // Create a modal for previewing
        if (!$("#questionPreviewModal").length) {
            $('body').append(`
                <div class="modal fade" id="questionPreviewModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Question Preview</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body" id="questionPreviewContent">
                                <!-- Question preview will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            `);
        }
        
        // Load preview content
        $.ajax({
            url: '../includes/quiz-builder/question-preview.php',
            type: 'GET',
            data: {
                question_id: questionId
            },
            success: function(response) {
                $("#questionPreviewContent").html(response);
                $("#questionPreviewModal").modal('show');
                removeOverlay();
            },
            error: function() {
                showAlert('danger', 'Failed to load question preview.');
                removeOverlay();
            }
        });
    });

    // Handle save question (delegated event)
    $(document).on('click', '#saveQuestionBtn', function() {
        // Get question type
        const questionType = $(this).data('question-type');
        
        // Validate form fields
        if (!validateQuestionForm(questionType)) {
            return;
        }
        
        // Collect form data
        const formData = collectQuestionFormData(questionType);
        
        // Show loading overlay
        createOverlay('Saving question...');
        
        // Send AJAX request
        $.ajax({
            url: '../ajax/assessments/save_question.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(result) {
                // Check if the result is already an object
                if (result && typeof result === 'object') {
                    if (result.success) {
                        // Close modal
                        $("#questionEditorModal").modal('hide');
                        
                        // Reload questions
                        loadQuestions();
                        
                        showAlert('success', result.message || 'Question saved successfully.');
                    } else {
                        showAlert('danger', 'Error: ' + (result.message || 'Failed to save question.'));
                    }
                } else {
                    // Try to parse result if it's a string
                    try {
                        const parsedResult = typeof result === 'string' ? JSON.parse(result) : result;
                        
                        if (parsedResult.success) {
                            // Close modal
                            $("#questionEditorModal").modal('hide');
                            
                            // Reload questions
                            loadQuestions();
                            
                            showAlert('success', parsedResult.message || 'Question saved successfully.');
                        } else {
                            showAlert('danger', 'Error: ' + (parsedResult.message || 'Failed to save question.'));
                        }
                    } catch (e) {
                        console.error('Error handling response', e);
                        console.error('Response:', result);
                        showAlert('danger', 'Error processing server response. Check browser console for details.');
                    }
                }
                
                // Remove loading overlay
                removeOverlay();
            },
            error: function() {
                showAlert('danger', 'Network error. Please try again.');
                removeOverlay();
            }
        });
    });
    
    // Validate question form based on type
    function validateQuestionForm(questionType) {
        let isValid = true;
        
        // Validate question text
        if (!$("#questionText").val().trim()) {
            $("#questionText").addClass('is-invalid');
            isValid = false;
        } else {
            $("#questionText").removeClass('is-invalid');
        }
        
        // Validate points
        if (!$("#questionPoints").val() || parseInt($("#questionPoints").val()) < 1) {
            $("#questionPoints").addClass('is-invalid');
            isValid = false;
        } else {
            $("#questionPoints").removeClass('is-invalid');
        }
        
        if (questionType === 'multiple_choice') {
            // Make sure we have at least 2 answer options
            if ($(".answer-option").length < 2) {
                showAlert('danger', 'You need at least 2 answer options.');
                isValid = false;
            }
            
            // Make sure each answer option has text
            $(".answer-text").each(function() {
                if (!$(this).val().trim()) {
                    $(this).addClass('is-invalid');
                    isValid = false;
                } else {
                    $(this).removeClass('is-invalid');
                }
            });
            
            // Make sure at least one answer is selected as correct
            if ($(".correct-answer:checked").length === 0) {
                showAlert('danger', 'You must select at least one correct answer.');
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    // Collect form data based on question type
    function collectQuestionFormData(questionType) {
        const formData = {
            quiz_id: $("#quizId").val(),
            question_id: $("#questionId").val() || null,
            question_text: $("#questionText").val(),
            question_type: questionType,
            points: $("#questionPoints").val(),
            explanation: $("#questionExplanation").val() || null
        };
        
        if (questionType === 'multiple_choice') {
            const answers = [];
            
            // Collect all answer options
            $(".answer-option").each(function() {
                const answerId = $(this).data('answer-id') || null;
                const answerText = $(this).find('.answer-text').val();
                const isCorrect = $(this).find('.correct-answer').is(':checked') ? 1 : 0;
                
                answers.push({
                    answer_id: answerId,
                    answer_text: answerText,
                    is_correct: isCorrect
                });
            });
            
            formData.answers = JSON.stringify(answers);
        } else if (questionType === 'true_false') {
            formData.correct_answer = $("input[name='correctAnswer']:checked").val();
        }
        
        return formData;
    }
    
    // Handle add answer option (delegated event)
    $(document).on('click', '#addAnswerBtn', function() {
        const answersContainer = $("#answersContainer");
        const newAnswerIndex = $(".answer-option").length + 1;
        
        const newAnswer = `
            <div class="answer-option mb-3" data-answer-id="">
                <div class="input-group">
                    <div class="input-group-text">
                        <input class="form-check-input correct-answer" type="radio" name="correctAnswer" value="${newAnswerIndex}">
                    </div>
                    <input type="text" class="form-control answer-text" placeholder="Answer option">
                    <button type="button" class="btn btn-outline-danger remove-answer-btn">
                        <i class="mdi mdi-delete"></i>
                    </button>
                </div>
            </div>
        `;
        
        answersContainer.append(newAnswer);
    });
    
    // Handle remove answer option (delegated event)
    $(document).on('click', '.remove-answer-btn', function() {
        // Only remove if we have more than 2 options
        if ($(".answer-option").length > 2) {
            $(this).closest('.answer-option').remove();
        } else {
            showAlert('danger', 'You need at least 2 answer options.');
        }
    });
});
    </script>
</body>
</html>