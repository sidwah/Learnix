<?php
/**
 * Get Quiz Question AJAX Handler
 * 
 * Returns the HTML for a specific quiz question.
 * 
 * @package Learnix
 * @subpackage AJAX
 */

// Include necessary files
require_once '../../backend/config.php';
require_once '../../backend/auth/session.php';

// Check if user is logged in
if (!isLoggedIn() || $_SESSION['role'] !== 'student') {
    echo '<div class="alert alert-danger">Access denied. Please login as a student.</div>';
    exit;
}

// Get request parameters
$attemptId = isset($_GET['attempt_id']) ? intval($_GET['attempt_id']) : 0;
$questionId = isset($_GET['question_id']) ? intval($_GET['question_id']) : 0;

if (!$attemptId || !$questionId) {
    echo '<div class="alert alert-danger">Missing required parameters.</div>';
    exit;
}

try {
    // Verify attempt belongs to current user
    $stmt = $conn->prepare("
        SELECT a.*, q.shuffle_answers
        FROM student_quiz_attempts a
        JOIN section_quizzes q ON a.quiz_id = q.quiz_id
        WHERE a.attempt_id = ? AND a.user_id = ? AND a.is_completed = 0
    ");
    $stmt->bind_param("ii", $attemptId, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $attempt = $result->fetch_assoc();
    $stmt->close();

    if (!$attempt) {
        echo '<div class="alert alert-danger">Attempt not found or has expired.</div>';
        exit;
    }

    // Get question details
    $stmt = $conn->prepare("
        SELECT q.*
        FROM quiz_questions q
        JOIN section_quizzes sq ON q.quiz_id = sq.quiz_id
        WHERE q.question_id = ? AND q.quiz_id = ?
    ");
    $stmt->bind_param("ii", $questionId, $attempt['quiz_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $question = $result->fetch_assoc();
    $stmt->close();

    if (!$question) {
        echo '<div class="alert alert-danger">Question not found.</div>';
        exit;
    }

    // Get student's previous answer if any
    $studentResponse = null;
    $stmt = $conn->prepare("
        SELECT *
        FROM student_question_responses
        WHERE attempt_id = ? AND question_id = ?
    ");
    $stmt->bind_param("ii", $attemptId, $questionId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $studentResponse = $result->fetch_assoc();
    }
    $stmt->close();

    // Get answer options for multiple choice questions
    $answers = [];
    if (in_array($question['question_type'], ['Multiple Choice', 'True/False'])) {
        $answerQuery = "
            SELECT a.*
            FROM quiz_answers a
            WHERE a.question_id = ?
        ";
        
        // Apply answer shuffling if enabled
        if ($attempt['shuffle_answers']) {
            $answerQuery .= " ORDER BY RAND()";
        } else {
            $answerQuery .= " ORDER BY a.answer_id";
        }
        
        $stmt = $conn->prepare($answerQuery);
        $stmt->bind_param("i", $questionId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $answers[] = $row;
        }
        $stmt->close();
    }

    // Get matching pairs for matching questions
    $matchingPairs = [];
    if ($question['question_type'] === 'Matching') {
        $stmt = $conn->prepare("
            SELECT *
            FROM quiz_matching_pairs
            WHERE question_id = ?
            ORDER BY position
        ");
        $stmt->bind_param("i", $questionId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $matchingPairs[] = $row;
        }
        $stmt->close();
    }

    // Get sequence items for ordering questions
    $sequenceItems = [];
    if ($question['question_type'] === 'Ordering') {
        $stmt = $conn->prepare("
            SELECT *
            FROM quiz_sequence_items
            WHERE question_id = ?
            ORDER BY " . ($attempt['shuffle_answers'] ? "RAND()" : "correct_position")
        );
        $stmt->bind_param("i", $questionId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $sequenceItems[] = $row;
        }
        $stmt->close();
    }

    // Get student's selected answers for multiple choice
    $selectedAnswers = [];
    if (in_array($question['question_type'], ['Multiple Choice', 'True/False']) && $studentResponse) {
        $stmt = $conn->prepare("
            SELECT answer_id
            FROM student_answer_selections
            WHERE response_id = ?
        ");
        $stmt->bind_param("i", $studentResponse['response_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $selectedAnswers[] = $row['answer_id'];
        }
        $stmt->close();
    }

    // Update session data
    $stmt = $conn->prepare("
        SELECT session_data
        FROM quiz_sessions
        WHERE attempt_id = ? AND is_active = 1
    ");
    $stmt->bind_param("i", $attemptId);
    $stmt->execute();
    $result = $stmt->get_result();
    $session = $result->fetch_assoc();
    $stmt->close();

    if ($session) {
        $sessionData = json_decode($session['session_data'], true) ?: [
            'question_order' => [],
            'current_question' => 0,
            'answers' => []
        ];
        
        // Find index of current question
        $currentIndex = array_search($questionId, $sessionData['question_order']);
        if ($currentIndex !== false) {
            $sessionData['current_question'] = $currentIndex;
            
            $stmt = $conn->prepare("
                UPDATE quiz_sessions
                SET session_data = ?, last_activity = NOW()
                WHERE attempt_id = ? AND is_active = 1
            ");
            $sessionDataJson = json_encode($sessionData);
            $stmt->bind_param("si", $sessionDataJson, $attemptId);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Output the question HTML
    ?>
    <div class="quiz-question p-4 bg-white rounded shadow-sm">
        <form id="questionForm" data-question-id="<?php echo $question['question_id']; ?>" data-question-type="<?php echo $question['question_type']; ?>">
            <div class="question-header mb-4">
                <h4 class="question-text">
                    <?php echo $question['question_text']; ?>
                </h4>
                <div class="question-meta text-muted small mt-2">
                    <span class="me-3">
                        <i class="fas fa-tag me-1"></i> <?php echo $question['question_type']; ?>
                    </span>
                    <span>
                        <i class="fas fa-trophy me-1"></i> <?php echo $question['points']; ?> points
                    </span>
                </div>
            </div>

            <div class="question-body">
                <?php if ($question['question_type'] === 'Multiple Choice'): ?>
                    <div class="multiple-choice-options">
                        <?php foreach ($answers as $answer): ?>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="answer_id" 
                                       id="answer_<?php echo $answer['answer_id']; ?>" 
                                       value="<?php echo $answer['answer_id']; ?>"
                                       <?php echo in_array($answer['answer_id'], $selectedAnswers) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="answer_<?php echo $answer['answer_id']; ?>">
                                    <?php echo $answer['answer_text']; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php elseif ($question['question_type'] === 'True/False'): ?>
                    <div class="true-false-options">
                        <?php foreach ($answers as $answer): ?>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="answer_id" 
                                       id="answer_<?php echo $answer['answer_id']; ?>" 
                                       value="<?php echo $answer['answer_id']; ?>"
                                       <?php echo in_array($answer['answer_id'], $selectedAnswers) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="answer_<?php echo $answer['answer_id']; ?>">
                                    <?php echo $answer['answer_text']; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php elseif ($question['question_type'] === 'Short_Answer'): ?>
                    <div class="short-answer-input">
                        <textarea class="form-control" name="answer_text" rows="4" placeholder="Type your answer here..."><?php echo $studentResponse ? $studentResponse['answer_text'] : ''; ?></textarea>
                    </div>
                <?php elseif ($question['question_type'] === 'Essay'): ?>
                    <div class="essay-input">
                        <textarea class="form-control" name="answer_text" rows="8" placeholder="Type your answer here..."><?php echo $studentResponse ? $studentResponse['answer_text'] : ''; ?></textarea>
                    </div>
                <?php elseif ($question['question_type'] === 'Fill in the Blanks'): ?>
                    <div class="fill-blanks-input">
                        <input type="text" class="form-control" name="answer_text" 
                               placeholder="Type your answer here..." 
                               value="<?php echo $studentResponse ? $studentResponse['answer_text'] : ''; ?>">
                        <small class="form-text text-muted">Separate multiple answers with commas if needed.</small>
                    </div>
                <?php elseif ($question['question_type'] === 'Matching'): ?>
                    <div class="matching-options">
                        <?php 
                        // Create array of right items for shuffling
                        $rightItems = array_column($matchingPairs, 'right_item');
                        if ($attempt['shuffle_answers']) {
                            shuffle($rightItems);
                        }
                        
                        foreach ($matchingPairs as $index => $pair): 
                            $selectedRight = '';
                            if ($studentResponse) {
                                // Get student's match for this pair
                                $stmt = $conn->prepare("
                                    SELECT student_right_match
                                    FROM student_matching_responses
                                    WHERE response_id = ? AND pair_id = ?
                                ");
                                $stmt->bind_param("ii", $studentResponse['response_id'], $pair['pair_id']);
                                $stmt->execute();
                                $matchResult = $stmt->get_result();
                                if ($matchResult->num_rows > 0) {
                                    $selectedRight = $matchResult->fetch_assoc()['student_right_match'];
                                }
                                $stmt->close();
                            }
                        ?>
                            <div class="matching-pair row mb-3 align-items-center">
                                <div class="col-5">
                                    <div class="matching-left p-2 border rounded">
                                        <?php echo $pair['left_item']; ?>
                                    </div>
                                </div>
                                <div class="col-2 text-center">
                                    <i class="fas fa-long-arrow-alt-right"></i>
                                </div>
                                <div class="col-5">
                                    <select class="form-select" name="match_<?php echo $pair['pair_id']; ?>">
                                        <option value="">-- Select match --</option>
                                        <?php foreach ($rightItems as $rightItem): ?>
                                            <option value="<?php echo htmlspecialchars($rightItem); ?>" 
                                                <?php echo $selectedRight === $rightItem ? 'selected' : ''; ?>>
                                                <?php echo $rightItem; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php elseif ($question['question_type'] === 'Ordering'): ?>
                    <div class="ordering-options">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Drag the items to place them in the correct order.
                        </div>
                        
                        <div class="sequence-items" id="sequenceContainer">
                            <?php 
                            // Get student's ordering if available
                            $studentOrder = [];
                            if ($studentResponse) {
                                $stmt = $conn->prepare("
                                    SELECT item_id, student_position
                                    FROM student_sequence_responses
                                    WHERE response_id = ?
                                    ORDER BY student_position
                                ");
                                $stmt->bind_param("i", $studentResponse['response_id']);
                                $stmt->execute();
                                $orderResult = $stmt->get_result();
                                while ($orderRow = $orderResult->fetch_assoc()) {
                                    $studentOrder[$orderRow['item_id']] = $orderRow['student_position'];
                                }
                                $stmt->close();
                                
                                // Sort items by student's order
                                usort($sequenceItems, function($a, $b) use ($studentOrder) {
                                    $posA = $studentOrder[$a['item_id']] ?? 9999;
                                    $posB = $studentOrder[$b['item_id']] ?? 9999;
                                    return $posA - $posB;
                                });
                            }
                            
                            foreach ($sequenceItems as $index => $item): 
                            ?>
                                <div class="sequence-item p-3 mb-2 bg-light border rounded" 
                                     data-item-id="<?php echo $item['item_id']; ?>">
                                    <div class="d-flex align-items-center">
                                        <div class="sequence-handle me-3">
                                            <i class="fas fa-grip-vertical text-muted"></i>
                                        </div>
                                        <div class="sequence-content flex-grow-1">
                                            <?php echo $item['content']; ?>
                                        </div>
                                        <input type="hidden" name="sequence_order[]" value="<?php echo $item['item_id']; ?>">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <script>
                    // Initialize sortable for sequence items
                    document.addEventListener('DOMContentLoaded', function() {
                        if (typeof Sortable !== 'undefined') {
                            new Sortable(document.getElementById('sequenceContainer'), {
                                animation: 150,
                                handle: '.sequence-handle',
                                onEnd: function() {
                                    // Update hidden inputs when order changes
                                    updateSequenceOrder();
                                }
                            });
                        }
                        
                        function updateSequenceOrder() {
                            const items = document.querySelectorAll('.sequence-item');
                            const inputs = document.querySelectorAll('input[name="sequence_order[]"]');
                            
                            items.forEach((item, index) => {
                                inputs[index].value = item.getAttribute('data-item-id');
                            });
                            
                            // Trigger auto-save
                            clearTimeout(window.autoSaveTimeout);
                            window.autoSaveTimeout = setTimeout(function() {
                                // Call your save function here
                                const saveEvent = new Event('orderChanged');
                                document.getElementById('questionForm').dispatchEvent(saveEvent);
                            }, 1000);
                        }
                    });
                    </script>
                <?php elseif ($question['question_type'] === 'Drag and Drop'): ?>
                    <div class="drag-drop-options">
                        <!-- Drag and Drop implementation would go here -->
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Drag and Drop questions are not currently supported.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <?php

} catch (Exception $e) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>