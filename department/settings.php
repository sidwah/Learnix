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
        
        // Check user role in department
        $role_query = "SELECT ds.role, ds.status FROM department_staff ds 
                       WHERE ds.user_id = ? AND ds.department_id = ? AND ds.deleted_at IS NULL";
        $stmt = $conn->prepare($role_query);
        $stmt->bind_param("ii", $user_id, $department_id);
        $stmt->execute();
        $role_result = $stmt->get_result();
        $user_dept_role = $role_result->fetch_assoc();
        $stmt->close();
        
        if (!$user_dept_role) {
            echo '<div class="alert alert-danger">You do not have access to this department.</div>';
            exit;
        }
        
        $is_head = ($user_dept_role['role'] === 'head');
        $is_secretary = ($user_dept_role['role'] === 'secretary');
        
        // Get department information
        $dept_query = "SELECT * FROM departments WHERE department_id = ?";
        $stmt = $conn->prepare($dept_query);
        $stmt->bind_param("i", $department_id);
        $stmt->execute();
        $dept_result = $stmt->get_result();
        $department = $dept_result->fetch_assoc();
        $stmt->close();
        
        // Get department statistics
        $stats_query = "SELECT 
            (SELECT COUNT(*) FROM department_instructors WHERE department_id = ? AND status = 'active' AND deleted_at IS NULL) as instructor_count,
            (SELECT COUNT(*) FROM courses WHERE department_id = ? AND deleted_at IS NULL) as course_count,
            (SELECT COUNT(*) FROM courses WHERE department_id = ? AND status = 'Published' AND deleted_at IS NULL) as published_courses,
            (SELECT COUNT(DISTINCT e.user_id) FROM enrollments e 
             JOIN courses c ON e.course_id = c.course_id 
             WHERE c.department_id = ? AND e.deleted_at IS NULL) as student_count";
        $stmt = $conn->prepare($stats_query);
        $stmt->bind_param("iiii", $department_id, $department_id, $department_id, $department_id);
        $stmt->execute();
        $stats_result = $stmt->get_result();
        $stats = $stats_result->fetch_assoc();
        $stmt->close();
        ?>

        <!-- Page Header -->
        <div class="page-header mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="page-header-title h2 mb-2">Department Settings</h1>
                    <p class="page-header-text text-muted mb-0">
                        Manage your department configuration and preferences
                        <span class="badge bg-primary-soft text-primary ms-2">
                            <?php echo $is_head ? 'Department Head' : 'Department Secretary'; ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Settings Navigation Tabs -->
        <ul class="nav nav-tabs nav-fill mb-4" id="settingsTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                    <i class="bi bi-info-circle me-2"></i>Department Info
                </button>
            </li>
            <?php if ($is_head): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="staff-tab" data-bs-toggle="tab" data-bs-target="#staff" type="button" role="tab">
                    <i class="bi bi-people-fill me-2"></i>Staff Overview
                </button>
            </li>
            <?php endif; ?>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="settingsTabContent">
            <!-- General Settings Tab -->
            <div class="tab-pane fade show active" id="general" role="tabpanel">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h4 class="card-title mb-0">Department Information</h4>
                    </div>
                    <div class="card-body">
                        <form id="departmentForm" method="POST" action="../backend/department/update-department.php">
                            <input type="hidden" name="department_id" value="<?php echo $department_id; ?>">
                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
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
                                          rows="4" maxlength="1000" onInput="checkChanges()"><?php echo htmlspecialchars($department['description'] ?? ''); ?></textarea>
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">Describe your department's mission and activities</small>
                                    <small class="text-muted">
                                        <span id="charCount"><?php echo strlen($department['description'] ?? ''); ?></span>/1000
                                    </small>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-medium">Department Status</label>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-<?php echo $department['is_active'] ? 'success' : 'danger'; ?>-soft text-<?php echo $department['is_active'] ? 'success' : 'danger'; ?> me-2">
                                        <i class="bi bi-<?php echo $department['is_active'] ? 'check-circle' : 'x-circle'; ?> me-1"></i>
                                        <?php echo $department['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                    <small class="text-muted">
                                        Created: <?php echo date('M d, Y', strtotime($department['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" id="saveBtn" class="btn btn-primary" disabled>
                                    <i class="bi bi-check-circle me-2"></i>Save Changes
                                </button>
                                <button type="button" id="resetBtn" class="btn btn-outline-secondary" onclick="resetForm()" disabled>
                                    <i class="bi bi-arrow-clockwise me-2"></i>Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Department Statistics -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h4 class="card-title mb-0">Department Overview</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 col-6 mb-3">
                                <div class="text-center p-3 rounded-3 bg-primary-soft">
                                    <div class="h3 mb-1 text-primary"><?php echo $stats['instructor_count']; ?></div>
                                    <div class="text-muted small">Active Instructors</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="text-center p-3 rounded-3 bg-success-soft">
                                    <div class="h3 mb-1 text-success"><?php echo $stats['course_count']; ?></div>
                                    <div class="text-muted small">Total Courses</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="text-center p-3 rounded-3 bg-info-soft">
                                    <div class="h3 mb-1 text-info"><?php echo $stats['published_courses']; ?></div>
                                    <div class="text-muted small">Published</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="text-center p-3 rounded-3 bg-warning-soft">
                                    <div class="h3 mb-1 text-warning"><?php echo $stats['student_count']; ?></div>
                                    <div class="text-muted small">Students</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($is_head): ?>
            <!-- Staff Overview Tab (Head Only) -->
            <div class="tab-pane fade" id="staff" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Department Staff Overview</h4>
                        <a href="secretary.php" class="btn btn-primary btn-sm">
                            <i class="bi bi-person-plus me-2"></i>Manage Staff
                        </a>
                    </div>
                    <div class="card-body">
                        <?php
                        // Get department head info
                        $head_query = "SELECT u.*, ds.appointment_date 
                                       FROM department_staff ds
                                       JOIN users u ON ds.user_id = u.user_id
                                       WHERE ds.department_id = ? AND ds.role = 'head' AND ds.status = 'active' AND ds.deleted_at IS NULL";
                        $stmt = $conn->prepare($head_query);
                        $stmt->bind_param("i", $department_id);
                        $stmt->execute();
                        $head_result = $stmt->get_result();
                        $department_head = $head_result->fetch_assoc();
                        $stmt->close();
                        
                        // Get department secretaries count
                        $secretaries_query = "SELECT COUNT(*) as secretary_count 
                                              FROM department_staff ds
                                              WHERE ds.department_id = ? AND ds.role = 'secretary' AND ds.status = 'active' AND ds.deleted_at IS NULL";
                        $stmt = $conn->prepare($secretaries_query);
                        $stmt->bind_param("i", $department_id);
                        $stmt->execute();
                        $sec_result = $stmt->get_result();
                        $secretary_data = $sec_result->fetch_assoc();
                        $stmt->close();
                        ?>
                        
                        <!-- Department Head -->
                        <div class="mb-4">
                            <h6 class="mb-3 d-flex align-items-center">
                                <i class="bi bi-person-badge text-primary me-2"></i>
                                Department Head
                            </h6>
                            <?php if ($department_head): ?>
                            <div class="d-flex align-items-center p-3 rounded-3 bg-primary-soft">
                                <div class="avatar avatar-lg rounded-circle bg-primary text-white me-3 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-person-check fs-4"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($department_head['first_name'] . ' ' . $department_head['last_name']); ?></h6>
                                    <p class="text-muted mb-1"><?php echo htmlspecialchars($department_head['email']); ?></p>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        Appointed: <?php echo date('M d, Y', strtotime($department_head['appointment_date'])); ?>
                                    </small>
                                </div>
                                <span class="badge bg-success-soft text-success">
                                    <i class="bi bi-check-circle me-1"></i>Active
                                </span>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-4 bg-light rounded-3">
                                <i class="bi bi-person-x fs-1 text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0">No department head assigned</p>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Department Secretaries Summary -->
                        <div class="mb-4">
                            <h6 class="mb-3 d-flex align-items-center">
                                <i class="bi bi-people text-info me-2"></i>
                                Department Secretaries
                            </h6>
                            <div class="d-flex align-items-center p-3 rounded-3 bg-info-soft">
                                <div class="avatar avatar-lg rounded-circle bg-info text-white me-3 d-flex align-items-center justify-content-center">
                                    <span class="fw-bold"><?php echo $secretary_data['secretary_count']; ?></span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?php echo $secretary_data['secretary_count']; ?> Active Secretar<?php echo $secretary_data['secretary_count'] != 1 ? 'ies' : 'y'; ?></h6>
                                    <p class="text-muted mb-0">
                                        <i class="bi bi-briefcase me-1"></i>
                                        Supporting department operations
                                    </p>
                                </div>
                                <a href="secretary.php" class="btn btn-outline-info btn-sm">
                                    <i class="bi bi-eye me-1"></i>View All
                                </a>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <a href="secretary.php" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-person-plus me-2"></i>Invite Secretary
                                </a>
                            </div>
                            <div class="col-md-6 mb-2">
                                <a href="instructors.php" class="btn btn-outline-success w-100 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-people me-2"></i>Manage Instructors
                                </a>
                            </div>
                        </div>
                        
                        <!-- Staff Summary Stats -->
                        <hr class="my-4">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="bg-light rounded-3 p-3">
                                    <div class="h5 mb-1 text-primary">1</div>
                                    <small class="text-muted">Head</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-light rounded-3 p-3">
                                    <div class="h5 mb-1 text-info"><?php echo $secretary_data['secretary_count']; ?></div>
                                    <small class="text-muted">Secretar<?php echo $secretary_data['secretary_count'] != 1 ? 'ies' : 'y'; ?></small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-light rounded-3 p-3">
                                    <div class="h5 mb-1 text-success"><?php echo $stats['instructor_count']; ?></div>
                                    <small class="text-muted">Instructors</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

    </div>
    <!-- End Content -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- JavaScript -->
<script>
// Store original values
const originalDescription = `<?php echo addslashes($department['description'] ?? ''); ?>`;

// Form handlers
document.addEventListener('DOMContentLoaded', function() {
    // Department form
    const deptForm = document.getElementById('departmentForm');
    if (deptForm) {
        deptForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm(this, 'Department description updated successfully!');
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

// Check for changes in department form
function checkChanges() {
    const currentDescription = document.getElementById('department_description').value;
    const hasChanges = currentDescription !== originalDescription;
    
    document.getElementById('saveBtn').disabled = !hasChanges;
    document.getElementById('resetBtn').disabled = !hasChanges;
}

// Reset department form
function resetForm() {
    document.getElementById('department_description').value = originalDescription;
    document.getElementById('charCount').textContent = originalDescription.length;
    checkChanges();
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
            
            // Update original values
            window.originalDescription = document.getElementById('department_description').value;
            checkChanges();
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