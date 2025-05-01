<?php
// student/course-content.php
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

// Add this near the top of the script
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// Access control for topics and quizzes
// Temporarily disable access control
$allow_access = true;

// Get topic or quiz ID from the URL
$topic_id = isset($_GET['topic']) ? intval($_GET['topic']) : 0;
$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;

// Get section information
$current_section_position = 0;
$current_item_position = 0;
if ($topic_id > 0) {
    // For topics, check access
    $topic_query = "SELECT cs.position as section_position, st.position as item_position, cs.course_id
                   FROM section_topics st
                   JOIN course_sections cs ON st.section_id = cs.section_id
                   WHERE st.topic_id = ?";
    $topic_stmt = $conn->prepare($topic_query);
    $topic_stmt->bind_param("i", $topic_id);
    $topic_stmt->execute();
    $topic_result = $topic_stmt->get_result();

    if ($topic_result->num_rows > 0) {
        $topic_info = $topic_result->fetch_assoc();
        $current_section_position = $topic_info['section_position'];
        $current_item_position = $topic_info['item_position'];
        $course_id = $topic_info['course_id']; // Make sure we have course_id

        // Check if this is the first topic
        $is_first_topic = false;
        if ($current_section_position == 1 && $current_item_position == 1) {
            $is_first_topic = true;
            $allow_access = true;
        }

        // Check completion status of this topic
        $topic_status_query = "SELECT completion_status 
                              FROM progress 
                              WHERE topic_id = ? AND enrollment_id = ?";
        $topic_status_stmt = $conn->prepare($topic_status_query);
        $topic_status_stmt->bind_param("ii", $topic_id, $enrollment_id);
        $topic_status_stmt->execute();
        $topic_status_result = $topic_status_stmt->get_result();

        if ($topic_status_result->num_rows > 0) {
            $status = $topic_status_result->fetch_assoc()['completion_status'];
            if ($status == 'Completed' || $status == 'In Progress') {
                $allow_access = true;
            }
        }

        if (!$allow_access && !$is_first_topic) {
            // MODIFIED: Check if the IMMEDIATE previous topic in section is completed
            if ($current_item_position > 1) {
                $prev_topic_query = "SELECT st.topic_id, p.completion_status
                                    FROM section_topics st
                                    LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ?
                                    JOIN course_sections cs ON st.section_id = cs.section_id
                                    WHERE cs.course_id = ? 
                                    AND cs.position = ? 
                                    AND st.position = ? - 1";
                $prev_topic_stmt = $conn->prepare($prev_topic_query);
                $prev_topic_stmt->bind_param("iiii", $enrollment_id, $course_id, $current_section_position, $current_item_position);
                $prev_topic_stmt->execute();
                $prev_topic_result = $prev_topic_stmt->get_result();

                if ($prev_topic_result->num_rows > 0) {
                    $prev_topic_data = $prev_topic_result->fetch_assoc();
                    if ($prev_topic_data['completion_status'] == 'Completed') {
                        $allow_access = true;
                    } else {
                        $redirect_message = "Please complete the previous topic before accessing this one.";
                    }
                } else {
                    // If no previous topic found, allow access
                    $allow_access = true;
                }
            }

            // For topics in later sections, check if the last topic in previous section is completed
            if (!$allow_access && $current_section_position > 1) {
                $last_topic_prev_section_query = "SELECT st.topic_id, p.completion_status
                                                FROM section_topics st
                                                LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ?
                                                JOIN course_sections cs ON st.section_id = cs.section_id
                                                WHERE cs.course_id = ? 
                                                AND cs.position = ? - 1
                                                ORDER BY st.position DESC
                                                LIMIT 1";
                $last_topic_stmt = $conn->prepare($last_topic_prev_section_query);
                $last_topic_stmt->bind_param("iii", $enrollment_id, $course_id, $current_section_position);
                $last_topic_stmt->execute();
                $last_topic_result = $last_topic_stmt->get_result();

                if ($last_topic_result->num_rows > 0) {
                    $last_topic_data = $last_topic_result->fetch_assoc();
                    if ($last_topic_data['completion_status'] == 'Completed') {
                        $allow_access = true;
                    } else {
                        $redirect_message = "Please complete the last topic of the previous section first.";
                    }
                } else {
                    // If no last topic found in previous section, allow access
                    $allow_access = true;
                }
            }
        }
    }
} else if ($quiz_id > 0) {
    // CHANGED: For quizzes, just get basic info but don't restrict access
    $quiz_query = "SELECT cs.position as section_position, cs.section_id, cs.course_id
                  FROM section_quizzes sq
                  JOIN course_sections cs ON sq.section_id = cs.section_id
                  WHERE sq.quiz_id = ?";
    $quiz_stmt = $conn->prepare($quiz_query);
    $quiz_stmt->bind_param("i", $quiz_id);
    $quiz_stmt->execute();
    $quiz_result = $quiz_stmt->get_result();

    if ($quiz_result->num_rows > 0) {
        $quiz_info = $quiz_result->fetch_assoc();
        $current_section_position = $quiz_info['section_position'];
        $quiz_section_id = $quiz_info['section_id'];
        $course_id = $quiz_info['course_id']; // Make sure we have course_id

        // CHANGED: No need to check access further, as we're allowing all access
        $allow_access = true;
    }
}

