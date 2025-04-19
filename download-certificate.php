<?php
/**
 * Certificate Download Script
 * 
 * This script allows users to download their certificates
 * after authentication and verification.
 */

// Start or resume session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Not logged in, redirect to login page
    header('Location: index.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

require_once 'vendor/autoload.php';
require_once 'backend/config.php';
require_once 'backend/certificates/CertificateRepository.php';

use Learnix\Certificates\CertificateRepository;

// Get certificate ID from request
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid certificate ID.");
}

$certificateId = (int)$_GET['id'];
$userId = $_SESSION['user_id'];

// Initialize repository
$certificateRepo = new CertificateRepository($conn);

// Get certificate details
$certificate = $certificateRepo->getCertificateById($certificateId);

if (!$certificate) {
    die("Certificate not found.");
}

// Security check: Make sure the user owns this certificate or is an admin
if ($certificate['user_id'] != $userId) {
    // Check if user is admin
    $isAdmin = false;
    
    // Query to check if user is admin
    $stmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($user['role'] == 'admin') {
            $isAdmin = true;
        }
    }
    
    // If not admin and not certificate owner, deny access
    if (!$isAdmin) {
        die("You do not have permission to download this certificate.");
    }
}

// Try multiple approaches to find the certificate file
$possiblePaths = [];
$certificateDir = __DIR__ . "/uploads/certificates/";
$courseId = $certificate['course_id'];
$username = strtolower(preg_replace('/[^a-z0-9]/', '_', $certificate['student_name']));
$userId = $certificate['user_id'];

// Add all possible filename patterns based on your existing files
$possiblePaths[] = "{$certificateDir}certificate_{$username}_{$courseId}_*.pdf";
$possiblePaths[] = "{$certificateDir}certificate_{$userId}_{$username}_{$courseId}_*.pdf";
$possiblePaths[] = "{$certificateDir}certificate_{$username}_{$userId}_{$courseId}_*.pdf";

// Try direct certificate_id match
$possiblePaths[] = "{$certificateDir}certificate_*_{$certificateId}.pdf";
$possiblePaths[] = "{$certificateDir}certificate_*_{$certificateId}_*.pdf";

$filePath = null;

// Try each pattern with glob to find matching files
foreach ($possiblePaths as $pattern) {
    $matches = glob($pattern);
    if (!empty($matches)) {
        // Use the most recent file if multiple matches exist
        usort($matches, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        $filePath = $matches[0];
        break;
    }
}

// Last resort: Look for any certificate files related to this user
if (!$filePath) {
    $lastResortPattern = "{$certificateDir}certificate_*{$username}*_{$courseId}_*.pdf";
    $matches = glob($lastResortPattern);
    if (!empty($matches)) {
        $filePath = $matches[0];
    }
}

// If still no file found, give a detailed error
if (!$filePath) {
    die("Certificate file not found. Patterns tried:<br>" . 
        implode("<br>", $possiblePaths) . 
        "<br><br>Please contact support with this information.");
}

// Increment download count
$certificateRepo->incrementDownloadCount($certificateId);

// Set headers for file download
header('Content-Type: application/pdf');
$downloadFilename = "Certificate_" . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $certificate['course_title']) . ".pdf";
header('Content-Disposition: attachment; filename="' . $downloadFilename . '"');header('Content-Length: ' . filesize($filePath));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output file data
readfile($filePath);
exit;