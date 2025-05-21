<?php
// Authentication check
require_once '../../backend/auth/admin/admin-auth-check.php';
require_once '../../backend/config.php';

// Ensure we're processing a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../admin/payment-settings.php');
    exit;
}

// Initialize response array
session_start();
$_SESSION['payment_settings_message'] = [
    'status' => 'error',
    'message' => 'Failed to update payment settings'
];

// Get and validate input
$instructorSplit = isset($_POST['instructor_split']) ? floatval($_POST['instructor_split']) : 0;
$platformFee = isset($_POST['platform_fee']) ? floatval($_POST['platform_fee']) : 0;
$holdingPeriod = isset($_POST['holding_period']) ? intval($_POST['holding_period']) : 0;
$minimumPayout = isset($_POST['minimum_payout']) ? floatval($_POST['minimum_payout']) : 0;
$changeReason = isset($_POST['change_reason']) ? trim($_POST['change_reason']) : '';

// Validate inputs
if ($instructorSplit < 0 || $instructorSplit > 100) {
    $_SESSION['payment_settings_message']['message'] = 'Instructor split must be between 0 and 100';
    header('Location: ../../admin/payment-settings.php');
    exit;
}

if ($platformFee < 0 || $platformFee > 100) {
    $_SESSION['payment_settings_message']['message'] = 'Platform fee must be between 0 and 100';
    header('Location: ../../admin/payment-settings.php');
    exit;
}

if (abs(($instructorSplit + $platformFee) - 100) > 0.01) { // Allow small floating point error
    $_SESSION['payment_settings_message']['message'] = 'Instructor split and platform fee must total 100%';
    header('Location: ../../admin/payment-settings.php');
    exit;
}

if ($holdingPeriod < 0) {
    $_SESSION['payment_settings_message']['message'] = 'Holding period cannot be negative';
    header('Location: ../../admin/payment-settings.php');
    exit;
}

if ($minimumPayout < 0) {
    $_SESSION['payment_settings_message']['message'] = 'Minimum payout cannot be negative';
    header('Location: ../../admin/payment-settings.php');
    exit;
}

// Process the request
try {
    // Get current settings
    $query = "SELECT setting_name, setting_value FROM revenue_settings";
    $result = mysqli_query($conn, $query);
    $currentSettings = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $currentSettings[$row['setting_name']] = $row['setting_value'];
        }
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    $admin_id = $_SESSION['user_id'];
    
    // Update settings and record history if values changed
    $settingsToUpdate = [
        'instructor_split' => $instructorSplit,
        'platform_fee' => $platformFee,
        'holding_period' => $holdingPeriod,
        'minimum_payout' => $minimumPayout
    ];
    
    $updateStmt = $conn->prepare("UPDATE revenue_settings SET setting_value = ?, updated_at = NOW() WHERE setting_name = ?");
    $historyStmt = $conn->prepare("INSERT INTO revenue_settings_history (setting_name, previous_value, new_value, changed_by, change_reason) VALUES (?, ?, ?, ?, ?)");
    
    $changesCount = 0;
    
    foreach ($settingsToUpdate as $settingName => $newValue) {
        if (!isset($currentSettings[$settingName]) || $currentSettings[$settingName] != $newValue) {
            // Update the setting
            $updateStmt->bind_param("ds", $newValue, $settingName);
            $updateStmt->execute();
            
            // Record history
            $previousValue = isset($currentSettings[$settingName]) ? $currentSettings[$settingName] : 0;
            $historyStmt->bind_param("sddis", $settingName, $previousValue, $newValue, $admin_id, $changeReason);
            $historyStmt->execute();
            
            $changesCount++;
        }
    }
    
    // Log activity
    if ($changesCount > 0) {
        $activity_details = [
            'previous' => array_intersect_key($currentSettings, $settingsToUpdate),
            'new' => $settingsToUpdate,
            'reason' => $changeReason
        ];
        
        $log_details = json_encode($activity_details);
        $log_query = "INSERT INTO user_activity_logs (user_id, activity_type, activity_details, ip_address) 
                      VALUES (?, 'update_revenue_settings', ?, ?)";
        $log_stmt = $conn->prepare($log_query);
        $ip = $_SERVER['REMOTE_ADDR'];
        $log_stmt->bind_param("iss", $admin_id, $log_details, $ip);
        $log_stmt->execute();
        
        $conn->commit();
        
        $_SESSION['payment_settings_message'] = [
            'status' => 'success', 
            'message' => 'Payment settings updated successfully'
        ];
    } else {
        $conn->commit();
        $_SESSION['payment_settings_message'] = [
            'status' => 'info', 
            'message' => 'No changes were made to payment settings'
        ];
    }
    
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['payment_settings_message'] = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// Redirect back to payment settings page
header('Location: ../../admin/payment-settings.php');
exit;