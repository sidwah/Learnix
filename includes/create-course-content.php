<?php

/**
 * Enhanced Content Creation Tab
 * File: ../includes/create-course-content.php
 * 
 * Improvements:
 * - Consistent ID naming with step-3-content
 * - Enhanced UI for adding topics and quizzes
 * - Improved validation and error handling
 * - Better visual feedback for actions
 * - Support for editing existing content
 */
?>
<div class="card">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Course Content Creation</h5>
        <div class="content-stats">
            <span class="badge bg-primary me-1" id="topicsCount">0 Topics</span>
            <span class="badge bg-danger" id="quizzesCount">0 Quizzes</span>
        </div>
    </div>
    <div class="card-body">
        <p class="text-muted">Add lessons, resources, and assessments to each section of your course.</p>

        <!-- Content Guide -->
        <div class="alert alert-info mb-4" role="alert">
            <h5 class="d-flex align-items-center">
                <i class="mdi mdi-lightbulb me-2"></i>
                <strong>Creating Effective Course Content</strong>
            </h5>
            <div class="row mb-2">
                <div class="col-md-6">
                    <h6 class="fw-bold">Topics (Lessons)</h6>
                    <ul class="mb-0">
                        <li><strong>Text:</strong> In-depth written content for reading-focused learners</li>
                        <li><strong>Video:</strong> Engaging video content from YouTube or external links</li>
                        <li><strong>Links:</strong> External resources for additional learning materials</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold">Quizzes (Assessments)</h6>
                    <ul class="mb-0">
                        <li>Add quizzes to test student understanding</li>
                        <li>Set a fair pass mark based on difficulty</li>
                        <li>Randomize questions option for better assessment</li>
                    </ul>
                </div>
            </div>
            <div class="alert alert-light mt-2 mb-0 p-2">
                <small><i class="mdi mdi-information-outline me-1"></i> Click each section to expand it and add content. You can add multiple topics and quizzes to each section.</small>
            </div>
        </div>

        <!-- Section Content Builder -->
        <div id="sectionContentArea" class="mb-4">
            <div class="content-loading text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading sections...</span>
                </div>
                <p class="mt-2">Loading course sections...</p>
            </div>

            <!-- Sections will be populated here dynamically -->
        </div>

        <!-- Empty State -->
        <div id="noSectionsWarning" class="alert alert-warning" style="display: none;">
            <div class="d-flex align-items-center">
                <i class="mdi mdi-alert-circle me-3" style="font-size: 24px;"></i>
                <div>
                    <h5 class="mb-1">No Sections Found</h5>
                    <p class="mb-0">Please go back to the Course Structure step and create at least one section before adding content.</p>
                </div>
            </div>
            <div class="mt-3 text-center">
                <button type="button" class="btn btn-warning" onclick="navigateToStep(2)">
                    <i class="mdi mdi-arrow-left me-1"></i> Go to Course Structure
                </button>
            </div>
        </div>

        <!-- Content Validation Error Summary -->
        <div id="contentValidationErrors" class="alert alert-danger mt-3" style="display: none;">
            <h5><i class="mdi mdi-alert-circle me-2"></i>Please fix the following issues:</h5>
            <ul id="contentErrorList"></ul>
        </div>
    </div>
</div>

