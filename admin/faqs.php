<?php
// Authentication check
require_once '../backend/auth/admin/admin-auth-check.php';

// Set page title
$pageTitle = "FAQs - Admin | Learnix";

include_once '../includes/admin/header.php';

// <!-- Menu -->
include_once '../includes/admin/sidebar.php';
// <!-- / Menu -->

// <!-- Navbar -->
include_once '../includes/admin/navbar.php';


// Get departments data from database
require_once '../backend/config.php';

// Initialize statistics
$totalFaqs = 0;
$activeFaqs = 0;
$studentFaqs = 0;
$instructorFaqs = 0;
$allUsersFaqs = 0;

// Get statistics from database
$stats_query = "SELECT 
                  COUNT(*) as total,
                  SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                  SUM(CASE WHEN role_visibility = 'all' OR role_visibility LIKE '%student%' THEN 1 ELSE 0 END) as student,
                  SUM(CASE WHEN role_visibility = 'all' OR role_visibility LIKE '%instructor%' THEN 1 ELSE 0 END) as instructor,
                  SUM(CASE WHEN role_visibility = 'all' THEN 1 ELSE 0 END) as all_users
                FROM faqs 
                WHERE deleted_at IS NULL";

$stats_result = $conn->query($stats_query);
if ($stats_result && $stats_row = $stats_result->fetch_assoc()) {
    $totalFaqs = intval($stats_row['total']);
    $activeFaqs = intval($stats_row['active']);
    $studentFaqs = intval($stats_row['student']);
    $instructorFaqs = intval($stats_row['instructor']);
    $allUsersFaqs = intval($stats_row['all_users']);
}
?>

<!-- / Navbar -->

<!-- Toast Notification -->
<div class="bs-toast toast toast-placement-ex m-2 fade bg-danger top-0 end-0" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000" id="errorToast" style="z-index: 9999; position: fixed;">
    <div class="toast-header">
        <i class="bx bx-bell me-2"></i>
        <div class="me-auto fw-semibold">Error</div>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body" id="errorToastMessage"></div>
</div>

<div class="bs-toast toast toast-placement-ex m-2 fade bg-success top-0 end-0" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000" id="successToast" style="z-index: 9999; position: fixed;">
    <div class="toast-header">
        <i class="bx bx-check me-2"></i>
        <div class="me-auto fw-semibold">Success</div>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body" id="successToastMessage"></div>
</div>
<!-- /Toast Notification -->

<!-- Loading Overlay -->
<div class="custom-overlay" id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center; flex-direction: column;">
    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
        <span class="visually-hidden">Loading...</span>
    </div>
    <div class="text-white mt-3" id="loading-message">Processing...</div>
</div>

