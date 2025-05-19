<?php
// Authentication check
require_once '../../backend/auth/admin/admin-auth-check.php';
require_once '../../backend/config.php';

// Initialize response array
$response = [
    'status' => 'error',
    'message' => 'Failed to retrieve department staff',
    'data' => []
];

try {
    // Get filter parameters
    $role = isset($_GET['role']) ? $_GET['role'] : 'all';
    $status = isset($_GET['status']) ? $_GET['status'] : 'all';
    $department = isset($_GET['department']) ? intval($_GET['department']) : 0;
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    
    // Build the query
    $query = "SELECT ds.staff_id, ds.role, ds.appointment_date, ds.status, 
                    u.user_id, u.first_name, u.last_name, u.email, u.profile_pic,
                    d.department_id, d.name as department_name
                FROM department_staff ds
                JOIN users u ON ds.user_id = u.user_id
                JOIN departments d ON ds.department_id = d.department_id
                WHERE ds.deleted_at IS NULL";
    
    $params = [];
    $param_types = "";
    
    // Add role filter
    if ($role !== 'all') {
        $query .= " AND ds.role = ?";
        $params[] = $role;
        $param_types .= "s";
    }
    
    // Add status filter
    if ($status !== 'all') {
        $query .= " AND ds.status = ?";
        $params[] = $status;
        $param_types .= "s";
    }
    
    // Add department filter
    if ($department > 0) {
        $query .= " AND d.department_id = ?";
        $params[] = $department;
        $param_types .= "i";
    }
    
    // Add search filter
    if (!empty($search)) {
        $search = "%$search%";
        $query .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR d.name LIKE ?)";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $param_types .= "ssss";
    }
    
    // Add ordering
    $query .= " ORDER BY u.first_name, u.last_name";
    
    // Prepare and execute query
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($param_types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch all rows
    $staff = [];
    while ($row = $result->fetch_assoc()) {
        // Format profile picture URL
        $row['profile_pic_url'] = !empty($row['profile_pic']) 
            ? '../uploads/department-staff/' . $row['profile_pic'] 
            : '../assets/img/avatars/1.png';
        
        // Format appointment date
        $row['formatted_appointment_date'] = date('F d, Y', strtotime($row['appointment_date']));
        
        // Add to staff array
        $staff[] = $row;
    }
    
    // Update response
    $response = [
        'status' => 'success',
        'message' => count($staff) . ' staff members found',
        'data' => $staff
    ];
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;