<?php
// save_course_settings.php - Handles saving course settings (Step 5)

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
$requiredFields = ['course_id', 'pricing_option', 'course_price', 'course_level', 'course_requirements'];
$missingFields = [];

foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || !isset($_POST[$field][0]) && $_POST[$field] === '') {
        $missingFields[] = $field;
    }
}

if (count($missingFields) > 0) {
    $response['message'] = 'Missing required fields: ' . implode(', ', $missingFields);
    echo json_encode($response);
    exit;
}

$courseId = intval($_POST['course_id']);
$pricingOption = sanitizeInput($_POST['pricing_option']);
$coursePrice = floatval($_POST['course_price']);
$courseLevel = sanitizeInput($_POST['course_level']);
$courseRequirements = sanitizeInput($_POST['course_requirements']);
$certificateEnabled = isset($_POST['certificates']) && $_POST['certificates'] === '1' ? 1 : 0;
$tags = isset($_POST['tags']) ? sanitizeInput($_POST['tags']) : '';

// Validate pricing option
if ($pricingOption === 'free') {
    $coursePrice = 0.00;
} else if ($pricingOption === 'one-time') {
    // Validate price for paid courses
    if ($coursePrice <= 0 || $coursePrice > 100) {
        $response['message'] = 'Invalid price. Price must be between $0.01 and $100.';
        echo json_encode($response);
        exit;
    }
} else {
    $response['message'] = 'Invalid pricing option.';
    echo json_encode($response);
    exit;
}

// Validate course level
$validLevels = ['beginner', 'intermediate', 'advanced', 'all-levels'];
if (!in_array($courseLevel, $validLevels)) {
    $response['message'] = 'Invalid course level.';
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

// Start transaction
$conn->begin_transaction();

try {
    // Update course settings
    $stmtUpdateCourse = $conn->prepare("
        UPDATE courses 
        SET price = ?, course_level = ?, certificate_enabled = ?, creation_step = 5
        WHERE course_id = ?
    ");
    $stmtUpdateCourse->bind_param("dsii", $coursePrice, $courseLevel, $certificateEnabled, $courseId);
    $stmtUpdateCourse->execute();
    $stmtUpdateCourse->close();
    
    // Update course requirements
    // First, check if requirements already exist
    $stmtCheckRequirements = $conn->prepare("SELECT requirement_id FROM course_requirements WHERE course_id = ?");
    $stmtCheckRequirements->bind_param("i", $courseId);
    $stmtCheckRequirements->execute();
    $stmtCheckRequirements->store_result();
    
    if ($stmtCheckRequirements->num_rows > 0) {
        // Update existing requirements
        $stmtUpdateRequirements = $conn->prepare("
            UPDATE course_requirements
            SET requirement_text = ?
            WHERE course_id = ?
        ");
        $stmtUpdateRequirements->bind_param("si", $courseRequirements, $courseId);
        $stmtUpdateRequirements->execute();
        $stmtUpdateRequirements->close();
    } else {
        // Insert new requirements
        $stmtInsertRequirements = $conn->prepare("
            INSERT INTO course_requirements (course_id, requirement_text)
            VALUES (?, ?)
        ");
        $stmtInsertRequirements->bind_param("is", $courseId, $courseRequirements);
        $stmtInsertRequirements->execute();
        $stmtInsertRequirements->close();
    }
    $stmtCheckRequirements->close();
    
    // Process tags
    if (!empty($tags)) {
        // First, delete existing tag mappings
        $stmtDeleteTags = $conn->prepare("DELETE FROM course_tag_mapping WHERE course_id = ?");
        $stmtDeleteTags->bind_param("i", $courseId);
        $stmtDeleteTags->execute();
        $stmtDeleteTags->close();
        
        // Process each tag
        $tagNames = explode(',', $tags);
        foreach ($tagNames as $tagName) {
            $tagName = trim($tagName);
            if (empty($tagName)) continue;
            
            // Check if tag exists
            $stmtCheckTag = $conn->prepare("SELECT tag_id FROM tags WHERE tag_name = ?");
            $stmtCheckTag->bind_param("s", $tagName);
            $stmtCheckTag->execute();
            $stmtCheckTag->store_result();
            
            if ($stmtCheckTag->num_rows > 0) {
                // Tag exists, get its ID
                $stmtCheckTag->bind_result($tagId);
                $stmtCheckTag->fetch();
                $stmtCheckTag->close();
            } else {
                // Create new tag
                $stmtCheckTag->close();
                $stmtNewTag = $conn->prepare("INSERT INTO tags (tag_name) VALUES (?)");
                $stmtNewTag->bind_param("s", $tagName);
                $stmtNewTag->execute();
                $tagId = $stmtNewTag->insert_id;
                $stmtNewTag->close();
            }
            
            // Map tag to course
            $stmtMapTag = $conn->prepare("INSERT INTO course_tag_mapping (course_id, tag_id) VALUES (?, ?)");
            $stmtMapTag->bind_param("ii", $courseId, $tagId);
            $stmtMapTag->execute();
            $stmtMapTag->close();
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    // Return success
    $response['success'] = true;
    $response['message'] = 'Course settings saved successfully.';
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
            'post' => $_POST
        ];
    }
} finally {
    // Close connection
    $conn->close();
}

// Send JSON response
echo json_encode($response);
exit;