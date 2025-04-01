<?php
// Fetch existing sections for this course
$sections_query = "SELECT * FROM course_sections WHERE course_id = ? ORDER BY position ASC";
$stmt = $conn->prepare($sections_query);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$sections_result = $stmt->get_result();
$sections = [];
while ($section = $sections_result->fetch_assoc()) {
    $sections[] = $section;
}
$stmt->close();

// Count total topics in course
$topics_count_query = "SELECT COUNT(*) as total_topics FROM section_topics 
                       WHERE section_id IN (SELECT section_id FROM course_sections WHERE course_id = ?)";
$stmt = $conn->prepare($topics_count_query);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$topics_count_result = $stmt->get_result();
$topics_count = $topics_count_result->fetch_assoc()['total_topics'] ?? 0;
$stmt->close();

// Count total quizzes in course
$quizzes_count_query = "SELECT COUNT(*) as total_quizzes FROM section_quizzes 
                       WHERE section_id IN (SELECT section_id FROM course_sections WHERE course_id = ?)";
$stmt = $conn->prepare($quizzes_count_query);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$quizzes_count_result = $stmt->get_result();
$quizzes_count = $quizzes_count_result->fetch_assoc()['total_quizzes'] ?? 0;
$stmt->close();
?>

<div class="curriculum-builder">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">Course Curriculum</h5>
                            <p class="text-muted mb-0">Organize your course content into sections and topics</p>
                        </div>
                        <div>
                            <button type="button" id="addSectionBtn" class="btn btn-primary">
                                <i class="mdi mdi-plus-circle"></i> Add Section
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="curriculum-stats card bg-light">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h3 class="fw-bold"><?php echo count($sections); ?></h3>
                            <p class="text-muted mb-0">Sections</p>
                        </div>
                        <div class="col-md-3">
                            <h3 class="fw-bold"><?php echo $topics_count; ?></h3>
                            <p class="text-muted mb-0">Topics</p>
                        </div>
                        <div class="col-md-3">
                            <h3 class="fw-bold"><?php echo $quizzes_count; ?></h3>
                            <p class="text-muted mb-0">Quizzes</p>
                        </div>
                        <div class="d-flex justify-content-between gap-3 mt-3">
                            <div class="col-md-3">
                                <button id="validateCourseBtn" class="btn btn-outline-warning">
                                    <i class="mdi mdi-check-circle"></i> Validate Course
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button id="previewCourseBtn" class="btn btn-outline-primary">
                                    <i class="mdi mdi-eye"></i> Preview Course
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Empty state for no sections -->
    <?php if (empty($sections)): ?>
        <div id="emptyCurriculumState" class="row mb-4">
            <div class="col-12">
                <div class="card border border-dashed border-primary bg-light">
                    <div class="card-body text-center py-5">
                        <div class="empty-state-icon mb-3">
                            <i class="mdi mdi-notebook-outline" style="font-size: 64px; color: #3e7bfa;"></i>
                        </div>
                        <h4>No Sections Added Yet</h4>
                        <p class="text-muted">Start building your course by adding sections and topics.</p>
                        <button class="btn btn-primary mt-2 add-first-section-btn">
                            <i class="mdi mdi-plus-circle"></i> Add Your First Section
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Sections Container (sortable) -->
    <div id="sectionsContainer" class="mb-4">
        <?php foreach ($sections as $section): ?>
            <?php
            // Fetch topics for this section
            $topics_query = "SELECT * FROM section_topics WHERE section_id = ? ORDER BY position ASC";
            $stmt = $conn->prepare($topics_query);
            $stmt->bind_param("i", $section['section_id']);
            $stmt->execute();
            $topics_result = $stmt->get_result();
            $topics = [];
            while ($topic = $topics_result->fetch_assoc()) {
                $topics[] = $topic;
            }
            $stmt->close();

            // Fetch quizzes for this section
            $quizzes_query = "SELECT * FROM section_quizzes WHERE section_id = ?";
            $stmt = $conn->prepare($quizzes_query);
            $stmt->bind_param("i", $section['section_id']);
            $stmt->execute();
            $quizzes_result = $stmt->get_result();
            $quizzes = [];
            while ($quiz = $quizzes_result->fetch_assoc()) {
                $quizzes[] = $quiz;
            }
            $stmt->close();

            $has_content = !empty($topics) || !empty($quizzes);
            ?>
            <div class="section-item mb-4" data-section-id="<?php echo $section['section_id']; ?>">
                <div class="card">
                    <div class="card-header section-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="drag-handle me-2">
                                    <i class="mdi mdi-drag-horizontal handle-icon"></i>
                                </div>
                                <h5 class="section-title mb-0"><?php echo htmlspecialchars($section['title']); ?></h5>
                            </div>
                            <div class="section-actions">
                                <button type="button" class="btn btn-sm btn-outline-primary edit-section-btn"
                                    data-section-id="<?php echo $section['section_id']; ?>"
                                    data-section-title="<?php echo htmlspecialchars($section['title']); ?>">
                                    <i class="mdi mdi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger delete-section-btn"
                                    data-section-id="<?php echo $section['section_id']; ?>">
                                    <i class="mdi mdi-delete"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary section-collapse-btn">
                                    <i class="mdi mdi-chevron-up"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body section-content">
                        <!-- Topics Container (sortable) -->
                        <div class="topics-container" data-section-id="<?php echo $section['section_id']; ?>">
                            <?php if (!$has_content): ?>
                                <div class="empty-topics-message text-center py-4">
                                    <p class="text-muted mb-0">No content in this section yet. Add topics or quizzes.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($topics as $topic): ?>
                                    <div class="topic-item mb-2 card bg-light" data-topic-id="<?php echo $topic['topic_id']; ?>">
                                        <div class="card-body py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <div class="drag-handle me-2">
                                                        <i class="mdi mdi-drag-vertical handle-icon"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="topic-title mb-0">
                                                            <?php echo htmlspecialchars($topic['title']); ?>
                                                            <?php if ($topic['is_previewable']): ?>
                                                                <span class="badge bg-success ms-2">Preview</span>
                                                            <?php endif; ?>
                                                        </h6>
                                                    </div>
                                                </div>
                                                <div class="topic-actions">
                                                    <button type="button" class="btn btn-sm btn-outline-primary edit-topic-btn"
                                                        data-topic-id="<?php echo $topic['topic_id']; ?>"
                                                        data-topic-title="<?php echo htmlspecialchars($topic['title']); ?>"
                                                        data-topic-previewable="<?php echo $topic['is_previewable']; ?>">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-success me-1 content-topic-btn"
                                                        data-topic-id="<?php echo $topic['topic_id']; ?>">
                                                        <i class="mdi mdi-file-document-edit"></i> Content
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-topic-btn"
                                                        data-topic-id="<?php echo $topic['topic_id']; ?>">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <?php foreach ($quizzes as $quiz): ?>
                                    <div class="quiz-item mb-2 card bg-light border-primary" data-quiz-id="<?php echo $quiz['quiz_id']; ?>">
                                        <div class="card-body py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-2">
                                                        <i class="mdi mdi-help-circle-outline text-primary" style="font-size: 1.2rem;"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="quiz-title mb-0">
                                                            <?php echo htmlspecialchars($quiz['quiz_title']); ?>
                                                            <span class="badge bg-primary ms-2">Quiz</span>
                                                            <?php if (isset($quiz['pass_mark'])): ?>
                                                                <small class="text-muted ms-2">Pass: <?php echo $quiz['pass_mark']; ?>%</small>
                                                            <?php endif; ?>
                                                        </h6>
                                                    </div>
                                                </div>
                                                <div class="quiz-actions">
                                                    <button type="button" class="btn btn-sm btn-outline-primary edit-quiz-btn"
                                                        data-quiz-id="<?php echo $quiz['quiz_id']; ?>"
                                                        data-section-id="<?php echo $section['section_id']; ?>">
                                                        <i class="mdi mdi-pencil"></i> Edit
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-quiz-btn"
                                                        data-quiz-id="<?php echo $quiz['quiz_id']; ?>">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-outline-primary add-topic-btn"
                                data-section-id="<?php echo $section['section_id']; ?>">
                                <i class="mdi mdi-plus-circle"></i> Add Topic
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary add-quiz-btn ms-2"
                                data-section-id="<?php echo $section['section_id']; ?>">
                                <i class="mdi mdi-help-circle"></i> Add Quiz
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Add/Edit Section Modal -->
    <div class="modal fade" id="sectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sectionModalTitle">Add New Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="sectionForm">
                        <input type="hidden" id="sectionId" value="">
                        <div class="mb-3">
                            <label for="sectionTitle" class="form-label">Section Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="sectionTitle" required
                                placeholder="e.g., Introduction to the Course">
                            <div class="invalid-feedback">Please enter a section title.</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveSectionBtn">Save Section</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Topic Modal -->
    <div class="modal fade" id="topicModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="topicModalTitle">Add New Topic</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="topicForm">
                        <input type="hidden" id="topicId" value="">
                        <input type="hidden" id="topicSectionId" value="">
                        <div class="mb-3">
                            <label for="topicTitle" class="form-label">Topic Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="topicTitle" required
                                placeholder="e.g., Getting Started with HTML">
                            <div class="invalid-feedback">Please enter a topic title.</div>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="topicPreviewable">
                            <label class="form-check-label" for="topicPreviewable">
                                Make available as free preview
                            </label>
                            <div class="form-text">
                                Free preview topics are accessible to non-enrolled students to sample your course.
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveTopicBtn">Save Topic</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Editor Modal -->
    <div class="modal fade" id="contentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contentModalTitle">Edit Topic Content</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="contentEditorContainer">
                        <!-- Content editor will be loaded here -->
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading content editor...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmTitle">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="deleteConfirmMessage">
                    Are you sure you want to delete this item? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Course Validation Modal -->
    <div class="modal fade" id="validationModal" tabindex="-1" aria-labelledby="validationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="validationModalLabel">Course Validation Results</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="validationSummary" class="mb-4">
                        <!-- Validation status summary will be inserted here -->
                    </div>

                    <div id="validationDetails">
                        <!-- Validation details will be inserted here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveAsDraftBtn" style="display: none;">Save as Draft</button>
                    <button type="button" class="btn btn-success" id="submitForReviewBtn" style="display: none;">Publish</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // At the beginning of your script
    const courseId = <?php echo $course_id; ?>;
    $(document).ready(function() {
        // Initialize SortableJS for sections
        initializeSortable();

        // Initialize section collapse functionality
        initializeSectionCollapse();

        // Add Section Button
        $('#addSectionBtn, .add-first-section-btn').click(function() {
            // Reset form
            $('#sectionForm').removeClass('was-validated')[0].reset();
            $('#sectionId').val('');
            $('#sectionModalTitle').text('Add New Section');

            // Show modal
            $('#sectionModal').modal('show');
        });

        // Save Section Button
        $('#saveSectionBtn').click(function() {
            const sectionForm = document.getElementById('sectionForm');

            // Form validation
            if (!sectionForm.checkValidity()) {
                sectionForm.classList.add('was-validated');
                return;
            }

            // Get form data
            const sectionId = $('#sectionId').val();
            const sectionTitle = $('#sectionTitle').val();
            const isEdit = sectionId !== '';

            // Show loading overlay
            createOverlay('Saving section...');

            // AJAX request to save section
            $.ajax({
                url: isEdit ? '../ajax/curriculum/update_section.php' : '../ajax/curriculum/add_section.php',
                type: 'POST',
                data: {
                    course_id: <?php echo $course_id; ?>,
                    section_id: sectionId,
                    title: sectionTitle
                },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);

                        if (result.success) {
                            // Hide modal
                            $('#sectionModal').modal('hide');

                            // Remove empty state if adding first section
                            $('#emptyCurriculumState').remove();

                            if (isEdit) {
                                // Update section title
                                $(`.section-item[data-section-id="${sectionId}"] .section-title`).text(sectionTitle);

                                showAlert('success', 'Section updated successfully');
                            } else {
                                // Append new section
                                const newSection = `
                                    <div class="section-item mb-4" data-section-id="${result.section_id}">
                                        <div class="card">
                                            <div class="card-header section-header bg-light">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <div class="drag-handle me-2">
                                                            <i class="mdi mdi-drag-horizontal handle-icon"></i>
                                                        </div>
                                                        <h5 class="section-title mb-0">${sectionTitle}</h5>
                                                    </div>
                                                    <div class="section-actions">
                                                        <button type="button" class="btn btn-sm btn-outline-primary edit-section-btn" 
                                                                data-section-id="${result.section_id}"
                                                                data-section-title="${sectionTitle}">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger delete-section-btn" 
                                                                data-section-id="${result.section_id}">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary section-collapse-btn">
                                                            <i class="mdi mdi-chevron-up"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body section-content">
                                                <!-- Topics Container (sortable) -->
                                                <div class="topics-container" data-section-id="${result.section_id}">
                                                    <div class="empty-topics-message text-center py-4">
                                                        <p class="text-muted mb-0">No content in this section yet. Add topics or quizzes.</p>
                                                    </div>
                                                </div>
                                                <div class="mt-3">
                                                    <button type="button" class="btn btn-sm btn-outline-primary add-topic-btn" 
                                                            data-section-id="${result.section_id}">
                                                        <i class="mdi mdi-plus-circle"></i> Add Topic
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-primary add-quiz-btn ms-2" 
                                                            data-section-id="${result.section_id}">
                                                        <i class="mdi mdi-help-circle"></i> Add Quiz
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;

                                $('#sectionsContainer').append(newSection);

                                // Reinitialize sortable for the new section
                                initializeSortable();

                                // Initialize section collapse
                                initializeSectionCollapse();

                                // Update section count
                                updateCurriculumStats();

                                showAlert('success', 'Section added successfully');
                            }
                        } else {
                            showAlert('danger', 'Error: ' + result.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response', e);
                        showAlert('danger', 'Error processing server response');
                    }

                    // Hide loading overlay
                    removeOverlay();
                },
                error: function() {
                    showAlert('danger', 'Network error while saving section');
                    removeOverlay();
                }
            });
        });

        // Edit Section Button
        $(document).on('click', '.edit-section-btn', function() {
            const sectionId = $(this).data('section-id');
            const sectionTitle = $(this).data('section-title');

            // Set form values
            $('#sectionId').val(sectionId);
            $('#sectionTitle').val(sectionTitle);

            // Update modal title
            $('#sectionModalTitle').text('Edit Section');

            // Show modal
            $('#sectionModal').modal('show');
        });

        // Delete Section Button
        $(document).on('click', '.delete-section-btn', function() {
            const sectionId = $(this).data('section-id');

            // Set confirmation message
            $('#deleteConfirmMessage').html(`
                Are you sure you want to delete this section? <br>
                <strong class="text-danger">This will also delete all topics, quizzes, and content within this section.</strong><br>
                This action cannot be undone.
            `);

            // Setup confirmation button
            $('#confirmDeleteBtn').data('type', 'section').data('id', sectionId);

            // Show confirmation modal
            $('#deleteConfirmModal').modal('show');
        });

        // Add Topic Button
        $(document).on('click', '.add-topic-btn', function() {
            const sectionId = $(this).data('section-id');

            // Reset form
            $('#topicForm').removeClass('was-validated')[0].reset();
            $('#topicId').val('');
            $('#topicSectionId').val(sectionId);

            // Update modal title
            $('#topicModalTitle').text('Add New Topic');

            // Show modal
            $('#topicModal').modal('show');
        });

        // Save Topic Button
        $('#saveTopicBtn').click(function() {
            const topicForm = document.getElementById('topicForm');

            // Form validation
            if (!topicForm.checkValidity()) {
                topicForm.classList.add('was-validated');
                return;
            }

            // Get form data
            const topicId = $('#topicId').val();
            const sectionId = $('#topicSectionId').val();
            const topicTitle = $('#topicTitle').val();
            const isPreviewable = $('#topicPreviewable').is(':checked') ? 1 : 0;
            const isEdit = topicId !== '';

            // Show loading overlay
            createOverlay('Saving topic...');

            // AJAX request to save topic
            $.ajax({
                url: isEdit ? '../ajax/curriculum/update_topic.php' : '../ajax/curriculum/add_topic.php',
                type: 'POST',
                data: {
                    topic_id: topicId,
                    section_id: sectionId,
                    title: topicTitle,
                    is_previewable: isPreviewable
                },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);

                        if (result.success) {
                            // Hide modal
                            $('#topicModal').modal('hide');

                            // Remove empty topics message
                            $(`.topics-container[data-section-id="${sectionId}"] .empty-topics-message`).remove();

                            if (isEdit) {
                                // Update topic in the UI
                                const topicElement = $(`.topic-item[data-topic-id="${topicId}"]`);
                                topicElement.find('.topic-title').html(
                                    topicTitle + (isPreviewable ? ' <span class="badge bg-success ms-2">Preview</span>' : '')
                                );

                                // Update data attributes
                                topicElement.find('.edit-topic-btn')
                                    .data('topic-title', topicTitle)
                                    .data('topic-previewable', isPreviewable);

                                showAlert('success', 'Topic updated successfully');
                            } else {
                                // Create new topic element
                                const newTopic = `
                                    <div class="topic-item mb-2 card bg-light" data-topic-id="${result.topic_id}">
                                        <div class="card-body py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <div class="drag-handle me-2">
                                                        <i class="mdi mdi-drag-vertical handle-icon"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="topic-title mb-0">
                                                            ${topicTitle}
                                                            ${isPreviewable ? '<span class="badge bg-success ms-2">Preview</span>' : ''}
                                                        </h6>
                                                    </div>
                                                </div>
                                                <div class="topic-actions">
                                                    <button type="button" class="btn btn-sm btn-outline-primary edit-topic-btn" 
                                                            data-topic-id="${result.topic_id}"
                                                            data-topic-title="${topicTitle}"
                                                            data-topic-previewable="${isPreviewable}">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-success me-1 content-topic-btn"
                                                            data-topic-id="${result.topic_id}">
                                                        <i class="mdi mdi-file-document-edit"></i> Content
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-topic-btn" 
                                                            data-topic-id="${result.topic_id}">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
</div>
                                </div>
                                `;

                                // Add to the topics container
                                $(`.topics-container[data-section-id="${sectionId}"]`).append(newTopic);

                                // Reinitialize sortable
                                initializeSortable();

                                // Update topic count
                                updateCurriculumStats();

                                showAlert('success', 'Topic added successfully');
                            }
                        } else {
                            showAlert('danger', 'Error: ' + result.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response', e);
                        showAlert('danger', 'Error processing server response');
                    }

                    // Hide loading overlay
                    removeOverlay();
                },
                error: function() {
                    showAlert('danger', 'Network error while saving topic');
                    removeOverlay();
                }
            });
        });

        // Edit Topic Button
        $(document).on('click', '.edit-topic-btn', function() {
            const topicId = $(this).data('topic-id');
            const topicTitle = $(this).data('topic-title');
            const isPreviewable = $(this).data('topic-previewable');
            const sectionId = $(this).closest('.topics-container').data('section-id');

            // Set form values
            $('#topicId').val(topicId);
            $('#topicSectionId').val(sectionId);
            $('#topicTitle').val(topicTitle);
            $('#topicPreviewable').prop('checked', isPreviewable == 1);

            // Update modal title
            $('#topicModalTitle').text('Edit Topic');

            // Show modal
            $('#topicModal').modal('show');
        });

        // Delete Topic Button
        $(document).on('click', '.delete-topic-btn', function() {
            const topicId = $(this).data('topic-id');

            // Set confirmation message
            $('#deleteConfirmMessage').html(`
                Are you sure you want to delete this topic? <br>
                <strong class="text-danger">This will also delete all content associated with this topic.</strong><br>
                This action cannot be undone.
            `);

            // Setup confirmation button
            $('#confirmDeleteBtn').data('type', 'topic').data('id', topicId);

            // Show confirmation modal
            $('#deleteConfirmModal').modal('show');
        });

        // Edit Content Button
        $(document).on('click', '.content-topic-btn', function() {
            const topicId = $(this).data('topic-id');

            // Show modal and load content editor
            $('#contentModal').modal('show');

            // Load content editor
            $('#contentEditorContainer').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading content editor...</p>
                </div>
            `);

            // AJAX request to load content editor
            $.ajax({
                url: '../ajax/curriculum/load_content_editor.php',
                type: 'GET',
                data: {
                    topic_id: topicId
                },
                success: function(response) {
                    $('#contentEditorContainer').html(response);
                },
                error: function() {
                    $('#contentEditorContainer').html(`
                        <div class="alert alert-danger mb-0">
                            <h5 class="alert-heading">Error Loading Content Editor</h5>
                            <p class="mb-0">There was a problem loading the content editor. Please try again later.</p>
                        </div>
                    `);
                }
            });
        });

        // Add Quiz Button
        // Add Quiz Button
        $(document).on('click', '.add-quiz-btn', function() {
            const sectionId = $(this).data('section-id');

            // Show loading overlay
            createOverlay('Loading quiz editor...');

            // Redirect to quiz editor with a "new" parameter to always create a new quiz
            window.location.href = `quiz-builder.php?course_id=${courseId}&section_id=${sectionId}&new=1`;
        });

        // Edit Quiz Button
        $(document).on('click', '.edit-quiz-btn', function() {
            const quizId = $(this).data('quiz-id');
            const sectionId = $(this).data('section-id');

            // Show loading overlay
            createOverlay('Loading quiz editor...');

            // Redirect to quiz editor with quiz ID
            window.location.href = `quiz-builder.php?course_id=${courseId}&section_id=${sectionId}&quiz_id=${quizId}`;
        });

        // Delete Quiz Button
        $(document).on('click', '.delete-quiz-btn', function() {
            const quizId = $(this).data('quiz-id');

            // Set confirmation message
            $('#deleteConfirmMessage').html(`
                Are you sure you want to delete this quiz? <br>
                <strong class="text-danger">This will also delete all questions associated with this quiz.</strong><br>
                This action cannot be undone.
            `);

            // Setup confirmation button
            $('#confirmDeleteBtn').data('type', 'quiz').data('id', quizId);

            // Show confirmation modal
            $('#deleteConfirmModal').modal('show');
        });

        // Confirm Delete Button
        $('#confirmDeleteBtn').click(function() {
            const type = $(this).data('type');
            const id = $(this).data('id');

            // Show loading overlay
            createOverlay(`Deleting ${type}...`);

            // Determine the AJAX endpoint
            let endpoint = '../ajax/curriculum/delete_section.php';
            let dataKey = 'section_id';

            if (type === 'topic') {
                endpoint = '../ajax/curriculum/delete_topic.php';
                dataKey = 'topic_id';
            } else if (type === 'quiz') {
                endpoint = '../ajax/assessments/delete_quiz.php';
                dataKey = 'quiz_id';
            }

            // AJAX request to delete item
            // AJAX request to delete item
            $.ajax({
                url: endpoint,
                type: 'POST',
                data: {
                    [dataKey]: id
                },
                success: function(response) {
                    try {
                        // Log the raw response for debugging
                        console.log("Raw response:", response);

                        // Try to parse the response as JSON - first check if it's already an object
                        const result = typeof response === 'object' ? response : JSON.parse(response);

                        if (result.success) {
                            // Close modal
                            $('#deleteConfirmModal').modal('hide');

                            if (type === 'section') {
                                // Remove section from UI
                                $(`.section-item[data-section-id="${id}"]`).fadeOut(300, function() {
                                    $(this).remove();

                                    // If no sections left, show empty state
                                    if ($('.section-item').length === 0) {
                                        $('#sectionsContainer').before(`
                                <div id="emptyCurriculumState" class="row mb-4">
                                    <div class="col-12">
                                        <div class="card border border-dashed border-primary bg-light">
                                            <div class="card-body text-center py-5">
                                                <div class="empty-state-icon mb-3">
                                                    <i class="mdi mdi-notebook-outline" style="font-size: 64px; color: #3e7bfa;"></i>
                                                </div>
                                                <h4>No Sections Added Yet</h4>
                                                <p class="text-muted">Start building your course by adding sections and topics.</p>
                                                <button class="btn btn-primary mt-2 add-first-section-btn">
                                                    <i class="mdi mdi-plus-circle"></i> Add Your First Section
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `);
                                    }
                                });
                            } else if (type === 'topic') {
                                // Remove topic from UI
                                $(`.topic-item[data-topic-id="${id}"]`).fadeOut(300, function() {
                                    $(this).remove();

                                    // Check if this was the last content item
                                    const topicsContainer = $(this).closest('.topics-container');
                                    checkSectionEmptyState(topicsContainer);
                                });
                            } else if (type === 'quiz') {
                                // Remove quiz from UI
                                $(`.quiz-item[data-quiz-id="${id}"]`).fadeOut(300, function() {
                                    $(this).remove();

                                    // Check if this was the last content item
                                    const topicsContainer = $(this).closest('.topics-container');
                                    checkSectionEmptyState(topicsContainer);
                                });
                            }

                            // Update curriculum stats
                            updateCurriculumStats();

                            showAlert('success', `${type.charAt(0).toUpperCase() + type.slice(1)} deleted successfully`);
                        } else {
                            showAlert('danger', 'Error: ' + (result.message || 'Unknown error'));
                        }
                    } catch (e) {
                        console.error('Error parsing response', e);
                        console.log('Raw response was:', response);
                        showAlert('danger', 'Error processing server response');
                    }

                    // Hide loading overlay
                    removeOverlay();
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                    console.log("Response text:", xhr.responseText);
                    showAlert('danger', `Network error while deleting ${type}`);
                    removeOverlay();
                }
            });
        });

        // Check if a section is empty and update UI
        function checkSectionEmptyState(topicsContainer) {
            const hasTopics = topicsContainer.find('.topic-item').length > 0;
            const hasQuizzes = topicsContainer.find('.quiz-item').length > 0;

            if (!hasTopics && !hasQuizzes) {
                topicsContainer.html(`
                    <div class="empty-topics-message text-center py-4">
                        <p class="text-muted mb-0">No content in this section yet. Add topics or quizzes.</p>
                    </div>
                `);
            }
        }

        // Preview Course Button
        // Preview Course Button
        $('#previewCourseBtn').click(function() {
            window.open(`course-preview.php?course_id=${courseId}`, '_blank');
        });

        // Initialize SortableJS for sections and topics
        function initializeSortable() {
            // Initialize section sorting
            const sectionsContainer = document.getElementById('sectionsContainer');
            if (sectionsContainer) {
                if (sectionsContainer.sortableJs) {
                    sectionsContainer.sortableJs.destroy();
                }

                sectionsContainer.sortableJs = new Sortable(sectionsContainer, {
                    handle: '.handle-icon',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-drag',
                    onEnd: function(evt) {
                        updateSectionOrder();
                    }
                });
            }

            // Initialize topic sorting for each section
            $('.topics-container').each(function() {
                const container = this;

                if (container.sortableJs) {
                    container.sortableJs.destroy();
                }

                if ($(container).find('.topic-item').length > 0) {
                    container.sortableJs = new Sortable(container, {
                        handle: '.handle-icon',
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        dragClass: 'sortable-drag',
                        filter: '.quiz-item', // Prevent quizzes from being dragged
                        onEnd: function(evt) {
                            updateTopicOrder($(container).data('section-id'));
                        }
                    });
                }
            });
        }

        // Initialize section collapse functionality
        function initializeSectionCollapse() {
            $(document).on('click', '.section-collapse-btn', function() {
                const sectionContent = $(this).closest('.card').find('.section-content');
                const icon = $(this).find('i');

                if (sectionContent.is(':visible')) {
                    sectionContent.slideUp();
                    icon.removeClass('mdi-chevron-up').addClass('mdi-chevron-down');
                } else {
                    sectionContent.slideDown();
                    icon.removeClass('mdi-chevron-down').addClass('mdi-chevron-up');
                }
            });
        }

        // Update section order after drag and drop
        function updateSectionOrder() {
            const sections = [];

            // Collect all sections in their current order
            $('.section-item').each(function(index) {
                sections.push({
                    section_id: $(this).data('section-id'),
                    position: index + 1
                });
            });

            // Show saving indicator
            $('#autoSaveIndicator').addClass('show');

            // AJAX request to update section order
            $.ajax({
                url: '../ajax/curriculum/update_order.php',
                type: 'POST',
                data: {
                    course_id: courseId,
                    type: 'section',
                    order: JSON.stringify(sections)
                },
                success: function(response) {
                    // Hide saving indicator
                    $('#autoSaveIndicator').removeClass('show');

                    try {
                        const result = JSON.parse(response);

                        if (!result.success) {
                            showAlert('danger', 'Error updating section order: ' + result.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response', e);
                    }
                },
                error: function() {
                    // Hide saving indicator
                    $('#autoSaveIndicator').removeClass('show');

                    showAlert('danger', 'Network error while updating section order');
                }
            });
        }

        // Update topic order after drag and drop
        function updateTopicOrder(sectionId) {
            const topics = [];

            // Collect all topics in their current order
            $(`.topics-container[data-section-id="${sectionId}"] .topic-item`).each(function(index) {
                topics.push({
                    topic_id: $(this).data('topic-id'),
                    position: index + 1
                });
            });

            // Show saving indicator
            $('#autoSaveIndicator').addClass('show');

            // AJAX request to update topic order
            $.ajax({
                url: '../ajax/curriculum/update_order.php',
                type: 'POST',
                data: {
                    section_id: sectionId,
                    type: 'topic',
                    order: JSON.stringify(topics)
                },
                success: function(response) {
                    // Hide saving indicator
                    $('#autoSaveIndicator').removeClass('show');

                    try {
                        const result = JSON.parse(response);

                        if (!result.success) {
                            showAlert('danger', 'Error updating topic order: ' + result.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response', e);
                    }
                },
                error: function() {
                    // Hide saving indicator
                    $('#autoSaveIndicator').removeClass('show');

                    showAlert('danger', 'Network error while updating topic order');
                }
            });
        }

        // Update curriculum statistics
        function updateCurriculumStats() {
            const sectionCount = $('.section-item').length;
            const topicCount = $('.topic-item').length;
            const quizCount = $('.quiz-item').length;

            // Update the counts in the UI
            $('.curriculum-stats h3').eq(0).text(sectionCount);
            $('.curriculum-stats h3').eq(1).text(topicCount);
            $('.curriculum-stats h3').eq(2).text(quizCount);
        }
    });

    // Validate curriculum
    function validateCurriculum() {
        const sectionCount = $('.section-item').length;

        if (sectionCount === 0) {
            showAlert('danger', 'Please add at least one section to your course curriculum.');
            return false;
        }

        // Check if any section has no content (topics or quizzes)
        let emptySection = false;
        $('.section-item').each(function() {
            const sectionTitle = $(this).find('.section-title').text();
            const topicCount = $(this).find('.topic-item').length;
            const quizCount = $(this).find('.quiz-item').length;

            if (topicCount === 0 && quizCount === 0) {
                showAlert('danger', `Section "${sectionTitle}" has no content. Please add at least one topic or quiz to each section.`);
                emptySection = true;
                return false; // Break the loop
            }
        });

        if (emptySection) {
            return false;
        }

        return true;
    }

// Add this script to the end of your course-creator.php file
$(document).ready(function() {
    // Add click handler for validate course button
    $('#validateCourseBtn').click(function() {
        validateCourse();
    });

    // Validation process
    function validateCourse() {
        // Show loading overlay with sequential messages
        showValidationProgress();

        // AJAX request to validate course after showing all progress messages
        setTimeout(function() {
            $.ajax({
                url: '../ajax/courses/validate_course.php',
                type: 'POST',
                data: {
                    course_id: courseId
                },
                success: function(response) {
                    try {
                        // Remove overlay after validation completes
                        removeOverlay();
                        
                        const result = JSON.parse(response);
                        
                        // Display validation results
                        displayValidationResults(result);
                        
                        // Show the validation modal
                        $('#validationModal').modal('show');
                    } catch (e) {
                        console.error('Error parsing validation response', e);
                        removeOverlay();
                        showAlert('danger', 'Error processing validation response');
                    }
                },
                error: function() {
                    removeOverlay();
                    showAlert('danger', 'Network error during course validation');
                }
            });
        }, 8 * 1500); // Wait for all validation messages (8 messages * 1.5 seconds each)
    }

    // Function to show sequential validation messages
    function showValidationProgress() {
        const validationMessages = [
            "Initiating validation process...",
            "Checking basic course information...",
            "Validating course description...",
            "Checking learning outcomes and requirements...",
            "Validating curriculum structure...",
            "Checking for empty sections or topics...",
            "Validating assessment components...",
            "Finalizing validation report..."
        ];
        
        // Show initial overlay
        createOverlay(validationMessages[0]);
        
        // Change message every 1.5 seconds
        let messageIndex = 1;
        const interval = setInterval(function() {
            if (messageIndex < validationMessages.length) {
                // Update the overlay message
                updateOverlayMessage(validationMessages[messageIndex]);
                messageIndex++;
            } else {
                clearInterval(interval);
            }
        }, 1500);
    }

    // Helper function to update overlay message
    function updateOverlayMessage(message) {
        const overlay = document.getElementById('pageOverlay');
        if (overlay) {
            const messageElement = overlay.querySelector('.fw-semibold');
            if (messageElement) {
                messageElement.textContent = message;
            }
        }
    }

    // Display validation results in the modal
    function displayValidationResults(validationData) {
        // Clear previous results
        $('#validationSummary').empty();
        $('#validationDetails').empty();
        
        // Hide action buttons by default
        $('#saveAsDraftBtn').hide();
        $('#submitForReviewBtn').hide();
        
        // Create summary message based on validation status
        const passedAll = validationData.passed;
        let summaryHtml = '';
        
        if (passedAll) {
            summaryHtml = `
                <div class="alert alert-success">
                    <h5 class="alert-heading"><i class="mdi mdi-check-circle me-2"></i>Validation Successful</h5>
                    <p class="mb-0">Your course meets all the requirements and is ready for publication.</p>
                </div>
            `;
            
            // Show action buttons for successful validation
            $('#saveAsDraftBtn').show();
            $('#submitForReviewBtn').show();
        } else {
            summaryHtml = `
                <div class="alert alert-danger">
                    <h5 class="alert-heading"><i class="mdi mdi-alert-circle me-2"></i>Validation Failed</h5>
                    <p class="mb-0">Please address the issues below before publishing your course.</p>
                </div>
            `;
        }
        
        $('#validationSummary').html(summaryHtml);
        
        // Build details section
        let detailsHtml = '<div class="accordion" id="validationAccordion">';
        
        // Add each validation category
        const categories = [
            { key: 'basicInfo', title: 'Basic Information', icon: 'mdi-information-outline' },
            { key: 'description', title: 'Course Description', icon: 'mdi-text-box-outline' },
            { key: 'outcomes', title: 'Learning Outcomes & Requirements', icon: 'mdi-check-circle-outline' },
            { key: 'curriculum', title: 'Curriculum Structure', icon: 'mdi-book-open-variant' },
            { key: 'assessments', title: 'Assessments', icon: 'mdi-help-circle-outline' }
        ];
        
        categories.forEach((category, index) => {
            const categoryData = validationData[category.key];
            if (categoryData) {
                const passed = categoryData.passed;
                const issues = categoryData.issues || [];
                
                detailsHtml += `
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading${index}">
                            <button class="accordion-button ${passed ? 'collapsed' : ''}" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#collapse${index}" aria-expanded="${passed ? 'false' : 'true'}" aria-controls="collapse${index}">
                                <i class="mdi ${category.icon} me-2"></i>
                                ${category.title}
                                ${passed ? 
                                    '<span class="badge bg-success ms-2"><i class="mdi mdi-check"></i> Passed</span>' : 
                                    '<span class="badge bg-danger ms-2"><i class="mdi mdi-close"></i> Issues Found</span>'}
                            </button>
                        </h2>
                        <div id="collapse${index}" class="accordion-collapse collapse ${!passed ? 'show' : ''}" 
                             aria-labelledby="heading${index}" data-bs-parent="#validationAccordion">
                            <div class="accordion-body">
                `;
                
                if (passed) {
                    detailsHtml += `<p class="text-success mb-0"><i class="mdi mdi-check-circle"></i> All ${category.title.toLowerCase()} requirements are met.</p>`;
                } else {
                    detailsHtml += '<ul class="list-group list-group-flush">';
                    issues.forEach(issue => {
                        let fixLink = '';
                        
                        // Add direct links to fix issues where applicable
                        if (issue.fixUrl) {
                            fixLink = `<a href="${issue.fixUrl}" class="btn btn-sm btn-outline-primary ms-2" data-bs-dismiss="modal">Fix Now</a>`;
                        } else if (issue.step) {
                            fixLink = `<button type="button" class="btn btn-sm btn-outline-primary ms-2 goto-step-btn" data-step="${issue.step}" data-bs-dismiss="modal">Fix Now</button>`;
                        }
                        
                        detailsHtml += `
                            <li class="list-group-item list-group-item-danger d-flex justify-content-between align-items-center">
                                <div><i class="mdi mdi-alert-circle me-2"></i>${issue.message}</div>
                                ${fixLink}
                            </li>
                        `;
                    });
                    detailsHtml += '</ul>';
                }
                
                detailsHtml += `
                            </div>
                        </div>
                    </div>
                `;
            }
        });
        
        detailsHtml += '</div>';
        $('#validationDetails').html(detailsHtml);
        
        // Set up action buttons
        setupActionButtons(passedAll);
    }

    // Setup action buttons based on validation results
    function setupActionButtons(validationPassed) {
        if (validationPassed) {
            // Save as Draft button
            $('#saveAsDraftBtn').off('click').on('click', function() {
                saveCourseAsDraft();
            });
            
            // Submit for Review button
            $('#submitForReviewBtn').off('click').on('click', function() {
                submitCourseForReview();
            });
        }
        
        // Go to step button for fixing issues
        $('.goto-step-btn').off('click').on('click', function() {
    const stepToGo = parseInt($(this).data('step'));
    $('#validationModal').modal('hide');
    
    // Use the global showStep function
    if (typeof window.showStep === 'function') {
        window.showStep(stepToGo);
    } else {
        console.error('showStep function not available');
        // Fallback for when showStep isn't directly accessible
        // Update progress indicator manually
        $('.progress-step').each(function() {
            const step = parseInt($(this).data('step'));
            if (step < stepToGo) {
                $(this).removeClass('active').addClass('completed');
            } else if (step === stepToGo) {
                $(this).addClass('active').removeClass('completed');
            } else {
                $(this).removeClass('active completed');
            }
        });
        
        // Show the target step
        $('.wizard-step').removeClass('active');
        $(`#step${stepToGo}`).addClass('active');
        
        // Update navigation buttons
        if (stepToGo === 1) {
            $('#prevStep').hide();
        } else {
            $('#prevStep').show();
        }
        
        if (stepToGo === 6) { // Assuming 6 is your max step
            $('#nextStep').hide();
        } else {
            $('#nextStep').show();
            $('#nextStep').html('Next <i class="mdi mdi-arrow-right"></i>');
        }
    }
});
    }

    // Save course as draft
    function saveCourseAsDraft() {
        createOverlay("Saving course as draft...");
        
        $.ajax({
            url: '../ajax/courses/save_course_status.php',
            type: 'POST',
            data: {
                course_id: courseId,
                status: 'Draft'
            },
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    if (result.success) {
                        $('#validationModal').modal('hide');
                        removeOverlay();
                        showAlert('success', 'Course successfully saved as draft');
                    } else {
                        removeOverlay();
                        showAlert('danger', 'Error: ' + result.message);
                    }
                } catch (e) {
                    console.error('Error parsing response', e);
                    removeOverlay();
                    showAlert('danger', 'Error processing server response');
                }
            },
            error: function() {
                removeOverlay();
                showAlert('danger', 'Network error while saving course');
            }
        });
    }

    // Submit course for review
    function submitCourseForReview() {
        // First check instructor verification status
        createOverlay("Checking instructor verification status...");
        
        $.ajax({
            url: '../ajax/instructors/check_verification_status.php',
            type: 'POST',
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    
                    if (result.verified) {
                        // Instructor is verified, proceed with publishing directly
                        updateOverlayMessage("Publishing course...");
                        setTimeout(function() {
                            publishCourse();
                        }, 1500);
                    } else {
                        // Instructor is not verified, submit for review
                        updateOverlayMessage("Instructor verification required");
                        setTimeout(function() {
                            updateOverlayMessage("Submitting for review...");
                            setTimeout(function() {
                                submitForReview();
                            }, 1500);
                        }, 1500);
                    }
                } catch (e) {
                    console.error('Error parsing response', e);
                    removeOverlay();
                    showAlert('danger', 'Error processing verification status');
                }
            },
            error: function() {
                removeOverlay();
                showAlert('danger', 'Network error while checking verification status');
            }
        });
    }

    // Publish course (for verified instructors)
    function publishCourse() {
        $.ajax({
            url: '../ajax/courses/publish_course.php',
            type: 'POST',
            data: {
                course_id: courseId
            },
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    
                    if (result.success) {
                        $('#validationModal').modal('hide');
                        removeOverlay();
                        showAlert('success', 'Course published successfully! Students can now enroll.');
                        
                        // Optionally redirect to courses list or dashboard
                        setTimeout(function() {
                            window.location.href = 'index.php';
                        }, 2000);
                    } else {
                        removeOverlay();
                        showAlert('danger', 'Error: ' + result.message);
                    }
                } catch (e) {
                    console.error('Error parsing response', e);
                    removeOverlay();
                    showAlert('danger', 'Error processing server response');
                }
            },
            error: function() {
                removeOverlay();
                showAlert('danger', 'Network error while publishing course');
            }
        });
    }

    // Submit for review (for unverified instructors)
    function submitForReview() {
        $.ajax({
            url: '../ajax/courses/submit_for_review.php',
            type: 'POST',
            data: {
                course_id: courseId
            },
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    
                    if (result.success) {
                        $('#validationModal').modal('hide');
                        removeOverlay();
                        showAlert('info', 'Your course has been submitted for review. You will be notified once it is approved.');
                        
                        // Optionally redirect to courses list or dashboard
                        setTimeout(function() {
                            window.location.href = 'index.php';
                        }, 3000);
                    } else {
                        removeOverlay();
                        showAlert('danger', 'Error: ' + result.message);
                    }
                } catch (e) {
                    console.error('Error parsing response', e);
                    removeOverlay();
                    showAlert('danger', 'Error processing server response');
                }
            },
            error: function() {
                removeOverlay();
                showAlert('danger', 'Network error while submitting course for review');
            }
        });
    }
});</script>

<style>
    .handle-icon {
        cursor: move;
        color: #aaa;
    }

    .section-item:hover .handle-icon,
    .topic-item:hover .handle-icon {
        color: #666;
    }

    .sortable-ghost {
        opacity: 0.5;
        background: #f8f9fa;
    }

    .sortable-chosen {
        background: #f8f9fa;
    }

    .empty-state-icon {
        opacity: 0.7;
    }

    .card-header.section-header {
        cursor: pointer;
    }

    .topic-item,
    .quiz-item {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .topic-item:hover,
    .quiz-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    }

    .quiz-item {
        border-left: 4px solid #3e7bfa;
    }

    .border-dashed {
        border-style: dashed !important;
    }
</style>