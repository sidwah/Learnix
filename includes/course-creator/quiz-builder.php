<?php
// Get section and course IDs from URL parameters
$section_id = isset($_GET['section_id']) ? intval($_GET['section_id']) : 0;
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// Check if we're editing an existing quiz
$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
$editing_mode = $quiz_id > 0;

// If section ID is provided, fetch section details
$section_title = '';
if ($section_id > 0) {
    $section_query = "SELECT title FROM course_sections WHERE section_id = ?";
    $stmt = $conn->prepare($section_query);
    $stmt->bind_param("i", $section_id);
    $stmt->execute();
    $section_result = $stmt->get_result();
    if ($section_result->num_rows > 0) {
        $section_title = $section_result->fetch_assoc()['title'];
    }
    $stmt->close();
}

// If editing an existing quiz, fetch quiz details
$quiz_data = null;
if ($editing_mode) {
    $quiz_query = "SELECT * FROM section_quizzes WHERE quiz_id = ?";
    $stmt = $conn->prepare($quiz_query);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $quiz_result = $stmt->get_result();
    if ($quiz_result->num_rows > 0) {
        $quiz_data = $quiz_result->fetch_assoc();
        $section_id = $quiz_data['section_id'];
    }
    $stmt->close();

    // If we got quiz data but no section title yet, fetch section title
    if ($quiz_data && empty($section_title)) {
        $section_query = "SELECT title FROM course_sections WHERE section_id = ?";
        $stmt = $conn->prepare($section_query);
        $stmt->bind_param("i", $section_id);
        $stmt->execute();
        $section_result = $stmt->get_result();
        if ($section_result->num_rows > 0) {
            $section_title = $section_result->fetch_assoc()['title'];
        }
        $stmt->close();
    }
}
?>

