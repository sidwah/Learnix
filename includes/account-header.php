<?php
require '../backend/session_start.php'; // Ensure session is started
require '../backend/config.php'; // Ensure connection file is correct

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'student') {
    // Log unauthorized access attempt for security auditing
    error_log("Unauthorized access attempt detected: " . json_encode($_SERVER));

    // Redirect unauthorized users to a custom unauthorized access page or login page
    header('Location: ../pages/');
    exit;
}

// Database connection
// Already included from config.php above

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
} else {
    echo "User not found!";
    exit;
}

// Close statement
$stmt->close();
// $conn->close();

// Fetch enrolled courses for the current user
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
<!DOCTYPE html>
<html lang="en" dir="">

<head>
    <!-- Required Meta Tags Always Come First -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Title -->
    <title>Learnix | Learn Better, Grow Better</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="../favicon.ico" />

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet">

    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="../assets/css/vendor.min.css">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/font/bootstrap-icons.css">

    <!-- CSS Learnix Template -->
    <link rel="stylesheet" href="../assets/css/theme.minc619.css?v=1.0">
</head>

<body>
    <!-- ========== HEADER ========== -->
    <header id="header" class="navbar navbar-expand-lg navbar-end navbar-light navbar-show-hide " data-hs-header-options='{
            "fixMoment": 1000,
            "fixEffect": "slide"
          }'>

        <div class="container">
            <nav class="js-mega-menu navbar-nav-wrap">
                <!-- Default Logo -->
                <a class="navbar-brand" href="index.php" aria-label="Learnix">
                    <img class="navbar-brand-logo" src="../assets/svg/logos/logo.svg" alt="Logo">
                </a>
                <!-- End Default Logo -->

                <!-- Secondary Content -->
                <div class="navbar-nav-wrap-secondary-content">
                    <!-- Search - Visible only on mobile -->
                    <div class="dropdown dropdown-course-search d-lg-none d-inline-block">
                        <a class="btn btn-ghost-secondary btn-sm btn-icon" href="#" id="navbarCourseSearchDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi-search"></i>
                        </a>
                        <div class="dropdown-menu dropdown-card" aria-labelledby="navbarCourseSearchDropdown">
                            <!-- Card -->
                            <div class="card card-sm">
                                <div class="card-body">
                                    <form class="input-group input-group-merge">
                                        <input type="text" class="form-control" placeholder="What do you want to learn?" aria-label="What do you want to learn?">
                                        <div class="input-group-append input-group-text">
                                            <i class="bi-search"></i>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- End Card -->
                        </div>
                    </div>
                    <!-- End Search -->

                    <!-- Account -->
                    <div class="dropdown">
                        <a href="#" id="navbarShoppingCartDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-dropdown-animation>
                            <img class="avatar avatar-xs avatar-circle" src="../uploads/profile/<?php echo $row['profile_pic']; ?>" alt="Profile">
                        </a>

                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarShoppingCartDropdown" style="min-width: 16rem;">
                            <a class="d-flex align-items-center p-2" href="account-overview.php">
                                <div class="flex-shrink-0">
                                    <img class="avatar" src="../uploads/profile/<?php echo $row['profile_pic']; ?>" alt="Profile">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <span class="d-block fw-semi-bold"><?php echo ($row['first_name'] . ' ' . $row['last_name']); ?></span>
                                    <span class="d-block text-muted small"><?php echo $row['email']; ?></span>
                                </div>
                            </a>

                            <div class="dropdown-divider my-3"></div>

                            <a class="dropdown-item" href="#">
                                <span class="dropdown-item-icon">
                                </span> <?php echo $_SESSION['role']; ?>
                            </a>

                            <div class="dropdown-divider my-3"></div>

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

                            <a class="dropdown-item" href="./account-help.php">
                                <span class="dropdown-item-icon">
                                    <i class="bi-question-circle"></i>
                                </span> Help
                            </a>
                            <a class="dropdown-item" href="../backend/signout.php">
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
                            <a id="coursesMegaMenu" class="hs-mega-menu-invoker nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi-journals me-2"></i> Courses</a>
                            
                            <!-- Mega Menu -->
                            <div class="hs-sub-menu dropdown-menu" aria-labelledby="coursesMegaMenu" style="min-width: 17rem;">
                                <!-- Categories listed here - can be added from database -->
                                <a class="dropdown-item" href="courses.php"><i class="bi-grid dropdown-item-icon"></i> All Categories</a>
                                <a class="dropdown-item" href="courses.php?category=technology"><i class="bi-code-slash dropdown-item-icon"></i> Technology</a>
                                <a class="dropdown-item" href="courses.php?category=business"><i class="bi-briefcase dropdown-item-icon"></i> Business</a>
                                <a class="dropdown-item" href="courses.php?category=design"><i class="bi-bezier2 dropdown-item-icon"></i> Design</a>
                                <a class="dropdown-item" href="courses.php?category=marketing"><i class="bi-graph-up dropdown-item-icon"></i> Marketing</a>
                                <a class="dropdown-item" href="courses.php?category=music"><i class="bi-music-note-list dropdown-item-icon"></i> Music</a>
                            </div>
                            <!-- End Mega Menu -->
                        </li>
                        <!-- End Courses -->

                        <!-- Search Form - visible only on desktop -->
                        <li class="nav-item flex-grow-1 d-none d-lg-inline-block">
                            <form class="input-group input-group-merge">
                                <div class="input-group-prepend input-group-text">
                                    <i class="bi-search"></i>
                                </div>
                                <input type="text" class="form-control" placeholder="What do you want to learn?" aria-label="What do you want to learn?">
                            </form>
                        </li>
                        <!-- End Search Form -->

                        <!-- My Courses -->
                        <li class="hs-has-mega-menu nav-item" data-hs-mega-menu-item-options='{
                            "desktop": {
                                "maxWidth": "22rem"
                            }
                        }'>
                            <a id="myCoursesMegaMenu" class="hs-mega-menu-invoker nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">My Courses</a>

                            <!-- Mega Menu -->
                            <div class="hs-mega-menu hs-position-right dropdown-menu" aria-labelledby="myCoursesMegaMenu" style="min-width: 22rem;">
                                
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
                        <!-- End My Courses -->
                    </ul>
                </div>
                <!-- End Collapse -->
            </nav>
        </div>
    </header>
    <!-- ========== END HEADER ========== -->