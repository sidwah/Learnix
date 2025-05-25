<?php
header('Content-Type: application/json');
require_once '../config.php';

// Check if user is logged in and has proper role
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['enrollment_id']) || !isset($input['user_id']) || !isset($input['course_id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit();
    }
    
    $enrollment_id = (int)$input['enrollment_id'];
    $student_user_id = (int)$input['user_id'];
    $course_id = (int)$input['course_id'];
    $dept_head_user_id = $_SESSION['user_id'];
    
    try {
        // Verify access
        $verify_query = "SELECT e.enrollment_id
                        FROM enrollments e
                        INNER JOIN courses c ON e.course_id = c.course_id
                        INNER JOIN departments d ON c.department_id = d.department_id
                        INNER JOIN department_staff ds ON d.department_id = ds.department_id
                        WHERE e.enrollment_id = ? 
                        AND ds.user_id = ? 
                        AND ds.role = 'head' 
                        AND ds.status = 'active' 
                        AND ds.deleted_at IS NULL
                        AND e.deleted_at IS NULL";
        
        $verify_stmt = $conn->prepare($verify_query);
        $verify_stmt->bind_param("ii", $enrollment_id, $dept_head_user_id);
        $verify_stmt->execute();
        
        if ($verify_stmt->get_result()->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit();
        }
        
        // Get overall course progress
        $overall_query = "SELECT 
                            COUNT(st.topic_id) as total_topics,
                            COUNT(CASE WHEN p.completion_status = 'Completed' THEN 1 END) as completed_topics,
                            COALESCE(
                                (COUNT(CASE WHEN p.completion_status = 'Completed' THEN 1 END) * 100.0) / 
                                NULLIF(COUNT(st.topic_id), 0), 0
                            ) as overall_progress,
                            SUM(CASE WHEN p.time_spent IS NOT NULL THEN p.time_spent ELSE 0 END) as total_time_minutes
                         FROM course_sections cs
                         LEFT JOIN section_topics st ON cs.section_id = st.section_id  
                         LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ? AND p.deleted_at IS NULL
                         WHERE cs.course_id = ? AND cs.deleted_at IS NULL";
        
        $overall_stmt = $conn->prepare($overall_query);
        $overall_stmt->bind_param("ii", $enrollment_id, $course_id);
        $overall_stmt->execute();
        $overall_result = $overall_stmt->get_result()->fetch_assoc();
        
        // Get quiz statistics
        $quiz_query = "SELECT 
                          COUNT(DISTINCT sq.quiz_id) as total_quizzes,
                          COUNT(DISTINCT CASE WHEN sqa.passed = 1 THEN sq.quiz_id END) as passed_quizzes,
                          AVG(CASE WHEN sqa.score IS NOT NULL THEN sqa.score ELSE 0 END) as average_score
                       FROM course_sections cs
                       LEFT JOIN section_quizzes sq ON cs.section_id = sq.section_id  
                       LEFT JOIN student_quiz_attempts sqa ON sq.quiz_id = sqa.quiz_id 
                           AND sqa.user_id = ? AND sqa.is_completed = 1 AND sqa.deleted_at IS NULL
                       WHERE cs.course_id = ? AND cs.deleted_at IS NULL";
        
        $quiz_stmt = $conn->prepare($quiz_query);
        $quiz_stmt->bind_param("ii", $student_user_id, $course_id);
        $quiz_stmt->execute();
        $quiz_result = $quiz_stmt->get_result()->fetch_assoc();
        
        // Get section-wise progress
        $sections_query = "SELECT 
                              cs.section_id,
                              cs.title,
                              cs.position,
                              COUNT(st.topic_id) as total_topics,
                              COUNT(CASE WHEN p.completion_status = 'Completed' THEN 1 END) as completed_topics,
                              COALESCE(
                                  (COUNT(CASE WHEN p.completion_status = 'Completed' THEN 1 END) * 100.0) / 
                                  NULLIF(COUNT(st.topic_id), 0), 0
                              ) as section_progress
                           FROM course_sections cs
                           LEFT JOIN section_topics st ON cs.section_id = st.section_id 
                           LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ? AND p.deleted_at IS NULL
                           WHERE cs.course_id = ? AND cs.deleted_at IS NULL
                           GROUP BY cs.section_id, cs.title, cs.position
                           ORDER BY cs.position";
        
        $sections_stmt = $conn->prepare($sections_query);
        $sections_stmt->bind_param("ii", $enrollment_id, $course_id);
        $sections_stmt->execute();
        $sections_result = $sections_stmt->get_result();
        
        $sections = [];
        while ($section = $sections_result->fetch_assoc()) {
            $sections[] = [
                'title' => $section['title'],
                'total_topics' => (int)$section['total_topics'],
                'completed_topics' => (int)$section['completed_topics'],
                'progress' => (float)$section['section_progress']
            ];
        }
        
        // Prepare response
        $progress_data = [
            'overall_progress' => (float)$overall_result['overall_progress'],
            'total_topics' => (int)$overall_result['total_topics'],
            'completed_topics' => (int)$overall_result['completed_topics'],
            'total_quizzes' => (int)$quiz_result['total_quizzes'],
            'passed_quizzes' => (int)$quiz_result['passed_quizzes'],
            'average_score' => round((float)$quiz_result['average_score'], 1),
            'time_spent' => round((int)$overall_result['total_time_minutes'] / 60, 1), // Convert to hours
            'sections' => $sections
        ];
        
        echo json_encode([
            'success' => true,
            'progress' => $progress_data
        ]);
        
    } catch (Exception $e) {
        error_log("Error fetching student progress: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while fetching progress data']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>