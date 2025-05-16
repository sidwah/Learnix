<?php
//ajax/content/save_text.php
require '../../backend/session_start.php';
require '../../backend/config.php';

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get POST data
$topic_id = isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0;
$content_text = isset($_POST['content_text']) ? $_POST['content_text'] : '';
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$position = isset($_POST['position']) ? intval($_POST['position']) : 0;
$content_id = isset($_POST['content_id']) ? intval($_POST['content_id']) : 0;
$instructor_id = $_SESSION['instructor_id'];
$user_id = $_SESSION['user_id'];

// Validate data
if (!$topic_id) {
    echo json_encode(['success' => false, 'message' => 'Topic ID is required']);
    exit;
}

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Content title is required']);
    exit;
}

// Verify that the topic belongs to a section of a course assigned to the current instructor
$stmt = $conn->prepare("
    SELECT 
        st.section_id, 
        cs.course_id
    FROM 
        section_topics st
    JOIN 
        course_sections cs ON st.section_id = cs.section_id
    JOIN 
        course_instructors ci ON cs.course_id = ci.course_id
    WHERE 
        st.topic_id = ? AND
        ci.instructor_id = ? AND
        ci.deleted_at IS NULL
");
$stmt->bind_param("ii", $topic_id, $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
$topic_data = $result->fetch_assoc();
$stmt->close();

if (!$topic_data) {
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
            // Save the current version if versioning table exists
            if ($conn->query("SHOW TABLES LIKE 'topic_content_versions'")->num_rows > 0) {
                $stmt = $conn->prepare("
                    INSERT INTO topic_content_versions 
                    (content_id, version_number, content_data, created_by, created_at)
                    SELECT 
                        ?, 
                        IFNULL((SELECT MAX(version_number) FROM topic_content_versions WHERE content_id = ?) + 1, 1),
                        JSON_OBJECT(
                            'title', ?,
                            'content_type', ?,
                            'content_text', ?,
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
                    $current_content['content_text'],
                    $current_content['description'],
                    $current_content['position'],
                    $user_id
                );
                $stmt->execute();
                $stmt->close();
            }
        }
        
        // Update existing content
        $stmt = $conn->prepare("
            UPDATE topic_content
            SET title = ?, content_text = ?, description = ?, position = ?, 
                updated_at = NOW(), updated_by = ?
            WHERE content_id = ? AND topic_id = ?
        ");
        $stmt->bind_param("sssiiii", $title, $content_text, $description, $position, $user_id, $content_id, $topic_id);
        $stmt->execute();
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Text content updated successfully',
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
            // Delete existing content (only one content per topic allowed)
            $stmt = $conn->prepare("DELETE FROM topic_content WHERE topic_id = ?");
            $stmt->bind_param("i", $topic_id);
            $stmt->execute();
            $stmt->close();
        }
        
        // Create new content
        $stmt = $conn->prepare("
            INSERT INTO topic_content 
            (topic_id, content_type, title, content_text, description, position, created_at, created_by)
            VALUES (?, 'text', ?, ?, ?, ?, NOW(), ?)
        ");
        $stmt->bind_param("isssii", $topic_id, $title, $content_text, $description, $position, $user_id);
        $stmt->execute();
        $new_content_id = $stmt->insert_id;
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Text content created successfully',
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