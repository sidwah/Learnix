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
                    <a class="nav-link active" href="account-overview.php">
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
                  <!--  <li class="nav-item">
                    <a class="nav-link" href="my-badges.php">
                      <i class="bi-chat-dots nav-icon"></i> Badges
                    </a>
                  </li> -->
                  <li class="nav-item">
                    <a class="nav-link" href="my-certifications.php">
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
          <div class="card">
            <div class="card-header border-bottom">
              <h4 class="card-header-title">Dashboard</h4>
            </div>

            <!-- Body -->
            <div class="card-body">
              <?php
              // session_start();
              $user_id = $_SESSION['user_id'];

              // Enrolled Courses
              $enrolled_sql = "SELECT COUNT(*) AS count FROM enrollments WHERE user_id = ?";
              $enrolled_stmt = $conn->prepare($enrolled_sql);
              $enrolled_stmt->bind_param("i", $user_id);
              $enrolled_stmt->execute();
              $enrolled_count = $enrolled_stmt->get_result()->fetch_assoc()['count'];


              // Get basic counts from enrollments table
              $stats_query = "SELECT 
COUNT(CASE WHEN status = 'Active' AND completion_percentage < 100 THEN 1 END) as active_courses,
COUNT(CASE WHEN completion_percentage >= 100 THEN 1 END) as completed_courses,
COUNT(*) as total_courses
FROM enrollments 
WHERE user_id = ?";

              $stats_stmt = $conn->prepare($stats_query);
              $stats_stmt->bind_param("i", $_SESSION['user_id']);
              $stats_stmt->execute();
              $stats_result = $stats_stmt->get_result();
              $stats = $stats_result->fetch_assoc();

              // If no stats found, set defaults
              $active_count = $stats['active_courses'] ?? 0;
              $completed_count = $stats['completed_courses'] ?? 0;
              // $total_hours = rand(5, 50); // Placeholder

              ?>

              <div class="row">
                <!-- Enrolled Courses -->
                <div class="col-md-4">
                  <a class="card card-sm shadow-sm rounded" href="#">
                    <div class="card-body text-center">
                      <i class="bi bi-journal-plus fs-1 text-primary mb-2"></i>
                      <h1 class="card-text large text-body"><?= $enrolled_count ?></h1>
                      <h5 class="card-title text-inherit">Enrolled Courses</h5>
                    </div>
                  </a>
                </div>

                <!-- Completed Courses -->
                <div class="col-md-4">
                  <a class="card card-sm shadow-sm rounded" href="#">
                    <div class="card-body text-center">
                      <i class="bi bi-check2-circle fs-1 text-success mb-2"></i>
                      <h1 class="card-text large text-body"><?= $completed_count ?></h1>
                      <h5 class="card-title text-inherit">Completed Courses</h5>
                    </div>
                  </a>
                </div>

                <!-- In Progress Courses -->
                <div class="col-md-4">
                  <a class="card card-sm shadow-sm rounded" href="#">
                    <div class="card-body text-center">
                      <i class="bi bi-hourglass-split fs-1 text-warning mb-2"></i>
                      <h1 class="card-text large text-body"><?= $active_count ?></h1>
                      <h5 class="card-title text-inherit">In Progress Courses</h5>
                    </div>
                  </a>
                </div>
              </div>

            </div>
            <!-- End Body -->
          </div>
          <!-- End Card -->


          <!-- Card -->
          <!-- <div id="editAddressCard" class="card"> -->
            <!-- <div class="card-header border-bottom">
              <h4 class="card-header-title">My Learnings</h4>
            </div> -->



            <!-- Body -->
            <!-- <div class="card-body"> -->
              <!-- <p class="center">No Courses yet</p> -->
              <!-- Add your course content here -->
            <!-- </div> -->
            <!-- End Body -->
          <!-- </div> -->
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