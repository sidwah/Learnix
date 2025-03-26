<?php
/**
 * Create Course - Resource Upload
 * File: ../includes/create-course-resources.php
 * 
 * This file contains the interface for uploading supplementary resources for the course:
 * - Bulk file upload for course resources
 * - Resource organization and management
 * - Display of uploaded resources
 */
?>

<div class="resource-upload-container">
    <h4 class="header-title mb-3">Resource Upload</h4>
    <p class="text-muted">
        Upload supplementary resources for your course. These resources will be available to students who enroll.
        You can upload PDFs, documents, spreadsheets, presentations, and other files that support your course content.
    </p>

    <div class="row mt-4">
        <div class="col-lg-12">
            <!-- Resource Upload Zone -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Upload Resources</h5>
                </div>
                <div class="card-body">
                    <div id="resourceDropzone" class="dropzone">
                        <div class="dz-message needsclick">
                            <i class="mdi mdi-cloud-upload-outline text-muted" style="font-size: 48px;"></i>
                            <h5>Drop files here or click to upload</h5>
                            <span class="text-muted">Upload any additional resources for your course</span>
                            <span class="text-muted d-block mt-2">(Max file size: 50MB)</span>
                        </div>
                    </div>
                    
                    <!-- <div class="mt-3">
                        <button type=" -->
                        <div class="mt-3">
                       <button type="button" id="startUploadBtn" class="btn btn-primary">
                           <i class="mdi mdi-upload me-1"></i> Start Upload
                       </button>
                       <button type="button" id="cancelUploadBtn" class="btn btn-light ms-2">
                           <i class="mdi mdi-close me-1"></i> Cancel Upload
                       </button>
                   </div>
                   
                   <div class="alert alert-info mt-3">
                       <div class="d-flex">
                           <div class="me-3">
                               <i class="mdi mdi-information-outline" style="font-size: 24px;"></i>
                           </div>
                           <div>
                               <h5 class="alert-heading">Supported File Types</h5>
                               <p class="mb-0">
                                   PDF (.pdf), Documents (.doc, .docx), Spreadsheets (.xls, .xlsx), 
                                   Presentations (.ppt, .pptx), Images (.jpg, .jpeg, .png), 
                                   Archives (.zip), and Text files (.txt).
                               </p>
                           </div>
                       </div>
                   </div>
               </div>
           </div>
           
           <!-- Resource Management -->
           <div class="card">
               <div class="card-header bg-light">
                   <div class="d-flex justify-content-between align-items-center">
                       <h5 class="card-title mb-0">Resource Management</h5>
                       <div class="header-actions">
                           <select id="resourceFilterSelect" class="form-select form-select-sm d-inline-block me-2" style="width: auto;">
                               <option value="all">All Resources</option>
                               <option value="pdf">PDFs</option>
                               <option value="document">Documents</option>
                               <option value="spreadsheet">Spreadsheets</option>
                               <option value="presentation">Presentations</option>
                               <option value="image">Images</option>
                               <option value="archive">Archives</option>
                               <option value="text">Text Files</option>
                           </select>
                           <div class="btn-group" role="group" aria-label="View options">
                               <button type="button" id="gridViewBtn" class="btn btn-sm btn-outline-secondary active">
                                   <i class="mdi mdi-view-grid"></i>
                               </button>
                               <button type="button" id="listViewBtn" class="btn btn-sm btn-outline-secondary">
                                   <i class="mdi mdi-view-list"></i>
                               </button>
                           </div>
                       </div>
                   </div>
               </div>
               <div class="card-body">
                   <!-- Resource List Empty State -->
                   <div id="resourceEmptyState" class="text-center py-5">
                       <div class="empty-state-icon mb-3">
                           <i class="mdi mdi-file-outline text-muted" style="font-size: 48px;"></i>
                       </div>
                       <h5>No Resources Added Yet</h5>
                       <p class="text-muted">
                           Upload resources to provide additional learning materials for your students.
                       </p>
                   </div>
                   
                   <!-- Resource List -->
                   <div id="resourceGrid" class="row">
                       <!-- Resources will be added here dynamically -->
                   </div>
                   
                   <!-- Resource Pagination -->
                   <div id="resourcePagination" class="d-flex justify-content-between align-items-center mt-4" style="display: none !important;">
                       <div class="showing-text text-muted small">
                           Showing <span id="resourceStart">0</span> to <span id="resourceEnd">0</span> of 
                           <span id="resourceTotal">0</span> resources
                       </div>
                       <ul class="pagination pagination-sm">
                           <li class="page-item disabled">
                               <a class="page-link" href="#" tabindex="-1">Previous</a>
                           </li>
                           <li class="page-item active"><a class="page-link" href="#">1</a></li>
                           <li class="page-item"><a class="page-link" href="#">2</a></li>
                           <li class="page-item"><a class="page-link" href="#">3</a></li>
                           <li class="page-item">
                               <a class="page-link" href="#">Next</a>
                           </li>
                       </ul>
                   </div>
               </div>
           </div>
       </div>
   </div>
