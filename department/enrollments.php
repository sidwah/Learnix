<?php //department/enrollments.php ?>
<?php include '../includes/department/header.php'; ?>

<?php
// Get department information and enrollments
try {
    $user_id = $_SESSION['user_id'];
    
    // Get department info for the logged-in user
    $dept_query = "SELECT d.department_id, d.name as department_name, d.code as department_code 
                   FROM departments d 
                   INNER JOIN department_staff ds ON d.department_id = ds.department_id 
                   WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' AND ds.deleted_at IS NULL";
    
    $dept_stmt = $conn->prepare($dept_query);
    $dept_stmt->bind_param("i", $user_id);
    $dept_stmt->execute();
    $dept_result = $dept_stmt->get_result();
    
    if ($dept_result->num_rows === 0) {
        header('Location: ../auth/login.php');
        exit();
    }
    
    $department = $dept_result->fetch_assoc();
    $department_id = $department['department_id'];
    
    // Get enrollment statistics
    $stats_query = "SELECT 
                        COUNT(DISTINCT e.enrollment_id) as total_enrollments,
                        SUM(CASE WHEN e.status = 'Suspended' THEN 1 ELSE 0 END) as suspended_enrollments,
                        COUNT(DISTINCT e.course_id) as enrolled_courses,
                        SUM(CASE WHEN e.status = 'Active' THEN 1 ELSE 0 END) as active_enrollments,
                        SUM(CASE WHEN e.status = 'Completed' THEN 1 ELSE 0 END) as completed_enrollments,
                        SUM(CASE WHEN e.completion_percentage >= 50 THEN 1 ELSE 0 END) as progress_50_plus,
                        AVG(e.completion_percentage) as avg_completion
                    FROM enrollments e
                    INNER JOIN courses c ON e.course_id = c.course_id
                    WHERE c.department_id = ? AND e.deleted_at IS NULL AND c.deleted_at IS NULL";
    
    $stats_stmt = $conn->prepare($stats_query);
    $stats_stmt->bind_param("i", $department_id);
    $stats_stmt->execute();
    $stats_result = $stats_stmt->get_result();
    $stats = $stats_result->fetch_assoc();
    
    // Get detailed enrollments with actual progress calculation
    $enrollments_query = "SELECT 
                            e.enrollment_id,
                            e.user_id,
                            e.course_id,
                            e.enrolled_at,
                            e.status,
                            e.completion_percentage,
                            e.last_accessed,
                            e.expiry_date,
                            c.title as course_title,
                            c.thumbnail as course_thumbnail,
                            c.course_level,
                            c.price,
                            u.first_name,
                            u.last_name,
                            u.email,
                            u.profile_pic,
                            sub.name as subcategory_name,
                            cat.name as category_name,
                            p.enrollment_id as has_payment,
                            p.amount as payment_amount,
                            p.payment_date,
                            p.status as payment_status,
                            -- Calculate actual progress from all sections
                            COALESCE(
                                (SELECT 
                                    (COUNT(CASE WHEN prog.completion_status = 'Completed' THEN 1 END) * 100.0) / 
                                    NULLIF(COUNT(st.topic_id), 0)
                                FROM course_sections cs
                                LEFT JOIN section_topics st ON cs.section_id = st.section_id 
                                LEFT JOIN progress prog ON st.topic_id = prog.topic_id AND prog.enrollment_id = e.enrollment_id AND prog.deleted_at IS NULL
                                WHERE cs.course_id = c.course_id AND cs.deleted_at IS NULL
                                ), 0
                            ) as calculated_progress
                        FROM enrollments e
                        INNER JOIN courses c ON e.course_id = c.course_id
                        INNER JOIN users u ON e.user_id = u.user_id
                        LEFT JOIN subcategories sub ON c.subcategory_id = sub.subcategory_id
                        LEFT JOIN categories cat ON sub.category_id = cat.category_id
                        LEFT JOIN course_payments p ON e.enrollment_id = p.enrollment_id AND p.status = 'Completed'
                        WHERE c.department_id = ? AND e.deleted_at IS NULL AND c.deleted_at IS NULL AND u.deleted_at IS NULL
                        ORDER BY e.enrolled_at DESC";
    
    $enrollments_stmt = $conn->prepare($enrollments_query);
    $enrollments_stmt->bind_param("i", $department_id);
    $enrollments_stmt->execute();
    $enrollments_result = $enrollments_stmt->get_result();
    $enrollments = $enrollments_result->fetch_all(MYSQLI_ASSOC);
    
} catch (Exception $e) {
    error_log("Error fetching department enrollments: " . $e->getMessage());
    $enrollments = [];
    $stats = [
        'total_enrollments' => 0,
        'suspended_enrollments' => 0,
        'enrolled_courses' => 0,
        'active_enrollments' => 0,
        'completed_enrollments' => 0,
        'progress_50_plus' => 0,
        'avg_completion' => 0
    ];
}

// Helper functions
function getProgressBar($percentage) {
    $percentage = (float)$percentage;
    $color = 'bg-danger';
    if ($percentage >= 75) $color = 'bg-success';
    elseif ($percentage >= 50) $color = 'bg-warning';
    elseif ($percentage >= 25) $color = 'bg-info';
    
    return '
        <div class="progress" style="height: 8px;">
            <div class="progress-bar ' . $color . '" role="progressbar" style="width: ' . $percentage . '%;" 
                 aria-valuenow="' . $percentage . '" aria-valuemin="0" aria-valuemax="100">
            </div>
        </div>
        <small class="text-muted">' . number_format($percentage, 1) . '%</small>
    ';
}

function getCourseThumbnail($thumbnail) {
    if (!empty($thumbnail) && file_exists("../uploads/thumbnails/" . $thumbnail)) {
        return "../uploads/thumbnails/" . $thumbnail;
    }
    return "../uploads/thumbnails/default.jpg";
}

function getStudentProfilePic($profile_pic) {
    if (!empty($profile_pic) && file_exists("../uploads/profile/" . $profile_pic)) {
        return "../uploads/profile/" . $profile_pic;
    }
    return "../uploads/profile/default.png";
}

function formatCurrency($amount) {
    return 'GHS ' . number_format((float)$amount, 2);
}

