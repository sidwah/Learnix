<?php // department/announcements.php ?>
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
    <div class="navbar-sidebar-aside-content content-space-1 content-space-md-2 px-lg-5 px-xl-5">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item"><a class="breadcrumb-link" href="index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Announcements</li>
                        </ol>
                    </nav>

                    <h1 class="page-header-title">Announcements</h1>
                    <p class="page-header-text">Manage department and course announcements</p>
                </div>

                <div class="col-sm-auto">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAnnouncementModal">
                        <i class="bi-plus me-1"></i> Create Announcement
                    </button>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <span class="h4 d-block mb-1">24</span>
                                <span class="d-block fs-5">Total</span>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-soft-primary icon-circle">
                                    <i class="bi-megaphone"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <span class="h4 d-block mb-1">3</span>
                                <span class="d-block fs-5">Active</span>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-soft-success icon-circle">
                                    <i class="bi-check-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <span class="h4 d-block mb-1">2</span>
                                <span class="d-block fs-5">Scheduled</span>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-soft-warning icon-circle">
                                    <i class="bi-clock"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <span class="h4 d-block mb-1">1</span>
                                <span class="d-block fs-5">Drafts</span>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-soft-secondary icon-circle">
                                    <i class="bi-file-earmark-text"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Stats Cards -->

        <!-- Main Content Card -->
        <div class="card">
            <!-- Nav Tabs -->
            <div class="card-header">
                <ul class="nav nav-segment nav-fill" id="announcementTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="published-tab" data-bs-toggle="pill" data-bs-target="#published" type="button" role="tab" aria-controls="published" aria-selected="true">
                            <i class="bi-check-circle me-1"></i>
                            Published
                            <span class="badge bg-success ms-1">3</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="scheduled-tab" data-bs-toggle="pill" data-bs-target="#scheduled" type="button" role="tab" aria-controls="scheduled" aria-selected="false">
                            <i class="bi-clock me-1"></i>
                            Scheduled
                            <span class="badge bg-warning ms-1">2</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="drafts-tab" data-bs-toggle="pill" data-bs-target="#drafts" type="button" role="tab" aria-controls="drafts" aria-selected="false">
                            <i class="bi-file-earmark-text me-1"></i>
                            Drafts
                            <span class="badge bg-secondary ms-1">1</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="archived-tab" data-bs-toggle="pill" data-bs-target="#archived" type="button" role="tab" aria-controls="archived" aria-selected="false">
                            <i class="bi-archive me-1"></i>
                            Archived
                            <span class="badge bg-dark ms-1">18</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Tab Content -->
            <div class="tab-content" id="announcementTabContent">
                <!-- Published Tab -->
                <div class="tab-pane fade show active" id="published" role="tabpanel" aria-labelledby="published-tab">
                    <div class="card-body">
                        <!-- Search and Filters for Published -->
                        <div class="row align-items-end mb-4">
                            <div class="col-md-4">
                                <label for="publishedSearch" class="form-label">Search Published</label>
                                <div class="input-group">
                                    <input type="search" class="form-control" id="publishedSearch" placeholder="Search announcements...">
                                    <button class="btn btn-outline-secondary" type="button">
                                        <i class="bi-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="publishedImportance" class="form-label">Importance</label>
                                <select class="form-select" id="publishedImportance">
                                    <option value="">All Importance</option>
                                    <option value="critical">Critical</option>
                                    <option value="high">High</option>
                                    <option value="medium">Medium</option>
                                    <option value="low">Low</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="publishedAudience" class="form-label">Audience</label>
                                <select class="form-select" id="publishedAudience">
                                    <option value="">All Audiences</option>
                                    <option value="department">Department</option>
                                    <option value="instructors">Instructors</option>
                                    <option value="students">Students</option>
                                    <option value="course">Course Specific</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-secondary w-100" onclick="clearFilters('published')">
                                    Clear
                                </button>
                            </div>
                        </div>

                        <!-- Published Announcements List -->
                        <div class="list-group list-group-flush">
                            <!-- Critical Published Announcement -->
                            <div class="list-group-item alert-soft-danger">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="badge bg-danger me-2">Critical</span>
                                            <small class="text-muted">Published 2 hours ago</small>
                                        </div>
                                        
                                        <h5 class="mb-1">Emergency System Maintenance Notice</h5>
                                        <p class="mb-2 text-muted">The learning management system will undergo emergency maintenance tonight from 11 PM to 3 AM...</p>
                                        
                                        <div class="d-flex align-items-center text-muted small">
                                            <span class="me-3"><i class="bi-people me-1"></i> 247 recipients</span>
                                            <span class="me-3"><i class="bi-eye me-1"></i> 198 read (80%)</span>
                                            <span class="me-3"><i class="bi-geo-alt me-1"></i> Department-wide</span>
                                        </div>
                                    </div>
                                    
                                    <div class="col-auto">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-ghost-secondary btn-icon btn-sm rounded-circle" data-bs-toggle="dropdown">
                                                <i class="bi-three-dots-vertical"></i>
                                            </button>
                                            
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="#" onclick="showActionModal('view', 1, 'Emergency System Maintenance Notice')">
                                                    <i class="bi-eye dropdown-item-icon"></i> View Details
                                                </a>
                                                <a class="dropdown-item" href="#" onclick="showActionModal('edit', 1, 'Emergency System Maintenance Notice')">
                                                    <i class="bi-pencil dropdown-item-icon"></i> Edit
                                                </a>
                                                <a class="dropdown-item" href="#" onclick="showActionModal('duplicate', 1, 'Emergency System Maintenance Notice')">
                                                    <i class="bi-files dropdown-item-icon"></i> Duplicate
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="#" onclick="showActionModal('archive', 1, 'Emergency System Maintenance Notice')">
                                                    <i class="bi-archive dropdown-item-icon"></i> Archive
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- High Priority Published -->
                            <div class="list-group-item alert-soft-warning">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="badge bg-warning text-dark me-2">High</span>
                                            <small class="text-muted">Published yesterday</small>
                                        </div>
                                        
                                        <h5 class="mb-1">Course Registration Opens Monday</h5>
                                        <p class="mb-2 text-muted">Course registration for the Spring 2024 semester opens this Monday at 8:00 AM...</p>
                                        
                                        <div class="d-flex align-items-center text-muted small">
                                            <span class="me-3"><i class="bi-people me-1"></i> 523 recipients</span>
                                            <span class="me-3"><i class="bi-eye me-1"></i> 445 read (85%)</span>
                                            <span class="me-3"><i class="bi-tag me-1"></i> Students only</span>
                                        </div>
                                    </div>
                                    
                                    <div class="col-auto">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-ghost-secondary btn-icon btn-sm rounded-circle" data-bs-toggle="dropdown">
                                                <i class="bi-three-dots-vertical"></i>
                                            </button>
                                            
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="#" onclick="showActionModal('view', 2, 'Course Registration Opens Monday')">
                                                    <i class="bi-eye dropdown-item-icon"></i> View Details
                                                </a>
                                                <a class="dropdown-item" href="#" onclick="showActionModal('edit', 2, 'Course Registration Opens Monday')">
                                                    <i class="bi-pencil dropdown-item-icon"></i> Edit
                                                </a>
                                                <a class="dropdown-item" href="#" onclick="showActionModal('duplicate', 2, 'Course Registration Opens Monday')">
                                                    <i class="bi-files dropdown-item-icon"></i> Duplicate
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="#" onclick="showActionModal('archive', 2, 'Course Registration Opens Monday')">
                                                    <i class="bi-archive dropdown-item-icon"></i> Archive
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Medium Priority Published -->
                            <div class="list-group-item alert-soft-primary">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="badge bg-primary me-2">Medium</span>
                                            <small class="text-muted">Published 2 days ago</small>
                                        </div>
                                        
                                        <h5 class="mb-1">New Course: Advanced Data Analytics</h5>
                                        <p class="mb-2 text-muted">We're excited to announce a new course offering in our Computer Science department...</p>
                                        
                                        <div class="d-flex align-items-center text-muted small">
                                            <span class="me-3"><i class="bi-people me-1"></i> 156 recipients</span>
                                            <span class="me-3"><i class="bi-eye me-1"></i> 89 read (57%)</span>
                                            <span class="me-3"><i class="bi-book me-1"></i> Course: CS-401</span>
                                            <span class="me-3"><i class="bi-paperclip me-1"></i> 2 attachments</span>
                                        </div>
                                    </div>
                                    
                                    <div class="col-auto">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-ghost-secondary btn-icon btn-sm rounded-circle" data-bs-toggle="dropdown">
                                                <i class="bi-three-dots-vertical"></i>
                                            </button>
                                            
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="#" onclick="showActionModal('view', 3, 'New Course: Advanced Data Analytics')">
                                                    <i class="bi-eye dropdown-item-icon"></i> View Details
                                                </a>
                                                <a class="dropdown-item" href="#" onclick="showActionModal('edit', 3, 'New Course: Advanced Data Analytics')">
                                                    <i class="bi-pencil dropdown-item-icon"></i> Edit
                                                </a>
                                                <a class="dropdown-item" href="#" onclick="showActionModal('duplicate', 3, 'New Course: Advanced Data Analytics')">
                                                    <i class="bi-files dropdown-item-icon"></i> Duplicate
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="#" onclick="showActionModal('archive', 3, 'New Course: Advanced Data Analytics')">
                                                    <i class="bi-archive dropdown-item-icon"></i> Archive
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scheduled Tab -->
                <div class="tab-pane fade" id="scheduled" role="tabpanel" aria-labelledby="scheduled-tab">
                    <div class="card-body">
                        <!-- Search for Scheduled -->
                        <div class="row align-items-end mb-4">
                            <div class="col-md-6">
                                <label for="scheduledSearch" class="form-label">Search Scheduled</label>
                                <div class="input-group">
                                    <input type="search" class="form-control" id="scheduledSearch" placeholder="Search scheduled announcements...">
                                    <button class="btn btn-outline-secondary" type="button">
                                        <i class="bi-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="scheduledDate" class="form-label">Scheduled Date</label>
                                <input type="date" class="form-control" id="scheduledDate">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-secondary w-100" onclick="clearFilters('scheduled')">
                                    Clear
                                </button>
                            </div>
                        </div>

                        <!-- Scheduled Announcements List -->
                        <div class="list-group list-group-flush">
                            <div class="list-group-item alert-soft-info">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="badge bg-warning text-dark me-2">High</span>
                                            <span class="badge bg-primary me-2">Tomorrow 9:00 AM</span>
                                        </div>
                                        
                                        <h5 class="mb-1">Course Registration Deadline Reminder</h5>
                                        <p class="mb-2 text-muted">Final reminder that course registration closes on Friday, December 15th at 11:59 PM...</p>
                                        
                                        <div class="d-flex align-items-center text-muted small">
                                            <span class="me-3"><i class="bi-people me-1"></i> 523 recipients</span>
                                            <span class="me-3"><i class="bi-tag me-1"></i> Students only</span>
                                            <span class="me-3"><i class="bi-calendar me-1"></i> Dec 14, 2024 09:00</span>
                                        </div>
                                    </div>
                                    
                                    <div class="col-auto">
                                        <button type="button" class="btn btn-success btn-sm me-2" onclick="showActionModal('sendNow', 4, 'Course Registration Deadline Reminder')">
                                            <i class="bi-send me-1"></i> Send Now
                                        </button>
                                        
                                        <div class="dropdown d-inline">
                                            <button type="button" class="btn btn-ghost-secondary btn-icon btn-sm rounded-circle" data-bs-toggle="dropdown">
                                                <i class="bi-three-dots-vertical"></i>
                                            </button>
                                            
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="#" onclick="showActionModal('edit', 4, 'Course Registration Deadline Reminder')">
                                                    <i class="bi-pencil dropdown-item-icon"></i> Edit
                                                </a>
                                                <a class="dropdown-item" href="#" onclick="showActionModal('reschedule', 4, 'Course Registration Deadline Reminder')">
                                                    <i class="bi-clock dropdown-item-icon"></i> Reschedule
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="#" onclick="showActionModal('cancel', 4, 'Course Registration Deadline Reminder')">
                                                    <i class="bi-x-circle dropdown-item-icon"></i> Cancel
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Drafts Tab -->
                <div class="tab-pane fade" id="drafts" role="tabpanel" aria-labelledby="drafts-tab">
                    <div class="card-body">
                        <!-- Search for Drafts -->
                        <div class="row align-items-end mb-4">
                            <div class="col-md-8">
                                <label for="draftsSearch" class="form-label">Search Drafts</label>
                                <div class="input-group">
                                    <input type="search" class="form-control" id="draftsSearch" placeholder="Search draft announcements...">
                                    <button class="btn btn-outline-secondary" type="button">
                                        <i class="bi-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-secondary w-100" onclick="clearFilters('drafts')">
                                    Clear
                                </button>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-danger w-100" onclick="showActionModal('deleteAll', null, 'All Drafts')">
                                    Delete All
                                </button>
                            </div>
                        </div>

                        <!-- Draft Announcements List -->
                        <div class="list-group list-group-flush">
                            <div class="list-group-item alert-soft-light">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="badge bg-secondary me-2">Medium</span>
                                            <span class="badge bg-light text-dark me-2">Draft</span>
                                            <small class="text-muted">Last edited 3 hours ago</small>
                                        </div>
                                        
                                        <h5 class="mb-1">Department Meeting - December Updates</h5>
                                        <p class="mb-2 text-muted">Monthly department meeting to discuss course updates, new policies, and upcoming events...</p>
                                        
                                        <div class="d-flex align-items-center text-muted small">
                                            <span class="me-3"><i class="bi-people me-1"></i> 43 recipients (when published)</span>
                                            <span class="me-3"><i class="bi-tag me-1"></i> Faculty only</span>
                                        </div>
                                    </div>
                                    
                                    <div class="col-auto">
                                        <button type="button" class="btn btn-primary btn-sm me-2" onclick="showActionModal('publish', 5, 'Department Meeting - December Updates')">
                                            <i class="bi-send me-1"></i> Publish
                                        </button>
                                        
                                        <div class="dropdown d-inline">
                                            <button type="button" class="btn btn-ghost-secondary btn-icon btn-sm rounded-circle" data-bs-toggle="dropdown">
                                                <i class="bi-three-dots-vertical"></i>
                                            </button>
                                            
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="#" onclick="showActionModal('edit', 5, 'Department Meeting - December Updates')">
                                                    <i class="bi-pencil dropdown-item-icon"></i> Edit
                                                </a>
                                                <a class="dropdown-item" href="#" onclick="showActionModal('duplicate', 5, 'Department Meeting - December Updates')">
                                                    <i class="bi-files dropdown-item-icon"></i> Duplicate
                                                </a>
                                                <a class="dropdown-item" href="#" onclick="showActionModal('schedule', 5, 'Department Meeting - December Updates')">
                                                    <i class="bi-clock dropdown-item-icon"></i> Schedule
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="#" onclick="showActionModal('delete', 5, 'Department Meeting - December Updates')">
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

                <!-- Archived Tab -->
                <div class="tab-pane fade" id="archived" role="tabpanel" aria-labelledby="archived-tab">
                    <div class="card-body">
                        <!-- Search for Archived -->
                        <div class="row align-items-end mb-4">
                            <div class="col-md-6">
                                <label for="archivedSearch" class="form-label">Search Archived</label>
                                <div class="input-group">
                                    <input type="search" class="form-control" id="archivedSearch" placeholder="Search archived announcements...">
                                    <button class="btn btn-outline-secondary" type="button">
                                        <i class="bi-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="archivedDate" class="form-label">Archive Date Range</label>
                                <input type="month" class="form-control" id="archivedDate">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-secondary w-100" onclick="clearFilters('archived')">
                                    Clear
                                </button>
                            </div>
                        </div>

                        <!-- Archived Announcements List -->
                        <div class="list-group list-group-flush">
                            <div class="list-group-item alert-soft-dark">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="badge bg-dark me-2">Archived</span>
                                            <small class="text-muted">Archived last week</small>
                                        </div>
                                        
                                        <h5 class="mb-1">Fall Semester Final Exams Schedule</h5>
                                        <p class="mb-2 text-muted">Final examination schedule for all courses in the Fall 2023 semester...</p>
                                        
                                        <div class="d-flex align-items-center text-muted small">
                                            <span class="me-3"><i class="bi-people me-1"></i> 892 recipients</span>
                                            <span class="me-3"><i class="bi-eye me-1"></i> 874 read (98%)</span>
                                            <span class="me-3"><i class="bi-calendar me-1"></i> Published Nov 15, 2023</span>
                                        </div>
                                    </div>
                                    
                                    <div class="col-auto">
                                        <button type="button" class="btn btn-outline-primary btn-sm me-2" onclick="showActionModal('restore', 6, 'Fall Semester Final Exams Schedule')">
                                            <i class="bi-arrow-clockwise me-1"></i> Restore
                                        </button>
                                        
                                        <div class="dropdown d-inline">
                                            <button type="button" class="btn btn-ghost-secondary btn-icon btn-sm rounded-circle" data-bs-toggle="dropdown">
                                                <i class="bi-three-dots-vertical"></i>
                                            </button>
                                            
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="#" onclick="showActionModal('view', 6, 'Fall Semester Final Exams Schedule')">
                                                    <i class="bi-eye dropdown-item-icon"></i> View
                                                </a>
                                                <a class="dropdown-item" href="#" onclick="showActionModal('duplicate', 6, 'Fall Semester Final Exams Schedule')">
                                                    <i class="bi-files dropdown-item-icon"></i> Duplicate
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="#" onclick="showActionModal('permanentDelete', 6, 'Fall Semester Final Exams Schedule')">
                                                    <i class="bi-trash dropdown-item-icon"></i> Delete Permanently
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Main Content Card -->
    </div>