</div>

<!-- Resource Detail Modal -->
<div class="modal fade" id="resourceDetailModal" tabindex="-1" aria-labelledby="resourceDetailModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title" id="resourceDetailModalLabel">Resource Details</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
           </div>
           <div class="modal-body">
               <div class="row">
                   <div class="col-md-4">
                       <div id="resourcePreview" class="resource-preview text-center">
                           <!-- Resource preview will be shown here -->
                       </div>
                   </div>
                   <div class="col-md-8">
                       <h5 id="resourceDetailName">Resource Name</h5>
                       <div class="mb-3">
                           <span id="resourceDetailType" class="badge bg-primary me-2">Type</span>
                           <span id="resourceDetailSize" class="text-muted small">Size</span>
                       </div>
                       
                       <div class="mb-3">
                           <label for="resourceDetailDescription" class="form-label">Description</label>
                           <textarea class="form-control" id="resourceDetailDescription" rows="3" placeholder="Add a description for this resource"></textarea>
                       </div>
                       
                       <div class="mb-3">
                           <label class="form-label d-block">Resource Options</label>
                           <div class="form-check form-switch">
                               <input class="form-check-input" type="checkbox" id="resourceDetailDownloadable" checked>
                               <label class="form-check-label" for="resourceDetailDownloadable">Allow students to download</label>
                           </div>
                       </div>
                       
                       <div class="mb-3">
                           <label class="form-label">Topic Association</label>
                           <select class="form-select" id="resourceDetailTopic">
                               <option value="">Not associated with any topic</option>
                               <!-- Topics will be added here dynamically -->
                           </select>
                           <div class="form-text">
                               Associate this resource with a specific topic in your course.
                           </div>
                       </div>
                   </div>
               </div>
           </div>
           <div class="modal-footer">
               <div class="me-auto">
                   <button type="button" class="btn btn-danger" id="deleteResourceBtn">
                       <i class="mdi mdi-delete me-1"></i> Delete
                   </button>
               </div>
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
               <button type="button" class="btn btn-primary" id="saveResourceDetailBtn">Save Changes</button>
           </div>
       </div>
   </div>
</div>

<!-- Include Dropzone.js -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>

<script>
// Global variables
let courseResources = [];
let resourceDropzone;
let currentResourceId = null;
let currentView = 'grid';
let currentFilter = 'all';
let resourcesPerPage = 12;
let currentPage = 1;

document.addEventListener('DOMContentLoaded', function() {
   // Initialize Dropzone
   initializeDropzone();
   
   // Setup event listeners
   setupResourceEvents();
   
   // Load course resources
   loadCourseResources();
});

