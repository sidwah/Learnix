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
} else {
    echo "User not found!";
    exit;
}

// Close statement
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en" dir="">

<head>
    <!-- Required Meta Tags Always Come First -->
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no" />

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
    <header id="header" class="navbar navbar-expand-lg navbar-end navbar-absolute-top navbar-light navbar-show-hide" data-hs-header-options='{
            "fixMoment": 1000,
            "fixEffect": "slide"
          }'>

        <div class="container">
            <nav class="js-mega-menu navbar-nav-wrap">
                <!-- Default Logo -->
                <a class="navbar-brand" href="index.php
" aria-label="Learnix">
                    <img class="navbar-brand-logo" src="../assets/svg/logos/logo.svg" alt="Logo">
                </a>
                <!-- End Default Logo -->

                <!-- Secondary Content -->
                <div class="navbar-nav-wrap-secondary-content">

                    <!-- Account -->
                    <div class="dropdown">
                        <a href="#" id="navbarShoppingCartDropdown active" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-dropdown-animation>
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
                            <a class="nav-link " href="index.php
">Home</a>
                        </li>

                        <!-- Courses -->
                        <li class="hs-has-sub-menu nav-item">
                            <a class="nav-link" href="courses.php"><i class="bi-journals me-2"></i> Courses</a>
                        </li>
                        <!-- End Courses -->


                        <!-- My Courses -->
                        <li class="hs-has-mega-menu nav-item" data-hs-mega-menu-item-options='{
                  "desktop": {
                    "maxWidth": "20rem"
                  }
                }'>
                            <a id="myCoursesMegaMenu" class="hs-mega-menu-invoker nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">My Courses</a>

                            <!-- Mega Menu -->
                            <div class="hs-mega-menu hs-position-right dropdown-menu" aria-labelledby="myCoursesMegaMenu" style="min-width: 32rem;">
                                <!-- Course -->
                                <a class="navbar-dropdown-menu-media-link" href="#">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <img class="avatar" src="../assets/svg/components/card-16.svg" alt="Image Description">
                                        </div>

                                        <div class="flex-grow-1 ms-3">
                                            <div class="mb-3">
                                                <span class="navbar-dropdown-menu-media-title">Java programming masterclass for software developers</span>
                                                <p class="navbar-dropdown-menu-media-desc">By Emily Milda</p>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="card-subtitle text-body">Completed</span>
                                                <small class="text-dark fw-semi-bold">25%</small>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar" style="width: 25%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                <!-- End Course -->

                                <!-- Course -->
                                <a class="navbar-dropdown-menu-media-link" href="#">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <img class="avatar" src="../assets/svg/components/card-5.svg" alt="Image Description">
                                        </div>

                                        <div class="flex-grow-1 ms-3">
                                            <div class="mb-3">
                                                <span class="navbar-dropdown-menu-media-title">The Ultimate MySQL Bootcamp: Go from SQL Beginner</span>
                                                <p class="navbar-dropdown-menu-media-desc">By Nataly Gaga and 2 others</p>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="card-subtitle text-body">Completed</span>
                                                <small class="text-dark fw-semi-bold">100%</small>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                <!-- End Course -->
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