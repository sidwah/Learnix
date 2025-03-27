<?php
require '../../backend/session_start.php';
require '../../backend/config.php';

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Validate required input
if (!isset($_POST['type']) || !isset($_POST['order'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$type = $_POST['type'];
$order = json_decode($_POST['order'], true);

// Validate type
if ($type !== 'section' && $type !== 'topic') {
    echo json_encode(['success' => false, 'message' => 'Invalid order type']);
    exit;
}

// Validate order array
if (!is_array($order) || empty($order)) {
    echo json_encode(['success' => false, 'message' => 'Invalid order data']);
    exit;
}

// Check if it's a section order update
if ($type === 'section') {
    if (!isset($_POST['course_id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing course ID']);
        exit;
    }
    
    $course_id = intval($_POST['course_id']);
    
    // Verify that the course belongs to the current instructor
    $stmt = $conn->prepare("SELECT instructor_id FROM courses WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $course = $result->fetch_assoc();
    $stmt->close();
    
    if (!$course || $course['instructor_id'] != $_SESSION['instructor_id']) {
        echo json_encode(['success' => false, 'message' => 'Course not found or not authorized']);
        exit;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update section positions
        $stmt = $conn->prepare("UPDATE course_sections SET position = ? WHERE section_id = ?");
        
        foreach ($order as $item) {
            $section_id = intval($item['section_id']);
            $position = intval($item['position']);
            
            $stmt->bind_param("ii", $position, $section_id);
            $stmt->execute();
        }
        
        $stmt->close();
        
        // Update course last modified timestamp
        $stmt = $conn->prepare("UPDATE courses SET updated_at = NOW() WHERE course_id = ?");
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Section order updated successfully'
        ]);
        
    } catch (Exception $e) {
        // Rollback in case of error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
// Check if it's a topic order update
else if ($type === 'topic') {
    if (!isset($_POST['section_id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing section ID']);
        exit;
    }
    
    $section_id = intval($_POST['section_id']);
    
    // Verify that the section belongs to a course owned by the current instructor
    $stmt = $conn->prepare("
        SELECT cs.course_id, c.instructor_id 
        FROM course_sections cs
        JOIN courses c ON cs.course_id = c.course_id
        WHERE cs.section_id = ?
    ");
    $stmt->bind_param("i", $section_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $section_data = $result->fetch_assoc();
    $stmt->close();
    
    if (!$section_data || $section_data['instructor_id'] != $_SESSION['instructor_id']) {
        echo json_encode(['success' => false, 'message' => 'Section not found or not authorized']);
        exit;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update topic positions
        $stmt = $conn->prepare("UPDATE section_topics SET position = ? WHERE topic_id = ?");
        
        foreach ($order as $item) {
            $topic_id = intval($item['topic_id']);
            $position = intval($item['position']);
            
            $stmt->bind_param("ii", $position, $topic_id);
            $stmt->execute();
        }
        
        $stmt->close();
        
        // Update course last modified timestamp
        $stmt = $conn->prepare("UPDATE courses SET updated_at = NOW() WHERE course_id = ?");
        $stmt->bind_param("i", $section_data['course_id']);
        $stmt->execute();
        $stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Topic order updated successfully'
        ]);
        
    } catch (Exception $e) {
        // Rollback in case of error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>