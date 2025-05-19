<?php
/**
 * Certificate Verification Page
 * 
 * This page allows verification of certificate authenticity with a design resembling a traditional certificate.
 */

require_once 'vendor/autoload.php';
require_once 'backend/config.php';
require_once 'backend/certificates/CertificateRepository.php';

use Learnix\Certificates\CertificateRepository;

$certificateRepo = new CertificateRepository($conn);
$verified = false;
$certificate = null;
$errorMessage = '';

if (isset($_GET['code']) && !empty($_GET['code'])) {
    $verificationCode = $_GET['code'];
    $certificate = $certificateRepo->getCertificateByHash($verificationCode); // Fixed method name
    
    if ($certificate) {
        $verified = true;
        if ($certificate['status'] == 'Generated') {
            $certificateRepo->updateStatus($certificate['certificate_id'], 'Verified');
        }
    } else {
        $errorMessage = 'Certificate not found. Please check the verification code and try again.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verification_code'])) {
    $verificationCode = $_POST['verification_code'];
    
    if (empty($verificationCode)) {
        $errorMessage = 'Please enter a verification code.';
    } else {
        $certificate = $certificateRepo->getCertificateByHash($verificationCode);
        
        if ($certificate) {
            $verified = true;
            if ($certificate['status'] == 'Generated') {
                $certificateRepo->updateStatus($certificate['certificate_id'], 'Verified');
            }
        } else {
            $errorMessage = 'Certificate not found. Please check the verification code and try again.';
        }
    }
}

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
    <title>Certificate Verification - Learnix</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:wght@400;700&family=Roboto+Slab:wght@400;700&display=swap">
    <style>
        :root {
            --primary: #2C4A7E;
            --accent: #D4A017;
            --success: #1A936F;
            --error: #DC3545;
            --dark: #2D2D2D;
            --light: #F8F1E9;
            --border: #8B5A2B;
        }

        body {
            font-family: 'Roboto Slab', serif;
            background: linear-gradient(to bottom, #E8D8C3, #F8F1E9);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .certificate-container {
            min-width: 900px;
            background: #FFF;
            border: 10px double var(--border);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            padding: 40px;
            position: relative;
            overflow: hidden;
            background-image: url('data:image/svg+xml,%3Csvg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"%3E%3Cpath d="M10 10h80v80H10z" stroke="%238B5A2B" stroke-width="1" fill="none" opacity="0.1"/%3E%3C/svg%3E');
            background-repeat: repeat;
        }

        .certificate-container::before,
        .certificate-container::after {
            content: '';
            position: absolute;
            background: var(--accent);
            opacity: 0.2;
        }

        .certificate-container::before {
            top: 0;
            left: 0;
            right: 0;
            height: 10px;
        }

        .certificate-container::after {
            bottom: 0;
            left: 0;
            right: 0;
            height: 10px;
        }

        .page-title {
            font-family: 'Great Vibes', cursive;
            font-size: 3.5rem;
            color: var(--primary);
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .certificate-border {
            border: 4px double var(--border);
            padding: 30px;
            border-radius: 10px;
            background: var(--light);
            position: relative;
        }

        .certificate-border::before {
            content: 'Certificate of Verification';
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--light);
            padding: 0 20px;
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            color: var(--primary);
        }

        .verification-form {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            border: 2px solid var(--border);
        }

        .form-intro {
            text-align: center;
            font-family: 'Playfair Display', serif;
            color: var(--dark);
            margin-bottom: 20px;
        }

        .form-control {
            border: 2px solid var(--border);
            border-radius: 5px;
            padding: 10px;
            font-family: 'Roboto Slab', serif;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 5px rgba(44, 74, 126, 0.3);
        }

        .btn-primary {
            background: var(--primary);
            border: none;
            border-radius: 5px;
            padding: 12px;
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background: #1e3a5f; /* Darkened primary color */
            transform: translateY(-2px);
        }

        .certificate-details {
            text-align: center;
            padding: 30px;
            border: 3px double var(--border);
            border-radius: 10px;
            background: var(--light);
            position: relative;
        }

        .certificate-header {
            margin-bottom: 30px;
        }

        .certificate-title h3 {
            font-family: 'Great Vibes', cursive;
            font-size: 2.5rem;
            color: var(--success);
            margin-bottom: 10px;
        }

        .certificate-title p {
            font-family: 'Playfair Display', serif;
            color: var(--dark);
        }

        .info-item {
            margin-bottom: 20px;
        }

        .info-label {
            font-family: 'Playfair Display', serif;
            font-size: 1rem;
            color: var(--border);
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .info-value {
            font-family: 'Roboto Slab', serif;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--dark);
        }

        .verification-badge {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            border-radius: 20px;
            background: var(--success);
            color: #FFF;
            font-family: 'Playfair Display', serif;
            margin: 20px auto;
        }

        .validation-seal {
            text-align: center;
            margin-top: 40px;
        }

        .seal-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: radial-gradient(circle, #FFD700, #DAA520);
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 4px double var(--border);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .seal-icon {
            font-size: 2rem;
            color: var(--dark);
        }

        .verification-success-text {
            font-family: 'Great Vibes', cursive;
            font-size: 1.8rem;
            color: var(--primary);
            margin-top: 20px;
        }

        .signature-line {
            margin-top: 30px;
            font-family: 'Playfair Display', serif;
            color: var(--dark);
            border-top: 2px solid var(--border);
            padding-top: 10px;
            display: inline-block;
        }

        .error-message {
            color: var(--error);
            font-family: 'Roboto Slab', serif;
            text-align: center;
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            .certificate-container {
                padding: 20px;
            }

            .page-title {
                font-size: 2.5rem;
            }

            .certificate-border::before {
                font-size: 1rem;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .certificate-details, .validation-seal {
            animation: fadeIn 0.6s ease-out;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <h2 class="page-title">Certificate Verification</h2>
        <div class="certificate-border">
            <?php if (!$verified): ?>
                <div class="verification-form">
                    <p class="form-intro">Verify the authenticity of a Learnix certificate.</p>
                    <form method="post" action="">
                        <div class="mb-3">
                            <input type="text" class="form-control" id="verification_code" name="verification_code" placeholder="Enter Verification Code">
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
                        <div class="certificate-title">
                            <h3>Certificate of Achievement</h3>
                            <p>This is to certify that</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Recipient</div>
                        <div class="info-value"><?php echo htmlspecialchars($certificate['student_name']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Has Successfully Completed</div>
                        <div class="info-value"><?php echo htmlspecialchars($certificate['course_title']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Certificate ID</div>
                        <div class="info-value"><?php echo htmlspecialchars($certificate['certificate_hash']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Issue Date</div>
                        <div class="info-value"><?php echo formatDate($certificate['issue_date']); ?></div>
                    </div>
                    <div class="verification-badge">
                        <i class="fas fa-shield-alt me-2"></i> Verified Authentic
                    </div>
                    <div class="validation-seal">
                        <div class="seal-circle">
                            <span class="seal-icon">🎓</span>
                        </div>
                        <p class="verification-success-text">Official Seal of Verification</p>
                        <div class="signature-line">Verified on <?php echo date('F j, Y'); ?></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formInput = document.querySelector('.form-control');
            if (formInput) {
                formInput.addEventListener('focus', () => formInput.style.borderColor = 'var(--primary)');
                formInput.addEventListener('blur', () => formInput.style.borderColor = 'var(--border)');
            }
        });
    </script>
</body>
</html>