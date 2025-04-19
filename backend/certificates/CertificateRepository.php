<?php
/**
 * CertificateRepository.php
 * 
 * Handles database operations related to certificates
 */

namespace Learnix\Certificates;

class CertificateRepository {
    /**
     * Database connection
     */
    private $db;
    
    /**
     * Constructor
     * 
     * @param object $db Database connection object
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Create a new certificate record
     * 
     * @param array $data Certificate data
     * @return int|false Certificate ID or false on failure
     */
    public function createCertificate($data) {
        $sql = "INSERT INTO certificates (
                    enrollment_id, 
                    issue_date, 
                    certificate_hash, 
                    template_id, 
                    status
                ) VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        
        $status = isset($data['status']) ? $data['status'] : 'Generated';
        $templateId = isset($data['template_id']) ? $data['template_id'] : 1;
        
        $stmt->bind_param(
            "issis", 
            $data['enrollment_id'], 
            $data['issue_date'], 
            $data['certificate_hash'], 
            $templateId,
            $status
        );
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        
        return false;
    }
    
    /**
     * Get certificate by ID
     * 
     * @param int $certificateId Certificate ID
     * @return array|false Certificate data or false if not found
     */
    public function getCertificateById($certificateId) {
        $sql = "SELECT 
                    c.*, 
                    e.user_id, 
                    e.course_id,
                    u.first_name,
                    u.last_name,
                    CONCAT(u.first_name, ' ', u.last_name) AS student_name,
                    co.title AS course_title
                FROM certificates c
                JOIN enrollments e ON c.enrollment_id = e.enrollment_id
                JOIN users u ON e.user_id = u.user_id
                JOIN courses co ON e.course_id = co.course_id
                WHERE c.certificate_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $certificateId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return false;
    }
    
    /**
     * Get certificate by hash
     * 
     * @param string $hash Certificate hash/verification code
     * @return array|false Certificate data or false if not found
     */
    public function getCertificateByHash($hash) {
        $sql = "SELECT 
                    c.*, 
                    e.user_id, 
                    e.course_id,
                    u.first_name,
                    u.last_name,
                    CONCAT(u.first_name, ' ', u.last_name) AS student_name,
                    co.title AS course_title
                FROM certificates c
                JOIN enrollments e ON c.enrollment_id = e.enrollment_id
                JOIN users u ON e.user_id = u.user_id
                JOIN courses co ON e.course_id = co.course_id
                WHERE c.certificate_hash = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $hash);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return false;
    }
    
    /**
     * Get certificates for a specific user
     * 
     * @param int $userId User ID
     * @return array User certificates
     */
    public function getUserCertificates($userId) {
        $sql = "SELECT 
                    c.*, 
                    co.course_id,
                    co.title AS course_title
                FROM certificates c
                JOIN enrollments e ON c.enrollment_id = e.enrollment_id
                JOIN courses co ON e.course_id = co.course_id
                WHERE e.user_id = ?
                ORDER BY c.issue_date DESC";
        
        $stmt = $this->db->prepare($sql);
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
     * Get certificates for a specific course
     * 
     * @param int $courseId Course ID
     * @return array Course certificates
     */
    public function getCourseCertificates($courseId) {
        $sql = "SELECT 
                    c.*, 
                    e.user_id,
                    CONCAT(u.first_name, ' ', u.last_name) AS student_name
                FROM certificates c
                JOIN enrollments e ON c.enrollment_id = e.enrollment_id
                JOIN users u ON e.user_id = u.user_id
                WHERE e.course_id = ?
                ORDER BY c.issue_date DESC";
        
        $stmt = $this->db->prepare($sql);
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
     * Check if a certificate exists for an enrollment
     * 
     * @param int $enrollmentId Enrollment ID
     * @return bool|array False if no certificate exists, or certificate data
     */
    public function getCertificateByEnrollment($enrollmentId) {
        $sql = "SELECT * FROM certificates WHERE enrollment_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $enrollmentId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return false;
    }
    
    /**
     * Update certificate status
     * 
     * @param int $certificateId Certificate ID
     * @param string $status New status
     * @return bool Success or failure
     */
    public function updateStatus($certificateId, $status) {
        $sql = "UPDATE certificates SET status = ? WHERE certificate_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $status, $certificateId);
        
        return $stmt->execute();
    }
    
    /**
     * Increment download count for a certificate
     * 
     * @param int $certificateId Certificate ID
     * @return bool Success or failure
     */
    public function incrementDownloadCount($certificateId) {
        $sql = "UPDATE certificates SET download_count = download_count + 1 WHERE certificate_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $certificateId);
        
        return $stmt->execute();
    }
    
    /**
     * Delete a certificate
     * 
     * @param int $certificateId Certificate ID
     * @return bool Success or failure
     */
    public function deleteCertificate($certificateId) {
        $sql = "DELETE FROM certificates WHERE certificate_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $certificateId);
        
        return $stmt->execute();
    }
}