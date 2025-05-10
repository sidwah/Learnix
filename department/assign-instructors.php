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
    <div class="navbar-sidebar-aside-content content-space-1 content-space-md-2 px-lg-5 px-xl-10" style="background: linear-gradient(135deg, #F8F9FA, #E7F0FA);">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2 mb-0 text-dark" style="font-weight: 600; letter-spacing: 0.5px;">Assign Instructors to Course</h1>
            <a href="../department/dashboard.php" class="btn btn-outline-dark btn-sm rounded-pill">
                <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-5">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(145deg, #E7F0FA, #D4E4FA); border-radius: 15px; transition: transform 0.3s;">
                    <div class="card-body text-center">
                        <i class="bi bi-book-fill text-primary" style="font-size: 2rem;"></i>
                        <h4 class="card-title mt-2 mb-1">Total Courses</h4>
                        <p class="display-6 text-dark" style="font-weight: 700;">25</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(145deg, #F5E9F9, #E8D4F4); border-radius: 15px; transition: transform 0.3s;">
                    <div class="card-body text-center">
                        <i class="bi bi-person-check-fill text-purple" style="font-size: 2rem;"></i>
                        <h4 class="card-title mt-2 mb-1">Assigned Instructors</h4>
                        <p class="display-6 text-dark" style="font-weight: 700;">42</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(145deg, #D4F4E2, #C2E8D4); border-radius: 15px; transition: transform 0.3s;">
                    <div class="card-body text-center">
                        <i class="bi bi-bell-fill text-success" style="font-size: 2rem;"></i>
                        <h4 class="card-title mt-2 mb-1">Pending Actions</h4>
                        <p class="display-6 text-dark" style="font-weight: 700;">3</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="row g-4">
            <!-- Course Selection Section -->
            <div class="col-lg-4">
                <div class="card border-0 h-100" style="background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); border-radius: 15px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);">
                    <div class="card-header border-0 bg-transparent">
                        <h4 class="card-title mb-0 text-dark" style="font-weight: 500;">Select Course</h4>
                    </div>
                    <div class="card-body">
                        <!-- Course Dropdown -->
                        <div class="mb-4">
                            <label for="courseSelect" class="form-label text-dark">Course</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                                <select id="courseSelect" class="form-select border-start-0" aria-label="Select a course" style="border-radius: 0 10px 10px 0;">
                                    <option value="" disabled selected>Select a course...</option>
                                    <!-- Populated dynamically from courses table -->
                                    <option value="1">Introduction to Python</option>
                                    <option value="2">Data Science Fundamentals</option>
                                    <option value="3">Business Analytics</option>
                                </select>
                            </div>
                        </div>

                        <!-- Course Info Card -->
                        <div id="courseInfo" class="d-none p-3 rounded" style="background: linear-gradient(145deg, #E7F0FA, #D4E4FA);">
                            <h5 class="mb-3 text-dark">Course Details</h5>
                            <ul class="list-unstyled text-dark">
                                <li><strong>Title:</strong> <span id="courseTitle">Introduction to Python</span></li>
                                <li><strong>Course ID:</strong> <span id="courseId">1</span></li>
                                <li><strong>Department:</strong> <span id="courseDept">Technology</span></li>
                                <li><strong>Current Instructors:</strong> <span id="currentInstructors">None</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instructor Assignment Section -->
            <div class="col-lg-8">
                <div class="card border-0 h-100" style="background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); border-radius: 15px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);">
                    <div class="card-header border-0 bg-transparent d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0 text-dark" style="font-weight: 500;">Assign Instructors</h4>
                        <div class="input-group input-group-sm w-auto" style="max-width: 250px;">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control border-start-0" placeholder="Search instructors..." id="instructorSearch" style="border-radius: 0 10px 10px 0;">
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <!-- Instructor Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless">
                                <thead style="background: linear-gradient(145deg, #E7F0FA, #D4E4FA);">
                                    <tr>
                                        <th scope="col">
                                            <input type="checkbox" id="selectAllInstructors" class="form-check-input">
                                        </th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Instructor ID</th>
                                        <th scope="col">Department</th>
                                        <th scope="col">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="instructorTableBody">
                                    <!-- Populated dynamically from instructors and department_instructors tables -->
                                    <tr class="align-middle" style="transition: background 0.2s;">
                                        <td><input type="checkbox" class="instructorCheckbox form-check-input" value="1"></td>
                                        <td>John Doe</td>
                                        <td>101</td>
                                        <td>Technology</td>
                                        <td><span class="badge bg-success rounded-pill">Available</span></td>
                                    </tr>
                                    <tr class="align-middle" style="transition: background 0.2s;">
                                        <td><input type="checkbox" class="instructorCheckbox form-check-input" value="2"></td>
                                        <td>Jane Smith</td>
                                        <td>102</td>
                                        <td>Business</td>
                                        <td><span class="badge bg-warning rounded-pill">Assigned</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer border-0 bg-transparent">
                        <button class="btn btn-primary rounded-pill px-4" id="assignButton" disabled style="background: linear-gradient(145deg, #007BFF, #0056b3); border: none;">Assign Selected Instructors</button>
                    </div>
                </div>
            </div>

            <!-- Assignment Summary Section -->
            <div class="col-12">
                <div class="card border-0" style="background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); border-radius: 15px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);">
                    <div class="card-header border-0 bg-transparent">
                        <h4 class="card-title mb-0 text-dark" style="font-weight: 500;">Assigned Instructors</h4>
                    </div>
                    <div class="card-body">
                        <!-- Assigned Instructors List -->
                        <div id="assignedInstructorsList" class="row g-3">
                            <!-- Populated dynamically -->
                            <div class="col-md-4">
                                <div class="card card-sm border-0 shadow-sm" style="background: linear-gradient(145deg, #F5E9F9, #E8D4F4); border-radius: 10px; transition: transform 0.3s;">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-1 text-dark">John Doe (ID: 101)</h6>
                                            <small class="text-muted">Technology</small>
                                        </div>
                                        <button class="btn btn-danger btn-sm rounded-circle" data-instructor-id="1" style="width: 30px; height: 30px;">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Empty State -->
                        <div id="noInstructors" class="text-center d-none">
                            <p class="text-muted">No instructors assigned to this course.</p>
                            <i class="bi bi-person-x text-muted" style="font-size: 2rem;"></i>
                        </div>
                        <!-- Summary Stats -->
                        <div class="mt-4 d-flex justify-content-between">
                            <p class="text-dark"><strong>Total Assigned:</strong> <span id="totalAssigned">1</span></p>
                            <p class="text-dark"><strong>Last Updated:</strong> <span id="lastUpdated">2025-05-10</span></p>
                        </div>
                    </div>
                    <div class="card-footer border-0 bg-transparent text-end">
                        <button class="btn btn-success rounded-pill px-4" id="saveButton" style="background: linear-gradient(145deg, #28A745, #1e7e34); border: none;">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border-radius: 15px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);">
                    <div class="modal-header border-0">
                        <h5 class="modal-title text-dark" id="confirmModalLabel">Confirm Action</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-dark">
                        <p id="confirmMessage">You are about to assign 2 instructors to Introduction to Python. Continue?</p>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-dark rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary rounded-pill" id="confirmAction" style="background: linear-gradient(145deg, #007BFF, #0056b3); border: none;">Yes, Assign</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Container -->
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 1050;">
            <div id="toastNotification" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true" style="background: linear-gradient(145deg, #D4F4E2, #C2E8D4); border-radius: 10px;">
                <div class="d-flex">
                    <div class="toast-body text-dark"></div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Content -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<?php include '../includes/department/footer.php'; ?>