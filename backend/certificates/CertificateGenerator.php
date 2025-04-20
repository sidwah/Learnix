<?php

/**
 * CertificateGenerator.php
 * 
 * Handles the generation of course completion certificates including:
 * - QR code generation for certificate verification
 * - HTML template processing
 * - Certificate storage
 */

namespace Learnix\Certificates;

require_once __DIR__ . '/../../vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class CertificateGenerator
{
    /**
     * Base URL for certificate verification
     */
    private $verifyBaseUrl = 'http://localhost:8888/Learnix/verify-certificate.php?code=';

    /**
     * Path to certificate template
     */
    private $templatePath = __DIR__ . '/../../templates/certificates/certificate.html';

    /**
     * Path to favicon
     */
    private $faviconPath = '../assets/img/favicon/favicon.ico';

    /**
     * Directory to store generated certificates
     */
    private $certificatesDir = '../uploads/certificates/';

    /**
     * Directory to store QR codes
     */
    private $qrCodesDir = '../uploads/certificates/qrcodes/';

    /**
     * Database connection
     */
    private $db;

    /**
     * Constructor
     */
    public function __construct($db)
    {
        $this->db = $db;

        // Ensure certificate directories exist
        $this->ensureDirectoriesExist();
    }

    /**
     * Create necessary directories if they don't exist
     */
    private function ensureDirectoriesExist()
    {
        // Create certificates directory
        if (!is_dir($this->certificatesDir)) {
            if (!mkdir($this->certificatesDir, 0755, true)) {
                error_log("Failed to create certificates directory: {$this->certificatesDir}");
            } else {
                error_log("Created certificates directory: {$this->certificatesDir}");
            }
        }

        // Create QR codes directory
        if (!is_dir($this->qrCodesDir)) {
            if (!mkdir($this->qrCodesDir, 0755, true)) {
                error_log("Failed to create QR codes directory: {$this->qrCodesDir}");
            } else {
                error_log("Created QR codes directory: {$this->qrCodesDir}");
            }
        }

        // Create templates directory if needed
        $templatesDir = dirname($this->templatePath);
        if (!is_dir($templatesDir)) {
            if (!mkdir($templatesDir, 0755, true)) {
                error_log("Failed to create templates directory: {$templatesDir}");
            } else {
                error_log("Created templates directory: {$templatesDir}");
            }
        }

        // Create default template if it doesn't exist
        if (!file_exists($this->templatePath)) {
            $this->createDefaultTemplate();
        }
    }

    /**
     * Create a default certificate template
     */
    private function createDefaultTemplate()
    {
        $defaultTemplate = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Course Completion Certificate</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
            color: #333;
            background-color: #fff;
        }
        .certificate-container {
            width: 100%;
            height: 100%;
            padding: 20px;
            box-sizing: border-box;
            position: relative;
        }
        .certificate {
            width: 100%;
            height: 100%;
            padding: 30px;
            box-sizing: border-box;
            border: 15px solid #3a66db;
            border-radius: 10px;
            background-color: #fff;
            position: relative;
        }
        .certificate-header {
            margin-bottom: 30px;
        }
        .logo {
            max-height: 60px;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 36px;
            margin-bottom: 20px;
            color: #3a66db;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .certificate-title {
            font-size: 24px;
            margin-bottom: 30px;
            color: #555;
        }
        .student-name {
            font-size: 32px;
            margin: 30px 0;
            font-weight: bold;
            color: #333;
        }
        .course-info {
            margin: 30px 0;
        }
        .course-title {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
            color: #3a66db;
        }
        .completion-date {
            font-size: 18px;
            margin: 20px 0;
            color: #555;
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
            padding: 0 50px;
        }
        .signature {
            text-align: center;
            min-width: 200px;
        }
        .signature-line {
            width: 100%;
            border-top: 1px solid #333;
            margin-bottom: 10px;
        }
        .verification-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .verification-code {
            font-size: 12px;
            text-align: left;
            color: #777;
        }
        .qr-code {
            text-align: right;
        }
        .qr-code img {
            max-width: 100px;
            height: auto;
        }
        .certificate-footer {
            margin-top: 30px;
            font-size: 14px;
            color: #777;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(58, 102, 219, 0.05);
            white-space: nowrap;
            z-index: 0;
        }
        .content {
            position: relative;
            z-index: 1;
        }

        /* Media print styles for nice printing */
        @media print {
            body {
                margin: 0;
                padding: 0;
                background-color: #fff;
            }
            .certificate-container {
                width: 100%;
                height: 100%;
                page-break-inside: avoid;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="certificate">
            <div class="watermark">LEARNIX CERTIFICATE</div>
            
            <div class="content">
                <div class="certificate-header">
                    <img src="{{favicon_path}}" alt="Learnix Logo" class="logo">
                    <h1>Certificate of Completion</h1>
                    <div class="certificate-title">This is to certify that</div>
                </div>
                
                <div class="student-name">{{student_name}}</div>
                
                <div class="certificate-title">has successfully completed the course</div>
                
                <div class="course-info">
                    <div class="course-title">{{course_title}}</div>
                    <div class="completion-date">Completed on: {{completion_date}}</div>
                </div>
                
                <div class="signature-section">
                    <div class="signature">
                        <div class="signature-line"></div>
                        <div>Course Instructor</div>
                    </div>
                    
                    <div class="signature">
                        <div class="signature-line"></div>
                        <div>Learnix Director</div>
                    </div>
                </div>
                
                <div class="verification-section">
                    <div class="verification-code">
                        Verification Code: {{verification_code}}<br>
                        Verify at: {{verification_url}}
                    </div>
                    
                    <div class="qr-code">
                        <img src="{{qr_code_path}}" alt="Verification QR Code">
                    </div>
                </div>
                
                <div class="certificate-footer">
                    Issued on {{issue_date}} • Certificate ID: {{course_id}}-{{verification_code}}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
HTML;

        if (file_put_contents($this->templatePath, $defaultTemplate)) {
            error_log("Created default certificate template at: {$this->templatePath}");
        } else {
            error_log("Failed to create default certificate template at: {$this->templatePath}");
        }
    }

    /**
     * Generate a certificate for a user who completed a course
     */
    public function generateCertificate($userId, $courseId, $enrollmentId)
    {
        try {
            // Get user and course information
            $user = $this->getUserInfo($userId);
            $course = $this->getCourseInfo($courseId);
            $enrollment = $this->getEnrollmentInfo($enrollmentId);

            if (!$user || !$course || !$enrollment) {
                error_log("Failed to get user, course, or enrollment info for certificate generation");
                return [
                    'success' => false,
                    'message' => 'Could not retrieve user or course information'
                ];
            }

            // Generate verification code
            $verificationCode = $this->generateVerificationCode();
            error_log("Generated verification code: {$verificationCode}");

            // Generate QR code and save it permanently
            $qrCodeFileName = 'qr_' . $userId . '_' . $courseId . '_' . time() . '.png';
            $qrCodePath = $this->qrCodesDir . $qrCodeFileName;
            $qrCodeUrl = 'uploads/certificates/qrcodes/' . $qrCodeFileName; // Relative URL for embedding

            if (!$this->generateQRCode($verificationCode, $qrCodePath)) {
                error_log("Failed to generate QR code at: {$qrCodePath}");
                return [
                    'success' => false,
                    'message' => 'Failed to generate QR code'
                ];
            }

            // Create certificate HTML from template
            $certificateHtml = $this->prepareTemplateHtml($user, $course, $enrollment, $verificationCode, $qrCodeUrl);

            // Save HTML certificate file
            $htmlFileName = 'certificate_' . preg_replace('/[^a-z0-9]/', '_', strtolower($user['username'])) . '_' . $courseId . '_' . time() . '.html';
            $htmlPath = $this->certificatesDir . $htmlFileName;

            if (!file_put_contents($htmlPath, $certificateHtml)) {
                error_log("Failed to save HTML certificate to: {$htmlPath}");
                return [
                    'success' => false,
                    'message' => 'Failed to save certificate HTML'
                ];
            }

            error_log("Saved HTML certificate to: {$htmlPath}");

            // Save certificate information to database
            $certificateId = $this->saveCertificateInfo($userId, $courseId, $enrollmentId, $verificationCode, $htmlPath);
            error_log("Saved certificate in database with ID: {$certificateId}");

        // Insert notification for certificate earned
$notificationSql = "
INSERT INTO user_notifications (user_id, title, type, message, created_at, is_read)
VALUES (?, ?, ?, ?, NOW(), 0)
";
$notificationStmt = $this->db->prepare($notificationSql);

$title = 'Certificate Earned'; // ✅ New Title
$type = 'Certificate Awarded';
$message = 'Congratulations! You have successfully earned a certificate for the course "' . $course['title'] . '". You can view or verify your certificate anytime.';

$notificationStmt->bind_param("isss", $userId, $title, $type, $message); // ✅ 4 fields bound here

if (!$notificationStmt->execute()) {
error_log("Failed to insert certificate notification: " . $this->db->error);
}

$notificationStmt->close();



            return [
                'success' => true,
                'certificate_id' => $certificateId,
                'html_path' => $htmlPath,
                'verification_code' => $verificationCode
            ];
        } catch (\Exception $e) {
            error_log("Certificate generation error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'Error generating certificate: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate QR code for certificate verification
     */
    private function generateQRCode($verificationCode, $outputPath)
    {
        try {
            // Create the verification URL
            $verificationUrl = $this->verifyBaseUrl . $verificationCode;
            error_log("QR code verification URL: {$verificationUrl}");

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
            $qrcode->render($verificationUrl, $outputPath);

            if (!file_exists($outputPath)) {
                throw new \Exception("QR code file not created");
            }

            error_log("Generated QR code at: {$outputPath}");
            return true;
        } catch (\Exception $e) {
            error_log("QR code generation error: " . $e->getMessage());

            // Emergency fallback: create a simple text-based QR code
            return $this->createFallbackQRCode($verificationCode, $outputPath);
        }
    }

    /**
     * Create a fallback QR code using PHP's GD library
     */
    private function createFallbackQRCode($verificationCode, $outputPath)
    {
        try {
            // Create a GD image (white background with text)
            $img = imagecreatetruecolor(200, 200);
            $white = imagecolorallocate($img, 255, 255, 255);
            $black = imagecolorallocate($img, 0, 0, 0);

            // Fill with white
            imagefill($img, 0, 0, $white);

            // Add text
            $text = "Verification Code:";
            $code = $verificationCode;
            $url = "Verify at:";
            $verifyUrl = $this->verifyBaseUrl . $verificationCode;

            imagestring($img, 4, 20, 60, $text, $black);
            imagestring($img, 5, 20, 80, $code, $black);
            imagestring($img, 4, 20, 120, $url, $black);
            imagestring($img, 3, 20, 140, $verifyUrl, $black);

            // Draw a border
            imagerectangle($img, 0, 0, 199, 199, $black);

            // Save the image
            imagepng($img, $outputPath);
            imagedestroy($img);

            error_log("Created fallback QR code at: {$outputPath}");
            return file_exists($outputPath);
        } catch (\Exception $e) {
            error_log("Fallback QR code generation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Prepare certificate HTML from template
     */
    private function prepareTemplateHtml($user, $course, $enrollment, $verificationCode, $qrCodeUrl)
    {
        // Check if template exists
        if (!file_exists($this->templatePath)) {
            error_log("Certificate template not found at: {$this->templatePath}");
            $this->createDefaultTemplate();
        }

        // Read the certificate template
        $template = file_get_contents($this->templatePath);
        if (!$template) {
            error_log("Failed to read certificate template");
            throw new \Exception("Failed to read certificate template");
        }

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
            '{{verification_url}}' => $this->verifyBaseUrl . $verificationCode,
            '{{favicon_path}}' => $this->faviconPath,
            '{{qr_code_path}}' => $qrCodeUrl
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Get user information from database
     */
    private function getUserInfo($userId)
    {
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
     */
    private function getCourseInfo($courseId)
    {
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
     */
    private function getEnrollmentInfo($enrollmentId)
    {
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
     */
    private function generateVerificationCode()
    {
        $timestamp = time();
        $randomStr = bin2hex(random_bytes(4));
        return strtoupper($randomStr . dechex($timestamp));
    }

    /**
     * Save certificate information to database
     */
    private function saveCertificateInfo($userId, $courseId, $enrollmentId, $verificationCode, $htmlPath)
    {
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
