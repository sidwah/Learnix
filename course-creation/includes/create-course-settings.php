<?php
/**
 * Create Course - Pricing & Settings
 * File: ../includes/create-course-settings.php
 * 
 * This file contains the form for configuring course settings:
 * - Course pricing options
 * - Enrollment settings
 * - Course level and visibility
 * - Certificate options
 */
?>

<div class="settings-container">
    <h4 class="header-title mb-3">Course Settings</h4>
    <p class="text-muted">
        Configure pricing, enrollment options, and other settings for your course. These settings 
        determine how students will access your course.
    </p>

    <div class="row mt-4">
        <!-- Course Settings Tabs -->
        <div class="col-lg-12">
            <ul class="nav nav-tabs nav-bordered mb-3" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="pricing-tab" data-bs-toggle="tab" href="#pricing-settings" role="tab" aria-controls="pricing-settings" aria-selected="true">
                        <i class="mdi mdi-currency-usd me-1"></i> Pricing
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="enrollment-tab" data-bs-toggle="tab" href="#enrollment-settings" role="tab" aria-controls="enrollment-settings" aria-selected="false">
                        <i class="mdi mdi-account-group me-1"></i> Enrollment
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="display-tab" data-bs-toggle="tab" href="#display-settings" role="tab" aria-controls="display-settings" aria-selected="false">
                        <i class="mdi mdi-eye me-1"></i> Display
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="certificate-tab" data-bs-toggle="tab" href="#certificate-settings" role="tab" aria-controls="certificate-settings" aria-selected="false">
                        <i class="mdi mdi-certificate me-1"></i> Certificate
                    </a>
                </li>
            </ul>
            
            <div class="tab-content">
                <!-- Pricing Settings -->
                <div class="tab-pane fade show active" id="pricing-settings" role="tabpanel" aria-labelledby="pricing-tab">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Course Pricing</h5>
                            <p class="text-muted">Set the price for your course or make it free.</p>
                            
                            <div class="mb-4">
                                <label class="form-label d-block">Pricing Type</label>
                                <div class="btn-group" role="group">
                                    <input type="radio" class="btn-check" name="pricingType" id="pricingFree" value="free">
                                    <label class="btn btn-outline-primary" for="pricingFree">Free</label>
                                    
                                    <input type="radio" class="btn-check" name="pricingType" id="pricingPaid" value="paid" checked>
                                    <label class="btn btn-outline-primary" for="pricingPaid">Paid</label>
                                </div>
                            </div>
                            
                            <div id="paidCourseOptions">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="coursePrice" class="form-label">Course Price <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" id="coursePrice" name="coursePrice" class="form-control" min="0.99" step="0.01" value="9.99" required>
                                            </div>
                                            <div class="form-text">
                                                Minimum price is $0.99
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="priceCurrency" class="form-label">Currency</label>
                                            <select class="form-select" id="priceCurrency" name="priceCurrency">
                                                <option value="USD" selected>USD ($)</option>
                                                <option value="EUR">EUR (€)</option>
                                                <option value="GBP">GBP (£)</option>
                                                <option value="CAD">CAD (C$)</option>
                                                <option value="AUD">AUD (A$)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="enableDiscounts" name="enableDiscounts">
                                        <label class="form-check-label" for="enableDiscounts">Enable discounts and coupons</label>
                                    </div>
                                    <div class="form-text">
                                        Allow students to apply discount codes to this course
                                    </div>
                                </div>
                                
                                <div id="discountOptions" style="display: none;">
                                    <div class="mb-3">
                                        <label for="discountPrice" class="form-label">Sale Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" id="discountPrice" name="discountPrice" class="form-control" min="0.99" step="0.01">
                                        </div>
                                        <div class="form-text">
                                            Optional: Set a reduced price for the course
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="discountExpiry" class="form-label">Sale Expiry Date</label>
                                        <input type="date" id="discountExpiry" name="discountExpiry" class="form-control">
                                        <div class="form-text">
                                            Optional: The date when the sale price expires
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Enrollment Settings -->
                <div class="tab-pane fade" id="enrollment-settings" role="tabpanel" aria-labelledby="enrollment-tab">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Enrollment Options</h5>
                            <p class="text-muted">Configure how students can enroll in your course.</p>
                            
                            <div class="mb-3">
                                <label for="accessLevel" class="form-label">Access Level</label>
                                <select class="form-select" id="accessLevel" name="accessLevel">
                                    <option value="Public" selected>Public - Anyone can enroll</option>
                                    <option value="Restricted">Restricted - Enrollment by approval only</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="enrollmentLimit" class="form-label">Enrollment Limit</label>
                                <input type="number" id="enrollmentLimit" name="enrollmentLimit" class="form-control" min="0" placeholder="Leave blank for unlimited">
                                <div class="form-text">
                                    Optional: Limit the number of students who can enroll
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enableEnrollmentPeriod" name="enableEnrollmentPeriod">
                                    <label class="form-check-label" for="enableEnrollmentPeriod">Set enrollment period</label>
                                </div>
                                <div class="form-text">
                                    Specify a time period when students can enroll in the course
                                </div>
                            </div>
                            
                            <div id="enrollmentPeriodOptions" class="row" style="display: none;">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="enrollmentStart" class="form-label">Start Date</label>
                                        <input type="date" id="enrollmentStart" name="enrollmentStart" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="enrollmentEnd" class="form-label">End Date</label>
                                        <input type="date" id="enrollmentEnd" name="enrollmentEnd" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Display Settings -->
                <div class="tab-pane fade" id="display-settings" role="tabpanel" aria-labelledby="display-tab">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Display Options</h5>
                            <p class="text-muted">Configure how your course appears to students.</p>
                            
                            <div class="mb-3">
                                <label for="courseLevel" class="form-label">Course Level</label>
                                <select class="form-select" id="courseLevel" name="courseLevel">
                                    <option value="Beginner" selected>Beginner</option>
                                    <option value="Intermediate">Intermediate</option>
                                    <option value="Advanced">Advanced</option>
                                    <option value="All Levels">All Levels</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="estimatedDuration" class="form-label">Estimated Duration</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <input type="number" id="durationHours" name="durationHours" class="form-control" min="0" value="0">
                                            <span class="input-group-text">hours</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <input type="number" id="durationMinutes" name="durationMinutes" class="form-control" min="0" max="59" value="0">
                                            <span class="input-group-text">minutes</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-text">
                                    Total time it takes to complete the course
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="courseVisibility" class="form-label">Visibility</label>
                                <select class="form-select" id="courseVisibility" name="courseVisibility">
                                    <option value="Public" selected>Public - Visible in catalog and search</option>
                                    <option value="Private">Private - Only visible to enrolled students</option>
                                    <option value="Password Protected">Password Protected - Requires a password to view</option>
                                    <option value="Coming Soon">Coming Soon - Visible but not yet enrollable</option>
                                </select>
                            </div>
                            
                            <div id="passwordProtectionOptions" class="mb-3" style="display: none;">
                                <label for="accessPassword" class="form-label">Access Password</label>
                                <input type="text" id="accessPassword" name="accessPassword" class="form-control" placeholder="Enter a password for course access">
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="isFeatured" name="isFeatured">
                                    <label class="form-check-label" for="isFeatured">Feature this course</label>
                                </div>
                                <div class="form-text">
                                    Featured courses appear prominently on the homepage
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Certificate Settings -->
                <div class="tab-pane fade" id="certificate-settings" role="tabpanel" aria-labelledby="certificate-tab">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Certificate Options</h5>
                            <p class="text-muted">Configure certificates for course completion.</p>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="certificateEnabled" name="certificateEnabled">
                                    <label class="form-check-label" for="certificateEnabled">Enable certificates</label>
                                </div>
                                <div class="form-text">
                                    Allow students to earn a certificate upon course completion
                                </div>
                            </div>
                            
                            <div id="certificateOptions" style="display: none;">
                                <div class="mb-3">
                                    <label for="certificateTemplate" class="form-label">Certificate Template</label>
                                    <select class="form-select" id="certificateTemplate" name="certificateTemplate">
                                        <option value="1" selected>Default Template</option>
                                        <option value="2">Professional Template</option>
                                        <option value="3">Academic Template</option>
                                    </select>
                                </div>
                                
                                <!-- <div class="mb-3">
                                    <label for="completionCriteria" class="form-label">Completion Criteria</label> -->
                                    <label for="completionCriteria" class="form-label">Completion Criteria</label>
                                   <select class="form-select" id="completionCriteria" name="completionCriteria">
                                       <option value="all" selected>Complete all lessons</option>
                                       <option value="quizzes">Pass all quizzes</option>
                                       <option value="percentage">Complete percentage of content</option>
                                   </select>
                               </div>
                               
                               <div id="percentageCriteriaOption" class="mb-3" style="display: none;">
                                   <label for="completionPercentage" class="form-label">Required Completion Percentage</label>
                                   <div class="input-group">
                                       <input type="number" id="completionPercentage" name="completionPercentage" class="form-control" min="50" max="100" value="80">
                                       <span class="input-group-text">%</span>
                                   </div>
                               </div>
                               
                               <div class="mb-3">
                                   <label for="certificateTitle" class="form-label">Certificate Title</label>
                                   <input type="text" id="certificateTitle" name="certificateTitle" class="form-control" placeholder="e.g., Certificate of Completion">
                                   <div class="form-text">
                                       The title that will appear on the certificate
                                   </div>
                               </div>
                           </div>
                       </div>
                   </div>
               </div>
           </div>
       </div>
   </div>
   
   <div class="mt-3 d-flex justify-content-end">
       <button type="button" id="saveSettingsBtn" class="btn btn-primary">
           <i class="mdi mdi-content-save me-1"></i> Save Settings
       </button>
   </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
   // Initialize options visibility based on checkboxes
   setupToggleOptions();
   
   // Set up event listeners
   setupSettingsEvents();
   
   // Load existing settings if editing
   loadExistingSettings();
});

