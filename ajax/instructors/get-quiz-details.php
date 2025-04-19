<?php
// Add these lines at the top of your get-quiz-details.php file:
error_reporting(0); // Turn off error reporting for production
header('Content-Type: application/json'); // Force JSON content type
require_once '../../backend/config.php';
require_once '../../backend/auth/session.php';

// Check if user is logged in and has instructor role
if (!isLoggedIn() || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get parameters
$quizId = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
$studentId = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;
$instructorId = $_SESSION['user_id'];

if (!$quizId || !$studentId) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

// Verify that the student is enrolled in the instructor's course
$verify_query = "SELECT EXISTS(
                    SELECT 1 FROM enrollments e
                    JOIN courses c ON e.course_id = c.course_id
                    WHERE e.user_id = ? AND c.instructor_id = ?
                ) as valid_student";
$stmt = $conn->prepare($verify_query);
$stmt->bind_param("ii", $studentId, $instructorId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row['valid_student']) {
    echo json_encode(['success' => false, 'message' => 'Student not found or not enrolled in your courses']);
    exit;
}

// Get quiz summary information
$quiz_summary_query = "SELECT 
                sq.quiz_id,
                sq.quiz_title,
                c.title as course_title,
                c.course_id,
                MAX(sqa.attempt_number) as attempts,
                MAX(sqa.score) as highest_score,
                AVG(sqa.score) as avg_score,
                SUM(sqa.time_spent) as total_time_spent
            FROM student_quiz_attempts sqa
            JOIN section_quizzes sq ON sqa.quiz_id = sq.quiz_id
            JOIN course_sections cs ON sq.section_id = cs.section_id
            JOIN courses c ON cs.course_id = c.course_id
            WHERE sqa.user_id = ? AND sqa.quiz_id = ? AND c.instructor_id = ?
            GROUP BY sq.quiz_id";
$stmt = $conn->prepare($quiz_summary_query);
$stmt->bind_param("iii", $studentId, $quizId, $instructorId);
$stmt->execute();
$result = $stmt->get_result();
$summary = $result->fetch_assoc();

if (!$summary) {
    echo json_encode(['success' => false, 'message' => 'Quiz data not found']);
    exit;
}

// Get all attempts for this quiz
$attempts_query = "SELECT 
                sqa.attempt_id,
                sqa.attempt_number,
                sqa.start_time,
                sqa.end_time,
                sqa.score,
                sqa.passed,
                sqa.time_spent
            FROM student_quiz_attempts sqa
            WHERE sqa.user_id = ? AND sqa.quiz_id = ?
            ORDER BY sqa.attempt_number DESC";
$stmt = $conn->prepare($attempts_query);
$stmt->bind_param("ii", $studentId, $quizId);
$stmt->execute();
$attempts_result = $stmt->get_result();
$attempts = [];
while ($attempt = $attempts_result->fetch_assoc()) {
    // Format date and time for display
    $date = new DateTime($attempt['end_time']);
    $attempt['formatted_date'] = $date->format('M d, Y g:i A');
    
    // Add a status based on passed
    $attempt['status'] = $attempt['passed'] ? 'Passed' : 'Failed';
    
    $attempts[] = $attempt;
}

// Get question performance data
$questions_query = "SELECT 
                qq.question_id,
                qq.question_text,
                qq.question_type,
                COUNT(sqr.response_id) as total_responses,
                SUM(CASE WHEN sqr.is_correct = 1 THEN 1 ELSE 0 END) as correct_responses,
                SUM(CASE WHEN sqr.is_correct = 0 THEN 1 ELSE 0 END) as incorrect_responses
            FROM quiz_questions qq
            LEFT JOIN student_question_responses sqr ON qq.question_id = sqr.question_id
            LEFT JOIN student_quiz_attempts sqa ON sqr.attempt_id = sqa.attempt_id
            WHERE qq.quiz_id = ? AND sqa.user_id = ?
            GROUP BY qq.question_id
            ORDER BY qq.question_order";
$stmt = $conn->prepare($questions_query);
$stmt->bind_param("ii", $quizId, $studentId);
$stmt->execute();
$questions_result = $stmt->get_result();
$questions = [];
while ($question = $questions_result->fetch_assoc()) {
    // Calculate success rate
    $totalResponses = $question['total_responses'];
    $question['success_rate'] = $totalResponses > 0 ? 
        round(($question['correct_responses'] / $totalResponses) * 100) : 0;
    
    // Truncate question text if too long
    if (strlen($question['question_text']) > 50) {
        $question['question_text'] = substr($question['question_text'], 0, 50) . '...';
    }
    
    $questions[] = $question;
}

// Format response
$response = [
    'success' => true,
    'quiz_id' => $summary['quiz_id'],
    'quiz_title' => $summary['quiz_title'],
    'course_title' => $summary['course_title'],
    'course_id' => $summary['course_id'],
    'attempts' => $summary['attempts'],
    'highest_score' => round($summary['highest_score'], 1),
    'avg_score' => round($summary['avg_score'], 1),
    'total_time_spent' => $summary['total_time_spent'],
    'attempts_list' => $attempts,
    'questions' => $questions
];

echo json_encode($response);
?>