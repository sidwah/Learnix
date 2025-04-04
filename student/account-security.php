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
                    <a class="nav-link" href="enrolled-courses.php">
                      <i class="bi-person-badge nav-icon"></i> Enrolled Courses
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="course-accomplishments.php">
                      <i class="bi-chat-dots nav-icon"></i> Accomplishments
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="student-certifications.php">
                      <i class="bi-award nav-icon"></i> Certifications
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="course-progress.php">
                      <i class="bi-bar-chart-line nav-icon"></i> Course Progress
                    </a>
                  </li>
                </ul>

                <!-- Payment Section for Students -->
                <span class="text-cap">Payments</span>
                <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                  <li class="nav-item">
                    <a class="nav-link" href="payment-history.php">
                      <i class="bi-credit-card nav-icon"></i> Payment History
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="payment-method.php">
                      <i class="bi-wallet nav-icon"></i> Payment Methods
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
<footer class="bg-dark">
  <footer class="bg-dark">
    <div class="container pb-1 pb-lg-5">
      <div class="row content-space-t-2">
        <div class="col-lg-3 mb-7 mb-lg-0">
          <!-- Logo -->
          <div class="mb-5">
            <a
              class="navbar-brand"
              href="../index.php"
              aria-label="Learnix">
              <img
                class="navbar-brand-logo"
                src="../assets/svg/logos/logo-white.svg"
                alt="Learnix Logo" />
            </a>
          </div>
          <!-- End Logo -->

          <!-- Contact Info -->
          <ul class="list-unstyled list-py-1">
            <li>
              <a class="link-sm link-light" href="#"><i class="bi-geo-alt-fill me-1"></i> Dimension College,
                Ghana</a>
            </li>
            <li>
              <a class="link-sm link-light" href="tel:+233123456789"><i class="bi-telephone-inbound-fill me-1"></i> +233 (0) 123
                456 789</a>
            </li>
          </ul>
          <!-- End Contact Info -->
        </div>
        <!-- End Col -->

        <div class="col-sm mb-7 mb-sm-0">
          <h5 class="text-white mb-3">Company</h5>
          <!-- List -->
          <ul class="list-unstyled list-py-1 mb-0">
            <li><a class="link-sm link-light" href="#">About Us</a></li>
            <li>
              <a class="link-sm link-light" href="#">Careers
                <span class="badge bg-warning text-dark rounded-pill ms-1">We're hiring</span></a>
            </li>
            <li><a class="link-sm link-light" href="#">Blog</a></li>
          </ul>
          <!-- End List -->
        </div>
        <!-- End Col -->

        <div class="col-sm mb-7 mb-sm-0">
          <h5 class="text-white mb-3">Resources</h5>
          <!-- List -->
          <ul class="list-unstyled list-py-1 mb-0">
            <li><a class="link-sm link-light" href="#">Help Center</a></li>
            <li><a class="link-sm link-light" href="#">Your Account</a></li>
          </ul>
          <!-- End List -->
        </div>
        <!-- End Col -->

        <div class="col-sm mb-7 mb-sm-0">
          <h5 class="text-white mb-3">Legal</h5>
          <!-- List -->
          <ul class="list-unstyled list-py-1 mb-0">
            <li>
              <a class="link-sm link-light" href="privacy-policy.php">Privacy Policy</a>
            </li>
            <li>
              <a class="link-sm link-light" href="terms.php">Terms of Service</a>
            </li>
          </ul>
          <!-- End List -->
        </div>
        <!-- End Col -->
      </div>
      <!-- End Row -->

      <div class="border-top border-white-10 my-7"></div>

      <div class="row mb-7 justify-content-between">
        <div class="col-sm mb-3 mb-sm-0">
          <!-- Socials -->
          <ul class="list-inline mb-0">

          </ul>
          <!-- End Socials -->
        </div>

        <div class="col-sm-auto">
          <!-- Socials -->
          <ul class="list-inline mb-0">
            <li class="list-inline-item">
              <a class="btn btn-soft-light btn-xs btn-icon" href="#">
                <i class="bi-facebook"></i>
              </a>
            </li>

            <li class="list-inline-item">
              <a class="btn btn-soft-light btn-xs btn-icon" href="#">
                <i class="bi-google"></i>
              </a>
            </li>

            <li class="list-inline-item">
              <a class="btn btn-soft-light btn-xs btn-icon" href="#">
                <i class="bi-twitter"></i>
              </a>
            </li>

            <li class="list-inline-item">
              <a class="btn btn-soft-light btn-xs btn-icon" href="#">
                <i class="bi-github"></i>
              </a>
            </li>
          </ul>
          <!-- End Socials -->
        </div>
      </div>

      <!-- Copyright -->
      <div class="w-md-85 text-lg-center mx-lg-auto">
        <p class="text-white-50 small">
          &copy; Learnix. <span id="currentYear"></span> All rights
          reserved.
        </p>

      </div>

      <script>
        // Get the current year
        const currentYear = new Date().getFullYear();
        // Set the text content of the element with ID 'currentYear'
        document.getElementById("currentYear").textContent = currentYear;
      </script>
      <!-- End Copyright -->
    </div>
  </footer>


  <!-- ========== END FOOTER ========== -->

  <!-- ========== SECONDARY CONTENTS ========== -->
  <!-- Go To -->
  <a
    class="js-go-to go-to position-fixed"
    href="javascript:;"
    style="visibility: hidden"
    data-hs-go-to-options='{
       "offsetTop": 700,
       "position": {
         "init": {
           "right": "2rem"
         },
         "show": {
           "bottom": "2rem"
         },
         "hide": {
           "bottom": "-2rem"
         }
       }
     }'>
    <i class="bi-chevron-up"></i>
  </a>

  <!-- Offcanvas Signup -->

  <!-- ========== END SECONDARY CONTENTS ========== -->

  <!-- JS Implementing Plugins -->
  <script src="../assets/js/vendor.min.js"></script>

  <!-- JS Learnix -->
  <script src="../assets/js/theme.min.js"></script>

  <!-- JS Plugins Init. -->
  <script>
    (function() {
      // INITIALIZATION OF HEADER
      // =======================================================
      new HSHeader("#header").init();

      // INITIALIZATION OF MEGA MENU
      // =======================================================
      new HSMegaMenu(".js-mega-menu", {
        desktop: {
          position: "left",
        },
      });

      // INITIALIZATION OF SHOW ANIMATIONS
      // =======================================================
      new HSShowAnimation(".js-animation-link");

      // INITIALIZATION OF BOOTSTRAP VALIDATION
      // =======================================================
      HSBsValidation.init(".js-validate", {
        onSubmit: (data) => {
          data.event.preventDefault();
          alert("Submited");
        },
      });

      // INITIALIZATION OF BOOTSTRAP DROPDOWN
      // =======================================================
      HSBsDropdown.init();

      // INITIALIZATION OF GO TO
      // =======================================================
      new HSGoTo(".js-go-to");

      // INITIALIZATION OF TEXT ANIMATION (TYPING)
      // =======================================================
      HSCore.components.HSTyped.init(".js-typedjs");

      // INITIALIZATION OF SWIPER
      // =======================================================
      var swiper = new Swiper(".js-swiper-course-hero", {
        preloaderClass: "custom-swiper-lazy-preloader",
        navigation: {
          nextEl: ".js-swiper-course-hero-button-next",
          prevEl: ".js-swiper-course-hero-button-prev",
        },
        slidesPerView: 1,
        loop: 1,
        breakpoints: {
          380: {
            slidesPerView: 2,
            spaceBetween: 15,
          },
          580: {
            slidesPerView: 3,
            spaceBetween: 15,
          },
          768: {
            slidesPerView: 4,
            spaceBetween: 15,
          },
          1024: {
            slidesPerView: 6,
            spaceBetween: 15,
          },
        },
        on: {
          imagesReady: function(swiper) {
            const preloader = swiper.el.querySelector(
              ".js-swiper-course-hero-preloader"
            );
            preloader.parentNode.removeChild(preloader);
          },
        },
      });
    })();
  </script>
  </body>

  <!-- Mirrored from htmlstream.com/preview/Learnix-v4.2/html/demo-course/index.php by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 02 Aug 2022 18:11:23 GMT -->

  </html>