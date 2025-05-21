<?php
// Authentication check
require_once '../backend/auth/admin/admin-auth-check.php';

// Set page title
$pageTitle = "General Settings - Admin | Learnix";

include_once '../includes/admin/header.php';
include_once '../includes/admin/sidebar.php';
include_once '../includes/admin/navbar.php';
?>

<!-- Loading Overlay -->
<div class="custom-overlay" id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center; flex-direction: column;">
    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
        <span class="visually-hidden">Loading...</span>
    </div>
    <div class="text-white mt-3" id="loading-message">Processing...</div>
</div>

<!-- Toast Notification -->
<div class="bs-toast toast toast-placement-ex m-2 fade bg-danger top-0 end-0" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000" id="errorToast" style="z-index: 9999; position: fixed;">
    <div class="toast-header">
        <i class="bx bx-bell me-2"></i>
        <div class="me-auto fw-semibold">Error</div>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body" id="errorToastMessage"></div>
</div>

<div class="bs-toast toast toast-placement-ex m-2 fade bg-success top-0 end-0" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000" id="successToast" style="z-index: 9999; position: fixed;">
    <div class="toast-header">
        <i class="bx bx-check me-2"></i>
        <div class="me-auto fw-semibold">Success</div>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body" id="successToastMessage"></div>
</div>
<!-- /Toast Notification -->

<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">General Settings</h4>

    <!-- Platform Media -->
    <div class="card mb-4">
        <h5 class="card-header">Platform Media</h5>
        <div class="card-body">
            <form id="platformMediaForm" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="logo" class="form-label">Upload Logo (PNG, JPG, JPEG)</label>
                    <input type="file" class="form-control" id="logo" name="logo" accept=".png,.jpg,.jpeg">
                    <div id="logoPreview" class="mt-2"></div>
                </div>
                <div class="mb-3">
                    <label for="favicon" class="form-label">Upload Favicon (ICO, PNG)</label>
                    <input type="file" class="form-control" id="favicon" name="favicon" accept=".ico,.png">
                    <div id="faviconPreview" class="mt-2"></div>
                </div>
                <button type="submit" class="btn btn-primary">Save Media</button>
            </form>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="card mb-4">
        <h5 class="card-header">Contact Information</h5>
        <div class="card-body">
            <form id="contactInfoForm">
                <div class="mb-3">
                    <label for="supportEmail" class="form-label">Support Email</label>
                    <input type="email" class="form-control" id="supportEmail" name="support_email" placeholder="support@learnix.com">
                </div>
                <div class="mb-3">
                    <label for="supportPhone" class="form-label">Support Phone</label>
                    <input type="text" class="form-control" id="supportPhone" name="support_phone" placeholder="+1-800-555-1234">
                </div>
                <button type="submit" class="btn btn-primary">Save Contact Info</button>
            </form>
        </div>
    </div>

    <!-- System Control -->
    <div class="card mb-4">
        <h5 class="card-header">System Control</h5>
        <div class="card-body">
            <form id="systemControlForm">
                <div class="mb-3">
                    <label for="maintenanceMode" class="form-label">Maintenance Mode</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="maintenanceMode" name="maintenance_mode">
                        <label class="form-check-label" for="maintenanceMode">Enable Maintenance Mode</label>
                    </div>
                    <small class="text-muted">When enabled, non-admin users will see a maintenance page.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Force Password Reset</label>
                    <button type="button" class="btn btn-warning" id="forcePasswordReset">Force Password Reset for All Users</button>
                    <small class="text-muted">This will require all users to reset their passwords on next login.</small>
                </div>
                <button type="submit" class="btn btn-primary">Save System Settings</button>
            </form>
        </div>
    </div>
</div>
<!-- / Content -->

<?php include_once '../includes/admin/footer.php'; ?>

