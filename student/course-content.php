<?php
// course-content.php
ob_start();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include header
include '../includes/student-header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login if not logged in
    header("Location: ../index.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Check if course_id is provided in the URL
if (!isset($_GET['course_id']) || !is_numeric($_GET['course_id'])) {
    // Redirect to courses page if no valid course ID is provided
    header("Location: courses.php");
    exit();
}

// Get course ID from URL
$course_id = intval($_GET['course_id']);

// Check if either topic or quiz_id is provided
if (isset($_GET['topic']) && is_numeric($_GET['topic'])) {
    $topic_id = intval($_GET['topic']);
    $quiz_id = null;
} elseif (isset($_GET['quiz_id']) && is_numeric($_GET['quiz_id'])) {
    $quiz_id = intval($_GET['quiz_id']);
    $topic_id = null;
} else {
    // Neither topic nor quiz_id provided
    header("Location: course-materials.php?course_id=" . $course_id);
    exit();
}

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

// Fetch details based on whether we have a topic_id or quiz_id
if ($topic_id) {
    // Fetch topic details
    $topic_query = "SELECT st.topic_id, st.title as topic_title, st.section_id, st.is_previewable,
                    cs.title as section_title,
                    tc.content_id, tc.content_type, tc.title as content_title, 
                    tc.content_text, tc.video_url, tc.video_file, tc.external_url, tc.file_path,
                    tc.description,
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
        header("Location: course-materials.php?course_id=" . $course_id);
        exit();
    }

    $topic = $topic_result->fetch_assoc();
    $section_id = $topic['section_id'];
    $section_title = $topic['section_title'];
    $topic_title = $topic['topic_title'] ?? $topic['content_title'];
    $content_type = $topic['content_type'];
    $completion_status = $topic['completion_status'];
    $last_position = $topic['last_position'];
} else if ($quiz_id) {
    // Fetch quiz details
    $quiz_query = "SELECT sq.*, cs.section_id, cs.title as section_title,
                   CASE WHEN sqa.is_completed = 1 THEN 'Completed' ELSE 'Not Started' END as completion_status
                   FROM section_quizzes sq
                   JOIN course_sections cs ON sq.section_id = cs.section_id
                   LEFT JOIN (
                       SELECT quiz_id, MAX(is_completed) as is_completed
                       FROM student_quiz_attempts
                       WHERE user_id = ?
                       GROUP BY quiz_id
                   ) sqa ON sq.quiz_id = sqa.quiz_id
                   WHERE sq.quiz_id = ?";
    $stmt = $conn->prepare($quiz_query);
    $stmt->bind_param("ii", $user_id, $quiz_id);
    $stmt->execute();
    $quiz_result = $stmt->get_result();

    if ($quiz_result->num_rows === 0) {
        // Quiz not found
        header("Location: course-materials.php?course_id=" . $course_id);
        exit();
    }

    $topic = $quiz_result->fetch_assoc();
    $section_id = $topic['section_id'];
    $section_title = $topic['section_title'];
    $topic_title = $topic['quiz_title'];
    $content_type = 'quiz';
    $completion_status = $topic['completion_status'];
    $last_position = 0; // No position for quizzes
}



// Add this after your topic details query

// Get student notes for this topic (if any)
$notes_content = '';
$notes_exist = false;
$notes_query = "SELECT content, timestamp, updated_at 
               FROM student_notes 
               WHERE user_id = ? AND topic_id = ?";
$stmt = $conn->prepare($notes_query);
$stmt->bind_param("ii", $user_id, $topic_id);
$stmt->execute();
$notes_result = $stmt->get_result();

