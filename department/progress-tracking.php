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
                    <h1 class="docs-page-header-title">Student Progress Tracking</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Summary Cards -->
        <div class="row mb-4">
            <!-- Total Enrollments Card -->
            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Total Enrollments</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="card-title text-inherit" id="totalEnrollments">0</h2>
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
            <!-- End Total Enrollments Card -->

            <!-- Completed Courses Card -->
            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Completed Courses</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="card-title text-inherit" id="completedCourses">0</h2>
                                <span class="text-body fs-6" id="completionPercentage">0%</span>
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
            <!-- End Completed Courses Card -->

            <!-- Active Enrollments Card -->
            <div class="col-sm-6 col-lg-3 mb-3 mb-sm-0">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Active Enrollments</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="card-title text-inherit" id="activeEnrollments">0</h2>
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
            <!-- End Active Enrollments Card -->

            <!-- Certificates Issued Card -->
            <div class="col-sm-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Certificates Issued</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="card-title text-inherit" id="certificatesIssued">0</h2>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-soft-info text-info p-2">
                                    <i class="bi-award-fill"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Certificates Issued Card -->
        </div>
        <!-- End Summary Cards -->

        <!-- Heading -->
        <h2 id="component-1" class="hs-docs-heading">
            Student Progress <a class="anchorjs-link" href="#component-1" aria-label="Anchor" data-anchorjs-icon="#"></a>
        </h2>
        <!-- End Heading -->

        <!-- Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center mb-3">
                <h4 class="card-header-title">Student Enrollments</h4>
                <div class="d-flex align-items-center">
                    <select id="statusFilter" class="form-select form-select-sm me-2">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                    </select>
                    <select id="courseFilter" class="form-select form-select-sm me-2">
                        <option value="all">All Courses</option>
                        <!-- Populated dynamically -->
                    </select>
                    <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="🔍 Search Student/Course...">
                </div>
            </div>

            <!-- Progress Table -->
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th class="sortable" data-sort="student">Student <span class="sort-icon">⇅</span></th>
                            <th class="sortable" data-sort="course">Course <span class="sort-icon">⇅</span></th>
                            <th class="sortable" data-sort="progress">Progress <span class="sort-icon">⇅</span></th>
                            <th class="sortable" data-sort="quiz_score">Quiz Score <span class="sort-icon">⇅</span></th>
                            <th class="sortable" data-sort="certificate">Certificate <span class="sort-icon">⇅</span></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="progressTableBody">
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

                .progress-bar {
                    transition: width 0.3s ease-in-out;
                }
            </style>

            <!-- Progress Details Modal -->
            <div class="modal fade" id="progressModal" tabindex="-1" aria-labelledby="progressModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="progressModalLabel">Student Progress Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Student Information -->
                            <div class="mb-4">
                                <h6>Student Information</h6>
                                <div class="d-flex align-items-center mb-3">
                                    <span class="avatar avatar-lg avatar-circle me-3" id="modalStudentAvatar">
                                        <span class="avatar-initials">S</span>
                                    </span>
                                    <div>
                                        <h5 class="mb-0" id="modalStudentName">Student Name</h5>
                                        <p class="mb-0 text-muted" id="modalStudentEmail">student@example.com</p>
                                        <small class="text-muted" id="modalEnrolledAt">Enrolled: Jan 01, 2023</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Course Information -->
                            <div class="mb-4">
                                <h6>Course Information</h6>
                                <div class="p-3 bg-soft-light rounded">
                                    <h5 class="mb-0" id="modalCourseTitle">Course Title</h5>
                                    <small class="text-muted" id="modalInstructorName">Instructor: Name</small>
                                </div>
                            </div>

                            <!-- Progress Details -->
                            <div class="mb-4">
                                <h6>Progress</h6>
                                <div class="progress mb-2">
                                    <div id="modalProgressBar" class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <p class="mb-0" id="modalProgressText">0% Complete</p>
                                <div id="modalTopicProgress" class="mt-3">
                                    <!-- Topic progress will be added here -->
                                </div>
                            </div>

                            <!-- Quiz Performance -->
                            <div class="mb-4">
                                <h6>Quiz Performance</h6>
                                <div id="modalQuizPerformance">
                                    <!-- Quiz attempts will be added here -->
                                </div>
                            </div>

                            <!-- Certificate Status -->
                            <div class="mb-4">
                                <h6>Certificate Status</h6>
                                <div id="modalCertificateStatus" class="badge bg-warning">Not Issued</div>
                                <div id="modalCertificateActions" class="mt-2">
                                    <!-- Certificate actions will be added here -->
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary d-none" id="issueCertificateBtn">Issue Certificate</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Progress Details Modal -->
        </div>
        <!-- End Card -->
    </div>
    <!-- End Content -->

    <!-- JavaScript for Progress Tracking -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let currentPage = 1;
            let enrollmentsPerPage = 10;
            let sortColumn = "enrolled_at";
            let sortDirection = "desc";
            let statusFilter = "all";
            let courseFilter = "all";
            let searchQuery = "";

            // Show alert notification function
            function showAlert(type, message) {
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
                alertDiv.setAttribute('role', 'alert');
                alertDiv.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                alertDiv.style.position = 'fixed';
                alertDiv.style.top = '20px';
                alertDiv.style.left = '50%';
                alertDiv.style.transform = 'translateX(-50%)';
                alertDiv.style.zIndex = '9999';
                alertDiv.style.minWidth = '300px';
                alertDiv.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
                document.body.appendChild(alertDiv);
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.classList.remove('show');
                        setTimeout(() => {
                            if (alertDiv.parentNode) {
                                alertDiv.parentNode.removeChild(alertDiv);
                            }
                        }, 300);
                    }
                }, 5000);
            }

            // Create and apply page overlay for loading effect
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

                const spinner = document.createElement('div');
                spinner.className = 'spinner-border text-primary';
                spinner.setAttribute('role', 'status');
                spinner.style.width = '3rem';
                spinner.style.height = '3rem';
                spinner.innerHTML = '<span class="visually-hidden">Loading...</span>';
                overlay.appendChild(spinner);

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

            // Fetch courses for filter dropdown
            function fetchCourses() {
                fetch('../backend/admin/progress-tracking.php?action=get_courses')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const courseSelect = document.getElementById('courseFilter');
                            data.courses.forEach(course => {
                                const option = document.createElement('option');
                                option.value = course.course_id;
                                option.textContent = course.title;
                                courseSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => console.error('Error fetching courses:', error));
            }

            // Fetch enrollment data
            function fetchEnrollments() {
                createOverlay("Loading student progress...");

                document.getElementById("progressTableBody").innerHTML = "";

                const params = new URLSearchParams({
                    action: 'get_enrollments',
                    page: currentPage,
                    per_page: enrollmentsPerPage,
                    sort_column: sortColumn,
                    sort_direction: sortDirection,
                    status: statusFilter,
                    course_id: courseFilter,
                    search: searchQuery
                });

                fetch(`../backend/admin/progress-tracking.php?${params}`)
                    .then(response => response.json())
                    .then(data => {
                        removeOverlay();

                        if (data.success) {
                            displayEnrollments(data.data);
                            createPagination(data.pagination);
                            updateSummaryCards(data.summary);
                        } else {
                            showAlert('danger', `Error: ${data.message}`);
                        }
                    })
                    .catch(error => {
                        removeOverlay();
                        console.error("Error fetching enrollments:", error);
                        showAlert('danger', "An error occurred while fetching data. Please try again.");
                    });
            }

            function updateSummaryCards(summary) {
                document.getElementById("totalEnrollments").textContent = summary.total;
                document.getElementById("completedCourses").textContent = summary.completed;
                document.getElementById("activeEnrollments").textContent = summary.active;
                document.getElementById("certificatesIssued").textContent = summary.certificates;

                const percentage = summary.total > 0 ? Math.round((summary.completed / summary.total) * 100) : 0;
                document.getElementById("completionPercentage").textContent = `${percentage}%`;
            }

            function formatDate(dateString) {
                const options = {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                };
                return new Date(dateString).toLocaleDateString(undefined, options);
            }

            function displayEnrollments(enrollments) {
                let tableBody = document.getElementById("progressTableBody");
                tableBody.innerHTML = "";

                if (enrollments.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="6" class="text-center text-muted">No enrollments found.</td></tr>`;
                    return;
                }

                enrollments.forEach(enrollment => {
                    const progress = enrollment.completion_percentage;
                    const status = progress >= 100 ? 'Completed' : 'Active';
                    const statusBadge = progress >= 100 ?
                        `<span class="badge bg-success">Completed</span>` :
                        `<span class="badge bg-warning">Active</span>`;

                    const quizScore = enrollment.avg_quiz_score ? `${enrollment.avg_quiz_score}%` : 'N/A';
                    const certificateStatus = enrollment.certificate_id ?
                        `<span class="badge bg-info">Issued</span>` :
                        `<span class="badge bg-warning">Not Issued</span>`;

                    const profilePicUrl = enrollment.profile_pic === 'default.png' ?
                        '../assets/img/160x160/img1.jpg' :
                        `../Uploads/instructor-profile/${enrollment.profile_pic}`;

                    tableBody.innerHTML += `
                        <tr id="row-${enrollment.enrollment_id}">
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-sm avatar-circle">
                                        <img src="${profilePicUrl}" alt="Profile" class="avatar-img">
                                    </span>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0">${enrollment.student_name}</h6>
                                        <small class="d-block text-muted">${enrollment.student_email}</small>
                                    </div>
                                </div>
                            </td>
                            <td>${enrollment.course_title}</td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: ${progress}%" aria-valuenow="${progress}" aria-valuemin="0" aria-valuemax="100">${progress}%</div>
                                </div>
                            </td>
                            <td>${quizScore}</td>
                            <td>${certificateStatus}</td>
                            <td>
                                <button class="btn btn-sm btn-soft-primary view-progress" data-id="${enrollment.enrollment_id}">
                                    <i class="bi-eye"></i>
                                </button>
                                ${!enrollment.certificate_id && progress >= 100 ? `
                                <button class="btn btn-sm btn-soft-success ms-2 issue-certificate" data-id="${enrollment.enrollment_id}">
                                    <i class="bi-award"></i>
                                </button>` : ''}
                            </td>
                        </tr>
                    `;
                });

                attachEventListeners();
                updateButtons();
            }

            function createPagination(pagination) {
                let paginationNumbers = document.getElementById("paginationNumbers");
                paginationNumbers.innerHTML = "";

                for (let i = 1; i <= pagination.total_pages; i++) {
                    let pageButton = document.createElement("button");
                    pageButton.className = `btn btn-sm ${i === pagination.current_page ? 'btn-primary' : 'btn-outline-primary'} mx-1`;
                    pageButton.innerText = i;
                    pageButton.dataset.page = i;
                    pageButton.onclick = function() {
                        currentPage = parseInt(this.dataset.page);
                        fetchEnrollments();
                    };
                    paginationNumbers.appendChild(pageButton);
                }
            }

            function updateButtons() {
                const prevButton = document.getElementById("prevPage");
                const nextButton = document.getElementById("nextPage");
                prevButton.disabled = (currentPage === 1);
            }

            function attachEventListeners() {
                document.querySelectorAll(".view-progress").forEach(button => {
                    button.addEventListener("click", function() {
                        let enrollmentId = this.getAttribute("data-id");
                        viewProgressDetails(enrollmentId);
                    });
                });

                document.querySelectorAll(".issue-certificate").forEach(button => {
                    button.addEventListener("click", function() {
                        let enrollmentId = this.getAttribute("data-id");
                        if (confirm("Are you sure you want to issue a certificate for this enrollment?")) {
                            issueCertificate(enrollmentId);
                        }
                    });
                });
            }

            function viewProgressDetails(enrollmentId) {
                createOverlay("Loading progress details...");

                fetch(`../backend/admin/progress-tracking.php?action=get_progress_details&enrollment_id=${enrollmentId}`)
                    .then(response => response.json())
                    .then(data => {
                        removeOverlay();

                        if (data.success) {
                            const enrollment = data.data;

                            document.getElementById("modalStudentName").textContent = enrollment.student_name;
                            document.getElementById("modalStudentEmail").textContent = enrollment.student_email;
                            document.getElementById("modalEnrolledAt").textContent = "Enrolled: " + formatDate(enrollment.enrolled_at);

                            const avatarContainer = document.getElementById("modalStudentAvatar");
                            avatarContainer.innerHTML = enrollment.profile_pic === 'default.png' ?
                                `<img src="../assets/img/160x160/img1.jpg" alt="Profile" class="avatar-img">` :
                                `<img src="../Uploads/instructor-profile/${enrollment.profile_pic}" alt="Profile" class="avatar-img">`;

                            document.getElementById("modalCourseTitle").textContent = enrollment.course_title;
                            document.getElementById("modalInstructorName").textContent = `Instructor: ${enrollment.instructor_name}`;

                            const progressBar = document.getElementById("modalProgressBar");
                            progressBar.style.width = `${enrollment.completion_percentage}%`;
                            progressBar.setAttribute('aria-valuenow', enrollment.completion_percentage);
                            document.getElementById("modalProgressText").textContent = `${enrollment.completion_percentage}% Complete`;

                            const topicProgress = document.getElementById("modalTopicProgress");
                            topicProgress.innerHTML = "";
                            if (enrollment.topics && enrollment.topics.length > 0) {
                                enrollment.topics.forEach(topic => {
                                    const status = topic.completion_status === 'completed' ?
                                        `<span class="badge bg-success">Completed</span>` :
                                        `<span class="badge bg-warning">In Progress</span>`;
                                    topicProgress.innerHTML += `
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>${topic.title}</span>
                                            ${status}
                                        </div>`;
                                });
                            } else {
                                topicProgress.innerHTML = `<p class="text-muted">No topic progress available.</p>`;
                            }

                            const quizPerformance = document.getElementById("modalQuizPerformance");
                            quizPerformance.innerHTML = "";
                            if (enrollment.quiz_attempts && enrollment.quiz_attempts.length > 0) {
                                enrollment.quiz_attempts.forEach(attempt => {
                                    quizPerformance.innerHTML += `
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>${attempt.quiz_title}</span>
                                            <span>Score: ${attempt.score}% ${attempt.passed ? '<span class="badge bg-success">Passed</span>' : '<span class="badge bg-danger">Failed</span>'}</span>
                                        </div>`;
                                });
                            } else {
                                quizPerformance.innerHTML = `<p class="text-muted">No quiz attempts recorded.</p>`;
                            }

                            const certificateStatus = document.getElementById("modalCertificateStatus");
                            const certificateActions = document.getElementById("modalCertificateActions");
                            const issueButton = document.getElementById("issueCertificateBtn");

                            if (enrollment.certificate_id) {
                                certificateStatus.className = "badge bg-info";
                                certificateStatus.textContent = "Issued";
                                certificateActions.innerHTML = `
                                    <a href="../backend/admin/progress-tracking.php?action=download_certificate&certificate_id=${enrollment.certificate_id}" class="btn btn-sm btn-soft-primary">
                                        <i class="bi-download"></i> Download
                                    </a>`;
                                issueButton.classList.add("d-none");
                            } else if (enrollment.completion_percentage >= 100) {
                                certificateStatus.className = "badge bg-warning";
                                certificateStatus.textContent = "Eligible";
                                certificateActions.innerHTML = "";
                                issueButton.classList.remove("d-none");
                                issueButton.setAttribute("data-id", enrollmentId);
                            } else {
                                certificateStatus.className = "badge bg-danger";
                                certificateStatus.textContent = "Not Eligible";
                                certificateActions.innerHTML = "";
                                issueButton.classList.add("d-none");
                            }

                            const progressModal = new bootstrap.Modal(document.getElementById("progressModal"));
                            progressModal.show();
                        } else {
                            showAlert('danger', `Error: ${data.message}`);
                        }
                    })
                    .catch(error => {
                        removeOverlay();
                        console.error("Error fetching progress details:", error);
                        showAlert('danger', "An error occurred while fetching details. Please try again.");
                    });
            }

            function issueCertificate(enrollmentId) {
                createOverlay("Issuing certificate...");

                const formData = new FormData();
                formData.append('enrollment_id', enrollmentId);
                formData.append('action', 'issue_certificate');

                fetch('../backend/admin/progress-tracking.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        removeOverlay();

                        if (data.success) {
                            fetchEnrollments();
                            showAlert('success', 'Certificate issued successfully.');
                            const modal = bootstrap.Modal.getInstance(document.getElementById("progressModal"));
                            if (modal) {
                                modal.hide();
                            }
                        } else {
                            showAlert('danger', `Error: ${data.message}`);
                        }
                    })
                    .catch(error => {
                        removeOverlay();
                        console.error("Error issuing certificate:", error);
                        showAlert('danger', "An error occurred while issuing certificate. Please try again.");
                    });
            }

            // Event Listeners
            document.getElementById("prevPage").addEventListener("click", function() {
                if (currentPage > 1) {
                    currentPage--;
                    fetchEnrollments();
                }
            });

            document.getElementById("nextPage").addEventListener("click", function() {
                currentPage++;
                fetchEnrollments();
            });

            document.getElementById("statusFilter").addEventListener("change", function() {
                statusFilter = this.value;
                currentPage = 1;
                fetchEnrollments();
            });

            document.getElementById("courseFilter").addEventListener("change", function() {
                courseFilter = this.value;
                currentPage = 1;
                fetchEnrollments();
            });

            document.getElementById("searchInput").addEventListener("input", function() {
                searchQuery = this.value;
                currentPage = 1;
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => {
                    fetchEnrollments();
                }, 500);
            });

            document.getElementById("issueCertificateBtn").addEventListener("click", function() {
                const enrollmentId = this.getAttribute("data-id");
                if (confirm("Are you sure you want to issue a certificate for this enrollment?")) {
                    issueCertificate(enrollmentId);
                }
            });

            document.querySelectorAll(".sortable").forEach(header => {
                header.addEventListener("click", function() {
                    let column = this.dataset.sort;
                    if (sortColumn === column) {
                        sortDirection = sortDirection === "asc" ? "desc" : "asc";
                    } else {
                        sortColumn = column;
                        sortDirection = "asc";
                    }

                    document.querySelectorAll(".sort-icon").forEach(icon => {
                        icon.innerHTML = "⇅";
                    });
                    this.querySelector(".sort-icon").innerHTML = sortDirection === "asc" ? "↑" : "↓";

                    fetchEnrollments();
                });
            });

            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Initialize
            fetchCourses();
            fetchEnrollments();
        });
    </script>
</main>
<!-- ========== END MAIN CONTENT ========== -->

<?php include '../includes/department/footer.php'; ?>