<!-- Section Content Template -->
<template id="sectionContentTemplate">
    <div class="section-content-card card mb-3" data-section-id="{sectionId}" data-section-index="{sectionIndex}">
        <div class="card-header d-flex align-items-center" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#sectionContent{sectionId}">
            <div class="section-number badge bg-primary me-2">{sectionNumber}</div>
            <h6 class="mb-0 flex-grow-1 section-title">{sectionTitle}</h6>
            <div class="section-stats me-2">
                <span class="badge bg-light text-dark section-topic-count">0 Topics</span>
                <span class="badge bg-light text-dark section-quiz-count">0 Quizzes</span>
            </div>
            <i class="mdi mdi-chevron-down section-toggle-icon"></i>
        </div>
        <div class="collapse" id="sectionContent{sectionId}">
            <div class="card-body">
                <div class="content-container">
                    <!-- Topics and quizzes will be added here -->
                </div>

                <!-- Empty State -->
                <div class="section-empty-state text-center py-4 border rounded bg-light mb-3">
                    <i class="mdi mdi-file-document-outline text-muted mb-2" style="font-size: 32px;"></i>
                    <p class="mb-0 text-muted">No content added to this section yet</p>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between align-items-center">
                    <div class="button-group">
                        <button type="button" class="btn btn-primary add-topic-btn" data-section-id="{sectionId}" data-section-index="{sectionIndex}">
                            <i class="mdi mdi-plus-circle me-1"></i> Add Topic
                        </button>
                        <button type="button" class="btn btn-outline-danger add-quiz-btn" data-section-id="{sectionId}" data-section-index="{sectionIndex}">
                            <i class="mdi mdi-plus-circle me-1"></i> Add Quiz
                        </button>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-secondary content-collapse-btn">
                        <i class="mdi mdi-chevron-up"></i> Collapse
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Topic Template -->
<template id="topicTemplate">
    <div class="topic-item card mb-3 border-left-success" data-topic-index="{topicIndex}">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0 d-flex align-items-center">
                <i class="mdi mdi-book-open-variant me-2 text-success"></i>
                <span class="topic-number">Topic {topicNumber}</span>
            </h6>
            <div class="topic-actions">
                <button type="button" class="btn btn-sm btn-outline-danger remove-topic-btn" title="Remove topic">
                    <i class="mdi mdi-trash-can"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Topic Title -->
            <div class="form-group mb-3">
                <label class="form-label">Topic Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control topic-title" name="topic_titles_{sectionId}[]"
                    placeholder="Enter a descriptive title for this topic" required>
                <div class="invalid-feedback">Topic title is required</div>
                <input type="hidden" name="topic_section_ids_{sectionId}[]" value="{sectionId}">
            </div>

            <!-- Content Type Selection -->
            <div class="form-group mb-3">
                <label class="form-label">Content Type <span class="text-danger">*</span></label>
                <select class="form-control content-type-select" name="content_type_{sectionId}[]" required>
                    <option value="" disabled selected>Select content type</option>
                    <option value="text">Text Content</option>
                    <option value="video">Video</option>
                    <option value="link">External Link</option>
                </select>
                <div class="invalid-feedback">Please select a content type</div>
            </div>

            <!-- Content Type Fields (Shown/Hidden based on selection) -->
            <div class="content-type-fields">
                <!-- Text Content Fields -->
                <div class="text-content-fields d-none">
                    <div class="form-group mb-3">
                        <label class="form-label">Text Content <span class="text-danger">*</span></label>
                        <textarea class="form-control text-content" name="topic_text_content_{sectionId}[]"
                            rows="5" placeholder="Enter the educational content for this topic"></textarea>
                        <div class="invalid-feedback">Please provide some text content</div>
                        <small class="form-text text-muted">Use clear explanations and examples to help students understand the material.</small>
                    </div>
                </div>

                <!-- Video Content Fields -->
                <div class="video-content-fields d-none">
                    <div class="form-group mb-3">
                        <label class="form-label">Video Source <span class="text-danger">*</span></label>
                        <select class="form-control video-type-select" name="video_type_{sectionId}[]">
                            <option value="" disabled selected>Select video source</option>
                            <option value="youtube">YouTube</option>
                            <option value="external">External Video Link</option>
                        </select>
                        <div class="invalid-feedback">Please select a video source</div>
                    </div>

                    <div class="form-group mb-3 video-url-field d-none">
                        <label class="form-label">Video URL <span class="text-danger">*</span></label>
                        <input type="url" class="form-control video-url" name="topic_video_links_{sectionId}[]"
                            placeholder="Enter the video URL (e.g., YouTube link)">
                        <div class="invalid-feedback">Please enter a valid video URL</div>
                        <small class="form-text text-muted youtube-help d-none">
                            Use the standard YouTube URL (e.g., https://www.youtube.com/watch?v=abcXYZ)
                        </small>
                        <small class="form-text text-muted external-help d-none">
                            Enter a direct link to the video file or streaming URL
                        </small>
                    </div>
                </div>

                <!-- External Link Fields -->
                <div class="link-content-fields d-none">
                    <div class="form-group mb-3">
                        <label class="form-label">External Resource URL <span class="text-danger">*</span></label>
                        <input type="url" class="form-control link-url" name="topic_external_links_{sectionId}[]"
                            placeholder="Enter the URL to the external resource">
                        <div class="invalid-feedback">Please enter a valid URL</div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Resource Description <span class="text-danger">*</span></label>
                        <textarea class="form-control link-description" name="topic_link_descriptions_{sectionId}[]"
                            rows="2" placeholder="Briefly describe what students will find at this link"></textarea>
                        <div class="invalid-feedback">Please provide a description for this resource</div>
                    </div>
                </div>
            </div>

            <!-- Common Fields -->
            <div class="form-group mb-0">
                <label class="form-label">Topic Description <small class="text-muted">(Optional)</small></label>
                <textarea class="form-control" name="topic_descriptions_{sectionId}[]"
                    rows="2" placeholder="Add a brief description of what students will learn in this topic"></textarea>
                <small class="form-text text-muted">This helps students understand what to expect from this topic.</small>
            </div>
        </div>
    </div>
</template>

<!-- Quiz Template -->
<template id="quizTemplate">
    <div class="quiz-item card mb-3 border-left-danger" data-quiz-index="{quizIndex}">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0 d-flex align-items-center">
                <i class="mdi mdi-help-circle me-2 text-danger"></i>
                <span class="quiz-number">Quiz {quizNumber}</span>
            </h6>
            <div class="quiz-actions">
                <button type="button" class="btn btn-sm btn-outline-danger remove-quiz-btn" title="Remove quiz">
                    <i class="mdi mdi-trash-can"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Quiz Title -->
            <div class="form-group mb-3">
                <label class="form-label">Quiz Title <span class="text-danger">*</span></label>
                <input type="text"
                    class="form-control quiz-title"
                    name="quiz_titles_{sectionId}[]"
                    placeholder="Enter a descriptive name for this quiz"
                    required>
                <div class="invalid-feedback">Quiz title is required</div>
                <input type="hidden" name="quiz_section_ids_{sectionId}[]" value="{sectionId}">
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <!-- Pass Mark -->
                    <label class="form-label">Pass Mark (%) <span class="text-danger">*</span></label>
                    <input type="number"
                        class="form-control pass-mark"
                        name="quiz_pass_marks_{sectionId}[]"
                        min="1"
                        max="100"
                        value="70"
                        required>
                    <div class="invalid-feedback">Please enter a pass mark between 1-100</div>
                </div>

                <div class="col-md-4 mb-3">
                    <!-- Time Limit -->
                    <label class="form-label">Time Limit (minutes)</label>
                    <input type="number"
                        class="form-control time-limit"
                        name="quiz_time_limits_{sectionId}[]"
                        min="1"
                        placeholder="e.g. 30">
                </div>

                <div class="col-md-4 mb-3">
                    <!-- Randomize Questions -->
                    <label class="form-label d-block">Randomize Questions</label>
                    <div class="form-check form-switch mt-1">
                        <input class="form-check-input randomize-questions"
                            type="checkbox"
                            name="quiz_random_{sectionId}[]"
                            value="1">
                        <label class="form-check-label">Shuffle order for each student</label>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="form-group mb-3">
                <label class="form-label">Instructions</label>
                <textarea class="form-control"
                    name="quiz_instructions_{sectionId}[]"
                    rows="3"
                    placeholder="Write any special instructions for students..."></textarea>
            </div>

            <!-- Next Phase Note -->
            <div class="alert alert-secondary mb-0">
                <small>
                    <i class="mdi mdi-information-outline me-1"></i>
                    You will be able to add questions to this quiz in the next phase after publishing your course.
                </small>
            </div>
        </div>
    </div>
</template>
<script>
$(document).ready(function () {
    $('#quiz-form').on('submit', function (e) {
        e.preventDefault(); // prevent normal form submit

        const form = $(this);
        const formData = new FormData(this);

        $.ajax({
            url: 'save_quiz.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    alert('✅ ' + response.message);
                    // optionally reload quizzes, clear form, etc.
                } else {
                    alert('❌ ' + response.message);
                }
            },
            error: function () {
                alert('❌ An error occurred while saving quizzes.');
            }
        });
    });
});
</script>



