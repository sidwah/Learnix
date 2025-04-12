<?php
/**
 * Save Quiz Response AJAX Handler
 * 
 * Saves a student's response to a quiz question.
 * 
 * @package Learnix
 * @subpackage AJAX
 */

// Include necessary files
require_once '../../backend/config.php';
require_once '../../backend/auth/session.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn() || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Access denied. Please login as a student.']);
    exit;
}

// Verify AJAX request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Get POST data
$attemptId = isset($_POST['attempt_id']) ? intval($_POST['attempt_id']) : 0;
$questionId = isset($_POST['question_id']) ? intval($_POST['question_id']) : 0;

if (!$attemptId || !$questionId) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Verify attempt belongs to current user and is active
    $stmt = $conn->prepare("
        SELECT a.*, q.question_type
        FROM student_quiz_attempts a
        JOIN quiz_questions q ON q.quiz_id = a.quiz_id
        WHERE a.attempt_id = ? AND a.user_id = ? AND a.is_completed = 0 AND q.question_id = ?
    ");
    $stmt->bind_param("iii", $attemptId, $_SESSION['user_id'], $questionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $attemptData = $result->fetch_assoc();
    $stmt->close();

    if (!$attemptData) {
        throw new Exception('Invalid attempt or question.');
    }

    // Check if a response already exists
    $stmt = $conn->prepare("
        SELECT response_id FROM student_question_responses
        WHERE attempt_id = ? AND question_id = ?
    ");
    $stmt->bind_param("ii", $attemptId, $questionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $responseExists = $result->num_rows > 0;
    $responseId = $responseExists ? $result->fetch_assoc()['response_id'] : null;
    $stmt->close();

    // Get question details
    $stmt = $conn->prepare("
        SELECT * FROM quiz_questions
        WHERE question_id = ?
    ");
    $stmt->bind_param("i", $questionId);
    $stmt->execute();
    $question = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$question) {
        throw new Exception('Question not found.');
    }

    // Process response based on question type
    $isCorrect = false;
    $pointsAwarded = 0;
    $answerText = null;
    $responseTime = isset($_POST['response_time']) ? intval($_POST['response_time']) : null;
    $pairs = [];
    $items = [];

    // Process multiple choice or true/false questions
    if (in_array($question['question_type'], ['Multiple Choice', 'True/False'])) {
        // Check if correct answer was selected
        if (isset($_POST['answer_id'])) {
            $answerId = intval($_POST['answer_id']);
            
            // Get the selected answer
            $stmt = $conn->prepare("
                SELECT is_correct FROM quiz_answers
                WHERE answer_id = ? AND question_id = ?
            ");
            $stmt->bind_param("ii", $answerId, $questionId);
            $stmt->execute();
            $answerResult = $stmt->get_result();
            
            if ($answerResult->num_rows > 0) {
                $answer = $answerResult->fetch_assoc();
                $isCorrect = $answer['is_correct'] == 1;
                $pointsAwarded = $isCorrect ? $question['points'] : 0;
            }
            $stmt->close();
        }
    } 
    // Process short answer or essay questions
    else if (in_array($question['question_type'], ['Short_Answer', 'Essay', 'Fill in the Blanks'])) {
        if (isset($_POST['answer_text'])) {
            $answerText = $_POST['answer_text'];
            
            // For short answer, check if matches any correct answers
            if ($question['question_type'] === 'Short_Answer' || $question['question_type'] === 'Fill in the Blanks') {
                // Get correct answers
                $stmt = $conn->prepare("
                    SELECT answer_text, match_type, match_percentage
                    FROM quiz_answers 
                    WHERE question_id = ? AND is_correct = 1
                ");
                $stmt->bind_param("i", $questionId);
                $stmt->execute();
                $correctAnswers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
                
                foreach ($correctAnswers as $correctAnswer) {
                    $matchType = $correctAnswer['match_type'] ?? 'exact';
                    $matchPercentage = $correctAnswer['match_percentage'] ?? 100;
                    
                    if ($matchType === 'exact' && strtolower(trim($answerText)) === strtolower(trim($correctAnswer['answer_text']))) {
                        $isCorrect = true;
                        $pointsAwarded = $question['points'];
                        break;
                    } else if ($matchType === 'case_insensitive' && strcasecmp(trim($answerText), trim($correctAnswer['answer_text'])) === 0) {
                        $isCorrect = true;
                        $pointsAwarded = $question['points'];
                        break;
                    } else if ($matchType === 'pattern' && preg_match('/' . $correctAnswer['answer_text'] . '/i', $answerText)) {
                        $isCorrect = true;
                        $pointsAwarded = $question['points'];
                        break;
                    }
                }
            } else if ($question['question_type'] === 'Essay') {
                // Essays require manual grading - save but don't score
                $isCorrect = false;
                $pointsAwarded = 0;
            }
        }
    }
    // Process matching questions
    else if ($question['question_type'] === 'Matching') {
        // Get all pairs for this question
        $stmt = $conn->prepare("
            SELECT * FROM quiz_matching_pairs
            WHERE question_id = ?
        ");
        $stmt->bind_param("i", $questionId);
        $stmt->execute();
        $pairs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // Count how many matches are correct
        $totalPairs = count($pairs);
        $correctPairs = 0;
        
        foreach ($pairs as $pair) {
            $pairId = $pair['pair_id'];
            $postKey = 'match_' . $pairId;
            
            if (isset($_POST[$postKey]) && !empty($_POST[$postKey])) {
                $studentMatch = $_POST[$postKey];
                if ($studentMatch === $pair['right_item']) {
                    $correctPairs++;
                }
            }
        }
        
        // Calculate points based on percentage correct
        if ($totalPairs > 0) {
            $percentageCorrect = ($correctPairs / $totalPairs) * 100;
            $isCorrect = $correctPairs == $totalPairs;
            $pointsAwarded = ($percentageCorrect / 100) * $question['points'];
        }
    }
    // Process ordering questions
    else if ($question['question_type'] === 'Ordering') {
        if (isset($_POST['sequence_order']) && is_array($_POST['sequence_order'])) {
            $sequenceOrder = $_POST['sequence_order'];
            
            // Get all sequence items with their correct positions
            $stmt = $conn->prepare("
                SELECT item_id, correct_position
                FROM quiz_sequence_items
                WHERE question_id = ?
            ");
            $stmt->bind_param("i", $questionId);
            $stmt->execute();
            $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            // Count how many items are in correct position
            $totalItems = count($items);
            $correctItems = 0;
            
            foreach ($sequenceOrder as $studentPosition => $itemId) {
                foreach ($items as $item) {
                    if ($item['item_id'] == $itemId) {
                        // Student position is 0-indexed, but database might be 1-indexed
                        if (($studentPosition + 1) == $item['correct_position']) {
                            $correctItems++;
                        }
                        break;
                    }
                }
            }
            
            // Calculate points based on percentage correct
            if ($totalItems > 0) {
                $percentageCorrect = ($correctItems / $totalItems) * 100;
                $isCorrect = $correctItems == $totalItems;
                $pointsAwarded = ($percentageCorrect / 100) * $question['points'];
            }
        }
    }

    // Save or update the response
    if ($responseExists) {
        // Update existing response
        $stmt = $conn->prepare("
            UPDATE student_question_responses
            SET is_correct = ?, points_awarded = ?, answer_text = ?, response_time = ?
            WHERE response_id = ?
        ");
        $stmt->bind_param("idsii", $isCorrect, $pointsAwarded, $answerText, $responseTime, $responseId);
        $stmt->execute();
        $stmt->close();
        
        // Delete existing answer selections
        if (in_array($question['question_type'], ['Multiple Choice', 'True/False'])) {
            $stmt = $conn->prepare("
                DELETE FROM student_answer_selections
                WHERE response_id = ?
            ");
            $stmt->bind_param("i", $responseId);
            $stmt->execute();
            $stmt->close();
        }
        
        // Delete existing matching responses
        if ($question['question_type'] === 'Matching') {
            $stmt = $conn->prepare("
                DELETE FROM student_matching_responses
                WHERE response_id = ?
            ");
            $stmt->bind_param("i", $responseId);
            $stmt->execute();
            $stmt->close();
        }
        
        // Delete existing sequence responses
        if ($question['question_type'] === 'Ordering') {
            $stmt = $conn->prepare("
                DELETE FROM student_sequence_responses
                WHERE response_id = ?
            ");
            $stmt->bind_param("i", $responseId);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        // Create new response
        $stmt = $conn->prepare("
            INSERT INTO student_question_responses 
            (attempt_id, question_id, is_correct, points_awarded, answer_text, response_time)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iiidsi", $attemptId, $questionId, $isCorrect, $pointsAwarded, $answerText, $responseTime);
        $stmt->execute();
        $responseId = $conn->insert_id;
        $stmt->close();
    }

    // Save multiple choice selections
    if (in_array($question['question_type'], ['Multiple Choice', 'True/False']) && isset($_POST['answer_id'])) {
        $answerId = intval($_POST['answer_id']);
        $stmt = $conn->prepare("
            INSERT INTO student_answer_selections 
            (response_id, answer_id)
            VALUES (?, ?)
        ");
        $stmt->bind_param("ii", $responseId, $answerId);
        $stmt->execute();
        $stmt->close();
    }
    
    // Save matching responses
    if ($question['question_type'] === 'Matching' && !empty($pairs)) {
        foreach ($pairs as $pair) {
            $pairId = $pair['pair_id'];
            $postKey = 'match_' . $pairId;
            
            if (isset($_POST[$postKey]) && !empty($_POST[$postKey])) {
                $studentMatch = $_POST[$postKey];
                $isCorrectMatch = $studentMatch === $pair['right_item'];
                
                $stmt = $conn->prepare("
                    INSERT INTO student_matching_responses 
                    (response_id, pair_id, student_right_match, is_correct)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->bind_param("iisi", $responseId, $pairId, $studentMatch, $isCorrectMatch);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    
    // Save sequence responses
    if ($question['question_type'] === 'Ordering' && isset($_POST['sequence_order']) && is_array($_POST['sequence_order']) && !empty($items)) {
        $sequenceOrder = $_POST['sequence_order'];
        
        foreach ($sequenceOrder as $studentPosition => $itemId) {
            // Find the correct position for this item
            $correctPosition = 0;
            foreach ($items as $item) {
                if ($item['item_id'] == $itemId) {
                    $correctPosition = $item['correct_position'];
                    break;
                }
            }
            
            $isCorrectPosition = ($studentPosition + 1) == $correctPosition;
            
            $stmt = $conn->prepare("
                INSERT INTO student_sequence_responses 
                (response_id, item_id, student_position, is_correct)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("iiii", $responseId, $itemId, $studentPosition, $isCorrectPosition);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Update session data to mark this question as answered
    $sessionId = null;
    $sessionData = null;
    
    $stmt = $conn->prepare("
        SELECT session_id, session_data
        FROM quiz_sessions
        WHERE attempt_id = ? AND is_active = 1
    ");
    $stmt->bind_param("i", $attemptId);
    $stmt->execute();
    $sessionResult = $stmt->get_result();
    
    if ($sessionResult->num_rows > 0) {
        $session = $sessionResult->fetch_assoc();
        $sessionId = $session['session_id'];
        $sessionData = json_decode($session['session_data'], true) ?: [
            'question_order' => [],
            'current_question' => 0,
            'answers' => []
        ];
        
        // Mark this question as answered
        $sessionData['answers'][$questionId] = [
            'answered' => true,
            'timestamp' => time()
        ];
    }
    $stmt->close();
    
    // Only update session data if we have a valid session
    if ($sessionId && $sessionData) {
        $stmt = $conn->prepare("
            UPDATE quiz_sessions
            SET session_data = ?, last_activity = NOW()
            WHERE session_id = ?
        ");
        $sessionDataJson = json_encode($sessionData);
        $stmt->bind_param("si", $sessionDataJson, $sessionId);
        $stmt->execute();
        $stmt->close();
    }

    // Commit transaction
    $conn->commit();

    // Return success
    echo json_encode([
        'success' => true,
        'response_id' => $responseId,
        'is_correct' => $isCorrect,
        'points_awarded' => $pointsAwarded,
        'message' => 'Response saved successfully.'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}