/**
* Initialize Dropzone for file uploads
*/
function initializeDropzone() {
   // Disable Dropzone auto discovery
   Dropzone.autoDiscover = false;
   
   // Initialize Dropzone
   resourceDropzone = new Dropzone("#resourceDropzone", {
       url: "ajax/upload_resource.php",
       paramName: "resource",
       maxFilesize: 50, // 50MB
       maxFiles: 10,
       parallelUploads: 2,
       uploadMultiple: false,
       addRemoveLinks: true,
       autoProcessQueue: false,
       acceptedFiles: ".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip,.txt",
       init: function() {
           var myDropzone = this;
           
           // Start upload button
           document.getElementById('startUploadBtn').addEventListener('click', function() {
               myDropzone.processQueue();
           });
           
           // Cancel upload button
           document.getElementById('cancelUploadBtn').addEventListener('click', function() {
               myDropzone.removeAllFiles(true);
           });
           
           // Process next file when one completes
           this.on("success", function() {
               if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length > 0) {
                   this.processQueue();
               }
           });
           
           // Refresh resource list after all files are processed
           this.on("queuecomplete", function() {
               loadCourseResources();
           });
           
           // Add course_id to the request
           this.on("sending", function(file, xhr, formData) {
               const courseId = document.getElementById('course_id').value;
               formData.append("course_id", courseId);
           });
       }
   });
}

/**
* Setup event listeners for resource management
*/
function setupResourceEvents() {
   // View toggle buttons
   document.getElementById('gridViewBtn').addEventListener('click', function() {
       setResourceView('grid');
   });
   
   document.getElementById('listViewBtn').addEventListener('click', function() {
       setResourceView('list');
   });
   
   // Resource filter
   document.getElementById('resourceFilterSelect').addEventListener('change', function() {
       currentFilter = this.value;
       currentPage = 1;
       renderResources();
   });
   
   // Modal events
   document.getElementById('saveResourceDetailBtn').addEventListener('click', function() {
       saveResourceDetail();
   });
   
   document.getElementById('deleteResourceBtn').addEventListener('click', function() {
       deleteResource();
   });
   
   // Delegate click for resource items
   document.getElementById('resourceGrid').addEventListener('click', function(event) {
       // Resource item click
       if (event.target.closest('.resource-item')) {
           const resourceItem = event.target.closest('.resource-item');
           const resourceId = resourceItem.getAttribute('data-resource-id');
           openResourceDetail(resourceId);
       }
   });
}

/**
* Load course resources from server
*/
function loadCourseResources() {
   const courseId = document.getElementById('course_id').value;
   if (!courseId) {
       // Show empty state for new courses
       document.getElementById('resourceEmptyState').style.display = 'block';
       return;
   }
   
   // Show loading state
   document.getElementById('resourceGrid').innerHTML = `
       <div class="col-12 text-center py-5">
           <div class="spinner-border text-primary" role="status">
               <span class="visually-hidden">Loading...</span>
           </div>
           <p class="mt-2">Loading resources...</p>
       </div>
   `;
   
   // Fetch resources via AJAX
   $.ajax({
       url: 'ajax/get_course_resources.php',
       type: 'GET',
       data: { course_id: courseId },
       dataType: 'json',
       success: function(response) {
           if (response.success) {
               courseResources = response.resources;
               renderResources();
               
               // Populate topics in resource detail modal
               populateTopicDropdown();
           } else {
               console.error('Error loading resources:', response.message);
               document.getElementById('resourceGrid').innerHTML = `
                   <div class="col-12">
                       <div class="alert alert-danger">
                           <i class="mdi mdi-alert-circle-outline me-2"></i>
                           Error loading resources: ${response.message}
                       </div>
                   </div>
               `;
           }
       },
       error: function() {
           console.error('Failed to load resources');
           document.getElementById('resourceGrid').innerHTML = `
               <div class="col-12">
                   <div class="alert alert-danger">
                       <i class="mdi mdi-alert-circle-outline me-2"></i>
                       Failed to load resources. Please refresh the page and try again.
                   </div>
               </div>
           `;
       }
   });
}

