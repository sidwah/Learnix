<?php
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
    SELECT st.topic_id, st.title, st.section_id, cs.course_id, c.instructor_id 
    FROM section_topics st
    JOIN course_sections cs ON st.section_id = cs.section_id
    JOIN courses c ON cs.course_id = c.course_id
    WHERE st.topic_id = ?
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
                    
                    if ($content_type === 'video' && $content) {
                        $video_url = $content['video_url'] ?? '';
                        $video_title = $content['title'];
                        $video_description = $content['content_text'] ?? '';
                    }
                    ?>
                    
                    <div class="mb-3">
                        <label for="videoTitle" class="form-label">Video Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="videoTitle" value="<?php echo htmlspecialchars($video_title); ?>" placeholder="Enter a title for this video">
                    </div>
                    
                    <div class="mb-3">
                        <label for="videoUrl" class="form-label">Video URL <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="videoUrl" value="<?php echo htmlspecialchars($video_url); ?>" placeholder="Enter YouTube or Vimeo URL">
                        <small class="form-text text-muted">
                            Paste a YouTube or Vimeo URL (e.g., https://www.youtube.com/watch?v=12345)
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="videoDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="videoDescription" rows="3" placeholder="Enter a description for this video"><?php echo htmlspecialchars($video_description); ?></textarea>
                    </div>
                    
                    <button type="button" class="btn btn-primary save-video-btn">
                        <i class="mdi mdi-content-save"></i> Save Video Content
                    </button>
                </div>
            </div>
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
                        $document_description = $content['content_text'] ?? '';
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
                                    <a href="../../uploads/documents/<?php echo htmlspecialchars($document_file); ?>" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
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
                        $link_description = $content['content_text'] ?? '';
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
                                            <a href="../../uploads/resources/<?php echo htmlspecialchars($resource['resource_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary me-1">
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
        // Initialize TinyMCE for rich text editor
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
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 16px; line-height: 1.6; }'
        });
        
        // Toggle content editor containers based on selected type
        $('input[name="contentType"]').change(function() {
            const contentType = $(this).val();
            
            // Hide all content editor containers
            $('.content-editor-container').hide();
            
            // Show the selected container
            $(`#${contentType}EditorContainer`).show();
        });
        
        // Save video content
        $('.save-video-btn').click(function() {
            const videoTitle = $('#videoTitle').val().trim();
            const videoUrl = $('#videoUrl').val().trim();
            const videoDescription = $('#videoDescription').val().trim();
            const topicId = $('.content-editor').data('topic-id');
            const contentId = <?php echo $content_id; ?>;
            
            // Validate inputs
            if (!videoTitle) {
                showAlert('danger', 'Please enter a video title');
                return;
            }
            
            if (!videoUrl) {
                showAlert('danger', 'Please enter a video URL');
                return;
            }
            
            // Show loading overlay
            createOverlay('Saving video content...');
            
            // Send AJAX request
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
        });
        
        // Save text content
        $('.save-text-btn').click(function() {
            const textTitle = $('#textTitle').val().trim();
            const textContent = tinymce.get('textContent').getContent();
            const topicId = $('.content-editor').data('topic-id');
            const contentId = <?php echo $content_id; ?>;
            
            // Validate inputs
            if (!textTitle) {
                showAlert('danger', 'Please enter a content title');
                return;
            }
            
            if (!textContent) {
                showAlert('danger', 'Please enter content text');
                return;
            }
            
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
                                                    <a href="../../uploads/documents/${result.file_path}" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
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