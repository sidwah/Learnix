<?php
/**
 * Create Course - Content Creation
 * File: ../includes/create-course-content.php
 * 
 * This file contains the interface for creating course content:
 * - Content editor for various types (video, text, etc.)
 * - Content management for topics
 * - Rich text editing capabilities
 */
?>

<div class="content-creation-container">
    <h4 class="header-title mb-3">Content Creation</h4>
    <p class="text-muted">
        Create and manage content for your course topics. Select a topic from the list and add various types of content.
    </p>

    <div class="row mt-4">
        <!-- Left Sidebar - Section/Topic Navigation -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Course Structure</h5>
                </div>
                <div class="card-body p-0">
                    <div id="topicsList" class="list-group list-group-flush topic-nav">
                        <!-- Empty state message -->
                        <div id="noTopicsMessage" class="text-center py-5">
                            <div class="empty-state-icon mb-3">
                                <i class="mdi mdi-information-outline text-muted" style="font-size: 48px;"></i>
                            </div>
                            <h5>No Topics Available</h5>
                            <p class="text-muted">
                                Please create sections and topics in the previous step first.
                            </p>
                        </div>
                        
                        <!-- Topics will be loaded here dynamically -->
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Content Area - Content Editor -->
        <div class="col-lg-8">
            <!-- Empty State (shown when no topic is selected) -->
            <div id="noTopicSelected" class="card">
                <div class="card-body text-center py-5">
                    <div class="empty-state-icon mb-4">
                        <i class="mdi mdi-file-document-outline text-muted" style="font-size: 64px;"></i>
                    </div>
                    <h4>No Topic Selected</h4>
                    <p class="text-muted">
                        Select a topic from the list on the left to start creating content.
                    </p>
                </div>
            </div>
            
            <!-- Content Editor (hidden until topic is selected) -->
            <div id="contentEditor" class="card" style="display: none;">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        Content for: <span id="selectedTopicTitle">Topic Title</span>
                    </h5>
                    <div>
                        <button type="button" id="addContentBtn" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-plus"></i> Add Content
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Content Type Selector (shown when adding new content) -->
                    <div id="contentTypeSelector" style="display: none;">
                        <h6 class="text-muted mb-3">Select Content Type:</h6>
                        <div class="row mb-4">
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="content-type-card border rounded p-3 text-center cursor-pointer" data-content-type="text">
                                    <i class="mdi mdi-text-box-outline mb-2" style="font-size: 32px;"></i>
                                    <h6>Text</h6>
                                    <p class="text-muted small mb-0">Rich text content with formatting</p>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="content-type-card border rounded p-3 text-center cursor-pointer" data-content-type="video">
                                    <i class="mdi mdi-video-outline mb-2" style="font-size: 32px;"></i>
                                    <h6>Video</h6>
                                    <p class="text-muted small mb-0">Upload or embed videos</p>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="content-type-card border rounded p-3 text-center cursor-pointer" data-content-type="link">
                                    <i class="mdi mdi-link-variant mb-2" style="font-size: 32px;"></i>
                                    <h6>Link</h6>
                                    <p class="text-muted small mb-0">External resource links</p>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="content-type-card border rounded p-3 text-center cursor-pointer" data-content-type="document">
                                    <i class="mdi mdi-file-document-outline mb-2" style="font-size: 32px;"></i>
                                    <h6>Document</h6>
                                    <p class="text-muted small mb-0">PDF or presentation files</p>
                                </div>
                            </div>
                        </div>
                        <button id="cancelContentBtn" class="btn btn-light btn-sm">
                            <i class="mdi mdi-close"></i> Cancel
                        </button>
                    </div>
                    
                    <!-- Content List -->
                    <div id="contentList">
                        <!-- Empty state for no content -->
                        <div id="noContentMessage" class="text-center py-4">
                            <div class="empty-state-icon mb-3">
                                <i class="mdi mdi-format-list-text text-muted" style="font-size: 36px;"></i>
                            </div>
                            <h5>No Content Added Yet</h5>
                            <p class="text-muted">
                                Click the "Add Content" button to add content to this topic.
                            </p>
                        </div>
                        
                        <!-- Content items will be loaded here dynamically -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Text Content Editor Modal -->
<div class="modal fade" id="textContentModal" tabindex="-1" aria-labelledby="textContentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textContentModalLabel">Add Text Content</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="textContentForm">
                    <input type="hidden" id="textContentId" name="contentId" value="">
                    <input type="hidden" id="textTopicId" name="topicId" value="">
                    <input type="hidden" id="textContentAction" name="contentAction" value="add">
                    
                    <div class="mb-3">
                        <label for="textContentTitle" class="form-label">Content Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="textContentTitle" name="contentTitle" 
                               placeholder="e.g., Introduction, Key Concepts" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="textContentBody" class="form-label">Content <span class="text-danger">*</span></label>
                        <textarea class="form-control rich-editor" id="textContentBody" name="contentBody" 
                                  rows="12" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="textContentDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="textContentDescription" name="contentDescription" 
                                  rows="2" placeholder="Brief description of this content (optional)"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveTextContentBtn">Save Content</button>
            </div>
        </div>
    </div>
</div>

