<?php
// Path: ajax/department/save_review_notes.php
require '../../backend/session_start.php';
require_once '../../backend/config.php';

// Check if user is signed in as department head
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get parameters
$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

// Validate parameters
if ($course_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Missing course ID']);
    exit;
}

// Get user's department
$dept_query = "SELECT ds.department_id 
               FROM department_staff ds 
               WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' AND ds.deleted_at IS NULL
               LIMIT 1";
$dept_stmt = $conn->prepare($dept_query);
$dept_stmt->bind_param("i", $_SESSION['user_id']);
$dept_stmt->execute();
$dept_result = $dept_stmt->get_result();

if ($dept_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Department access error']);
    exit;
}

$department = $dept_result->fetch_assoc();
$department_id = $department['department_id'];

// Check if the course belongs to the department head's department
$course_query = "SELECT course_id FROM courses WHERE course_id = ? AND department_id = ? AND deleted_at IS NULL";
$stmt = $conn->prepare($course_query);
$stmt->bind_param("ii", $course_id, $department_id);
$stmt->execute();
$course_result = $stmt->get_result();

if ($course_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to review this course']);
    exit;
}

// Check if the course_review_notes table exists, if not create it
$table_check_query = "SHOW TABLES LIKE 'course_review_notes'";
$table_check_result = $conn->query($table_check_query);

if ($table_check_result->num_rows === 0) {
    // Create the table if it doesn't exist
    $create_table_query = "CREATE TABLE `course_review_notes` (
                          `note_id` int(11) NOT NULL AUTO_INCREMENT,
                          `course_id` int(11) NOT NULL,
                          `reviewer_id` int(11) NOT NULL,
                          `notes` text NOT NULL,
                          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                          `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                          PRIMARY KEY (`note_id`),
                          UNIQUE KEY `course_reviewer` (`course_id`, `reviewer_id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if (!$conn->query($create_table_query)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create notes table']);
        exit;
    }
}

// Check if notes already exist for this course and reviewer
$check_query = "SELECT note_id FROM course_review_notes WHERE course_id = ? AND reviewer_id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("ii", $course_id, $_SESSION['user_id']);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$note_exists = ($check_result->num_rows > 0);
$check_stmt->close();

// Insert or update notes
if ($note_exists) {
    // Update existing notes
    $update_query = "UPDATE course_review_notes SET notes = ? WHERE course_id = ? AND reviewer_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sii", $notes, $course_id, $_SESSION['user_id']);
} else {
    // Insert new notes
    $insert_query = "INSERT INTO course_review_notes (course_id, reviewer_id, notes) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iis", $course_id, $_SESSION['user_id'], $notes);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Notes saved successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save notes']);
}

$stmt->close();
$conn->close();
?>