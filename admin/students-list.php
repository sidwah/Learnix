<?php
// Authentication check
require_once '../backend/auth/admin/admin-auth-check.php';

// Set page title
$pageTitle = "Students - Admin | Learnix";

include_once '../includes/admin/header.php';

// <!-- Menu -->
include_once '../includes/admin/sidebar.php';
// <!-- / Menu -->

// <!-- Navbar -->
include_once '../includes/admin/navbar.php';
// <!-- / Navbar -->

// Get data from database
require_once '../backend/config.php';

// Fetch all students with their details
$query = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.profile_pic, u.status, u.created_at,
                 COUNT(DISTINCT e.enrollment_id) AS enrolled_courses,
                 COUNT(DISTINCT c.certificate_id) AS certificates_count,
                 AVG(IFNULL(e.completion_percentage, 0)) AS avg_completion
          FROM users u
          LEFT JOIN enrollments e ON u.user_id = e.user_id AND e.deleted_at IS NULL
          LEFT JOIN certificates c ON e.enrollment_id = c.enrollment_id AND c.deleted_at IS NULL
          WHERE u.role = 'student' AND u.deleted_at IS NULL
          GROUP BY u.user_id
          ORDER BY u.first_name, u.last_name";

$result = mysqli_query($conn, $query);

// Count statistics
$totalStudents = 0;
$activeCounts = 0;
$inactiveCounts = 0;
$suspendedCounts = 0;

$students = [];
if ($result && mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
    $students[] = $row;
    $totalStudents++;

    // Count by status
    if ($row['status'] == 'active') $activeCounts++;
    if ($row['status'] == 'inactive') $inactiveCounts++;
    if ($row['status'] == 'suspended') $suspendedCounts++;
  }
}

