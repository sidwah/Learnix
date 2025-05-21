<?php
// Authentication check
require_once '../backend/auth/admin/admin-auth-check.php';

// Set page title
$pageTitle = "Issue Reports - Admin | Learnix";

include_once '../includes/admin/header.php';
include_once '../includes/admin/sidebar.php';
include_once '../includes/admin/navbar.php';
?>

<!-- Loading Overlay -->
<div class="custom-overlay" id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center; flex-direction: column;">
    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
        <span class="visually-hidden">Loading...</span>
    </div>
    <div class="text-white mt-3" id="loading-message">Processing...</div>
</div>

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

<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">
  <!-- Summary Cards -->
  <div class="row mb-4">
    <!-- Total Issues -->
    <div class="col-md-6 col-lg-3 mb-3">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h5 class="card-title mb-1">Total Issues</h5>
              <p class="card-text" data-type="total">0</p>
            </div>
            <div class="bg-label-primary rounded-circle p-3">
              <i class="bx bx-error-circle bx-md text-primary"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Pending Issues -->
    <div class="col-md-6 col-lg-3 mb-3">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h5 class="card-title mb-1">Pending Issues</h5>
              <p class="card-text" data-type="pending">0</p>
            </div>
            <div class="bg-label-warning rounded-circle p-3">
              <i class="bx bx-time bx-md text-warning"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- In Progress Issues -->
    <div class="col-md-6 col-lg-3 mb-3">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h5 class="card-title mb-1">In Progress</h5>
              <p class="card-text" data-type="in_progress">0</p>
            </div>
            <div class="bg-label-info rounded-circle p-3">
              <i class="bx bx-refresh bx-md text-info"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Resolved Issues -->
    <div class="col-md-6 col-lg-3 mb-3">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h5 class="card-title mb-1">Resolved Issues</h5>
              <p class="card-text" data-type="resolved">0</p>
            </div>
            <div class="bg-label-success rounded-circle p-3">
              <i class="bx bx-check-circle bx-md text-success"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Issue Reports Table -->
  <div class="card">
    <!-- Filter and Search -->
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Issue Reports</h5>
      <div class="d-flex align-items-center">
        <div class="me-3">
          <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="bx bx-search"></i></span>
            <input type="text" class="form-control" id="issueSearch" placeholder="Search issues..." aria-label="Search">
          </div>
        </div>
        <div class="me-3">
          <div class="btn-group" role="group" aria-label="Filter by status">
            <button type="button" class="btn btn-outline-secondary btn-sm filter-status active" data-status="all">
              All
            </button>
            <button type="button" class="btn btn-outline-warning btn-sm filter-status" data-status="Pending">
              Pending
            </button>
            <button type="button" class="btn btn-outline-info btn-sm filter-status" data-status="In Progress">
              In Progress
            </button>
            <button type="button" class="btn btn-outline-success btn-sm filter-status" data-status="Resolved">
              Resolved
            </button>
          </div>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="exportBtn">
          <i class="bx bx-export me-1"></i> Export
        </button>
      </div>
    </div>
    <!-- Table -->
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>User Type</th>
              <th>Issue Type</th>
              <th>Description</th>
              <th>Status</th>
              <th>Created At</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="issueTableBody"></tbody>
        </table>
      </div>
      <!-- Pagination -->
      <nav aria-label="Issue reports pagination" class="mt-3">
        <ul class="pagination justify-content-center" id="pagination">
          <!-- Pagination items will be added dynamically -->
        </ul>
      </nav>
    </div>
  </div>
</div>
<!-- / Content -->

<?php include_once '../includes/admin/footer.php'; ?>

