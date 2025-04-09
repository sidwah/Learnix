<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page with return URL
    header("Location: ../../index.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Check if course_id is provided
if (!isset($_GET['course_id']) || !is_numeric($_GET['course_id'])) {
    // Redirect to courses page if no course ID
    header("Location: ../../student/courses.php");
    exit();
}

$course_id = intval($_GET['course_id']);
$user_id = $_SESSION['user_id'];

// Connect to database
require_once '../config.php';

// First, check if the course exists and is published
$sql = "SELECT * FROM courses WHERE course_id = ? AND status = 'Published'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Course doesn't exist or isn't published
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
    $_SESSION['info_message'] = "You are already enrolled in this course.";
    header("Location: ../../student/course-materials.php?course_id=" . $course_id);
    exit();
}

// Process enrollment
if ($course['price'] == 0) {
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
        while ($topic = $topics_result->fetch_assoc()) {
            $sql = "INSERT INTO progress (enrollment_id, topic_id, completion_status) 
                    VALUES (?, ?, 'Not Started')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $enrollment_id, $topic['topic_id']);
            $stmt->execute();
        }
        
        // Set success message and redirect
        $_SESSION['success_message'] = "You have successfully enrolled in this course!";
        header("Location: ../../student/course-materials.php?course_id=" . $course_id);
        exit();
    } else {
        // Error during enrollment
        $_SESSION['error_message'] = "There was an error enrolling in this course. Please try again.";
        header("Location: ../../student/course-overview.php?id=" . $course_id);
        exit();
    }
} else {
    // Paid course, redirect to checkout
    header("Location: ../../student/checkout.php?course_id=" . $course_id);
    exit();
}

// Close connection
$stmt->close();
$conn->close();
?>