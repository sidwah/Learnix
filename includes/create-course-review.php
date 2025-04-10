<!-- Step 6: Review & Publish Tab -->
<div class="card">
    <div class="card-header bg-light">
        <h5 class="mb-0">Review Your Course</h5>
    </div>
    <div class="card-body">
        <div class="text-center mb-4">
            <p class="text-muted">Review your course content before publishing. Make sure everything is complete and accurate.</p>
        </div>

        <!-- Completeness Indicator -->
        <div class="card mb-4 border-primary">
            <div class="card-header bg-primary text-white">
                <div class="d-flex align-items-center">
                    <i class="mdi mdi-check-circle me-2 fs-5"></i>
                    <h5 class="mb-0">Course Completeness Checklist</h5>
                </div>
            </div>
            <div class="card-body">
                <div id="completenessChecklist">
                    <div class="d-flex align-items-center mb-3">
                        <div class="progress flex-grow-1 me-3" style="height: 8px;">
                            <div id="completenessProgress" class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="fw-bold" id="completenessPercentage">0%</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center" id="check-basics">
                                    <span><i class="mdi mdi-circle-outline me-2 text-muted"></i> Basic details</span>
                                    <a href="#" class="btn btn-sm btn-outline-primary edit-section" data-step="1">
                                        <i class="mdi mdi-pencil"></i> Edit
                                    </a>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center" id="check-structure">
                                    <span><i class="mdi mdi-circle-outline me-2 text-muted"></i> Course structure</span>
                                    <a href="#" class="btn btn-sm btn-outline-primary edit-section" data-step="2">
                                        <i class="mdi mdi-pencil"></i> Edit
                                    </a>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center" id="check-content">
                                    <span><i class="mdi mdi-circle-outline me-2 text-muted"></i> Content creation</span>
                                    <a href="#" class="btn btn-sm btn-outline-primary edit-section" data-step="3">
                                        <i class="mdi mdi-pencil"></i> Edit
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center" id="check-resources">
                                    <span><i class="mdi mdi-circle-outline me-2 text-muted"></i> Resources</span>
                                    <a href="#" class="btn btn-sm btn-outline-primary edit-section" data-step="4">
                                        <i class="mdi mdi-pencil"></i> Edit
                                    </a>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center" id="check-settings">
                                    <span><i class="mdi mdi-circle-outline me-2 text-muted"></i> Pricing & settings</span>
                                    <a href="#" class="btn btn-sm btn-outline-primary edit-section" data-step="5">
                                        <i class="mdi mdi-pencil"></i> Edit
                                    </a>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center" id="check-final">
                                    <span><i class="mdi mdi-circle-outline me-2 text-muted"></i> Ready to publish</span>
                                    <span class="badge bg-secondary" id="publishReadinessStatus">Checking...</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes & Approval Information -->
        <div class="alert alert-info d-flex" role="alert">
            <div class="me-3 fs-3">
                <i class="mdi mdi-information-outline"></i>
            </div>
            <div>
                <h5 class="alert-heading">Ready to Publish Your Course?</h5>
                <p>Your course is saved as a draft. Once you publish it, our team will review the content to ensure it meets our quality standards.</p>
                <p class="mb-0"><strong>Review times:</strong> Most courses are approved within 1-3 business days. You'll receive an email notification once the review is complete.</p>
            </div>
        </div>

        <!-- Review Content Container -->
        <div id="courseReviewContainer" class="mt-4">
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading course details...</span>
                </div>
                <p class="mt-2">Loading your course details...</p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card mt-4 mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-secondary" id="saveDraft">
                        <i class="mdi mdi-content-save me-1"></i> Save as Draft
                    </button>

                    <div>
                        <button type="button" class="btn btn-outline-primary me-2" id="previewCourse">
                            <i class="mdi mdi-eye me-1"></i> Preview Course
                        </button>
                        <button type="button" class="btn btn-primary" id="publishCourse">
                            <i class="mdi mdi-publish me-1"></i> Publish Course
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Review Section Templates -->
<template id="reviewSectionTemplate">
    <div class="card mb-4 review-section" id="review-${sectionId}">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="mdi ${sectionIcon} me-2"></i> ${sectionTitle}
            </h5>
            <a href="#" class="btn btn-sm btn-outline-primary edit-section" data-step="${stepNumber}">
                <i class="mdi mdi-pencil me-1"></i> Edit
            </a>
        </div>
        <div class="card-body">
            <div class="review-content">
                ${sectionContent}
            </div>
        </div>
    </div>
</template>

<!-- Enhanced Styles for Review Tab -->
<style>
    /* Review section styling */
    .review-section {
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }

    .review-section:hover {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .review-section#review-basics {
        border-left-color: #3498db;
    }

    .review-section#review-structure {
        border-left-color: #f39c12;
    }

    .review-section#review-content {
        border-left-color: #2ecc71;
    }

    .review-section#review-resources {
        border-left-color: #9b59b6;
    }

    .review-section#review-settings {
        border-left-color: #e74c3c;
    }

    /* Completeness checklist styling */
    .checklist-item-complete .mdi-circle-outline {
        color: #28a745 !important;
    }

    .checklist-item-complete .mdi-circle-outline::before {
        content: "\F0765";
        /* mdi-check-circle */
    }

    .checklist-item-error .mdi-circle-outline {
        color: #dc3545 !important;
    }

    .checklist-item-error .mdi-circle-outline::before {
        content: "\F05AB";
        /* mdi-alert-circle */
    }

    /* Accordion styling for course structure */
    .review-accordion .accordion-button {
        padding: 0.75rem 1.25rem;
    }

    .review-accordion .accordion-button:not(.collapsed) {
        color: #0c63e4;
        background-color: rgba(13, 110, 253, 0.1);
    }

    .review-accordion .accordion-body {
        padding: 1rem 1.25rem;
        background-color: #f8f9fa;
    }

    /* Badge styling */
    .topic-badge {
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.35em 0.65em;
    }

    /* Empty state styling */
    .empty-state {
        padding: 2rem;
        text-align: center;
        background-color: #f8f9fa;
        border-radius: 0.25rem;
        border: 1px dashed #dee2e6;
    }

    .empty-state i {
        font-size: 2.5rem;
        color: #adb5bd;
        margin-bottom: 1rem;
    }

    /* Preview modal styling */
    .preview-modal-dialog {
        max-width: 95%;
        height: 90vh;
        margin: 1rem auto;
    }

    .preview-modal-content {
        height: 100%;
    }

    .preview-iframe {
        width: 100%;
        height: 100%;
        border: none;
    }

    /* Publish confirmation modal */
    .publish-confirm-modal .modal-body {
        padding: 2rem;
    }

    /* Loading animation */
    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .loading:after {
        content: '';
        display: block;
        width: 1.2em;
        height: 1.2em;
        position: absolute;
        left: calc(50% - 0.6em);
        top: calc(50% - 0.6em);
        border: 0.15em solid currentColor;
        border-right-color: transparent;
        border-radius: 50%;
        animation: spin 0.75s linear infinite;
    }

    .btn.loading {
        color: transparent !important;
        pointer-events: none;
        position: relative;
    }
</style>