<!-- Custom CSS for Content Creation -->
<style>
    /* Section styling */
    .section-content-card {
        transition: all 0.3s ease;
        margin-bottom: 15px;
    }

    .section-content-card:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .section-content-card .card-header {
        transition: background-color 0.3s ease;
    }

    .section-content-card .card-header:hover {
        background-color: #f0f7ff;
    }

    .section-number {
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
    }

    .section-toggle-icon {
        transition: transform 0.3s ease;
    }

    .collapse.show+.card-body .section-toggle-icon {
        transform: rotate(180deg);
    }

    /* Topic and Quiz cards */
    .topic-item,
    .quiz-item {
        transition: all 0.2s ease;
    }

    .topic-item:hover,
    .quiz-item:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .border-left-success {
        border-left: 4px solid #28a745;
    }

    .border-left-danger {
        border-left: 4px solid #dc3545;
    }

    /* Empty state styling */
    .section-empty-state {
        transition: opacity 0.3s ease;
    }

    /* Animation for new items */
    @keyframes highlightNew {
        0% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.5);
        }

        70% {
            box-shadow: 0 0 0 6px rgba(40, 167, 69, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
        }
    }

    .highlight-new {
        animation: highlightNew 1s ease;
    }

    /* Error highlighting */
    .is-invalid-card {
        border-color: #dc3545;
    }

    /* Content stats badges */
    .content-stats .badge {
        min-width: 80px;
    }
</style>

