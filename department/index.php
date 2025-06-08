<?php include '../includes/department/header.php'; ?>
<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Custom CSS for Modern Dashboard -->
<style>
    :root {
        --primary-soft: #f8f9ff;
        --primary-light: #e3e7ff;
        --primary: #6366f1;
        --primary-dark: #4f46e5;

        --success-soft: #f0fdf4;
        --success-light: #dcfce7;
        --success: #22c55e;

        --warning-soft: #fffbeb;
        --warning-light: #fef3c7;
        --warning: #f59e0b;

        --info-soft: #f0f9ff;
        --info-light: #e0f2fe;
        --info: #06b6d4;

        --danger-soft: #fef2f2;
        --danger-light: #fecaca;
        --danger: #ef4444;

        --neutral-50: #fafafa;
        --neutral-100: #f5f5f5;
        --neutral-200: #e5e5e5;
        --neutral-300: #d4d4d4;
        --neutral-600: #525252;
        --neutral-700: #404040;
        --neutral-800: #262626;
    }

    body {
        background-color: var(--neutral-50);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .dashboard-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .dashboard-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .metric-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        border: none;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        height: 100%;
    }

    .metric-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .metric-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 1rem;
    }

    .chart-card {
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        border: none;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
    }

    .quick-action-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        text-align: center;
        text-decoration: none;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .quick-action-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        text-decoration: none;
    }

    .quick-action-card.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: var(--neutral-100);
    }

    .quick-action-card.disabled:hover {
        transform: none;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .quick-action-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 1rem;
    }

    .status-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }

    .table-modern {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .table-modern .table {
        margin-bottom: 0;
    }

    .table-modern .table thead th {
        background-color: var(--neutral-50);
        border: none;
        padding: 1rem 1.5rem;
        font-weight: 600;
        color: var(--neutral-700);
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
    }

    .table-modern .table tbody td {
        border: none;
        padding: 1rem 1.5rem;
        vertical-align: middle;
    }

    .table-modern .table tbody tr {
        border-bottom: 1px solid var(--neutral-100);
    }

    .table-modern .table tbody tr:last-child {
        border-bottom: none;
    }

    .sidebar-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        border: none;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
        height: fit-content;
    }

    .progress-ring {
        width: 60px;
        height: 60px;
    }

    .alert-modern {
        border-radius: 16px;
        border: none;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .btn-soft {
        border-radius: 10px;
        font-weight: 500;
        padding: 0.5rem 1rem;
        border: none;
        transition: all 0.3s ease;
    }

    .avatar-modern {
        width: 32px;
        height: 32px;
        border-radius: 8px;
    }

    .role-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }
</style>

