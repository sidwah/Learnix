

<?php
require '../backend/session_start.php'; // Ensure session is started
require '../backend/config.php'; // Ensure session is started

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    // Log unauthorized access attempt for security auditing
    error_log("Unauthorized access attempt detected: " . json_encode($_SERVER));

    // Redirect unauthorized users to a custom unauthorized access page or login page
    header('Location: landing.php');
    exit;
}


// Initialize course_id from URL parameter or session
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : (isset($_SESSION['current_course_id']) ? $_SESSION['current_course_id'] : 0);

// If no course_id is available, redirect to the instructor dashboard
if ($course_id <= 0) {
    header('Location: index.php');
    exit;
}

// Store course_id in session for persistence
$_SESSION['current_course_id'] = $course_id;

// Fetch current course data and step
$stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = ? AND instructor_id = ?");
$stmt->bind_param("ii", $course_id, $_SESSION['instructor_id']);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();

// If course doesn't exist or doesn't belong to this instructor, redirect
if (!$course) {
    header('Location: index.php');
    exit;
}

// Get current step from course creation_step field (defaulting to 1 if not set)
$current_step = isset($course['creation_step']) ? intval($course['creation_step']) : 1;
$max_step = 6; // Total number of steps in the wizard
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Create Course | Learnix - Empowering Education</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Create and manage your course content, curriculum, and assessments." name="description" />
    <meta content="Learnix Development Team" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- third party css -->
    <link href="assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <!-- third party css end -->

    <!-- App css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>


    <!-- Include SortableJS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>

    <!-- Custom CSS for Course Creator -->
    <style>
        .wizard-progress-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }

        .wizard-progress-bar:before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e3e3e3;
            z-index: 0;
        }

        .progress-step {
            position: relative;
            z-index: 1;
            text-align: center;
            width: 40px;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #f5f5f5;
            border: 2px solid #e3e3e3;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin: 0 auto 10px;
            transition: all 0.3s ease;
        }

        .step-label {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }

        .progress-step.active .step-circle {
            background-color: #3e7bfa;
            border-color: #3e7bfa;
            color: #fff;
        }

        .progress-step.active .step-label {
            color: #3e7bfa;
            font-weight: bold;
        }

        .progress-step.completed .step-circle {
            background-color: #1abc9c;
            border-color: #1abc9c;
            color: #fff;
        }

        .wizard-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e3e3e3;
        }

        .wizard-step-content {
            min-height: 300px;
        }

        .wizard-step {
            display: none;
        }

        .wizard-step.active {
            display: block;
        }

        .auto-save-indicator {
            display: inline-block;
            font-size: 12px;
            color: #6c757d;
            margin-left: 10px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .auto-save-indicator.show {
            opacity: 1;
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
                                        <li class="breadcrumb-item"><a href="index.php">Instructor</a></li>
                                        <li class="breadcrumb-item active">Create Course</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Create Course: <?php echo htmlspecialchars($course['title'] ?: 'New Course'); ?></h4>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title mb-3">Course Creation Wizard</h4>

                                    <!-- Wizard Progress Tracker -->
                                    <div class="wizard-progress-bar">
                                        <div class="progress-step <?php echo ($current_step >= 1) ? 'active' : ''; ?> <?php echo ($current_step > 1) ? 'completed' : ''; ?>" data-step="1">
                                            <div class="step-circle">1</div>
                                            <div class="step-label w-100">Basic Info</div>
                                        </div>
                                        <div class="progress-step <?php echo ($current_step >= 2) ? 'active' : ''; ?> <?php echo ($current_step > 2) ? 'completed' : ''; ?>" data-step="2">
                                            <div class="step-circle">2</div>
                                            <div class="step-label">Description</div>
                                        </div>
                                        <div class="progress-step <?php echo ($current_step >= 3) ? 'active' : ''; ?> <?php echo ($current_step > 3) ? 'completed' : ''; ?>" data-step="3">
                                            <div class="step-circle">3</div>
                                            <div class="step-label">Outcomes</div>
                                        </div>
                                        <div class="progress-step <?php echo ($current_step >= 4) ? 'active' : ''; ?> <?php echo ($current_step > 4) ? 'completed' : ''; ?>" data-step="4">
                                            <div class="step-circle">4</div>
                                            <div class="step-label">Pricing</div>
                                        </div>
                                        <div class="progress-step <?php echo ($current_step >= 5) ? 'active' : ''; ?> <?php echo ($current_step > 5) ? 'completed' : ''; ?>" data-step="5">
                                            <div class="step-circle">5</div>
                                            <div class="step-label">Tags</div>
                                        </div>
                                        <div class="progress-step <?php echo ($current_step >= 6) ? 'active' : ''; ?>" data-step="6">
                                            <div class="step-circle">6</div>
                                            <div class="step-label">Curriculum</div>
                                        </div>
                                    </div>
                                    <style>
                                        .wizard-progress-bar {
                                            display: flex;
                                            justify-content: space-between;
                                            margin-bottom: 30px;
                                            position: relative;
                                            gap: 10px;
                                            /* Adds spacing between steps */
                                        }

                                        .progress-step {
                                            flex: 1 1 0;
                                            min-width: 120px;
                                            /* Increase to give more breathing space */
                                            text-align: center;
                                        }

                                        .step-label {
                                            display: block;
                                            font-size: 13px;
                                            white-space: normal;
                                            word-break: break-word;
                                            line-height: 1.2;
                                        }

                                        .progress-step {
                                            flex-grow: 1;
                                            flex-shrink: 1;
                                            flex-basis: 0;
                                        }
                                    </style>

                                    <!-- Auto-save indicator -->
                                    <div class="auto-save-indicator" id="autoSaveIndicator">
                                        <i class="mdi mdi-content-save"></i> Saving changes...
                                    </div>

                                    <!-- Wizard Content Container -->
                                    <div class="wizard-step-content">
                                        <!-- Step 1: Basic Information -->
                                        <div class="wizard-step <?php echo ($current_step == 1) ? 'active' : ''; ?>" id="step1">
                                            <?php include '../includes/course-creator/basic-info.php'; ?>
                                        </div>

                                        <!-- Step 2: Detailed Description -->
                                        <div class="wizard-step <?php echo ($current_step == 2) ? 'active' : ''; ?>" id="step2">
                                            <?php include '../includes/course-creator/description.php'; ?>
                                        </div>

                                        <!-- Step 3: Outcomes & Requirements -->
                                        <div class="wizard-step <?php echo ($current_step == 3) ? 'active' : ''; ?>" id="step3">
                                            <?php include '../includes/course-creator/outcomes-requirements.php'; ?>
                                        </div>

                                        <!-- Step 4: Pricing & Settings -->
                                        <div class="wizard-step <?php echo ($current_step == 4) ? 'active' : ''; ?>" id="step4">
                                            <?php include '../includes/course-creator/pricing-settings.php'; ?>
                                        </div>

                                        <!-- Step 5: Tags -->
                                        <div class="wizard-step <?php echo ($current_step == 5) ? 'active' : ''; ?>" id="step5">
                                            <?php include '../includes/course-creator/tags.php'; ?>
                                        </div>

                                        <!-- Step 6: Curriculum Builder -->
                                        <div class="wizard-step <?php echo ($current_step == 6) ? 'active' : ''; ?>" id="step6">
                                            <?php include '../includes/course-creator/curriculum-builder.php'; ?>
                                        </div>
                                    </div>

                                    <!-- Wizard Navigation -->
                                    <!-- Wizard Navigation -->
                                    <div class="wizard-navigation">
                                        <div class="d-flex justify-content-between w-100">
                                            <div>
                                                <button type="button" class="btn btn-secondary" id="prevStep" <?php echo ($current_step == 1) ? 'style="display:none;"' : ''; ?>>
                                                    <i class="mdi mdi-arrow-left"></i> Previous
                                                </button>
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-primary" id="nextStep" <?php echo ($current_step == $max_step) ? 'style="display:none;"' : ''; ?>>
                                                    Next <i class="mdi mdi-arrow-right"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div> <!-- end card-body -->
                            </div> <!-- end card-->
                        </div> <!-- end col -->
                    </div>
                    <!-- end row -->

                </div> <!-- container -->

            </div> <!-- content -->

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

    <?php include '../includes/instructor-darkmode.php'; ?>

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

    <!-- Course Creator Wizard JS -->

    <script>
        $(document).ready(function() {
            let currentStep = <?php echo $current_step; ?>;

            // Show the specified step - make it globally accessible
            window.showStep = function(step) {
                // Hide all steps
                $('.wizard-step').removeClass('active');

                // Show the current step
                $('#step' + step).addClass('active');

                // Update progress indicator
                updateProgressIndicator(step);

                // Update navigation buttons based on current step
                updateNavigationButtons(step);

                // Update current step variable
                currentStep = step;

                // Update URL with current step
                history.replaceState(null, null, '?course_id=<?php echo $course_id; ?>&step=' + step);
            };

            // Navigation button handlers
            $('#nextStep').on('click', function() {
                // Validate current step before proceeding
                if (validateStep(currentStep)) {
                    if (currentStep < <?php echo $max_step; ?>) {
                        // Save current step data
                        saveStepData(currentStep, function() {
                            // Update step in database
                            updateStepProgress(currentStep + 1, function() {
                                // Show next step
                                currentStep++;
                                showStep(currentStep);
                            });
                        });
                    }
                } else {
                    showAlert('danger', 'Please complete all required fields before proceeding.');
                }
            });

            $('#prevStep').on('click', function() {
                if (currentStep > 1) {
                    // No validation needed when going back
                    saveStepData(currentStep, function() {
                        currentStep--;
                        showStep(currentStep);
                    });
                }
            });

            // Show the specified step
            function showStep(step) {
                // Hide all steps
                $('.wizard-step').removeClass('active');

                // Show the current step
                $('#step' + step).addClass('active');

                // Update progress indicator
                updateProgressIndicator(step);

                // Update navigation buttons based on current step
                updateNavigationButtons(step);

                // Update URL with current step
                history.replaceState(null, null, '?course_id=<?php echo $course_id; ?>&step=' + step);
            }

            // Update navigation buttons based on current step
            // Update navigation buttons based on current step
            function updateNavigationButtons(step) {
                // Show/hide previous button on first step
                if (step === 1) {
                    $('#prevStep').hide();
                } else {
                    $('#prevStep').show();
                }

                // Show/hide next button on last step
                if (step === <?php echo $max_step; ?>) {
                    $('#nextStep').hide();
                } else {
                    $('#nextStep').show();
                    $('#nextStep').html('Next <i class="mdi mdi-arrow-right"></i>');
                }
            }

            // Update progress indicator
            function updateProgressIndicator(currentStep) {
                $('.progress-step').each(function() {
                    const stepNum = parseInt($(this).data('step'));

                    if (stepNum < currentStep) {
                        $(this).removeClass('active').addClass('completed');
                    } else if (stepNum === currentStep) {
                        $(this).addClass('active').removeClass('completed');
                    } else {
                        $(this).removeClass('active completed');
                    }
                });
            }

            // Save current step data
            function saveStepData(step, callback) {
                // Show saving indicator
                $('#autoSaveIndicator').addClass('show');

                // Different saving logic based on step
                switch (step) {
                    case 1:
                        // Save basic info (will be implemented in basic-info.php)
                        if (typeof saveBasicInfo === 'function') {
                            saveBasicInfo(function() {
                                $('#autoSaveIndicator').removeClass('show');
                                if (callback) callback();
                            });
                        } else {
                            $('#autoSaveIndicator').removeClass('show');
                            if (callback) callback();
                        }
                        break;
                    case 2:
                        // Save description (will be implemented in description.php)
                        if (typeof saveDescription === 'function') {
                            saveDescription(function() {
                                $('#autoSaveIndicator').removeClass('show');
                                if (callback) callback();
                            });
                        } else {
                            $('#autoSaveIndicator').removeClass('show');
                            if (callback) callback();
                        }
                        break;
                        // Add cases for other steps
                    default:
                        $('#autoSaveIndicator').removeClass('show');
                        if (callback) callback();
                }
            }

            // Update step progress in database
            function updateStepProgress(newStep, callback) {
                $.ajax({
                    url: '../ajax/courses/update_step.php',
                    type: 'POST',
                    data: {
                        course_id: <?php echo $course_id; ?>,
                        step: newStep
                    },
                    success: function(response) {
                        try {
                            const result = JSON.parse(response);
                            if (result.success) {
                                if (callback) callback();
                            } else {
                                showAlert('danger', 'Error updating progress: ' + result.message);
                            }
                        } catch (e) {
                            showAlert('danger', 'Error parsing server response');
                            console.error(e, response);
                        }
                    },
                    error: function() {
                        showAlert('danger', 'Network error while updating progress');
                    }
                });
            }

            // Validate current step
            function validateStep(step) {
                switch (step) {
                    case 1:
                        // Basic info validation will be implemented in basic-info.php
                        return typeof validateBasicInfo === 'function' ? validateBasicInfo() : true;
                    case 2:
                        // Description validation will be implemented in description.php
                        return typeof validateDescription === 'function' ? validateDescription() : true;
                    case 3:
                        // Outcomes & requirements validation
                        return typeof validateOutcomesRequirements === 'function' ? validateOutcomesRequirements() : true;
                    case 4:
                        // Pricing & settings validation
                        return typeof validatePricingSettings === 'function' ? validatePricingSettings() : true;
                    case 5:
                        // Tags validation
                        return typeof validateTags === 'function' ? validateTags() : true;
                    case 6:
                        // Curriculum validation
                        return typeof validateCurriculum === 'function' ? validateCurriculum() : true;
                    default:
                        return true;
                }
            }

            // Initialize the first view
            showStep(currentStep);

            // Set up autosave for form inputs
            $(document).on('change', 'input, select, textarea', function() {
                // Debounce autosave to prevent too many requests
                clearTimeout(window.autosaveTimer);
                window.autosaveTimer = setTimeout(function() {
                    saveStepData(currentStep);
                }, 2000); // 2 second delay
            });
        });
    </script>
    <!-- Include jQuery & TouchSpin (if not already included in your project) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-touchspin/4.3.0/jquery.bootstrap-touchspin.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-touchspin/4.3.0/jquery.bootstrap-touchspin.min.css">

    <!-- bundle -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.min.js"></script>

    <!-- demo app -->
    <script src="assets/js/pages/demo.form-wizard.js"></script>
    <!-- end demo js-->

</body>

</html>