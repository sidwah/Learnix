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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Activity Logs</h1>
            <div>
                <button type="button" class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#exportModal">
                    <i class="bi-download me-1"></i> Export
                </button>
                <div class="dropdown">
                    <button type="button" class="btn btn-primary dropdown-toggle" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi-funnel me-1"></i> Filters
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 300px;" aria-labelledby="filterDropdown">
                        <h5 class="mb-3">Filter Logs</h5>
                        <form id="filterForm">
                            <div class="mb-3">
                                <label for="logType" class="form-label">Log Type</label>
                                <select class="form-select" id="logType" name="logType">
                                    <option value="">All Types</option>
                                    <option value="login">User Login</option>
                                    <option value="course_create">Course Creation</option>
                                    <option value="content_update">Content Update</option>
                                    <option value="user_management">User Management</option>
                                    <option value="system">System Activity</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="dateRange" class="form-label">Date Range</label>
                                <input type="text" class="js-range-datepicker form-control flatpickr-input" id="dateRange" name="dateRange" placeholder="Select date range" data-hs-daterangepicker-options='{
                                    "autoUpdateInput": false,
                                    "locale": {
                                        "cancelLabel": "Clear"
                                    }
                                }'>
                            </div>
                            <div class="mb-3">
                                <label for="userSearch" class="form-label">User</label>
                                <input type="text" class="form-control" id="userSearch" name="userSearch" placeholder="Search by username or email">
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="reset" class="btn btn-white">Reset</button>
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="card card-hover-shadow h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle">Total Activities</h6>
                                <span class="display-6 text-dark">1,248</span>
                            </div>
                            <div class="avatar avatar-sm avatar-soft-dark avatar-circle">
                                <span class="avatar-initials">
                                    <i class="bi-activity"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="card card-hover-shadow h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle">Today's Activities</h6>
                                <span class="display-6 text-dark">42</span>
                            </div>
                            <div class="avatar avatar-sm avatar-soft-info avatar-circle">
                                <span class="avatar-initials">
                                    <i class="bi-calendar-day"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3 mb-3 mb-sm-0">
                <div class="card card-hover-shadow h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle">Active Users</h6>
                                <span class="display-6 text-dark">18</span>
                            </div>
                            <div class="avatar avatar-sm avatar-soft-success avatar-circle">
                                <span class="avatar-initials">
                                    <i class="bi-people"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="card card-hover-shadow h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle">System Events</h6>
                                <span class="display-6 text-dark">7</span>
                            </div>
                            <div class="avatar avatar-sm avatar-soft-warning avatar-circle">
                                <span class="avatar-initials">
                                    <i class="bi-gear"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Stats Cards -->

        <!-- Activity Logs Table -->
        <div class="card">
            <div class="card-header card-header-content-sm-between">
                <div class="mb-2 mb-sm-0">
                    <form>
                        <div class="input-group input-group-merge">
                            <input type="text" class="form-control" placeholder="Search activities..." aria-label="Search activities">
                            <button type="button" class="input-group-append input-group-text">
                                <i class="bi-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="d-flex align-items-center">
                    <span class="me-2">Show:</span>
                    <select class="form-select form-select-sm w-auto">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Activity Type</th>
                            <th>Description</th>
                            <th>User</th>
                            <th>IP Address</th>
                            <th>Timestamp</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#2458</td>
                            <td>
                                <span class="badge bg-soft-success text-success">
                                    <i class="bi-box-arrow-in-right me-1"></i> Login
                                </span>
                            </td>
                            <td>User logged in successfully</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-xs avatar-circle me-2">
                                        <img class="avatar-img" src="../assets/img/160x160/img1.jpg" alt="User">
                                    </div>
                                    <span>john.doe@example.com</span>
                                </div>
                            </td>
                            <td>192.168.1.1</td>
                            <td>2 minutes ago</td>
                            <td>
                                <button type="button" class="btn btn-white btn-sm" data-bs-toggle="modal" data-bs-target="#logDetailModal">
                                    <i class="bi-eye me-1"></i> View
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>#2457</td>
                            <td>
                                <span class="badge bg-soft-primary text-primary">
                                    <i class="bi-journal-plus me-1"></i> Course Creation
                                </span>
                            </td>
                            <td>Created new course "Advanced JavaScript"</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-xs avatar-circle me-2">
                                        <img class="avatar-img" src="../assets/img/160x160/img2.jpg" alt="User">
                                    </div>
                                    <span>jane.smith@example.com</span>
                                </div>
                            </td>
                            <td>192.168.1.45</td>
                            <td>15 minutes ago</td>
                            <td>
                                <button type="button" class="btn btn-white btn-sm" data-bs-toggle="modal" data-bs-target="#logDetailModal">
                                    <i class="bi-eye me-1"></i> View
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>#2456</td>
                            <td>
                                <span class="badge bg-soft-info text-info">
                                    <i class="bi-file-earmark-text me-1"></i> Content Update
                                </span>
                            </td>
                            <td>Updated module "React Hooks" in course "React Fundamentals"</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-xs avatar-circle me-2">
                                        <img class="avatar-img" src="../assets/img/160x160/img3.jpg" alt="User">
                                    </div>
                                    <span>michael.johnson@example.com</span>
                                </div>
                            </td>
                            <td>192.168.1.102</td>
                            <td>1 hour ago</td>
                            <td>
                                <button type="button" class="btn btn-white btn-sm" data-bs-toggle="modal" data-bs-target="#logDetailModal">
                                    <i class="bi-eye me-1"></i> View
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>#2455</td>
                            <td>
                                <span class="badge bg-soft-warning text-warning">
                                    <i class="bi-person-plus me-1"></i> User Management
                                </span>
                            </td>
                            <td>Added new user "sarah.williams@example.com" with Instructor role</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-xs avatar-circle me-2">
                                        <img class="avatar-img" src="../assets/img/160x160/img4.jpg" alt="User">
                                    </div>
                                    <span>department@example.com</span>
                                </div>
                            </td>
                            <td>192.168.1.10</td>
                            <td>3 hours ago</td>
                            <td>
                                <button type="button" class="btn btn-white btn-sm" data-bs-toggle="modal" data-bs-target="#logDetailModal">
                                    <i class="bi-eye me-1"></i> View
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>#2454</td>
                            <td>
                                <span class="badge bg-soft-danger text-danger">
                                    <i class="bi-exclamation-triangle me-1"></i> Failed Login
                                </span>
                            </td>
                            <td>Failed login attempt for "unknown.user@example.com"</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-xs avatar-circle me-2">
                                        <span class="avatar-initials bg-soft-dark">UU</span>
                                    </div>
                                    <span>unknown.user@example.com</span>
                                </div>
                            </td>
                            <td>192.168.1.75</td>
                            <td>5 hours ago</td>
                            <td>
                                <button type="button" class="btn btn-white btn-sm" data-bs-toggle="modal" data-bs-target="#logDetailModal">
                                    <i class="bi-eye me-1"></i> View
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>#2453</td>
                            <td>
                                <span class="badge bg-soft-secondary text-secondary">
                                    <i class="bi-gear me-1"></i> System
                                </span>
                            </td>
                            <td>Scheduled database backup completed successfully</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-xs avatar-circle me-2">
                                        <span class="avatar-initials bg-soft-dark">SY</span>
                                    </div>
                                    <span>System</span>
                                </div>
                            </td>
                            <td>127.0.0.1</td>
                            <td>Yesterday, 2:30 AM</td>
                            <td>
                                <button type="button" class="btn btn-white btn-sm" data-bs-toggle="modal" data-bs-target="#logDetailModal">
                                    <i class="bi-eye me-1"></i> View
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <p class="mb-0">Showing <span class="fw-semibold">1</span> to <span class="fw-semibold">6</span> of <span class="fw-semibold">1,248</span> entries</p>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-end mb-0">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">Previous</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
        <!-- End Activity Logs Table -->
    </div>
    <!-- End Content -->

    <!-- Log Detail Modal -->
    <div class="modal fade" id="logDetailModal" tabindex="-1" role="dialog" aria-labelledby="logDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logDetailModalLabel">Activity Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <h6 class="mb-1">Activity Type</h6>
                        <span class="badge bg-soft-success text-success">
                            <i class="bi-box-arrow-in-right me-1"></i> Login
                        </span>
                    </div>
                    <div class="mb-4">
                        <h6 class="mb-1">Description</h6>
                        <p>User logged in successfully</p>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h6 class="mb-1">User</h6>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm avatar-circle me-2">
                                    <img class="avatar-img" src="../assets/img/160x160/img1.jpg" alt="User">
                                </div>
                                <div>
                                    <span class="d-block">John Doe</span>
                                    <span class="d-block text-muted small">john.doe@example.com</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-1">IP Address</h6>
                            <p>192.168.1.1</p>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h6 class="mb-1">Timestamp</h6>
                            <p>2 minutes ago</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-1">Location</h6>
                            <p>New York, US (Approximate)</p>
                        </div>
                    </div>
                    <div class="mb-4">
                        <h6 class="mb-1">Additional Data</h6>
                        <div class="bg-light p-3 rounded">
                            <pre class="mb-0" style="white-space: pre-wrap;">{
    "browser": "Chrome 112.0",
    "device": "Desktop",
    "os": "Windows 10"
}</pre>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Log Detail Modal -->

    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportModalLabel">Export Activity Logs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-4">
                            <label for="exportFormat" class="form-label">Format</label>
                            <select class="form-select" id="exportFormat">
                                <option value="csv">CSV</option>
                                <option value="excel">Excel</option>
                                <option value="pdf">PDF</option>
                                <option value="json">JSON</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="exportDateRange" class="form-label">Date Range</label>
                            <input type="text" class="js-range-datepicker form-control flatpickr-input" id="exportDateRange" placeholder="Select date range" data-hs-daterangepicker-options='{
                                "autoUpdateInput": false,
                                "locale": {
                                    "cancelLabel": "Clear"
                                }
                            }'>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Columns to Include</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="exportId" checked>
                                        <label class="form-check-label" for="exportId">ID</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="exportType" checked>
                                        <label class="form-check-label" for="exportType">Activity Type</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="exportDescription" checked>
                                        <label class="form-check-label" for="exportDescription">Description</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="exportUser" checked>
                                        <label class="form-check-label" for="exportUser">User</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="exportIp" checked>
                                        <label class="form-check-label" for="exportIp">IP Address</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="exportTimestamp" checked>
                                        <label class="form-check-label" for="exportTimestamp">Timestamp</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary">
                        <i class="bi-download me-1"></i> Export
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Export Modal -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<?php include '../includes/department/footer.php'; ?>