<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main">
    <!-- Navbar -->
    <nav class="js-nav-scroller navbar navbar-expand-lg navbar-sidebar navbar-vertical navbar-light bg-white border-end"
        data-hs-nav-scroller-options='{
            "type": "vertical",
            "target": ".navbar-nav .active",
            "offset": 80
           }'>
        <?php include '../includes/department/sidebar.php'; ?>
    </nav>

    <!-- Content -->
    <div class="navbar-sidebar-aside-content content-space-t-3 content-space-b-2 px-lg-4 px-xl-5">
        <?php
        include '../backend/config.php';

        // Get current user role and department info
        $user_id = $_SESSION['user_id'] ?? 0;
        $user_role = $_SESSION['role'] ?? '';
        $department_id = $_SESSION['department_id'] ?? 0;

        if (!$department_id || !$user_id) {
            echo '<div class="alert alert-warning">Department information not found. Please contact system administrator.</div>';
            exit;
        }

        // Check if user is department head or secretary
        $role_query = "SELECT ds.role, ds.status FROM department_staff ds 
                       WHERE ds.user_id = ? AND ds.department_id = ? AND ds.deleted_at IS NULL";
        $stmt = $conn->prepare($role_query);
        $stmt->bind_param("ii", $user_id, $department_id);
        $stmt->execute();
        $role_result = $stmt->get_result();
        $user_dept_role = $role_result->fetch_assoc();
        $stmt->close();

        if (!$user_dept_role) {
            echo '<div class="alert alert-danger">You do not have access to this department. Please contact system administrator.</div>';
            exit;
        }

        $is_head = ($user_dept_role['role'] === 'head');
        $is_secretary = ($user_dept_role['role'] === 'secretary');

        // Get department info
        $dept_query = "SELECT name, code, description FROM departments WHERE department_id = ?";
        $stmt = $conn->prepare($dept_query);
        $stmt->bind_param("i", $department_id);
        $stmt->execute();
        $dept_result = $stmt->get_result();
        $department = $dept_result->fetch_assoc();
        $stmt->close();

        if (!$department) {
            echo '<div class="alert alert-danger">Department not found. Please contact system administrator.</div>';
            exit;
        }

        // Get current department head info (only if user is not head themselves)
        $head_info = null;
        if (!$is_head) {
            $head_query = "SELECT u.first_name, u.last_name, u.email, ds.appointment_date 
                           FROM department_staff ds
                           JOIN users u ON ds.user_id = u.user_id
                           WHERE ds.department_id = ? AND ds.role = 'head' AND ds.status = 'active' AND ds.deleted_at IS NULL";
            $stmt = $conn->prepare($head_query);
            $stmt->bind_param("i", $department_id);
            $stmt->execute();
            $head_result = $stmt->get_result();
            $head_info = $head_result->fetch_assoc();
            $stmt->close();
        }

        // Get current user info for secretary role
        $current_user_info = null;
        if ($is_secretary) {
            $user_query = "SELECT first_name, last_name, email FROM users WHERE user_id = ?";
            $stmt = $conn->prepare($user_query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user_result = $stmt->get_result();
            $current_user_info = $user_result->fetch_assoc();
            $stmt->close();
        }

        // Department statistics
        $query = "SELECT 
            (SELECT COUNT(*) FROM department_staff 
                WHERE department_id = ? AND role = 'secretary' AND deleted_at IS NULL AND status = 'active') AS secretary_count,
                
            (SELECT COUNT(DISTINCT di.instructor_id) FROM department_instructors di
                JOIN instructors i ON di.instructor_id = i.instructor_id
                WHERE di.department_id = ? AND di.status = 'active' AND di.deleted_at IS NULL) AS instructor_count,
                
            (SELECT COUNT(*) FROM courses WHERE department_id = ? AND deleted_at IS NULL) AS course_count,
            
            (SELECT COUNT(*) FROM courses WHERE department_id = ? AND status = 'Published' 
                AND approval_status = 'approved' AND deleted_at IS NULL) AS approved_course_count,
                
            (SELECT COUNT(*) FROM courses WHERE department_id = ? 
                AND (approval_status = 'pending' OR approval_status = 'submitted_for_review') 
                AND deleted_at IS NULL) AS pending_course_count,
                
            (SELECT COUNT(*) FROM courses WHERE department_id = ? AND approval_status = 'revisions_requested' 
                AND deleted_at IS NULL) AS revision_course_count,
                
            (SELECT COUNT(*) FROM courses WHERE department_id = ? AND approval_status = 'rejected' 
                AND deleted_at IS NULL) AS rejected_course_count,
                
            (SELECT COUNT(*) FROM courses WHERE department_id = ? AND financial_approval_date IS NULL 
                AND deleted_at IS NULL) AS pending_financial_approval,
                
            (SELECT COUNT(*) FROM enrollments e
                JOIN courses c ON e.course_id = c.course_id
                WHERE c.department_id = ? AND e.deleted_at IS NULL) AS enrollment_count,
                
            (SELECT COUNT(*) FROM enrollments e
                JOIN courses c ON e.course_id = c.course_id
                WHERE c.department_id = ? AND e.status = 'Active' AND e.deleted_at IS NULL) AS active_enrollment_count,
                
            (SELECT COUNT(*) FROM enrollments e
                JOIN courses c ON e.course_id = c.course_id
                WHERE c.department_id = ? AND e.status = 'Completed' AND e.deleted_at IS NULL) AS completed_enrollment_count,
                
            (SELECT COALESCE(SUM(cp.amount), 0) FROM course_payments cp
                JOIN enrollments e ON cp.enrollment_id = e.enrollment_id
                JOIN courses c ON e.course_id = c.course_id
                WHERE c.department_id = ? AND cp.status = 'Completed' AND cp.deleted_at IS NULL) AS total_revenue,
                
            (SELECT COALESCE(SUM(cp.amount), 0) FROM course_payments cp
                JOIN enrollments e ON cp.enrollment_id = e.enrollment_id
                JOIN courses c ON e.course_id = c.course_id
                WHERE c.department_id = ? AND cp.status = 'Completed' 
                AND cp.payment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND cp.deleted_at IS NULL) AS monthly_revenue";

        $stmt = $conn->prepare($query);
        $stmt->bind_param(
            "iiiiiiiiiiiii",
            $department_id,
            $department_id,
            $department_id,
            $department_id,
            $department_id,
            $department_id,
            $department_id,
            $department_id,
            $department_id,
            $department_id,
            $department_id,
            $department_id,
            $department_id
        );
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result) {
            $data = $result->fetch_assoc();
            $secretary_count = $data['secretary_count'];
            $instructor_count = $data['instructor_count'];
            $course_count = $data['course_count'];
            $approved_course_count = $data['approved_course_count'];
            $pending_course_count = $data['pending_course_count'];
            $revision_course_count = $data['revision_course_count'];
            $rejected_course_count = $data['rejected_course_count'];
            $pending_financial_approval = $data['pending_financial_approval'];
            $enrollment_count = $data['enrollment_count'];
            $active_enrollment_count = $data['active_enrollment_count'];
            $completed_enrollment_count = $data['completed_enrollment_count'];
            $total_revenue = $data['total_revenue'];
            $monthly_revenue = $data['monthly_revenue'];
        } else {
            die("Error executing department query: " . mysqli_error($conn));
        }
        $stmt->close();

        // Get recent course submissions
        $recent_submissions_query = "SELECT c.title, c.course_id, c.approval_status, u.first_name, u.last_name, 
                                   c.updated_at
                            FROM courses c
                            JOIN course_instructors ci ON c.course_id = ci.course_id AND ci.is_primary = 1
                            JOIN instructors i ON ci.instructor_id = i.instructor_id
                            JOIN users u ON i.user_id = u.user_id
                            WHERE c.department_id = ? 
                            AND c.deleted_at IS NULL
                            ORDER BY c.updated_at DESC
                            LIMIT 8";

        $stmt = $conn->prepare($recent_submissions_query);
        $stmt->bind_param("i", $department_id);
        $stmt->execute();
        $recent_submissions_result = $stmt->get_result();
        $stmt->close();
        ?>

        <!-- Modern Dashboard Header -->
        <div class="dashboard-header text-white position-relative">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-building fs-4"></i>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h1 class="h2 mb-0"><?php echo htmlspecialchars($department['name']); ?></h1>
                                <span class="role-badge" style="background: rgba(255,255,255,0.2); color: white;">
                                    <?php echo $is_head ? 'Department Head' : 'Department Secretary'; ?>
                                </span>
                            </div>
                            <p class="mb-0 opacity-75">Department Code: <?php echo htmlspecialchars($department['code']); ?></p>
                        </div>
                    </div>

                    <?php if ($is_head): ?>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm avatar-circle me-2" style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-person-check text-white"></i>
                            </div>
                            <div>
                                <span class="fw-medium">You are the Department Head</span>
                                <small class="d-block opacity-75">Full administrative access</small>
                            </div>
                        </div>
                    <?php elseif ($is_secretary && $head_info): ?>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm avatar-circle me-2" style="background: rgba(255,255,255,0.2);">
                                <i class="bi bi-person-workspace text-white"></i>
                            </div>
                            <div>
                                <span class="fw-medium">Department Head: <?php echo htmlspecialchars($head_info['first_name'] . ' ' . $head_info['last_name']); ?></span>
                                <?php if ($head_info['appointment_date']): ?>
                                    <small class="d-block opacity-75">Since <?php echo date('M Y', strtotime($head_info['appointment_date'])); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="d-flex flex-lg-column gap-2">
                        <?php if ($is_head): ?>
                            <a href="settings.php" class="btn btn-light btn-sm">
                                <i class="bi bi-gear me-2"></i>Settings
                            </a>
                            <!-- <a href="reports.php" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3);">
                                <i class="bi bi-file-earmark-bar-graph me-2"></i>Reports
                            </a> -->
                        <?php else: ?>
                            <!-- <a href="profile.php" class="btn btn-light btn-sm">
                                <i class="bi bi-person me-2"></i>My Profile
                            </a>
                            <a href="support.php" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3);">
                                <i class="bi bi-headset me-2"></i>Support
                            </a> -->
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Items Alert -->
        <?php if (($is_head && ($pending_course_count > 0 || $pending_financial_approval > 0)) || ($is_secretary && $pending_course_count > 0)): ?>
            <div class="alert-modern" style="background: var(--warning-soft); border-left: 4px solid var(--warning);">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div style="width: 40px; height: 40px; background: var(--warning-light); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-exclamation-triangle text-warning"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1" style="color: var(--neutral-800);">
                            <?php echo $is_head ? 'Action Required' : 'Items Requiring Attention'; ?>
                        </h6>
                        <div class="d-flex gap-2 flex-wrap">
                            <?php if ($pending_course_count > 0): ?>
                                <span class="status-badge" style="background: var(--warning-light); color: var(--warning);">
                                    <?php echo $pending_course_count; ?> course<?php echo $pending_course_count != 1 ? 's' : ''; ?> awaiting review
                                </span>
                            <?php endif; ?>
                            <?php if ($is_head && $pending_financial_approval > 0): ?>
                                <span class="status-badge" style="background: var(--info-light); color: var(--info);">
                                    <?php echo $pending_financial_approval; ?> financial approval<?php echo $pending_financial_approval != 1 ? 's' : ''; ?> pending
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        <?php if ($is_head): ?>
                            <a href="courses.php" class="btn btn-soft" style="background: var(--warning); color: white;">Review Now</a>
                        <?php else: ?>
                            <a href="courses.php" class="btn btn-soft" style="background: var(--warning); color: white;">View Courses</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Key Metrics Grid -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="metric-card">
                    <div class="metric-icon" style="background: var(--primary-soft); color: var(--primary);">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <h3 class="mb-1" style="color: var(--neutral-800);"><?php echo number_format($instructor_count); ?></h3>
                            <p class="text-muted mb-0">Instructors</p>
                            <small class="text-muted"><?php echo $secretary_count; ?> Secretary<?php echo $secretary_count != 1 ? 's' : ''; ?></small>
                        </div>
                        <a href="instructors.php" class="btn btn-soft" style="background: var(--primary-light); color: var(--primary);">
                            <?php echo $is_head ? 'Manage' : 'View'; ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="metric-card">
                    <div class="metric-icon" style="background: var(--success-soft); color: var(--success);">
                        <i class="bi bi-book"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <h3 class="mb-1" style="color: var(--neutral-800);"><?php echo number_format($course_count); ?></h3>
                            <p class="text-muted mb-0">Courses</p>
                            <small class="text-muted"><?php echo $approved_course_count; ?> Approved</small>
                        </div>
                        <a href="courses.php" class="btn btn-soft" style="background: var(--success-light); color: var(--success);">View</a>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="metric-card">
                    <div class="metric-icon" style="background: var(--info-soft); color: var(--info);">
                        <i class="bi bi-mortarboard"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <h3 class="mb-1" style="color: var(--neutral-800);"><?php echo number_format($enrollment_count); ?></h3>
                            <p class="text-muted mb-0">Enrollments</p>
                            <small class="text-muted"><?php echo $active_enrollment_count; ?> Active</small>
                        </div>
                        <a href="enrollments.php" class="btn btn-soft" style="background: var(--info-light); color: var(--info);">Details</a>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="metric-card">
                    <div class="metric-icon" style="background: var(--warning-soft); color: var(--warning);">
                        <span class="fs-4 fw-bold">₵</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <h3 class="mb-1" style="color: var(--neutral-800);">
                                <?php if ($is_head): ?>
                                    GH¢<?php echo number_format($total_revenue, 0); ?>
                                <?php else: ?>
                                    <i class="bi bi-lock text-muted"></i>
                                <?php endif; ?>
                            </h3>
                            <p class="text-muted mb-0">
                                <?php echo $is_head ? 'Total Revenue' : 'Revenue (Restricted)'; ?>
                            </p>
                            <small class="text-muted">
                                <?php echo $is_head ? 'GH¢' . number_format($monthly_revenue, 0) . ' this month' : 'Contact head for details'; ?>
                            </small>
                        </div>
                        <?php if ($is_head): ?>
                            <a href="#" class="btn btn-soft" style="background: var(--warning-light); color: var(--warning);">View</a>
                        <?php else: ?>
                            <span class="btn btn-soft disabled" style="background: var(--neutral-100); color: var(--neutral-400);">Restricted</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row g-4 mb-4">
            <!-- Course Status Chart -->
            <div class="col-lg-6">
                <div class="chart-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0" style="color: var(--neutral-800);">Course Distribution</h5>
                        <div class="dropdown">
                            <button class="btn btn-sm" style="background: var(--neutral-100); border: none;" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="courses.php">View all courses</a>
                                <?php if ($is_head): ?>
                                    <a class="dropdown-item" href="course-analytics.php">Detailed analytics</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-6">
                            <canvas id="courseStatusChart" style="max-height: 200px;"></canvas>
                        </div>
                        <div class="col-6">
                            <div class="space-y-3">
                                <div class="d-flex align-items-center">
                                    <div style="width: 12px; height: 12px; background: var(--success); border-radius: 3px; margin-right: 8px;"></div>
                                    <div class="flex-grow-1">
                                        <div class="fw-medium"><?php echo $approved_course_count; ?></div>
                                        <div class="text-muted small">Approved</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div style="width: 12px; height: 12px; background: var(--warning); border-radius: 3px; margin-right: 8px;"></div>
                                    <div class="flex-grow-1">
                                        <div class="fw-medium"><?php echo $pending_course_count; ?></div>
                                        <div class="text-muted small">Pending</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div style="width: 12px; height: 12px; background: var(--info); border-radius: 3px; margin-right: 8px;"></div>
                                    <div class="flex-grow-1">
                                        <div class="fw-medium"><?php echo $revision_course_count; ?></div>
                                        <div class="text-muted small">Revision</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div style="width: 12px; height: 12px; background: var(--danger); border-radius: 3px; margin-right: 8px;"></div>
                                    <div class="fw-medium"><?php echo $rejected_course_count; ?></div>
                                    <div class="text-muted small">Rejected</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Performance Overview -->
            <div class="col-lg-6">
                <div class="chart-card">
                    <h5 class="mb-4" style="color: var(--neutral-800);">Department Performance</h5>
                    <div class="row text-center">
                        <div class="col-6 mb-4">
                            <div class="position-relative d-inline-block">
                                <svg class="progress-ring" width="60" height="60">
                                    <circle cx="30" cy="30" r="25" stroke="var(--neutral-200)" stroke-width="4" fill="none" />
                                    <circle cx="30" cy="30" r="25" stroke="var(--success)" stroke-width="4" fill="none"
                                        stroke-dasharray="<?php echo 157 * ($completed_enrollment_count / max($enrollment_count, 1)); ?> 157"
                                        stroke-linecap="round" transform="rotate(-90 30 30)" />
                                </svg>
                                <div class="position-absolute top-50 start-50 translate-middle">
                                    <strong><?php echo number_format(($completed_enrollment_count / max($enrollment_count, 1)) * 100, 0); ?>%</strong>
                                </div>
                            </div>
                            <div class="mt-2">
                                <div class="fw-medium">Completion Rate</div>
                                <small class="text-muted"><?php echo $completed_enrollment_count; ?> of <?php echo $enrollment_count; ?></small>
                            </div>
                        </div>
                        <div class="col-6 mb-4">
                            <div class="position-relative d-inline-block">
                                <svg class="progress-ring" width="60" height="60">
                                    <circle cx="30" cy="30" r="25" stroke="var(--neutral-200)" stroke-width="4" fill="none" />
                                    <circle cx="30" cy="30" r="25" stroke="var(--primary)" stroke-width="4" fill="none"
                                        stroke-dasharray="<?php echo 157 * ($approved_course_count / max($course_count, 1)); ?> 157"
                                        stroke-linecap="round" transform="rotate(-90 30 30)" />
                                </svg>
                                <div class="position-absolute top-50 start-50 translate-middle">
                                    <strong><?php echo number_format(($approved_course_count / max($course_count, 1)) * 100, 0); ?>%</strong>
                                </div>
                            </div>
                            <div class="mt-2">
                                <div class="fw-medium">Approval Rate</div>
                                <small class="text-muted"><?php echo $approved_course_count; ?> of <?php echo $course_count; ?></small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row">
                                <div class="col-6">
                                    <div class="text-center p-3" style="background: var(--success-soft); border-radius: 12px;">
                                        <div class="h4 mb-1" style="color: var(--success);"><?php echo number_format($enrollment_count / max($course_count, 1), 1); ?></div>
                                        <div class="text-muted small">Avg. Enrollments</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-3" style="background: var(--warning-soft); border-radius: 12px;">
                                        <?php if ($is_head): ?>
                                            <div class="h4 mb-1" style="color: var(--warning);">GH¢<?php echo number_format($total_revenue / max($course_count, 1), 0); ?></div>
                                            <div class="text-muted small">Avg. Revenue</div>
                                        <?php else: ?>
                                            <div class="h4 mb-1" style="color: var(--neutral-400);"><i class="bi bi-lock"></i></div>
                                            <div class="text-muted small">Restricted</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Quick Actions -->
        <div class="chart-card mb-4">
            <h5 class="mb-4" style="color: var(--neutral-800);">Quick Actions</h5>
            <div class="row g-3">
                <!-- Invite Instructor - Head Only -->
                <?php if ($is_head): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <a href="invite-instructor.php" class="quick-action-card text-decoration-none">
                            <div class="quick-action-icon" style="background: var(--primary-soft); color: var(--primary);">
                                <i class="bi bi-person-plus"></i>
                            </div>
                            <span class="fw-medium" style="color: var(--neutral-800);">Invite Instructor</span>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="quick-action-card disabled">
                            <div class="quick-action-icon" style="background: var(--neutral-200); color: var(--neutral-400);">
                                <i class="bi bi-person-plus"></i>
                            </div>
                            <span class="fw-medium" style="color: var(--neutral-400);">Invite Instructor</span>
                            <small class="text-muted mt-1">Head Only</small>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Create Course - Head Only -->
                <?php if ($is_head): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <a href="initiate-course.php" class="quick-action-card text-decoration-none">
                            <div class="quick-action-icon" style="background: var(--success-soft); color: var(--success);">
                                <i class="bi bi-plus-circle"></i>
                            </div>
                            <span class="fw-medium" style="color: var(--neutral-800);">Initiate Course</span>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="quick-action-card disabled">
                            <div class="quick-action-icon" style="background: var(--neutral-200); color: var(--neutral-400);">
                                <i class="bi bi-plus-circle"></i>
                            </div>
                            <span class="fw-medium" style="color: var(--neutral-400);">Initiate Course</span>
                            <small class="text-muted mt-1">Head Only</small>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Send Announcement - Both can access -->
                <!-- <div class="col-6 col-md-4 col-lg-2">
                    <a href="send-announcement.php" class="quick-action-card text-decoration-none">
                        <div class="quick-action-icon" style="background: var(--info-soft); color: var(--info);">
                            <i class="bi bi-megaphone"></i>
                        </div>
                        <span class="fw-medium" style="color: var(--neutral-800);">
                            <?php //echo $is_head ? 'Send Announcement' : 'Draft Announcement'; ?>
                        </span>
                    </a>
                </div> -->

                <!-- Generate Report - Head only, Secretary can view -->
                <div class="col-6 col-md-4 col-lg-3">
                    <?php if ($is_head): ?>
                        <a href="analytic-overview.php" class="quick-action-card text-decoration-none">
                            <div class="quick-action-icon" style="background: var(--neutral-100); color: var(--neutral-600);">
                                <i class="bi bi-file-earmark-bar-graph"></i>
                            </div>
                            <span class="fw-medium" style="color: var(--neutral-800);">Generate Report</span>
                        </a>
                    <?php else: ?>
                        <a href="analytic-overview.php" class="quick-action-card text-decoration-none">
                            <div class="quick-action-icon" style="background: var(--neutral-100); color: var(--neutral-600);">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            <span class="fw-medium" style="color: var(--neutral-800);">View Reports</span>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Financial Overview - Head Only -->
                <?php if ($is_head): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <a href="analytic-overview.php" class="quick-action-card text-decoration-none">
                            <div class="quick-action-icon" style="background: var(--danger-soft); color: var(--danger);">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <span class="fw-medium" style="color: var(--neutral-800);">Financials</span>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="quick-action-card disabled">
                            <div class="quick-action-icon" style="background: var(--neutral-200); color: var(--neutral-400);">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <span class="fw-medium" style="color: var(--neutral-400);">Financials</span>
                            <small class="text-muted mt-1">Head Only</small>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main Content Section -->
        <div class="row g-4">
            <!-- Recent Course Activities -->
            <div class="col-lg-8">
                <div class="table-modern">
                    <div class="d-flex justify-content-between align-items-center p-4 pb-0">
                        <h5 class="mb-0" style="color: var(--neutral-800);">Recent Course Activities</h5>
                        <a href="courses.php" class="btn btn-soft" style="background: var(--neutral-100); color: var(--neutral-600);">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th>Instructor</th>
                                    <th>Status</th>
                                    <th>Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recent_submissions_result && $recent_submissions_result->num_rows > 0): ?>
                                    <?php while ($course = $recent_submissions_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <div class="fw-medium mb-1" style="color: var(--neutral-800);">
                                                        <?php echo htmlspecialchars(substr($course['title'], 0, 40)) . (strlen($course['title']) > 40 ? '...' : ''); ?>
                                                    </div>
                                                    <small class="text-muted">ID: <?php echo $course['course_id']; ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-modern me-2" style="background: var(--primary-soft); color: var(--primary); display: flex; align-items: center; justify-content: center;">
                                                        <i class="bi bi-person fs-6"></i>
                                                    </div>
                                                    <span style="color: var(--neutral-700);"><?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                $status_styles = [
                                                    'pending' => ['bg' => 'var(--warning-soft)', 'color' => 'var(--warning)', 'text' => 'Pending'],
                                                    'submitted_for_review' => ['bg' => 'var(--info-soft)', 'color' => 'var(--info)', 'text' => 'Submitted'],
                                                    'under_review' => ['bg' => 'var(--primary-soft)', 'color' => 'var(--primary)', 'text' => 'Under Review'],
                                                    'approved' => ['bg' => 'var(--success-soft)', 'color' => 'var(--success)', 'text' => 'Approved'],
                                                    'revisions_requested' => ['bg' => 'var(--danger-soft)', 'color' => 'var(--danger)', 'text' => 'Needs Revision'],
                                                    'rejected' => ['bg' => 'var(--neutral-100)', 'color' => 'var(--neutral-600)', 'text' => 'Rejected']
                                                ];
                                                $status = $course['approval_status'];
                                                $style = $status_styles[$status] ?? ['bg' => 'var(--neutral-100)', 'color' => 'var(--neutral-600)', 'text' => ucwords(str_replace('_', ' ', $status))];
                                                ?>
                                                <span class="status-badge" style="background: <?php echo $style['bg']; ?>; color: <?php echo $style['color']; ?>;">
                                                    <?php echo $style['text']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div style="color: var(--neutral-700);"><?php echo date('M d', strtotime($course['updated_at'])); ?></div>
                                                <small class="text-muted"><?php echo date('g:i A', strtotime($course['updated_at'])); ?></small>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="courses.php"
                                                        class="btn btn-sm btn-soft" style="background: var(--neutral-100); color: var(--neutral-600);">
                                                        View
                                                    </a>
                                                    <?php if ($is_head && $course['approval_status'] == 'submitted_for_review'): ?>
                                                        <a href="courses.php"
                                                            class="btn btn-sm btn-soft" style="background: var(--primary-light); color: var(--primary);">
                                                            Review
                                                        </a>
                                                    <?php elseif ($is_secretary && in_array($course['approval_status'], ['submitted_for_review', 'under_review'])): ?>
                                                        <span class="btn btn-sm btn-soft disabled" style="background: var(--neutral-100); color: var(--neutral-400);">
                                                            Awaiting Head
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-inbox fs-1 d-block mb-3" style="color: var(--neutral-300);"></i>
                                                <div class="fw-medium mb-1">No recent activities</div>
                                                <div class="small">Course activities will appear here</div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Role-specific Performance Summary -->
                <div class="sidebar-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0" style="color: var(--neutral-800);">
                            <?php echo $is_head ? 'Performance Summary' : 'Department Overview'; ?>
                        </h6>
                        <span class="badge" style="background: var(--success-light); color: var(--success);">Live Data</span>
                    </div>

                    <div class="space-y-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-medium" style="color: var(--neutral-800);">Active Courses</div>
                                <small class="text-muted">Currently running</small>
                            </div>
                            <div class="h4 mb-0" style="color: var(--success);"><?php echo $approved_course_count; ?></div>
                        </div>
                        <hr style="border-color: var(--neutral-200);">

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-medium" style="color: var(--neutral-800);">Total Students</div>
                                <small class="text-muted">Enrolled learners</small>
                            </div>
                            <div class="h4 mb-0" style="color: var(--info);"><?php echo $enrollment_count; ?></div>
                        </div>
                        <hr style="border-color: var(--neutral-200);">

                        <?php if ($is_head): ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-medium" style="color: var(--neutral-800);">Monthly Revenue</div>
                                    <small class="text-muted">Last 30 days</small>
                                </div>
                                <div class="h4 mb-0" style="color: var(--warning);">GH¢<?php echo number_format($monthly_revenue, 0); ?></div>
                            </div>
                            <hr style="border-color: var(--neutral-200);">
                        <?php else: ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-medium" style="color: var(--neutral-800);">Pending Reviews</div>
                                    <small class="text-muted">Awaiting head review</small>
                                </div>
                                <div class="h4 mb-0" style="color: var(--warning);"><?php echo $pending_course_count; ?></div>
                            </div>
                            <hr style="border-color: var(--neutral-200);">
                        <?php endif; ?>

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-medium" style="color: var(--neutral-800);">Success Rate</div>
                                <small class="text-muted">Course completions</small>
                            </div>
                            <div class="h4 mb-0" style="color: var(--primary);"><?php echo number_format(($completed_enrollment_count / max($enrollment_count, 1)) * 100, 1); ?>%</div>
                        </div>
                    </div>
                </div>

                <!-- Role-specific Tasks/Info -->
                <div class="sidebar-card">
                    <h6 class="mb-3" style="color: var(--neutral-800);">
                        <?php echo $is_head ? 'Pending Tasks' : 'My Tasks'; ?>
                    </h6>

                    <div class="space-y-3">
                        <?php if ($is_head): ?>
                            <?php if ($pending_course_count > 0): ?>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div style="width: 32px; height: 32px; background: var(--warning-soft); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-clock-history" style="color: var(--warning);"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-medium small" style="color: var(--neutral-800);">Course Reviews</div>
                                        <div class="text-muted small"><?php echo $pending_course_count; ?> courses awaiting your review</div>
                                    </div>
                                    <a href="courses.php" class="btn btn-xs btn-soft" style="background: var(--warning-light); color: var(--warning);">Review</a>
                                </div>
                            <?php endif; ?>

                            <?php if ($pending_financial_approval > 0): ?>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div style="width: 32px; height: 32px; background: var(--info-soft); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                            <span style="color: var(--info); font-weight: bold; font-size: 18px;">₵</span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-medium small" style="color: var(--neutral-800);">Financial Approvals</div>
                                        <div class="text-muted small"><?php echo $pending_financial_approval; ?> courses need financial review</div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($revision_course_count > 0): ?>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div style="width: 32px; height: 32px; background: var(--danger-soft); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-arrow-repeat" style="color: var(--danger);"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-medium small" style="color: var(--neutral-800);">Follow-up Needed</div>
                                        <div class="text-muted small"><?php echo $revision_course_count; ?> courses with requested revisions</div>
                                    </div>
                                </div>
                            <?php endif; ?>

                        <?php else: ?>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div style="width: 32px; height: 32px; background: var(--primary-soft); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-file-earmark-text" style="color: var(--primary);"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-medium small" style="color: var(--neutral-800);">Administrative Support</div>
                                    <div class="text-muted small">Assist with course documentation and communication</div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div style="width: 32px; height: 32px; background: var(--info-soft); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-people" style="color: var(--info);"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-medium small" style="color: var(--neutral-800);">Instructor Coordination</div>
                                    <div class="text-muted small">Monitor instructor activities and communication</div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div style="width: 32px; height: 32px; background: var(--success-soft); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-clipboard-data" style="color: var(--success);"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-medium small" style="color: var(--neutral-800);">Report Assistance</div>
                                    <div class="text-muted small">Help compile department reports and statistics</div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (($is_head && $pending_course_count == 0 && $pending_financial_approval == 0 && $revision_course_count == 0) || $is_secretary): ?>
                            <div class="text-center py-3">
                                <div class="text-muted">
                                    <i class="bi bi-check-circle fs-2 d-block mb-2" style="color: var(--success);"></i>
                                    <div class="small"><?php echo $is_head ? 'All caught up!' : 'Supporting department operations'; ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="sidebar-card">
                    <h6 class="mb-3" style="color: var(--neutral-800);">Quick Stats</h6>

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-2" style="background: var(--primary-soft); border-radius: 10px;">
                                <div class="h5 mb-1" style="color: var(--primary);"><?php echo $pending_course_count; ?></div>
                                <div class="text-muted small">Pending Reviews</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2" style="background: var(--warning-soft); border-radius: 10px;">
                                <div class="h5 mb-1" style="color: var(--warning);"><?php echo $revision_course_count; ?></div>
                                <div class="text-muted small">Need Revision</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2" style="background: var(--success-soft); border-radius: 10px;">
                                <div class="h5 mb-1" style="color: var(--success);"><?php echo $active_enrollment_count; ?></div>
                                <div class="text-muted small">Active Students</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2" style="background: var(--info-soft); border-radius: 10px;">
                                <div class="h5 mb-1" style="color: var(--info);"><?php echo $completed_enrollment_count; ?></div>
                                <div class="text-muted small">Completed</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- End Content -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- Chart.js Scripts -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Course Status Donut Chart
        const courseStatusCtx = document.getElementById('courseStatusChart').getContext('2d');
        new Chart(courseStatusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Approved', 'Pending', 'Needs Revision', 'Rejected'],
                datasets: [{
                    data: [
                        <?php echo $approved_course_count; ?>,
                        <?php echo $pending_course_count; ?>,
                        <?php echo $revision_course_count; ?>,
                        <?php echo $rejected_course_count; ?>
                    ],
                    backgroundColor: [
                        'var(--success)',
                        'var(--warning)',
                        'var(--info)',
                        'var(--danger)'
                    ],
                    borderWidth: 0,
                    cutout: '70%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'var(--neutral-200)',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: false
                    }
                }
            }
        });
    });

    // Show loading overlay function
    function showOverlay(message = null) {
        const existingOverlay = document.querySelector('.custom-overlay');
        if (existingOverlay) {
            existingOverlay.remove();
        }

        const overlay = document.createElement('div');
        overlay.className = 'custom-overlay';
        overlay.innerHTML = `
   <div class="spinner-border text-primary" role="status">
       <span class="visually-hidden">Loading...</span>
   </div>
   ${message ? `<div class="text-white ms-3">${message}</div>` : ''}
   `;

        document.body.appendChild(overlay);
    }

    // Remove Loading Overlay
    function removeOverlay() {
        const overlay = document.querySelector('.custom-overlay');
        if (overlay) {
            overlay.remove();
        }
    }

    // Add smooth hover animations
    document.querySelectorAll('.metric-card, .quick-action-card:not(.disabled)').forEach(card => {
        card.addEventListener('mouseenter', function() {
            if (!this.classList.contains('disabled')) {
                this.style.transform = 'translateY(-4px)';
                this.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.1)';
            }
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.05)';
        });
    });

    // Role-based tooltips for disabled actions
    document.querySelectorAll('.quick-action-card.disabled').forEach(card => {
        card.addEventListener('click', function(e) {
            e.preventDefault();
            // You can add a tooltip or modal here to explain why the action is disabled
        });
    });
