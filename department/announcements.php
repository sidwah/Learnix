<!-- admin/announcements.php  -->
<?php include '../includes/admin-header.php'; ?>
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

    <!-- Content Container -->
    <div class="navbar-sidebar-aside-content content-space-1 content-space-md-2 px-lg-5 px-xl-10">
        <!-- Page Header Section -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="page-header-title">Announcement Management</h1>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAnnouncementModal">
                        <i class="bi-plus"></i> Create Announcement
                    </button>
                </div>
            </div>
        </div>
        <!-- End Page Header Section -->

        <!-- Statistics Cards Section -->
        <div class="row mb-4">
            <!-- Total Announcements Card -->
            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Total Announcements</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="card-title text-inherit" id="totalAnnouncements">24</h2>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-soft-primary text-primary p-2">
                                    <i class="bi-megaphone-fill"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Total Announcements Card -->

            <!-- Active Announcements Card -->
            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Active Announcements</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="card-title text-inherit" id="activeAnnouncements">18</h2>
                                <span class="text-body fs-6" id="activePercentage">75%</span>
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
            <!-- End Active Announcements Card -->

            <!-- Scheduled Announcements Card -->
            <div class="col-sm-6 col-lg-3 mb-3 mb-sm-0">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Scheduled</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="card-title text-inherit" id="scheduledAnnouncements">4</h2>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-soft-warning text-warning p-2">
                                    <i class="bi-clock-fill"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Scheduled Announcements Card -->

            <!-- Read Rate Card -->
            <div class="col-sm-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Avg. Read Rate</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="card-title text-inherit" id="readRate">68%</h2>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-soft-info text-info p-2">
                                    <i class="bi-eye-fill"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Read Rate Card -->
        </div>
        <!-- End Statistics Cards Section -->

        <!-- Filter and Search Controls Section -->
        <div class="container content-space-b-1">
            <!-- Search and Filter Form -->
            <form>
                <!-- Row 1: Search Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <!-- Enhanced Search Input Card -->
                        <div class="input-card input-card-sm">
                            <!-- Title/Content Search Field -->
                            <div class="input-card-form">
                                <label for="announcementTitleForm" class="form-label visually-hidden">Title or content</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-prepend input-group-text">
                                        <i class="bi-search"></i>
                                    </span>
                                    <input type="text" class="form-control" id="announcementTitleForm" placeholder="Title or content" aria-label="Title or content">
                                </div>
                            </div>
                            <!-- Target Audience Search Field -->
                            <div class="input-card-form">
                                <label for="targetAudienceForm" class="form-label visually-hidden">Target audience</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-prepend input-group-text">
                                        <i class="bi-people"></i>
                                    </span>
                                    <input type="text" class="form-control" id="targetAudienceForm" placeholder="Target audience" aria-label="Target audience">
                                </div>
                            </div>
                            <!-- Search Button -->
                            <button type="button" class="btn btn-primary">Search</button>
                        </div>
                        <!-- End Enhanced Search Input Card -->
                    </div>
                </div>

                <!-- Search Options Section -->
                <div class="row align-items-center mb-4">
                    <!-- Search Scope Label -->
                    <div class="col-md-auto mb-2 mb-md-0">
                        <h6 class="mb-0">Search in:</h6>
                    </div>
                    <!-- Search Field Options -->
                    <div class="col-md mb-2 mb-md-0">
                        <!-- Title Checkbox -->
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="searchInCheckbox1" value="title" checked>
                            <label class="form-check-label" for="searchInCheckbox1">Title</label>
                        </div>
                        <!-- Content Checkbox -->
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="searchInCheckbox2" value="content" checked>
                            <label class="form-check-label" for="searchInCheckbox2">Content</label>
                        </div>
                        <!-- Creator Checkbox -->
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="searchInCheckbox3" value="creator">
                            <label class="form-check-label" for="searchInCheckbox3">Creator</label>
                        </div>
                    </div>
                    <!-- Active Only Toggle Switch -->
                    <div class="col-md-auto">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="activeOnlySwitch">
                            <label class="form-check-label" for="activeOnlySwitch">Active only</label>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Advanced Filters Section -->
                <div class="row gx-2 gx-md-3 mb-3 align-items-end">
                    <!-- Status Filter Dropdown -->
                    <div class="col-sm-6 col-md-3 mb-3 mb-md-0">
                        <label class="form-label small" for="statusFilter">Status</label>
                        <select class="form-select" id="statusFilter" aria-label="Filter by status">
                            <option selected>All status</option>
                            <option value="Published">Published</option>
                            <option value="Scheduled">Scheduled</option>
                            <option value="Draft">Draft</option>
                            <option value="Archived">Archived</option>
                        </select>
                    </div>

                    <!-- Importance Filter Dropdown -->
                    <div class="col-sm-6 col-md-3 mb-3 mb-md-0">
                        <label class="form-label small" for="importanceFilter">Importance</label>
                        <select class="form-select" id="importanceFilter" aria-label="Filter by importance">
                            <option selected>All importance</option>
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                            <option value="Critical">Critical</option>
                        </select>
                    </div>

                    <!-- Date Range Picker -->
                    <div class="col-sm-6 col-md-3 mb-3 mb-md-0">
                        <label class="form-label small" for="dateRangePicker">Date Range</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-prepend input-group-text">
                                <i class="bi-calendar-range"></i>
                            </span>
                            <input type="text" id="dateRangePicker" class="form-control flatpickr-custom" placeholder="Select date range">
                        </div>
                    </div>

                    <!-- Export Button -->
                    <div class="col-sm-6 col-md-3 mb-3 mb-md-0">
                        <button type="button" class="btn btn-outline-primary w-100">
                            <i class="bi-download me-1"></i> Export Results
                        </button>
                    </div>
                </div>

                <!-- Results Count Display -->
                <div class="row">
                    <div class="col-12 text-end mb-3">
                        <span class="text-muted small">Showing 1-10 of 24 announcements</span>
                    </div>
                </div>
            </form>
            <!-- End Search and Filter Form -->
        </div>
        <!-- End Filter and Search Controls Section -->

        <!-- Date Range Picker Initialization Script -->
        <script>
            // Initialize flatpickr date range picker when the DOM is fully loaded
            document.addEventListener('DOMContentLoaded', function() {
                // Check if flatpickr library is available
                if (typeof flatpickr !== 'undefined') {
                    // Initialize the date range picker with custom options
                    flatpickr("#dateRangePicker", {
                        mode: "range", // Enable date range selection
                        dateFormat: "Y-m-d", // Internal date format
                        altInput: true, // Use an alternative input to display the dates
                        altFormat: "F j, Y", // Human-readable date format
                        rangeSeparator: " to ", // Text to separate dates in range
                        // Callback function when date picker closes
                        onClose: function(selectedDates, dateStr, instance) {
                            if (selectedDates.length > 0) {
                                // Log selected date range for debugging
                                console.log("Date range selected:", dateStr);
                                // Here you would trigger filtering of announcements based on date range
                            }
                        }
                    });
                }
            });
        </script>


        <!-- Nav -->
        <div class="text-center">
            <ul class="nav nav-segment nav-pills mb-4" id="announcementTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-tab" data-bs-toggle="pill" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="true">All Announcements</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="systemwide-tab" data-bs-toggle="pill" data-bs-target="#systemwide" type="button" role="tab" aria-controls="systemwide" aria-selected="false">System-wide</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="course-tab" data-bs-toggle="pill" data-bs-target="#course" type="button" role="tab" aria-controls="course" aria-selected="false">Course</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="templates-tab" data-bs-toggle="pill" data-bs-target="#templates" type="button" role="tab" aria-controls="templates" aria-selected="false">Templates</button>
                </li>
            </ul>
        </div>
        <!-- End Nav -->

        <!-- Tab Content -->
        <div class="tab-content" id="announcementTabContent">
            <!-- All Announcements Tab -->
            <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                <div class="card">
                    <div class="card-header">
                        <div class="row justify-content-between align-items-center flex-grow-1">
                            <div class="col-md">
                                <h4 class="card-header-title">All Announcements</h4>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Importance</th>
                                    <th>Target</th>
                                    <th>Created by</th>
                                    <th>Published</th>
                                    <th>Read Rate</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Announcement Row -->
                                <tr>
                                    <td>
                                        <a class="d-flex align-items-center" href="#">
                                            <div class="flex-grow-1 ms-3">
                                                <span class="d-block h5 text-inherit mb-0">Platform Maintenance Notice</span>
                                                <span class="d-block fs-6 text-body">Scheduled downtime for system updates</span>
                                            </div>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Published</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">Critical</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-soft-primary">System-wide</span>
                                    </td>
                                    <td>
                                        <a class="d-flex align-items-center" href="#">
                                            <div class="avatar avatar-circle">
                                                <img class="avatar-img" src="../assets/img/160x160/img10.jpg" alt="Admin User">
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <span class="d-block fs-6 text-body">Admin</span>
                                            </div>
                                        </a>
                                    </td>
                                    <td>Apr 25, 2025</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="fs-6 me-2">72%</span>
                                            <div class="progress table-progress">
                                                <div class="progress-bar" role="progressbar" style="width: 72%" aria-valuenow="72" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a class="btn btn-white btn-sm" href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                <i class="bi-pencil"></i>
                                            </a>
                                            <a class="btn btn-white btn-sm" href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Duplicate">
                                                <i class="bi-copy"></i>
                                            </a>
                                            <a class="btn btn-white btn-sm" href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                                <i class="bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <!-- End Announcement Row -->

                                <!-- Announcement Row -->
                                <tr>
                                    <td>
                                        <a class="d-flex align-items-center" href="#">
                                            <div class="flex-grow-1 ms-3">
                                                <span class="d-block h5 text-inherit mb-0">Welcome to Spring Semester</span>
                                                <span class="d-block fs-6 text-body">Important information for all students</span>
                                            </div>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">Scheduled</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">Medium</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-soft-info">Students</span>
                                    </td>
                                    <td>
                                        <a class="d-flex align-items-center" href="#">
                                            <div class="avatar avatar-circle">
                                                <img class="avatar-img" src="../assets/img/160x160/img9.jpg" alt="Admin User">
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <span class="d-block fs-6 text-body">Admin</span>
                                            </div>
                                        </a>
                                    </td>
                                    <td>May 1, 2025</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="fs-6 me-2">0%</span>
                                            <div class="progress table-progress">
                                                <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a class="btn btn-white btn-sm" href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                <i class="bi-pencil"></i>
                                            </a>
                                            <a class="btn btn-white btn-sm" href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Duplicate">
                                                <i class="bi-copy"></i>
                                            </a>
                                            <a class="btn btn-white btn-sm" href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                                <i class="bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <!-- End Announcement Row -->

                                <!-- Announcement Row -->
                                <tr>
                                    <td>
                                        <a class="d-flex align-items-center" href="#">
                                            <div class="flex-grow-1 ms-3">
                                                <span class="d-block h5 text-inherit mb-0">New Instructor Resources Available</span>
                                                <span class="d-block fs-6 text-body">Updated teaching materials and guides</span>
                                            </div>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Published</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Low</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-soft-warning">Instructors</span>
                                    </td>
                                    <td>
                                        <a class="d-flex align-items-center" href="#">
                                            <div class="avatar avatar-circle">
                                                <img class="avatar-img" src="../assets/img/160x160/img8.jpg" alt="Admin User">
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <span class="d-block fs-6 text-body">Admin</span>
                                            </div>
                                        </a>
                                    </td>
                                    <td>Apr 20, 2025</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="fs-6 me-2">86%</span>
                                            <div class="progress table-progress">
                                                <div class="progress-bar" role="progressbar" style="width: 86%" aria-valuenow="86" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a class="btn btn-white btn-sm" href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                <i class="bi-pencil"></i>
                                            </a>
                                            <a class="btn btn-white btn-sm" href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Duplicate">
                                                <i class="bi-copy"></i>
                                            </a>
                                            <a class="btn btn-white btn-sm" href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                                <i class="bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <!-- End Announcement Row -->
                            </tbody>
                        </table>
                    </div>
                    <!-- End Table -->

                    <!-- Pagination -->
                    <div class="card-footer">
                        <div class="d-flex justify-content-center justify-content-sm-end">
                            <nav aria-label="Announcements pagination">
                                <ul class="pagination">
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                                    <li class="page-item">
                                        <a class="page-link" href="#" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                    <!-- End Pagination -->
                </div>
            </div>
            <!-- End All Announcements Tab -->

            <!-- System-wide Tab -->
            <div class="tab-pane fade" id="systemwide" role="tabpanel" aria-labelledby="systemwide-tab">
                <div class="card">
                    <div class="card-header">
                        <div class="row justify-content-between align-items-center flex-grow-1">
                            <div class="col-md">
                                <h4 class="card-header-title">System-wide Announcements</h4>
                            </div>
                        </div>
                    </div>

                    <!-- Similar table structure as All Announcements but with system-wide only -->
                    <div class="table-responsive datatable-custom">
                        <!-- System-wide announcements table content here -->
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Importance</th>
                                    <th>Target Roles</th>
                                    <th>Created by</th>
                                    <th>Published</th>
                                    <th>Read Rate</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- System-wide announcement entry -->
                                <tr>
                                    <td>
                                        <a class="d-flex align-items-center" href="#">
                                            <div class="flex-grow-1 ms-3">
                                                <span class="d-block h5 text-inherit mb-0">Platform Maintenance Notice</span>
                                            </div>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Published</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">Critical</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-soft-primary">All Users</span>
                                    </td>
                                    <td>
                                        <a class="d-flex align-items-center" href="#">
                                            <div class="avatar avatar-circle">
                                                <img class="avatar-img" src="../assets/img/160x160/img10.jpg" alt="Admin User">
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <span class="d-block fs-6 text-body">Admin</span>
                                            </div>
                                        </a>
                                    </td>
                                    <td>Apr 25, 2025</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="fs-6 me-2">72%</span>
                                            <div class="progress table-progress">
                                                <div class="progress-bar" role="progressbar" style="width: 72%" aria-valuenow="72" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a class="btn btn-white btn-sm" href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                <i class="bi-pencil"></i>
                                            </a>
                                            <a class="btn btn-white btn-sm" href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Duplicate">
                                                <i class="bi-copy"></i>
                                            </a>
                                            <a class="btn btn-white btn-sm" href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                                <i class="bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <!-- End system-wide announcement entry -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- End System-wide Tab -->

            <!-- Course Tab -->
            <div class="tab-pane fade" id="course" role="tabpanel" aria-labelledby="course-tab">
                <div class="card">
                    <div class="card-header">
                        <div class="row justify-content-between align-items-center flex-grow-1">
                            <div class="col-md">
                                <h4 class="card-header-title">Course Announcements</h4>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" id="courseFilter">
                                    <option selected>All Courses</option>
                                    <option>Web Development 101</option>
                                    <option>Database Systems</option>
                                    <option>Mobile App Development</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Course announcements table -->
                    <div class="table-responsive datatable-custom">
                        <!-- Course announcements table content here -->
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <!-- Table structure similar to above -->
                            <thead class="thead-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Course</th>
                                    <th>Status</th>
                                    <th>Importance</th>
                                    <th>Created by</th>
                                    <th>Published</th>
                                    <th>Read Rate</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Course announcement entry -->
                                <tr>
                                    <td>
                                        <a class="d-flex align-items-center" href="#">
                                            <div class="flex-grow-1 ms-3">
                                                <span class="d-block h5 text-inherit mb-0">Final Project Guidelines</span>
                                            </div>
                                        </a>
                                    </td>
                                    <td>Web Development 101</td>
                                    <td>
                                        <span class="badge bg-success">Published</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">Medium</span>
                                    </td>
                                    <td>
                                        <a class="d-flex align-items-center" href="#">
                                            <div class="avatar avatar-circle">
                                                <img class="avatar-img" src="../assets/img/160x160/img5.jpg" alt="Instructor">
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <span class="d-block fs-6 text-body">John Smith</span>
                                            </div>
                                        </a>
                                    </td>
                                    <td>Apr 15, 2025</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="fs-6 me-2">88%</span>
                                            <div class="progress table-progress">
                                                <div class="progress-bar" role="progressbar" style="width: 88%" aria-valuenow="88" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a class="btn btn-white btn-sm" href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                <i class="bi-pencil"></i>
                                            </a>
                                            <a class="btn btn-white btn-sm" href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Duplicate">
                                                <i class="bi-copy"></i>
                                            </a>
                                            <a class="btn btn-white btn-sm" href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                                <i class="bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <!-- End course announcement entry -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- End Course Tab -->

            <!-- Templates Tab -->
            <div class="tab-pane fade" id="templates" role="tabpanel" aria-labelledby="templates-tab">
                <div class="card">
                    <div class="card-header">
                        <div class="row justify-content-between align-items-center flex-grow-1">
                            <div class="col-md">
                                <h4 class="card-header-title">Announcement Templates</h4>
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createTemplateModal">
                                    <i class="bi-plus"></i>
                                    <i class="bi-plus"></i> Create Template
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Card Grid for Templates -->
                    <div class="card-body">
                        <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3">
                            <!-- Template Card -->
                            <div class="col mb-3">
                                <div class="card h-100">
                                    <div class="card-pinned">
                                        <div class="card-body">
                                            <h5 class="card-title">System Maintenance</h5>
                                            <p class="card-text">Template for scheduled maintenance announcements.</p>
                                        </div>

                                        <div class="card-footer">
                                            <div class="row justify-content-between align-items-center">
                                                <div class="col">
                                                    <small class="text-muted">Last updated: Apr 10, 2025</small>
                                                </div>
                                                <div class="col-auto">
                                                    <div class="dropdown">
                                                        <button type="button" class="btn btn-ghost-secondary btn-icon btn-sm card-dropdown-btn" id="template1DropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="bi-three-dots-vertical"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="template1DropdownBtn">
                                                            <a class="dropdown-item" href="#">
                                                                <i class="bi-pencil dropdown-item-icon"></i> Edit
                                                            </a>
                                                            <a class="dropdown-item" href="#">
                                                                <i class="bi-file-earmark-plus dropdown-item-icon"></i> Use as template
                                                            </a>
                                                            <a class="dropdown-item" href="#">
                                                                <i class="bi-trash dropdown-item-icon"></i> Delete
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Template Card -->

                            <!-- Template Card -->
                            <div class="col mb-3">
                                <div class="card h-100">
                                    <div class="card-pinned">
                                        <div class="card-body">
                                            <h5 class="card-title">Course Updates</h5>
                                            <p class="card-text">Template for announcing course content updates.</p>
                                        </div>

                                        <div class="card-footer">
                                            <div class="row justify-content-between align-items-center">
                                                <div class="col">
                                                    <small class="text-muted">Last updated: Apr 5, 2025</small>
                                                </div>
                                                <div class="col-auto">
                                                    <div class="dropdown">
                                                        <button type="button" class="btn btn-ghost-secondary btn-icon btn-sm card-dropdown-btn" id="template2DropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="bi-three-dots-vertical"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="template2DropdownBtn">
                                                            <a class="dropdown-item" href="#">
                                                                <i class="bi-pencil dropdown-item-icon"></i> Edit
                                                            </a>
                                                            <a class="dropdown-item" href="#">
                                                                <i class="bi-file-earmark-plus dropdown-item-icon"></i> Use as template
                                                            </a>
                                                            <a class="dropdown-item" href="#">
                                                                <i class="bi-trash dropdown-item-icon"></i> Delete
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Template Card -->

                            <!-- Template Card -->
                            <div class="col mb-3">
                                <div class="card h-100">
                                    <div class="card-pinned">
                                        <div class="card-body">
                                            <h5 class="card-title">Important Deadlines</h5>
                                            <p class="card-text">Template for reminding students about upcoming deadlines.</p>
                                        </div>

                                        <div class="card-footer">
                                            <div class="row justify-content-between align-items-center">
                                                <div class="col">
                                                    <small class="text-muted">Last updated: Mar 28, 2025</small>
                                                </div>
                                                <div class="col-auto">
                                                    <div class="dropdown">
                                                        <button type="button" class="btn btn-ghost-secondary btn-icon btn-sm card-dropdown-btn" id="template3DropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="bi-three-dots-vertical"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="template3DropdownBtn">
                                                            <a class="dropdown-item" href="#">
                                                                <i class="bi-pencil dropdown-item-icon"></i> Edit
                                                            </a>
                                                            <a class="dropdown-item" href="#">
                                                                <i class="bi-file-earmark-plus dropdown-item-icon"></i> Use as template
                                                            </a>
                                                            <a class="dropdown-item" href="#">
                                                                <i class="bi-trash dropdown-item-icon"></i> Delete
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Template Card -->
                        </div>
                    </div>
                    <!-- End Card Grid -->
                </div>
            </div>
            <!-- End Templates Tab -->
        </div>
        <!-- End Tab Content -->

        <!-- Create Announcement Modal -->
        <div class="modal fade" id="createAnnouncementModal" tabindex="-1" aria-labelledby="createAnnouncementModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createAnnouncementModalLabel">Create Announcement</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <!-- Title -->
                            <div class="mb-3">
                                <label for="announcementTitle" class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="announcementTitle" placeholder="Enter announcement title" required>
                            </div>

                            <!-- Content -->
                            <div class="mb-3">
                                <label for="announcementContent" class="form-label">Content <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="announcementContent" rows="6" placeholder="Enter announcement content" required></textarea>
                            </div>

                            <div class="row">
                                <!-- Importance Level -->
                                <div class="col-md-6 mb-3">
                                    <label for="importanceLevel" class="form-label">Importance Level</label>
                                    <select class="form-select" id="importanceLevel">
                                        <option value="Low">Low</option>
                                        <option value="Medium" selected>Medium</option>
                                        <option value="High">High</option>
                                        <option value="Critical">Critical</option>
                                    </select>
                                </div>

                                <!-- Status -->
                                <div class="col-md-6 mb-3">
                                    <label for="announcementStatus" class="form-label">Status</label>
                                    <select class="form-select" id="announcementStatus">
                                        <option value="Draft">Save as Draft</option>
                                        <option value="Published" selected>Publish Immediately</option>
                                        <option value="Scheduled">Schedule for Later</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Schedule Date/Time (initially hidden) -->
                            <div class="mb-3" id="scheduleOptions" style="display: none;">
                                <label for="scheduleDateTime" class="form-label">Schedule Date/Time</label>
                                <input type="datetime-local" class="form-control" id="scheduleDateTime">
                            </div>

                            <!-- Expiration Date -->
                            <div class="mb-3">
                                <label for="expirationDate" class="form-label">Expiration Date (Optional)</label>
                                <input type="date" class="form-control" id="expirationDate">
                                <div class="form-text">Announcement will be automatically archived after this date.</div>
                            </div>

                            <!-- Target Options -->
                            <div class="mb-4">
                                <label class="form-label">Target</label>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="targetType" id="targetSystemWide" checked>
                                    <label class="form-check-label" for="targetSystemWide">
                                        System-wide
                                    </label>
                                </div>

                                <!-- Target Roles (for system-wide) -->
                                <div id="systemWideOptions" class="ms-4 mb-3">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="targetAllUsers" checked>
                                        <label class="form-check-label" for="targetAllUsers">All Users</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="targetStudents">
                                        <label class="form-check-label" for="targetStudents">Students Only</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="targetInstructors">
                                        <label class="form-check-label" for="targetInstructors">Instructors Only</label>
                                    </div>
                                </div>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="targetType" id="targetCourseSpecific">
                                    <label class="form-check-label" for="targetCourseSpecific">
                                        Course-specific
                                    </label>
                                </div>

                                <!-- Course Selection (initially hidden) -->
                                <div id="courseSpecificOptions" class="ms-4 mb-3" style="display: none;">
                                    <select class="form-select" id="targetCourse">
                                        <option value="">Select a course</option>
                                        <option value="1">Web Development 101</option>
                                        <option value="2">Database Systems</option>
                                        <option value="3">Mobile App Development</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Delivery Channels -->
                            <div class="mb-3">
                                <label class="form-label">Delivery Channels</label>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="channelInApp" checked disabled>
                                    <label class="form-check-label" for="channelInApp">In-App Notification</label>
                                    <small class="form-text d-block">Always enabled</small>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="channelEmail">
                                    <label class="form-check-label" for="channelEmail">Email Notification</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="channelPush">
                                    <label class="form-check-label" for="channelPush">Push Notification</label>
                                    <small class="form-text d-block">For mobile app users</small>
                                </div>
                            </div>

                            <!-- File Attachments -->
                            <div class="mb-3">
                                <label class="form-label">Attachments (Optional)</label>
                                <div class="attachment-upload-container">
                                    <div class="d-flex align-items-center mb-2">
                                        <button type="button" class="btn btn-primary me-2" id="attachment-upload-btn">
                                            <i class="bi-file-earmark-arrow-up me-1"></i> Add Files
                                        </button>
                                        <input type="file" id="attachment-file-input" multiple style="display: none" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                        <span class="text-muted" id="attachment-count">0/5 files uploaded</span>
                                    </div>
                                    <p class="text-muted small">Max file size: 10MB. Allowed formats: PDF, DOC, DOCX, JPG, PNG</p>
                                </div>
                                <div id="attachment-preview" class="mt-3">
                                    <div class="row" id="attachment-files-container">
                                        <!-- Attachment previews will be shown here -->
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveAnnouncement">Create Announcement</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Create Announcement Modal -->

        <!-- Create Template Modal -->
        <div class="modal fade" id="createTemplateModal" tabindex="-1" aria-labelledby="createTemplateModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createTemplateModalLabel">Create Announcement Template</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <!-- Template Name -->
                            <div class="mb-3">
                                <label for="templateName" class="form-label">Template Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="templateName" placeholder="Enter template name" required>
                            </div>

                            <!-- Template Content -->
                            <div class="mb-3">
                                <label for="templateContent" class="form-label">Template Content <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="templateContent" rows="6" placeholder="Enter template content with placeholders" required></textarea>
                                <div class="form-text">
                                    Use placeholders like {{course_name}}, {{date}}, {{instructor_name}} that will be replaced when using the template.
                                </div>
                            </div>

                            <!-- Default Settings -->
                            <div class="mb-3">
                                <label class="form-label">Default Settings</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="defaultImportance" class="form-label">Default Importance</label>
                                            <select class="form-select" id="defaultImportance">
                                                <option value="Low">Low</option>
                                                <option value="Medium" selected>Medium</option>
                                                <option value="High">High</option>
                                                <option value="Critical">Critical</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="defaultTarget" class="form-label">Default Target</label>
                                            <select class="form-select" id="defaultTarget">
                                                <option value="All Users" selected>All Users</option>
                                                <option value="Students">Students Only</option>
                                                <option value="Instructors">Instructors Only</option>
                                                <option value="Course">Course-specific</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary">Save Template</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Create Template Modal -->
    </div>
    <!-- End Content -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- JS Scripts -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Format file size function
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Handle status change to show/hide scheduling options
        const announcementStatus = document.getElementById('announcementStatus');
        const scheduleOptions = document.getElementById('scheduleOptions');

        if (announcementStatus && scheduleOptions) {
            announcementStatus.addEventListener('change', function() {
                if (this.value === 'Scheduled') {
                    scheduleOptions.style.display = 'block';

                    // Set default scheduled time to tomorrow at 9:00 AM
                    const tomorrow = new Date();
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    tomorrow.setHours(9, 0, 0, 0);

                    // Format for datetime-local input
                    const formattedDate = tomorrow.toISOString().slice(0, 16);
                    document.getElementById('scheduleDateTime').value = formattedDate;
                } else {
                    scheduleOptions.style.display = 'none';
                }
            });
        }

        // Handle target type change to show/hide relevant options
        const targetSystemWide = document.getElementById('targetSystemWide');
        const targetCourseSpecific = document.getElementById('targetCourseSpecific');
        const systemWideOptions = document.getElementById('systemWideOptions');
        const courseSpecificOptions = document.getElementById('courseSpecificOptions');

        if (targetSystemWide && targetCourseSpecific && systemWideOptions && courseSpecificOptions) {
            targetSystemWide.addEventListener('change', function() {
                if (this.checked) {
                    systemWideOptions.style.display = 'block';
                    courseSpecificOptions.style.display = 'none';
                }
            });

            targetCourseSpecific.addEventListener('change', function() {
                if (this.checked) {
                    systemWideOptions.style.display = 'none';
                    courseSpecificOptions.style.display = 'block';
                }
            });
        }

        // Handle mutually exclusive checkboxes for targeting
        const targetAllUsers = document.getElementById('targetAllUsers');
        const targetStudents = document.getElementById('targetStudents');
        const targetInstructors = document.getElementById('targetInstructors');

        if (targetAllUsers && targetStudents && targetInstructors) {
            targetAllUsers.addEventListener('change', function() {
                if (this.checked) {
                    targetStudents.checked = false;
                    targetInstructors.checked = false;
                }
            });

            targetStudents.addEventListener('change', function() {
                if (this.checked) {
                    targetAllUsers.checked = false;
                    // Allow both Students and Instructors to be checked
                }
            });

            targetInstructors.addEventListener('change', function() {
                if (this.checked) {
                    targetAllUsers.checked = false;
                    // Allow both Students and Instructors to be checked
                }
            });
        }

        // File upload handling (similar to instructor interface)
        const MAX_FILES = 5;
        const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
        const ALLOWED_TYPES = ['.pdf', '.doc', '.docx', '.jpg', '.jpeg', '.png'];

        // Array to store uploaded files
        const uploadedFiles = [];

        // Initialize file upload button
        const attachmentUploadBtn = document.getElementById('attachment-upload-btn');
        const attachmentFileInput = document.getElementById('attachment-file-input');

        if (attachmentUploadBtn && attachmentFileInput) {
            attachmentUploadBtn.addEventListener('click', function() {
                attachmentFileInput.click();
            });

            attachmentFileInput.addEventListener('change', function(e) {
                const files = e.target.files;

                if (!files || files.length === 0) return;

                // Process each selected file
                Array.from(files).forEach(file => {
                    // Check if we've reached the maximum
                    if (uploadedFiles.length >= MAX_FILES) {
                        alert(`Maximum ${MAX_FILES} files allowed`);
                        return;
                    }

                    // Validate file size
                    if (file.size > MAX_FILE_SIZE) {
                        alert(`File ${file.name} exceeds maximum size of 10MB`);
                        return;
                    }

                    // Validate file type
                    const fileExt = '.' + file.name.split('.').pop().toLowerCase();
                    if (!ALLOWED_TYPES.includes(fileExt)) {
                        alert(`File type ${fileExt} not allowed`);
                        return;
                    }

                    // Create a unique ID for the file
                    const fileId = 'file-' + Date.now() + '-' + Math.floor(Math.random() * 1000);

                    // Add to uploaded files array
                    uploadedFiles.push({
                        id: fileId,
                        file: file,
                        name: file.name,
                        size: file.size
                    });

                    // Add preview card
                    addFilePreview(fileId, file.name, file.size);

                    // Update the counter
                    updateFileCounter();
                });

                // Clear the file input for next selection
                this.value = '';
            });
        }

        // Function to add file preview card
        function addFilePreview(fileId, fileName, fileSize) {
            const fileIconClass = getFileIconClass(fileName);
            const filesContainer = document.getElementById('attachment-files-container');

            if (filesContainer) {
                const filePreview = document.createElement('div');
                filePreview.className = 'col-md-4 mb-2';
                filePreview.id = `attachment-${fileId}`;
                filePreview.innerHTML = `
                   <div class="card border">
                       <div class="card-body p-2">
                           <div class="d-flex align-items-start">
                               <div class="me-2">
                                   <i class="${fileIconClass} font-24 text-primary"></i>
                               </div>
                               <div class="flex-grow-1 overflow-hidden">
                                   <h5 class="font-14 mt-0 text-truncate mb-1">${fileName}</h5>
                                   <p class="text-muted font-13 mb-0 text-truncate">${formatFileSize(fileSize)}</p>
                               </div>
                               <div class="dropdown">
                                   <button type="button" class="btn btn-ghost-secondary btn-icon btn-sm" onclick="removeAttachment('${fileId}')">
                                       <i class="bi-trash"></i>
                                   </button>
                               </div>
                           </div>
                       </div>
                   </div>
               `;

                filesContainer.appendChild(filePreview);
            }
        }

        // Get appropriate icon based on file extension
        function getFileIconClass(fileName) {
            const ext = fileName.split('.').pop().toLowerCase();

            if (['jpg', 'jpeg', 'png', 'gif', 'bmp'].includes(ext)) {
                return 'bi-image';
            } else if (['pdf'].includes(ext)) {
                return 'bi-file-pdf';
            } else if (['doc', 'docx'].includes(ext)) {
                return 'bi-file-word';
            } else if (['xls', 'xlsx'].includes(ext)) {
                return 'bi-file-excel';
            } else if (['ppt', 'pptx'].includes(ext)) {
                return 'bi-file-ppt';
            } else {
                return 'bi-file-earmark';
            }
        }

        // Function to update file counter
        function updateFileCounter() {
            const attachmentCount = document.getElementById('attachment-count');
            if (attachmentCount) {
                attachmentCount.textContent = `${uploadedFiles.length}/${MAX_FILES} files uploaded`;

                // Disable button if max reached
                if (uploadedFiles.length >= MAX_FILES && attachmentUploadBtn) {
                    attachmentUploadBtn.disabled = true;
                } else if (attachmentUploadBtn) {
                    attachmentUploadBtn.disabled = false;
                }
            }
        }

        // Define removeAttachment function in the global scope
        window.removeAttachment = function(fileId) {
            const fileElement = document.getElementById(`attachment-${fileId}`);
            if (fileElement) {
                fileElement.remove();

                // Remove from array
                const index = uploadedFiles.findIndex(f => f.id === fileId);
                if (index !== -1) {
                    uploadedFiles.splice(index, 1);
                }

                // Update counter
                updateFileCounter();
            }
        };

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Save announcement button handler
        const saveAnnouncementBtn = document.getElementById('saveAnnouncement');
        if (saveAnnouncementBtn) {
            saveAnnouncementBtn.addEventListener('click', function() {
                // Get form values
                const title = document.getElementById('announcementTitle').value;
                const content = document.getElementById('announcementContent').value;
                const importance = document.getElementById('importanceLevel').value;
                const status = document.getElementById('announcementStatus').value;

                // Validation
                if (!title.trim()) {
                    alert('Please enter a title for the announcement');
                    return;
                }

                if (!content.trim()) {
                    alert('Please enter content for the announcement');
                    return;
                }

                // For demo purposes, just close the modal
                alert('Announcement created successfully!');
                $('#createAnnouncementModal').modal('hide');

                // In a real implementation, you would submit the form data via AJAX
            });
        }
    });
</script>

<?php include '../includes/admin-footer.php'; ?>