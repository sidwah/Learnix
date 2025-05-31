<?php
require '../backend/session_start.php'; // Ensure session is started

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    error_log("Unauthorized access attempt detected: " . json_encode($_SERVER));
    header('Location: landing.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Instructor | Learnix - Notifications</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="View and manage notifications related to your courses, students, and system updates." />
    <meta name="author" content="Learnix Team" />
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <!-- Third-party CSS -->
    <link href="assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <!-- App CSS -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style"/>
    <!-- Custom CSS for Notifications -->
    <style>
        .notification-card { 
            border-radius: 10px; 
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); 
            transition: transform 0.3s ease-in-out; 
        }
        .notification-card:hover { 
            transform: translateY(-5px); 
        }
        .notification-item { 
            border-bottom: 1px solid #e9ecef; 
            padding: 15px; 
            transition: background-color 0.3s; 
        }
        .notification-item.unread { 
            background-color: #f8f9fa; 
            font-weight: 500; 
        }
        .notification-item.hidden { 
            background-color: #e9ecef; 
            opacity: 0.7; 
            font-style: italic; 
        }
        .notification-item:hover { 
            background-color: #f1f3f5; 
        }
        .action-btn { 
            border-radius: 20px; 
            padding: 6px 12px; 
            font-size: 14px; 
        }
        .form-select { 
            border-radius: 8px; 
        }
        .insights-list li { 
            margin-bottom: 10px; 
        }
        @media (max-width: 768px) {
            .notification-card { 
                margin-bottom: 20px; 
            }
            .action-btn { 
                width: 100%; 
                margin-bottom: 10px; 
            }
        }
    </style>
</head>

