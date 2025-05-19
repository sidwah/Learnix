<?php
// Authentication check
require_once '../backend/auth/admin/admin-auth-check.php';

// Set page title
$pageTitle = "Department Staff - Admin | Learnix";

include_once '../includes/admin/header.php';

// <!-- Menu -->
include_once '../includes/admin/sidebar.php';
// <!-- / Menu -->

// <!-- Navbar -->
include_once '../includes/admin/navbar.php';

// Get staff data from database
require_once '../backend/config.php';

// Fetch all department staff with their details
$query = "SELECT ds.staff_id, ds.role, ds.appointment_date, ds.status, 
                 u.user_id, u.first_name, u.last_name, u.email, u.profile_pic,
                 d.department_id, d.name as department_name
          FROM department_staff ds
          JOIN users u ON ds.user_id = u.user_id
          JOIN departments d ON ds.department_id = d.department_id
          WHERE ds.deleted_at IS NULL
          ORDER BY u.first_name, u.last_name";

$result = mysqli_query($conn, $query);

// Count statistics
$totalStaff = 0;
$headCount = 0;
$secretaryCount = 0;
$activeCount = 0;
$inactiveCount = 0;

$staffMembers = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $staffMembers[] = $row;
        $totalStaff++;
        if ($row['role'] == 'head') $headCount++;
        if ($row['role'] == 'secretary') $secretaryCount++;
        if ($row['status'] == 'active') $activeCount++;
        if ($row['status'] == 'inactive') $inactiveCount++;
    }
}
?>

<!-- / Navbar -->

<!-- Toast Notification -->
<div class="bs-toast toast toast-placement-ex m-2 fade bg-danger top-0 end-0" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000" id="errorToast" style="z-index: 9999; position: fixed;">
    <div class="toast-header">
        <i class="bx bx-bell me-2"></i>
        <div class="me-auto fw-semibold">Error</div>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body" id="errorToastMessage"></div>
</div>

<div class="bs-toast toast toast-placement-ex m-2 fade bg-success top-0 end-0" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000" id="successToast" style="z-index: 9999; position: fixed;">
    <div class="toast-header">
        <i class="bx bx-check me-2"></i>
        <div class="me-auto fw-semibold">Success</div>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body" id="successToastMessage"></div>
</div>
<!-- /Toast Notification -->

<!-- Loading Overlay -->
<div class="custom-overlay" id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9998; align-items: center; justify-content: center; flex-direction: column;">
    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
        <span class="visually-hidden">Loading...</span>
    </div>
    <div class="text-white mt-3" id="loading-message">Processing...</div>
</div>

