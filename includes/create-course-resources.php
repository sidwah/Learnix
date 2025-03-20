<?php

/**
 * Enhanced Resource Upload Tab for Course Creation
 * File: ../includes/create-course-resources.php
 * 
 * Improvements:
 * - Modern drag-and-drop file upload
 * - Better organization by section
 * - Improved validation and error handling
 * - Visual progress tracking
 * - Better file type filtering
 */
?>
<div class="card">
    <div class="card-header bg-light">
        <h5 class="mb-0">Upload Course Resources</h5>
    </div>
    <div class="card-body">
        <p class="text-muted">Add supplementary materials for your course topics. These will be available for students to download.</p>

        <!-- Upload Guidelines -->
        <div class="alert alert-info mb-4" role="alert">
            <div class="d-flex align-items-center mb-2">
                <i class="mdi mdi-information-outline me-2" style="font-size: 20px;"></i>
                <h5 class="mb-0"><strong>File Upload Guidelines</strong></h5>
            </div>
            <div class="ps-4">
                <ul class="mb-1">
                    <li><strong>Allowed file types:</strong> PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, ZIP, MP3, MP4</li>
                    <li><strong>Maximum file size:</strong> 16MB per file</li>
                    <li><strong>For videos:</strong> We recommend using YouTube or Vimeo links (added in the Content tab) for better streaming performance</li>
                    <li><strong>File naming:</strong> Use descriptive names without special characters</li>
                </ul>
                <div class="mt-2 small">
                    <i class="mdi mdi-lightbulb text-warning"></i> <strong>Tip:</strong>
                    You can drag and drop files directly onto the upload areas.
                </div>
            </div>
        </div>

        <!-- Files Upload Area -->
        <div id="resourceUploadArea" class="mb-4">
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading upload options...</p>
            </div>
        </div>

        <!-- Empty State (shown when no topics exist) -->
        <div id="emptyResourceState" class="text-center p-5 bg-light rounded border" style="display: none;">
            <i class="mdi mdi-file-upload-outline text-muted mb-3" style="font-size: 48px;"></i>
            <h5>No Topics Available for Resources</h5>
            <p class="text-muted">Please go back to the Content Creation step and add topics first.</p>
            <button type="button" class="btn btn-outline-primary" onclick="goToContentTab()">
                <i class="mdi mdi-arrow-left me-1"></i> Go to Content Creation
            </button>
        </div>

        <!-- Upload Progress Tracker -->
        <div id="uploadProgressContainer" class="mt-4" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Upload Progress</h5>
                <span class="badge bg-primary" id="uploadCounter">0/0 Files</span>
            </div>
            <div class="progress" style="height: 20px;">
                <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                    role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
                    style="width: 0%">0%</div>
            </div>
            <p id="uploadProgressStatus" class="mt-2 text-center text-muted">Ready to upload</p>
        </div>

        <!-- Upload Actions -->
        <div class="d-flex justify-content-between mt-4">
            <button type="button" class="btn btn-outline-secondary" id="skipResourcesBtn">
                Skip Resources
            </button>
            <button type="button" class="btn btn-primary" id="uploadAllResourcesBtn">
                <i class="mdi mdi-upload me-1"></i> Upload All Resources
            </button>
        </div>
    </div>
</div>

<!-- Expandable Section Template -->
<template id="sectionUploadTemplate">
    <div class="resource-section card mb-3" data-section-id="${sectionId}">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0 section-title">
                <i class="mdi mdi-folder-outline me-1"></i> ${sectionTitle}
            </h5>
            <button class="btn btn-sm btn-link toggle-section" type="button">
                <i class="mdi mdi-chevron-down"></i>
            </button>
        </div>
        <div class="card-body section-body">
            <div class="topics-container">
                <!-- Topic upload cards will be inserted here -->
            </div>
        </div>
    </div>
</template>

<!-- Topic Resource Upload Template -->
<template id="topicResourceTemplate">
    <div class="topic-resource-card card mb-3" data-topic-id="${topicId}" data-section-id="${sectionId}">
        <div class="card-header d-flex justify-content-between align-items-center py-2 ps-3 pe-2">
            <h6 class="mb-0 topic-title">
                <i class="mdi mdi-${contentType === 'video' ? 'video' : contentType === 'link' ? 'link' : 'text'} me-1"></i> ${topicTitle}
            </h6>
            <div class="upload-status-badge"></div>
        </div>
        <div class="card-body">
            <!-- Resources Upload -->
            <div class="mb-3">
                <label class="form-label d-flex justify-content-between">
                    <span>Supplementary Resources</span>
                    <small class="text-muted">Max 16MB per file</small>
                </label>
                <div class="dropzone-upload" id="dropzone-${topicId}">
                    <input type="file" class="file-input topic-resources" name="topic_resources_${topicId}[]"
                        multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.mp3,.mp4" style="display: none;">
                    <div class="dropzone-area">
                        <div class="dropzone-message">
                            <i class="mdi mdi-cloud-upload-outline"></i>
                            <p>Drag files here or <span class="text-primary">click to browse</span></p>
                            <small class="text-muted">PDF, DOC, XLS, PPT, ZIP, MP3, MP4</small>
                        </div>
                    </div>
                </div>
                <div class="selected-files mt-2"></div>
            </div>

            <!-- Video Upload (only for video content types) -->
            <div class="video-upload-container ${contentType === 'video' ? '' : 'd-none'}">
                <div class="mb-3">
                    <label class="form-label d-flex justify-content-between">
                        <span>Direct Video Upload (Optional)</span>
                        <small class="text-muted">Max 16MB</small>
                    </label>
                    <div class="dropzone-upload video-dropzone" id="video-dropzone-${topicId}">
                        <input type="file" class="file-input topic-video" name="topic_video_${topicId}"
                            accept="video/mp4,video/webm,video/ogg" style="display: none;">
                        <div class="dropzone-area">
                            <div class="dropzone-message">
                                <i class="mdi mdi-video-outline"></i>
                                <p>Drag video here or <span class="text-primary">click to browse</span></p>
                                <small class="text-muted">MP4, WebM, OGG (recommend YouTube for larger videos)</small>
                            </div>
                        </div>
                    </div>
                    <div class="video-preview mt-2"></div>
                </div>
            </div>

            <!-- Upload Status Area -->
            <div class="upload-status-area mt-3"></div>
        </div>
    </div>
