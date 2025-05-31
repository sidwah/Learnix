<?php include '../includes/student-header.php'; ?>

<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main" class="bg-light">
  <!-- Breadcrumb -->
  <?php include '../includes/student-breadcrumb.php'; ?>
  <!-- End Breadcrumb -->

  <!-- Content -->
  <div class="container content-space-1 content-space-t-lg-0 content-space-b-lg-2 mt-lg-n10">
    <div class="row">
      <div class="col-lg-3">
        <!-- Navbar -->
        <div class="navbar-expand-lg navbar-light">
          <div id="sidebarNav" class="collapse navbar-collapse navbar-vertical">
            <!-- Card -->
            <div class="card flex-grow-1 mb-5">
              <div class="card-body">
                <!-- Avatar -->
                <div class="d-none d-lg-block text-center mb-5">
                  <div class="avatar avatar-xxl avatar-circle mb-3">
                    <div class="flex-shrink-0">
                      <img class="avatar avatar-xl avatar-circle"
                        src="../Uploads/profile/<?php echo $row['profile_pic'] ?>"
                        alt="Profile">
                    </div>
                  </div>
                  <h4 class="card-title mb-0"><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></h4>
                  <p class="card-text small"><?php echo $row['email']; ?></p>
                </div>
                <!-- End Avatar -->

                <!-- Sidebar Content -->
                <!-- Overview Section -->
                <span class="text-cap">Overview</span>
                <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                  <li class="nav-item">
                    <a class="nav-link" href="account-overview.php">
                      <i class="bi-person-circle nav-icon"></i> Account Overview
                    </a>
                  </li>
                </ul>

                <!-- Account Section -->
                <span class="text-cap">Account</span>
                <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                  <li class="nav-item">
                    <a class="nav-link" href="account-profile.php">
                      <i class="bi-person-badge nav-icon"></i> Personal info
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="account-security.php">
                      <i class="bi-shield-shaded nav-icon"></i> Security
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="account-notifications.php">
                      <i class="bi-bell nav-icon"></i> Notifications
                      <!-- <span class="badge bg-soft-dark text-dark rounded-pill nav-link-badge">0</span> -->
                    </a>
                  </li>
                </ul>

                <!-- Student-Specific Section -->
                <span class="text-cap">My Courses</span>
                <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                  <li class="nav-item">
                    <a class="nav-link active" href="my-courses.php">
                      <i class="bi-person-badge nav-icon"></i> Enrolled Courses
                    </a>
                  </li>
                  <!-- <li class="nav-item">
                    <a class="nav-link" href="my-badges.php">
                      <i class="bi-chat-dots nav-icon"></i> Badges
                    </a>
                  </li> -->
                  <li class="nav-item">
                    <a class="nav-link" href="my-certifications.php">
                      <i class="bi-award nav-icon"></i> Certifications
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="my-notes.php">
                      <i class="bi-journal-text nav-icon"></i> Notes
                    </a>
                  </li>
                </ul>

                <!-- Payment Section for Students -->
                <span class="text-cap">Payments</span>
                <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                  <li class="nav-item">
                    <a class="nav-link" href="payment-history.php">
                      <i class="bi-credit-card nav-icon"></i> Payment History
                    </a>
                  </li>
                </ul>

                <!-- Instructor/Admin Section (Dynamic Role Check) -->
                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'instructor'): ?>
                  <span class="text-cap">Instructor</span>
                  <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                    <li class="nav-item">
                      <a class="nav-link" href="instructor-courses.php">
                        <i class="bi-person-badge nav-icon"></i> My Courses
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="create-course.php">
                        <i class="bi-file-earmark-plus nav-icon"></i> Create Course
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="manage-students.php">
                        <i class="bi-person-check nav-icon"></i> Manage Students
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="course-feedback.php">
                        <i class="bi-chat-dots nav-icon"></i> Course Feedback
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="instructor-withdrawal.php">
                        <i class="bi-wallet nav-icon"></i> Withdrawal
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="instructor-analytics.php">
                        <i class="bi-gear nav-icon"></i> Analytics
                      </a>
                    </li>
                  </ul>
                <?php endif; ?>

                <!-- Sign-out & Help Section -->
                <span class="text-cap">---</span>
                <ul class="nav nav-sm nav-tabs nav-vertical">
                  <li class="nav-item">
                    <a class="nav-link" href="account-help.php">
                      <i class="bi-question-circle nav-icon"></i> Help
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="FAQ.php">
                      <i class="bi-card-list nav-icon"></i> FAQ's
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="report.php">
                      <i class="bi-exclamation-triangle nav-icon"></i> Report Issues
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="../backend/signout.php">
                      <i class="bi-box-arrow-right nav-icon"></i> Sign out
                    </a>
                  </li>
                </ul>
                <!-- End of Sidebar -->
              </div>
            </div>
            <!-- End Card -->
          </div>
        </div>
        <!-- End Navbar -->
      </div>
      <!-- End Col -->

      <div class="col-lg-9">
        <div class="d-grid gap-3 gap-lg-5">
          <!-- Stats Card -->
          <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body p-4">
              <div class="row align-items-center">
                <?php
                $stats_query = "SELECT 
                    COUNT(CASE WHEN status = 'Active' AND completion_percentage < 100 THEN 1 END) as active_courses,
                    COUNT(CASE WHEN completion_percentage >= 100 THEN 1 END) as completed_courses,
                    COUNT(*) as total_courses
                    FROM enrollments 
                    WHERE user_id = ?";
                $stats_stmt = $conn->prepare($stats_query);
                $stats_stmt->bind_param("i", $_SESSION['user_id']);
                $stats_stmt->execute();
                $stats_result = $stats_stmt->get_result();
                $stats = $stats_result->fetch_assoc();
                $active_courses = $stats['active_courses'] ?? 0;
                $completed_courses = $stats['completed_courses'] ?? 0;
                $total_hours = rand(5, 50);
                ?>
                <div class="col-md-6 mb-3 mb-md-0">
                  <div class="d-flex align-items-center">
                    <div class="bg-gradient-primary rounded-3 p-3 me-3">
                      <i class="bi bi-fire text-white fs-3"></i>
                    </div>
                    <div>
                      <h5 class="mb-1">Learning Stats</h5>
                      <h2 class="mb-0 text-primary"><?php echo $active_courses; ?> Active</h2>
                      <p class="small text-muted mb-0">Keep learning to maintain your progress!</p>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="me-3 text-center">
                      <div class="fs-5 fw-bold text-success"><?php echo $completed_courses; ?></div>
                      <div class="small text-muted">Completed</div>
                    </div>
                    <div class="text-center">
                      <div class="fs-5 fw-bold text-warning"><?php echo $total_hours; ?>h</div>
                      <div class="small text-muted">This Month</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Enrolled Courses Card -->
          <div id="enrolledCoursesCard" class="card">
            <div class="card-header border-bottom">
              <h4 class="card-header-title">Enrolled Courses</h4>
            </div>

            <!-- Body -->
            <div class="card-body">
              <!-- Course Controls -->
              <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <div class="flex space-x-4 w-full md:w-auto">
                  <div class="relative w-full md:w-64">
                    <input type="text" class="form-control pl-10" id="searchCourses" placeholder="Search courses..." oninput="filterCourses()">
                    <i class="bi bi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                  </div>
                  <select class="form-select w-full md:w-40" id="filterStatus" onchange="filterCourses()">
                    <option value="all">All Status</option>
                    <option value="active">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="suspended">Suspended</option>
                    <option value="expired">Expired</option>
                  </select>
                  <button class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">Reset</button>
                </div>
                <a href="courses.php" class="btn btn-sm btn-primary">Browse More Courses</a>
              </div>

              <!-- Course List -->
              <div id="courseList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php
                $query = "SELECT c.*, e.enrollment_id, e.enrolled_at, e.completion_percentage, e.status as enrollment_status 
                    FROM enrollments e 
                    JOIN courses c ON e.course_id = c.course_id 
                    WHERE e.user_id = ? 
                    ORDER BY e.enrolled_at DESC";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();

                $defaults = [
                  'course_id' => 0,
                  'title' => 'Untitled Course',
                  'thumbnail' => 'default.jpg',
                  'short_description' => 'No description available',
                  'enrollment_id' => 0,
                  'enrolled_at' => date('Y-m-d H:i:s'),
                  'completion_percentage' => 0,
                  'enrollment_status' => 'active'
                ];

                $courses = [];
                if ($result->num_rows > 0) {
                  while ($course = $result->fetch_assoc()) {
                    $course = array_merge($defaults, $course);
                    $progress = min(max(round(floatval($course['completion_percentage'])), 0), 100);
                    $status = strtolower($course['enrollment_status']);
                    $courses[] = [
                      'id' => $course['course_id'],
                      'title' => $course['title'],
                      'description' => $course['short_description'],
                      'thumbnail' => "../Uploads/thumbnails/{$course['thumbnail']}",
                      'progress' => $progress,
                      'status' => $status,
                      'enrolled_at' => $course['enrolled_at'],
                      'favorite' => false
                    ];
                ?>
                    <div class="course-item bg-white rounded-lg shadow-sm hover:shadow-lg transition-shadow overflow-hidden"
                      data-status="<?php echo htmlspecialchars($status); ?>"
                      data-progress="<?php echo $progress; ?>"
                      data-id="<?php echo $course['course_id']; ?>">
                      <div class="relative">
                        <img src="../Uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>"
                          alt="<?php echo htmlspecialchars($course['title']); ?>"
                          class="w-full h-40 object-cover">
                        <button class="absolute top-2 right-2 text-white bg-gray-800 bg-opacity-50 rounded-full p-1 hover:bg-opacity-75"
                          onclick="toggleFavorite(this)"
                          data-id="<?php echo $course['course_id']; ?>">
                          <i class="bi bi-heart"></i>
                        </button>
                      </div>
                      <div class="p-4">
                        <h5 class="text-base font-semibold truncate"><?php echo htmlspecialchars($course['title']); ?></h5>
                        <p class="text-sm text-gray-600 truncate"><?php echo htmlspecialchars($course['short_description']); ?></p>
                        <span class="badge bg-soft-<?php echo $progress >= 100 ? 'success' : 'info'; ?> text-<?php echo $progress >= 100 ? 'success' : 'info'; ?> rounded-pill mt-2 mb-2">
                          <?php echo $progress >= 100 ? 'COMPLETED' : strtoupper($status); ?>
                        </span>
                        <div class="mt-2">
                          <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-primary"
                              role="progressbar"
                              style="width: <?php echo $progress; ?>%;"
                              aria-valuenow="<?php echo $progress; ?>"
                              aria-valuemin="0"
                              aria-valuemax="100"></div>
                          </div>
                          <span class="text-xs text-gray-400"><?php echo $progress; ?>% Complete</span>
                        </div>
                        <div class="mt-4 flex justify-between items-center">
                          <a href="course-overview.php?id=<?php echo $course['course_id']; ?>"
                            class="btn btn-sm btn-soft-secondary">Course Details</a>
                          <a href="course-materials.php?course_id=<?php echo $course['course_id']; ?>"
                            class="btn btn-sm btn-primary"><?php echo $progress >= 100 ? 'Review Course' : 'Continue Learning'; ?></a>
                        </div>
                      </div>
                    </div>
                <?php
                  }
                }
                ?>
              </div>

              <!-- Empty State -->
              <div id="emptyCourses" class="text-center py-8 col-span-3 <?php echo $result->num_rows > 0 ? 'hidden' : ''; ?>">
                <i class="bi bi-book text-gray-400 text-4xl mb-2"></i>
                <p class="text-gray-600">No courses found. Enroll in a course to get started!</p>
                <a href="courses.php" class="btn btn-primary mt-3">Browse Courses</a>
              </div>

              <!-- Pagination -->
              <?php if ($result->num_rows > 9): ?>
                <div class="flex justify-center mt-6">
                  <nav aria-label="Page navigation">
                    <ul class="pagination">
                      <li class="page-item"><a class="page-link" href="#" onclick="changePage(currentPage - 1)">Previous</a></li>
                      <li class="page-item"><a class="page-link active" href="#" onclick="changePage(1)">1</a></li>
                      <li class="page-item"><a class="page-link" href="#" onclick="changePage(2)">2</a></li>
                      <li class="page-item"><a class="page-link" href="#" onclick="changePage(3)">3</a></li>
                      <li class="page-item"><a class="page-link" href="#" onclick="changePage(currentPage + 1)">Next</a></li>
                    </ul>
                  </nav>
                </div>
              <?php endif; ?>
            </div>
            <!-- End Body -->
          </div>
          <!-- End Card -->
        </div>
      </div>
      <!-- End Col -->
    </div>
    <!-- End Row -->
  </div>
  <!-- End Content -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- ========== FOOTER ========== -->
