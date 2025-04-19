<?php
// backend/badges/BadgeHandler.php

require_once __DIR__ . '/../config.php';

class BadgeHandler {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    /**
     * Award a course badge to a user when all requirements are met
     * 
     * @param int $user_id User ID
     * @param int $course_id Course ID
     * @return array Result information with success flag
     */
    public function awardCourseBadge($user_id, $course_id) {
        // First, verify that all requirements are actually met
        $all_requirements_met = $this->checkAllCourseRequirementsMet($user_id, $course_id);
        
        if (!$all_requirements_met) {
            return [
                'success' => false,
                'message' => 'Not all course requirements have been met'
            ];
        }
        
        // Check if user already has this badge
        if ($this->userHasBadgeForCourse($user_id, $course_id, 'course_completion')) {
            return [
                'success' => false,
                'message' => 'Badge already awarded'
            ];
        }
        
        // Get badge ID for course completion
        $badge_id = $this->getBadgeIdByType('course_completion');
        if (!$badge_id) {
            return [
                'success' => false,
                'message' => 'Badge type not found'
            ];
        }
        
        // Award badge
        $result = $this->awardBadge($user_id, $badge_id, $course_id);
        
        return [
            'success' => $result,
            'message' => $result ? 'Course completion badge awarded' : 'Failed to award badge',
            'badge_id' => $badge_id
        ];
    }
    
    /**
     * Award section completion badge
     * 
     * @param int $user_id User ID
     * @param int $course_id Course ID
     * @param int $section_id Section ID
     * @return array Result information with success flag
     */
    public function awardSectionBadge($user_id, $course_id, $section_id) {
        // Check if section is actually complete
        $section_complete = $this->checkSectionComplete($user_id, $course_id, $section_id);
        
        if (!$section_complete) {
            return [
                'success' => false,
                'message' => 'Section is not complete'
            ];
        }
        
        // Check if user already has this badge
        if ($this->userHasBadgeForSection($user_id, $course_id, $section_id)) {
            return [
                'success' => false,
                'message' => 'Section badge already awarded'
            ];
        }
        
        // Get badge ID for section completion
        $badge_id = $this->getBadgeIdByType('section_completion');
        if (!$badge_id) {
            return [
                'success' => false,
                'message' => 'Badge type not found'
            ];
        }
        
        // Award badge
        $result = $this->awardBadge($user_id, $badge_id, $course_id, $section_id);
        
        return [
            'success' => $result,
            'message' => $result ? 'Section completion badge awarded' : 'Failed to award badge',
            'badge_id' => $badge_id
        ];
    }
    
    /**
     * Check if all course requirements are met
     * 
     * @param int $user_id User ID
     * @param int $course_id Course ID
     * @return bool True if all requirements are met
     */
    public function checkAllCourseRequirementsMet($user_id, $course_id) {
        // Check topics completion
        $topics_query = "SELECT 
                       COUNT(DISTINCT st.topic_id) as total_topics,
                       COUNT(DISTINCT CASE WHEN p.completion_status = 'Completed' THEN st.topic_id END) as completed_topics
                       FROM course_sections cs
                       JOIN section_topics st ON cs.section_id = st.section_id
                       LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = (
                           SELECT enrollment_id FROM enrollments WHERE user_id = ? AND course_id = ?
                       )
                       WHERE cs.course_id = ?";
        $stmt = $this->conn->prepare($topics_query);
        $stmt->bind_param("iii", $user_id, $course_id, $course_id);
        $stmt->execute();
        $topics_result = $stmt->get_result();
        $topics_data = $topics_result->fetch_assoc();
        
        // If not all topics are completed, requirements are not met
        if ($topics_data['completed_topics'] < $topics_data['total_topics']) {
            return false;
        }
        
        // Check quiz completion
        $quiz_query = "SELECT 
                     COUNT(sq.quiz_id) as total_quizzes,
                     COUNT(CASE WHEN sqa.score >= sq.pass_mark THEN 1 END) as passed_quizzes
                     FROM section_quizzes sq
                     JOIN course_sections cs ON sq.section_id = cs.section_id
                     LEFT JOIN (
                         SELECT quiz_id, MAX(score) as score
                         FROM student_quiz_attempts
                         WHERE user_id = ?
                         GROUP BY quiz_id
                     ) sqa ON sq.quiz_id = sqa.quiz_id
                     WHERE cs.course_id = ?";
        $stmt = $this->conn->prepare($quiz_query);
        $stmt->bind_param("ii", $user_id, $course_id);
        $stmt->execute();
        $quiz_result = $stmt->get_result();
        $quiz_data = $quiz_result->fetch_assoc();
        
        // If there are quizzes but not all are passed, requirements are not met
        if ($quiz_data['total_quizzes'] > 0 && $quiz_data['passed_quizzes'] < $quiz_data['total_quizzes']) {
            return false;
        }
        
        // All requirements are met
        return true;
    }
    
