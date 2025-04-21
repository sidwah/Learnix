<?php
require '../backend/session_start.php'; // Ensure session is started
require '../backend/config.php'; // Ensure connection file is correct
require 'toast.php';

// Check if the user is signed in and has the 'student' role
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

// Fetch enrolled courses for the current user
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

// Fetch notifications from database
$notifications_query = "
    SELECT notification_id, title, message, is_read, created_at, type, related_id, related_type
    FROM user_notifications
    WHERE user_id = ? AND is_hidden = 0
    ORDER BY created_at DESC
    LIMIT 50
";

$stmt = $conn->prepare($notifications_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications_result = $stmt->get_result();

$notifications = [];
while ($notification = $notifications_result->fetch_assoc()) {
    $notifications[] = $notification;
}

// Close statement
$stmt->close();

// Count unread notifications
$unread_count = 0;
foreach ($notifications as $notification) {
    if ($notification['is_read'] == 0) {
        $unread_count++;
    }
}

/**
 * Get time ago string from timestamp
 * 
 * @param string $datetime
 * @return string
 */
function getTimeAgo($datetime)
{
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;

    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' min' . ($mins != 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours != 1 ? 's' : '') . ' ago';
    } elseif ($diff < 172800) {
        return 'Yesterday';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days != 1 ? 's' : '') . ' ago';
    } else {
        return date('M j', $time);
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="">

<head>
    <!-- Required Meta Tags Always Come First -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Preload Dark Mode Check - MUST be first -->
    <script>
        // Immediately check if dark mode should be enabled
        (function() {
            // Check for saved preference
            const darkModeEnabled = localStorage.getItem('darkMode') === 'true';

            if (darkModeEnabled) {
                // Add a class to the HTML element immediately
                document.documentElement.classList.add('dark-mode-preload');

                // Add inline styles to prevent flash of light mode
                const style = document.createElement('style');
                style.textContent = `
                    .dark-mode-preload {
                        background-color: #1e2a36 !important;
                        color: #e9ecef !important;
                    }
                    .dark-mode-preload body,
                    .dark-mode-preload .navbar,
                    .dark-mode-preload .card,
                    .dark-mode-preload .dropdown-menu,
                    .dark-mode-preload .bg-white {
                        background-color: #1e2a36 !important;
                        color: #e9ecef !important;
                    }
                    .dark-mode-preload .navbar-toggler,
                    .dark-mode-preload .form-control {
                        background-color: #253545 !important;
                        color: #e9ecef !important;
                    }
                `;
                document.head.appendChild(style);

                // Tell DarkReader to initialize immediately
                window.loadDarkModeOnStart = true;
            }
        })();
    </script>

    <title>Student | Learnix - Learn Better, Grow Better</title>
    <meta name="description" content="Access your courses, track progress, and achieve your learning goals on Learnix." />
    <meta name="author" content="Learnix Team" />

    <!-- Favicon -->
    <link rel="shortcut icon" href="../favicon.ico" />

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet">

    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="../assets/css/vendor.min.css">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/font/bootstrap-icons.css">

    <!-- CSS Learnix Template -->
    <link rel="stylesheet" href="../assets/css/theme.minc619.css?v=1.0">

    <!-- Dark Reader Library -->
    <script src="https://cdn.jsdelivr.net/npm/darkreader@4.9.58/darkreader.min.js"></script>

    <!-- Enhanced UI Styles -->
    <style>
        /* Notification badge enhancement */
        .notification-badge {
            padding: 0.25rem 0.4rem;
            font-weight: 600;
            border: 2px solid var(--bs-white);
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.15);
        }

        /* Pulse animation for notification badge */
        .pulse-animation {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.85;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Notification panel styling */
        #offcanvasNotifications {
            max-width: 380px;
        }

        /* Notification item styling */
        .notification-item {
            transition: background-color 0.15s ease;
            border-left: 3px solid transparent;
        }

        .notification-item:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.05);
        }

        .notification-item.unread {
            border-left-color: var(--bs-primary);
            background-color: rgba(var(--bs-primary-rgb), 0.03);
        }

        .notification-time {
            font-size: 0.7rem;
            color: var(--bs-secondary);
        }

        /* Avatar styling for notifications */
        .notification-avatar {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Notification action buttons */
        .notification-actions {
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .notification-item:hover .notification-actions {
            opacity: 1;
        }

        /* Empty state styling */
        .notification-empty-state {
            padding: 2.5rem 1rem;
        }

        /* ========== ENHANCED NAV ICONS ========== */
        /* Common styles for all nav icons */
        .nav-link.btn-icon {
            position: relative;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.25s ease;
            background-color: transparent;
            margin: 0 2px;
        }

        .nav-link.btn-icon:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.1);
            transform: translateY(-2px);
        }

        .nav-link.btn-icon:active {
            transform: translateY(0);
        }

        .nav-link.btn-icon i {
            font-size: 1.25rem;
            transition: all 0.2s ease;
        }

        .nav-link.btn-icon:hover i {
            color: var(--bs-primary);
        }

        /* Special hover effect for dark mode toggle */
        #darkModeToggle {
            position: relative;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.25s ease;
            overflow: hidden;
        }

        #darkModeToggle:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.1);
            transform: translateY(-2px);
        }

        #darkModeToggle:active {
            transform: translateY(0);
        }

        #darkModeToggle i {
            font-size: 1.25rem;
            transition: all 0.3s ease;
        }

        #darkModeToggle:hover i {
            color: var(--bs-primary);
            transform: rotate(12deg);
        }

        /* Profile icon special effects */
        .nav-item.dropdown>.nav-link {
            position: relative;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.25s ease;
            padding: 0;
        }

        .nav-item.dropdown>.nav-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .nav-item.dropdown>.nav-link:before {
            content: '';
            position: absolute;
            width: calc(100% + 4px);
            height: calc(100% + 4px);
            border-radius: 50%;
            border: 2px solid transparent;
            top: -2px;
            left: -2px;
            transition: all 0.3s ease;
        }

        .nav-item.dropdown>.nav-link:hover:before {
            border-color: var(--bs-primary);
        }

        .nav-item.dropdown>.nav-link .avatar {
            transition: all 0.3s ease;
        }

        .nav-item.dropdown>.nav-link:hover .avatar {
            transform: scale(1.05);
        }

        /* Hover effect for notification bell */
        .notification-dropdown .nav-link:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.1);
            border-radius: 50%;
            transition: all 0.2s ease;
        }


        /* Courses mega menu enhancement */


        .fade-in-up {
            animation: fadeInUp 0.3s ease forwards;
        }

        @keyframes ripple {
            0% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(0);
            }

            100% {
                opacity: 0;
                transform: translate(-50%, -50%) scale(3);
            }
        }
    </style>

    <script>
        // Dark Reader Configuration
        function configureDarkReader() {
            DarkReader.setFetchMethod(window.fetch);

            // Custom dynamic theme
            const options = {
                brightness: 100,
                contrast: 90,
                sepia: 10,

                // Custom CSS to fix specific elements
                css: `
            /* Fix for dropdown menus */
            .dropdown-menu {
                background-color: #253545 !important;
                border-color: #495057 !important;
            }
            
            .dropdown-item:hover {
                background-color: rgba(13, 110, 253, 0.1) !important;
            }
            
            /* Fix for search input */
            .form-control {
                background-color: #2c3e50 !important;
                color: #eee !important;
                border-color: #495057 !important;
            }
            
            /* Fix for cards */
            .card {
                background-color: #253545 !important;
            }
            
            /* Fix for progress bar backgrounds */
            .progress {
                background-color: #343a40 !important;
            }
            
            /* Fix for course thumbnails */
            .navbar-dropdown-menu-media-title,
            .navbar-dropdown-menu-media-desc {
                color: #e9ecef !important;
            }
            
            /* Fix for offcanvas elements */
            .offcanvas {
                background-color: #1e2a36 !important;
                color: #e9ecef !important;
            }
            
            .offcanvas-header {
                border-color: #495057 !important;
            }
        `,

                // Custom fixes for specific elements
                fixes: {
                    invert: [
                        '.navbar-brand-logo',
                        '.avatar'
                    ],

                    css: '',

                    // Don't invert images
                    ignoreImageAnalysis: ['*'],

                    // Don't apply filtering to certain elements
                    disableStyleSheetsProxy: true
                }
            };

            return options;
        }

        // Apply dark mode if needed immediately
        if (window.loadDarkModeOnStart) {
            const options = configureDarkReader();
            DarkReader.enable(options);
        }

        // DOM Content Loaded Event
        document.addEventListener('DOMContentLoaded', function() {
            // Create toggle button - Now placing it in the #darkModeContainer element
            const darkModeContainer = document.getElementById('darkModeContainer');
            if (darkModeContainer) {
                darkModeContainer.innerHTML = `
            <a id="darkModeToggle" class="nav-link" href="javascript:;" title="Toggle Dark Mode">
                <i class="bi-moon-stars"></i>
                <span class="d-lg-none ms-1">Dark Mode</span>
            </a>
        `;

                // Apply saved preference (button state)
                const darkModeEnabled = localStorage.getItem('darkMode') === 'true';
                updateToggleButton(darkModeEnabled);

                // Add toggle listener
                document.getElementById('darkModeToggle').addEventListener('click', toggleDarkMode);

                // Remove preload class if it exists
                document.documentElement.classList.remove('dark-mode-preload');
            }

            // Add icons to My Courses mega menu
            const myCoursesMegaMenu = document.getElementById('myCoursesMegaMenu');
            if (myCoursesMegaMenu) {
                // Add icon to My Courses text
                myCoursesMegaMenu.innerHTML = '<i class="bi-collection-play me-2"></i> My Courses';

                // Fix dropdown arrow
                myCoursesMegaMenu.classList.add('dropdown-toggle');
            }

            // Enhance the course thumbnails to show a quick preview on hover
            const courseLinks = document.querySelectorAll('.navbar-dropdown-menu-media-link');
            courseLinks.forEach(link => {
                const title = link.querySelector('.navbar-dropdown-menu-media-title');
                const progressBar = link.querySelector('.progress-bar');

                if (progressBar) {
                    // Add animation to progress bars
                    const value = progressBar.style.width;
                    progressBar.style.width = '0%';

                    // Use setTimeout to trigger the animation after a small delay
                    setTimeout(() => {
                        progressBar.style.width = value;
                    }, 200);
                }
            });

            // Add ripple effect to buttons
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(button => {
                button.addEventListener('mousedown', function(e) {
                    const circle = document.createElement('div');
                    const x = e.clientX - this.getBoundingClientRect().left;
                    const y = e.clientY - this.getBoundingClientRect().top;

                    circle.style.position = 'absolute';
                    circle.style.width = '100px';
                    circle.style.height = '100px';
                    circle.style.borderRadius = '50%';
                    circle.style.backgroundColor = 'rgba(255, 255, 255, 0.3)';
                    circle.style.transform = 'translate(-50%, -50%)';
                    circle.style.left = x + 'px';
                    circle.style.top = y + 'px';
                    circle.style.pointerEvents = 'none';
                    circle.style.animation = 'ripple 0.6s linear forwards';

                    this.style.overflow = 'hidden';
                    this.style.position = 'relative';
                    this.appendChild(circle);

                    setTimeout(() => {
                        if (circle.parentNode === this) {
                            this.removeChild(circle);
                        }
                    }, 600);
                });
            });

            // Add animation for the floating action button
            const fab = document.getElementById('floatingActionButton');

            if (fab) {
                // Add a small bounce animation when the page loads
                setTimeout(() => {
                    fab.querySelector('button').style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        fab.querySelector('button').style.transform = 'scale(1)';
                    }, 200);
                }, 1000);

                // Add hover effect
                fab.querySelector('button').addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.1) rotate(45deg)';
                });

                fab.querySelector('button').addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1) rotate(0)';
                });
            }
        });

        // Toggle dark mode function
        function toggleDarkMode() {
            const darkModeEnabled = localStorage.getItem('darkMode') === 'true';
            if (darkModeEnabled) {
                disableDarkMode();
            } else {
                enableDarkMode();
            }
        }

        // Enable dark mode
        function enableDarkMode() {
            const options = configureDarkReader();
            DarkReader.enable(options);
            localStorage.setItem('darkMode', 'true');
            updateToggleButton(true);
        }

        // Disable dark mode
        function disableDarkMode() {
            DarkReader.disable();
            localStorage.setItem('darkMode', 'false');
            updateToggleButton(false);
        }

        // Update button appearance
        function updateToggleButton(isDark) {
            const btn = document.getElementById('darkModeToggle');
            if (!btn) return;

            if (isDark) {
                btn.innerHTML = `
            <i class="bi-sun"></i>
            <span class="d-lg-none ms-1">Light Mode</span>
        `;
            } else {
                btn.innerHTML = `
            <i class="bi-moon-stars"></i>
            <span class="d-lg-none ms-1">Dark Mode</span>
        `;
            }
        }
    </script>
