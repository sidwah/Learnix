<?php
//ajax/curriculum/load_content_editor.php
require '../../backend/session_start.php';
require '../../backend/config.php';

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo '<div class="alert alert-danger">Unauthorized access</div>';
    exit;
}

// Validate required input
if (!isset($_GET['topic_id'])) {
    echo '<div class="alert alert-danger">Missing topic ID</div>';
    exit;
}

$topic_id = intval($_GET['topic_id']);

// Verify that the topic belongs to a section of a course owned by the current instructor
$stmt = $conn->prepare("
    SELECT 
        st.topic_id, 
        st.title, 
        st.section_id, 
        cs.course_id, 
        ci.instructor_id,
        ci.is_primary
    FROM 
        section_topics st
    JOIN 
        course_sections cs ON st.section_id = cs.section_id
    JOIN 
        courses c ON cs.course_id = c.course_id
    LEFT JOIN 
        course_instructors ci ON c.course_id = ci.course_id
    WHERE 
        st.topic_id = ?
");
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$result = $stmt->get_result();
$topic_data = $result->fetch_assoc();
$stmt->close();

if (!$topic_data || $topic_data['instructor_id'] != $_SESSION['instructor_id']) {
    echo '<div class="alert alert-danger">Topic not found or not authorized</div>';
    exit;
}

// Check if topic already has content
$stmt = $conn->prepare("SELECT * FROM topic_content WHERE topic_id = ? LIMIT 1");
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$result = $stmt->get_result();
$content = $result->fetch_assoc();
$stmt->close();

$content_type = $content ? $content['content_type'] : '';
$content_id = $content ? $content['content_id'] : 0;

// Also check if topic has resources
$stmt = $conn->prepare("SELECT * FROM topic_resources WHERE topic_id = ? ORDER BY resource_id ASC");
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$result = $stmt->get_result();
$resources = [];
while ($resource = $result->fetch_assoc()) {
    $resources[] = $resource;
}
$stmt->close();
?>

<div class="content-editor" data-topic-id="<?php echo $topic_id; ?>">
    <div class="content-editor-header mb-4">
        <h4 class="mb-1"><?php echo htmlspecialchars($topic_data['title']); ?></h4>
        <p class="text-muted mb-0">Edit content for this topic</p>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Content Type</h5>
                    <p class="card-text">Select the type of content for this topic</p>

                    <div class="content-type-selector">
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="contentType" id="contentTypeVideo" value="video" autocomplete="off" <?php echo ($content_type === 'video') ? 'checked' : ''; ?>>
                            <label class="btn btn-outline-primary" for="contentTypeVideo">
                                <i class="mdi mdi-play-circle-outline"></i> Video
                            </label>

                            <input type="radio" class="btn-check" name="contentType" id="contentTypeText" value="text" autocomplete="off" <?php echo ($content_type === 'text') ? 'checked' : ''; ?>>
                            <label class="btn btn-outline-primary" for="contentTypeText">
                                <i class="mdi mdi-text-box-outline"></i> Text
                            </label>

                            <input type="radio" class="btn-check" name="contentType" id="contentTypeDocument" value="document" autocomplete="off" <?php echo ($content_type === 'document') ? 'checked' : ''; ?>>
                            <label class="btn btn-outline-primary" for="contentTypeDocument">
                                <i class="mdi mdi-file-document-outline"></i> Document
                            </label>

                            <input type="radio" class="btn-check" name="contentType" id="contentTypeLink" value="link" autocomplete="off" <?php echo ($content_type === 'link') ? 'checked' : ''; ?>>
                            <label class="btn btn-outline-primary" for="contentTypeLink">
                                <i class="mdi mdi-link"></i> Link
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content editor containers -->
    <div class="content-editor-containers">
        <!-- Video content editor -->
        <div class="content-editor-container" id="videoEditorContainer" style="<?php echo ($content_type === 'video') ? '' : 'display: none;'; ?>">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="mdi mdi-play-circle-outline"></i> Video Content</h5>
                </div>
                <div class="card-body">
                    <?php
                    // If we have video content, get the details
                    $video_url = '';
                    $video_title = '';
                    $video_description = '';
                    $video_file = '';
                    $content_source = 'url'; // Default to URL input

                    if ($content_type === 'video' && $content) {
                        $video_title = $content['title'];
                        $video_description = $content['description'] ?? '';

                        // Determine source based on which field has a value
                        if (!empty($content['video_url'])) {
                            $content_source = 'url';
                            $video_url = $content['video_url'];
                        } else if (!empty($content['video_file'])) {
                            $content_source = 'upload';
                            $video_file = $content['video_file'];
                        }
                    }
                    ?>

                    <div class="mb-3">
                        <label for="videoTitle" class="form-label">Video Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="videoTitle" name="video_title" value="<?php echo htmlspecialchars($video_title); ?>" placeholder="Enter a title for this video">
                    </div>

                    <!-- Video Upload/Preview Container -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Video Source</h5>

                            <!-- Content Source Selector -->
                            <div class="btn-group w-100 mb-3" role="group">
                                <input type="radio" class="btn-check" name="contentSource" id="sourceUrl" value="url" <?php echo ($content_source === 'url') ? 'checked' : ''; ?>>
                                <label class="btn btn-outline-primary" for="sourceUrl">YouTube/Vimeo URL</label>

                                <input type="radio" class="btn-check" name="contentSource" id="sourceUpload" value="upload" <?php echo ($content_source === 'upload') ? 'checked' : ''; ?>>
                                <label class="btn btn-outline-primary" for="sourceUpload">Upload Video File</label>
                            </div>

                            <!-- Video Preview Area - Shared between both sources -->
                            <div class="video-preview-area mb-4">
                                <h6 class="border-bottom pb-2 mb-3">Video Preview</h6>

                                <?php if ($content_source === 'url' && !empty($video_url)): ?>
                                    <!-- Embedded player for URL videos -->
                                    <div class="embed-responsive embed-responsive-16by9">
                                        <?php
                                        // Convert YouTube or Vimeo URL to embed format
                                        $embed_url = '';
                                        if (strpos($video_url, 'youtube.com') !== false || strpos($video_url, 'youtu.be') !== false) {
                                            // Extract YouTube ID
                                            preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $video_url, $matches);
                                            $youtube_id = $matches[1] ?? '';
                                            if ($youtube_id) {
                                                $embed_url = "https://www.youtube.com/embed/{$youtube_id}";
                                            }
                                        } elseif (strpos($video_url, 'vimeo.com') !== false) {
                                            // Extract Vimeo ID
                                            preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/', $video_url, $matches);
                                            $vimeo_id = $matches[1] ?? '';
                                            if ($vimeo_id) {
                                                $embed_url = "https://player.vimeo.com/video/{$vimeo_id}";
                                            }
                                        }

                                        if ($embed_url): ?>
                                            <iframe class="embed-responsive-item" src="<?php echo htmlspecialchars($embed_url); ?>" allowfullscreen></iframe>
                                        <?php else: ?>
                                            <div class="alert alert-warning">Invalid video URL format. Please enter a valid YouTube or Vimeo URL.</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted mt-2"><small>Source: YouTube/Vimeo URL </small></div>

                                <?php elseif ($content_source === 'upload' && !empty($video_file)): ?>
                                    <!-- Video player for uploaded videos -->
                                    <video controls class="w-100 rounded">
                                        <source src="../uploads/videos/<?php echo htmlspecialchars($video_file); ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                    <div class="text-muted mt-2"><small>Source: Uploaded file - <?php echo htmlspecialchars($video_file); ?></small></div>

                                <?php else: ?>
                                    <!-- Placeholder when no video is present -->
                                    <div class="placeholder-video">
                                        <i class="mdi mdi-video-outline display-4"></i>
                                        <p class="mt-2">No video content yet</p>
                                        <p class="small text-muted">Select your preferred method and add video content below</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- URL Input Container -->
                            <div id="urlContainer" class="content-input <?php echo ($content_source === 'upload') ? 'd-none' : ''; ?>">
                                <h6 class="border-bottom pb-2 mb-3">Enter Video URL</h6>

                                <?php if ($content_source === 'upload' && !empty($video_file)): ?>
                                    <!-- Warning shown when switching to URL but already have an uploaded file -->
                                    <div class="alert alert-warning mb-3" id="urlReplacementWarning">
                                        <i class="mdi mdi-alert-circle-outline"></i> You currently have an uploaded video file.
                                        Adding a URL video will replace your current video content.
                                    </div>
                                <?php endif; ?>

                                <div class="input-group mb-2">
                                    <input type="url" class="form-control" id="videoUrl" name="video_url"
                                        value="<?php echo ($content_source === 'url') ? htmlspecialchars($video_url) : ''; ?>"
                                        placeholder="Enter YouTube or Vimeo URL">
                                    <button class="btn btn-outline-primary" type="button" id="previewUrlBtn">Preview</button>
                                </div>
                                <small class="form-text text-muted mb-3 d-block">
                                    Paste a YouTube or Vimeo URL (e.g., https://www.youtube.com/watch?v=12345)
                                </small>
                            </div>

                            <!-- File Upload Container -->
                            <div id="uploadContainer" class="content-input <?php echo ($content_source === 'url') ? 'd-none' : ''; ?>">
                                <h6 class="border-bottom pb-2 mb-3">Upload Video File</h6>

                                <?php if ($content_source === 'url' && !empty($video_url)): ?>
                                    <!-- Warning shown when switching to Upload but already have a URL video -->
                                    <div class="alert alert-warning mb-3" id="uploadReplacementWarning">
                                        <i class="mdi mdi-alert-circle-outline"></i> You currently have a YouTube/Vimeo video.
                                        Uploading a new video file will replace your current video content.
                                    </div>
                                <?php endif; ?>

                                <input type="file" id="videoFile" name="video_file" class="d-none" accept="video/*">
                                <div class="mb-3">
                                    <button type="button" id="videoFileBtn" class="btn btn-outline-primary">
                                        <i class="mdi mdi-upload"></i> Select Video File
                                    </button>
                                    <div id="selectedFileName" class="mt-2"></div>
                                    <small class="form-text text-muted d-block mt-2">
                                        Accepted formats: MP4, WebM, MOV (Max size: 100MB)
                                    </small>
                                </div>

                                <!-- Upload Progress -->
                                <div class="progress mt-3 d-none" id="uploadProgress">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">0%</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="videoDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="videoDescription" name="video_description" rows="3" placeholder="Enter a description for this video"><?php echo htmlspecialchars($video_description); ?></textarea>
                    </div>

                    <button type="button" class="btn btn-primary save-video-btn">
                        <i class="mdi mdi-content-save"></i> Save Video Content
                    </button>
                    <div id="saveVideoFeedback" class="mt-2"></div>
                </div>
            </div>

            <!-- Confirmation Modal -->
            <div class="modal fade" id="replaceConfirmModal" tabindex="-1" aria-labelledby="replaceConfirmModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="replaceConfirmModalLabel">Replace Existing Video?</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>You already have a video from another source. Do you want to replace it with this new video?</p>
                            <p>This action cannot be undone.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" id="confirmReplaceBtn">Replace Video</button>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                .placeholder-video {
                    background-color: #f8f9fa;
                    border: 2px dashed #dee2e6;
                    border-radius: 0.25rem;
                    padding: 3rem 1rem;
                    text-align: center;
                    color: #6c757d;
                }

                .video-preview-area {
                    min-height: 250px;
                }

                .embed-responsive {
                    position: relative;
                    display: block;
                    width: 100%;
                    padding: 0;
                    overflow: hidden;
                }

                .embed-responsive::before {
                    display: block;
                    content: "";
                    padding-top: 56.25%;
                    /* 16:9 Aspect Ratio */
                }

                .embed-responsive-item {
                    position: absolute;
                    top: 0;
                    bottom: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    border: 0;
                }
            </style>

            <script>
                // Store video data for both sources
                const videoData = {
                    url: {
                        exists: <?php echo (!empty($video_url) && $content_source === 'url') ? 'true' : 'false'; ?>,
                        value: '<?php echo htmlspecialchars($video_url); ?>'
                    },
                    upload: {
                        exists: <?php echo (!empty($video_file) && $content_source === 'upload') ? 'true' : 'false'; ?>,
                        file: null,
                        filename: '<?php echo htmlspecialchars($video_file); ?>'
                    }
                };

                // Variables to keep track of pending actions that require confirmation
                let pendingAction = null;
                let pendingFile = null;
                let pendingUrl = null;


                // Toggle between URL and Upload options
                document.querySelectorAll('input[name="contentSource"]').forEach(radio => {
                    radio.addEventListener('change', function() {
                        // Hide all input containers
                        document.querySelectorAll('.content-input').forEach(container => {
                            container.classList.add('d-none');
                        });

                        // Show the selected container
                        if (this.value === 'url') {
                            document.getElementById('urlContainer').classList.remove('d-none');
                        } else {
                            document.getElementById('uploadContainer').classList.remove('d-none');
                        }
                    });
                });

                // Select video file button handler
                document.getElementById('videoFileBtn').addEventListener('click', function() {
                    document.getElementById('videoFile').click();
                });

                // Handle file selection with replacement check
                document.getElementById('videoFile').addEventListener('change', function() {
                    const file = this.files[0];
                    if (!file) return;

                    // Check if we already have a URL video
                    if (videoData.url.exists) {
                        // Store the pending file and show confirmation modal
                        pendingAction = 'upload';
                        pendingFile = file;
                        const replaceModal = new bootstrap.Modal(document.getElementById('replaceConfirmModal'));
                        replaceModal.show();
                    } else {
                        // Process the file directly if no conflict
                        processVideoFile(file);
                    }
                });

                // Process video file after selection (or confirmation)
                function processVideoFile(file) {
                    const fileNameElement = document.getElementById('selectedFileName');
                    fileNameElement.textContent = file.name;
                    fileNameElement.classList.add('text-success');

                    videoData.upload.exists = true;
                    videoData.upload.file = file;

                    // Clear URL data since we're replacing it
                    videoData.url.exists = false;
                    videoData.url.value = '';
                    document.getElementById('videoUrl').value = '';

                    // Update preview
                    updateVideoPreview('upload', file);
                }

                // Preview URL button handler with replacement check
                document.getElementById('previewUrlBtn').addEventListener('click', function() {
                    const videoUrl = document.getElementById('videoUrl').value.trim();
                    if (!videoUrl) {
                        alert('Please enter a video URL');
                        return;
                    }

                    // Process URL to check if it's valid before showing confirmation
                    const urlData = processVideoUrl(videoUrl);
                    if (!urlData.valid) {
                        alert('Invalid video URL format. Please enter a valid YouTube or Vimeo URL.');
                        return;
                    }

                    // Check if we already have an uploaded video
                    if (videoData.upload.exists) {
                        // Store the pending URL and show confirmation modal
                        pendingAction = 'url';
                        pendingUrl = videoUrl;
                        const replaceModal = new bootstrap.Modal(document.getElementById('replaceConfirmModal'));
                        replaceModal.show();
                    } else {
                        // Process the URL directly if no conflict
                        processVideoUrl(videoUrl, true);
                    }
                });

                // Process video URL after entry (or confirmation)
                function processVideoUrl(videoUrl, updatePreview = false) {
                    // Extract video ID and check if valid
                    let embedUrl = '';
                    let videoId = '';
                    let isValid = false;
                    let platform = '';

                    // YouTube URL pattern matching
                    const youtubeRegex = /(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/;
                    const youtubeMatch = videoUrl.match(youtubeRegex);

                    // Vimeo URL pattern matching
                    const vimeoRegex = /vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/;
                    const vimeoMatch = videoUrl.match(vimeoRegex);

                    if (youtubeMatch && youtubeMatch[1]) {
                        videoId = youtubeMatch[1];
                        embedUrl = `https://www.youtube.com/embed/${videoId}`;
                        isValid = true;
                        platform = 'YouTube';
                    } else if (vimeoMatch && vimeoMatch[1]) {
                        videoId = vimeoMatch[1];
                        embedUrl = `https://player.vimeo.com/video/${videoId}`;
                        isValid = true;
                        platform = 'Vimeo';
                    }

                    if (updatePreview && isValid) {
                        videoData.url.exists = true;
                        videoData.url.value = videoUrl;

                        // Clear upload data since we're replacing it
                        videoData.upload.exists = false;
                        videoData.upload.file = null;
                        videoData.upload.filename = '';
                        document.getElementById('selectedFileName').textContent = '';

                        // Update preview
                        updateVideoPreview('url', embedUrl);
                    }

                    return {
                        valid: isValid,
                        platform: platform,
                        embedUrl: embedUrl
                    };
                }

                // Update video preview based on source type
                function updateVideoPreview(sourceType, content) {
                    const previewArea = document.querySelector('.video-preview-area');

                    if (sourceType === 'url') {
                        // content should be the embed URL
                        previewArea.innerHTML = `
                <h6 class="border-bottom pb-2 mb-3">Video Preview</h6>
                <div class="embed-responsive embed-responsive-16by9">
                    <iframe class="embed-responsive-item" src="${content}" allowfullscreen></iframe>
                </div>
                <div class="text-muted mt-2"><small>Source: YouTube/Vimeo URL</small></div>
            `;
                    } else if (sourceType === 'upload') {
                        if (typeof content === 'object' && content instanceof File) {
                            // Case for new file selection (File object)
                            previewArea.innerHTML = `
                    <h6 class="border-bottom pb-2 mb-3">Video Preview</h6>
                    <video controls class="w-100 rounded">
                        <source src="${URL.createObjectURL(content)}" type="${content.type}">
                        Your browser does not support the video tag.
                    </video>
                    <div class="text-muted mt-2"><small>Source: Uploaded file - ${content.name}</small></div>
                `;
                        } else if (typeof content === 'string') {
                            // Case for existing uploaded file (filename string)
                            previewArea.innerHTML = `
                    <h6 class="border-bottom pb-2 mb-3">Video Preview</h6>
                    <video controls class="w-100 rounded">
                        <source src="../../uploads/videos/${content}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    <div class="text-muted mt-2"><small>Source: Uploaded file - ${content}</small></div>
                `;
                        }
                    } else {
                        // No content, show placeholder
                        previewArea.innerHTML = `
                <h6 class="border-bottom pb-2 mb-3">Video Preview</h6>
                <div class="placeholder-video">
                    <i class="mdi mdi-video-outline display-4"></i>
                    <p class="mt-2">No video content yet</p>
                    <p class="small text-muted">Select your preferred method and add video content below</p>
                </div>
            `;
                    }
                }

                // Confirmation button handler
                document.getElementById('confirmReplaceBtn').addEventListener('click', function() {
                    // Close the modal
                    const replaceModal = bootstrap.Modal.getInstance(document.getElementById('replaceConfirmModal'));
                    replaceModal.hide();

                    // Process the pending action
                    if (pendingAction === 'upload' && pendingFile) {
                        processVideoFile(pendingFile);
                    } else if (pendingAction === 'url' && pendingUrl) {
                        processVideoUrl(pendingUrl, true);
                    }

                    // Reset pending variables
                    pendingAction = null;
                    pendingFile = null;
                    pendingUrl = null;
                });

                // Save button handler
                document.querySelector('.save-video-btn').addEventListener('click', function() {
                    const contentSource = document.querySelector('input[name="contentSource"]:checked').value;
                    const videoTitle = document.getElementById('videoTitle').value.trim();
                    const videoDescription = document.getElementById('videoDescription').value.trim();
                    const feedbackElement = document.getElementById('saveVideoFeedback');
                    const topicId = document.querySelector('.content-editor').dataset.topicId;
                    // Get content ID if it exists (for updates)
                    const contentId = document.querySelector('.content-editor').dataset.contentId || 0;

                    // Validate title
                    if (!videoTitle) {
                        feedbackElement.innerHTML = '<div class="alert alert-danger">Please enter a video title</div>';
                        return;
                    }

                    if (contentSource === 'url') {
                        if (!videoData.url.exists) {
                            feedbackElement.innerHTML = '<div class="alert alert-danger">Please add and preview a video URL</div>';
                            return;
                        }

                        // Save URL video
                        saveVideoContent({
                            topic_id: topicId,
                            content_id: contentId,
                            source: 'url',
                            title: videoTitle,
                            video_url: videoData.url.value,
                            description: videoDescription
                        });

                    } else {
                        if (!videoData.upload.exists) {
                            feedbackElement.innerHTML = '<div class="alert alert-danger">Please select a video file</div>';
                            return;
                        }

                        if (videoData.upload.file) {
                            // Upload new file
                            uploadVideoFile(videoData.upload.file, videoTitle, videoDescription, topicId, contentId);
                        } else {
                            // Using existing uploaded file
                            saveVideoContent({
                                topic_id: topicId,
                                content_id: contentId,
                                source: 'upload',
                                title: videoTitle,
                                video_file: videoData.upload.filename,
                                description: videoDescription
                            });
                        }
                    }
                });

                function saveVideoContent(data) {
                    const feedbackElement = document.getElementById('saveVideoFeedback');
                    feedbackElement.innerHTML = '<div class="alert alert-info">Saving video content...</div>';

                    // Show loading overlay if available
                    if (typeof showOverlay === 'function') {
                        showOverlay('Saving video content...');
                    }

                    // AJAX call to save content
                    fetch('../ajax/content/save_video.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(data)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                feedbackElement.innerHTML = '<div class="alert alert-success">Video content saved successfully!</div>';

                                // Update content ID for future saves if returned
                                if (data.content_id) {
                                    document.querySelector('.content-editor').dataset.contentId = data.content_id;
                                }
                            } else {
                                feedbackElement.innerHTML = `<div class="alert alert-danger">Error: ${data.message}</div>`;
                            }

                            // Remove overlay if available
                            if (typeof removeOverlay === 'function') {
                                removeOverlay();
                            }
                        })
                        .catch(error => {
                            feedbackElement.innerHTML = '<div class="alert alert-danger">Error saving video content. Please try again.</div>';
                            console.error('Error:', error);

                            // Remove overlay if available
                            if (typeof removeOverlay === 'function') {
                                removeOverlay();
                            }
                        });
                }

                function uploadVideoFile(file, title, description, topicId, contentId) {
                    // Show progress bar
                    const progressBar = document.querySelector('#uploadProgress');
                    const progressBarInner = progressBar.querySelector('.progress-bar');
                    const feedbackElement = document.getElementById('saveVideoFeedback');

                    progressBar.classList.remove('d-none');
                    feedbackElement.innerHTML = '<div class="alert alert-info">Uploading video file...</div>';

                    const formData = new FormData();
                    formData.append('video_file', file);
                    formData.append('title', title);
                    formData.append('description', description);
                    formData.append('topic_id', topicId);

                    // Add content_id if updating existing content
                    if (contentId && contentId !== '0') {
                        formData.append('content_id', contentId);
                    }

                    // AJAX upload with progress tracking
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', '../ajax/content/upload_video.php', true);

                    xhr.upload.onprogress = function(e) {
                        if (e.lengthComputable) {
                            const percentComplete = Math.round((e.loaded / e.total) * 100);
                            progressBarInner.style.width = percentComplete + '%';
                            progressBarInner.textContent = percentComplete + '%';
                        }
                    };

                    xhr.onload = function() {
                        progressBar.classList.add('d-none');

                        if (xhr.status === 200) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    feedbackElement.innerHTML = '<div class="alert alert-success">Video uploaded successfully!</div>';

                                    // Update content ID for future saves
                                    if (response.content_id) {
                                        document.querySelector('.content-editor').dataset.contentId = response.content_id;
                                    }

                                    // Update videoData.upload
                                    videoData.upload.exists = true;
                                    videoData.upload.filename = response.file_path;
                                    videoData.upload.file = null; // Clear file object after successful upload

                                    // Show success message with video name
                                    const fileName = response.file_name || file.name;
                                    feedbackElement.innerHTML = `<div class="alert alert-success">Video '${fileName}' uploaded successfully!</div>`;
                                } else {
                                    feedbackElement.innerHTML = `<div class="alert alert-danger">Error: ${response.message}</div>`;
                                }
                            } catch (e) {
                                feedbackElement.innerHTML = '<div class="alert alert-danger">Error processing server response</div>';
                                console.error('Error parsing response:', e, xhr.responseText);
                            }
                        } else {
                            feedbackElement.innerHTML = `<div class="alert alert-danger">Upload failed with status: ${xhr.status}</div>`;
                        }
                    };

                    xhr.onerror = function() {
                        progressBar.classList.add('d-none');
                        feedbackElement.innerHTML = '<div class="alert alert-danger">Upload failed. Please check your connection and try again.</div>';
                    };

                    xhr.send(formData);
                }
            </script>
        </div>

        <!-- Text content editor -->
        <div class="content-editor-container" id="textEditorContainer" style="<?php echo ($content_type === 'text') ? '' : 'display: none;'; ?>">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="mdi mdi-text-box-outline"></i> Text Content</h5>
                </div>
                <div class="card-body">
                    <?php
                    // If we have text content, get the details
                    $text_title = '';
                    $text_content = '';

                    if ($content_type === 'text' && $content) {
                        $text_title = $content['title'];
                        $text_content = $content['content_text'] ?? '';
                    }
                    ?>

                    <div class="mb-3">
                        <label for="textTitle" class="form-label">Content Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="textTitle" value="<?php echo htmlspecialchars($text_title); ?>" placeholder="Enter a title for this content">
                    </div>

                    <div class="mb-3">
                        <label for="textContent" class="form-label">Content <span class="text-danger">*</span></label>
                        <textarea class="form-control rich-editor" id="textContent" rows="10"><?php echo htmlspecialchars($text_content); ?></textarea>
                    </div>

                    <button type="button" class="btn btn-primary save-text-btn">
                        <i class="mdi mdi-content-save"></i> Save Text Content
                    </button>
                </div>
            </div>
        </div>

        <!-- Document content editor -->
        <div class="content-editor-container" id="documentEditorContainer" style="<?php echo ($content_type === 'document') ? '' : 'display: none;'; ?>">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="mdi mdi-file-document-outline"></i> Document Content</h5>
                </div>
                <div class="card-body">
                    <?php
                    // If we have document content, get the details
                    $document_title = '';
                    $document_description = '';
                    $document_file = '';

                    if ($content_type === 'document' && $content) {
                        $document_title = $content['title'];
                        $document_description = $content['description'] ?? '';
                        $document_file = $content['file_path'] ?? '';
                    }
                    ?>

                    <div class="mb-3">
                        <label for="documentTitle" class="form-label">Document Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="documentTitle" value="<?php echo htmlspecialchars($document_title); ?>" placeholder="Enter a title for this document">
                    </div>

                    <div class="mb-3">
                        <label for="documentDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="documentDescription" rows="3" placeholder="Enter a description for this document"><?php echo htmlspecialchars($document_description); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="documentFile" class="form-label">Upload Document <span class="text-danger">*</span></label>
                        <input class="form-control" type="file" id="documentFile">
                        <small class="form-text text-muted">
                            Allowed file types: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX (max 10MB)
                        </small>
                    </div>

                    <?php if ($document_file): ?>
                        <div class="mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h6>Current Document</h6>
                                    <p class="mb-0">
                                        <i class="mdi mdi-file-document"></i>
                                        <?php echo htmlspecialchars(basename($document_file)); ?>
                                        <a href="../uploads/documents/<?php echo htmlspecialchars($document_file); ?>" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                                            <i class="mdi mdi-eye"></i> View
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <button type="button" class="btn btn-primary save-document-btn">
                        <i class="mdi mdi-content-save"></i> Save Document Content
                    </button>
                </div>
            </div>
        </div>

        <!-- Link content editor -->
        <div class="content-editor-container" id="linkEditorContainer" style="<?php echo ($content_type === 'link') ? '' : 'display: none;'; ?>">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="mdi mdi-link"></i> Link Content</h5>
                </div>
                <div class="card-body">
                    <?php
                    // If we have link content, get the details
                    $link_title = '';
                    $link_description = '';
                    $external_url = '';

                    if ($content_type === 'link' && $content) {
                        $link_title = $content['title'];
                        $link_description = $content['description'] ?? '';
                        $external_url = $content['external_url'] ?? '';
                    }
                    ?>

                    <div class="mb-3">
                        <label for="linkTitle" class="form-label">Link Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="linkTitle" value="<?php echo htmlspecialchars($link_title); ?>" placeholder="Enter a title for this link">
                    </div>

                    <div class="mb-3">
                        <label for="externalUrl" class="form-label">External URL <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="externalUrl" value="<?php echo htmlspecialchars($external_url); ?>" placeholder="Enter external URL">
                        <small class="form-text text-muted">
                            Enter the full URL including https:// (e.g., https://example.com/resource)
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="linkDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="linkDescription" rows="3" placeholder="Enter a description for this link"><?php echo htmlspecialchars($link_description); ?></textarea>
                    </div>

                    <button type="button" class="btn btn-primary save-link-btn">
                        <i class="mdi mdi-content-save"></i> Save Link Content
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Resources Section -->
    <div class="resource-editor mt-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="mdi mdi-attachment"></i> Additional Resources</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Upload supplementary materials for this topic (optional)</p>

                <!-- Resource list -->
                <div class="resource-list mb-3">
                    <?php if (empty($resources)): ?>
                        <div class="empty-resources text-center py-3">
                            <p class="text-muted mb-0">No resources added yet</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($resources as $resource): ?>
                            <div class="resource-item card mb-2" data-resource-id="<?php echo $resource['resource_id']; ?>">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="mdi mdi-file-document-outline me-2"></i>
                                            <span class="resource-name"><?php echo htmlspecialchars(basename($resource['resource_path'])); ?></span>
                                        </div>
                                        <div>
                                            <a href="../uploads/resources/<?php echo htmlspecialchars($resource['resource_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary me-1">
                                                <i class="mdi mdi-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-resource-btn">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Resource upload -->
                <div class="resource-upload">
                    <div class="mb-3">
                        <label for="resourceFile" class="form-label">Upload Resource</label>
                        <input class="form-control" type="file" id="resourceFile">
                        <small class="form-text text-muted">
                            Allowed file types: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, ZIP (max 10MB)
                        </small>
                    </div>

                    <button type="button" class="btn btn-outline-primary upload-resource-btn">
                        <i class="mdi mdi-upload"></i> Upload Resource
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Initialize TinyMCE for rich text editing -->
<script src="https://cdn.tiny.cloud/1/4fnlr08nx5aczp8z0vkgtm2sblkj0y9qywi9iox6hs7ghxgv/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<script>
    $(document).ready(function() {
        // Get the current content type from PHP
        const currentContentType = '<?php echo $content_type; ?>';

        if (currentContentType) {
            // Select the appropriate radio button
            $(`#contentType${currentContentType.charAt(0).toUpperCase() + currentContentType.slice(1)}`).prop('checked', true);

            // Show the correct editor container
            $('.content-editor-container').hide();
            $(`#${currentContentType}EditorContainer`).show();
        }

        // Initialize variables for file upload selections
        if (videoData.upload.exists && videoData.upload.filename) {
            const fileNameElement = document.getElementById('selectedFileName');
            if (fileNameElement) {
                fileNameElement.textContent = videoData.upload.filename;
                fileNameElement.classList.add('text-success');
            }
        }

        // For URL videos, make sure the URL is in the input
        if (videoData.url.exists && videoData.url.value) {
            $('#videoUrl').val(videoData.url.value);
        }
        // Single TinyMCE initialization with safe checks
        function initializeTinyMCE() {
            try {
                // Remove any existing instances first
                if (typeof tinymce !== 'undefined') {
                    tinymce.remove();
                }

                tinymce.init({
                    selector: '.rich-editor',
                    height: 400,
                    menubar: true,
                    plugins: [
                        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                        'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
                    ],
                    toolbar: 'undo redo | blocks | ' +
                        'bold italic backcolor | alignleft aligncenter ' +
                        'alignright alignjustify | bullist numlist outdent indent | ' +
                        'removeformat | help',
                    content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 16px; line-height: 1.6; }',
                    setup: function(editor) {
                        editor.on('init', function() {
                            console.log('TinyMCE editor initialized:', editor.id);
                        });
                    },
                    init_instance_callback: function(editor) {
                        console.log('TinyMCE instance loaded:', editor.id);
                    }
                });
            } catch (error) {
                console.error('Error initializing TinyMCE:', error);

                // Fallback to standard textarea if TinyMCE fails
                $('.rich-editor').each(function() {
                    $(this).prop('rows', 10);
                });
            }
        }

        // Initialize TinyMCE
        initializeTinyMCE();

        // Toggle content editor containers based on selected type
        $('input[name="contentType"]').change(function() {
            const contentType = $(this).val();

            // Hide all content editor containers
            $('.content-editor-container').hide();

            // Show the selected container
            $(`#${contentType}EditorContainer`).show();
        });

        // Save text content with extremely robust retrieval
        $('.save-text-btn').click(function() {
            const textTitle = $('#textTitle').val().trim();

            // Extremely robust content retrieval
            let textContent = '';

            try {
                // Force reinitialize if needed
                if (typeof tinymce === 'undefined' || !tinymce.editors || tinymce.editors.length === 0) {
                    console.warn('Forcing TinyMCE reinitialization');
                    tinymce.remove();
                    tinymce.init({
                        selector: '#textContent',
                        height: 400,
                        plugins: [
                            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                            'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
                        ],
                        toolbar: 'undo redo | blocks | ' +
                            'bold italic backcolor | alignleft aligncenter ' +
                            'alignright alignjustify | bullist numlist outdent indent | ' +
                            'removeformat | help'
                    });
                }

                // Multiple methods to retrieve editor
                const editorMethods = [
                    () => tinymce.get('textContent'),
                    () => tinymce.editors.find(ed => ed.id === 'textContent'),
                    () => {
                        // Last resort: try to find by selector
                        const editors = tinymce.editors;
                        console.log('Available editors:', editors.map(ed => ed.id));
                        return editors[0];
                    }
                ];

                // Try each method to get the editor
                let editor = null;
                for (const method of editorMethods) {
                    editor = method();
                    if (editor) break;
                }

                // Retrieve content
                if (editor) {
                    try {
                        textContent = editor.getContent() ||
                            (editor.getBody && editor.getBody() ? editor.getBody().textContent : '') ||
                            $('#textContent').val();
                    } catch (editorError) {
                        console.warn('Error getting editor content:', editorError);
                        textContent = $('#textContent').val();
                    }

                    console.log('Retrieved content:', {
                        method: editor.id,
                        length: textContent.length
                    });
                } else {
                    // Absolute fallback
                    textContent = $('#textContent').val();
                    console.warn('Could not retrieve TinyMCE editor content');
                }
            } catch (error) {
                console.error('Critical error retrieving content:', error);
                textContent = $('#textContent').val();
            }

            // Validate inputs
            if (!textTitle) {
                showAlert('danger', 'Please enter a content title');
                return;
            }

            if (!textContent || textContent.trim() === '') {
                // Log additional diagnostic information
                console.log('Content retrieval diagnostics:', {
                    textareaValue: $('#textContent').val(),
                    tinymceExists: typeof tinymce !== 'undefined',
                    editorsCount: tinymce ? tinymce.editors.length : 'N/A',
                    editorIds: tinymce ? tinymce.editors.map(ed => ed.id) : 'N/A'
                });

                showAlert('danger', 'Please enter content text');
                return;
            }

            const topicId = $('.content-editor').data('topic-id');
            const contentId = <?php echo $content_id; ?>;

            // Show loading overlay
            createOverlay('Saving text content...');

            // Send AJAX request
            $.ajax({
                url: '../ajax/content/save_text.php',
                type: 'POST',
                data: {
                    topic_id: topicId,
                    content_id: contentId,
                    title: textTitle,
                    content_text: textContent
                },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);

                        if (result.success) {
                            showAlert('success', 'Text content saved successfully');

                            // Update content ID for future saves
                            if (result.content_id) {
                                $('.content-editor').data('content-id', result.content_id);
                            }
                        } else {
                            showAlert('danger', 'Error: ' + result.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response', e);
                        showAlert('danger', 'Error processing server response');
                    }

                    // Hide loading overlay
                    removeOverlay();
                },
                error: function() {
                    showAlert('danger', 'Network error while saving text content');
                    removeOverlay();
                }
            });
        });

        // Save video content
        // Save video content with fixed validation
        $('.save-video-btn').click(function() {
            const contentSource = document.querySelector('input[name="contentSource"]:checked').value;
            const videoTitle = $('#videoTitle').val().trim();
            const videoDescription = $('#videoDescription').val().trim();
            const topicId = $('.content-editor').data('topic-id');
            const contentId = <?php echo $content_id; ?>;

            // Validate title (always required)
            if (!videoTitle) {
                showAlert('danger', 'Please enter a video title');
                return;
            }

            // Validate based on selected content source
            if (contentSource === 'url') {
                const videoUrl = $('#videoUrl').val().trim();

                if (!videoUrl) {
                    showAlert('danger', 'Please enter a video URL');
                    return;
                }

                // Show loading overlay
                createOverlay('Saving video content...');

                // Send AJAX request for URL video
                $.ajax({
                    url: '../ajax/content/save_video.php',
                    type: 'POST',
                    data: {
                        topic_id: topicId,
                        content_id: contentId,
                        title: videoTitle,
                        video_url: videoUrl,
                        description: videoDescription
                    },
                    success: function(response) {
                        try {
                            const result = JSON.parse(response);

                            if (result.success) {
                                showAlert('success', 'Video content saved successfully');

                                // Update content ID for future saves
                                if (result.content_id) {
                                    $('.content-editor').data('content-id', result.content_id);
                                }
                            } else {
                                showAlert('danger', 'Error: ' + result.message);
                            }
                        } catch (e) {
                            console.error('Error parsing response', e);
                            showAlert('danger', 'Error processing server response');
                        }

                        // Hide loading overlay
                        removeOverlay();
                    },
                    error: function() {
                        showAlert('danger', 'Network error while saving video content');
                        removeOverlay();
                    }
                });
            } else {
                // Upload source
                if (!videoData.upload.exists) {
                    showAlert('danger', 'Please select a video file');
                    return;
                }

                if (videoData.upload.file) {
                    // Upload new file
                    uploadVideoFile(videoData.upload.file, videoTitle, videoDescription, topicId, contentId);
                } else {
                    // Using existing uploaded file
                    createOverlay('Saving video content...');

                    // Send AJAX request for uploaded file
                    $.ajax({
                        url: '../ajax/content/save_video.php',
                        type: 'POST',
                        data: {
                            topic_id: topicId,
                            content_id: contentId,
                            title: videoTitle,
                            video_file: videoData.upload.filename,
                            description: videoDescription
                        },
                        success: function(response) {
                            try {
                                const result = JSON.parse(response);

                                if (result.success) {
                                    showAlert('success', 'Video content saved successfully');

                                    // Update content ID for future saves
                                    if (result.content_id) {
                                        $('.content-editor').data('content-id', result.content_id);
                                    }
                                } else {
                                    showAlert('danger', 'Error: ' + result.message);
                                }
                            } catch (e) {
                                console.error('Error parsing response', e);
                                showAlert('danger', 'Error processing server response');
                            }

                            // Hide loading overlay
                            removeOverlay();
                        },
                        error: function() {
                            showAlert('danger', 'Network error while saving video content');
                            removeOverlay();
                        }
                    });
                }
            }
        });
        // Save document content
        $('.save-document-btn').click(function() {
            const documentTitle = $('#documentTitle').val().trim();
            const documentDescription = $('#documentDescription').val().trim();
            const documentFile = $('#documentFile')[0].files[0];
            const topicId = $('.content-editor').data('topic-id');
            const contentId = <?php echo $content_id; ?>;

            // Validate inputs
            if (!documentTitle) {
                showAlert('danger', 'Please enter a document title');
                return;
            }

            if (!documentFile && contentId === 0) {
                showAlert('danger', 'Please select a document to upload');
                return;
            }

            // Show loading overlay
            createOverlay('Saving document content...');

            // Create form data for file upload
            const formData = new FormData();
            formData.append('topic_id', topicId);
            formData.append('content_id', contentId);
            formData.append('title', documentTitle);
            formData.append('description', documentDescription);

            if (documentFile) {
                formData.append('document_file', documentFile);
            }

            // Send AJAX request
            $.ajax({
                url: '../ajax/content/save_document.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        const result = JSON.parse(response);

                        if (result.success) {
                            showAlert('success', 'Document content saved successfully');

                            // Update content ID for future saves
                            if (result.content_id) {
                                $('.content-editor').data('content-id', result.content_id);
                            }

                            // If a new file was uploaded, update the current document display
                            if (result.file_path) {
                                const currentDocument = `
                                    <div class="mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6>Current Document</h6>
                                                <p class="mb-0">
                                                    <i class="mdi mdi-file-document"></i> 
                                                    ${result.file_name}
                                                    <a href="../uploads/documents/${result.file_path}" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                                                        <i class="mdi mdi-eye"></i> View
                                                    </a>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                `;

                                // Add the current document display if not already present
                                if ($('#documentEditorContainer .card-body > .mb-3:last-child').find('h6').text() !== 'Current Document') {
                                    $('#documentFile').closest('.mb-3').after(currentDocument);
                                } else {
                                    $('#documentEditorContainer .card-body > .mb-3:last-child').replaceWith(currentDocument);
                                }

                                // Clear the file input
                                $('#documentFile').val('');
                            }
                        } else {
                            showAlert('danger', 'Error: ' + result.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response', e);
                        showAlert('danger', 'Error processing server response');
                    }

                    // Hide loading overlay
                    removeOverlay();
                },
                error: function() {
                    showAlert('danger', 'Network error while saving document content');
                    removeOverlay();
                }
            });
        });

        // Save link content
        $('.save-link-btn').click(function() {
            const linkTitle = $('#linkTitle').val().trim();
            const externalUrl = $('#externalUrl').val().trim();
            const linkDescription = $('#linkDescription').val().trim();
            const topicId = $('.content-editor').data('topic-id');
            const contentId = <?php echo $content_id; ?>;

            // Validate inputs
            if (!linkTitle) {
                showAlert('danger', 'Please enter a link title');
                return;
            }

            if (!externalUrl) {
                showAlert('danger', 'Please enter an external URL');
                return;
            }

            // Show loading overlay
            createOverlay('Saving link content...');

            // Send AJAX request
            $.ajax({
                url: '../ajax/content/save_link.php',
                type: 'POST',
                data: {
                    topic_id: topicId,
                    content_id: contentId,
                    title: linkTitle,
                    external_url: externalUrl,
                    description: linkDescription
                },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);

                        if (result.success) {
                            showAlert('success', 'Link content saved successfully');

                            // Update content ID for future saves
                            if (result.content_id) {
                                $('.content-editor').data('content-id', result.content_id);
                            }
                        } else {
                            showAlert('danger', 'Error: ' + result.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response', e);
                        showAlert('danger', 'Error processing server response');
                    }

                    // Hide loading overlay
                    removeOverlay();
                },
                error: function() {
                    showAlert('danger', 'Network error while saving link content');
                    removeOverlay();
                }
            });
        });

        // Upload resource
        $('.upload-resource-btn').click(function() {
            const resourceFile = $('#resourceFile')[0].files[0];
            const topicId = $('.content-editor').data('topic-id');

            // Validate input
            if (!resourceFile) {
                showAlert('danger', 'Please select a resource file to upload');
                return;
            }

            // Show loading overlay
            createOverlay('Uploading resource...');

            // Create form data for file upload
            const formData = new FormData();
            formData.append('topic_id', topicId);
            formData.append('resource_file', resourceFile);

            // Send AJAX request
            $.ajax({
                url: '../ajax/content/upload_resource.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        const result = JSON.parse(response);

                        if (result.success) {
                            showAlert('success', 'Resource uploaded successfully');

                            // Add the resource to the list
                            const resourceItem = `
                                <div class="resource-item card mb-2" data-resource-id="${result.resource_id}">
                                    <div class="card-body py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="mdi mdi-file-document-outline me-2"></i>
                                                <span class="resource-name">${result.file_name}</span>
                                            </div>
                                            <div>
                                                <a href="../../uploads/resources/${result.file_path}" target="_blank" class="btn btn-sm btn-outline-primary me-1">
                                                    <i class="mdi mdi-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-resource-btn">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;

                            // Remove empty resources message if present
                            $('.empty-resources').remove();

                            // Add the resource to the list
                            $('.resource-list').append(resourceItem);

                            // Clear the file input
                            $('#resourceFile').val('');
                        } else {
                            showAlert('danger', 'Error: ' + result.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response', e);
                        showAlert('danger', 'Error processing server response');
                    }

                    // Hide loading overlay
                    removeOverlay();
                },
                error: function() {
                    showAlert('danger', 'Network error while uploading resource');
                    removeOverlay();
                }
            });
        });

        // Delete resource
        $(document).on('click', '.delete-resource-btn', function() {
            const resourceItem = $(this).closest('.resource-item');
            const resourceId = resourceItem.data('resource-id');
            const topicId = $('.content-editor').data('topic-id');

            // Confirm delete
            if (!confirm('Are you sure you want to delete this resource? This action cannot be undone.')) {
                return;
            }

            // Show loading overlay
            createOverlay('Deleting resource...');

            // Send AJAX request
            $.ajax({
                url: '../ajax/content/delete_resource.php',
                type: 'POST',
                data: {
                    topic_id: topicId,
                    resource_id: resourceId
                },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);

                        if (result.success) {
                            showAlert('success', 'Resource deleted successfully');

                            // Remove the resource from the list
                            resourceItem.remove();

                            // If no resources left, show empty message
                            if ($('.resource-item').length === 0) {
                                $('.resource-list').html(`
                                    <div class="empty-resources text-center py-3">
                                        <p class="text-muted mb-0">No resources added yet</p>
                                    </div>
                                `);
                            }
                        } else {
                            showAlert('danger', 'Error: ' + result.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response', e);
                        showAlert('danger', 'Error processing server response');
                    }

                    // Hide loading overlay
                    removeOverlay();
                },
                error: function() {
                    showAlert('danger', 'Network error while deleting resource');
                    removeOverlay();
                }
            });
        });
    });
</script>