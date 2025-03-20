<div id="loginOffcanvasFormLogin">
    <!-- Heading -->
    <div class="text-center mb-7">
        <h3 class="modal-title">Sign in to Learnix</h3>
        <p>Log in to access your learning portal</p>
    </div>
    <!-- End Heading -->

    <form action="../backend/auth/student/signin.php" class="js-validate needs-validation" novalidate>
        <!-- Form -->
        <div class="mb-3">
            <label class="form-label" for="loginOffcanvasFormLoginEmail">Your Email</label>
            <input
                type="email"
                class="form-control form-control-lg"
                name="email"
                id="loginOffcanvasFormLoginEmail"
                placeholder="e.g., johndoe@email.com"
                aria-label="e.g., johndoe@email.com"
                required />
            <span class="invalid-feedback">Please enter your valid email.</span>
        </div>
        <!-- End Form -->

        <!-- Form -->
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <label class="form-label" for="loginOffcanvasFormLoginPassword">Password</label>

                <a
                    class="js-animation-link form-label-link"
                    href="javascript:;"
                    data-hs-show-animation-options='{"targetSelector": "#loginOffcanvasFormResetPassword", "groupName": "idForm"}'>Forgot Password?</a>
            </div>

            <input
                type="password"
                class="form-control form-control-lg"
                name="password"
                id="loginOffcanvasFormLoginPassword"
                placeholder="Enter your password"
                aria-label="Enter your password"
                required
                minlength="8" />
            <span class="invalid-feedback">Please enter your correct password.</span>
        </div>
        <!-- End Form -->

        <div class="d-grid gap-3 text-center">
            <button type="submit" class="btn btn-primary btn-lg">Sign in</button>

            <br>

            <p>
                Don't have an account yet?
                <a class="js-animation-link link" href="javascript:;" role="button"
                    data-hs-show-animation-options='{"targetSelector": "#loginOffcanvasFormSignup", "groupName": "idForm"}'>Sign up</a>
            </p>
        </div>
    </form>
</div>


<!-- Verification Modal with Improved UI -->
<div class="modal fade" id="verificationModal" tabindex="-1" aria-labelledby="verificationModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="text-align: center;">
                <h5 class="modal-title" id="verificationModalLabel">Verify Your Account</h5>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-envelope-open-text text-primary" style="font-size: 48px;"></i>
                </div>
                <p class="text-center">A 5-digit verification code has been sent to your email. Enter the code below to verify your account.</p>
                <form id="verificationForm">
                    <div class="d-flex justify-content-center gap-2 mb-4">
                        <input type="text" maxlength="1" class="form-control form-control-lg text-center verification-input" required name="code[]" style="width: 3rem; height: 3rem; font-size: 1.5rem;" />
                        <input type="text" maxlength="1" class="form-control form-control-lg text-center verification-input" required name="code[]" style="width: 3rem; height: 3rem; font-size: 1.5rem;" />
                        <input type="text" maxlength="1" class="form-control form-control-lg text-center verification-input" required name="code[]" style="width: 3rem; height: 3rem; font-size: 1.5rem;" />
                        <input type="text" maxlength="1" class="form-control form-control-lg text-center verification-input" required name="code[]" style="width: 3rem; height: 3rem; font-size: 1.5rem;" />
                        <input type="text" maxlength="1" class="form-control form-control-lg text-center verification-input" required name="code[]" style="width: 3rem; height: 3rem; font-size: 1.5rem;" />
                    </div>
                    <div class="d-grid gap-3">
                        <button type="submit" class="btn btn-primary btn-lg">Verify Account</button>
                    </div>
                </form>
                <div class="text-center mt-3 text-muted small">
                    <p>Didn't receive the code? Check your spam folder or request a new code.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // Improved login form submission with better error handling and user feedback
