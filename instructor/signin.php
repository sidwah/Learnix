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
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style"/>

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
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
                        <p class="text-muted">Don't have an account? <a href="signup.php" class="text-muted ms-1"><b>Sign Up</b></a></p>
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

    <!-- bundle -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
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

        // Handle Signin Form Submission
        document.getElementById("signinForm").onsubmit = async (e) => {
            e.preventDefault();
            const signinButton = document.getElementById("signinButton");
            signinButton.disabled = true;
            signinButton.innerHTML = '<i class="mdi mdi-loading mdi-spin me-1"></i> Signing in...';

            // Create overlay to prevent interaction with loading message
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
                        showAlert('warning', "⚠️ Your password must be reset before signing in.");
                        setTimeout(() => {
                            window.location.href = "../backend/auth/instructor/reset_password.php";
                        }, 3000);
                    } else if (result.status === "unverified") {
                        // Skip confirmation and directly resend verification code
                        removeOverlay();
                        createOverlay("Sending verification code...");
                        
                        const resendResponse = await fetch("../backend/auth/instructor/resend_verification.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify({
                                email: formData.get("email")
                            }),
                        });

                        const resendResult = await resendResponse.json();

                        if (resendResponse.ok && resendResult.status === "success") {
                            removeOverlay();
                            showAlert('success', "✅ Verification code sent to your email. Please check your inbox.");

                            // Store email for verification
                            sessionStorage.setItem('pendingVerificationEmail', formData.get("email"));

                            // Reset and initialize verification inputs
                            const verificationInputs = document.querySelectorAll('.verification-input');
                            verificationInputs.forEach(input => {
                                input.value = '';
                            });

                            // Show verification modal
                            const modal = new bootstrap.Modal(document.getElementById("verificationModal"));
                            modal.show();
                        } else {
                            removeOverlay();
                            showAlert('danger', resendResult.message || "❌ Failed to send verification code.");
                        }
                    } else if (result.status === "lockout") {
                        // Handle account lockout
                        removeOverlay();
                        showAlert('danger', "❌ Your account has been temporarily locked due to multiple failed login attempts. Please try again later or reset your password.");
                    } else {
                        removeOverlay();
                        showAlert('danger', result.message || "❌ Sign in failed. Please check your credentials.");
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

        // Handle Verification Form Submission
        document.getElementById("verificationForm").onsubmit = async (e) => {
            e.preventDefault();

            const verifyButton = e.target.querySelector("button[type='submit']");
            verifyButton.disabled = true;
            verifyButton.innerHTML = '<i class="mdi mdi-loading mdi-spin me-1"></i> Verifying...';

            // Create overlay to prevent interaction
            createOverlay("Verifying your account...");

            const verificationInputs = Array.from(e.target.querySelectorAll("input[name='code[]']")).map((input) => input.value.trim());
            const code = verificationInputs.join("");

            try {
                const response = await fetch("../backend/auth/instructor/verify_account.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        code
                    }),
                });

                const result = await response.json();

                if (response.ok && result.status === "success") {
                    removeOverlay();
                    createOverlay("Verification successful! Redirecting...");
                    showAlert('success', "✅ Verification successful! You can now sign in.");

                    // Clear stored email
                    sessionStorage.removeItem('pendingVerificationEmail');

                    // Close the modal if it exists
                    const modalElement = document.getElementById("verificationModal");
                    if (modalElement) {
                        const verificationModal = bootstrap.Modal.getInstance(modalElement);
                        if (verificationModal) {
                            verificationModal.hide();
                        }
                    }

                    // Redirect after verification
                    setTimeout(() => {
                        window.location.reload();
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

        // Set up verification input auto-advance behavior when the DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            const verificationInputs = document.querySelectorAll('.verification-input');

            // Only digits regex
            const onlyDigits = /^[0-9]$/;

            verificationInputs.forEach((input, index) => {
                // Move to next input when a character is entered
                input.addEventListener('input', function(e) {
                    // Only allow digits
                    if (!onlyDigits.test(this.value) && this.value !== '') {
                        this.value = '';
                        return;
                    }
                    
                    if (this.value.length === this.maxLength) {
                        if (index < verificationInputs.length - 1) {
                            verificationInputs[index + 1].focus();
                        } else {
                            // If this is the last input, check if all inputs are filled
                            const allFilled = Array.from(verificationInputs).every(input => input.value.length === 1);
                            if (allFilled) {
                                // Submit the form automatically when all digits are entered
                                document.getElementById("verificationForm").dispatchEvent(new Event('submit'));
                            }
                        }
                    }
                });

                // Handle paste event for verification code
                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const pastedData = (e.clipboardData || window.clipboardData).getData('text');
                    
                    // Check if pasted data is a 5-digit number
                    if (/^\d{5}$/.test(pastedData)) {
                        // Distribute digits across the inputs
                        verificationInputs.forEach((input, i) => {
                            if (i < pastedData.length) {
                                input.value = pastedData[i];
                            }
                        });
                        
                        // Auto-submit if all fields are filled
                        document.getElementById("verificationForm").dispatchEvent(new Event('submit'));
                    }
                });

                // Go back to previous input on backspace if current is empty
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && this.value.length === 0) {
                        if (index > 0) {
                            verificationInputs[index - 1].focus();
                        }
                    }
                });
            });

            // Password toggle functionality - direct approach
            document.addEventListener('DOMContentLoaded', function() {
                // Add a direct click handler for the password toggle
                document.body.addEventListener('click', function(e) {
                    // Target both the eye icon and its parent
                    if (e.target.classList.contains('password-eye') || 
                        (e.target.classList.contains('input-group-text') && e.target.querySelector('.password-eye'))) {
                        
                        // Get the password input
                        const passwordInput = document.getElementById('password');
                        // Get the toggle element (parent of eye icon)
                        const toggleElement = e.target.classList.contains('password-eye') ? 
                                             e.target.parentElement : e.target;
                        
                        // Toggle password visibility
                        if (passwordInput.type === 'password') {
                            passwordInput.type = 'text';
                            toggleElement.setAttribute('data-password', 'true');
                        } else {
                            passwordInput.type = 'password';
                            toggleElement.setAttribute('data-password', 'false');
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>