<?php
// get-preview-content.php
session_start();
require_once '../backend/config.php';

// Validate inputs
if (
    !isset($_GET['course_id']) || !isset($_GET['topic_id']) ||
    !is_numeric($_GET['course_id']) || !is_numeric($_GET['topic_id'])
) {
    echo '<p class="text-danger">Invalid request</p>';
    exit;
}

$course_id = intval($_GET['course_id']);
$topic_id = intval($_GET['topic_id']);

// Verify that this topic is allowed for preview
$sql = "SELECT st.*, tc.*, c.title as course_title 
        FROM section_topics st
        JOIN topic_content tc ON st.topic_id = tc.topic_id
        JOIN course_sections cs ON st.section_id = cs.section_id
        JOIN courses c ON cs.course_id = c.course_id
        WHERE st.topic_id = ? AND c.course_id = ? AND st.is_previewable = 1";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo '<p class="text-danger">Error preparing the SQL statement</p>';
    error_log("Error preparing the SQL statement: " . $conn->error);
    exit;
}

$stmt->bind_param("ii", $topic_id, $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    echo '<p class="text-danger">Error executing the query</p>';
    error_log("Error executing the query: " . $stmt->error);
    exit;
}

if ($result->num_rows === 0) {
    echo '<p class="text-danger">This content is not available for preview</p>';
    exit;
}

$topic = $result->fetch_assoc();

// Display the preview content based on content type
echo '<h4 class="mb-3">' . htmlspecialchars($topic['title']) . '</h4>';

switch ($topic['content_type']) {
    case 'video':
        // Show video preview
        if (!empty($topic['video_url'])) {
            echo '<div class="ratio ratio-16x9 mb-4">';
            
            $video_url = $topic['video_url'];
    
            // Vimeo embed
            if (preg_match('/vimeo\.com\/(\d+)/', $video_url, $matches)) {
                $embed_url = "https://player.vimeo.com/video/{$matches[1]}?title=0&byline=0&portrait=0&transparent=0";
                echo '<iframe id="vimeo-player" src="' . htmlspecialchars($embed_url) . '" allow="autoplay; fullscreen" allowfullscreen></iframe>';
    
            // YouTube embed
            } elseif (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w\-]+)/', $video_url, $matches)) {
                $embed_url = "https://www.youtube-nocookie.com/embed/{$matches[1]}?enablejsapi=1&modestbranding=1&rel=0&controls=0";
                echo '<iframe id="youtube-player" src="' . htmlspecialchars($embed_url) . '" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
    
            } else {
                echo '<p class="text-danger">Unsupported video platform.</p>';
            }
    
            echo '</div>';
        } else {
            echo '<p class="text-center"><i class="bi bi-play-circle display-4"></i></p>';
            echo '<p class="text-center">Video preview available after enrollment</p>';
        }
        break;
    

    case 'text':
        // Show first 300 characters of text content
        $preview_text = substr($topic['content_text'], 0, 300);
        echo '<div class="preview-text mb-4">';
        echo '<p>' . nl2br(htmlspecialchars($preview_text)) . '...</p>';
        echo '<p class="text-muted">Continue reading after enrollment</p>';
        echo '</div>';
        break;

    case 'link':
        echo '<div class="text-center mb-4">';
        echo '<p><i class="bi bi-link-45deg display-4"></i></p>';
        echo '<p>External resource link available after enrollment</p>';
        echo '</div>';
        break;

    case 'document':
        echo '<div class="text-center mb-4">';
        echo '<p><i class="bi bi-file-earmark-text display-4"></i></p>';
        echo '<p>Document preview available after enrollment</p>';
        echo '</div>';
        break;

    default:
        echo '<p class="text-center">Preview not available for this content type</p>';
}

// Show a description if available
if (!empty($topic['description'])) {
    echo '<div class="mt-4">';
    echo '<h5>Description</h5>';
    echo '<p>' . nl2br(htmlspecialchars($topic['description'])) . '</p>';
    echo '</div>';
}

// Close database connection
$stmt->close();
$conn->close();
