<?php
// department/header.php
require '../backend/session_start.php'; // Ensure session is started
require '../backend/config.php'; // Ensure session is started

// Check if the user is signed in and is a department staff member
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || !isset($_SESSION['department_id']) || !isset($_SESSION['role']) || !in_array($_SESSION['role'], ['department_head', 'department_secretary'])) {
  // Log unauthorized access attempt for security auditing
  error_log("Unauthorized access attempt to protected page: " . $_SERVER['REQUEST_URI'] . " | IP: " . $_SERVER['REMOTE_ADDR']);

  // Redirect unauthorized users to the sign-in page
  header('Location: signin.php');
  exit;
}
$pageTitle = $_SESSION['department_name']. ' - Department';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required Meta Tags Always Come First -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <title><?php echo $pageTitle; ?> | Learnix </title>
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
          .dark-mode-preload .offcanvas {
            background-color: #1e2a36 !important;
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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

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

  <!-- Custom CSS for Notifications -->
  <style>
    .notification-bell {
      position: relative;
    }
    .notification-badge {
      position: absolute;
      top: -5px;
      right: -5px;
      width: 10px;
      height: 10px;
      background-color: #dc3545;
      border-radius: 50%;
      display: none;
    }
    .notification-panel {
      max-height: 80vh;
      overflow-y: auto;
    }
    .notification-item {
      padding: 10px;
      border-bottom: 1px solid #e9ecef;
      transition: background-color 0.3s;
    }
    .notification-item.unread {
      background-color: #e9ecef;
      font-weight: 600;
      border-left: 3px solid #007bff;
    }
    .notification-item:hover {
      background-color: #f1f3f5;
    }
    .notification-item h6 {
      font-size: 0.95rem; /* Slightly smaller title */
    }
    .notification-item p {
      font-size: 0.85rem; /* Smaller message */
    }
    .notification-item small {
      font-size: 0.75rem; /* Smaller timestamp */
    }
    .notification-close {
      cursor: pointer;
      color: #6c757d;
    }
    .notification-close:hover {
      color: #dc3545;
    }
    .notification-icon {
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      margin-right: 10px;
    }
    .notification-date-header {
      font-size: 0.85rem;
      color: #6c757d;
      padding: 10px;
      font-weight: 600;
    }
    .clear-all-btn {
      font-size: 0.85rem;
      color: #007bff;
      text-decoration: none;
    }
    .clear-all-btn:hover {
      text-decoration: underline;
    }
    .offcanvas-header {
      border-bottom: 1px solid #e9ecef;
    }
    .offcanvas-body {
      padding: 0;
    }
    .offcanvas-footer {
      border-top: 1px solid #e9ecef;
      padding: 10px;
      text-align: center;
    }
    .view-all-btn {
      font-size: 0.85rem;
    }
    /* Dark mode adjustments */
    .dark-mode-preload .notification-item,
    .dark-mode-preload .notification-panel,
    .dark-mode-preload .offcanvas-header,
    .dark-mode-preload .offcanvas-footer {
      background-color: #253545 !important;
      color: #e9ecef !important;
    }
    .dark-mode-preload .notification-item.unread {
      background-color: #2c3e50 !important;
      border-left: 3px solid #4dabf7;
    }
    .dark-mode-preload .notification-item:hover {
      background-color: #34495e !important;
    }
    .dark-mode-preload .notification-date-header,
    .dark-mode-preload .notification-close {
      color: #adb5bd !important;
    }
    .dark-mode-preload .clear-all-btn,
    .dark-mode-preload .view-all-btn {
      color: #4dabf7 !important;
    }
    .dark-mode-preload .notification-icon.alert-soft-primary,
    .dark-mode-preload .notification-icon.alert-soft-success,
    .dark-mode-preload .notification-icon.alert-soft-warning,
    .dark-mode-preload .notification-icon.alert-soft-danger {
      color: #e9ecef !important;
    }
  </style>

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
          
          /* Fix notification panel */
          .notification-panel {
            background-color: #1e2a36 !important;
          }
          
          .notification-item {
            background-color: #253545 !important;
          }
          
          .notification-item:hover {
            background-color: #2c3e50 !important;
          }
          
          /* Fix toast notifications */
          .toast {
            background-color: #253545 !important;
            color: #e9ecef !important;
          }
          
          .toast .toast-header {
            background-color: #1e2a36 !important;
            color: #e9ecef !important;
          }
        `,

        // Fix specific elements to always be light
        ignoreInlineStyle: [],

        // Custom fixes for specific elements
        fixes: {
          invert: [
            '.navbar-brand-logo',
            '.bi-box-arrow-right'
          ],
          css: '',
          ignoreImageAnalysis: ['*'],
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
          <button id="darkModeToggle" class="btn btn-soft-primary btn-sm" title="Toggle Dark Mode">
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
            <a class="navbar-brand" href="index.php" aria-label="Space">
              <img class="navbar-brand-logo" src="../assets/svg/logos/logo.svg" alt="Logo">
            </a>
          </div>
          <!-- End Default Logo -->

          <div class="col-md px-lg-0">
            <div class="d-flex justify-content-between align-items-center px-lg-5 px-xl-10">
              <!-- Navbar -->
              <ul class="navbar-nav p-0">
                <!-- Notification Bell -->
                <li class="nav-item me-3">
                  <button id="notificationBell" class="btn btn-ghost-secondary notification-bell" 
                    data-bs-toggle="offcanvas" data-bs-target="#offcanvasNotifications" aria-controls="offcanvasNotifications" 
                    title="Notifications">
                    <i class="bi-bell fs-4"></i>
                    <span class="notification-badge"></span>
                  </button>
                </li>
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

  <!-- Offcanvas for Notifications -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNotifications" aria-labelledby="offcanvasNotificationsLabel">
    <div class="offcanvas-header">
      <h5 id="offcanvasNotificationsLabel">Notifications</h5>
      <div>
        <a href="javascript:void(0);" class="clear-all-btn me-3" id="clear-all-notifications">Clear All</a>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
    </div>
    <div class="offcanvas-body notification-panel">
      <div id="notification-list"></div>
      <div class="text-center" id="loading-spinner" style="display: none;">
        <i class="bi-arrow-clockwise fs-4 animate-spin"></i>
      </div>
    </div>
    <div class="offcanvas-footer">
      <a href="notifications.php" class="btn btn-outline-primary btn-sm view-all-btn">
        <i class="bi-list me-1"></i> View All
      </a>
    </div>
  </div>

  <!-- Notification JavaScript -->
  <script>
    document.addEventListener("DOMContentLoaded", async function() {
      const notificationList = document.getElementById("notification-list");
      const notificationBell = document.getElementById("notificationBell");
      const notificationBadge = notificationBell.querySelector(".notification-badge");
      const clearAllLink = document.getElementById("clear-all-notifications");
      const loadingSpinner = document.getElementById("loading-spinner");
      const offcanvasNotifications = document.getElementById("offcanvasNotifications");

      // Notification type to icon mapping
      const typeIcons = {
        course: "bi-book",
        student: "bi-person",
        system: "bi-gear",
        comment: "bi-chat-left-text",
        new_user: "bi-person-plus",
        profile: "bi-person-circle"
      };

      // Available alert-soft-* classes
      const alertClasses = [
        "alert-soft-primary",
        "alert-soft-success",
        "alert-soft-warning",
        "alert-soft-danger"
      ];

      // Function to get random alert class
      function getRandomAlertClass() {
        return alertClasses[Math.floor(Math.random() * alertClasses.length)];
      }

      // Function to fetch and display notifications
      async function fetchNotifications() {
        try {
          loadingSpinner.style.display = "block";
          const response = await fetch("../backend/get_notifications.php");
          const data = await response.json();

          if (data.success) {
            // Update notification badge
            notificationBadge.style.display = data.unread_count > 0 ? "block" : "none";

            // Clear existing notifications
            notificationList.innerHTML = "";

            // Group notifications by date
            const groupedNotifications = groupNotificationsByDate(data.notifications);

            // Render notifications
            for (const [date, notifications] of Object.entries(groupedNotifications)) {
              const dateHeader = document.createElement("div");
              dateHeader.className = "notification-date-header";
              dateHeader.textContent = date;
              notificationList.appendChild(dateHeader);

              notifications.forEach(notification => {
                const iconClass = typeIcons[notification.type] || "bi-bell";
                const alertClass = getRandomAlertClass();
                const notificationItem = document.createElement("div");
                notificationItem.className = `notification-item ${notification.is_read ? "read" : "unread"}`;
                notificationItem.innerHTML = `
                  <div class="d-flex align-items-start">
                    <span class="notification-icon ${alertClass}">
                      <i class="${iconClass} fs-5"></i>
                    </span>
                    <div class="flex-grow-1">
                      <h6 class="mb-1">${notification.title}</h6>
                      <p class="mb-1 text-muted">${notification.message}</p>
                      <small class="text-muted">${formatTime(notification.created_at)}</small>
                    </div>
                    <span class="notification-close bi-x fs-5" data-id="${notification.notification_id}"></span>
                  </div>
                `;
                notificationList.appendChild(notificationItem);

                // Add click event to mark as read
                if (!notification.is_read) {
                  notificationItem.addEventListener("click", async () => {
                    await markNotificationAsRead(notification.notification_id);
                    fetchNotifications();
                  });
                }
              });
            }

            // Add click event for close (hide) buttons
            document.querySelectorAll(".notification-close").forEach(button => {
              button.addEventListener("click", async (e) => {
                e.stopPropagation(); // Prevent triggering parent click
                const notificationId = button.getAttribute("data-id");
                await hideNotification(notificationId);
                fetchNotifications();
              });
            });
          } else {
            notificationList.innerHTML = '<div class="text-center text-muted p-3">No notifications found</div>';
            notificationBadge.style.display = "none";
          }
        } catch (error) {
          console.error("Error fetching notifications:", error);
          notificationList.innerHTML = '<div class="text-center text-muted p-3">Error loading notifications</div>';
          notificationBadge.style.display = "none";
        } finally {
          loadingSpinner.style.display = "none";
        }
      }

      // Function to group notifications by date
      function groupNotificationsByDate(notifications) {
        const today = new Date().toDateString();
        const yesterday = new Date(Date.now() - 86400000).toDateString();
        const grouped = {};

        notifications.forEach(notification => {
          const date = new Date(notification.created_at).toDateString();
          let label;
          if (date === today) {
            label = "Today";
          } else if (date === yesterday) {
            label = "Yesterday";
          } else {
            label = new Date(notification.created_at).toLocaleDateString();
          }
          if (!grouped[label]) {
            grouped[label] = [];
          }
          grouped[label].push(notification);
        });

        return grouped;
      }

      // Function to format time
      function formatTime(createdAt) {
        const now = new Date();
        const created = new Date(createdAt);
        const diff = Math.floor((now - created) / 1000);

        if (diff < 60) return `${diff} seconds ago`;
        if (diff < 3600) return `${Math.floor(diff / 60)} minutes ago`;
        if (diff < 86400) return `${Math.floor(diff / 3600)} hours ago`;
        return created.toLocaleDateString();
      }

      // Function to mark a notification as read
      async function markNotificationAsRead(notificationId) {
        try {
          const response = await fetch(`../backend/mark_notification_read.php?id=${notificationId}`);
          const data = await response.json();
          if (!data.success) {
            console.error("Error marking notification as read:", data.error);
          }
        } catch (error) {
          console.error("Error marking notification:", error);
        }
      }

      // Function to hide a notification
      async function hideNotification(notificationId) {
        try {
          const response = await fetch(`../backend/hide_notification.php?id=${notificationId}`);
          const data = await response.json();
          if (!data.success) {
            console.error("Error hiding notification:", data.error);
          }
        } catch (error) {
          console.error("Error hiding notification:", error);
        }
      }

      // Function to mark all notifications as read
      async function markAllNotificationsRead() {
        try {
          const response = await fetch("../backend/mark_all_notifications_read.php");
          const data = await response.json();
          if (data.success) {
            fetchNotifications();
          } else {
            console.error("Error marking all notifications as read:", data.error);
          }
        } catch (error) {
          console.error("Error marking all notifications:", error);
        }
      }

      // Add event listener for Clear All
      clearAllLink.addEventListener("click", async () => {
        await markAllNotificationsRead();
      });

      // Fix backdrop issue
      offcanvasNotifications.addEventListener('hidden.bs.offcanvas', function () {
        const backdrop = document.querySelector('.offcanvas-backdrop');
        if (backdrop) {
          backdrop.remove();
        }
      });

      // Initial fetch
      fetchNotifications();

      // Poll every 5 seconds
      setInterval(fetchNotifications, 5000);
    });
  </script>