</template>

<!-- Selected File Template -->
<template id="selectedFileTemplate">
    <div class="selected-file" data-filename="${fileName}">
        <div class="d-flex align-items-center p-2 rounded border mb-2">
            <i class="mdi mdi-${fileIcon} me-2 text-${fileColor}"></i>
            <span class="file-name flex-grow-1 text-truncate">${fileName}</span>
            <span class="file-size text-muted me-2">${fileSize}</span>
            <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-file"
                title="Remove file">
                <i class="mdi mdi-close"></i>
            </button>
        </div>
    </div>
</template>

<!-- Custom styles for resource upload -->
<style>
    /* Dropzone styling */
    .dropzone-upload {
        border: 2px dashed #dee2e6;
        border-radius: 0.25rem;
        background-color: #f8f9fa;
        transition: all 0.3s ease;
    }

    .dropzone-upload:hover {
        background-color: #f1f3f5;
        cursor: pointer;
    }

    .dropzone-area {
        padding: 2rem 1rem;
        text-align: center;
    }

    .dropzone-message i {
        font-size: 2rem;
        color: #6c757d;
        margin-bottom: 0.5rem;
    }

    .dropzone-upload.dragover {
        background-color: rgba(13, 110, 253, 0.05);
        border-color: #0d6efd;
    }

    .dropzone-upload.error {
        background-color: rgba(220, 53, 69, 0.05);
        border-color: #dc3545;
    }

    /* Selected files styling */
    .selected-file .file-name {
        max-width: 200px;
    }

    /* Upload status badges */
    .upload-status-badge .badge {
        font-size: 0.75rem;
    }

    /* Resource section toggle */
    .toggle-section {
        transition: transform 0.3s ease;
    }

    .toggle-section.collapsed {
        transform: rotate(-90deg);
    }

    /* Video preview */
    .video-preview video {
        max-width: 100%;
        max-height: 200px;
        margin-top: 0.5rem;
    }

    /* Upload status area styling */
    .upload-status-area .alert {
        margin-bottom: 0.5rem;
        position: relative;
    }

    .upload-status-area .alert .progress {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 3px;
        width: 100%;
        background: transparent;
    }

    .upload-status-area .alert .progress-bar {
        background-color: rgba(255, 255, 255, 0.3);
    }

    /* Upload counter badge */
    #uploadCounter {
        font-size: 0.875rem;
        padding: 0.35rem 0.65rem;
    }

    /* Success/error effects */
    @keyframes success-pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
        }

        70% {
            box-shadow: 0 0 0 5px rgba(40, 167, 69, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
        }
    }

    @keyframes error-pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
        }

        70% {
            box-shadow: 0 0 0 5px rgba(220, 53, 69, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
        }
    }

    .success-pulse {
        animation: success-pulse 1.5s ease;
    }

    .error-pulse {
        animation: error-pulse 1.5s ease;
    }
</style>

