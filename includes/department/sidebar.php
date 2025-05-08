<?php
$user_id = $_SESSION["user_id"];
$user_role = $_SESSION["role"]; // Make sure role is set in session

// Query to fetch user data
$query = "SELECT first_name, last_name
          FROM users
          WHERE user_id = $user_id";

// Execute the query
$result = mysqli_query($conn, $query);

// Check if the query was successful and fetch the data
if (!$result) {
  die("Error executing query: " . mysqli_error($conn));
}

// Fetch the user data
$user_data = mysqli_fetch_assoc($result);
?>

<!-- Navbar Collapse -->
<div id="navbarVerticalNavMenu" class="collapse navbar-collapse">
  <div class="navbar-brand-wrapper border-end" style="height: auto;">
    <!-- Default Logo -->
    <div class="d-flex align-items-center mb-3">
      <a class="navbar-brand" href="index.php" aria-label="Space">
        <img class="navbar-brand-logo" src="../assets/svg/logos/logo.svg" alt="Logo">
      </a>
    </div>
    <!-- End Default Logo -->

    <!-- Nav -->
    <ul class="nav nav-segment nav-fill nav-justified">
      <li class="nav-item">
        <a class="nav-link active" href="#">
          <?php echo $user_role === 'department_head' ? 'Department Head' : 'Secretary'; ?>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#"><?php echo htmlspecialchars($user_data['first_name']); ?></a>
      </li>
    </ul>
    <!-- End Nav -->
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

      <!-- Department Management -->
      <?php if ($user_role === 'department_head'): ?>
      <li class="nav-item">
        <span class="nav-subtitle">Department Management</span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="department-settings.php">Department Settings</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="appoint-secretary.php">Secretary Management</a>
      </li>
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
        <a class="nav-link" href="assign-instructors.php">Assign Instructors</a>
      </li>
      <?php endif; ?>
      
      <li class="nav-item">
        <a class="nav-link" href="courses.php">Department Courses</a>
      </li>
      
      <?php if ($user_role === 'department_head'): ?>
      <li class="nav-item">
        <a class="nav-link" href="review-courses.php">Course Review & Approval</a>
      </li>
      <?php else: ?>
      <li class="nav-item">
        <a class="nav-link" href="track-reviews.php">Track Course Reviews</a>
      </li>
      <?php endif; ?>
      
      <li class="nav-item">
        <a class="nav-link" href="enrollments.php">Course Enrollments</a>
      </li>

      <li class="nav-item my-2 my-lg-5"></li>

      <!-- Student Management -->
      <li class="nav-item">
        <span class="nav-subtitle">Student Management</span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="students.php">Department Students</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="student-progress.php">Student Progress</a>
      </li>

      <li class="nav-item my-2 my-lg-5"></li>

      <!-- Communications -->
      <li class="nav-item">
        <span class="nav-subtitle">Communication</span>
      </li>
      
      <?php if ($user_role === 'department_head'): ?>
      <li class="nav-item">
        <a class="nav-link" href="create-announcement.php">Create Announcements</a>
      </li>
      <?php endif; ?>
      
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
      <?php endif; ?>

      <li class="nav-item my-2 my-lg-5"></li>

      <!-- Analytics and Reporting -->
      <li class="nav-item">
        <span class="nav-subtitle">Analytics & Reports</span>
      </li>
      
      <li class="nav-item">
        <a class="nav-link" href="department-reports.php">
          <?php echo $user_role === 'department_head' ? 'Department Analytics' : 'Standard Reports'; ?>
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

      <li class="nav-item my-2 my-lg-5"></li>

      <!-- Documents & Resources -->
      <li class="nav-item">
        <span class="nav-subtitle">Documents & Resources</span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="department-documents.php">Department Documents</a>
      </li>
      
      <?php if ($user_role === 'department_secretary'): ?>
      <li class="nav-item">
        <a class="nav-link" href="document-management.php">Manage Documentation</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="schedule-management.php">Department Schedule</a>
      </li>
      <?php endif; ?>
      
      <li class="nav-item">
        <a class="nav-link" href="resources.php">Shared Resources</a>
      </li>

      <li class="nav-item my-2 my-lg-5"></li>

      <!-- Profile & Support -->
      <li class="nav-item">
        <span class="nav-subtitle">Profile & Support</span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="profile-settings.php">My Profile</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="help-center.php">Help Center</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="contact-admin.php">Contact Admin</a>
      </li>

      <li class="nav-item my-2 my-lg-5"></li>

      <!-- Sign Out -->
      <li class="nav-item">
        <a class="nav-link" href="../backend/signout.php">Sign Out</a>
      </li>
    </ul>
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