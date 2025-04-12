<?php
/**
 * Get All Quiz Questions AJAX Handler
 * 
 * Returns the HTML for all questions in a quiz at once (Coursera style).
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

if (!$attemptId) {
    echo '<div class="alert alert-danger">Missing required parameters.</div>';
    exit;
}

try {
    // Verify attempt belongs to current user
    $stmt = $conn->prepare("
        SELECT a.*, q.quiz_title, q.shuffle_answers, q.quiz_id
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

    // Get all questions for this quiz
    $questions = [];
    if ($attempt['shuffle_answers']) {
        $stmt = $conn->prepare("
            SELECT *
            FROM quiz_questions
            WHERE quiz_id = ?
            ORDER BY RAND()
        ");
    } else {
        $stmt = $conn->prepare("
            SELECT *
            FROM quiz_questions
            WHERE quiz_id = ?
            ORDER BY question_order
        ");
    }
    $stmt->bind_param("i", $attempt['quiz_id']);
    $stmt->execute();
    $questionsResult = $stmt->get_result();
    while ($row = $questionsResult->fetch_assoc()) {
        $questions[] = $row;
    }
    $stmt->close();

    if (empty($questions)) {
        echo '<div class="alert alert-danger">No questions found for this quiz.</div>';
        exit;
    }

    // Output header
    echo '<div class="quiz-title-header">
            <h3>' . htmlspecialchars($attempt['quiz_title']) . '</h3>
            <p class="text-muted">Answer all questions below. Your progress is automatically saved.</p>
          </div>';

    // Output each question
    $questionNumber = 1;
    foreach ($questions as $question) {
        // Get student's previous answer if any
        $studentResponse = null;
        $stmt = $conn->prepare("
            SELECT *
            FROM student_question_responses
            WHERE attempt_id = ? AND question_id = ?
        ");
        $stmt->bind_param("ii", $attemptId, $question['question_id']);
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
            $stmt->bind_param("i", $question['question_id']);
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
            $stmt->bind_param("i", $question['question_id']);
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
            $stmt->bind_param("i", $question['question_id']);
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

        // Output question HTML
        ?>
        <div class="quiz-question" data-question-id="<?php echo $question['question_id']; ?>" data-question-type="<?php echo $question['question_type']; ?>">
            <form id="questionForm_<?php echo $question['question_id']; ?>" data-question-id="<?php echo $question['question_id']; ?>" data-question-type="<?php echo $question['question_type']; ?>">
                <div class="quiz-question-header">
                    <div class="d-flex align-items-center mb-2">
                        <div class="badge bg-primary me-2"><?php echo $questionNumber; ?></div>
                        <h5 class="mb-0">
                            <?php echo $question['question_text']; ?>
                            <?php if ($question['points'] > 1): ?>
                                <span class="badge bg-light text-dark ms-2"><?php echo $question['points']; ?> points</span>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="text-muted small">
                        <?php echo $question['question_type']; ?>
                    </div>
                </div>

                <div class="quiz-question-body">
                    <?php if ($question['question_type'] === 'Multiple Choice'): ?>
                        <div class="quiz-options">
                            <?php foreach ($answers as $index => $answer): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="answer_id" 
                                           id="answer_<?php echo $question['question_id']; ?>_<?php echo $answer['answer_id']; ?>" 
                                           value="<?php echo $answer['answer_id']; ?>"
                                           <?php echo in_array($answer['answer_id'], $selectedAnswers) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="answer_<?php echo $question['question_id']; ?>_<?php echo $answer['answer_id']; ?>">
                                        <?php echo $answer['answer_text']; ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($question['question_type'] === 'True/False'): ?>
                        <div class="quiz-options">
                            <?php foreach ($answers as $answer): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="answer_id" 
                                           id="answer_<?php echo $question['question_id']; ?>_<?php echo $answer['answer_id']; ?>" 
                                           value="<?php echo $answer['answer_id']; ?>"
                                           <?php echo in_array($answer['answer_id'], $selectedAnswers) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="answer_<?php echo $question['question_id']; ?>_<?php echo $answer['answer_id']; ?>">
                                        <?php echo $answer['answer_text']; ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($question['question_type'] === 'Short_Answer'): ?>
                        <div class="mb-3">
                            <textarea class="form-control" name="answer_text" rows="4" placeholder="Type your answer here..."><?php echo $studentResponse ? $studentResponse['answer_text'] : ''; ?></textarea>
                        </div>
                    <?php elseif ($question['question_type'] === 'Essay'): ?>
                        <div class="mb-3">
                            <textarea class="form-control" name="answer_text" rows="8" placeholder="Type your answer here..."><?php echo $studentResponse ? $studentResponse['answer_text'] : ''; ?></textarea>
                        </div>
                    <?php elseif ($question['question_type'] === 'Fill in the Blanks'): ?>
                        <div class="mb-3">
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
                                        <i class="bi bi-arrow-right"></i>
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
                                <i class="bi bi-info-circle me-2"></i>
                                Drag the items to place them in the correct order.
                            </div>
                            
                            <div class="sequence-items" id="sequenceContainer_<?php echo $question['question_id']; ?>">
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
                                    if (!empty($studentOrder)) {
                                        usort($sequenceItems, function($a, $b) use ($studentOrder) {
                                            $posA = $studentOrder[$a['item_id']] ?? 9999;
                                            $posB = $studentOrder[$b['item_id']] ?? 9999;
                                            return $posA - $posB;
                                        });
                                    }
                                }
                                
                                foreach ($sequenceItems as $index => $item): 
                                ?>
                                    <div class="sequence-item p-3 mb-2 bg-light border rounded" 
                                         data-item-id="<?php echo $item['item_id']; ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="sequence-handle me-3">
                                                <i class="bi bi-grip-vertical"></i>
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
                                new Sortable(document.getElementById('sequenceContainer_<?php echo $question['question_id']; ?>'), {
                                    animation: 150,
                                    handle: '.sequence-handle',
                                    onEnd: function() {
                                        // Update hidden inputs when order changes
                                        updateSequenceOrder_<?php echo $question['question_id']; ?>();
                                    }
                                });
                            }
                            
                            function updateSequenceOrder_<?php echo $question['question_id']; ?>() {
                                const container = document.getElementById('sequenceContainer_<?php echo $question['question_id']; ?>');
                                const items = container.querySelectorAll('.sequence-item');
                                const inputs = container.querySelectorAll('input[name="sequence_order[]"]');
                                
                                items.forEach((item, index) => {
                                    inputs[index].value = item.getAttribute('data-item-id');
                                });
                                
                                // Trigger auto-save
                                clearTimeout(window.autoSaveTimeout);
                                window.autoSaveTimeout = setTimeout(function() {
                                    // Call your save function here
                                    const saveEvent = new Event('orderChanged');
                                    document.getElementById('questionForm_<?php echo $question['question_id']; ?>').dispatchEvent(saveEvent);
                                }, 1000);
                            }
                        });
                        </script>
                    <?php elseif ($question['question_type'] === 'Drag and Drop'): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-circle me-2"></i>
                            Drag and Drop questions are not currently supported.
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <?php
        $questionNumber++;
    }

} catch (Exception $e) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>