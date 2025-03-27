<?php
// Fetch current course full description if editing
$full_description = isset($course['full_description']) ? $course['full_description'] : '';
?>

<div class="row">
    <div class="col-12">
        <div class="mb-3">
            <label for="courseDescription" class="form-label">Course Detailed Description <span class="text-danger">*</span></label>
            <textarea id="courseDescription" name="full_description" class="form-control rich-editor"><?php echo htmlspecialchars($full_description); ?></textarea>
            <small class="form-text text-muted mt-2">
                Provide a comprehensive description of your course. Include what students will learn, 
                the approach you'll take, and why your course is valuable. You can use formatting, 
                lists, and media to make your description engaging and clear.
            </small>
        </div>

        <div class="description-tips card bg-light mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="mdi mdi-lightbulb-outline text-warning"></i> Tips for a Great Description</h5>
                <ul class="mb-0">
                    <li>Start with a clear overview of what your course teaches</li>
                    <li>Mention specific skills students will gain</li>
                    <li>Include information about your teaching approach</li>
                    <li>Highlight what makes your course unique</li>
                    <li>Consider adding bullet points for readability</li>
                    <li>Keep paragraphs short and focused</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Initialize TinyMCE for rich text editing -->
<script src="https://cdn.tiny.cloud/1/4fnlr08nx5aczp8z0vkgtm2sblkj0y9qywi9iox6hs7ghxgv/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    $(document).ready(function() {
        // Initialize TinyMCE
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
                // Trigger autosave when editor content changes
                editor.on('Change', function(e) {
                    clearTimeout(window.descriptionAutoSaveTimer);
                    window.descriptionAutoSaveTimer = setTimeout(function() {
                        saveDescription();
                    }, 2000); // 2 second delay
                });
            }
        });
    });
    
    // Validate the description form
    function validateDescription() {
        const content = tinymce.get('courseDescription').getContent();
        if (!content || content.trim() === '') {
            showAlert('danger', 'Please provide a detailed description for your course.');
            return false;
        }
        return true;
    }
    
    // Save description via AJAX
    function saveDescription(callback) {
        // Show saving indicator
        $('#autoSaveIndicator').addClass('show');
        
        // Get content from TinyMCE
        const description = tinymce.get('courseDescription').getContent();
        
        // Send AJAX request
        $.ajax({
            url: '../ajax/courses/save_description.php',
            type: 'POST',
            data: {
                course_id: <?php echo $course_id; ?>,
                full_description: description
            },
            success: function(response) {
                // Hide saving indicator
                $('#autoSaveIndicator').removeClass('show');
                
                try {
                    const result = JSON.parse(response);
                    
                    if (result.success) {
                        if (callback) callback();
                    } else {
                        showAlert('danger', 'Error saving course description: ' + result.message);
                        if (callback) callback();
                    }
                } catch (e) {
                    console.error('Error parsing server response', e);
                    showAlert('danger', 'Error processing server response.');
                    if (callback) callback();
                }
            },
            error: function() {
                // Hide saving indicator
                $('#autoSaveIndicator').removeClass('show');
                
                showAlert('danger', 'Network error while saving course description.');
                if (callback) callback();
            }
        });
    }
</script>

<style>
    .tox-tinymce {
        border-radius: 0.25rem;
    }
    
    .description-tips {
        border-left: 4px solid #ffc107;
    }
    
    .description-tips ul {
        padding-left: 1.2rem;
    }
</style>