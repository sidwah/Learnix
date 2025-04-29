<?php
require_once '../../backend/config.php';
require_once '../../backend/session_start.php';

// Check if user is logged in and is an instructor
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if all required fields are present
if (!isset($_POST['announcement_id']) || !isset($_POST['title']) || !isset($_POST['content'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$announcement_id = intval($_POST['announcement_id']);
$title = trim($_POST['title']);
$content = $_POST['content'];
$importance = $_POST['importance'] ?? 'Medium';
$status = $_POST['status'] ?? 'Draft';
$scheduled_date = !empty($_POST['scheduled_date']) ? $_POST['scheduled_date'] : null;
$expiration_date = !empty($_POST['expiration_date']) ? $_POST['expiration_date'] : null;
$target_type = $_POST['target_type'] ?? 'all_courses';
$course_id = ($target_type === 'course' && !empty($_POST['course_id'])) ? intval($_POST['course_id']) : null;
$email_notification = isset($_POST['email_notification']) && $_POST['email_notification'] === '1';

// Process existing files (from edit)
$existing_files = isset($_POST['existing_files']) ? $_POST['existing_files'] : [];

try {
    // Start transaction
    $conn->begin_transaction();
    
    // First, check if the announcement belongs to this instructor
    $stmt = $conn->prepare("SELECT * FROM course_announcements WHERE announcement_id = ? AND created_by = ?");
    $stmt->bind_param("ii", $announcement_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Announcement not found or you do not have permission to edit it']);
        exit;
    }
    
    // Update the announcement
    $stmt = $conn->prepare("UPDATE course_announcements SET 
                           title = ?, 
                           content = ?, 
                           importance = ?, 
                           status = ?, 
                           scheduled_at = ?, 
                           expires_at = ?, 
                           course_id = ?,
                           is_system_wide = ?,
                           updated_at = NOW()
                           WHERE announcement_id = ?");
    
    $is_system_wide = ($target_type === 'all_courses') ? 1 : 0;
    $stmt->bind_param("ssssssiis", 
                     $title, 
                     $content, 
                     $importance, 
                     $status, 
                     $scheduled_date, 
                     $expiration_date, 
                     $course_id, 
                     $is_system_wide,
                     $announcement_id);
    
    if (!$stmt->execute()) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to update announcement: ' . $stmt->error]);
        exit;
    }
    
    // Handle attachments
    // First, get current attachments
    $stmt = $conn->prepare("SELECT attachment_id FROM announcement_attachments WHERE announcement_id = ?");
    $stmt->bind_param("i", $announcement_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $current_attachments = [];
    while ($row = $result->fetch_assoc()) {
        $current_attachments[] = $row['attachment_id'];
    }
    
    // Delete attachments that are no longer needed
    $to_delete = array_diff($current_attachments, $existing_files);
    foreach ($to_delete as $attachment_id) {
        // Get file path before deleting
        $stmt = $conn->prepare("SELECT file_path FROM announcement_attachments WHERE attachment_id = ?");
        $stmt->bind_param("i", $attachment_id);
        $stmt->execute();
        $file_result = $stmt->get_result();
        
        if ($file_row = $file_result->fetch_assoc()) {
            $file_path = '../../' . $file_row['file_path'];
            if (file_exists($file_path)) {
                unlink($file_path); // Delete the file
            }
        }
        
        // Delete from database
        $stmt = $conn->prepare("DELETE FROM announcement_attachments WHERE attachment_id = ?");
        $stmt->bind_param("i", $attachment_id);
        $stmt->execute();
    }
    
    // Process new file uploads
    if (isset($_FILES['files'])) {
        $upload_dir = '../../uploads/announcements/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $files = $_FILES['files'];
        $fileCount = count($files['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $tmp_name = $files['tmp_name'][$i];
                $name = basename($files['name'][$i]);
                $size = $files['size'][$i];
                $type = $files['type'][$i];
                
                // Generate unique file name
                $file_name = uniqid() . '_' . $name;
                $upload_path = $upload_dir . $file_name;
                $db_path = 'uploads/announcements/' . $file_name;
                
                if (move_uploaded_file($tmp_name, $upload_path)) {
                    // Save file info to database
                    $stmt = $conn->prepare("INSERT INTO announcement_attachments 
                                         (announcement_id, file_path, file_name, file_size, file_type) 
                                         VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("issis", $announcement_id, $db_path, $name, $size, $type);
                    $stmt->execute();
                }
            }
        }
    }
    
    // Commit the transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Announcement updated successfully']);
    
} catch (Exception $e) {
    // Roll back the transaction if anything failed
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>