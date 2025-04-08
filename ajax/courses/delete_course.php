<?php
// ajax/courses/delete_course.php

require_once '../../backend/session_start.php';
require_once '../../backend/config.php';

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Set headers for JSON response
header('Content-Type: application/json');

// Get the course ID from the request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['course_id']) || empty($data['course_id'])) {
    echo json_encode(['success' => false, 'message' => 'Course ID is required']);
    exit;
}

$course_id = intval($data['course_id']);
$user_id = $_SESSION['user_id'];

try {
    // First, check if the instructor owns the course
    $stmt = $conn->prepare("
        SELECT c.course_id, c.title, c.status, i.instructor_id 
        FROM courses c
        JOIN instructors i ON c.instructor_id = i.instructor_id
        WHERE c.course_id = ? AND i.user_id = ?
    ");
    $stmt->bind_param("ii", $course_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Course not found or you do not have permission to delete it']);
        exit;
    }

    $course = $result->fetch_assoc();

    // Check if the course is in draft status
    if ($course['status'] !== 'Draft') {
        echo json_encode(['success' => false, 'message' => 'Only draft courses can be deleted']);
        exit;
    }

    // Start a transaction to ensure data integrity
    $conn->begin_transaction();

    // Delete course-related data
    // Delete course tags mapping
    $stmt = $conn->prepare("DELETE FROM course_tag_mapping WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();

    // Delete course learning outcomes
    $stmt = $conn->prepare("DELETE FROM course_learning_outcomes WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();

    // Delete course requirements
    $stmt = $conn->prepare("DELETE FROM course_requirements WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();

    // Delete topics and quizzes associated with sections
    $stmt = $conn->prepare("
        SELECT section_id FROM course_sections WHERE course_id = ?
    ");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $sections_result = $stmt->get_result();

    while ($section = $sections_result->fetch_assoc()) {
        $section_id = $section['section_id'];

        // Delete quizzes associated with the section
        $stmt = $conn->prepare("DELETE FROM section_quizzes WHERE section_id = ?");
        $stmt->bind_param("i", $section_id);
        $stmt->execute();

        // Delete topics associated with the section
        $stmt = $conn->prepare("DELETE FROM section_topics WHERE section_id = ?");
        $stmt->bind_param("i", $section_id);
        $stmt->execute();
    }

    // Delete course sections
    $stmt = $conn->prepare("DELETE FROM course_sections WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();

    // Delete course settings if they exist
    $stmt = $conn->prepare("DELETE FROM course_settings WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();

    // Finally, delete the course itself
    $stmt = $conn->prepare("DELETE FROM courses WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();

    // Delete topic content and resources for all topics
    $stmt = $conn->prepare("
DELETE tc FROM topic_content tc
JOIN section_topics st ON tc.topic_id = st.topic_id
JOIN course_sections cs ON st.section_id = cs.section_id
WHERE cs.course_id = ?
");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();

    // Delete topic resources
    $stmt = $conn->prepare("
DELETE tr FROM topic_resources tr
JOIN section_topics st ON tr.topic_id = st.topic_id
JOIN course_sections cs ON st.section_id = cs.section_id
WHERE cs.course_id = ?
");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();

    // Delete course detailed descriptions
    $stmt = $conn->prepare("DELETE FROM course_detailed_descriptions WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();

    // Delete course media
    $stmt = $conn->prepare("DELETE FROM course_media WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();

    // Delete course analytics
    $stmt = $conn->prepare("DELETE FROM course_analytics WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();

    // Delete course announcements
    $stmt = $conn->prepare("DELETE FROM course_announcements WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();

    // Delete enrollments
    $stmt = $conn->prepare("DELETE FROM enrollments WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();

    // Delete course ratings
    $stmt = $conn->prepare("DELETE FROM course_ratings WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();

    // Delete course wishlist entries
    $stmt = $conn->prepare("DELETE FROM course_wishlist WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();

    // Commit the transaction
    $conn->commit();

    // Log the activity
    $activity_details = "Deleted course: " . $course['title'];
    $stmt = $conn->prepare("
        INSERT INTO user_activity_logs (user_id, activity_type, activity_details, ip_address, user_agent) 
        VALUES (?, 'course_delete', ?, ?, ?)
    ");
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $stmt->bind_param("isss", $user_id, $activity_details, $ip_address, $user_agent);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Course deleted successfully']);
} catch (Exception $e) {
    // If an error occurs, roll back the transaction
    if ($conn->connect_errno === 0) {
        $conn->rollback();
    }

    error_log("Error deleting course: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while deleting the course']);
}
