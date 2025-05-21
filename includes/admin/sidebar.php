<?php

/**
 * Admin Sidebar Component for Learnix LMS - Fixed version with Finance section
 * 
 * Usage: include 'includes/admin/sidebar.php';
 */

// Get current page to determine active state
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="index.php" class="app-brand-link">
            <span class="app-brand-logo demo">
                <img src="assets/img/logo.png" alt="Learnix" width="30">
            </span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2">Learnix</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1" id="sidebar-menu">
        <!-- Dashboard -->
        <li class="menu-item <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>" data-menu-slug="index.php">
            <a href="index.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Analytics">Dashboard</div>
            </a>
        </li>

        <!-- Department Management -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Department Management</span>
        </li>
        <li class="menu-item <?php echo (strpos($currentPage, 'department') !== false) ? 'active open' : ''; ?>" data-menu-slug="departments">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-building"></i>
                <div data-i18n="Departments">Departments</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item <?php echo ($currentPage == 'departments-list.php') ? 'active' : ''; ?>" data-menu-slug="departments-list.php">
                    <a href="departments-list.php" class="menu-link">
                        <div data-i18n="View All">View All</div>
                    </a>
                </li>
                <li class="menu-item <?php echo ($currentPage == 'department-staff.php') ? 'active' : ''; ?>" data-menu-slug="department-staff.php">
                    <a href="department-staff.php" class="menu-link">
                        <div data-i18n="Department Staff">Department Staff</div>
                    </a>
                </li>
                <!--  <li class="menu-item <?php //echo ($currentPage == 'departments-staff.php') ? 'active' : ''; 
                                            ?>" data-menu-slug="departments-staff.php">
                    <a href="departments-staff.php" class="menu-link">
                        <div data-i18n="Department Staff">Department Staff</div>
                    </a>
                </li> -->
            </ul>
        </li>

        <!-- User Management -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">User Management</span>
        </li>
        <li class="menu-item <?php echo (strpos($currentPage, 'instructors') !== false) ? 'active open' : ''; ?>">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-user-voice"></i>
                <div data-i18n="Instructors">Instructors</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item <?php echo ($currentPage == 'instructors-list.php') ? 'active' : ''; ?>">
                    <a href="instructors-list.php" class="menu-link">
                        <div data-i18n="View All">View All</div>
                    </a>
                </li>
                <!-- <li class="menu-item <?php //echo ($currentPage == 'instructors-invite.php') ? 'active' : ''; 
                                            ?>">
                    <a href="instructors-invite.php" class="menu-link">
                        <div data-i18n="Invite New">Invite New</div>
                    </a>
                </li> -->
            </ul>
        </li>

        <li class="menu-item <?php echo (strpos($currentPage, 'students') !== false) ? 'active open' : ''; ?>">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-user"></i>
                <div data-i18n="Students">Students</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item <?php echo ($currentPage == 'students-list.php') ? 'active' : ''; ?>">
                    <a href="students-list.php" class="menu-link">
                        <div data-i18n="View All">View All</div>
                    </a>
                </li>
                <li class="menu-item <?php echo ($currentPage == 'students-progress.php') ? 'active' : ''; ?>">
                    <a href="students-progress.php" class="menu-link">
                        <div data-i18n="Progress Tracking">Progress Tracking</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Course Management -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Course Management</span>
        </li>
        <li class="menu-item <?php echo (strpos($currentPage, 'courses') !== false) ? 'active open' : ''; ?>">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-book-open"></i>
                <div data-i18n="Courses">Courses</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item <?php echo ($currentPage == 'courses-list.php') ? 'active' : ''; ?>">
                    <a href="courses-list.php" class="menu-link">
                        <div data-i18n="All Courses">All Courses</div>
                    </a>
                </li>
                <!-- <li class="menu-item <?php //echo ($currentPage == 'courses-performance.php') ? 'active' : ''; 
                                            ?>">
                    <a href="courses-performance.php" class="menu-link">
                        <div data-i18n="Performance">Performance</div>
                    </a>
                </li> -->
                <li class="menu-item <?php echo ($currentPage == 'course-categories.php') ? 'active' : ''; ?>">
                    <a href="course-categories.php" class="menu-link">
                        <div data-i18n="Categories & Subcategories">Categories & Subcategories</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Analytics & Reports -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Analytics & Reports</span>
        </li>
        <li class="menu-item <?php echo (strpos($currentPage, 'reports') !== false) ? 'active open' : ''; ?>">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-line-chart"></i>
                <div data-i18n="Reports">Reports</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item <?php echo ($currentPage == 'reports-overview.php') ? 'active' : ''; ?>">
                    <a href="reports-overview.php" class="menu-link">
                        <div data-i18n="Overview">Overview</div>
                    </a>
                </li>
                <li class="menu-item <?php echo ($currentPage == 'reports-students.php') ? 'active' : ''; ?>">
                    <a href="reports-students.php" class="menu-link">
                        <div data-i18n="Student Reports">Students Reports</div>
                    </a>
                </li>
                <li class="menu-item <?php echo ($currentPage == 'reports-instructors.php') ? 'active' : ''; ?>">
                    <a href="reports-instructors.php" class="menu-link">
                        <div data-i18n="Instructors Reports">Instructors Reports</div>
                    </a>
                </li>
                <li class="menu-item <?php echo ($currentPage == 'reports-departments.php') ? 'active' : ''; ?>">
                    <a href="reports-departments.php" class="menu-link">
                        <div data-i18n="Departments Reports">Departments Reports</div>
                    </a>
                </li>
                <li class="menu-item <?php echo ($currentPage == 'reports-revenue.php') ? 'active' : ''; ?>">
                    <a href="reports-revenue.php" class="menu-link">
                        <div data-i18n="Revenue Reports">Revenue Reports</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Finance Management -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Finance Management</span>
        </li>
        <li class="menu-item <?php echo (strpos($currentPage, 'finance') !== false) ? 'active open' : ''; ?>">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <span class="menu-icon" style="font-weight: bold;">₵</span>
                <div data-i18n="Finance">Finance</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item <?php echo ($currentPage == 'finance-transactions.php') ? 'active' : ''; ?>" data-menu-slug="finance-transactions.php">
                    <a href="finance-transactions.php" class="menu-link">
                        <div data-i18n="Transactions">Transactions</div>
                    </a>
                </li>
                <!-- <li class="menu-item <?php // echo ($currentPage == 'finance-refunds.php') ? 'active' : ''; 
                                            ?>" data-menu-slug="finance-refunds.php">
                    <a href="finance-refunds.php" class="menu-link">
                        <div data-i18n="Refunds">Refunds</div>
                    </a>
                </li> -->
                <li class="menu-item <?php echo ($currentPage == 'finance-reports.php') ? 'active' : ''; ?>" data-menu-slug="finance-reports.php">
                    <a href="finance-reports.php" class="menu-link">
                        <div data-i18n="Financial Reports">Financial Reports</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- System Settings -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">System Settings</span>
        </li>
        <li class="menu-item <?php echo (strpos($currentPage, 'settings') !== false) ? 'active open' : ''; ?>">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-cog"></i>
                <div data-i18n="Settings">Settings</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item <?php echo ($currentPage == 'settings-general.php') ? 'active' : ''; ?>">
                    <a href="settings-general.php" class="menu-link">
                        <div data-i18n="General">General</div>
                    </a>
                </li>
                <li class="menu-item <?php echo ($currentPage == 'settings-email.php') ? 'active' : ''; ?>">
                    <a href="settings-email.php" class="menu-link">
                        <div data-i18n="Email">Email</div>
                    </a>
                </li>
                <li class="menu-item <?php echo ($currentPage == 'payment-settings.php') ? 'active' : ''; ?>">
                    <a href="payment-settings.php" class="menu-link">
                        <div data-i18n="Payment">Payment</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Help & Support -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Help & Support</span>
        </li>
        <li class="menu-item <?php echo ($currentPage == 'documentation.php') ? 'active' : ''; ?>">
            <a href="documentation.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-file"></i>
                <div data-i18n="Documentation">Documentation</div>
            </a>
        </li>
        <li class="menu-item <?php echo ($currentPage == 'support.php') ? 'active' : ''; ?>">
            <a href="support.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-support"></i>
                <div data-i18n="Support">Technical Support</div>
            </a>
        </li>
    </ul>
