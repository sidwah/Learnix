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

<!DOCTYPE html>
<html lang="en">


<head>
  <!-- Required Meta Tags Always Come First -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- Title -->
  <title>Admin | Learnix - Empowering Education</title>

  <!-- Favicon -->
  <link rel="shortcut icon" href="../favicon.ico">

  <!-- Learnix -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet">

  <!-- CSS Implementing Plugins -->
  <link rel="stylesheet" href="../assets/css/vendor.min.css">
  <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/font/bootstrap-icons.css">

  <!-- CSS Front Template -->
  <link rel="stylesheet" href="../assets/css/theme.minc619.css?v=1.0">
  <link rel="stylesheet" href="../assets/css/docs.css">
<!-- Latest Bootstrap 5 theme for Tom Select (CDN) -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
</head>

<body class="navbar-sidebar-aside-lg">
  <!-- ========== HEADER ========== -->
  <header id="header" class="navbar navbar-expand navbar-fixed navbar-end navbar-light navbar-sticky-lg-top bg-white">
    <div class="container-fluid">
      <nav class="navbar-nav-wrap">
        <div class="row flex-grow-1">
          <!-- Default Logo -->
          <div class="docs-navbar-sidebar-container d-flex align-items-center mb-2 mb-lg-0">
            <a class="navbar-brand" href="index.html" aria-label="Space">
              <img class="navbar-brand-logo" src="../assets/svg/logos/logo.svg" alt="Logo">
            </a>
          </div>
          <!-- End Default Logo -->

          <div class="col-md px-lg-0">
            <div class="d-flex justify-content-between align-items-center px-lg-5 px-xl-10">


              <!-- Navbar -->
              <ul class="navbar-nav p-0">

                <li class="nav-item">
                  <a class="btn btn-primary btn-sm" href="../backend/signout.php">
                    <i class="bi-box-arrow-right me-1"></i> Sign Out
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



  