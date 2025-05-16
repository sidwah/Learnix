<?php
// Path: ajax/preview/load_topic_content.php
require '../../backend/session_start.php';

// Check if user is signed in as instructor
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get topic ID from request
$topic_id = isset($_GET['topic_id']) ? intval($_GET['topic_id']) : 0;

// If no topic ID is provided, return error
if ($topic_id === 0) {
    echo '<div class="alert alert-danger">Invalid topic ID</div>';
    exit;
}

// Include database connection
require_once '../../backend/config.php';

// Fetch topic details
$topic_query = "SELECT * FROM section_topics WHERE topic_id = ?";
$stmt = $conn->prepare($topic_query);
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$topic = $stmt->get_result()->fetch_assoc();
$stmt->close();

// If topic not found, return error
if (!$topic) {
    echo '<div class="alert alert-danger">Topic not found</div>';
    exit;
}

// Fetch section and course info
$section_query = "SELECT cs.section_id, cs.course_id 
                 FROM course_sections cs 
                 WHERE cs.section_id = ? AND cs.deleted_at IS NULL";
$stmt = $conn->prepare($section_query);
$stmt->bind_param("i", $topic['section_id']);
$stmt->execute();
$section_info = $stmt->get_result()->fetch_assoc();
$stmt->close();

// If section not found, return error
if (!$section_info) {
    echo '<div class="alert alert-danger">Section not found</div>';
    exit;
}

// Verify instructor has access to this course using the course_instructors junction table
$instructor_access_query = "SELECT ci.course_id
                           FROM course_instructors ci
                           WHERE ci.course_id = ? 
                           AND ci.instructor_id = ?
                           AND ci.deleted_at IS NULL";
$stmt = $conn->prepare($instructor_access_query);
$stmt->bind_param("ii", $section_info['course_id'], $_SESSION['instructor_id']);
$stmt->execute();
$instructor_has_access = $stmt->get_result()->num_rows > 0;
$stmt->close();

// Verify instructor has access to this course
if (!$instructor_has_access) {
    echo '<div class="alert alert-danger">You do not have permission to view this content</div>';
    exit;
}

// Fetch topic content
$content_query = "SELECT * FROM topic_content WHERE topic_id = ?";
$stmt = $conn->prepare($content_query);
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$content_result = $stmt->get_result();
$content = $content_result->fetch_assoc();
$stmt->close();

// If no content found
if (!$content) {
    echo '<div class="alert alert-warning">';
    echo '<h5 class="alert-heading">No Content Added Yet</h5>';
    echo '<p>This topic does not have any content. As an instructor, you should add content for your students.</p>';
    echo '<a href="course-creator.php?course_id=' . $section_info['course_id'] . '&step=6" class="btn btn-primary btn-sm mt-2">';
    echo '<i class="mdi mdi-plus-circle"></i> Add Content Now';
    echo '</a>';
    echo '</div>';
    exit;
}

// Fetch topic resources
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

// Display content based on type
echo '<div class="topic-content-container">';

// Topic header
echo '<div class="mb-4">';
echo '<h3>' . htmlspecialchars($topic['title']) . '</h3>';
echo '</div>';