/**
* Set resource view mode (grid or list)
*/
function setResourceView(viewMode) {
   // Update active button
   document.getElementById('gridViewBtn').classList.toggle('active', viewMode === 'grid');
   document.getElementById('listViewBtn').classList.toggle('active', viewMode === 'list');
   
   // Set current view
   currentView = viewMode;
   
   // Re-render resources
   renderResources();
}

/**
* Render resources based on current view, filter, and page
*/
function renderResources() {
   const resourceGrid = document.getElementById('resourceGrid');
   
   // Clear grid
   resourceGrid.innerHTML = '';
   
   // Check if resources exist
   if (!courseResources || courseResources.length === 0) {
       document.getElementById('resourceEmptyState').style.display = 'block';
       document.getElementById('resourcePagination').style.display = 'none !important';
       return;
   }
   
   // Hide empty state
   document.getElementById('resourceEmptyState').style.display = 'none';
   
   // Filter resources
   let filteredResources = courseResources;
   if (currentFilter !== 'all') {
       filteredResources = courseResources.filter(resource => getResourceType(resource.file_name) === currentFilter);
   }
   
   // Check if any resources match the filter
   if (filteredResources.length === 0) {
       resourceGrid.innerHTML = `
           <div class="col-12 text-center py-5">
               <div class="empty-state-icon mb-3">
                   <i class="mdi mdi-filter-remove-outline text-muted" style="font-size: 48px;"></i>
               </div>
               <h5>No Resources Match Filter</h5>
               <p class="text-muted">
                   Try changing your filter selection to see more resources.
               </p>
           </div>
       `;
       document.getElementById('resourcePagination').style.display = 'none !important';
       return;
   }
   
   // Paginate resources
   const startIndex = (currentPage - 1) * resourcesPerPage;
   const endIndex = Math.min(startIndex + resourcesPerPage, filteredResources.length);
   const paginatedResources = filteredResources.slice(startIndex, endIndex);
   // Render resources
   if (currentView === 'grid') {
       renderGridView(paginatedResources, resourceGrid);
   } else {
       renderListView(paginatedResources, resourceGrid);
   }
   
   // Update pagination
   updatePagination(filteredResources.length, startIndex, endIndex);
}

/**
* Render resources in grid view
*/
function renderGridView(resources, container) {
   resources.forEach(resource => {
       const resourceItem = document.createElement('div');
       resourceItem.className = 'col-md-3 col-sm-6 mb-4';
       
       const resourceType = getResourceType(resource.file_name);
       const resourceIcon = getResourceIcon(resourceType);
       const resourceTypeLabel = getResourceTypeLabel(resourceType);
       
       resourceItem.innerHTML = `
           <div class="resource-item card h-100 cursor-pointer" data-resource-id="${resource.id}">
               <div class="card-body text-center p-3">
                   <div class="resource-icon mb-3">
                       <i class="mdi ${resourceIcon}" style="font-size: 48px;"></i>
                   </div>
                   <h6 class="resource-name mb-1" title="${resource.file_name}">${truncateString(resource.file_name, 20)}</h6>
                   <div class="text-muted small mb-2">${formatFileSize(resource.file_size)}</div>
                   <span class="badge bg-${getResourceBadgeColor(resourceType)}">${resourceTypeLabel}</span>
               </div>
           </div>
       `;
       
       container.appendChild(resourceItem);
   });
}

