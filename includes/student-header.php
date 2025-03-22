<?php
require '../backend/session_start.php'; // Ensure session is started
require '../backend/config.php'; // Ensure connection file is correct

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'student') {
  // Log unauthorized access attempt for security auditing
  error_log("Unauthorized access attempt detected: " . json_encode($_SERVER));

  // Redirect unauthorized users to a custom unauthorized access page or login page
  header('Location: ../');


  exit;
}

?>


<?php

// Database connection
require '../backend/config.php'; // Ensure this file contains the connection to your database

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  echo "User not logged in!";
  exit;
}

$user_id = $_SESSION['user_id'];

// Prepare and execute query
$stmt = $conn->prepare("SELECT first_name, last_name, email, profile_pic FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
  $row = $result->fetch_assoc(); // Fetch user details
  $_SESSION['email'] = $row['email'];
} else {
  echo "User not found!";
  exit;
}

// Close statement
$stmt->close();
// $conn->close();
?>

<!DOCTYPE html>
<html lang="en" dir="">


<head>
  <!-- Required Meta Tags Always Come First -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- Title -->
  <title>Learnix | Your Pathway to Knowledge and Growth</title>

  <!-- Favicon -->
  <link rel="shortcut icon" href="../favicon.ico">

  <!-- Font -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet">

  <!-- CSS Implementing Plugins -->
  <link rel="stylesheet" href="../assets/css/vendor.min.css">
  <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/vendor/aos/dist/aos.css">

  <!-- CSS Learnix Template -->
  <link rel="stylesheet" href="../assets/css/theme.minc619.css?v=1.0">
</head>

<body>
  <!-- ========== HEADER ========== -->
  <header id="header" class="navbar navbar-expand-lg navbar-end navbar">
    <div class="container">
      <nav class="js-mega-menu navbar-nav-wrap">
        <!-- Default Logo -->
        <a class="navbar-brand" href="index.php" aria-label="Learnix">
          <img class="navbar-brand-logo" src="../assets/svg/logos/logo.svg" alt="Logo">
        </a>
        <!-- End Default Logo -->

        <!-- Secondary Content -->
        <div class="navbar-nav-wrap-secondary-content">

          <!-- Account -->
          <div class="dropdown">
            <a href="#" id="navbarProfileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-dropdown-animation>
              <img class="avatar avatar-xs avatar-circle" src="../uploads/profile/<?php echo htmlspecialchars($row['profile_pic']); ?>" alt="User Profile">
            </a>

            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarProfileDropdown" style="min-width: 16rem;">
              <!-- Profile Info -->
              <a class="d-flex align-items-center p-2 text-decoration-none" href="account-overview.php">
                <div class="flex-shrink-0">
                  <img class="avatar" src="../uploads/profile/<?php echo htmlspecialchars($row['profile_pic']); ?>" alt="User Profile">
                </div>
                <div class="flex-grow-1 ms-3">
                  <span class="d-block fw-semi-bold"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></span>
                  <span class="d-block text-muted small"><?php echo htmlspecialchars($row['email']); ?></span>
                </div>
              </a>

              <div class="dropdown-divider my-3"></div>

              <!-- User Role -->
              <a class="dropdown-item" href="#">
                <span class="dropdown-item-icon">
                  <i class="bi-person-badge"></i>
                </span> <?php echo htmlspecialchars($_SESSION['role']); ?>
              </a>

              <div class="dropdown-divider my-3"></div>

              <!-- Account Settings -->
              <a class="dropdown-item" href="account-overview.php">
                <span class="dropdown-item-icon">
                  <i class="bi-person"></i>
                </span> Account
              </a>
              <a class="dropdown-item" href="account-notifications.php">
                <span class="dropdown-item-icon">
                  <i class="bi-chat-left-dots"></i>
                </span> Notifications
              </a>
              <a class="dropdown-item" href="payment-history.php">
                <span class="dropdown-item-icon">
                  <i class="bi-wallet2"></i>
                </span> Purchase History
              </a>
              <a class="dropdown-item" href="payment-method.php">
                <span class="dropdown-item-icon">
                  <i class="bi-credit-card"></i>
                </span> Payment Methods
              </a>

              <div class="dropdown-divider"></div>

              <!-- Help & Logout -->
              <a class="dropdown-item" href="account-help.php">
                <span class="dropdown-item-icon">
                  <i class="bi-question-circle"></i>
                </span> Help
              </a>

              <a class="dropdown-item text-danger" href="../backend/signout.php">
                <span class="dropdown-item-icon">
                  <i class="bi-box-arrow-right"></i>
                </span> Sign Out
              </a>
            </div>
          </div>

          <!-- End Account -->

        </div>
        <!-- End Secondary Content -->

        <!-- Toggler -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-default">
            <i class="bi-list"></i>
          </span>
          <span class="navbar-toggler-toggled">
            <i class="bi-x"></i>
          </span>
        </button>
        <!-- End Toggler -->

        <!-- Collapse -->
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" href="index.php">Home</a>
            </li>

            <!-- Courses -->
            <li class="hs-has-sub-menu nav-item">
              <a class="nav-link" href="courses.php"><i class="bi-journals me-2"></i> Courses</a>
            </li>
            <!-- End Courses -->
            <?php
