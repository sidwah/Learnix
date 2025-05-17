<?php
// department/courses.php
include '../includes/department/header.php';
require_once '../backend/config.php';
require_once '../includes/department/courses_functions.php';
require_once '../includes/department/course_table_row.php';
require_once '../includes/department/course_modals.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    header('Location: ../admin/departments.php');
    exit;
}

// Get user's department
$dept_query = "SELECT ds.department_id 
               FROM department_staff ds 
               WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' AND ds.deleted_at IS NULL
               LIMIT 1";
$dept_stmt = $conn->prepare($dept_query);
$dept_stmt->bind_param("i", $_SESSION['user_id']);
$dept_stmt->execute();
$dept_result = $dept_stmt->get_result();

if ($dept_result->num_rows === 0) {
    header('Location: ../admin/departments.php');
    exit;
}

$department = $dept_result->fetch_assoc();
$department_id = $department['department_id'];

// Get categories for filters
$categories_query = "SELECT DISTINCT c.name 
                    FROM courses co
                    JOIN subcategories sub ON co.subcategory_id = sub.subcategory_id
                    JOIN categories c ON sub.category_id = c.category_id
                    WHERE co.department_id = ?
                    ORDER BY c.name";
$categories_stmt = $conn->prepare($categories_query);
$categories_stmt->bind_param("i", $department_id);
$categories_stmt->execute();
$categories_result = $categories_stmt->get_result();
$categories = $categories_result->fetch_all(MYSQLI_ASSOC);

// Get course levels
$levels_query = "SELECT DISTINCT course_level 
                FROM courses 
                WHERE department_id = ?
                ORDER BY course_level";
$levels_stmt = $conn->prepare($levels_query);
$levels_stmt->bind_param("i", $department_id);
$levels_stmt->execute();
$levels_result = $levels_stmt->get_result();
$levels = [];
while ($row = $levels_result->fetch_assoc()) {
    $levels[] = $row['course_level'];
}

