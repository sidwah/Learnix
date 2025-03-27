<?php
require_once('../../backend/config.php');
session_start();

// Check if user is logged in and is an instructor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get POST data
$quizId = isset($_POST['quiz_id']) && !empty($_POST['quiz_id']) ? $_POST['quiz_id'] : null;
$sectionId = $_POST['section_id'] ?? null;
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$passMark = $_POST['pass_mark'] ?? 70;
$timeLimit = !empty($_POST['time_limit']) ? $_POST['time_limit'] : null;
$randomizeQuestions = $_POST['randomize_questions'] ?? 0;
$showCorrectAnswers = $_POST['show_correct_answers'] ?? 0;

// Validate required fields
if (empty($sectionId) || empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Required fields are missing']);
    exit;
}

// Get course_id from section
$stmt = $conn->prepare("SELECT course_id FROM course_sections WHERE section_id = ?");
$stmt->bind_param("i", $sectionId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid section']);
    exit;
}

$courseId = $result->fetch_assoc()['course_id'];
$stmt->close();

// Check if user owns this course
$stmt = $conn->prepare("
    SELECT c.course_id 
    FROM courses c 
    JOIN instructors i ON c.instructor_id = i.instructor_id 
    WHERE c.course_id = ? AND i.user_id = ?
");
$stmt->bind_param("ii", $courseId, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to modify this course']);
    exit;
}
$stmt->close();

// Begin transaction
$conn->begin_transaction();

try {
    if ($quizId) {
        // Update existing quiz
        $stmt = $conn->prepare("UPDATE section_quizzes SET 
            title = ?, 
            description = ?, 
            pass_mark = ?, 
            time_limit = ?, 
            randomize_questions = ?, 
            show_correct_answers = ? 
            WHERE quiz_id = ?");
            
        $stmt->bind_param("ssiiiis", 
            $title, 
            $description, 
            $passMark, 
            $timeLimit, 
            $randomizeQuestions, 
            $showCorrectAnswers, 
            $quizId);
            
        $stmt->execute();
        $stmt->close();
        
        // Get associated topic_id
        $stmt = $conn->prepare("SELECT topic_id FROM section_quizzes WHERE quiz_id = ?");
        $stmt->bind_param("i", $quizId);
        $stmt->execute();
        $result = $stmt->get_result();
        $topicId = $result->fetch_assoc()['topic_id'];
        $stmt->close();
        
        // Update the topic title
        $stmt = $conn->prepare("UPDATE section_topics SET title = ? WHERE topic_id = ?");
        $stmt->bind_param("si", $title, $topicId);
        $stmt->execute();
        $stmt->close();
        
        $conn->commit();
        echo json_encode(['success' => true, 'quiz_id' => $quizId, 'topic_id' => $topicId]);
    } else {
        // Create a new topic for the quiz
        $stmt = $conn->prepare("INSERT INTO section_topics (section_id, title, position) VALUES (?, ?, 999)");
        $stmt->bind_param("is", $sectionId, $title);
        $stmt->execute();
        $topicId = $conn->insert_id;
        $stmt->close();
        
        // Create new quiz
        $stmt = $conn->prepare("INSERT INTO section_quizzes 
            (section_id, topic_id, quiz_title, description, pass_mark, time_limit, randomize_questions, show_correct_answers) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            
        $stmt->bind_param("iissiiii", 
            $sectionId, 
            $topicId, 
            $title, 
            $description, 
            $passMark, 
            $timeLimit, 
            $randomizeQuestions, 
            $showCorrectAnswers);
            
        $stmt->execute();
        $quizId = $conn->insert_id;
        $stmt->close();
        
        // Reorder topics to maintain positions
        $stmt = $conn->prepare("
            UPDATE section_topics 
            SET position = (SELECT COUNT(*) FROM (SELECT * FROM section_topics) as t WHERE section_id = ? AND topic_id <= ?)
            WHERE topic_id = ?
        ");
        $stmt->bind_param("iii", $sectionId, $topicId, $topicId);
        $stmt->execute();
        $stmt->close();
        
        $conn->commit();
        echo json_encode(['success' => true, 'quiz_id' => $quizId, 'topic_id' => $topicId]);
    }
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>