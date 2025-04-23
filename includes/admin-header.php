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
                    <span class="notification-badge" id="notificationCount">3</span>
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
      <button id="clearAllNotifications" class="btn btn-sm btn-ghost-secondary">Clear All</button>
    </div>
    
    <!-- Reports Group -->
    <div class="notification-group" id="reportsGroup">
      <div class="notification-group-header">
        <i class="bi-file-earmark-text me-2"></i> Reports
      </div>
      <div class="notification-items" id="reportsItems">
        <!-- Report notifications will be dynamically added here -->
        <div class="notification-item notification-item-important" data-id="report-1">
          <span class="close-btn"><i class="bi-x"></i></span>
          <div class="notification-title">Monthly Revenue Report Ready</div>
          <div class="notification-content">The monthly revenue report for April 2025 is now available for review.</div>
          <div class="notification-time">2 hours ago</div>
          <div class="notification-actions">
            <button class="btn btn-sm btn-outline-primary">View Report</button>
            <button class="btn btn-sm btn-outline-secondary">Download</button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- System Updates Group -->
    <div class="notification-group" id="systemGroup">
      <div class="notification-group-header">
        <i class="bi-gear me-2"></i> System Updates
      </div>
      <div class="notification-items" id="systemItems">
        <!-- System notifications will be dynamically added here -->
        <div class="notification-item notification-item-system" data-id="system-1">
          <span class="close-btn"><i class="bi-x"></i></span>
          <div class="notification-title">System Maintenance</div>
          <div class="notification-content">System maintenance scheduled for tonight at 2:00 AM UTC.</div>
          <div class="notification-time">5 hours ago</div>
        </div>
      </div>
    </div>
    
    <!-- Messages Group -->
    <div class="notification-group" id="messagesGroup">
      <div class="notification-group-header">
        <i class="bi-chat-dots me-2"></i> Messages
      </div>
      <div class="notification-items" id="messagesItems">
        <!-- Message notifications will be dynamically added here -->
        <div class="notification-item notification-item-message" data-id="message-1">
          <span class="close-btn"><i class="bi-x"></i></span>
          <div class="notification-title">New Message from Sarah</div>
          <div class="notification-content">Hi there! Just wanted to check in about the new course materials.</div>
          <div class="notification-time">1 day ago</div>
          <div class="notification-actions">
            <button class="btn btn-sm btn-outline-primary">Reply</button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Other Notifications Group -->
    <div class="notification-group" id="otherGroup">
      <div class="notification-group-header">
        <i class="bi-bell me-2"></i> Other Notifications
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
          clearAllBtn: document.getElementById('clearAllNotifications')
        },
        
        // Storage for notifications
        notifications: {
          reports: [],
          system: [],
          messages: [],
          other: []
        },
        
        // Initialize the notification system
        init: function() {
          // Add event listeners
          this.addEventListeners();
          
          // Load saved notifications from localStorage (in a real app)
          // this.loadNotifications();
          
          // Add some test notifications
          this.addTestNotifications();
          
          // Update notification count badge
          this.updateNotificationCount();
          
          // Add toast container to DOM if it doesn't exist
          if (!this.elements.toastContainer) {
            this.elements.toastContainer = document.createElement('div');
            this.elements.toastContainer.id = 'toastContainer';
            this.elements.toastContainer.className = 'toast-container';
            document.body.appendChild(this.elements.toastContainer);
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
              self.clearAllNotifications();
            });
          }
          
          // Individual notification close buttons
          document.querySelectorAll('.notification-item .close-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
              e.stopPropagation();
              const notificationItem = this.closest('.notification-item');
              const notificationId = notificationItem.dataset.id;
              self.removeNotification(notificationId);
            });
          });
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
        
        // Add a new notification
        addNotification: function(notification) {
          // Add to storage based on category
          switch(notification.category) {
            case 'reports':
              this.notifications.reports.unshift(notification);
              this.addNotificationToPanel(notification, 'reportsItems');
              break;
            case 'system':
              this.notifications.system.unshift(notification);
              this.addNotificationToPanel(notification, 'systemItems');
              break;
            case 'messages':
              this.notifications.messages.unshift(notification);
              this.addNotificationToPanel(notification, 'messagesItems');
              break;
            default:
              this.notifications.other.unshift(notification);
              this.addNotificationToPanel(notification, 'otherItems');
          }
          
          // Show toast notification
          this.showToast(notification);
          
          // Update notification count
          this.updateNotificationCount();
          
          // Save notifications (in a real app)
          // this.saveNotifications();
          
          return notification.id;
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
          toastElement.setAttribute('data-id', notification.id);
          
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
                    <button type="button" class="btn btn-sm btn-${action.type}">${action.text}</button>
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
          
          // Add action button events if important
          if (notification.important && notification.actions) {
            const actionButtons = toastElement.querySelectorAll('.toast-body .btn');
            actionButtons.forEach((btn, index) => {
              btn.addEventListener('click', function() {
                if (notification.actions[index].callback) {
                  notification.actions[index].callback();
                }
                // Don't remove the toast for important notifications
              });
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
        
        // Add notification to the panel
        addNotificationToPanel: function(notification, containerId) {
          const container = document.getElementById(containerId);
          if (!container) return;
          
          // Create notification item element
          const notificationItem = document.createElement('div');
          notificationItem.className = `notification-item notification-item-${notification.category === 'reports' ? 'important' : notification.category}`;
          notificationItem.setAttribute('data-id', notification.id);
          
          // Create notification content
          notificationItem.innerHTML = `
            <span class="close-btn"><i class="bi-x"></i></span>
            <div class="notification-title">${notification.title}</div>
            <div class="notification-content">${notification.message}</div>
            <div class="notification-time">${notification.time}</div>
            ${notification.actions ? `
              <div class="notification-actions">
                ${notification.actions.map(action => `
                  <button class="btn btn-sm btn-outline-${action.type}">${action.text}</button>
                `).join('')}
              </div>
            ` : ''}
          `;
          
          // Add event listeners for notification item
          container.prepend(notificationItem);
          
          // Add close button event
          const closeBtn = notificationItem.querySelector('.close-btn');
          if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
              e.stopPropagation();
              this.removeNotification(notification.id);
            });
          }
          
          // Add action button events
          if (notification.actions) {
            const actionButtons = notificationItem.querySelectorAll('.notification-actions .btn');
            actionButtons.forEach((btn, index) => {
              btn.addEventListener('click', function(e) {
                e.stopPropagation();
                if (notification.actions[index].callback) {
                  notification.actions[index].callback();
                }
              });
            });
          }
        },
        
        // Remove a notification
        removeNotification: function(notificationId) {
          // Remove from panel
          const notificationItem = document.querySelector(`.notification-item[data-id="${notificationId}"]`);
          if (notificationItem) {
            notificationItem.parentNode.removeChild(notificationItem);
          }
          
          // Remove from storage
          let notificationRemoved = false;
          
          for (const category in this.notifications) {
            const index = this.notifications[category].findIndex(n => n.id === notificationId);
            if (index !== -1) {
              this.notifications[category].splice(index, 1);
              notificationRemoved = true;
              break;
            }
          }
          
          // Remove toast if exists
          const toast = document.querySelector(`.toast[data-id="${notificationId}"]`);
          if (toast) {
            this.removeToast(toast);
          }
          
          // Update notification count
          if (notificationRemoved) {
            this.updateNotificationCount();
            // Save notifications (in a real app)
            // this.saveNotifications();
          }
        },
        
        // Clear all notifications
        clearAllNotifications: function() {
          // Clear all notification items from panel
          document.querySelectorAll('.notification-items').forEach(container => {
            container.innerHTML = '';
          });
          
          // Clear storage
          this.notifications = {
            reports: [],
            system: [],
            messages: [],
            other: []
          };
          
          // Remove all toasts
          const toasts = document.querySelectorAll('.toast');
          toasts.forEach(toast => {
            this.removeToast(toast);
          });
          
          // Update notification count
          this.updateNotificationCount();
          
          // Save notifications (in a real app)
          // this.saveNotifications();
        },
        
        // Update notification count badge
        updateNotificationCount: function() {
          const totalCount = 
            this.notifications.reports.length + 
            this.notifications.system.length + 
            this.notifications.messages.length + 
            this.notifications.other.length;
          
          if (this.elements.badge) {
            this.elements.badge.textContent = totalCount;
            this.elements.badge.style.display = totalCount > 0 ? 'flex' : 'none';
          }
        },
        
        // Add test notifications for demo
        addTestNotifications: function() {
          // Add notification groups to the panel if they don't exist
          const groups = ['reports', 'system', 'messages', 'other'];
          groups.forEach(group => {
            if (!document.getElementById(`${group}Group`)) {
              const groupElement = document.createElement('div');
              groupElement.className = 'notification-group';
              groupElement.id = `${group}Group`;
              
              const header = document.createElement('div');
              header.className = 'notification-group-header';
              
              let icon = 'bell';
              switch(group) {
                case 'reports': icon = 'file-earmark-text'; break;
                case 'system': icon = 'gear'; break;
                case 'messages': icon = 'chat-dots'; break;
              }
              
              header.innerHTML = `<i class="bi-${icon} me-2"></i> ${group.charAt(0).toUpperCase() + group.slice(1)}`;
              
              const items = document.createElement('div');
              items.className = 'notification-items';
              items.id = `${group}Items`;
              
              groupElement.appendChild(header);
              groupElement.appendChild(items);
              
              this.elements.panel.appendChild(groupElement);
            }
          });
        },
        
        // Generate a unique ID for notifications
        generateId: function(prefix) {
          return `${prefix}-${Date.now()}-${Math.floor(Math.random() * 1000)}`;
        },
        
        // Create a standard notification
        createStandardNotification: function(title, message, category = 'other') {
          const notification = {
            id: this.generateId(category),
            title: title,
            message: message,
            category: category,
            time: 'Just now',
            important: false
          };
          
          return this.addNotification(notification);
        },
        
        // Create an important notification with actions
        createImportantNotification: function(title, message, actions = [], category = 'reports') {
          const notification = {
            id: this.generateId(category),
            title: title,
            message: message,
            category: category,
            time: 'Just now',
            important: true,
            actions: actions
          };
          
          return this.addNotification(notification);
        }
      };
      
      // Initialize the notification system
      notificationSystem.init();
      
      // Expose the notification system to the window for testing
      window.notificationSystem = notificationSystem;
      
      // Add test notifications on page load for demo purposes
      setTimeout(() => {
        notificationSystem.createStandardNotification(
          'Welcome to Learnix Admin',
          'You have successfully logged in to the administration panel.',
          'system'
        );
        
        setTimeout(() => {
          notificationSystem.createImportantNotification(
            'New Course Submission',
            'A new course "Advanced Machine Learning" has been submitted for review.',
            [
              { text: 'Review Now', type: 'primary', callback: () => alert('Review action clicked') },
              { text: 'Later', type: 'secondary', callback: () => alert('Later action clicked') }
            ],
            'reports'
          );
          
          setTimeout(() => {
            notificationSystem.createStandardNotification(
              'New Message from John',
              'Hey there! I have a question about the course platform.',
              'messages'
            );
          }, 2000);
        }, 3000);
      }, 1000);
      
      // Add demo button to test notifications
      const demoContainer = document.createElement('div');
      demoContainer.className = 'container mt-4';
      demoContainer.innerHTML = `
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">Notification System Demo</h5>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <h6>Standard Notifications</h6>
                <button id="demoStandardSystem" class="btn btn-sm btn-primary mb-2">System Update</button>
                <button id="demoStandardMessage" class="btn btn-sm btn-success mb-2">New Message</button>
                <button id="demoStandardOther" class="btn btn-sm btn-warning mb-2">Other Notification</button>
              </div>
              <div class="col-md-6">
                <h6>Important Notifications</h6>
                <button id="demoImportantReport" class="btn btn-sm btn-danger mb-2">Report Ready</button>
                <button id="demoImportantSystem" class="btn btn-sm btn-info mb-2">Important System Alert</button>
              </div>
            </div>
          </div>
        </div>
      `;
      
      document.body.appendChild(demoContainer);
      
      // Add event listeners for demo buttons
      document.getElementById('demoStandardSystem').addEventListener('click', function() {
        notificationSystem.createStandardNotification(
          'System Update Available',
          'A new system update is available for installation.',
          'system'
        );
      });
      
      document.getElementById('demoStandardMessage').addEventListener('click', function() {
        notificationSystem.createStandardNotification(
          'New Message from Admin',
          'Please review the updated guidelines for course submissions.',
          'messages'
        );
      });
      
      document.getElementById('demoStandardOther').addEventListener('click', function() {
        notificationSystem.createStandardNotification(
          'Browser Update',
          'Your browser is up to date!',
          'other'
        );
      });
      
      document.getElementById('demoImportantReport').addEventListener('click', function() {
        notificationSystem.createImportantNotification(
          'Critical Report Ready',
          'The quarterly financial report is now available for review.',
          [
            { text: 'View Report', type: 'primary', callback: () => alert('View Report clicked') },
            { text: 'Download', type: 'secondary', callback: () => alert('Download clicked') }
          ],
          'reports'
        );
      });
      
      document.getElementById('demoImportantSystem').addEventListener('click', function() {
        notificationSystem.createImportantNotification(
          'Urgent System Maintenance',
          'The system will undergo maintenance in 30 minutes. Please save your work.',
          [
            { text: 'Got it', type: 'primary', callback: () => alert('Got it clicked') }
          ],
          'system'
        );
      });
    });
  </script>
  