document.querySelector("form[action='../backend/auth/student/signin.php']").onsubmit = async (e) => {
    e.preventDefault();

    // Get the submit button and disable it
    const submitButton = e.target.querySelector("button[type='submit']");
    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Signing in...';

    // Create overlay with message to prevent interaction
    createOverlay("Signing you in...");

    const formData = new FormData(e.target);
    const email = e.target.querySelector("input[name='email']").value;

    try {
        const response = await fetch('../backend/auth/student/signin.php', {
            method: 'POST',
            body: formData,
        });

        const resultText = await response.text();
        let result;

        // Try to parse as JSON, fallback to text if not JSON
        try {
            result = JSON.parse(resultText);
            console.log("Response:", result);
        } catch (e) {
            console.log("Response is not JSON:", resultText);
            result = {
                status: response.ok ? "success" : "error",
                message: resultText
            };
        } 

        // Check for successful login
        if (result.status === "success") {
            // Display personalized success message if we have user info
            const userName = result.user?.first_name ? result.user.first_name : '';
            showAlert('success', userName ? 
                `Welcome back, ${userName}! Redirecting to your dashboard...` : 
                'Sign in successful! Redirecting to your dashboard...');

            // Keep overlay active during redirect
            setTimeout(() => {
                window.location.href = "../student/"; // Redirect to dashboard
            }, 2000);
        }
        // Check if account is locked out
        else if (result.lockout) {
            removeOverlay();
            showAlert('danger', `${result.message} Try again later.`, 8000);
            submitButton.disabled = false;
            submitButton.innerHTML = 'Sign in';
        }
        // Check if account needs verification
        else if (result.verification_required || 
                (result.message && result.message.includes("not verified"))) {

            removeOverlay();
            showAlert('warning', 'Your account needs verification. We\'ll send a verification code to your email.', 5000);

            // Show verification modal and request new code (if not already sent)
            setTimeout(() => {
                if (result.code_sent) {
                    // Code already sent from backend
                    showAlert('info', 'A verification code has been sent to your email.', 5000);
                    showVerificationModalWithEmail(email);
                } else {
                    // Code needs to be requested
                    showVerificationModal(email);
                }
            }, 1000);

            submitButton.disabled = false;
            submitButton.innerHTML = 'Sign in';
        }
        // Handle too many attempts
        // Handle too many attempts
else if (result.attempts_left !== undefined) {
    removeOverlay();
    let message = result.message;
    
    // Add attempts left info if available - but ONLY if we know it's a real account
    if (result.attempts_left > 0) {
        message += ` You have ${result.attempts_left} attempt${result.attempts_left !== 1 ? 's' : ''} remaining.`;
    }
    
    showAlert('danger', message, 6000);
    submitButton.disabled = false;
    submitButton.innerHTML = 'Sign in';
    
    // Visual feedback on the form
    const passwordField = document.getElementById('loginOffcanvasFormLoginPassword');
    passwordField.classList.add('is-invalid');
    setTimeout(() => {
        passwordField.classList.remove('is-invalid');
    }, 2000);
}
// Handle other errors (no attempts info - likely non-existent email)
else {
    removeOverlay();
    showAlert('danger', result.message || resultText || "Invalid email or password. Please try again.");
    submitButton.disabled = false;
    submitButton.innerHTML = 'Sign in';
}
    } catch (error) {
        console.error("Login error:", error);
        removeOverlay();
        showAlert('danger', 'Network error: Unable to connect to the server. Please check your internet connection.');

        submitButton.disabled = false;
        submitButton.innerHTML = 'Sign in';
    }
};

// Shows verification modal with pre-existing email (code already sent)
function showVerificationModalWithEmail(email) {
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

        // Update modal to show masked email for security
        updateModalWithMaskedEmail(email);

        // Set up the verification code input behavior
        setupVerificationInputs();

        // Set up form submission with the email context
        setupVerificationFormSubmission(email);
    } catch (error) {
        console.error("Error showing verification modal:", error);
        showAlert('danger', "Error showing verification modal. Please try again later.");
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

        // First send the verification code
        createOverlay("Sending verification code...");

        fetch("../backend/auth/student/resend_code.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    email
                })
            })
            .then(response => response.text())
            .then(resultText => {
                removeOverlay();

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
                    showAlert('success', "A verification code has been sent to your email.", 5000);

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

                    // Update modal to show masked email for security
                    updateModalWithMaskedEmail(email);

                    // Set up the verification code input behavior
                    setupVerificationInputs();

                    // Set up form submission with the email context
                    setupVerificationFormSubmission(email);
                } else {
                    showAlert('danger', result.message || resultText || "Failed to send verification code.");
                }
            })
            .catch(error => {
                removeOverlay();
                console.error("Error sending verification code:", error);
                showAlert('danger', "Network error: Unable to send verification code.");
            });
    } catch (error) {
        removeOverlay();
        console.error("Error showing verification modal:", error);
        showAlert('danger', "Error showing verification modal. Please try again later.");
    }
}

