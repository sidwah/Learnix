<?php
/**
 * Enhanced Course Creation Form
 * File: ../includes/create-course-form.php
 * 
 * Features:
 * - Consistent step naming convention for better tracking
 * - Enhanced navigation with visual step indicators
 * - Improved progress tracking
 * - Better error handling and validation
 * - Optimized UX with clear visual feedback
 */
?>
<form id="createCourseForm" enctype="multipart/form-data" class="needs-validation" novalidate>
    <input type="hidden" id="course_id" name="course_id" value="">
    <input type="hidden" id="current_step" name="current_step" value="1">
    <input type="hidden" id="max_completed_step" name="max_completed_step" value="0">
    
    <div id="courseWizard">
        <!-- Navigation Bar -->
        <ul class="nav nav-pills nav-justified form-wizard-header mb-3">
            <li class="nav-item">
                <a href="#step-1-basics" data-bs-toggle="tab" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2 active" data-step="1">
                    <!-- <div class="step-indicator">1</div> -->
                    <i class="mdi mdi-information-outline me-1"></i>
                    <span class="d-none d-sm-inline">Basic Details</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#step-2-structure" data-bs-toggle="tab" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2" data-step="2">
                    <!-- <div class="step-indicator">2</div> -->
                    <i class="mdi mdi-format-list-bulleted me-1"></i>
                    <span class="d-none d-sm-inline">Course Structure</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#step-3-content" data-bs-toggle="tab" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2" data-step="3">
                    <!-- <div class="step-indicator">3</div> -->
                    <i class="mdi mdi-file-document me-1"></i>
                    <span class="d-none d-sm-inline">Content Creation</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#step-4-resources" data-bs-toggle="tab" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2" data-step="4">
                    <!-- <div class="step-indicator">4</div> -->
                    <i class="mdi mdi-upload me-1"></i>
                    <span class="d-none d-sm-inline">Resource Upload</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#step-5-settings" data-bs-toggle="tab" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2" data-step="5">
                    <!-- <div class="step-indicator">5</div> -->
                    <i class="mdi mdi-currency-usd me-1"></i>
                    <span class="d-none d-sm-inline">Pricing & Settings</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#step-6-review" data-bs-toggle="tab" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2" data-step="6">
                    <!-- <div class="step-indicator">6</div> -->
                    <i class="mdi mdi-checkbox-marked-circle-outline me-1"></i>
                    <span class="d-none d-sm-inline">Review & Publish</span>
                </a>
            </li>
        </ul>

        <div class="tab-content b-0 mb-0">
            <!-- Progress Bar -->
            <div id="wizard-progress" class="progress mb-3" style="height: 8px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                     role="progressbar" style="width: 16.67%;" aria-valuenow="16.67" aria-valuemin="0" aria-valuemax="100"></div>
            </div>

            <!-- Include Tab Content Files with Renamed IDs -->
            <!-- Step 1: Basic Details -->
            <div class="tab-pane fade show active" id="step-1-basics">
                <?php include '../includes/create-course-basic.php'; ?>
            </div>

            <!-- Step 2: Course Structure -->
            <div class="tab-pane fade" id="step-2-structure">
                <?php include '../includes/create-course-structure.php'; ?>
            </div>

            <!-- Step 3: Content Creation -->
            <div class="tab-pane fade" id="step-3-content">
                <?php include '../includes/create-course-content.php'; ?>
            </div>

            <!-- Step 4: Resource Upload -->
            <div class="tab-pane fade" id="step-4-resources">
                <?php include '../includes/create-course-resources.php'; ?>
            </div>

            <!-- Step 5: Pricing & Settings -->
            <div class="tab-pane fade" id="step-5-settings">
                <?php include '../includes/create-course-settings.php'; ?>
            </div>

            <!-- Step 6: Review & Publish -->
            <div class="tab-pane fade" id="step-6-review">
                <?php include '../includes/create-course-review.php'; ?>
            </div>

            <!-- Navigation and Step Indicator -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <button type="button" class="btn btn-secondary prev-step" id="prevButton">
                    <i class="mdi mdi-arrow-left me-1"></i> Previous
                </button>
                
                <div class="step-status text-center">
                    <span class="badge bg-primary p-2">Step <span id="current-step-display">1</span> of 6</span>
                </div>
                
                <button type="button" class="btn btn-primary next-step" id="nextButton">
                    Next <i class="mdi mdi-arrow-right ms-1"></i>
                </button>
            </div>
            
            <!-- Form Error Summary (Hidden by default) -->
            <div id="form-errors" class="alert alert-danger mt-3" style="display: none;">
                <h5><i class="mdi mdi-alert-circle me-2"></i>Please fix the following errors:</h5>
                <ul id="error-list"></ul>
            </div>
        </div> <!-- end tab-content -->
    </div> <!-- end #courseWizard-->
