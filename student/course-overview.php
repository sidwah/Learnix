<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include header
include '../includes/student-header.php';

// Check if course_id is provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Redirect to courses page if no valid ID is provided
    header("Location: courses.php");
    exit();
}

// Get course ID from URL
$course_id = intval($_GET['id']);

// Connect to database
require_once '../backend/config.php';

// Fetch course details
$sql = "SELECT c.*, u.first_name, u.last_name, u.profile_pic, u.username, 
               i.bio, cat.name AS category_name, cat.slug AS category_slug,
               sub.name AS subcategory_name, sub.slug AS subcategory_slug
        FROM courses c
        JOIN instructors i ON c.instructor_id = i.instructor_id
        JOIN users u ON i.user_id = u.user_id
        JOIN subcategories sub ON c.subcategory_id = sub.subcategory_id
        JOIN categories cat ON sub.category_id = cat.category_id
        WHERE c.course_id = ? AND c.status = 'Published'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if course exists and is published
if ($result->num_rows === 0) {
    // Redirect to courses page if course not found
    header("Location: courses.php");
    exit();
}

// Get course data
$course = $result->fetch_assoc();

// Get course sections and topics
$sql = "SELECT cs.*, COUNT(st.topic_id) as topic_count,
               SUM(CASE WHEN tc.content_type = 'video' THEN 1 ELSE 0 END) as video_count,
               SUM(CASE WHEN tc.content_type = 'text' THEN 1 ELSE 0 END) as text_count,
               SUM(CASE WHEN tc.content_type = 'link' THEN 1 ELSE 0 END) as link_count,
               SUM(CASE WHEN tc.content_type = 'document' THEN 1 ELSE 0 END) as document_count
        FROM course_sections cs
        LEFT JOIN section_topics st ON cs.section_id = st.section_id
        LEFT JOIN topic_content tc ON st.topic_id = tc.topic_id
        WHERE cs.course_id = ?
        GROUP BY cs.section_id
        ORDER BY cs.position";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$sections_result = $stmt->get_result();
$sections = [];
$total_lectures = 0;
$total_duration = 0; // in minutes

while ($section = $sections_result->fetch_assoc()) {
    $sections[] = $section;
    $total_lectures += $section['topic_count'];
    
    // For estimation, assume each video is ~10 mins and each text/link/doc is ~5 mins
    $total_duration += ($section['video_count'] * 10) + (($section['text_count'] + $section['link_count'] + $section['document_count']) * 5);
}

// Get course requirements
$sql = "SELECT * FROM course_requirements WHERE course_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$requirements_result = $stmt->get_result();
$requirements = [];

while ($requirement = $requirements_result->fetch_assoc()) {
    $requirements[] = $requirement;
}

// Get course learning outcomes
$sql = "SELECT * FROM course_learning_outcomes WHERE course_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$outcomes_result = $stmt->get_result();
$outcomes = [];

while ($outcome = $outcomes_result->fetch_assoc()) {
    $outcomes[] = $outcome;
}

// Get course ratings
$sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
        FROM course_ratings 
        WHERE course_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$ratings_result = $stmt->get_result();
$rating_data = $ratings_result->fetch_assoc();

$avg_rating = number_format($rating_data['avg_rating'] ?? 0, 1);
$review_count = $rating_data['review_count'] ?? 0;

// Get rating distribution
$sql = "SELECT 
            COUNT(CASE WHEN rating >= 4.5 THEN 1 END) as five_star,
            COUNT(CASE WHEN rating >= 3.5 AND rating < 4.5 THEN 1 END) as four_star,
            COUNT(CASE WHEN rating >= 2.5 AND rating < 3.5 THEN 1 END) as three_star,
            COUNT(CASE WHEN rating >= 1.5 AND rating < 2.5 THEN 1 END) as two_star,
            COUNT(CASE WHEN rating < 1.5 THEN 1 END) as one_star
        FROM course_ratings 
        WHERE course_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$rating_dist_result = $stmt->get_result();
$rating_dist = $rating_dist_result->fetch_assoc();

// Get sample reviews
$sql = "SELECT cr.*, u.first_name, u.last_name, u.profile_pic, u.username
        FROM course_ratings cr
        JOIN users u ON cr.user_id = u.user_id
        WHERE cr.course_id = ?
        ORDER BY cr.created_at DESC
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$reviews_result = $stmt->get_result();
$reviews = [];