</aside>
<!-- Add the dynamic sidebar functionality -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const menuItems = document.querySelectorAll('#sidebar-menu .menu-item');
        const currentPath = window.location.pathname;
        let activeItem = null;

        // Check localStorage for saved active item
        const savedItemSlug = localStorage.getItem('activeMenuItem');

        // Helper function to set active item
        function setActiveItem(item) {
            // Remove active class from all items except those that should remain open
            menuItems.forEach(menuItem => {
                if (menuItem !== item && !menuItem.contains(item)) {
                    menuItem.classList.remove('active');

                    // Only remove 'open' class from items that aren't parents of the active item
                    if (!item || !menuItem.contains(item)) {
                        menuItem.classList.remove('open');
                    }
                }
            });

            // Add active class to selected item
            if (item) {
                item.classList.add('active');

                // If it's a submenu item, open its parent
                const parentItem = item.closest('.menu-sub')?.closest('.menu-item');
                if (parentItem) {
                    parentItem.classList.add('active', 'open');
                }

                // Save to localStorage
                const itemSlug = item.getAttribute('data-menu-slug');
                if (itemSlug) {
                    localStorage.setItem('activeMenuItem', itemSlug);
                }

                // Scroll into view
                setTimeout(() => {
                    item.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }, 100);
            }
        }

        // First find active item for exact page match
        menuItems.forEach(item => {
            const menuLink = item.querySelector(':scope > .menu-link');
            if (menuLink && !menuLink.classList.contains('menu-toggle')) {
                const href = menuLink.getAttribute('href');
                if (href && currentPath.endsWith(href)) {
                    activeItem = item;
                    return; // Exit the loop if exact match found
                }
            }
        });

        // If no exact match, check for partial match using data-menu-slug
        if (!activeItem) {
            menuItems.forEach(item => {
                const itemSlug = item.getAttribute('data-menu-slug');
                if (itemSlug && currentPath.includes(itemSlug)) {
                    activeItem = item;
                }
            });
        }

        // If still no match, try to use saved item if it exists
        if (!activeItem && savedItemSlug) {
            menuItems.forEach(item => {
                if (item.getAttribute('data-menu-slug') === savedItemSlug) {
                    activeItem = item;
                }
            });
        }

        // Set active state on the found item
        if (activeItem) {
            setActiveItem(activeItem);
        }

        // Add click event listeners to all menu links that are not toggles
        menuItems.forEach(item => {
            const link = item.querySelector(':scope > .menu-link');
            if (link && !link.classList.contains('menu-toggle')) {
                link.addEventListener('click', function(e) {
                    setActiveItem(item);
                });
            }
        });

        // Special handling for menu toggles (parent items)
        const menuToggles = document.querySelectorAll('.menu-link.menu-toggle');
        menuToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent default link behavior
                e.stopPropagation(); // Stop event from bubbling up

                const parentItem = this.closest('.menu-item');
                if (parentItem) {
                    // Toggle only this item's open state without affecting others
                    parentItem.classList.toggle('open');
                }
            });
        });
    });
</script>

<!-- Layout container -->
<div class="layout-page">