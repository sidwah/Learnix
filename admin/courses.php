<?php include '../includes/admin-header.php'; ?>
<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main">
    <!-- Navbar -->
    <nav class=" navbar navbar-expand-lg navbar-sidebar navbar-vertical navbar-light bg-white border-end">
        <?php include '../includes/admin-sidebar.php'; ?>
    </nav>
    <!-- End Navbar -->

    <!-- Content -->
    <div class="navbar-sidebar-aside-content content-space-1 content-space-md-2 px-lg-5 px-xl-10">
        <!-- Page Header -->
        <div class="docs-page-header">
            <div class="row align-items-center">
                <div class="col-sm">
                    <h1 class="docs-page-header-title">Courses Management</h1>
                    <p class="docs-page-header-text">Manage course submissions, approvals, and more</p>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Summary Cards -->
        <div class="row mb-4">
            <!-- Total Courses Card -->
            <div class="col">
                <div class="card h-100">
                    <div class="card-body p-3">
                        <h6 class="card-subtitle mb-1 fs-6">Total Courses</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="card-title text-inherit mb-0" id="total_courses">
                                    <i class="spinner-border spinner-border-sm text-primary"></i>
                                </h2>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-soft-primary text-primary p-2">
                                    <i class="bi-journal-bookmark"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Published Courses Card -->
            <div class="col">
                <div class="card h-100">
                    <div class="card-body p-3">
                        <h6 class="card-subtitle mb-1 fs-6">Published</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="card-title text-inherit mb-0" id="published_courses">
                                    <i class="spinner-border spinner-border-sm text-success"></i>
                                </h2>
                                <small class="text-body fs-6" id="published_percentage">
                                    <i class="spinner-border spinner-border-sm text-muted"></i>
                                </small>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-soft-success text-success p-2">
                                    <i class="bi-check-circle-fill"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Courses Card -->
            <div class="col">
                <div class="card h-100">
                    <div class="card-body p-3">
                        <h6 class="card-subtitle mb-1 fs-6">Pending</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="card-title text-inherit mb-0" id="pending_courses">
                                    <i class="spinner-border spinner-border-sm text-warning"></i>
                                </h2>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-soft-warning text-warning p-2">
                                    <i class="bi-hourglass-split"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rejected Courses Card -->
            <div class="col">
                <div class="card h-100">
                    <div class="card-body p-3">
                        <h6 class="card-subtitle mb-1 fs-6">Rejected</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="card-title text-inherit mb-0" id="rejected_courses">
                                    <i class="spinner-border spinner-border-sm text-danger"></i>
                                </h2>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-soft-danger text-danger p-2">
                                    <i class="bi-x-circle-fill"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Suspended Courses Card -->
            <div class="col">
                <div class="card h-100">
                    <div class="card-body p-3">
                        <h6 class="card-subtitle mb-1 fs-6">Suspended</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="card-title text-inherit mb-0" id="suspended_courses">
                                    <i class="spinner-border spinner-border-sm text-secondary"></i>
                                </h2>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-soft-secondary text-secondary p-2">
                                    <i class="bi-pause-circle-fill"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Summary Cards -->

        <!-- Heading -->
        <h2 id="component-1" class="hs-docs-heading">
            Course Submissions <a class="anchorjs-link" href="#component-1" aria-label="Anchor" data-anchorjs-icon="#"></a>
        </h2>
        <!-- End Heading -->

        <!-- Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center mb-3">
                <h4 class="card-header-title">Course submissions</h4>
                <div class="d-flex align-items-center">
                    <select id="statusFilter" class="form-select form-select-sm me-2">
                        <option value="all">All Status</option>
                        <option value="published">Published</option>
                        <option value="pending">Pending</option>
                        <option value="rejected">Rejected</option>
                        <option value="suspended">Suspended</option>
                    </select>
                    <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="🔍 Search Courses...">
                </div>
            </div>

            <!-- Courses Table -->
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th class="sortable" data-sort="name">Course Title <span class="sort-icon">⇅</span></th>
                            <th class="sortable" data-sort="instructor">Instructor <span class="sort-icon">⇅</span></th>
                            <th class="sortable" data-sort="category">Category <span class="sort-icon">⇅</span></th>
                            <th class="sortable" data-sort="enrollment">Enrollment <span class="sort-icon">⇅</span></th>
                            <th class="sortable" data-sort="status">Status <span class="sort-icon">⇅</span></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Course data will be loaded dynamically -->
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading courses...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls (kept for consistency) -->
            <div class="d-flex justify-content-between align-items-center mt-3 px-3 pb-3">
                <button id="prevPage" class="btn btn-sm btn-outline-primary">Previous</button>
                <span id="paginationNumbers">
                    <button class="btn btn-sm btn-primary mx-1">1</button>
                </span>
                <button id="nextPage" class="btn btn-sm btn-outline-primary">Next</button>
            </div>

            <style>
                .sortable {
                    cursor: pointer;
                    user-select: none;
                }

                .sort-icon {
                    font-size: 14px;
                    margin-left: 5px;
                    color: gray;
                }

                #searchInput {
                    max-width: 200px;
                }
            </style>

            <!-- Course Details Modal -->
            <div class="modal fade" id="courseModal" tabindex="-1" aria-labelledby="courseModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="courseModalLabel">Course Submission Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Course Information -->
                            <div class="mb-4">
                                <h6>Course Information</h6>
                                <div class="d-flex align-items-center mb-3">
                                    <img src="../uploads/thumbnails/default.jpg"
                                        alt="Course Thumbnail"
                                        class="avatar avatar-lg me-3 rounded"
                                        id="modalCourseThumbnail">
                                    <div>
                                        <h5 class="mb-0" id="modalCourseTitle">Loading...</h5>
                                        <p class="mb-0 text-muted" id="modalCourseInstructor">Instructor: Loading...</p>
                                        <small class="text-muted" id="modalCourseCreated">Created: Loading...</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Course Description -->
                            <div class="mb-4">
                                <h6>Description</h6>
                                <div class="p-3 bg-soft-light rounded overflow-auto" style="max-height: 200px;">
                                    <p id="modalCourseDescription">Loading course description...</p>
                                </div>
                            </div>

                            <!-- Course Outline -->
                            <div class="mb-4">
                                <h6>Course Outline</h6>
                                <div class="p-3 bg-soft-light rounded overflow-auto" style="max-height: 300px;" id="modalCourseOutline">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div> Loading course outline...
                                </div>
                            </div>

                            <!-- Course Materials -->
                            <div class="mb-4" id="materialSection">
                                <h6>Course Materials</h6>
                                <div class="row" id="modalCourseMaterials">
                                    <div class="col-12 text-center py-3">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div> Loading course materials...
                                    </div>
                                </div>
                            </div>

                            <!-- Course Status -->
                            <div id="currentStatusSection" class="mb-4">
                                <h6>Current Status</h6>
                                <div id="modalCurrentStatus" class="badge bg-warning">Loading...</div>
                                <div id="modalRejectionReason" class="mt-2 d-none">
                                    <small class="text-muted">Reason for rejection:</small>
                                    <p class="mb-0 text-danger"></p>
                                </div>
                            </div>

                            <!-- Review History -->
                            <div id="reviewHistorySection" class="mb-4">
                                <h6>Review History</h6>
                                <div id="reviewHistoryContent" class="p-3 bg-soft-light rounded overflow-auto" style="max-height: 200px;">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div> Loading review history...
                                </div>
                            </div>

                            <!-- Admin Actions Section -->
                            <div id="adminActionSection">
                                <h6>Course Action</h6>
                                <div class="d-flex flex-column">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="courseAction" id="publishRadio" value="publish">
                                        <label class="form-check-label text-success" for="publishRadio">
                                            Publish course
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="courseAction" id="rejectCourseRadio" value="reject">
                                        <label class="form-check-label text-danger" for="rejectCourseRadio">
                                            Reject course
                                        </label>
                                    </div>
                                    <div id="rejectionReasonField" class="mb-3 d-none">
                                        <label for="rejectionReason" class="form-label">Reason for rejection <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="rejectionReason" rows="3"
                                            placeholder="Provide a clear reason for the rejection. This will be shown to the instructor."
                                            aria-describedby="rejectionReasonHelp"></textarea>
                                        <div id="rejectionReasonHelp" class="form-text">
                                            Please explain why this course is being rejected so the instructor can make appropriate improvements.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-white">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="submitCourseAction" data-course-id="">Submit Action</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Course Details Modal -->

        <!-- Approve Course Modal -->
        <div class="modal fade" id="approveCourseModal" tabindex="-1" aria-labelledby="approveCourseModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="approveCourseModalLabel">Approve Course</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to approve "<span id="approveCourseName"></span>"?</p>
                        <p>This will make the course available to all students.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" id="confirmApprove" data-course-id="">Approve Course</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Approve Course Modal -->

        <!-- Suspend Course Modal -->
        <div class="modal fade" id="suspendCourseModal" tabindex="-1" aria-labelledby="suspendCourseModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="suspendCourseModalLabel">Suspend Course</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to suspend "<span id="suspendCourseName"></span>"?</p>
                        <p>This will temporarily make the course unavailable to students.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-warning" id="confirmSuspend" data-course-id="">Suspend Course</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Suspend Course Modal -->

        <!-- Toast Notification -->
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <strong class="me-auto" id="toastTitle"><i class="bi-info-circle-fill text-info me-2"></i>Notification</strong>
                    <small id="toastTime">Just now</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body" id="toastMessage">
                    This is a notification message.
                </div>
            </div>
        </div>
        <!-- End Toast Notification -->
    </div>
    <!-- End Card -->
    </div>
    <!-- End Content -->
