<?php include '../includes/student-header.php'; ?>
<?php
// require_once 'db_connection.php'; // adjust as needed

$instructor = null;

if (isset($_GET['username'])) {
    $username = $_GET['username'];

    try {
        $stmt = $conn->prepare("
    SELECT 
        u.user_id,
        u.first_name, 
        u.last_name, 
        u.email,
        u.profile_pic,
        u.phone,
        u.location,
        u.created_at,
        i.instructor_id,
        i.bio,
        -- Check for verified status from verification requests
        (SELECT 
            CASE 
                WHEN EXISTS (
                    SELECT 1 FROM instructor_verification_requests ivr 
                    WHERE ivr.instructor_id = i.instructor_id 
                    AND ivr.status = 'approved'
                ) 
                THEN 'verified' 
                ELSE 'unverified' 
            END
        ) AS verification_status,
        
        -- Social Links
        sl.facebook,
        sl.twitter,
        sl.instagram,
        sl.linkedin,
        sl.github,
        
        -- Instructor Experience
        (
            SELECT JSON_ARRAYAGG(
                JSON_OBJECT(
                    'job_title', job_title,
                    'company_name', company_name,
                    'years_worked', years_worked,
                    'job_description', job_description
                )
            )
            FROM instructor_experience ie 
            WHERE ie.instructor_id = i.instructor_id
            AND ie.deleted_at IS NULL
        ) AS professional_experience,
        
        -- Course Count using course_instructors junction table
        (
            SELECT COUNT(*) FROM courses c 
            JOIN course_instructors ci ON c.course_id = ci.course_id
            WHERE ci.instructor_id = i.instructor_id 
            AND c.status = 'Published'
            AND c.deleted_at IS NULL
        ) AS published_courses_count,
        
        -- Total Course Enrollments using course_instructors
        (
            SELECT COALESCE(SUM(e.enrollment_count), 0) 
            FROM (
                SELECT c.course_id, COUNT(*) as enrollment_count 
                FROM enrollments e
                JOIN courses c ON e.course_id = c.course_id
                JOIN course_instructors ci ON c.course_id = ci.course_id  
                WHERE ci.instructor_id = i.instructor_id
                AND e.deleted_at IS NULL
                AND c.deleted_at IS NULL
                GROUP BY c.course_id
            ) e
        ) AS total_student_enrollments,
        
        -- Add average rating and review count using course_instructors
        (SELECT COALESCE(AVG(cr.rating), 0) 
         FROM course_ratings cr 
         JOIN courses c ON cr.course_id = c.course_id
         JOIN course_instructors ci ON c.course_id = ci.course_id
         WHERE ci.instructor_id = i.instructor_id) AS average_rating,
         
        (SELECT COUNT(*) 
         FROM course_ratings cr 
         JOIN courses c ON cr.course_id = c.course_id
         JOIN course_instructors ci ON c.course_id = ci.course_id
         WHERE ci.instructor_id = i.instructor_id) AS review_count

    FROM instructors i
    JOIN users u ON i.user_id = u.user_id
    LEFT JOIN instructor_social_links sl ON i.instructor_id = sl.instructor_id
    WHERE u.username = ?
    AND i.deleted_at IS NULL
    LIMIT 1
");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $instructor = $result->fetch_assoc();

        if (!$instructor) {
            header("HTTP/1.0 404 Not Found");
            echo "Instructor profile not found.";
            exit;
        }
    } catch (Exception $e) {
        echo "Error fetching instructor profile: " . $e->getMessage();
        exit;
    }
} else {
    header("Location: 404.php");
    exit;
}
?>


<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main">
    <!-- Content -->
    <div class="container content-space-t-1 content-space-t-sm-2 content-space-b-2">
        <div class="row">
            <div class="col-md-5 col-lg-4 mb-7 mb-md-0">
                <!-- Sticky Block -->
                <div id="stickyBlockStartPoint">
                    <div class="js-sticky-block" data-hs-sticky-block-options='{
                   "parentSelector": "#stickyBlockStartPoint",
                   "breakpoint": "md",
                   "startPoint": "#stickyBlockStartPoint",
                   "endPoint": "#stickyBlockEndPoint",
                   "stickyOffsetTop": 12,
                   "stickyOffsetBottom": 12
                 }'>
                        <!-- Card -->
                        <div class="card">
                            <!-- Card Header -->
                            <div class="card-header text-center">
                                <div class="mb-3">
                                    <img
                                        class="avatar avatar-xxl avatar-circle avatar-centered"
                                        src="../uploads/instructor-profile/<?php echo !empty($instructor['profile_pic']) ? htmlspecialchars($instructor['profile_pic']) : 'default.png'; ?>"
                                        alt="Profile Picture">
                                </div>

                                <span class="d-block text-body small mb-3">
                                    Joined in <?php echo date('Y', strtotime($instructor['created_at'])); ?>
                                </span>

                                <!--
                                <a class="btn btn-outline-primary btn-transition" href="send-message.php?to=<?php echo urlencode($instructor['username']); ?>">
                                    <i class="bi-envelope me-2"></i> Send Message
                                </a>
                                -->
                            </div>
                            <!-- End Card Header -->


                            <!-- Card Body -->
                            <div class="card-body">
                                <div class="row mb-7">
                                    <!-- Reviews -->
                                    <div class="col-6 col-md-12 col-lg-6 mb-4">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <span class="avatar avatar-xs">
                                                    <img class="avatar-img" src="../assets/svg/illustrations/review-rating-shield.svg" alt="Reviews">
                                                </span>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <span class="text-body small">
                                                    <?php echo isset($instructor['review_count']) ? (int)$instructor['review_count'] : '0'; ?> Reviews
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Rating -->
                                    <div class="col-6 col-md-12 col-lg-6 mb-4">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <span class="avatar avatar-xs">
                                                    <img class="avatar-img" src="../assets/svg/illustrations/star.svg" alt="Rating">
                                                </span>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <span class="text-body small">
                                                    <?php echo isset($instructor['average_rating']) ? number_format($instructor['average_rating'], 2) : 'N/A'; ?> rating
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Top Teacher Badge (conditional based on verification) -->
                                    <div class="col-6 col-md-12 col-lg-6 mb-4 mb-lg-0">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <span class="avatar avatar-xs">
                                                    <?php if ($instructor['verification_status'] === 'verified'): ?>
                                                        <img class="avatar-img" src="../assets/svg/illustrations/medal.svg" alt="Top Teacher">
                                                    <?php else: ?>
                                                        <div class="d-flex align-items-center justify-content-center h-100 bg-soft-primary rounded-circle">
                                                            <i class="bi-person text-primary"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <span class="text-body small">
                                                    <?php if ($instructor['verification_status'] === 'verified'): ?>
                                                        Top teacher
                                                    <?php else: ?>
                                                        New Instructor
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Courses Count -->
                                    <div class="col-6 col-md-12 col-lg-6">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <span class="avatar avatar-xs">
                                                    <img class="avatar-img" src="../assets/svg/illustrations/add-file.svg" alt="Courses">
                                                </span>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <span class="text-body small">
                                                    <?php echo (int)($instructor['published_courses_count'] ?? 0); ?> courses
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <!-- Connected Accounts -->
                                <div class="mb-4">
                                    <h4>Connected accounts</h4>
                                </div>

                                <?php
                                $socials = [
                                    'github'   => ['icon' => 'bi-github',   'label' => 'GitHub',   'text' => 'View profile'],
                                    'twitter'  => ['icon' => 'bi-twitter',  'label' => 'Twitter',  'text' => 'Follow on Twitter'],
                                    'linkedin' => ['icon' => 'bi-linkedin', 'label' => 'LinkedIn', 'text' => 'Connect on LinkedIn'],
                                    'facebook' => ['icon' => 'bi-facebook', 'label' => 'Facebook', 'text' => 'Visit Facebook profile']
                                ];

                                $hasSocials = false;
                                ?>

                                <div class="row">
                                    <?php foreach ($socials as $platform => $data): ?>
                                        <?php if (!empty($instructor[$platform])): ?>
                                            <?php $hasSocials = true; ?>
                                            <div class="col-6 col-md-12 col-lg-6 mb-4">
                                                <a class="d-flex" href="<?php echo htmlspecialchars($instructor[$platform]); ?>" target="_blank">
                                                    <div class="flex-shrink-0">
                                                        <div class="icon icon-xs icon-soft-secondary">
                                                            <i class="<?php echo $data['icon']; ?>"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 mt-n1 ms-3">
                                                        <span class="d-block small fw-semi-bold"><?php echo $data['label']; ?></span>
                                                        <small class="d-block text-body"><?php echo $data['text']; ?></small>
                                                    </div>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>

                                <?php if (!$hasSocials): ?>
                                    <div class="alert alert-soft-secondary text-center small" role="alert">
                                        This instructor has not connected any social media accounts yet.
                                    </div>
                                <?php endif; ?>
                                <!-- End Connected Accounts -->

                            </div>
                            <!-- End Card Body -->

                            <a class="card-footer text-body small text-center" href="#">
                                <i class="bi-flag me-1"></i> Report this author
                            </a>
                        </div>
                        <!-- End Card -->
                    </div>
                </div>
                <!-- End Sticky Block -->
            </div>
            <!-- End Col -->

            <div class="col-md-7 col-lg-8">
                <div class="ps-lg-6">
                    <div class="mb-3 mb-sm-0 me-2 d-flex align-items-center">
                        <h2><?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?></h2>
                        <?php if ($instructor['verification_status'] === 'verified'): ?>
                            <span class="badge bg-primary-soft ms-2" title="Verified Instructor">
                                <i class="bi-patch-check-fill text-primary"></i> Verified
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php
                    $experienceData = $instructor['professional_experience'] ?? '';
                    $experience = !empty($experienceData) ? json_decode($experienceData, true) : [];

                    $latestExperience = !empty($experience) ? end($experience) : null;
                    ?>

                    <?php if ($latestExperience && !empty($latestExperience['job_title']) && !empty($latestExperience['company_name'])): ?>
                        <div class="d-flex small mb-3">
                            <div class="flex-shrink-0">
                                <i class="bi-briefcase-fill"></i>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <?php echo htmlspecialchars($latestExperience['job_title'] . ', ' . $latestExperience['company_name']); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php
                    $bio = trim($instructor['bio'] ?? '');
                    ?>

                    <!-- About Me -->
                    <?php if (!empty($bio)): ?>
                        <p><?php echo nl2br(htmlspecialchars($bio)); ?></p>
                    <?php endif; ?>

                    <!-- My Journey Timeline -->
                    <?php if (!empty($experience)): ?>
                        <div class="collapse" id="collapseJourneySection">
                            <h5 class="mt-4 mb-3">My Journey</h5>

                            <div class="timeline">
                                <?php foreach ($experience as $exp): ?>
                                    <div class="timeline-item">
                                        <h6 class="mb-1 pt-1"><?php echo htmlspecialchars($exp['job_title']); ?></h6>
                                        <p class="text-muted mb-1 small ms-4">
                                            <?php echo htmlspecialchars($exp['company_name']); ?> &bullet; <?php echo (int)$exp['years_worked']; ?> year(s)
                                        </p>
                                        <?php if (!empty($exp['job_description'])): ?>
                                            <p class="text-muted mb-3 small ms-4"><?php echo nl2br(htmlspecialchars($exp['job_description'])); ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Toggle Link -->
                        <a class="link link-collapse" data-bs-toggle="collapse" href="#collapseJourneySection" role="button" aria-expanded="false" aria-controls="collapseJourneySection">
                            <span class="link-collapse-default">Show My Journey</span>
                            <span class="link-collapse-active">Hide My Journey</span>
                        </a>
                    <?php else: ?>
                        <div class="alert alert-soft-secondary text-center small" role="alert"">The instructor hasn't added any professional experience yet.</div>
