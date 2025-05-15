<?php
// backend/courses/initiate_course.php

// Add error logging
ini_set('log_errors', 1);
ini_set('error_log', '../logs/php-error.log');

// Disable output buffering to prevent extra output
ob_clean();

session_start();
require_once '../config.php';

// Set headers for JSON response
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Add this to catch any warnings/errors
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to output

// Check if user is logged in and has department head role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get user's department with proper role check
$dept_query = "SELECT ds.department_id 
               FROM department_staff ds 
               WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' AND ds.deleted_at IS NULL
               LIMIT 1";
$dept_stmt = $conn->prepare($dept_query);
$dept_stmt->bind_param("i", $_SESSION['user_id']);
$dept_stmt->execute();
$dept_result = $dept_stmt->get_result();

if ($dept_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Department not found']);
    exit;
}

$department = $dept_result->fetch_assoc();
$department_id = $department['department_id'];

// Determine action based on request
$action = $_GET['action'] ?? null;
$step = $_GET['step'] ?? null;

if ($action === 'finalize') {
    handleFinalize($conn, $_SESSION['user_id']);
} elseif ($step) {
    handleStep($conn, $step, $department_id, $_SESSION['user_id']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

function handleStep($conn, $step, $department_id, $user_id) {
    // Check if it's a multipart/form-data request for file uploads
    if (isset($_FILES['thumbnail']) && $step == 1) {
        // For file uploads, data is in $_POST
        $input = $_POST;
    } else {
        // For JSON data
        $input = json_decode(file_get_contents('php://input'), true);
    }
    
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data']);
        return;
    }
    
    // Add debug logging
    error_log("Step: " . $step . ", Input: " . print_r($input, true));
    if (isset($_FILES['thumbnail'])) {
        error_log("Thumbnail file data: " . print_r($_FILES['thumbnail'], true));
    }
    
    try {
        $conn->begin_transaction();
        
        switch ($step) {
            case '1':
                $result = saveStep1($conn, $input, $department_id);
                break;
            case '2':
                if (!isset($input['course_id']) || !$input['course_id']) {
                    throw new Exception('Course ID required for step 2');
                }
                $result = saveStep2($conn, $input, $department_id);
                break;
            case '3':
                if (!isset($input['course_id']) || !$input['course_id']) {
                    throw new Exception('Course ID required for step 3');
                }
                $result = saveStep3($conn, $input, $department_id);
                break;
            default:
                throw new Exception('Invalid step');
        }
        
        $conn->commit();
        echo json_encode($result);
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error in handleStep: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}


function saveStep1($conn, $data, $department_id) {
    // Validate required fields
    $required = ['title', 'category_id', 'subcategory_id', 'course_level', 'short_description'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    // Process thumbnail upload if present
    $thumbnail_filename = null;
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
        // Validate file size (max 2MB)
        if ($_FILES['thumbnail']['size'] > 2 * 1024 * 1024) {
            throw new Exception("Thumbnail file size exceeds the 2MB limit");
        }
        
        // Validate file type
        $mime_type = mime_content_type($_FILES['thumbnail']['tmp_name']);
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!in_array($mime_type, $allowed_types)) {
            throw new Exception("Invalid file type. Only JPG, JPEG, and PNG are allowed. Got: " . $mime_type);
        }
        
        // Generate unique filename
        $file_extension = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
        $thumbnail_filename = 'course_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
        
        // Create uploads directory if it doesn't exist
        $upload_dir = '../../uploads/thumbnails/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $upload_path = $upload_dir . $thumbnail_filename;
        
        // Move the uploaded file
        if (!move_uploaded_file($_FILES['thumbnail']['tmp_name'], $upload_path)) {
            throw new Exception("Failed to upload thumbnail");
        }
    }
    
    // First, verify the category exists and is not deleted
    $cat_check = "SELECT c.category_id, c.name 
                  FROM categories c
                  WHERE c.category_id = ? AND c.deleted_at IS NULL";
    $cat_stmt = $conn->prepare($cat_check);
    $cat_stmt->bind_param("i", $data['category_id']);
    $cat_stmt->execute();
    $cat_result = $cat_stmt->get_result();
    
    if ($cat_result->num_rows === 0) {
        throw new Exception("Selected category does not exist");
    }
    
    // Then check if the category is mapped to the department
    $dept_cat_check = "SELECT dcm.mapping_id 
                       FROM department_category_mapping dcm
                       WHERE dcm.department_id = ? AND dcm.category_id = ? 
                       AND dcm.is_active = 1 AND dcm.deleted_at IS NULL";
    $dept_cat_stmt = $conn->prepare($dept_cat_check);
    $dept_cat_stmt->bind_param("ii", $department_id, $data['category_id']);
    $dept_cat_stmt->execute();
    
    // If no mapping exists, create one
    if ($dept_cat_stmt->get_result()->num_rows === 0) {
        $create_mapping = "INSERT INTO department_category_mapping 
                          (department_id, category_id, is_active, created_by) 
                          VALUES (?, ?, 1, (SELECT user_id FROM department_staff 
                                           WHERE department_id = ? AND role = 'head' 
                                           AND status = 'active' AND deleted_at IS NULL LIMIT 1))";
        $mapping_stmt = $conn->prepare($create_mapping);
        $mapping_stmt->bind_param("iii", $department_id, $data['category_id'], $department_id);
        $mapping_stmt->execute();
    }
    
    // Verify subcategory belongs to category
    $subcat_check = "SELECT subcategory_id 
                     FROM subcategories 
                     WHERE subcategory_id = ? AND category_id = ? AND deleted_at IS NULL";
    $subcat_stmt = $conn->prepare($subcat_check);
    $subcat_stmt->bind_param("ii", $data['subcategory_id'], $data['category_id']);
    $subcat_stmt->execute();
    
    if ($subcat_stmt->get_result()->num_rows === 0) {
        throw new Exception("Subcategory does not belong to selected category");
    }
    
    // Validate price (ensure it's a valid number)
    $price = isset($data['price']) ? floatval($data['price']) : 0.00;
    if ($price < 0) {
        throw new Exception("Price cannot be negative");
    }
    
    // Create course entry with thumbnail
    if (isset($data['course_id']) && $data['course_id']) {
        // Update existing course
        if ($thumbnail_filename) {
            // If new thumbnail uploaded, update with it
            $sql = "UPDATE courses 
                    SET title = ?, short_description = ?, subcategory_id = ?, course_level = ?, 
                        price = ?, thumbnail = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE course_id = ? AND department_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssdsi", 
                $data['title'], 
                $data['short_description'], 
                $data['subcategory_id'], 
                $data['course_level'],
                $price,
                $thumbnail_filename,
                $data['course_id'],
                $department_id
            );
        } else {
            // If no new thumbnail, don't update that field
            $sql = "UPDATE courses 
                    SET title = ?, short_description = ?, subcategory_id = ?, course_level = ?, 
                        price = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE course_id = ? AND department_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssdii", 
                $data['title'], 
                $data['short_description'], 
                $data['subcategory_id'], 
                $data['course_level'],
                $price,
                $data['course_id'],
                $department_id
            );
        }
        $stmt->execute();
        $course_id = $data['course_id'];
        
        // Get current thumbnail filename if needed
        if (!$thumbnail_filename) {
            $query = "SELECT thumbnail FROM courses WHERE course_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $course_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            if ($row && !empty($row['thumbnail'])) {
                $thumbnail_filename = $row['thumbnail'];
            }
        }
    } else {
        // Create new course with thumbnail
        $sql = "INSERT INTO courses (department_id, title, short_description, subcategory_id, 
                course_level, status, approval_status, certificate_enabled, 
                creation_step, price, thumbnail) 
                VALUES (?, ?, ?, ?, ?, 'Draft', 'pending', 0, 1, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssds", 
            $department_id,
            $data['title'], 
            $data['short_description'], 
            $data['subcategory_id'], 
            $data['course_level'],
            $price,
            $thumbnail_filename
        );
        $stmt->execute();
        $course_id = $conn->insert_id;
    }
    
    // Save estimated_duration in course_settings if provided
    if (!empty($data['estimated_duration'])) {
        $settings_sql = "INSERT INTO course_settings (course_id, estimated_duration, difficulty_level) 
                        VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE 
                        estimated_duration = VALUES(estimated_duration),
                        difficulty_level = VALUES(difficulty_level)";
        $settings_stmt = $conn->prepare($settings_sql);
        $settings_stmt->bind_param("iss", $course_id, $data['estimated_duration'], $data['course_level']);
        $settings_stmt->execute();
    }
    
    // Return the thumbnail URL for the frontend
    $response = [
        'success' => true, 
        'course_id' => $course_id
    ];
    
    if ($thumbnail_filename) {
        $response['thumbnail_url'] = 'uploads/thumbnails/' . $thumbnail_filename;
    }
    
    return $response;
}

