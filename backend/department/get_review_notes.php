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
    
    if (!$course_id) {
        echo json_encode(['success' => false, 'message' => 'Missing course ID']);
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
    
    // Get review notes
    $notes_query = "SELECT 
                        rn.note_id,
                        rn.note_type,
                        rn.note_content,
                        rn.created_at,
                        st.title as topic_title,
                        cs.title as section_title,
                        sq.quiz_title
                    FROM review_notes rn
                    LEFT JOIN section_topics st ON rn.topic_id = st.topic_id
                    LEFT JOIN section_quizzes sq ON rn.quiz_id = sq.quiz_id
                    LEFT JOIN course_sections cs ON st.section_id = cs.section_id OR sq.section_id = cs.section_id
                    WHERE rn.course_id = ? AND rn.reviewer_id = ?
                    ORDER BY rn.created_at DESC";
    
    $notes_stmt = $conn->prepare($notes_query);
    $notes_stmt->bind_param("ii", $course_id, $user_id);
    $notes_stmt->execute();
    $notes_result = $notes_stmt->get_result();
    $notes = $notes_result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode([
        'success' => true,
        'notes' => $notes
    ]);
    
} catch (Exception $e) {
    error_log("Error getting review notes: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while loading review notes']);
}
?>