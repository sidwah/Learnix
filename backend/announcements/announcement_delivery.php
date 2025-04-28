<?php

/**
 * Announcement Delivery
 * 
 * This file contains functions for processing and delivering announcements
 * to users through various channels (in-app, email, push).
 * 
 * @package Learnix
 * @subpackage Announcements
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../phpmailer/vendor/autoload.php'; // Adjust path as needed

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Queues an announcement for delivery to recipients
 * 
 * @param int $announcement_id The announcement ID
 * @param array $recipients Array of user IDs to receive the announcement
 * @return bool True on success, false on failure
 */
function queueAnnouncementDelivery($announcement_id, $recipients)
{
    global $conn;

    if (empty($recipients)) {
        return false;
    }

    try {
        // Begin transaction
        $conn->begin_transaction();

        // Get announcement details
        $stmt = $conn->prepare("
            SELECT title, content, importance
            FROM course_announcements 
            WHERE announcement_id = ?
        ");
        $stmt->bind_param("i", $announcement_id);
        $stmt->execute();
        $announcement = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$announcement) {
            return false;
        }

        // Determine which delivery channels to use based on importance
        $channels = ['In-App']; // Always deliver in-app

        // Add email for Medium, High, Critical
        if (in_array($announcement['importance'], ['Medium', 'High', 'Critical'])) {
            $channels[] = 'Email';
        }

        // Add push for High, Critical
        if (in_array($announcement['importance'], ['High', 'Critical'])) {
            $channels[] = 'Push';
        }

        // Insert delivery records for each recipient and channel
        $insertStmt = $conn->prepare("
            INSERT INTO announcement_delivery_logs 
            (announcement_id, user_id, delivery_channel, delivery_status) 
            VALUES (?, ?, ?, 'Pending')
        ");

        $now = date('Y-m-d H:i:s');
        $totalQueued = 0;

        foreach ($recipients as $user_id) {
            foreach ($channels as $channel) {
                // Skip if already has a delivery record for this channel
                $checkStmt = $conn->prepare("
                    SELECT delivery_id 
                    FROM announcement_delivery_logs 
                    WHERE announcement_id = ? AND user_id = ? AND delivery_channel = ?
                ");
                $checkStmt->bind_param("iis", $announcement_id, $user_id, $channel);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();
                $checkStmt->close();

                if ($checkResult->num_rows === 0) {
                    $insertStmt->bind_param("iis", $announcement_id, $user_id, $channel);
                    $insertStmt->execute();
                    $totalQueued++;
                }
            }
        }
        $insertStmt->close();

        // Update statistics with queued count
        $stmt = $conn->prepare("
            UPDATE announcement_statistics 
            SET delivery_count = delivery_count + ? 
            WHERE announcement_id = ?
        ");
        $stmt->bind_param("ii", $totalQueued, $announcement_id);
        $stmt->execute();
        $stmt->close();

        // Commit transaction
        $conn->commit();

        return true;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error queuing announcement delivery: " . $e->getMessage());
        return false;
    }
}

/**
 * Processes the delivery queue for pending announcements
 * 
 * This function should be called by a cron job or task scheduler
 * to process pending announcement deliveries
 * 
 * @param int $batchSize Maximum number of deliveries to process in one batch
 * @return array Statistics about processed deliveries
 */
function processAnnouncementDeliveryQueue($batchSize = 50)
{
    global $conn;

    $stats = [
        'total' => 0,
        'success' => 0,
        'failed' => 0,
        'by_channel' => [
            'Email' => ['success' => 0, 'failed' => 0],
            'Push' => ['success' => 0, 'failed' => 0],
            'In-App' => ['success' => 0, 'failed' => 0]
        ]
    ];

    try {
        // Begin transaction
        $conn->begin_transaction();

        // Get pending deliveries
        $stmt = $conn->prepare("
            SELECT dl.delivery_id, dl.announcement_id, dl.user_id, dl.delivery_channel,
                   a.title, a.content, a.importance, a.is_pinned,
                   u.email, u.first_name, u.last_name, u.username
            FROM announcement_delivery_logs dl
            JOIN course_announcements a ON dl.announcement_id = a.announcement_id
            JOIN users u ON dl.user_id = u.user_id
            WHERE dl.delivery_status = 'Pending'
            ORDER BY a.importance DESC, dl.delivery_id ASC
            LIMIT ?
        ");
        $stmt->bind_param("i", $batchSize);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $now = date('Y-m-d H:i:s');

        while ($delivery = $result->fetch_assoc()) {
            $stats['total']++;
            $success = false;

            switch ($delivery['delivery_channel']) {
                case 'Email':
                    $success = sendAnnouncementEmail($delivery);
                    break;

                case 'Push':
                    $success = sendAnnouncementPush($delivery);
                    break;

                case 'In-App':
                    // In-app notifications are considered delivered when queued
                    $success = true;
                    break;
            }

            // Update delivery status
            $status = $success ? 'Sent' : 'Failed';
            $updateStmt = $conn->prepare("
                UPDATE announcement_delivery_logs 
                SET delivery_status = ?, delivery_time = ?, 
                    error_details = ? 
                WHERE delivery_id = ?
            ");

            $errorDetails = $success ? null : "Failed to deliver via " . $delivery['delivery_channel'];
            $updateStmt->bind_param("sssi", $status, $now, $errorDetails, $delivery['delivery_id']);
            $updateStmt->execute();
            $updateStmt->close();

            // Update statistics
            if ($success) {
                $stats['success']++;
                $stats['by_channel'][$delivery['delivery_channel']]['success']++;
            } else {
                $stats['failed']++;
                $stats['by_channel'][$delivery['delivery_channel']]['failed']++;
            }
        }

        // Commit transaction
        $conn->commit();

        return $stats;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error processing announcement delivery queue: " . $e->getMessage());
        return ['error' => $e->getMessage()];
    }
}

/**
 * Sends an announcement via email
 * 
 * @param array $delivery The delivery record with announcement and user details
 * @return bool True on success, false on failure
 */
function sendAnnouncementEmail($delivery)
{
    try {
        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'barrock.sidwah@st.rmu.edu.gh';
        $mail->Password = 'mtltujmsmmlkkxtv'; // Use a secure method instead
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Recipient
        $mail->setFrom('no-reply@learnix.com', 'Learnix');
        $mail->addAddress($delivery['email'], $delivery['first_name'] . ' ' . $delivery['last_name']);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Learnix Announcement: ' . $delivery['title'];

        // Importance indicator
        $importanceClass = '';
        $importanceText = '';

        switch ($delivery['importance']) {
            case 'Critical':
                $importanceClass = 'critical';
                $importanceText = 'CRITICAL';
                break;
            case 'High':
                $importanceClass = 'high';
                $importanceText = 'HIGH IMPORTANCE';
                break;
            case 'Medium':
                $importanceClass = 'medium';
                $importanceText = 'MEDIUM IMPORTANCE';
                break;
            default:
                $importanceClass = 'low';
                $importanceText = '';
        }

        // Email template
        $mail->Body = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Learnix Announcement</title>
            <style>
                @import url(\'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap\');
                
                body {
                    font-family: \'Poppins\', Arial, sans-serif;
                    line-height: 1.6;
                    color: #333333;
                    margin: 0;
                    padding: 0;
                    background-color: #f9f9f9;
                }
                
                .email-container {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #ffffff;
                    border-radius: 8px;
                    overflow: hidden;
                    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                }
                
                .email-header {
                    background: linear-gradient(135deg, #3a66db 0%, #5f85e5 100%);
                    padding: 30px;
                    text-align: center;
                }
                
                .email-body {
                    padding: 30px;
                }
                
                .email-footer {
                    background-color: #f5f5f5;
                    padding: 20px;
                    text-align: center;
                    font-size: 12px;
                    color: #666666;
                }
                
                h1 {
                    color: #ffffff;
                    margin: 0;
                    font-size: 24px;
                    font-weight: 600;
                }
                
                h2 {
                    color: #3a66db;
                    margin-top: 0;
                    font-size: 20px;
                    font-weight: 500;
                }
                
                p {
                    margin: 16px 0;
                    font-size: 15px;
                }
                
                .importance-tag {
                    display: inline-block;
                    padding: 5px 10px;
                    border-radius: 4px;
                    font-size: 12px;
                    font-weight: 600;
                    margin-bottom: 16px;
                }
                
                .importance-critical {
                    background-color: #ffebee;
                    color: #d32f2f;
                    border-left: 4px solid #d32f2f;
                }
                
                .importance-high {
                    background-color: #fff8e1;
                    color: #ff8f00;
                    border-left: 4px solid #ff8f00;
                }
                
                .importance-medium {
                    background-color: #e8f5e9;
                    color: #388e3c;
                    border-left: 4px solid #388e3c;
                }
                
                .content-container {
                    background-color: #f9f9f9;
                    border-radius: 6px;
                    padding: 20px;
                    margin: 20px 0;
                }
                
                .action-button {
                    display: inline-block;
                    padding: 12px 24px;
                    background-color: #3a66db;
                    color: white;
                    text-decoration: none;
                    border-radius: 4px;
                    font-weight: 500;
                    margin-top: 20px;
                }
                
                .social-icons {
                    margin-top: 20px;
                }
                
                .social-icons a {
                    display: inline-block;
                    margin: 0 10px;
                    color: #3a66db;
                    text-decoration: none;
                }
                
                @media screen and (max-width: 600px) {
                    .email-container {
                        width: 100%;
                        border-radius: 0;
                    }
                    
                    .email-header, .email-body, .email-footer {
                        padding: 20px;
                    }
                }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="email-header">
                    <h1>Learnix</h1>
                </div>
                
                <div class="email-body">
                    <h2>' . htmlspecialchars($delivery['title']) . '</h2>
                    
                    ' . ($importanceText ? '<div class="importance-tag importance-' . $importanceClass . '">' . $importanceText . '</div>' : '') . '
                    
                    <div class="content-container">
                        ' . $delivery['content'] . '
                    </div>
                    
                    <a href="http://localhost:8888/learnix/" class="action-button">View in Dashboard</a>
                </div>
                
                <div class="email-footer">
                    <p>&copy; ' . date('Y') . ' Learnix. All rights reserved.</p>
                    <p>If you do not wish to receive these emails, you can adjust your notification settings in your profile.</p>
                    <div class="social-icons">
                        <a href="#">Twitter</a> | 
                        <a href="#">Facebook</a> | 
                        <a href="#">Instagram</a>
                    </div>
                </div>
            </div>
        </body>
        </html>';

        // Plain text alternative
        $mail->AltBody = $delivery['title'] . "\n\n" .
            ($importanceText ? $importanceText . "\n\n" : '') .
            strip_tags($delivery['content']) . "\n\n" .
            "View in Dashboard: http://localhost:8888/learnix/";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error sending announcement email: " . $e->getMessage());
        return false;
    }
}

/**
 * Sends an announcement via push notification
 * 
 * @param array $delivery The delivery record with announcement and user details
 * @return bool True on success, false on failure
 */
function sendAnnouncementPush($delivery)
{
    // This implementation depends on your push notification provider
    // Below is a placeholder implementation - you'll need to adapt this
    // to your specific push notification service (Firebase, OneSignal, etc.)

    try {
        // Get user device tokens
        global $conn;
        $stmt = $conn->prepare("
                             SELECT device_token, device_platform 
                             FROM user_devices 
                             WHERE user_id = ? AND is_active = 1
                         ");
        $stmt->bind_param("i", $delivery['user_id']);
        $stmt->execute();
        $deviceResult = $stmt->get_result();
        $stmt->close();

        if ($deviceResult->num_rows === 0) {
            // No registered devices
            return false;
        }

        $successCount = 0;

        while ($device = $deviceResult->fetch_assoc()) {
            // Format the notification based on importance
            $notificationTitle = 'Learnix';
            $notificationBody = $delivery['title'];

            if ($delivery['importance'] === 'Critical') {
                $notificationTitle = '🚨 CRITICAL: ' . $notificationTitle;
            } elseif ($delivery['importance'] === 'High') {
                $notificationTitle = '⚠️ ' . $notificationTitle;
            }

            // Configure payload
            $payload = [
                'notification' => [
                    'title' => $notificationTitle,
                    'body' => $notificationBody,
                    'sound' => ($delivery['importance'] === 'Critical') ? 'critical_alert.wav' : 'default',
                    'badge' => 1,
                    'click_action' => 'OPEN_ANNOUNCEMENT_DETAIL'
                ],
                'data' => [
                    'announcement_id' => $delivery['announcement_id'],
                    'importance' => $delivery['importance'],
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ];

            // Send notification based on platform
            if ($device['device_platform'] === 'android') {
                // Send to Firebase (Android)
                // Implement Android-specific code here
                $success = sendFirebasePush($device['device_token'], $payload);
            } elseif ($device['device_platform'] === 'ios') {
                // Send to Apple (iOS)
                // Implement iOS-specific code here
                $success = sendApplePush($device['device_token'], $payload);
            } else {
                $success = false;
            }

            if ($success) {
                $successCount++;
            }
        }

        // Return true if at least one notification was sent successfully
        return $successCount > 0;
    } catch (Exception $e) {
        error_log("Error sending announcement push notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Placeholder function for sending Firebase push notifications
 * Replace with your actual implementation
 * 
 * @param string $token The device token
 * @param array $payload The notification payload
 * @return bool True on success, false on failure
 */
function sendFirebasePush($token, $payload)
{
    // This is a placeholder - implement your actual Firebase push logic
    // Example implementation:

    try {
        // Your Firebase server key from Firebase console
        $serverKey = 'YOUR_FIREBASE_SERVER_KEY';

        // Firebase API URL
        $url = 'https://fcm.googleapis.com/fcm/send';

        // Prepare the message
        $message = [
            'to' => $token,
            'notification' => $payload['notification'],
            'data' => $payload['data']
        ];

        // Send via cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: key=' . $serverKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $response = json_decode($result, true);
            return $response['success'] === 1;
        }

        return false;
    } catch (Exception $e) {
        error_log("Firebase push error: " . $e->getMessage());
        return false;
    }
}

/**
 * Placeholder function for sending Apple push notifications
 * Replace with your actual implementation
 * 
 * @param string $token The device token
 * @param array $payload The notification payload
 * @return bool True on success, false on failure
 */
function sendApplePush($token, $payload)
{
    // This is a placeholder - implement your actual Apple push logic
    // Apple push requires a more complex setup with certificates
    // For production use, consider a third-party service or proper APNs implementation

    return false; // Placeholder return
}

/**
 * Check and trigger scheduled announcements
 * This function should be called by a cron job
 * 
 * @return array Statistics about processed announcements
 */
function processScheduledAnnouncements()
{
    global $conn;

    $stats = [
        'processed' => 0,
        'published' => 0,
        'errors' => 0
    ];

    try {
        // Begin transaction
        $conn->begin_transaction();

        // Find announcements scheduled for now or earlier
        $now = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("
                             SELECT announcement_id 
                             FROM course_announcements 
                             WHERE status = 'Scheduled' 
                             AND scheduled_at <= ?
                         ");
        $stmt->bind_param("s", $now);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        while ($announcement = $result->fetch_assoc()) {
            $stats['processed']++;

            // Update status to Published
            $updateStmt = $conn->prepare("
                                 UPDATE course_announcements 
                                 SET status = 'Published', 
                                     scheduled_at = NULL, 
                                     updated_at = ? 
                                 WHERE announcement_id = ?
                             ");
            $updateStmt->bind_param("si", $now, $announcement['announcement_id']);
            $success = $updateStmt->execute();
            $updateStmt->close();

            if ($success) {
                // Get recipients and queue delivery
                $recipients = getAnnouncementRecipients($announcement['announcement_id']);
                if (queueAnnouncementDelivery($announcement['announcement_id'], $recipients)) {
                    $stats['published']++;

                    // Update statistics with recipient count
                    $recipientCount = count($recipients);
                    $statsStmt = $conn->prepare("
                                         UPDATE announcement_statistics 
                                         SET total_recipients = ? 
                                         WHERE announcement_id = ?
                                     ");
                    $statsStmt->bind_param("ii", $recipientCount, $announcement['announcement_id']);
                    $statsStmt->execute();
                    $statsStmt->close();
                } else {
                    $stats['errors']++;
                }
            } else {
                $stats['errors']++;
            }
        }

        // Commit transaction
        $conn->commit();

        return $stats;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error processing scheduled announcements: " . $e->getMessage());
        return ['error' => $e->getMessage()];
    }
}

/**
 * Process recurring announcements
 * This function should be called by a daily cron job
 * 
 * @return array Statistics about processed recurring announcements
 */
function processRecurringAnnouncements()
{
    global $conn;

    $stats = [
        'processed' => 0,
        'created' => 0,
        'errors' => 0
    ];

    try {
        // Begin transaction
        $conn->begin_transaction();

        $today = date('Y-m-d');
        $now = date('Y-m-d H:i:s');
        $dayOfWeek = date('w'); // 0 (Sunday) through 6 (Saturday)
        $dayOfMonth = date('j'); // Day of the month without leading zeros

        // Get active recurring announcements
        $stmt = $conn->prepare("
                             SELECT r.*, 
                                    a.course_id, a.is_system_wide, a.target_roles, a.title, 
                                    a.content, a.importance, a.is_pinned, a.created_by
                             FROM recurring_announcements r
                             JOIN course_announcements a ON r.announcement_id = a.announcement_id
                             WHERE r.is_active = 1
                             AND (r.end_date IS NULL OR r.end_date >= ?)
                             AND r.start_date <= ?
                         ");
        $stmt->bind_param("ss", $today, $today);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        while ($recurring = $result->fetch_assoc()) {
            $stats['processed']++;
            $shouldCreate = false;

            // Check if it's time to create a new instance based on frequency
            switch ($recurring['frequency']) {
                case 'Daily':
                    $shouldCreate = true;
                    break;

                case 'Weekly':
                    // Check if today is the specified day of week
                    if ($recurring['day_of_week'] == $dayOfWeek) {
                        $shouldCreate = true;
                    }
                    break;

                case 'Monthly':
                    // Check if today is the specified day of month
                    if ($recurring['day_of_month'] == $dayOfMonth) {
                        $shouldCreate = true;
                    }
                    break;

                case 'Custom':
                    // Custom logic can be implemented here
                    // For now, skip these
                    continue 2; // Skip to next iteration
            }

            // Skip if already created an instance today
            if ($shouldCreate && $recurring['last_triggered']) {
                $lastDate = date('Y-m-d', strtotime($recurring['last_triggered']));
                if ($lastDate === $today) {
                    $shouldCreate = false;
                }
            }

            if ($shouldCreate) {
                // Create a new announcement instance
                $insertStmt = $conn->prepare("
                                     INSERT INTO course_announcements 
                                     (course_id, is_system_wide, target_roles, title, content, 
                                      importance, is_pinned, created_by, status) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Published')
                                 ");
                $insertStmt->bind_param(
                    "iissssii",
                    $recurring['course_id'],
                    $recurring['is_system_wide'],
                    $recurring['target_roles'],
                    $recurring['title'],
                    $recurring['content'],
                    $recurring['importance'],
                    $recurring['is_pinned'],
                    $recurring['created_by']
                );

                if ($insertStmt->execute()) {
                    $newAnnouncementId = $insertStmt->insert_id;
                    $insertStmt->close();

                    // Update last_triggered date
                    $updateStmt = $conn->prepare("
                                         UPDATE recurring_announcements 
                                         SET last_triggered = ? 
                                         WHERE recurring_id = ?
                                     ");
                    $updateStmt->bind_param("si", $now, $recurring['recurring_id']);
                    $updateStmt->execute();
                    $updateStmt->close();

                    // Create initial statistics record
                    $statsStmt = $conn->prepare("
                                         INSERT INTO announcement_statistics 
                                         (announcement_id, total_recipients, delivery_count, read_count) 
                                         VALUES (?, 0, 0, 0)
                                     ");
                    $statsStmt->bind_param("i", $newAnnouncementId);
                    $statsStmt->execute();
                    $statsStmt->close();

                    // Queue for delivery
                    $recipients = getAnnouncementRecipients($newAnnouncementId);
                    if (queueAnnouncementDelivery($newAnnouncementId, $recipients)) {
                        $stats['created']++;

                        // Update statistics with recipient count
                        $recipientCount = count($recipients);
                        $updateStatsStmt = $conn->prepare("
                                             UPDATE announcement_statistics 
                                             SET total_recipients = ? 
                                             WHERE announcement_id = ?
                                         ");
                        $updateStatsStmt->bind_param("ii", $recipientCount, $newAnnouncementId);
                        $updateStatsStmt->execute();
                        $updateStatsStmt->close();
                    } else {
                        $stats['errors']++;
                    }
                } else {
                    $insertStmt->close();
                    $stats['errors']++;
                }
            }
        }

        // Commit transaction
        $conn->commit();

        return $stats;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error processing recurring announcements: " . $e->getMessage());
        return ['error' => $e->getMessage()];
    }
}
