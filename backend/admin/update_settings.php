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

try {
    // Initialize response
    $response = ['success' => true, 'message' => 'Settings updated successfully'];

    // Handle file uploads
    $upload_dir = '../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0775, true);
    }

    $logo_path = null;
    $favicon_path = null;

    // Process logo
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $logo_ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        if (!in_array(strtolower($logo_ext), ['png', 'jpg', 'jpeg'])) {
            throw new Exception('Invalid logo file type. Only PNG, JPG, JPEG allowed.');
        }
        $logo_name = 'logo_' . time() . '.' . $logo_ext;
        $logo_dest = $upload_dir . $logo_name;
        if (!move_uploaded_file($_FILES['logo']['tmp_name'], $logo_dest)) {
            throw new Exception('Failed to upload logo.');
        }
        $logo_path = 'uploads/' . $logo_name;
    }

    // Process favicon
    if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
        $favicon_ext = pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION);
        if (!in_array(strtolower($favicon_ext), ['ico', 'png'])) {
            throw new Exception('Invalid favicon file type. Only ICO, PNG allowed.');
        }
        $favicon_name = 'favicon_' . time() . '.' . $favicon_ext;
        $favicon_dest = $upload_dir . $favicon_name;
        if (!move_uploaded_file($_FILES['favicon']['tmp_name'], $favicon_dest)) {
            throw new Exception('Failed to upload favicon.');
        }
        $favicon_path = 'Uploads/' . $favicon_name;
    }

    // Get form data
    $support_email = isset($_POST['support_email']) ? trim($_POST['support_email']) : null;
    $support_phone = isset($_POST['support_phone']) ? trim($_POST['support_phone']) : null;
    $maintenance_mode = isset($_POST['maintenance_mode']) ? (int)$_POST['maintenance_mode'] : 0;
    $force_password_reset = isset($_POST['force_password_reset']) && $_POST['force_password_reset'] === '1';

    // Validate email
    if ($support_email && !filter_var($support_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid support email format.');
    }

    // Prepare update query for settings
    $query = "UPDATE settings SET ";
    $params = [];
    $types = '';
    
    if ($logo_path) {
        $query .= "logo_path = ?, ";
        $params[] = $logo_path;
        $types .= 's';
    }
    if ($favicon_path) {
        $query .= "favicon_path = ?, ";
        $params[] = $favicon_path;
        $types .= 's';
    }
    if ($support_email) {
        $query .= "support_email = ?, ";
        $params[] = $support_email;
        $types .= 's';
    }
    if ($support_phone) {
        $query .= "support_phone = ?, ";
        $params[] = $support_phone;
        $types .= 's';
    }
    $query .= "maintenance_mode = ?, updated_at = NOW() ";
    $params[] = $maintenance_mode;
    $types .= 'i';
    
    $query .= "WHERE id = 1";
    
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . mysqli_error($conn));
    }
    
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Execute failed: ' . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);

    // Handle force password reset
    if ($force_password_reset) {
        $query = "UPDATE users SET force_password_reset = 1";
        if (!mysqli_query($conn, $query)) {
            throw new Exception('Failed to force password reset: ' . mysqli_error($conn));
        }
        $response['message'] .= ' Password reset enforced for all users.';
    }

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

mysqli_close($conn);
?>