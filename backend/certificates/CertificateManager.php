<?php
/**
 * CertificateManager.php
 * 
 * Handles certificate management functions including:
 * - Certificate verification
 * - Certificate retrieval
 * - Certificate status updates
 */

namespace Learnix\Certificates;

class CertificateManager {
    /**
     * Database connection
     */
    private $db;
    
    /**
     * Certificate Generator instance
     */
    private $certificateGenerator;
    
    /**
     * Path to stored certificates
     */
    private $certificatesDir = '../uploads/certificates/';
    
    /**
     * Constructor
     * 
     * @param object $db Database connection object
     * @param CertificateGenerator $certificateGenerator Certificate generator instance
     */
    public function __construct($db, $certificateGenerator = null) {
        $this->db = $db;
        $this->certificateGenerator = $certificateGenerator;
    }
    
    /**
     * Verify certificate authenticity by hash
     * 
     * @param string $certificateHash Certificate hash/verification code
     * @return array Certificate details or error message
     */
    public function verifyCertificate($certificateHash) {
        $stmt = $this->db->prepare("
            SELECT 
                c.certificate_id,
                c.enrollment_id,
                c.issue_date,
                c.certificate_hash,
                c.status,
                e.user_id,
                e.course_id,
                CONCAT(u.first_name, ' ', u.last_name) AS student_name,
                co.title AS course_title
            FROM certificates c
            JOIN enrollments e ON c.enrollment_id = e.enrollment_id
            JOIN users u ON e.user_id = u.user_id
            JOIN courses co ON e.course_id = co.course_id
            WHERE c.certificate_hash = ?
        ");
        
        $stmt->bind_param("s", $certificateHash);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $certificate = $result->fetch_assoc();
            
            // Update verification status if not already verified
            if ($certificate['status'] == 'Generated') {
                $this->updateCertificateStatus($certificate['certificate_id'], 'Verified');
            }
            
            return [
                'success' => true,
                'certificate' => $certificate
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Certificate not found or invalid.'
        ];
    }
    
    /**
     * Get certificate by ID
     * 
     * @param int $certificateId Certificate ID
     * @return array|false Certificate details or false if not found
     */
    public function getCertificate($certificateId) {
        $stmt = $this->db->prepare("
            SELECT 
                c.certificate_id,
                c.enrollment_id,
                c.issue_date,
                c.certificate_hash,
                c.status,
                e.user_id,
                e.course_id,
                u.first_name,
                u.last_name,
                co.title AS course_title
            FROM certificates c
            JOIN enrollments e ON c.enrollment_id = e.enrollment_id
            JOIN users u ON e.user_id = u.user_id
            JOIN courses co ON e.course_id = co.course_id
            WHERE c.certificate_id = ?
        ");
        
        $stmt->bind_param("i", $certificateId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return false;
    }
    
    /**
     * Get certificates for a user
     * 
     * @param int $userId User ID
     * @return array User's certificates
     */
    public function getUserCertificates($userId) {
        $stmt = $this->db->prepare("
            SELECT 
                c.certificate_id,
                c.issue_date,
                c.certificate_hash,
                c.status,
                co.course_id,
                co.title AS course_title
            FROM certificates c
            JOIN enrollments e ON c.enrollment_id = e.enrollment_id
            JOIN courses co ON e.course_id = co.course_id
            WHERE e.user_id = ?
            ORDER BY c.issue_date DESC
        ");
        
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $certificates = [];
        while ($row = $result->fetch_assoc()) {
            $certificates[] = $row;
        }
        
        return $certificates;
    }
    
    /**
     * Get certificates for a course
     * 
     * @param int $courseId Course ID
     * @return array Course certificates
     */
    public function getCourseCertificates($courseId) {
        $stmt = $this->db->prepare("
            SELECT 
                c.certificate_id,
                c.issue_date,
                c.certificate_hash,
                c.status,
                e.user_id,
                CONCAT(u.first_name, ' ', u.last_name) AS student_name
            FROM certificates c
            JOIN enrollments e ON c.enrollment_id = e.enrollment_id
            JOIN users u ON e.user_id = u.user_id
            WHERE e.course_id = ?
            ORDER BY c.issue_date DESC
        ");
        
        $stmt->bind_param("i", $courseId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $certificates = [];
        while ($row = $result->fetch_assoc()) {
            $certificates[] = $row;
        }
        
        return $certificates;
    }
    
    /**
     * Update certificate status
     * 
     * @param int $certificateId Certificate ID
     * @param string $status New status (Generated, Verified, Shared)
     * @return bool Success or failure
     */
    public function updateCertificateStatus($certificateId, $status) {
        $validStatuses = ['Generated', 'Verified', 'Shared'];
        
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        $stmt = $this->db->prepare("
            UPDATE certificates 
            SET status = ? 
            WHERE certificate_id = ?
        ");
        
        $stmt->bind_param("si", $status, $certificateId);
        return $stmt->execute();
    }
    
    /**
     * Generate a certificate for a course completion
     * 
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @return array Result of certificate generation
     */
    public function generateCourseCompletionCertificate($userId, $courseId) {
        // First check if the user has completed the course
        $stmt = $this->db->prepare("
            SELECT e.enrollment_id, e.completion_percentage, c.certificate_enabled
            FROM enrollments e
            JOIN courses c ON e.course_id = c.course_id
            WHERE e.user_id = ? AND e.course_id = ?
        ");
        
        $stmt->bind_param("ii", $userId, $courseId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            return [
                'success' => false,
                'message' => 'User is not enrolled in this course.'
            ];
        }
        
        $enrollment = $result->fetch_assoc();
        
        // Check if certificates are enabled for this course
        if ($enrollment['certificate_enabled'] != 1) {
            return [
                'success' => false,
                'message' => 'Certificates are not enabled for this course.'
            ];
        }
        
        // Check if the user has completed the course (>= 100%)
        if ($enrollment['completion_percentage'] < 100) {
            return [
                'success' => false,
                'message' => 'Course must be completed to generate a certificate.',
                'completion_percentage' => $enrollment['completion_percentage']
            ];
        }
        
        // Check if a certificate already exists
        $stmt = $this->db->prepare("
            SELECT certificate_id
            FROM certificates
            WHERE enrollment_id = ?
        ");
        
        $stmt->bind_param("i", $enrollment['enrollment_id']);
        $stmt->execute();
        $certificateResult = $stmt->get_result();
        
        if ($certificateResult->num_rows > 0) {
            $certificate = $certificateResult->fetch_assoc();
            
            return [
                'success' => true,
                'message' => 'Certificate already exists.',
                'certificate_id' => $certificate['certificate_id']
            ];
        }
        
        // Generate a new certificate
        if ($this->certificateGenerator) {
            return $this->certificateGenerator->generateCertificate(
                $userId,
                $courseId,
                $enrollment['enrollment_id']
            );
        } else {
            return [
                'success' => false,
                'message' => 'Certificate generator not available.'
            ];
        }
    }
    
    /**
     * Get the path to download a certificate
     * 
     * @param int $certificateId Certificate ID
     * @param int $userId User ID (for security)
     * @return array Success status and file path or error message
     */
    public function getCertificateDownloadPath($certificateId, $userId) {
        $stmt = $this->db->prepare("
            SELECT 
                c.certificate_id,
                c.certificate_hash,
                e.user_id
            FROM certificates c
            JOIN enrollments e ON c.enrollment_id = e.enrollment_id
            WHERE c.certificate_id = ?
        ");
        
        $stmt->bind_param("i", $certificateId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            return [
                'success' => false,
                'message' => 'Certificate not found.'
            ];
        }
        
        $certificate = $result->fetch_assoc();
        
        // Verify the user owns this certificate or is an admin
        if ($certificate['user_id'] != $userId) {
            // Check if user is admin (example logic, adapt to your system)
            $stmt = $this->db->prepare("SELECT role FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $userResult = $stmt->get_result();
            $user = $userResult->fetch_assoc();
            
            if ($user['role'] != 'admin') {
                return [
                    'success' => false,
                    'message' => 'You do not have permission to access this certificate.'
                ];
            }
        }
        
        // Construct the certificate filename
        $hash = $certificate['certificate_hash'];
        
        // Look for the certificate file
        $directory = $this->certificatesDir;
        $pattern = $directory . 'certificate_*_' . $certificate['certificate_id'] . '.pdf';
        $files = glob($pattern);
        
        if (empty($files)) {
            return [
                'success' => false,
                'message' => 'Certificate file not found.'
            ];
        }
        
        // Return the first matching file (should only be one)
        return [
            'success' => true,
            'file_path' => $files[0]
        ];
    }
    
    /**
     * Increment the download count for a certificate
     * 
     * @param int $certificateId Certificate ID
     * @return bool Success
     */
    public function incrementDownloadCount($certificateId) {
        $stmt = $this->db->prepare("
            UPDATE certificates 
            SET download_count = download_count + 1 
            WHERE certificate_id = ?
        ");
        
        $stmt->bind_param("i", $certificateId);
        return $stmt->execute();
    }
}