function truncateTitle($title, $maxLength = 35) {
    if (strlen($title) <= $maxLength) {
        return $title;
    }
    return substr($title, 0, $maxLength) . '...';
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
            <div class="row align-items-center mb-3">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">Student Enrollments</h1>
                    <p class="page-header-text">Monitor student enrollments and progress for <?php echo htmlspecialchars($department['department_name']); ?> courses</p>
                </div>
                <div class="col-sm-auto">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="exportEnrollments()">
                            <i class="bi-download me-1"></i> Export Data
                        </button>
                        <button type="button" class="btn btn-primary" onclick="refreshData()">
                            <i class="bi-arrow-clockwise me-1"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="card-subtitle mb-2">Total Enrollments</h6>
                                <span class="h3 text-dark"><?php echo number_format($stats['total_enrollments']); ?></span>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-md icon-soft-primary">
                                    <i class="bi-people"></i>
                                </div>
                            </div>
                        </div>
                        <span class="badge bg-soft-info text-info mt-2">
                            <i class="bi-info-circle"></i> All Time
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="card-subtitle mb-2">Suspended Enrollments</h6>
                                <span class="h3 text-dark"><?php echo number_format($stats['suspended_enrollments']); ?></span>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-md icon-soft-danger">
                                    <i class="bi-pause-circle"></i>
                                </div>
                            </div>
                        </div>
                        <span class="badge bg-soft-danger text-danger mt-2">
                            <i class="bi-exclamation-triangle"></i> Suspended
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="card-subtitle mb-2">Active Enrollments</h6>
                                <span class="h3 text-dark"><?php echo number_format($stats['active_enrollments']); ?></span>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-md icon-soft-warning">
                                    <i class="bi-play-circle"></i>
                                </div>
                            </div>
                        </div>
                        <span class="badge bg-soft-warning text-warning mt-2">
                            <i class="bi-activity"></i> In Progress
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="card-subtitle mb-2">Completion Rate</h6>
                                <span class="h3 text-dark"><?php echo number_format($stats['avg_completion'], 1); ?>%</span>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-md icon-soft-secondary">
                                    <i class="bi-bar-chart"></i>
                                </div>
                            </div>
                        </div>
                        <span class="badge bg-soft-secondary text-secondary mt-2">
                            <i class="bi-trophy"></i> Average
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Statistics Cards -->

        <!-- Enrollments Table -->
        <div class="card">
            <div class="card-header card-header-content-md-between">
                <div class="mb-2 mb-md-0">
                    <h4 class="card-header-title">Student Enrollments</h4>
                </div>
                
                <!-- Search and Filter -->
                <div class="d-flex gap-2 align-items-center">
                    <div class="input-group input-group-sm" style="width: 400px;">
                        <div class="input-group-text">
                            <i class="bi-search"></i>
                        </div>
                        <input type="search" class="form-control" placeholder="Search students or courses..." id="enrollmentSearch">
                    </div>
                    
                    <div class="ms-3">
                        <select class="form-select form-select-sm" id="progressFilter" style="width: 150px;">
                            <option value="all">All Progress</option>
                            <option value="0-25">0-25%</option>
                            <option value="26-50">26-50%</option>
                            <option value="51-75">51-75%</option>
                            <option value="76-100">76-100%</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-responsive datatable-custom">
                <?php if (count($enrollments) > 0): ?>
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table" id="enrollmentsTable">
                    <thead class="thead-light">
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Progress</th>
                            <th>Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrollments as $enrollment): 
                            $student_profile_pic = getStudentProfilePic($enrollment['profile_pic']);
                            $course_thumbnail = getCourseThumbnail($enrollment['course_thumbnail']);
                            $progress = (float)$enrollment['calculated_progress']; // Use calculated progress
                        ?>
                        <tr data-progress="<?php echo $progress; ?>" 
                            data-enrollment-id="<?php echo $enrollment['enrollment_id']; ?>"
                            data-user-id="<?php echo $enrollment['user_id']; ?>"
                            data-course-id="<?php echo $enrollment['course_id']; ?>"
                            data-status="<?php echo $enrollment['status']; ?>"
                            data-student-name="<?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?>"
                            data-student-email="<?php echo htmlspecialchars($enrollment['email']); ?>"
                            data-course-title="<?php echo htmlspecialchars($enrollment['course_title']); ?>"
                            data-enrolled-date="<?php echo date('M j, Y', strtotime($enrollment['enrolled_at'])); ?>"
                            data-last-accessed="<?php echo $enrollment['last_accessed'] ? date('M j, Y g:i A', strtotime($enrollment['last_accessed'])) : 'Never'; ?>">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar avatar-sm avatar-circle">
                                            <img class="avatar-img" src="<?php echo $student_profile_pic; ?>" alt="Student" style="object-fit: cover;">
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="text-inherit mb-0">
                                            <?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?>
                                        </h6>
                                        <p class="fs-6 text-body mb-0"><?php echo htmlspecialchars($enrollment['email']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <img class="avatar avatar-sm" src="<?php echo $course_thumbnail; ?>" alt="Course" style="object-fit: cover; border-radius: 0.375rem;">
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="text-inherit mb-0" title="<?php echo htmlspecialchars($enrollment['course_title']); ?>">
                                            <?php echo htmlspecialchars(truncateTitle($enrollment['course_title'])); ?>
                                        </h6>
                                        <p class="fs-6 text-body mb-0">
                                            <span class="badge bg-soft-primary text-primary">
                                                <?php echo htmlspecialchars($enrollment['category_name'] ?? 'Uncategorized'); ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php echo getProgressBar($progress); ?>
                            </td>
                            <td>
                                <?php if ($enrollment['has_payment']): ?>
                                    <span class="d-block fw-semibold text-success"><?php echo formatCurrency($enrollment['payment_amount']); ?></span>
                                    <small class="text-muted">Paid</small>
                                <?php elseif ($enrollment['price'] > 0): ?>
                                    <span class="d-block fw-semibold text-warning"><?php echo formatCurrency($enrollment['price']); ?></span>
                                    <small class="text-warning">Pending</small>
                                <?php else: ?>
                                    <span class="badge bg-soft-info text-info">Free</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-circle btn-white btn-sm" 
                                            onclick="viewEnrollmentDetails(<?php echo $enrollment['enrollment_id']; ?>)" 
                                            data-bs-toggle="tooltip" title="View Details">
                                        <i class="bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-circle btn-soft-primary btn-sm" 
                                            onclick="viewStudentProgress(<?php echo $enrollment['enrollment_id']; ?>)" 
                                            data-bs-toggle="tooltip" title="View Progress">
                                        <i class="bi-graph-up"></i>
                                    </button>
                                    <?php if ($enrollment['status'] === 'Active'): ?>
                                    <div class="btn-group dropdown">
                                        <button type="button" class="btn btn-circle btn-soft-secondary btn-sm dropdown-toggle" 
                                                data-bs-toggle="dropdown" aria-expanded="false"
                                                title="More Actions">
                                            <i class="bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="openSuspendModal(<?php echo $enrollment['enrollment_id']; ?>, '<?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?>'); return false;">
                                                    <i class="bi-pause-circle dropdown-item-icon"></i> Suspend
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="openReminderModal(<?php echo $enrollment['enrollment_id']; ?>, '<?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?>', '<?php echo htmlspecialchars($enrollment['course_title']); ?>'); return false;">
                                                    <i class="bi-bell dropdown-item-icon"></i> Send Reminder
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>

                <!-- Empty State -->
                <div class="empty-state <?php echo count($enrollments) > 0 ? 'd-none' : ''; ?>" id="emptyState">
                    <div class="text-center py-5">
                        <div class="empty-state-icon mb-4">
                            <i class="bi-people text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
                        </div>
                        <h4 class="text-muted mb-3">No enrollments found</h4>
                        <p class="text-muted mb-4">
                            <span id="emptyStateMessage">
                                <?php echo count($enrollments) === 0 ? "No students have enrolled in your department's courses yet." : "No enrollments match your current filters."; ?>
                            </span>
                        </p>
                        <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                            <i class="bi-arrow-clockwise me-1"></i> Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <?php if (count($enrollments) > 0): ?>
            <div class="card-footer">
                <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
                    <div class="col-sm mb-2 mb-sm-0">
                        <div class="d-flex justify-content-center justify-content-sm-start align-items-center">
                            <span class="me-2">Showing:</span>
                            <select class="form-select form-select-sm" style="width: auto;">
                                <option value="25" selected>25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="All">All</option>
                            </select>
                            <span class="text-secondary mx-2">of</span>
                            <span id="totalEnrollments"><?php echo count($enrollments); ?></span>
                        </div>
                    </div>

                    <div class="col-sm-auto">
                        <nav aria-label="Enrollments pagination">
                            <ul class="pagination pagination-sm modern-pagination mb-0">
                                <li class="page-item">
                                    <a class="page-link" href="#" aria-label="Previous">
                                        <i class="bi-chevron-left"></i>
                                    </a>
                                </li>
                                <li class="page-item active">
                                    <a class="page-link" href="#">1</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#" aria-label="Next">
                                        <i class="bi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <!-- End Enrollments Table -->

        <!-- Enrollment Details Modal -->
        <div class="modal fade" id="enrollmentDetailsModal" tabindex="-1" aria-labelledby="enrollmentDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="enrollmentDetailsModalLabel">Enrollment Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="enrollmentDetailsContent">
                            <!-- Details will be loaded here -->
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Enrollment Details Modal -->

        <!-- Student Progress Modal -->
        <div class="modal fade" id="studentProgressModal" tabindex="-1" aria-labelledby="studentProgressModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="studentProgressModalLabel">Student Progress</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="studentProgressContent">
                            <!-- Progress details will be loaded here -->
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2">Loading student progress...</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Student Progress Modal -->

        <!-- Suspend Confirmation Modal -->
        <div class="modal fade" id="suspendConfirmModal" tabindex="-1" aria-labelledby="suspendConfirmModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="suspendConfirmModalLabel">
                            <i class="bi-exclamation-triangle text-warning me-2"></i>Suspend Enrollment
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning" role="alert">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi-exclamation-triangle me-2"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <strong>Warning:</strong> This action will immediately suspend the student's access to the course.
                                </div>
                            </div>
                        </div>
                        
                        <p>Are you sure you want to suspend <strong id="suspendStudentName"></strong>'s enrollment?</p>
                        
                        <div class="mb-3">
                            <h6 class="text-cap mb-2">This will:</h6>
                            <ul class="list-unstyled">
                                <li class="mb-1"><i class="bi-x-circle text-danger me-2"></i>Immediately revoke access to the course</li>
                                <li class="mb-1"><i class="bi-pause text-warning me-2"></i>Stop progress tracking</li>
                                <li class="mb-1"><i class="bi-gear text-info me-2"></i>Require manual reactivation</li>
                            </ul>
                        </div>
                        
                        <div class="mb-3">
                            <label for="suspendReason" class="form-label">Reason for suspension (optional)</label>
                            <textarea class="form-control" id="suspendReason" rows="3" placeholder="Enter reason for suspension..."></textarea>
                        </div>
                        
                        <p class="text-muted small">
                            <i class="bi-info-circle me-1"></i>This action can be reversed later by changing the enrollment status.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmSuspendBtn">
                            <i class="bi-pause-circle me-1"></i>Suspend Enrollment
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Suspend Confirmation Modal -->

        <!-- Send Reminder Modal -->
        <div class="modal fade" id="reminderModal" tabindex="-1" aria-labelledby="reminderModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="reminderModalLabel">
                            <i class="bi-bell text-primary me-2"></i>Send Progress Reminder
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info" role="alert">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi-info-circle me-2"></i>
                                </div>
                                <div class="flex-grow-1">
                                    Send a motivational email to <strong id="reminderStudentName"></strong> about their progress in <strong id="reminderCourseName"></strong>.
                               </div>
                           </div>
                       </div>
                       
                       <div class="mb-4">
                           <label for="reminderTemplate" class="form-label">Choose a reminder template</label>
                           <select class="form-select" id="reminderTemplate" onchange="updateReminderContent()">
                               <option value="">Select a template...</option>
                               <option value="encouragement">General Encouragement</option>
                               <option value="progress">Progress Motivation</option>
                               <option value="deadline">Gentle Deadline Reminder</option>
                               <option value="achievement">Achievement Recognition</option>
                               <option value="support">Offer Support</option>
                               <option value="custom">Custom Message</option>
                           </select>
                       </div>
                       
                       <div class="mb-3">
                           <label for="reminderSubject" class="form-label">Email Subject</label>
                           <input type="text" class="form-control" id="reminderSubject" placeholder="Enter email subject">
                       </div>
                       
                       <div class="mb-3">
                           <label for="reminderMessage" class="form-label">Message</label>
                           <textarea class="form-control" id="reminderMessage" rows="6" placeholder="Enter your custom message..."></textarea>
                           <div class="form-text">
                               <i class="bi-lightbulb me-1"></i>The system will automatically include course progress details and a direct link to continue learning.
                           </div>
                       </div>
                       
                       <div class="mb-3">
                           <div class="form-check">
                               <input class="form-check-input" type="checkbox" id="includeProgress" checked>
                               <label class="form-check-label" for="includeProgress">
                                   Include detailed progress information
                               </label>
                           </div>
                           <div class="form-check">
                               <input class="form-check-input" type="checkbox" id="includeCourseLink" checked>
                               <label class="form-check-label" for="includeCourseLink">
                                   Include direct link to course
                               </label>
                           </div>
                       </div>
                   </div>
                   <div class="modal-footer">
                       <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
                       <button type="button" class="btn btn-primary" id="sendReminderBtn">
                           <i class="bi-send me-1"></i>Send Reminder
                       </button>
                   </div>
               </div>
           </div>
       </div>
       <!-- End Send Reminder Modal -->

   </div>
   <!-- End Content -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- Toast Notifications -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
   <!-- Success Toast -->
   <div id="successToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
       <div class="toast-header bg-success text-white">
           <i class="bi-check-circle-fill me-2"></i>
           <strong class="me-auto">Success</strong>
           <small>just now</small>
           <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
       </div>
       <div class="toast-body" id="successToastBody">
           Operation completed successfully!
       </div>
   </div>

   <!-- Error Toast -->
   <div id="errorToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
       <div class="toast-header bg-danger text-white">
           <i class="bi-exclamation-circle-fill me-2"></i>
           <strong class="me-auto">Error</strong>
           <small>just now</small>
           <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
       </div>
       <div class="toast-body" id="errorToastBody">
           An error occurred. Please try again.
       </div>
   </div>

   <!-- Info Toast -->
   <div id="infoToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
       <div class="toast-header bg-primary text-white">
           <i class="bi-info-circle-fill me-2"></i>
           <strong class="me-auto">Information</strong>
           <small>just now</small>
           <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
       </div>
       <div class="toast-body" id="infoToastBody">
           Information message here.
       </div>
   </div>
</div>

<script>
// Global variables for modals
let currentEnrollmentId = null;
let currentStudentName = null;
let currentCourseName = null;

// Predefined reminder templates
const reminderTemplates = {
   encouragement: {
       subject: "Keep Going! Your Learning Journey Awaits",
       message: `Hi {student_name},

I hope this message finds you well! I wanted to reach out and encourage you to continue with your learning journey in {course_name}.

Learning new skills takes time and dedication, and I believe in your ability to succeed. Every small step forward is progress worth celebrating.

Remember, the best investment you can make is in yourself and your education. Take it one topic at a time, and don't hesitate to reach out if you need any support.

Keep up the great work!

Best regards,
{department_name} Department`
   },
   progress: {
       subject: "Great Progress in {course_name}! Let's Keep the Momentum",
       message: `Dear {student_name},

I've been reviewing the progress in our {course_name} course, and I wanted to personally congratulate you on the work you've done so far!

Your dedication to learning is inspiring, and I can see the effort you're putting in. Every topic you complete brings you closer to mastering these valuable skills.

I encourage you to keep building on this momentum. The next sections are equally engaging and will build upon what you've already learned.

If you have any questions or need clarification on any topics, please don't hesitate to reach out.

Looking forward to seeing your continued success!

Best regards,
{department_name} Department`
   },
   deadline: {
       subject: "Friendly Reminder: Continue Your Learning in {course_name}",
       message: `Hello {student_name},

I hope you're doing well! I wanted to send a gentle reminder about your enrollment in {course_name}.

Learning at your own pace is perfectly fine, and I want to ensure you have all the support you need to succeed. Sometimes life gets busy, but I encourage you to set aside some time for your learning journey.

Even dedicating just 15-30 minutes a day can make a significant difference in your progress and retention of the material.

If there's anything preventing you from continuing or if you need any assistance, please let me know. I'm here to help!

Warm regards,
{department_name} Department`
   },
   achievement: {
       subject: "Celebrating Your Learning Achievements in {course_name}",
       message: `Dear {student_name},

I wanted to take a moment to acknowledge and celebrate your achievements in {course_name}!

Your commitment to learning and the progress you've made so far is commendable. It's students like you who make teaching so rewarding.

As you continue your learning journey, remember that each topic you master is building a foundation for your future success. The skills you're developing will serve you well in your career and personal growth.

Keep up the excellent work, and remember that I'm here to support you every step of the way.

Congratulations on your progress!

Best regards,
{department_name} Department`
   },
   support: {
       subject: "Here to Support Your Learning Journey in {course_name}",
       message: `Hi {student_name},

I hope you're enjoying your experience in {course_name}. As your department head, I want you to know that your success is important to me.

Learning can sometimes be challenging, and that's completely normal. If you're finding any topics difficult or if you have questions about the course material, please don't hesitate to reach out.

We have various resources available to support your learning:
- Discussion forums for peer interaction
- Additional reading materials
- One-on-one support sessions (if needed)

Remember, asking for help is a sign of strength, not weakness. We're all here to ensure your success.

Please feel free to contact me if you need any assistance or guidance.

Best regards,
{department_name} Department`
   }
};

document.addEventListener('DOMContentLoaded', function() {
   // Initialize tooltips
   try {
       var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
       var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
           return new bootstrap.Tooltip(tooltipTriggerEl);
       });
   } catch (error) {
       console.log('Tooltip initialization skipped:', error);
   }

   // Filter functionality
   const progressFilter = document.getElementById('progressFilter');
   const searchInput = document.getElementById('enrollmentSearch');
   const enrollmentRows = document.querySelectorAll('#enrollmentsTable tbody tr');
   const emptyState = document.getElementById('emptyState');
   const tableContainer = document.querySelector('#enrollmentsTable');
   
   function updateTableVisibility() {
       if (!tableContainer) return;
       
       const visibleRows = Array.from(enrollmentRows).filter(row => row.style.display !== 'none');
       
       if (visibleRows.length === 0) {
           if (tableContainer) tableContainer.style.display = 'none';
           emptyState.classList.remove('d-none');
           
           const emptyMessage = document.getElementById('emptyStateMessage');
           emptyMessage.textContent = "No enrollments match your current filters.";
       } else {
           if (tableContainer) tableContainer.style.display = '';
           emptyState.classList.add('d-none');
       }
   }
   
   function filterRows() {
       const progressValue = progressFilter.value;
       const searchTerm = searchInput.value.toLowerCase();
       
       enrollmentRows.forEach(row => {
           let show = true;
           
           // Progress filter
           if (progressValue !== 'all' && show) {
               const progress = parseFloat(row.dataset.progress);
               let progressRange = progressValue.split('-');
               let min = parseInt(progressRange[0]);
               let max = parseInt(progressRange[1]);
               
               if (progress < min || progress > max) {
                   show = false;
               }
           }
           
           // Search filter
           if (searchTerm && show) {
               const studentName = row.querySelector('h6').textContent.toLowerCase();
               const studentEmail = row.querySelector('p').textContent.toLowerCase();
               const courseName = row.querySelector('td:nth-child(2) h6').textContent.toLowerCase();
               
               if (!studentName.includes(searchTerm) && 
                   !studentEmail.includes(searchTerm) && 
                   !courseName.includes(searchTerm)) {
                   show = false;
               }
           }
           
           row.style.display = show ? '' : 'none';
       });
       
       updateTableVisibility();
   }
   
   // Add event listeners for filters
   if (progressFilter) progressFilter.addEventListener('change', filterRows);
   if (searchInput) searchInput.addEventListener('input', filterRows);

   // Modal event listeners
   document.getElementById('confirmSuspendBtn').addEventListener('click', function() {
       if (currentEnrollmentId) {
           processSuspendEnrollment();
       }
   });

   document.getElementById('sendReminderBtn').addEventListener('click', function() {
       if (currentEnrollmentId) {
           processSendReminder();
       }
   });
});

