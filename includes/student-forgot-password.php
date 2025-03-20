    <!-- Forgot Password -->
    <div id="loginOffcanvasFormResetPassword" style="display: none; opacity: 0">
      <!-- Heading -->
      <div class="text-center mb-7">
        <h3 class="modal-title">Forgot Password</h3>
        <p>Enter your email address to receive a verification code.</p>
      </div>

      <form class="js-validate needs-validation" novalidate id="forgotPasswordForm">
        <!-- Email Form -->
        <div class="mb-3">
          <div class="d-flex justify-content-between align-items-center">

            <label class="form-label" for="email">Your Email</label>

            <a
              class="js-animation-link form-label-link"
              href="javascript:;"
              data-hs-show-animation-options='{
                    "targetSelector": "#loginOffcanvasFormLogin",
                    "groupName": "idForm"
                  }'>
              <i class="bi-chevron-left small"></i> Back to Sign in
            </a>
          </div>

          <input
            type="email"
            class="form-control form-control-lg"
            name="email"
            id="resetPasswordEmail"
            placeholder="e.g., johndoe@email.com"
            required />
          <span class="invalid-feedback">Please enter a valid email address.</span>
        </div>

        <div class="d-grid gap-3 text-center">
          <button type="submit" class="btn btn-primary btn-lg" id="forgotPasswordButton">Submit</button>
        </div>
      </form>
    </div>
    <!-- Verification Modal -->
    <div class="modal fade" id="verificationModal" tabindex="-1" aria-labelledby="verificationModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="verificationModalLabel">Enter Verification Code</h5>
          </div>
          <div class="modal-body">
            <p>A 5-digit verification code has been sent to your email.</p>
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

    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="resetPasswordModalLabel">Set New Password</h5>
          </div>
          <div class="modal-body">
            <form id="resetPasswordForm">
              <div class="mb-3">
                <label for="newPwd" class="form-label">New Password</label>
                <input class="form-control" type="password" id="newPwd" name="newPwd" required placeholder="Enter a new password">
              </div>
              <div class="mb-3">
                <label for="confirmPwd" class="form-label">Confirm Password</label>
                <input class="form-control" type="password" id="confirmPwd" name="confirmPwd" required placeholder="Re-enter your password">
              </div>
              <div class="d-grid gap-3">
                <button type="submit" class="btn btn-primary">Update Password</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <script>
      document.addEventListener("DOMContentLoaded", function() {
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

        // Store email for reset process
        let resetEmail = '';

        // Handle forgot password form submission
        const forgotPasswordForm = document.getElementById('forgotPasswordForm');
        if (forgotPasswordForm) {
          forgotPasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const emailInput = document.getElementById('resetPasswordEmail');
            resetEmail = emailInput.value.trim();

            // Validate email
            if (!resetEmail || !resetEmail.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
              emailInput.classList.add('is-invalid');
              return;
            }

            let submitButton = document.getElementById('forgotPasswordButton');
            submitButton.disabled = true;
            submitButton.textContent = "Sending...";

            createOverlay('Sending verification code...');

            // Send request to generate reset code
            let formData = new FormData();
            formData.append('email', resetEmail);

            fetch('../backend/auth/student/forgot_password.php', {
                method: 'POST',
                body: formData
              })
              .then(response => response.json())
              .then(data => {
                removeOverlay();

                if (data.status === "success") {
                  // Show verification modal
                  const verificationModal = new bootstrap.Modal(document.getElementById('verificationModal'));
                  verificationModal.show();

                  // Focus first digit input
                  setTimeout(() => {
                    document.querySelector('.verification-input').focus();
                  }, 500);

                  showAlert('success', "Verification code sent to your email");
                } else {
                  showAlert('danger', data.message || "Failed to send verification code");
                }
                submitButton.textContent = "Submit";
                submitButton.disabled = false;
              })
              .catch(error => {
                console.error('Reset Code Error:', error);
                removeOverlay();
                showAlert('danger', "Network error. Please try again.");
                submitButton.textContent = "Submit";
                submitButton.disabled = false;
              });
          });
        }

        // Setup verification code input handling
        const verificationInputs = document.querySelectorAll('.verification-input');
        if (verificationInputs.length > 0) {
          verificationInputs.forEach((input, index) => {
            // Auto focus next input when a digit is entered
            input.addEventListener('input', function(e) {
              // Only allow numbers
              this.value = this.value.replace(/[^0-9]/g, '');

              // If a digit was entered and there's a next input, focus it
              if (this.value && index < 4) {
                document.querySelectorAll('.verification-input')[index + 1].focus();
              }
            });

            // Handle backspace - move to previous input
            input.addEventListener('keydown', function(e) {
              if (e.key === 'Backspace' && !this.value && index > 0) {
                document.querySelectorAll('.verification-input')[index - 1].focus();
              }
            });

            // Handle paste event across all inputs
            input.addEventListener('paste', function(e) {
              e.preventDefault();
              const paste = (e.clipboardData || window.clipboardData).getData('text');

              // Check if pasted content is a 5-digit number
              if (/^\d{5}$/.test(paste)) {
                // Distribute the digits across all inputs
                document.querySelectorAll('.verification-input').forEach((digitInput, i) => {
                  digitInput.value = paste[i];
                });

                // Focus the last input
                document.querySelectorAll('.verification-input')[4].focus();
              }
            });
          });
        }

        // Handle verification form submission
        const verificationForm = document.getElementById('verificationForm');
        if (verificationForm) {
          verificationForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Collect all digits into one code
            const codeInputs = document.querySelectorAll('.verification-input');
            const codeArray = Array.from(codeInputs).map(input => input.value);
            const verificationCode = codeArray.join('');

            // Validate code format
            if (!verificationCode.match(/^\d{5}$/)) {
              codeInputs.forEach(input => {
                input.classList.add('is-invalid');
              });
              showAlert('danger', "Please enter all 5 digits of the verification code");
              return;
            }

            let submitButton = this.querySelector("button[type='submit']");
            submitButton.disabled = true;
            submitButton.textContent = "Verifying...";

            createOverlay('Verifying code...');

            // Send verification request
            let formData = new FormData();
            formData.append('email', resetEmail);
            formData.append('code', verificationCode);

            fetch('../backend/auth/student/verify_reset_code.php', {
                method: 'POST',
                body: formData
              })
              .then(response => response.json())
              .then(data => {
                removeOverlay();

                if (data.status === "success") {
                  // Hide verification modal
                  const verificationModalInstance = bootstrap.Modal.getInstance(document.getElementById('verificationModal'));
                  if (verificationModalInstance) {
                    verificationModalInstance.hide();
                  }

                  // Show reset password modal
                  const resetPasswordModal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
                  resetPasswordModal.show();

                  showAlert('success', "Verification successful! Please set a new password.");
                } else {
                  showAlert('danger', data.message || "Invalid verification code");
                  submitButton.textContent = "Verify";
                  submitButton.disabled = false;

                  // Handle account lockout
                  if (data.locked) {
                    // Create lockout message
                    const lockoutDiv = document.createElement('div');
                    lockoutDiv.className = 'alert alert-danger text-center mt-3';
                    lockoutDiv.innerHTML = `
                <i class="bi bi-exclamation-triangle-fill"></i>
                <p class="mb-0">Too many failed attempts. Please try again later.</p>
              `;

                    // Add to modal
                    this.appendChild(lockoutDiv);

                    // Disable verification form
                    codeInputs.forEach(input => {
                      input.disabled = true;
                    });
                    submitButton.disabled = true;

                    // Auto-close modal after 5 seconds
                    setTimeout(() => {
                      const modal = bootstrap.Modal.getInstance(document.getElementById('verificationModal'));
                      if (modal) {
                        modal.hide();
                      }
                    }, 5000);
                  } else {
                    // Clear inputs for retry
                    codeInputs.forEach(input => {
                      input.value = '';
                      input.classList.remove('is-invalid');
                    });
                    codeInputs[0].focus();

                    // Show attempts remaining warning if provided
                    if (data.attempts_remaining) {
                      let attemptsWarning = document.getElementById('verificationAttemptsWarning');
                      if (!attemptsWarning) {
                        attemptsWarning = document.createElement('div');
                        attemptsWarning.id = 'verificationAttemptsWarning';
                        attemptsWarning.className = 'text-center text-danger small mt-3';
                        this.appendChild(attemptsWarning);
                      }
                      attemptsWarning.innerHTML = `<i class="bi bi-exclamation-circle me-1"></i> ${data.attempts_remaining} attempt${data.attempts_remaining !== 1 ? 's' : ''} remaining before lockout`;
                    }
                  }
                }
              })
              .catch(error => {
                console.error('Verification Error:', error);
                removeOverlay();
                showAlert('danger', "Network error. Please try again.");
                submitButton.textContent = "Verify";
                submitButton.disabled = false;
              });
          });
        }

        // Handle reset password form submission
        const resetPasswordForm = document.getElementById('resetPasswordForm');
        if (resetPasswordForm) {
          resetPasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log("Reset password form submitted");

            // Initialize valid flag
            let isValid = true;

            // Get elements and values
            const newPasswordElem = document.getElementById('newPwd');
            const confirmPasswordElem = document.getElementById('confirmPwd');

            if (!newPasswordElem || !confirmPasswordElem) {
              showAlert('danger', "Form error: Password fields not found");
              return;
            }

            const newPassword = newPasswordElem.value.trim();
            const confirmPassword = confirmPasswordElem.value.trim();

            console.log("Password lengths:", newPassword.length, confirmPassword.length);

            // Validate password length
            if (newPassword.length < 8) {
              newPasswordElem.classList.add('is-invalid');
              showAlert('danger', "Password must be at least 8 characters long");
              isValid = false;
            } else {
              newPasswordElem.classList.remove('is-invalid');
            }

            // Validate password match
            if (newPassword !== confirmPassword) {
              confirmPasswordElem.classList.add('is-invalid');
              showAlert('danger', "Passwords do not match");
              isValid = false;
            } else {
              confirmPasswordElem.classList.remove('is-invalid');
            }

            // Return early if validation failed
            if (!isValid) return;

            let submitButton = this.querySelector("button[type='submit']");
            submitButton.disabled = true;
            submitButton.textContent = "Updating...";

            createOverlay('Resetting your password...');

            // Send reset password request
            let formData = new FormData();
            formData.append('email', resetEmail);
            formData.append('password', newPassword);
            formData.append('confirm_password', confirmPassword);

            console.log("Sending reset request for email:", resetEmail);

            fetch('../backend/auth/student/reset_password.php', {
                method: 'POST',
                body: formData
              })
              .then(response => {
                // Log the raw response if there's an error
                if (!response.ok) {
                  response.clone().text().then(text => {
                    console.error('Response error:', text);
                  });
                }
                return response.json();
              })
              .then(data => {
                removeOverlay();

                if (data.status === "success") {
                  // Hide reset password modal
                  const resetPasswordModalInstance = bootstrap.Modal.getInstance(document.getElementById('resetPasswordModal'));
                  if (resetPasswordModalInstance) {
                    resetPasswordModalInstance.hide();
                  }

                  showAlert('success', data.message || "Password reset successful! You can now sign in with your new password.");

                  // Redirect back to login after delay
                  setTimeout(() => {
                    // If navigateToLogin isn't defined, try to show the login form
                    try {
                      const loginLink = document.querySelector('.js-animation-link[data-hs-show-animation-options*="loginOffcanvasFormLogin"]');
                      if (loginLink) {
                        loginLink.click();
                      }
                    } catch (e) {
                      console.log('Could not navigate to login automatically');
                    }
                  }, 2000);
                } else {
                  showAlert('danger', data.message || "Failed to reset password");
                  submitButton.textContent = "Update Password";
                  submitButton.disabled = false;
                }
              })
              .catch(error => {
                console.error('Reset Password Error:', error);
                removeOverlay();

                showAlert('danger', "Network error. Please try again.");
                submitButton.textContent = "Update Password";
                submitButton.disabled = false;
              });
          });
        }

        // Add some additional styling for verification inputs
        document.querySelectorAll('.verification-input').forEach(input => {
          // Make each input box larger
          input.style.fontSize = '24px';
          input.style.fontWeight = 'bold';
        });
      });
    </script>