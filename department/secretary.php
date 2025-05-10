<?php // department/appoint-secretary.php ?>
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
    <div class="navbar-sidebar-aside-content content-space-1 content-space-md-2 px-lg-5 px-xl-10">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-end mb-3">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">Secretary Management</h1>
                    <p class="page-header-text">Manage your department secretary appointment and permissions</p>
                </div>
                <div class="col-sm-auto">
                    <?php if (!isset($has_secretary) || !$has_secretary): ?>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#appointSecretaryModal">
                        <i class="bi bi-person-plus-fill me-2"></i>Appoint New Secretary
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
                        <h4 class="card-header-title">Current Secretary Status</h4>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (isset($current_secretary) && !empty($current_secretary)): ?>
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
                        <div class="dropdown">
                            <button type="button" class="btn btn-ghost-secondary btn-icon btn-sm rounded-circle" id="secretaryActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="secretaryActionsDropdown">
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#viewSecretaryModal">
                                    <i class="bi bi-eye dropdown-item-icon"></i> View Details
                                </a>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#updatePermissionsModal">
                                    <i class="bi bi-shield-check dropdown-item-icon"></i> Update Permissions
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
                
                <!-- Secretary Quick Stats -->
                <div class="row mt-4">
                    <div class="col-sm-6 col-md-3">
                        <div class="stats stats-sm">
                            <div class="stats-icon stats-icon-lg bg-soft-primary text-primary">
                                <i class="bi bi-folder-fill"></i>
                            </div>
                            <div class="stats-content">
                                <span class="stats-title">Documents Managed</span>
                                <span class="stats-number">145</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="stats stats-sm">
                            <div class="stats-icon stats-icon-lg bg-soft-success text-success">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="stats-content">
                                <span class="stats-title">Tasks Completed</span>
                                <span class="stats-number">89</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="stats stats-sm">
                            <div class="stats-icon stats-icon-lg bg-soft-info text-info">
                                <i class="bi bi-clock-fill"></i>
                            </div>
                            <div class="stats-content">
                                <span class="stats-title">Response Time</span>
                                <span class="stats-number">2.5h</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="stats stats-sm">
                            <div class="stats-icon stats-icon-lg bg-soft-warning text-warning">
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <div class="stats-content">
                                <span class="stats-title">Performance</span>
                                <span class="stats-number">4.8/5</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php elseif (isset($pending_invitation) && !empty($pending_invitation)): ?>
                <!-- Pending Invitation -->
                <div class="text-center py-4">
                    <div class="avatar avatar-xl avatar-soft-warning avatar-circle mx-auto mb-3">
                        <i class="bi bi-clock-history avatar-icon"></i>
                    </div>
                    <h5>Invitation Pending</h5>
                    <p class="text-muted">
                        An invitation has been sent to <strong><?php echo htmlspecialchars($pending_invitation['email']); ?></strong><br>
                        <small>Sent on <?php echo date('M d, Y - H:i', strtotime($pending_invitation['created_at'])); ?></small>
                    </p>
                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <button type="button" class="btn btn-outline-primary" onclick="resendInvitation()">
                            <i class="bi bi-arrow-repeat me-2"></i>Resend Invitation
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="cancelInvitation()">
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

        <!-- Secretary Permissions Overview -->
        <?php if (isset($current_secretary) && !empty($current_secretary)): ?>
        <div class="card mb-5">
            <div class="card-header">
                <h4 class="card-header-title">Secretary Permissions</h4>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="permInstructorMgmt" checked disabled>
                            <label class="form-check-label" for="permInstructorMgmt">
                                <strong>Instructor Management</strong>
                                <span class="d-block text-muted">Process instructor requests and documentation</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="permCourseMgmt" checked disabled>
                            <label class="form-check-label" for="permCourseMgmt">
                                <strong>Course Management</strong>
                                <span class="d-block text-muted">Update course metadata and scheduling</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="permCommunication" checked disabled>
                            <label class="form-check-label" for="permCommunication">
                                <strong>Communication</strong>
                                <span class="d-block text-muted">Draft communications and manage emails</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="permReporting" checked disabled>
                            <label class="form-check-label" for="permReporting">
                                <strong>Reporting</strong>
                                <span class="d-block text-muted">Generate and compile reports</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-4">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updatePermissionsModal">
                        <i class="bi bi-gear-fill me-2"></i>Manage Permissions
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-header-title">Recent Activity</h4>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="avatar avatar-sm avatar-soft-primary avatar-circle">
                                    <i class="bi bi-file-earmark-text"></i>
                                </div>
                            </div>
                            <div class="col">
                                <h6 class="mb-0">Processed instructor application</h6>
                                <p class="mb-0 text-muted">John Doe - Reviewed credentials and documentation</p>
                            </div>
                            <div class="col-auto">
                                <small class="text-muted">2 hours ago</small>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="avatar avatar-sm avatar-soft-success avatar-circle">
                                    <i class="bi bi-calendar-check"></i>
                                </div>
                            </div>
                            <div class="col">
                                <h6 class="mb-0">Updated course schedule</h6>
                                <p class="mb-0 text-muted">Advanced Mathematics - Added new time slots</p>
                            </div>
                            <div class="col-auto">
                                <small class="text-muted">5 hours ago</small>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="avatar avatar-sm avatar-soft-info avatar-circle">
                                    <i class="bi bi-envelope"></i>
                                </div>
                            </div>
                            <div class="col">
                                <h6 class="mb-0">Sent department announcement</h6>
                                <p class="mb-0 text-muted">End of semester exam schedule</p>
                            </div>
                            <div class="col-auto">
                                <small class="text-muted">Yesterday</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="#" class="link">View All Activity</a>
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
                    <h5 class="modal-title" id="appointSecretaryModalLabel">Appoint New Secretary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="appointSecretaryForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="secretaryFirstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="secretaryFirstName" required>
                            </div>
                            <div class="col-md-6">
                                <label for="secretaryLastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="secretaryLastName" required>
                            </div>
                            <div class="col-12">
                                <label for="secretaryEmail" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="secretaryEmail" required>
                            </div>
                            <div class="col-12">
                                <h6 class="mb-3">Permissions</h6>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="instructor_management" id="permInstructorNew" checked>
                                            <label class="form-check-label" for="permInstructorNew">
                                                Instructor Management
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="course_management" id="permCourseNew" checked>
                                            <label class="form-check-label" for="permCourseNew">
                                                Course Management
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="communication" id="permCommNew" checked>
                                            <label class="form-check-label" for="permCommNew">
                                                Communication
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="reporting" id="permReportNew" checked>
                                            <label class="form-check-label" for="permReportNew">
                                                Reporting
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                            <li>Revoke current secretary's access</li>
                            <li>Transfer all pending tasks to the new secretary</li>
                            <li>Send a notification to both parties</li>
                        </ul>
                    </div>
                    <form id="replaceSecretaryForm">
                        <div class="mb-3">
                            <label for="newSecretaryEmail" class="form-label">New Secretary Email</label>
                            <input type="email" class="form-control" id="newSecretaryEmail" required>
                        </div>
                        <div class="mb-3">
                            <label for="replaceReason" class="form-label">Reason for Replacement</label>
                            <textarea class="form-control" id="replaceReason" rows="3" required></textarea>
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
                        <p>This action will permanently remove the secretary position. You will need to appoint a new secretary if needed.</p>
                    </div>
                    <form id="removeSecretaryForm">
                        <div class="mb-3">
                            <label for="removeReason" class="form-label">Reason for Removal</label>
                            <textarea class="form-control" id="removeReason" rows="3" required></textarea>
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
    
    <!-- Update Permissions Modal -->
    <div class="modal fade" id="updatePermissionsModal" tabindex="-1" aria-labelledby="updatePermissionsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updatePermissionsModalLabel">Update Secretary Permissions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updatePermissionsForm">
                        <div class="mb-4">
                            <h6>Administrative Permissions</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="instructor_management" id="permInstructorEdit" checked>
                                <label class="form-check-label" for="permInstructorEdit">
                                    <strong>Instructor Management</strong>
                                    <span class="d-block text-muted small">Process instructor requests and documentation</span>
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="course_management" id="permCourseEdit" checked>
                                <label class="form-check-label" for="permCourseEdit">
                                    <strong>Course Management</strong>
                                    <span class="d-block text-muted small">Update course metadata and scheduling</span>
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="communication" id="permCommEdit" checked>
                                <label class="form-check-label" for="permCommEdit">
                                    <strong>Communication</strong>
                                    <span class="d-block text-muted small">Draft communications and manage emails</span>
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="reporting" id="permReportEdit" checked>
                                <label class="form-check-label" for="permReportEdit">
                                    <strong>Reporting</strong>
                                    <span class="d-block text-muted small">Generate and compile reports</span>
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h6>Advanced Permissions</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="user_management" id="permUserEdit">
                                <label class="form-check-label" for="permUserEdit">
                                    <strong>Basic User Management</strong>
                                    <span class="d-block text-muted small">View and assist with student accounts</span>
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="department_settings" id="permDeptEdit">
                                <label class="form-check-label" for="permDeptEdit">
                                    <strong>Department Settings</strong>
                                    <span class="d-block text-muted small">Update non-critical department settings</span>
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="updatePermissions()">
                        Save Changes
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
                                    <p><strong>Last Activity</strong></p>
                                    <p class="text-muted">2 hours ago</p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <h6>Contact Information</h6>
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item px-0">
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                <i class="bi bi-envelope"></i>
                                            </div>
                                            <div class="col">
                                                <p class="mb-0"><?php echo htmlspecialchars($current_secretary['email']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#contactSecretaryModal">
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

</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- JavaScript Functions -->
<script>
    function appointSecretary() {
        // Collect form data
        const formData = {
            first_name: document.getElementById('secretaryFirstName').value,
            last_name: document.getElementById('secretaryLastName').value,
            email: document.getElementById('secretaryEmail').value,
            permissions: {
                instructor_management: document.getElementById('permInstructorNew').checked,
                course_management: document.getElementById('permCourseNew').checked,
                communication: document.getElementById('permCommNew').checked,
                reporting: document.getElementById('permReportNew').checked
            }
        };

        // Add loading overlay
        showOverlay('Sending invitation...');

        // AJAX call to appoint secretary
        fetch('/backend/department/appoint-secretary.php', {
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
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('appointSecretaryModal'));
                modal.hide();
                
                // Show success message
                showToast('Success', 'Secretary invitation sent successfully!', 'success');
                
                // Reload page after 1 second
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast('Error', data.message || 'Failed to send invitation.', 'error');
            }
        })
        .catch(error => {
            removeOverlay();
            showToast('Error', 'An error occurred. Please try again.', 'error');
        });
    }

    function resendInvitation() {
        if (confirm('Are you sure you want to resend the invitation?')) {
            showOverlay('Resending invitation...');
            
            fetch('/backend/department/resend-secretary-invitation.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                removeOverlay();
                if (data.success) {
                    showToast('Success', 'Invitation resent successfully!', 'success');
                } else {
                    showToast('Error', data.message || 'Failed to resend invitation.', 'error');
                }
            })
            .catch(error => {
                removeOverlay();
                showToast('Error', 'An error occurred. Please try again.', 'error');
            });
        }
    }

    function cancelInvitation() {
        if (confirm('Are you sure you want to cancel the invitation?')) {
            showOverlay('Canceling invitation...');
            
            fetch('/backend/department/cancel-secretary-invitation.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                removeOverlay();
                if (data.success) {
                    showToast('Success', 'Invitation canceled successfully!', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showToast('Error', data.message || 'Failed to cancel invitation.', 'error');
                }
            })
            .catch(error => {
                removeOverlay();
                showToast('Error', 'An error occurred. Please try again.', 'error');
            });
        }
    }

    function replaceSecretary() {
        const newEmail = document.getElementById('newSecretaryEmail').value;
        const reason = document.getElementById('replaceReason').value;
        
        if (!newEmail || !reason) {
            showToast('Error', 'Please fill in all required fields.', 'error');
            return;
        }
        
        if (confirm('Are you sure you want to replace the current secretary?')) {
            showOverlay('Processing replacement...');
            
            fetch('/backend/department/replace-secretary.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    new_email: newEmail,
                    reason: reason
                })
            })
            .then(response => response.json())
            .then(data => {
                removeOverlay();
                if (data.success) {
                    showToast('Success', 'Secretary replacement initiated successfully!', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showToast('Error', data.message || 'Failed to replace secretary.', 'error');
                }
            })
            .catch(error => {
                removeOverlay();
                showToast('Error', 'An error occurred. Please try again.', 'error');
            });
        }
    }

    function removeSecretary() {
        const reason = document.getElementById('removeReason').value;
        const confirmed = document.getElementById('confirmRemoval').checked;
        
        if (!reason || !confirmed) {
            showToast('Error', 'Please fill in all required fields and confirm the action.', 'error');
            return;
        }
        
        showOverlay('Removing secretary...');
        
        fetch('/backend/department/remove-secretary.php', {
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
                showToast('Success', 'Secretary removed successfully!', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast('Error', data.message || 'Failed to remove secretary.', 'error');
            }
        })
        .catch(error => {
            removeOverlay();
            showToast('Error', 'An error occurred. Please try again.', 'error');
        });
    }

    function updatePermissions() {
        const permissions = {
            instructor_management: document.getElementById('permInstructorEdit').checked,
            course_management: document.getElementById('permCourseEdit').checked,
            communication: document.getElementById('permCommEdit').checked,
            reporting: document.getElementById('permReportEdit').checked,
            user_management: document.getElementById('permUserEdit').checked,
            department_settings: document.getElementById('permDeptEdit').checked
        };
        
        showOverlay('Updating permissions...');
        
        fetch('/backend/department/update-secretary-permissions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ permissions })
        })
        .then(response => response.json())
        .then(data => {
            removeOverlay();
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('updatePermissionsModal'));
                modal.hide();
                showToast('Success', 'Permissions updated successfully!', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast('Error', data.message || 'Failed to update permissions.', 'error');
            }
        })
        .catch(error => {
            removeOverlay();
            showToast('Error', 'An error occurred. Please try again.', 'error');
        });
    }

    function sendMessage() {
        const subject = document.getElementById('messageSubject').value;
        const body = document.getElementById('messageBody').value;
        const ccMyself = document.getElementById('ccMyselfCheck').checked;
        
        if (!subject || !body) {
            showToast('Error', 'Please fill in all required fields.', 'error');
            return;
        }
        
        showOverlay('Sending message...');
        
        fetch('/backend/department/contact-secretary.php', {
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
                const modal = bootstrap.Modal.getInstance(document.getElementById('contactSecretaryModal'));
                modal.hide();
                showToast('Success', 'Message sent successfully!', 'success');
            } else {
                showToast('Error', data.message || 'Failed to send message.', 'error');
            }
        })
        .catch(error => {
            removeOverlay();
            showToast('Error', 'An error occurred. Please try again.', 'error');
        });
    }
</script>

<?php include '../includes/department/footer.php'; ?>