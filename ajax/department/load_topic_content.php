<?php
// Path: ajax/department/load_topic_content.php
require '../../backend/session_start.php';

// Check if user is signed in as department head
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get parameters
$topic_id = isset($_GET['topic_id']) ? intval($_GET['topic_id']) : 0;
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// If no topic ID or course ID is provided, return error
if ($topic_id === 0 || $course_id === 0) {
    echo '<div class="alert alert-danger">Missing parameters</div>';
    exit;
}

// Include database connection
require_once '../../backend/config.php';

// Get user's department
$dept_query = "SELECT ds.department_id 
               FROM department_staff ds 
               WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' AND ds.deleted_at IS NULL
               LIMIT 1";
$dept_stmt = $conn->prepare($dept_query);
$dept_stmt->bind_param("i", $_SESSION['user_id']);
$dept_stmt->execute();
$dept_result = $dept_stmt->get_result();

if ($dept_result->num_rows === 0) {
    echo '<div class="alert alert-danger">Department access error</div>';
    exit;
}

$department = $dept_result->fetch_assoc();
$department_id = $department['department_id'];

// Check if the course belongs to the department head's department
$course_check_query = "SELECT course_id FROM courses WHERE course_id = ? AND department_id = ? AND deleted_at IS NULL";
$course_check_stmt = $conn->prepare($course_check_query);
$course_check_stmt->bind_param("ii", $course_id, $department_id);
$course_check_stmt->execute();
$course_access = ($course_check_stmt->get_result()->num_rows > 0);
$course_check_stmt->close();

if (!$course_access) {
    echo '<div class="alert alert-danger">You do not have permission to view this course</div>';
    exit;
}

// Verify the topic belongs to the course
$topic_query = "SELECT st.topic_id, st.title, st.section_id, cs.course_id  
               FROM section_topics st
               JOIN course_sections cs ON st.section_id = cs.section_id
               WHERE st.topic_id = ? AND cs.course_id = ?";
$stmt = $conn->prepare($topic_query);
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
echo '<div class="review-content">';

// Topic header with review flag
echo '<div class="mb-4 d-flex justify-content-between align-items-center">';
echo '<h3>' . htmlspecialchars($topic['title']) . '</h3>';
echo '<span class="badge bg-primary">Under Review</span>';
echo '</div>';

// Content display based on type
switch ($content['content_type']) {
    case 'text':
        echo '<div class="text-content mb-4">';
        echo $content['content_text']; // Text content may contain HTML
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
                echo '</div>';
            }
        } 
        elseif (!empty($content['video_file'])) {
            // Uploaded video file
            $video_path = '../../uploads/videos/' . $content['video_file'];
            
            echo '<div class="mb-3">';
            echo '<video controls class="w-100" style="max-height: 500px;" preload="metadata">';
            echo '<source src="' . htmlspecialchars($video_path) . '" type="video/mp4">';
            echo 'Your browser does not support HTML5 video.';
            echo '</video>';
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
        echo '<a href="../../uploads/documents/' . $content['file_path'] . '" class="btn btn-primary btn-sm" target="_blank">';
        echo '<i class="mdi mdi-download me-1"></i> Download Document</a>';
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

// Fetch and display any resources
$resources_query = "SELECT * FROM topic_resources WHERE topic_id = ?";
$stmt = $conn->prepare($resources_query);
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$resources_result = $stmt->get_result();
$resources = [];
while ($resource = $resources_result->fetch_assoc()) {
    $resources[] = $resource;
}
$stmt->close();

// Display resources if any
if (!empty($resources)) {
    echo '<div class="resources-section mt-4">';
    echo '<h5>Additional Resources</h5>';
    echo '<div class="list-group">';
    
    foreach ($resources as $resource) {
        echo '<div class="list-group-item list-group-item-action resource-item">';
        echo '<div class="d-flex align-items-center">';
        echo '<i class="mdi mdi-file me-3 text-primary"></i>';
        echo '<div>';
        echo '<h6 class="mb-0">' . htmlspecialchars(basename($resource['resource_path'])) . '</h6>';
        echo '<small class="text-muted">Resource file</small>';
        echo '</div>';
        echo '<a href="../../uploads/resources/' . $resource['resource_path'] . '" target="_blank" class="btn btn-sm btn-outline-primary ms-auto">';
        echo '<i class="mdi mdi-download"></i> Download';
        echo '</a>';
        echo '</div>';
        echo '</div>';
    }
    
    echo '</div>'; // End list-group
    echo '</div>'; // End resources-section
}

echo '</div>'; // End review-content
?>