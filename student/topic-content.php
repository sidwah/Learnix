<?php
// course-content.php
ob_start();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include header
include '../includes/signin-header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login if not logged in
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Check if course_id and topic_id are provided in the URL
if (!isset($_GET['course_id']) || !is_numeric($_GET['course_id']) || !isset($_GET['topic']) || !is_numeric($_GET['topic'])) {
    // Redirect to courses page if no valid IDs are provided
    header("Location: courses.php");
    exit();
}

// Get course and topic IDs from URL
$course_id = intval($_GET['course_id']);
$topic_id = intval($_GET['topic']);

// Connect to database
require_once '../backend/config.php';

// First, check if user is enrolled in this course
$enrollment_query = "SELECT e.enrollment_id, e.status, e.current_topic_id, c.title as course_title 
                     FROM enrollments e
                     JOIN courses c ON e.course_id = c.course_id
                     WHERE e.user_id = ? AND e.course_id = ? AND e.status = 'Active'";
$stmt = $conn->prepare($enrollment_query);
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$enrollment_result = $stmt->get_result();

if ($enrollment_result->num_rows === 0) {
    // User is not enrolled in this course
    header("Location: courses.php");
    exit();
}
$enrollment = $enrollment_result->fetch_assoc();
$enrollment_id = $enrollment['enrollment_id'];
$course_title = $enrollment['course_title'];

// Fetch topic details
$topic_query = "SELECT st.topic_id, st.title as topic_title, st.section_id, st.is_previewable,
                cs.title as section_title,
                tc.content_id, tc.content_type, tc.title as content_title, 
                tc.content_text, tc.video_url, tc.external_url, tc.file_path,
                sq.quiz_id, sq.quiz_title, sq.pass_mark, sq.time_limit, sq.instruction,
                COALESCE(p.completion_status, 'Not Started') as completion_status,
                p.last_position
                FROM section_topics st
                JOIN course_sections cs ON st.section_id = cs.section_id
                LEFT JOIN topic_content tc ON st.topic_id = tc.topic_id
                LEFT JOIN section_quizzes sq ON st.topic_id = sq.topic_id
                LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ?
                WHERE st.topic_id = ?";
$stmt = $conn->prepare($topic_query);
$stmt->bind_param("ii", $enrollment_id, $topic_id);
$stmt->execute();
$topic_result = $stmt->get_result();

if ($topic_result->num_rows === 0) {
    // Topic not found
    header("Location: learn.php?course_id=" . $course_id);
    exit();
}

$topic = $topic_result->fetch_assoc();
$section_id = $topic['section_id'];
$section_title = $topic['section_title'];
$topic_title = $topic['topic_title'] ?? $topic['content_title'];
$content_type = $topic['content_type'];
$completion_status = $topic['completion_status'];
$last_position = $topic['last_position'];

// Get video source details if content is a video
$video_source = null;
if ($content_type === 'video') {
    // First try with content_id if available
    if (!empty($topic['content_id'])) {
        $video_query = "SELECT vs.provider, vs.source_url, vs.duration_seconds 
                       FROM video_sources vs
                       WHERE vs.content_id = ?";
        $stmt = $conn->prepare($video_query);
        $stmt->bind_param("i", $topic['content_id']);
        $stmt->execute();
        $video_result = $stmt->get_result();
        if ($video_result->num_rows > 0) {
            $video_source = $video_result->fetch_assoc();
            
            // Check if we have a YouTube URL but provider is not set to YouTube
            if (!empty($video_source['source_url']) && 
                (strpos($video_source['source_url'], 'youtube.com') !== false || 
                 strpos($video_source['source_url'], 'youtu.be') !== false) && 
                $video_source['provider'] != 'YouTube') {
                $video_source['provider'] = 'YouTube';
            }
        }
    }
    
    // If no video source was found by content_id, try with topic_id
    if (!$video_source) {
        $video_query = "SELECT vs.provider, vs.source_url, vs.duration_seconds 
                       FROM video_sources vs
                       JOIN topic_content tc ON vs.content_id = tc.content_id
                       WHERE tc.topic_id = ?";
        $stmt = $conn->prepare($video_query);
        $stmt->bind_param("i", $topic_id);
        $stmt->execute();
        $video_result = $stmt->get_result();
        if ($video_result->num_rows > 0) {
            $video_source = $video_result->fetch_assoc();
            
            // Check if we have a YouTube URL but provider is not set to YouTube
            if (!empty($video_source['source_url']) && 
                (strpos($video_source['source_url'], 'youtube.com') !== false || 
                 strpos($video_source['source_url'], 'youtu.be') !== false) && 
                $video_source['provider'] != 'YouTube') {
                $video_source['provider'] = 'YouTube';
            }
        }
    }
    
    // If video source is still not found, but we have a video_url in the topic data, create a simple video source
    if (!$video_source && !empty($topic['video_url'])) {
        $video_source = [
            'source_url' => $topic['video_url'],
            'provider' => 'HTML5', // Default to HTML5
            'duration_seconds' => 0
        ];
        
        // Auto-detect YouTube or Vimeo links
        if (strpos($topic['video_url'], 'youtube.com') !== false || 
            strpos($topic['video_url'], 'youtu.be') !== false) {
            $video_source['provider'] = 'YouTube';
        } elseif (strpos($topic['video_url'], 'vimeo.com') !== false) {
            $video_source['provider'] = 'Vimeo';
        }
    }
}

// Get topic resources
$resources = [];
$resources_query = "SELECT resource_id, resource_path 
                   FROM topic_resources 
                   WHERE topic_id = ?";
$stmt = $conn->prepare($resources_query);
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$resources_result = $stmt->get_result();
while ($resource = $resources_result->fetch_assoc()) {
    $resources[] = $resource;
}

