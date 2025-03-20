<?php
/**
 * Enhanced Basic Details Tab for Course Creation
 * File: ../includes/create-course-basic.php
 * 
 * Improvements:
 * - Fixed ID duplication issues
 * - Enhanced validation with better error handling
 * - Improved image upload and compression
 * - Better UX for learning outcomes management
 * - Seamless integration with wizard navigation
 */
?>
<div class="card">
    <div class="card-header bg-light">
        <h5 class="mb-0">Basic Course Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <!-- Course Title -->
                <div class="row mb-3">
                    <label class="col-md-3 col-form-label" for="courseTitle">Course Title <span class="text-danger">*</span></label>
                    <div class="col-md-9">
                        <input type="text" class="form-control" id="courseTitle" name="courseTitle"
                            placeholder="Enter Course Title" required>
                        <div class="invalid-feedback">Please enter a course title</div>
                    </div>
                </div>

                <!-- Short Description -->
                <div class="row mb-3">
                    <label class="col-md-3 col-form-label" for="shortDescription">Short Description <span class="text-danger">*</span></label>
                    <div class="col-md-9">
                        <input type="text" class="form-control" id="shortDescription" name="shortDescription"
                            placeholder="Enter short description (max 150 characters)" maxlength="150" required>
                        <div class="invalid-feedback">Please enter a short description</div>
                        <small class="form-text text-muted">A brief description that appears in course listings (150 chars max)</small>
                    </div>
                </div>

                <!-- Full Description -->
                <div class="row mb-3">
                    <label class="col-md-3 col-form-label" for="fullDescription">Full Description <span class="text-danger">*</span></label>
                    <div class="col-md-9">
                        <textarea class="form-control" id="fullDescription" name="fullDescription" rows="5"
                            placeholder="Enter detailed course description" required></textarea>
                        <div class="invalid-feedback">Please enter a full description of your course</div>
                        <small class="form-text text-muted">Detailed information about what the course covers</small>
                    </div>
                </div>

                <!-- What You'll Learn -->
                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">What You'll Learn <span class="text-danger">*</span></label>
                    <div class="col-md-9">
                        <div id="learningOutcomesContainer">
                            <div class="input-group mb-2">
                                <input type="text" class="form-control learning-outcome-input" name="learningOutcomes[]"
                                    placeholder="Enter a learning outcome" required>
                                <button type="button" class="btn btn-success add-outcome">
                                    <i class="mdi mdi-plus"></i>
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted">List specific skills or knowledge students will gain</small>
                        <div id="outcomes-error" class="text-danger mt-1" style="display: none;">
                            Please add at least one learning outcome
                        </div>
                    </div>
                </div>

                <!-- Category -->
                <div class="row mb-3">
                    <label class="col-md-3 col-form-label" for="subcategory">Category <span class="text-danger">*</span></label>
                    <div class="col-md-9">
                        <select class="form-control" name="subcategory" id="subcategory" required>
                            <option value="">Select a Category</option>
                            <?php
                            // Fetch subcategories and categories for dropdown
                            $query = $conn->query("
                                SELECT s.subcategory_id, s.name as subname, c.name as catname 
                                FROM subcategories s
                                JOIN categories c ON s.category_id = c.category_id
                                ORDER BY c.name, s.name ASC
                            ");
                            
                            $currentCategory = '';
                            while ($row = $query->fetch_assoc()) {
                                if ($currentCategory != $row['catname']) {
                                    if ($currentCategory != '') {
                                        echo '</optgroup>';
                                    }
                                    $currentCategory = $row['catname'];
                                    echo "<optgroup label='{$row['catname']}'>";
                                }
                                echo "<option value='{$row['subcategory_id']}'>{$row['subname']}</option>";
                            }
                            
                            if ($currentCategory != '') {
                                echo '</optgroup>';
                            }
                            ?>
                        </select>
                        <div class="invalid-feedback">Please select a category</div>
                    </div>
                </div>

                <!-- Thumbnail Image -->
                <div class="row mb-3">
                    <label class="col-md-3 col-form-label" for="thumbnailImage">Thumbnail Image <span class="text-danger">*</span></label>
                    <div class="col-md-9">
                        <div class="thumbnail-upload-container" id="thumbnailDropzone">
                            <input type="file" class="form-control d-none" id="thumbnailImage"
                                name="thumbnailImage" accept="image/*">
                            <img id="thumbnailPreview" 
                                src="../instructor/assets/images/thumbnail-default.jpg"
                                alt="Thumbnail Preview">
                            <div class="upload-overlay">
                                <i class="mdi mdi-cloud-upload"></i>
                                <span>Click to upload or drop image here</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <p id="uploadInstruction" class="text-muted mb-0">
                                <small>Click to upload an image (Recommended: 1280×720, max 2MB)</small>
                            </p>
                            <button type="button" id="removeImageBtn" class="btn btn-sm btn-outline-danger" style="display: none;">
                                <i class="mdi mdi-trash-can"></i> Remove
                            </button>
                        </div>
                        <div id="image-error" class="text-danger mt-1" style="display: none;">
                            Please upload a thumbnail image
                        </div>
                    </div>
                </div>
            </div> <!-- end col -->
        </div> <!-- end row -->
    </div>
</div>

<!-- Enhanced styles for the thumbnail uploader -->
<style>
.thumbnail-upload-container {
    width: 100%;
    height: 300px;
    border: 2px dashed #ccc;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    cursor: pointer;
    background-color: #f8f9fa;
    position: relative;
    transition: all 0.3s ease;
}

.thumbnail-upload-container:hover {
    border-color: #6c757d;
    background-color: #e9ecef;
}

.thumbnail-upload-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: all 0.3s ease;
}

