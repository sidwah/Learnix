<?php
// Fetch current course settings if editing
$price = isset($course['price']) ? $course['price'] : '0.00';
$access_level = isset($course['access_level']) ? $course['access_level'] : 'Public';
$certificate_enabled = isset($course['certificate_enabled']) ? $course['certificate_enabled'] : 0;

// Get course settings from separate table if exists
$settings_query = "SELECT * FROM course_settings WHERE course_id = ?";
$stmt = $conn->prepare($settings_query);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$settings_result = $stmt->get_result();
$course_settings = $settings_result->fetch_assoc();
$stmt->close();

// Initialize settings with defaults or existing values
$enrollment_limit = isset($course_settings['enrollment_limit']) ? $course_settings['enrollment_limit'] : null;
$enrollment_start = isset($course_settings['enrollment_period_start']) ? $course_settings['enrollment_period_start'] : null;
$enrollment_end = isset($course_settings['enrollment_period_end']) ? $course_settings['enrollment_period_end'] : null;
$estimated_duration = isset($course_settings['estimated_duration']) ? $course_settings['estimated_duration'] : null;
$visibility = isset($course_settings['visibility']) ? $course_settings['visibility'] : 'Public';
$access_password = isset($course_settings['access_password']) ? $course_settings['access_password'] : null;
?>

