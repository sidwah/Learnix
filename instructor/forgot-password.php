<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Forgot Password | Learnix - Empowering Education</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Intuitive dashboard for instructors to create, manage courses, track student progress, and engage learners effectively." name="description" />
    <meta content="Learnix Team" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- App css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style"/>

    <style>
        /* Verification input styling */
        .verification-input {
            width: 50px;
            height: 50px;
            font-size: 24px;
            text-align: center;
            border-radius: 8px;
            border: 1px solid #ced4da;
        }

        .verification-input:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
            outline: none;
        }

        .verification-input + .verification-input {
            margin-left: 5px;
        }

        /* Custom alert styling */
        .custom-alert {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            min-width: 300px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        /* Overlay styling */
        .custom-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 15px;
            z-index: 9999;
        }

        /* Password strength indicator */
        .password-strength {
            height: 5px;
            margin-top: 8px;
            background-color: #e9ecef;
            border-radius: 3px;
            position: relative;
        }

        .password-strength-bar {
            height: 100%;
            border-radius: 3px;
            transition: width 0.3s;
        }

        /* Modal enhancements */
        .modal-content {
            border-radius: 10px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .modal-body {
            padding: 20px;
        }
    </style>
</head>

<body class="authentication-bg pb-0" data-layout-config='{"darkMode":false}'>
    <div class="auth-fluid">
        <!--Auth fluid left content -->
        <div class="auth-fluid-form-box">
            <div class="align-items-center d-flex h-100">
                <div class="card-body">

                    <!-- Logo -->
                    <div class="auth-brand text-center text-lg-start">
                        <a href="index.php" class="logo-dark">
                            <span><img src="assets/images/logo-dark.png" alt="Learnix Logo" height="18"></span>
                        </a>
                        <a href="index.php" class="logo-light">
                            <span><img src="assets/images/logo.png" alt="Learnix Logo" height="18"></span>
                        </a>
                    </div>

                    <!-- title-->
                    <h4 class="mt-0">Reset Password</h4>
                    <p class="text-muted mb-4">Enter your email address to receive a verification code for resetting your password.</p>

                    <!-- form -->
                    <form id="forgotPasswordForm">
                        <div class="mb-3">
                            <label for="emailaddress" class="form-label">Email address</label>
                            <input class="form-control" type="email" id="emailaddress" name="email" required placeholder="Enter your email">
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>
                        
                        <div class="mb-0 text-center d-grid">
                            <button class="btn btn-primary" type="submit" id="forgotPasswordButton">
                                <i class="mdi mdi-lock-reset me-1"></i> Send Code
                            </button>
                        </div>
                    </form>
                    <!-- end form-->

                    <!-- Footer-->
                    <footer class="footer footer-alt">
                        <p class="text-muted">Back to <a href="signin.php" class="text-muted ms-1"><b>Sign In</b></a></p>
                    </footer>

                </div> <!-- end .card-body -->
            </div> <!-- end .align-items-center.d-flex.h-100-->
        </div>
        <!-- end auth-fluid-form-box-->

        <!-- Auth fluid right content -->
        <div class="auth-fluid-right text-center">
            <div class="auth-user-testimonial">
                <h2 class="mb-3">Secure Account Recovery</h2>
                <p class="lead"><i class="mdi mdi-format-quote-open"></i> Learnix makes account recovery simple and secure. Our verification system ensures that only you can access your instructor dashboard and educational resources. <i class="mdi mdi-format-quote-close"></i>
                </p>
                <p>
                    - Learnix Security Team
                </p>
            </div> <!-- end auth-user-testimonial-->
        </div>
        <!-- end Auth fluid right content -->
    </div>
    <!-- end auth-fluid-->

    <!-- No toast container needed for alerts -->

    <!-- Verification Modal -->
    <div class="modal fade" id="verificationModal" tabindex="-1" aria-labelledby="verificationModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="verificationModalLabel">Enter Verification Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-center">A 5-digit verification code has been sent to your email.</p>
                    <form id="verificationForm">
                        <div class="d-flex justify-content-center gap-2 mb-3">
                            <input type="text" maxlength="1" class="form-control text-center verification-input" required name="code[]">
                            <input type="text" maxlength="1" class="form-control text-center verification-input" required name="code[]">
                            <input type="text" maxlength="1" class="form-control text-center verification-input" required name="code[]">
                            <input type="text" maxlength="1" class="form-control text-center verification-input" required name="code[]">
                            <input type="text" maxlength="1" class="form-control text-center verification-input" required name="code[]">
                        </div>
                        <div class="d-grid gap-3">
                            <button type="submit" class="btn btn-primary">Verify</button>
                        </div>
                        <div class="text-center mt-3">
                            <a href="#" id="resendCodeLink" class="text-muted">Resend Code</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resetPasswordModalLabel">Set New Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="resetPasswordForm">
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">New Password</label>
                            <div class="input-group">
                                <input
                                    type="password"
                                    class="form-control"
                                    id="newPassword"
                                    name="newPassword"
                                    required
                                    placeholder="Enter a new password">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength mt-2">
                                <div class="password-strength-bar" style="width: 0%;"></div>
                            </div>
                            <div class="form-text text-muted">
                                Password must be at least 8 characters long
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <input
                                type="password"
                                class="form-control"
                                id="confirmPassword"
                                name="confirmPassword"
                                required
                                placeholder="Re-enter your password">
                            <div class="invalid-feedback">Passwords do not match</div>
                        </div>
                        <div class="d-grid gap-3">
                            <button type="submit" class="btn btn-primary">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- bundle -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.min.js"></script>

    <!-- Custom Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modals
            const verificationModal = new bootstrap.Modal(document.getElementById('verificationModal'));
            const resetPasswordModal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));

            // Forms
            const forgotPasswordForm = document.getElementById('forgotPasswordForm');
            const verificationForm = document.getElementById('verificationForm');
            const resetPasswordForm = document.getElementById('resetPasswordForm');

            // Inputs
            const emailInput = document.getElementById('emailaddress');
            const verificationInputs = document.querySelectorAll('.verification-input');
            const resendCodeLink = document.getElementById('resendCodeLink');
            const togglePasswordBtn = document.getElementById('togglePassword');
            const newPasswordInput = document.getElementById('newPassword');
            const confirmPasswordInput = document.getElementById('confirmPassword');
            const passwordStrengthBar = document.querySelector('.password-strength-bar');

            // Store email for reset process
            let resetEmail = '';

            // Show Loading Overlay
            function showOverlay(message = null) {
                // Remove any existing overlay
                const existingOverlay = document.querySelector('.custom-overlay');
                if (existingOverlay) {
                    existingOverlay.remove();
                }

                // Create new overlay
                const overlay = document.createElement('div');
                overlay.className = 'custom-overlay';
                
                // Add spinner
                const spinner = document.createElement('div');
                spinner.className = 'spinner-border text-light';
                spinner.setAttribute('role', 'status');
                spinner.innerHTML = '<span class="visually-hidden">Loading...</span>';
                overlay.appendChild(spinner);
                
                // Add message if provided
                if (message) {
                    const messageElement = document.createElement('div');
                    messageElement.className = 'text-white fs-5';
                    messageElement.textContent = message;
                    overlay.appendChild(messageElement);
                }

                document.body.appendChild(overlay);
            }

            // Remove Loading Overlay
            function removeOverlay() {
                const overlay = document.querySelector('.custom-overlay');
                if (overlay) {
                    overlay.remove();
                }
            }

            // Show alert notification function
            function showAlert(type, message) {
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'danger'} alert-dismissible fade show custom-alert`;
                alertDiv.setAttribute('role', 'alert');
                alertDiv.innerHTML = `
                    ${type === 'success' ? '<i class="mdi mdi-check-circle me-1"></i>' : type === 'warning' ? '<i class="mdi mdi-alert-circle me-1"></i>' : '<i class="mdi mdi-close-circle me-1"></i>'}
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                
                document.body.appendChild(alertDiv);
                
                // Auto-dismiss after 5 seconds
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.classList.remove('show');
                        setTimeout(() => {
                            if (alertDiv.parentNode) {
                                alertDiv.parentNode.removeChild(alertDiv);
                            }
                        }, 300);
                    }
                }, 5000);
            }

            // Check password strength
            function checkPasswordStrength(password) {
                let strength = 0;
                
                // Length check
                if (password.length >= 8) strength += 25;
                
                // Contains lowercase
                if (/[a-z]/.test(password)) strength += 25;
                
                // Contains uppercase
                if (/[A-Z]/.test(password)) strength += 25;
                
                // Contains numbers or special chars
                if (/[0-9!@#$%^&*(),.?":{}|<>]/.test(password)) strength += 25;
                
                // Update strength bar
                passwordStrengthBar.style.width = `${strength}%`;
                
                // Set color based on strength
                if (strength < 50) {
                    passwordStrengthBar.style.backgroundColor = '#dc3545'; // Danger/red
                } else if (strength < 75) {
                    passwordStrengthBar.style.backgroundColor = '#ffc107'; // Warning/yellow
                } else {
                    passwordStrengthBar.style.backgroundColor = '#198754'; // Success/green
                }
                
                return strength;
            }

            // Ensure minimum wait time for UX
            function ensureMinWaitTime(startTime, minTime, callback) {
                const elapsedTime = Date.now() - startTime;
                const remainingTime = Math.max(0, minTime - elapsedTime);
                setTimeout(callback, remainingTime);
            }

            // Forgot Password Form Submission
            forgotPasswordForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const email = emailInput.value.trim();
                const submitBtn = this.querySelector('button[type="submit"]');
                
                // Disable button and update text
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin me-1"></i> Sending...';

                // Basic email validation
                if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    emailInput.classList.add('is-invalid');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="mdi mdi-lock-reset me-1"></i> Send Code';
                    return;
                }

                // Show overlay
                showOverlay('Sending verification code...');
                const startTime = Date.now();

                // Prepare form data
                const formData = new FormData();
                formData.append('email', email);

                // Send request
                fetch('../backend/auth/instructor/forgot_password.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Ensure minimum wait time for better UX
                    ensureMinWaitTime(startTime, 2000, () => {
                        removeOverlay();
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="mdi mdi-lock-reset me-1"></i> Send Code';

                        if (data.status === 'success') {
                            // Store email for verification
                            resetEmail = email;

                            // Show verification modal
                            verificationModal.show();

                            // Reset and focus first verification input
                            verificationInputs.forEach(input => {
                                input.value = '';
                                input.classList.remove('is-invalid');
                            });
                            verificationInputs[0].focus();

                            // Show success notification
                            showAlert('success', data.message || 'Verification code sent successfully');
                        } else {
                            // Show error notification with danger styling
                            showAlert('danger', data.message || 'Failed to send verification code');
                        }
                    });
                })
                .catch(error => {
                    // Ensure minimum wait time for better UX
                    ensureMinWaitTime(startTime, 2000, () => {
                        removeOverlay();
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="mdi mdi-lock-reset me-1"></i> Send Code';
                        console.error('Error:', error);
                        showAlert('danger', 'Network error. Please try again.');
                    });
                });
            });

            // Verification Code Input Handling
            verificationInputs.forEach((input, index) => {
                // Only allow numbers
                input.addEventListener('input', function(e) {
                    this.value = this.value.replace(/[^0-9]/g, '');
                    
                    if (this.value.length === 1) {
                        // Move focus to next input if a digit is entered
                        if (index < verificationInputs.length - 1) {
                            verificationInputs[index + 1].focus();
                        } else {
                            // Check if all inputs are filled
                            const allFilled = Array.from(verificationInputs).every(input => input.value.length === 1);
                            if (allFilled) {
                                // Auto-submit when all digits are entered
                                verificationForm.dispatchEvent(new Event('submit'));
                            }
                        }
                    }
                });

                // Handle backspace to move to previous input
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && this.value.length === 0 && index > 0) {
                        verificationInputs[index - 1].focus();
                    }
                });

                // Handle paste event for verification code
                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const pastedData = (e.clipboardData || window.clipboardData).getData('text');
                    
                    // Check if pasted data is a 5-digit number
                    if (/^\d{5}$/.test(pastedData)) {
                        // Distribute digits across inputs
                        verificationInputs.forEach((input, i) => {
                            if (i < pastedData.length) {
                                input.value = pastedData[i];
                            }
                        });
                        
                        // Auto-submit
                        verificationForm.dispatchEvent(new Event('submit'));
                    }
                });
            });

            // Verification Form Submission
            verificationForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const verifyButton = this.querySelector('button[type="submit"]');
                verifyButton.disabled = true;
                verifyButton.innerHTML = '<i class="mdi mdi-loading mdi-spin me-1"></i> Verifying...';

                // Collect verification code
                const verificationCode = Array.from(verificationInputs)
                    .map(input => input.value)
                    .join('');

                // Validate code
                if (!/^\d{5}$/.test(verificationCode)) {
                    showAlert('danger', 'Please enter a valid 5-digit code');
                    verifyButton.disabled = false;
                    verifyButton.innerHTML = 'Verify';
                    return;
                }

                // Show overlay
                showOverlay('Verifying code...');
                const startTime = Date.now();

                // Prepare form data
                const formData = new FormData();
                formData.append('email', resetEmail);
                formData.append('code', verificationCode);

                // Send verification request
                fetch('../backend/auth/instructor/verify_reset_code.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Ensure minimum wait time for better UX
                    ensureMinWaitTime(startTime, 2000, () => {
                        removeOverlay();
                        verifyButton.disabled = false;
                        verifyButton.innerHTML = 'Verify';

                        if (data.status === 'success') {
                            // Close verification modal
                            verificationModal.hide();

                            // Show reset password modal
                            resetPasswordModal.show();

                            // Reset password fields
                            newPasswordInput.value = '';
                            confirmPasswordInput.value = '';
                            passwordStrengthBar.style.width = '0%';

                            // Show success notification
                            showAlert('success', data.message || 'Verification successful');
                        } else {
                            // Handle verification failure
                            if (data.locked) {
                                // Account locked
                                showAlert('danger', `Too many attempts. Try again in ${data.minutes_remaining} minutes.`);

                                // Disable verification inputs
                                verificationInputs.forEach(input => {
                                    input.disabled = true;
                                    input.classList.add('is-invalid');
                                });
                            } else {
                                // Clear inputs and show error
                                verificationInputs.forEach(input => {
                                    input.value = '';
                                    input.classList.remove('is-invalid');
                                });
                                verificationInputs[0].focus();

                                // Show error notification
                                showAlert('danger', data.message || 'Invalid verification code');
                            }
                        }
                    });
                })
                .catch(error => {
                    // Ensure minimum wait time for better UX
                    ensureMinWaitTime(startTime, 2000, () => {
                        removeOverlay();
                        verifyButton.disabled = false;
                        verifyButton.innerHTML = 'Verify';
                        console.error('Error:', error);
                        showToast('Network error. Please try again.', 'danger');
                    });
                });
            });

            // Resend Code Link
            resendCodeLink.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Disable link temporarily
                this.style.pointerEvents = 'none';
                this.style.opacity = '0.5';

                // Show overlay
                showOverlay('Resending verification code...');
                const startTime = Date.now();

                // Prepare form data
                const formData = new FormData();
                formData.append('email', resetEmail);

                // Send resend code request
                fetch('../backend/auth/instructor/forgot_password.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Ensure minimum wait time for better UX
                    ensureMinWaitTime(startTime, 2000, () => {
                        removeOverlay();
                        
                        // Re-enable link
                        resendCodeLink.style.pointerEvents = 'auto';
                        resendCodeLink.style.opacity = '1';

                        if (data.status === 'success') {
                            // Reset verification inputs
                            verificationInputs.forEach(input => {
                                input.value = '';
                                input.classList.remove('is-invalid');
                                input.disabled = false;
                            });
                            verificationInputs[0].focus();
                            
                            // Show success notification
                            showAlert('success', data.message || 'Verification code resent successfully');
                        } else {
                            // Show error notification with danger styling
                            showAlert('danger', data.message || 'Failed to resend verification code');
                        }
                    });
                })
                .catch(error => {
                    // Ensure minimum wait time for better UX
                    ensureMinWaitTime(startTime, 2000, () => {
                        removeOverlay();
                        
                        // Re-enable link
                        resendCodeLink.style.pointerEvents = 'auto';
                        resendCodeLink.style.opacity = '1';
                        
                        console.error('Error:', error);
                        showToast('Network error. Please try again.', 'danger');
                    });
                });
            });

            // Password input handling
            newPasswordInput.addEventListener('input', function() {
                const strength = checkPasswordStrength(this.value);
                
                // Update confirm password validation
                if (confirmPasswordInput.value) {
                    if (this.value !== confirmPasswordInput.value) {
                        confirmPasswordInput.classList.add('is-invalid');
                    } else {
                        confirmPasswordInput.classList.remove('is-invalid');
                    }
                }
            });

            // Confirm password validation
            confirmPasswordInput.addEventListener('input', function() {
                if (this.value && this.value !== newPasswordInput.value) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            });

            // Toggle Password Visibility
            togglePasswordBtn.addEventListener('click', function() {
                const type = newPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                newPasswordInput.setAttribute('type', type);
                
                // Toggle eye icon
                this.innerHTML = type === 'password' ?
                    '<i class="bi bi-eye"></i>' :
                    '<i class="bi bi-eye-slash"></i>';
            });

            // Reset Password Form Submission
            resetPasswordForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const newPassword = newPasswordInput.value.trim();
                const confirmPassword = confirmPasswordInput.value.trim();
                const submitBtn = this.querySelector('button[type="submit"]');
                
                // Disable button and update text
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin me-1"></i> Updating...';

                // Validate new password
                if (newPassword.length < 8) {
                    showAlert('danger', 'Password must be at least 8 characters long');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Update Password';
                    return;
                }

                // Validate password match
                if (newPassword !== confirmPassword) {
                    confirmPasswordInput.classList.add('is-invalid');
                    showAlert('danger', 'Passwords do not match');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Update Password';
                    return;
                }

                // Show overlay
                showOverlay('Resetting password...');
                const startTime = Date.now();

                // Prepare form data
                const formData = new FormData();
                formData.append('email', resetEmail);
                formData.append('password', newPassword);
                formData.append('confirm_password', confirmPassword);

                // Send reset password request
                fetch('../backend/auth/instructor/reset_password.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Ensure minimum wait time for better UX
                    ensureMinWaitTime(startTime, 2000, () => {
                        removeOverlay();
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Update Password';

                        if (data.status === 'success') {
                            // Close reset password modal
                            resetPasswordModal.hide();

                            // Show success notification
                            showAlert('success', data.message || 'Password reset successful');

                            // Show loading overlay for redirect
                            showOverlay('Redirecting to login...');

                            // Redirect to login after a short delay
                            setTimeout(() => {
                                window.location.href = 'signin.php';
                            }, 2000);
                        } else {
                            // Show error notification
                            showAlert('danger', data.message || 'Failed to reset password');
                        }
                    });
                })
                .catch(error => {
                    // Ensure minimum wait time for better UX
                    ensureMinWaitTime(startTime, 2000, () => {
                        removeOverlay();
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Update Password';
                        console.error('Error:', error);
                        showToast('Network error. Please try again.', 'danger');
                    });
                });
            });
        });
    </script>
</body>
</html>