<?php
// includes/department/course_card.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!function_exists('formatCourseStatus')) {
    die('Error: formatCourseStatus function is not defined. Ensure courses_functions.php is included.');
}

// Make sure we don't re-declare this function
if (!function_exists('renderCourseCard')) {
    function renderCourseCard($course) {
        // Format course data
        $status_info = formatCourseStatus($course['status'], $course['approval_status']);
        $student_count = $course['student_count'] ?? 0;
        $average_rating = $course['average_rating'] ? round($course['average_rating'], 1) : 0;
        $category_color = getCategoryColor($course['category_name']);
        $level_color = getLevelColor($course['course_level']);
        
        // Get course progress
        $progress_info = getCourseProgress($course['course_id']);
        $progress_percentage = $progress_info['progress_percentage'];
        $current_step = $course['creation_step'] ?? 1;
        $completion_percentage = $course['completion_percentage'] ?? 0;
        
        // Check if creation is complete (4/4)
        $isCreationComplete = ($current_step >= 4);

        // Format instructors
        if (!function_exists('getInitialColor')) {
            function getInitialColor($userId) {
                // Example logic to determine color based on user ID
                $colors = ['red', 'blue', 'green', 'yellow', 'purple'];
                return $colors[$userId % count($colors)];
            }
        }
        
        // Format instructors
        $instructors_html = '';
        $instructor_count = count($course['instructors'] ?? []);
        foreach (($course['instructors'] ?? []) as $instructor) {
            $initials = substr($instructor['first_name'], 0, 1) . substr($instructor['last_name'], 0, 1);
            $full_name = "{$instructor['first_name']} {$instructor['last_name']}";
            $instructors_html .= "
                <div class=\"avatar avatar-xs\" data-instructor=\"{$full_name}\">
                    <span class=\"avatar-initial bg-soft-" . getInitialColor($instructor['user_id']) . " text-" . getInitialColor($instructor['user_id']) . "\">" . htmlspecialchars($initials) . "</span>
                    <span class=\"name-tooltip\">{$full_name}</span>
                </div>
            ";
        }
        
        // Fix thumbnail path
        $thumbnailPath = !empty($course['thumbnail']) 
            ? '../uploads/thumbnails/' . $course['thumbnail']
            : '../assets/img/placeholder-course.jpg';
        
        ob_start();
        ?>
        <div class="col-md-3 course-card" 
             data-course-name="<?php echo htmlspecialchars($course['title']); ?>" 
             data-status="<?php echo strtolower($course['approval_status'] ?? $course['status']); ?>" 
             data-category="<?php echo strtolower($course['category_name']); ?>" 
             data-level="<?php echo strtolower($course['course_level']); ?>">
            <div class="card h-100">
                <div class="position-relative">
                    <span class="badge <?php echo $status_info['class']; ?> position-absolute top-0 end-0 m-2 z-1">
                        <?php echo $status_info['label']; ?>
                    </span>
                    <div class="ratio ratio-16x9">
                        <?php if (!empty($course['thumbnail'])): ?>
                            <img src="<?php echo $thumbnailPath; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="card-img-top" style="object-fit: cover;">
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center bg-light">
                                <i class="bi-journal-bookmark fs-3 text-muted"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="mb-2">
                        <span class="badge bg-soft-<?php echo $category_color; ?> text-<?php echo $category_color; ?> me-1" style="font-size: 0.7rem;">
                            <?php echo htmlspecialchars($course['category_name']); ?>
                        </span>
                        <span class="badge bg-soft-<?php echo $level_color; ?> text-<?php echo $level_color; ?>" style="font-size: 0.7rem;">
                            <?php echo htmlspecialchars($course['course_level']); ?>
                        </span>
                    </div>
                    <h6 class="card-title mb-1"><?php echo htmlspecialchars($course['title']); ?></h6>
                    <p class="card-text text-muted small mb-2">
                        <?php echo htmlspecialchars(substr($course['short_description'], 0, 100)) . '...'; ?>
                    </p>
                    
                    <?php if (($course['status'] === 'Draft' || $course['approval_status'] === 'revisions_requested') && !$isCreationComplete): ?>
                        <!-- Progress indicator for incomplete courses -->
                        <div class="progress mb-2" style="height: 4px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $progress_percentage; ?>%"></div>
                        </div>
                        <small class="text-muted d-block mb-2">Step <?php echo $current_step; ?> of 4 complete</small>
                    <?php elseif ($isCreationComplete && $course['status'] !== 'Published'): ?>
                        <!-- Show course completion when creation is complete but not yet published -->
                        <div class="progress mb-2" style="height: 4px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $completion_percentage; ?>%"></div>
                        </div>
                        <small class="text-muted d-block mb-2">Course Completion: <?php echo $completion_percentage; ?>%</small>
                    <?php elseif ($course['status'] === 'Published'): ?>
                        <!-- Performance metrics for published courses -->
                        <div class="row g-2 mb-2">
                            <div class="col-4">
                                <div class="text-center">
                                    <div class="text-primary fw-medium small"><?php echo $student_count; ?></div>
                                    <small class="text-muted" style="font-size: 0.7rem;">Students</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center">
                                    <div class="text-success fw-medium small"><?php echo $average_rating; ?></div>
                                    <small class="text-muted" style="font-size: 0.7rem;">Rating</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center">
                                    <div class="text-info fw-medium small">
                                        <?php
                                        $completion_rate = $student_count > 0 ? 
                                            round(($course['completed_students'] ?? 0) / $student_count * 100) : 0;
                                        echo $completion_rate;
                                        ?>%
                                    </div>
                                    <small class="text-muted" style="font-size: 0.7rem;">Completion</small>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Status-specific information -->
                        <div class="alert alert-soft-info py-1 px-2 mb-2">
                            <div class="d-flex align-items-center">
                                <i class="bi-clock me-1"></i>
                                <small>
                                    <?php
                                    if ($course['approval_status'] === 'under_review') {
                                        echo 'Currently under review';
                                    } elseif ($course['approval_status'] === 'submitted_for_review') {
                                        echo 'Submitted ' . getTimeAgo($course['updated_at']);
                                    } else {
                                        echo 'Last updated ' . getTimeAgo($course['updated_at']);
                                    }
                                    ?>
                                </small>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="avatar-stack">
                                <?php echo $instructors_html; ?>
                            </div>
                            <small class="text-muted ms-2"><?php echo $instructor_count; ?> instructor<?php echo $instructor_count !== 1 ? 's' : ''; ?></small>
                        </div>
                        <div class="dropdown dropdown-fixed">
                            <button type="button" class="btn btn-sm btn-ghost-secondary btn-icon rounded-pill" id="courseDropdown<?php echo $course['course_id']; ?>" data-bs-toggle="dropdown" aria-expanded="false" data-bs-offset="0,10">
                                <i class="bi-three-dots"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="courseDropdown<?php echo $course['course_id']; ?>">
                                <?php renderCourseActions($course); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Make sure we don't re-declare this function
if (!function_exists('renderCourseActions')) {
    function renderCourseActions($course) {
        $actions = [];
        
        switch ($course['status']) {
            case 'Draft':
                $actions[] = [
                    'icon' => 'eye',
                    'text' => 'View Details',
                    'action' => 'view_details',
                    'class' => ''
                ];
                break;
                
            case 'Published':
                $actions[] = [
                    'icon' => 'graph-up',
                    'text' => 'View Analytics',
                    'action' => 'view_analytics',
                    'class' => ''
                ];
                $actions[] = [
                    'icon' => 'eye-slash',
                    'text' => 'Unpublish',
                    'action' => 'unpublish',
                    'class' => 'text-warning'
                ];
                break;
                
            default:
                $actions[] = [
                    'icon' => 'eye',
                    'text' => 'View Details',
                    'action' => 'view_details',
                    'class' => ''
                ];
                break;
        }
        
        // Management link - always available
        $actions[] = [
            'icon' => 'gear',
            'text' => 'Manage Course',
            'action' => 'manage_course',
            'class' => ''
        ];
        
        // Review actions for submitted courses
        if ($course['approval_status'] === 'submitted_for_review' || $course['approval_status'] === 'under_review') {
            $actions[] = ['divider' => true];
            $actions[] = [
                'icon' => 'check-circle',
                'text' => 'Approve',
                'action' => 'approve',
                'class' => 'text-success'
            ];
            $actions[] = [
                'icon' => 'arrow-counterclockwise',
                'text' => 'Request Revisions',
                'action' => 'request_revisions',
                'class' => 'text-warning'
            ];
            $actions[] = [
                'icon' => 'x-circle',
                'text' => 'Reject',
                'action' => 'reject',
                'class' => 'text-danger'
            ];
        }
        
        $actions[] = ['divider' => true];
        $actions[] = [
            'icon' => 'archive',
            'text' => 'Archive',
            'action' => 'archive',
            'class' => 'text-danger'
        ];
        
        foreach ($actions as $action) {
            if (isset($action['divider'])) {
                echo '<div class="dropdown-divider"></div>';
                continue;
            }
            
            echo "<a class=\"dropdown-item {$action['class']}\" href=\"#\" 
                    data-action=\"{$action['action']}\" 
                    data-course-id=\"{$course['course_id']}\">
                    <i class=\"bi-{$action['icon']} me-2\"></i>
                    {$action['text']}
                </a>";
        }
    }
}

// Helper functions
if (!function_exists('getCategoryColor')) {
    function getCategoryColor($category) {
        $colors = [
            'Technology' => 'primary',
            'Business' => 'success',
            'Science' => 'purple',
            'Arts' => 'info',
            'Health' => 'warning',
            'Engineering' => 'danger'
        ];
        return $colors[$category] ?? 'secondary';
    }
}

if (!function_exists('getLevelColor')) {
    function getLevelColor($level) {
        $colors = [
            'Beginner' => 'info',
            'Intermediate' => 'warning',
            'Advanced' => 'danger',
            'All Levels' => 'secondary'
        ];
        return $colors[$level] ?? 'secondary';
    }
}

if (!function_exists('getInitialColor')) {
    function getInitialColor($user_id) {
        $colors = ['primary', 'success', 'info', 'warning', 'danger', 'purple'];
        return $colors[$user_id % count($colors)];
    }
}

if (!function_exists('getTimeAgo')) {
    function getTimeAgo($datetime) {
        $timestamp = strtotime($datetime);
        $current_time = time();
        $time_difference = $current_time - $timestamp;
        
        if ($time_difference < 60) {
            return 'just now';
        } elseif ($time_difference < 3600) {
            $minutes = floor($time_difference / 60);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        } elseif ($time_difference < 86400) {
            $hours = floor($time_difference / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($time_difference < 604800) {
            $days = floor($time_difference / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            return date('M j, Y', $timestamp);
        }
    }
}