</main>
<!-- ========== END MAIN CONTENT ========== -->



<!-- JS Custom -->
<script>
    let currentPage = 1;
    const itemsPerPage = 10;

    // Function to load courses data from the server
    function loadCourses(status = 'all', searchTerm = '') {
        // Show a loading indicator
        document.querySelector('tbody').innerHTML = `
        <tr>
            <td colspan="6" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading courses...</p>
            </td>
        </tr>
    `;

        // Fetch courses data
        fetch('../ajax/admin/fetch_courses.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    displayCourses(data.courses, status, searchTerm);
                    loadCourseStats(); // Refresh stats when courses are loaded
                } else {
                    showToast('danger', data.message || 'Error loading courses');
                    document.querySelector('tbody').innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="alert alert-danger">
                                Failed to load courses. Please try again.
                            </div>
                        </td>
                    </tr>
                `;
                }
            })
            .catch(error => {
                console.error('Error fetching courses:', error);
                showToast('danger', 'Failed to load courses');
                document.querySelector('tbody').innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <div class="alert alert-danger">
                            An error occurred while loading courses. Please try again.
                        </div>
                    </td>
                </tr>
            `;
            });
    }

    // Function to load course statistics
    // Function to load course statistics
    function loadCourseStats() {
        fetch('../ajax/admin/fetch_course_stats.php')
            .then(response => response.json())
            .then(data => {
                // Update dashboard cards with stats
                document.getElementById('total_courses').textContent = data.total;
                document.getElementById('published_courses').textContent = data.published;
                document.getElementById('published_percentage').textContent = `${data.published_percentage}% of total`;
                document.getElementById('pending_courses').textContent = data.pending;
                document.getElementById('rejected_courses').textContent = data.rejected;

                // Add this line to update the suspended courses count
                if (document.getElementById('suspended_courses')) {
                    document.getElementById('suspended_courses').textContent = data.suspended || 0;
                }
            })
            .catch(error => {
                console.error('Error fetching course stats:', error);
            });
    }

    // Function to display courses in the table
