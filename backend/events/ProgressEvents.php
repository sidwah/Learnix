<?php
// backend/events/ProgressEvents.php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../progress/BadgeQualificationService.php';

class ProgressEvents {
    private $qualificationService;
    
    public function __construct() {
        $this->qualificationService = new BadgeQualificationService();
    }
    
    /**
     * Handle topic completion event
     * 
     * @param int $user_id User ID
     * @param int $enrollment_id Enrollment ID
     * @param int $topic_id Topic ID
     * @param int $course_id Course ID
     * @return array Results of badge evaluations
     */
    public function onTopicCompleted($user_id, $enrollment_id, $topic_id, $course_id) {
        $results = [];
        
        // Update learning streak for the user
        $streak_days = $this->qualificationService->updateLearningStreak($user_id);
        
        // Check if this completes a section
        $section_completion = $this->qualificationService->checkSectionCompletion($enrollment_id, $topic_id);
        if ($section_completion) {
            $results['section_badge'] = $this->qualificationService->evaluateEvent(
                $section_completion['event_type'],
                $section_completion
            );
        }
        
        // Check if this completes the course
        $course_completion = $this->qualificationService->checkCourseCompletion($enrollment_id, $course_id);
        if ($course_completion) {
            $results['course_badge'] = $this->qualificationService->evaluateEvent(
                $course_completion['event_type'],
                $course_completion
            );
        }
        
        // Check for streak achievements
        $streak_event = $this->qualificationService->checkLearningStreak($user_id);
        if ($streak_event) {
            $results['streak_badge'] = $this->qualificationService->evaluateEvent(
                $streak_event['event_type'],
                $streak_event
            );
        }
        
        return $results;
    }
    
    /**
     * Handle quiz completion event
     * 
     * @param int $user_id User ID
     * @param int $course_id Course ID
     * @param int $quiz_id Quiz ID
     * @param float $score Quiz score (percentage)
     * @param bool $passed Whether quiz was passed
     * @return array Results of badge evaluations
     */
    public function onQuizCompleted($user_id, $course_id, $quiz_id, $score, $passed) {
        $results = [];
        
        // Update learning streak
        $streak_days = $this->qualificationService->updateLearningStreak($user_id);
        
        // Check for quiz excellence achievement
        $quiz_event = $this->qualificationService->checkQuizExcellence($user_id, $course_id, $quiz_id, $score);
        if ($quiz_event) {
            $results['quiz_badge'] = $this->qualificationService->evaluateEvent(
                $quiz_event['event_type'],
                $quiz_event
            );
        }
        
        return $results;
    }
}