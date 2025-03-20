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


    <!-- Content -->
    <div class="navbar-sidebar-aside-content content-space-1 content-space-md-2 px-lg-5 px-xl-10">
        <!-- Page Header -->
        <div class="docs-page-header">
            <div class="row align-items-center">
                <div class="col-sm">
                    <h1 class="docs-page-header-title">Instructor Verification</h1>
                    <button class="btn btn-sm btn-soft-primary ms-3" id="diagnosePathsBtn">
                        <i class="bi-tools me-1"></i> File Path Diagnostic
                    </button>
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
                        <h6 class="card-subtitle mb-2">Verification Requests</h6>
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

            <!-- Verified Instructors Card -->
            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Verified Instructors</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="card-title text-inherit" id="verifiedInstructors">0</h2>
                                <span class="text-body fs-6" id="verifiedPercentage">0%</span>
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
            <!-- End Verified Instructors Card -->

            <!-- Pending Verifications Card -->
            <div class="col-sm-6 col-lg-3 mb-3 mb-sm-0">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Pending Verifications</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="card-title text-inherit" id="pendingVerifications">0</h2>
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
            <!-- End Pending Verifications Card -->

            <!-- Rejected Verifications Card -->
            <div class="col-sm-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Rejected Verifications</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="card-title text-inherit" id="rejectedVerifications">0</h2>
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
            <!-- End Rejected Verifications Card -->
        </div>
        <!-- End Summary Cards -->

        <!-- Heading -->
        <h2 id="component-1" class="hs-docs-heading">
            Verification Requests <a class="anchorjs-link" href="#component-1" aria-label="Anchor" data-anchorjs-icon="#"></a>
        </h2>
        <!-- End Heading -->

        <!-- Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center mb-3">
                <h4 class="card-header-title">Instructor verification requests</h4>
                <div class="d-flex align-items-center">
                    <select id="statusFilter" class="form-select form-select-sm me-2">
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Verified</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="🔍 Search Name/Email...">
                </div>
            </div>

            <!-- Verification Table -->
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th class="sortable" data-sort="name">Instructor <span class="sort-icon">⇅</span></th>
                            <th class="sortable" data-sort="status">Status <span class="sort-icon">⇅</span></th>
                            <th class="sortable" data-sort="documents">Documents <span class="sort-icon">⇅</span></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="verificationTableBody">
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

                .document-thumbnail {
                    width: 40px;
                    height: 40px;
                    border-radius: 4px;
                    margin-right: 5px;
                    cursor: pointer;
                    border: 1px solid #e7eaf3;
                    color: #377dff;
                }

                .document-count {
                    background-color: #f8fafd;
                    border-radius: 4px;
                    padding: 2px 8px;
                    font-size: 12px;
                    color: #677788;
                }
            </style>

            <!-- Verification Details Modal -->
            <div class="modal fade" id="verificationModal" tabindex="-1" aria-labelledby="verificationModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="verificationModalLabel">Verification Request Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Instructor Information -->
                            <div class="mb-4">
                                <h6>Instructor Information</h6>
                                <div class="d-flex align-items-center mb-3">
                                    <span class="avatar avatar-lg avatar-circle me-3" id="modalInstructorAvatar">
                                        <span class="avatar-initials">J</span>
                                    </span>
                                    <div>
                                        <h5 class="mb-0" id="modalInstructorName">John Doe</h5>
                                        <p class="mb-0 text-muted" id="modalInstructorEmail">johndoe@example.com</p>
                                        <small class="text-muted" id="modalInstructorJoined">Joined: Jan 01, 2023</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Professional Credentials -->
                            <div class="mb-4">
                                <h6>Professional Credentials</h6>
                                <div class="p-3 bg-soft-light rounded overflow-auto" id="modalCredentials" style="max-height: 200px;">
                                    Professional credentials information will appear here.
                                </div>
                            </div>

                            <!-- Uploaded Documents -->
                            <div class="mb-4">
                                <h6>Verification Documents</h6>
                                <div class="row" id="modalDocuments">
                                    <!-- Document previews will be added here -->
                                </div>
                            </div>

                            <!-- Verification Status -->
                            <div id="currentStatusSection" class="mb-4">
                                <h6>Current Status</h6>
                                <div id="modalCurrentStatus" class="badge bg-warning">Pending</div>
                                <div id="modalRejectionReason" class="mt-2 d-none">
                                    <small class="text-muted">Reason for rejection:</small>
                                    <p class="mb-0 text-danger"></p>
                                </div>
                            </div>

                            <!-- Admin Actions Section -->
                            <div id="adminActionSection">
                                <h6>Verification Action</h6>
                                <div class="d-flex flex-column">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="verificationAction" id="approveRadio" value="approve">
                                        <label class="form-check-label text-success" for="approveRadio">
                                            Approve verification
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="verificationAction" id="rejectRadio" value="reject">
                                        <label class="form-check-label text-danger" for="rejectRadio">
                                            Reject verification
                                        </label>
                                    </div>
                                    <div id="rejectionReasonField" class="mb-3 d-none">
                                        <label for="rejectionReason" class="form-label">Reason for rejection</label>
                                        <textarea class="form-control" id="rejectionReason" rows="3" placeholder="Provide a clear reason for the rejection"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="submitVerificationAction">Submit Decision</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Verification Modal -->

            <!-- Document Preview Modal -->
            <div class="modal fade" id="documentPreviewModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Document Preview</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                            <!-- Dynamic content will be inserted here -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                            <a id="documentDownloadLink" href="#" class="btn btn-primary" download>
                                <i class="bi-download me-1"></i> Download
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Document Preview Modal -->
        </div>
        <!-- End Card -->
    </div>
    <!-- End Content -->

    <!-- JavaScript for Instructor Verification -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let currentPage = 1;
            let requestsPerPage = 10;
            let sortColumn = "submitted_at";
            let sortDirection = "desc";
            let statusFilter = "all";
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
                // Position the alert
                alertDiv.style.position = 'fixed';
                alertDiv.style.top = '20px';
                alertDiv.style.left = '50%';
                alertDiv.style.transform = 'translateX(-50%)';
                alertDiv.style.zIndex = '9999';
                alertDiv.style.minWidth = '300px';
                alertDiv.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
                document.body.appendChild(alertDiv);
                // Auto-dismiss after 5 seconds
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

            function fetchVerificationRequests() {
                // Show loading overlay
                createOverlay("Loading verification requests...");

                // Clear the table body
                document.getElementById("verificationTableBody").innerHTML = "";

                // Build query parameters
                const params = new URLSearchParams({
                    action: 'get_verification_requests',
                    page: currentPage,
                    per_page: requestsPerPage,
                    sort_column: sortColumn,
                    sort_direction: sortDirection,
                    status: statusFilter,
                    search: searchQuery
                });

                // Fetch data from backend
                fetch(`../backend/admin/instructor-verification.php?${params}`)
                    .then(response => response.json())
                    .then(data => {
                        // Remove loading overlay
                        removeOverlay();

                        if (data.success) {
                            // Update the UI with the fetched data
                            displayVerificationRequests(data.data);
                            createPagination(data.pagination);
                            updateSummaryCards(data.summary);
                        } else {
                            // Show error message
                            showAlert('danger', `Error: ${data.message}`);
                        }
                    })
                    .catch(error => {
                        // Remove loading overlay
                        removeOverlay();

                        console.error("Error fetching verification requests:", error);
                        showAlert('danger', "An error occurred while fetching data. Please try again.");
                    });
            }

            function updateSummaryCards(summary) {
                document.getElementById("totalInstructors").textContent = summary.total;
                document.getElementById("verifiedInstructors").textContent = summary.verified;
                document.getElementById("pendingVerifications").textContent = summary.pending;
                document.getElementById("rejectedVerifications").textContent = summary.rejected;

                const percentage = summary.total > 0 ? Math.round((summary.verified / summary.total) * 100) : 0;
                document.getElementById("verifiedPercentage").textContent = `${percentage}%`;
            }

            function formatDate(dateString) {
                const options = {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                };
                return new Date(dateString).toLocaleDateString(undefined, options);
            }

            function displayVerificationRequests(requests) {
                let tableBody = document.getElementById("verificationTableBody");
                tableBody.innerHTML = "";

                if (requests.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="4" class="text-center text-muted">No verification requests found.</td></tr>`;
                    return;
                }

                requests.forEach(request => {
                    let statusBadge = "";

                    switch (request.status) {
                        case "pending":
                            statusBadge = `<span class="badge bg-warning">Pending</span>`;
                            break;
                        case "approved":
                            statusBadge = `<span class="badge bg-success">Verified</span>`;
                            break;
                        case "rejected":
                            statusBadge = `<span class="badge bg-danger">Rejected</span>`;
                            break;
                    }

                    let documentsDisplay = "";

                    if (request.documentCount > 0) {
                        documentsDisplay = `<div class="d-flex align-items-center">`;

                        // Show document icons instead of placeholder images
                        for (let i = 0; i < Math.min(2, request.documentCount); i++) {
                            documentsDisplay += `
                                <div class="document-thumbnail view-document d-flex align-items-center justify-content-center bg-soft-primary" 
                                    data-request-id="${request.id}" data-doc-index="${i}" title="View Document ${i+1}">
                                    <i class="bi-file-earmark-text fs-5"></i>
                                </div>`;
                        }

                        // If there are more than 2 documents, show the remaining count
                        if (request.documentCount > 2) {
                            documentsDisplay += `<span class="document-count">+${request.documentCount - 2}</span>`;
                        }

                        documentsDisplay += `</div>`;
                    } else {
                        documentsDisplay = `<span class="text-muted">No documents</span>`;
                    }

                    // Determine profile picture URL
                    const profilePicUrl = request.profilePic === 'default.png' ?
                        '../assets/img/160x160/img1.jpg' // Default image path
                        :
                        `../uploads/instructor-profile/${request.profilePic}`; // Custom profile pic path

                    // Action buttons based on status
                    let actionButtons = `
                        <button class="btn btn-sm btn-soft-primary view-request" data-id="${request.id}">
                            <i class="bi-eye"></i>
                        </button>
                    `;

                    // Add buttons for pending requests
                    if (request.status === "pending") {
                        actionButtons += `
                            <button class="btn btn-sm btn-soft-success ms-2 approve-btn" data-id="${request.id}">
                                <i class="bi-check-lg"></i>
                            </button>
                            <button class="btn btn-sm btn-soft-danger ms-2 reject-btn" data-id="${request.id}">
                                <i class="bi-x-lg"></i>
                            </button>
                        `;
                    }

                    tableBody.innerHTML += `
                        <tr id="row-${request.id}">
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-sm avatar-circle">
                                        <img src="${profilePicUrl}" alt="Profile" class="avatar-img">
                                    </span>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0">${request.name}
                                            ${request.status === 'approved' ? 
                                            '<img class="avatar avatar-xss ms-1" src="../assets/svg/illustrations/top-vendor.svg" alt="Verified" data-bs-toggle="tooltip" data-bs-placement="top" title="Verified user">' : ''}
                                        </h6>
                                        <small class="d-block text-muted">${request.email}</small>
                                    </div>
                                </div>
                            </td>
                            <td>${statusBadge}</td>
                            <td>${documentsDisplay}</td>
                            <td>${actionButtons}</td>
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
                        fetchVerificationRequests();
                    };
                    paginationNumbers.appendChild(pageButton);
                }
            }

            function updateButtons() {
                const prevButton = document.getElementById("prevPage");
                const nextButton = document.getElementById("nextPage");

                prevButton.disabled = (currentPage === 1);

                // Next button will be disabled by pagination data when on last page
                // This will be updated when we receive the response
            }

            function attachEventListeners() {
                // View request details
                document.querySelectorAll(".view-request").forEach(button => {
                    button.addEventListener("click", function() {
                        let requestId = this.getAttribute("data-id");
                        viewRequestDetails(requestId);
                    });
                });

                // Quick approve
                document.querySelectorAll(".approve-btn").forEach(button => {
                    button.addEventListener("click", function() {
                        let requestId = this.getAttribute("data-id");
                        if (confirm("Are you sure you want to approve this verification request?")) {
                            updateVerificationStatus(requestId, "approved");
                        }
                    });
                });

                // Quick reject (opens modal with reason field)
                document.querySelectorAll(".reject-btn").forEach(button => {
                    button.addEventListener("click", function() {
                        let requestId = this.getAttribute("data-id");
                        viewRequestDetails(requestId);
                        // Pre-select reject option
                        document.getElementById("rejectRadio").checked = true;
                        document.getElementById("rejectionReasonField").classList.remove("d-none");
                    });
                });

                // View document
                document.querySelectorAll(".view-document").forEach(img => {
                    img.addEventListener("click", function(e) {
                        e.stopPropagation();
                        let requestId = this.getAttribute("data-request-id");
                        let docIndex = this.getAttribute("data-doc-index");
                        viewDocument(requestId, docIndex);
                    });
                });
            }

            function viewRequestDetails(requestId) {
    // Show loading overlay
    createOverlay("Loading verification details...");

    // Fetch request details from the backend
    fetch(`../backend/admin/instructor-verification.php?action=get_request_details&verification_id=${requestId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            // Remove loading overlay
            removeOverlay();

            if (data.success) {
                const request = data.data;

                // Set modal data
                document.getElementById("modalInstructorName").textContent = request.name;
                document.getElementById("modalInstructorEmail").textContent = request.email;
                document.getElementById("modalInstructorJoined").textContent = "Joined: " + formatDate(request.joinDate);

                // Set avatar - always use profile picture instead of initials
                const avatarContainer = document.getElementById("modalInstructorAvatar");
                if (request.profilePic === 'default.png') {
                    // Use default profile image instead of initials
                    avatarContainer.innerHTML = `<img src="../assets/img/160x160/img1.jpg" alt="Default Profile" class="avatar-img">`;
                } else {
                    avatarContainer.innerHTML = `<img src="../uploads/instructor-profile/${request.profilePic}" alt="Profile" class="avatar-img">`;
                }

                // Display credentials properly with formatting
                document.getElementById("modalCredentials").innerHTML = `<p class="mb-0">${request.credentials}</p>`;

                // Set current status
                const statusBadge = document.getElementById("modalCurrentStatus");
                statusBadge.className = "badge";

                switch (request.status) {
                    case "pending":
                        statusBadge.classList.add("bg-warning");
                        statusBadge.textContent = "Pending";
                        break;
                    case "approved":
                        statusBadge.classList.add("bg-success");
                        statusBadge.textContent = "Verified";
                        break;
                    case "rejected":
                        statusBadge.classList.add("bg-danger");
                        statusBadge.textContent = "Rejected";
                        break;
                }

                // Show rejection reason if exists
                const reasonSection = document.getElementById("modalRejectionReason");

                if (request.status === "rejected" && request.rejectionReason) {
                    reasonSection.classList.remove("d-none");
                    reasonSection.querySelector("p").textContent = request.rejectionReason;
                } else {
                    reasonSection.classList.add("d-none");
                }

                // Generate document previews
                const documentsContainer = document.getElementById("modalDocuments");
                documentsContainer.innerHTML = "";

                if (request.documents && request.documents.length > 0) {
                    request.documents.forEach((doc, index) => {
                        // Determine file type and display appropriate icon
                        const fileExt = doc.path.split('.').pop().toLowerCase();
                        let fileIcon = 'bi-file-earmark-text';
                        let fileType = 'Document';

                        if (['pdf'].includes(fileExt)) {
                            fileIcon = 'bi-file-earmark-pdf';
                            fileType = 'PDF';
                        } else if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExt)) {
                            fileIcon = 'bi-file-earmark-image';
                            fileType = 'Image';
                        } else if (['doc', 'docx'].includes(fileExt)) {
                            fileIcon = 'bi-file-earmark-word';
                            fileType = 'Word Document';
                        } else if (['xls', 'xlsx'].includes(fileExt)) {
                            fileIcon = 'bi-file-earmark-excel';
                            fileType = 'Excel Document';
                        } else if (['ppt', 'pptx'].includes(fileExt)) {
                            fileIcon = 'bi-file-earmark-slides';
                            fileType = 'PowerPoint';
                        }

                        const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExt);

                        // Check if file exists (safely)
                        let documentExists = true;
                        if (typeof doc.exists === 'boolean') {
                            documentExists = doc.exists;
                        }

                        let documentPreview;
                        if (!documentExists) {
                            // Show missing file warning
                            documentPreview = `
                                <div class="card-img-top d-flex flex-column align-items-center justify-content-center bg-soft-danger" 
                                    style="height: 160px;">
                                    <i class="bi-exclamation-triangle-fill display-4 text-danger"></i>
                                    <p class="small text-danger mt-2">File not found</p>
                                </div>`;
                        } else if (isImage) {
                            documentPreview = `
                                <img src="../backend/admin/instructor-verification.php?action=download_document&path=${encodeURIComponent(doc.path)}" 
                                    class="card-img-top cursor-pointer view-document-modal" 
                                    data-document-path="${doc.path}" alt="Document ${index+1}" 
                                    style="height: 160px; object-fit: cover;">`;
                        } else {
                            documentPreview = `
                                <div class="card-img-top cursor-pointer view-document-modal d-flex align-items-center justify-content-center bg-soft-primary" 
                                    style="height: 160px;" data-document-path="${doc.path}">
                                    <i class="${fileIcon} display-4"></i>
                                </div>`;
                        }

                        // Format file size if available
                        let fileSizeText = '';
                        if (typeof doc.size === 'number' && doc.size > 0) {
                            if (doc.size < 1024) {
                                fileSizeText = `${doc.size} bytes`;
                            } else if (doc.size < 1024 * 1024) {
                                fileSizeText = `${(doc.size / 1024).toFixed(1)} KB`;
                            } else {
                                fileSizeText = `${(doc.size / (1024 * 1024)).toFixed(1)} MB`;
                            }
                        }

                        // Use direct PHP handler for document downloads
                        const downloadPath = `../backend/admin/instructor-verification.php?action=download_document&path=${encodeURIComponent(doc.path)}`;

                        // Determine if file buttons should be enabled
                        const previewBtn = documentExists ?
                            `<button class="btn btn-xs btn-soft-primary view-document-modal" 
                                data-document-path="${doc.path}">Preview</button>` :
                            `<button class="btn btn-xs btn-soft-primary" disabled>Preview</button>`;

                        const downloadBtn = documentExists ?
                            `<a href="${downloadPath}" class="btn btn-xs btn-soft-success" 
                                download="${doc.path}">Download</a>` :
                            `<button class="btn btn-xs btn-soft-success" disabled>Download</button>`;

                        documentsContainer.innerHTML += `
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    ${documentPreview}
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <p class="card-text small mb-0 text-truncate" title="${doc.path}">${doc.path}</p>
                                            <span class="badge bg-soft-primary text-primary">${fileType}</span>
                                        </div>
                                        ${fileSizeText ? `<small class="text-muted">${fileSizeText}</small>` : ''}
                                        <div class="d-flex justify-content-between mt-2">
                                            ${previewBtn}
                                            ${downloadBtn}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    // Attach event listeners for document previews
                    document.querySelectorAll(".view-document-modal").forEach(img => {
                        img.addEventListener("click", function() {
                            const docPath = this.getAttribute("data-document-path");
                            viewDocumentByPath(docPath);
                        });
                    });
                } else {
                    documentsContainer.innerHTML = `<div class="col-12"><p class="text-muted">No documents uploaded</p></div>`;
                }

                // Show/hide admin action section based on status
                const adminActionSection = document.getElementById("adminActionSection");
                const submitButton = document.getElementById("submitVerificationAction");

                if (request.status === "pending") {
                    adminActionSection.classList.remove("d-none");
                    submitButton.classList.remove("d-none"); // Show submit button
                    // Reset radio buttons and textarea
                    document.getElementById("approveRadio").checked = false;
                    document.getElementById("rejectRadio").checked = false;
                    document.getElementById("rejectionReasonField").classList.add("d-none");
                    document.getElementById("rejectionReason").value = "";
                } else {
                    adminActionSection.classList.add("d-none");
                    submitButton.classList.add("d-none"); // Hide submit button for approved/rejected requests
                }

                // Store request ID on submit button
                document.getElementById("submitVerificationAction").setAttribute("data-id", requestId);

                // Show modal
                const verificationModal = new bootstrap.Modal(document.getElementById("verificationModal"));
                verificationModal.show();
            } else {
                showAlert('danger', `Error: ${data.message}`);
            }
        })
        .catch(error => {
            // Remove loading overlay
            removeOverlay();

            console.error("Error fetching request details:", error);
            showAlert('danger', "An error occurred while fetching request details. Please try again.");
        });
}
            function viewDocument(requestId, docIndex) {
                // Show loading overlay
                createOverlay("Loading document...");

                // Get document details from verification request
                fetch(`../backend/admin/instructor-verification.php?action=get_request_details&verification_id=${requestId}`)
                    .then(response => response.json())
                    .then(data => {
                        // Remove loading overlay
                        removeOverlay();

                        if (data.success && data.data.documents && data.data.documents[docIndex]) {
                            const docPath = data.data.documents[docIndex].path;
                            viewDocumentByPath(docPath);
                        } else {
                            showAlert('danger', "Document not found");
                        }
                    })
                    .catch(error => {
                        // Remove loading overlay
                        removeOverlay();

                        console.error("Error loading document:", error);
                        showAlert('danger', "Error loading document. Please try again.");
                    });
            }

            function viewDocumentByPath(docPath) {
                // Show loading overlay while preparing document preview
                createOverlay("Loading document preview...");

                const fileExt = docPath.split('.').pop().toLowerCase();
                const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExt);
                const isPdf = fileExt === 'pdf';

                // Generate a unique ID for this modal instance
                const modalId = 'documentModal-' + Math.random().toString(36).substring(2, 15);

                // Create download URL with cache-busting parameter
                const timestamp = new Date().getTime();
                const directDownloadUrl = `../backend/admin/instructor-verification.php?action=download_document&path=${encodeURIComponent(docPath)}&t=${timestamp}`;

                // First, create the modal with a loading indicator
                let modalHTML = `
        <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${docPath}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center" id="modalBody-${modalId}">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p>Preparing preview...</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <a href="${directDownloadUrl}" class="btn btn-primary" download="${docPath}">
                            <i class="bi-download me-1"></i> Download
                        </a>
                    </div>
                </div>
            </div>
        </div>
    `;

                // Add modal to document
                const modalContainer = document.createElement('div');
                modalContainer.innerHTML = modalHTML;
                document.body.appendChild(modalContainer);

                // Create and show the modal
                const modal = new bootstrap.Modal(document.getElementById(modalId));
                modal.show();

                // Get the modal body for content updates
                const modalBody = document.getElementById(`modalBody-${modalId}`);

                // Function to update the modal content based on file type
                function updateModalContent() {
                    if (isImage) {
                        // For images, load in a new Image object first to check if it works
                        const img = new Image();
                        img.onload = function() {
                            modalBody.innerHTML = `
                    <img src="${directDownloadUrl}" alt="Document Preview" class="img-fluid rounded">
                `;
                        };
                        img.onerror = function() {
                            modalBody.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi-exclamation-triangle-fill me-2"></i>
                        Error loading image. The file may be missing or inaccessible.
                    </div>
                    <div class="text-center">
                        <button class="btn btn-primary" onclick="window.open('${directDownloadUrl}', '_blank')">
                            <i class="bi-box-arrow-up-right me-1"></i> Try opening in new tab
                        </button>
                    </div>
                `;
                        };
                        img.src = directDownloadUrl;
                    } else if (isPdf) {
                        // For PDFs, try using an object tag which is more reliable than embed or iframe
                        modalBody.innerHTML = `
                <object data="${directDownloadUrl}" type="application/pdf" width="100%" height="600">
                    <div class="alert alert-warning">
                        <p>It appears your browser doesn't support embedded PDFs.</p>
                        <button class="btn btn-primary" onclick="window.open('${directDownloadUrl}', '_blank')">
                            <i class="bi-box-arrow-up-right me-1"></i> Open PDF in new tab
                        </button>
                    </div>
                </object>
            `;
                    } else {
                        // For other file types
                        let fileIcon = 'bi-file-earmark-text';
                        let fileType = 'Document';

                        if (['doc', 'docx'].includes(fileExt)) {
                            fileIcon = 'bi-file-earmark-word';
                            fileType = 'Word Document';
                        } else if (['xls', 'xlsx'].includes(fileExt)) {
                            fileIcon = 'bi-file-earmark-excel';
                            fileType = 'Excel Document';
                        } else if (['ppt', 'pptx'].includes(fileExt)) {
                            fileIcon = 'bi-file-earmark-slides';
                            fileType = 'PowerPoint';
                        }

                        modalBody.innerHTML = `
                <div class="text-center py-4">
                    <i class="${fileIcon} display-1 text-primary mb-3"></i>
                    <h4>${fileType}</h4>
                    <p class="text-muted">This file type cannot be previewed directly.</p>
                    <div class="mt-3">
                        <button class="btn btn-primary" onclick="window.open('${directDownloadUrl}', '_blank')">
                            <i class="bi-box-arrow-up-right me-1"></i> Open in new tab
                        </button>
                    </div>
                </div>
            `;
                    }
                }

                // Add event listener to remove modal from DOM when it's hidden
                document.getElementById(modalId).addEventListener('hidden.bs.modal', function() {
                    document.body.removeChild(modalContainer);
                });

                // Update content after a short delay to ensure modal is rendered
                setTimeout(function() {
                    updateModalContent();
                    removeOverlay();
                }, 500);
            }

            function updateVerificationStatus(requestId, newStatus, rejectionReason = null) {
                // Show loading overlay
                createOverlay(`${newStatus === "approved" ? "Approving" : "Rejecting"} verification request...`);

                const formData = new FormData();
                formData.append('verification_id', requestId);
                formData.append('status', newStatus);

                if (rejectionReason) {
                    formData.append('rejection_reason', rejectionReason);
                }

                fetch('../backend/admin/instructor-verification.php?action=update_status', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Remove loading overlay
                        removeOverlay();

                        if (data.success) {
                            // Refresh the data
                            fetchVerificationRequests();

                            // Show success message
                            showAlert('success', `Verification request ${newStatus === "approved" ? "approved" : "rejected"} successfully.`);

                            // Close any open modals
                            const modal = bootstrap.Modal.getInstance(document.getElementById("verificationModal"));
                            if (modal) {
                                modal.hide();
                            }
                        } else {
                            showAlert('danger', `Error: ${data.message}`);
                        }
                    })
                    .catch(error => {
                        // Remove loading overlay
                        removeOverlay();

                        console.error("Error updating verification status:", error);
                        showAlert('danger', "An error occurred while updating status. Please try again.");
                    });
            }

            // Add a function to test file paths directly
            function testFilePaths(fileToFind = null) {
                createOverlay("Testing file paths...");

                const url = fileToFind ?
                    `../backend/admin/instructor-verification.php?action=test_file_paths&find_file=${encodeURIComponent(fileToFind)}` :
                    '../backend/admin/instructor-verification.php?action=test_file_paths';

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        removeOverlay();
                        console.log("File path test results:", data);

                        // Show the results in an alert for easy viewing
                        if (data.success) {
                            // Display results in a modal instead of an alert
                            let resultsHtml = `
                                <div class="modal fade" id="filePathModal" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">File Path Test Results</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-4">
                                                    <h6 class="text-primary">Path Information</h6>
                                                    <ul class="list-group mb-3">
                                                        <li class="list-group-item"><strong>Selected documents path:</strong> ${data.paths.selected_documents_path}</li>
                                                        <li class="list-group-item"><strong>Selected profile path:</strong> ${data.paths.selected_profile_path}</li>
                                                        <li class="list-group-item"><strong>Script directory:</strong> ${data.paths.script_dir}</li>
                                                        <li class="list-group-item"><strong>Document root:</strong> ${data.paths.document_root}</li>
                                                        <li class="list-group-item"><strong>Current working directory:</strong> ${data.paths.current_working_dir}</li>
                                                    </ul>
                                                </div>
                                                
                                                <div class="mb-4">
                                                    <h6 class="text-primary">Existence Checks</h6>
                                                    <table class="table table-sm table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Path</th>
                                                                <th>Exists</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                            `;

                            // Add existence checks
                            for (const [path, exists] of Object.entries(data.paths.exists_check)) {
                                resultsHtml += `
                                    <tr>
                                        <td>${path}</td>
                                        <td>${exists ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>'}</td>
                                    </tr>
                                `;
                            }

                            resultsHtml += `
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="mb-4">
                                    <h6 class="text-primary">Write Permission Tests</h6>
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Directory</th>
                                                <th>Writable</th>
                                                <th>Error</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                            `;

                            // Add write tests
                            for (const [key, test] of Object.entries(data.write_test)) {
                                resultsHtml += `
                                    <tr>
                                        <td>${test.dir}</td>
                                        <td>${test.can_write ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>'}</td>
                                        <td>${test.error || 'None'}</td>
                                    </tr>
                                `;
                            }

                            resultsHtml += `
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="mb-4">
                                    <h6 class="text-primary">Document Files</h6>
                                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Filename</th>
                                                    <th>Size</th>
                                                    <th>Modified</th>
                                                    <th>Readable</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                            `;

                            // Add document files
                            if (data.document_files && data.document_files.length > 0) {
                                for (const file of data.document_files) {
                                    resultsHtml += `
                                        <tr>
                                            <td>${file.name}</td>
                                            <td>${formatFileSize(file.size)}</td>
                                            <td>${file.modified}</td>
                                            <td>${file.is_readable ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>'}</td>
                                        </tr>
                                    `;
                                }
                            } else {
                                resultsHtml += `
                                    <tr>
                                        <td colspan="4" class="text-center">No files found</td>
                                    </tr>
                                `;
                            }

                            resultsHtml += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                            `;

                            // Add modal to body
                            const modalDiv = document.createElement('div');
                            modalDiv.innerHTML = resultsHtml;
                            document.body.appendChild(modalDiv);

                            // Show the modal
                            const modal = new bootstrap.Modal(document.getElementById('filePathModal'));
                            modal.show();

                            // Remove modal from DOM when hidden
                            document.getElementById('filePathModal').addEventListener('hidden.bs.modal', function() {
                                document.body.removeChild(modalDiv);
                            });
                        } else {
                            showAlert('danger', "Error testing file paths: " + data.message);
                        }
                    })
                    .catch(error => {
                        removeOverlay();
                        console.error("Error testing file paths:", error);
                        showAlert('danger', "Error testing file paths. See console for details.");
                    });
            }

            // Helper function to format file size
            function formatFileSize(bytes) {
                if (bytes < 1024) {
                    return bytes + ' bytes';
                } else if (bytes < 1024 * 1024) {
                    return (bytes / 1024).toFixed(1) + ' KB';
                } else {
                    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
                }
            }

            // Event Listeners
            document.getElementById("prevPage").addEventListener("click", function() {
                if (currentPage > 1) {
                    currentPage--;
                    fetchVerificationRequests();
                }
            });

            document.getElementById("nextPage").addEventListener("click", function() {
                currentPage++;
                fetchVerificationRequests();
            });

            // Status filter change
            document.getElementById("statusFilter").addEventListener("change", function() {
                statusFilter = this.value;
                currentPage = 1; // Reset to first page when filter changes
                fetchVerificationRequests();
            });

            // Search input
            document.getElementById("searchInput").addEventListener("input", function() {
                searchQuery = this.value;
                currentPage = 1; // Reset to first page when search changes

                // Add debounce to avoid too many requests while typing
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => {
                    fetchVerificationRequests();
                }, 500);
            });

            // Radio buttons for rejection reason field
            document.getElementById("rejectRadio").addEventListener("change", function() {
                if (this.checked) {
                    document.getElementById("rejectionReasonField").classList.remove("d-none");
                }
            });

            document.getElementById("approveRadio").addEventListener("change", function() {
                if (this.checked) {
                    document.getElementById("rejectionReasonField").classList.add("d-none");
                }
            });

            // Submit verification action
            document.getElementById("submitVerificationAction").addEventListener("click", function() {
                const requestId = this.getAttribute("data-id");
                const approveRadio = document.getElementById("approveRadio");
                const rejectRadio = document.getElementById("rejectRadio");

                if (!approveRadio.checked && !rejectRadio.checked) {
                    showAlert('danger', "Please select an action (approve or reject).");
                    return;
                }

                if (rejectRadio.checked) {
                    const rejectionReason = document.getElementById("rejectionReason").value.trim();

                    if (!rejectionReason) {
                        showAlert('danger', "Please provide a reason for rejection.");
                        return;
                    }

                    updateVerificationStatus(requestId, "rejected", rejectionReason);
                } else {
                    updateVerificationStatus(requestId, "approved");
                }
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

                    // Fetch sorted data
                    fetchVerificationRequests();
                });
            });

            // File path diagnostic button
            document.getElementById("diagnosePathsBtn").addEventListener("click", function() {
                testFilePaths();
            });

            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

            // Initialize
            fetchVerificationRequests();
        });
    </script>
</main>
<!-- ========== END MAIN CONTENT ========== -->

<?php include '../includes/admin-footer.php'; ?>