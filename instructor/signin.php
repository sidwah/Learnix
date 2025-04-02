<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Sign In | Learnix - Empowering Education</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="An intuitive instructor dashboard to manage courses, track student progress, and enhance the learning experience." name="description" />
    <meta content="Learnix Development Team" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- App css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

    <meta name="sourcemap" content="off">

</head>

<body class="loading authentication-bg" data-layout-config='{"darkMode":false}'>
    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xxl-4 col-lg-5">
                <div class="card">
    <!-- Logo -->
    <div class="card-header pt-4 pb-4 text-center bg-primary">
        <a href="index.php">
            <span><img src="assets/images/logo.png" alt="Learnix Logo" height="18"></span>
        </a>
    </div>

    <div class="card-body p-4">
        <div class="text-center w-75 m-auto">
            <h4 class="text-dark-50 text-center pb-0 fw-bold">Instructor Sign In</h4>
            <p class="text-muted mb-4">Enter your email address and password to access your instructor dashboard.</p>
        </div>

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

            <div class="mb-3 mb-0 text-center">
                <button class="btn btn-primary" type="submit" id="signinButton">Sign In</button>
            </div>
        </form>
    </div> <!-- end card-body -->
</div> <!-- end card -->

<!-- Verification Modal -->
<div class="modal" id="verificationModal" tabindex="-1" aria-labelledby="verificationModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
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

<script>
    // Show alert notification function
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'danger'} alert-dismissible fade show`;
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        // Position the alert
        alertDiv.style.position = 'fixed';
        alertDiv.style.top = '20px';
        alertDiv.style.left = '50%';
        alertDiv.style.transform = 'translateX(-50%)';
        alertDiv.style.zIndex = '9999';
        alertDiv.style.minWidth = '300px';
        alertDiv.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
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
        signinButton.innerHTML = "Signing in...";

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
            signinButton.innerHTML = "Sign In";
        }
    };

    // Handle Verification Form Submission
    document.getElementById("verificationForm").onsubmit = async (e) => {
        e.preventDefault();

        const verifyButton = e.target.querySelector("button[type='submit']");
        verifyButton.disabled = true;
        verifyButton.innerHTML = "Verifying...";

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
    });
</script>

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
</style>


                    <div class="row mt-3">
                        <div class="col-12 text-center">
                            <p class="text-muted">Don't have an account? <a href="signup.php" class="text-muted ms-1"><b>Sign Up</b></a></p>
                        </div> <!-- end col -->
                    </div>
                    <!-- end row -->

                </div> <!-- end col -->
            </div>
            <!-- end row -->
        </div>
        <!-- end container -->
    </div>
    <!-- end page -->

    <footer class="footer footer-alt">
        © Learnix .<script>
            document.write(new Date().getFullYear())
        </script> All rights reserved.
    </footer>

    <!-- bundle -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.min.js"></script>

</body>

</html>