/**
* Set up toggle options based on checkboxes
*/
function setupToggleOptions() {
   // Pricing type toggle
   document.querySelectorAll('input[name="pricingType"]').forEach(radio => {
       radio.addEventListener('change', function() {
           document.getElementById('paidCourseOptions').style.display = 
               this.value === 'paid' ? 'block' : 'none';
       });
   });
   
   // Discounts toggle
   document.getElementById('enableDiscounts').addEventListener('change', function() {
       document.getElementById('discountOptions').style.display = 
           this.checked ? 'block' : 'none';
   });
   
   // Enrollment period toggle
   document.getElementById('enableEnrollmentPeriod').addEventListener('change', function() {
       document.getElementById('enrollmentPeriodOptions').style.display = 
           this.checked ? 'block' : 'none';
   });
   
   // Course visibility - password protection
   document.getElementById('courseVisibility').addEventListener('change', function() {
       document.getElementById('passwordProtectionOptions').style.display = 
           this.value === 'Password Protected' ? 'block' : 'none';
   });
   
   // Certificate toggle
   document.getElementById('certificateEnabled').addEventListener('change', function() {
       document.getElementById('certificateOptions').style.display = 
           this.checked ? 'block' : 'none';
   });
   
   // Completion criteria toggle
   document.getElementById('completionCriteria').addEventListener('change', function() {
       document.getElementById('percentageCriteriaOption').style.display = 
           this.value === 'percentage' ? 'block' : 'none';
   });
}

