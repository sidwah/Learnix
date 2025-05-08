<nav
    class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar">
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
                    <span class="notification-dot" id="notification-dot"></span>
                </a>
                <!-- Dropdown menu -->
                <ul class="dropdown-menu dropdown-menu-end notification-dropdown" id="notification-dropdown">
                    <li class="dropdown-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0" style="font-size: 0.85rem;">Notifications</h6>
                        <span class="badge bg-primary rounded-pill" id="notification-count">3</span>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <!-- Static notification examples -->
                    <div id="notification-list">
                        <li class="notification-item" data-id="1">
                            <a href="instructors-verification.php" class="dropdown-item notification-link">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar">
                                            <span class="avatar-initial rounded-circle bg-label-success">
                                                <i class="bx bx-user-check"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">New Instructor Verification</h6>
                                        <p class="mb-0" style="font-size: 0.85rem;">Dr. Sarah Johnson submitted verification documents</p>
                                        <small class="text-muted" style="font-size: 0.65rem;">30 minutes ago</small>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <div class="dropdown-divider"></div>
                        </li>
                        <li class="notification-item" data-id="2">
                            <a href="courses-pending.php" class="dropdown-item notification-link">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar">
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                <i class="bx bx-book-content"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">Course Awaiting Approval</h6>
                                        <p class="mb-0" style="font-size: 0.85rem;">Advanced Machine Learning submitted for review</p>
                                        <small class="text-muted" style="font-size: 0.65rem;">2 hours ago</small>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <div class="dropdown-divider"></div>
                        </li>
                        <li class="notification-item" data-id="3">
                            <a href="reports-overview.php" class="dropdown-item notification-link">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar">
                                            <span class="avatar-initial rounded-circle bg-label-warning">
                                                <i class="bx bx-error"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">System Alert</h6>
                                        <p class="mb-0" style="font-size: 0.85rem;">Storage usage at 85% capacity</p>
                                        <small class="text-muted" style="font-size: 0.65rem;">1 day ago</small>
                                    </div>
                                </div>
                            </a>
                        </li>
                    </div>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li class="dropdown-footer">
                        <a href="notifications.php" class="dropdown-item d-flex justify-content-center">
                            View All Notifications
                        </a>
                    </li>
                </ul>
            </li>

            <!-- User -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="../uploads/admin-avatar/default.png" alt class="w-px-40 h-auto rounded-circle" />
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <img src="../uploads/admin-avatar/default.png" alt class="w-px-40 h-auto rounded-circle" />
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
                        <a class="dropdown-item" href="settings-general.php">
                            <i class="bx bx-cog me-2"></i>
                            <span class="align-middle">Settings</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="reports.php">
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
            display: block;
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

        @media (max-width: 576px) {
            .notification-dropdown {
                width: 320px;
            }
        }
    </style>

    <!-- Add simplified JavaScript for notifications UI -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // For demo purposes, notification bell already shows a dot
            const notificationDot = document.getElementById('notification-dot');
            notificationDot.style.display = 'block';

            // Add click handlers to mark notifications as read
            document.querySelectorAll('.notification-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    const notificationItem = this.closest('.notification-item');
                    if (notificationItem) {
                        notificationItem.classList.add('read');
                    }
                });
            });
        });
    </script>