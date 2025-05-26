<?php
// department/initiate-course.php
include '../includes/department/header.php';
?>

<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main">
    <!-- Navbar -->
    <nav class="js-nav-scroller navbar navbar-expand-lg navbar-sidebar navbar-vertical navbar-light bg-white border-end" data-hs-nav-scroller-options='{
            "type": "vertical",
            "target": ".navbar-nav .active",
            "offset": 80
           }'>

        <?php include '../includes/department/sidebar.php'; ?>
    </nav>
    <!-- End Navbar -->

    <!-- Content -->
    <div class="navbar-sidebar-aside-content content-space-1 content-space-md-2 px-lg-5 px-xl-5">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="page-header-title">Create New Course</h1>
                    <p class="page-header-text">Set up a new course for your department</p>
                </div>
                <div class="col-auto">
                    <button class="btn btn-ghost-secondary me-2" onclick="saveDraft()">
                        <i class="bi-cloud-arrow-up me-1"></i> Save Draft
                    </button>
                    <a href="courses.php" class="btn btn-ghost-secondary">
                        <i class="bi-arrow-left me-1"></i> Back to Courses
                    </a>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Course Creation Wizard -->
        <div class="card card-body">
            <!-- Step Indicator -->
            <div class="step-indicator mb-5">
                <ul class="step-indicator-nav">
                    <li class="step-indicator-item active" data-step="1">
                        <div class="step-indicator-wrapper">
                            <span class="step-indicator-icon">
                                <i class="bi-info-circle"></i>
                            </span>
                            <div class="step-indicator-content">
                                <span class="step-indicator-title">Basic Information</span>
                                <span class="step-indicator-text">Course title, category, and level</span>
                            </div>
                        </div>
                    </li>

                    <li class="step-indicator-item" data-step="2">
                        <div class="step-indicator-wrapper">
                            <span class="step-indicator-icon">
                                <i class="bi-card-text"></i>
                            </span>
                            <div class="step-indicator-content">
                                <span class="step-indicator-title">Course Details</span>
                                <span class="step-indicator-text">Description and learning outcomes</span>
                            </div>
                        </div>
                    </li>

                    <li class="step-indicator-item" data-step="3">
                        <div class="step-indicator-wrapper">
                            <span class="step-indicator-icon">
                                <i class="bi-gear"></i>
                            </span>
                            <div class="step-indicator-content">
                                <span class="step-indicator-title">Settings</span>
                                <span class="step-indicator-text">Price, access, and configuration</span>
                            </div>
                        </div>
                    </li>

                    <li class="step-indicator-item" data-step="4">
                        <div class="step-indicator-wrapper">
                            <span class="step-indicator-icon">
                                <i class="bi-check-circle"></i>
                            </span>
                            <div class="step-indicator-content">
                                <span class="step-indicator-title">Review</span>
                                <span class="step-indicator-text">Finalize and save</span>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
            <!-- End Step Indicator -->

            <!-- Course Form -->
            <form id="courseInitiationForm" enctype="multipart/form-data">
                <!-- Step 1: Basic Information -->
                <div id="step1" class="step-content active">
                    <div class="row g-4">
                        <div class="col-12">
                            <h4 class="mb-4">Basic Information</h4>
                        </div>

                        <div class="col-md-12">
                            <label for="title" class="form-label">Course Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="Enter course title" required maxlength="255">
                            <div class="form-text">A clear, descriptive name that tells students what they'll learn</div>
                        </div>

                        <div class="col-md-6">
                            <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select a category</option>
                                <!-- Populated via AJAX -->
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="subcategory_id" class="form-label">Subcategory <span class="text-danger">*</span></label>
                            <select class="form-select" id="subcategory_id" name="subcategory_id" required>
                                <option value="">First select a category</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="course_level" class="form-label">Course Level <span class="text-danger">*</span></label>
                            <select class="form-select" id="course_level" name="course_level" required>
                                <option value="">Select difficulty level</option>
                                <option value="Beginner">Beginner</option>
                                <option value="Intermediate">Intermediate</option>
                                <option value="Advanced">Advanced</option>
                                <option value="All Levels">All Levels</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="estimated_duration" class="form-label">Estimated Duration</label>
                            <select class="form-select" id="estimated_duration" name="estimated_duration">
                                <option value="">Select duration</option>
                                <option value="Less than 1 hour">Less than 1 hour</option>
                                <option value="1-2 hours">1-2 hours</option>
                                <option value="3-5 hours">3-5 hours</option>
                                <option value="6-10 hours">6-10 hours</option>
                                <option value="11-20 hours">11-20 hours</option>
                                <option value="21-40 hours">21-40 hours</option>
                                <option value="40+ hours">40+ hours</option>
                                <option value="1 week">1 week</option>
                                <option value="2-3 weeks">2-3 weeks</option>
                                <option value="1 month">1 month</option>
                                <option value="2-3 months">2-3 months</option>
                                <option value="6+ months">6+ months</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="short_description" class="form-label">Short Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="short_description" name="short_description" rows="3" placeholder="Brief overview of the course" required maxlength="255"></textarea>
                            <div class="form-text d-flex justify-content-between">
                                <span>This appears in course listings</span>
                                <span id="short_desc_count">0/255</span>
                            </div>
                        </div>

                        <!-- Course Thumbnail Field -->
                        <div class="col-12 mt-3">
                            <label for="thumbnail" class="form-label">Course Thumbnail</label>
                            <div class="input-group">
                                <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/png, image/jpeg, image/jpg">
                                <button class="btn btn-outline-secondary" type="button" id="thumbnailPreviewBtn">Preview</button>
                            </div>
                            <div class="form-text">Recommended size: 600x400 pixels. Maximum file size: 2MB.</div>
                            <div id="thumbnailPreview" class="mt-2" style="display: none;">
                                <img src="" alt="Thumbnail preview" class="img-thumbnail" style="max-height: 200px;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Course Details -->
                <div id="step2" class="step-content">
                    <div class="row g-4">
                        <div class="col-12">
                            <h4 class="mb-4">Course Details</h4>
                        </div>

                        <div class="col-12">
                            <label for="full_description" class="form-label">Full Description</label>
                            <div id="full_description" class="rich-text-editor" style="height: 200px;"></div>
                            <!-- Character counter will be added here dynamically -->
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label mb-0">Learning Outcomes</label>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addOutcome()">
                                    <i class="bi-plus me-1"></i> Add Outcome
                                </button>
                            </div>
                            <div id="learningOutcomes">
                                <div class="outcome-item d-flex gap-2 mb-2">
                                    <span class="badge bg-soft-primary text-primary d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">1</span>
                                    <input type="text" class="form-control" name="outcomes[]" placeholder="What will students learn?">
                                    <button type="button" class="btn btn-ghost-danger btn-sm remove-outcome" style="display:none;">
                                        <i class="bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-text">Define what students will be able to do after completing this course</div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label mb-0">Requirements</label>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addRequirement()">
                                    <i class="bi-plus me-1"></i> Add Requirement
                                </button>
                            </div>
                            <div id="courseRequirements">
                                <div class="requirement-item d-flex gap-2 mb-2">
                                    <span class="badge bg-soft-danger text-danger d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">!</span>
                                    <input type="text" class="form-control" name="requirements[]" placeholder="What do students need before taking this course?">
                                    <button type="button" class="btn btn-ghost-danger btn-sm remove-requirement" style="display:none;">
                                        <i class="bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-text">List any prerequisites, technical requirements, or materials needed</div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Settings -->
                <div id="step3" class="step-content">
                    <div class="row g-4">
                        <div class="col-12">
                            <h4 class="mb-4">Course Settings</h4>
                        </div>

                        <div class="col-md-6">
                            <label for="price" class="form-label">Price (GHS)</label>
                            <div class="input-group">
                                <span class="input-group-text">₵</span>
                                <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" value="0.00" pattern="^\d*(\.\d{0,2})?$">
                            </div>
                            <div class="form-text">Set to 0 for free courses</div>
                        </div>

                        <div class="col-md-6">
                            <label for="access_level" class="form-label">Access Level</label>
                            <select class="form-select" id="access_level" name="access_level">
                                <option value="Public" selected>Public - Anyone can enroll</option>
                                <option value="Restricted">Restricted - Requires approval</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="visibility" class="form-label">Visibility</label>
                            <select class="form-select" id="visibility" name="visibility">
                                <option value="Public" selected>Public - Visible in course listings</option>
                                <option value="Private">Private - Only accessible via direct link</option>
                                <option value="Password Protected">Password Protected</option>
                                <option value="Coming Soon">Coming Soon - Visible but not enrollable</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="enrollment_limit" class="form-label">Enrollment Limit</label>
                            <input type="number" class="form-control" id="enrollment_limit" name="enrollment_limit" min="1" placeholder="Leave empty for unlimited">
                            <div class="form-text">Maximum number of students allowed</div>
                        </div>

                        <div class="col-12" id="passwordField" style="display:none;">
                            <label for="access_password" class="form-label">Access Password</label>
                            <input type="password" class="form-control" id="access_password" name="access_password" placeholder="Enter access password">
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="certificate_enabled" name="certificate_enabled" value="1">
                                <label class="form-check-label" for="certificate_enabled">Enable Certificate of Completion</label>
                            </div>
                            <div class="form-text">Students will receive a certificate upon course completion</div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Review -->
                <div id="step4" class="step-content">
                    <div class="row g-4">
                        <div class="col-12">
                            <h4 class="mb-4">Review Course Details</h4>
                            <div class="alert alert-info">
                                <i class="bi-info-circle me-2"></i>
                                Please review all information before finalizing. You can still edit these details after creating the course.
                            </div>
                        </div>

                        <!-- Thumbnail preview in review step -->
                        <div class="col-12 mb-3">
                            <h6>Course Thumbnail:</h6>
                            <div id="review_thumbnail" class="text-center">
                                <span class="text-muted">No thumbnail uploaded</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card bg-soft-light">
                                <div class="card-body">
                                    <h6 class="card-title">Basic Information</h6>
                                    <dl class="row">
                                        <dt class="col-sm-5">Title:</dt>
                                        <dd class="col-sm-7" id="review_title">-</dd>

                                        <dt class="col-sm-5">Level:</dt>
                                        <dd class="col-sm-7" id="review_level">-</dd>

                                        <dt class="col-sm-5">Category:</dt>
                                        <dd class="col-sm-7" id="review_category">-</dd>

                                        <dt class="col-sm-5">Duration:</dt>
                                        <dd class="col-sm-7" id="review_duration">-</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card bg-soft-light">
                                <div class="card-body">
                                    <h6 class="card-title">Settings</h6>
                                    <dl class="row">
                                        <dt class="col-sm-5">Price:</dt>
                                        <dd class="col-sm-7" id="review_price">-</dd>

                                        <dt class="col-sm-5">Access:</dt>
                                        <dd class="col-sm-7" id="review_access">-</dd>

                                        <dt class="col-sm-5">Certificate:</dt>
                                        <dd class="col-sm-7" id="review_certificate">-</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card bg-soft-light">
                                <div class="card-body">
                                    <h6 class="card-title">Course Overview</h6>
                                    <p id="review_description" class="text-muted">-</p>

                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <h6>Learning Outcomes:</h6>
                                            <ul id="review_outcomes" class="text-muted">
                                                <li>No outcomes specified</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Requirements:</h6>
                                            <ul id="review_requirements" class="text-muted">
                                                <li>No requirements specified</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="d-flex justify-content-between mt-5 pt-4 border-top">
                    <button type="button" class="btn btn-ghost-secondary" id="prevBtn" onclick="prevStep()" style="display:none;">
                        <i class="bi-arrow-left me-1"></i> Previous
                    </button>

                    <div class="d-flex gap-2 ms-auto">
                        <button type="button" class="btn btn-ghost-secondary" onclick="saveDraft()">
                            <i class="bi-cloud-arrow-up me-1"></i> Save Draft
                        </button>
                        <button type="button" class="btn btn-primary" id="nextBtn" onclick="nextStep()">
                            Next <i class="bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <!-- End Course Creation Wizard -->
    </div>
    <!-- End Content -->

    <!-- Toast -->
    <div id="liveToast" class="position-fixed toast hide" role="alert" aria-live="assertive" aria-atomic="true" style="top: 20px; right: 20px; z-index: 1000;">
        <div class="toast-header">
            <div class="d-flex align-items-center flex-grow-1">
                <div id="toastIcon" class="flex-shrink-0 rounded-circle bg-success bg-opacity-10 text-success p-2 d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px;">
                    <i class="bi bi-check-lg fs-6"></i>
                </div>
                <div class="flex-grow-1">
                    <h5 id="toastTitle" class="mb-0">System Notification</h5>
                    <small id="toastTime">Just Now</small>
                </div>
                <div class="text-end">
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
        <div id="toastBody" class="toast-body">
            Hello, world! This is a toast message.
        </div>
    </div>
    <!-- End Toast -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- Custom CSS -->