// Get course stats for the cards
$stats = getCourseStats($department_id);
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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
        <div class="page-header mb-3">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">Department Courses</h1>
                    <p class="text-muted mb-0">Manage and monitor all department courses</p>
                </div>
                <div class="col-sm-auto">
                    <a href="initiate-course.php" class="btn btn-primary">
                        <i class="bi-plus me-1"></i> Create New Course
                    </a>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Stats Cards -->
        <div class="row mb-3">
            <div class="col-sm-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body py-3">
                        <h6 class="text-muted text-uppercase mb-1" style="font-size: 0.75rem;">Total Courses</h6>
                        <div class="d-flex align-items-center">
                            <h3 class="mb-0 total-courses-count"><?php echo $stats['total_courses'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body py-3">
                        <h6 class="text-muted text-uppercase mb-1" style="font-size: 0.75rem;">Published</h6>
                        <div class="d-flex align-items-center">
                            <h3 class="mb-0 published-courses-count"><?php echo $stats['published_courses'] ?? 0; ?></h3>
                            <span class="badge bg-soft-success text-success ms-2 published-courses-percentage">
                                <?php echo $stats['published_percentage'] ?? 0; ?>%
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body py-3">
                        <h6 class="text-muted text-uppercase mb-1" style="font-size: 0.75rem;">Draft/Pending</h6>
                        <div class="d-flex align-items-center">
                            <h3 class="mb-0 pending-courses-count"><?php echo $stats['draft_pending_courses'] ?? 0; ?></h3>
                            <span class="badge bg-soft-warning text-warning ms-2 pending-courses-percentage">
                                <?php echo $stats['pending_percentage'] ?? 0; ?>%
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body py-3">
                        <h6 class="text-muted text-uppercase mb-1" style="font-size: 0.75rem;">Under Review</h6>
                        <div class="d-flex align-items-center">
                            <h3 class="mb-0 review-courses-count"><?php echo $stats['under_review_courses'] ?? 0; ?></h3>
                            <span class="badge bg-soft-info text-info ms-2 review-courses-percentage">
                                <?php echo $stats['review_percentage'] ?? 0; ?>%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Stats Cards -->

        <!-- Filters and Search -->
        <div class="card mb-3">
            <div class="card-header border-0 py-2">
                <div class="row justify-content-between align-items-center">
                    <div class="col-auto">
                        <h5 class="card-header-title mb-0">Course Management</h5>
                    </div>
                </div>
            </div>

            <div class="card-body py-2">
                <div class="row align-items-center g-2">
                    <div class="col-md-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="bi-search"></i>
                            </span>
                            <input type="text" id="courseSearch" class="form-control" placeholder="Search courses...">
                        </div>
                    </div>

                    <div class="col-md-9">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="draft">Draft</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="published">Published</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="categoryFilter">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category['name']); ?>">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="levelFilter">
                                    <option value="">All Levels</option>
                                    <?php foreach ($levels as $level): ?>
                                        <option value="<?php echo htmlspecialchars($level); ?>">
                                            <?php echo htmlspecialchars($level); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="sortFilter">
                                    <option value="newest">Newest First</option>
                                    <option value="oldest">Oldest First</option>
                                    <option value="name">Name A-Z</option>
                                    <option value="updated">Recently Updated</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Filters and Search -->

        <!-- Course Count and Per Page Selector -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <span class="text-muted" id="courseCount">Loading courses...</span>
            </div>
            <div>
                <label class="form-label small mb-0 me-2">Show:</label>
                <select class="form-select form-select-sm d-inline-block w-auto" id="perPageSelect">
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span class="text-muted small ms-2">courses per page</span>
            </div>
        </div>

        <!-- Table View -->
        <div id="courses-table" class="card">
            <div class="table-responsive">
                <table class="table table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Course</th>
                            <th scope="col">Status</th>
                            <th scope="col">Instructors</th>
                            <th scope="col">Students</th>
                            <th scope="col" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="courseTableBody">
                        <!-- Table content will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
        <!-- End Table View -->

        <!-- Pagination -->
        <nav aria-label="Courses pagination" class="mt-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small" id="paginationInfo">Showing 0 of 0 courses</span>
                </div>
                <ul class="pagination justify-content-center" id="paginationControls">
                    <!-- Pagination will be populated by JavaScript -->
                </ul>
            </div>
        </nav>

        <!-- No Results State -->
        <div id="noResults" style="display: none;" class="text-center py-5">
            <i class="bi-search fs-1 text-muted"></i>
            <h5 class="mt-3">No courses found</h5>
            <p class="text-muted">Try adjusting your search or filter criteria</p>
        </div>
    </div>
    <!-- End Content -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- All Course Modals -->
<?php renderAllCourseModals(); ?>

