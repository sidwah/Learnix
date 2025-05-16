<?php
//ajax/assessments/delete_question.php
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
    if (!isset($_POST['question_id'])) {
        throw new Exception('Missing question ID');
    }

    $question_id = intval($_POST['question_id']);
    $instructor_id = $_SESSION['instructor_id'];
    
    // Verify instructor's access to the question using course_instructors
    $access_check_query = "SELECT qq.quiz_id, sq.section_id, cs.course_id 
                           FROM quiz_questions qq
                           JOIN section_quizzes sq ON qq.quiz_id = sq.quiz_id
                           JOIN course_sections cs ON sq.section_id = cs.section_id
                           JOIN course_instructors ci ON cs.course_id = ci.course_id
                           WHERE qq.question_id = ?
                           AND ci.instructor_id = ?
                           AND ci.deleted_at IS NULL";
                           
    $stmt = $conn->prepare($access_check_query);
    $stmt->bind_param("ii", $question_id, $instructor_id);
    $stmt->execute();
    $access_result = $stmt->get_result();
    
    if ($access_result->num_rows === 0) {
        throw new Exception('Question not found or you do not have permission to delete it');
    }
    
    $access_data = $access_result->fetch_assoc();
    $stmt->close();
    
    // Start transaction
    $conn->begin_transaction();
    
    // Delete answers for this question first (foreign key constraint)
    $delete_answers_query = "DELETE FROM quiz_answers WHERE question_id = ?";
    $stmt = $conn->prepare($delete_answers_query);
    $stmt->bind_param("i", $question_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to delete answers: ' . $stmt->error);
    }
    $stmt->close();
    
    // Delete the question
    $delete_question_query = "DELETE FROM quiz_questions WHERE question_id = ?";
    $stmt = $conn->prepare($delete_question_query);
    $stmt->bind_param("i", $question_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to delete question: ' . $stmt->error);
    }
    $stmt->close();
    
    // Get the quiz_id
    $quiz_id = $access_data['quiz_id'];
    
    // Reorganize the remaining questions' order - fixed query
    // First initialize the position counter
    $conn->query("SET @pos := 0");
    
    // Then update the question orders
    $reorder_query = "UPDATE quiz_questions 
                      SET question_order = (@pos := @pos + 1) 
                      WHERE quiz_id = ? 
                      ORDER BY question_order";
                      
    $stmt = $conn->prepare($reorder_query);
    $stmt->bind_param("i", $quiz_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to reorder questions: ' . $stmt->error);
    }
    $stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    $response['success'] = true;
    $response['message'] = 'Question deleted successfully';
    
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
    error_log('Unexpected output in delete_question.php: ' . $output);
}

// Set headers to prevent caching
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// Ensure proper content type
header('Content-Type: application/json');

// Send the JSON response
echo json_encode($response);
exit;