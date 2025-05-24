<?php 
include '../includes/department/header.php';

// Check if user is logged in and has proper role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    header('Location: ../auth/login.php');
    exit();
}

// Get department information and courses
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
    
    // Get course statistics
    $stats_query = "SELECT 
                        COUNT(*) as total_courses,
                        SUM(CASE WHEN status = 'Published' THEN 1 ELSE 0 END) as published_courses,
                        SUM(CASE WHEN approval_status IN ('pending', 'submitted_for_review', 'under_review') THEN 1 ELSE 0 END) as pending_courses,
                        SUM(CASE WHEN creation_step < 4 THEN 1 ELSE 0 END) as draft_courses,
                        SUM(CASE WHEN financial_approval_date IS NOT NULL THEN 1 ELSE 0 END) as financially_approved
                    FROM courses 
                    WHERE department_id = ? AND deleted_at IS NULL";
    
    $stats_stmt = $conn->prepare($stats_query);
    $stats_stmt->bind_param("i", $department_id);
    $stats_stmt->execute();
    $stats_result = $stats_stmt->get_result();
    $stats = $stats_result->fetch_assoc();
    
    // Get courses with instructor information
    $courses_query = "SELECT 
                        c.course_id,
                        c.title,
                        c.short_description,
                        c.status,
                        c.approval_status,
                        c.financial_approval_date,
                        c.price,
                        c.created_at,
                        c.updated_at,
                        c.course_level,
                        c.creation_step,
                        c.thumbnail,
                        sub.name as subcategory_name,
                        cat.name as category_name,
                        GROUP_CONCAT(
                            CONCAT(u.first_name, ' ', u.last_name) 
                            ORDER BY ci.is_primary DESC, u.first_name ASC 
                            SEPARATOR ', '
                        ) as instructor_names,
                        GROUP_CONCAT(
                            u.profile_pic 
                            ORDER BY ci.is_primary DESC, u.first_name ASC 
                            SEPARATOR ', '
                        ) as instructor_pics,
                        COUNT(DISTINCT ci.instructor_id) as instructor_count
                      FROM courses c
                      LEFT JOIN subcategories sub ON c.subcategory_id = sub.subcategory_id
                      LEFT JOIN categories cat ON sub.category_id = cat.category_id
                      LEFT JOIN course_instructors ci ON c.course_id = ci.course_id AND ci.deleted_at IS NULL
                      LEFT JOIN instructors i ON ci.instructor_id = i.instructor_id AND i.deleted_at IS NULL
                      LEFT JOIN users u ON i.user_id = u.user_id AND u.deleted_at IS NULL
                      WHERE c.department_id = ? AND c.deleted_at IS NULL
                      GROUP BY c.course_id
                      ORDER BY c.created_at DESC";
    
    $courses_stmt = $conn->prepare($courses_query);
    $courses_stmt->bind_param("i", $department_id);
    $courses_stmt->execute();
    $courses_result = $courses_stmt->get_result();
    $courses = $courses_result->fetch_all(MYSQLI_ASSOC);
    
} catch (Exception $e) {
    error_log("Error fetching department courses: " . $e->getMessage());
    $courses = [];
    $stats = ['total_courses' => 0, 'published_courses' => 0, 'pending_courses' => 0, 'draft_courses' => 0, 'financially_approved' => 0];
}

// Function to determine if course can be managed
function canManageCourse($course) {
    return !empty($course['financial_approval_date']);
}

// Function to get course status badge
function getStatusBadge($status, $approval_status, $creation_step) {
    if ($creation_step < 4) {
        return '<span class="badge bg-soft-secondary text-secondary"><i class="bi-pencil me-1"></i>Draft</span>';
    }
    
    switch ($status) {
        case 'Published':
            return '<span class="badge bg-soft-success text-success"><i class="bi-check-circle me-1"></i>Published</span>';
        default:
            if ($approval_status === 'pending') {
                return '<span class="badge bg-soft-warning text-warning"><i class="bi-clock me-1"></i>Submitted for Review</span>';
            } else {
                return '<span class="badge bg-soft-warning text-warning"><i class="bi-clock me-1"></i>' . ucfirst(str_replace('_', ' ', $approval_status)) . '</span>';
            }
    }
}