</head>

<body>
    <!-- ========== HEADER ========== -->
    <header id="header" class="navbar navbar-expand-lg navbar-end navbar-light navbar-show-hide" data-hs-header-options='{
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
                        <li class="nav-item">
                            <a class="nav-link" href="courses.php"><i class="bi-journals me-2"></i> Courses</a>
                        </li>



                        <!-- Spacer between left and right side navigation -->
                        <li class="nav-item flex-grow-1 d-none d-lg-inline-block">
                        </li>

                        <!-- RIGHT SIDE NAVIGATION ITEMS -->

                        <!-- 1. Search Icon -->
                        <li class="nav-item d-none d-lg-inline-block">
                            <a class="nav-link btn-icon" href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbarSearch" aria-controls="offcanvasNavbarSearch">
                                <i class="bi-search"></i>
                            </a>
                        </li>

                        <!-- 2. Dark Mode Toggle - This will be filled by JS -->
                        <li class="nav-item d-none d-lg-inline-block" id="darkModeContainer">
                            <!-- Dark mode toggle will be placed here by the script -->
                        </li>

                        <!-- 3. Notifications Icon -->
                        <li class="nav-item dropdown notification-dropdown d-none d-lg-inline-block">
                            <a class="nav-link btn-icon position-relative" href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNotifications" aria-controls="offcanvasNotifications">
                                <i class="bi-bell fs-5"></i>
                                <?php if ($unread_count > 0): ?>
                                    <span class="notification-badge position-absolute translate-middle badge rounded-pill bg-danger pulse-animation"
                                        style="top: 0px; right: -3px; font-size: 0.65rem; transform-origin: center;">
                                        <?php echo $unread_count; ?><span class="visually-hidden">unread notifications</span>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>

                        <!-- 4. My Courses -->
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
                                        <a class="navbar-dropdown-menu-media-link" href="course-materials.php?course_id=<?php echo htmlspecialchars($course['course_id']); ?>">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0">
                                                    <img class="avatar" style="height: auto; width: 60px; object-fit: cover;" src="../uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>" alt="Course Thumbnail">
                                                </div>

                                                <div class="flex-grow-1 ms-3">
                                                    <div class="mb-3">
                                                        <span class="navbar-dropdown-menu-media-title " style="font-size: 0.7rem;"><?php echo htmlspecialchars($course['title']); ?></span>
                                                        <p class="navbar-dropdown-menu-media-desc" style="font-size: 0.7rem;">By <?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?></p>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <span class="card-subtitle text-body " style="font-size: 0.6rem;">
                                                            <?php
                                                            if ($course['completion_percentage'] >= 100) {
                                                                echo 'Completed';
                                                            } else {
                                                                echo 'In Progress';
                                                            }
                                                            ?>
                                                        </span>
                                                        <small style="font-size: 0.6rem;" class="text-dark fw-semi-bold"><?php echo htmlspecialchars(number_format($course['completion_percentage'], 0)); ?>%</small>
                                                    </div>
                                                    <div class="progress" style="height: 0.25rem;">
                                                        <div class="progress-bar <?php echo $course['completion_percentage'] >= 100 ? 'bg-success' : ''; ?>"
                                                            role="progressbar"
                                                            style="width: <?php echo htmlspecialchars($course['completion_percentage']); ?>%; "
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
                                        <a class="dropdown-item text-center" href="enrolled-courses.php">
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

                        <!-- 5. Profile Pic/Account -->
                        <li class="nav-item dropdown">
                            <a href="#" id="navbarShoppingCartDropdown" class="nav-link" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-dropdown-animation>
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
                        </li>
                    </ul>
                </div>
                <!-- End Collapse -->
            </nav>
        </div>
    </header>
    <!-- ========== END HEADER ========== -->

    <!-- ========== OFFCANVAS COMPONENTS ========== -->

    <!-- Offcanvas Search -->
    <div class="offcanvas offcanvas-top offcanvas-navbar-search bg-light" tabindex="-1" id="offcanvasNavbarSearch">
        <div class="offcanvas-body">
            <div class="container">
                <div class="w-lg-75 mx-lg-auto">
                    <div class="d-flex justify-content-end mb-3">
                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>

                    <div class="mb-7">
                        <!-- Form -->
                        <form action="search-results.php" method="get">
                            <!-- Input Card -->
                            <div class="input-card">
                                <div class="input-card-form">
                                    <input type="text" class="form-control form-control-lg" name="q" placeholder="What do you want to learn?" aria-label="What do you want to learn?">
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg">Search</button>
                            </div>
                            <!-- End Input Card -->
                        </form>
                        <!-- End Form -->
                    </div>

                    <div class="d-none d-md-block">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <h5>Popular Searches</h5>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <!-- List -->
                                        <ul class="list-pointer list-pointer-primary mb-0">
                                            <li class="list-pointer-item">
                                                <a class="link-sm link-secondary" href="search-results.php?q=python">Python Programming</a>
                                            </li>
                                            <li class="list-pointer-item">
                                                <a class="link-sm link-secondary" href="search-results.php?q=javascript">JavaScript</a>
                                            </li>
                                            <li class="list-pointer-item">
                                                <a class="link-sm link-secondary" href="search-results.php?q=web+development">Web Development</a>
                                            </li>
                                            <li class="list-pointer-item">
                                                <a class="link-sm link-secondary" href="search-results.php?q=data+science">Data Science</a>
                                            </li>
                                        </ul>
                                        <!-- End List -->
                                    </div>
                                    <!-- End Col -->

                                    <div class="col-6">
                                        <!-- List -->
                                        <ul class="list-pointer list-pointer-primary mb-0">
                                            <li class="list-pointer-item">
                                                <a class="link-sm link-secondary" href="search-results.php?q=digital+marketing">Digital Marketing</a>
                                            </li>
                                            <li class="list-pointer-item">
                                                <a class="link-sm link-secondary" href="search-results.php?q=graphic+design">Graphic Design</a>
                                            </li>
                                            <li class="list-pointer-item">
                                                <a class="link-sm link-secondary" href="search-results.php?q=machine+learning">Machine Learning</a>
                                            </li>
                                            <li class="list-pointer-item">
                                                <a class="link-sm link-secondary" href="search-results.php?q=excel">Excel Skills</a>
                                            </li>
                                        </ul>
                                        <!-- End List -->
                                    </div>
                                    <!-- End Col -->
                                </div>
                                <!-- End Row -->
                            </div>
                            <!-- End Col -->

                            <div class="col-sm-6">
                                <!-- Card -->
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <img class="img-fluid" src="../assets/img/mockups/img1.png" alt="Image Description">
                                            </div>
                                            <!-- End Col -->

                                            <div class="col-6">
                                                <div class="mb-4">
                                                    <h5>New Course</h5>
                                                    <p>Master advanced web development techniques with our newest course.</p>
                                                </div>
                                                <a class="btn btn-outline-primary btn-xs btn-transition" href="course-details.php?id=123">Learn more <i class="bi-chevron-right small ms-1"></i></a>
                                            </div>
                                            <!-- End Col -->
                                        </div>
                                        <!-- End Row -->
                                    </div>
                                </div>
                                <!-- End Card -->
                            </div>
                            <!-- End Col -->
                        </div>
                        <!-- End Row -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Offcanvas Search -->

    <!-- Notifications Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNotifications">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title">
                Notifications
                <?php if ($unread_count > 0): ?>
                    <span class="badge bg-primary rounded-pill ms-2"><?php echo $unread_count; ?> new</span>
                <?php endif; ?>
            </h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body p-0">
            <?php if (count($notifications) > 0): ?>
                <!-- Filters and actions for notifications -->
                <div class="d-flex justify-content-between border-bottom p-3">
                    <!-- Nav tabs -->
                    <ul class="nav nav-pills nav-segment" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="all-notifications-tab" data-bs-toggle="pill" data-bs-target="#all-notifications" role="tab" aria-controls="all-notifications" aria-selected="true">All</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="unread-notifications-tab" data-bs-toggle="pill" data-bs-target="#unread-notifications" role="tab" aria-controls="unread-notifications" aria-selected="false">Unread</a>
                        </li>
                    </ul>

                    <!-- Mark all read button -->
                    <button type="button" class="btn btn-sm btn-ghost-secondary mark-all-read-btn">
                        <i class="bi-check2-all me-1"></i> Mark all read
                    </button>
                </div>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- All notifications tab -->
                    <div class="tab-pane fade show active" id="all-notifications" role="tabpanel" aria-labelledby="all-notifications-tab">
                        <div class="notification-list">
                            <?php foreach ($notifications as $index => $notification):
                                $timeAgo = getTimeAgo($notification['created_at']);
                                $isUnread = $notification['is_read'] === 0;
                                $bgColor = '';

                                // Define icon and background color based on notification type from DB
                                switch (strtolower($notification['type'])) {
                                    case 'course':
                                        $icon = 'bi-mortarboard-fill';
                                        $bgColor = 'bg-success';
                                        break;
                                    case 'assignment':
                                        $icon = 'bi-clipboard-check-fill';
                                        $bgColor = 'bg-info';
                                        break;
                                    case 'deadline':
                                        $icon = 'bi-calendar-event-fill';
                                        $bgColor = 'bg-warning';
                                        break;
                                    case 'live':
                                        $icon = 'bi-camera-video-fill';
                                        $bgColor = 'bg-danger';
                                        break;
                                    case 'update':
                                        $icon = 'bi-arrow-clockwise';
                                        $bgColor = 'bg-primary';
                                        break;
                                    default:
                                        $icon = 'bi-bell-fill';
                                        $bgColor = 'bg-secondary';
                                }
                            ?>
                                <!-- Notification item -->
                                <div class="notification-item d-flex p-3 border-bottom <?php echo $isUnread ? 'unread' : ''; ?>"
                                    data-notification-id="<?php echo $notification['notification_id']; ?>"
                                    data-is-read="<?php echo $notification['is_read']; ?>">
                                    <div class="flex-shrink-0">
                                        <div class="notification-avatar <?php echo $bgColor; ?> rounded-circle text-white">
                                            <i class="<?php echo $icon; ?>"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                            <span class="notification-time text-muted"><?php echo $timeAgo; ?></span>
                                        </div>
                                        <p class="text-body small mb-0"><?php echo htmlspecialchars($notification['message']); ?></p>

                                        <!-- Action buttons that appear on hover -->
                                        <div class="notification-actions d-flex gap-2 mt-2">
                                            <?php if ($isUnread): ?>
                                                <button class="btn btn-sm btn-outline-secondary mark-read-btn"
                                                    data-notification-id="<?php echo $notification['notification_id']; ?>">
                                                    <i class="bi-check-lg"></i> Mark as read
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-ghost-secondary delete-notification-btn"
                                                data-notification-id="<?php echo $notification['notification_id']; ?>">
                                                <i class="bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Unread notifications tab -->
                    <div class="tab-pane fade" id="unread-notifications" role="tabpanel" aria-labelledby="unread-notifications-tab">
                        <div class="notification-list">
                            <?php
                            $hasUnread = false;
                            foreach ($notifications as $index => $notification):
                                if ($notification['is_read'] === 0):
                                    $hasUnread = true;
                                    $timeAgo = getTimeAgo($notification['created_at']);
                                    $bgColor = '';

                                    // Define icon and background color based on notification type from DB
                                    switch (strtolower($notification['type'])) {
                                        case 'course':
                                            $icon = 'bi-mortarboard-fill';
                                            $bgColor = 'bg-success';
                                            break;
                                        case 'assignment':
                                            $icon = 'bi-clipboard-check-fill';
                                            $bgColor = 'bg-info';
                                            break;
                                        case 'deadline':
                                            $icon = 'bi-calendar-event-fill';
                                            $bgColor = 'bg-warning';
                                            break;
                                        case 'live':
                                            $icon = 'bi-camera-video-fill';
                                            $bgColor = 'bg-danger';
                                            break;
                                        case 'update':
                                            $icon = 'bi-arrow-clockwise';
                                            $bgColor = 'bg-primary';
                                            break;
                                        default:
                                            $icon = 'bi-bell-fill';
                                            $bgColor = 'bg-secondary';
                                    }
                            ?>
                                    <!-- Notification item -->
                                    <div class="notification-item d-flex p-3 border-bottom unread"
                                        data-notification-id="<?php echo $notification['notification_id']; ?>"
                                        data-is-read="0">
                                        <div class="flex-shrink-0">
                                            <div class="notification-avatar <?php echo $bgColor; ?> rounded-circle text-white">
                                                <i class="<?php echo $icon; ?>"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                                <span class="notification-time text-muted"><?php echo $timeAgo; ?></span>
                                            </div>
                                            <p class="text-body small mb-0"><?php echo htmlspecialchars($notification['message']); ?></p>

                                            <!-- Action buttons that appear on hover -->
                                            <div class="notification-actions d-flex gap-2 mt-2">
                                                <button class="btn btn-sm btn-outline-secondary mark-read-btn"
                                                    data-notification-id="<?php echo $notification['notification_id']; ?>">
                                                    <i class="bi-check-lg"></i> Mark as read
                                                </button>
                                                <button class="btn btn-sm btn-ghost-secondary delete-notification-btn"
                                                    data-notification-id="<?php echo $notification['notification_id']; ?>">
                                                    <i class="bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                endif;
                            endforeach;

                            if (!$hasUnread):
                                ?>
                                <!-- No unread notifications message -->
                                <div id="no-unread-message" class="text-center p-4">
                                    <div class="d-inline-block p-2 rounded-circle bg-light mb-2">
                                        <i class="bi-check2-all fs-4 text-success"></i>
                                    </div>
                                    <h6>No unread notifications</h6>
                                    <p class="text-muted small">You're all caught up!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Footer with actions -->
                <div class="text-center p-3 border-top">
                    <a href="account-notifications.php" class="btn btn-sm btn-primary w-100">
                        View all notifications
                        <i class="bi-arrow-right ms-1"></i>
                    </a>
                </div>
            <?php else: ?>
                <!-- Empty state (no notifications) -->
                <div class="notification-empty-state text-center">
                    <div class="d-inline-block p-3 rounded-circle bg-light mb-3">
                        <i class="bi-bell-slash fs-2 text-secondary"></i>
                    </div>
                    <h5>No notifications yet</h5>
                    <p class="text-muted">You're all caught up! We'll notify you when something important happens.</p>
                    <button class="btn btn-sm btn-outline-primary mt-2" data-bs-dismiss="offcanvas">
                        <i class="bi-arrow-left me-1"></i> Return to dashboard
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- End Notifications Offcanvas -->

    <!-- BONUS: Floating Action Button for quick access to common actions -->
    <div id="floatingActionButton" class="position-fixed bottom-0 start-0 mb-5 ms-4" style="z-index: 1030;">
        <button class="btn btn-primary btn-lg rounded-circle shadow d-flex align-items-center justify-content-center"
            style="width: 60px; height: 60px; transition: all 0.3s ease;"
            data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi-plus fs-4"></i>
        </button>

        <ul class="dropdown-menu dropdown-menu-start p-2 mt-2 fade-in-up" style="min-width: 220px;">
            <li class="mb-2">
                <a class="dropdown-item rounded-3 d-flex align-items-center" href="courses.php">
                    <span class="dropdown-item-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 32px; height: 32px;">
                        <i class="bi-search"></i>
                    </span>
                    <span class="ms-2">Find New Courses</span>
                </a>
            </li>
            <li class="mb-2">
                <a class="dropdown-item rounded-3 d-flex align-items-center" href="notes.php">
                    <span class="dropdown-item-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 32px; height: 32px;">
                        <i class="bi-journal-text"></i>
                    </span>
                    <span class="ms-2">My Study Notes</span>
                </a>
            </li>
            <li class="mb-2">
                <a class="dropdown-item rounded-3 d-flex align-items-center" href="enrolled-courses.php">
                    <span class="dropdown-item-icon bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 32px; height: 32px;">
                        <i class="bi-collection-play"></i>
                    </span>
                    <span class="ms-2">Continue Learning</span>
                </a>
            </li>
            <li>
                <a class="dropdown-item rounded-3 d-flex align-items-center" href="calendar.php">
                    <span class="dropdown-item-icon bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 32px; height: 32px;">
                        <i class="bi-calendar-event"></i>
                    </span>
                    <span class="ms-2">My Schedule</span>
                </a>
            </li>
        </ul>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mark as read functionality
            document.querySelectorAll('.mark-read-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const notificationId = this.dataset.notificationId;
                    const notificationItem = this.closest('.notification-item');

                    fetch('../backend/mark_notification_read.php?id=' + notificationId, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                notificationItem.classList.remove('unread');
                                notificationItem.dataset.isRead = "1";
                                this.remove(); // Remove the mark as read button
                                updateUnreadCount();
                                showToast('Notification marked as read', 'success');
                            } else {
                                showToast('Error marking notification as read', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showToast('Network error - please try again', 'error');
                        });
                });
            });

            // Delete (hide) notification functionality
            document.querySelectorAll('.delete-notification-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const notificationId = this.dataset.notificationId;
                    const notificationItem = this.closest('.notification-item');

                    fetch('../backend/hide_notification.php?id=' + notificationId, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Animate removal
                                notificationItem.style.opacity = '0';
                                setTimeout(() => {
                                    notificationItem.style.height = notificationItem.offsetHeight + 'px';
                                    notificationItem.style.height = '0';
                                    notificationItem.style.padding = '0';
                                    notificationItem.style.margin = '0';
                                    notificationItem.style.overflow = 'hidden';
                                    notificationItem.style.border = 'none';

                                    setTimeout(() => {
                                        notificationItem.remove();

                                        // Update unread count if this was an unread notification
                                        if (notificationItem.classList.contains('unread')) {
                                            updateUnreadCount();
                                        }

                                        // Check if all notifications are gone
                                        if (document.querySelectorAll('.notification-item').length === 0) {
                                            showEmptyState();
                                        }

                                        // Check if no unread items are visible when in unread filter
                                        if (document.getElementById('showUnreadNotifs').classList.contains('active')) {
                                            const visibleUnread = document.querySelectorAll('.notification-item.unread');
                                            if (visibleUnread.length === 0) {
                                                showNoUnreadMessage();
                                            }
                                        }

                                        showToast('Notification removed', 'success');
                                    }, 300);
                                }, 200);
                            } else {
                                showToast('Error hiding notification', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showToast('Network error - please try again', 'error');
                        });
                });
            });

            // Mark all as read functionality
            const markAllReadBtn = document.querySelector('.mark-all-read-btn');
            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Only proceed if there are unread notifications
                    const unreadItems = document.querySelectorAll('.notification-item.unread');
                    if (unreadItems.length === 0) {
                        showToast('No unread notifications to mark', 'info');
                        return;
                    }

                    fetch('../backend/mark_all_notifications_read.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Update all notifications in the UI
                                unreadItems.forEach(item => {
                                    item.classList.remove('unread');
                                    item.dataset.isRead = "1";
                                    const markReadBtn = item.querySelector('.mark-read-btn');
                                    if (markReadBtn) markReadBtn.remove();
                                });

                                // If in unread filter view, these items should be hidden
                                if (document.getElementById('showUnreadNotifs').classList.contains('active')) {
                                    unreadItems.forEach(item => {
                                        item.style.display = 'none';
                                    });
                                    showNoUnreadMessage();
                                }

                                // Update unread count
                                updateUnreadCount();
                                showToast('All notifications marked as read', 'success');
                            } else {
                                showToast('Error marking all as read', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showToast('Network error - please try again', 'error');
                        });
                });
            } else {
                console.error('Mark all read button not found');
            }

            // Filter buttons functionality
            const showAllBtn = document.getElementById('all-notifications-tab');
            const showUnreadBtn = document.getElementById('unread-notifications-tab');

            if (showAllBtn && showUnreadBtn) {
                showAllBtn.addEventListener('click', function() {
                    this.classList.add('active');
                    showUnreadBtn.classList.remove('active');

                    // Show all notifications and hide any no-unread message
                    document.querySelectorAll('.notification-item').forEach(item => {
                        item.style.display = 'flex'; // Use flex to maintain layout
                    });

                    hideNoUnreadMessage();

                    console.log('Showing all notifications');
                });

                showUnreadBtn.addEventListener('click', function() {
                    this.classList.add('active');
                    showAllBtn.classList.remove('active');

                    // Count unread items for later check
                    let unreadCount = 0;

                    // Filter to only show unread items
                    document.querySelectorAll('.notification-item').forEach(item => {
                        if (item.classList.contains('unread')) {
                            item.style.display = 'flex'; // Use flex to maintain layout
                            unreadCount++;
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    // Show a message if no unread notifications
                    if (unreadCount === 0) {
                        showNoUnreadMessage();
                    } else {
                        hideNoUnreadMessage();
                    }

                    console.log('Showing only unread notifications:', unreadCount);
                });
            } else {
                console.error('Filter buttons not found', {
                    showAllBtn,
                    showUnreadBtn
                });
            }

            // Helper function to update the unread badge count
            function updateUnreadCount() {
                fetch('../backend/get_unread_count.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const count = data.unread_count;

                            // Update the count in the navbar badge
                            const badge = document.querySelector('.notification-badge');
                            if (badge) {
                                if (count > 0) {
                                    badge.innerHTML = count + '<span class="visually-hidden">unread notifications</span>';
                                    badge.style.display = 'inline-flex';
                                } else {
                                    badge.style.display = 'none';
                                }
                            }

                            // Update the count in the offcanvas header
                            const headerBadge = document.querySelector('.offcanvas-title .badge');
                            if (headerBadge) {
                                if (count > 0) {
                                    headerBadge.innerHTML = count + ' new';
                                    headerBadge.style.display = 'inline-flex';
                                } else {
                                    headerBadge.style.display = 'none';
                                }
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching unread count:', error);
                    });
            }

            // Helper function to show empty state when all notifications are gone
            function showEmptyState() {
                const notificationList = document.querySelector('.notification-list');
                const filterBar = document.querySelector('.offcanvas-body .d-flex.justify-content-between');
                const footerActions = document.querySelector('.text-center.p-3.border-top');

                if (notificationList) {
                    notificationList.innerHTML = '';

                    if (filterBar) filterBar.style.display = 'none';
                    if (footerActions) footerActions.style.display = 'none';

                    const emptyState = document.createElement('div');
                    emptyState.className = 'notification-empty-state text-center';
                    emptyState.innerHTML = `
                <div class="d-inline-block p-3 rounded-circle bg-light mb-3">
                    <i class="bi-bell-slash fs-2 text-secondary"></i>
                </div>
                <h5>All caught up!</h5>
                <p class="text-muted">You have no notifications at the moment.</p>
                <button class="btn btn-sm btn-outline-primary mt-2" data-bs-dismiss="offcanvas">
                    <i class="bi-arrow-left me-1"></i> Return to dashboard
                </button>
            `;

                    notificationList.appendChild(emptyState);
                }
            }

            // Function to show message when there are no unread notifications
            function showNoUnreadMessage() {
                // Check if the message already exists
                if (!document.getElementById('no-unread-message')) {
                    const notificationList = document.querySelector('.notification-list');

                    if (notificationList) {
                        const noUnreadMessage = document.createElement('div');
                        noUnreadMessage.id = 'no-unread-message';
                        noUnreadMessage.className = 'text-center p-4';
                        noUnreadMessage.innerHTML = `
                    <div class="d-inline-block p-2 rounded-circle bg-light mb-2">
                        <i class="bi-check2-all fs-4 text-success"></i>
                    </div>
                    <h6>No unread notifications</h6>
                    <p class="text-muted small">You're all caught up!</p>
                `;

                        notificationList.appendChild(noUnreadMessage);
                        console.log('Added no unread message');
                    }
                }
            }

            // Function to hide no unread message
            function hideNoUnreadMessage() {
                const noUnreadMessage = document.getElementById('no-unread-message');
                if (noUnreadMessage) {
                    noUnreadMessage.remove();
                    console.log('Removed no unread message');
                }
            }

            // Enhanced toast message function with proper styling and icon
            function showToast(message, type = 'success') {
                // Get the toast element
                const toast = document.getElementById('liveToast');
                if (!toast) return;

                // Update toast content
                const toastBody = toast.querySelector('.toast-body');
                if (toastBody) {
                    toastBody.textContent = message;
                }

                // Update toast type and styles
                toast.classList.remove('bg-soft-success', 'bg-soft-danger', 'bg-soft-warning', 'bg-soft-info');

                // Update header elements
                const toastTitle = toast.querySelector('.toast-header h5');
                const avatar = toast.querySelector('.toast-header .avatar');

                switch (type) {
                    case 'success':
                        toast.classList.add('bg-soft-success');
                        if (toastTitle) toastTitle.textContent = 'Success';
                        if (avatar) avatar.style.borderColor = '#198754';
                        break;
                    case 'error':
                        toast.classList.add('bg-soft-danger');
                        if (toastTitle) toastTitle.textContent = 'Error';
                        if (avatar) avatar.style.borderColor = '#dc3545';
                        break;
                    case 'warning':
                        toast.classList.add('bg-soft-warning');
                        if (toastTitle) toastTitle.textContent = 'Warning';
                        if (avatar) avatar.style.borderColor = '#ffc107';
                        break;
                    case 'info':
                        toast.classList.add('bg-soft-info');
                        if (toastTitle) toastTitle.textContent = 'Information';
                        if (avatar) avatar.style.borderColor = '#0dcaf0';
                        break;
                }

                // Add appropriate icon based on type (using Bootstrap icons)
                let iconClass = '';
                switch (type) {
                    case 'success':
                        iconClass = 'bi-check-circle-fill';
                        break;
                    case 'error':
                        iconClass = 'bi-exclamation-circle-fill';
                        break;
                    case 'warning':
                        iconClass = 'bi-exclamation-triangle-fill';
                        break;
                    case 'info':
                        iconClass = 'bi-info-circle-fill';
                        break;
                }

                // Add icon before the message
                if (toastBody) {
                    toastBody.innerHTML = `<i class="${iconClass} me-2"></i> ${message}`;
                }

                // Update timestamp
                const timeElement = toast.querySelector('small');
                if (timeElement) {
                    timeElement.textContent = 'Just now';
                }

                // Show the toast
                const bsToast = new bootstrap.Toast(toast);
                bsToast.show();
            }

            // Initialize - add debug logging
            console.log('Notification script loaded');
            console.log('Mark all button found:', !!document.querySelector('.mark-all-read-btn'));
            console.log('Filter buttons found:', !!document.getElementById('showAllNotifs'), !!document.getElementById('showUnreadNotifs'));
        });
    </script>