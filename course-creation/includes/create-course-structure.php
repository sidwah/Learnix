<?php
/**
 * Create Course - Course Structure
 * File: ../includes/create-course-structure.php
 * 
 * This file contains the interface for building the course curriculum:
 * - Creating and organizing sections
 * - Adding topics within sections
 * - Drag and drop reordering
 */
?>

<div class="course-structure-container">
    <h4 class="header-title mb-3">Course Structure</h4>
    <p class="text-muted">
        Organize your course content into sections and topics. Create a logical structure that guides 
        students through your curriculum. Drag and drop to reorder items.
    </p>

    <div class="row mt-4">
        <div class="col-12">
            <!-- Structure Builder Interface -->
            <div class="structure-builder card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Curriculum Builder</h5>
                        <button type="button" id="addSectionBtn" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-plus"></i> Add Section
                        </button>
                    </div>
                    
                    <!-- Empty state message -->
                    <div id="emptyCurriculum" class="text-center py-5 <?php echo isset($_GET['course_id']) ? 'd-none' : ''; ?>">
                        <div class="empty-state-icon mb-3">
                            <i class="mdi mdi-format-list-bulleted text-muted" style="font-size: 48px;"></i>
                        </div>
                        <h5>No Sections Added Yet</h5>
                        <p class="text-muted">
                            Start building your course by adding sections and topics.<br>
                            Click the "Add Section" button to get started.
                        </p>
                    </div>
                    
                    <!-- Sections container -->
                    <div id="sectionsContainer" class="mt-3">
                        <!-- Sections will be added here dynamically -->
                    </div>
                </div>
            </div>
            
            <!-- Structure Summary -->
            <div class="structure-summary card mt-3">
                <div class="card-body">
                    <h5 class="card-title">Curriculum Summary</h5>
                    <div class="row mt-3">
                        <div class="col-md-3 col-sm-6">
                            <div class="border rounded p-3 text-center mb-3">
                                <h3 id="sectionCount" class="mb-1">0</h3>
                                <p class="text-muted mb-0">Sections</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="border rounded p-3 text-center mb-3">
                                <h3 id="topicCount" class="mb-1">0</h3>
                                <p class="text-muted mb-0">Topics</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="border rounded p-3 text-center mb-3">
                                <h3 id="contentCount" class="mb-1">0</h3>
                                <p class="text-muted mb-0">Content Items</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="border rounded p-3 text-center mb-3">
                                <h3 id="estimatedDuration" class="mb-1">0h 0m</h3>
                                <p class="text-muted mb-0">Est. Duration</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Section Template (Hidden) -->
<template id="sectionTemplate">
    <div class="section-item card mb-3" data-section-id="{section_id}">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <span class="drag-handle me-2">
                        <i class="mdi mdi-drag-horizontal" style="cursor: move;"></i>
                    </span>
                    <h5 class="section-title mb-0">{section_title}</h5>
                </div>
                <div class="section-actions">
                    <button type="button" class="btn btn-sm btn-outline-primary add-topic-btn me-1" data-section-id="{section_id}">
                        <i class="mdi mdi-plus"></i> Add Topic
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary edit-section-btn me-1" data-section-id="{section_id}">
                        <i class="mdi mdi-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger delete-section-btn" data-section-id="{section_id}">
                        <i class="mdi mdi-delete"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="topics-container" data-section-id="{section_id}">
                <!-- Topics will be added here dynamically -->
                <div class="empty-topics text-center py-3">
                    <p class="text-muted mb-0">No topics added yet. Click "Add Topic" to add content.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Topic Template (Hidden) -->
<template id="topicTemplate">
    <div class="topic-item mb-2 border rounded p-3" data-topic-id="{topic_id}">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <span class="topic-drag-handle me-2">
                    <i class="mdi mdi-drag-vertical" style="cursor: move;"></i>
                </span>
                <div>
                    <h6 class="topic-title mb-0">{topic_title}</h6>
                    <small class="text-muted">{content_type}</small>
                </div>
            </div>
            <div class="topic-actions">
                <div class="form-check form-switch d-inline-block me-2">
                    <input class="form-check-input preview-toggle" type="checkbox" 
                           id="previewToggle{topic_id}" data-topic-id="{topic_id}">
                    <label class="form-check-label" for="previewToggle{topic_id}">Preview</label>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary edit-topic-btn me-1" data-topic-id="{topic_id}">
                    <i class="mdi mdi-pencil"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger delete-topic-btn" data-topic-id="{topic_id}">
                    <i class="mdi mdi-delete"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<!-- Add Section Modal -->