while ($review = $reviews_result->fetch_assoc()) {
    $reviews[] = $review;
}

// Get instructor info and stats
$sql = "SELECT 
            COUNT(DISTINCT c.course_id) as course_count,
            COUNT(DISTINCT cr.user_id) as student_count,
            COUNT(DISTINCT cr.rating_id) as review_count,
            AVG(cr.rating) as instructor_rating
        FROM instructors i
        LEFT JOIN courses c ON i.instructor_id = c.instructor_id AND c.status = 'Published'
        LEFT JOIN course_ratings cr ON c.course_id = cr.course_id
        WHERE i.instructor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course['instructor_id']);
$stmt->execute();
$instructor_stats_result = $stmt->get_result();
$instructor_stats = $instructor_stats_result->fetch_assoc();

// Get instructor socials
$sql = "SELECT * FROM instructor_social_links WHERE instructor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course['instructor_id']);
$stmt->execute();
$socials_result = $stmt->get_result();
$socials = $socials_result->fetch_assoc() ?? [];

// Format duration string
function formatDuration($minutes) {
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    
    if ($hours > 0) {
        return sprintf("%d:%02d hours", $hours, $mins);
    } else {
        return sprintf("%d minutes", $mins);
    }
}

// Format course duration
$duration_text = formatDuration($total_duration);

// Check if user is enrolled in this course
$is_enrolled = false;
if (isset($_SESSION['user_id'])) {
    // Check if the user is enrolled in this course
    $user_id = $_SESSION['user_id'];
    
    $sql = "SELECT * FROM enrollments WHERE user_id = ? AND course_id = ? AND status = 'Active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $course_id);
    $stmt->execute();
    $enrollment_result = $stmt->get_result();
    
    $is_enrolled = ($enrollment_result->num_rows > 0);
}

// Check if user is the instructor of this course
$is_instructor = false;
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'instructor') {
    $is_instructor = ($_SESSION['instructor_id'] == $course['instructor_id']);
}

// Close database connection
$stmt->close();
// $conn->close();

// Function to generate star rating HTML
function generateStarRating($rating) {
    $rating = floatval($rating);
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
    
    $html = '';
    
    // Full stars
    for ($i = 0; $i < $fullStars; $i++) {
        $html .= '<img src="../assets/svg/illustrations/star.svg" alt="Review rating" width="16">';
    }
    
    // Half star
    if ($halfStar) {
        $html .= '<img src="../assets/svg/illustrations/star-half.svg" alt="Review rating" width="16">';
    }
    
    // Empty stars
    for ($i = 0; $i < $emptyStars; $i++) {
        $html .= '<img src="../assets/svg/illustrations/star-muted.svg" alt="Review rating" width="16">';
    }
    
    return $html;
}

// Function for relative time
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return "just now";
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . " " . ($mins == 1 ? "minute" : "minutes") . " ago";
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . " " . ($hours == 1 ? "hour" : "hours") . " ago";
    } elseif ($diff < 2592000) {
        $days = floor($diff / 86400);
        return $days . " " . ($days == 1 ? "day" : "days") . " ago";
    } elseif ($diff < 31536000) {
        $months = floor($diff / 2592000);
        return $months . " " . ($months == 1 ? "month" : "months") . " ago";
    } else {
        $years = floor($diff / 31536000);
        return $years . " " . ($years == 1 ? "year" : "years") . " ago";
    }
}

?>

