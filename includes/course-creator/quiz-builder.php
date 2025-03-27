<div class="quiz-builder-container">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Quiz Builder</h5>
            <a href="#" class="btn btn-sm btn-outline-secondary btn-back-to-curriculum">
                <i class="bi bi-arrow-left"></i> Back to Curriculum
            </a>
        </div>
        <div class="card-body">
            <form id="quizSettingsForm">
                <input type="hidden" id="quizId" name="quiz_id" value="<?php echo isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0; ?>">
                <input type="hidden" id="sectionId" name="section_id" value="<?php echo isset($_GET['section_id']) ? intval($_GET['section_id']) : 0; ?>">
                <input type="hidden" id="topicId" name="topic_id" value="<?php echo isset($_GET['topic_id']) ? intval($_GET['topic_id']) : 0; ?>">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="quizTitle" class="form-label">Quiz Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="quizTitle" name="quiz_title" required>
                    </div>
                    <div class="col-md-6">
                        <label for="passmark" class="form-label">Pass Mark (%) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="passmark" name="pass_mark" min="0" max="100" value="70" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="timeLimit" class="form-label">Time Limit (minutes)</label>
                        <input type="number" class="form-control" id="timeLimit" name="time_limit" min="0">
                        <small class="text-muted">Leave empty for no time limit</small>
                    </div>
                    <div class="col-md-6">
                        <label for="attemptsAllowed" class="form-label">Attempts Allowed</label>
                        <input type="number" class="form-control" id="attemptsAllowed" name="attempts_allowed" min="1" value="1">
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="randomizeQuestions" name="randomize_questions" value="1">
                            <label class="form-check-label" for="randomizeQuestions">Randomize Questions</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="shuffleAnswers" name="shuffle_answers" value="1">
                            <label class="form-check-label" for="shuffleAnswers">Shuffle Answers</label>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="showCorrectAnswers" name="show_correct_answers" value="1">
                            <label class="form-check-label" for="showCorrectAnswers">Show Correct Answers After Submission</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="isRequired" name="is_required" value="1" checked>
                            <label class="form-check-label" for="isRequired">Required for Course Completion</label>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="quizInstructions" class="form-label">Instructions</label>
                    <textarea class="form-control" id="quizInstructions" name="instruction" rows="3"></textarea>
                </div>
                
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary" id="saveQuizSettings">Save Settings</button>
                    <button type="button" class="btn btn-success" id="continueToQuestions" disabled>Continue to Questions <i class="bi bi-arrow-right"></i></button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Questions container - will be shown after saving settings -->
    <div class="card mt-4 d-none" id="questionsContainer">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Quiz Questions</h5>
            <button type="button" class="btn btn-primary btn-sm" id="addQuestionBtn">
                <i class="bi bi-plus-circle"></i> Add Question
            </button>
        </div>
        <div class="card-body">
            <div class="alert alert-info" id="noQuestionsMessage">
                <i class="bi bi-info-circle"></i> No questions added yet. Click "Add Question" to create your first question.
            </div>
            
            <div class="questions-list" id="questionsList">
                <!-- Questions will be populated here via JavaScript -->
            </div>
            
            <div class="text-end mt-3">
                <button type="button" class="btn btn-success" id="finishQuizBtn" disabled>Finish Quiz</button>
            </div>
        </div>
    </div>
    
    <!-- Question Form Modal -->
    <div class="modal fade" id="questionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="questionModalTitle">Add Question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="questionForm">
                        <input type="hidden" id="questionId" name="question_id" value="0">
                        <input type="hidden" id="modalQuizId" name="quiz_id">
                        
                        <div class="mb-3">
                            <label for="questionType" class="form-label">Question Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="questionType" name="question_type" required>
                                <option value="Multiple Choice">Multiple Choice</option>
                                <option value="True/False">True/False</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="questionText" class="form-label">Question Text <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="questionText" name="question_text" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="questionPoints" class="form-label">Points <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="questionPoints" name="points" min="1" value="1" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="questionDifficulty" class="form-label">Difficulty</label>
                            <select class="form-select" id="questionDifficulty" name="difficulty">
                                <option value="Easy">Easy</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="Hard">Hard</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="questionExplanation" class="form-label">Explanation</label>
                            <textarea class="form-control" id="questionExplanation" name="explanation" rows="2" placeholder="Explain why the correct answer is correct (optional)"></textarea>
                        </div>
                        
                        <!-- Answer options container -->
                        <div id="answerOptionsContainer">
                            <!-- For Multiple Choice -->
                            <div id="multipleChoiceContainer">
                                <h6 class="mb-3">Answer Options</h6>
                                <div id="answerOptions">
                                    <!-- Answer options will be added here dynamically -->
                                    <div class="answer-option mb-2" data-index="0">
                                        <div class="input-group">
                                            <div class="input-group-text">
                                                <input type="radio" name="correct_answer" value="0" required>
                                            </div>
                                            <input type="text" class="form-control" name="answer_text[]" placeholder="Answer option" required>
                                            <button type="button" class="btn btn-outline-danger remove-answer" disabled>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="answer-option mb-2" data-index="1">
                                        <div class="input-group">
                                            <div class="input-group-text">
                                                <input type="radio" name="correct_answer" value="1" required>
                                            </div>
                                            <input type="text" class="form-control" name="answer_text[]" placeholder="Answer option" required>
                                            <button type="button" class="btn btn-outline-danger remove-answer" disabled>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addAnswerOption">
                                    <i class="bi bi-plus-circle"></i> Add Answer Option
                                </button>
                            </div>
                            
                            <!-- For True/False -->
                            <div id="trueFalseContainer" class="d-none">
                                <h6 class="mb-3">Select Correct Answer</h6>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="tf_correct_answer" id="tfTrue" value="true" required>
                                    <label class="form-check-label" for="tfTrue">True</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tf_correct_answer" id="tfFalse" value="false" required>
                                    <label class="form-check-label" for="tfFalse">False</label>
                                </div>
                            </div>
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
</div>

