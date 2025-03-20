<?php
require '../backend/session_start.php'; // Ensure session is started
require '../backend/config.php'; // Ensure session is started

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'admin') {
    // Log unauthorized access attempt for security auditing
    error_log("Unauthorized access attempt detected: " . json_encode($_SERVER));

    // Redirect unauthorized users to a custom unauthorized access page or login page
    header('Location: signin.php');
    exit;
}
?>

<?php
    // Include the database configuration file
    // include '../connect.php';

    // Query to fetch the counts for each category
  // $query = "SELECT 
  //             (SELECT COUNT(*) FROM users) AS user_count,
  //             (SELECT COUNT(*) FROM courses) AS course_count,
  //             (SELECT COUNT(*) FROM course_categories) AS categories_count,
  //             (SELECT COUNT(*) FROM lessons) AS lesson_count,
  //             (SELECT COUNT(*) FROM user_activity) AS activity_count,
  //             (SELECT COUNT(*) FROM feedback) AS feedback_count,
  //             (SELECT COUNT(*) FROM users WHERE role = 'student') AS student_count,
  //             (SELECT COUNT(*) FROM users WHERE role = 'instructor') AS instructor_count,
  //             (SELECT COUNT(*) FROM enrollments) AS enrollment_count";

  // // Execute the query
  // $result = mysqli_query($conn, $query);

  // // Check if the query was successful and fetch the data
  // if ($result) {
  //     $data = mysqli_fetch_assoc($result);
  //     $user_count = $data['user_count'];
  //     $course_count = $data['course_count'];
  //     $categories_count = $data['categories_count'];
  //     $lesson_count = $data['lesson_count'];
  //     $activity_count = $data['activity_count'];
  //     $feedback_count = $data['feedback_count'];
  //     $student_count = $data['student_count'];
  //     $instructor_count = $data['instructor_count'];
  //     $enrollment_count = $data['enrollment_count'];
  // } else {
  //     // Handle the error if the query fails
  //     die("Error executing query: " . mysqli_error($conn));
  // }

  // // Query to fetch the recent activity logs from user_activity table
  // $query = "SELECT username, activity_type, activity_details, activity_time
  //           FROM user_activity
  //           ORDER BY activity_time DESC
  //           LIMIT 4";

  // // Execute the query
  // $result = mysqli_query($conn, $query);

  // // Check if the query was successful and fetch the data
  // if ($result) {
  //     // Initialize an array to hold the activity data
  //     $activities = mysqli_fetch_all($result, MYSQLI_ASSOC);
  // } else {
  //     // Handle the error if the query fails
  //     die("Error executing query: " . mysqli_error($conn));
  // }

  // // Close the database connection
  // // mysqli_close($conn);

  // // Function to format time difference into human-readable form
  // function time_elapsed_string($datetime, $full = false) {
  //     $now = new DateTime;
  //     $ago = new DateTime($datetime);
  //     $diff = $now->diff($ago);

  //     // Manually calculate the number of weeks
  //     $weeks = floor($diff->d / 7);
  //     $diff->d -= $weeks * 7;

  //     // Prepare the time difference components
  //     $string = array(
  //         'y' => 'year',
  //         'm' => 'month',
  //         'd' => 'day',
  //         'h' => 'hour',
  //         'i' => 'minute',
  //         's' => 'second',
  //     );

  //     // Include weeks in the string if calculated
  //     if ($weeks > 0) {
  //         $string['w'] = $weeks . ' week' . ($weeks > 1 ? 's' : '');
  //     }

  //     // Iterate through the time components and format them
  //     foreach ($string as $k => &$v) {
  //         // Only access valid DateInterval properties
  //         if ($k !== 'w' && !$diff->$k) {
  //             unset($string[$k]);
  //         } elseif ($k !== 'w') {
  //             $v = $diff->$k . ' ' . ($diff->$k > 1 ? $v . 's' : $v);
  //         }
  //     }

  //     // If not full, return only the first component
  //     if (!$full) $string = array_slice($string, 0, 1);

  //     return $string ? implode(', ', $string) . ' ago' : 'just now';
  // }
