<?php
// backend/student/process-momo-payment.php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once '../config.php';
require_once '../paystack-config.php';
require_once '../PHPMailer/src/Exception.php';
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "You must be logged in to complete a purchase.";
    header("Location: ../../student/");
    exit();
}

// Check if required fields are provided
if (!isset($_POST['course_id']) || !isset($_POST['transaction_id']) || !isset($_POST['phone']) || !isset($_POST['provider'])) {
    $_SESSION['error_message'] = "Missing required information.";
    header("Location: ../../student/courses.php");
    exit();
}

$course_id = intval($_POST['course_id']);
$user_id = $_SESSION['user_id'];
$transaction_id = $_POST['transaction_id'];
$phone = $_POST['phone'];
$provider = $_POST['provider'];

// Fetch course details
$sql = "SELECT c.*, u.first_name AS instructor_first_name, 
               u.last_name AS instructor_last_name, 
               u.email AS instructor_email,
               i.instructor_id,
               s.email AS student_email, 
               s.first_name AS student_first_name,
               s.last_name AS student_last_name
        FROM courses c
        JOIN course_instructors ci ON ci.course_id = c.course_id AND ci.is_primary = 1
        JOIN instructors i ON ci.instructor_id = i.instructor_id
        JOIN users u ON i.user_id = u.user_id
        JOIN users s ON s.user_id = ?
        WHERE c.course_id = ? AND c.status = 'Published'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Course not found or not available.";
    header("Location: ../../student/courses.php");
    exit();
}

$course = $result->fetch_assoc();

// Check if already enrolled
$sql = "SELECT * FROM enrollments WHERE user_id = ? AND course_id = ? AND status = 'Active'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$enrollment_result = $stmt->get_result();

if ($enrollment_result->num_rows > 0) {
    $_SESSION['info_message'] = "You are already enrolled in this course.";
    header("Location: ../../student/course-materials.php?course_id=" . $course_id);
    exit();
}


// Start a transaction
$conn->begin_transaction();

try {
    // Create enrollment record
    $sql = "INSERT INTO enrollments (user_id, course_id, enrolled_at, status) 
            VALUES (?, ?, NOW(), 'Active')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $course_id);
    $stmt->execute();
    $enrollment_id = $stmt->insert_id;

    // Record payment
    $sql = "INSERT INTO course_payments (enrollment_id, amount, currency, payment_date, payment_method, transaction_id, status) 
            VALUES (?, ?, 'GHS', NOW(), CONCAT(?, ' Mobile Money'), ?, 'Completed')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idss", $enrollment_id, $course['price'], $provider, $transaction_id);
    $stmt->execute();
    $payment_id = $stmt->insert_id;

    // Initialize progress records
    $sql = "SELECT st.topic_id 
            FROM section_topics st 
            JOIN course_sections cs ON st.section_id = cs.section_id 
            WHERE cs.course_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $topics_result = $stmt->get_result();

    while ($topic = $topics_result->fetch_assoc()) {
        $sql = "INSERT INTO progress (enrollment_id, topic_id, completion_status) 
                VALUES (?, ?, 'Not Started')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $enrollment_id, $topic['topic_id']);
        $stmt->execute();
    }

    // Fetch the latest approved instructor share from course_financial_history
    $sql = "SELECT instructor_share 
        FROM course_financial_history 
        WHERE course_id = ? 
        AND instructor_share > 0 
        ORDER BY change_date DESC 
        LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $instructor_share_percentage = $row['instructor_share']; // e.g., 70.00 for 70%
    } else {
        // Fallback to default split from revenue_settings if no course-specific share exists
        $sql = "SELECT instructor_share FROM revenue_settings ORDER BY updated_at DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $instructor_share_percentage = $row['instructor_share'] ?? 80.00; // Default to 80% if not set
    }
    $stmt->close();

    // Calculate instructor earnings based on the fetched share
    $instructor_share_percentage = $instructor_share_percentage / 100; // Convert to decimal (e.g., 0.70)
    $instructor_share = $course['price'] * $instructor_share_percentage;
    $platform_fee = $course['price'] - $instructor_share;

    // Record instructor earnings
    $sql = "INSERT INTO instructor_earnings 
        (instructor_id, course_id, payment_id, amount, instructor_share, platform_fee, status, created_at, available_at) 
        VALUES (?, ?, ?, ?, ?, ?, 'Pending', NOW(), DATE_ADD(NOW(), INTERVAL 15 DAY))";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiddd", $course['instructor_id'], $course_id, $payment_id, $course['price'], $instructor_share, $platform_fee);
    $stmt->execute();

    // Update course analytics
    $sql = "INSERT INTO course_analytics (course_id, total_students, active_students, revenue_total, revenue_month, views_total, last_updated)
