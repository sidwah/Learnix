<?php

/**
 * Enhanced Helper Functions for Course Creation
 * File: ../includes/create-course-helpers.php
 * 
 * This file provides utility functions used throughout the course creation process,
 * including UI components, validation, navigation, and error handling.
 */
?>
<script>
    /**
     * CourseWizard Utilities
     * A comprehensive set of helper functions for course creation
     * with improved UI, accessibility, and functionality
     */

    /**
     * Enhanced Overlay System
     * Supports multiple types (loading, confirmation, alert)
     * with improved styling and animations
     */
    const OverlaySystem = {
        /**
         * Create a loading overlay with spinner and message
         * @param {string} message - Optional message to display
         * @param {Object} options - Optional configuration
         */
        createOverlay: function(message = "Loading...", options = {}) {
            // Remove existing overlay if any
            this.removeOverlay();

            // Default options
            const defaultOptions = {
                spinnerColor: 'primary',
                backgroundColor: 'rgba(255, 255, 255, 0.85)',
                blur: true,
                showProgress: false,
                zIndex: 9998,
                allowClose: false
            };

            // Merge options
            const settings = {
                ...defaultOptions,
                ...options
            };

            // Create overlay element
            const overlay = document.createElement('div');
            overlay.id = 'pageOverlay';
            overlay.className = 'page-overlay fade-in';
            overlay.setAttribute('role', 'dialog');
            overlay.setAttribute('aria-modal', 'true');
            overlay.setAttribute('aria-labelledby', 'overlayMessage');

            // Set styles
            Object.assign(overlay.style, {
                position: 'fixed',
                top: '0',
                left: '0',
                width: '100%',
                height: '100%',
                backgroundColor: settings.backgroundColor,
                backdropFilter: settings.blur ? 'blur(5px)' : 'none',
                zIndex: settings.zIndex,
                display: 'flex',
                flexDirection: 'column',
                justifyContent: 'center',
                alignItems: 'center',
                gap: '15px',
                transition: 'opacity 0.3s ease',
                opacity: '0'
            });

            // Create content container
            const contentContainer = document.createElement('div');
            contentContainer.className = 'overlay-content';
            contentContainer.style.padding = '30px';
            contentContainer.style.borderRadius = '10px';
            contentContainer.style.textAlign = 'center';
            contentContainer.style.maxWidth = '90%';

            // Add spinner
            const spinner = document.createElement('div');
            spinner.className = `spinner-border text-${settings.spinnerColor}`;
            spinner.setAttribute('role', 'status');
            spinner.style.width = '3rem';
            spinner.style.height = '3rem';
            spinner.innerHTML = '<span class="visually-hidden">Loading...</span>';
            contentContainer.appendChild(spinner);

            // Add message if provided
            if (message) {
                const messageElement = document.createElement('div');
                messageElement.id = 'overlayMessage';
                messageElement.className = `fw-semibold fs-5 text-${settings.spinnerColor} mt-3`;
                messageElement.textContent = message;
                contentContainer.appendChild(messageElement);
            }

            // Add progress bar if needed
            if (settings.showProgress) {
                const progressContainer = document.createElement('div');
                progressContainer.className = 'progress mt-3';
                progressContainer.style.width = '250px';
                progressContainer.style.height = '8px';
                progressContainer.setAttribute('role', 'progressbar');

                const progressBar = document.createElement('div');
                progressBar.id = 'overlayProgressBar';
                progressBar.className = `progress-bar progress-bar-striped progress-bar-animated bg-${settings.spinnerColor}`;
                progressBar.style.width = '0%';
                progressBar.setAttribute('aria-valuenow', '0');
                progressBar.setAttribute('aria-valuemin', '0');
                progressBar.setAttribute('aria-valuemax', '100');

                progressContainer.appendChild(progressBar);
                contentContainer.appendChild(progressContainer);

                const progressText = document.createElement('div');
                progressText.id = 'overlayProgressText';
                progressText.className = 'small mt-1 text-muted';
                progressText.textContent = '0%';
                contentContainer.appendChild(progressText);
            }

            // Add close button if allowed
            if (settings.allowClose) {
                const closeButton = document.createElement('button');
                closeButton.className = 'btn btn-light mt-3';
                closeButton.textContent = 'Cancel';
                closeButton.addEventListener('click', () => this.removeOverlay());
                contentContainer.appendChild(closeButton);
            }

            // Add to overlay
            overlay.appendChild(contentContainer);

            // Add to document
            document.body.appendChild(overlay);

            // Trigger fade in (separate to ensure transition works)
            setTimeout(() => {
                overlay.style.opacity = '1';
            }, 10);

            // Return overlay for reference
            return overlay;
        },

        /**
         * Remove the overlay with animation
         */
        removeOverlay: function() {
            const overlay = document.getElementById('pageOverlay');
            if (overlay) {
                // Fade out
                overlay.style.opacity = '0';

                // Remove after animation
                setTimeout(() => {
                    if (overlay.parentNode) {
                        overlay.parentNode.removeChild(overlay);
                    }
                }, 300);
            }
        },

        /**
         * Update overlay message
         * @param {string} message - New message to display
         */
        updateMessage: function(message) {
            const messageElement = document.getElementById('overlayMessage');
            if (messageElement) {
                messageElement.textContent = message;
            }
        },

        /**
         * Update progress bar in the overlay
         * @param {number} percentage - Progress percentage (0-100)
         */
        updateProgress: function(percentage) {
            const progressBar = document.getElementById('overlayProgressBar');
            const progressText = document.getElementById('overlayProgressText');

            if (progressBar) {
                const progress = Math.min(Math.max(percentage, 0), 100);
                progressBar.style.width = `${progress}%`;
                progressBar.setAttribute('aria-valuenow', progress);

                if (progressText) {
                    progressText.textContent = `${Math.round(progress)}%`;
                }
            }
        },

        /**
         * Show a confirmation dialog with custom buttons
         * @param {string} message - Confirmation message
         * @param {Function} onConfirm - Callback for confirmation
         * @param {Function} onCancel - Callback for cancellation
         * @param {Object} options - Custom options
         */
        showConfirmation: function(message, onConfirm, onCancel, options = {}) {
            // Default options
            const defaultOptions = {
                title: 'Confirmation',
                confirmText: 'Confirm',
                cancelText: 'Cancel',
                confirmClass: 'btn-primary',
                cancelClass: 'btn-light',
                icon: 'question'
            };

            // Merge options
            const settings = {
                ...defaultOptions,
                ...options
            };

            // Create overlay
            const overlay = document.createElement('div');
            overlay.id = 'confirmationOverlay';
            overlay.className = 'confirmation-overlay fade-in';
            overlay.setAttribute('role', 'dialog');
            overlay.setAttribute('aria-modal', 'true');
            overlay.setAttribute('aria-labelledby', 'confirmationTitle');

            // Set styles
            Object.assign(overlay.style, {
                position: 'fixed',
                top: '0',
                left: '0',
                width: '100%',
                height: '100%',
                backgroundColor: 'rgba(0, 0, 0, 0.5)',
                backdropFilter: 'blur(3px)',
                zIndex: '9999',
                display: 'flex',
                justifyContent: 'center',
                alignItems: 'center',
                transition: 'opacity 0.3s ease',
                opacity: '0'
            });

            // Create dialog
            const dialog = document.createElement('div');
            dialog.className = 'card';
            dialog.style.maxWidth = '450px';
            dialog.style.width = '90%';
            dialog.style.boxShadow = '0 15px 30px rgba(0,0,0,0.2)';
            dialog.style.borderRadius = '10px';
            dialog.style.overflow = 'hidden';

            // Get icon based on type
            let iconClass = 'mdi-help-circle';
            if (settings.icon === 'warning') iconClass = 'mdi-alert';
            if (settings.icon === 'info') iconClass = 'mdi-information';
            if (settings.icon === 'error') iconClass = 'mdi-alert-circle';
            if (settings.icon === 'success') iconClass = 'mdi-check-circle';

            // Add content
            dialog.innerHTML = `
<div class="card-header bg-light">
<h5 class="mb-0 d-flex align-items-center" id="confirmationTitle">
<i class="mdi ${iconClass} me-2"></i>
${settings.title}
</h5>
</div>
<div class="card-body p-4">
<p class="mb-4">${message}</p>
<div class="d-flex justify-content-end gap-2">
<button type="button" class="btn ${settings.cancelClass} cancel-btn">${settings.cancelText}</button>
<button type="button" class="btn ${settings.confirmClass} confirm-btn">${settings.confirmText}</button>
</div>
</div>
`;

            // Add to overlay
            overlay.appendChild(dialog);

            // Add to document
            document.body.appendChild(overlay);

            // Trigger fade in
            setTimeout(() => {
                overlay.style.opacity = '1';
            }, 10);

            // Add button event listeners
            const confirmBtn = dialog.querySelector('.confirm-btn');
            const cancelBtn = dialog.querySelector('.cancel-btn');

            if (confirmBtn) {
                confirmBtn.addEventListener('click', () => {
                    this.removeConfirmation();
                    if (typeof onConfirm === 'function') {
                        onConfirm();
                    }
                });
            }

            if (cancelBtn) {
                cancelBtn.addEventListener('click', () => {
                    this.removeConfirmation();
                    if (typeof onCancel === 'function') {
                        onCancel();
                    }
                });
            }

            // Add keyboard listeners
            document.addEventListener('keydown', function escHandler(e) {
                if (e.key === 'Escape') {
                    document.removeEventListener('keydown', escHandler);
                    this.removeConfirmation();
                    if (typeof onCancel === 'function') {
                        onCancel();
                    }
                }
                if (e.key === 'Enter') {
                    document.removeEventListener('keydown', escHandler);
                    this.removeConfirmation();
                    if (typeof onConfirm === 'function') {
                        onConfirm();
                    }
                }
            }.bind(this));

            // Return dialog for reference
            return dialog;
        },

        /**
         * Remove the confirmation dialog
         */
        removeConfirmation: function() {
            const overlay = document.getElementById('confirmationOverlay');
            if (overlay) {
                // Fade out
                overlay.style.opacity = '0';

                // Remove after animation
                setTimeout(() => {
                    if (overlay.parentNode) {
                        overlay.parentNode.removeChild(overlay);
                    }
                }, 300);
            }
        }
    };

    /**
     * Enhanced Notification System
     * Shows alerts with improved styling, stacking, and animations
     */
    const NotificationSystem = {
        container: null,
        notifications: [],
        maxNotifications: 3,

        /**
         * Initialize the notification container
         * @private
         */
        _initContainer: function() {
            if (this.container) return;

            this.container = document.createElement('div');
            this.container.id = 'notification-container';
            this.container.className = 'notification-container';
            this.container.setAttribute('aria-live', 'polite');

            Object.assign(this.container.style, {
                position: 'fixed',
                top: '20px',
                right: '20px',
                zIndex: '9999',
                display: 'flex',
                flexDirection: 'column',
                gap: '10px',
                maxWidth: '350px',
                width: 'calc(100% - 40px)'
            });

            document.body.appendChild(this.container);
        },

        /**
         * Show a notification
         * @param {string} type - Type of notification (success, danger, warning, info)
         * @param {string} message - The message to display
         * @param {number} duration - How long to show (ms)
         * @param {Object} options - Additional options
         * @return {HTMLElement} The created notification element
         */
        show: function(type, message, duration = 5000, options = {}) {
            // Initialize container if not already done
            this._initContainer();

            // Default options
            const defaultOptions = {
                title: null,
                icon: true,
                progress: true,
                dismissible: true
            };

            // Merge options
            const settings = {
                ...defaultOptions,
                ...options
            };

            // Get icon based on type
            let iconClass = '';
            switch (type) {
                case 'success':
                    iconClass = 'mdi-check-circle';
                    break;
                case 'danger':
                    iconClass = 'mdi-alert-circle';
                    break;
                case 'warning':
                    iconClass = 'mdi-alert';
                    break;
                case 'info':
                default:
                    iconClass = 'mdi-information';
                    break;
            }

            // Create notification element
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} alert-dismissible notification fade-in`;
            notification.setAttribute('role', 'alert');

            // Set styles
            Object.assign(notification.style, {
                margin: '0',
                borderRadius: '8px',
                boxShadow: '0 4px 15px rgba(0, 0, 0, 0.15)',
                borderLeft: '4px solid',
                borderLeftColor: type === 'success' ? 'var(--bs-success)' : (type === 'danger' ? 'var(--bs-danger)' :
                    (type === 'warning' ? 'var(--bs-warning)' : 'var(--bs-info)')),
                opacity: '0',
                transform: 'translateX(20px)',
                transition: 'all 0.3s ease',
                overflow: 'hidden'
            });

            // Create notification content
            let notificationContent = '';

            // Add title if provided
            if (settings.title) {
                notificationContent += `<h6 class="alert-heading mb-1">${settings.title}</h6>`;
            }

            // Add message with icon
            notificationContent += `
<div class="d-flex align-items-center">
${settings.icon ? `<i class="mdi ${iconClass} me-2" style="font-size: 1.25rem;"></i>` : ''}
<div>${message}</div>
</div>
`;

            // Add dismiss button if needed
            if (settings.dismissible) {
                notificationContent += `
<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
`;
            }

            // Set content
            notification.innerHTML = notificationContent;

            // Add progress bar if needed
            if (settings.progress && duration > 0) {
                const progressBar = document.createElement('div');
                progressBar.className = 'progress notification-progress';
                progressBar.style.position = 'absolute';
                progressBar.style.bottom = '0';
                progressBar.style.left = '0';
                progressBar.style.height = '3px';
                progressBar.style.width = '100%';
                progressBar.style.backgroundColor = 'rgba(0, 0, 0, 0.1)';
                progressBar.style.overflow = 'hidden';

                const progressInner = document.createElement('div');
                progressInner.className = `bg-${type}`;
                progressInner.style.height = '100%';
                progressInner.style.width = '100%';
                progressInner.style.transition = `width ${duration}ms linear`;

                progressBar.appendChild(progressInner);
                notification.appendChild(progressBar);

                // Start progress animation
                setTimeout(() => {
                    progressInner.style.width = '0%';
                }, 10);
            }

            // Add click event to close button
            const closeButton = notification.querySelector('.btn-close');
            if (closeButton) {
                closeButton.addEventListener('click', () => {
                    this.closeNotification(notification);
                });
            }

            // Add to container
            this.container.appendChild(notification);

            // Add to tracking array
            this.notifications.push(notification);

            // Remove oldest notification if too many
            if (this.notifications.length > this.maxNotifications) {
                const oldest = this.notifications.shift();
                this.closeNotification(oldest);
            }

            // Show notification with animation
            setTimeout(() => {
                notification.style.opacity = '1';
                notification.style.transform = 'translateX(0)';
            }, 10);

            // Auto-dismiss if duration is set
            if (duration > 0) {
                setTimeout(() => {
                    this.closeNotification(notification);
                }, duration);
            }

            return notification;
        },

        /**
         * Close a specific notification
         * @param {HTMLElement} notification - The notification to close
         */
        closeNotification: function(notification) {
            if (!notification || !notification.parentNode) return;

            // Add fade out animation
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(20px)';

            // Remove after animation
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }

                // Remove from tracking array
                const index = this.notifications.indexOf(notification);
                if (index > -1) {
                    this.notifications.splice(index, 1);
                }
            }, 300);
        },

        /**
         * Close all notifications
         */
        closeAll: function() {
            this.notifications.forEach(notification => {
                this.closeNotification(notification);
            });
        },

        /**
         * Show a success notification
         * @param {string} message - Message to display
         * @param {number} duration - How long to show
         * @param {Object} options - Additional options
         */
        success: function(message, duration, options) {
            return this.show('success', message, duration, options);
        },

        /**
         * Show an error notification
         * @param {string} message - Message to display
         * @param {number} duration - How long to show
         * @param {Object} options - Additional options
         */
        error: function(message, duration, options) {
            return this.show('danger', message, duration, options);
        },

        /**
         * Show a warning notification
         * @param {string} message - Message to display
         * @param {number} duration - How long to show
         * @param {Object} options - Additional options
         */
        warning: function(message, duration, options) {
            return this.show('warning', message, duration, options);
        },

        /**
         * Show an info notification
         * @param {string} message - Message to display
         * @param {number} duration - How long to show
         * @param {Object} options - Additional options
         */
        info: function(message, duration, options) {
            return this.show('info', message, duration, options);
        }
    };

    /**
     * Enhanced Form Validation System
     * Provides comprehensive validation for different field types
     */
    const ValidationSystem = {
        /**
         * Validate a single field
         * @param {HTMLElement} field - The field to validate
         * @param {Object} options - Validation options
         * @return {boolean} Is the field valid
         */
        validateField: function(field, options = {}) {
            if (!field) return false;

            // Get field type
            const type = field.type || field.tagName.toLowerCase();
            const value = field.value.trim();
            const isRequired = field.hasAttribute('required') || options.required;
            let isValid = true;
            let errorMessage = '';

            // Skip validation for disabled or hidden fields
            if (field.disabled || field.style.display === 'none') {
                return true;
            }

            // Basic required check
            if (isRequired && value === '') {
                isValid = false;
                errorMessage = options.requiredMessage || 'This field is required';
            } else {
                // Type-specific validation
                switch (type) {
                    case 'email':
                        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (value && !emailPattern.test(value)) {
                            isValid = false;
                            errorMessage = options.emailMessage || 'Please enter a valid email address';
                        }
                        break;

                    case 'number':
                    case 'range':
                        if (value && isNaN(parseFloat(value))) {
                            isValid = false;
                            errorMessage = options.numberMessage || 'Please enter a valid number';
                        }

                        // Check min/max
                        if (value && !isNaN(parseFloat(value))) {
                            const num = parseFloat(value);

                            if (field.hasAttribute('min') && num < parseFloat(field.getAttribute('min'))) {
                                isValid = false;
                                errorMessage = options.minMessage || `Minimum value is ${field.getAttribute('min')}`;
                            }

                            if (field.hasAttribute('max') && num > parseFloat(field.getAttribute('max'))) {
                                isValid = false;
                                errorMessage = options.maxMessage || `Maximum value is ${field.getAttribute('max')}`;
                            }
                        }
                        break;

                    case 'url':
                        try {
                            if (value) {
                                new URL(value);
                            }
                        } catch (_) {
                            isValid = false;
                            errorMessage = options.urlMessage || 'Please enter a valid URL';
                        }
                        break;

                    case 'file':
                        // Check if files are selected when required
                        if (isRequired && (!field.files || field.files.length === 0)) {
                            isValid = false;
                            errorMessage = options.fileRequiredMessage || 'Please select a file';
                        }

                        // Validate file type if accept attribute is set
                        if (field.files?.length > 0 && field.accept) {
                            const acceptedTypes = field.accept.split(',').map(type => type.trim());
                            const file = field.files[0];

                            // Check if file type matches any accepted type
                            const isAcceptedType = acceptedTypes.some(acceptedType => {
                                // Handle image/* or similar patterns
                                if (acceptedType.endsWith('/*')) {
                                    const baseType = acceptedType.slice(0, -2);
                                    return file.type.startsWith(baseType);
                                }

                                // Handle specific extensions like .jpg
                                if (acceptedType.startsWith('.')) {
                                    const ext = acceptedType.slice(1);
                                    return file.name.toLowerCase().endsWith(`.${ext}`);
                                }

                                // Handle specific mime types
                                return file.type === acceptedType;
                            });

                            if (!isAcceptedType) {
                                isValid = false;
                                errorMessage = options.fileTypeMessage || 'File type not supported';
                            }
                        }

                        // Validate file size if maxSize option is provided
                        if (field.files?.length > 0 && options.maxSize) {
                            const maxSizeBytes = options.maxSize * 1024 * 1024; // Convert MB to bytes
                            if (field.files[0].size > maxSizeBytes) {
                                isValid = false;
                                errorMessage = options.fileSizeMessage || `File size exceeds ${options.maxSize}MB limit`;
                            }
                        }
                        break;

                        // Custom pattern validation
                    default:
                        if (field.hasAttribute('pattern') && value) {
                            const pattern = new RegExp(field.getAttribute('pattern'));
                            if (!pattern.test(value)) {
                                isValid = false;
                                errorMessage = options.patternMessage || 'Please match the requested format';
                            }
                        }

                        // Minlength/maxlength for text inputs
                        if (field.hasAttribute('minlength') && value) {
                            const minLength = parseInt(field.getAttribute('minlength'));
                            if (value.length < minLength) {
                                isValid = false;
                                errorMessage = options.minLengthMessage || `Minimum length is ${minLength} characters`;
                            }
                        }

                        if (field.hasAttribute('maxlength') && value) {
                            const maxLength = parseInt(field.getAttribute('maxlength'));
                            if (value.length > maxLength) {
                                isValid = false;
                                errorMessage = options.maxLengthMessage || `Maximum length is ${maxLength} characters`;
                            }
                        }
                        break;
                }

                // Custom validation function
                if (isValid && options.validator && typeof options.validator === 'function') {
                    const customValidation = options.validator(value, field);
                    if (customValidation !== true) {
                        isValid = false;
                        errorMessage = customValidation || 'Invalid value';
                    }
                }
            }

            // Update field UI based on validation result
            this.updateFieldUI(field, isValid, errorMessage);

            return isValid;
        },

        /**
         * Update field UI based on validation result
         * @param {HTMLElement} field - The field to update
         * @param {boolean} isValid - Is the field valid
         * @param {string} errorMessage - Error message to display
         * @private
         */
        updateFieldUI: function(field, isValid, errorMessage = '') {
            if (!field) return;

            // Get the form control container
            const formGroup = field.closest('.form-group') || field.closest('.mb-3');

            // Remove existing validation classes
            field.classList.remove('is-valid', 'is-invalid');

            // Add appropriate validation class
            if (isValid) {
                field.classList.add('is-valid');
            } else {
                field.classList.add('is-invalid');
            }

            // Handle feedback message
            let feedback = formGroup?.querySelector('.invalid-feedback');

            // Create feedback element if it doesn't exist
            if (!feedback && !isValid) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';

                // Try to insert after the field
                if (field.nextElementSibling) {
                    field.parentNode.insertBefore(feedback, field.nextElementSibling);
                } else {
                    field.parentNode.appendChild(feedback);
                }
            }

            // Update feedback message
            if (feedback && !isValid) {
                feedback.textContent = errorMessage;
            }

            // Special handling for select2 and other enhanced inputs
            if (field.classList.contains('select2-hidden-accessible') && window.jQuery) {
                if (isValid) {
                    jQuery(field).next('.select2-container').removeClass('is-invalid').addClass('is-valid');
                } else {
                    jQuery(field).next('.select2-container').removeClass('is-valid').addClass('is-invalid');
                }
            }
        },

        /**
         * Validate a set of fields
         * @param {Array} fields - Array of field objects with id and options
         * @param {Object} options - Global validation options
         * @return {Object} Validation result with isValid flag and errorMessages
         */
        validateFields: function(fields, options = {}) {
            let isValid = true;
            let errorMessages = [];
            let firstInvalidField = null;

            fields.forEach(field => {
                const element = field.element || document.getElementById(field.id);
                if (!element) {
                    if (field.required !== false) { // Skip if explicitly marked as not required
                        isValid = false;
                        errorMessages.push(`Field ${field.name || field.id} not found`);
                    }
                    return;
                }

                // Merge global options with field-specific options
                const fieldOptions = {
                    ...options,
                    ...field.options,
                    required: field.required
                };

                // Validate the field
                const fieldIsValid = this.validateField(element, fieldOptions);

                // Update overall validation status
                if (!fieldIsValid) {
                    isValid = false;
                    errorMessages.push(field.name ? `${field.name} is invalid` : 'A field is invalid');

                    // Track first invalid field for scrolling
                    if (!firstInvalidField) {
                        firstInvalidField = element;
                    }
                }
            });

            // Scroll to first invalid field if any
            if (firstInvalidField && options.scrollToError !== false) {
                this.scrollToField(firstInvalidField);
            }

            return {
                isValid,
                errorMessages,
                firstInvalidField
            };
        },

        /**
         * Scroll to a field smoothly
         * @param {HTMLElement} field - The field to scroll to
         * @private
         */
        scrollToField: function(field) {
            if (!field) return;

            // Find appropriate container to scroll
            const container = field.closest('.card-body') || field.closest('.tab-pane') || window;

            // Get the field position
            const fieldRect = field.getBoundingClientRect();
            const containerRect = container === window ? {
                    top: 0,
                    left: 0
                } :
                container.getBoundingClientRect();

            // Calculate scroll position
            const scrollTop = field.offsetTop - containerRect.top - 100; // 100px offset for spacing

            // Scroll smoothly
            if (container === window) {
                window.scrollTo({
                    top: scrollTop,
                    behavior: 'smooth'
                });
            } else {
                container.scrollTo({
                    top: scrollTop,
                    behavior: 'smooth'
                });
            }

            // Flash highlight the field
            field.style.transition = 'background-color 0.3s ease';
            field.style.backgroundColor = 'rgba(253, 237, 237, 1)';

            setTimeout(() => {
                field.style.backgroundColor = '';

                // Focus the field after scrolling
                field.focus();
            }, 1000);
        }
    };

    /**
     * Navigation and Progress System
     * Handles tab navigation and wizard progress
     */
    const NavigationSystem = {
        /**
         * Get the current active tab
         * @return {HTMLElement} The active tab element
         */
        getActiveTab: function() {
            return document.querySelector('.nav-link.active');
        },

        /**
         * Get the current tab index
         * @return {number} Index of the active tab (0-based)
         */
        getCurrentTabIndex: function() {
            const activeTab = this.getActiveTab();
            if (!activeTab) return 0;

            const tabs = document.querySelectorAll('.nav-link');
            return Array.from(tabs).indexOf(activeTab);
        },

        /**
         * Get the current step number
         * @return {number} Current step (1-based)
         */
        getCurrentStep: function() {
            return this.getCurrentTabIndex() + 1;
        },

        /**
         * Get the total number of steps
         * @return {number} Total number of steps
         */
        getTotalSteps: function() {
            return document.querySelectorAll('.nav-link').length;
        },

        /**
         * Go to a specific tab by index
         * @param {number} index - Tab index (0-based)
         */
        goToTab: function(index) {
            const tabs = document.querySelectorAll('.nav-link');
            if (index < 0 || index >= tabs.length) return;

            const tab = new bootstrap.Tab(tabs[index]);
            tab.show();
        },

        /**
         * Go to a specific step by number
         * @param {number} step - Step number (1-based)
         */
        goToStep: function(step) {
            this.goToTab(step - 1);
        },

        /**
         * Go to the next step
         * @return {number} New step number
         */
        nextStep: function() {
            const currentIndex = this.getCurrentTabIndex();
            const nextIndex = currentIndex + 1;

            if (nextIndex < this.getTotalSteps()) {
                this.goToTab(nextIndex);
                return nextIndex + 1;
            }

            return currentIndex + 1;
        },

        /**
         * Go to the previous step
         * @return {number} New step number
         */
        prevStep: function() {
            const currentIndex = this.getCurrentTabIndex();
            const prevIndex = currentIndex - 1;

            if (prevIndex >= 0) {
                this.goToTab(prevIndex);
                return prevIndex + 1;
            }

            return currentIndex + 1;
        },

        /**
         * Update the progress bar
         * @param {number} percentage - Progress percentage (0-100)
         */
        updateProgressBar: function(percentage) {
            // Find the progress bar
            const progressBar = document.querySelector('#wizard-progress .progress-bar') ||
                document.querySelector('#bar .progress-bar');

            if (progressBar) {
                progressBar.style.width = `${percentage}%`;
                progressBar.setAttribute('aria-valuenow', percentage);
            }
        },

        /**
         * Update progress based on current step
         */
        updateProgress: function() {
            const currentStep = this.getCurrentStep();
            const totalSteps = this.getTotalSteps();
            const percentage = ((currentStep - 1) / (totalSteps - 1)) * 100;

            this.updateProgressBar(percentage);
        }
    };

    /**
     * Data Persistence System
     * Saves form data to prevent loss on page refresh or navigation
     */
    const DataPersistenceSystem = {
        /**
         * Save form data to local storage
         * @param {string} formId - Form ID
         * @param {string} courseId - Course ID
         */
        saveFormData: function(formId = 'createCourseForm', courseId = null) {
            const form = document.getElementById(formId);
            if (!form) return;

            const formData = new FormData(form);
            const data = {};

            // Convert FormData to object
            for (const [key, value] of formData.entries()) {
                // Skip file inputs, they can't be saved to localStorage
                if (value instanceof File) continue;

                // Handle array inputs (name contains [])
                if (key.includes('[]')) {
                    const baseKey = key.replace('[]', '');
                    if (!data[baseKey]) {
                        data[baseKey] = [];
                    }
                    data[baseKey].push(value);
                } else {
                    data[key] = value;
                }
            }

            // Use course ID if available, otherwise use 'newCourse'
            const storageKey = `courseForm_${courseId || 'newCourse'}`;

            try {
                localStorage.setItem(storageKey, JSON.stringify({
                    data: data,
                    timestamp: Date.now()
                }));
            } catch (e) {
                console.warn('Failed to save form data to localStorage:', e);
            }
        },

        /**
         * Load form data from local storage
         * @param {string} formId - Form ID
         * @param {string} courseId - Course ID
         * @return {boolean} Whether data was loaded
         */
        loadFormData: function(formId = 'createCourseForm', courseId = null) {
            const form = document.getElementById(formId);
            if (!form) return false;

            // Use course ID if available, otherwise use 'newCourse'
            const storageKey = `courseForm_${courseId || 'newCourse'}`;

            try {
                const savedData = localStorage.getItem(storageKey);
                if (!savedData) return false;

                const {
                    data,
                    timestamp
                } = JSON.parse(savedData);

                // Check if data is too old (24 hours)
                const isExpired = (Date.now() - timestamp) > (24 * 60 * 60 * 1000);
                if (isExpired) {
                    localStorage.removeItem(storageKey);
                    return false;
                }

                // Fill form fields
                Object.entries(data).forEach(([key, value]) => {
                    // Handle array values
                    if (Array.isArray(value)) {
                        // Special handling for certain array fields
                        if (key === 'learningOutcomes') {
                            this._restoreLearningOutcomes(value);
                        } else {
                            // Generic array field handling
                            this._restoreArrayField(key, value);
                        }
                    } else {
                        // Handle single values
                        const field = form.querySelector(`[name="${key}"]`);
                        if (field) {
                            field.value = value;
                        }
                    }
                });

                return true;
            } catch (e) {
                console.warn('Failed to load form data from localStorage:', e);
                return false;
            }
        },

        /**
         * Restore learning outcomes
         * @param {Array} outcomes - Array of learning outcomes
         * @private
         */
        _restoreLearningOutcomes: function(outcomes) {
            const container = document.getElementById('learningOutcomesContainer');
            if (!container) return;

            // Clear existing outcomes
            container.innerHTML = '';

            // Add each outcome
            outcomes.forEach(outcome => {
                const inputGroup = document.createElement('div');
                inputGroup.classList.add('input-group', 'mb-2');

                const input = document.createElement('input');
                input.type = 'text';
                input.name = 'learningOutcomes[]';
                input.className = 'form-control learning-outcome-input';
                input.placeholder = 'Enter a learning outcome';
                input.value = outcome;
                input.required = true;

                inputGroup.appendChild(input);
                container.appendChild(inputGroup);
            });

            // Update buttons if function exists
            if (typeof updateOutcomeButtons === 'function') {
                updateOutcomeButtons();
            }
        },

        /**
         * Restore a generic array field
         * @param {string} key - Field name
         * @param {Array} values - Field values
         * @private
         */
        _restoreArrayField: function(key, values) {
            // Find all fields with this name
            const fields = document.querySelectorAll(`[name="${key}[]"]`);

            // If we have the right number of fields, fill them
            if (fields.length === values.length) {
                fields.forEach((field, index) => {
                    field.value = values[index];
                });
            } else if (fields.length < values.length) {
                // Need to add more fields
                fields.forEach((field, index) => {
                    field.value = values[index];
                });

                // Handle special cases for common field types
                const firstField = fields[0];
                if (firstField) {
                    // Special handling based on field type
                    if (firstField.classList.contains('section-title')) {
                        // Section title - need to add more sections
                        for (let i = fields.length; i < values.length; i++) {
                            // Try to use existing add function
                            if (typeof addSection === 'function') {
                                const newSection = addSection();
                                const titleInput = newSection.querySelector('.section-title');
                                if (titleInput) {
                                    titleInput.value = values[i];
                                }
                            }
                        }
                    }
                    // Add more special cases as needed
                }
            }
            // If we have too many fields, just fill what we can
        },

        /**
         * Clear saved form data
         * @param {string} courseId - Course ID
         */
        clearSavedData: function(courseId = null) {
            const storageKey = `courseForm_${courseId || 'newCourse'}`;
            try {
                localStorage.removeItem(storageKey);
            } catch (e) {
                console.warn('Failed to clear form data from localStorage:', e);
            }
        }
    };

    /**
     * Utility Functions
     * General utility functions for the course wizard
     */
    const WizardUtils = {
        /**
         * Debounce a function to prevent multiple rapid calls
         * @param {Function} func - Function to debounce
         * @param {number} wait - Wait time in ms
         * @return {Function} Debounced function
         */
        debounce: function(func, wait = 300) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        },

        /**
         * Format a file size for display
         * @param {number} bytes - Size in bytes
         * @return {string} Formatted size
         */
        formatFileSize: function(bytes) {
            if (bytes === 0) return '0 Bytes';

            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));

            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },

        /**
         * Generate a unique ID
         * @return {string} Unique ID
         */
        generateUniqueId: function() {
            return 'id_' + Date.now().toString(36) + Math.random().toString(36).substr(2, 5);
        },

        /**
         * Check if an element is visible in viewport
         * @param {HTMLElement} element - Element to check
         * @return {boolean} Is element visible
         */
        isElementInViewport: function(element) {
            if (!element) return false;

            const rect = element.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        },

        /**
         * Copy text to clipboard
         * @param {string} text - Text to copy
         * @return {Promise} Result of copy operation
         */
        copyToClipboard: function(text) {
            // Use Clipboard API if available
            if (navigator.clipboard && navigator.clipboard.writeText) {
                return navigator.clipboard.writeText(text);
            }

            // Fallback method
            return new Promise((resolve, reject) => {
                try {
                    const textArea = document.createElement('textarea');
                    textArea.value = text;
                    textArea.style.position = 'fixed';
                    textArea.style.left = '-999999px';
                    textArea.style.top = '-999999px';
                    document.body.appendChild(textArea);
                    textArea.focus();
                    textArea.select();

                    const success = document.execCommand('copy');
                    document.body.removeChild(textArea);

                    if (success) {
                        resolve();
                    } else {
                        reject(new Error('Copy command was unsuccessful'));
                    }
                } catch (err) {
                    reject(err);
                }
            });
        }
    };

    // Create backward compatibility for existing functions
    // These are the original function names, mapped to the enhanced versions

    /**
     * Create loading overlay with message
     * @param {string} message - Optional message
     */
    function createOverlay(message = "Loading...") {
        return OverlaySystem.createOverlay(message);
    }

    /**
     * Remove the loading overlay
     */
    function removeOverlay() {
        OverlaySystem.removeOverlay();
    }

    /**
     * Show alert notification
     * @param {string} type - Alert type (success, danger)
     * @param {string} message - Alert message
     */
    function showAlert(type, message) {
        return NotificationSystem.show(type, message);
    }

    /**
     * Update progress bar
     * @param {number} percentage - Progress percentage
     */
    function updateProgressBar(percentage) {
        NavigationSystem.updateProgressBar(percentage);
    }

    /**
     * Move to the next tab
     */
    function moveToNextTab() {
        NavigationSystem.nextStep();
    }

    /**
     * Move to the previous tab
     */
    function moveToPrevTab() {
        NavigationSystem.prevStep();
    }

    /**
     * Validate required fields
     * @param {Array} fields - Array of field objects
     * @return {Object} Validation result
     */
    function validateFields(fields) {
        const formattedFields = fields.map(field => ({
            id: field.id,
            name: field.name,
            required: true,
            options: {}
        }));

        return ValidationSystem.validateFields(formattedFields);
    }

    /**
     * Handle server error with context
     * @param {Error} error - Error object
     * @param {string} context - Error context
     */
    function handleServerError(error, context = 'operation') {
        // Determine message
        let message = `Error during ${context}`;

        if (error.message) {
            message += `: ${error.message}`;
        } else if (typeof error === 'string') {
            message += `: ${error}`;
        } else {
            message += '. Please try again.';
        }

        // Show alert
        NotificationSystem.error(message);
    }

    /**
     * Navigate to a specific step
     * @param {number} step - Step number (1-based)
     */
    function navigateToStep(step) {
        NavigationSystem.goToStep(step);
    }

    // Add CSS for animations and other styling
    document.addEventListener('DOMContentLoaded', function() {
        // Create style element for animations
        const style = document.createElement('style');
        style.textContent = `
@keyframes fade-in {
from { opacity: 0; }
to { opacity: 1; }
}

@keyframes fade-out {
from { opacity: 1; }
to { opacity: 0; }
}

.fade-in {
animation: fade-in 0.3s ease forwards;
}

.fade-out {
animation: fade-out 0.3s ease forwards;
}

/* Improved loading spinner */
@keyframes spin {
0% { transform: rotate(0deg); }
100% { transform: rotate(360deg); }
}

/* Notification styling */
.notification {
position: relative;
overflow: hidden;
}

.notification-progress {
position: absolute;
bottom: 0;
left: 0;
width: 100%;
height: 3px;
background-color: rgba(0, 0, 0, 0.1);
}

/* Form validation styling */
.is-invalid:not(form) {
border-color: var(--bs-danger);
padding-right: calc(1.5em + 0.75rem);
background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
background-repeat: no-repeat;
background-position: right calc(0.375em + 0.1875rem) center;
background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.is-valid:not(form) {
border-color: var(--bs-success);
padding-right: calc(1.5em + 0.75rem);
background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
background-repeat: no-repeat;
background-position: right calc(0.375em + 0.1875rem) center;
background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}
`;

        // Add style to head
        document.head.appendChild(style);
    });
</script>