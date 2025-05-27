<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files for email
require_once '../config.php';
require_once '../PHPMailer/src/Exception.php';
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // For AJAX requests
    if (isset($_POST['ajax'])) {
        echo json_encode([
            'success' => false,
            'message' => 'You must be logged in to enroll in courses.'
        ]);
        exit();
    }

    // Redirect to login page with return URL
    header("Location: ../../index.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Get course_id from GET or POST
$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : (isset($_GET['course_id']) ? intval($_GET['course_id']) : 0);

// Check if course_id is provided
if (!$course_id) {
    // For AJAX requests
    if (isset($_POST['ajax'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid course information.'
        ]);
        exit();
    }

    // Redirect to courses page if no course ID
    header("Location: ../../student/courses.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// First, check if the course exists and is published
$sql = "SELECT 
    c.course_id,
    c.title,
    c.short_description,
    c.full_description,
    c.status,
    ci.instructor_id,
    u.user_id AS instructor_user_id,
    u.email AS instructor_email,
    u.first_name AS instructor_first_name,
    u.last_name AS instructor_last_name,
    ci.is_primary,
    s.user_id AS student_user_id,
    s.email AS student_email,
    s.first_name AS student_first_name,
    s.last_name AS student_last_name
FROM 
    courses c
    INNER JOIN course_instructors ci ON c.course_id = ci.course_id
    INNER JOIN instructors i ON ci.instructor_id = i.instructor_id
    INNER JOIN users u ON i.user_id = u.user_id
    INNER JOIN users s ON s.user_id = ?
WHERE 
    c.course_id = ?
    AND c.status = 'Published'
    AND c.deleted_at IS NULL
    AND ci.deleted_at IS NULL
    AND i.deleted_at IS NULL;";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Course doesn't exist or isn't published
    if (isset($_POST['ajax'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Course not found or not available.'
        ]);
        exit();
    }

    $_SESSION['error_message'] = "Course not found or not available.";
    header("Location: ../../student/courses.php");
    exit();
}

$course = $result->fetch_assoc();

// Check if user is already enrolled
$sql = "SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // User is already enrolled, redirect to learning page
    if (isset($_POST['ajax'])) {
        echo json_encode([
            'success' => true,
            'message' => 'You are already enrolled in this course.',
            'redirect' => '../../student/course-materials.php?course_id=' . $course_id
        ]);
        exit();
    }

    $_SESSION['info_message'] = "You are already enrolled in this course.";
    header("Location: ../../student/course-materials.php?course_id=" . $course_id);
    exit();
}

// Process enrollment
if ($course['price'] == 0) {
    // Start transaction
    $conn->begin_transaction();

    try {
        // Free course, enroll directly
        $sql = "INSERT INTO enrollments (user_id, course_id, enrolled_at, status)
                VALUES (?, ?, NOW(), 'Active')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $course_id);

        if ($stmt->execute()) {
            // Enrollment successful
            $enrollment_id = $stmt->insert_id;

            // Get all section topics for initial progress records
            $sql = "SELECT st.topic_id
                    FROM section_topics st
                    JOIN course_sections cs ON st.section_id = cs.section_id
                    WHERE cs.course_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $course_id);
            $stmt->execute();
            $topics_result = $stmt->get_result();

            // Create initial progress records
            // Create initial progress records
            while ($topic = $topics_result->fetch_assoc()) {
                $sql = "INSERT INTO progress (enrollment_id, topic_id, completion_status)
            VALUES (?, ?, 'Not Started')";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $enrollment_id, $topic['topic_id']);
                $stmt->execute();
            }

            // Add notification for successful enrollment
            $notification_title = "Course Enrollment";
            $notification_message = "You have successfully enrolled in {$course['title']}.";
            $sql = "INSERT INTO user_notifications (user_id, type, title, message, related_id, related_type, is_read, created_at)
        VALUES (?, 'enrollment', ?, ?, ?, 'course', 0, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issi", $user_id, $notification_title, $notification_message, $course_id);
            $stmt->execute();

            // Update course analytics
            $sql = "INSERT INTO course_analytics (course_id, total_students, active_students, last_updated)
        VALUES (?, 1, 1, NOW())
        ON DUPLICATE KEY UPDATE 
        total_students = total_students + 1,
        active_students = active_students + 1,
        last_updated = NOW()";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $course_id);
            $stmt->execute();

            // Commit the transaction
            $conn->commit();

            // Send enrollment confirmation email
            sendEnrollmentEmail($course);

            // AJAX response
            if (isset($_POST['ajax'])) {
                echo json_encode([
                    'success' => true,
                    'message' => 'You have successfully enrolled in this course!',
                    'redirect' => '../../student/course-materials.php?course_id=' . $course_id
                ]);
                exit();
            }

            // Standard response
            $_SESSION['success_message'] = "You have successfully enrolled in this course!";
            header("Location: ../../student/course-materials.php?course_id=" . $course_id);
            exit();
        } else {
            // Rollback on error
            $conn->rollback();

            // Error during enrollment
            if (isset($_POST['ajax'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'There was an error enrolling in this course. Please try again.'
                ]);
                exit();
            }

            $_SESSION['error_message'] = "There was an error enrolling in this course. Please try again.";
            header("Location: ../../student/course-overview.php?id=" . $course_id);
            exit();
        }
    } catch (Exception $e) {
        // Rollback on exception
        $conn->rollback();

        // Log the error
        error_log("Enrollment error: " . $e->getMessage());

        if (isset($_POST['ajax'])) {
            echo json_encode([
                'success' => false,
                'message' => 'There was an error enrolling in this course. Please try again.'
            ]);
            exit();
        }

        $_SESSION['error_message'] = "There was an error enrolling in this course. Please try again.";
        header("Location: ../../student/course-overview.php?id=" . $course_id);
        exit();
    }
} else {
    // Paid course
    if (isset($_POST['ajax'])) {
        echo json_encode([
            'success' => false,
            'message' => 'This is a paid course. Please complete the checkout process.',
            'redirect' => '../../student/checkout.php?course_id=' . $course_id
        ]);
        exit();
    }

    // Redirect to checkout
    header("Location: ../../student/checkout.php?course_id=" . $course_id);
    exit();
}

