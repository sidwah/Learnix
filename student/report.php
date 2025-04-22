<?php include '../includes/student-header.php'; ?>


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
                    <a class="nav-link" href="account-profile.php">
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
                    <a class="nav-link active" href="report.php">
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


          <!-- Card -->
          <div id="editAddressCard" class="card">
            <div class="card-header border-bottom">
              <h4 class="card-header-title">Report Issues</h4>
            </div>

            <!-- Body -->
            <div class="card-body">
              <!-- Add your course content here -->
              <form id="reportIssueForm">
                <input type="hidden" id="reportUserId" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">

                <!-- Issue Type -->
                <div class="row mb-4">
                  <label for="issueType" class="col-sm-3 col-form-label form-label">Issue Type <span class="text-danger">*</span> </label>
                  <div class="col-sm-9">
                    <select class="form-select" id="issueType" name="issue_type" required>
                      <option value="" selected disabled>Select an issue type</option>
                      <option value="Technical">Technical Issue</option>
                      <option value="Course">Course-Related</option>
                      <option value="Account">Account Problem</option>
                      <option value="Other">Other</option>
                    </select>
                  </div>
                </div>

                <!-- Issue Description -->
                <div class="row mb-4">
                  <label for="issueDescription" class="col-sm-3 col-form-label form-label">Description <span class="text-danger">*</span></label>
                  <div class="col-sm-9">
                    <textarea class="form-control" id="issueDescription" name="description" rows="4" placeholder="Provide a brief description" required></textarea>
                  </div>
                </div>

                <!-- File Upload -->
                <div class="row mb-4">
                  <label for="issueFile" class="col-sm-3 col-form-label form-label">Attach File</label>
                  <div class="col-sm-9">
                    <input type="file" class="form-control" id="issueFile" name="issue_file">
                  </div>
                </div>

                <div class="d-flex justify-content-end gap-3">
                  <button type="button" class="btn btn-white" onclick="resetForm()">Cancel</button>
                  <button type="submit" id="reportButton" class="btn btn-primary">
                    <span id="buttonText">Report Issue</span>
                    <span id="spinner" class="spinner-border spinner-border-sm d-none"></span>
                  </button>
                </div>
              </form>

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

function resetForm() {
    document.getElementById("reportIssueForm").reset();
}

document.getElementById("reportIssueForm").addEventListener("submit", function(e) {
    e.preventDefault();
    
    let formData = new FormData(this);
    let reportButton = document.getElementById("reportButton");
    let buttonText = document.getElementById("buttonText");
    let spinner = document.getElementById("spinner");
    
    // Disable button and show spinner
    reportButton.disabled = true;
    buttonText.textContent = "Reporting...";
    spinner.classList.remove("d-none");
    
    // Create overlay to prevent interaction
    createOverlay();
    
    fetch("../backend/others/report_issue.php", {
        method: "POST",
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            showAlert('success', data.message);
            document.getElementById("reportIssueForm").reset();
        } else {
            showAlert('danger', data.message);
        }
        removeOverlay();
    })
    .catch(error => {
        console.error("Error:", error);
        showAlert('danger', "Something went wrong! Please try again.");
        removeOverlay();
    })
    .finally(() => {
        // Re-enable button and hide spinner
        reportButton.disabled = false;
        buttonText.textContent = "Report Issue";
        spinner.classList.add("d-none");
    });
});
              </script>

            </div>
            <!-- End Body -->
          </div>
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
  <?php include '../includes/student-footer.php'; ?>
  <!-- ========== END FOOTER ========== -->