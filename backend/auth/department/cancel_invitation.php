<?php
// backend/department/cancel_invitation.php
require_once '../../config.php'; // Database connection file

// Start or resume session to get the department head's ID
session_start();

// Check if user is logged in and is a department head
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    http_response_code(403); // Forbidden
    exit(json_encode(['status' => 'error', 'message' => 'Unauthorized access']));
}

$departmentHeadId = $_SESSION['user_id'];
$departmentId = $_SESSION['department_id'];
$departmentName = $_SESSION['department_name'];

// Process the POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode JSON input if content type is application/json
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    
    if (strpos($contentType, 'application/json') !== false) {
        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);
        
        // If json_decode failed, handle the error
        if (!is_array($decoded)) {
            http_response_code(400);
            exit(json_encode(['status' => 'error', 'message' => 'Invalid JSON format']));
        }
        
        $_POST = $decoded;
    }
    
    // Get invitation ID
    $invitationId = intval($_POST['id'] ?? 0);
    
    if ($invitationId <= 0) {
        http_response_code(400);
        exit(json_encode(['status' => 'error', 'message' => 'Invalid invitation ID']));
    }
    
    // Connect to database
    $conn = new mysqli($host, $username, $password, $db_name);
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        http_response_code(500);
        exit(json_encode(['status' => 'error', 'message' => 'Server error. Please try again later.']));
    }
    
    try {
        // Check if the invitation exists and belongs to a department managed by this department head
        $stmt = $conn->prepare(
            "SELECT ii.email, ii.department_id 
             FROM instructor_invitations ii
             WHERE ii.id = ? 
             AND ii.department_id IN (
                 SELECT department_id FROM department_staff 
                 WHERE user_id = ? AND role = 'head' AND status = 'active' AND deleted_at IS NULL
             )
             AND ii.is_used = 0"
        );
        $stmt->bind_param("ii", $invitationId, $departmentHeadId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            http_response_code(404);
            exit(json_encode(['status' => 'error', 'message' => 'Invitation not found or unauthorized.']));
        }
        
        $invitation = $result->fetch_assoc();
        $stmt->close();
        
        // Begin transaction
        $conn->begin_transaction();
        
        // Invalidate the invitation
        // In a real application, you might want to use soft delete pattern or keep record of cancelled invitations
        $stmt = $conn->prepare("DELETE FROM instructor_invitations WHERE id = ?");
        $stmt->bind_param("i", $invitationId);
        
        if ($stmt->execute()) {
            $conn->commit();
            http_response_code(200);
            exit(json_encode([
                'status' => 'success', 
                'message' => 'Invitation cancelled successfully',
                'data' => [
                    'email' => $invitation['email']
                ]
            ]));
        } else {
            $conn->rollback();
            http_response_code(500);
            exit(json_encode(['status' => 'error', 'message' => 'Failed to cancel invitation.']));
        }
    } catch (Exception $e) {
        // If any error occurred, rollback transaction
        if ($conn->connect_errno === 0) {
            $conn->rollback();
        }
        error_log("Error in cancel_invitation.php: " . $e->getMessage());
        http_response_code(500);
        exit(json_encode(['status' => 'error', 'message' => 'Server error. Please try again later.']));
    } finally {
        $conn->close();
    }
}

// If the request method is not POST, return an error
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    exit(json_encode(['status' => 'error', 'message' => 'Method not allowed']));
}
?>