<!-- Navbar -->
<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <!-- Search -->
        <div class="navbar-nav align-items-center">
            <div class="nav-item d-flex align-items-center">
                <i class="bx bx-search fs-4 lh-0 me-2"></i>
                <input type="text"
                    class="form-control border-0 shadow-none"
                    placeholder="Search courses, instructors..."
                    aria-label="Search..."
                    style="width: 400px;" />
            </div>
        </div>
        <!-- /Search -->

        <ul class="navbar-nav flex-row align-items-center ms-auto">
            <!-- Notification Bell -->
            <li class="nav-item notification-item me-3">
                <a href="#" class="notification-link" aria-label="Notifications" data-bs-toggle="dropdown">
                    <i class="bx bx-bell bx-sm align-middle"></i>
                    <span class="notification-dot" id="notification-dot" style="display: none;"></span>
                </a>
                <!-- Dropdown menu -->
                <ul class="dropdown-menu dropdown-menu-end notification-dropdown" id="notification-dropdown">
                    <li class="dropdown-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0" style="font-size: 0.85rem;">Notifications</h6>
                        <span class="badge bg-primary rounded-pill" id="notification-count">0</span>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <div id="notification-list">
                        <!-- Notifications will be dynamically inserted here -->
                    </div>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li class="dropdown-footer d-flex justify-content-between align-items-center">
                        <a href="javascript:void(0);" class="dropdown-item" id="mark-all-read" style="display: none;">Mark All as Read</a>
                        <a href="notifications.php" class="dropdown-item">View All Notifications</a>
                    </li>
                </ul>
            </li>

            <!-- User -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="../Uploads/admin-avatar/default.png" alt class="w-px-40 h-auto rounded-circle" />
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <img src="../Uploads/admin-avatar/default.png" alt class="w-px-40 h-auto rounded-circle" />
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="fw-semibold d-block">Admin User</span>
                                    <small class="text-muted">Administrator</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="profile.php">
                            <i class="bx bx-user me-2"></i>
                            <span class="align-middle">My Profile</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="general-settings.php">
                            <i class="bx bx-cog me-2"></i>
                            <span class="align-middle">Settings</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="issue-reports.php">
                            <span class="d-flex align-items-center align-middle">
                                <i class="flex-shrink-0 bx bx-file me-2"></i>
                                <span class="flex-grow-1 align-middle">Reports</span>
                                <span class="flex-shrink-0 badge badge-center rounded-pill bg-danger w-px-20 h-px-20" id="reports-badge">2</span>
                            </span>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="../backend/signout.php">
                            <i class="bx bx-power-off me-2"></i>
                            <span class="align-middle">Sign Out</span>
                        </a>
                    </li>
                </ul>
            </li>
            <!--/ User -->
        </ul>
    </div>
</nav>