/**
* Set up events for settings page
*/
function setupSettingsEvents() {
   // Save settings button
   document.getElementById('saveSettingsBtn').addEventListener('click', function() {
       saveSettings();
   });
}

/**
* Save course settings
*/
function saveSettings() {
   // Validate form
   if (!validateSettings()) {
       return;
   }
   
   // Show loading state
   const saveBtn = document.getElementById('saveSettingsBtn');
   saveBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin me-1"></i> Saving...';
   saveBtn.disabled = true;
   
   // Gather all settings
   const settings = gatherSettingsData();
   
   // Submit settings via AJAX
   $.ajax({
       url: 'ajax/save_course_settings.php',
       type: 'POST',
       data: settings,
       dataType: 'json',
       success: function(response) {
           // Restore button state
           saveBtn.innerHTML = '<i class="mdi mdi-content-save me-1"></i> Save Settings';
           saveBtn.disabled = false;
           
           if (response.success) {
               // Show success message
               showSettingsAlert('success', 'Settings saved successfully');
               
               // Update course status
               if (response.course_id) {
                   document.getElementById('course_id').value = response.course_id;
               }
               
               // Navigate to next step
               if (typeof navigateToStep === 'function') {
                   const currentStep = parseInt(document.getElementById('current_step').value, 10);
                   const maxCompleted = parseInt(document.getElementById('max_completed_step').value, 10);
                   
                   if (currentStep > maxCompleted) {
                       document.getElementById('max_completed_step').value = currentStep;
                   }
                   
                   navigateToStep(currentStep + 1);
               }
           } else {
               // Show error message
               showSettingsAlert('danger', 'Error saving settings: ' + response.message);
           }
       },
       error: function() {
           // Restore button state
           saveBtn.innerHTML = '<i class="mdi mdi-content-save me-1"></i> Save Settings';
           saveBtn.disabled = false;
           
           // Show error message
           showSettingsAlert('danger', 'Failed to save settings. Please try again.');
       }
   });
}

