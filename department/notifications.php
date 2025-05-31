<?php
include '../includes/department/header.php';
require '../backend/session_start.php'; // Ensure session is started
require '../backend/config.php'; // Ensure database connection

// Check if the user is signed in and has the appropriate role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || !isset($_SESSION['department_id']) || !isset($_SESSION['role']) || !in_array($_SESSION['role'], ['department_head', 'department_secretary'])) {
    error_log("Unauthorized access attempt detected: " . json_encode($_SERVER));
    header('Location: signin.php');
    exit;
}
?>

<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main">
    <!-- Navbar -->
    <nav class="js-nav-scroller navbar navbar-expand-lg navbar-sidebar navbar-vertical navbar-light bg-white border-end" data-hs-nav-scroller-options='{
            "type": "vertical",
            "target": ".navbar-nav .active",
            "offset": 80
           }'>
        <?php include '../includes/department/sidebar.php'; ?>
    </nav>
    <!-- End Navbar -->

    <!-- Content -->
    <div class="navbar-sidebar-aside-content content-space-1 content-space-md-2 px-lg-5 px-xl-5">
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex justify-content-between align-items-center">
                    <h4 class="page-title mb-0">Notifications</h4>
                    <div>
                        <button class="btn btn-primary btn-sm me-2" onclick="markAllRead()">Mark All as Read</button>
                        <button class="btn btn-outline-danger btn-sm" onclick="clearAllNotifications()">Clear All</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page Title -->

        <!-- Error Alert -->
        <div id="errorAlert" class="alert alert-danger alert-dismissible fade show d-none" role="alert">
            <span id="errorMessage"></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <!-- Filters -->
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Filter Notifications</h5>
                        <form id="filterNotifications">
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
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Your Notifications</h5>
                            <div id="notificationCount" class="text-muted small"></div>
                        </div>
                        <div id="notificationList"></div>
                        <div class="text-center" id="loading-spinner" style="display: none;">
                            <i class="bi-arrow-clockwise fs-4 animate-spin"></i>
                        </div>
                        
                        <!-- Pagination -->
                        <nav aria-label="Notifications pagination" id="paginationContainer" class="mt-4" style="display: none;">
                            <ul class="pagination justify-content-center" id="paginationList">
                                <!-- Pagination items will be inserted here -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actionable Insights -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card bg-soft-light">
                    <div class="card-body">
                        <h5 class="card-title">Actionable Insights</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="bi-reply me-2"></i> Review recent course approvals to ensure timely updates.</li>
                            <li class="mb-2"><i class="bi-person-plus me-2"></i> Monitor new instructor registrations for department alignment.</li>
                            <li><i class="bi-bell me-2"></i> Clear old notifications to stay organized.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Content -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<?php include '../includes/department/footer.php'; ?>