// Function to get financial status badge
function getFinancialStatusBadge($financial_approval_date) {
    if (!empty($financial_approval_date)) {
        return '<span class="badge bg-soft-success text-success"><i class="bi-currency-dollar me-1"></i>Approved</span>';
    } else {
        return '<span class="badge bg-soft-warning text-warning"><i class="bi-hourglass-split me-1"></i>Pending</span>';
    }
}

// Function to get course thumbnail
function getCourseThumbnail($thumbnail) {
    if (!empty($thumbnail) && file_exists("../uploads/thumbnails/" . $thumbnail)) {
        return "../uploads/thumbnails/" . $thumbnail;
    }
    return "../uploads/thumbnails/default.jpg";
}

// Function to get instructor profile picture
function getInstructorProfilePic($profile_pic) {
    if (!empty($profile_pic) && file_exists("../uploads/instructor-profile/" . $profile_pic)) {
        return "../uploads/instructor-profile/" . $profile_pic;
    }
    return "../uploads/instructor-profile/default.png";
}
?>

<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-sidebar navbar-vertical navbar-light bg-white border-end">
        <?php include '../includes/department/sidebar.php'; ?>
    </nav>
    <!-- End Navbar -->

    <!-- Content -->
    <div class="navbar-sidebar-aside-content content-space-1 content-space-md-2 px-lg-5 px-xl-5">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center mb-3">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">Course Management</h1>
                    <p class="page-header-text">Manage and oversee courses for <?php echo htmlspecialchars($department['department_name']); ?></p>
                </div>
                <div class="col-sm-auto">
                    <a href="initiate-course.php" class="btn btn-primary">
                        <i class="bi-plus me-1"></i> Create New Course
                    </a>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="card-subtitle mb-2">Total Courses</h6>
                                <span class="h3 text-dark"><?php echo $stats['total_courses']; ?></span>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-md icon-soft-primary">
                                    <i class="bi-collection"></i>
                                </div>
                            </div>
                        </div>
                        <span class="badge bg-soft-info text-info mt-2">
                            <i class="bi-info-circle"></i> Total
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="card-subtitle mb-2">Published Courses</h6>
                                <span class="h3 text-dark"><?php echo $stats['published_courses']; ?></span>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-md icon-soft-success">
                                    <i class="bi-check-circle"></i>
                                </div>
                            </div>
                        </div>
                        <span class="badge bg-soft-success text-success mt-2">
                            <i class="bi-check-circle"></i> Active
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="card-subtitle mb-2">Pending Approval</h6>
                                <span class="h3 text-dark"><?php echo $stats['pending_courses']; ?></span>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-md icon-soft-warning">
                                    <i class="bi-clock"></i>
                                </div>
                            </div>
                        </div>
                        <span class="badge bg-soft-warning text-warning mt-2">
                            <i class="bi-clock"></i> Waiting
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="card-subtitle mb-2">Draft Courses</h6>
                                <span class="h3 text-dark"><?php echo $stats['draft_courses']; ?></span>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-md icon-soft-secondary">
                                    <i class="bi-pencil"></i>
                                </div>
                            </div>
                        </div>
                        <span class="badge bg-soft-secondary text-secondary mt-2">
                            <i class="bi-pencil"></i> Draft
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Summary Cards -->

        <!-- Courses Table -->
        <div class="card">
            <div class="card-header card-header-content-md-between">
                <div class="mb-2 mb-md-0">
                    <h4 class="card-header-title">Department Courses</h4>
                </div>
                
                <!-- Search and Filter -->
                <div class="d-flex gap-2 align-items-center">
                    <div class="input-group input-group-sm" style="width: 500px;">
                        <div class="input-group-text">
                            <i class="bi-search"></i>
                        </div>
                        <input type="search" class="form-control" placeholder="Search courses..." id="courseSearch">
                    </div>
                    
                    <div class="ms-3">
                        <select class="form-select form-select-sm" id="statusFilter" style="width: 200px;">
                            <option value="all">All Courses</option>
                            <option value="published">Published</option>
                            <option value="pending">Pending Approval</option>
                            <option value="draft">Draft</option>
                            <option value="financially_approved">Financially Approved</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-responsive datatable-custom">
                <?php if (count($courses) > 0): ?>
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table" id="coursesTable">
                    <thead class="thead-light">
                        <tr>
                            <th>Course</th>
                            <th>Category</th>
                            <th>Instructor(s)</th>
                            <th>Status</th>
                            <th>Financial Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): 
                            $status_class = ($course['creation_step'] < 4) ? 'draft' : strtolower($course['status']);
                            $financial_status = !empty($course['financial_approval_date']) ? 'approved' : 'pending';
                            $thumbnail_path = getCourseThumbnail($course['thumbnail']);
                        ?>
                        <tr data-status="<?php echo $status_class; ?>" data-financial="<?php echo $financial_status; ?>">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <img class="avatar avatar-lg" src="<?php echo $thumbnail_path; ?>" alt="Course thumbnail" style="object-fit: cover;">
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="text-inherit mb-0 text-truncate-title" title="<?php echo htmlspecialchars($course['title']); ?>">
                                            <?php echo htmlspecialchars($course['title']); ?>
                                        </h5>
                                        <p class="fs-6 text-body mb-0 text-truncate-desc" title="<?php echo htmlspecialchars($course['short_description'] ?? ''); ?>">
                                            <?php echo htmlspecialchars($course['short_description'] ?? 'No description available'); ?>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-soft-primary text-primary">
                                    <?php echo htmlspecialchars($course['category_name'] ?? 'Uncategorized'); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($course['instructor_count'] > 0): ?>
                                    <div class="avatar-group avatar-group-xs">
                                        <?php 
                                        $instructors = explode(', ', $course['instructor_names']);
                                        $instructor_pics = explode(', ', $course['instructor_pics']);
                                        $max_display = 3;
                                        for ($i = 0; $i < min(count($instructors), $max_display); $i++): 
                                            $profile_pic_path = getInstructorProfilePic($instructor_pics[$i] ?? '');
                                        ?>
                                            <span class="avatar avatar-xs avatar-circle" 
                                                  data-bs-toggle="tooltip" 
                                                  data-bs-placement="top" 
                                                  title="<?php echo htmlspecialchars($instructors[$i]); ?>">
                                                <img class="avatar-img" src="<?php echo $profile_pic_path; ?>" alt="<?php echo htmlspecialchars($instructors[$i]); ?>" style="object-fit: cover;">
                                            </span>
                                        <?php endfor; ?>
                                        <?php if (count($instructors) > $max_display): ?>
                                            <span class="avatar avatar-xs avatar-circle avatar-soft-secondary" 
                                                  data-bs-toggle="tooltip" 
                                                  data-bs-placement="top" 
                                                  title="<?php echo (count($instructors) - $max_display); ?> more instructors">
                                                <span class="avatar-initials">+<?php echo (count($instructors) - $max_display); ?></span>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">Not Assigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo getStatusBadge($course['status'], $course['approval_status'], $course['creation_step']); ?>
                            </td>
                            <td>
                                <?php echo getFinancialStatusBadge($course['financial_approval_date']); ?>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-circle btn-white" 
                                            onclick="viewCourse(<?php echo $course['course_id']; ?>)" 
                                            data-bs-toggle="tooltip" title="View Course">
                                        <i class="bi-eye"></i>
                                    </button>
                                    <?php if (canManageCourse($course)): ?>
                                        <button type="button" class="btn btn-circle btn-soft-primary" 
                                                onclick="manageCourse(<?php echo $course['course_id']; ?>)" 
                                                data-bs-toggle="tooltip" title="Manage Course">
                                            <i class="bi-gear"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>

                <!-- Empty State -->
                <div class="empty-state <?php echo count($courses) > 0 ? 'd-none' : ''; ?>" id="emptyState">
                    <div class="text-center py-5">
                        <div class="empty-state-icon mb-4">
                            <i class="bi-collection text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
                        </div>
                        <h4 class="text-muted mb-3">No courses found</h4>
                        <p class="text-muted mb-4">
                            <span id="emptyStateMessage">
                                <?php echo count($courses) === 0 ? "No courses have been created yet. Start by creating your first course." : "It looks like there are no courses matching your criteria."; ?>
                            </span>
                        </p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="initiate-course.php" class="btn btn-primary">
                                <i class="bi-plus me-1"></i> Create First Course
                            </a>
                            <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                                <i class="bi-arrow-clockwise me-1"></i> Clear Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (count($courses) > 0): ?>
            <div class="card-footer">
                <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
                    <div class="col-sm mb-2 mb-sm-0">
                        <div class="d-flex justify-content-center justify-content-sm-start align-items-center">
                            <span class="me-2">Showing:</span>
                            <select class="form-select form-select-sm" style="width: auto;">
                                <option value="12">12</option>
                                <option value="24" selected>24</option>
                                <option value="48">48</option>
                                <option value="All">All</option>
                            </select>
                            <span class="text-secondary mx-2">of</span>
                            <span id="totalCourses"><?php echo count($courses); ?></span>
                        </div>
                    </div>

                    <div class="col-sm-auto">
                        <nav aria-label="Course pagination">
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
        <!-- End Courses Table -->

        <!-- View Course Modal -->
        <div class="modal fade" id="viewCourseModal" tabindex="-1" aria-labelledby="viewCourseModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewCourseModalLabel">Course Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="courseDetailsContent">
                            <!-- Course details will be loaded here -->
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary d-none" id="viewToManageBtn">
                            <i class="bi-gear me-1"></i>Manage Course
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End View Course Modal -->

    </div>
    <!-- End Content -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- Loading Overlay -->
