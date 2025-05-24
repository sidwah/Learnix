<?php
// Make sure session is started in the including file
// session_start();  // Usually done in the header.php file

// includes/department/sidebar.php

// Check if user is logged in
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["role"])) {
    // Redirect to login page if not logged in
    header("Location: ../auth/sign-in.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$user_role = $_SESSION["role"]; // Set the user role from session

// Get first name and last name from session
$first_name = $_SESSION["first_name"] ?? '';
$last_name = $_SESSION["last_name"] ?? '';

// Get profile pic from session or use default
$profile_pic = $_SESSION["profile_pic"] ?? 'default.png';

// Create user data array from session variables
$user_data = [
    'first_name' => $first_name,
    'last_name' => $last_name,
    'profile_pic' => $profile_pic
];

// No database query needed since we're using session variables
?>

<!-- Navbar Collapse -->
<div id="navbarVerticalNavMenu" class="collapse navbar-collapse">
  <div class="navbar-brand-wrapper border-end" style="height: auto;">
    <!-- Default Logo -->
    <div class="d-flex align-items-center mb-2">
      <a class="navbar-brand" href="index.php" aria-label="Learnix">
        <img class="navbar-brand-logo" src="../assets/svg/logos/logo.svg" alt="Logo">
      </a>
    </div>
    <!-- End Default Logo -->
    
    <!-- User Info - Compact Layout -->
    <div class="d-flex align-items-center mb-1 px-2  mt-3">
      <div class="avatar avatar-sm avatar-circle">
        <img class="avatar-img" src="<?php echo !empty($user_data['profile_pic']) ? '../uploads/' . htmlspecialchars($user_data['profile_pic']) : '../uploads/default.png'; ?>" alt="Profile Image">
      </div>
      <div class="ms-2 overflow-hidden">
        <h6 class="mb-0 text-truncate"><?php echo htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']); ?></h6>
        <span class="badge bg-soft-primary text-primary small">
          <?php echo $user_role === 'department_head' ? 'Department Head' : 'Secretary'; ?>
        </span>
      </div>
    </div>
    <!-- End User Info -->
  </div>
  <div class="docs-navbar-sidebar-aside-body navbar-sidebar-aside-body">
    <ul id="navbarSettings" class="navbar-nav nav nav-vertical nav-tabs nav-tabs-borderless nav-sm">
      <!-- Dashboard -->
      <li class="nav-item">
        <span class="nav-subtitle">Department Dashboard</span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="index.php">Overview</a>
      </li>

      <li class="nav-item my-2 my-lg-5"></li>

      <!-- Secretary Quick Actions -->
      <?php if ($user_role === 'department_secretary'): ?>
      <li class="nav-item">
        <span class="nav-subtitle">Quick Actions</span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="daily-tasks.php">
          <span class="nav-indicator">🎯</span> Daily Tasks
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="pending-requests.php">
          <span class="nav-indicator">📝</span> Pending Requests
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="urgent-actions.php">
          <span class="nav-indicator">⚡</span> Urgent Actions
        </a>
      </li>
      <li class="nav-item my-2 my-lg-5"></li>
      <?php endif; ?>

      <!-- Department Management -->
      <?php if ($user_role === 'department_head'): ?>
      <li class="nav-item">
        <span class="nav-subtitle">Department Management</span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="settings.php">Settings</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="secretary.php">Secretary</a>
      </li>
      <!-- <li class="nav-item">
        <a class="nav-link" href="manage-secretary.php">Manage Secretary Permissions</a>
      </li> -->
      <li class="nav-item my-2 my-lg-5"></li>
      <?php endif; ?>

      <!-- Staff Management -->
      <li class="nav-item">
        <span class="nav-subtitle">Instructor Management</span>
      </li>
      
      <?php if ($user_role === 'department_head'): ?>
      <li class="nav-item">
        <a class="nav-link" href="invite-instructor.php">Add New Instructor</a>
      </li>
      <?php endif; ?>
      
      <li class="nav-item">
        <a class="nav-link" href="instructors.php">
          <?php echo $user_role === 'department_head' ? 'Manage Instructors' : 'View Instructors'; ?>
        </a>
      </li>
      
      <?php if ($user_role === 'department_secretary'): ?>
      <li class="nav-item">
        <a class="nav-link" href="instructor-requests.php">Process Instructor Requests</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="instructor-documentation.php">Instructor Documentation</a>
      </li>
      <?php endif; ?>

      <li class="nav-item my-2 my-lg-5"></li>

      <!-- Course Management -->
      <li class="nav-item">
        <span class="nav-subtitle">Course Management</span>
      </li>
      
      <?php if ($user_role === 'department_head'): ?>
      <li class="nav-item">
        <a class="nav-link" href="initiate-course.php">Initiate New Course</a>
      </li>
      <li class="nav-item">
        <!-- <a class="nav-link" href="assign-instructors.php">Assign Instructors</a> -->
      </li>
      <?php endif; ?>
      
      <li class="nav-item">
        <a class="nav-link" href="courses.php">Courses</a>
      </li>
      
      <?php if ($user_role === 'department_secretary'): ?>
      <li class="nav-item">
        <a class="nav-link" href="course-metadata.php">Course Information</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="course-scheduling.php">Course Scheduling</a>
      </li>
      <?php endif; ?>
      
      
      
      <li class="nav-item">
        <a class="nav-link" href="enrollments.php">Course Enrollments</a>
      </li>

      
      <!-- Student Management -->
      
      <?php if ($user_role === 'department_secretary'): ?>
        <li class="nav-item my-2 my-lg-5"></li>
        <li class="nav-item">
          <span class="nav-subtitle">Student Management</span>
        </li>
      <li class="nav-item">
        <a class="nav-link" href="student-records.php">Student Records</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="enrollment-support.php">Enrollment Support</a>
      </li>
      <?php endif; ?>

      <li class="nav-item my-2 my-lg-5"></li>

      <!-- Communications -->
      <li class="nav-item">
        <span class="nav-subtitle">Communication</span>
      </li>
      
      
      <li class="nav-item">
        <a class="nav-link" href="announcements.php">
          <?php echo $user_role === 'department_head' ? 'Manage Announcements' : 'View Announcements'; ?>
        </a>
      </li>
      
      <li class="nav-item">
        <a class="nav-link" href="notifications.php">
          <?php echo $user_role === 'department_head' ? 'Manage Notifications' : 'View Notifications'; ?>
        </a>
      </li>
      
      <?php if ($user_role === 'department_secretary'): ?>
      <li class="nav-item">
        <a class="nav-link" href="draft-communications.php">Draft Communications</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="email-management.php">Email Management</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="meeting-coordination.php">Meeting Coordination</a>
      </li>
      <?php endif; ?>

      <li class="nav-item my-2 my-lg-5"></li>

      <!-- Analytics and Reporting -->
      <li class="nav-item">
        <span class="nav-subtitle">Analytics & Reports</span>
      </li>
      
      <li class="nav-item">
        <a class="nav-link" href="reports.php">
          <?php echo $user_role === 'department_head' ? 'Analytics' : 'Standard Reports'; ?>
        </a>
      </li>
      
      <?php if ($user_role === 'department_head'): ?>
      <li class="nav-item">
        <a class="nav-link" href="instructor-performance.php">Instructor Performance</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="course-effectiveness.php">Course Effectiveness</a>
      </li>
      <?php endif; ?>
      
      <li class="nav-item">
        <a class="nav-link" href="activity-tracking.php">
          <?php echo $user_role === 'department_head' ? 'Activity Monitoring' : 'Activity Tracking'; ?>
        </a>
      </li>
      
      <?php if ($user_role === 'department_secretary'): ?>
      <li class="nav-item">
        <a class="nav-link" href="generate-reports.php">Generate Reports</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="data-compilation.php">Data Compilation</a>
      </li>
      <?php endif; ?>

      <li class="nav-item my-2 my-lg-5"></li>

      <!-- Documents & Resources -->
      <li class="nav-item">
        <span class="nav-subtitle">Documents & Resources</span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="documents.php">Documents</a>
      </li>
    
      

      <li class="nav-item my-2 my-lg-5"></li>

      <!-- Profile & Support -->
      <li class="nav-item">
        <span class="nav-subtitle">Profile & Support</span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="profile.php">My Profile</a>
      </li>
      
      <?php if ($user_role === 'department_secretary'): ?>
      <li class="nav-item">
        <a class="nav-link" href="preferences.php">My Preferences</a>
      </li>
      <?php endif; ?>
      
      <li class="nav-item">
        <a class="nav-link" href="help-center.php">Help Center</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="report.php">Report</a>
      </li>

      <li class="nav-item my-2 my-lg-5"></li>

      <!-- Sign Out -->
      <li class="nav-item">
        <a class="nav-link" href="../backend/signout.php">Sign Out</a>
      </li>
    </ul>
  </div>
  
  <!-- Session Management Indicator -->
  <div class="navbar-footer border-top p-3">
    <div class="d-flex justify-content-between align-items-center">
      <!-- <small class="text-muted">
        <i class="bi bi-clock-fill me-1"></i>
        Session: <?php // echo date('H:i'); ?>
      </small> -->
      <small class="text-muted">
        <i class="bi bi-building me-1"></i>
        <?php echo htmlspecialchars($_SESSION['department_name'] ?? 'Department'); ?>
      </small>
    </div>
    <?php if ($user_role === 'department_secretary'): ?>
    <div class="mt-2">
      <small class="badge bg-soft-info text-info w-100">
        <i class="bi bi-shield-check me-1"></i>
        Limited Access Mode
      </small>
    </div>
    <?php endif; ?>
  </div>
</div>
<!-- End Navbar Collapse -->

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const navLinks = document.querySelectorAll('#navbarSettings .nav-link');
    const currentPath = window.location.pathname; // Get the current path
    let activeLink = null;

    // Check localStorage for the saved link
    const savedLink = localStorage.getItem('activeNavLink');

    if (savedLink && currentPath.includes(savedLink)) {
      // Set the active class on the saved link if it matches the current page
      activeLink = Array.from(navLinks).find(link => link.getAttribute('href') === savedLink);
      if (activeLink) {
        activeLink.classList.add('active');
      }
    } else {
      // Automatically set the active class based on URL
      navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPath.substring(currentPath.lastIndexOf('/') + 1)) {
          activeLink = link;
          link.classList.add('active');
          localStorage.setItem('activeNavLink', link.getAttribute('href')); // Save the current active link
        }
      });
    }

    // Add click event to all nav links
    navLinks.forEach(link => {
      link.addEventListener('click', function() {
        localStorage.setItem('activeNavLink', this.getAttribute('href')); // Save on click
      });
    });

    // Scroll to the active link if it exists
    if (activeLink) {
      activeLink.scrollIntoView({
        behavior: 'smooth',
        block: 'center'
      });
    }
  });
</script>