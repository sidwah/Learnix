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
        
        // Log this badge award
        $this->logBadgeAward($user_id, $badge_id, $course_id);
        
        // Send notification
        $this->sendBadgeNotification($user_id, $badge_id, 'course_completion');
        
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
        
        // Log this badge award
        $this->logBadgeAward($user_id, $badge_id, $course_id, $section_id);
        
        // Send notification
        $this->sendBadgeNotification($user_id, $badge_id, 'section_completion', $section_id);
        
        return [
            'success' => $result,
            'message' => $result ? 'Section completion badge awarded' : 'Failed to award badge',
            'badge_id' => $badge_id
        ];
    }
    
    /**
     * Award a perfect score badge for quiz excellence
     * 
     * @param int $user_id User ID
     * @param int $course_id Course ID
     * @param int $quiz_id Quiz ID
     * @param float $score Quiz score
     * @return array Result information with success flag
     */
    public function awardQuizExcellenceBadge($user_id, $course_id, $quiz_id, $score) {
        // Only award for perfect scores (100%)
        if ($score < 100) {
            return [
                'success' => false,
                'message' => 'Score is not perfect'
            ];
        }
        
        // Check if user already has this badge for this quiz
        $stmt = $this->conn->prepare("SELECT user_badge_id 
                                     FROM user_badges ub
                                     JOIN badges b ON ub.badge_id = b.badge_id  
                                     WHERE ub.user_id = ? 
                                     AND ub.course_id = ?
                                     AND b.badge_type = 'quiz_excellence'
                                     AND ub.additional_data = ?");
        $quiz_data = json_encode(['quiz_id' => $quiz_id]);
        $stmt->bind_param("iis", $user_id, $course_id, $quiz_data);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return [
                'success' => false,
                'message' => 'Quiz excellence badge already awarded'
            ];
        }
        
        // Get badge ID for quiz excellence
        $badge_id = $this->getBadgeIdByType('quiz_excellence');
        if (!$badge_id) {
            return [
                'success' => false,
                'message' => 'Badge type not found'
            ];
        }
        
        // Award badge with additional data
        $stmt = $this->conn->prepare("INSERT INTO user_badges 
                                     (user_id, badge_id, course_id, section_id, earned_at, additional_data) 
                                     VALUES (?, ?, ?, NULL, NOW(), ?)");
        $stmt->bind_param("iiis", $user_id, $badge_id, $course_id, $quiz_data);
        $result = $stmt->execute();
        
        // Log and notify
        if ($result) {
            $this->logBadgeAward($user_id, $badge_id, $course_id, null, 'quiz');
            $this->sendBadgeNotification($user_id, $badge_id, 'quiz_excellence');
        }
        
        return [
            'success' => $result,
            'message' => $result ? 'Quiz excellence badge awarded' : 'Failed to award badge',
            'badge_id' => $badge_id
        ];
    }
    
    /**
     * Award streak badge for consistent learning
     * 
     * @param int $user_id User ID
     * @param int $streak_days Number of consecutive days
     * @return array Result information
     */
    public function awardStreakBadge($user_id, $streak_days) {
        // Determine tier based on streak length
        $tier = null;
        $badge_type = 'streak';
        
        if ($streak_days >= 30) {
            $tier = 'platinum';
        } else if ($streak_days >= 14) {
            $tier = 'gold';
        } else if ($streak_days >= 7) {
            $tier = 'silver';
        } else if ($streak_days >= 3) {
            $tier = 'bronze';
        } else {
            return [
                'success' => false,
                'message' => 'Streak not long enough for a badge'
            ];
        }
        
        // Check if user already has this tier of streak badge
        $stmt = $this->conn->prepare("SELECT user_badge_id 
                                     FROM user_badges ub
                                     JOIN badges b ON ub.badge_id = b.badge_id
                                     WHERE ub.user_id = ? 
                                     AND b.badge_type = ? 
                                     AND b.tier = ?");
        $stmt->bind_param("iss", $user_id, $badge_type, $tier);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return [
                'success' => false,
                'message' => "Streak badge ($tier) already awarded"
            ];
        }
        
        // Get badge ID for this streak tier
        $stmt = $this->conn->prepare("SELECT badge_id FROM badges 
                                     WHERE badge_type = ? 
                                     AND tier = ? 
                                     AND is_active = 1 
                                     LIMIT 1");
        $stmt->bind_param("ss", $badge_type, $tier);
        $stmt->execute();
        $badge_result = $stmt->get_result();
        
        if ($badge_result->num_rows == 0) {
            return [
                'success' => false,
                'message' => "Badge type not found for $tier tier"
            ];
        }
        
        $badge = $badge_result->fetch_assoc();
        $badge_id = $badge['badge_id'];
        
        // Award badge with streak data
        $stmt = $this->conn->prepare("INSERT INTO user_badges 
                                     (user_id, badge_id, earned_at, additional_data) 
                                     VALUES (?, ?, NOW(), ?)");
        $streak_data = json_encode(['streak_days' => $streak_days]);
        $stmt->bind_param("iis", $user_id, $badge_id, $streak_data);
        $result = $stmt->execute();
        
        // Log and notify
        if ($result) {
            $this->logBadgeAward($user_id, $badge_id, null, null, 'streak');
            $this->sendBadgeNotification($user_id, $badge_id, 'streak', null, $tier);
        }
        
        return [
            'success' => $result,
            'message' => $result ? "Streak badge ($tier) awarded" : 'Failed to award badge',
            'badge_id' => $badge_id,
            'tier' => $tier
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
    private function getBadgeIdByType($badge_type, $tier = null) {
        if ($tier) {
            $stmt = $this->conn->prepare("SELECT badge_id FROM badges 
                                         WHERE badge_type = ? AND tier = ? AND is_active = 1 
                                         LIMIT 1");
            $stmt->bind_param("ss", $badge_type, $tier);
        } else {
            $stmt = $this->conn->prepare("SELECT badge_id FROM badges 
                                         WHERE badge_type = ? AND is_active = 1 
                                         LIMIT 1");
            $stmt->bind_param("s", $badge_type);
        }
        
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
    
    /**
     * Log badge award to user_activity_logs
     */
    private function logBadgeAward($user_id, $badge_id, $course_id = null, $section_id = null, $type = 'award') {
        // Get badge details
        $stmt = $this->conn->prepare("SELECT name, badge_type, tier FROM badges WHERE badge_id = ?");
        $stmt->bind_param("i", $badge_id);
        $stmt->execute();
        $badge_result = $stmt->get_result();
        $badge = $badge_result->fetch_assoc();
        
        // Create activity details
        $details = [
            'badge_id' => $badge_id,
            'badge_name' => $badge['name'],
            'badge_type' => $badge['badge_type'],
            'badge_tier' => $badge['tier'],
            'course_id' => $course_id,
            'section_id' => $section_id
        ];
        
        $details_json = json_encode($details);
        
        // Log to user_activity_logs
        $stmt = $this->conn->prepare("INSERT INTO user_activity_logs 
                                     (user_id, activity_type, activity_details, ip_address) 
                                     VALUES (?, 'badge_earned', ?, ?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $stmt->bind_param("iss", $user_id, $details_json, $ip);
        $stmt->execute();
    }
    
    /**
     * Send badge notification
     */
    private function sendBadgeNotification($user_id, $badge_id, $badge_type, $section_id = null, $tier = null) {
        // Get badge name
        $stmt = $this->conn->prepare("SELECT name FROM badges WHERE badge_id = ?");
        $stmt->bind_param("i", $badge_id);
        $stmt->execute();
        $badge_result = $stmt->get_result();
        $badge = $badge_result->fetch_assoc();
        
        // Create notification message based on badge type
        $title = "New Badge Earned!";
        $message = "";
        
        switch ($badge_type) {
            case 'course_completion':
                $message = "Congratulations! You've earned the \"{$badge['name']}\" badge for completing this course.";
                break;
                
            case 'section_completion':
                // Get section name if section_id is provided
                if ($section_id) {
                    $stmt = $this->conn->prepare("SELECT title FROM course_sections WHERE section_id = ?");
                    $stmt->bind_param("i", $section_id);
                    $stmt->execute();
                    $section_result = $stmt->get_result();
                    $section = $section_result->fetch_assoc();
                    $message = "You've earned the \"{$badge['name']}\" badge for completing the \"{$section['title']}\" section.";
                } else {
                    $message = "You've earned the \"{$badge['name']}\" badge for completing a course section.";
                }
                break;
                
            case 'quiz_excellence':
                $message = "Amazing work! You've earned the \"{$badge['name']}\" badge for your perfect score.";
                break;
                
            case 'streak':
                $tier_text = ucfirst($tier ?: 'achievement');
                $message = "Keep it up! You've earned the {$tier_text} \"{$badge['name']}\" badge for your learning streak.";
                break;
                
            default:
                $message = "You've earned the \"{$badge['name']}\" badge!";
        }
        
        // Insert notification
        $stmt = $this->conn->prepare("INSERT INTO user_notifications 
                                     (user_id, type, title, message, related_id, related_type, created_at) 
                                     VALUES (?, 'badge', ?, ?, ?, 'badge', NOW())");
        $stmt->bind_param("issi", $user_id, $title, $message, $badge_id);
        $stmt->execute();
    }
    
    /**
     * Generate badge display SVG
     * 
     * @param int $badge_id Badge ID to generate display for
     * @return string SVG content
     */
    public function generateBadgeSVG($badge_id) {
        // Get badge details
        $stmt = $this->conn->prepare("SELECT name, badge_type, tier, badge_image FROM badges WHERE badge_id = ?");
        $stmt->bind_param("i", $badge_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            return null;
        }
        
        $badge = $result->fetch_assoc();
        
        // Template path based on tier
        $tier = $badge['tier'] ?: 'bronze';
        $template_path = "../assets/img/badges/tiers/{$tier}.svg";
        
        // Achievement icon path based on badge type
        $badge_type = $badge['badge_type'];
        $icon_path = "../assets/img/badges/achievements/{$badge_type}.svg";
        
        // If files don't exist, use default
        if (!file_exists($template_path)) {
            $template_path = "../assets/img/badges/tiers/default.svg";
        }
        
        if (!file_exists($icon_path)) {
            $icon_path = "../assets/img/badges/achievements/default.svg";
        }
        
        // Load SVG template and icon
        $template_svg = file_get_contents($template_path);
        $icon_svg = file_exists($icon_path) ? file_get_contents($icon_path) : '';
        
        // Extract icon inner content (skip the outer <svg> tag)
        if (!empty($icon_svg)) {
            $icon_content = preg_match('/<svg[^>]*>(.*)<\/svg>/s', $icon_svg, $matches) ? $matches[1] : '';
        } else {
            $icon_content = '';
        }
        
        // Replace placeholders in template
        $badge_svg = str_replace(
            ['{{BADGE_ICON}}', '{{BADGE_NAME}}', '{{BADGE_DATE}}'],
            [$icon_content, htmlspecialchars($badge['name']), date('M d, Y')],
            $template_svg
        );
        
        return $badge_svg;
    }
    
    /**
     * Process events for awarding badges
     * This method will be called by the event system
     * 
     * @param string $event_type The type of event
     * @param array $event_data Event data
     */
    public function processEvent($event_type, $event_data) {
        switch ($event_type) {
            case 'section_completed':
                // Award section badge when a section is completed
                if (isset($event_data['user_id'], $event_data['course_id'], $event_data['section_id'])) {
                    return $this->awardSectionBadge(
                        $event_data['user_id'],
                        $event_data['course_id'],
                        $event_data['section_id']
                    );
                }
                break;
                
            case 'course_completed':
                // Award course badge when a course is completed
                if (isset($event_data['user_id'], $event_data['course_id'])) {
                    return $this->awardCourseBadge(
                        $event_data['user_id'],
                        $event_data['course_id']
                    );
                }
                break;
                
            case 'quiz_completed':
                // Award quiz excellence badge for perfect scores
                if (isset($event_data['user_id'], $event_data['course_id'], 
                          $event_data['quiz_id'], $event_data['score'])) {
                    return $this->awardQuizExcellenceBadge(
                        $event_data['user_id'],
                        $event_data['course_id'],
                        $event_data['quiz_id'],
                        $event_data['score']
                    );
                }
                break;
                
            case 'learning_streak_updated':
                // Award streak badge
                if (isset($event_data['user_id'], $event_data['streak_days'])) {
                    return $this->awardStreakBadge(
                        $event_data['user_id'],
                        $event_data['streak_days']
                    );
                }
                break;
        }
        
        return ['success' => false, 'message' => 'No handler for event type or missing data'];
    }
}