<!-- Video Content Editor Modal -->
<div class="modal fade" id="videoContentModal" tabindex="-1" aria-labelledby="videoContentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="videoContentModalLabel">Add Video Content</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="videoContentForm">
                    <input type="hidden" id="videoContentId" name="contentId" value="">
                    <input type="hidden" id="videoTopicId" name="topicId" value="">
                    <input type="hidden" id="videoContentAction" name="contentAction" value="add">
                    <input type="hidden" id="videoFilePath" name="videoFilePath" value="">
                    
                    <div class="mb-3">
                        <label for="videoContentTitle" class="form-label">Content Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="videoContentTitle" name="contentTitle" 
                               placeholder="e.g., Introduction Video, Demonstration" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label d-block">Video Source <span class="text-danger">*</span></label>
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="videoSourceType" id="videoSourceUpload" value="upload" checked>
                            <label class="btn btn-outline-primary" for="videoSourceUpload">Upload Video</label>
                            
                            <input type="radio" class="btn-check" name="videoSourceType" id="videoSourceEmbed" value="embed">
                            <label class="btn btn-outline-primary" for="videoSourceEmbed">YouTube/Vimeo</label>
                        </div>
                    </div>
                    
                    <!-- Upload Video Section -->
                    <div id="uploadVideoSection" class="mb-3">
                        <div class="mb-3">
                            <!-- <label for="videoFile" class="form-label">Upload Video File -->
                            <label for="videoFile" class="form-label">Upload Video File <span class="text-danger">*</span></label>
                           <input type="file" class="form-control" id="videoFile" name="videoFile" 
                                  accept="video/mp4,video/webm,video/ogg">
                           <div class="form-text">
                               Maximum file size: 500MB. Supported formats: MP4, WebM, OGG.
                           </div>
                           <div id="videoUploadProgress" class="progress mt-2" style="display: none;">
                               <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" 
                                    style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                           </div>
                       </div>
                       
                       <div id="videoPreviewContainer" class="mt-3" style="display: none;">
                           <label class="form-label">Video Preview:</label>
                           <div class="border rounded p-2">
                               <video id="videoPreview" controls class="w-100" style="max-height: 300px;"></video>
                           </div>
                       </div>
                   </div>
                   
                   <!-- Embed Video Section -->
                   <div id="embedVideoSection" class="mb-3" style="display: none;">
                       <label for="videoEmbedUrl" class="form-label">Video URL <span class="text-danger">*</span></label>
                       <input type="url" class="form-control" id="videoEmbedUrl" name="videoEmbedUrl" 
                              placeholder="e.g., https://www.youtube.com/watch?v=abcdefg">
                       <div class="form-text">
                           Paste a YouTube or Vimeo video URL. The video will be embedded in your course.
                       </div>
                       
                       <div id="embedPreviewContainer" class="mt-3" style="display: none;">
                           <label class="form-label">Video Preview:</label>
                           <div id="embedPreview" class="border rounded p-2" style="min-height: 300px;">
                               <!-- Embed preview will be added here -->
                           </div>
                       </div>
                   </div>
                   
                   <div class="mb-3">
                       <label for="videoDuration" class="form-label">Video Duration (minutes)</label>
                       <input type="number" class="form-control" id="videoDuration" name="videoDuration" 
                              min="1" step="1" placeholder="e.g., 15">
                       <div class="form-text">
                           Enter the video duration in minutes to help calculate course length.
                       </div>
                   </div>
                   
                   <div class="mb-3">
                       <label for="videoContentDescription" class="form-label">Description</label>
                       <textarea class="form-control" id="videoContentDescription" name="contentDescription" 
                                 rows="2" placeholder="Brief description of this video (optional)"></textarea>
                   </div>
               </form>
           </div>
           <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
               <button type="button" class="btn btn-primary" id="saveVideoContentBtn">Save Content</button>
           </div>
       </div>
   </div>
</div>

<!-- Link Content Editor Modal -->
<div class="modal fade" id="linkContentModal" tabindex="-1" aria-labelledby="linkContentModalLabel" aria-hidden="true">
   <div class="modal-dialog">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title" id="linkContentModalLabel">Add Link Content</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
           </div>
           <div class="modal-body">
               <form id="linkContentForm">
                   <input type="hidden" id="linkContentId" name="contentId" value="">
                   <input type="hidden" id="linkTopicId" name="topicId" value="">
                   <input type="hidden" id="linkContentAction" name="contentAction" value="add">
                   
                   <div class="mb-3">
                       <label for="linkContentTitle" class="form-label">Content Title <span class="text-danger">*</span></label>
                       <input type="text" class="form-control" id="linkContentTitle" name="contentTitle" 
                              placeholder="e.g., Additional Resources, Reference Material" required>
                   </div>
                   
                   <div class="mb-3">
                       <label for="linkUrl" class="form-label">Link URL <span class="text-danger">*</span></label>
                       <input type="url" class="form-control" id="linkUrl" name="linkUrl" 
                              placeholder="e.g., https://example.com/resource" required>
                   </div>
                   
                   <div class="mb-3">
                       <div class="form-check form-switch">
                           <input class="form-check-input" type="checkbox" id="linkOpenNewTab" name="linkOpenNewTab" checked>
                           <label class="form-check-label" for="linkOpenNewTab">Open in new tab</label>
                       </div>
                   </div>
                   
                   <div class="mb-3">
                       <label for="linkContentDescription" class="form-label">Description</label>
                       <textarea class="form-control" id="linkContentDescription" name="contentDescription" 
                                 rows="3" placeholder="Describe what this link contains and why it's relevant"></textarea>
                   </div>
               </form>
           </div>
           <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
               <button type="button" class="btn btn-primary" id="saveLinkContentBtn">Save Content</button>
           </div>
       </div>
   </div>
</div>

<!-- Document Content Editor Modal -->
<div class="modal fade" id="documentContentModal" tabindex="-1" aria-labelledby="documentContentModalLabel" aria-hidden="true">
   <div class="modal-dialog">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title" id="documentContentModalLabel">Add Document Content</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
           </div>
           <div class="modal-body">
               <form id="documentContentForm">
                   <input type="hidden" id="documentContentId" name="contentId" value="">
                   <input type="hidden" id="documentTopicId" name="topicId" value="">
                   <input type="hidden" id="documentContentAction" name="contentAction" value="add">
                   <input type="hidden" id="documentFilePath" name="documentFilePath" value="">
                   
                   <div class="mb-3">
                       <label for="documentContentTitle" class="form-label">Content Title <span class="text-danger">*</span></label>
                       <input type="text" class="form-control" id="documentContentTitle" name="contentTitle" 
                              placeholder="e.g., Course Slides, Worksheet" required>
                   </div>
                   
                   <div class="mb-3">
                       <label for="documentFile" class="form-label">Upload Document <span class="text-danger">*</span></label>
                       <input type="file" class="form-control" id="documentFile" name="documentFile" 
                              accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx">
                       <div class="form-text">
                           Maximum file size: 50MB. Supported formats: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX.
                       </div>
                       <div id="documentUploadProgress" class="progress mt-2" style="display: none;">
                           <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" 
                                style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                       </div>
                   </div>
                   
                   <div id="documentPreviewContainer" class="mt-3 border rounded p-2" style="display: none;">
                       <div class="d-flex align-items-center">
                           <i class="mdi mdi-file-document-outline me-2" style="font-size: 32px;"></i>
                           <div>
                               <div id="documentFileName">document.pdf</div>
                               <div id="documentFileSize" class="text-muted small">123 KB</div>
                           </div>
                       </div>
                   </div>
                   
                   <div class="mb-3 mt-3">
                       <div class="form-check form-switch">
                           <input class="form-check-input" type="checkbox" id="documentDownloadable" name="documentDownloadable" checked>
                           <label class="form-check-label" for="documentDownloadable">Allow students to download</label>
                       </div>
                   </div>
                   
                   <div class="mb-3">
                       <label for="documentContentDescription" class="form-label">Description</label>
                       <textarea class="form-control" id="documentContentDescription" name="contentDescription" 
                                 rows="3" placeholder="Describe what this document contains"></textarea>
                   </div>
               </form>
           </div>
           <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
               <button type="button" class="btn btn-primary" id="saveDocumentContentBtn">Save Content</button>
           </div>
       </div>
   </div>
</div>

<script>
// Global variables
let curriculumData = null;
let selectedTopicId = null;
let contentCounter = 0;

