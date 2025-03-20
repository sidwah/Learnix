<?php 
/**
 * Enhanced Course Structure Tab for Course Creation
 * File: ../includes/create-course-structure.php
 * 
 * Improvements:
 * - Consistent ID naming with step-2-structure
 * - Enhanced section management with drag-and-drop
 * - Visual indication of section order
 * - Improved validation and error handling
 * - Better user experience with visual feedback
 */
?>
<div class="card">
    <div class="card-header bg-light">
        <h5 class="mb-0">Course Structure</h5>
    </div>
    <div class="card-body">
        <p class="text-muted">Create sections to organize your course content. Each section represents a major topic or module in your course.</p>
        
        <!-- Course Sections Builder -->
        <div class="row mb-4">
            <div class="col-12">
                <label class="form-label"><strong>Course Sections</strong> <span class="text-danger">*</span></label>
                <div id="sectionsContainer" class="section-container mb-3">
                    <!-- Sections will be added here dynamically -->
                    <div class="section-item card" data-position="1">
                        <div class="card-body">
                            <div class="section-header d-flex align-items-center">
                                <div class="section-drag me-2">
                                    <i class="mdi mdi-drag-horizontal handle" title="Drag to reorder"></i>
                                </div>
                                <div class="section-number me-2">1</div>
                                <div class="section-input flex-grow-1">
                                    <input type="text" class="form-control section-title" name="sections[]"
                                        placeholder="Enter section title (e.g., Introduction to the Course)" required>
                                    <div class="invalid-feedback">Section title is required</div>
                                </div>
                                <div class="section-actions ms-2">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-section" title="Remove section">
                                        <i class="mdi mdi-trash-can"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="section_positions[]" value="1" class="section-position">
                        <input type="hidden" name="section_ids[]" value="" class="section-id">
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <button type="button" id="addSectionBtn" class="btn btn-primary">
                        <i class="mdi mdi-plus me-1"></i> Add Section
                    </button>
                    <div class="text-muted">
                        <small><i class="mdi mdi-information-outline me-1"></i> Drag sections to reorder</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Instructions for Instructors -->
        <div class="alert alert-info" role="alert">
            <h5 class="d-flex align-items-center">
                <i class="mdi mdi-lightbulb me-2"></i>
                <strong>Tips for Creating an Effective Course Structure</strong>
            </h5>
            <ul class="mb-0">
                <li><strong>Be Descriptive:</strong> Use clear, descriptive section titles that tell students what they'll learn</li>
                <li><strong>Logical Order:</strong> Arrange sections in a logical sequence, with each building on previous knowledge</li>
                <li><strong>Consistency:</strong> Try to maintain similar scope and size for each section</li>
                <li><strong>Manageable Chunks:</strong> Aim for 4-10 sections for most courses to keep content organized</li>
                <li><strong>Preview Future Steps:</strong> In the next steps, you'll add content and resources to each section</li>
            </ul>
        </div>

        <!-- Hidden field for section IDs -->
        <input type="hidden" id="section_ids" name="section_ids" value="">
    </div>
</div>

