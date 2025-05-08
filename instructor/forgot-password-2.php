<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Forgot Password | Learnix - Empowering Education</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Intuitive dashboard for instructors to create, manage courses, track student progress, and engage learners effectively." />
    <meta name="author" content="Learnix Team" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- App css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

    <style>
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

        .verification-input+.verification-input {
            margin-left: 5px;
        }

        .toast-container {
            z-index: 9999;
        }

        .custom-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
    </style>
</head>

<body class="loading authentication-bg" data-layout-config='{"darkMode":false}'>
    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xxl-4 col-lg-5">
                    <div class="card">
                        <div class="card-header pt-4 pb-4 text-center bg-primary">
                            <a href="index.php">
                                <img src="assets/images/logo.png" alt="Logo" height="18">
                            </a>
                        </div>

                        <div class="card-body p-4">
                            <div class="text-center w-75 m-auto">
                                <h4 class="text-dark-50 text-center pb-0 fw-bold">Forgot Password</h4>
                                <p class="text-muted mb-4">Enter your email address to receive a verification code.</p>
                            </div>

                            <form id="forgotPasswordForm">
                                <div class="mb-3">
                                    <label for="emailaddress" class="form-label">Email address</label>
                                    <input class="form-control" type="email" id="emailaddress" name="email" required placeholder="Enter your email">
                                    <div class="invalid-feedback">Please enter a valid email address.</div>
                                </div>

                                <div class="mb-3 mb-0 text-center">
                                    <button class="btn btn-primary" type="submit" id="forgotPasswordButton">Send Code</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Verification Modal -->
                    <div class="modal fade" id="verificationModal" tabindex="-1" aria-labelledby="verificationModalLabel" aria-hidden="true" data-bs-backdrop="static">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="verificationModalLabel">Enter Verification Code</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>A 5-digit verification code has been sent to your email.</p>
                                    <form id="verificationForm">
                                        <div class="d-flex justify-content-center gap-2 mb-3">
                                            <input type="text" maxlength="1" class="form-control text-center verification-input" required>
                                            <input type="text" maxlength="1" class="form-control text-center verification-input" required>
                                            <input type="text" maxlength="1" class="form-control text-center verification-input" required>
                                            <input type="text" maxlength="1" class="form-control text-center verification-input" required>
                                            <input type="text" maxlength="1" class="form-control text-center verification-input" required>
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

                    <div class="row mt-3">
                        <div class="col-12 text-center">
                            <p class="text-muted">Don't have an account? <a href="signup.php" class="text-muted ms-1"><b>Sign Up</b></a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer footer-alt">
        © Learnix. <script>
            document.write(new Date().getFullYear())
        </script> All rights reserved.
    </footer>

    <!-- bundle -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
                overlay.innerHTML = `
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                ${message ? `<div class="text-white ms-3">${message}</div>` : ''}
            `;

                document.body.appendChild(overlay);
            }

            // Remove Loading Overlay
            function removeOverlay() {
                const overlay = document.querySelector('.custom-overlay');
                if (overlay) {
                    overlay.remove();
                }
            }

            // Show Toast Notification
            function showNotification(message, type = 'success') {
                // Create toast container if it doesn't exist
                let toastContainer = document.getElementById('toastContainer');
                if (!toastContainer) {
                    toastContainer = document.createElement('div');
                    toastContainer.id = 'toastContainer';
                    toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                    document.body.appendChild(toastContainer);
                }

                // Create toast element
                const toastDiv = document.createElement('div');
                toastDiv.className = `toast align-items-center text-bg-${type === 'success' ? 'success' : 'danger'} border-0`;
                toastDiv.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;

                // Add to container and show
                toastContainer.appendChild(toastDiv);
                const toast = new bootstrap.Toast(toastDiv);
                toast.show();

                // Remove toast after it's hidden
                toastDiv.addEventListener('hidden.bs.toast', () => {
                    toastDiv.remove();
                });
            }

            // Forgot Password Form Submission
            forgotPasswordForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const email = emailInput.value.trim();
                const submitBtn = this.querySelector('button[type="submit"]');

                // Basic email validation
                if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    emailInput.classList.add('is-invalid');
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
                        // Calculate how much time has passed since showing the overlay
                        const elapsedTime = Date.now() - startTime;
                        const remainingTime = Math.max(0, 2000 - elapsedTime);

                        // Wait at least 2 seconds before proceeding
                        setTimeout(() => {
                            removeOverlay();

                            if (data.status === 'success') {
                                // Store email for verification
                                resetEmail = email;

                                // Show verification modal
                                verificationModal.show();

                                // Focus first verification input
                                verificationInputs[0].focus();

                                // Show success notification
                                showNotification(data.message || 'Verification code sent successfully');
                            } else {
                                // Show error notification with danger styling
                                showNotification(data.message || 'Failed to send verification code', 'danger');
                            }
                        }, remainingTime);
                    })
                    .catch(error => {
                        // Calculate how much time has passed
                        const elapsedTime = Date.now() - startTime;
                        const remainingTime = Math.max(0, 2000 - elapsedTime);

                        // Wait at least 2 seconds before showing error
                        setTimeout(() => {
                            removeOverlay();
                            console.error('Error:', error);

                            // Show network error notification
                            showNotification('Network error. Please try again.', 'danger');
                        }, remainingTime);
                    });
            });

            // Verification Code Input Handling
            verificationInputs.forEach((input, index) => {
                input.addEventListener('input', function(e) {
                    // Only allow numbers
                    this.value = this.value.replace(/[^0-9]/g, '');

                    // Move focus to next input if a digit is entered
                    if (this.value.length === 1 && index < 4) {
                        verificationInputs[index + 1].focus();
                    }
                });

                // Handle backspace to move to previous input
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && this.value.length === 0 && index > 0) {
                        verificationInputs[index - 1].focus();
                    }
                });
            });

            // Verification Form Submission
            verificationForm.addEventListener('submit', function(e) {
                e.preventDefault();

                // Collect verification code
                const verificationCode = Array.from(verificationInputs)
                    .map(input => input.value)
                    .join('');

                // Validate code
                if (!/^\d{5}$/.test(verificationCode)) {
                    showNotification('Please enter a valid 5-digit code', 'danger');
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
                        // Calculate how much time has passed
                        const elapsedTime = Date.now() - startTime;
                        const remainingTime = Math.max(0, 2000 - elapsedTime);

                        // Wait at least 2 seconds before proceeding
                        setTimeout(() => {
                            removeOverlay();

                            if (data.status === 'success') {
                                // Close verification modal
                                verificationModal.hide();

                                // Show reset password modal
                                resetPasswordModal.show();

                                // Show success notification
                                showNotification(data.message || 'Verification successful');
                            } else {
                                // Handle verification failure
                                if (data.locked) {
                                    // Account locked
                                    showNotification(`Too many attempts. Try again in ${data.minutes_remaining} minutes.`, 'danger');

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
                                    showNotification(data.message || 'Invalid verification code', 'danger');
                                }
                            }
                        }, remainingTime);
                    })
                    .catch(error => {
                        // Calculate how much time has passed
                        const elapsedTime = Date.now() - startTime;
                        const remainingTime = Math.max(0, 2000 - elapsedTime);

                        // Wait at least 2 seconds before showing error
                        setTimeout(() => {
                            removeOverlay();
                            console.error('Error:', error);

                            // Show network error notification
                            showNotification('Network error. Please try again.', 'danger');
                        }, remainingTime);
                    });
            });

            // Resend Code Link
            resendCodeLink.addEventListener('click', function(e) {
                e.preventDefault();

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
                        // Calculate how much time has passed
                        const elapsedTime = Date.now() - startTime;
                        const remainingTime = Math.max(0, 2000 - elapsedTime);

                        // Wait at least 2 seconds before proceeding
                        setTimeout(() => {
                            removeOverlay();

                            if (data.status === 'success') {
                                // Show success notification
                                showNotification(data.message || 'Verification code resent successfully');
                            } else {
                                // Show error notification with danger styling
                                showNotification(data.message || 'Failed to resend verification code', 'danger');
                            }
                        }, remainingTime);
                    })
                    .catch(error => {
                        // Calculate how much time has passed
                        const elapsedTime = Date.now() - startTime;
                        const remainingTime = Math.max(0, 2000 - elapsedTime);

                        // Wait at least 2 seconds before showing error
                        setTimeout(() => {
                            removeOverlay();
                            console.error('Error:', error);

                            // Show network error notification
                            showNotification('Network error. Please try again.', 'danger');
                        }, remainingTime);
                    });
            });

            // Reset Password Form Submission
            resetPasswordForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const newPassword = document.getElementById('newPassword').value.trim();
                const confirmPassword = document.getElementById('confirmPassword').value.trim();

                // Validate new password
                if (newPassword.length < 8) {
                    showNotification('Password must be at least 8 characters long', 'danger');
                    return;
                }

                // Validate password match
                if (newPassword !== confirmPassword) {
                    showNotification('Passwords do not match', 'danger');
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
                        // Calculate how much time has passed
                        const elapsedTime = Date.now() - startTime;
                        const remainingTime = Math.max(0, 2000 - elapsedTime);

                        // Wait at least 2 seconds before proceeding
                        setTimeout(() => {
                            removeOverlay();

                            if (data.status === 'success') {
                                // Close reset password modal
                                resetPasswordModal.hide();

                                // Show success notification
                                showNotification(data.message || 'Password reset successful');

                                // Redirect to login after a short delay
                                setTimeout(() => {
                                    window.location.href = 'signin.php';
                                }, 2000);
                            } else {
                                // Show error notification
                                showNotification(data.message || 'Failed to reset password', 'danger');
                            }
                        }, remainingTime);
                    })
                    .catch(error => {
                        // Calculate how much time has passed
                        const elapsedTime = Date.now() - startTime;
                        const remainingTime = Math.max(0, 2000 - elapsedTime);

                        // Wait at least 2 seconds before showing error
                        setTimeout(() => {
                            removeOverlay();
                            console.error('Error:', error);

                            // Show network error notification
                            showNotification('Network error. Please try again.', 'danger');
                        }, remainingTime);
                    });
            });

            // Toggle Password Visibility
            togglePasswordBtn.addEventListener('click', function() {
                const passwordInput = document.getElementById('newPassword');
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                // Toggle eye icon
                this.innerHTML = type === 'password' ?
                    '<i class="bi bi-eye"></i>' :
                    '<i class="bi bi-eye-slash"></i>';
            });
        });
    </script>
</body>

</html>