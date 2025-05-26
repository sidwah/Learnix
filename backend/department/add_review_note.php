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
    
    // Verify user has access to this course
    $access_query = "SELECT d.department_id 
                     FROM departments d 
                     INNER JOIN department_staff ds ON d.department_id = ds.department_id 
                     INNER JOIN courses c ON c.department_id = d.department_id
                     WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' 
                     AND ds.deleted_at IS NULL AND c.course_id = ? AND c.deleted_at IS NULL";
    
    $access_stmt = $conn->prepare($access_query);
    $access_stmt->bind_param("ii", $user_id, $course_id);
    $access_stmt->execute();
    
    if ($access_stmt->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit();
    }
    
    // Create review_notes table if it doesn't exist
    // $create_table_query = "CREATE TABLE IF NOT EXISTS review_notes (
    //     note_id INT AUTO_INCREMENT PRIMARY KEY,
    //     course_id INT NOT NULL,
    //     topic_id INT NULL,
    //     quiz_id INT NULL,
    //     reviewer_id INT NOT NULL,
    //     note_type ENUM('general', 'suggestion', 'concern', 'positive') DEFAULT 'general',
    //     note_content TEXT NOT NULL,
    //     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    //     FOREIGN KEY (course_id) REFERENCES courses(course_id),
    //     FOREIGN KEY (topic_id) REFERENCES section_topics(topic_id),
    //     FOREIGN KEY (quiz_id) REFERENCES section_quizzes(quiz_id),
    //     FOREIGN KEY (reviewer_id) REFERENCES users(user_id)
    // )";
    // $conn->query($create_table_query);
    
    // Insert review note
    $insert_query = "INSERT INTO review_notes (course_id, topic_id, quiz_id, reviewer_id, note_type, note_content) VALUES (?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("iiiiss", $course_id, $topic_id, $quiz_id, $user_id, $note_type, $note_content);
    
    if ($insert_stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Review note added successfully'
        ]);
    } else {
        throw new Exception('Failed to add review note');
    }
    
} catch (Exception $e) {
    error_log("Error adding review note: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while adding the note']);
}
?>