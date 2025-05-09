<?php include '../includes/department/header.php'; ?>
<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
    <div class="navbar-sidebar-aside-content content-space-t-3 content-space-b-2 px-lg-2 px-xl-3">
        <?php
        include '../backend/config.php';
        
        // Get current department ID from session
        $department_id = $_SESSION['department_id'] ?? 0;
        
        if (!$department_id) {
            echo '<div class="alert alert-warning">Department information not found. Please contact system administrator.</div>';
            exit;
        }
        
        // Get department info
        $dept_query = "SELECT name, code FROM departments WHERE department_id = ?";
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
        
        // Department statistics
        $query = "SELECT 
            (SELECT COUNT(*) FROM department_staff 
                WHERE department_id = ? AND role = 'secretary' AND deleted_at IS NULL) AS secretary_count,
                
            (SELECT COUNT(DISTINCT i.instructor_id) FROM instructors i
                JOIN course_instructors ci ON i.instructor_id = ci.instructor_id
                JOIN courses c ON ci.course_id = c.course_id
                WHERE c.department_id = ? AND ci.deleted_at IS NULL) AS instructor_count,
                
            (SELECT COUNT(*) FROM courses WHERE department_id = ? AND deleted_at IS NULL) AS course_count,
            
            (SELECT COUNT(*) FROM courses WHERE department_id = ? AND status = 'Published' 
                AND approval_status = 'approved' AND deleted_at IS NULL) AS approved_course_count,
                
            (SELECT COUNT(*) FROM courses WHERE department_id = ? 
                AND (approval_status = 'pending' OR approval_status = 'submitted_for_review') 
                AND deleted_at IS NULL) AS pending_course_count,
                
            (SELECT COUNT(*) FROM courses WHERE department_id = ? AND approval_status = 'revisions_requested' 
                AND deleted_at IS NULL) AS revision_course_count,
                
            (SELECT COUNT(*) FROM enrollments e
                JOIN courses c ON e.course_id = c.course_id
                WHERE c.department_id = ? AND e.deleted_at IS NULL) AS enrollment_count";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiiiiii", $department_id, $department_id, $department_id, 
                          $department_id, $department_id, $department_id, $department_id);
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
            $enrollment_count = $data['enrollment_count'];
        } else {
            die("Error executing department query: " . mysqli_error($conn));
        }
        $stmt->close();
        
        // Get recent course submissions
        $recent_submissions_query = "SELECT c.title, c.course_id, c.approval_status, u.first_name, u.last_name, 
                                   c.updated_at
                            FROM courses c
                            JOIN course_instructors ci ON c.course_id = ci.course_id
                            JOIN instructors i ON ci.instructor_id = i.instructor_id
                            JOIN users u ON i.user_id = u.user_id
                            WHERE c.department_id = ? 
                            AND c.approval_status IN ('pending', 'submitted_for_review')
                            AND c.deleted_at IS NULL
                            ORDER BY c.updated_at DESC
                            LIMIT 5";
        
        $stmt = $conn->prepare($recent_submissions_query);
        $stmt->bind_param("i", $department_id);
        $stmt->execute();
        $recent_submissions_result = $stmt->get_result();
        $stmt->close();
        
        // Get recent course approvals
        $recent_approvals_query = "SELECT c.title, c.course_id, u.first_name, u.last_name, crr.updated_at
                            FROM courses c
                            JOIN course_review_requests crr ON c.course_id = crr.course_id
                            JOIN users u ON crr.reviewer_id = u.user_id
                            WHERE c.department_id = ?
                            AND crr.status = 'Approved'
                            AND c.deleted_at IS NULL
                            ORDER BY crr.updated_at DESC
                            LIMIT 5";
        
        $stmt = $conn->prepare($recent_approvals_query);
        $stmt->bind_param("i", $department_id);
        $stmt->execute();
        $recent_approvals_result = $stmt->get_result();
        $stmt->close();
        ?>

        <!-- Department Header -->
        <div class="bg-soft-primary rounded-3 p-4 mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="h3 text-primary mb-0">
                        <?php echo htmlspecialchars($department['name']); ?> Dashboard
                    </h1>
                    <p class="text-primary-dark mb-0">
                        Department Code: <?php echo htmlspecialchars($department['code']); ?>
                    </p>
                </div>
                <div class="col-auto">
                    <a href="department-settings.php" class="btn btn-sm btn-primary">
                        <i class="bi bi-gear me-1"></i>Settings
                    </a>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0">
                                <span class="avatar avatar-xs avatar-soft-primary avatar-circle">
                                    <i class="bi bi-people"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="card-subtitle text-muted">Instructors</h6>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <h3 class="mb-0 me-2"><?php echo number_format($instructor_count); ?></h3>
                            <a href="department-instructors.php" class="btn btn-xs btn-soft-primary">Manage</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0">
                                <span class="avatar avatar-xs avatar-soft-primary avatar-circle">
                                    <i class="bi bi-book"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="card-subtitle text-muted">Courses</h6>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <h3 class="mb-0 me-2"><?php echo number_format($course_count); ?></h3>
                            <a href="department-courses.php" class="btn btn-xs btn-soft-primary">View</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0">
                                <span class="avatar avatar-xs avatar-soft-primary avatar-circle">
                                    <i class="bi bi-mortarboard"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="card-subtitle text-muted">Enrollments</h6>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <h3 class="mb-0 me-2"><?php echo number_format($enrollment_count); ?></h3>
                            <a href="department-enrollments.php" class="btn btn-xs btn-soft-primary">Details</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0">
                                <span class="avatar avatar-xs avatar-soft-warning avatar-circle">
                                    <i class="bi bi-clock-history"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="card-subtitle text-muted">Pending Reviews</h6>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <h3 class="mb-0 me-2"><?php echo number_format($pending_course_count); ?></h3>
                            <a href="pending-reviews.php" class="btn btn-xs btn-soft-warning">Review</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Status Overview Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-header-title mb-0">Course Status Overview</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center p-3">
                            <span class="d-block display-6 text-primary mb-2"><?php echo number_format($approved_course_count); ?></span>
                            <span class="d-block text-muted">Approved Courses</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3">
                            <span class="d-block display-6 text-warning mb-2"><?php echo number_format($pending_course_count); ?></span>
                            <span class="d-block text-muted">Pending Review</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3">
                            <span class="d-block display-6 text-info mb-2"><?php echo number_format($revision_course_count); ?></span>
                            <span class="d-block text-muted">Needs Revision</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="card-header-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body pb-3">
                <div class="row">
                    <div class="col-6 col-md-3 mb-3">
                        <a href="invite-instructor.php" class="card card-sm border-0 bg-soft-primary text-primary text-center h-100">
                            <div class="card-body p-3">
                                <i class="bi bi-person-plus fs-3 mb-2"></i>
                                <span class="d-block small">Invite Instructor</span>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <a href="create-course.php" class="card card-sm border-0 bg-soft-success text-success text-center h-100">
                            <div class="card-body p-3">
                                <i class="bi bi-plus-circle fs-3 mb-2"></i>
                                <span class="d-block small">Create Course</span>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <a href="send-announcement.php" class="card card-sm border-0 bg-soft-info text-info text-center h-100">
                            <div class="card-body p-3">
                                <i class="bi bi-megaphone fs-3 mb-2"></i>
                                <span class="d-block small">Announcement</span>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <a href="generate-report.php" class="card card-sm border-0 bg-soft-dark text-dark text-center h-100">
                            <div class="card-body p-3">
                                <i class="bi bi-file-earmark-bar-graph fs-3 mb-2"></i>
                                <span class="d-block small">Generate Report</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php
        // Get department-specific activities (excluding system admin actions)
        $activity_query = "SELECT 
            'course_update' as activity_type,
            c.title as activity_subject,
            c.updated_at as performed_at,
            c.approval_status,
            CASE 
                WHEN c.approval_status = 'approved' THEN 'Course approved'
                WHEN c.approval_status = 'pending' THEN 'Course submitted for review'
                WHEN c.approval_status = 'revisions_requested' THEN 'Revisions requested'
                WHEN c.approval_status = 'rejected' THEN 'Course rejected'
                ELSE 'Course status updated'
            END as action_description
            FROM courses c
            WHERE c.department_id = ?
            AND c.deleted_at IS NULL
            ORDER BY c.updated_at DESC
            LIMIT 10";
            
        $stmt = $conn->prepare($activity_query);
        $stmt->bind_param("i", $department_id);
        $stmt->execute();
        $activity_result = $stmt->get_result();
        $stmt->close();
        
        // Combine both course result sets
        $all_courses = array();
        if ($recent_submissions_result && $recent_submissions_result->num_rows > 0) {
            $recent_submissions_result->data_seek(0); // Reset pointer
            while($row = $recent_submissions_result->fetch_assoc()) {
                $row['status'] = 'pending';
                $row['action_label'] = 'Review';
                $row['action_url'] = 'review-course.php?id=' . $row['course_id'];
                $all_courses[] = $row;
            }
        }
        
        if ($recent_approvals_result && $recent_approvals_result->num_rows > 0) {
            $recent_approvals_result->data_seek(0); // Reset pointer
            while($row = $recent_approvals_result->fetch_assoc()) {
                $row['status'] = 'approved';
                $row['action_label'] = 'View';
                $row['action_url'] = 'view-course.php?id=' . $row['course_id'];
                $all_courses[] = $row;
            }
        }
        
        // Sort by most recent updated_at
        usort($all_courses, function($a, $b) {
            return strtotime($b['updated_at']) - strtotime($a['updated_at']);
        });
        
        // Limit to 8 courses
        $all_courses = array_slice($all_courses, 0, 8);
        ?>
        
        <!-- Course Overview & Recent Activities -->
        <div class="row">
            <!-- Course Stats and List -->
            <div class="col-lg-8 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-header-title mb-0">Course Management</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <!-- Chart Legend -->
                            <div class="col-lg-6 mb-3">
                                <div class="d-flex mb-2">
                                    <div style="width: 15px; height: 15px; background-color: #4285F4;" class="me-2"></div>
                                    <span class="me-3">Approved: <?php echo $approved_course_count; ?></span>
                                </div>
                                <div class="d-flex mb-2">
                                    <div style="width: 15px; height: 15px; background-color: #FBBC05;" class="me-2"></div>
                                    <span class="me-3">Pending: <?php echo $pending_course_count; ?></span>
                                </div>
                                <div class="d-flex mb-2">
                                    <div style="width: 15px; height: 15px; background-color: #34A853;" class="me-2"></div>
                                    <span class="me-3">Revision: <?php echo $revision_course_count; ?></span>
                                </div>
                                <div class="d-flex mb-2">
                                    <div style="width: 15px; height: 15px; background-color: #9AA0A6;" class="me-2"></div>
                                    <span class="me-3">Other: <?php echo $course_count - ($approved_course_count + $pending_course_count + $revision_course_count); ?></span>
                                </div>
                            </div>
                            
                            <!-- Course Stats -->
                            <div class="col-lg-6 mb-3">
                                <div class="row">
                                    <div class="col-6 mb-2">
                                        <div class="text-end">
                                            <div class="fw-bold">Approved:</div>
                                            <div class="h4"><?php echo $approved_course_count; ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <div class="text-end">
                                            <div class="fw-bold">Pending:</div>
                                            <div class="h4"><?php echo $pending_course_count; ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <div class="text-end">
                                            <div class="fw-bold">Revision:</div>
                                            <div class="h4"><?php echo $revision_course_count; ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <div class="text-end">
                                            <div class="fw-bold">Other:</div>
                                            <div class="h4"><?php echo $course_count - ($approved_course_count + $pending_course_count + $revision_course_count); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Course List -->
                        <div style="max-height: 400px; overflow-y: auto;">
                            <table class="table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Course</th>
                                        <th>Contributors</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($all_courses) > 0): ?>
                                        <?php foreach($all_courses as $course): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($course['title']); ?></h6>
                                                        <small class="text-muted">Updated <?php echo date('M d', strtotime($course['updated_at'])); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <!-- We'd normally fetch all contributors, but for now, show the instructor -->
                                                <div class="avatar-group">
                                                    <span class="avatar avatar-circle avatar-xs" data-bs-toggle="tooltip" 
                                                          title="<?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?>">
                                                        <img class="avatar-img" src="../uploads/default.png" alt="Avatar">
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($course['status'] == 'pending'): ?>
                                                    <span class="badge bg-soft-warning text-warning">Pending Review</span>
                                                <?php elseif ($course['status'] == 'approved'): ?>
                                                    <span class="badge bg-soft-success text-success">Approved</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <a href="<?php echo $course['action_url']; ?>" class="btn btn-xs btn-soft-primary"><?php echo $course['action_label']; ?></a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4">No courses found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top-0 text-end">
                        <a href="department-courses.php" class="btn btn-sm btn-primary">View All Courses</a>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activities -->
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-header-title mb-0">Recent Activities</h5>
                    </div>
                    <div class="card-body p-3" style="max-height: 550px; overflow-y: auto;">
                        <?php if ($activity_result && $activity_result->num_rows > 0): ?>
                            <?php while($activity = $activity_result->fetch_assoc()): ?>
                            <div class="mb-4 pb-3 border-bottom">
                                <div class="mb-1">
                                    <div class="fw-bold"><?php echo htmlspecialchars($activity['action_description']); ?></div>
                                    <div class="text-body"><?php echo htmlspecialchars($activity['activity_subject']); ?></div>
                                </div>
                                <div class="text-muted">
                                    <?php echo date('M d, Y', strtotime($activity['performed_at'])); ?>
                                    <span class="float-end"><?php echo date('g:i A', strtotime($activity['performed_at'])); ?></span>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <p class="text-muted">No recent department activities</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- End Content -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- Show loading overlay function -->
<script>
function showOverlay(message = null) {
    // Remove any existing overlay
    const existingOverlay = document.querySelector('.custom-overlay');
    if (existingOverlay) {
        existingOverlay.remove();
    }

    // Create new overlay
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
</script>

<?php include '../includes/department/footer.php'; ?>