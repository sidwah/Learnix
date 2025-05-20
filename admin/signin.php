<!DOCTYPE html>
<!-- admin/signin.php -->
<html
  lang="en"
  class="light-style customizer-hide"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="assets/"
  data-template="vertical-menu-template-free">

<head>
  <meta charset="utf-8" />
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>Sign In - Admin | Learnix</title>

  <meta name="description" content="" />

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="../favicon.ico" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
    rel="stylesheet" />

  <!-- Icons. Uncomment required icon fonts -->
  <link rel="stylesheet" href="assets/vendor/fonts/boxicons.css" />

  <!-- Core CSS -->
  <link rel="stylesheet" href="assets/vendor/css/core.css" class="template-customizer-core-css" />
  <link rel="stylesheet" href="assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
  <link rel="stylesheet" href="assets/css/demo.css" />

  <!-- Vendors CSS -->
  <link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

  <!-- Page CSS -->
  <!-- Page -->
  <link rel="stylesheet" href="assets/vendor/css/pages/page-auth.css" />
  <!-- Helpers -->
  <script src="assets/vendor/js/helpers.js"></script>

  <script src="assets/js/config.js"></script>

  <!-- Custom Overlay Styles -->
  <!-- Add this to the existing <style> section in the head -->
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

    .verification-digit,
    .reset-verification-digit {
      font-size: 1.5rem;
      font-weight: bold;
      text-align: center;
    }

    /* Toast styles to ensure they appear on top */
    .bs-toast.toast {
      z-index: 9999 !important;
      /* Higher than overlay and modals */
      position: fixed;
      box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.15);
    }

    /* Adjustments for modal overlay */
    .modal-backdrop {
      z-index: 9997;
      /* Lower than overlay but higher than other elements */
    }

    .modal {
      z-index: 9998;
      /* Same as overlay */
    }
  </style>
</head>