<?php include '../includes/student-footer.php'; ?>
<!-- ========== END FOOTER ========== -->

<!-- Tailwind CSS CDN -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

<!-- Bootstrap JS for Modal -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<style>
  .bg-gradient-primary {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
  }

  .hover-shadow {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .hover-shadow:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1) !important;
  }

  .progress {
    border-radius: 10rem;
  }

  .progress-bar {
    border-radius: 10rem;
  }
</style>

<script>
  // Course data from PHP
  const courses = <?php echo json_encode($courses); ?>;
  let currentPage = 1;
  const coursesPerPage = 9;

  // Render courses
  function renderCourses(filteredCourses) {
    const courseList = document.getElementById('courseList');
    const emptyCourses = document.getElementById('emptyCourses');

    courseList.innerHTML = '';

    const startIndex = (currentPage - 1) * coursesPerPage;
    const paginatedCourses = filteredCourses.slice(startIndex, startIndex + coursesPerPage);

    if (paginatedCourses.length === 0 && filteredCourses.length === 0) {
      if (emptyCourses) emptyCourses.classList.remove('hidden');
      courseList.classList.add('hidden');
      return;
    }

    if (emptyCourses) emptyCourses.classList.add('hidden');
    courseList.classList.remove('hidden');

    paginatedCourses.forEach(course => {
      const statusClass = course.progress >= 100 ? 'success' : 'info';
      const courseItem = document.createElement('div');
      courseItem.className = 'course-item bg-white rounded-lg shadow-sm hover:shadow-lg transition-shadow overflow-hidden';
      courseItem.dataset.status = course.status;
      courseItem.dataset.progress = course.progress;
      courseItem.dataset.id = course.id;
      courseItem.innerHTML = `
        <div class="relative">
          <img src="${course.thumbnail}" alt="${course.title}" class="w-full h-40 object-cover">
          <button class="absolute top-2 right-2 text-white bg-gray-800 bg-opacity-50 rounded-full p-1 hover:bg-opacity-75" onclick="toggleFavorite(this)" data-id="${course.id}">
            <i class="bi bi-heart${course.favorite ? '-fill text-red-500' : ''}"></i>
          </button>
        </div>
        <div class="p-4">
          <h5 class="text-base font-semibold truncate">${course.title}</h5>
          <p class="text-sm text-gray-600 truncate">${course.description}</p>
          <span class="badge bg-soft-${statusClass} text-${statusClass} rounded-pill mt-2 mb-2">
            ${course.progress >= 100 ? 'COMPLETED' : course.status.toUpperCase()}
          </span>
          <div class="mt-2">
            <div class="progress" style="height: 10px;">
              <div class="progress-bar bg-primary" role="progressbar" style="width: ${course.progress}%;" aria-valuenow="${course.progress}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <span class="text-xs text-gray-400">${course.progress}% Complete</span>
          </div>
          <div class="mt-4 flex justify-between items-center">
            <a href="course-overview.php?id=${course.id}" class="btn btn-sm btn-soft-secondary">Course Details</a>
            <a href="course-materials.php?course_id=${course.id}" class="btn btn-sm btn-primary">${course.progress >= 100 ? 'Review Course' : 'Continue Learning'}</a>
          </div>
        </div>
      `;
      courseList.appendChild(courseItem);
    });

    updatePagination(filteredCourses.length);
  }

  // Update pagination
  function updatePagination(totalCourses) {
    const totalPages = Math.ceil(totalCourses / coursesPerPage);
    const pagination = document.querySelector('.pagination');
    if (!pagination) return;
    pagination.innerHTML = `
      <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="changePage(currentPage - 1)">Previous</a>
      </li>
    `;
    for (let i = 1; i <= totalPages; i++) {
      pagination.innerHTML += `
        <li class="page-item ${i === currentPage ? 'active' : ''}">
          <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
        </li>
      `;
    }
    pagination.innerHTML += `
      <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="changePage(currentPage + 1)">Next</a>
      </li>
    `;
  }

  // Change page
  function changePage(page) {
    const totalPages = Math.ceil(courses.length / coursesPerPage);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    filterCourses();
  }

  // Filter and search courses
  function filterCourses() {
    const searchQuery = document.getElementById('searchCourses').value.toLowerCase();
    const filterStatus = document.getElementById('filterStatus').value;

    let filteredCourses = courses;
    if (filterStatus !== 'all') {
      filteredCourses = courses.filter(c =>
        filterStatus === 'completed' ? c.progress >= 100 : c.status === filterStatus
      );
    }
    if (searchQuery) {
      filteredCourses = filteredCourses.filter(c =>
        c.title.toLowerCase().includes(searchQuery) ||
        c.description.toLowerCase().includes(searchQuery)
      );
    }

    currentPage = 1;
    renderCourses(filteredCourses);
  }

  // Reset filters
  function resetFilters() {
    const searchCourses = document.getElementById('searchCourses');
    const filterStatus = document.getElementById('filterStatus');
    if (searchCourses) searchCourses.value = '';
    if (filterStatus) filterStatus.value = 'all';
    currentPage = 1;
    renderCourses(courses);
  }

  // Toggle favorite
  function toggleFavorite(button) {
    const id = parseInt(button.dataset.id);
    const course = courses.find(c => c.id === id);
    if (course) {
      course.favorite = !course.favorite;
      button.innerHTML = `<i class="bi bi-heart${course.favorite ? '-fill text-red-500' : ''}"></i>`;
    }
  }

  // Page transition for course links
  document.addEventListener('click', function(e) {
    const courseLink = e.target.closest('a[href^="course-materials.php"], a[href^="course-overview.php"]');
    if (courseLink) {
      e.preventDefault();
      const href = courseLink.getAttribute('href');
      const overlay = document.createElement('div');
      overlay.className = 'position-fixed top-0 start-0 w-100 h-100 bg-white';
      overlay.style.opacity = '0';
      overlay.style.transition = 'opacity 0.3s ease';
      overlay.style.zIndex = '9999';
      document.body.appendChild(overlay);
      setTimeout(() => {
        overlay.style.opacity = '1';
        setTimeout(() => {
          window.location.href = href;
        }, 300);
      }, 10);
    }
  });

  // Initial render
  renderCourses(courses);
</script>