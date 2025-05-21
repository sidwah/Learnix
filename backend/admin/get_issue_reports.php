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

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = isset($_GET['per_page']) ? max(1, (int)$_GET['per_page']) : 10;
$offset = ($page - 1) * $per_page;

try {
    // Fetch total count for pagination
    $query = "SELECT COUNT(*) AS total FROM issue_reports ";
    $result = mysqli_query($conn, $query);
    if (!$result) {
        throw new Exception('Query failed: ' . mysqli_error($conn));
    }
    $total_count = mysqli_fetch_assoc($result)['total'];
    mysqli_free_result($result);
    $total_pages = ceil($total_count / $per_page);

    // Fetch issue reports with user details
    $query = "
        SELECT 
            ir.id,
            ir.issue_type,
            ir.description,
            ir.status,
            ir.created_at,
            ir.updated_at,
            ir.admin_notes,
            ir.file_path,
            u.username,
            u.email,
            u.role AS user_type
        FROM issue_reports ir
        JOIN users u ON ir.user_id = u.user_id
        
        ORDER BY ir.created_at DESC
        LIMIT ? OFFSET ?
    ";
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, 'ii', $per_page, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $issues = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $issues[] = $row;
    }
    mysqli_stmt_close($stmt);

    // Fetch summary counts
    $query = "
        SELECT 
            COUNT(*) AS total,
            SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending,
            SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) AS in_progress,
            SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) AS resolved
        FROM issue_reports
        
    ";
    $result = mysqli_query($conn, $query);
    if (!$result) {
        throw new Exception('Query failed: ' . mysqli_error($conn));
    }
    $summary = mysqli_fetch_assoc($result);
    mysqli_free_result($result);

    echo json_encode([
        'success' => true,
        'data' => [
            'issues' => $issues,
            'summary' => $summary,
            'total_pages' => $total_pages
        ]
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