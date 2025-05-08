<?php
// ajax/admin/fetch_admin_notifications.php
require_once '../../backend/session_start.php';
require_once '../../backend/config.php';

// Ensure only admins can access this endpoint
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Initialize response array
$response = [
    'success' => false,
    'notifications' => [
        'reports' => [],
        'system' => [],
        'messages' => [],
        'other' => []
    ],
    'counts' => [
        'reports' => 0,
        'system' => 0,
        'messages' => 0,
        'other' => 0,
        'total' => 0
    ],
    'shouldGroup' => [
        'reports' => false,
        'system' => false,
        'messages' => false,
        'other' => false
    ]
];

$admin_id = $_SESSION['user_id'];

try {
    $conn = new mysqli('localhost', 'root', 'root', 'learnix_db');
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // 1. Fetch user notifications for admin
    $notificationQuery = "SELECT 
                            notification_id, 
                            type, 
                            title, 
                            message, 
                            related_id, 
                            related_type, 
                            is_read, 
                            created_at 
                          FROM user_notifications 
                          WHERE user_id = ? 
                          AND is_deleted = 0
                          ORDER BY created_at DESC 
                          LIMIT 50";
    
    $stmt = $conn->prepare($notificationQuery);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $category = 'other';
        
        // Determine category based on notification type
        if (strpos($row['type'], 'report') !== false) {
            $category = 'reports';
        } elseif (strpos($row['type'], 'system') !== false) {
            $category = 'system';
        } elseif (strpos($row['type'], 'message') !== false) {
            $category = 'messages';
        }
        
        // Format created_at as a relative time
        $time = new DateTime($row['created_at']);
        $now = new DateTime();
        $interval = $now->diff($time);
        
        if ($interval->days > 0) {
            $timeAgo = $interval->days . ' day' . ($interval->days > 1 ? 's' : '') . ' ago';
        } elseif ($interval->h > 0) {
            $timeAgo = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
        } elseif ($interval->i > 0) {
            $timeAgo = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
        } else {
            $timeAgo = 'Just now';
        }
        
        // Add to response
        $response['notifications'][$category][] = [
            'id' => 'notification-' . $row['notification_id'],
            'title' => $row['title'],
            'message' => $row['message'],
            'category' => $category,
            'time' => $timeAgo,
            'is_read' => (bool)$row['is_read'],
            'important' => $category === 'reports',
            'related_id' => $row['related_id'],
            'related_type' => $row['related_type'],
            'actions' => getActionsForNotification($row['type'], $row['related_id'], $row['related_type'])
        ];
        
        if (!$row['is_read']) {
            $response['counts'][$category]++;
            $response['counts']['total']++;
        }
    }
    
    // Check if we need to group notifications (more than 7 in a category)
    foreach ($response['notifications'] as $category => $notifications) {
        $response['shouldGroup'][$category] = count($notifications) > 7;
    }
    
    // 2. Fetch pending issue reports not yet in notifications
    $pendingReportsQuery = "SELECT 
                              ir.id, 
                              ir.issue_type, 
                              ir.description, 
                              ir.status, 
                              ir.created_at,
                              u.first_name,
                              u.last_name,
                              u.email
                            FROM issue_reports ir
                            JOIN users u ON ir.user_id = u.user_id
                            WHERE ir.status = 'Pending'
                            AND NOT EXISTS (
                              SELECT 1 FROM user_notifications un 
                              WHERE un.related_id = ir.id 
                              AND un.related_type = 'issue_report'
                              AND un.user_id = ?
                              AND un.is_deleted = 0
                            )";
    
    $stmt = $conn->prepare($pendingReportsQuery);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Add these as new notifications and also create notifications in the database
    $insertNotificationStmt = $conn->prepare("INSERT INTO user_notifications 
                                            (user_id, type, title, message, related_id, related_type, is_deleted) 
                                            VALUES (?, ?, ?, ?, ?, ?, 0)");
    
    while ($row = $result->fetch_assoc()) {
        // Create title based on issue type
        $issueTypeTitle = ucfirst($row['issue_type']);
        $title = "New {$issueTypeTitle} Issue Report";
        
        // Create message with user name
        $userFullName = $row['first_name'] . ' ' . $row['last_name'];
        $message = "User {$userFullName} reported an issue: " . (strlen($row['description']) > 100 ? substr($row['description'], 0, 100) . '...' : $row['description']);
        
        // Insert into notifications
        $type = 'report_' . strtolower($row['issue_type']);
        $related_id = $row['id'];
        $related_type = 'issue_report';
        $insertNotificationStmt->bind_param("isssss", $admin_id, $type, $title, $message, $related_id, $related_type);
        $insertNotificationStmt->execute();
        $notification_id = $conn->insert_id;
        
        // Format created_at as a relative time
        $time = new DateTime($row['created_at']);
        $now = new DateTime();
        $interval = $now->diff($time);
        
        if ($interval->days > 0) {
            $timeAgo = $interval->days . ' day' . ($interval->days > 1 ? 's' : '') . ' ago';
        } elseif ($interval->h > 0) {
            $timeAgo = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
        } elseif ($interval->i > 0) {
            $timeAgo = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
        } else {
            $timeAgo = 'Just now';
        }
        
        // Add to response
        $response['notifications']['reports'][] = [
            'id' => 'notification-' . $notification_id,
            'title' => $title,
            'message' => $message,
            'category' => 'reports',
            'time' => $timeAgo,
            'is_read' => false,
            'important' => true,
            'related_id' => $row['id'],
            'related_type' => 'issue_report',
            'actions' => [
                [
                    'text' => 'View Details',
                    'type' => 'primary',
                    'url' => 'issues.php?filter=' . $row['issue_type']
                ],
                [
                    'text' => 'Mark as Read',
                    'type' => 'secondary',
                    'url' => 'javascript:void(0)',
                    'action' => 'markAsRead'
                ]
            ]
        ];
        
        $response['counts']['reports']++;
        $response['counts']['total']++;
    }
    
    // 3. Check for course submissions needing approval
    $courseSubmissionsQuery = "SELECT 
                                 c.course_id, 
                                 c.title, 
                                 i.instructor_id,
                                 u.first_name,
                                 u.last_name,
                                 c.created_at
                               FROM courses c
                               JOIN instructors i ON c.instructor_id = i.instructor_id
                               JOIN users u ON i.user_id = u.user_id
                               WHERE c.approval_status = 'Pending'
                               AND NOT EXISTS (
                                 SELECT 1 FROM user_notifications un 
                                 WHERE un.related_id = c.course_id 
                                 AND un.related_type = 'course_approval'
                                 AND un.user_id = ?
                                 AND un.is_deleted = 0
                               )";
    
    $stmt = $conn->prepare($courseSubmissionsQuery);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Create notification
        $title = "New Course Submission";
        $message = "Instructor {$row['first_name']} {$row['last_name']} has submitted a new course: \"{$row['title']}\" for review.";
        
        // Insert into notifications
        $type = 'report_course_submission';
        $related_id = $row['course_id'];
        $related_type = 'course_approval';
        $insertNotificationStmt->bind_param("isssss", $admin_id, $type, $title, $message, $related_id, $related_type);
        $insertNotificationStmt->execute();
        $notification_id = $conn->insert_id;
        
        // Format created_at as a relative time
        $time = new DateTime($row['created_at']);
        $now = new DateTime();
        $interval = $now->diff($time);
        
        if ($interval->days > 0) {
            $timeAgo = $interval->days . ' day' . ($interval->days > 1 ? 's' : '') . ' ago';
        } elseif ($interval->h > 0) {
            $timeAgo = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
        } elseif ($interval->i > 0) {
            $timeAgo = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
        } else {
            $timeAgo = 'Just now';
        }
        
        // Add to response
        $response['notifications']['reports'][] = [
            'id' => 'notification-' . $notification_id,
            'title' => $title,
            'message' => $message,
            'category' => 'reports',
            'time' => $timeAgo,
            'is_read' => false,
            'important' => true,
            'related_id' => $row['course_id'],
            'related_type' => 'course_approval',
            'actions' => [
                [
                    'text' => 'Go to Courses',
                    'type' => 'primary',
                    'url' => 'courses.php?status=pending'
                ],
                [
                    'text' => 'Mark as Read',
                    'type' => 'secondary',
                    'url' => 'javascript:void(0)',
                    'action' => 'markAsRead'
                ]
            ]
        ];
        
        $response['counts']['reports']++;
        $response['counts']['total']++;
    }
    
    // 4. Check for instructor verification requests
    $verificationRequestsQuery = "SELECT 
                                    ivr.verification_id, 
                                    ivr.instructor_id,
                                    ivr.submitted_at,
                                    u.first_name,
                                    u.last_name,
                                    u.email
                                  FROM instructor_verification_requests ivr
                                  JOIN instructors i ON ivr.instructor_id = i.instructor_id
                                  JOIN users u ON i.user_id = u.user_id
                                  WHERE ivr.status = 'pending'
                                  AND NOT EXISTS (
                                    SELECT 1 FROM user_notifications un 
                                    WHERE un.related_id = ivr.verification_id 
                                    AND un.related_type = 'instructor_verification'
                                    AND un.user_id = ?
                                    AND un.is_deleted = 0
                                  )";
    
    $stmt = $conn->prepare($verificationRequestsQuery);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Create notification
        $title = "Instructor Verification Request";
        $message = "{$row['first_name']} {$row['last_name']} ({$row['email']}) has requested instructor verification.";
        
        // Insert into notifications
        $type = 'report_instructor_verification';
        $related_id = $row['verification_id'];
        $related_type = 'instructor_verification';
        $insertNotificationStmt->bind_param("isssss", $admin_id, $type, $title, $message, $related_id, $related_type);
        $insertNotificationStmt->execute();
        $notification_id = $conn->insert_id;
        
        // Format created_at as a relative time
        $time = new DateTime($row['submitted_at']);
        $now = new DateTime();
        $interval = $now->diff($time);
        
        if ($interval->days > 0) {
            $timeAgo = $interval->days . ' day' . ($interval->days > 1 ? 's' : '') . ' ago';
        } elseif ($interval->h > 0) {
            $timeAgo = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
        } elseif ($interval->i > 0) {
            $timeAgo = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
        } else {
            $timeAgo = 'Just now';
        }
        
        // Add to response
        $response['notifications']['reports'][] = [
            'id' => 'notification-' . $notification_id,
            'title' => $title,
            'message' => $message,
            'category' => 'reports',
            'time' => $timeAgo,
            'is_read' => false,
            'important' => true,
            'related_id' => $row['verification_id'],
            'related_type' => 'instructor_verification',
            'actions' => [
                [
                    'text' => 'View Instructors',
                    'type' => 'primary',
                    'url' => 'instructors.php?verification=pending'
                ],
                [
                    'text' => 'Mark as Read',
                    'type' => 'secondary',
                    'url' => 'javascript:void(0)',
                    'action' => 'markAsRead'
                ]
            ]
        ];
        
        $response['counts']['reports']++;
        $response['counts']['total']++;
    }
    
    // Check if we need to group notifications after adding new ones
    foreach ($response['notifications'] as $category => $notifications) {
        $response['shouldGroup'][$category] = count($notifications) > 7;
    }
    
    $response['success'] = true;
    
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