<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main">
    <!-- Breadcrumb -->
    <div class="bg-light">
        <div class="container py-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-no-gutter mb-0">
                    <li class="breadcrumb-item"><a class="breadcrumb-link" href="../index.php">Home</a></li>
                    <li class="breadcrumb-item"><a class="breadcrumb-link" href="courses.php">Courses</a></li>
                    <!-- <li class="breadcrumb-item"><a class="breadcrumb-link" href="courses.php?category=<?php echo htmlspecialchars($course['category_slug']); ?>"><?php echo htmlspecialchars($course['category_name']); ?></a></li> -->
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($course['title']); ?></li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Breadcrumb -->
    
    <div class="position-relative">
        <!-- Hero -->
        <div class="gradient-y-overlay-lg-white bg-img-start content-space-2" style="background-image: url(../assets/img/1920x800/img6.jpg);">
            <div class="container">
                <div class="row">
                    <div class="col-md-7 col-lg-8">
                        <?php if ($course['price'] == 0): ?>
                            <small class="badge bg-success rounded-pill">Free</small>
                        <?php elseif ($course['price'] < 20): ?>
                            <small class="badge bg-info rounded-pill">Low Price</small>
                        <?php endif; ?>
                        
                        <h1><?php echo htmlspecialchars($course['title']); ?></h1>
                        <p class="mt-2 lead"><?php echo htmlspecialchars($course['short_description']); ?></p>

                        <div class="d-flex align-items-center flex-wrap">
                            <!-- Instructor -->
                            <div class="d-flex align-items-center me-4">
                                <div class="flex-shrink-0 avatar-group avatar-group-xs">
                                    <span class="avatar avatar-xs avatar-circle">
                                        <img class="avatar-img" src="../uploads/instructor-profile/<?php echo htmlspecialchars($course['profile_pic']); ?>" alt="<?php echo htmlspecialchars($course['first_name']); ?>">
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="ps-2">Created by 
                                        <a class="link" href="instructor-profile.php?username=<?php echo htmlspecialchars($course['username']); ?>">
                                            <?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?>
                                        </a>
                                    </span>
                                </div>
                            </div>
                            <!-- End Instructor -->

                            <!-- Rating -->
                            <div class="d-flex align-items-center flex-wrap">
                                <div class="d-flex gap-1">
                                    <?php echo generateStarRating($avg_rating); ?>
                                </div>
                                <div class="ms-1">
                                    <span class="fw-semi-bold text-dark me-1"><?php echo $avg_rating; ?></span>
                                    <span>(<?php echo $review_count; ?> reviews)</span>
                                </div>
                            </div>
                            <!-- End Rating -->
                        </div>
                    </div>
                    <!-- End Col -->
                </div>
                <!-- End Row -->
            </div>
        </div>
        <!-- End Hero -->

        <!-- Sidebar -->
        <div class="container content-space-t-md-2 position-md-absolute top-0 start-0 end-0">
            <div class="row justify-content-end">
                <div class="col-md-5 col-lg-4 position-relative zi-2 mb-7 mb-md-0">
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
                                <div class="p-1">
                                    <!-- Thumbnail -->
                                    <div class="bg-img-start text-center rounded-2 py-10 px-5" style="background-image: url(../uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>);">
                                        <a class="video-player video-player-btn" href="#previewModal" role="button" data-bs-toggle="modal">
                                            <span class="d-flex justify-content-center align-items-center">
                                                <span class="video-player-icon shadow-sm">
                                                    <i class="bi-play-fill"></i>
                                                </span>
                                            </span>
                                            <span class="text-white">Preview this course</span>
                                        </a>
                                    </div>
                                    <!-- End Thumbnail -->
                                </div>

                                <!-- Card Body -->
                                <div class="card-body">
                                    <div class="mb-3">
                                        <?php if ($course['price'] > 0): ?>
                                            <span class="card-title h2">$<?php echo number_format($course['price'], 2); ?></span>
                                        <?php else: ?>
                                            <span class="card-title h2">Free</span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($is_enrolled): ?>
                                        <!-- Already enrolled -->
                                        <div class="d-grid mb-2">
                                            <a class="btn btn-success btn-transition" href="lesson.php?course_id=<?php echo $course_id; ?>">
                                                <i class="bi-play-circle me-1"></i> Continue Learning
                                            </a>
                                        </div>
                                    <?php elseif ($is_instructor): ?>
                                        <!-- Course instructor -->
                                        <div class="d-grid mb-2">
                                            <a class="btn btn-outline-primary btn-transition" href="../instructor/course-edit.php?id=<?php echo $course_id; ?>">
                                                <i class="bi-pencil me-1"></i> Edit Course
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <!-- Not enrolled -->
                                        <div class="d-grid mb-2">
                                            <?php if ($course['price'] > 0): ?>
                                                <a class="btn btn-primary btn-transition" href="checkout.php?course_id=<?php echo $course_id; ?>">
                                                    <i class="bi-cart me-1"></i> Buy Now
                                                </a>
                                            <?php else: ?>
                                                <a class="btn btn-primary btn-transition" href="enroll.php?course_id=<?php echo $course_id; ?>">
                                                    <i class="bi-journal-check me-1"></i> Enroll Now
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if ($course['price'] > 0): ?>
                                            <div class="text-center mb-4">
                                                <p class="card-text small">30-day money-back guarantee</p>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <h4 class="card-title">This course includes</h4>

                                    <ul class="list-unstyled list-py-1">
                                        <li><i class="bi-camera-video nav-icon"></i> <?php echo $total_lectures; ?> lessons</li>
                                        <li><i class="bi-stopwatch nav-icon"></i> <?php echo $duration_text; ?> total length</li>
                                        <li><i class="bi-file-text nav-icon"></i> Resources & supplemental materials</li>
                                        <li><i class="bi-file-earmark-arrow-down nav-icon"></i> Downloadable content</li>
                                        <li><i class="bi-phone nav-icon"></i> Access on mobile and tablet</li>
                                        <?php if ($course['certificate_enabled']): ?>
                                            <li><i class="bi-award nav-icon"></i> Certificate of Completion</li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                                <!-- End Card Body -->
                            </div>
                            <!-- End Card -->
                        </div>
                    </div>
                    <!-- End Sticky Block -->
                </div>
            </div>
        </div>
        <!-- End Sidebar -->
    </div>

    <!-- Content -->
    <div class="container content-space-t-2 content-space-t-md-1">
        <div class="row">
            <div class="col-md-7 col-lg-8">
                <!-- Display any messages -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['success_message']; 
                        unset($_SESSION['success_message']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['error_message']; 
                        unset($_SESSION['error_message']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Learning Outcomes Section -->
                <h3 class="mb-4">What you'll learn:</h3>

                <div class="row">
                    <?php if (count($outcomes) > 0): ?>
                        <?php 
                        $halfCount = ceil(count($outcomes) / 2);
                        $firstHalf = array_slice($outcomes, 0, $halfCount);
                        $secondHalf = array_slice($outcomes, $halfCount);
                        ?>
                        
                        <div class="col-lg-6">
                            <!-- List Checked -->
                            <ul class="list-checked list-checked-primary">
                                <?php foreach ($firstHalf as $outcome): ?>
                                    <li class="list-checked-item"><?php echo htmlspecialchars($outcome['outcome_text']); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <!-- End List Checked -->
                        </div>
                        <!-- End Col -->

                        <?php if (count($secondHalf) > 0): ?>
                            <div class="col-lg-6">
                                <!-- List Checked -->
                                <ul class="list-checked list-checked-primary">
                                    <?php foreach ($secondHalf as $outcome): ?>
                                        <li class="list-checked-item"><?php echo htmlspecialchars($outcome['outcome_text']); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <!-- End List Checked -->
                            </div>
                            <!-- End Col -->
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <p class="text-muted">No specific learning outcomes have been provided for this course.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- End Row -->
                
                <!-- Prerequisites Section -->
                <?php if (count($requirements) > 0): ?>
                <div class="mt-5">
                    <h3 class="mb-4">Prerequisites:</h3>
                    <ul class="list-unstyled list-py-1">
                        <?php foreach ($requirements as $requirement): ?>
                            <li>
                                <i class="bi-check-circle-fill text-success me-2"></i>
                                <?php echo htmlspecialchars($requirement['requirement_text']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- Course Content Accordion -->
                <div class="border-top pt-7 mt-7">
                    <div class="row mb-4">
                        <div class="col-8">
                            <h3 class="mb-0">Course content</h3>
                        </div>
                        <!-- End Col -->

                        <div class="col-4 text-end">
                            <div class="row">
                                <div class="col-lg-6">
                                    <span class="small"><?php echo $total_lectures; ?> lectures</span>
                                </div>
                                <!-- End Col -->

                                <div class="col-lg-6">
                                    <span class="small"><?php echo $duration_text; ?></span>
                                </div>
                                <!-- End Col -->
                            </div>
                            <!-- End Row -->
                        </div>
                        <!-- End Col -->
                    </div>
                    <!-- End Row -->

                    <!-- Accordion -->
                    <div class="accordion accordion-btn-icon-start">
                        <?php foreach ($sections as $index => $section): ?>
                            <!-- Accordion Item -->
                            <div class="accordion-item">
                                <div class="accordion-header" id="heading<?php echo $section['section_id']; ?>">
                                    <a class="accordion-button <?php echo ($index !== 0) ? 'collapsed' : ''; ?>" role="button" data-bs-toggle="collapse" data-bs-target="#accordionCourse<?php echo $section['section_id']; ?>" aria-expanded="<?php echo ($index === 0) ? 'true' : 'false'; ?>" aria-controls="accordionCourse<?php echo $section['section_id']; ?>">
                                        <div class="flex-grow-1 ps-3">
                                            <div class="row">
                                                <div class="col-8">
                                                    <?php echo htmlspecialchars($section['title']); ?>
                                                </div>
                                                <!-- End Col -->

                                                <div class="col-4 text-end">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <span class="small text-muted fw-normal"><?php echo $section['topic_count']; ?> lectures</span>
                                                        </div>
                                                        <!-- End Col -->

                                                        <div class="col-lg-6">
                                                            <?php 
                                                            $section_duration = ($section['video_count'] * 10) + (($section['text_count'] + $section['link_count'] + $section['document_count']) * 5);
                                                            ?>
                                                            <span class="small text-muted fw-normal"><?php echo formatDuration($section_duration); ?></span>
                                                        </div>
                                                        <!-- End Col -->
                                                    </div>
                                                    <!-- End Row -->
                                                </div>
                                                <!-- End Col -->
                                            </div>
                                            <!-- End Row -->
                                        </div>
                                    </a>
                                </div>
                                <div id="accordionCourse<?php echo $section['section_id']; ?>" class="accordion-collapse collapse <?php echo ($index === 0) ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $section['section_id']; ?>">
                                    <div class="accordion-body">
                                        <!-- List Group -->
                                        <div class="list-group list-group-flush list-group-no-gutters">
                                            <?php
                                            // Get topics for this section
                                            require_once '../backend/config.php';
                                            $sql = "SELECT st.*, tc.content_type, tc.title as content_title, tc.video_url, tc.content_text, tc.external_url
                                                    FROM section_topics st
                                                    LEFT JOIN topic_content tc ON st.topic_id = tc.topic_id
                                                    WHERE st.section_id = ?
                                                    ORDER BY st.position";
                                            $stmt = $conn->prepare($sql);
                                            $stmt->bind_param("i", $section['section_id']);
                                            $stmt->execute();
                                            $topics_result = $stmt->get_result();
                                            
                                            while ($topic = $topics_result->fetch_assoc()):
                                            ?>
                                                <!-- Item -->
                                                <div class="list-group-item">
                                                    <div class="row">
                                                        <div class="col-8">
                                                            <div class="d-flex">
                                                                <div class="flex-shrink-0">
                                                                    <?php if ($topic['content_type'] === 'video'): ?>
                                                                        <i class="bi-play-circle-fill small"></i>
                                                                    <?php elseif ($topic['content_type'] === 'text'): ?>
                                                                        <i class="bi-file-text small"></i>
                                                                    <?php elseif ($topic['content_type'] === 'link'): ?>
                                                                        <i class="bi-link-45deg small"></i>
                                                                    <?php elseif ($topic['content_type'] === 'document'): ?>
                                                                        <i class="bi-file-earmark-text small"></i>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="flex-grow-1 ms-2">
                                                                    <span class="small"><?php echo htmlspecialchars($topic['content_title'] ?? $topic['title']); ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- End Col -->

                                                        <div class="col-4 text-end">
                                                            <div class="row">
                                                                <div class="col-lg-6">
                                                                    <?php if ($is_enrolled || $is_instructor): ?>
                                                                        <a class="small" href="lesson.php?course_id=<?php echo $course_id; ?>&topic_id=<?php echo $topic['topic_id']; ?>">View</a>
                                                                    <?php else: ?>
                                                                        <a class="small" href="#previewModal" data-bs-toggle="modal">Preview</a>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <!-- End Col -->

                                                                <div class="col-lg-6">
                                                                    <?php if ($topic['content_type'] === 'video'): ?>
                                                                        <span class="text-primary small">~10 mins</span>
                                                                    <?php elseif ($topic['content_type'] === 'text' || $topic['content_type'] === 'link' || $topic['content_type'] === 'document'): ?>
                                                                        <span class="text-primary small">~5 mins</span>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <!-- End Col -->
                                                            </div>
                                                            <!-- End Row -->
                                                        </div>
                                                        <!-- End Col -->
                                                    </div>
                                                </div>
                                                <!-- End Item -->
                                            <?php endwhile; ?>
                                        </div>
                                        <!-- End List Group -->
                                    </div>
                                </div>
                            </div>
                            <!-- End Accordion Item -->
                        <?php endforeach; ?>
                    </div>
                    <!-- End Accordion -->

                    <?php
                    // Close topic query connection
                    if (isset($stmt)) {
                        $stmt->close();
                    }
                    if (isset($conn)) {
                        $conn->close();
                    }
                    ?>
                </div>
                <!-- End Course Content Accordion -->

                <!-- Course Description -->
                <div class="border-top pt-7 mt-7">
                    <div class="mb-4">
                        <h3>Description</h3>
                    </div>

                    <div class="course-description">
                        <?php
                        // Display the first two paragraphs initially
                        $description = $course['full_description'];
                        $paragraphs = explode("\n", $description);
                        $visible_paragraphs = array_slice($paragraphs, 0, 2);
                        $hidden_paragraphs = array_slice($paragraphs, 2);
                        
                        foreach ($visible_paragraphs as $paragraph) {
                            if (trim($paragraph) !== '') {
                                echo "<p>" . nl2br(htmlspecialchars($paragraph)) . "</p>";
                            }
                        }
                        ?>

                        <!-- Read More - Collapse -->
                        <?php if (count($hidden_paragraphs) > 0): ?>
                            <div class="collapse" id="collapseCourseDescriptionSection">
                                <?php
                                foreach ($hidden_paragraphs as $paragraph) {
                                    if (trim($paragraph) !== '') {
                                        echo "<p>" . nl2br(htmlspecialchars($paragraph)) . "</p>";
                                    }
                                }
                                ?>
                                
                                <?php if (count($requirements) > 0): ?>
                                    <h4>Requirements</h4>
                                    <ul class="text-body pl-6">
                                        <?php foreach ($requirements as $requirement): ?>
                                            <li><?php echo htmlspecialchars($requirement['requirement_text']); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                            <!-- End Read More - Collapse -->

                            <!-- Link -->
                            <a class="link link-collapse" data-bs-toggle="collapse" href="#collapseCourseDescriptionSection" role="button" aria-expanded="false" aria-controls="collapseCourseDescriptionSection">
                                <span class="link-collapse-default">Read more</span>
                                <span class="link-collapse-active">Read less</span>
                            </a>
                            <!-- End Link -->
                        <?php endif; ?>
                    </div>
                </div>
                <!-- End Course Description -->

                <hr class="my-7">

                <!-- Instructor Section -->
                <div class="mb-4">
                    <h3>About the instructor</h3>
                </div>

                <div class="row">
                    <div class="col-sm-4 mb-4 mb-sm-0">
                        <div class="mb-3">
                            <img class="avatar avatar-xl avatar-circle" src="../uploads/instructor-profile/<?php echo htmlspecialchars($course['profile_pic']); ?>" alt="<?php echo htmlspecialchars($course['first_name']); ?>">
                        </div>

                        <ul class="list-unstyled list-py-1">
                            <?php if (isset($instructor_stats['instructor_rating'])): ?>
                                <li><i class="bi-star dropdown-item-icon"></i> <?php echo number_format($instructor_stats['instructor_rating'], 2); ?> Instructor rating</li>
                            <?php endif; ?>
                            <li><i class="bi-chat-left-dots dropdown-item-icon"></i> <?php echo $instructor_stats['review_count']; ?> reviews</li>
                            <li><i class="bi-person dropdown-item-icon"></i> <?php echo $instructor_stats['student_count']; ?> students</li>
                            <li><i class="bi-play-circle dropdown-item-icon"></i> <?php echo $instructor_stats['course_count']; ?> courses</li>
                        </ul>
                    </div>
                    <!-- End Col -->

                    <div class="col-sm-8">
                        <!-- Instructor Info -->
                        <div class="mb-2">
                            <h4 class="mb-1">
                                <a href="instructor-profile.php?username=<?php echo htmlspecialchars($course['username']); ?>">
                                    <?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?>
                                </a>
                            </h4>
                            <p class="fw-semi-bold">Instructor</p>
                        </div>

                        <p><?php echo nl2br(htmlspecialchars($course['bio'] ?? 'No bio information available.')); ?></p>
                        
                        <?php if (!empty($socials)): ?>
                            <div class="d-flex mt-4">
                                <?php if (!empty($socials['facebook'])): ?>
                                    <a class="btn btn-soft-secondary btn-sm btn-icon rounded-circle me-2" href="<?php echo htmlspecialchars($socials['facebook']); ?>" target="_blank">
                                        <i class="bi-facebook"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (!empty($socials['twitter'])): ?>
                                    <a class="btn btn-soft-secondary btn-sm btn-icon rounded-circle me-2" href="<?php echo htmlspecialchars($socials['twitter']); ?>" target="_blank">
                                        <i class="bi-twitter"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (!empty($socials['instagram'])): ?>
                                    <a class="btn btn-soft-secondary btn-sm btn-icon rounded-circle me-2" href="<?php echo htmlspecialchars($socials['instagram']); ?>" target="_blank">
                                        <i class="bi-instagram"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (!empty($socials['linkedin'])): ?>
                                    <a class="btn btn-soft-secondary btn-sm btn-icon rounded-circle me-2" href="<?php echo htmlspecialchars($socials['linkedin']); ?>" target="_blank">
                                        <i class="bi-linkedin"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (!empty($socials['github'])): ?>
                                    <a class="btn btn-soft-secondary btn-sm btn-icon rounded-circle me-2" href="<?php echo htmlspecialchars($socials['github']); ?>" target="_blank">
                                        <i class="bi-github"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <!-- End Instructor Info -->
                    </div>
                    <!-- End Col -->
                </div>
                <!-- End Instructor Section -->

                <hr class="my-7">

                <!-- Student Feedback Section -->
                <div class="mb-4">
                    <h3>Student feedback</h3>
                </div>

                <div class="row mb-5">
                    <div class="col-lg-4">
                        <!-- Card -->
                        <div class="card card-sm bg-primary text-center mb-3">
                            <div class="card-body">
                                <span class="display-4 text-white"><?php echo $avg_rating; ?></span>

                                <div class="d-flex justify-content-center gap-2 mb-2">
                                    <?php echo generateStarRating($avg_rating); ?>
                                </div>
                                <span class="text-white">Course rating</span>
                            </div>
                        </div>
                        <!-- End Card -->
                    </div>
                    <!-- End Col -->

                    <div class="col-lg-8">
                        <!-- Ratings -->
                        <div class="d-grid gap-2">
                            <?php 
                            $total_reviews = $review_count;
                            if ($total_reviews > 0):
                                $star_percentages = [
                                    5 => isset($rating_dist['five_star']) ? ($rating_dist['five_star'] / $total_reviews * 100) : 0,
                                    4 => isset($rating_dist['four_star']) ? ($rating_dist['four_star'] / $total_reviews * 100) : 0,
                                    3 => isset($rating_dist['three_star']) ? ($rating_dist['three_star'] / $total_reviews * 100) : 0,
                                    2 => isset($rating_dist['two_star']) ? ($rating_dist['two_star'] / $total_reviews * 100) : 0,
                                    1 => isset($rating_dist['one_star']) ? ($rating_dist['one_star'] / $total_reviews * 100) : 0
                                ];
                                
                                // Display rating bars
                                for ($stars = 5; $stars >= 1; $stars--):
                                    $star_count = 0;
                                    switch($stars) {
                                        case 5: $star_count = $rating_dist['five_star'] ?? 0; break;
                                        case 4: $star_count = $rating_dist['four_star'] ?? 0; break;
                                        case 3: $star_count = $rating_dist['three_star'] ?? 0; break;
                                        case 2: $star_count = $rating_dist['two_star'] ?? 0; break;
                                        case 1: $star_count = $rating_dist['one_star'] ?? 0; break;
                                    }
                            ?>
                                <div class="row align-items-center">
                                    <div class="col-7">
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: <?php echo round($star_percentages[$stars]); ?>%;" aria-valuenow="<?php echo round($star_percentages[$stars]); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <!-- End Col -->

                                    <div class="col-2 text-end">
                                        <div class="d-flex">
                                            <div class="d-flex gap-1 me-2">
                                                <?php
                                                for ($i = 1; $i <= 5; $i++) {
                                                    if ($i <= $stars) {
                                                        echo '<img src="../assets/svg/illustrations/star.svg" alt="Review rating" width="16">';
                                                    } else {
                                                        echo '<img src="../assets/svg/illustrations/star-muted.svg" alt="Review rating" width="16">';
                                                    }
                                                }
                                                ?>
                                            </div>
                                            <span><?php echo $star_count; ?></span>
                                        </div>
                                    </div>
                                    <!-- End Col -->
                                </div>
                                <!-- End Row -->
                            <?php 
                                endfor;
                            else:
                            ?>
                                <div class="text-center text-muted">
                                    <p>No ratings yet. Be the first to rate this course!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <!-- End Ratings -->
                    </div>
                    <!-- End Col -->
                </div>
                <!-- End Row -->

                <!-- Reviews Section -->
                <div class="border-bottom pb-4 mb-4">
                    <div class="row align-items-center">
                        <div class="col-sm-6 mb-2 mb-sm-0">
                            <h3 class="mb-0">Reviews</h3>
                        </div>
                        <!-- End Col -->
                    </div>
                    <!-- End Row -->
                </div>
                <!-- End Heading -->

                <!-- Comments/Reviews -->
                <ul class="list-comment list-comment-divider mb-7">
                    <?php if (count($reviews) > 0): ?>
                        <?php foreach ($reviews as $review): ?>
                            <!-- Review Item -->
                            <li class="list-comment-item">
                                <div class="d-flex gap-1 mb-3">
                                    <?php echo generateStarRating($review['rating']); ?>
                                </div>

                                <!-- Media -->
                                <div class="d-flex align-items-center mb-3">
                                    <div class="flex-shrink-0">
                                        <img class="avatar avatar-sm avatar-circle" src="../uploads/instructor-profile/<?php echo htmlspecialchars($review['profile_pic']); ?>" alt="<?php echo htmlspecialchars($review['first_name']); ?>">
                                    </div>

                                    <div class="flex-grow-1 ms-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0"><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></h5>
                                            <span class="d-block small text-muted"><?php echo timeAgo($review['created_at']); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Media -->

                                <div class="mb-5">
                                    <p><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                                </div>

                                <!-- Review Footer -->
                                <div class="mb-2">
                                    <span class="text-dark fw-semi-bold"><?php echo htmlspecialchars($review['first_name']); ?></span>
                                    <span>- Verified Purchase</span>
                                </div>

                                <!-- Helpful buttons -->
                                <div class="d-flex align-items-center">
                                    <span class="small me-2">Was this helpful?</span>

                                    <div class="d-flex gap-2">
                                        <a class="btn btn-white btn-xs" href="javascript:;">
                                            <i class="bi-hand-thumbs-up me-1"></i> Yes
                                        </a>
                                        <a class="btn btn-white btn-xs" href="javascript:;">
                                            <i class="bi-hand-thumbs-down me-1"></i> No
                                        </a>
                                    </div>
                                </div>
                                <!-- End Review Footer -->
                            </li>
                            <!-- End Review Item -->
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-comment-item">
                            <div class="text-center py-4">
                                <p class="text-muted">No reviews yet. Be the first to review this course!</p>
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>
                <!-- End Comments/Reviews -->

                <?php if ($review_count > 5): ?>
                    <div class="text-center">
                        <a class="btn btn-outline-primary btn-transition" href="course-reviews.php?id=<?php echo $course_id; ?>">See all reviews</a>
                    </div>
                <?php endif; ?>

                <hr class="my-7">
            </div>
        </div>
    </div>
    <!-- End Content -->

    <!-- Sticky Block End Point -->
    <div id="stickyBlockEndPoint"></div>
</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">Course Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-5">
                <img src="../uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="img-fluid mb-4" style="max-height: 300px;">
                <h4 class="mb-3"><?php echo htmlspecialchars($course['title']); ?></h4>
                <p class="mb-4"><?php echo htmlspecialchars($course['short_description']); ?></p>
                
                <?php if ($course['price'] > 0): ?>
                    <div class="d-grid gap-2 col-6 mx-auto">
                        <p class="h3 mb-3">$<?php echo number_format($course['price'], 2); ?></p>
                        <a href="checkout.php?course_id=<?php echo $course_id; ?>" class="btn btn-primary btn-lg">Buy Now</a>
                    </div>
                <?php else: ?>
                    <div class="d-grid gap-2 col-6 mx-auto">
                        <p class="h3 mb-3 text-success">Free</p>
                        <a href="enroll.php?course_id=<?php echo $course_id; ?>" class="btn btn-success btn-lg">Enroll Now</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/student-footer.php'; ?>