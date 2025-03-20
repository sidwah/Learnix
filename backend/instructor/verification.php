<?php
// Prevent any output before our JSON response
ob_start();

require_once("../config.php");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to send JSON response and exit
function sendJsonResponse($status, $message, $data = []) {
    // Clean any buffered output
    ob_end_clean();
    
    // Set proper content type header
    header('Content-Type: application/json');
    
    // Combine status and message with any additional data
    $response = array_merge([
        'status' => $status,
        'message' => $message
    ], $data);
    
    // Output JSON and exit
    echo json_encode($response);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJsonResponse('error', 'User not logged in');
}

$user_id = $_SESSION['user_id'];


// Database connection
// require_once '../../backend/config.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Check if the action is "verification"
if (!isset($_POST['action']) || $_POST['action'] !== 'verification') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid action'
    ]);
    exit;
}

// Validate credentials field
if (!isset($_POST['credentials']) || empty(trim($_POST['credentials']))) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Professional credentials are required'
    ]);
    exit;
}

$credentials = trim($_POST['credentials']);

// Check if we have any files uploaded
if (empty($_FILES['verification_docs']['name'][0])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'At least one supporting document is required'
    ]);
    exit;
}

// Get instructor ID from database or create instructor record if not exists
$instructor_id = null;
$query = "SELECT instructor_id FROM instructors WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $instructor_id = $row['instructor_id'];
} else {
    // Create instructor record
    $query = "INSERT INTO instructors (user_id, bio, created_at) VALUES (?, '', NOW())";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $instructor_id = mysqli_insert_id($conn);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to create instructor record: ' . mysqli_error($conn)
        ]);
        exit;
    }
}

// Process file uploads
$uploadDir = '../../uploads/verification-docs/';

// Create directory if it doesn't exist
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Array to store uploaded file paths
$uploadedFiles = [];
$allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
$maxFileSize = 5 * 1024 * 1024; // 5MB

// Process each uploaded file
foreach ($_FILES['verification_docs']['tmp_name'] as $key => $tmp_name) {
    // Check if file was uploaded without errors
    if ($_FILES['verification_docs']['error'][$key] !== UPLOAD_ERR_OK) {
        continue;
    }
    
    $fileName = $_FILES['verification_docs']['name'][$key];
    $fileSize = $_FILES['verification_docs']['size'][$key];
    $fileType = $_FILES['verification_docs']['type'][$key];
    
    // Validate file type
    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid file type. Only PDF, JPG, and PNG files are allowed.'
        ]);
        exit;
    }
    
    // Validate file size
    if ($fileSize > $maxFileSize) {
        echo json_encode([
            'status' => 'error',
            'message' => 'File size exceeds the maximum limit of 5MB.'
        ]);
        exit;
    }
    
    // Generate unique file name
    $newFileName = uniqid('verify_' . $instructor_id . '_') . '_' . $fileName;
    $uploadFilePath = $uploadDir . $newFileName;
    
    // Move the uploaded file
    if (move_uploaded_file($tmp_name, $uploadFilePath)) {
        $uploadedFiles[] = $newFileName;
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to upload file: ' . $fileName
        ]);
        exit;
    }
}

// If we have no successful uploads
if (empty($uploadedFiles)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No files were successfully uploaded'
    ]);
    exit;
}

// Insert verification request into database
$query = "INSERT INTO instructor_verification_requests 
          (instructor_id, credentials, status, submitted_at) 
          VALUES (?, ?, 'pending', NOW())";
$stmt = mysqli_prepare($conn, $query);
$status = 'pending';
mysqli_stmt_bind_param($stmt, "is", $instructor_id, $credentials);

if (!mysqli_stmt_execute($stmt)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to submit verification request: ' . mysqli_error($conn)
    ]);
    exit;
}

$verification_id = mysqli_insert_id($conn);

// Store uploaded document references
foreach ($uploadedFiles as $file) {
    $query = "INSERT INTO instructor_verification_documents 
              (verification_id, document_path, uploaded_at) 
              VALUES (?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "is", $verification_id, $file);
    mysqli_stmt_execute($stmt);
}

// Update instructor verification status
$query = "UPDATE instructors SET verification_status = 'pending' WHERE instructor_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $instructor_id);
mysqli_stmt_execute($stmt);

// Optional: Send notification email to admin
$admin_email = "admin@yourdomain.com";
$subject = "New Instructor Verification Request";
$message = "A new instructor verification request has been submitted.\n\n";
$message .= "Instructor ID: " . $instructor_id . "\n";
$message .= "Verification ID: " . $verification_id . "\n";
$message .= "Please review the request in the admin dashboard.";
$headers = "From: noreply@yourdomain.com";

// Uncomment to enable email notification
// mail($admin_email, $subject, $message, $headers);

// Return success response
// Instead of direct echo json_encode, use the function:
sendJsonResponse('success', 'Verification request submitted successfully. We will review your credentials shortly.', [
    'verification_id' => $verification_id
]);
exit;
?>