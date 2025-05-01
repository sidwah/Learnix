<?php
// includes/student/previous-attempts.php

// Ensure required variables are set
if (!isset($attempts) || !isset($question_count)) {
    echo '<div class="alert alert-danger">Error: Missing required data for previous attempts.</div>';
    return;
}
?>

<div class="card mt-4">
    <div class="card-header bg-light d-flex align-items-center justify-content-between">
        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Previous Attempts</h5>
        <span class="badge bg-secondary">Last 5 Attempts</span>
    </div>
    <div class="card-body">
        <div id="attemptsList" class="d-flex flex-column gap-3">
            <?php if (empty($attempts)): ?>
                <div class="alert alert-info text-center">No previous attempts found.</div>
            <?php else: ?>
                <?php foreach ($attempts as $attempt): ?>
                    <div class="border rounded p-3 bg-white shadow-sm d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold">Attempt <?php echo $attempt['attempt_number']; ?></div>
                            <small class="text-muted"><?php echo date('F j, Y - g:i A', strtotime($attempt['start_time'])); ?></small>
                        </div>
                        <div class="text-end">
                            <div class="fs-5 fw-semibold <?php echo $attempt['passed'] ? 'text-success' : 'text-danger'; ?>">
                                <?php echo number_format($attempt['score'], 0); ?>% 
                                <span class="badge <?php echo $attempt['passed'] ? 'bg-success' : 'bg-danger'; ?> ms-2">
                                    <?php echo $attempt['passed'] ? 'Passed' : 'Failed'; ?>
                                </span>
                            </div>
                            <div class="small text-muted">
                                <!-- Placeholder for correct answers count; adjust if question count is tracked per attempt -->
                                - / <?php echo $question_count; ?> · 
                                <?php echo gmdate('i:s', $attempt['time_spent']) . ' mins'; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>