.thumbnail-upload-container .upload-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: white;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.thumbnail-upload-container:hover .upload-overlay {
    opacity: 1;
}

.upload-overlay i {
    font-size: 40px;
    margin-bottom: 10px;
}

.thumbnail-upload-container.drag-over {
    border-color: #28a745;
    background-color: rgba(40, 167, 69, 0.1);
}

.thumbnail-upload-container.error {
    border-color: #dc3545;
}

.thumbnail-upload-container.processing {
    pointer-events: none;
    opacity: 0.7;
}

/* Learning outcomes styling */
.learning-outcome-input {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}

.input-group .btn {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

.remove-outcome {
    border-radius: 0;
}
</style>

<!-- JavaScript for Basic Details Tab -->
<script>
/**
 * Enhanced Basic Details Tab Functionality
 * 
 * Features:
 * - Advanced image handling with compression
 * - Drag and drop support for images
 * - Improved outcome management
 * - Better validation and error handling
 * - Integration with wizard navigation
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the thumbnail uploader
    initThumbnailUploader();
    
    // Initialize learning outcomes management
    initLearningOutcomes();
    
    // Check if we're loading an existing course
    const urlParams = new URLSearchParams(window.location.search);
    const courseId = urlParams.get('course_id');
    
    if (courseId) {
        // Pre-fill the hidden course ID field if it exists
        const courseIdField = document.getElementById('course_id');
        if (courseIdField) {
            courseIdField.value = courseId;
        }
        
        // Load existing course data
        loadExistingCourseDetails(courseId);
    }
    
    // Setup validation for fields
    setupFieldValidation();
});

/**
 * Setup validation for required fields
 */
function setupFieldValidation() {
    // Add blur event listeners to required fields
    document.querySelectorAll('#step-1-basics input[required], #step-1-basics select[required], #step-1-basics textarea[required]').forEach(field => {
        field.addEventListener('blur', function() {
            validateField(this);
        });
        
        field.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    });
}

/**
 * Validate an individual field
 */
function validateField(field) {
    if (field.type === 'file') {
        // Special handling for file inputs
        return;
    }
    
    if (!field.value.trim()) {
        field.classList.add('is-invalid');
        return false;
    } else {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        return true;
    }
}

/**
 * Initialize thumbnail uploader with drag and drop
 */
function initThumbnailUploader() {
    const dropzone = document.getElementById('thumbnailDropzone');
    const fileInput = document.getElementById('thumbnailImage');
    const preview = document.getElementById('thumbnailPreview');
    const instruction = document.getElementById('uploadInstruction');
    const removeBtn = document.getElementById('removeImageBtn');
    const errorMsg = document.getElementById('image-error');
    
    if (!dropzone || !fileInput || !preview) return;
    
    // Open file dialog when container is clicked
    dropzone.addEventListener('click', function() {
        fileInput.click();
    });
    
    // Handle file selection
    fileInput.addEventListener('change', function(event) {
        handleThumbnailSelection(event);
    });
    
    // Handle drag and drop
    dropzone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropzone.classList.add('drag-over');
    });
    
    dropzone.addEventListener('dragleave', function() {
        dropzone.classList.remove('drag-over');
    });
    
    dropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropzone.classList.remove('drag-over');
        
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            handleThumbnailSelection({ target: fileInput });
        }
    });
    
    // Remove image button
    if (removeBtn) {
        removeBtn.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevent dropzone click
            
            // Reset preview
            preview.src = 'https://via.placeholder.com/600x300?text=Upload+Course+Thumbnail';
            fileInput.value = ''; // Clear file input
            
            // Hide remove button
            removeBtn.style.display = 'none';
            
            // Update instruction
            instruction.innerHTML = '<small>Click to upload an image (Recommended: 1280×720, max 2MB)</small>';
            
            // Show error if we're not in edit mode
            const courseId = document.getElementById('course_id')?.value;
            if (!courseId) {
                errorMsg.style.display = 'block';
            }
        });
    }
}

