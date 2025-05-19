<?php
// Authentication check
require_once '../backend/auth/admin/admin-auth-check.php';

// Set page title
$pageTitle = "Instructors - Admin | Learnix";

include_once '../includes/admin/header.php';

// <!-- Menu -->
include_once '../includes/admin/sidebar.php';
// <!-- / Menu -->

// <!-- Navbar -->
include_once '../includes/admin/navbar.php';

// Get data from database
require_once '../backend/config.php';

// Fetch all instructors with their details
$query = "SELECT i.instructor_id, i.bio, i.created_at, i.updated_at,
                 u.user_id, u.first_name, u.last_name, u.email, u.profile_pic, u.status,
                 d.department_id, d.name as department_name
          FROM instructors i
          JOIN users u ON i.user_id = u.user_id
          LEFT JOIN department_instructors di ON i.instructor_id = di.instructor_id AND di.status = 'active' AND di.deleted_at IS NULL
          LEFT JOIN departments d ON di.department_id = d.department_id
          WHERE i.deleted_at IS NULL
          ORDER BY u.first_name, u.last_name";

$result = mysqli_query($conn, $query);

// Fetch departments for the modal
$departmentsQuery = "SELECT department_id, name FROM departments WHERE deleted_at IS NULL ORDER BY name";
$departmentsResult = mysqli_query($conn, $departmentsQuery);
$departments = [];
if ($departmentsResult && mysqli_num_rows($departmentsResult) > 0) {
  while ($row = mysqli_fetch_assoc($departmentsResult)) {
    $departments[] = $row;
  }
}

// Count statistics
$totalInstructors = 0;
$activeCounts = 0;
$pendingCounts = 0;
$inactiveCounts = 0;
$suspendedCounts = 0;

$instructors = [];
if ($result && mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
    $instructors[] = $row;
    $totalInstructors++;

    // Count by status
    if ($row['status'] == 'active') $activeCounts++;
    if ($row['status'] == 'pending') $pendingCounts++;
    if ($row['status'] == 'inactive') $inactiveCounts++;
    if ($row['status'] == 'suspended') $suspendedCounts++;
  }
}