<style>
    /* Step Indicator Styles */
    .step-indicator {
        position: relative;
    }

    .step-indicator-nav {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 0;
        position: relative;
    }

    .step-indicator-nav::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 20px;
        right: 20px;
        height: 2px;
        background-color: #e7eaf3;
        z-index: 1;
    }

    .step-indicator-item {
        flex: 1;
        text-align: center;
        position: relative;
    }

    .step-indicator-wrapper {
        position: relative;
        z-index: 2;
    }

    /* Replace the step indicator styles */
    .step-indicator-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #f8f9fa;
        color: #adb5bd;
        font-size: 18px;
        transition: all 0.3s ease;
        border: 2px solid #e9ecef;
    }

    .step-indicator-title {
        display: block;
        font-weight: 400;
        color: #6c757d;
        transition: color 0.3s ease;
    }

    .step-indicator-text {
        display: block;
        font-size: 0.75rem;
        color: #adb5bd;
    }

    .step-indicator-item.active .step-indicator-icon {
        background-color: #e3f2fd;
        color: #1976d2;
        border-color: #90caf9;
    }

    .step-indicator-item.active .step-indicator-title {
        color: #1976d2;
    }

    .step-indicator-item.completed .step-indicator-icon {
        background-color: #e8f5e9;
        color: #43a047;
        border-color: #81c784;
    }

    .step-indicator-item.completed::before {
        content: '';
        position: absolute;
        top: 20px;
        left: -50%;
        width: 100%;
        height: 2px;
        background-color: #81c784;
        z-index: 1;
    }

    .step-indicator-nav::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 20px;
        right: 20px;
        height: 2px;
        background-color: #e9ecef;
        z-index: 1;
    }

    /* Form Step Content */
    .step-content {
        display: none;
        animation: fadeIn 0.3s ease;
    }

    .step-content.active {
        display: block;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Rich Text Editor */
    .rich-text-editor {
        border: 1px solid #e7eaf3;
        border-radius: 0.375rem;
    }

    /* Custom scrollbar for step content */
    .step-content::-webkit-scrollbar {
        width: 6px;
    }

    .step-content::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .step-content::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }

    .step-content::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .step-indicator-nav {
            flex-direction: column;
            align-items: center;
        }

        .step-indicator-nav::before {
            display: none;
        }

        .step-indicator-item {
            margin-bottom: 1rem;
        }

        .step-indicator-item::before {
            display: none !important;
        }
    }