document.addEventListener('DOMContentLoaded', function() {
   // Initialize rich text editor if available
   initializeRichTextEditor();
   
   // Set up event listeners
   setupContentCreationEvents();
   
   // Load curriculum data
   loadCurriculumData();
});

/**
* Initialize TinyMCE for rich text editing
*/
function initializeRichTextEditor() {
   if (typeof tinyMCE !== 'undefined') {
       tinyMCE.init({
           selector: '.rich-editor',
           height: 400,
           menubar: false,
           plugins: [
               'advlist autolink lists link image charmap print preview anchor',
               'searchreplace visualblocks code fullscreen',
               'insertdatetime media table paste code help wordcount'
           ],
           toolbar: 'undo redo | formatselect | bold italic backcolor | ' +
                    'alignleft aligncenter alignright alignjustify | ' +
                    'bullist numlist outdent indent | removeformat | help',
           content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; }'
       });
   }
}

/**
* Set up all event listeners for content creation
*/
function setupContentCreationEvents() {
   // Add Content button click
   document.getElementById('addContentBtn').addEventListener('click', function() {
       document.getElementById('contentTypeSelector').style.display = 'block';
       document.getElementById('contentList').style.display = 'none';
   });
   
   // Cancel content button click
   document.getElementById('cancelContentBtn').addEventListener('click', function() {
       document.getElementById('contentTypeSelector').style.display = 'none';
       document.getElementById('contentList').style.display = 'block';
   });
   
   // Content type selection
   const contentTypeCards = document.querySelectorAll('.content-type-card');
   contentTypeCards.forEach(card => {
       card.addEventListener('click', function() {
           const contentType = this.getAttribute('data-content-type');
           openContentEditor(contentType);
       });
   });
   
   // Video source type toggle
   document.querySelectorAll('input[name="videoSourceType"]').forEach(radio => {
       radio.addEventListener('change', function() {
           toggleVideoSource(this.value);
       });
   });
   
   // Video file input change
   document.getElementById('videoFile').addEventListener('change', function() {
       handleVideoFileSelection(this);
   });
   
   // Video embed URL input change
   document.getElementById('videoEmbedUrl').addEventListener('input', function() {
       handleVideoEmbedUrlChange(this.value);
   });
   
   // Document file input change
   document.getElementById('documentFile').addEventListener('change', function() {
       handleDocumentFileSelection(this);
   });
   
   // Save content buttons
   document.getElementById('saveTextContentBtn').addEventListener('click', saveTextContent);
   document.getElementById('saveVideoContentBtn').addEventListener('click', saveVideoContent);
   document.getElementById('saveLinkContentBtn').addEventListener('click', saveLinkContent);
   document.getElementById('saveDocumentContentBtn').addEventListener('click', saveDocumentContent);
   
   // Setup delegation for dynamic content actions
   document.addEventListener('click', function(event) {
       // Edit content button
       if (event.target.classList.contains('edit-content-btn') || 
           event.target.closest('.edit-content-btn')) {
           const button = event.target.classList.contains('edit-content-btn') ? 
                          event.target : event.target.closest('.edit-content-btn');
           const contentId = button.getAttribute('data-content-id');
           const contentType = button.getAttribute('data-content-type');
           editContent(contentId, contentType);
       }
       
       // Delete content button
       if (event.target.classList.contains('delete-content-btn') || 
           event.target.closest('.delete-content-btn')) {
           const button = event.target.classList.contains('delete-content-btn') ? 
                          event.target : event.target.closest('.delete-content-btn');
           const contentId = button.getAttribute('data-content-id');
           const contentType = button.getAttribute('data-content-type');
           deleteContent(contentId, contentType);
       }
       
       // Topic selection in sidebar
       if (event.target.classList.contains('topic-nav-item') || 
           event.target.closest('.topic-nav-item')) {
           const item = event.target.classList.contains('topic-nav-item') ? 
                        event.target : event.target.closest('.topic-nav-item');
           const topicId = item.getAttribute('data-topic-id');
           selectTopic(topicId);
       }
   });
}

/**
* Load course curriculum data
*/
function loadCurriculumData() {
   const courseId = document.getElementById('course_id').value;
   if (!courseId) {
       // Show message for new course
       document.getElementById('noTopicsMessage').innerHTML = `
           <div class="empty-state-icon mb-3">
               <i class="mdi mdi-information-outline text-muted" style="font-size: 48px;"></i>
           </div>
           <h5>New Course</h5>
           <p class="text-muted">
               Create sections and topics in the "Course Structure" step first.
           </p>
       `;
       return;
   }
   
   // Show loading state
   document.getElementById('topicsList').innerHTML = `
       <div class="text-center py-4">
           <div class="spinner-border text-primary" role="status">
               <span class="visually-hidden">Loading...</span>
           </div>
           <p class="mt-2">Loading topics...</p>
       </div>
   `;
   
   // Fetch curriculum data via AJAX
   $.ajax({
       url: 'ajax/get_course_curriculum.php',
       type: 'GET',
       data: { course_id: courseId },
       dataType: 'json',
       success: function(response) {
           if (response.success) {
               curriculumData = response.curriculum;
               renderTopicsSidebar();
               
               // Check if we should auto-select a topic
               const urlParams = new URLSearchParams(window.location.search);
               const topicParam = urlParams.get('topic_id');
               if (topicParam) {
                   selectTopic(topicParam);
               }
           } else {
               // Show error message
               document.getElementById('topicsList').innerHTML = `
                   <div class="text-center py-4">
                       <div class="alert alert-danger">
                           <i class="mdi mdi-alert-circle-outline me-2"></i>
                           Error loading curriculum: ${response.message}
                       </div>
                   </div>
               `;
           }
       },
       error: function() {
           // Show error message
           document.getElementById('topicsList').innerHTML = `
               <div class="text-center py-4">
                   <div class="alert alert-danger">
                       <i class="mdi mdi-alert-circle-outline me-2"></i>
                       Failed to load curriculum. Please refresh the page and try again.
                   </div>
               </div>
           `;
       }
   });
}