</form>

<!-- Include helper functions -->
<?php include '../includes/create-course-helpers.php'; ?>

<!-- Custom styles for the enhanced wizard -->
<style>
/* Step indicator badges */
.step-indicator {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background-color: #e9ecef;
    color: #495057;
    font-weight: 600;
    margin-right: 8px;
    font-size: 14px;
}

.nav-link.active .step-indicator {
    background-color: white;
    color: #1565C0;
}

/* Improve tab transitions */
.tab-pane {
    transition: all 0.3s ease;
}

.tab-pane.fade {
    opacity: 0;
    transform: translateY(10px);
}

.tab-pane.fade.show {
    opacity: 1;
    transform: translateY(0);
}

/* Make wizard tabs responsive */
@media (max-width: 767.98px) {
    .form-wizard-header .nav-link {
        padding: 0.5rem 0.25rem;
    }
    
    .step-indicator {
        width: 20px;
        height: 20px;
        font-size: 12px;
        margin-right: 4px;
    }
}

/* Improve progress bar */
#wizard-progress .progress-bar {
    transition: width 0.6s ease;
}

/* Navigation button enhancements */
.btn.prev-step:hover, .btn.next-step:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Indicate completed steps */
.nav-link[data-completed="true"] .step-indicator:after {
    content: "✓";
    position: absolute;
    font-size: 12px;
}

/* Loading state for buttons */
.btn.loading {
    position: relative;
    pointer-events: none;
    color: transparent !important;
}

.btn.loading:after {
    content: "";
    position: absolute;
    width: 16px;
    height: 16px;
    top: calc(50% - 8px);
    left: calc(50% - 8px);
    border: 2px solid white;
    border-radius: 50%;
    border-right-color: transparent;
    animation: button-loading-spinner 0.75s linear infinite;
}

@keyframes button-loading-spinner {
    from {
        transform: rotate(0turn);
    }
    to {
        transform: rotate(1turn);
    }
}
</style>

<!-- Enhanced Wizard Navigation Script -->
<script>
/**
 * Enhanced Navigation System for Course Creation Wizard
 * Features:
 * - Robust error handling and validation
 * - Improved UX with loading states
 * - Step tracking and completion status
 * - Cleaner tab navigation and progress indication
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the wizard system
    initEnhancedWizard();
    
    // Check for existing course being edited
    checkForExistingCourse();
});

/**
 * Initialize the enhanced wizard navigation system
 */
