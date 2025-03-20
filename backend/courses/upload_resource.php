<?php
// upload_resource.php - Handles uploading individual resource files

// Start session and include config
require '../session_start.php';
require '../config.php';

// Set response header
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'file_path' => null
];

// Function to sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Session expired. Please login again.';
    echo json_encode($response);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

// Validate required fields
if (!isset($_POST['course_id']) || empty($_POST['course_id'])) {
    $response['message'] = 'Course ID is required.';
    echo json_encode($response);
    exit;
}

if (!isset($_POST['topic_id']) || empty($_POST['topic_id'])) {
    $response['message'] = 'Topic ID is required.';
    echo json_encode($response);
    exit;
}

if (!isset($_POST['file_type']) || empty($_POST['file_type'])) {
    $response['message'] = 'File type is required.';
    echo json_encode($response);
    exit;
}

// Validate file upload
if (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
    $response['message'] = 'No file uploaded.';
    echo json_encode($response);
    exit;
}

$courseId = intval($_POST['course_id']);
$topicId = intval($_POST['topic_id']);
$fileType = sanitizeInput($_POST['file_type']);

// Get instructor ID for the logged-in user
$userId = $_SESSION['user_id'];
$stmtInstructor = $conn->prepare("SELECT instructor_id FROM instructors WHERE user_id = ?");
$stmtInstructor->bind_param("i", $userId);
$stmtInstructor->execute();
$stmtInstructor->bind_result($instructorId);
$stmtInstructor->fetch();
$stmtInstructor->close();

if (!$instructorId) {
    $response['message'] = 'Instructor not found. Please make sure your account has instructor privileges.';
    echo json_encode($response);
    exit;
}

// Verify course belongs to this instructor
$stmtVerifyCourse = $conn->prepare("SELECT course_id FROM courses WHERE course_id = ? AND instructor_id = ?");
$stmtVerifyCourse->bind_param("ii", $courseId, $instructorId);
$stmtVerifyCourse->execute();
$stmtVerifyCourse->store_result();

if ($stmtVerifyCourse->num_rows === 0) {
    $response['message'] = 'You do not have permission to modify this course.';
    echo json_encode($response);
    exit;
}
$stmtVerifyCourse->close();

// Verify topic belongs to this course
$stmtVerifyTopic = $conn->prepare("
    SELECT t.topic_id 
    FROM section_topics t
    JOIN course_sections s ON t.section_id = s.section_id
    WHERE t.topic_id = ? AND s.course_id = ?
");
$stmtVerifyTopic->bind_param("ii", $topicId, $courseId);
$stmtVerifyTopic->execute();
$stmtVerifyTopic->store_result();

if ($stmtVerifyTopic->num_rows === 0) {
    $response['message'] = 'Topic does not belong to this course.';
    echo json_encode($response);
    exit;
}
$stmtVerifyTopic->close();

try {
    // Check file upload errors
    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        // Map PHP file upload error codes to meaningful messages
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form.',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.'
        ];
        
        $errorMessage = isset($uploadErrors[$_FILES['file']['error']]) ? 
                         $uploadErrors[$_FILES['file']['error']] : 
                         'Unknown upload error.';
                         
        throw new Exception($errorMessage);
    }
    
    // Get file information
    $fileName = $_FILES['file']['name'];
    $fileSize = $_FILES['file']['size'];
    $fileTmpName = $_FILES['file']['tmp_name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Validate file size (16MB limit)
    $maxFileSize = 16 * 1024 * 1024; // 16MB in bytes
    if ($fileSize > $maxFileSize) {
        throw new Exception('File size exceeds the maximum limit of 16MB.');
    }
    
    // Set allowed extensions based on file type
    $allowedExtensions = [];
    
    if ($fileType === 'resource') {
        $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'mp3', 'mp4'];
        $uploadDir = '../../uploads/resources/';
        $fileNamePrefix = 'resource_topic_';
    } else if ($fileType === 'video') {
        $allowedExtensions = ['mp4', 'webm', 'ogg'];
        $uploadDir = '../../uploads/videos/';
        $fileNamePrefix = 'video_topic_';
    } else {
        throw new Exception('Invalid file type specified.');
    }
    
    // Validate file extension
    if (!in_array($fileExtension, $allowedExtensions)) {
        throw new Exception('Invalid file type. Allowed types: ' . implode(', ', $allowedExtensions));
    }
    
    // Create upload directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception('Failed to create upload directory.');
        }
    }
    
    // Generate unique filename
    $uniqueFileName = $fileNamePrefix . $topicId . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $fileExtension;
    $filePath = $uploadDir . $uniqueFileName;
    
    // Move the uploaded file
    if (!move_uploaded_file($fileTmpName, $filePath)) {
        throw new Exception('Failed to move uploaded file.');
    }
    
    // Update database based on file type
    if ($fileType === 'resource') {
        // Insert into topic_resources table
        $stmtResource = $conn->prepare("INSERT INTO topic_resources (topic_id, resource_path) VALUES (?, ?)");
        $stmtResource->bind_param("is", $topicId, $uniqueFileName);
        $stmtResource->execute();
        $stmtResource->close();
    } else if ($fileType === 'video') {
        // Update the topic_content table to add the video file path
        $stmtContent = $conn->prepare("
            UPDATE topic_content 
            SET file_path = ? 
            WHERE topic_id = ? AND content_type = 'video'
        ");
        $stmtContent->bind_param("si", $uniqueFileName, $topicId);
        $stmtContent->execute();
        $stmtContent->close();
    }
    
    // Update course to mark resource upload step
    $stmtUpdateCourse = $conn->prepare("
        UPDATE courses 
        SET creation_step = 4 
        WHERE course_id = ? AND creation_step < 4
    ");
    $stmtUpdateCourse->bind_param("i", $courseId);
    $stmtUpdateCourse->execute();
    $stmtUpdateCourse->close();
    
    // Return success response
    $response['success'] = true;
    $response['message'] = 'File uploaded successfully.';
    $response['file_path'] = $uniqueFileName;
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    
    // Add detailed error info for debugging
    if (isset($_GET['debug']) && $_GET['debug'] === '1') {
        $response['debug'] = [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'post' => $_POST,
            'files' => $_FILES
        ];
    }
} finally {
    // Close connection
    $conn->close();
}

// Send JSON response
echo json_encode($response);
exit;