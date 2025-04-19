<?php
/**
 * CertificateGenerator.php
 * 
 * Handles the generation of course completion certificates including:
 * - QR code generation for certificate verification
 * - HTML template processing
 * - PDF conversion and storage
 */

namespace Learnix\Certificates;

require_once __DIR__ . '/../../vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Mpdf\Mpdf;

class CertificateGenerator {
    /**
     * Base URL for certificate verification
     */
    private $verifyBaseUrl = 'http://localhost:8888/Learnix/verify/';
    
    /**
     * Path to certificate template
     */
// Update the template path in CertificateGenerator.php
private $templatePath = __DIR__ . '/../../templates/certificates/certificate.html';    
    /**
     * Path to favicon
     */
    private $faviconPath = 'assets/img/favicon/favicon.ico';
    
    /**
     * Directory to store generated certificates
     */
    private $certificatesDir = '../uploads/certificates/';
    
    /**
     * Directory to store temporary QR codes
     */
    private $qrCodesDir = '../uploads/certificates/qrcodes/';
    
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
        
        // Ensure certificate directories exist
        if (!is_dir($this->certificatesDir)) {
            mkdir($this->certificatesDir, 0755, true);
        }
        
        if (!is_dir($this->qrCodesDir)) {
            mkdir($this->qrCodesDir, 0755, true);
        }
    }
    
    /**
     * Generate a certificate for a user who completed a course
     * 
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @param int $enrollmentId Enrollment ID
     * @return array Certificate details or error message
     */
    public function generateCertificate($userId, $courseId, $enrollmentId) {
        try {
            // Get user and course information
            $user = $this->getUserInfo($userId);
            $course = $this->getCourseInfo($courseId);
            $enrollment = $this->getEnrollmentInfo($enrollmentId);
            
            if (!$user || !$course || !$enrollment) {
                return [
                    'success' => false,
                    'message' => 'Could not retrieve user or course information'
                ];
            }
            
            // Generate verification code
            $verificationCode = $this->generateVerificationCode();
            
            // Generate QR code
            $qrCodePath = $this->generateQRCode($verificationCode);
            
            // Create certificate HTML from template
            $certificateHtml = $this->prepareTemplateHtml($user, $course, $enrollment, $verificationCode, $qrCodePath);
            
            // Convert HTML to PDF
            $pdfPath = $this->convertToPdf($certificateHtml, $user['username'], $courseId);
            
            // Save certificate information to database
            $certificateId = $this->saveCertificateInfo($userId, $courseId, $enrollmentId, $verificationCode, $pdfPath);
            
            // Clean up temporary QR code file
            if (file_exists($qrCodePath)) {
                unlink($qrCodePath);
            }
            
            return [
                'success' => true,
                'certificate_id' => $certificateId,
                'pdf_path' => $pdfPath,
                'verification_code' => $verificationCode
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error generating certificate: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get user information from database
     * 
     * @param int $userId User ID
     * @return array|false User information or false on failure
     */
    private function getUserInfo($userId) {
        $stmt = $this->db->prepare("SELECT user_id, first_name, last_name, username, email FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // Add full name for convenience
            $user['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
            return $user;
        }
        
        return false;
    }
    
    /**
     * Get course information from database
     * 
     * @param int $courseId Course ID
     * @return array|false Course information or false on failure
     */
    private function getCourseInfo($courseId) {
        $stmt = $this->db->prepare("
            SELECT c.course_id, c.title, c.instructor_id, 
                   CONCAT(u.first_name, ' ', u.last_name) AS instructor_name
            FROM courses c
            JOIN instructors i ON c.instructor_id = i.instructor_id
            JOIN users u ON i.user_id = u.user_id
            WHERE c.course_id = ?
        ");
        $stmt->bind_param("i", $courseId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return false;
    }
    
    /**
     * Get enrollment information from database
     * 
     * @param int $enrollmentId Enrollment ID
     * @return array|false Enrollment information or false on failure
     */
    private function getEnrollmentInfo($enrollmentId) {
        $stmt = $this->db->prepare("
            SELECT enrollment_id, user_id, course_id, enrolled_at, completion_percentage
            FROM enrollments
            WHERE enrollment_id = ?
        ");
        $stmt->bind_param("i", $enrollmentId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return false;
    }
    
    /**
     * Generate a unique verification code
     * 
     * @return string Verification code
     */
    private function generateVerificationCode() {
        // Generate a unique code (combination of random string and timestamp)
        $timestamp = time();
        $randomStr = bin2hex(random_bytes(4));
        return strtoupper($randomStr . dechex($timestamp));
    }
    
    /**
     * Generate QR code for certificate verification
     * 
     * @param string $verificationCode Verification code
     * @return string Path to generated QR code image
     */
    private function generateQRCode($verificationCode) {
        // Create the verification URL
        $verificationUrl = $this->verifyBaseUrl . $verificationCode;
        
        // Set QR code options
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_L,
            'scale' => 5,
            'imageBase64' => false,
            'imageTransparent' => false,
        ]);
        
        // Generate QR code
        $qrcode = new QRCode($options);
        
        // Save QR code to file
        $qrCodePath = $this->qrCodesDir . 'qr_' . $verificationCode . '.png';
        $qrcode->render($verificationUrl, $qrCodePath);
        
        return $qrCodePath;
    }
    
    /**
     * Prepare certificate HTML from template
     * 
     * @param array $user User information
     * @param array $course Course information
     * @param array $enrollment Enrollment information
     * @param string $verificationCode Verification code
     * @param string $qrCodePath Path to QR code image
     * @return string Certificate HTML
     */
    private function prepareTemplateHtml($user, $course, $enrollment, $verificationCode, $qrCodePath) {
        // Read the certificate template
        $template = file_get_contents($this->templatePath);
        
        // Current date for certificate issuance
        $currentDate = date('F j, Y');
        
        // Calculate completion date - either now or from the database
        $completionDate = isset($enrollment['completion_date']) 
            ? date('F j, Y', strtotime($enrollment['completion_date'])) 
            : $currentDate;
        
        // Replace placeholders with actual data
        $replacements = [
            '{{student_name}}' => $user['full_name'],
            '{{course_title}}' => $course['title'],
            '{{course_id}}' => $course['course_id'],
            '{{completion_date}}' => $completionDate,
            '{{issue_date}}' => $currentDate,
            '{{verification_code}}' => $verificationCode,
            '{{favicon_path}}' => $this->faviconPath,
            '{{qr_code_path}}' => $qrCodePath
        ];
        
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
    
    /**
     * Convert HTML to PDF
     * 
     * @param string $html Certificate HTML
     * @param string $username Username for the filename
     * @param int $courseId Course ID for the filename
     * @return string Path to generated PDF file
     */
    private function convertToPdf($html, $username, $courseId) {
        // Create PDF filename based on username and course ID
        $filename = 'certificate_' . preg_replace('/[^a-z0-9]/', '_', strtolower($username)) . '_' . $courseId . '_' . time() . '.pdf';
        $pdfPath = $this->certificatesDir . $filename;
        
        // Initialize mPDF
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L', // Landscape orientation
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'margin_header' => 0,
            'margin_footer' => 0
        ]);
        
        // Add the HTML content
        $mpdf->WriteHTML($html);
        
        // Save as PDF
        $mpdf->Output($pdfPath, 'F');
        
        return $pdfPath;
    }
    
    /**
     * Save certificate information to database
     * 
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @param int $enrollmentId Enrollment ID
     * @param string $verificationCode Verification code
     * @param string $pdfPath Path to PDF file
     * @return int Certificate ID
     */
    private function saveCertificateInfo($userId, $courseId, $enrollmentId, $verificationCode, $pdfPath) {
        $stmt = $this->db->prepare("
            INSERT INTO certificates (
                enrollment_id, 
                issue_date, 
                certificate_hash, 
                template_id, 
                status
            ) VALUES (?, NOW(), ?, 1, 'Generated')
        ");
        
        $stmt->bind_param("is", $enrollmentId, $verificationCode);
        $stmt->execute();
        
        return $this->db->insert_id;
    }
}