<div class="custom-overlay d-none" id="loadingOverlay">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <div class="text-white ms-3">Processing...</div>
</div>

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

   <!-- Warning Toast -->
   <div id="warningToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
       <div class="toast-header bg-warning text-dark">
           <i class="bi-exclamation-triangle-fill me-2"></i>
           <strong class="me-auto">Warning</strong>
           <small>just now</small>
           <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
       </div>
       <div class="toast-body" id="warningToastBody">
           Warning message here.
       </div>
   </div>
</div>

<script>
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
   const statusFilter = document.getElementById('statusFilter');
   const courseRows = document.querySelectorAll('#coursesTable tbody tr');
   const emptyState = document.getElementById('emptyState');
   const tableContainer = document.querySelector('#coursesTable');
   
   function updateTableVisibility() {
       if (!tableContainer) return; // Handle case when no courses exist initially
       
       const visibleRows = Array.from(courseRows).filter(row => row.style.display !== 'none');
       
       if (visibleRows.length === 0) {
           if (tableContainer) tableContainer.style.display = 'none';
           emptyState.classList.remove('d-none');
           
           // Update empty state message based on current filter
           const currentFilter = statusFilter.value;
           const emptyMessage = document.getElementById('emptyStateMessage');
           
           if (currentFilter === 'all') {
               emptyMessage.textContent = "No courses have been created yet. Start by creating your first course.";
           } else {
               const filterText = statusFilter.options[statusFilter.selectedIndex].text;
               emptyMessage.textContent = `No courses found with status "${filterText}". Try adjusting your filters.`;
           }
       } else {
           if (tableContainer) tableContainer.style.display = '';
           emptyState.classList.add('d-none');
       }
   }
   
   if (statusFilter && courseRows.length > 0) {
       statusFilter.addEventListener('change', function() {
           const filter = this.value;
           
           courseRows.forEach(row => {
               if (filter === 'all') {
                   row.style.display = '';
               } else {
                   const status = row.dataset.status;
                   const financialStatus = row.dataset.financial;
                   
                   let shouldShow = false;
                   switch(filter) {
                       case 'published':
                           shouldShow = status === 'published';
                           break;
                       case 'pending':
                           shouldShow = status.includes('pending') || status.includes('under_review') || status.includes('submitted');
                           break;
                       case 'draft':
                           shouldShow = status === 'draft';
                           break;
                       case 'financially_approved':
                           shouldShow = financialStatus === 'approved';
                           break;
                   }
                   
                   row.style.display = shouldShow ? '' : 'none';
               }
           });
           
           updateTableVisibility();
       });
   }

   // Search functionality
   const searchInput = document.getElementById('courseSearch');
   if (searchInput && courseRows.length > 0) {
       searchInput.addEventListener('input', function() {
           const searchTerm = this.value.toLowerCase();
           
           courseRows.forEach(row => {
               const courseName = row.querySelector('h5').textContent.toLowerCase();
               const courseDescription = row.querySelector('p').textContent.toLowerCase();
               const category = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
               
               if (courseName.includes(searchTerm) || 
                   courseDescription.includes(searchTerm) || 
                   category.includes(searchTerm)) {
                   row.style.display = '';
               } else {
                   row.style.display = 'none';
               }
           });
           
           updateTableVisibility();
       });
   }
});

