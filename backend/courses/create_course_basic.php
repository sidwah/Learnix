<?php
/**
 * Backend script for handling basic course details (Step 1)
 * File: ../backend/courses/create_course_basic.php
 */

// Start session and include config
require '../session_start.php';
require '../config.php';

// Set response header
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'course_id' => null
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
$requiredFields = ['courseTitle', 'shortDescription', 'fullDescription', 'subcategory'];
$missingFields = [];

foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        $missingFields[] = $field;
    }
}

if (count($missingFields) > 0) {
    $response['message'] = 'Missing required fields: ' . implode(', ', $missingFields);
    echo json_encode($response);
    exit;
}

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

// Get and sanitize input data
$courseTitle = sanitizeInput($_POST['courseTitle']);
$shortDescription = sanitizeInput($_POST['shortDescription']);
$fullDescription = sanitizeInput($_POST['fullDescription']);
$subcategoryId = sanitizeInput($_POST['subcategory']);

// Validate subcategory exists
$stmtCheckSubcategory = $conn->prepare("SELECT subcategory_id FROM subcategories WHERE subcategory_id = ?");
$stmtCheckSubcategory->bind_param("i", $subcategoryId);
$stmtCheckSubcategory->execute();
$stmtCheckSubcategory->store_result();

if ($stmtCheckSubcategory->num_rows === 0) {
    $stmtCheckSubcategory->close();
    $response['message'] = 'Invalid subcategory selected.';
    echo json_encode($response);
    exit;
}
$stmtCheckSubcategory->close();

// Start transaction
$conn->begin_transaction();

try {
    // Check if we're updating an existing course or creating a new one
    $courseId = isset($_POST['course_id']) ? intval($_POST['course_id']) : null;
    
    if ($courseId) {
        // Verify course belongs to this instructor
        $stmtVerifyCourse = $conn->prepare("SELECT course_id FROM courses WHERE course_id = ? AND instructor_id = ?");
        $stmtVerifyCourse->bind_param("ii", $courseId, $instructorId);
        $stmtVerifyCourse->execute();
        $stmtVerifyCourse->store_result();
        
        if ($stmtVerifyCourse->num_rows === 0) {
            $stmtVerifyCourse->close();
            throw new Exception("You don't have permission to update this course.");
        }
        $stmtVerifyCourse->close();
        
        // Update existing course
        $stmtCourse = $conn->prepare("
            UPDATE courses 
            SET title = ?, short_description = ?, full_description = ?, subcategory_id = ?
            WHERE course_id = ? AND instructor_id = ?
        ");
        $stmtCourse->bind_param("sssiii", $courseTitle, $shortDescription, $fullDescription, $subcategoryId, $courseId, $instructorId);
        $stmtCourse->execute();
        
        if ($stmtCourse->affected_rows === 0 && $stmtCourse->errno !== 0) {
            throw new Exception("Failed to update course: " . $stmtCourse->error);
        }
        $stmtCourse->close();
    } else {
        // Insert new course
        $stmtCourse = $conn->prepare("
            INSERT INTO courses (instructor_id, title, short_description, full_description, subcategory_id, status)
            VALUES (?, ?, ?, ?, ?, 'Draft')
        ");
        $stmtCourse->bind_param("isssi", $instructorId, $courseTitle, $shortDescription, $fullDescription, $subcategoryId);
        $stmtCourse->execute();
        $courseId = $stmtCourse->insert_id;
        $stmtCourse->close();
        
        if (!$courseId) {
            throw new Exception("Failed to create course.");
        }
    }
    
    // Handle learning outcomes
    if (isset($_POST['learningOutcomes']) && is_array($_POST['learningOutcomes'])) {
        // Delete existing outcomes if this is an update
        if (isset($_POST['course_id'])) {
            $stmtDeleteOutcomes = $conn->prepare("DELETE FROM course_learning_outcomes WHERE course_id = ?");
            $stmtDeleteOutcomes->bind_param("i", $courseId);
            $stmtDeleteOutcomes->execute();
            $stmtDeleteOutcomes->close();
        }
        
        // Insert new outcomes
        $stmtOutcome = $conn->prepare("INSERT INTO course_learning_outcomes (course_id, outcome_text) VALUES (?, ?)");
        
        foreach ($_POST['learningOutcomes'] as $outcome) {
            $outcomeText = sanitizeInput($outcome);
            if (!empty($outcomeText)) {
                $stmtOutcome->bind_param("is", $courseId, $outcomeText);
                $stmtOutcome->execute();
            }
        }
        
        $stmtOutcome->close();
    }
    
    // Upload thumbnail if provided
    if (isset($_FILES['thumbnailImage']) && $_FILES['thumbnailImage']['error'] === UPLOAD_ERR_OK) {
        $thumbnailDir = '../../uploads/thumbnails/';
        
        // Create directory if it doesn't exist
        if (!is_dir($thumbnailDir)) {
            if (!mkdir($thumbnailDir, 0755, true)) {
                throw new Exception("Failed to create uploads directory.");
            }
        }
        
        // Generate unique filename
        $fileExtension = strtolower(pathinfo($_FILES['thumbnailImage']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception("Invalid file type. Allowed types: " . implode(', ', $allowedExtensions));
        }
        
        $thumbnailFilename = "thumbnail_{$courseId}_" . time() . ".{$fileExtension}";
        $thumbnailPath = $thumbnailDir . $thumbnailFilename;
        
        // Move the uploaded file
        if (!move_uploaded_file($_FILES['thumbnailImage']['tmp_name'], $thumbnailPath)) {
            throw new Exception("Failed to upload thumbnail.");
        }
        
        // Update course with thumbnail path
        $stmtThumbnail = $conn->prepare("UPDATE courses SET thumbnail = ? WHERE course_id = ?");
        $stmtThumbnail->bind_param("si", $thumbnailFilename, $courseId);
        $stmtThumbnail->execute();
        $stmtThumbnail->close();
    }
    
    // Update course creation step - first check if creation_step column exists
    $checkColumnQuery = "SHOW COLUMNS FROM courses LIKE 'creation_step'";
    $checkColumnResult = $conn->query($checkColumnQuery);
    
    if ($checkColumnResult->num_rows > 0) {
        // Column exists, update it
        $stmtStep = $conn->prepare("UPDATE courses SET creation_step = 1 WHERE course_id = ?");
        $stmtStep->bind_param("i", $courseId);
        $stmtStep->execute();
        $stmtStep->close();
    }
    
    // Commit transaction
    $conn->commit();
    
    // Return success
    $response['success'] = true;
    $response['message'] = isset($_POST['course_id']) ? 'Course details updated successfully.' : 'Course created successfully.';
    $response['course_id'] = $courseId;
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
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