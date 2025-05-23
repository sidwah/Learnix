<?php
// backend/department/get-department-settings.php
function getDepartmentSettings($conn, $department_id) {
    $query = "SELECT ds.*, d.name as department_name, d.code as department_code, d.description as department_description
              FROM department_settings ds
              RIGHT JOIN departments d ON ds.department_id = d.department_id
              WHERE d.department_id = ? AND d.deleted_at IS NULL";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $department_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    
    // Set defaults if no settings exist
    if (!$data['setting_id']) {
        $data['invitation_expiry_hours'] = 48;
        $data['auto_approve_instructors'] = 0;
        $data['require_mfa'] = 1;
        $data['email_notifications_enabled'] = 1;
    }
    
    return $data;
}
?>