<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Sign Up | Learnix - Empowering Education</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Intuitive dashboard for instructors to create, manage courses, track student progress, and engage learners effectively." />
    <meta name="author" content="Learnix Team" />
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
            <img src="assets/images/logo.png" alt="Logo" height="18">
        </a>
    </div>

    <!-- Signup Form -->
    <div class="card-body p-4">
        <div class="text-center w-75 m-auto">
            <h4 class="text-dark-50 text-center mt-0 fw-bold">Join Learnix as an Instructor</h4>
            <p class="text-muted mb-4">Don't have an instructor account? Sign up now and start creating courses in just a few steps.</p>
        </div>

        <form id="signupForm">
            <div class="mb-3">
                <label for="firstname" class="form-label">First Name</label>
                <input class="form-control" type="text" id="firstname" name="firstName" placeholder="Enter your first name" required>
            </div>
            <div class="mb-3">
                <label for="lastname" class="form-label">Last Name</label>
                <input class="form-control" type="text" id="lastname" name="lastName" placeholder="Enter your last name" required>
            </div>
            <div class="mb-3">
                <label for="emailaddress" class="form-label">Email address</label>
                <input class="form-control" type="email" id="emailaddress" name="email" placeholder="Enter your email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group input-group-merge">
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                    <div class="input-group-text" data-password="false">
                        <span class="password-eye"></span>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="checkbox-signup" required>
                    <label class="form-check-label" for="checkbox-signup">I accept <a href="#" class="text-muted">Terms and Conditions</a></label>
                </div>
            </div>
            <div class="mb-3 text-center">
                <button id="submitButton" class="btn btn-primary" type="submit">Sign Up</button>
            </div>
        </form>
    </div>

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
</div>

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




<script>
    // Show alert notification function
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
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

// Handle Signup Form Submission
document.getElementById("signupForm").onsubmit = async (e) => {
    e.preventDefault();
    const submitButton = document.getElementById("submitButton");
    submitButton.disabled = true;
    submitButton.innerHTML = "Signing up...";

    // Create overlay to prevent interaction
    createOverlay("Processing your registration...");

    const formData = new FormData(e.target);

    try {
        const response = await fetch("../backend/auth/instructor/signup.php", {
            method: "POST",
            body: formData,
        });

        const result = await response.text();

        if (response.ok) {
            if (result === "verification_sent") {
                removeOverlay();
                showAlert('success', "A verification code has been sent to your email.");
                const modal = new bootstrap.Modal(document.getElementById("verificationModal"));
                modal.show();
            } else if (result === "unverified") {
                removeOverlay();
                showAlert('warning', "Your account is not verified. Sign In to verify it now!");
                setTimeout(() => {
                    window.location.href = "../instructor/signin.php";
                }, 3000);
            } else {
                removeOverlay();
                showAlert('danger', result || "An error occurred. Please try again.");
            }
        } else {
            removeOverlay();
            showAlert('danger', "Server returned an error. Please try again.");
        }
    } catch (error) {
        removeOverlay();
        showAlert('danger', "There was an error processing your request.");
    } finally {
        submitButton.disabled = false;
        submitButton.innerHTML = "Sign Up";
    }
};

// Function to redirect with overlay
function redirectWithOverlay(url, message = "Redirecting you...", delay = 3000) {
    // Create overlay with message
    createOverlay(message);
    
    // Set timeout for redirection
    setTimeout(() => {
        window.location.href = url;
    }, delay);
}

// Handle Verification Form Submission - Updated with overlay during redirect
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
            showAlert('success', "✅ Account verified successfully! You can now Sign In...");

            // Close the modal
            const modal = bootstrap.Modal.getInstance(document.getElementById("verificationModal"));
            modal.hide();

            // Use the new redirect with overlay function
            redirectWithOverlay("../instructor/signin.php", "Account verified! Redirecting you to login...");
        } else {
            removeOverlay();
            showAlert('danger', result.message || "Verification failed. Please try again.");
        }
    } catch (error) {
        removeOverlay();
        showAlert('danger', "There was an error processing your request.");
    } finally {
        verifyButton.disabled = false;
        verifyButton.innerHTML = "Verify";
    }
};

// Handle Signup Form Submission - Updated for unverified redirect
document.getElementById("signupForm").onsubmit = async (e) => {
    e.preventDefault();
    const submitButton = document.getElementById("submitButton");
    submitButton.disabled = true;
    submitButton.innerHTML = "Signing up...";

    // Create overlay to prevent interaction
    createOverlay("Processing your registration...");

    const formData = new FormData(e.target);

    try {
        const response = await fetch("../backend/auth/instructor/signup.php", {
            method: "POST",
            body: formData,
        });

        const result = await response.text();

        if (response.ok) {
            if (result === "verification_sent") {
                removeOverlay();
                showAlert('success', "A verification code has been sent to your email.");
                const modal = new bootstrap.Modal(document.getElementById("verificationModal"));
                modal.show();
            } else if (result === "unverified") {
                removeOverlay();
                showAlert('warning', "Your account is not verified. Sign In to verify it now!");
                // Use the new redirect with overlay function
                redirectWithOverlay("../instructor/signin.php", "Redirecting you to login for verification...");
            } else {
                removeOverlay();
                showAlert('danger', result || "An error occurred. Please try again.");
            }
        } else {
            removeOverlay();
            showAlert('danger', "Server returned an error. Please try again.");
        }
    } catch (error) {
        removeOverlay();
        showAlert('danger', "There was an error processing your request.");
    } finally {
        submitButton.disabled = false;
        submitButton.innerHTML = "Sign Up";
    }
};
// Add auto-tab functionality for verification code inputs
document.querySelectorAll('.verification-input').forEach((input, index, inputs) => {
    input.addEventListener('input', function() {
        if (this.value.length >= this.maxLength) {
            // Move to next input if available
            if (index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        }
    });

    // Handle backspace to move to previous input
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && this.value === '' && index > 0) {
            inputs[index - 1].focus();
        }
    });
});
</script>


                    <div class="row mt-3">
                        <div class="col-12 text-center">
                            <p class="text-muted">Already have account? <a href="signin.php" class="text-muted ms-1"><b>Sign In</b></a></p>
                        </div> <!-- end col-->
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
        © Learnix. <script>
            document.write(new Date().getFullYear())
        </script> All rights reserved.
    </footer>

    <!-- bundle -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.min.js"></script>

</body>

</html>