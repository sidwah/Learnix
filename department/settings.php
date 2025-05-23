<?php // department/settings.php ?>
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
        
        <!-- Toast -->
        <div id="liveToast" class="position-fixed toast hide" role="alert" aria-live="assertive" aria-atomic="true" style="top: 20px; right: 20px; z-index: 1000;">
            <div class="toast-header">
                <div class="d-flex align-items-center flex-grow-1">
                    <div id="toastIcon" class="flex-shrink-0 rounded-circle bg-success bg-soft text-success p-2 d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px;">
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
            <div id="toastBody" class="toast-body"></div>
        </div>
        <!-- End Toast -->

        <?php
        include '../backend/config.php';
        
        // Get current user role and department info
        $user_id = $_SESSION['user_id'] ?? 0;
        $department_id = $_SESSION['department_id'] ?? 0;
        
        if (!$department_id || !$user_id) {
            echo '<div class="alert alert-warning">Access denied. Please contact system administrator.</div>';
            exit;
        }
        
        // Check user role in department (only head can access settings)
        $role_query = "SELECT ds.role, ds.status FROM department_staff ds 
                       WHERE ds.user_id = ? AND ds.department_id = ? AND ds.deleted_at IS NULL AND ds.status = 'active'";
        $stmt = $conn->prepare($role_query);
        $stmt->bind_param("ii", $user_id, $department_id);
        $stmt->execute();
        $role_result = $stmt->get_result();
        $user_dept_role = $role_result->fetch_assoc();
        $stmt->close();
        
        if (!$user_dept_role || $user_dept_role['role'] !== 'head') {
            echo '<div class="alert alert-danger">Access denied. Only department heads can access settings.</div>';
            exit;
        }
        
        // Get department information and settings
        $dept_query = "SELECT d.*, ds.invitation_expiry_hours, ds.auto_approve_instructors, ds.require_mfa, ds.email_notifications_enabled 
                       FROM departments d 
                       LEFT JOIN department_settings ds ON d.department_id = ds.department_id 
                       WHERE d.department_id = ?";
        $stmt = $conn->prepare($dept_query);
        $stmt->bind_param("i", $department_id);
        $stmt->execute();
        $dept_result = $stmt->get_result();
        $department = $dept_result->fetch_assoc();
        $stmt->close();
        
        // Set default values if no settings exist
        $settings = [
            'invitation_expiry_hours' => $department['invitation_expiry_hours'] ?? 48,
            'auto_approve_instructors' => $department['auto_approve_instructors'] ?? 0,
            'require_mfa' => $department['require_mfa'] ?? 1,
            'email_notifications_enabled' => $department['email_notifications_enabled'] ?? 1
        ];
        ?>

        <!-- Page Header -->
        <div class="page-header mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="page-header-title h2 mb-2">Department Settings</h1>
                    <p class="page-header-text text-muted mb-0">
                        Configure your department preferences and policies
                    </p>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Settings Navigation Tabs -->
        <ul class="nav nav-tabs nav-fill mb-4" id="settingsTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                    <i class="bi bi-gear me-2"></i>General Settings
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="invitations-tab" data-bs-toggle="tab" data-bs-target="#invitations" type="button" role="tab">
                    <i class="bi bi-envelope me-2"></i>Invitations
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                    <i class="bi bi-shield-lock me-2"></i>Security
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab">
                    <i class="bi bi-bell me-2"></i>Notifications
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="settingsTabContent">
            
            <!-- General Settings Tab -->
            <div class="tab-pane fade show active" id="general" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h4 class="card-title mb-0">Department Information</h4>
                        <p class="text-muted mb-0">Basic department details and description</p>
                    </div>
                    <div class="card-body">
                        <form id="generalSettingsForm" method="POST" action="../backend/department/update-general-settings.php">
                            <input type="hidden" name="department_id" value="<?php echo $department_id; ?>">
                            <input type="hidden" name="original_description" value="<?php echo htmlspecialchars($department['description'] ?? ''); ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="department_name" class="form-label fw-medium">Department Name</label>
                                    <input type="text" class="form-control bg-light" id="department_name" 
                                           value="<?php echo htmlspecialchars($department['name']); ?>" readonly>
                                    <small class="text-muted">Managed by institution administrator</small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="department_code" class="form-label fw-medium">Department Code</label>
                                    <input type="text" class="form-control bg-light" id="department_code" 
                                           value="<?php echo htmlspecialchars($department['code']); ?>" readonly>
                                    <small class="text-muted">Managed by institution administrator</small>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="department_description" class="form-label fw-medium">Department Description</label>
                                <textarea class="form-control" id="department_description" name="department_description" 
                                          rows="4" maxlength="1000" onInput="checkGeneralChanges()"><?php echo htmlspecialchars($department['description'] ?? ''); ?></textarea>
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">Describe your department's mission and activities</small>
                                    <small class="text-muted">
                                        <span id="charCount"><?php echo strlen($department['description'] ?? ''); ?></span>/1000
                                    </small>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" id="saveGeneralBtn" class="btn btn-primary" disabled>
                                    <i class="bi bi-check-circle me-2"></i>Save Changes
                                </button>
                                <button type="button" id="resetGeneralBtn" class="btn btn-outline-secondary" onclick="resetGeneralForm()" disabled>
                                    <i class="bi bi-arrow-clockwise me-2"></i>Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Invitations Settings Tab -->
            <div class="tab-pane fade" id="invitations" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h4 class="card-title mb-0">Invitation Settings</h4>
                        <p class="text-muted mb-0">Configure how invitations are sent and managed</p>
                    </div>
                    <div class="card-body">
                        <form id="invitationSettingsForm" method="POST" action="../backend/department/update-invitation-settings.php">
                            <input type="hidden" name="department_id" value="<?php echo $department_id; ?>">
                            <input type="hidden" name="original_expiry_hours" value="<?php echo $settings['invitation_expiry_hours']; ?>">
                            <input type="hidden" name="original_auto_approve" value="<?php echo $settings['auto_approve_instructors']; ?>">
                            
                            <div class="mb-4">
                                <label for="invitation_expiry_hours" class="form-label fw-medium">Invitation Expiry Time</label>
                                <select class="form-select" id="invitation_expiry_hours" name="invitation_expiry_hours" onchange="checkInvitationChanges()">
                                    <option value="24" <?php echo ($settings['invitation_expiry_hours'] == 24) ? 'selected' : ''; ?>>24 Hours</option>
                                    <option value="48" <?php echo ($settings['invitation_expiry_hours'] == 48) ? 'selected' : ''; ?>>48 Hours (2 Days)</option>
                                    <option value="72" <?php echo ($settings['invitation_expiry_hours'] == 72) ? 'selected' : ''; ?>>72 Hours (3 Days)</option>
                                    <option value="168" <?php echo ($settings['invitation_expiry_hours'] == 168) ? 'selected' : ''; ?>>1 Week</option>
                                    <option value="336" <?php echo ($settings['invitation_expiry_hours'] == 336) ? 'selected' : ''; ?>>2 Weeks</option>
                                </select>
                                <small class="text-muted">How long invitation links remain valid for secretaries and instructors</small>
                            </div>
                            
                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="auto_approve_instructors" 
                                           name="auto_approve_instructors" value="1" onchange="checkInvitationChanges()"
                                           <?php echo $settings['auto_approve_instructors'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="auto_approve_instructors">
                                        <span class="fw-medium">Auto-approve Instructor Invitations</span>
                                        <span class="d-block text-muted small">Automatically activate instructors when they accept invitations</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="alert alert-soft-info d-flex align-items-start">
                                <i class="bi bi-info-circle me-2 mt-1"></i>
                                <div>
                                    <strong>Invitation Workflow:</strong>
                                    <ul class="mb-0 mt-1">
                                        <li>Secretary and instructor invitations will expire after the selected time</li>
                                        <li>Expired invitations can be resent with a new expiry time</li>
                                        <li>You'll receive notifications when invitations are about to expire</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" id="saveInvitationBtn" class="btn btn-primary" disabled>
                                    <i class="bi bi-check-circle me-2"></i>Save Changes
                                </button>
                                <button type="button" id="resetInvitationBtn" class="btn btn-outline-secondary" onclick="resetInvitationForm()" disabled>
                                    <i class="bi bi-arrow-clockwise me-2"></i>Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Security Settings Tab -->
            <div class="tab-pane fade" id="security" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h4 class="card-title mb-0">Security Settings</h4>
                        <p class="text-muted mb-0">Configure security requirements for department staff</p>
                    </div>
                    <div class="card-body">
                        <form id="securitySettingsForm" method="POST" action="../backend/department/update-security-settings.php">
                            <input type="hidden" name="department_id" value="<?php echo $department_id; ?>">
                            <input type="hidden" name="original_require_mfa" value="<?php echo $settings['require_mfa']; ?>">
                            
                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="require_mfa" 
                                           name="require_mfa" value="1" onchange="checkSecurityChanges()"
                                           <?php echo $settings['require_mfa'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="require_mfa">
                                        <span class="fw-medium">Require Multi-Factor Authentication</span>
                                        <span class="d-block text-muted small">All department staff must enable MFA on their accounts</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="alert alert-soft-warning d-flex align-items-start">
                                <i class="bi bi-shield-exclamation me-2 mt-1"></i>
                                <div>
                                    <strong>Security Notice:</strong>
                                    <p class="mb-0 mt-1">
                                        Enabling MFA requirement will apply to all current and future department staff. 
                                        Existing staff will be prompted to set up MFA on their next login.
                                    </p>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" id="saveSecurityBtn" class="btn btn-primary" disabled>
                                    <i class="bi bi-check-circle me-2"></i>Save Changes
                                </button>
                                <button type="button" id="resetSecurityBtn" class="btn btn-outline-secondary" onclick="resetSecurityForm()" disabled>
                                    <i class="bi bi-arrow-clockwise me-2"></i>Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Notifications Settings Tab -->
            <div class="tab-pane fade" id="notifications" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h4 class="card-title mb-0">Notification Preferences</h4>
                        <p class="text-muted mb-0">Configure how you receive department notifications</p>
                    </div>
                    <div class="card-body">
                        <form id="notificationSettingsForm" method="POST" action="../backend/department/update-notification-settings.php">
                            <input type="hidden" name="department_id" value="<?php echo $department_id; ?>">
                            <input type="hidden" name="original_email_notifications" value="<?php echo $settings['email_notifications_enabled']; ?>">
                            
                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="email_notifications_enabled" 
                                           name="email_notifications_enabled" value="1" onchange="checkNotificationChanges()"
                                           <?php echo $settings['email_notifications_enabled'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="email_notifications_enabled">
                                        <span class="fw-medium">Email Notifications</span>
                                        <span class="d-block text-muted small">Receive email notifications for important department events</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <h6 class="mb-3">Notification Types</h6>
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item px-0">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-person-plus text-primary me-3"></i>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">New Staff Invitations</h6>
                                                <small class="text-muted">When secretaries or instructors accept invitations</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="list-group-item px-0">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-book text-success me-3"></i>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">Course Updates</h6>
                                                <small class="text-muted">Course submissions, approvals, and changes</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="list-group-item px-0">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-clock text-warning me-3"></i>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">Invitation Expiry Warnings</h6>
                                                <small class="text-muted">24 hours before invitations expire</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="list-group-item px-0">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-exclamation-triangle text-danger me-3"></i>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">System Alerts</h6>
                                                <small class="text-muted">Important system announcements and security alerts</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" id="saveNotificationBtn" class="btn btn-primary" disabled>
                                    <i class="bi bi-check-circle me-2"></i>Save Changes
                                </button>
                                <button type="button" id="resetNotificationBtn" class="btn btn-outline-secondary" onclick="resetNotificationForm()" disabled>
                                    <i class="bi bi-arrow-clockwise me-2"></i>Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>

    </div>
    <!-- End Content -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- JavaScript -->
<script>
// Store original values
const originalDescription = `<?php echo addslashes($department['description'] ?? ''); ?>`;
const originalExpiryHours = <?php echo $settings['invitation_expiry_hours']; ?>;
const originalAutoApprove = <?php echo $settings['auto_approve_instructors']; ?>;
const originalRequireMfa = <?php echo $settings['require_mfa']; ?>;
const originalEmailNotifications = <?php echo $settings['email_notifications_enabled']; ?>;

// Form handlers
document.addEventListener('DOMContentLoaded', function() {
    // General form
    const generalForm = document.getElementById('generalSettingsForm');
    if (generalForm) {
        generalForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm(this, 'General settings updated successfully!');
        });
    }
    
    // Invitation form
    const invitationForm = document.getElementById('invitationSettingsForm');
    if (invitationForm) {
        invitationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm(this, 'Invitation settings updated successfully!');
        });
    }
    
    // Security form
    const securityForm = document.getElementById('securitySettingsForm');
    if (securityForm) {
        securityForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm(this, 'Security settings updated successfully!');
        });
    }
    
    // Notification form
    const notificationForm = document.getElementById('notificationSettingsForm');
    if (notificationForm) {
        notificationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm(this, 'Notification settings updated successfully!');
        });
    }
    
    // Character count for description
    const descTextarea = document.getElementById('department_description');
    if (descTextarea) {
        descTextarea.addEventListener('input', function() {
            document.getElementById('charCount').textContent = this.value.length;
        });
    }
});

