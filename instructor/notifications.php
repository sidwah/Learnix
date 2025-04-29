
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
                                            <div class="col-md-4 mb-3">
                                                <label for="typeFilter" class="form-label">Type</label>
                                                <select class="form-select" id="typeFilter" aria-label="Filter by type">
                                                    <option value="all">All Types</option>
                                                    <option value="course">Course</option>
                                                    <option value="student">Student</option>
                                                    <option value="system">System</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="statusFilter" class="form-label">Status</label>
                                                <select class="form-select" id="statusFilter" aria-label="Filter by status">
                                                    <option value="all">All Statuses</option>
                                                    <option value="unread">Unread</option>
                                                    <option value="read">Read</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3 d-flex align-items-end">
                                                <button type="button" class="btn btn-primary w-100" onclick="filterNotifications()">Apply Filters</button>
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
                                    <div id="notificationList">
                                        <!-- Dynamically populated -->
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
    <!-- Custom JS for Notification Simulation -->
    <script>
        // Mock data for notifications
        const notifications = [
            {
                id: 1,
                message: 'New enrollment in "Intro to Programming" by John Doe.',
                type: 'course',
                timestamp: '2025-04-29 10:30',
                status: 'unread'
            },
            {
                id: 2,
                message: 'Student Jane Smith submitted a quiz in "Data Science".',
                type: 'student',
                timestamp: '2025-04-28 15:45',
                status: 'unread'
            },
            {
                id: 3,
                message: 'System update: New analytics features available.',
                type: 'system',
                timestamp: '2025-04-27 09:00',
                status: 'read'
            },
            {
                id: 4,
                message: '"Intro to Programming" course approved for publication.',
                type: 'course',
                timestamp: '2025-04-26 12:15',
                status: 'read'
            },
            {
                id: 5,
                message: 'Student feedback received for "Data Science".',
                type: 'student',
                timestamp: '2025-04-25 14:20',
                status: 'unread'
            }
        ];

        function renderNotifications(filteredNotifications) {
            const notificationList = document.querySelector('#notificationList');
            notificationList.innerHTML = '';
            if (filteredNotifications.length === 0) {
                notificationList.innerHTML = '<p class="text-muted">No notifications found.</p>';
                return;
            }
            filteredNotifications.forEach(notification => {
                const notificationItem = document.createElement('div');
                notificationItem.className = `notification-item ${notification.status}`;
                notificationItem.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-0">${notification.message}</p>
                            <small class="text-muted">Type: ${notification.type.charAt(0).toUpperCase() + notification.type.slice(1)} | ${notification.timestamp}</small>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-outline-primary action-btn me-2" onclick="toggleReadStatus(${notification.id})">
                                ${notification.status === 'unread' ? 'Mark as Read' : 'Mark as Unread'}
                            </button>
                            <button class="btn btn-sm btn-outline-danger action-btn" onclick="deleteNotification(${notification.id})">Delete</button>
                        </div>
                    </div>
                `;
                notificationList.appendChild(notificationItem);
            });
        }

        function filterNotifications() {
            const typeFilter = document.querySelector('#typeFilter').value;
            const statusFilter = document.querySelector('#statusFilter').value;

            const filteredNotifications = notifications.filter(notification => {
                const matchesType = typeFilter === 'all' || notification.type === typeFilter;
                const matchesStatus = statusFilter === 'all' || notification.status === statusFilter;
                return matchesType && matchesStatus;
            });

            renderNotifications(filteredNotifications);
        }

        function toggleReadStatus(id) {
            const notification = notifications.find(n => n.id === id);
            if (notification) {
                notification.status = notification.status === 'unread' ? 'read' : 'unread';
                filterNotifications();
            }
        }

        function deleteNotification(id) {
            const index = notifications.findIndex(n => n.id === id);
            if (index !== -1) {
                notifications.splice(index, 1);
                filterNotifications();
            }
        }

        function markAllRead() {
            notifications.forEach(notification => {
                notification.status = 'read';
            });
            filterNotifications();
        }

        function clearAllNotifications() {
            notifications.length = 0;
            renderNotifications(notifications);
        }

        // Initial render
        renderNotifications(notifications);
    </script>
</body>
</html>
