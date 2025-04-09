<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once '../config.php';
require_once '../stripe-config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "You must be logged in to complete a purchase.";
    header("Location: ../../student/");
    exit();
}

// Check if course_id and stripeToken are provided
if (!isset($_POST['course_id']) || !isset($_POST['stripeToken'])) {
    $_SESSION['error_message'] = "Missing required information.";
    header("Location: ../../student/courses.php");
    exit();
}

$course_id = intval($_POST['course_id']);
$user_id = $_SESSION['user_id'];
$token = $_POST['stripeToken'];
$cardName = $_POST['cardName'];

// Fetch course details
$sql = "SELECT * FROM courses WHERE course_id = ? AND status = 'Published'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
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
            
            // Commit transaction
$conn->commit();

// Success, redirect to success page
$_SESSION['success_message'] = "Payment successful! You are now enrolled in the course.";
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
    } else {
        $_SESSION['error_message'] = "Payment failed. Please try again.";
        header("Location: ../../student/checkout.php?course_id=" . $course_id);
        exit();
    }
    
} catch (\Stripe\Exception\CardException $e) {
    // Card was declined
    $_SESSION['error_message'] = "Your card was declined: " . $e->getError()->message;
    header("Location: ../../student/checkout.php?course_id=" . $course_id);
    exit();
} catch (\Exception $e) {
    // Other Stripe error
    $_SESSION['error_message'] = "Payment error: " . $e->getMessage();
    // Log the error
    error_log("Stripe error: " . $e->getMessage());
    header("Location: ../../student/checkout.php?course_id=" . $course_id);
    exit();
}