// Check for changes functions
function checkGeneralChanges() {
    const currentDescription = document.getElementById('department_description').value;
    const hasChanges = currentDescription !== originalDescription;
    
    document.getElementById('saveGeneralBtn').disabled = !hasChanges;
    document.getElementById('resetGeneralBtn').disabled = !hasChanges;
}

function checkInvitationChanges() {
    const currentExpiryHours = parseInt(document.getElementById('invitation_expiry_hours').value);
    const currentAutoApprove = document.getElementById('auto_approve_instructors').checked ? 1 : 0;
    
    const hasChanges = currentExpiryHours !== originalExpiryHours || currentAutoApprove !== originalAutoApprove;
    
    document.getElementById('saveInvitationBtn').disabled = !hasChanges;
    document.getElementById('resetInvitationBtn').disabled = !hasChanges;
}

function checkSecurityChanges() {
    const currentRequireMfa = document.getElementById('require_mfa').checked ? 1 : 0;
    const hasChanges = currentRequireMfa !== originalRequireMfa;
    
    document.getElementById('saveSecurityBtn').disabled = !hasChanges;
    document.getElementById('resetSecurityBtn').disabled = !hasChanges;
}

function checkNotificationChanges() {
    const currentEmailNotifications = document.getElementById('email_notifications_enabled').checked ? 1 : 0;
    const hasChanges = currentEmailNotifications !== originalEmailNotifications;
    
    document.getElementById('saveNotificationBtn').disabled = !hasChanges;
    document.getElementById('resetNotificationBtn').disabled = !hasChanges;
}

