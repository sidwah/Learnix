<?php
include("../backend/config.php");
// Near the top of your file where you fetch user data
$user_id = $_SESSION['user_id'];

// Get user profile pic, name and verification status
$query = "SELECT u.profile_pic, u.first_name, u.last_name
          FROM users u 
          LEFT JOIN instructors i ON u.user_id = i.user_id 
          WHERE u.user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$userData = mysqli_fetch_assoc($result);

// Set profile image path
$defaultImage = "default.png";
$profileImage = $userData['profile_pic'] ? "../Uploads/instructor-profile/" . $userData['profile_pic'] : $defaultImage;

// Store user name for display
$userName = $userData['first_name'] . ' ' . $userData['last_name'];
?>

<div class="navbar-custom">
    <ul class="list-unstyled topbar-menu float-end mb-0">
        <li class="dropdown notification-list">
            <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                <i class="dripicons-bell noti-icon"></i>
                <span class="noti-icon-badge" id="unread-count" style="display: none;"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated dropdown-lg">
                <!-- item-->
                <div class="dropdown-item noti-title px-3">
                    <h5 class="m-0">
                        <span class="float-end">
                            <a href="javascript:void(0);" class="text-dark" id="clear-all-notifications">
                                <small>Clear All</small>
                            </a>
                        </span>Notification
                    </h5>
                </div>
                <div class="px-3" style="max-height: 300px;" data-simplebar>
                    <div id="notification-list"></div>
                    <div class="text-center" id="loading-spinner" style="display: none;">
                        <i class="mdi mdi-dots-circle mdi-spin text-muted h3 mt-0"></i>
                    </div>
                </div>
                <!-- All-->
                <a href="notifications.php" class="dropdown-item text-center text-primary notify-item border-top border-light py-2">
                    View All
                </a>
            </div>
        </li>
        <li class="notification-list">
            <a class="nav-link end-bar-toggle" href="javascript:void(0);">
                <i class="dripicons-gear noti-icon"></i>
            </a>
        </li>
        <li class="dropdown notification-list">
            <a class="nav-link dropdown-toggle nav-user arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                <span class="account-user-avatar">
                    <div style="position: relative; display: inline-block;">
                        <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="user-image" class="rounded-circle avatar-lg img-thumbnail">
                    </div>
                </span>
                <span>
                    <span class="account-user-name" id="instructorName">
                        <?php echo htmlspecialchars($userName); ?>
                    </span>
                    <span class="account-position">Instructor</span>
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated topbar-dropdown-menu profile-dropdown">
                <div class="dropdown-header noti-title">
                    <h6 class="text-overflow m-0">Welcome !</h6>
                </div>
                <a href="profile.php" class="dropdown-item notify-item">
                    <i class="mdi mdi-account-circle me-1"></i>
                    <span>My Account</span>
                </a>
                <a href="../backend/signout.php" class="dropdown-item notify-item">
                    <i class="mdi mdi-logout me-1"></i>
                    <span>Sign Out</span>
                </a>
            </div>
        </li>
    </ul>
    <button class="button-menu-mobile open-left">
        <i class="mdi mdi-menu"></i>
    </button>
</div>

<script>
document.addEventListener("DOMContentLoaded", async function() {
    const notificationList = document.getElementById("notification-list");
    const unreadCountBadge = document.getElementById("unread-count");
    const clearAllLink = document.getElementById("clear-all-notifications");
    const loadingSpinner = document.getElementById("loading-spinner");

    // Function to fetch and display notifications
    async function fetchNotifications() {
        try {
            loadingSpinner.style.display = "block";
            const response = await fetch("../backend/get_notifications.php");
            const data = await response.json();

            if (data.success) {
                // Update unread count badge (show/hide only, no number)
                unreadCountBadge.style.display = data.unread_count > 0 ? "inline-block" : "none";

                // Clear existing notifications
                notificationList.innerHTML = "";

                // Group notifications by date
                const groupedNotifications = groupNotificationsByDate(data.notifications);

                // Render notifications
                for (const [date, notifications] of Object.entries(groupedNotifications)) {
                    const dateHeader = document.createElement("h5");
                    dateHeader.className = "text-muted font-13 fw-normal mt-0";
                    dateHeader.textContent = date;
                    notificationList.appendChild(dateHeader);

                    notifications.forEach(notification => {
                        const notificationItem = document.createElement("a");
                        notificationItem.href = "javascript:void(0);";
                        notificationItem.className = `dropdown-item p-0 notify-item card ${notification.is_read ? "read-noti" : "unread-noti"} shadow-none mb-2`;
                        notificationItem.innerHTML = `
                            <div class="card-body">
                                <span class="float-end noti-close-btn text-muted" data-id="${notification.notification_id}">
                                    <i class="mdi mdi-close"></i>
                                </span>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="notify-icon ${getIconClass(notification.type)}">
                                            ${getIconContent(notification.type)}
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 text-truncate ms-2">
                                        <h5 class="noti-item-title fw-semibold font-14">
                                            ${notification.title}
                                            <small class="fw-normal text-muted ms-1">${formatTime(notification.created_at)}</small>
                                        </h5>
                                        <small class="noti-item-subtitle text-muted">${notification.message}</small>
                                    </div>
                                </div>
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

                // Add click event for hide buttons
                document.querySelectorAll(".noti-close-btn").forEach(button => {
                    button.addEventListener("click", async (e) => {
                        e.stopPropagation(); // Prevent triggering parent click
                        const notificationId = button.getAttribute("data-id");
                        await hideNotification(notificationId);
                        fetchNotifications();
                    });
                });
            } else {
                notificationList.innerHTML = '<div class="text-center text-muted">No notifications found</div>';
                unreadCountBadge.style.display = "none";
            }
        } catch (error) {
            console.error("Error fetching notifications:", error);
            notificationList.innerHTML = '<div class="text-center text-muted">Error loading notifications</div>';
            unreadCountBadge.style.display = "none";
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

    // Function to get icon class based on notification type
    function getIconClass(type) {
        switch (type) {
            case "comment": return "bg-primary";
            case "new_user": return "bg-info";
            case "profile": return "";
            default: return "bg-secondary";
        }
    }

    // Function to get icon content based on notification type
    function getIconContent(type) {
        switch (type) {
            case "comment": return '<i class="mdi mdi-comment-account-outline"></i>';
            case "new_user": return '<i class="mdi mdi-account-plus"></i>';
            case "profile": return '<img src="assets/images/users/avatar-2.jpg" class="img-fluid rounded-circle" alt="" />';
            default: return '<i class="mdi mdi-bell"></i>';
        }
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

    // Initial fetch
    fetchNotifications();

    // Poll every 5 seconds
    setInterval(fetchNotifications, 5000);

    // Fetch instructor details
    try {
        const response = await fetch("../backend/instructor/get_instructor_details.php");
        const data = await response.json();
        if (data.status === "success") {
            document.getElementById("instructorName").textContent = data.full_name;
        } else {
            document.getElementById("instructorName").textContent = "Instructor";
        }
    } catch (error) {
        console.error("Error fetching instructor details:", error);
    }

    // Auto-fade success message after 5 seconds
    setTimeout(() => {
        const successAlert = document.querySelector('.verification-success');
        if (successAlert) {
            const bsAlert = new bootstrap.Alert(successAlert);
            bsAlert.close();
        }
    }, 5000);
});
</script>