<body class="loading" data-layout-color="light" data-leftbar-theme="dark" data-layout-mode="fluid" data-rightbar-onstart="true">
    <div class="wrapper">
        <!-- Sidebar -->
        <?php include '../includes/instructor-sidebar.php'; ?>
        <!-- End Sidebar -->

        <div class="content-page">
            <div class="content">
                <!-- Topbar -->
                <?php include '../includes/instructor-topnavbar.php'; ?>
                <!-- End Topbar -->

                <!-- Start Content -->
                <div class="container-fluid">
                    <!-- Page Title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <button class="btn btn-primary action-btn" onclick="markAllRead()">Mark All as Read</button>
                                    <button class="btn btn-outline-danger action-btn ms-2" onclick="clearAllNotifications()">Clear All</button>
                                </div>
                                <h4 class="page-title">Notifications</h4>
                            </div>
                        </div>
                    </div>
                    <!-- End Page Title -->

                    <!-- Filters -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card notification-card">
                                <div class="card-body">
                                    <h5 class="card-title">Filter Notifications</h5>
                                    <form id="filterForm">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="typeFilter" class="form-label">Type</label>
                                                <select class="form-select" id="typeFilter" aria-label="Filter by type">
                                                    <option value="all">All Types</option>
                                                    <option value="course">Course</option>
                                                    <option value="student">Student</option>
                                                    <option value="system">System</option>
                                                    <option value="comment">Comment</option>
                                                    <option value="new_user">New User</option>
                                                    <option value="profile">Profile</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="statusFilter" class="form-label">Status</label>
                                                <select class="form-select" id="statusFilter" aria-label="Filter by status">
                                                    <option value="all">All Statuses</option>
                                                    <option value="unread">Unread</option>
                                                    <option value="read">Read</option>
                                                    <option value="hidden">Hidden</option>
                                                </select>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notification List -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card notification-card">
                                <div class="card-body">
                                    <h5 class="card-title">Your Notifications</h5>
                                    <div id="notificationList"></div>
                                    <div class="text-center" id="loading-spinner" style="display: none;">
                                        <i class="mdi mdi-dots-circle mdi-spin text-muted h3 mt-0"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actionable Insights -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card notification-card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Actionable Insights</h5>
                                    <ul class="list-unstyled insights-list">
                                        <li><i class="mdi mdi-reply me-2"></i> Respond to recent student submissions to boost engagement.</li>
                                        <li><i class="mdi mdi-account-plus me-2"></i> Review new enrollments to tailor course content.</li>
                                        <li><i class="mdi mdi-bell-check me-2"></i> Clear old notifications to stay organized.</li>
                                    </ul>
                                    <a href="#" class="btn btn-light mt-3">View All Insights</a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- End Container -->

            </div>
            <!-- End Content -->

            <!-- Footer -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                            © Learnix. <script>document.write(new Date().getFullYear())</script> All rights reserved.
                        </div>
                    </div>
                </div>
            </footer>
            <!-- End Footer -->
        </div>
        <!-- End Content Page -->
    </div>
    <!-- End Wrapper -->

    <!-- Dark Mode -->
    <?php include '../includes/instructor-darkmode.php'; ?>

    <!-- Scripts -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.min.js"></script>
    <script src="assets/js/vendor/jquery-jvectormap-1.2.2.min.js"></script>
    <script src="assets/js/vendor/jquery-jvectormap-world-mill-en.js"></script>
    <!-- Custom JS for Notifications -->
    <script>
        document.addEventListener("DOMContentLoaded", async function() {
            const notificationList = document.getElementById("notificationList");
            const loadingSpinner = document.getElementById("loading-spinner");
            const typeFilter = document.getElementById("typeFilter");
            const statusFilter = document.getElementById("statusFilter");

            // Function to fetch and display notifications
            async function fetchNotifications() {
                try {
                    loadingSpinner.style.display = "block";
                    const type = typeFilter.value;
                    const status = statusFilter.value;
                    const response = await fetch(`../backend/filter_notifications.php?type=${type}&status=${status}`);
                    const data = await response.json();

                    if (data.success) {
                        renderNotifications(data.notifications);
                    } else {
                        notificationList.innerHTML = '<p class="text-muted">No notifications found.</p>';
                    }
                } catch (error) {
                    console.error("Error fetching notifications:", error);
                    notificationList.innerHTML = '<p class="text-muted">Error loading notifications.</p>';
                } finally {
                    loadingSpinner.style.display = "none";
                }
            }

            // Function to render notifications
            function renderNotifications(notifications) {
                notificationList.innerHTML = '';
                if (notifications.length === 0) {
                    notificationList.innerHTML = '<p class="text-muted">No notifications found.</p>';
                    return;
                }
                notifications.forEach(notification => {
                    const notificationItem = document.createElement('div');
                    notificationItem.className = `notification-item ${notification.is_hidden ? 'hidden' : notification.is_read ? 'read' : 'unread'}`;
                    notificationItem.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-0">${notification.title}</p>
                                <small class="text-muted">Type: ${notification.type.charAt(0).toUpperCase() + notification.type.slice(1)} | ${formatTime(notification.created_at)}</small>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-outline-primary action-btn me-2" onclick="toggleReadStatus(${notification.notification_id})">
                                    ${notification.is_read ? 'Mark as Unread' : 'Mark as Read'}
                                </button>
                                <button class="btn btn-sm btn-outline-danger action-btn" onclick="hideNotification(${notification.notification_id})">
                                    ${notification.is_hidden ? 'Unhide' : 'Delete'}
                                </button>
                            </div>
                        </div>
                    `;
                    notificationList.appendChild(notificationItem);
                });
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

            // Function to toggle read status
            async function toggleReadStatus(notificationId) {
                try {
                    const response = await fetch(`../backend/mark_notification_read.php?id=${notificationId}`);
                    const data = await response.json();
                    if (data.success) {
                        fetchNotifications();
                    } else {
                        console.error("Error toggling read status:", data.error);
                    }
                } catch (error) {
                    console.error("Error toggling read status:", error);
                }
            }

            // Function to hide or unhide a notification
            async function hideNotification(notificationId) {
                try {
                    const response = await fetch(`../backend/hide_notification.php?id=${notificationId}`);
                    const data = await response.json();
                    if (data.success) {
                        fetchNotifications();
                    } else {
                        console.error("Error hiding/unhiding notification:", data.error);
                    }
                } catch (error) {
                    console.error("Error hiding/unhiding notification:", error);
                }
            }

            // Function to mark all notifications as read
            async function markAllRead() {
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

            // Function to clear all notifications (hide them)
            async function clearAllNotifications() {
                try {
                    const notifications = document.querySelectorAll('.notification-item:not(.hidden)');
                    for (const notification of notifications) {
                        const notificationId = notification.querySelector('.btn-outline-danger').getAttribute('onclick').match(/\d+/)[0];
                        await hideNotification(notificationId);
                    }
                    fetchNotifications();
                } catch (error) {
                    console.error("Error clearing all notifications:", error);
                }
            }

            // Add event listeners for immediate filter application
            typeFilter.addEventListener("change", fetchNotifications);
            statusFilter.addEventListener("change", fetchNotifications);

            // Initial fetch
            fetchNotifications();

            // Poll every 5 seconds
            setInterval(fetchNotifications, 5000);
        });
    </script>
</body>
</html>