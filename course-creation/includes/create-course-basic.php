<?php
/**
 * Create Course - Basic Information
 * File: ../includes/create-course-basic.php
 * 
 * This file contains the form for collecting basic course information:
 * - Course title
 * - Course description
 * - Category selection
 * - Course thumbnail
 */
?>

<div class="basic-info-container">
    <h4 class="header-title mb-3">Basic Course Information</h4>
    <p class="text-muted">
        Start by providing the essential details about your course. This information helps students
        understand what your course is about and helps with discoverability.
    </p>

    <div class="row mt-4">
        <!-- Course Title -->
        <div class="col-md-8">
            <div class="mb-3">
                <label for="courseTitle" class="form-label">Course Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="courseTitle" name="courseTitle" 
                       placeholder="Enter a clear, descriptive title" maxlength="100" required>
                <div class="form-text">
                    Aim for a title that's specific, benefit-driven, and contains relevant keywords.
                    <span id="titleCounter" class="float-end">0/100</span>
                </div>
                <div class="invalid-feedback">Please enter a course title.</div>
            </div>
        </div>

        <!-- Course Level Selection -->
        <div class="col-md-4">
            <div class="mb-3">
                <label for="courseLevel" class="form-label">Course Level</label>
                <select class="form-select" id="courseLevel" name="courseLevel">
                    <option value="Beginner">Beginner</option>
                    <option value="Intermediate">Intermediate</option>
                    <option value="Advanced">Advanced</option>
                    <option value="All Levels">All Levels</option>
                </select>
                <div class="form-text">
                    Select the expertise level required for your course.
                </div>
            </div>
        </div>

        <!-- Short Description -->
        <div class="col-md-12">
            <div class="mb-3">
                <label for="shortDescription" class="form-label">Short Description <span class="text-danger">*</span></label>
                <textarea class="form-control" id="shortDescription" name="shortDescription" 
                          rows="2" maxlength="255" placeholder="Brief overview of your course" required></textarea>
                <div class="form-text">
                    Write a compelling summary that appears in search results and course cards.
                    <span id="descCounter" class="float-end">0/255</span>
                </div>
                <div class="invalid-feedback">Please enter a short description.</div>
            </div>
        </div>

        <!-- Full Description -->
        <div class="col-md-12">
            <div class="mb-4">
                <label for="fullDescription" class="form-label">Full Description</label>
                <textarea class="form-control rich-editor" id="fullDescription" name="fullDescription" 
                          rows="6" placeholder="Detailed description of your course"></textarea>
                <div class="form-text">
                    Provide a comprehensive description of what students will learn and why they should take your course.
                    You can format text, add lists, and include images.
                </div>
            </div>
        </div>

        <!-- Category Selection -->
        <div class="col-md-6">
            <div class="mb-3">
                <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                <select class="form-select" id="category" name="category" required>
                    <option value="" selected disabled>Select Category</option>
                    <?php
                    // Get categories from database (example code - needs to be implemented)
                    $categories = []; // This would be populated from database
                    
                    // Connect to database and get categories
                    try {
                        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        
                        $stmt = $pdo->prepare("SELECT category_id, name FROM categories ORDER BY name");
                        $stmt->execute();
                        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                        // Log error but don't expose details to user
                        error_log("Database error: " . $e->getMessage());
                        echo '<option value="" disabled>Could not load categories</option>';
                    }
                    
                    // Output categories
                    foreach ($categories as $category) {
                        echo '<option value="' . htmlspecialchars($category['category_id']) . '">' 
                             . htmlspecialchars($category['name']) . '</option>';
                    }
                    ?>
                </select>
                <div class="form-text">
                    Choose the main category that best fits your course.
                </div>
                <div class="invalid-feedback">Please select a category.</div>
            </div>
        </div>

        <!-- Subcategory Selection (loads dynamically based on category) -->
        <div class="col-md-6">
            <div class="mb-3">
                <label for="subcategory" class="form-label">Subcategory <span class="text-danger">*</span></label>
                <select class="form-select" id="subcategory" name="subcategory" required disabled>
                    <option value="" selected disabled>Select Category First</option>
                </select>
                <div class="form-text">
                    Choose a subcategory to help students find your course more easily.
                </div>
                <div class="invalid-feedback">Please select a subcategory.</div>
            </div>
        </div>

        <!-- Course Thumbnail -->
        <div class="col-md-12">
            <div class="mb-3">
                <label class="form-label">Course Thumbnail <span class="text-danger">*</span></label>
                <div class="course-thumbnail-container">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="thumbnail-preview mb-2">
                                <img id="thumbnailPreview" src="assets/images/placeholder-thumbnail.jpg" 
                                     alt="Course Thumbnail" class="img-fluid rounded border">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-2">
                                <input type="file" class="form-control" id="courseThumbnail" name="courseThumbnail" 
                                       accept="image/jpeg, image/png, image/jpg" required>
                                <input type="hidden" id="thumbnailPath" name="thumbnailPath" value="">
                                <div class="form-text">
                                    Upload a high-quality image (JPEG or PNG) with a 16:9 aspect ratio.
                                    Minimum dimensions: 1280 × 720 pixels.
                                </div>
                                <div class="invalid-feedback">Please upload a course thumbnail.</div>
                            </div>
                            <div class="thumbnail-requirements mt-2">
                                <h6 class="text-muted">Thumbnail Requirements:</h6>
                                <ul class="text-muted small">
                                    <li>High resolution (minimum 1280 × 720 pixels)</li>
                                    <li>16:9 aspect ratio for best display</li>
                                    <li>Clear, professional, and relevant to your course</li>
                                    <li>No text is recommended (may be cut off on mobile)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Tags -->
        <div class="col-md-12">
            <div class="mb-3">
                <label for="courseTags" class="form-label">Course Tags</label>
                <select class="select2 form-control select2-multiple" id="courseTags" name="courseTags[]" 
                        multiple="multiple" data-placeholder="Choose relevant tags">
                    <?php
                    // Get tags from database (example code - needs to be implemented)
                    $tags = []; // This would be populated from database
                    
                    // Connect to database and get tags
                    try {
                        $stmt = $pdo->prepare("SELECT tag_id, tag_name FROM tags ORDER BY tag_name");
                        $stmt->execute();
                        $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                        // Log error but don't expose details to user
                        error_log("Database error: " . $e->getMessage());
                    }
                    
                    // Output tags
                    foreach ($tags as $tag) {
                        echo '<option value="' . htmlspecialchars($tag['tag_id']) . '">' 
                             . htmlspecialchars($tag['tag_name']) . '</option>';
                    }
                    ?>
                </select>
                <div class="form-text">
                    Select up to 5 tags that describe your course content. Tags help with discoverability.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize character counters
    initializeCharacterCounter('courseTitle', 'titleCounter', 100);
    initializeCharacterCounter('shortDescription', 'descCounter', 255);
    
    // Initialize rich text editor
    if (typeof tinyMCE !== 'undefined') {
        tinyMCE.init({
            selector: '.rich-editor',
            height: 300,
            menubar: false,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount'
            ],
            toolbar: 'undo redo | formatselect | bold italic backcolor | \
                     alignleft aligncenter alignright alignjustify | \
                     bullist numlist outdent indent | removeformat | help',
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; }'
        });
    }
    
    // Initialize Select2 for tags
    if (typeof $.fn.select2 !== 'undefined') {
        $('#courseTags').select2({
            maximumSelectionLength: 5,
            width: '100%'
        });
    }
    
    // Handle category change to load subcategories
    $('#category').on('change', function() {
        const categoryId = $(this).val();
        if (!categoryId) return;
        
        // Show loading state
        const subcategorySelect = $('#subcategory');
        subcategorySelect.html('<option value="" disabled selected>Loading...</option>');
        subcategorySelect.prop('disabled', true);
        
        // Fetch subcategories via AJAX
        $.ajax({
            url: 'ajax/load_subcategories.php',
            type: 'POST',
            data: { category_id: categoryId },
            dataType: 'json',
            success: function(response) {
                // Clear loading option
                subcategorySelect.empty();
                
                // Add default option
                subcategorySelect.append('<option value="" disabled selected>Select Subcategory</option>');
                
                // Add subcategories
                if (response.success && response.subcategories.length > 0) {
                    response.subcategories.forEach(function(subcategory) {
                        subcategorySelect.append(`<option value="${subcategory.id}">${subcategory.name}</option>`);
                    });
                    subcategorySelect.prop('disabled', false);
                } else {
                    subcategorySelect.append('<option value="" disabled>No subcategories found</option>');
                }
            },
            error: function() {
                subcategorySelect.html('<option value="" disabled selected>Error loading subcategories</option>');
                console.error('Error loading subcategories');
            }
        });
    });
    
    // Handle thumbnail upload and preview
    $('#courseThumbnail').on('change', function() {
        const file = this.files[0];
        if (file) {
            // Validate file type
            const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!validTypes.includes(file.type)) {
                alert('Please upload a valid image file (JPEG or PNG)');
                this.value = '';
                return;
            }
            
            // Validate file size (max 5MB)
            const maxSize = 5 * 1024 * 1024; // 5MB in bytes
            if (file.size > maxSize) {
                alert('File size exceeds 5MB limit');
                this.value = '';
                return;
            }
            
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#thumbnailPreview').attr('src', e.target.result);
            }
            reader.readAsDataURL(file);
            
            // Upload thumbnail in background
            uploadThumbnail(file);
        }
    });
    
    // Check if editing existing course and load data
    loadExistingCourseData();
});

