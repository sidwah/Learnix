<!DOCTYPE html>
<!-- admin/forgot-password.php -->
<html
  lang="en"
  class="light-style customizer-hide"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="assets/"
  data-template="vertical-menu-template-free"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>Forgot Password - Admin | Learnix</title>

    <meta name="description" content="Reset your admin password for the Learnix platform." />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />

    <!-- Icons -->
    <link rel="stylesheet" href="assets/vendor/fonts/boxicons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="assets/vendor/css/pages/page-auth.css" />

    <!-- Helpers -->
    <script src="assets/vendor/js/helpers.js"></script>
    <script src="assets/js/config.js"></script>

    <!-- Custom styles -->
    <style>
      .custom-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(5px);
        z-index: 9998;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 15px;
      }
      
      .verification-digit {
        font-size: 1.5rem;
        font-weight: bold;
        text-align: center;
      }

      /* Toast styles to ensure they appear on top */
      .bs-toast.toast {
        z-index: 9999 !important; /* Higher than overlay and modals */
        position: fixed;
        box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.15);
      }
      
      /* Adjustments for modal overlay */
      .modal-backdrop {
        z-index: 9997; /* Lower than overlay but higher than other elements */
      }
      
      .modal {
        z-index: 9998; /* Same as overlay */
      }
    </style>
  </head>

  <body>
    <!-- Content -->
    <div class="container-xxl">
      <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner py-4">
          <!-- Forgot Password -->
          <div class="card">
            <div class="card-body">
              <!-- Logo -->
              <div class="app-brand justify-content-center">
                <a href="index.php" class="app-brand-link gap-2">
                  <span class="app-brand-logo demo">
                    <img src="assets/img/logo.png" alt="Learnix" width="30" >
                  </span>
                  <span class="app-brand-text demo text-body fw-bolder">learnix</span>
                </a>
              </div>
              <!-- /Logo -->
              <h4 class="mb-2">Admin Forgot Password 🔒</h4>
              <p class="mb-4">Enter your email to receive instructions for resetting your admin password.</p>
              <form id="formForgotPassword" class="mb-3">
                <div class="mb-3">
                  <label for="email" class="form-label">Email</label>
                  <input
                    type="email"
                    class="form-control"
                    id="email"
                    name="email"
                    placeholder="Enter your email"
                    autofocus
                    required
                  />
                  <div class="invalid-feedback">Please enter a valid email address.</div>
                </div>
                <button type="submit" class="btn btn-primary d-grid w-100">Send Reset Code</button>
              </form>
              <div class="text-center">
                <a href="signin.php" class="d-flex align-items-center justify-content-center">
                  <i class="bx bx-chevron-left scaleX-n1-rtl bx-sm" aria-hidden="true"></i>
                  Back to Sign In
                </a>
              </div>
            </div>
          </div>
          <!-- /Forgot Password -->
        </div>
      </div>
    </div>
    <!-- /Content -->

    <!-- Reset Verification Modal -->
    <div class="modal fade" id="resetVerificationModal" tabindex="-1" aria-labelledby="resetVerificationModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="resetVerificationModalLabel">Verify Reset Code</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="text-center mb-4">
              <i class="bx bx-shield-quarter fs-1 text-primary"></i>
            </div>
            <p class="text-center">A verification code has been sent to your email. Please enter the code below to continue.</p>
            <form id="resetVerificationForm" class="needs-validation" novalidate>
              <div class="mb-3">
                <label class="form-label">Verification Code</label>
                <div class="d-flex justify-content-between gap-2">
                  <input type="text" class="form-control text-center verification-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                  <input type="text" class="form-control text-center verification-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                  <input type="text" class="form-control text-center verification-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                  <input type="text" class="form-control text-center verification-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                  <input type="text" class="form-control text-center verification-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                  <input type="text" class="form-control text-center verification-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
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
                <i class="bx bx-time me-2 text-muted"></i>
                <p class="text-muted small mb-0">The code will expire in 10 minutes</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- End Reset Verification Modal -->

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
              <i class="bx bx-lock fs-1 text-primary"></i>
            </div>
            <p class="text-center">Please create a new password for your account.</p>
            <form id="newPasswordForm" class="needs-validation" novalidate>
              <div class="mb-3 form-password-toggle">
                <label for="newPassword" class="form-label">New Password</label>
                <div class="input-group input-group-merge">
                  <input
                    type="password"
                    id="newPassword"
                    class="form-control"
                    name="newPassword"
                    placeholder="Enter new password"
                    required 
                    minlength="8"
                  />
                  <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                </div>
                <div class="invalid-feedback">Password must be at least 8 characters long.</div>
                <div class="form-text mt-2">
                  <div class="d-flex align-items-center">
                    <i class="bx bx-info-circle me-2 text-primary"></i>
                    <span>Password must be at least 8 characters long.</span>
                  </div>
                </div>
              </div>
              <div class="mb-4 form-password-toggle">
                <label for="confirmPassword" class="form-label">Confirm Password</label>
                <div class="input-group input-group-merge">
                  <input
                    type="password"
                    id="confirmPassword"
                    class="form-control"
                    name="confirmPassword"
                    placeholder="Confirm new password"
                    required
                  />
                  <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
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

    <!-- Toast Notification -->
    <div class="bs-toast toast toast-placement-ex m-2 fade bg-danger top-0 end-0" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000" id="errorToast" style="z-index: 9999; position: fixed;">
      <div class="toast-header">
        <i class="bx bx-bell me-2"></i>
        <div class="me-auto fw-semibold">Error</div>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body" id="errorToastMessage"></div>
    </div>

    <div class="bs-toast toast toast-placement-ex m-2 fade bg-success top-0 end-0" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000" id="successToast" style="z-index: 9999; position: fixed;">
      <div class="toast-header">
        <i class="bx bx-check me-2"></i>
        <div class="me-auto fw-semibold">Success</div>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body" id="successToastMessage"></div>
    </div>
    <!-- /Toast Notification -->

    <!-- Core JS -->
    <script src="assets/vendor/libs/jquery/jquery.js"></script>
    <script src="assets/vendor/libs/popper/popper.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>
    <script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="assets/vendor/js/menu.js"></script>

    <!-- Main JS -->
    <script src="assets/js/main.js"></script>

    <!-- Password Reset JS -->
    <script>
      document.addEventListener("DOMContentLoaded", function() {
        // Store email for reset process
        let resetEmail = '';
        
        // Show toast notification function
        function showToast(type, message) {
          const toast = document.getElementById(type === 'success' ? 'successToast' : 'errorToast');
          const toastMessage = document.getElementById(type === 'success' ? 'successToastMessage' : 'errorToastMessage');
          
          toastMessage.textContent = message;
          
          const bsToast = new bootstrap.Toast(toast);
          bsToast.show();
        }
        
        // Create and apply page overlay for loading effect with optional message
        function showOverlay(message = null) {
          // Remove any existing overlay
          const existingOverlay = document.querySelector('.custom-overlay');
          if (existingOverlay) {
            existingOverlay.remove();
          }
          
          // Create new overlay
          const overlay = document.createElement('div');
          overlay.className = 'custom-overlay';
          
          // Add a loading spinner
          const spinner = document.createElement('div');
          spinner.className = 'spinner-border text-primary';
          spinner.setAttribute('role', 'status');
          spinner.innerHTML = '<span class="visually-hidden">Loading...</span>';
          overlay.appendChild(spinner);
          
          // Add message if provided
          if (message) {
            const messageElement = document.createElement('div');
            messageElement.className = 'fw-semibold text-primary';
            messageElement.textContent = message;
            overlay.appendChild(messageElement);
          }
          
          document.body.appendChild(overlay);
        }
        
        // Remove overlay
        function removeOverlay() {
          const overlay = document.querySelector('.custom-overlay');
          if (overlay) {
            overlay.remove();
          }
        }
        
        // Handle forgot password form submission
        document.getElementById('formForgotPassword').addEventListener('submit', function(e) {
          e.preventDefault();
          
          const emailInput = document.getElementById('email');
          resetEmail = emailInput.value.trim();
          
          // Validate email
          if (!resetEmail || !resetEmail.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            emailInput.classList.add('is-invalid');
            return;
          } else {
            emailInput.classList.remove('is-invalid');
          }
          
          let submitButton = this.querySelector("button[type='submit']");
          submitButton.disabled = true;
          submitButton.textContent = "Sending...";
          
          showOverlay('Sending reset code...');
          
          // Send request to generate reset code
          let formData = new FormData();
          formData.append('email', resetEmail);
          
          fetch('../backend/auth/admin/forgot_password.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            removeOverlay();
            
            if (data.status === "success") {
              // Show verification modal
              const resetVerificationModal = new bootstrap.Modal(document.getElementById('resetVerificationModal'));
              resetVerificationModal.show();
              
              // Focus first digit input
              setTimeout(() => {
                document.querySelector('.verification-digit').focus();
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
          document.getElementById('resetVerificationCode').value = digits.join('');
        }
        
        // Handle verification form submission
        document.getElementById('resetVerificationForm').addEventListener('submit', function(e) {
          e.preventDefault();
          
          // Combine all input values into verification code
          updateVerificationCode();
          
          let verificationCode = document.getElementById('resetVerificationCode').value;
          let submitButton = this.querySelector("button[type='submit']");
          
          // Validate code format
          if (!verificationCode.match(/^\d{6}$/)) {
            document.querySelectorAll('.verification-digit').forEach(input => {
              input.classList.add('is-invalid');
            });
            return;
          } else {
            document.querySelectorAll('.verification-digit').forEach(input => {
              input.classList.remove('is-invalid');
            });
          }
          
          // Disable button and show loading state
          submitButton.disabled = true;
          submitButton.textContent = "Verifying...";
          
          // Create form data for verification
          let formData = new FormData();
          formData.append('email', resetEmail);
          formData.append('code', verificationCode);
          
          showOverlay('Verifying the code...');
          
          // Send verification request
          fetch('../backend/auth/admin/verify_reset_code.php', {
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
                  <i class="bx bx-error-circle fs-4 mb-2"></i>
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
                document.querySelectorAll('.verification-digit').forEach(input => {
                  input.value = '';
                });
                // Focus on the first input
                document.querySelector('.verification-digit').focus();
                
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
                  attemptsWarning.innerHTML = `<i class="bx bx-error-circle me-1"></i> ${data.attempts_remaining} attempt${data.attempts_remaining !== 1 ? 's' : ''} remaining before reset is locked`;
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
          
          showOverlay('Updating your password...');
          
          // Send password reset request
          fetch('../backend/auth/admin/reset_password.php', {
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
              
              // Redirect to sign-in page after 3 seconds
              setTimeout(() => {
                window.location.href = "signin.php";
              }, 3000);
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
          
          fetch('../backend/auth/admin/forgot_password.php', {
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
            
            // Reset the resend link after 30 seconds
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

        // Toggle password visibility
        document.querySelectorAll('.input-group-text').forEach(toggle => {
          toggle.addEventListener('click', function() {
            const passwordInput = this.previousElementSibling;
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
              passwordInput.type = 'text';
              icon.classList.remove('bx-hide');
              icon.classList.add('bx-show');
            } else {
              passwordInput.type = 'password';
              icon.classList.remove('bx-show');
              icon.classList.add('bx-hide');
            }
          });
        });
      });
    </script>

    <!-- Page JS -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
  </body>
</html>