<!-- JavaScript for Form Handling and AJAX -->
<script>
    // Toast initialization
    const successToast = new bootstrap.Toast(document.getElementById('successToast'));
    const errorToast = new bootstrap.Toast(document.getElementById('errorToast'));

    // Show loading overlay
    function showLoading(message = 'Processing...') {
        document.getElementById('loading-message').textContent = message;
        document.getElementById('loadingOverlay').style.display = 'flex';
    }

    // Hide loading overlay
    function hideLoading() {
        document.getElementById('loadingOverlay').style.display = 'none';
    }

    // Show toast
    function showToast(type, message) {
        const toast = type === 'success' ? successToast : errorToast;
        const messageElement = document.getElementById(type + 'ToastMessage');
        messageElement.textContent = message;
        toast.show();
    }

    // Load current settings
    function loadSettings() {
        showLoading('Loading settings...');
        fetch('../backend/admin/get_settings.php')
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (!data.success) {
                    showToast('error', data.message || 'Failed to load settings');
                    return;
                }

                const settings = data.data;
                // Update contact info form
                document.getElementById('supportEmail').value = settings.support_email || '';
                document.getElementById('supportPhone').value = settings.support_phone || '';
                // Update maintenance mode toggle
                document.getElementById('maintenanceMode').checked = !!settings.maintenance_mode;
                // Update media previews
                if (settings.logo_path) {
                    document.getElementById('logoPreview').innerHTML = `<img src="http://localhost:8888/learnix/${settings.logo_path}" alt="Logo" style="max-width: 200px;">`;
                }
                if (settings.favicon_path) {
                    document.getElementById('faviconPreview').innerHTML = `<img src="http://localhost:8888/learnix/${settings.favicon_path}" alt="Favicon" style="max-width: 32px;">`;
                }
            })
            .catch(error => {
                hideLoading();
                showToast('error', 'Network error: ' + error.message);
            });
    }

    // Handle Platform Media form submission
    document.getElementById('platformMediaForm').addEventListener('submit', function(e) {
        e.preventDefault();
        showLoading('Uploading media...');

        const formData = new FormData(this);
        fetch('../backend/admin/update_settings.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (!data.success) {
                    showToast('error', data.message || 'Failed to update media');
                    return;
                }
                showToast('success', data.message);
                loadSettings(); // Refresh previews
            })
            .catch(error => {
                hideLoading();
                showToast('error', 'Network error: ' + error.message);
            });
    });

    // Handle Contact Info form submission
    document.getElementById('contactInfoForm').addEventListener('submit', function(e) {
        e.preventDefault();
        showLoading('Saving contact info...');

        const formData = new FormData(this);
        fetch('../backend/admin/update_settings.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (!data.success) {
                    showToast('error', data.message || 'Failed to update contact info');
                    return;
                }
                showToast('success', data.message);
            })
            .catch(error => {
                hideLoading();
                showToast('error', 'Network error: ' + error.message);
            });
    });

    // Handle System Control form submission
    document.getElementById('systemControlForm').addEventListener('submit', function(e) {
        e.preventDefault();
        showLoading('Saving system settings...');

        const formData = new FormData();
        formData.append('maintenance_mode', document.getElementById('maintenanceMode').checked ? 1 : 0);

        fetch('../backend/admin/update_settings.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (!data.success) {
                    showToast('error', data.message || 'Failed to update system settings');
                    return;
                }
                showToast('success', data.message);
            })
            .catch(error => {
                hideLoading();
                showToast('error', 'Network error: ' + error.message);
            });
    });

    // Handle Force Password Reset button
    document.getElementById('forcePasswordReset').addEventListener('click', function() {
        if (!confirm('Are you sure you want to force a password reset for all users?')) {
            return;
        }
        showLoading('Forcing password reset...');

        const formData = new FormData();
        formData.append('force_password_reset', 1);

        fetch('../backend/admin/update_settings.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (!data.success) {
                    showToast('error', data.message || 'Failed to force password reset');
                    return;
                }
                showToast('success', data.message);
            })
            .catch(error => {
                hideLoading();
                showToast('error', 'Network error: ' + error.message);
            });
    });

    // Load settings on page load
    document.addEventListener('DOMContentLoaded', loadSettings);
</script>