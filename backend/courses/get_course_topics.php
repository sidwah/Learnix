<?php
// get_course_topics.php - Fetches topics for a course

// Start session and include config
require '../session_start.php';
require '../config.php';

// Set response header
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'topics' => []
];

// Function to sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Session expired. Please login again.';
    echo json_encode($response);
    exit;
}

// Check if request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

// Validate course ID
if (!isset($_GET['course_id']) || empty($_GET['course_id'])) {
    $response['message'] = 'Course ID is required.';
    echo json_encode($response);
    exit;
}

$courseId = intval($_GET['course_id']);

// Get instructor ID for the logged-in user
$userId = $_SESSION['user_id'];
$stmtInstructor = $conn->prepare("SELECT instructor_id FROM instructors WHERE user_id = ?");
$stmtInstructor->bind_param("i", $userId);
$stmtInstructor->execute();
$stmtInstructor->bind_result($instructorId);
$stmtInstructor->fetch();
$stmtInstructor->close();

if (!$instructorId) {
    $response['message'] = 'Instructor not found. Please make sure your account has instructor privileges.';
    echo json_encode($response);
    exit;
}

// Verify course belongs to this instructor
$stmtVerifyCourse = $conn->prepare("SELECT course_id FROM courses WHERE course_id = ? AND instructor_id = ?");
$stmtVerifyCourse->bind_param("ii", $courseId, $instructorId);
$stmtVerifyCourse->execute();
$stmtVerifyCourse->store_result();

if ($stmtVerifyCourse->num_rows === 0) {
    $response['message'] = 'You do not have permission to access this course.';
    echo json_encode($response);
    exit;
}
$stmtVerifyCourse->close();

try {
    // Query to get all topics for this course
    $query = "
        SELECT 
            t.topic_id,
            t.section_id,
            t.title,
            t.position,
            s.title AS section_title,
            c.content_type
        FROM 
            section_topics t
        JOIN 
            course_sections s ON t.section_id = s.section_id
        LEFT JOIN 
            topic_content c ON t.topic_id = c.topic_id
        WHERE 
            s.course_id = ?
        ORDER BY 
            s.position, t.position
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch all topics
    $topics = [];
    while ($row = $result->fetch_assoc()) {
        $topics[] = [
            'topic_id' => $row['topic_id'],
            'section_id' => $row['section_id'],
            'title' => $row['title'],
            'position' => $row['position'],
            'section_title' => $row['section_title'],
            'content_type' => $row['content_type']
        ];
    }
    
    $stmt->close();
    
    // Return the topics
    $response['success'] = true;
    $response['topics'] = $topics;
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    
    // Add detailed error info for debugging
    if (isset($_GET['debug']) && $_GET['debug'] === '1') {
        $response['debug'] = [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ];
    }
} finally {
    // Close connection
    $conn->close();
}

// Send JSON response
echo json_encode($response);
exit;