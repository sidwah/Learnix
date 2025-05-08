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
        <div class="docs-page-header">
            <div class="row align-items-center">
                <div class="col-sm">
                    <h1 class="docs-page-header-title">Instructor Management</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Summary Cards -->
        <div class="row mb-4">
            <!-- Total Instructors Card -->
            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Total Instructors</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="card-title text-inherit" id="totalInstructors">0</h2>
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
            <!-- End Total Instructors Card -->

            <!-- Active Instructors Card -->
            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Active Instructors</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="card-title text-inherit" id="activeInstructors">0</h2>
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
            <!-- End Active Instructors Card -->

            <!-- Suspended Instructors Card -->
            <div class="col-sm-6 col-lg-3 mb-3 mb-sm-0">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Suspended Instructors</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="card-title text-inherit" id="suspendedInstructors">0</h2>
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
            <!-- End Suspended Instructors Card -->

            <!-- Banned Instructors Card -->
            <div class="col-sm-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Deleted Instructors</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="card-title text-inherit" id="bannedInstructors">0</h2>
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
            <!-- End Banned Instructors Card -->
        </div>
        <!-- End Summary Cards -->

        <!-- Heading -->
        <h2 id="component-1" class="hs-docs-heading">
            Instructor Management <a class="anchorjs-link" href="#component-1" aria-label="Anchor" data-anchorjs-icon="#"></a>
        </h2>
        <!-- End Heading -->

        <!-- Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center mb-3">
                <h4 class="card-header-title">Instructor Users</h4>
                <div class="d-flex align-items-center">
                    <select id="statusFilter" class="form-select form-select-sm me-2">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                        <option value="banned">Deleted</option>
                    </select>
                    <select id="verifiedFilter" class="form-select form-select-sm me-2">
                        <option value="all">All Verification</option>
                        <option value="verified">Verified</option>
                        <option value="unverified">Unverified</option>
                    </select>
                    <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="🔍 Search Name/Email...">
                </div>
            </div>

            <!-- Instructor Table -->
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th class="sortable" data-sort="name">Instructor <span class="sort-icon">⇅</span></th>
                            <th class="sortable" data-sort="status">Status <span class="sort-icon">⇅</span></th>
                            <th class="sortable" data-sort="courses">Courses <span class="sort-icon">⇅</span></th>
                            <th class="sortable" data-sort="students">Students <span class="sort-icon">⇅</span></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="instructorTableBody">
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
                }

                .sort-icon {
                    font-size: 14px;
                    margin-left: 5px;
                    color: gray;
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

                .avatar img.avatar-verified {
                    position: absolute;
                    bottom: -3px;
                    right: -3px;
                    width: 15px;
                    height: 15px;
                }
            </style>

            <!-- Instructor Details Modal -->
            <div class="modal fade" id="instructorModal" tabindex="-1" aria-labelledby="instructorModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="instructorModalLabel">Instructor Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Instructor Information -->
                            <div class="mb-4">
                                <h6>Instructor Information</h6>
                                <div class="d-flex align-items-center mb-3">
                                    <span class="avatar avatar-lg avatar-circle me-3 position-relative" id="modalInstructorAvatar">
                                        <span class="avatar-initials">J</span>
                                        <span id="modalVerifiedBadge" class="d-none">
                                            <img class="avatar-verified" src="../assets/svg/illustrations/top-vendor.svg" alt="Verified" data-bs-toggle="tooltip" data-bs-placement="top" title="Verified instructor">
                                        </span>
                                    </span>
                                    <div>
                                        <h5 class="mb-0" id="modalInstructorName">John Doe</h5>
                                        <p class="mb-0 text-muted" id="modalInstructorEmail">johndoe@example.com</p>
                                        <small class="text-muted" id="modalInstructorJoined">Joined: Jan 01, 2023</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Account Summary -->
                            <div class="row mb-4">
                                <!-- Courses Card -->
                                <div class="col-sm-4">
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

                                <!-- Students Card -->
                                <div class="col-sm-4">
                                    <div class="card bg-soft-success">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="card-subtitle">Students</h6>
                                                    <h4 class="mb-0" id="modalStudentCount">0</h4>
                                                </div>
                                                <i class="bi-people fs-1 text-success"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Revenue Card -->
                                <div class="col-sm-4">
                                    <div class="card bg-soft-info">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="card-subtitle">Revenue</h6>
                                                    <h4 class="mb-0" id="modalRevenue">₵0</h4>
                                                </div>
                                                <i class="bi-currency-dollar fs-1 text-info"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabs Navigation -->
                            <ul class="nav nav-tabs" id="instructorTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">Profile</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="courses-tab" data-bs-toggle="tab" data-bs-target="#courses" type="button" role="tab">Courses</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="students-tab" data-bs-toggle="tab" data-bs-target="#students" type="button" role="tab">Students</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments" type="button" role="tab">Payments</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab">Activity Log</button>
                                </li>
                            </ul>

                            <!-- Tabs Content -->
                            <div class="tab-content p-3 border border-top-0 rounded-bottom" id="instructorTabsContent">
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

                                                <dt class="col-sm-4">Verification</dt>
                                                <dd class="col-sm-8" id="modalVerificationStatus">
                                                    <span class="badge bg-success">Verified</span>
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>

                                    <h6 class="mt-4">Biography</h6>
                                    <p id="modalBio" class="text-muted">Instructor biography will appear here.</p>

                                    <h6 class="mt-4">Specializations</h6>
                                    <div id="modalSpecializations">
                                        <span class="badge bg-soft-primary text-primary me-2 mb-2">Web Development</span>
                                        <span class="badge bg-soft-primary text-primary me-2 mb-2">JavaScript</span>
                                        <span class="badge bg-soft-primary text-primary me-2 mb-2">React</span>
                                    </div>
                                </div>

                                <!-- Courses Tab -->
                                <div class="tab-pane fade" id="courses" role="tabpanel" aria-labelledby="courses-tab">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Course</th>
                                                    <th>Students</th>
                                                    <th>Rating</th>
                                                    <th>Price</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="modalCoursesTable">
                                                <!-- Courses will be added here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Students Tab -->
                                <div class="tab-pane fade" id="students" role="tabpanel" aria-labelledby="students-tab">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Student</th>
                                                    <th>Course</th>
                                                    <th>Enrolled On</th>
                                                    <th>Progress</th>
                                                </tr>
                                            </thead>
                                            <tbody id="modalStudentsTable">
                                                <!-- Students will be added here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Payments Tab -->
                                <div class="tab-pane fade" id="payments" role="tabpanel" aria-labelledby="payments-tab">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Amount</th>
                                                    <th>Course</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="modalPaymentsTable">
                                                <!-- Payments will be added here -->
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
                                            Delete Account
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
            <!-- End Instructor Modal -->

            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    let instructorData = [];
                    let filteredInstructors = [];
                    let currentPage = 1;
                    let instructorsPerPage = 10;
                    let sortColumn = "name";
                    let sortDirection = "asc";

                    function fetchInstructorData() {
                        fetch("../backend/admin/fetch-instructors.php")
                            .then(response => response.json())
                            .then(data => {
                                instructorData = data.map((instructor) => ({
                                    id: instructor.id,
                                    name: instructor.name,
                                    email: instructor.email,
                                    status: instructor.status,
                                    profile_pic: instructor.profile_pic,
                                    isVerified: instructor.verification_status === 'verified',
                                    courses: parseInt(instructor.courses_count) || 0,
                                    students: parseInt(instructor.students_count) || 0,
                                    revenue: parseFloat(instructor.total_revenue) || 0,
                                    phone: instructor.phone || "N/A",
                                    location: instructor.location || "N/A",
                                    joinDate: new Date(instructor.join_date),
                                    bio: instructor.bio || "Instructor bio not available.",
                                    specializations: instructor.specializations || [],
                                    coursesList: instructor.courses_list || [],
                                    statusReason: instructor.status_reason || (instructor.status !== "active" ? "No reason provided" : null)
                                }));

                                filteredInstructors = [...instructorData];
                                displayInstructors();
                                createPagination();
                                updateSummaryCards();
                            })
                            .catch(error => console.error("Error fetching instructors:", error));
                    }

                    function updateSummaryCards() {
                        const totalCount = instructorData.length;
                        const activeCount = instructorData.filter(inst => inst.status === "active").length;
                        const suspendedCount = instructorData.filter(inst => inst.status === "suspended").length;
                        const bannedCount = instructorData.filter(inst => inst.status === "banned").length;

                        document.getElementById("totalInstructors").textContent = totalCount;
                        document.getElementById("activeInstructors").textContent = activeCount;
                        document.getElementById("suspendedInstructors").textContent = suspendedCount;
                        document.getElementById("bannedInstructors").textContent = bannedCount;

                        const percentage = totalCount > 0 ? Math.round((activeCount / totalCount) * 100) : 0;
                        document.getElementById("activePercentage").textContent = `${percentage}%`;
                    }

                    function formatDate(date) {
                        const options = {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        };
                        return new Date(date).toLocaleDateString(undefined, options);
                    }

                    function timeAgo(date) {
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

                    function displayInstructors() {
                        let tableBody = document.getElementById("instructorTableBody");
                        tableBody.innerHTML = "";

                        let startIndex = (currentPage - 1) * instructorsPerPage;
                        let endIndex = startIndex + instructorsPerPage;
                        let paginatedInstructors = filteredInstructors.slice(startIndex, endIndex);

                        if (paginatedInstructors.length === 0) {
                            tableBody.innerHTML = `<tr><td colspan="6" class="text-center text-muted">No instructors found.</td></tr>`;
                            return;
                        }

                        paginatedInstructors.forEach(instructor => {
                            let statusBadge = "";

                            switch (instructor.status) {
                                case "active":
                                    statusBadge = `<span class="badge bg-success">Active</span>`;
                                    break;
                                case "suspended":
                                    statusBadge = `<span class="badge bg-warning">Suspended</span>`;
                                    break;
                                case "banned":
                                    statusBadge = `<span class="badge bg-danger">Deleted</span>`;
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

                            if (instructor.status !== "active") {
                                statusDropdown += `<a href="#" class="status-action change-status" data-id="${instructor.id}" data-status="active">Set as Active</a>`;
                            }
                            if (instructor.status !== "suspended") {
                                statusDropdown += `<a href="#" class="status-action change-status" data-id="${instructor.id}" data-status="suspended">Suspend Account</a>`;
                            }
                            if (instructor.status !== "banned") {
                                statusDropdown += `<a href="#" class="status-action change-status" data-id="${instructor.id}" data-status="banned">Delete Account</a>`;
                            }

                            statusDropdown += `
                                </div>
                            </div>
                        `;

                            const verifiedBadge = instructor.isVerified ?
                                `<img class="avatar avatar-xss ms-1" src="../assets/svg/illustrations/top-vendor.svg" alt="Verified" data-bs-toggle="tooltip" data-bs-placement="top" title="Verified instructor">` : '';

                            tableBody.innerHTML += `
                            <tr id="row-${instructor.id}">
                                <td>
                                    <div class="d-flex align-items-center position-relative">
                                        <span class="avatar avatar-sm avatar-${instructor.status === 'active' ? 'success' : instructor.status === 'suspended' ? 'warning' : 'danger'} avatar-circle">
                                            ${instructor.profile_pic ?
                                                `<img src="../uploads/instructor-profile/${instructor.profile_pic}" alt="Profile Picture" class="avatar-img">` :
                                                `<span class="avatar-initials">${instructor.name.charAt(0)}</span>`
                                            }
                                            ${instructor.isVerified ? 
                                                `<img class="avatar-verified" src="../assets/svg/illustrations/top-vendor.svg" alt="Verified">` : 
                                                ''}
                                        </span>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-0">${instructor.name} ${verifiedBadge}</h6>
                                            <small class="d-block text-muted">${instructor.email}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>${statusBadge}</td>
                                <td>${instructor.courses}</td>
                                <td>${instructor.students}</td>
                                <td>
                                    <button class="btn btn-sm btn-soft-primary view-instructor" data-id="${instructor.id}">
                                        <i class="bi-eye"></i>
                                    </button>
                                    ${statusDropdown}
                                </td>
                            </tr>
                        `;
                        });

                        attachEventListeners();
                        updateButtons();
                    }

                    function attachEventListeners() {
                        // View instructor details
                        document.querySelectorAll(".view-instructor").forEach(button => {
                            button.addEventListener("click", function() {
                                let instructorId = this.getAttribute("data-id");
                                viewInstructorDetails(instructorId);
                            });
                        });

                        // Change status
                        document.querySelectorAll(".change-status").forEach(link => {
                            link.addEventListener("click", function(e) {
                                e.preventDefault();
                                let instructorId = this.getAttribute("data-id");
                                let newStatus = this.getAttribute("data-status");

                                viewInstructorDetails(instructorId);

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

                    function viewInstructorDetails(instructorId) {
                        let instructor = instructorData.find(inst => inst.id == instructorId);

                        if (!instructor) return;

                        // Set instructor basic info
                        document.getElementById("modalInstructorName").textContent = instructor.name;
                        document.getElementById("modalInstructorEmail").textContent = instructor.email;
                        document.getElementById("modalInstructorJoined").textContent = "Joined: " + formatDate(instructor.joinDate);

                        // Set avatar
                        const avatarElement = document.getElementById("modalInstructorAvatar");
                        avatarElement.className = `avatar avatar-lg avatar-${instructor.status === 'active' ? 'success' : instructor.status === 'suspended' ? 'warning' : 'danger'} avatar-circle me-3 position-relative`;

                        if (instructor.profile_pic) {
                            avatarElement.innerHTML = `<img src="../uploads/instructor-profile/${instructor.profile_pic}" alt="Profile Picture" class="avatar-img">`;
                        } else {
                            avatarElement.innerHTML = `<span class="avatar-initials">${instructor.name.charAt(0)}</span>`;
                        }

                        // Show verified badge if applicable
                        // const verifiedBadge = document.getElementById("modalVerifiedBadge");
                        // console.log("Is Verified:", instructor.isVerified); 
                        // console.log("Is Verified:", verifiedBadge); // Should log `true` for verified instructors
                        // if (instructor.isVerified) {
                        //     verifiedBadge.classList.remove("d-none");
                        // } else {
                        //     verifiedBadge.classList.add("d-none");
                        // }

                        // Set summary cards data
                        document.getElementById("modalCourseCount").textContent = instructor.courses;
                        document.getElementById("modalStudentCount").textContent = instructor.students;
                        document.getElementById("modalRevenue").textContent = `₵${instructor.revenue.toLocaleString()}`;

                        // Set profile tab data
                        document.getElementById("modalFullName").textContent = instructor.name;
                        document.getElementById("modalEmail").textContent = instructor.email;
                        document.getElementById("modalPhone").textContent = instructor.phone;
                        document.getElementById("modalLocation").textContent = instructor.location;

                        // Set status badge
                        const statusBadge = document.getElementById("modalStatus");
                        statusBadge.className = "badge";
                        switch (instructor.status) {
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

                        document.getElementById("modalJoinedDate").textContent = formatDate(instructor.joinDate);

                        // Set verification status
                        const verificationStatus = document.getElementById("modalVerificationStatus");
                        if (instructor.isVerified) {
                            verificationStatus.innerHTML = `<span class="badge bg-success">Verified</span>`;
                        } else {
                            verificationStatus.innerHTML = `<span class="badge bg-secondary">Unverified</span>`;
                        }

                        // Set bio and specializations
                        document.getElementById("modalBio").textContent = instructor.bio;

                        const specializationsContainer = document.getElementById("modalSpecializations");
                        specializationsContainer.innerHTML = "";
                        instructor.specializations.forEach(spec => {
                            specializationsContainer.innerHTML += `
                            <span class="badge bg-soft-primary text-primary me-2 mb-2">${spec}</span>
                        `;
                        });

                        // Populate courses tab
                        const coursesTable = document.getElementById("modalCoursesTable");
                        coursesTable.innerHTML = "";

                        if (instructor.coursesList && instructor.coursesList.length > 0) {
                            instructor.coursesList.forEach(course => {
                                coursesTable.innerHTML += `
                                <tr>
                                    <td>${course.title}</td>
                                    <td>${course.students}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span>${course.rating}</span>
                                            <div class="ms-2 text-warning">
                                                ${'★'.repeat(Math.round(course.rating))}${'☆'.repeat(5 - Math.round(course.rating))}
                                            </div>
                                        </div>
                                    </td>
                                    <td>₵${course.price}</td>
                                    <td>
                                        <span class="badge bg-${course.status === 'published' ? 'success' : 'secondary'}">
                                            ${course.status === 'published' ? 'Published' : 'Draft'}
                                        </span>
                                    </td>
                                </tr>
                            `;
                            });
                        } else {
                            coursesTable.innerHTML = `<tr><td colspan="5" class="text-center text-muted">No courses found</td></tr>`;
                        }

                        // Set empty tables for other tabs (would be populated with real data in a production app)
                        document.getElementById("modalStudentsTable").innerHTML = `<tr><td colspan="4" class="text-center text-muted">No student data available</td></tr>`;
                        document.getElementById("modalPaymentsTable").innerHTML = `<tr><td colspan="4" class="text-center text-muted">No payment data available</td></tr>`;
                        document.getElementById("modalActivityLog").innerHTML = `<li class="step-item"><div class="text-muted">No activity logs available</div></li>`;

                        // Show status reason if exists
                        const reasonSection = document.getElementById("modalStatusReason");
                        if (instructor.status !== "active" && instructor.statusReason) {
                            reasonSection.classList.remove("d-none");
                            reasonSection.querySelector("p").textContent = instructor.statusReason;
                        } else {
                            reasonSection.classList.add("d-none");
                        }

                        // Set current status
                        const currentStatusBadge = document.getElementById("modalCurrentStatus");
                        currentStatusBadge.className = "badge";
                        switch (instructor.status) {
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
                        document.getElementById("activeRadioContainer").style.display = instructor.status === "active" ? "none" : "block";
                        document.getElementById("suspendRadioContainer").style.display = instructor.status === "suspended" ? "none" : "block";
                        document.getElementById("banRadioContainer").style.display = instructor.status === "banned" ? "none" : "block";

                        // Reset radio buttons and textarea
                        document.getElementById("activeRadio").checked = false;
                        document.getElementById("suspendRadio").checked = false;
                        document.getElementById("banRadio").checked = false;
                        document.getElementById("actionReasonField").classList.add("d-none");
                        document.getElementById("actionReason").value = "";

                        // Store instructor ID on submit button
                        document.getElementById("submitStatusAction").setAttribute("data-id", instructorId);

                        // Show the modal
                        const instructorModal = new bootstrap.Modal(document.getElementById("instructorModal"));
                        instructorModal.show();
                    }

                    function updateInstructorStatus(instructorId, newStatus, statusReason = null) {
                        // In a real app, this would be an API call
                        // For demonstration, we'll update the local data
                        let instructor = instructorData.find(inst => inst.id == instructorId);

                        if (instructor) {
                            instructor.status = newStatus;

                            if (newStatus !== "active" && statusReason) {
                                instructor.statusReason = statusReason;
                            }

                            // Update the UI
                            applyFilters();
                            updateSummaryCards();

                            // Show success message
                            alert(`Instructor status updated to ${newStatus} successfully.`);
                        }
                    }

                    function createPagination() {
                        let paginationNumbers = document.getElementById("paginationNumbers");
                        paginationNumbers.innerHTML = "";

                        let totalPages = Math.ceil(filteredInstructors.length / instructorsPerPage);
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

                                displayInstructors();
                            };
                            paginationNumbers.appendChild(pageButton);
                        }
                    }

                    function updateButtons() {
                        document.getElementById("prevPage").disabled = (currentPage === 1);
                        document.getElementById("nextPage").disabled = (currentPage === Math.ceil(filteredInstructors.length / instructorsPerPage));
                    }

                    function applyFilters() {
                        const searchQuery = document.getElementById("searchInput").value.toLowerCase();
                        const statusFilter = document.getElementById("statusFilter").value;
                        const verifiedFilter = document.getElementById("verifiedFilter").value;

                        filteredInstructors = instructorData.filter(instructor => {
                            const matchesSearch = instructor.name.toLowerCase().includes(searchQuery) ||
                                instructor.email.toLowerCase().includes(searchQuery);

                            const matchesStatus = statusFilter === "all" || instructor.status === statusFilter;

                            const matchesVerification = verifiedFilter === "all" ||
                                (verifiedFilter === "verified" && instructor.isVerified) ||
                                (verifiedFilter === "unverified" && !instructor.isVerified);

                            return matchesSearch && matchesStatus && matchesVerification;
                        });

                        // Sort the instructors
                        sortInstructors();

                        currentPage = 1;
                        createPagination();
                        displayInstructors();
                    }

                    function sortInstructors() {
                        filteredInstructors.sort((a, b) => {
                            let valA = a[sortColumn];
                            let valB = b[sortColumn];

                            if (sortColumn === "name" || sortColumn === "email") {
                                return sortDirection === "asc" ?
                                    valA.localeCompare(valB) :
                                    valB.localeCompare(valA);
                            } else if (sortColumn === "lastLogin") {
                                return sortDirection === "asc" ?
                                    new Date(valA) - new Date(valB) :
                                    new Date(valB) - new Date(valA);
                            } else if (sortColumn === "courses" || sortColumn === "students") {
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

                            displayInstructors();
                        }
                    });

                    document.getElementById("nextPage").addEventListener("click", function() {
                        if (currentPage < Math.ceil(filteredInstructors.length / instructorsPerPage)) {
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

                            displayInstructors();
                        }
                    });

                    // Status filter change
                    document.getElementById("statusFilter").addEventListener("change", function() {
                        applyFilters();
                    });

                    // Verification filter change
                    document.getElementById("verifiedFilter").addEventListener("change", function() {
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
                        const instructorId = this.getAttribute("data-id");
                        const activeRadio = document.getElementById("activeRadio");
                        const suspendRadio = document.getElementById("suspendRadio");
                        const banRadio = document.getElementById("banRadio");

                        if (!activeRadio.checked && !suspendRadio.checked && !banRadio.checked) {
                            alert("Please select an action (Active, Suspend, or Ban).");
                            return;
                        }

                        let newStatus = "";
                        if (activeRadio.checked) {
                            newStatus = "active";
                            updateInstructorStatus(instructorId, newStatus);
                        } else {
                            if (suspendRadio.checked) {
                                newStatus = "suspended";
                            } else if (banRadio.checked) {
                                newStatus = "banned";
                            }

                            const actionReason = document.getElementById("actionReason").value.trim();

                            if (!actionReason) {
                                alert("Please provide a reason for this action.");
                                return;
                            }

                            updateInstructorStatus(instructorId, newStatus, actionReason);
                        }

                        // Close the modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById("instructorModal"));
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

                            // Update sort icons
                            document.querySelectorAll(".sort-icon").forEach(icon => {
                                icon.innerHTML = "⇅";
                            });

                            this.querySelector(".sort-icon").innerHTML = sortDirection === "asc" ? "↑" : "↓";

                            // Sort and display
                            sortInstructors();
                            displayInstructors();
                        });
                    });

                    // Initialize
                    fetchInstructorData();
                });
            </script>
            <!-- End Instructor Table -->
        </div>
        <!-- End Card -->
    </div>
    <!-- End Content -->

</main>
<!-- ========== END MAIN CONTENT ========== -->


<?php include '../includes/department/footer.php'; ?>