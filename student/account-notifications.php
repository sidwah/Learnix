<?php
include '../includes/student-header.php';
require '../backend/session_start.php'; // Ensure session is started
require '../backend/config.php'; // Ensure database connection

// Check if the user is signed in and is a student
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
  error_log("Unauthorized access attempt detected: " . json_encode($_SERVER));
  header('Location: ../signin.php');
  exit;
}
?>

<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main" class="bg-light">
  <!-- Breadcrumb -->
  <?php include '../includes/student-breadcrumb.php'; ?>
  <!-- End Breadcrumb -->

  <!-- Content -->
  <div class="container content-space-1 content-space-t-lg-0 content-space-b-lg-2 mt-lg-n10">
    <div class="row">
      <div class="col-lg-3">
        <!-- Navbar -->
        <div class="navbar-expand-lg navbar-light">
          <div id="sidebarNav" class="collapse navbar-collapse navbar-vertical">
            <!-- Card -->
            <div class="card flex-grow-1 mb-5">
              <div class="card-body">
                <!-- Avatar -->
                <div class="d-none d-lg-block text-center mb-5">
                  <div class="avatar avatar-xxl avatar-circle mb-3">
                    <div class="flex-shrink-0">
                      <img class="avatar avatar-xl avatar-circle"
                        src="../Uploads/profile/<?php echo htmlspecialchars($row['profile_pic']); ?>"
                        alt="Profile">
                    </div>
                  </div>
                  <h4 class="card-title mb-0"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></h4>
                  <p class="card-text small"><?php echo htmlspecialchars($row['email']); ?></p>
                </div>
                <!-- End Avatar -->

                <!-- Sidebar Content -->
                <!-- Overview Section -->
                <span class="text-cap">Overview</span>
                <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                  <li class="nav-item">
                    <a class="nav-link" href="account-overview.php">
                      <i class="bi-person-circle nav-icon"></i> Account Overview
                    </a>
                  </li>
                </ul>

                <!-- Account Section -->
                <span class="text-cap">Account</span>
                <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                  <li class="nav-item">
                    <a class="nav-link" href="account-profile.php">
                      <i class="bi-person-badge nav-icon"></i> Personal info
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="account-security.php">
                      <i class="bi-shield-shaded nav-icon"></i> Security
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link active" href="account-notifications.php">
                      <i class="bi-bell nav-icon"></i> Notifications
                      <span class="badge bg-soft-dark text-dark rounded-pill nav-link-badge" id="notificationCount">0</span>
                    </a>
                  </li>
                </ul>

                <!-- Student-Specific Section -->
                <span class="text-cap">My Courses</span>
                <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                  <li class="nav-item">
                    <a class="nav-link" href="my-courses.php">
                      <i class="bi-person-badge nav-icon"></i> Enrolled Courses
                    </a>
                  </li>
                  <!-- <li class="nav-item">
                    <a class="nav-link" href="my-badges.php">
                      <i class="bi-chat-dots nav-icon"></i> Badges
                    </a>
                  </li> -->
                  <li class="nav-item">
                    <a class="nav-link" href="my-certifications.php">
                      <i class="bi-award nav-icon"></i> Certifications
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="my-notes.php">
                      <i class="bi-journal-text nav-icon"></i> Notes
                    </a>
                  </li>
                </ul>

                <!-- Payment Section for Students -->
                <span class="text-cap">Payments</span>
                <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                  <li class="nav-item">
                    <a class="nav-link" href="payment-history.php">
                      <i class="bi-credit-card nav-icon"></i> Payment History
                    </a>
                  </li>
                </ul>

                <!-- Instructor/Admin Section (Dynamic Role Check) -->
                <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'instructor'])): ?>
                  <span class="text-cap">Instructor</span>
                  <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                    <li class="nav-item">
                      <a class="nav-link" href="instructor-courses.php">
                        <i class="bi-person-badge nav-icon"></i> My Courses
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="create-course.php">
                        <i class="bi-file-earmark-plus nav-icon"></i> Create Course
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="manage-students.php">
                        <i class="bi-person-check nav-icon"></i> Manage Students
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="course-feedback.php">
                        <i class="bi-chat-dots nav-icon"></i> Course Feedback
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="instructor-withdrawal.php">
                        <i class="bi-wallet nav-icon"></i> Withdrawal
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="instructor-analytics.php">
                        <i class="bi-gear nav-icon"></i> Analytics
                      </a>
                    </li>
                  </ul>
                <?php endif; ?>

                <!-- Sign-out & Help Section -->
                <span class="text-cap">---</span>
                <ul class="nav nav-sm nav-tabs nav-vertical">
                  <li class="nav-item">
                    <a class="nav-link" href="account-help.php">
                      <i class="bi-question-circle nav-icon"></i> Help
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="FAQ.php">
                      <i class="bi-card-list nav-icon"></i> FAQ's
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="report.php">
                      <i class="bi-exclamation-triangle nav-icon"></i> Report Issues
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="../backend/signout.php">
                      <i class="bi-box-arrow-right nav-icon"></i> Sign out
                    </a>
                  </li>
                </ul>
                <!-- End of Sidebar -->
              </div>
            </div>
            <!-- End Card -->
          </div>
        </div>
        <!-- End Navbar -->
      </div>
      <!-- End Col -->

      <div class="col-lg-9">
        <div class="d-grid gap-3 gap-lg-5">
          <!-- Card -->
          <div id="editAddressCard" class="card">
            <div class="card-header border-bottom">
              <h4 class="card-header-title">Notifications</h4>
            </div>

            <!-- Body -->
            <div class="card-body">
              <!-- Error Alert -->
              <div id="errorAlert" class="alert alert-danger alert-dismissible fade show d-none" role="alert">
                <span id="errorMessage"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>

              <!-- Notification Controls -->
              <div class="flex justify-between items-center mb-4">
                <div class="flex space-x-4">
                  <select class="form-select w-40" id="filterType" aria-label="Filter by type">
                    <option value="all">All Types</option>
                    <option value="course">Course Updates</option>
                    <option value="assignment">Assignments</option>
                    <option value="system">System</option>
                  </select>
                  <select class="form-select w-40" id="sortOrder" aria-label="Sort order">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                  </select>
                </div>
                <div class="flex space-x-2">
                  <button class="btn btn-sm btn-primary" onclick="markAllRead()">Mark All as Read</button>
                  <button class="btn btn-sm btn-outline-danger" onclick="clearAllNotifications()">Clear All</button>
                </div>
              </div>

              <!-- Notification List -->
              <div id="notificationList" class="space-y-4"></div>

              <!-- Empty State -->
              <div id="emptyState" class="hidden text-center py-8">
                <i class="bi bi-bell-slash text-gray-400 text-4xl mb-2"></i>
                <p class="text-gray-600">No notifications to display.</p>
              </div>
            </div>
            <!-- End Body -->
          </div>
          <!-- End Card -->

          <!-- Actionable Insights -->
          <div class="card bg-soft-light">
            <div class="card-body">
              <h5 class="card-title">Actionable Insights</h5>
              <ul class="list-unstyled">
                <li class="mb-2"><i class="bi-book me-2"></i> Check new course materials to stay up-to-date.</li>
                <li class="mb-2"><i class="bi-pencil-square me-2"></i> Submit assignments before deadlines.</li>
                <li><i class="bi-bell me-2"></i> Clear read notifications to stay organized.</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <!-- End Col -->
    </div>
    <!-- End Row -->
  </div>
  <!-- End Content -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- ========== FOOTER ========== -->