// Content display based on type
switch ($content['content_type']) {
    case 'text':
        echo '<div class="text-content">';
        echo $content['content_text']; // Note: Not using htmlspecialchars as this is rich text
        echo '</div>';
        
        // If there are resources, add them after the text content
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
                echo '<a href="../uploads/resources/' . $resource['resource_path'] . '" target="_blank" class="btn btn-sm btn-outline-primary ms-auto">';
                echo '<i class="mdi mdi-download"></i> Download';
                echo '</a>';
                echo '</div>';
                echo '</div>';
            }
            
            echo '</div>';
            echo '</div>';
        }
        break;

    case 'video':
        echo '<div class="video-content mb-4">';
        
        // Get video source information from video_sources table if available
        $video_source = null;
        $video_query = "SELECT vs.provider, vs.source_url, vs.duration_seconds 
                        FROM video_sources vs
                        WHERE vs.content_id = ?";
        $stmt = $conn->prepare($video_query);
        $stmt->bind_param("i", $content['content_id']);
        $stmt->execute();
        $video_result = $stmt->get_result();
        
        if ($video_result->num_rows > 0) {
            $video_source = $video_result->fetch_assoc();
            
            // Auto-detect provider based on URL if not properly set
            if (!empty($video_source['source_url'])) {
                if (strpos($video_source['source_url'], 'youtube.com') !== false || 
                    strpos($video_source['source_url'], 'youtu.be') !== false) {
                    $video_source['provider'] = 'YouTube';
                } elseif (strpos($video_source['source_url'], 'vimeo.com') !== false) {
                    $video_source['provider'] = 'Vimeo';
                }
            }
        } else {
            // If no video source in the table, determine source from content
            if (!empty($content['video_url'])) {
                // Use the video URL from content
                $video_source = [
                    'source_url' => $content['video_url'],
                    'provider' => 'HTML5', // Default to HTML5
                    'duration_seconds' => 0
                ];
                
                // Auto-detect provider based on URL
                if (strpos($content['video_url'], 'youtube.com') !== false || 
                    strpos($content['video_url'], 'youtu.be') !== false) {
                    $video_source['provider'] = 'YouTube';
                } elseif (strpos($content['video_url'], 'vimeo.com') !== false) {
                    $video_source['provider'] = 'Vimeo';
                }
            } 
            elseif (!empty($content['video_file'])) {
                // Use the uploaded video file
                $video_source = [
                    'source_url' => '../uploads/videos/' . $content['video_file'],
                    'provider' => 'Uploaded', // Custom provider for uploaded files
                    'duration_seconds' => 0
                ];
            }
            else {
                // No video source found
                $video_source = [
                    'source_url' => '',
                    'provider' => 'None',
                    'duration_seconds' => 0
                ];
            }
        }
        $stmt->close();
        
        // Helper functions for extracting video IDs
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
        
        function extractVimeoID($url) {
            $pattern = '/(?:vimeo\.com\/(?:video\/|channels\/.*\/|groups\/.*\/videos\/|album\/.*\/video\/|)|\d+)(\d+)(?:$|\/|\?)/i';
            preg_match($pattern, $url, $matches);
            return isset($matches[1]) ? $matches[1] : '';
        }
        
        // Display video based on provider
        if (!empty($video_source['source_url'])) {
            $source_url = $video_source['source_url'];
            
            // Container for video
            echo '<div class="video-container position-relative">';
            
            // Video embedding based on provider
            if ($video_source['provider'] == 'YouTube') {
                $video_id = extractYoutubeID($source_url);
                
                if ($video_id) {
                    // YouTube embed
                    echo '<div class="ratio ratio-16x9 mb-3">';
                    echo '<iframe src="https://www.youtube.com/embed/' . $video_id . '?rel=0" 
                          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                          allowfullscreen class="youtube-player"></iframe>';
                    echo '</div>';
                    
                    // Add direct links below
                    echo '<div class="d-flex justify-content-center mb-3">';
                    echo '<a href="https://www.youtube.com/watch?v=' . $video_id . '" target="_blank" 
                          class="btn btn-primary btn-sm me-2">';
                    echo '<i class="mdi mdi-youtube"></i> Watch on YouTube';
                    echo '</a>';
                    echo '<a href="' . htmlspecialchars($source_url) . '" target="_blank" 
                          class="btn btn-outline-secondary btn-sm">';
                    echo '<i class="mdi mdi-open-in-new"></i> Open Original URL';
                    echo '</a>';
                    echo '</div>';
                } else {
                    echo '<div class="alert alert-danger">';
                    echo '<h5 class="alert-heading">Invalid YouTube URL</h5>';
                    echo '<p>Could not extract a valid YouTube video ID.</p>';
                    echo '<p>URL: ' . htmlspecialchars($source_url) . '</p>';
                    echo '</div>';
                }
            } elseif ($video_source['provider'] == 'Vimeo') {
                $video_id = extractVimeoID($source_url);
                
                if ($video_id) {
                    // Vimeo embed
                    echo '<div class="ratio ratio-16x9 mb-3">';
                    echo '<iframe src="https://player.vimeo.com/video/' . $video_id . '?title=0&byline=0&portrait=0" 
                          allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
                    echo '</div>';
                    
                    // Add direct links below
                    echo '<div class="d-flex justify-content-center mb-3">';
                    echo '<a href="https://vimeo.com/' . $video_id . '" target="_blank" 
                          class="btn btn-primary btn-sm me-2">';
                    echo '<i class="mdi mdi-vimeo"></i> Watch on Vimeo';
                    echo '</a>';
                    echo '<a href="' . htmlspecialchars($source_url) . '" target="_blank" 
                          class="btn btn-outline-secondary btn-sm">';
                    echo '<i class="mdi mdi-open-in-new"></i> Open Original URL';
                    echo '</a>';
                    echo '</div>';
                } else {
                    echo '<div class="alert alert-danger">';
                    echo '<h5 class="alert-heading">Invalid Vimeo URL</h5>';
                    echo '<p>Could not extract a valid Vimeo video ID.</p>';
                    echo '<p>URL: ' . htmlspecialchars($source_url) . '</p>';
                    echo '</div>';
                }
            } 
            elseif ($video_source['provider'] == 'Uploaded') {
                // Uploaded video file - display using HTML5 video player
                echo '<div class="mb-3">';
                echo '<video controls class="w-100" style="max-height: 500px;">';
                echo '<source src="' . htmlspecialchars($source_url) . '" type="video/mp4">';
                echo 'Your browser does not support HTML5 video.';
                echo '</video>';
                echo '</div>';
                
                echo '<div class="alert alert-info mb-3">';
                echo '<h5 class="alert-heading">Uploaded Video</h5>';
                echo '<p class="mb-0">This is a video file that was uploaded directly to the platform.</p>';
                echo '</div>';
            }
            else {
                // Default HTML5 or other video provider
                echo '<div class="alert alert-info">';
                echo '<h5 class="alert-heading">Video Content</h5>';
                echo '<p>URL: <a href="' . htmlspecialchars($source_url) . '" target="_blank">' . 
                      htmlspecialchars($source_url) . '</a></p>';
                
                // Try HTML5 video player for common formats
                if (preg_match('/\.(mp4|webm|ogg)$/i', $source_url)) {
                    echo '<video controls class="w-100 mb-3" style="max-height: 400px;">';
                    echo '<source src="' . htmlspecialchars($source_url) . '" type="video/' . 
                          pathinfo($source_url, PATHINFO_EXTENSION) . '">';
                    echo 'Your browser does not support HTML5 video.';
                    echo '</video>';
                }
                
                echo '<p class="mb-0">This video will be available to enrolled students.</p>';
                echo '</div>';
            }
            
            echo '</div>'; // End video-container
        } else {
            echo '<div class="alert alert-warning">';
            echo '<h5 class="alert-heading">No Video Source</h5>';
            echo '<p>No video source is associated with this content. Please add a video URL or upload a video file.</p>';
            echo '</div>';
        }
        
        // Add tabbed interface for Description and Resources
        echo '<div class="card mt-4">';
        echo '<div class="card-header bg-light">';
        echo '<ul class="nav nav-tabs card-header-tabs" id="contentTabs" role="tablist">';
        
        // Description tab
        echo '<li class="nav-item" role="presentation">';
        echo '<button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab" aria-controls="description" aria-selected="true">';
        echo '<i class="mdi mdi-text-box-outline me-1"></i> Description';
        echo '</button>';
        echo '</li>';
        
        // Resources tab
        echo '<li class="nav-item" role="presentation">';
        echo '<button class="nav-link" id="resources-tab" data-bs-toggle="tab" data-bs-target="#resources" type="button" role="tab" aria-controls="resources" aria-selected="false">';
        echo '<i class="mdi mdi-file-document-outline me-1"></i> Resources';
        echo '</button>';
        echo '</li>';
        
        echo '</ul>';
        echo '</div>';
        
        echo '<div class="card-body">';
        echo '<div class="tab-content" id="contentTabsContent">';
        
        // Description tab content
        echo '<div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">';
        if (!empty($content['description'])) {
            echo '<h5 class="mb-3">Description</h5>';
            echo '<p>' . htmlspecialchars($content['description']) . '</p>';
        } else {
            echo '<p class="text-muted">No description available for this video.</p>';
        }
        echo '</div>';
        
        // Resources tab content
        echo '<div class="tab-pane fade" id="resources" role="tabpanel" aria-labelledby="resources-tab">';
        if (!empty($resources)) {
            echo '<h5 class="mb-3">Additional Resources</h5>';
            echo '<div class="list-group">';
            
            foreach ($resources as $resource) {
                echo '<div class="list-group-item list-group-item-action resource-item">';
                echo '<div class="d-flex align-items-center">';
                echo '<i class="mdi mdi-file me-3 text-primary"></i>';
                echo '<div>';
                echo '<h6 class="mb-0">' . htmlspecialchars(basename($resource['resource_path'])) . '</h6>';
                echo '<small class="text-muted">Resource file</small>';
                echo '</div>';
                echo '<a href="../uploads/resources/' . $resource['resource_path'] . '" target="_blank" class="btn btn-sm btn-outline-primary ms-auto">';
                echo '<i class="mdi mdi-download"></i> Download';
                echo '</a>';
                echo '</div>';
                echo '</div>';
            }
            
            echo '</div>';
        } else {
            echo '<p class="text-muted">No additional resources available for this video.</p>';
        }
        echo '</div>';
        
        echo '</div>'; // End tab-content
        echo '</div>'; // End card-body
        echo '</div>'; // End card for tabs
        
        echo '</div>'; // End video-content
        break;
            
    case 'document':
        echo '<div class="document-content">';
        echo '<div class="alert alert-info">';
        echo '<h5 class="alert-heading">Document Preview</h5>';
        echo '<p>Students will be able to download the document: ' . htmlspecialchars($content['file_path']) . '</p>';
        echo '<a href="../uploads/documents/' . $content['file_path'] . '" target="_blank" class="btn btn-primary btn-sm">';
        echo '<i class="mdi mdi-download"></i> Download Document';
        echo '</a>';
        echo '</div>';
        
        // Add tabbed interface similar to video
        echo '<div class="card mt-4">';
        echo '<div class="card-header bg-light">';
        echo '<ul class="nav nav-tabs card-header-tabs" id="docContentTabs" role="tablist">';
        
        // Description tab
        echo '<li class="nav-item" role="presentation">';
        echo '<button class="nav-link active" id="doc-description-tab" data-bs-toggle="tab" data-bs-target="#doc-description" type="button" role="tab" aria-controls="doc-description" aria-selected="true">';
        echo '<i class="mdi mdi-text-box-outline me-1"></i> Description';
        echo '</button>';
        echo '</li>';
        
        // Resources tab
        echo '<li class="nav-item" role="presentation">';
        echo '<button class="nav-link" id="doc-resources-tab" data-bs-toggle="tab" data-bs-target="#doc-resources" type="button" role="tab" aria-controls="doc-resources" aria-selected="false">';
        echo '<i class="mdi mdi-file-document-outline me-1"></i> Resources';
        echo '</button>';
        echo '</li>';
        
        echo '</ul>';
        echo '</div>';
        
        echo '<div class="card-body">';
        echo '<div class="tab-content" id="docContentTabsContent">';
        
        // Description tab content
        echo '<div class="tab-pane fade show active" id="doc-description" role="tabpanel" aria-labelledby="doc-description-tab">';
        if (!empty($content['description'])) {
            echo '<h5 class="mb-3">Description</h5>';
            echo '<p>' . htmlspecialchars($content['description']) . '</p>';
        } else {
            echo '<p class="text-muted">No description available for this document.</p>';
        }
        echo '</div>';
        
        // Resources tab content
        echo '<div class="tab-pane fade" id="doc-resources" role="tabpanel" aria-labelledby="doc-resources-tab">';
        if (!empty($resources)) {
            echo '<h5 class="mb-3">Additional Resources</h5>';
            echo '<div class="list-group">';
            
            foreach ($resources as $resource) {
                echo '<div class="list-group-item list-group-item-action resource-item">';
                echo '<div class="d-flex align-items-center">';
                echo '<i class="mdi mdi-file me-3 text-primary"></i>';
                echo '<div>';
                echo '<h6 class="mb-0">' . htmlspecialchars(basename($resource['resource_path'])) . '</h6>';
                echo '<small class="text-muted">Resource file</small>';
                echo '</div>';
                echo '<a href="../uploads/resources/' . $resource['resource_path'] . '" target="_blank" class="btn btn-sm btn-outline-primary ms-auto">';
                echo '<i class="mdi mdi-download"></i> Download';
                echo '</a>';
                echo '</div>';
                echo '</div>';
            }
            
            echo '</div>';
        } else {
            echo '<p class="text-muted">No additional resources available for this document.</p>';
        }
        echo '</div>';
        
        echo '</div>'; // End tab-content
        echo '</div>'; // End card-body
        echo '</div>'; // End card for tabs
        
        echo '</div>'; // End document-content
        break;

    case 'link':
        echo '<div class="link-content">';
        echo '<div class="alert alert-info">';
        echo '<h5 class="alert-heading">External Resource</h5>';
        echo '<p>External URL: <a href="' . htmlspecialchars($content['external_url']) . '" target="_blank">';
        echo htmlspecialchars($content['external_url']) . '</a></p>';
        echo '</div>';
        
        // Add tabbed interface similar to video and document
        echo '<div class="card mt-4">';
        echo '<div class="card-header bg-light">';
        echo '<ul class="nav nav-tabs card-header-tabs" id="linkContentTabs" role="tablist">';
        
        // Description tab
        echo '<li class="nav-item" role="presentation">';
        echo '<button class="nav-link active" id="link-description-tab" data-bs-toggle="tab" data-bs-target="#link-description" type="button" role="tab" aria-controls="link-description" aria-selected="true">';
        echo '<i class="mdi mdi-text-box-outline me-1"></i> Description';
        echo '</button>';
        echo '</li>';
        
        // Resources tab
        echo '<li class="nav-item" role="presentation">';
        echo '<button class="nav-link" id="link-resources-tab" data-bs-toggle="tab" data-bs-target="#link-resources" type="button" role="tab" aria-controls="link-resources" aria-selected="false">';
        echo '<i class="mdi mdi-file-document-outline me-1"></i> Resources';
        echo '</button>';
        echo '</li>';
        
        echo '</ul>';
        echo '</div>';
        
        echo '<div class="card-body">';
        echo '<div class="tab-content" id="linkContentTabsContent">';
        
        // Description tab content
        echo '<div class="tab-pane fade show active" id="link-description" role="tabpanel" aria-labelledby="link-description-tab">';
        if (!empty($content['description'])) {
            echo '<h5 class="mb-3">Description</h5>';
            echo '<p>' . htmlspecialchars($content['description']) . '</p>';
        } else {
            echo '<p class="text-muted">No description available for this link.</p>';
        }
        echo '</div>';
        
        // Resources tab content
        echo '<div class="tab-pane fade" id="link-resources" role="tabpanel" aria-labelledby="link-resources-tab">';
        if (!empty($resources)) {
            echo '<h5 class="mb-3">Additional Resources</h5>';
            echo '<div class="list-group">';
            
            foreach ($resources as $resource) {
                echo '<div class="list-group-item list-group-item-action resource-item">';
                echo '<div class="d-flex align-items-center">';
                echo '<i class="mdi mdi-file me-3 text-primary"></i>';
                echo '<div>';
                echo '<h6 class="mb-0">' . htmlspecialchars(basename($resource['resource_path'])) . '</h6>';
                echo '<small class="text-muted">Resource file</small>';
                echo '</div>';
                echo '<a href="../uploads/resources/' . $resource['resource_path'] . '" target="_blank" class="btn btn-sm btn-outline-primary ms-auto">';
                echo '<i class="mdi mdi-download"></i> Download';
                echo '</a>';
                echo '</div>';
                echo '</div>';
            }
            
            echo '</div>';
        } else {
            echo '<p class="text-muted">No additional resources available for this link.</p>';
        }
        echo '</div>';
        
        echo '</div>'; // End tab-content
        echo '</div>'; // End card-body
        echo '</div>'; // End card for tabs
        
        echo '</div>'; // End link-content
        break;

    default:
        echo '<div class="alert alert-warning">Unknown content type</div>';
}

echo '</div>'; // End topic-content-container

// Add some custom CSS to enhance the tab styling
echo '<style>
    .nav-tabs .nav-link {
        color: #495057;
        background-color: transparent;
        border-color: transparent;
    }
    
    .nav-tabs .nav-link.active {
        color: #3e7bfa;
        background-color: #fff;
        border-color: #dee2e6 #dee2e6 #fff;
        border-bottom: 2px solid #3e7bfa;
    }
    
    .resource-item {
        transition: all 0.2s ease;
    }
    
    .resource-item:hover {
        background-color: #f8f9fa;
    }
</style>';
?>