// If access is not allowed, redirect back to course materials with a message
if (!$allow_access) {
    // Store the message in a session variable
    $_SESSION['access_denied_message'] = $redirect_message ?: "You need to complete previous content before accessing this.";

    // Redirect back to course materials
    header("Location: course-materials.php?course_id=" . $course_id);
    exit();
}

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
                          COUNT(DISTINCT st.topic_id) as total_topics,
                          (SELECT COUNT(DISTINCT sq.quiz_id) 
                           FROM section_quizzes sq 
                           WHERE sq.section_id = ?) as total_quizzes,
                          (SELECT COUNT(DISTINCT sq.quiz_id) 
                           FROM section_quizzes sq 
                           LEFT JOIN (
                               SELECT quiz_id, MAX(is_completed) as is_completed
                               FROM student_quiz_attempts
                               WHERE user_id = ?
                               GROUP BY quiz_id
                           ) sqa ON sq.quiz_id = sqa.quiz_id
                           WHERE sq.section_id = ? AND sqa.is_completed = 1) as completed_quizzes
                          FROM section_topics st
                          LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ?
                          WHERE st.section_id = ?";
$stmt = $conn->prepare($section_progress_query);
$stmt->bind_param("iiiii", $section_id, $user_id, $section_id, $enrollment_id, $section_id);
$stmt->execute();
$section_progress_result = $stmt->get_result();
$section_progress = $section_progress_result->fetch_assoc();

$section_percentage = 0;
$total_items = $section_progress['total_topics'] + $section_progress['total_quizzes'];
$completed_items = $section_progress['completed_topics'] + $section_progress['completed_quizzes'];

if ($total_items > 0) {
    $section_percentage = round(($completed_items / $total_items) * 100);
}

// Calculate course progress for the progress bar - now including quizzes
$course_progress_query = "SELECT 
                         COUNT(DISTINCT CASE WHEN p.completion_status = 'Completed' THEN st.topic_id END) as completed_topics,
                         COUNT(DISTINCT st.topic_id) as total_topics,
                         (SELECT COUNT(DISTINCT sq.quiz_id) 
                          FROM section_quizzes sq 
                          JOIN course_sections cs2 ON sq.section_id = cs2.section_id
                          WHERE cs2.course_id = ?) as total_quizzes,
                         (SELECT COUNT(DISTINCT sq.quiz_id) 
                          FROM section_quizzes sq 
                          JOIN course_sections cs2 ON sq.section_id = cs2.section_id
                          LEFT JOIN (
                              SELECT quiz_id, MAX(score) as score, MAX(passed) as passed
                              FROM student_quiz_attempts
                              WHERE user_id = ?
                              GROUP BY quiz_id
                          ) sqa ON sq.quiz_id = sqa.quiz_id
                          WHERE cs2.course_id = ? AND sqa.passed = 1) as passed_quizzes
                         FROM course_sections cs
                         JOIN section_topics st ON cs.section_id = st.section_id
                         LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ?
                         WHERE cs.course_id = ?";

$stmt = $conn->prepare($course_progress_query);
$stmt->bind_param("iiiii", $course_id, $user_id, $course_id, $enrollment_id, $course_id);
$stmt->execute();
$course_progress_result = $stmt->get_result();
$course_progress = $course_progress_result->fetch_assoc();

$course_percentage = 0;
$total_items = $course_progress['total_topics'] + $course_progress['total_quizzes'];
$completed_items = $course_progress['completed_topics'] + $course_progress['passed_quizzes'];

if ($total_items > 0) {
    $course_percentage = round(($completed_items / $total_items) * 100);
}

// Update the completion percentage in enrollments table
$update_enrollment = "UPDATE enrollments 
                    SET completion_percentage = ?, 
                        last_accessed = NOW()
                    WHERE enrollment_id = ?";
