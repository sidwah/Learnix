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
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
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

// Construct file path using certificate hash for uniqueness
$certificateHash = $certificate['certificate_hash'];
$username = strtolower(preg_replace('/[^a-z0-9]/', '_', $certificate['student_name']));
$filename = "certificate_{$username}_{$certificateId}.pdf";
// In download-certificate.php, use a path relative to the document root:
    $filePath = __DIR__ . "/uploads/certificates/{$filename}";
// Check if file exists
if (!file_exists($filePath)) {
    // Certificate PDF doesn't exist - this should not happen normally
    // But we could regenerate it here or show an error
    die("Certificate file not found. Please contact support.");
}

// Increment download count
$certificateRepo->incrementDownloadCount($certificateId);

// Set headers for file download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output file data
readfile($filePath);
exit;