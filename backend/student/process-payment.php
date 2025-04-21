<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once '../config.php';
require_once '../stripe-config.php';
require_once '../PHPMailer/src/Exception.php';
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    if (isset($_POST['modal'])) {
        echo json_encode([
            'success' => false,
            'message' => "You must be logged in to complete a purchase."
        ]);
        exit();
    }

    $_SESSION['error_message'] = "You must be logged in to complete a purchase.";
    header("Location: ../../student/");
    exit();
}

// Check if course_id and stripeToken are provided
if (!isset($_POST['course_id']) || !isset($_POST['stripeToken'])) {
    if (isset($_POST['modal'])) {
        echo json_encode([
            'success' => false,
            'message' => "Missing required information."
        ]);
        exit();
    }

    $_SESSION['error_message'] = "Missing required information.";
    header("Location: ../../student/courses.php");
    exit();
}

$course_id = intval($_POST['course_id']);
$user_id = $_SESSION['user_id'];
$token = $_POST['stripeToken'];
$cardName = $_POST['cardName'];

// Fetch course details
$sql = "SELECT c.*, u.first_name AS instructor_first_name, 
               u.last_name AS instructor_last_name, 
               u.email AS instructor_email,
               i.instructor_id,
               s.email AS student_email, 
               s.first_name AS student_first_name,
               s.last_name AS student_last_name
        FROM courses c
        JOIN instructors i ON c.instructor_id = i.instructor_id
        JOIN users u ON i.user_id = u.user_id
        JOIN users s ON s.user_id = ?
        WHERE c.course_id = ? AND c.status = 'Published'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    if (isset($_POST['modal'])) {
        echo json_encode([
            'success' => false,
            'message' => "Course not found or not available."
        ]);
        exit();
    }

    $_SESSION['error_message'] = "Course not found or not available.";
    header("Location: ../../student/courses.php");
    exit();
}

$course = $result->fetch_assoc();
$amount = $course['price'] * 100; // Convert to cents for Stripe

// Check if already enrolled
$sql = "SELECT * FROM enrollments WHERE user_id = ? AND course_id = ? AND status = 'Active'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$enrollment_result = $stmt->get_result();

if ($enrollment_result->num_rows > 0) {
    if (isset($_POST['modal'])) {
        echo json_encode([
            'success' => true,
            'message' => "You are already enrolled in this course.",
            'redirect' => 'course-materials.php?course_id=' . $course_id
        ]);
        exit();
    }

    $_SESSION['info_message'] = "You are already enrolled in this course.";
    header("Location: ../../student/course-materials.php?course_id=" . $course_id);
    exit();
}

// Process payment with Stripe
try {
    // Retrieve customer information
    $sql = "SELECT first_name, last_name, email FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_result = $stmt->get_result();
    $user = $user_result->fetch_assoc();

    // Create a charge
    $charge = \Stripe\Charge::create([
        'amount' => $amount,
        'currency' => STRIPE_CURRENCY,
        'description' => 'Course: ' . $course['title'],
        'source' => $token,
        'metadata' => [
            'course_id' => $course_id,
            'user_id' => $user_id,
            'email' => $user['email']
        ]
    ]);

    // If charge is successful, create enrollment
    if ($charge->status === 'succeeded') {
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
                    VALUES (?, ?, ?, NOW(), 'Credit Card', ?, 'Completed')";
            $stmt = $conn->prepare($sql);
            $transaction_id = $charge->id;
            $currency = strtoupper(STRIPE_CURRENCY);
            $stmt->bind_param("idss", $enrollment_id, $course['price'], $currency, $transaction_id);
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

            // Calculate instructor earnings
            $platform_fee = $course['price'] * 0.20; // 20% platform fee
            $instructor_share = $course['price'] - $platform_fee;

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
            $notification_title = "Payment Successful";
            $notification_message = "Your payment of ₵" . number_format($course['price'], 2) . " for " . $course['title'] . " was successful. You now have full access to the course.";
            $sql = "INSERT INTO user_notifications (user_id, type, title, message, related_id, related_type, is_read, created_at)
VALUES (?, 'payment', ?, ?, ?, 'course', 0, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issi", $user_id, $notification_title, $notification_message, $course_id);
            $stmt->execute();

            // Commit transaction
            $conn->commit();

            // Send enrollment confirmation email
            sendEnrollmentEmail($course);

            // Success response based on request type
            if (isset($_POST['modal'])) {
                echo json_encode([
                    'success' => true,
                    'message' => "Payment successful! You are now enrolled in the course.",
                    'course_title' => $course['title'],
                    'course_id' => $course_id
                ]);
                exit();
            }

            // Success, redirect to success page
            $_SESSION['success_message'] = "Payment successful! You are now enrolled in the course.";
            header("Location: ../../student/payment-success.php?course_id=" . $course_id);
            exit();
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();

            if (isset($_POST['modal'])) {
                echo json_encode([
                    'success' => false,
                    'message' => "Enrollment failed after payment. Please contact support."
                ]);
                exit();
            }

            $_SESSION['error_message'] = "Enrollment failed after payment. Please contact support.";
            // Log the error
            error_log("Enrollment error: " . $e->getMessage());
            header("Location: ../../student/checkout.php?course_id=" . $course_id);
            exit();
        }
    } else {
        if (isset($_POST['modal'])) {
            echo json_encode([
                'success' => false,
                'message' => "Payment failed. Please try again."
            ]);
            exit();
        }

        $_SESSION['error_message'] = "Payment failed. Please try again.";
        header("Location: ../../student/checkout.php?course_id=" . $course_id);
        exit();
    }
} catch (\Stripe\Exception\CardException $e) {
    // Card was declined
    if (isset($_POST['modal'])) {
        echo json_encode([
            'success' => false,
            'message' => "Your card was declined: " . $e->getError()->message
        ]);
        exit();
    }

    $_SESSION['error_message'] = "Your card was declined: " . $e->getError()->message;
    header("Location: ../../student/checkout.php?course_id=" . $course_id);
    exit();
} catch (\Exception $e) {
    // Other Stripe error
    if (isset($_POST['modal'])) {
        echo json_encode([
            'success' => false,
            'message' => "Payment error: " . $e->getMessage()
        ]);
        exit();
    }

    $_SESSION['error_message'] = "Payment error: " . $e->getMessage();
    // Log the error
    error_log("Stripe error: " . $e->getMessage());
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

        // Email body with payment confirmation details
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
                    <h2>Thank You for Your Purchase!</h2>
                    
                    <p>Dear ' . $course['student_first_name'] . ' ' . $course['student_last_name'] . ',</p>
                    
                    <p>Your payment has been processed successfully and you are now enrolled in <strong>' . $course['title'] . '</strong>. Get ready to start an amazing learning journey!</p>
                    
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
            "Your payment has been processed successfully and you are now enrolled in " . $course['title'] . ".\n\n" .
            "Payment Receipt:\n" .
            "- Course: " . $course['title'] . "\n" .
            "- Date: " . date('F j, Y') . "\n" .
            "- Instructor: " . $course['instructor_first_name'] . " " . $course['instructor_last_name'] . "\n" .
            "- Total: ₵" . number_format($course['price'], 2) . "\n\n" .
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
