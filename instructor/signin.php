<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Sign In | Learnix - Empowering Education</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Intuitive dashboard for instructors to create, manage courses, track student progress, and engage learners effectively." name="description" />
    <meta content="Learnix Team" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="../favicon.ico">

    <!-- App css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

    <!-- Additional custom styles -->
    <style>
        /* Verification code input styling */
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

        /* Modal styling */
        .modal-content {
            padding: 20px;
            border-radius: 10px;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
        }

        .modal-body p {
            text-align: center;
            margin-bottom: 20px;
        }

        /* Custom alert styling */
        .custom-alert {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            min-width: 300px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
                    <h4 class="mt-0">Instructor Sign In</h4>
                    <p class="text-muted mb-4">Enter your email address and password to access your instructor dashboard.</p>

                    <!-- form -->
                    <form id="signinForm">
                        <div class="mb-3">
                            <label for="emailaddress" class="form-label">Email address</label>
                            <input class="form-control" type="email" id="emailaddress" name="email" required placeholder="Enter your email">
                        </div>
                        <div class="mb-3">
                            <a href="forgot-password.php" class="text-muted float-end"><small>Forgot your password?</small></a>
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password" name="password" class="form-control" required placeholder="Enter your password">
                                <div class="input-group-text" data-password="false">
                                    <span class="password-eye"></span>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid mb-0 text-center">
                            <button class="btn btn-primary" type="submit" id="signinButton"><i class="mdi mdi-login"></i> Sign In</button>
                        </div>
                    </form>
                    <!-- end form-->

                    <!-- Footer-->
                    <footer class="footer footer-alt">
                        <p class="text-muted">Need an account? Please contact your department head for access.</p>
                    </footer>

                </div> <!-- end .card-body -->
            </div> <!-- end .align-items-center.d-flex.h-100-->
        </div>
        <!-- end auth-fluid-form-box-->

        <!-- Auth fluid right content -->
        <div class="auth-fluid-right text-center">
            <div class="auth-user-testimonial">
                <h2 class="mb-3">Transform Your Teaching</h2>
                <p class="lead"><i class="mdi mdi-format-quote-open"></i> Learnix has revolutionized how I engage with my students. The intuitive interface and powerful analytics help me personalize learning like never before. <i class="mdi mdi-format-quote-close"></i>
                </p>
                <p>
                    - Dr. Sarah Johnson, Professor of Education
                </p>
            </div> <!-- end auth-user-testimonial-->
        </div>
        <!-- end Auth fluid right content -->
    </div>
    <!-- end auth-fluid-->

    <!-- Verification Modal -->
    <div class="modal fade" id="verificationModal" tabindex="-1" aria-labelledby="verificationModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="verificationModalLabel">Verify Your Account</h5>
                </div>
                <div class="modal-body">
                    <p>A 5-digit verification code has been sent to your email. Enter the code below to verify your account.</p>
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
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Password Change Modal -->
    <div class="modal fade" id="passwordChangeModal" tabindex="-1" aria-labelledby="passwordChangeModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title" id="passwordChangeModalLabel">Set Your Password</h5>
                </div>
                <div class="modal-body">
                    <div class="alert alert-soft-primary">
                        <p class="mb-0"><strong>Welcome!</strong> As this is your first login, please set a permanent password for your account.</p>
                    </div>
                    <form id="passwordChangeForm">
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">New Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="newPassword" name="newPassword" class="form-control" required placeholder="Enter new password">
                                <div class="input-group-text" onclick="togglePassword('newPassword')">
                                    <span class="password-eye"></span>
                                </div>
                            </div>
                            <div class="form-text">
                                Password must be at least 8 characters long and include a mix of letters, numbers, and special characters.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="confirmPassword" name="confirmPassword" class="form-control" required placeholder="Confirm your password">
                                <div class="input-group-text" onclick="togglePassword('confirmPassword')">
                                    <span class="password-eye"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Password Strength Indicator -->
                        <div class="mb-3">
                            <label class="form-label">Password Strength</label>
                            <div class="progress" style="height: 6px;">
                                <div id="passwordStrength" class="progress-bar bg-danger" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div id="passwordFeedback" class="form-text mt-1">
                                Enter a new password
                            </div>
                        </div>

                        <div class="d-grid gap-3">
                            <button type="submit" class="btn btn-primary">Set Password & Continue</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- bundle -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        // Handle password change form
        document.getElementById("passwordChangeForm").onsubmit = async (e) => {
            e.preventDefault();

            const submitButton = e.target.querySelector("button[type='submit']");
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="mdi mdi-loading mdi-spin me-1"></i> Setting Password...';

            createOverlay("Setting your password...");

            const formData = new FormData(e.target);
            const newPassword = formData.get('newPassword');
            const confirmPassword = formData.get('confirmPassword');

            if (newPassword !== confirmPassword) {
                removeOverlay();
                showAlert('danger', "❌ Passwords do not match!");
                submitButton.disabled = false;
                submitButton.innerHTML = 'Set Password & Continue';
                return;
            }

            try {
                const response = await fetch("../backend/auth/instructor/change_password.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        newPassword: newPassword,
                        confirmPassword: confirmPassword,
                        isFirstTime: true
                    })
                });

                const result = await response.json();

                if (response.ok && result.status === "success") {
                    removeOverlay();

                    // Close modal
                    const modalElement = document.getElementById("passwordChangeModal");
                    const passwordModal = bootstrap.Modal.getInstance(modalElement);
                    if (passwordModal) {
                        passwordModal.hide();
                    }

                    // Show success and redirect
                    createOverlay("Password set successfully! Redirecting...");
                    showAlert('success', "✅ Password set successfully! Taking you to your dashboard...");

                    setTimeout(() => {
                        window.location.href = "../instructor/index.php";
                    }, 2000);
                } else {
                    removeOverlay();
                    showAlert('danger', result.message || "❌ Failed to set password. Please try again.");
                }
            } catch (error) {
                removeOverlay();
                showAlert('danger', "❌ There was an error processing your request. Please try again.");
                console.error("Error:", error);
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = 'Set Password & Continue';
            }
        };

        // Password strength checker
        document.getElementById('newPassword').addEventListener('input', function() {
            const password = this.value;
            const strength = checkPasswordStrength(password);
            updatePasswordStrengthUI(strength);
        });

        function checkPasswordStrength(password) {
            let score = 0;

            // Length check
            if (password.length >= 8) score += 25;
            if (password.length >= 12) score += 25;

            // Character variety checks
            if (/[a-z]/.test(password)) score += 10;
            if (/[A-Z]/.test(password)) score += 10;
            if (/[0-9]/.test(password)) score += 10;
            if (/[^A-Za-z0-9]/.test(password)) score += 20;

            return score;
        }

        function updatePasswordStrengthUI(score) {
            const progressBar = document.getElementById('passwordStrength');
            const feedback = document.getElementById('passwordFeedback');

            progressBar.style.width = score + '%';
            progressBar.setAttribute('aria-valuenow', score);

            if (score < 50) {
                progressBar.className = 'progress-bar bg-danger';
                feedback.textContent = 'Weak password';
                feedback.className = 'form-text mt-1 text-danger';
            } else if (score < 75) {
                progressBar.className = 'progress-bar bg-warning';
                feedback.textContent = 'Fair password';
                feedback.className = 'form-text mt-1 text-warning';
            } else {
                progressBar.className = 'progress-bar bg-success';
                feedback.textContent = 'Strong password';
                feedback.className = 'form-text mt-1 text-success';
            }
        }

        // Password toggle function
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const toggleElement = event.currentTarget;

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleElement.setAttribute('data-password', 'true');
            } else {
                passwordInput.type = 'password';
                toggleElement.setAttribute('data-password', 'false');
            }
        }

        // Handle Signin Form Submission
        document.getElementById("signinForm").onsubmit = async (e) => {
            e.preventDefault();
            const signinButton = document.getElementById("signinButton");
            signinButton.disabled = true;
            signinButton.innerHTML = '<i class="mdi mdi-loading mdi-spin me-1"></i> Signing in...';

            createOverlay("Signing in...");

            const formData = new FormData(e.target);

            try {
                const response = await fetch("../backend/auth/instructor/signin.php", {
                    method: "POST",
                    body: formData,
                });

                const result = await response.json();

                if (response.ok) {
                    if (result.status === "success") {
                        removeOverlay();
                        createOverlay("Sign in successful! Redirecting...");
                        showAlert('success', "✅ Sign in successful! Redirecting to your dashboard...");
                        setTimeout(() => {
                            window.location.href = "../instructor/index.php";
                        }, 2000);
                    } else if (result.status === "reset_required") {
                        removeOverlay();
                        showAlert('info', "Please set your permanent password to continue.");

                        // Show the password change modal
                        const passwordModal = new bootstrap.Modal(document.getElementById("passwordChangeModal"));
                        passwordModal.show();
                    } else if (result.status === "mfa_required") {
                        removeOverlay();
                        showAlert('info', "Please enter the verification code sent to your email.");

                        // Show verification modal for MFA
                        const modal = new bootstrap.Modal(document.getElementById("verificationModal"));
                        modal.show();
                    } else if (result.status === "unverified") {
                        removeOverlay();
                        showAlert('warning', "Your account is not verified. Please contact support.");
                    } else if (result.status === "lockout") {
                        removeOverlay();
                        showAlert('danger', "❌ Account locked due to multiple failed attempts.");
                    } else {
                        removeOverlay();
                        showAlert('danger', result.message || "❌ Sign in failed.");
                    }
                } else {
                    removeOverlay();
                    showAlert('danger', "❌ Server error. Please try again.");
                }
            } catch (error) {
                removeOverlay();
                showAlert('danger', "❌ There was an error processing your request. Please try again.");
                console.error("Error:", error);
            } finally {
                signinButton.disabled = false;
                signinButton.innerHTML = '<i class="mdi mdi-login"></i> Sign In';
            }
        };

        // Handle Verification Form Submission for MFA
        document.getElementById("verificationForm").onsubmit = async (e) => {
            e.preventDefault();

            const verifyButton = e.target.querySelector("button[type='submit']");
            verifyButton.disabled = true;
            verifyButton.innerHTML = '<i class="mdi mdi-loading mdi-spin me-1"></i> Verifying...';

            createOverlay("Verifying your account...");

            const verificationInputs = Array.from(e.target.querySelectorAll("input[name='code[]']")).map((input) => input.value.trim());
            const code = verificationInputs.join("");

            try {
                const response = await fetch("../backend/auth/instructor/verify_mfa.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        code: code
                    }),
                });

                const result = await response.json();

                if (response.ok && result.status === "success") {
                    removeOverlay();
                    createOverlay("Verification successful! Redirecting...");
                    showAlert('success', "✅ Verification successful! Redirecting to your dashboard...");
                    setTimeout(() => {
                        window.location.href = "../instructor/index.php";
                    }, 2000);
                } else {
                    removeOverlay();
                    showAlert('danger', result.message || "❌ Verification failed. Please try again.");
                }
            } catch (error) {
                removeOverlay();
                showAlert('danger', "❌ There was an error processing your request. Please try again.");
                console.error("Error:", error);
            } finally {
                verifyButton.disabled = false;
                verifyButton.innerHTML = "Verify";
            }
        };

        // Show alert notification function
        function showAlert(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'danger'} alert-dismissible fade show custom-alert`;
            alertDiv.setAttribute('role', 'alert');
            alertDiv.innerHTML = `
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
    </script>
</body>

</html>