<?php
// ajax/students/save-notes.php
// This endpoint saves student notes for a specific topic

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Send error as JSON
function sendError($message, $code = 400) {
    header('Content-Type: application/json');
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendError('Unauthorized', 401);
}

// Check if required parameters are provided
if (!isset($_POST['topic_id']) || !isset($_POST['note_content'])) {
    sendError('Missing required parameters');
}

// Get parameters
$topic_id = intval($_POST['topic_id']);
$note_content = trim($_POST['note_content']);
$user_id = $_SESSION['user_id'];
$timestamp = isset($_POST['timestamp']) ? intval($_POST['timestamp']) : null;

// Connect to database
require_once '../../backend/config.php';

// Check if this topic exists
$topic_check = "SELECT topic_id FROM section_topics WHERE topic_id = ?";
$stmt = $conn->prepare($topic_check);
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$topic_result = $stmt->get_result();

if ($topic_result->num_rows === 0) {
    sendError('Invalid topic ID');
}

// Check if note already exists for this user and topic
$check_note = "SELECT note_id FROM student_notes WHERE user_id = ? AND topic_id = ?";
$stmt = $conn->prepare($check_note);
$stmt->bind_param("ii", $user_id, $topic_id);
$stmt->execute();
$note_check = $stmt->get_result();

if ($note_check->num_rows > 0) {
    // Update existing note
    $note_id = $note_check->fetch_assoc()['note_id'];
    $update_note = "UPDATE student_notes 
                   SET content = ?, 
                       timestamp = ?, 
                       updated_at = NOW() 
                   WHERE note_id = ?";
    $stmt = $conn->prepare($update_note);
    $stmt->bind_param("sii", $note_content, $timestamp, $note_id);
    $success = $stmt->execute();
} else {
    // Insert new note
    $insert_note = "INSERT INTO student_notes 
                  (user_id, topic_id, content, timestamp) 
                  VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_note);
    $stmt->bind_param("iisi", $user_id, $topic_id, $note_content, $timestamp);
    $success = $stmt->execute();
    
    if ($success) {
        $note_id = $conn->insert_id;
    }
}

// Return response
header('Content-Type: application/json');
echo json_encode([
    'success' => $success, 
    'message' => $success ? 'Notes saved successfully' : 'Failed to save notes',
    'note_id' => $success ? $note_id : null,
    'updated_at' => date('Y-m-d H:i:s')
]);

// Close database connection
$stmt->close();
$conn->close();
?>