<div class="modal fade" id="addSectionModal" tabindex="-1" aria-labelledby="addSectionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSectionModalLabel">Add New Section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="sectionForm">
                    <input type="hidden" id="sectionId" name="sectionId" value="">
                    <input type="hidden" id="sectionAction" name="sectionAction" value="add">
                    
                    <div class="mb-3">
                        <label for="sectionTitle" class="form-label">Section Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="sectionTitle" name="sectionTitle" 
                               placeholder="e.g., Introduction, Advanced Techniques" required>
                        <div class="form-text">
                            Give your section a clear, descriptive title.
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveSectionBtn">Save Section</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Topic Modal -->
<div class="modal fade" id="addTopicModal" tabindex="-1" aria-labelledby="addTopicModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTopicModalLabel">Add New Topic</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="topicForm">
                    <input type="hidden" id="topicId" name="topicId" value="">
                    <input type="hidden" id="topicSectionId" name="topicSectionId" value="">
                    <input type="hidden" id="topicAction" name="topicAction" value="add">
                    
                    <div class="mb-3">
                        <label for="topicTitle" class="form-label">Topic Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="topicTitle" name="topicTitle" 
                               placeholder="e.g., Getting Started, Key Concepts" required>
                        <div class="form-text">
                            Give your topic a clear, descriptive title.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="isPreviewable" name="isPreviewable">
                            <label class="form-check-label" for="isPreviewable">Allow Free Preview</label>
                        </div>
                        <div class="form-text">
                            Enable this to make this topic available as a preview for non-enrolled students.
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveTopicBtn">Save Topic</button>
            </div>
        </div>
    </div>
</div>

<script>
// Section and topic counters
let sectionCounter = 0;
let topicCounter = 0;
let contentCounter = 0;
let totalDurationMinutes = 0;

// Store sections and topics in memory
let curriculumData = {
    sections: [],
    topics: []
};

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Sortable for sections container
    initializeSortable();
    
    // Set up event listeners
    setupEventListeners();
    
    // Load existing curriculum if editing a course
    loadExistingCurriculum();
});

/**
 * Initialize Sortable.js for drag-and-drop
 */
function initializeSortable() {
    // Make sections sortable
    new Sortable(document.getElementById('sectionsContainer'), {
        animation: 150,
        handle: '.drag-handle',
        ghostClass: 'section-item-ghost',
        onEnd: function() {
            updateSectionPositions();
        }
    });
    
    // Initialize topic sorting (will be called for each section's topics container)
    initializeTopicSorting();
}

/**
 * Initialize sorting for topics within a section
 */
function initializeTopicSorting(sectionId = null) {
   // If sectionId is provided, only initialize for that section
   let containers;
   
   if (sectionId) {
       containers = document.querySelectorAll(`.topics-container[data-section-id="${sectionId}"]`);
   } else {
       containers = document.querySelectorAll('.topics-container');
   }
   
   containers.forEach(container => {
       new Sortable(container, {
           animation: 150,
           handle: '.topic-drag-handle',
           ghostClass: 'topic-item-ghost',
           onEnd: function() {
               updateTopicPositions(container.getAttribute('data-section-id'));
           }
       });
   });
}