<!-- JS Implementing Plugins -->
<script src="../assets/js/vendor.min.js"></script>
<script src="../assets/vendor/flatpickr/dist/flatpickr.min.js"></script>
<script src="../assets/vendor/daterangepicker/moment.min.js"></script>
<script src="../assets/vendor/daterangepicker/daterangepicker.js"></script>

<!-- JS Front -->
<script src="../assets/js/theme.min.js"></script>

<!-- JS Plugins Init. -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize date range picker
        $('.js-range-datepicker').daterangepicker();

        // Filter form submission
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            // Here you would typically make an AJAX call to filter the logs
            // For demo purposes, we'll just log the form data
            const formData = $(this).serializeArray();
            console.log('Filtering with:', formData);
            // Close the dropdown
            $('.dropdown-menu').removeClass('show');
        });

        // Export modal form submission
        $('#exportModal .btn-primary').on('click', function() {
            const format = $('#exportFormat').val();
            const dateRange = $('#exportDateRange').val();
            // Here you would typically make an AJAX call to export the logs
            // For demo purposes, we'll just log the export settings
            console.log(`Exporting as ${format} for date range: ${dateRange}`);
            $('#exportModal').modal('hide');
        });

        // Log detail modal would be populated dynamically based on which log was clicked
        $('#logDetailModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget); // Button that triggered the modal
            // Extract info from data-* attributes or from the table row
            // For demo purposes, we'll just use the static content
        });
    });
</script>