<div class="quiz-builder">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="course-creator.php?step=6&course_id=<?php echo $course_id; ?>">Curriculum</a></li>
                                <li class="breadcrumb-item active"><?php echo $editing_mode ? 'Edit Quiz' : 'Create Quiz'; ?></li>
                            </ol>
                            <h4 class="mt-2">
                                <?php echo $editing_mode ? 'Edit Quiz' : 'Create Quiz'; ?>
                                <?php if (!empty($section_title)): ?>
                                    <small class="text-muted">in <?php echo htmlspecialchars($section_title); ?></small>
                                <?php endif; ?>
                            </h4>
                        </div>
                        <div>
                            <button type="button" id="previewQuizBtn" class="btn btn-outline-primary me-2">
                                <i class="mdi mdi-eye"></i> Preview Quiz
                            </button>
                            <button type="button" id="saveQuizBtn" class="btn btn-primary">
                                <i class="mdi mdi-content-save"></i> Save Quiz
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left column: Quiz settings -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quiz Settings</h5>
                </div>
                <div class="card-body">
                    <form id="quizSettingsForm">
                        <input type="hidden" id="quizId" value="<?php echo $quiz_id; ?>">
                        <input type="hidden" id="sectionId" value="<?php echo $section_id; ?>">

                        <div class="mb-3">
                            <label for="quizTitle" class="form-label">Quiz Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="quizTitle" name="quizTitle" required
                                placeholder="e.g., Module 1 Assessment"
                                value="<?php echo $editing_mode ? htmlspecialchars($quiz_data['quiz_title']) : ''; ?>">
                            <div class="invalid-feedback">Please enter a quiz title.</div>
                        </div>

                        <div class="mb-3">
                            <label for="quizInstructions" class="form-label">Instructions</label>
                            <textarea class="form-control" id="quizInstructions" name="quizInstructions" rows="3"
                                placeholder="Instructions for students"><?php echo $editing_mode ? htmlspecialchars($quiz_data['instruction']) : ''; ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="timeLimit" class="form-label">Time Limit (minutes)</label>
                            <input type="number" class="form-control" id="timeLimit" name="timeLimit" min="0"
                                placeholder="Leave blank for no time limit"
                                value="<?php echo $editing_mode && $quiz_data['time_limit'] ? $quiz_data['time_limit'] : ''; ?>">
                            <div class="form-text">Set to 0 or leave blank for unlimited time.</div>
                        </div>

                        <div class="mb-3">
                            <label for="passMark" class="form-label">Pass Mark (%)</label>
                            <input type="number" class="form-control" id="passMark" name="passMark" min="0" max="100"
                                value="<?php echo $editing_mode ? $quiz_data['pass_mark'] : '70'; ?>">
                        </div>

                        <div class="mb-3">
                            <label for="attemptsAllowed" class="form-label">Attempts Allowed</label>
                            <input type="number" class="form-control" id="attemptsAllowed" name="attemptsAllowed" min="1"
                                value="<?php echo $editing_mode ? $quiz_data['attempts_allowed'] : '1'; ?>">
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="randomizeQuestions" name="randomizeQuestions"
                                <?php echo $editing_mode && $quiz_data['randomize_questions'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="randomizeQuestions">Randomize Questions</label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="showCorrectAnswers" name="showCorrectAnswers"
                                <?php echo $editing_mode && $quiz_data['show_correct_answers'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="showCorrectAnswers">Show Correct Answers After Submission</label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="shuffleAnswers" name="shuffleAnswers"
                                <?php echo $editing_mode && $quiz_data['shuffle_answers'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="shuffleAnswers">Shuffle Answer Options</label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="isRequired" name="isRequired"
                                <?php echo (!$editing_mode || $quiz_data['is_required']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="isRequired">Required to Complete</label>
                            <div class="form-text">Students must pass this quiz to complete the course section.</div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right column: Questions management -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Questions</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-primary" id="addQuestionBtn">
                            <i class="mdi mdi-plus-circle"></i> Add Question
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Empty state for no questions -->
                    <div id="emptyQuestionsState" class="text-center py-5" <?php echo $editing_mode ? 'style="display:none;"' : ''; ?>>
                        <div class="empty-state-icon mb-3">
                            <i class="mdi mdi-help-circle-outline" style="font-size: 64px; color: #3e7bfa;"></i>
                        </div>
                        <h5>No Questions Added Yet</h5>
                        <p class="text-muted">Start building your quiz by adding questions.</p>
                        <button class="btn btn-primary mt-2 add-first-question-btn">
                            <i class="mdi mdi-plus-circle"></i> Add Your First Question
                        </button>
                    </div>

                    <!-- Questions container -->
                    <div id="questionsContainer" <?php echo !$editing_mode ? 'style="display:none;"' : ''; ?>>
                        <?php
                        if ($editing_mode) {
                            // Fetch questions for this quiz
                            $questions_query = "SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY question_order ASC";
                            $stmt = $conn->prepare($questions_query);
                            $stmt->bind_param("i", $quiz_id);
                            $stmt->execute();
                            $questions_result = $stmt->get_result();

                            if ($questions_result->num_rows > 0) {
                                $question_number = 1;
                                while ($question = $questions_result->fetch_assoc()) {
                                    // Get answer options
                                    $answers_query = "SELECT * FROM quiz_answers WHERE question_id = ? ORDER BY answer_id ASC";
                                    $stmt2 = $conn->prepare($answers_query);
                                    $stmt2->bind_param("i", $question['question_id']);
                                    $stmt2->execute();
                                    $answers_result = $stmt2->get_result();

                                    $answers = [];
                                    while ($answer = $answers_result->fetch_assoc()) {
                                        $answers[] = $answer;
                                    }
                                    $stmt2->close();

                                    // Determine question type icon
                                    $type_icon = 'mdi-format-list-bulleted';
                                    $type_text = 'Multiple Choice';

                                    if ($question['question_type'] == 'True/False') {
                                        $type_icon = 'mdi-toggle-switch-outline';
                                        $type_text = 'True/False';
                                    }

                                    // Output question card
                                    echo '<div class="card mb-3 question-card" data-question-id="' . $question['question_id'] . '">';
                                    echo '<div class="card-header bg-light">';
                                    echo '<div class="d-flex justify-content-between align-items-center">';
                                    echo '<div class="d-flex align-items-center">';
                                    echo '<span class="question-number me-2">' . $question_number . '.</span>';
                                    echo '<span class="question-type me-2"><i class="mdi ' . $type_icon . '"></i> ' . $type_text . '</span>';
                                    echo '<h6 class="mb-0 question-title">' . htmlspecialchars($question['question_text']) . '</h6>';
                                    echo '</div>';
                                    echo '<div class="question-actions">';
                                    echo '<button type="button" class="btn btn-sm btn-outline-primary edit-question-btn">';
                                    echo '<i class="mdi mdi-pencil"></i></button>';
                                    echo '<button type="button" class="btn btn-sm btn-outline-danger ms-2 delete-question-btn">';
                                    echo '<i class="mdi mdi-delete"></i></button>';
                                    echo '</div>';
                                    echo '</div>';
                                    echo '</div>';
                                    echo '<div class="card-body">';
                                    echo '<div class="question-content mb-2">' . htmlspecialchars($question['question_text']) . '</div>';
                                    echo '<div class="answer-options">';

                                    foreach ($answers as $answer) {
                                        $is_correct = $answer['is_correct'] == 1 ? 'text-success correct-answer' : '';
                                        echo '<div class="answer-option ' . $is_correct . '">';
                                        echo '<i class="mdi ' . ($answer['is_correct'] == 1 ? 'mdi-check-circle text-success' : 'mdi-circle-outline') . ' me-2"></i>';
                                        echo htmlspecialchars($answer['answer_text']);
                                        echo '</div>';
                                    }

                                    echo '</div>';
                                    echo '</div>';
                                    echo '</div>';

                                    $question_number++;
                                }
                            }
                            $stmt->close();
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Question Modal -->
<div class="modal fade" id="questionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="questionModalTitle">Add New Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="questionForm">
                    <input type="hidden" id="questionId" value="">

                    <div class="mb-3">
                        <label for="questionType" class="form-label">Question Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="questionType" required>
                            <option value="Multiple Choice">Multiple Choice</option>
                            <option value="True/False">True/False</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="questionText" class="form-label">Question <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="questionText" rows="3" required></textarea>
                        <div class="invalid-feedback">Please enter your question.</div>
                    </div>

                    <div class="mb-3">
                        <label for="questionPoints" class="form-label">Points</label>
                        <input type="number" class="form-control" id="questionPoints" min="1" value="1">
                    </div>

                    <div id="multipleChoiceOptions">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label">Answer Options <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addAnswerBtn">
                                <i class="mdi mdi-plus-circle"></i> Add Option
                            </button>
                        </div>

                        <div id="answerOptionsContainer">
                            <!-- Answer options will be added here -->
                            <div class="answer-option-row mb-2">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <input class="form-check-input mt-0 is-correct-radio" type="radio" name="correctAnswer" checked>
                                    </div>
                                    <input type="text" class="form-control answer-text" placeholder="Enter answer option" required>
                                    <button type="button" class="btn btn-outline-danger remove-answer-btn">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="answer-option-row mb-2">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <input class="form-check-input mt-0 is-correct-radio" type="radio" name="correctAnswer">
                                    </div>
                                    <input type="text" class="form-control answer-text" placeholder="Enter answer option" required>
                                    <button type="button" class="btn btn-outline-danger remove-answer-btn">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="form-text mb-3">Select the radio button next to the correct answer.</div>
                    </div>

                    <div id="trueFalseOptions" style="display:none;">
                        <label class="form-label">Correct Answer <span class="text-danger">*</span></label>
                        <div class="mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="trueFalseCorrect" id="trueFalseTrue" value="true" checked>
                                <label class="form-check-label" for="trueFalseTrue">True</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="trueFalseCorrect" id="trueFalseFalse" value="false">
                                <label class="form-check-label" for="trueFalseFalse">False</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="explanationText" class="form-label">Explanation (Optional)</label>
                        <textarea class="form-control" id="explanationText" rows="2"
                            placeholder="Provide an explanation for the correct answer"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveQuestionBtn">Save Question</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Question type change handler
        $('#questionType').change(function() {
            const questionType = $(this).val();

            if (questionType === 'Multiple Choice') {
                $('#multipleChoiceOptions').show();
                $('#trueFalseOptions').hide();
            } else if (questionType === 'True/False') {
                $('#multipleChoiceOptions').hide();
                $('#trueFalseOptions').show();
            }
        });

        // Add answer option button
        $('#addAnswerBtn').click(function() {
            if ($('.answer-option-row').length >= 10) {
                showAlert('warning', 'Maximum 10 answer options allowed.');
                return;
            }

            const newOption = `
                <div class="answer-option-row mb-2">
                    <div class="input-group">
                        <div class="input-group-text">
                            <input class="form-check-input mt-0 is-correct-radio" type="radio" name="correctAnswer">
                        </div>
                        <input type="text" class="form-control answer-text" placeholder="Enter answer option" required>
                        <button type="button" class="btn btn-outline-danger remove-answer-btn">
                            <i class="mdi mdi-delete"></i>
                        </button>
                    </div>
                </div>
            `;

            $('#answerOptionsContainer').append(newOption);
        });

        // Remove answer option button
        $(document).on('click', '.remove-answer-btn', function() {
            if ($('.answer-option-row').length <= 2) {
                showAlert('warning', 'At least 2 answer options are required.');
                return;
            }

            $(this).closest('.answer-option-row').remove();

            // Ensure at least one option is selected as correct
            if ($('.is-correct-radio:checked').length === 0) {
                $('.is-correct-radio').first().prop('checked', true);
            }
        });

        // Add question button
        $('#addQuestionBtn, .add-first-question-btn').click(function() {
            // Reset form
            resetQuestionForm();

            // Set modal title
            $('#questionModalTitle').text('Add New Question');

            // Show modal
            $('#questionModal').modal('show');
        });

        // Save question button
        $('#saveQuestionBtn').click(function() {
            const questionForm = document.getElementById('questionForm');

            // Form validation
            if (!questionForm.checkValidity()) {
                questionForm.classList.add('was-validated');
                return;
            }

            // Get form data
            const questionId = $('#questionId').val();
            const questionType = $('#questionType').val();
            const questionText = $('#questionText').val();
            const questionPoints = $('#questionPoints').val();
            const explanation = $('#explanationText').val();
            const isEdit = questionId !== '';

            // Prepare answers data
            let answers = [];

            if (questionType === 'Multiple Choice') {
                $('.answer-option-row').each(function(index) {
                    const isCorrect = $(this).find('.is-correct-radio').is(':checked') ? 1 : 0;
                    const answerText = $(this).find('.answer-text').val();

                    if (answerText.trim() !== '') {
                        answers.push({
                            text: answerText,
                            is_correct: isCorrect
                        });
                    }
                });

                // Validate answers
                if (answers.length < 2) {
                    showAlert('danger', 'Please add at least 2 answer options.');
                    return;
                }

                // Check if at least one answer is marked as correct
                const hasCorrectAnswer = answers.some(answer => answer.is_correct === 1);
                if (!hasCorrectAnswer) {
                    showAlert('danger', 'Please select a correct answer.');
                    return;
                }
            } else if (questionType === 'True/False') {
                const trueIsCorrect = $('#trueFalseTrue').is(':checked') ? 1 : 0;
                const falseIsCorrect = $('#trueFalseFalse').is(':checked') ? 1 : 0;

                answers = [{
                        text: 'True',
                        is_correct: trueIsCorrect
                    },
                    {
                        text: 'False',
                        is_correct: falseIsCorrect
                    }
                ];
            }

            // Show loading overlay
            createOverlay('Saving question...');

            // AJAX request to save question
            $.ajax({
                url: isEdit ? '../ajax/assessments/update_question.php' : '../ajax/assessments/add_question.php',
                type: 'POST',
                data: {
                    quiz_id: $('#quizId').val(),
                    question_id: questionId,
                    question_text: questionText,
                    question_type: questionType,
                    points: questionPoints,
                    explanation: explanation,
                    answers: JSON.stringify(answers)
                },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);

                        if (result.success) {
                            // Hide modal
                            $('#questionModal').modal('hide');

                            // Remove empty state if adding first question
                            $('#emptyQuestionsState').hide();
                            $('#questionsContainer').show();

                            // Determine question type icon
                            const typeIcon = questionType === 'Multiple Choice' ? 'mdi-format-list-bulleted' : 'mdi-toggle-switch-outline';

                            if (isEdit) {
                                // Update question in UI
                                const questionCard = $(`.question-card[data-question-id="${questionId}"]`);
                                questionCard.find('.question-title').text(questionText);
                                questionCard.find('.question-content').text(questionText);
                                questionCard.find('.question-type').html(`<i class="mdi ${typeIcon}"></i> ${questionType}`);

                                // Update answer options
                                const answerContainer = questionCard.find('.answer-options');
                                answerContainer.empty();

                                answers.forEach(function(answer) {
                                    const isCorrect = answer.is_correct === 1 ? 'text-success correct-answer' : '';
                                    const icon = answer.is_correct === 1 ? 'mdi-check-circle text-success' : 'mdi-circle-outline';

                                    answerContainer.append(`
                                        <div class="answer-option ${isCorrect}">
                                            <i class="mdi ${icon} me-2"></i>
                                            ${answer.text}
                                        </div>
                                    `);
                                });

                                showAlert('success', 'Question updated successfully');
                            } else {
                                // Create new question card
                                const questionNumber = $('.question-card').length + 1;
                                const newQuestion = `
                                    <div class="card mb-3 question-card" data-question-id="${result.question_id}">
                                        <div class="card-header bg-light">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <span class="question-number me-2">${questionNumber}.</span>
                                                    <span class="question-type me-2"><i class="mdi ${typeIcon}"></i> ${questionType}</span>
                                                    <h6 class="mb-0 question-title">${questionText}</h6>
                                                </div>
                                                <div class="question-actions">
                                                    <button type="button" class="btn btn-sm btn-outline-primary edit-question-btn">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger ms-2 delete-question-btn">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="question-content mb-2">${questionText}</div>
                                            <div class="answer-options">
                                `;

                                answers.forEach(function(answer) {
                                    const isCorrect = answer.is_correct === 1 ? 'text-success correct-answer' : '';
                                    const icon = answer.is_correct === 1 ? 'mdi-check-circle text-success' : 'mdi-circle-outline';

                                    newQuestion += `
                                        <div class="answer-option ${isCorrect}">
                                            <i class="mdi ${icon} me-2"></i>
                                            ${answer.text}
                                        </div>
                                    `;
                                });

                                newQuestion += `
                                            </div>
                                        </div>
                                    </div>
                                `;

                                $('#questionsContainer').append(newQuestion);

                                showAlert('success', 'Question added successfully');
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
                    showAlert('danger', 'Network error while saving question');
                    removeOverlay();
                }
            });
        });

        // Edit question button
        $(document).on('click', '.edit-question-btn', function() {
            const questionCard = $(this).closest('.question-card');
            const questionId = questionCard.data('question-id');

            // Show loading overlay
            createOverlay('Loading question data...');

            // AJAX request to get question data
            $.ajax({
                url: '../ajax/assessments/get_question.php',
                type: 'GET',
                data: {
                    question_id: questionId
                },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);

                        if (result.success) {
                            const questionData = result.data;

                            // Reset and fill form
                            resetQuestionForm();

                            // Set question ID and type
                            $('#questionId').val(questionId);
                            $('#questionType').val(questionData.question_type).trigger('change');

                            // Set other fields
                            $('#questionText').val(questionData.question_text);
                            $('#questionPoints').val(questionData.points);
                            $('#explanationText').val(questionData.explanation || '');

                            // Handle answer options based on question type
                            if (questionData.question_type === 'Multiple Choice') {
                                // Clear default answer options
                                $('#answerOptionsContainer').empty();

                                // Add each answer option
                                questionData.answers.forEach(function(answer) {
                                    const newOption = `
                                        <div class="answer-option-row mb-2">
                                            <div class="input-group">
                                               <div class="input-group-text">
                                                   <input class="form-check-input mt-0 is-correct-radio" type="radio" name="correctAnswer" ${answer.is_correct == 1 ? 'checked' : ''}>
                                               </div>
                                               <input type="text" class="form-control answer-text" placeholder="Enter answer option" value="${answer.answer_text}" required>
                                               <button type="button" class="btn btn-outline-danger remove-answer-btn">
                                                   <i class="mdi mdi-delete"></i>
                                               </button>
                                           </div>
                                       </div>
                                   `;

                                    $('#answerOptionsContainer').append(newOption);
                                });
                            } else if (questionData.question_type === 'True/False') {
                                // Set true/false radio buttons
                                questionData.answers.forEach(function(answer) {
                                    if (answer.answer_text === 'True' && answer.is_correct == 1) {
                                        $('#trueFalseTrue').prop('checked', true);
                                    } else if (answer.answer_text === 'False' && answer.is_correct == 1) {
                                        $('#trueFalseFalse').prop('checked', true);
                                    }
                                });
                            }

                            // Update modal title
                            $('#questionModalTitle').text('Edit Question');

                            // Show modal
                            $('#questionModal').modal('show');
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
                    showAlert('danger', 'Network error while loading question data');
                    removeOverlay();
                }
            });
        });

        // Delete question button
        $(document).on('click', '.delete-question-btn', function() {
            const questionCard = $(this).closest('.question-card');
            const questionId = questionCard.data('question-id');

            if (confirm('Are you sure you want to delete this question? This action cannot be undone.')) {
                // Show loading overlay
                createOverlay('Deleting question...');

                // AJAX request to delete question
                $.ajax({
                    url: '../ajax/assessments/delete_question.php',
                    type: 'POST',
                    data: {
                        question_id: questionId
                    },
                    success: function(response) {
                        try {
                            const result = JSON.parse(response);

                            if (result.success) {
                                // Remove question card from UI
                                questionCard.fadeOut(300, function() {
                                    $(this).remove();

                                    // Update question numbers
                                    $('.question-card').each(function(index) {
                                        $(this).find('.question-number').text((index + 1) + '.');
                                    });

                                    // If no questions left, show empty state
                                    if ($('.question-card').length === 0) {
                                        $('#questionsContainer').hide();
                                        $('#emptyQuestionsState').show();
                                    }
                                });

                                showAlert('success', 'Question deleted successfully');
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
                        showAlert('danger', 'Network error while deleting question');
                        removeOverlay();
                    }
                });
            }
        });

        // Save quiz button
        $('#saveQuizBtn').click(function() {
            const quizForm = document.getElementById('quizSettingsForm');

            // Form validation
            if (!quizForm.checkValidity()) {
                quizForm.classList.add('was-validated');
                return;
            }

            // Get form data
            const quizId = $('#quizId').val();
            const sectionId = $('#sectionId').val();
            const quizTitle = $('#quizTitle').val();
            const instructions = $('#quizInstructions').val();
            const timeLimit = $('#timeLimit').val() || null;
            const passMark = $('#passMark').val() || 70;
            const attemptsAllowed = $('#attemptsAllowed').val() || 1;
            const randomizeQuestions = $('#randomizeQuestions').is(':checked') ? 1 : 0;
            const showCorrectAnswers = $('#showCorrectAnswers').is(':checked') ? 1 : 0;
            const shuffleAnswers = $('#shuffleAnswers').is(':checked') ? 1 : 0;
            const isRequired = $('#isRequired').is(':checked') ? 1 : 0;
            const isEdit = quizId !== '';

            // Validate
            if (sectionId <= 0) {
                showAlert('danger', 'Invalid section. Please make sure you are adding a quiz to a valid section.');
                return;
            }

            // Check if there are any questions (for existing quizzes)
            if (isEdit && $('.question-card').length === 0) {
                if (!confirm('This quiz has no questions. Are you sure you want to save it?')) {
                    return;
                }
            }

            // Show loading overlay
            createOverlay('Saving quiz...');

            // AJAX request to save quiz
            $.ajax({
                url: '../ajax/assessments/save_quiz.php',
                type: 'POST',
                data: {
                    quiz_id: quizId,
                    section_id: sectionId,
                    quiz_title: quizTitle,
                    instruction: instructions,
                    time_limit: timeLimit,
                    pass_mark: passMark,
                    attempts_allowed: attemptsAllowed,
                    randomize_questions: randomizeQuestions,
                    show_correct_answers: showCorrectAnswers,
                    shuffle_answers: shuffleAnswers,
                    is_required: isRequired
                },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);

                        if (result.success) {
                            showAlert('success', `Quiz ${isEdit ? 'updated' : 'created'} successfully`);

                            // If new quiz, update the quiz ID
                            if (!isEdit) {
                                $('#quizId').val(result.quiz_id);

                                // Update URL for reload purposes without navigating away
                                const url = new URL(window.location);
                                url.searchParams.set('quiz_id', result.quiz_id);
                                window.history.replaceState({}, '', url);
                            }

                            // Check if user wants to add questions or go back to curriculum
                            if (!isEdit && confirm('Quiz saved successfully. Do you want to add questions now?')) {
                                // Stay on page for adding questions
                                removeOverlay();
                            } else {
                                // Redirect back to curriculum
                                window.location.href = `course-creator.php?step=6&course_id=${<?php echo $course_id; ?>}`;
                            }
                        } else {
                            showAlert('danger', 'Error: ' + result.message);
                            removeOverlay();
                        }
                    } catch (e) {
                        console.error('Error parsing response', e);
                        showAlert('danger', 'Error processing server response');
                        removeOverlay();
                    }
                },
                error: function() {
                    showAlert('danger', 'Network error while saving quiz');
                    removeOverlay();
                }
            });
        });

        // Preview quiz button
        $('#previewQuizBtn').click(function() {
            const quizId = $('#quizId').val();

            if (!quizId) {
                showAlert('warning', 'Please save the quiz first before previewing.');
                return;
            }

            window.open(`quiz-preview.php?quiz_id=${quizId}`, '_blank');
        });

        // Reset question form
        function resetQuestionForm() {
            // Reset form validation
            $('#questionForm').removeClass('was-validated')[0].reset();

            // Reset hidden fields
            $('#questionId').val('');

            // Set default values
            $('#questionType').val('Multiple Choice').trigger('change');
            $('#questionPoints').val('1');

            // Reset answer options for multiple choice
            $('#answerOptionsContainer').html(`
               <div class="answer-option-row mb-2">
                   <div class="input-group">
                       <div class="input-group-text">
                           <input class="form-check-input mt-0 is-correct-radio" type="radio" name="correctAnswer" checked>
                       </div>
                       <input type="text" class="form-control answer-text" placeholder="Enter answer option" required>
                       <button type="button" class="btn btn-outline-danger remove-answer-btn">
                           <i class="mdi mdi-delete"></i>
                       </button>
                   </div>
               </div>
               <div class="answer-option-row mb-2">
                   <div class="input-group">
                       <div class="input-group-text">
                           <input class="form-check-input mt-0 is-correct-radio" type="radio" name="correctAnswer">
                       </div>
                       <input type="text" class="form-control answer-text" placeholder="Enter answer option" required>
                       <button type="button" class="btn btn-outline-danger remove-answer-btn">
                           <i class="mdi mdi-delete"></i>
                       </button>
                   </div>
               </div>
           `);

            // Reset true/false options
            $('#trueFalseTrue').prop('checked', true);
        }
    });
</script>

<style>
    .question-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .question-card:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    }

    .answer-option {
        padding: 8px 12px;
        margin-bottom: 8px;
        border-radius: 4px;
        background-color: #f8f9fa;
    }

    .correct-answer {
        background-color: rgba(40, 167, 69, 0.1);
    }

    .empty-state-icon {
        opacity: 0.7;
    }

    .card-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }

    #answerOptionsContainer .input-group-text {
        background-color: #f8f9fa;
    }

    .question-number {
        font-weight: bold;
        min-width: 25px;
    }

    .question-type {
        color: #6c757d;
        font-size: 0.875rem;
        border-right: 1px solid #dee2e6;
        padding-right: 10px;
        margin-right: 10px;
    }
</style>