/**
* Validate settings before saving
*/
function validateSettings() {
   // Reset validation state
   document.querySelectorAll('.is-invalid').forEach(field => {
       field.classList.remove('is-invalid');
   });
   
   let isValid = true;
   const errors = [];
   
   // Validate price if paid course
   if (document.getElementById('pricingPaid').checked) {
       const price = document.getElementById('coursePrice').value;
       if (!price || parseFloat(price) < 0.99) {
           document.getElementById('coursePrice').classList.add('is-invalid');
           errors.push('Course price must be at least $0.99');
           isValid = false;
       }
   }
   
   // Validate enrollment dates if enabled
   if (document.getElementById('enableEnrollmentPeriod').checked) {
       const startDate = document.getElementById('enrollmentStart').value;
       const endDate = document.getElementById('enrollmentEnd').value;
       
       if (!startDate) {
           document.getElementById('enrollmentStart').classList.add('is-invalid');
           errors.push('Enrollment start date is required');
           isValid = false;
       }
       
       if (!endDate) {
           document.getElementById('enrollmentEnd').classList.add('is-invalid');
           errors.push('Enrollment end date is required');
           isValid = false;
       }
       
       if (startDate && endDate && new Date(startDate) >= new Date(endDate)) {
           document.getElementById('enrollmentStart').classList.add('is-invalid');
           document.getElementById('enrollmentEnd').classList.add('is-invalid');
           errors.push('Enrollment end date must be after start date');
           isValid = false;
       }
   }
   
   // Validate password if password protected
   if (document.getElementById('courseVisibility').value === 'Password Protected') {
       const password = document.getElementById('accessPassword').value;
       if (!password) {
           document.getElementById('accessPassword').classList.add('is-invalid');
           errors.push('Access password is required for password-protected courses');
           isValid = false;
       }
   }
   
   // Validate certificate title if certificates enabled
   if (document.getElementById('certificateEnabled').checked) {
       const title = document.getElementById('certificateTitle').value;
       if (!title) {
           document.getElementById('certificateTitle').classList.add('is-invalid');
           errors.push('Certificate title is required');
           isValid = false;
       }
   }
   
   // Show errors if any
   if (!isValid) {
       let errorMessage = 'Please fix the following errors:';
       errors.forEach(error => {
           errorMessage += '<br>- ' + error;
       });
       
       showSettingsAlert('danger', errorMessage);
   }
   
   return isValid;
}

/**
* Gather all settings data for submission
*/
function gatherSettingsData() {
   const settings = {};
   
   // Get course ID if editing
   const courseId = document.getElementById('course_id').value;
   if (courseId) {
       settings.course_id = courseId;
   }
   
   // Pricing settings
   settings.pricing_type = document.querySelector('input[name="pricingType"]:checked').value;
   settings.course_price = document.getElementById('coursePrice').value;
   settings.price_currency = document.getElementById('priceCurrency').value;
   settings.enable_discounts = document.getElementById('enableDiscounts').checked ? 1 : 0;
   
   if (document.getElementById('enableDiscounts').checked) {
       settings.discount_price = document.getElementById('discountPrice').value;
       settings.discount_expiry = document.getElementById('discountExpiry').value;
   }
   
   // Enrollment settings
   settings.access_level = document.getElementById('accessLevel').value;
   settings.enrollment_limit = document.getElementById('enrollmentLimit').value;
   settings.enable_enrollment_period = document.getElementById('enableEnrollmentPeriod').checked ? 1 : 0;
   
   if (document.getElementById('enableEnrollmentPeriod').checked) {
       settings.enrollment_start = document.getElementById('enrollmentStart').value;
       settings.enrollment_end = document.getElementById('enrollmentEnd').value;
   }
   
   // Display settings
   settings.course_level = document.getElementById('courseLevel').value;
   settings.duration_hours = document.getElementById('durationHours').value;
   settings.duration_minutes = document.getElementById('durationMinutes').value;
   settings.course_visibility = document.getElementById('courseVisibility').value;
   
   if (document.getElementById('courseVisibility').value === 'Password Protected') {
       settings.access_password = document.getElementById('accessPassword').value;
   }
   
   settings.is_featured = document.getElementById('isFeatured').checked ? 1 : 0;
   
   // Certificate settings
   settings.certificate_enabled = document.getElementById('certificateEnabled').checked ? 1 : 0;
   
   if (document.getElementById('certificateEnabled').checked) {
       settings.certificate_template = document.getElementById('certificateTemplate').value;
       settings.completion_criteria = document.getElementById('completionCriteria').value;
       
       if (document.getElementById('completionCriteria').value === 'percentage') {
           settings.completion_percentage = document.getElementById('completionPercentage').value;
       }
       
       settings.certificate_title = document.getElementById('certificateTitle').value;
   }
   
   return settings;
}