<!-- Enhanced styles for sections -->
<style>
.section-container {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.section-item {
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    transition: all 0.2s ease;
    position: relative;
}

.section-item:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.section-header {
    width: 100%;
}

.section-drag {
    cursor: grab;
    color: #6c757d;
    font-size: 18px;
}

.section-drag:hover {
    color: #495057;
}

.section-number {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    background-color: #f0f0f0;
    color: #495057;
    border-radius: 15px;
    font-weight: 600;
    font-size: 14px;
}

.section-item.ui-sortable-helper {
    box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
    border: 1px solid #007bff;
    z-index: 1000;
}

.section-item.ui-sortable-placeholder {
    visibility: visible !important;
    border: 2px dashed #007bff;
    background-color: rgba(0, 123, 255, 0.05);
    box-shadow: none;
}

.sortable-ghost {
    opacity: 0.5;
}

.section-item.highlight {
    border-color: #28a745;
    animation: highlight-pulse 1s ease;
}

@keyframes highlight-pulse {
    0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
    70% { box-shadow: 0 0 0 6px rgba(40, 167, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
}

.section-item.error {
    border-color: #dc3545;
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 30px;
    background-color: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 6px;
}

.empty-state i {
    font-size: 40px;
    color: #adb5bd;
    margin-bottom: 15px;
}
</style>

<!-- Enhanced JavaScript for Course Structure Tab -->
<script>
/**
 * Enhanced Course Structure Management
 * 
 * Features:
 * - Drag and drop section reordering
 * - Better validation and error handling
 * - Visual feedback for user actions
 * - Improved section management
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the section builder
    initSectionBuilder();
    
    // Check if editing an existing course
    const courseId = document.getElementById('course_id')?.value;
    if (courseId) {
        loadExistingSections(courseId);
    }
    
    // Setup validation for the structure form
    setupStructureValidation();
});

/**
 * Initialize the section builder
 */
function initSectionBuilder() {
    // Add section button handler
    const addSectionBtn = document.getElementById('addSectionBtn');
    if (addSectionBtn) {
        addSectionBtn.addEventListener('click', addSection);
    }
    
    // Section removal handler (using event delegation)
    const sectionsContainer = document.getElementById('sectionsContainer');
    if (sectionsContainer) {
        sectionsContainer.addEventListener('click', function(e) {
            // Handle remove button clicks
            if (e.target.closest('.remove-section')) {
                const sectionItem = e.target.closest('.section-item');
                if (sectionItem) {
                    removeSection(sectionItem);
                }
            }
        });
        
        // Initialize sorting if jQuery UI is available
        if (typeof $ !== 'undefined' && $.fn.sortable) {
            $('#sectionsContainer').sortable({
                handle: '.handle',
                axis: 'y',
                placeholder: 'section-item ui-sortable-placeholder',
                start: function(e, ui) {
                    ui.item.addClass('ui-sortable-helper');
                },
                stop: function(e, ui) {
                    ui.item.removeClass('ui-sortable-helper');
                    updateSectionNumbers();
                }
            });
        } else {
            // Fallback for when jQuery UI is not available
            console.log('jQuery UI not available for drag-and-drop. Using basic reordering.');
            
            // Add move up/down buttons to each section
            document.querySelectorAll('.section-item').forEach(addMoveButtons);
        }
    }
    
    // Initialize validation for section inputs
    document.querySelectorAll('.section-title').forEach(input => {
        input.addEventListener('blur', function() {
            validateSectionTitle(this);
        });
        
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
            const sectionItem = this.closest('.section-item');
            if (sectionItem) {
                sectionItem.classList.remove('error');
            }
        });
    });
    
    // Update section numbers on initial load
    updateSectionNumbers();
}

/**
 * Add move up/down buttons to a section (fallback for jQuery UI)
 */
function addMoveButtons(section) {
    const actionsDiv = section.querySelector('.section-actions');
    if (!actionsDiv) return;
    
    // Create move up button
    const moveUpBtn = document.createElement('button');
    moveUpBtn.type = 'button';
    moveUpBtn.className = 'btn btn-sm btn-outline-secondary ms-2 move-up';
    moveUpBtn.innerHTML = '<i class="mdi mdi-arrow-up"></i>';
    moveUpBtn.title = 'Move section up';
    moveUpBtn.addEventListener('click', function() {
        moveSectionUp(section);
    });
    
    // Create move down button
    const moveDownBtn = document.createElement('button');
    moveDownBtn.type = 'button';
    moveDownBtn.className = 'btn btn-sm btn-outline-secondary ms-2 move-down';
    moveDownBtn.innerHTML = '<i class="mdi mdi-arrow-down"></i>';
    moveDownBtn.title = 'Move section down';
    moveDownBtn.addEventListener('click', function() {
        moveSectionDown(section);
    });
    
    // Add buttons to the actions div
    actionsDiv.appendChild(moveUpBtn);
    actionsDiv.appendChild(moveDownBtn);
}

/**
 * Move a section up in the order
 */
function moveSectionUp(section) {
    const prev = section.previousElementSibling;
    if (prev) {
        section.parentNode.insertBefore(section, prev);
        updateSectionNumbers();
        
        // Highlight the moved section
        section.classList.add('highlight');
        setTimeout(() => {
            section.classList.remove('highlight');
        }, 1000);
    }
}

/**
 * Move a section down in the order
 */
function moveSectionDown(section) {
    const next = section.nextElementSibling;
    if (next) {
        section.parentNode.insertBefore(next, section);
        updateSectionNumbers();
        
        // Highlight the moved section
        section.classList.add('highlight');
        setTimeout(() => {
            section.classList.remove('highlight');
        }, 1000);
    }
}

/**
 * Add a new section
 */
function addSection() {
    const sectionsContainer = document.getElementById('sectionsContainer');
    if (!sectionsContainer) return;
    
    const sections = sectionsContainer.querySelectorAll('.section-item');
    const newPosition = sections.length + 1;
    
    // Create new section
    const newSection = document.createElement('div');
    newSection.className = 'section-item card';
    newSection.dataset.position = newPosition;
    
    // Generate unique temp ID for new section
    const tempId = 'new_' + Date.now();
    
    newSection.innerHTML = `
        <div class="card-body">
            <div class="section-header d-flex align-items-center">
                <div class="section-drag me-2">
                    <i class="mdi mdi-drag-horizontal handle" title="Drag to reorder"></i>
                </div>
                <div class="section-number me-2">${newPosition}</div>
                <div class="section-input flex-grow-1">
                    <input type="text" class="form-control section-title" name="sections[]"
                        placeholder="Enter section title" required>
                    <div class="invalid-feedback">Section title is required</div>
                </div>
                <div class="section-actions ms-2">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-section" title="Remove section">
                        <i class="mdi mdi-trash-can"></i>
                    </button>
                </div>
            </div>
        </div>
        <input type="hidden" name="section_positions[]" value="${newPosition}" class="section-position">
        <input type="hidden" name="section_ids[]" value="${tempId}" class="section-id">
    `;
    
    // Add to container
    sectionsContainer.appendChild(newSection);
    
    // Add fallback move buttons if jQuery UI not available
    if (!(typeof $ !== 'undefined' && $.fn.sortable)) {
        addMoveButtons(newSection);
    }
    
    // Setup validation for the new input
    const newInput = newSection.querySelector('.section-title');
    if (newInput) {
        newInput.addEventListener('blur', function() {
            validateSectionTitle(this);
        });
        
        newInput.addEventListener('input', function() {
            this.classList.remove('is-invalid');
            newSection.classList.remove('error');
        });
        
        // Focus the new input
        newInput.focus();
    }
    
    // Update section numbers
    updateSectionNumbers();
    
    // Highlight the new section
    newSection.classList.add('highlight');
    setTimeout(() => {
        newSection.classList.remove('highlight');
    }, 1500);
    
    // Scroll to the new section if needed
    newSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
    
    return newSection;
}

/**
 * Remove a section
 */
function removeSection(section) {
    const sectionsContainer = document.getElementById('sectionsContainer');
    if (!sectionsContainer) return;
    
    const sections = sectionsContainer.querySelectorAll('.section-item');
    
    // Don't allow removing if it's the only section
    if (sections.length <= 1) {
        if (typeof showAlert === 'function') {
            showAlert('warning', 'You need at least one section in your course.');
        } else {
            alert('You need at least one section in your course.');
        }
        return;
    }
    
    // Confirm deletion
    if (confirm('Are you sure you want to remove this section? This action cannot be undone.')) {
        // Create fade-out effect
        section.style.transition = 'all 0.3s ease';
        section.style.opacity = '0';
        section.style.height = '0';
        section.style.margin = '0';
        section.style.padding = '0';
        section.style.overflow = 'hidden';
        
        // Remove after animation
        setTimeout(() => {
            section.remove();
            updateSectionNumbers();
        }, 300);
    }
}

/**
 * Update section numbers based on their order
 */
function updateSectionNumbers() {
    const sections = document.querySelectorAll('.section-item');
    
    sections.forEach((section, index) => {
        // Update position
        const position = index + 1;
        section.dataset.position = position;
        
        // Update position hidden input
        const positionInput = section.querySelector('.section-position');
        if (positionInput) {
            positionInput.value = position;
        }
        
        // Update number display
        const numberDisplay = section.querySelector('.section-number');
        if (numberDisplay) {
            numberDisplay.textContent = position;
        }
    });
}

/**
 * Validate a section title input
 */
function validateSectionTitle(input) {
    const sectionItem = input.closest('.section-item');
    
    if (!input.value.trim()) {
        input.classList.add('is-invalid');
        if (sectionItem) {
            sectionItem.classList.add('error');
        }
        return false;
    } else {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        if (sectionItem) {
            sectionItem.classList.remove('error');
        }
        return true;
    }
}

/**
 * Setup validation for the structure form
 */
function setupStructureValidation() {
    // Add validation for all section titles
    document.querySelectorAll('.section-title').forEach(input => {
        input.addEventListener('blur', function() {
            validateSectionTitle(this);
        });
    });
}

/**
 * Load existing sections when editing a course
 */
function loadExistingSections(courseId) {
    // Don't load if no course ID
    if (!courseId) return;
    
    // Show loading state
    if (typeof createOverlay === 'function') {
        createOverlay("Loading course sections...");
    }
    
    fetch(`../backend/courses/get_course_sections.php?course_id=${courseId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const sections = data.sections;
                const sectionIds = {};
                
                // Clear existing sections
                const sectionsContainer = document.getElementById('sectionsContainer');
                if (sectionsContainer) {
                    sectionsContainer.innerHTML = '';
                }
                
                if (sections.length === 0) {
                    // No sections found, create default section
                    addSection();
                } else {
                    // Add each existing section
                    sections.forEach((section, index) => {
                        const newSection = document.createElement('div');
                        newSection.className = 'section-item card';
                        newSection.dataset.position = section.position || (index + 1);
                        
                        newSection.innerHTML = `
                            <div class="card-body">
                                <div class="section-header d-flex align-items-center">
                                    <div class="section-drag me-2">
                                        <i class="mdi mdi-drag-horizontal handle" title="Drag to reorder"></i>
                                    </div>
                                    <div class="section-number me-2">${index + 1}</div>
                                    <div class="section-input flex-grow-1">
                                        <input type="text" class="form-control section-title" name="sections[]"
                                            value="${section.title}" placeholder="Enter section title" required>
                                        <div class="invalid-feedback">Section title is required</div>
                                    </div>
                                    <div class="section-actions ms-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-section" title="Remove section">
                                            <i class="mdi mdi-trash-can"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="section_positions[]" value="${section.position || (index + 1)}" class="section-position">
                            <input type="hidden" name="section_ids[]" value="${section.section_id}" class="section-id">
                        `;
                        
                        // Add to container
                        sectionsContainer.appendChild(newSection);
                        
                        // Add fallback move buttons if jQuery UI not available
                        if (!(typeof $ !== 'undefined' && $.fn.sortable)) {
                            addMoveButtons(newSection);
                        }
                        
                        // Store section ID mapping
                        sectionIds[index] = section.section_id;
                    });
                    
                    // Store section IDs in hidden field
                    document.getElementById('section_ids').value = JSON.stringify(sectionIds);
                    
                    // Setup validation for all inputs
                    document.querySelectorAll('.section-title').forEach(input => {
                        input.addEventListener('blur', function() {
                            validateSectionTitle(this);
                        });
                        
                        input.addEventListener('input', function() {
                            this.classList.remove('is-invalid');
                            const sectionItem = this.closest('.section-item');
                            if (sectionItem) {
                                sectionItem.classList.remove('error');
                            }
                        });
                    });
                    
                    // Update section numbers
                    updateSectionNumbers();
                }
                
                // Show success message
                if (typeof showAlert === 'function') {
                    showAlert('success', 'Course sections loaded successfully');
                }
            } else {
                // Error loading sections
                if (typeof showAlert === 'function') {
                    showAlert('danger', data.message || 'Error loading course sections');
                }
                
                // Create default section
                addSection();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            if (typeof showAlert === 'function') {
                showAlert('danger', 'Error loading course sections: ' + error.message);
            }
            
            // Create default section
            addSection();
        })
        .finally(() => {
            // Remove loading overlay
            if (typeof removeOverlay === 'function') {
                removeOverlay();
            }
        });
}

/**
 * Enhanced function to save course structure
 */
function saveCourseStructure() {
    // Show loading overlay
    if (typeof createOverlay === 'function') {
        createOverlay("Saving course structure...");
    }
    
    // Disable the Next button
    const nextButton = document.getElementById('nextButton');
    if (nextButton) {
        nextButton.disabled = true;
        if (nextButton.classList) {
            nextButton.classList.add('loading');
        }
    }
    
    // Get course ID from hidden field
    const courseId = document.getElementById('course_id')?.value;
    if (!courseId) {
        if (typeof removeOverlay === 'function') {
            removeOverlay();
        }
        
        if (typeof showAlert === 'function') {
            showAlert('danger', 'Course ID not found. Please save basic details first.');
        }
        
        if (nextButton) {
            nextButton.disabled = false;
            if (nextButton.classList) {
                nextButton.classList.remove('loading');
            }
        }
        
        return;
    }
    
    // Validate all sections first
    const sections = document.querySelectorAll('.section-item');
    let isValid = true;
    
    // Check if there are any sections
    if (sections.length === 0) {
        isValid = false;
        
        if (typeof showAlert === 'function') {
            showAlert('danger', 'At least one section is required.');
        }
        
        // Create a default section
        addSection();
    }
    
    // Validate each section title
    let firstInvalidSection = null;
    
    sections.forEach((section, index) => {
        const titleInput = section.querySelector('.section-title');
        if (!titleInput || !titleInput.value.trim()) {
            isValid = false;
            section.classList.add('error');
            titleInput.classList.add('is-invalid');
            
            if (!firstInvalidSection) {
                firstInvalidSection = section;
            }
        }
    });
    
    // Scroll to first invalid section if any
    if (firstInvalidSection) {
        if (typeof removeOverlay === 'function') {
            removeOverlay();
        }
        
        firstInvalidSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        if (typeof showAlert === 'function') {
            showAlert('danger', 'Please fill in all section titles before proceeding.');
        }
        
        if (nextButton) {
            nextButton.disabled = false;
            if (nextButton.classList) {
                nextButton.classList.remove('loading');
            }
        }
        
        return;
    }
    
    // Create form data for submission
    const formData = new FormData();
    formData.append('course_id', courseId);
    
    // Add sections
    sections.forEach((section, index) => {
        const titleInput = section.querySelector('.section-title');
        const positionInput = section.querySelector('.section-position');
        const sectionId = section.querySelector('.section-id')?.value || '';
        
        formData.append(`sections[${index}]`, titleInput.value.trim());
        formData.append(`section_positions[${index}]`, positionInput ? positionInput.value : index + 1);
        
        // Include section ID if it exists (for updates)
        if (sectionId && !sectionId.startsWith('new_')) {
            formData.append(`section_ids[${index}]`, sectionId);
        }
    });
    
    // Send AJAX request
    fetch('../backend/courses/create_course_structure.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Store section IDs in hidden field and individual sections
            storeSectionIds(data.section_ids);
            
            // Show success message
            if (typeof showAlert === 'function') {
                showAlert('success', 'Course structure saved successfully!');
            }
            
            // Update progress bar
            if (typeof updateProgressBar === 'function') {
                updateProgressBar(40); // 40% complete after second step
            }
            
            // Mark step as completed
            const currentStep = document.getElementById('current_step');
            const maxCompletedStep = document.getElementById('max_completed_step');
            
            if (currentStep && maxCompletedStep) {
                maxCompletedStep.value = currentStep.value;
            }
            
            // Move to the next tab
            if (typeof navigateToStep === 'function') {
                navigateToStep(3);
            } else if (typeof moveToNextTab === 'function') {
                moveToNextTab();
            }
            
            return true;
        } else {
            // Show error
            if (typeof showAlert === 'function') {
                showAlert('danger', data.message || 'Error saving course structure');
            }
            
            return false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        // Show error
        if (typeof showAlert === 'function') {
            showAlert('danger', 'Error saving course structure: ' + error.message);
        }
        
        return false;
    })
    .finally(() => {
        // Remove overlay
        if (typeof removeOverlay === 'function') {
            removeOverlay();
        }
        
        // Enable next button
        if (nextButton) {
            nextButton.disabled = false;
            if (nextButton.classList) {
                nextButton.classList.remove('loading');
            }
        }
    });
}

/**
 * Store section IDs in form fields
 */
function storeSectionIds(sectionIds) {
    if (!sectionIds) return;
    
    // Store in the main hidden field
    const sectionIdsField = document.getElementById('section_ids');
    if (sectionIdsField) {
        sectionIdsField.value = JSON.stringify(sectionIds);
    }
    
    // Update individual section ID fields
    Object.entries(sectionIds).forEach(([index, id]) => {
        const sections = document.querySelectorAll('.section-item');
        if (index < sections.length) {
            const idField = sections[index].querySelector('.section-id');
            if (idField) {
                idField.value = id;
            }
        }
    });
}
</script>