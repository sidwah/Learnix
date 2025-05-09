<!DOCTYPE html>
<html lang="en" dir="">

<head>
    <!-- Required Meta Tags Always Come First -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Title -->
    <title>Department Portal Sign in - Learnix</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="../favicon.ico">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet">

    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="../assets/css/vendor.min.css">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/font/bootstrap-icons.css">

    <!-- CSS Learnix Template -->
    <link rel="stylesheet" href="../assets/css/theme.minc619.css?v=1.0">

    <style>
        /* Toast container */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        
        /* Toast styling */
        .toast {
            background-color: white;
            min-width: 300px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            /* border-left: 4px solid; */
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }
        
        .toast.show {
            opacity: 1;
        }
        
       
        
        .toast-body {
            padding: 12px 15px;
        }
        
        .toast-icon {
            font-size: 1.25rem;
            margin-right: 10px;
        }
        
        .toast-icon.success {
            color: #198754;
        }
        
        .toast-icon.error {
            color: #dc3545;
        }
    </style>
</head>

<body>
    <!-- Toast Container -->
    <div class="toast-container"></div>
    
    <!-- ========== HEADER ========== -->
    <header id="header" class="navbar navbar-expand-lg navbar-end navbar-absolute-top navbar-light navbar-show-hide" data-hs-header-options='{
            "fixMoment": 1000,
            "fixEffect": "slide"
          }'>
        <div class="container">
            <nav class="js-mega-menu navbar-nav-wrap">
                <!-- Default Logo -->
                <a class="navbar-brand" href="../" aria-label="Learnix">
                    <img class="navbar-brand-logo" src="../assets/svg/logos/logo.svg" alt="Logo">
                </a>
                <!-- End Default Logo -->

                <!-- Toggler -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-default">
                        <i class="bi-list"></i>
                    </span>
                    <span class="navbar-toggler-toggled">
                        <i class="bi-x"></i>
                    </span>
                </button>
                <!-- End Toggler -->

                <!-- Collapse -->
                <div class="collapse navbar-collapse" id="navbarNavDropdown">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link " href="../pages/">Home</a>
                        </li>
                        <!-- About Us -->
                        <li class="nav-item">
                            <a class="nav-link" href="../pages/about-us.php">About Us</a>
                        </li>
                        <!-- End About Us -->

                        <!-- Contact Us -->
                        <li class="nav-item">
                            <a class="nav-link" href="../pages/contact-us.php">Contact Us</a>
                        </li>
                        <!-- End Contact Us -->
                    </ul>
                </div>
                <!-- End Collapse -->
            </nav>
        </div>
    </header>
    <!-- ========== END HEADER ========== -->

    <!-- ========== MAIN CONTENT ========== -->
    <main id="content" role="main">
        <!-- Form -->
        <div class="container content-space-3 content-space-t-lg-4 content-space-b-lg-3">
            <div class="flex-grow-1 mx-auto" style="max-width: 28rem;">

                <!-- Heading -->
                <div class="text-center mb-5 mb-md-7">
                    <h1 class="h2 fw-bold">Department Portal</h1>
                    <p class="text-muted">Sign in to manage your department, courses, and faculty resources.</p>
                </div>
                <!-- End Heading -->

                <!-- Sign In Form -->
                <form class="js-validate needs-validation" novalidate>
                    <!-- Email Input -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="departmentSigninEmail">Email Address</label>
                        <input type="email" class="form-control form-control-lg" name="email" id="departmentSigninEmail" placeholder="Enter your email" aria-label="Enter your email" required>
                        <span class="invalid-feedback">Please enter a valid email address.</span>
                    </div>
                    <!-- End Email Input -->

                    <!-- Password Input -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label fw-semibold" for="departmentSigninPassword">Password</label>
                            <a class="form-label-link text-primary" href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Forgot Password?</a>
                        </div>

                        <div class="input-group input-group-merge">
                            <input type="password" class="js-toggle-password form-control form-control-lg" name="password" id="departmentSigninPassword" placeholder="Enter your password" aria-label="Enter your password" required minlength="8" data-hs-toggle-password-options='{
                "target": "#togglePassword",
                "defaultClass": "bi-eye-slash",
                "showClass": "bi-eye",
                "classChangeTarget": "#togglePasswordIcon"
            }'>
                            <a id="togglePassword" class="input-group-text" href="javascript:;">
                                <i id="togglePasswordIcon" class="bi-eye-slash"></i>
                            </a>
                        </div>

                        <span class="invalid-feedback">Your password must be at least 8 characters long.</span>
                    </div>
                    <!-- End Password Input -->

                    <!-- Submit Button -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Sign In</button>
                    </div>
                </form>
                <!-- End Sign In Form -->

                <!-- Forgot Password Modal -->
                <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold" id="forgotPasswordModalLabel">Reset Password</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center mb-4">
                                    <i class="bi bi-key-fill fs-1 text-primary"></i>
                                </div>
                                <p class="text-muted text-center">Enter your department email address, and we'll send you a password reset code.</p>
                                <form id="forgotPasswordForm">
                                    <div class="mb-3">
                                        <label for="forgotEmail" class="form-label">Email Address</label>
                                        <input type="email" class="form-control form-control-lg" id="forgotEmail" placeholder="Enter your email" required>
                                        <div class="invalid-feedback">Please enter a valid email address.</div>
                                    </div>
                                    <div class="d-grid mt-4">
                                        <button type="submit" class="btn btn-primary">Send Reset Code</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Forgot Password Modal -->

                <!-- Reset Password Verification Modal -->
                <div class="modal fade" id="resetVerificationModal" tabindex="-1" aria-labelledby="resetVerificationModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="resetVerificationModalLabel">Verify Reset Code</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center mb-4">
                                    <i class="bi bi-shield-check fs-1 text-primary"></i>
                                </div>
                                <p class="text-center">A verification code has been sent to your email. Please enter the 6-digit code below to continue.</p>
                                <form id="resetVerificationForm" class="needs-validation" novalidate>
                                    <div class="mb-3">
                                        <label class="form-label">Verification Code</label>
                                        <div class="d-flex justify-content-between gap-2">
                                            <input type="text" class="form-control form-control-lg text-center reset-verification-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                                            <input type="text" class="form-control form-control-lg text-center reset-verification-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                                            <input type="text" class="form-control form-control-lg text-center reset-verification-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                                            <input type="text" class="form-control form-control-lg text-center reset-verification-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                                            <input type="text" class="form-control form-control-lg text-center reset-verification-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                                            <input type="text" class="form-control form-control-lg text-center reset-verification-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                                        </div>
                                        <input type="hidden" id="resetVerificationCode" name="resetVerificationCode">
                                        <div class="invalid-feedback text-center mt-2">
                                            Please enter the complete 6-digit verification code.
                                        </div>
                                    </div>
                                    <div class="d-grid mt-4">
                                        <button type="submit" class="btn btn-primary">Verify Code</button>
                                    </div>
                                </form>
                                <div class="mt-4 text-center">
                                    <p>Didn't receive the code? <a href="#" id="resendResetCode" class="text-decoration-none">Resend code</a></p>
                                    <div class="d-flex align-items-center justify-content-center mt-2">
                                        <i class="bi bi-clock me-2 text-muted"></i>
                                        <p class="text-muted small mb-0">The code will expire in 10 minutes</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Reset Password Verification Modal -->

                <!-- New Password Modal -->
                <div class="modal fade" id="newPasswordModal" tabindex="-1" aria-labelledby="newPasswordModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="newPasswordModalLabel">Create New Password</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center mb-4">
                                    <i class="bi bi-lock-fill fs-1 text-primary"></i>
                                </div>
                                <p class="text-center">Please create a new password for your department account.</p>
                                <form id="newPasswordForm" class="needs-validation" novalidate>
                                    <div class="mb-3">
                                        <label for="newPassword" class="form-label">New Password</label>
                                        <div class="input-group input-group-merge">
                                            <input type="password" class="js-toggle-password form-control form-control-lg" id="newPassword" name="newPassword" placeholder="Enter new password" required minlength="8"
                                                data-hs-toggle-password-options='{
                                                "target": "#toggleNewPassword",
                                                "defaultClass": "bi-eye-slash",
                                                "showClass": "bi-eye",
                                                "classChangeTarget": "#toggleNewPasswordIcon"
                                            }'>
                                            <a id="toggleNewPassword" class="input-group-text" href="javascript:;">
                                                <i id="toggleNewPasswordIcon" class="bi-eye-slash"></i>
                                            </a>
                                        </div>
                                        <div class="invalid-feedback">Password must be at least 8 characters long.</div>
                                        <div class="form-text mt-2">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-info-circle me-2 text-primary"></i>
                                                <span>Password must be at least 8 characters long.</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label for="confirmPassword" class="form-label">Confirm Password</label>
                                        <div class="input-group input-group-merge">
                                            <input type="password" class="js-toggle-password form-control form-control-lg" id="confirmPassword" name="confirmPassword" placeholder="Confirm new password" required
                                                data-hs-toggle-password-options='{
                                                "target": "#toggleConfirmPassword",
                                                "defaultClass": "bi-eye-slash",
                                                "showClass": "bi-eye",
                                                "classChangeTarget": "#toggleConfirmPasswordIcon"
                                            }'>
                                            <a id="toggleConfirmPassword" class="input-group-text" href="javascript:;">
                                                <i id="toggleConfirmPasswordIcon" class="bi-eye-slash"></i>
                                            </a>
                                        </div>
                                        <div class="invalid-feedback">Passwords do not match.</div>
                                    </div>
                                    <div class="d-grid mt-4">
                                        <button type="submit" class="btn btn-primary">Reset Password</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End New Password Modal -->

                <!-- Verification Code Modal -->
                <div class="modal fade" id="verificationModal" tabindex="-1" aria-labelledby="verificationModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="verificationModalLabel">Two-Factor Authentication</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center mb-4">
                                    <i class="bi bi-shield-lock fs-1 text-primary"></i>
                                </div>
                                <p class="text-center">To secure your department account, a verification code has been sent to your email. Please enter the 6-digit code below.</p>
                                <form id="verificationForm" class="needs-validation" novalidate>
                                    <div class="mb-3">
                                        <label class="form-label">Verification Code</label>
                                        <div class="d-flex justify-content-between gap-2">
                                            <input type="text" class="form-control form-control-lg text-center verification-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                                            <input type="text" class="form-control form-control-lg text-center verification-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                                            <input type="text" class="form-control form-control-lg text-center verification-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                                            <input type="text" class="form-control form-control-lg text-center verification-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                                            <input type="text" class="form-control form-control-lg text-center verification-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                                            <input type="text" class="form-control form-control-lg text-center verification-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                                        </div>
                                        <input type="hidden" id="verificationCode" name="verificationCode">
                                        <div class="invalid-feedback text-center mt-2">
                                            Please enter the complete 6-digit verification code.
                                        </div>
                                    </div>
                                    <div class="d-grid mt-4">
                                        <button type="submit" class="btn btn-primary">Verify</button>
                                    </div>
                                </form>
                                <div class="mt-4 text-center">
                                    <p>Didn't receive the code? <a href="#" id="resendCode" class="text-decoration-none">Resend code</a></p>
                                    <div class="d-flex align-items-center justify-content-center mt-2">
                                        <i class="bi bi-clock me-2 text-muted"></i>
                                        <p class="text-muted small mb-0">The code will expire in 10 minutes</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Verification Code Modal -->

                <!-- Force Password Reset Modal -->
                <div class="modal fade" id="forcePasswordResetModal" tabindex="-1" aria-labelledby="forcePasswordResetModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="forcePasswordResetModalLabel">Password Update Required</h5>
                            </div>
                            <div class="modal-body">
                                <div class="text-center mb-4">
                                    <i class="bi bi-shield-lock-fill fs-1 text-primary"></i>
                                </div>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    For security reasons, you need to update your password before continuing.
                                </div>
                                <form id="forcePasswordResetForm" class="needs-validation" novalidate>
                                    <div class="mb-3">
                                        <label for="newForcePassword" class="form-label">New Password</label>
                                        <div class="input-group input-group-merge">
                                            <input type="password" class="js-toggle-password form-control form-control-lg" id="newForcePassword" name="newForcePassword" placeholder="Enter new password" required minlength="8"
                                                data-hs-toggle-password-options='{
                                "target": "#toggleNewForcePassword",
                                "defaultClass": "bi-eye-slash",
                                "showClass": "bi-eye",
                                "classChangeTarget": "#toggleNewForcePasswordIcon"
                            }'>
                                            <a id="toggleNewForcePassword" class="input-group-text" href="javascript:;">
                                                <i id="toggleNewForcePasswordIcon" class="bi-eye-slash"></i>
                                            </a>
                                        </div>
                                        <div class="invalid-feedback">Password must be at least 8 characters long.</div>
                                    </div>
                                    <div class="mb-4">
                                        <label for="confirmForcePassword" class="form-label">Confirm Password</label>
                                        <div class="input-group input-group-merge">
                                            <input type="password" class="js-toggle-password form-control form-control-lg" id="confirmForcePassword" name="confirmForcePassword" placeholder="Confirm new password" required
                                                data-hs-toggle-password-options='{
                                "target": "#toggleConfirmForcePassword",
                                "defaultClass": "bi-eye-slash",
                                "showClass": "bi-eye",
                                "classChangeTarget": "#toggleConfirmForcePasswordIcon"
                            }'>
                                            <a id="toggleConfirmForcePassword" class="input-group-text" href="javascript:;">
                                                <i id="toggleConfirmForcePasswordIcon" class="bi-eye-slash"></i>
                                            </a>
                                        </div>
                                        <div class="invalid-feedback">Passwords do not match.</div>
                                    </div>
                                    <div class="password-requirements mb-4">
                                        <p class="fw-semibold mb-2">Password requirements:</p>
                                        <ul class="list-unstyled">
                                            <li class="requirements-item">
                                                <i class="bi bi-check-circle text-success me-1"></i>
                                                At least 8 characters
                                            </li>
                                            <li class="requirements-item">
                                                <i class="bi bi-check-circle text-success me-1"></i>
                                                Combination of letters, numbers, and symbols recommended
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="d-grid mt-4">
                                        <button type="submit" class="btn btn-primary">Update Password</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Force Password Reset Modal -->
            </div>
        </div>
        <!-- End Form -->
    </main>
    <!-- ========== END MAIN CONTENT ========== -->

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Show toast notification function
            function showToast(type, message) {
                const toastContainer = document.querySelector('.toast-container');
                
                // Create toast element
                const toast = document.createElement('div');
                toast.className = `toast ${type === 'success' ? 'success' : 'error'}`;
                
                // Set toast content
                toast.innerHTML = `
                    <div class="toast-body d-flex align-items-center">
                        <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'} toast-icon ${type === 'success' ? 'success' : 'error'}"></i>
                        <div>${message}</div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                `;
                
                // Add toast to container
                toastContainer.appendChild(toast);
                
                // Show toast
                setTimeout(() => {
                    toast.classList.add('show');
                }, 100);
                
                // Auto-dismiss after 5 seconds
                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => {
                        if (toast.parentNode) {
                            toast.parentNode.removeChild(toast);
                        }
                    }, 300);
                }, 5000);
                
                // Add dismiss functionality to close button
                const closeBtn = toast.querySelector('.btn-close');
                closeBtn.addEventListener('click', function() {
                    toast.classList.remove('show');
                    setTimeout(() => {
                        if (toast.parentNode) {
                            toast.parentNode.removeChild(toast);
                        }
                    }, 300);
                });
            }

            // Create and apply page overlay for loading effect with optional message
            function createOverlay(message = null) {
                const overlay = document.createElement('div');
                overlay.id = 'pageOverlay';
                overlay.style.position = 'fixed';
                overlay.style.top = '0';
                overlay.style.left = '0';
                overlay.style.width = '100%';
                overlay.style.height = '100%';
                overlay.style.backgroundColor = 'rgba(255, 255, 255, 0.7)';
                overlay.style.backdropFilter = 'blur(5px)';
                overlay.style.zIndex = '9998';
                overlay.style.display = 'flex';
                overlay.style.flexDirection = 'column';
                overlay.style.justifyContent = 'center';
                overlay.style.alignItems = 'center';
                overlay.style.gap = '15px';

                // Add a loading spinner
                const spinner = document.createElement('div');
                spinner.className = 'spinner-border text-primary';
                spinner.setAttribute('role', 'status');
                spinner.style.width = '3rem';
                spinner.style.height = '3rem';
                spinner.innerHTML = '<span class="visually-hidden">Loading...</span>';
                overlay.appendChild(spinner);

                // Add message if provided
                if (message) {
                    const messageElement = document.createElement('div');
                    messageElement.className = 'fw-semibold fs-5 text-primary';
                    messageElement.textContent = message;
                    overlay.appendChild(messageElement);
                }

                document.body.appendChild(overlay);
            }

            // Remove overlay
            function removeOverlay() {
                const overlay = document.getElementById('pageOverlay');
                if (overlay) {
                    document.body.removeChild(overlay);
                }
            }
            
            // Store user email for verification process
            let currentUserEmail = '';

            // Store email for reset process
            let resetEmail = '';

            // Handle main sign-in form submission
            document.querySelector('.js-validate').addEventListener('submit', function(e) {
                e.preventDefault();

                let form = this;
                let formData = new FormData(form);
                let submitButton = form.querySelector("button[type='submit']");

                // Store email for verification process
                currentUserEmail = formData.get('email');

                // Disable button and show loading state
                submitButton.disabled = true;
                submitButton.textContent = "Signing in...";

                // Create overlay with custom message
                createOverlay('Verifying your credentials...');

                fetch('../backend/auth/department/signin.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    try {
                        let jsonData = JSON.parse(data);
                        if (jsonData.status === "success") {
                            if (jsonData.requirePasswordReset) {
                                removeOverlay();
                                submitButton.textContent = "Sign In";
                                submitButton.disabled = false;

                                // Show force password reset modal
                                const forcePasswordResetModal = new bootstrap.Modal(document.getElementById('forcePasswordResetModal'));
                                forcePasswordResetModal.show();

                                // Focus the first password input when modal is shown
                                setTimeout(() => {
                                    document.getElementById('newForcePassword').focus();
                                }, 500);
                            } else if (jsonData.requireVerification) {
                                removeOverlay();
                                submitButton.textContent = "Sign In";
                                submitButton.disabled = false;

                                // Show verification modal
                                const verificationModal = new bootstrap.Modal(document.getElementById('verificationModal'));
                                verificationModal.show();

                                // Focus the first digit input when modal is shown
                                setTimeout(() => {
                                    document.querySelector('.verification-digit').focus();

                                    // Add additional CSS for the digit inputs
                                    document.querySelectorAll('.verification-digit').forEach(input => {
                                        input.style.fontSize = '1.5rem';
                                        input.style.fontWeight = 'bold';
                                    });
                                }, 500);
                            } else {
                                submitButton.textContent = "Redirecting...";
                                showToast('success', "Welcome to the Department Portal! Redirecting to your dashboard...");

                                createOverlay('Welcome! Redirecting to your dashboard...');
                                setTimeout(() => {
                                    window.location.href = "index.php";
                                }, 2000);
                            }
                        } else {
                            removeOverlay();
                            showToast('error', jsonData.message);
                            submitButton.textContent = "Sign In";
                            submitButton.disabled = false;
                        }
                    } catch (error) {
                        console.error("JSON Parse Error:", error, data);
                        removeOverlay();
                        showToast('error', "Unexpected server response. Please try again.");
                        submitButton.textContent = "Sign In";
                        submitButton.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    removeOverlay();
                    showToast('error', "Network error. Please check your connection.");
                    submitButton.textContent = "Sign In";
                    submitButton.disabled = false;
                });
            });

            // Handle forgot password form submission
            document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const emailInput = document.getElementById('forgotEmail');
                resetEmail = emailInput.value.trim();

                // Validate email
                if (!resetEmail || !resetEmail.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                    emailInput.classList.add('is-invalid');
                    return;
                }

                let submitButton = this.querySelector("button[type='submit']");
                submitButton.disabled = true;
                submitButton.textContent = "Sending...";

                createOverlay('Sending reset code...');

                // Send request to generate reset code
                let formData = new FormData();
                formData.append('email', resetEmail);

                fetch('../backend/auth/department/forgot_password.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    removeOverlay();

                    if (data.status === "success") {
                        // Hide forgot password modal
                        bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal')).hide();

                        // Show verification modal
                        const resetVerificationModal = new bootstrap.Modal(document.getElementById('resetVerificationModal'));
                        resetVerificationModal.show();

                        // Focus first digit input
                        setTimeout(() => {
                            document.querySelector('.reset-verification-digit').focus();

                            // Style digit inputs
                            document.querySelectorAll('.reset-verification-digit').forEach(input => {
                                input.style.fontSize = '1.5rem';
                                input.style.fontWeight = 'bold';
                            });
                        }, 500);

                        showToast('success', "Reset code sent to your email");
                    } else {
                        showToast('error', data.message || "Failed to send reset code");
                        submitButton.textContent = "Send Reset Code";
                        submitButton.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Reset Code Error:', error);
                    removeOverlay();
                    showToast('error', "Network error. Please try again.");
                    submitButton.textContent = "Send Reset Code";
                    submitButton.disabled = false;
                });
            });

            // Setup reset verification digit inputs
            document.querySelectorAll('.reset-verification-digit').forEach((input, index) => {
                // Auto focus next input when a digit is entered
                input.addEventListener('input', function(e) {
                    // Only allow numbers
                    this.value = this.value.replace(/[^0-9]/g, '');

                    // If a digit was entered and there's a next input, focus it
                    if (this.value && index < 5) {
                        document.querySelectorAll('.reset-verification-digit')[index + 1].focus();
                    }

                    // Combine all digits into the hidden field
                    updateResetVerificationCode();
                });

                // Handle backspace - move to previous input
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && !this.value && index > 0) {
                        document.querySelectorAll('.reset-verification-digit')[index - 1].focus();
                    }
                });

                // Handle paste event across all inputs
                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const paste = (e.clipboardData || window.clipboardData).getData('text');

                    // Check if pasted content is a 6-digit number
                    if (/^\d{6}$/.test(paste)) {
                        // Distribute the digits across all inputs
                        document.querySelectorAll('.reset-verification-digit').forEach((digitInput, i) => {
                            digitInput.value = paste[i];
                        });
                        updateResetVerificationCode();
                    }
                });
            });

            // Helper function to update the hidden reset verification code field
            function updateResetVerificationCode() {
                const digits = Array.from(document.querySelectorAll('.reset-verification-digit')).map(input => input.value);
                document.getElementById('resetVerificationCode').value = digits.join('');
            }

            // Handle reset verification form submission
            document.getElementById('resetVerificationForm').addEventListener('submit', function(e) {
                e.preventDefault();

                // Combine all input values into verification code
                updateResetVerificationCode();

                let verificationCode = document.getElementById('resetVerificationCode').value;
                let submitButton = this.querySelector("button[type='submit']");

                // Validate code format
                if (!verificationCode.match(/^\d{6}$/)) {
                    document.querySelectorAll('.reset-verification-digit').forEach(input => {
                        input.classList.add('is-invalid');
                    });
                    return;
                }

                // Disable button and show loading state
                submitButton.disabled = true;
                submitButton.textContent = "Verifying...";

                // Create form data for verification
                let formData = new FormData();
                formData.append('email', resetEmail);
                formData.append('code', verificationCode);

                createOverlay('Verifying the code...');

                // Send verification request
                fetch('../backend/auth/department/verify_reset_code.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    removeOverlay();

                    if (data.status === "success") {
                        // Hide the verification modal
                        bootstrap.Modal.getInstance(document.getElementById('resetVerificationModal')).hide();

                        // Show the new password modal
                        const newPasswordModal = new bootstrap.Modal(document.getElementById('newPasswordModal'));
                        newPasswordModal.show();

                        showToast('success', "Verification successful! Please create a new password.");
                    } else {
                        showToast('error', data.message || "Invalid verification code");
                        submitButton.textContent = "Verify Code";
                        submitButton.disabled = false;

                        // Handle account lockout for reset attempts
                        if (data.locked) {
                            // Show lockout message in modal
                            const verificationForm = document.getElementById('resetVerificationForm');
                            const formContainer = verificationForm.parentElement;

                            // Create lockout message
                            const lockoutDiv = document.createElement('div');
                            lockoutDiv.className = 'alert alert-danger text-center mt-3';
                            lockoutDiv.innerHTML = `
                                <i class="bi bi-exclamation-triangle-fill fs-4 mb-2"></i>
                                <p class="mb-1"><strong>Reset Temporarily Locked</strong></p>
                                <p class="mb-0">Too many failed verification attempts. Please try again later.</p>
                            `;

                            // Disable verification form
                            verificationForm.querySelectorAll('input, button').forEach(el => {
                                el.disabled = true;
                            });

                            document.getElementById('resendResetCode').style.pointerEvents = 'none';
                            document.getElementById('resendResetCode').style.opacity = '0.5';

                            // Add lockout message to modal
                            formContainer.appendChild(lockoutDiv);

                            // Auto-close modal after 5 seconds
                            setTimeout(() => {
                                bootstrap.Modal.getInstance(document.getElementById('resetVerificationModal')).hide();
                            }, 5000);
                        } else {
                            // Clear the input fields for retry
                            document.querySelectorAll('.reset-verification-digit').forEach(input => {
                                input.value = '';
                                input.classList.remove('is-invalid');
                            });
                            // Focus on the first input
                            document.querySelector('.reset-verification-digit').focus();

                            // Show attempts remaining warning if provided
                            if (data.attempts_remaining) {
                                // Add or update attempts warning
                                let attemptsWarning = document.getElementById('resetAttemptsWarning');
                                if (!attemptsWarning) {
                                    attemptsWarning = document.createElement('div');
                                    attemptsWarning.id = 'resetAttemptsWarning';
                                    attemptsWarning.className = 'mt-3 text-center text-danger small';
                                    document.getElementById('resetVerificationForm').appendChild(attemptsWarning);
                                }
                                attemptsWarning.innerHTML = `<i class="bi bi-exclamation-circle me-1"></i> ${data.attempts_remaining} attempt${data.attempts_remaining !== 1 ? 's' : ''} remaining before reset is locked`;
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error('Reset Verification Error:', error);
                    removeOverlay();
                    showToast('error', "Network error. Please try again.");
                    submitButton.textContent = "Verify Code";
                    submitButton.disabled = false;
                });
            });

            // Handle resend reset code click
            document.getElementById('resendResetCode').addEventListener('click', function(e) {
                e.preventDefault();

                if (!resetEmail) {
                    showToast('error', "Session expired. Please try again.");
                    return;
                }

                this.textContent = "Sending...";
                this.style.pointerEvents = "none";

                let formData = new FormData();
                formData.append('email', resetEmail);
                formData.append('resend', true);

                fetch('../backend/auth/department/forgot_password.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        showToast('success', "A new reset code has been sent to your email");
                    } else {
                        showToast('error', data.message || "Failed to resend code");
                    }

                    // Reset the resend link
                    setTimeout(() => {
                        document.getElementById('resendResetCode').textContent = "Resend code";
                        document.getElementById('resendResetCode').style.pointerEvents = "";
                    }, 30000); // Disable for 30 seconds to prevent spam
                })
                .catch(error => {
                    console.error('Resend Reset Error:', error);
                    showToast('error', "Network error. Please try again.");
                    document.getElementById('resendResetCode').textContent = "Resend code";
                    document.getElementById('resendResetCode').style.pointerEvents = "";
                });
            });

            // Handle new password form submission
            document.getElementById('newPasswordForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const newPassword = document.getElementById('newPassword').value;
                const confirmPassword = document.getElementById('confirmPassword').value;
                let isValid = true;

                // Validate password length
                if (newPassword.length < 8) {
                    document.getElementById('newPassword').classList.add('is-invalid');
                    isValid = false;
                } else {
                    document.getElementById('newPassword').classList.remove('is-invalid');
                }

                // Validate password match
                if (newPassword !== confirmPassword) {
                    document.getElementById('confirmPassword').classList.add('is-invalid');
                    isValid = false;
                } else {
                    document.getElementById('confirmPassword').classList.remove('is-invalid');
                }

                if (!isValid) return;

                let submitButton = this.querySelector("button[type='submit']");
                submitButton.disabled = true;
                submitButton.textContent = "Resetting...";

                // Create form data for password reset
                let formData = new FormData();
                formData.append('email', resetEmail);
                formData.append('password', newPassword);
                formData.append('confirm_password', confirmPassword);

                createOverlay('Updating your password...');

                // Send password reset request
                fetch('../backend/auth/department/reset_password.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    removeOverlay();

                    if (data.status === "success") {
                        // Hide the new password modal
                        bootstrap.Modal.getInstance(document.getElementById('newPasswordModal')).hide();

                        showToast('success', "Password reset successful! You can now sign in with your new password.");
                    } else {
                        showToast('error', data.message || "Failed to reset password");
                        submitButton.textContent = "Reset Password";
                        submitButton.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Password Reset Error:', error);
                    removeOverlay();
                    showToast('error', "Network error. Please try again.");
                    submitButton.textContent = "Reset Password";
                    submitButton.disabled = false;
                });
            });

            // Setup verification digit inputs
            document.querySelectorAll('.verification-digit').forEach((input, index) => {
                // Auto focus next input when a digit is entered
                input.addEventListener('input', function(e) {
                    // Only allow numbers
                    this.value = this.value.replace(/[^0-9]/g, '');

                    // If a digit was entered and there's a next input, focus it
                    if (this.value && index < 5) {
                        document.querySelectorAll('.verification-digit')[index + 1].focus();
                    }

                    // Combine all digits into the hidden field
                    updateVerificationCode();
                });

                // Handle backspace - move to previous input
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && !this.value && index > 0) {
                        document.querySelectorAll('.verification-digit')[index - 1].focus();
                    }
                });

                // Handle paste event across all inputs
                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const paste = (e.clipboardData || window.clipboardData).getData('text');

                    // Check if pasted content is a 6-digit number
                    if (/^\d{6}$/.test(paste)) {
                        // Distribute the digits across all inputs
                        document.querySelectorAll('.verification-digit').forEach((digitInput, i) => {
                            digitInput.value = paste[i];
                        });
                        updateVerificationCode();
                    }
                });
            });

            // Helper function to update the hidden verification code field
            function updateVerificationCode() {
                const digits = Array.from(document.querySelectorAll('.verification-digit')).map(input => input.value);
                document.getElementById('verificationCode').value = digits.join('');
            }

            // Handle verification form submission
            document.getElementById('verificationForm').addEventListener('submit', function(e) {
                e.preventDefault();

                // Combine all input values into verification code
                updateVerificationCode();

                let verificationCode = document.getElementById('verificationCode').value;
                let submitButton = this.querySelector("button[type='submit']");

                // Validate code format
                if (!verificationCode.match(/^\d{6}$/)) {
                    document.querySelectorAll('.verification-digit').forEach(input => {
                        input.classList.add('is-invalid');
                    });
                    return;
                }

                // Disable button and show loading state
                submitButton.disabled = true;
                submitButton.textContent = "Verifying...";

                // Create form data for verification
                let formData = new FormData();
                formData.append('email', currentUserEmail);
                formData.append('code', verificationCode);

                createOverlay();

                // Send verification request
                fetch('../backend/auth/department/verify_code.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    removeOverlay();

                    if (data.status === "success") {
                        // Hide the modal
                        bootstrap.Modal.getInstance(document.getElementById('verificationModal')).hide();

                        showToast('success', "Verification successful! Redirecting to dashboard...");

                        createOverlay('Verification successful! Redirecting to dashboard...');
                        setTimeout(() => {
                            window.location.href = "index.php";
                        }, 2000);
                    } else {
                        showToast('error', data.message || "Invalid verification code");
                        submitButton.textContent = "Verify";
                        submitButton.disabled = false;

                        // Handle account lockout
                        if (data.locked) {
                            // Show lockout message in modal
                            const verificationForm = document.getElementById('verificationForm');
                            const formContainer = verificationForm.parentElement;

                            // Create lockout message
                            const lockoutDiv = document.createElement('div');
                            lockoutDiv.className = 'alert alert-danger text-center mt-3';
                            lockoutDiv.innerHTML = `
                                <i class="bi bi-exclamation-triangle-fill fs-4 mb-2"></i>
                                <p class="mb-1"><strong>Account Temporarily Locked</strong></p>
                                <p class="mb-0">Too many failed verification attempts. Please try again later.</p>
                            `;

                            // Disable verification form
                            verificationForm.querySelectorAll('input, button').forEach(el => {
                                el.disabled = true;
                            });

                            document.getElementById('resendCode').style.pointerEvents = 'none';
                            document.getElementById('resendCode').style.opacity = '0.5';

                            // Add lockout message to modal
                            formContainer.appendChild(lockoutDiv);

                            // Auto-close modal after 5 seconds
                            setTimeout(() => {
                                bootstrap.Modal.getInstance(document.getElementById('verificationModal')).hide();
                            }, 5000);
                        } else {
                            // Clear the input fields for retry
                            document.querySelectorAll('.verification-digit').forEach(input => {
                                input.value = '';
                                input.classList.remove('is-invalid');
                            });
                            // Focus on the first input
                            document.querySelector('.verification-digit').focus();

                            // Show attempts remaining warning if provided
                            if (data.attempts_remaining) {
                                // Add or update attempts warning
                                let attemptsWarning = document.getElementById('attemptsWarning');
                                if (!attemptsWarning) {
                                    attemptsWarning = document.createElement('div');
                                    attemptsWarning.id = 'attemptsWarning';
                                    attemptsWarning.className = 'mt-3 text-center text-danger small';
                                    document.getElementById('verificationForm').appendChild(attemptsWarning);
                                }
                                attemptsWarning.innerHTML = `<i class="bi bi-exclamation-circle me-1"></i> ${data.attempts_remaining} attempt${data.attempts_remaining !== 1 ? 's' : ''} remaining before your account is locked`;
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error('Verification Error:', error);
                    removeOverlay();
                    showToast('error', "Network error. Please try again.");
                    submitButton.textContent = "Verify";
                    submitButton.disabled = false;
                });
            });

            // Handle resend code click
            document.getElementById('resendCode').addEventListener('click', function(e) {
                e.preventDefault();

                if (!currentUserEmail) {
                    showToast('error', "Session expired. Please sign in again.");
                    return;
                }

                this.textContent = "Sending...";
                this.style.pointerEvents = "none";

                let formData = new FormData();
                formData.append('email', currentUserEmail);
                formData.append('resend', true);

                fetch('../backend/auth/department/signin.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        showToast('success', "A new verification code has been sent to your email");
                    } else {
                        showToast('error', data.message || "Failed to resend code");
                    }

                    // Reset the resend link
                    setTimeout(() => {
                        document.getElementById('resendCode').textContent = "Resend code";
                        document.getElementById('resendCode').style.pointerEvents = "";
                    }, 30000); // Disable for 30 seconds to prevent spam
                })
                .catch(error => {
                    console.error('Resend Error:', error);
                    showToast('error', "Network error. Please try again.");
                    document.getElementById('resendCode').textContent = "Resend code";
                    document.getElementById('resendCode').style.pointerEvents = "";
                });
            });
            
            // Handle force password reset form submission
            document.getElementById('forcePasswordResetForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const newPassword = document.getElementById('newForcePassword').value;
                const confirmPassword = document.getElementById('confirmForcePassword').value;
                let isValid = true;

                // Validate password length
                if (newPassword.length < 8) {
                    document.getElementById('newForcePassword').classList.add('is-invalid');
                    isValid = false;
                } else {
                    document.getElementById('newForcePassword').classList.remove('is-invalid');
                }

                // Validate password match
                if (newPassword !== confirmPassword) {
                    document.getElementById('confirmForcePassword').classList.add('is-invalid');
                    isValid = false;
                } else {
                    document.getElementById('confirmForcePassword').classList.remove('is-invalid');
                }

                if (!isValid) return;

                let submitButton = this.querySelector("button[type='submit']");
                submitButton.disabled = true;
                submitButton.textContent = "Updating...";

                // Create form data for password update
                let formData = new FormData();
                formData.append('newPassword', newPassword);
                formData.append('confirmPassword', confirmPassword);

                createOverlay('Updating your password...');

                // Send password update request
                fetch('../backend/auth/department/update_forced_password.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    removeOverlay();

                    if (data.status === "success") {
                        // Hide the force password reset modal
                        bootstrap.Modal.getInstance(document.getElementById('forcePasswordResetModal')).hide();

                        showToast('success', "Password updated successfully! You will now be redirected to your dashboard.");

                        setTimeout(() => {
                            createOverlay('Redirecting to your dashboard...');
                            window.location.href = "index.php";
                        }, 2000);
                    } else {
                        showToast('error', data.message || "Failed to update password");
                        submitButton.textContent = "Update Password";
                        submitButton.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Password Update Error:', error);
                    removeOverlay();
                    showToast('error', "Network error. Please try again.");
                    submitButton.textContent = "Update Password";
                    submitButton.disabled = false;
                });
            });

            // Password toggles
            const passwordInput = document.getElementById("departmentSigninPassword");
            const togglePassword = document.getElementById("togglePassword");
            const togglePasswordIcon = document.getElementById("togglePasswordIcon");

            togglePassword.addEventListener("click", function() {
                if (passwordInput.type === "password") {
                    passwordInput.type = "text";
                    togglePasswordIcon.classList.remove("bi-eye-slash");
                    togglePasswordIcon.classList.add("bi-eye");
                } else {
                    passwordInput.type = "password";
                    togglePasswordIcon.classList.remove("bi-eye");
                    togglePasswordIcon.classList.add("bi-eye-slash");
                }
            });
        });
    </script>

    <?php include '../includes/footer.php'; ?>