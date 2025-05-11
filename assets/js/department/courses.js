// assets/js/department/courses.js

document.addEventListener('DOMContentLoaded', function() {
    // Initialize variables
    const viewSwitchButtons = document.querySelectorAll('[data-view]');
    const cardsView = document.getElementById('courses-cards');
    const tableView = document.getElementById('courses-table');
    const searchInput = document.getElementById('courseSearch');
    const statusFilter = document.getElementById('statusFilter');
    const categoryFilter = document.getElementById('categoryFilter');
    const levelFilter = document.getElementById('levelFilter');
    const sortFilter = document.getElementById('sortFilter');
    const noResults = document.getElementById('noResults');
    
    let currentView = 'cards';
    let courses = [];
    let currentFilters = {
        search: '',
        status: '',
        category: '',
        level: '',
        sort: 'newest'
    };
    
    // Initialize tooltips
    initializeTooltips();
    
    // Initialize view
    initializeView();
    
    // Load initial data
    loadCourses();
    
    // Event listeners
    setupEventListeners();
    
    // Helper Functions
    function initializeTooltips() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    function initializeView() {
        // Set initial view to cards
        cardsView.style.display = 'flex';
        tableView.style.display = 'none';
    }
    
    function setupEventListeners() {
        // View switching
        viewSwitchButtons.forEach(button => {
            button.addEventListener('click', handleViewSwitch);
        });
        
        // Search and filters
        searchInput.addEventListener('input', debounce(handleSearch, 300));
        statusFilter.addEventListener('change', handleFilterChange);
        categoryFilter.addEventListener('change', handleFilterChange);
        levelFilter.addEventListener('change', handleFilterChange);
        sortFilter.addEventListener('change', handleFilterChange);
        
        // Course actions
        document.addEventListener('click', handleCourseAction);
        
        // Quick filters
        document.querySelectorAll('#quickFilters .nav-link').forEach(link => {
            link.addEventListener('click', handleQuickFilter);
        });
    }
    
    function handleViewSwitch(e) {
        e.preventDefault();
        
        // Remove active class from all buttons
        viewSwitchButtons.forEach(btn => btn.classList.remove('active'));
        
        // Add active class to clicked button
        this.classList.add('active');
        
        // Update current view
        currentView = this.dataset.view;
        
        // Show/hide appropriate view
        if (currentView === 'cards') {
            cardsView.style.display = 'flex';
            tableView.style.display = 'none';
        } else {
            cardsView.style.display = 'none';
            tableView.style.display = 'block';
            populateTable();
        }
    }
    
    function handleSearch(e) {
        currentFilters.search = e.target.value;
        filterCourses();
    }
    
    function handleFilterChange(e) {
        currentFilters[e.target.id.replace('Filter', '')] = e.target.value;
        filterCourses();
    }
    
    function handleQuickFilter(e) {
        e.preventDefault();
        
        // Update active quick filter
        document.querySelectorAll('#quickFilters .nav-link').forEach(link => {
            link.classList.remove('active');
        });
        this.classList.add('active');
        
        // Apply filter
        const filterValue = this.dataset.filter;
        if (filterValue) {
            const [type, value] = filterValue.split('=');
            if (type && type !== 'needs_attention') {
                currentFilters[type] = value;
                document.getElementById(type + 'Filter').value = value;
            }
        } else {
            // Reset all filters
            Object.keys(currentFilters).forEach(key => {
                if (key !== 'sort') currentFilters[key] = '';
            });
            resetFilters();
        }
        
        filterCourses();
    }
    
    function resetFilters() {
        statusFilter.value = '';
        categoryFilter.value = '';
        levelFilter.value = '';
        searchInput.value = '';
    }
    
    function loadCourses() {
        showLoading();
        
        const params = new URLSearchParams({
            ...currentFilters,
            view: currentView
        });
        
        fetch(`../ajax/department/search_courses.php?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                hideLoading();
                
                if (data.success) {
                    courses = data.courses || [];
                    updateView(data.html);
                    updateNoResultsState(data.count === 0);
                    
                    // Re-initialize tooltips for new content
                    initializeTooltips();
                } else {
                    showError(data.message || 'Failed to load courses');
                }
            })
            .catch(error => {
                hideLoading();
                showError('Error loading courses: ' + error.message);
            });
    }
    
    function filterCourses() {
        loadCourses();
    }
    
    function updateView(html) {
        if (currentView === 'cards') {
            cardsView.innerHTML = html;
        } else {
            document.getElementById('courseTableBody').innerHTML = html;
        }
    }
    
    function populateTable() {
        // If switching to table view, ensure data is loaded
        if (currentView === 'table' && courses.length === 0) {
            loadCourses();
        }
    }
    
    function updateNoResultsState(show) {
        if (show) {
            cardsView.style.display = 'none';
            tableView.style.display = 'none';
            noResults.style.display = 'block';
        } else {
            noResults.style.display = 'none';
            if (currentView === 'cards') {
                cardsView.style.display = 'flex';
                tableView.style.display = 'none';
            } else {
                cardsView.style.display = 'none';
                tableView.style.display = 'block';
            }
        }
    }
    
    function handleCourseAction(e) {
        const actionElement = e.target.closest('[data-action]');
        if (!actionElement) return;
        
        e.preventDefault();
        const action = actionElement.dataset.action;
        const courseId = actionElement.dataset.courseId;
        
        switch (action) {
            case 'view_details':
                showCourseDetails(courseId);
                break;
                
            case 'view_analytics':
                window.location.href = `course-analytics.php?course_id=${courseId}`;
                break;
                
            case 'manage_course':
                // Redirect to course management page instead of inline editing
                window.location.href = `manage-course.php?course_id=${courseId}`;
                break;
                
            case 'approve':
                confirmAction('Approve Course', 'Are you sure you want to approve this course?', 
                    () => performCourseAction(courseId, 'approve'));
                break;
                
            case 'request_revisions':
                showRevisionRequestForm(courseId);
                break;
                
            case 'reject':
                showRejectCourseForm(courseId);
                break;
                
            case 'unpublish':
                confirmAction('Unpublish Course', 'Are you sure you want to unpublish this course?', 
                    () => performCourseAction(courseId, 'unpublish'));
                break;
                
            case 'archive':
                confirmAction('Archive Course', 'Are you sure you want to archive this course?', 
                    () => performCourseAction(courseId, 'archive'), 'danger');
                break;
        }
    }
    
    function performCourseAction(courseId, action, additionalData = {}) {
        showLoading();
        
        const formData = new FormData();
        formData.append('action', action);
        formData.append('course_id', courseId);
        
        // Add any additional data
        Object.keys(additionalData).forEach(key => {
            formData.append(key, additionalData[key]);
        });
        
        fetch('../ajax/department/course_action_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    showSuccess(data.message);
                    loadCourses(); // Reload courses to reflect changes
                }
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            hideLoading();
            showError('Error performing action: ' + error.message);
        });
    }
    
    function showCourseDetails(courseId) {
        const modal = new bootstrap.Modal(document.getElementById('courseDetailsModal'));
        modal.show();
        
        // Load course details via AJAX
        fetch(`../ajax/department/course_action_handler.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=view_details&course_id=${courseId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('courseDetailsContent').innerHTML = data.html;
            } else {
                showError(data.message);
                modal.hide();
            }
        })
        .catch(error => {
            showError('Error loading course details: ' + error.message);
            modal.hide();
        });
    }
    
    function showRevisionRequestForm(courseId) {
        const modal = new bootstrap.Modal(document.getElementById('revisionRequestModal'));
        document.getElementById('revisionCourseId').value = courseId;
        document.getElementById('revisionComments').value = '';
        modal.show();
        
        document.getElementById('revisionRequestForm').onsubmit = function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../ajax/department/course_action_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modal.hide();
                    showSuccess(data.message);
                    loadCourses();
                } else {
                    showError(data.message);
                }
            })
            .catch(error => {
                showError('Error sending revision request: ' + error.message);
            });
        };
    }
    
    function showRejectCourseForm(courseId) {
        const modal = new bootstrap.Modal(document.getElementById('rejectCourseModal'));
        document.getElementById('rejectCourseId').value = courseId;
        document.getElementById('rejectComments').value = '';
        modal.show();
        
        document.getElementById('rejectCourseForm').onsubmit = function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../ajax/department/course_action_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modal.hide();
                    showSuccess(data.message);
                    loadCourses();
                } else {
                    showError(data.message);
                }
            })
            .catch(error => {
                showError('Error rejecting course: ' + error.message);
            });
        };
    }
    
    // Utility functions
    function confirmAction(title, message, callback, type = 'primary') {
        const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
        document.getElementById('confirmationModalLabel').textContent = title;
        document.getElementById('confirmationMessage').textContent = message;
        
        const confirmBtn = document.getElementById('confirmActionBtn');
        confirmBtn.className = `btn btn-${type}`;
        confirmBtn.textContent = 'Confirm';
        
        confirmBtn.onclick = function() {
            modal.hide();
            callback();
        };
        
        modal.show();
    }
    
    function showLoading() {
        // Simple loading overlay
        const overlay = document.createElement('div');
        overlay.id = 'loadingOverlay';
        overlay.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center';
        overlay.style.backgroundColor = 'rgba(255, 255, 255, 0.8)';
        overlay.style.zIndex = '9999';
        overlay.innerHTML = `
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        `;
        document.body.appendChild(overlay);
    }
    
    function hideLoading() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.remove();
        }
    }
    
    function showSuccess(message) {
        // Use your preferred notification system
        // This is a simple toast implementation
        showToast(message, 'success');
    }
    
    function showError(message) {
        // Use your preferred notification system
        // This is a simple toast implementation
        showToast(message, 'danger');
    }
    
    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        
        // Add to toast container (create if doesn't exist)
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '11';
            document.body.appendChild(toastContainer);
        }
        
        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }
    
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
});