<!-- End Content -->

   <!-- Create Announcement Modal -->
   <div class="modal fade" id="createAnnouncementModal" tabindex="-1" aria-labelledby="createAnnouncementModalLabel" aria-hidden="true">
       <div class="modal-dialog modal-lg">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title" id="createAnnouncementModalLabel">Create New Announcement</h5>
                   <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               
               <div class="modal-body">
                   <form id="createAnnouncementForm">
                       <!-- Title -->
                       <div class="mb-4">
                           <label for="announcementTitle" class="form-label">Title <span class="text-danger">*</span></label>
                           <input type="text" class="form-control" id="announcementTitle" placeholder="Enter announcement title" required>
                       </div>

                       <!-- Audience & Course Selection -->
                       <div class="row mb-4">
                           <div class="col-md-6">
                               <label for="audienceType" class="form-label">Audience <span class="text-danger">*</span></label>
                               <select class="form-select" id="audienceType" required>
                                   <option value="">Select audience</option>
                                   <option value="department">Entire Department</option>
                                   <option value="instructors">Instructors Only</option>
                                   <option value="students">Students Only</option>
                                   <option value="course">Specific Course</option>
                               </select>
                           </div>
                           
                           <div class="col-md-6">
                               <label for="courseSelect" class="form-label">Course (if applicable)</label>
                               <select class="form-select" id="courseSelect" disabled>
                                   <option value="">Select course</option>
                                   <option value="1">CS-101: Introduction to Programming</option>
                                   <option value="2">CS-201: Data Structures</option>
                                   <option value="3">CS-301: Database Systems</option>
                               </select>
                           </div>
                       </div>

                       <!-- Importance & Status -->
                       <div class="row mb-4">
                           <div class="col-md-6">
                               <label for="importance" class="form-label">Importance <span class="text-danger">*</span></label>
                               <select class="form-select" id="importance" required>
                                   <option value="">Select importance</option>
                                   <option value="critical">Critical</option>
                                   <option value="high">High</option>
                                   <option value="medium">Medium</option>
                                   <option value="low">Low</option>
                               </select>
                           </div>
                           
                           <div class="col-md-6">
                               <label for="announcementStatus" class="form-label">Status</label>
                               <select class="form-select" id="announcementStatus">
                                   <option value="draft">Save as Draft</option>
                                   <option value="published">Publish Now</option>
                                   <option value="scheduled">Schedule for Later</option>
                               </select>
                           </div>
                       </div>

                       <!-- Schedule DateTime (hidden by default) -->
                       <div class="mb-4 d-none" id="scheduleSection">
                           <label for="scheduleDateTime" class="form-label">Schedule Date & Time</label>
                           <input type="datetime-local" class="form-control" id="scheduleDateTime">
                       </div>

                       <!-- Content -->
                       <div class="mb-4">
                           <label for="announcementContent" class="form-label">Content <span class="text-danger">*</span></label>
                           <textarea class="form-control" id="announcementContent" rows="6" placeholder="Enter announcement content..." required></textarea>
                           <div class="form-text">You can use basic formatting and links in your announcement.</div>
                       </div>

                       <!-- File Attachments -->
                       <div class="mb-4">
                           <label for="attachments" class="form-label">Attachments</label>
                           <input type="file" class="form-control" id="attachments" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                           <div class="form-text">You can attach multiple files (PDF, DOC, images). Max 10MB per file.</div>
                       </div>

                       <!-- Expiry Date -->
                       <div class="mb-4">
                           <label for="expiryDate" class="form-label">Expiry Date (Optional)</label>
                           <input type="datetime-local" class="form-control" id="expiryDate">
                           <div class="form-text">The announcement will be automatically archived after this date.</div>
                       </div>

                       <!-- Preview Section -->
                       <div class="border rounded p-3 bg-light">
                           <h6 class="mb-2">Preview:</h6>
                           <div id="announcementPreview" class="text-muted">
                               Preview will appear here as you type...
                           </div>
                       </div>
                   </form>
               </div>
               
               <div class="modal-footer">
                   <button type="button" class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button>
                   <button type="button" class="btn btn-outline-primary" onclick="showActionModal('saveDraft', null, 'New Announcement')">Save as Draft</button>
                   <button type="button" class="btn btn-primary" onclick="showActionModal('publishNew', null, 'New Announcement')">Publish Announcement</button>
               </div>
           </div>
       </div>
   </div>
   <!-- End Create Announcement Modal -->

   <!-- Action Confirmation Modal -->
   <div class="modal fade" id="actionModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
       <div class="modal-dialog modal-dialog-centered">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title" id="actionModalLabel">Confirm Action</h5>
                   <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <div class="modal-body">
                   <div class="text-center">
                       <div class="mb-3">
                           <i id="actionIcon" class="text-warning" style="font-size: 3rem;"></i>
                       </div>
                       <h6 id="actionTitle">Are you sure?</h6>
                       <p id="actionMessage" class="text-muted mb-0">This action cannot be undone.</p>
                       <div id="actionDetails" class="mt-3 p-3 bg-light rounded d-none">
                           <small class="text-muted" id="actionDetailsText"></small>
                       </div>
                   </div>
               </div>
               <div class="modal-footer">
                   <button type="button" class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button>
                   <button type="button" class="btn" id="confirmActionBtn">Confirm</button>
               </div>
           </div>
       </div>
   </div>
   <!-- End Action Confirmation Modal -->

   <!-- Toast Container -->
   <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
       <!-- Success Toast Template -->
       <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
           <div class="d-flex">
               <div class="toast-body">
                   <i class="bi-check-circle me-2"></i>
                   <span id="successMessage">Action completed successfully!</span>
               </div>
               <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
           </div>
       </div>

       <!-- Error Toast Template -->
       <div id="errorToast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
           <div class="d-flex">
               <div class="toast-body">
                   <i class="bi-exclamation-triangle me-2"></i>
                   <span id="errorMessage">An error occurred. Please try again.</span>
               </div>
               <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
           </div>
       </div>

       <!-- Warning Toast Template -->
       <div id="warningToast" class="toast align-items-center text-white bg-warning border-0" role="alert" aria-live="assertive" aria-atomic="true">
           <div class="d-flex">
               <div class="toast-body">
                   <i class="bi-exclamation-circle me-2"></i>
                   <span id="warningMessage">Please check your input.</span>
               </div>
               <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
           </div>
       </div>

       <!-- Info Toast Template -->
       <div id="infoToast" class="toast align-items-center text-white bg-info border-0" role="alert" aria-live="assertive" aria-atomic="true">
           <div class="d-flex">
               <div class="toast-body">
                   <i class="bi-info-circle me-2"></i>
                   <span id="infoMessage">Information message.</span>
               </div>
               <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
           </div>
       </div>
   </div>
   <!-- End Toast Container -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<script>
