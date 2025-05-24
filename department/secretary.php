<?php // department/secretary.php 
?>
<?php include '../includes/department/header.php'; ?>

<?php
// Get current user's department
$currentUserId = $_SESSION['user_id'];
$deptQuery = "SELECT ds.department_id, d.name as department_name 
              FROM department_staff ds 
              JOIN departments d ON ds.department_id = d.department_id 
              WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' AND ds.deleted_at IS NULL";
$deptStmt = $conn->prepare($deptQuery);
$deptStmt->bind_param("i", $currentUserId);
$deptStmt->execute();
$deptResult = $deptStmt->get_result();

if ($deptResult->num_rows === 0) {
    header('Location: ../department-head/index.php');
    exit;
}

$deptRow = $deptResult->fetch_assoc();
$departmentId = $deptRow['department_id'];
$departmentName = $deptRow['department_name'];

// Initialize variables
$has_secretary = false;
$current_secretary = null;
$pending_invitation = null;

// First, check for pending invitation (prioritize this over active secretary)
$invitationQuery = "SELECT dsi.*, u.first_name, u.last_name 
                   FROM department_staff_invitations dsi
                   LEFT JOIN users u ON dsi.email = u.email
                   WHERE dsi.department_id = ? AND dsi.role = 'secretary' 
                   AND dsi.is_used = 0 AND dsi.expiry_time > NOW()
                   ORDER BY dsi.created_at DESC LIMIT 1";
$invitationStmt = $conn->prepare($invitationQuery);
$invitationStmt->bind_param("i", $departmentId);
$invitationStmt->execute();
$invitationResult = $invitationStmt->get_result();

if ($invitationResult->num_rows > 0) {
    $pending_invitation = $invitationResult->fetch_assoc();
} else {
    // No pending invitation, check for active secretary
    $secretaryQuery = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.profile_pic, u.mfa_enabled,
                              ds.appointment_date, ds.status
                       FROM department_staff ds 
                       JOIN users u ON ds.user_id = u.user_id 
                       WHERE ds.department_id = ? AND ds.role = 'secretary' AND ds.status = 'active' AND ds.deleted_at IS NULL";
    $secretaryStmt = $conn->prepare($secretaryQuery);
    $secretaryStmt->bind_param("i", $departmentId);
    $secretaryStmt->execute();
    $secretaryResult = $secretaryStmt->get_result();

    if ($secretaryResult->num_rows > 0) {
        $has_secretary = true;
        $current_secretary = $secretaryResult->fetch_assoc();
    }
}

// Get department settings
$settingsQuery = "SELECT * FROM department_settings WHERE department_id = ?";
$settingsStmt = $conn->prepare($settingsQuery);
$settingsStmt->bind_param("i", $departmentId);
$settingsStmt->execute();
$settingsResult = $settingsStmt->get_result();