<!-- Content wrapper -->
<div class="content-wrapper">

    <!-- Add CSS for notifications -->
    <style>
        .notification-dot {
            position: absolute;
            top: 0.1rem;
            right: 0.1rem;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #ff3e1d;
            display: none;
        }

        .notification-dropdown {
            width: 400px;
            padding: 0;
            box-shadow: 0 2px 16px 0 rgba(67, 89, 113, 0.45);
        }

        .notification-dropdown .dropdown-header {
            padding: 0.75rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-item {
            position: relative;
        }

        .notification-item.read .notification-link {
            background-color: #f5f5f9;
        }

        .notification-item .dropdown-item {
            white-space: normal;
            padding: 0.75rem 1.5rem;
            position: relative;
        }

        .notification-item .dropdown-item:hover,
        .notification-item .dropdown-item:focus {
            background-color: rgba(67, 89, 113, 0.04);
        }

        .notification-dropdown .dropdown-footer {
            padding: 0.75rem 0;
        }

        .notification-dropdown .dropdown-divider {
            margin: 0;
        }

        .notification-close {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            font-size: 0.75rem;
            color: #696cff;
            cursor: pointer;
        }

        .notification-close:hover {
            color: #ff3e1d;
        }

        @media (max-width: 576px) {
            .notification-dropdown {
                width: 320px;
            }
        }
    </style>

    <!-- Add JavaScript for notifications -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notificationDot = document.getElementById('notification-dot');
            const notificationCount = document.getElementById('notification-count');
            const notificationList = document.getElementById('notification-list');
            const markAllRead = document.getElementById('mark-all-read');

            // Function to fetch notifications
            function fetchNotifications() {
                fetch('../backend/get_notifications.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateNotifications(data.notifications);
                            updateUnreadCount(data.unread_count || 0); // Ensure count is always a number
                        } else {
                            console.error('Error fetching notifications:', data.error);
                            updateUnreadCount(0); // Fallback to 0 on error
                        }
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        updateUnreadCount(0); // Fallback to 0 on fetch failure
                    });
            }

            // Function to update notification list
            function updateNotifications(notifications) {
                notificationList.innerHTML = '';
                if (notifications.length === 0) {
                    notificationList.innerHTML = `
                        <li class="dropdown-item text-center">
                            <p class="mb-0" style="font-size: 0.85rem;">No new notifications</p>
                        </li>
                    `;
                    markAllRead.style.display = 'none';
                    return;
                }
                const hasUnread = notifications.some(n => n.is_read === 0);
                markAllRead.style.display = hasUnread ? 'block' : 'none';

                notifications.forEach(notification => {
                    const isRead = notification.is_read === 1 ? 'read' : '';
                    const iconClass = getIconClass(notification.type);
                    const link = getNotificationLink(notification.type);
                    const notificationItem = `
                        <li class="notification-item ${isRead}" data-id="${notification.notification_id}">
                            <a href="${link}" class="dropdown-item notification-link">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar">
                                            <span class="avatar-initial rounded-circle bg-label-${notification.type === 'instructor_verification' ? 'success' : notification.type === 'course_approval' ? 'primary' : 'warning'}">
                                                <i class="bx ${iconClass}"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">${notification.title}</h6>
                                        <p class="mb-0" style="font-size: 0.85rem;">${notification.message}</p>
                                        <small class="text-muted" style="font-size: 0.65rem;">${notification.created_at}</small>
                                    </div>
                                </div>
                            </a>
                            <span class="notification-close" data-id="${notification.notification_id}">×</span>
                        </li>
                        <li><div class="dropdown-divider"></div></li>
                    `;
                    notificationList.insertAdjacentHTML('beforeend', notificationItem);
                });

                // Add event listeners for notification links
                document.querySelectorAll('.notification-link').forEach(link => {
                    link.addEventListener('click', function(e) {
                        const notificationItem = this.closest('.notification-item');
                        const notificationId = notificationItem.getAttribute('data-id');
                        if (!notificationItem.classList.contains('read')) {
                            markNotificationRead(notificationId);
                            notificationItem.classList.add('read');
                            updateMarkAllReadVisibility();
                        }
                    });
                });

                // Add event listeners for close buttons
                document.querySelectorAll('.notification-close').forEach(closeBtn => {
                    closeBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation(); // Prevent dropdown from closing
                        const notificationId = this.getAttribute('data-id');
                        hideNotification(notificationId);
                        this.closest('.notification-item').remove();
                        // Remove the following divider if it exists
                        const nextDivider = this.closest('.notification-item').nextElementSibling;
                        if (nextDivider && nextDivider.querySelector('.dropdown-divider')) {
                            nextDivider.remove();
                        }
                        fetchNotifications(); // Refresh notifications and count
                    });
                });
            }

            // Function to update unread count
            function updateUnreadCount(count) {
                // Ensure count is a number and update UI
                const numericCount = Number(count) || 0;
                notificationCount.textContent = numericCount;
                notificationDot.style.display = numericCount > 0 ? 'block' : 'none';
            }

            // Function to mark a single notification as read
            function markNotificationRead(notificationId) {
                fetch(`../backend/mark_notification_read.php?id=${notificationId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            fetchNotifications(); // Refresh to ensure accurate count
                        } else {
                            console.error('Error marking notification as read:', data.error);
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            }

            // Function to mark all notifications as read
            function markAllNotificationsRead() {
                fetch('../backend/mark_all_notifications_read.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            fetchNotifications(); // Refresh to update UI and count
                        } else {
                            console.error('Error marking all notifications as read:', data.error);
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            }

            // Function to hide a notification
            function hideNotification(notificationId) {
                fetch(`../backend/hide_notification.php?id=${notificationId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            console.error('Error hiding notification:', data.error);
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            }

            // Function to update "Mark All as Read" visibility
            function updateMarkAllReadVisibility() {
                const unreadItems = document.querySelectorAll('.notification-item:not(.read)');
                markAllRead.style.display = unreadItems.length > 0 ? 'block' : 'none';
            }

            // Helper function to get icon class based on notification type
            function getIconClass(type) {
                switch (type) {
                    case 'instructor_verification':
                        return 'bx-user-check';
                    case 'course_approval':
                        return 'bx-book-content';
                    case 'system_alert':
                        return 'bx-error';
                    default:
                        return 'bx-bell';
                }
            }

            // Helper function to get link based on notification type
            function getNotificationLink(type) {
                switch (type) {
                    case 'instructor_verification':
                        return 'instructors-verification.php';
                    case 'course_approval':
                        return 'courses-pending.php';
                    case 'system_alert':
                        return 'reports-overview.php';
                    default:
                        return '#';
                }
            }

            // Initial fetch
            fetchNotifications();

            // Poll for new notifications every 5 seconds
            setInterval(() => {
                fetchNotifications();
            }, 5000);

            // Event listener for mark all read
            markAllRead.addEventListener('click', function(e) {
                e.preventDefault();
                markAllNotificationsRead();
            });
        });
    </script>
</div>