// Global variables for action handling
let currentAction = null;
let currentId = null;
let currentTitle = null;

// Toast Functions
function showToast(type, message) {
   const toastElement = document.getElementById(type + 'Toast');
   const messageElement = document.getElementById(type + 'Message');
   
   if (toastElement && messageElement) {
       messageElement.textContent = message;
       const toast = new bootstrap.Toast(toastElement, {
           autohide: true,
           delay: 5000
       });
       toast.show();
   }
}

// Action Modal Configuration
const actionConfigs = {
   view: {
       title: 'View Announcement',
       message: 'Opening announcement details...',
       icon: 'bi-eye text-info',
       btnClass: 'btn-primary',
       btnText: 'View',
       showDetails: false
   },
   edit: {
       title: 'Edit Announcement',
       message: 'This will open the announcement editor.',
       icon: 'bi-pencil text-info',
       btnClass: 'btn-primary',
       btnText: 'Edit',
       showDetails: false
   },
   duplicate: {
       title: 'Duplicate Announcement',
       message: 'This will create a copy of this announcement as a draft.',
       icon: 'bi-files text-info',
       btnClass: 'btn-primary',
       btnText: 'Duplicate',
       showDetails: true,
       details: 'The duplicated announcement will be saved as a draft and can be modified before publishing.'
   },
   archive: {
       title: 'Archive Announcement',
       message: 'This announcement will be moved to the archived section.',
       icon: 'bi-archive text-warning',
       btnClass: 'btn-warning',
       btnText: 'Archive',
       showDetails: true,
       details: 'Archived announcements can be restored later if needed.'
   },
   sendNow: {
       title: 'Send Announcement Now',
       message: 'This will immediately send the scheduled announcement to all recipients.',
       icon: 'bi-send text-success',
       btnClass: 'btn-success',
       btnText: 'Send Now',
       showDetails: true,
       details: 'Recipients will receive the announcement immediately via their preferred notification method.'
   },
   cancel: {
       title: 'Cancel Scheduled Announcement',
       message: 'This will cancel the scheduled announcement and move it to drafts.',
       icon: 'bi-x-circle text-warning',
       btnClass: 'btn-warning',
       btnText: 'Cancel Schedule',
       showDetails: true,
       details: 'The announcement will be moved to drafts where you can edit or reschedule it.'
   },
   publish: {
       title: 'Publish Announcement',
       message: 'This will immediately publish the announcement to all selected recipients.',
       icon: 'bi-send text-success',
       btnClass: 'btn-success',
       btnText: 'Publish',
       showDetails: true,
       details: 'All recipients will be notified according to their notification preferences.'
   },
   schedule: {
       title: 'Schedule Announcement',
       message: 'This will open the scheduling options for this announcement.',
       icon: 'bi-clock text-info',
       btnClass: 'btn-primary',
       btnText: 'Schedule',
       showDetails: false
   },
   delete: {
       title: 'Delete Announcement',
       message: 'This will permanently delete this announcement.',
       icon: 'bi-trash text-danger',
       btnClass: 'btn-danger',
       btnText: 'Delete',
       showDetails: true,
       details: 'This action cannot be undone. All associated data will be permanently removed.'
   },
   deleteAll: {
       title: 'Delete All Drafts',
       message: 'This will permanently delete ALL draft announcements.',
       icon: 'bi-trash text-danger',
       btnClass: 'btn-danger',
       btnText: 'Delete All',
       showDetails: true,
       details: 'This action cannot be undone. All draft announcements will be permanently removed.'
   },
   restore: {
       title: 'Restore Announcement',
       message: 'This will restore the announcement to the published section.',
       icon: 'bi-arrow-clockwise text-success',
       btnClass: 'btn-success',
       btnText: 'Restore',
       showDetails: true,
       details: 'The announcement will be moved back to the published section and become visible again.'
   },
   permanentDelete: {
       title: 'Permanently Delete',
       message: 'This will permanently delete this announcement from the archive.',
       icon: 'bi-trash text-danger',
       btnClass: 'btn-danger',
       btnText: 'Delete Permanently',
       showDetails: true,
       details: 'This action cannot be undone. All data including engagement metrics will be permanently lost.'
   },
   reschedule: {
       title: 'Reschedule Announcement',
       message: 'This will open the rescheduling options.',
       icon: 'bi-clock text-info',
       btnClass: 'btn-primary',
       btnText: 'Reschedule',
       showDetails: false
   },
   saveDraft: {
       title: 'Save as Draft',
       message: 'This will save the announcement as a draft.',
       icon: 'bi-file-earmark-text text-info',
       btnClass: 'btn-primary',
       btnText: 'Save Draft',
       showDetails: true,
       details: 'You can continue editing the draft later or publish it when ready.'
   },
   publishNew: {
       title: 'Publish New Announcement',
       message: 'This will publish the announcement immediately.',
       icon: 'bi-send text-success',
       btnClass: 'btn-success',
       btnText: 'Publish',
       showDetails: true,
       details: 'The announcement will be sent to all selected recipients based on the current settings.'
   }
};