/**
* Set up event listeners for buttons and forms
*/
function setupEventListeners() {
   // Add Section button
   document.getElementById('addSectionBtn').addEventListener('click', function() {
       // Reset form
       document.getElementById('sectionForm').reset();
       document.getElementById('sectionId').value = '';
       document.getElementById('sectionAction').value = 'add';
       document.getElementById('addSectionModalLabel').textContent = 'Add New Section';
       
       // Show modal
       new bootstrap.Modal(document.getElementById('addSectionModal')).show();
   });
   
   // Save Section button
   document.getElementById('saveSectionBtn').addEventListener('click', function() {
       const form = document.getElementById('sectionForm');
       
       // Basic validation
       if (!form.checkValidity()) {
           form.classList.add('was-validated');
           return;
       }
       
       // Get form data
       const sectionId = document.getElementById('sectionId').value;
       const sectionTitle = document.getElementById('sectionTitle').value;
       const action = document.getElementById('sectionAction').value;
       
       // Handle add/edit actions
       if (action === 'add') {
           addSection(sectionTitle);
       } else {
           editSection(sectionId, sectionTitle);
       }
       
       // Hide modal
       bootstrap.Modal.getInstance(document.getElementById('addSectionModal')).hide();
   });
   
   // Save Topic button
   document.getElementById('saveTopicBtn').addEventListener('click', function() {
       const form = document.getElementById('topicForm');
       
       // Basic validation
       if (!form.checkValidity()) {
           form.classList.add('was-validated');
           return;
       }
       
       // Get form data
       const topicId = document.getElementById('topicId').value;
       const sectionId = document.getElementById('topicSectionId').value;
       const topicTitle = document.getElementById('topicTitle').value;
       const isPreviewable = document.getElementById('isPreviewable').checked;
       const action = document.getElementById('topicAction').value;
       
       // Handle add/edit actions
       if (action === 'add') {
           addTopic(sectionId, topicTitle, isPreviewable);
       } else {
           editTopic(topicId, topicTitle, isPreviewable);
       }
       
       // Hide modal
       bootstrap.Modal.getInstance(document.getElementById('addTopicModal')).hide();
   });
   
   // Delegate event listeners for dynamic elements
   document.addEventListener('click', function(event) {
       // Edit Section button
       if (event.target.classList.contains('edit-section-btn') || 
           event.target.closest('.edit-section-btn')) {
           const button = event.target.classList.contains('edit-section-btn') ? 
                          event.target : event.target.closest('.edit-section-btn');
           const sectionId = button.getAttribute('data-section-id');
           openEditSectionModal(sectionId);
       }
       
       // Delete Section button
       if (event.target.classList.contains('delete-section-btn') || 
           event.target.closest('.delete-section-btn')) {
           const button = event.target.classList.contains('delete-section-btn') ? 
                          event.target : event.target.closest('.delete-section-btn');
           const sectionId = button.getAttribute('data-section-id');
           deleteSection(sectionId);
       }
       
       // Add Topic button
       if (event.target.classList.contains('add-topic-btn') || 
           event.target.closest('.add-topic-btn')) {
           const button = event.target.classList.contains('add-topic-btn') ? 
                          event.target : event.target.closest('.add-topic-btn');
           const sectionId = button.getAttribute('data-section-id');
           openAddTopicModal(sectionId);
       }
       
       // Edit Topic button
       if (event.target.classList.contains('edit-topic-btn') || 
           event.target.closest('.edit-topic-btn')) {
           const button = event.target.classList.contains('edit-topic-btn') ? 
                          event.target : event.target.closest('.edit-topic-btn');
           const topicId = button.getAttribute('data-topic-id');
           openEditTopicModal(topicId);
       }
       
       // Delete Topic button
       if (event.target.classList.contains('delete-topic-btn') || 
           event.target.closest('.delete-topic-btn')) {
           const button = event.target.classList.contains('delete-topic-btn') ? 
                          event.target : event.target.closest('.delete-topic-btn');
           const topicId = button.getAttribute('data-topic-id');
           deleteTopic(topicId);
       }
   });
   
   // Listen for preview toggle changes
   document.addEventListener('change', function(event) {
       if (event.target.classList.contains('preview-toggle')) {
           const topicId = event.target.getAttribute('data-topic-id');
           const isEnabled = event.target.checked;
           updateTopicPreviewStatus(topicId, isEnabled);
       }
   });
}

/**
* Add a new section to the curriculum
*/
function addSection(title) {
   // Generate temporary ID for new section
   const tempId = 'new_' + Date.now();
   
   // Create section object
   const sectionData = {
       id: tempId,
       title: title,
       position: curriculumData.sections.length,
       topics: []
   };
   
   // Add to curriculum data
   curriculumData.sections.push(sectionData);
   
   // Add to UI
   renderSection(sectionData);
   
   // Update counters
   updateCurriculumSummary();
   
   // Hide empty state message if visible
   document.getElementById('emptyCurriculum').classList.add('d-none');
   
   // Save to server
   saveSectionToServer(sectionData);
}

/**
* Render a section in the UI
*/
function renderSection(sectionData) {
   const template = document.getElementById('sectionTemplate').innerHTML;
   const html = template
       .replace(/{section_id}/g, sectionData.id)
       .replace(/{section_title}/g, sectionData.title);
   
   // Create DOM element from HTML string
   const tempDiv = document.createElement('div');
   tempDiv.innerHTML = html;
   const sectionElement = tempDiv.firstElementChild;
   
   // Add to container
   document.getElementById('sectionsContainer').appendChild(sectionElement);
   
   // Initialize sorting for topics in this section
   initializeTopicSorting(sectionData.id);
   
   // Render topics if any
   const topicsContainer = sectionElement.querySelector(`.topics-container[data-section-id="${sectionData.id}"]`);
   if (sectionData.topics && sectionData.topics.length > 0) {
       // Clear empty message
       topicsContainer.querySelector('.empty-topics').classList.add('d-none');
       
       // Render each topic
       sectionData.topics.forEach(topic => {
           renderTopic(topic, topicsContainer);
       });
   }
}

