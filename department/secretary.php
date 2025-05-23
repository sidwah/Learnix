<?php // department/secretary.php ?>
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
        
        <!-- Toast Container -->
        <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;"></div>

        <?php
        include '../backend/config.php';
        
        // Get current user role and department info
        $user_id = $_SESSION['user_id'] ?? 0;
        $department_id = $_SESSION['department_id'] ?? 0;
        
        if (!$department_id || !$user_id) {
            echo '<div class="alert alert-warning">Access denied. Please contact system administrator.</div>';
            exit;
        }
        
        // Check user role in department (only head can manage secretary)
        $role_query = "SELECT ds.role, ds.status FROM department_staff ds 
                       WHERE ds.user_id = ? AND ds.department_id = ? AND ds.deleted_at IS NULL AND ds.status = 'active'";
        $stmt = $conn->prepare($role_query);
        $stmt->bind_param("ii", $user_id, $department_id);
        $stmt->execute();
        $role_result = $stmt->get_result();
        $user_dept_role = $role_result->fetch_assoc();
        $stmt->close();
        
        if (!$user_dept_role || $user_dept_role['role'] !== 'head') {
            echo '<div class="alert alert-danger">Access denied. Only department heads can manage secretaries.</div>';
            exit;
        }
        
        // Get department information and settings
        $dept_query = "SELECT d.*, ds.invitation_expiry_hours 
                       FROM departments d 
                       LEFT JOIN department_settings ds ON d.department_id = ds.department_id 
                       WHERE d.department_id = ?";
        $stmt = $conn->prepare($dept_query);
        $stmt->bind_param("i", $department_id);
        $stmt->execute();
        $dept_result = $stmt->get_result();
        $department = $dept_result->fetch_assoc();
        $stmt->close();
        
        $expiry_hours = $department['invitation_expiry_hours'] ?? 48;
        
        // Check for active secretary
        $secretary_query = "SELECT u.*, ds.appointment_date, ds.status, ds.staff_id
                           FROM department_staff ds
                           JOIN users u ON ds.user_id = u.user_id
                           WHERE ds.department_id = ? AND ds.role = 'secretary' 
                           AND ds.status = 'active' AND ds.deleted_at IS NULL";
        $stmt = $conn->prepare($secretary_query);
        $stmt->bind_param("i", $department_id);
        $stmt->execute();
        $secretary_result = $stmt->get_result();
        $current_secretary = $secretary_result->fetch_assoc();
        $stmt->close();
        
        // Check for pending invitation
        $pending_query = "SELECT *, 
                         CASE 
                             WHEN expiry_time <= NOW() THEN 'expired'
                             ELSE 'active'
                         END as status
                         FROM department_staff_invitations 
                         WHERE department_id = ? AND role = 'secretary' AND is_used = 0 
                         ORDER BY created_at DESC LIMIT 1";
        $stmt = $conn->prepare($pending_query);
        $stmt->bind_param("i", $department_id);
        $stmt->execute();
        $pending_result = $stmt->get_result();
        $pending_invitation = $pending_result->fetch_assoc();
        $stmt->close();
        
        // Determine current state
        $state = 'none'; // none, pending, active
        if ($current_secretary) {
            $state = 'active';
        } elseif ($pending_invitation) {
            $state = 'pending';
        }
        ?>

        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-end mb-3">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">Secretary Management</h1>
                    <p class="page-header-text">Manage your department secretary appointment and responsibilities</p>
                </div>
                <div class="col-sm-auto">
                    <?php if ($state === 'none'): ?>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#appointSecretaryModal">
                            <i class="bi bi-person-plus-fill me-2"></i>Appoint Secretary
                        </button>
                    <?php elseif ($state === 'pending'): ?>
                        <div class="btn-group">
                            <?php if ($pending_invitation['status'] === 'expired'): ?>
                                <button type="button" class="btn btn-outline-warning" onclick="showResendConfirmation()">
                                    <i class="bi bi-arrow-repeat me-2"></i>Resend Invitation
                                </button>
                            <?php endif; ?>
                            <button type="button" class="btn btn-outline-danger" onclick="showCancelConfirmation()">
                                <i class="bi bi-x-circle me-2"></i>Cancel Invitation
                            </button>
                        </div>
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
                        <h4 class="card-header-title">Current Status</h4>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if ($state === 'active'): ?>
                    <!-- Active Secretary -->
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
                                    <i class="bi bi-check-circle-fill me-1"></i>Active Secretary
                                </span>
                                <span class="badge bg-soft-info text-info">
                                    <i class="bi bi-calendar3 me-1"></i>Since: <?php echo date('M d, Y', strtotime($current_secretary['appointment_date'])); ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="dropdown">
                                <button type="button" class="btn btn-ghost-secondary btn-icon btn-sm rounded-circle" id="secretaryActionsDropdown" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#viewSecretaryModal">
                                        <i class="bi bi-eye dropdown-item-icon"></i> View Details
                                    </a>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#contactSecretaryModal">
                                        <i class="bi bi-envelope dropdown-item-icon"></i> Contact Secretary
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#replaceSecretaryModal">
                                        <i class="bi bi-person-fill-gear dropdown-item-icon"></i> Replace Secretary
                                    </a>
                                    <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#removeSecretaryModal">
                                        <i class="bi bi-person-dash dropdown-item-icon"></i> Remove Secretary
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                <?php elseif ($state === 'pending'): ?>
                    <!-- Pending Invitation -->
                    <div class="text-center py-4">
                        <div class="avatar avatar-xl avatar-soft-<?php echo $pending_invitation['status'] === 'expired' ? 'warning' : 'info'; ?> avatar-circle mx-auto mb-3">
                            <i class="bi bi-<?php echo $pending_invitation['status'] === 'expired' ? 'clock-history' : 'envelope-check'; ?> avatar-icon"></i>
                        </div>
                        <h5><?php echo $pending_invitation['status'] === 'expired' ? 'Invitation Expired' : 'Invitation Pending'; ?></h5>
                        <p class="text-muted">
                            Invitation sent to <strong><?php echo htmlspecialchars($pending_invitation['email']); ?></strong><br>
                            <small>
                                Sent: <?php echo date('M d, Y - H:i', strtotime($pending_invitation['created_at'])); ?><br>
                                <?php if ($pending_invitation['status'] === 'expired'): ?>
                                    <span class="text-warning">Expired: <?php echo date('M d, Y - H:i', strtotime($pending_invitation['expiry_time'])); ?></span>
                                <?php else: ?>
                                    <span class="text-info">Expires: <?php echo date('M d, Y - H:i', strtotime($pending_invitation['expiry_time'])); ?></span>
                                <?php endif; ?>
                            </small>
                        </p>
                        <div class="d-flex justify-content-center gap-2 mt-3">
                            <?php if ($pending_invitation['status'] === 'expired'): ?>
                                <button type="button" class="btn btn-outline-primary" onclick="showResendConfirmation()">
                                    <i class="bi bi-arrow-repeat me-2"></i>Resend Invitation
                                </button>
                            <?php endif; ?>
                            <button type="button" class="btn btn-outline-danger" onclick="showCancelConfirmation()">
                                <i class="bi bi-x-circle me-2"></i>Cancel Invitation
                            </button>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <!-- No Secretary -->
                    <div class="text-center py-4">
                        <div class="avatar avatar-xl avatar-soft-secondary avatar-circle mx-auto mb-3">
                            <i class="bi bi-person-plus avatar-icon"></i>
                        </div>
                        <h5>No Secretary Appointed</h5>
                        <p class="text-muted">You haven't appointed a secretary for your department yet.</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#appointSecretaryModal">
                            <i class="bi bi-person-plus-fill me-2"></i>Appoint Secretary
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- End Secretary Status Card -->

        <!-- Secretary Responsibilities Overview -->
        <?php if ($state === 'active' || $state === 'pending'): ?>
        <div class="card mb-5">
            <div class="card-header">
                <h4 class="card-header-title">Secretary Responsibilities</h4>
                <p class="text-muted mb-0">The following are the standard responsibilities for department secretaries</p>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" checked disabled>
                            <label class="form-check-label">
                                <strong>Administrative Support</strong>
                                <span class="d-block text-muted">Process paperwork and manage department records</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" checked disabled>
                            <label class="form-check-label">
                                <strong>Communication Management</strong>
                                <span class="d-block text-muted">Draft communications and handle correspondence</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" checked disabled>
                            <label class="form-check-label">
                                <strong>Course Support</strong>
                                <span class="d-block text-muted">Assist with course documentation and scheduling</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" checked disabled>
                            <label class="form-check-label">
                                <strong>Reporting Assistance</strong>
                                <span class="d-block text-muted">Generate standard reports and compile data</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="alert alert-info mt-4">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Note:</strong> These responsibilities are built into the system and cannot be modified. They represent the standard access and permissions granted to department secretaries.
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
    <!-- End Content -->

    <!-- Modals -->
    <!-- Appoint Secretary Modal -->
    <div class="modal fade" id="appointSecretaryModal" tabindex="-1" aria-labelledby="appointSecretaryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="appointSecretaryModalLabel">Appoint Department Secretary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="appointSecretaryForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="secretaryFirstName" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="secretaryFirstName" required>
                            </div>
                            <div class="col-md-6">
                                <label for="secretaryLastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="secretaryLastName" required>
                            </div>
                            <div class="col-12">
                                <label for="secretaryEmail" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="secretaryEmail" required>
                                <small class="text-muted">The invitation will be sent to this email address</small>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-4">
                            <h6 class="alert-heading">
                                <i class="bi bi-clock me-2"></i>Invitation Details
                            </h6>
                            <ul class="mb-0">
                                <li>Invitation will expire in <strong><?php echo $expiry_hours; ?> hours</strong></li>
                                <li>Temporary login credentials will be sent via email</li>
                                <li>Secretary must complete profile setup upon first login</li>
                                <li>You can resend or cancel the invitation if needed</li>
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
                        <p>Replacing the secretary will:</p>
                        <ul class="mb-0">
                            <li>Revoke current secretary's access immediately</li>
                            <li>Send notification to current secretary</li>
                            <li>Send invitation to new secretary</li>
                        </ul>
                    </div>
                    <form id="replaceSecretaryForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="newSecretaryFirstName" class="form-label">New Secretary First Name</label>
                                <input type="text" class="form-control" id="newSecretaryFirstName" required>
                            </div>
                            <div class="col-md-6">
                                <label for="newSecretaryLastName" class="form-label">New Secretary Last Name</label>
                                <input type="text" class="form-control" id="newSecretaryLastName" required>
                            </div>
                            <div class="col-12">
                                <label for="newSecretaryEmail" class="form-label">New Secretary Email</label>
                                <input type="email" class="form-control" id="newSecretaryEmail" required>
                            </div>
                            <div class="col-12">
                                <label for="replaceReason" class="form-label">Reason for Replacement</label>
                                <textarea class="form-control" id="replaceReason" rows="3" required 
                                         placeholder="Please provide a reason for the secretary replacement..."></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="replaceSecretary()">
                        <i class="bi bi-person-fill-gear me-2"></i>Replace Secretary
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
                        <p>This action will permanently remove the secretary position and revoke all access immediately.</p>
                    </div>
                    <form id="removeSecretaryForm">
                        <div class="mb-3">
                            <label for="removeReason" class="form-label">Reason for Removal</label>
                            <textarea class="form-control" id="removeReason" rows="3" required
                                     placeholder="Please provide a reason for removing the secretary..."></textarea>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confirmRemoval" required>
                            <label class="form-check-label" for="confirmRemoval">
                                I understand this action cannot be undone and will remove secretary access immediately
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="removeSecretary()">
                        <i class="bi bi-person-dash me-2"></i>Remove Secretary
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

    <!-- View Secretary Details Modal -->
    <?php if ($state === 'active'): ?>
    <div class="modal fade" id="viewSecretaryModal" tabindex="-1" aria-labelledby="viewSecretaryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewSecretaryModalLabel">Secretary Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
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
                                    <p><strong>Username</strong></p>
                                    <p class="text-muted"><?php echo htmlspecialchars($current_secretary['username']); ?></p>
                                </div>
                                <div class="col-sm-6">
                                    <p><strong>Last Updated</strong></p>
                                    <p class="text-muted"><?php echo date('M d, Y', strtotime($current_secretary['updated_at'])); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#contactSecretaryModal">
                        <i class="bi bi-envelope me-2"></i>Contact Secretary
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- JavaScript -->
<script>
    // Main Functions
    function appointSecretary() {
        const formData = {
            first_name: document.getElementById('secretaryFirstName').value.trim(),
            last_name: document.getElementById('secretaryLastName').value.trim(),
            email: document.getElementById('secretaryEmail').value.trim()
        };

        if (!formData.first_name || !formData.last_name || !formData.email) {
            showToast('error', 'Please fill in all required fields.');
            return;
        }

        if (!isValidEmail(formData.email)) {
            showToast('error', 'Please enter a valid email address.');
            return;
        }

        showOverlay('Sending invitation...');

        fetch('../backend/department/appoint-secretary.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        })
        .then(response => response.text())
        .then(text => {
            removeOverlay();
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    closeModal('appointSecretaryModal');
                    showToast('success', 'Secretary invitation sent successfully!');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showToast('error', data.message || 'Failed to send invitation.');
                }
            } catch (e) {
                console.error('JSON Parse Error:', e, 'Response:', text);
                showToast('error', 'Server error occurred. Please try again.');
            }
        })
        .catch(error => {
            removeOverlay();
            console.error('Error:', error);
            showToast('error', 'Network error occurred. Please try again.');
        });
    }

    function showResendConfirmation() {
        showConfirmationModal(
            'Resend Invitation',
            'Are you sure you want to resend the invitation? This will generate new login credentials and extend the expiry time.',
            'warning',
            'Resend Invitation',
            () => executeResendInvitation()
        );
    }

    function executeResendInvitation() {
        showOverlay('Resending invitation...');

        fetch('../backend/department/resend-secretary-invitation.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            removeOverlay();
            if (data.success) {
                showToast('success', 'Invitation resent successfully!');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showToast('error', data.message || 'Failed to resend invitation.');
            }
        })
        .catch(error => {
            removeOverlay();
            console.error('Error:', error);
            showToast('error', 'Network error occurred. Please try again.');
        });
    }

    function showCancelConfirmation() {
        showConfirmationModal(
            'Cancel Invitation',
            'Are you sure you want to cancel the invitation? This action cannot be undone and you will be able to appoint a new secretary.',
            'danger',
            'Cancel Invitation',
            () => executeCancelInvitation()
        );
    }

    function executeCancelInvitation() {
        showOverlay('Canceling invitation...');

        fetch('../backend/department/cancel-secretary-invitation.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            removeOverlay();
            if (data.success) {
                showToast('success', 'Invitation canceled successfully! You can now appoint a new secretary.');
                setTimeout(() => window.location.reload(), 1500);
           } else {
               showToast('error', data.message || 'Failed to cancel invitation.');
           }
       })
       .catch(error => {
           removeOverlay();
           console.error('Error:', error);
           showToast('error', 'Network error occurred. Please try again.');
       });
   }

   function replaceSecretary() {
       const formData = {
           first_name: document.getElementById('newSecretaryFirstName').value.trim(),
           last_name: document.getElementById('newSecretaryLastName').value.trim(),
           email: document.getElementById('newSecretaryEmail').value.trim(),
           reason: document.getElementById('replaceReason').value.trim()
       };

       if (!formData.first_name || !formData.last_name || !formData.email || !formData.reason) {
           showToast('error', 'Please fill in all required fields.');
           return;
       }

       if (!isValidEmail(formData.email)) {
           showToast('error', 'Please enter a valid email address.');
           return;
       }

       // Close the form modal first
       closeModal('replaceSecretaryModal');
       
       // Show confirmation modal
       showConfirmationModal(
           'Replace Secretary',
           'Are you sure you want to replace the current secretary? This action will immediately revoke their access and send an invitation to the new secretary.',
           'warning',
           'Replace Secretary',
           () => executeReplaceSecretary(formData)
       );
   }

   function executeReplaceSecretary(formData) {
       showOverlay('Processing replacement...');

       fetch('../backend/department/replace-secretary.php', {
           method: 'POST',
           headers: { 'Content-Type': 'application/json' },
           body: JSON.stringify(formData)
       })
       .then(response => response.json())
       .then(data => {
           removeOverlay();
           if (data.success) {
               showToast('success', 'Secretary replacement initiated successfully!');
               setTimeout(() => window.location.reload(), 1500);
           } else {
               showToast('error', data.message || 'Failed to replace secretary.');
           }
       })
       .catch(error => {
           removeOverlay();
           console.error('Error:', error);
           showToast('error', 'Network error occurred. Please try again.');
       });
   }

   function removeSecretary() {
       const reason = document.getElementById('removeReason').value.trim();
       const confirmed = document.getElementById('confirmRemoval').checked;

       if (!reason || !confirmed) {
           showToast('error', 'Please provide a reason and confirm the action.');
           return;
       }

       // Close the form modal first
       closeModal('removeSecretaryModal');

       // Show confirmation modal
       showConfirmationModal(
           'Remove Secretary',
           'This action will permanently remove the secretary position and revoke all access immediately. This cannot be undone.',
           'danger',
           'Remove Secretary',
           () => executeRemoveSecretary(reason)
       );
   }

   function executeRemoveSecretary(reason) {
       showOverlay('Removing secretary...');

       fetch('../backend/department/remove-secretary.php', {
           method: 'POST',
           headers: { 'Content-Type': 'application/json' },
           body: JSON.stringify({ reason })
       })
       .then(response => response.json())
       .then(data => {
           removeOverlay();
           if (data.success) {
               showToast('success', 'Secretary removed successfully!');
               setTimeout(() => window.location.reload(), 1500);
           } else {
               showToast('error', data.message || 'Failed to remove secretary.');
           }
       })
       .catch(error => {
           removeOverlay();
           console.error('Error:', error);
           showToast('error', 'Network error occurred. Please try again.');
       });
   }

   function sendMessage() {
       const formData = {
           subject: document.getElementById('messageSubject').value.trim(),
           body: document.getElementById('messageBody').value.trim(),
           cc_myself: document.getElementById('ccMyselfCheck').checked
       };

       if (!formData.subject || !formData.body) {
           showToast('error', 'Please fill in all required fields.');
           return;
       }

       showOverlay('Sending message...');

       fetch('../backend/department/contact-secretary.php', {
           method: 'POST',
           headers: { 'Content-Type': 'application/json' },
           body: JSON.stringify(formData)
       })
       .then(response => response.json())
       .then(data => {
           removeOverlay();
           if (data.success) {
               closeModal('contactSecretaryModal');
               showToast('success', 'Message sent successfully!');
               document.getElementById('contactSecretaryForm').reset();
           } else {
               showToast('error', data.message || 'Failed to send message.');
           }
       })
       .catch(error => {
           removeOverlay();
           console.error('Error:', error);
           showToast('error', 'Network error occurred. Please try again.');
       });
   }

   // Utility Functions
   function isValidEmail(email) {
       const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
       return emailRegex.test(email);
   }

   function closeModal(modalId) {
       const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
       if (modal) modal.hide();
   }

   function showConfirmationModal(title, message, type, confirmText, onConfirm) {
       // Remove existing confirmation modal if any
       const existingModal = document.getElementById('confirmationModal');
       if (existingModal) {
           existingModal.remove();
       }

       // Determine button and icon colors
       let buttonClass, iconClass;
       switch(type) {
           case 'danger':
               buttonClass = 'btn-danger';
               iconClass = 'bi-exclamation-triangle text-danger';
               break;
           case 'warning':
               buttonClass = 'btn-warning';
               iconClass = 'bi-exclamation-triangle text-warning';
               break;
           default:
               buttonClass = 'btn-primary';
               iconClass = 'bi-question-circle text-primary';
       }

       // Create modal HTML
       const modalHtml = `
           <div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
               <div class="modal-dialog">
                   <div class="modal-content">
                       <div class="modal-header">
                           <h5 class="modal-title">
                               <i class="${iconClass} me-2"></i>${title}
                           </h5>
                           <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                       </div>
                       <div class="modal-body">
                           <p>${message}</p>
                       </div>
                       <div class="modal-footer">
                           <button type="button" class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button>
                           <button type="button" class="btn ${buttonClass}" id="confirmActionBtn">${confirmText}</button>
                       </div>
                   </div>
               </div>
           </div>
       `;

       // Add modal to DOM
       document.body.insertAdjacentHTML('beforeend', modalHtml);

       // Get modal elements
       const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
       const confirmBtn = document.getElementById('confirmActionBtn');

       // Add event listener to confirm button
       confirmBtn.addEventListener('click', function() {
           modal.hide();
           onConfirm();
       });

       // Remove modal from DOM when hidden
       document.getElementById('confirmationModal').addEventListener('hidden.bs.modal', function() {
           this.remove();
       });

       // Show modal
       modal.show();
   }

   function showOverlay(message = null) {
       const existingOverlay = document.querySelector('.custom-overlay');
       if (existingOverlay) existingOverlay.remove();

       const overlay = document.createElement('div');
       overlay.className = 'custom-overlay position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center';
       overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
       overlay.style.zIndex = '9999';
       overlay.innerHTML = `
           <div class="text-center text-white">
               <div class="spinner-border text-primary mb-2" role="status">
                   <span class="visually-hidden">Loading...</span>
               </div>
               ${message ? `<div>${message}</div>` : ''}
           </div>
       `;
       document.body.appendChild(overlay);
   }

   function removeOverlay() {
       const overlay = document.querySelector('.custom-overlay');
       if (overlay) overlay.remove();
   }

   function showToast(type, message, title = null) {
       let toastContainer = document.getElementById('toast-container');
       if (!toastContainer) {
           toastContainer = document.createElement('div');
           toastContainer.id = 'toast-container';
           toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
           toastContainer.style.zIndex = '1055';
           document.body.appendChild(toastContainer);
       }

       const toastId = 'toast-' + Date.now();
       const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
       const titleText = title || (type === 'success' ? 'Success' : 'Error');

       const toastHtml = `
           <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
               <div class="toast-header">
                   <div class="rounded me-2 ${bgClass}" style="width: 20px; height: 20px;"></div>
                   <strong class="me-auto">${titleText}</strong>
                   <small>Just now</small>
                   <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
               </div>
               <div class="toast-body">${message}</div>
           </div>
       `;

       toastContainer.insertAdjacentHTML('beforeend', toastHtml);
       
       const toastElement = document.getElementById(toastId);
       const toast = new bootstrap.Toast(toastElement);
       toast.show();

       toastElement.addEventListener('hidden.bs.toast', function() {
           toastElement.remove();
       });
   }

   // Clear form when modals are hidden
   document.addEventListener('DOMContentLoaded', function() {
       ['appointSecretaryModal', 'replaceSecretaryModal', 'removeSecretaryModal', 'contactSecretaryModal'].forEach(modalId => {
           const modal = document.getElementById(modalId);
           if (modal) {
               modal.addEventListener('hidden.bs.modal', function() {
                   const form = modal.querySelector('form');
                   if (form) form.reset();
               });
           }
       });
   });
</script>

<?php include '../includes/department/footer.php'; ?>