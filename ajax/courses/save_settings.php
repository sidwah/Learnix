<?php
require '../../backend/session_start.php';
require '../../backend/config.php';

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Validate required input
if (!isset($_POST['course_id']) || !isset($_POST['price']) || !isset($_POST['access_level'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$course_id = intval($_POST['course_id']);
$price = floatval($_POST['price']);
$certificate_enabled = isset($_POST['certificate_enabled']) ? intval($_POST['certificate_enabled']) : 0;
$access_level = $_POST['access_level'];
$visibility = isset($_POST['visibility']) ? $_POST['visibility'] : 'Public';
$access_password = isset($_POST['access_password']) ? $_POST['access_password'] : null;
$enrollment_limit = isset($_POST['enrollment_limit']) && !empty($_POST['enrollment_limit']) ? intval($_POST['enrollment_limit']) : null;
$estimated_duration = isset($_POST['estimated_duration']) ? $_POST['estimated_duration'] : null;
$enrollment_start = isset($_POST['enrollment_start']) && !empty($_POST['enrollment_start']) ? $_POST['enrollment_start'] : null;
$enrollment_end = isset($_POST['enrollment_end']) && !empty($_POST['enrollment_end']) ? $_POST['enrollment_end'] : null;

// Validate inputs
if ($price < 0) {
    echo json_encode(['success' => false, 'message' => 'Price cannot be negative']);
    exit;
}

// If price is between 0.01 and 0.99, reject it
if ($price > 0 && $price < 0.99) {
    echo json_encode(['success' => false, 'message' => 'Minimum price for paid courses is $0.99']);
    exit;
}

// Validate access level
$valid_access_levels = ['Public', 'Restricted'];
if (!in_array($access_level, $valid_access_levels)) {
    echo json_encode(['success' => false, 'message' => 'Invalid access level']);
    exit;
}

// Validate visibility
$valid_visibility = ['Public', 'Private', 'Password Protected', 'Coming Soon'];
if (!in_array($visibility, $valid_visibility)) {
    echo json_encode(['success' => false, 'message' => 'Invalid visibility setting']);
    exit;
}

// Require password if visibility is set to Password Protected
if ($visibility === 'Password Protected' && empty($access_password)) {
    echo json_encode(['success' => false, 'message' => 'Password is required for password-protected courses']);
    exit;
}

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

// Start transaction
$conn->begin_transaction();

try {
    // Update course table
    $stmt = $conn->prepare("UPDATE courses SET 
                            price = ?, 
                            certificate_enabled = ?, 
                            access_level = ?,
                            updated_at = NOW() 
                            WHERE course_id = ?");
                            
    $stmt->bind_param("disi", $price, $certificate_enabled, $access_level, $course_id);
    $stmt->execute();
    $stmt->close();
    
    // Check if course settings already exist
    $stmt = $conn->prepare("SELECT setting_id FROM course_settings WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_settings = $result->fetch_assoc();
    $stmt->close();
    
    if ($existing_settings) {
        // Update existing settings
        $stmt = $conn->prepare("UPDATE course_settings SET 
                                enrollment_limit = ?, 
                                enrollment_period_start = ?, 
                                enrollment_period_end = ?,
                                visibility = ?,
                                access_password = ?,
                                estimated_duration = ?,
                                last_updated = NOW() 
                                WHERE course_id = ?");
                                
        $stmt->bind_param("isssssi", $enrollment_limit, $enrollment_start, $enrollment_end, $visibility, $access_password, $estimated_duration, $course_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // Insert new settings
        $stmt = $conn->prepare("INSERT INTO course_settings (
                                course_id, 
                                enrollment_limit,
                                enrollment_period_start,
                                enrollment_period_end,
                                visibility,
                                access_password,
                                estimated_duration,
                                last_updated
                                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                                
        $stmt->bind_param("iisssss", $course_id, $enrollment_limit, $enrollment_start, $enrollment_end, $visibility, $access_password, $estimated_duration);
        $stmt->execute();
        $stmt->close();
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Course settings saved successfully']);
    
} catch (Exception $e) {
    // Rollback in case of error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>