// Show Action Modal
function showActionModal(action, id, title) {
   currentAction = action;
   currentId = id;
   currentTitle = title;
   
   const config = actionConfigs[action];
   if (!config) return;
   
   // Update modal content
   document.getElementById('actionModalLabel').textContent = config.title;
   document.getElementById('actionTitle').textContent = config.title;
   document.getElementById('actionMessage').textContent = config.message;
   document.getElementById('actionIcon').className = config.icon;
   
   const confirmBtn = document.getElementById('confirmActionBtn');
   confirmBtn.className = `btn ${config.btnClass}`;
   confirmBtn.textContent = config.btnText;
   
   // Show/hide details
   const detailsDiv = document.getElementById('actionDetails');
   if (config.showDetails && config.details) {
       document.getElementById('actionDetailsText').textContent = config.details;
       detailsDiv.classList.remove('d-none');
   } else {
       detailsDiv.classList.add('d-none');
   }
   
   // Show modal
   const modal = new bootstrap.Modal(document.getElementById('actionModal'));
   modal.show();
   
   // Update confirm button handler
   confirmBtn.onclick = function() {
       executeAction(action, id, title);
       modal.hide();
   };
}

// Execute Action
function executeAction(action, id, title) {
   switch(action) {
       case 'view':
           showToast('info', `Opening details for: ${title}`);
           // Implement view logic
           break;
           
       case 'edit':
           showToast('info', `Opening editor for: ${title}`);
           // Implement edit logic
           break;
           
       case 'duplicate':
           showToast('success', `Announcement duplicated successfully!`);
           // Implement duplicate logic and update UI
           break;
           
       case 'archive':
           showToast('success', `"${title}" archived successfully!`);
           // Remove from current view and update counters
           break;
           
       case 'sendNow':
           showToast('success', `"${title}" sent successfully!`);
           // Move from scheduled to published tab
           break;
           
       case 'cancel':
           showToast('success', `Scheduled announcement cancelled!`);
           // Move from scheduled to drafts tab
           break;
           
       case 'publish':
           showToast('success', `"${title}" published successfully!`);
           // Move from drafts to published tab
           break;
           
       case 'schedule':
           showToast('info', `Opening scheduler for: ${title}`);
           // Implement schedule logic
           break;
           
       case 'delete':
           showToast('success', `"${title}" deleted successfully!`);
           // Remove from drafts list
           break;
           
       case 'deleteAll':
           showToast('success', `All drafts deleted successfully!`);
           // Clear drafts list and update counter
           break;
           
       case 'restore':
           showToast('success', `"${title}" restored successfully!`);
           // Move from archived to published tab
           break;
           
       case 'permanentDelete':
           showToast('success', `"${title}" permanently deleted!`);
           // Remove from archived list
           break;
           
       case 'reschedule':
           showToast('info', `Opening reschedule options for: ${title}`);
           // Implement reschedule logic
           break;
           
       case 'saveDraft':
           const form = document.getElementById('createAnnouncementForm');
           if (form.checkValidity()) {
               showToast('success', 'Announcement saved as draft!');
               bootstrap.Modal.getInstance(document.getElementById('createAnnouncementModal')).hide();
               // Update drafts tab counter and list
           } else {
               showToast('warning', 'Please fill in all required fields');
           }
           break;
           
       case 'publishNew':
           const publishForm = document.getElementById('createAnnouncementForm');
           if (publishForm.checkValidity()) {
               const status = document.getElementById('announcementStatus').value;
               if (status === 'scheduled') {
                   showToast('success', 'Announcement scheduled successfully!');
               } else {
                   showToast('success', 'Announcement published successfully!');
               }
               bootstrap.Modal.getInstance(document.getElementById('createAnnouncementModal')).hide();
               // Update appropriate tab counter and list
           } else {
               showToast('warning', 'Please fill in all required fields');
           }
           break;
           
       default:
           showToast('error', 'Unknown action');
   }
}

