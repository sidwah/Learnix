<?php
// backend/progress/BadgeQualificationService.php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../badges/BadgeHandler.php';

class BadgeQualificationService {
    private $conn;
    private $badgeHandler;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
        $this->badgeHandler = new BadgeHandler();
    }
    
    /**
     * Evaluate if an event should trigger a badge award
     * 
     * @param string $event_type The type of event
     * @param array $event_data Event data
     * @return array Results of badge evaluations
     */
    public function evaluateEvent($event_type, $event_data) {
        // Process the event through the badge handler
        return $this->badgeHandler->processEvent($event_type, $event_data);
    }
    
    /**
     * Check if topic completion leads to section completion
     * Evaluates if completing this topic completes a section
     * 
     * @param int $enrollment_id Enrollment ID
     * @param int $topic_id Topic ID
     * @return array|null Event data if section is completed, null otherwise
     */
    public function checkSectionCompletion($enrollment_id, $topic_id) {
        // Get user_id and course_id from enrollment
        $stmt = $this->conn->prepare("SELECT user_id, course_id FROM enrollments WHERE enrollment_id = ?");
        $stmt->bind_param("i", $enrollment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            return null;
        }
        
        $enrollment = $result->fetch_assoc();
        $user_id = $enrollment['user_id'];
        $course_id = $enrollment['course_id'];
        
        // Get section_id for this topic
        $stmt = $this->conn->prepare("SELECT section_id FROM section_topics WHERE topic_id = ?");
        $stmt->bind_param("i", $topic_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            return null;
        }
        
        $topic_data = $result->fetch_assoc();
        $section_id = $topic_data['section_id'];
        
        // Check if section is now complete
        // Count remaining topics in the section
        $stmt = $this->conn->prepare("SELECT COUNT(*) as remaining_count
                                    FROM section_topics st
                                    LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ?
                                    WHERE st.section_id = ? 
                                    AND (p.completion_status IS NULL OR p.completion_status != 'Completed')");
        $stmt->bind_param("ii", $enrollment_id, $section_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $remaining = $result->fetch_assoc();
        
        // If no remaining topics, check if quizzes are completed
        if ($remaining['remaining_count'] == 0) {
            $stmt = $this->conn->prepare("SELECT 
                                        COUNT(sq.quiz_id) as total_quizzes,
                                        COUNT(CASE WHEN sqa.passed = 1 THEN 1 END) as passed_quizzes
                                        FROM section_quizzes sq
                                        LEFT JOIN (
                                            SELECT quiz_id, MAX(passed) as passed
                                            FROM student_quiz_attempts
                                            WHERE user_id = ?
                                            GROUP BY quiz_id
                                        ) sqa ON sq.quiz_id = sqa.quiz_id
                                        WHERE sq.section_id = ?");
            $stmt->bind_param("ii", $user_id, $section_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $quiz_data = $result->fetch_assoc();
            
            // If all quizzes are passed or there are no quizzes, section is complete
            if ($quiz_data['total_quizzes'] == 0 || $quiz_data['passed_quizzes'] == $quiz_data['total_quizzes']) {
                // Section is completed
                return [
                    'event_type' => 'section_completed',
                    'user_id' => $user_id,
                    'course_id' => $course_id,
                    'section_id' => $section_id
                ];
            }
        }
        
        return null;
    }
    
    /**
     * Check if topic completion leads to course completion
     * Evaluates if completing this topic completes the entire course
     * 
     * @param int $enrollment_id Enrollment ID
     * @param int $course_id Course ID
     * @return array|null Event data if course is completed, null otherwise
     */
    public function checkCourseCompletion($enrollment_id, $course_id) {
        // Get user_id from enrollment
        $stmt = $this->conn->prepare("SELECT user_id FROM enrollments WHERE enrollment_id = ?");
        $stmt->bind_param("i", $enrollment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            return null;
        }
        
        $enrollment = $result->fetch_assoc();
        $user_id = $enrollment['user_id'];
        
        // Check if all topics are completed
        $stmt = $this->conn->prepare("SELECT 
                                    COUNT(DISTINCT st.topic_id) as total_topics,
                                    COUNT(DISTINCT CASE WHEN p.completion_status = 'Completed' THEN st.topic_id END) as completed_topics
                                    FROM course_sections cs
                                    JOIN section_topics st ON cs.section_id = st.section_id
                                    LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ?
                                    WHERE cs.course_id = ?");
        $stmt->bind_param("ii", $enrollment_id, $course_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $topic_data = $result->fetch_assoc();
        
        // If not all topics are completed, course is not complete
        if ($topic_data['completed_topics'] < $topic_data['total_topics']) {
            return null;
        }
        
        // Check if all quizzes are passed
        $stmt = $this->conn->prepare("SELECT 
                                    COUNT(sq.quiz_id) as total_quizzes,
                                    COUNT(CASE WHEN sqa.passed = 1 THEN 1 END) as passed_quizzes
                                    FROM section_quizzes sq
                                    JOIN course_sections cs ON sq.section_id = cs.section_id
                                    LEFT JOIN (
                                        SELECT quiz_id, MAX(passed) as passed
                                        FROM student_quiz_attempts
                                        WHERE user_id = ?
                                        GROUP BY quiz_id
                                    ) sqa ON sq.quiz_id = sqa.quiz_id
                                    WHERE cs.course_id = ?");
        $stmt->bind_param("ii", $user_id, $course_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $quiz_data = $result->fetch_assoc();
        
        // If there are quizzes and not all passed, course is not complete
        if ($quiz_data['total_quizzes'] > 0 && $quiz_data['passed_quizzes'] < $quiz_data['total_quizzes']) {
            return null;
        }
        
        // Course is completed
        return [
            'event_type' => 'course_completed',
            'user_id' => $user_id,
            'course_id' => $course_id
        ];
    }
    
    /**
     * Check for streak achievements
     * Called when user completes a topic, to check for daily learning streaks
     * 
     * @param int $user_id User ID
     * @return array|null Event data if streak achievement earned, null otherwise
     */
    public function checkLearningStreak($user_id) {
        // Get current streak from user_learning_stats
        $stmt = $this->conn->prepare("SELECT current_streak_days FROM user_learning_stats 
                                    WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            // No learning stats yet
            return null;
        }
        
        $stats = $result->fetch_assoc();
        $streak_days = $stats['current_streak_days'];
        
        // Check if streak is at a badge-worthy milestone
        // Streak badges are at 3, 7, 14, and 30 days
        if ($streak_days == 3 || $streak_days == 7 || $streak_days == 14 || $streak_days == 30) {
            return [
                'event_type' => 'learning_streak_updated',
                'user_id' => $user_id,
                'streak_days' => $streak_days
            ];
        }
        
        return null;
    }
    
    /**
     * Check for quiz excellence achievement
     * Called when user completes a quiz with a perfect score
     * 
     * @param int $user_id User ID
     * @param int $course_id Course ID
     * @param int $quiz_id Quiz ID
     * @param float $score Quiz score (percentage)
     * @return array|null Event data if achievement earned, null otherwise
     */
    public function checkQuizExcellence($user_id, $course_id, $quiz_id, $score) {
        // Only trigger for perfect (100%) scores
        if ($score >= 100) {
            return [
                'event_type' => 'quiz_completed',
                'user_id' => $user_id,
                'course_id' => $course_id,
                'quiz_id' => $quiz_id,
                'score' => $score
            ];
        }
        
        return null;
    }
    
    /**
     * Update a user's learning streak
     * Called when user engages with learning content
     * 
     * @param int $user_id User ID
     * @return int Current streak days
     */
    public function updateLearningStreak($user_id) {
        // Get current date for comparison
        $today = date('Y-m-d');
        
        // Check if user already has learning stats
        $stmt = $this->conn->prepare("SELECT user_id, last_activity_date, current_streak_days 
                                    FROM user_learning_stats 
                                    WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // User has existing stats
            $stats = $result->fetch_assoc();
            $last_date = date('Y-m-d', strtotime($stats['last_activity_date']));
            $streak_days = $stats['current_streak_days'];
            
            // Calculate date difference
            $datetime1 = new DateTime($last_date);
            $datetime2 = new DateTime($today);
            $interval = $datetime1->diff($datetime2);
            $days_diff = $interval->days;
            
            // If same day, no streak update needed
            if ($days_diff == 0) {
                return $streak_days;
            }
            
            // If one day after last activity, increment streak
            if ($days_diff == 1) {
                $streak_days++;
            } else {
                // More than one day, reset streak to 1
                $streak_days = 1;
            }
            
            // Update streak
            $stmt = $this->conn->prepare("UPDATE user_learning_stats 
                                        SET current_streak_days = ?, last_activity_date = ? 
                                        WHERE user_id = ?");
            $stmt->bind_param("isi", $streak_days, $today, $user_id);
            $stmt->execute();
        } else {
            // First activity for this user
            $streak_days = 1;
            
            // Create new stats record
            $stmt = $this->conn->prepare("INSERT INTO user_learning_stats 
                                        (user_id, last_activity_date, current_streak_days) 
                                        VALUES (?, ?, 1)");
            $stmt->bind_param("is", $user_id, $today);
            $stmt->execute();
        }
        
        return $streak_days;
    }
}