if ($settingsResult->num_rows > 0) {
    $department_settings = $settingsResult->fetch_assoc();
} else {
    $department_settings = [
        'invitation_expiry_hours' => 48,
        'auto_approve_instructors' => 0,
        'require_mfa' => 1,
        'email_notifications_enabled' => 1
    ];
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
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-end mb-3">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">Secretary Management</h1>
                    <p class="page-header-text">Manage your department secretary appointment</p>
                </div>
                <div class="col-sm-auto">
                    <?php if (!$has_secretary && !$pending_invitation): ?>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#appointSecretaryModal">
                            <i class="bi bi-person-plus-fill me-2"></i>Appoint Secretary
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Secretary Status Card -->
        <div class="card mb-5">
            <div class="card-header bg-light">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-header-title">Secretary Status</h4>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if ($pending_invitation): ?>
                    <!-- Pending Invitation -->
                    <div class="text-center py-5">
                        <div class="avatar avatar-xl avatar-soft-warning avatar-circle mx-auto mb-3">
                            <i class="bi bi-clock-history avatar-icon"></i>
                        </div>
                        <h5>Secretary Invitation Pending</h5>
                        <p class="text-muted mb-3">
                            Invitation sent to <strong><?php echo htmlspecialchars($pending_invitation['email']); ?></strong><br>
                            <small class="text-muted">Sent: <?php echo date('M d, Y - H:i', strtotime($pending_invitation['created_at'])); ?></small><br>
                            <small class="text-danger">
                                <i class="bi bi-clock me-1"></i>
                                Expires: <?php echo date('M d, Y - H:i', strtotime($pending_invitation['expiry_time'])); ?>
                            </small>
                        </p>

                        <div class="d-flex justify-content-center gap-2">
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#resendInvitationModal">
                                <i class="bi bi-arrow-repeat me-2"></i>Resend Invitation
                            </button>
                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelInvitationModal">
                                <i class="bi bi-x-circle me-2"></i>Cancel Invitation
                            </button>
                        </div>
                    </div>

                <?php elseif ($has_secretary && $current_secretary): ?>
                    <!-- Secretary Assigned -->
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="avatar avatar-xl avatar-soft-primary avatar-circle">
                                <img class="avatar-img" src="../uploads/<?php echo $current_secretary['profile_pic'] ?? 'default.png'; ?>" alt="Secretary Profile">
                            </div>
                        </div>
                        <div class="col">
                            <h5 class="mb-1"><?php echo htmlspecialchars($current_secretary['first_name'] . ' ' . $current_secretary['last_name']); ?></h5>
                            <span class="badge bg-soft-primary text-primary"><?php echo htmlspecialchars($current_secretary['email']); ?></span>
                            <div class="mt-2">
                                <span class="badge bg-soft-success text-success me-2">
                                    <i class="bi bi-check-circle-fill me-1"></i>Active
                                </span>
                                <span class="badge bg-soft-info text-info">
                                    <i class="bi bi-calendar3 me-1"></i>Appointed: <?php echo date('M d, Y', strtotime($current_secretary['appointment_date'])); ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <!-- Replace the dropdown section around line 158 -->
                            <div class="dropdown">
                                <button type="button" class="btn btn-ghost-secondary btn-icon btn-sm rounded-circle"
                                    id="secretaryActionsDropdown"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="secretaryActionsDropdown">
                                    <a class="dropdown-item" href="#" data-bs-target="#viewSecretaryModal">
                                        <i class="bi bi-eye dropdown-item-icon"></i> View Details
                                    </a>
                                    <a class="dropdown-item" href="#" data-bs-target="#contactSecretaryModal">
                                        <i class="bi bi-envelope dropdown-item-icon"></i> Contact Secretary
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#" data-bs-target="#replaceSecretaryModal">
                                        <i class="bi bi-person-fill-gear dropdown-item-icon"></i> Replace Secretary
                                    </a>
                                    <a class="dropdown-item text-danger" href="#" data-bs-target="#removeSecretaryModal">
                                        <i class="bi bi-person-dash dropdown-item-icon"></i> Remove Secretary
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- No Secretary -->
                    <div class="text-center py-5">
                        <div class="avatar avatar-xl avatar-soft-secondary avatar-circle mx-auto mb-3">
                            <i class="bi bi-person-plus avatar-icon"></i>
                        </div>
                        <h5>No Secretary Appointed</h5>
                        <p class="text-muted mb-3">You haven't appointed a secretary for your department yet.</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#appointSecretaryModal">
                            <i class="bi bi-person-plus-fill me-2"></i>Appoint Secretary
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- End Secretary Status Card -->

        <!-- Department Information Card -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-header-title">Department Information</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm avatar-soft-primary avatar-circle me-3">
                                <i class="bi bi-building avatar-icon"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Department</h6>
                                <small class="text-muted"><?php echo htmlspecialchars($departmentName); ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm avatar-soft-info avatar-circle me-3">
                                <i class="bi bi-clock avatar-icon"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Invitation Expiry</h6>
                                <small class="text-muted"><?php echo $department_settings['invitation_expiry_hours']; ?> hours</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Content -->

    <!-- ALL MODALS -->

    <!-- Appoint Secretary Modal -->
    <div class="modal fade" id="appointSecretaryModal" tabindex="-1" aria-labelledby="appointSecretaryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="appointSecretaryModalLabel">Appoint Secretary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="appointSecretaryForm">
                        <div class="mb-3">
                            <label for="secretaryFirstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="secretaryFirstName" required>
                        </div>
                        <div class="mb-3">
                            <label for="secretaryLastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="secretaryLastName" required>
                        </div>
                        <div class="mb-3">
                            <label for="secretaryEmail" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="secretaryEmail" required>
                        </div>
                        <div class="alert alert-info">
                            <h6 class="alert-heading">Secretary Responsibilities</h6>
                            <ul class="mb-0">
                                <li>Process instructor requests and documentation</li>
                                <li>Assist with course management tasks</li>
                                <li>Handle department communications</li>
                                <li>Generate departmental reports</li>
                            </ul>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="appointSecretary()">
                        <i class="bi bi-send-fill me-2"></i>Send Invitation
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Resend Invitation Modal -->
    <div class="modal fade" id="resendInvitationModal" tabindex="-1" aria-labelledby="resendInvitationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resendInvitationModalLabel">Resend Invitation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h6 class="alert-heading">Resend Invitation</h6>
                        <p class="mb-0">This will generate a new temporary password and extend the invitation expiry time. The previous invitation will be invalidated.</p>
                    </div>
                    <?php if ($pending_invitation): ?>
                        <p>Resending invitation to: <strong><?php echo htmlspecialchars($pending_invitation['email']); ?></strong></p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="resendInvitation()">
                        <i class="bi bi-arrow-repeat me-2"></i>Resend Invitation
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Invitation Modal -->
    <div class="modal fade" id="cancelInvitationModal" tabindex="-1" aria-labelledby="cancelInvitationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelInvitationModalLabel">Cancel Invitation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <h6 class="alert-heading">Cancel Invitation</h6>
                        <p class="mb-0">This will permanently cancel the pending invitation. The invitee will no longer be able to accept the secretary position.</p>
                    </div>
                    <?php if ($pending_invitation): ?>
                        <p>Canceling invitation for: <strong><?php echo htmlspecialchars($pending_invitation['email']); ?></strong></p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost-secondary" data-bs-dismiss="modal">Keep Invitation</button>
                    <button type="button" class="btn btn-danger" onclick="cancelInvitation()">
                        <i class="bi bi-x-circle me-2"></i>Cancel Invitation
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Secretary Details Modal -->
    <div class="modal fade" id="viewSecretaryModal" tabindex="-1" aria-labelledby="viewSecretaryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewSecretaryModalLabel">Secretary Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if ($has_secretary && $current_secretary): ?>
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <div class="avatar avatar-xl avatar-circle mx-auto mb-3">
                                    <img class="avatar-img" src="../uploads/<?php echo $current_secretary['profile_pic'] ?? 'default.png'; ?>" alt="Secretary Profile">
                                </div>
                                <h5><?php echo htmlspecialchars($current_secretary['first_name'] . ' ' . $current_secretary['last_name']); ?></h5>
                                <p class="text-muted"><?php echo htmlspecialchars($current_secretary['email']); ?></p>
                            </div>
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <p><strong>Appointment Date</strong></p>
                                        <p class="text-muted"><?php echo date('M d, Y', strtotime($current_secretary['appointment_date'])); ?></p>
                                    </div>
                                    <div class="col-sm-6">
                                        <p><strong>Status</strong></p>
                                        <span class="badge bg-soft-success text-success">Active</span>
                                    </div>
                                    <div class="col-sm-6">
                                        <p><strong>MFA Enabled</strong></p>
                                        <p class="text-muted">
                                            <?php echo $current_secretary['mfa_enabled'] ? 'Yes' : 'No'; ?>
                                        </p>
                                    </div>
                                    <div class="col-sm-6">
                                        <p><strong>User ID</strong></p>
                                        <p class="text-muted">#<?php echo $current_secretary['user_id']; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="openContactFromView()">
                        <i class="bi bi-envelope me-2"></i>Contact Secretary
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Secretary Modal -->
    <div class="modal fade" id="contactSecretaryModal" tabindex="-1" aria-labelledby="contactSecretaryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contactSecretaryModalLabel">Contact Secretary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="contactSecretaryForm">
                        <div class="mb-3">
                            <label for="messageSubject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="messageSubject" required>
                        </div>
                        <div class="mb-3">
                            <label for="messageBody" class="form-label">Message</label>
                            <textarea class="form-control" id="messageBody" rows="4" required></textarea>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="ccMyselfCheck">
                            <label class="form-check-label" for="ccMyselfCheck">
                                Send me a copy
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="sendMessage()">
                        <i class="bi bi-send-fill me-2"></i>Send Message
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Replace Secretary Modal -->
    <div class="modal fade" id="replaceSecretaryModal" tabindex="-1" aria-labelledby="replaceSecretaryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="replaceSecretaryModalLabel">Replace Secretary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning" role="alert">
                        <h6 class="alert-heading">Important</h6>
                        <p class="mb-0">This will remove the current secretary and send an invitation to a new person.</p>
                    </div>
                    <form id="replaceSecretaryForm">
                        <div class="mb-3">
                            <label for="newSecretaryFirstName" class="form-label">New Secretary First Name</label>
                            <input type="text" class="form-control" id="newSecretaryFirstName" required>
                        </div>
                        <div class="mb-3">
                            <label for="newSecretaryLastName" class="form-label">New Secretary Last Name</label>
                            <input type="text" class="form-control" id="newSecretaryLastName" required>
                        </div>
                        <div class="mb-3">
                            <label for="newSecretaryEmail" class="form-label">New Secretary Email</label>
                            <input type="email" class="form-control" id="newSecretaryEmail" required>
                        </div>
                        <div class="mb-3">
                            <label for="replaceReason" class="form-label">Reason for Replacement</label>
                            <textarea class="form-control" id="replaceReason" rows="3" required maxlength="500"></textarea>
                            <small class="form-text text-muted">0/500 characters</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="replaceSecretary()">
                        Replace Secretary
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Remove Secretary Modal -->
    <div class="modal fade" id="removeSecretaryModal" tabindex="-1" aria-labelledby="removeSecretaryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="removeSecretaryModalLabel">Remove Secretary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger" role="alert">
                        <h6 class="alert-heading">Warning</h6>
                        <p class="mb-0">This will permanently remove the secretary position. You will need to appoint a new secretary if needed.</p>
                    </div>
                    <form id="removeSecretaryForm">
                        <div class="mb-3">
                            <label for="removeReason" class="form-label">Reason for Removal</label>
                            <textarea class="form-control" id="removeReason" rows="3" required maxlength="500"></textarea>
                            <small class="form-text text-muted">0/500 characters</small>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confirmRemoval" required>
                            <label class="form-check-label" for="confirmRemoval">
                                I understand this action cannot be undone
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="removeSecretary()">
                        Remove Secretary
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="position-fixed w-100 h-100 d-none" style="top: 0; left: 0; background: rgba(255,255,255,0.8); z-index: 9999;">
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div id="loadingMessage" class="fw-semibold text-primary">Processing...</div>
            </div>
        </div>
    </div>

</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- COMPLETE JAVASCRIPT -->
<!-- JavaScript Functions -->
<script>
    // Utility Functions
    function showOverlay(message = 'Processing...') {
        const overlay = document.getElementById('loadingOverlay');
        const messageEl = document.getElementById('loadingMessage');
        messageEl.textContent = message;
        overlay.classList.remove('d-none');
    }

    function removeOverlay() {
        const overlay = document.getElementById('loadingOverlay');
        overlay.classList.add('d-none');
    }

    function showToast(title, message, type = 'info') {
        // Remove any existing toasts
        const existingToasts = document.querySelectorAll('.toast');
        existingToasts.forEach(toast => toast.remove());

        // Create toast HTML
        const toastHtml = `
            <div class="toast show position-fixed" role="alert" aria-live="assertive" aria-atomic="true" 
                 style="top: 20px; right: 20px; z-index: 10000; min-width: 300px;">
                <div class="toast-header bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} text-white">
                    <i class="bi bi-${type === 'success' ? 'check-circle-fill' : type === 'error' ? 'exclamation-triangle-fill' : 'info-circle-fill'} me-2"></i>
                    <strong class="me-auto">${title}</strong>
                    <small>Just now</small>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;

        // Add toast to page
        document.body.insertAdjacentHTML('beforeend', toastHtml);

        // Auto remove after 5 seconds
        setTimeout(() => {
            const toast = document.querySelector('.toast');
            if (toast) {
                toast.remove();
            }
        }, 5000);
    }

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Bootstrap Dropdown Handler
    function initializeDropdowns() {
        const dropdownToggle = document.getElementById('secretaryActionsDropdown');

        if (dropdownToggle) {
            // Initialize Bootstrap dropdown
            const dropdown = new bootstrap.Dropdown(dropdownToggle);

            // Handle dropdown item clicks
            const dropdownMenu = dropdownToggle.nextElementSibling;
            const dropdownItems = dropdownMenu.querySelectorAll('.dropdown-item');

            dropdownItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Close dropdown
                    dropdown.hide();

                    // Get the target modal from data-bs-target attribute
                    const targetModal = this.getAttribute('data-bs-target');
                    if (targetModal) {
                        // Small delay to ensure dropdown closes first
                        setTimeout(() => {
                            const modal = new bootstrap.Modal(document.querySelector(targetModal));
                            modal.show();
                        }, 100);
                    }
                });
            });
        }
    }

    // Secretary Management Functions
    function appointSecretary() {
        const formData = {
            first_name: document.getElementById('secretaryFirstName').value.trim(),
            last_name: document.getElementById('secretaryLastName').value.trim(),
            email: document.getElementById('secretaryEmail').value.trim()
        };

        if (!formData.first_name || !formData.last_name || !formData.email) {
            showToast('Error', 'Please fill in all required fields.', 'error');
            return;
        }

        if (!isValidEmail(formData.email)) {
            showToast('Error', 'Please enter a valid email address.', 'error');
            return;
        }

        showOverlay('Sending invitation...');

        fetch('../backend/department/appoint-secretary.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                removeOverlay();
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('appointSecretaryModal'));
                    modal.hide();
                    showToast('Success', data.message, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showToast('Error', data.message || 'Failed to send invitation.', 'error');
                }
            })
            .catch(error => {
                removeOverlay();
                console.error('Error:', error);
                showToast('Error', 'An error occurred. Please try again.', 'error');
            });
    }

    function resendInvitation() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('resendInvitationModal'));
        modal.hide();

        showOverlay('Resending invitation...');

        fetch('../backend/department/resend-secretary-invitation.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                removeOverlay();
                if (data.success) {
                    showToast('Success', data.message, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showToast('Error', data.message || 'Failed to resend invitation.', 'error');
                }
            })
            .catch(error => {
                removeOverlay();
                console.error('Error:', error);
                showToast('Error', 'An error occurred. Please try again.', 'error');
            });
    }

    function cancelInvitation() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('cancelInvitationModal'));
        modal.hide();

        showOverlay('Canceling invitation...');

        fetch('../backend/department/cancel-secretary-invitation.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                removeOverlay();
                if (data.success) {
                    showToast('Success', data.message, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showToast('Error', data.message || 'Failed to cancel invitation.', 'error');
                }
            })
            .catch(error => {
                removeOverlay();
                console.error('Error:', error);
                showToast('Error', 'An error occurred. Please try again.', 'error');
            });
    }

    function replaceSecretary() {
        const firstName = document.getElementById('newSecretaryFirstName').value.trim();
        const lastName = document.getElementById('newSecretaryLastName').value.trim();
        const email = document.getElementById('newSecretaryEmail').value.trim();
        const reason = document.getElementById('replaceReason').value.trim();

        if (!firstName || !lastName || !email || !reason) {
            showToast('Error', 'Please fill in all required fields.', 'error');
            return;
        }

        if (!isValidEmail(email)) {
            showToast('Error', 'Please enter a valid email address.', 'error');
            return;
        }

        const modal = bootstrap.Modal.getInstance(document.getElementById('replaceSecretaryModal'));
        modal.hide();

        showOverlay('Processing replacement...');

        fetch('../backend/department/replace-secretary.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    first_name: firstName,
                    last_name: lastName,
                    email: email,
                    reason: reason
                })
            })
            .then(response => response.json())
            .then(data => {
                removeOverlay();
                if (data.success) {
                    showToast('Success', data.message, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showToast('Error', data.message || 'Failed to replace secretary.', 'error');
                }
            })
            .catch(error => {
                removeOverlay();
                console.error('Error:', error);
                showToast('Error', 'An error occurred. Please try again.', 'error');
            });
    }

    function removeSecretary() {
        const reasonField = document.getElementById('removeReason');
        const confirmCheckbox = document.getElementById('confirmRemoval');

        const reason = reasonField ? reasonField.value.trim() : '';
        const confirmed = confirmCheckbox ? confirmCheckbox.checked : false;

        // Debugging: Log the state of inputs
        console.log('Remove Secretary - Reason:', reason);
        console.log('Remove Secretary - Confirmed:', confirmed);

        // Clear any existing validation feedback
        if (reasonField) {
            reasonField.classList.remove('is-invalid');
            const existingFeedback = reasonField.parentNode.querySelector('.invalid-feedback');
            if (existingFeedback) existingFeedback.remove();
        }

        // Validate inputs
        let errorMessages = [];
        if (!reason) {
            errorMessages.push('Please provide a reason for removal.');
            if (reasonField) {
                reasonField.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = 'Reason for removal is required.';
                reasonField.parentNode.appendChild(feedback);
            }
        }
        if (!confirmed) {
            errorMessages.push('Please confirm the removal action.');
            if (confirmCheckbox) {
                confirmCheckbox.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = 'You must confirm this action.';
                confirmCheckbox.parentNode.appendChild(feedback);
            }
        }

        if (errorMessages.length > 0) {
            showToast('Error', errorMessages.join(' '), 'error');
            return;
        }

        const modal = bootstrap.Modal.getInstance(document.getElementById('removeSecretaryModal'));
        modal.hide();

        showOverlay('Removing secretary...');

        fetch('../backend/department/remove-secretary.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    reason: reason
                })
            })
            .then(response => response.json())
            .then(data => {
                removeOverlay();
                if (data.success) {
                    showToast('Success', data.message, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showToast('Error', data.message || 'Failed to remove secretary.', 'error');
                }
            })
            .catch(error => {
                removeOverlay();
                console.error('Error:', error);
                showToast('Error', 'An error occurred. Please try again.', 'error');
            });
    }

    function sendMessage() {
        const subject = document.getElementById('messageSubject').value.trim();
        const body = document.getElementById('messageBody').value.trim();
        const ccMyself = document.getElementById('ccMyselfCheck').checked;

        if (!subject || !body) {
            showToast('Error', 'Please fill in all required fields.', 'error');
            return;
        }

        const modal = bootstrap.Modal.getInstance(document.getElementById('contactSecretaryModal'));
        modal.hide();

        showOverlay('Sending message...');

        fetch('../backend/department/contact-secretary.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    subject: subject,
                    body: body,
                    cc_myself: ccMyself
                })
            })
            .then(response => response.json())
            .then(data => {
                removeOverlay();
                if (data.success) {
                    showToast('Success', data.message, 'success');
                    // Clear form
                    document.getElementById('contactSecretaryForm').reset();
                } else {
                    showToast('Error', data.message || 'Failed to send message.', 'error');
                }
            })
            .catch(error => {
                removeOverlay();
                console.error('Error:', error);
                showToast('Error', 'An error occurred. Please try again.', 'error');
            });
    }

    // Modal chain handling for view -> contact
    function openContactFromView() {
        // Close view modal first
        const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewSecretaryModal'));
        if (viewModal) {
            viewModal.hide();
        }

        // Open contact modal after view modal closes
        setTimeout(() => {
            const contactModal = new bootstrap.Modal(document.getElementById('contactSecretaryModal'));
            contactModal.show();
        }, 300);
    }

    // Form validation helpers
    function setupFormValidation() {
        // Email validation
        const emailFields = ['secretaryEmail', 'newSecretaryEmail'];
        emailFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('blur', function() {
                    if (this.value && !isValidEmail(this.value)) {
                        this.classList.add('is-invalid');
                        let feedback = this.parentNode.querySelector('.invalid-feedback');
                        if (!feedback) {
                            feedback = document.createElement('div');
                            feedback.className = 'invalid-feedback';
                            feedback.textContent = 'Please enter a valid email address.';
                            this.parentNode.appendChild(feedback);
                        }
                    } else {
                        this.classList.remove('is-invalid');
                        const feedback = this.parentNode.querySelector('.invalid-feedback');
                        if (feedback) {
                            feedback.remove();
                        }
                    }
                });

                field.addEventListener('input', function() {
                    if (this.classList.contains('is-invalid') && isValidEmail(this.value)) {
                        this.classList.remove('is-invalid');
                        const feedback = this.parentNode.querySelector('.invalid-feedback');
                        if (feedback) {
                            feedback.remove();
                        }
                    }
                });
            }
        });

        // Character counter for text areas
        const reasonFields = ['replaceReason', 'removeReason'];
        reasonFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                const maxLength = 500;
                const counter = field.parentNode.querySelector('.form-text');

                if (counter) {
                    counter.textContent = `0/${maxLength} characters`;
                }

                field.addEventListener('input', function() {
                    const length = this.value.length;
                    if (counter) {
                        counter.textContent = `${length}/${maxLength} characters`;
                    }

                    if (length > maxLength) {
                        this.classList.add('is-invalid');
                        if (counter) {
                            counter.classList.remove('text-muted');
                            counter.classList.add('text-danger');
                        }
                    } else {
                        this.classList.remove('is-invalid');
                        if (counter) {
                            counter.classList.remove('text-danger');
                            counter.classList.add('text-muted');
                        }
                    }
                });
            }
        });

        // Checkbox validation for confirmRemoval
        const confirmCheckbox = document.getElementById('confirmRemoval');
        if (confirmCheckbox) {
            confirmCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    this.classList.remove('is-invalid');
                    const feedback = this.parentNode.querySelector('.invalid-feedback');
                    if (feedback) feedback.remove();
                }
            });
        }
    }

    // Clear forms when modals close
    function setupModalCleanup() {
        const modalsToClean = [
            'appointSecretaryModal',
            'replaceSecretaryModal',
            'removeSecretaryModal',
            'contactSecretaryModal'
        ];

        modalsToClean.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.addEventListener('hidden.bs.modal', function() {
                    const form = this.querySelector('form');
                    if (form) {
                        form.reset();

                        // Remove validation classes
                        const invalidFields = form.querySelectorAll('.is-invalid');
                        invalidFields.forEach(field => field.classList.remove('is-invalid'));

                        // Remove feedback messages
                        const feedbacks = form.querySelectorAll('.invalid-feedback');
                        feedbacks.forEach(feedback => feedback.remove());

                        // Reset character counter
                        const counter = form.querySelector('.form-text');
                        if (counter) {
                            counter.textContent = '0/500 characters';
                            counter.classList.remove('text-danger');
                            counter.classList.add('text-muted');
                        }
                    }
                });
            }
        });
    }

    // Toast cleanup
    function setupToastCleanup() {
        // Clean up toasts when user clicks close button
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-close') && e.target.closest('.toast')) {
                const toast = e.target.closest('.toast');
                toast.remove();
            }
        });
    }

    // Initialize everything when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all functionality
        initializeDropdowns();
        setupFormValidation();
        setupModalCleanup();
        setupToastCleanup();

        // Handle view modal contact button click
        const viewModalContactBtn = document.querySelector('#viewSecretaryModal .btn-primary');
        if (viewModalContactBtn) {
            viewModalContactBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                openContactFromView();
            });
        }

        // Handle keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Escape key closes all modals and dropdowns
            if (e.key === 'Escape') {
                // Close all open dropdowns
                const openDropdowns = document.querySelectorAll('.dropdown-menu.show');
                openDropdowns.forEach(dropdown => {
                    const toggle = dropdown.previousElementSibling;
                    if (toggle) {
                        const bootstrapDropdown = bootstrap.Dropdown.getInstance(toggle);
                        if (bootstrapDropdown) {
                            bootstrapDropdown.hide();
                        }
                    }
                });
            }
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                const openDropdowns = document.querySelectorAll('.dropdown-menu.show');
                openDropdowns.forEach(dropdown => {
                    const toggle = dropdown.previousElementSibling;
                    if (toggle) {
                        const bootstrapDropdown = bootstrap.Dropdown.getInstance(toggle);
                        if (bootstrapDropdown) {
                            bootstrapDropdown.hide();
                        }
                    }
                });
            }
        });

        console.log('Secretary management interface initialized successfully');
    });
</script>
<!-- Bootstrap JS Bundle (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-..." crossorigin="anonymous"></script>

<?php include '../includes/department/footer.php'; ?>