/**
 * Initialize character counter for text fields
 */
function initializeCharacterCounter(inputId, counterId, maxLength) {
    const input = document.getElementById(inputId);
    const counter = document.getElementById(counterId);
    
    if (input && counter) {
        // Initial count
        counter.textContent = `${input.value.length}/${maxLength}`;
        
        // Update on input
        input.addEventListener('input', function() {
            counter.textContent = `${this.value.length}/${maxLength}`;
            
            // Visual indicator when approaching limit
            if (this.value.length > maxLength * 0.9) {
                counter.classList.add('text-warning');
            } else {
                counter.classList.remove('text-warning');
            }
            
            // Visual indicator when at limit
            if (this.value.length === maxLength) {
                counter.classList.add('text-danger');
            } else {
                counter.classList.remove('text-danger');
            }
        });
    }
}

/**
 * Upload thumbnail file in background
 */
function uploadThumbnail(file) {
    const formData = new FormData();
    formData.append('thumbnail', file);
    
    // Get course ID if editing
    const courseId = document.getElementById('course_id').value;
    if (courseId) {
        formData.append('course_id', courseId);
    }
    
    // Upload via AJAX
    $.ajax({
        url: 'ajax/upload_thumbnail.php',
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
                    // Update progress indicator if needed
                }
            }, false);
            
            return xhr;
        },
        success: function(response) {
            try {
                const result = JSON.parse(response);
                if (result.success) {
                    // Store the thumbnail path
                    document.getElementById('thumbnailPath').value = result.path;
                } else {
                   console.error('Upload error:', result.message);
                   alert('Error uploading thumbnail: ' + result.message);
               }
           } catch (e) {
               console.error('Invalid response from server:', response);
               alert('Error processing server response');
           }
       },
       error: function() {
           console.error('Thumbnail upload failed');
           alert('Failed to upload thumbnail. Please try again.');
       }
   });
}