?>


<!DOCTYPE html>
<html lang="en">

<!-- Mirrored from htmlstream.com/preview/front-v4.2/html/documentation/index.php by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 02 Aug 2022 18:12:04 GMT -->

<head>
    <!-- Required Meta Tags Always Come First -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Title -->
    <title> Learnix | Admin </title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="../favicon.ico">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet">

    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="../assets/css/vendor.min.css">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/font/bootstrap-icons.css">

    <!-- CSS Front Template -->
    <link rel="stylesheet" href="../assets/css/theme.minc619.css?v=1.0">
    <link rel="stylesheet" href="../assets/css/docs.css">
</head>

<body class="navbar-sidebar-aside-lg">
  <!-- ========== HEADER ========== -->
  <header id="header" class="navbar navbar-expand navbar-fixed navbar-end navbar-light navbar-sticky-lg-top bg-white">
    <div class="container-fluid">
      <nav class="navbar-nav-wrap">
        <div class="row flex-grow-1">
          <!-- Default Logo -->
          <div class="docs-navbar-sidebar-container d-flex align-items-center mb-2 mb-lg-0">
            <a class="navbar-brand" href="index.php" aria-label="Space">
              <img class="navbar-brand-logo" src="../assets/svg/logos/logo.svg" alt="Logo">
            </a>
            <a href="index.php">
              <span class="badge bg-soft-primary text-primary">Admin</span>
            </a>
          </div>
          <!-- End Default Logo -->

          <div class="col-md px-lg-0">
            <div class="d-flex justify-content-between align-items-center px-lg-5 px-xl-10">
              <div class="d-none d-md-block">
                <!-- Search Form -->
                <form id="snippetsSearch" class="position-relative" data-hs-list-options='{
                       "searchMenu": true,
                       "keyboard": true,
                       "item": "searchTemplate",
                       "valueNames": ["component", "category", {"name": "link", "attr": "href"}],
                       "empty": "#searchNoResults"
                     }'>
                  <!-- Input Group -->
                  <div class="input-group input-group-merge navbar-input-group">
                    <div class="input-group-prepend input-group-text">
                      <i class="bi-search"></i>
                    </div>


                    <a class="input-group-append input-group-text" href="javascript:;">
                      <i id="clearSearchResultsIcon" class="bi-x" style="display: none;"></i>
                    </a>
                  </div>
                  <!-- End Input Group -->

                  <!-- List -->
                  <div class="list dropdown-menu w-100 overflow-auto" style="max-height: 16rem;"></div>
                  <!-- End List -->

                  <!-- Empty -->
                  <div id="searchNoResults" style="display: none;">
                    <div class="text-center p-4">
                      <img class="mb-3" src="../assets/svg/illustrations/oc-error.svg" alt="Image Description" style="width: 10rem;">
                      <p class="mb-0">No Results</p>
                    </div>
                  </div>
                  <!-- End Empty -->
                </form>
                <!-- End Search Form -->

                <!-- List Item Template -->
                <div class="d-none">
                  <div id="searchTemplate" class="dropdown-item">
                    <a class="d-block link" href="#">
                      <span class="category d-block fw-normal text-muted mb-1"></span>
                      <span class="component text-dark"></span>
                    </a>
                  </div>
                </div>
                <!-- End List Item Template -->
              </div>

              <!-- Navbar -->
              <ul class="navbar-nav p-0">
               
                <li class="nav-item">
                  <a class="btn btn-primary btn-sm" href="../users/">
                    <i class="bi-eye me-1"></i> Preview Demo
                  </a>
                </li>
              </ul>
              <!-- End Navbar -->
            </div>
          </div>
          <!-- End Col -->
        </div>
        <!-- End Row -->
      </nav>
    </div>
  </header>
  <!-- ========== END HEADER ========== -->

  <!-- ========== MAIN CONTENT ========== -->
  <main id="content" role="main">
    <!-- Navbar -->
    <nav class="js-nav-scroller navbar navbar-expand-lg navbar-sidebar navbar-vertical navbar-light bg-white border-end" data-hs-nav-scroller-options='{
            "type": "vertical",
            "target": ".navbar-nav .active",
            "offset": 80
           }'>
      <!-- Navbar Toggle -->
      <button type="button" class="navbar-toggler btn btn-white d-grid w-100" data-bs-toggle="collapse" data-bs-target="#navbarVerticalNavMenu" aria-label="Toggle navigation" aria-expanded="false" aria-controls="navbarVerticalNavMenu">
        <span class="d-flex justify-content-between align-items-center">
          <span class="h6 mb-0">Nav menu</span>

          <span class="navbar-toggler-default">
            <i class="bi-list"></i>
          </span>

          <span class="navbar-toggler-toggled">
            <i class="bi-x"></i>
          </span>
        </span>
      </button>
      <!-- End Navbar Toggle -->

      <!-- Navbar Collapse -->
      <div id="navbarVerticalNavMenu" class="collapse navbar-collapse">
        <div class="navbar-brand-wrapper border-end" style="height: auto;">
          <!-- Default Logo -->
          <div class="d-flex align-items-center mb-3">
            <a class="navbar-brand" href="index.php" aria-label="Space">
              <img class="navbar-brand-logo" src="../assets/svg/logos/logo.svg" alt="Logo">
            </a>
            <a class="navbar-brand-badge" href="index.php">
              <span class="badge bg-soft-primary text-primary ms-2">Admin</span>
            </a>
          </div>
          <!-- End Default Logo -->

          <!-- Nav -->
          <ul class="nav nav-segment nav-fill nav-justified">
            <li class="nav-item">
                            <a class="nav-link active" href="">Welcome Back, <?php echo $_SESSION['first_name']?></a>

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
                <a class="nav-link active" href="index.php">Overview</a>
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
                <a class="nav-link" href="admins.php">Admins</a>
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
                <a class="nav-link" href="add-course.php">Add New Course</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="categories.php">Course Categories</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="enrollments.php">Manage Enrollments</a>
                </li>

                <li class="nav-item my-2 my-lg-5"></li>

                <!-- Instructor Approval -->
                <li class="nav-item">
                <span class="nav-subtitle">Instructor Management</span>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="instructor-approval.php">Approve Instructors</a>
                </li>

                <li class="nav-item my-2 my-lg-5"></li>

                <!-- Payments and Earnings -->
                <li class="nav-item">
                <span class="nav-subtitle">Payments and Earnings</span>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="payments.php">Manage Payments</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="instructor-earnings.php">Instructor Earnings</a>
                </li>

                <li class="nav-item my-2 my-lg-5"></li>

                <!-- Reports and Analytics -->
                <li class="nav-item">
                <span class="nav-subtitle">Reports & Analytics</span>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="student-performance.php">Student Performance</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="course-completion.php">Course Completion Rates</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="engagement-stats.php">Engagement Stats</a>
                </li>

                <li class="nav-item my-2 my-lg-5"></li>

                <!-- Content Management -->
                <li class="nav-item">
                <span class="nav-subtitle">Content Management</span>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="course-materials.php">Course Materials</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="lesson-plans.php">Lesson Plans</a>
                </li>

                <li class="nav-item my-2 my-lg-5"></li>

                <!-- Interactive Learning -->
                <li class="nav-item">
                <span class="nav-subtitle">Interactive Learning</span>
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

                <li class="nav-item my-2 my-lg-5"></li>

                <!-- Sign Out -->
                <li class="nav-item">
                <a class="nav-link" href="../backend/signout.php">Sign Out</a>
                </li>
            </ul>
        </div>

      </div>
      <!-- End Navbar Collapse -->
    </nav>
    <!-- End Navbar -->

    <!-- Content -->
    <div class="navbar-sidebar-aside-content content-space-t-3 content-space-b-2 px-lg-5 px-xl-10">
        <div class="row justify-content-between align-items-center mb-10">
            <div class="col-md-6 col-xl-5">
                <div class="mb-4">
                    <h1 class="display-5 mb-3">Admin <span class="text-primary text-highlight-warning">Dashboard</span> Overview</h1>
                    <p class="lead">Manage your system and track key activities in real-time. Get quick insights into the platform's performance.</p>
                </div>
          <div class="d-flex flex-wrap gap-2">
            <!-- Card -->
            <div class="bg-soft-secondary text-center rounded p-3" style="min-width: 7rem;">
              <h2 class="h1 fw-normal mb-1"><?php echo $user_count; ?></h2>
              <span class="text-cap mb-0" style="font-size: 0.75rem;">Users</span>
            </div>
            <!-- End Card -->

            <!-- Card -->
            <div class="bg-soft-secondary text-center rounded p-3" style="min-width: 7rem;">
              <h2 class="h1 fw-normal mb-1"><?php echo $course_count; ?></h2>
              <span class="text-cap mb-0" style="font-size: 0.75rem;">Courses</span>
            </div>
            <!-- End Card -->

            <!-- Card -->
            <div class="bg-soft-secondary text-center rounded p-3" style="min-width: 7rem;">
              <h2 class="h1 fw-normal mb-1"><?php echo $feedback_count; ?></h2>
              <span class="text-cap mb-0" style="font-size: 0.75rem;">Feedback</span>
            </div>
            <!-- End Card -->
          </div>
            </div>
            <!-- End Col -->

            <div class="col-md-6 col-xl-6">
                <img class="img-fluid" src="../assets/svg/illustrations/oc-building-apps.svg" alt="Admin Dashboard Illustration">
            </div>
            <!-- End Col -->
        </div>

        <hr class="mb-5">

        <!-- Dashboard Summary Section -->
        <div class="row">
            <!-- Card for Dashboard Summary Title -->
            <div class="col-sm-3 mb-5 mb-sm-0">
                <h4>Dashboard Summary</h4>
            </div>

            <div class="col-sm-9">
                <div class="row row-cols-sm-2 row-cols-md-3">
                    <!-- Total Students Card -->
                    <div class="col mb-4">
                        <!-- Card -->
                        <a class="card card-sm card-transition h-100" href="#" data-aos="fade-up">
                            <div class="card-body text-center">
                                <h5 class="card-title text-inherit">Total Students</h5>
                                <h1 class="card-text large text-body"><?php echo number_format($student_count); ?></h1>
                            </div>
                        </a>
                        <!-- End Card -->
                    </div>
                    <!-- End Col -->

                    <!-- Total Instructors Card -->
                    <div class="col mb-4">
                        <!-- Card -->
                        <a class="card card-sm card-transition h-100" href="#" data-aos="fade-up">
                            <div class="card-body text-center">
                                <h5 class="card-title text-inherit">Total Instructors</h5>
                                <h1 class="card-text large text-body"><?php echo number_format($instructor_count); ?></h1>
                            </div>
                        </a>
                        <!-- End Card -->
                    </div>
                    <!-- End Col -->

                    <!-- Total Courses Card -->
                    <div class="col mb-4">
                        <!-- Card -->
                        <a class="card card-sm card-transition h-100" href="#" data-aos="fade-up">
                            <div class="card-body text-center">
                                <h5 class="card-title text-inherit">Total Courses</h5>
                                <h1 class="card-text large text-body"><?php echo number_format($course_count); ?></h1>
                            </div>
                        </a>
                        <!-- End Card -->
                    </div>
                    <!-- End Col -->

                    <!-- Total Enrollments Card -->
                    <div class="col mb-4">
                        <!-- Card -->
                        <a class="card card-sm card-transition h-100" href="#" data-aos="fade-up">
                            <div class="card-body text-center">
                                <h5 class="card-title text-inherit">Total Enrollments</h5>
                                <h1 class="card-text large text-body"><?php echo number_format($enrollment_count); ?></h1>
                            </div>
                        </a>
                        <!-- End Card -->
                    </div>
                    <!-- End Col -->
                </div>
                <!-- End Row -->
            </div>
            <!-- End Col -->
        </div>
        <!-- End Row -->


        <hr class="mb-5">

        <!-- Recent Activity Section -->
        <div class="row mt-5">
            <!-- Header for Recent Activity -->
            <div class="col-sm-3 mb-5 mb-sm-0">
                <h4>Recent Activity</h4>
            </div>

            <!-- Column with Cards for Recent Activity -->
            <div class="col-sm-9">
                <div class="row row-cols-sm-2 row-cols-md-3">
                    <?php
                        // Loop through each activity and display it in a card
                        foreach ($activities as $activity) {
                            // Set the badge color based on the activity type
                            $badge_class = '';
                            switch ($activity['activity_type']) {
                                case 'login':
                                    $badge_class = 'bg-success';
                                    break;
                                case 'Sign Out':
                                    $badge_class = 'bg-danger';
                                    break;
                                case 'quiz_attempt':
                                    $badge_class = 'bg-primary';
                                    break;
                                case 'course_view':
                                    $badge_class = 'bg-info';
                                    break;
                                case 'profile_update':
                                    $badge_class = 'bg-warning';
                                    break;
                                default:
                                    $badge_class = 'bg-secondary';
                            }
                    ?>
                    <!-- Activity Card -->
                    <div class="col mb-4">
                        <a class="card card-sm card-transition h-100" href="activity-details.php" data-aos="fade-up">
                            <div class="card-body">
                                <h5 class="card-title text-inherit"><?php echo $activity['username']; ?> <?php echo ucfirst($activity['activity_type']); ?> <?php echo isset($activity['activity_details']) ? "\"".$activity['activity_details']."\"" : ''; ?></h5>
                                <p class="card-text small text-body"><?php echo time_elapsed_string($activity['activity_time']); ?></p>
                                <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($activity['activity_type']); ?></span>
                            </div>
                        </a>
                    </div>
                    <!-- End Activity Card -->
                    <?php
                        }
                    ?>
                </div>
                <!-- End Row -->
            </div>
            <!-- End Col -->
        </div>

        <!-- End Row -->


        <hr class="mb-5">
        <!-- Approved and Rejected Instructor Applications Section -->
        <div class="row mt-5">
            <!-- Header for Approved and Rejected Applications -->
            <div class="col-sm-3 mb-5 mb-sm-0">
                <h4>Instructor Application Status</h4>
            </div>

            <?php
            // Fetch the last 6 instructor applications (approved or rejected), ordered by the most recent first
            $query = "
                SELECT * 
                FROM instructor_applications 
                WHERE approval_status IN ('approved', 'rejected') 
                ORDER BY application_id DESC 
                LIMIT 6
            ";
            $result = mysqli_query($conn, $query);
            $applications = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
            ?>

            <!-- Column with Cards for Approved and Rejected Applications -->
            <div class="col-sm-9">
                <div class="row row-cols-sm-2 row-cols-md-3">

                    <!-- Loop through the fetched applications -->
                    <?php foreach ($applications as $row): ?>
                        <?php
                        $full_name = $row['full_name'];
                        $email = $row['email'];
                        $courses = $row['courses'];
                        $approval_status = $row['approval_status'];
                        $badge_class = $approval_status === 'approved' ? 'bg-success' : 'bg-danger';
                        ?>
                        <!-- Application Card (Approved or Rejected) -->
                        <div class="col mb-4">
                            <div class="card card-sm card-transition h-100" data-aos="fade-up">
                                <div class="card-body">
                                    <h5 class="card-title text-inherit">Instructor: <?= htmlspecialchars($full_name) ?></h5>
                                    <p class="card-text small text-body">Email: <?= htmlspecialchars($email) ?></p>
                                    <p class="card-text small text-body">Courses Mentioned: <?= htmlspecialchars($courses) ?></p>
                                    <span class="badge <?= $badge_class ?>"><?= ucfirst($approval_status) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>
                <!-- End Row -->
            </div>
            <!-- End Col -->
        </div>
        <!-- End Row -->

        <!-- End Row -->

        <hr class="mb-5">

        <!-- Payments & Earnings Summary Section -->
        <div class="row mt-5">
            <!-- Header for Payments & Earnings Summary -->
            <div class="col-sm-3 mb-5 mb-sm-0">
                <h4>Payments & Earnings Summary</h4>
            </div>

            <?php
            // Fetching transaction data from the database
            $query = "
                SELECT t.*, u.first_name, u.last_name 
                FROM transactions t
                JOIN users u ON t.username = u.username
                ORDER BY t.transaction_date DESC LIMIT 4";  // Fetch the most recent 4 transactions

            $result = mysqli_query($conn, $query);
            $transaction_rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
            ?>

            <!-- Column with Cards for Earnings & Payments Information -->
            <div class="col-sm-9">
                <div class="row row-cols-sm-2 row-cols-md-3">
                    <?php foreach ($transaction_rows as $row) {
                        $instructor_name = $row['first_name'] . ' ' . $row['last_name'];
                        $course_title = $row['course_title'];
                        $amount = $row['amount'];
                        $status = $row['status'];  // 'completed', 'pending', 'failed'
                        $is_free = $row['is_free'];  // 1 if free, 0 if paid
                    ?>
                        <!-- Transaction Card -->
                        <div class="col mb-4">
                            <a class="card card-sm card-transition h-100" href="payment-details.php?id=<?= $row['transaction_id'] ?>" data-aos="fade-up">
                                <div class="card-body">
                                    <h5 class="card-title text-inherit">Instructor: <?= $instructor_name ?></h5>
                                    <p class="card-text small text-body">Course: "<?= $course_title ?>"</p>
                                    <p class="card-text small text-body">
                                        <?php if ($is_free) { ?>
                                            <strong>Free Course</strong>
                                        <?php } else { ?>
                                            Amount: <strong>$<?= number_format($amount, 2) ?></strong>
                                        <?php } ?>
                                    </p>
                                    <?php if ($status == 'pending') { ?>
                                        <span class="badge bg-warning">Pending</span>
                                    <?php } elseif ($status == 'failed') { ?>
                                        <span class="badge bg-danger">Failed</span>
                                    <?php } else { ?>
                                        <span class="badge bg-success">Completed</span>
                                    <?php } ?>
                                </div>
                            </a>
                        </div>
                        <!-- End Transaction Card -->
                    <?php } ?>
                </div>
                <!-- End Row -->
            </div>
            <!-- End Col -->
        </div>
        <!-- End Row -->


        <hr class="mb-5">

        <!-- User Engagement & Performance Section -->
        <div class="row mt-5">
            <!-- Header for User Engagement & Performance -->
            <div class="col-sm-3 mb-5 mb-sm-0">
                <h4>User Engagement & Performance</h4>
            </div>

            <?php
            // Fetching student engagement data (active students with their enrolled courses, progress)
            $query_students = "
                SELECT u.first_name, u.last_name, u.username, COUNT(e.course_id) AS courses_enrolled, 
                    AVG(e.progress) AS avg_progress
                FROM users u
                LEFT JOIN enrollments e ON u.username = e.username
                WHERE u.role = 'student'
                GROUP BY u.username
                LIMIT 6";  // Fetching top 6 active/involved students
            $result_students = mysqli_query($conn, $query_students);
            $student_rows = mysqli_fetch_all($result_students, MYSQLI_ASSOC);

            // Fetching instructor performance data (courses created, students enrolled, average rating)
            $query_instructors = "
                SELECT i.first_name, i.last_name, i.username, COUNT(c.course_id) AS courses_created,
                    (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.course_id) AS students_enrolled, 
                    AVG(r.rating) AS avg_rating
                FROM users i
                JOIN courses c ON i.username = c.instructor_username
                LEFT JOIN course_reviews r ON c.course_id = r.course_id
                WHERE i.role = 'instructor'
                GROUP BY i.username
                ORDER BY avg_rating DESC LIMIT 6";  // Fetching top 6 instructors by performance
            $result_instructors = mysqli_query($conn, $query_instructors);
            $instructor_rows = mysqli_fetch_all($result_instructors, MYSQLI_ASSOC);
            ?>

            <!-- Column with Cards for User Engagement & Performance Information -->
            <div class="col-sm-9">
                <div class="row row-cols-sm-2 row-cols-md-3">

                    <!-- Loop through student engagement data -->
                    <?php foreach ($student_rows as $student) {
                        $student_name = htmlspecialchars($student['first_name'] . ' ' . $student['last_name']);
                        $courses_enrolled = $student['courses_enrolled'];
                        $progress = $student['avg_progress'] ?? 0;  // Default to 0 if null
                        $progress = round($progress, 0);  // Assuming avg_progress is stored as a percentage
                        $status = ($progress >= 50) ? 'Active' : 'Inactive';
                    ?>
                        <!-- Student Engagement Card -->
                        <div class="col mb-4">
                            <a class="card card-sm card-transition h-100" href="student-engagement-details.php?username=<?= urlencode($student['username']) ?>" data-aos="fade-up">
                                <div class="card-body">
                                    <h5 class="card-title text-inherit">Student: <?= $student_name ?></h5>
                                    <p class="card-text small text-body">Courses Enrolled: <?= $courses_enrolled ?></p>
                                    <p class="card-text small text-body">Progress: <strong><?= $progress ?>%</strong></p>
                                    <span class="badge <?= ($status == 'Active') ? 'bg-info' : 'bg-warning' ?>"><?= $status ?></span>
                                </div>
                            </a>
                        </div>
                        <!-- End Student Engagement Card -->
                    <?php } ?>

                    <!-- Loop through instructor performance data -->
                    <?php foreach ($instructor_rows as $instructor) {
                        $instructor_name = htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']);
                        $courses_created = $instructor['courses_created'];
                        $students_enrolled = $instructor['students_enrolled'];
                        $avg_rating = $instructor['avg_rating'] ?? 0;  // Default to 0 if null
                        $avg_rating = round($avg_rating, 1);  // Assuming rating is from 1 to 5
                        $performance_status = ($avg_rating >= 4.5) ? 'Top Instructor' : 'Good Performer';
                        $badge_class = ($performance_status == 'Top Instructor') ? 'bg-success' : 'bg-info';
                    ?>
                        <!-- Instructor Performance Card -->
                        <div class="col mb-4">
                            <a class="card card-sm card-transition h-100" href="instructor-performance-details.php?username=<?= urlencode($instructor['username']) ?>" data-aos="fade-up">
                                <div class="card-body">
                                    <h5 class="card-title text-inherit">Instructor: <?= $instructor_name ?></h5>
                                    <p class="card-text small text-body">Courses Created: <?= $courses_created ?></p>
                                    <p class="card-text small text-body">Students Enrolled: <?= $students_enrolled ?></p>
                                    <p class="card-text small text-body">Average Rating: <strong><?= $avg_rating ?>/5</strong></p>
                                    <span class="badge <?= $badge_class ?>"><?= $performance_status ?></span>
                                </div>
                            </a>
                        </div>
                        <!-- End Instructor Performance Card -->
                    <?php } ?>

                </div>
                <!-- End Row -->
            </div>
            <!-- End Col -->
        </div>
        <!-- End Row -->

        <hr class="mb-5">

        <!-- Notifications Sections-->
        <div class="row mt-5">
            <!-- Header for Notifications -->
            <div class="col-sm-3 mb-5 mb-sm-0">
                <h4>Notifications</h4>
            </div>

            <?php
            // Pagination: Get the current page and calculate the offset
            $limit = 10;  // Number of notifications per page
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;  // Get the current page, default is 1
            $offset = ($page - 1) * $limit;

            // Fetching notifications from the database
            $query_notifications = "
                SELECT n.notification_id, n.message, n.created_at, n.is_read, u.first_name, u.last_name
                FROM notifications n
                LEFT JOIN users u ON n.username = u.username
                ORDER BY n.created_at DESC
                LIMIT $limit OFFSET $offset";  // Fetching notifications with pagination
            $result_notifications = mysqli_query($conn, $query_notifications);
            $notification_rows = mysqli_fetch_all($result_notifications, MYSQLI_ASSOC);

            // Fetching the total number of notifications for pagination purposes
            $query_count = "SELECT COUNT(*) AS total FROM notifications";
            $result_count = mysqli_query($conn, $query_count);
            $total_notifications = mysqli_fetch_assoc($result_count)['total'];
            $total_pages = ceil($total_notifications / $limit);  // Calculate total pages
            ?>

            <!-- Column for Notifications List -->
            <div class="col-sm-9">
                <div class="list-group">
                    <?php foreach ($notification_rows as $notification) {
                        $notification_time = strtotime($notification['created_at']);
                        $time_ago = time() - $notification_time;
                        if ($time_ago < 60) {
                            $time_display = 'Just now';
                        } elseif ($time_ago < 3600) {
                            $time_display = floor($time_ago / 60) . ' minutes ago';
                        } elseif ($time_ago < 86400) {
                            $time_display = floor($time_ago / 3600) . ' hours ago';
                        } else {
                            $time_display = floor($time_ago / 86400) . ' days ago';
                        }

                        // Check if the notification is read
                        $read_class = ($notification['is_read']) ? 'text-muted' : 'text-dark';
                    ?>
                        <!-- Notification Item -->
                        <a href="notification-details.php?notification_id=<?= $notification['notification_id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 <?= $read_class ?>"><?= $notification['message'] ?></h6>
                                <p class="mb-1 <?= $read_class ?>">By: <?= htmlspecialchars($notification['first_name']) ?> <?= htmlspecialchars($notification['last_name']) ?></p>
                            </div>
                            <small><?= $time_display ?></small>
                        </a>
                        <!-- End Notification Item -->
                    <?php } ?>
                </div>
                <!-- End List Group -->

                <!-- Pagination Controls -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= ($page == 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page - 1 ?>" tabindex="-1">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php } ?>
                        <li class="page-item <?= ($page == $total_pages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                        </li>
                    </ul>
                </nav>
                <!-- End Pagination Controls -->
            </div>
            <!-- End Col -->
        </div>
        <!-- End Row -->


    </div>
    <!-- End Content -->
  </main>
  <!-- ========== END MAIN CONTENT ========== -->

  <!-- ========== SECONDARY CONTENTS ========== -->
  <!-- Go To -->
  <a class="js-go-to go-to position-fixed" href="javascript:;" style="visibility: hidden;" data-hs-go-to-options='{
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
  <!-- ========== END SECONDARY CONTENTS ========== -->

  <!-- JS Implementing Plugins -->
  <script src="../assets/js/vendor.min.js"></script>

  <!-- JS Front -->
  <script src="../assets/js/theme.min.js"></script>

  <!-- JS Plugins Init. -->
  <script>
    (function() {
      // INITIALIZATION OF HEADER
      // =======================================================
      new HSHeader('#header').init()


      // INITIALIZATION OF LISTJS COMPONENT
      // =======================================================
      HSCore.components.HSList.init('#snippetsSearch')
      const snippetsSearch = HSCore.components.HSList.getItem('snippetsSearch')


      // GET JSON FILE RESULTS
      // =======================================================
      fetch('../assets/json/snippets-search.json')
      .then(response => response.json())
      .then(data => {
        snippetsSearch.add(data)
      })


      // INITIALIZATION OF GO TO
      // =======================================================
      new HSGoTo('.js-go-to')
    })()
  </script>
</body>

<!-- Mirrored from htmlstream.com/preview/front-v4.2/html/documentation/index.php by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 02 Aug 2022 18:13:25 GMT -->

</html>