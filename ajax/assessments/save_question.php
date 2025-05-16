<?php
//ajax/assessments/save_question.php
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
    $section_quiz_id = intval($_POST['quiz_id']);
    $question_id = isset($_POST['question_id']) && !empty($_POST['question_id']) ? intval($_POST['question_id']) : null;
    $question_text = trim($_POST['question_text']);
    $question_type = $_POST['question_type'];
    $points = intval($_POST['points']);
    $explanation = isset($_POST['explanation']) && !empty($_POST['explanation']) ? trim($_POST['explanation']) : null;
    $instructor_id = $_SESSION['instructor_id'];
    $user_id = $_SESSION['user_id'];
    
    // Check for different variants of question type
    // We'll try different formats until we find one that works
    $question_type_variants = [
        'multiple_choice' => [
            'Multiple Choice',
            'Multiple_Choice',
            'MultipleChoice',
            'Multiple-Choice',
            'MULTIPLE CHOICE'
        ],
        'true_false' => [
            'True/False',
            'True_False',
            'TrueFalse',
            'True-False',
            'TRUE FALSE'
        ]
    ];
    
    if ($question_type === 'multiple_choice') {
        $question_type_enum = $question_type_variants['multiple_choice'][0]; // Start with first variant
    } elseif ($question_type === 'true_false') {
        $question_type_enum = $question_type_variants['true_false'][0]; // Start with first variant
    } else {
        throw new Exception('Invalid question type');
    }
    
    // Verify instructor's access to the quiz using course_instructors
    $access_check_query = "
        SELECT 
            sq.section_id, 
            cs.course_id
        FROM 
            section_quizzes sq
        JOIN 
            course_sections cs ON sq.section_id = cs.section_id
        JOIN 
            course_instructors ci ON cs.course_id = ci.course_id
        WHERE 
            sq.quiz_id = ? AND
            ci.instructor_id = ? AND
            ci.deleted_at IS NULL
    ";
    $stmt = $conn->prepare($access_check_query);
    $stmt->bind_param("ii", $section_quiz_id, $instructor_id);
    $stmt->execute();
    $access_result = $stmt->get_result();
    
    if ($access_result->num_rows === 0) {
        throw new Exception('Quiz not found or you do not have permission to modify it');
    }
    $access_data = $access_result->fetch_assoc();
    $stmt->close();
    
    // Validate data
    if (empty($question_text)) {
        throw new Exception('Question text is required');
    }
    if ($points <= 0) {
        throw new Exception('Points must be greater than 0');
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    // Check if there's an entry in the quizzes table for this section_quiz_id
    $check_quiz_query = "SELECT quiz_id FROM quizzes WHERE quiz_id = ?";
    $stmt = $conn->prepare($check_quiz_query);
    $stmt->bind_param("i", $section_quiz_id);
    $stmt->execute();
    $quiz_result = $stmt->get_result();
    $stmt->close();
    
    if ($quiz_result->num_rows === 0) {
        // Need to create an entry in the quizzes table
        $sq_query = "SELECT * FROM section_quizzes WHERE quiz_id = ?";
        $stmt = $conn->prepare($sq_query);
        $stmt->bind_param("i", $section_quiz_id);
        $stmt->execute();
        $sq_result = $stmt->get_result();
        
        if ($sq_result->num_rows === 0) {
            throw new Exception('Section quiz not found');
        }
        $sq_data = $sq_result->fetch_assoc();
        $stmt->close();
        
        // Insert into quizzes table using the same ID
        $insert_quiz = "INSERT INTO quizzes (quiz_id, course_id, title)
                        VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_quiz);
        $stmt->bind_param("iis", $section_quiz_id, $access_data['course_id'], $sq_data['quiz_title']);
        // Try to execute, but continue even if it fails (in case the entry was created by another process)
        $stmt->execute();
        $stmt->close();
    }
    
    // Process the question
    if ($question_id) {
        // For each variant of the question type, try to update
        $success = false;
        $last_error = '';
        $variants = $question_type === 'multiple_choice' ?
            $question_type_variants['multiple_choice'] :
            $question_type_variants['true_false'];
            
        foreach ($variants as $variant) {
            // Update existing question with user tracking
            $update_query = "UPDATE quiz_questions SET
                question_text = ?,
                question_type = ?,
                points = ?,
                explanation = ?,
                updated_at = CURRENT_TIMESTAMP,
                updated_by = ?
            WHERE question_id = ? AND quiz_id = ?";
            
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssissii", $question_text, $variant, $points, $explanation, $user_id, $question_id, $section_quiz_id);
            
            if ($stmt->execute()) {
                $success = true;
                $question_type_enum = $variant; // Remember the successful variant
                $stmt->close();
                break;
            } else {
                $last_error = $stmt->error;
                $stmt->close();
            }
        }
        
        if (!$success) {
            throw new Exception('Failed to update question after trying all type variants. Last error: ' . $last_error);
        }
        
        // Delete existing answers for this question to avoid orphaned data
        $delete_answers_query = "DELETE FROM quiz_answers WHERE question_id = ?";
        $stmt = $conn->prepare($delete_answers_query);
        $stmt->bind_param("i", $question_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to clear previous answers: ' . $stmt->error);
        }
        $stmt->close();
    } else {
        // For each variant of the question type, try to insert
        $success = false;
        $last_error = '';
        $variants = $question_type === 'multiple_choice' ?
            $question_type_variants['multiple_choice'] :
            $question_type_variants['true_false'];
            
        foreach ($variants as $variant) {
            // Create a temporary table of question_order values to avoid subquery issues
            $create_temp_table = "CREATE TEMPORARY TABLE IF NOT EXISTS temp_question_order AS
                                  SELECT question_order FROM quiz_questions WHERE quiz_id = ?";
            $stmt = $conn->prepare($create_temp_table);
            $stmt->bind_param("i", $section_quiz_id);
            $stmt->execute();
            $stmt->close();
            
            // Get the max order
            $max_order_query = "SELECT IFNULL(MAX(question_order), 0) + 1 AS next_order FROM temp_question_order";
            $result = $conn->query($max_order_query);
            $next_order = $result->fetch_assoc()['next_order'];
            
            // Create new question with user tracking
            $insert_query = "INSERT INTO quiz_questions (
                quiz_id,
                question_text,
                question_type,
                points,
                explanation,
                created_at,
                created_by,
                difficulty,
                question_order
            ) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP, ?, 'Medium', ?)";
            
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("issiisi", $section_quiz_id, $question_text, $variant, $points, $explanation, $user_id, $next_order);
            
            if ($stmt->execute()) {
                $question_id = $conn->insert_id;
                $success = true;
                $question_type_enum = $variant; // Remember the successful variant
                $stmt->close();
                
                // Drop the temporary table
                $conn->query("DROP TEMPORARY TABLE IF EXISTS temp_question_order");
                break;
            } else {
                $last_error = $stmt->error;
                $stmt->close();
                
                // Drop the temporary table
                $conn->query("DROP TEMPORARY TABLE IF EXISTS temp_question_order");
            }
        }
        
        if (!$success) {
            throw new Exception('Failed to create question after trying all type variants. Last error: ' . $last_error);
        }
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
        
        // Insert answers with user tracking
        $insert_answer_query = "INSERT INTO quiz_answers (
            question_id,
            answer_text,
            is_correct,
            created_at,
            created_by
        ) VALUES (?, ?, ?, CURRENT_TIMESTAMP, ?)";
        
        $stmt = $conn->prepare($insert_answer_query);
        
        foreach ($answers as $answer) {
            $answer_text = trim($answer['answer_text']);
            $is_correct = intval($answer['is_correct']);
            
            if (empty($answer_text)) {
                throw new Exception('Answer text cannot be empty');
            }
            
            $stmt->bind_param("isii", $question_id, $answer_text, $is_correct, $user_id);
            
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
        
        // Insert True option with user tracking
        $insert_true_query = "INSERT INTO quiz_answers (
            question_id,
            answer_text,
            is_correct,
            created_at,
            created_by
        ) VALUES (?, 'True', ?, CURRENT_TIMESTAMP, ?)";
        
        $stmt = $conn->prepare($insert_true_query);
        $true_is_correct = $correct_answer === 'true' ? 1 : 0;
        $stmt->bind_param("iii", $question_id, $true_is_correct, $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to save True option: ' . $stmt->error);
        }
        $stmt->close();
        
        // Insert False option with user tracking
        $insert_false_query = "INSERT INTO quiz_answers (
            question_id,
            answer_text,
            is_correct,
            created_at,
            created_by
        ) VALUES (?, 'False', ?, CURRENT_TIMESTAMP, ?)";
        
        $stmt = $conn->prepare($insert_false_query);
        $false_is_correct = $correct_answer === 'false' ? 1 : 0;
        $stmt->bind_param("iii", $question_id, $false_is_correct, $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to save False option: ' . $stmt->error);
        }
        $stmt->close();
    }
    
    // Commit transaction
    $conn->commit();
    
    // Add successful values to response for debugging
    $response['debug_type_used'] = $question_type_enum;
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