<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Admin /</span> FAQs Management
    </h4>

    <!-- Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Total FAQs</p>
                            <div class="d-flex align-items-end mb-2">
                                <h4 class="card-title mb-0 me-2" id="total-faqs-count"><?php echo $totalFaqs; ?></h4>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-primary rounded p-2">
                                <i class="bx bx-question-mark bx-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Active FAQs</p>
                            <div class="d-flex align-items-end mb-2">
                                <h4 class="card-title mb-0 me-2" id="active-faqs-count"><?php echo $activeFaqs; ?></h4>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-success rounded p-2">
                                <i class="bx bx-check-circle bx-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Student FAQs</p>
                            <div class="d-flex align-items-end mb-2">
                                <h4 class="card-title mb-0 me-2" id="student-faqs-count"><?php echo $studentFaqs; ?></h4>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-info rounded p-2">
                                <i class="bx bx-user bx-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Instructor FAQs</p>
                            <div class="d-flex align-items-end mb-2">
                                <h4 class="card-title mb-0 me-2" id="instructor-faqs-count"><?php echo $instructorFaqs; ?></h4>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge bg-label-warning rounded p-2">
                                <i class="bx bx-chalkboard bx-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQs List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">FAQs List</h5>
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="text" class="form-control" id="faqSearch" placeholder="Search FAQs..." aria-label="Search">
                    </div>
                </div>
                <div class="me-3">
                    <div class="btn-group" role="group" aria-label="Filter FAQs">
                        <button type="button" class="btn btn-outline-secondary btn-sm filter-badge active" data-filter="all">
                            All
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm filter-badge" data-filter="active">
                            Active
                        </button>
                        <button type="button" class="btn btn-outline-warning btn-sm filter-badge" data-filter="inactive">
                            Inactive
                        </button>
                        <button type="button" class="btn btn-outline-info btn-sm filter-badge" data-filter="student">
                            Students
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm filter-badge" data-filter="instructor">
                            Instructors
                        </button>
                    </div>
                </div>
                <button type="button" class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#createFaqModal">
                    <i class="bx bx-plus me-1"></i> Add New FAQ
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="exportBtn">
                    <i class="bx bx-export me-1"></i> Export
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="faqsTable">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Question</th>
                            <th>Visibility</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="faqsTableBody">
                        <!-- Table content will be loaded dynamically -->
                        <tr>
                            <td colspan="6" class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading FAQs...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="row mt-3">
                <div class="col-sm-12 col-md-6">
                    <div class="dataTables_info" id="dataTable_info" role="status" aria-live="polite">
                        Showing <span id="showing-start">0</span> to <span id="showing-end">0</span> of <span id="total-entries">0</span> entries
                    </div>
                </div>
                <div class="col-sm-12 col-md-6">
                    <div class="dataTables_paginate paging_simple_numbers" id="dataTable_paginate">
                        <ul class="pagination justify-content-end" id="pagination-container">
                            <li class="paginate_button page-item previous disabled" id="dataTable_previous">
                                <a href="#" aria-controls="dataTable" data-dt-idx="0" tabindex="0" class="page-link">Previous</a>
                            </li>
                            <li class="paginate_button page-item next disabled" id="dataTable_next">
                                <a href="#" aria-controls="dataTable" data-dt-idx="2" tabindex="0" class="page-link">Next</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- / Content -->

<!-- Create FAQ Modal -->
<div class="modal fade" id="createFaqModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New FAQ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createFaqForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="faqCategory" class="form-label">Category</label>
                            <select id="faqCategory" class="form-select" required>
                                <option value="">Select Category</option>
                                <option value="Account">Account</option>
                                <option value="Courses">Courses</option>
                                <option value="Instructor">Instructor</option>
                                <option value="Payments">Payments</option>
                                <option value="Technical">Technical</option>
                                <option value="Department">Department</option>
                                <option value="Certificates">Certificates</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="faqVisibility" class="form-label">Visibility</label>
                            <select id="faqVisibility" class="form-select" required>
                                <option value="all">All Users</option>
                                <option value="student">Students Only</option>
                                <option value="instructor">Instructors Only</option>
                                <option value="department_head">Department Heads Only</option>
                                <option value="admin">Admins Only</option>
                                <option value="student,instructor">Students & Instructors</option>
                                <option value="department_head,instructor">Department Heads & Instructors</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="faqQuestion" class="form-label">Question</label>
                        <input type="text" id="faqQuestion" class="form-control" placeholder="Enter the question" required />
                    </div>

                    <div class="mb-3">
                        <label for="faqAnswer" class="form-label">Answer</label>
                        <textarea id="faqAnswer" class="form-control" rows="6" placeholder="Enter the answer" required></textarea>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="faqStatus" checked>
                            <label class="form-check-label" for="faqStatus">Active</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveFaqBtn">Save FAQ</button>
            </div>
        </div>
    </div>
</div>

<!-- View FAQ Modal -->
<div class="modal fade" id="viewFaqModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View FAQ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6 class="text-muted">Category</h6>
                        <p id="viewCategory"></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Visibility</h6>
                        <p id="viewVisibility"></p>
                    </div>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted">Question</h6>
                    <p id="viewQuestion" class="fs-5 fw-semibold"></p>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted">Answer</h6>
                    <div id="viewAnswer" class="p-3 bg-light rounded"></div>
                </div>
                <div class="row mb-0">
                    <div class="col-md-4">
                        <h6 class="text-muted">Status</h6>
                        <p id="viewStatus"></p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Created At</h6>
                        <p id="viewCreatedAt"></p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Last Updated</h6>
                        <p id="viewLastUpdated"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="editFromViewBtn">Edit</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit FAQ Modal -->
