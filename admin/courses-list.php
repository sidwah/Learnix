<?php
// Authentication check
require_once '../backend/auth/admin/admin-auth-check.php';

// Set page title
$pageTitle = "Courses - Admin | Learnix";

include_once '../includes/admin/header.php';

// <!-- Menu -->
include_once '../includes/admin/sidebar.php';
// <!-- / Menu -->

// <!-- Navbar -->
include_once '../includes/admin/navbar.php';
// <!-- / Navbar -->

// Get data from database
require_once '../backend/config.php';

// Fetch all courses with their details
$query = "SELECT c.course_id, c.title, c.short_description, c.thumbnail, c.status, c.approval_status, 
                 c.created_at, c.department_id, d.name as department_name,
                 COUNT(DISTINCT e.enrollment_id) AS enrolled_students,
                 GROUP_CONCAT(DISTINCT CONCAT(u.first_name, ' ', u.last_name) SEPARATOR '|') as instructor_names,
                 GROUP_CONCAT(DISTINCT ci.instructor_id) as instructor_ids
          FROM courses c
          LEFT JOIN departments d ON c.department_id = d.department_id
          LEFT JOIN enrollments e ON c.course_id = e.course_id AND e.deleted_at IS NULL
          LEFT JOIN course_instructors ci ON c.course_id = ci.course_id AND ci.deleted_at IS NULL
          LEFT JOIN instructors i ON ci.instructor_id = i.instructor_id
          LEFT JOIN users u ON i.user_id = u.user_id
          WHERE c.deleted_at IS NULL
          GROUP BY c.course_id
          ORDER BY c.created_at DESC";

$result = mysqli_query($conn, $query);

// Count statistics
$totalCourses = 0;
$publishedCounts = 0;
$draftCounts = 0;
$pendingCounts = 0;
$approvedCounts = 0;
$rejectedCounts = 0;
$revisionsRequestedCounts = 0;