function saveStep2($conn, $data, $department_id) {
    if (!isset($data['course_id']) || !$data['course_id']) {
        throw new Exception('Course ID required');
    }
    
    $course_id = $data['course_id'];
    
    // Verify course belongs to department
    $verify_sql = "SELECT course_id FROM courses WHERE course_id = ? AND department_id = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("ii", $course_id, $department_id);
    $verify_stmt->execute();
    
    if ($verify_stmt->get_result()->num_rows === 0) {
        throw new Exception('Course not found or unauthorized');
    }
    
    // Update course full description in courses table directly
    if (isset($data['full_description'])) {
        $desc_sql = "UPDATE courses SET full_description = ? WHERE course_id = ?";
        $desc_stmt = $conn->prepare($desc_sql);
        $desc_stmt->bind_param("si", $data['full_description'], $course_id);
        $desc_stmt->execute();
        
        // Log if the update failed
        if ($desc_stmt->affected_rows <= 0 && $desc_stmt->errno != 0) {
            error_log("Failed to update full_description: " . $desc_stmt->error);
        }
    }
    
    // Clear existing outcomes and requirements
    $delete_outcomes = $conn->prepare("DELETE FROM course_learning_outcomes WHERE course_id = ?");
    $delete_outcomes->bind_param("i", $course_id);
    $delete_outcomes->execute();
    
    $delete_requirements = $conn->prepare("DELETE FROM course_requirements WHERE course_id = ?");
    $delete_requirements->bind_param("i", $course_id);
    $delete_requirements->execute();
    
    // Insert learning outcomes
    if (isset($data['outcomes']) && is_array($data['outcomes'])) {
        $outcome_sql = "INSERT INTO course_learning_outcomes (course_id, outcome_text) VALUES (?, ?)";
        $outcome_stmt = $conn->prepare($outcome_sql);
        
        foreach ($data['outcomes'] as $outcome) {
            if (trim($outcome)) {
                $outcome_stmt->bind_param("is", $course_id, trim($outcome));
                $outcome_stmt->execute();
            }
        }
    }
    
    // Insert requirements
    if (isset($data['requirements']) && is_array($data['requirements'])) {
        $req_sql = "INSERT INTO course_requirements (course_id, requirement_text) VALUES (?, ?)";
        $req_stmt = $conn->prepare($req_sql);
        
        foreach ($data['requirements'] as $requirement) {
            if (trim($requirement)) {
                $req_stmt->bind_param("is", $course_id, trim($requirement));
                $req_stmt->execute();
            }
        }
    }
    
    // Update creation step
    $update_sql = "UPDATE courses SET creation_step = 2, updated_at = CURRENT_TIMESTAMP WHERE course_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $course_id);
    $update_stmt->execute();
    
    return ['success' => true];
}

