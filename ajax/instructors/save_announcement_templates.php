<?php
require_once '../../backend/config.php';
require_once '../../backend/session_start.php';

// Check if user is logged in and is an instructor
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if required fields are present
if (!isset($_POST['title']) || !isset($_POST['content'])) {
    echo json_encode(['success' => false, 'message' => 'Title and content are required']);
    exit;
}

$title = trim($_POST['title']);
$content = $_POST['content'];
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : null;
$user_id = $_SESSION['user_id'];

try {
    if ($template_id) {
        // This is an update
        // First check if the template belongs to this instructor
        $stmt = $conn->prepare("SELECT * FROM announcement_templates WHERE template_id = ? AND created_by = ?");
        $stmt->bind_param("ii", $template_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Template not found or you do not have permission to edit it']);
            exit;
        }
        
        // Update the template
        $stmt = $conn->prepare("UPDATE announcement_templates SET title = ?, content = ?, updated_at = NOW() WHERE template_id = ?");
        $stmt->bind_param("ssi", $title, $content, $template_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Template updated successfully', 'template_id' => $template_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update template: ' . $stmt->error]);
        }
    } else {
        // This is a new template
        $stmt = $conn->prepare("INSERT INTO announcement_templates (title, content, created_by) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $title, $content, $user_id);
        
        if ($stmt->execute()) {
            $template_id = $conn->insert_id;
            echo json_encode(['success' => true, 'message' => 'Template created successfully', 'template_id' => $template_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create template: ' . $stmt->error]);
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>