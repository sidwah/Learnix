<?php
/**
 * Course Progress Helper Functions
 * Contains helper functions to check course completion status
 */

/**
 * Helper function to check if all required quizzes in a course have been completed with a passing grade
 * 
 * @param int $userId User ID
 * @param int $courseId Course ID
 * @param object $conn Database connection
 * @return array Status information with counts
 */
function checkQuizzesCompleted($userId, $courseId, $conn) {
    // Get all quizzes for this course, including is_required flag
    $quizQuery = "SELECT 
                 sq.quiz_id, 
                 sq.quiz_title, 
                 sq.pass_mark,
                 sq.is_required,
                 cs.section_id,
                 cs.title as section_title,
                 COALESCE(
                     (SELECT MAX(score) 
                      FROM student_quiz_attempts 
                      WHERE user_id = ? AND quiz_id = sq.quiz_id), 
                     0
                 ) as highest_score,
                 COALESCE(
                     (SELECT MAX(passed) 
                      FROM student_quiz_attempts 
                      WHERE user_id = ? AND quiz_id = sq.quiz_id), 
                     0
                 ) as is_passed
                 FROM section_quizzes sq
                 JOIN course_sections cs ON sq.section_id = cs.section_id
                 WHERE cs.course_id = ?
                 ORDER BY cs.position, sq.quiz_id";
    
    $stmt = $conn->prepare($quizQuery);
    $stmt->bind_param("iii", $userId, $userId, $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $quizzes = [];
    $totalQuizzes = 0;
    $totalRequiredQuizzes = 0;
    $passedQuizzes = 0;
    $passedRequiredQuizzes = 0;
    $failedQuizzes = [];
    $failedRequiredQuizzes = [];
    
    while ($quiz = $result->fetch_assoc()) {
        $totalQuizzes++;
        
        if ($quiz['is_required'] == 1) {
            $totalRequiredQuizzes++;
        }
        
        if ($quiz['is_passed'] == 1) {
            $passedQuizzes++;
            
            if ($quiz['is_required'] == 1) {
                $passedRequiredQuizzes++;
            }
        } else {
            $failedQuizzes[] = [
                'quiz_id' => $quiz['quiz_id'],
                'quiz_title' => $quiz['quiz_title'],
                'section_title' => $quiz['section_title'],
                'highest_score' => $quiz['highest_score'],
                'pass_mark' => $quiz['pass_mark'],
                'is_required' => $quiz['is_required']
            ];
            
            if ($quiz['is_required'] == 1) {
                $failedRequiredQuizzes[] = [
                    'quiz_id' => $quiz['quiz_id'],
                    'quiz_title' => $quiz['quiz_title'],
                    'section_title' => $quiz['section_title'],
                    'highest_score' => $quiz['highest_score'],
                    'pass_mark' => $quiz['pass_mark']
                ];
            }
        }
        
        $quizzes[] = $quiz;
    }
    
    // Check if all REQUIRED quizzes are passed
    $allRequiredPassed = ($totalRequiredQuizzes == $passedRequiredQuizzes);
    
    // Check if all quizzes (both required and optional) are passed
    $allPassed = ($totalQuizzes == $passedQuizzes);
    
    return [
        'all_passed' => $allPassed,
        'all_required_passed' => $allRequiredPassed, // New field for required quizzes only
        'total_quizzes' => $totalQuizzes,
        'total_required_quizzes' => $totalRequiredQuizzes, // Count of required quizzes
        'passed_quizzes' => $passedQuizzes,
        'passed_required_quizzes' => $passedRequiredQuizzes, // Count of passed required quizzes
        'failed_quizzes' => $failedQuizzes,
        'failed_required_quizzes' => $failedRequiredQuizzes, // Only failed required quizzes
        'quizzes' => $quizzes
    ];
}

/**
 * Check if all requirements for course completion are met
 * 
 * @param int $userId User ID
 * @param int $courseId Course ID
 * @param int $enrollmentId Enrollment ID
 * @param object $conn Database connection
 * @return array Status information with details
 */
function checkCourseCompletionRequirements($userId, $courseId, $enrollmentId, $conn) {
    // Check topics completion
    $topicsQuery = "SELECT 
                   COUNT(DISTINCT st.topic_id) as total_topics,
                   COUNT(DISTINCT CASE WHEN p.completion_status = 'Completed' THEN st.topic_id END) as completed_topics
                   FROM course_sections cs
                   JOIN section_topics st ON cs.section_id = st.section_id
                   LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ?
                   WHERE cs.course_id = ?";
    $stmt = $conn->prepare($topicsQuery);
    $stmt->bind_param("ii", $enrollmentId, $courseId);
    $stmt->execute();
    $topicsResult = $stmt->get_result();
    $topicsData = $topicsResult->fetch_assoc();
    
    // Check if all topics are completed
    $allTopicsCompleted = ($topicsData['completed_topics'] == $topicsData['total_topics']);
    
    // Check quiz completion
    $quizStatus = checkQuizzesCompleted($userId, $courseId, $conn);
    // Use all_required_passed instead of all_passed
    $allRequiredQuizzesPassed = $quizStatus['all_required_passed'];
    
    // Overall completion status
    $allRequirementsMet = $allTopicsCompleted && $allRequiredQuizzesPassed;
    
    return [
        'all_requirements_met' => $allRequirementsMet,
        'topics_status' => [
            'all_completed' => $allTopicsCompleted,
            'total_topics' => $topicsData['total_topics'],
            'completed_topics' => $topicsData['completed_topics']
        ],
        'quiz_status' => $quizStatus
    ];
}