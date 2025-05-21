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
    $changedSettings = [];
    
    foreach ($settingsToUpdate as $settingName => $newValue) {
        // Always get the current value from the database directly for accurate history
        $currentValueQuery = "SELECT setting_value FROM revenue_settings WHERE setting_name = ?";
        $currentValueStmt = $conn->prepare($currentValueQuery);
        $currentValueStmt->bind_param("s", $settingName);
        $currentValueStmt->execute();
        $currentValueResult = $currentValueStmt->get_result();
        $currentValue = 0; // Default if not found
        
        if ($currentValueResult && $currentValueResult->num_rows > 0) {
            $currentValueRow = $currentValueResult->fetch_assoc();
            $currentValue = $currentValueRow['setting_value'];
        }
        
        // Only update and record history if there's an actual change
        if ($currentValue != $newValue) {
            // Update the setting
            $updateStmt->bind_param("ds", $newValue, $settingName);
            $updateStmt->execute();
            
            // Record history
            $historyStmt->bind_param("sddis", $settingName, $currentValue, $newValue, $admin_id, $changeReason);
            $historyStmt->execute();
            
            // Track the changed setting for notifications
            $changedSettings[$settingName] = [
                'previous' => $currentValue,
                'new' => $newValue
            ];
            
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
        
        // Get admin details for notification
        $admin_query = "SELECT CONCAT(first_name, ' ', last_name) AS admin_name FROM users WHERE user_id = ?";
        $admin_stmt = $conn->prepare($admin_query);
        $admin_stmt->bind_param("i", $admin_id);
        $admin_stmt->execute();
        $admin_result = $admin_stmt->get_result();
        $admin_name = "An administrator";
        
        if ($admin_result && $admin_result->num_rows > 0) {
            $admin_row = $admin_result->fetch_assoc();
            $admin_name = $admin_row['admin_name'];
        }
        
        // Create notification message
        $notification_title = "Payment Policy Update";
        $notification_message = "The platform payment policies have been updated by " . $admin_name . ". ";
        
        // Add details about what changed
        $changes = [];
        $readableSettingNames = [
            'instructor_split' => 'Instructor Revenue Share',
            'platform_fee' => 'Platform Fee',
            'holding_period' => 'Payment Holding Period',
            'minimum_payout' => 'Minimum Payout Threshold'
        ];
        
        foreach ($changedSettings as $setting => $values) {
            $readableName = isset($readableSettingNames[$setting]) ? $readableSettingNames[$setting] : $setting;
            
            if ($setting == 'instructor_split' || $setting == 'platform_fee') {
                $changes[] = $readableName . " changed from " . $values['previous'] . "% to " . $values['new'] . "%";
            } elseif ($setting == 'holding_period') {
                $changes[] = $readableName . " changed from " . $values['previous'] . " days to " . $values['new'] . " days";
            } else {
                $changes[] = $readableName . " changed from $" . $values['previous'] . " to $" . $values['new'];
            }
        }
        
        $notification_message .= "Changes: " . implode(", ", $changes) . ".";
        
        if (!empty($changeReason)) {
            $notification_message .= " Reason: \"" . $changeReason . "\"";
        }
        
        // Get all users except students to notify them
        $users_query = "SELECT user_id FROM users 
                        WHERE role != 'student' AND deleted_at IS NULL AND status = 'active'";
        $users_result = mysqli_query($conn, $users_query);
        
        if ($users_result && mysqli_num_rows($users_result) > 0) {
            // Prepare notification insertion statement
            $notification_stmt = $conn->prepare("INSERT INTO user_notifications 
                                            (user_id, type, title, message, related_id, related_type) 
                                            VALUES (?, 'payment_update', ?, ?, NULL, 'system')");
            
            // Send in-app notifications to each user
            while ($user = mysqli_fetch_assoc($users_result)) {
                // Skip sending to the admin who made the change
                if ($user['user_id'] == $admin_id) {
                    continue;
                }
                
                // Insert in-app notification
                $notification_stmt->bind_param("iss", $user['user_id'], $notification_title, $notification_message);
                $notification_stmt->execute();
            }
        }
        
        $conn->commit();
        
        $_SESSION['payment_settings_message'] = [
            'status' => 'success', 
            'message' => 'Payment settings updated successfully. In-app notifications sent to all relevant users.'
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