<!-- JavaScript for Filtering, Search, Toasts, Pagination, and AJAX -->
<script>
  // Toast initialization
  const successToast = new bootstrap.Toast(document.getElementById('successToast'));
  const errorToast = new bootstrap.Toast(document.getElementById('errorToast'));

  // Show loading overlay
  function showLoading(message = 'Processing...') {
    document.getElementById('loading-message').textContent = message;
    document.getElementById('loadingOverlay').style.display = 'flex';
  }

  // Hide loading overlay
  function hideLoading() {
    document.getElementById('loadingOverlay').style.display = 'none';
  }

  // Show toast
  function showToast(type, message) {
    const toast = type === 'success' ? successToast : errorToast;
    const messageElement = document.getElementById(type + 'ToastMessage');
    messageElement.textContent = message;
    toast.show();
  }

  // Pagination state
  let currentPage = 1;
  const itemsPerPage = 10;
  let totalPages = 1;

  // Fetch and render issue reports
  function loadIssues(page = 1) {
    showLoading('Loading issues...');
    fetch(`../backend/admin/get_issue_reports.php?page=${page}&per_page=${itemsPerPage}`)
      .then(response => response.json())
      .then(data => {
        hideLoading();
        if (!data.success) {
          showToast('error', data.message || 'Failed to load issues');
          return;
        }

        const { issues, summary, total_pages } = data.data;
        totalPages = total_pages || 1;
        currentPage = page;

        // Update summary cards
        document.querySelector('.card-text[data-type="total"]').textContent = summary.total;
        document.querySelector('.card-text[data-type="pending"]').textContent = summary.pending;
        document.querySelector('.card-text[data-type="in_progress"]').textContent = summary.in_progress;
        document.querySelector('.card-text[data-type="resolved"]').textContent = summary.resolved;

        // Update table
        const tableBody = document.getElementById('issueTableBody');
        tableBody.innerHTML = '';
        issues.forEach(issue => {
          const row = document.createElement('tr');
          row.setAttribute('data-status', issue.status);
          row.id = `issueRow${issue.id}`;
          row.innerHTML = `
            <td>${issue.user_type}</td>
            <td>${issue.issue_type}</td>
            <td>${issue.description}</td>
            <td><span class="badge bg-label-${issue.status === 'Pending' ? 'warning' : issue.status === 'In Progress' ? 'info' : 'success'}">${issue.status}</span></td>
            <td>${issue.created_at}</td>
            <td>
              <div class="dropdown">
                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                  <i class="bx bx-dots-vertical-rounded"></i>
                </button>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#viewIssueModal${issue.id}">
                    <i class="bx bx-show me-1"></i> View
                  </a>
                  ${issue.status !== 'Resolved' ? `<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editIssueModal${issue.id}">
                    <i class="bx bx-edit-alt me-1"></i> Edit
                  </a>` : ''}
                </div>
              </div>
            </td>
          `;
          tableBody.appendChild(row);

          // Create View modal
          const viewModal = document.createElement('div');
          viewModal.className = 'modal fade';
          viewModal.id = `viewIssueModal${issue.id}`;
          viewModal.tabIndex = -1;
          viewModal.setAttribute('aria-labelledby', `viewIssueModalLabel${issue.id}`);
          viewModal.setAttribute('aria-hidden', 'true');
          viewModal.innerHTML = `
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="viewIssueModalLabel${issue.id}">Issue Details</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <p><strong>User:</strong> ${issue.username}</p>
                  <p><strong>User Type:</strong> ${issue.user_type}</p>
                  <p><strong>Email:</strong> ${issue.email}</p>
                  <p><strong>Issue Type:</strong> ${issue.issue_type}</p>
                  <p><strong>Description:</strong> ${issue.description}</p>
                  <p><strong>Status:</strong> ${issue.status}</p>
                  <p><strong>Created At:</strong> ${issue.created_at}</p>
                  <p><strong>Updated At:</strong> <span id="updatedAt${issue.id}">${issue.updated_at || '-'}</span></p>
                  <p><strong>File Attached:</strong> ${issue.file_path ? `<a href="http://localhost:8888/learnix/${issue.file_path}">Download</a>` : 'None'}</p>
                  <p><strong>Admin Notes:</strong> <span id="adminNotes${issue.id}">${issue.admin_notes || '-'}</span></p>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
              </div>
            </div>
          `;
          document.body.appendChild(viewModal);

          // Create Edit modal (if not Resolved)
          if (issue.status !== 'Resolved') {
            const editModal = document.createElement('div');
            editModal.className = 'modal fade';
            editModal.id = `editIssueModal${issue.id}`;
            editModal.tabIndex = -1;
            editModal.setAttribute('aria-labelledby', `editIssueModalLabel${issue.id}`);
            editModal.setAttribute('aria-hidden', 'true');
            editModal.innerHTML = `
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="editIssueModalLabel${issue.id}">Edit Issue - ${issue.issue_type}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <form>
                      <div class="mb-3">
                        <label for="issueStatus${issue.id}" class="form-label">Status</label>
                        <select class="form-select" id="issueStatus${issue.id}">
                          <option value="Pending" ${issue.status === 'Pending' ? 'selected' : ''}>Pending</option>
                          <option value="In Progress" ${issue.status === 'In Progress' ? 'selected' : ''}>In Progress</option>
                          <option value="Resolved" ${issue.status === 'Resolved' ? 'selected' : ''}>Resolved</option>
                        </select>
                      </div>
                      <div class="mb-3">
                        <label for="issueNotes${issue.id}" class="form-label">Admin Notes</label>
                        <textarea class="form-control" id="issueNotes${issue.id}" rows="4" placeholder="Add notes...">${issue.admin_notes || ''}</textarea>
                      </div>
                    </form>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary save-changes" data-issue-id="${issue.id}">Save Changes</button>
                  </div>
                </div>
              </div>
            `;
            document.body.appendChild(editModal);
          }
        });

        // Update pagination
        updatePagination();
      })
      .catch(error => {
        hideLoading();
        showToast('error', 'Network error: ' + error.message);
      });
  }

  // Update pagination controls
  function updatePagination() {
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';

    // Previous button
    const prevItem = document.createElement('li');
    prevItem.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
    prevItem.innerHTML = `<a class="page-link" href="javascript:void(0);">Previous</a>`;
    prevItem.addEventListener('click', () => {
      if (currentPage > 1) loadIssues(currentPage - 1);
    });
    pagination.appendChild(prevItem);

    // Page numbers
    const maxPagesToShow = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
    let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);
    if (endPage - startPage + 1 < maxPagesToShow) {
      startPage = Math.max(1, endPage - maxPagesToShow + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
      const pageItem = document.createElement('li');
      pageItem.className = `page-item ${i === currentPage ? 'active' : ''}`;
      pageItem.innerHTML = `<a class="page-link" href="javascript:void(0);">${i}</a>`;
      pageItem.addEventListener('click', () => loadIssues(i));
      pagination.appendChild(pageItem);
    }

    // Next button
    const nextItem = document.createElement('li');
    nextItem.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
    nextItem.innerHTML = `<a class="page-link" href="javascript:void(0);">Next</a>`;
    nextItem.addEventListener('click', () => {
      if (currentPage < totalPages) loadIssues(currentPage + 1);
    });
    pagination.appendChild(nextItem);
  }

  // Search functionality (client-side)
  document.getElementById('issueSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#issueTableBody tr');
    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
  });

  // Filter by status (client-side)
  document.querySelectorAll('.filter-status').forEach(button => {
    button.addEventListener('click', function() {
      document.querySelectorAll('.filter-status').forEach(btn => btn.classList.remove('active'));
      this.classList.add('active');
      const status = this.getAttribute('data-status');
      const rows = document.querySelectorAll('#issueTableBody tr');
      rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status');
        row.style.display = (status === 'all' || rowStatus === status) ? '' : 'none';
      });
    });
  });

  // Export button
  document.getElementById('exportBtn').addEventListener('click', function() {
    showLoading('Exporting data...');
    window.location.href = '../backend/admin/export_issue_reports.php';
    setTimeout(() => {
      hideLoading();
      showToast('success', 'Data exported successfully.');
    }, 1000); // Delay to allow download to start
  });

  // Save changes in edit modals
  document.addEventListener('click', function(e) {
    if (e.target.classList.contains('save-changes')) {
      const issueId = e.target.getAttribute('data-issue-id');
      const status = document.getElementById(`issueStatus${issueId}`).value;
      const notes = document.getElementById(`issueNotes${issueId}`).value;

      // Input validation
      if (!status) {
        showToast('error', 'Please select a status.');
        return;
      }

      showLoading('Saving changes...');
      fetch('../backend/admin/update_issue.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ issue_id: issueId, status, admin_notes: notes })
      })
        .then(response => response.json())
        .then(data => {
          hideLoading();
          if (!data.success) {
            showToast('error', data.message || 'Failed to update issue');
            return;
          }

          showToast('success', data.message);
          const row = document.getElementById(`issueRow${issueId}`);
          if (row) {
            const statusCell = row.querySelector('.badge');
            statusCell.textContent = status;
            statusCell.className = `badge bg-label-${status === 'Pending' ? 'warning' : status === 'In Progress' ? 'info' : 'success'}`;
            row.setAttribute('data-status', status);
            if (status === 'Resolved') {
              const editLink = row.querySelector(`.dropdown-menu a[data-bs-target="#editIssueModal${issueId}"]`);
              if (editLink) editLink.remove();
            }
          }
          // Update modal data
          document.getElementById(`adminNotes${issueId}`).textContent = notes || '-';
          document.getElementById(`updatedAt${issueId}`).textContent = new Date().toLocaleString();
          const modal = bootstrap.Modal.getInstance(document.getElementById(`editIssueModal${issueId}`));
          modal.hide();
        })
        .catch(error => {
          hideLoading();
          showToast('error', 'Network error: ' + error.message);
        });
    }
  });

  // Load issues on page load
  document.addEventListener('DOMContentLoaded', () => loadIssues(1));
</script>