function initEnhancedWizard() {
    // Get wizard elements
    const tabLinks = document.querySelectorAll('.nav-pills .nav-link');
    const nextButton = document.getElementById('nextButton');
    const prevButton = document.getElementById('prevButton');
    const progressBar = document.querySelector('#wizard-progress .progress-bar');
    const stepDisplay = document.getElementById('current-step-display');
    
    // Set up tab shown event handlers
    tabLinks.forEach((tabLink) => {
        tabLink.addEventListener('shown.bs.tab', function(event) {
            const step = parseInt(event.target.getAttribute('data-step'), 10);
            
            // Update current step tracking
            document.getElementById('current_step').value = step;
            
            // Update UI elements
            updateWizardUI(step);
            
            // Initialize step-specific content
            initializeStepContent(event.target.getAttribute('href'));
            
            // Log for debugging
            console.log(`Step ${step} activated: ${event.target.getAttribute('href')}`);
        });
        
        // Validate navigation to prevent skipping steps
        tabLink.addEventListener('click', function(event) {
            const targetStep = parseInt(event.target.getAttribute('data-step'), 10);
            const currentStep = parseInt(document.getElementById('current_step').value, 10);
            const maxCompletedStep = parseInt(document.getElementById('max_completed_step').value, 10);
            
            // Only allow navigation to completed steps or current+1
            if (targetStep > currentStep && targetStep > maxCompletedStep + 1) {
                event.preventDefault();
                event.stopPropagation();
                
                showAlert('warning', 'Please complete the current step before skipping ahead.');
                return false;
            }
        });
    });
    
    // Next button handler with validation
    if (nextButton) {
        nextButton.addEventListener('click', function() {
            // Get current step
            const currentStep = parseInt(document.getElementById('current_step').value, 10);
            
            // Process current step and validate before moving to next
            processCurrentStep(currentStep);
        });
    }
    
    // Previous button handler
    if (prevButton) {
        prevButton.addEventListener('click', function() {
            // Get current step
            const currentStep = parseInt(document.getElementById('current_step').value, 10);
            
            // Only go back if not on first step
            if (currentStep > 1) {
                navigateToStep(currentStep - 1);
            }
        });
    }
    
    // Set up publish and draft buttons
    setupActionButtons();
}

/**
 * Process the current step, validate, and save data
 */
function processCurrentStep(step) {
    // Clear any previous errors
    clearFormErrors();
    
    // Show button loading state
    setButtonLoading('nextButton', true);
    
    switch(step) {
        case 1:
            if (typeof saveBasicDetails === 'function') {
                // The original function will handle navigation if successful
                saveBasicDetails();
            } else {
                // For testing - normally we'd validate here
                validateAndNavigate(step);
            }
            break;
            
        case 2:
            if (typeof saveCourseStructure === 'function') {
                saveCourseStructure();
            } else {
                validateAndNavigate(step);
            }
            break;
            
        case 3:
            if (typeof saveAllContent === 'function') {
                saveAllContent();
            } else {
                validateAndNavigate(step);
            }
            break;
            
        case 4:
            if (typeof uploadAllResources === 'function') {
                uploadAllResources();
            } else {
                validateAndNavigate(step);
            }
            break;
            
        case 5:
            if (typeof saveSettings === 'function') {
                saveSettings();
            } else {
                validateAndNavigate(step);
            }
            break;
            
        default:
            // For last step, no next button action needed
            setButtonLoading('nextButton', false);
            break;
    }
}

/**
 * Update UI elements based on current step
 */
function updateWizardUI(step) {
    const totalSteps = 6; // Total number of steps in our wizard
    
    // Update step display
    const stepDisplay = document.getElementById('current-step-display');
    if (stepDisplay) {
        stepDisplay.textContent = step;
    }
    
    // Update progress bar
    const progressPercentage = ((step - 1) / (totalSteps - 1)) * 100;
    const progressBar = document.querySelector('#wizard-progress .progress-bar');
    if (progressBar) {
        progressBar.style.width = `${progressPercentage}%`;
        progressBar.setAttribute('aria-valuenow', progressPercentage);
    }
    
    // Update navigation buttons
    const prevButton = document.getElementById('prevButton');
    const nextButton = document.getElementById('nextButton');
    
    if (prevButton) {
        // Hide previous button on first step
        prevButton.style.display = step === 1 ? 'none' : 'block';
    }
    
    if (nextButton) {
        // Change text for last step or hide on review
        if (step === totalSteps) {
            nextButton.style.display = 'none'; // Hide on review page
        } else if (step === totalSteps - 1) {
            nextButton.innerHTML = 'Review <i class="mdi mdi-arrow-right ms-1"></i>';
            nextButton.style.display = 'block';
        } else {
            nextButton.innerHTML = 'Next <i class="mdi mdi-arrow-right ms-1"></i>';
            nextButton.style.display = 'block';
        }
    }
    
    // Mark completed steps
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        const linkStep = parseInt(link.getAttribute('data-step'), 10);
        
        // Current step is active
        if (linkStep === step) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
        
        // Steps before current are completed
        if (linkStep < step) {
            link.setAttribute('data-completed', 'true');
        }
    });
}

