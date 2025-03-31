<?php
require '../../backend/session_start.php';
require '../../backend/config.php';

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Validate required input
if (!isset($_POST['topic_id']) || !isset($_POST['title']) || !isset($_POST['external_url'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$topic_id = intval($_POST['topic_id']);
$title = trim($_POST['title']);
$external_url = trim($_POST['external_url']);
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$content_id = isset($_POST['content_id']) ? intval($_POST['content_id']) : 0;

// Validate input
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Title cannot be empty']);
    exit;
}

if (empty($external_url)) {
    echo json_encode(['success' => false, 'message' => 'External URL cannot be empty']);
    exit;
}

// Validate URL format
if (!filter_var($external_url, FILTER_VALIDATE_URL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid URL format']);
    exit;
}

// Verify that the topic belongs to a section of a course owned by the current instructor
$stmt = $conn->prepare("
    SELECT st.section_id, cs.course_id, c.instructor_id 
    FROM section_topics st
    JOIN course_sections cs ON st.section_id = cs.section_id
    JOIN courses c ON cs.course_id = c.course_id
    WHERE st.topic_id = ?
");
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$result = $stmt->get_result();
$topic_data = $result->fetch_assoc();
$stmt->close();

if (!$topic_data || $topic_data['instructor_id'] != $_SESSION['instructor_id']) {
    echo json_encode(['success' => false, 'message' => 'Topic not found or not authorized']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Check if content already exists
    if ($content_id > 0) {
        // First, save the current version if it exists
        $stmt = $conn->prepare("
            SELECT * FROM topic_content 
            WHERE content_id = ? AND topic_id = ?
        ");
        $stmt->bind_param("ii", $content_id, $topic_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_content = $result->fetch_assoc();
        $stmt->close();
        
        if ($current_content) {
            // Save the current version
            $stmt = $conn->prepare("
                INSERT INTO topic_content_versions 
                (content_id, version_number, content_data, created_by, created_at)
                SELECT 
                    ?, 
                    IFNULL((SELECT MAX(version_number) FROM topic_content_versions WHERE content_id = ?) + 1, 1),
                    JSON_OBJECT(
                        'title', ?,
                        'content_type', ?,
                        'external_url', ?,
                        'description', ?,
                        'position', ?
                    ),
                    ?,
                    NOW()
            ");
            $stmt->bind_param(
                "iissssii",
                $content_id,
                $content_id,
                $current_content['title'],
                $current_content['content_type'],
                $current_content['external_url'],
                $current_content['description'],
                $current_content['position'],
                $_SESSION['user_id']
            );
            $stmt->execute();
            $stmt->close();
        }
        
        // Update existing content
        $stmt = $conn->prepare("
            UPDATE topic_content
            SET title = ?, external_url = ?, description = ?, updated_at = NOW()
            WHERE content_id = ? AND topic_id = ?
        ");
        $stmt->bind_param("sssii", $title, $external_url, $description, $content_id, $topic_id);
        $stmt->execute();
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Link content updated successfully',
            'content_id' => $content_id
        ]);
    } else {
        // Check if any content already exists for this topic
        $stmt = $conn->prepare("SELECT content_id FROM topic_content WHERE topic_id = ?");
        $stmt->bind_param("i", $topic_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $existing_content = $result->fetch_assoc();
        $stmt->close();
        
        if ($existing_content) {
            // Delete existing content
            $stmt = $conn->prepare("DELETE FROM topic_content WHERE topic_id = ?");
            $stmt->bind_param("i", $topic_id);
            $stmt->execute();
            $stmt->close();
        }
        
        // Create new content
        $stmt = $conn->prepare("
            INSERT INTO topic_content 
            (topic_id, content_type, title, external_url, description, position, created_at)
            VALUES (?, 'link', ?, ?, ?, 0, NOW())
        ");
        $stmt->bind_param("isss", $topic_id, $title, $external_url, $description);
        $stmt->execute();
        $new_content_id = $stmt->insert_id;
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Link content created successfully',
            'content_id' => $new_content_id
        ]);
    }
    
    // Update course last modified timestamp
    $stmt = $conn->prepare("UPDATE courses SET updated_at = NOW() WHERE course_id = ?");
    $stmt->bind_param("i", $topic_data['course_id']);
    $stmt->execute();
    $stmt->close();
    
    // Commit transaction
    $conn->commit();
} catch (Exception $e) {
    // Rollback in case of error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>