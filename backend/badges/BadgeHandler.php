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
     * Award course completion badge
     */
    public function awardCourseBadge($user_id, $course_id) {
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
     */
    public function awardSectionBadge($user_id, $course_id, $section_id) {
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