// Function to mask email address for privacy in the modal
function updateModalWithMaskedEmail(email) {
    const modalText = document.querySelector("#verificationModal .modal-body p");
    if (!modalText) return;
    
    // Mask email (show first 2 chars, hide middle, show domain)
    const [username, domain] = email.split('@');
    const maskedUsername = username.substring(0, 2) + '*'.repeat(Math.max(1, username.length - 2));
    const maskedEmail = `${maskedUsername}@${domain}`;
    
    modalText.innerHTML = `A 5-digit verification code has been sent to <strong>${maskedEmail}</strong>. Enter the code below to verify your account.`;
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
        input.addEventListener('input', function(e) {
            // Only allow numbers
            this.value = this.value.replace(/[^0-9]/g, '');
            
            if (this.value.length === this.maxLength && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }

            // If last input and all are filled, auto-submit
            if (index === inputs.length - 1 && this.value.length === this.maxLength) {
                const allFilled = Array.from(inputs).every(inp => inp.value.length === 1);
                if (allFilled) {
                    // Auto-submit after a brief delay to allow user to see the completed state
                    setTimeout(() => {
                        document.getElementById('verificationForm').querySelector('button[type="submit"]').click();
                    }, 300);
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
                    // If all filled, auto-submit after a short delay
                    setTimeout(() => {
                        document.getElementById('verificationForm').querySelector('button[type="submit"]').click();
                    }, 300);
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

    // Add resend code button if it doesn't exist
    if (!document.getElementById('resendCodeBtn')) {
        const submitBtn = form.querySelector('button[type="submit"]');
        const resendBtn = document.createElement('button');
        resendBtn.type = 'button';
        resendBtn.id = 'resendCodeBtn';
        resendBtn.className = 'btn btn-outline-secondary';
        resendBtn.innerHTML = 'Resend Code';
        
        // Start countdown for resend button
        let countdown = 60;
        resendBtn.disabled = true;
        resendBtn.innerHTML = `Resend Code (${countdown}s)`;
        
        const countdownInterval = setInterval(() => {
            countdown--;
            if (countdown <= 0) {
                clearInterval(countdownInterval);
                resendBtn.disabled = false;
                resendBtn.innerHTML = 'Resend Code';
            } else {
                resendBtn.innerHTML = `Resend Code (${countdown}s)`;
            }
        }, 1000);
        
        resendBtn.addEventListener('click', () => {
            // Reset countdown
            countdown = 60;
            resendBtn.disabled = true;
            resendBtn.innerHTML = `Resend Code (${countdown}s)`;
            
            const countdownInterval = setInterval(() => {
                countdown--;
                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    resendBtn.disabled = false;
                    resendBtn.innerHTML = 'Resend Code';
                } else {
                    resendBtn.innerHTML = `Resend Code (${countdown}s)`;
                }
            }, 1000);
            
            // Resend the code
            createOverlay("Resending verification code...");
            
            fetch("../backend/auth/student/resend_code.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ email })
            })
            .then(response => response.json())
            .then(result => {
                removeOverlay();
                if (result.status === "success") {
                    showAlert('success', "A new verification code has been sent to your email.");
                    
                    // Clear input fields for new code
                    document.querySelectorAll('.verification-input').forEach(input => {
                        input.value = '';
                    });
                    document.querySelector('.verification-input').focus();
                } else {
                    showAlert('danger', result.message || "Failed to resend verification code.");
                }
            })
            .catch(error => {
                removeOverlay();
                showAlert('danger', "Network error: Failed to resend verification code.");
            });
        });
        
        // Insert before the submit button's parent element
        submitBtn.parentElement.insertAdjacentElement('beforebegin', resendBtn);
    }

    form.onsubmit = async (e) => {
        e.preventDefault();

        const verifyButton = e.target.querySelector("button[type='submit']");
        verifyButton.disabled = true;
        verifyButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Verifying...';

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

            if (response.ok && result.status === "success") {
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

                // Show success message with confetti animation if available
                showSuccessVerification(result.message || "Account verified successfully! You can now sign in.");

                // Redirect to login page after a short delay to see the success animation
                setTimeout(() => {
                    window.location.reload(); // Reload for a fresh login form
                }, 3000);

                // Note: Don't remove overlay during redirect
            } else if (result.lockout) {
                removeOverlay();
                showAlert('danger', result.message, 8000);
                verifyButton.disabled = false;
                verifyButton.innerHTML = "Verify";
            } else if (result.attempts_left !== undefined) {
                removeOverlay();
                showAlert('danger', result.message, 5000);
                verifyButton.disabled = false;
                verifyButton.innerHTML = "Verify";
                
                // Highlight inputs to indicate error
                document.querySelectorAll('.verification-input').forEach(input => {
                    input.classList.add('is-invalid');
                    setTimeout(() => input.classList.remove('is-invalid'), 2000);
                });
            } else {
                removeOverlay();
                showAlert('danger', result.message || "Verification failed. Please try again.");
                verifyButton.disabled = false;
                verifyButton.innerHTML = "Verify";
            }
        } catch (error) {
            console.error("Verification error:", error);
            removeOverlay();
            showAlert('danger', "Network error: Unable to verify your account.");
            verifyButton.disabled = false;
            verifyButton.innerHTML = "Verify";
        }
    };
}

// Show a more impressive success animation with confetti effect if possible
function showSuccessVerification(message) {
    // First show standard alert
    showAlert('success', message, 5000);
    
    // Try to add confetti animation if available
    try {
        const confettiContainer = document.createElement('div');
        confettiContainer.id = 'successConfetti';
        confettiContainer.style.position = 'fixed';
        confettiContainer.style.top = '0';
        confettiContainer.style.left = '0';
        confettiContainer.style.width = '100%';
        confettiContainer.style.height = '100%';
        confettiContainer.style.pointerEvents = 'none';
        confettiContainer.style.zIndex = '9997';
        document.body.appendChild(confettiContainer);

        // Create confetti elements
        for (let i = 0; i < 150; i++) {
            const confetti = document.createElement('div');
            confetti.style.position = 'absolute';
            confetti.style.width = `${Math.random() * 10 + 5}px`;
            confetti.style.height = `${Math.random() * 5 + 3}px`;
            confetti.style.backgroundColor = `hsl(${Math.random() * 360}, 100%, 50%)`;
            confetti.style.borderRadius = '50%';
            confetti.style.top = '-10px';
            confetti.style.left = `${Math.random() * 100}%`;
            confetti.style.transform = `rotate(${Math.random() * 360}deg)`;
            confetti.style.opacity = Math.random();
            
            // Add animation
            confetti.style.animation = `fall-${i} ${Math.random() * 3 + 2}s ease-in-out forwards`;
            const keyframes = `
                @keyframes fall-${i} {
                    to {
                        top: calc(100% + 10px);
                        transform: translateX(${Math.random() * 200 - 100}px) rotate(${Math.random() * 360}deg);
                    }
                }
            `;
            const style = document.createElement('style');
            style.appendChild(document.createTextNode(keyframes));
            document.head.appendChild(style);
            
            confettiContainer.appendChild(confetti);
        }
        
        // Remove confetti after animation completes
        setTimeout(() => {
            if (confettiContainer.parentNode) {
                confettiContainer.parentNode.removeChild(confettiContainer);
            }
        }, 5000);
    } catch (e) {
        console.log("Confetti animation not available:", e);
    }
}

// Show alert notification function with customizable duration
function showAlert(type, message, duration = 5000) {
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
    
    // Add icon based on alert type
    let icon = '';
    if (type === 'success') icon = '<i class="fas fa-check-circle me-2"></i>';
    else if (type === 'danger') icon = '<i class="fas fa-exclamation-circle me-2"></i>';
    else if (type === 'warning') icon = '<i class="fas fa-exclamation-triangle me-2"></i>';
    else if (type === 'info') icon = '<i class="fas fa-info-circle me-2"></i>';
    
    alertDiv.innerHTML = `
        ${icon}${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Position the alert
    alertDiv.style.position = 'fixed';
    alertDiv.style.top = '20px';
    alertDiv.style.left = '50%';
    alertDiv.style.transform = 'translateX(-50%)';
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '300px';
    alertDiv.style.maxWidth = '85%';
    alertDiv.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
    alertDiv.style.borderLeft = type === 'success' ? '4px solid #198754' : 
                               type === 'danger' ? '4px solid #dc3545' :
                               type === 'warning' ? '4px solid #ffc107' : '4px solid #0dcaf0';
    document.body.appendChild(alertDiv);

    // Auto-dismiss after specified duration
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.classList.remove('show');
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.parentNode.removeChild(alertDiv);
                }
            }, 300);
        }
    }, duration);
}
</script>