<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Admin /</span> Department Staff
    </h4>

    <!-- Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Total Staff</p>
                            <div class="d-flex align-items-end mb-2">
                                <h4 class="card-title mb-0 me-2"><?php echo $totalStaff; ?></h4>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-primary rounded p-2">
                                <i class="bx bx-group bx-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Department Heads</p>
                            <div class="d-flex align-items-end mb-2">
                                <h4 class="card-title mb-0 me-2"><?php echo $headCount; ?></h4>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-info rounded p-2">
                                <i class="bx bx-user-voice bx-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Department Secretaries</p>
                            <div class="d-flex align-items-end mb-2">
                                <h4 class="card-title mb-0 me-2"><?php echo $secretaryCount; ?></h4>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-success rounded p-2">
                                <i class="bx bx-user-pin bx-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Active / Inactive</p>
                            <div class="d-flex align-items-end mb-2">
                                <h4 class="card-title mb-0 me-2"><?php echo $activeCount; ?> / <?php echo $inactiveCount; ?></h4>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-warning rounded p-2">
                                <i class="bx bx-user-check bx-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Staff List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Department Staff</h5>
            <div class="d-flex align-items-center">
                <?php if ($totalStaff > 0): ?>
                    <div class="me-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" class="form-control" id="staffSearch" placeholder="Search staff..." aria-label="Search">
                        </div>
                    </div>
                    <div class="me-3">
                        <div class="btn-group" role="group" aria-label="Filter by status">
                            <button type="button" class="btn btn-outline-secondary btn-sm filter-status active" data-status="all">
                                All
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm filter-status" data-status="active">
                                Active
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-sm filter-status" data-status="inactive">
                                Inactive
                            </button>
                        </div>
                    </div>
                    <div class="dropdown me-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-filter-alt me-1"></i> Filter Role
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item filter-role" href="javascript:void(0);" data-role="all">All Roles</a></li>
                            <li><a class="dropdown-item filter-role" href="javascript:void(0);" data-role="head">Department Heads</a></li>
                            <li><a class="dropdown-item filter-role" href="javascript:void(0);" data-role="secretary">Department Secretaries</a></li>
                        </ul>
                    </div>
                <?php endif; ?>
                <?php if ($totalStaff > 0): ?>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="exportBtn">
                        <i class="bx bx-export me-1"></i> Export
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <?php if (count($staffMembers) > 0): ?>
            <div class="table-responsive text-nowrap">
                <table class="table" id="staffTable">
                    <thead>
                        <tr>
                            <th>Staff Member</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Appointment Date</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        <?php foreach ($staffMembers as $staff): ?>
                            <tr class="staff-row"
                                data-id="<?php echo $staff['staff_id']; ?>"
                                data-user-id="<?php echo $staff['user_id']; ?>"
                                data-role="<?php echo $staff['role']; ?>"
                                data-status="<?php echo $staff['status']; ?>"
                                data-department="<?php echo $staff['department_id']; ?>"
                                data-name="<?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?>"
                                data-first-name="<?php echo htmlspecialchars($staff['first_name']); ?>"
                                data-last-name="<?php echo htmlspecialchars($staff['last_name']); ?>"
                                data-email="<?php echo htmlspecialchars($staff['email']); ?>"
                                data-profile-pic="<?php echo !empty($staff['profile_pic']) ? '../uploads/department-staff/' . $staff['profile_pic'] : '../assets/img/avatars/1.png'; ?>"
                                data-appointment="<?php echo date('F d, Y', strtotime($staff['appointment_date'])); ?>"
                                data-department-name="<?php echo htmlspecialchars($staff['department_name']); ?>">
                                <td>
                                    <div class="d-flex justify-content-start align-items-center">
                                        <div class="avatar-wrapper">
                                            <div class="avatar me-2">
                                                <img src="<?php echo !empty($staff['profile_pic']) ? '../uploads/department-staff/' . $staff['profile_pic'] : '../assets/img/avatars/1.png'; ?>" alt="Staff Avatar" class="rounded-circle">
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold"><?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?></span>
                                            <small class="text-muted"><?php echo htmlspecialchars($staff['email']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-<?php echo $staff['role'] == 'head' ? 'primary' : 'info'; ?>">
                                        <?php echo $staff['role'] == 'head' ? 'Department Head' : 'Department Secretary'; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($staff['department_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($staff['appointment_date'])); ?></td>
                                <td>
                                    <span class="badge bg-label-<?php echo $staff['status'] == 'active' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($staff['status']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-block">
                                        <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill btn-icon view-staff" data-bs-toggle="modal" data-bs-target="#viewStaffModal" title="View Details">
                                            <i class="bx bx-show-alt"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill btn-icon change-status" data-bs-toggle="modal" data-bs-target="#changeStatusModal" title="Change Status">
                                            <i class="bx bx-transfer"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill btn-icon reset-password" data-bs-toggle="modal" data-bs-target="#resetPasswordModal" title="Reset Password">
                                            <i class="bx bx-key"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Empty search results state -->
                <div id="empty-search-results" class="text-center py-5 d-none">
                    <div class="empty-state">
                        <div class="empty-state-icon mb-4">
                            <i class="bx bx-search" style="font-size: 4rem; color: #dfe3e7;"></i>
                        </div>
                        <h5 class="mb-2">No Staff Found</h5>
                        <p class="mb-0 text-muted">No staff members match your current filters.</p>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-3" id="clearFiltersBtn">
                            <i class="bx bx-reset me-1"></i> Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="card-footer">
                <div class="row">
                    <div class="col-sm-12 col-md-5">
                        <div class="dataTables_info" id="pagination-info" role="status" aria-live="polite">
                            Showing <span id="showing-start">1</span> to <span id="showing-end"><?php echo count($staffMembers); ?></span> of <span id="total-entries"><?php echo $totalStaff; ?></span> entries
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-7">
                        <div class="dataTables_paginate paging_simple_numbers" id="pagination-container">
                            <ul class="pagination pagination-sm justify-content-end mb-0">
                                <li class="paginate_button page-item previous disabled" id="pagination-previous">
                                    <a href="#" class="page-link">Previous</a>
                                </li>
                                <li class="paginate_button page-item active">
                                    <a href="#" class="page-link">1</a>
                                </li>
                                <?php if ($totalStaff > 10): ?>
                                    <li class="paginate_button page-item">
                                        <a href="#" class="page-link">2</a>
                                    </li>
                                <?php endif; ?>
                                <?php if ($totalStaff > 20): ?>
                                    <li class="paginate_button page-item">
                                        <a href="#" class="page-link">3</a>
                                    </li>
                                <?php endif; ?>
                                <li class="paginate_button page-item next<?php echo ($totalStaff <= 10) ? ' disabled' : ''; ?>" id="pagination-next">
                                    <a href="#" class="page-link">Next</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Empty state -->
            <div class="card-body text-center py-5">
                <div class="empty-state">
                    <div class="empty-state-icon mb-4">
                        <i class="bx bx-user" style="font-size: 6rem; color: #dfe3e7;"></i>
                    </div>
                    <h4 class="mb-2">No Department Staff Found</h4>
                    <p class="mb-4 text-muted">No department staff members have been appointed yet.<br>Department staff are appointed through department management.</p>
                    <a href="departments.php" class="btn btn-primary">
                        <i class="bx bx-building me-1"></i> Manage Departments
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- View Staff Modal -->
    <div class="modal fade" id="viewStaffModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Staff Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <img src="../assets/img/avatars/1.png" alt="Staff Avatar" class="rounded-circle border" width="100" height="100" id="view-staff-image">
                        <h5 class="mt-3 mb-1" id="view-staff-name"></h5>
                        <p class="text-muted small" id="view-staff-email"></p>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <p class="mb-1 text-muted small">Role</p>
                            <p class="mb-0" id="view-staff-role"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="mb-1 text-muted small">Status</p>
                            <p class="mb-0" id="view-staff-status"></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <p class="mb-1 text-muted small">Department</p>
                            <p class="mb-0" id="view-staff-department"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="mb-1 text-muted small">Appointment Date</p>
                            <p class="mb-0" id="view-staff-appointment"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Status Modal -->
    <div class="modal fade" id="changeStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change Staff Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="changeStatusForm" action="../backend/admin/change-staff-status.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="statusStaffId" name="staff_id" value="">
                        <input type="hidden" id="statusAction" name="action" value="">
                        <input type="hidden" id="statusUserEmail" name="user_email" value="">
                        <input type="hidden" id="statusUserRole" name="user_role" value="">
                        <input type="hidden" id="statusUserName" name="user_name" value="">

                        <div class="text-center mb-4">
                            <div class="avatar avatar-lg">
                                <img src="../assets/img/avatars/1.png" alt="Staff Avatar" class="rounded-circle" id="status-staff-image">
                            </div>
                            <h5 class="mt-2 mb-0" id="status-staff-name"></h5>
                            <p class="text-muted small mb-2" id="status-staff-role"></p>
                            <p class="text-muted small" id="status-staff-department"></p>
                        </div>

                        <div id="deactivate-message" class="d-none">
                            <div class="alert alert-warning">
                                <h6 class="alert-heading mb-1">Are you sure you want to deactivate this staff member?</h6>
                                <p class="mb-0">Deactivating will prevent the staff member from accessing department functions until reactivated.</p>
                            </div>
                        </div>

                        <div id="activate-message" class="d-none">
                            <div class="alert alert-info">
                                <h6 class="alert-heading mb-1">Are you sure you want to activate this staff member?</h6>
                                <p class="mb-0">Activating will allow the staff member to access department functions again.</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="resetPasswordForm" action="../backend/admin/reset-staff-password.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="resetUserId" name="user_id" value="">
                        <input type="hidden" id="resetUserEmail" name="user_email" value="">
                        <input type="hidden" id="resetUserName" name="user_name" value="">
                        <input type="hidden" id="resetUserRole" name="user_role" value="">

                        <div class="text-center mb-4">
                            <div class="avatar avatar-lg">
                                <img src="../assets/img/avatars/1.png" alt="Staff Avatar" class="rounded-circle" id="reset-staff-image">
                            </div>
                            <h5 class="mt-2 mb-0" id="reset-staff-name"></h5>
                            <p class="text-muted small mb-0" id="reset-staff-email"></p>
                        </div>

                        <div class="alert alert-warning">
                            <h6 class="alert-heading mb-1">Are you sure you want to reset the password?</h6>
                            <p class="mb-0">A password reset link will be sent to the staff member's email. The current password will remain active until the reset link is used.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Send Reset Link</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- / Content -->

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function(tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Toast notification function
        function showToast(message, type = 'success') {
            const toastEl = document.getElementById(type === 'success' ? 'successToast' : 'errorToast');
            const toastMessageEl = document.getElementById(type === 'success' ? 'successToastMessage' : 'errorToastMessage');

            if (toastEl && toastMessageEl) {
                toastMessageEl.textContent = message;
                const toast = new bootstrap.Toast(toastEl);
                toast.show();
            }
        }

        // Show/hide loading overlay
        function showOverlay(message = 'Processing...') {
            const overlay = document.getElementById('loadingOverlay');
            const messageEl = document.getElementById('loading-message');

            if (messageEl) {
                messageEl.textContent = message;
            }

            overlay.style.display = 'flex';
            overlay.dataset.startTime = Date.now();
        }

        function removeOverlay() {
            const overlay = document.getElementById('loadingOverlay');
            const startTime = parseInt(overlay.dataset.startTime || 0);
            const currentTime = Date.now();
            const elapsedTime = currentTime - startTime;

            if (elapsedTime >= 1000) {
                overlay.style.display = 'none';
            } else {
                setTimeout(() => {
                    overlay.style.display = 'none';
                }, 1000 - elapsedTime);
            }
        }

        // Pagination functionality
        const ITEMS_PER_PAGE = 10; // Max number of items per page
        let currentPage = 1;

        function setupPagination() {
            // Get all visible rows
            const visibleRows = Array.from(document.querySelectorAll('.staff-row'))
                .filter(row => row.style.display !== 'none');

            const totalItems = visibleRows.length;
            const totalPages = Math.ceil(totalItems / ITEMS_PER_PAGE);

            // Update pagination info
            document.getElementById('showing-start').textContent =
                totalItems > 0 ? ((currentPage - 1) * ITEMS_PER_PAGE + 1) : 0;
            document.getElementById('showing-end').textContent =
                Math.min(currentPage * ITEMS_PER_PAGE, totalItems);
            document.getElementById('total-entries').textContent = totalItems;

            // Hide all rows first
            visibleRows.forEach(row => {
                row.classList.add('d-none');
            });

            // Show only rows for current page
            const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
            const endIndex = Math.min(startIndex + ITEMS_PER_PAGE, totalItems);

            for (let i = startIndex; i < endIndex; i++) {
                visibleRows[i].classList.remove('d-none');
            }

            // Update pagination UI
            updatePaginationUI(totalPages);
        }

        function updatePaginationUI(totalPages) {
            const paginationContainer = document.querySelector('#pagination-container ul');

            // Clear existing page numbers (except Previous and Next)
            const pageItems = document.querySelectorAll('#pagination-container ul li:not(.previous):not(.next)');
            pageItems.forEach(item => item.remove());

            // Previous button state
            const prevButton = document.getElementById('pagination-previous');
            if (currentPage === 1) {
                prevButton.classList.add('disabled');
            } else {
                prevButton.classList.remove('disabled');
            }

            // Next button state
            const nextButton = document.getElementById('pagination-next');
            if (currentPage === totalPages || totalPages === 0) {
                nextButton.classList.add('disabled');
            } else {
                nextButton.classList.remove('disabled');
            }

            // Insert page numbers
            const maxVisiblePages = 5; // Maximum number of page buttons to show
            let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

            // Adjust start if we're near the end
            if (endPage - startPage + 1 < maxVisiblePages && startPage > 1) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }

            // Insert page numbers
            for (let i = startPage; i <= endPage; i++) {
                const li = document.createElement('li');
                li.className = `paginate_button page-item ${i === currentPage ? 'active' : ''}`;
                li.innerHTML = `<a href="#" class="page-link">${i}</a>`;

                // Add click handler
                li.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (i !== currentPage) {
                        currentPage = i;
                        setupPagination();
                    }
                });

                // Insert before the Next button
                paginationContainer.insertBefore(li, nextButton);
            }

            // Add ellipsis if needed
            if (startPage > 1) {
                const ellipsisStart = document.createElement('li');
                ellipsisStart.className = 'paginate_button page-item disabled';
                ellipsisStart.innerHTML = '<a href="#" class="page-link">...</a>';
                paginationContainer.insertBefore(ellipsisStart, paginationContainer.querySelector(`li:nth-child(${2})`));
            }

            if (endPage < totalPages) {
                const ellipsisEnd = document.createElement('li');
                ellipsisEnd.className = 'paginate_button page-item disabled';
                ellipsisEnd.innerHTML = '<a href="#" class="page-link">...</a>';
                paginationContainer.insertBefore(ellipsisEnd, nextButton);
            }
        }

        // Add event listeners for previous and next buttons
        document.getElementById('pagination-previous').addEventListener('click', function(e) {
            e.preventDefault();
            if (currentPage > 1) {
                currentPage--;
                setupPagination();
            }
        });

        document.getElementById('pagination-next').addEventListener('click', function(e) {
            e.preventDefault();
            const visibleRows = document.querySelectorAll('.staff-row:not([style*="display: none"])');
            const totalPages = Math.ceil(visibleRows.length / ITEMS_PER_PAGE);

            if (currentPage < totalPages) {
                currentPage++;
                setupPagination();
            }
        });

        // Filter functionality
        let currentRoleFilter = 'all';
        let currentStatusFilter = 'all';

        // Search functionality
        document.getElementById('staffSearch').addEventListener('keyup', function() {
            filterStaff();
        });

        // Role filter - using dropdown
        document.querySelectorAll('.filter-role').forEach(btn => {
            btn.addEventListener('click', function() {
                currentRoleFilter = this.getAttribute('data-role');
                filterStaff();
            });
        });

        // Status filter - using buttons
        document.querySelectorAll('.filter-status').forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all buttons
                document.querySelectorAll('.filter-status').forEach(b => b.classList.remove('active'));

                // Add active class to clicked button
                this.classList.add('active');

                currentStatusFilter = this.getAttribute('data-status');
                filterStaff();
            });
        });

        // Clear filters
        document.getElementById('clearFiltersBtn').addEventListener('click', function() {
            document.getElementById('staffSearch').value = '';
            currentRoleFilter = 'all';
            currentStatusFilter = 'all';

            // Reset status filter buttons
            document.querySelectorAll('.filter-status').forEach(btn => {
                btn.classList.remove('active');
                if (btn.getAttribute('data-status') === 'all') {
                    btn.classList.add('active');
                }
            });

            filterStaff();
        });

        function filterStaff() {
            const searchTerm = document.getElementById('staffSearch').value.toLowerCase();
            const rows = document.querySelectorAll('.staff-row');
            const emptySearchResults = document.getElementById('empty-search-results');

            let visibleCount = 0;

            rows.forEach(row => {
                const name = row.getAttribute('data-name').toLowerCase();
                const email = row.getAttribute('data-email').toLowerCase();
                const role = row.getAttribute('data-role');
                const status = row.getAttribute('data-status');
                const department = row.getAttribute('data-department-name').toLowerCase();

                // Check if row matches search term
                const matchesSearch = name.includes(searchTerm) ||
                    email.includes(searchTerm) ||
                    department.includes(searchTerm);

                // Check if row matches role filter
                const matchesRole = currentRoleFilter === 'all' || role === currentRoleFilter;

                // Check if row matches status filter
                const matchesStatus = currentStatusFilter === 'all' || status === currentStatusFilter;

                // Show/hide row based on filters
                if (matchesSearch && matchesRole && matchesStatus) {
                    row.style.display = ''; // Make row visible to filter
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show/hide empty state for filtered results
            if (visibleCount === 0 && rows.length > 0) {
                // We have staff but none match the filter
                if (emptySearchResults) {
                    emptySearchResults.classList.remove('d-none');
                }
            } else {
                if (emptySearchResults) {
                    emptySearchResults.classList.add('d-none');
                }

                // Reset to page 1 when filtering
                currentPage = 1;

                // Setup pagination with filtered results
                setupPagination();
            }
        }

        // View staff details
        document.querySelectorAll('.view-staff').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');

                // Set modal values
                document.getElementById('view-staff-image').src = row.getAttribute('data-profile-pic');
                document.getElementById('view-staff-name').textContent = row.getAttribute('data-name');
                document.getElementById('view-staff-email').textContent = row.getAttribute('data-email');

                // Set role badge
                const role = row.getAttribute('data-role');
                document.getElementById('view-staff-role').innerHTML =
                    `<span class="badge bg-label-${role === 'head' ? 'primary' : 'info'}">${role === 'head' ? 'Department Head' : 'Department Secretary'}</span>`;

                // Set status badge
                const status = row.getAttribute('data-status');
                document.getElementById('view-staff-status').innerHTML =
                    `<span class="badge bg-label-${status === 'active' ? 'success' : 'warning'}">${status === 'active' ? 'Active' : 'Inactive'}</span>`;

                document.getElementById('view-staff-department').textContent = row.getAttribute('data-department-name');
                document.getElementById('view-staff-appointment').textContent = row.getAttribute('data-appointment');
            });
        });

        // Change status
        document.querySelectorAll('.change-status').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');
                const staffId = row.getAttribute('data-id');
                const status = row.getAttribute('data-status');
                const newStatus = status === 'active' ? 'inactive' : 'active';
                const action = status === 'active' ? 'deactivate' : 'activate';

                // Set form values
                document.getElementById('statusStaffId').value = staffId;
                document.getElementById('statusAction').value = action;
                document.getElementById('statusUserEmail').value = row.getAttribute('data-email');
                document.getElementById('statusUserRole').value = row.getAttribute('data-role') === 'head' ? 'department_head' : 'department_secretary';
                document.getElementById('statusUserName').value = row.getAttribute('data-name');

                // Set staff info in the modal
                document.getElementById('status-staff-image').src = row.getAttribute('data-profile-pic');
                document.getElementById('status-staff-name').textContent = row.getAttribute('data-name');
                document.getElementById('status-staff-role').textContent =
                    row.getAttribute('data-role') === 'head' ? 'Department Head' : 'Department Secretary';
                document.getElementById('status-staff-department').textContent = row.getAttribute('data-department-name');

                // Show the appropriate message
                if (action === 'deactivate') {
                    document.getElementById('deactivate-message').classList.remove('d-none');
                    document.getElementById('activate-message').classList.add('d-none');
                } else {
                    document.getElementById('deactivate-message').classList.add('d-none');
                    document.getElementById('activate-message').classList.remove('d-none');
                }
            });
        });

        // Reset password
        document.querySelectorAll('.reset-password').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');
                const userId = row.getAttribute('data-user-id');

                // Set form values
                document.getElementById('resetUserId').value = userId;
                document.getElementById('resetUserEmail').value = row.getAttribute('data-email');
                document.getElementById('resetUserName').value = row.getAttribute('data-name');
                document.getElementById('resetUserRole').value = row.getAttribute('data-role') === 'head' ? 'department_head' : 'department_secretary';

                // Set staff info in the modal
                document.getElementById('reset-staff-image').src = row.getAttribute('data-profile-pic');
                document.getElementById('reset-staff-name').textContent = row.getAttribute('data-name');
                document.getElementById('reset-staff-email').textContent = row.getAttribute('data-email');
            });
        });

        // AJAX form submission handler
        // AJAX form submission handler
        function handleFormSubmit(formId, url) {
            const form = document.getElementById(formId);
            if (!form) return;

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Show loading overlay
                showOverlay('Processing...');

                // Submit form using fetch API
                fetch(url, {
                        method: 'POST',
                        body: new FormData(form)
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Remove overlay
                        removeOverlay();

                        if (data.status === 'success') {
                            // Show success toast
                            showToast(data.message, 'success');

                            // Close modal if applicable
                            const modal = bootstrap.Modal.getInstance(form.closest('.modal'));
                            if (modal) {
                                modal.hide();
                            }

                            // Reload page after a delay
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            // Show error toast
                            showToast(data.message || 'An error occurred', 'danger');
                        }
                    })
                    .catch(error => {
                        // Remove overlay
                        removeOverlay();
                        console.error('Error:', error);
                        showToast('An unexpected error occurred. Please try again.', 'danger');
                    });
            });
        }

        // Initialize form handlers
        handleFormSubmit('changeStatusForm', '../backend/admin/change-staff-status.php');
        handleFormSubmit('resetPasswordForm', '../backend/admin/reset-staff-password.php');

        // Export functionality
        document.getElementById('exportBtn').addEventListener('click', function() {
            // Show loading overlay
            showOverlay('Generating export...');

            // Get table data
            const table = document.getElementById('staffTable');
            const rows = table.querySelectorAll('tbody tr:not([style*="display: none"])');

            let csvContent = "data:text/csv;charset=utf-8,";

            // Add headers
            csvContent += "Name,Role,Department,Email,Appointment Date,Status\n";

            // Add rows
            rows.forEach(row => {
                const name = row.getAttribute('data-name').replace(/"/g, '""');
                const role = row.getAttribute('data-role') === 'head' ? 'Department Head' : 'Department Secretary';
                const department = row.getAttribute('data-department-name').replace(/"/g, '""');
                const email = row.getAttribute('data-email').replace(/"/g, '""');
                const appointment = row.getAttribute('data-appointment').replace(/"/g, '""');
                const status = row.getAttribute('data-status');

                csvContent += `"${name}","${role}","${department}","${email}","${appointment}","${status.charAt(0).toUpperCase() + status.slice(1)}"\n`;
            });

            // Create download link
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "department_staff_export_" + new Date().toISOString().split('T')[0] + ".csv");
            document.body.appendChild(link);

            // Delay for animation
            setTimeout(() => {
                // Trigger download
                link.click();

                // Clean up
                document.body.removeChild(link);
                removeOverlay();

                // Show toast
                showToast('Department staff list exported successfully', 'success');
            }, 1000);
        });

        // Initialize pagination
        setupPagination();
    });
</script>

<?php include_once '../includes/admin/footer.php'; ?>