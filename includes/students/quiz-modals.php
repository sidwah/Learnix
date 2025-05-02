<!-- Start Quiz Modal -->
<div class="modal fade" id="startQuizModal" tabindex="-1" aria-labelledby="startQuizModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="startQuizModalLabel">Start Quiz</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you ready to start the quiz?</p>
                <ul class="list-unstyled" id="startQuizDetails">
                    <li><i class="bi bi-clock me-2"></i>Loading...</li>
                    <li><i class="bi bi-check-circle me-2"></i>Loading...</li>
                    <li><i class="bi bi-exclamation-circle me-2"></i>No pausing or rewinding allowed</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="confirmStartQuiz"><i class="bi bi-play-circle me-2"></i>Start Now</button>
            </div>
        </div>
    </div>
</div>

<!-- Resume Quiz Modal -->
<div class="modal fade" id="resumeQuizModal" tabindex="-1" aria-labelledby="resumeQuizModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resumeQuizModalLabel">Resume Quiz</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>You have an active quiz attempt with <span id="modalRemainingTime"></span> remaining.</p>
                <p>Would you like to resume where you left off?</p>
                <ul class="list-unstyled">
                    <li><i class="bi bi-exclamation-circle me-2"></i>Time will continue counting down immediately.</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-success" id="confirmResumeQuiz"><i class="bi bi-play-circle me-2"></i>Resume Now</button>
            </div>
        </div>
    </div>
</div>

<!-- Forfeit Quiz Modal -->
<div class="modal fade" id="forfeitQuizModal" tabindex="-1" aria-labelledby="forfeitQuizModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="forfeitQuizModalLabel">Forfeit Quiz</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to forfeit this quiz attempt?</p>
                <p>Your attempt will be submitted with the score based on answers provided so far.</p>
                <ul class="list-unstyled">
                    <li><i class="bi bi-exclamation-circle me-2"></i>This action cannot be undone.</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger" id="confirmForfeitQuiz"><i class="bi bi-x-circle me-2"></i>Submit and Forfeit</button>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Submission Modal -->
<div class="modal fade" id="confirmSubmitModal" tabindex="-1" aria-labelledby="confirmSubmitLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmSubmitLabel">Submit Quiz</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to submit this quiz?
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="confirmSubmitBtn">Yes, Submit</button>
            </div>
        </div>
    </div>
</div>