// Action functions
function viewEnrollmentDetails(enrollmentId) {
   const modal = new bootstrap.Modal(document.getElementById('enrollmentDetailsModal'));
   const contentDiv = document.getElementById('enrollmentDetailsContent');
   
   // Show loading
   contentDiv.innerHTML = `
       <div class="text-center py-4">
           <div class="spinner-border text-primary" role="status">
               <span class="visually-hidden">Loading...</span>
           </div>
           <p class="text-muted mt-2">Loading enrollment details...</p>
       </div>
   `;
   
   modal.show();
   
   // Find enrollment data from current page
   const enrollmentRow = document.querySelector(`[data-enrollment-id="${enrollmentId}"]`);
   if (enrollmentRow) {
       const studentName = enrollmentRow.dataset.studentName;
       const studentEmail = enrollmentRow.dataset.studentEmail;
       const courseTitle = enrollmentRow.dataset.courseTitle;
       const enrolledDate = enrollmentRow.dataset.enrolledDate;
       const lastAccessed = enrollmentRow.dataset.lastAccessed;
       const status = enrollmentRow.dataset.status;
       const progress = enrollmentRow.dataset.progress;
       const payment = enrollmentRow.querySelector('td:nth-child(4)').innerHTML;
       
       // Get status badge
       let statusBadge = '';
       switch(status) {
           case 'Active':
               statusBadge = '<span class="badge bg-soft-success text-success"><i class="bi-play-circle me-1"></i>Active</span>';
               break;
           case 'Completed':
               statusBadge = '<span class="badge bg-soft-primary text-primary"><i class="bi-check-circle me-1"></i>Completed</span>';
               break;
           case 'Expired':
               statusBadge = '<span class="badge bg-soft-warning text-warning"><i class="bi-clock me-1"></i>Expired</span>';
               break;
           case 'Suspended':
               statusBadge = '<span class="badge bg-soft-danger text-danger"><i class="bi-pause-circle me-1"></i>Suspended</span>';
               break;
           default:
               statusBadge = `<span class="badge bg-soft-secondary text-secondary">${status}</span>`;
       }
       
       contentDiv.innerHTML = `
           <div class="row">
               <div class="col-md-6 mb-4">
                   <h6 class="text-cap">Student Information</h6>
                   <div class="card border-0 bg-soft-primary">
                       <div class="card-body">
                           <h6 class="card-title">${studentName}</h6>
                           <p class="card-text text-muted">${studentEmail}</p>
                       </div>
                   </div>
               </div>
               <div class="col-md-6 mb-4">
                   <h6 class="text-cap">Course Information</h6>
                   <div class="card border-0 bg-soft-info">
                       <div class="card-body">
                           <h6 class="card-title">${courseTitle}</h6>
                       </div>
                   </div>
               </div>
               <div class="col-md-3 mb-3">
                   <h6 class="text-cap">Enrollment Date</h6>
                   <p class="text-body">${enrolledDate}</p>
               </div>
               <div class="col-md-3 mb-3">
                   <h6 class="text-cap">Status</h6>
                   <div>${statusBadge}</div>
               </div>
               <div class="col-md-3 mb-3">
                   <h6 class="text-cap">Last Activity</h6>
                   <p class="text-body">${lastAccessed}</p>
               </div>
               <div class="col-md-3 mb-3">
                   <h6 class="text-cap">Progress</h6>
                   <div class="progress mb-2" style="height: 12px;">
                       <div class="progress-bar bg-primary" style="width: ${progress}%"></div>
                   </div>
                   <small class="text-muted">${parseFloat(progress).toFixed(1)}% Complete</small>
               </div>
               <div class="col-md-12 mb-3">
                   <h6 class="text-cap">Payment Information</h6>
                   <div>${payment}</div>
               </div>
           </div>
       `;
   }
}