/**
* Edit an existing section
*/
function editSection(sectionId, newTitle) {
   // Update in curriculum data
   const sectionIndex = curriculumData.sections.findIndex(s => s.id == sectionId);
   if (sectionIndex !== -1) {
       curriculumData.sections[sectionIndex].title = newTitle;
       
       // Update in UI
       const sectionElement = document.querySelector(`.section-item[data-section-id="${sectionId}"]`);
       if (sectionElement) {
           sectionElement.querySelector('.section-title').textContent = newTitle;
       }
       
       // Save changes to server
       updateSectionOnServer(curriculumData.sections[sectionIndex]);
   }
}

/**
* Delete a section
*/
function deleteSection(sectionId) {
   if (!confirm('Are you sure you want to delete this section and all its topics? This cannot be undone.')) {
       return;
   }
   
   // Remove from curriculum data
   const sectionIndex = curriculumData.sections.findIndex(s => s.id == sectionId);
   if (sectionIndex !== -1) {
       // Remember topics to update content counter
       const topicsCount = curriculumData.sections[sectionIndex].topics.length;
       
       // Remove section from array
       curriculumData.sections.splice(sectionIndex, 1);
       
       // Update topic counter
       topicCounter -= topicsCount;
       
       // Update UI
       const sectionElement = document.querySelector(`.section-item[data-section-id="${sectionId}"]`);
       if (sectionElement) {
           sectionElement.remove();
       }
       
       // Update positions for remaining sections
       updateSectionPositions();
       
       // Update summary
       updateCurriculumSummary();
       
       // Show empty state if no sections left
       if (curriculumData.sections.length === 0) {
           document.getElementById('emptyCurriculum').classList.remove('d-none');
       }
       
       // Delete from server
       deleteSectionFromServer(sectionId);
   }
}

/**
* Open modal to add a new topic
*/
function openAddTopicModal(sectionId) {
   // Reset form
   document.getElementById('topicForm').reset();
   document.getElementById('topicId').value = '';
   document.getElementById('topicSectionId').value = sectionId;
   document.getElementById('topicAction').value = 'add';
   document.getElementById('addTopicModalLabel').textContent = 'Add New Topic';
   
   // Show modal
   new bootstrap.Modal(document.getElementById('addTopicModal')).show();
}

/**
* Add a new topic to a section
*/
function addTopic(sectionId, title, isPreviewable) {
   // Find section in curriculum data
   const sectionIndex = curriculumData.sections.findIndex(s => s.id == sectionId);
   if (sectionIndex === -1) return;
   
   // Generate temporary ID for new topic
   const tempId = 'new_topic_' + Date.now();
   
   // Create topic object
   const topicData = {
       id: tempId,
       section_id: sectionId,
       title: title,
       is_previewable: isPreviewable,
       content_type: 'Not set',
       position: curriculumData.sections[sectionIndex].topics.length
   };
   
   // Add to curriculum data
   curriculumData.sections[sectionIndex].topics.push(topicData);
   
   // Find topics container in UI
   const topicsContainer = document.querySelector(`.topics-container[data-section-id="${sectionId}"]`);
   if (topicsContainer) {
       // Hide empty message if visible
       const emptyMessage = topicsContainer.querySelector('.empty-topics');
       if (emptyMessage) {
           emptyMessage.classList.add('d-none');
       }
       
       // Render topic
       renderTopic(topicData, topicsContainer);
   }
   
   // Update counters
   topicCounter++;
   updateCurriculumSummary();
   
   // Save to server
   saveTopicToServer(topicData);
}

/**
* Render a topic in the UI
*/
function renderTopic(topicData, container) {
   const template = document.getElementById('topicTemplate').innerHTML;
   const html = template
       .replace(/{topic_id}/g, topicData.id)
       .replace(/{topic_title}/g, topicData.title)
       .replace(/{content_type}/g, topicData.content_type || 'Not set');
   
   // Create DOM element from HTML string
   const tempDiv = document.createElement('div');
   tempDiv.innerHTML = html;
   const topicElement = tempDiv.firstElementChild;
   
   // Set preview toggle status
   if (topicData.is_previewable) {
       const toggleElement = topicElement.querySelector(`.preview-toggle[data-topic-id="${topicData.id}"]`);
       if (toggleElement) toggleElement.checked = true;
   }
   
   // Add to container
   container.appendChild(topicElement);
}