/**
* Render the topics sidebar
*/
function renderTopicsSidebar() {
   const topicsList = document.getElementById('topicsList');
   
   // Clear existing content
   topicsList.innerHTML = '';
   
   // Count total topics
   let totalTopics = 0;
   curriculumData.sections.forEach(section => {
       totalTopics += section.topics.length;
   });
   
   // Show appropriate message if no topics
   if (totalTopics === 0) {
       document.getElementById('noTopicsMessage').style.display = 'block';
       return;
   } else {
       document.getElementById('noTopicsMessage').style.display = 'none';
   }
   
   // Sort sections by position
   curriculumData.sections.sort((a, b) => a.position - b.position);
   
   // Render sections and topics
   curriculumData.sections.forEach(section => {
       // Add section header
       const sectionHeader = document.createElement('div');
       sectionHeader.className = 'list-group-item list-group-item-secondary';
       sectionHeader.innerHTML = `<strong>${section.title}</strong>`;
       topicsList.appendChild(sectionHeader);
       
       // Sort topics by position
       section.topics.sort((a, b) => a.position - b.position);
       
       // Add topics for this section
       section.topics.forEach(topic => {
           const topicItem = document.createElement('a');
           topicItem.href = 'javascript:void(0);';
           topicItem.className = 'list-group-item list-group-item-action topic-nav-item';
           topicItem.setAttribute('data-topic-id', topic.id);
           topicItem.setAttribute('data-section-id', section.id);
           
           // Count content items
           const contentCount = topic.content_items ? topic.content_items.length : 0;
           
           // Add completion indicator
           const completionBadge = contentCount > 0 
               ? `<span class="badge bg-success float-end">${contentCount}</span>` 
               : `<span class="badge bg-warning float-end">Empty</span>`;
           
           topicItem.innerHTML = `
               <div class="d-flex w-100 justify-content-between align-items-center">
                   <div>${topic.title}</div>
                   ${completionBadge}
               </div>
           `;
           
           topicsList.appendChild(topicItem);
       });
   });
}

/**
* Select a topic to edit
*/
function selectTopic(topicId) {
   // Find the topic in curriculum data
   let selectedTopic = null;
   let selectedSection = null;
   
   for (const section of curriculumData.sections) {
       const topic = section.topics.find(t => t.id == topicId);
       if (topic) {
           selectedTopic = topic;
           selectedSection = section;
           break;
       }
   }
   
   if (!selectedTopic) {
       console.error(`Topic not found: ${topicId}`);
       return;
   }
   
   // Update selected topic ID
   selectedTopicId = topicId;
   
   // Update UI - highlight selected topic
   document.querySelectorAll('.topic-nav-item').forEach(item => {
       item.classList.remove('active');
   });
   document.querySelector(`.topic-nav-item[data-topic-id="${topicId}"]`).classList.add('active');
   
   // Show content editor, hide empty state
   document.getElementById('noTopicSelected').style.display = 'none';
   document.getElementById('contentEditor').style.display = 'block';
   document.getElementById('contentTypeSelector').style.display = 'none';
   document.getElementById('contentList').style.display = 'block';
   
   // Update topic title
   document.getElementById('selectedTopicTitle').textContent = selectedTopic.title;
   
   // Load content items
   loadTopicContent(selectedTopic);
}

/**
* Load content items for the selected topic
*/
function loadTopicContent(topic) {
   const contentList = document.getElementById('contentList');
   const noContentMessage = document.getElementById('noContentMessage');
   
   // Clear existing content
   contentList.innerHTML = '';
   
   // Check if topic has content
   if (!topic.content_items || topic.content_items.length === 0) {
       noContentMessage.style.display = 'block';
       return;
   }
   
   // Hide no content message
   noContentMessage.style.display = 'none';
   
   // Sort content items by position
   topic.content_items.sort((a, b) => a.position - b.position);
   
   // Render content items
   topic.content_items.forEach(content => {
       renderContentItem(content, contentList);
   });
}

/**
* Render a content item in the list
*/
function renderContentItem(content, container) {
   const contentItem = document.createElement('div');
   contentItem.className = 'content-item card mb-3';
   contentItem.setAttribute('data-content-id', content.id);
   
   // Determine icon based on content type
   let icon, typeLabel;
   switch (content.content_type) {
       case 'text':
           icon = 'mdi-text-box-outline';
           typeLabel = 'Text';
           break;
       case 'video':
           icon = 'mdi-video-outline';
           typeLabel = 'Video';
           break;
       case 'link':
           icon = 'mdi-link-variant';
           typeLabel = 'Link';
           break;
       case 'document':
           icon = 'mdi-file-document-outline';
           typeLabel = 'Document';
           break;
       default:
           icon = 'mdi-file-outline';
           typeLabel = 'Unknown';
   }
   
   // Create content preview based on type
   let contentPreview = '';
   
   if (content.content_type === 'text') {
       // For text, show a snippet
       const textSnippet = content.content_text 
           ? content.content_text.substring(0, 100) + (content.content_text.length > 100 ? '...' : '')
           : 'No content text available';
       
       contentPreview = `
           <div class="text-preview">
               <p class="mb-0 text-muted small">${textSnippet}</p>
           </div>
       `;
   } else if (content.content_type === 'video') {
       if (content.video_url) {
           // For embedded videos
           contentPreview = `
               <div class="video-preview d-flex align-items-center">
                   <i class="mdi mdi-play-circle-outline me-2" style="font-size: 24px;"></i>
                   <div class="text-muted small">${content.video_url}</div>
               </div>
           `;
       } else if (content.file_path) {
           // For uploaded videos
           contentPreview = `
               <div class="video-preview d-flex align-items-center">
                   <i class="mdi mdi-play-circle-outline me-2" style="font-size: 24px;"></i>
                   <div class="text-muted small">Uploaded video: ${content.file_path}</div>
               </div>
           `;
       }
   } else if (content.content_type === 'link') {
       contentPreview = `
           <div class="link-preview d-flex align-items-center">
               <i class="mdi mdi-link-variant me-2" style="font-size: 24px;"></i>
               <div class="text-muted small">${content.external_url}</div>
           </div>
       `;
   } else if (content.content_type === 'document') {
       contentPreview = `
           <div class="document-preview d-flex align-items-center">
               <i class="mdi mdi-file-document-outline me-2" style="font-size: 24px;"></i>
               <div class="text-muted small">${content.file_path}</div>
           </div>
       `;
   }
   
   // Build content item HTML
   contentItem.innerHTML = `
       <div class="card-header bg-light">
           <div class="d-flex justify-content-between align-items-center">
               <div class="d-flex align-items-center">
                   <i class="mdi ${icon} me-2"></i>
                   <h6 class="mb-0">${content.title}</h6>
                   <span class="badge bg-secondary ms-2">${typeLabel}</span>
               </div>
               <div class="content-actions">
                   <button type="button" class="btn btn-sm btn-outline-secondary edit-content-btn me-1" 
                           data-content-id="${content.id}" data-content-type="${content.content_type}">
                       <i class="mdi mdi-pencil"></i>
                   </button>
                   <button type="button" class="btn btn-sm btn-outline-danger delete-content-btn" 
                           data-content-id="${content.id}" data-content-type="${content.content_type}">
                       <i class="mdi mdi-delete"></i>
                   </button>
               </div>
           </div>
       </div>
       <div class="card-body">
           ${contentPreview}
           ${content.description ? `<div class="mt-2 small">${content.description}</div>` : ''}
       </div>
   `;
   
   container.appendChild(contentItem);
}

