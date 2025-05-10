<?php
require_once '../config.php';
session_start();

// Set JSON header
header('Content-Type: application/json');

// Check if user is department head
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized access', 
        'active' => [], 
        'pending' => [], 
        'inactive' => [], 
        'summary' => [
            'total' => 0, 
            'active_count' => 0, 
            'pending_count' => 0, 
            'inactive_count' => 0
        ]
    ]);
    exit;
}

$departmentId = $_SESSION['department_id'];

// Log the request for debugging
error_log("get_instructors.php - Department ID: $departmentId");

try {
    // Initialize response data
    $response = [
        'success' => true,
        'active' => [],
        'pending' => [],
        'inactive' => [],
        'summary' => [
            'total' => 0,
            'active_count' => 0,
            'pending_count' => 0,
            'inactive_count' => 0
        ]
    ];

    // Get active instructors
    $stmt = $conn->prepare("
        SELECT 
            u.user_id,
            u.first_name,
            u.last_name,
            u.email,
            u.profile_pic,
            i.instructor_id,
            di.status,
            COUNT(DISTINCT ci.course_id) as course_count,
            MAX(u.updated_at) as last_active
        FROM department_instructors di
        JOIN instructors i ON di.instructor_id = i.instructor_id
        JOIN users u ON i.user_id = u.user_id
        LEFT JOIN course_instructors ci ON i.instructor_id = ci.instructor_id AND ci.deleted_at IS NULL
        WHERE di.department_id = ? 
        AND di.status = 'active' 
        AND di.deleted_at IS NULL
        GROUP BY i.instructor_id
        ORDER BY u.first_name, u.last_name
    ");
    
    $stmt->bind_param("i", $departmentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $response['active'][] = [
            'instructor_id' => (int)$row['instructor_id'],
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'email' => $row['email'],
            'profile_pic' => $row['profile_pic'] ?? 'default.png',
            'course_count' => (int)$row['course_count'],
            'last_active' => $row['last_active'] ? getTimeAgo($row['last_active']) : 'Never'
        ];
    }
    $stmt->close();
    
    // Get pending invitations - make sure they exist in the table
    $stmt = $conn->prepare("
        SELECT 
            id,
            email,
            first_name,
            last_name,
            created_at,
            expiry_time,
            TIMESTAMPDIFF(HOUR, NOW(), expiry_time) as hours_remaining,
            TIMESTAMPDIFF(MINUTE, NOW(), expiry_time) as minutes_remaining
        FROM instructor_invitations
        WHERE department_id = ? 
        AND is_used = 0 
        AND expiry_time > NOW()
        ORDER BY created_at DESC
    ");
    
    $stmt->bind_param("i", $departmentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $time_left = '';
        if ($row['hours_remaining'] > 0) {
            $time_left = $row['hours_remaining'] . ' hours left';
        } else if ($row['minutes_remaining'] > 0) {
            $time_left = $row['minutes_remaining'] . ' minutes left';
        } else {
            $time_left = 'Expiring soon';
        }
        
        $response['pending'][] = [
            'id' => (int)$row['id'],
            'name' => ($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''),
            'email' => $row['email'],
            'invited' => date('M d', strtotime($row['created_at'])),
            'expires' => $time_left,
            'expires_status' => $row['hours_remaining'] < 6 ? 'warning' : 'success'
        ];
    }
    $stmt->close();
    
    // Get inactive instructors
    $stmt = $conn->prepare("
        SELECT 
            u.user_id,
            u.first_name,
            u.last_name,
            u.email,
            u.profile_pic,
            i.instructor_id,
            di.deleted_at,
            di.status
        FROM department_instructors di
        JOIN instructors i ON di.instructor_id = i.instructor_id
        JOIN users u ON i.user_id = u.user_id
        WHERE di.department_id = ? 
        AND di.status = 'inactive' 
        AND di.deleted_at IS NOT NULL
        ORDER BY di.deleted_at DESC
    ");
    
    $stmt->bind_param("i", $departmentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Get deactivation reason from logs
        $stmt2 = $conn->prepare("
            SELECT details 
            FROM department_activity_logs 
            WHERE department_id = ? 
            AND action_type = 'instructor_deactivate'
            AND JSON_EXTRACT(details, '$.instructor_id') = ?
            ORDER BY performed_at DESC 
            LIMIT 1
        ");
        $stmt2->bind_param("ii", $departmentId, $row['instructor_id']);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        
        $reason = 'N/A';
        if ($row2 = $result2->fetch_assoc()) {
            $details = json_decode($row2['details'], true);
            $reason = $details['reason'] ?? 'N/A';
        }
        $stmt2->close();
        
        $response['inactive'][] = [
            'instructor_id' => (int)$row['instructor_id'],
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'email' => $row['email'],
            'profile_pic' => $row['profile_pic'] ?? 'default.png',
            'deactivated' => date('M d, Y', strtotime($row['deleted_at'])),
            'reason' => $reason
        ];
    }
    $stmt->close();
    
    // Calculate summary
    $response['summary']['active_count'] = count($response['active']);
    $response['summary']['pending_count'] = count($response['pending']);
    $response['summary']['inactive_count'] = count($response['inactive']);
    $response['summary']['total'] = $response['summary']['active_count'] + 
                                   $response['summary']['pending_count'] + 
                                   $response['summary']['inactive_count'];
    
    // Log the response for debugging
    error_log("get_instructors.php - Response prepared successfully: " . 
              "Active: " . $response['summary']['active_count'] . 
              ", Pending: " . $response['summary']['pending_count'] . 
              ", Inactive: " . $response['summary']['inactive_count']);
    
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error in get_instructors.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error occurred',
        'message' => $e->getMessage(),
        'active' => [],
        'pending' => [],
        'inactive' => [],
        'summary' => [
            'total' => 0,
            'active_count' => 0,
            'pending_count' => 0,
            'inactive_count' => 0
        ]
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

function getTimeAgo($datetime) {
    if (empty($datetime)) {
        return 'Never';
    }
    
    $time_ago = strtotime($datetime);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    
    if ($time_difference < 1) return 'just now';
    
    $condition = array(
        12 * 30 * 24 * 60 * 60 => 'year',
        30 * 24 * 60 * 60 => 'month',
        24 * 60 * 60 => 'day',
        60 * 60 => 'hour',
        60 => 'minute',
        1 => 'second'
    );
    
    foreach ($condition as $secs => $str) {
        $d = $time_difference / $secs;
        if ($d >= 1) {
            $t = round($d);
            return $t . ' ' . $str . ($t > 1 ? 's' : '') . ' ago';
        }
    }
    
    return 'Never';
}
?>