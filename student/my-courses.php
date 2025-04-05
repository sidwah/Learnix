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
                                <div class="d-flex">
                                    <div class="me-3 text-center">
                                        <div class="fs-5 fw-bold text-primary"><?php echo rand(5, 20); ?></div>
                                        <div class="small text-muted">Active Courses</div>
                                    </div>
                                    <div class="me-3 text-center">
                                        <div class="fs-5 fw-bold text-success"><?php echo rand(1, 15); ?></div>
                                        <div class="small text-muted">Completed</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="fs-5 fw-bold text-warning"><?php echo rand(5, 50); ?>h</div>
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
                                            <li><a class="dropdown-item" href="#" data-filter="favorite"><i class="bi bi-heart me-2"></i>Favorites</a></li>
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

                            <!-- Recently Accessed Section -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">Continue Learning</h5>
                                    <a href="#" class="small text-decoration-none">View all</a>
                                </div>
                                <div class="row g-3">
                                    <!-- Sample Recently Accessed Course Cards -->
                                    <div class="col-md-6 col-lg-3">
                                        <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                                            <div class="card-img-top position-relative">
                                                <img src="../uploads/thumbnails/default.jpg" class="card-img-top" alt="Course thumbnail">
                                                <div class="badge bg-primary position-absolute top-0 start-0 m-2">New</div>
                                            </div>
                                            <div class="card-body">
                                                <h6 class="card-title mb-1">Advanced JavaScript Patterns</h6>
                                                <p class="small text-muted mb-2">Last accessed: 2 hours ago</p>
                                                <div class="progress mb-3" style="height: 4px;">
                                                    <div class="progress-bar bg-warning" style="width: 45%"></div>
                                                </div>
                                                <a href="#" class="btn btn-sm btn-outline-primary w-100">Continue</a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-3">
                                        <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                                            <div class="card-img-top position-relative">
                                                <img src="../uploads/thumbnails/default.jpg" class="card-img-top" alt="Course thumbnail">
                                                <div class="badge bg-success position-absolute top-0 start-0 m-2">Popular</div>
                                            </div>
                                            <div class="card-body">
                                                <h6 class="card-title mb-1">UX Design Fundamentals</h6>
                                                <p class="small text-muted mb-2">Last accessed: 1 day ago</p>
                                                <div class="progress mb-3" style="height: 4px;">
                                                    <div class="progress-bar bg-info" style="width: 78%"></div>
                                                </div>
                                                <a href="#" class="btn btn-sm btn-outline-primary w-100">Continue</a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-3">
                                        <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                                            <div class="card-img-top position-relative">
                                                <img src="../uploads/thumbnails/default.jpg" class="card-img-top" alt="Course thumbnail">
                                            </div>
                                            <div class="card-body">
                                                <h6 class="card-title mb-1">Python Data Analysis</h6>
                                                <p class="small text-muted mb-2">Last accessed: 3 days ago</p>
                                                <div class="progress mb-3" style="height: 4px;">
                                                    <div class="progress-bar bg-danger" style="width: 22%"></div>
                                                </div>
                                                <a href="#" class="btn btn-sm btn-outline-primary w-100">Continue</a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-3">
                                        <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                                            <div class="card-img-top position-relative">
                                                <img src="../uploads/thumbnails/default.jpg" class="card-img-top" alt="Course thumbnail">
                                                <div class="badge bg-info position-absolute top-0 start-0 m-2">Updated</div>
                                            </div>
                                            <div class="card-body">
                                                <h6 class="card-title mb-1">Cloud Architecture</h6>
                                                <p class="small text-muted mb-2">Last accessed: 1 week ago</p>
                                                <div class="progress mb-3" style="height: 4px;">
                                                    <div class="progress-bar bg-success" style="width: 95%"></div>
                                                </div>
                                                <a href="#" class="btn btn-sm btn-outline-primary w-100">Continue</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- All Courses Section -->
                            <div class="mt-5">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">My Courses</h5>
                                    <div class="small text-muted">Showing <?php echo $result->num_rows; ?> courses</div>
                                </div>

                                <div class="row g-4" id="coursesContainer">
                                    <?php
                                    if ($result->num_rows > 0) {
                                        while ($course = $result->fetch_assoc()) {
                                            $progress = round($course['completion_percentage']);
                                            $statusClass = $progress == 100 ? 'bg-success' : ($progress > 70 ? 'bg-primary' : ($progress > 30 ? 'bg-info' : 'bg-warning'));
                                    ?>
                                            <div class="col-md-6 col-lg-4 col-xl-3 course-card"
                                                data-status="<?php echo $course['enrollment_status']; ?>"
                                                data-favorite="0"
                                                data-progress="<?php echo $progress; ?>">
                                                <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                                                    <div class="card-img-top position-relative">
                                                        <img src="../uploads/thumbnails/<?php echo $course['thumbnail']; ?>" class="card-img-top" alt="<?php echo $course['title']; ?>">
                                                        <div class="position-absolute top-0 end-0 m-2">
                                                            <button class="btn btn-sm btn-icon btn-light rounded-circle btn-favorite" data-course-id="<?php echo $course['course_id']; ?>">
                                                                <i class="bi-heart"></i>
                                                            </button>
                                                        </div>
                                                        <?php if ($progress == 100): ?>
                                                            <div class="position-absolute top-0 start-0 m-2">
                                                                <span class="badge bg-success">Completed</span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <h6 class="card-title mb-0 flex-grow-1"><?php echo $course['title']; ?></h6>
                                                            <span class="badge bg-light text-dark small ms-2"><?php echo rand(1, 5); ?> <i class="bi-star-fill text-warning small"></i></span>
                                                        </div>

                                                        <p class="small text-muted mb-3"><?php echo substr($course['short_description'], 0, 80); ?>...</p>

                                                        <div class="d-flex justify-content-between align-items-center mb-2 small">
                                                            <span>Progress</span>
                                                            <span class="fw-bold"><?php echo $progress; ?>%</span>
                                                        </div>
                                                        <div class="progress mb-3" style="height: 6px;">
                                                            <div class="progress-bar <?php echo $statusClass; ?>" style="width: <?php echo $progress; ?>%"></div>
                                                        </div>

                                                        <div class="d-flex justify-content-between">
                                                            <a href="learn.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-sm <?php echo $progress == 100 ? 'btn-outline-success' : 'btn-primary'; ?> flex-grow-1 me-2">
                                                                <?php echo $progress == 100 ? 'Review' : 'Continue'; ?>
                                                            </a>
                                                            <a href="course-overview.php?id=<?php echo $course['course_id']; ?>" class="btn btn-sm btn-outline-secondary" title="Details">
                                                                <i class="bi-three-dots"></i>
                                                            </a>
                                                        </div>
                                                    </div>

                                                    <div class="card-footer bg-transparent small text-muted border-0 pt-0">
                                                        <div class="d-flex justify-content-between">
                                                            <span><i class="bi-calendar me-1"></i> <?php echo date('M d, Y', strtotime($course['enrolled_at'])); ?></span>
                                                            <span><i class="bi-clock me-1"></i> <?php echo rand(1, 10); ?>h</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                        }
                                    } else {
                                        ?>
                                        <div class="col-12 text-center py-5">
                                            <div class="mb-4">
                                                <i class="bi-book display-4 text-muted opacity-25"></i>
                                            </div>
                                            <h4 class="mb-3">No courses enrolled yet</h4>
                                            <p class="text-muted mb-4">Discover our catalog and start your learning journey today</p>
                                            <a href="courses.php" class="btn btn-primary px-4">
                                                <i class="bi-search me-2"></i>Browse Courses
                                            </a>
                                        </div>
                                    <?php
                                    }
                                    ?>
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
                                                (filter === 'completed' && parseFloat(card.dataset.progress) === 100) ||
                                                (filter === 'favorite' && card.dataset.favorite === '1');

                                            card.style.opacity = '0';
                                            card.style.transform = 'translateY(20px)';

                                            setTimeout(() => {
                                                card.style.display = shouldShow ? '' : 'none';
                                                card.style.opacity = '';
                                                card.style.transform = '';
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
                                        courseCards.forEach((card, index) => {
                                            setTimeout(() => {
                                                coursesContainer.appendChild(card);
                                                card.style.opacity = '0';
                                                card.style.transform = 'translateY(20px)';

                                                setTimeout(() => {
                                                    card.style.opacity = '';
                                                    card.style.transform = '';
                                                }, 50);
                                            }, index * 50);
                                        });
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
                                        const coursesContainer = document.getElementById('coursesContainer');

                                        // Add transition class
                                        coursesContainer.classList.add('transition-all');

                                        setTimeout(() => {
                                            courseCards.forEach(card => {
                                                card.classList.remove('col-md-6', 'col-lg-4', 'col-xl-3', 'col-12');

                                                if (view === 'list') {
                                                    card.classList.add('col-12');
                                                    // Add list view specific modifications
                                                    card.querySelector('.card').classList.add('flex-row');
                                                    card.querySelector('.card-img-top').classList.add('w-25');
                                                    card.querySelector('.card-body').classList.add('flex-grow-1');
                                                } else {
                                                    card.classList.add('col-md-6', 'col-lg-4', 'col-xl-3');
                                                    // Reset grid view
                                                    card.querySelector('.card').classList.remove('flex-row');
                                                    card.querySelector('.card-img-top').classList.remove('w-25');
                                                    card.querySelector('.card-body').classList.remove('flex-grow-1');
                                                }
                                            });

                                            // Remove transition class after layout change
                                            setTimeout(() => {
                                                coursesContainer.classList.remove('transition-all');
                                            }, 300);
                                        }, 50);
                                    });
                                });

                                // Favorite functionality with animation
                                document.querySelectorAll('.btn-favorite').forEach(button => {
                                    button.addEventListener('click', function() {
                                        const icon = this.querySelector('i');
                                        const card = this.closest('.course-card');

                                        if (icon.classList.contains('bi-heart')) {
                                            // Add to favorites
                                            icon.classList.remove('bi-heart');
                                            icon.classList.add('bi-heart-fill', 'text-danger');
                                            card.dataset.favorite = '1';

                                            // Add bounce animation
                                            this.classList.add('animate__animated', 'animate__bounce');
                                            setTimeout(() => {
                                                this.classList.remove('animate__animated', 'animate__bounce');
                                            }, 1000);
                                        } else {
                                            // Remove from favorites
                                            icon.classList.remove('bi-heart-fill', 'text-danger');
                                            icon.classList.add('bi-heart');
                                            card.dataset.favorite = '0';
                                        }

                                        // AJAX call to update favorite status would go here
                                    });
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