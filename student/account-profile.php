<?php include '../includes/student-header.php'; ?>
  <!-- ========== END HEADER ========== -->

  <!-- ========== MAIN CONTENT ========== -->
  <main id="content" role="main" class="bg-light">
    <!-- Breadcrumb -->

    <?php include '../includes/student-breadcrumb.php'; ?>
    
    <!-- End Breadcrumb -->

    <!-- Content -->
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
                      <a class="nav-link active" href="account-profile.php">
                        <i class="bi-person-badge nav-icon"></i> Personal info
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="account-security.php">
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
                <h4 class="card-header-title">Personal info</h4>
              </div>

              <div class="card-body">
                <form id="editForm" enctype="multipart/form-data">
                  <input type="hidden" id="editUserId" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">

                  <!-- Profile photo -->
                  <div class="row mb-4">
                    <label class="col-sm-3 col-form-label form-label">Profile photo</label>
                    <div class="col-sm-9">
                      <div class="d-flex align-items-center">
                        <label class="avatar avatar-xl avatar-circle" for="avatarUploader">
                        <img id="avatarImg" class="avatar-img" src="../uploads/profile/<?php echo $row['profile_pic']; ?>" alt="Image Description">
                        </label>
                        <div class="d-grid d-sm-flex gap-2 ms-4">
                          <div class="form-attachment-btn btn btn-primary btn-sm">Upload photo
                            <input type="file" class="js-file-attach form-attachment-btn-label" name="avatar" id="avatarUploader"
                              data-hs-file-attach-options='{
                                "textTarget": "#avatarImg",
                                "mode": "image",
                                "targetAttr": "src",
                                "resetTarget": ".js-file-attach-reset-img",
                                "resetImg": "../uploads/profile/default.jpg",
                                "allowTypes": [".png", ".jpeg", ".jpg"]
                              }'>
                          </div>
                          <button type="button" class="js-file-attach-reset-img btn btn-white btn-sm">Delete</button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Full name -->
                  <div class="row mb-4">
                    <label for="firstNameLabel" class="col-sm-3 col-form-label form-label">Full name</label>
                    <div class="col-sm-9">
                      <div class="input-group">
                        <input type="text" class="form-control" name="firstName" id="editFirstName" value="<?php echo $row['first_name']; ?>" placeholder="First Name">
                        <input type="text" class="form-control" name="lastName" id="editLastName" value="<?php echo $row['last_name']; ?>" placeholder="Last Name">
                      </div>
                    </div>
                  </div>

                  <!-- Email (disabled) -->
                  <div class="row mb-4">
                    <label for="emailLabel" class="col-sm-3 col-form-label form-label">Email</label>
                    <div class="col-sm-9">
                      <input type="email" class="form-control" name="email" id="editEmail" value="<?php echo $row['email']; ?>" disabled>
                    </div>
                  </div>

                  <!-- Submit buttons -->
                  <div class="card-footer pt-0">
                    <div class="d-flex justify-content-end gap-3">
                      <button type="submit" class="btn btn-primary" id="submitButton">Save Changes</button>
                      <button type="button" class="btn btn-primary" id="loadingButton" style="display: none;" disabled>
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Loading...
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>

            <!-- JavaScript -->
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

document.getElementById('editForm').addEventListener('submit', function(event) {
    event.preventDefault();
    
    // Button references
    const submitButton = document.getElementById('submitButton');
    const loadingButton = document.getElementById('loadingButton');
    
    // Show loading button and hide submit button
    submitButton.style.display = 'none';
    loadingButton.style.display = 'inline-block';
    
    // Create overlay to prevent interaction
    createOverlay();
    
    const formData = new FormData(this);
    // Remove the email field from the FormData object as it cannot be updated
    formData.delete('email');
    
    fetch('../backend/auth/student/update_profile.php', {
        method: 'POST',
        body: formData,
    })
    .then(response => response.text())
    .then(result => {
        // Determine alert type based on result content
        const isSuccess = !result.toLowerCase().includes('error') && 
                        !result.toLowerCase().includes('failed') &&
                        !result.toLowerCase().includes('invalid');
        
        showAlert(isSuccess ? 'success' : 'danger', result);
        
        // Refresh the page after a delay for successful updates
        if (isSuccess) {
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            removeOverlay();
        }
    })
    .catch(error => {
        console.error('Error updating user:', error);
        removeOverlay();
        showAlert('danger', 'An error occurred. Please try again.');
    })
    .finally(() => {
        // Reset button states
        submitButton.style.display = 'inline-block';
        loadingButton.style.display = 'none';
    });
});            </script>
            <!-- End Card -->



          </div>
        </div>
        <!-- End Col -->
      </div>
      <!-- End Row -->
    </div>
    <!-- End Content -->
  </main>
  <!-- ========== END MAIN CONTENT ========== -->

  <!-- ========== FOOTER ========== -->
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

  <!-- JS Image Replacing -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Handle the file input change to update the avatar image
      document.getElementById('avatarUploader').addEventListener('change', function(event) {
        const file = event.target.files[0];
        const avatarImg = document.getElementById('avatarImg');

        if (file) {
          // Create a URL for the selected file
          const reader = new FileReader();
          reader.onload = function(e) {
            // Update the avatar image source
            avatarImg.src = e.target.result;
          };
          reader.readAsDataURL(file);
        }
      });

      // Handle the reset button to delete the avatar image
      document.querySelector('.js-file-attach-reset-img').addEventListener('click', function() {
        const avatarImg = document.getElementById('avatarImg');
        avatarImg.src = "../uploads/profile/default.jpg"; // Reset to the default image
        document.getElementById('avatarUploader').value = ""; // Clear the input
      });
    });
  </script>

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


</html>