/**
* Show alert message for settings operations
*/
function showSettingsAlert(type, message) {
   // Remove any existing alerts
   document.querySelectorAll('.settings-alert').forEach(alert => {
       alert.remove();
   });
   
   // Create alert element
   const alertElement = document.createElement('div');
   alertElement.className = `alert alert-${type} settings-alert alert-dismissible fade show`;
   alertElement.innerHTML = `
       ${message}
       <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
   `;
   
   // Insert alert before the tabs
   const tabsElement = document.querySelector('.nav-tabs');
   tabsElement.parentNode.insertBefore(alertElement, tabsElement);
   
   // Auto-dismiss after 5 seconds
   setTimeout(() => {
       if (alertElement.parentNode) {
           const bsAlert = new bootstrap.Alert(alertElement);
           bsAlert.close();
       }
   }, 5000);
}

/**
* Load existing settings if editing a course
*/
function loadExistingSettings() {
   const courseId = document.getElementById('course_id').value;
   if (!courseId) return;
   
   // Fetch settings via AJAX
   $.ajax({
       url: 'ajax/get_course_settings.php',
       type: 'GET',
       data: { course_id: courseId },
       dataType: 'json',
       success: function(response) {
           if (response.success) {
               populateSettingsForm(response.settings);
           } else {
               console.error('Error loading settings:', response.message);
               showSettingsAlert('warning', 'Could not load existing settings: ' + response.message);
           }
       },
       error: function() {
           console.error('Failed to load settings');
           showSettingsAlert('warning', 'Failed to load existing settings. Using default values.');
       }
   });
}

/**
* Populate settings form with existing data
*/
function populateSettingsForm(settings) {
   if (!settings) return;
   
   // Pricing settings
   if (settings.pricing_type) {
       const pricingType = settings.pricing_type === 'free' ? 'pricingFree' : 'pricingPaid';
       document.getElementById(pricingType).checked = true;
       document.getElementById('paidCourseOptions').style.display = 
           settings.pricing_type === 'free' ? 'none' : 'block';
   }
   
   if (settings.course_price) {
       document.getElementById('coursePrice').value = settings.course_price;
   }
   
   if (settings.price_currency) {
       document.getElementById('priceCurrency').value = settings.price_currency;
   }
   
   if (settings.enable_discounts) {
       document.getElementById('enableDiscounts').checked = settings.enable_discounts == 1;
       document.getElementById('discountOptions').style.display = 
           settings.enable_discounts == 1 ? 'block' : 'none';
   }
   
   if (settings.discount_price) {
       document.getElementById('discountPrice').value = settings.discount_price;
   }
   
   if (settings.discount_expiry) {
       document.getElementById('discountExpiry').value = settings.discount_expiry;
   }
   
   // Enrollment settings
   if (settings.access_level) {
       document.getElementById('accessLevel').value = settings.access_level;
   }
   
   if (settings.enrollment_limit) {
       document.getElementById('enrollmentLimit').value = settings.enrollment_limit;
   }
   
   if (settings.enable_enrollment_period) {
       document.getElementById('enableEnrollmentPeriod').checked = settings.enable_enrollment_period == 1;
       document.getElementById('enrollmentPeriodOptions').style.display = 
           settings.enable_enrollment_period == 1 ? 'block' : 'none';
   }
   
   if (settings.enrollment_start) {
       document.getElementById('enrollmentStart').value = settings.enrollment_start;
   }
   
   if (settings.enrollment_end) {
       document.getElementById('enrollmentEnd').value = settings.enrollment_end;
   }
   
   // Display