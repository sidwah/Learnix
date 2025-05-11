<?php include '../includes/department/header.php'; ?>

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
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="page-header-title">Instructor Management</h1>
                    <p class="page-header-text">Manage all instructors in your department</p>
                </div>
                <div class="col-auto">
                    <a href="invite-instructor.php" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Add Instructor
                    </a>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2">Total Instructors</h6>
                        <div class="d-flex align-items-center">
                            <h2 class="mb-0 total-instructors-count">0</h2>
                            <!-- <span class="badge bg-soft-success text-success ms-2">Department</span> -->
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-sm-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2">Active</h6>
                        <div class="d-flex align-items-center">
                            <h2 class="mb-0 active-instructors-count">0</h2>
                            <span class="badge bg-soft-success text-success ms-2 active-instructors-percentage">0%</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-sm-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2">Pending</h6>
                        <div class="d-flex align-items-center">
                            <h2 class="mb-0 pending-instructors-count">0</h2>
                            <span class="badge bg-soft-warning text-warning ms-2 pending-instructors-percentage">0%</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-sm-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2">Inactive</h6>
                        <div class="d-flex align-items-center">
                            <h2 class="mb-0 inactive-instructors-count">0</h2>
                            <span class="badge bg-soft-danger text-danger ms-2 inactive-instructors-percentage">0%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Summary Cards -->

        <!-- Search -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control" id="instructorSearch" placeholder="Search instructors..." aria-label="Search" aria-describedby="basic-addon1">
                </div>
            </div>
            <div class="col-md-6">
                <button class="btn btn-outline-secondary" id="clearSearch">
                    <i class="bi bi-x-circle"></i> Clear Search
                </button>
            </div>
        </div>
        <!-- End Search -->

        <!-- Tabs -->
        <div class="mb-3">
            <ul class="nav nav-tabs" id="instructorTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active" type="button" role="tab">
                        Active (<span class="active-tab-count">0</span>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                        Pending (<span class="pending-tab-count">0</span>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="inactive-tab" data-bs-toggle="tab" data-bs-target="#inactive" type="button" role="tab">
                        Inactive (<span class="inactive-tab-count">0</span>)
                    </button>
                </li>
            </ul>
        </div>
        <!-- End Tabs -->

        <!-- Tab Content -->
        <div class="tab-content" id="instructorTabContent">
            <!-- Active Instructors Tab -->
            <div class="tab-pane fade show active" id="active" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-header-title">Active Instructors</h5>
                    </div>
                    <div class="table-responsive" id="active-table-container">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Courses</th>
                                    <th>Last Active</th>
                                    <th style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="active-tbody">
                                <!-- Dynamically populated -->
                            </tbody>
                        </table>
                    </div>
                    <div id="active-empty-state" class="text-center py-5" style="display: none;">
                        <div class="text-muted">
                            <i class="bi bi-people fs-1 mb-3"></i>
                            <h5>No Active Instructors</h5>
                            <p>No instructors are currently active.</p>
                            <a href="invite-instructor.php" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i> Add First Instructor
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Instructors Tab -->
            <div class="tab-pane fade" id="pending" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-header-title">Pending Invitations</h5>
                        <p class="card-text text-muted">Click on any invitation to manage it</p>
                    </div>
                    <div class="table-responsive" id="pending-table-container">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>Email</th>
                                    <th>Invited</th>
                                    <th>Expires</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="pending-tbody">
                                <!-- Dynamically populated -->
                            </tbody>
                        </table>
                    </div>
                    <div id="pending-empty-state" class="text-center py-5" style="display: none;">
                        <div class="text-muted">
                            <i class="bi bi-clock-history fs-1 mb-3"></i>
                            <h5>No Pending Invitations</h5>
                            <p>All invitations have been accepted or there are no pending invitations.</p>
                            <a href="invite-instructor.php" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i> Send New Invitation
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inactive Instructors Tab -->
            <div class="tab-pane fade" id="inactive" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-header-title">Inactive Instructors</h5>
                    </div>
                    <div class="table-responsive" id="inactive-table-container">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Deactivated</th>
                                    <th>Reason</th>
                                    <th style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="inactive-tbody">
                                <!-- Dynamically populated -->
                            </tbody>
                        </table>
                    </div>
                    <div id="inactive-empty-state" class="text-center py-5" style="display: none;">
                        <div class="text-muted">
                            <i class="bi bi-pause-circle fs-1 mb-3"></i>
                            <h5>No Inactive Instructors</h5>
                            <p>All instructors are currently active.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Tab Content -->
    </div>
    <!-- End Content -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- Instructor Details Modal -->
<div class="modal fade" id="instructorDetailsModal" tabindex="-1" aria-labelledby="instructorDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="instructorDetailsModalLabel">Instructor Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="instructor-details-loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div id="instructor-details-content" style="display: none;">
                    <div class="row mb-4">
                        <div class="col-auto">
                            <img class="avatar avatar-xl avatar-circle" id="instructor-profile-pic" src="../uploads/instructor-profile/default.png" alt="Profile">
                        </div>
                        <div class="col">
                            <h4 id="instructor-name">Loading...</h4>
                            <p class="text-muted" id="instructor-email">Loading...</p>
                            <span class="badge" id="instructor-status-badge">Loading...</span>
                        </div>
                    </div>
                    
                    <div class="row text-center mb-4">
                        <div class="col-md-3">
                            <h6 class="text-muted">Active Courses</h6>
                            <h4 id="active-courses">0</h4>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted">Total Students</h6>
                            <h4 id="total-students">0</h4>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted">Average Rating</h6>
                            <h4 id="average-rating">0.0/5.0</h4>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted">Department</h6>
                            <h4 id="instructor-department">N/A</h4>
                        </div>
                    </div>
                    
                    <div>
                        <h6 class="text-muted mb-2">Recent Activity</h6>
                        <ul class="list-unstyled" id="recent-activity">
                            <!-- Dynamically populated -->
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Deactivate Modal -->
<div class="modal fade" id="deactivateModal" tabindex="-1" aria-labelledby="deactivateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deactivateModalLabel">Deactivate Instructor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to deactivate this instructor? They will receive an email notification.</p>
                <div class="mb-3">
                    <label for="deactivateReason" class="form-label">Reason for deactivation <span class="text-danger">*</span></label>
                    <select class="form-select" id="deactivateReason" required>
                        <option value="">Select a reason...</option>
                        <option value="temporary_leave">Temporary Leave</option>
                        <option value="performance_issues">Performance Issues</option>
                        <option value="policy_violation">Policy Violation</option>
                        <option value="no_longer_needed">Position No Longer Needed</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="mb-3" id="deactivateCustomReasonContainer" style="display: none;">
                    <label for="deactivateCustomReason" class="form-label">Please specify</label>
                    <textarea class="form-control" id="deactivateCustomReason" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="confirmDeactivate()">Deactivate</button>
            </div>
        </div>
    </div>
</div>

<!-- Reactivate Modal -->
<div class="modal fade" id="reactivateModal" tabindex="-1" aria-labelledby="reactivateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reactivateModalLabel">Reactivate Instructor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to reactivate this instructor? They will receive an email notification and gain access to their courses again.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="confirmReactivate()">Reactivate</button>
            </div>
        </div>
    </div>
</div>

<!-- Remove Modal -->
<div class="modal fade" id="removeModal" tabindex="-1" aria-labelledby="removeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger" id="removeModalLabel">Remove Instructor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Warning!</strong> This action will permanently remove the instructor from your department.
                </div>
                <p>This action will:</p>
                <ul>
                    <li>Remove the instructor from all department courses</li>
                    <li>Transfer their courses to other instructors</li>
                    <li>Send them a removal notification email</li>
                </ul>
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="confirmRemoval">
                    <label class="form-check-label" for="confirmRemoval">
                        I understand and want to permanently remove this instructor
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRemoveBtn" onclick="confirmRemove()" disabled>Remove Instructor</button>
            </div>
        </div>
    </div>
</div>

<!-- Your Custom Toast -->
<div id="liveToast" class="position-fixed toast hide" role="alert" aria-live="assertive" aria-atomic="true" style="top: 20px; right: 20px; z-index: 1000;">
    <div class="toast-header">
        <div class="d-flex align-items-center flex-grow-1">
            <div id="toastIcon" class="flex-shrink-0 rounded-circle bg-success bg-opacity-10 text-success p-2 d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px;">
                <i class="bi bi-check-lg fs-6"></i>
            </div>
            <div class="flex-grow-1">
                <h5 id="toastTitle" class="mb-0">System Notification</h5>
                <small id="toastTime">Just Now</small>
            </div>
            <div class="text-end">
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
    <div id="toastBody" class="toast-body">
        Hello, world! This is a toast message.
    </div>
</div>

<!-- Custom Overlay -->
<div id="customOverlay" class="custom-overlay" style="display: none;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <div class="text-white ms-3" id="overlayMessage">Processing...</div>
</div>

<?php include '../includes/department/footer.php'; ?>

<style>
.custom-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
    color: white;
    backdrop-filter: blur(2px);
}

.btn-group .btn {
    border-radius: 0;
}

.btn-group .btn:first-child {
    border-top-left-radius: var(--bs-border-radius-sm);
    border-bottom-left-radius: var(--bs-border-radius-sm);
}

.btn-group .btn:last-child {
    border-top-right-radius: var(--bs-border-radius-sm);
    border-bottom-right-radius: var(--bs-border-radius-sm);
}
</style>

<script>
// JavaScript for instructor management
let currentInstructorId = null;
let currentInstructorEmail = null;
let currentInstructorName = null;
let allInstructors = {
    active: [],
    pending: [],
    inactive: []
};

// Initialize page
$(document).ready(function() {
    loadInstructors();
    setupEventListeners();
    
    // Set up auto-refresh every 30 seconds
    setInterval(loadInstructors, 30000);
});

function setupEventListeners() {
    // Search functionality
    $('#instructorSearch').on('input', function() {
        filterInstructors();
    });
    
    // Clear search
    $('#clearSearch').on('click', function() {
        $('#instructorSearch').val('');
        filterInstructors();
    });
    
    // Confirm removal checkbox
    $('#confirmRemoval').change(function() {
        $('#confirmRemoveBtn').prop('disabled', !this.checked);
    });
    
    // Deactivate reason select
    $('#deactivateReason').change(function() {
        if ($(this).val() === 'other') {
            $('#deactivateCustomReasonContainer').show();
        } else {
            $('#deactivateCustomReasonContainer').hide();
            $('#deactivateCustomReason').val('');
        }
    });
}

function showOverlay(message = 'Processing...') {
    $('#overlayMessage').text(message);
    $('#customOverlay').fadeIn(200);
}

function hideOverlay() {
    $('#customOverlay').fadeOut(200);
}

function showToast(title, message, type = 'success') {
    const toastEl = $('#liveToast');
    const toastIcon = $('#toastIcon');
    const toastIconElement = toastIcon.find('i');
    
    // Reset classes
    toastIcon.removeClass();
    toastIcon.addClass('flex-shrink-0 rounded-circle p-2 d-flex align-items-center justify-content-center me-2');
    toastIconElement.removeClass();
    
    // Set up based on type
    switch(type) {
        case 'success':
            toastIcon.addClass('bg-success bg-opacity-10 text-success');
            toastIconElement.addClass('bi bi-check-lg fs-6');
            break;
        case 'error':
            toastIcon.addClass('bg-danger bg-opacity-10 text-danger');
            toastIconElement.addClass('bi bi-exclamation-circle-fill fs-6');
            break;
        case 'warning':
            toastIcon.addClass('bg-warning bg-opacity-10 text-warning');
            toastIconElement.addClass('bi bi-exclamation-triangle-fill fs-6');
            break;
        case 'info':
            toastIcon.addClass('bg-info bg-opacity-10 text-info');
            toastIconElement.addClass('bi bi-info-circle-fill fs-6');
            break;
    }
    
    // Set content
    $('#toastTitle').text(title);
    $('#toastBody').text(message);
    $('#toastTime').text('Just Now');
    
    // Show toast
    const toast = new bootstrap.Toast(toastEl[0], {
        autohide: true,
        delay: 5000
    });
    toast.show();
}

function loadInstructors() {
    $.ajax({
        url: '../backend/department/get_instructors.php',
        method: 'GET',
        success: function(response) {
            console.log('Instructors response:', response);
            
            if (!response || typeof response !== 'object') {
                console.error('Invalid response format');
                showToast('Error', 'Failed to load instructor data', 'error');
                return;
            }
            
            allInstructors = {
                active: response.active || [],
                pending: response.pending || [],
                inactive: response.inactive || []
            };
            
            const summary = response.summary || {
                total: 0,
                active_count: 0,
                pending_count: 0,
                inactive_count: 0
            };
            
            filterInstructors();
            updateSummaryCards(summary);
        },
        error: function(xhr, status, error) {
            console.error('Failed to load instructors:', error);
            console.error('Response:', xhr.responseText);
            showToast('Error', 'Failed to load instructor data. Please refresh the page.', 'error');
            
            allInstructors = {
                active: [],
                pending: [],
                inactive: []
            };
            updateSummaryCards({
                total: 0,
                active_count: 0,
                pending_count: 0,
                inactive_count: 0
            });
        }
    });
}

function filterInstructors() {
    const searchTerm = $('#instructorSearch').val().toLowerCase();
    
    if (!searchTerm) {
        updateInstructorTables(allInstructors);
        updateTabCounts(allInstructors);
        return;
    }
    
    const filtered = {
        active: filterCategory(allInstructors.active, searchTerm),
        pending: filterCategory(allInstructors.pending, searchTerm),
        inactive: filterCategory(allInstructors.inactive, searchTerm)
    };
    
    updateInstructorTables(filtered);
    updateTabCounts(filtered);
}

function filterCategory(items, searchTerm) {
    return items.filter(item => {
        const name = item.name || '';
        const email = item.email || '';
        return name.toLowerCase().includes(searchTerm) || 
               email.toLowerCase().includes(searchTerm);
    });
}

function updateSummaryCards(summary) {
    const total = summary.total || 0;
    const activeCount = summary.active_count || 0;
    const pendingCount = summary.pending_count || 0;
    const inactiveCount = summary.inactive_count || 0;
    
    $('.total-instructors-count').text(total);
    $('.active-instructors-count').text(activeCount);
    $('.pending-instructors-count').text(pendingCount);
    $('.inactive-instructors-count').text(inactiveCount);
    
    if (total > 0) {
        $('.active-instructors-percentage').text(Math.round((activeCount / total) * 100) + '%');
        $('.pending-instructors-percentage').text(Math.round((pendingCount / total) * 100) + '%');
        $('.inactive-instructors-percentage').text(Math.round((inactiveCount / total) * 100) + '%');
    } else {
        $('.active-instructors-percentage').text('0%');
        $('.pending-instructors-percentage').text('0%');
        $('.inactive-instructors-percentage').text('0%');
    }
}

function updateTabCounts(data) {
    $('.active-tab-count').text((data.active || []).length);
    $('.pending-tab-count').text((data.pending || []).length);
    $('.inactive-tab-count').text((data.inactive || []).length);
}

function updateInstructorTables(data) {
    updateActiveInstructorsTable(data.active || []);
    updatePendingInvitationsTable(data.pending || []);
    updateInactiveInstructorsTable(data.inactive || []);
}

function updateActiveInstructorsTable(instructors) {
    const tbody = $('#active-tbody');
    tbody.empty();
    
    if (instructors.length === 0) {
        $('#active-table-container').hide();
        $('#active-empty-state').show();
        return;
    }
    
    $('#active-table-container').show();
    $('#active-empty-state').hide();
    
    instructors.forEach(function(instructor) {
        const row = `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <img class="avatar avatar-sm avatar-circle" src="../uploads/instructor-profile/${instructor.profile_pic || 'default.png'}" alt="Profile">
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <a class="d-inline-block link-dark" href="#" data-instructor-id="${instructor.instructor_id}" onclick="event.preventDefault(); viewInstructor(${instructor.instructor_id});">
                                <h6 class="text-hover-primary mb-0">${instructor.name}</h6>
                            </a>
                            <small class="d-block">${instructor.email}</small>
                        </div>
                    </div>
                </td>
                <td>${instructor.course_count || 0}</td>
                <td>${instructor.last_active || 'Never'}</td>
                <td>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-soft-info" onclick="viewInstructor(${instructor.instructor_id})" data-bs-toggle="tooltip" title="View Details">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-soft-warning" onclick="deactivateInstructor(${instructor.instructor_id}, '${encodeURIComponent(instructor.email)}', '${encodeURIComponent(instructor.name)}')" data-bs-toggle="tooltip" title="Deactivate">
                            <i class="bi bi-pause-circle"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-soft-danger" onclick="removeInstructor(${instructor.instructor_id}, '${encodeURIComponent(instructor.email)}', '${encodeURIComponent(instructor.name)}')" data-bs-toggle="tooltip" title="Remove">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
    
    initializeTooltips();
}

function updatePendingInvitationsTable(invitations) {
    const tbody = $('#pending-tbody');
    tbody.empty();
    
    if (invitations.length === 0) {
        $('#pending-table-container').hide();
        $('#pending-empty-state').show();
        return;
    }
    
    $('#pending-table-container').show();
    $('#pending-empty-state').hide();
    
    invitations.forEach(function(invitation) {
        const badgeClass = invitation.expires_status === 'warning' ? 'bg-soft-warning text-warning' : 'bg-soft-success text-success';
        const row = `
            <tr class="table-hover" onclick="location.href='invite-instructor.php?invitation_id=${invitation.id}'" style="cursor: pointer;">
                <td>
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <span class="avatar avatar-sm avatar-soft-secondary avatar-circle">
                                <span class="avatar-initials">${invitation.email.substring(0, 2).toUpperCase()}</span>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">${invitation.email}</h6>
                        </div>
                    </div>
                </td>
                <td>${invitation.invited}</td>
                <td>
                    <span class="badge ${badgeClass}">${invitation.expires}</span>
                </td>
                <td>
                    <i class="bi bi-chevron-right text-muted"></i>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

function updateInactiveInstructorsTable(instructors) {
    const tbody = $('#inactive-tbody');
    tbody.empty();
    
    if (instructors.length === 0) {
        $('#inactive-table-container').hide();
        $('#inactive-empty-state').show();
        return;
    }
    
    $('#inactive-table-container').show();
    $('#inactive-empty-state').hide();
    
    instructors.forEach(function(instructor) {
        const row = `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <img class="avatar avatar-sm avatar-circle" src="../uploads/instructor-profile/${instructor.profile_pic || 'default.png'}" alt="Profile">
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">${instructor.name}</h6>
                            <small class="d-block">${instructor.email}</small>
                        </div>
                    </div>
                </td>
                <td>${instructor.deactivated}</td>
                <td>
                    <span class="badge bg-soft-secondary">${instructor.reason || 'N/A'}</span>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-soft-info" onclick="viewInstructor(${instructor.instructor_id})" data-bs-toggle="tooltip" title="View Details">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-soft-success" onclick="reactivateInstructor(${instructor.instructor_id}, '${encodeURIComponent(instructor.email)}', '${encodeURIComponent(instructor.name)}')" data-bs-toggle="tooltip" title="Reactivate">
                            <i class="bi bi-play-circle"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-soft-danger" onclick="removeInstructor(${instructor.instructor_id}, '${encodeURIComponent(instructor.email)}', '${encodeURIComponent(instructor.name)}')" data-bs-toggle="tooltip" title="Remove">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
    
    initializeTooltips();
}

function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

function viewInstructor(instructorId) {
    currentInstructorId = instructorId;
    
    $('#instructor-details-loading').show();
    $('#instructor-details-content').hide();
    $('#instructorDetailsModal').modal('show');
    
    console.log('Viewing instructor:', instructorId);
    
    $.ajax({
        url: '../backend/department/get_instructor_details.php',
        method: 'GET',
        data: { instructor_id: instructorId },
        success: function(response) {
            console.log('Instructor details response:', response);
            
            if (response && typeof response === 'object') {
                populateInstructorModal(response);
                $('#instructor-details-loading').hide();
                $('#instructor-details-content').show();
            } else {
                console.error('Invalid response format for instructor details');
                showToast('Error', 'Failed to load instructor details', 'error');
                $('#instructorDetailsModal').modal('hide');
            }
        },
        error: function(xhr, status, error) {
            console.error('Failed to load instructor details:', error);
            console.error('Response:', xhr.responseText);
            showToast('Error', 'Failed to load instructor details', 'error');
            $('#instructorDetailsModal').modal('hide');
        }
    });
}

function populateInstructorModal(data) {
    $('#instructor-profile-pic').attr('src', `../uploads/instructor-profile/${data.instructor.profile_pic || 'default.png'}`);
    $('#instructor-name').text(data.instructor.name || 'Unknown');
    $('#instructor-email').text(data.instructor.email || 'N/A');
    
    const statusBadge = $('#instructor-status-badge');
    statusBadge.removeClass();
    statusBadge.addClass('badge');
    
    if (data.instructor.status === 'active') {
        statusBadge.addClass('bg-soft-success text-success').text('Active');
    } else if (data.instructor.status === 'inactive') {
        statusBadge.addClass('bg-soft-danger text-danger').text('Inactive');
    } else {
        statusBadge.addClass('bg-soft-warning text-warning').text('Pending');
    }
    
    $('#active-courses').text(data.stats.active_courses || 0);
    $('#total-students').text(data.stats.total_students || 0);
    $('#average-rating').text((data.stats.average_rating || '0.0') + '/5.0');
    $('#instructor-department').text(data.stats.department || 'N/A');
    
    const activityList = $('#recent-activity');
    activityList.empty();
    
    if (data.recent_activity && data.recent_activity.length > 0) {
        data.recent_activity.forEach(function(activity) {
            const activityIcon = activity.type === 'login' ? 'bi-dot text-success' : 
                               activity.type === 'course' ? 'bi-dot text-info' : 
                               'bi-dot text-warning';
                               
            activityList.append(`<li><i class="bi ${activityIcon}"></i> ${activity.description} - ${activity.date}</li>`);
        });
    } else {
        activityList.append('<li class="text-muted">No recent activity</li>');
    }
}

function deactivateInstructor(instructorId, email, name) {
    currentInstructorId = instructorId;
    currentInstructorEmail = decodeURIComponent(email);
    currentInstructorName = decodeURIComponent(name);
    
    $('#deactivateReason').val('');
    $('#deactivateCustomReasonContainer').hide();
    $('#deactivateCustomReason').val('');
    $('#deactivateModal').modal('show');
}

function reactivateInstructor(instructorId, email, name) {
    currentInstructorId = instructorId;
    currentInstructorEmail = decodeURIComponent(email);
    currentInstructorName = decodeURIComponent(name);
    
    $('#reactivateModal').modal('show');
}

function removeInstructor(instructorId, email, name) {
    currentInstructorId = instructorId;
    currentInstructorEmail = decodeURIComponent(email);
    currentInstructorName = decodeURIComponent(name);
    
    $('#confirmRemoval').prop('checked', false);
    $('#confirmRemoveBtn').prop('disabled', true);
    $('#removeModal').modal('show');
}

function confirmDeactivate() {
    const reason = $('#deactivateReason').val();
    if (!reason) {
        showToast('Warning', 'Please select a reason for deactivation', 'warning');
        return;
    }
    
    let finalReason = reason;
    if (reason === 'other') {
        const customReason = $('#deactivateCustomReason').val().trim();
        if (!customReason) {
            showToast('Warning', 'Please specify the reason for deactivation', 'warning');
            return;
        }
        finalReason = customReason;
    }
    
    const btn = event.target;
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deactivating...';
    btn.disabled = true;
    
    showOverlay('Deactivating instructor...');
    
    console.log('Deactivating instructor:', {
        instructor_id: currentInstructorId,
        email: currentInstructorEmail,
        name: currentInstructorName,
        reason: finalReason
    });
    
    $.ajax({
        url: '../backend/department/manage_instructor_status.php',
        method: 'POST',
        data: {
            action: 'deactivate',
            instructor_id: currentInstructorId,
            instructor_email: currentInstructorEmail,
            instructor_name: currentInstructorName,
            reason: finalReason
        },
        success: function(response) {
            console.log('Deactivate response:', response);
            hideOverlay();
            $('#deactivateModal').modal('hide');
            showToast('Success', `${currentInstructorName} has been deactivated. They will receive an email notification.`, 'success');
            loadInstructors();
        },
        error: function(xhr, status, error) {
            console.error('Deactivation failed:', error);
            console.error('Response:', xhr.responseText);
            showToast('Error', 'Failed to deactivate instructor. Please try again.', 'error');
            hideOverlay();
        },
        complete: function() {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });
}

function confirmReactivate() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Reactivating...';
    btn.disabled = true;
    
    showOverlay('Reactivating instructor...');
    
    console.log('Reactivating instructor:', {
        instructor_id: currentInstructorId,
        email: currentInstructorEmail,
        name: currentInstructorName
    });
    
    $.ajax({
        url: '../backend/department/manage_instructor_status.php',
        method: 'POST',
        data: {
            action: 'reactivate',
            instructor_id: currentInstructorId,
            instructor_email: currentInstructorEmail,
            instructor_name: currentInstructorName
        },
        success: function(response) {
            console.log('Reactivate response:', response);
            hideOverlay();
            $('#reactivateModal').modal('hide');
            showToast('Success', `${currentInstructorName} has been reactivated. They will receive an email notification.`, 'success');
            loadInstructors();
        },
        error: function(xhr, status, error) {
            console.error('Reactivation failed:', error);
            console.error('Response:', xhr.responseText);
            showToast('Error', 'Failed to reactivate instructor. Please try again.', 'error');
            hideOverlay();
        },
        complete: function() {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });
}

function confirmRemove() {
    if (!$('#confirmRemoval').is(':checked')) {
        showToast('Warning', 'Please confirm that you want to remove this instructor', 'warning');
        return;
    }
    
    const btn = event.target;
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Removing...';
    btn.disabled = true;
    
    showOverlay('Removing instructor...');
    
    console.log('Removing instructor:', {
        instructor_id: currentInstructorId,
        email: currentInstructorEmail,
        name: currentInstructorName
    });
    
    $.ajax({
        url: '../backend/department/manage_instructor_status.php',
        method: 'POST',
        data: {
            action: 'remove',
            instructor_id: currentInstructorId,
            instructor_email: currentInstructorEmail,
            instructor_name: currentInstructorName
        },
        success: function(response) {
            console.log('Remove response:', response);
            hideOverlay();
            $('#removeModal').modal('hide');
            showToast('Success', `${currentInstructorName} has been removed from the department. They will receive an email notification.`, 'success');
            loadInstructors();
        },
        error: function(xhr, status, error) {
            console.error('Removal failed:', error);
            console.error('Response:', xhr.responseText);
            showToast('Error', 'Failed to remove instructor. Please try again.', 'error');
            hideOverlay();
        },
        complete: function() {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });
}
</script>