/**
 * Handle thumbnail selection and processing
 */
async function handleThumbnailSelection(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('thumbnailPreview');
    const instruction = document.getElementById('uploadInstruction');
    const dropzone = document.getElementById('thumbnailDropzone');
    const removeBtn = document.getElementById('removeImageBtn');
    const errorMsg = document.getElementById('image-error');
    
    if (!file) return;
    
    // Validate file type
    if (!file.type.match('image.*')) {
        showAlert('danger', 'Please select an image file (JPEG, PNG, GIF)');
        return;
    }
    
    // Hide error message
    if (errorMsg) {
        errorMsg.style.display = 'none';
    }
    
    try {
        // Show processing state
        dropzone.classList.add('processing');
        instruction.innerHTML = '<small><i class="mdi mdi-loading mdi-spin"></i> Processing image...</small>';
        
        // Display original image while processing
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
        
        // Get file size in MB
        const fileSizeMB = file.size / (1024 * 1024);
        console.log(`Original image size: ${fileSizeMB.toFixed(2)} MB`);
        
        // Compress image if larger than 2MB or dimensions too large
        let processedFile = file;
        
        if (fileSizeMB > 2) {
            instruction.innerHTML = '<small><i class="mdi mdi-loading mdi-spin"></i> Compressing image...</small>';
            
            // First compression attempt - try 80% quality
            processedFile = await compressImage(file, 1280, 720, 0.8);
            
            // If still too large, compress further
            if (processedFile.size > 2 * 1024 * 1024) {
                instruction.innerHTML = '<small><i class="mdi mdi-loading mdi-spin"></i> Image is large, optimizing further...</small>';
                processedFile = await compressImage(processedFile, 1024, 576, 0.7);
            }
            
            // Final size check
            const compressedSizeMB = processedFile.size / (1024 * 1024);
            console.log(`Compressed to: ${compressedSizeMB.toFixed(2)} MB (${Math.round((1 - (processedFile.size / file.size)) * 100)}% reduction)`);
            
            // Replace the file in the input
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(processedFile);
            event.target.files = dataTransfer.files;
            
            // Show compressed preview
            const finalReader = new FileReader();
            finalReader.onload = function(e) {
                preview.src = e.target.result;
            };
            finalReader.readAsDataURL(processedFile);
            
            instruction.innerHTML = `<small>Compressed to ${compressedSizeMB.toFixed(2)} MB</small>`;
        } else {
            instruction.innerHTML = '<small>Image looks good!</small>';
        }
        
        // Show remove button
        if (removeBtn) {
            removeBtn.style.display = 'block';
        }
    } catch (error) {
        console.error("Error processing image:", error);
        instruction.innerHTML = '<small>Failed to process image. Try a different one.</small>';
        showAlert('danger', 'Error processing image: ' + error.message);
    } finally {
        // Remove processing state
        dropzone.classList.remove('processing');
    }
}