<!-- Enhanced JavaScript for Content Creation -->
<script>
    /**
     * Enhanced Content Creation Module
     * 
     * Features:
     * - Improved UI for adding topics and quizzes
     * - Better validation with visual feedback
     * - Enhanced error handling
     * - Support for editing existing content
     * - Real-time content statistics
     */

    // Content tracking variables
    let topicCounter = {};
    let quizCounter = {};
    let totalTopics = 0;
    let totalQuizzes = 0;

    /**
     * Initialize the content creation UI
     */
    document.addEventListener('DOMContentLoaded', function() {
        // Setup event handling for content tab
        setupContentEventHandlers();
    });

    /**
     * Set up event handlers for the content creation tab
     */
    function setupContentEventHandlers() {
        // Delegate events for the section content area
        const sectionContentArea = document.getElementById('sectionContentArea');
        if (sectionContentArea) {
            // Add topic button clicks
            sectionContentArea.addEventListener('click', function(e) {
                // Add topic button
                if (e.target.closest('.add-topic-btn')) {
                    const button = e.target.closest('.add-topic-btn');
                    const sectionId = button.getAttribute('data-section-id');
                    const sectionIndex = button.getAttribute('data-section-index');
                    addTopicToSection(sectionId, sectionIndex);
                }

                // Add quiz button
                if (e.target.closest('.add-quiz-btn')) {
                    const button = e.target.closest('.add-quiz-btn');
                    const sectionId = button.getAttribute('data-section-id');
                    const sectionIndex = button.getAttribute('data-section-index');
                    addQuizToSection(sectionId, sectionIndex);
                }

                // Remove topic button
                if (e.target.closest('.remove-topic-btn')) {
                    const topicItem = e.target.closest('.topic-item');
                    if (topicItem) {
                        removeTopic(topicItem);
                    }
                }

                // Remove quiz button
                if (e.target.closest('.remove-quiz-btn')) {
                    const quizItem = e.target.closest('.quiz-item');
                    if (quizItem) {
                        removeQuiz(quizItem);
                    }
                }

                // Collapse section button
                if (e.target.closest('.content-collapse-btn')) {
                    const section = e.target.closest('.section-content-card');
                    if (section) {
                        const collapse = section.querySelector('.collapse');
                        if (collapse && typeof bootstrap !== 'undefined') {
                            const bsCollapse = bootstrap.Collapse.getInstance(collapse);
                            if (bsCollapse) {
                                bsCollapse.hide();
                            }
                        }
                    }
                }
            });

            // Handle content type selection changes
            sectionContentArea.addEventListener('change', function(e) {
                // Content type select
                if (e.target.classList.contains('content-type-select')) {
                    toggleContentTypeFields(e.target);
                }

                // Video type select
                if (e.target.classList.contains('video-type-select')) {
                    toggleVideoUrlField(e.target);
                }

                // Input validation on change
                if (e.target.closest('.form-control')) {
                    const input = e.target.closest('.form-control');
                    validateField(input);
                }
            });

            // Input validation on blur
            sectionContentArea.addEventListener('blur', function(e) {
                if (e.target.closest('.form-control')) {
                    const input = e.target.closest('.form-control');
                    validateField(input);
                }
            }, true);
        }
    }

    /**
     * Load content UI for all sections
     */
    function loadSectionContentUI() {
        const sectionContentArea = document.getElementById('sectionContentArea');
        const noSectionsWarning = document.getElementById('noSectionsWarning');

        if (!sectionContentArea) return;

        // Show loading state initially
        sectionContentArea.innerHTML = `
<div class="content-loading text-center py-4">
<div class="spinner-border text-primary" role="status">
<span class="visually-hidden">Loading sections...</span>
</div>
<p class="mt-2">Loading course sections...</p>
</div>
`;

        // Get section IDs from hidden field
        const sectionIdsField = document.getElementById('section_ids');
        if (!sectionIdsField || !sectionIdsField.value) {
            // No sections found
            sectionContentArea.innerHTML = '';
            if (noSectionsWarning) {
                noSectionsWarning.style.display = 'block';
            }
            return;
        }

        try {
            const sectionIds = JSON.parse(sectionIdsField.value);

            // Clear previous content
            sectionContentArea.innerHTML = '';
            if (noSectionsWarning) {
                noSectionsWarning.style.display = 'none';
            }

            // Get section elements from the previous step
            const sections = document.querySelectorAll('.section-item');

            // Check if we have section data
            if (Object.keys(sectionIds).length === 0) {
                // No sections found
                if (noSectionsWarning) {
                    noSectionsWarning.style.display = 'block';
                }
                return;
            }

            // Reset counters
            topicCounter = {};
            quizCounter = {};
            totalTopics = 0;
            totalQuizzes = 0;

            // Create content cards for each section
            Object.entries(sectionIds).forEach(([index, sectionId], position) => {
                // Get section title from previous step
                let sectionTitle = `Section ${position + 1}`;
                const sectionElement = sections[parseInt(index)];

                if (sectionElement) {
                    const titleInput = sectionElement.querySelector('input[name="sections[]"]');
                    if (titleInput && titleInput.value) {
                        sectionTitle = titleInput.value;
                    }
                }

                // Initialize counters for this section
                topicCounter[sectionId] = 0;
                quizCounter[sectionId] = 0;

                // Get template and replace placeholders
                const template = document.getElementById('sectionContentTemplate');
                if (template) {
                    let content = template.innerHTML
                        .replace(/{sectionId}/g, sectionId)
                        .replace(/{sectionIndex}/g, index)
                        .replace(/{sectionNumber}/g, position + 1)
                        .replace(/{sectionTitle}/g, sectionTitle);

                    // Create a container element
                    const container = document.createElement('div');
                    container.innerHTML = content;

                    // Append to section content area
                    sectionContentArea.appendChild(container.firstElementChild);
                }
            });

            // Load existing content if any
            loadExistingContent();

            // Auto-expand the first section after a short delay
            setTimeout(() => {
                const firstSection = sectionContentArea.querySelector('.section-content-card');
                if (firstSection) {
                    const collapseElement = firstSection.querySelector('.collapse');
                    if (collapseElement && typeof bootstrap !== 'undefined') {
                        new bootstrap.Collapse(collapseElement, {
                            toggle: true
                        });
                    }
                }
            }, 300);
        } catch (error) {
            console.error('Error parsing section IDs:', error);
            sectionContentArea.innerHTML = `
<div class="alert alert-danger">
<div class="d-flex align-items-center">
<i class="mdi mdi-alert-circle me-3" style="font-size: 24px;"></i>
<div>
<h5 class="mb-1">Error Loading Sections</h5>
<p class="mb-0">There was a problem loading your course sections: ${error.message}</p>
</div>
</div>
</div>
`;
        }
    }

    /**
     * Load existing content for edit mode
     */
    function loadExistingContent() {
        const courseId = document.getElementById('course_id')?.value;
        if (!courseId) return;

        // Show loading indicator
        if (typeof createOverlay === 'function') {
            createOverlay("Loading course content...");
        }

        // Fetch existing content
        fetch(`../backend/courses/get_course_content.php?course_id=${courseId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Process sections with their content
                    if (data.sections) {
                        data.sections.forEach(section => {
                            const sectionId = section.section_id;
                            const sectionCard = document.querySelector(`.section-content-card[data-section-id="${sectionId}"]`);

                            if (sectionCard) {
                                // Add topics
                                if (section.topics && section.topics.length > 0) {
                                    section.topics.forEach(topic => {
                                        addExistingTopic(sectionId, topic);
                                    });
                                }

                                // Add quizzes
                                if (section.quizzes && section.quizzes.length > 0) {
                                    section.quizzes.forEach(quiz => {
                                        addExistingQuiz(sectionId, quiz);
                                    });
                                }

                                // Update section empty state
                                updateSectionEmptyState(sectionCard);
                            }
                        });
                    }

                    // Update the global counters display
                    updateContentStats();

                    // Success message
                    if (typeof showAlert === 'function') {
                        showAlert('success', 'Course content loaded successfully!');
                    }
                } else {
                    // Show warning but continue
                    console.warn('No content found or error loading content:', data.message);
                    if (typeof showAlert === 'function') {
                        showAlert('warning', 'No existing content found. Start adding topics and quizzes!');
                    }
                }
            })
            .catch(error => {
                console.error('Error loading content:', error);
                if (typeof showAlert === 'function') {
                    showAlert('danger', 'Error loading course content: ' + error.message);
                }
            })
            .finally(() => {
                // Remove loading overlay
                if (typeof removeOverlay === 'function') {
                    removeOverlay();
                }
            });
    }

    /**
     * Add an existing topic to a section
     */
    function addExistingTopic(sectionId, topic) {
        // Get the section card
        const sectionCard = document.querySelector(`.section-content-card[data-section-id="${sectionId}"]`);
        if (!sectionCard) return;

        // Get content container
        const contentContainer = sectionCard.querySelector('.content-container');
        if (!contentContainer) return;

        // Increment topic counter for this section
        if (!topicCounter[sectionId]) topicCounter[sectionId] = 0;
        topicCounter[sectionId]++;
        totalTopics++;

        // Get topic index
        const topicIndex = topicCounter[sectionId];

        // Create topic from template
        const topicElement = createTopicElement(sectionId, topicIndex);
        if (!topicElement) return;

        // Add to container
        contentContainer.appendChild(topicElement);

        // Fill in the topic data
        fillTopicData(topicElement, topic);

        // Update section stats
        updateSectionTopicCount(sectionCard);

        return topicElement;
    }

    /**
     * Fill a topic element with data
     */
    function fillTopicData(topicElement, topic) {
        if (!topicElement || !topic) return;

        // Set topic title
        const titleInput = topicElement.querySelector('.topic-title');
        if (titleInput && topic.title) {
            titleInput.value = topic.title;
        }

        // Set content type
        const contentTypeSelect = topicElement.querySelector('.content-type-select');
        if (contentTypeSelect && topic.content_type) {
            contentTypeSelect.value = topic.content_type;
            toggleContentTypeFields(contentTypeSelect);
        }

        // Set description
        const descriptionTextarea = topicElement.querySelector('textarea[name^="topic_descriptions_"]');
        if (descriptionTextarea && topic.description) {
            descriptionTextarea.value = topic.description;
        }

        // Set content type specific data
        if (topic.content_type === 'text') {
            const textContent = topicElement.querySelector('textarea[name^="topic_text_content_"]');
            if (textContent && topic.text_content) {
                textContent.value = topic.text_content;
            }
        } else if (topic.content_type === 'video') {
            const videoTypeSelect = topicElement.querySelector('.video-type-select');
            const videoUrl = topicElement.querySelector('.video-url');

            if (videoTypeSelect && topic.video_type) {
                videoTypeSelect.value = topic.video_type;
                toggleVideoUrlField(videoTypeSelect);
            }

            if (videoUrl && topic.video_url) {
                videoUrl.value = topic.video_url;
            }
        } else if (topic.content_type === 'link') {
            const linkUrl = topicElement.querySelector('input[name^="topic_external_links_"]');
            const linkDesc = topicElement.querySelector('textarea[name^="topic_link_descriptions_"]');

            if (linkUrl && topic.external_url) {
                linkUrl.value = topic.external_url;
            }

            if (linkDesc && topic.link_description) {
                linkDesc.value = topic.link_description;
            }
        }
    }

    /**
     * Add an existing quiz to a section
     */
    function addExistingQuiz(sectionId, quiz) {
        // Get the section card
        const sectionCard = document.querySelector(`.section-content-card[data-section-id="${sectionId}"]`);
        if (!sectionCard) return;

        // Get content container
        const contentContainer = sectionCard.querySelector('.content-container');
        if (!contentContainer) return;

        // Increment quiz counter
        if (!quizCounter[sectionId]) quizCounter[sectionId] = 0;
        quizCounter[sectionId]++;
        totalQuizzes++;

        // Get quiz index
        const quizIndex = quizCounter[sectionId];

        // Create quiz from template
        const quizElement = createQuizElement(sectionId, quizIndex);
        if (!quizElement) return;

        // Add to container
        contentContainer.appendChild(quizElement);

        // Fill in the quiz data
        const titleInput = quizElement.querySelector('.quiz-title');
        if (titleInput && quiz.title) {
            titleInput.value = quiz.title;
        }

        const passMarkInput = quizElement.querySelector('.pass-mark');
        if (passMarkInput && quiz.pass_mark) {
            passMarkInput.value = quiz.pass_mark;
        }

        const randomizeCheckbox = quizElement.querySelector('.randomize-questions');
        if (randomizeCheckbox && quiz.randomize_questions) {
            randomizeCheckbox.checked = quiz.randomize_questions === '1';
        }

        // Update section stats
        updateSectionQuizCount(sectionCard);

        return quizElement;
    }

    /**
     * Add a new topic to a section
     */
    function addTopicToSection(sectionId, sectionIndex) {
        // Get the section card
        const sectionCard = document.querySelector(`.section-content-card[data-section-id="${sectionId}"]`);
        if (!sectionCard) return;

        // Get content container
        const contentContainer = sectionCard.querySelector('.content-container');
        if (!contentContainer) return;

        // Increment topic counter for this section
        if (!topicCounter[sectionId]) topicCounter[sectionId] = 0;
        topicCounter[sectionId]++;
        totalTopics++;

        // Get topic index
        const topicIndex = topicCounter[sectionId];

        // Create topic from template
        const topicElement = createTopicElement(sectionId, topicIndex);
        if (!topicElement) return;

        // Add to container
        contentContainer.appendChild(topicElement);

        // Hide empty state if any content exists
        updateSectionEmptyState(sectionCard);

        // Update section stats
        updateSectionTopicCount(sectionCard);
        updateContentStats();

        // Highlight new topic
        topicElement.classList.add('highlight-new');
        setTimeout(() => {
            topicElement.classList.remove('highlight-new');
        }, 1500);

        // Focus the title field
        const titleInput = topicElement.querySelector('.topic-title');
        if (titleInput) {
            titleInput.focus();
        }

        // Scroll to the new topic
        topicElement.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });

        return topicElement;
    }

    /**
     * Create a topic element from template
     */
    function createTopicElement(sectionId, topicIndex) {
        // Get template
        const template = document.getElementById('topicTemplate');
        if (!template) return null;

        // Replace template placeholders
        let content = template.innerHTML
            .replace(/{sectionId}/g, sectionId)
            .replace(/{topicIndex}/g, topicIndex - 1)
            .replace(/{topicNumber}/g, topicIndex);

        // Create element
        const div = document.createElement('div');
        div.innerHTML = content;
        return div.firstElementChild;
    }

    /**
     * Add a new quiz to a section
     */
    function addQuizToSection(sectionId, sectionIndex) {
        // Get the section card
        const sectionCard = document.querySelector(`.section-content-card[data-section-id="${sectionId}"]`);
        if (!sectionCard) return;

        // Get content container
        const contentContainer = sectionCard.querySelector('.content-container');
        if (!contentContainer) return;

        // Increment quiz counter
        if (!quizCounter[sectionId]) quizCounter[sectionId] = 0;
        quizCounter[sectionId]++;
        totalQuizzes++;

        // Get quiz index
        const quizIndex = quizCounter[sectionId];

        // Create quiz from template
        const quizElement = createQuizElement(sectionId, quizIndex);
        if (!quizElement) return;

        // Add to container
        contentContainer.appendChild(quizElement);

        // Hide empty state if any content exists
        updateSectionEmptyState(sectionCard);

        // Update section stats
        updateSectionQuizCount(sectionCard);
        updateContentStats();

        // Highlight new quiz
        quizElement.classList.add('highlight-new');
        setTimeout(() => {
            quizElement.classList.remove('highlight-new');
        }, 1500);

        // Focus the title field
        const titleInput = quizElement.querySelector('.quiz-title');
        if (titleInput) {
            titleInput.focus();
        }

        // Scroll to the new quiz
        quizElement.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });

        return quizElement;
    }

    /**
     * Create a quiz element from template
     */
    function createQuizElement(sectionId, quizIndex) {
        // Get template
        const template = document.getElementById('quizTemplate');
        if (!template) return null;

        // Replace template placeholders
        let content = template.innerHTML
            .replace(/{sectionId}/g, sectionId)
            .replace(/{quizIndex}/g, quizIndex - 1)
            .replace(/{quizNumber}/g, quizIndex);

        // Create element
        const div = document.createElement('div');
        div.innerHTML = content;
        return div.firstElementChild;
    }

    /**
     * Remove a topic
     */
    function removeTopic(topicElement) {
        if (!topicElement) return;

        // Get section card
        const sectionCard = topicElement.closest('.section-content-card');
        if (!sectionCard) return;

        // Confirm removal
        if (confirm('Are you sure you want to remove this topic? This action cannot be undone.')) {
            // Add fade-out animation
            topicElement.style.transition = 'all 0.3s ease';
            topicElement.style.opacity = '0';
            topicElement.style.transform = 'translateX(10px)';

            // Remove after animation
            setTimeout(() => {
                topicElement.remove();

                // Update stats
                totalTopics--;

                // Update section counters
                const sectionId = sectionCard.getAttribute('data-section-id');
                if (sectionId && topicCounter[sectionId]) {
                    topicCounter[sectionId]--;
                }

                // Update UI
                updateSectionEmptyState(sectionCard);
                updateSectionTopicCount(sectionCard);
                updateContentStats();
            }, 300);
        }
    }

    /**
     * Remove a quiz
     */
    function removeQuiz(quizElement) {
        if (!quizElement) return;

        // Get section card
        const sectionCard = quizElement.closest('.section-content-card');
        if (!sectionCard) return;

        // Confirm removal
        if (confirm('Are you sure you want to remove this quiz? This action cannot be undone.')) {
            // Add fade-out animation
            quizElement.style.transition = 'all 0.3s ease';
            quizElement.style.opacity = '0';
            quizElement.style.transform = 'translateX(10px)';

            // Remove after animation
            setTimeout(() => {
                quizElement.remove();

                // Update stats
                totalQuizzes--;

                // Update section counters
                const sectionId = sectionCard.getAttribute('data-section-id');
                if (sectionId && quizCounter[sectionId]) {
                    quizCounter[sectionId]--;
                }

                // Update UI
                updateSectionEmptyState(sectionCard);
                updateSectionQuizCount(sectionCard);
                updateContentStats();
            }, 300);
        }
    }

    /**
     * Toggle content type fields
     */
    function toggleContentTypeFields(select) {
        if (!select) return;

        // Get the topic item
        const topicItem = select.closest('.topic-item');
        if (!topicItem) return;

        // Get the content type
        const contentType = select.value;

        // Get field containers
        const textFields = topicItem.querySelector('.text-content-fields');
        const videoFields = topicItem.querySelector('.video-content-fields');
        const linkFields = topicItem.querySelector('.link-content-fields');

        // Hide all fields first
        if (textFields) textFields.classList.add('d-none');
        if (videoFields) videoFields.classList.add('d-none');
        if (linkFields) linkFields.classList.add('d-none');

        // Show fields based on content type
        if (contentType === 'text' && textFields) {
            textFields.classList.remove('d-none');
        } else if (contentType === 'video' && videoFields) {
            videoFields.classList.remove('d-none');

            // Check if video type is set
            const videoTypeSelect = topicItem.querySelector('.video-type-select');
            if (videoTypeSelect) {
                toggleVideoUrlField(videoTypeSelect);
            }
        } else if (contentType === 'link' && linkFields) {
            linkFields.classList.remove('d-none');
        }

        // Remove validation errors
        validateField(select);
    }

    /**
     * Toggle video URL field based on video type
     */
    function toggleVideoUrlField(select) {
        if (!select) return;

        // Get the topic item
        const topicItem = select.closest('.topic-item');
        if (!topicItem) return;

        // Get the video type
        const videoType = select.value;

        // Get URL field
        const urlField = topicItem.querySelector('.video-url-field');
        if (!urlField) return;

        // Get help text elements
        const youtubeHelp = urlField.querySelector('.youtube-help');
        const externalHelp = urlField.querySelector('.external-help');

        // Show/hide based on type
        if (videoType === 'youtube' || videoType === 'external') {
            urlField.classList.remove('d-none');

            // Show appropriate help text
            if (youtubeHelp) youtubeHelp.classList.toggle('d-none', videoType !== 'youtube');
            if (externalHelp) externalHelp.classList.toggle('d-none', videoType !== 'external');
        } else {
            urlField.classList.add('d-none');
        }

        // Remove validation errors
        validateField(select);
    }

    /**
     * Update section empty state
     */
    function updateSectionEmptyState(sectionCard) {
        if (!sectionCard) return;

        // Get content container and empty state
        const contentContainer = sectionCard.querySelector('.content-container');
        const emptyState = sectionCard.querySelector('.section-empty-state');

        if (!contentContainer || !emptyState) return;

        // Check if any content exists
        const hasContent = contentContainer.children.length > 0;

        // Show/hide empty state
        emptyState.style.display = hasContent ? 'none' : 'block';
    }

    /**
     * Update section topic count badge
     */
    function updateSectionTopicCount(sectionCard) {
        if (!sectionCard) return;

        // Get the section ID
        const sectionId = sectionCard.getAttribute('data-section-id');
        if (!sectionId) return;

        // Get the count badge
        const countBadge = sectionCard.querySelector('.section-topic-count');
        if (!countBadge) return;

        // Get topic count
        const count = topicCounter[sectionId] || 0;

        // Update the badge
        countBadge.textContent = count === 1 ? '1 Topic' : `${count} Topics`;

        // Update badge color based on count
        if (count > 0) {
            countBadge.classList.remove('bg-light', 'text-dark');
            countBadge.classList.add('bg-success', 'text-white');
        } else {
            countBadge.classList.remove('bg-success', 'text-white');
            countBadge.classList.add('bg-light', 'text-dark');
        }
    }

    /**
     * Update section quiz count badge
     */
    function updateSectionQuizCount(sectionCard) {
        if (!sectionCard) return;

        // Get the section ID
        const sectionId = sectionCard.getAttribute('data-section-id');
        if (!sectionId) return;

        // Get the count badge
        const countBadge = sectionCard.querySelector('.section-quiz-count');
        if (!countBadge) return;

        // Get quiz count
        const count = quizCounter[sectionId] || 0;

        // Update the badge
        countBadge.textContent = count === 1 ? '1 Quiz' : `${count} Quizzes`;

        // Update badge color based on count
        if (count > 0) {
            countBadge.classList.remove('bg-light', 'text-dark');
            countBadge.classList.add('bg-danger', 'text-white');
        } else {
            countBadge.classList.remove('bg-danger', 'text-white');
            countBadge.classList.add('bg-light', 'text-dark');
        }
    }

    /**
     * Update global content stats
     */
    function updateContentStats() {
        const topicsCount = document.getElementById('topicsCount');
        const quizzesCount = document.getElementById('quizzesCount');

        if (topicsCount) {
            topicsCount.textContent = totalTopics === 1 ? '1 Topic' : `${totalTopics} Topics`;
        }

        if (quizzesCount) {
            quizzesCount.textContent = totalQuizzes === 1 ? '1 Quiz' : `${totalQuizzes} Quizzes`;
        }
    }

    /**
     * Validate a form field
     */
    function validateField(field) {
        if (!field) return true;

        // Skip non-required fields
        if (!field.required) return true;

        // Check for empty value
        const isValid = field.value.trim() !== '';

        // Update validation classes
        if (isValid) {
            field.classList.remove('is-invalid');
        } else {
            field.classList.add('is-invalid');
        }

        return isValid;
    }

    /**
     * Validate all fields in a topic or quiz
     */
    function validateContentItem(item) {
        if (!item) return {
            isValid: true,
            errors: []
        };

        const errors = [];
        let isValid = true;

        // Get all required fields
        const requiredFields = item.querySelectorAll('[required]');

        // Validate each field
        requiredFields.forEach(field => {
            const fieldValid = validateField(field);

            if (!fieldValid) {
                isValid = false;

                // Get field label or name
                let fieldName = '';
                const label = field.closest('.form-group')?.querySelector('.form-label');

                if (label) {
                    fieldName = label.textContent.replace('*', '').trim();
                } else {
                    fieldName = field.getAttribute('placeholder') || field.name;
                }

                errors.push(`${fieldName} is required`);
            }
        });

        // Validate content type specific fields
        if (item.classList.contains('topic-item')) {
            const contentType = item.querySelector('.content-type-select')?.value;

            if (contentType === 'video') {
                const videoType = item.querySelector('.video-type-select')?.value;
                const videoUrl = item.querySelector('.video-url');

                if (!videoType) {
                    isValid = false;
                    errors.push('Video source type is required');
                }

                if (videoUrl && videoType && !videoUrl.value.trim()) {
                    isValid = false;
                    videoUrl.classList.add('is-invalid');
                    errors.push('Video URL is required');
                }
            } else if (contentType === 'link') {
                const linkUrl = item.querySelector('input[name^="topic_external_links_"]');
                const linkDesc = item.querySelector('textarea[name^="topic_link_descriptions_"]');

                if (linkUrl && !linkUrl.value.trim()) {
                    isValid = false;
                    linkUrl.classList.add('is-invalid');
                    errors.push('External resource URL is required');
                }

                if (linkDesc && !linkDesc.value.trim()) {
                    isValid = false;
                    linkDesc.classList.add('is-invalid');
                    errors.push('Resource description is required');
                }
            } else if (contentType === 'text') {
                const textContent = item.querySelector('textarea[name^="topic_text_content_"]');

                if (textContent && !textContent.value.trim()) {
                    isValid = false;
                    textContent.classList.add('is-invalid');
                    errors.push('Text content is required');
                }
            }
        }

        // Highlight entire card if invalid
        if (!isValid) {
            item.classList.add('is-invalid-card');
        } else {
            item.classList.remove('is-invalid-card');
        }

        return {
            isValid,
            errors
        };
    }

    /**
     * Validate all content before saving
     */
    function validateAllContent() {
        let isValid = true;
        const errors = [];

        // Check if there is any content
        if (totalTopics === 0 && totalQuizzes === 0) {
            errors.push('Please add at least one topic or quiz to your course');
            isValid = false;
        }

        // Validate all topics and quizzes
        document.querySelectorAll('.topic-item, .quiz-item').forEach((item, index) => {
            // Get item type and number
            const isQuiz = item.classList.contains('quiz-item');
            const itemType = isQuiz ? 'Quiz' : 'Topic';
            const itemNumber = item.querySelector(isQuiz ? '.quiz-number' : '.topic-number')?.textContent || `${itemType} ${index + 1}`;

            // Get section title
            const sectionCard = item.closest('.section-content-card');
            const sectionTitle = sectionCard?.querySelector('.section-title')?.textContent || 'Unknown Section';

            // Validate the item
            const validation = validateContentItem(item);

            if (!validation.isValid) {
                isValid = false;

                // Add context to error messages
                validation.errors.forEach(error => {
                    errors.push(`${sectionTitle} - ${itemNumber}: ${error}`);
                });

                // Expand the section if collapsed
                const collapse = sectionCard?.querySelector('.collapse');
                if (collapse && typeof bootstrap !== 'undefined' && !collapse.classList.contains('show')) {
                    new bootstrap.Collapse(collapse, {
                        toggle: true
                    });
                }
            }
        });

        // Display errors if any
        if (!isValid) {
            const errorContainer = document.getElementById('contentValidationErrors');
            const errorList = document.getElementById('contentErrorList');

            if (errorContainer && errorList) {
                // Clear previous errors
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
                errorContainer.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        } else {
            // Hide error container
            const errorContainer = document.getElementById('contentValidationErrors');
            if (errorContainer) {
                errorContainer.style.display = 'none';
            }
        }

        return {
            isValid,
            errors
        };
    }

    /**
     * Enhanced function to save all content
     */
    function saveAllContent() {
        // Validate content first
        const validation = validateAllContent();
        if (!validation.isValid) {
            if (typeof showAlert === 'function') {
                showAlert('danger', 'Please fix the validation errors before saving.');
            }
            return false;
        }

        // Show loading overlay
        if (typeof createOverlay === 'function') {
            createOverlay("Saving course content...");
        }

        // Disable the Next button
        const nextButton = document.getElementById('nextButton');
        if (nextButton) {
            nextButton.disabled = true;
            if (nextButton.classList) {
                nextButton.classList.add('loading');
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

            if (nextButton) {
                nextButton.disabled = false;
                if (nextButton.classList) {
                    nextButton.classList.remove('loading');
                }
            }

            return false;
        }

        // Create form data
        const formData = new FormData();
        formData.append('course_id', courseId);

        // Add section IDs
        const sectionCards = document.querySelectorAll('.section-content-card');
        sectionCards.forEach(card => {
            const sectionId = card.getAttribute('data-section-id');
            if (sectionId) {
                formData.append('section_ids[]', sectionId);
            }
        });

        // Add topic and quiz data
        sectionCards.forEach(card => {
            const sectionId = card.getAttribute('data-section-id');
            if (!sectionId) return;

            // Add topics for this section
            const topics = card.querySelectorAll('.topic-item');
            topics.forEach((topic, index) => {
                addTopicToFormData(formData, topic, sectionId, index);
            });

            // Add quizzes for this section
            const quizzes = card.querySelectorAll('.quiz-item');
            quizzes.forEach((quiz, index) => {
                addQuizToFormData(formData, quiz, sectionId, index);
            });
        });

        // Send AJAX request
        fetch('../backend/courses/create_course_content.php', {
                method: 'POST',
                body: formData
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
                        showAlert('success', 'Course content saved successfully!');
                    }

                    // Update progress bar
                    if (typeof updateProgressBar === 'function') {
                        updateProgressBar(60); // 60% complete after third step
                    }

                    // Mark step as completed
                    const currentStep = document.getElementById('current_step');
                    const maxCompletedStep = document.getElementById('max_completed_step');

                    if (currentStep && maxCompletedStep) {
                        maxCompletedStep.value = Math.max(parseInt(maxCompletedStep.value), parseInt(currentStep.value));
                    }

                    // Move to next step
                    if (typeof navigateToStep === 'function') {
                        navigateToStep(4);
                    } else if (typeof moveToNextTab === 'function') {
                        moveToNextTab();
                    }

                    return true;
                } else {
                    // Show error
                    if (typeof showAlert === 'function') {
                        showAlert('danger', data.message || 'Error saving course content');
                    }

                    return false;
                }
            })
            .catch(error => {
                console.error('Error:', error);

                // Show error
                if (typeof showAlert === 'function') {
                    showAlert('danger', 'Error saving course content: ' + error.message);
                }

                return false;
            })
            .finally(() => {
                // Remove overlay
                if (typeof removeOverlay === 'function') {
                    removeOverlay();
                }

                // Enable next button
                if (nextButton) {
                    nextButton.disabled = false;
                    if (nextButton.classList) {
                        nextButton.classList.remove('loading');
                    }
                }
            });

        return true;
    }

    /**
     * Add topic data to FormData
     */
    function addTopicToFormData(formData, topic, sectionId, index) {
        if (!formData || !topic || !sectionId) return;

        // Get basic topic data
        const titleInput = topic.querySelector('.topic-title');
        const contentTypeSelect = topic.querySelector('.content-type-select');
        const descriptionTextarea = topic.querySelector(`textarea[name^="topic_descriptions_"]`);

        if (titleInput && contentTypeSelect) {
            formData.append(`topic_titles[${sectionId}][${index}]`, titleInput.value.trim());
            formData.append(`content_type[${sectionId}][${index}]`, contentTypeSelect.value);

            if (descriptionTextarea) {
                formData.append(`topic_descriptions[${sectionId}][${index}]`, descriptionTextarea.value.trim());
            }

            // Add content-specific data
            const contentType = contentTypeSelect.value;

            if (contentType === 'text') {
                const textContent = topic.querySelector('.text-content');
                if (textContent) {
                    formData.append(`topic_text_content[${sectionId}][${index}]`, textContent.value.trim());
                }
            } else if (contentType === 'video') {
                const videoType = topic.querySelector('.video-type-select');
                const videoUrl = topic.querySelector('.video-url');

                if (videoType) {
                    formData.append(`video_type[${sectionId}][${index}]`, videoType.value);
                }

                if (videoUrl) {
                    formData.append(`topic_video_links[${sectionId}][${index}]`, videoUrl.value.trim());
                }
            } else if (contentType === 'link') {
                const linkUrl = topic.querySelector('.link-url');
                const linkDesc = topic.querySelector('.link-description');

                if (linkUrl) {
                    formData.append(`topic_external_links[${sectionId}][${index}]`, linkUrl.value.trim());
                }

                if (linkDesc) {
                    formData.append(`topic_link_descriptions[${sectionId}][${index}]`, linkDesc.value.trim());
                }
            }
        }
    }

    /**
     * Add quiz data to FormData
     */
    function addQuizToFormData(formData, quiz, sectionId, index) {
        if (!formData || !quiz || !sectionId) return;

        // Get quiz data
        const titleInput = quiz.querySelector('.quiz-title');
        const randomCheck = quiz.querySelector('.randomize-questions');
        const passMarkInput = quiz.querySelector('.pass-mark');

        if (titleInput && passMarkInput) {
            formData.append(`quiz_titles[${sectionId}][${index}]`, titleInput.value.trim());
            formData.append(`quiz_random[${sectionId}][${index}]`, randomCheck && randomCheck.checked ? '1' : '0');
            formData.append(`quiz_pass_marks[${sectionId}][${index}]`, passMarkInput.value);
        }
    }
</script>