<!-- Add custom styles -->
<style>
    .card {
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }

    .card:hover {
        box-shadow: 0 .25rem .5rem rgba(0, 0, 0, .075);
        transform: translateY(-2px);
    }

    /* Avatar Stack */
    .avatar-stack {
        display: flex;
        align-items: center;
        position: relative;
    }

    .avatar-stack .avatar {
        border: 2px solid #fff;
        transition: all 0.3s ease;
        position: relative;
        margin-left: -6px;
    }

    .avatar-stack .avatar:first-child {
        margin-left: 0;
    }

    .avatar-stack .avatar:hover {
        transform: translateX(8px) scale(1.05);
        z-index: 10;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .avatar-stack .avatar:hover~.avatar {
        transform: translateX(8px);
    }

    .avatar-initial {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 600;
    }

    /* Instructor Name Tooltip */
    .name-tooltip {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%) translateY(-5px);
        background: #3a3f45;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: all 0.2s ease;
        z-index: 100;
    }

    .avatar:hover .name-tooltip {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(-10px);
    }

    .name-tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        border-width: 4px;
        border-style: solid;
        border-color: #3a3f45 transparent transparent transparent;
        transform: translateX(-50%);
    }

    /* Soft Button Colors */
    .btn-soft-primary {
        background-color: rgba(55, 125, 255, 0.1);
        color: #377dff;
        border-color: transparent;
    }

    .btn-soft-primary:hover {
        background-color: rgba(55, 125, 255, 0.2);
        color: #377dff;
    }

    .btn-soft-info {
        background-color: rgba(0, 201, 219, 0.1);
        color: #00c9db;
        border-color: transparent;
    }

    .btn-soft-info:hover {
        background-color: rgba(0, 201, 219, 0.2);
        color: #00c9db;
    }

    .btn-soft-success {
        background-color: rgba(0, 201, 167, 0.1);
        color: #00c9a7;
        border-color: transparent;
    }

    .btn-soft-success:hover {
        background-color: rgba(0, 201, 167, 0.2);
        color: #00c9a7;
    }

    .btn-soft-warning {
        background-color: rgba(255, 181, 107, 0.1);
        color: #ffb56b;
        border-color: transparent;
    }

    .btn-soft-warning:hover {
        background-color: rgba(255, 181, 107, 0.2);
        color: #ffb56b;
    }

    .btn-soft-danger {
        background-color: rgba(237, 53, 81, 0.1);
        color: #ed3551;
        border-color: transparent;
    }

    .btn-soft-danger:hover {
        background-color: rgba(237, 53, 81, 0.2);
        color: #ed3551;
    }

    .btn-soft-secondary {
        background-color: rgba(226, 230, 236, 0.1);
        color: #677788;
        border-color: transparent;
    }

    .btn-soft-secondary:hover {
        background-color: rgba(226, 230, 236, 0.2);
        color: #677788;
    }

    .btn-soft-purple {
        background-color: rgba(123, 80, 250, 0.1);
        color: #7b50fa;
        border-color: transparent;
    }

    .btn-soft-purple:hover {
        background-color: rgba(123, 80, 250, 0.2);
        color: #7b50fa;
    }

    /* Ghost button */
    .btn-ghost-secondary {
        color: #677788;
        background-color: transparent;
        border-color: transparent;
    }

    .btn-ghost-secondary:hover {
        color: #495057;
        background-color: rgba(226, 230, 236, 0.1);
    }

    /* Badge enhancements */
    .text-purple {
        color: #7b50fa !important;
    }

    .bg-soft-purple {
        background-color: rgba(123, 80, 250, 0.1) !important;
    }

    /* Search highlighting */
    .highlight {
        background-color: #fff8e1;
        padding: 0 2px;
        border-radius: 2px;
    }

    /* Pagination styling */
    .pagination .page-link {
        color: #677788;
        border-color: #dee2e6;
    }

    .pagination .page-link:hover {
        color: #377dff;
        background-color: #e8f5ff;
        border-color: #dee2e6;
    }

    .pagination .page-item.active .page-link {
        background-color: #377dff;
        border-color: #377dff;
        color: #fff;
    }

    .pagination .page-item.disabled .page-link {
        color: #98a6ad;
        background-color: #fff;
        border-color: #dee2e6;
    }
</style>