// Function to display courses with pagination
function displayCourses(courses, filterStatus = 'all', searchTerm = '') {
    const tableBody = document.querySelector('tbody');
    tableBody.innerHTML = '';

    // Filter courses based on status and search term
    const filteredCourses = courses.filter(course => {
        const matchesStatus = filterStatus === 'all' ||
            (filterStatus === 'published' && course.status === 'Published' && course.approval_status === 'Approved' && !course.is_suspended) ||
            (filterStatus === 'pending' && course.approval_status === 'Pending') ||
            (filterStatus === 'rejected' && course.approval_status === 'Rejected') ||
            (filterStatus === 'suspended' && course.is_suspended);

        const matchesSearch = searchTerm === '' ||
            course.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
            course.instructor_name.toLowerCase().includes(searchTerm.toLowerCase());

        return matchesStatus && matchesSearch;
    });

    // Calculate pagination
    const totalItems = filteredCourses.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, totalItems);
    const paginatedCourses = filteredCourses.slice(startIndex, endIndex);

    if (paginatedCourses.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4">
                    <div class="alert alert-info">
                        No courses found matching your criteria.
                    </div>
                </td>
            </tr>
        `;
        updatePaginationControls(totalPages, 0);
        return;
    }

    // Create table rows for each course
    paginatedCourses.forEach(course => {
        const thumbnailPath = course.thumbnail ? `../uploads/thumbnails/${course.thumbnail}` : '../assets/img/300x200/img1.jpg';

        // Determine course status display
        let statusBadge = '';
        let actionButtons = '';

        if (course.is_suspended) {
            statusBadge = `<span class="badge bg-secondary">Suspended</span>`;
            actionButtons = `
                <button class="btn btn-sm btn-soft-primary view-course" data-course-id="${course.course_id}">
                    <i class="bi-eye"></i>
                </button>
                <button class="btn btn-sm btn-soft-success ms-2 restore-btn" 
                        data-course-id="${course.course_id}" 
                        data-course-name="${course.title}">
                    <i class="bi-arrow-counterclockwise"></i>
                </button>
            `;
        } else if (course.status === 'Published' && course.approval_status === 'Approved') {
            statusBadge = `<span class="badge bg-success">Published</span>`;
            actionButtons = `
                <button class="btn btn-sm btn-soft-primary view-course" data-course-id="${course.course_id}">
                    <i class="bi-eye"></i>
                </button>
                <button class="btn btn-sm btn-soft-warning ms-2 suspend-btn" 
                        data-course-id="${course.course_id}" 
                        data-course-name="${course.title}">
                    <i class="bi-pause-fill"></i>
                </button>
            `;
        } else if (course.approval_status === 'Pending') {
            statusBadge = `<span class="badge bg-warning">Pending</span>`;
            actionButtons = `
                <button class="btn btn-sm btn-soft-primary view-course" data-course-id="${course.course_id}">
                    <i class="bi-eye"></i>
                </button>
                <button class="btn btn-sm btn-soft-success ms-2 approve-btn" 
                        data-course-id="${course.course_id}" 
                        data-course-name="${course.title}">
                    <i class="bi-check-lg"></i>
                </button>
                <button class="btn btn-sm btn-soft-danger ms-2 reject-btn" 
                        data-course-id="${course.course_id}" 
                        data-course-name="${course.title}">
                    <i class="bi-x-lg"></i>
                </button>
            `;
        } else if (course.approval_status === 'Rejected') {
            statusBadge = `<span class="badge bg-danger">Rejected</span>`;
            actionButtons = `
                <button class="btn btn-sm btn-soft-primary view-course" data-course-id="${course.course_id}">
                    <i class="bi-eye"></i>
                </button>
                <button class="btn btn-sm btn-soft-success ms-2 approve-btn" 
                        data-course-id="${course.course_id}" 
                        data-course-name="${course.title}">
                    <i class="bi-check-lg"></i>
                </button>
            `;
        }

        // Create enrollment badge
        const enrollmentCount = parseInt(course.enrollment_count) || 0;
        const enrollmentBadge = enrollmentCount > 0 ?
            `<span class="badge bg-soft-info text-info">${enrollmentCount} students</span>` :
            `<span class="badge bg-soft-secondary text-secondary">0 students</span>`;

        // Add table row
        tableBody.innerHTML += `
            <tr data-course-id="${course.course_id}">
                <td>
                    <div class="d-flex align-items-center">
                        <img src="${thumbnailPath}"
                            alt="${course.title}"
                            class="avatar avatar-sm me-3 rounded">
                        <div>
                            <h6 class="mb-0">${course.title}</h6>
                            <small class="text-muted" style="font-size: 0.75rem;">${course.formatted_date}</small>
                        </div>
                    </div>
                </td>
                <td>${course.instructor_name}</td>
                <td id="breadcrumb-${course.id}">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">${course.category_name}</li>
                        <li class="breadcrumb-item">${course.subcategory_name}</li>
                    </ol>
                </td>
                <td>${enrollmentBadge}</td>
                <td>${statusBadge}</td>
                <td>
                    ${actionButtons}
                </td>
            </tr>
        `;
    });

    // Update pagination controls
    updatePaginationControls(totalPages, totalItems);
    
    // Re-attach event listeners to buttons
    attachEventListeners();
}

// Function to update pagination controls
function updatePaginationControls(totalPages, totalItems) {
    const paginationNumbers = document.getElementById('paginationNumbers');
    const prevButton = document.getElementById('prevPage');
    const nextButton = document.getElementById('nextPage');
    
    // Clear existing pagination numbers
    paginationNumbers.innerHTML = '';
    
    // Update previous button state
    prevButton.disabled = currentPage === 1;
    
    // Update next button state
    nextButton.disabled = currentPage === totalPages || totalPages === 0;
    
    // Show current page and total pages
    const pageInfo = document.createElement('span');
    pageInfo.className = 'mx-2';
    // pageInfo.textContent = `Page ${currentPage} of ${totalPages} (${totalItems} items)`;
    paginationNumbers.appendChild(pageInfo);
    
    // Add page number buttons (optional - for direct page navigation)
    if (totalPages > 1) {
        const maxVisiblePages = 5; // Maximum number of page buttons to show
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
        
        // Adjust if we're at the end
        if (endPage - startPage + 1 < maxVisiblePages) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }
        
        // Add "First" button if needed
        if (startPage > 1) {
            const firstButton = document.createElement('button');
            firstButton.className = 'btn btn-sm btn-outline-primary mx-1';
            firstButton.textContent = '1';
            firstButton.addEventListener('click', () => {
                currentPage = 1;
                loadCourses(document.getElementById('statusFilter').value, document.getElementById('searchInput').value);
            });
            paginationNumbers.appendChild(firstButton);
            
            if (startPage > 2) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'mx-1';
                ellipsis.textContent = '...';
                paginationNumbers.appendChild(ellipsis);
            }
        }
        
        // Add page number buttons
        for (let i = startPage; i <= endPage; i++) {
            const pageButton = document.createElement('button');
            pageButton.className = `btn btn-sm ${i === currentPage ? 'btn-primary' : 'btn-outline-primary'} mx-1`;
            pageButton.textContent = i;
            pageButton.addEventListener('click', () => {
                currentPage = i;
                loadCourses(document.getElementById('statusFilter').value, document.getElementById('searchInput').value);
            });
            paginationNumbers.appendChild(pageButton);
        }
        
        // Add "Last" button if needed
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'mx-1';
                ellipsis.textContent = '...';
                paginationNumbers.appendChild(ellipsis);
            }
            
            const lastButton = document.createElement('button');
            lastButton.className = 'btn btn-sm btn-outline-primary mx-1';
            lastButton.textContent = totalPages;
            lastButton.addEventListener('click', () => {
                currentPage = totalPages;
                loadCourses(document.getElementById('statusFilter').value, document.getElementById('searchInput').value);
            });
            paginationNumbers.appendChild(lastButton);
        }
    }
}

    // Function to attach event listeners to buttons
    function attachEventListeners() {
        // View course details
        document.querySelectorAll(".view-course").forEach(button => {
            button.addEventListener("click", function() {
                const courseId = this.getAttribute('data-course-id');
                fetchCourseDetails(courseId);
            });
        });

        // Quick approve
        document.querySelectorAll(".approve-btn").forEach(button => {
            button.addEventListener("click", function() {
                const courseId = this.getAttribute('data-course-id');
                const courseName = this.getAttribute('data-course-name');

                // Set the course name in the modal
                document.getElementById('approveCourseName').textContent = courseName;

                // Store the course ID on the confirm button
                document.getElementById('confirmApprove').setAttribute('data-course-id', courseId);

                // Show the modal
                const modal = new bootstrap.Modal(document.getElementById('approveCourseModal'));
                modal.show();
            });
        });

        // Quick reject
        document.querySelectorAll(".reject-btn").forEach(button => {
            button.addEventListener("click", function() {
                const courseId = this.getAttribute('data-course-id');
                const courseName = this.getAttribute('data-course-name');

                // Set the course name in the modal
                document.getElementById('modalCourseTitle').textContent = courseName;

                // Store the course ID on the submit button
                document.getElementById('submitCourseAction').setAttribute('data-course-id', courseId);

                const modal = new bootstrap.Modal(document.getElementById('courseModal'));
                modal.show();
                // Pre-select reject option
                document.getElementById("rejectCourseRadio").checked = true;
                document.getElementById("rejectionReasonField").classList.remove("d-none");
            });
        });

        // Suspend course
        document.querySelectorAll(".suspend-btn").forEach(button => {
            button.addEventListener("click", function() {
                const courseId = this.getAttribute('data-course-id');
                const courseName = this.getAttribute('data-course-name');

                // Set the course name in the modal
                document.getElementById('suspendCourseName').textContent = courseName;

                // Store the course ID on the confirm button
                document.getElementById('confirmSuspend').setAttribute('data-course-id', courseId);

                // Show the modal
                const modal = new bootstrap.Modal(document.getElementById('suspendCourseModal'));
                modal.show();
            });
        });

        // Restore course
        document.querySelectorAll(".restore-btn").forEach(button => {
            button.addEventListener("click", function() {
                const courseId = this.getAttribute('data-course-id');
                const courseName = this.getAttribute('data-course-name');

                if (confirm(`Are you sure you want to restore "${courseName}"? This will make the course published again.`)) {
                    restoreCourse(courseId);
                }
            });
        });

    }

    // Function to restore a suspended course
    function restoreCourse(courseId) {
        fetch('../ajax/admin/restore_course.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `course_id=${courseId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast('success', 'Course restored successfully');
                    loadCourses(document.getElementById('statusFilter').value, document.getElementById('searchInput').value);
                } else {
                    showToast('danger', data.message || 'Error restoring course');
                }
            })
            .catch(error => {
                console.error('Error restoring course:', error);
                showToast('danger', 'Failed to restore course');
            });
    }

    // Function to fetch course details for the modal
    function fetchCourseDetails(courseId) {
        // Show loading state in modal
        document.getElementById('modalCourseTitle').textContent = "Loading...";
        document.getElementById('modalCourseInstructor').textContent = "Instructor: Loading...";
        document.getElementById('modalCourseCreated').textContent = "Created: Loading...";
        document.getElementById('modalCourseDescription').textContent = "Loading course description...";
        document.getElementById('modalCourseOutline').innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading course outline...';
        document.getElementById('modalCourseMaterials').innerHTML = '<div class="col-12 text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading course materials...</div>';
        document.getElementById('modalCurrentStatus').className = 'badge bg-secondary';
        document.getElementById('modalCurrentStatus').textContent = 'Loading...';
        document.getElementById('reviewHistoryContent').innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading review history...';

        // Reset radio buttons
        document.getElementById('publishRadio').checked = false;
        document.getElementById('rejectCourseRadio').checked = false;
        document.getElementById('rejectionReasonField').classList.add('d-none');
        document.getElementById('rejectionReason').value = '';

        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('courseModal'));
        modal.show();

        // Fetch course details from the server
        fetch(`../ajax/admin/fetch_course_details.php?id=${courseId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Populate modal with course details
                    const course = data.course;

                    // Update basic course info with course ID
                    document.getElementById('modalCourseTitle').textContent = `${course.title} (ID: ${course.course_id})`;
                    document.getElementById('modalCourseInstructor').textContent = `Instructor: ${course.instructor_name}`;
                    document.getElementById('modalCourseCreated').textContent = `Created: ${course.formatted_created_date}`;

                    // Set the thumbnail with proper styling
                    const modalThumbnail = document.getElementById('modalCourseThumbnail');
                    modalThumbnail.src = course.thumbnail ? `../uploads/thumbnails/${course.thumbnail}` : '../uploads/thumbnails/default.jpg';
                    modalThumbnail.style.objectFit = 'cover';
                    modalThumbnail.style.width = '80px';
                    modalThumbnail.style.height = '80px';

                    // Update course description
                    document.getElementById('modalCourseDescription').innerHTML = course.full_description;

                    // Update course status
                    let statusClass = 'bg-secondary';
                    if (course.status === 'Published' && course.approval_status === 'Approved') {
                        statusClass = 'bg-success';
                        document.getElementById('modalCurrentStatus').textContent = 'Published';
                    } else if (course.approval_status === 'Pending') {
                        statusClass = 'bg-warning';
                        document.getElementById('modalCurrentStatus').textContent = 'Pending Review';
                    } else if (course.approval_status === 'Rejected') {
                        statusClass = 'bg-danger';
                        document.getElementById('modalCurrentStatus').textContent = 'Rejected';

                        // Show rejection reason if available
                        if (data.review_history && data.review_history.length > 0) {
                            const latestReview = data.review_history[0];
                            if (latestReview.status === 'Rejected' && latestReview.review_notes) {
                                document.getElementById('modalRejectionReason').classList.remove('d-none');
                                document.getElementById('modalRejectionReason').querySelector('p').textContent = latestReview.review_notes;
                            }
                        }
                    }
                    document.getElementById('modalCurrentStatus').className = `badge ${statusClass}`;

                    // Build course outline
                    let outlineHtml = '';
                    if (data.sections && data.sections.length > 0) {
                        data.sections.forEach(section => {
                            outlineHtml += `<div class="mb-3">
                            <h6>${section.title}</h6>
                            <ul class="list-group list-group-flush">`;

                            if (section.topics && section.topics.length > 0) {
                                section.topics.forEach(topic => {
                                    outlineHtml += `<li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>${topic.title}</span>
                                    ${topic.is_previewable ? '<span class="badge bg-soft-info text-info">Preview</span>' : ''}
                                </li>`;
                                });
                            } else {
                                outlineHtml += `<li class="list-group-item text-muted">No topics in this section</li>`;
                            }

                            // Add quizzes if any
                            if (section.quizzes && section.quizzes.length > 0) {
                                section.quizzes.forEach(quiz => {
                                    outlineHtml += `<li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="bi-journal-check me-2"></i>${quiz.title}</span>
                                    <span class="badge bg-soft-primary text-primary">Quiz (${quiz.pass_mark}% pass)</span>
                                </li>`;
                                });
                            }

                            outlineHtml += `</ul></div>`;
                        });
                    } else {
                        outlineHtml = '<div class="alert alert-info">No course content available</div>';
                    }
                    document.getElementById('modalCourseOutline').innerHTML = outlineHtml;

                    // Build course materials
                    if (data.resources && data.resources.length > 0) {
                        let materialsHtml = '';
                        data.resources.forEach(resource => {
                            let iconClass = 'bi-file-earmark';
                            if (resource.file_extension === 'PDF') iconClass = 'bi-file-earmark-pdf';
                            else if (['DOC', 'DOCX'].includes(resource.file_extension)) iconClass = 'bi-file-earmark-word';
                            else if (['XLS', 'XLSX'].includes(resource.file_extension)) iconClass = 'bi-file-earmark-excel';
                            else if (['PPT', 'PPTX'].includes(resource.file_extension)) iconClass = 'bi-file-earmark-ppt';
                            else if (['JPG', 'JPEG', 'PNG', 'GIF'].includes(resource.file_extension)) iconClass = 'bi-file-earmark-image';

                            materialsHtml += `<div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="${iconClass} fs-2 text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="card-title mb-1">${resource.file_name}</h6>
                                            <p class="card-text small text-muted mb-0">
                                                ${resource.section_title} > ${resource.topic_title}
                                            </p>
                                            <p class="card-text small">${resource.file_size}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                        });
                        document.getElementById('modalCourseMaterials').innerHTML = materialsHtml;
                        document.getElementById('materialSection').classList.remove('d-none');
                    } else {
                        document.getElementById('materialSection').classList.add('d-none');
                    }

                    // Build review history
                    if (data.review_history && data.review_history.length > 0) {
                        let historyHtml = '<div class="timeline">';
                        data.review_history.forEach(review => {
                            let statusBadge = '';
                            if (review.status === 'Pending') statusBadge = '<span class="badge bg-warning">Pending</span>';
                            else if (review.status === 'Approved') statusBadge = '<span class="badge bg-success">Approved</span>';
                            else if (review.status === 'Rejected') statusBadge = '<span class="badge bg-danger">Rejected</span>';
                            else if (review.status === 'Changes Requested') statusBadge = '<span class="badge bg-info">Changes Requested</span>';

                            historyHtml += `<div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <span class="timeline-item-badge bg-soft-primary">
                                        <i class="bi-clock-history text-primary"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">Submitted by ${review.requester_name}</h6>
                                        ${statusBadge}
                                    </div>
                                    <p class="text-muted small mb-2">${review.formatted_created_date}</p>`;

                            if (review.review_notes) {
                                historyHtml += `<div class="p-3 bg-soft-light rounded mb-2">
                                <small class="text-muted">Review Notes:</small>
                                <p class="mb-0">${review.review_notes}</p>
                            </div>`;
                            }

                            if (review.reviewer_name) {
                                historyHtml += `<p class="text-muted small mb-0">Reviewed by ${review.reviewer_name}</p>`;
                            }

                            historyHtml += `</div></div>`;
                        });
                        historyHtml += '</div>';
                        document.getElementById('reviewHistoryContent').innerHTML = historyHtml;
                        document.getElementById('reviewHistorySection').classList.remove('d-none');
                    } else {
                        document.getElementById('reviewHistoryContent').innerHTML = '<div class="alert alert-info">No review history available</div>';
                        document.getElementById('reviewHistorySection').classList.remove('d-none');
                    }

                    // Update submit button with course ID
                    document.getElementById('submitCourseAction').setAttribute('data-course-id', course.course_id);

                } else {
                    showToast('danger', data.message || 'Error loading course details');
                }
            })
            .catch(error => {
                console.error('Error fetching course details:', error);
                showToast('danger', 'Failed to load course details');
                document.getElementById('modalCourseTitle').textContent = "Error loading course";
                document.getElementById('modalCourseDescription').innerHTML = '<div class="alert alert-danger">Failed to load course details. Please try again.</div>';
            });
    }

    // Function to approve a course
    function approveCourse(courseId) {
        fetch('../ajax/admin/approve_course.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `course_id=${courseId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast('success', 'Course approved successfully');
                    loadCourses(document.getElementById('statusFilter').value, document.getElementById('searchInput').value);
                } else {
                    showToast('danger', data.message || 'Error approving course');
                }
            })
            .catch(error => {
                console.error('Error approving course:', error);
                showToast('danger', 'Failed to approve course');
            });
    }

    // Function to reject a course with enhanced debugging
    function rejectCourse(courseId, reason) {
        console.log(`Attempting to reject course ID: ${courseId} with reason: "${reason}"`);

        // Show loading state
        const submitButton = document.getElementById('submitCourseAction');
        const originalButtonText = submitButton.innerHTML;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Rejecting...';
        submitButton.disabled = true;

        // Create the request body
        const formData = new URLSearchParams();
        formData.append('course_id', courseId);
        formData.append('reason', reason);

        console.log('Request payload:', formData.toString());

        fetch('../ajax/admin/reject_course.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.text().then(text => {
                    try {
                        // Try to parse the response as JSON
                        const data = JSON.parse(text);
                        console.log('Parsed response:', data);
                        return data;
                    } catch (e) {
                        // If parsing fails, log the raw response
                        console.error('Failed to parse response as JSON:', text);
                        throw new Error('Invalid JSON response from server');
                    }
                });
            })
            .then(data => {
                // Reset button state
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;

                if (data.status === 'success') {
                    console.log('Course rejection successful');
                    showToast('success', 'Course rejected successfully');
                    loadCourses(document.getElementById('statusFilter').value, document.getElementById('searchInput').value);
                } else {
                    console.error('Server returned error:', data.message);
                    showToast('danger', data.message || 'Error rejecting course');
                }
            })
            .catch(error => {
                // Reset button state
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;

                console.error('Error rejecting course:', error);
                showToast('danger', 'Failed to reject course. Please try again.');
            });
    }
   
    // Function to suspend a course
    function suspendCourse(courseId) {
        fetch('../ajax/admin/suspend_course.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `course_id=${courseId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast('warning', 'Course has been suspended');
                    loadCourses(document.getElementById('statusFilter').value, document.getElementById('searchInput').value);
                } else {
                    showToast('danger', data.message || 'Error suspending course');
                }
            })
            .catch(error => {
                console.error('Error suspending course:', error);
                showToast('danger', 'Failed to suspend course');
            });
    }

    // Toast Notification System
    function showToast(type, message) {
        const toastElement = document.getElementById('liveToast');
        const toastMessage = document.getElementById('toastMessage');
        const toastTime = document.getElementById('toastTime');
        const toastTitle = document.getElementById('toastTitle');

        // Set toast content
        toastMessage.textContent = message;
        toastTime.textContent = "Just now";

        // Set toast color and icon based on type
        toastElement.classList.remove('bg-soft-success', 'bg-soft-danger', 'bg-soft-warning', 'bg-soft-info');

        if (type === 'success') {
            toastElement.classList.add('bg-soft-success');
            toastTitle.innerHTML = '<i class="bi-check-circle-fill text-success me-2"></i>Success';
        } else if (type === 'danger') {
            toastElement.classList.add('bg-soft-danger');
            toastTitle.innerHTML = '<i class="bi-x-circle-fill text-danger me-2"></i>Error';
        } else if (type === 'warning') {
            toastElement.classList.add('bg-soft-warning');
            toastTitle.innerHTML = '<i class="bi-exclamation-triangle-fill text-warning me-2"></i>Warning';
        } else if (type === 'info') {
            toastElement.classList.add('bg-soft-info');
            toastTitle.innerHTML = '<i class="bi-info-circle-fill text-info me-2"></i>Info';
        }

        // Show the toast
        const toast = bootstrap.Toast.getOrCreateInstance(toastElement);
        toast.show();

        // Remove toast color after it's hidden
        toastElement.addEventListener('hidden.bs.toast', function() {
            toastElement.classList.remove('bg-soft-success', 'bg-soft-danger', 'bg-soft-warning', 'bg-soft-info');
        }, {
            once: true
        });
    }

    // Document ready function
    document.addEventListener("DOMContentLoaded", function() {

        // Add CSS for proper display of thumbnails and text truncation
        const style = document.createElement('style');
        style.textContent = `
        .course-title {
            max-width: 220px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .breadcrumb {
            margin-bottom: 0;
            background-color: transparent;
            padding: 0;
        }
        
        .breadcrumb-item {
            font-size: 0.875rem;
        }
        
        .avatar-thumbnail {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
    `;
        document.head.appendChild(style);

        // Load courses initially
        loadCourses();

        // Load course statistics
        loadCourseStats();

        // Search functionality
        document.getElementById("searchInput").addEventListener("input", function() {
            const searchTerm = this.value.toLowerCase();
            loadCourses(document.getElementById('statusFilter').value, searchTerm);
        });

        // Status filter
        document.getElementById("statusFilter").addEventListener("change", function() {
            const status = this.value;
            loadCourses(status, document.getElementById('searchInput').value);
        });

        // Radio buttons for rejection reason field
        document.getElementById("rejectCourseRadio").addEventListener("change", function() {
            if (this.checked) {
                document.getElementById("rejectionReasonField").classList.remove("d-none");
                // Focus on the rejection reason field
                setTimeout(() => {
                    document.getElementById("rejectionReason").focus();
                }, 300);
            }
        });

        document.getElementById("publishRadio").addEventListener("change", function() {
            if (this.checked) {
                document.getElementById("rejectionReasonField").classList.add("d-none");
                // Clear any validation errors
                const rejectionField = document.getElementById("rejectionReason");
                rejectionField.classList.remove('is-invalid');
                if (document.getElementById('rejectionReasonError')) {
                    document.getElementById('rejectionReasonError').remove();
                }
            }
        });

        // Submit course action
        document.getElementById("submitCourseAction").addEventListener("click", function() {
            const publishRadio = document.getElementById("publishRadio");
            const rejectRadio = document.getElementById("rejectCourseRadio");
            const courseId = this.getAttribute('data-course-id');

            if (!publishRadio.checked && !rejectRadio.checked) {
                showToast('danger', "Please select an action (publish or reject).");
                return;
            }

            if (rejectRadio.checked) {
                const rejectionReason = document.getElementById("rejectionReason").value.trim();

                if (!rejectionReason) {
                    // Highlight the rejection reason field with a red border
                    const rejectionField = document.getElementById("rejectionReason");
                    rejectionField.classList.add('is-invalid');

                    // Add error message below the field if it doesn't exist
                    if (!document.getElementById('rejectionReasonError')) {
                        const errorDiv = document.createElement('div');
                        errorDiv.id = 'rejectionReasonError';
                        errorDiv.className = 'invalid-feedback';
                        errorDiv.innerText = 'Please provide a reason for rejection.';
                        rejectionField.parentNode.appendChild(errorDiv);
                    }

                    showToast('danger', "Please provide a reason for rejection.");
                    return;
                }

                // Remove error styling if validation passes
                const rejectionField = document.getElementById("rejectionReason");
                rejectionField.classList.remove('is-invalid');

                // Reject the course
                rejectCourse(courseId, rejectionReason);
            } else {
                // Approve the course
                approveCourse(courseId);
            }

            const modal = bootstrap.Modal.getInstance(document.getElementById('courseModal'));
            modal.hide();
        });
        // Add validation on the rejection reason textarea
        document.getElementById("rejectionReason").addEventListener("input", function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
                if (document.getElementById('rejectionReasonError')) {
                    document.getElementById('rejectionReasonError').remove();
                }
            }
        });
        // Confirm approve action
        document.getElementById('confirmApprove').addEventListener('click', function() {
            const courseId = this.getAttribute('data-course-id');
            approveCourse(courseId);

            // Close the modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('approveCourseModal'));
            modal.hide();
        });

        // Confirm suspend action
        document.getElementById('confirmSuspend').addEventListener('click', function() {
            const courseId = this.getAttribute('data-course-id');
            suspendCourse(courseId);

            // Close the modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('suspendCourseModal'));
            modal.hide();
        });

        // Sorting functionality
        document.querySelectorAll(".sortable").forEach(header => {
            header.addEventListener("click", function() {
                const sortType = this.getAttribute('data-sort');
                const icon = this.querySelector(".sort-icon");
                const currentDirection = icon.innerHTML === "↑" ? "asc" : "desc";
                const newDirection = currentDirection === "asc" ? "desc" : "asc";

                // Update sort icon
                document.querySelectorAll(".sort-icon").forEach(icon => {
                    icon.innerHTML = "⇅";
                });

                icon.innerHTML = newDirection === "asc" ? "↑" : "↓";

                // Here you would typically re-fetch data with the new sort parameters
                // For now, we'll just show a toast message
                showToast('info', `Sorting by ${sortType} in ${newDirection}ending order`);
            });
        });


        // Pagination controls
        document.getElementById('prevPage').addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                loadCourses(document.getElementById('statusFilter').value, document.getElementById('searchInput').value);
            }
        });

        document.getElementById('nextPage').addEventListener('click', function() {
            // We don't know the total pages here, but the next button will be disabled if we're on the last page
            currentPage++;
            loadCourses(document.getElementById('statusFilter').value, document.getElementById('searchInput').value);
        });

        document.getElementById('nextPage').addEventListener('click', function() {
            showToast('info', 'Next page clicked');
        });
    });
</script>

<?php include '../includes/admin-footer.php'; ?>