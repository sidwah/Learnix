<?php include '../includes/signin-header.php'; ?>


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
                                                src="../uploads/profile/<?php echo $row['profile_pic'] ?>"
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
                                            <span class="badge bg-soft-dark text-dark rounded-pill nav-link-badge">0</span>
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
                                    <li class="nav-item">
                                        <a class="nav-link" href="course-accomplishments.php">
                                            <i class="bi-chat-dots nav-icon"></i> Accomplishments
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="student-certifications.php">
                                            <i class="bi-award nav-icon"></i> Certifications
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="course-progress.php">
                                            <i class="bi-bar-chart-line nav-icon"></i> Course Progress
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
                                    <li class="nav-item">
                                        <a class="nav-link" href="payment-method.php">
                                            <i class="bi-wallet nav-icon"></i> Payment Methods
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
                    <!-- Card -->
                    <div class="card">
                        <div class="card-header border-bottom">
                            <h4 class="card-header-title">Enrolled Courses</h4>
                        </div>

                        <!-- Body -->
                        <div class="card-body">

                            <!-- Header with Stats -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h3 class="mb-1">My Learning</h3>
                                    <p class="text-muted mb-0">Welcome back! Continue your learning journey</p>
                                </div>
                                <?php
                                // Get basic counts from enrollments table
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

                                // If no stats found, set defaults
                                $active_courses = $stats['active_courses'] ?? 0;
                                $completed_courses = $stats['completed_courses'] ?? 0;
                                $total_hours = rand(5, 50); // Placeholder
                                ?>
                                <div class="d-flex">
                                    <div class="me-3 text-center">
                                        <div class="fs-5 fw-bold text-primary"><?php echo $active_courses; ?></div>
                                        <div class="small text-muted">Active Courses</div>
                                    </div>
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

                            <!-- Filter & Sort Controls -->
                            <div class="d-flex justify-content-between align-items-center mb-4 bg-light rounded-3 p-3">
                                <div class="d-flex align-items-center">
                                    <div class="dropdown me-3">
                                        <button class="btn btn-light border dropdown-toggle" type="button" id="courseFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-funnel me-2"></i>All Courses
                                        </button>
                                        <ul class="dropdown-menu shadow-sm" aria-labelledby="courseFilterDropdown">
                                            <li><a class="dropdown-item active" href="#" data-filter="all"><i class="bi bi-collection me-2"></i>All Courses</a></li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li><a class="dropdown-item" href="#" data-filter="active"><i class="bi bi-play-circle me-2"></i>In Progress</a></li>
                                            <li><a class="dropdown-item" href="#" data-filter="completed"><i class="bi bi-check-circle me-2"></i>Completed</a></li>
                                        </ul>
                                    </div>

                                    <div class="form-check form-switch me-3">
                                        <input class="form-check-input" type="checkbox" id="showArchivedSwitch">
                                        <label class="form-check-label small" for="showArchivedSwitch">Show Archived</label>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center">
                                    <div class="dropdown me-3">
                                        <button class="btn btn-light border dropdown-toggle" type="button" id="sortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-arrow-down-up me-2"></i>Recent
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="sortDropdown">
                                            <li><a class="dropdown-item active" href="#" data-sort="recent"><i class="bi bi-clock-history me-2"></i>Recent</a></li>
                                            <li><a class="dropdown-item" href="#" data-sort="name"><i class="bi bi-sort-alpha-down me-2"></i>Name</a></li>
                                            <li><a class="dropdown-item" href="#" data-sort="progress"><i class="bi bi-graph-up me-2"></i>Progress</a></li>
                                        </ul>
                                    </div>

                                    <div class="btn-group shadow-sm" role="group">
                                        <button type="button" class="btn btn-light border-end-0 active" data-view="grid">
                                            <i class="bi bi-grid-3x3-gap"></i>
                                        </button>
                                        <button type="button" class="btn btn-light border-start-0" data-view="list">
                                            <i class="bi bi-list-task"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Learning Streak & Goals Card -->
                            <div class="card mb-4 border-0 shadow-sm">
                                <div class="card-body p-4">
                                    <div class="row align-items-center">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-gradient-primary rounded-3 p-3 me-3">
                                                    <i class="bi bi-fire text-white fs-3"></i>
                                                </div>
                                                <div>
                                                    <h5 class="mb-1">Learning Streak</h5>
                                                    <h2 class="mb-0 text-primary"><?php echo rand(1, 30); ?> days</h2>
                                                    <p class="small text-muted mb-0">Keep learning to maintain your streak!</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-gradient-success rounded-3 p-3 me-3">
                                                    <i class="bi bi-check-circle text-white fs-3"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <h5 class="mb-0">Weekly Goal</h5>
                                                        <span class="text-success fw-bold">65%</span>
                                                    </div>
                                                    <div class="progress mb-2" style="height: 8px;">
                                                        <div class="progress-bar bg-success" role="progressbar" style="width: 65%;" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <p class="small text-muted mb-0">3.25 of 5 hours completed</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- All Courses Section -->
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">My Courses</h5>
                                    <?php
                                    // Count total enrollments
                                    $count_query = "SELECT COUNT(*) as total FROM enrollments WHERE user_id = ?";
                                    $count_stmt = $conn->prepare($count_query);
                                    $count_stmt->bind_param("i", $_SESSION['user_id']);
                                    $count_stmt->execute();
                                    $count_result = $count_stmt->get_result();
                                    $count_data = $count_result->fetch_assoc();
                                    $total_courses = $count_data['total'] ?? 0;
                                    ?>
                                    <div class="small text-muted">Showing <?php echo $total_courses; ?> courses</div>
                                </div>

                                <?php
                                // Fetch enrolled courses
                                $query = "SELECT c.*, e.enrollment_id, e.enrolled_at, e.completion_percentage, e.status as enrollment_status 
             FROM enrollments e 
             JOIN courses c ON e.course_id = c.course_id 
             WHERE e.user_id = ? 
             ORDER BY e.enrolled_at DESC";

                                $stmt = $conn->prepare($query);
                                $stmt->bind_param("i", $_SESSION['user_id']);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                // Set default values for all possible fields
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

                                // Debug progress values
                                echo "<!-- DEBUG: Progress values -->";
                                ?>

                                <div class="row g-4" id="coursesContainer">
                                    <?php if ($result->num_rows > 0) : ?>
                                        <?php while ($course = $result->fetch_assoc()) :
                                            // Merge with defaults to ensure all fields exist
                                            $course = array_merge($defaults, $course);

                                            // Debug this specific course's progress value
                                            echo "<!-- Course: " . htmlspecialchars($course['title']) . " - Progress: " . $course['completion_percentage'] . " -->";

                                            // Convert properly, ensuring we handle all possible data formats
                                            $progress = 0;
                                            if (isset($course['completion_percentage'])) {
                                                if (is_numeric($course['completion_percentage'])) {
                                                    $progress = floatval($course['completion_percentage']);
                                                }
                                            }

                                            // Ensure the progress is within valid range and rounded
                                            $progress = min(max(round($progress), 0), 100);

                                            // Debug the processed progress value
                                            echo "<!-- After processing: " . $progress . " -->";

                                            $statusClass = ($progress >= 100) ? 'bg-success' : ($progress > 70 ? 'bg-primary' : ($progress > 30 ? 'bg-info' : 'bg-warning'));
                                        ?>
                                            <div class="col-md-6 col-lg-4 mb-3 course-card w-50"
                                                data-status="<?= htmlspecialchars($course['enrollment_status']) ?>"
                                                data-favorite="0"
                                                data-progress="<?= $progress ?>">

                                                <div class="card h-100 border-0 shadow-sm hover-shadow">
                                                    <!-- Card Image -->
                                                    <div class="card-img-top position-relative">
                                                        <img class="card-img-top"
                                                            src="../uploads/thumbnails/<?= htmlspecialchars($course['thumbnail']) ?>"
                                                            alt="<?= htmlspecialchars($course['title']) ?>">
                                                    </div>

                                                    <div class="card-body" style=" margin-bottom: -2rem;">
                                                        <h5 class="card-title"><?= htmlspecialchars($course['title']) ?></h5>
                                                        <p class="card-text small text-truncate">
                                                            <?= htmlspecialchars($course['short_description']) ?>
                                                        </p>

                                                        <!-- Progress Bar -->
                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                            <small><?= $progress ?>% complete</small>
                                                            <?php if ($progress >= 100) : ?>
                                                                <span class="badge bg-success">Completed</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="progress mb-3" style="height: 5px;">
                                                            <div class="progress-bar <?= $statusClass ?>"
                                                                role="progressbar"
                                                                style="width: <?= $progress ?>%;"
                                                                aria-valuenow="<?= $progress ?>"
                                                                aria-valuemin="0"
                                                                aria-valuemax="100"></div>
                                                        </div>

                                                        <div class="d-grid gap-2 ">
                                                            <?php if ($progress < 100) : ?>
                                                                <a href="learn.php?course_id=<?= (int)$course['course_id'] ?>"
                                                                    class="btn btn-primary btn-sm">
                                                                    Continue Learning
                                                                </a>
                                                            <?php else : ?>
                                                                <a href="learn.php?course_id=<?= (int)$course['course_id'] ?>"
                                                                    class="btn btn-outline-primary btn-sm">
                                                                    Review Course
                                                                </a>
                                                            <?php endif; ?>
                                                            <a href="course-overview.php?id=<?= (int)$course['course_id'] ?>"
                                                                class="btn btn-outline-secondary btn-sm">
                                                                Course Details
                                                            </a>
                                                        </div>
                                                    </div>

                                                    <div class="card-footer bg-white small text-muted">
                                                        <div class="d-flex justify-content-between">
                                                            <span>Enrolled: <?= date('M d, Y', strtotime($course['enrolled_at'])) ?></span>
                                                            <span>
                                                                <i class="bi-clock"></i>
                                                                <?= rand(1, 10) ?> hrs
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    <?php else : ?>
                                        <div class="col-12 text-center py-5">
                                            <div class="mb-4">
                                                <i class="bi-book display-4 text-muted"></i>
                                            </div>
                                            <h5>You haven't enrolled in any courses yet</h5>
                                            <p class="text-muted">Browse our catalog to find a course that interests you.</p>
                                            <a href="courses.php" class="btn btn-primary">Browse Courses</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Pagination -->
                            <?php if ($result->num_rows > 8): ?>
                                <nav aria-label="Page navigation" class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <li class="page-item disabled">
                                            <a class="page-link" href="#" tabindex="-1" aria-disabled="true">
                                                <i class="bi-chevron-left"></i>
                                            </a>
                                        </li>
                                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                                        <li class="page-item">
                                            <a class="page-link" href="#">
                                                <i class="bi-chevron-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>

                        <style>
                            .hover-shadow {
                                transition: transform 0.2s ease, box-shadow 0.2s ease;
                            }

                            .hover-shadow:hover {
                                transform: translateY(-2px);
                                box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1) !important;
                            }

                            .bg-gradient-primary {
                                background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
                            }

                            .bg-gradient-success {
                                background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
                            }

                            .progress {
                                border-radius: 10rem;
                            }

                            .progress-bar {
                                border-radius: 10rem;
                            }

                            .btn-icon {
                                width: 32px;
                                height: 32px;
                                display: inline-flex;
                                align-items: center;
                                justify-content: center;
                                padding: 0;
                            }
                        </style>

                        <script>
                            // Enhanced JavaScript with animations
                            document.addEventListener('DOMContentLoaded', function() {
                                // Filter functionality with animation
                                const filterLinks = document.querySelectorAll('[data-filter]');
                                filterLinks.forEach(link => {
                                    link.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        const filter = this.dataset.filter;

                                        // Update UI
                                        document.getElementById('courseFilterDropdown').innerHTML =
                                            `<i class="bi bi-funnel me-2"></i>${this.textContent}`;

                                        filterLinks.forEach(item => item.classList.remove('active'));
                                        this.classList.add('active');

                                        // Animate filtering
                                        const courseCards = document.querySelectorAll('.course-card');
                                        courseCards.forEach((card, index) => {
                                            const shouldShow = filter === 'all' ||
                                                (filter === 'active' && parseFloat(card.dataset.progress) < 100) ||
                                                (filter === 'completed' && parseFloat(card.dataset.progress) >= 100) ||
                                                (filter === 'favorite' && card.dataset.favorite === '1');

                                            card.style.opacity = '0';
                                            card.style.transform = 'translateY(20px)';

                                            setTimeout(() => {
                                                card.style.display = shouldShow ? '' : 'none';
                                                setTimeout(() => {
                                                    if (shouldShow) {
                                                        card.style.opacity = '';
                                                        card.style.transform = '';
                                                    }
                                                }, 50);
                                            }, index * 50);
                                        });
                                    });
                                });

                                // Sort functionality with animation
                                const sortLinks = document.querySelectorAll('[data-sort]');
                                sortLinks.forEach(link => {
                                    link.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        const sort = this.dataset.sort;

                                        // Update UI
                                        document.getElementById('sortDropdown').innerHTML =
                                            `<i class="bi bi-arrow-down-up me-2"></i>${this.textContent}`;

                                        sortLinks.forEach(item => item.classList.remove('active'));
                                        this.classList.add('active');

                                        // Sort with animation
                                        const coursesContainer = document.getElementById('coursesContainer');
                                        const courseCards = Array.from(document.querySelectorAll('.course-card'));

                                        courseCards.sort((a, b) => {
                                            if (sort === 'name') {
                                                return a.querySelector('.card-title').textContent.localeCompare(
                                                    b.querySelector('.card-title').textContent);
                                            } else if (sort === 'progress') {
                                                return parseFloat(b.dataset.progress) - parseFloat(a.dataset.progress);
                                            }
                                            return 0;
                                        });

                                        // Animate reordering
                                        courseCards.forEach(card => card.style.opacity = '0');

                                        setTimeout(() => {
                                            courseCards.forEach(card => coursesContainer.appendChild(card));

                                            setTimeout(() => {
                                                courseCards.forEach((card, index) => {
                                                    setTimeout(() => {
                                                        card.style.opacity = '';
                                                    }, index * 50);
                                                });
                                            }, 100);
                                        }, 300);
                                    });
                                });

                                // View toggle with smooth transition
                                const viewButtons = document.querySelectorAll('[data-view]');
                                viewButtons.forEach(button => {
                                    button.addEventListener('click', function() {
                                        viewButtons.forEach(btn => btn.classList.remove('active'));
                                        this.classList.add('active');

                                        const view = this.dataset.view;
                                        const courseCards = document.querySelectorAll('.course-card');

                                        courseCards.forEach(card => {
                                            // Fade out
                                            card.style.opacity = '0';
                                            card.style.transition = 'opacity 0.3s ease';
                                        });

                                        // Change layout after fade out
                                        setTimeout(() => {
                                            courseCards.forEach(card => {
                                                card.classList.remove('col-md-6', 'col-lg-4', 'col-xl-3', 'col-12');
                                                const cardElement = card.querySelector('.card');

                                                if (view === 'list') {
                                                    card.classList.add('col-12');
                                                    cardElement.classList.add('flex-row');
                                                    cardElement.querySelector('.card-img-top').style.width = '25%';
                                                    cardElement.querySelector('.card-body').classList.add('flex-grow-1');
                                                } else {
                                                    card.classList.add('col-md-6', 'col-lg-4');
                                                    cardElement.classList.remove('flex-row');
                                                    cardElement.querySelector('.card-img-top').style.width = '';
                                                    cardElement.querySelector('.card-body').classList.remove('flex-grow-1');
                                                }
                                            });

                                            // Fade back in
                                            setTimeout(() => {
                                                courseCards.forEach(card => {
                                                    card.style.opacity = '1';
                                                });
                                            }, 100);
                                        }, 300);
                                    });
                                });

                                // Show archived courses toggle
                                document.getElementById('showArchivedSwitch').addEventListener('change', function() {
                                    const showArchived = this.checked;
                                    const courseCards = document.querySelectorAll('.course-card');

                                    courseCards.forEach(card => {
                                        if (card.dataset.status === 'suspended' || card.dataset.status === 'expired') {
                                            card.style.display = showArchived ? '' : 'none';
                                        }
                                    });
                                });

                                // Add page transitions
                                document.addEventListener('click', function(e) {
                                    // Check if the clicked element is a course link
                                    const courseLink = e.target.closest('a[href^="learn.php"], a[href^="course-overview.php"]');
                                    if (courseLink) {
                                        e.preventDefault();
                                        const href = courseLink.getAttribute('href');

                                        // Create and show overlay
                                        const overlay = document.createElement('div');
                                        overlay.className = 'position-fixed top-0 start-0 w-100 h-100 bg-white';
                                        overlay.style.opacity = '0';
                                        overlay.style.transition = 'opacity 0.3s ease';
                                        overlay.style.zIndex = '9999';

                                        document.body.appendChild(overlay);

                                        // Animate overlay
                                        setTimeout(() => {
                                            overlay.style.opacity = '1';
                                            setTimeout(() => {
                                                window.location.href = href;
                                            }, 300);
                                        }, 10);
                                    }
                                });
                            });
                        </script>
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


<?php include '../includes/student-footer.php'; ?>