// Fetch enrolled courses for the current user
$user_id = $_SESSION['user_id'];

// Query to fetch the top 3 most recently enrolled courses
$enrolled_courses_query = "
    SELECT c.course_id, c.title, c.thumbnail, i.first_name, i.last_name, 
           e.completion_percentage, e.enrolled_at, e.last_accessed
    FROM enrollments e
    JOIN courses c ON e.course_id = c.course_id
    JOIN instructors ins ON c.instructor_id = ins.instructor_id
    JOIN users i ON ins.user_id = i.user_id
    WHERE e.user_id = ?
    ORDER BY e.enrolled_at DESC
    LIMIT 3
";

$stmt = $conn->prepare($enrolled_courses_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$enrolled_courses_result = $stmt->get_result();

// Count total enrolled courses (for showing the "See all" text)
$total_courses_query = "SELECT COUNT(*) as total FROM enrollments WHERE user_id = ?";
$total_stmt = $conn->prepare($total_courses_query);
$total_stmt->bind_param("i", $user_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_courses = $total_row['total'];

// Store courses in an array
$enrolled_courses = [];
while ($course = $enrolled_courses_result->fetch_assoc()) {
    $enrolled_courses[] = $course;
}
?>

<!-- My Courses Dropdown HTML -->
<li class="hs-has-mega-menu nav-item" data-hs-mega-menu-item-options='{
      "desktop": {
        "maxWidth": "22rem"
      }
    }'>
  <a id="myCoursesMegaMenu" class="hs-mega-menu-invoker nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">My Courses</a>

  <!-- Mega Menu -->
  <div class="hs-mega-menu hs-position-right dropdown-menu" aria-labelledby="myCoursesMegaMenu" style="min-width: 32rem;">
    
    <?php if (count($enrolled_courses) > 0): ?>
      <!-- Enrolled Courses -->
      <?php foreach ($enrolled_courses as $key => $course): ?>
        <!-- Course -->
        <a class="navbar-dropdown-menu-media-link" href="course-details.php?id=<?php echo htmlspecialchars($course['course_id']); ?>">
          <div class="d-flex">
            <div class="flex-shrink-0">
              <img class="avatar" src="../uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>" alt="Course Thumbnail">
            </div>

            <div class="flex-grow-1 ms-3">
              <div class="mb-3">
                <span class="navbar-dropdown-menu-media-title"><?php echo htmlspecialchars($course['title']); ?></span>
                <p class="navbar-dropdown-menu-media-desc">By <?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?></p>
              </div>
              <div class="d-flex justify-content-between">
                <span class="card-subtitle text-body">
                  <?php 
                  if ($course['completion_percentage'] >= 100) {
                      echo 'Completed';
                  } else {
                      echo 'In Progress';
                  }
                  ?>
                </span>
                <small class="text-dark fw-semi-bold"><?php echo htmlspecialchars(number_format($course['completion_percentage'], 0)); ?>%</small>
              </div>
              <div class="progress">
                <div class="progress-bar <?php echo $course['completion_percentage'] >= 100 ? 'bg-success' : ''; ?>" 
                     role="progressbar" 
                     style="width: <?php echo htmlspecialchars($course['completion_percentage']); ?>%;" 
                     aria-valuenow="<?php echo htmlspecialchars($course['completion_percentage']); ?>" 
                     aria-valuemin="0" 
                     aria-valuemax="100"></div>
              </div>
            </div>
          </div>
        </a>
        <!-- End Course -->
        
        <?php if ($key < count($enrolled_courses) - 1): ?>
          <div class="dropdown-divider my-3"></div>
        <?php endif; ?>
      <?php endforeach; ?>
    
      <?php if ($total_courses > 3): ?>
        <!-- See All Courses Link -->
        <div class="dropdown-divider my-3"></div>
        <a class="dropdown-item text-center" href="my-courses.php">
          <span>See All Courses (<?php echo htmlspecialchars($total_courses); ?>)</span>
          <i class="bi-chevron-right small ms-1"></i>
        </a>
      <?php endif; ?>

    <?php else: ?>
      <!-- No courses message -->
      <div class="text-center p-4">
        <p class="mb-0">You haven't enrolled in any courses yet.</p>
        <a href="courses.php" class="btn btn-sm btn-primary mt-3">Browse Courses</a>
      </div>
    <?php endif; ?>
    
  </div>
  <!-- End Mega Menu -->
</li>
</ul>
        </div>
        <!-- End Collapse -->
      </nav>
    </div>
  </header>

  <!-- ========== END HEADER ========== -->