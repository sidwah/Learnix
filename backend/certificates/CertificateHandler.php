<?php
// backend/certificates/CertificateHandler.php
require_once __DIR__ . '/CertificateGenerator.php';
require_once __DIR__ . '/../config.php';

use Learnix\Certificates\CertificateGenerator;

class CertificateHandler {
    private $conn;
    private $certificateGenerator;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
        $this->certificateGenerator = new CertificateGenerator($conn);
    }
    
    /**
     * Check if user is eligible for a certificate and generate it if eligible
     * 
     * @param int $enrollment_id Enrollment ID
     * @param int $course_id Course ID
     * @param int $user_id User ID
     * @return array Result information with success flag
     */
    public function generateCertificateIfEligible($enrollment_id, $course_id, $user_id) {
        // Check if certificate already exists
        $existing = $this->getCertificate($enrollment_id);
        if ($existing) {
            return [
                'success' => false,
                'message' => 'Certificate already exists',
                'certificate_id' => $existing['certificate_id']
            ];
        }
        
        // First, check if all requirements are met
        $requirements_met = $this->checkCertificationRequirements($user_id, $course_id);
        
        if (!$requirements_met) {
            return [
                'success' => false,
                'message' => 'Not all course requirements have been met for certificate'
            ];
        }
        
        // Verify the course allows certificates
        $certificateEnabled = $this->isCertificateEnabled($course_id);
        if (!$certificateEnabled) {
            return [
                'success' => false,
                'message' => 'Certificates are not enabled for this course'
            ];
        }
        
        // Generate certificate
        $result = $this->certificateGenerator->generateCertificate($user_id, $course_id, $enrollment_id);
        $certificate_id = $result['success'] ? $result['certificate_id'] : false;        
        if (!$certificate_id) {
            return [
                'success' => false,
                'message' => 'Failed to generate certificate'
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Certificate generated successfully',
            'certificate_id' => $certificate_id
        ];
    }
    
    /**
     * Check if all certification requirements are met
     * 
     * @param int $user_id User ID
     * @param int $course_id Course ID
     * @return bool True if all requirements are met
     */
    private function checkCertificationRequirements($user_id, $course_id) {
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
     * Get existing certificate for an enrollment
     */
    private function getCertificate($enrollment_id) {
        $stmt = $this->conn->prepare("SELECT * FROM certificates WHERE enrollment_id = ?");
        $stmt->bind_param("i", $enrollment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Check if certificates are enabled for a course
     */
    private function isCertificateEnabled($course_id) {
        $stmt = $this->conn->prepare("SELECT certificate_enabled FROM courses WHERE course_id = ?");
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $course = $result->fetch_assoc();
            return $course['certificate_enabled'] == 1;
        }
        
        return false;
    }
}