<!-- Custom CSS for Notifications -->
<style>
    .notification-item {
        padding: 15px;
        border-bottom: 1px solid #e9ecef;
        transition: background-color 0.3s;
    }
    .notification-item.unread {
        background-color: #e9ecef;
        font-weight: 600;
        border-left: 4px solid #007bff;
    }
    .notification-item.hidden {
        background-color: #dee2e6;
        opacity: 0.7;
        font-style: italic;
    }
    .notification-item:hover {
        background-color: #f1f3f5;
    }
    .notification-item h6 {
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
    
    /* Pagination styles */
    .pagination .page-link {
        color: #007bff;
        border: 1px solid #dee2e6;
    }
    .pagination .page-item.active .page-link {
        background-color: #007bff;
        border-color: #007bff;
        color: white;
    }
    .pagination .page-link:hover {
        background-color: #e9ecef;
        border-color: #dee2e6;
    }
    .pagination .page-item.disabled .page-link {
        color: #6c757d;
        background-color: #fff;
        border-color: #dee2e6;
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
    .dark-mode-preload .notification-icon.alert-soft-primary,
    .dark-mode-preload .notification-icon.alert-soft-success,
    .dark-mode-preload .notification-icon.alert-soft-warning,
    .dark-mode-preload .notification-icon.alert-soft-danger {
        color: #e9ecef !important;
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
    .dark-mode-preload .pagination .page-link {
        background-color: #253545 !important;
        border-color: #495057 !important;
        color: #e9ecef !important;
    }
    .dark-mode-preload .pagination .page-item.active .page-link {
        background-color: #4dabf7 !important;
        border-color: #4dabf7 !important;
    }
</style>

<!-- Custom JS for Notifications -->
<script>
    // Global variables and functions
    const typeIcons = {
        course: "bi-book",
        student: "bi-person",
        system: "bi-gear",
        comment: "bi-chat-left-text",
        new_user: "bi-person-plus",
        profile: "bi-person-circle"
    };

    const alertClasses = [
        "alert-soft-primary",
        "alert-soft-success",
        "alert-soft-warning",
        "alert-soft-danger"
    ];

    // Pagination variables
    let currentPage = 1;
    const itemsPerPage = 15;
    let totalNotifications = 0;
    let allNotifications = [];

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
        return created.toLocaleDateString();
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

    function updateNotificationCount() {
        const countElement = document.getElementById('notificationCount');
        if (countElement) {
            const startItem = ((currentPage - 1) * itemsPerPage) + 1;
            const endItem = Math.min(currentPage * itemsPerPage, totalNotifications);
            countElement.textContent = totalNotifications > 0 
                ? `Showing ${startItem}-${endItem} of ${totalNotifications} notifications`
                : 'No notifications found';
        }
    }

    function createPagination() {
        const totalPages = Math.ceil(totalNotifications / itemsPerPage);
        const paginationContainer = document.getElementById('paginationContainer');
        const paginationList = document.getElementById('paginationList');
        
        if (totalPages <= 1) {
            paginationContainer.style.display = 'none';
            return;
        }

        paginationContainer.style.display = 'block';
        paginationList.innerHTML = '';

        // Previous button
        const prevItem = document.createElement('li');
        prevItem.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevItem.innerHTML = `
            <a class="page-link" href="#" onclick="changePage(${currentPage - 1}); return false;">
                <i class="bi-chevron-left"></i>
            </a>
        `;
        paginationList.appendChild(prevItem);

        // Page numbers
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);

        // First page and ellipsis
        if (startPage > 1) {
            const firstItem = document.createElement('li');
            firstItem.className = 'page-item';
            firstItem.innerHTML = `<a class="page-link" href="#" onclick="changePage(1); return false;">1</a>`;
            paginationList.appendChild(firstItem);

            if (startPage > 2) {
                const ellipsisItem = document.createElement('li');
                ellipsisItem.className = 'page-item disabled';
                ellipsisItem.innerHTML = '<span class="page-link">...</span>';
                paginationList.appendChild(ellipsisItem);
            }
        }

        // Page numbers around current page
        for (let i = startPage; i <= endPage; i++) {
            const pageItem = document.createElement('li');
            pageItem.className = `page-item ${i === currentPage ? 'active' : ''}`;
            pageItem.innerHTML = `<a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>`;
            paginationList.appendChild(pageItem);
        }

        // Last page and ellipsis
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                const ellipsisItem = document.createElement('li');
                ellipsisItem.className = 'page-item disabled';
                ellipsisItem.innerHTML = '<span class="page-link">...</span>';
                paginationList.appendChild(ellipsisItem);
            }

            const lastItem = document.createElement('li');
            lastItem.className = 'page-item';
            lastItem.innerHTML = `<a class="page-link" href="#" onclick="changePage(${totalPages}); return false;">${totalPages}</a>`;
            paginationList.appendChild(lastItem);
        }

        // Next button
        const nextItem = document.createElement('li');
        nextItem.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        nextItem.innerHTML = `
            <a class="page-link" href="#" onclick="changePage(${currentPage + 1}); return false;">
                <i class="bi-chevron-right"></i>
            </a>
        `;
        paginationList.appendChild(nextItem);
    }

    function changePage(page) {
        const totalPages = Math.ceil(totalNotifications / itemsPerPage);
        if (page < 1 || page > totalPages) return;
        
        currentPage = page;
        renderCurrentPage();
        createPagination();
        updateNotificationCount();
        
        // Scroll to top of notifications
        document.getElementById('notificationList').scrollIntoView({ behavior: 'smooth' });
    }

    function renderCurrentPage() {
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const pageNotifications = allNotifications.slice(startIndex, endIndex);
        
        const notificationList = document.getElementById('notificationList');
        renderNotifications(notificationList, pageNotifications);
    }

    async function fetchNotifications(notificationList, loadingSpinner, typeFilter, statusFilter) {
        try {
            loadingSpinner.style.display = "block";
            const type = typeFilter.value;
            const status = statusFilter.value;
            const response = await fetch(`../backend/filter_notifications.php?type=${type}&status=${status}`);
            const data = await response.json();

            if (data.success) {
                allNotifications = data.notifications || [];
                totalNotifications = allNotifications.length;
                currentPage = 1; // Reset to first page when filtering
                
                renderCurrentPage();
                createPagination();
                updateNotificationCount();
            } else {
                allNotifications = [];
                totalNotifications = 0;
                notificationList.innerHTML = '<div class="text-center text-muted p-3">No notifications found.</div>';
                document.getElementById('paginationContainer').style.display = 'none';
                updateNotificationCount();
            }
        } catch (error) {
            console.error("Error fetching notifications:", error);
            allNotifications = [];
            totalNotifications = 0;
            notificationList.innerHTML = '<div class="text-center text-muted p-3">Error loading notifications.</div>';
            document.getElementById('paginationContainer').style.display = 'none';
            showError('Failed to load notifications.');
            updateNotificationCount();
        } finally {
            loadingSpinner.style.display = "none";
        }
    }

    function renderNotifications(notificationList, notifications) {
        notificationList.innerHTML = '';
        if (!notifications || notifications.length === 0) {
            notificationList.innerHTML = '<div class="text-center text-muted p-3">No notifications found.</div>';
            return;
        }
        notifications.forEach(notification => {
            const iconClass = typeIcons[notification.type] || "bi-bell";
            const alertClass = getRandomAlertClass();
            const readIcon = notification.is_read ? 'bi-envelope' : 'bi-envelope-open';
            const readColor = notification.is_read ? 'text-success' : 'text-warning';
            const notificationItem = document.createElement('div');
            notificationItem.className = `notification-item ${notification.is_hidden ? 'hidden' : notification.is_read ? 'read' : 'unread'}`;
            // Only include toggle icon for unread notifications
            const toggleIcon = notification.is_read ? '' : `
                <i class="bi-read-toggle ${readIcon} ${readColor} fs-5 action-icon me-2" 
                   data-bs-toggle="tooltip" 
                   data-bs-title="Mark as Read" 
                   onclick="toggleReadStatus(${notification.notification_id})"></i>`;
            notificationItem.innerHTML = `
                <div class="d-flex align-items-start">
                    <span class="notification-icon ${alertClass}">
                        <i class="${iconClass} fs-5"></i>
                    </span>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">${notification.title}</h6>
                        <p class="mb-1 text-muted">${notification.message}</p>
                        <small class="text-muted small-text">Type: ${notification.type.charAt(0).toUpperCase() + notification.type.slice(1)} | ${formatTime(notification.created_at)}</small>
                    </div>
                    <div class="ms-3 d-flex align-items-center">
                        ${toggleIcon}
                        <i class="bi-archive-action bi-archive text-danger fs-5 action-icon" 
                           data-bs-toggle="tooltip" 
                           data-bs-title="${notification.is_hidden ? 'Restore' : 'Archive'}" 
                           onclick="hideNotification(${notification.notification_id})"></i>
                    </div>
                </div>
            `;
            notificationList.appendChild(notificationItem);
        });

        // Reinitialize tooltips after rendering
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(tooltipTriggerEl => {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
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
                const loadingSpinner = document.getElementById("loading-spinner");
                const typeFilter = document.getElementById("typeFilter");
                const statusFilter = document.getElementById("statusFilter");
                if (notificationList && loadingSpinner && typeFilter && statusFilter) {
                    fetchNotifications(notificationList, loadingSpinner, typeFilter, statusFilter);
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
                const loadingSpinner = document.getElementById("loading-spinner");
                const typeFilter = document.getElementById("typeFilter");
                const statusFilter = document.getElementById("statusFilter");
                if (notificationList && loadingSpinner && typeFilter && statusFilter) {
                    fetchNotifications(notificationList, loadingSpinner, typeFilter, statusFilter);
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
                const loadingSpinner = document.getElementById("loading-spinner");
                const typeFilter = document.getElementById("typeFilter");
                const statusFilter = document.getElementById("statusFilter");
                if (notificationList && loadingSpinner && typeFilter && statusFilter) {
                    fetchNotifications(notificationList, loadingSpinner, typeFilter, statusFilter);
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
            // Get all visible notifications from the current data
            const visibleNotifications = allNotifications.filter(n => !n.is_hidden);
            
            for (const notification of visibleNotifications) {
                const response = await fetch(`../backend/hide_notification.php?id=${notification.notification_id}`);
                const data = await response.json();
                if (!data.success) {
                    console.error(`Failed to hide notification ${notification.notification_id}:`, data.error);
                }
            }
            
            // Refresh the notifications list
            const notificationList = document.getElementById("notificationList");
            const loadingSpinner = document.getElementById("loading-spinner");
            const typeFilter = document.getElementById("typeFilter");
            const statusFilter = document.getElementById("statusFilter");
            if (notificationList && loadingSpinner && typeFilter && statusFilter) {
                fetchNotifications(notificationList, loadingSpinner, typeFilter, statusFilter);
            }
        } catch (error) {
            console.error("Error clearing notifications:", error);
            showError('Failed to clear notifications due to a network error.');
        }
    }

    // Main event listener
    document.addEventListener("DOMContentLoaded", function() {
        // Ensure DOM elements exist
        const notificationList = document.getElementById("notificationList");
        const loadingSpinner = document.getElementById("loading-spinner");
        const typeFilter = document.getElementById("typeFilter");
        const statusFilter = document.getElementById("statusFilter");

        if (!notificationList || !loadingSpinner || !typeFilter || !statusFilter) {
            console.error("Required DOM elements not found");
            showError('Page initialization failed due to missing elements.');
            return;
        }

        // Initialize Bootstrap tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(tooltipTriggerEl => {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Add event listeners for immediate filter application
        typeFilter.addEventListener("change", () => fetchNotifications(notificationList, loadingSpinner, typeFilter, statusFilter));
        statusFilter.addEventListener("change", () => fetchNotifications(notificationList, loadingSpinner, typeFilter, statusFilter));

        // Initial fetch
        fetchNotifications(notificationList, loadingSpinner, typeFilter, statusFilter);

        // Poll every 5 seconds
        setInterval(() => fetchNotifications(notificationList, loadingSpinner, typeFilter, statusFilter), 5000);
    });
</script>