<?php
//ajax/courses/save_outcomes_requirements.php
// Set proper headers first
header('Content-Type: application/json');

require '../../backend/session_start.php';
require '../../backend/config.php';

// Initialize response array
$response = ['success' => false, 'message' => ''];

try {
    // 1. Authentication and Authorization Check
    if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
        throw new Exception('Unauthorized access', 403);
    }

    // 2. Request Method Validation
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method', 405);
    }

    // 3. Content-Type Check (handle both form-data and JSON)
    $contentType = isset($_SERVER['CONTENT_TYPE']) ? trim($_SERVER['CONTENT_TYPE']) : '';
    if (strpos($contentType, 'application/json') !== false) {
        $json = file_get_contents('php://input');
        $_POST = json_decode($json, true);
    }

    // 4. Required Parameters Validation
    $requiredParams = ['course_id', 'outcomes', 'requirements'];
    foreach ($requiredParams as $param) {
        if (!isset($_POST[$param])) {
            throw new Exception("Missing required parameter: $param", 400);
        }
    }

    // 5. Data Type Validation
    $course_id = filter_var($_POST['course_id'], FILTER_VALIDATE_INT);
    if ($course_id === false || $course_id <= 0) {
        throw new Exception('Invalid course ID', 400);
    }

    if (!is_array($_POST['outcomes']) || !is_array($_POST['requirements'])) {
        throw new Exception('Outcomes and requirements must be arrays', 400);
    }

    // 6. Course Ownership Verification
    $stmt = $conn->prepare("SELECT instructor_id FROM courses WHERE course_id = ?");
    if (!$stmt) {
        throw new Exception('Database preparation failed', 500);
    }
    
    $stmt->bind_param("i", $course_id);
    if (!$stmt->execute()) {
        throw new Exception('Database query failed', 500);
    }
    
    $result = $stmt->get_result();
    $course = $result->fetch_assoc();
    $stmt->close();

    if (!$course) {
        throw new Exception('Course not found', 404);
    }
    if ($course['instructor_id'] != $_SESSION['instructor_id']) {
        throw new Exception('Not authorized for this course', 403);
    }

    // 7. Database Transaction
    $conn->begin_transaction();

    try {
        // Delete existing outcomes
        $stmt = $conn->prepare("DELETE FROM course_learning_outcomes WHERE course_id = ?");
        if (!$stmt || !$stmt->bind_param("i", $course_id) || !$stmt->execute()) {
            throw new Exception('Failed to delete existing outcomes', 500);
        }
        $stmt->close();

        // Insert new outcomes
        if (!empty($_POST['outcomes'])) {
            $stmt = $conn->prepare("INSERT INTO course_learning_outcomes (course_id, outcome_text) VALUES (?, ?)");
            if (!$stmt) {
                throw new Exception('Failed to prepare outcomes statement', 500);
            }
            
            foreach ($_POST['outcomes'] as $outcome) {
                $outcome_text = trim($outcome);
                if (!empty($outcome_text)) {
                    if (!$stmt->bind_param("is", $course_id, $outcome_text) || !$stmt->execute()) {
                        throw new Exception('Failed to insert outcome: ' . htmlspecialchars($outcome_text), 500);
                    }
                }
            }
            $stmt->close();
        }

        // Delete existing requirements
        $stmt = $conn->prepare("DELETE FROM course_requirements WHERE course_id = ?");
        if (!$stmt || !$stmt->bind_param("i", $course_id) || !$stmt->execute()) {
            throw new Exception('Failed to delete existing requirements', 500);
        }
        $stmt->close();

        // Insert new requirements
        if (!empty($_POST['requirements'])) {
            $stmt = $conn->prepare("INSERT INTO course_requirements (course_id, requirement_text) VALUES (?, ?)");
            if (!$stmt) {
                throw new Exception('Failed to prepare requirements statement', 500);
            }
            
            foreach ($_POST['requirements'] as $requirement) {
                $requirement_text = trim($requirement);
                if (!empty($requirement_text)) {
                    if (!$stmt->bind_param("is", $course_id, $requirement_text) || !$stmt->execute()) {
                        throw new Exception('Failed to insert requirement: ' . htmlspecialchars($requirement_text), 500);
                    }
                }
            }
            $stmt->close();
        }

        // Update course timestamp
        $stmt = $conn->prepare("UPDATE courses SET updated_at = NOW() WHERE course_id = ?");
        if (!$stmt || !$stmt->bind_param("i", $course_id) || !$stmt->execute()) {
            throw new Exception('Failed to update course timestamp', 500);
        }
        $stmt->close();

        $conn->commit();
        
        $response = [
            'success' => true,
            'message' => 'Course outcomes and requirements saved successfully'
        ];

    } catch (Exception $e) {
        $conn->rollback();
        throw $e; // Re-throw to outer catch block
    }

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 400);
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>