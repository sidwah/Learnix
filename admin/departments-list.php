<?php
// Authentication check
require_once '../backend/auth/admin/admin-auth-check.php';

// Set page title
$pageTitle = "Departments List - Admin | Learnix";

include_once '../includes/admin/header.php';

// <!-- Menu -->
include_once '../includes/admin/sidebar.php';
// <!-- / Menu -->

// <!-- Navbar -->
include_once '../includes/admin/navbar.php';

// Get departments data from database
require_once '../backend/config.php';

// Fetch departments with their heads
$query = "SELECT * FROM vw_departments_with_heads ORDER BY name";
$result = mysqli_query($conn, $query);

// Count statistics
$totalDepartments = 0;
$activeDepartments = 0;
$withHead = 0;
$withoutHead = 0;

$departments = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $departments[] = $row;
        $totalDepartments++;
        if ($row['is_active'] == 1) $activeDepartments++;
        if ($row['head_status'] == 'with-head') $withHead++; else $withoutHead++;
    }
}
?>

<!-- / Navbar -->

<!-- Toast container -->
<div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1090;"></div>


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
    <span class="text-muted fw-light">Admin /</span> Departments
  </h4>
  
  <!-- Cards -->
  <div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="card-info">
              <p class="card-text">Total Departments</p>
              <div class="d-flex align-items-end mb-2">
                <h4 class="card-title mb-0 me-2"><?php echo $totalDepartments; ?></h4>
              </div>
            </div>
            <div class="card-icon">
              <span class="badge bg-label-primary rounded p-2">
                <i class="bx bx-building bx-md"></i>
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
              <p class="card-text">Active Departments</p>
              <div class="d-flex align-items-end mb-2">
                <h4 class="card-title mb-0 me-2"><?php echo $activeDepartments; ?></h4>
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
              <p class="card-text">With Department Head</p>
              <div class="d-flex align-items-end mb-2">
                <h4 class="card-title mb-0 me-2"><?php echo $withHead; ?></h4>
              </div>
            </div>
            <div class="card-icon">
              <span class="badge bg-label-info rounded p-2">
                <i class="bx bx-user-check bx-md"></i>
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
              <p class="card-text">Without Department Head</p>
              <div class="d-flex align-items-end mb-2">
                <h4 class="card-title mb-0 me-2"><?php echo $withoutHead; ?></h4>
              </div>
            </div>
            <div class="card-icon">
              <span class="badge bg-label-warning rounded p-2">
                <i class="bx bx-user-x bx-md"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Departments List -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Departments List</h5>
      <div class="d-flex align-items-center">
        <?php if ($totalDepartments > 0): ?>
        <div class="me-3">
          <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="bx bx-search"></i></span>
            <input type="text" class="form-control" id="departmentSearch" placeholder="Search departments..." aria-label="Search">
          </div>
        </div>
        <div class="me-3">
          <div class="btn-group" role="group" aria-label="Filter departments">
            <button type="button" class="btn btn-outline-secondary btn-sm filter-badge active" data-filter="all">
              All
            </button>
            <button type="button" class="btn btn-outline-success btn-sm filter-badge" data-filter="active">
              Active
            </button>
            <button type="button" class="btn btn-outline-warning btn-sm filter-badge" data-filter="inactive">
              Inactive
            </button>
            <button type="button" class="btn btn-outline-info btn-sm filter-badge" data-filter="with-head">
              With Head
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm filter-badge" data-filter="without-head">
              Without Head
            </button>
          </div>
        </div>
        <?php endif; ?>
        <button type="button" class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#createDepartmentModal">
          <i class="bx bx-plus me-1"></i> Create Department
        </button>
        <?php if ($totalDepartments > 0): ?>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="exportBtn">
          <i class="bx bx-export me-1"></i> Export
        </button>
        <?php endif; ?>
      </div>
    </div>
    
    <?php if (count($departments) > 0): ?>
    <div class="table-responsive text-nowrap">
      <table class="table" id="departmentsTable">
        <thead>
          <tr>
            <th>Department Name</th>
            <th>Code</th>
            <th class="text-center">Department Head</th>
            <th>Status</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          <?php foreach ($departments as $dept): ?>
          <tr class="department-row" 
              data-id="<?php echo $dept['department_id']; ?>"
              data-status="<?php echo $dept['is_active'] ? 'active' : 'inactive'; ?>" 
              data-head-status="<?php echo $dept['head_status']; ?>" 
              data-name="<?php echo htmlspecialchars($dept['name']); ?>" 
              data-code="<?php echo htmlspecialchars($dept['code']); ?>"
              data-description="<?php echo htmlspecialchars($dept['description'] ?? ''); ?>"
              data-created="<?php echo date('F d, Y', strtotime($dept['created_at'])); ?>"
              <?php if ($dept['head_status'] == 'with-head'): ?>
              data-head-name="<?php echo htmlspecialchars($dept['head_first_name'] . ' ' . $dept['head_last_name']); ?>"
              data-head-email="<?php echo htmlspecialchars($dept['head_email']); ?>"
              data-head-img="<?php echo !empty($dept['head_profile_pic']) ? '../uploads/department-staff/' . $dept['head_profile_pic'] : '../assets/img/avatars/1.png'; ?>"
              <?php endif; ?>
          >
            <td><strong><?php echo htmlspecialchars($dept['name']); ?></strong></td>
            <td><?php echo htmlspecialchars($dept['code']); ?></td>
            <td class="text-center">
              <?php if ($dept['head_status'] == 'with-head'): ?>
              <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center justify-content-center">
                <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" 
                    class="avatar avatar-xs pull-up" title="<?php echo htmlspecialchars($dept['head_first_name'] . ' ' . $dept['head_last_name']); ?>">
                  <img src="<?php echo !empty($dept['head_profile_pic']) ? '../uploads/department-staff/' . $dept['head_profile_pic'] : '../assets/img/avatars/1.png'; ?>" alt="Avatar" class="rounded-circle" />
                </li>
              </ul>
              <?php else: ?>
              <button type="button" class="btn btn-sm btn-outline-primary assign-head-btn" data-bs-toggle="modal" data-bs-target="#assignHeadModal" data-department-id="<?php echo $dept['department_id']; ?>" data-department-name="<?php echo htmlspecialchars($dept['name']); ?>">
                <i class="bx bx-user-plus me-1"></i> Assign Head
              </button>
              <?php endif; ?>
            </td>
            <td><span class="badge bg-label-<?php echo $dept['is_active'] ? 'primary' : 'warning'; ?> me-1"><?php echo $dept['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
            <td>
              <div class="dropdown d-inline-block">
                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bx bx-dots-vertical-rounded"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                  <a class="dropdown-item text-info view-dept" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#viewDepartmentModal">
                    <i class="bx bx-show-alt me-1"></i> View
                  </a>
                  <a class="dropdown-item text-primary edit-dept" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editDepartmentModal">
                    <i class="bx bx-edit-alt me-1"></i> Edit
                  </a>
                  <a class="dropdown-item text-warning archive-dept" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#archiveDepartmentModal">
                    <i class="bx bx-archive me-1"></i> Archive
                  </a>
                  <a class="dropdown-item text-danger delete-dept" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#deleteDepartmentModal">
                    <i class="bx bx-trash me-1"></i> Delete
                  </a>
                </div>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <!-- Empty search results state -->
<div id="empty-search-results" class="text-center py-5 d-none">
  <div class="empty-state">
    <div class="empty-state-icon mb-4">
      <i class="bx bx-search" style="font-size: 4rem; color: #dfe3e7;"></i>
    </div>
    <h5 class="mb-2">No Departments Found</h5>
    <p class="mb-0 text-muted">No departments match your current filters.</p>
    <button type="button" class="btn btn-sm btn-outline-primary mt-3" id="clearFiltersBtn">
      <i class="bx bx-reset me-1"></i> Clear Filters
    </button>
  </div>
</div>
    </div>
    
    <!-- Pagination -->
    <div class="card-footer">
      <div class="row">
        <div class="col-sm-12 col-md-5">
          <div class="dataTables_info" id="pagination-info" role="status" aria-live="polite">
            Showing <span id="showing-start">1</span> to <span id="showing-end"><?php echo count($departments); ?></span> of <span id="total-entries"><?php echo $totalDepartments; ?></span> entries
          </div>
        </div>
        <div class="col-sm-12 col-md-7">
          <div class="dataTables_paginate paging_simple_numbers" id="pagination-container">
            <ul class="pagination pagination-sm justify-content-end mb-0">
              <li class="paginate_button page-item previous disabled" id="pagination-previous">
                <a href="#" class="page-link">Previous</a>
              </li>
              <li class="paginate_button page-item active">
                <a href="#" class="page-link">1</a>
              </li>
              <?php if ($totalDepartments > 10): ?>
              <li class="paginate_button page-item">
                <a href="#" class="page-link">2</a>
              </li>
              <?php endif; ?>
              <?php if ($totalDepartments > 20): ?>
              <li class="paginate_button page-item">
                <a href="#" class="page-link">3</a>
              </li>
              <?php endif; ?>
              <li class="paginate_button page-item next<?php echo ($totalDepartments <= 10) ? ' disabled' : ''; ?>" id="pagination-next">
                <a href="#" class="page-link">Next</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    
    <?php else: ?>
    <!-- Empty state -->
    <div class="card-body text-center py-5">
      <div class="empty-state">
        <div class="empty-state-icon mb-4">
          <i class="bx bx-buildings" style="font-size: 6rem; color: #dfe3e7;"></i>
        </div>
        <h4 class="mb-2">No Departments Found</h4>
        <p class="mb-4 text-muted">You haven't created any departments yet. <br>Departments help organize courses and assign department heads.</p>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createDepartmentModal">
          <i class="bx bx-plus me-1"></i> Create Your First Department
        </button>
      </div>
    </div>
    <?php endif; ?>
  </div>
  
  <!-- Create Department Modal -->
  <div class="modal fade" id="createDepartmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Create Department</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="createDepartmentForm" action="../backend/admin/create-department.php" method="POST">
          <div class="modal-body">
            <div class="row">
              <div class="col mb-3">
                <label for="departmentName" class="form-label">Department Name</label>
                <input type="text" id="departmentName" name="name" class="form-control" placeholder="Enter Department Name" required />
              </div>
            </div>
            <div class="row">
              <div class="col mb-3">
                <label for="departmentCode" class="form-label">Department Code</label>
                <input type="text" id="departmentCode" name="code" class="form-control" placeholder="e.g., CS, ENG" required />
              </div>
            </div>
            <div class="row">
              <div class="col mb-3">
                <label for="departmentDescription" class="form-label">Description</label>
                <textarea id="departmentDescription" name="description" class="form-control" placeholder="Enter department description"></textarea>
              </div>
            </div>
            <div class="row">
              <div class="col mb-3">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="isActive" name="is_active" checked />
                  <label class="form-check-label" for="isActive">Active</label>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Create Department</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  
  <!-- View Department Modal -->
  <div class="modal fade" id="viewDepartmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Department Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-12 mb-3">
              <h6 class="text-muted">Department Information</h6>
              <hr/>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <p class="mb-1 text-muted small">Department Name</p>
              <p class="mb-0 fw-bold" id="view-department-name"></p>
            </div>
            <div class="col-md-6 mb-3">
              <p class="mb-1 text-muted small">Department Code</p>
              <p class="mb-0 fw-bold" id="view-department-code"></p>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <p class="mb-1 text-muted small">Status</p>
              <p class="mb-0" id="view-department-status"></p>
            </div>
            <div class="col-md-6 mb-3">
              <p class="mb-1 text-muted small">Created On</p>
              <p class="mb-0" id="view-department-created"></p>
            </div>
          </div>
          <div class="row">
            <div class="col-12 mb-3">
              <p class="mb-1 text-muted small">Description</p>
              <p class="mb-0" id="view-department-description"></p>
            </div>
          </div>
          
          <!-- Department Head section - will be dynamically shown/hidden -->
          <div class="row" id="view-head-section">
            <div class="col-12 mb-3">
              <h6 class="text-muted">Department Head</h6>
              <hr/>
            </div>
            <div class="col-md-12 mb-3">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                  <img src="../assets/img/avatars/1.png" alt="Avatar" id="view-head-img" class="rounded-circle" width="42" height="42" />
                </div>
                <div class="flex-grow-1 ms-3">
                  <h6 class="mb-0" id="view-head-name"></h6>
                  <small class="text-muted" id="view-head-email"></small>
                </div>
              </div>
            </div>
          </div>
          
          <!-- No Head section - will be dynamically shown/hidden -->
          <div class="row d-none" id="view-no-head-section">
            <div class="col-12 mb-3">
              <h6 class="text-muted">Department Head</h6>
              <hr/>
            </div>
            <div class="col-md-12 mb-3">
              <div class="alert alert-info mb-0">
                <div class="d-flex">
                  <i class="bx bx-info-circle text-info me-2 mt-1"></i>
                  <div>
                    <p class="mb-0">No department head assigned.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Edit Department Modal -->
  <div class="modal fade" id="editDepartmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Department</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="editDepartmentForm" action="../backend/admin/update-department.php" method="POST">
          <div class="modal-body">
            <input type="hidden" id="editDepartmentId" name="department_id" value="">
            <div class="row">
              <div class="col mb-3">
                <label for="editDepartmentName" class="form-label">Department Name</label>
                <input type="text" id="editDepartmentName" name="name" class="form-control" placeholder="Enter Department Name" required />
              </div>
            </div>
            <div class="row">
              <div class="col mb-3">
                <label for="editDepartmentCode" class="form-label">Department Code</label>
                <input type="text" id="editDepartmentCode" name="code" class="form-control" placeholder="e.g., CS, ENG" required />
              </div>
            </div>
            <div class="row">
              <div class="col mb-3">
                <label for="editDepartmentDescription" class="form-label">Description</label>
                <textarea id="editDepartmentDescription" name="description" class="form-control" placeholder="Enter department description"></textarea>
              </div>
            </div>
            <div class="row">
              <div class="col mb-3">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="editIsActive" name="is_active" />
                  <label class="form-check-label" for="editIsActive">Active</label>
                </div>
              </div>
            </div>
            
            <!-- Department Head Information -->
            <div class="row mt-3" id="edit-head-section">
              <div class="col-12 mb-3">
                <h6 class="text-muted">Department Head</h6>
                <hr/>
              </div>
              
              <div class="row" id="current-head-section">
                <div class="col-md-12 mb-3">
                  <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                      <div class="flex-shrink-0">
                        <img src="../assets/img/avatars/1.png" alt="Avatar" id="edit-head-img" class="rounded-circle" width="42" height="42" />
                      </div>
                      <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0" id="edit-head-name"></h6>
                        <small class="text-muted" id="edit-head-email"></small>
                      </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="removeHeadBtn">
                      <i class="bx bx-x"></i> Remove
                    </button>
                  </div>
                </div>
                <input type="hidden" name="remove_head" id="removeHeadInput" value="0">
              </div>
              
              <div class="row d-none" id="no-head-section">
                <div class="col-md-12 mb-3">
                  <div class="alert alert-info mb-0">
                    <div class="d-flex">
                      <i class="bx bx-info-circle text-info me-2 mt-1"></i>
                      <div>
                        <p class="mb-0">No department head assigned. You can assign a head after saving these changes.</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  
  <!-- Assign Department Head Modal -->
  <div class="modal fade" id="assignHeadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Assign Department Head</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="assignHeadForm" action="../backend/admin/assign-department-head.php" method="POST">
          <div class="modal-body">
            <input type="hidden" id="assignDepartmentId" name="department_id" value="">
            <div class="mb-3">
              <div class="alert alert-info">
                <div class="d-flex">
                  <i class="bx bx-info-circle text-info me-2 mt-1"></i>
                  <div>
                    <p class="mb-0">Assigning a head to <strong id="assignDepartmentName"></strong>. The person will receive an email invitation.</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col mb-3">
                <label for="headEmail" class="form-label">Email Address</label>
                <input type="email" id="headEmail" name="email" class="form-control" placeholder="Enter email address" required />
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="headFirstName" class="form-label">First Name</label>
                <input type="text" id="headFirstName" name="first_name" class="form-control" placeholder="First name" required />
              </div>
              <div class="col-md-6 mb-3">
                <label for="headLastName" class="form-label">Last Name</label>
                <input type="text" id="headLastName" name="last_name" class="form-control" placeholder="Last name" required />
              </div>
            </div>
            <div class="row">
              <div class="col mb-3">
                <label for="headNotes" class="form-label">Additional Notes</label>
                <textarea id="headNotes" name="notes" class="form-control" placeholder="Optional notes"></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Send Invitation</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  
  <!-- Archive Department Modal -->
  <div class="modal fade" id="archiveDepartmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Archive Department</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="text-center mb-4">
            <i class="bx bx-archive text-warning" style="font-size: 6rem;"></i>
          </div>
          <p class="mb-0">Are you sure you want to archive the <strong id="archiveDepartmentName"></strong> department?</p>
          <p class="mb-0">Archiving a department will:</p>
          <ul class="mt-2">
            <li>Make it inactive and unavailable for new courses</li>
            <li>Preserve all historical data</li>
            <li>Allow it to be restored in the future if needed</li>
          </ul>
          <p class="mb-0">This action can be reversed later.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <form id="archiveDepartmentForm" action="../backend/admin/archive-department.php" method="POST">
            <input type="hidden" id="archiveDepartmentId" name="department_id" value="">
            <button type="submit" class="btn btn-warning">Archive Department</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Delete Department Modal -->
  <div class="modal fade" id="deleteDepartmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Delete Department</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="text-center mb-4">
            <i class="bx bx-error-circle text-danger" style="font-size: 6rem;"></i>
          </div>
          <p class="mb-0 text-center">Are you sure you want to permanently delete the <strong id="deleteDepartmentName"></strong> department?</p>
          <p class="mt-3 mb-0"><strong>Warning:</strong> This action cannot be undone. Deleting this department will:</p>
          <ul class="mt-2 text-danger">
            <li>Permanently remove all department data</li>
            <li>Remove department head assignments</li>
            <li>Affect associated courses and content</li>
            <li>Impact reporting and analytics</li>
          </ul>
          <p class="mb-0">Consider <strong>archiving</strong> instead if you may need this department in the future.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <form id="deleteDepartmentForm" action="../backend/admin/delete-department.php" method="POST">
            <input type="hidden" id="deleteDepartmentId" name="department_id" value="">
            <button type="submit" class="btn btn-danger">Delete Permanently</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- JavaScript for handling everything -->
<script>

// Pagination functionality
const ITEMS_PER_PAGE = 10; // Max number of items per page
let currentPage = 1;

function setupPagination() {
    // Get all visible rows
    const visibleRows = Array.from(document.querySelectorAll('.department-row'))
        .filter(row => row.style.display !== 'none');
    
    const totalItems = visibleRows.length;
    const totalPages = Math.ceil(totalItems / ITEMS_PER_PAGE);
    
    // Update pagination info
    document.getElementById('showing-start').textContent = 
        totalItems > 0 ? ((currentPage - 1) * ITEMS_PER_PAGE + 1) : 0;
    document.getElementById('showing-end').textContent = 
        Math.min(currentPage * ITEMS_PER_PAGE, totalItems);
    document.getElementById('total-entries').textContent = totalItems;
    
    // Hide all rows first
    visibleRows.forEach(row => {
        row.classList.add('d-none');
    });
    
    // Show only rows for current page
    const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
    const endIndex = Math.min(startIndex + ITEMS_PER_PAGE, totalItems);
    
    for (let i = startIndex; i < endIndex; i++) {
        visibleRows[i].classList.remove('d-none');
    }
    
    // Update pagination UI
    updatePaginationUI(totalPages);
}

function updatePaginationUI(totalPages) {
    const paginationContainer = document.querySelector('#pagination-container ul');
    
    // Clear existing page numbers (except Previous and Next)
    const pageItems = document.querySelectorAll('#pagination-container ul li:not(.previous):not(.next)');
    pageItems.forEach(item => item.remove());
    
    // Previous button state
    const prevButton = document.getElementById('pagination-previous');
    if (currentPage === 1) {
        prevButton.classList.add('disabled');
    } else {
        prevButton.classList.remove('disabled');
    }
    
    // Next button state
    const nextButton = document.getElementById('pagination-next');
    if (currentPage === totalPages || totalPages === 0) {
        nextButton.classList.add('disabled');
    } else {
        nextButton.classList.remove('disabled');
    }
    
    // Insert page numbers
    const maxVisiblePages = 5; // Maximum number of page buttons to show
    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
    
    // Adjust start if we're near the end
    if (endPage - startPage + 1 < maxVisiblePages && startPage > 1) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }
    
    // Insert page numbers
    for (let i = startPage; i <= endPage; i++) {
        const li = document.createElement('li');
        li.className = `paginate_button page-item ${i === currentPage ? 'active' : ''}`;
        li.innerHTML = `<a href="#" class="page-link">${i}</a>`;
        
        // Add click handler
        li.addEventListener('click', function(e) {
            e.preventDefault();
            if (i !== currentPage) {
                currentPage = i;
                setupPagination();
            }
        });
        
        // Insert before the Next button
        paginationContainer.insertBefore(li, nextButton);
    }
    
    // Add ellipsis if needed
    if (startPage > 1) {
        const ellipsisStart = document.createElement('li');
        ellipsisStart.className = 'paginate_button page-item disabled';
        ellipsisStart.innerHTML = '<a href="#" class="page-link">...</a>';
        paginationContainer.insertBefore(ellipsisStart, paginationContainer.querySelector(`li:nth-child(${2})`));
    }
    
    if (endPage < totalPages) {
        const ellipsisEnd = document.createElement('li');
        ellipsisEnd.className = 'paginate_button page-item disabled';
        ellipsisEnd.innerHTML = '<a href="#" class="page-link">...</a>';
        paginationContainer.insertBefore(ellipsisEnd, nextButton);
    }
}

