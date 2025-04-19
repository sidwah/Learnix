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
        
        // Verify 100% completion
        $completion = $this->verifyCompletion($enrollment_id, $course_id);
        if (!$completion['is_completed']) {
            return [
                'success' => false,
                'message' => 'Course not fully completed',
                'percentage' => $completion['percentage']
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
     * Verify if course is 100% completed
     */
    private function verifyCompletion($enrollment_id, $course_id) {
        $stmt = $this->conn->prepare("SELECT completion_percentage FROM enrollments WHERE enrollment_id = ? AND course_id = ?");
        $stmt->bind_param("ii", $enrollment_id, $course_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $enrollment = $result->fetch_assoc();
            $percentage = $enrollment['completion_percentage'];
            
            return [
                'is_completed' => $percentage >= 100,
                'percentage' => $percentage
            ];
        }
        
        return ['is_completed' => false, 'percentage' => 0];
    }
}