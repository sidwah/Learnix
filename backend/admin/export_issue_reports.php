<?php
require_once '../config.php';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="issue_reports_' . date('Y-m-d_H-i-s') . '.csv"');

// Authentication check
require_once '../auth/admin/admin-auth-check.php';

// Check database connection
if (!$conn) {
    http_response_code(500);
    echo 'Database connection failed';
    exit;
}

try {
    // Fetch issue reports for CSV
    $query = "
        SELECT 
            u.role AS user_type,
            ir.issue_type,
            ir.description,
            ir.status,
            ir.created_at
        FROM issue_reports ir
        JOIN users u ON ir.user_id = u.user_id 
        ORDER BY ir.created_at DESC
    ";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception('Query failed: ' . mysqli_error($conn));
    }

    // Create CSV output
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Write CSV headers
    fputcsv($output, ['User Type', 'Issue Type', 'Description', 'Status', 'Created At']);
    
    // Write data rows
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['user_type'],
            $row['issue_type'],
            $row['description'],
            $row['status'],
            $row['created_at']
        ]);
    }
    
    mysqli_free_result($result);
    fclose($output);
} catch (Exception $e) {
    http_response_code(500);
    echo 'Error: ' . $e->getMessage();
}

mysqli_close($conn);
?>