/**
* Open modal to edit an existing topic
*/
function openEditTopicModal(topicId) {
   // Find topic in curriculum data
   let topicData = null;
   for (const section of curriculumData.sections) {
       const topic = section.topics.find(t => t.id == topicId);
       if (topic) {
           topicData = topic;
           break;
       }
   }
   
   if (!topicData) return;
   
   // Populate form
   document.getElementById('topicId').value = topicId;
   document.getElementById('topicSectionId').value = topicData.section_id;
   document.getElementById('topicTitle').value = topicData.title;
   document.getElementById('isPreviewable').checked = topicData.is_previewable;
   document.getElementById('topicAction').value = 'edit';
   document.getElementById('addTopicModalLabel').textContent = 'Edit Topic';
   
   // Show modal
   new bootstrap.Modal(document.getElementById('addTopicModal')).show();
}

/**
* Edit an existing topic
*/
function editTopic(topicId, newTitle, isPreviewable) {
   // Find topic in curriculum data
   let topicUpdated = false;
   for (const section of curriculumData.sections) {
       const topicIndex = section.topics.findIndex(t => t.id == topicId);
       if (topicIndex !== -1) {
           // Update topic data
           section.topics[topicIndex].title = newTitle;
           section.topics[topicIndex].is_previewable = isPreviewable;
           
           // Update in UI
           const topicElement = document.querySelector(`.topic-item[data-topic-id="${topicId}"]`);
           if (topicElement) {
               topicElement.querySelector('.topic-title').textContent = newTitle;
               topicElement.querySelector(`.preview-toggle[data-topic-id="${topicId}"]`).checked = isPreviewable;
           }
           
           // Save changes to server
           updateTopicOnServer(section.topics[topicIndex]);
           
           topicUpdated = true;
           break;
       }
   }
   
   return topicUpdated;
}

/**
* Delete a topic
*/
function deleteTopic(topicId) {
   if (!confirm('Are you sure you want to delete this topic and all its content? This cannot be undone.')) {
       return;
   }
   
   // Find topic in curriculum data
   let topicDeleted = false;
   for (const section of curriculumData.sections) {
       const topicIndex = section.topics.findIndex(t => t.id == topicId);
       if (topicIndex !== -1) {
           // Remove topic from array
           section.topics.splice(topicIndex, 1);
           
           // Update UI
        //    const topicElement = document.querySelector(`.topic-item
           // Update UI
           const topicElement = document.querySelector(`.topic-item[data-topic-id="${topicId}"]`);
           if (topicElement) {
               topicElement.remove();
           }
           
           // Show empty message if no topics left
           if (section.topics.length === 0) {
               const topicsContainer = document.querySelector(`.topics-container[data-section-id="${section.id}"]`);
               if (topicsContainer) {
                   const emptyMessage = topicsContainer.querySelector('.empty-topics');
                   if (emptyMessage) {
                       emptyMessage.classList.remove('d-none');
                   }
               }
           }
           
           // Update positions for remaining topics
           updateTopicPositions(section.id);
           
           // Update counters
           topicCounter--;
           updateCurriculumSummary();
           
           // Delete from server
           deleteTopicFromServer(topicId);
           
           topicDeleted = true;
           break;
       }
   }
   
   return topicDeleted;
}

/**
* Update the preview status of a topic
*/
function updateTopicPreviewStatus(topicId, isEnabled) {
   // Find topic in curriculum data
   for (const section of curriculumData.sections) {
       const topicIndex = section.topics.findIndex(t => t.id == topicId);
       if (topicIndex !== -1) {
           // Update preview status
           section.topics[topicIndex].is_previewable = isEnabled;
           
           // Save changes to server
           updateTopicOnServer(section.topics[topicIndex]);
           break;
       }
   }
}

/**
* Update positions of sections after drag and drop
*/
function updateSectionPositions() {
   // Get all section elements in their current order
   const sectionElements = document.querySelectorAll('.section-item');
   
   // Create a map of current positions
   const newPositions = {};
   sectionElements.forEach((element, index) => {
       const sectionId = element.getAttribute('data-section-id');
       newPositions[sectionId] = index;
   });
   
   // Update positions in curriculum data
   curriculumData.sections.forEach(section => {
       if (newPositions.hasOwnProperty(section.id)) {
           section.position = newPositions[section.id];
       }
   });
   
   // Sort sections array by position
   curriculumData.sections.sort((a, b) => a.position - b.position);
   
   // Save updated positions to server
   saveSectionPositionsToServer();
}

