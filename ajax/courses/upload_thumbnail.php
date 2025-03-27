<?php
require '../../backend/session_start.php';
require '../../backend/config.php';

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Validate required input
if (!isset($_POST['course_id']) || !isset($_FILES['thumbnail'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$course_id = intval($_POST['course_id']);
$file = $_FILES['thumbnail'];

// Verify that the course belongs to the current instructor
$stmt = $conn->prepare("SELECT instructor_id FROM courses WHERE course_id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();
$stmt->close();

if (!$course || $course['instructor_id'] != $_SESSION['instructor_id']) {
    echo json_encode(['success' => false, 'message' => 'Course not found or not authorized']);
    exit;
}

// Validate file
if ($file['error'] !== UPLOAD_ERR_OK) {
    $error_message = 'Upload error: ';
    switch ($file['error']) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $error_message .= 'File size exceeds limit.';
            break;
        case UPLOAD_ERR_PARTIAL:
            $error_message .= 'File was only partially uploaded.';
            break;
        case UPLOAD_ERR_NO_FILE:
            $error_message .= 'No file was uploaded.';
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            $error_message .= 'Missing temporary folder.';
            break;
        case UPLOAD_ERR_CANT_WRITE:
            $error_message .= 'Failed to write file to disk.';
            break;
        case UPLOAD_ERR_EXTENSION:
            $error_message .= 'File upload stopped by extension.';
            break;
        default:
            $error_message .= 'Unknown upload error.';
    }
    echo json_encode(['success' => false, 'message' => $error_message]);
    exit;
}

// Validate file type
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, and GIF are allowed.']);
    exit;
}

// Validate file size (max 2MB)
if ($file['size'] > 2 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File size exceeds the limit of 2MB.']);
    exit;
}

// Create the uploads directory if it doesn't exist
$upload_dir = '../../uploads/thumbnails/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Generate a unique filename
$timestamp = time();
$filename = "thumbnail_{$course_id}_{$timestamp}." . pathinfo($file['name'], PATHINFO_EXTENSION);
$file_path = $upload_dir . $filename;

// Move the uploaded file
if (!move_uploaded_file($file['tmp_name'], $file_path)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save the uploaded file.']);
    exit;
}

// Update the database with the new thumbnail
$stmt = $conn->prepare("UPDATE courses SET thumbnail = ?, updated_at = NOW() WHERE course_id = ?");
$stmt->bind_param("si", $filename, $course_id);
$success = $stmt->execute();
$stmt->close();

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Thumbnail uploaded successfully', 'file_path' => $filename]);
} else {
    // Delete the file if database update fails
    @unlink($file_path);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}
?>