function viewStudentProgress(enrollmentId) {
   const modal = new bootstrap.Modal(document.getElementById('studentProgressModal'));
   const contentDiv = document.getElementById('studentProgressContent');
   
   // Show loading
   contentDiv.innerHTML = `
       <div class="text-center py-4">
           <div class="spinner-border text-primary" role="status">
               <span class="visually-hidden">Loading...</span>
           </div>
           <p class="text-muted mt-2">Loading detailed progress...</p>
       </div>
   `;
   
   modal.show();
   
   // Find enrollment data from current page
   const enrollmentRow = document.querySelector(`[data-enrollment-id="${enrollmentId}"]`);
   if (enrollmentRow) {
       const studentName = enrollmentRow.dataset.studentName;
       const courseTitle = enrollmentRow.dataset.courseTitle;
       const courseId = enrollmentRow.dataset.courseId;
       const userId = enrollmentRow.dataset.userId;
       
       // Update modal title
       document.getElementById('studentProgressModalLabel').textContent = `${studentName} - Progress Details`;
       
       // Fetch detailed progress from backend
       fetch('../backend/department/get_student_progress.php', {
           method: 'POST',
           headers: {
               'Content-Type': 'application/json',
           },
           body: JSON.stringify({
               enrollment_id: enrollmentId,
               user_id: userId,
               course_id: courseId
           })
       })
       .then(response => response.json())
       .then(data => {
           if (data.success) {
               displayProgressDetails(data.progress, studentName, courseTitle);
           } else {
               contentDiv.innerHTML = `
                   <div class="text-center py-4">
                       <i class="bi-exclamation-circle text-danger" style="font-size: 3rem;"></i>
                       <h5 class="text-danger mt-2">Error Loading Progress</h5>
                       <p class="text-muted">Unable to load detailed progress information</p>
                   </div>
               `;
           }
       })
       .catch(error => {
           console.error('Error:', error);
           contentDiv.innerHTML = `
               <div class="text-center py-4">
                   <i class="bi-exclamation-circle text-danger" style="font-size: 3rem;"></i>
                   <h5 class="text-danger mt-2">Connection Error</h5>
                   <p class="text-muted">Unable to fetch progress data</p>
               </div>
           `;
       });
   }
}