/**
* Update positions of topics after drag and drop
*/
function updateTopicPositions(sectionId) {
   // Find section in curriculum data
   const sectionIndex = curriculumData.sections.findIndex(s => s.id == sectionId);
   if (sectionIndex === -1) return;
   
   // Get all topic elements in their current order
   const topicElements = document.querySelectorAll(`.topics-container[data-section-id="${sectionId}"] .topic-item`);
   
   // Create a map of current positions
   const newPositions = {};
   topicElements.forEach((element, index) => {
       const topicId = element.getAttribute('data-topic-id');
       newPositions[topicId] = index;
   });
   
   // Update positions in curriculum data
   curriculumData.sections[sectionIndex].topics.forEach(topic => {
       if (newPositions.hasOwnProperty(topic.id)) {
           topic.position = newPositions[topic.id];
       }
   });
   
   // Sort topics array by position
   curriculumData.sections[sectionIndex].topics.sort((a, b) => a.position - b.position);
   
   // Save updated positions to server
   saveTopicPositionsToServer(sectionId);
}

/**
* Open the edit section modal
*/
function openEditSectionModal(sectionId) {
   // Find section in curriculum data
   const sectionIndex = curriculumData.sections.findIndex(s => s.id == sectionId);
   if (sectionIndex === -1) return;
   
   // Populate form
   document.getElementById('sectionId').value = sectionId;
   document.getElementById('sectionTitle').value = curriculumData.sections[sectionIndex].title;
   document.getElementById('sectionAction').value = 'edit';
   document.getElementById('addSectionModalLabel').textContent = 'Edit Section';
   
   // Show modal
   new bootstrap.Modal(document.getElementById('addSectionModal')).show();
}

/**
* Update curriculum summary counters
*/
function updateCurriculumSummary() {
   document.getElementById('sectionCount').textContent = curriculumData.sections.length;
   document.getElementById('topicCount').textContent = topicCounter;
   document.getElementById('contentCount').textContent = contentCounter;
   
   // Convert minutes to hours and minutes for display
   const hours = Math.floor(totalDurationMinutes / 60);
   const minutes = totalDurationMinutes % 60;
   document.getElementById('estimatedDuration').textContent = `${hours}h ${minutes}m`;
}

/**
* Load existing curriculum if editing a course
*/
function loadExistingCurriculum() {
   const courseId = document.getElementById('course_id').value;
   if (!courseId) return;
   
   // Show loading state
   const sectionsContainer = document.getElementById('sectionsContainer');
   sectionsContainer.innerHTML = `
       <div class="text-center py-4">
           <div class="spinner-border text-primary" role="status">
               <span class="visually-hidden">Loading...</span>
           </div>
           <p class="mt-2">Loading course curriculum...</p>
       </div>
   `;
   
   // Fetch curriculum data via AJAX
   $.ajax({
       url: 'ajax/get_course_curriculum.php',
       type: 'GET',
       data: { course_id: courseId },
       dataType: 'json',
       success: function(response) {
           // Clear loading indicator
           sectionsContainer.innerHTML = '';
           
           if (response.success) {
               if (response.curriculum && response.curriculum.sections) {
                   // Reset counters
                   sectionCounter = 0;
                   topicCounter = 0;
                   contentCounter = 0;
                   totalDurationMinutes = 0;
                   
                   // Store curriculum data
                   curriculumData = response.curriculum;
                   
                   // Sort sections by position
                   curriculumData.sections.sort((a, b) => a.position - b.position);
                   
                   // Calculate counters
                   sectionCounter = curriculumData.sections.length;
                   
                   curriculumData.sections.forEach(section => {
                       // Sort topics by position
                       section.topics.sort((a, b) => a.position - b.position);
                       
                       // Count topics
                       topicCounter += section.topics.length;
                       
                       // Count content items and duration
                       section.topics.forEach(topic => {
                           if (topic.content_items) {
                               contentCounter += topic.content_items.length;
                               
                               // Calculate estimated duration
                               topic.content_items.forEach(item => {
                                   if (item.duration_minutes) {
                                       totalDurationMinutes += parseInt(item.duration_minutes);
                                   }
                               });
                           }
                       });
                   });
                   
                   // Update summary
                   updateCurriculumSummary();
                   
                   // Render sections and topics
                   if (curriculumData.sections.length > 0) {
                       // Hide empty state
                       document.getElementById('emptyCurriculum').classList.add('d-none');
                       
                       // Render each section
                       curriculumData.sections.forEach(section => {
                           renderSection(section);
                       });
                   }
               } else {
                   // Show empty state
                   document.getElementById('emptyCurriculum').classList.remove('d-none');
               }
           } else {
               console.error('Error loading curriculum:', response.message);
               alert('Error loading curriculum: ' + response.message);
               
               // Show empty state
               document.getElementById('emptyCurriculum').classList.remove('d-none');
           }
       },
       error: function() {
           // Clear loading indicator
           sectionsContainer.innerHTML = '';
           
           console.error('Failed to load curriculum');
           alert('Failed to load curriculum. Please refresh the page and try again.');
           
           // Show empty state
           document.getElementById('emptyCurriculum').classList.remove('d-none');
       }
   });
}