$stmt = $conn->prepare($update_enrollment);
$stmt->bind_param("di", $course_percentage, $enrollment_id);
$stmt->execute();

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

    // Check if course is now 100% complete
    if ($completed_percentage >= 100) {
        // Check if all quizzes have been passed
        $quiz_check_query = "SELECT 
                        COUNT(sq.quiz_id) as total_quizzes,
                        COUNT(CASE WHEN sqa.score >= sq.pass_mark THEN 1 END) as passed_quizzes
                       FROM section_quizzes sq
                       JOIN course_sections cs ON sq.section_id = cs.section_id
                       LEFT JOIN (
                           SELECT quiz_id, MAX(score) as score, MAX(passed) as passed
                           FROM student_quiz_attempts
                           WHERE user_id = ?
                           GROUP BY quiz_id
                       ) sqa ON sq.quiz_id = sqa.quiz_id
                       WHERE cs.course_id = ?";
        $stmt = $conn->prepare($quiz_check_query);
        $stmt->bind_param("ii", $user_id, $course_id);
        $stmt->execute();
        $quiz_result = $stmt->get_result();
        $quiz_data = $quiz_result->fetch_assoc();

        $all_requirements_met = true;

        // Check if all quizzes were passed
        if ($quiz_data['total_quizzes'] > 0 && $quiz_data['passed_quizzes'] < $quiz_data['total_quizzes']) {
            $all_requirements_met = false;
        }

        // Check if there are any other completion requirements (e.g., assignments)
        // You can add additional checks here for other requirements

        // Only proceed with certificate and badge if all requirements are met
        if ($all_requirements_met) {
            // Include certificate and badge handlers
            require_once '../backend/certificates/CertificateHandler.php';

            // Generate certificate
            $certificateHandler = new CertificateHandler();
            $certificateResult = $certificateHandler->generateCertificateIfEligible($enrollment_id, $course_id, $user_id);

            // Store results for notification
            $_SESSION['certificate_generated'] = $certificateResult['success'] ?? false;
            $_SESSION['completion_notification'] = true;
        } else {
            // Store a notification that course is not fully complete
            $_SESSION['incomplete_requirements'] = true;
            $_SESSION['quizzes_remaining'] = $quiz_data['total_quizzes'] - $quiz_data['passed_quizzes'];
        }
    }

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
        header("Location: course-materials.php?course_id=" . $course_id . "§ion=" . $section_id);
    }
    exit();
}
// At the end of your file, after all processing:
ob_end_flush();

// Close database connection
// $stmt->close();
// $conn->close();

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
            $video_path = '../Uploads/videos/' . $topic['video_file'];
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
            $file_path = '../Uploads/documents/' . $file_path;
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

    /* Result-specific styles */
    .border-success {
        border-color: #198754 !important;
    }

    .border-danger {
        border-color: #dc3545 !important;
    }

    .bg-opacity-10 {
        --bs-bg-opacity: 0.1;
    }

    .quiz-question.border-success {
        border-left-width: 4px;
    }

    .quiz-question.border-danger {
        border-left-width: 4px;
    }

    .quiz-result-summary {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .chart-container {
        width: 150px;
        height: 150px;
        margin: 0 auto;
    }

    /* For review mode */
    .review-answers .form-check-label {
        transition: all 0.2s ease;
    }

    /* Quiz UI Styles */
    .quiz-cont .card {
        transition: all 0.2s ease;
    }

    .quiz-cont .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .quiz-cont .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1.125rem;
    }

    #quizTimer {
        font-weight: 500;
    }

    #timeRemaining {
        color: #ff6b6b;
        font-weight: bold;
    }

    .quiz-question {
        padding: 1.5rem;
        border-left: 4px solid transparent;
        margin-bottom: 1rem;
        background-color: #f8f9fa;
        border-radius: 0.375rem;
    }

    .quiz-question h5 {
        margin-bottom: 1rem;
    }

    .form-check {
        margin-bottom: 0.5rem;
        padding-left: 2rem;
    }

    .form-check-input {
        margin-left: -2rem;
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

<!-- // Add condition for incomplete requirements notification -->
<?php if (isset($_SESSION['incomplete_requirements']) && $_SESSION['incomplete_requirements']): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get the toast element
            const toast = document.getElementById('liveToast');
            const toastHeader = toast.querySelector('.toast-header h5');
            const toastBody = toast.querySelector('.toast-body');

            // Update toast content
            toastHeader.textContent = "Course Progress Update";
            toastBody.innerHTML = "You've completed all topics, but still need to <?php echo $_SESSION['quizzes_remaining'] > 0 ? 'pass ' . $_SESSION['quizzes_remaining'] . ' quiz(es)' : 'complete some requirements'; ?> to fully complete this course.";

            // Show the toast
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        });
    </script>

    <?php
    // Clear notifications after displaying them
    unset($_SESSION['incomplete_requirements']);
    unset($_SESSION['quizzes_remaining']);
    ?>