/**
 * Initialize step-specific content
 */
function initializeStepContent(tabId) {
    // Call appropriate initialization function based on tab ID
    switch(tabId) {
        case '#step-3-content':
            if (typeof loadSectionContentUI === 'function') {
                loadSectionContentUI();
            }
            break;
            
        case '#step-4-resources':
            if (typeof loadResourceUploadUI === 'function') {
                loadResourceUploadUI();
            }
            break;
            
        case '#step-5-settings':
            if (typeof loadExistingSettings === 'function') {
                loadExistingSettings();
            }
            break;
            
        case '#step-6-review':
            if (typeof loadCourseReview === 'function') {
                loadCourseReview();
            }
            break;
    }
}

/**
 * Navigate to a specific step
 */
function navigateToStep(step) {
    // Find the tab link for the given step
    const tabLink = document.querySelector(`.nav-link[data-step="${step}"]`);
    
    if (tabLink) {
        // Use Bootstrap's tab API
        const tab = new bootstrap.Tab(tabLink);
        tab.show();
        
        // Update current step tracking
        document.getElementById('current_step').value = step;
        
        // Update UI
        updateWizardUI(step);
        
        // Update max completed step if advancing
        const maxCompleted = parseInt(document.getElementById('max_completed_step').value, 10);
        if (step > maxCompleted) {
            document.getElementById('max_completed_step').value = step - 1; // Mark previous step as completed
        }
        
        // Scroll to top
        window.scrollTo({top: 0, behavior: 'smooth'});
    }
}

/**
 * Validate the current step and navigate to next if valid
 */
function validateAndNavigate(currentStep) {
    // Get step-specific required fields
    const requiredFields = getRequiredFieldsForStep(currentStep);
    
    // Validate all required fields
    let isValid = true;
    let errors = [];
    
    requiredFields.forEach(field => {
        const element = document.getElementById(field.id);
        if (!element || !element.value.trim()) {
            isValid = false;
            errors.push(`${field.label} is required`);
            
            // Highlight invalid field
            if (element) {
                element.classList.add('is-invalid');
            }
        }
    });
    
    // Remove loading state
    setButtonLoading('nextButton', false);
    
    if (!isValid) {
        // Show errors
        displayFormErrors(errors);
        return false;
    }
    
    // Valid - mark step as completed and navigate to next
    const maxCompleted = parseInt(document.getElementById('max_completed_step').value, 10);
    if (currentStep > maxCompleted) {
        document.getElementById('max_completed_step').value = currentStep;
    }
    
    // Navigate to next step
    navigateToStep(currentStep + 1);
    return true;
}

/**
 * Get required fields for specific step
 */
function getRequiredFieldsForStep(step) {
    // Return different fields based on the step
    switch(step) {
        case 1:
            return [
                { id: 'courseTitle', label: 'Course Title' },
                { id: 'shortDescription', label: 'Short Description' },
                { id: 'subcategory', label: 'Category' }
            ];
            
        case 2:
            // Course structure validation fields
            return [];
            
        case 3:
            // Content validation fields
            return [];
            
        case 4:
            // Resources validation fields
            return [];
            
        case 5:
            return [
                { id: 'courseLevel', label: 'Course Level' }
            ];
            
        default:
            return [];
    }
}

/**
 * Display form validation errors
 */