/**
 * Compress image while maintaining aspect ratio
 */
function compressImage(file, maxWidth, maxHeight, quality) {
    return new Promise((resolve, reject) => {
        // If it's not an image, return the original file
        if (!file.type.startsWith('image/')) {
            resolve(file);
            return;
        }
        
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = function(event) {
            const img = new Image();
            img.src = event.target.result;
            
            img.onload = function() {
                // Calculate new dimensions
                let width = img.width;
                let height = img.height;
                const aspectRatio = width / height;
                
                // Resize if larger than max dimensions
                if (width > maxWidth) {
                    width = maxWidth;
                    height = Math.round(width / aspectRatio);
                }
                
                if (height > maxHeight) {
                    height = maxHeight;
                    width = Math.round(height * aspectRatio);
                }
                
                // Create canvas and draw image
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                
                const ctx = canvas.getContext('2d');
                ctx.fillStyle = '#FFFFFF'; // White background
                ctx.fillRect(0, 0, width, height);
                ctx.drawImage(img, 0, 0, width, height);
                
                // Convert to blob with specified quality
                canvas.toBlob(function(blob) {
                    // Create a new file with the same name but compressed
                    const newFile = new File([blob], file.name, {
                        type: 'image/jpeg',
                        lastModified: Date.now()
                    });
                    
                    resolve(newFile);
                }, 'image/jpeg', quality);
            };
            
            img.onerror = function() {
                reject(new Error('Failed to load image'));
            };
        };
        
        reader.onerror = function() {
            reject(new Error('Failed to read file'));
        };
    });
}

/**
 * Initialize learning outcomes management
 */
function initLearningOutcomes() {
    const container = document.getElementById('learningOutcomesContainer');
    if (!container) return;
    
    // Add outcome button event delegation
    container.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-outcome') || 
            e.target.closest('.add-outcome')) {
            addLearningOutcome();
        }
        
        if (e.target.classList.contains('remove-outcome') || 
            e.target.closest('.remove-outcome')) {
            const inputGroup = e.target.closest('.input-group');
            container.removeChild(inputGroup);
            updateOutcomeButtons();
        }
    });
    
    // Update buttons initially
    updateOutcomeButtons();
}

/**
 * Add new learning outcome field
 */
function addLearningOutcome() {
    const container = document.getElementById('learningOutcomesContainer');
    const inputGroups = container.querySelectorAll('.input-group');
    const lastInputGroup = inputGroups[inputGroups.length - 1];
    const lastInput = lastInputGroup.querySelector('.learning-outcome-input');

    // Only add new input if the last one is not empty
    if (lastInput.value.trim() !== '') {
        // Create a new input group
        const newInputGroup = document.createElement('div');
        newInputGroup.classList.add('input-group', 'mb-2');

        const newInput = document.createElement('input');
        newInput.type = 'text';
        newInput.name = 'learningOutcomes[]';
        newInput.classList.add('form-control', 'learning-outcome-input');
        newInput.placeholder = 'Enter a learning outcome';
        newInput.required = true;

        // Add the new input to the DOM
        newInputGroup.appendChild(newInput);
        container.appendChild(newInputGroup);

        // Update all buttons
        updateOutcomeButtons();

        // Focus the new input
        newInput.focus();
        
        // Hide error message if visible
        const errorMsg = document.getElementById('outcomes-error');
        if (errorMsg) {
            errorMsg.style.display = 'none';
        }
    } else {
        // If last input is empty, focus it
        lastInput.focus();
    }
}