// Add event listeners for previous and next buttons
document.getElementById('pagination-previous').addEventListener('click', function(e) {
    e.preventDefault();
    if (currentPage > 1) {
        currentPage--;
        setupPagination();
    }
});

document.getElementById('pagination-next').addEventListener('click', function(e) {
    e.preventDefault();
    const visibleRows = document.querySelectorAll('.department-row:not([style*="display: none"])');
    const totalPages = Math.ceil(visibleRows.length / ITEMS_PER_PAGE);
    
    if (currentPage < totalPages) {
        currentPage++;
        setupPagination();
    }
});

// Modify filterDepartments to call setupPagination after filtering
function filterDepartments() {
    const searchInput = document.getElementById('departmentSearch');
    if (!searchInput) return;
    
    const searchTerm = searchInput.value.toLowerCase();
    const activeFilter = document.querySelector('.filter-badge.active').getAttribute('data-filter');
    const rows = document.querySelectorAll('.department-row');
    const emptySearchResults = document.getElementById('empty-search-results');
    
    let visibleCount = 0;
    
    rows.forEach(row => {
        const name = row.getAttribute('data-name').toLowerCase();
        const code = row.getAttribute('data-code').toLowerCase();
        const status = row.getAttribute('data-status');
        const headStatus = row.getAttribute('data-head-status');
        
        // Check if row matches search term
        const matchesSearch = name.includes(searchTerm) || code.includes(searchTerm);
        
        // Check if row matches active filter
        let matchesFilter = false;
        switch (activeFilter) {
            case 'all':
                matchesFilter = true;
                break;
            case 'active':
                matchesFilter = (status === 'active');
                break;
            case 'inactive':
                matchesFilter = (status === 'inactive');
                break;
            case 'with-head':
                matchesFilter = (headStatus === 'with-head');
                break;
            case 'without-head':
                matchesFilter = (headStatus === 'without-head');
                break;
        }

        
        // Show/hide row based on filters
        if (matchesSearch && matchesFilter) {
            row.style.display = ''; // Make row visible to filter
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Show/hide empty state for filtered results
    if (visibleCount === 0 && rows.length > 0) {
        // We have departments but none match the filter
        if (emptySearchResults) {
            emptySearchResults.classList.remove('d-none');
        }
    } else {
        if (emptySearchResults) {
            emptySearchResults.classList.add('d-none');
        }
        
        // Reset to page 1 when filtering
        currentPage = 1;
        
        // Setup pagination with filtered results
        setupPagination();
    }
}
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Toast notification function
    function showToast(message, type = 'success') {
        const toastContainer = document.getElementById('toast-container');
        
        // Create toast element
        const toastId = 'toast-' + Date.now();
        const toastEl = document.createElement('div');
        toastEl.id = toastId;
        toastEl.className = `toast align-items-center text-white bg-${type} border-0`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        
        // Toast content
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        
        // Add toast to container
        document.getElementById('toast-container').appendChild(toastEl);
        
        // Initialize and show toast
        const toast = new bootstrap.Toast(toastEl, {
            autohide: true,
            delay: 5000
        });
        toast.show();
        
        // Remove toast element after it's hidden
        toastEl.addEventListener('hidden.bs.toast', function() {
            toastEl.remove();
        });
    }
    
    // Show loading overlay with minimum duration
    function showOverlay(message = 'Processing...') {
    const overlay = document.getElementById('loadingOverlay');
    const messageEl = document.getElementById('loading-message');
    
    if (messageEl) {
        messageEl.textContent = message;
    }
    
    overlay.style.display = 'flex';
    
    // Store the start time
    overlay.dataset.startTime = Date.now();
}
    
    // Remove loading overlay, ensuring minimum duration of 1 second
    function removeOverlay() {
        const overlay = document.getElementById('loadingOverlay');
        const startTime = parseInt(overlay.dataset.startTime || 0);
        const currentTime = Date.now();
        const elapsedTime = currentTime - startTime;
        
        if (elapsedTime >= 1000) {
            // If already displayed for at least 1 second, remove immediately
            overlay.style.display = 'none';
        } else {
            // Otherwise, wait for the remaining time
            setTimeout(() => {
                overlay.style.display = 'none';
            }, 1000 - elapsedTime);
        }
    }
    
    // AJAX form submission handler
    function handleFormSubmit(formId, url, successCallback = null) {
        const form = document.getElementById(formId);
        if (!form) return;
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading overlay
            showOverlay('Processing...');
            
            // Submit form using fetch API
            fetch(url, {
                method: 'POST',
                body: new FormData(form)
            })
            .then(response => response.json())
            .then(data => {
                // Remove overlay
                removeOverlay();
                
                if (data.status === 'success') {
                    // Show success toast
                    showToast(data.message, 'success');
                    
                    // Close modal if applicable
                    const modal = bootstrap.Modal.getInstance(form.closest('.modal'));
                    if (modal) {
                        modal.hide();
                    }
                    
                    // Run success callback if provided
                    if (successCallback && typeof successCallback === 'function') {
                        successCallback(data);
                    } else {
                        // Reload page after a delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }
                } else {
                    // Show error toast
                    showToast(data.message, 'danger');
                }
            })
            .catch(error => {
                // Remove overlay
                removeOverlay();
                console.error('Error:', error);
                showToast('An unexpected error occurred. Please try again.', 'danger');
            });
        });
    }
    
    // Initialize form handlers
    handleFormSubmit('createDepartmentForm', '../backend/admin/create-department.php');
    handleFormSubmit('editDepartmentForm', '../backend/admin/update-department.php');
    handleFormSubmit('assignHeadForm', '../backend/admin/assign-department-head.php');
    handleFormSubmit('archiveDepartmentForm', '../backend/admin/archive-department.php');
    handleFormSubmit('deleteDepartmentForm', '../backend/admin/delete-department.php');
    
    // View department details
    document.querySelectorAll('.view-dept').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            
            document.getElementById('view-department-name').textContent = row.getAttribute('data-name');
            document.getElementById('view-department-code').textContent = row.getAttribute('data-code');
            document.getElementById('view-department-created').textContent = row.getAttribute('data-created');
            document.getElementById('view-department-description').textContent = row.getAttribute('data-description') || 'No description provided.';
            
            // Set status badge
            const status = row.getAttribute('data-status');
            document.getElementById('view-department-status').innerHTML = 
                `<span class="badge bg-label-${status === 'active' ? 'primary' : 'warning'}">${status === 'active' ? 'Active' : 'Inactive'}</span>`;
            
            // Handle department head section
            const headStatus = row.getAttribute('data-head-status');
            if (headStatus === 'with-head') {
                document.getElementById('view-head-section').classList.remove('d-none');
                document.getElementById('view-no-head-section').classList.add('d-none');
                document.getElementById('view-head-name').textContent = row.getAttribute('data-head-name');
                document.getElementById('view-head-email').textContent = row.getAttribute('data-head-email');
                document.getElementById('view-head-img').src = row.getAttribute('data-head-img');
            } else {
                document.getElementById('view-head-section').classList.add('d-none');
                document.getElementById('view-no-head-section').classList.remove('d-none');
            }
        });
    });
    
    // Edit department setup
    document.querySelectorAll('.edit-dept').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            
            document.getElementById('editDepartmentId').value = row.getAttribute('data-id');
            document.getElementById('editDepartmentName').value = row.getAttribute('data-name');
            document.getElementById('editDepartmentCode').value = row.getAttribute('data-code');
            document.getElementById('editDepartmentDescription').value = row.getAttribute('data-description') || '';
            document.getElementById('editIsActive').checked = row.getAttribute('data-status') === 'active';
            
            // Reset remove head flag
            document.getElementById('removeHeadInput').value = '0';
            
            // Handle department head section
            const headStatus = row.getAttribute('data-head-status');
            if (headStatus === 'with-head') {
                document.getElementById('current-head-section').classList.remove('d-none');
                document.getElementById('no-head-section').classList.add('d-none');
                document.getElementById('edit-head-name').textContent = row.getAttribute('data-head-name');
                document.getElementById('edit-head-email').textContent = row.getAttribute('data-head-email');
                document.getElementById('edit-head-img').src = row.getAttribute('data-head-img');
            } else {
                document.getElementById('current-head-section').classList.add('d-none');
                document.getElementById('no-head-section').classList.remove('d-none');
            }
        });
    });
    
    // Setup archive modal
    document.querySelectorAll('.archive-dept').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            document.getElementById('archiveDepartmentId').value = row.getAttribute('data-id');
            document.getElementById('archiveDepartmentName').textContent = row.getAttribute('data-name');
        });
    });
    
    // Setup delete modal
    document.querySelectorAll('.delete-dept').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            document.getElementById('deleteDepartmentId').value = row.getAttribute('data-id');
            document.getElementById('deleteDepartmentName').textContent = row.getAttribute('data-name');
        });
    });
    
    // Setup assign head modal
    document.querySelectorAll('.assign-head-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const departmentId = this.getAttribute('data-department-id');
            const departmentName = this.getAttribute('data-department-name');
            document.getElementById('assignDepartmentId').value = departmentId;
            document.getElementById('assignDepartmentName').textContent = departmentName;
        });
    });
    
    // Search functionality
    const searchInput = document.getElementById('departmentSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            filterDepartments();
        });
    }
    
    // Filter buttons functionality
    const filterButtons = document.querySelectorAll('.filter-badge');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Apply filter
            filterDepartments();
        });
    });

    // Clear filters button