/**
* Render resources in list view
*/
function renderListView(resources, container) {
   const listContainer = document.createElement('div');
   listContainer.className = 'col-12';
   
   const table = document.createElement('table');
   table.className = 'table table-hover';
   
   // Create table header
   const tableHeader = document.createElement('thead');
   tableHeader.innerHTML = `
       <tr>
           <th style="width: 50px;"></th>
           <th>File Name</th>
           <th>Type</th>
           <th>Size</th>
           <th>Topic</th>
           <th>Uploaded</th>
       </tr>
   `;
   
   // Create table body
   const tableBody = document.createElement('tbody');
   
   resources.forEach(resource => {
       const resourceType = getResourceType(resource.file_name);
       const resourceIcon = getResourceIcon(resourceType);
       const resourceTypeLabel = getResourceTypeLabel(resourceType);
       
       const row = document.createElement('tr');
       row.className = 'resource-item';
       row.setAttribute('data-resource-id', resource.id);
       
       // Format date
       const uploadDate = new Date(resource.created_at);
       const formattedDate = uploadDate.toLocaleDateString();
       
       row.innerHTML = `
           <td><i class="mdi ${resourceIcon}"></i></td>
           <td>${resource.file_name}</td>
           <td><span class="badge bg-${getResourceBadgeColor(resourceType)}">${resourceTypeLabel}</span></td>
           <td>${formatFileSize(resource.file_size)}</td>
           <td>${resource.topic_id ? getTopic(resource.topic_id).title : '-'}</td>
           <td>${formattedDate}</td>
       `;
       
       tableBody.appendChild(row);
   });
   
   // Add header and body to table
   table.appendChild(tableHeader);
   table.appendChild(tableBody);
   
   // Add table to container
   listContainer.appendChild(table);
   container.appendChild(listContainer);
}

/**
* Update pagination controls
*/
function updatePagination(totalResources, startIndex, endIndex) {
   const pagination = document.getElementById('resourcePagination');
   const startText = document.getElementById('resourceStart');
   const endText = document.getElementById('resourceEnd');
   const totalText = document.getElementById('resourceTotal');
   
   // Update text
   startText.textContent = startIndex + 1;
   endText.textContent = endIndex;
   totalText.textContent = totalResources;
   
   // Show/hide pagination based on resource count
   if (totalResources <= resourcesPerPage) {
       pagination.style.display = 'none !important';
   } else {
       pagination.style.display = 'flex !important';
       
       // Calculate total pages
       const totalPages = Math.ceil(totalResources / resourcesPerPage);
       
       // TODO: Implement pagination controls
       // This would include generating page numbers and handling next/previous buttons
   }
}

/**
* Open resource detail modal
*/
function openResourceDetail(resourceId) {
   // Find resource
   const resource = courseResources.find(r => r.id == resourceId);
   if (!resource) {
       console.error(`Resource not found: ${resourceId}`);
       return;
   }
   
   // Set current resource ID
   currentResourceId = resourceId;
   
   // Set resource details
   document.getElementById('resourceDetailModalLabel').textContent = resource.file_name;
   document.getElementById('resourceDetailName').textContent = resource.file_name;
   
   const resourceType = getResourceType(resource.file_name);
   document.getElementById('resourceDetailType').textContent = getResourceTypeLabel(resourceType);
   document.getElementById('resourceDetailType').className = `badge bg-${getResourceBadgeColor(resourceType)} me-2`;
   
   document.getElementById('resourceDetailSize').textContent = formatFileSize(resource.file_size);
   document.getElementById('resourceDetailDescription').value = resource.description || '';
   document.getElementById('resourceDetailDownloadable').checked = resource.is_downloadable !== false;
   
   // Set topic if associated
   if (resource.topic_id) {
       document.getElementById('resourceDetailTopic').value = resource.topic_id;
   } else {
       document.getElementById('resourceDetailTopic').value = '';
   }
   
   // Set resource preview based on type
   const previewContainer = document.getElementById('resourcePreview');
   const resourceIcon = getResourceIcon(resourceType);
   
   if (resourceType === 'image') {
       // Show image preview
       previewContainer.innerHTML = `
           <img src="uploads/course_resources/${resource.file_path}" alt="${resource.file_name}" 
                class="img-fluid rounded" style="max-height: 300px;">
       `;
   } else if (resourceType === 'pdf' && resource.file_path) {
       // Show PDF preview (PDF.js could be integrated for better preview)
       previewContainer.innerHTML = `
           <div class="pdf-preview border rounded p-3 mb-3">
               <i class="mdi ${resourceIcon}" style="font-size: 64px;"></i>
               <div class="mt-2">PDF Preview</div>
           </div>
           <a href="uploads/course_resources/${resource.file_path}" target="_blank" class="btn btn-sm btn-primary">
               <i class="mdi mdi-eye me-1"></i> View PDF
           </a>
       `;
   } else {
       // Show generic file icon
       previewContainer.innerHTML = `
           <div class="file-icon-preview">
               <i class="mdi ${resourceIcon}" style="font-size: 84px;"></i>
           </div>
       `;
   }
   
   // Show modal
   new bootstrap.Modal(document.getElementById('resourceDetailModal')).show();
}