/**
* Save a new section to the server
*/
function saveSectionToServer(sectionData) {
   const courseId = document.getElementById('course_id').value;
   if (!courseId) return;
   
   // Create form data
   const formData = {
       course_id: courseId,
       title: sectionData.title,
       position: sectionData.position,
       temp_id: sectionData.id
   };
   
   // Send AJAX request
   $.ajax({
       url: 'ajax/save_course_section.php',
       type: 'POST',
       data: formData,
       dataType: 'json',
       success: function(response) {
           if (response.success) {
               // Update section ID in curriculum data
               const sectionIndex = curriculumData.sections.findIndex(s => s.id === sectionData.id);
               if (sectionIndex !== -1) {
                   // Store real ID from server
                   const oldId = curriculumData.sections[sectionIndex].id;
                   const newId = response.section_id;
                   
                   // Update ID in data
                   curriculumData.sections[sectionIndex].id = newId;
                   
                   // Update ID in DOM
                   const sectionElement = document.querySelector(`.section-item[data-section-id="${oldId}"]`);
                   if (sectionElement) {
                       // Update main element
                       sectionElement.setAttribute('data-section-id', newId);
                       
                       // Update child elements
                       const childElements = sectionElement.querySelectorAll(`[data-section-id="${oldId}"]`);
                       childElements.forEach(element => {
                           element.setAttribute('data-section-id', newId);
                       });
                       
                       // Update buttons
                       const buttons = sectionElement.querySelectorAll(`button[data-section-id="${oldId}"]`);
                       buttons.forEach(button => {
                           button.setAttribute('data-section-id', newId);
                       });
                   }
               }
           } else {
               console.error('Error saving section:', response.message);
               alert('Error saving section: ' + response.message);
           }
       },
       error: function() {
           console.error('Failed to save section');
           alert('Failed to save section. Please try again.');
       }
   });
}

/**
* Update an existing section on the server
*/
function updateSectionOnServer(sectionData) {
   const courseId = document.getElementById('course_id').value;
   if (!courseId) return;
   
   // Create form data
   const formData = {
       course_id: courseId,
       section_id: sectionData.id,
       title: sectionData.title,
       position: sectionData.position
   };
   
   // Send AJAX request
   $.ajax({
       url: 'ajax/update_course_section.php',
       type: 'POST',
       data: formData,
       dataType: 'json',
       success: function(response) {
           if (!response.success) {
               console.error('Error updating section:', response.message);
               alert('Error updating section: ' + response.message);
           }
       },
       error: function() {
           console.error('Failed to update section');
           alert('Failed to update section. Please try again.');
       }
   });
}

/**
* Delete a section from the server
*/
function deleteSectionFromServer(sectionId) {
   const courseId = document.getElementById('course_id').value;
   if (!courseId) return;
   
   // Create form data
   const formData = {
       course_id: courseId,
       section_id: sectionId
   };
   
   // Send AJAX request
   $.ajax({
       url: 'ajax/delete_course_section.php',
       type: 'POST',
       data: formData,
       dataType: 'json',
       success: function(response) {
           if (!response.success) {
               console.error('Error deleting section:', response.message);
               alert('Error deleting section: ' + response.message);
           }
       },
       error: function() {
           console.error('Failed to delete section');
           alert('Failed to delete section. Please try again.');
       }
   });
}

/**
* Save section positions to the server
*/
function saveSectionPositionsToServer() {
   const courseId = document.getElementById('course_id').value;
   if (!courseId) return;
   
   // Create positions data
   const positions = curriculumData.sections.map(section => ({
       id: section.id,
       position: section.position
   }));
   
   // Send AJAX request
   $.ajax({
       url: 'ajax/update_section_positions.php',
       type: 'POST',
       data: {
           course_id: courseId,
           positions: JSON.stringify(positions)
       },
       dataType: 'json',
       success: function(response) {
           if (!response.success) {
               console.error('Error updating section positions:', response.message);
               // Don't show alert for this as it's not critical
           }
       },
       error: function() {
           console.error('Failed to update section positions');
           // Don't show alert for this as it's not critical
       }
   });
}