// Get next and previous topics for navigation
$next_topic_query = "SELECT st.topic_id 
                     FROM section_topics st 
                     WHERE st.section_id = ? AND st.position > 
                        (SELECT position FROM section_topics WHERE topic_id = ?) 
                     ORDER BY st.position ASC 
                     LIMIT 1";
$stmt = $conn->prepare($next_topic_query);
$stmt->bind_param("ii", $section_id, $topic_id);
$stmt->execute();
$next_topic_result = $stmt->get_result();
$next_topic_id = null;
if ($next_topic_result->num_rows > 0) {
    $next_topic = $next_topic_result->fetch_assoc();
    $next_topic_id = $next_topic['topic_id'];
}

$prev_topic_query = "SELECT st.topic_id 
                     FROM section_topics st 
                     WHERE st.section_id = ? AND st.position < 
                        (SELECT position FROM section_topics WHERE topic_id = ?) 
                     ORDER BY st.position DESC 
                     LIMIT 1";
$stmt = $conn->prepare($prev_topic_query);
$stmt->bind_param("ii", $section_id, $topic_id);
$stmt->execute();
$prev_topic_result = $stmt->get_result();
$prev_topic_id = null;
if ($prev_topic_result->num_rows > 0) {
    $prev_topic = $prev_topic_result->fetch_assoc();
    $prev_topic_id = $prev_topic['topic_id'];
}

// Fetch all topics for this section to build the sidebar
$section_topics_query = "SELECT st.topic_id, st.title as topic_title, st.is_previewable, st.position,
                        tc.content_type, 
                        COALESCE(p.completion_status, 'Not Started') as completion_status,
                        st.topic_id = ? as is_current_topic
                    FROM section_topics st
                    LEFT JOIN topic_content tc ON st.topic_id = tc.topic_id
                    LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ?
                    WHERE st.section_id = ?
                    ORDER BY st.position";
$stmt = $conn->prepare($section_topics_query);
$stmt->bind_param("iii", $topic_id, $enrollment_id, $section_id);
$stmt->execute();
$section_topics_result = $stmt->get_result();
$section_topics = [];
while ($section_topic = $section_topics_result->fetch_assoc()) {
    $section_topics[] = $section_topic;
}

// Update the current topic in enrollments table
$update_current_topic = "UPDATE enrollments 
                        SET current_topic_id = ? 
                        WHERE enrollment_id = ?";
$update_stmt = $conn->prepare($update_current_topic);
$update_stmt->bind_param("ii", $topic_id, $enrollment_id);
$update_stmt->execute();

// Calculate section progress for the progress bar
$section_progress_query = "SELECT 
                          COUNT(DISTINCT CASE WHEN p.completion_status = 'Completed' THEN st.topic_id END) as completed_topics,
                          COUNT(DISTINCT st.topic_id) as total_topics
                          FROM section_topics st
                          LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ?
                          WHERE st.section_id = ?";
$stmt = $conn->prepare($section_progress_query);
$stmt->bind_param("ii", $enrollment_id, $section_id);
$stmt->execute();
$section_progress_result = $stmt->get_result();
$section_progress = $section_progress_result->fetch_assoc();

$section_percentage = 0;
if ($section_progress['total_topics'] > 0) {
    $section_percentage = round(($section_progress['completed_topics'] / $section_progress['total_topics']) * 100);
}

// Calculate course progress for the progress bar
$course_progress_query = "SELECT 
                         COUNT(DISTINCT CASE WHEN p.completion_status = 'Completed' THEN st.topic_id END) as completed_topics,
                         COUNT(DISTINCT st.topic_id) as total_topics
                         FROM course_sections cs
                         JOIN section_topics st ON cs.section_id = st.section_id
                         LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ?
                         WHERE cs.course_id = ?";
$stmt = $conn->prepare($course_progress_query);
$stmt->bind_param("ii", $enrollment_id, $course_id);
$stmt->execute();
$course_progress_result = $stmt->get_result();
$course_progress = $course_progress_result->fetch_assoc();

$course_percentage = 0;
if ($course_progress['total_topics'] > 0) {
    $course_percentage = round(($course_progress['completed_topics'] / $course_progress['total_topics']) * 100);
}

// Helper function to get content type icon
function getContentTypeIcon($content_type) {
    switch ($content_type) {
        case 'video':
            return 'bi-play-circle-fill';
        case 'text':
            return 'bi-file-text-fill';
        case 'link':
            return 'bi-link-45deg';
        case 'document':
            return 'bi-file-earmark-fill';
        default:
            return 'bi-circle-fill';
    }
}

// Helper function to format duration
function formatDuration($seconds) {
    if (!$seconds) return "N/A";
    
    $minutes = floor($seconds / 60);
    return $minutes . " min";
}

// Helper function to extract YouTube video ID
function extractYoutubeID($url) {
    // Handle youtu.be short links
    if (strpos($url, 'youtu.be') !== false) {
        $pattern = '/youtu\.be\/([a-zA-Z0-9_-]{11})/i';
        preg_match($pattern, $url, $matches);
        return isset($matches[1]) ? $matches[1] : '';
    }
    
    // Handle youtube.com links
    $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i';
    preg_match($pattern, $url, $matches);
    return isset($matches[1]) ? $matches[1] : '';
}

// Helper function to extract Vimeo video ID
function extractVimeoID($url) {
    $pattern = '/(?:vimeo\.com\/(?:video\/|channels\/.*\/|groups\/.*\/videos\/|album\/.*\/video\/|)|\d+)(\d+)(?:$|\/|\?)/i';
    preg_match($pattern, $url, $matches);
    return isset($matches[1]) ? $matches[1] : '';
}

