<?php include '../includes/student-header.php'; ?>


<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main" class="bg-light">
  <!-- Breadcrumb -->
  <?php include '../includes/student-breadcrumb.php'; ?>

  <!-- End Breadcrumb -->

  <!-- Content Section -->
  <div class="container content-space-1 content-space-t-lg-0 content-space-b-lg-2 mt-lg-n10">
    <div class="row">
      <div class="col-lg-3">
        <!-- Navbar -->
        <div class="navbar-expand-lg navbar-light">
          <div id="sidebarNav" class="collapse navbar-collapse navbar-vertical">
            <!-- Card -->
            <div class="card flex-grow-1 mb-5">
              <div class="card-body">
                <!-- Avatar -->
                <div class="d-none d-lg-block text-center mb-5">
                  <div class="avatar avatar-xxl avatar-circle mb-3">
                    <div class="flex-shrink-0">
                      <img class="avatar avatar-xl avatar-circle"
                        src="../uploads/profile/<?php echo $row['profile_pic'] ?>"
                        alt="Profile">
                    </div>
                  </div>
                  <h4 class="card-title mb-0"><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></h4>
                  <p class="card-text small"><?php echo $row['email']; ?></p>
                </div>
                <!-- End Avatar -->

                <!-- Sidebar Content -->

                <!-- Overview Section -->
                <span class="text-cap">Overview</span>
                <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                  <li class="nav-item">
                    <a class="nav-link" href="account-overview.php">
                      <i class="bi-person-circle nav-icon"></i> Account Overview
                    </a>
                  </li>
                </ul>

                <!-- Account Section -->
                <span class="text-cap">Account</span>
                <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                  <li class="nav-item">
                    <a class="nav-link" href="account-profile.php">
                      <i class="bi-person-badge nav-icon"></i> Personal info
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link active" href="account-security.php">
                      <i class="bi-shield-shaded nav-icon"></i> Security
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="account-notifications.php">
                      <i class="bi-bell nav-icon"></i> Notifications
                      <span class="badge bg-soft-dark text-dark rounded-pill nav-link-badge">0</span>
                    </a>
                  </li>
                </ul>

                <!-- Student-Specific Section -->
                <span class="text-cap">My Courses</span>
                <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                  <li class="nav-item">
                    <a class="nav-link" href="my-courses.php">
                      <i class="bi-person-badge nav-icon"></i> Enrolled Courses
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="my-badges.php">
                      <i class="bi-chat-dots nav-icon"></i> Badges
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="my-certifications.php">
                      <i class="bi-award nav-icon"></i> Certifications
                    </a>
                  </li>
                  <!-- <li class="nav-item">
                    <a class="nav-link" href="course-progress.php">
                      <i class="bi-bar-chart-line nav-icon"></i> Course Progress
                    </a>
                  </li> -->
                </ul>

                <!-- Payment Section for Students -->
                <span class="text-cap">Payments</span>
                <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                  <li class="nav-item">
                    <a class="nav-link" href="payment-history.php">
                      <i class="bi-credit-card nav-icon"></i> Payment History
                    </a>
                  </li>

                </ul>



                <!-- Sign-out & Help Section -->
                <span class="text-cap">---</span>
                <ul class="nav nav-sm nav-tabs nav-vertical">
                  <li class="nav-item">
                    <a class="nav-link" href="account-help.php">
                      <i class="bi-question-circle nav-icon"></i> Help
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="FAQ.php">
                      <i class="bi-card-list nav-icon"></i> FAQ's
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="report.php">
                      <i class="bi-exclamation-triangle nav-icon"></i> Report Issues
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="../backend/signout.php">
                      <i class="bi-box-arrow-right nav-icon"></i> Sign out
                    </a>
                  </li>
                </ul>

                <!-- End of Sidebar -->

              </div>
            </div>
            <!-- End Card -->
          </div>
        </div>
        <!-- End Navbar -->
      </div>
      <!-- End Col -->

      <div class="col-lg-9">
        <div class="d-grid gap-3 gap-lg-5">


          <!-- Card -->
          <div class="card">
            <div class="card-header border-bottom">
              <h5 class="card-header-title">Account Security</h5>
            </div>
            <div class="card-body">
              <form id="passwordChangeForm">
                <!-- Current Password -->
                <div class="row mb-4">
                  <label for="currentPassword" class="col-sm-3 col-form-label form-label">Current password</label>
                  <div class="col-sm-9">
                    <input type="password" class="form-control" name="currentPassword" id="currentPassword" placeholder="Enter current password" required>
                  </div>
                </div>

                <!-- New Password -->
                <div class="row mb-4">
                  <label for="newPassword" class="col-sm-3 col-form-label form-label">New password</label>
                  <div class="col-sm-9">
                    <input type="password" class="form-control" name="newPassword" id="newPassword" placeholder="Enter new password" required>
                  </div>
                </div>

                <!-- Confirm New Password -->
                <div class="row mb-4">
                  <label for="confirmNewPassword" class="col-sm-3 col-form-label form-label">Confirm new password</label>
                  <div class="col-sm-9">
                    <input type="password" class="form-control" name="confirmNewPassword" id="confirmNewPassword" placeholder="Confirm your new password" required>
                  </div>
                </div>

                <div class="row mb-4">
                  <label for="" class="col-sm-3"></label>

                  <div class="col-sm-9">
                    <h5>Password Recommendations:</h5>
                    <p class="card-text small">For better security, consider the following:</p>

                    <ul class="small">
                      <li>Use at least 8 characters</li>
                      <li>Include both uppercase and lowercase letters</li>
                      <li>Add numbers and special characters for strength</li>
                    </ul>
                  </div>


                </div>



                <div class="d-flex justify-content-end gap-3">
                  <button type="button" class="btn btn-white" onclick="resetForm()">Cancel</button>
                  <button type="submit" id="updateButton" class="btn btn-primary">Update Password</button>
                </div>
              </form>
            </div>

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

              // Create and apply page overlay for loading effect
              function createOverlay() {
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
                overlay.style.justifyContent = 'center';
                overlay.style.alignItems = 'center';

                // Add a loading spinner
                const spinner = document.createElement('div');
                spinner.className = 'spinner-border text-primary';
                spinner.setAttribute('role', 'status');
                spinner.innerHTML = '<span class="visually-hidden">Loading...</span>';

                overlay.appendChild(spinner);
                document.body.appendChild(overlay);
              }

              // Remove overlay
              function removeOverlay() {
                const overlay = document.getElementById('pageOverlay');
                if (overlay) {
                  document.body.removeChild(overlay);
                }
              }

              document.getElementById('passwordChangeForm').addEventListener('submit', function(event) {
                event.preventDefault();

                const updateButton = document.getElementById('updateButton');
                const currentPassword = document.getElementById('currentPassword').value;
                const newPassword = document.getElementById('newPassword').value;
                const confirmNewPassword = document.getElementById('confirmNewPassword').value;

                // Client-side validation
                if (newPassword !== confirmNewPassword) {
                  showAlert('danger', 'New password and confirm password do not match!');
                  return;
                }

                if (newPassword.length < 8) {
                  showAlert('danger', 'Password must be at least 8 characters long.');
                  return;
                }

                // Disable the button and show loading text
                updateButton.disabled = true;
                updateButton.textContent = 'Updating...';

                // Create overlay to prevent interaction
                createOverlay();

                // Send the request to update the password
                const formData = new FormData(this);

                fetch('../backend/auth/student/change_password.php', {
                    method: 'POST',
                    body: formData,
                  })
                  .then(response => response.text())
                  .then(result => {
                    // Check if the operation was successful
                    if (result.includes('success')) {
                      showAlert('success', result);
                      // Reload the page after a short delay
                      setTimeout(() => {
                        location.reload();
                      }, 2000);
                    } else {
                      removeOverlay();
                      showAlert('danger', result);
                    }
                  })
                  .catch(error => {
                    console.error('Error:', error);
                    removeOverlay();
                    showAlert('danger', 'An error occurred. Please try again.');
                  })
                  .finally(() => {
                    // Re-enable the button and restore its original text
                    updateButton.disabled = false;
                    updateButton.textContent = 'Update Password';
                  });
              });

              function resetForm() {
                document.getElementById('passwordChangeForm').reset();
              }
            </script>


          </div>
          <!-- End Card -->


        </div>
      </div>
      <!-- End Col -->
    </div>
    <!-- End Row -->
  </div>
  <!-- End Content Section -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- ========== FOOTER ========== -->
<?php include '../includes/student-footer.php'; ?>
<!-- ========== END FOOTER ========== -->