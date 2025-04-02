<?php include '../includes/admin-header.php'; ?>
<!-- Toast Notification -->
<div id="liveToast" class="position-fixed toast hide" role="alert" aria-live="assertive" aria-atomic="true" style="top: 20px; right: 20px; z-index: 1000;">
    <div class="toast-header">
        <div class="d-flex align-items-center flex-grow-1">
            <div class="flex-shrink-0">
                <img class="avatar avatar-sm avatar-circle" src="../assets/img/160x160/default.jpg" alt="Notification">
            </div>
            <div class="flex-grow-1 ms-3">
                <h5 class="mb-0" id="toastTitle">System Notification</h5>
                <small class="ms-auto text-muted" id="toastTime">Just now</small>
            </div>
            <div class="text-end">
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
    <div class="toast-body" id="toastMessage">
        Notification message goes here
    </div>
</div>
<!-- End Toast Notification -->

<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main">
    <!-- Navbar -->
    <nav class="js-nav-scroller navbar navbar-expand-lg navbar-sidebar navbar-vertical navbar-light bg-white border-end" data-hs-nav-scroller-options='{
            "type": "vertical",
            "target": ".navbar-nav .active",
            "offset": 80
           }'>

        <?php include '../includes/admin-sidebar.php'; ?>
    </nav>
    <!-- End Navbar -->

    <!-- Content -->
    <div class="navbar-sidebar-aside-content content-space-1 content-space-md-2 px-lg-5 px-xl-10">
        <!-- Page Header -->
        <div class="docs-page-header">
            <div class="row align-items-center">
                <div class="col-sm">
                    <h1 class="docs-page-header-title">Student Management</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Summary Cards -->
        <div class="row mb-4">
            <!-- Total Students Card -->
            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Total Students</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="card-title text-inherit" id="totalStudents">0</h2>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-soft-primary text-primary p-2">
                                    <i class="bi-people-fill"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Total Students Card -->

            <!-- Active Students Card -->
            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Active Students</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="card-title text-inherit" id="activeStudents">0</h2>
                                <span class="text-body fs-6" id="activePercentage">0%</span>
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
            <!-- End Active Students Card -->

            <!-- Suspended Students Card -->
            <div class="col-sm-6 col-lg-3 mb-3 mb-sm-0">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Suspended Students</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="card-title text-inherit" id="suspendedStudents">0</h2>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-soft-warning text-warning p-2">
                                    <i class="bi-pause-circle-fill"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Suspended Students Card -->

            <!-- Banned Students Card -->
            <div class="col-sm-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Banned Students</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="card-title text-inherit" id="bannedStudents">0</h2>
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
            <!-- End Banned Students Card -->
        </div>
        <!-- End Summary Cards -->

        <!-- Heading -->
        <h2 id="component-1" class="hs-docs-heading">
            Student Management <a class="anchorjs-link" href="#component-1" aria-label="Anchor" data-anchorjs-icon="#"></a>
        </h2>
        <!-- End Heading -->

        <!-- Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center mb-3">
                <h4 class="card-header-title">Student Users</h4>
                <div class="d-flex align-items-center">
                    <select id="statusFilter" class="form-select form-select-sm me-2">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                        <option value="banned">Banned</option>
                    </select>
                    <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="🔍 Search Name/Email...">
                </div>
            </div>

            <!-- Student Table -->
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th class="sortable" data-sort="name">Student <span class="sort-icon">⇅</span></th>
                            <th class="sortable" data-sort="status">Status <span class="sort-icon">⇅</span></th>
                            <th class="sortable" data-sort="courses_enrolled">Courses <span class="sort-icon">⇅</span></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="studentTableBody">
                        <!-- Data will be injected here -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
            <div class="d-flex justify-content-between align-items-center mt-3 px-3 pb-3">
                <button id="prevPage" class="btn btn-sm btn-outline-primary">Previous</button>
                <span id="paginationNumbers"></span>
                <button id="nextPage" class="btn btn-sm btn-outline-primary">Next</button>
            </div>

            <style>
                .sortable {
                    cursor: pointer;
                    user-select: none;
                    position: relative;
                    padding-right: 20px;
                }

                .sort-icon {
                    position: absolute;
                    right: 5px;
                    font-size: 14px;
                    color: gray;
                }

                .sort-icon.active {
                    color: var(--bs-primary);
                }

                .sort-icon.asc::after {
                    content: "↑";
                }

                .sort-icon.desc::after {
                    content: "↓";
                }

                #searchInput {
                    max-width: 200px;
                }

                .status-dropdown {
                    position: relative;
                    display: inline-block;
                }

                .status-dropdown-content {
                    display: none;
                    position: absolute;
                    background-color: #f9f9f9;
                    min-width: 160px;
                    box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
                    z-index: 1;
                    border-radius: 0.5rem;
                    padding: 0.5rem 0;
                }

                .status-dropdown:hover .status-dropdown-content {
                    display: block;
                }

                .status-action {
                    display: block;
                    padding: 0.5rem 1rem;
                    text-decoration: none;
                    color: #333;
                    transition: background-color 0.2s;
                }

                .status-action:hover {
                    background-color: #f1f1f1;
                }
            </style>

            <!-- Student Details Modal -->
            <div class="modal fade" id="studentModal" tabindex="-1" aria-labelledby="studentModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="studentModalLabel">Student Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Student Information -->
                            <div class="mb-4">
                                <h6>Student Information</h6>
                                <div class="d-flex align-items-center mb-3">
                                    <span class="avatar avatar-lg avatar-circle me-3 position-relative" id="modalStudentAvatar">
                                        <span class="avatar-initials">J</span>
                                    </span>
                                    <div>
                                        <h5 class="mb-0" id="modalStudentName">John Doe</h5>
                                        <p class="mb-0 text-muted" id="modalStudentEmail">johndoe@example.com</p>
                                        <small class="text-muted" id="modalStudentJoined">Joined: Jan 01, 2023</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Account Summary -->
                            <div class="row mb-4">
                                <!-- Courses Card -->
                                <div class="col-sm-6">
                                    <div class="card bg-soft-primary">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="card-subtitle">Courses</h6>
                                                    <h4 class="mb-0" id="modalCourseCount">0</h4>
                                                </div>
                                                <i class="bi-book fs-1 text-primary"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Completed Courses Card -->
                                <div class="col-sm-6">
                                    <div class="card bg-soft-success">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="card-subtitle">Completed</h6>
                                                    <h4 class="mb-0" id="modalCompletedCount">0</h4>
                                                </div>
                                                <i class="bi-check-circle fs-1 text-success"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabs Navigation -->
                            <ul class="nav nav-tabs" id="studentTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">Profile</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="courses-tab" data-bs-toggle="tab" data-bs-target="#courses" type="button" role="tab">Courses</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab">Activity Log</button>
                                </li>
                            </ul>

                            <!-- Tabs Content -->
                            <div class="tab-content p-3 border border-top-0 rounded-bottom" id="studentTabsContent">
                                <!-- Profile Tab -->
                                <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Personal Information</h6>
                                            <dl class="row">
                                                <dt class="col-sm-4">Full Name</dt>
                                                <dd class="col-sm-8" id="modalFullName">John Doe</dd>

                                                <dt class="col-sm-4">Email</dt>
                                                <dd class="col-sm-8" id="modalEmail">john@example.com</dd>

                                                <dt class="col-sm-4">Phone</dt>
                                                <dd class="col-sm-8" id="modalPhone">+1 (555) 123-4567</dd>

                                                <dt class="col-sm-4">Location</dt>
                                                <dd class="col-sm-8" id="modalLocation">New York, USA</dd>
                                            </dl>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Account Information</h6>
                                            <dl class="row">
                                                <dt class="col-sm-4">Status</dt>
                                                <dd class="col-sm-8"><span id="modalStatus" class="badge bg-success">Active</span></dd>

                                                <dt class="col-sm-4">Joined On</dt>
                                                <dd class="col-sm-8" id="modalJoinedDate">Jan 1, 2023</dd>

                                                <dt class="col-sm-4">Last Login</dt>
                                                <dd class="col-sm-8" id="modalLastLogin">2 hours ago</dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>

                                <!-- Courses Tab -->
                                <div class="tab-pane fade" id="courses" role="tabpanel" aria-labelledby="courses-tab">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Course</th>
                                                    <th>Instructor</th>
                                                    <th>Enrolled On</th>
                                                    <th>Progress</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="modalCoursesTable">
                                                <!-- Courses will be added here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Activity Log Tab -->
                                <div class="tab-pane fade" id="activity" role="tabpanel" aria-labelledby="activity-tab">
                                    <div class="timeline-step">
                                        <ul class="step" id="modalActivityLog">
                                            <!-- Activity logs will be added here -->
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Current Status Section -->
                            <div id="currentStatusSection" class="mt-4">
                                <h6>Current Status</h6>
                                <div id="modalCurrentStatus" class="badge bg-success">Active</div>
                                <div id="modalStatusReason" class="mt-2 d-none">
                                    <small class="text-muted">Reason for action:</small>
                                    <p class="mb-0 text-secondary"></p>
                                </div>
                            </div>

                            <!-- Admin Actions Section -->
                            <div id="adminActionSection" class="mt-4">
                                <h6>Status Action</h6>
                                <div class="d-flex flex-column">
                                    <div class="form-check mb-2" id="activeRadioContainer">
                                        <input class="form-check-input" type="radio" name="statusAction" id="activeRadio" value="active">
                                        <label class="form-check-label text-success" for="activeRadio">
                                            Set as Active
                                        </label>
                                    </div>
                                    <div class="form-check mb-2" id="suspendRadioContainer">
                                        <input class="form-check-input" type="radio" name="statusAction" id="suspendRadio" value="suspended">
                                        <label class="form-check-label text-warning" for="suspendRadio">
                                            Suspend Account
                                        </label>
                                    </div>
                                    <div class="form-check mb-3" id="banRadioContainer">
                                        <input class="form-check-input" type="radio" name="statusAction" id="banRadio" value="banned">
                                        <label class="form-check-label text-danger" for="banRadio">
                                            Ban Account
                                        </label>
                                    </div>
                                    <div id="actionReasonField" class="mb-3 d-none">
                                        <label for="actionReason" class="form-label">Reason for action</label>
                                        <textarea class="form-control" id="actionReason" rows="3" placeholder="Provide a reason for this action"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="submitStatusAction">Update Status</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Student Modal -->

            <script>
                // Toast Notification System
                function showToast(type, message) {
                    const toastElement = document.getElementById('liveToast');
                    const toastMessage = document.getElementById('toastMessage');
                    const toastTime = document.getElementById('toastTime');

                    // Set toast content - keeping title as "System Notification" regardless of type
                    toastMessage.textContent = message;
                    toastTime.textContent = "Just now";

                    // Show the toast
                    const toast = bootstrap.Toast.getOrCreateInstance(toastElement);
                    toast.show();
                }

                // Create and apply page overlay for loading effect with optional message
                function createOverlay(message = null) {
                    const overlay = document.createElement('div');
                    overlay.id = 'pageOverlay';
                    overlay.style.position = 'fixed';
                    overlay.style.top = '0';
                    overlay.style.left = '0';
                    overlay.style.width = '100%';
                    overlay.style.height = '100%';
                    overlay.style.backgroundColor = 'rgba(255, 255, 255, 0.7)';
                    overlay.style.backdropFilter = 'blur(5px)';
                    overlay.style.zIndex = '9998';
                    overlay.style.display = 'flex';
                    overlay.style.flexDirection = 'column';
                    overlay.style.justifyContent = 'center';
                    overlay.style.alignItems = 'center';
                    overlay.style.gap = '15px';

                    // Add a loading spinner
                    const spinner = document.createElement('div');
                    spinner.className = 'spinner-border text-primary';
                    spinner.setAttribute('role', 'status');
                    spinner.style.width = '3rem';
                    spinner.style.height = '3rem';
                    spinner.innerHTML = '<span class="visually-hidden">Loading...</span>';
                    overlay.appendChild(spinner);

                    // Add message if provided
                    if (message) {
                        const messageElement = document.createElement('div');
                        messageElement.className = 'fw-semibold fs-5 text-primary';
                        messageElement.textContent = message;
                        overlay.appendChild(messageElement);
                    }

                    document.body.appendChild(overlay);
                }

                // Remove overlay
                function removeOverlay() {
                    const overlay = document.getElementById('pageOverlay');
                    if (overlay) {
                        document.body.removeChild(overlay);
                    }
                }

                document.addEventListener("DOMContentLoaded", function() {
                    let studentData = [];
                    let filteredStudents = [];
                    let currentPage = 1;
                    let studentsPerPage = 10;
                    let sortColumn = "name";
                    let sortDirection = "asc";

                    function fetchStudentData() {
                        createOverlay("Loading student data...");
                        fetch("../backend/admin/fetch-students.php")
                            .then(response => response.json())
                            .then(data => {
                                studentData = data.map((student, index) => ({
                                    id: student.id,
                                    name: student.name,
                                    email: student.email,
                                    status: student.status,
                                    profile_pic: student.profile_pic,
                                    courses_enrolled: student.courses_enrolled || 0,
                                    courses_completed: student.courses_completed || 0,
                                    phone: student.phone || "N/A",
                                    location: student.location || "N/A",
                                    joinDate: student.created_at ? new Date(student.created_at) : null, // Default to null instead of new Date()
                                    lastLogin: student.last_login ? new Date(student.last_login) : null,
                                    coursesList: [],
                                    statusReason: student.status !== "active" ? "No reason provided" : null
                                }));

                                filteredStudents = [...studentData];
                                displayStudents();
                                createPagination();
                                updateSummaryCards();
                                removeOverlay();
                            })
                            .catch(error => {
                                console.error("Error fetching students:", error);
                                removeOverlay();
                                showToast('error', 'Failed to load student data');
                            });
                    }

                    function updateSummaryCards() {
                        const totalCount = studentData.length;
                        const activeCount = studentData.filter(student => student.status === "active").length;
                        const suspendedCount = studentData.filter(student => student.status === "suspended").length;
                        const bannedCount = studentData.filter(student => student.status === "banned").length;

                        document.getElementById("totalStudents").textContent = totalCount;
                        document.getElementById("activeStudents").textContent = activeCount;
                        document.getElementById("suspendedStudents").textContent = suspendedCount;
                        document.getElementById("bannedStudents").textContent = bannedCount;

                        const percentage = totalCount > 0 ? Math.round((activeCount / totalCount) * 100) : 0;
                        document.getElementById("activePercentage").textContent = `${percentage}%`;
                    }

                    function formatDate(date) {
                        if (!date || isNaN(new Date(date))) {
                            return "Not available"; // Better message when date is not available
                        }
                        const options = {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        };
                        return new Date(date).toLocaleDateString(undefined, options);
                    }

                    function timeAgo(date) {
                        if (!date || isNaN(new Date(date))) return "Never";

                        const seconds = Math.floor((new Date() - new Date(date)) / 1000);

                        let interval = Math.floor(seconds / 31536000);
                        if (interval > 1) return interval + " years ago";
                        if (interval === 1) return "1 year ago";

                        interval = Math.floor(seconds / 2592000);
                        if (interval > 1) return interval + " months ago";
                        if (interval === 1) return "1 month ago";

                        interval = Math.floor(seconds / 86400);
                        if (interval > 1) return interval + " days ago";
                        if (interval === 1) return "1 day ago";

                        interval = Math.floor(seconds / 3600);
                        if (interval > 1) return interval + " hours ago";
                        if (interval === 1) return "1 hour ago";

                        interval = Math.floor(seconds / 60);
                        if (interval > 1) return interval + " minutes ago";
                        if (interval === 1) return "1 minute ago";

                        return "Just now";
                    }

                    function displayStudents() {
                        let tableBody = document.getElementById("studentTableBody");
                        tableBody.innerHTML = "";

                        let startIndex = (currentPage - 1) * studentsPerPage;
                        let endIndex = startIndex + studentsPerPage;
                        let paginatedStudents = filteredStudents.slice(startIndex, endIndex);

                        if (paginatedStudents.length === 0) {
                            tableBody.innerHTML = `<tr><td colspan="4" class="text-center text-muted">No students found.</td></tr>`;
                            return;
                        }

                        paginatedStudents.forEach(student => {
                            let statusBadge = "";

                            switch (student.status) {
                                case "active":
                                    statusBadge = `<span class="badge bg-success">Active</span>`;
                                    break;
                                case "suspended":
                                    statusBadge = `<span class="badge bg-warning">Suspended</span>`;
                                    break;
                                case "banned":
                                    statusBadge = `<span class="badge bg-danger">Banned</span>`;
                                    break;
                            }

                            // Status dropdown with options based on current status
                            let statusDropdown = `
                            <div class="status-dropdown">
                                <button class="btn btn-sm btn-soft-secondary">
                                    <i class="bi-three-dots-vertical"></i>
                                </button>
                                <div class="status-dropdown-content">
                            `;

                            if (student.status !== "active") {
                                statusDropdown += `<a href="#" class="status-action change-status" data-id="${student.id}" data-status="active">Set as Active</a>`;
                            }
                            if (student.status !== "suspended") {
                                statusDropdown += `<a href="#" class="status-action change-status" data-id="${student.id}" data-status="suspended">Suspend Account</a>`;
                            }
                            if (student.status !== "banned") {
                                statusDropdown += `<a href="#" class="status-action change-status" data-id="${student.id}" data-status="banned">Ban Account</a>`;
                            }

                            statusDropdown += `
                                </div>
                            </div>
                            `;

                            tableBody.innerHTML += `
                            <tr id="row-${student.id}">
                                <td>
                                    <div class="d-flex align-items-center position-relative">
                                        <span class="avatar avatar-sm avatar-${student.status === 'active' ? 'success' : student.status === 'suspended' ? 'warning' : 'danger'} avatar-circle">
                                            ${student.profile_pic ?
                                                `<img src="../uploads/profile/${student.profile_pic}" alt="Profile Picture" class="avatar-img">` :
                                                `<span class="avatar-initials">${student.name.charAt(0)}</span>`
                                            }
                                        </span>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-0">${student.name}</h6>
                                            <small class="d-block text-muted">${student.email}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>${statusBadge}</td>
                                <td>${student.courses_enrolled}</td>
                                <td>
                                    <button class="btn btn-sm btn-soft-primary view-student" data-id="${student.id}">
                                        <i class="bi-eye"></i>
                                    </button>
                                    ${statusDropdown}
                                </td>
                            </tr>
                            `;
                        });

                        attachEventListeners();
                        updateButtons();
                        updateSortIcons();
                    }

                    function updateSortIcons() {
                        document.querySelectorAll('.sort-icon').forEach(icon => {
                            icon.className = 'sort-icon';
                            const parentTh = icon.closest('th');
                            if (parentTh && parentTh.dataset.sort === sortColumn) {
                                icon.classList.add('active', sortDirection);
                            }
                        });
                    }

                    function attachEventListeners() {
                        // View student details
                        document.querySelectorAll(".view-student").forEach(button => {
                            button.addEventListener("click", function() {
                                let studentId = this.getAttribute("data-id");
                                viewStudentDetails(studentId);
                            });
                        });

                        // Change status
                        document.querySelectorAll(".change-status").forEach(link => {
                            link.addEventListener("click", function(e) {
                                e.preventDefault();
                                let studentId = this.getAttribute("data-id");
                                let newStatus = this.getAttribute("data-status");

                                viewStudentDetails(studentId);

                                // Pre-select the appropriate radio button
                                if (newStatus === "active") {
                                    document.getElementById("activeRadio").checked = true;
                                } else if (newStatus === "suspended") {
                                    document.getElementById("suspendRadio").checked = true;
                                    document.getElementById("actionReasonField").classList.remove("d-none");
                                } else if (newStatus === "banned") {
                                    document.getElementById("banRadio").checked = true;
                                    document.getElementById("actionReasonField").classList.remove("d-none");
                                }
                            });
                        });
                    }

                    function viewStudentDetails(studentId) {
                        let student = studentData.find(s => s.id == studentId);

                        if (!student) return;

                        // Set student basic info
                        document.getElementById("modalStudentName").textContent = student.name;
                        document.getElementById("modalStudentEmail").textContent = student.email;
                        document.getElementById("modalStudentJoined").textContent = "Joined: " + formatDate(student.joinDate);

                        // Set avatar
                        const avatarElement = document.getElementById("modalStudentAvatar");
                        avatarElement.className = `avatar avatar-lg avatar-${student.status === 'active' ? 'success' : student.status === 'suspended' ? 'warning' : 'danger'} avatar-circle me-3 position-relative`;

                        if (student.profile_pic) {
                            avatarElement.innerHTML = `<img src="../uploads/profile/${student.profile_pic}" alt="Profile Picture" class="avatar-img">`;
                        } else {
                            avatarElement.innerHTML = `<span class="avatar-initials">${student.name.charAt(0)}</span>`;
                        }

                        // Set summary cards data
                        document.getElementById("modalCourseCount").textContent = student.courses_enrolled;
                        document.getElementById("modalCompletedCount").textContent = student.courses_completed;

                        // Set profile tab data
                        document.getElementById("modalFullName").textContent = student.name;
                        document.getElementById("modalEmail").textContent = student.email;
                        document.getElementById("modalPhone").textContent = student.phone;
                        document.getElementById("modalLocation").textContent = student.location;

                        // Set status badge
                        const statusBadge = document.getElementById("modalStatus");
                        statusBadge.className = "badge";
                        switch (student.status) {
                            case "active":
                                statusBadge.classList.add("bg-success");
                                statusBadge.textContent = "Active";
                                break;
                            case "suspended":
                                statusBadge.classList.add("bg-warning");
                                statusBadge.textContent = "Suspended";
                                break;
                            case "banned":
                                statusBadge.classList.add("bg-danger");
                                statusBadge.textContent = "Banned";
                                break;
                        }

                        document.getElementById("modalJoinedDate").textContent = formatDate(student.joinDate);
                        document.getElementById("modalLastLogin").textContent = timeAgo(student.lastLogin);

                        // Populate courses tab
                        const coursesTable = document.getElementById("modalCoursesTable");
                        coursesTable.innerHTML = "";

                        if (student.coursesList && student.coursesList.length > 0) {
                            student.coursesList.forEach(course => {
                                coursesTable.innerHTML += `
                                <tr>
                                    <td>${course.title}</td>
                                    <td>${course.instructor}</td>
                                    <td>${formatDate(course.enrolled_date)}</td>
                                    <td>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar" role="progressbar" style="width: ${course.progress}%" aria-valuenow="${course.progress}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <small class="text-muted">${course.progress}% complete</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-${course.status === 'completed' ? 'success' : 'primary'}">
                                            ${course.status === 'completed' ? 'Completed' : 'In Progress'}
                                        </span>
                                    </td>
                                </tr>
                                `;
                            });
                        } else {
                            coursesTable.innerHTML = `<tr><td colspan="5" class="text-center text-muted">No courses found</td></tr>`;
                        }

                        // Set empty tables for other tabs (would be populated with real data in a production app)
                        document.getElementById("modalActivityLog").innerHTML = `<li class="step-item"><div class="text-muted">No activity logs available</div></li>`;

                        // Show status reason if exists
                        const reasonSection = document.getElementById("modalStatusReason");
                        if (student.status !== "active" && student.statusReason) {
                            reasonSection.classList.remove("d-none");
                            reasonSection.querySelector("p").textContent = student.statusReason;
                        } else {
                            reasonSection.classList.add("d-none");
                        }

                        // Set current status
                        const currentStatusBadge = document.getElementById("modalCurrentStatus");
                        currentStatusBadge.className = "badge";
                        switch (student.status) {
                            case "active":
                                currentStatusBadge.classList.add("bg-success");
                                currentStatusBadge.textContent = "Active";
                                break;
                            case "suspended":
                                currentStatusBadge.classList.add("bg-warning");
                                currentStatusBadge.textContent = "Suspended";
                                break;
                            case "banned":
                                currentStatusBadge.classList.add("bg-danger");
                                currentStatusBadge.textContent = "Banned";
                                break;
                        }

                        // Hide the radio button for the current status in admin action section
                        document.getElementById("activeRadioContainer").style.display = student.status === "active" ? "none" : "block";
                        document.getElementById("suspendRadioContainer").style.display = student.status === "suspended" ? "none" : "block";
                        document.getElementById("banRadioContainer").style.display = student.status === "banned" ? "none" : "block";

                        // Reset radio buttons and textarea
                        document.getElementById("activeRadio").checked = false;
                        document.getElementById("suspendRadio").checked = false;
                        document.getElementById("banRadio").checked = false;
                        document.getElementById("actionReasonField").classList.add("d-none");
                        document.getElementById("actionReason").value = "";

                        // Store student ID on submit button
                        document.getElementById("submitStatusAction").setAttribute("data-id", studentId);

                        // Show the modal
                        const studentModal = new bootstrap.Modal(document.getElementById("studentModal"));
                        studentModal.show();

                        // Fetch and display activity logs, including status changes
                        fetch(`../backend/admin/fetch-student-activity.php?student_id=${studentId}`)
                            .then(response => response.json())
                            .then(data => {
                                const activityLogContainer = document.getElementById("modalActivityLog");

                                if (data.logs && data.logs.length > 0) {
                                    activityLogContainer.innerHTML = "";

                                    data.logs.forEach(log => {
                                        let activityIcon = '';
                                        let activityClass = '';

                                        // Set appropriate icon and class based on activity type
                                        if (log.activity_type === 'status_change') {
                                            if (log.new_status === 'active') {
                                                activityIcon = 'bi-check-circle-fill';
                                                activityClass = 'text-success';
                                            } else if (log.new_status === 'suspended') {
                                                activityIcon = 'bi-pause-circle-fill';
                                                activityClass = 'text-warning';
                                            } else if (log.new_status === 'banned') {
                                                activityIcon = 'bi-x-circle-fill';
                                                activityClass = 'text-danger';
                                            }
                                        } else if (log.activity_type === 'login') {
                                            activityIcon = 'bi-box-arrow-in-right';
                                            activityClass = 'text-primary';
                                        } else {
                                            activityIcon = 'bi-activity';
                                            activityClass = 'text-secondary';
                                        }

                                        activityLogContainer.innerHTML += `
                <li class="step-item">
                    <div class="step-content-wrapper">
                        <span class="step-icon step-icon-sm ${activityClass}">
                            <i class="${activityIcon}"></i>
                        </span>
                        <div class="step-content">
                            <h5 class="mb-1">${log.activity_title}</h5>
                            <p class="fs-6 mb-1">${log.activity_details}</p>
                            <small class="text-muted">${timeAgo(log.created_at)}</small>
                        </div>
                    </div>
                </li>`;
                                    });
                                } else {
                                    activityLogContainer.innerHTML = `<li class="step-item"><div class="text-muted">No activity logs available</div></li>`;
                                }
                            })
                            .catch(error => {
                                console.error("Error fetching activity logs:", error);
                                document.getElementById("modalActivityLog").innerHTML = `<li class="step-item"><div class="text-muted">Error loading activity logs</div></li>`;
                            });
                    }

                    function updateStudentStatus(studentId, newStatus, statusReason = null) {
                        createOverlay("Updating student status...");

                        // Prepare data for API call
                        const requestData = {
                            student_id: studentId,
                            status: newStatus
                        };

                        // Add reason if provided for suspended or banned status
                        if (newStatus !== "active" && statusReason) {
                            requestData.reason = statusReason;
                        }

                        // Make API call to update status
                        fetch("../backend/admin/update-student-status.php", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json"
                                },
                                body: JSON.stringify(requestData)
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Find and update student in local data
                                    let student = studentData.find(s => s.id == studentId);

                                    if (student) {
                                        student.status = newStatus;

                                        if (newStatus !== "active" && statusReason) {
                                            student.statusReason = statusReason;
                                        }

                                        // Update the UI
                                        applyFilters();
                                        updateSummaryCards();
                                        showToast('success', data.message || `Student status updated to ${newStatus} successfully.`);
                                    }
                                } else {
                                    showToast('error', data.message || 'Failed to update student status');
                                }
                                removeOverlay();
                            })
                            .catch(error => {
                                console.error("Error updating student status:", error);
                                showToast('error', 'An error occurred while updating student status');
                                removeOverlay();
                            });
                    }

                    function createPagination() {
                        let paginationNumbers = document.getElementById("paginationNumbers");
                        paginationNumbers.innerHTML = "";

                        let totalPages = Math.ceil(filteredStudents.length / studentsPerPage);
                        for (let i = 1; i <= totalPages; i++) {
                            let pageButton = document.createElement("button");
                            pageButton.className = `btn btn-sm ${i === currentPage ? 'btn-primary' : 'btn-outline-primary'} mx-1`;
                            pageButton.innerText = i;
                            pageButton.dataset.page = i;
                            pageButton.onclick = function() {
                                const pageNum = parseInt(this.dataset.page);
                                currentPage = pageNum;

                                // Update active button
                                document.querySelectorAll("#paginationNumbers button").forEach(btn => {
                                    if (parseInt(btn.dataset.page) === pageNum) {
                                        btn.classList.remove('btn-outline-primary');
                                        btn.classList.add('btn-primary');
                                    } else {
                                        btn.classList.remove('btn-primary');
                                        btn.classList.add('btn-outline-primary');
                                    }
                                });

                                displayStudents();
                            };
                            paginationNumbers.appendChild(pageButton);
                        }
                    }

                    function updateButtons() {
                        document.getElementById("prevPage").disabled = (currentPage === 1);
                        document.getElementById("nextPage").disabled = (currentPage === Math.ceil(filteredStudents.length / studentsPerPage));
                    }

                    function applyFilters() {
                        const searchQuery = document.getElementById("searchInput").value.toLowerCase();
                        const statusFilter = document.getElementById("statusFilter").value;

                        filteredStudents = studentData.filter(student => {
                            const matchesSearch = student.name.toLowerCase().includes(searchQuery) ||
                                student.email.toLowerCase().includes(searchQuery);

                            const matchesStatus = statusFilter === "all" || student.status === statusFilter;

                            return matchesSearch && matchesStatus;
                        });

                        // Sort the students
                        sortStudents();

                        currentPage = 1;
                        createPagination();
                        displayStudents();
                    }

                    function sortStudents() {
                        filteredStudents.sort((a, b) => {
                            let valA = a[sortColumn];
                            let valB = b[sortColumn];

                            if (sortColumn === "name" || sortColumn === "email") {
                                return sortDirection === "asc" ?
                                    valA.localeCompare(valB) :
                                    valB.localeCompare(valA);
                            } else if (sortColumn === "courses_enrolled") {
                                return sortDirection === "asc" ?
                                    valA - valB :
                                    valB - valA;
                            } else if (sortColumn === "status") {
                                const order = {
                                    "active": 1,
                                    "suspended": 2,
                                    "banned": 3
                                };

                                return sortDirection === "asc" ?
                                    order[valA] - order[valB] :
                                    order[valB] - order[valA];
                            }

                            return 0;
                        });
                    }

                    // Event Listeners
                    document.getElementById("prevPage").addEventListener("click", function() {
                        if (currentPage > 1) {
                            currentPage--;

                            // Update active button
                            document.querySelectorAll("#paginationNumbers button").forEach(btn => {
                                const pageNum = parseInt(btn.dataset.page);
                                if (pageNum === currentPage) {
                                    btn.classList.remove('btn-outline-primary');
                                    btn.classList.add('btn-primary');
                                } else {
                                    btn.classList.remove('btn-primary');
                                    btn.classList.add('btn-outline-primary');
                                }
                            });

                            displayStudents();
                        }
                    });

                    document.getElementById("nextPage").addEventListener("click", function() {
                        if (currentPage < Math.ceil(filteredStudents.length / studentsPerPage)) {
                            currentPage++;

                            // Update active button
                            document.querySelectorAll("#paginationNumbers button").forEach(btn => {
                                const pageNum = parseInt(btn.dataset.page);
                                if (pageNum === currentPage) {
                                    btn.classList.remove('btn-outline-primary');
                                    btn.classList.add('btn-primary');
                                } else {
                                    btn.classList.remove('btn-primary');
                                    btn.classList.add('btn-outline-primary');
                                }
                            });

                            displayStudents();
                        }
                    });

                    // Status filter change
                    document.getElementById("statusFilter").addEventListener("change", function() {
                        applyFilters();
                    });

                    // Search input
                    document.getElementById("searchInput").addEventListener("input", function() {
                        applyFilters();
                    });

                    // Radio buttons for action reason field
                    document.getElementById("activeRadio").addEventListener("change", function() {
                        if (this.checked) {
                            document.getElementById("actionReasonField").classList.add("d-none");
                        }
                    });

                    document.getElementById("suspendRadio").addEventListener("change", function() {
                        if (this.checked) {
                            document.getElementById("actionReasonField").classList.remove("d-none");
                        }
                    });

                    document.getElementById("banRadio").addEventListener("change", function() {
                        if (this.checked) {
                            document.getElementById("actionReasonField").classList.remove("d-none");
                        }
                    });

                    // Submit status action
                    document.getElementById("submitStatusAction").addEventListener("click", function() {
                        const studentId = this.getAttribute("data-id");
                        const activeRadio = document.getElementById("activeRadio");
                        const suspendRadio = document.getElementById("suspendRadio");
                        const banRadio = document.getElementById("banRadio");

                        if (!activeRadio.checked && !suspendRadio.checked && !banRadio.checked) {
                            showToast('error', 'Please select an action (Active, Suspend, or Ban)');
                            return;
                        }

                        let newStatus = "";
                        if (activeRadio.checked) {
                            newStatus = "active";
                            updateStudentStatus(studentId, newStatus);
                        } else {
                            if (suspendRadio.checked) {
                                newStatus = "suspended";
                            } else if (banRadio.checked) {
                                newStatus = "banned";
                            }

                            const actionReason = document.getElementById("actionReason").value.trim();

                            if (!actionReason) {
                                showToast('error', 'Please provide a reason for this action');
                                return;
                            }

                            updateStudentStatus(studentId, newStatus, actionReason);
                        }

                        // Close the modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById("studentModal"));
                        modal.hide();
                    });

                    // Sorting Functionality
                    document.querySelectorAll(".sortable").forEach(header => {
                        header.addEventListener("click", function() {
                            let column = this.dataset.sort;

                            if (sortColumn === column) {
                                sortDirection = sortDirection === "asc" ? "desc" : "asc";
                            } else {
                                sortColumn = column;
                                sortDirection = "asc";
                            }

                            // Sort and display
                            sortStudents();
                            displayStudents();
                        });
                    });

                    // Initialize
                    fetchStudentData();
                });
            </script>
            <!-- End Student Table -->
        </div>
        <!-- End Card -->
    </div>
    <!-- End Content -->

</main>
<!-- ========== END MAIN CONTENT ========== -->

<?php include '../includes/admin-footer.php'; ?>