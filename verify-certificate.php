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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --primary: #0056D2;
            --primary-light: #3B82F6;
            --primary-dark: #1E40AF;
            --accent: #14B8A6;
            --success: #10B981;
            --warning: #F59E0B;
            --error: #EF4444;
            --dark: #1F2937;
            --light: #F9FAFB;
            --gray: #6B7280;
            --gray-light: #E5E7EB;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px 0;
        }
        
        .page-wrapper {
            width: 100%;
        }
        
        .verification-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            background-color: #fff;
            position: relative;
            overflow: hidden;
        }
        
        .verification-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }
        
        .page-title {
            text-align: center;
            margin-bottom: 30px;
            color: var(--dark);
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            position: relative;
            padding-bottom: 12px;
        }
        
        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            border-radius: 3px;
        }
        
        .verification-form {
            max-width: 500px;
            margin: 0 auto 30px;
            background-color: var(--light);
            padding: 30px;
            border-radius: 12px;
            border: 1px solid var(--gray-light);
            transition: all 0.3s ease;
        }
        
        .verification-form:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            transform: translateY(-5px);
        }
        
        .form-intro {
            text-align: center;
            margin-bottom: 25px;
            color: var(--gray);
            font-size: 1rem;
            line-height: 1.6;
        }
        
        .form-control {
            border-radius: 8px;
            border: 2px solid var(--gray-light);
            padding: 12px 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        
        .form-label {
            color: var(--dark);
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 500;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.6s;
            z-index: -1;
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 86, 210, 0.15);
        }
        
        .certificate-details {
            background-color: var(--light);
            border-radius: 12px;
            padding: 35px;
            margin-top: 30px;
            border: 1px solid var(--gray-light);
            position: relative;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .certificate-details::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--success), var(--accent));
        }
        
        .certificate-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px dashed var(--gray-light);
        }
        
        .certificate-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--success), #34D399);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
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
            color: var(--success);
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .certificate-title p {
            margin: 8px 0 0;
            color: var(--gray);
            font-size: 1rem;
        }
        
        .info-row {
            margin-bottom: 25px;
        }
        
        .info-item {
            margin-bottom: 20px;
            transition: all 0.3s;
            padding: 15px;
            border-radius: 8px;
        }
        
        .info-item:hover {
            background-color: rgba(229, 231, 235, 0.4);
        }
        
        .info-label {
            font-size: 0.85rem;
            color: var(--gray);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            font-weight: 500;
        }
        
        .info-value {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--dark);
        }
        
        .verification-badge {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 30px;
            background-color: var(--success);
            color: white;
            font-weight: 500;
            font-size: 0.95rem;
            margin-top: 20px;
            box-shadow: 0 3px 8px rgba(16, 185, 129, 0.2);
            transition: all 0.3s;
        }
        
        .verification-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(16, 185, 129, 0.3);
        }
        
        .verification-badge i {
            margin-right: 6px;
            font-size: 1.1rem;
        }
        
        .validation-seal {
            text-align: center;
            margin-top: 40px;
            position: relative;
            padding: 20px 0;
        }
        
        .validation-seal::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--gray-light), transparent);
        }
        
        .seal-circle {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: radial-gradient(circle, #fef3c7, #facc15);
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: inset 0 0 10px #d4af37, 0 8px 20px rgba(0, 0, 0, 0.1);
            border: 5px double #bfa52f;
            font-family: 'Georgia', serif;
            margin: 30px auto;
            animation: pulse 2s infinite ease-in-out;
            position: relative;
            z-index: 1;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .seal-border {
            width: 90%;
            height: 90%;
            border-radius: 50%;
            border: 2px dashed #7c6a0a;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
        }
        
        .seal-border::before {
            content: '';
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            border-radius: 50%;
            border: 1px solid rgba(124, 106, 10, 0.3);
        }
        
        .seal-inner-text .seal-top,
        .seal-inner-text .seal-bottom {
            margin: 3px; 
            font-size: 10px;
            text-transform: uppercase;
            color: #4c4304;
            font-weight: bold;
            letter-spacing: 1px;
        }
        
        .seal-icon {
            font-size: 36px;
            margin: 8px 0;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        
        .verification-success-text {
            text-align: center;
            margin-top: 15px;
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            color: var(--dark);
            font-weight: 600;
        }
        
        .error-message {
            color: var(--error);
            margin-top: 10px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
        }
        
        .error-message::before {
            content: '⚠️';
            margin-right: 6px;
        }
        
        .signature-line {
            margin-top: 30px;
            text-align: center;
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .wave-decoration {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: linear-gradient(-45deg, transparent 33.33%, var(--gray-light) 33.33%, var(--gray-light) 66.66%, transparent 66.66%);
            background-size: 20px 20px;
            opacity: 0.6;
        }
        
        /* Responsive styles */
        @media (max-width: 768px) {
            .verification-container {
                padding: 30px 20px;
            }
            
            .certificate-header {
                flex-direction: column;
                text-align: center;
            }
            
            .certificate-icon {
                margin-right: 0;
                margin-bottom: 15px;
            }
            
            .info-item {
                text-align: center;
            }
        }
        
        /* Animation for verified state */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .certificate-details, .validation-seal {
            animation: fadeIn 0.6s ease-out forwards;
        }
        
        .validation-seal {
            animation-delay: 0.3s;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="container">
            <div class="verification-container">
                <div class="wave-decoration"></div>
                <h2 class="page-title">Certificate Verification</h2>
                
                <?php if (!$verified): ?>
                    <div class="verification-form">
                        <p class="form-intro">Enter the verification code to validate the authenticity of a Learnix certificate.</p>
                        
                        <form method="post" action="">
                            <div class="mb-4">
                                <label for="verification_code" class="form-label">Verification Code</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="text" class="form-control" id="verification_code" name="verification_code" placeholder="Enter your verification code">
                                </div>
                                <?php if (!empty($errorMessage)): ?>
                                    <div class="error-message"><?php echo $errorMessage; ?></div>
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-check-circle me-2"></i>Verify Certificate
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="certificate-details">
                        <div class="certificate-header">
                            <div class="certificate-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="certificate-title">
                                <h3>Certificate Verified</h3>
                                <p>This certificate has been verified as authentic and valid.</p>
                            </div>
                        </div>
                        
                        <div class="row info-row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-user me-2"></i>Recipient
                                    </div>
                                    <div class="info-value"><?php echo htmlspecialchars($certificate['student_name']); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-fingerprint me-2"></i>Certificate ID
                                    </div>
                                    <div class="info-value"><?php echo htmlspecialchars($certificate['certificate_hash']); ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row info-row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-book-open me-2"></i>Course
                                    </div>
                                    <div class="info-value"><?php echo htmlspecialchars($certificate['course_title']); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-calendar-alt me-2"></i>Issue Date
                                    </div>
                                    <div class="info-value"><?php echo formatDate($certificate['issue_date']); ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="verification-badge">
                            <i class="fas fa-shield-alt"></i> Verified Certificate
                        </div>
                    </div>
                    
                    <div class="validation-seal">
                        <div class="seal-circle">
                            <div class="seal-border">
                                <div class="seal-inner-text">
                                    <div class="seal-top">Learnix Certificate</div>
                                    <div class="seal-icon">🎓</div>
                                    <div class="seal-bottom">Authentic</div>
                                </div>
                            </div>
                        </div>
                        <p class="verification-success-text">Official Learnix Certificate</p>
                        <div class="signature-line">Digitally verified on <?php echo date('F j, Y'); ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add some subtle interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Highlight form fields on focus
            const formInputs = document.querySelectorAll('.form-control');
            formInputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('focused');
                });
            });
            
            // Add subtle hover effects to info items
            const infoItems = document.querySelectorAll('.info-item');
            infoItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px)';
                });
                item.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>