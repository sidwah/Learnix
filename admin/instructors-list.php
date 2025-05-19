<?php
// Authentication check
require_once '../backend/auth/admin/admin-auth-check.php';

// Set page title
$pageTitle = "Instructors List - Admin | Learnix";

// Simulate fetching instructors (replace with actual query)
$instructors = [
    [
        'id' => 1,
        'user_id' => 101,
        'first_name' => 'Alice',
        'last_name' => 'Smith',
        'email' => 'alice.smith@university.edu',
        'avatar' => 'alice-smith.jpg',
        'bio' => 'Expert in Python and Data Science with 10 years of teaching experience.',
        'status' => 'active',
        'courses' => [
            ['id' => 1, 'title' => 'Python Programming', 'thumbnail' => 'python.jpg'],
            ['id' => 2, 'title' => 'Data Science 101', 'thumbnail' => 'data-science.jpg'],
            ['id' => 3, 'title' => 'Machine Learning', 'thumbnail' => 'ml.jpg'],
            ['id' => 4, 'title' => 'AI Basics', 'thumbnail' => 'ai.jpg'],
            ['id' => 5, 'title' => 'Big Data', 'thumbnail' => 'big-data.jpg']
        ],
        'department' => 'Computer Science',
        'rating' => 4.8,
        'total_students' => 500,
        'created_at' => '2024-06-10',
        'updated_at' => '2025-04-20'
    ],
    [
        'id' => 2,
        'user_id' => 102,
        'first_name' => 'Bob',
        'last_name' => 'Johnson',
        'email' => 'bob.johnson@university.edu',
        'avatar' => 'bob-johnson.jpg',
        'bio' => 'Specializes in network security and cybersecurity.',
        'status' => 'active',
        'courses' => [
            ['id' => 3, 'title' => 'Network Security', 'thumbnail' => 'network.jpg']
        ],
        'department' => 'Cybersecurity',
        'rating' => 4.5,
        'total_students' => 300,
        'created_at' => '2024-07-15',
        'updated_at' => '2025-04-18'
    ],
    [
        'id' => 3,
        'user_id' => 103,
        'first_name' => 'Emma',
        'last_name' => 'Brown',
        'email' => 'emma.brown@university.edu',
        'avatar' => 'emma-brown.jpg',
        'bio' => 'New instructor awaiting course assignment.',
        'status' => 'pending',
        'courses' => [],
        'department' => 'Unassigned',
        'rating' => 0,
        'total_students' => 0,
        'created_at' => '2025-03-01',
        'updated_at' => '2025-04-19'
    ],
    [
        'id' => 4,
        'user_id' => 104,
        'first_name' => 'James',
        'last_name' => 'Taylor',
        'email' => 'james.taylor@university.edu',
        'avatar' => 'james-taylor.jpg',
        'bio' => 'Web design expert with industry experience.',
        'status' => 'inactive',
        'courses' => [
            ['id' => 5, 'title' => 'Web Design Fundamentals', 'thumbnail' => 'web-design.jpg']
        ],
        'department' => 'Web Development',
        'rating' => 4.2,
        'total_students' => 120,
        'created_at' => '2024-08-20',
        'updated_at' => '2025-04-17'
    ]
];

// Calculate status counts for dashboard cards
$statusCounts = [
    'active' => 0,
    'pending' => 0,
    'inactive' => 0,
    'suspended' => 0
];

foreach ($instructors as $instructor) {
    if (isset($statusCounts[$instructor['status']])) {
        $statusCounts[$instructor['status']]++;
    }
}

include_once '../includes/admin/header.php';
include_once '../includes/admin/sidebar.php';
include_once '../includes/admin/navbar.php';
?>

