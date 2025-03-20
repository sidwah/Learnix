<div id="loginOffcanvasFormSignup" style="display: none; opacity: 0">
    <!-- Heading -->
    <div class="text-center mb-7">
        <h3 class="modal-title">Sign up</h3>
        <p>Fill out the form to create your account</p>
    </div>
    <!-- Signup Form -->
    <form class="js-validate needs-validation" id="signupForm" novalidate>
        <!-- First Name -->
        <div class="mb-3">
            <label class="form-label" for="firstName">First Name</label>
            <input
                type="text"
                class="form-control form-control-lg"
                name="firstName"
                id="firstName"
                placeholder="Enter your first name"
                required />
            <span class="invalid-feedback">Please enter a valid first name.</span>
        </div>

        <!-- Last Name -->
        <div class="mb-3">
            <label class="form-label" for="lastName">Last Name</label>
            <input
                type="text"
                class="form-control form-control-lg"
                name="lastName"
                id="lastName"
                placeholder="Enter your last name"
                required />
            <span class="invalid-feedback">Please enter a valid last name.</span>
        </div>

        <!-- Email -->
        <div class="mb-3">
            <label class="form-label" for="email">Email Address</label>
            <input
                type="email"
                class="form-control form-control-lg"
                name="email"
                id="email"
                placeholder="Enter a valid email address"
                required />
            <span class="invalid-feedback">Please enter a valid email address.</span>
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <input
                type="password"
                class="form-control form-control-lg"
                name="password"
                id="password"
                placeholder="At least 8 characters"
                minlength="8"
                required />
            <span class="invalid-feedback">Your password is invalid. Please try again.</span>
        </div>

        <!-- Confirm Password -->
        <div class="mb-3">
            <label class="form-label" for="confirmPassword">Confirm Password</label>
            <input
                type="password"
                class="form-control form-control-lg"
                name="confirmPassword"
                id="confirmPassword"
                placeholder="Re-enter your password"
                minlength="8"
                required
                data-hs-validation-equal-field="#password" />
            <span class="invalid-feedback">Passwords do not match. Please try again.</span>
        </div>

        <div class="d-grid gap-3 text-center">
            <button type="submit" class="btn btn-primary btn-lg" id="submitButton">Sign up</button>
            <br>
            <p>
                Already have an account?
                <a class="js-animation-link link" href="javascript:;" role="button"
                    data-hs-show-animation-options='{"targetSelector": "#loginOffcanvasFormLogin", "groupName": "idForm"}'>Sign in</a>
            </p>
        </div>
    </form>

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
                            <input type="text" maxlength="1" class="form-control text-center verification-input" required name="code[]" />
                            <input type="text" maxlength="1" class="form-control text-center verification-input" required name="code[]" />
                            <input type="text" maxlength="1" class="form-control text-center verification-input" required name="code[]" />
                            <input type="text" maxlength="1" class="form-control text-center verification-input" required name="code[]" />
                            <input type="text" maxlength="1" class="form-control text-center verification-input" required name="code[]" />
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
// Show alert notification function - improved version
function showAlert(type, message) {
    // Remove any existing alerts first to prevent stacking
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => {
        if (alert.parentNode) {
            alert.parentNode.removeChild(alert);
        }
    });

    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : type === 'info' ? 'info' : 'danger'} alert-dismissible fade show`;
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
    alertDiv.style.maxWidth = '80%';
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
    // First, check if there's already an overlay and remove it
    removeOverlay();
    
    const overlay = document.createElement('div');
    overlay.id = 'pageOverlay';
    overlay.style.position = 'fixed';
    overlay.style.top = '0';
    overlay.style.left = '0';
    overlay.style.width = '100%';
    overlay.style.height = '100%';
    overlay.style.backgroundColor = 'rgba(255, 255, 255, 0.8)';
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
    
    // Prevent any interaction with the page while overlay is active
    document.body.style.overflow = 'hidden';
}

// Remove overlay
function removeOverlay() {
    const overlay = document.getElementById('pageOverlay');
    if (overlay) {
        // Re-enable scrolling
        document.body.style.overflow = '';
        document.body.removeChild(overlay);
    }
}

// Show confirmation dialog
function showConfirmation(message) {
    return new Promise((resolve) => {
        // Check for existing confirmation modal and remove it
        const existingModal = document.querySelector('.modal.confirmation-dialog');
        if (existingModal) {
            document.body.removeChild(existingModal);
        }
        
        const confirmDiv = document.createElement('div');
        confirmDiv.className = 'modal fade confirmation-dialog';
        confirmDiv.setAttribute('tabindex', '-1');
        confirmDiv.setAttribute('aria-hidden', 'true');
        
        confirmDiv.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelBtn">Cancel</button>
                        <button type="button" class="btn btn-primary" id="confirmBtn">Confirm</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(confirmDiv);
        
        const modal = new bootstrap.Modal(confirmDiv);
        modal.show();
        
        document.getElementById('cancelBtn').addEventListener('click', () => {
            modal.hide();
            confirmDiv.addEventListener('hidden.bs.modal', () => {
                if (document.body.contains(confirmDiv)) {
                    document.body.removeChild(confirmDiv);
                }
                resolve(false);
            }, { once: true });
        });
        
        document.getElementById('confirmBtn').addEventListener('click', () => {
            modal.hide();
            confirmDiv.addEventListener('hidden.bs.modal', () => {
                if (document.body.contains(confirmDiv)) {
                    document.body.removeChild(confirmDiv);
                }
                resolve(true);
            }, { once: true });
        });
        
        confirmDiv.addEventListener('hidden.bs.modal', () => {
            if (document.body.contains(confirmDiv)) {
                document.body.removeChild(confirmDiv);
            }
            resolve(false);
        }, { once: true });
    });
}

// Improved signup form submission
document.getElementById("signupForm").onsubmit = async (e) => {
    e.preventDefault();
    const submitButton = document.getElementById("submitButton");
    submitButton.disabled = true;
    submitButton.innerHTML = "Signing up...";
  
    // Create overlay with a message to prevent interaction
    createOverlay("Creating your account...");
  
    const formData = new FormData(e.target);
    const email = document.getElementById("email").value;
  
    try {
        const response = await fetch("../backend/auth/student/signup.php", {
            method: "POST",
            body: formData,
        });
  
        const resultText = await response.text();
        let result;
  
        // Try to parse as JSON, fallback to text if not JSON
        try {
            result = JSON.parse(resultText);
            console.log("Response parsed as JSON:", result);
        } catch (e) {
            console.log("Response is not JSON:", resultText);
            result = {
                status: response.ok ? "success" : "error",
                message: resultText
            };
        }
  
        // Check for verification_sent in either JSON format or text response
        if ((result.status === "success" && result.message === "verification_sent") || 
            resultText.includes("verification_sent")) {
            
            removeOverlay();
            showAlert('success', "Registration successful! Please check your email for verification code.");
  
            // Show verification modal with debugging
            console.log("Showing verification modal for:", email);
            showVerificationModal(email);
        } else if (result.message === "unverified" || resultText === "unverified" || 
                  (result.message && result.message.includes("not verified"))) {
            
            removeOverlay();
            const shouldVerify = await showConfirmation("Your account is not verified. Would you like to verify it now?");
            
            if (shouldVerify) {
                createOverlay("Sending verification code...");
                await resendVerificationCode(email);
            }
        } else if (resultText === "registered" || 
                  (result.status === "success" && result.message && 
                   result.message.includes("successful"))) {
            
            showAlert('success', "Registration successful! Redirecting...");
            
            // Force redirection to index page after successful registration
            setTimeout(() => {
                window.location.href = "../index.php";
            }, 2000);
        } else {
            // Show error message
            removeOverlay();
            showAlert('danger', result.message || resultText || "There was an issue with your registration.");
        }
    } catch (error) {
        console.error("Signup error:", error);
        removeOverlay();
        showAlert('danger', "Network error: Please check your connection.");
    } finally {
        submitButton.disabled = false;
        submitButton.innerHTML = "Sign up";
    }
};

// Improved resend verification code function
async function resendVerificationCode(email) {
    showAlert('info', "Sending verification code to your email...");
  
    try {
        const response = await fetch("../backend/auth/student/resend_code.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                email
            }),
        });
  
        const resultText = await response.text();
        let result;
  
        try {
            result = JSON.parse(resultText);
        } catch (e) {
            result = {
                status: "unknown",
                message: resultText
            };
        }
  
        if (result.status === "success" || resultText === "verification_resent") {
            removeOverlay();
            showAlert('success', "A new verification code has been sent to your email.");
            showVerificationModal(email);
        } else {
            removeOverlay();
            showAlert('danger', result.message || resultText || "Failed to resend the verification code.");
        }
    } catch (error) {
        console.error("Error resending verification code:", error);
        removeOverlay();
        showAlert('danger', "Network error: Unable to resend verification code.");
    }
}

// Function to show verification modal and request new code
function showVerificationModal(email) {
    try {
        const modalElement = document.getElementById("verificationModal");
        if (!modalElement) {
            console.error("Verification modal element not found");
            return;
        }
      
        // Check if Bootstrap is available
        if (typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        } else {
            // Fallback for when Bootstrap JS isn't loaded
            console.warn("Bootstrap not found, using fallback modal display");
            modalElement.style.display = 'block';
            modalElement.classList.add('show');
            document.body.classList.add('modal-open');
          
            // Add backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
        }
  
        // Set up the verification code input behavior
        setupVerificationInputs();
  
        // Set up form submission with the email context
        setupVerificationFormSubmission(email);
    } catch (error) {
        console.error("Error showing verification modal:", error);
        showAlert('danger', "Please check your email for a verification code and contact support if you have issues.");
    }
}

// Set up verification code input fields for better UX
function setupVerificationInputs() {
    const inputs = document.querySelectorAll('.verification-input');
    
    if (inputs.length === 0) {
        console.error("Verification input fields not found");
        return;
    }
  
    // Clear any previous values
    inputs.forEach(input => input.value = '');
  
    // Focus the first input
    inputs[0].focus();
  
    inputs.forEach((input, index) => {
        // Auto-advance to next input
        input.addEventListener('input', function() {
            if (this.value.length === this.maxLength && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
  
            // If last input and all are filled, auto-submit
            if (index === inputs.length - 1 && this.value.length === this.maxLength) {
                const allFilled = Array.from(inputs).every(inp => inp.value.length === 1);
                if (allFilled) {
                    // Optional: auto-submit
                    // document.getElementById('verificationForm').querySelector('button[type="submit"]').click();
                }
            }
        });
  
        // Allow backspace to go back to previous input
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !this.value && index > 0) {
                inputs[index - 1].focus();
            }
        });
  
        // Paste handling - distribute across inputs
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            const digits = pastedText.replace(/\D/g, '').split('').slice(0, inputs.length);
  
            if (digits.length > 0) {
                // Fill inputs with pasted digits
                digits.forEach((digit, i) => {
                    if (index + i < inputs.length) {
                        inputs[index + i].value = digit;
                    }
                });
  
                // Focus the next empty input or the last one
                const nextEmptyIndex = Array.from(inputs).findIndex(inp => !inp.value);
                if (nextEmptyIndex !== -1) {
                    inputs[nextEmptyIndex].focus();
                } else {
                    inputs[inputs.length - 1].focus();
                }
            }
        });
    });
}

// Handle verification form submission
function setupVerificationFormSubmission(email) {
    const form = document.getElementById('verificationForm');
    
    if (!form) {
        console.error("Verification form not found");
        return;
    }
  
    form.onsubmit = async (e) => {
        e.preventDefault();
  
        const verifyButton = e.target.querySelector("button[type='submit']");
        verifyButton.disabled = true;
        verifyButton.innerHTML = "Verifying...";
  
        // Create overlay to prevent interaction
        createOverlay("Verifying your account...");
  
        const verificationInputs = Array.from(e.target.querySelectorAll("input[name='code[]']"))
            .map(input => input.value.trim());
  
        const code = verificationInputs.join("");
  
        if (code.length !== 5 || isNaN(code)) {
            removeOverlay();
            showAlert('danger', "Please enter a valid 5-digit verification code.");
            verifyButton.disabled = false;
            verifyButton.innerHTML = "Verify";
            return;
        }
  
        try {
            const response = await fetch("../backend/auth/student/verify_account.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    email,
                    code
                }),
            });
  
            let result;
            try {
                result = await response.json();
            } catch (e) {
                const text = await response.text();
                result = {
                    status: response.ok ? "success" : "error",
                    message: text
                };
            }
  
            if (response.ok && (result.status === "success" || result === "verified")) {
                try {
                    // Hide the modal
                    const modalInstance = bootstrap.Modal.getInstance(document.getElementById("verificationModal"));
                    if (modalInstance) {
                        modalInstance.hide();
                    } else {
                        // Fallback if bootstrap instance not found
                        const modalElement = document.getElementById("verificationModal");
                        modalElement.style.display = 'none';
                        modalElement.classList.remove('show');
                        document.body.classList.remove('modal-open');
                        const backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) backdrop.remove();
                    }
                } catch (modalError) {
                    console.error("Error hiding modal:", modalError);
                }
  
                // Show success message
                showAlert('success', "Account verified successfully! Sign In...");
  
                // Redirect to index page after a short delay
                setTimeout(() => {
                    window.location.href = "../index.php";
                }, 2000);
                
                // Note: We intentionally don't remove the overlay here to prevent user interaction during redirect
            } else {
                removeOverlay();
                showAlert('danger', result.message || "Verification failed. Please try again.");
            }
        } catch (error) {
            console.error("Verification error:", error);
            removeOverlay();
            showAlert('danger', "Network error: Unable to verify your account.");
        } finally {
            verifyButton.disabled = false;
            verifyButton.innerHTML = "Verify";
        }
    };
}    </script>
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
</div>