/**
 * Send enrollment confirmation email using PHPMailer
 * 
 * @param array $course Course details
 * @return bool Success status
 */
function sendEnrollmentEmail($course)
{
    try {
        $mail = new PHPMailer(true);

        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'barrock.sidwah@st.rmu.edu.gh';
        $mail->Password = 'mtltujmsmmlkkxtv'; // Use a secure method instead
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Sender and recipient details
        $mail->setFrom('no-reply@learnix.com', 'Learnix');
        $mail->addAddress($course['student_email']);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to ' . $course['title'] . ' - Your Enrollment Confirmation';

        // Email body
        $mail->Body = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Enrollment Confirmation</title>
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
                
                .email-header img {
                    max-width: 150px;
                    height: auto;
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
                
                .button {
                    display: inline-block;
                    background-color: #3a66db;
                    color: #ffffff;
                    text-decoration: none;
                    padding: 12px 30px;
                    border-radius: 4px;
                    font-weight: 600;
                    margin: 20px 0;
                }
                
                .features {
                    background-color: #f5f7fa;
                    border-radius: 6px;
                    padding: 20px;
                    margin: 20px 0;
                }
                
                .features ul {
                    margin: 0;
                    padding: 0 0 0 20px;
                }
                
                .features ul li {
                    margin-bottom: 10px;
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
                    <h2>Welcome to Your New Course!</h2>
                    
                    <p>Dear ' . $course['student_first_name'] . ' ' . $course['student_last_name'] . ',</p>
                    
                    <p>Thank you for enrolling in <strong>' . $course['title'] . '</strong>. Your course is now ready for you to begin learning!</p>
                    
                    <p>This course is taught by <strong>' . $course['instructor_first_name'] . ' ' . $course['instructor_last_name'] . '</strong> and includes:</p>
                    
                    <div class="features">
                        <ul>
                            <li>Comprehensive curriculum</li>
                            <li>Practical exercises</li>
                            <li>Downloadable resources</li>
                            <li>24/7 access to all materials</li>
                        </ul>
                    </div>
                    
                    <div style="text-align: center;">
                        <a href="https://yourdomain.com/student/course-materials.php?course_id=' . $course['course_id'] . '" class="button">START LEARNING NOW</a>
                    </div>
                    
                    <p>If you have any questions about the course, please don\'t hesitate to contact our support team at <a href="mailto:support@learnix.com">support@learnix.com</a>.</p>
                    
                    <p>Happy learning!</p>
                    
                    <p style="margin-top: 30px; font-style: italic; color: #7f8c8d;">
                        Best regards,<br>
                        The Learnix Team
                    </p>
                </div>
                
                <div class="email-footer">
                    <p>&copy; ' . date('Y') . ' Learnix. All rights reserved.</p>
                    <p>If you didn\'t enroll in this course, please contact us immediately at <a href="mailto:support@learnix.com">support@learnix.com</a>.</p>
                    <div class="social-icons">
                        <a href="#">Twitter</a> | 
                        <a href="#">Facebook</a> | 
                        <a href="#">Instagram</a>
                    </div>
                </div>
            </div>
        </body>
        </html>';

        // Alternative plain text body
        $mail->AltBody = "Welcome to " . $course['title'] . "!\n\n" .
            "Dear " . $course['student_first_name'] . " " . $course['student_last_name'] . ",\n\n" .
            "Thank you for enrolling in " . $course['title'] . ". Your course is now ready for you to begin learning.\n\n" .
            "This course is taught by " . $course['instructor_first_name'] . " " . $course['instructor_last_name'] . ".\n\n" .
            "To start learning now, visit: https://yourdomain.com/student/course-materials.php?course_id=" . $course['course_id'] . "\n\n" .
            "Happy learning!\n\n" .
            "Best regards,\n" .
            "The Learnix Team";

        return $mail->send();
    } catch (Exception $e) {
        error_log("Mail Error: " . $e->getMessage());
        return false;
    }
}

// Close connection
// Ensure the connection is closed properly
if (isset($stmt)) {
    $stmt->close();
}
if (isset($conn)) {
    $conn->close();
}
