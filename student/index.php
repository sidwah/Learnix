<?php include '../includes/signin-header.php'; ?>
<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main">
  <!-- Hero -->
  <div class="position-relative gradient-x-three-sm-primary rounded-2 content-space-t-md-1 content-space-b-md-2 mx-md-10">
    <div class="container position-relative content-space-t-2 content-space-t-lg-3 content-space-b-1">
      <div class="row position-relative zi-2">
        <div class="col-lg-8">
          <!-- Heading -->
          <div class="w-lg-75 text-center text-lg-start mb-5 mb-lg-7">
            <h1 class="display-4">Elevate your skills through world-class
              <span class="text-primary text-highlight-warning">
                <span class="js-typedjs" data-hs-typed-options='{
        "strings": ["education", "training", "mentorship", "development"],
        "typeSpeed": 90,
        "loop": true,
        "backSpeed": 30,
        "backDelay": 2500
      }'></span>
              </span>.
            </h1>
          </div>
          <!-- End Heading -->
          <form>
            <!-- Input Card -->
            <div class="input-card input-card-sm">
              <div class="input-card-form">
                <label for="courseSearchForm" class="form-label visually-hidden">Course, topic, or instructor</label>
                <div class="input-group input-group-merge">
                  <span class="input-group-prepend input-group-text">
                    <i class="bi-search"></i>
                  </span>
                  <input type="text" class="form-control" id="courseSearchForm" placeholder="Course, topic, or instructor" aria-label="Course, topic, or instructor">
                </div>
              </div>
              <div class="input-card-form">
                <label for="categoryForm" class="form-label visually-hidden">Category</label>
                <div class="input-group input-group-merge">
                  <span class="input-group-prepend input-group-text">
                    <i class="bi-grid"></i>
                  </span>
                  <select class="form-control" id="categoryForm" aria-label="Category">
                    <option selected>All Categories</option>
                    <option>Technology</option>
                    <option>Business</option>
                    <option>Health</option>
                    <option>Arts & Humanities</option>
                    <option>Science</option>
                  </select>
                </div>
              </div>
              <button type="button" class="btn btn-primary">Find Courses</button>
            </div>
            <!-- End Input Card -->
          </form>
          <p class="form-text">Explore over 10,000 courses from top instructors</p>
        </div>
        <!-- End Col -->
      </div>
      <!-- End Row -->
      <div class="d-none d-lg-block col-lg-6 position-lg-absolute top-0 end-0">
        <img class="img-fluid rounded-2" src="../assets/img/900x900/img23.jpg" alt="Student Learning">
        <!-- SVG Shape -->
        <div class="position-absolute top-0 start-0 zi-n1 mt-n6 ms-n7" style="width: 10rem;">
          <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 335.2 335.2" width="100" height="100">
            <circle fill="#FFC107" opacity=".7" cx="167.6" cy="167.6" r="130.1" />
          </svg>
        </div>
        <!-- End SVG Shape -->
        <!-- SVG Shape -->
        <div class="position-absolute bottom-0 end-0 zi-n1 mb-n6 me-n10" style="width: 10rem;">
          <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 335.2 335.2" width="120" height="120">
            <circle fill="none" stroke="#377dff" stroke-width="75" cx="167.6" cy="167.6" r="130.1" />
          </svg>
        </div>
        <!-- End SVG Shape -->
        <p class="form-text">Learn at your own pace, anytime, anywhere</p>
      </div>
    </div>
  </div>
  <!-- End Hero -->

  <!-- Dashboard Tabs Section -->
  <div class="container content-space-2 content-space-lg-3">
    <!-- Nav -->
    <div class="text-center">
      <ul class="nav nav-segment nav-pills mb-7" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="pills-my-learning-tab" data-bs-toggle="pill" data-bs-target="#pills-my-learning" type="button" role="tab" aria-controls="pills-my-learning" aria-selected="true">
            <i class="bi-book me-1"></i> My Learning
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="pills-recommended-tab" data-bs-toggle="pill" data-bs-target="#pills-recommended" type="button" role="tab" aria-controls="pills-recommended" aria-selected="false">
            <i class="bi-lightning-charge me-1"></i> Recommended
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="pills-certificates-tab" data-bs-toggle="pill" data-bs-target="#pills-certificates" type="button" role="tab" aria-controls="pills-certificates" aria-selected="false">
            <i class="bi-award me-1"></i> Certificates
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="pills-analytics-tab" data-bs-toggle="pill" data-bs-target="#pills-analytics" type="button" role="tab" aria-controls="pills-analytics" aria-selected="false">
            <i class="bi-graph-up me-1"></i> Analytics
          </button>
        </li>
      </ul>
    </div>
    <!-- End Nav -->

    <!-- Tab Content -->
    <div class="tab-content" id="pills-tabContent">
      <!-- Tab 1: My Learning -->
      <div class="tab-pane fade show active" id="pills-my-learning" role="tabpanel" aria-labelledby="pills-my-learning-tab">
        <!-- In Progress Courses Section -->
        <div class="mb-5">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">In Progress</h3>
            <a class="link" href="enrolled-courses.php">View all <i class="bi-chevron-right small ms-1"></i></a>
          </div>

          <?php if (count($enrolled_courses) > 0): ?>
            <div class="row">
              <?php foreach ($enrolled_courses as $course): ?>
                <?php if ($course['completion_percentage'] < 100): ?>
                  <!-- Course Card -->
                  <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
                    <div class="card h-100">
                      <img class="card-img-top" src="../uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                      <div class="card-body">
                        <h5 class="card-title">
                          <a class="text-dark" href="learn.php?id=<?php echo htmlspecialchars($course['course_id']); ?>">
                            <?php echo htmlspecialchars($course['title']); ?>
                          </a>
                        </h5>
                        <p class="card-text small">Instructor: <?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?></p>

                        <!-- Progress -->
                        <div class="d-flex justify-content-between align-items-center mb-1">
                          <span class="small">Progress</span>
                          <span class="small"><?php echo htmlspecialchars(number_format($course['completion_percentage'], 0)); ?>%</span>
                        </div>
                        <div class="progress mb-3" style="height: 5px;">
                          <div class="progress-bar" role="progressbar" style="width: <?php echo htmlspecialchars($course['completion_percentage']); ?>%" aria-valuenow="<?php echo htmlspecialchars($course['completion_percentage']); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>

                        <!-- Continue Button -->
                        <a href="learn.php?course_id=<?php echo htmlspecialchars($course['course_id']); ?>" class="btn btn-primary btn-sm w-100">Continue Learning</a>
                      </div>
                    </div>
                  </div>
                  <!-- End Course Card -->
                <?php endif; ?>
              <?php endforeach; ?>

              <?php if (!array_filter($enrolled_courses, function ($course) {
                return $course['completion_percentage'] < 100;
              })): ?>
                <div class="col-12">
                  <div class="text-center py-5">
                    <img class="img-fluid mb-3" src="../assets/svg/illustrations/oc-browse.svg" alt="Empty state" width="200">
                    <p>You don't have any courses in progress.</p>
                    <a href="courses.php" class="btn btn-soft-primary">Browse Courses</a>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-5">
              <img class="img-fluid mb-3" src="../assets/svg/illustrations/oc-browse.svg" alt="Empty state" width="200">
              <p>You haven't started any courses yet.</p>
              <a href="courses.php" class="btn btn-soft-primary">Browse Courses</a>
            </div>
            <!-- End Empty State -->
          <?php endif; ?>
        </div>
        <!-- End In Progress Courses Section -->

        <!-- Upcoming Deadlines -->
        <div class="mb-5">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Upcoming Deadlines</h3>
          </div>

          <div class="card">
            <div class="card-body">
              <!-- Timeline -->
              <ul class="step step-icon-sm mb-0">
                <!-- Timeline Item -->
                <li class="step-item">
                  <div class="step-content-wrapper">
                    <span class="step-icon step-icon-soft-primary">
                      <i class="bi-calendar-check"></i>
                    </span>
                    <div class="step-content">
                      <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Quiz: Introduction to Web Development</h5>
                        <small class="text-danger">Due in 3 days</small>
                      </div>
                      <p class="mb-2">Course: Full-Stack Web Development</p>
                      <a href="#" class="btn btn-soft-primary btn-xs">Start Quiz</a>
                    </div>
                  </div>
                </li>
                <!-- End Timeline Item -->

                <!-- Timeline Item -->
                <li class="step-item">
                  <div class="step-content-wrapper">
                    <span class="step-icon step-icon-soft-warning">
                      <i class="bi-journal"></i>
                    </span>
                    <div class="step-content">
                      <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Assignment: Data Visualization Project</h5>
                        <small class="text-danger">Due in 5 days</small>
                      </div>
                      <p class="mb-2">Course: Data Science Fundamentals</p>
                      <a href="#" class="btn btn-soft-warning btn-xs">View Assignment</a>
                    </div>
                  </div>
                </li>
                <!-- End Timeline Item -->
              </ul>
              <!-- End Timeline -->
            </div>
          </div>
        </div>
        <!-- End Upcoming Deadlines -->

        <!-- Recently Completed -->
        <div>
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Recently Completed</h3>
            <a class="link" href="my-achievements.php">View all <i class="bi-chevron-right small ms-1"></i></a>
          </div>

          <?php
          // Filter for completed courses
          $completed_courses = array_filter($enrolled_courses, function ($course) {
            return $course['completion_percentage'] >= 100;
          });
          ?>

          <?php if (!empty($completed_courses)): ?>
            <div class="row">
              <?php foreach ($completed_courses as $course): ?>
                <!-- Completed Course Card -->
                <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
                  <div class="card h-100">
                    <div class="position-relative">
                      <img class="card-img-top" src="../uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                      <span class="badge bg-success position-absolute top-0 end-0 m-3">
                        <i class="bi-check-circle me-1"></i> Completed
                      </span>
                    </div>
                    <div class="card-body">
                      <h5 class="card-title">
                        <a class="text-dark" href="learn.php?id=<?php echo htmlspecialchars($course['course_id']); ?>">
                          <?php echo htmlspecialchars($course['title']); ?>
                        </a>
                      </h5>
                      <p class="card-text small">Instructor: <?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?></p>
                      <div class="d-grid gap-2">
                        <a href="certificates.php?course_id=<?php echo htmlspecialchars($course['course_id']); ?>" class="btn btn-soft-success btn-sm">View Certificate</a>
                        <a href="learn.php?course_id=<?php echo htmlspecialchars($course['course_id']); ?>" class="btn btn-outline-secondary btn-sm">Review Course</a>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- End Completed Course Card -->
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-5">
              <img class="img-fluid mb-3" src="../assets/svg/illustrations/oc-medal.svg" alt="Empty state" width="200">
              <p>You haven't completed any courses yet.</p>
              <a href="enrolled-courses.php" class="btn btn-soft-primary">See Your Courses</a>
            </div>
            <!-- End Empty State -->
          <?php endif; ?>
        </div>
        <!-- End Recently Completed -->
      </div>
      <!-- End Tab 1 -->

      <!-- Tab 2: Recommended Courses -->
      <div class="tab-pane fade" id="pills-recommended" role="tabpanel" aria-labelledby="pills-recommended-tab">
        <!-- Stats Cards -->
        <div class="row mb-5">
          <!-- Stat Card -->
          <div class="col-sm-6 col-lg-3 mb-4">
            <div class="card h-100">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="flex-shrink-0">
                    <span class="avatar avatar-sm avatar-soft-primary avatar-circle">
                      <span class="avatar-initials">
                        <i class="bi-lightning-charge"></i>
                      </span>
                    </span>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h6 class="card-subtitle mb-2">Based on Your Activity</h6>
                    <div class="d-flex align-items-center">
                      <h3 class="mb-0">18</h3>
                      <span class="badge bg-soft-success text-success ms-2">
                        <i class="bi-arrow-up"></i> New
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- End Stat Card -->

          <!-- Stat Card -->
          <div class="col-sm-6 col-lg-3 mb-4">
            <div class="card h-100">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="flex-shrink-0">
                    <span class="avatar avatar-sm avatar-soft-info avatar-circle">
                      <span class="avatar-initials">
                        <i class="bi-chat-dots"></i>
                      </span>
                    </span>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h6 class="card-subtitle mb-2">Career Advancement</h6>
                    <div class="d-flex align-items-center">
                      <h3 class="mb-0">12</h3>
                      <span class="badge bg-soft-success text-success ms-2">
                        <i class="bi-arrow-up"></i> 5
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- End Stat Card -->

          <!-- Stat Card -->
          <div class="col-sm-6 col-lg-3 mb-4">
            <div class="card h-100">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="flex-shrink-0">
                    <span class="avatar avatar-sm avatar-soft-danger avatar-circle">
                      <span class="avatar-initials">
                        <i class="bi-fire"></i>
                      </span>
                    </span>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h6 class="card-subtitle mb-2">Popular Now</h6>
                    <div class="d-flex align-items-center">
                      <h3 class="mb-0">24</h3>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- End Stat Card -->

          <!-- Stat Card -->
          <div class="col-sm-6 col-lg-3 mb-4">
            <div class="card h-100">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="flex-shrink-0">
                    <span class="avatar avatar-sm avatar-soft-success avatar-circle">
                      <span class="avatar-initials">
                        <i class="bi-star"></i>
                      </span>
                    </span>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h6 class="card-subtitle mb-2">Top Rated</h6>
                    <div class="d-flex align-items-center">
                      <h3 class="mb-0">15</h3>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- End Stat Card -->
        </div>
        <!-- End Stats Cards -->

        <!-- Recommended Courses Grid -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 mb-5">
          <!-- Course Card 1 -->
          <div class="col mb-4">
            <div class="card h-100">
              <img class="card-img-top" src="../assets/img/400x500/img8.jpg" alt="Card image cap">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <span class="badge bg-soft-primary text-primary">Web Development</span>
                  <span class="d-block text-muted small">24 lessons</span>
                </div>
                <h5 class="card-title"><a class="text-dark" href="#">Modern JavaScript from the Beginning</a></h5>
                <p class="card-text">Learn modern JavaScript practices and build projects along the way.</p>
              </div>
              <div class="card-footer">
                <div class="d-flex align-items-center">
                  <div class="avatar-group avatar-group-xs me-3">
                    <span class="avatar avatar-circle">
                      <img class="avatar-img" src="../assets/img/160x160/img3.jpg" alt="Image Description">
                    </span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center flex-grow-1">
                    <span class="card-text">John Smith</span>
                    <div class="d-flex align-items-center">
                      <span class="me-1"><i class="bi-star-fill text-warning"></i></span>
                      <span>4.85</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- End Course Card 1 -->
        </div>
        <!-- End Recommended Courses Grid -->

        <!-- View All Button -->
        <div class="text-center">
          <a class="btn btn-outline-primary" href="#">See all recommendations <i class="bi-chevron-right small ms-1"></i></a>
        </div>
        <!-- End View All Button -->
      </div>
      <!-- End Tab 2 -->

      <!-- Tab 3: Certificates -->
      <div class="tab-pane fade" id="pills-certificates" role="tabpanel" aria-labelledby="pills-certificates-tab">
        <!-- Earned Certificates Section -->
        <div class="mb-5">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">Your Earned Certificates</h3>
          </div>

          <?php
          // Query for certificates
          // In a real application, you'd query the certificates from the database
          $hasCertificates = !empty($completed_courses);
          ?>

          <?php if ($hasCertificates): ?>
            <div class="row">
              <?php foreach ($completed_courses as $course): ?>
                <!-- Certificate Card -->
                <div class="col-sm-6 col-lg-4 mb-4">
                  <div class="card h-100">
                    <div class="card-pinned">
                      <img class="card-img-top" src="../assets/img/700x400/img1.jpg" alt="Certificate">
                      <div class="card-pinned-top-end">
                        <div class="dropdown">
                          <button type="button" class="btn btn-ghost-secondary btn-icon btn-sm rounded-circle" id="dropdownMenuButtonCert1" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi-three-dots-vertical"></i>
                          </button>
                          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButtonCert1">
                            <a class="dropdown-item" href="#"><i class="bi-download dropdown-item-icon"></i> Download PDF</a>
                            <a class="dropdown-item" href="#"><i class="bi-share dropdown-item-icon"></i> Share</a>
                            <a class="dropdown-item" href="#"><i class="bi-linkedin dropdown-item-icon"></i> Add to LinkedIn</a>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="card-body">
                      <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                      <p class="card-text small">Earned on: <?php echo date('F j, Y', strtotime($course['enrolled_at'] . ' + 30 days')); ?></p>
                      <div class="d-flex align-items-center">
                        <img class="avatar avatar-xss me-2" src="../assets/svg/brands/learnix-icon.svg" alt="Learnix">
                        <span class="small">Issued by Learnix</span>
                      </div>
                    </div>
                    <div class="card-footer">
                      <div class="row align-items-center">
                        <div class="col">
                          <a href="#" class="btn btn-primary btn-sm">View Certificate</a>
                        </div>
                        <div class="col-auto">
                          <span class="badge bg-soft-success text-success">Verified</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- End Certificate Card -->
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-5">
              <img class="img-fluid mb-3" src="../assets/svg/illustrations/oc-certificate.svg" alt="No certificates" width="200">
              <h5>No certificates yet</h5>
              <p>Complete a course to earn your first certificate.</p>
              <a href="enrolled-courses.php" class="btn btn-soft-primary">Continue Learning</a>
            </div>
            <!-- End Empty State -->
          <?php endif; ?>
        </div>
        <!-- End Earned Certificates Section -->

        <!-- Professional Certifications Section -->
        <div>
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">Recommended Certification Paths</h3>
          </div>

          <div class="row">
            <!-- Certification Path Card -->
            <div class="col-md-6 col-xl-4 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="d-flex align-items-center mb-3">
                    <span class="flex-shrink-0 avatar avatar-lg avatar-4x3 me-3">
                      <img class="avatar-img" src="../assets/svg/brands/google-webdev.svg" alt="Image Description">
                    </span>
                    <div>
                      <h4 class="mb-1">Full-Stack Web Development</h4>
                      <span class="d-block text-body">5 Courses • 6 Months</span>
                    </div>
                  </div>

                  <p class="card-text">Master modern web development with HTML, CSS, JavaScript, React, Node.js, and MongoDB.</p>

                  <ul class="list-checked list-checked-primary mb-0">
                    <li class="list-checked-item">Professional Certificate</li>
                    <li class="list-checked-item">Beginner Friendly</li>
                    <li class="list-checked-item">Projects Included</li>
                  </ul>
                </div>
                <div class="card-footer pt-0">
                  <div class="d-grid">
                    <a class="btn btn-outline-primary" href="#">View Details</a>
                  </div>
                </div>
              </div>
            </div>
            <!-- End Certification Path Card -->

            <!-- Certification Path Card -->
            <div class="col-md-6 col-xl-4 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="d-flex align-items-center mb-3">
                    <span class="flex-shrink-0 avatar avatar-lg avatar-4x3 me-3">
                      <img class="avatar-img" src="../assets/svg/brands/data-analysis.svg" alt="Image Description">
                    </span>
                    <div>
                      <h4 class="mb-1">Data Science Professional</h4>
                      <span class="d-block text-body">8 Courses • 8 Months</span>
                    </div>
                  </div>

                  <p class="card-text">Become a data scientist with Python, pandas, machine learning, and visualization skills.</p>

                  <ul class="list-checked list-checked-primary mb-0">
                    <li class="list-checked-item">Professional Certificate</li>
                    <li class="list-checked-item">Intermediate Level</li>
                    <li class="list-checked-item">Capstone Project</li>
                  </ul>
                </div>
                <div class="card-footer pt-0">
                  <div class="d-grid">
                    <a class="btn btn-outline-primary" href="#">View Details</a>
                  </div>
                </div>
              </div>
            </div>
            <!-- End Certification Path Card -->

            <!-- Certification Path Card -->
            <div class="col-md-6 col-xl-4 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="d-flex align-items-center mb-3">
                    <span class="flex-shrink-0 avatar avatar-lg avatar-4x3 me-3">
                      <img class="avatar-img" src="../assets/svg/brands/cybersecurity.svg" alt="Image Description">
                    </span>
                    <div>
                      <h4 class="mb-1">Cybersecurity Specialist</h4>
                      <span class="d-block text-body">6 Courses • 7 Months</span>
                    </div>
                  </div>

                  <p class="card-text">Learn network security, ethical hacking, and cyber defense techniques.</p>

                  <ul class="list-checked list-checked-primary mb-0">
                    <li class="list-checked-item">Industry Certification</li>
                    <li class="list-checked-item">Advanced Level</li>
                    <li class="list-checked-item">Hands-on Labs</li>
                  </ul>
                </div>
                <div class="card-footer pt-0">
                  <div class="d-grid">
                    <a class="btn btn-outline-primary" href="#">View Details</a>
                  </div>
                </div>
              </div>
            </div>
            <!-- End Certification Path Card -->
          </div>
        </div>
        <!-- End Professional Certifications Section -->
      </div>
      <!-- End Tab 3 -->

      <!-- Tab 4: Analytics -->
      <div class="tab-pane fade" id="pills-analytics" role="tabpanel" aria-labelledby="pills-analytics-tab">
        <!-- Learning Activity Overview -->
        <div class="row mb-5">
          <div class="col-lg-8 mb-4 mb-lg-0">
            <div class="card h-100">
              <div class="card-header">
                <h5 class="card-header-title">Learning Activity</h5>
                <div class="dropdown">
                  <button class="btn btn-ghost-secondary btn-sm" type="button" id="learningActivityDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <span>Last 30 days</span>
                    <i class="bi-chevron-down ms-1"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-end" aria-labelledby="learningActivityDropdown">
                    <a class="dropdown-item" href="#">Last 7 days</a>
                    <a class="dropdown-item active" href="#">Last 30 days</a>
                    <a class="dropdown-item" href="#">Last 3 months</a>
                    <a class="dropdown-item" href="#">Last 12 months</a>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <!-- Activity Chart -->
                <div class="chartjs-custom mx-auto">
                  <div style="height: 18rem; position: relative;">
                    <!-- Placeholder for chart -->
                    <div class="d-flex flex-column align-items-center justify-content-center h-100">
                      <img class="mb-3" src="../assets/svg/illustrations/oc-chart.svg" alt="Chart Placeholder" style="width: 10rem;">
                      <p class="card-text">Your daily learning activity will appear here</p>
                    </div>
                  </div>
                </div>
                <!-- End Activity Chart -->
              </div>
              <div class="card-footer pt-0">
                <div class="row justify-content-center">
                  <div class="col-auto">
                    <span class="legend-indicator bg-primary"></span>
                    <span>Hours Studied</span>
                  </div>
                  <div class="col-auto">
                    <span class="legend-indicator bg-info"></span>
                    <span>Lessons Completed</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="row">
              <!-- Stat Card: Study Streak -->
              <div class="col-sm-6 col-lg-12 mb-4">
                <div class="card h-100">
                  <div class="card-body">
                    <div class="d-flex align-items-center">
                      <div class="flex-shrink-0">
                        <span class="avatar avatar-sm avatar-soft-primary avatar-circle">
                          <span class="avatar-initials">
                            <i class="bi-calendar4-week"></i>
                          </span>
                        </span>
                      </div>
                      <div class="flex-grow-1 ms-3">
                        <h6 class="card-subtitle mb-1">Current streak</h6>
                        <div class="d-flex align-items-center">
                          <h2 class="mb-0 me-2">5</h2>
                          <span class="badge bg-soft-success text-success">
                            <i class="bi-fire"></i> days
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- End Stat Card -->

              <!-- Stat Card: Total Hours -->
              <div class="col-sm-6 col-lg-12 mb-4">
                <div class="card h-100">
                  <div class="card-body">
                    <div class="d-flex align-items-center">
                      <div class="flex-shrink-0">
                        <span class="avatar avatar-sm avatar-soft-info avatar-circle">
                          <span class="avatar-initials">
                            <i class="bi-clock-history"></i>
                          </span>
                        </span>
                      </div>
                      <div class="flex-grow-1 ms-3">
                        <h6 class="card-subtitle mb-1">Total study time</h6>
                        <div class="d-flex align-items-center">
                          <h2 class="mb-0 me-2">24.5</h2>
                          <span class="badge bg-soft-info text-info">
                            hours
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- End Stat Card -->
            </div>
          </div>
        </div>
        <!-- End Learning Activity Overview -->

        <!-- Weekly Progress -->
        <div class="mb-5">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Weekly Progress</h3>
          </div>

          <div class="card">
            <div class="card-body">
              <div class="row">
                <!-- Week Day -->
                <div class="col text-center">
                  <div class="mb-3">
                    <span class="display-6">M</span>
                  </div>
                  <div class="avatar avatar-circle avatar-md" style="background-color: #e7f0fd;">
                    <span class="avatar-initials" style="color: #377dff;">1</span>
                  </div>
                  <p class="small mt-2 mb-0">hr</p>
                </div>
                <!-- End Week Day -->

                <!-- Week Day -->
                <div class="col text-center">
                  <div class="mb-3">
                    <span class="display-6">T</span>
                  </div>
                  <div class="avatar avatar-circle avatar-md" style="background-color: #e7f0fd;">
                    <span class="avatar-initials" style="color: #377dff;">2</span>
                  </div>
                  <p class="small mt-2 mb-0">hrs</p>
                </div>
                <!-- End Week Day -->

                <!-- Week Day -->
                <div class="col text-center">
                  <div class="mb-3">
                    <span class="display-6">W</span>
                  </div>
                  <div class="avatar avatar-circle avatar-md bg-primary">
                    <span class="avatar-initials text-white">3</span>
                  </div>
                  <p class="small mt-2 mb-0">hrs</p>
                </div>
                <!-- End Week Day -->

                <!-- Week Day -->
                <div class="col text-center">
                  <div class="mb-3">
                    <span class="display-6">T</span>
                  </div>
                  <div class="avatar avatar-circle avatar-md" style="background-color: #e7f0fd;">
                    <span class="avatar-initials" style="color: #377dff;">0</span>
                  </div>
                  <p class="small mt-2 mb-0">hr</p>
                </div>
                <!-- End Week Day -->

                <!-- Week Day -->
                <div class="col text-center">
                  <div class="mb-3">
                    <span class="display-6">F</span>
                  </div>
                  <div class="avatar avatar-circle avatar-md" style="background-color: #e7f0fd;">
                    <span class="avatar-initials" style="color: #377dff;">1.5</span>
                  </div>
                  <p class="small mt-2 mb-0">hrs</p>
                </div>
                <!-- End Week Day -->

                <!-- Week Day -->
                <div class="col text-center">
                  <div class="mb-3">
                    <span class="display-6">S</span>
                  </div>
                  <div class="avatar avatar-circle avatar-md bg-primary">
                    <span class="avatar-initials text-white">2</span>
                  </div>
                  <p class="small mt-2 mb-0">hrs</p>
                </div>
                <!-- End Week Day -->

                <!-- Week Day -->
                <div class="col text-center">
                  <div class="mb-3">
                    <span class="display-6">S</span>
                  </div>
                  <div class="avatar avatar-circle avatar-md">
                    <span class="avatar-initials" style="color: #677788;">-</span>
                  </div>
                  <p class="small mt-2 mb-0">Today</p>
                </div>
                <!-- End Week Day -->
              </div>
            </div>
            <div class="card-footer">
              <div class="row align-items-center">
                <div class="col-sm">
                  <span class="d-block">This week: <span class="text-dark fw-semibold">9.5 hours</span></span>
                  <span class="d-block text-muted small">Goal: 12 hours per week</span>
                </div>
                <div class="col-sm-auto mt-3 mt-sm-0">
                  <div class="progress" style="height: 8px; width: 200px;">
                    <div class="progress-bar" role="progressbar" style="width: 79.2%;" aria-valuenow="79.2" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- End Weekly Progress -->

        <!-- Skills Progress -->
        <div>
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Skills Development</h3>
          </div>

          <div class="row row-cols-1 row-cols-md-2 g-4 mb-5">
            <!-- Skill Card -->
            <div class="col">
              <div class="card h-100">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Web Development</h4>
                    <span class="badge bg-soft-primary text-primary">Intermediate</span>
                  </div>

                  <!-- Skill Bars -->
                  <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                      <span>HTML/CSS</span>
                      <span>85%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                      <div class="progress-bar" role="progressbar" style="width: 85%;" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                  </div>

                  <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                      <span>JavaScript</span>
                      <span>68%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                      <div class="progress-bar" role="progressbar" style="width: 68%;" aria-valuenow="68" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                  </div>

                  <div class="mb-0">
                    <div class="d-flex justify-content-between mb-1">
                      <span>Responsive Design</span>
                      <span>92%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                      <div class="progress-bar" role="progressbar" style="width: 92%;" aria-valuenow="92" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                  </div>
                  <!-- End Skill Bars -->
                </div>
                <div class="card-footer">
                  <div class="d-flex justify-content-between align-items-center">
                    <span>5 courses in progress</span>
                    <a href="#" class="btn btn-soft-primary btn-xs">View details</a>
                  </div>
                </div>
              </div>
            </div>
            <!-- End Skill Card -->

            <!-- Skill Card -->
            <div class="col">
              <div class="card h-100">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Data Science</h4>
                    <span class="badge bg-soft-warning text-warning">Beginner</span>
                  </div>

                  <!-- Skill Bars -->
                  <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                      <span>Python</span>
                      <span>42%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                      <div class="progress-bar" role="progressbar" style="width: 42%;" aria-valuenow="42" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                  </div>

                  <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                      <span>Data Visualization</span>
                      <span>35%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                      <div class="progress-bar" role="progressbar" style="width: 35%;" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                  </div>

                  <div class="mb-0">
                    <div class="d-flex justify-content-between mb-1">
                      <span>Machine Learning</span>
                      <span>10%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                      <div class="progress-bar" role="progressbar" style="width: 10%;" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                  </div>
                  <!-- End Skill Bars -->
                </div>
                <div class="card-footer">
                  <div class="d-flex justify-content-between align-items-center">
                    <span>2 courses in progress</span>
                    <a href="#" class="btn btn-soft-primary btn-xs">View details</a>
                  </div>
                </div>
              </div>
            </div>
            <!-- End Skill Card -->
          </div>

          <!-- Achievements -->
          <div class="card">
            <div class="card-header">
              <h5 class="card-header-title">Your Achievements</h5>
            </div>
            <div class="card-body">
              <div class="row">
                <!-- Achievement -->
                <div class="col-6 col-md-3 text-center mb-4 mb-md-0">
                  <div class="avatar avatar-lg avatar-circle mb-3 mx-auto">
                    <img class="avatar-img" src="../assets/svg/illustrations/oc-medal.svg" alt="Perfect Attendance">
                  </div>
                  <h6>5-Day Streak</h6>
                  <p class="small text-muted mb-0">Learn 5 days in a row</p>
                </div>
                <!-- End Achievement -->

                <!-- Achievement -->
                <div class="col-6 col-md-3 text-center mb-4 mb-md-0">
                  <div class="avatar avatar-lg avatar-soft-primary avatar-circle mb-3 mx-auto">
                    <span class="avatar-initials">
                      <i class="bi-clock"></i>
                    </span>
                  </div>
                  <h6>First 10 Hours</h6>
                  <p class="small text-muted mb-0">Complete 10 hours of learning</p>
                </div>
                <!-- End Achievement -->

                <!-- Achievement -->
                <div class="col-6 col-md-3 text-center">
                  <div class="avatar avatar-lg avatar-soft-info avatar-circle mb-3 mx-auto">
                    <span class="avatar-initials">
                      <i class="bi-book"></i>
                    </span>
                  </div>
                  <h6>First Course</h6>
                  <p class="small text-muted mb-0">Complete your first course</p>
                </div>
                <!-- End Achievement -->

                <!-- Achievement -->
                <div class="col-6 col-md-3 text-center">
                  <div class="avatar avatar-lg avatar-soft-secondary avatar-circle avatar-bordered mb-3 mx-auto">
                    <span class="avatar-initials">
                      <i class="bi-trophy"></i>
                    </span>
                  </div>
                  <h6>Quiz Master</h6>
                  <p class="small text-muted mb-0">Score 100% on 5 quizzes</p>
                </div>
                <!-- End Achievement -->
              </div>
            </div>
            <div class="card-footer text-center">
              <a class="btn btn-outline-primary btn-sm" href="#">View all achievements</a>
            </div>
          </div>
          <!-- End Achievements -->
        </div>
        <!-- End Skills Progress -->
      </div>
      <!-- End Tab 4 -->
    </div>
    <!-- End Tab Content -->
  </div>
  <!-- End Dashboard Tabs Section -->

  <!-- Create Collection Modal -->
  <div class="modal fade" id="createCollectionModal" tabindex="-1" aria-labelledby="createCollectionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="createCollectionModalLabel">Create New Collection</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form>
            <!-- Form Group -->
            <div class="mb-4">
              <label for="collectionNameLabel" class="form-label">Collection Name</label>
              <input type="text" class="form-control" id="collectionNameLabel" placeholder="Enter collection name" aria-label="Enter collection name">
            </div>
            <!-- End Form Group -->

            <!-- Form Group -->
            <div class="mb-4">
              <label for="collectionDescriptionLabel" class="form-label">Description (optional)</label>
              <textarea class="form-control" id="collectionDescriptionLabel" placeholder="Enter a short description" rows="3"></textarea>
            </div>
            <!-- End Form Group -->

            <!-- Form Check -->
            <div class="mb-4">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="privateCollectionCheck">
                <label class="form-check-label" for="privateCollectionCheck">
                  Make this collection private
                </label>
              </div>
            </div>
            <!-- End Form Check -->
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary">Create Collection</button>
        </div>
      </div>
    </div>
  </div>
  <!-- End Create Collection Modal -->
</main>
<!-- ========== END MAIN CONTENT ========== -->
<?php include '../includes/student-footer.php'; ?>