<script>
    // Init TinyMCE for question text
    tinymce.init({
        selector: '#questionText',
        height: 200,
        menubar: false,
        plugins: ['lists', 'link', 'image', 'code'],
        toolbar: 'undo redo | formatselect | bold italic | bullist numlist | link image | code'
    });

    // Function to load quiz settings
    function loadQuizSettings() {
        const quizId = $('#quizId').val();
        if (quizId > 0) {
            // Show loading overlay
            createOverlay('Loading quiz settings...');
            
            $.ajax({
                url: '../ajax/assessments/get_quiz.php',
                type: 'GET',
                data: { quiz_id: quizId },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        // Populate form with quiz settings
                        $('#quizTitle').val(response.data.quiz_title);
                        $('#passmark').val(response.data.pass_mark);
                        $('#timeLimit').val(response.data.time_limit);
                        $('#attemptsAllowed').val(response.data.attempts_allowed);
                        $('#randomizeQuestions').prop('checked', response.data.randomize_questions == 1);
                        $('#shuffleAnswers').prop('checked', response.data.shuffle_answers == 1);
                        $('#showCorrectAnswers').prop('checked', response.data.show_correct_answers == 1);
                        $('#isRequired').prop('checked', response.data.is_required == 1);
                        $('#quizInstructions').val(response.data.instruction);
                        
                        // Enable continue button
                        $('#continueToQuestions').prop('disabled', false);
                        
                        // Load questions if they exist
                        loadQuizQuestions();
                    } else {
                        showAlert('danger', 'Error loading quiz settings: ' + response.message);
                    }
                    removeOverlay();
                },
                error: function() {
                    showAlert('danger', 'Server error while loading quiz settings');
                    removeOverlay();
                }
            });
        }
    }
    
    // Function to load quiz questions
    function loadQuizQuestions() {
        const quizId = $('#quizId').val();
        if (quizId > 0) {
            createOverlay('Loading questions...');
            
            $.ajax({
                url: '../ajax/assessments/get_questions.php',
                type: 'GET',
                data: { quiz_id: quizId },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        if (response.data.length > 0) {
                            // Show questions container
                            $('#questionsContainer').removeClass('d-none');
                            $('#noQuestionsMessage').addClass('d-none');
                            $('#questionsList').empty();
                            
                            // Populate questions
                            response.data.forEach(function(question, index) {
                                addQuestionToList(question, index + 1);
                            });
                            
                            // Enable finish button if at least one question exists
                            $('#finishQuizBtn').prop('disabled', false);
                        } else {
                            $('#noQuestionsMessage').removeClass('d-none');
                        }
                    } else {
                        showAlert('danger', 'Error loading questions: ' + response.message);
                    }
                    removeOverlay();
                },
                error: function() {
                    showAlert('danger', 'Server error while loading questions');
                    removeOverlay();
                }
            });
        }
    }
    
    // Function to add a question to the list
    function addQuestionToList(question, number) {
        const questionItem = `
            <div class="card mb-3 question-item" data-question-id="${question.question_id}">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Question ${number}: ${question.question_type}</span>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-primary edit-question" data-question-id="${question.question_id}">
                            <i class="bi bi-pencil"></i> Edit
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger delete-question" data-question-id="${question.question_id}">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="question-text mb-2">${question.question_text}</div>
                    <div class="points-badge"><span class="badge bg-info">${question.points} point${question.points > 1 ? 's' : ''}</span></div>
                </div>
            </div>
        `;
        
        $('#questionsList').append(questionItem);
    }
    
    // Document ready function
    $(document).ready(function() {
        // Load existing quiz if editing
        loadQuizSettings();
        
        // Save quiz settings
        $('#quizSettingsForm').submit(function(e) {
            e.preventDefault();
            
            // Show loading overlay
            createOverlay('Saving quiz settings...');
            
            $.ajax({
                url: '../ajax/assessments/save_quiz.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        showAlert('success', 'Quiz settings saved successfully');
                        
                        // Update quiz ID if it's a new quiz
                        if ($('#quizId').val() == 0) {
                            $('#quizId').val(response.quiz_id);
                            $('#modalQuizId').val(response.quiz_id);
                        }
                        
                        // Enable continue button
                        $('#continueToQuestions').prop('disabled', false);
                        
                        // Show questions container
                        $('#questionsContainer').removeClass('d-none');
                        
                        // Load questions if they exist
                        loadQuizQuestions();
                    } else {
                        showAlert('danger', 'Error: ' + response.message);
                    }
                    removeOverlay();
                },
                error: function() {
                    showAlert('danger', 'Server error while saving quiz settings');
                    removeOverlay();
                }
            });
        });
        
        // Continue to questions button
        $('#continueToQuestions').click(function() {
            $('#questionsContainer').removeClass('d-none');
            $('html, body').animate({
                scrollTop: $('#questionsContainer').offset().top - 20
            }, 500);
        });
        
        // Add question button
        $('#addQuestionBtn').click(function() {
            // Reset form
            $('#questionForm')[0].reset();
            $('#questionId').val(0);
            $('#modalQuizId').val($('#quizId').val());
            $('#questionModalTitle').text('Add Question');
            
            // Reset TinyMCE
            tinymce.get('questionText').setContent('');
            
            // Show Multiple Choice by default
            $('#questionType').val('Multiple Choice');
            $('#multipleChoiceContainer').removeClass('d-none');
            $('#trueFalseContainer').addClass('d-none');
            
            // Reset answer options
            $('#answerOptions').html(`
                <div class="answer-option mb-2" data-index="0">
                    <div class="input-group">
                        <div class="input-group-text">
                            <input type="radio" name="correct_answer" value="0" required>
                        </div>
                        <input type="text" class="form-control" name="answer_text[]" placeholder="Answer option" required>
                        <button type="button" class="btn btn-outline-danger remove-answer" disabled>
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="answer-option mb-2" data-index="1">
                    <div class="input-group">
                        <div class="input-group-text">
                            <input type="radio" name="correct_answer" value="1" required>
                        </div>
                        <input type="text" class="form-control" name="answer_text[]" placeholder="Answer option" required>
                        <button type="button" class="btn btn-outline-danger remove-answer" disabled>
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `);
            
            // Show modal
            $('#questionModal').modal('show');
        });
        
        // Question type change
        $('#questionType').change(function() {
            const questionType = $(this).val();
            
            if (questionType === 'True/False') {
                $('#multipleChoiceContainer').addClass('d-none');
                $('#trueFalseContainer').removeClass('d-none');
            } else {
                $('#multipleChoiceContainer').removeClass('d-none');
                $('#trueFalseContainer').addClass('d-none');
            }
        });
        
        // Add answer option
        $('#addAnswerOption').click(function() {
            const optionsCount = $('.answer-option').length;
            const newIndex = optionsCount;
            
            const newOption = `
                <div class="answer-option mb-2" data-index="${newIndex}">
                    <div class="input-group">
                        <div class="input-group-text">
                            <input type="radio" name="correct_answer" value="${newIndex}" required>
                        </div>
                        <input type="text" class="form-control" name="answer_text[]" placeholder="Answer option" required>
                        <button type="button" class="btn btn-outline-danger remove-answer">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            
            $('#answerOptions').append(newOption);
            
            // Enable remove buttons if more than 2 options
            if (optionsCount + 1 > 2) {
                $('.remove-answer').prop('disabled', false);
            }
        });
        
        // Remove answer option (event delegation)
        $(document).on('click', '.remove-answer', function() {
            if (!$(this).prop('disabled')) {
                $(this).closest('.answer-option').remove();
                
                // Disable remove buttons if only 2 options left
                if ($('.answer-option').length <= 2) {
                    $('.remove-answer').prop('disabled', true);
                }
                
                // Update indices
                $('.answer-option').each(function(idx) {
                    $(this).attr('data-index', idx);
                    $(this).find('input[type="radio"]').val(idx);
                });
            }
        });
        
        // Save question
        $('#saveQuestionBtn').click(function() {
            // Get TinyMCE content
            const questionText = tinymce.get('questionText').getContent();
            if (!questionText) {
                showAlert('danger', 'Question text is required');
                return;
            }
            
            // Check if correct answer is selected for multiple choice
            const questionType = $('#questionType').val();
            let formData = new FormData($('#questionForm')[0]);
            
            // Replace question_text with TinyMCE content
            formData.delete('question_text');
            formData.append('question_text', questionText);
            
            if (questionType === 'Multiple Choice') {
                if (!$('input[name="correct_answer"]:checked').val()) {
                    showAlert('danger', 'Please select the correct answer');
                    return;
                }
            } else if (questionType === 'True/False') {
                if (!$('input[name="tf_correct_answer"]:checked').val()) {
                    showAlert('danger', 'Please select whether True or False is correct');
                    return;
                }
            }
            
            // Show loading overlay
            createOverlay('Saving question...');
            
            $.ajax({
                url: '../ajax/assessments/save_question.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        showAlert('success', 'Question saved successfully');
                        
                        // Close modal
                        $('#questionModal').modal('hide');
                        
                        // Reload questions
                        loadQuizQuestions();
                        
                        // Enable finish button
                        $('#finishQuizBtn').prop('disabled', false);
                    } else {
                        showAlert('danger', 'Error: ' + response.message);
                    }
                    removeOverlay();
                },
                error: function() {
                    showAlert('danger', 'Server error while saving question');
                    removeOverlay();
                }
            });
        });
        
        // Edit question (event delegation)
        $(document).on('click', '.edit-question', function() {
            const questionId = $(this).data('question-id');
            
            // Show loading overlay
            createOverlay('Loading question...');
            
            $.ajax({
                url: '../ajax/assessments/get_question.php',
                type: 'GET',
                data: { question_id: questionId },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        // Populate form
                        $('#questionId').val(questionId);
                        $('#modalQuizId').val($('#quizId').val());
                        $('#questionModalTitle').text('Edit Question');
                        $('#questionType').val(response.data.question_type);
                        tinymce.get('questionText').setContent(response.data.question_text);
                        $('#questionPoints').val(response.data.points);
                        $('#questionDifficulty').val(response.data.difficulty || 'Medium');
                        $('#questionExplanation').val(response.data.explanation || '');
                        
                        // Handle question type specific content
                        if (response.data.question_type === 'True/False') {
                            $('#multipleChoiceContainer').addClass('d-none');
                            $('#trueFalseContainer').removeClass('d-none');
                            
                            // Set correct answer
                            if (response.data.answers.length > 0) {
                                const correctAnswer = response.data.answers[0].is_correct ? 'true' : 'false';
                                $(`input[name="tf_correct_answer"][value="${correctAnswer}"]`).prop('checked', true);
                            }
                        } else {
                            $('#multipleChoiceContainer').removeClass('d-none');
                            $('#trueFalseContainer').addClass('d-none');
                            
                            // Populate answer options
                            $('#answerOptions').empty();
                            
                            response.data.answers.forEach(function(answer, index) {
                                const option = `
                                    <div class="answer-option mb-2" data-index="${index}">
                                        <div class="input-group">
                                            <div class="input-group-text">
                                                <input type="radio" name="correct_answer" value="${index}" ${answer.is_correct ? 'checked' : ''} required>
                                            </div>
                                            <input type="text" class="form-control" name="answer_text[]" value="${answer.answer_text}" placeholder="Answer option" required>
                                            <button type="button" class="btn btn-outline-danger remove-answer" ${response.data.answers.length <= 2 ? 'disabled' : ''}>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                `;
                                
                                $('#answerOptions').append(option);
                            });
                        }
                        
                        // Show modal
                        $('#questionModal').modal('show');
                    } else {
                        showAlert('danger', 'Error loading question: ' + response.message);
                    }
                    removeOverlay();
                },
                error: function() {
                    showAlert('danger', 'Server error while loading question');
                    removeOverlay();
                }
            });
        });
        
        // Delete question (event delegation)
        $(document).on('click', '.delete-question', function() {
            const questionId = $(this).data('question-id');
            
            if (confirm('Are you sure you want to delete this question? This action cannot be undone.')) {
                // Show loading overlay
                createOverlay('Deleting question...');
                
                $.ajax({
                    url: '../ajax/assessments/delete_question.php',
                    type: 'POST',
                    data: { question_id: questionId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            showAlert('success', 'Question deleted successfully');
                            
                            // Reload questions
                            loadQuizQuestions();
                        } else {
                            showAlert('danger', 'Error: ' + response.message);
                        }
                        removeOverlay();
                    },
                    error: function() {
                        showAlert('danger', 'Server error while deleting question');
                        removeOverlay();
                    }
                });
            }
        });
        
        // Finish quiz button
        $('#finishQuizBtn').click(function() {
            // Redirect back to curriculum builder
            window.location.href = 'instructor/course-creator.php?step=6';
        });
        
        // Back to curriculum button
        $('.btn-back-to-curriculum').click(function(e) {
            e.preventDefault();
            window.location.href = 'instructor/course-creator.php?step=6';
        });
    });
</script>