function saveStep3($conn, $data, $department_id) {
    if (!isset($data['course_id']) || !$data['course_id']) {
        throw new Exception('Course ID required');
    }
    
    $course_id = intval($data['course_id']);
    
    // Verify course belongs to department
    $verify_sql = "SELECT course_id, course_level FROM courses WHERE course_id = ? AND department_id = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("ii", $course_id, $department_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows === 0) {
        throw new Exception('Course not found or unauthorized');
    }
    
    $course_data = $verify_result->fetch_assoc();
    $course_level = $course_data['course_level']; // Get the course level from the existing course data
    
    // Prepare data - Handle empty strings as NULL where appropriate
    $enrollment_limit = empty($data['enrollment_limit']) ? NULL : intval($data['enrollment_limit']);
    $access_password = ($data['visibility'] === 'Password Protected' && !empty($data['access_password'])) ? $data['access_password'] : NULL;
    $visibility = $data['visibility'] ?? 'Public';
    $access_level = $data['access_level'] ?? 'Public';
    $estimated_duration = $data['estimated_duration'] ?? '';
    
    // Insert/Update course settings with proper difficulty_level (which should be the course_level)
    $settings_sql = "INSERT INTO course_settings 
                    (course_id, enrollment_limit, visibility, access_password, difficulty_level, estimated_duration) 
                    VALUES (?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                    enrollment_limit = VALUES(enrollment_limit), 
                    visibility = VALUES(visibility), 
                    access_password = VALUES(access_password), 
                    difficulty_level = VALUES(difficulty_level), 
                    estimated_duration = VALUES(estimated_duration)";
                    
    $settings_stmt = $conn->prepare($settings_sql);
    
    // Use 'i' for integer (NULL-safe), 's' for string
    // Use course_level for difficulty_level, not access_level
    $settings_stmt->bind_param("sissss", 
        $course_id,
        $enrollment_limit,
        $visibility,
        $access_password,
        $course_level,  // Use course_level here, not access_level
        $estimated_duration
    );
    
    if (!$settings_stmt->execute()) {
        throw new Exception('Failed to save course settings: ' . $settings_stmt->error);
    }
    
    // Validate and clean price
    $price = isset($data['price']) ? floatval($data['price']) : 0.00;
    if ($price < 0) {
        throw new Exception("Price cannot be negative");
    }
    
    // Update main course table
    $course_sql = "UPDATE courses 
                   SET price = ?, access_level = ?, certificate_enabled = ?, 
                       creation_step = 3, updated_at = CURRENT_TIMESTAMP 
                   WHERE course_id = ?";
    $course_stmt = $conn->prepare($course_sql);
    
    $certificate_enabled = isset($data['certificate_enabled']) && $data['certificate_enabled'] ? 1 : 0;
    
    $course_stmt->bind_param("dsii", $price, $access_level, $certificate_enabled, $course_id);
    
    if (!$course_stmt->execute()) {
        throw new Exception('Failed to update course: ' . $course_stmt->error);
    }
    
    return ['success' => true];
}