<script>
    /**
     * Enhanced Course Review System
     * 
     * This script completely rebuilds the review functionality with:
     * - Reliable course data loading
     * - Visual completeness checking with status indicators
     * - Better organized review sections with detailed information
     * - Responsive preview functionality that works across browsers
     * - Robust navigation and action buttons
     */

    // Main function to load course review and check completeness
    function loadCourseReviewAndCheckCompleteness() {
        // Get course ID
        const courseId = document.getElementById('course_id')?.value;
        if (!courseId) {
            handleMissingCourseId();
            return;
        }

        // Publish course
        function publishCourse() {
            // Show loading overlay
            if (typeof createOverlay === 'function') {
                createOverlay("Publishing your course...");
            }

            // Disable publish button
            const publishButton = document.getElementById('publishCourse');
            if (publishButton) {
                publishButton.disabled = true;
                if (publishButton.classList) {
                    publishButton.classList.add('loading');
                } else {
                    publishButton.innerHTML = '<i class="mdi mdi-spin mdi-loading me-1"></i> Publishing...';
                }
            }

            // Get course ID
            const courseId = document.getElementById('course_id')?.value;
            if (!courseId) {
                if (typeof removeOverlay === 'function') {
                    removeOverlay();
                }

                if (typeof showAlert === 'function') {
                    showAlert('danger', 'Course ID not found. Please save basic details first.');
                }

                if (publishButton) {
                    publishButton.disabled = false;
                    if (publishButton.classList) {
                        publishButton.classList.remove('loading');
                    } else {
                        publishButton.innerHTML = '<i class="mdi mdi-publish me-1"></i> Publish Course';
                    }
                }
                return;
            }

            // Send publish request
            fetch('../backend/courses/publish_course.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `course_id=${courseId}`
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Show success message
                        if (typeof showAlert === 'function') {
                            showAlert('success', 'Course published successfully! It will now go through a review process.');
                        }

                        // Display success state with confetti if available
                        showPublishSuccess();

                        // Redirect to instructor dashboard after a delay
                        setTimeout(() => {
                            window.location.href = '../instructor/courses.php';
                        }, 3000);
                    } else {
                        // Show error
                        if (typeof showAlert === 'function') {
                            showAlert('danger', data.message || 'Error publishing course. Please try again.');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);

                    // Show error
                    if (typeof showAlert === 'function') {
                        showAlert('danger', 'Error publishing course: ' + error.message);
                    }
                })
                .finally(() => {
                    // Remove overlay
                    if (typeof removeOverlay === 'function') {
                        removeOverlay();
                    }

                    // Re-enable publish button
                    if (publishButton) {
                        publishButton.disabled = false;
                        if (publishButton.classList) {
                            publishButton.classList.remove('loading');
                        } else {
                            publishButton.innerHTML = '<i class="mdi mdi-publish me-1"></i> Publish Course';
                        }
                    }
                });
        }

        // Show publish success state with confetti if available
        function showPublishSuccess() {
            // Create success message overlay
            const successOverlay = document.createElement('div');
            successOverlay.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center';
            successOverlay.style.backgroundColor = 'rgba(255, 255, 255, 0.9)';
            successOverlay.style.zIndex = '9999';

            successOverlay.innerHTML = `
<div class="text-center">
    <div class="mb-4">
        <i class="mdi mdi-check-circle text-success" style="font-size: 5rem;"></i>
    </div>
    <h2 class="mb-3">Course Published Successfully!</h2>
    <p class="lead">Your course has been submitted for review.</p>
    <p>You will be redirected to your instructor dashboard in a moment...</p>
</div>
`;

            document.body.appendChild(successOverlay);

            // Try to show confetti effect if confetti library is available
            if (typeof confetti === 'function') {
                confetti({
                    particleCount: 100,
                    spread: 70,
                    origin: {
                        y: 0.6
                    }
                });
            }
        }

        // Save course as draft
        function saveDraft() {
            // Show confirmation
            if (confirm('Save your progress and return to the instructor dashboard?')) {
                // Show brief saving message
                if (typeof showAlert === 'function') {
                    showAlert('success', 'Course saved as draft!');
                }

                // Redirect to instructor dashboard after a short delay
                setTimeout(() => {
                    window.location.href = '../instructor/courses.php';
                }, 1000);
            }
        }

        // Call this function to initialize the review page when the page loads