<?php endif; ?>


                    <style>
                        .timeline {
                            position: relative;
                            padding-left: 16px;
                        }

                        .timeline:before {
                            content: '';
                            position: absolute;
                            top: 0;
                            bottom: 0;
                            left: 0;
                            width: 2px;
                            background: #dee2e6;
                        }

                        .timeline-item {
                            position: relative;
                            padding-bottom: 1.5rem;
                            margin-left: -16px;
                        }

                        .timeline-item:last-child {
                            padding-bottom: 0;
                        }

                        .timeline-item:before {
                            content: '';
                            position: absolute;
                            left: -5px;
                            top: 0.35em;
                            width: 12px;
                            height: 12px;
                            border-radius: 50%;
                            background: #0d6efd;
                            border: 2px solid white;
                        }

                        .timeline-item h5,
                        .timeline-item h6 {
                            font-weight: 600;
                            margin: 0;
                            padding-left: 24px;
                        }
                    </style>

                    <?php
                    $courses = [];

                    try {
                    $stmt = $conn->prepare("
    SELECT 
        c.course_id,
        c.title,
        c.thumbnail,
        c.price,
        c.created_at,
        
        -- Count number of lessons (topics)
        (SELECT COUNT(*) FROM section_topics st 
         JOIN course_sections cs ON st.section_id = cs.section_id 
         WHERE cs.course_id = c.course_id
         AND cs.deleted_at IS NULL) AS lesson_count,
        
        -- Calculate total duration from video content
        (SELECT COALESCE(SUM(
            CASE 
                WHEN vs.duration_seconds IS NULL THEN 0
                ELSE vs.duration_seconds
            END), 0)
         FROM video_sources vs
         JOIN topic_content tc ON vs.content_id = tc.content_id
         JOIN section_topics st ON tc.topic_id = st.topic_id
         JOIN course_sections cs ON st.section_id = cs.section_id
         WHERE cs.course_id = c.course_id
         AND tc.deleted_at IS NULL
         AND cs.deleted_at IS NULL) AS total_duration_seconds,
        
        -- Get average rating
        (SELECT COALESCE(AVG(cr.rating), 0) 
         FROM course_ratings cr 
         WHERE cr.course_id = c.course_id) AS average_rating,
        
        -- Get review count
        (SELECT COUNT(*) 
         FROM course_ratings cr 
         WHERE cr.course_id = c.course_id) AS review_count
        
    FROM courses c
    JOIN course_instructors ci ON c.course_id = ci.course_id
    WHERE ci.instructor_id = ?
    AND c.status = 'Published'
    AND c.deleted_at IS NULL
    ORDER BY c.created_at DESC
");
                        $stmt->bind_param("i", $instructor['instructor_id']);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $courses = $result->fetch_all(MYSQLI_ASSOC);
                    } catch (Exception $e) {
                        echo "Error fetching instructor courses: " . $e->getMessage();
                    }
                    ?>

                    <?php
                    $maxCourses = 6;
                    $coursePreview = array_slice($courses, 0, $maxCourses);
                    ?>

                    <!-- Courses -->
                    <div class=" border-top pt-5 mt-5">
                            <div class="mb-4">
                                <h4>Courses taught by <?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?></h4>
                            </div>

                            <?php if (!empty($coursePreview)): ?>
                                <div class="d-grid gap-5 mb-7">
                                    <?php foreach ($coursePreview as $course): ?>
                                        <!-- Course Card -->
                                        <a class="d-block" href="course-overview.php?id=<?php echo $course['course_id']; ?>">
                                            <div class="row">
                                                <div class="col-sm-5 col-lg-3 mb-3 mb-sm-0">
                                                    <!-- Fixed size image container -->
                                                    <div style="height: 80px; overflow: hidden;">
                                                        <?php
                                                        $thumbnailPath = !empty($course['thumbnail']) ?
                                                            '../uploads/thumbnails/' . htmlspecialchars($course['thumbnail']) :
                                                            '../assets/svg/components/placeholder-img.svg';
                                                        ?>
                                                        <img class="card-img" src="<?php echo $thumbnailPath; ?>"
                                                            alt="<?php echo htmlspecialchars($course['title']); ?>"
                                                            style="width: 100%; height: 100%; object-fit: cover;">
                                                    </div>
                                                </div>

                                                <div class="col-sm-7 col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-6 mb-2 mb-lg-0">
                                                            <h5 class="text-inherit"><?php echo htmlspecialchars($course['title']); ?></h5>

                                                            <div class="d-flex align-items-center flex-wrap">
                                                                <!-- Rating -->
                                                                <div class="d-flex gap-1">
                                                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                                                        <img src="../assets/svg/illustrations/star.svg" alt="Review rating" width="16">
                                                                    <?php endfor; ?>
                                                                </div>
                                                                <div class="ms-1">
                                                                    <span class="text-body ms-1"><?php echo number_format($course['average_rating'], 2); ?></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <div class="row">
                                                                <div class="col-7">
                                                                    <div class="text-muted small mb-2">
                                                                        <i class="bi-book me-1"></i> <?php echo $course['lesson_count']; ?> lessons
                                                                    </div>
                                                                    <div class="text-muted small">
                                                                        <i class="bi-clock me-1"></i> <?php
                                                                                                        $hours = floor($course['total_duration_seconds'] / 3600);
                                                                                                        $minutes = floor(($course['total_duration_seconds'] % 3600) / 60);
                                                                                                        echo $hours . 'h ' . $minutes . 'm';
                                                                                                        ?>
                                                                    </div>
                                                                </div>

                                                                <div class="col-5 text-end">
                                                                    <h5 class="text-primary mb-0">₵<?php echo number_format($course['price'], 2); ?></h5>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                        <!-- End Course Card -->
                                    <?php endforeach; ?>
                                </div>

                                <?php if (count($courses) > $maxCourses && !empty($instructor['username'])): ?>
                                    <div class="text-end small">
                                        <a class="link" href="courses.php?instructor=<?php echo urlencode($instructor['username']); ?>">
                                            See all courses <i class="bi-chevron-right small ms-1"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>

                            <?php else: ?>
                                <div class="alert alert-soft-secondary text-center">
                                    This instructor hasn't published any courses yet.
                                </div>
                            <?php endif; ?>
                        </div>
                        <!-- End Courses -->


                        <!-- Reviews -->
                        <div class="border-top pt-5 mt-5">
                            <div class="mb-4">
                                <h4>Reviews</h4>
                            </div>

                            <?php
                            // Fetch all reviews for the instructor's courses
                            try {
           $stmt = $conn->prepare("
    SELECT 
        cr.rating_id, 
        cr.review_text, 
        cr.rating, 
        cr.created_at,
        u.user_id, 
        u.first_name, 
        u.last_name, 
        u.profile_pic,
        c.title as course_title, 
        c.course_id
    FROM course_ratings cr
    JOIN courses c ON cr.course_id = c.course_id
    JOIN users u ON cr.user_id = u.user_id
    JOIN course_instructors ci ON c.course_id = ci.course_id
    WHERE ci.instructor_id = ? 
    AND ci.is_primary = 1
    AND cr.review_text IS NOT NULL
    AND c.deleted_at IS NULL
    ORDER BY cr.created_at DESC
    LIMIT 5
");
                                $stmt->bind_param("i", $instructor['instructor_id']);
                                $stmt->execute();
                                $reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                            } catch (Exception $e) {
                                // Silently fail - we'll handle empty results below
                                $reviews = [];
                            }
                            ?>

                            <?php if (!empty($reviews)): ?>
                                <!-- Comment -->
                                <ul class="list-comment list-comment-divider mb-7">
                                    <?php foreach ($reviews as $review): ?>
                                        <!-- Item -->
                                        <li class="list-comment-item">
                                            <!-- Media -->
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="flex-shrink-0">
                                                    <?php if (!empty($review['profile_pic'])): ?>
                                                        <img class="avatar avatar-sm avatar-circle" src="../uploads/profile/<?php echo htmlspecialchars($review['profile_pic']); ?>" alt="User">
                                                    <?php else: ?>
                                                        <div class="avatar avatar-sm avatar-soft-primary avatar-circle">
                                                            <span class="avatar-initials"><?php echo substr($review['first_name'], 0, 1) . substr($review['last_name'], 0, 1); ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="flex-grow-1 ms-3">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></h6>
                                                        <span class="d-block small text-muted"><?php echo date('F j, Y', strtotime($review['created_at'])); ?></span>
                                                    </div>
                                                    <small class="text-muted">
                                                        On <a href="course-overview.php?id=<?php echo $review['course_id']; ?>"><?php echo htmlspecialchars($review['course_title']); ?></a>
                                                    </small>
                                                </div>
                                            </div>
                                            <!-- End Media -->

                                            <div class="d-flex gap-1 mb-3 ms-8">
                                                <?php for ($i = 0; $i < 5; $i++): ?>
                                                    <?php if ($i < $review['rating']): ?>
                                                        <img src="../assets/svg/illustrations/star.svg" alt="Review rating" width="16">
                                                    <?php else: ?>
                                                        <img src="../assets/svg/illustrations/star-muted.svg" alt="Review rating" width="16">
                                                    <?php endif; ?>
                                                <?php endfor; ?>
                                            </div>

                                            <div class="mb-5 ms-7">
                                                <p class="mb-1 small ms-2"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                                            </div>
                                        </li>
                                        <!-- End Item -->
                                    <?php endforeach; ?>
                                </ul>
                                <!-- End Comment -->

                                <!-- See All Reviews Button -->
                                <div class="text-center">
                                    <a class="btn btn-outline-primary btn-transition btn-sm" href="instructor-reviews.php?id=<?php echo $instructor['instructor_id']; ?>">
                                        See All Reviews <i class="bi-chevron-right small ms-1"></i>
                                    </a>
                                </div>

                            <?php else: ?>
                                <div class="alert alert-soft-secondary text-center">
                                    This instructor hasn't received any reviews yet.
                                </div>
                            <?php endif; ?>
                        </div>
                        <!-- End Reviews -->


                        <!-- Sticky Block End Point -->
                        <div id="stickyBlockEndPoint"></div>
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