VALUES (?, 1, 1, ?, ?, 1, NOW())
ON DUPLICATE KEY UPDATE 
total_students = total_students + 1,
active_students = active_students + 1,
revenue_total = revenue_total + ?,
revenue_month = revenue_month + ?,
last_updated = NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idddd", $course_id, $course['price'], $course['price'], $course['price'], $course['price']);
    $stmt->execute();

    // Add user notification for successful enrollment
    $notification_title = "Mobile Money Payment Successful";
    $notification_message = "Your mobile money payment of ₵" . number_format($course['price'], 2) . " for " . $course['title'] . " was successful. You now have full access to the course.";
    $sql = "INSERT INTO user_notifications (user_id, type, title, message, related_id, related_type, is_read, created_at)
VALUES (?, 'payment', ?, ?, ?, 'course', 0, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issi", $user_id, $notification_title, $notification_message, $course_id);
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    // Send enrollment confirmation email
    sendEnrollmentEmail($course, $provider);

    // Success, redirect to success page
    $_SESSION['success_message'] = "Mobile Money payment successful! You are now enrolled in the course.";
    header("Location: ../../student/payment-success.php?course_id=" . $course_id);
    exit();
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();

    $_SESSION['error_message'] = "Enrollment failed after payment. Please contact support.";
    // Log the error
    error_log("Enrollment error: " . $e->getMessage());
    header("Location: ../../student/checkout.php?course_id=" . $course_id);
    exit();
}

/**
 * Send enrollment confirmation email using PHPMailer
 * 
 * @param array $course Course details
 * @param string $provider Mobile money provider
 * @return bool Success status
 */
function sendEnrollmentEmail($course, $provider)
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

        // Format provider name for display
        $providerName = ucfirst($provider) . " Mobile Money";

        // Same email template as in process-payment.php but with MoMo details
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
                    background: linear-gradient(135deg, #f26522 0%, #ff8b59 100%);
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
                    color: #f26522;
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
                    background-color: #f26522;
                    color: #ffffff;
                    text-decoration: none;
                    padding: 12px 30px;
                    border-radius: 4px;
                    font-weight: 600;
                    margin: 20px 0;
                }
                
                .receipt-box {
                    background-color: #f5f7fa;
                    border-radius: 6px;
                    padding: 20px;
                    margin: 20px 0;
                }
                
                .receipt-box h3 {
                    margin-top: 0;
                    color: #333;
                    font-size: 18px;
                }
                
                .receipt-item {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 10px;
                }
                
                .receipt-total {
                    display: flex;
                    justify-content: space-between;
                    border-top: 1px solid #ddd;
                    margin-top: 10px;
                    padding-top: 10px;
                    font-weight: bold;
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
                    color: #f26522;
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
                    <h2>Thank You for Your Purchase!</h2>
                    
                    <p>Dear ' . $course['student_first_name'] . ' ' . $course['student_last_name'] . ',</p>
                    
                    <p>Your mobile money payment has been processed successfully and you are now enrolled in <strong>' . $course['title'] . '</strong>. Get ready to start an amazing learning journey!</p>
                    
                    <div class="receipt-box">
                        <h3>Payment Receipt</h3>
                        <div class="receipt-item">
                            <span>Course:</span>
                            <span>' . $course['title'] . '</span>
                        </div>
                        <div class="receipt-item">
                            <span>Date:</span>
                            <span>' . date('F j, Y') . '</span>
                        </div>
                        <div class="receipt-item">
                            <span>Payment Method:</span>
                            <span>' . $providerName . '</span>
                        </div>
                        <div class="receipt-item">
                            <span>Instructor:</span>
                            <span>' . $course['instructor_first_name'] . ' ' . $course['instructor_last_name'] . '</span>
                        </div>
                        <div class="receipt-total">
                            <span>Total:</span>
                            <span>₵' . number_format($course['price'], 2) . '</span>
                        </div>
                    </div>
                    
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
                    <p>If you didn\'t make this purchase, please contact us immediately at <a href="mailto:support@learnix.com">support@learnix.com</a>.</p>
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
        $mail->AltBody = "Thank You for Your Purchase!\n\n" .
            "Dear " . $course['student_first_name'] . " " . $course['student_last_name'] . ",\n\n" .
            "Your mobile money payment has been processed successfully and you are now enrolled in " . $course['title'] . ".\n\n" .
            "Payment Receipt:\n" .
            "- Course: " . $course['title'] . "\n" .
            "- Date: " . date('F j, Y') . "\n" .
            "- Payment Method: " . $providerName . "\n" .
            "- Instructor: " . $course['instructor_first_name'] . " " . $course['instructor_last_name'] . "\n" .
            "- Total: ₵" . number_format($course['price'], 2) . "\n\n" .
            "To start learning now, visit: localhost:8888/Learnix/student/course-materials.php?course_id=" . $course['course_id'] . "\n\n" .
            "Happy learning!\n\n" .
            "Best regards,\n" .
            "The Learnix Team";

        return $mail->send();
    } catch (Exception $e) {
        error_log("Mail Error: " . $e->getMessage());
        return false;
    }
}
