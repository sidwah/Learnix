<?php
//ajax/assessments/delete_quiz.php
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
    if (!isset($_POST['quiz_id'])) {
        throw new Exception('Missing quiz ID');
    }

    $quiz_id = intval($_POST['quiz_id']);
    $instructor_id = $_SESSION['instructor_id'];
    
    // Verify instructor's access to the quiz using course_instructors table
    $access_check_query = "
        SELECT 
            sq.quiz_id, 
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
    $stmt->bind_param("ii", $quiz_id, $instructor_id);
    $stmt->execute();
    $access_result = $stmt->get_result();
    
    if ($access_result->num_rows === 0) {
        throw new Exception('Quiz not found or you do not have permission to delete it');
    }
    
    $access_data = $access_result->fetch_assoc();
    $stmt->close();
    
    // Start transaction
    $conn->begin_transaction();

    // First get all questions for this quiz
    $questions_query = "SELECT question_id FROM quiz_questions WHERE quiz_id = ?";
    $stmt = $conn->prepare($questions_query);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $questions_result = $stmt->get_result();
    $question_ids = [];
    
    while ($row = $questions_result->fetch_assoc()) {
        $question_ids[] = $row['question_id'];
    }
    $stmt->close();
    
    // Delete answers for all questions
    if (!empty($question_ids)) {
        foreach ($question_ids as $question_id) {
            $delete_answers_query = "DELETE FROM quiz_answers WHERE question_id = ?";
            $stmt = $conn->prepare($delete_answers_query);
            $stmt->bind_param("i", $question_id);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to delete answers for question ID ' . $question_id);
            }
            $stmt->close();
        }
    }
    
    // Delete all questions for this quiz
    $delete_questions_query = "DELETE FROM quiz_questions WHERE quiz_id = ?";
    $stmt = $conn->prepare($delete_questions_query);
    $stmt->bind_param("i", $quiz_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to delete quiz questions');
    }
    $stmt->close();
    
    // Delete the quiz itself
    $delete_quiz_query = "DELETE FROM section_quizzes WHERE quiz_id = ?";
    $stmt = $conn->prepare($delete_quiz_query);
    $stmt->bind_param("i", $quiz_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to delete quiz');
    }
    $stmt->close();
    
    // Delete entry from quizzes table if it exists
    $delete_from_quizzes = "DELETE FROM quizzes WHERE quiz_id = ?";
    $stmt = $conn->prepare($delete_from_quizzes);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute(); // Not checking result as this may not always exist
    $stmt->close();
    
    // Delete any student quiz attempts
    $delete_attempts_query = "DELETE FROM student_quiz_attempts WHERE quiz_id = ?";
    $stmt = $conn->prepare($delete_attempts_query);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute(); // Not checking result as there may not be any attempts
    $stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    $response['success'] = true;
    $response['message'] = 'Quiz deleted successfully';
    
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
    error_log('Unexpected output in delete_quiz.php: ' . $output);
}

// Set headers to prevent caching
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// Ensure proper content type
header('Content-Type: application/json');

// Send the JSON response
echo json_encode($response);
exit;