// Reset functions
function resetGeneralForm() {
    document.getElementById('department_description').value = originalDescription;
    document.getElementById('charCount').textContent = originalDescription.length;
    checkGeneralChanges();
}

function resetInvitationForm() {
    document.getElementById('invitation_expiry_hours').value = originalExpiryHours;
    document.getElementById('auto_approve_instructors').checked = originalAutoApprove === 1;
    checkInvitationChanges();
}

function resetSecurityForm() {
    document.getElementById('require_mfa').checked = originalRequireMfa === 1;
    checkSecurityChanges();
}

function resetNotificationForm() {
    document.getElementById('email_notifications_enabled').checked = originalEmailNotifications === 1;
    checkNotificationChanges();
}

// Submit form function
function submitForm(form, successMessage) {
    showOverlay('Saving changes...');
    
    fetch(form.action, {
        method: 'POST',
        body: new FormData(form)
    })
    .then(response => response.json())
    .then(data => {
        removeOverlay();
        if (data.success) {
            showToast('success', successMessage);
            
            // Update original values based on form type
            if (form.id === 'generalSettingsForm') {
                window.originalDescription = document.getElementById('department_description').value;
                checkGeneralChanges();
            } else if (form.id === 'invitationSettingsForm') {
                window.originalExpiryHours = parseInt(document.getElementById('invitation_expiry_hours').value);
                window.originalAutoApprove = document.getElementById('auto_approve_instructors').checked ? 1 : 0;
                checkInvitationChanges();
            } else if (form.id === 'securitySettingsForm') {
                window.originalRequireMfa = document.getElementById('require_mfa').checked ? 1 : 0;
                checkSecurityChanges();
            } else if (form.id === 'notificationSettingsForm') {
                window.originalEmailNotifications = document.getElementById('email_notifications_enabled').checked ? 1 : 0;
                checkNotificationChanges();
            }
        } else {
            showToast('error', data.message || 'An error occurred');
        }
    })
    .catch(error => {
        removeOverlay();
        showToast('error', 'Network error occurred');
        console.error('Error:', error);
    });
}