// Get course details for each student
foreach ($students as $key => $student) {
  $coursesQuery = "SELECT c.course_id, c.title, c.thumbnail 
                   FROM courses c
                   JOIN enrollments e ON c.course_id = e.course_id
                   WHERE e.user_id = ? AND e.deleted_at IS NULL
                   AND c.deleted_at IS NULL
                   LIMIT 5";

  $stmt = $conn->prepare($coursesQuery);
  $stmt->bind_param("i", $student['user_id']);
  $stmt->execute();
  $coursesResult = $stmt->get_result();

  $courses = [];
  if ($coursesResult && mysqli_num_rows($coursesResult) > 0) {
    while ($course = mysqli_fetch_assoc($coursesResult)) {
      $courses[] = $course;
    }
  }

  $students[$key]['courses'] = $courses;
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
    <span class="text-muted fw-light">Admin /</span> Students
  </h4>

  <!-- Cards -->
  <div class="row mb-4">
    <div class="col-md-4 col-sm-6 mb-3">
      <div class="card status-card" data-status="active">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="card-info">
              <p class="card-text">Active Students</p>
              <div class="d-flex align-items-end mb-2">
                <h4 class="card-title mb-0 me-2"><?php echo $activeCounts; ?></h4>
              </div>
            </div>
            <div class="card-icon">
              <span class="badge bg-label-success rounded p-2">
                <i class="bx bx-user-check bx-md"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-4 col-sm-6 mb-3">
      <div class="card status-card" data-status="inactive">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="card-info">
              <p class="card-text">Inactive Students</p>
              <div class="d-flex align-items-end mb-2">
                <h4 class="card-title mb-0 me-2"><?php echo $inactiveCounts; ?></h4>
              </div>
            </div>
            <div class="card-icon">
              <span class="badge bg-label-info rounded p-2">
                <i class="bx bx-user-x bx-md"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-4 col-sm-6 mb-3">
      <div class="card status-card" data-status="suspended">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="card-info">
              <p class="card-text">Suspended Students</p>
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

  <!-- Students List -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Students List</h5>
      <div class="d-flex align-items-center">
        <?php if ($totalStudents > 0): ?>
          <div class="me-3">
            <div class="input-group input-group-sm">
              <span class="input-group-text"><i class="bx bx-search"></i></span>
              <input type="text" class="form-control" id="studentSearch" placeholder="Search students..." aria-label="Search">
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
              <button type="button" class="btn btn-outline-info btn-sm filter-status" data-status="inactive">
                Inactive
              </button>
              <button type="button" class="btn btn-outline-danger btn-sm filter-status" data-status="suspended">
                Suspended
              </button>
            </div>
          </div>
        <?php endif; ?>
        <?php if ($totalStudents > 0): ?>
          <button type="button" class="btn btn-outline-secondary btn-sm" id="exportBtn">
            <i class="bx bx-export me-1"></i> Export
          </button>
        <?php endif; ?>
      </div>
    </div>

    <?php if (count($students) > 0): ?>
      <div class="table-responsive text-nowrap">
        <table class="table" id="studentsTable">
          <thead>
            <tr>
              <th>Student</th>
              <th>Status</th>
              <th>Enrolled Courses</th>
              <th>Completion</th>
              <th>Certificates</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            <?php foreach ($students as $student): ?>
              <tr class="student-row"
                data-id="<?php echo $student['user_id']; ?>"
                data-status="<?php echo $student['status']; ?>"
                data-name="<?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>"
                data-first-name="<?php echo htmlspecialchars($student['first_name']); ?>"
                data-last-name="<?php echo htmlspecialchars($student['last_name']); ?>"
                data-email="<?php echo htmlspecialchars($student['email']); ?>"
                data-profile-pic="<?php echo !empty($student['profile_pic']) ? '../uploads/profile/' . $student['profile_pic'] : '../assets/img/avatars/1.png'; ?>"
                data-created="<?php echo date('F d, Y', strtotime($student['created_at'])); ?>"
                data-courses="<?php echo count($student['courses']); ?>"
                data-certificates="<?php echo $student['certificates_count']; ?>">
                <td>
                  <div class="d-flex justify-content-start align-items-center">
                    <div class="avatar-wrapper">
                      <div class="avatar me-2">
                        <img src="<?php echo !empty($student['profile_pic']) ? '../uploads/profile/' . $student['profile_pic'] : '../assets/img/avatars/1.png'; ?>" alt="Student Avatar" class="rounded-circle">
                      </div>
                    </div>
                    <div class="d-flex flex-column">
                      <span class="fw-semibold"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></span>
                      <small class="text-muted"><?php echo htmlspecialchars($student['email']); ?></small>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="badge bg-label-<?php 
                                              echo $student['status'] == 'active' ? 'success' : ($student['status'] == 'inactive' ? 'info' : 'danger'); ?>">
                    <?php echo ucfirst($student['status']); ?>
                  </span>
                </td>
                <td>
                  <div class="d-flex align-items-center">
                    <span class="fw-semibold me-2"><?php echo count($student['courses']); ?></span>
                    <?php if (count($student['courses']) > 0): ?>
                      <div class="course-thumbnails">
                        <?php
                          $displayCourses = array_slice($student['courses'], 0, 3);
                          foreach ($displayCourses as $course):
                        ?>
                          <img src="<?php echo !empty($course['thumbnail']) ? '../uploads/thumbnails/' . $course['thumbnail'] : '../uploads/thumbnails/default.jpg'; ?>"
                            alt="<?php echo htmlspecialchars($course['title']); ?>"
                            title="<?php echo htmlspecialchars($course['title']); ?>"
                            class="rounded-circle me-1" style="width:24px; height:24px; object-fit:cover;">
                        <?php endforeach; ?>

                        <?php if (count($student['courses']) > 3): ?>
                          <span class="badge bg-light text-dark">+<?php echo (count($student['courses']) - 3); ?></span>
                        <?php endif; ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </td>
                <td>
                  <?php 
                    $completion = round($student['avg_completion'], 0);
                    $badgeClass = 'bg-label-danger';
                    if ($completion >= 75) {
                      $badgeClass = 'bg-label-success';
                    } else if ($completion >= 50) {
                      $badgeClass = 'bg-label-info';
                    } else if ($completion >= 25) {
                      $badgeClass = 'bg-label-warning';
                    }
                  ?>
                  <div class="d-flex align-items-center">
                    <div class="progress w-100 me-3" style="height: 8px;">
                      <div class="progress-bar" role="progressbar" style="width: <?php echo $completion; ?>%;" 
                          aria-valuenow="<?php echo $completion; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <small class="fw-semibold"><?php echo $completion; ?>%</small>
                  </div>
                </td>
                <td>
                  <span class="badge <?php echo $badgeClass; ?>"><?php echo $student['certificates_count']; ?></span>
                </td>
                <td class="text-center">
                  <div class="d-inline-block">
                    <button type="button" class="btn btn-sm btn-icon btn-outline-primary rounded-pill btn-icon view-student" data-bs-toggle="modal" data-bs-target="#viewStudentModal" title="View Details">
                      <i class="bx bx-show-alt"></i>
                    </button>
                    <?php if ($student['status'] == 'active'): ?>
                     <button type="button" class="btn btn-sm btn-icon btn-outline-warning rounded-pill btn-icon change-status" data-status="inactive" title="Deactivate">
                       <i class="bx bx-pause"></i>
                     </button>
                   <?php endif; ?>

                   <?php if ($student['status'] == 'inactive'): ?>
                     <button type="button" class="btn btn-sm btn-icon btn-outline-success rounded-pill btn-icon change-status" data-status="active" title="Activate">
                       <i class="bx bx-play"></i>
                     </button>
                   <?php endif; ?>

                   <?php if ($student['status'] == 'suspended'): ?>
                     <button type="button" class="btn btn-sm btn-icon btn-outline-success rounded-pill btn-icon change-status" data-status="active" title="Restore Access">
                       <i class="bx bx-revision"></i>
                     </button>
                   <?php endif; ?>

                   <?php if ($student['status'] != 'suspended'): ?>
                     <button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-pill btn-icon suspend-student" title="Suspend">
                       <i class="bx bx-block"></i>
                     </button>
                   <?php endif; ?>

                   <button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-pill btn-icon delete-student" data-bs-toggle="modal" data-bs-target="#deleteStudentModal" title="Delete">
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
           <h5 class="mb-2">No Students Found</h5>
           <p class="mb-0 text-muted">No students match your current filters.</p>
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
             Showing <span id="showing-start">1</span> to <span id="showing-end"><?php echo min(10, count($students)); ?></span> of <span id="total-entries"><?php echo $totalStudents; ?></span> entries
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
               <?php if ($totalStudents > 10): ?>
                 <li class="paginate_button page-item">
                   <a href="#" class="page-link">2</a>
                 </li>
               <?php endif; ?>
               <?php if ($totalStudents > 20): ?>
                 <li class="paginate_button page-item">
                   <a href="#" class="page-link">3</a>
                 </li>
               <?php endif; ?>
               <li class="paginate_button page-item next<?php echo ($totalStudents <= 10) ? ' disabled' : ''; ?>" id="pagination-next">
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
         <h4 class="mb-2">No Students Found</h4>
         <p class="mb-4 text-muted">There are no students registered on the platform yet.</p>
       </div>
     </div>
   <?php endif; ?>
 </div>

 <!-- View Student Modal -->
 <div class="modal fade" id="viewStudentModal" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog modal-lg" role="document">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title">Student Details</h5>
         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
       </div>
       <div class="modal-body">
         <div class="row">
           <div class="col-md-4 text-center mb-4 mb-md-0">
             <img src="../assets/img/avatars/1.png" alt="Student Avatar" class="rounded-circle border" width="150" height="150" id="view-student-image">
             <div class="mt-3">
               <h5 id="view-student-name" class="mb-1"></h5>
               <p class="text-muted small" id="view-student-email"></p>
               <span class="badge bg-label-success" id="view-student-status"></span>
             </div>
           </div>
           <div class="col-md-8">
             <div class="row mb-3">
               <div class="col-md-6">
                 <h6 class="fw-semibold">Joined</h6>
                 <p id="view-student-joined" class="text-muted"></p>
               </div>
               <div class="col-md-6">
                 <h6 class="fw-semibold">Enrolled Courses</h6>
                 <p id="view-student-courses" class="text-muted"></p>
               </div>
             </div>

             <div class="row mb-3">
               <div class="col-md-6">
                 <h6 class="fw-semibold">Average Completion</h6>
                 <div class="d-flex align-items-center">
                   <div class="progress w-75 me-2" style="height: 8px;">
                     <div class="progress-bar" role="progressbar" style="width: 0%;" 
                         aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" id="view-student-completion-bar"></div>
                   </div>
                   <span id="view-student-completion">0%</span>
                 </div>
               </div>
               <div class="col-md-6">
                 <h6 class="fw-semibold">Certificates Earned</h6>
                 <p id="view-student-certificates" class="text-muted"></p>
               </div>
             </div>

             <h6 class="fw-semibold mb-3">Enrolled Courses</h6>
             <div id="view-student-course-list" class="mb-3">
               <!-- Course list will be inserted here -->
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

 <!-- Change Status Modal -->
 <div class="modal fade" id="changeStatusModal" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered" role="document">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title">Change Student Status</h5>
         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
       </div>
       <form id="changeStatusForm" action="../backend/admin/update-student-status.php" method="POST">
         <div class="modal-body">
           <input type="hidden" id="statusStudentId" name="user_id" value="">
           <input type="hidden" id="statusAction" name="status" value="">
           <input type="hidden" id="statusUserEmail" name="user_email" value="">
           <input type="hidden" id="statusUserName" name="user_name" value="">

           <div class="text-center mb-4">
             <div class="avatar avatar-lg">
               <img src="../assets/img/avatars/1.png" alt="Student Avatar" class="rounded-circle" id="status-student-image">
             </div>
             <h5 class="mt-2 mb-0" id="status-student-name"></h5>
             <p class="text-muted small mb-2" id="status-student-email"></p>
           </div>

           <div id="activate-message" class="d-none">
             <div class="alert alert-success">
               <h6 class="alert-heading mb-1">Activate Student</h6>
               <p class="mb-0">Are you sure you want to activate this student? This will allow them to log in and access student features.</p>
             </div>
           </div>

           <div id="deactivate-message" class="d-none">
             <div class="alert alert-warning">
               <h6 class="alert-heading mb-1">Deactivate Student</h6>
               <p class="mb-0">Are you sure you want to deactivate this student? They will not be able to access their account until reactivated.</p>
             </div>
           </div>

           <div id="suspend-message" class="d-none">
             <div class="alert alert-danger">
               <h6 class="alert-heading mb-1">Suspend Student</h6>
               <p class="mb-0">Are you sure you want to suspend this student? This will prevent them from accessing any student features and notify them of the suspension.</p>
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

 <!-- Delete Student Modal -->
 <div class="modal fade" id="deleteStudentModal" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered" role="document">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title">Delete Student</h5>
         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
       </div>
       <form id="deleteStudentForm" action="../backend/admin/delete-student.php" method="POST">
         <div class="modal-body">
           <input type="hidden" id="deleteStudentId" name="user_id" value="">

           <div class="text-center mb-4">
             <i class="bx bx-error-circle text-danger" style="font-size: 6rem;"></i>
           </div>

           <p class="mb-0 text-center">Are you sure you want to delete <strong id="delete-student-name"></strong>?</p>

           <div id="delete-warning" class="alert alert-warning mt-3">
             <div class="d-flex">
               <i class="bx bx-error me-2 mt-1"></i>
               <div>
                 <p class="mb-0">This action will remove this student from all courses, delete their progress, and cannot be undone.</p>
                 <p class="mb-0 mt-2">Consider <strong>deactivating</strong> the student instead.</p>
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
     const visibleRows = Array.from(document.querySelectorAll('.student-row'))
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
     const visibleRows = document.querySelectorAll('.student-row:not([style*="display: none"])');
     const totalPages = Math.ceil(visibleRows.length / ITEMS_PER_PAGE);
     if (currentPage < totalPages) {
       currentPage++;
       setupPagination();
     }
   });

   // **Filter Functionality**
   let currentStatusFilter = 'all';

   document.getElementById('studentSearch').addEventListener('keyup', filterStudents);

   document.querySelectorAll('.filter-status').forEach(btn => {
     btn.addEventListener('click', function() {
       document.querySelectorAll('.filter-status').forEach(b => b.classList.remove('active'));
       this.classList.add('active');
       currentStatusFilter = this.getAttribute('data-status');
       filterStudents();
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
       filterStudents();
     });
   });

   document.getElementById('clearFiltersBtn').addEventListener('click', function() {
     document.getElementById('studentSearch').value = '';
     currentStatusFilter = 'all';
     document.querySelectorAll('.filter-status').forEach(btn => {
       btn.classList.remove('active');
       if (btn.getAttribute('data-status') === 'all') btn.classList.add('active');
     });
     filterStudents();
   });

   function filterStudents() {
     const searchTerm = document.getElementById('studentSearch').value.toLowerCase();
     const rows = document.querySelectorAll('.student-row');
     const emptySearchResults = document.getElementById('empty-search-results');
     let visibleCount = 0;

     rows.forEach(row => {
       const name = row.getAttribute('data-name').toLowerCase();
       const email = row.getAttribute('data-email').toLowerCase();
       const status = row.getAttribute('data-status');

       const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
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

   // **View Student Details**
   document.querySelectorAll('.view-student').forEach(btn => {
     btn.addEventListener('click', function() {
       const row = this.closest('tr');
       const viewModal = document.getElementById('viewStudentModal');

       document.getElementById('view-student-image').src = row.getAttribute('data-profile-pic');
       document.getElementById('view-student-name').textContent = row.getAttribute('data-name');
       document.getElementById('view-student-email').textContent = row.getAttribute('data-email');

       const status = row.getAttribute('data-status');
       const statusBadgeClass = status === 'active' ? 'bg-label-success' :
         status === 'inactive' ? 'bg-label-info' : 'bg-label-danger';
       document.getElementById('view-student-status').className = `badge ${statusBadgeClass}`;
       document.getElementById('view-student-status').textContent = ucfirst(status);

       document.getElementById('view-student-joined').textContent = row.getAttribute('data-created');
       document.getElementById('view-student-courses').textContent = row.getAttribute('data-courses');
       document.getElementById('view-student-certificates').textContent = row.getAttribute('data-certificates');

       // Set completion percentage
       const completionCell = row.querySelector('td:nth-child(4)');
       const completionText = completionCell.querySelector('small').textContent;
       document.getElementById('view-student-completion').textContent = completionText;
       document.getElementById('view-student-completion-bar').style.width = completionText;
       
       // Update course list
       document.getElementById('view-student-course-list').innerHTML = '';
       const coursesCount = parseInt(row.getAttribute('data-courses'));
       if (coursesCount > 0) {
         const courseElements = Array.from(row.querySelectorAll('.course-thumbnails img'));
         let courseListHTML = '<div class="d-flex flex-wrap">';
         
         courseElements.forEach(img => {
           courseListHTML += `
             <div class="me-3 mb-3 text-center">
               <img src="${img.src}" class="rounded mb-2" style="width:64px; height:64px; object-fit:cover;">
               <div class="small text-muted">${img.title}</div>
             </div>
           `;
         });
         
         courseListHTML += '</div>';
         document.getElementById('view-student-course-list').innerHTML = courseListHTML;
       } else {
         document.getElementById('view-student-course-list').innerHTML = `
           <div class="alert alert-light w-100 mb-0">
             <div class="d-flex">
               <i class="bx bx-info-circle me-2 mt-1"></i>
               <div>This student is not enrolled in any courses.</div>
             </div>
           </div>
         `;
       }
     });
   });

   // **Change Status Buttons**
   document.querySelectorAll('.change-status').forEach(btn => {
     btn.addEventListener('click', function() {
       const row = this.closest('tr');
       const studentId = row.getAttribute('data-id');
       const newStatus = this.getAttribute('data-status');

       document.getElementById('statusStudentId').value = studentId;
       document.getElementById('statusAction').value = newStatus;
       document.getElementById('statusUserEmail').value = row.getAttribute('data-email');
       document.getElementById('statusUserName').value = row.getAttribute('data-name');

       document.getElementById('status-student-image').src = row.getAttribute('data-profile-pic');
       document.getElementById('status-student-name').textContent = row.getAttribute('data-name');
       document.getElementById('status-student-email').textContent = row.getAttribute('data-email');

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

   // **Suspend Student**
   document.querySelectorAll('.suspend-student').forEach(btn => {
     btn.addEventListener('click', function() {
       const row = this.closest('tr');
       const studentId = row.getAttribute('data-id');

       document.getElementById('statusStudentId').value = studentId;
       document.getElementById('statusAction').value = 'suspended';
       document.getElementById('statusUserEmail').value = row.getAttribute('data-email');
       document.getElementById('statusUserName').value = row.getAttribute('data-name');

       document.getElementById('status-student-image').src = row.getAttribute('data-profile-pic');
       document.getElementById('status-student-name').textContent = row.getAttribute('data-name');
       document.getElementById('status-student-email').textContent = row.getAttribute('data-email');

       document.getElementById('activate-message').classList.add('d-none');
       document.getElementById('deactivate-message').classList.add('d-none');
       document.getElementById('suspend-message').classList.remove('d-none');

       const modal = new bootstrap.Modal(document.getElementById('changeStatusModal'));
       modal.show();
     });
   });

   // **Delete Student**
   document.querySelectorAll('.delete-student').forEach(btn => {
     btn.addEventListener('click', function() {
       const row = this.closest('tr');
       const studentId = row.getAttribute('data-id');

       document.getElementById('deleteStudentId').value = studentId;
       document.getElementById('delete-student-name').textContent = row.getAttribute('data-name');
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

   handleFormSubmit('changeStatusForm', '../backend/admin/update-student-status.php');
   handleFormSubmit('deleteStudentForm', '../backend/admin/delete-student.php');

   // **Export Functionality**
   document.getElementById('exportBtn').addEventListener('click', function() {
     showOverlay('Generating export...');
     const table = document.getElementById('studentsTable');
     const rows = table.querySelectorAll('tbody tr:not([style*="display: none"])');

     let csvContent = "data:text/csv;charset=utf-8,Name,Email,Status,Enrolled Courses,Completion,Certificates\n";
     rows.forEach(row => {
       const name = row.getAttribute('data-name').replace(/"/g, '""');
       const email = row.getAttribute('data-email').replace(/"/g, '""');
       const status = row.getAttribute('data-status');
       const courses = row.getAttribute('data-courses');
       const completion = row.querySelector('td:nth-child(4) small').textContent;
       const certificates = row.getAttribute('data-certificates');
       csvContent += `"${name}","${email}","${ucfirst(status)}","${courses}","${completion}","${certificates}"\n`;
     });

     const encodedUri = encodeURI(csvContent);
     const link = document.createElement("a");
     link.setAttribute("href", encodedUri);
     link.setAttribute("download", `students_export_${new Date().toISOString().split('T')[0]}.csv`);
     document.body.appendChild(link);

     setTimeout(() => {
       link.click();
       document.body.removeChild(link);
       removeOverlay();
       showToast('Students list exported successfully', 'success');
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