<?php include '../includes/student-footer.php'; ?>
<!-- ========== END FOOTER ========== -->

<!-- Tailwind CSS CDN -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

<!-- Custom CSS for Notifications -->
<style>
  .notification-item {
    padding: 1rem;
    border-radius: 0.5rem;
    background-color: #ffffff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.2s;
  }

  .notification-item:hover {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
  }

  .notification-item.unread {
    background-color: #f0f6ff;
    font-weight: 600;
    border-left: 4px solid #007bff;
  }

  .notification-item.hidden {
    background-color: #e9ecef;
    opacity: 0.7;
    font-style: italic;
  }

  .notification-item h5 {
    font-size: 0.95rem;
  }

  .notification-item p {
    font-size: 0.85rem;
  }

  .notification-item .small-text {
    font-size: 0.75rem;
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

  .action-icon {
    cursor: pointer;
    font-size: 1.1rem;
    margin-left: 12px;
    transition: opacity 0.2s;
  }

  .action-icon:hover {
    opacity: 0.7;
  }

  /* Dark mode adjustments */
  .dark-mode-preload .notification-item,
  .dark-mode-preload .card-body,
  .dark-mode-preload .bg-soft-light {
    background-color: #253545 !important;
    color: #e9ecef !important;
  }

  .dark-mode-preload .notification-item.unread {
    background-color: #2c3e50 !important;
    border-left: 4px solid #4dabf7;
  }

  .dark-mode-preload .notification-item.hidden {
    background-color: #2c3e50 !important;
    opacity: 0.7;
  }

  .dark-mode-preload .notification-item:hover {
    background-color: #34495e !important;
  }

  .dark-mode-preload .action-icon.text-success {
    color: #2ecc71 !important;
  }

  .dark-mode-preload .action-icon.text-warning {
    color: #f1c40f !important;
  }

  .dark-mode-preload .action-icon.text-danger {
    color: #e74c3c !important;
  }
</style>

<!-- JavaScript for Notification Interactivity -->
<script>
  // Type-to-icon mapping
  const typeIcons = {
    course: "book",
    assignment: "pencil-square",
    system: "gear"
  };

  // Alert classes for notification icons
  const alertClasses = [
    "alert-soft-primary",
    "alert-soft-success",
    "alert-soft-warning",
    "alert-soft-danger"
  ];

  function getRandomAlertClass() {
    return alertClasses[Math.floor(Math.random() * alertClasses.length)];
  }

  function formatTime(createdAt) {
    const now = new Date();
    const created = new Date(createdAt);
    const diff = Math.floor((now - created) / 1000);

    if (diff < 60) return `${diff} seconds ago`;
    if (diff < 3600) return `${Math.floor(diff / 60)} minutes ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} hours ago`;
    return created.toLocaleString();
  }

  function showError(message) {
    const errorAlert = document.getElementById('errorAlert');
    const errorMessage = document.getElementById('errorMessage');
    if (errorAlert && errorMessage) {
      errorMessage.textContent = message;
      errorAlert.classList.remove('d-none');
      setTimeout(() => errorAlert.classList.add('d-none'), 5000);
    }
  }

  async function fetchNotifications(notificationList, emptyState, filterType, sortOrder) {
    try {
      emptyState.classList.add('hidden');
      notificationList.innerHTML = '<div class="text-center py-4"><i class="bi-arrow-clockwise fs-4 animate-spin"></i></div>';

      const type = filterType.value;
      const sort = sortOrder.value === 'newest' ? 'DESC' : 'ASC';
      const response = await fetch(`../backend/filter_notifications.php?type=${type}&sort=${sort}`);
      const data = await response.json();

      if (data.success && data.notifications.length > 0) {
        renderNotifications(notificationList, data.notifications);
        updateNotificationCount(data.notifications.filter(n => !n.is_read).length);
      } else {
        notificationList.innerHTML = '';
        emptyState.classList.remove('hidden');
      }
    } catch (error) {
      console.error("Error fetching notifications:", error);
      notificationList.innerHTML = '';
      emptyState.classList.remove('hidden');
      showError('Failed to load notifications.');
    }
  }

  function renderNotifications(notificationList, notifications) {
    notificationList.innerHTML = '';
    notifications.forEach(notification => {
      const iconClass = typeIcons[notification.type] || "bell";
      const alertClass = getRandomAlertClass();
      const readIcon = notification.is_read ? 'envelope' : 'envelope-open';
      const readColor = notification.is_read ? 'text-success' : 'text-warning';
      const notificationItem = document.createElement('div');
      notificationItem.className = `notification-item p-4 rounded-lg flex justify-between items-center ${notification.is_hidden ? 'hidden' : notification.is_read ? 'read opacity-75' : 'unread'}`;
      // Only include toggle icon for unread notifications
      const toggleIcon = notification.is_read ? '' : `
        <i class="bi-read-toggle bi-${readIcon} ${readColor} fs-5 action-icon me-2" 
           data-bs-toggle="tooltip" 
           data-bs-title="Mark as Read" 
           onclick="toggleReadStatus(${notification.notification_id})"></i>`;
      notificationItem.innerHTML = `
        <div class="flex items-center space-x-4">
          <span class="notification-icon ${alertClass}">
            <i class="bi-${iconClass} text-primary text-xl"></i>
          </span>
          <div>
            <h5 class="text-sm font-semibold">${notification.title}</h5>
            <p class="text-sm text-gray-600">${notification.message}</p>
            <span class="text-xs text-gray-400 small-text">${formatTime(notification.created_at)}</span>
          </div>
        </div>
        <div class="flex space-x-2">
          ${toggleIcon}
          <i class="bi-archive-action bi-archive text-danger fs-5 action-icon" 
             data-bs-toggle="tooltip" 
             data-bs-title="${notification.is_hidden ? 'Restore' : 'Archive'}" 
             onclick="hideNotification(${notification.notification_id})"></i>
        </div>
      `;
      notificationList.appendChild(notificationItem);
    });

    // Reinitialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
  }

  function updateNotificationCount(count) {
    const badge = document.getElementById('notificationCount');
    if (badge) {
      badge.textContent = count;
      badge.classList.toggle('d-none', count === 0);
    }
  }

  async function toggleReadStatus(notificationId) {
    try {
      const url = `../backend/mark_notification_read.php?id=${notificationId}`;
      console.log(`Calling: ${url}`); // Debug URL
      const response = await fetch(url);
      const data = await response.json();
      console.log("Backend response:", JSON.stringify(data)); // Debug response
      if (data.success) {
        const notificationList = document.getElementById("notificationList");
        const emptyState = document.getElementById("emptyState");
        const filterType = document.getElementById("filterType");
        const sortOrder = document.getElementById("sortOrder");
        if (notificationList && emptyState && filterType && sortOrder) {
          fetchNotifications(notificationList, emptyState, filterType, sortOrder);
        } else {
          console.error("DOM elements not found for refresh");
          showError('Failed to refresh notifications.');
        }
      } else {
        console.error("Error marking as read:", data.error || "Unknown error");
        showError(`Failed to mark notification as read: ${data.error || 'Unknown error'}`);
      }
    } catch (error) {
      console.error("Error marking as read:", error);
      showError('Failed to mark notification as read due to a network error.');
    }
  }

  async function hideNotification(notificationId) {
    try {
      const response = await fetch(`../backend/hide_notification.php?id=${notificationId}`);
      const data = await response.json();
      if (data.success) {
        const notificationList = document.getElementById("notificationList");
        const emptyState = document.getElementById("emptyState");
        const filterType = document.getElementById("filterType");
        const sortOrder = document.getElementById("sortOrder");
        if (notificationList && emptyState && filterType && sortOrder) {
          fetchNotifications(notificationList, emptyState, filterType, sortOrder);
        }
      } else {
        console.error("Error hiding/unhiding notification:", data.error);
        showError(`Failed to archive/restore notification: ${data.error || 'Unknown error'}`);
      }
    } catch (error) {
      console.error("Error hiding/unhiding notification:", error);
      showError('Failed to archive/restore notification due to a network error.');
    }
  }

  async function markAllRead() {
    try {
      const response = await fetch("../backend/mark_all_notifications_read.php");
      const data = await response.json();
      if (data.success) {
        const notificationList = document.getElementById("notificationList");
        const emptyState = document.getElementById("emptyState");
        const filterType = document.getElementById("filterType");
        const sortOrder = document.getElementById("sortOrder");
        if (notificationList && emptyState && filterType && sortOrder) {
          fetchNotifications(notificationList, emptyState, filterType, sortOrder);
        }
      } else {
        console.error("Error marking all notifications as read:", data.error);
        showError(`Failed to mark all notifications as read: ${data.error || 'Unknown error'}`);
      }
    } catch (error) {
      console.error("Error marking all:", error);
      showError('Failed to mark all notifications as read due to a network error.');
    }
  }

  async function clearAllNotifications() {
    try {
      const notifications = document.querySelectorAll('.notification-item:not(.hidden)');
      for (const notification of notifications) {
        const notificationId = parseInt(notification.querySelector('.bi-archive-action').getAttribute('onclick').match(/\d+/)[0]);
        await hideNotification(notificationId);
      }
      const notificationList = document.getElementById("notificationList");
      const emptyState = document.getElementById("emptyState");
      const filterType = document.getElementById("filterType");
      const sortOrder = document.getElementById("sortOrder");
      if (notificationList && emptyState && filterType && sortOrder) {
        fetchNotifications(notificationList, emptyState, filterType, sortOrder);
      }
    } catch (error) {
      console.error("Error clearing notifications:", error);
      showError('Failed to clear notifications due to a network error.');
    }
  }

  // Main event listener
  document.addEventListener("DOMContentLoaded", function() {
    const notificationList = document.getElementById("notificationList");
    const emptyState = document.getElementById("emptyState");
    const filterType = document.getElementById("filterType");
    const sortOrder = document.getElementById("sortOrder");

    if (!notificationList || !emptyState || !filterType || !sortOrder) {
      console.error("Required DOM elements not found");
      showError('Page initialization failed due to missing elements.');
      return;
    }

    // Add event listeners for filters
    filterType.addEventListener("change", () => fetchNotifications(notificationList, emptyState, filterType, sortOrder));
    sortOrder.addEventListener("change", () => fetchNotifications(notificationList, emptyState, filterType, sortOrder));

    // Initial fetch
    fetchNotifications(notificationList, emptyState, filterType, sortOrder);

    // Poll every 5 seconds
    setInterval(() => fetchNotifications(notificationList, emptyState, filterType, sortOrder), 5000);
  });
</script>