</script>

<!-- Additional Responsive CSS -->
<style>
    @media (max-width: 768px) {
        .dashboard-header {
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .dashboard-header .row {
            text-align: center;
        }

        .dashboard-header .col-lg-4 {
            margin-top: 1rem;
        }

        .metric-card {
            margin-bottom: 1rem;
        }

        .quick-action-card {
            padding: 1rem;
        }

        .quick-action-icon {
            width: 48px;
            height: 48px;
            font-size: 20px;
        }

        .chart-card {
            padding: 1rem;
        }

        .table-responsive {
            font-size: 0.875rem;
        }

        .role-badge {
            font-size: 0.65rem;
            padding: 0.2rem 0.5rem;
        }
    }

    .space-y-3>*+* {
        margin-top: 1rem;
    }

    .space-y-4>*+* {
        margin-top: 1.5rem;
    }

    .custom-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    /* Permission-based styling */
    .permission-restricted {
        opacity: 0.6;
        pointer-events: none;
    }

    .permission-restricted::after {
        content: '🔒';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 1.2rem;
    }

    /* Role indicator styles */
    .role-head {
        border-left: 4px solid var(--primary);
    }

    .role-secretary {
        border-left: 4px solid var(--info);
    }

    /* Enhanced disabled state */
    .quick-action-card.disabled {
        position: relative;
        overflow: hidden;
    }

    .quick-action-card.disabled::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: repeating-linear-gradient(45deg,
                transparent,
                transparent 10px,
                rgba(0, 0, 0, 0.03) 10px,
                rgba(0, 0, 0, 0.03) 20px);
        pointer-events: none;
    }

    /* Success animations for approved actions */
    @keyframes successPulse {
        0% {
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(34, 197, 94, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
        }
    }

    .success-pulse {
        animation: successPulse 2s infinite;
    }

    /* Warning animations for pending items */
    @keyframes warningPulse {
        0% {
            box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(245, 158, 11, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(245, 158, 11, 0);
        }
    }

    .warning-pulse {
        animation: warningPulse 2s infinite;
    }
</style>

<!-- Role-based JavaScript functionality -->
<script>
    // Role-based dashboard functionality
    const userRole = '<?php echo $is_head ? "head" : "secretary"; ?>';
    const pendingCount = <?php echo $pending_course_count; ?>;
    const financialPendingCount = <?php echo $pending_financial_approval; ?>;

    // Add pulsing animation to urgent items
    document.addEventListener('DOMContentLoaded', function() {
        // Add warning pulse to pending items if user is head
        if (userRole === 'head' && (pendingCount > 0 || financialPendingCount > 0)) {
            const alertElement = document.querySelector('.alert-modern');
            if (alertElement) {
                alertElement.classList.add('warning-pulse');
            }
        }

        // Add success pulse to performance metrics if doing well
        const completionRate = <?php echo ($completed_enrollment_count / max($enrollment_count, 1)) * 100; ?>;
        if (completionRate > 80) {
            const performanceCards = document.querySelectorAll('.progress-ring');
            performanceCards.forEach(card => {
                card.closest('.col-6').classList.add('success-pulse');
            });
        }

        // Role-specific welcome message (you can customize this)
        if (userRole === 'secretary') {
            console.log('Welcome, Department Secretary! You have viewing access to most dashboard features.');
        } else {
            console.log('Welcome, Department Head! You have full administrative access.');
        }
    });

    // Function to check permissions before navigation
    function checkPermission(action, requiredRole = 'head') {
        if (userRole !== requiredRole) {
            alert(`This action requires ${requiredRole} privileges. Please contact your department head.`);
            return false;
        }
        return true;
    }

    // Add event listeners for restricted actions
    document.querySelectorAll('.quick-action-card.disabled').forEach(card => {
        card.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.querySelector('span').textContent.trim();
            alert(`"${action}" requires Department Head privileges.`);
        });
    });

    // Update page title based on role
    document.title = `${document.title} - ${userRole === 'head' ? 'Department Head' : 'Department Secretary'}`;
</script>

<?php include '../includes/department/footer.php'; ?>