<!-- Enhanced JavaScript for Resource Upload -->
<script>
    /**
     * Enhanced Resource Upload System
     * 
     * Features:
     * - Drag and drop file upload
     * - Organized by section and topic
     * - File type validation
     * - Visual progress tracking
     * - Better error handling
     */

    // Global variables for tracking uploads
    let totalUploads = 0;
    let completedUploads = 0;
    let uploadErrors = 0;
    let uploadQueue = [];
    let isUploading = false;
    let sectionTopicMap = {};

    // File type icons and colors mapping
    const fileTypeIcons = {
        'pdf': {
            icon: 'file-pdf-box',
            color: 'danger'
        },
        'doc': {
            icon: 'file-word-box',
            color: 'primary'
        },
        'docx': {
            icon: 'file-word-box',
            color: 'primary'
        },
        'xls': {
            icon: 'file-excel-box',
            color: 'success'
        },
        'xlsx': {
            icon: 'file-excel-box',
            color: 'success'
        },
        'ppt': {
            icon: 'file-powerpoint-box',
            color: 'warning'
        },
        'pptx': {
            icon: 'file-powerpoint-box',
            color: 'warning'
        },
        'zip': {
            icon: 'zip-box',
            color: 'secondary'
        },
        'mp3': {
            icon: 'file-music-outline',
            color: 'info'
        },
        'mp4': {
            icon: 'file-video-outline',
            color: 'danger'
        },
        'webm': {
            icon: 'file-video-outline',
            color: 'danger'
        },
        'ogg': {
            icon: 'file-music-outline',
            color: 'info'
        },
        'default': {
            icon: 'file-outline',
            color: 'secondary'
        }
    };

    /**
     * Initialize the resource upload UI
     */
    document.addEventListener('DOMContentLoaded', function() {
        // Setup tab change event listeners
        setupTabListeners();

        // Setup upload button event listeners
        setupButtonListeners();
    });

    /**
     * Setup tab event listeners
     */
    function setupTabListeners() {
        // Listen for tab shown events
        document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(event) {
                if (event.target.getAttribute('href') === '#step-4-resources' ||
                    event.target.getAttribute('href') === '#resource-upload') {
                    loadResourceUploadUI();
                }
            });
        });

        // Also listen for custom wizard events
        document.addEventListener('wizardStepChanged', function(e) {
            if (e.detail.tabId === '#step-4-resources' ||
                e.detail.tabId === '#resource-upload') {
                loadResourceUploadUI();
            }
        });
    }

    /**
     * Setup button event listeners
     */
    function setupButtonListeners() {
        // Upload all resources button
        document.addEventListener('click', function(e) {
            if (e.target.id === 'uploadAllResourcesBtn' || e.target.closest('#uploadAllResourcesBtn')) {
                e.preventDefault();
                uploadAllResources();
            }
        });

        // Skip resources button
        document.addEventListener('click', function(e) {
            if (e.target.id === 'skipResourcesBtn' || e.target.closest('#skipResourcesBtn')) {
                e.preventDefault();
                skipResources();
            }
        });

        // Next button override (for compatibility with old navigation)
        const nextButton = document.querySelector('.next a') || document.getElementById('nextButton');
        if (nextButton) {
            nextButton.addEventListener('click', function(e) {
                const activeTab = document.querySelector('.nav-link.active');
                if (activeTab && (activeTab.getAttribute('href') === '#resource-upload' ||
                        activeTab.getAttribute('href') === '#step-4-resources')) {
                    e.preventDefault();
                    e.stopPropagation();
                    uploadAllResources();
                    return false;
                }
            });
        }
    }

    /**
     * Load resource upload UI
     */
    function loadResourceUploadUI() {
        const resourceUploadArea = document.getElementById('resourceUploadArea');
        const emptyState = document.getElementById('emptyResourceState');

        if (!resourceUploadArea) return;

        // Show loading state
        resourceUploadArea.innerHTML = `
<div class="text-center py-5">
<div class="spinner-border text-primary" role="status">
<span class="visually-hidden">Loading...</span>
</div>
<p class="mt-2">Loading resource upload options...</p>
</div>
`;

        if (emptyState) {
            emptyState.style.display = 'none';
        }

        // Get course ID
        const courseId = document.getElementById('course_id')?.value;
        if (!courseId) {
            resourceUploadArea.innerHTML = `
<div class="alert alert-warning">
<div class="d-flex align-items-center">
<i class="mdi mdi-alert-circle me-2"></i>
<div>Course ID not found. Please save basic details first.</div>
</div>
</div>
`;
            return;
        }

        // Fetch topics organized by sections
        fetch(`../backend/courses/get_course_topics.php?course_id=${courseId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const topics = data.topics || [];

                    // Show empty state if no topics
                    if (topics.length === 0) {
                        resourceUploadArea.innerHTML = '';
                        if (emptyState) {
                            emptyState.style.display = 'block';
                        } else {
                            resourceUploadArea.innerHTML = `
<div class="text-center p-5 bg-light rounded border">
<i class="mdi mdi-file-upload-outline text-muted mb-3" style="font-size: 48px;"></i>
<h5>No Topics Available for Resources</h5>
<p class="text-muted">Please go back to the Content Creation step and add topics first.</p>
<button type="button" class="btn btn-outline-primary" onclick="goToContentTab()">
<i class="mdi mdi-arrow-left me-1"></i> Go to Content Creation
</button>
</div>
`;
                        }
                        return;
                    }

                    // Organize topics by section
                    const sectionsMap = {};

                    // Also build a map of sections and their topics for later use
                    sectionTopicMap = {};

                    topics.forEach(topic => {
                        const sectionId = topic.section_id;

                        // Initialize section in maps if not exists
                        if (!sectionsMap[sectionId]) {
                            sectionsMap[sectionId] = {
                                id: sectionId,
                                title: topic.section_title || `Section ${sectionId}`,
                                topics: []
                            };
                        }

                        if (!sectionTopicMap[sectionId]) {
                            sectionTopicMap[sectionId] = [];
                        }

                        // Add topic to section
                        sectionsMap[sectionId].topics.push(topic);

                        // Add to section-topic map for later reference
                        sectionTopicMap[sectionId].push(topic.topic_id);
                    });

                    // Clear upload area
                    resourceUploadArea.innerHTML = '';

                    // Create section upload cards
                    Object.values(sectionsMap).forEach(section => {
                        createSectionUploadCard(resourceUploadArea, section);
                    });

                    // Initialize dropzones after rendering
                    initializeDropzones();

                    // Expand first section by default
                    const firstSectionToggle = resourceUploadArea.querySelector('.toggle-section');
                    if (firstSectionToggle) {
                        firstSectionToggle.click();
                    }
                } else {
                    // Error loading topics
                    resourceUploadArea.innerHTML = `
<div class="alert alert-danger">
<div class="d-flex align-items-center">
<i class="mdi mdi-alert-circle me-2"></i>
<div>Error loading topics: ${data.message || 'Unknown error'}</div>
</div>
</div>
`;
                }
            })
            .catch(error => {
                console.error('Error fetching topics:', error);
                resourceUploadArea.innerHTML = `
<div class="alert alert-danger">
<div class="d-flex align-items-center">
<i class="mdi mdi-alert-circle me-2"></i>
<div>Error loading topics: ${error.message}</div>
</div>
</div>
`;
            });
    }

    /**
     * Create a section upload card with its topics
     */
    function createSectionUploadCard(container, section) {
        const template = document.getElementById('sectionUploadTemplate');
        if (!template) return;

        // Create section card from template
        const content = template.innerHTML
            .replace(/\${sectionId}/g, section.id)
            .replace(/\${sectionTitle}/g, section.title);

        // Create container element
        const sectionElement = document.createElement('div');
        sectionElement.innerHTML = content;
        const sectionCard = sectionElement.firstElementChild;

        // Add toggle functionality
        const toggleBtn = sectionCard.querySelector('.toggle-section');
        const sectionBody = sectionCard.querySelector('.section-body');

        if (toggleBtn && sectionBody) {
            toggleBtn.addEventListener('click', function() {
                const isExpanded = sectionBody.style.display !== 'none';
                sectionBody.style.display = isExpanded ? 'none' : 'block';
                toggleBtn.querySelector('i').className = isExpanded ? 'mdi mdi-chevron-right' : 'mdi mdi-chevron-down';
                toggleBtn.classList.toggle('collapsed', isExpanded);
            });

            // Collapse by default
            sectionBody.style.display = 'none';
            toggleBtn.querySelector('i').className = 'mdi mdi-chevron-right';
            toggleBtn.classList.add('collapsed');
        }

        // Add to container
        container.appendChild(sectionCard);

        // Add topic cards
        const topicsContainer = sectionCard.querySelector('.topics-container');
        if (topicsContainer && section.topics && section.topics.length > 0) {
            section.topics.forEach(topic => {
                createTopicUploadCard(topicsContainer, topic);
            });
        } else if (topicsContainer) {
            // No topics message
            topicsContainer.innerHTML = `
<div class="alert alert-light border text-center">
<i class="mdi mdi-information-outline me-1"></i>
No topics in this section
</div>
`;
        }

        return sectionCard;
    }

    /**
     * Create a topic upload card
     */
    function createTopicUploadCard(container, topic) {
        const template = document.getElementById('topicResourceTemplate');
        if (!template) return;

        // Create topic card from template
        const content = template.innerHTML
            .replace(/\${topicId}/g, topic.topic_id)
            .replace(/\${sectionId}/g, topic.section_id)
            .replace(/\${topicTitle}/g, topic.title)
            .replace(/\${contentType}/g, topic.content_type || 'text');

        // Create container element
        const topicElement = document.createElement('div');
        topicElement.innerHTML = content;
        const topicCard = topicElement.firstElementChild;

        // Add to container
        container.appendChild(topicCard);

        return topicCard;
    }

    /**
     * Initialize all dropzones
     */
    function initializeDropzones() {
        // Regular resource dropzones
        document.querySelectorAll('.dropzone-upload:not(.video-dropzone)').forEach(dropzone => {
            initializeDropzone(dropzone);
        });

        // Video dropzones
        document.querySelectorAll('.video-dropzone').forEach(dropzone => {
            initializeVideoDropzone(dropzone);
        });
    }

    /**
     * Initialize a standard dropzone
     */
    function initializeDropzone(dropzone) {
        const input = dropzone.querySelector('.file-input');
        const dropzoneArea = dropzone.querySelector('.dropzone-area');

        if (!input || !dropzoneArea) return;

        // Get selected files container
        const topicCard = dropzone.closest('.topic-resource-card');
        const selectedFilesContainer = topicCard?.querySelector('.selected-files');

        // Click to select files
        dropzoneArea.addEventListener('click', () => {
            input.click();
        });

        // Handle file selection
        input.addEventListener('change', event => {
            handleFileSelection(event.target.files, selectedFilesContainer, input);
        });

        // Handle drag and drop
        dropzoneArea.addEventListener('dragover', e => {
            e.preventDefault();
            dropzoneArea.classList.add('dragover');
        });

        dropzoneArea.addEventListener('dragleave', () => {
            dropzoneArea.classList.remove('dragover');
        });

        dropzoneArea.addEventListener('drop', e => {
            e.preventDefault();
            dropzoneArea.classList.remove('dragover');

            if (e.dataTransfer.files.length) {
                handleFileSelection(e.dataTransfer.files, selectedFilesContainer, input);
            }
        });
    }

    /**
     * Initialize a video dropzone
     */
    function initializeVideoDropzone(dropzone) {
        const input = dropzone.querySelector('.file-input');
        const dropzoneArea = dropzone.querySelector('.dropzone-area');

        if (!input || !dropzoneArea) return;

        // Get video preview container
        const topicCard = dropzone.closest('.topic-resource-card');
        const videoPreviewContainer = topicCard?.querySelector('.video-preview');

        // Click to select video
        dropzoneArea.addEventListener('click', () => {
            input.click();
        });

        // Handle file selection
        input.addEventListener('change', event => {
            handleVideoSelection(event.target.files[0], videoPreviewContainer, input, dropzone);
        });

        // Handle drag and drop
        dropzoneArea.addEventListener('dragover', e => {
            e.preventDefault();
            dropzoneArea.classList.add('dragover');
        });

        dropzoneArea.addEventListener('dragleave', () => {
            dropzoneArea.classList.remove('dragover');
        });

        dropzoneArea.addEventListener('drop', e => {
            e.preventDefault();
            dropzoneArea.classList.remove('dragover');

            if (e.dataTransfer.files.length) {
                handleVideoSelection(e.dataTransfer.files[0], videoPreviewContainer, input, dropzone);
            }
        });
    }

    /**
     * Handle file selection for regular resources
     */
    function handleFileSelection(files, container, input) {
        if (!container || !files.length) return;

        // Check for total files size
        let totalSize = 0;
        let invalidFiles = [];
        let validFiles = [];

        // Get allowed file types from input's accept attribute
        const allowedExtensions = (input.accept || '').split(',').map(type =>
            type.trim().toLowerCase().replace('.', '')
        );

        // Validate files
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const extension = file.name.split('.').pop().toLowerCase();

            // Check file size (16MB limit)
            if (file.size > 16 * 1024 * 1024) {
                invalidFiles.push({
                    name: file.name,
                    reason: 'File size exceeds 16MB limit'
                });
                continue;
            }

            // Check file type
            if (allowedExtensions.length && !allowedExtensions.includes(extension)) {
                invalidFiles.push({
                    name: file.name,
                    reason: 'File type not allowed'
                });
                continue;
            }

            totalSize += file.size;
            validFiles.push(file);
        }

        // Show alerts for invalid files
        if (invalidFiles.length > 0) {
            let errorMessage = 'The following files could not be added:<br>';
            invalidFiles.forEach(file => {
                errorMessage += `<li><strong>${file.name}</strong>: ${file.reason}</li>`;
            });

            if (typeof showAlert === 'function') {
                showAlert('warning', errorMessage);
            } else {
                alert('Some files could not be added due to size or type restrictions.');
            }
        }

        // Process valid files
        validFiles.forEach(file => {
            // Check if file already exists in the list
            const existingFile = container.querySelector(`.selected-file[data-filename="${file.name}"]`);
            if (existingFile) {
                // Flash the existing file to indicate it's already added
                existingFile.classList.add('success-pulse');
                setTimeout(() => {
                    existingFile.classList.remove('success-pulse');
                }, 1500);
                return;
            }

            // Create file preview
            const fileExtension = file.name.split('.').pop().toLowerCase();
            const fileInfo = fileTypeIcons[fileExtension] || fileTypeIcons.default;
            const fileSize = formatFileSize(file.size);

            const template = document.getElementById('selectedFileTemplate');
            if (!template) return;

            const content = template.innerHTML
                .replace(/\${fileName}/g, file.name)
                .replace(/\${fileIcon}/g, fileInfo.icon)
                .replace(/\${fileColor}/g, fileInfo.color)
                .replace(/\${fileSize}/g, fileSize);

            const fileElement = document.createElement('div');
            fileElement.innerHTML = content;
            const fileItem = fileElement.firstElementChild;

            // Add remove button event
            const removeBtn = fileItem.querySelector('.remove-file');
            if (removeBtn) {
                removeBtn.addEventListener('click', () => {
                    // Remove file item
                    fileItem.remove();

                    // Remove file from input's FileList
                    const newFileList = Array.from(input.files).filter(f => f.name !== file.name);

                    // Create a new DataTransfer object
                    const dataTransfer = new DataTransfer();
                    newFileList.forEach(f => dataTransfer.items.add(f));

                    // Set the new FileList
                    input.files = dataTransfer.files;

                    // Update file count badge
                    updateTopicFileBadge(input.closest('.topic-resource-card'));
                });
            }

            // Add file item to container
            container.appendChild(fileItem);

            // Highlight the new file
            fileItem.classList.add('success-pulse');
            setTimeout(() => {
                fileItem.classList.remove('success-pulse');
            }, 1500);

            // Update file count badge
            updateTopicFileBadge(input.closest('.topic-resource-card'));
        });

        // We can't directly modify the input's files
        // To keep the selected files we need to create a new FileList
        if (validFiles.length > 0) {
            // Get current files
            const currentFiles = Array.from(input.files || []).filter(file => {
                // Check for duplicate files we already processed
                return !validFiles.some(newFile => newFile.name === file.name);
            });

            // Create new FileList
            const dataTransfer = new DataTransfer();

            // Add existing files first
            currentFiles.forEach(file => {
                dataTransfer.items.add(file);
            });

            // Add new valid files
            validFiles.forEach(file => {
                dataTransfer.items.add(file);
            });

            // Set new FileList
            input.files = dataTransfer.files;
        }
    }

    /**
     * Handle video file selection
     */
    function handleVideoSelection(file, container, input, dropzone) {
        if (!container || !file) return;

        // Clear previous preview
        container.innerHTML = '';

        // Validate file size (16MB limit)
        if (file.size > 16 * 1024 * 1024) {
            container.innerHTML = `
<div class="alert alert-danger mt-2">
<i class="mdi mdi-alert-circle me-1"></i>
File size exceeds 16MB limit. Please use a YouTube link instead.
</div>
`;

            // Clear the input
            input.value = '';

            // Flash error
            dropzone.classList.add('error');
            setTimeout(() => {
                dropzone.classList.remove('error');
            }, 1500);

            return;
        }

        // Validate file type
        const fileType = file.type.toLowerCase();
        if (!fileType.includes('video/')) {
            container.innerHTML = `
<div class="alert alert-danger mt-2">
<i class="mdi mdi-alert-circle me-1"></i>
Invalid file type. Please select a video file.
</div>
`;

            // Clear the input
            input.value = '';

            // Flash error
            dropzone.classList.add('error');
            setTimeout(() => {
                dropzone.classList.remove('error');
            }, 1500);

            return;
        }

        // Create video preview
        const video = document.createElement('video');
        video.controls = true;
        video.preload = 'metadata';

        // Set source after metadata loaded
        video.addEventListener('loadedmetadata', () => {
            // Validate video duration (limit to 10 minutes for direct uploads)
            if (video.duration > 600) { // 600 seconds = 10 minutes
                container.innerHTML = `
<div class="alert alert-warning mt-2">
<i class="mdi mdi-alert-circle me-1"></i>
Video duration exceeds 10 minutes. Consider using a YouTube link instead.
</div>
`;
                return;
            }

            // Add file info
            const fileSize = formatFileSize(file.size);
            const duration = formatDuration(video.duration);

            container.innerHTML = `
<div class="d-flex align-items-center p-2 rounded border mb-2">
<i class="mdi mdi-video me-2 text-danger"></i>
<div class="flex-grow-1">
<div class="file-name text-truncate">${file.name}</div>
<small class="text-muted">${fileSize} • ${duration}</small>
</div>
<button type="button" class="btn btn-sm btn-link text-danger p-0 remove-video" 
title="Remove video">
<i class="mdi mdi-close"></i>
</button>
</div>
`;

            // Re-add the video element
            container.appendChild(video);

            // Add remove button event
            const removeBtn = container.querySelector('.remove-video');
            if (removeBtn) {
                removeBtn.addEventListener('click', () => {
                    container.innerHTML = '';
                    input.value = '';
                });
            }
        });

        // Set video source
        video.src = URL.createObjectURL(file);

        // Add to container while loading
        container.appendChild(video);
        container.insertAdjacentHTML('afterbegin', `
<div class="d-flex align-items-center p-2 rounded border mb-2">
<i class="mdi mdi-loading mdi-spin me-2 text-primary"></i>
<div class="flex-grow-1">
<div class="file-name text-truncate">${file.name}</div>
<small class="text-muted">Loading video info...</small>
</div>
</div>
`);

        // Update topic card badge
        updateTopicFileBadge(input.closest('.topic-resource-card'));
    }

    /**
     * Update file count badge for a topic
     */
    function updateTopicFileBadge(topicCard) {
        if (!topicCard) return;

        const resourcesInput = topicCard.querySelector('.topic-resources');
        const videoInput = topicCard.querySelector('.topic-video');
        const badgeContainer = topicCard.querySelector('.upload-status-badge');

        if (!badgeContainer) return;

        let fileCount = 0;
        let videoCount = 0;

        // Count regular resources
        if (resourcesInput && resourcesInput.files) {
            fileCount = resourcesInput.files.length;
        }

        // Count video
        if (videoInput && videoInput.files && videoInput.files.length > 0) {
            videoCount = 1;
        }

        // Update badge
        if (fileCount > 0 || videoCount > 0) {
            const filesText = fileCount > 0 ? `${fileCount} file${fileCount > 1 ? 's' : ''}` : '';
            const videoText = videoCount > 0 ? '1 video' : '';
            const separator = fileCount > 0 && videoCount > 0 ? ' + ' : '';
            const text = `${filesText}${separator}${videoText}`;

            badgeContainer.innerHTML = `<span class="badge bg-primary">${text}</span>`;
        } else {
            badgeContainer.innerHTML = '';
        }
    }

    /**
     * Format file size in human-readable format
     */
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';

        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    /**
     * Format video duration
     */
    function formatDuration(seconds) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = Math.floor(seconds % 60);

        return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
    }

    /**
     * Upload all resources
     */
    function uploadAllResources() {
        // Reset upload state
        totalUploads = 0;
        completedUploads = 0;
        uploadErrors = 0;
        uploadQueue = [];
        isUploading = false;

        // Get course ID
        const courseId = document.getElementById('course_id')?.value;
        if (!courseId) {
            if (typeof showAlert === 'function') {
                showAlert('danger', 'Course ID not found. Please save basic details first.');
            } else {
                alert('Course ID not found. Please save basic details first.');
            }
            return;
        }

        // Show loading state
        if (typeof createOverlay === 'function') {
            createOverlay("Preparing resource uploads...");
        }

        // Disable buttons
        const uploadButton = document.getElementById('uploadAllResourcesBtn');
        const skipButton = document.getElementById('skipResourcesBtn');

        if (uploadButton) {
            uploadButton.disabled = true;
            uploadButton.innerHTML = '<i class="mdi mdi-spin mdi-loading me-1"></i> Preparing...';
        }

        if (skipButton) {
            skipButton.disabled = true;
        }

        // Show progress container
        const progressContainer = document.getElementById('uploadProgressContainer');
        const progressBar = document.getElementById('uploadProgressBar');
        const progressStatus = document.getElementById('uploadProgressStatus');
        const uploadCounter = document.getElementById('uploadCounter');

        if (progressContainer) {
            progressContainer.style.display = 'block';
        }

        if (progressBar) {
            progressBar.style.width = '0%';
            progressBar.textContent = '0%';
        }

        if (progressStatus) {
            progressStatus.textContent = 'Preparing to upload...';
        }

        // Build upload queue
        buildUploadQueue(courseId);

        // Update total in counter
        if (uploadCounter) {
            uploadCounter.textContent = `0/${uploadQueue.length} Files`;
        }

        // Check if there are any files to upload
        if (uploadQueue.length === 0) {
            // No files to upload
            if (progressStatus) {
                progressStatus.textContent = 'No files selected for upload.';
            }

            // Remove loading overlay
            if (typeof removeOverlay === 'function') {
                removeOverlay();
            }

            // Re-enable buttons
            if (uploadButton) {
                uploadButton.disabled = false;
                uploadButton.innerHTML = '<i class="mdi mdi-upload me-1"></i> Upload All Resources';
            }

            if (skipButton) {
                skipButton.disabled = false;
            }

            // Show info message
            if (typeof showAlert === 'function') {
                showAlert('info', 'No resources to upload. Proceeding to next step.');
            }

            // Proceed to next step
            proceedToNextStep();

            return;
        }

        // Update UI with total uploads
        totalUploads = uploadQueue.length;

        if (progressStatus) {
            progressStatus.textContent = `Starting upload of ${totalUploads} files...`;
        }

        // Start uploading
        isUploading = true;
        processUploadQueue();

        // Remove initial overlay
        if (typeof removeOverlay === 'function') {
            removeOverlay();
        }
    }

    /**
     * Build the upload queue
     */
    function buildUploadQueue(courseId) {
        // Reset queue
        uploadQueue = [];

        // Get all topic cards
        const topicCards = document.querySelectorAll('.topic-resource-card');

        topicCards.forEach(card => {
            const topicId = card.getAttribute('data-topic-id');
            const sectionId = card.getAttribute('data-section-id');
            const statusArea = card.querySelector('.upload-status-area');

            // Check for resource files
            const resourcesInput = card.querySelector('.topic-resources');
            if (resourcesInput && resourcesInput.files && resourcesInput.files.length > 0) {
                for (let i = 0; i < resourcesInput.files.length; i++) {
                    const file = resourcesInput.files[i];

                    uploadQueue.push({
                        file: file,
                        topicId: topicId,
                        sectionId: sectionId,
                        type: 'resource',
                        statusArea: statusArea
                    });
                }
            }

            // Check for video file
            const videoInput = card.querySelector('.topic-video');
            if (videoInput && videoInput.files && videoInput.files.length > 0) {
                uploadQueue.push({
                    file: videoInput.files[0],
                    topicId: topicId,
                    sectionId: sectionId,
                    type: 'video',
                    statusArea: statusArea
                });
            }
        });
    }

    /**
     * Process the upload queue
     */
    function processUploadQueue() {
        // If not uploading or queue is empty, stop
        if (!isUploading || uploadQueue.length === 0) {
            finishUploads();
            return;
        }

        // Get course ID
        const courseId = document.getElementById('course_id')?.value;
        if (!courseId) {
            finishUploads(true, 'Course ID not found');
            return;
        }

        // Process up to 3 files at once
        const maxConcurrent = 3;
        const currentBatch = [];

        // Get next batch
        for (let i = 0; i < Math.min(maxConcurrent, uploadQueue.length); i++) {
            currentBatch.push(uploadQueue.shift());
        }

        // Upload each file in batch
        const uploadPromises = currentBatch.map(item => {
            return uploadFile(item, courseId);
        });

        // When batch is done, process next batch
        Promise.all(uploadPromises).then(() => {
            // Continue with next batch
            if (uploadQueue.length > 0) {
                processUploadQueue();
            } else {
                finishUploads();
            }
        });
    }

    /**
     * Upload a single file
     */
    function uploadFile(item, courseId) {
        return new Promise((resolve) => {
            const {
                file,
                topicId,
                type,
                statusArea
            } = item;

            // Create status element
            const statusId = `status-${type}-${topicId}-${Date.now()}`;
            const statusElement = document.createElement('div');
            statusElement.id = statusId;
            statusElement.className = 'alert alert-info position-relative';
            statusElement.innerHTML = `
<div class="d-flex align-items-center">
<div class="spinner-border spinner-border-sm me-2" role="status"></div>
<div class="flex-grow-1">
<strong class="d-block text-truncate">${file.name}</strong>
<small class="upload-status">Uploading...</small>
</div>
</div>
<div class="progress">
<div class="progress-bar" role="progressbar" style="width: 0%"></div>
</div>
`;

            if (statusArea) {
                statusArea.appendChild(statusElement);
            }

            // Create FormData
            const formData = new FormData();
            formData.append('course_id', courseId);
            formData.append('topic_id', topicId);
            formData.append('file_type', type);
            formData.append('file', file);

            // Create XHR for upload progress
            const xhr = new XMLHttpRequest();

            // Track progress
            xhr.upload.addEventListener('progress', event => {
                if (event.lengthComputable) {
                    const percent = Math.round((event.loaded / event.total) * 100);

                    // Update status element progress
                    const progressBar = statusElement.querySelector('.progress-bar');
                    if (progressBar) {
                        progressBar.style.width = `${percent}%`;
                    }

                    // Update status text
                    const statusText = statusElement.querySelector('.upload-status');
                    if (statusText) {
                        statusText.textContent = `Uploading... ${percent}%`;
                    }
                }
            });

            // Handle completion
            xhr.addEventListener('load', () => {
                completedUploads++;
                updateOverallProgress();

                try {
                    const response = JSON.parse(xhr.responseText);

                    if (response.success) {
                        // Success
                        statusElement.className = 'alert alert-success';
                        statusElement.innerHTML = `
<div class="d-flex align-items-center">
<i class="mdi mdi-check-circle me-2"></i>
<div>
<strong class="d-block text-truncate">${file.name}</strong>
<small>Uploaded successfully</small>
</div>
</div>
`;
                    } else {
                        // API error
                        uploadErrors++;
                        statusElement.className = 'alert alert-danger';
                        statusElement.innerHTML = `
<div class="d-flex align-items-center">
<i class="mdi mdi-alert-circle me-2"></i>
<div>
<strong class="d-block text-truncate">${file.name}</strong>
<small>Error: ${response.message || 'Upload failed'}</small>
</div>
</div>
`;
                    }
                } catch (error) {
                    // Parse error
                    uploadErrors++;
                    statusElement.className = 'alert alert-danger';
                    statusElement.innerHTML = `
<div class="d-flex align-items-center">
<i class="mdi mdi-alert-circle me-2"></i>
<div>
<strong class="d-block text-truncate">${file.name}</strong>
<small>Error: Response parsing failed</small>
</div>
</div>
`;
                }

                resolve();
            });

            // Handle errors
            xhr.addEventListener('error', () => {
                completedUploads++;
                uploadErrors++;
                updateOverallProgress();

                statusElement.className = 'alert alert-danger';
                statusElement.innerHTML = `
<div class="d-flex align-items-center">
<i class="mdi mdi-alert-circle me-2"></i>
<div>
<strong class="d-block text-truncate">${file.name}</strong>
<small>Error: Network error occurred</small>
</div>
</div>
`;

                resolve();
            });

            // Handle abortion
            xhr.addEventListener('abort', () => {
                completedUploads++;
                uploadErrors++;
                updateOverallProgress();

                statusElement.className = 'alert alert-warning';
                statusElement.innerHTML = `
<div class="d-flex align-items-center">
<i class="mdi mdi-alert me-2"></i>
<div>
<strong class="d-block text-truncate">${file.name}</strong>
<small>Upload cancelled</small>
</div>
</div>
`;

                resolve();
            });

            // Send request
            xhr.open('POST', '../backend/courses/upload_resource.php', true);
            xhr.send(formData);
        });
    }

    /**
     * Update overall progress display
     */
    function updateOverallProgress() {
        const progressBar = document.getElementById('uploadProgressBar');
        const progressStatus = document.getElementById('uploadProgressStatus');
        const uploadCounter = document.getElementById('uploadCounter');

        if (totalUploads > 0) {
            const percentage = Math.round((completedUploads / totalUploads) * 100);

            if (progressBar) {
                progressBar.style.width = `${percentage}%`;
                progressBar.textContent = `${percentage}%`;
            }

            if (progressStatus) {
                progressStatus.textContent = `Uploading ${completedUploads}/${totalUploads} files${uploadErrors > 0 ? ` (${uploadErrors} errors)` : ''}...`;
            }

            if (uploadCounter) {
                uploadCounter.textContent = `${completedUploads}/${totalUploads} Files`;
            }
        }
    }

    /**
     * Finish the upload process
     */
    function finishUploads(hasError = false, errorMessage = '') {
        isUploading = false;

        // Update UI
        const progressStatus = document.getElementById('uploadProgressStatus');
        const uploadButton = document.getElementById('uploadAllResourcesBtn');
        const skipButton = document.getElementById('skipResourcesBtn');

        // Re-enable buttons
        if (uploadButton) {
            uploadButton.disabled = false;
            uploadButton.innerHTML = '<i class="mdi mdi-upload me-1"></i> Upload All Resources';
        }

        if (skipButton) {
            skipButton.disabled = false;
        }

        if (hasError) {
            // Show error message
            if (progressStatus) {
                progressStatus.textContent = `Upload failed: ${errorMessage}`;
            }

            if (typeof showAlert === 'function') {
                showAlert('danger', `Upload failed: ${errorMessage}`);
            }

            return;
        }

        // Update status based on results
        if (progressStatus) {
            if (uploadErrors > 0) {
                progressStatus.textContent = `Upload completed with ${uploadErrors} errors. Please see details above.`;
            } else if (completedUploads > 0) {
                progressStatus.textContent = 'All files uploaded successfully!';
            } else {
                progressStatus.textContent = 'No files were uploaded.';
            }
        }

        // Show appropriate message
        if (uploadErrors > 0) {
            if (typeof showAlert === 'function') {
                showAlert('warning', `Upload completed with ${uploadErrors} errors. You can try to upload the failed files again or proceed to the next step.`);
            }
        } else if (completedUploads > 0) {
            if (typeof showAlert === 'function') {
                showAlert('success', 'All resources uploaded successfully!');
            }

            // Proceed to next step after a short delay
            setTimeout(() => {
                proceedToNextStep();
            }, 1000);
        } else {
            // No files uploaded
            if (typeof showAlert === 'function') {
                showAlert('info', 'No resources were uploaded. Proceeding to next step.');
            }

            // Proceed to next step
            proceedToNextStep();
        }
    }

    /**
     * Skip resources and go to next step
     */
    function skipResources() {
        // Show confirmation if files are selected
        const hasFiles = document.querySelector('.topic-resources').files.length > 0 ||
            document.querySelector('.topic-video').files.length > 0;

        if (hasFiles) {
            const confirmed = confirm('You have selected files that haven\'t been uploaded. Are you sure you want to skip the resource upload?');
            if (!confirmed) {
                return;
            }
        }

        // Show info message
        if (typeof showAlert === 'function') {
            showAlert('info', 'Skipping resource upload and proceeding to next step.');
        }

        // Proceed to next step
        proceedToNextStep();
    }

    /**
     * Proceed to the next step
     */
    function proceedToNextStep() {
        // Update progress bar if function exists
        if (typeof updateProgressBar === 'function') {
            updateProgressBar(80); // 80% complete after fourth step
        }

        // Navigate to next step
        if (typeof navigateToStep === 'function') {
            // New navigation system
            navigateToStep(5);
        } else if (typeof moveToNextTab === 'function') {
            // Old navigation system
            moveToNextTab();
        } else {
            // Fallback: try to find and activate next tab
            const activeTab = document.querySelector('.nav-link.active');
            if (activeTab) {
                const activeTabIndex = Array.from(document.querySelectorAll('.nav-link')).indexOf(activeTab);
                const nextTab = document.querySelectorAll('.nav-link')[activeTabIndex + 1];

                if (nextTab && typeof bootstrap !== 'undefined') {
                    const tab = new bootstrap.Tab(nextTab);
                    tab.show();
                }
            }
        }
    }

    /**
     * Go to Content Creation tab
     */
    function goToContentTab() {
        // Navigate to content tab
        if (typeof navigateToStep === 'function') {
            // New navigation system
            navigateToStep(3);
        } else {
            // Fallback: find and activate content tab
            const contentTab = document.querySelector('a[href="#step-3-content"], a[href="#content-creation"]');
            if (contentTab && typeof bootstrap !== 'undefined') {
                const tab = new bootstrap.Tab(contentTab);
                tab.show();
            }
        }
    }
</script>