function displayProgressDetails(progressData, studentName, courseTitle) {
   const contentDiv = document.getElementById('studentProgressContent');
   
   contentDiv.innerHTML = `
       <div class="row mb-4">
           <div class="col-md-8">
               <h6 class="text-cap">Course</h6>
               <h5>${courseTitle}</h5>
           </div>
           <div class="col-md-4 text-md-end">
               <h6 class="text-cap">Overall Progress</h6>
               <div class="d-flex align-items-center justify-content-md-end">
                   <div class="progress me-3" style="width: 100px; height: 12px;">
                       <div class="progress-bar bg-primary" style="width: ${progressData.overall_progress}%"></div>
                   </div>
                   <span class="fw-bold">${progressData.overall_progress.toFixed(1)}%</span>
               </div>
           </div>
       </div>
       
       <div class="row">
           <div class="col-md-3 mb-3">
               <div class="card text-center">
                   <div class="card-body">
                       <h6 class="card-title text-cap">Topics Completed</h6>
                       <h3 class="text-primary">${progressData.completed_topics}</h3>
                       <small class="text-muted">out of ${progressData.total_topics}</small>
                   </div>
               </div>
           </div>
           <div class="col-md-3 mb-3">
               <div class="card text-center">
                   <div class="card-body">
                       <h6 class="card-title text-cap">Quizzes Passed</h6>
                       <h3 class="text-success">${progressData.passed_quizzes}</h3>
                       <small class="text-muted">out of ${progressData.total_quizzes}</small>
                   </div>
               </div>
           </div>
           <div class="col-md-3 mb-3">
               <div class="card text-center">
                   <div class="card-body">
                       <h6 class="card-title text-cap">Time Spent</h6>
                       <h3 class="text-info">${progressData.time_spent}h</h3>
                       <small class="text-muted">learning time</small>
                   </div>
               </div>
           </div>
           <div class="col-md-3 mb-3">
               <div class="card text-center">
                   <div class="card-body">
                       <h6 class="card-title text-cap">Avg. Score</h6>
                       <h3 class="text-warning">${progressData.average_score}%</h3>
                       <small class="text-muted">quiz average</small>
                   </div>
               </div>
           </div>
       </div>
       
       <div class="mt-4">
           <h6 class="text-cap mb-3">Section Progress</h6>
           <div class="list-group">
               ${generateSectionProgressFromData(progressData.sections)}
           </div>
       </div>
   `;
}

