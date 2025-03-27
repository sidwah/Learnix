<?php
// Fetch current course learning outcomes if editing
$query = "SELECT * FROM course_learning_outcomes WHERE course_id = ? ORDER BY outcome_id ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$outcomes_result = $stmt->get_result();
$outcomes = [];
while ($outcome = $outcomes_result->fetch_assoc()) {
    $outcomes[] = $outcome;
}
$stmt->close();

// Fetch current course requirements if editing
$query = "SELECT * FROM course_requirements WHERE course_id = ? ORDER BY requirement_id ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$requirements_result = $stmt->get_result();
$requirements = [];
while ($requirement = $requirements_result->fetch_assoc()) {
    $requirements[] = $requirement;
}
$stmt->close();
?>

<div class="row">
    <!-- Learning Outcomes Section -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h4 class="card-title mb-0">Learning Outcomes</h4>
                <p class="card-subtitle mb-0 mt-1">What will students learn?</p>
            </div>
            <div class="card-body">
                <p class="text-muted">List the specific skills, knowledge, or abilities that students will gain by completing your course.</p>

                <div id="learningOutcomes" class="mb-3">
                    <!-- Learning outcomes will be added here dynamically -->
                    <?php if (empty($outcomes)): ?>
                        <div class="outcome-empty-state text-center py-4">
                            <div class="empty-state-icon mb-3">
                                <i class="mdi mdi-school-outline"></i>
                            </div>
                            <h5>No Learning Outcomes Added</h5>
                            <p class="text-muted">Add what students will learn in your course</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($outcomes as $index => $outcome): ?>
                            <div class="outcome-item mb-2">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <i class="mdi mdi-check-circle-outline"></i>
                                    </div>
                                    <input type="text" class="form-control outcome-input"
                                        value="<?php echo htmlspecialchars($outcome['outcome_text']); ?>"
                                        placeholder="e.g., Create responsive websites using HTML and CSS">
                                    <button type="button" class="btn btn-danger remove-outcome">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <button type="button" id="addOutcomeBtn" class="btn btn-outline-primary">
                    <i class="mdi mdi-plus-circle"></i> Add Learning Outcome
                </button>

                <div class="alert alert-info mt-3">
                    <strong>Tip:</strong> Good learning outcomes are specific and measurable. For example: "Build a responsive website using HTML, CSS, and JavaScript" is better than "Learn web development".
                </div>
            </div>
        </div>
    </div>

    <!-- Requirements Section -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-secondary text-white">
                <h4 class="card-title mb-0">Course Requirements</h4>
                <p class="card-subtitle mb-0 mt-1">What do students need to know?</p>
            </div>
            <div class="card-body">
                <p class="text-muted">List any prerequisites, prior knowledge, or tools that students will need before starting your course.</p>

                <div id="courseRequirements" class="mb-3">
                    <!-- Requirements will be added here dynamically -->
                    <?php if (empty($requirements)): ?>
                        <div class="requirement-empty-state text-center py-4">
                            <div class="empty-state-icon mb-3">
                                <i class="mdi mdi-notebook-check-outline"></i>
                            </div>
                            <h5>No Requirements Added</h5>
                            <p class="text-muted">Add what students need to know before starting</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($requirements as $index => $requirement): ?>
                            <div class="requirement-item mb-2">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <i class="mdi mdi-chevron-right"></i>
                                    </div>
                                    <input type="text" class="form-control requirement-input"
                                        value="<?php echo htmlspecialchars($requirement['requirement_text']); ?>"
                                        placeholder="e.g., Basic knowledge of HTML">
                                    <button type="button" class="btn btn-danger remove-requirement">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <button type="button" id="addRequirementBtn" class="btn btn-outline-secondary">
                    <i class="mdi mdi-plus-circle"></i> Add Requirement
                </button>

                <div class="alert alert-info mt-3">
                    <strong>Tip:</strong> Be honest about prerequisites, but don't set the bar too high. Indicate if your course is suitable for complete beginners.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Declare all variables at the top
    let autoSaveTimer = null;

    // Main document ready function
    $(document).ready(function() {
        // Setup Learning Outcomes
        function setupOutcomes() {
            // Add Outcome Button
            $('#addOutcomeBtn').click(function() {
                // Remove empty state if present
                $('.outcome-empty-state').remove();

                // Add new outcome input
                const outcomeItem = `
                    <div class="outcome-item mb-2">
                        <div class="input-group">
                            <div class="input-group-text">
                                <i class="mdi mdi-check-circle-outline"></i>
                            </div>
                            <input type="text" class="form-control outcome-input" 
                                   placeholder="e.g., Create responsive websites using HTML and CSS">
                            <button type="button" class="btn btn-danger remove-outcome">
                                <i class="mdi mdi-delete"></i>
                            </button>
                        </div>
                    </div>
                `;

                $('#learningOutcomes').append(outcomeItem);

                // Focus the new input
                $('#learningOutcomes .outcome-input:last').focus();

                // Trigger auto-save
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(saveOutcomesAndRequirements, 2000);
            });

            // Remove Outcome Button
            $(document).on('click', '.remove-outcome', function() {
                $(this).closest('.outcome-item').remove();

                // Show empty state if no outcomes left
                if ($('#learningOutcomes .outcome-item').length === 0) {
                    $('#learningOutcomes').html(`
                        <div class="outcome-empty-state text-center py-4">
                            <div class="empty-state-icon mb-3">
                                <i class="mdi mdi-school-outline"></i>
                            </div>
                            <h5>No Learning Outcomes Added</h5>
                            <p class="text-muted">Add what students will learn in your course</p>
                        </div>
                    `);
                }

                // Trigger auto-save
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(saveOutcomesAndRequirements, 2000);
            });
        }

        // Setup Course Requirements
        function setupRequirements() {
            // Add Requirement Button
            $('#addRequirementBtn').click(function() {
                // Remove empty state if present
                $('.requirement-empty-state').remove();

                // Add new requirement input
                const requirementItem = `
                    <div class="requirement-item mb-2">
                        <div class="input-group">
                            <div class="input-group-text">
                                <i class="mdi mdi-chevron-right"></i>
                            </div>
                            <input type="text" class="form-control requirement-input" 
                                   placeholder="e.g., Basic knowledge of HTML">
                            <button type="button" class="btn btn-danger remove-requirement">
                                <i class="mdi mdi-delete"></i>
                            </button>
                        </div>
                    </div>
                `;

                $('#courseRequirements').append(requirementItem);

                // Focus the new input
                $('#courseRequirements .requirement-input:last').focus();

                // Trigger auto-save
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(saveOutcomesAndRequirements, 2000);
            });

            // Remove Requirement Button
            $(document).on('click', '.remove-requirement', function() {
                $(this).closest('.requirement-item').remove();

                // Show empty state if no requirements left
                if ($('#courseRequirements .requirement-item').length === 0) {
                    $('#courseRequirements').html(`
                        <div class="requirement-empty-state text-center py-4">
                            <div class="empty-state-icon mb-3">
                                <i class="mdi mdi-notebook-check-outline"></i>
                            </div>
                            <h5>No Requirements Added</h5>
                            <p class="text-muted">Add what students need to know before starting</p>
                        </div>
                    `);
                }

                // Trigger auto-save
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(saveOutcomesAndRequirements, 2000);
            });
        }

        // Initialize the components
        setupOutcomes();
        setupRequirements();

        // Add event listeners for input changes
        $(document).on('input', '.outcome-input, .requirement-input', function() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(saveOutcomesAndRequirements, 2000);
        });
    });

    // Validate outcomes and requirements
    function validateOutcomesAndRequirements() {
        // Check if at least one valid outcome exists
        let hasValidOutcome = false;
        $('.outcome-input').each(function() {
            if ($(this).val().trim() !== '') {
                hasValidOutcome = true;
                return false; // Break the loop
            }
        });

        if (!hasValidOutcome) {
            showAlert('danger', 'Please add at least one learning outcome.');
            return false;
        }

        return true;
    }

    function saveOutcomesAndRequirements(callback) {
        // Show saving indicator
        $('#autoSaveIndicator').addClass('show');

        // Collect all outcomes
        const outcomes = [];
        $('.outcome-input').each(function() {
            const text = $(this).val().trim();
            if (text !== '') {
                outcomes.push(text);
            }
        });

        // Collect all requirements
        const requirements = [];
        $('.requirement-input').each(function() {
            const text = $(this).val().trim();
            if (text !== '') {
                requirements.push(text);
            }
        });

        // Prepare data in format PHP expects
        const data = {
            course_id: <?php echo $course_id; ?>,
            outcomes: outcomes,
            requirements: requirements
        };

        // Send AJAX request with proper headers
        $.ajax({
            url: '../ajax/courses/save_outcomes_requirements.php',
            type: 'POST',
            contentType: 'application/json', // Send as JSON
            data: JSON.stringify(data), // Encode the JS object
            dataType: 'json',
            success: function(response) {
                $('#autoSaveIndicator').removeClass('show');
                if (response.success) {
                    if (callback) callback();
                } else {
                    showAlert('danger', response.message || 'Error saving data');
                }
            },
            error: function(xhr) {
                $('#autoSaveIndicator').removeClass('show');
                let errorMsg = 'Network error occurred';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.status === 400) {
                    errorMsg = 'Invalid data sent to server';
                }
                showAlert('danger', errorMsg);
            }
        });
    }
</script>

<style>
    .empty-state-icon {
        font-size: 48px;
        color: #ccc;
    }

    .card-header .card-subtitle {
        opacity: 0.8;
        font-size: 0.9rem;
    }

    .outcome-item,
    .requirement-item {
        transition: all 0.3s ease;
    }

    .outcome-item:hover,
    .requirement-item:hover {
        transform: translateY(-2px);
    }
</style>