/**
* Open content editor based on type
*/
function openContentEditor(contentType, contentData = null) {
   // Hide content type selector
   document.getElementById('contentTypeSelector').style.display = 'none';
   
   // Determine which modal to show
   let modalId;
   switch (contentType) {
       case 'text':
           modalId = 'textContentModal';
           break;
       case 'video':
           modalId = 'videoContentModal';
           break;
       case 'link':
           modalId = 'linkContentModal';
           break;
       case 'document':
           modalId = 'documentContentModal';
           break;
       default:
           console.error(`Unsupported content type: ${contentType}`);
           return;
   }
   
   // Set modal title based on action
   const isEdit = contentData !== null;
   const modalTitle = document.getElementById(`${modalId}Label`);
   modalTitle.textContent = isEdit ? `Edit ${capitalizeFirstLetter(contentType)} Content` : `Add ${capitalizeFirstLetter(contentType)} Content`;
   
   // Reset form
   document.getElementById(`${contentType}ContentForm`).reset();
   
   // Set topic ID
   document.getElementById(`${contentType}TopicId`).value = selectedTopicId;
   
   // Set action type
   document.getElementById(`${contentType}ContentAction`).value = isEdit ? 'edit' : 'add';
   
   // Populate form if editing
   if (isEdit) {
       populateContentForm(contentType, contentData);
   } else {
       // Clear any previous content
       if (contentType === 'text' && typeof tinyMCE !== 'undefined') {
           tinyMCE.get('textContentBody').setContent('');
       }
   }
   
   // Show appropriate sections for video content
   if (contentType === 'video') {
       const sourceType = isEdit && contentData.video_url ? 'embed' : 'upload';
       document.getElementById(sourceType === 'upload' ? 'videoSourceUpload' : 'videoSourceEmbed').checked = true;
       toggleVideoSource(sourceType);
   }
   
   // Show modal
   new bootstrap.Modal(document.getElementById(modalId)).show();
}

/**
* Populate content form when editing
*/
function populateContentForm(contentType, contentData) {
   // Set content ID
   document.getElementById(`${contentType}ContentId`).value = contentData.id;
   
   // Set common fields
   document.getElementById(`${contentType}ContentTitle`).value = contentData.title;
   
   if (contentData.description) {
       document.getElementById(`${contentType}ContentDescription`).value = contentData.description;
   }
   
   // Set content type specific fields
   switch (contentType) {
       case 'text':
           if (typeof tinyMCE !== 'undefined') {
               tinyMCE.get('textContentBody').setContent(contentData.content_text || '');
           } else {
               document.getElementById('textContentBody').value = contentData.content_text || '';
           }
           break;
           
       case 'video':
           if (contentData.video_url) {
               // Embedded video
               document.getElementById('videoSourceEmbed').checked = true;
               document.getElementById('videoEmbedUrl').value = contentData.video_url;
               toggleVideoSource('embed');
               handleVideoEmbedUrlChange(contentData.video_url);
           } else if (contentData.file_path) {
               // Uploaded video
               document.getElementById('videoSourceUpload').checked = true;
               document.getElementById('videoFilePath').value = contentData.file_path;
               toggleVideoSource('upload');
               
               // Show video preview
               const videoPreview = document.getElementById('videoPreview');
               videoPreview.src = `uploads/course_videos/${contentData.file_path}`;
               document.getElementById('videoPreviewContainer').style.display = 'block';
           }
           
           if (contentData.duration_minutes) {
               document.getElementById('videoDuration').value = contentData.duration_minutes;
           }
           break;
           
       case 'link':
           document.getElementById('linkUrl').value = contentData.external_url || '';
           document.getElementById('linkOpenNewTab').checked = contentData.open_in_new_tab !== false;
           break;
           
       case 'document':
           if (contentData.file_path) {
            //    document.getElementById('documentFilePath').value = content
            document.getElementById('documentFilePath').value = contentData.file_path;
               
               // Show document preview
               document.getElementById('documentFileName').textContent = contentData.file_path;
               document.getElementById('documentFileSize').textContent = contentData.file_size || 'Unknown size';
               document.getElementById('documentPreviewContainer').style.display = 'block';
           }
           
           document.getElementById('documentDownloadable').checked = contentData.is_downloadable !== false;
           break;
   }
}

/**
* Toggle video source type (upload/embed)
*/
function toggleVideoSource(sourceType) {
   const uploadSection = document.getElementById('uploadVideoSection');
   const embedSection = document.getElementById('embedVideoSection');
   
   if (sourceType === 'upload') {
       uploadSection.style.display = 'block';
       embedSection.style.display = 'none';
   } else {
       uploadSection.style.display = 'none';
       embedSection.style.display = 'block';
   }
}

/**
* Handle video file selection
*/
function handleVideoFileSelection(fileInput) {
   if (!fileInput.files || fileInput.files.length === 0) {
       document.getElementById('videoPreviewContainer').style.display = 'none';
       return;
   }
   
   const file = fileInput.files[0];
   
   // Validate file type
   const validTypes = ['video/mp4', 'video/webm', 'video/ogg'];
   if (!validTypes.includes(file.type)) {
       alert('Please select a valid video file (MP4, WebM, OGG).');
       fileInput.value = '';
       return;
   }
   
   // Validate file size (500MB max)
   const maxSize = 500 * 1024 * 1024; // 500MB in bytes
   if (file.size > maxSize) {
       alert('File size exceeds 500MB limit.');
       fileInput.value = '';
       return;
   }
   
   // Create video preview
   const videoPreview = document.getElementById('videoPreview');
   videoPreview.src = URL.createObjectURL(file);
   document.getElementById('videoPreviewContainer').style.display = 'block';
}

