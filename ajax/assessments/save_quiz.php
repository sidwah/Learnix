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
    if (!isset($_POST['section_id']) || !isset($_POST['quiz_title']) || !isset($_POST['pass_mark'])) {
        throw new Exception('Missing required fields');
    }

    // Get form data
    $section_id = intval($_POST['section_id']);
    $course_id = intval($_POST['course_id']);
    $quiz_id = isset($_POST['quiz_id']) && !empty($_POST['quiz_id']) ? intval($_POST['quiz_id']) : null;
    $quiz_title = trim($_POST['quiz_title']);
    $instruction = isset($_POST['instruction']) ? trim($_POST['instruction']) : null;
    $pass_mark = intval($_POST['pass_mark']);
    $time_limit = isset($_POST['time_limit']) && !empty($_POST['time_limit']) ? intval($_POST['time_limit']) : null;
    $attempts_allowed = isset($_POST['attempts_allowed']) ? intval($_POST['attempts_allowed']) : 1;
    $randomize_questions = isset($_POST['randomize_questions']) ? intval($_POST['randomize_questions']) : 0;
    $show_correct_answers = isset($_POST['show_correct_answers']) ? intval($_POST['show_correct_answers']) : 0;
    $shuffle_answers = isset($_POST['shuffle_answers']) ? intval($_POST['shuffle_answers']) : 0;
    $is_required = isset($_POST['is_required']) ? intval($_POST['is_required']) : 1;

    // Validate instructor's access to this course and section
    $instructor_id = $_SESSION['instructor_id'];
    
    // Check course ownership
    $course_check_query = "SELECT * FROM courses WHERE course_id = ? AND instructor_id = ?";
    $stmt = $conn->prepare($course_check_query);
    $stmt->bind_param("ii", $course_id, $instructor_id);
    $stmt->execute();
    $course_result = $stmt->get_result();
    
    if ($course_result->num_rows === 0) {
        throw new Exception('You do not have permission to modify this course');
    }
    $stmt->close();
    
    // Check if section belongs to this course
    $section_check_query = "SELECT * FROM course_sections WHERE section_id = ? AND course_id = ?";
    $stmt = $conn->prepare($section_check_query);
    $stmt->bind_param("ii", $section_id, $course_id);
    $stmt->execute();
    $section_result = $stmt->get_result();
    
    if ($section_result->num_rows === 0) {
        throw new Exception('Invalid section for this course');
    }
    $stmt->close();

    // Validate data
    if (empty($quiz_title)) {
        throw new Exception('Quiz title is required');
    }
    
    if ($pass_mark < 0 || $pass_mark > 100) {
        throw new Exception('Pass mark must be between 0 and 100');
    }
    
    if ($time_limit !== null && $time_limit <= 0) {
        throw new Exception('Time limit must be greater than 0');
    }
    
    if ($attempts_allowed <= 0) {
        throw new Exception('Attempts allowed must be greater than 0');
    }

    // Start transaction
    $conn->begin_transaction();

    if ($quiz_id) {
        // Update existing quiz
        $update_query = "UPDATE section_quizzes SET 
                            quiz_title = ?, 
                            description = ?, 
                            randomize_questions = ?, 
                            pass_mark = ?,
                            time_limit = ?,
                            attempts_allowed = ?,
                            show_correct_answers = ?,
                            shuffle_answers = ?,
                            is_required = ?,
                            instruction = ?,
                            updated_at = CURRENT_TIMESTAMP
                        WHERE quiz_id = ?";
        
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssiiiiiissi", 
            $quiz_title, 
            $instruction, 
            $randomize_questions, 
            $pass_mark,
            $time_limit,
            $attempts_allowed,
            $show_correct_answers,
            $shuffle_answers,
            $is_required,
            $instruction,
            $quiz_id
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update quiz: ' . $stmt->error);
        }
        
        $response['success'] = true;
        $response['message'] = 'Quiz updated successfully';
        $response['quiz_id'] = $quiz_id;
        
        $stmt->close();
    } else {
        // Create new quiz
        $insert_query = "INSERT INTO section_quizzes (
                            section_id, 
                            quiz_title, 
                            description, 
                            randomize_questions, 
                            pass_mark,
                            time_limit,
                            attempts_allowed,
                            show_correct_answers,
                            shuffle_answers,
                            is_required,
                            instruction,
                            created_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
        
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("issiiiiiiis", 
            $section_id, 
            $quiz_title, 
            $instruction, 
            $randomize_questions, 
            $pass_mark,
            $time_limit,
            $attempts_allowed,
            $show_correct_answers,
            $shuffle_answers,
            $is_required,
            $instruction
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create quiz: ' . $stmt->error);
        }
        
        $quiz_id = $conn->insert_id;
        $response['success'] = true;
        $response['message'] = 'Quiz created successfully';
        $response['quiz_id'] = $quiz_id;
        
        $stmt->close();
    }

    // Commit transaction
    $conn->commit();
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
    error_log('Unexpected output in save_quiz.php: ' . $output);
}

// Set headers to prevent caching
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// Ensure proper content type
header('Content-Type: application/json');

// Send the JSON response
echo json_encode($response);
exit;