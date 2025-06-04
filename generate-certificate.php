<?php
/**
 * Generate Certificate Script
 * 
 * This script can be called to generate a certificate for a user
 * who has completed a course. Can be triggered by system or user action.
 */

// Start or resume session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Not logged in, redirect to login page
    header('Location: index.php');
    exit;
}

require_once 'vendor/autoload.php';
require_once 'backend/config.php';
require_once 'backend/certificates/CertificateGenerator.php';
require_once 'backend/certificates/CertificateManager.php';

use Learnix\Certificates\CertificateGenerator;
use Learnix\Certificates\CertificateManager;

// Get course ID from request
if (!isset($_GET['course_id']) || !is_numeric($_GET['course_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid course ID.']);
    exit;
}

$courseId = (int)$_GET['course_id'];
$userId = $_SESSION['user_id'];

// Initialize certificate classes
$certificateGenerator = new CertificateGenerator($conn);
$certificateManager = new CertificateManager($conn, $certificateGenerator);

// Generate the certificate
$result = $certificateManager->generateCourseCompletionCertificate($userId, $courseId);

// Return result as JSON
header('Location: student/my-certifications.php');
echo json_encode($result);
exit;