/**
 * Update outcome buttons based on number of inputs
 */
function updateOutcomeButtons() {
    const container = document.getElementById('learningOutcomesContainer');
    const inputGroups = container.querySelectorAll('.input-group');

    // Remove all existing buttons
    inputGroups.forEach(group => {
        const buttons = group.querySelectorAll('button');
        buttons.forEach(button => button.remove());
    });

    // Add appropriate buttons based on position
    inputGroups.forEach((group, index) => {
        const isLast = index === inputGroups.length - 1;
        const isOnly = inputGroups.length === 1;

        if (isLast) {
            // Last input always gets a plus button
            const addButton = document.createElement('button');
            addButton.type = 'button';
            addButton.classList.add('btn', 'btn-success', 'add-outcome');
            addButton.innerHTML = '<i class="mdi mdi-plus"></i>';
            group.appendChild(addButton);
        }

        if (!isOnly) {
            // All inputs except when there's only one get a minus button
            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.classList.add('btn', 'btn-danger', 'remove-outcome');
            removeButton.innerHTML = '<i class="mdi mdi-minus"></i>';
            group.appendChild(removeButton);
        }
    });
}

/**
 * Load existing course details when editing
 */
function loadExistingCourseDetails(courseId) {
    // Show loading overlay
    if (typeof createOverlay === 'function') {
        createOverlay("Loading course details...");
    }
    
    fetch(`../backend/courses/get_course_basic.php?course_id=${courseId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const course = data.course;
                
                // Fill in basic details
                document.getElementById('courseTitle').value = course.title || '';
                document.getElementById('shortDescription').value = course.short_description || '';
                
                // Handle special case for full description (could be in snow-editor or fullDescription)
                const fullDescField = document.getElementById('fullDescription') || document.getElementById('snow-editor');
                if (fullDescField) {
                    fullDescField.value = course.full_description || '';
                }
                
                // Set category
                const subcategorySelect = document.getElementById('subcategory');
                if (subcategorySelect && course.subcategory_id) {
                    subcategorySelect.value = course.subcategory_id;
                }
                
                // Set thumbnail preview
                if (course.thumbnail) {
                    const preview = document.getElementById('thumbnailPreview');
                    const instruction = document.getElementById('uploadInstruction');
                    const removeBtn = document.getElementById('removeImageBtn');
                    
                    if (preview) {
                        preview.src = `../uploads/thumbnails/${course.thumbnail}`;
                    }
                    
                    if (instruction) {
                        instruction.innerHTML = '<small>Current image (click to change)</small>';
                    }
                    
                    if (removeBtn) {
                        removeBtn.style.display = 'block';
                    }
                }
                
                // Add learning outcomes
                const learningOutcomesContainer = document.getElementById('learningOutcomesContainer');
                if (learningOutcomesContainer) {
                    learningOutcomesContainer.innerHTML = '';
                    
                    if (course.learning_outcomes && course.learning_outcomes.length > 0) {
                        course.learning_outcomes.forEach(outcome => {
                            const inputGroup = document.createElement('div');
                            inputGroup.classList.add('input-group', 'mb-2');
                            
                            const input = document.createElement('input');
                            input.type = 'text';
                            input.name = 'learningOutcomes[]';
                            input.classList.add('form-control', 'learning-outcome-input');
                            input.placeholder = 'Enter a learning outcome';
                            input.value = outcome.outcome_text || '';
                            input.required = true;
                            
                            inputGroup.appendChild(input);
                            learningOutcomesContainer.appendChild(inputGroup);
                        });
                    } else {
                        // Add a default empty one if no outcomes
                        const inputGroup = document.createElement('div');
                        inputGroup.classList.add('input-group', 'mb-2');
                        
                        const input = document.createElement('input');
                        input.type = 'text';
                        input.name = 'learningOutcomes[]';
                        input.classList.add('form-control', 'learning-outcome-input');
                        input.placeholder = 'Enter a learning outcome';
                        input.required = true;
                        
                        inputGroup.appendChild(input);
                        learningOutcomesContainer.appendChild(inputGroup);
                    }
                    
                    // Update buttons
                    updateOutcomeButtons();
                }
                
                // Show a success message
                if (typeof showAlert === 'function') {
                    showAlert('success', 'Course details loaded successfully');
                }
            } else {
                // Show error
                if (typeof showAlert === 'function') {
                    showAlert('danger', data.message || 'Error loading course details');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            if (typeof showAlert === 'function') {
                showAlert('danger', 'Error loading course details: ' + error.message);
            }
        })
        .finally(() => {
            // Hide loading overlay
            if (typeof removeOverlay === 'function') {
                removeOverlay();
            }
        });
}

/**
 * Validate all basic details before saving
 */
function validateBasicDetails() {
    let isValid = true;
    let errorMessages = [];
    
    // Required fields
    const requiredFields = [
        { id: 'courseTitle', name: 'Course Title' },
        { id: 'shortDescription', name: 'Short Description' },
        { id: 'fullDescription', name: 'Full Description', altId: 'snow-editor' },
        { id: 'subcategory', name: 'Category' }
    ];
    
    // Validate each required field
    requiredFields.forEach(field => {
        let element = document.getElementById(field.id);
        
        // Try alternative ID if provided and main ID not found
        if (!element && field.altId) {
            element = document.getElementById(field.altId);
        }
        
        if (!element || !element.value.trim()) {
            isValid = false;
            errorMessages.push(`${field.name} is required`);
            
            if (element) {
                element.classList.add('is-invalid');
            }
        } else if (element) {
            element.classList.remove('is-invalid');
        }
    });
    
    // Check learning outcomes
    const learningOutcomes = document.querySelectorAll('input[name="learningOutcomes[]"]');
    let hasOutcome = false;
    
    learningOutcomes.forEach(outcome => {
        if (outcome.value.trim()) {
            hasOutcome = true;
        }
    });
    
    if (!hasOutcome) {
        isValid = false;
        errorMessages.push('At least one learning outcome is required');
        
        // Show outcome error
        const errorMsg = document.getElementById('outcomes-error');
        if (errorMsg) {
            errorMsg.style.display = 'block';
        }
    } else {
        // Hide outcome error
        const errorMsg = document.getElementById('outcomes-error');
        if (errorMsg) {
            errorMsg.style.display = 'none';
        }
    }
    
    // Check thumbnail (only for new courses)
    const thumbnailInput = document.getElementById('thumbnailImage');
    const courseId = document.getElementById('course_id')?.value;
    const thumbnailPreview = document.getElementById('thumbnailPreview');
    const isUpdatingCourse = courseId && courseId.trim() !== '';
    
    if (!isUpdatingCourse && thumbnailPreview && thumbnailPreview.src.includes('placeholder')) {
        isValid = false;
        errorMessages.push('Thumbnail image is required');
        
        // Show image error
        const imageError = document.getElementById('image-error');
        if (imageError) {
            imageError.style.display = 'block';
        }
        
        // Highlight dropzone
        const dropzone = document.getElementById('thumbnailDropzone');
        if (dropzone) {
            dropzone.classList.add('error');
            setTimeout(() => {
                dropzone.classList.remove('error');
            }, 2000);
        }
    } else {
        // Hide image error
        const imageError = document.getElementById('image-error');
        if (imageError) {
            imageError.style.display = 'none';
        }
    }
    
    return { isValid, errorMessages };
}

/**
 * Enhanced save basic details function
 */
function saveBasicDetails() {
    // Validate all fields
    const validation = validateBasicDetails();
    
    // Show loading overlay
    if (typeof createOverlay === 'function') {
        createOverlay("Saving course details...");
    }
    
    // Disable the Next button
    const nextButton = document.getElementById('nextButton');
    if (nextButton) {
        nextButton.disabled = true;
        nextButton.classList.add('loading');
    }
    
    if (!validation.isValid) {
        // Remove overlay
        if (typeof removeOverlay === 'function') {
            removeOverlay();
        }
        
        // Enable next button
        if (nextButton) {
            nextButton.disabled = false;
            nextButton.classList.remove('loading');
        }
        
        // Show error
        if (typeof showAlert === 'function') {
            showAlert('danger', 'Please fix the following errors:<br>' + validation.errorMessages.join('<br>'));
        }
        
        return false;
    }
    
    // Create FormData object
    const formData = new FormData();
    
    // Add course basic details
    formData.append('courseTitle', document.getElementById('courseTitle').value.trim());
    formData.append('shortDescription', document.getElementById('shortDescription').value.trim());
    
    // Handle full description (could be in snow-editor or fullDescription)
    const fullDescField = document.getElementById('fullDescription') || document.getElementById('snow-editor');
    if (fullDescField) {
        formData.append('fullDescription', fullDescField.value.trim());
    }
    
    // Add subcategory
    const subcategory = document.getElementById('subcategory');
    if (subcategory) {
        formData.append('subcategory', subcategory.value);
    }
    
    // Add learning outcomes
    const learningOutcomes = document.querySelectorAll('input[name="learningOutcomes[]"]');
    learningOutcomes.forEach((outcome, index) => {
        if (outcome.value.trim()) {
            formData.append(`learningOutcomes[${index}]`, outcome.value.trim());
        }
    });
    
    // Add course ID if it exists (for updates)
    const courseId = document.getElementById('course_id')?.value;
    const isUpdatingCourse = courseId && courseId.trim() !== '';
    
    if (isUpdatingCourse) {
        formData.append('course_id', courseId);
    }
    
    // Add thumbnail only if a file is selected or we're creating a new course
    const thumbnailInput = document.getElementById('thumbnailImage');
    if (thumbnailInput && thumbnailInput.files.length > 0) {
        formData.append('thumbnailImage', thumbnailInput.files[0]);
    }
    
    // Send AJAX request
    fetch('../backend/courses/create_course_basic.php', {
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
            // Store the course ID in all hidden fields
            const courseIdFields = document.querySelectorAll('[name="course_id"]');
            courseIdFields.forEach(field => {
                field.value = data.course_id;
            });
            
            // Show success message
            if (typeof showAlert === 'function') {
                showAlert('success', 'Basic course details saved successfully!');
            }
            
            // Update progress bar
            if (typeof updateProgressBar === 'function') {
                updateProgressBar(20); // 20% complete after first step
            }
            
            // Mark step as completed
            const currentStep = document.getElementById('current_step');
            const maxCompletedStep = document.getElementById('max_completed_step');
            
            if (currentStep && maxCompletedStep) {
                maxCompletedStep.value = currentStep.value;
            }
            
            // Move to the next tab programmatically
            if (typeof navigateToStep === 'function') {
                navigateToStep(2);
            } else if (typeof moveToNextTab === 'function') {
                moveToNextTab();
            }
            
            return true;
        } else {
            // Show error
            if (typeof showAlert === 'function') {
                showAlert('danger', data.message || 'Error saving course details');
            }
            
            return false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        // Show error
        if (typeof showAlert === 'function') {
            showAlert('danger', 'Error saving course details: ' + error.message);
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
            nextButton.classList.remove('loading');
        }
    });
    
    return true;
}
</script>