<?php
// admin-header.php
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

  <title>Admin | Learnix - Empowering Education</title>
<meta name="description" content="Admin panel for managing users, courses, instructors, and platform settings on Learnix." />
<meta name="author" content="Learnix Team" />

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
          .dark-mode-preload .navbar-vertical,
          .dark-mode-preload .bg-white {
            background-color: #1e2a36 !important;
            color: #e9ecef !important;
          }
          .dark-mode-preload .table,
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

  <!-- Dark Reader Library -->
  <script src="https://cdn.jsdelivr.net/npm/darkreader@4.9.58/darkreader.min.js"></script>

  <!-- Dark Mode Configuration and Toggle Script -->
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
              /* Fix for tables */
              .table-thead-bordered th {
                  border-color: #495057 !important;
              }
              
              /* Fix for form elements */
              .form-control, .form-select {
                  background-color: #2c3e50 !important;
                  color: #eee !important;
              }
              
              /* Fix for modals */
              .modal-content {
                  background-color: #1e2a36 !important;
              }
              
              /* Fix for cards */
              .card {
                  background-color: #1e2a36 !important;
              }
              
              /* Fix for navbar */
              .navbar-vertical.navbar-light {
                  background-color: #1e2a36 !important;
              }
          `,
          
          // Fix specific elements to always be light
          ignoreInlineStyle: [
              /* elements to ignore inline styles for */
          ],
          
          // Custom fixes for specific elements
          fixes: {
              invert: [
                  /* elements to invert */
                  '.navbar-brand-logo', 
                  '.bi-box-arrow-right'
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
      // Create toggle button
      const headerNav = document.querySelector('.navbar-nav');
      if (headerNav) {
          const toggleBtn = document.createElement('li');
          toggleBtn.className = 'nav-item me-2';
          toggleBtn.innerHTML = `
              <button id="darkModeToggle" class="btn btn-outline-primary btn-sm" title="Toggle Dark Mode">
                  <i class="bi-moon-stars"></i>
                  <span class="d-none d-md-inline-block ms-1">Dark Mode</span>
              </button>
          `;
          headerNav.prepend(toggleBtn);
          
          // Apply saved preference (button state)
          const darkModeEnabled = localStorage.getItem('darkMode') === 'true';
          updateToggleButton(darkModeEnabled);
          
          // Add toggle listener
          document.getElementById('darkModeToggle').addEventListener('click', toggleDarkMode);
          
          // Remove preload class if it exists
          document.documentElement.classList.remove('dark-mode-preload');
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
                
            `;
            btn.classList.add('active');
        } else {
            btn.innerHTML = `
                <i class="bi-moon-stars"></i>
                
            `;
            btn.classList.remove('active');
        }
    }
  </script>
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
                <!-- Dark mode toggle will be added here by JavaScript -->
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