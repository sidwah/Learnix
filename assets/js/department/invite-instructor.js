document.addEventListener('DOMContentLoaded', function() {
    // Initialize toast
    const toastEl = document.getElementById('liveToast');
    const toast = new bootstrap.Toast(toastEl);
    
    // Initialize confirmation modal
    const confirmationModalEl = document.getElementById('confirmationModal');
    const confirmationModal = new bootstrap.Modal(confirmationModalEl);
    
    // Form elements
    const inviteForm = document.getElementById('inviteInstructorForm');
    const cancelButton = document.getElementById('cancelButton');
    
    // Function to show toast with custom settings
    function showToast(type, title, message) {
        const iconDiv = document.getElementById('toastIcon');
        
        if (type === 'success') {
            iconDiv.className = 'flex-shrink-0 rounded-circle bg-success bg-opacity-10 text-success p-2 d-flex align-items-center justify-content-center me-2';
            iconDiv.innerHTML = '<i class="bi bi-check-lg fs-6"></i>';
        } else {
            iconDiv.className = 'flex-shrink-0 rounded-circle bg-danger bg-opacity-10 text-danger p-2 d-flex align-items-center justify-content-center me-2';
            iconDiv.innerHTML = '<i class="bi bi-x-lg fs-6"></i>';
        }
        
        iconDiv.style.width = '24px';
        iconDiv.style.height = '24px';
        
        document.getElementById('toastTitle').textContent = title;
        document.getElementById('toastBody').textContent = message;
        document.getElementById('toastTime').textContent = 'Just now';
        
        toast.show();
    }
    
    // Show Loading Overlay
    function showOverlay(message = null) {
        // Remove any existing overlay
        const existingOverlay = document.querySelector('.custom-overlay');
        if (existingOverlay) {
            existingOverlay.remove();
        }

        // Create new overlay
        const overlay = document.createElement('div');
        overlay.className = 'custom-overlay';
        overlay.innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        ${message ? `<div class="text-white ms-3">${message}</div>` : ''}
        `;

        document.body.appendChild(overlay);
    }

    // Remove Loading Overlay
    function removeOverlay() {
        const overlay = document.querySelector('.custom-overlay');
        if (overlay) {
            overlay.remove();
        }
    }
    
    // Form submit handler
    inviteForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate form data
        const email = document.getElementById('instructorEmail').value.trim();
        const firstName = document.getElementById('instructorFirstName').value.trim();
        const lastName = document.getElementById('instructorLastName').value.trim();
        
        if (!email || !firstName || !lastName) {
            showToast('error', 'Validation Error', 'Please complete all required fields.');
            return;
        }
        
        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showToast('error', 'Validation Error', 'Please enter a valid email address.');
            return;
        }
        
        // Set email in confirmation modal
        document.getElementById('confirmEmail').textContent = email;
        
        // Show confirmation modal
        confirmationModal.show();
        
        // Store form data in the confirm button
        document.getElementById('confirmSendBtn').dataset.formData = JSON.stringify({
            email: email,
            firstName: firstName,
            lastName: lastName,
            notes: document.getElementById('additionalNotes').value.trim()
        });
    });
    
    // Confirm send button handler
    document.getElementById('confirmSendBtn').addEventListener('click', function() {
        // Hide confirmation modal
        confirmationModal.hide();
        
        // Get form data
        const formData = JSON.parse(this.dataset.formData);
        
        // Show loading overlay
        showOverlay('Sending invitation...');
        
        // Send AJAX request to backend
        fetch('../backend/auth/department/add_instructor.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || 'An error occurred');
                });
            }
            return response.json();
        })
        .then(data => {
            // Hide loading overlay
            removeOverlay();
            
            // Show success toast
            showToast('success', 'Invitation Sent', `Invitation sent successfully to ${formData.email}`);
            
            // Reset form
            inviteForm.reset();
            
            // Refresh pending invitations list if available
            if (typeof refreshPendingInvitations === 'function') {
                refreshPendingInvitations();
            } else {
                // If we don't have a refresh function, reload after 2 seconds
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }
        })
        .catch(error => {
            // Hide loading overlay
            removeOverlay();
            
            // Get the error message
            let errorMessage = error.message || `Failed to send invitation to ${formData.email}. Please try again.`;
            let errorTitle = 'Sending Failed';
            
            // If the error is about a duplicate invitation or existing instructor
            if (errorMessage.includes('already been sent') || 
                errorMessage.includes('already associated with your department')) {
                errorTitle = 'Instructor Already Exists';
            }
            
            // Show error toast
            showToast('error', errorTitle, errorMessage);
        });
    });
    
    // Cancel button handler
    cancelButton.addEventListener('click', function() {
        // Reset form
        inviteForm.reset();
        
        // Show toast notification
        showToast('success', 'Form Reset', 'The form has been reset.');
    });
    
    // Clean up modal backdrop when closed
    confirmationModalEl.addEventListener('hidden.bs.modal', function() {
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
        document.body.classList.remove('modal-open');
    });
    
    // Initialize tooltips if they exist on the page
    if (typeof bootstrap.Tooltip !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Handle resend and cancel operations
    document.addEventListener('click', function(e) {
        // For elements added dynamically, use delegated event handling
        
        // Resend invitation
        if (e.target && e.target.closest('.btn-resend')) {
            const button = e.target.closest('.btn-resend');
            const id = button.dataset.id;
            const email = button.dataset.email;
            
            document.getElementById('resendEmail').textContent = email;
            document.getElementById('confirmResendBtn').dataset.id = id;
            
            const resendModal = new bootstrap.Modal(document.getElementById('resendInvitationModal'));
            resendModal.show();
        }
        
        // Cancel invitation
        if (e.target && e.target.closest('.btn-cancel')) {
            const button = e.target.closest('.btn-cancel');
            const id = button.dataset.id;
            const email = button.dataset.email;
            
            document.getElementById('cancelEmail').textContent = email;
            document.getElementById('confirmCancelBtn').dataset.id = id;
            
            const cancelModal = new bootstrap.Modal(document.getElementById('cancelInvitationModal'));
            cancelModal.show();
        }
    });
    
    // Confirm resend button
    if (document.getElementById('confirmResendBtn')) {
        document.getElementById('confirmResendBtn').addEventListener('click', function() {
            const id = this.dataset.id;
            const resendModal = bootstrap.Modal.getInstance(document.getElementById('resendInvitationModal'));
            
            // Hide modal and show loading
            resendModal.hide();
            showOverlay('Resending invitation...');
            
            // Send AJAX request to backend
            fetch('../backend/auth/department/resend_invitation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: id
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'An error occurred');
                    });
                }
                return response.json();
            })
            .then(data => {
                // Hide loading overlay
                removeOverlay();
                
                // Show success toast
                showToast('success', 'Invitation Resent', `Invitation has been resent successfully to ${data.data.email}`);
                
                // Refresh the page after a short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            })
            .catch(error => {
                // Hide loading overlay
                removeOverlay();
                
                // Show error toast
                showToast('error', 'Resend Failed', error.message || 'Failed to resend invitation. Please try again.');
            });
        });
    }
    
    // Confirm cancel button
    if (document.getElementById('confirmCancelBtn')) {
        document.getElementById('confirmCancelBtn').addEventListener('click', function() {
            const id = this.dataset.id;
            const cancelModal = bootstrap.Modal.getInstance(document.getElementById('cancelInvitationModal'));
            
            // Hide modal and show loading
            cancelModal.hide();
            showOverlay('Cancelling invitation...');
            
            // Send AJAX request to backend
            fetch('../backend/auth/department/cancel_invitation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: id
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'An error occurred');
                    });
                }
                return response.json();
            })
            .then(data => {
                // Hide loading overlay
                removeOverlay();
                
                // Show success toast
                showToast('success', 'Invitation Cancelled', `Invitation to ${data.data.email} has been cancelled successfully.`);
                
                // Refresh the page after a short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            })
            .catch(error => {
                // Hide loading overlay
                removeOverlay();
                
                // Show error toast
                showToast('error', 'Cancellation Failed', error.message || 'Failed to cancel invitation. Please try again.');
            });
        });
    }
    
    // Refresh button
    if (document.getElementById('refreshInvitations')) {
        document.getElementById('refreshInvitations').addEventListener('click', function() {
            showOverlay('Refreshing invitations...');
            
            // Simply reload the page
            setTimeout(() => {
                window.location.reload();
            }, 800);
        });
    }
    
    // Function to refresh pending invitations - can be called from other parts of the page
    function refreshPendingInvitations() {
        // Show loading overlay
        showOverlay('Refreshing invitations...');
        
        // In a more advanced implementation, this would fetch fresh data via AJAX
        // For now, just reload the page after a short delay
        setTimeout(() => {
            window.location.reload();
        }, 800);
    }
});