// Helper function to generate actions for different notification types
function getActionsForNotification($type, $relatedId, $relatedType) {
    $actions = [];
    
    if (strpos($type, 'report_') === 0) {
        if ($relatedType === 'issue_report') {
            $actions = [
                [
                    'text' => 'View Details',
                    'type' => 'primary',
                    'url' => 'issues.php?filter=' . str_replace('report_', '', $type)
                ],
                [
                    'text' => 'Mark as Read',
                    'type' => 'secondary',
                    'url' => 'javascript:void(0)',
                    'action' => 'markAsRead'
                ]
            ];
        } elseif ($relatedType === 'course_approval') {
            $actions = [
                [
                    'text' => 'Go to Courses',
                    'type' => 'primary',
                    'url' => 'courses.php?status=pending'
                ],
                [
                    'text' => 'Mark as Read',
                    'type' => 'secondary',
                    'url' => 'javascript:void(0)',
                    'action' => 'markAsRead'
                ]
            ];
        } elseif ($relatedType === 'instructor_verification') {
            $actions = [
                [
                    'text' => 'View Instructors',
                    'type' => 'primary',
                    'url' => 'instructors.php?verification=pending'
                ],
                [
                    'text' => 'Mark as Read',
                    'type' => 'secondary',
                    'url' => 'javascript:void(0)',
                    'action' => 'markAsRead'
                ]
            ];
        }
    } elseif (strpos($type, 'system_') === 0) {
        // System notifications actions
        $actions = [
            [
                'text' => 'Mark as Read',
                'type' => 'primary',
                'url' => 'javascript:void(0)',
                'action' => 'markAsRead'
            ]
        ];
    } elseif (strpos($type, 'message_') === 0) {
        // Message notifications actions
        $actions = [
            [
                'text' => 'View Messages',
                'type' => 'primary',
                'url' => 'messages.php'
            ],
            [
                'text' => 'Mark as Read',
                'type' => 'secondary',
                'url' => 'javascript:void(0)',
                'action' => 'markAsRead'
            ]
        ];
    }
    
    return $actions;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);