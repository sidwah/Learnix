<?php
// includes/department/course_modals.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function renderCourseDetailsModal() {
    ?>
    <!-- Course Details Modal -->
    <div class="modal fade" id="courseDetailsModal" tabindex="-1" aria-labelledby="courseDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="courseDetailsModalLabel">Course Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="courseDetailsContent">
                        <!-- Loaded via AJAX -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function renderRevisionRequestModal() {
    ?>
    <!-- Revision Request Modal -->
    <div class="modal fade" id="revisionRequestModal" tabindex="-1" aria-labelledby="revisionRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="revisionRequestModalLabel">Request Revisions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="revisionRequestForm">
                    <div class="modal-body">
                        <input type="hidden" id="revisionCourseId" name="course_id" value="">
                        <input type="hidden" name="action" value="request_revisions">
                        
                        <div class="mb-3">
                            <label for="revisionComments" class="form-label">Revision Comments <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="revisionComments" name="comments" rows="4" required
                                placeholder="Please provide specific feedback about what needs to be revised..."></textarea>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="bi-info-circle me-1"></i>
                            These comments will be sent to the instructors to help them make the necessary improvements.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Send Revision Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
}

function renderRejectCourseModal() {
    ?>
    <!-- Reject Course Modal -->
    <div class="modal fade" id="rejectCourseModal" tabindex="-1" aria-labelledby="rejectCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectCourseModalLabel">Reject Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="rejectCourseForm">
                    <div class="modal-body">
                        <input type="hidden" id="rejectCourseId" name="course_id" value="">
                        <input type="hidden" name="action" value="reject">
                        
                        <div class="mb-3">
                            <label for="rejectComments" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejectComments" name="comments" rows="4" required
                                placeholder="Please provide reasons for rejecting this course..."></textarea>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="bi-exclamation-triangle me-1"></i>
                            <strong>Warning:</strong> This action will mark the course as rejected and require it to be resubmitted for review.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
}

function renderConfirmationModal() {
    ?>
    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalLabel">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmationMessage">Are you sure you want to proceed?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn" id="confirmActionBtn">Confirm</button>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// Helper function to render all modals at once
function renderAllCourseModals() {
    renderCourseDetailsModal();
    renderRevisionRequestModal();
    renderRejectCourseModal();
    renderConfirmationModal();
}
?>