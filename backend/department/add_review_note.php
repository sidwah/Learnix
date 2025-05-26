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
    $course_id = isset($input['course_id']) ? (int)$input['course_id'] : 0;
    $topic_id = isset($input['topic_id']) ? (int)$input['topic_id'] : null;
    $quiz_id = isset($input['quiz_id']) ? (int)$input['quiz_id'] : null;
    $note_type = isset($input['note_type']) ? $input['note_type'] : 'general';
    $note_content = isset($input['note_content']) ? trim($input['note_content']) : '';
    
    if (!$course_id || empty($note_content)) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Verify user has access to this course and get department info
    $access_query = "SELECT d.department_id, d.name as dept_name, c.title as course_title,
                            CONCAT(u.first_name, ' ', u.last_name) as reviewer_name
                     FROM departments d 
                     INNER JOIN department_staff ds ON d.department_id = ds.department_id 
                     INNER JOIN courses c ON c.department_id = d.department_id
                     INNER JOIN users u ON u.user_id = ?
                     WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' 
                     AND ds.deleted_at IS NULL AND c.course_id = ? AND c.deleted_at IS NULL";
    
    $access_stmt = $conn->prepare($access_query);
    $access_stmt->bind_param("iii", $user_id, $user_id, $course_id);
    $access_stmt->execute();
    $access_result = $access_stmt->get_result();
    
    if ($access_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit();
    }
    
    $course_data = $access_result->fetch_assoc();
    
    // Start transaction
    $conn->begin_transaction();
    
    // Insert review note
    $insert_query = "INSERT INTO review_notes (course_id, topic_id, quiz_id, reviewer_id, note_type, note_content) VALUES (?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("iiiiss", $course_id, $topic_id, $quiz_id, $user_id, $note_type, $note_content);
    
    if (!$insert_stmt->execute()) {
        throw new Exception('Failed to add review note');
    }
    
    $note_id = $conn->insert_id;
    
    // Get all instructors assigned to this course
    $instructors_query = "SELECT DISTINCT u.user_id, u.first_name, u.last_name
                         FROM course_instructors ci
                         INNER JOIN instructors i ON ci.instructor_id = i.instructor_id
                         INNER JOIN users u ON i.user_id = u.user_id
                         WHERE ci.course_id = ? AND ci.deleted_at IS NULL 
                         AND u.status = 'active' AND u.deleted_at IS NULL";
    
    $instructors_stmt = $conn->prepare($instructors_query);
    $instructors_stmt->bind_param("i", $course_id);
    $instructors_stmt->execute();
    $instructors_result = $instructors_stmt->get_result();
    
    // Prepare notification details with context
    $context_text = '';
    if ($topic_id) {
        // Get topic title
        $topic_query = "SELECT title FROM section_topics WHERE topic_id = ?";
        $topic_stmt = $conn->prepare($topic_query);
        $topic_stmt->bind_param("i", $topic_id);
        $topic_stmt->execute();
        $topic_result = $topic_stmt->get_result();
        if ($topic_row = $topic_result->fetch_assoc()) {
            $context_text = " on topic: " . $topic_row['title'];
        }
    } elseif ($quiz_id) {
        // Get quiz title
        $quiz_query = "SELECT quiz_title FROM section_quizzes WHERE quiz_id = ?";
        $quiz_stmt = $conn->prepare($quiz_query);
        $quiz_stmt->bind_param("i", $quiz_id);
        $quiz_stmt->execute();
        $quiz_result = $quiz_stmt->get_result();
        if ($quiz_row = $quiz_result->fetch_assoc()) {
            $context_text = " on quiz: " . $quiz_row['quiz_title'];
        }
    }
    
    $note_type_display = ucfirst($note_type);
    $notification_title = "New Review Note: {$note_type_display}";
    $notification_message = "{$course_data['reviewer_name']} added a {$note_type} review note for course '{$course_data['course_title']}'{$context_text}.";
    
    // Send notifications to all instructors
    $notification_query = "INSERT INTO user_notifications (user_id, type, title, message, related_id, related_type) VALUES (?, 'course_review_note', ?, ?, ?, 'course')";
    $notification_stmt = $conn->prepare($notification_query);
    
    $notifications_sent = 0;
    while ($instructor = $instructors_result->fetch_assoc()) {
        $notification_stmt->bind_param("issi", $instructor['user_id'], $notification_title, $notification_message, $course_id);
        if ($notification_stmt->execute()) {
            $notifications_sent++;
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Review note added successfully',
        'notifications_sent' => $notifications_sent,
        'note_id' => $note_id
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    error_log("Error adding review note: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while adding the note']);
}
?>