function generateSectionProgressFromData(sections) {
   let html = '';
   sections.forEach((section, index) => {
       const isCompleted = section.progress >= 100;
       const isCurrent = section.progress > 0 && section.progress < 100;
       
       html += `
           <div class="list-group-item d-flex justify-content-between align-items-center">
               <div class="d-flex align-items-center">
                   <div class="me-3">
                       ${isCompleted ? '<i class="bi-check-circle-fill text-success"></i>' : 
                         isCurrent ? '<i class="bi-play-circle-fill text-primary"></i>' : 
                         '<i class="bi-circle text-muted"></i>'}
                   </div>
                   <div>
                       <h6 class="mb-0">${section.title}</h6>
                       <small class="text-muted">${section.completed_topics} of ${section.total_topics} topics</small>
                   </div>
               </div>
               <div class="text-end">
                   <div class="progress" style="width: 80px; height: 8px;">
                       <div class="progress-bar ${isCompleted ? 'bg-success' : isCurrent ? 'bg-primary' : 'bg-light'}" 
                            style="width: ${section.progress}%"></div>
                   </div>
                   <small class="text-muted">${section.progress.toFixed(0)}%</small>
               </div>
           </div>
       `;
   });
   
   return html;
}

// Suspend Modal Functions
function openSuspendModal(enrollmentId, studentName) {
   currentEnrollmentId = enrollmentId;
   currentStudentName = studentName;
   
   document.getElementById('suspendStudentName').textContent = studentName;
   document.getElementById('suspendReason').value = '';
   
   const modal = new bootstrap.Modal(document.getElementById('suspendConfirmModal'));
   modal.show();
}

function processSuspendEnrollment() {
   const reason = document.getElementById('suspendReason').value.trim();
   
   // Show loading state
   const confirmBtn = document.getElementById('confirmSuspendBtn');
   const originalText = confirmBtn.innerHTML;
   confirmBtn.innerHTML = '<div class="spinner-border spinner-border-sm me-2" role="status"></div>Processing...';
   confirmBtn.disabled = true;
   
   // Make API call to suspend enrollment
   fetch('../backend/department/suspend_enrollment.php', {
       method: 'POST',
       headers: {
           'Content-Type': 'application/json',
       },
       body: JSON.stringify({
           enrollment_id: currentEnrollmentId,
           action: 'suspend',
           reason: reason
       })
   })
   .then(response => response.json())
   .then(data => {
       // Reset button state
       confirmBtn.innerHTML = originalText;
       confirmBtn.disabled = false;
       
       if (data.success) {
           // Close modal
           const modal = bootstrap.Modal.getInstance(document.getElementById('suspendConfirmModal'));
           modal.hide();
           
           showToast(`${currentStudentName}'s enrollment has been suspended successfully.`, 'success');
           
           // Refresh the page after delay
           setTimeout(() => {
               window.location.reload();
           }, 2000);
       } else {
           showToast(data.message || 'Failed to suspend enrollment.', 'error');
       }
   })
   .catch(error => {
       console.error('Error:', error);
       confirmBtn.innerHTML = originalText;
       confirmBtn.disabled = false;
       showToast('An error occurred while suspending the enrollment.', 'error');
   });
}