<!-- JavaScript for table view only -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize variables
        const searchInput = document.getElementById('courseSearch');
        const statusFilter = document.getElementById('statusFilter');
        const categoryFilter = document.getElementById('categoryFilter');
        const levelFilter = document.getElementById('levelFilter');
        const sortFilter = document.getElementById('sortFilter');
        const perPageSelect = document.getElementById('perPageSelect');
        const noResults = document.getElementById('noResults');
        const courseCount = document.getElementById('courseCount');
        const paginationInfo = document.getElementById('paginationInfo');
        const paginationControls = document.getElementById('paginationControls');

        let currentPage = 1;
        let totalPages = 1;
        let totalCourses = 0;
        let perPage = 20;
        let currentFilters = {
            search: '',
            status: '',
            category: '',
            level: '',
            sort: 'newest'
        };

        // Initialize tooltips
        initializeTooltips();

        // Load initial data
        loadCourses();

        // Event listeners
        setupEventListeners();

        // Helper Functions
        function initializeTooltips() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        function setupEventListeners() {
            // Search and filters
            searchInput.addEventListener('input', debounce(handleSearch, 300));
            statusFilter.addEventListener('change', handleFilterChange);
            categoryFilter.addEventListener('change', handleFilterChange);
            levelFilter.addEventListener('change', handleFilterChange);
            sortFilter.addEventListener('change', handleFilterChange);
            perPageSelect.addEventListener('change', handlePerPageChange);

            // Course actions
            document.addEventListener('click', handleCourseAction);

            // Pagination
            document.addEventListener('click', handlePaginationClick);
        }

        function handleSearch(e) {
            currentFilters.search = e.target.value;
            currentPage = 1; // Reset to first page on search
            filterCourses();
        }

        function handleFilterChange(e) {
            currentFilters[e.target.id.replace('Filter', '')] = e.target.value;
            currentPage = 1; // Reset to first page on filter change
            filterCourses();
        }

        function handlePerPageChange(e) {
            perPage = parseInt(e.target.value);
            currentPage = 1; // Reset to first page
            loadCourses();
        }

        function handlePaginationClick(e) {
            const paginationLink = e.target.closest('.page-link[data-page]');
            if (!paginationLink) return;

            e.preventDefault();
            const newPage = parseInt(paginationLink.dataset.page);

            if (newPage >= 1 && newPage <= totalPages && newPage !== currentPage) {
                currentPage = newPage;
                loadCourses();
            }
        }

        function loadCourses() {
            showLoading();

            const params = new URLSearchParams({
                ...currentFilters,
                view: 'table', // Always use table view
                page: currentPage,
                limit: perPage
            });

            fetch(`../ajax/department/search_courses.php?${params.toString()}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    hideLoading();

                    if (data.success) {
                        updateTableView(data.html);
                        updateCourseCount(data.count);
                        updatePagination(data.current_page, data.total_pages, data.total_count);
                        updateNoResultsState(data.count === 0);

                        // Re-initialize tooltips for new content
                        initializeTooltips();

                        // Initialize dropdowns
                        initializeDropdowns();

                        // Dispatch event for other listeners
                        document.dispatchEvent(new CustomEvent('courses-updated'));
                    } else {
                        showError(data.message || 'Failed to load courses');
                    }
                })
                .catch(error => {
                    hideLoading();
                    showError('Error loading courses: ' + error.message);
                    console.error('AJAX Error:', error);
                });
        }

        function filterCourses() {
            loadCourses();
        }

        function updateTableView(html) {
            document.getElementById('courseTableBody').innerHTML = html;
        }

        function updateCourseCount(count) {
            if (count === 0) {
                courseCount.textContent = 'No courses found';
            } else if (count === 1) {
                courseCount.textContent = '1 course found';
            } else {
                courseCount.textContent = `${count} courses found`;
            }
        }

        function updatePagination(current, total, totalCount) {
            currentPage = current;
            totalPages = total;
            totalCourses = totalCount;

            // Update pagination info
            const start = ((currentPage - 1) * perPage) + 1;
            const end = Math.min(currentPage * perPage, totalCount);
            paginationInfo.textContent = `Showing ${start}-${end} of ${totalCount} courses`;

            // Build pagination controls
            let paginationHtml = '';

            // Previous button
            paginationHtml += `
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage - 1}" tabindex="${currentPage === 1 ? '-1' : ''}">
                        <i class="bi-chevron-left me-1"></i> Previous
                    </a>
                </li>
            `;

            // First page if not visible
            if (currentPage > 3) {
                paginationHtml += `
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="1">1</a>
                    </li>
                `;
                if (currentPage > 4) {
                    paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }

            // Page numbers around current page
            const start_page = Math.max(1, currentPage - 2);
            const end_page = Math.min(totalPages, currentPage + 2);

            for (let page = start_page; page <= end_page; page++) {
                paginationHtml += `
                    <li class="page-item ${page === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${page}">${page}</a>
                    </li>
                `;
            }

            // Last page if not visible
            if (currentPage < totalPages - 2) {
                if (currentPage < totalPages - 3) {
                    paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
                paginationHtml += `
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a>
                    </li>
                `;
            }

            // Next button
            paginationHtml += `
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage + 1}" tabindex="${currentPage === totalPages ? '-1' : ''}">
                        Next <i class="bi-chevron-right ms-1"></i>
                    </a>
                </li>
            `;

            paginationControls.innerHTML = paginationHtml;

            // Hide pagination if only one page
            if (totalPages <= 1) {
                paginationControls.style.display = 'none';
            } else {
                paginationControls.style.display = 'flex';
            }
        }

        function updateNoResultsState(show) {
            if (show) {
                document.getElementById('courses-table').style.display = 'none';
                noResults.style.display = 'block';
                paginationControls.style.display = 'none';
            } else {
                noResults.style.display = 'none';
                document.getElementById('courses-table').style.display = 'block';
            }
        }

        // Initialize dropdowns with proper configuration
        function initializeDropdowns() {
            const dropdownElements = document.querySelectorAll('.dropdown-fixed .btn[data-bs-toggle="dropdown"]');
            dropdownElements.forEach(dropdown => {
                // Clean up any existing dropdown initialization
                try {
                    const dropdownInstance = bootstrap.Dropdown.getInstance(dropdown);
                    if (dropdownInstance) {
                        dropdownInstance.dispose();
                    }
                } catch (e) {
                    // Ignore errors if no instance exists
                }

                // Create new dropdown instance with proper configuration
                new bootstrap.Dropdown(dropdown, {
                    boundary: 'viewport',
                    reference: 'toggle',
                    display: 'static'
                });
            });
        }

function handleCourseAction(e) {
    const actionElement = e.target.closest('[data-action]');
    if (!actionElement) return;

    e.preventDefault();
    const action = actionElement.dataset.action;
    const courseId = actionElement.dataset.courseId;

    switch (action) {
        case 'view_details':
            showCourseDetails(courseId);
            break;

        case 'view_analytics':
            window.location.href = `course-analytics.php?course_id=${courseId}`;
            break;

        case 'manage_course':
            // Redirect to course management page
            window.location.href = `manage-course.php?course_id=${courseId}`;
            break;
            
        case 'review_course':
            // Redirect to the new review page
            window.location.href = `review-course.php?course_id=${courseId}`;
            break;

        case 'publish_course':
            // Publish the approved course
            confirmAction('Publish Course', 'Are you sure you want to publish this course? It will be visible to students.',
                () => performCourseAction(courseId, 'publish'));
            break;

        case 'unpublish':
            confirmAction('Unpublish Course', 'Are you sure you want to unpublish this course?',
                () => performCourseAction(courseId, 'unpublish'));
            break;

        case 'archive':
            confirmAction('Archive Course', 'Are you sure you want to archive this course?',
                () => performCourseAction(courseId, 'archive'), 'danger');
            break;
    }
}       
function performCourseAction(courseId, action, additionalData = {}) {
            showLoading();

            const formData = new FormData();
            formData.append('action', action);
            formData.append('course_id', courseId);

            // Add any additional data
            Object.keys(additionalData).forEach(key => {
                formData.append(key, additionalData[key]);
            });

            fetch('../ajax/department/course_action_handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();

                    if (data.success) {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            showSuccess(data.message);
                            loadCourses(); // Reload courses to reflect changes
                        }
                    } else {
                        showError(data.message);
                    }
                })
                .catch(error => {
                    hideLoading();
                    showError('Error performing action: ' + error.message);
                });
        }

        function showCourseDetails(courseId) {
            const modal = new bootstrap.Modal(document.getElementById('courseDetailsModal'));
            modal.show();

            // Load course details via AJAX
            fetch(`../ajax/department/course_action_handler.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=view_details&course_id=${courseId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('courseDetailsContent').innerHTML = data.html;
                    } else {
                        showError(data.message);
                        modal.hide();
                    }
                })
                .catch(error => {
                    showError('Error loading course details: ' + error.message);
                    modal.hide();
                });
        }

        function showRevisionRequestForm(courseId) {
            const modal = new bootstrap.Modal(document.getElementById('revisionRequestModal'));
            document.getElementById('revisionCourseId').value = courseId;
            document.getElementById('revisionComments').value = '';
            modal.show();

            document.getElementById('revisionRequestForm').onsubmit = function(e) {
                e.preventDefault();

                const formData = new FormData(this);

                fetch('../ajax/department/course_action_handler.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            modal.hide();
                            showSuccess(data.message);
                            loadCourses();
                        } else {
                            showError(data.message);
                        }
                    })
                    .catch(error => {
                        showError('Error sending revision request: ' + error.message);
                    });
            };
        }

        function showRejectCourseForm(courseId) {
            const modal = new bootstrap.Modal(document.getElementById('rejectCourseModal'));
            document.getElementById('rejectCourseId').value = courseId;
            document.getElementById('rejectComments').value = '';
            modal.show();

            document.getElementById('rejectCourseForm').onsubmit = function(e) {
                e.preventDefault();

                const formData = new FormData(this);

                fetch('../ajax/department/course_action_handler.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            modal.hide();
                            showSuccess(data.message);
                            loadCourses();
                        } else {
                            showError(data.message);
                        }
                    })
                    .catch(error => {
                        showError('Error rejecting course: ' + error.message);
                    });
            };
        }

        // Utility functions
        function confirmAction(title, message, callback, type = 'primary') {
            const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
            document.getElementById('confirmationModalLabel').textContent = title;
            document.getElementById('confirmationMessage').textContent = message;

            const confirmBtn = document.getElementById('confirmActionBtn');
            confirmBtn.className = `btn btn-${type}`;
            confirmBtn.textContent = 'Confirm';

            confirmBtn.onclick = function() {
                modal.hide();
                callback();
            };

            modal.show();
        }

        function showLoading() {
            // Use the existing showOverlay function if it exists
            if (typeof window.showOverlay === 'function') {
                window.showOverlay('Loading courses...');
                return;
            }

            // Fallback to creating a simple loading overlay
            const overlay = document.createElement('div');
            overlay.id = 'loadingOverlay';
            overlay.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center';
            overlay.style.backgroundColor = 'rgba(255, 255, 255, 0.8)';
            overlay.style.zIndex = '9999';
            overlay.innerHTML = `
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            `;
            document.body.appendChild(overlay);
        }

        function hideLoading() {
            // Use the existing removeOverlay function if it exists
            if (typeof window.removeOverlay === 'function') {
                window.removeOverlay();
                return;
            }

            // Fallback to removing the simple loading overlay
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) {
                overlay.remove();
            }
        }

        function showSuccess(message) {
            showToast(message, 'success');
        }

        function showError(message) {
            showToast(message, 'danger');
        }

        function showToast(message, type) {
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type} border-0`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            `;

            // Add to toast container (create if doesn't exist)
            let toastContainer = document.getElementById('toastContainer');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toastContainer';
                toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                toastContainer.style.zIndex = '11';
                document.body.appendChild(toastContainer);
            }

            toastContainer.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();

            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    });
</script>

<?php
// Close the connection at the very end
$conn->close();
include '../includes/department/footer.php';
?>