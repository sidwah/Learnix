<?php

/**
 * My Certificates Page
 * 
 * Displays all certificates earned by a student and allows them to download and share verification links.
 */

// Start or resume session
session_start();

// Check if user is logged in and has student role
if (!isset($_SESSION['user_id'])) {
  // Not logged in, redirect to login page
  header('Location: ../index.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
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

// Function to format date
function formatDate($dateString)
{
  $date = new DateTime($dateString);
  return $date->format('Y-m-d');
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
                        src="../Uploads/profile/<?php echo htmlspecialchars($row['profile_pic']); ?>"
                        alt="Profile">
                    </div>
                  </div>
                  <h4 class="card-title mb-0"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></h4>
                  <p class="card-text small"><?php echo htmlspecialchars($row['email']); ?></p>
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
                      <!-- <span class="badge bg-soft-dark text-dark rounded-pill nav-link-badge">0</span> -->
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
                  <!-- <li class="nav-item">
                    <a class="nav-link" href="my-badges.php">
                      <i class="bi-chat-dots nav-icon"></i> Badges
                    </a>
                  </li> -->
                  <li class="nav-item">
                    <a class="nav-link active" href="my-certifications.php">
                      <i class="bi-award nav-icon"></i> Certifications
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="my-notes.php">
                      <i class="bi-journal-text nav-icon"></i> Notes
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
          <div id="certificationsCard" class="card">
            <div class="card-header border-bottom">
              <h4 class="card-header-title">Certifications</h4>
            </div>

            <!-- Body -->
            <div class="card-body">
              <!-- Certification Controls -->
              <div class="flex justify-between items-center mb-4">
                <input type="text" class="form-control w-64" id="searchCertifications" placeholder="Search certifications..." oninput="filterCertifications()">
                <a href="my-courses.php" class="btn btn-sm btn-primary">Earn More Certifications</a>
              </div>

              <!-- Certification List -->
              <div id="certificationList" class="space-y-4">
                <?php if (empty($certificates)): ?>
                  <!-- Empty State -->
                  <div id="emptyCertifications" class="text-center py-8">
                    <i class="bi bi-award text-gray-400 text-4xl mb-2"></i>
                    <p class="text-gray-600">No certifications earned yet. Complete a course to earn your first certificate!</p>
                    <a href="my-courses.php" class="btn btn-primary mt-3">Explore Courses</a>
                  </div>
                <?php endif; ?>
              </div>

              <!-- Empty State (for JavaScript filtering) -->
              <div id="emptyCertifications" class="hidden text-center py-8">
                <i class="bi bi-award text-gray-400 text-4xl mb-2"></i>
                <p class="text-gray-600">No certifications earned yet. Complete a course to earn your first certificate!</p>
                <a href="my-courses.php" class="btn btn-primary mt-3">Explore Courses</a>
              </div>
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

<!-- Tailwind CSS CDN -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

<!-- JavaScript for Certification Interactivity -->
<script>
  // Certification data from PHP backend
  const certifications = [
    <?php foreach ($certificates as $certificate): ?>,
      {
        id: <?php echo $certificate['certificate_id']; ?>,
        title: '<?php echo htmlspecialchars($certificate['course_title']); ?>',
        course: '<?php echo htmlspecialchars($certificate['course_title']); ?>',
        issueDate: '<?php echo formatDate($certificate['issue_date']); ?>',
        downloadUrl: '../download-certificate.php?id=<?php echo $certificate['certificate_id']; ?>',
        verifyUrl: 'Learnix/verify-certificate.php?code=<?php echo htmlspecialchars($certificate['certificate_hash']); ?>'
      },
    <?php endforeach; ?>
  ];

  // Render certifications
  function renderCertifications(filteredCertifications) {
    const certificationList = document.getElementById('certificationList');
    const emptyCertifications = document.getElementById('emptyCertifications');
    certificationList.innerHTML = '';

    if (filteredCertifications.length === 0) {
      emptyCertifications.classList.remove('hidden');
      return;
    }

    emptyCertifications.classList.add('hidden');
    filteredCertifications.forEach(cert => {
      const certificationItem = document.createElement('div');
      certificationItem.className = 'certification-item p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow flex justify-between items-center';
      certificationItem.innerHTML = `
        <div class="flex items-center space-x-4">
          <i class="bi bi-award text-primary text-xl"></i>
          <div>
            <h5 class="text-sm font-semibold">${cert.title}</h5>
            <p class="text-sm text-gray-600">Issued on: ${new Date(cert.issueDate).toLocaleDateString()}</p>
            <span class="text-xs text-gray-400">Course: ${cert.course}</span>
          </div>
        </div>
        <div class="flex space-x-2 relative">
          <button class="btn btn-sm btn-soft-primary" onclick="toggleShareMenu(this, '${cert.verifyUrl}', '${cert.title}')">Share</button>
          <a href="${cert.downloadUrl}" class="btn btn-sm btn-soft-secondary" target="_blank">View</a>
          <div class="share-menu hidden absolute right-0 top-full mt-2 bg-white shadow-lg rounded-lg p-2 z-10" style="min-width: 150px;">
            <a href="#" onclick="shareToLinkedIn('${cert.verifyUrl}', '${cert.title}')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">LinkedIn</a>
            <a href="#" onclick="shareToTwitter('${cert.verifyUrl}', '${cert.title}')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Twitter</a>
            <a href="#" onclick="shareToFacebook('${cert.verifyUrl}', '${cert.title}')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Facebook</a>
            <a href="#" onclick="shareToEmail('${cert.verifyUrl}', '${cert.title}')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Email</a>
          </div>
        </div>
      `;
      certificationList.appendChild(certificationItem);
    });
  }

  // Filter certifications
  function filterCertifications() {
    const searchQuery = document.getElementById('searchCertifications').value.toLowerCase();
    const filteredCertifications = certifications.filter(cert =>
      cert.title.toLowerCase().includes(searchQuery) || cert.course.toLowerCase().includes(searchQuery)
    );
    renderCertifications(filteredCertifications);
  }

  // Toggle share menu
  function toggleShareMenu(button, url, title) {
    const shareMenu = button.nextElementSibling.nextElementSibling;
    const isVisible = !shareMenu.classList.contains('hidden');
    // Close all other share menus
    document.querySelectorAll('.share-menu').forEach(menu => menu.classList.add('hidden'));
    // Toggle the current menu
    shareMenu.classList.toggle('hidden', isVisible);
  }

  // Share to LinkedIn
  function shareToLinkedIn(url, title) {
    const shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`;
    window.open(shareUrl, '_blank', 'width=600,height=400');
  }

  // Share to Twitter
  function shareToTwitter(url, title) {
    const text = `I earned my ${title} certificate! Verify it here: ${url}`;
    const shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}`;
    window.open(shareUrl, '_blank', 'width=600,height=400');
  }

  // Share to Facebook
  function shareToFacebook(url, title) {
    const shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
    window.open(shareUrl, '_blank', 'width=600,height=400');
  }

  // Share to Email
  function shareToEmail(url, title) {
    const subject = `My ${title} Certificate Verification`;
    const body = `I earned my ${title} certificate! You can verify it here: ${url}`;
    const shareUrl = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    window.location.href = shareUrl;
  }

  // Close share menu when clicking outside
  document.addEventListener('click', (event) => {
    if (!event.target.closest('.share-menu') && !event.target.closest('.btn-soft-primary')) {
      document.querySelectorAll('.share-menu').forEach(menu => menu.classList.add('hidden'));
    }
  });

  // Initial render
  renderCertifications(certifications);
</script>