// Handle topic completion (mark as completed)
// Handle topic completion (mark as completed) with improved redirection
if (isset($_POST['mark_completed']) && $_POST['mark_completed'] == 1) {
    // Check if there's already a progress record
    $check_progress = "SELECT progress_id FROM progress WHERE enrollment_id = ? AND topic_id = ?";
    $stmt = $conn->prepare($check_progress);
    $stmt->bind_param("ii", $enrollment_id, $topic_id);
    $stmt->execute();
    $progress_check = $stmt->get_result();
    
    if ($progress_check->num_rows > 0) {
        // Update existing progress
        $update_progress = "UPDATE progress 
                           SET completion_status = 'Completed', 
                               completion_date = NOW() 
                           WHERE enrollment_id = ? AND topic_id = ?";
        $stmt = $conn->prepare($update_progress);
        $stmt->bind_param("ii", $enrollment_id, $topic_id);
        $stmt->execute();
    } else {
        // Insert new progress record
        $insert_progress = "INSERT INTO progress 
                          (enrollment_id, topic_id, completion_status, completion_date) 
                          VALUES (?, ?, 'Completed', NOW())";
        $stmt = $conn->prepare($insert_progress);
        $stmt->bind_param("ii", $enrollment_id, $topic_id);
        $stmt->execute();
    }
    
    // Calculate overall course progress to update enrollments table
    $progress_query = "SELECT 
                        COUNT(DISTINCT CASE WHEN p.completion_status = 'Completed' THEN st.topic_id END) as completed_topics,
                        COUNT(DISTINCT st.topic_id) as total_topics
                       FROM course_sections cs
                       JOIN section_topics st ON cs.section_id = st.section_id
                       LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ?
                       WHERE cs.course_id = ?";
    $stmt = $conn->prepare($progress_query);
    $stmt->bind_param("ii", $enrollment_id, $course_id);
    $stmt->execute();
    $progress_result = $stmt->get_result();
    $progress_data = $progress_result->fetch_assoc();
    
    $completed_percentage = 0;
    if ($progress_data['total_topics'] > 0) {
        $completed_percentage = round(($progress_data['completed_topics'] / $progress_data['total_topics']) * 100);
    }
    
    // Update the completion percentage in enrollments table
    $update_enrollment = "UPDATE enrollments 
                         SET completion_percentage = ?, 
                             last_accessed = NOW()
                         WHERE enrollment_id = ?";
    $stmt = $conn->prepare($update_enrollment);
    $stmt->bind_param("di", $completed_percentage, $enrollment_id);
    $stmt->execute();
    
    // Check if this section has any more uncompleted topics
    $remaining_topics_query = "SELECT COUNT(*) as remaining_count
                              FROM section_topics st
                              LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ?
                              WHERE st.section_id = ? 
                              AND (p.completion_status IS NULL OR p.completion_status != 'Completed')
                              AND st.topic_id != ?"; // Exclude current topic which we just completed
    $stmt = $conn->prepare($remaining_topics_query);
    $stmt->bind_param("iii", $enrollment_id, $section_id, $topic_id);
    $stmt->execute();
    $remaining_result = $stmt->get_result();
    $remaining_data = $remaining_result->fetch_assoc();
    $remaining_topics = $remaining_data['remaining_count'];
    
    if ($remaining_topics > 0 && $next_topic_id) {
        // If there are more topics to complete in this section, go to the next topic
        header("Location: course-content.php?course_id=" . $course_id . "&topic=" . $next_topic_id);
    } else {
        // If all topics in this section are completed, go back to the course overview
        header("Location: learn.php?course_id=" . $course_id . "&section=" . $section_id);
    }
    exit();
}
// At the end of your file, after all processing:
ob_end_flush();

// Close database connection
$stmt->close();
$conn->close();
?>