$courses = [];
if ($result && mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
    $courses[] = $row;
    $totalCourses++;

    // Count by status
    if ($row['status'] == 'Published') $publishedCounts++;
    if ($row['status'] == 'Draft') $draftCounts++;

    // Count by approval status
    if ($row['approval_status'] == 'pending') $pendingCounts++;
    if ($row['approval_status'] == 'approved') $approvedCounts++;
    if ($row['approval_status'] == 'rejected') $rejectedCounts++;
    if ($row['approval_status'] == 'revisions_requested') $revisionsRequestedCounts++;
  }
}
?>

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
    <span class="text-muted fw-light">Admin /</span> Courses
  </h4>

  <!-- Cards -->
  <div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
      <div class="card status-card" data-status="Published" data-type="status">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="card-info">
              <p class="card-text">Published Courses</p>
              <div class="d-flex align-items-end mb-2">
                <h4 class="card-title mb-0 me-2"><?php echo $publishedCounts; ?></h4>
              </div>
            </div>
            <div class="card-icon">
              <span class="badge bg-label-success rounded p-2">
                <i class="bx bx-globe bx-md"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-3 col-sm-6 mb-3">
      <div class="card status-card" data-status="Draft" data-type="status">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="card-info">
              <p class="card-text">Draft Courses</p>
              <div class="d-flex align-items-end mb-2">
                <h4 class="card-title mb-0 me-2"><?php echo $draftCounts; ?></h4>
              </div>
            </div>
            <div class="card-icon">
              <span class="badge bg-label-secondary rounded p-2">
                <i class="bx bx-edit bx-md"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-3 col-sm-6 mb-3">
      <div class="card status-card" data-status="pending" data-type="approval">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="card-info">
              <p class="card-text">Pending Approval</p>
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
      <div class="card status-card" data-status="approved" data-type="approval">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="card-info">
              <p class="card-text">Approved Courses</p>
              <div class="d-flex align-items-end mb-2">
                <h4 class="card-title mb-0 me-2"><?php echo $approvedCounts; ?></h4>
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
  </div>

  <!-- Courses List -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Courses List</h5>
      <div class="d-flex align-items-center">
        <?php if ($totalCourses > 0): ?>
          <div class="me-3">
            <div class="input-group input-group-sm">
              <span class="input-group-text"><i class="bx bx-search"></i></span>
              <input type="text" class="form-control" id="courseSearch" placeholder="Search courses..." aria-label="Search">
            </div>
          </div>
          <div class="me-3">
            <div class="btn-group" role="group" aria-label="Filter by status">
              <button type="button" class="btn btn-outline-secondary btn-sm filter-status active" data-type="all">
                All
              </button>
              <button type="button" class="btn btn-outline-success btn-sm filter-status" data-type="status" data-status="Published">
                Published
              </button>
              <button type="button" class="btn btn-outline-secondary btn-sm filter-status" data-type="status" data-status="Draft">
                Draft
              </button>
            </div>
          </div>
          <div class="dropdown me-3">
            <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bx bx-filter-alt me-1"></i> Approval Status
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a class="dropdown-item filter-approval-item active" href="javascript:void(0);" data-status="all">All Statuses</a>
              </li>
              <li>
                <a class="dropdown-item filter-approval-item" href="javascript:void(0);" data-status="pending">Pending</a>
              </li>
              <li>
                <a class="dropdown-item filter-approval-item" href="javascript:void(0);" data-status="approved">Approved</a>
              </li>
              <li>
                <a class="dropdown-item filter-approval-item" href="javascript:void(0);" data-status="rejected">Rejected</a>
              </li>
              <li>
                <a class="dropdown-item filter-approval-item" href="javascript:void(0);" data-status="revisions_requested">Revisions Requested</a>
              </li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li>
                <a class="dropdown-item" href="javascript:void(0);" id="clearFiltersBtn">
                  <i class="bx bx-reset me-1"></i> Clear All Filters
                </a>
              </li>
            </ul>
          </div>
        <?php endif; ?>
        <?php if ($totalCourses > 0): ?>
          <button type="button" class="btn btn-outline-secondary btn-sm" id="exportBtn">
            <i class="bx bx-export me-1"></i> Export
          </button>
        <?php endif; ?>
      </div>
    </div>

    <?php if (count($courses) > 0): ?>
      <div class="table-responsive text-nowrap">
        <table class="table" id="coursesTable">
          <thead>
            <tr>
              <th>Course</th>
              <th>Status</th>
              <th>Department</th>
              <th>Instructor</th>
              <th>Students</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            <?php foreach ($courses as $course): 
              $instructorNames = $course['instructor_names'] ? explode('|', $course['instructor_names']) : [];
              $instructorIds = $course['instructor_ids'] ? explode(',', $course['instructor_ids']) : [];
              ?>
              <tr class="course-row"
                data-id="<?php echo $course['course_id']; ?>"
                data-status="<?php echo $course['status']; ?>"
                data-approval="<?php echo $course['approval_status']; ?>"
                data-department="<?php echo $course['department_id']; ?>"
                data-title="<?php echo htmlspecialchars($course['title']); ?>"
                data-description="<?php echo htmlspecialchars($course['short_description']); ?>"
                data-thumbnail="<?php echo !empty($course['thumbnail']) ? '../uploads/thumbnails/' . $course['thumbnail'] : '../uploads/thumbnails/default.jpg'; ?>"
                data-instructor-ids="<?php echo htmlspecialchars(implode(',', $instructorIds)); ?>"
                data-instructor-names="<?php echo htmlspecialchars(implode(', ', $instructorNames)); ?>"
                data-students="<?php echo $course['enrolled_students']; ?>"
                data-created="<?php echo date('F d, Y', strtotime($course['created_at'])); ?>"
                data-department-name="<?php echo htmlspecialchars($course['department_name'] ?? 'Unknown Department'); ?>">
                <td>
                  <div class="d-flex justify-content-start align-items-center">
                    <div class="avatar-wrapper">
                      <div class="avatar me-2">
                        <img src="<?php echo !empty($course['thumbnail']) ? '../uploads/thumbnails/' . $course['thumbnail'] : '../uploads/thumbnails/default.jpg'; ?>" alt="Course Thumbnail" class="rounded">
                      </div>
                    </div>
                    <div class="d-flex flex-column">
                      <span class="fw-semibold"><?php echo htmlspecialchars($course['title']); ?></span>
                      <small class="text-muted"><?php echo htmlspecialchars(substr($course['short_description'], 0, 50) . (strlen($course['short_description']) > 50 ? '...' : '')); ?></small>
                    </div>
                  </div>
                </td>
                <td>
                  <div>
                    <span class="badge bg-label-<?php 
                                                echo $course['status'] == 'Published' ? 'success' : 'secondary'; ?>">
                      <?php echo $course['status']; ?>
                    </span>
                    <br>
                    <small class="text-muted mt-1">
                      <span class="badge bg-label-<?php 
                                                  echo $course['approval_status'] == 'approved' ? 'success' : 
                                                      ($course['approval_status'] == 'pending' ? 'warning' : 
                                                      ($course['approval_status'] == 'rejected' ? 'danger' : 
                                                      ($course['approval_status'] == 'revisions_requested' ? 'info' : 'secondary'))); ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $course['approval_status'])); ?>
                      </span>
                    </small>
                  </div>
                </td>
                <td><?php echo htmlspecialchars($course['department_name'] ?? 'Unknown Department'); ?></td>
                <td>
                  <?php if (!empty($instructorNames)): ?>
                    <?php 
                      $displayInstructors = array_slice($instructorNames, 0, 2);
                      echo htmlspecialchars(implode(', ', $displayInstructors));
                      if (count($instructorNames) > 2) {
                        echo ' <span class="badge bg-light text-dark">+' . (count($instructorNames) - 2) . ' more</span>';
                      }
                    ?>
                  <?php else: ?>
                    <span class="text-muted">No instructors assigned</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge bg-label-primary"><?php echo $course['enrolled_students']; ?></span>
                </td>
                <td class="text-center">
                  <div class="d-inline-block">
                    <button type="button" class="btn btn-sm btn-icon btn-outline-primary rounded-pill btn-icon view-course" data-bs-toggle="modal" data-bs-target="#viewCourseModal" title="View Details">
                      <i class="bx bx-show-alt"></i>
                    </button>

                    <?php if ($course['approval_status'] == 'pending' || $course['approval_status'] == 'under_review'): ?>
                      <button type="button" class="btn btn-sm btn-icon btn-outline-success rounded-pill btn-icon change-status" 
                              data-status="" data-approval="approved" title="Approve">
                        <i class="bx bx-check"></i>
                      </button>

                      <button type="button" class="btn btn-sm btn-icon btn-outline-info rounded-pill btn-icon change-status" 
                              data-status="" data-approval="revisions_requested" title="Request Revisions">
                        <i class="bx bx-revision"></i>
                      </button>

                      <button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-pill btn-icon change-status" 
                              data-status="" data-approval="rejected" title="Reject">
                        <i class="bx bx-x"></i>
                      </button>
                    <?php endif; ?>

                    <?php if ($course['approval_status'] == 'approved'): ?>
                      <?php if ($course['status'] == 'Draft'): ?>
                        <button type="button" class="btn btn-sm btn-icon btn-outline-success rounded-pill btn-icon change-status" 
                                data-status="Published" data-approval="" title="Publish">
                          <i class="bx bx-globe"></i>
                        </button>
                      <?php else: ?>
                        <button type="button" class="btn btn-sm btn-icon btn-outline-secondary rounded-pill btn-icon change-status" 
                                data-status="Draft" data-approval="" title="Unpublish">
                          <i class="bx bx-hide"></i>
                        </button>
                      <?php endif; ?>
                    <?php endif; ?>

                    <button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-pill btn-icon delete-course" data-bs-toggle="modal" data-bs-target="#deleteCourseModal" title="Delete">
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
            <h5 class="mb-2">No Courses Found</h5>
            <p class="mb-0 text-muted">No courses match your current filters.</p>
            <button type="button" class="btn btn-sm btn-outline-primary mt-3" id="clearFiltersBtn2">
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
              Showing <span id="showing-start">1</span> to <span id="showing-end"><?php echo min(10, count($courses)); ?></span> of <span id="total-entries"><?php echo $totalCourses; ?></span> entries
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
                <?php if ($totalCourses > 10): ?>
                  <li class="paginate_button page-item">
                    <a href="#" class="page-link">2</a>
                  </li>
                <?php endif; ?>
                <?php if ($totalCourses > 20): ?>
                  <li class="paginate_button page-item">
                    <a href="#" class="page-link">3</a>
                  </li>
                <?php endif; ?>
                <li class="paginate_button page-item next<?php echo ($totalCourses <= 10) ? ' disabled' : ''; ?>" id="pagination-next">
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
            <i class="bx bx-book-content" style="font-size: 6rem; color: #dfe3e7;"></i>
          </div>
          <h4 class="mb-2">No Courses Found</h4>
          <p class="mb-4 text-muted">There are no courses available on the platform yet.</p>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- View Course Modal -->
  <div class="modal fade" id="viewCourseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Course Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-4 text-center mb-4 mb-md-0">
              <img src="../assets/img/backgrounds/course-default.jpg" alt="Course Thumbnail" class="rounded border img-fluid" id="view-course-image">
              <div class="mt-3">
                <h5 id="view-course-title" class="mb-1"></h5>
                <p class="text-muted small" id="view-course-department"></p>
                <div id="view-course-status-badges" class="mt-2">
                  <span class="badge bg-label-success" id="view-course-status"></span>
                  <span class="badge bg-label-warning" id="view-approval-status"></span>
                </div>
              </div>
            </div>
            <div class="col-md-8">
              <div class="mb-3">
                <h6 class="fw-semibold">Description</h6>
                <p id="view-course-description" class="text-muted"></p>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <h6 class="fw-semibold">Instructor(s)</h6>
                  <p id="view-course-instructors" class="text-muted"></p>
                </div>
                <div class="col-md-6">
                  <h6 class="fw-semibold">Created On</h6>
                  <p id="view-course-created" class="text-muted"></p>
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <h6 class="fw-semibold">Students Enrolled</h6>
                  <p id="view-course-students" class="text-muted"></p>
                </div>
              </div>
              
              <?php if (count($courses) > 0): ?>
              <div class="mt-4">
                <h6 class="fw-semibold mb-3">Actions</h6>
                <div id="view-course-actions">
                  <div id="approval-actions" class="mb-3">
                    <!-- Will be filled dynamically -->
                  </div>
                  <div id="publish-actions" class="mb-3">
                    <!-- Will be filled dynamically -->
                  </div>
                </div>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Course Status Change Modal -->
  <div class="modal fade" id="courseStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="status-change-title">Change Course Status</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="courseStatusForm" action="../backend/admin/update-course-status.php" method="POST">
          <div class="modal-body">
            <input type="hidden" id="statusCourseId" name="course_id" value="">
            <input type="hidden" id="statusCourseStatus" name="course_status" value="">
            <input type="hidden" id="statusApprovalStatus" name="approval_status" value="">

            <div class="text-center mb-4">
              <div class="avatar avatar-lg">
                <img src="../assets/img/backgrounds/course-default.jpg" alt="Course Thumbnail" class="rounded" id="status-course-image">
              </div>
              <h5 class="mt-2 mb-0" id="status-course-title"></h5>
              <p class="text-muted small mb-0" id="status-course-department"></p>
              <p class="text-muted small" id="status-course-instructor"></p>
            </div>

            <div id="status-message" class="alert alert-info">
              <h6 class="alert-heading mb-1" id="status-alert-title">Status Change</h6>
              <p class="mb-0" id="status-alert-message">You are about to change the status of this course.</p>
            </div>
            
            <!-- Publish After Approval Option - Only shown when approving -->
            <div id="publish-option" class="form-check form-switch mb-3 d-none">
              <input class="form-check-input" type="checkbox" id="publishAfterApproval">
              <label class="form-check-label" for="publishAfterApproval">Publish course after approval</label>
              <div class="form-text">If checked, the course will be published immediately after approval.</div>
            </div>

            <div class="mb-3">
              <label for="feedbackText" class="form-label">Feedback/Comments (Optional)</label>
              <textarea class="form-control" id="feedbackText" name="feedback" rows="4" placeholder="Enter any feedback or comments for the instructor..."></textarea>
              <div class="form-text">This feedback will be sent to the course instructor(s).</div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="confirmStatusBtn">Confirm Change</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Delete Course Modal -->
  <div class="modal fade" id="deleteCourseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Delete Course</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="deleteCourseForm" action="../backend/admin/delete-course.php" method="POST">
          <div class="modal-body">
            <input type="hidden" id="deleteCourseId" name="course_id" value="">

            <div class="text-center mb-4">
              <i class="bx bx-error-circle text-danger" style="font-size: 6rem;"></i>
            </div>

            <p class="mb-0 text-center">Are you sure you want to delete <strong id="delete-course-title"></strong>?</p>

            <div id="delete-warning" class="alert alert-warning mt-3">
              <div class="d-flex">
                <i class="bx bx-error me-2 mt-1"></i>
                <div>
                  <p class="mb-0">This action will remove this course, all its content, and enrollments.</p>
                  <p class="mb-0 mt-2" id="delete-student-count">Students enrolled: <strong id="delete-course-students">0</strong></p>
                  <p class="mb-0 mt-2">This action cannot be undone.</p>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">Delete Permanently</button>
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
      const visibleRows = Array.from(document.querySelectorAll('.course-row'))
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
      const visibleRows = document.querySelectorAll('.course-row:not([style*="display: none"])');
      const totalPages = Math.ceil(visibleRows.length / ITEMS_PER_PAGE);
      if (currentPage < totalPages) {
        currentPage++;
        setupPagination();
      }
    });

    // **Filter Functionality**
    let currentStatusFilter = 'all';
    let currentStatusType = 'all';
    let currentApprovalFilter = 'all';

    document.getElementById('courseSearch').addEventListener('keyup', filterCourses);

    document.querySelectorAll('.status-card').forEach(card => {
      card.addEventListener('click', function() {
        const status = this.getAttribute('data-status');
        const type = this.getAttribute('data-type');
        
        document.querySelectorAll('.filter-status').forEach(item => {
          item.classList.remove('active');
          if (item.getAttribute('data-status') === status && 
              item.getAttribute('data-type') === type) {
            item.classList.add('active');
          } else if (!status && !type && item.getAttribute('data-type') === 'all') {
            item.classList.add('active');
          }
        });
        
        if (type === 'status') {
          currentStatusFilter = status;
          currentStatusType = type;
        } else if (type === 'approval') {
          currentApprovalFilter = status;
        }
        
        filterCourses();
      });
    });
    
    document.querySelectorAll('.filter-status').forEach(item => {
      item.addEventListener('click', function() {
        document.querySelectorAll('.filter-status').forEach(i => i.classList.remove('active'));
        this.classList.add('active');
        
        if (this.getAttribute('data-type') === 'all') {
          currentStatusFilter = 'all';
          currentStatusType = 'all';
        } else {
          currentStatusFilter = this.getAttribute('data-status');
          currentStatusType = this.getAttribute('data-type');
        }
        
        filterCourses();
      });
    });
    
    document.querySelectorAll('.filter-approval-item').forEach(item => {
      item.addEventListener('click', function() {
        document.querySelectorAll('.filter-approval-item').forEach(i => i.classList.remove('active'));
        this.classList.add('active');
        
        currentApprovalFilter = this.getAttribute('data-status');
        filterCourses();
      });
    });

    const clearFiltersBtns = document.querySelectorAll('#clearFiltersBtn, #clearFiltersBtn2');
    clearFiltersBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        document.getElementById('courseSearch').value = '';
        currentStatusFilter = 'all';
        currentStatusType = 'all';
        currentApprovalFilter = 'all';
        
        document.querySelectorAll('.filter-status').forEach(item => {
          item.classList.remove('active');
          if (item.getAttribute('data-type') === 'all') {
            item.classList.add('active');
          }
        });
        
        document.querySelectorAll('.filter-approval-item').forEach(item => {
          item.classList.remove('active');
          if (item.getAttribute('data-status') === 'all') {
            item.classList.add('active');
          }
        });
        
        filterCourses();
      });
    });

    function filterCourses() {
      const searchTerm = document.getElementById('courseSearch').value.toLowerCase();
      const rows = document.querySelectorAll('.course-row');
      const emptySearchResults = document.getElementById('empty-search-results');
      let visibleCount = 0;

      rows.forEach(row => {
        const title = row.getAttribute('data-title').toLowerCase();
        const description = row.getAttribute('data-description').toLowerCase();
        const instructorNames = row.getAttribute('data-instructor-names').toLowerCase();
        const departmentName = row.getAttribute('data-department-name').toLowerCase();
        const status = row.getAttribute('data-status');
        const approvalStatus = row.getAttribute('data-approval');

        const matchesSearch = title.includes(searchTerm) || 
                              description.includes(searchTerm) || 
                              instructorNames.includes(searchTerm) ||
                              departmentName.includes(searchTerm);
                              
        const matchesStatus = currentStatusFilter === 'all' || 
                             (currentStatusType === 'status' && status === currentStatusFilter);
                            
        const matchesApproval = currentApprovalFilter === 'all' || 
                               approvalStatus === currentApprovalFilter;

        if (matchesSearch && matchesStatus && matchesApproval) {
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

    // **View Course Details**
    document.querySelectorAll('.view-course').forEach(btn => {
      btn.addEventListener('click', function() {
        const row = this.closest('tr');
        const courseId = row.getAttribute('data-id');
        const courseStatus = row.getAttribute('data-status');
        const approvalStatus = row.getAttribute('data-approval');
        
        // Set the details in the modal
        document.getElementById('view-course-image').src = row.getAttribute('data-thumbnail');
        document.getElementById('view-course-title').textContent = row.getAttribute('data-title');
        document.getElementById('view-course-department').textContent = row.getAttribute('data-department-name');
        document.getElementById('view-course-description').textContent = row.getAttribute('data-description') || 'No description available.';
        document.getElementById('view-course-instructors').textContent = row.getAttribute('data-instructor-names') || 'No instructors assigned.';
        document.getElementById('view-course-created').textContent = row.getAttribute('data-created');
        document.getElementById('view-course-students').textContent = row.getAttribute('data-students');
        
        // Set status badges
        const courseStatusBadge = document.getElementById('view-course-status');
        courseStatusBadge.textContent = courseStatus;
        courseStatusBadge.className = `badge ${courseStatus === 'Published' ? 'bg-label-success' : 'bg-label-secondary'}`;
        
        const approvalStatusBadge = document.getElementById('view-approval-status');
        approvalStatusBadge.textContent = approvalStatus.replace(/_/g, ' ');
        approvalStatusBadge.className = `badge ${
          approvalStatus === 'approved' ? 'bg-label-success' : 
          (approvalStatus === 'pending' ? 'bg-label-warning' : 
          (approvalStatus === 'rejected' ? 'bg-label-danger' : 
          (approvalStatus === 'revisions_requested' ? 'bg-label-info' : 'bg-label-secondary')))
        }`;
        
        // Setup available actions in the modal
        const approvalActionsContainer = document.getElementById('approval-actions');
        const publishActionsContainer = document.getElementById('publish-actions');
        
        approvalActionsContainer.innerHTML = '';
        publishActionsContainer.innerHTML = '';
        
        // Add approval actions if pending
        if (approvalStatus === 'pending' || approvalStatus === 'under_review') {
          approvalActionsContainer.innerHTML = `
            <h6>Approval Actions</h6>
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-success modal-change-status" data-status="" data-approval="approved">
                <i class="bx bx-check me-1"></i> Approve
              </button>
              <button type="button" class="btn btn-info modal-change-status" data-status="" data-approval="revisions_requested">
                <i class="bx bx-revision me-1"></i> Request Revisions
              </button>
              <button type="button" class="btn btn-danger modal-change-status" data-status="" data-approval="rejected">
                <i class="bx bx-x me-1"></i> Reject
              </button>
            </div>
          `;
        }
        
        // Add publish actions if approved
        if (approvalStatus === 'approved') {
          if (courseStatus === 'Draft') {
            publishActionsContainer.innerHTML = `
              <h6>Publication Actions</h6>
              <button type="button" class="btn btn-success modal-change-status" data-status="Published" data-approval="">
                <i class="bx bx-globe me-1"></i> Publish Course
              </button>
            `;
          } else {
            publishActionsContainer.innerHTML = `
              <h6>Publication Actions</h6>
              <button type="button" class="btn btn-secondary modal-change-status" data-status="Draft" data-approval="">
                <i class="bx bx-hide me-1"></i> Unpublish Course
              </button>
            `;
          }
        }
        
        // Add event listeners to the buttons in the modal
        document.querySelectorAll('.modal-change-status').forEach(button => {
          button.addEventListener('click', function() {
            const courseStatus = this.getAttribute('data-status') || '';
            const approvalStatus = this.getAttribute('data-approval') || '';
            
            console.log('Modal button clicked with:', {
              courseId,
              courseStatus,
              approvalStatus
            });
            
            // Fill in the status change modal
            document.getElementById('statusCourseId').value = courseId;
            document.getElementById('statusCourseStatus').value = courseStatus;
            document.getElementById('statusApprovalStatus').value = approvalStatus;
            
            document.getElementById('status-course-image').src = row.getAttribute('data-thumbnail');
            document.getElementById('status-course-title').textContent = row.getAttribute('data-title');
            document.getElementById('status-course-department').textContent = row.getAttribute('data-department-name');
            document.getElementById('status-course-instructor').textContent = `Instructor: ${row.getAttribute('data-instructor-names') || 'None'}`;
            
            // Set the appropriate status message
            const statusTitle = document.getElementById('status-alert-title');
            const statusMessage = document.getElementById('status-alert-message');
            const statusAlert = document.getElementById('status-message');
            const modalTitle = document.getElementById('status-change-title');
            const publishOption = document.getElementById('publish-option');
            
            // Hide publish option by default
            publishOption.classList.add('d-none');
            
            // Set modal content based on action type
            if (courseStatus) {
              if (courseStatus === 'Published') {
                modalTitle.textContent = 'Publish Course';
                statusTitle.textContent = 'Publish Course';
                statusMessage.textContent = 'You are about to publish this course, making it visible to students.';
                statusAlert.className = 'alert alert-success';
                document.getElementById('confirmStatusBtn').textContent = 'Publish';
              } else {
                modalTitle.textContent = 'Unpublish Course';
                statusTitle.textContent = 'Unpublish Course';
                statusMessage.textContent = 'You are about to unpublish this course, hiding it from students.';
                statusAlert.className = 'alert alert-secondary';
                document.getElementById('confirmStatusBtn').textContent = 'Unpublish';
              }
            } else if (approvalStatus) {
              if (approvalStatus === 'approved') {
                modalTitle.textContent = 'Approve Course';
                statusTitle.textContent = 'Approve Course';
                statusMessage.textContent = 'You are about to approve this course, allowing it to be published.';
                statusAlert.className = 'alert alert-success';
                document.getElementById('confirmStatusBtn').textContent = 'Approve';
                
                // Show publish option when approving
                publishOption.classList.remove('d-none');
              } else if (approvalStatus === 'rejected') {
                modalTitle.textContent = 'Reject Course';
                statusTitle.textContent = 'Reject Course';
                statusMessage.textContent = 'You are about to reject this course. The instructor will need to make significant changes before resubmitting.';
                statusAlert.className = 'alert alert-danger';
                document.getElementById('confirmStatusBtn').textContent = 'Reject';
              } else if (approvalStatus === 'revisions_requested') {
                modalTitle.textContent = 'Request Revisions';
                statusTitle.textContent = 'Request Revisions';
                statusMessage.textContent = 'You are about to request revisions for this course. The instructor will need to make the requested changes and resubmit.';
                statusAlert.className = 'alert alert-info';
                document.getElementById('confirmStatusBtn').textContent = 'Request Revisions';
              }
            }
            
            // Close the view modal and open the status change modal
            const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewCourseModal'));
            viewModal.hide();
            
            const statusModal = new bootstrap.Modal(document.getElementById('courseStatusModal'));
            statusModal.show();
          });
        });
        
        // Additional modal behavior can be added here
      });
    });

    // **Change Course Status**
    document.querySelectorAll('.change-status').forEach(btn => {
      btn.addEventListener('click', function() {
        const row = this.closest('tr');
        const courseId = row.getAttribute('data-id');
        const courseStatus = this.getAttribute('data-status') || '';
        const approvalStatus = this.getAttribute('data-approval') || '';
        
        console.log('Button clicked with:', {
          courseId,
          courseStatus,
          approvalStatus
        });
        
        // Set values in the modal
        document.getElementById('statusCourseId').value = courseId;
        document.getElementById('statusCourseStatus').value = courseStatus;
        document.getElementById('statusApprovalStatus').value = approvalStatus;
        
        document.getElementById('status-course-image').src = row.getAttribute('data-thumbnail');
        document.getElementById('status-course-title').textContent = row.getAttribute('data-title');
        document.getElementById('status-course-department').textContent = row.getAttribute('data-department-name');
        document.getElementById('status-course-instructor').textContent = `Instructor: ${row.getAttribute('data-instructor-names')}`;
        
        // Set the appropriate status message
        const statusTitle = document.getElementById('status-alert-title');
        const statusMessage = document.getElementById('status-alert-message');
        const statusAlert = document.getElementById('status-message');
        const modalTitle = document.getElementById('status-change-title');
        const publishOption = document.getElementById('publish-option');
        
        // Hide publish option by default
        publishOption.classList.add('d-none');
        
        if (courseStatus) {
          if (courseStatus === 'Published') {
            modalTitle.textContent = 'Publish Course';
            statusTitle.textContent = 'Publish Course';
            statusMessage.textContent = 'You are about to publish this course, making it visible to students.';
            statusAlert.className = 'alert alert-success';
            document.getElementById('confirmStatusBtn').textContent = 'Publish';
          } else {
            modalTitle.textContent = 'Unpublish Course';
            statusTitle.textContent = 'Unpublish Course';
            statusMessage.textContent = 'You are about to unpublish this course, hiding it from students.';
            statusAlert.className = 'alert alert-secondary';
            document.getElementById('confirmStatusBtn').textContent = 'Unpublish';
          }
        } else if (approvalStatus) {
          if (approvalStatus === 'approved') {
            modalTitle.textContent = 'Approve Course';
            statusTitle.textContent = 'Approve Course';
            statusMessage.textContent = 'You are about to approve this course, allowing it to be published.';
            statusAlert.className = 'alert alert-success';
            document.getElementById('confirmStatusBtn').textContent = 'Approve';
            
            // Show publish option when approving
            publishOption.classList.remove('d-none');
          } else if (approvalStatus === 'rejected') {
            modalTitle.textContent = 'Reject Course';
            statusTitle.textContent = 'Reject Course';
            statusMessage.textContent = 'You are about to reject this course. The instructor will need to make significant changes before resubmitting.';
            statusAlert.className = 'alert alert-danger';
            document.getElementById('confirmStatusBtn').textContent = 'Reject';
          } else if (approvalStatus === 'revisions_requested') {
            modalTitle.textContent = 'Request Revisions';
            statusTitle.textContent = 'Request Revisions';
            statusMessage.textContent = 'You are about to request revisions for this course. The instructor will need to make the requested changes and resubmit.';
            statusAlert.className = 'alert alert-info';
            document.getElementById('confirmStatusBtn').textContent = 'Request Revisions';
          }
        }
        
        const modal = new bootstrap.Modal(document.getElementById('courseStatusModal'));
        modal.show();
      });
    });
    
    // Handle the publish after approval checkbox
    document.getElementById('publishAfterApproval').addEventListener('change', function() {
      if (this.checked) {
        document.getElementById('statusCourseStatus').value = 'Published';
      } else {
        document.getElementById('statusCourseStatus').value = '';
      }
    });

    // **Delete Course**
    document.querySelectorAll('.delete-course').forEach(btn => {
      btn.addEventListener('click', function() {
        const row = this.closest('tr');
        const courseId = row.getAttribute('data-id');
        const courseTitle = row.getAttribute('data-title');
        const enrolledStudents = row.getAttribute('data-students');
        
        document.getElementById('deleteCourseId').value = courseId;
        document.getElementById('delete-course-title').textContent = courseTitle;
        document.getElementById('delete-course-students').textContent = enrolledStudents;
      });
    });

    // **AJAX Form Submission Handler**
    function handleFormSubmit(formId, url) {
      const form = document.getElementById(formId);
      if (!form) return;

      form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Debug: Log form values before submission
        const formData = new FormData(form);
        console.log('Submitting form values:');
        for (let [key, value] of formData.entries()) {
          console.log(`${key}: ${value}`);
        }
        
        showOverlay('Processing...');

        fetch(url, {
            method: 'POST',
            body: formData
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

    handleFormSubmit('courseStatusForm', '../backend/admin/update-course-status.php');
    handleFormSubmit('deleteCourseForm', '../backend/admin/delete-course.php');

    // **Export Functionality**
    document.getElementById('exportBtn').addEventListener('click', function() {
      showOverlay('Generating export...');
      const table = document.getElementById('coursesTable');
      const rows = table.querySelectorAll('tbody tr:not([style*="display: none"])');

      let csvContent = "data:text/csv;charset=utf-8,Course Title,Status,Approval Status,Department,Instructors,Enrolled Students,Created Date\n";
      rows.forEach(row => {
        const title = row.getAttribute('data-title').replace(/"/g, '""');
        const status = row.getAttribute('data-status');
        const approvalStatus = ucfirst(row.getAttribute('data-approval').replace(/_/g, ' '));
        const department = row.getAttribute('data-department-name').replace(/"/g, '""');
        const instructors = row.getAttribute('data-instructor-names').replace(/"/g, '""');
        const students = row.getAttribute('data-students');
        const created = row.getAttribute('data-created');
        
        csvContent += `"${title}","${status}","${approvalStatus}","${department}","${instructors}","${students}","${created}"\n`;
      });

      const encodedUri = encodeURI(csvContent);
      const link = document.createElement("a");
      link.setAttribute("href", encodedUri);
      link.setAttribute("download", `courses_export_${new Date().toISOString().split('T')[0]}.csv`);
      document.body.appendChild(link);

      setTimeout(() => {
        link.click();
        document.body.removeChild(link);
        removeOverlay();
        showToast('Courses list exported successfully', 'success');
      }, 1000);
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