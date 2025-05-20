<?php
// Authentication check
require_once '../../backend/auth/admin/admin-auth-check.php';
require_once '../../backend/config.php';

// Ensure we're processing a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Initialize response array
$response = [
    'status' => 'error',
    'message' => 'Failed to delete course'
];

// Get and validate input
$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;

// Validate inputs
if ($course_id <= 0) {
    $response['message'] = 'Invalid course ID';
    echo json_encode($response);
    exit;
}

// Helper function to check if a column exists in a table
function columnExists($conn, $table, $column) {
    $query = "SHOW COLUMNS FROM `$table` LIKE '$column'";
    $result = $conn->query($query);
    return $result && $result->num_rows > 0;
}

// Process the request
try {
    // Get course details
    $query = "SELECT c.course_id, c.title, c.department_id,
                     GROUP_CONCAT(DISTINCT ci.instructor_id) as instructor_ids
              FROM courses c
              LEFT JOIN course_instructors ci ON c.course_id = ci.course_id AND ci.deleted_at IS NULL
              WHERE c.course_id = ? AND c.deleted_at IS NULL
              GROUP BY c.course_id";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Course not found');
    }
    
    $course = $result->fetch_assoc();
    $instructor_ids = $course['instructor_ids'] ? explode(',', $course['instructor_ids']) : [];
    
    // Start transaction
    $conn->begin_transaction();
    
    // Set deleted_at timestamp for course
    $now = date('Y-m-d H:i:s');
    
    // Soft delete course record
    $update_course = "UPDATE courses SET deleted_at = ? WHERE course_id = ?";
    $stmt = $conn->prepare($update_course);
    $stmt->bind_param("si", $now, $course_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to delete course record: ' . $stmt->error);
    }
    
    // Handle course_sections (check if deleted_at exists)
    if (columnExists($conn, 'course_sections', 'deleted_at')) {
        $update_sections = "UPDATE course_sections SET deleted_at = ? WHERE course_id = ?";
        $stmt = $conn->prepare($update_sections);
        $stmt->bind_param("si", $now, $course_id);
        $stmt->execute();
    }
    
    // Get section IDs for cascading deletes
    $section_query = "SELECT section_id FROM course_sections WHERE course_id = ?";
    $stmt = $conn->prepare($section_query);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $section_result = $stmt->get_result();
    $section_ids = [];
    
    while ($row = $section_result->fetch_assoc()) {
        $section_ids[] = $row['section_id'];
    }
    
    // If there are sections, soft delete related records
    if (!empty($section_ids)) {
        $section_id_list = implode(',', $section_ids);
        
        // Handle section_topics (check if deleted_at exists)
        if (columnExists($conn, 'section_topics', 'deleted_at')) {
            $update_topics = "UPDATE section_topics SET deleted_at = ? WHERE section_id IN ($section_id_list)";
            $stmt = $conn->prepare($update_topics);
            $stmt->bind_param("s", $now);
            $stmt->execute();
        }
        
        // Get topic IDs for cascading deletes
        $topic_query = "SELECT topic_id FROM section_topics WHERE section_id IN ($section_id_list)";
        $topic_result = $conn->query($topic_query);
        $topic_ids = [];
        
        if ($topic_result && $topic_result->num_rows > 0) {
            while ($row = $topic_result->fetch_assoc()) {
                $topic_ids[] = $row['topic_id'];
            }
        }
        
        // If there are topics, soft delete related content
        if (!empty($topic_ids)) {
            $topic_id_list = implode(',', $topic_ids);
            
            // Handle topic_content (check if deleted_at exists)
            if (columnExists($conn, 'topic_content', 'deleted_at')) {
                $update_content = "UPDATE topic_content SET deleted_at = ? WHERE topic_id IN ($topic_id_list)";
                $stmt = $conn->prepare($update_content);
                $stmt->bind_param("s", $now);
                $stmt->execute();
            }
            
            // Handle section_quizzes (check if deleted_at exists)
            if (columnExists($conn, 'section_quizzes', 'deleted_at')) {
                $update_quizzes = "UPDATE section_quizzes SET deleted_at = ? WHERE topic_id IN ($topic_id_list)";
                $stmt = $conn->prepare($update_quizzes);
                $stmt->bind_param("s", $now);
                $stmt->execute();
            }
        }
    }
    
    // Handle course_instructors (check if deleted_at exists)
    if (columnExists($conn, 'course_instructors', 'deleted_at')) {
        $update_instructors = "UPDATE course_instructors SET deleted_at = ? WHERE course_id = ?";
        $stmt = $conn->prepare($update_instructors);
        $stmt->bind_param("si", $now, $course_id);
        $stmt->execute();
    }
    
    // Handle course_requirements (check if deleted_at exists)
    if (columnExists($conn, 'course_requirements', 'deleted_at')) {
        $update_requirements = "UPDATE course_requirements SET deleted_at = ? WHERE course_id = ?";
        $stmt = $conn->prepare($update_requirements);
        $stmt->bind_param("si", $now, $course_id);
        $stmt->execute();
    }
    
    // Handle course_learning_outcomes (check if deleted_at exists)
    if (columnExists($conn, 'course_learning_outcomes', 'deleted_at')) {
        $update_outcomes = "UPDATE course_learning_outcomes SET deleted_at = ? WHERE course_id = ?";
        $stmt = $conn->prepare($update_outcomes);
        $stmt->bind_param("si", $now, $course_id);
        $stmt->execute();
    }
    
    // Handle course_detailed_descriptions (check if deleted_at exists)
    if (columnExists($conn, 'course_detailed_descriptions', 'deleted_at')) {
        $update_descriptions = "UPDATE course_detailed_descriptions SET deleted_at = ? WHERE course_id = ?";
        $stmt = $conn->prepare($update_descriptions);
        $stmt->bind_param("si", $now, $course_id);
        $stmt->execute();
    }
    
    // Handle enrollments (check if deleted_at exists)
    if (columnExists($conn, 'enrollments', 'deleted_at')) {
        $update_enrollments = "UPDATE enrollments SET deleted_at = ? WHERE course_id = ?";
        $stmt = $conn->prepare($update_enrollments);
        $stmt->bind_param("si", $now, $course_id);
        $stmt->execute();
    }
    
    // Log the activity
    $admin_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Fallback to ID 1 if not set
    
    $log_details = json_encode([
        'course_id' => $course_id,
        'course_title' => $course['title'],
        'department_id' => $course['department_id']
    ]);
    
    $log_query = "INSERT INTO user_activity_logs (user_id, activity_type, activity_details, ip_address) 
                  VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($log_query);
    $activity_type = "course_deleted";
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt->bind_param("isss", $admin_id, $activity_type, $log_details, $ip);
    $stmt->execute();
    
    // Notify instructors about the course deletion
    if (!empty($instructor_ids)) {
        $instructor_ids_str = implode(',', $instructor_ids);
        
        $instructor_query = "SELECT u.user_id, u.email, u.first_name, u.last_name
                             FROM users u
                             JOIN instructors i ON u.user_id = i.user_id
                             WHERE i.instructor_id IN ($instructor_ids_str)
                             AND u.deleted_at IS NULL";
        
        $instructor_result = $conn->query($instructor_query);
        
        if ($instructor_result && $instructor_result->num_rows > 0) {
            while ($instructor = $instructor_result->fetch_assoc()) {
                // Create in-app notification for each instructor
                $notification_title = "Course Deleted";
                $notification_message = "Your course \"{$course['title']}\" has been deleted by an administrator.";
                
                $notification_query = "INSERT INTO user_notifications (user_id, type, title, message, related_type) 
                                      VALUES (?, 'course_deleted', ?, ?, 'course')";
                $stmt = $conn->prepare($notification_query);
                $related_type = 'course';
                $stmt->bind_param("iss", $instructor['user_id'], $notification_title, $notification_message);
                $stmt->execute();
                
                // Send email notification
                $to = $instructor['email'];
                $subject = "Learnix - Course Deleted";
                
                // HTML email message
                $message = '
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Course Deleted</title>
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
                        
                        .email-header h1 {
                            color: #ffffff;
                            margin: 0;
                            font-size: 24px;
                            font-weight: 600;
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
                        
                        h2 {
                            color: #dc3545;
                            margin-top: 0;
                            font-size: 20px;
                            font-weight: 500;
                        }
                        
                        p {
                            margin: 16px 0;
                            font-size: 15px;
                        }
                        
                        .course-title {
                            font-weight: 600;
                            color: #333;
                        }
                        
                        .alert-box {
                            background-color: #fdf7f7;
                            border-left: 4px solid #dc3545;
                            padding: 15px;
                            margin-top: 20px;
                            border-radius: 0 4px 4px 0;
                        }
                    </style>
                </head>
                <body>
                    <div class="email-container">
                        <div class="email-header">
                            <h1>Learnix</h1>
                        </div>
                        
                        <div class="email-body">
                            <h2>Course Deleted</h2>
                            
                            <p>Hello ' . htmlspecialchars($instructor['first_name']) . ',</p>
                            
                            <p>This is to inform you that your course <span class="course-title">"' . htmlspecialchars($course['title']) . '"</span> has been deleted by an administrator.</p>
                            
                            <div class="alert-box">
                                <p style="margin-top: 0;">If you believe this was done in error or have questions, please contact the system administrator.</p>
                            </div>
                        </div>
                        
                        <div class="email-footer">
                            <p>© 2025 Learnix. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
                ';
                
                // Set email headers
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= "From: Learnix <no-reply@learnix.com>" . "\r\n";
                
                // Send email
                mail($to, $subject, $message, $headers);
            }
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    // Set success response
    $response = [
        'status' => 'success',
        'message' => 'Course deleted successfully'
    ];
    
} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();
    $response['message'] = $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;