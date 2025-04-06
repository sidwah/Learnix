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
            <a class="link" href="my-courses.php">View all <i class="bi-chevron-right small ms-1"></i></a>
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
                  <!-- Bootstrap Icon Example -->
                  <div class="text-center py-5">
                    <div class="mb-3">
                      <i class="bi bi-journal-bookmark-fill" style="font-size: 4rem; color: #0d6efd;"></i>
                    </div>
                    <p>You don't have any courses in progress.</p>
                    <a href="courses.php" class="btn btn-soft-primary">Browse Courses</a>
                  </div>

                </div>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-5">
              <i class="bi bi-book fs-1 text-primary mb-3"></i> <!-- Bootstrap icon -->
              <p>You haven't started any courses yet.</p>
              <a href="courses.php" class="btn btn-soft-primary">Browse Courses</a>
            </div>

            <!-- End Empty State -->
          <?php endif; ?>
        </div>
        <!-- End In Progress Courses Section -->


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
              <i class="bi bi-award fs-1 text-primary mb-3"></i> <!-- Bootstrap icon -->
              <p>You haven't completed any courses yet.</p>
              <a href="my-courses.php" class="btn btn-soft-primary">See Your Courses</a>
            </div>

            <!-- End Empty State -->
          <?php endif; ?>
        </div>
        <!-- End Recently Completed -->
      </div>
      <!-- End Tab 1 -->


      <!-- Tab 2: Recommended Courses -->
      <?php
      // Fetch recommended courses based on user activity (courses in same categories as enrolled courses)
      $activity_based_query = "
SELECT DISTINCT c.course_id, c.title, c.thumbnail, c.short_description, c.price, 
       u.first_name, u.last_name, u.profile_pic,
       COALESCE(AVG(cr.rating), 0) as avg_rating,
       (SELECT COUNT(*) FROM section_topics st JOIN course_sections cs ON st.section_id = cs.section_id WHERE cs.course_id = c.course_id) as lesson_count
FROM courses c
JOIN instructors i ON c.instructor_id = i.instructor_id
JOIN users u ON i.user_id = u.user_id
LEFT JOIN course_ratings cr ON c.course_id = cr.course_id
WHERE c.subcategory_id IN (
    SELECT DISTINCT c2.subcategory_id
    FROM enrollments e
    JOIN courses c2 ON e.course_id = c2.course_id
    WHERE e.user_id = ?
)
AND c.course_id NOT IN (
    SELECT course_id FROM enrollments WHERE user_id = ?
)
AND c.status = 'Published'
AND c.approval_status = 'Approved'
GROUP BY c.course_id
ORDER BY avg_rating DESC
LIMIT 4
";

      $stmt = $conn->prepare($activity_based_query);
      $stmt->bind_param("ii", $user_id, $user_id);
      $stmt->execute();
      $activity_based_courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

      // Get activity count from results
      $activity_count = count($activity_based_courses);

      // Fetch career-focused courses count
      $career_count_query = "
SELECT COUNT(DISTINCT c.course_id) as count
FROM courses c
JOIN course_tag_mapping ctm ON c.course_id = ctm.course_id
JOIN tags t ON ctm.tag_id = t.tag_id
WHERE c.course_id NOT IN (
    SELECT course_id FROM enrollments WHERE user_id = ?
)
AND c.status = 'Published'
AND c.approval_status = 'Approved'
AND (
    t.tag_name IN ('Career Development', 'Leadership', 'Management', 'Professional Skills')
    OR c.title LIKE '%career%' OR c.title LIKE '%professional%'
)
";

      $stmt = $conn->prepare($career_count_query);
      $stmt->bind_param("i", $user_id);
      $stmt->execute();
      $career_count_result = $stmt->get_result()->fetch_assoc();
      $career_count = $career_count_result ? $career_count_result['count'] : 0;

      // Fetch popular courses count (courses with most enrollments)
      $popular_count_query = "
SELECT COUNT(DISTINCT c.course_id) as count
FROM courses c
JOIN enrollments e ON c.course_id = e.course_id
WHERE c.course_id NOT IN (
    SELECT course_id FROM enrollments WHERE user_id = ?
)
AND c.status = 'Published'
AND c.approval_status = 'Approved'
GROUP BY c.course_id
HAVING COUNT(e.enrollment_id) > 0
";

      $stmt = $conn->prepare($popular_count_query);
      $stmt->bind_param("i", $user_id);
      $stmt->execute();
      $result = $stmt->get_result();
      $popular_count = $result->num_rows;

      // Fetch top-rated courses count
      $top_rated_count_query = "
