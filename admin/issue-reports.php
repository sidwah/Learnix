<?php
// Authentication check
require_once '../backend/auth/admin/admin-auth-check.php';

// Set page title
$pageTitle = "Issue Reports - Admin | Learnix";

include_once '../includes/admin/header.php';
include_once '../includes/admin/sidebar.php';
include_once '../includes/admin/navbar.php';
?>

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
              <p class="card-text">150</p>
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
              <p class="card-text">50</p>
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
              <p class="card-text">40</p>
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
              <p class="card-text">60</p>
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
          <tbody id="issueTableBody">
            <!-- Sample Row 1 -->
            <tr data-status="Pending">
              <td>Student</td>
              <td>Technical</td>
              <td>Unable to access course videos</td>
              <td><span class="badge bg-label-warning">Pending</span></td>
              <td>2025-05-20 10:30</td>
              <td>
                <div class="dropdown">
                  <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="bx bx-dots-vertical-rounded"></i>
                  </button>
                  <div class="dropdown-menu">
                    <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#viewIssueModal1">
                      <i class="bx bx-show me-1"></i> View
                    </a>
                    <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editIssueModal1">
                      <i class="bx bx-edit-alt me-1"></i> Edit
                    </a>
                  </div>
                </div>
              </td>
            </tr>
            <!-- Sample Row 2 -->
            <tr data-status="In Progress">
              <td>Instructor</td>
              <td>Course</td>
              <td>Incorrect quiz answers marked</td>
              <td><span class="badge bg-label-info">In Progress</span></td>
              <td>2025-05-19 14:45</td>
              <td>
                <div class="dropdown">
                  <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="bx bx-dots-vertical-rounded"></i>
                  </button>
                  <div class="dropdown-menu">
                    <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#viewIssueModal2">
                      <i class="bx bx-show me-1"></i> View
                    </a>
                    <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editIssueModal2">
                      <i class="bx bx-edit-alt me-1"></i> Edit
                    </a>
                  </div>
                </div>
              </td>
            </tr>
            <!-- Sample Row 3 -->
            <tr data-status="Resolved">
              <td>Student</td>
              <td>Account</td>
              <td>Payment not reflecting</td>
              <td><span class="badge bg-label-success">Resolved</span></td>
              <td>2025-05-18 09:15</td>
              <td>
                <div class="dropdown">
                  <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="bx bx-dots-vertical-rounded"></i>
                  </button>
                  <div class="dropdown-menu">
                    <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#viewIssueModal3">
                      <i class="bx bx-show me-1"></i> View
                    </a>
                  </div>
                </div>
              </td>
            </tr>
            <!-- Sample Row 4 -->
            <tr data-status="Pending">
              <td>Admin</td>
              <td>Other</td>
              <td>Dashboard layout issue</td>
              <td><span class="badge bg-label-warning">Pending</span></td>
              <td>2025-05-17 16:20</td>
              <td>
                <div class="dropdown">
                  <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="bx bx-dots-vertical-rounded"></i>
                  </button>
                  <div class="dropdown-menu">
                    <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#viewIssueModal4">
                      <i class="bx bx-show me-1"></i> View
                    </a>
                    <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editIssueModal4">
                      <i class="bx bx-edit-alt me-1"></i> Edit
                    </a>
                  </div>
                </div>
              </td>
            </tr>
            <!-- Sample Row 5 -->
            <tr data-status="In Progress">
              <td>Student</td>
              <td>Technical</td>
              <td>Login issues on mobile app</td>
              <td><span class="badge bg-label-info">In Progress</span></td>
              <td>2025-05-16 12:00</td>
              <td>
                <div class="dropdown">
                  <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="bx bx-dots-vertical-rounded"></i>
                  </button>
                  <div class="dropdown-menu">
                    <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#viewIssueModal5">
                      <i class="bx bx-show me-1"></i> View
                    </a>
                    <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editIssueModal5">
                      <i class="bx bx-edit-alt me-1"></i> Edit
                    </a>
                  </div>
                </div>
              </td>
            </tr>
            <!-- Sample Row 6 -->
            <tr data-status="Resolved">
              <td>Instructor</td>
              <td>Course</td>
              <td>Assignment submission error</td>
              <td><span class="badge bg-label-success">Resolved</span></td>
              <td>2025-05-15 08:30</td>
              <td>
                <div class="dropdown">
                  <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="bx bx-dots-vertical-rounded"></i>
                  </button>
                  <div class="dropdown-menu">
                    <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#viewIssueModal6">
                      <i class="bx bx-show me-1"></i> View
                    </a>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<!-- / Content -->

