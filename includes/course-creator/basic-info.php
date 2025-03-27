<?php
// Fetch categories for dropdown
$categories_query = "SELECT * FROM categories ORDER BY name ASC";
$categories_result = $conn->query($categories_query);
$categories = [];
while ($category = $categories_result->fetch_assoc()) {
    $categories[] = $category;
}

// Fetch course level options
$course_levels = ['Beginner', 'Intermediate', 'Advanced', 'All Levels'];

// Fetch current course data if editing
$course_title = isset($course['title']) ? $course['title'] : '';
$course_short_description = isset($course['short_description']) ? $course['short_description'] : '';
$course_level = isset($course['course_level']) ? $course['course_level'] : 'Beginner';
$subcategory_id = isset($course['subcategory_id']) ? $course['subcategory_id'] : '';
$thumbnail = isset($course['thumbnail']) ? $course['thumbnail'] : '';

// If subcategory is set, get its category ID
$category_id = 0;
if ($subcategory_id) {
    $cat_query = "SELECT category_id FROM subcategories WHERE subcategory_id = $subcategory_id";
    $cat_result = $conn->query($cat_query);
    if ($cat_result && $cat_row = $cat_result->fetch_assoc()) {
        $category_id = $cat_row['category_id'];
    }
}
?>

<form id="basicInfoForm" class="needs-validation" novalidate>
    <div class="row">
        <div class="col-md-8">
            <!-- Course Title -->
            <div class="mb-3">
                <label for="courseTitle" class="form-label">Course Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="courseTitle" name="title" 
                       value="<?php echo htmlspecialchars($course_title); ?>" required>
                <div class="invalid-feedback">Please enter a course title.</div>
                <small class="form-text text-muted">Choose a clear, specific title that describes what students will learn.</small>
            </div>
            
            <!-- Short Description -->
            <div class="mb-3">
                <label for="shortDescription" class="form-label">Short Description <span class="text-danger">*</span></label>
                <textarea class="form-control" id="shortDescription" name="short_description" 
                          rows="3" required><?php echo htmlspecialchars($course_short_description); ?></textarea>
                <div class="invalid-feedback">Please enter a short description.</div>
                <small class="form-text text-muted">Briefly describe your course in 2-3 sentences (max 250 characters).</small>
            </div>
            
            <!-- Category Selection -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                    <select class="form-select" id="category" name="category" required>
                        <option value="" disabled <?php echo empty($category_id) ? 'selected' : ''; ?>>Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>" 
                                    <?php echo ($category_id == $category['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Please select a category.</div>
                </div>
                
                <div class="col-md-6">
                    <label for="subcategory" class="form-label">Subcategory <span class="text-danger">*</span></label>
                    <select class="form-select" id="subcategory" name="subcategory_id" required>
                        <option value="" disabled selected>Select Category First</option>
                    </select>
                    <div class="invalid-feedback">Please select a subcategory.</div>
                </div>
            </div>
            
            <!-- Course Level -->
            <div class="mb-3">
                <label for="courseLevel" class="form-label">Course Level <span class="text-danger">*</span></label>
                <select class="form-select" id="courseLevel" name="course_level" required>
                    <?php foreach ($course_levels as $level): ?>
                        <option value="<?php echo $level; ?>" <?php echo ($course_level == $level) ? 'selected' : ''; ?>>
                            <?php echo $level; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">Please select a course level.</div>
                <small class="form-text text-muted">Select the appropriate level for your target audience.</small>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Thumbnail Upload -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Course Thumbnail</h5>
                    <div class="text-center mb-3">
                        <div class="mb-3 thumbnail-preview">
                            <?php if (!empty($thumbnail)): ?>
                                <img src="../uploads/thumbnails/<?php echo htmlspecialchars($thumbnail); ?>" 
                                     class="img-fluid rounded" alt="Course Thumbnail">
                            <?php else: ?>
                                <div class="placeholder-thumbnail">
                                    <i class="mdi mdi-image-outline"></i>
                                    <p>No thumbnail uploaded</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <input type="file" id="thumbnailUpload" name="thumbnail" class="d-none" accept="image/*">
                        <button type="button" id="thumbnailUploadBtn" class="btn btn-outline-primary">
                            <i class="mdi mdi-upload"></i> Upload Thumbnail
                        </button>
                        <div id="thumbnailFeedback" class="invalid-feedback d-block"></div>
                        <small class="form-text text-muted d-block mt-2">Recommended size: 750x422 pixels (16:9 ratio)</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    $(document).ready(function() {
        // Load subcategories when category changes
        $('#category').change(function() {
            var categoryId = $(this).val();
            
            if (categoryId) {
                // Show loading indicator
                $('#subcategory').html('<option value="" disabled selected>Loading...</option>');
                
                // Fetch subcategories via AJAX
                $.ajax({
                    url: '../ajax/load_subcategories.php',
                    type: 'POST',
                    data: { category_id: categoryId },
                    success: function(response) {
                        try {
                            const subcategories = JSON.parse(response);
                            
                            // Populate subcategory dropdown
                            var options = '<option value="" disabled selected>Select Subcategory</option>';
                            subcategories.forEach(function(item) {
                                options += '<option value="' + item.subcategory_id + '">' + item.name + '</option>';
                            });
                            
                            $('#subcategory').html(options);
                            
                            // If editing and we have a subcategory selected, select it
                            <?php if ($subcategory_id): ?>
                            $('#subcategory').val('<?php echo $subcategory_id; ?>');
                            <?php endif; ?>
                            
                        } catch (e) {
                            console.error('Error parsing subcategories', e);
                            $('#subcategory').html('<option value="" disabled selected>Error loading subcategories</option>');
                        }
                    },
                    error: function() {
                        $('#subcategory').html('<option value="" disabled selected>Error loading subcategories</option>');
                    }
                });
            } else {
                // Reset subcategory dropdown if no category selected
                $('#subcategory').html('<option value="" disabled selected>Select Category First</option>');
            }
        });

        // Trigger change to load subcategories if category is selected
        if ($('#category').val()) {
            $('#category').trigger('change');
        }
        
        // Thumbnail upload button handler
        $('#thumbnailUploadBtn').click(function() {
            $('#thumbnailUpload').click();
        });
        
        // Handle thumbnail file selection
        $('#thumbnailUpload').change(function() {
            var file = this.files[0];
            
            if (file) {
                // Validate file type
                var validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
                if (!validTypes.includes(file.type)) {
                    $('#thumbnailFeedback').text('Please select a valid image file (JPEG, PNG, or GIF).');
                    return;
                }
                
                // Validate file size (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    $('#thumbnailFeedback').text('Image must be less than 2MB in size.');
                    return;
                }
                
                // Show loading state
                createOverlay('Uploading thumbnail...');
                
                // Create FormData for file upload
                var formData = new FormData();
                formData.append('thumbnail', file);
                formData.append('course_id', <?php echo $course_id; ?>);
                
                // Upload the file
                $.ajax({
                    url: '../ajax/courses/upload_thumbnail.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        removeOverlay();
                        try {
                            const result = JSON.parse(response);
                            
                            if (result.success) {
                                // Update thumbnail preview
                                $('.thumbnail-preview').html('<img src="../uploads/thumbnails/' + result.file_path + 
                                                          '" class="img-fluid rounded" alt="Course Thumbnail">');
                                
                                // Clear error messages
                                $('#thumbnailFeedback').text('');
                                
                                // Show success message
                                showAlert('success', 'Thumbnail uploaded successfully!');
                            } else {
                                $('#thumbnailFeedback').text(result.message || 'Error uploading thumbnail.');
                            }
                        } catch (e) {
                            console.error('Error parsing upload response', e);
                            $('#thumbnailFeedback').text('Error processing upload response.');
                        }
                    },
                    error: function() {
                        removeOverlay();
                        $('#thumbnailFeedback').text('Network error while uploading thumbnail.');
                    }
                });
            }
        });
    });
    
    // Validate the basic info form
    function validateBasicInfo() {
        const form = document.getElementById('basicInfoForm');
        if (!form.checkValidity()) {
            // Trigger the browser's validation UI
            form.classList.add('was-validated');
            return false;
        }
        return true;
    }
    
    // Save basic info via AJAX
    function saveBasicInfo(callback) {
        // Get form data
        const title = $('#courseTitle').val();
        const shortDescription = $('#shortDescription').val();
        const subcategoryId = $('#subcategory').val();
        const courseLevel = $('#courseLevel').val();
        
        // Send AJAX request
        $.ajax({
            url: '../ajax/courses/save_basics.php',
            type: 'POST',
            data: {
                course_id: <?php echo $course_id; ?>,
                title: title,
                short_description: shortDescription,
                subcategory_id: subcategoryId,
                course_level: courseLevel
            },
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    
                    if (result.success) {
                        if (callback) callback();
                    } else {
                        showAlert('danger', 'Error saving course basics: ' + result.message);
                        if (callback) callback();
                    }
                } catch (e) {
                    console.error('Error parsing server response', e);
                    showAlert('danger', 'Error processing server response.');
                    if (callback) callback();
                }
            },
            error: function() {
                showAlert('danger', 'Network error while saving course basics.');
                if (callback) callback();
            }
        });
    }
</script>

<style>
    .placeholder-thumbnail {
        width: 100%;
        height: 160px;
        border: 2px dashed #ccc;
        border-radius: 4px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #aaa;
    }
    
    .placeholder-thumbnail i {
        font-size: 48px;
        margin-bottom: 10px;
    }
    
    .thumbnail-preview img {
        max-height: 160px;
        width: auto;
        max-width: 100%;
        object-fit: cover;
    }
</style>