SELECT COUNT(DISTINCT c.course_id) as count
FROM courses c
JOIN course_ratings cr ON c.course_id = cr.course_id
WHERE c.course_id NOT IN (
    SELECT course_id FROM enrollments WHERE user_id = ?
)
AND c.status = 'Published'
AND c.approval_status = 'Approved'
GROUP BY c.course_id
HAVING AVG(cr.rating) >= 4
";

      $stmt = $conn->prepare($top_rated_count_query);
      $stmt->bind_param("i", $user_id);
      $stmt->execute();
      $result = $stmt->get_result();
      $top_rated_count = $result->num_rows;

      // If any count is 0, use the number of published courses to show something
      if ($career_count == 0 || $popular_count == 0 || $top_rated_count == 0) {
        $published_count_query = "
    SELECT COUNT(*) as count
    FROM courses
    WHERE status = 'Published' 
    AND approval_status = 'Approved'
    AND course_id NOT IN (
        SELECT course_id FROM enrollments WHERE user_id = ?
    )
    ";

        $stmt = $conn->prepare($published_count_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $published_count_result = $stmt->get_result()->fetch_assoc();
        $published_count = $published_count_result ? $published_count_result['count'] : 0;

        // Use published count for any zero values
        if ($career_count == 0) $career_count = min($published_count, 12);
        if ($popular_count == 0) $popular_count = min($published_count, 24);
        if ($top_rated_count == 0) $top_rated_count = min($published_count, 15);
      }
      ?>
      <div class="tab-pane fade" id="pills-recommended" role="tabpanel" aria-labelledby="pills-recommended-tab">
        <!-- Stats Cards -->
        <div class="row mb-5">
          <!-- Activity Based Recommendations -->
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
                      <h3 class="mb-0"><?php echo $activity_count; ?></h3>
                      <?php if ($activity_count > 0): ?>
                        <span class="badge bg-soft-success text-success ms-2">
                          <i class="bi-arrow-up"></i> New
                        </span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Career Advancement -->
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
                      <h3 class="mb-0"><?php echo $career_count; ?></h3>
                      <?php if ($career_count > 0): ?>
                        <span class="badge bg-soft-success text-success ms-2">
                          <i class="bi-arrow-up"></i> <?php echo min($career_count, 5); ?>
                        </span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Popular Courses -->
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
                      <h3 class="mb-0"><?php echo $popular_count; ?></h3>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Top Rated -->
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
                      <h3 class="mb-0"><?php echo $top_rated_count; ?></h3>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- End Stats Cards -->

        <!-- Recommended Courses Based on Activity -->
        <h5 class="mb-3">Based on Your Activity</h5>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 mb-5">
          <?php if (count($activity_based_courses) > 0): ?>
            <?php foreach ($activity_based_courses as $course): ?>
              <div class="col mb-4">
                <div class="card h-100">
                  <img class="card-img-top" src="../uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <span class="badge bg-soft-primary text-primary">
                        <?php
                        // You would need to fetch the category name based on course_id
                        echo "Recommended";
                        ?>
                      </span>
                      <span class="d-block text-muted small"><?php echo htmlspecialchars($course['lesson_count']); ?> lessons</span>
                    </div>
                    <h5 class="card-title"><a class="text-dark" href="course-overview.php?id=<?php echo $course['course_id']; ?>"><?php echo htmlspecialchars($course['title']); ?></a></h5>
                    <p class="card-text"><?php echo htmlspecialchars($course['short_description']); ?></p>

                    <?php if ($course['price'] > 0): ?>
                      <div class="mb-2">
                        <span class="fw-bold text-primary">$<?php echo number_format($course['price'], 2); ?></span>
                      </div>
                    <?php else: ?>
                      <div class="mb-2">
                        <span class="badge bg-soft-success text-success">Free</span>
                      </div>
                    <?php endif; ?>
                  </div>
                  <div class="card-footer">
                    <div class="d-flex align-items-center">
                      <div class="avatar-group avatar-group-xs me-3">
                        <span class="avatar avatar-circle">
                          <img class="avatar-img" src="../uploads/profile/<?php echo htmlspecialchars($course['profile_pic']); ?>" alt="Instructor Image">
                        </span>
                      </div>
                      <div class="d-flex justify-content-between align-items-center flex-grow-1">
                        <span class="card-text"><?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?></span>
                        <div class="d-flex align-items-center">
                          <span class="me-1"><i class="bi-star-fill text-warning"></i></span>
                          <span><?php echo number_format($course['avg_rating'], 2); ?></span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-12">
              <div class="alert alert-info text-center">
                <i class="bi-info-circle me-2"></i>
                We couldn't find any courses based on your activity. Try exploring more courses to get personalized recommendations!
              </div>
            </div>
          <?php endif; ?>
        </div>

        <!-- View All Button -->
        <div class="text-center">
          <a class="btn btn-outline-primary" href="courses.php">See all recommendations <i class="bi-chevron-right small ms-1"></i></a>
        </div>
        <!-- End View All Button -->
      </div>

      <script>
        // Add this to your JavaScript file
        document.addEventListener('DOMContentLoaded', function() {
          // Get the recommendations tab
          const recommendationsTab = document.getElementById('pills-recommended-tab');

          if (recommendationsTab) {
            // Add click event to show loading overlay when tab is clicked
            recommendationsTab.addEventListener('click', function() {
              // Only show loading if the tab is not already active
              if (!this.classList.contains('active')) {
                showOverlay('Loading recommendations...');

                // Simulate loading time (in a real implementation, this would be based on AJAX completion)
                setTimeout(function() {
                  removeOverlay();
                  // Optionally show a notification
                  showNotification('Recommendations loaded successfully', {
                    type: 'success',
                    title: 'Recommendations'
                  });
                }, 800); // Adjust timing based on your needs
              }
            });
          }
        });
      </script>

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
                        <img class="avatar avatar-xss me-2" src="../favicon.ico" alt="Learnix">
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
              <i class="bi bi-file-earmark-text fs-1 text-primary mb-3"></i> <!-- Bootstrap icon -->
              <h5>No certificates yet</h5>
              <p>Complete a course to earn your first certificate.</p>
              <a href="my-courses.php" class="btn btn-soft-primary">Continue Learning</a>
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
                    <div class="d-flex flex-column align-items-center justify-content-center">
                      <i class="bi bi-bar-chart fs-1 text-primary mb-3"></i> <!-- Bootstrap icon -->
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



        <!-- Skills Progress -->
        <div>
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Skills Development</h3>
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
                    <i class="bi bi-award fs-1 text-primary"></i> <!-- Bootstrap icon -->
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

</main>
<!-- ========== END MAIN CONTENT ========== -->
<?php include '../includes/student-footer.php'; ?>