const clearFiltersBtn = document.getElementById('clearFiltersBtn');
if (clearFiltersBtn) {
    clearFiltersBtn.addEventListener('click', function() {
        // Reset search input
        const searchInput = document.getElementById('departmentSearch');
        if (searchInput) {
            searchInput.value = '';
        }
        
        // Reset filter buttons to "All"
        const allFilterBtn = document.querySelector('.filter-badge[data-filter="all"]');
        if (allFilterBtn) {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            allFilterBtn.classList.add('active');
        }
        
        // Apply filters
        filterDepartments();
    });
}
    
    // Function to filter departments based on search and filter buttons
// Function to filter departments based on search and filter buttons
function filterDepartments() {
    const searchInput = document.getElementById('departmentSearch');
    if (!searchInput) return;
    
    const searchTerm = searchInput.value.toLowerCase();
    const activeFilter = document.querySelector('.filter-badge.active').getAttribute('data-filter');
    const rows = document.querySelectorAll('.department-row');
    const emptySearchResults = document.getElementById('empty-search-results');
    
    let visibleCount = 0;
    
    rows.forEach(row => {
        const name = row.getAttribute('data-name').toLowerCase();
        const code = row.getAttribute('data-code').toLowerCase();
        const status = row.getAttribute('data-status');
        const headStatus = row.getAttribute('data-head-status');
        
        // Check if row matches search term
        const matchesSearch = name.includes(searchTerm) || code.includes(searchTerm);
        
        // Check if row matches active filter
        let matchesFilter = false;
        switch (activeFilter) {
            case 'all':
                matchesFilter = true;
                break;
            case 'active':
                matchesFilter = (status === 'active');
                break;
            case 'inactive':
                matchesFilter = (status === 'inactive');
                break;
            case 'with-head':
                matchesFilter = (headStatus === 'with-head');
                break;
            case 'without-head':
                matchesFilter = (headStatus === 'without-head');
                break;
        }
        
        // Show/hide row based on filters
        if (matchesSearch && matchesFilter) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update pagination info
    const showingStart = document.getElementById('showing-start');
    const showingEnd = document.getElementById('showing-end');
    
    if (showingStart && showingEnd) {
        showingStart.textContent = visibleCount > 0 ? '1' : '0';
        showingEnd.textContent = visibleCount;
    }
    
    // Show/hide empty state for filtered results
    if (visibleCount === 0 && rows.length > 0) {
        // We have departments but none match the filter
        if (emptySearchResults) {
            emptySearchResults.classList.remove('d-none');
        }
    } else {
        if (emptySearchResults) {
            emptySearchResults.classList.add('d-none');
        }
    }
}

    // Remove Department Head functionality
    const removeHeadBtn = document.getElementById('removeHeadBtn');
    if (removeHeadBtn) {
        removeHeadBtn.addEventListener('click', function() {
            document.getElementById('current-head-section').classList.add('d-none');
            document.getElementById('no-head-section').classList.remove('d-none');
            document.getElementById('removeHeadInput').value = '1';
        });
    }
    
    // Export functionality
    const exportBtn = document.getElementById('exportBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            // Show loading overlay
            showOverlay('Generating export...');
            
            // Get table data
            const table = document.getElementById('departmentsTable');
            const rows = table.querySelectorAll('tbody tr:not([style*="display: none"])');
            
            let csvContent = "data:text/csv;charset=utf-8,";
            
            // Add headers
            csvContent += "Department Name,Code,Has Department Head,Status\n";
            
            // Add rows
            rows.forEach(row => {
                const name = row.getAttribute('data-name').replace(/"/g, '""');
                const code = row.getAttribute('data-code').replace(/"/g, '""');
                const hasHead = row.getAttribute('data-head-status') === 'with-head' ? 'Yes' : 'No';
                const status = row.getAttribute('data-status');
                
                csvContent += `"${name}","${code}","${hasHead}","${status.charAt(0).toUpperCase() + status.slice(1)}"\n`;
            });
            
            // Create download link
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "departments_export_" + new Date().toISOString().split('T')[0] + ".csv");
            document.body.appendChild(link);
            
            // Delay for animation
            setTimeout(() => {
                // Trigger download
                link.click();
                
                // Clean up
                document.body.removeChild(link);
                removeOverlay();
                
                // Show toast
                showToast('Departments exported successfully', 'success');
            }, 1000);
        });
    }
    setupPagination();
});
</script>
<!-- / Content -->

<?php include_once '../includes/admin/footer.php'; ?>