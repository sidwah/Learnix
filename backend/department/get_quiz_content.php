<?php
require_once '../config.php';

header('Content-Type: application/json');

// Check if user is logged in and has proper role
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $quiz_id = isset($input['quiz_id']) ? (int)$input['quiz_id'] : 0;
    $course_id = isset($input['course_id']) ? (int)$input['course_id'] : 0;
    
    if (!$quiz_id || !$course_id) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Verify user has access to this course
    $access_query = "SELECT d.department_id 
                     FROM departments d 
                     INNER JOIN department_staff ds ON d.department_id = ds.department_id 
                     INNER JOIN courses c ON c.department_id = d.department_id
                     INNER JOIN course_sections cs ON c.course_id = cs.course_id
                     INNER JOIN section_quizzes sq ON cs.section_id = sq.section_id
                     WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' 
                     AND ds.deleted_at IS NULL AND c.course_id = ? AND sq.quiz_id = ?  ";
    
    $access_stmt = $conn->prepare($access_query);
    $access_stmt->bind_param("iii", $user_id, $course_id, $quiz_id);
    $access_stmt->execute();
    
    if ($access_stmt->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit();
    }
    
    // Get quiz details
    $quiz_query = "SELECT 
                      sq.quiz_id,
                      sq.quiz_title,
                      sq.description,
                      sq.time_limit,
                      sq.attempts_allowed,
                      sq.pass_mark,
                      sq.randomize_questions,
                      sq.shuffle_answers,
                      sq.show_correct_answers,
                      sq.is_required,
                      sq.instruction,
                      cs.title as section_title,
                      cs.position as section_position
                   FROM section_quizzes sq
                   INNER JOIN course_sections cs ON sq.section_id = cs.section_id
                   WHERE sq.quiz_id = ? AND cs.course_id = ? ";
    
    $quiz_stmt = $conn->prepare($quiz_query);
    $quiz_stmt->bind_param("ii", $quiz_id, $course_id);
    $quiz_stmt->execute();
    $quiz_result = $quiz_stmt->get_result();
    
    if ($quiz_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Quiz not found']);
        exit();
    }
    
    $quiz_data = $quiz_result->fetch_assoc();
    
    // Get quiz questions for preview - First check if there's a related quiz in the quizzes table
    $related_quiz_query = "SELECT q.quiz_id as main_quiz_id 
                          FROM quizzes q 
                          INNER JOIN course_sections cs ON q.course_id = cs.course_id
                          WHERE cs.section_id = (SELECT section_id FROM section_quizzes WHERE quiz_id = ?)
                          AND q.title = (SELECT quiz_title FROM section_quizzes WHERE quiz_id = ?)
                          LIMIT 1";
    
    $related_quiz_stmt = $conn->prepare($related_quiz_query);
    $related_quiz_stmt->bind_param("ii", $quiz_id, $quiz_id);
    $related_quiz_stmt->execute();
    $related_quiz_result = $related_quiz_stmt->get_result();
    
    $questions = [];
    if ($related_quiz_result->num_rows > 0) {
        $related_quiz = $related_quiz_result->fetch_assoc();
        $main_quiz_id = $related_quiz['main_quiz_id'];
        
        // Get questions from the main quiz
        $questions_query = "SELECT 
                               qq.question_id,
                               qq.question_text,
                               qq.question_type,
                               qq.points,
                               qq.difficulty,
                               qq.explanation,
                               qq.question_order
                            FROM quiz_questions qq
                            WHERE qq.quiz_id = ?  
                            ORDER BY qq.question_order ASC, qq.question_id ASC";
        
        $questions_stmt = $conn->prepare($questions_query);
        $questions_stmt->bind_param("i", $main_quiz_id);
        $questions_stmt->execute();
        $questions_result = $questions_stmt->get_result();
        $questions = $questions_result->fetch_all(MYSQLI_ASSOC);
    }
    
    $quiz_data['questions'] = $questions;
    $quiz_data['question_count'] = count($questions);
    
    echo json_encode([
        'success' => true,
        'quiz' => $quiz_data
    ]);
    
} catch (Exception $e) {
    error_log("Error getting quiz content: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while loading quiz content']);
}
?>