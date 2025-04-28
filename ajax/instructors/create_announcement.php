<?php
require_once '../../backend/config.php';
require '../../backend/session_start.php';

// Check if user is signed in as instructor
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$instructor_id = $_SESSION['instructor_id'];
$user_id = $_SESSION['user_id'];

// Process the form data
try {
    // Connect to database
    $conn = new mysqli('localhost', 'root', 'root', 'learnix_db');
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Extract form data
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $importance = $_POST['importance'] ?? 'Medium';
    $status = $_POST['status'] ?? 'Draft';
    $scheduled_date = !empty($_POST['scheduled_date']) ? $_POST['scheduled_date'] : null;
    $expiration_date = !empty($_POST['expiration_date']) ? $_POST['expiration_date'] : null;
    $target_type = $_POST['target_type'] ?? '';
    $course_id = !empty($_POST['course_id']) ? $_POST['course_id'] : null;
    $email_notification = isset($_POST['email_notification']) && $_POST['email_notification'] === '1';
    
    // Basic validation
    if (empty($title) || empty($content)) {
        echo json_encode(['success' => false, 'message' => 'Title and content are required']);
        exit;
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    // Insert into course_announcements table
    $stmt = $conn->prepare("INSERT INTO course_announcements (
        course_id, is_system_wide, title, content, importance, 
        status, created_by, scheduled_at, expires_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $is_system_wide = 0; // Instructors can't create system-wide announcements
    
    // Set course_id based on target type
    if ($target_type === 'all_courses') {
        $course_id = null; // Will be targeted through announcement_target_groups
    }
    
    $stmt->bind_param(
        "iissssiss", 
        $course_id, 
        $is_system_wide, 
        $title, 
        $content, 
        $importance, 
        $status, 
        $user_id, 
        $scheduled_date, 
        $expiration_date
    );
    
    $stmt->execute();
    $announcement_id = $conn->insert_id;
    $stmt->close();
    
    // If targeting all courses, add entries to announcement_target_groups
    if ($target_type === 'all_courses') {
        // Get all courses for this instructor
        $stmt = $conn->prepare("SELECT course_id FROM courses WHERE instructor_id = ?");
        $stmt->bind_param("i", $instructor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $stmt_insert = $conn->prepare("INSERT INTO announcement_target_groups (announcement_id, target_type, target_id) VALUES (?, 'Course', ?)");
        
        while ($row = $result->fetch_assoc()) {
            $course_id = $row['course_id'];
            $stmt_insert->bind_param("ii", $announcement_id, $course_id);
            $stmt_insert->execute();
        }
        
        $stmt->close();
        $stmt_insert->close();
    }
    
    // Handle file uploads
    if (isset($_FILES['files']) && !empty($_FILES['files']['name'][0])) {
        $upload_dir = '../../uploads/announcements/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $stmt_file = $conn->prepare("INSERT INTO announcement_attachments (
            announcement_id, file_path, file_name, file_size, file_type
        ) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($_FILES['files']['name'] as $key => $name) {
            if ($_FILES['files']['error'][$key] === 0) {
                $tmp_name = $_FILES['files']['tmp_name'][$key];
                $size = $_FILES['files']['size'][$key];
                $type = $_FILES['files']['type'][$key];
                
                // Generate unique filename
                $file_ext = pathinfo($name, PATHINFO_EXTENSION);
                $unique_name = uniqid('ann_') . '.' . $file_ext;
                $file_path = $upload_dir . $unique_name;
                
                // Move the uploaded file
                if (move_uploaded_file($tmp_name, $file_path)) {
                    $rel_path = 'uploads/announcements/' . $unique_name;
                    
                    $stmt_file->bind_param("issss", $announcement_id, $rel_path, $name, $size, $type);
                    $stmt_file->execute();
                }
            }
        }
        
        $stmt_file->close();
    }
    
    // Create statistics entry
    $stmt = $conn->prepare("INSERT INTO announcement_statistics (announcement_id) VALUES (?)");
    $stmt->bind_param("i", $announcement_id);
    $stmt->execute();
    $stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'announcement_id' => $announcement_id]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->ping()) {
        $conn->rollback();
    }
    
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error. Please try again later.']);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>