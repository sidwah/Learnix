<?php
require_once '../config.php';

// Authentication check
require_once '../auth/admin/admin-auth-check.php';

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=learnix_faqs_export_' . date('Y-m-d') . '.csv');
header('Pragma: no-cache');
header('Expires: 0');

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for Excel UTF-8 compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add CSV headers
fputcsv($output, ['Category', 'Question', 'Answer', 'Visibility', 'Status', 'Created', 'Last Updated']);

// Get FAQs - similar filtering as get_faqs.php
$where_conditions = array("deleted_at IS NULL");
$params = array();
$param_types = "";

// Process filters if provided
if (isset($_GET['status']) && in_array($_GET['status'], ['active', 'inactive'])) {
    $where_conditions[] = "status = ?";
    $params[] = $_GET['status'];
    $param_types .= "s";
}

if (isset($_GET['visibility'])) {
    $visibility = $_GET['visibility'];
    if ($visibility === 'all') {
        $where_conditions[] = "role_visibility = 'all'";
    } elseif (in_array($visibility, ['student', 'instructor', 'department_head', 'admin'])) {
        $where_conditions[] = "(role_visibility = 'all' OR role_visibility LIKE ?)";
        $params[] = "%$visibility%";
        $param_types .= "s";
    }
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = $_GET['search'];
    $where_conditions[] = "(question LIKE ? OR answer LIKE ? OR category LIKE ?)";
    $params[] = "%$search_term%";
    $params[] = "%$search_term%";
    $params[] = "%$search_term%";
    $param_types .= "sss";
}

// Build where clause
$where_clause = implode(" AND ", $where_conditions);

// Prepare query
$sql = "SELECT category, question, answer, role_visibility, status, created_at, last_updated 
        FROM faqs 
        WHERE $where_clause 
        ORDER BY category, id";

$stmt = $conn->prepare($sql);

if (!empty($param_types)) {
    $stmt->bind_param($param_types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Output data rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['category'],
        $row['question'],
        $row['answer'],
        $row['role_visibility'],
        $row['status'],
        $row['created_at'],
        $row['last_updated']
    ]);
}

// Close statement and connection
$stmt->close();
$conn->close();