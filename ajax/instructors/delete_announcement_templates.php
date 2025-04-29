<?php
require_once '../../backend/config.php';
require_once '../../backend/session_start.php';

// Check if user is logged in and is an instructor
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if template_id is provided
if (!isset($_POST['template_id'])) {
    echo json_encode(['success' => false, 'message' => 'Template ID is required']);
    exit;
}

$template_id = intval($_POST['template_id']);
$user_id = $_SESSION['user_id'];

try {
    // First check if the template belongs to this instructor
    $stmt = $conn->prepare("SELECT * FROM announcement_templates WHERE template_id = ? AND created_by = ?");
    $stmt->bind_param("ii", $template_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Template not found or you do not have permission to delete it']);
        exit;
    }
    
    // Instead of actually deleting, set is_active to 0
    $stmt = $conn->prepare("UPDATE announcement_templates SET is_active = 0 WHERE template_id = ?");
    $stmt->bind_param("i", $template_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Template deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete template: ' . $stmt->error]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>