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

try {
    $query = "SELECT logo_path, favicon_path, support_email, support_phone, maintenance_mode FROM settings WHERE id = 1";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception('Query failed: ' . mysqli_error($conn));
    }
    
    $settings = mysqli_fetch_assoc($result);
    mysqli_free_result($result);

    echo json_encode([
        'success' => true,
        'data' => $settings
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