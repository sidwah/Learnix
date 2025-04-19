<?php
/**
 * Certificate Display/Download Script
 * 
 * This script displays the HTML certificate for users to print or screenshot.
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
if ($certificate['user_id'] != $userId && $_SESSION['role'] != 'admin') {
    die("You do not have permission to access this certificate.");
}

// Increment download/view count
$certificateRepo->incrementDownloadCount($certificateId);

// Find the HTML certificate file
$certificateDirPath = __DIR__ . "/uploads/certificates/";

// Get username in the format used in filenames
// From CertificateGenerator.php we know it uses: preg_replace('/[^a-z0-9]/', '_', strtolower($user['username']))
$username = '';
if (isset($certificate['username'])) {
    // If username is available directly
    $username = preg_replace('/[^a-z0-9]/', '_', strtolower($certificate['username']));
} else if (isset($certificate['student_name'])) {
    // Try to extract from student name as fallback (though this is less reliable)
    $username = preg_replace('/[^a-z0-9]/', '_', strtolower($certificate['student_name']));
}

$courseId = $certificate['course_id'];

// Debug info
error_log("Looking for certificate files with pattern: username={$username}, courseId={$courseId}");

// Try multiple search patterns
$htmlFiles = [];

// Pattern 1: Standard pattern from observed filenames
if (!empty($username)) {
    $htmlFiles = glob("{$certificateDirPath}certificate_{$username}_{$courseId}_*.html");
    error_log("Pattern 1 results: " . count($htmlFiles) . " files found");
}

// Pattern 2: Username might have spaces replaced with underscores
if (empty($htmlFiles) && !empty($username)) {
    $altUsername = str_replace(' ', '_', $username);
    $htmlFiles = glob("{$certificateDirPath}certificate_{$altUsername}_{$courseId}_*.html");
    error_log("Pattern 2 results: " . count($htmlFiles) . " files found");
}

// Pattern 3: More general pattern - just course ID (use when username might be completely different)
if (empty($htmlFiles)) {
    $htmlFiles = glob("{$certificateDirPath}certificate_*_{$courseId}_*.html");
    error_log("Pattern 3 results: " . count($htmlFiles) . " files found");
}

// Pattern 4: Emergency fallback - just "certificate_" prefix
if (empty($htmlFiles)) {
    $htmlFiles = glob("{$certificateDirPath}certificate_*.html");
    error_log("Pattern 4 results: " . count($htmlFiles) . " files found");
}

// Check all certificate files if we have to
if (empty($htmlFiles)) {
    $allCertFiles = glob("{$certificateDirPath}*.html");
    error_log("Found " . count($allCertFiles) . " total HTML files in certificate directory");
    
    if (!empty($allCertFiles)) {
        // Let's list all the files for debugging
        error_log("All certificate files: " . implode(", ", $allCertFiles));
    } else {
        error_log("No HTML files found in certificate directory: {$certificateDirPath}");
    }
    
    die("Certificate file not found. Please contact support with reference: Cert#{$certificateId}");
}

// Get the most recent HTML file
usort($htmlFiles, function($a, $b) {
    return filemtime($b) - filemtime($a);
});
$htmlFile = $htmlFiles[0];
error_log("Selected certificate file: {$htmlFile}");

// Create a nicer filename for download reference
$certificateTitle = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $certificate['course_title']);

// Read the HTML file
$html = file_get_contents($htmlFile);
if ($html === false) {
    error_log("Failed to read certificate file: {$htmlFile}");
    die("Error reading certificate file. Please contact support.");
}

// Create a completely new document structure with the certificate embedded
$certificateContent = $html;

// Create a new document with our controls properly positioned above
$document = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Completion</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        .page-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
        }
        .controls-container {
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .certificate-container {
            width: 100%;
            margin: 0 auto;
            padding: 0;
        }
        .print-btn {
            background-color: #3a66db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
            font-weight: bold;
        }
        .back-btn {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .tip {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 0;
        }
        
        /* Print styles */
        @media print {
            .no-print {
                display: none !important;
            }
            .page-container {
                display: block;
            }
            .certificate-container {
                margin: 0;
                padding: 0;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <!-- Controls Container -->
        <div class="controls-container no-print">
            <h3 style="margin-top: 0;">Your Certificate is Ready!</h3>
            <p>To save your certificate, use one of these options:</p>
            <div style="margin: 15px 0;">
                <button onclick="window.print();" class="print-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" style="vertical-align: text-bottom; margin-right: 5px;" viewBox="0 0 16 16">
                        <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2H5zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1z"/>
                        <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2V7zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                    </svg>
                    Print Certificate
                </button>
                <button onclick="window.location.href='student/my-certifications.php';" class="back-btn">
                    Back to Certificates
                </button>
            </div>
            <p class="tip">Tip: For best results, disable headers and footers in your browser's print settings.</p>
        </div>
        
        <!-- Certificate Container -->
        <div class="certificate-container">
            $certificateContent
        </div>
    </div>
</body>
</html>
HTML;

// Output the new HTML document
echo $document;
exit;