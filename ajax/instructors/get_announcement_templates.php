<?php
require_once '../../backend/config.php';
require_once '../../backend/session_start.php';

// Check if user is logged in and is an instructor
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if template_id is provided
if (!isset($_GET['template_id'])) {
    echo json_encode(['success' => false, 'message' => 'Template ID is required']);
    exit;
}

$template_id = intval($_GET['template_id']);
$user_id = $_SESSION['user_id'];

try {
    // Fetch the template
    $stmt = $conn->prepare("SELECT * FROM announcement_templates WHERE template_id = ? AND created_by = ? AND is_active = 1");
    $stmt->bind_param("ii", $template_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Template not found or you do not have permission to view it']);
        exit;
    }
    
    $template = $result->fetch_assoc();
    echo json_encode(['success' => true, 'template' => $template]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>