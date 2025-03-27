<?php
// Fetch current course tags if editing
$query = "SELECT t.tag_id, t.tag_name 
          FROM course_tag_mapping ctm 
          JOIN tags t ON ctm.tag_id = t.tag_id 
          WHERE ctm.course_id = ? 
          ORDER BY t.tag_name ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$tags_result = $stmt->get_result();
$course_tags = [];
while ($tag = $tags_result->fetch_assoc()) {
    $course_tags[] = $tag;
}
$stmt->close();

// Fetch popular tags for suggestions
$popular_tags_query = "SELECT t.tag_id, t.tag_name, COUNT(ctm.course_id) as course_count 
                      FROM tags t 
                      JOIN course_tag_mapping ctm ON t.tag_id = ctm.tag_id 
                      GROUP BY t.tag_id 
                      ORDER BY course_count DESC 
                      LIMIT 30";
$popular_tags_result = $conn->query($popular_tags_query);
$popular_tags = [];
while ($tag = $popular_tags_result->fetch_assoc()) {
    $popular_tags[] = $tag;
}
?>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="card-title mb-0">Course Tags</h4>
                <p class="card-subtitle mb-0 mt-1">Add relevant tags to help students find your course</p>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <p class="text-muted">
                        Tags help potential students discover your course through search and recommendations. 
                        Add relevant, specific tags that accurately describe your course content and target audience.
                    </p>
                </div>
                
                <!-- Current Tags Display -->
                <div class="mb-4">
                    <label class="form-label">Current Tags</label>
                    <div id="currentTags" class="d-flex flex-wrap gap-2">
                        <?php if (empty($course_tags)): ?>
                            <div id="noTagsMessage" class="text-muted fst-italic">No tags added yet</div>
                        <?php else: ?>
                            <?php foreach ($course_tags as $tag): ?>
                                <div class="badge bg-light text-dark p-2 tag-badge" data-tag-id="<?php echo $tag['tag_id']; ?>">
                                    <?php echo htmlspecialchars($tag['tag_name']); ?>
                                    <button type="button" class="btn-close ms-1 remove-tag" aria-label="Remove"></button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Tag Search Input -->
                <div class="mb-4">
                    <label for="tagSearch" class="form-label">Add Tags</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="tagSearch" placeholder="Search or add new tags">
                        <button type="button" class="btn btn-primary" id="addTagBtn">
                            <i class="mdi mdi-plus"></i> Add
                        </button>
                    </div>
                    <div id="tagSuggestions" class="tag-suggestions mt-2 d-none">
                        <!-- Suggestions will be populated dynamically -->
                    </div>
                    <small class="form-text text-muted">
                        Type to search existing tags or create new ones. Press Enter or click Add to add the tag.
                    </small>
                </div>
                
                <!-- Popular Tags Section -->
                <div>
                    <label class="form-label">Popular Tags</label>
                    <div class="popular-tags d-flex flex-wrap gap-2">
                        <?php foreach ($popular_tags as $tag): ?>
                            <div class="badge bg-light text-dark p-2 popular-tag" data-tag-id="<?php echo $tag['tag_id']; ?>">
                                <?php echo htmlspecialchars($tag['tag_name']); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="alert alert-info mt-4">
                    <h5 class="alert-heading"><i class="mdi mdi-information-outline"></i> Tips for Effective Tagging</h5>
                    <ul class="mb-0 ps-3">
                        <li>Include your main topic (e.g., "Python", "Digital Marketing")</li>
                        <li>Add specific technologies or methods covered (e.g., "TensorFlow", "SEO")</li>
                        <li>Consider skill level tags (e.g., "Beginner", "Advanced")</li>
                        <li>Include job roles relevant to your content (e.g., "Data Scientist", "Web Developer")</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Store current course tags
        let currentTags = <?php echo json_encode($course_tags); ?>;
        
        // Tag search input handler
        $('#tagSearch').on('input', function() {
            const searchTerm = $(this).val().trim();
            
            if (searchTerm.length >= 2) {
                // Show loading state
                $('#tagSuggestions').html('<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Searching...</div>').removeClass('d-none');
                
                // Fetch tag suggestions via AJAX
                $.ajax({
                    url: '../ajax/courses/search_tags.php',
                    type: 'POST',
                    data: { search: searchTerm },
                    success: function(response) {
                        try {
                            const tags = JSON.parse(response);
                            
                            if (tags.length > 0) {
                                // Populate suggestions
                                let suggestionsHtml = '';
                                tags.forEach(function(tag) {
                                    // Check if tag is already added
                                    const isAdded = currentTags.some(t => t.tag_id === tag.tag_id);
                                    if (!isAdded) {
                                        suggestionsHtml += `
                                            <div class="tag-suggestion p-2" data-tag-id="${tag.tag_id}" data-tag-name="${escapeHtml(tag.tag_name)}">
                                                ${escapeHtml(tag.tag_name)}
                                            </div>
                                        `;
                                    }
                                });
                                
                                // Add option to create new tag if no exact match
                                const exactMatch = tags.some(tag => tag.tag_name.toLowerCase() === searchTerm.toLowerCase());
                                if (!exactMatch) {
                                    suggestionsHtml += `
                                        <div class="tag-suggestion p-2 border-top create-tag" data-tag-name="${escapeHtml(searchTerm)}">
                                            <i class="mdi mdi-plus-circle-outline"></i> Create new tag: "${escapeHtml(searchTerm)}"
                                        </div>
                                    `;
                                }
                                
                                $('#tagSuggestions').html(suggestionsHtml).removeClass('d-none');
                            } else {
                                // No suggestions found, offer to create new tag
                                $('#tagSuggestions').html(`
                                    <div class="tag-suggestion p-2 create-tag" data-tag-name="${escapeHtml(searchTerm)}">
                                        <i class="mdi mdi-plus-circle-outline"></i> Create new tag: "${escapeHtml(searchTerm)}"
                                    </div>
                                `).removeClass('d-none');
                            }
                        } catch (e) {
                            console.error('Error parsing tag suggestions', e);
                            $('#tagSuggestions').addClass('d-none');
                        }
                    },
                    error: function() {
                        $('#tagSuggestions').html('<div class="text-danger">Error loading suggestions</div>').removeClass('d-none');
                    }
                });
            } else {
                $('#tagSuggestions').addClass('d-none');
            }
        });
        
        // Add tag when clicking on a suggestion
        $(document).on('click', '.tag-suggestion', function() {
            const tagId = $(this).data('tag-id');
            const tagName = $(this).data('tag-name');
            
            if ($(this).hasClass('create-tag')) {
                // Create a new tag
                createTag(tagName);
            } else {
                // Add existing tag
                addTag(tagId, tagName);
            }
            
            // Clear search and suggestions
            $('#tagSearch').val('').focus();
            $('#tagSuggestions').addClass('d-none');
        });
        
        // Add tag button click handler
        $('#addTagBtn').click(function() {
            const searchTerm = $('#tagSearch').val().trim();
            
            if (searchTerm) {
                // Check if tag already exists in our current tags
                const existingTag = currentTags.find(t => t.tag_name.toLowerCase() === searchTerm.toLowerCase());
                
                if (existingTag) {
                    showAlert('info', 'This tag has already been added.');
                } else {
                    // Try to find existing tag or create new one
                    $.ajax({
                        url: '../ajax/courses/find_or_create_tag.php',
                        type: 'POST',
                        data: { tag_name: searchTerm },
                        success: function(response) {
                            try {
                                const result = JSON.parse(response);
                                
                                if (result.success) {
                                    addTag(result.tag_id, result.tag_name);
                                    
                                    // Clear search
                                    $('#tagSearch').val('').focus();
                                    $('#tagSuggestions').addClass('d-none');
                                } else {
                                    showAlert('danger', 'Error adding tag: ' + result.message);
                                }
                            } catch (e) {
                                console.error('Error parsing response', e);
                                showAlert('danger', 'Error processing server response.');
                            }
                        },
                        error: function() {
                            showAlert('danger', 'Network error while adding tag.');
                        }
                    });
                }
            }
        });
        
        // Handle enter key in search input
        $('#tagSearch').keypress(function(e) {
            if (e.which === 13) { // Enter key
                e.preventDefault();
                $('#addTagBtn').click();
            }
        });
        
        // Remove tag click handler
        $(document).on('click', '.remove-tag', function(e) {
            e.stopPropagation();
            const tagBadge = $(this).closest('.tag-badge');
            const tagId = tagBadge.data('tag-id');
            
            // Remove from current tags array
            currentTags = currentTags.filter(t => t.tag_id !== tagId);
            
            // Remove from display
            tagBadge.remove();
            
            // Show "no tags" message if all tags removed
            if (currentTags.length === 0) {
                $('#currentTags').append('<div id="noTagsMessage" class="text-muted fst-italic">No tags added yet</div>');
            }
            
            // Save to server
            saveTagsToServer();
        });
        
        // Add popular tag click handler
        $('.popular-tag').click(function() {
            const tagId = $(this).data('tag-id');
            const tagName = $(this).text().trim();
            
            // Check if already added
            if (!currentTags.some(t => t.tag_id === tagId)) {
                addTag(tagId, tagName);
            } else {
                showAlert('info', 'This tag has already been added.');
            }
        });
        
        // Create new tag function
        function createTag(tagName) {
            $.ajax({
                url: '../ajax/courses/create_tag.php',
                type: 'POST',
                data: { tag_name: tagName },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        
                        if (result.success) {
                            addTag(result.tag_id, result.tag_name);
                        } else {
                            showAlert('danger', 'Error creating tag: ' + result.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response', e);
                        showAlert('danger', 'Error processing server response.');
                    }
                },
                error: function() {
                    showAlert('danger', 'Network error while creating tag.');
                }
            });
        }
        
        // Add tag to the UI and current tags array
        function addTag(tagId, tagName) {
            // Remove "no tags" message if present
            $('#noTagsMessage').remove();
            
            // Add to current tags array
            if (!currentTags.some(t => t.tag_id === tagId)) {
                currentTags.push({ tag_id: tagId, tag_name: tagName });
                
                // Add to UI
                const tagBadge = `
                    <div class="badge bg-light text-dark p-2 tag-badge" data-tag-id="${tagId}">
                        ${escapeHtml(tagName)}
                        <button type="button" class="btn-close ms-1 remove-tag" aria-label="Remove"></button>
                    </div>
                `;
                $('#currentTags').append(tagBadge);
                
                // Save to server
                saveTagsToServer();
            }
        }
        
        // Save tags to server
        function saveTagsToServer() {
            // Show saving indicator
            $('#autoSaveIndicator').addClass('show');
            
            // Create array of tag IDs
            const tagIds = currentTags.map(t => t.tag_id);
            
            // Send AJAX request
            $.ajax({
                url: '../ajax/courses/save_tags.php',
                type: 'POST',
                data: {
                    course_id: <?php echo $course_id; ?>,
                    tags: tagIds
                },
                success: function(response) {
                    // Hide saving indicator
                    $('#autoSaveIndicator').removeClass('show');
                    
                    try {
                        const result = JSON.parse(response);
                        
                        if (!result.success) {
                            showAlert('danger', 'Error saving tags: ' + result.message);
                        }
                    } catch (e) {
                        console.error('Error parsing server response', e);
                        showAlert('danger', 'Error processing server response.');
                    }
                },
                error: function() {
                    // Hide saving indicator
                    $('#autoSaveIndicator').removeClass('show');
                    
                    showAlert('danger', 'Network error while saving tags.');
                }
            });
        }
        
        // Escape HTML to prevent XSS
        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    });
    
    // Validate tags
    function validateTags() {
        // No strict validation required for tags
        return true;
    }
</script>

<style>
    .tag-badge {
        border-radius: 50px;
        font-weight: normal;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }
    
    .tag-badge:hover {
        background-color: #e9ecef !important;
    }
    
    .tag-suggestions {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        max-height: 200px;
        overflow-y: auto;
    }
    
    .tag-suggestion {
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    
    .tag-suggestion:hover {
        background-color: #f8f9fa;
    }
    
    .create-tag {
        color: #0d6efd;
    }
    
    .popular-tag {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .popular-tag:hover {
        background-color: #e9ecef !important;
    }
    
    .btn-close {
        font-size: 0.7rem;
        opacity: 0.5;
    }
</style>