<div class="row">
    <!-- Pricing Section -->
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="card-title mb-0">Course Pricing</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="coursePrice" class="form-label">Price (GHS) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <!-- <span class="input-group-text">&#8373;</span> -->
                        <input type="number" step="0.01" min="0" class="form-control" id="coursePrice" name="price" 
                               value="<?php echo htmlspecialchars($price); ?>" disabled>
                    </div>
                    <small class="form-text text-muted">
                        Set to 0 for a free course. Minimum price is ₵0.99 for paid courses.
                    </small>
                </div>
                
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="certificateEnabled" name="certificate_enabled" 
                           <?php echo $certificate_enabled ? 'checked' : ''; ?> disabled>
                    <label class="form-check-label" for="certificateEnabled">
                        Enable Course Completion Certificate
                    </label>
                    <div class="form-text">
                        When enabled, students will receive a certificate upon course completion.
                    </div>
                </div>
                
                <!-- <div class="mb-3">
                    <label for="estimatedDuration" class="form-label">Estimated Course Duration</label>
                    <select class="form-select" id="estimatedDuration" name="estimated_duration">
                        <option value="" <?php //echo empty($estimated_duration) ? 'selected' : ''; ?>>Select Duration</option>
                        <option value="< 1 hour" <?php // echo $estimated_duration === '< 1 hour' ? 'selected' : ''; ?>>Less than 1 hour</option>
                        <option value="1-3 hours" <?php //echo $estimated_duration === '1-3 hours' ? 'selected' : ''; ?>>1-3 hours</option>
                        <option value="3-6 hours" <?php //echo $estimated_duration === '3-6 hours' ? 'selected' : ''; ?>>3-6 hours</option>
                        <option value="6-12 hours" <?php //echo $estimated_duration === '6-12 hours' ? 'selected' : ''; ?>>6-12 hours</option>
                        <option value="12-24 hours" <?php //echo $estimated_duration === '12-24 hours' ? 'selected' : ''; ?>>12-24 hours</option>
                        <option value="> 24 hours" <?php //echo $estimated_duration === '> 24 hours' ? 'selected' : ''; ?>>More than 24 hours</option>
                    </select>
                    <div class="form-text">
                        Help students understand the time commitment required.
                    </div>
                </div> -->
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-info text-white">
                <h4 class="card-title mb-0">Enrollment Settings</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="enrollmentLimit" class="form-label">Enrollment Limit</label>
                    <input type="number" min="0" class="form-control" id="enrollmentLimit" name="enrollment_limit" 
                           value="<?php echo htmlspecialchars($enrollment_limit ?? ''); ?>" placeholder="Unlimited" disabled>
                    <div class="form-text">
                        Leave empty for unlimited enrollments. Set a number to limit the total students.
                    </div>
                </div>
                
                <div class="form-check form-switch mb-3" id="limitEnrollmentPeriodSwitch">
                    <input class="form-check-input" type="checkbox" id="limitEnrollmentPeriod" 
                           <?php echo (!empty($enrollment_start) || !empty($enrollment_end)) ? 'checked' : ''; ?> disabled>
                    <label class="form-check-label" for="limitEnrollmentPeriod">
                        Limit Enrollment Period
                    </label>
                </div>
                
                <div id="enrollmentPeriodContainer" class="<?php echo (empty($enrollment_start) && empty($enrollment_end)) ? 'd-none' : ''; ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="enrollmentStart" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="enrollmentStart" name="enrollment_start" 
                                   value="<?php echo htmlspecialchars($enrollment_start ? date('Y-m-d', strtotime($enrollment_start)) : ''); ?>" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="enrollmentEnd" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="enrollmentEnd" name="enrollment_end" 
                                   value="<?php echo htmlspecialchars($enrollment_end ? date('Y-m-d', strtotime($enrollment_end)) : ''); ?>" disabled>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Access Settings Section -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h4 class="card-title mb-0">Access Settings</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="accessLevel" class="form-label">Access Level <span class="text-danger">*</span></label>
                    <select class="form-select" id="accessLevel" name="access_level" disabled>
                        <option value="Public" <?php echo $access_level === 'Public' ? 'selected' : ''; ?>>Public</option>
                        <option value="Restricted" <?php echo $access_level === 'Restricted' ? 'selected' : ''; ?>>Restricted</option>
                    </select>
                    <div class="form-text">
                        Public courses are visible to everyone. Restricted courses are only accessible with direct links.
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="visibility" class="form-label">Visibility Status</label>
                    <select class="form-select" id="visibility" name="visibility" disabled>
                        <option value="Public" <?php echo $visibility === 'Public' ? 'selected' : ''; ?>>Public - Visible in search and catalog</option>
                        <option value="Private" <?php echo $visibility === 'Private' ? 'selected' : ''; ?>>Private - Only visible to specific students</option>
                        <option value="Password Protected" <?php echo $visibility === 'Password Protected' ? 'selected' : ''; ?>>Password Protected</option>
                        <option value="Coming Soon" <?php echo $visibility === 'Coming Soon' ? 'selected' : ''; ?>>Coming Soon - Show preview page</option>
                    </select>
                </div>
                
                <div id="passwordContainer" class="mb-3 <?php echo $visibility !== 'Password Protected' ? 'd-none' : ''; ?>">
                    <label for="accessPassword" class="form-label">Access Password</label>
                    <input type="text" class="form-control" id="accessPassword" name="access_password" 
                           value="<?php echo htmlspecialchars($access_password ?? ''); ?>" disabled>
                    <div class="form-text">
                        Students will need this password to enroll in your course.
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <h5 class="alert-heading"><i class="mdi mdi-information-outline"></i> Course Publishing Process</h5>
                    <p class="mb-0">Once you complete all steps, your course will be submitted for review before it becomes available to students. During the review process, you can continue to refine your content.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Initialize price input with TouchSpin if available
        if ($.fn.TouchSpin) {
            $('#coursePrice').TouchSpin({
                min: 0,
                max: 999.99,
                step: 0.01,
                decimals: 2,
                prefix: '&#8373;'
            });
        }
        
        // Toggle enrollment period container
        $('#limitEnrollmentPeriod').change(function() {
            if ($(this).is(':checked')) {
                $('#enrollmentPeriodContainer').removeClass('d-none');
            } else {
                $('#enrollmentPeriodContainer').addClass('d-none');
                $('#enrollmentStart, #enrollmentEnd').val('');
            }
        });
        
        // Show/hide password field based on visibility selection
        $('#visibility').change(function() {
            if ($(this).val() === 'Password Protected') {
                $('#passwordContainer').removeClass('d-none');
            } else {
                $('#passwordContainer').addClass('d-none');
                $('#accessPassword').val('');
            }
        });
        
        // Add change event listener to inputs
        $('input, select').change(function() {
            // Trigger autosave
            clearTimeout(window.settingsAutoSaveTimer);
            window.settingsAutoSaveTimer = setTimeout(function() {
                saveSettings();
            }, 2000); // 2 second delay
        });
    });
    
    // Validate the settings form
    function validateSettings() {
        // Check if price is valid
        const price = parseFloat($('#coursePrice').val());
        if (isNaN(price) || price < 0) {
            showAlert('danger', 'Please enter a valid price (0 or greater).');
            return false;
        }
        
        // If price is greater than 0 but less than 0.99, show error
        if (price > 0 && price < 0.99) {
            showAlert('danger', 'Minimum price for paid courses is ₵0.99.');
            return false;
        }
        
        // If password protection is selected but no password is set
        if ($('#visibility').val() === 'Password Protected' && $('#accessPassword').val().trim() === '') {
            showAlert('danger', 'Please provide an access password or select a different visibility option.');
            return false;
        }
        
        // Validate enrollment period if set
        if ($('#limitEnrollmentPeriod').is(':checked')) {
            const startDate = $('#enrollmentStart').val();
            const endDate = $('#enrollmentEnd').val();
            
            // If either date is set, both must be set
            if ((startDate && !endDate) || (!startDate && endDate)) {
                showAlert('danger', 'Please provide both start and end dates for the enrollment period.');
                return false;
            }
            
            // If both dates are set, end date must be after start date
            if (startDate && endDate && new Date(startDate) >= new Date(endDate)) {
                showAlert('danger', 'End date must be after start date.');
                return false;
            }
        }
        
        return true;
    }
    
    // Save settings via AJAX
    function saveSettings(callback) {
        // Show saving indicator
        $('#autoSaveIndicator').addClass('show');
        
        // Collect form data
        const formData = {
            course_id: <?php echo $course_id; ?>,
            price: $('#coursePrice').val(),
            certificate_enabled: $('#certificateEnabled').is(':checked') ? 1 : 0,
            access_level: $('#accessLevel').val(),
            visibility: $('#visibility').val(),
            access_password: $('#accessPassword').val(),
            enrollment_limit: $('#enrollmentLimit').val(),
            estimated_duration: $('#estimatedDuration').val()
        };
        
        // Add enrollment period if enabled
        if ($('#limitEnrollmentPeriod').is(':checked')) {
            formData.enrollment_start = $('#enrollmentStart').val();
            formData.enrollment_end = $('#enrollmentEnd').val();
        }
        
        // Send AJAX request
        $.ajax({
            url: '../ajax/courses/save_settings.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                // Hide saving indicator
                $('#autoSaveIndicator').removeClass('show');
                
                try {
                    const result = JSON.parse(response);
                    
                    if (result.success) {
                        if (callback) callback();
                    } else {
                        showAlert('danger', 'Error saving course settings: ' + result.message);
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
                
                showAlert('danger', 'Network error while saving course settings.');
                if (callback) callback();
            }
        });
    }
</script>

<style>
    /* Override TouchSpin styles to match the theme */
    .bootstrap-touchspin .input-group-btn-vertical .bootstrap-touchspin-up {
        border-radius: 0 4px 0 0;
    }
    
    .bootstrap-touchspin .input-group-btn-vertical .bootstrap-touchspin-down {
        border-radius: 0 0 4px 0;
    }
</style>