<!-- View Issue Modals -->
<div class="modal fade" id="viewIssueModal1" tabindex="-1" aria-labelledby="viewIssueModalLabel1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewIssueModalLabel1">Issue Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>User:</strong> John Doe</p>
        <p><strong>User Type:</strong> Student</p>
        <p><strong>Email:</strong> john.doe@example.com</p>
        <p><strong>Issue Type:</strong> Technical</p>
        <p><strong>Description:</strong> Unable to access course videos due to a playback error on the platform.</p>
        <p><strong>Status:</strong> Pending</p>
        <p><strong>Created At:</strong> 2025-05-20 10:30</p>
        <p><strong>File Attached:</strong> <a href="#">screenshot.png</a></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="viewIssueModal2" tabindex="-1" aria-labelledby="viewIssueModalLabel2" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewIssueModalLabel2">Issue Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>User:</strong> Jane Smith</p>
        <p><strong>User Type:</strong> Instructor</p>
        <p><strong>Email:</strong> jane.smith@example.com</p>
        <p><strong>Issue Type:</strong> Course</p>
        <p><strong>Description:</strong> Incorrect quiz answers marked in course ID 123.</p>
        <p><strong>Status:</strong> In Progress</p>
        <p><strong>Created At:</strong> 2025-05-19 14:45</p>
        <p><strong>File Attached:</strong> None</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="viewIssueModal3" tabindex="-1" aria-labelledby="viewIssueModalLabel3" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewIssueModalLabel3">Issue Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>User:</strong> Alice Johnson</p>
        <p><strong>User Type:</strong> Student</p>
        <p><strong>Email:</strong> alice.johnson@example.com</p>
        <p><strong>Issue Type:</strong> Account</p>
        <p><strong>Description:</strong> Payment for course ID 456 not reflecting in account.</p>
        <p><strong>Status:</strong> Resolved</p>
        <p><strong>Created At:</strong> 2025-05-18 09:15</p>
        <p><strong>File Attached:</strong> <a href="#">payment_receipt.pdf</a></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="viewIssueModal4" tabindex="-1" aria-labelledby="viewIssueModalLabel4" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewIssueModalLabel4">Issue Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>User:</strong> Bob Wilson</p>
        <p><strong>User Type:</strong> Admin</p>
        <p><strong>Email:</strong> bob.wilson@example.com</p>
        <p><strong>Issue Type:</strong> Other</p>
        <p><strong>Description:</strong> Dashboard layout misaligned on smaller screens.</p>
        <p><strong>Status:</strong> Pending</p>
        <p><strong>Created At:</strong> 2025-05-17 16:20</p>
        <p><strong>File Attached:</strong> <a href="#">dashboard_screenshot.jpg</a></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="viewIssueModal5" tabindex="-1" aria-labelledby="viewIssueModalLabel5" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewIssueModalLabel5">Issue Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>User:</strong> Emma Brown</p>
        <p><strong>User Type:</strong> Student</p>
        <p><strong>Email:</strong> emma.brown@example.com</p>
        <p><strong>Issue Type:</strong> Technical</p>
        <p><strong>Description:</strong> Login issues on mobile app after recent update.</p>
        <p><strong>Status:</strong> In Progress</p>
        <p><strong>Created At:</strong> 2025-05-16 12:00</p>
        <p><strong>File Attached:</strong> None</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="viewIssueModal6" tabindex="-1" aria-labelledby="viewIssueModalLabel6" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewIssueModalLabel6">Issue Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>User:</strong> Michael Lee</p>
        <p><strong>User Type:</strong> Instructor</p>
        <p><strong>Email:</strong> michael.lee@example.com</p>
        <p><strong>Issue Type:</strong> Course</p>
        <p><strong>Description:</strong> Assignment submission error for course ID 789.</p>
        <p><strong>Status:</strong> Resolved</p>
        <p><strong>Created At:</strong> 2025-05-15 08:30</p>
        <p><strong>File Attached:</strong> <a href="#">error_log.txt</a></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Issue Modals -->