// Get courses for each instructor
foreach ($instructors as $key => $instructor) {
  $coursesQuery = "SELECT c.course_id, c.title, c.thumbnail 
                     FROM courses c
                     JOIN course_instructors ci ON c.course_id = ci.course_id
                     WHERE ci.instructor_id = ? AND ci.deleted_at IS NULL
                     AND c.deleted_at IS NULL
                     LIMIT 5";

  $stmt = $conn->prepare($coursesQuery);
  $stmt->bind_param("i", $instructor['instructor_id']);
  $stmt->execute();
  $coursesResult = $stmt->get_result();

  $courses = [];
  if ($coursesResult && mysqli_num_rows($coursesResult) > 0) {
    while ($course = mysqli_fetch_assoc($coursesResult)) {
      $courses[] = $course;
    }
  }

  $instructors[$key]['courses'] = $courses;
  $instructors[$key]['course_count'] = count($courses);
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
<div class="custom-overlay" id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9997; align-items: center; justify-content: center; flex-direction: column;">
  <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
    <span class="visually-hidden">Loading...</span>
  </div>
  <div class="text-white mt-3" id="loading-message">Processing...</div>
</div>

<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Admin /</span> Instructors
  </h4>

  <!-- Cards -->
  <div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
      <div class="card status-card" data-status="active">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="card-info">
              <p class="card-text">Active Instructors</p>
              <div class="d-flex align-items-end mb-2">
                <h4 class="card-title mb-0 me-2"><?php echo $activeCounts; ?></h4>
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
      <div class="card status-card" data-status="pending">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="card-info">
              <p class="card-text">Pending Verification</p>
              <div class="d-flex align-items-end mb-2">
                <h4 class="card-title mb-0 me-2"><?php echo $pendingCounts; ?></h4>
              </div>
            </div>
            <div class="card-icon">
              <span class="badge bg-label-warning rounded p-2">
                <i class="bx bx-time bx-md"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-3 col-sm-6 mb-3">
      <div class="card status-card" data-status="inactive">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="card-info">
              <p class="card-text">Inactive Instructors</p>
              <div class="d-flex align-items-end mb-2">
                <h4 class="card-title mb-0 me-2"><?php echo $inactiveCounts; ?></h4>
              </div>
            </div>
            <div class="card-icon">
              <span class="badge bg-label-info rounded p-2">
                <i class="bx bx-pause-circle bx-md"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-3 col-sm-6 mb-3">
      <div class="card status-card" data-status="suspended">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="card-info">
              <p class="card-text">Suspended</p>
              <div class="d-flex align-items-end mb-2">
                <h4 class="card-title mb-0 me-2"><?php echo $suspendedCounts; ?></h4>
              </div>
            </div>
            <div class="card-icon">
              <span class="badge bg-label-danger rounded p-2">
                <i class="bx bx-block bx-md"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Instructors List -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Instructors List</h5>
      <div class="d-flex align-items-center">
        <?php if ($totalInstructors > 0): ?>
          <div class="me-3">
            <div class="input-group input-group-sm">
              <span class="input-group-text"><i class="bx bx-search"></i></span>
              <input type="text" class="form-control" id="instructorSearch" placeholder="Search instructors..." aria-label="Search">
            </div>
          </div>
          <div class="me-3">
            <div class="btn-group" role="group" aria-label="Filter by status">
              <button type="button" class="btn btn-outline-secondary btn-sm filter-status active" data-status="all">
                All
              </button>
              <button type="button" class="btn btn-outline-success btn-sm filter-status" data-status="active">
                Active
              </button>
              <button type="button" class="btn btn-outline-warning btn-sm filter-status" data-status="pending">
                Pending
              </button>
              <button type="button" class="btn btn-outline-info btn-sm filter-status" data-status="inactive">
                Inactive
              </button>
              <button type="button" class="btn btn-outline-danger btn-sm filter-status" data-status="suspended">
                Suspended
              </button>
            </div>
          </div>
        <?php endif; ?>
        <!-- <button type="button" class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addInstructorModal">
          <i class="bx bx-plus me-1"></i> Add Instructor
        </button> -->
        <?php if ($totalInstructors > 0): ?>
          <button type="button" class="btn btn-outline-secondary btn-sm" id="exportBtn">
            <i class="bx bx-export me-1"></i> Export
          </button>
        <?php endif; ?>
      </div>
    </div>

    <?php if (count($instructors) > 0): ?>
      <div class="table-responsive text-nowrap">
        <table class="table" id="instructorsTable">
          <thead>
            <tr>
              <th>Instructor</th>
              <th>Status</th>
              <th>Department</th>
              <th>Courses</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            <?php foreach ($instructors as $instructor): ?>
              <tr class="instructor-row"
                data-id="<?php echo $instructor['instructor_id']; ?>"
                data-user-id="<?php echo $instructor['user_id']; ?>"
                data-status="<?php echo $instructor['status']; ?>"
                data-department="<?php echo $instructor['department_id'] ?? ''; ?>"
                data-name="<?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?>"
                data-first-name="<?php echo htmlspecialchars($instructor['first_name']); ?>"
                data-last-name="<?php echo htmlspecialchars($instructor['last_name']); ?>"
                data-email="<?php echo htmlspecialchars($instructor['email']); ?>"
                data-profile-pic="<?php echo !empty($instructor['profile_pic']) ? '../uploads/instructor-profile/' . $instructor['profile_pic'] : '../assets/img/avatars/1.png'; ?>"
                data-created="<?php echo date('F d, Y', strtotime($instructor['created_at'])); ?>"
                data-department-name="<?php echo htmlspecialchars($instructor['department_name'] ?? 'Not Assigned'); ?>"
                data-bio="<?php echo htmlspecialchars($instructor['bio'] ?? ''); ?>"
                data-courses="<?php echo $instructor['course_count']; ?>">
                <td>
                  <div class="d-flex justify-content-start align-items-center">
                    <div class="avatar-wrapper">
                      <div class="avatar me-2">
                        <img src="<?php echo !empty($instructor['profile_pic']) ? '../uploads/instructor-profile/' . $instructor['profile_pic'] : '../assets/img/avatars/1.png'; ?>" alt="Instructor Avatar" class="rounded-circle">
                      </div>
                    </div>
                    <div class="d-flex flex-column">
                      <span class="fw-semibold"><?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?></span>
                      <small class="text-muted"><?php echo htmlspecialchars($instructor['email']); ?></small>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="badge bg-label-<?php
                                              echo $instructor['status'] == 'active' ? 'success' : ($instructor['status'] == 'pending' ? 'warning' : ($instructor['status'] == 'inactive' ? 'info' :
                                                'danger')); ?>">
                    <?php echo ucfirst($instructor['status']); ?>
                  </span>
                </td>
                <td><?php echo htmlspecialchars($instructor['department_name'] ?? 'Not Assigned'); ?></td>
                <td>
                  <div class="course-thumbnails">
                    <?php if (count($instructor['courses']) > 0): ?>
                      <?php
                      $displayCourses = array_slice($instructor['courses'], 0, 3);
                      foreach ($displayCourses as $course):
                      ?>
                        <img src="<?php echo !empty($course['thumbnail']) ? '../uploads/thumbnails/' . $course['thumbnail'] : '../uploads/thumbnails/default.jpg'; ?>"
                          alt="<?php echo htmlspecialchars($course['title']); ?>"
                          title="<?php echo htmlspecialchars($course['title']); ?>"
                          class="rounded me-1" style="width:40px; height:40px; object-fit:cover;">
                      <?php endforeach; ?>

                      <?php if (count($instructor['courses']) > 3): ?>
                        <span class="badge bg-light text-dark">+<?php echo (count($instructor['courses']) - 3); ?> more</span>
                      <?php endif; ?>
                    <?php else: ?>
                      <span class="text-muted small">No courses assigned</span>
                    <?php endif; ?>
                  </div>
                </td>
                <td class="text-center">
                  <div class="d-inline-block">
                    <button type="button" class="btn btn-sm btn-icon btn-outline-primary rounded-pill btn-icon view-instructor" data-bs-toggle="modal" data-bs-target="#viewInstructorModal" title="View Details">
                      <i class="bx bx-show-alt"></i>
                    </button>

                    <?php if ($instructor['status'] == 'pending'): ?>
                      <button type="button" class="btn btn-sm btn-icon btn-outline-success rounded-pill btn-icon approve-instructor" title="Approve Instructor">
                        <i class="bx bx-check"></i>
                      </button>
                    <?php endif; ?>

                    <?php if ($instructor['status'] == 'active'): ?>
                      <button type="button" class="btn btn-sm btn-icon btn-outline-warning rounded-pill btn-icon change-status" data-status="inactive" title="Deactivate">
                        <i class="bx bx-pause"></i>
                      </button>
                    <?php endif; ?>

                    <?php if ($instructor['status'] == 'inactive'): ?>
                      <button type="button" class="btn btn-sm btn-icon btn-outline-success rounded-pill btn-icon change-status" data-status="active" title="Activate">
                        <i class="bx bx-play"></i>
                      </button>
                    <?php endif; ?>

                    <?php if ($instructor['status'] == 'suspended'): ?>
                      <button type="button" class="btn btn-sm btn-icon btn-outline-success rounded-pill btn-icon change-status" data-status="active" title="Restore Access">
                        <i class="bx bx-revision"></i>
                      </button>
                    <?php endif; ?>

                    <?php if ($instructor['status'] != 'suspended'): ?>
                      <button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-pill btn-icon suspend-instructor" title="Suspend">
                        <i class="bx bx-block"></i>
                      </button>
                    <?php endif; ?>

                    <button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-pill btn-icon delete-instructor" data-bs-toggle="modal" data-bs-target="#deleteInstructorModal" title="Delete">
                      <i class="bx bx-trash"></i>
                    </button>
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
            <h5 class="mb-2">No Instructors Found</h5>
            <p class="mb-0 text-muted">No instructors match your current filters.</p>
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
              Showing <span id="showing-start">1</span> to <span id="showing-end"><?php echo count($instructors); ?></span> of <span id="total-entries"><?php echo $totalInstructors; ?></span> entries
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
                <?php if ($totalInstructors > 10): ?>
                  <li class="paginate_button page-item">
                    <a href="#" class="page-link">2</a>
                  </li>
                <?php endif; ?>
                <?php if ($totalInstructors > 20): ?>
                  <li class="paginate_button page-item">
                    <a href="#" class="page-link">3</a>
                  </li>
                <?php endif; ?>
                <li class="paginate_button page-item next<?php echo ($totalInstructors <= 10) ? ' disabled' : ''; ?>" id="pagination-next">
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
            <i class="bx bx-user-voice" style="font-size: 6rem; color: #dfe3e7;"></i>
          </div>
          <h4 class="mb-2">No Instructors Found</h4>
          <p class="mb-4 text-muted">Get started by adding an instructor to your platform.</p>
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInstructorModal">
            <i class="bx bx-plus me-1"></i> Add Your First Instructor
          </button>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- View Instructor Modal -->
  <!-- View Instructor Modal -->
  <div class="modal fade" id="viewInstructorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Instructor Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-4 text-center mb-4 mb-md-0">
              <img src="../assets/img/avatars/1.png" alt="Instructor Avatar" class="rounded-circle border" width="150" height="150" id="view-instructor-image">
              <div class="mt-3">
                <h5 id="view-instructor-name" class="mb-1"></h5>
                <p class="text-muted small" id="view-instructor-email"></p>
                <span class="badge bg-label-success" id="view-instructor-status"></span>
              </div>
            </div>
            <div class="col-md-8">
              <div class="mb-3">
                <h6 class="fw-semibold">Instructor Bio</h6>
                <p id="view-instructor-bio" class="text-muted"></p>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <h6 class="fw-semibold">Department</h6>
                  <div class="d-flex align-items-center">
                    <p id="view-instructor-department" class="text-muted mb-0 me-2"></p>
                    <button type="button" class="btn btn-sm btn-outline-primary change-department"
                      data-bs-toggle="modal" data-bs-target="#changeDepartmentModal">
                      <i class="bx bx-edit-alt"></i> Change
                    </button>
                  </div>
                </div>
                <div class="col-md-6">
                  <h6 class="fw-semibold">Joined</h6>
                  <p id="view-instructor-joined" class="text-muted"></p>
                </div>
              </div>

              <h6 class="fw-semibold mb-3">Courses</h6>
              <div id="view-instructor-courses" class="mb-3">
                <div class="d-flex flex-wrap" id="view-course-list">
                  <!-- Course thumbnails will be inserted here -->
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

  <!-- Add Instructor Modal -->
  <div class="modal fade" id="addInstructorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Instructor</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="addInstructorForm" action="../backend/admin/add-instructor.php" method="POST">
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="firstName" class="form-label">First Name</label>
                <input type="text" id="firstName" name="first_name" class="form-control" placeholder="Enter First Name" required />
              </div>
              <div class="col-md-6 mb-3">
                <label for="lastName" class="form-label">Last Name</label>
                <input type="text" id="lastName" name="last_name" class="form-control" placeholder="Enter Last Name" required />
              </div>
            </div>

            <div class="row">
              <div class="col-12 mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Enter Email" required />
              </div>
            </div>

            <div class="row">
              <div class="col-12 mb-3">
                <label for="department" class="form-label">Department</label>
                <div class="input-group">
                  <input type="text" id="departmentSearch" class="form-control" placeholder="Search department..." autocomplete="off">
                  <input type="hidden" id="departmentId" name="department_id">
                  <button class="btn btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bx bx-chevron-down"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end" id="departmentDropdown">
                    <?php foreach ($departments as $department): ?>
                      <li><a class="dropdown-item department-item" href="javascript:void(0);" data-id="<?php echo $department['department_id']; ?>"><?php echo htmlspecialchars($department['name']); ?></a></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
                <div id="selectedDepartment" class="form-text">No department selected</div>
              </div>
            </div>

            <div class="row">
              <div class="col-12 mb-3">
                <label for="bio" class="form-label">Bio</label>
                <textarea id="bio" name="bio" class="form-control" rows="3" placeholder="Enter instructor bio"></textarea>
              </div>
            </div>

            <div class="row">
              <div class="col mb-3">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="sendInvitation" name="send_invitation" checked />
                  <label class="form-check-label" for="sendInvitation">Send invitation email</label>
                </div>
              </div>
            </div>

            <div id="emailMessage" class="alert alert-info">
              <div class="d-flex">
                <span class="alert-icon text-info me-2">
                  <i class="bx bx-envelope"></i>
                </span>
                <div>
                  An invitation email will be sent with instructions to create an account.
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Add Instructor</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Change Status Modal -->
  <div class="modal fade" id="changeStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Change Instructor Status</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="changeStatusForm" action="../backend/admin/update-instructor-status.php" method="POST">
          <div class="modal-body">
            <input type="hidden" id="statusInstructorId" name="instructor_id" value="">
            <input type="hidden" id="statusAction" name="status" value="">
            <input type="hidden" id="statusUserEmail" name="user_email" value="">
            <input type="hidden" id="statusUserName" name="user_name" value="">

            <div class="text-center mb-4">
              <div class="avatar avatar-lg">
                <img src="../assets/img/avatars/1.png" alt="Instructor Avatar" class="rounded-circle" id="status-instructor-image">
              </div>
              <h5 class="mt-2 mb-0" id="status-instructor-name"></h5>
              <p class="text-muted small mb-2" id="status-instructor-email"></p>
              <p class="text-muted small" id="status-instructor-department"></p>
            </div>

            <div id="activate-message" class="d-none">
              <div class="alert alert-success">
                <h6 class="alert-heading mb-1">Activate Instructor</h6>
                <p class="mb-0">Are you sure you want to activate this instructor? This will allow them to log in and access instructor features.</p>
              </div>
            </div>

            <div id="deactivate-message" class="d-none">
              <div class="alert alert-warning">
                <h6 class="alert-heading mb-1">Deactivate Instructor</h6>
                <p class="mb-0">Are you sure you want to deactivate this instructor? They will not be able to access their account until reactivated.</p>
              </div>
            </div>

            <div id="suspend-message" class="d-none">
              <div class="alert alert-danger">
                <h6 class="alert-heading mb-1">Suspend Instructor</h6>
                <p class="mb-0">Are you sure you want to suspend this instructor? This will prevent them from accessing any instructor features and notify them of the suspension.</p>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Confirm</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Delete Instructor Modal -->
  <div class="modal fade" id="deleteInstructorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Delete Instructor</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="deleteInstructorForm" action="../backend/admin/delete-instructor.php" method="POST">
          <div class="modal-body">
            <input type="hidden" id="deleteInstructorId" name="instructor_id" value="">

            <div class="text-center mb-4">
              <i class="bx bx-error-circle text-danger" style="font-size: 6rem;"></i>
            </div>

            <p class="mb-0 text-center">Are you sure you want to delete <strong id="delete-instructor-name"></strong>?</p>

            <div id="delete-warning" class="alert alert-warning mt-3">
              <div class="d-flex">
                <i class="bx bx-error me-2 mt-1"></i>
                <div>
                  <p class="mb-0">This instructor has <strong id="delete-course-count"></strong> courses. Deleting this instructor will remove them from these courses.</p>
                  <p class="mb-0 mt-2">Consider <strong>deactivating</strong> the instructor instead.</p>
                </div>
              </div>
            </div>

            <p class="text-center text-danger mt-3">This action cannot be undone.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">Delete Permanently</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Change Department Modal -->
  <div class="modal fade" id="changeDepartmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Change Department</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="changeDepartmentForm" action="../backend/admin/update-instructor-department.php" method="POST">
          <div class="modal-body">
            <input type="hidden" id="deptInstructorId" name="instructor_id" value="">
            <input type="hidden" id="deptUserEmail" name="user_email" value="">
            <input type="hidden" id="deptUserName" name="user_name" value="">

            <div class="text-center mb-4">
              <div class="avatar avatar-lg">
                <img src="../assets/img/avatars/1.png" alt="Instructor Avatar" class="rounded-circle" id="dept-instructor-image">
              </div>
              <h5 class="mt-2 mb-0" id="dept-instructor-name"></h5>
              <p class="text-muted small mb-0" id="dept-instructor-email"></p>
            </div>

            <div class="mb-3">
              <label for="departmentSelect" class="form-label">Department</label>
              <select class="form-select" id="departmentSelect" name="department_id" required>
                <option value="">Select Department</option>
                <?php foreach ($departments as $dept): ?>
                  <option value="<?php echo $dept['department_id']; ?>">
                    <?php echo htmlspecialchars($dept['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">Current department: <span id="current-department">None</span></div>
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
</div>
<!-- / Content -->

<script>
 document.addEventListener('DOMContentLoaded', function() {
  // **Initialize Tooltips**
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.forEach(function(tooltipTriggerEl) {
    new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // **Toast Notification Function**
  function showToast(message, type = 'success') {
    const toastEl = document.getElementById(type === 'success' ? 'successToast' : 'errorToast');
    const toastMessageEl = document.getElementById(type === 'success' ? 'successToastMessage' : 'errorToastMessage');

    if (toastEl && toastMessageEl) {
      toastMessageEl.textContent = message;
      const toast = new bootstrap.Toast(toastEl);
      toast.show();
    }
  }

  // **Show/Hide Loading Overlay**
  function showOverlay(message = 'Processing...') {
    const overlay = document.getElementById('loadingOverlay');
    const messageEl = document.getElementById('loading-message');

    if (messageEl) {
      messageEl.textContent = message;
    }

    overlay.style.display = 'flex';
    overlay.dataset.startTime = Date.now();
  }

  function removeOverlay() {
    const overlay = document.getElementById('loadingOverlay');
    const startTime = parseInt(overlay.dataset.startTime || 0);
    const currentTime = Date.now();
    const elapsedTime = currentTime - startTime;

    if (elapsedTime >= 1000) {
      overlay.style.display = 'none';
    } else {
      setTimeout(() => {
        overlay.style.display = 'none';
      }, 1000 - elapsedTime);
    }
  }

  // **Pagination Functionality**
  const ITEMS_PER_PAGE = 10; // Max number of items per page
  let currentPage = 1;

  function setupPagination() {
    const visibleRows = Array.from(document.querySelectorAll('.instructor-row'))
      .filter(row => row.style.display !== 'none');

    const totalItems = visibleRows.length;
    const totalPages = Math.ceil(totalItems / ITEMS_PER_PAGE);

    document.getElementById('showing-start').textContent =
      totalItems > 0 ? ((currentPage - 1) * ITEMS_PER_PAGE + 1) : 0;
    document.getElementById('showing-end').textContent =
      Math.min(currentPage * ITEMS_PER_PAGE, totalItems);
    document.getElementById('total-entries').textContent = totalItems;

    visibleRows.forEach(row => row.classList.add('d-none'));

    const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
    const endIndex = Math.min(startIndex + ITEMS_PER_PAGE, totalItems);

    for (let i = startIndex; i < endIndex; i++) {
      visibleRows[i].classList.remove('d-none');
    }

    updatePaginationUI(totalPages);
  }

  function updatePaginationUI(totalPages) {
    const paginationContainer = document.querySelector('#pagination-container ul');
    const pageItems = document.querySelectorAll('#pagination-container ul li:not(.previous):not(.next)');
    pageItems.forEach(item => item.remove());

    const prevButton = document.getElementById('pagination-previous');
    prevButton.classList.toggle('disabled', currentPage === 1);

    const nextButton = document.getElementById('pagination-next');
    nextButton.classList.toggle('disabled', currentPage === totalPages || totalPages === 0);

    const maxVisiblePages = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

    if (endPage - startPage + 1 < maxVisiblePages && startPage > 1) {
      startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
      const li = document.createElement('li');
      li.className = `paginate_button page-item ${i === currentPage ? 'active' : ''}`;
      li.innerHTML = `<a href="#" class="page-link">${i}</a>`;
      li.addEventListener('click', function(e) {
        e.preventDefault();
        if (i !== currentPage) {
          currentPage = i;
          setupPagination();
        }
      });
      paginationContainer.insertBefore(li, nextButton);
    }

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

  document.getElementById('pagination-previous').addEventListener('click', function(e) {
    e.preventDefault();
    if (currentPage > 1) {
      currentPage--;
      setupPagination();
    }
  });

  document.getElementById('pagination-next').addEventListener('click', function(e) {
    e.preventDefault();
    const visibleRows = document.querySelectorAll('.instructor-row:not([style*="display: none"])');
    const totalPages = Math.ceil(visibleRows.length / ITEMS_PER_PAGE);
    if (currentPage < totalPages) {
      currentPage++;
      setupPagination();
    }
  });

  // **Filter Functionality**
  let currentStatusFilter = 'all';

  document.getElementById('instructorSearch').addEventListener('keyup', filterInstructors);

  document.querySelectorAll('.filter-status').forEach(btn => {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.filter-status').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      currentStatusFilter = this.getAttribute('data-status');
      filterInstructors();
    });
  });

  document.querySelectorAll('.status-card').forEach(card => {
    card.addEventListener('click', function() {
      const status = this.getAttribute('data-status');
      document.querySelectorAll('.filter-status').forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('data-status') === status) btn.classList.add('active');
      });
      currentStatusFilter = status;
      filterInstructors();
    });
  });

  document.getElementById('clearFiltersBtn').addEventListener('click', function() {
    document.getElementById('instructorSearch').value = '';
    currentStatusFilter = 'all';
    document.querySelectorAll('.filter-status').forEach(btn => {
      btn.classList.remove('active');
      if (btn.getAttribute('data-status') === 'all') btn.classList.add('active');
    });
    filterInstructors();
  });

  function filterInstructors() {
    const searchTerm = document.getElementById('instructorSearch').value.toLowerCase();
    const rows = document.querySelectorAll('.instructor-row');
    const emptySearchResults = document.getElementById('empty-search-results');
    let visibleCount = 0;

    rows.forEach(row => {
      const name = row.getAttribute('data-name').toLowerCase();
      const email = row.getAttribute('data-email').toLowerCase();
      const department = row.getAttribute('data-department-name').toLowerCase();
      const status = row.getAttribute('data-status');

      const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm) || department.includes(searchTerm);
      const matchesStatus = currentStatusFilter === 'all' || status === currentStatusFilter;

      if (matchesSearch && matchesStatus) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (visibleCount === 0 && rows.length > 0) {
      if (emptySearchResults) emptySearchResults.classList.remove('d-none');
    } else {
      if (emptySearchResults) emptySearchResults.classList.add('d-none');
      currentPage = 1;
      setupPagination();
    }
  }

  // **Department Search for Add Instructor Modal**
  const departmentSearchInput = document.getElementById('departmentSearch');
  const departmentItems = document.querySelectorAll('.department-item');
  const selectedDepartmentText = document.getElementById('selectedDepartment');

  if (departmentSearchInput) {
    departmentSearchInput.addEventListener('keyup', function() {
      const searchTerm = this.value.toLowerCase();
      departmentItems.forEach(item => {
        const departmentName = item.textContent.toLowerCase();
        item.style.display = departmentName.includes(searchTerm) ? '' : 'none';
      });
    });

    departmentItems.forEach(item => {
      item.addEventListener('click', function() {
        const deptId = this.getAttribute('data-id');
        const deptName = this.textContent;
        departmentSearchInput.value = deptName;
        document.getElementById('departmentId').value = deptId;
        selectedDepartmentText.textContent = `Selected: ${deptName}`;
        selectedDepartmentText.classList.add('text-success');
      });
    });
  }

  // **View Instructor Details**
  document.querySelectorAll('.view-instructor').forEach(btn => {
    btn.addEventListener('click', function() {
      const row = this.closest('tr');
      const viewModal = document.getElementById('viewInstructorModal');

      document.getElementById('view-instructor-image').src = row.getAttribute('data-profile-pic');
      document.getElementById('view-instructor-name').textContent = row.getAttribute('data-name');
      document.getElementById('view-instructor-email').textContent = row.getAttribute('data-email');

      const status = row.getAttribute('data-status');
      const statusBadgeClass = status === 'active' ? 'bg-label-success' :
        status === 'pending' ? 'bg-label-warning' :
        status === 'inactive' ? 'bg-label-info' : 'bg-label-danger';
      document.getElementById('view-instructor-status').className = `badge ${statusBadgeClass}`;
      document.getElementById('view-instructor-status').textContent = ucfirst(status);

      document.getElementById('view-instructor-bio').textContent = row.getAttribute('data-bio') || 'No bio available';
      document.getElementById('view-instructor-department').textContent = row.getAttribute('data-department-name');
      document.getElementById('view-instructor-joined').textContent = row.getAttribute('data-created');

      document.getElementById('view-course-list').innerHTML = '';
      const coursesCount = parseInt(row.getAttribute('data-courses'));
      document.getElementById('view-course-list').innerHTML = coursesCount > 0 ?
        `<div class="alert alert-info w-100 mb-0">
            <div class="d-flex">
              <i class="bx bx-info-circle me-2 mt-1"></i>
              <div>This instructor has ${coursesCount} assigned courses.</div>
            </div>
          </div>` :
        `<div class="alert alert-light w-100 mb-0">
            <div class="d-flex">
              <i class="bx bx-error-circle me-2 mt-1"></i>
              <div>No courses assigned to this instructor.</div>
            </div>
          </div>`;

      viewModal.setAttribute('data-instructor-id', row.getAttribute('data-id'));
      viewModal.setAttribute('data-instructor-name', row.getAttribute('data-name'));
      viewModal.setAttribute('data-instructor-email', row.getAttribute('data-email'));
      viewModal.setAttribute('data-instructor-img', row.getAttribute('data-profile-pic'));
      viewModal.setAttribute('data-department-id', row.getAttribute('data-department') || '');
      viewModal.setAttribute('data-department-name', row.getAttribute('data-department-name') || 'Not Assigned');
    });
  });

  // **Change Status Buttons**
  document.querySelectorAll('.change-status').forEach(btn => {
    btn.addEventListener('click', function() {
      const row = this.closest('tr');
      const instructorId = row.getAttribute('data-id');
      const newStatus = this.getAttribute('data-status');

      document.getElementById('statusInstructorId').value = instructorId;
      document.getElementById('statusAction').value = newStatus;
      document.getElementById('statusUserEmail').value = row.getAttribute('data-email');
      document.getElementById('statusUserName').value = row.getAttribute('data-name');

      document.getElementById('status-instructor-image').src = row.getAttribute('data-profile-pic');
      document.getElementById('status-instructor-name').textContent = row.getAttribute('data-name');
      document.getElementById('status-instructor-email').textContent = row.getAttribute('data-email');
      document.getElementById('status-instructor-department').textContent = row.getAttribute('data-department-name');

      document.getElementById('activate-message').classList.add('d-none');
      document.getElementById('deactivate-message').classList.add('d-none');
      document.getElementById('suspend-message').classList.add('d-none');

      if (newStatus === 'active') document.getElementById('activate-message').classList.remove('d-none');
      else if (newStatus === 'inactive') document.getElementById('deactivate-message').classList.remove('d-none');
      else if (newStatus === 'suspended') document.getElementById('suspend-message').classList.remove('d-none');

      const modal = new bootstrap.Modal(document.getElementById('changeStatusModal'));
      modal.show();
    });
  });

  // **Suspend Instructor**
  document.querySelectorAll('.suspend-instructor').forEach(btn => {
    btn.addEventListener('click', function() {
      const row = this.closest('tr');
      const instructorId = row.getAttribute('data-id');

      document.getElementById('statusInstructorId').value = instructorId;
      document.getElementById('statusAction').value = 'suspended';
      document.getElementById('statusUserEmail').value = row.getAttribute('data-email');
      document.getElementById('statusUserName').value = row.getAttribute('data-name');

      document.getElementById('status-instructor-image').src = row.getAttribute('data-profile-pic');
      document.getElementById('status-instructor-name').textContent = row.getAttribute('data-name');
      document.getElementById('status-instructor-email').textContent = row.getAttribute('data-email');
      document.getElementById('status-instructor-department').textContent = row.getAttribute('data-department-name');

      document.getElementById('activate-message').classList.add('d-none');
      document.getElementById('deactivate-message').classList.add('d-none');
      document.getElementById('suspend-message').classList.remove('d-none');

      const modal = new bootstrap.Modal(document.getElementById('changeStatusModal'));
      modal.show();
    });
  });

  // **Delete Instructor**
  document.querySelectorAll('.delete-instructor').forEach(btn => {
    btn.addEventListener('click', function() {
      const row = this.closest('tr');
      const instructorId = row.getAttribute('data-id');
      const courseCount = parseInt(row.getAttribute('data-courses'));

      document.getElementById('deleteInstructorId').value = instructorId;
      document.getElementById('delete-instructor-name').textContent = row.getAttribute('data-name');
      document.getElementById('delete-course-count').textContent = courseCount;

      document.getElementById('delete-warning').classList.toggle('d-none', courseCount <= 0);
    });
  });

  // **AJAX Form Submission Handler**
  function handleFormSubmit(formId, url) {
    const form = document.getElementById(formId);
    if (!form) return;

    form.addEventListener('submit', function(e) {
      e.preventDefault();
      showOverlay('Processing...');

      fetch(url, {
        method: 'POST',
        body: new FormData(form)
      })
        .then(response => {
          if (!response.ok) throw new Error('Network response was not ok');
          return response.json();
        })
        .then(data => {
          removeOverlay();
          if (data.status === 'success') {
            showToast(data.message, 'success');
            const modal = bootstrap.Modal.getInstance(form.closest('.modal'));
            if (modal) modal.hide();
            setTimeout(() => window.location.reload(), 1500);
          } else {
            showToast(data.message || 'An error occurred', 'error');
          }
        })
        .catch(error => {
          removeOverlay();
          console.error('Error:', error);
          showToast('An unexpected error occurred. Please try again.', 'error');
        });
    });
  }

  handleFormSubmit('addInstructorForm', '../backend/admin/add-instructor.php');
  handleFormSubmit('changeStatusForm', '../backend/admin/update-instructor-status.php');
  handleFormSubmit('deleteInstructorForm', '../backend/admin/delete-instructor.php');
  handleFormSubmit('changeDepartmentForm', '../backend/admin/update-instructor-department.php');

  // **Export Functionality**
  document.getElementById('exportBtn').addEventListener('click', function() {
    showOverlay('Generating export...');
    const table = document.getElementById('instructorsTable');
    const rows = table.querySelectorAll('tbody tr:not([style*="display: none"])');

    let csvContent = "data:text/csv;charset=utf-8,Name,Email,Status,Department,Courses\n";
    rows.forEach(row => {
      const name = row.getAttribute('data-name').replace(/"/g, '""');
      const email = row.getAttribute('data-email').replace(/"/g, '""');
      const status = row.getAttribute('data-status');
      const department = row.getAttribute('data-department-name').replace(/"/g, '""');
      const courseCount = row.getAttribute('data-courses');
      csvContent += `"${name}","${email}","${ucfirst(status)}","${department}","${courseCount}"\n`;
    });

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `instructors_export_${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);

    setTimeout(() => {
      link.click();
      document.body.removeChild(link);
      removeOverlay();
      showToast('Instructors list exported successfully', 'success');
    }, 1000);
  });

  // **Change Department Functionality (Updated)**
  document.addEventListener('click', function(e) {
    if (e.target.matches('.change-department') || e.target.closest('.change-department')) {
      const viewModal = document.getElementById('viewInstructorModal');
      const instructorId = viewModal.getAttribute('data-instructor-id');
      const instructorName = viewModal.getAttribute('data-instructor-name');
      const instructorEmail = viewModal.getAttribute('data-instructor-email');
      const instructorImg = viewModal.getAttribute('data-instructor-img');
      const departmentId = viewModal.getAttribute('data-department-id');
      const departmentName = viewModal.getAttribute('data-department-name');

      if (!instructorId) {
        console.error('No instructor ID found in the modal');
        showToast('Instructor data not found. Please refresh the page.', 'error');
        return;
      }

      document.getElementById('deptInstructorId').value = instructorId;
      document.getElementById('deptUserEmail').value = instructorEmail;
      document.getElementById('deptUserName').value = instructorName;
      document.getElementById('dept-instructor-image').src = instructorImg;
      document.getElementById('dept-instructor-name').textContent = instructorName;
      document.getElementById('dept-instructor-email').textContent = instructorEmail;
      document.getElementById('current-department').textContent = departmentName || 'None';

      document.getElementById('departmentSelect').value = departmentId || '';

      document.getElementById('changeDepartmentModal').setAttribute('data-return-modal', 'viewInstructorModal');
    }
  });

  document.getElementById('changeDepartmentModal').addEventListener('hidden.bs.modal', function() {
    const returnModalId = this.getAttribute('data-return-modal');
    if (returnModalId) {
      const returnModal = new bootstrap.Modal(document.getElementById(returnModalId));
      returnModal.show();
    }
  });

  // **Utility Function**
  function ucfirst(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
  }

  // **Initialize Pagination**
  setupPagination();
});
</script>

<?php include_once '../includes/admin/footer.php'; ?>