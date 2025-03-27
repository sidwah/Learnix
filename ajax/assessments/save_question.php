<?php
// Include necessary files
require_once '../../backend/config.php';
session_start();

// Check if user is logged in and is an instructor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Get instructor_id from user_id
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT instructor_id FROM instructors WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Instructor not found']);
    exit;
}

$instructor = $result->fetch_assoc();
$instructor_id = $instructor['instructor_id'];

// Validate input data
if (!isset($_POST['quiz_id']) || !isset($_POST['question_type']) || !isset($_POST['question_text'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

$question_id = isset($_POST['question_id']) ? intval($_POST['question_id']) : 0;
$quiz_id = intval($_POST['quiz_id']);
$question_type = $_POST['question_type'];
$question_text = $_POST['question_text'];
$points = isset($_POST['points']) ? intval($_POST['points']) : 1;
$explanation = isset($_POST['explanation']) ? $_POST['explanation'] : null;
$difficulty = isset($_POST['difficulty']) ? $_POST['difficulty'] : 'Medium';
$question_order = isset($_POST['question_order']) ? intval($_POST['question_order']) : 0;

// Verify that the quiz belongs to the instructor's course
$stmt = $conn->prepare("
    SELECT sq.quiz_id 
    FROM section_quizzes sq 
    JOIN course_sections s ON sq.section_id = s.section_id 
    JOIN courses c ON s.course_id = c.course_id 
    WHERE sq.quiz_id = ? AND c.instructor_id = ?
");
$stmt->bind_param("ii", $quiz_id, $instructor_id);
$stmt->execute();
$quiz_result = $stmt->get_result();

if ($quiz_result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Quiz not found or not authorized']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    if ($question_id > 0) {
        // Update existing question
        $stmt = $conn->prepare("
            UPDATE quiz_questions 
            SET question_text = ?, question_type = ?, points = ?, explanation = ?, difficulty = ?, question_order = ? 
            WHERE question_id = ? AND quiz_id = ?
        ");
        $stmt->bind_param("ssississi", $question_text, $question_type, $points, $explanation, $difficulty, $question_order, $question_id, $quiz_id);
        $stmt->execute();
        
        // Delete existing answers
        $stmt = $conn->prepare("DELETE FROM quiz_answers WHERE question_id = ?");
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
    } else {
        // Insert new question
        $stmt = $conn->prepare("
            INSERT INTO quiz_questions (quiz_id, question_text, question_type, points, explanation, difficulty, question_order)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issisis", $quiz_id, $question_text, $question_type, $points, $explanation, $difficulty, $question_order);
        $stmt->execute();
        
        $question_id = $conn->insert_id;
    }
    
    // Handle answers based on question type
    if ($question_type === 'Multiple Choice') {
        // Get answer texts and correct answer index
        $answer_texts = isset($_POST['answer_text']) ? $_POST['answer_text'] : [];
        $correct_answer = isset($_POST['correct_answer']) ? intval($_POST['correct_answer']) : -1;
        
        // Make sure we have answers and a valid correct answer
        if (empty($answer_texts) || $correct_answer < 0 || $correct_answer >= count($answer_texts)) {
            throw new Exception('Missing or invalid answer options');
        }
        
        // Insert answers
        foreach ($answer_texts as $index => $text) {
            $is_correct = ($index == $correct_answer) ? 1 : 0;
            
            $stmt = $conn->prepare("
                INSERT INTO quiz_answers (question_id, answer_text, is_correct)
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param("isi", $question_id, $text, $is_correct);
            $stmt->execute();
        }
    } else if ($question_type === 'True/False') {
        // Get correct answer for true/false
        $tf_correct = isset($_POST['tf_correct_answer']) ? $_POST['tf_correct_answer'] : null;
        
        if ($tf_correct === null) {
            throw new Exception('Missing correct answer for True/False question');
        }
        
        // Insert True answer
        $is_true_correct = ($tf_correct === 'true') ? 1 : 0;
        $stmt = $conn->prepare("
            INSERT INTO quiz_answers (question_id, answer_text, is_correct)
            VALUES (?, 'True', ?)
        ");
        $stmt->bind_param("ii", $question_id, $is_true_correct);
        $stmt->execute();
        
        // Insert False answer
        $is_false_correct = ($tf_correct === 'false') ? 1 : 0;
        $stmt = $conn->prepare("
            INSERT INTO quiz_answers (question_id, answer_text, is_correct)
            VALUES (?, 'False', ?)
        ");
        $stmt->bind_param("ii", $question_id, $is_false_correct);
        $stmt->execute();
    } else {
        throw new Exception('Unsupported question type');
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => $question_id > 0 ? 'Question updated successfully' : 'Question created successfully',
        'question_id' => $question_id
    ]);
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>