<!-- Main Content -->
<main id="content" role="main" class="bg-light">
    <!-- Breadcrumb -->
    <div class="container content-space-t-1 pb-3 ">
        <div class="row align-items-lg-center">
            <div class="col-lg mb-2 mb-lg-0">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb bg-primary ">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="courses.php">Courses</a></li>
                        <li class="breadcrumb-item"><a href="learn.php?course_id=<?php echo $course_id; ?>"><?php echo htmlspecialchars($course_title); ?></a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($section_title); ?></li>
                    </ol>
                </nav>
                <!-- End Breadcrumb -->
            </div>
            <!-- End Col -->
        </div>
        <!-- End Row -->
    </div>
    <!-- End Breadcrumb -->

    <!-- Main container with consistent layout -->
    <div class="container mb-9">
        <div class="row d-flex justify-content-between">
            <!-- Left Sidebar with improved cursor styling for collapsible elements -->
            <div class="col-md-3" style="padding-right: 40px; margin-left: -20px;">
                <!-- Course Navigation with custom cursor on clickable elements -->
                <div class="sidebar-module mb-4">
                    <div class="navbar-expand-lg">
                        <div class="collapse navbar-collapse show">
                            <div class="nav nav-pills nav-vertical w-100">
                                <!-- Clickable header with pointer cursor -->
                                <a class="nav-link dropdown-toggle d-flex justify-content-between align-items-center clickable-header" data-bs-toggle="collapse" data-bs-target="#moduleContent">
                                    <div>
                                        <i class="bi-book nav-icon"></i>
                                        <span><?php echo htmlspecialchars($section_title); ?></span>
                                    </div>
                                    <i class="bi-chevron-down small ms-2"></i>
                                </a>

                                <div id="moduleContent" class="nav-collapse collapse show w-100">
                                    <div class="ps-3">
                                        <!-- Navigation items with proper wrapping -->
                                        <?php foreach ($section_topics as $st): ?>
                                            <a class="nav-link <?php echo ($st['is_current_topic']) ? 'active' : ''; ?>" 
                                               href="course-content.php?course_id=<?php echo $course_id; ?>&topic=<?php echo $st['topic_id']; ?>">
                                                <div class="d-flex">
                                                    <div class="flex-shrink-0 me-2">
                                                        <?php if ($st['completion_status'] === 'Completed'): ?>
                                                            <i class="bi-check-circle-fill text-success"></i>
                                                        <?php elseif ($st['is_current_topic']): ?>
                                                            <i class="bi-play-circle-fill text-primary"></i>
                                                        <?php else: ?>
                                                            <i class="<?php echo getContentTypeIcon($st['content_type']); ?> <?php echo $st['is_previewable'] ? 'text-muted' : 'text-secondary'; ?>"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="flex-grow-1 text-wrap">
                                                        <span class="<?php echo ($st['is_current_topic']) ? 'fw-bold' : ''; ?>">
                                                            <?php echo htmlspecialchars($st['topic_title']); ?>
                                                        </span>
                                                        <span class="d-block text-muted small">
                                                            <?php echo ucfirst($st['content_type'] ?? 'Content'); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Learning Progress with pointer cursor -->
                <div class="sidebar-module mb-4">
                    <div class="navbar-expand-lg">
                        <div class="collapse navbar-collapse show">
                            <div class="nav nav-pills nav-vertical w-100">
                                <!-- Clickable header with pointer cursor -->
                                <a class="nav-link dropdown-toggle d-flex justify-content-between align-items-center clickable-header" data-bs-toggle="collapse" data-bs-target="#learningProgress">
                                    <div>
                                        <i class="bi-graph-up nav-icon"></i>
                                        <span>Learning Progress</span>
                                    </div>
                                    <i class="bi-chevron-down small ms-2"></i>
                                </a>

                                <div id="learningProgress" class="nav-collapse collapse show w-100">
                                    <div class="card border-0 bg-light w-100">
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>Current Section</span>
                                                    <span class="text-muted">
                                                        <?php echo $section_progress['completed_topics']; ?>/<?php echo $section_progress['total_topics']; ?> Items
                                                    </span>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar bg-primary" style="width: <?php echo $section_percentage; ?>%"></div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>Overall Course</span>
                                                    <span class="text-muted"><?php echo $course_percentage; ?>% Complete</span>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar bg-primary" style="width: <?php echo $course_percentage; ?>%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quiz Performance section (if applicable) -->
                <?php if (isset($topic['quiz_id'])): ?>
                <div class="sidebar-module">
                    <div class="navbar-expand-lg">
                        <div class="collapse navbar-collapse show">
                            <div class="nav nav-pills nav-vertical w-100">
                                <!-- Clickable header with pointer cursor -->
                                <a class="nav-link dropdown-toggle d-flex justify-content-between align-items-center clickable-header" data-bs-toggle="collapse" data-bs-target="#quizInfo">
                                    <div>
                                        <i class="bi-clipboard-check nav-icon"></i>
                                        <span>Quiz Information</span>
                                    </div>
                                    <i class="bi-chevron-down small ms-2"></i>
                                </a>

                                <div id="quizInfo" class="nav-collapse collapse show w-100">
                                    <div class="card border-0 bg-light w-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span>Pass Mark</span>
                                                <h4 class="mb-0 text-success"><?php echo $topic['pass_mark']; ?>%</h4>
                                            </div>
                                            <?php if ($topic['time_limit']): ?>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Time Limit</span>
                                                <span><?php echo $topic['time_limit']; ?> min</span>
                                            </div>
                                            <?php endif; ?>
                                            <?php if ($completion_status === 'Completed'): ?>
                                            <button class="btn btn-success w-100 mt-3" disabled>
                                                <i class="bi-check-circle me-2"></i> Quiz Completed
                                            </button>
                                            <?php else: ?>
                                            <button class="btn btn-primary w-100 mt-3" id="startQuizBtn">
                                                <i class="bi-play-fill me-2"></i> Start Quiz
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <!-- End sidebar -->

            <!-- Main Content Area - maintaining the right-side content -->
            <div class="col-md-9" style="padding-left: 30px;">
                <!-- Video Debug Information (uncomment for debugging) -->
                <?php if(false): // Debug info - disabled by default ?>
                <div class="alert alert-info mb-3">
                    <h5>Debug Info</h5>
                    <p>Content Type: <?php echo $content_type; ?></p>
                    <?php if($content_type === 'video'): ?>
                        <p>Video Source: <?php echo print_r($video_source, true); ?></p>
                        <p>Video URL: <?php echo $topic['video_url'] ?? 'Not set'; ?></p>
                        <p>Content ID: <?php echo $topic['content_id'] ?? 'Not set'; ?></p>
                        <?php if($video_source): ?>
                            <p>YouTube ID: <?php echo extractYoutubeID($video_source['source_url']); ?></p>
                            <p>Vimeo ID: <?php echo extractVimeoID($video_source['source_url']); ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if ($content_type === 'video'): ?>
                <!-- Video Content - Improved Video Player -->
                <div class="card mb-4">
                    <div class="card-body p-0">
                        <div id="videoPlayer" class="video-player video-player-inline-btn">
                            <?php if ($video_source && ($video_source['provider'] === 'YouTube' || strpos($video_source['source_url'], 'youtu') !== false)): ?>
                                <?php 
                                $youtube_id = extractYoutubeID($video_source['source_url']); 
                                ?>
                                <?php if($youtube_id): ?>
                                <div class="ratio ratio-16x9">
                                    <iframe 
                                        src="https://www.youtube.com/embed/<?php echo $youtube_id; ?>?enablejsapi=1&rel=0&origin=<?php echo urlencode($_SERVER['HTTP_HOST']); ?>" 
                                        title="<?php echo htmlspecialchars($topic_title); ?>"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                        allowfullscreen
                                        id="youtubePlayer">
                                    </iframe>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-warning">
                                    <p>Unable to extract YouTube video ID from URL: <?php echo htmlspecialchars($video_source['source_url']); ?></p>
                                    <!-- Fallback to direct embed of the YouTube URL -->
                                    <div class="ratio ratio-16x9 mt-3">
                                        <iframe 
                                            src="<?php echo str_replace('youtu.be/', 'youtube.com/embed/', $video_source['source_url']); ?>" 
                                            title="<?php echo htmlspecialchars($topic_title); ?>"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                            allowfullscreen>
                                        </iframe>
                                    </div>
                                </div>
                                <?php endif; ?>
                            <?php elseif ($video_source && $video_source['provider'] === 'Vimeo'): ?>
                                <?php $vimeo_id = extractVimeoID($video_source['source_url']); ?>
                                <?php if($vimeo_id): ?>
                                <div class="ratio ratio-16x9">
                                    <iframe 
                                        src="https://player.vimeo.com/video/<?php echo $vimeo_id; ?>?api=1&byline=0&portrait=0&title=0" 
                                        title="<?php echo htmlspecialchars($topic_title); ?>"
                                        allow="autoplay; fullscreen; picture-in-picture" 
                                        allowfullscreen
                                        id="vimeoPlayer">
                                    </iframe>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-warning">
                                    <p>Unable to extract Vimeo video ID from URL: <?php echo htmlspecialchars($video_source['source_url']); ?></p>
                                </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <!-- Self-hosted video or direct URL -->
                                <div class="ratio ratio-16x9">
                                    <video controls id="htmlVideo" preload="metadata" controlsList="nodownload" poster="../assets/img/1920x800/img6.jpg">
                                        <?php 
                                        // Determine the video URL
                                        $video_url = null;
                                        
                                        if(!empty($topic['video_url'])) {
                                            $video_url = $topic['video_url'];
                                        } elseif($video_source && !empty($video_source['source_url'])) {
                                            $video_url = $video_source['source_url'];
                                        } else {
                                            // Fallback to a test video
                                            $video_url = 'https://file-examples.com/storage/fe7d3a0d44631509da1f416/2017/04/file_example_MP4_480_1_5MG.mp4';
                                        }
                                        ?>
                                        <source src="<?php echo htmlspecialchars($video_url); ?>" type="video/mp4">
                                        <p>Your browser does not support HTML5 video. <a href="<?php echo htmlspecialchars($video_url); ?>" download>Download the video</a> instead.</p>
                                    </video>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php elseif ($content_type === 'text'): ?>
                <!-- Text Content -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="mb-4"><?php echo htmlspecialchars($topic_title); ?></h4>
                        <div class="content-text">
                            <?php echo $topic['content_text']; ?>
                        </div>
                    </div>
                </div>

                <?php elseif ($content_type === 'link'): ?>
                <!-- External Link Content -->
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <h4 class="mb-4"><?php echo htmlspecialchars($topic_title); ?></h4>
                        <p>This content is hosted on an external website.</p>
                        <a href="<?php echo htmlspecialchars($topic['external_url']); ?>" target="_blank" class="btn btn-primary btn-lg mb-3">
                            <i class="bi-box-arrow-up-right me-2"></i> Visit External Resource
                        </a>
                        <div class="alert alert-info mt-3">
                            <i class="bi-info-circle me-2"></i> After reviewing the external content, return to this page and mark this item as completed.
                        </div>
                    </div>
                </div>

                <?php elseif ($content_type === 'document'): ?>
                <!-- Document Content -->
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <h4 class="mb-4"><?php echo htmlspecialchars($topic_title); ?></h4>
                        <p>This content is provided as a downloadable document.</p>
                        <a href="../<?php echo htmlspecialchars($topic['file_path']); ?>" target="_blank" class="btn btn-primary btn-lg mb-3">
                            <i class="bi-file-earmark-text me-2"></i> View Document
                        </a>
                        <a href="../<?php echo htmlspecialchars($topic['file_path']); ?>" download class="btn btn-outline-primary btn-lg mb-3">
                            <i class="bi-download me-2"></i> Download Document
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Tab Navigation System -->
                <div class="course-content-tabs">
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs" id="courseContentTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button">Description</button>
                        </li>
                        <?php if ($content_type === 'video'): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="transcript-tab" data-bs-toggle="tab" data-bs-target="#transcript" type="button">Transcript</button>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes" type="button">Notes</button>
                        </li>
                        <?php if (!empty($resources)): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="resources-tab" data-bs-toggle="tab" data-bs-target="#resources" type="button">Resources</button>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="discussion-tab" data-bs-toggle="tab" data-bs-target="#discussion" type="button">Discussion</button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content p-3 border border-top-0 rounded-bottom" id="courseContentTabsContent">
                        <!-- Description Tab -->
                        <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
                            <h5><?php echo htmlspecialchars($topic_title); ?></h5>
                            <p><?php echo htmlspecialchars($topic['description'] ?? 'This content covers ' . $topic_title . ' and is part of the ' . $section_title . ' section.'); ?></p>
                            <div class="mt-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi-clock me-2"></i>
                                    <span>Estimated Time: <?php echo ($topic['time_limit'] ?? 10); ?> minutes</span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi-tag-fill me-2"></i>
                                    <span>Content Type: <?php echo ucfirst($content_type); ?></span>
                                </div>
                            </div>
                        </div>

                        <?php if ($content_type === 'video'): ?>
                        <!-- Transcript Tab -->
                        <div class="tab-pane fade" id="transcript" role="tabpanel" aria-labelledby="transcript-tab">
                            <div class="transcript-container">
                                <div class="d-flex justify-content-end mb-3">
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="bi-download me-1"></i> Download Transcript
                                    </button>
                                </div>
                                <div class="transcript-content">
                                    <p><strong>00:00</strong> - Welcome to this lesson.</p>
                                    <p><strong>00:15</strong> - In this video, we'll be covering the key concepts related to this topic.</p>
                                    <p><strong>00:32</strong> - Let's start by looking at some examples.</p>
                                    <p><strong>01:05</strong> - Now let's explore the practical applications of what we've learned.</p>
                                    <!-- More transcript content would go here -->
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Notes Tab -->
                        <div class="tab-pane fade" id="notes" role="tabpanel" aria-labelledby="notes-tab">
                            <div class="notes-container">
                                <div class="mb-3">
                                    <textarea class="form-control" id="personalNotes" rows="8" placeholder="Take notes on this lesson..."></textarea>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <button class="btn btn-primary" id="saveNotes">
                                        <i class="bi-save me-1"></i> Save Notes
                                    </button>
                                    <button class="btn btn-outline-secondary" id="printNotes">
                                        <i class="bi-printer me-1"></i> Print Notes
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Resources Tab -->
                        <?php if (!empty($resources)): ?>
                        <div class="tab-pane fade" id="resources" role="tabpanel" aria-labelledby="resources-tab">
                            <div class="resources-container">
                                <h5>Additional Learning Materials</h5>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($resources as $resource): 
                                        $file_extension = pathinfo($resource['resource_path'], PATHINFO_EXTENSION);
                                        $file_icon = 'bi-file-earmark';
                                        $file_color = 'text-primary';
                                        
                                        if (in_array($file_extension, ['pdf'])) {
                                            $file_icon = 'bi-file-pdf';
                                            $file_color = 'text-danger';
                                        } elseif (in_array($file_extension, ['doc', 'docx'])) {
                                            $file_icon = 'bi-file-word';
                                            $file_color = 'text-primary';
                                        } elseif (in_array($file_extension, ['xls', 'xlsx'])) {
                                            $file_icon = 'bi-file-excel';
                                            $file_color = 'text-success';
                                        } elseif (in_array($file_extension, ['ppt', 'pptx'])) {
                                            $file_icon = 'bi-file-ppt';
                                            $file_color = 'text-warning';
                                        } elseif (in_array($file_extension, ['zip', 'rar'])) {
                                            $file_icon = 'bi-file-zip';
                                            $file_color = 'text-secondary';
                                        } elseif (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                                            $file_icon = 'bi-file-image';
                                            $file_color = 'text-info';
                                        } elseif (in_array($file_extension, ['mp3', 'wav'])) {
                                            $file_icon = 'bi-file-music';
                                            $file_color = 'text-danger';
                                        } elseif (in_array($file_extension, ['mp4', 'avi', 'mov'])) {
                                            $file_icon = 'bi-file-play';
                                            $file_color = 'text-success';
                                        } elseif (in_array($file_extension, ['html', 'css', 'js', 'php'])) {
                                            $file_icon = 'bi-file-code';
                                            $file_color = 'text-primary';
                                        }
                                        
                                        $file_name = basename($resource['resource_path']);
                                    ?>
                                    <li class="list-group-item d-flex align-items-center">
                                        <i class="<?php echo $file_icon; ?> me-3 <?php echo $file_color; ?> fs-4"></i>
                                        <div>
                                            <p class="mb-0 fw-medium"><?php echo htmlspecialchars($file_name); ?></p>
                                            <small class="text-muted"><?php echo strtoupper($file_extension); ?></small>
                                        </div>
                                        <a href="../<?php echo htmlspecialchars($resource['resource_path']); ?>" class="btn btn-sm btn-outline-primary ms-auto" download>Download</a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Discussion Tab -->
                        <div class="tab-pane fade" id="discussion" role="tabpanel" aria-labelledby="discussion-tab">
                            <div class="discussion-container">
                                <div class="mb-4">
                                    <h5>Discussion Forum</h5>
                                    <p class="text-muted">Join the conversation about this lesson with other students.</p>
                                    <div class="d-grid gap-2 d-md-flex">
                                        <button class="btn btn-primary" id="newDiscussionBtn">
                                            <i class="bi-plus-circle me-1"></i> New Discussion
                                        </button>
                                        <button class="btn btn-outline-secondary" id="filterDiscussionsBtn">
                                            <i class="bi-filter me-1"></i> Filter
                                        </button>
                                    </div>
                                </div>

                                <!-- Sample discussion threads -->
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <img src="https://via.placeholder.com/36" class="rounded-circle me-2" alt="User">
                                            <div>
                                                <h6 class="mb-0">John Doe</h6>
                                                <small class="text-muted">2 days ago</small>
                                            </div>
                                            <span class="ms-auto badge bg-primary">3 replies</span>
                                        </div>
                                        <h6>Question about this topic</h6>
                                        <p class="mb-0">Can someone explain the concept in more detail? I'm struggling to understand...</p>
                                    </div>
                                </div>

                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <img src="https://via.placeholder.com/36" class="rounded-circle me-2" alt="User">
                                            <div>
                                                <h6 class="mb-0">Jane Smith</h6>
                                                <small class="text-muted">3 days ago</small>
                                            </div>
                                            <span class="ms-auto badge bg-primary">5 replies</span>
                                        </div>
                                        <h6>Great resource recommendation</h6>
                                        <p class="mb-0">I found this helpful article that explains this topic in more detail...</p>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-center mt-4">
                                    <nav aria-label="Discussion pagination">
                                        <ul class="pagination">
                                            <li class="page-item disabled">
                                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                                            </li>
                                            <li class="page-item active" aria-current="page">
                                                <a class="page-link" href="#">1</a>
                                            </li>
                                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                                            <li class="page-item">
                                                <a class="page-link" href="#">Next</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Navigation Controls -->
                    <div class="d-flex justify-content-between p-4 bg-light">
                        <?php if ($prev_topic_id): ?>
                        <a href="course-content.php?course_id=<?php echo $course_id; ?>&topic=<?php echo $prev_topic_id; ?>" class="btn btn-outline-secondary">
                            <i class="bi-chevron-left me-2"></i> Previous Lecture
                        </a>
                        <?php else: ?>
                        <button class="btn btn-outline-secondary" disabled>
                            <i class="bi-chevron-left me-2"></i> Previous Lecture
                        </button>
                        <?php endif; ?>

                        <!-- Mark as completed form -->
                        <?php if ($completion_status !== 'Completed'): ?>
                        <form method="post">
                            <input type="hidden" name="mark_completed" value="1">
                            <button type="submit" class="btn btn-primary">Mark as Completed</button>
                        </form>
                        <?php else: ?>
                        <button class="btn btn-success" disabled>
                            <i class="bi-check-circle me-2"></i> Completed
                        </button>
                        <?php endif; ?>

                        <?php if ($next_topic_id): ?>
                        <a href="course-content.php?course_id=<?php echo $course_id; ?>&topic=<?php echo $next_topic_id; ?>" class="btn btn-outline-secondary">
                            Next Lecture <i class="bi-chevron-right ms-2"></i>
                        </a>
                        <?php else: ?>
                        <button class="btn btn-outline-secondary" disabled>
                            Next Lecture <i class="bi-chevron-right ms-2"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Custom CSS to fix the specific issues -->