// Reminder Modal Functions
function openReminderModal(enrollmentId, studentName, courseName) {
   currentEnrollmentId = enrollmentId;
   currentStudentName = studentName;
   currentCourseName = courseName;
   
   document.getElementById('reminderStudentName').textContent = studentName;
   document.getElementById('reminderCourseName').textContent = courseName;
   
   // Reset form
   document.getElementById('reminderTemplate').value = '';
   document.getElementById('reminderSubject').value = '';
   document.getElementById('reminderMessage').value = '';
   document.getElementById('includeProgress').checked = true;
   document.getElementById('includeCourseLink').checked = true;
   
   const modal = new bootstrap.Modal(document.getElementById('reminderModal'));
   modal.show();
}

function updateReminderContent() {
   const templateSelect = document.getElementById('reminderTemplate');
   const subjectInput = document.getElementById('reminderSubject');
   const messageTextarea = document.getElementById('reminderMessage');
   
   const selectedTemplate = templateSelect.value;
   
   if (selectedTemplate && selectedTemplate !== 'custom' && reminderTemplates[selectedTemplate]) {
       const template = reminderTemplates[selectedTemplate];
       
       // Replace placeholders in subject and message
       let subject = template.subject
           .replace('{student_name}', currentStudentName)
           .replace('{course_name}', currentCourseName)
           .replace('{department_name}', '<?php echo htmlspecialchars($department['department_name']); ?>');
           
       let message = template.message
           .replace('{student_name}', currentStudentName)
           .replace('{course_name}', currentCourseName)
           .replace('{department_name}', '<?php echo htmlspecialchars($department['department_name']); ?>');
       
       subjectInput.value = subject;
       messageTextarea.value = message;
   } else if (selectedTemplate === 'custom') {
       subjectInput.value = `Continue Your Learning Journey - ${currentCourseName}`;
       messageTextarea.value = '';
       messageTextarea.focus();
   }
}

function processSendReminder() {
   const subject = document.getElementById('reminderSubject').value.trim();
   const message = document.getElementById('reminderMessage').value.trim();
   const includeProgress = document.getElementById('includeProgress').checked;
   const includeCourseLink = document.getElementById('includeCourseLink').checked;
   
   if (!subject) {
       showToast('Please enter an email subject.', 'error');
       return;
   }
   
   if (!message) {
       showToast('Please enter a message.', 'error');
       return;
   }
   
   // Show loading state
   const sendBtn = document.getElementById('sendReminderBtn');
   const originalText = sendBtn.innerHTML;
   sendBtn.innerHTML = '<div class="spinner-border spinner-border-sm me-2" role="status"></div>Sending...';
   sendBtn.disabled = true;
   
   // Make API call to send reminder
   fetch('../backend/department/send_reminder.php', {
       method: 'POST',
       headers: {
           'Content-Type': 'application/json',
       },
       body: JSON.stringify({
           enrollment_id: currentEnrollmentId,
           subject: subject,
           message: message,
           include_progress: includeProgress,
           include_course_link: includeCourseLink
       })
   })
   .then(response => response.json())
   .then(data => {
       // Reset button state
       sendBtn.innerHTML = originalText;
       sendBtn.disabled = false;
       
       if (data.success) {
           // Close modal
           const modal = bootstrap.Modal.getInstance(document.getElementById('reminderModal'));
           modal.hide();
           
           showToast(`Reminder email sent to ${currentStudentName} successfully.`, 'success');
       } else {
           showToast(data.message || 'Failed to send reminder email.', 'error');
       }
   })
   .catch(error => {
       console.error('Error:', error);
       sendBtn.innerHTML = originalText;
       sendBtn.disabled = false;
       showToast('An error occurred while sending the reminder.', 'error');
   });
}

function exportEnrollments() {
   showToast('Preparing enrollment data for export...', 'info');
   
   // Make API call for export
   window.open('../backend/department/export_enrollments.php', '_blank');
   
   setTimeout(() => {
       showToast('Export completed! Download should begin shortly.', 'success');
   }, 2000);
}

function refreshData() {
   showToast('Refreshing enrollment data...', 'info');
   setTimeout(() => {
       window.location.reload();
   }, 1000);
}

function clearFilters() {
   const progressFilter = document.getElementById('progressFilter');
   const searchInput = document.getElementById('enrollmentSearch');
   
   if (progressFilter) progressFilter.value = 'all';
   if (searchInput) searchInput.value = '';
   
   // Show all rows
   const enrollmentRows = document.querySelectorAll('#enrollmentsTable tbody tr');
   enrollmentRows.forEach(row => {
       row.style.display = '';
   });
   
   // Hide empty state and show table
   const tableContainer = document.querySelector('#enrollmentsTable');
   const emptyState = document.getElementById('emptyState');
   
   if (tableContainer) tableContainer.style.display = '';
   if (emptyState) emptyState.classList.add('d-none');
   
   showToast('Filters cleared successfully', 'info');
}

// Toast notification function
function showToast(message, type = 'info') {
   const toastElement = document.getElementById(`${type}Toast`);
   const toastBody = document.getElementById(`${type}ToastBody`);
   
   if (toastElement && toastBody) {
       toastBody.textContent = message;
       
       const toast = new bootstrap.Toast(toastElement, {
           autohide: true,
           delay: 4000
       });
       
       toast.show();
   }
}
</script>

<style>
.avatar-sm {
   width: 2.5rem;
   height: 2.5rem;
}

.avatar-img {
   width: 100%;
   height: 100%;
   object-fit: cover;
   border-radius: 50%;
}

.bg-soft-primary {
   background-color: rgba(55, 125, 255, 0.1) !important;
}

.bg-soft-success {
   background-color: rgba(0, 201, 167, 0.1) !important;
}

.bg-soft-warning {
   background-color: rgba(255, 193, 7, 0.1) !important;
}

.bg-soft-info {
   background-color: rgba(54, 162, 235, 0.1) !important;
}

.bg-soft-secondary {
   background-color: rgba(108, 117, 125, 0.1) !important;
}

.bg-soft-danger {
   background-color: rgba(220, 53, 69, 0.1) !important;
}

.icon {
   display: inline-flex;
   align-items: center;
   justify-content: center;
   border-radius: 50%;
}

.icon-md {
   width: 3rem;
   height: 3rem;
   font-size: 1.25rem;
}