/**
* Handle video embed URL change
*/
function handleVideoEmbedUrlChange(url) {
   const embedPreview = document.getElementById('embedPreview');
   const previewContainer = document.getElementById('embedPreviewContainer');
   
   if (!url) {
       previewContainer.style.display = 'none';
       return;
   }
   
   // Check for YouTube URL
   const youtubeRegex = /(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/;
   const youtubeMatch = url.match(youtubeRegex);
   
   // Check for Vimeo URL
   const vimeoRegex = /(?:vimeo\.com\/(?:video\/)?|player\.vimeo\.com\/video\/)(\d+)(?:\?.*)?$/;
   const vimeoMatch = url.match(vimeoRegex);
   
   if (youtubeMatch) {
       // YouTube video
       const videoId = youtubeMatch[1];
       embedPreview.innerHTML = `
           <iframe width="100%" height="300" src="https://www.youtube.com/embed/${videoId}" 
                   frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; 
                   gyroscope; picture-in-picture" allowfullscreen></iframe>
       `;
       previewContainer.style.display = 'block';
   } else if (vimeoMatch) {
       // Vimeo video
       const videoId = vimeoMatch[1];
       embedPreview.innerHTML = `
           <iframe src="https://player.vimeo.com/video/${videoId}" width="100%" height="300" 
                   frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
       `;
       previewContainer.style.display = 'block';
   } else {
       // Not a recognized video URL
       embedPreview.innerHTML = `
           <div class="alert alert-warning">
               <i class="mdi mdi-alert-circle-outline me-2"></i>
               URL not recognized as a YouTube or Vimeo video.
           </div>
       `;
       previewContainer.style.display = 'block';
   }
}

/**
* Handle document file selection
*/
function handleDocumentFileSelection(fileInput) {
   if (!fileInput.files || fileInput.files.length === 0) {
       document.getElementById('documentPreviewContainer').style.display = 'none';
       return;
   }
   
   const file = fileInput.files[0];
   
   // Validate file type
   const validTypes = [
       'application/pdf', 
       'application/msword', 
       'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
       'application/vnd.ms-powerpoint',
       'application/vnd.openxmlformats-officedocument.presentationml.presentation',
       'application/vnd.ms-excel',
       'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
   ];
   
   if (!validTypes.includes(file.type)) {
       alert('Please select a valid document file (PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX).');
       fileInput.value = '';
       return;
   }
   
   // Validate file size (50MB max)
   const maxSize = 50 * 1024 * 1024; // 50MB in bytes
   if (file.size > maxSize) {
       alert('File size exceeds 50MB limit.');
       fileInput.value = '';
       return;
   }
   
   // Update document preview
   document.getElementById('documentFileName').textContent = file.name;
   document.getElementById('documentFileSize').textContent = formatFileSize(file.size);
   document.getElementById('documentPreviewContainer').style.display = 'block';
}

/**
* Save text content
*/
function saveTextContent() {
   const form = document.getElementById('textContentForm');
   
   // Basic validation
   if (!form.checkValidity()) {
       form.classList.add('was-validated');
       return;
   }
   
   // Get form data
   const contentId = document.getElementById('textContentId').value;
   const topicId = document.getElementById('textTopicId').value;
   const title = document.getElementById('textContentTitle').value;
   const description = document.getElementById('textContentDescription').value;
   const action = document.getElementById('textContentAction').value;
   
   // Get content from TinyMCE
   let content = '';
   if (typeof tinyMCE !== 'undefined') {
       content = tinyMCE.get('textContentBody').getContent();
   } else {
       content = document.getElementById('textContentBody').value;
   }
   
   // Create content data object
   const contentData = {
       id: contentId || `new_${Date.now()}`,
       topic_id: topicId,
       title: title,
       description: description,
       content_type: 'text',
       content_text: content,
       position: 0 // Will be set when saving
   };
   
   // Hide modal
   bootstrap.Modal.getInstance(document.getElementById('textContentModal')).hide();
   
   // Add or update content
   if (action === 'add') {
       addContent(contentData);
   } else {
       updateContent(contentData);
   }
}

/**
* Save video content
*/
function saveVideoContent() {
   const form = document.getElementById('videoContentForm');
   
   // Basic validation
   if (!form.checkValidity()) {
       form.classList.add('was-validated');
       return;
   }
   
   // Get common form data
   const contentId = document.getElementById('videoContentId').value;
   const topicId = document.getElementById('videoTopicId').value;
   const title = document.getElementById('videoContentTitle').value;
   const description = document.getElementById('videoContentDescription').value;
   const duration = document.getElementById('videoDuration').value;
   const action = document.getElementById('videoContentAction').value;
   
   // Get source type
   const sourceType = document.querySelector('input[name="videoSourceType"]:checked').value;
   
   // Create content data object
   const contentData = {
       id: contentId || `new_${Date.now()}`,
       topic_id: topicId,
       title: title,
       description: description,
       content_type: 'video',
       duration_minutes: duration,
       position: 0 // Will be set when saving
   };
   
   if (sourceType === 'embed') {
       // For embedded videos
       const embedUrl = document.getElementById('videoEmbedUrl').value;
       
       if (!embedUrl) {
           alert('Please enter a video URL.');
           return;
       }
       
       contentData.video_url = embedUrl;
       
       // Hide modal and save content
       bootstrap.Modal.getInstance(document.getElementById('videoContentModal')).hide();
       
       if (action === 'add') {
           addContent(contentData);
       } else {
           updateContent(contentData);
       }
   } else {
       // For uploaded videos
       const videoFile = document.getElementById('videoFile').files[0];
       const existingPath = document.getElementById('videoFilePath').value;
       
       // Check if we're editing with an existing file path
       if (action === 'edit' && existingPath && !videoFile) {
           contentData.file_path = existingPath;
           
           // Hide modal and save content
           bootstrap.Modal.getInstance(document.getElementById('videoContentModal')).hide();
           updateContent(contentData);
           return;
       }
       
       // Validate file selection
       if (!videoFile) {
           alert('Please select a video file.');
           return;
       }
       
       // Upload the file first
       uploadVideoFile(videoFile, function(success, filePath) {
           if (success) {
               contentData.file_path = filePath;
               
               // Hide modal and save content
               bootstrap.Modal.getInstance(document.getElementById('videoContentModal')).hide();
               
               if (action === 'add') {
                   addContent(contentData);
               } else {
                   updateContent(contentData);
               }
           } else {
               alert('Failed to upload video. Please try again.');
           }
       });
   }
}

/**
* Save link content
*/
function saveLinkContent() {
   const form = document.getElementById('linkContentForm');
   
   // Basic validation
   if (!form.checkValidity()) {
       form.classList.add('was-validated');
       return;
   }
   
   // Get form data
   const contentId = document.getElementById('linkContentId').value;
   const topicId = document.getElementById('linkTopicId').value;
   const title = document.getElementById('linkContentTitle').value;
   const url = document.getElementById('linkUrl').value;
   const openInNewTab = document.getElementById('linkOpenNewTab').checked;
   const description = document.getElementById('linkContentDescription').value;
   const action = document.getElementById('linkContentAction').value;
   
   // Create content data object
   const contentData = {
       id: contentId || `new_${Date.now()}`,
       topic_id: topicId,
       title: title,
       description: description,
       content_type: 'link',
       external_url: url,
       open_in_new_tab: openInNewTab,
       position: 0 // Will be set when saving
   };
   
   // Hide modal
   bootstrap.Modal.getInstance(document.getElementById('linkContentModal')).hide();
   
   // Add or update content
   if (action === 'add') {
       addContent(contentData);
   } else {
       updateContent(contentData);
   }
}

/**
* Save document content
*/
function saveDocumentContent() {
   const form = document.getElementById('documentContentForm');
   
   // Basic validation
   if (!form.checkValidity()) {
       form.classList.add('was-validated');
       return;
   }
   
   // Get form data
   const contentId = document.getElementById('documentContentId').value;
   const topicId = document.getElementById('documentTopicId').value;
   const title = document.getElementById('documentContentTitle').value;
   const isDownloadable = document.getElementById('documentDownloadable').checked;
   const description = document.getElementById('documentContentDescription').value;
   const action = document.getElementById('documentContentAction').value;
   const existingPath = document.getElementById('documentFilePath').value;
   
   // Create content data object
   const contentData = {
       id: contentId || `new_${Date.now()}`,
       topic_id: topicId,
       title: title,
       description: description,
       content_type: 'document',
       is_downloadable: isDownloadable,
       position: 0 // Will be set when saving
   };
   
   // Check if we're editing with an existing file path
   if (action === 'edit' && existingPath && !document.getElementById('documentFile').files[0]) {
       contentData.file_path = existingPath;
       
       // Hide modal and save content
       bootstrap.Modal.getInstance(document.getElementById('documentContentModal')).hide();
       updateContent(contentData);
       return;
   }
   
   // Validate file selection
   const documentFile = document.getElementById('documentFile').files[0];
   if (!documentFile) {
       alert('Please select a document file.');
       return;
   }
   
   // Upload the file first
   uploadDocumentFile(documentFile, function(success, filePath, fileSize) {
       if (success) {
           contentData.file_path = filePath;
           contentData.file_size = fileSize;
           
           // Hide modal and save content
           bootstrap.Modal.getInstance(document.getElementById('documentContentModal')).hide();
           
           if (action === 'add') {
               addContent(contentData);
           } else {
               updateContent(contentData);
           }
       } else {
           alert('Failed to upload document. Please try again.');
       }
   });
}

/**
* Upload video file
*/
function uploadVideoFile(file, callback) {
   const formData = new FormData();
   formData.append('video', file);
   
   // Get course ID
   const courseId = document.getElementById('course_id').value;
   formData.append('course_id', courseId);
   
   // Show progress
   const progressBar = document.getElementById('videoUploadProgress');
   const progressIndicator = progressBar.querySelector('.progress-bar');
   progressBar.style.display = 'block';
   
   // Upload via AJAX
   $.ajax({
       url: 'ajax/upload_video.php',
       type: 'POST',
       data: formData,
       processData: false,
       contentType: false,
       xhr: function() {
           const xhr = new window.XMLHttpRequest();
           
           // Add progress event
           xhr.upload.addEventListener('progress', function(e) {
               if (e.lengthComputable) {
                   const percent = Math.round((e.loaded / e.total) * 100);
                   progressIndicator.style.width = percent + '%';
                   progressIndicator.textContent = percent + '%';
                   progressIndicator.setAttribute('aria-valuenow', percent);
               }
           }, false);
           
           return xhr;
       },
       success: function(response) {
           // Hide progress
           progressBar.style.display = 'none';
           
           try {
               const result = JSON.parse(response);
               if (result.success) {
                //    callback(true,
                callback(true, result.path);
               } else {
                   console.error('Upload error:', result.message);
                   callback(false);
               }
           } catch (e) {
               console.error('Invalid response from server:', response);
               callback(false);
           }
       },
       error: function() {
           // Hide progress
           progressBar.style.display = 'none';
           
           console.error('Video upload failed');
           callback(false);
       }
   });
}

/**
* Upload document file
*/
function uploadDocumentFile(file, callback) {
   const formData = new FormData();
   formData.append('document', file);
   
   // Get course ID
   const courseId = document.getElementById('course_id').value;
   formData.append('course_id', courseId);
   
   // Show progress
   const progressBar = document.getElementById('documentUploadProgress');
   const progressIndicator = progressBar.querySelector('.progress-bar');
   progressBar.style.display = 'block';
   
   // Upload via AJAX
   $.ajax({
       url: 'ajax/upload_document.php',
       type: 'POST',
       data: formData,
       processData: false,
       contentType: false,
       xhr: function() {
           const xhr = new window.XMLHttpRequest();
           
           // Add progress event
           xhr.upload.addEventListener('progress', function(e) {
               if (e.lengthComputable) {
                   const percent = Math.round((e.loaded / e.total) * 100);
                   progressIndicator.style.width = percent + '%';
                   progressIndicator.textContent = percent + '%';
                   progressIndicator.setAttribute('aria-valuenow', percent);
               }
           }, false);
           
           return xhr;
       },
       success: function(response) {
           // Hide progress
           progressBar.style.display = 'none';
           
           try {
               const result = JSON.parse(response);
               if (result.success) {
                   callback(true, result.path, formatFileSize(file.size));
               } else {
                   console.error('Upload error:', result.message);
                   callback(false);
               }
           } catch (e) {
               console.error('Invalid response from server:', response);
               callback(false);
           }
       },
       error: function() {
           // Hide progress
           progressBar.style.display = 'none';
           
           console.error('Document upload failed');
           callback(false);
       }
   });
}

/**
* Add a new content item
*/
function addContent(contentData) {
   // Find topic in curriculum data
   let topicFound = false;
   
   for (const section of curriculumData.sections) {
       const topicIndex = section.topics.findIndex(t => t.id == contentData.topic_id);
       if (topicIndex !== -1) {
           // Initialize content items array if not exist
           if (!section.topics[topicIndex].content_items) {
               section.topics[topicIndex].content_items = [];
           }
           
           // Set position to end of array
           contentData.position = section.topics[topicIndex].content_items.length;
           
           // Add to array
           section.topics[topicIndex].content_items.push(contentData);
           
           // Update curriculum counter
           contentCounter++;
           
           topicFound = true;
           break;
       }
   }
   
   if (!topicFound) {
       console.error(`Topic not found: ${contentData.topic_id}`);
       return;
   }
   
   // Reload content list
   loadTopicContent(findTopicById(contentData.topic_id));
   
   // Update topic in sidebar
   updateTopicInSidebar(contentData.topic_id);
   
   // Save to server
   saveContentToServer(contentData);
}

/**
* Update an existing content item
*/
function updateContent(contentData) {
   // Find topic and content in curriculum data
   let contentUpdated = false;
   
   for (const section of curriculumData.sections) {
       const topicIndex = section.topics.findIndex(t => t.id == contentData.topic_id);
       if (topicIndex !== -1 && section.topics[topicIndex].content_items) {
           const contentIndex = section.topics[topicIndex].content_items.findIndex(c => c.id == contentData.id);
           if (contentIndex !== -1) {
               // Preserve position
               contentData.position = section.topics[topicIndex].content_items[contentIndex].position;
               
               // Update the content
               section.topics[topicIndex].content_items[contentIndex] = contentData;
               
               contentUpdated = true;
               break;
           }
       }
   }
   
   if (!contentUpdated) {
       console.error(`Content not found: ${contentData.id}`);
       return;
   }
   
   // Reload content list
   loadTopicContent(findTopicById(contentData.topic_id));
   
   // Save to server
   updateContentOnServer(contentData);
}

/**
* Edit a content item
*/
function editContent(contentId, contentType) {
   // Find the content item
   const contentData = findContentById(contentId);
   if (!contentData) {
       console.error(`Content not found: ${contentId}`);
       return;
   }
   
   // Open the content editor
   openContentEditor(contentType, contentData);
}

/**
* Delete a content item
*/
function deleteContent(contentId, contentType) {
   if (!confirm('Are you sure you want to delete this content? This cannot be undone.')) {
       return;
   }
   
   // Find the content item
   let contentDeleted = false;
   let topicId = null;
   
   for (const section of curriculumData.sections) {
       for (const topic of section.topics) {
           if (topic.content_items) {
               const contentIndex = topic.content_items.findIndex(c => c.id == contentId);
               if (contentIndex !== -1) {
                   // Save topic ID
                   topicId = topic.id;
                   
                   // Remove from array
                   topic.content_items.splice(contentIndex, 1);
                   
                   // Update positions
                   topic.content_items.forEach((item, index) => {
                       item.position = index;
                   });
                   
                   // Update curriculum counter
                   contentCounter--;
                   
                   contentDeleted = true;
                   break;
               }
           }
       }
       if (contentDeleted) break;
   }
   
   if (!contentDeleted) {
       console.error(`Content not found: ${contentId}`);
       return;
   }
   
   // Reload content list
   loadTopicContent(findTopicById(topicId));
   
   // Update topic in sidebar
   updateTopicInSidebar(topicId);
   
   // Delete from server
   deleteContentFromServer(contentId);
}

/**
* Save content item to server
*/
function saveContentToServer(contentData) {
   const courseId = document.getElementById('course_id').value;
   if (!courseId) return;
   
   // Create form data
   const formData = {
       course_id: courseId,
       content: JSON.stringify(contentData),
       temp_id: contentData.id
   };
   
   // Send AJAX request
   $.ajax({
       url: 'ajax/save_topic_content.php',
       type: 'POST',
       data: formData,
       dataType: 'json',
       success: function(response) {
           if (response.success) {
               // Update content ID in curriculum data
               for (const section of curriculumData.sections) {
                   const topicIndex = section.topics.findIndex(t => t.id == contentData.topic_id);
                   if (topicIndex !== -1 && section.topics[topicIndex].content_items) {
                       const contentIndex = section.topics[topicIndex].content_items.findIndex(c => c.id === contentData.id);
                       if (contentIndex !== -1) {
                           // Store real ID from server
                           const oldId = section.topics[topicIndex].content_items[contentIndex].id;
                           section.topics[topicIndex].content_items[contentIndex].id = response.content_id;
                           
                           // Update in DOM if needed
                           const contentElement = document.querySelector(`.content-item[data-content-id="${oldId}"]`);
                           if (contentElement) {
                               contentElement.setAttribute('data-content-id', response.content_id);
                               
                               // Update buttons
                               const buttons = contentElement.querySelectorAll(`button[data-content-id="${oldId}"]`);
                               buttons.forEach(button => {
                                   button.setAttribute('data-content-id', response.content_id);
                               });
                           }
                           
                           break;
                       }
                   }
               }
           } else {
               console.error('Error saving content:', response.message);
               alert('Error saving content: ' + response.message);
           }
       },
       error: function() {
           console.error('Failed to save content');
           alert('Failed to save content. Please try again.');
       }
   });
}

/**
* Update content item on server
*/
function updateContentOnServer(contentData) {
   const courseId = document.getElementById('course_id').value;
   if (!courseId) return;
   
   // Create form data
   const formData = {
       course_id: courseId,
       content_id: contentData.id,
       content: JSON.stringify(contentData)
   };
   
   // Send AJAX request
   $.ajax({
       url: 'ajax/update_topic_content.php',
       type: 'POST',
       data: formData,
       dataType: 'json',
       success: function(response) {
           if (!response.success) {
               console.error('Error updating content:', response.message);
               alert('Error updating content: ' + response.message);
           }
       },
       error: function() {
           console.error('Failed to update content');
           alert('Failed to update content. Please try again.');
       }
   });
}

/**
* Delete content item from server
*/
function deleteContentFromServer(contentId) {
   const courseId = document.getElementById('course_id').value;
   if (!courseId) return;
   
   // Create form data
   const formData = {
       course_id: courseId,
       content_id: contentId
   };
   
   // Send AJAX request
   $.ajax({
       url: 'ajax/delete_topic_content.php',
       type: 'POST',
       data: formData,
       dataType: 'json',
       success: function(response) {
           if (!response.success) {
               console.error('Error deleting content:', response.message);
               alert('Error deleting content: ' + response.message);
           }
       },
       error: function() {
           console.error('Failed to delete content');
           alert('Failed to delete content. Please try again.');
       }
   });
}

/**
* Update topic in sidebar to show content count
*/
function updateTopicInSidebar(topicId) {
   // Find topic in curriculum data
   const topic = findTopicById(topicId);
   if (!topic) return;
   
   // Find topic element in sidebar
   const topicElement = document.querySelector(`.topic-nav-item[data-topic-id="${topicId}"]`);
   if (!topicElement) return;
   
   // Update badge
   const contentCount = topic.content_items ? topic.content_items.length : 0;
   const badgeClass = contentCount > 0 ? 'bg-success' : 'bg-warning';
   const badgeText = contentCount > 0 ? contentCount : 'Empty';
   
   // Replace or create badge
   let badge = topicElement.querySelector('.badge');
   if (badge) {
       badge.className = `badge ${badgeClass} float-end`;
       badge.textContent = badgeText;
   } else {
       // Create new badge
       badge = document.createElement('span');
       badge.className = `badge ${badgeClass} float-end`;
       badge.textContent = badgeText;
       
       // Find or create container
       let container = topicElement.querySelector('.d-flex');
       if (!container) {
           container = document.createElement('div');
           container.className = 'd-flex w-100 justify-content-between align-items-center';
           container.innerHTML = `<div>${topic.title}</div>`;
           topicElement.appendChild(container);
       }
       
       container.appendChild(badge);
   }
}

/**
* Helper function to find topic by ID
*/
function findTopicById(topicId) {
   for (const section of curriculumData.sections) {
       const topic = section.topics.find(t => t.id == topicId);
       if (topic) return topic;
   }
   return null;
}

/**
* Helper function to find content by ID
*/
function findContentById(contentId) {
   for (const section of curriculumData.sections) {
       for (const topic of section.topics) {
           if (topic.content_items) {
               const content = topic.content_items.find(c => c.id == contentId);
               if (content) return content;
           }
       }
   }
   return null;
}

/**
* Format file size for display
*/
function formatFileSize(bytes) {
   if (bytes === 0) return '0 Bytes';
   
   const k = 1024;
   const sizes = ['Bytes', 'KB', 'MB', 'GB'];
   const i = Math.floor(Math.log(bytes) / Math.log(k));
   
   return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
* Capitalize first letter of a string
*/
function capitalizeFirstLetter(string) {
   return string.charAt(0).toUpperCase() + string.slice(1);
}
</script>