/**
* Save resource detail changes
*/
function saveResourceDetail() {
   if (!currentResourceId) return;
   
   // Find resource
   const resourceIndex = courseResources.findIndex(r => r.id == currentResourceId);
   if (resourceIndex === -1) {
       console.error(`Resource not found: ${currentResourceId}`);
       return;
   }
   
   // Get form values
   const description = document.getElementById('resourceDetailDescription').value;
   const isDownloadable = document.getElementById('resourceDetailDownloadable').checked;
   const topicId = document.getElementById('resourceDetailTopic').value;
   
   // Update resource in local array
   courseResources[resourceIndex].description = description;
   courseResources[resourceIndex].is_downloadable = isDownloadable;
   courseResources[resourceIndex].topic_id = topicId || null;
   
   // Save changes to server
   saveResourceToServer(courseResources[resourceIndex]);
   
   // Hide modal
   bootstrap.Modal.getInstance(document.getElementById('resourceDetailModal')).hide();
   
   // Re-render resources
   renderResources();
}

/**
* Delete resource
*/
function deleteResource() {
   if (!currentResourceId) return;
   
   if (!confirm('Are you sure you want to delete this resource? This cannot be undone.')) {
       return;
   }
   
   // Find resource
   const resourceIndex = courseResources.findIndex(r => r.id == currentResourceId);
   if (resourceIndex === -1) {
       console.error(`Resource not found: ${currentResourceId}`);
       return;
   }
   
   // Remove from local array
   const resource = courseResources[resourceIndex];
   courseResources.splice(resourceIndex, 1);
   
   // Delete from server
   deleteResourceFromServer(resource.id);
   
   // Hide modal
   bootstrap.Modal.getInstance(document.getElementById('resourceDetailModal')).hide();
   
   // Re-render resources
   renderResources();
}

/**
* Populate topic dropdown in resource detail modal
*/
function populateTopicDropdown() {
   const topicSelect = document.getElementById('resourceDetailTopic');
   
   // Clear existing options, keeping the default
   while (topicSelect.options.length > 1) {
       topicSelect.remove(1);
   }
   
   // Get course curriculum
   $.ajax({
       url: 'ajax/get_course_curriculum.php',
       type: 'GET',
       data: { course_id: document.getElementById('course_id').value },
       dataType: 'json',
       success: function(response) {
           if (response.success && response.curriculum && response.curriculum.sections) {
               const curriculum = response.curriculum;
               
               // Add option group for each section
               curriculum.sections.forEach(section => {
                   if (section.topics && section.topics.length > 0) {
                       const optgroup = document.createElement('optgroup');
                       optgroup.label = section.title;
                       
                       // Add topics as options
                       section.topics.forEach(topic => {
                           const option = document.createElement('option');
                           option.value = topic.id;
                           option.textContent = topic.title;
                           optgroup.appendChild(option);
                       });
                       
                       topicSelect.appendChild(optgroup);
                   }
               });
           }
       }
   });
}

/**
* Get topic by ID
*/
function getTopic(topicId) {
   // Default value if topic not found
   const defaultTopic = { title: 'Unknown Topic' };
   
   // Try to find topic in curriculum data
   // Note: This relies on curriculum data being available in the page
   // If not available, we just return the default
   if (typeof curriculumData !== 'undefined') {
       for (const section of curriculumData.sections) {
           const topic = section.topics.find(t => t.id == topicId);
           if (topic) return topic;
       }
   }
   
   return defaultTopic;
}

