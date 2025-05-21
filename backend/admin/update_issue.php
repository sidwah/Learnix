<?php
require_once '../config.php';
header('Content-Type: application/json');

// Authentication check
require_once '../auth/admin/admin-auth-check.php';

// Check database connection
if (!$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$issue_id = isset($data['issue_id']) ? (int)$data['issue_id'] : 0;
$status = isset($data['status']) ? trim($data['status']) : '';
$admin_notes = isset($data['admin_notes']) ? trim($data['admin_notes']) : '';

// Validate input
if ($issue_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid issue ID']);
    exit;
}

$valid_statuses = ['Pending', 'In Progress', 'Resolved'];
if (!in_array($status, $valid_statuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    // Prepare and execute update query
    $query = "
        UPDATE issue_reports 
        SET 
            status = ?,
            admin_notes = ?,
            updated_at = NOW()
        WHERE id = ? 
    ";
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, 'ssi', $status, $admin_notes, $issue_id);
    $success = mysqli_stmt_execute($stmt);
    
    if (!$success) {
        throw new Exception('Execute failed: ' . mysqli_stmt_error($stmt));
    }
    
    $affected_rows = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    if ($affected_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Issue not found or no changes made']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Issue updated successfully'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

mysqli_close($conn);
?>