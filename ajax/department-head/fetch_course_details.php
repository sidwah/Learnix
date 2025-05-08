<?php
// Include database connection
require_once '../../backend/config.php';

// Check if admin is logged in
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Initialize response array
$response = [
    'status' => 'success',
    'course' => [],
    'sections' => [],
    'materials' => [],
    'review_history' => []
];

// Check if course_id is set
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $response['status'] = 'error';
    $response['message'] = 'Course ID is required';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

try {
    $courseId = intval($_GET['id']);
    
    // Fetch course information with instructor details
    $courseQuery = "
        SELECT 
            c.course_id,
            c.title,
            c.short_description,
            c.full_description,
            c.thumbnail,
            c.status,
            c.approval_status,
            c.price,
            c.course_level,
            c.certificate_enabled,
            c.created_at,
            c.updated_at,
            CONCAT(u.first_name, ' ', u.last_name) AS instructor_name,
            u.user_id AS instructor_user_id,
            i.instructor_id,
            i.verification_status AS instructor_verification,
            i.bio AS instructor_bio,
            cat.name AS category_name,
            cat.category_id,
            sub.name AS subcategory_name,
            sub.subcategory_id,
            (SELECT COUNT(*) FROM enrollments WHERE course_id = c.course_id) AS enrollment_count
        FROM 
            courses c
        JOIN 
            instructors i ON c.instructor_id = i.instructor_id
        JOIN 
            users u ON i.user_id = u.user_id
        JOIN 
            subcategories sub ON c.subcategory_id = sub.subcategory_id
        JOIN 
            categories cat ON sub.category_id = cat.category_id
        WHERE 
            c.course_id = ?
    ";
    
    $stmt = $conn->prepare($courseQuery);
    $stmt->bind_param('i', $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $response['status'] = 'error';
        $response['message'] = 'Course not found';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    $response['course'] = $result->fetch_assoc();
    
    // Format dates
    $createdDate = new DateTime($response['course']['created_at']);
    $response['course']['formatted_created_date'] = $createdDate->format('F j, Y');
    
    // Fetch learning outcomes
    $outcomesQuery = "
        SELECT outcome_id, outcome_text
        FROM course_learning_outcomes
        WHERE course_id = ?
        ORDER BY outcome_id
    ";
    
    $stmt = $conn->prepare($outcomesQuery);
    $stmt->bind_param('i', $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $response['outcomes'] = [];
    while ($row = $result->fetch_assoc()) {
        $response['outcomes'][] = $row;
    }
    
    // Fetch requirements
    $requirementsQuery = "
        SELECT requirement_id, requirement_text
        FROM course_requirements
        WHERE course_id = ?
        ORDER BY requirement_id
    ";
    
    $stmt = $conn->prepare($requirementsQuery);
    $stmt->bind_param('i', $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $response['requirements'] = [];
    while ($row = $result->fetch_assoc()) {
        $response['requirements'][] = $row;
    }
    
    // Fetch course sections and topics
    $sectionsQuery = "
        SELECT 
            s.section_id,
            s.title AS section_title,
            s.position AS section_position,
            t.topic_id,
            t.title AS topic_title,
            t.position AS topic_position,
            t.is_previewable,
            q.quiz_id,
            q.quiz_title,
            q.pass_mark
        FROM 
            course_sections s
        LEFT JOIN 
            section_topics t ON s.section_id = t.section_id
        LEFT JOIN 
            section_quizzes q ON s.section_id = q.section_id
        WHERE 
            s.course_id = ?
        ORDER BY 
            s.position, t.position
    ";
    
    $stmt = $conn->prepare($sectionsQuery);
    $stmt->bind_param('i', $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $sections = [];
    $sectionMap = [];
    
    while ($row = $result->fetch_assoc()) {
        $sectionId = $row['section_id'];
        
        if (!isset($sectionMap[$sectionId])) {
            $sectionMap[$sectionId] = count($sections);
            $sections[] = [
                'section_id' => $sectionId,
                'title' => $row['section_title'],
                'position' => $row['section_position'],
                'topics' => [],
                'quizzes' => []
            ];
        }
        
        $sectionIndex = $sectionMap[$sectionId];
        
        // Add topic if it exists
        if ($row['topic_id']) {
            $sections[$sectionIndex]['topics'][] = [
                'topic_id' => $row['topic_id'],
                'title' => $row['topic_title'],
                'position' => $row['topic_position'],
                'is_previewable' => $row['is_previewable']
            ];
        }
        
        // Add quiz if it exists and isn't already added
        if ($row['quiz_id']) {
            $quizExists = false;
            foreach ($sections[$sectionIndex]['quizzes'] as $existingQuiz) {
                if ($existingQuiz['quiz_id'] === $row['quiz_id']) {
                    $quizExists = true;
                    break;
                }
            }
            
            if (!$quizExists) {
                $sections[$sectionIndex]['quizzes'][] = [
                    'quiz_id' => $row['quiz_id'],
                    'title' => $row['quiz_title'],
                    'pass_mark' => $row['pass_mark']
                ];
            }
        }
    }
    
    $response['sections'] = $sections;
    
    // Fetch review requests
    $reviewQuery = "
        SELECT 
            r.request_id,
            r.status,
            r.requested_by,
            CONCAT(u1.first_name, ' ', u1.last_name) AS requester_name,
            r.request_notes,
            r.reviewer_id,
            CONCAT(u2.first_name, ' ', u2.last_name) AS reviewer_name,
            r.review_notes,
            r.created_at,
            r.updated_at
        FROM 
            course_review_requests r
        JOIN 
            users u1 ON r.requested_by = u1.user_id
        LEFT JOIN 
            users u2 ON r.reviewer_id = u2.user_id
        WHERE 
            r.course_id = ?
        ORDER BY 
            r.created_at DESC
    ";
    
    $stmt = $conn->prepare($reviewQuery);
    $stmt->bind_param('i', $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $response['review_history'] = [];
    while ($row = $result->fetch_assoc()) {
        $createdDate = new DateTime($row['created_at']);
        $row['formatted_created_date'] = $createdDate->format('F j, Y g:i A');
        
        if ($row['updated_at']) {
            $updatedDate = new DateTime($row['updated_at']);
            $row['formatted_updated_date'] = $updatedDate->format('F j, Y g:i A');
        }
        
        $response['review_history'][] = $row;
    }
    
    // Fetch course resources
    $resourcesQuery = "
        SELECT 
            r.resource_id,
            r.topic_id,
            r.resource_path,
            t.title AS topic_title,
            s.title AS section_title
        FROM 
            topic_resources r
        JOIN 
            section_topics t ON r.topic_id = t.topic_id
        JOIN 
            course_sections s ON t.section_id = s.section_id
        WHERE 
            s.course_id = ?
    ";
    
    $stmt = $conn->prepare($resourcesQuery);
    $stmt->bind_param('i', $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $response['resources'] = [];
    while ($row = $result->fetch_assoc()) {
        // Get file info
        $filePath = '../../uploads/resources/' . $row['resource_path'];
        $fileInfo = pathinfo($filePath);
        $fileSize = file_exists($filePath) ? filesize($filePath) : 0;
        
        // Format file size
        if ($fileSize < 1024) {
            $formattedSize = $fileSize . ' B';
        } elseif ($fileSize < 1024 * 1024) {
            $formattedSize = round($fileSize / 1024, 1) . ' KB';
        } else {
            $formattedSize = round($fileSize / (1024 * 1024), 1) . ' MB';
        }
        
        $row['file_name'] = $fileInfo['basename'];
        $row['file_extension'] = isset($fileInfo['extension']) ? strtoupper($fileInfo['extension']) : '';
        $row['file_size'] = $formattedSize;
        $row['download_url'] = '../uploads/resources/' . $row['resource_path'];
        
        $response['resources'][] = $row;
    }
    
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = 'Error fetching course details: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>