/**
* Save resource changes to server
*/
function saveResourceToServer(resource) {
   const courseId = document.getElementById('course_id').value;
   if (!courseId) return;
   
   // Create form data
   const formData = {
       course_id: courseId,
       resource_id: resource.id,
       description: resource.description,
       is_downloadable: resource.is_downloadable ? 1 : 0,
       topic_id: resource.topic_id
   };
   
   // Send AJAX request
   $.ajax({
       url: 'ajax/update_resource.php',
       type: 'POST',
       data: formData,
       dataType: 'json',
       success: function(response) {
           if (!response.success) {
               console.error('Error updating resource:', response.message);
               alert('Error updating resource: ' + response.message);
           }
       },
       error: function() {
           console.error('Failed to update resource');
           alert('Failed to update resource. Please try again.');
       }
   });
}

/**
* Delete resource from server
*/
function deleteResourceFromServer(resourceId) {
   const courseId = document.getElementById('course_id').value;
   if (!courseId) return;
   
   // Create form data
   const formData = {
       course_id: courseId,
       resource_id: resourceId
   };
   
   // Send AJAX request
   $.ajax({
       url: 'ajax/delete_resource.php',
       type: 'POST',
       data: formData,
       dataType: 'json',
       success: function(response) {
           if (!response.success) {
               console.error('Error deleting resource:', response.message);
               alert('Error deleting resource: ' + response.message);
           }
       },
       error: function() {
           console.error('Failed to delete resource');
           alert('Failed to delete resource. Please try again.');
       }
   });
}

/**
* Get resource type based on file extension
*/
function getResourceType(filename) {
   const extension = filename.split('.').pop().toLowerCase();
   
   const typeMap = {
       'pdf': 'pdf',
       'doc': 'document',
       'docx': 'document',
       'xls': 'spreadsheet',
       'xlsx': 'spreadsheet',
       'ppt': 'presentation',
       'pptx': 'presentation',
       'jpg': 'image',
       'jpeg': 'image',
       'png': 'image',
       'gif': 'image',
       'zip': 'archive',
       'rar': 'archive',
       '7z': 'archive',
       'txt': 'text'
   };
   
   return typeMap[extension] || 'other';
}

/**
* Get resource icon class based on type
*/
function getResourceIcon(resourceType) {
   const iconMap = {
       'pdf': 'mdi-file-pdf-outline',
       'document': 'mdi-file-word-outline',
       'spreadsheet': 'mdi-file-excel-outline',
       'presentation': 'mdi-file-powerpoint-outline',
       'image': 'mdi-file-image-outline',
       'archive': 'mdi-zip-box-outline',
       'text': 'mdi-file-document-outline',
       'other': 'mdi-file-outline'
   };
   
   return iconMap[resourceType] || 'mdi-file-outline';
}

/**
* Get resource type label
*/
function getResourceTypeLabel(resourceType) {
   const labelMap = {
       'pdf': 'PDF',
       'document': 'Document',
       'spreadsheet': 'Spreadsheet',
       'presentation': 'Presentation',
       'image': 'Image',
       'archive': 'Archive',
       'text': 'Text File',
       'other': 'File'
   };
   
   return labelMap[resourceType] || 'File';
}

/**
* Get resource badge color
*/
function getResourceBadgeColor(resourceType) {
    const colorMap = {
       'pdf': 'danger',
       'document': 'primary',
       'spreadsheet': 'success',
       'presentation': 'warning',
       'image': 'info',
       'archive': 'dark',
       'text': 'secondary',
       'other': 'light'
   };
   
   return colorMap[resourceType] || 'light';
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
* Truncate string with ellipsis
*/
function truncateString(str, maxLength) {
   if (str.length <= maxLength) return str;
   return str.substring(0, maxLength - 3) + '...';
}
</script>