function handleFinalize($conn, $user_id) {
    $course_id = $_GET['course_id'] ?? null;
    
    if (!$course_id) {
        echo json_encode(['success' => false, 'message' => 'Course ID required']);
        return;
    }
    
    try {
        $conn->begin_transaction();
        
        // Validate course exists and belongs to user's department
        $validate_sql = "SELECT c.course_id, c.title, c.department_id, c.thumbnail 
                        FROM courses c
                        JOIN department_staff ds ON c.department_id = ds.department_id
                        WHERE c.course_id = ? AND ds.user_id = ? AND ds.role = 'head' 
                              AND ds.status = 'active' AND ds.deleted_at IS NULL";
        $validate_stmt = $conn->prepare($validate_sql);
        $validate_stmt->bind_param("ii", $course_id, $user_id);
        $validate_stmt->execute();
        $result = $validate_stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Course not found or unauthorized');
        }
        
        $course = $result->fetch_assoc();
        
        // Validate all required data exists
        $checks = [
            "SELECT course_id FROM course_learning_outcomes WHERE course_id = ?" => "At least one learning outcome is required",
            "SELECT course_id FROM course_requirements WHERE course_id = ?" => "At least one requirement is required"
        ];
        
        foreach ($checks as $sql => $error_message) {
            $check_stmt = $conn->prepare($sql);
            $check_stmt->bind_param("i", $course_id);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows === 0) {
                throw new Exception($error_message);
            }
        }
        
        // Update course to final state
        $finalize_sql = "UPDATE courses 
                        SET creation_step = 4, status = 'Draft', approval_status = 'pending', 
                            updated_at = CURRENT_TIMESTAMP 
                        WHERE course_id = ?";
        $finalize_stmt = $conn->prepare($finalize_sql);
        $finalize_stmt->bind_param("i", $course_id);
        $finalize_stmt->execute();
        
        // Log activity
        $log_sql = "INSERT INTO user_activity_logs (user_id, activity_type, activity_details, ip_address, user_agent) 
                   VALUES (?, 'course_initiated', ?, ?, ?)";
        $log_stmt = $conn->prepare($log_sql);
        $activity_details = "Initiated course: " . $course['title'];
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $log_stmt->bind_param("isss", $user_id, $activity_details, $ip_address, $user_agent);
        $log_stmt->execute();
        
        $conn->commit();
        
        // Include thumbnail URL in response if it exists
        $response = ['success' => true, 'message' => 'Course created successfully'];
        if (!empty($course['thumbnail'])) {
            $response['thumbnail_url'] = 'uploads/thumbnails/' . $course['thumbnail'];
        }
        
        echo json_encode($response);
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error in handleFinalize: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

$conn->close();
?>