<?php
require_once '../config.php';

header('Content-Type: application/json');

// Check if user is logged in and has proper role
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $topic_id = isset($input['topic_id']) ? (int)$input['topic_id'] : 0;
    $course_id = isset($input['course_id']) ? (int)$input['course_id'] : 0;
    
    if (!$topic_id || !$course_id) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Verify user has access to this course
    $access_query = "SELECT d.department_id 
                     FROM departments d 
                     INNER JOIN department_staff ds ON d.department_id = ds.department_id 
                     INNER JOIN courses c ON c.department_id = d.department_id
                     WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' 
                     AND ds.deleted_at IS NULL AND c.course_id = ? AND c.deleted_at IS NULL";
    
    $access_stmt = $conn->prepare($access_query);
    $access_stmt->bind_param("ii", $user_id, $course_id);
    $access_stmt->execute();
    
    if ($access_stmt->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit();
    }
    
    // Get topic details with content
    $topic_query = "SELECT 
                        st.topic_id,
                        st.title,
                        st.position,
                        cs.position as section_position,
                        cs.title as section_title,
                        tc.content_type,
                        tc.title as content_title,
                        tc.content_text,
                        tc.video_url,
                        tc.video_file,
                        tc.external_url,
                        tc.file_path,
                        tc.description
                    FROM section_topics st
                    INNER JOIN course_sections cs ON st.section_id = cs.section_id
                    LEFT JOIN topic_content tc ON st.topic_id = tc.topic_id AND tc.deleted_at IS NULL
                    WHERE st.topic_id = ? AND cs.course_id = ?  
                    ORDER BY tc.position ASC
                    LIMIT 1";
    
    $topic_stmt = $conn->prepare($topic_query);
    $topic_stmt->bind_param("ii", $topic_id, $course_id);
    $topic_stmt->execute();
    $topic_result = $topic_stmt->get_result();
    
    if ($topic_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Topic not found']);
        exit();
    }
    
    $topic_data = $topic_result->fetch_assoc();
    
    // Get topic resources
    $resources_query = "SELECT resource_path, created_at FROM topic_resources WHERE topic_id = ? ORDER BY created_at ASC";
    $resources_stmt = $conn->prepare($resources_query);
    $resources_stmt->bind_param("i", $topic_id);
    $resources_stmt->execute();
    $resources_result = $resources_stmt->get_result();
    $resources = [];
    
    while ($resource = $resources_result->fetch_assoc()) {
        $resources[] = [
            'name' => basename($resource['resource_path']),
            'path' => $resource['resource_path']
        ];
    }
    
    $topic_data['resources'] = $resources;
    
    echo json_encode([
        'success' => true,
        'topic' => $topic_data
    ]);
    
} catch (Exception $e) {
    error_log("Error getting topic content: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while loading topic content']);
}
?>