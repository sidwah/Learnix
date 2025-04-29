<?php include '../includes/student-header.php'; ?>

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
                        src="../Uploads/profile/<?php echo $row['profile_pic'] ?>"
                        alt="Profile">
                    </div>
                  </div>
                  <h4 class="card-title mb-0"><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></h4>
                  <p class="card-text small"><?php echo $row['email']; ?></p>
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
                      <span class="badge bg-soft-dark text-dark rounded-pill nav-link-badge">0</span>
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
                  <li class="nav-item">
                    <a class="nav-link" href="my-badges.php">
                      <i class="bi-chat-dots nav-icon"></i> Badges
                    </a>
                  </li>
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
                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'instructor'): ?>
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
              <!-- Notification Controls -->
              <div class="flex justify-between items-center mb-4">
                <div class="flex space-x-4">
                  <select class="form-select w-40" id="filterType" onchange="filterNotifications()">
                    <option value="all">All Types</option>
                    <option value="course">Course Updates</option>
                    <option value="assignment">Assignments</option>
                    <option value="system">System</option>
                  </select>
                  <select class="form-select w-40" id="sortOrder" onchange="filterNotifications()">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                  </select>
                </div>
                <button class="btn btn-sm btn-outline-primary" onclick="markAllRead()">Mark All as Read</button>
              </div>

              <!-- Notification List -->
              <div id="notificationList" class="space-y-4">
                <!-- Sample Notification (Dynamically populated via JS) -->
                <div class="notification-item p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow flex justify-between items-center" data-type="course" data-timestamp="2025-04-28T10:00:00Z">
                  <div class="flex items-center space-x-4">
                    <i class="bi bi-book text-primary text-xl"></i>
                    <div>
                      <h5 class="text-sm font-semibold">New Lecture Added</h5>
                      <p class="text-sm text-gray-600">A new lecture on "Algorithms" has been added to Introduction to Computer Science.</p>
                      <span class="text-xs text-gray-400">Apr 28, 2025, 10:00 AM</span>
                    </div>
                  </div>
                  <div class="flex space-x-2">
                    <button class="btn btn-sm btn-soft-primary" onclick="toggleRead(this)">Mark as Read</button>
                    <button class="btn btn-sm btn-soft-danger" onclick="deleteNotification(this)">Delete</button>
                  </div>
                </div>
                <!-- Add more notifications dynamically via JS -->
              </div>

              <!-- Empty State -->
              <div id="emptyState" class="hidden text-center py-8">
                <i class="bi bi-bell-slash text-gray-400 text-4xl mb-2"></i>
                <p class="text-gray-600">No notifications to display.</p>
              </div>
            </div>
            <!-- End Body -->
          </div>
          <!-- End Card -->
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

<!-- JavaScript for Notification Interactivity -->
<script>
  // Sample notification data (replace with PHP backend data)
  const notifications = [{
      id: 1,
      type: 'course',
      title: 'New Lecture Added',
      message: 'A new lecture on "Algorithms" has been added to Introduction to Computer Science.',
      timestamp: '2025-04-28T10:00:00Z',
      read: false
    },
    {
      id: 2,
      type: 'assignment',
      title: 'Assignment Due Soon',
      message: 'Your Calculus Problem Set is due on May 3, 2025.',
      timestamp: '2025-04-27T15:30:00Z',
      read: false
    },
    {
      id: 3,
      type: 'system',
      title: 'System Maintenance',
      message: 'LMS will undergo maintenance on May 1, 2025, from 1:00 AM to 3:00 AM.',
      timestamp: '2025-04-26T09:00:00Z',
      read: true
    }
  ];

  // Render notifications
  function renderNotifications(filteredNotifications) {
    const notificationList = document.getElementById('notificationList');
    const emptyState = document.getElementById('emptyState');
    notificationList.innerHTML = '';

    if (filteredNotifications.length === 0) {
      emptyState.classList.remove('hidden');
      return;
    }

    emptyState.classList.add('hidden');
    filteredNotifications.forEach(notification => {
      const notificationItem = document.createElement('div');
      notificationItem.className = `notification-item p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow flex justify-between items-center ${notification.read ? 'opacity-75' : ''}`;
      notificationItem.dataset.type = notification.type;
      notificationItem.dataset.timestamp = notification.timestamp;
      notificationItem.innerHTML = `
        <div class="flex items-center space-x-4">
          <i class="bi bi-${notification.type === 'course' ? 'book' : notification.type === 'assignment' ? 'pencil-square' : 'gear'} text-primary text-xl"></i>
          <div>
            <h5 class="text-sm font-semibold">${notification.title}</h5>
            <p class="text-sm text-gray-600">${notification.message}</p>
            <span class="text-xs text-gray-400">${new Date(notification.timestamp).toLocaleString()}</span>
          </div>
        </div>
        <div class="flex space-x-2">
          <button class="btn btn-sm btn-soft-primary" onclick="toggleRead(this)">${notification.read ? 'Mark as Unread' : 'Mark as Read'}</button>
          <button class="btn btn-sm btn-soft-danger" onclick="deleteNotification(this)">Delete</button>
        </div>
      `;
      notificationList.appendChild(notificationItem);
    });
  }

  // Filter and sort notifications
  function filterNotifications() {
    const filterType = document.getElementById('filterType').value;
    const sortOrder = document.getElementById('sortOrder').value;

    let filteredNotifications = notifications;
    if (filterType !== 'all') {
      filteredNotifications = notifications.filter(n => n.type === filterType);
    }

    filteredNotifications.sort((a, b) => {
      const dateA = new Date(a.timestamp);
      const dateB = new Date(b.timestamp);
      return sortOrder === 'newest' ? dateB - dateA : dateA - dateB;
    });

    renderNotifications(filteredNotifications);
  }

  // Mark all notifications as read
  function markAllRead() {
    notifications.forEach(n => n.read = true);
    filterNotifications();
  }

  // Toggle read/unread status
  function toggleRead(button) {
    const notificationItem = button.closest('.notification-item');
    const timestamp = notificationItem.dataset.timestamp;
    const notification = notifications.find(n => n.timestamp === timestamp);
    notification.read = !notification.read;
    filterNotifications();
  }

  // Delete a notification
  function deleteNotification(button) {
    const notificationItem = button.closest('.notification-item');
    const timestamp = notificationItem.dataset.timestamp;
    const index = notifications.findIndex(n => n.timestamp === timestamp);
    notifications.splice(index, 1);
    filterNotifications();
  }

  // Initial render
  renderNotifications(notifications);
</script>