<div class="modal fade" id="editFaqModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit FAQ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editFaqForm">
                    <input type="hidden" id="editFaqId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editFaqCategory" class="form-label">Category</label>
                            <select id="editFaqCategory" class="form-select" required>
                                <option value="">Select Category</option>
                                <option value="Account">Account</option>
                                <option value="Courses">Courses</option>
                                <option value="Instructor">Instructor</option>
                                <option value="Payments">Payments</option>
                                <option value="Technical">Technical</option>
                                <option value="Department">Department</option>
                                <option value="Certificates">Certificates</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editFaqVisibility" class="form-label">Visibility</label>
                            <select id="editFaqVisibility" class="form-select" required>
                                <option value="all">All Users</option>
                                <option value="student">Students Only</option>
                                <option value="instructor">Instructors Only</option>
                                <option value="department_head">Department Heads Only</option>
                                <option value="admin">Admins Only</option>
                                <option value="student,instructor">Students & Instructors</option>
                                <option value="department_head,instructor">Department Heads & Instructors</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="editFaqQuestion" class="form-label">Question</label>
                        <input type="text" id="editFaqQuestion" class="form-control" placeholder="Enter the question" required />
                    </div>

                    <div class="mb-3">
                        <label for="editFaqAnswer" class="form-label">Answer</label>
                        <textarea id="editFaqAnswer" class="form-control" rows="6" placeholder="Enter the answer" required></textarea>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="editFaqStatus">
                            <label class="form-check-label" for="editFaqStatus">Active</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="updateFaqBtn">Update FAQ</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteFaqModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete FAQ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="deleteFaqId">
                <p>Are you sure you want to delete this FAQ? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Pagination variables
        let currentPage = 1;
        const defaultPerPage = 10;
        let currentFilter = 'all';
        let currentSearch = '';

        // Initial load
        loadFaqs();

        // Search functionality
        document.getElementById('faqSearch').addEventListener('keyup', function(e) {
            currentSearch = this.value.trim();
            // Reset to first page when searching
            currentPage = 1;
            // Slight delay to avoid too many requests while typing
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                loadFaqs();
            }, 500);
        });

        // Filter buttons
        document.querySelectorAll('.filter-badge').forEach(button => {
            button.addEventListener('click', function() {
                // Update active button
                document.querySelectorAll('.filter-badge').forEach(btn => {
                    btn.classList.remove('active');
                });
                this.classList.add('active');

                // Get filter value
                currentFilter = this.getAttribute('data-filter');

                // Reset to first page when filtering
                currentPage = 1;

                // Load faqs with new filter
                loadFaqs();
            });
        });

        // Pagination event listeners
        document.querySelector('#dataTable_previous').addEventListener('click', function(e) {
            e.preventDefault();
            if (currentPage > 1) {
                currentPage--;
                loadFaqs();
            }
        });

        document.querySelector('#dataTable_next').addEventListener('click', function(e) {
            e.preventDefault();
            currentPage++;
            loadFaqs();
        });

        // Save new FAQ
        document.getElementById('saveFaqBtn').addEventListener('click', function() {
            const category = document.getElementById('faqCategory').value;
            const visibility = document.getElementById('faqVisibility').value;
            const question = document.getElementById('faqQuestion').value;
            const answer = document.getElementById('faqAnswer').value;
            const status = document.getElementById('faqStatus').checked;

            // Validate form
            if (!category || !visibility || !question || !answer) {
                showErrorToast('Please fill in all required fields');
                return;
            }

            // Show loading overlay
            showOverlay('Creating new FAQ...');

            // Prepare form data
            const formData = new FormData();
            formData.append('category', category);
            formData.append('question', question);
            formData.append('answer', answer);
            formData.append('role_visibility', visibility);
            formData.append('status', status);

            // Send request
            fetch('../backend/admin/add_faq.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // Hide loading overlay
                    removeOverlay();

                    if (data.success) {
                        // Hide the modal
                        const createModal = bootstrap.Modal.getInstance(document.getElementById('createFaqModal'));
                        createModal.hide();

                        // Reset form
                        document.getElementById('createFaqForm').reset();

                        // Show success message
                        showSuccessToast(data.message);

                        // Reload FAQs
                        loadFaqs();
                    } else {
                        // Show error
                        showErrorToast(data.message || 'An error occurred while adding the FAQ');
                    }
                })
                .catch(error => {
                    removeOverlay();
                    showErrorToast('An error occurred: ' + error.message);
                });
        });

        // Update FAQ
        document.getElementById('updateFaqBtn').addEventListener('click', function() {
            const faqId = document.getElementById('editFaqId').value;
            const category = document.getElementById('editFaqCategory').value;
            const visibility = document.getElementById('editFaqVisibility').value;
            const question = document.getElementById('editFaqQuestion').value;
            const answer = document.getElementById('editFaqAnswer').value;
            const status = document.getElementById('editFaqStatus').checked;

            // Validate form
            if (!faqId || !category || !visibility || !question || !answer) {
                showErrorToast('Please fill in all required fields');
                return;
            }

            // Show loading overlay
            showOverlay('Updating FAQ...');

            // Prepare form data
            const formData = new FormData();
            formData.append('faq_id', faqId);
            formData.append('category', category);
            formData.append('question', question);
            formData.append('answer', answer);
            formData.append('role_visibility', visibility);
            formData.append('status', status);

            // Send request
            fetch('../backend/admin/update_faq.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // Hide loading overlay
                    removeOverlay();

                    if (data.success) {
                        // Hide the modal
                        const editModal = bootstrap.Modal.getInstance(document.getElementById('editFaqModal'));
                        editModal.hide();

                        // Show success message
                        showSuccessToast(data.message);

                        // Reload FAQs
                        loadFaqs();
                    } else {
                        // Show error
                        showErrorToast(data.message || 'An error occurred while updating the FAQ');
                    }
                })
                .catch(error => {
                    removeOverlay();
                    showErrorToast('An error occurred: ' + error.message);
                });
        });

        // Delete FAQ
        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            const faqId = document.getElementById('deleteFaqId').value;

            if (!faqId) {
                showErrorToast('FAQ ID is missing');
                return;
            }

            // Show loading overlay
            showOverlay('Deleting FAQ...');

            // Prepare form data
            const formData = new FormData();
            formData.append('faq_id', faqId);

            // Send request
            fetch('../backend/admin/delete_faq.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // Hide loading overlay
                    removeOverlay();

                    if (data.success) {
                        // Hide the modal
                        const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteFaqModal'));
                        deleteModal.hide();

                        // Show success message
                        showSuccessToast(data.message);

                        // Reload FAQs
                        loadFaqs();
                    } else {
                        // Show error
                        showErrorToast(data.message || 'An error occurred while deleting the FAQ');
                    }
                })
                .catch(error => {
                    removeOverlay();
                    showErrorToast('An error occurred: ' + error.message);
                });
        });

        // Export button
        // Export button
        document.getElementById('exportBtn').addEventListener('click', function() {
            // Show loading overlay while preparing download
            showOverlay('Generating CSV export...');

            // Build export URL with current filters
            let exportUrl = '../backend/admin/export_faqs.php?';

            if (currentFilter !== 'all') {
                if (currentFilter === 'active' || currentFilter === 'inactive') {
                    exportUrl += `status=${currentFilter}&`;
                } else {
                    exportUrl += `visibility=${currentFilter}&`;
                }
            }

            if (currentSearch) {
                exportUrl += `search=${encodeURIComponent(currentSearch)}&`;
            }

            // Use fetch to get the CSV data
            fetch(exportUrl)
                .then(response => response.blob())
                .then(blob => {
                    // Create a download link
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;

                    // Generate filename with current date
                    const date = new Date().toISOString().slice(0, 10);
                    a.download = `learnix_faqs_export_${date}.csv`;

                    // Append to body, click and remove
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    a.remove();

                    // Hide overlay and show success
                    removeOverlay();
                    showSuccessToast('FAQs exported successfully');
                })
                .catch(error => {
                    removeOverlay();
                    showErrorToast('Error exporting FAQs: ' + error.message);
                });
        });

        // Functions for CRUD operations

        // Load FAQs from backend
        function loadFaqs() {
            // Show loading state
            document.getElementById('faqsTableBody').innerHTML = `
            <tr>
              <td colspan="6" class="text-center">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading FAQs...</p>
              </td>
            </tr>
        `;

            // Build URL with filters and pagination
            let url = `../backend/admin/get_faqs.php?page=${currentPage}&per_page=${defaultPerPage}`;

            if (currentFilter !== 'all') {
                if (currentFilter === 'active' || currentFilter === 'inactive') {
                    url += `&status=${currentFilter}`;
                } else {
                    url += `&visibility=${currentFilter}`;
                }
            }

            if (currentSearch) {
                url += `&search=${encodeURIComponent(currentSearch)}`;
            }

            // Fetch data
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the statistics
                        updateStatistics(data.stats);

                        // Generate table content
                        renderFaqsTable(data.faqs);

                        // Update pagination
                        updatePagination(data.pagination);
                    } else {
                        // Show error
                        document.getElementById('faqsTableBody').innerHTML = `
                        <tr>
                          <td colspan="6" class="text-center">
                            <i class="bx bx-error-circle text-danger mb-2" style="font-size: 2rem;"></i>
                            <p>${data.message || 'Error loading FAQs'}</p>
                          </td>
                        </tr>
                    `;
                    }
                })
                .catch(error => {
                    document.getElementById('faqsTableBody').innerHTML = `
                    <tr>
                      <td colspan="6" class="text-center">
                        <i class="bx bx-error-circle text-danger mb-2" style="font-size: 2rem;"></i>
                        <p>An error occurred: ${error.message}</p>
                      </td>
                    </tr>
                `;
                });
        }

        // Render FAQs table with data
        function renderFaqsTable(faqs) {
            const tableBody = document.getElementById('faqsTableBody');

            // Clear table
            tableBody.innerHTML = '';

            if (faqs.length === 0) {
                tableBody.innerHTML = `
                <tr>
                  <td colspan="6" class="text-center py-4">
                    <i class="bx bx-info-circle text-primary mb-2" style="font-size: 2rem;"></i>
                    <p>No FAQs found matching your criteria</p>
                  </td>
                </tr>
            `;
                return;
            }

            // Add each FAQ to the table
            faqs.forEach(faq => {
                // Create row
                const row = document.createElement('tr');
                row.setAttribute('data-id', faq.id);
                row.setAttribute('data-status', faq.status);
                row.setAttribute('data-visibility', faq.role_visibility);

                // Category badge class
                let categoryBadgeClass = '';
                switch (faq.category) {
                    case 'Account':
                        categoryBadgeClass = 'secondary';
                        break;
                    case 'Courses':
                        categoryBadgeClass = 'success';
                        break;
                    case 'Instructor':
                        categoryBadgeClass = 'primary';
                        break;
                    case 'Payments':
                        categoryBadgeClass = 'danger';
                        break;
                    case 'Technical':
                        categoryBadgeClass = 'warning';
                        break;
                    case 'Department':
                        categoryBadgeClass = 'info';
                        break;
                    case 'Certificates':
                        categoryBadgeClass = 'dark';
                        break;
                    default:
                        categoryBadgeClass = 'secondary';
                }

                // Visibility badges
                let visibilityHTML = '';
                if (faq.role_visibility === 'all') {
                    visibilityHTML = '<span class="badge bg-label-success">All Users</span>';
                } else {
                    const roles = faq.role_visibility.split(',');
                    roles.forEach(role => {
                        let badgeColor = '';
                        switch (role.trim()) {
                            case 'student':
                                badgeColor = 'info';
                                break;
                            case 'instructor':
                                badgeColor = 'primary';
                                break;
                            case 'department_head':
                                badgeColor = 'warning';
                                break;
                            case 'admin':
                                badgeColor = 'danger';
                                break;
                            default:
                                badgeColor = 'secondary';
                        }
                        visibilityHTML += `<span class="badge bg-label-${badgeColor} me-1">${role.trim().charAt(0).toUpperCase() + role.trim().slice(1)}</span>`;
                    });
                }

                // Build row HTML
                row.innerHTML = `
                <td>
                    <span class="badge bg-label-${categoryBadgeClass} me-1">${faq.category}</span>
                </td>
                <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    ${faq.question}
                </td>
                <td>${visibilityHTML}</td>
                <td>
                    <div class="form-check form-switch">
                        <input class="form-check-input status-toggle" type="checkbox" data-id="${faq.id}" ${faq.status === 'active' ? 'checked' : ''}>
                    </div>
                </td>
                <td>${formatDate(faq.last_updated)}</td>
                <td>
                    <div class="dropdown">
                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item view-faq" href="javascript:void(0);" data-id="${faq.id}">
                                <i class="bx bx-show me-1"></i> View
                            </a>
                            <a class="dropdown-item edit-faq" href="javascript:void(0);" data-id="${faq.id}">
                                <i class="bx bx-edit-alt me-1"></i> Edit
                            </a>
                            <a class="dropdown-item delete-faq" href="javascript:void(0);" data-id="${faq.id}">
                                <i class="bx bx-trash me-1"></i> Delete
                            </a>
                        </div>
                    </div>
                </td>
            `;

                tableBody.appendChild(row);
            });

            // Add event listeners to the newly created elements
            addEventListeners();
        }

        // Update pagination controls
        function updatePagination(pagination) {
            if (!pagination) return;

            const {
                total,
                per_page,
                current_page,
                total_pages
            } = pagination;

            // Update showing text
            const start = total === 0 ? 0 : (current_page - 1) * per_page + 1;
            const end = Math.min(current_page * per_page, total);

            document.getElementById('showing-start').textContent = start;
            document.getElementById('showing-end').textContent = end;
            document.getElementById('total-entries').textContent = total;

            // Previous button
            const prevButton = document.getElementById('dataTable_previous');
            if (current_page <= 1) {
                prevButton.classList.add('disabled');
            } else {
                prevButton.classList.remove('disabled');
            }

            // Next button
            const nextButton = document.getElementById('dataTable_next');
            if (current_page >= total_pages) {
                nextButton.classList.add('disabled');
            } else {
                nextButton.classList.remove('disabled');
            }

            // Page numbers
            const paginationContainer = document.getElementById('pagination-container');

            // Remove existing page numbers (keep previous and next buttons)
            const pageItems = paginationContainer.querySelectorAll('.page-item:not(.previous):not(.next)');
            pageItems.forEach(item => item.remove());

            // Add page numbers
            for (let i = 1; i <= total_pages; i++) {
                const pageItem = document.createElement('li');
                pageItem.className = `paginate_button page-item ${i === current_page ? 'active' : ''}`;

                const pageLink = document.createElement('a');
                pageLink.href = '#';
                pageLink.setAttribute('aria-controls', 'dataTable');
                pageLink.setAttribute('data-dt-idx', i);
                pageLink.setAttribute('tabindex', '0');
                pageLink.className = 'page-link';
                pageLink.textContent = i;

                pageLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    currentPage = i;
                    loadFaqs();
                });

                pageItem.appendChild(pageLink);

                // Insert before next button
                paginationContainer.insertBefore(pageItem, nextButton);
            }
        }

        // Update statistics display
        function updateStatistics(stats) {
            if (!stats) return;

            document.getElementById('total-faqs-count').textContent = stats.total;
            document.getElementById('active-faqs-count').textContent = stats.active;
            document.getElementById('student-faqs-count').textContent = stats.student;
            document.getElementById('instructor-faqs-count').textContent = stats.instructor;
        }

        // Add event listeners to dynamically created elements
        function addEventListeners() {
            // Status toggle switches
            document.querySelectorAll('.status-toggle').forEach(toggle => {
                toggle.addEventListener('change', function() {
                    const faqId = this.getAttribute('data-id');
                    const isActive = this.checked;

                    // Prepare form data
                    const formData = new FormData();
                    formData.append('faq_id', faqId);
                    formData.append('status', isActive);

                    // Send request
                    fetch('../backend/admin/toggle_faq_status.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Update row data attribute
                                const row = document.querySelector(`tr[data-id="${faqId}"]`);
                                if (row) {
                                    row.setAttribute('data-status', isActive ? 'active' : 'inactive');
                                }

                                // Show success message
                                if (isActive) {
                                    showSuccessToast('FAQ activated successfully');
                                } else {
                                    // Special toast for deactivation - using warning style
                                    const toast = document.getElementById('successToast');
                                    toast.classList.remove('bg-success');
                                    toast.classList.add('bg-warning');
                                    document.getElementById('successToastMessage').textContent = 'FAQ deactivated successfully';

                                    // Show toast
                                    const bsToast = new bootstrap.Toast(toast);
                                    bsToast.show();

                                    // Reset toast class after it's hidden
                                    toast.addEventListener('hidden.bs.toast', function() {
                                        toast.classList.remove('bg-warning');
                                        toast.classList.add('bg-success');
                                    }, {
                                        once: true
                                    });
                                }

                                // Refresh stats in background
                                refreshStats();
                            } else {
                                // Show error and revert the toggle
                                showErrorToast(data.message || 'Error updating FAQ status');
                                this.checked = !isActive;
                            }
                        })
                        .catch(error => {
                            // Show error and revert the toggle
                            showErrorToast('An error occurred: ' + error.message);
                            this.checked = !isActive;
                        });
                });
            });

            // View FAQ buttons
            document.querySelectorAll('.view-faq').forEach(button => {
                button.addEventListener('click', function() {
                    const faqId = this.getAttribute('data-id');
                    viewFaq(faqId);
                });
            });

            // Edit FAQ buttons
            document.querySelectorAll('.edit-faq').forEach(button => {
                button.addEventListener('click', function() {
                    const faqId = this.getAttribute('data-id');
                    editFaq(faqId);
                });
            });

            // Delete FAQ buttons
            document.querySelectorAll('.delete-faq').forEach(button => {
                button.addEventListener('click', function() {
                    const faqId = this.getAttribute('data-id');
                    document.getElementById('deleteFaqId').value = faqId;

                    // Show delete confirmation modal
                    const deleteModal = new bootstrap.Modal(document.getElementById('deleteFaqModal'));
                    deleteModal.show();
                });
            });
        }

        // View FAQ details
        function viewFaq(faqId) {
            // Fetch FAQ details
            showOverlay('Loading FAQ details...');

            fetch(`../backend/admin/get_faqs.php?id=${faqId}`)
                .then(response => response.json())
                .then(data => {
                    removeOverlay();

                    if (data.success && data.faqs.length > 0) {
                        const faq = data.faqs[0];

                        document.getElementById('viewCategory').textContent = faq.category;

                        // Format visibility
                        let visibilityHtml = '';
                        if (faq.role_visibility === 'all') {
                            visibilityHtml = '<span class="badge bg-label-success">All Users</span>';
                        } else {
                            const roles = faq.role_visibility.split(',');
                            roles.forEach(role => {
                                let badgeColor = '';
                                switch (role.trim()) {
                                    case 'student':
                                        badgeColor = 'info';
                                        break;
                                    case 'instructor':
                                        badgeColor = 'primary';
                                        break;
                                    case 'department_head':
                                        badgeColor = 'warning';
                                        break;
                                    case 'admin':
                                        badgeColor = 'danger';
                                        break;
                                    default:
                                        badgeColor = 'secondary';
                                }
                                visibilityHtml += `<span class="badge bg-label-${badgeColor} me-1">${role.trim().charAt(0).toUpperCase() + role.trim().slice(1)}</span>`;
                            });
                        }
                        document.getElementById('viewVisibility').innerHTML = visibilityHtml;

                        document.getElementById('viewQuestion').textContent = faq.question;
                        document.getElementById('viewAnswer').textContent = faq.answer;

                        // Format status
                        const statusHtml = faq.status === 'active' ?
                            '<span class="badge bg-success">Active</span>' :
                            '<span class="badge bg-secondary">Inactive</span>';
                        document.getElementById('viewStatus').innerHTML = statusHtml;

                        document.getElementById('viewCreatedAt').textContent = formatDate(faq.created_at);
                        document.getElementById('viewLastUpdated').textContent = formatDate(faq.last_updated);

                        // Store faq id for edit button
                        document.getElementById('editFromViewBtn').setAttribute('data-id', faqId);

                        // Show the modal
                        const viewModal = new bootstrap.Modal(document.getElementById('viewFaqModal'));
                        viewModal.show();
                    } else {
                        showErrorToast(data.message || 'Error loading FAQ details');
                    }
                })
                .catch(error => {
                    removeOverlay();
                    showErrorToast('An error occurred: ' + error.message);
                });
        }

        // Edit FAQ
        function editFaq(faqId) {
            // Fetch FAQ details
            showOverlay('Loading FAQ details...');

            fetch(`../backend/admin/get_faqs.php?id=${faqId}`)
                .then(response => response.json())
                .then(data => {
                    removeOverlay();

                    if (data.success && data.faqs.length > 0) {
                        const faq = data.faqs[0];

                        document.getElementById('editFaqId').value = faq.id;
                        document.getElementById('editFaqCategory').value = faq.category;
                        document.getElementById('editFaqVisibility').value = faq.role_visibility;
                        document.getElementById('editFaqQuestion').value = faq.question;
                        document.getElementById('editFaqAnswer').value = faq.answer;
                        document.getElementById('editFaqStatus').checked = faq.status === 'active';

                        // Show the modal
                        const editModal = new bootstrap.Modal(document.getElementById('editFaqModal'));
                        editModal.show();
                    } else {
                        showErrorToast(data.message || 'Error loading FAQ details');
                    }
                })
                .catch(error => {
                    removeOverlay();
                    showErrorToast('An error occurred: ' + error.message);
                });
        }

        // Edit from view button
        document.getElementById('editFromViewBtn').addEventListener('click', function() {
            const faqId = this.getAttribute('data-id');

            // Hide view modal
            const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewFaqModal'));
            viewModal.hide();

            // Trigger edit with delay to avoid modal conflicts
            setTimeout(() => {
                editFaq(faqId);
            }, 500);
        });

        // Refresh statistics
        function refreshStats() {
            fetch('../backend/admin/get_faqs.php?stats_only=1')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateStatistics(data.stats);
                    }
                })
                .catch(error => {
                    console.error('Error refreshing stats:', error);
                });
        }

        // Helper function to format date
        function formatDate(dateString) {
            if (!dateString) return 'N/A';

            const options = {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };

            return new Date(dateString).toLocaleDateString(undefined, options);
        }

        // Toast notification handlers
        function showSuccessToast(message) {
            document.getElementById('successToastMessage').textContent = message;
            const toast = new bootstrap.Toast(document.getElementById('successToast'));
            toast.show();
        }

        function showErrorToast(message) {
            document.getElementById('errorToastMessage').textContent = message;
            const toast = new bootstrap.Toast(document.getElementById('errorToast'));
            toast.show();
        }

        // Loading overlay
        function showOverlay(message = 'Processing...') {
            const overlay = document.getElementById('loadingOverlay');
            document.getElementById('loading-message').textContent = message;
            overlay.style.display = 'flex';
        }

        function removeOverlay() {
            const overlay = document.getElementById('loadingOverlay');
            overlay.style.display = 'none';
        }
    });
</script>

<?php include_once '../includes/admin/footer.php'; ?>