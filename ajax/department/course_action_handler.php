<?php
// ajax/department/course_action_handler.php
// session_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../backend/config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in and has department head role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$action = $_POST['action'] ?? '';
$course_id = $_POST['course_id'] ?? 0;

if (!$action || !$course_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Route to appropriate backend file based on action
$response = null;

switch ($action) {
    case 'archive':
    case 'approve':
    case 'request_revisions':
    case 'reject':
    case 'unpublish':
        // Use course_actions.php
        $_POST['action'] = $action;
        $_POST['course_id'] = $course_id;
        
        ob_start();
        include '../../backend/department/course_actions.php';
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        // If response is not valid JSON, create a proper response
        if (!$response) {
            $response = [
                'success' => false,
                'message' => 'Invalid response from backend'
            ];
        }
        break;
        
    case 'view_details':
        // Get course details
        ob_start();
        include '../../backend/department/course_details.php';
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        // If response is not valid JSON, create a proper response
        if (!$response) {
            $response = [
                'success' => false,
                'message' => 'Failed to load course details'
            ];
        }
        break;
        
    default:
        http_response_code(400);
        $response = ['success' => false, 'message' => 'Unknown action'];
}

// Return response
echo json_encode($response);
?>