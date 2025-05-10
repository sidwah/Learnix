<?php
require_once '../config.php';
session_start();

// Add header to ensure JSON response
header('Content-Type: application/json');

// Check if user is department head
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$departmentId = $_SESSION['department_id'];
$departmentHeadId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$action = $_POST['action'] ?? '';
$instructorId = intval($_POST['instructor_id'] ?? 0);
$instructorEmail = $_POST['instructor_email'] ?? '';
$instructorName = $_POST['instructor_name'] ?? '';
$reason = $_POST['reason'] ?? '';

if (empty($action) || $instructorId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit;
}

// Log the request for debugging
error_log("manage_instructor_status.php - Action: $action, Instructor ID: $instructorId");

try {
    $conn->begin_transaction();
    
    switch ($action) {
        case 'deactivate':
            // Verify instructor belongs to this department and is active
            $stmt = $conn->prepare("
                SELECT di.id, u.email, u.first_name, u.last_name 
                FROM department_instructors di
                JOIN instructors i ON di.instructor_id = i.instructor_id
                JOIN users u ON i.user_id = u.user_id
                WHERE di.instructor_id = ? AND di.department_id = ? 
                AND di.status = 'active' AND di.deleted_at IS NULL
            ");
            $stmt->bind_param("ii", $instructorId, $departmentId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                $stmt->close();
                throw new Exception('Instructor not found or already inactive');
            }
            
            $instructorData = $result->fetch_assoc();
            $stmt->close();
            
            // If email/name not provided, use from database
            if (empty($instructorEmail)) {
                $instructorEmail = $instructorData['email'];
            }
            if (empty($instructorName)) {
                $instructorName = $instructorData['first_name'] . ' ' . $instructorData['last_name'];
            }
            
            // Deactivate instructor
            $stmt = $conn->prepare("
                UPDATE department_instructors 
                SET status = 'inactive', deleted_at = NOW() 
                WHERE instructor_id = ? AND department_id = ?
            ");
            $stmt->bind_param("ii", $instructorId, $departmentId);
            $stmt->execute();
            $stmt->close();
            
            // Log the action
            $stmt = $conn->prepare("
                INSERT INTO department_activity_logs 
                (department_id, user_id, action_type, details, performed_at) 
                VALUES (?, ?, 'instructor_deactivate', ?, NOW())
            ");
            $details = json_encode([
                'instructor_id' => $instructorId,
                'instructor_name' => $instructorName,
                'reason' => $reason
            ]);
            $stmt->bind_param("iis", $departmentId, $departmentHeadId, $details);
            $stmt->execute();
            $stmt->close();
            
            // Note: Email functionality is removed for now to prevent dependency issues
            $message = 'Instructor deactivated successfully';
            break;
            
        case 'reactivate':
            // Verify instructor belongs to this department and is inactive
            $stmt = $conn->prepare("
                SELECT di.id, u.email, u.first_name, u.last_name 
                FROM department_instructors di
                JOIN instructors i ON di.instructor_id = i.instructor_id
                JOIN users u ON i.user_id = u.user_id
                WHERE di.instructor_id = ? AND di.department_id = ? 
                AND di.status = 'inactive' AND di.deleted_at IS NOT NULL
            ");
            $stmt->bind_param("ii", $instructorId, $departmentId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                $stmt->close();
                throw new Exception('Instructor not found or already active');
            }
            
            $instructorData = $result->fetch_assoc();
            $stmt->close();
            
            // If email/name not provided, use from database
            if (empty($instructorEmail)) {
                $instructorEmail = $instructorData['email'];
            }
            if (empty($instructorName)) {
                $instructorName = $instructorData['first_name'] . ' ' . $instructorData['last_name'];
            }
            
            // Reactivate instructor
            $stmt = $conn->prepare("
                UPDATE department_instructors 
                SET status = 'active', deleted_at = NULL 
                WHERE instructor_id = ? AND department_id = ?
            ");
            $stmt->bind_param("ii", $instructorId, $departmentId);
            $stmt->execute();
            $stmt->close();
            
            // Log the action
            $stmt = $conn->prepare("
                INSERT INTO department_activity_logs 
                (department_id, user_id, action_type, details, performed_at) 
                VALUES (?, ?, 'instructor_reactivate', ?, NOW())
            ");
            $details = json_encode([
                'instructor_id' => $instructorId,
                'instructor_name' => $instructorName
            ]);
            $stmt->bind_param("iis", $departmentId, $departmentHeadId, $details);
            $stmt->execute();
            $stmt->close();
            
            $message = 'Instructor reactivated successfully';
            break;
            
        case 'remove':
            // Verify instructor belongs to this department
            $stmt = $conn->prepare("
                SELECT di.id, u.email, u.first_name, u.last_name 
                FROM department_instructors di
                JOIN instructors i ON di.instructor_id = i.instructor_id
                JOIN users u ON i.user_id = u.user_id
                WHERE di.instructor_id = ? AND di.department_id = ?
            ");
            $stmt->bind_param("ii", $instructorId, $departmentId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                $stmt->close();
                throw new Exception('Instructor not found in your department');
            }
            
            $instructorData = $result->fetch_assoc();
            $stmt->close();
            
            // If email/name not provided, use from database
            if (empty($instructorEmail)) {
                $instructorEmail = $instructorData['email'];
            }
            if (empty($instructorName)) {
                $instructorName = $instructorData['first_name'] . ' ' . $instructorData['last_name'];
            }
            
            // Check if instructor has courses
            $stmt = $conn->prepare("
                SELECT COUNT(*) as course_count 
                FROM course_instructors ci
                WHERE ci.instructor_id = ? AND ci.deleted_at IS NULL
            ");
            $stmt->bind_param("i", $instructorId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            if ($row['course_count'] > 0) {
                // Soft delete their course associations
                $stmt = $conn->prepare("
                    UPDATE course_instructors 
                    SET deleted_at = NOW() 
                    WHERE instructor_id = ?
                ");
                $stmt->bind_param("i", $instructorId);
                $stmt->execute();
                $stmt->close();
            }
            
            // Remove instructor from department (soft delete)
            $stmt = $conn->prepare("
                UPDATE department_instructors 
                SET deleted_at = NOW(), status = 'inactive'
                WHERE instructor_id = ? AND department_id = ?
            ");
            $stmt->bind_param("ii", $instructorId, $departmentId);
            $stmt->execute();
            $stmt->close();
            
            // Log the action
            $stmt = $conn->prepare("
                INSERT INTO department_activity_logs 
                (department_id, user_id, action_type, details, performed_at) 
                VALUES (?, ?, 'instructor_remove', ?, NOW())
            ");
            $details = json_encode([
                'instructor_id' => $instructorId,
                'instructor_name' => $instructorName,
                'courses_affected' => $row['course_count']
            ]);
            $stmt->bind_param("iis", $departmentId, $departmentHeadId, $details);
            $stmt->execute();
            $stmt->close();
            
            $message = 'Instructor removed successfully';
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
    $conn->commit();
    
    // Log success for debugging
    error_log("manage_instructor_status.php - Action '$action' completed successfully for instructor $instructorId");
    
    echo json_encode([
        'success' => true, 
        'message' => $message,
        'action' => $action,
        'instructor_id' => $instructorId
    ]);

} catch (Exception $e) {
    $conn->rollback();
    error_log("Error in manage_instructor_status.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage(),
        'action' => $action,
        'instructor_id' => $instructorId
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>