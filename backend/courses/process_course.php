<?php
require '../session_start.php';
require '../config.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set response type
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$userId = $_SESSION['user_id'];
$response = ["success" => false, "message" => ""];

// Verify instructor status
$stmtInstructor = $conn->prepare("SELECT instructor_id FROM instructors WHERE user_id = ?");
$stmtInstructor->bind_param("i", $userId);
$stmtInstructor->execute();
$stmtInstructor->store_result();

if ($stmtInstructor->num_rows == 0) {
    $response["message"] = "User is not an instructor";
    echo json_encode($response);
    exit;
}

$stmtInstructor->bind_result($instructorId);
$stmtInstructor->fetch();
$stmtInstructor->close();

// Helper function to sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Log function for debugging
function logData($message) {
    file_put_contents('course_creation_log.txt', date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
} 

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    logData("Form submission received");
    
    // Validate required fields
    $requiredFields = ['courseTitle', 'shortDescription', 'category', 'courseLevel'];
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        $response["message"] = "Missing required fields: " . implode(', ', $missingFields);
        echo json_encode($response);
        exit;
    }

    // Start transaction for database integrity
    $conn->begin_transaction();
    
    try {
        // Basic course information
        $courseTitle = sanitizeInput($_POST['courseTitle']);
        $shortDescription = sanitizeInput($_POST['shortDescription']); 
        $fullDescription = isset($_POST['fullDescription']) ? $_POST['fullDescription'] : ''; // Allow HTML in description
        $category = sanitizeInput($_POST['category']);
        $courseLevel = sanitizeInput($_POST['courseLevel']);
        // $tags = isset($_POST['tags']) ? sanitizeInput($_POST['tags']) : '';
        
        // Pricing information
        $pricingOption = isset($_POST['pricingOptions']) ? sanitizeInput($_POST['pricingOptions']) : 'free';
        $coursePrice = ($pricingOption === 'free') ? '0.00' : sanitizeInput($_POST['coursePrice']);
        
        // Additional settings
        $certificateEnabled = isset($_POST['certificates']) ? 1 : 0;
        $courseRequirements = isset($_POST['courseRequirements']) ? sanitizeInput($_POST['courseRequirements']) : '';
        
        // Get category ID from slug
        $stmtCategory = $conn->prepare("SELECT subcategory_id  FROM subcategories WHERE slug = ?");
        $stmtCategory->bind_param("s", $category);
        $stmtCategory->execute();
        $stmtCategory->store_result();
        
        if ($stmtCategory->num_rows == 0) {
            throw new Exception("Invalid category selected");
        }
        
        $stmtCategory->bind_result($categoryId);
        $stmtCategory->fetch();
        $stmtCategory->close();
        
        // Default status (draft)
        $status = 'draft';
        
        // Insert course record
        $stmt = $conn->prepare("INSERT INTO courses (title, short_description, full_description, category_id, instructor_id, price, status, certificate_enabled, course_level, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssiisds", $courseTitle, $shortDescription, $fullDescription, $categoryId, $instructorId, $coursePrice, $status, $certificateEnabled, $courseLevel);
        
        if (!$stmt->execute()) {
            throw new Exception("Error creating course: " . $stmt->error);
        }
        
        $courseId = $stmt->insert_id;
        $stmt->close();
        
        logData("Course record created with ID: $courseId");
        
        // Process learning outcomes
        if (isset($_POST['learningOutcomes']) && is_array($_POST['learningOutcomes'])) {
            $stmtOutcome = $conn->prepare("INSERT INTO course_learning_outcomes (course_id, outcome_text) VALUES (?, ?)");
            
            foreach ($_POST['learningOutcomes'] as $outcome) {
                if (!empty(trim($outcome))) {
                    $outcomeText = sanitizeInput($outcome);
                    $stmtOutcome->bind_param("is", $courseId, $outcomeText);
                    $stmtOutcome->execute();
                }
            }
            
            $stmtOutcome->close();
            logData("Learning outcomes processed");
        }
        
        // Process course requirements
        if (!empty($courseRequirements)) {
            $stmtReq = $conn->prepare("INSERT INTO course_requirements (course_id, requirement_text) VALUES (?, ?)");
            $stmtReq->bind_param("is", $courseId, $courseRequirements);
            $stmtReq->execute();
            $stmtReq->close();
            logData("Course requirements processed");
        }
        
        // Process sections
        if (isset($_POST['sections']) && is_array($_POST['sections'])) {
            $stmtSection = $conn->prepare("INSERT INTO course_sections (course_id, title, position) VALUES (?, ?, ?)");
            
            for ($i = 0; $i < count($_POST['sections']); $i++) {
                $sectionTitle = sanitizeInput($_POST['sections'][$i]);
                if (!empty(trim($sectionTitle))) {
                    $position = $i + 1;
                    $stmtSection->bind_param("isi", $courseId, $sectionTitle, $position);
                    $stmtSection->execute();
                    $sectionId = $conn->insert_id;
                    
                    // Process courses and quizzes for this section if available
                    // This would require additional logic based on your form structure
                }
            }
            
            $stmtSection->close();
            logData("Sections processed");
        }
        
        // Process thumbnail image
        if (isset($_FILES['thumbnailImage']) && $_FILES['thumbnailImage']['error'] == 0) {
            $thumbnailDir = '../../uploads/thumbnails/';
            
            if (!is_dir($thumbnailDir)) {
                mkdir($thumbnailDir, 0755, true);
            }
            
            $ext = pathinfo($_FILES['thumbnailImage']['name'], PATHINFO_EXTENSION);
            $thumbnailFile = $thumbnailDir . "thumbnail_" . $courseId . "_" . time() . "." . $ext;
            
            if (move_uploaded_file($_FILES['thumbnailImage']['tmp_name'], $thumbnailFile)) {
                $thumbnailPath = str_replace('../../', '', $thumbnailFile);
                
                $stmtThumb = $conn->prepare("UPDATE courses SET thumbnail = ? WHERE course_id = ?");
                $stmtThumb->bind_param("si", $thumbnailPath, $courseId);
                $stmtThumb->execute();
                $stmtThumb->close();
                
                logData("Thumbnail uploaded: $thumbnailPath");
            } else {
                logData("Failed to move thumbnail file");
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        $response["success"] = true;
        $response["message"] = "Course created successfully!";
        $response["course_id"] = $courseId;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $response["message"] = "Error: " . $e->getMessage();
        logData("ERROR: " . $e->getMessage());
    }
    
    $conn->close();
    echo json_encode($response);
    logData("Error: " . $e->getMessage());

    exit;
} else {
    $response["message"] = "Invalid request method";
    echo json_encode($response);
}
?>