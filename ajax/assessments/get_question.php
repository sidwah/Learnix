<?php
// Include session and authentication check
require '../../backend/session_start.php';

// Check if user is signed in and is an instructor
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Connect to database
require_once '../../backend/config.php';

// Start output buffering to capture any unexpected output
ob_start();

// Initialize response array
$response = ['success' => false];

try {
    // Validate required fields
    if (!isset($_POST['quiz_id']) || !isset($_POST['question_text']) || !isset($_POST['question_type']) || !isset($_POST['points'])) {
        throw new Exception('Missing required fields');
    }

    // Get form data
    $quiz_id = intval($_POST['quiz_id']);
    $question_id = isset($_POST['question_id']) && !empty($_POST['question_id']) ? intval($_POST['question_id']) : null;
    $question_text = trim($_POST['question_text']);
    $question_type = $_POST['question_type'];
    $points = intval($_POST['points']);
    $explanation = isset($_POST['explanation']) && !empty($_POST['explanation']) ? trim($_POST['explanation']) : null;
    
    // Map question type from frontend to database enum values
    // The ENUM in the database is likely: 
    // ENUM('Multiple Choice','True/False','Short_Answer','Matching','Ordering','Essay','Fill in the Blanks','Drag and Drop')
    if ($question_type === 'multiple_choice') {
        $question_type_enum = 'Multiple Choice';  // Exact match for the ENUM value
    } elseif ($question_type === 'true_false') {
        $question_type_enum = 'True/False';  // Exact match for the ENUM value
    } else {
        throw new Exception('Invalid question type');
    }

    // Validate data
    if (empty($question_text)) {
        throw new Exception('Question text is required');
    }
    
    if ($points <= 0) {
        throw new Exception('Points must be greater than 0');
    }

    // Verify instructor's access to the quiz
    // Get the course_id associated with this quiz via the section
    $access_check_query = "SELECT cs.course_id, c.instructor_id 
                          FROM section_quizzes sq 
                          JOIN course_sections cs ON sq.section_id = cs.section_id 
                          JOIN courses c ON cs.course_id = c.course_id 
                          WHERE sq.quiz_id = ?";
    $stmt = $conn->prepare($access_check_query);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $access_result = $stmt->get_result();
    
    if ($access_result->num_rows === 0) {
        throw new Exception('Quiz not found');
    }
    
    $access_data = $access_result->fetch_assoc();
    if ($access_data['instructor_id'] != $_SESSION['instructor_id']) {
        throw new Exception('You do not have permission to modify this quiz');
    }
    $stmt->close();

    // Start transaction
    $conn->begin_transaction();

    if ($question_id) {
        // Update existing question
        $update_query = "UPDATE quiz_questions SET 
                            question_text = ?, 
                            question_type = ?, 
                            points = ?,
                            explanation = ?
                        WHERE question_id = ? AND quiz_id = ?";
        
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssissi", $question_text, $question_type_enum, $points, $explanation, $question_id, $quiz_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update question: ' . $stmt->error);
        }
        $stmt->close();
        
        // Delete existing answers for this question to avoid orphaned data
        $delete_answers_query = "DELETE FROM quiz_answers WHERE question_id = ?";
        $stmt = $conn->prepare($delete_answers_query);
        $stmt->bind_param("i", $question_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to clear previous answers: ' . $stmt->error);
        }
        $stmt->close();
        
    } else {
        // Create new question
        $insert_query = "INSERT INTO quiz_questions (
                            quiz_id, 
                            question_text, 
                            question_type, 
                            points,
                            explanation,
                            created_at,
                            difficulty,
                            question_order
                        ) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP, 'Medium', (SELECT IFNULL(MAX(question_order), 0) + 1 FROM quiz_questions q2 WHERE q2.quiz_id = ?))";
        
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("issisi", $quiz_id, $question_text, $question_type_enum, $points, $explanation, $quiz_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create question: ' . $stmt->error);
        }
        
        $question_id = $conn->insert_id;
        $stmt->close();
    }
    
    // Process answers based on question type
    if ($question_type === 'multiple_choice') {
        // Validate we have answers data
        if (!isset($_POST['answers'])) {
            throw new Exception('No answers provided for multiple choice question');
        }
        
        $answers = json_decode($_POST['answers'], true);
        if (!is_array($answers) || count($answers) < 2) {
            throw new Exception('Multiple choice questions must have at least 2 answer options');
        }
        
        // Check if at least one answer is marked as correct
        $has_correct_answer = false;
        foreach ($answers as $answer) {
            if (isset($answer['is_correct']) && $answer['is_correct'] == 1) {
                $has_correct_answer = true;
                break;
            }
        }
        
        if (!$has_correct_answer) {
            throw new Exception('At least one answer must be marked as correct');
        }
        
        // Insert answers
        $insert_answer_query = "INSERT INTO quiz_answers (
                                    question_id, 
                                    answer_text, 
                                    is_correct, 
                                    created_at
                                ) VALUES (?, ?, ?, CURRENT_TIMESTAMP)";
        
        $stmt = $conn->prepare($insert_answer_query);
        
        foreach ($answers as $answer) {
            $answer_text = trim($answer['answer_text']);
            $is_correct = intval($answer['is_correct']);
            
            if (empty($answer_text)) {
                throw new Exception('Answer text cannot be empty');
            }
            
            $stmt->bind_param("isi", $question_id, $answer_text, $is_correct);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to save answer option: ' . $stmt->error);
            }
        }
        
        $stmt->close();
        
    } elseif ($question_type === 'true_false') {
        // Validate we have correct answer data
        if (!isset($_POST['correct_answer'])) {
            throw new Exception('No correct answer provided for true/false question');
        }
        
        $correct_answer = $_POST['correct_answer'] === 'true' ? 'true' : 'false';
        
        // Insert True option
        $insert_true_query = "INSERT INTO quiz_answers (
                                question_id, 
                                answer_text, 
                                is_correct, 
                                created_at
                            ) VALUES (?, 'True', ?, CURRENT_TIMESTAMP)";
        
        $stmt = $conn->prepare($insert_true_query);
        $true_is_correct = $correct_answer === 'true' ? 1 : 0;
        $stmt->bind_param("ii", $question_id, $true_is_correct);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to save True option: ' . $stmt->error);
        }
        $stmt->close();
        
        // Insert False option
        $insert_false_query = "INSERT INTO quiz_answers (
                                question_id, 
                                answer_text, 
                                is_correct, 
                                created_at
                            ) VALUES (?, 'False', ?, CURRENT_TIMESTAMP)";
        
        $stmt = $conn->prepare($insert_false_query);
        $false_is_correct = $correct_answer === 'false' ? 1 : 0;
        $stmt->bind_param("ii", $question_id, $false_is_correct);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to save False option: ' . $stmt->error);
        }
        $stmt->close();
    }

    // Commit transaction
    $conn->commit();
    
    $response['success'] = true;
    $response['message'] = $question_id ? 'Question updated successfully' : 'Question created successfully';
    $response['question_id'] = $question_id;
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn->ping()) {
        $conn->rollback();
    }
    
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

// Return JSON response
// Get any buffered output
$output = ob_get_clean();

// If there was unexpected output, log it for debugging
if (!empty($output)) {
    // Log the unexpected output for debugging
    error_log('Unexpected output in save_question.php: ' . $output);
}

// Set headers to prevent caching
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// Ensure proper content type
header('Content-Type: application/json');

// Send the JSON response
echo json_encode($response);
exit;