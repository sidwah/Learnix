<?php
/**
 * File: update_course.php
 * Description: Handles course updates from the edit modal
 * Location: ../backend/courses/
 */

// Include database connection
require_once '../config.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Check if instructor is logged in
session_start();
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

// Get instructor ID
$user_id = $_SESSION['user_id'];
$instructor_query = "SELECT instructor_id FROM instructors WHERE user_id = ?";
$stmt = $conn->prepare($instructor_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$instructor_result = $stmt->get_result();

if ($instructor_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Instructor not found']);
    exit;
}

$instructor_row = $instructor_result->fetch_assoc();
$instructor_id = $instructor_row['instructor_id'];
$stmt->close();

// Check if course_id is provided
if (!isset($_POST['course_id']) || empty($_POST['course_id'])) {
    echo json_encode(['success' => false, 'message' => 'Course ID is required']);
    exit;
}

// Sanitize input
$course_id = intval($_POST['course_id']);

// Check if the course belongs to the instructor
$ownership_query = "SELECT course_id FROM courses WHERE course_id = ? AND instructor_id = ?";
$stmt = $conn->prepare($ownership_query);
$stmt->bind_param("ii", $course_id, $instructor_id);
$stmt->execute();
$ownership_result = $stmt->get_result();

if ($ownership_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to edit this course']);
    exit;
}
$stmt->close();

// Get and sanitize course data
$title = trim($_POST['title'] ?? '');
$short_description = trim($_POST['short_description'] ?? '');
$full_description = $_POST['full_description'] ?? '';
$price = floatval($_POST['price'] ?? 0);
$course_level = $_POST['course_level'] ?? 'Beginner';
$status = $_POST['status'] ?? 'Draft';
$access_level = $_POST['access_level'] ?? 'Public';
$subcategory_id = intval($_POST['subcategory_id'] ?? 0);
$certificate_enabled = isset($_POST['certificate_enabled']) ? (($_POST['certificate_enabled'] == '1' || $_POST['certificate_enabled'] === true) ? 1 : 0) : 0;

// Validate required fields
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Course title is required']);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Update course basic info
    $update_sql = "UPDATE courses SET 
                  title = ?,
                  short_description = ?,
                  full_description = ?,
                  subcategory_id = ?,
                  status = ?,
                  price = ?,
                  access_level = ?,
                  course_level = ?,
                  certificate_enabled = ?,
                  updated_at = CURRENT_TIMESTAMP
                  WHERE course_id = ?";
    
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sssissssii", 
        $title, 
        $short_description, 
        $full_description, 
        $subcategory_id, 
        $status, 
        $price, 
        $access_level, 
        $course_level, 
        $certificate_enabled, 
        $course_id
    );
    $stmt->execute();
    
    // Check if update was successful
    if ($stmt->affected_rows < 0) {
        throw new Exception("Failed to update course information");
    }
    $stmt->close();
    
    // Handle thumbnail upload if provided
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        // Validate file type and size
        if (!in_array($_FILES['thumbnail']['type'], $allowed_types)) {
            throw new Exception("Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.");
        }
        
        if ($_FILES['thumbnail']['size'] > $max_size) {
            throw new Exception("File size exceeds the limit (5MB).");
        }
        
        // Generate unique filename
        $extension = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
        $filename = "thumbnail_{$course_id}_" . time() . "." . $extension;
        $target_path = "../../uploads/thumbnails/" . $filename;
        
        // Create directory if it doesn't exist
        if (!is_dir("../../uploads/thumbnails/")) {
            mkdir("../../uploads/thumbnails/", 0755, true);
        }
        
        // Move the uploaded file
        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target_path)) {
            // Update course thumbnail
            $thumbnail_sql = "UPDATE courses SET thumbnail = ? WHERE course_id = ?";
            $stmt = $conn->prepare($thumbnail_sql);
            $stmt->bind_param("si", $filename, $course_id);
            $stmt->execute();
            $stmt->close();
        } else {
            throw new Exception("Failed to upload thumbnail.");
        }
    }
    
    // Update course requirements
    if (isset($_POST['requirements']) && is_array($_POST['requirements'])) {
        // Delete existing requirements
        $delete_sql = "DELETE FROM course_requirements WHERE course_id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $stmt->close();
        
        // Insert new requirements
        $insert_sql = "INSERT INTO course_requirements (course_id, requirement_text) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_sql);
        
        foreach ($_POST['requirements'] as $requirement) {
            if (!empty(trim($requirement))) {
                $stmt->bind_param("is", $course_id, $requirement);
                $stmt->execute();
            }
        }
        $stmt->close();
    }
    
    // Update learning outcomes
    if (isset($_POST['outcomes']) && is_array($_POST['outcomes'])) {
        // Delete existing outcomes
        $delete_sql = "DELETE FROM course_learning_outcomes WHERE course_id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $stmt->close();
        
        // Insert new outcomes
        $insert_sql = "INSERT INTO course_learning_outcomes (course_id, outcome_text) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_sql);
        
        foreach ($_POST['outcomes'] as $outcome) {
            if (!empty(trim($outcome))) {
                $stmt->bind_param("is", $course_id, $outcome);
                $stmt->execute();
            }
        }
        $stmt->close();
    }
    
    // Update course tags
    if (isset($_POST['tags']) && is_array($_POST['tags'])) {
        // Delete existing tag mappings
        $delete_sql = "DELETE FROM course_tag_mapping WHERE course_id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $stmt->close();
        
        // Insert new tag mappings
        $insert_sql = "INSERT INTO course_tag_mapping (course_id, tag_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_sql);
        
        foreach ($_POST['tags'] as $tag_id) {
            if (!empty($tag_id)) {
                $tag_id = intval($tag_id);
                $stmt->bind_param("ii", $course_id, $tag_id);
                $stmt->execute();
            }
        }
        $stmt->close();
    }
    
    // Commit transaction
    $conn->commit();
    
    // Return success response
    echo json_encode([
        'success' => true, 
        'message' => 'Course updated successfully',
        'course_id' => $course_id
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Log error
    error_log('Error updating course: ' . $e->getMessage());
    
    // Return error message
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to update course: ' . $e->getMessage()
    ]);
} finally {
    // Close connection
    $conn->close();
}
?>