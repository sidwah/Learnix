<?php
// includes/students/quiz-attempts-cards.php

// Simulate attempts - replace with actual DB logic if needed
$attempts = [
    ['score' => 92, 'status' => 'Passed', 'correct' => 18, 'total' => 20, 'time' => '08:41', 'date' => 'April 29, 2025 - 3:30 PM'],
    ['score' => 78, 'status' => 'Passed', 'correct' => 16, 'total' => 20, 'time' => '10:05', 'date' => 'April 29, 2025 - 12:15 PM'],
    ['score' => 54, 'status' => 'Failed', 'correct' => 11, 'total' => 20, 'time' => '09:50', 'date' => 'April 28, 2025 - 6:45 PM'],
    ['score' => 69, 'status' => 'Failed', 'correct' => 13, 'total' => 20, 'time' => '12:20', 'date' => 'April 28, 2025 - 1:03 PM'],
    ['score' => 85, 'status' => 'Passed', 'correct' => 17, 'total' => 20, 'time' => '09:13', 'date' => 'April 27, 2025 - 11:00 AM'],
];
?>

<!-- Previous Attempts (Card Stack Style) -->
<div class="card mt-4">
    <div class="card-header bg-light d-flex align-items-center justify-content-between">
        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Previous Attempts</h5>
        <span class="badge bg-secondary">Last <?php echo count($attempts); ?> Attempts</span>
    </div>
    <div class="card-body">
        <div class="d-flex flex-column gap-3">
            <?php
            $count = count($attempts);
            foreach ($attempts as $i => $attempt):
                $index = $count - $i;
                $isPassed = strtolower($attempt['status']) === 'passed';
            ?>
                <div class="border rounded p-3 bg-white shadow-sm d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold">Attempt <?php echo $index; ?></div>
                        <small class="text-muted"><?php echo $attempt['date']; ?></small>
                    </div>
                    <div class="text-end">
                        <div class="fs-5 fw-semibold <?php echo $isPassed ? 'text-success' : 'text-danger'; ?>">
                            <?php echo $attempt['score']; ?>%
                            <span class="badge <?php echo $isPassed ? 'bg-success' : 'bg-danger'; ?> ms-2">
                                <?php echo $attempt['status']; ?>
                            </span>
                        </div>
                        <div class="small text-muted"><?php echo $attempt['correct'] . '/' . $attempt['total']; ?> · <?php echo $attempt['time']; ?> mins</div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
