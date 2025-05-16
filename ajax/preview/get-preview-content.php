<?php
// Path: ajax/get-preview-content.php
require '../../backend/session_start.php';
require_once '../../backend/config.php';

// Get parameters
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$topic_id = isset($_GET['topic_id']) ? intval($_GET['topic_id']) : 0;

// If no course ID or topic ID, return error
if ($course_id === 0 || $topic_id === 0) {
    echo '<div class="alert alert-danger">Missing parameters</div>';
    exit;
}

// Check if topic exists and is previewable
$stmt = $conn->prepare("
    SELECT st.topic_id, st.title, st.section_id, st.is_previewable, cs.course_id  
    FROM section_topics st
    JOIN course_sections cs ON st.section_id = cs.section_id
    WHERE st.topic_id = ? AND cs.course_id = ?
");
$stmt->bind_param("ii", $topic_id, $course_id);
$stmt->execute();
$result = $stmt->get_result();
$topic = $result->fetch_assoc();
$stmt->close();

// If topic doesn't exist or doesn't belong to course
if (!$topic) {
    echo '<div class="alert alert-danger">Topic not found</div>';
    exit;
}

// Check if topic is previewable or if user is enrolled or is instructor
$is_previewable = ($topic['is_previewable'] == 1);
$is_enrolled = false;
$is_instructor = false;

if (isset($_SESSION['user_id'])) {
    // Check if user is enrolled
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ? AND status = 'Active'");
    $stmt->bind_param("ii", $user_id, $course_id);
    $stmt->execute();
    $is_enrolled = ($stmt->get_result()->num_rows > 0);
    $stmt->close();
    
    // FIXED: Check if user is an instructor for this course using course_instructors junction table
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'instructor' && isset($_SESSION['instructor_id'])) {
        $instructor_id = $_SESSION['instructor_id'];
        $stmt = $conn->prepare("
            SELECT ci.course_id 
            FROM course_instructors ci
            WHERE ci.course_id = ? 
            AND ci.instructor_id = ?
            AND ci.deleted_at IS NULL
        ");
        $stmt->bind_param("ii", $course_id, $instructor_id);
        $stmt->execute();
        $is_instructor = ($stmt->get_result()->num_rows > 0);
        $stmt->close();
    }
}

// If not previewable and not enrolled and not instructor, deny access
if (!$is_previewable && !$is_enrolled && !$is_instructor) {
    echo '<div class="alert alert-warning">This content is not available for preview</div>';
    exit;
}

// Get topic content
$stmt = $conn->prepare("SELECT * FROM topic_content WHERE topic_id = ?");
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$content = $stmt->get_result()->fetch_assoc();
$stmt->close();

// If no content, show message
if (!$content) {
    echo '<div class="alert alert-info">No content available for this lesson</div>';
    exit;
}

// Display content based on type
echo '<div class="preview-content">';

// Content display based on type
switch ($content['content_type']) {
    case 'text':
        // For text content, show a sample/portion
        echo '<div class="text-content mb-4">';
        // Simple HTML sanitization
        $text = $content['content_text'];
        $limited_text = substr($text, 0, 1000); // Limit to first 1000 chars
        if (strlen($text) > 1000) {
            $limited_text .= '... <em>(Preview limit reached)</em>';
        }
        echo $limited_text; // Text content may contain HTML
        echo '</div>';
        break;
        
    case 'video':
        echo '<div class="video-content mb-4">';
        
        // Different handling based on source
        if (!empty($content['video_url'])) {
            // URL video (YouTube, Vimeo, etc.)
            $video_url = $content['video_url'];
            
            // Detect video platform
            if (strpos($video_url, 'youtube.com') !== false || strpos($video_url, 'youtu.be') !== false) {
                // YouTube
                $youtube_id = '';
                
                // Parse YouTube ID from URL
                if (preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $video_url, $matches)) {
                    $youtube_id = $matches[1];
                }
                
                if ($youtube_id) {
                    echo '<div class="ratio ratio-16x9 mb-3">';
                    echo '<iframe id="youtube-player" src="https://www.youtube.com/embed/' . $youtube_id . '?rel=0&controls=1" 
                          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                          allowfullscreen></iframe>';
                    echo '</div>';
                } else {
                    echo '<div class="alert alert-danger">Invalid YouTube URL</div>';
                }
            } 
            elseif (strpos($video_url, 'vimeo.com') !== false) {
                // Vimeo
                $vimeo_id = '';
                
                // Parse Vimeo ID from URL
                if (preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/', $video_url, $matches)) {
                    $vimeo_id = $matches[1];
                }
                
                if ($vimeo_id) {
                    echo '<div class="ratio ratio-16x9 mb-3">';
                    echo '<iframe id="vimeo-player" src="https://player.vimeo.com/video/' . $vimeo_id . '?title=0&byline=0&portrait=0" 
                          allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
                    echo '</div>';
                } else {
                    echo '<div class="alert alert-danger">Invalid Vimeo URL</div>';
                }
            } 
            else {
                // Other URL formats
                echo '<div class="alert alert-info">';
                echo '<p>Video URL: <a href="' . htmlspecialchars($video_url) . '" target="_blank">' . 
                      htmlspecialchars($video_url) . '</a></p>';
                echo '<p>Enroll to access this video content.</p>';
                echo '</div>';
            }
        } 
        elseif (!empty($content['video_file'])) {
            // Uploaded video file
            $video_path = '../uploads/videos/' . $content['video_file'];
            
            echo '<div class="mb-3">';
            echo '<video controls class="w-100" style="max-height: 500px;" preload="metadata">';
            echo '<source src="' . htmlspecialchars($video_path) . '" type="video/mp4">';
            echo 'Your browser does not support HTML5 video.';
            echo '</video>';
            echo '</div>';
            
            echo '<div class="alert alert-info">';
            echo '<p>This is a preview of the uploaded video content.</p>';
            echo '</div>';
        } 
        else {
            echo '<div class="alert alert-warning">No video source available</div>';
        }
        
        // Add description if available
        if (!empty($content['description'])) {
            echo '<div class="card mt-3">';
            echo '<div class="card-header">Description</div>';
            echo '<div class="card-body">';
            echo '<p>' . htmlspecialchars($content['description']) . '</p>';
            echo '</div>';
            echo '</div>';
        }
        
        echo '</div>'; // End video-content
        break;
        
    case 'document':
        echo '<div class="document-preview mb-4">';
        echo '<div class="alert alert-info">';
        echo '<h5>Document Content</h5>';
        echo '<p>This lesson includes a document: ' . htmlspecialchars(basename($content['file_path'])) . '</p>';
        echo '<p>Enroll in the course to download this document.</p>';
        echo '</div>';
        
        // Add description if available
        if (!empty($content['description'])) {
            echo '<div class="card mt-3">';
            echo '<div class="card-header">Description</div>';
            echo '<div class="card-body">';
            echo '<p>' . htmlspecialchars($content['description']) . '</p>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
        break;
        
    case 'link':
        echo '<div class="link-preview mb-4">';
        echo '<div class="alert alert-info">';
        echo '<h5>External Link</h5>';
        echo '<p>This lesson links to: <a href="' . htmlspecialchars($content['external_url']) . 
              '" target="_blank">' . htmlspecialchars($content['external_url']) . '</a></p>';
        echo '</div>';
        
        // Add description if available
        if (!empty($content['description'])) {
            echo '<div class="card mt-3">';
            echo '<div class="card-header">Description</div>';
            echo '<div class="card-body">';
            echo '<p>' . htmlspecialchars($content['description']) . '</p>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
        break;
        
    default:
        echo '<div class="alert alert-warning">Unknown content type</div>';
}

echo '</div>'; // End preview-content
?>