<?php endif; ?>

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

                    <?php
                    // Get the course requirements
                    $quiz_requirements_query = "SELECT
                         COUNT(sq.quiz_id) as total_quizzes,
                         COUNT(CASE WHEN sqa.score >= sq.pass_mark THEN 1 END) as passed_quizzes
                         FROM section_quizzes sq
                         JOIN course_sections cs ON sq.section_id = cs.section_id
                         LEFT JOIN (
                             SELECT quiz_id, MAX(score) as score, MAX(passed) as passed
                             FROM student_quiz_attempts
                             WHERE user_id = ?
                             GROUP BY quiz_id
                         ) sqa ON sq.quiz_id = sqa.quiz_id
                         WHERE cs.course_id = ?";
                    $stmt = $conn->prepare($quiz_requirements_query);
                    $stmt->bind_param("ii", $user_id, $course_id);
                    $stmt->execute();
                    $quiz_requirements_result = $stmt->get_result();
                    $quiz_requirements = $quiz_requirements_result->fetch_assoc();

                    $topics_requirements_query = "SELECT
                           COUNT(DISTINCT st.topic_id) as total_topics,
                           COUNT(DISTINCT CASE WHEN p.completion_status = 'Completed' THEN st.topic_id END) as completed_topics
                           FROM course_sections cs
                           JOIN section_topics st ON cs.section_id = st.section_id
                           LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ?
                           WHERE cs.course_id = ?";
                    $stmt = $conn->prepare($topics_requirements_query);
                    $stmt->bind_param("ii", $enrollment_id, $course_id);
                    $stmt->execute();
                    $topics_requirements_result = $stmt->get_result();
                    $topics_requirements = $topics_requirements_result->fetch_assoc();

                    // Calculate if all requirements are met
                    $all_topics_completed = $topics_requirements['completed_topics'] == $topics_requirements['total_topics'];
                    $all_quizzes_passed = $quiz_requirements['passed_quizzes'] == $quiz_requirements['total_quizzes'];
                    $all_requirements_met = $all_topics_completed && $all_quizzes_passed;
                    ?>

                    <div class="d-none d-md-block mb-7">
                        <h4 class="mb-3">Completion Requirements</h4>
                        <ul class="navbar-nav nav nav-vertical nav-tabs nav-tabs-borderless nav-sm">
                            <li class="nav-item">
                                <span class="nav-subtitle">Course Content</span>
                            </li>
                            <li class="nav-item">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span></span> <!-- Empty span for spacing -->
                                    <span class="text-muted" style="font-size: 10px;"><?php echo $topics_requirements['completed_topics']; ?>/<?php echo $topics_requirements['total_topics']; ?></span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar <?php echo $all_topics_completed ? 'bg-success' : 'bg-primary'; ?>"
                                        style="width: <?php echo ($topics_requirements['total_topics'] > 0) ? ($topics_requirements['completed_topics'] / $topics_requirements['total_topics']) * 100 : 0; ?>%">
                                    </div>
                                </div>
                            </li>
                            <li class="nav-item my-1 my-lg-2"></li>

                            <?php if ($quiz_requirements['total_quizzes'] > 0): ?>
                                <li class="nav-item">
                                    <span class="nav-subtitle">Course Quizzes</span>
                                </li>
                                <li class="nav-item">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span></span> <!-- Empty span for spacing -->
                                        <span class="text-muted" style="font-size: 10px;"><?php echo $quiz_requirements['passed_quizzes']; ?>/<?php echo $quiz_requirements['total_quizzes']; ?></span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar <?php echo $all_quizzes_passed ? 'bg-success' : 'bg-primary'; ?>"
                                            style="width: <?php echo ($quiz_requirements['total_quizzes'] > 0) ? ($quiz_requirements['passed_quizzes'] / $quiz_requirements['total_quizzes']) * 100 : 0; ?>%">
                                        </div>
                                    </div>
                                </li>
                                <li class="nav-item my-1 my-lg-2"></li>
                            <?php endif; ?>

                            <!-- Add more requirements here if needed -->

                            <li class="nav-item">
                                <span class="nav-subtitle">Certification Status</span>
                            </li>
                            <li class="nav-item">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span></span> <!-- Empty span for spacing -->
                                    <?php
                                    // Calculate overall completion 
                                    $total_requirements = $topics_requirements['total_topics'] + $quiz_requirements['total_quizzes'];
                                    $completed_requirements = $topics_requirements['completed_topics'] + $quiz_requirements['passed_quizzes'];
                                    ?>
                                    <span class="text-muted" style="font-size: 10px;"><?php echo $completed_requirements; ?>/<?php echo $total_requirements; ?></span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar <?php echo $all_requirements_met ? 'bg-success' : 'bg-primary'; ?>"
                                        style="width: <?php echo ($total_requirements > 0) ? ($completed_requirements / $total_requirements) * 100 : 0; ?>%">
                                    </div>
                                </div>
                            </li>
                            <li class="nav-item mt-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">All Requirements Met</span>
                                    <?php if ($all_requirements_met): ?>
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    <?php else: ?>
                                        <i class="bi bi-x-circle-fill text-danger"></i>
                                    <?php endif; ?>
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
                            <?php include '../includes/students/quiz-handler.php'; ?>
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
                                            <h5><i class="bi bi-journal-text me-2"></i>My Notes</h5>
                                            <p class="text-muted">Take notes for this topic that will be saved for your future reference.</p>
                                        </div>
                                        <div class="col-auto">
                                            <div class="btn-group">
                                                <button id="saveNotes" class="btn btn-primary">
                                                    <i class="bi bi-save me-2"></i>Save Notes
                                                </button>
                                                <button id="printNotes" class="btn btn-outline-secondary">
                                                    <i class="bi bi-printer me-2"></i>Print
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
                                                                <a href="<?php echo '../Uploads/resources/' . htmlspecialchars($resource_path); ?>" class="btn btn-sm btn-soft-primary w-100" download>
                                                                    <i class="bi bi-download me-2"></i> Download
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
                                        <h5><i class="bi bi-chat-left-text me-2"></i>Discussion</h5>
                                        <div class="d-flex gap-2">
                                            <button id="newDiscussionBtn" class="btn btn-sm btn-primary">
                                                <i class="bi bi-plus-circle me-1"></i> New Discussion
                                            </button>
                                            <button id="filterDiscussionsBtn" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-funnel me-1"></i> Filter
                                            </button>
                                        </div>
                                    </div>

                                    <div class="p-4 bg-light rounded mb-4 text-center">
                                        <div class="mb-3">
                                            <i class="bi bi-chat-square-text fs-1 text-primary"></i>
                                        </div>
                                        <h5>No discussions yet</h5>
                                        <p class="text-muted">Be the first to start a discussion about this topic.</p>
                                        <button class="btn btn-primary">
                                            <i class="bi bi-plus-circle me-2"></i>Start a Discussion
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- End Tab Content -->
                        <?php endif; ?>
                    </div>

                    <!-- Navigation Controls -->
                    <div class="d-flex justify-content-between align-items-center border-top pt-4 mt-4">
                        <?php if (isset($_GET['quiz_id'])): ?>
                            <!-- Quiz Navigation Controls -->
                            <a href="course-materials.php?course_id=<?php echo $course_id; ?>§ion=<?php echo $section_id; ?>"
                                class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left me-1"></i> Back to Course Materials
                            </a>
                        <?php else: ?>
                            <!-- Regular Content Navigation Controls -->
                            <?php if ($prev_topic_id): ?>
                                <a href="course-content.php?course_id=<?php echo $course_id; ?>&topic=<?php echo $prev_topic_id; ?>"
                                    class="btn btn-soft-primary">
                                    <i class="bi bi-chevron-left me-1"></i> Previous Lesson
                                </a>
                            <?php else: ?>
                                <button class="btn btn-soft-secondary" disabled>
                                    <i class="bi bi-chevron-left me-1"></i> Previous Lesson
                                </button>
                            <?php endif; ?>

                            <!-- Mark as completed form - only for regular content -->
                            <?php if ($completion_status !== 'Completed'): ?>
                                <form method="post">
                                    <input type="hidden" name="mark_completed" value="1">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i> Mark as Completed
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-success" disabled>
                                    <i class="bi bi-check-circle me-1"></i> Completed
                                </button>
                            <?php endif; ?>

                            <?php if ($next_topic_id): ?>
                                <a href="course-content.php?course_id=<?php echo $course_id; ?>&topic=<?php echo $next_topic_id; ?>"
                                    class="btn btn-soft-primary">
                                    Next Lesson <i class="bi bi-chevron-right ms-1"></i>
                                </a>
                            <?php else: ?>
                                <button class="btn btn-soft-secondary" disabled>
                                    Next Lesson <i class="bi bi-chevron-right ms-1"></i>
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <!-- End Navigation Controls -->
                </div>
            </div>
            <!-- End Col -->
        </div>
        <!-- End Row -->
    </div>
    <!-- End Content -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- ========== JavaScript ========== -->
