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

// Add is_deleted column to user_notifications table if it doesn't exist
try {
    $conn = new mysqli('localhost', 'root', 'root', 'learnix_db');
    if (!$conn->connect_error) {
        // Check if is_deleted column exists
        $checkColQuery = "SHOW COLUMNS FROM user_notifications LIKE 'is_deleted'";
        $result = $conn->query($checkColQuery);
        
        if ($result->num_rows == 0) {
            // Add the column if it doesn't exist
            $addColQuery = "ALTER TABLE user_notifications ADD COLUMN is_deleted TINYINT(1) NOT NULL DEFAULT 0";
            $conn->query($addColQuery);
        }
        
        // $conn->close();
    }
} catch (Exception $e) {
    // Log the error but continue execution
    error_log("Error updating database schema: " . $e->getMessage());
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

  <!-- Notification System CSS -->
  <style>
    /* Notification Panel Styles */
    .notification-panel {
      position: fixed;
      top: 0;
      right: -400px;
      width: 380px;
      height: 100vh;
      background-color: #fff;
      box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
      transition: right 0.3s ease;
      z-index: 1050;
      overflow-y: auto;
      padding: 20px;
    }
    
    .notification-panel.show {
      right: 0;
    }
    
    .notification-panel-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
    }
    
    .notification-group {
      margin-bottom: 20px;
    }
    
    .notification-group-header {
      font-weight: 600;
      margin-bottom: 10px;
      color: #566a7f;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .notification-group-count {
      font-size: 12px;
      background-color: #f0f0f0;
      padding: 2px 8px;
      border-radius: 12px;
      color: #666;
    }
    
    .notification-item {
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 8px;
      background-color: #f8f9fa;
      transition: all 0.2s ease;
      cursor: pointer;
      position: relative;
    }
    
    .notification-item:hover {
      background-color: #f0f0f0;
    }
    
    .notification-item .close-btn {
      position: absolute;
      top: 8px;
      right: 8px;
      font-size: 14px;
      color: #999;
      cursor: pointer;
    }
    
    .notification-item .close-btn:hover {
      color: #333;
    }
    
    .notification-item-important {
      border-left: 4px solid #ff4d4f;
      background-color: #fff1f0;
    }
    
    .notification-item-system {
      border-left: 4px solid #3498db;
      background-color: #e6f7ff;
    }
    
    .notification-item-message {
      border-left: 4px solid #52c41a;
      background-color: #f6ffed;
    }
    
    .notification-item-other {
      border-left: 4px solid #faad14;
      background-color: #fffbe6;
    }
    
    .notification-time {
      font-size: 12px;
      color: #999;
      margin-top: 5px;
    }
    
    .notification-title {
      font-weight: 600;
      margin-bottom: 5px;
    }
    
    .notification-content {
      font-size: 13px;
      color: #666;
    }
    
    .notification-actions {
      display: flex;
      gap: 8px;
      margin-top: 10px;
    }
    
    /* Toast Notification Styles */
    .toast-container {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 1040;
    }
    
    .toast {
      min-width: 300px;
      max-width: 350px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      border-radius: 8px !important;
      border: none !important;
      margin-bottom: 10px;
      opacity: 0;
      transform: translateX(100%);
      transition: all 0.3s ease;
    }
    
    .toast.show {
      opacity: 1;
      transform: translateX(0);
    }
    
    .toast.hide-animation {
      opacity: 0;
      transform: translateX(100%);
    }
    
    .toast-important {
      border-left: 4px solid #ff4d4f !important;
    }
    
    .toast-system {
      border-left: 4px solid #3498db !important;
    }
    
    .toast-message {
      border-left: 4px solid #52c41a !important;
    }
    
    .toast-other {
      border-left: 4px solid #faad14 !important;
    }
    
    /* Bell Icon Badge Styles */
    .notification-bell {
      position: relative;
    }
    
    .notification-badge {
      position: absolute;
      top: -5px;
      right: -5px;
      width: 16px;
      height: 16px;
      background-color: #ff4d4f;
      color: white;
      border-radius: 50%;
      font-size: 10px;
      font-weight: bold;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    
    /* View All Button */
    .view-all-btn {
      font-size: 12px;
      color: #3498db;
      cursor: pointer;
      text-decoration: underline;
    }
    
    /* Grouped Notifications */
    .notification-group-collapsed .notification-item:nth-child(n+5) {
      display: none;
    }
    
    /* Dark Mode Compatibility */
    .dark-mode-preload .notification-panel,
    html[data-darkreader-mode] .notification-panel {
      background-color: #1e2a36 !important;
      box-shadow: -5px 0 15px rgba(0, 0, 0, 0.3);
    }
    
    .dark-mode-preload .notification-item, 
    html[data-darkreader-mode] .notification-item {
      background-color: #253545 !important;
    }
    
    .dark-mode-preload .notification-item:hover,
    html[data-darkreader-mode] .notification-item:hover {
      background-color: #2c3e50 !important;
    }
    
    .dark-mode-preload .notification-panel-header,
    html[data-darkreader-mode] .notification-panel-header {
      border-bottom-color: #495057 !important;
    }
    
    .dark-mode-preload .notification-item-important,
    html[data-darkreader-mode] .notification-item-important {
      background-color: rgba(255, 77, 79, 0.1) !important;
    }
    
    .dark-mode-preload .notification-item-system,
    html[data-darkreader-mode] .notification-item-system {
      background-color: rgba(52, 152, 219, 0.1) !important;
    }
    
    .dark-mode-preload .notification-item-message,
    html[data-darkreader-mode] .notification-item-message {
      background-color: rgba(82, 196, 26, 0.1) !important;
    }
    
    .dark-mode-preload .notification-item-other,
    html[data-darkreader-mode] .notification-item-other {
      background-color: rgba(250, 173, 20, 0.1) !important;
    }
    
    .dark-mode-preload .notification-group-count,
    html[data-darkreader-mode] .notification-group-count {
      background-color: #2c3e50 !important;
    }
    
    /* Overlay when notification panel is open */
    .notification-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 1049;
      display: none;
    }
    
    .notification-overlay.show {
      display: block;
    }
    
    /* Read notification styles */
    .notification-item.read {
      opacity: 0.7;
      background-color: #f8f8f8 !important;
    }
    
    html[data-darkreader-mode] .notification-item.read {
      background-color: #1c2630 !important;
    }
    
    .dark-mode-preload .notification-item.read {
      background-color: #1c2630 !important;
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
                <!-- Notification Bell - Added here -->
                <li class="nav-item me-3">
                  <button id="notificationBell" class="btn btn-ghost-secondary notification-bell" 
                          title="Notifications">
                    <i class="bi-bell fs-4"></i>
                    <span class="notification-badge" id="notificationCount">0</span>
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

  <!-- Toast Container - For Displaying Notifications -->
  <div class="toast-container" id="toastContainer">
    <!-- Toasts will be dynamically added here -->
  </div>

  <!-- Notification Panel -->
  <div class="notification-panel" id="notificationPanel">
    <div class="notification-panel-header">
      <h4 class="mb-0">Notifications</h4>
      <button id="clearAllNotifications" class="btn btn-sm btn-ghost-secondary">Mark All Read</button>
    </div>
    
    <!-- Reports Group -->
    <div class="notification-group" id="reportsGroup">
      <div class="notification-group-header">
        <span><i class="bi-file-earmark-text me-2"></i> Reports</span>
        <span class="notification-group-count" id="reportsCount">0</span>
      </div>
      <div class="notification-items" id="reportsItems">
        <!-- Report notifications will be dynamically added here -->
      </div>
    </div>
    
    <!-- System Updates Group -->
    <div class="notification-group" id="systemGroup">
      <div class="notification-group-header">
        <span><i class="bi-gear me-2"></i> System Updates</span>
        <span class="notification-group-count" id="systemCount">0</span>
      </div>
      <div class="notification-items" id="systemItems">
        <!-- System notifications will be dynamically added here -->
      </div>
    </div>
    
    <!-- Messages Group -->
    <div class="notification-group" id="messagesGroup">
      <div class="notification-group-header">
        <span><i class="bi-chat-dots me-2"></i> Messages</span>
        <span class="notification-group-count" id="messagesCount">0</span>
      </div>
      <div class="notification-items" id="messagesItems">
        <!-- Message notifications will be dynamically added here -->
      </div>
    </div>
    
    <!-- Other Notifications Group -->
    <div class="notification-group" id="otherGroup">
      <div class="notification-group-header">
        <span><i class="bi-bell me-2"></i> Other Notifications</span>
        <span class="notification-group-count" id="otherCount">0</span>
      </div>
      <div class="notification-items" id="otherItems">
        <!-- Other notifications will be dynamically added here -->
      </div>
    </div>
  </div>

  <!-- Notification Overlay - For closing the panel when clicking outside -->
  <div class="notification-overlay" id="notificationOverlay"></div>

  <!-- Rest of your page content goes here -->
  
  <!-- Notification System JavaScript -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Notification system configuration
      const notificationSystem = {
        // DOM Elements
        elements: {
          bell: document.getElementById('notificationBell'),
          panel: document.getElementById('notificationPanel'),
          overlay: document.getElementById('notificationOverlay'),
          toastContainer: document.getElementById('toastContainer'),
          badge: document.getElementById('notificationCount'),
          clearAllBtn: document.getElementById('clearAllNotifications'),
          counters: {
            reports: document.getElementById('reportsCount'),
            system: document.getElementById('systemCount'),
            messages: document.getElementById('messagesCount'),
            other: document.getElementById('otherCount')
          }
        },
        
        // Storage for notifications
        notifications: {
          reports: [],
          system: [],
          messages: [],
          other: []
        },
        
        // Grouping state
        groupState: {
          reports: false,
          system: false,
          messages: false,
          other: false
        },
        
        // Initialize the notification system
        init: function() {
          // Add event listeners
          this.addEventListeners();
          
          // Empty notification containers
          document.querySelectorAll('.notification-items').forEach(container => {
            container.innerHTML = '';
          });
          
          // Fetch notifications from server
          this.fetchNotifications();
          
          // Set up periodic refresh (every 60 seconds)
          setInterval(() => {
            this.fetchNotifications(true); // silent refresh
          }, 60000);
          
          // Add toast container to DOM if it doesn't exist
          if (!this.elements.toastContainer) {
            this.elements.toastContainer = document.createElement('div');
            this.elements.toastContainer.id = 'toastContainer';
            this.elements.toastContainer.className = 'toast-container';
            document.body.appendChild(this.elements.toastContainer);
          }
        },
        
        // Fetch notifications from server
        fetchNotifications: function(silent = false) {
          fetch('../ajax/admin/fetch_admin_notifications.php')
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                // Store notifications
                this.notifications = data.notifications;
                
                // Store grouping state
                this.groupState = data.shouldGroup;
                
                // Update the notification panel
                this.updateNotificationPanel();
                
                // Update notification count
                this.updateNotificationCountFromData(data.counts);
                
                // Update group counters
                this.updateGroupCounters(data.counts);
                
                // Show toast for new notifications if not silent refresh
                if (!silent) {
                  this.showNewNotificationsToast();
                }
              } else {
                console.error('Error fetching notifications:', data.error);
              }
            })
            .catch(error => {
              console.error('Error fetching notifications:', error);
            });
        },
        
        // Update group counters
        updateGroupCounters: function(counts) {
          for (const category in counts) {
            if (category !== 'total' && this.elements.counters[category]) {
              this.elements.counters[category].textContent = counts[category];
              
              // Hide group if no notifications
              const group = document.getElementById(`${category}Group`);
              if (group) {
                if (counts[category] === 0 && this.notifications[category].length === 0) {
                  group.style.display = 'none';
                } else {
                  group.style.display = 'block';
                }
              }
            }
          }
        },
        
        // Update notification panel with fetched notifications
        updateNotificationPanel: function() {
          // Clear existing notifications
          document.querySelectorAll('.notification-items').forEach(container => {
            container.innerHTML = '';
          });
          
          // Add each notification to the panel
          for (const category in this.notifications) {
            const container = document.getElementById(`${category}Items`);
            if (!container) continue;
            
            // Get parent group
            const group = document.getElementById(`${category}Group`);
            
            if (this.notifications[category].length === 0) {
              container.innerHTML = '<div class="text-muted py-3 text-center">No notifications</div>';
              
              // Hide empty groups
              if (group) {
                group.style.display = 'none';
              }
              continue;
            }
            
            // Show the group
            if (group) {
              group.style.display = 'block';
            }
            
            // Check if we need to group notifications
            const shouldGroup = this.groupState[category];
            if (shouldGroup) {
              // Add collapsible class
              container.parentElement.classList.add('notification-group-collapsed');
              
              // Add first 4 notifications
              for (let i = 0; i < Math.min(4, this.notifications[category].length); i++) {
                this.addNotificationToPanel(this.notifications[category][i], `${category}Items`);
              }
              
              // Add "View All" button if more than 4
              if (this.notifications[category].length > 4) {
                const viewAllBtn = document.createElement('div');
                viewAllBtn.className = 'text-center my-2';
                viewAllBtn.innerHTML = `<span class="view-all-btn">View all ${this.notifications[category].length} notifications</span>`;
                
                viewAllBtn.querySelector('.view-all-btn').addEventListener('click', () => {
                  container.parentElement.classList.remove('notification-group-collapsed');
                  
                  // Remove "View All" button
                  viewAllBtn.remove();
                  
                  // Add remaining notifications
                  for (let i = 4; i < this.notifications[category].length; i++) {
                    this.addNotificationToPanel(this.notifications[category][i], `${category}Items`);
                  }
                });
                
                container.appendChild(viewAllBtn);
              }
            } else {
              // Add all notifications
              this.notifications[category].forEach(notification => {
                this.addNotificationToPanel(notification, `${category}Items`);
              });
            }
          }
        },
        
        // Show toast for new unread notifications
        showNewNotificationsToast: function() {
          const totalUnread = 
            this.countUnread(this.notifications.reports) + 
            this.countUnread(this.notifications.system) + 
            this.countUnread(this.notifications.messages) + 
            this.countUnread(this.notifications.other);
            
          if (totalUnread > 0) {
            // Find most recent unread notification
            let mostRecent = null;
            
            for (const category in this.notifications) {
              for (const notification of this.notifications[category]) {
                if (!notification.is_read && (!mostRecent || notification.time === 'Just now')) {
                  mostRecent = notification;
                }
              }
            }
            
            if (mostRecent) {
              this.showToast({
                id: 'new-notifications',
                title: totalUnread > 1 ? `${totalUnread} New Notifications` : 'New Notification',
                message: totalUnread > 1 ? 
                  `You have ${totalUnread} unread notifications including: "${mostRecent.title}"` : 
                  mostRecent.message,
                category: mostRecent.category,
                important: mostRecent.important
              });
            }
          }
        },
        
        // Count unread notifications in an array
        countUnread: function(notificationsArray) {
          return notificationsArray.filter(n => !n.is_read).length;
        },
        
        // Update notification count badge from fetched data
        updateNotificationCountFromData: function(counts) {
          const totalCount = counts.total;
          
          if (this.elements.badge) {
            this.elements.badge.textContent = totalCount;
            this.elements.badge.style.display = totalCount > 0 ? 'flex' : 'none';
          }
        },
        
        // Add event listeners for notification interactions
        addEventListeners: function() {
          const self = this;
          
          // Bell click - Toggle notification panel
          if (this.elements.bell) {
            this.elements.bell.addEventListener('click', function() {
              self.toggleNotificationPanel();
            });
          }
          
          // Close panel when clicking outside
          if (this.elements.overlay) {
            this.elements.overlay.addEventListener('click', function() {
              self.closeNotificationPanel();
            });
          }
          
          // Clear all notifications
          if (this.elements.clearAllBtn) {
            this.elements.clearAllBtn.addEventListener('click', function() {
              self.markAllAsRead();
            });
          }
        },
        
        // Toggle notification panel visibility
        toggleNotificationPanel: function() {
          if (this.elements.panel.classList.contains('show')) {
            this.closeNotificationPanel();
          } else {
            this.openNotificationPanel();
          }
        },
        
        // Open notification panel
        openNotificationPanel: function() {
          this.elements.panel.classList.add('show');
          this.elements.overlay.classList.add('show');
        },
        
        // Close notification panel
        closeNotificationPanel: function() {
          this.elements.panel.classList.remove('show');
          this.elements.overlay.classList.remove('show');
        },
        
        // Add notification to the panel
        addNotificationToPanel: function(notification, containerId) {
          const container = document.getElementById(containerId);
          if (!container) return;
          
          // Create notification item element
          const notificationItem = document.createElement('div');
          notificationItem.className = `notification-item notification-item-${notification.category === 'reports' ? 'important' : notification.category}`;
          if (notification.is_read) {
            notificationItem.classList.add('read');
          }
          notificationItem.setAttribute('data-id', notification.id);
          
          // Create notification content
          notificationItem.innerHTML = `
            <span class="close-btn"><i class="bi-x"></i></span>
            <div class="notification-title">${notification.title}</div>
            <div class="notification-content">${notification.message}</div>
            <div class="notification-time">${notification.time}</div>
            ${notification.actions && notification.actions.length > 0 ? `
              <div class="notification-actions">
                ${notification.actions.map(action => {
                  if (action.action === 'markAsRead') {
                    return `<button class="btn btn-sm btn-outline-${action.type} mark-read-btn">${action.text}</button>`;
                  } else {
                    return `<a href="${action.url}" class="btn btn-sm btn-outline-${action.type}">${action.text}</a>`;
                  }
                }).join('')}
              </div>
            ` : ''}
          `;
          
          // Add to container
          container.appendChild(notificationItem);
          
          // Mark as read when clicked (except on action buttons or close button)
          notificationItem.addEventListener('click', (e) => {
            if (!e.target.closest('.close-btn') && 
                !e.target.closest('.btn') && 
                !notification.is_read) {
              this.markNotificationRead(notification.id);
            }
          });
          
          // Add close button event - This deletes the notification
          const closeBtn = notificationItem.querySelector('.close-btn');
          if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
              e.stopPropagation();
              this.deleteNotification(notification.id);
            });
          }
          
          // Add "Mark as Read" button event
          const markReadBtn = notificationItem.querySelector('.mark-read-btn');
          if (markReadBtn) {
            markReadBtn.addEventListener('click', (e) => {
              e.stopPropagation();
              this.markNotificationRead(notification.id);
            });
          }
        },
        
        // Mark notification as read
        markNotificationRead: function(notificationId) {
          // Find notification in storage
          let foundNotification = null;
          let foundCategory = null;
          
          for (const category in this.notifications) {
            const index = this.notifications[category].findIndex(n => n.id === notificationId);
            if (index !== -1) {
              foundNotification = this.notifications[category][index];
              foundCategory = category;
              break;
            }
          }
          
          if (!foundNotification || foundNotification.is_read) return;
          
          // Update UI
          const notificationItem = document.querySelector(`.notification-item[data-id="${notificationId}"]`);
          if (notificationItem) {
            notificationItem.classList.add('read');
          }
          
          // Update in storage
          foundNotification.is_read = true;
          
          // Update count
          this.updateNotificationCount();
          
          // Update group counter
          if (foundCategory && this.elements.counters[foundCategory]) {
            const currentCount = parseInt(this.elements.counters[foundCategory].textContent);
            this.elements.counters[foundCategory].textContent = Math.max(0, currentCount - 1);
          }
          
          // Send to server
          const formData = new FormData();
          formData.append('notification_id', notificationId);
          
          fetch('../ajax/admin/mark_notification_read.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .catch(error => {
            console.error('Error marking notification as read:', error);
          });
        },
        
        // Delete a notification
        deleteNotification: function(notificationId) {
          // Remove from panel
          const notificationItem = document.querySelector(`.notification-item[data-id="${notificationId}"]`);
          if (notificationItem) {
            notificationItem.parentNode.removeChild(notificationItem);
          }
          
          // Find notification in storage
          let foundNotification = null;
          let foundCategory = null;
          let foundIndex = -1;
          
          for (const category in this.notifications) {
            const index = this.notifications[category].findIndex(n => n.id === notificationId);
            if (index !== -1) {
              foundNotification = this.notifications[category][index];
              foundCategory = category;
              foundIndex = index;
              break;
            }
          }
          
          if (!foundNotification) return;
          
          // Update count if needed
          if (!foundNotification.is_read) {
            // Update group counter
            if (foundCategory && this.elements.counters[foundCategory]) {
              const currentCount = parseInt(this.elements.counters[foundCategory].textContent);
              this.elements.counters[foundCategory].textContent = Math.max(0, currentCount - 1);
            }
          }
          
          // Remove from storage
          if (foundCategory && foundIndex !== -1) {
            this.notifications[foundCategory].splice(foundIndex, 1);
          }
          
          // Update notification count
          this.updateNotificationCount();
          
          // If panel is empty for this category, add "No notifications" message
          for (const category in this.notifications) {
            const container = document.getElementById(`${category}Items`);
            if (container && this.notifications[category].length === 0) {
              container.innerHTML = '<div class="text-muted py-3 text-center">No notifications</div>';
            }
          }
          
          // Send to server
          const formData = new FormData();
          formData.append('notification_id', notificationId);
          
          fetch('../ajax/admin/delete_notification.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .catch(error => {
            console.error('Error deleting notification:', error);
          });
        },
        
        // Update notification count badge
        updateNotificationCount: function() {
          const totalCount = 
            this.countUnread(this.notifications.reports) + 
            this.countUnread(this.notifications.system) + 
            this.countUnread(this.notifications.messages) + 
            this.countUnread(this.notifications.other);
          
          if (this.elements.badge) {
            this.elements.badge.textContent = totalCount;
            this.elements.badge.style.display = totalCount > 0 ? 'flex' : 'none';
          }
        },
        
        // Show toast notification
        showToast: function(notification) {
          const self = this;
          const toastElement = document.createElement('div');
          
          // Set toast class based on notification category
          let categoryClass = '';
          switch(notification.category) {
            case 'reports':
              categoryClass = 'toast-important';
              break;
            case 'system':
              categoryClass = 'toast-system';
              break;
            case 'messages':
              categoryClass = 'toast-message';
              break;
            default:
              categoryClass = 'toast-other';
          }
          
          // Create toast HTML
          toastElement.className = `toast ${categoryClass}`;
          toastElement.setAttribute('role', 'alert');
          toastElement.setAttribute('aria-live', 'assertive');
          toastElement.setAttribute('aria-atomic', 'true');
          toastElement.setAttribute('data-id', notification.id || 'system-toast-' + Date.now());
          
          toastElement.innerHTML = `
            <div class="toast-header">
              <div class="d-flex align-items-center flex-grow-1">
                <div class="flex-shrink-0">
                  <img class="avatar avatar-sm avatar-circle" src="../Learnix.jpg" alt="Logo">
                </div>
                <div class="flex-grow-1 ms-3">
                  <h5 class="mb-0">${notification.title}</h5>
                  <small class="ms-auto">Just Now</small>
                </div>
                <div class="text-end">
                  <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
              </div>
            </div>
            <div class="toast-body">
              ${notification.message}
              ${notification.important && notification.actions ? `
                <div class="mt-2 pt-2 border-top">
                  ${notification.actions.map(action => `
                    <a href="${action.url}" class="btn btn-sm btn-${action.type}">${action.text}</a>
                  `).join(' ')}
                </div>
              ` : ''}
            </div>
          `;
          
          // Add to toast container
          this.elements.toastContainer.appendChild(toastElement);
          
          // Add show class after a small delay for animation
          setTimeout(() => {
            toastElement.classList.add('show');
          }, 50);
          
          // Add close button event
          const closeBtn = toastElement.querySelector('.btn-close');
          if (closeBtn) {
            closeBtn.addEventListener('click', function() {
              self.removeToast(toastElement);
            });
          }
          
          // Auto-dismiss for non-important notifications
          if (!notification.important) {
            setTimeout(() => {
              self.removeToast(toastElement);
            }, 5000); // 5 seconds
          }
        },
        
        // Remove toast with animation
        removeToast: function(toastElement) {
          toastElement.classList.add('hide-animation');
          
          // Remove from DOM after animation completes
          setTimeout(() => {
            if (toastElement && toastElement.parentNode) {
              toastElement.parentNode.removeChild(toastElement);
            }
          }, 300); // Animation duration
        },
        
        // Mark all notifications as read
        markAllAsRead: function() {
          fetch('../ajax/admin/mark_all_notifications_read.php', {
            method: 'POST'
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // Update all notifications in storage as read
              for (const category in this.notifications) {
                this.notifications[category].forEach(notification => {
                  notification.is_read = true;
                });
              }
              
              // Update UI
              document.querySelectorAll('.notification-item').forEach(item => {
                item.classList.add('read');
              });
              
              // Update count
              this.updateNotificationCount();
              
              // Update group counters
              for (const category in this.elements.counters) {
                if (this.elements.counters[category]) {
                  this.elements.counters[category].textContent = '0';
                }
              }
              
              // Show success toast
              this.showToast({
                title: 'Notifications Cleared',
                message: 'All notifications have been marked as read.',
                category: 'system',
                important: false
              });
            }
          })
          .catch(error => {
            console.error('Error marking all notifications as read:', error);
          });
        }
      };
      
      // Initialize the notification system
      notificationSystem.init();
      
      // Expose the notification system to the window for debugging
      window.notificationSystem = notificationSystem;
    });
  </script>