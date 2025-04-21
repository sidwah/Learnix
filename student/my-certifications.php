<?php
/**
 * My Certificates Page
 * 
 * Displays all certificates earned by a student and allows them to download them.
 */

// Start or resume session
session_start();

// Check if user is logged in and has student role
if (!isset($_SESSION['user_id'])) {
    // Not logged in, redirect to login page
    header('Location: ../login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

require_once '../vendor/autoload.php';
require_once '../backend/config.php';
require_once '../backend/certificates/CertificateRepository.php';

use Learnix\Certificates\CertificateRepository;


$userId = $_SESSION['user_id'];

// Initialize repository
$certificateRepo = new CertificateRepository($conn);

// Get all certificates for the user
$certificates = $certificateRepo->getUserCertificates($userId);

// Get user info from session or database as needed - already loaded in included header

// Function to format date
function formatDate($dateString) {
    $date = new DateTime($dateString);
    return $date->format('F j, Y');
}

include '../includes/student-header.php';
?>

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
                    <a class="nav-link active" href="my-certifications.php">
                      <i class="bi-award nav-icon"></i>Certificates
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

                <!-- Instructor/Admin Section (Dynamic Role Check) -->
                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'instructor'): ?>
                  <span class="text-cap">Instructor</span>
                  <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                    <li class="nav-item">
                      <a class="nav-link" href="instructor-courses.php">
                        <i class="bi-person-badge nav-icon"></i> My Courses
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="create-course.php">
                        <i class="bi-file-earmark-plus nav-icon"></i> Create Course
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="manage-students.php">
                        <i class="bi-person-check nav-icon"></i> Manage Students
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="course-feedback.php">
                        <i class="bi-chat-dots nav-icon"></i> Course Feedback
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="instructor-withdrawal.php">
                        <i class="bi-wallet nav-icon"></i> Withdrawal
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="instructor-analytics.php">
                        <i class="bi-gear nav-icon"></i> Analytics
                      </a>
                    </li>
                  </ul>
                <?php endif; ?>

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
                      <a class="nav-link " href="report.php">
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
          <div id="editAddressCard" class="card">
            <div class="card-header border-bottom">
              <h4 class="card-header-title">My Certificates</h4>
            </div>

            <!-- Body -->
            <div class="card-body">
              <!-- Certificates content starts here -->
              
              <?php if (empty($certificates)): ?>
                <div class="text-center py-5">
                  <div class="mb-3">
                    <i class="bi-award fs-1 text-muted"></i>
                  </div>
                  <h3 class="mb-2">No Certificates Yet</h3>
                  <p class="mb-4 text-muted">Complete courses to earn certificates that will appear here. Certificates showcase your achievements and can be shared with employers.</p>
                  <a href="courses.php" class="btn btn-primary">Browse Courses</a>
                </div>
              <?php else: ?>
                <div class="row">
                  <?php foreach ($certificates as $certificate): ?>
                    <div class="col-md-6 mb-4">
                      <div class="card h-100 shadow-sm">
                        <div class="card-header bg-soft-primary">
                          <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-header-title mb-0"><?php echo htmlspecialchars($certificate['course_title']); ?></h5>
                            <span class="badge bg-soft-success text-success">Verified</span>
                          </div>
                        </div>
                        
                        <div class="card-body">
                          <div class="bg-soft-secondary p-3 mb-3 rounded d-flex align-items-center justify-content-center" style="height: 100px;">
                            <div class="text-center">
                              <i class="bi-award fs-2 text-primary mb-1"></i>
                              <div>Certificate Preview</div>
                            </div>
                          </div>
                          
                          <div class="d-flex align-items-center small text-muted mb-3">
                            <i class="bi-calendar me-1"></i>
                            <span>Issued on: <?php echo formatDate($certificate['issue_date']); ?></span>
                          </div>
                          
                          <div class="d-flex flex-column flex-sm-row gap-2 mt-3">
                            <a href="../download-certificate.php?id=<?php echo $certificate['certificate_id']; ?>" class="btn btn-primary btn-sm">
                              <i class="bi-download me-1"></i> Download
                            </a>
                            <a href="../verify-certificate.php?code=<?php echo $certificate['certificate_hash']; ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                              <i class="bi-shield-check me-1"></i> Verify
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
              <!-- Certificates content ends here -->
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