if ($notes_result->num_rows > 0) {
    $notes_data = $notes_result->fetch_assoc();
    $notes_content = $notes_data['content'];
    $notes_timestamp = $notes_data['timestamp'];
    $notes_updated = $notes_data['updated_at'];
    $notes_exist = true;
}

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
            if (
                !empty($video_source['source_url']) &&
                (strpos($video_source['source_url'], 'youtube.com') !== false ||
                    strpos($video_source['source_url'], 'youtu.be') !== false) &&
                $video_source['provider'] != 'YouTube'
            ) {
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
            if (
                !empty($video_source['source_url']) &&
                (strpos($video_source['source_url'], 'youtube.com') !== false ||
                    strpos($video_source['source_url'], 'youtu.be') !== false) &&
                $video_source['provider'] != 'YouTube'
            ) {
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
        if (
            strpos($topic['video_url'], 'youtube.com') !== false ||
            strpos($topic['video_url'], 'youtu.be') !== false
        ) {
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
$section_topics_query = "SELECT 
                      st.topic_id, 
                      st.title as topic_title, 
                      st.is_previewable, 
                      st.position,
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

// After fetching section_topics, fetch section quizzes
$section_quizzes_query = "SELECT 
                         sq.quiz_id,
                         sq.quiz_title,
                         sq.section_id,
                         sq.topic_id,
                         COALESCE(sqa.is_completed, 0) as is_completed,
                         CASE
                             WHEN sqa.is_completed = 1 THEN 'Completed'
                             ELSE 'Not Started'
                         END as completion_status
                         FROM section_quizzes sq
                         LEFT JOIN (
                             SELECT quiz_id, MAX(is_completed) as is_completed
                             FROM student_quiz_attempts
                             WHERE user_id = ?
                             GROUP BY quiz_id
                         ) sqa ON sq.quiz_id = sqa.quiz_id
                         WHERE sq.section_id = ?";

$stmt = $conn->prepare($section_quizzes_query);
$stmt->bind_param("ii", $user_id, $section_id);
$stmt->execute();
$section_quizzes_result = $stmt->get_result();
$section_quizzes = [];
while ($quiz = $section_quizzes_result->fetch_assoc()) {
    $section_quizzes[] = $quiz;
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
function getContentTypeIcon($content_type)
{
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
function formatDuration($seconds)
{
    if (!$seconds) return "N/A";

    $minutes = floor($seconds / 60);
    return $minutes . " min";
}

// Helper function to extract YouTube video ID
function extractYoutubeID($url)
{
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
function extractVimeoID($url)
{
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
        header("Location: course-materials.php?course_id=" . $course_id . "&section=" . $section_id);
    }
    exit();
}
// At the end of your file, after all processing:
ob_end_flush();

// Close database connection
// $stmt->close();
// $conn->close();
?>
<?php
// Helper functions for content display (place these at the top of the file, near the other helper functions)

// Helper function to get content display based on content type
function getContentDisplay($topic, $video_source, $content_type)
{
    $html = '';

    switch ($content_type) {
        case 'video':
            $html .= getVideoDisplay($topic, $video_source);
            break;
        case 'text':
            $html .= getTextDisplay($topic);
            break;
        case 'document':
            $html .= getDocumentDisplay($topic);
            break;
        case 'link':
            $html .= getLinkDisplay($topic);
            break;
        default:
            $html .= '<div class="alert alert-warning">No content available for this topic.</div>';
    }

    return $html;
}

// Function to handle video content display
function getVideoDisplay($topic, $video_source)
{
    $html = '<div class="mb-5">';

    if (!empty($video_source) && $video_source['provider'] == 'YouTube') {
        // YouTube video
        $youtube_id = extractYoutubeID($video_source['source_url']);
        if ($youtube_id) {
            $html .= '<div class="ratio ratio-16x9">
                <iframe src="https://www.youtube.com/embed/' . $youtube_id . '?rel=0" 
                    title="YouTube video player" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    allowfullscreen></iframe>
            </div>';
        }
    } else if (!empty($video_source) && $video_source['provider'] == 'Vimeo') {
        // Vimeo video
        $vimeo_id = extractVimeoID($video_source['source_url']);
        if ($vimeo_id) {
            $html .= '<div class="ratio ratio-16x9">
                <iframe src="https://player.vimeo.com/video/' . $vimeo_id . '?h=d5cc0268d7" 
                    frameborder="0" 
                    allow="autoplay; fullscreen; picture-in-picture" 
                    allowfullscreen></iframe>
            </div>';
        }
    } else {
        // Self-hosted or other video
        $video_path = '';

        if (!empty($video_source['source_url'])) {
            $video_path = $video_source['source_url'];
        } else if (!empty($topic['video_url'])) {
            $video_path = $topic['video_url'];
        } else if (!empty($topic['video_file'])) {
            $video_path = '../uploads/videos/' . $topic['video_file'];
        }

        if (!empty($video_path)) {
            $html .= '<div class="ratio ratio-16x9">
                <video controls class="w-100">
                    <source src="' . htmlspecialchars($video_path) . '" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>';
        } else {
            $html .= '<div class="alert alert-warning">Video source not available. </div>';
        }
    }

    $html .= '</div>';
    return $html;
}

// Function to handle text content display
function getTextDisplay($topic)
{
    $html = '<div class="content-text mb-5">';

    if (!empty($topic['content_text'])) {
        $html .= $topic['content_text']; // This will include any HTML formatting from the database
    } else {
        $html .= '<div class="alert alert-warning">Text content not available.</div>';
    }

    $html .= '</div>';
    return $html;
}

// Function to handle document display
function getDocumentDisplay($topic)
{
    $html = '<div class="document-display mb-5">';

    if (!empty($topic['file_path'])) {
        $file_extension = pathinfo($topic['file_path'], PATHINFO_EXTENSION);
        $file_path = $topic['file_path'];

        // Check if it's a full URL or a relative path
        if (strpos($file_path, 'http') !== 0) {
            $file_path = '../' . $file_path;
        }

        $html .= '<div class="card">';
        $html .= '<div class="card-body">';
        $html .= '<h5 class="card-title"><i class="bi bi-file-earmark me-2"></i>' . htmlspecialchars($topic['content_title']) . '</h5>';

        // For PDF files, we can try to embed them
        if (strtolower($file_extension) == 'pdf') {
            $html .= '<div class="ratio ratio-16x9 mb-3">
                <embed src="' . htmlspecialchars($file_path) . '" type="application/pdf" width="100%" height="600px" />
            </div>';
        }

        // Always provide a download link
        $html .= '<a href="' . htmlspecialchars($file_path) . '" class="btn btn-primary" download>
            <i class="bi bi-download me-2"></i>Download Document
        </a>';
        $html .= '</div></div>';
    } else {
        $html .= '<div class="alert alert-warning">Document not available.</div>';
    }

    $html .= '</div>';
    return $html;
}

// Function to handle external links
function getLinkDisplay($topic)
{
    $html = '<div class="link-display mb-5">';

    if (!empty($topic['external_url'])) {
        $url = $topic['external_url'];

        $html .= '<div class="card">';
        $html .= '<div class="card-body">';
        $html .= '<h5 class="card-title"><i class="bi bi-link-45deg me-2"></i>External Resource</h5>';

        if (!empty($topic['description'])) {
            $html .= '<p class="card-text">' . htmlspecialchars($topic['description']) . '</p>';
        }

        $html .= '<a href="' . htmlspecialchars($url) . '" class="btn btn-primary" target="_blank">
            <i class="bi bi-box-arrow-up-right me-2"></i>Visit External Resource
        </a>';
        $html .= '</div></div>';
    } else {
        $html .= '<div class="alert alert-warning">Link not available.</div>';
    }

    $html .= '</div>';
    return $html;
}
?>
<!-- Add these styles to the head section -->
<style>
    /* Content styles */
    .content-text {
        line-height: 1.8;
        font-size: 1.05rem;
    }

    .content-text h1,
    .content-text h2,
    .content-text h3,
    .content-text h4,
    .content-text h5,
    .content-text h6 {
        margin-top: 1.5rem;
        margin-bottom: 1rem;
    }

    .content-text img {
        max-width: 100%;
        height: auto;
        border-radius: 0.375rem;
    }

    .content-text ul,
    .content-text ol {
        padding-left: 1.5rem;
        margin-bottom: 1rem;
    }

    .content-text blockquote {
        padding: 1rem;
        background-color: rgba(0, 0, 0, 0.03);
        border-left: 4px solid #377dff;
        margin: 1.5rem 0;
    }

    /* Document display */
    .document-display .card {
        transition: all 0.2s ease;
    }

    .document-display .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    /* Link display */
    .link-display .card {
        transition: all 0.2s ease;
        border-left: 4px solid #377dff;
    }

    .link-display .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    /* Resource items */
    .resources-container .card {
        transition: all 0.2s ease;
    }

    .resources-container .card:hover {
        box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }
</style>

<!-- Toast -->
<div id="liveToast" class="position-fixed toast hide" role="alert" aria-live="assertive" aria-atomic="true" style="top: 20px; right: 20px; z-index: 1000;">
    <div class="toast-header">
        <div class="d-flex align-items-center flex-grow-1">
            <div class="flex-shrink-0">
                <img class="avatar avatar-sm avatar-circle" src="../favicon.ico" alt="Image description">
            </div>
            <div class="flex-grow-1 ms-3">
                <h5 class="mb-0">System Notification</h5>
                <small class="ms-auto">Just Now</small>
            </div>
            <div class="text-end">
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
    <div class="toast-body">
        Hello, world! This is a toast message.
    </div>
</div>
<!-- End Toast -->

<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main">
    <!-- Breadcrumb -->
    <div class="border-top border-bottom">
        <div class="container py-3">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="courses.php">Courses</a></li>
                    <li class="breadcrumb-item"><a href="course-materials.php?course_id=<?php echo $course_id; ?>"><?php echo htmlspecialchars($course_title); ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($section_title); ?></li>
                </ol>
            </nav>
            <!-- End Breadcrumb -->
        </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Content -->
    <div class="container content-space-t-1 content-space-b-lg-1">
        <div class="row">
            <div class="col-md-4 col-lg-3 mb-9 mb-md-0">
                <div class="pe-lg-2">
                    <div class="mb-7">
                        <ul id="navbar" class="navbar-nav nav nav-vertical nav-tabs nav-tabs-borderless nav-sm">
                            <li class="nav-item">
                                <span class="nav-subtitle"><?php echo htmlspecialchars($section_title); ?></span>
                            </li>

                            <?php
                            // Display regular topics
                            foreach ($section_topics as $section_topic):
                            ?>
                                <li class="nav-item d-flex justify-content-between align-items-start">
                                    <a class="nav-link <?php echo $section_topic['is_current_topic'] ? 'active' : ''; ?> text-wrap"
                                        href="course-content.php?course_id=<?php echo $course_id; ?>&topic=<?php echo $section_topic['topic_id']; ?>"
                                        style="max-width: 85%; word-break: break-word;">
                                        <?php echo htmlspecialchars($section_topic['topic_title']); ?>
                                    </a>

                                    <?php if ($section_topic['completion_status'] == 'Completed'): ?>
                                        <i class="bi bi-check-circle-fill text-success ms-2 flex-shrink-0 mt-1"></i>
                                    <?php elseif ($section_topic['completion_status'] == 'In Progress'): ?>
                                        <i class="bi bi-hourglass-split text-warning ms-2 flex-shrink-0 mt-1"></i>
                                    <?php else: ?>
                                        <?php
                                        $iconClass = '';
                                        switch ($section_topic['content_type']) {
                                            case 'video':
                                                $iconClass = 'bi-play-circle-fill';
                                                break;
                                            case 'document':
                                                $iconClass = 'bi-file-earmark-fill';
                                                break;
                                            case 'text':
                                                $iconClass = 'bi-file-text-fill';
                                                break;
                                            case 'link':
                                                $iconClass = 'bi-link-45deg';
                                                break;
                                            default:
                                                $iconClass = 'bi-circle-fill';
                                        }
                                        ?>
                                        <i class="<?php echo $iconClass; ?> text-secondary ms-2 flex-shrink-0 mt-1"></i>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>

                            <!-- Display quizzes associated with this section -->
                            <?php foreach ($section_quizzes as $quiz): ?>
                                <li class="nav-item d-flex justify-content-between align-items-start">
                                    <a class="nav-link <?php echo (isset($_GET['quiz_id']) && $_GET['quiz_id'] == $quiz['quiz_id']) ? 'active' : ''; ?> text-wrap"
                                        href="course-content.php?course_id=<?php echo $course_id; ?>&quiz_id=<?php echo $quiz['quiz_id']; ?>"
                                        style="max-width: 85%; word-break: break-word;">
                                        <span style="color: #ff6b6b;"><?php echo htmlspecialchars($quiz['quiz_title']); ?></span>
                                    </a>

                                    <?php if ($quiz['completion_status'] == 'Completed'): ?>
                                        <i class="bi bi-check-circle-fill text-success ms-2 flex-shrink-0 mt-1"></i>
                                    <?php else: ?>
                                        <i class="bi bi-question-circle-fill text-secondary ms-2 flex-shrink-0 mt-1"></i>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>

                            <?php if (count($section_topics) + count($section_quizzes) > 6): ?>
                                <li class="nav-item">
                                    <a class="nav-link dropdown-toggle text-wrap" href="#moreContents" role="button"
                                        data-bs-toggle="collapse" aria-expanded="false" aria-controls="moreContents"
                                        style="word-break: break-word;">More</a>
                                    <div id="moreContents" class="nav-collapse collapse">
                                        <!-- Additional content items here -->
                                    </div>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <div class="d-none d-md-block mb-7">
                        <h4 class="mb-3">Learning Progress</h4>
                        <ul id="navbar" class="navbar-nav nav nav-vertical nav-tabs nav-tabs-borderless nav-sm">
                            <li class="nav-item">
                                <span class="nav-subtitle">Current Course Section</span>
                            </li>
                            <li class="nav-item">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span></span> <!-- Empty span for spacing -->
                                    <span class="text-muted" style="font-size: 10px;"><?php echo $section_percentage; ?>%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-primary" style="width: <?php echo $section_percentage; ?>%"></div>
                                </div>
                            </li>
                            <li class="nav-item my-1 my-lg-2"></li>
                            <li class="nav-item">
                                <span class="nav-subtitle">Overall Course</span>
                            </li>
                            <li class="nav-item">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span></span> <!-- Empty span for spacing -->
                                    <span class="text-muted" style="font-size: 10px;"><?php echo $course_percentage; ?>%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-primary" style="width: <?php echo $course_percentage; ?>%"></div>
                                </div>
                            </li>
                        </ul>
                    </div>



                    <a class="link-sm link-secondary" href="#">
                        <i class="bi-flag me-2"></i> Report
                    </a>
                </div>
            </div>
            <!-- End Col -->

            <div class="col-md-8 col-lg-9 column-divider-md">
                <div class="ps-lg-2">
                    <!-- Content Title -->
                    <div class="mb-2">
                        <?php if (isset($_GET['quiz_id'])): ?>
                            <h2><?php echo htmlspecialchars($topic['quiz_title']); ?></h2>
                        <?php else: ?>
                            <h2><?php echo htmlspecialchars($topic['content_title'] ?? $topic['topic_title'] ?? 'Content'); ?></h2>
                        <?php endif; ?>
                    </div>

                    <!-- Dynamic Content Display -->
<div class="content-container mb-5">
    <?php if (isset($_GET['quiz_id'])): ?>
        <!-- QUIZ DISPLAY - No tabs, just the quiz -->
        <div class="quiz-cont">
            <?php
            // Fetch the quiz details
            $fetch_quiz = "SELECT * FROM section_quizzes WHERE quiz_id = ?";
            $stmt = $conn->prepare($fetch_quiz);
            $stmt->bind_param("i", $_GET['quiz_id']);
            $stmt->execute();
            $quiz_result = $stmt->get_result();

            if ($quiz_result->num_rows > 0) {
                $quiz = $quiz_result->fetch_assoc();

                // Set required variables for the quiz display component
                $_SESSION['enrollment_id'] = $enrollment_id;
                $quizId = $quiz['quiz_id'];
                $topicContent = $quiz;

                // Include the quiz display component
                include '../includes/students/quiz-display.php';
            } else {
                echo '<div class="alert alert-danger">Quiz not found</div>';
            }
            ?>
        </div>
    <?php else: ?>
        <!-- REGULAR CONTENT DISPLAY -->
        <?php echo getContentDisplay($topic, $video_source, $content_type); ?>
        
        <!-- Nav Scroller for tabs - Only shown for regular content -->
        <div class="js-nav-scroller hs-nav-scroller-horizontal">
            <span class="hs-nav-scroller-arrow-prev" style="display: none;">
                <a class="hs-nav-scroller-arrow-link" href="javascript:;">
                    <i class="bi-chevron-left"></i>
                </a>
            </span>

            <span class="hs-nav-scroller-arrow-next" style="display: none;">
                <a class="hs-nav-scroller-arrow-link" href="javascript:;">
                    <i class="bi-chevron-right"></i>
                </a>
            </span>

            <!-- Nav -->
            <ul class="nav nav-segment nav-fill mb-7" id="featuresTab" role="tablist">
                <?php if (!empty($topic['description'])): ?>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" href="#description" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" role="tab" aria-controls="description" aria-selected="true" style="min-width: 7rem;">Description</a>
                    </li>
                <?php endif; ?>

                <!-- Notes tab (always show) -->
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?php echo empty($topic['description']) ? 'active' : ''; ?>"
                        href="#notes" id="notes-tab" data-bs-toggle="tab"
                        data-bs-target="#notes" role="tab"
                        aria-controls="notes"
                        aria-selected="<?php echo empty($topic['description']) ? 'true' : 'false'; ?>"
                        style="min-width: 7rem;">
                        Notes
                        <?php if ($notes_exist): ?>
                            <i class="bi-check-circle-fill text-success ms-1 small"></i>
                        <?php endif; ?>
                    </a>
                </li>

                <?php if (!empty($resources)): ?>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" href="#resources" id="resources-tab" data-bs-toggle="tab" data-bs-target="#resources" role="tab" aria-controls="resources" aria-selected="false" style="min-width: 7rem;">Resources</a>
                    </li>
                <?php endif; ?>

                <li class="nav-item" role="presentation">
                    <a class="nav-link" href="#discussion" id="discussion-tab" data-bs-toggle="tab" data-bs-target="#discussion" role="tab" aria-controls="discussion" aria-selected="false" style="min-width: 7rem;">Discussion</a>
                </li>
            </ul>
            <!-- End Nav -->
        </div>
        <!-- End Nav Scroller -->

        <!-- Tab Content - Only for regular content -->
        <div class="tab-content" id="pills-tabContent">
            <?php if (!empty($topic['description'])): ?>
                <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
                    <h5><?php echo htmlspecialchars($topic['content_title']); ?></h5>
                    <div class="content-description">
                        <?php echo $topic['description']; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Notes Tab -->
            <div class="tab-pane fade <?php echo empty($topic['description']) ? 'show active' : ''; ?>" id="notes" role="tabpanel" aria-labelledby="notes-tab">
                <div class="row mb-4">
                    <div class="col">
                        <h5><i class="bi-journal-text me-2"></i>My Notes</h5>
                        <p class="text-muted">Take notes for this topic that will be saved for your future reference.</p>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group">
                            <button id="saveNotes" class="btn btn-primary">
                                <i class="bi-save me-2"></i>Save Notes
                            </button>
                            <button id="printNotes" class="btn btn-outline-secondary">
                                <i class="bi-printer me-2"></i>Print
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <textarea id="personalNotes" class="form-control" style="min-height: 200px" placeholder="Start typing your notes here..."><?php echo htmlspecialchars($notes_content); ?></textarea>
                </div>

                <?php if ($notes_exist): ?>
                    <div class="text-muted small">
                        Last updated: <?php echo date('F j, Y, g:i a', strtotime($notes_updated)); ?>
                    </div>
                <?php endif; ?>

                <div id="notesStatus" class="mt-2" style="display: none;"></div>
            </div>

            <?php if (!empty($resources)): ?>
                <div class="tab-pane fade" id="resources" role="tabpanel" aria-labelledby="resources-tab">
                    <h4 class="mb-3">Additional Resources</h4>
                    <div class="list-group">
                        <div class="row g-3">
                            <?php foreach ($resources as $resource): ?>
                                <?php
                                $resource_path = $resource['resource_path'];
                                $resource_name = basename($resource_path);
                                $resource_ext = strtolower(pathinfo($resource_path, PATHINFO_EXTENSION));

                                // Determine icon based on file extension
                                $icon_class = 'bi-file-earmark';
                                if (in_array($resource_ext, ['pdf'])) {
                                    $icon_class = 'bi-file-earmark-pdf';
                                } elseif (in_array($resource_ext, ['doc', 'docx'])) {
                                    $icon_class = 'bi-file-earmark-word';
                                } elseif (in_array($resource_ext, ['xls', 'xlsx'])) {
                                    $icon_class = 'bi-file-earmark-excel';
                                } elseif (in_array($resource_ext, ['ppt', 'pptx'])) {
                                    $icon_class = 'bi-file-earmark-ppt';
                                } elseif (in_array($resource_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                    $icon_class = 'bi-file-earmark-image';
                                } elseif (in_array($resource_ext, ['zip', 'rar'])) {
                                    $icon_class = 'bi-file-earmark-zip';
                                }
                                ?>
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="<?php echo $icon_class; ?> fs-2 text-primary"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="card-title mb-0"><?php echo htmlspecialchars($resource_name); ?></h6>
                                                    <p class="card-text small text-muted"><?php echo strtoupper($resource_ext); ?> file</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-transparent border-top-0">
                                            <a href="<?php echo '../' . htmlspecialchars($resource_path); ?>" class="btn btn-sm btn-soft-primary w-100" download>
                                                <i class="bi-download me-2"></i> Download
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="tab-pane fade" id="discussion" role="tabpanel" aria-labelledby="discussion-tab">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5><i class="bi-chat-left-text me-2"></i>Discussion</h5>
                    <div class="d-flex gap-2">
                        <button id="newDiscussionBtn" class="btn btn-sm btn-primary">
                            <i class="bi-plus-circle me-1"></i> New Discussion
                        </button>
                        <button id="filterDiscussionsBtn" class="btn btn-sm btn-outline-secondary">
                            <i class="bi-funnel me-1"></i> Filter
                        </button>
                    </div>
                </div>

                <div class="p-4 bg-light rounded mb-4 text-center">
                    <div class="mb-3">
                        <i class="bi-chat-square-text fs-1 text-primary"></i>
                    </div>
                    <h5>No discussions yet</h5>
                    <p class="text-muted">Be the first to start a discussion about this topic.</p>
                    <button class="btn btn-primary">
                        <i class="bi-plus-circle me-2"></i>Start a Discussion
                    </button>
                </div>
            </div>
        </div>
        <!-- End Tab Content -->
    <?php endif; ?>
</div>



<?php
// Modify the Navigation Controls section to be conditional based on content type
// This should be placed after the previous code block
?>

<!-- Navigation Controls - Show differently for quizzes vs regular content -->
<div class="d-flex justify-content-between align-items-center border-top pt-4 mt-4">
    <?php if (isset($_GET['quiz_id'])): ?>
        <!-- Quiz Navigation Controls -->
        <a href="course-materials.php?course_id=<?php echo $course_id; ?>&section=<?php echo $section_id; ?>" 
            class="btn btn-outline-primary">
            <i class="bi-arrow-left me-1"></i> Back to Course Materials
        </a>
        
        <div>
            <!-- Quiz progress will be shown here by the quiz-display.php component -->
        </div>
        
        <!-- No "Next" button during quiz -->
    <?php else: ?>
        <!-- Regular Content Navigation Controls -->
        <?php if ($prev_topic_id): ?>
            <a href="course-content.php?course_id=<?php echo $course_id; ?>&topic=<?php echo $prev_topic_id; ?>"
                class="btn btn-soft-primary">
                <i class="bi-chevron-left me-1"></i> Previous Lesson
            </a>
        <?php else: ?>
            <button class="btn btn-soft-secondary" disabled>
                <i class="bi-chevron-left me-1"></i> Previous Lesson
            </button>
        <?php endif; ?>

        <!-- Mark as completed form - only for regular content -->
        <?php if ($completion_status !== 'Completed'): ?>
            <form method="post">
                <input type="hidden" name="mark_completed" value="1">
                <button type="submit" class="btn btn-primary">
                    <i class="bi-check-circle me-1"></i> Mark as Completed
                </button>
            </form>
        <?php else: ?>
            <button class="btn btn-success" disabled>
                <i class="bi-check-circle me-1"></i> Completed
            </button>
        <?php endif; ?>

        <?php if ($next_topic_id): ?>
            <a href="course-content.php?course_id=<?php echo $course_id; ?>&topic=<?php echo $next_topic_id; ?>"
                class="btn btn-soft-primary">
                Next Lesson <i class="bi-chevron-right ms-1"></i>
            </a>
        <?php else: ?>
            <button class="btn btn-soft-secondary" disabled>
                Next Lesson <i class="bi-chevron-right ms-1"></i>
            </button>
        <?php endif; ?>
    <?php endif; ?>
</div>


                </div>
            </div>
            <!-- End Col -->
        </div>
        <!-- End Row -->
    </div>
    <!-- End Content -->
</main>
<!-- ========== END MAIN CONTENT ========== -->
<?php
// Near line 1639, find this JavaScript block and modify it to handle both topic and quiz contexts
// Look for document.addEventListener('DOMContentLoaded', function() {

// Replace the JavaScript code that's causing the error with this:
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Notes functionality
    const saveNotesBtn = document.getElementById('saveNotes');
    const personalNotes = document.getElementById('personalNotes');
    const notesStatus = document.getElementById('notesStatus');
    const notesTab = document.getElementById('notes-tab');

    // Check if we're in a quiz context
    const isQuiz = <?php echo isset($_GET['quiz_id']) ? 'true' : 'false'; ?>;
    
    // Only initialize these events for regular content, not quizzes
    if (saveNotesBtn && personalNotes && !isQuiz) {
        // Auto-save timer
        let autoSaveTimer;

        // Event for typing in notes
        personalNotes.addEventListener('input', function() {
            // Clear previous auto-save timer
            clearTimeout(autoSaveTimer);

            // Set new auto-save timer (save after 2 seconds of inactivity)
            autoSaveTimer = setTimeout(function() {
                saveNotes(true); // true = auto-save
            }, 2000);
        });

        // Save notes button click
        saveNotesBtn.addEventListener('click', function() {
            saveNotes(false); // false = manual save
        });

        // Function to save notes
        function saveNotes(isAutoSave) {
            const notesContent = personalNotes.value.trim();
            // Use proper conditional to set topicId only if it exists
            const topicId = <?php echo !empty($topic_id) ? $topic_id : 'null'; ?>;
            const videoPosition = 0; // Get current video position if needed

            // Don't save if empty (unless clearing notes) or if topicId is null
            if ((!notesContent && isAutoSave) || topicId === null) {
                return;
            }

            // Show saving indicator
            if (!isAutoSave) {
                saveNotesBtn.disabled = true;
                saveNotesBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...';
            }

            // Send to server
            fetch('../ajax/students/save-notes.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `topic_id=${topicId}&note_content=${encodeURIComponent(notesContent)}&timestamp=${videoPosition}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update status
                        notesStatus.innerHTML = isAutoSave ?
                            '<div class="alert alert-info alert-dismissible fade show py-1 px-3" role="alert"><small>Notes auto-saved <i class="bi-check-circle ms-1"></i></small><button type="button" class="btn-close btn-close-sm p-1" data-bs-dismiss="alert" aria-label="Close"></button></div>' :
                            '<div class="alert alert-success alert-dismissible fade show" role="alert">Notes saved successfully <i class="bi-check-circle ms-1"></i><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                        notesStatus.style.display = 'block';

                        // Add check icon to tab if not already there
                        if (!notesTab.querySelector('.bi-check-circle-fill')) {
                            notesTab.innerHTML += ' <i class="bi-check-circle-fill text-success ms-1 small"></i>';
                        }

                        // Auto-hide auto-save message
                        if (isAutoSave) {
                            setTimeout(() => {
                                const alert = notesStatus.querySelector('.alert');
                                if (alert) {
                                    alert.classList.remove('show');
                                    setTimeout(() => {
                                        notesStatus.style.display = 'none';
                                    }, 300);
                                }
                            }, 3000);
                        }
                    } else {
                        // Show error
                        notesStatus.innerHTML = `<div class="alert alert-danger alert-dismissible fade show" role="alert">Error saving notes: ${data.message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
                        notesStatus.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error saving notes:', error);
                    notesStatus.innerHTML = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Error saving notes. Please try again.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                    notesStatus.style.display = 'block';
                })
                .finally(() => {
                    // Reset button state for manual save
                    if (!isAutoSave) {
                        saveNotesBtn.disabled = false;
                        saveNotesBtn.innerHTML = '<i class="bi-save me-2"></i>Save Notes';
                    }
                });
        }
    }

    // Print notes button
    const printNotesBtn = document.getElementById('printNotes');
    if (printNotesBtn && personalNotes && !isQuiz) {
        printNotesBtn.addEventListener('click', function() {
            const notesContent = personalNotes.value;
            const topicTitle = '<?php echo isset($topic["content_title"]) ? addslashes(htmlspecialchars($topic["content_title"])) : ""; ?>';
            const courseName = '<?php echo isset($course_title) ? addslashes(htmlspecialchars($course_title)) : ""; ?>';

            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>My Notes: ${topicTitle}</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            line-height: 1.6;
                            padding: 30px;
                            max-width: 800px;
                            margin: 0 auto;
                        }
                        .header {
                            border-bottom: 1px solid #ddd;
                            padding-bottom: 10px;
                            margin-bottom: 20px;
                        }
                        h1 {
                            color: #333;
                            font-size: 24px;
                            margin-bottom: 5px;
                        }
                        h2 {
                            color: #666;
                            font-size: 18px;
                            font-weight: normal;
                            margin-top: 0;
                        }
                        .meta {
                            color: #777;
                            font-size: 14px;
                            margin-bottom: 20px;
                        }
                        .notes-content {
                            white-space: pre-wrap;
                            font-size: 16px;
                        }
                        @media print {
                            body {
                                padding: 0;
                            }
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>${topicTitle}</h1>
                        <h2>${courseName}</h2>
                    </div>
                    <div class="meta">
                        <p>Notes taken by: <?php echo isset($_SESSION['first_name']) && isset($_SESSION['last_name']) ? htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) : "Student"; ?></p>
                        <p>Date: ${new Date().toLocaleDateString()}</p>
                    </div>
                    <div class="notes-content">${notesContent.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>')}</div>
                    <script>
                        window.print();
                        setTimeout(function() {
                            window.close();
                        }, 500);
                    <\/script>
                </body>
                </html>
            `);
            printWindow.document.close();
        });
    }
});
</script>

<?php
// Add this JavaScript to handle quiz start/completion
// This should be added near the other JavaScript code
?>
<script>
// Quiz-specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're in a quiz context
    const isQuiz = <?php echo isset($_GET['quiz_id']) ? 'true' : 'false'; ?>;
    
    if (isQuiz) {
        // Find the quiz container
        const quizContainer = document.querySelector('.quiz-cont');
        
        // Listen for quiz start event
        document.addEventListener('quizStarted', function() {
            // Hide navigation when quiz starts
            const navigationControls = document.querySelector('.d-flex.justify-content-between.align-items-center.border-top');
            if (navigationControls) {
                navigationControls.style.display = 'none';
            }
            
            // You could also hide the sidebar if needed
            const sidebar = document.querySelector('.col-md-4.col-lg-3');
            if (sidebar) {
                sidebar.style.display = 'none';
            }
            
            // Make the content column full width
            const contentColumn = document.querySelector('.col-md-8.col-lg-9');
            if (contentColumn) {
                contentColumn.classList.remove('col-md-8', 'col-lg-9');
                contentColumn.classList.add('col-12');
            }
        });
        
        // Listen for quiz completion
        document.addEventListener('quizCompleted', function(event) {
            // Show navigation when quiz completes
            const navigationControls = document.querySelector('.d-flex.justify-content-between.align-items-center.border-top');
            if (navigationControls) {
                navigationControls.style.display = 'flex';
            }
            
            // Show the sidebar again
            const sidebar = document.querySelector('.col-md-4.col-lg-3');
            if (sidebar) {
                sidebar.style.display = 'block';
            }
            
            // Restore original column width
            const contentColumn = document.querySelector('.col-12');
            if (contentColumn) {
                contentColumn.classList.remove('col-12');
                contentColumn.classList.add('col-md-8', 'col-lg-9');
            }
            
            // Update quiz completion status in sidebar
            const quizItem = document.querySelector(`.nav-link[href*="quiz_id=<?php echo isset($_GET['quiz_id']) ? $_GET['quiz_id'] : ''; ?>"]`);
            if (quizItem && quizItem.nextElementSibling) {
                quizItem.nextElementSibling.className = 'bi bi-check-circle-fill text-success ms-2 flex-shrink-0 mt-1';
            }
        });
    }
});
</script>
<!-- // Add this JavaScript before the closing body tag -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Document ready: Initializing enhanced video player');

    // Check if we're in a quiz context
    const isQuiz = <?php echo isset($_GET['quiz_id']) ? 'true' : 'false'; ?>;
    
    // Only run video tracking code if we're not in a quiz
    if (!isQuiz) {
        // Video tracking - only initialize these variables if not in quiz mode
        const videoElements = document.querySelectorAll('video');
        const courseId = <?php echo $course_id; ?>;
        const topicId = <?php echo !empty($topic_id) ? $topic_id : 'null'; ?>;
        const enrollmentId = <?php echo $enrollment_id; ?>;
        const lastPosition = <?php echo $last_position ?? 0; ?>;

        // Only proceed if we have a valid topicId
        if (topicId !== null && videoElements.length > 0) {
            // Video completion threshold (consider video watched at 95%)
            const COMPLETION_THRESHOLD = 0.95;

            // Function to mark topic as completed via AJAX
            function markAsCompleted() {
                console.log('Marking topic as completed');

                // Show a small notification to the user
                function showToast(title, message) {
                    // Get the existing toast element
                    const toast = document.getElementById('liveToast');

                    // Update the toast content
                    toast.querySelector('.toast-header h5').textContent = title;
                    toast.querySelector('.toast-body').textContent = message;

                    // Show the toast
                    toast.classList.remove('hide');
                    toast.classList.add('show');

                    // Hide the toast after 5 seconds
                    setTimeout(() => {
                        toast.classList.remove('show');
                        toast.classList.add('hide');
                    }, 5000);
                }

                // Call this function when you want to show the notification
                showToast("Topic Completed", "This topic has been marked as completed!");

                // Send AJAX request to mark as completed
                fetch('../ajax/students/mark-topic-completed.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'enrollment_id=' + enrollmentId + '&topic_id=' + topicId
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Topic marked as completed:', data);

                        // Update UI to reflect completed status
                        const completeButton = document.querySelector('form[method="post"] button[type="submit"]');
                        if (completeButton) {
                            const completedButton = document.createElement('button');
                            completedButton.className = 'btn btn-success';
                            completedButton.disabled = true;
                            completedButton.innerHTML = '<i class="bi-check-circle me-1"></i> Completed';

                            // Replace the form with the completed button
                            completeButton.closest('form').replaceWith(completedButton);

                            // Update sidebar icon for this topic
                            const sidebarItem = document.querySelector('.nav-link.active');
                            if (sidebarItem) {
                                const sidebarIcon = sidebarItem.nextElementSibling;
                                if (sidebarIcon && sidebarIcon.classList.contains('bi')) {
                                    sidebarIcon.className = 'bi bi-check-circle-fill text-success ms-2 flex-shrink-0 mt-1';
                                }
                            }

                            // Update progress bars
                            updateProgressBars();
                        }
                    })
                    .catch(error => {
                        console.error('Error marking topic as completed:', error);
                    });
            }

            // Update progress bars after completion
            function updateProgressBars() {
                // Calculate new percentages (simulating completion of one more item)
                const sectionProgressBar = document.querySelector('.nav-item:nth-of-type(2) .progress-bar');
                const courseProgressBar = document.querySelector('.nav-item:nth-of-type(4) .progress-bar');

                if (sectionProgressBar) {
                    const sectionPercentageElem = document.querySelector('.nav-item:nth-of-type(2) .text-muted');
                    let currentPercentage = parseInt(sectionPercentageElem.textContent);
                    const sectionTopicsCount = <?php echo isset($section_progress['total_topics']) ? $section_progress['total_topics'] : 0; ?>;
                    const completedTopics = <?php echo isset($section_progress['completed_topics']) ? $section_progress['completed_topics'] : 0; ?>;
                    
                    if (sectionTopicsCount > 0) {
                        const newPercentage = Math.min(Math.ceil((completedTopics + 1) / sectionTopicsCount * 100), 100);
                        sectionPercentageElem.textContent = newPercentage + '%';
                        sectionProgressBar.style.width = newPercentage + '%';
                    }
                }

                if (courseProgressBar) {
                    const coursePercentageElem = document.querySelector('.nav-item:nth-of-type(4) .text-muted');
                    let currentPercentage = parseInt(coursePercentageElem.textContent);
                    const courseTopicsCount = <?php echo isset($course_progress['total_topics']) ? $course_progress['total_topics'] : 0; ?>;
                    const completedTopics = <?php echo isset($course_progress['completed_topics']) ? $course_progress['completed_topics'] : 0; ?>;
                    
                    if (courseTopicsCount > 0) {
                        const newPercentage = Math.min(Math.ceil((completedTopics + 1) / courseTopicsCount * 100), 100);
                        coursePercentageElem.textContent = newPercentage + '%';
                        courseProgressBar.style.width = newPercentage + '%';
                    }
                }
            }

            // Handle HTML5 video
            if (videoElements.length > 0) {
                const mainVideo = videoElements[0];
                console.log('HTML5 video player found');

                // Create poster image container for replay screen
                const videoContainer = mainVideo.closest('.ratio-16x9');
                if (videoContainer) {
                    const posterContainer = document.createElement('div');
                    posterContainer.className = 'video-poster-container position-absolute top-0 start-0 w-100 h-100 d-none';
                    posterContainer.style.backgroundColor = '#000';
                    posterContainer.style.zIndex = '2';

                    // Add poster image (if available) or use a default
                    const posterImage = document.createElement('img');
                    posterImage.className = 'w-100 h-100 object-fit-contain opacity-50';
                    posterImage.src = mainVideo.poster || '../uploads/video-poster.jpg';
                    posterImage.alt = 'Video thumbnail';

                    // Add replay button
                    const replayButton = document.createElement('button');
                    replayButton.className = 'btn btn-primary btn-lg position-absolute top-50 start-50 translate-middle';
                    replayButton.innerHTML = '<i class="bi-arrow-repeat me-2"></i>Replay Video';
                    replayButton.addEventListener('click', function() {
                        posterContainer.classList.add('d-none');
                        mainVideo.currentTime = 0;
                        mainVideo.play().catch(e => console.error('Replay failed:', e));
                    });

                    // Add to container
                    posterContainer.appendChild(posterImage);
                    posterContainer.appendChild(replayButton);
                    videoContainer.appendChild(posterContainer);
                    videoContainer.style.position = 'relative';
                }

                // Set the video to the last position if available
                if (lastPosition > 0) {
                    mainVideo.addEventListener('loadedmetadata', function() {
                        // Only seek if the last position is within the video duration
                        if (lastPosition < mainVideo.duration) {
                            mainVideo.currentTime = lastPosition;
                            console.log('Set video position to:', lastPosition);
                        }
                    });
                }

                // Track video progress
                let lastTrackedTime = 0;
                const TRACK_INTERVAL = 10; // Track every 10 seconds
                let videoCompleted = false;

                mainVideo.addEventListener('timeupdate', function() {
                    const currentTime = Math.floor(mainVideo.currentTime);
                    const completionPercentage = mainVideo.currentTime / mainVideo.duration;

                    // Only track every TRACK_INTERVAL seconds to reduce server load
                    if (currentTime - lastTrackedTime >= TRACK_INTERVAL) {
                        lastTrackedTime = currentTime;
                        trackVideoProgress(currentTime);
                    }

                    // Mark as completed if reached threshold and not already completed
                    if (completionPercentage >= COMPLETION_THRESHOLD && !videoCompleted) {
                        videoCompleted = true;
                        markAsCompleted();
                    }
                });

                // Handle video ended
                mainVideo.addEventListener('ended', function() {
                    console.log('Video ended');

                    // Show replay screen
                    const posterContainer = document.querySelector('.video-poster-container');
                    if (posterContainer) {
                        posterContainer.classList.remove('d-none');
                    }

                    // If not already marked as completed, mark it now
                    if (!videoCompleted) {
                        markAsCompleted();
                    }
                });

                // Track video progress
                function trackVideoProgress(position) {
                    // Use fetch API to send the progress to the server
                    fetch('../ajax/students/track-video-progress.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'enrollment_id=' + enrollmentId + '&topic_id=' + topicId + '&position=' + position
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log('Progress tracked', data);
                        })
                        .catch(error => {
                            console.error('Error tracking progress:', error);
                        });
                }
            }

            // YouTube API integration
            const youtubeIframes = document.querySelectorAll('iframe[src*="youtube.com"]');
            let ytPlayer = null;

            if (youtubeIframes.length > 0) {
                console.log('YouTube player found, loading YouTube API');
                // Load YouTube API
                const tag = document.createElement('script');
                tag.src = "https://www.youtube.com/iframe_api";
                const firstScriptTag = document.getElementsByTagName('script')[0];
                firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

                // Initialize YouTube players when API is ready
                window.onYouTubeIframeAPIReady = function() {
                    console.log('YouTube API ready');
                    youtubeIframes.forEach((iframe, index) => {
                        // Get the original iframe src
                        const originalSrc = iframe.src;

                        // Extract video ID
                        const videoIdMatch = originalSrc.match(/embed\/([^?]+)/);
                        const videoId = videoIdMatch ? videoIdMatch[1] : null;

                        if (!videoId) {
                            console.error('Could not extract YouTube video ID');
                            return;
                        }

                        // Replace the iframe with a div for the API to use
                        const playerId = 'youtube-player-' + index;
                        const playerDiv = document.createElement('div');
                        playerDiv.id = playerId;

                        iframe.parentNode.replaceChild(playerDiv, iframe);

                        // Create poster container for replay
                        const playerContainer = playerDiv.parentNode;
                        const posterContainer = document.createElement('div');
                        posterContainer.className = 'video-poster-container position-absolute top-0 start-0 w-100 h-100 d-none';
                        posterContainer.style.backgroundColor = '#000';
                        posterContainer.style.zIndex = '2';

                        // Add poster image
                        const posterImage = document.createElement('img');
                        posterImage.className = 'w-100 h-100 object-fit-contain opacity-50';
                        posterImage.src = "https://img.youtube.com/vi/" + videoId + "/maxresdefault.jpg";
                        posterImage.alt = 'Video thumbnail';

                        // Add replay button
                        const replayButton = document.createElement('button');
                        replayButton.className = 'btn btn-primary btn-lg position-absolute top-50 start-50 translate-middle';
                        replayButton.innerHTML = '<i class="bi-arrow-repeat me-2"></i>Replay Video';
                        replayButton.addEventListener('click', function() {
                            posterContainer.classList.add('d-none');
                            // Restart video
                            ytPlayer.seekTo(0);
                            ytPlayer.playVideo();
                        });

                        // Add to container
                        posterContainer.appendChild(posterImage);
                        posterContainer.appendChild(replayButton);

                        // Make sure container has position relative
                        if (playerContainer.style.position !== 'relative') {
                            playerContainer.style.position = 'relative';
                        }
                        playerContainer.appendChild(posterContainer);

                        // Create YouTube player
                        ytPlayer = new YT.Player(playerId, {
                            videoId: videoId,
                            playerVars: {
                                'rel': 0, // Don't show related videos
                                'showinfo': 0, // Hide video info
                                'modestbranding': 1, // Hide YouTube logo
                                'fs': 1, // Show fullscreen button
                                'start': lastPosition // Start from last position
                            },
                            events: {
                                'onStateChange': function(event) {
                                    // Track when video is paused or ended
                                    if (event.data == YT.PlayerState.PAUSED) {
                                        const currentTime = Math.floor(event.target.getCurrentTime());
                                        trackVideoProgress(currentTime);
                                    } else if (event.data == YT.PlayerState.ENDED) {
                                        console.log('YouTube video ended');

                                        // Show replay screen
                                        posterContainer.classList.remove('d-none');

                                        // Mark as completed
                                        markAsCompleted();
                                    }

                                    // Check if video is near completion
                                    if (event.data == YT.PlayerState.PLAYING) {
                                        // Start checking progress
                                        const checkProgress = setInterval(function() {
                                            if (!ytPlayer) {
                                                clearInterval(checkProgress);
                                                return;
                                            }

                                            const duration = ytPlayer.getDuration();
                                            const currentTime = ytPlayer.getCurrentTime();
                                            const completionPercentage = currentTime / duration;

                                            // If we're at or past the threshold, mark as completed
                                            if (completionPercentage >= COMPLETION_THRESHOLD) {
                                                markAsCompleted();
                                                clearInterval(checkProgress);
                                            }

                                            // Stop checking if video isn't playing
                                            if (ytPlayer.getPlayerState() !== YT.PlayerState.PLAYING) {
                                                clearInterval(checkProgress);
                                            }
                                        }, 5000); // Check every 5 seconds
                                    }
                                }
                            }
                        });

                        // Track video progress
                        function trackVideoProgress(position) {
                            fetch('../ajax/students/track-video-progress.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded',
                                    },
                                    body: 'enrollment_id=' + enrollmentId + '&topic_id=' + topicId + '&position=' + position
                                })
                                .then(response => response.json())
                                .then(data => {
                                    console.log('YouTube progress tracked', data);
                                })
                                .catch(error => {
                                    console.error('Error tracking YouTube progress:', error);
                                });
                        }
                    });
                };
            }

            // Vimeo API integration
            const vimeoIframes = document.querySelectorAll('iframe[src*="vimeo.com"]');
            let vimeoPlayer = null;

            if (vimeoIframes.length > 0) {
                console.log('Vimeo player found, loading Vimeo API');
                // Load Vimeo API
                const vimeoScript = document.createElement('script');
                vimeoScript.src = "https://player.vimeo.com/api/player.js";
                document.body.appendChild(vimeoScript);

                vimeoScript.onload = function() {
                    vimeoIframes.forEach((iframe) => {
                        // Create container for replay screen
                        const playerContainer = iframe.parentNode;
                        const posterContainer = document.createElement('div');
                        posterContainer.className = 'video-poster-container position-absolute top-0 start-0 w-100 h-100 d-none';
                        posterContainer.style.backgroundColor = '#000';
                        posterContainer.style.zIndex = '2';

                        // We'll add the poster image after getting it from the Vimeo API

                        // Add replay button
                        const replayButton = document.createElement('button');
                        replayButton.className = 'btn btn-primary btn-lg position-absolute top-50 start-50 translate-middle';
                        replayButton.innerHTML = '<i class="bi-arrow-repeat me-2"></i>Replay Video';
                        replayButton.addEventListener('click', function() {
                            posterContainer.classList.add('d-none');
                            // Restart video
                            vimeoPlayer.setCurrentTime(0);
                            vimeoPlayer.play();
                        });

                        // Add to container
                        posterContainer.appendChild(replayButton);

                        // Make sure container has position relative
                        if (playerContainer.style.position !== 'relative') {
                            playerContainer.style.position = 'relative';
                        }
                        playerContainer.appendChild(posterContainer);

                        // Initialize Vimeo player
                        vimeoPlayer = new Vimeo.Player(iframe, {
                            dnt: true // Do not track
                        });

                        // Get video metadata for poster image
                        vimeoPlayer.getVideoTitle().then(function(title) {
                            // Get the video thumbnail
                            vimeoPlayer.getVideoThumbnails().then(function(thumbnails) {
                                if (thumbnails && thumbnails.length > 0) {
                                    // Add poster image
                                    const posterImage = document.createElement('img');
                                    posterImage.className = 'w-100 h-100 object-fit-contain opacity-50';
                                    posterImage.src = thumbnails[0].url;
                                    posterImage.alt = title || 'Video thumbnail';
                                    posterContainer.insertBefore(posterImage, replayButton);
                                }
                            }).catch(function(error) {
                                console.error('Error getting Vimeo thumbnail:', error);
                            });
                        }).catch(function(error) {
                            console.error('Error getting Vimeo title:', error);
                        });

                        // Set initial position
                        if (lastPosition > 0) {
                            vimeoPlayer.setCurrentTime(lastPosition);
                        }

                        // Completed flag
                        let videoCompleted = false;

                        // Track timeupdate
                        let lastVimeoTrackedTime = 0;
                        vimeoPlayer.on('timeupdate', function(data) {
                            const currentTime = Math.floor(data.seconds);

                            // Track every 10 seconds
                            if (currentTime - lastVimeoTrackedTime >= 10) {
                                lastVimeoTrackedTime = currentTime;
                                trackVideoProgress(currentTime);
                            }

                            // Check completion
                            const completionPercentage = data.seconds / data.duration;
                            if (completionPercentage >= COMPLETION_THRESHOLD && !videoCompleted) {
                                videoCompleted = true;
                                markAsCompleted();
                            }
                        });

                        // Handle end of video
                        vimeoPlayer.on('ended', function() {
                            console.log('Vimeo video ended');

                            // Show replay screen
                            posterContainer.classList.remove('d-none');

                            // Mark as completed if not already done
                            if (!videoCompleted) {
                                markAsCompleted();
                            }
                        });

                        // Track video progress
                        function trackVideoProgress(position) {
                            fetch('../ajax/students/track-video-progress.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded',
                                    },
                                    body: 'enrollment_id=' + enrollmentId + '&topic_id=' + topicId + '&position=' + position
                                })
                                .then(response => response.json())
                                .then(data => {
                                    console.log('Vimeo progress tracked', data);
                                })
                                .catch(error => {
                                    console.error('Error tracking Vimeo progress:', error);
                                });
                        }
                    });
                };
            }
        }
    }
});</script>

<script src="../assets/js/fixed-quiz-submission.js"></script>

<?php
// Include footer
include '../includes/student-footer.php';
?>