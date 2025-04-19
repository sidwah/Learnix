<?php
/**
 * Certificate Verification Page
 * 
 * This page allows anyone to verify the authenticity of a certificate
 * by entering the verification code or accessing directly via URL.
 */

require_once 'vendor/autoload.php';
require_once 'backend/config.php';
require_once 'backend/certificates/CertificateRepository.php';

use Learnix\Certificates\CertificateRepository;


$certificateRepo = new CertificateRepository($conn);
$verified = false;
$certificate = null;
$errorMessage = '';

// Check if verification code is provided in URL
if (isset($_GET['code']) && !empty($_GET['code'])) {
    $verificationCode = $_GET['code'];
    $certificate = $certificateRepo->getCertificateByHash($verificationCode);
    
    if ($certificate) {
        $verified = true;
        // Update status to verified if not already
        if ($certificate['status'] == 'Generated') {
            $certificateRepo->updateStatus($certificate['certificate_id'], 'Verified');
        }
    } else {
        $errorMessage = 'Certificate not found. Please check the verification code and try again.';
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verification_code'])) {
    $verificationCode = $_POST['verification_code'];
    
    if (empty($verificationCode)) {
        $errorMessage = 'Please enter a verification code.';
    } else {
        $certificate = $certificateRepo->getCertificateByHash($verificationCode);
        
        if ($certificate) {
            $verified = true;
            // Update status to verified if not already
            if ($certificate['status'] == 'Generated') {
                $certificateRepo->updateStatus($certificate['certificate_id'], 'Verified');
            }
        } else {
            $errorMessage = 'Certificate not found. Please check the verification code and try again.';
        }
    }
}

// Function to format date
function formatDate($dateString) {
    $date = new DateTime($dateString);
    return $date->format('F j, Y');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Certificate - Learnix</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .verification-container {
            max-width: 900px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }
        .verification-form {
            max-width: 500px;
            margin: 0 auto 30px;
        }
        .certificate-details {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            margin-top: 30px;
        }
        .certificate-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .certificate-icon {
            width: 60px;
            height: 60px;
            background-color: #28a745;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        .certificate-icon i {
            color: white;
            font-size: 30px;
        }
        .certificate-title {
            flex: 1;
        }
        .certificate-title h3 {
            margin: 0;
            color: #28a745;
        }
        .certificate-title p {
            margin: 5px 0 0;
            color: #6c757d;
        }
        .info-item {
            margin-bottom: 15px;
        }
        .info-label {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 5px;
        }
        .info-value {
            font-weight: 500;
            font-size: 16px;
        }
        .verification-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            background-color: #28a745;
            color: white;
            font-weight: 500;
            font-size: 14px;
            margin-top: 20px;
        }
        .validation-seal {
            text-align: center;
            margin-top: 30px;
        }
        .seal-image {
            width: 120px;
            height: 120px;
            margin: 0 auto;
            background: radial-gradient(circle, #f8d775 0%, #f0b429 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: bold;
            font-size: 18px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            position: relative;
        }
        .seal-inner {
            width: 80%;
            height: 80%;
            border-radius: 50%;
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #0056D2;
            font-size: 30px;
            border: 2px solid #f0b429;
        }
        .error-message {
            color: #dc3545;
            margin-top: 10px;
        }
        @media (max-width: 768px) {
            .certificate-header {
                flex-direction: column;
                text-align: center;
            }
            .certificate-icon {
                margin-right: 0;
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="verification-container">
            <h2 class="text-center mb-4">Certificate Verification</h2>
            
            <?php if (!$verified): ?>
                <div class="verification-form">
                    <p class="text-center mb-4">Enter the verification code to validate the authenticity of a Learnix certificate.</p>
                    
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="verification_code" class="form-label">Verification Code</label>
                            <input type="text" class="form-control" id="verification_code" name="verification_code" placeholder="Enter verification code">
                            <?php if (!empty($errorMessage)): ?>
                                <div class="error-message"><?php echo $errorMessage; ?></div>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Verify Certificate</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="certificate-details">
                    <div class="certificate-header">
                        <div class="certificate-icon">
                            <i class="bi bi-check-lg">✓</i>
                        </div>
                        <div class="certificate-title">
                            <h3>Certificate Verified</h3>
                            <p>This certificate has been verified as authentic.</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-label">RECIPIENT</div>
                                <div class="info-value"><?php echo htmlspecialchars($certificate['student_name']); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-label">CERTIFICATE ID</div>
                                <div class="info-value"><?php echo htmlspecialchars($certificate['certificate_hash']); ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-label">COURSE</div>
                                <div class="info-value"><?php echo htmlspecialchars($certificate['course_title']); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-label">ISSUE DATE</div>
                                <div class="info-value"><?php echo formatDate($certificate['issue_date']); ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="verification-badge">
                        <i class="bi bi-shield-check me-1">🛡️</i> Verified
                    </div>
                </div>
                
                <div class="validation-seal">
                    <div class="seal-image">
                        <div class="seal-inner">LRX</div>
                    </div>
                    <p class="mt-2">Official Learnix Certificate</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>