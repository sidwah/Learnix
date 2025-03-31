<?php
require_once '../../backend/config.php';
require_once '../../backend/session_start.php';

// Check if user is signed in and is an instructor
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get instructor verification status
$stmt = $conn->prepare("
    SELECT verification_status 
    FROM instructors 
    WHERE user_id = ?
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Instructor profile not found']);
    exit;
}

$instructor = $result->fetch_assoc();

// Check if verified
$isVerified = ($instructor['verification_status'] === 'verified');

// Check if there's a pending verification request
$hasPendingRequest = false;
if (!$isVerified) {
    $stmt = $conn->prepare("
        SELECT verification_id 
        FROM instructor_verification_requests 
        WHERE instructor_id = ? AND status = 'pending'
    ");
    $stmt->bind_param("i", $_SESSION['instructor_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $hasPendingRequest = ($result->num_rows > 0);
}

// Return the verification status
echo json_encode([
    'success' => true,
    'verified' => $isVerified,
    'pendingRequest' => $hasPendingRequest,
    'status' => $instructor['verification_status']
]);