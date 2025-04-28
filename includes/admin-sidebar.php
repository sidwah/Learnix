<?php
$user_id = $_SESSION["user_id"];
// Query to fetch first name logs from users table
$query = "SELECT first_name, last_name
            FROM users
            WHERE $user_id";

// Execute the query
$result = mysqli_query($conn, $query);

// Check if the query was successful and fetch the data
if (!$result) {

  die("Error executing query: " . mysqli_error($conn));
}

// Close the database connection
// mysqli_close($conn);
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
        <a class="nav-link active" href="#">Admin</a>
      </li>
      <li class="nav-item">
        <?php
        $user_id = $_SESSION["user_id"];
        // Query to fetch first name logs from users table
        $query = "SELECT first_name, last_name 
          FROM users 
          WHERE user_id = $user_id";  // Added "user_id =" before the variable

        // Execute the query
        $result = mysqli_query($conn, $query);

        // Check if the query was successful and fetch the data
        if (!$result) {
          die("Error executing query: " . mysqli_error($conn));
        }

        // Fetch the user data
        $user_data = mysqli_fetch_assoc($result);

        // Close the database connection
        // mysqli_close($conn);
        ?>
        <a class="nav-link" href="#"><?php echo htmlspecialchars($user_data['first_name']); ?> </a>
      </li>
    </ul>
    <!-- End Nav -->
  </div>

  <div class="docs-navbar-sidebar-aside-body navbar-sidebar-aside-body">
    <ul id="navbarSettings" class="navbar-nav nav nav-vertical nav-tabs nav-tabs-borderless nav-sm">
      <!-- Admin Dashboard -->
      <li class="nav-item">
        <span class="nav-subtitle">Admin Dashboard</span>
      </li>
      <li class="nav-item">
        <a class="nav-link " href="index.php">Overview</a>
      </li>

      <li class="nav-item my-2 my-lg-5"></li>

      <!-- User Management -->
      <li class="nav-item">
        <span class="nav-subtitle">User Management</span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="students.php">Students</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="instructors.php">Instructors</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="verify-instructor.php">Verify Instructor</a>
      </li>

      <li class="nav-item my-2 my-lg-5"></li>

      <!-- Course Management -->
      <li class="nav-item">
        <span class="nav-subtitle">Course Management</span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="courses.php">All Courses</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="categories.php">Course Categories</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="enrollments.php">Manage Enrollments</a>
      </li>

      <li class="nav-item my-2 my-lg-5"></li>

      <!-- Content & Learning Management -->
      <li class="nav-item">
        <span class="nav-subtitle">Content & Learning Management</span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="course-materials.php">Course Materials</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="lesson-plans.php">Lesson Plans</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="forums.php">Discussion Forums</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="quizzes.php">Quizzes & Exams</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="certificates.php">Certificates & Badges</a>
      </li>

      <li class="nav-item my-2 my-lg-5"></li>

      <!-- User Insights -->
      <li class="nav-item">
        <span class="nav-subtitle">User Insights</span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="progress-tracking.php">Progress Tracking</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="activity-logs.php">Activity Logs</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="performance-analytics.php">Performance Analytics</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="reports.php">Reports & Analytics</a>
      </li>

      <li class="nav-item my-2 my-lg-5"></li>

      <!-- Payments & Earnings -->
      <li class="nav-item">
        <span class="nav-subtitle">Payments & Earnings</span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="payments.php">Manage Payments</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="instructor-earnings.php">Instructor Earnings</a>
      </li>

      <li class="nav-item my-2 my-lg-5"></li>

      <!-- Announcements & Notifications -->
      <li class="nav-item">
        <span class="nav-subtitle">Announcements & Notifications</span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="announcements.php">Announcements</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="notifications.php">Manage Notifications</a>
      </li>

      <li class="nav-item my-2 my-lg-5"></li>

      <!-- Settings -->
      <li class="nav-item">
        <span class="nav-subtitle">Settings</span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="system-settings.php">System Settings</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="user-roles.php">User Roles & Permissions</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="payment-gateway.php">Payment Gateway</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="customization.php">Customization</a>
      </li>

      <li class="nav-item my-2 my-lg-5"></li>

      <!-- Support -->
      <li class="nav-item">
        <span class="nav-subtitle">Support</span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="faq.php">FAQ</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="contact-support.php">Contact Support</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="chatbot.php">Chatbot Management</a>
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