<!-- Custom CSS -->
<style>
  /* Reuse styles from course UI */
  .table-responsive .dropdown-menu {
    position: absolute;
    right: 0;
    left: auto;
    transform: none !important;
    top: 100%;
    z-index: 1000;
  }

  .status-card {
    transition: transform 0.3s ease;
    cursor: pointer;
  }

  .status-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  }

  .status-card .card-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
  }

  .status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 5px;
  }

  .filter-badge.active {
    background-color: #696cff !important;
    color: #fff !important;
  }

  .instructor-avatar {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 50%;
  }

  .instructor-info {
    display: flex;
    align-items: center;
  }

  .course-thumbnails img {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 4px;
    margin-right: 5px;
  }

  .course-thumbnails .more-courses {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background-color: #f1f1f1;
    border-radius: 4px;
    font-size: 0.85rem;
    color: #697a8d;
  }

  .profile-bio {
    max-height: 100px;
    overflow-y: auto;
  }

  .profile-details .list-group-item {
    padding: 0.5rem 0;
  }

  .courses-list img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 4px;
  }

  .courses-list .more-courses {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    background-color: #f1f1f1;
    border-radius: 4px;
    font-size: 1rem;
    color: #697a8d;
  }
</style>

<!-- Content wrapper -->
<div class="content-wrapper">
  <!-- Content -->
  <div class="container-xxl flex-grow-1 container-p-y">
    <!-- Toast Notifications -->
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
    <!-- /Toast Notifications -->

    <h4 class="fw-bold py-3 mb-4">
      <span class="text-muted fw-light">Administration /</span>
      Instructors List
    </h4>

    <!-- Status Cards -->
    <div class="row mb-4">
      <div class="col-lg col-md-6 col-sm-6 mb-3">
        <div class="card status-card h-100" data-filter="active">
          <div class="card-body d-flex align-items-center">
            <div class="card-icon bg-label-success me-3">
              <i class="bx bx-check-circle fs-3"></i>
            </div>
            <div>
              <h5 class="card-title mb-0">Active</h5>
              <p class="card-text fs-3 fw-semibold mb-0"><?php echo $statusCounts['active']; ?></p>
              <small class="text-muted">Teaching instructors</small>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg col-md-6 col-sm-6 mb-3">
        <div class="card status-card h-100" data-filter="pending">
          <div class="card-body d-flex align-items-center">
            <div class="card-icon bg-label-warning me-3">
              <i class="bx bx-time fs-3"></i>
            </div>
            <div>
              <h5 class="card-title mb-0">Pending</h5>
              <p class="card-text fs-3 fw-semibold mb-0"><?php echo $statusCounts['pending']; ?></p>
              <small class="text-muted">Awaiting approval</small>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg col-md-6 col-sm-6 mb-3">
        <div class="card status-card h-100" data-filter="inactive">
          <div class="card-body d-flex align-items-center">
            <div class="card-icon bg-label-info me-3">
              <i class="bx bx-pause-circle fs-3"></i>
            </div>
            <div>
              <h5 class="card-title mb-0">Inactive</h5>
              <p class="card-text fs-3 fw-semibold mb-0"><?php echo $statusCounts['inactive']; ?></p>
              <small class="text-muted">Not teaching</small>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg col-md-6 col-sm-6 mb-3">
        <div class="card status-card h-100" data-filter="suspended">
          <div class="card-body d-flex align-items-center">
            <div class="card-icon bg-label-danger me-3">
              <i class="bx bx-block fs-3"></i>
            </div>
            <div>
              <h5 class="card-title mb-0">Suspended</h5>
              <p class="card-text fs-3 fw-semibold mb-0"><?php echo $statusCounts['suspended']; ?></p>
              <small class="text-muted">Restricted access</small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Instructors Table -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Instructors List</h5>
            <div class="d-flex align-items-center">
              <div class="me-3">
                <div class="input-group input-group-sm">
                  <span class="input-group-text"><i class="bx bx-search"></i></span>
                  <input type="text" class="form-control" id="instructorSearch" placeholder="Search instructors..." aria-label="Search">
                </div>
              </div>
              <div class="me-3">
                <div class="btn-group" role="group" aria-label="Filter instructors">
                  <button type="button" class="btn btn-outline-secondary btn-sm filter-badge active" data-filter="all">All</button>
                  <button type="button" class="btn btn-outline-success btn-sm filter-badge" data-filter="active">Active</button>
                  <button type="button" class="btn btn-outline-warning btn-sm filter-badge" data-filter="pending">Pending</button>
                  <button type="button" class="btn btn-outline-info btn-sm filter-badge" data-filter="inactive">Inactive</button>
                  <button type="button" class="btn btn-outline-danger btn-sm filter-badge" data-filter="suspended">Suspended</button>
                </div>
              </div>
              <a href="add-instructor.php" class="btn btn-primary btn-sm me-2">
                <i class="bx bx-plus me-1"></i> Add Instructor
              </a>
              <button type="button" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-export me-1"></i> Export
              </button>
            </div>
          </div>
          <div class="table-responsive text-nowrap">
            <table class="table" id="instructorsTable">
              <thead>
                <tr>
                  <th>Instructor</th>
                  <th>Status</th>
                  <th>Courses</th>
                  <th>Department</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody class="table-border-bottom-0">
                <?php 
                if (empty($instructors)) {
                ?>
                <tr>
                  <td colspan="5" class="text-center py-3">
                    <div class="d-flex flex-column align-items-center py-4">
                      <i class="bx bx-user mb-2" style="font-size: 3rem; color: #d9dee3;"></i>
                      <h6 class="mb-1">No instructors found</h6>
                      <p class="text-muted mb-0">Start adding instructors to your platform</p>
                    </div>
                  </td>
                </tr>
                <?php
                } else {
                  foreach ($instructors as $instructor) {
                    $status_badge = [
                      'active' => 'bg-label-success',
                      'pending' => 'bg-label-warning',
                      'inactive' => 'bg-label-info',
                      'suspended' => 'bg-label-danger'
                    ];
                    $status_dot = [
                      'active' => 'bg-success',
                      'pending' => 'bg-warning',
                      'inactive' => 'bg-info',
                      'suspended' => 'bg-danger'
                    ];
                    $status_label = ucfirst($instructor['status']);
                ?>
                  <tr data-status="<?php echo $instructor['status']; ?>">
                    <td>
                      <div class="instructor-info">
                        <img src="../Uploads/instructor-profile/<?php echo htmlspecialchars($instructor['avatar']); ?>" 
                             alt="<?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?>" 
                             class="instructor-avatar me-3" />
                        <div>
                          <strong><?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?></strong>
                          <div class="small text-muted"><?php echo htmlspecialchars($instructor['email']); ?></div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="badge <?php echo $status_badge[$instructor['status']]; ?>">
                        <span class="status-dot <?php echo $status_dot[$instructor['status']]; ?>"></span>
                        <?php echo $status_label; ?>
                      </span>
                    </td>
                    <td>
                      <div class="course-thumbnails">
                        <?php 
                        $max_thumbnails = 4;
                        $course_count = count($instructor['courses']);
                        $display_courses = array_slice($instructor['courses'], 0, $max_thumbnails);
                        foreach ($display_courses as $course) { 
                        ?>
                          <img src="../Uploads/course-thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>" 
                               alt="<?php echo htmlspecialchars($course['title']); ?>" 
                               title="<?php echo htmlspecialchars($course['title']); ?>" 
                               data-bs-toggle="tooltip" />
                        <?php 
                        }
                        if ($course_count > $max_thumbnails) {
                        ?>
                          <span class="more-courses" data-bs-toggle="tooltip" title="<?php echo ($course_count - $max_thumbnails); ?> more courses">
                            +<?php echo ($course_count - $max_thumbnails); ?>
                          </span>
                        <?php } ?>
                        <?php if ($course_count === 0) { ?>
                          <span class="text-muted">No courses assigned</span>
                        <?php } ?>
                      </div>
                    </td>
                    <td><?php echo htmlspecialchars($instructor['department']); ?></td>
                    <td>
                      <div class="dropdown">
                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                          <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu">
                          <a class="dropdown-item" href="javascript:void(0);" onclick="viewProfile(<?php echo $instructor['id']; ?>)">
                            <i class="bx bx-show me-1"></i> View Profile
                          </a>
                          <?php if ($instructor['status'] == 'pending') { ?>
                            <a class="dropdown-item text-success" href="javascript:void(0);" onclick="updateStatus(<?php echo $instructor['id']; ?>, 'active')">
                              <i class="bx bx-check-circle me-1"></i> Approve
                            </a>
                          <?php } else if ($instructor['status'] == 'active') { ?>
                            <a class="dropdown-item text-info" href="javascript:void(0);" onclick="updateStatus(<?php echo $instructor['id']; ?>, 'inactive')">
                              <i class="bx bx-pause-circle me-1"></i> Deactivate
                            </a>
                            <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="updateStatus(<?php echo $instructor['id']; ?>, 'suspended')">
                              <i class="bx bx-block me-1"></i> Suspend
                            </a>
                          <?php } else if ($instructor['status'] == 'inactive') { ?>
                            <a class="dropdown-item text-success" href="javascript:void(0);" onclick="updateStatus(<?php echo $instructor['id']; ?>, 'active')">
                              <i class="bx bx-check-circle me-1"></i> Activate
                            </a>
                          <?php } else if ($instructor['status'] == 'suspended') { ?>
                            <a class="dropdown-item text-success" href="javascript:void(0);" onclick="updateStatus(<?php echo $instructor['id']; ?>, 'active')">
                              <i class="bx bx-check-circle me-1"></i> Restore
                            </a>
                          <?php } ?>
                          <a class="dropdown-item text-danger" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $instructor['id']; ?>">
                            <i class="bx bx-trash me-1"></i> Delete
                          </a>
                        </div>
                      </div>
                    </td>
                  </tr>
                <?php 
                  }
                } 
                ?>
              </tbody>
            </table>
          </div>
          <!-- Pagination -->
          <div class="card-footer">
            <div class="row">
              <div class="col-sm-12 col-md-5">
                <div class="dataTables_info" id="pagination-info" role="status" aria-live="polite">
                  Showing <span id="showing-start">1</span> to <span id="showing-end">4</span> of <span id="total-entries"><?php echo count($instructors); ?></span> entries
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
                    <li class="paginate_button page-item next" id="pagination-next">
                      <a href="#" class="page-link">Next</a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Profile Modals -->
    <?php foreach ($instructors as $instructor) { ?>
      <div class="modal fade" id="profileModal<?php echo $instructor['id']; ?>" tabindex="-1" aria-labelledby="profileModalLabel<?php echo $instructor['id']; ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="profileModalLabel<?php echo $instructor['id']; ?>">
                Instructor Profile: <?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?>
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="text-center">
                    <img src="../Uploads/instructor-profile/<?php echo htmlspecialchars($instructor['avatar']); ?>" 
                         alt="<?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?>" 
                         class="instructor-avatar mb-3" style="width: 150px; height: 150px;" />
                    <h6><?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?></h6>
                    <p class="text-muted"><?php echo htmlspecialchars($instructor['email']); ?></p>
                  </div>
                  <h6 class="fw-semibold mb-2">Bio</h6>
                  <p class="profile-bio"><?php echo htmlspecialchars($instructor['bio']); ?></p>
                  <h6 class="fw-semibold mb-2">Details</h6>
                  <ul class="list-group list-group-flush profile-details">
                    <li class="list-group-item d-flex justify-content-between">
                      <span>Department</span>
                      <span class="fw-semibold"><?php echo htmlspecialchars($instructor['department']); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                      <span>Status</span>
                      <span class="fw-semibold"><?php echo ucfirst($instructor['status']); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                      <span>Total Students</span>
                      <span class="fw-semibold"><?php echo $instructor['total_students']; ?></span>
                    </li>
                    <?php if ($instructor['rating'] > 0) { ?>
                      <li class="list-group-item d-flex justify-content-between">
                        <span>Rating</span>
                        <span class="fw-semibold"><?php echo $instructor['rating']; ?>/5</span>
                      </li>
                    <?php } ?>
                    <li class="list-group-item d-flex justify-content-between">
                      <span>Joined</span>
                      <span class="fw-semibold"><?php echo $instructor['created_at']; ?></span>
                    </li>
                  </ul>
                </div>
                <div class="col-md-6">
                  <h6 class="fw-semibold mb-2">Courses Taught</h6>
                  <div class="courses-list">
                    <?php 
                    $max_thumbnails = 4;
                    $course_count = count($instructor['courses']);
                    $display_courses = array_slice($instructor['courses'], 0, $max_thumbnails);
                    if (empty($instructor['courses'])) { 
                    ?>
                      <p class="text-muted">No courses assigned</p>
                    <?php } else { ?>
                      <?php foreach ($display_courses as $course) { ?>
                        <div class="mb-2 d-flex align-items-center">
                          <img src="../Uploads/course-thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>" 
                               alt="<?php echo htmlspecialchars($course['title']); ?>" 
                               class="me-2" />
                          <span><?php echo htmlspecialchars($course['title']); ?></span>
                        </div>
                      <?php } ?>
                      <?php if ($course_count > $max_thumbnails) { ?>
                        <div class="more-courses" data-bs-toggle="tooltip" title="<?php echo ($course_count - $max_thumbnails); ?> more courses">
                          +<?php echo ($course_count - $max_thumbnails); ?>
                        </div>
                      <?php } ?>
                    <?php } ?>
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
    <?php } ?>

    <!-- Delete Confirmation Modals -->
    <?php foreach ($instructors as $instructor) { ?>
      <div class="modal fade" id="deleteModal<?php echo $instructor['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $instructor['id']; ?>" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="deleteModalLabel<?php echo $instructor['id']; ?>">Delete Instructor</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="text-center mb-4">
                <i class="bx bx-trash text-danger" style="font-size: 6rem;"></i>
              </div>
              <p class="text-center">Are you sure you want to delete the instructor:</p>
              <h4 class="text-center mb-4">"<?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?>"</h4>
              <?php if (!empty($instructor['courses'])) { ?>
                <div class="alert alert-warning">
                  <div class="d-flex">
                    <i class="bx bx-error-circle me-2"></i>
                    <div>
                      <p class="mb-0">This instructor is assigned to <strong><?php echo count($instructor['courses']); ?> course(s)</strong>. Deleting them will remove their association with these courses.</p>
                      <p class="mb-0 mt-2">Consider <strong>deactivating</strong> the instructor instead.</p>
                    </div>
                  </div>
                </div>
              <?php } ?>
              <p class="text-center text-danger mt-3">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-danger" onclick="deleteInstructor(<?php echo $instructor['id']; ?>)">Delete Instructor</button>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
  </div>
  <!-- / Content -->

<?php include_once '../includes/admin/footer.php'; ?>

<script>
  // Initialize tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
  })

  // Pagination variables
  let currentPage = 1;
  const rowsPerPage = 10;
  let filteredRows = [];

  // Show toast function
  function showToast(type, message) {
    const toastEl = document.getElementById(type + 'Toast');
    const messageEl = document.getElementById(type + 'ToastMessage');
    if (toastEl && messageEl) {
      messageEl.textContent = message;
      const toast = new bootstrap.Toast(toastEl);
      toast.show();
    }
  }

  // Document ready
  document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-badge');
    const tableRows = document.querySelectorAll('#instructorsTable tbody tr');
    const statusCards = document.querySelectorAll('.status-card');
    const searchInput = document.getElementById('instructorSearch');

    filteredRows = Array.from(tableRows).filter(row => row.style.display !== 'none');
    updatePagination();
    displayRows(1);

    statusCards.forEach(card => {
      card.addEventListener('click', function() {
        const filter = this.getAttribute('data-filter');
        filterButtons.forEach(btn => {
          btn.classList.remove('active');
          if (btn.getAttribute('data-filter') === filter) {
            btn.classList.add('active');
          }
        });
        applyFilter(filter);
      });
    });

    filterButtons.forEach(button => {
      button.addEventListener('click', function() {
        filterButtons.forEach(btn => btn.classList.remove('active'));
        this.classList.add('active');
        const filter = this.getAttribute('data-filter');
        applyFilter(filter);
      });
    });

    searchInput.addEventListener('keyup', function() {
      const searchTerm = this.value.toLowerCase();
      const activeFilter = document.querySelector('.filter-badge.active').getAttribute('data-filter');
      filteredRows = Array.from(tableRows).filter(row => {
        const name = row.querySelector('td:first-child').textContent.toLowerCase();
        const email = row.querySelector('td:first-child .text-muted').textContent.toLowerCase();
        const rowStatus = row.getAttribute('data-status');
        const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
        const matchesFilter = activeFilter === 'all' || rowStatus === activeFilter;
        return matchesSearch && matchesFilter;
      });
      currentPage = 1;
      updatePagination();
      displayRows(currentPage);
    });

    document.getElementById('pagination-previous').addEventListener('click', function(e) {
      e.preventDefault();
      if (currentPage > 1) {
        displayRows(currentPage - 1);
      }
    });

    document.getElementById('pagination-next').addEventListener('click', function(e) {
      e.preventDefault();
      if (currentPage < Math.ceil(filteredRows.length / rowsPerPage)) {
        displayRows(currentPage + 1);
      }
    });

    function applyFilter(filter) {
      const searchTerm = searchInput.value.toLowerCase();
      filteredRows = Array.from(tableRows).filter(row => {
        const name = row.querySelector('td:first-child').textContent.toLowerCase();
        const email = row.querySelector('td:first-child .text-muted').textContent.toLowerCase();
        const status = row.getAttribute('data-status');
        const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
        const matchesFilter = filter === 'all' || status === filter;
        return matchesSearch && matchesFilter;
      });
      currentPage = 1;
      updatePagination();
      displayRows(currentPage);
    }
  });

  // Pagination functions
  function updatePagination() {
    const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
    const paginationContainer = document.querySelector('#pagination-container ul');
    const previousButton = document.getElementById('pagination-previous');
    const nextButton = document.getElementById('pagination-next');

    const pageButtons = paginationContainer.querySelectorAll('li:not(#pagination-previous):not(#pagination-next)');
    pageButtons.forEach(button => button.remove());

    const maxPagesToShow = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
    let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);

    if (endPage - startPage + 1 < maxPagesToShow && startPage > 1) {
      startPage = Math.max(1, endPage - maxPagesToShow + 1);
    }

    if (startPage > 1) {
      addPageButton(1);
      if (startPage > 2) {
        const ellipsis = document.createElement('li');
        ellipsis.className = 'paginate_button page-item disabled';
        ellipsis.innerHTML = '<span class="page-link">...</span>';
        paginationContainer.insertBefore(ellipsis, nextButton);
      }
    }

    for (let i = startPage; i <= endPage; i++) {
      addPageButton(i);
    }

    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        const ellipsis = document.createElement('li');
        ellipsis.className = 'paginate_button page-item disabled';
        ellipsis.innerHTML = '<span class="page-link">...</span>';
        paginationContainer.insertBefore(ellipsis, nextButton);
      }
      addPageButton(totalPages);
    }

    previousButton.classList.toggle('disabled', currentPage === 1);
    nextButton.classList.toggle('disabled', currentPage === totalPages || totalPages === 0);
    updatePaginationInfo();

    function addPageButton(pageNum) {
      const pageItem = document.createElement('li');
      pageItem.className = `paginate_button page-item ${pageNum === currentPage ? 'active' : ''}`;
      const pageLink = document.createElement('a');
      pageLink.href = '#';
      pageLink.className = 'page-link';
      pageLink.textContent = pageNum;
      pageLink.addEventListener('click', function(e) {
        e.preventDefault();
        displayRows(pageNum);
      });
      pageItem.appendChild(pageLink);
      paginationContainer.insertBefore(pageItem, nextButton);
    }
  }

  function displayRows(page) {
    currentPage = page;
    const tableRows = document.querySelectorAll('#instructorsTable tbody tr');

    tableRows.forEach(row => {
      row.style.display = 'none';
    });

    const startIndex = (page - 1) * rowsPerPage;
    const endIndex = startIndex + rowsPerPage;

    for (let i = startIndex; i < endIndex && i < filteredRows.length; i++) {
      filteredRows[i].style.display = '';
    }

    const pageButtons = document.querySelectorAll('#pagination-container .page-item:not(#pagination-previous):not(#pagination-next)');
    pageButtons.forEach((button) => {
      if (button.textContent === page.toString()) {
        button.classList.add('active');
      } else {
        button.classList.remove('active');
      }
    });

    const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
    document.getElementById('pagination-previous').classList.toggle('disabled', page === 1);
    document.getElementById('pagination-next').classList.toggle('disabled', page === totalPages);
    updatePaginationInfo();
  }

  function updatePaginationInfo() {
    const startIndex = (currentPage - 1) * rowsPerPage + 1;
    const endIndex = Math.min(startIndex + rowsPerPage - 1, filteredRows.length);
    document.getElementById('showing-start').textContent = filteredRows.length > 0 ? startIndex : 0;
    document.getElementById('showing-end').textContent = endIndex;
    document.getElementById('total-entries').textContent = filteredRows.length;
  }

  // Instructor-specific functions
  function viewProfile(instructorId) {
    const modalId = `profileModal${instructorId}`;
    const modal = new bootstrap.Modal(document.getElementById(modalId));
    modal.show();
  }

  function updateStatus(instructorId, status) {
    showOverlay("Updating status...");
    setTimeout(() => {
      console.log(`Updating instructor ${instructorId} status to ${status}`);
      removeOverlay();
      let message = '';
      switch(status) {
        case 'active': message = 'Instructor activated successfully.'; break;
        case 'inactive': message = 'Instructor deactivated successfully.'; break;
        case 'suspended': message = 'Instructor suspended successfully.'; break;
      }
      showToast('success', message);
      setTimeout(() => {
        location.reload();
      }, 1500);

      // Example AJAX:
      // fetch('../backend/update_instructor_status.php', {
      //   method: 'POST',
      //   headers: { 'Content-Type': 'application/json' },
      //   body: JSON.stringify({ instructor_id: instructorId, status: status })
      // }).then(response => response.json()).then(data => {
      //   removeOverlay();
      //   showToast(data.success ? 'success' : 'error', data.message);
      //   if (data.success) setTimeout(() => location.reload(), 1500);
      // }).catch(error => {
      //   removeOverlay();
      //   showToast('error', 'Error updating status.');
      // });
    }, 1000);
  }

  function deleteInstructor(instructorId) {
    showOverlay("Deleting instructor...");
    setTimeout(() => {
      console.log(`Deleting instructor ${instructorId}`);
      removeOverlay();
      const modalId = `deleteModal${instructorId}`;
      const modal = document.getElementById(modalId);
      if (modal) {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) bsModal.hide();
      }
      showToast('success', 'Instructor deleted successfully.');
      setTimeout(() => {
        location.reload();
      }, 1500);

      // Example AJAX:
      // fetch('../backend/delete_instructor.php', {
      //   method: 'POST',
      //   headers: { 'Content-Type': 'application/json' },
      //   body: JSON.stringify({ instructor_id: instructorId })
      // }).then(response => response.json()).then(data => {
      //   removeOverlay();
      //   if (data.success) {
      //     bootstrap.Modal.getInstance(document.getElementById(modalId)).hide();
      //     showToast('success', data.message);
      //     setTimeout(() => location.reload(), 1500);
      //   } else {
      //     showToast('error', data.message || 'Error deleting instructor.');
      //   }
      // }).catch(error => {
      //   removeOverlay();
      //   showToast('error', 'Error deleting instructor.');
      // });
    }, 1200);
  }

  // Placeholder for showOverlay and removeOverlay (assumed to exist in global scripts)
  function showOverlay(message) {
    console.log(`Showing overlay: ${message}`);
  }

  function removeOverlay() {
    console.log('Removing overlay');
  }
</script>