<style>
    /* Fix text wrapping in sidebar */
    .text-wrap {
        word-break: break-word;
        overflow-wrap: break-word;
        width: 100%;
    }

    /* Ensure icon alignment */
    .nav-icon {
        width: 1.25rem;
        text-align: center;
        display: inline-block;
    }

    /* Adjust spacing in sidebar navigation */
    .nav-vertical .nav-link {
        padding: 0.5rem;
        white-space: normal;
    }

    /* Fix the flex layout to ensure proper wrapping */
    .d-flex .flex-grow-1 {
        min-width: 0;
    }

    /* Prevent content from breaking out of containers */
    .navbar-sidebar-aside-content {
        max-width: 100%;
        overflow-wrap: break-word;
    }

    /* Ensure proper spacing between sidebar and main content on mobile */
    @media (max-width: 767.98px) {
        .col-md-3 {
            margin-bottom: 2rem;
        }
    }

    /* Match the tab style in the screenshot */
    .nav-tabs .nav-link {
        border-radius: 0;
        padding: 0.5rem 1rem;
    }

    /* Remove the default caret from the dropdown-toggle class */
    .dropdown-toggle::after {
        display: none;
    }

    /* Add pointer cursor to clickable header elements */
    .clickable-header {
        cursor: pointer;
    }

    /* Change text color on hover instead of background */
    .clickable-header:hover {
        background-color: transparent;
        /* Remove background hover effect */
        color: var(--bs-primary) !important;
        /* Change text to primary color */
    }

    /* Also change the icon color on hover */
    .clickable-header:hover .nav-icon,
    .clickable-header:hover .bi-chevron-down {
        color: var(--bs-primary);
    }

    /* Ensure elements take up full width */
    .w-100 {
        width: 100% !important;
    }

    /* Add a little more space for the arrows */
    .nav-vertical .nav-link {
        padding-right: 0.75rem;
    }

    /* Make sure nav pills wrap properly */
    .nav-vertical {
        flex-direction: column;
        width: 100%;
    }

    /* Add slight transition for smoother interaction */
    .clickable-header .bi-chevron-down {
        transition: transform 0.2s ease;
    }

    /* Rotate arrow when expanded */
    .clickable-header[aria-expanded="true"] .bi-chevron-down {
        transform: rotate(180deg);
    }

    /* Add transition for text color change */
    .clickable-header,
    .clickable-header .nav-icon,
    .clickable-header .bi-chevron-down {
        transition: color 0.2s ease;
    }