<script>
   document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap components
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => new bootstrap.Tooltip(tooltip));

    const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
    popovers.forEach(popover => new bootstrap.Popover(popover));

    // Notes functionality
    const saveNotesBtn = document.getElementById('saveNotes');
    const notesTextarea = document.getElementById('personalNotes');
    const notesStatus = document.getElementById('notesStatus');
    const printNotesBtn = document.getElementById('printNotes');

    if (saveNotesBtn && notesTextarea) {
        saveNotesBtn.addEventListener('click', function() {
            const notesContent = notesTextarea.value;
            const topicId = <?php echo json_encode($topic_id); ?>;
            const userId = <?php echo json_encode($user_id); ?>;

            fetch('../backend/save_notes.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: userId,
                    topic_id: topicId,
                    content: notesContent
                })
            })
            .then(response => response.json())
            .then(data => {
                notesStatus.style.display = 'block';
                if (data.success) {
                    notesStatus.className = 'alert alert-success';
                    notesStatus.textContent = 'Notes saved successfully!';
                    const notesTab = document.querySelector('#notes-tab');
                    if (!notesTab.querySelector('.bi-check-circle-fill') && notesContent.trim()) {
                        notesTab.insertAdjacentHTML('beforeend', '<i class="bi bi-check-circle-fill text-success ms-1 small"></i>');
                    }
                } else {
                    notesStatus.className = 'alert alert-danger';
                    notesStatus.textContent = 'Failed to save notes: ' + (data.error || 'Unknown error');
                }
                setTimeout(() => {
                    notesStatus.style.display = 'none';
                }, 3000);
            })
            .catch(error => {
                notesStatus.style.display = 'block';
                notesStatus.className = 'alert alert-danger';
                notesStatus.textContent = 'Error saving notes: ' + error.message;
                setTimeout(() => {
                    notesStatus.style.display = 'none';
                }, 3000);
            });
        });
    }

    if (printNotesBtn && notesTextarea) {
        printNotesBtn.addEventListener('click', function() {
            const notesContent = notesTextarea.value;
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Notes for <?php echo htmlspecialchars($topic_title); ?></title>
                        <style>
                            body { font-family: Arial, sans-serif; padding: 20px; }
                            h1 { font-size: 24px; }
                            .notes { white-space: pre-wrap; }
                        </style>
                    </head>
                    <body>
                        <h1>Notes for <?php echo htmlspecialchars($topic_title); ?></h1>
                        <div class="notes">${notesContent.replace(/</g, '<').replace(/>/g, '>')}</div>
                    </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        });
    }

    // Quiz UI functionality
    const isQuiz = <?php echo isset($_GET['quiz_id']) ? 'true' : 'false'; ?>;

    if (isQuiz) {
        const startQuizBtn = document.getElementById('confirmStartQuiz');
        const quizOverview = document.querySelector('.quiz-cont .card');
        const quizQuestions = document.getElementById('quizQuestions');
        const sidebar = document.querySelector('.col-md-4.col-lg-3');
        const contentColumn = document.querySelector('.col-md-8.col-lg-9');
        const navigationControls = document.querySelector('.d-flex.justify-content-between.align-items-center.border-top');
        const startBtn = document.getElementById('startQuizBtn');
        const timeLimit = <?php echo isset($quiz) ? (int)$quiz['time_limit'] : 0; ?>;
        let timerInterval;
        let attemptCount = parseInt(startBtn.dataset.currentAttempts) || 0;
        const maxAttempts = parseInt(startBtn.dataset.maxAttempts) || 5;

        if (startQuizBtn) {
            startQuizBtn.addEventListener('click', function() {
                if (!quizOverview || !quizQuestions) {
                    console.error('Quiz UI elements missing');
                    return;
                }

                // Hide the overview and show questions
                quizOverview.style.display = 'none';
                quizQuestions.style.display = 'block';

                // Hide sidebar
                if (sidebar) {
                    sidebar.style.display = 'none';
                }

                // Make content full width
                if (contentColumn) {
                    contentColumn.classList.remove('col-md-8', 'col-lg-9');
                    contentColumn.classList.add('col-12');
                }

                // Hide navigation
                if (navigationControls) {
                    navigationControls.style.display = 'none';
                }

                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('startQuizModal'));
                if (modal) {
                    modal.hide();
                }

                // Load questions
                const questionContainer = document.getElementById('questionContainer');
                const submitButtonWrapper = document.getElementById('submitButtonWrapper');
                const submitBtn = document.getElementById('submitQuiz');
                if (!questionContainer || !submitButtonWrapper || !submitBtn) {
                    console.error('Question container, submit button wrapper, or submit button missing');
                    return;
                }

                submitButtonWrapper.style.display = 'block'; // Ensure wrapper is visible
                submitBtn.disabled = false; // Enable submit button by default

                fetch('../includes/students/quiz-questions.php?quiz_id=<?php echo $quiz['quiz_id']; ?>')
                    .then(response => response.text())
                    .then(html => {
                        questionContainer.innerHTML = html;
                        console.log('Questions loaded successfully');
                    })
                    .catch(error => {
                        console.error('Error loading questions:', error);
                        questionContainer.innerHTML = `
                            <div class="alert alert-danger">Error loading questions: ${error.message}</div>
                        `;
                    });

                // Start timer if applicable
                if (timeLimit > 0) {
                    let timeLeft = timeLimit * 60;
                    const timeDisplay = document.getElementById('timeRemaining');
                    if (!timeDisplay) {
                        console.error('Time display element missing');
                        return;
                    }
                    timerInterval = setInterval(() => {
                        let mins = Math.floor(timeLeft / 60);
                        let secs = timeLeft % 60;
                        timeDisplay.textContent = `${mins}:${secs < 10 ? '0' : ''}${secs}`;
                        timeLeft--;
                        if (timeLeft < 0) {
                            clearInterval(timerInterval);
                            alert("Time's up! Submitting quiz...");
                            submitQuiz();
                        }
                    }, 1000);
                }
            });
        }

        // Handle confirm submission
        const confirmSubmitBtn = document.getElementById('confirmSubmitBtn');
        if (confirmSubmitBtn) {
            confirmSubmitBtn.addEventListener('click', function() {
                const modal = bootstrap.Modal.getInstance(document.getElementById('confirmSubmitModal'));
                if (modal) {
                    modal.hide();
                }
                submitQuiz();
            });
        }

        // Submit quiz function
        function submitQuiz() {
            const questionContainer = document.getElementById('questionContainer');
            const quizTimer = document.getElementById('quizTimer');
            const submitButtonWrapper = document.getElementById('submitButtonWrapper');

            if (!questionContainer || !submitButtonWrapper) {
                console.error('Question container or submit button wrapper missing');
                return;
            }

            // Clear timer
            if (timerInterval) {
                clearInterval(timerInterval);
            }

            // Hide timer and submit button
            if (quizTimer) quizTimer.style.display = 'none';
            submitButtonWrapper.style.display = 'none';

            // Increment attempt count
            attemptCount++;
            startBtn.dataset.currentAttempts = attemptCount;

            // Update previous attempts list
            const attemptsList = document.getElementById('attemptsList');
            if (!attemptsList) {
                console.error('Attempts list missing');
                return;
            }
            const newAttempt = `
                <div class="border rounded p-3 bg-white shadow-sm d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold">Attempt ${attemptCount}</div>
                        <small class="text-muted">April 30, 2025 - ${new Date().toLocaleTimeString()}</small>
                    </div>
                    <div class="text-end">
                        <div class="fs-5 fw-semibold text-success">85% <span class="badge bg-success ms-2">Passed</span></div>
                        <div class="small text-muted">17/20 · 09:13 mins</div>
                    </div>
                </div>
            `;
            attemptsList.insertAdjacentHTML('afterbegin', newAttempt);

            // Keep only the last 5 attempts
            const attempts = attemptsList.querySelectorAll('div');
            if (attempts.length > 5) {
                attempts[attempts.length - 1].remove();
            }

            // Set button to No Attempts Left
            startBtn.disabled = true;
            startBtn.innerHTML = '<i class="bi bi-lock-fill me-2"></i>No Attempts Left';

            // Show hardcoded result summary
            questionContainer.innerHTML = `
                <div class="quiz-result-summary text-center p-4 border rounded bg-light">
                    <div class="mb-3">
                        <i class="bi bi-patch-check-fill text-success fs-1"></i>
                        <h4 class="mt-2">Quiz Submitted Successfully</h4>
                        <p class="text-muted">Well done! Here's how you performed.</p>
                        <span class="badge bg-success text-center">Passed</span>
                    </div>
                    <div class="row justify-content-center g-3 mb-4">
                        <div class="col-md-4">
                            <div class="border rounded p-3 bg-white shadow-sm h-100">
                                <p class="mb-1 text-muted">Score</p>
                                <h3 class="text-success">85%</h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 bg-white shadow-sm h-100">
                                <p class="mb-1 text-muted">Correct Answers</p>
                                <h5>17 / 20</h5>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 bg-white shadow-sm h-100">
                                <p class="mb-1 text-muted">Time Taken</p>
                                <h5>09:13</h5>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary" id="returnToQuizOverview">
                        <i class="bi bi-arrow-left-circle me-1"></i> Return to Quiz
                    </button>
                </div>
            `;

            // Add event listener for return button
            const returnBtn = document.getElementById('returnToQuizOverview');
            if (returnBtn) {
                returnBtn.addEventListener('click', function() {
                    if (!quizQuestions || !quizOverview) {
                        console.error('Quiz UI elements missing on return');
                        return;
                    }

                    // Reset UI to quiz overview
                    quizQuestions.style.display = 'none';
                    quizOverview.style.display = 'block';

                    // Restore sidebar
                    if (sidebar) {
                        sidebar.style.display = 'block';
                    }

                    // Restore content column width
                    if (contentColumn) {
                        contentColumn.classList.remove('col-12');
                        contentColumn.classList.add('col-md-8', 'col-lg-9');
                    }

                    // Restore navigation controls
                    if (navigationControls) {
                        navigationControls.style.display = 'flex';
                    }

                    // Start cooldown
                    startCooldown();
                });
            }
        }

        // Cooldown function
        function startCooldown() {
            const cooldownTimer = document.getElementById('cooldownTimer');
            if (!cooldownTimer) {
                console.error('Cooldown timer element missing');
                return;
            }

            let cooldownTime = 10; // 10 seconds for countdown
            startBtn.disabled = true;
            cooldownTimer.style.display = 'block';
            cooldownTimer.classList.remove('blink');
            cooldownTimer.innerHTML = `Cooldown: ${cooldownTime}s`;

            const cooldownInterval = setInterval(() => {
                cooldownTime--;
                cooldownTimer.innerHTML = `Cooldown: ${cooldownTime}s`;
                console.log(`Cooldown time: ${cooldownTime}s`);
                if (cooldownTime <= 0) {
                    clearInterval(cooldownInterval);
                    cooldownTimer.innerHTML = 'Attempt Reset';
                    cooldownTimer.classList.add('blink');
                    setTimeout(() => {
                        cooldownTimer.style.display = 'none';
                        cooldownTimer.classList.remove('blink');
                        startBtn.disabled = false;
                        startBtn.innerHTML = '<i class="bi bi-play-circle me-2"></i>Start Quiz';
                        attemptCount = 0; // Reset attempt count
                        startBtn.dataset.currentAttempts = attemptCount;
                        console.log('Attempt count reset to:', attemptCount);
                    }, 3000); // Blink for 3 seconds
                }
            }, 1000);
        }
    }

    // Video progress tracking (if video content)
    <?php if ($content_type === 'video' && !empty($video_source) && $video_source['provider'] === 'HTML5'): ?>
        const video = document.querySelector('video');
        if (video) {
            video.addEventListener('timeupdate', function() {
                const currentTime = video.currentTime;
                const duration = video.duration;
                if (duration && currentTime >= duration * 0.9 && !video.dataset.progressSent) {
                    video.dataset.progressSent = 'true';
                    fetch('../backend/update_video_progress.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            enrollment_id: <?php echo $enrollment_id; ?>,
                            topic_id: <?php echo $topic_id; ?>,
                            status: 'In Progress'
                        })
                    });
                }
            });

            video.addEventListener('ended', function() {
                fetch('../backend/update_video_progress.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        enrollment_id: <?php echo $enrollment_id; ?>,
                        topic_id: <?php echo $topic_id; ?>,
                        status: 'Completed'
                    })
                });
            });
        }
    <?php endif; ?>
});
</script>
<!-- ========== END JavaScript ========== -->

<?php
// Include footer
include '../includes/student-footer.php';
?>