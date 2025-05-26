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
    $action = isset($input['action']) ? $input['action'] : '';
    
    if (!$course_id || !$action) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Verify user has access to this course
    $access_query = "SELECT c.*, d.department_id 
                     FROM courses c
                     INNER JOIN departments d ON c.department_id = d.department_id
                     INNER JOIN department_staff ds ON d.department_id = ds.department_id 
                     WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' 
                     AND ds.deleted_at IS NULL AND c.course_id = ? AND c.deleted_at IS NULL";
    
    $access_stmt = $conn->prepare($access_query);
    $access_stmt->bind_param("ii", $user_id, $course_id);
    $access_stmt->execute();
    $course_result = $access_stmt->get_result();
    
    if ($course_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Access denied or course not found']);
        exit();
    }
    
    $course = $course_result->fetch_assoc();
    
    // Check financial approval
    if (empty($course['financial_approval_date'])) {
        echo json_encode(['success' => false, 'message' => 'Course must be financially approved before review']);
        exit();
    }
    
    $conn->begin_transaction();
    
    switch ($action) {
        case 'start_review':
            if ($course['approval_status'] !== 'submitted_for_review') {
                throw new Exception('Course is not in the correct status for review');
            }
            
            // Update course status to under_review
            $update_query = "UPDATE courses SET approval_status = 'under_review', updated_at = NOW() WHERE course_id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("i", $course_id);
            $update_stmt->execute();
            
            // Add to review history
            $history_query = "INSERT INTO course_review_history (course_id, reviewed_by, previous_status, new_status, comments) VALUES (?, ?, 'submitted_for_review', 'under_review', 'Review started')";
            $history_stmt = $conn->prepare($history_query);
            $history_stmt->bind_param("ii", $course_id, $user_id);
            $history_stmt->execute();
            
            $message = 'Course review started successfully';
            break;
            
        case 'continue_review':
            if ($course['approval_status'] !== 'under_review') {
                throw new Exception('Course is not currently under review');
            }
            
            $message = 'Continuing course review';
            break;
            
        default:
            throw new Exception('Invalid action specified');
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    error_log("Error updating review status: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>