    /**
     * Check if a section is complete
     * 
     * @param int $user_id User ID
     * @param int $course_id Course ID
     * @param int $section_id Section ID
     * @return bool True if section is complete
     */
    private function checkSectionComplete($user_id, $course_id, $section_id) {
        // Get enrollment ID
        $enrollment_query = "SELECT enrollment_id FROM enrollments WHERE user_id = ? AND course_id = ?";
        $stmt = $this->conn->prepare($enrollment_query);
        $stmt->bind_param("ii", $user_id, $course_id);
        $stmt->execute();
        $enrollment_result = $stmt->get_result();
        
        if ($enrollment_result->num_rows == 0) {
            return false;
        }
        
        $enrollment = $enrollment_result->fetch_assoc();
        $enrollment_id = $enrollment['enrollment_id'];
        
        // Check topics completion
        $topics_query = "SELECT 
                       COUNT(DISTINCT st.topic_id) as total_topics,
                       COUNT(DISTINCT CASE WHEN p.completion_status = 'Completed' THEN st.topic_id END) as completed_topics
                       FROM section_topics st
                       LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ?
                       WHERE st.section_id = ?";
        $stmt = $this->conn->prepare($topics_query);
        $stmt->bind_param("ii", $enrollment_id, $section_id);
        $stmt->execute();
        $topics_result = $stmt->get_result();
        $topics_data = $topics_result->fetch_assoc();
        
        // If not all topics are completed, section is not complete
        if ($topics_data['completed_topics'] < $topics_data['total_topics']) {
            return false;
        }
        
        // Check quiz completion
        $quiz_query = "SELECT 
                     COUNT(sq.quiz_id) as total_quizzes,
                     COUNT(CASE WHEN sqa.score >= sq.pass_mark THEN 1 END) as passed_quizzes
                     FROM section_quizzes sq
                     LEFT JOIN (
                         SELECT quiz_id, MAX(score) as score
                         FROM student_quiz_attempts
                         WHERE user_id = ?
                         GROUP BY quiz_id
                     ) sqa ON sq.quiz_id = sqa.quiz_id
                     WHERE sq.section_id = ?";
        $stmt = $this->conn->prepare($quiz_query);
        $stmt->bind_param("ii", $user_id, $section_id);
        $stmt->execute();
        $quiz_result = $stmt->get_result();
        $quiz_data = $quiz_result->fetch_assoc();
        
        // If there are quizzes but not all are passed, section is not complete
        if ($quiz_data['total_quizzes'] > 0 && $quiz_data['passed_quizzes'] < $quiz_data['total_quizzes']) {
            return false;
        }
        
        // Section is complete
        return true;
    }
    
    /**
     * Check if user already has a badge for this course
     */
    private function userHasBadgeForCourse($user_id, $course_id, $badge_type) {
        $stmt = $this->conn->prepare("SELECT ub.user_badge_id 
                                     FROM user_badges ub 
                                     JOIN badges b ON ub.badge_id = b.badge_id 
                                     WHERE ub.user_id = ? 
                                     AND ub.course_id = ? 
                                     AND b.badge_type = ?");
        $stmt->bind_param("iis", $user_id, $course_id, $badge_type);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
    }
    
    /**
     * Check if user already has a badge for this section
     */
    private function userHasBadgeForSection($user_id, $course_id, $section_id) {
        $stmt = $this->conn->prepare("SELECT user_badge_id 
                                     FROM user_badges 
                                     WHERE user_id = ? 
                                     AND course_id = ? 
                                     AND section_id = ?");
        $stmt->bind_param("iii", $user_id, $course_id, $section_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
    }
    
    /**
     * Get badge ID by type
     */
    private function getBadgeIdByType($badge_type) {
        $stmt = $this->conn->prepare("SELECT badge_id FROM badges WHERE badge_type = ? AND is_active = 1 LIMIT 1");
        $stmt->bind_param("s", $badge_type);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $badge = $result->fetch_assoc();
            return $badge['badge_id'];
        }
        
        return null;
    }
    
    /**
     * Award badge to user
     */
    private function awardBadge($user_id, $badge_id, $course_id, $section_id = null) {
        $stmt = $this->conn->prepare("INSERT INTO user_badges 
                                     (user_id, badge_id, course_id, section_id, earned_at) 
                                     VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("iiis", $user_id, $badge_id, $course_id, $section_id);
        return $stmt->execute();
    }
}