<div class="modal fade" id="editIssueModal1" tabindex="-1" aria-labelledby="editIssueModalLabel1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editIssueModalLabel1">Edit Issue - Technical</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form>
          <div class="mb-3">
            <label for="issueStatus1" class="form-label">Status</label>
            <select class="form-select" id="issueStatus1">
              <option value="Pending" selected>Pending</option>
              <option value="In Progress">In Progress</option>
              <option value="Resolved">Resolved</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="issueNotes1" class="form-label">Admin Notes</label>
            <textarea class="form-control" id="issueNotes1" rows="4" placeholder="Add notes..."></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary">Save Changes</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="editIssueModal2" tabindex="-1" aria-labelledby="editIssueModalLabel2" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editIssueModalLabel2">Edit Issue - Course</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form>
          <div class="mb-3">
            <label for="issueStatus2" class="form-label">Status</label>
            <select class="form-select" id="issueStatus2">
              <option value="Pending">Pending</option>
              <option value="In Progress" selected>In Progress</option>
              <option value="Resolved">Resolved</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="issueNotes2" class="form-label">Admin Notes</label>
            <textarea class="form-control" id="issueNotes2" rows="4" placeholder="Add notes..."></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary">Save Changes</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="editIssueModal4" tabindex="-1" aria-labelledby="editIssueModalLabel4" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editIssueModalLabel4">Edit Issue - Other</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form>
          <div class="mb-3">
            <label for="issueStatus4" class="form-label">Status</label>
            <select class="form-select" id="issueStatus4">
              <option value="Pending" selected>Pending</option>
              <option value="In Progress">In Progress</option>
              <option value="Resolved">Resolved</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="issueNotes4" class="form-label">Admin Notes</label>
            <textarea class="form-control" id="issueNotes4" rows="4" placeholder="Add notes..."></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary">Save Changes</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="editIssueModal5" tabindex="-1" aria-labelledby="editIssueModalLabel5" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editIssueModalLabel5">Edit Issue - Technical</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form>
          <div class="mb-3">
            <label for="issueStatus5" class="form-label">Status</label>
            <select class="form-select" id="issueStatus5">
              <option value="Pending">Pending</option>
              <option value="In Progress" selected>In Progress</option>
              <option value="Resolved">Resolved</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="issueNotes5" class="form-label">Admin Notes</label>
            <textarea class="form-control" id="issueNotes5" rows="4" placeholder="Add notes..."></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary">Save Changes</button>
      </div>
    </div>
  </div>
</div>

<?php include_once '../includes/admin/footer.php'; ?>

<!-- JavaScript for Filtering and Search -->
<script>
  // Search functionality
  document.getElementById('issueSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#issueTableBody tr');
    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
  });

  // Filter by status
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

  // Export button (placeholder)
  document.getElementById('exportBtn').addEventListener('click', function() {
    alert('Export functionality will be implemented here.');
  });
</script>

<!-- now lets make it work we will be putting the backend files in the ../backend/admin/

no code

tell me the backend files we need and why? -->