<?php

/**
 * Enhanced Pricing & Settings Tab for Course Creation
 * File: ../includes/create-course-settings.php
 * 
 * Improvements:
 * - Fixed tab ID to match naming convention (step-5-settings)
 * - Enhanced tag management with search and selection
 * - Improved price input with better validation
 * - Better certificate toggle implementation
 * - Added comprehensive field validation
 */
?>
<div class="card">
    <div class="card-header bg-light">
        <h5 class="mb-0">Pricing & Course Settings</h5>
    </div>
    <div class="card-body">
        <p class="text-muted">Configure pricing options and finalize settings for your course.</p>

        <!-- Pricing Options -->
        <div class="setting-section mb-4">
            <h6 class="section-title"><i class="mdi mdi-currency-usd me-1"></i> Pricing Options</h6>
            <div class="row mb-3">
                <label class="col-md-3 col-form-label" for="pricingOptions">Pricing Model</label>
                <div class="col-md-9">
                    <select class="form-select" id="pricingOptions" name="pricingOptions" required>
                        <option value="one-time">One-time Purchase</option>
                        <option value="free">Free Course</option>
                    </select>
                    <div class="form-text text-muted">Choose whether students can access your course for free or need to purchase it.</div>
                </div>
            </div>

            <!-- Course Price (for paid courses) -->
            <div id="priceSection" class="row mb-3">
                <label class="col-md-3 col-form-label" for="coursePrice">Price</label>
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" class="form-control" id="coursePrice" name="coursePrice" min="0.99" max="100" step="0.01" value="9.99" required>
                    </div>
                    <div class="form-text text-muted">Set your course price between $0.99 and $100.00.</div>
                    <div class="invalid-feedback" id="priceError">
                        Please enter a valid price between $0.99 and $100.00.
                    </div>
                </div>
            </div>

            <!-- Hidden field for actual price value -->
            <input type="hidden" id="formattedPrice" name="formattedPrice" value="9.99">
        </div>

        <!-- Course Settings -->
        <div class="setting-section mb-4">
            <h6 class="section-title"><i class="mdi mdi-tune me-1"></i> Course Settings</h6>

            <!-- Experience Level -->
            <div class="row mb-3">
                <label class="col-md-3 col-form-label" for="courseLevel">Experience Level</label>
                <div class="col-md-9">
                    <select class="form-select" id="courseLevel" name="courseLevel" required>
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                        <option value="all-levels">All Levels</option>
                    </select>
                    <div class="form-text text-muted">Select the appropriate experience level for your target students.</div>
                </div>
            </div>

            <!-- Certificate Setting -->
            <div class="row mb-3">
                <label class="col-md-3 col-form-label" for="certificates">Certificate</label>
                <div class="col-md-9">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="certificates" name="certificates">
                        <label class="form-check-label" for="certificates">
                            Enable Certificate Upon Completion
                        </label>
                    </div>
                    <div class="form-text text-muted">When enabled, students will receive a certificate after completing the course.</div>
                </div>
            </div>

            <!-- Course Tags -->
            <div class="row mb-3">
                <label class="col-md-3 col-form-label" for="tagInput">Course Tags</label>
                <div class="col-md-9">
                    <div class="tag-input-container">
                        <div class="tag-display-area" id="tagDisplayArea">
                            <!-- Selected tags will appear here -->
                            <input type="text" id="tagInput" class="tag-search-input" placeholder="Type to search tags...">
                        </div>
                        <div id="tagSuggestions" class="tag-suggestions">
                            <!-- Tag suggestions will appear here -->
                        </div>
                    </div>
                    <div class="tag-counter mt-1">
                        <small><span id="tagCount">0</span>/5 tags selected</small>
                    </div>
                    <div class="form-text text-muted">
                        Select up to 5 tags that best describe your course content. Well-tagged courses are easier for students to find.
                    </div>
                    <!-- Hidden field to store selected tags -->
                    <input type="hidden" id="tagValues" name="tags" value="">
                </div>
            </div>

            <!-- Course Requirements -->
            <div class="row mb-3">
                <label class="col-md-3 col-form-label" for="courseRequirements">Prerequisites</label>
                <div class="col-md-9">
                    <textarea class="form-control" id="courseRequirements" name="courseRequirements" rows="4"
                        placeholder="List any prerequisites students should have before taking this course (e.g., 'Basic knowledge of HTML', 'Computer with internet access')"></textarea>
                    <div class="form-text text-muted">
                        List knowledge, skills, tools, or equipment students should have before starting your course.
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Action Buttons -->
        <div class="row mt-4">
            <div class="col-12 text-end">
                <button type="button" id="saveSettingsBtn" class="btn btn-primary">
                    <i class="mdi mdi-content-save me-1"></i> Save Settings
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS for the Settings Tab -->
<style>
    /* Setting section styling */
    .setting-section {
        padding-bottom: 1.5rem;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid #e9ecef;
    }

    .section-title {
        margin-bottom: 1.2rem;
        color: #495057;
        font-weight: 600;
    }

    /* Tag system styling */
    .tag-input-container {
        position: relative;
    }

    .tag-display-area {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 0.375rem 0.75rem;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        min-height: 38px;
        background-color: #fff;
        align-items: center;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .tag-display-area:focus-within {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .tag-search-input {
        flex: 1;
        border: none;
        outline: none;
        padding: 0.25rem 0;
        min-width: 100px;
        background: transparent;
    }

    .tag-item {
        display: inline-flex;
        align-items: center;
        background-color: #e9ecef;
        color: #212529;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        margin-right: 0.25rem;
        font-size: 0.875rem;
    }

    .tag-item .tag-remove {
        margin-left: 0.25rem;
        cursor: pointer;
        font-size: 0.875rem;
        opacity: 0.6;
    }

    .tag-item .tag-remove:hover {
        opacity: 1;
    }

    .tag-suggestions {
        position: absolute;
        width: 100%;
        max-height: 200px;
        overflow-y: auto;
        background-color: #fff;
        border: 1px solid #ced4da;
        border-top: none;
        border-radius: 0 0 0.25rem 0.25rem;
        z-index: 10;
        display: none;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .tag-suggestion-item {
        padding: 0.5rem 0.75rem;
        cursor: pointer;
        transition: background-color 0.15s ease-in-out;
    }

    .tag-suggestion-item:hover,
    .tag-suggestion-item.active {
        background-color: #f8f9fa;
    }

    .tag-counter {
        color: #6c757d;
        text-align: right;
    }

    /* Price input custom styling */
    #coursePrice::-webkit-outer-spin-button,
    #coursePrice::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    #coursePrice[type=number] {
        -moz-appearance: textfield;
    }

    /* Certificate switch styling */
    .form-switch .form-check-input {
        width: 2.5em;
        height: 1.25em;
    }

    .form-switch .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
</style>

<!-- Enhanced JavaScript for Settings Tab -->
<script>
    /**
     * Enhanced Settings Tab for Course Creation
     * Features:
     * - Proper tag management with search functionality
     * - Enhanced price input with validation
     * - Improved certificate toggle
     * - Better field validation
     */
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize pricing toggles
        initPricingOptions();

        // Initialize tag system
        initTagSystem();

        // Setup validation handlers
        setupFieldValidation();

        // Setup save button event
        const saveSettingsBtn = document.getElementById('saveSettingsBtn');
        if (saveSettingsBtn) {
            saveSettingsBtn.addEventListener('click', function() {
                saveSettings();
            });
        }

        // Setup next button event for older navigation
        const nextButton = document.querySelector('.next a') || document.getElementById('nextButton');
        if (nextButton) {
            nextButton.addEventListener('click', function(e) {
                const activeTab = document.querySelector('.nav-link.active');
                if (activeTab && (activeTab.getAttribute('href') === '#pricing-settings' ||
                        activeTab.getAttribute('href') === '#step-5-settings')) {
                    e.preventDefault();
                    e.stopPropagation();
                    saveSettings();
                    return false;
                }
            });
        }

        // Check if we're editing an existing course
        const courseId = document.getElementById('course_id')?.value;
        if (courseId) {
            loadExistingSettings(courseId);
        }
    });

    /**
     * Initialize pricing options and toggles
     */
    function initPricingOptions() {
        const pricingOptions = document.getElementById('pricingOptions');
        const priceSection = document.getElementById('priceSection');
        const coursePrice = document.getElementById('coursePrice');
        const formattedPrice = document.getElementById('formattedPrice');

        if (pricingOptions) {
            // Handle pricing model changes
            pricingOptions.addEventListener('change', function() {
                togglePricingFields();
            });

            // Initialize on load
            togglePricingFields();
        }

        if (coursePrice) {
            // Format price on input
            coursePrice.addEventListener('input', function() {
                validateAndFormatPrice();
            });

            // Format on blur (to ensure valid format)
            coursePrice.addEventListener('blur', function() {
                validateAndFormatPrice(true);
            });

            // Initialize price
            validateAndFormatPrice();
        }
    }

    /**
     * Toggle pricing fields based on selected option
     */
    function togglePricingFields() {
        const pricingOption = document.getElementById('pricingOptions').value;
        const priceSection = document.getElementById('priceSection');
        const coursePrice = document.getElementById('coursePrice');
        const formattedPrice = document.getElementById('formattedPrice');

        if (pricingOption === 'free') {
            // Hide price input for free courses
            if (priceSection) {
                priceSection.style.display = 'none';
            }

            // Set price to 0
            if (coursePrice) {
                coursePrice.value = '0.00';
            }

            if (formattedPrice) {
                formattedPrice.value = '0.00';
            }
        } else {
            // Show price input for paid courses
            if (priceSection) {
                priceSection.style.display = 'flex';
            }

            // Set default price if empty
            if (coursePrice && (coursePrice.value === '0.00' || coursePrice.value === '')) {
                coursePrice.value = '9.99';
                if (formattedPrice) {
                    formattedPrice.value = '9.99';
                }
            }
        }
    }

    /**
     * Validate and format price input
     */
    function validateAndFormatPrice(enforceMin = false) {
        const coursePrice = document.getElementById('coursePrice');
        const formattedPrice = document.getElementById('formattedPrice');
        const priceError = document.getElementById('priceError');

        if (!coursePrice || !formattedPrice) return;

        // Get the current value
        let value = coursePrice.value.trim();

        // Remove non-numeric characters except decimals
        value = value.replace(/[^\d.]/g, '');

        // Ensure only one decimal point
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }

        // Convert to a number
        let numValue = parseFloat(value);

        // Handle empty or invalid input
        if (value === '' || isNaN(numValue)) {
            numValue = 0;
        }

        // Enforce minimum price if specified
        if (enforceMin && numValue < 0.99 && numValue > 0) {
            numValue = 0.99;
        }

        // Apply maximum constraint
        if (numValue > 100) {
            numValue = 100;
        }

        // Format to 2 decimal places
        const formattedValue = numValue.toFixed(2);

        // Update the displayed value
        coursePrice.value = formattedValue;

        // Store in hidden field
        formattedPrice.value = formattedValue;

        // Set validation state
        if ((numValue < 0.99 && numValue > 0) || numValue > 100) {
            coursePrice.classList.add('is-invalid');
            if (priceError) {
                priceError.style.display = 'block';
            }
        } else {
            coursePrice.classList.remove('is-invalid');
            if (priceError) {
                priceError.style.display = 'none';
            }
        }
    }

    /**
     * Initialize tag system with search and selection
     */
    function initTagSystem() {
        const tagInput = document.getElementById('tagInput');
        const tagDisplayArea = document.getElementById('tagDisplayArea');
        const tagSuggestions = document.getElementById('tagSuggestions');
        const tagValues = document.getElementById('tagValues');
        const tagCount = document.getElementById('tagCount');

        if (!tagInput || !tagDisplayArea || !tagSuggestions || !tagValues) return;

        // Sample tags for demonstration - in production, these would come from a database
        const availableTags = [
            'Web Development', 'Programming', 'JavaScript', 'Python', 'Data Science',
            'Machine Learning', 'Artificial Intelligence', 'Business', 'Design',
            'Photography', 'Marketing', 'SEO', 'Mobile Development', 'Game Development',
            'Database', 'Cloud Computing', 'DevOps', 'Cybersecurity', 'Networking',
            'UI/UX Design', 'Graphics Design', 'Video Editing', 'Music Production',
            'Finance', 'Accounting', 'Leadership', 'Project Management', 'Entrepreneurship'
        ];

        let selectedTags = [];

        // Initialize tag count
        updateTagCount();

        // Handle tag input focus
        tagInput.addEventListener('focus', function() {
            // Show suggestions based on current input
            showTagSuggestions(tagInput.value);
        });

        // Handle tag input keyup for search
        tagInput.addEventListener('input', function() {
            // Show suggestions based on current input
            showTagSuggestions(tagInput.value);
        });

        // Handle clicks outside to hide suggestions
        document.addEventListener('click', function(e) {
            if (!tagDisplayArea.contains(e.target) && !tagSuggestions.contains(e.target)) {
                tagSuggestions.style.display = 'none';
            }
        });

        // Function to show tag suggestions
        function showTagSuggestions(query) {
            // Clear suggestions
            tagSuggestions.innerHTML = '';

            // Filter tags based on query
            const filteredTags = availableTags.filter(tag =>
                tag.toLowerCase().includes(query.toLowerCase()) &&
                !selectedTags.includes(tag)
            );

            // Show suggestions if we have any
            if (filteredTags.length > 0) {
                filteredTags.forEach(tag => {
                    const suggestionItem = document.createElement('div');
                    suggestionItem.className = 'tag-suggestion-item';
                    suggestionItem.textContent = tag;
                    suggestionItem.addEventListener('click', function() {
                        addTag(tag);
                        tagSuggestions.style.display = 'none';
                        tagInput.value = '';
                        tagInput.focus();
                    });
                    tagSuggestions.appendChild(suggestionItem);
                });
                tagSuggestions.style.display = 'block';
            } else {
                tagSuggestions.style.display = 'none';
            }
        }

        // Function to add a tag
        function addTag(tag) {
            // Check if we already have 5 tags
            if (selectedTags.length >= 5) {
                if (typeof showAlert === 'function') {
                    showAlert('warning', 'You can only select up to 5 tags.');
                } else {
                    alert('You can only select up to 5 tags.');
                }
                return;
            }

            // Check if tag is already selected
            if (selectedTags.includes(tag)) {
                return;
            }

            // Add tag to array
            selectedTags.push(tag);

            // Create tag UI element
            const tagElement = document.createElement('div');
            tagElement.className = 'tag-item';
            tagElement.innerHTML = `
${tag}
<span class="tag-remove" data-tag="${tag}">&times;</span>
`;

            // Insert before input
            tagDisplayArea.insertBefore(tagElement, tagInput);

            // Update hidden field
            updateTagValues();
        }

        // Function to remove a tag
        function removeTag(tag) {
            // Remove from array
            selectedTags = selectedTags.filter(t => t !== tag);

            // Remove UI element
            const tagElements = tagDisplayArea.querySelectorAll('.tag-item');
            tagElements.forEach(el => {
                if (el.textContent.trim().replace('×', '') === tag) {
                    el.remove();
                }
            });

            // Update hidden field
            updateTagValues();
        }

        // Handle clicks on tag remove buttons
        tagDisplayArea.addEventListener('click', function(e) {
            if (e.target.classList.contains('tag-remove')) {
                const tag = e.target.getAttribute('data-tag');
                removeTag(tag);
            }
        });

        // Update hidden field with selected tags
        function updateTagValues() {
            tagValues.value = selectedTags.join(',');
            updateTagCount();
        }

        // Update tag counter
        function updateTagCount() {
            if (tagCount) {
                tagCount.textContent = selectedTags.length;
            }
        }

        // Function to pre-populate tags (used when editing)
        window.setTags = function(tags) {
            // Clear existing tags
            selectedTags = [];
            const tagElements = tagDisplayArea.querySelectorAll('.tag-item');
            tagElements.forEach(el => el.remove());

            // Add each tag
            tags.forEach(tag => {
                if (tag && tag.trim()) {
                    addTag(tag.trim());
                }
            });
        };
    }

    /**
     * Setup validation for all form fields
     */
    function setupFieldValidation() {
        // Add validation for required fields
        document.querySelectorAll('#step-5-settings input[required], #step-5-settings select[required], #step-5-settings textarea[required]')
            .forEach(field => {
                field.addEventListener('blur', function() {
                    validateField(this);
                });

                field.addEventListener('input', function() {
                    this.classList.remove('is-invalid');
                });
            });
    }

    /**
     * Validate a single field
     */
    function validateField(field) {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            return false;
        } else {
            field.classList.remove('is-invalid');
            return true;
        }
    }

    /**
     * Load existing settings when editing a course
     */
    function loadExistingSettings(courseId) {
        if (!courseId) return;

        // Show loading state
        if (typeof createOverlay === 'function') {
            createOverlay("Loading course settings...");
        }

        fetch(`../backend/courses/get_course_settings.php?course_id=${courseId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const settings = data.settings;

                    // Set pricing option
                    const pricingOptions = document.getElementById('pricingOptions');
                    if (pricingOptions) {
                        if (parseFloat(settings.price) === 0) {
                            pricingOptions.value = 'free';
                        } else {
                            pricingOptions.value = 'one-time';
                        }

                        // Trigger change event to update visibility
                        const event = new Event('change');
                        pricingOptions.dispatchEvent(event);
                    }

                    // Set course price
                    const coursePrice = document.getElementById('coursePrice');
                    const formattedPrice = document.getElementById('formattedPrice');
                    if (coursePrice && settings.price) {
                        coursePrice.value = parseFloat(settings.price).toFixed(2);

                        if (formattedPrice) {
                            formattedPrice.value = parseFloat(settings.price).toFixed(2);
                        }
                    }

                    // Set course level
                    const courseLevel = document.getElementById('courseLevel');
                    if (courseLevel && settings.course_level) {
                        courseLevel.value = settings.course_level;
                    }

                    // Set tags
                    if (settings.tags && typeof window.setTags === 'function') {
                        // Convert tags string to array
                        let tagsArray = [];

                        if (Array.isArray(settings.tags)) {
                            // If it's already an array
                            tagsArray = settings.tags;
                        } else if (typeof settings.tags === 'string') {
                            // If it's a comma-separated string
                            tagsArray = settings.tags.split(',').map(tag => tag.trim());
                        } else if (settings.tags && typeof settings.tags === 'object') {
                            // If it's an object with tag data
                            tagsArray = settings.tags.map(tag => tag.tag_name || tag);
                        }

                        // Set the tags
                        window.setTags(tagsArray);
                    }

                    // Set requirements
                    const courseRequirements = document.getElementById('courseRequirements');
                    if (courseRequirements && settings.requirements) {
                        // Handle different formats of requirements
                        if (Array.isArray(settings.requirements)) {
                            // Join array items if it's an array of requirement objects
                            const reqText = settings.requirements.map(req =>
                                req.requirement_text || req
                            ).join('\n');

                            courseRequirements.value = reqText;
                        } else {
                            courseRequirements.value = settings.requirements;
                        }
                    }

                    // Set certificate
                    const certificates = document.getElementById('certificates');
                    if (certificates) {
                        certificates.checked = settings.certificate_enabled === '1' ||
                            settings.certificate_enabled === true ||
                            settings.certificate_enabled === 1;
                    }

                    // Show success message
                    if (typeof showAlert === 'function') {
                        showAlert('success', 'Course settings loaded successfully');
                    }
                } else {
                    // Show error
                    if (typeof showAlert === 'function') {
                        showAlert('danger', data.message || 'Error loading course settings');
                    }
                }
            })
            .catch(error => {
                console.error('Error loading settings:', error);

                // Show error
                if (typeof showAlert === 'function') {
                    showAlert('danger', 'Error loading course settings: ' + error.message);
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
     * Enhanced function to save settings
     */
/**
 * Enhanced function to save settings with improved error handling
 */
function saveSettings() {
    console.log("saveSettings function called");
    
    // Show loading state
    if (typeof createOverlay === 'function') {
        createOverlay("Saving course settings...");
    }

    // Add loading state to save button
    const saveButton = document.getElementById('saveSettingsBtn') || document.getElementById('nextButton');
    if (saveButton) {
        saveButton.disabled = true;
        if (saveButton.classList) {
            saveButton.classList.add('loading');
        }
    }

    // Get course ID from hidden field
    const courseId = document.getElementById('course_id')?.value;
    if (!courseId) {
        console.error("No course ID found");
        
        // Hide loading state
        if (typeof removeOverlay === 'function') {
            removeOverlay();
        }

        // Show error
        if (typeof showAlert === 'function') {
            showAlert('danger', 'Course ID not found. Please save basic details first.');
        } else {
            alert('Course ID not found. Please save basic details first.');
        }

        // Re-enable save button
        if (saveButton) {
            saveButton.disabled = false;
            if (saveButton.classList) {
                saveButton.classList.remove('loading');
            }
        }

        return false;
    }

    console.log("Course ID found:", courseId);

    // Validate required fields
    const requiredFields = document.querySelectorAll('#step-5-settings input[required], #step-5-settings select[required], #step-5-settings textarea[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        // Skip hidden fields
        if (field.offsetParent === null && field.type !== 'hidden') {
            return;
        }

        if (!validateField(field)) {
            isValid = false;
            console.warn(`Validation failed for field: ${field.id || field.name}`);
        }
    });

    // Additional validation for price if pricing is one-time
    const pricingOptions = document.getElementById('pricingOptions');
    const coursePrice = document.getElementById('coursePrice');
    const formattedPrice = document.getElementById('formattedPrice');

    if (pricingOptions && pricingOptions.value === 'one-time') {
        const price = parseFloat(formattedPrice?.value || coursePrice?.value || 0);

        if (isNaN(price) || price < 0.99 || price > 100) {
            // Invalid price
            isValid = false;
            console.warn(`Invalid price: ${price}`);

            if (coursePrice) {
                coursePrice.classList.add('is-invalid');
            }

            if (typeof showAlert === 'function') {
                showAlert('danger', 'Please enter a valid price between $0.99 and $100.00');
            }
        }
    }

    if (!isValid) {
        console.error("Validation failed");
        
        // Hide loading state
        if (typeof removeOverlay === 'function') {
            removeOverlay();
        }

        // Show error
        if (typeof showAlert === 'function') {
            showAlert('danger', 'Please fix the validation errors before saving.');
        }

        // Re-enable save button
        if (saveButton) {
            saveButton.disabled = false;
            if (saveButton.classList) {
                saveButton.classList.remove('loading');
            }
        }

        return false;
    }

    // Create form data
    const formData = new FormData();
    formData.append('course_id', courseId);

    // Pricing option
    formData.append('pricing_option', pricingOptions?.value || 'one-time');

    // Price (use formatted price or calculate based on the option)
    const price = pricingOptions?.value === 'free' ? '0.00' : (formattedPrice?.value || coursePrice?.value || '0.99');
    formData.append('course_price', price);

    // Course level
    const courseLevel = document.getElementById('courseLevel');
    formData.append('course_level', courseLevel?.value || 'beginner');

    // Tags
    const tagValues = document.getElementById('tagValues');
    formData.append('tags', tagValues?.value || '');

    // Requirements
    const courseRequirements = document.getElementById('courseRequirements');
    formData.append('course_requirements', courseRequirements?.value || '');

    // Certificate
    const certificates = document.getElementById('certificates');
    formData.append('certificates', certificates?.checked ? '1' : '0');

    console.log("Sending AJAX request to save settings");

    // First, let's check if the backend endpoint exists
    fetch('../backend/courses/save_course_settings.php', {
        method: 'HEAD'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Backend endpoint not found: ${response.status}`);
        }
        
        // Now send the actual data
        return fetch('../backend/courses/save_course_settings.php', {
            method: 'POST',
            body: formData
        });
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        
        // First try to parse as JSON
        return response.text().then(text => {
            if (!text) {
                throw new Error('Empty response received from server');
            }
            
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Error parsing JSON:', e);
                console.log('Response text:', text);
                throw new Error(`Invalid JSON response: ${text.substring(0, 100)}...`);
            }
        });
    })
    .then(data => {
        console.log("Response received:", data);
        
        if (data.success) {
            // Show success message
            if (typeof showAlert === 'function') {
                showAlert('success', 'Course settings saved successfully!');
            }

            // Update progress bar
            if (typeof updateProgressBar === 'function') {
                updateProgressBar(80); // 80% complete after settings step
            }

            // Mark step as completed
            const currentStep = document.getElementById('current_step');
            const maxCompletedStep = document.getElementById('max_completed_step');

            if (currentStep && maxCompletedStep) {
                const stepValue = parseInt(currentStep.value);
                maxCompletedStep.value = stepValue;
                console.log(`Marked step ${stepValue} as completed`);
            }

            // Try multiple navigation methods to ensure one works
            tryToNavigateNext();
            
            return true;
        } else {
            // Show error
            console.error("Server returned success=false:", data.message);
            if (typeof showAlert === 'function') {
                showAlert('danger', data.message || 'Error saving course settings');
            } else {
                alert(data.message || 'Error saving course settings');
            }

            return false;
        }
    })
    .catch(error => {
        console.error('Error in AJAX request:', error);

        // Show error
        if (typeof showAlert === 'function') {
            showAlert('danger', 'Error saving course settings: ' + error.message);
        } else {
            alert('Error saving course settings: ' + error.message);
        }

        // As fallback, try to proceed anyway if navigation is stuck
        if (confirm('There was an error saving your settings. Would you like to continue to the next step anyway?')) {
            tryToNavigateNext();
        }
        
        return false;
    })
    .finally(() => {
        console.log("AJAX request completed (success or failure)");
        
        // Always hide loading state
        if (typeof removeOverlay === 'function') {
            removeOverlay();
        }

        // Always re-enable save button
        if (saveButton) {
            saveButton.disabled = false;
            if (saveButton.classList) {
                saveButton.classList.remove('loading');
            }
        }
    });

    return true;
}

