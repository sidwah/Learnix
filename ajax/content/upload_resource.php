<?php
//ajax/content/upload_resource.php
require '../../backend/session_start.php';
require '../../backend/config.php';

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Validate required input
if (!isset($_POST['topic_id']) || !isset($_FILES['resource_file'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$topic_id = intval($_POST['topic_id']);
$instructor_id = $_SESSION['instructor_id'];
$user_id = $_SESSION['user_id'];

// Verify that the topic belongs to a section of a course assigned to the current instructor
$stmt = $conn->prepare("
    SELECT 
        st.section_id, 
        cs.course_id
    FROM 
        section_topics st
    JOIN 
        course_sections cs ON st.section_id = cs.section_id
    JOIN 
        course_instructors ci ON cs.course_id = ci.course_id
    WHERE 
        st.topic_id = ? AND
        ci.instructor_id = ? AND
        ci.deleted_at IS NULL
");
$stmt->bind_param("ii", $topic_id, $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
$topic_data = $result->fetch_assoc();
$stmt->close();

if (!$topic_data) {
    echo json_encode(['success' => false, 'message' => 'Topic not found or not authorized']);
    exit;
}

// Get file details
$file_tmp = $_FILES['resource_file']['tmp_name'];
$file_size = $_FILES['resource_file']['size'];
$file_name = $_FILES['resource_file']['name'];
$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

// Validate file size (max 10MB)
if ($file_size > 10 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File size exceeds 10MB limit']);
    exit;
}

// Validate file extension
$allowed_ext = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'zip', 'txt'];
if (!in_array($file_ext, $allowed_ext)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid file type. Allowed: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, ZIP, TXT'
    ]);
    exit;
}

// Create upload directory if not exists
$upload_dir = '../../uploads/resources/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Generate unique filename
$file_path = 'resource_topic_' . $topic_id . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $file_ext;
$upload_path = $upload_dir . $file_path;

// Move uploaded file
if (!move_uploaded_file($file_tmp, $upload_path)) {
    echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
    exit;
}

// Check if created_by column exists in the table
$column_check = $conn->query("SHOW COLUMNS FROM topic_resources LIKE 'created_by'");
$has_created_by = $column_check->num_rows > 0;

// Save resource to database - with or without user tracking based on column existence
if ($has_created_by) {
    // With user tracking
    $stmt = $conn->prepare("
        INSERT INTO topic_resources (topic_id, resource_path, created_at, created_by)
        VALUES (?, ?, NOW(), ?)
    ");
    $stmt->bind_param("isi", $topic_id, $file_path, $user_id);
} else {
    // Without user tracking
    $stmt = $conn->prepare("
        INSERT INTO topic_resources (topic_id, resource_path, created_at)
        VALUES (?, ?, NOW())
    ");
    $stmt->bind_param("is", $topic_id, $file_path);
}

$success = $stmt->execute();
$resource_id = $stmt->insert_id;
$stmt->close();

if ($success) {
    // Update course last modified timestamp
    $stmt = $conn->prepare("UPDATE courses SET updated_at = NOW() WHERE course_id = ?");
    $stmt->bind_param("i", $topic_data['course_id']);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Resource uploaded successfully',
        'resource_id' => $resource_id,
        'file_path' => $file_path,
        'file_name' => $file_name
    ]);
} else {
    // Delete the uploaded file
    if (file_exists($upload_path)) {
        unlink($upload_path);
    }
    
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}
?>