/**
* Save a new topic to the server
*/
function saveTopicToServer(topicData) {
   const courseId = document.getElementById('course_id').value;
   if (!courseId) return;
   
   // Create form data
   const formData = {
       course_id: courseId,
       section_id: topicData.section_id,
       title: topicData.title,
       position: topicData.position,
       is_previewable: topicData.is_previewable ? 1 : 0,
       temp_id: topicData.id
   };
   
   // Send AJAX request
   $.ajax({
       url: 'ajax/save_course_topic.php',
       type: 'POST',
       data: formData,
       dataType: 'json',
       success: function(response) {
           if (response.success) {
               // Find topic in curriculum data
               for (const section of curriculumData.sections) {
                   if (section.id == topicData.section_id) {
                       const topicIndex = section.topics.findIndex(t => t.id === topicData.id);
                       if (topicIndex !== -1) {
                           // Store real ID from server
                           const oldId = section.topics[topicIndex].id;
                           const newId = response.topic_id;
                           
                           // Update ID in data
                           section.topics[topicIndex].id = newId;
                           
                           // Update ID in DOM
                           const topicElement = document.querySelector(`.topic-item[data-topic-id="${oldId}"]`);
                           if (topicElement) {
                               // Update main element
                               topicElement.setAttribute('data-topic-id', newId);
                               
                               // Update child elements
                               const childElements = topicElement.querySelectorAll(`[data-topic-id="${oldId}"]`);
                            //    childElements.forEach(element => {
                            //        element.setAttribute('
                                   childElements.forEach(element => {
                                   element.setAttribute('data-topic-id', newId);
                               });
                               
                               // Update buttons
                               const buttons = topicElement.querySelectorAll(`button[data-topic-id="${oldId}"]`);
                               buttons.forEach(button => {
                                   button.setAttribute('data-topic-id', newId);
                               });
                               
                               // Update toggle ID
                               const toggle = topicElement.querySelector(`#previewToggle${oldId}`);
                               if (toggle) {
                                   toggle.id = `previewToggle${newId}`;
                                   const label = topicElement.querySelector(`label[for="previewToggle${oldId}"]`);
                                   if (label) {
                                       label.setAttribute('for', `previewToggle${newId}`);
                                   }
                               }
                           }
                       }
                       break;
                   }
               }
           } else {
               console.error('Error saving topic:', response.message);
               alert('Error saving topic: ' + response.message);
           }
       },
       error: function() {
           console.error('Failed to save topic');
           alert('Failed to save topic. Please try again.');
       }
   });
}

/**
* Update an existing topic on the server
*/
function updateTopicOnServer(topicData) {
   const courseId = document.getElementById('course_id').value;
   if (!courseId) return;
   
   // Create form data
   const formData = {
       course_id: courseId,
       topic_id: topicData.id,
       title: topicData.title,
       position: topicData.position,
       is_previewable: topicData.is_previewable ? 1 : 0
   };
   
   // Send AJAX request
   $.ajax({
       url: 'ajax/update_course_topic.php',
       type: 'POST',
       data: formData,
       dataType: 'json',
       success: function(response) {
           if (!response.success) {
               console.error('Error updating topic:', response.message);
               alert('Error updating topic: ' + response.message);
           }
       },
       error: function() {
           console.error('Failed to update topic');
           alert('Failed to update topic. Please try again.');
       }
   });
}

/**
* Delete a topic from the server
*/
function deleteTopicFromServer(topicId) {
   const courseId = document.getElementById('course_id').value;
   if (!courseId) return;
   
   // Create form data
   const formData = {
       course_id: courseId,
       topic_id: topicId
   };
   
   // Send AJAX request
   $.ajax({
       url: 'ajax/delete_course_topic.php',
       type: 'POST',
       data: formData,
       dataType: 'json',
       success: function(response) {
           if (!response.success) {
               console.error('Error deleting topic:', response.message);
               alert('Error deleting topic: ' + response.message);
           }
       },
       error: function() {
           console.error('Failed to delete topic');
           alert('Failed to delete topic. Please try again.');
       }
   });
}

/**
* Save topic positions to the server
*/
function saveTopicPositionsToServer(sectionId) {
   const courseId = document.getElementById('course_id').value;
   if (!courseId) return;
   
   // Find section in curriculum data
   const sectionIndex = curriculumData.sections.findIndex(s => s.id == sectionId);
   if (sectionIndex === -1) return;
   
   // Create positions data
   const positions = curriculumData.sections[sectionIndex].topics.map(topic => ({
       id: topic.id,
       position: topic.position
   }));
   
   // Send AJAX request
   $.ajax({
       url: 'ajax/update_topic_positions.php',
       type: 'POST',
       data: {
           course_id: courseId,
           section_id: sectionId,
           positions: JSON.stringify(positions)
       },
       dataType: 'json',
       success: function(response) {
           if (!response.success) {
               console.error('Error updating topic positions:', response.message);
               // Don't show alert for this as it's not critical
           }
       },
       error: function() {
           console.error('Failed to update topic positions');
           // Don't show alert for this as it's not critical
       }
   });
}
</script>