.icon-soft-primary {
   background-color: rgba(55, 125, 255, 0.1);
   color: #377dff;
}

.icon-soft-success {
   background-color: rgba(0, 201, 167, 0.1);
   color: #00c9a7;
}

.icon-soft-warning {
   background-color: rgba(255, 193, 7, 0.1);
   color: #ffc107;
}

.icon-soft-secondary {
   background-color: rgba(108, 117, 125, 0.1);
   color: #6c757d;
}

.icon-soft-danger {
   background-color: rgba(220, 53, 69, 0.1);
   color: #dc3545;
}

.btn-circle {
   width: 32px;
   height: 32px;
   border-radius: 50%;
   padding: 0;
   display: inline-flex;
   align-items: center;
   justify-content: center;
   font-size: 12px;
   transition: all 0.2s ease-in-out;
}

.btn-sm.btn-circle {
   width: 28px;
   height: 28px;
   font-size: 11px;
}

.btn-circle:hover {
   transform: translateY(-1px);
   box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.btn-soft-primary {
   background-color: rgba(55, 125, 255, 0.1);
   border-color: rgba(55, 125, 255, 0.2);
   color: #377dff;
}

.btn-soft-primary:hover {
   background-color: #377dff;
   border-color: #377dff;
   color: white;
}

.btn-soft-secondary {
   background-color: rgba(108, 117, 125, 0.1);
   border-color: rgba(108, 117, 125, 0.2);
   color: #6c757d;
}

.btn-soft-secondary:hover {
   background-color: #6c757d;
   border-color: #6c757d;
   color: white;
}

.dropdown-toggle::after {
   display: none;
}

.dropdown-menu {
   border: none;
   box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
   border-radius: 0.5rem;
   min-width: 150px;
}

.dropdown-item {
   padding: 0.5rem 1rem;
   font-size: 0.875rem;
   transition: all 0.2s ease-in-out;
}

.dropdown-item:hover {
   background-color: #f8f9fa;
   transform: translateX(2px);
}

.dropdown-item-icon {
   width: 1rem;
   margin-right: 0.5rem;
}

.table th {
   font-weight: 600;
   color: #677788;
   font-size: 0.75rem;
   text-transform: uppercase;
   letter-spacing: 0.5px;
}

.card-header-title {
   font-size: 1.125rem;
   font-weight: 600;
}

.page-header-title {
   font-size: 1.75rem;
   font-weight: 600;
   color: #1e2022;
}

.page-header-text {
   color: #677788;
   margin-bottom: 0;
}

.text-cap {
   text-transform: uppercase;
   font-size: 0.75rem;
   font-weight: 600;
   color: #677788;
   letter-spacing: 0.5px;
   margin-bottom: 0.5rem;
}

.progress {
   height: 8px;
   border-radius: 4px;
   background-color: #e9ecef;
}

.progress-bar {
   border-radius: 4px;
}

.empty-state {
   background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
   border-radius: 12px;
   margin: 2rem 0;
}

.empty-state h4 {
   font-weight: 600;
   color: #495057;
}

.empty-state p {
   font-size: 1rem;
   line-height: 1.6;
   max-width: 400px;
   margin: 0 auto;
}

.toast-container {
   max-width: 350px;
}

.toast {
   border: none;
   box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.toast-header {
   border-bottom: 1px solid rgba(0, 0, 0, 0.1);
   font-weight: 600;
}

.modern-pagination {
   border-radius: 10px;
   overflow: hidden;
   box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.modern-pagination .page-item {
   margin: 0;
   border: none;
}

.modern-pagination .page-link {
   border: none;
   background: transparent;
   color: #6c757d;
   padding: 0.5rem 0.75rem;
   font-weight: 500;
   transition: all 0.2s ease-in-out;
}

.modern-pagination .page-item:first-child .page-link {
   border-top-left-radius: 10px;
   border-bottom-left-radius: 10px;
   background: #f8f9fa;
}

.modern-pagination .page-item:last-child .page-link {
   border-top-right-radius: 10px;
   border-bottom-right-radius: 10px;
   background: #f8f9fa;
}

.modern-pagination .page-item.active .page-link {
   background: linear-gradient(135deg, #377dff 0%, #5a8dee 100%);
   color: white;
   box-shadow: 0 4px 12px rgba(55, 125, 255, 0.3);
   transform: translateY(-1px);
}

.modern-pagination .page-link:hover:not(.active) {
   background: #e9ecef;
   color: #377dff;
   transform: translateY(-1px);
   box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

/* Modal Specific Styles */
.modal-content {
   border: none;
   border-radius: 1rem;
   box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
}

.modal-header {
   border-bottom: 1px solid #e9ecef;
   background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
   border-radius: 1rem 1rem 0 0;
}

.modal-title {
   font-weight: 600;
   color: #1e2022;
}

.alert {
   border: none;
   border-radius: 0.75rem;
}

.form-label {
   font-weight: 500;
   color: #495057;
   margin-bottom: 0.5rem;
}

.form-control:focus {
   border-color: #377dff;
   box-shadow: 0 0 0 0.2rem rgba(55, 125, 255, 0.25);
}

.form-select:focus {
   border-color: #377dff;
   box-shadow: 0 0 0 0.2rem rgba(55, 125, 255, 0.25);
}

.list-unstyled li {
   padding: 0.25rem 0;
}

.spinner-border-sm {
   width: 1rem;
   height: 1rem;
   border-width: 0.125em;
}

/* Responsive Design */
@media (max-width: 768px) {
   .d-flex.gap-2.align-items-center {
       flex-direction: column;
       align-items: stretch !important;
       gap: 0.5rem !important;
   }

   .btn-circle {
       width: 28px;
       height: 28px;
       font-size: 11px;
   }

   .icon-md {
       width: 2.5rem;
       height: 2.5rem;
       font-size: 1rem;
   }

   .modal-dialog {
       margin: 0.5rem;
   }

   .modal-lg {
       max-width: calc(100% - 1rem);
   }

   .modal-xl {
       max-width: calc(100% - 1rem);
   }
}

@media (max-width: 576px) {
   .input-group[style*="width: 400px"] {
       width: 100% !important;
   }

   .ms-3 {
       margin-left: 0 !important;
       margin-top: 0.5rem !important;
   }

   .page-header-title {
       font-size: 1.5rem;
   }

   .card-header {
       padding: 1rem;
   }

   .card-body {
       padding: 1rem;
   }
}
</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php include '../includes/department/footer.php'; ?>