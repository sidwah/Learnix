
<?php
require '../../backend/session_start.php';
require_once '../../backend/config.php';

// Check if the user is an instructor
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$instructor_id = $_SESSION['instructor_id'];
$course_id = isset($_POST['course_id']) ? $_POST['course_id'] : 'all';
$time_frame = isset($_POST['time_frame']) ? $_POST['time_frame'] : 'monthly';

// Validate inputs
$valid_time_frames = ['weekly', 'monthly', 'quarterly', 'yearly'];
if (!in_array($time_frame, $valid_time_frames)) {
    $time_frame = 'monthly';
}

if ($course_id !== 'all') {
    // Verify the course belongs to the instructor
    $sql = "SELECT COUNT(*) as count 
            FROM courses c 
            JOIN course_instructors ci ON c.course_id = ci.course_id 
            WHERE ci.instructor_id = ? AND c.course_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $instructor_id, $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid course']);
        $stmt->close();
        exit;
    }
    $stmt->close();
}

// Build redirect query
$redirect_query = "?time_frame=$time_frame";
if ($course_id !== 'all') {
    $redirect_query .= "&course_id=$course_id";
}

echo json_encode([
    'success' => true,
    'redirect_query' => $redirect_query
]);

$conn->close();
?>