<body>
  <!-- Content -->

  <div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
      <div class="authentication-inner">
        <!-- Register -->
        <div class="card">
          <div class="card-body">
            <!-- Logo -->
            <div class="app-brand justify-content-center">
              <a href="index.php" class="app-brand-link gap-2">
                <span class="app-brand-logo demo">
                  <img src="assets/img/logo.png" alt="Learnix" width="30">
                </span>
                <span class="app-brand-text demo text-body fw-bolder">learnix</span>
              </a>
            </div>
            <!-- /Logo -->
            <h4 class="mb-2">Admin Sign in</h4>
            <p class="mb-4">Please sign in to access the admin dashboard</p>

            <form id="formAuthentication" class="mb-3" method="POST" novalidate>
              <div class="mb-3">
                <label for="email" class="form-label">Email </label>
                <input
                  type="text"
                  class="form-control"
                  id="email"
                  name="email"
                  placeholder="Enter your email"
                  autofocus
                  required />
                <div class="invalid-feedback">Please enter a valid email address.</div>
              </div>
              <div class="mb-3 form-password-toggle">
                <div class="d-flex justify-content-between">
                  <label class="form-label" for="password">Password</label>
                  <a href="forgot-password.php">
                    <small>Forgot Password?</small>
                  </a>
                </div>
                <div class="input-group input-group-merge">
                  <input
                    type="password"
                    id="password"
                    class="form-control"
                    name="password"
                    placeholder="············"
                    aria-describedby="password"
                    required
                    minlength="8" />
                  <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                </div>
                <div class="invalid-feedback">Password must be at least 8 characters long.</div>
              </div>
              <div class="mb-3">
                <!-- <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="remember-me" />
                  <label class="form-check-label" for="remember-me"> Remember Me </label>
                </div> -->
              </div>
              <div class="mb-3">
                <button class="btn btn-primary d-grid w-100" type="submit">Sign in</button>
              </div>
            </form>
          </div>
        </div>
        <!-- /Register -->
      </div>
    </div>
  </div>

  <!-- Verification Code Modal -->
  <div class="modal fade" id="verificationModal" tabindex="-1" aria-labelledby="verificationModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="verificationModalLabel">Two-Factor Authentication</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="text-center mb-4">
            <i class="bx bx-shield-quarter fs-1 text-primary"></i>
          </div>
          <p class="text-center">A verification code has been sent to your email. Please enter the code below to complete sign-in.</p>
          <form id="verificationForm" class="needs-validation" novalidate>
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
              <input type="hidden" id="verificationCode" name="verificationCode">
              <div class="invalid-feedback text-center mt-2">
                Please enter the complete 6-digit verification code.
              </div>
            </div>
            <div class="d-grid mt-4">
              <button type="submit" class="btn btn-primary">Verify</button>
            </div>
          </form>
          <div class="mt-4 text-center">
            <p>Didn't receive the code? <a href="#" id="resendCode" class="text-decoration-none">Resend code</a></p>
            <div class="d-flex align-items-center justify-content-center mt-2">
              <i class="bx bx-time me-2 text-muted"></i>
              <p class="text-muted small mb-0">The code will expire in 10 minutes</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

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
  <!-- build:js assets/vendor/js/core.js -->
  <script src="assets/vendor/libs/jquery/jquery.js"></script>
  <script src="assets/vendor/libs/popper/popper.js"></script>
  <script src="assets/vendor/js/bootstrap.js"></script>
  <script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

  <script src="assets/vendor/js/menu.js"></script>
  <!-- endbuild -->

  <!-- Vendors JS -->

  <!-- Main JS -->
  <script src="assets/js/main.js"></script>

  <!-- Authentication JS -->
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      // Store user email for verification process
      let currentUserEmail = '';

      // Show alert notification function
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

      // Handle main sign-in form submission
      document.getElementById('formAuthentication').addEventListener('submit', function(e) {
        e.preventDefault();

        // Basic validation
        let isValid = true;
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');

        if (!emailInput.value || !emailInput.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
          emailInput.classList.add('is-invalid');
          isValid = false;
        } else {
          emailInput.classList.remove('is-invalid');
        }

        if (!passwordInput.value || passwordInput.value.length < 8) {
          passwordInput.classList.add('is-invalid');
          isValid = false;
        } else {
          passwordInput.classList.remove('is-invalid');
        }

        if (!isValid) return;

        // Store email for verification process
        currentUserEmail = emailInput.value.trim();

        // Create form data
        let formData = new FormData(this);
        let submitButton = this.querySelector("button[type='submit']");

        // Disable button and show loading state
        submitButton.disabled = true;
        submitButton.textContent = "Signing in...";

        // Create overlay with custom message
        showOverlay('Verifying your credentials...');

        // Send AJAX request
        fetch('../backend/auth/admin/signin.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.status === "success") {
              if (data.requireVerification) {
                removeOverlay();
                submitButton.textContent = "Sign In";
                submitButton.disabled = false;

                // Show verification modal
                const verificationModal = new bootstrap.Modal(document.getElementById('verificationModal'));
                verificationModal.show();

                // Focus the first digit input when modal is shown
                setTimeout(() => {
                  document.querySelector('.verification-digit').focus();
                }, 500);
              } else {
                submitButton.textContent = "Redirecting...";
                showToast('success', "Welcome, Admin! Redirecting to dashboard...");

                showOverlay('Welcome! Redirecting to dashboard...');
                setTimeout(() => {
                  window.location.href = "index.php";
                }, 2000);
              }
            } else {
              removeOverlay();
              showToast('error', data.message || "Invalid login credentials.");
              submitButton.textContent = "Sign In";
              submitButton.disabled = false;
            }
          })
          .catch(error => {
            console.error('Fetch Error:', error);
            removeOverlay();
            showToast('error', "Network error. Please check your connection.");
            submitButton.textContent = "Sign In";
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
        document.getElementById('verificationCode').value = digits.join('');
      }

      // Handle verification form submission
      document.getElementById('verificationForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Combine all input values into verification code
        updateVerificationCode();

        let verificationCode = document.getElementById('verificationCode').value;
        let submitButton = this.querySelector("button[type='submit']");

        // Validate code format
        if (!verificationCode.match(/^\d{6}$/)) {
          document.querySelectorAll('.verification-digit').forEach(input => {
            input.classList.add('is-invalid');
          });
          return;
        }

        // Disable button and show loading state
        submitButton.disabled = true;
        submitButton.textContent = "Verifying...";

        // Create form data for verification
        let formData = new FormData();
        formData.append('email', currentUserEmail);
        formData.append('code', verificationCode);

        showOverlay('Verifying the code...');

        // Send verification request
        fetch('../backend/auth/admin/verify_code.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            removeOverlay();

            if (data.status === "success") {
              // Hide the modal
              bootstrap.Modal.getInstance(document.getElementById('verificationModal')).hide();

              showToast('success', "Verification successful! Redirecting to dashboard...");

              showOverlay('Verification successful! Redirecting to dashboard...');
              setTimeout(() => {
                window.location.href = "index.php";
              }, 2000);
            } else {
              showToast('error', data.message || "Invalid verification code");
              submitButton.textContent = "Verify";
              submitButton.disabled = false;

              // Handle account lockout
              if (data.locked) {
                // Show lockout message in modal
                const verificationForm = document.getElementById('verificationForm');
                const formContainer = verificationForm.parentElement;

                // Create lockout message
                const lockoutDiv = document.createElement('div');
                lockoutDiv.className = 'alert alert-danger text-center mt-3';
                lockoutDiv.innerHTML = `
                  <i class="bx bx-error-circle fs-4 mb-2"></i>
                  <p class="mb-1"><strong>Account Temporarily Locked</strong></p>
                  <p class="mb-0">Too many failed verification attempts. Please try again later.</p>
                `;

                // Disable verification form
                verificationForm.querySelectorAll('input, button').forEach(el => {
                  el.disabled = true;
                });

                document.getElementById('resendCode').style.pointerEvents = 'none';
                document.getElementById('resendCode').style.opacity = '0.5';

                // Add lockout message to modal
                formContainer.appendChild(lockoutDiv);

                // Auto-close modal after 5 seconds
                setTimeout(() => {
                  bootstrap.Modal.getInstance(document.getElementById('verificationModal')).hide();
                }, 5000);
              } else {
                // Clear the input fields for retry
                document.querySelectorAll('.verification-digit').forEach(input => {
                  input.value = '';
                  input.classList.remove('is-invalid');
                });
                // Focus on the first input
                document.querySelector('.verification-digit').focus();

                // Show attempts remaining warning if provided
                if (data.attempts_remaining) {
                  // Add or update attempts warning
                  let attemptsWarning = document.getElementById('attemptsWarning');
                  if (!attemptsWarning) {
                    attemptsWarning = document.createElement('div');
                    attemptsWarning.id = 'attemptsWarning';
                    attemptsWarning.className = 'mt-3 text-center text-danger small';
                    document.getElementById('verificationForm').appendChild(attemptsWarning);
                  }
                  attemptsWarning.innerHTML = `<i class="bx bx-error-circle me-1"></i> ${data.attempts_remaining} attempt${data.attempts_remaining !== 1 ? 's' : ''} remaining before your account is locked`;
                }
              }
            }
          })
          .catch(error => {
            console.error('Verification Error:', error);
            removeOverlay();
            showToast('error', "Network error. Please try again.");
            submitButton.textContent = "Verify";
            submitButton.disabled = false;
          });
      });

      // Handle resend code click
      document.getElementById('resendCode').addEventListener('click', function(e) {
        e.preventDefault();

        if (!currentUserEmail) {
          showToast('error', "Session expired. Please sign in again.");
          return;
        }

        this.textContent = "Sending...";
        this.style.pointerEvents = "none";

        let formData = new FormData();
        formData.append('email', currentUserEmail);
        formData.append('resend', true);

        fetch('../backend/auth/admin/signin.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.status === "success") {
              showToast('success', "A new verification code has been sent to your email");
            } else {
              showToast('error', data.message || "Failed to resend code");
            }

            // Reset the resend link
            setTimeout(() => {
              document.getElementById('resendCode').textContent = "Resend code";
              document.getElementById('resendCode').style.pointerEvents = "";
            }, 30000); // Disable for 30 seconds to prevent spam
          })
          .catch(error => {
            console.error('Resend Error:', error);
            showToast('error', "Network error. Please try again.");
            document.getElementById('resendCode').textContent = "Resend code";
            document.getElementById('resendCode').style.pointerEvents = "";
          });
      });
    });
  </script>

  <!-- Place this tag in your head or just before your close body tag. -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
</body>

</html>