document.addEventListener('DOMContentLoaded', function() {
    // Set up event listeners for the review tab
    document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(event) {
            if (event.target.getAttribute('href') === '#step-6-review') {
                loadCourseReviewAndCheckCompleteness();
            }
        });
    });
    
    // Also check if we're already on the review tab
    if (document.querySelector('.nav-link.active[href="#step-6-review"]')) {
        loadCourseReviewAndCheckCompleteness();
    }
    
    // Set up action buttons
    setupActionButtons();
    setupEditSectionLinks();
});

        // Setup edit section links to jump to specific tabs
        function setupEditSectionLinks() {
            document.querySelectorAll('.edit-section').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    const step = parseInt(this.getAttribute('data-step'), 10);
                    if (isNaN(step)) return;

                    // Navigate to the specified step
                    if (typeof navigateToStep === 'function') {
                        navigateToStep(step);
                    } else {
                        // Fallback for old navigation system
                        const tabLinks = document.querySelectorAll('.nav-pills .nav-link');
                        if (tabLinks[step - 1]) {
                            const tab = new bootstrap.Tab(tabLinks[step - 1]);
                            tab.show();
                        }
                    }
                });
            });
        }

        // Setup action buttons (publish, draft, preview)
        function setupActionButtons() {
            // Publish button
            const publishButton = document.getElementById('publishCourse');
            if (publishButton) {
                publishButton.addEventListener('click', confirmPublishCourse);
            }

            // Save as draft button
            const saveDraftButton = document.getElementById('saveDraft');
            if (saveDraftButton) {
                saveDraftButton.addEventListener('click', saveDraft);
            }

            // Preview button
            const previewButton = document.getElementById('previewCourse');
            if (previewButton) {
                previewButton.addEventListener('click', previewCourse);
            }
        }

        // Show loading state
        if (typeof createOverlay === 'function') {
            createOverlay("Loading course details...");
        }

        // Load course details and check completeness
        loadCourseReview(courseId);
    }

    // Handle case when course ID is missing
    function handleMissingCourseId() {
        const reviewContainer = document.getElementById('courseReviewContainer');
        if (reviewContainer) {
            reviewContainer.innerHTML = `
<div class="alert alert-warning d-flex align-items-center" role="alert">
    <i class="mdi mdi-alert-circle me-3 fs-3"></i>
    <div>
        <h5 class="alert-heading">Course ID Not Found</h5>
        <p class="mb-0">Please complete the Basic Details step first to create your course.</p>
    </div>
</div>
`;
        }

        // Update completeness checklist
        updateCompletenessChecklist({
            basics: false,
            structure: false,
            content: false,
            resources: false,
            settings: false
        });

        if (typeof removeOverlay === 'function') {
            removeOverlay();
        }
    }

    // Load course details for review
    function loadCourseReview(courseId) {
        const reviewContainer = document.getElementById('courseReviewContainer');
        if (!reviewContainer) {
            if (typeof removeOverlay === 'function') {
                removeOverlay();
            }
            return;
        }

        // Fetch course details with a robust approach (with error handling)
        fetchCourseDetailsWithRetry(courseId)
            .then(data => {
                if (data.success) {
                    // Clear loading indicator
                    reviewContainer.innerHTML = '';

                    // Render course review sections
                    renderEnhancedReview(data.course, reviewContainer);

                    // Check course completeness
                    checkCourseCompleteness(data.course);

                    // Show success message
                    if (typeof showAlert === 'function') {
                        showAlert('success', 'Course details loaded successfully!');
                    }
                } else {
                    // Show error message
                    reviewContainer.innerHTML = `
<div class="alert alert-danger d-flex align-items-center" role="alert">
    <i class="mdi mdi-alert-circle me-3 fs-3"></i>
    <div>
        <h5 class="alert-heading">Error Loading Course</h5>
        <p class="mb-0">${data.message || 'Could not load course details. Please try again.'}</p>
    </div>
</div>
`;

                    // Update completeness checklist to show errors
                    updateCompletenessChecklist({
                        basics: false,
                        structure: false,
                        content: false,
                        resources: false,
                        settings: false,
                        error: true
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching course details:', error);

                // Show error message
                reviewContainer.innerHTML = `
<div class="alert alert-danger d-flex align-items-center" role="alert">
    <i class="mdi mdi-alert-circle me-3 fs-3"></i>
    <div>
        <h5 class="alert-heading">Error Loading Course</h5>
        <p class="mb-0">There was a problem loading your course: ${error.message}</p>
    </div>
</div>
`;

                // Update completeness checklist to show errors
                updateCompletenessChecklist({
                    basics: false,
                    structure: false,
                    content: false,
                    resources: false,
                    settings: false,
                    error: true
                });
            })
            .finally(() => {
                // Remove loading overlay
                if (typeof removeOverlay === 'function') {
                    removeOverlay();
                }
            });
    }

    // Fetch course details with retry logic
function fetchCourseDetailsWithRetry(courseId, retries = 2) {
    return fetch(`../backend/courses/get_course_details.php?course_id=${courseId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.text();
        })
        .then(text => {
            try {
                const data = JSON.parse(text);
                
                // Check if there's an error property in the response
                if (data.error) {
                    return {
                        success: false,
                        message: data.error,
                        course: null
                    };
                }
                
                // Otherwise, structure the data for our frontend
                return {
                    success: true,
                    course: data
                };
            } catch (e) {
                console.error('Error parsing JSON response:', e);
                console.log('Response text:', text);
                throw new Error('Invalid response format from server');
            }
        })
        .catch(error => {
            if (retries > 0) {
                console.log(`Retrying fetch, ${retries} attempts left`);
                return new Promise(resolve => setTimeout(resolve, 1000))
                    .then(() => fetchCourseDetailsWithRetry(courseId, retries - 1));
            }
            throw error;
        });
}

    // Render enhanced review with better organization and visual hierarchy
    function renderEnhancedReview(course, container) {
        // Basic Information
        createEnhancedReviewSection({
            sectionId: 'basics',
            stepNumber: 1,
            sectionTitle: 'Basic Information',
            sectionIcon: 'mdi-information-outline',
            sectionContent: generateBasicInfoHTML(course),
            container: container
        });

        // Course Structure
        createEnhancedReviewSection({
            sectionId: 'structure',
            stepNumber: 2,
            sectionTitle: 'Course Structure',
            sectionIcon: 'mdi-format-list-bulleted',
            sectionContent: generateContentStructureHTML(course),
            container: container
        });

        // Course Content
        createEnhancedReviewSection({
            sectionId: 'content',
            stepNumber: 3,
            sectionTitle: 'Course Content',
            sectionIcon: 'mdi-file-document-outline',
            sectionContent: generateContentDetailsHTML(course),
            container: container
        });

        // Resources
        createEnhancedReviewSection({
            sectionId: 'resources',
            stepNumber: 4,
            sectionTitle: 'Course Resources',
            sectionIcon: 'mdi-folder-outline',
            sectionContent: generateResourcesHTML(course),
            container: container
        });

        // Pricing & Settings
        createEnhancedReviewSection({
            sectionId: 'settings',
            stepNumber: 5,
            sectionTitle: 'Pricing & Settings',
            sectionIcon: 'mdi-cog-outline',
            sectionContent: generateSettingsHTML(course),
            container: container
        });

        // Initialize any Bootstrap components
        initBootstrapComponents();
    }

    // Create an enhanced review section using template
    function createEnhancedReviewSection(options) {
        const template = document.getElementById('reviewSectionTemplate');
        if (!template || !options.container) return;

        const html = template.innerHTML
            .replace(/\${sectionId}/g, options.sectionId)
            .replace(/\${stepNumber}/g, options.stepNumber)
            .replace(/\${sectionTitle}/g, options.sectionTitle)
            .replace(/\${sectionIcon}/g, options.sectionIcon)
            .replace(/\${sectionContent}/g, options.sectionContent);

        const wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        options.container.appendChild(wrapper.firstElementChild);
    }

    // Generate HTML for basic info section
    function generateBasicInfoHTML(course) {
        if (!course) {
            return generateEmptyState('No basic details found', 'Please complete the Basic Details step.');
        }

        return `
<div class="row mb-4">
    <div class="col-md-4">
        <div class="position-relative">
            <img src="../uploads/thumbnails/${course.thumbnail || 'default.jpg'}" 
                class="img-fluid rounded shadow-sm" alt="Course Thumbnail">
            <div class="position-absolute bottom-0 end-0 m-2">
                <span class="badge ${course.status === 'Published' ? 'bg-success' : 'bg-warning'} px-3 py-2">
                    ${course.status || 'Draft'}
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <h4 class="mb-2">${course.title || 'Untitled Course'}</h4>
        <p class="text-muted">${course.short_description || 'No description provided'}</p>

        <div class="row mt-3">
            <div class="col-md-6">
                <div class="mb-2">
                    <strong><i class="mdi mdi-tag me-1"></i> Category:</strong> 
                    <span class="badge bg-light text-dark">${course.category_name || 'Uncategorized'}</span>
                    <span class="badge bg-light text-dark">${course.subcategory_name || ''}</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-2">
                    <strong><i class="mdi mdi-account me-1"></i> Instructor:</strong> 
                    <span>${course.instructor_name || 'You'}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<h5 class="border-bottom pb-2 mb-3"><i class="mdi mdi-text-box-outline me-1"></i> Full Description</h5>
<div class="p-3 bg-light rounded mb-3">
    ${course.full_description || '<em class="text-muted">No full description provided</em>'}
</div>

<h5 class="border-bottom pb-2 mb-3"><i class="mdi mdi-bullseye-arrow me-1"></i> What You'll Learn</h5>
${generateLearningOutcomesHTML(course.learning_outcomes)}
`;
    }

    // Generate HTML for learning outcomes
    function generateLearningOutcomesHTML(outcomes) {
        if (!outcomes || outcomes.length === 0) {
            return `<div class="text-muted fst-italic">No learning outcomes specified</div>`;
        }

        return `
<div class="row">
    ${outcomes.map(outcome => `
    <div class="col-md-6 mb-2">
        <div class="d-flex">
            <div class="me-2 text-success">
                <i class="mdi mdi-check-circle"></i>
            </div>
            <div>
                ${outcome.outcome_text || outcome}
            </div>
        </div>
    </div>
    `).join('')}
</div>
`;
    }

    // Generate HTML for course structure section
    function generateContentStructureHTML(course) {
        if (!course.sections || course.sections.length === 0) {
            return generateEmptyState('No course structure defined', 'Please complete the Course Structure step.');
        }

        return `
<p class="mb-3">Your course contains <strong>${course.sections.length}</strong> sections organized as follows:</p>

<div class="accordion review-accordion" id="structureAccordion">
    ${course.sections.map((section, index) => `
    <div class="accordion-item">
        <h2 class="accordion-header" id="sectionHeading${section.section_id}">
            <button class="accordion-button collapsed" type="button" 
                data-bs-toggle="collapse" data-bs-target="#sectionCollapse${section.section_id}" 
                aria-expanded="false" aria-controls="sectionCollapse${section.section_id}">
                <span class="fw-bold">Section ${index + 1}:</span>&nbsp;${section.title}
                ${generateSectionBadges(section)}
            </button>
        </h2>
        <div id="sectionCollapse${section.section_id}" class="accordion-collapse collapse" 
            aria-labelledby="sectionHeading${section.section_id}" data-bs-parent="#structureAccordion">
            <div class="accordion-body">
                <div class="d-flex justify-content-end mb-2">
                    <a href="#" class="btn btn-sm btn-outline-primary edit-section" data-step="3">
                        <i class="mdi mdi-pencil me-1"></i> Edit Content
                    </a>
                </div>

                ${generateSectionSummary(section)}
            </div>
        </div>
    </div>
    `).join('')}
</div>
`;
    }

    // Generate badges for section (number of topics, quizzes)
    function generateSectionBadges(section) {
        const topicCount = section.topics?.length || 0;
        const quizCount = section.quizzes?.length || 0;

        let badgeHtml = '';

        if (topicCount > 0) {
            badgeHtml += `
<span class="ms-2 badge bg-info topic-badge">
    <i class="mdi mdi-book-open-variant me-1"></i>${topicCount} Topic${topicCount !== 1 ? 's' : ''}
</span>
`;
        }

        if (quizCount > 0) {
            badgeHtml += `
<span class="ms-2 badge bg-danger topic-badge">
    <i class="mdi mdi-help-circle me-1"></i>${quizCount} Quiz${quizCount !== 1 ? 'zes' : ''}
</span>
`;
        }

        if (topicCount === 0 && quizCount === 0) {
            badgeHtml += `
<span class="ms-2 badge bg-warning topic-badge">
    <i class="mdi mdi-alert me-1"></i>No Content
</span>
`;
        }

        return badgeHtml;
    }

    // Generate a summary of section content
    function generateSectionSummary(section) {
        let html = '';

        // Topics
        if (section.topics && section.topics.length > 0) {
            html += `
<h6 class="mb-2"><i class="mdi mdi-book-open-page-variant me-1"></i> Topics</h6>
<ul class="list-group mb-3">
    ${section.topics.map((topic, index) => `
    <li class="list-group-item d-flex justify-content-between align-items-center">
        <div>
            <span class="me-2">${index + 1}.</span>
            <span>${topic.title}</span>
        </div>
        <span class="badge ${getContentTypeBadgeClass(topic.content_type)}">
            ${getContentTypeDisplay(topic.content_type)}
        </span>
    </li>
    `).join('')}
</ul>
`;
        }

        // Quizzes
        if (section.quizzes && section.quizzes.length > 0) {
            html += `
<h6 class="mb-2"><i class="mdi mdi-help-box me-1"></i> Quizzes</h6>
<ul class="list-group">
    ${section.quizzes.map((quiz, index) => `
    <li class="list-group-item">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="me-2">${index + 1}.</span>
                <span>${quiz.quiz_title || quiz.title}</span>
            </div>
            <span class="badge bg-danger">Quiz</span>
        </div>
        <div class="small text-muted mt-1">
            <span class="me-3">Pass Mark: ${quiz.pass_mark}%</span>
            <span>Randomize: ${quiz.randomize_questions === '1' ? 'Yes' : 'No'}</span>
        </div>
    </li>
    `).join('')}
</ul>
`;
        }

        // No content
        if ((!section.topics || section.topics.length === 0) &&
            (!section.quizzes || section.quizzes.length === 0)) {
            html += `
<div class="alert alert-warning" role="alert">
    <i class="mdi mdi-alert me-2"></i>
    No content has been added to this section yet. Please add topics or quizzes.
</div>
`;
        }

        return html;
    }

    // Generate HTML for content details section
    function generateContentDetailsHTML(course) {
        // Count total topics and quizzes
        let totalTopics = 0;
        let totalQuizzes = 0;

        if (course.sections) {
            course.sections.forEach(section => {
                if (section.topics) totalTopics += section.topics.length;
                if (section.quizzes) totalQuizzes += section.quizzes.length;
            });
        }

        if (totalTopics === 0 && totalQuizzes === 0) {
            return generateEmptyState('No course content created', 'Please complete the Content Creation step.');
        }

        return `
<div class="mb-4">
    <div class="row text-center">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="display-4 fw-bold text-primary">${totalTopics}</h2>
                    <p class="mb-0">Total Topics</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="display-4 fw-bold text-danger">${totalQuizzes}</h2>
                    <p class="mb-0">Total Quizzes</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-success" role="alert">
    <div class="d-flex">
        <div class="me-3">
            <i class="mdi mdi-check-circle-outline fs-3"></i>
        </div>
        <div>
            <h5 class="alert-heading">Content Summary</h5>
            <p>Your course has ${totalTopics} learning topics across ${course.sections?.length || 0} sections, 
            with ${totalQuizzes} quizzes to help students test their knowledge.</p>
            <hr>
            <p class="mb-0">Want to add more content? <a href="#" class="alert-link edit-section" data-step="3">Click here</a> to go back to the Content Creation step.</p>
        </div>
    </div>
</div>
`;
    }

    // Generate HTML for resources section
    function generateResourcesHTML(course) {
        // Collect all resources
        let allResources = [];
        let videoCount = 0;
        let documentCount = 0;
        let linkCount = 0;

        if (course.sections) {
            course.sections.forEach(section => {
                if (section.topics) {
                    section.topics.forEach(topic => {
                        // Count content types
                        if (topic.content_type === 'video') videoCount++;
                        if (topic.content_type === 'text') documentCount++;
                        if (topic.content_type === 'link') linkCount++;

                        // Collect resources
                        if (topic.resources && topic.resources.length > 0) {
                            allResources = [...allResources, ...topic.resources.map(resource => ({
                                ...resource,
                                topic_title: topic.title,
                                section_title: section.title
                            }))];
                        }
                    });
                }
            });
        }

        if (allResources.length === 0 && videoCount === 0 && documentCount === 0 && linkCount === 0) {
            return generateEmptyState('No resources added', 'Please complete the Resource Upload step.');
        }

        // Resource type summary
        const resourceSummary = `
<div class="row text-center mb-4">
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-body">
                <i class="mdi mdi-video fs-1 text-danger"></i>
                <h3>${videoCount}</h3>
                <p class="mb-0">Video Lectures</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-body">
                <i class="mdi mdi-file-document fs-1 text-primary"></i>
                <h3>${documentCount}</h3>
                <p class="mb-0">Text Lessons</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-body">
                <i class="mdi mdi-link fs-1 text-success"></i>
                <h3>${linkCount}</h3>
                <p class="mb-0">External Links</p>
            </div>
        </div>
    </div>
</div>
`;

        // Resource list
        let resourceList = '';
        if (allResources.length === 0) {
            resourceList = `
<div class="alert alert-info">
    <i class="mdi mdi-information-outline me-2"></i>
    No supplementary resources have been uploaded yet.
</div>
`;
        } else {
            resourceList = `
<h5 class="border-bottom pb-2 mb-3">Supplementary Resources</h5>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Resource Name</th>
                <th>Section</th>
                <th>Topic</th>
                <th>Type</th>
            </tr>
        </thead>
        <tbody>
            ${allResources.map((resource, index) => `
            <tr>
                <td>${index + 1}</td>
                <td>${getResourceName(resource.resource_path)}</td>
                <td>${resource.section_title || 'Unknown'}</td>
                <td>${resource.topic_title || 'Unknown'}</td>
                <td><span class="badge ${getResourceTypeBadgeClass(resource.resource_path)}">${getFileTypeFromPath(resource.resource_path)}</span></td>
            </tr>
            `).join('')}
        </tbody>
    </table>
</div>
`;
        }

        return resourceSummary + resourceList;
    }

    // Get resource name from path
    function getResourceName(path) {
        if (!path) return 'Unknown';

        // Extract filename from path
        const filename = path.split('/').pop();

        // Remove any timestamp or identifier in filename
        return filename.replace(/^resource_topic_\d+_\d+_/, '');
    }

    // Generate HTML for settings section
    function generateSettingsHTML(course) {
        if (!course) {
            return generateEmptyState('No pricing or settings defined', 'Please complete the Pricing & Settings step.');
        }

        return `
<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">₵ Pricing Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <strong>Price:</strong>
                    <div>
                        ${parseFloat(course.price) > 0 ? 
                        `<span class="badge bg-primary px-3 py-2">₵${parseFloat(course.price).toFixed(2)}</span>` : 
                        '<span class="badge bg-success px-3 py-2">Free</span>'}
                    </div>
                </div>
                <div class="mb-0 d-flex justify-content-between align-items-center">
                    <strong>Access Level:</strong>
                    <span class="badge bg-secondary">${course.access_level || 'Public'}</span>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="mdi mdi-tag-multiple me-1"></i> Course Tags</h6>
            </div>
            <div class="card-body">
                ${course.tags && course.tags.length > 0 ? 
                `<div class="d-flex flex-wrap gap-2">
                    ${Array.isArray(course.tags) ? course.tags.map(tag => {
                        const tagName = typeof tag === 'object' ? (tag.tag_name || '') : tag;
                        return `<span class="badge bg-light text-dark border">${tagName}</span>`;
                    }).join('') : ''}
                </div>` : 
                '<span class="text-muted">No tags added</span>'}
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="mdi mdi-cog me-1"></i> Course Options</h6>
            </div>
            <div class="card-body">
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <strong>Course Level:</strong>
                    <span class="badge ${getCourseLevelBadgeClass(course.course_level)}">${formatCourseLevel(course.course_level)}</span>
                </div>
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <strong>Certificate Enabled:</strong>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" disabled ${course.certificate_enabled === '1' || course.certificate_enabled === true || course.certificate_enabled === 1 ? 'checked' : ''}>
                    </div>
                </div>
                <div class="mb-0 d-flex justify-content-between align-items-center">
                    <strong>Status:</strong>
                    <span class="badge ${course.status === 'Published' ? 'bg-success' : 'bg-warning'}">${course.status || 'Draft'}</span>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="mdi mdi-notebook me-1"></i> Course Requirements</h6>
            </div>
            <div class="card-body">
                ${generateRequirementsHTML(course.requirements)}
            </div>
        </div>
    </div>
</div>
`;
    }

    // Generate HTML for course requirements
    function generateRequirementsHTML(requirements) {
        if (!requirements || requirements.length === 0) {
            return `<span class="text-muted">No specific requirements added</span>`;
        }

        // Handle different formats of requirements
        let reqArray = [];

        if (Array.isArray(requirements)) {
            reqArray = requirements.map(req => typeof req === 'object' ? req.requirement_text : req);
        } else if (typeof requirements === 'string') {
            reqArray = requirements.split('\n').filter(r => r.trim());
        }

        if (reqArray.length === 0) {
            return `<span class="text-muted">No specific requirements added</span>`;
        }

        return `
<ul class="list-group list-group-flush">
    ${reqArray.map(req => `
    <li class="list-group-item px-0 border-0">
        <i class="mdi mdi-check-circle text-success me-2"></i>${req}
    </li>
    `).join('')}
</ul>
`;
    }

    // Generate empty state placeholder
    function generateEmptyState(title, message) {
        return `
<div class="empty-state">
    <i class="mdi mdi-information-outline"></i>
    <h5>${title}</h5>
    <p class="text-muted">${message}</p>
</div>
`;
    }

    // Format course level for display
    function formatCourseLevel(level) {
        if (!level) return 'Beginner';

        switch (level.toLowerCase()) {
            case 'beginner':
                return 'Beginner';
            case 'intermediate':
                return 'Intermediate';
            case 'advanced':
                return 'Advanced';
            case 'all-levels':
            case 'all levels':
            case 'all':
                return 'All Levels';
            default:
                return level;
        }
    }

    // Helper function to get badge class for content type
    function getContentTypeBadgeClass(contentType) {
        switch (contentType) {
            case 'text':
                return 'bg-info';
            case 'video':
                return 'bg-danger';
            case 'link':
                return 'bg-primary';
            case 'document':
                return 'bg-secondary';
            default:
                return 'bg-secondary';
        }
    }

    // Helper function to get display name for content type
    function getContentTypeDisplay(contentType) {
        switch (contentType) {
            case 'text':
                return 'Text';
            case 'video':
                return 'Video';
            case 'link':
                return 'Link';
            case 'document':
                return 'Document';
            default:
                return 'Unknown';
        }
    }

    // Helper function to get badge class for course level
    function getCourseLevelBadgeClass(level) {
        if (!level) return 'bg-success';

        switch (level.toLowerCase()) {
            case 'beginner':
                return 'bg-success';
            case 'intermediate':
                return 'bg-info';
            case 'advanced':
                return 'bg-danger';
            case 'all-levels':
            case 'all levels':
            case 'all':
                return 'bg-primary';
            default:
                return 'bg-secondary';
        }
    }

    // Helper function to get badge class for resource type
    function getResourceTypeBadgeClass(path) {
        if (!path) return 'bg-secondary';

        const extension = path.split('.').pop().toLowerCase();

        const fileTypeClasses = {
            'pdf': 'bg-danger',
            'doc': 'bg-primary',
            'docx': 'bg-primary',
            'xls': 'bg-success',
            'xlsx': 'bg-success',
            'ppt': 'bg-warning',
            'pptx': 'bg-warning',
            'zip': 'bg-secondary',
            'mp3': 'bg-info',
            'mp4': 'bg-danger',
            'webm': 'bg-danger',
            'ogg': 'bg-info',
            'jpg': 'bg-purple',
            'jpeg': 'bg-purple',
            'png': 'bg-purple',
            'gif': 'bg-purple'
        };

        return fileTypeClasses[extension] || 'bg-secondary';
    }

    // Helper function to get file type from path
    function getFileTypeFromPath(path) {
        if (!path) return 'Unknown';

        const extension = path.split('.').pop().toLowerCase();

        const fileTypes = {
            'pdf': 'PDF Document',
            'doc': 'Word Document',
            'docx': 'Word Document',
            'xls': 'Excel Spreadsheet',
            'xlsx': 'Excel Spreadsheet',
            'ppt': 'PowerPoint',
            'pptx': 'PowerPoint',
            'zip': 'ZIP Archive',
            'mp3': 'Audio File',
            'mp4': 'Video File',
            'webm': 'Video File',
            'ogg': 'Audio/Video File',
            'jpg': 'Image',
            'jpeg': 'Image',
            'png': 'Image',
            'gif': 'Image'
        };

        return fileTypes[extension] || extension.toUpperCase();
    }

    // Check course completeness and update checklist
    function checkCourseCompleteness(course) {
        // Check each section for completeness
        const completeness = {
            basics: checkBasicDetailsCompleteness(course),
            structure: checkStructureCompleteness(course),
            content: checkContentCompleteness(course),
            resources: checkResourcesCompleteness(course),
            settings: checkSettingsCompleteness(course)
        };

        // Check if course is ready to publish
        completeness.readyToPublish =
            completeness.basics &&
            completeness.structure &&
            completeness.content;

        // Update the UI
        updateCompletenessChecklist(completeness);

        // Update publish button state
        updatePublishButtonState(completeness.readyToPublish);

        return completeness;
    }

    // Check if basic details are complete
    function checkBasicDetailsCompleteness(course) {
        if (!course) return false;

        return !!(
            course.title &&
            course.short_description &&
            course.full_description &&
            course.thumbnail &&
            course.subcategory_id &&
            course.learning_outcomes?.length > 0
        );
    }

    // Check if structure is complete
    function checkStructureCompleteness(course) {
        if (!course || !course.sections) return false;

        return course.sections.length > 0 &&
            course.sections.every(section => section.title?.trim());
    }

    // Check if content is complete
    function checkContentCompleteness(course) {
        if (!course || !course.sections) return false;

        // Check if at least one section has content
        let hasContent = false;

        for (const section of course.sections) {
            if ((section.topics && section.topics.length > 0) ||
                (section.quizzes && section.quizzes.length > 0)) {
                hasContent = true;
                break;
            }
        }

        return hasContent;
    }

    // Check if resources are complete
    function checkResourcesCompleteness(course) {
        // Resources are optional, but check if there's at least one video/text content
        if (!course || !course.sections) return false;

        for (const section of course.sections) {
            if (section.topics) {
                for (const topic of section.topics) {
                    if (topic.content_type === 'video' ||
                        topic.content_type === 'text' ||
                        (topic.resources && topic.resources.length > 0)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    // Check if settings are complete
    function checkSettingsCompleteness(course) {
        // Settings are optional but should have the basics
        if (!course) return false;

        return !!(course.course_level &&
            (course.price !== undefined && course.price !== null));
    }

    // Update completeness checklist
    function updateCompletenessChecklist(completeness) {
        // Update each checklist item
        updateChecklistItem('basics', completeness.basics);
        updateChecklistItem('structure', completeness.structure);
        updateChecklistItem('content', completeness.content);
        updateChecklistItem('resources', completeness.resources);
        updateChecklistItem('settings', completeness.settings);

        // Update final readiness
        const finalCheckItem = document.getElementById('check-final');
        const statusBadge = document.getElementById('publishReadinessStatus');

        if (finalCheckItem && statusBadge) {
            const icon = finalCheckItem.querySelector('i');

            if (completeness.error) {
                // Error state
                finalCheckItem.classList.remove('checklist-item-complete');
                finalCheckItem.classList.add('checklist-item-error');
                if (icon) {
                    icon.className = 'mdi mdi-alert-circle me-2 text-danger';
                }
                statusBadge.className = 'badge bg-danger';
                statusBadge.textContent = 'Error';
            } else if (completeness.readyToPublish) {
                // Ready state
                finalCheckItem.classList.add('checklist-item-complete');
                finalCheckItem.classList.remove('checklist-item-error');
                if (icon) {
                    icon.className = 'mdi mdi-check-circle me-2 text-success';
                }
                statusBadge.className = 'badge bg-success';
                statusBadge.textContent = 'Ready';
            } else {
                // Not ready state
                finalCheckItem.classList.remove('checklist-item-complete');
                finalCheckItem.classList.remove('checklist-item-error');
                if (icon) {
                    icon.className = 'mdi mdi-circle-outline me-2 text-muted';
                }
                statusBadge.className = 'badge bg-warning';
                statusBadge.textContent = 'Incomplete';
            }
        }

        // Update progress percentage
        updateCompletenessPercentage(completeness);
    }

    // Update individual checklist item
    function updateChecklistItem(itemKey, isComplete) {
        const itemElement = document.getElementById(`check-${itemKey}`);
        if (!itemElement) return;

        const icon = itemElement.querySelector('i');

        if (isComplete) {
            itemElement.classList.add('checklist-item-complete');
            if (icon) {
                icon.className = 'mdi mdi-check-circle me-2 text-success';
            }
        } else {
            itemElement.classList.remove('checklist-item-complete');
            if (icon) {
                icon.className = 'mdi mdi-circle-outline me-2 text-muted';
            }
        }
    }

    // Update completeness percentage
    function updateCompletenessPercentage(completeness) {
        const progressBar = document.getElementById('completenessProgress');
        const percentageText = document.getElementById('completenessPercentage');

        if (!progressBar || !percentageText) return;

        // Count completed items
        let completed = 0;
        let total = 0;

        ['basics', 'structure', 'content', 'resources', 'settings'].forEach(key => {
            total++;
            if (completeness[key]) completed++;
        });

        // Calculate percentage
        const percentage = Math.round((completed / total) * 100);

        // Update UI
        progressBar.style.width = `${percentage}%`;
        progressBar.setAttribute('aria-valuenow', percentage);
        percentageText.textContent = `${percentage}%`;

        // Update progress bar color
        if (percentage === 100) {
            progressBar.className = 'progress-bar progress-bar-striped bg-success';
        } else if (percentage >= 60) {
            progressBar.className = 'progress-bar progress-bar-striped bg-info';
        } else if (percentage >= 30) {
            progressBar.className = 'progress-bar progress-bar-striped bg-warning';
        } else {
            progressBar.className = 'progress-bar progress-bar-striped bg-danger';
        }
    }

    // Update publish button state
    function updatePublishButtonState(isReadyToPublish) {
        const publishButton = document.getElementById('publishCourse');
        if (!publishButton) return;

        if (isReadyToPublish) {
            publishButton.disabled = false;
            publishButton.classList.remove('btn-secondary');
            publishButton.classList.add('btn-primary');

            // Remove any tooltips
            if (publishButton.hasAttribute('data-bs-toggle')) {
                publishButton.removeAttribute('data-bs-toggle');
                publishButton.removeAttribute('data-bs-placement');
                publishButton.removeAttribute('title');

                // Destroy tooltip if it exists
                if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                    const tooltip = bootstrap.Tooltip.getInstance(publishButton);
                    if (tooltip) {
                        tooltip.dispose();
                    }
                }
            }
        } else {
            publishButton.disabled = true;
            publishButton.classList.remove('btn-primary');
            publishButton.classList.add('btn-secondary');

            // Add tooltip if not ready
            publishButton.setAttribute('data-bs-toggle', 'tooltip');
            publishButton.setAttribute('data-bs-placement', 'top');
            publishButton.setAttribute('title', 'Please complete all required sections before publishing');

            // Initialize tooltip if Bootstrap is available
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                new bootstrap.Tooltip(publishButton);
            }
        }
    }

    // Initialize Bootstrap components
    function initBootstrapComponents() {
        // Initialize any tooltips
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        // Initialize any accordions
        if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
            const accordionItems = document.querySelectorAll('.accordion-collapse');
            if (accordionItems.length > 0) {
                // Make first item expanded by default
                const firstItem = accordionItems[0];
                if (firstItem) {
                    const collapse = new bootstrap.Collapse(firstItem, {
                        toggle: false
                    });
                    collapse.show();
                }
            }
        }
    }

// Preview course with multiple fallback options
function previewCourse() {
    // Get course ID
    const courseId = document.getElementById('course_id')?.value;
    if (!courseId) {
        showAlert('warning', 'Course ID not found. Please save basic details first.');
        return;
    }

    // Simple direct method - open in new window
    const previewUrl = `../preview/course.php?course_id=${courseId}&preview=1&t=${Date.now()}`;
    window.open(previewUrl, '_blank');
}

    // Try multiple preview methods in order of preference
    function tryPreviewMethods(previewUrl) {
        // Method 1: Modern Bootstrap modal
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            try {
                showBootstrapModal(previewUrl);
                return;
            } catch (e) {
                console.warn("Bootstrap modal preview failed:", e);
            }
        }

        // Method 2: jQuery modal fallback
        if (typeof $ !== 'undefined' && $.fn.modal) {
            try {
                showjQueryModal(previewUrl);
                return;
            } catch (e) {
                console.warn("jQuery modal preview failed:", e);
            }
        }

        // Method 3: Standard popup window (most reliable)
        try {
            openPreviewInNewWindow(previewUrl);
            return;
        } catch (e) {
            console.warn("Window preview failed:", e);

            // Last resort: direct navigation
            if (confirm("Unable to open preview window. Would you like to open the preview in a new tab?")) {
                window.open(previewUrl, '_blank');
            }
        }
    }

    // Show preview in Bootstrap 5 modal
    function showBootstrapModal(previewUrl) {
        // Create or update modal
        let previewModal = document.getElementById('coursePreviewModal');

        if (!previewModal) {
            // Create new modal
            previewModal = document.createElement('div');
            previewModal.id = 'coursePreviewModal';
            previewModal.className = 'modal fade';
            previewModal.setAttribute('tabindex', '-1');
            previewModal.setAttribute('aria-hidden', 'true');
            previewModal.setAttribute('role', 'dialog');

            previewModal.innerHTML = `
        <div class="modal-dialog modal-xl modal-dialog-centered preview-modal-dialog">
            <div class="modal-content preview-modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Course Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe id="previewFrame" class="preview-iframe"
                        src="${previewUrl}"
                        title="Course Preview"></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close Preview</button>
                </div>
            </div>
        </div>
    `;

            document.body.appendChild(previewModal);
        } else {
            // Update existing modal iframe source
            const iframe = previewModal.querySelector('#previewFrame');
            if (iframe) {
                iframe.src = previewUrl;
            }
        }

        // Show modal
        const modal = new bootstrap.Modal(previewModal);
        modal.show();
    }

    // Show preview in jQuery modal
    function showjQueryModal(previewUrl) {
        // Create or update modal
        let previewModal = $('#coursePreviewModal');

        if (previewModal.length === 0) {
            // Create new jQuery modal
            const modalHTML = `
        <div class="modal fade" id="coursePreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered preview-modal-dialog">
                <div class="modal-content preview-modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Course Preview</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-0">
                        <iframe id="previewFrame" class="preview-iframe"
                            src="${previewUrl}"
                            title="Course Preview"></iframe>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close Preview</button>
                    </div>
                </div>
            </div>
        </div>
    `;

            $('body').append(modalHTML);
            previewModal = $('#coursePreviewModal');
        } else {
            // Update existing modal iframe source
            previewModal.find('#previewFrame').attr('src', previewUrl);
        }

        // Show modal
        previewModal.modal('show');
    }

    // Open preview in new window
    function openPreviewInNewWindow(previewUrl) {
        // Create a window with sensible dimensions
        const previewWindow = window.open(
            previewUrl,
            'coursePreview',
            'width=1024,height=768,resizable=yes,scrollbars=yes,status=yes'
        );

        // Focus the new window
        if (previewWindow) {
            previewWindow.focus();
        } else {
            // If window.open is blocked, show an alert
            throw new Error('Preview window was blocked by the browser');
        }
    }

    // Confirm publish dialog
    function confirmPublishCourse() {
        // Check if ready to publish
        const readinessStatus = document.getElementById('publishReadinessStatus');
        if (readinessStatus && readinessStatus.textContent !== 'Ready') {
            if (typeof showAlert === 'function') {
                showAlert('warning', 'Please complete all required sections before publishing your course.');
            } else {
                alert('Please complete all required sections before publishing your course.');
            }
            return;
        }

        // Modern confirmation dialog if Bootstrap modal is available
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            // Check if modal already exists
            let confirmModal = document.getElementById('publishConfirmModal');

            if (!confirmModal) {
                // Create modal element
                confirmModal = document.createElement('div');
                confirmModal.id = 'publishConfirmModal';
                confirmModal.className = 'modal fade publish-confirm-modal';
                confirmModal.setAttribute('tabindex', '-1');
                confirmModal.setAttribute('aria-hidden', 'true');

                confirmModal.innerHTML = `
<div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Publish Your Course</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center">
            <div class="mb-4">
                <i class="mdi mdi-rocket-launch-outline text-primary" style="font-size: 4rem;"></i>
            </div>
            <h4>Ready to Publish?</h4>
            <p>Your course will be submitted for review before becoming visible to students. This process usually takes 1-3 business days.</p>
            <p class="mb-0"><strong>Note:</strong> Once published, some information cannot be changed without going through the review process again.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" id="confirmPublishBtn">
                <i class="mdi mdi-publish me-1"></i> Publish Course
            </button>
        </div>
    </div>
</div>
`;

                document.body.appendChild(confirmModal);

                // Add event listener to confirm button
                const confirmBtn = confirmModal.querySelector('#confirmPublishBtn');
                if (confirmBtn) {
                    confirmBtn.addEventListener('click', function() {
                        // Dismiss modal
                        const modal = bootstrap.Modal.getInstance(confirmModal);
                        if (modal) {
                            modal.hide();
                        }

                        // Publish course
                        publishCourse();
                    });
                }
            }

            // Show modal
            const modal = new bootstrap.Modal(confirmModal);
            modal.show();
        } else {
            // Fallback to browser confirm
            if (confirm('Are you sure you want to publish this course? Once published, it will go through a review process before becoming visible to students.')) {
                publishCourse();
            }
        }
    }

    /**
     * Add these essential utility functions to your existing code
     * (Just before your script's closing tag)
     */

    // Utility function to create overlay loading indicators
    function createOverlay(message = "Loading...") {
        // Remove any existing overlay
        removeOverlay();

        // Create overlay element
        const overlay = document.createElement('div');
        overlay.id = 'pageOverlay';
        overlay.className = 'position-fixed d-flex align-items-center justify-content-center';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.width = '100%';
        overlay.style.height = '100%';
        overlay.style.backgroundColor = 'rgba(255, 255, 255, 0.8)';
        overlay.style.zIndex = '9999';

        // Create spinner and message
        overlay.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary mb-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-2">${message}</div>
        </div>
    `;

        // Add to document
        document.body.appendChild(overlay);

        return overlay;
    }

    // Remove overlay
    function removeOverlay() {
        const overlay = document.getElementById('pageOverlay');
        if (overlay) {
            overlay.remove();
        }
    }

    // Simple alert function if not already defined
    function showAlert(type, message, duration = 3000) {
        // Create alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.setAttribute('role', 'alert');
        alertDiv.style.top = '1rem';
        alertDiv.style.right = '1rem';
        alertDiv.style.zIndex = '9999';
        alertDiv.style.maxWidth = '90%';
        alertDiv.style.width = '350px';

        // Add message
        alertDiv.innerHTML = `
        <div>${message}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

        // Add to document
        document.body.appendChild(alertDiv);

        // Remove after duration
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 150);
        }, duration);

        return alertDiv;
    }

    // Call this function to initialize the review page when the page loads
    document.addEventListener('DOMContentLoaded', function() {
        // Set up event listeners for the review tab
        document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(event) {
                if (event.target.getAttribute('href') === '#step-6-review') {
                    loadCourseReviewAndCheckCompleteness();
                }
            });
        });

        // Also check if we're already on the review tab
        if (document.querySelector('.nav-link.active[href="#step-6-review"]')) {
            loadCourseReviewAndCheckCompleteness();
        }

        // Set up action buttons
        setupActionButtons();
        setupEditSectionLinks();
    });

    // Add this script to your page (temporarily) to help diagnose what's wrong
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Diagnostic script loaded');

        // Check if course ID exists
        const courseId = document.getElementById('course_id')?.value;
        console.log('Course ID found:', courseId);

        // Test if fetch is working properly with your backend
        if (courseId) {
            console.log('Attempting to fetch course details...');
            fetch(`../backend/courses/get_course_details.php?course_id=${courseId}`)
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.text();
                })
                .then(text => {
                    console.log('Raw response:', text);
                    try {
                        const data = JSON.parse(text);
                        console.log('Parsed data:', data);
                        return data;
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        throw e;
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                });
        }

        // Set up clicking functionality for buttons
        const previewBtn = document.getElementById('previewCourse');
        if (previewBtn) {
            console.log('Preview button found');
            previewBtn.addEventListener('click', function() {
                console.log('Preview button clicked');
                const previewUrl = `../preview/course.php?course_id=${courseId}&preview=1`;
                window.open(previewUrl, '_blank');
            });
        }

        const publishBtn = document.getElementById('publishCourse');
        if (publishBtn) {
            console.log('Publish button found');
            publishBtn.addEventListener('click', function() {
                console.log('Publish button clicked');
                if (confirm('Do you want to publish this course?')) {
                    console.log('Publishing course...');
                    fetch('../backend/courses/publish_course.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `course_id=${courseId}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log('Publish response:', data);
                            alert(data.message || 'Course published successfully!');
                        })
                        .catch(error => {
                            console.error('Publish error:', error);
                            alert('Error publishing course: ' + error.message);
                        });
                }
            });
        }

        const saveDraftBtn = document.getElementById('saveDraft');
        if (saveDraftBtn) {
            console.log('Save Draft button found');
            saveDraftBtn.addEventListener('click', function() {
                console.log('Save Draft button clicked');
                if (confirm('Save as draft and return to dashboard?')) {
                    window.location.href = '../instructor/courses.php';
                }
            });
        }

        // Display a simple course summary without all the complex code
        const reviewContainer = document.getElementById('courseReviewContainer');
        if (reviewContainer && courseId) {
            fetch(`../backend/courses/get_course_details.php?course_id=${courseId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        reviewContainer.innerHTML = `
                        <div class="alert alert-danger">
                            <strong>Error:</strong> ${data.error}
                        </div>
                    `;
                        return;
                    }

                    // Display basic course info
                    reviewContainer.innerHTML = `
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Course Summary</h5>
                        </div>
                        <div class="card-body">
                            <h4>${data.title || 'No title'}</h4>
                            <p>${data.short_description || 'No description'}</p>
                            <p><strong>Status:</strong> ${data.status || 'Draft'}</p>
                            <p><strong>Sections:</strong> ${data.sections?.length || 0}</p>
                            <p><strong>Price:</strong> ₵${parseFloat(data.price || 0).toFixed(2)}</p>
                        </div>
                    </div>
                `;
                })
                .catch(error => {
                    console.error('Error fetching course details:', error);
                    reviewContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Error:</strong> ${error.message || 'Could not load course details'}
                    </div>
                `;
                });
        }
    });

    // Utility function to create overlay loading indicators
    function createOverlay(message = "Loading...") {
        // Remove any existing overlay
        removeOverlay();

        // Create overlay element
        const overlay = document.createElement('div');
        overlay.id = 'pageOverlay';
        overlay.className = 'position-fixed d-flex align-items-center justify-content-center';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.width = '100%';
        overlay.style.height = '100%';
        overlay.style.backgroundColor = 'rgba(255, 255, 255, 0.8)';
        overlay.style.zIndex = '9999';

        // Create spinner and message
        overlay.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary mb-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-2">${message}</div>
        </div>
    `;

        // Add to document
        document.body.appendChild(overlay);

        return overlay;
    }

    // Remove overlay
    function removeOverlay() {
        const overlay = document.getElementById('pageOverlay');
        if (overlay) {
            overlay.remove();
        }
    }

    // Simple alert function if not already defined
    function showAlert(type, message, duration = 3000) {
        // Create alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.setAttribute('role', 'alert');
        alertDiv.style.top = '1rem';
        alertDiv.style.right = '1rem';
        alertDiv.style.zIndex = '9999';
        alertDiv.style.maxWidth = '90%';
        alertDiv.style.width = '350px';

        // Add message
        alertDiv.innerHTML = `
        <div>${message}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

        // Add to document
        document.body.appendChild(alertDiv);

        // Remove after duration
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 150);
        }, duration);

        return alertDiv;
    }

    // This is a complete replacement script that uses a simpler approach