/**
 * Try multiple methods to navigate to the next step
 * This handles different navigation systems that might be in place
 */
function tryToNavigateNext() {
    console.log("Attempting to navigate to next step");
    
    // Method 1: Modern navigation API from enhanced wizard
    if (typeof navigateToStep === 'function') {
        console.log("Using navigateToStep function");
        navigateToStep(6); // Move to review step
        return;
    }
    
    // Method 2: Older moveToNextTab function
    if (typeof moveToNextTab === 'function') {
        console.log("Using moveToNextTab function");
        moveToNextTab();
        return;
    }
    
    // Method 3: Direct tab navigation
    try {
        const reviewTab = document.querySelector('a[href="#step-6-review"]');
        if (reviewTab) {
            console.log("Using direct bootstrap tab navigation");
            const tab = new bootstrap.Tab(reviewTab);
            tab.show();
            return;
        }
    } catch (e) {
        console.warn("Direct tab navigation failed:", e);
    }
    
    // Method 4: jQuery fallback
    try {
        if (typeof $ !== 'undefined') {
            console.log("Using jQuery tab navigation");
            $('a[href="#step-6-review"]').tab('show');
            return;
        }
    } catch (e) {
        console.warn("jQuery tab navigation failed:", e);
    }
    
    // Method 5: Last resort - simulate click on the tab
    try {
        const reviewTabLink = document.querySelector('a[href="#step-6-review"]');
        if (reviewTabLink) {
            console.log("Simulating click on the review tab");
            reviewTabLink.click();
            return;
        }
    } catch (e) {
        console.warn("Tab click simulation failed:", e);
    }
    
    console.warn("All navigation methods failed");
}
</script>