// Toast function
function showToast(type, message) {
    const toast = document.getElementById('liveToast');
    const toastIcon = document.getElementById('toastIcon');
    const toastTitle = document.getElementById('toastTitle');
    const toastBody = document.getElementById('toastBody');
    const toastTime = document.getElementById('toastTime');
    
    toastBody.textContent = message;
    toastTime.textContent = 'Just now';
    
    if (type === 'success') {
        toastIcon.className = 'flex-shrink-0 rounded-circle bg-success-soft text-success p-2 d-flex align-items-center justify-content-center me-2';
        toastIcon.innerHTML = '<i class="bi bi-check-lg fs-6"></i>';
        toastTitle.textContent = 'Success';
    } else {
        toastIcon.className = 'flex-shrink-0 rounded-circle bg-danger-soft text-danger p-2 d-flex align-items-center justify-content-center me-2';
        toastIcon.innerHTML = '<i class="bi bi-exclamation-triangle fs-6"></i>';
        toastTitle.textContent = 'Error';
    }
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
}

// Loading overlay functions
function showOverlay(message = null) {
    const existingOverlay = document.querySelector('.custom-overlay');
    if (existingOverlay) {
        existingOverlay.remove();
    }

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
    if (overlay) {
        overlay.remove();
    }
}
</script>

<?php include '../includes/department/footer.php'; ?>