</style>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    console.log('Document ready: Initializing video player functionality');
    
    // Optional reload button (disabled for production)
    if (false) {
        console.log('Adding manual reload button');
        const videoContainer = document.querySelector('.video-player');
        if (videoContainer) {
            const reloadButton = document.createElement('button');
            reloadButton.className = 'btn btn-sm btn-primary position-absolute top-0 end-0 m-2 z-index-2';
            reloadButton.innerHTML = '<i class="bi-arrow-clockwise"></i> Reload Video';
            reloadButton.addEventListener('click', function() {
                location.reload();
            });
            videoContainer.style.position = 'relative';
            videoContainer.appendChild(reloadButton);
        }
    }
    
    // Initialize YouTube API if YouTube player exists
    const youtubePlayer = document.getElementById('youtubePlayer');
    if (youtubePlayer) {
        console.log('YouTube player found, loading YouTube API');
        // Load YouTube API
        const tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        const firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
        
        // This function will be called when the API is ready
        window.onYouTubeIframeAPIReady = function() {
            console.log('YouTube API ready');
            const player = new YT.Player('youtubePlayer', {
                events: {
                    'onReady': onPlayerReady,
                    'onStateChange': onPlayerStateChange,
                    'onError': onPlayerError
                }
            });
            
            function onPlayerReady(event) {
                console.log('YouTube player ready');
                // Optional: Auto-play video (note: might be blocked by browser)
                // event.target.playVideo();
            }
            
            function onPlayerStateChange(event) {
                // Track video progress here if needed
                if (event.data === YT.PlayerState.ENDED) {
                    console.log('Video ended');
                    // Optional: Mark as completed when video ends
                }
            }
            
            function onPlayerError(event) {
                console.error('YouTube player error:', event.data);
                // Display fallback message or try alternative video source
                showVideoError('There was an error playing this YouTube video. Please try again later.');
            }
        };
    }
    
    // Initialize Vimeo API if Vimeo player exists
    const vimeoPlayer = document.getElementById('vimeoPlayer');
    if (vimeoPlayer) {
        console.log('Vimeo player found, loading Vimeo API');
        // Load Vimeo API
        const vimeoScript = document.createElement('script');
        vimeoScript.src = "https://player.vimeo.com/api/player.js";
        vimeoScript.onload = function() {
            console.log('Vimeo API loaded');
            // Create Vimeo player instance
            const player = new Vimeo.Player(vimeoPlayer);
            
            player.ready().then(function() {
                console.log('Vimeo player ready');
            }).catch(function(error) {
                console.error('Vimeo player error:', error);
                showVideoError('There was an error loading the Vimeo player. Please try again later.');
            });
            
            player.on('ended', function() {
                console.log('Vimeo video ended');
                // Optional: Mark as completed when video ends
            });
            
            player.on('error', function(error) {
                console.error('Vimeo playback error:', error);
                showVideoError('There was an error playing this Vimeo video. Please try again later.');
            });
        };
        document.head.appendChild(vimeoScript);
    }
    
    // Handle HTML5 video if present
    const htmlVideo = document.getElementById('htmlVideo');
    if (htmlVideo) {
        console.log('HTML5 video player found');
        
        // Force a reload of the video source to try to ensure it loads
        const currentSrc = htmlVideo.querySelector('source').src;
        htmlVideo.querySelector('source').src = currentSrc + '?t=' + new Date().getTime();
        htmlVideo.load();
        
        // Log video element details
        console.log('HTML5 video element:', htmlVideo);
        console.log('Video source:', htmlVideo.querySelector('source').src);
        
        htmlVideo.addEventListener('loadedmetadata', function() {
            console.log('Video metadata loaded', {
                duration: htmlVideo.duration,
                readyState: htmlVideo.readyState,
                networkState: htmlVideo.networkState
            });
        });
        
        htmlVideo.addEventListener('canplay', function() {
            console.log('Video can play now');
            // Uncomment to autoplay
            // htmlVideo.play().catch(e => console.error('Autoplay failed:', e));
        });
        
        htmlVideo.addEventListener('playing', function() {
            console.log('Video playing');
        });
        
        htmlVideo.addEventListener('ended', function() {
            console.log('Video ended');
            // Optional: Mark as completed when video ends
        });
        
        htmlVideo.addEventListener('error', function(e) {
            console.error('HTML5 video error:', e, htmlVideo.error);
            showVideoError('There was an error playing this video. Error code: ' + 
                           (htmlVideo.error ? htmlVideo.error.code : 'unknown'));
            
            // Try using a fallback video
            setTimeout(() => {
                console.log('Trying fallback video...');
                htmlVideo.querySelector('source').src = 'https://file-examples.com/storage/fe7d3a0d44631509da1f416/2017/04/file_example_MP4_480_1_5MG.mp4';
                htmlVideo.load();
            }, 2000);
        });
        
        // Additional events for better debugging
        htmlVideo.addEventListener('stalled', function() {
            console.warn('Video playback stalled');
        });
        
        htmlVideo.addEventListener('suspend', function() {
            console.warn('Video download suspended');
        });
        
        htmlVideo.addEventListener('abort', function() {
            console.warn('Video download aborted');
        });
    }
    
    // Helper function to show video errors
    function showVideoError(message) {
        const videoPlayer = document.getElementById('videoPlayer');
        if (videoPlayer) {
            // Create error message element
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-warning text-center p-5';
            errorDiv.innerHTML = `
                <i class="bi-exclamation-triangle fs-1 mb-3"></i>
                <h5>Video Playback Error</h5>
                <p>${message}</p>
                <button class="btn btn-primary mt-2" onclick="location.reload()">
                    <i class="bi-arrow-clockwise me-2"></i> Reload Video
                </button>
            `;
            
            // Replace video player with error message
            videoPlayer.innerHTML = '';
            videoPlayer.appendChild(errorDiv);
        }
    }
    
    // Save notes button
    const saveNotesBtn = document.getElementById('saveNotes');
    if (saveNotesBtn) {
        saveNotesBtn.addEventListener('click', function() {
            const notesContent = document.getElementById('personalNotes').value;
            // Here you would typically save to a database via AJAX
            console.log('Saving notes:', notesContent);
            alert('Notes saved successfully!');
        });
    }
    
    // Print notes button
    const printNotesBtn = document.getElementById('printNotes');
    if (printNotesBtn) {
        printNotesBtn.addEventListener('click', function() {
            const notesContent = document.getElementById('personalNotes').value;
            const printWindow = window.open('', '_blank');
            printWindow.document.write('<html><head><title>My Notes</title>');
            printWindow.document.write('<style>body{font-family:Arial,sans-serif;line-height:1.6;padding:20px}h1{color:#333}.notes-content{white-space:pre-wrap}</style>');
            printWindow.document.write('</head><body><h1>My Notes</h1>');
            printWindow.document.write('<div class="notes-content">' + notesContent + '</div>');
            printWindow.document.write('<script>window.print();setTimeout(function(){window.close()},500);<\/script>');
            printWindow.document.write('</body></html>');
            printWindow.document.close();
        });
    }
    
    // New discussion button
    const newDiscussionBtn = document.getElementById('newDiscussionBtn');
    if (newDiscussionBtn) {
        newDiscussionBtn.addEventListener('click', function() {
            alert('New discussion form would open here.');
        });
    }
    
    // Filter discussions button
    const filterDiscussionsBtn = document.getElementById('filterDiscussionsBtn');
    if (filterDiscussionsBtn) {
        filterDiscussionsBtn.addEventListener('click', function() {
            alert('Discussion filter options would show here.');
        });
    }
    
    // Start quiz button
    const startQuizBtn = document.getElementById('startQuizBtn');
    if (startQuizBtn) {
        startQuizBtn.addEventListener('click', function() {
            window.location.href = 'quiz.php?course_id=<?php echo $course_id; ?>&quiz_id=<?php echo $topic['quiz_id'] ?? 0; ?>';
        });
    }
});
</script>

<?php include '../includes/student-footer.php'; ?>