// Clear filters function
function clearFilters() {
   const statusFilter = document.getElementById('statusFilter');
   const searchInput = document.getElementById('courseSearch');
   
   if (statusFilter) statusFilter.value = 'all';
   if (searchInput) searchInput.value = '';
   
   // Show all rows
   const courseRows = document.querySelectorAll('#coursesTable tbody tr');
   courseRows.forEach(row => {
       row.style.display = '';
   });
   
  // Hide empty state and show table
  const tableContainer = document.querySelector('#coursesTable');
  const emptyState = document.getElementById('emptyState');
  
  if (tableContainer) tableContainer.style.display = '';
  if (emptyState) emptyState.classList.add('d-none');
  
  showToast('Filters cleared successfully', 'info');
}

// Course action functions
function viewCourse(courseId) {
  // Show the view modal
  const viewModal = new bootstrap.Modal(document.getElementById('viewCourseModal'));
  viewModal.show();
  
  // Load course details - simplified approach
  const contentDiv = document.getElementById('courseDetailsContent');
  const manageBtn = document.getElementById('viewToManageBtn');
  
  // Show loading spinner
  contentDiv.innerHTML = `
      <div class="text-center py-4">
          <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
          </div>
          <p class="text-muted mt-2">Loading course details...</p>
      </div>
  `;
  
  // Find course data from the current page (instead of AJAX)
  const courseRow = document.querySelector(`[onclick*="viewCourse(${courseId})"]`).closest('tr');
  if (courseRow) {
      const title = courseRow.querySelector('h5').textContent;
      const description = courseRow.querySelector('p').textContent;
      const category = courseRow.querySelector('.badge').textContent;
      const statusBadge = courseRow.querySelector('td:nth-child(4)').innerHTML;
      const financialBadge = courseRow.querySelector('td:nth-child(5)').innerHTML;
      const thumbnail = courseRow.querySelector('img').src;
      
      // Update modal title
      document.getElementById('viewCourseModalLabel').textContent = title;
      
      // Check if manage button should be shown
      const financialStatus = courseRow.dataset.financial;
      if (financialStatus === 'approved') {
          manageBtn.classList.remove('d-none');
          manageBtn.setAttribute('data-course-id', courseId);
      } else {
          manageBtn.classList.add('d-none');
      }
      
      // Build course details HTML
      contentDiv.innerHTML = `
          <div class="row">
              <div class="col-md-4 mb-3">
                  <img src="${thumbnail}" alt="Course Thumbnail" class="img-fluid rounded" style="width: 100%; height: 200px; object-fit: cover;">
              </div>
              <div class="col-md-8">
                  <div class="mb-3">
                      <h6 class="text-cap">Course Title</h6>
                      <p class="text-body">${title}</p>
                  </div>
                  <div class="row">
                      <div class="col-md-6 mb-3">
                          <h6 class="text-cap">Category</h6>
                          <p class="text-body">${category}</p>
                      </div>
                      <div class="col-md-6 mb-3">
                          <h6 class="text-cap">Status</h6>
                          <div>${statusBadge}</div>
                      </div>
                  </div>
                  <div class="mb-3">
                      <h6 class="text-cap">Financial Status</h6>
                      <div>${financialBadge}</div>
                  </div>
              </div>
              <div class="col-md-12 mb-3">
                  <h6 class="text-cap">Description</h6>
                  <p class="text-body">${description}</p>
              </div>
          </div>
      `;
      
      showToast('Course details loaded successfully', 'info');
  } else {
      contentDiv.innerHTML = `
          <div class="text-center py-4">
              <i class="bi-exclamation-circle text-danger" style="font-size: 3rem;"></i>
              <h5 class="text-danger mt-2">Error Loading Course</h5>
              <p class="text-muted">Unable to load course details</p>
          </div>
      `;
  }
}