/**
* Load existing course data if editing
*/
function loadExistingCourseData() {
   const courseId = document.getElementById('course_id').value;
   if (!courseId) return; // Not editing an existing course
   
   // Fetch course data via AJAX
   $.ajax({
       url: 'ajax/get_course_data.php',
       type: 'GET',
       data: { course_id: courseId },
       dataType: 'json',
       success: function(response) {
           if (response.success) {
               const course = response.course;
               
               // Populate form fields
               document.getElementById('courseTitle').value = course.title || '';
               document.getElementById('shortDescription').value = course.short_description || '';
               
               // Handle rich text editor content
               if (typeof tinyMCE !== 'undefined') {
                   tinyMCE.get('fullDescription').setContent(course.full_description || '');
               } else {
                   document.getElementById('fullDescription').value = course.full_description || '';
               }
               
               // Set category and trigger change to load subcategories
               if (course.category_id) {
                   $('#category').val(course.category_id).trigger('change');
                   
                   // Need to set subcategory after categories load
                   setTimeout(function() {
                       $('#subcategory').val(course.subcategory_id);
                   }, 1000);
               }
               
               // Set course level
               if (course.course_level) {
                   document.getElementById('courseLevel').value = course.course_level;
               }
               
               // Set thumbnail preview if exists
               if (course.thumbnail) {
                   document.getElementById('thumbnailPreview').src = 'uploads/thumbnails/' + course.thumbnail;
                   document.getElementById('thumbnailPath').value = course.thumbnail;
               }
               
               // Set tags if using Select2
               if (typeof $.fn.select2 !== 'undefined' && course.tags) {
                   $('#courseTags').val(course.tags).trigger('change');
               }
               
               // Update character counters
               document.getElementById('titleCounter').textContent = `${course.title?.length || 0}/100`;
               document.getElementById('descCounter').textContent = `${course.short_description?.length || 0}/255`;
           } else {
               console.error('Error loading course data:', response.message);
               alert('Error loading course data: ' + response.message);
           }
       },
       error: function() {
           console.error('Failed to load course data');
           alert('Failed to load course data. Please refresh the page and try again.');
       }
   });
}
</script>