function displayFormErrors(errors) {
    if (!errors || errors.length === 0) return;
    
    const errorContainer = document.getElementById('form-errors');
    const errorList = document.getElementById('error-list');
    
    if (errorContainer && errorList) {
        // Clear existing errors
        errorList.innerHTML = '';
        
        // Add new errors
        errors.forEach(error => {
            const li = document.createElement('li');
            li.textContent = error;
            errorList.appendChild(li);
        });
        
        // Show error container
        errorContainer.style.display = 'block';
        
        // Scroll to errors
        errorContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    
    // Also show an alert for immediate attention
    showAlert('danger', 'Please fix the form errors before proceeding.');
}

/**
 * Clear form errors
 */
function clearFormErrors() {
    const errorContainer = document.getElementById('form-errors');
    if (errorContainer) {
        errorContainer.style.display = 'none';
    }
    
    // Clear invalid state from fields
    document.querySelectorAll('.is-invalid').forEach(field => {
        field.classList.remove('is-invalid');
    });
}

/**
 * Set button loading state
 */
function setButtonLoading(buttonId, isLoading) {
    const button = document.getElementById(buttonId);
    if (button) {
        if (isLoading) {
            button.classList.add('loading');
            button.setAttribute('disabled', 'disabled');
        } else {
            button.classList.remove('loading');
            button.removeAttribute('disabled');
        }
    }
}

/**
 * Check if editing an existing course
 */
function checkForExistingCourse() {
    // Get course ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const courseId = urlParams.get('course_id');
    
    if (courseId) {
        // Set course ID in the hidden field
        document.getElementById('course_id').value = courseId;
        console.log(`Loading existing course: ${courseId}`);
        
        // Show loading overlay
        createOverlay('Loading course details...');
        
        // We'd typically load the course data here with AJAX
        // For now, just simulate the process
        setTimeout(() => {
            removeOverlay();
            showAlert('info', 'Editing existing course. Some fields have been pre-filled.');
        }, 1000);
    }
}

/**
 * Setup action buttons on the review page
 */
function setupActionButtons() {
    // Publish button
    const publishButton = document.getElementById('publishCourse');
    if (publishButton) {
        publishButton.addEventListener('click', function(e) {
            if (typeof publishCourse === 'function') {
                e.preventDefault();
                
                // Show confirmation dialog
                if (confirm('Are you sure you want to publish this course? Once published, it will be submitted for review.')) {
                    publishCourse();
                }
            }
        });
    }
    
    // Save as draft button
    const saveDraftButton = document.getElementById('saveDraft');
    if (saveDraftButton) {
        saveDraftButton.addEventListener('click', function(e) {
            if (typeof saveDraft === 'function') {
                e.preventDefault();
                saveDraft();
            }
        });
    }
}

/**
 * Show alert notification
 */
function showAlert(type, message, duration = 5000) {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.setAttribute('role', 'alert');
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.maxWidth = '400px';
    alertDiv.style.zIndex = '9999';
    alertDiv.style.boxShadow = '0 4px 8px rgba(0,0,0,0.15)';
    
    // Add proper icon based on type
    const iconClass = type === 'success' ? 'mdi-check-circle' : 
                     type === 'danger' ? 'mdi-alert-circle' :
                     type === 'warning' ? 'mdi-alert' : 'mdi-information';
    
    alertDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="mdi ${iconClass} me-2"></i>
            <div>${message}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add to document
    document.body.appendChild(alertDiv);
    
    // Auto-dismiss
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.classList.remove('show');
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.parentNode.removeChild(alertDiv);
                }
            }, 300);
        }
    }, duration);
}

/**
 * Create loading overlay
 */
function createOverlay(message = "Loading...") {
    // Remove existing overlay
    removeOverlay();
    
    // Create overlay
    const overlay = document.createElement('div');
    overlay.id = 'pageOverlay';
    overlay.className = 'position-fixed d-flex flex-column justify-content-center align-items-center';
    overlay.style.top = '0';
    overlay.style.left = '0';
    overlay.style.width = '100%';
    overlay.style.height = '100%';
    overlay.style.backgroundColor = 'rgba(255, 255, 255, 0.8)';
    overlay.style.backdropFilter = 'blur(3px)';
    overlay.style.zIndex = '9999';
    
    // Add spinner and message
    overlay.innerHTML = `
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="mt-3 fw-bold fs-5 text-primary">${message}</div>
    `;
    
    // Add to document
    document.body.appendChild(overlay);
}

/**
 * Remove loading overlay
 */
function removeOverlay() {
    const overlay = document.getElementById('pageOverlay');
    if (overlay) {
        document.body.removeChild(overlay);
    }
}
</script>