function manageCourse(courseId) {
  // Redirect to manage course page
  window.location.href = `manage-course.php?course_id=${courseId}`;
}

// Handle manage button click from view modal
document.getElementById('viewToManageBtn').addEventListener('click', function() {
  const courseId = this.getAttribute('data-course-id');
  if (courseId) {
      window.location.href = `manage-course.php?course_id=${courseId}`;
  }
});

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

// Show/Hide overlay function
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

function removeOverlay() {
  const overlay = document.querySelector('.custom-overlay');
  if (overlay) {
      overlay.remove();
  }
}
</script>

<style>
.custom-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.alert-soft-info {
  background-color: rgba(54, 162, 235, 0.1);
  border-color: rgba(54, 162, 235, 0.2);
  color: #36a2eb;
}

.bg-soft-primary {
  background-color: rgba(55, 125, 255, 0.1) !important;
}

.bg-soft-success {
  background-color: rgba(0, 201, 167, 0.1) !important;
}

.bg-soft-warning {
  background-color: rgba(255, 193, 7, 0.1) !important;
  border: 1px solid rgba(255, 193, 7, 0.2);
}

.bg-soft-info {
  background-color: rgba(54, 162, 235, 0.1) !important;
}

.bg-soft-secondary {
  background-color: rgba(108, 117, 125, 0.1) !important;
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

.avatar-group .avatar {
  border: 2px solid #fff;
  margin-left: -0.75rem;
}

.avatar-group .avatar:first-child {
  margin-left: 0;
}

.avatar-soft-secondary {
  background-color: rgba(108, 117, 125, 0.1);
  color: #6c757d;
}

/* Avatar Image Styles */
.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

.avatar-lg {
  width: 4rem;
  height: 4rem;
  border-radius: 0.5rem;
}

.avatar-xs {
  width: 1.5rem;
  height: 1.5rem;
}

/* Circular Action Buttons */
.btn-circle {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  padding: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  transition: all 0.2s ease-in-out;
}

.btn-circle:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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

.btn-soft-secondary:hover:not(:disabled) {
  background-color: #6c757d;
  border-color: #6c757d;
  color: white;
}

.btn-soft-secondary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
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

.form-text {
  font-size: 0.75rem;
  color: #677788;
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

.text-cap {
  text-transform: uppercase;
  font-size: 0.75rem;
  font-weight: 600;
  color: #677788;
  letter-spacing: 0.5px;
  margin-bottom: 0.5rem;
}

.alert-heading {
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.text-warning {
  color: #ffc107 !important;
}

/* Text truncation styles */
.text-truncate-title {
  max-width: 250px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.text-truncate-desc {
  max-width: 300px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Empty State Styles */
.empty-state {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-radius: 12px;
  margin: 2rem 0;
}

.empty-state-icon {
  position: relative;
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

/* Modern Pagination Styles */
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
  position: relative;
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

.modern-pagination .page-item:first-child .page-link:hover,
.modern-pagination .page-item:last-child .page-link:hover {
  background: #377dff;
  color: white;
}

.modern-pagination .page-link i {
  font-size: 0.875rem;
}

@media (max-width: 768px) {
  .d-flex.gap-2.align-items-center {
      flex-direction: column;
      align-items: stretch !important;
      gap: 0.5rem !important;
  }

  .icon-md {
      width: 2.5rem;
      height: 2.5rem;
      font-size: 1rem;
  }

  .text-truncate-title {
      max-width: 180px;
  }

  .text-truncate-desc {
      max-width: 200px;
  }

  .modern-pagination {
      justify-content: center;
  }

  .modern-pagination .page-link {
      padding: 0.375rem 0.5rem;
      font-size: 0.875rem;
  }

  .btn-circle {
      width: 32px;
      height: 32px;
      font-size: 12px;
  }

  .empty-state {
      margin: 1rem 0;
  }

  .empty-state p {
      font-size: 0.9rem;
  }
}

@media (max-width: 576px) {
  .text-truncate-title {
      max-width: 150px;
  }

  .text-truncate-desc {
      max-width: 170px;
  }

  .input-group[style*="width: 500px"] {
      width: 100% !important;
  }

  .ms-3 {
      margin-left: 0 !important;
      margin-top: 0.5rem !important;
  }
}
</style>

<?php include '../includes/department/footer.php'; ?>