// to getting your review tab working quickly.

document.addEventListener('DOMContentLoaded', function() {
    // Utility functions
    function createOverlay(message = "Loading...") {
        removeOverlay();
        const overlay = document.createElement('div');
        overlay.id = 'pageOverlay';
        overlay.className = 'position-fixed d-flex align-items-center justify-content-center';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.width = '100%';
        overlay.style.height = '100%';
        overlay.style.backgroundColor = 'rgba(255, 255, 255, 0.8)';
        overlay.style.zIndex = '9999';
        
        overlay.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary mb-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-2">${message}</div>
            </div>
        `;
        
        document.body.appendChild(overlay);
        return overlay;
    }

    function removeOverlay() {
        const overlay = document.getElementById('pageOverlay');
        if (overlay) overlay.remove();
    }

    function showAlert(type, message, duration = 3000) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.setAttribute('role', 'alert');
        alertDiv.style.top = '1rem';
        alertDiv.style.right = '1rem';
        alertDiv.style.zIndex = '9999';
        alertDiv.style.maxWidth = '90%';
        alertDiv.style.width = '350px';
        
        alertDiv.innerHTML = `
            <div>${message}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 150);
        }, duration);
        
        return alertDiv;
    }
    
    // Load course details for review - simplified version
    function loadCourseReview() {
        const courseId = document.getElementById('course_id')?.value;
        const reviewContainer = document.getElementById('courseReviewContainer');
        
        if (!courseId || !reviewContainer) {
            if (!courseId) {
                showAlert('warning', 'Course ID not found. Please save basic details first.');
                if (reviewContainer) {
                    reviewContainer.innerHTML = `
                        <div class="alert alert-warning">
                            <strong>Course ID not found</strong>
                            <p>Please complete the Basic Details step first to create your course.</p>
                        </div>
                    `;
                }
            }
            removeOverlay();
            return;
        }
        
        createOverlay("Loading course details...");
        
        // Fetch course details
        fetch(`../backend/courses/get_course_details.php?course_id=${courseId}`)
            .then(response => response.text())
            .then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Error parsing JSON:', e);
                    console.log('Response text:', text);
                    throw new Error('Could not parse server response');
                }
            })
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                
                // Clear loading state
                reviewContainer.innerHTML = '';
                
                // Render a simple course summary
                reviewContainer.innerHTML = generateCourseReviewHTML(data);
                
                // Update completeness checklist
                updateCompletenessChecklist(data);
                
                // Show success message
                showAlert('success', 'Course details loaded successfully!');
            })
            .catch(error => {
                console.error('Error loading course:', error);
                
                reviewContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <h5 class="alert-heading">Error Loading Course</h5>
                        <p>${error.message || 'Could not load course details. Please try again.'}</p>
                    </div>
                `;
                
                // Update completeness with errors
                updateCompletenessChecklist(null, true);
            })
            .finally(() => {
                removeOverlay();
            });
    }
    
    // Generate simplified course review HTML
    function generateCourseReviewHTML(course) {
        // Basic information card
        const basicInfo = `
            <div class="card mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="mdi mdi-information-outline me-2"></i> Basic Information</h5>
                    <a href="#" class="btn btn-sm btn-outline-primary edit-section" data-step="1">
                        <i class="mdi mdi-pencil me-1"></i> Edit
                    </a>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <img src="../uploads/thumbnails/${course.thumbnail || 'default.jpg'}" 
                                class="img-fluid rounded shadow-sm" alt="Course Thumbnail">
                        </div>
                        <div class="col-md-8">
                            <h4>${course.title || 'Untitled Course'}</h4>
                            <p class="text-muted">${course.short_description || 'No description provided'}</p>
                            <div class="mb-2">
                                <strong>Status:</strong> 
                                <span class="badge ${course.status === 'Published' ? 'bg-success' : 'bg-warning'}">
                                    ${course.status || 'Draft'}
                                </span>
                            </div>
                            <div class="mb-2">
                                <strong>Price:</strong> 
                                <span class="badge bg-primary">₵${parseFloat(course.price || 0).toFixed(2)}</span>
                            </div>
                        </div>
                    </div>
                    
                    <h5 class="border-bottom pb-2 mb-3">Full Description</h5>
                    <div class="p-3 bg-light rounded mb-3">
                        ${course.full_description || '<em class="text-muted">No full description provided</em>'}
                    </div>
                </div>
            </div>
        `;
        
        // Structure card
        const structure = `
            <div class="card mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="mdi mdi-format-list-bulleted me-2"></i> Course Structure</h5>
                    <a href="#" class="btn btn-sm btn-outline-primary edit-section" data-step="2">
                        <i class="mdi mdi-pencil me-1"></i> Edit
                    </a>
                </div>
                <div class="card-body">
                    <p>Your course contains <strong>${course.sections?.length || 0}</strong> sections.</p>
                    
                    <ul class="list-group">
                        ${course.sections?.map((section, index) => `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold">Section ${index + 1}:</span> ${section.title}
                                </div>
                                <span class="badge bg-primary rounded-pill">${section.topic_count || 0} topics</span>
                            </li>
                        `).join('') || '<li class="list-group-item">No sections defined yet</li>'}
                    </ul>
                </div>
            </div>
        `;
        
        // Settings card
        const settings = `
            <div class="card mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="mdi mdi-cog-outline me-2"></i> Course Settings</h5>
                    <a href="#" class="btn btn-sm btn-outline-primary edit-section" data-step="5">
                        <i class="mdi mdi-pencil me-1"></i> Edit
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Course Level:</strong>
                                <span class="badge bg-info">${course.course_level || 'Not set'}</span>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Certificate:</strong>
                                <span class="badge ${course.certificate_enabled ? 'bg-success' : 'bg-secondary'}">
                                    ${course.certificate_enabled ? 'Enabled' : 'Disabled'}
                                </span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Requirements:</strong>
                                ${course.requirements?.length ? 
                                    `<ul class="list-unstyled mb-0 mt-2">
                                        ${course.requirements.map(req => `
                                            <li><i class="mdi mdi-check-circle text-success me-2"></i>${req.requirement_text || req}</li>
                                        `).join('')}
                                    </ul>` : 
                                    '<span class="text-muted">No requirements specified</span>'
                                }
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Combine and return all cards
        return basicInfo + structure + settings;
    }
    
    // Update completeness checklist (simplified)
    function updateCompletenessChecklist(course, hasError = false) {
        // Set up default states
        const completeness = {
            basics: !!course?.title,
            structure: !!(course?.sections && course.sections.length > 0),
            content: false,
            resources: false,
            settings: !!course?.course_level
        };
        
        // Check for content
        if (course?.sections) {
            for (const section of course.sections) {
                if (section.topic_count > 0) {
                    completeness.content = true;
                    break;
                }
            }
        }
        
        // Update checklist items
        updateChecklistItem('basics', completeness.basics);
        updateChecklistItem('structure', completeness.structure);
        updateChecklistItem('content', completeness.content);
        updateChecklistItem('resources', completeness.resources);
        updateChecklistItem('settings', completeness.settings);
        
        // Update final readiness
        const finalCheckItem = document.getElementById('check-final');
        const statusBadge = document.getElementById('publishReadinessStatus');
        
        if (finalCheckItem && statusBadge) {
            const icon = finalCheckItem.querySelector('i');
            const isReady = completeness.basics && completeness.structure && completeness.content;
            
            if (hasError) {
                finalCheckItem.classList.remove('checklist-item-complete');
                finalCheckItem.classList.add('checklist-item-error');
                if (icon) icon.className = 'mdi mdi-alert-circle me-2 text-danger';
                statusBadge.className = 'badge bg-danger';
                statusBadge.textContent = 'Error';
            } else if (isReady) {
                finalCheckItem.classList.add('checklist-item-complete');
                finalCheckItem.classList.remove('checklist-item-error');
                if (icon) icon.className = 'mdi mdi-check-circle me-2 text-success';
                statusBadge.className = 'badge bg-success';
                statusBadge.textContent = 'Ready';
            } else {
                finalCheckItem.classList.remove('checklist-item-complete');
                finalCheckItem.classList.remove('checklist-item-error');
                if (icon) icon.className = 'mdi mdi-circle-outline me-2 text-muted';
                statusBadge.className = 'badge bg-warning';
                statusBadge.textContent = 'Incomplete';
            }
            
            // Enable/disable publish button
            const publishButton = document.getElementById('publishCourse');
            if (publishButton) {
                publishButton.disabled = !isReady;
            }
        }
        
        // Update progress percentage
        updateCompletenessPercentage(completeness);
    }
    
    // Update individual checklist item
    function updateChecklistItem(itemKey, isComplete) {
        const itemElement = document.getElementById(`check-${itemKey}`);
        if (!itemElement) return;
        
        const icon = itemElement.querySelector('i');
        
        if (isComplete) {
            itemElement.classList.add('checklist-item-complete');
            if (icon) icon.className = 'mdi mdi-check-circle me-2 text-success';
        } else {
            itemElement.classList.remove('checklist-item-complete');
            if (icon) icon.className = 'mdi mdi-circle-outline me-2 text-muted';
        }
    }
    
    // Update completeness percentage
    function updateCompletenessPercentage(completeness) {
        const progressBar = document.getElementById('completenessProgress');
        const percentageText = document.getElementById('completenessPercentage');
        
        if (!progressBar || !percentageText) return;
        
        // Count completed items
        let completed = 0;
        let total = 0;
        
        for (const key in completeness) {
            if (key !== 'readyToPublish') {
                total++;
                if (completeness[key]) completed++;
            }
        }
        
        // Calculate percentage
        const percentage = Math.round((completed / total) * 100);
        
        // Update UI
        progressBar.style.width = `${percentage}%`;
        progressBar.setAttribute('aria-valuenow', percentage);
        percentageText.textContent = `${percentage}%`;
        
        // Update progress bar color
        if (percentage === 100) {
            progressBar.className = 'progress-bar progress-bar-striped bg-success';
        } else if (percentage >= 60) {
            progressBar.className = 'progress-bar progress-bar-striped bg-info';
        } else if (percentage >= 30) {
            progressBar.className = 'progress-bar progress-bar-striped bg-warning';
        } else {
            progressBar.className = 'progress-bar progress-bar-striped bg-danger';
        }
    }
    
    // Setup edit section links to jump to specific tabs
    function setupEditSectionLinks() {
        document.querySelectorAll('.edit-section').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                const step = parseInt(this.getAttribute('data-step'), 10);
                if (isNaN(step)) return;
                
                // Navigate to the specified step
                if (typeof navigateToStep === 'function') {
                    navigateToStep(step);
                } else {
                    // Fallback for basic navigation
                    const tabLinks = document.querySelectorAll('.nav-pills .nav-link');
                    if (tabLinks[step - 1]) {
                        if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                            const tab = new bootstrap.Tab(tabLinks[step - 1]);
                            tab.show();
                        } else {
                            // Extremely basic fallback
                            tabLinks.forEach(tab => tab.classList.remove('active'));
                            tabLinks[step - 1].classList.add('active');
                            
                            // Find the corresponding tab content
                            const tabId = tabLinks[step - 1].getAttribute('href');
                            if (tabId) {
                                document.querySelectorAll('.tab-pane').forEach(pane => {
                                    pane.classList.remove('show', 'active');
                                });
                                const tabPane = document.querySelector(tabId);
                                if (tabPane) {
                                    tabPane.classList.add('show', 'active');
                                }
                            }
                        }
                    }
                }
            });
        });
    }
    
    // Setup action buttons
    function setupActionButtons() {
        // Preview button
        const previewButton = document.getElementById('previewCourse');
        if (previewButton) {
            previewButton.addEventListener('click', function() {
                const courseId = document.getElementById('course_id')?.value;
                if (!courseId) {
                    showAlert('warning', 'Course ID not found. Please save basic details first.');
                    return;
                }
                
                const previewUrl = `../preview/course.php?course_id=${courseId}&preview=1&t=${Date.now()}`;
                window.open(previewUrl, '_blank');
            });
        }
        
        // Save as draft button
        const saveDraftButton = document.getElementById('saveDraft');
        if (saveDraftButton) {
            saveDraftButton.addEventListener('click', function() {
                if (confirm('Save your progress and return to the instructor dashboard?')) {
                    showAlert('success', 'Course saved as draft!');
                    setTimeout(() => {
                        window.location.href = '../instructor/courses.php';
                    }, 1000);
                }
            });
        }
        
        // Publish button
        const publishButton = document.getElementById('publishCourse');
        if (publishButton) {
            publishButton.addEventListener('click', function() {
                const readinessStatus = document.getElementById('publishReadinessStatus');
                if (readinessStatus && readinessStatus.textContent !== 'Ready') {
                    showAlert('warning', 'Please complete all required sections before publishing your course.');
                    return;
                }
                
                if (confirm('Are you sure you want to publish this course? Once published, it will go through a review process before becoming visible to students.')) {
                    publishCourse();
                }
            });
        }
    }
    
    // Publish course
    function publishCourse() {
        // Get course ID
        const courseId = document.getElementById('course_id')?.value;
        if (!courseId) {
            showAlert('danger', 'Course ID not found. Please save basic details first.');
            return;
        }
        
        // Show loading overlay
        createOverlay("Publishing your course...");
        
        // Disable publish button
        const publishButton = document.getElementById('publishCourse');
        if (publishButton) {
            publishButton.disabled = true;
        }
        
        // Send publish request
        fetch('../backend/courses/publish_course.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `course_id=${courseId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showAlert('success', 'Course published successfully! It will now go through a review process.');
                
                // Display success state
                showPublishSuccess();
                
                // Redirect to instructor dashboard after a delay
                setTimeout(() => {
                    window.location.href = '../instructor/courses.php';
                }, 3000);
            } else {
                // Show error
                showAlert('danger', data.message || 'Error publishing course. Please try again.');
                
                // Re-enable publish button
                if (publishButton) {
                    publishButton.disabled = false;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Error publishing course: ' + error.message);
            
            // Re-enable publish button
            if (publishButton) {
                publishButton.disabled = false;
            }
        })
        .finally(() => {
            // Remove overlay
            removeOverlay();
        });
    }
    
    // Show publish success state
    function showPublishSuccess() {
        // Create success message overlay
        const successOverlay = document.createElement('div');
        successOverlay.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center';
        successOverlay.style.backgroundColor = 'rgba(255, 255, 255, 0.9)';
        successOverlay.style.zIndex = '9999';
        
        successOverlay.innerHTML = `
            <div class="text-center">
                <div class="mb-4">
                    <i class="mdi mdi-check-circle text-success" style="font-size: 5rem;"></i>
                </div>
                <h2 class="mb-3">Course Published Successfully!</h2>
                <p class="lead">Your course has been submitted for review.</p>
                <p>You will be redirected to your instructor dashboard in a moment...</p>
            </div>
        `;
        
        document.body.appendChild(successOverlay);
    }
    
    // Initialize the page
    function initReviewPage() {
        setupEditSectionLinks();
        setupActionButtons();
        loadCourseReview();
    }
    
    // Set up tab change listeners
    document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(event) {
            if (event.target.getAttribute('href') === '#step-6-review') {
                loadCourseReview();
            }
        });
    });
    
    // Run initialization if we're already on the review tab
    if (document.querySelector('.nav-link.active[href="#step-6-review"]')) {
        initReviewPage();
    } else {
        // Otherwise wait for tab show events
        setupEditSectionLinks();
        setupActionButtons();
    }
});
</script>