</style>

<!-- JavaScript for Course Initiation -->
<script>
    class CourseInitiator {
        constructor() {
            this.currentStep = 1;
            this.totalSteps = 4;
            this.courseId = null;
            this.thumbnailUrl = null; // Store thumbnail URL
            this.isEditMode = false;
            this.courseData = null; // Store loaded course data

            // Check if we're in edit mode
            const urlParams = new URLSearchParams(window.location.search);
            const courseIdParam = urlParams.get('course_id');

            if (courseIdParam) {
                this.isEditMode = true;
                this.courseId = courseIdParam;
            }

            this.initializeEventListeners();
            this.setupForm();

            // Load course data after form setup (if in edit mode)
            if (this.isEditMode) {
                this.loadCourseData(courseIdParam);
            }
        }

        async loadCourseData(courseId) {
            try {
                const response = await fetch(`../backend/courses/fetch_course_details.php?course_id=${courseId}`);
                const data = await response.json();

                if (!data.success) {
                    this.showToast(data.message || 'Failed to load course data', 'danger');
                    return;
                }

                // Store course data
                this.courseData = data;

                // Populate form with course data once categories are loaded
                this.populateFormWithCourseData(data);
            } catch (error) {
                console.error('Error loading course data:', error);
                this.showToast('Error loading course data', 'danger');
            }
        }

        populateFormWithCourseData(data) {
            const form = document.getElementById('courseInitiationForm');
            const course = data.course;

            // Step 1: Basic Information
            form.title.value = course.title || '';
            form.short_description.value = course.short_description || '';

            // Update character count
            document.getElementById('short_desc_count').textContent = (course.short_description?.length || 0) + '/255';

            // Set category and then load subcategories
            if (course.category_id) {
                // First set the category
                const categorySelect = document.getElementById('category_id');
                categorySelect.value = course.category_id;

                // Then load subcategories
                this.loadSubcategories().then(() => {
                    // Set subcategory after subcategories are loaded
                    if (course.subcategory_id) {
                        const subcategorySelect = document.getElementById('subcategory_id');
                        subcategorySelect.value = course.subcategory_id;
                    }
                });
            }

            // Other fields
            if (course.course_level) {
                const levelSelect = document.getElementById('course_level');
                levelSelect.value = course.course_level;
            }

            if (course.estimated_duration) {
                const durationSelect = document.getElementById('estimated_duration');
                durationSelect.value = course.estimated_duration;
            }

            // Set thumbnail preview if exists
            if (course.thumbnail_url) {
                this.thumbnailUrl = course.thumbnail_url;
                const previewContainer = document.getElementById('thumbnailPreview');
                const previewImg = previewContainer.querySelector('img');
                previewImg.src = course.thumbnail_url;
                previewContainer.style.display = 'block';
            }

            // Step 2: Course Details
            if (course.full_description) {
                this.quill.root.innerHTML = course.full_description;
            }

            // Add learning outcomes
            if (data.outcomes && data.outcomes.length > 0) {
                // Clear default outcome
                document.getElementById('learningOutcomes').innerHTML = '';
                data.outcomes.forEach((outcome, index) => {
                    this.addOutcome(outcome);
                });
            }

            // Add requirements
            if (data.requirements && data.requirements.length > 0) {
                // Clear default requirement
                document.getElementById('courseRequirements').innerHTML = '';
                data.requirements.forEach((requirement, index) => {
                    this.addRequirement(requirement);
                });
            }

            // Step 3: Settings
            if (course.price) {
                document.getElementById('price').value = parseFloat(course.price).toFixed(2);
            }

            if (course.access_level) {
                document.getElementById('access_level').value = course.access_level;
            }

            if (course.visibility) {
                document.getElementById('visibility').value = course.visibility;
                this.togglePasswordField(); // Show password field if needed
            }

            if (course.enrollment_limit) {
                document.getElementById('enrollment_limit').value = course.enrollment_limit;
            }

            if (course.access_password && course.visibility === 'Password Protected') {
                document.getElementById('access_password').value = course.access_password;
            }

            if (course.certificate_enabled) {
                document.getElementById('certificate_enabled').checked =
                    course.certificate_enabled === "1" ||
                    course.certificate_enabled === 1 ||
                    course.certificate_enabled === true;
            }

            // Update UI based on edit mode
            this.updateUIForEditMode();
        }

        updateUIForEditMode() {
            if (this.isEditMode) {
                // Change page title and buttons
                document.querySelector('.page-header-title').textContent = 'Edit Course';
                document.querySelector('.page-header-text').textContent = 'Update course details';

                // Change final button text
                const nextBtn = document.getElementById('nextBtn');
                if (this.currentStep === this.totalSteps) {
                    nextBtn.innerHTML = '<i class="bi-check-circle me-1"></i> Update Course';
                    nextBtn.className = 'btn btn-success';
                }
            }
        }

        initializeEventListeners() {
            // Character counter for short description
            document.getElementById('short_description').addEventListener('input', (e) => {
                document.getElementById('short_desc_count').textContent = e.target.value.length + '/255';
            });

            // Category change handler
            document.getElementById('category_id').addEventListener('change', this.loadSubcategories.bind(this));

            // Visibility change handler
            document.getElementById('visibility').addEventListener('change', this.togglePasswordField.bind(this));
        }

        setupForm() {
            // Setup rich text editor using Quill
            // Setup rich text editor using Quill
            this.quill = new Quill('#full_description', {
                theme: 'snow',
                placeholder: 'Write a comprehensive description of your course...',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        ['blockquote', 'code-block'],
                        [{
                            'header': 1
                        }, {
                            'header': 2
                        }],
                        [{
                            'list': 'ordered'
                        }, {
                            'list': 'bullet'
                        }],
                        [{
                            'script': 'sub'
                        }, {
                            'script': 'super'
                        }],
                        [{
                            'indent': '-1'
                        }, {
                            'indent': '+1'
                        }],
                        ['link', 'image'],
                        ['clean']
                    ]
                }
            });

            // Add character counter for full description
            this.quill.on('text-change', () => {
                const textLength = this.quill.getText().trim().length;
                const existingCounter = document.getElementById('full_desc_count');

                if (!existingCounter) {
                    // Create counter element if it doesn't exist
                    const counterDiv = document.createElement('div');
                    counterDiv.className = 'form-text d-flex justify-content-between mt-2';
                    counterDiv.innerHTML = `
            <span>Minimum 60 characters required</span>
            <span id="full_desc_count">${textLength}/60+</span>
        `;
                    document.querySelector('#full_description').parentNode.appendChild(counterDiv);
                } else {
                    // Update existing counter
                    const counter = document.getElementById('full_desc_count');
                    counter.textContent = `${textLength}/60+`;

                    // Change color based on whether minimum is met
                    if (textLength < 60) {
                        counter.style.color = '#dc3545'; // Red
                    } else {
                        counter.style.color = '#198754'; // Green
                    }
                }
            });

            // Load department categories
            this.loadCategories();

            // Setup price validation
            const priceInput = document.getElementById('price');
            priceInput.addEventListener('input', (e) => {
                let value = e.target.value;
                // Remove any non-numeric characters except decimal point
                value = value.replace(/[^0-9.]/g, '');
                // Ensure only one decimal point
                const parts = value.split('.');
                if (parts.length > 2) {
                    value = parts[0] + '.' + parts[1];
                }
                // Limit to 2 decimal places
                if (parts.length === 2 && parts[1].length > 2) {
                    value = parts[0] + '.' + parts[1].substring(0, 2);
                }
                e.target.value = value;
            });

            // Thumbnail preview
            document.getElementById('thumbnailPreviewBtn').addEventListener('click', () => {
                const thumbnailInput = document.getElementById('thumbnail');
                const previewContainer = document.getElementById('thumbnailPreview');
                const previewImg = previewContainer.querySelector('img');

                if (thumbnailInput.files && thumbnailInput.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        previewContainer.style.display = 'block';
                    }
                    reader.readAsDataURL(thumbnailInput.files[0]);
                } else {
                    previewContainer.style.display = 'none';
                }
            });

            // Thumbnail file change event
            document.getElementById('thumbnail').addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const fileSize = this.files[0].size / 1024 / 1024; // Size in MB
                    if (fileSize > 2) {
                        this.value = ''; // Clear the field
                        this.classList.add('is-invalid');
                        const feedback = document.querySelector('.invalid-feedback');
                        if (feedback) {
                            feedback.remove();
                        }
                        this.insertAdjacentHTML('afterend', '<div class="invalid-feedback">File size exceeds 2MB limit.</div>');
                    } else {
                        this.classList.remove('is-invalid');
                        const feedback = document.querySelector('.invalid-feedback');
                        if (feedback) {
                            feedback.remove();
                        }
                    }
                }
            });
        }

        async loadCategories() {
            try {
                const response = await fetch('../backend/courses/fetch_categories_dropdown.php');
                const categories = await response.json();

                const categorySelect = document.getElementById('category_id');
                categorySelect.innerHTML = '<option value="">Select a category</option>';

                categories.forEach(category => {
                    categorySelect.innerHTML += `<option value="${category.category_id}">${category.name}</option>`;
                });

                // If in edit mode and we have course data, populate category and subcategory
                if (this.isEditMode && this.courseData) {
                    const course = this.courseData.course;
                    if (course.category_id) {
                        categorySelect.value = course.category_id;

                        // Then load and set subcategories
                        this.loadSubcategories().then(() => {
                            if (course.subcategory_id) {
                                const subcategorySelect = document.getElementById('subcategory_id');
                                subcategorySelect.value = course.subcategory_id;
                            }
                        });
                    }
                }

            } catch (error) {
                console.error('Error loading categories:', error);
                this.showToast('Error loading categories', 'danger');
            }
        }

        async loadSubcategories() {
            const categoryId = document.getElementById('category_id').value;
            const subcategorySelect = document.getElementById('subcategory_id');

            if (!categoryId) {
                subcategorySelect.innerHTML = '<option value="">First select a category</option>';
                return;
            }

            // Show loading indicator
            subcategorySelect.innerHTML = '<option value="">Loading...</option>';

            try {
                // Fixed API endpoint to properly filter subcategories
                const response = await fetch(`../backend/courses/get_subcategories.php?category_id=${categoryId}`);
                const subcategories = await response.json();

                subcategorySelect.innerHTML = '<option value="">Select a subcategory</option>';

                if (subcategories && subcategories.length > 0) {
                    subcategories.forEach(subcategory => {
                        subcategorySelect.innerHTML += `<option value="${subcategory.subcategory_id}">${subcategory.name}</option>`;
                    });
                } else {
                    subcategorySelect.innerHTML += '<option value="">No subcategories available</option>';
                }

                // If we're in edit mode and have course data with a subcategory, select it
                if (this.isEditMode && this.courseData && this.courseData.course.subcategory_id) {
                    subcategorySelect.value = this.courseData.course.subcategory_id;
                }

                return subcategories;
            } catch (error) {
                console.error('Error loading subcategories:', error);
                subcategorySelect.innerHTML = '<option value="">Error loading subcategories</option>';
                this.showToast('Error loading subcategories', 'danger');
                return [];
            }
        }

        togglePasswordField() {
            const visibility = document.getElementById('visibility').value;
            const passwordField = document.getElementById('passwordField');
            passwordField.style.display = visibility === 'Password Protected' ? 'block' : 'none';
        }

        // Modified to accept an initial value
        addOutcome(initialValue = '') {
            const container = document.getElementById('learningOutcomes');
            const index = container.children.length + 1;
            const div = document.createElement('div');
            div.className = 'outcome-item d-flex gap-2 mb-2';
            div.innerHTML = `
            <span class="badge bg-soft-primary text-primary d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">${index}</span>
            <input type="text" class="form-control" name="outcomes[]" placeholder="What will students learn?" value="${initialValue}">
            <button type="button" class="btn btn-ghost-danger btn-sm remove-outcome">
                <i class="bi-trash"></i>
            </button>
        `;
            container.appendChild(div);
            this.attachRemoveListener(div.querySelector('.remove-outcome'));
            this.updateRemoveButtons('learningOutcomes', '.remove-outcome');
        }

        // Modified to accept an initial value
        addRequirement(initialValue = '') {
            const container = document.getElementById('courseRequirements');
            const div = document.createElement('div');
            div.className = 'requirement-item d-flex gap-2 mb-2';
            div.innerHTML = `
            <span class="badge bg-soft-danger text-danger d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">!</span>
            <input type="text" class="form-control" name="requirements[]" placeholder="What do students need before taking this course?" value="${initialValue}">
            <button type="button" class="btn btn-ghost-danger btn-sm remove-requirement">
                <i class="bi-trash"></i>
            </button>
        `;
            container.appendChild(div);
            this.attachRemoveListener(div.querySelector('.remove-requirement'));
            this.updateRemoveButtons('courseRequirements', '.remove-requirement');
        }

        attachRemoveListener(button) {
            button.addEventListener('click', (e) => {
                e.target.closest('div').remove();
                const container = e.target.closest('.outcome-item') ? 'learningOutcomes' : 'courseRequirements';
                const selector = e.target.closest('.outcome-item') ? '.remove-outcome' : '.remove-requirement';
                this.updateRemoveButtons(container, selector);

                if (container === 'learningOutcomes') {
                    this.updateOutcomeNumbers();
                }
            });
        }

        updateRemoveButtons(containerId, selector) {
            const container = document.getElementById(containerId);
            const items = container.querySelectorAll(selector);
            items.forEach((button, index) => {
                button.style.display = items.length > 1 ? 'inline-block' : 'none';
            });
        }

        updateOutcomeNumbers() {
            const badges = document.querySelectorAll('#learningOutcomes .badge');
            badges.forEach((badge, index) => {
                badge.textContent = index + 1;
            });
        }

        async nextStep() {
            if (!this.validateStep(this.currentStep)) return;

            if (this.currentStep < this.totalSteps) {
                try {
                    await this.saveStep(this.currentStep);

                    // Add a check to ensure courseId exists before moving to step 2+
                    if (this.currentStep === 1 && !this.courseId) {
                        this.showToast('Failed to create course. Please try again.', 'danger');
                        return;
                    }

                    this.currentStep++;
                    this.updateStepDisplay();

                    if (this.currentStep === 4) {
                        this.populateReview();
                    }
                } catch (error) {
                    // Error is already handled in saveStep, just prevent progression
                    return;
                }
            } else {
                this.finalizeCourse();
            }
        }

        prevStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
                this.updateStepDisplay();
            }
        }

        updateStepDisplay() {
            // Update step indicator
            const indicators = document.querySelectorAll('.step-indicator-item');
            indicators.forEach((indicator, index) => {
                indicator.classList.remove('active', 'completed');
                if (index + 1 === this.currentStep) {
                    indicator.classList.add('active');
                } else if (index + 1 < this.currentStep) {
                    indicator.classList.add('completed');
                }
            });

            // Update step content
            const contents = document.querySelectorAll('.step-content');
            contents.forEach((content, index) => {
                content.classList.remove('active');
                if (index + 1 === this.currentStep) {
                    content.classList.add('active');
                }
            });

            // Update navigation buttons
            document.getElementById('prevBtn').style.display = this.currentStep === 1 ? 'none' : 'inline-block';
            const nextBtn = document.getElementById('nextBtn');
            if (this.currentStep === this.totalSteps) {
                if (this.isEditMode) {
                    nextBtn.innerHTML = '<i class="bi-check-circle me-1"></i> Update Course';
                } else {
                    nextBtn.innerHTML = '<i class="bi-check-circle me-1"></i> Create Course';
                }
                nextBtn.className = 'btn btn-success';
            } else {
                nextBtn.innerHTML = 'Next <i class="bi-arrow-right ms-1"></i>';
                nextBtn.className = 'btn btn-primary';
            }
        }

        validateStep(step) {
            let isValid = true;
            const form = document.getElementById('courseInitiationForm');

            switch (step) {
                case 1:
                    const fields = ['title', 'category_id', 'subcategory_id', 'course_level', 'short_description'];
                    fields.forEach(field => {
                        const element = form[field];
                        if (!element.value.trim()) {
                            element.classList.add('is-invalid');
                            isValid = false;
                        } else {
                            element.classList.remove('is-invalid');
                        }
                    });

                    // Validate thumbnail if one is selected
                    if (form.thumbnail.files.length > 0) {
                        const file = form.thumbnail.files[0];
                        const fileSize = file.size / 1024 / 1024; // Size in MB
                        const fileType = file.type;

                        if (fileSize > 2) {
                            form.thumbnail.classList.add('is-invalid');
                            this.showToast('Thumbnail file size exceeds 2MB limit', 'warning');
                            isValid = false;
                        } else if (!['image/jpeg', 'image/jpg', 'image/png'].includes(fileType)) {
                            form.thumbnail.classList.add('is-invalid');
                            this.showToast('Thumbnail must be a JPG, JPEG, or PNG file', 'warning');
                            isValid = false;
                        } else {
                            form.thumbnail.classList.remove('is-invalid');
                        }
                    }
                    break;

               case 2:
    // Validate full description minimum characters
    const fullDescriptionText = this.quill ? this.quill.getText().trim() : '';
    const fullDescriptionContainer = document.querySelector('#full_description .ql-container');
    
    if (fullDescriptionText.length < 60) {
        // Only apply border style if container exists
        if (fullDescriptionContainer) {
            fullDescriptionContainer.style.border = '1px solid #dc3545';
        }
        this.showToast('Full description must be at least 60 characters long', 'warning');
        isValid = false;
    } else {
        // Only apply border style if container exists
        if (fullDescriptionContainer) {
            fullDescriptionContainer.style.border = '1px solid #e7eaf3';
        }
    }

                case 3:
                    // Validate settings if needed
                    const visibility = form.visibility.value;
                    if (visibility === 'Password Protected' && !form.access_password.value.trim()) {
                        form.access_password.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        form.access_password.classList.remove('is-invalid');
                    }
                    break;
            }

            return isValid;
        }

        collectStepData(step) {
            const form = document.getElementById('courseInitiationForm');
            let data = {};

            // Convert step to number for consistent comparison
            const stepNum = typeof step === 'string' ? parseInt(step) : step;

            switch (stepNum) {
                case 1:
                    data.title = form.title.value.trim();
                    data.category_id = form.category_id.value;
                    data.subcategory_id = form.subcategory_id.value;
                    data.course_level = form.course_level.value;
                    data.estimated_duration = form.estimated_duration.value || null;
                    data.short_description = form.short_description.value.trim();

                    // Check if thumbnail file is selected
                    if (form.thumbnail.files && form.thumbnail.files.length > 0) {
                        data.has_thumbnail = true;
                    } else {
                        data.has_thumbnail = false;
                    }
                    break;

                case 2:
                    data.full_description = this.quill.root.innerHTML;
                    data.outcomes = Array.from(form.querySelectorAll('input[name="outcomes[]"]'))
                        .map(input => input.value.trim())
                        .filter(value => value);
                    data.requirements = Array.from(form.querySelectorAll('input[name="requirements[]"]'))
                        .map(input => input.value.trim())
                        .filter(value => value);
                    break;

                case 3:
                    data.price = form.price.value || '0.00';
                    data.access_level = form.access_level.value;
                    data.visibility = form.visibility.value;

                    // Send null instead of empty string for enrollment_limit
                    const enrollmentLimit = form.enrollment_limit.value.trim();
                    data.enrollment_limit = enrollmentLimit ? parseInt(enrollmentLimit) : null;

                    data.access_password = form.access_password.value.trim() || null;
                    data.certificate_enabled = form.certificate_enabled.checked ? 1 : 0;

                    // Include estimated_duration if it exists
                    if (form.estimated_duration && form.estimated_duration.value) {
                        data.estimated_duration = form.estimated_duration.value;
                    }
                    break;
            }

            return data;
        }

        async saveStep(step) {
            console.log('saveStep called with step:', step, 'courseId:', this.courseId);

            const formData = this.collectStepData(step);

            // Always send course_id if it exists
            let requestData = {
                ...formData
            };

            if (this.courseId) {
                requestData.course_id = this.courseId;
            }

            console.log('Request data:', requestData);

            try {
                this.showToast('Saving...', 'info');

                // For step 1 with thumbnail upload, use FormData instead of JSON
                if (step == 1 && document.getElementById('thumbnail').files.length > 0) {
                    const formDataObj = new FormData();

                    // Add all the regular fields
                    Object.keys(requestData).forEach(key => {
                        formDataObj.append(key, requestData[key]);
                    });

                    // Add course_id if it exists
                    if (this.courseId) {
                        formDataObj.append('course_id', this.courseId);
                    }

                    // Add the file
                    const thumbnailFile = document.getElementById('thumbnail').files[0];
                    formDataObj.append('thumbnail', thumbnailFile);

                    const response = await fetch(`../backend/courses/initiate_course.php?step=${step}`, {
                        method: 'POST',
                        body: formDataObj
                    });

                    // Process response as before
                    if (!response.ok) {
                        throw new Error(`Server error: ${response.status} ${response.statusText}`);
                    }

                    const responseText = await response.text();
                    console.log('Raw response:', responseText);

                    let result;
                    try {
                        result = JSON.parse(responseText);
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        console.error('Response text:', responseText);
                        throw new Error('Invalid JSON response from server. Please check server logs.');
                    }

                    console.log('Parsed response:', result);

                    if (result.success) {
                        if (step == 1) {
                            this.courseId = result.course_id;
                            console.log('Course ID set:', this.courseId);

                            // Store thumbnail URL if it was uploaded
                            if (result.thumbnail_url) {
                                this.thumbnailUrl = result.thumbnail_url;
                                console.log('Thumbnail URL set:', this.thumbnailUrl);
                            }
                        }
                        this.showToast('Progress saved successfully', 'success');
                    } else {
                        throw new Error(result.message || 'Failed to save progress');
                    }
                } else {
                    // For other steps or no file, use JSON as before
                    const response = await fetch(`../backend/courses/initiate_course.php?step=${step}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(requestData)
                    });

                    // Process response as before...
                    if (!response.ok) {
                        throw new Error(`Server error: ${response.status} ${response.statusText}`);
                    }

                    const responseText = await response.text();
                    console.log('Raw response:', responseText);

                    let result;
                    try {
                        result = JSON.parse(responseText);
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        console.error('Response text:', responseText);
                        throw new Error('Invalid JSON response from server. Please check server logs.');
                    }

                    console.log('Parsed response:', result);

                    if (result.success) {
                        if (step == 1) {
                            this.courseId = result.course_id;
                            console.log('Course ID set:', this.courseId);

                            // Store thumbnail URL if returned
                            if (result.thumbnail_url) {
                                this.thumbnailUrl = result.thumbnail_url;
                                console.log('Thumbnail URL set:', this.thumbnailUrl);
                            }
                        }
                        this.showToast('Progress saved successfully', 'success');
                    } else {
                        throw new Error(result.message || 'Failed to save progress');
                    }
                }
            } catch (error) {
                console.error('Error saving step:', error);
                this.showToast('Error saving progress: ' + error.message, 'danger');
                throw error;
            }
        }

        populateReview() {
            const form = document.getElementById('courseInitiationForm');

            // Basic Information
            document.getElementById('review_title').textContent = form.title.value;
            document.getElementById('review_level').textContent = form.course_level.value;

            // Get category and subcategory text
            let categoryText = '';
            try {
                const categoryName = form.category_id.selectedOptions[0]?.text || '';
                const subcategoryName = form.subcategory_id.selectedOptions[0]?.text || '';
                categoryText = categoryName + (subcategoryName ? ' > ' + subcategoryName : '');
            } catch (e) {
                console.error('Error getting category text:', e);
                categoryText = 'Error getting category details';
            }
            document.getElementById('review_category').textContent = categoryText;

            document.getElementById('review_duration').textContent = form.estimated_duration.value || '-';

            // Settings
            document.getElementById('review_price').textContent = '₵' + parseFloat(form.price.value || 0).toFixed(2);
            document.getElementById('review_access').textContent = form.access_level.value;
            document.getElementById('review_certificate').textContent = form.certificate_enabled.checked ? 'Yes' : 'No';

            // Description
            document.getElementById('review_description').textContent = form.short_description.value;

            // Outcomes
            const outcomes = Array.from(form.querySelectorAll('input[name="outcomes[]"]'))
                .map(input => input.value.trim())
                .filter(value => value);
            const outcomesHtml = outcomes.length ? outcomes.map(outcome => `<li>${outcome}</li>`).join('') : '<li>No outcomes specified</li>';
            document.getElementById('review_outcomes').innerHTML = outcomesHtml;

            // Requirements
            const requirements = Array.from(form.querySelectorAll('input[name="requirements[]"]'))
                .map(input => input.value.trim())
                .filter(value => value);
            const requirementsHtml = requirements.length ? requirements.map(req => `<li>${req}</li>`).join('') : '<li>No requirements specified</li>';
            document.getElementById('review_requirements').innerHTML = requirementsHtml;

            // Thumbnail preview
            const thumbnailPreview = document.getElementById('review_thumbnail');
            if (this.thumbnailUrl) {
                thumbnailPreview.innerHTML = `<img src="../${this.thumbnailUrl}" alt="Course thumbnail" class="img-thumbnail" style="max-height: 200px;">`;
            } else {
                thumbnailPreview.innerHTML = `<span class="text-muted">No thumbnail uploaded</span>`;
            }
        }

        async finalizeCourse() {
            if (!this.courseId) {
                this.showToast('Course ID missing. Please try again.', 'danger');
                return;
            }

            try {
                const actionText = this.isEditMode ? 'Updating' : 'Creating';
                this.showToast(`${actionText} course...`, 'info');
                const response = await fetch(`../backend/courses/initiate_course.php?action=finalize&course_id=${this.courseId}`, {
                    method: 'POST'
                });

                const result = await response.json();

                if (result.success) {
                    const successMessage = this.isEditMode ? 'Course updated successfully!' : 'Course created successfully!';
                    this.showToast(successMessage, 'success');
                    // Redirect after a delay
                    setTimeout(() => {
                        window.location.href = 'courses.php';
                    }, 1500);
                } else {
                    const errorAction = this.isEditMode ? 'update' : 'create';
                    throw new Error(result.message || `Failed to ${errorAction} course`);
                }
            } catch (error) {
                console.error('Error finalizing course:', error);
                const errorAction = this.isEditMode ? 'updating' : 'creating';
                this.showToast(`Error ${errorAction} course: ` + error.message, 'danger');
            }
        }

        async saveDraft() {
            if (!this.courseId) {
                // First time save - ensure step 1 is complete
                if (!this.validateStep(1)) {
                    this.showToast('Please complete basic information before saving', 'warning');
                    return;
                }
                try {
                    await this.saveStep(1);
                    this.showToast('Draft saved successfully', 'success');
                } catch (error) {
                    // Error already handled in saveStep
                }
            } else {
                // Save current step
                if (this.validateStep(this.currentStep)) {
                    try {
                        await this.saveStep(this.currentStep);
                        this.showToast('Draft saved successfully', 'success');
                    } catch (error) {
                        // Error already handled in saveStep
                    }
                }
            }
        }

        showToast(message, type = 'info') {
            const toastElement = document.getElementById('liveToast');
            const toastIcon = document.getElementById('toastIcon');
            const toastTitle = document.getElementById('toastTitle');
            const toastBody = document.getElementById('toastBody');
            const toastTime = document.getElementById('toastTime');

            // Update toast appearance based on type
            let iconClass = 'bi-info-circle';
            let iconBgClass = 'bg-info bg-opacity-10 text-info';
            let titleText = 'System Notification';

            switch (type) {
                case 'success':
                    iconClass = 'bi-check-lg';
                    iconBgClass = 'bg-success bg-opacity-10 text-success';
                    titleText = 'Success';
                    break;
                case 'danger':
                case 'error':
                    iconClass = 'bi-exclamation-triangle';
                    iconBgClass = 'bg-danger bg-opacity-10 text-danger';
                    titleText = 'Error';
                    break;
                case 'warning':
                    iconClass = 'bi-exclamation-circle';
                    iconBgClass = 'bg-warning bg-opacity-10 text-warning';
                    titleText = 'Warning';
                    break;
            }

            // Update toast content
            toastIcon.className = `flex-shrink-0 rounded-circle ${iconBgClass} p-2 d-flex align-items-center justify-content-center me-2`;
            toastIcon.innerHTML = `<i class="${iconClass} fs-6"></i>`;
            toastTitle.textContent = titleText;
            toastBody.textContent = message;
            toastTime.textContent = 'Just Now';

            // Show the toast
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
        }
    }

    // Initialize the form
    document.addEventListener('DOMContentLoaded', () => {
        window.courseInitiator = new CourseInitiator();
    });

    // Global functions for button handlers
    function nextStep() {
        window.courseInitiator.nextStep();
    }

    function prevStep() {
        window.courseInitiator.prevStep();
    }

    function saveDraft() {
        window.courseInitiator.saveDraft();
    }

    function addOutcome() {
        window.courseInitiator.addOutcome();
    }

    function addRequirement() {
        window.courseInitiator.addRequirement();
    }
</script>

<?php include '../includes/department/footer.php'; ?>