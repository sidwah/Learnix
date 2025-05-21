<?php
// Authentication check
require_once '../../backend/auth/admin/admin-auth-check.php';
require_once '../../backend/config.php';

// Get default instructor share from revenue_settings
$query = "SELECT setting_value FROM revenue_settings WHERE setting_name = 'instructor_split' LIMIT 1";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $instructor_share = floatval($row['setting_value']);
    
    $response = [
        'status' => 'success',
'instructor_share' => $instructor_share
   ];
} else {
   $response = [
       'status' => 'error',
       'message' => 'Failed to retrieve default share',
       'instructor_share' => 80 // Fallback value
   ];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;