// Filter Functions
function clearFilters(tab) {
   const searchInput = document.getElementById(tab + 'Search');
   if (searchInput) searchInput.value = '';
   
   const selects = document.querySelectorAll(`#${tab} select`);
   selects.forEach(select => select.value = '');
   
   const dateInputs = document.querySelectorAll(`#${tab} input[type="date"], #${tab} input[type="month"]`);
   dateInputs.forEach(input => input.value = '');
   
   showToast('info', 'Filters cleared');
}

// Form Handlers
document.getElementById('audienceType').addEventListener('change', function() {
   const courseSelect = document.getElementById('courseSelect');
   if (this.value === 'course') {
       courseSelect.disabled = false;
       courseSelect.required = true;
   } else {
       courseSelect.disabled = true;
       courseSelect.required = false;
       courseSelect.value = '';
   }
});

document.getElementById('announcementStatus').addEventListener('change', function() {
   const scheduleSection = document.getElementById('scheduleSection');
   if (this.value === 'scheduled') {
       scheduleSection.classList.remove('d-none');
       document.getElementById('scheduleDateTime').required = true;
   } else {
       scheduleSection.classList.add('d-none');
       document.getElementById('scheduleDateTime').required = false;
   }
});

// Live preview update
document.getElementById('announcementContent').addEventListener('input', function() {
   const preview = document.getElementById('announcementPreview');
   const content = this.value;
   if (content.trim()) {
       preview.innerHTML = content.replace(/\n/g, '<br>');
       preview.classList.remove('text-muted');
   } else {
       preview.innerHTML = 'Preview will appear here as you type...';
       preview.classList.add('text-muted');
   }
});

// Initialize Bootstrap components
document.addEventListener('DOMContentLoaded', function() {
   // Initialize tooltips if any
   var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
   var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
       return new bootstrap.Tooltip(tooltipTriggerEl);
   });
});
</script>

<?php include '../includes/department/footer.php'; ?>