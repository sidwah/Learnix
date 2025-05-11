<?php
// includes/department/course_table_row.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function renderCourseTableRow($course) {
    // Format course data
    $status_info = formatCourseStatus($course['status'], $course['approval_status']);
    $student_count = $course['student_count'] ?? 0;
    $category_color = getCategoryColor($course['category_name']);
    $level_color = getLevelColor($course['course_level']);
    
    // Get course progress
    $progress_info = getCourseProgress($course['course_id']);
    $progress_percentage = $progress_info['progress_percentage'];
    
    // Format instructors
    if (!function_exists('getInitialColor')) {
        function getInitialColor($userId) {
            // Example logic to determine color based on user ID
            $colors = ['red', 'blue', 'green', 'yellow', 'purple'];
            return $colors[$userId % count($colors)];
        }
    }

    $instructors_html = '<div class="avatar-stack avatar-group-hover">';
    foreach (($course['instructors'] ?? []) as $instructor) {
        $initials = substr($instructor['first_name'], 0, 1) . substr($instructor['last_name'], 0, 1);
        $full_name = "{$instructor['first_name']} {$instructor['last_name']}";
        $color = getInitialColor($instructor['user_id']);
        $instructors_html .= "
            <span class=\"avatar avatar-xs\" data-bs-toggle=\"tooltip\" data-bs-placement=\"top\" title=\"{$full_name}\">
                <span class=\"avatar-initial bg-soft-{$color} text-{$color} rounded-circle\">{$initials}</span>
            </span>
        ";
    }
    $instructors_html .= '</div>';
    
    ob_start();
    ?>
    <tr data-course-id="<?php echo $course['course_id']; ?>">
        <td>
            <div class="d-flex align-items-center">
                <div class="avatar avatar-sm me-3">
                    <div class="avatar-initial bg-light text-muted rounded">
                        <i class="bi-journal-bookmark"></i>
                    </div>
                </div>
                <div>
                    <h6 class="mb-0"><?php echo htmlspecialchars($course['title']); ?></h6>
                    <div class="d-flex gap-1 mt-1">
                        <span class="badge badge-pill bg-soft-<?php echo $category_color; ?> text-<?php echo $category_color; ?>">
                            <?php echo htmlspecialchars($course['category_name']); ?>
                        </span>
                        <span class="badge badge-pill bg-soft-<?php echo $level_color; ?> text-<?php echo $level_color; ?>">
                            <?php echo htmlspecialchars($course['course_level']); ?>
                        </span>
                    </div>
                </div>
            </div>
        </td>
        <td>
            <span class="badge <?php echo $status_info['class']; ?>"><?php echo $status_info['label']; ?></span>
        </td>
        <td>
            <?php echo $instructors_html; ?>
        </td>
        <td>
            <div class="d-flex align-items-center gap-2">
                <div class="progress flex-grow-1" style="height: 6px;">
                    <div class="progress-bar<?php echo $progress_percentage === 100 ? ' bg-success' : ''; ?>" 
                         role="progressbar" 
                         style="width: <?php echo $progress_percentage; ?>%" 
                         aria-valuenow="<?php echo $progress_percentage; ?>" 
                         aria-valuemin="0" 
                         aria-valuemax="100"></div>
                </div>
                <small class="text-muted"><?php echo $progress_percentage; ?>%</small>
            </div>
        </td>
        <td>
            <?php if ($course['status'] === 'Published'): ?>
                <span class="text-success fw-medium"><?php echo $student_count; ?></span>
            <?php else: ?>
                <span class="text-muted">-</span>
            <?php endif; ?>
        </td>
        <td class="text-center">
            <div class="d-flex justify-content-center gap-1">
                <?php renderTableActions($course); ?>
            </div>
        </td>
    </tr>
    <?php
    return ob_get_clean();
}

function renderTableActions($course) {
    $actions = [];
    
    switch ($course['status']) {
        case 'Draft':
            $actions[] = [
                'icon' => 'eye',
                'title' => 'View Details',
                'action' => 'view_details',
                'color' => 'info'
            ];
            break;
            
        case 'Published':
            $actions[] = [
                'icon' => 'graph-up',
                'title' => 'View Analytics',
                'action' => 'view_analytics',
                'color' => 'info'
            ];
            $actions[] = [
                'icon' => 'eye-slash',
                'title' => 'Unpublish',
                'action' => 'unpublish',
                'color' => 'warning'
            ];
            break;
            
        default:
            $actions[] = [
                'icon' => 'eye',
                'title' => 'View Details',
                'action' => 'view_details',
                'color' => 'info'
            ];
            break;
    }
    
    // Management link - always available
    $actions[] = [
        'icon' => 'gear',
        'title' => 'Manage Course',
        'action' => 'manage_course',
        'color' => 'primary'
    ];
    
    // Review actions for submitted courses
    if ($course['approval_status'] === 'submitted_for_review' || $course['approval_status'] === 'under_review') {
        $actions[] = [
            'icon' => 'check-circle',
            'title' => 'Approve',
            'action' => 'approve',
            'color' => 'success'
        ];
        $actions[] = [
            'icon' => 'arrow-counterclockwise',
            'title' => 'Request Revisions',
            'action' => 'request_revisions',
            'color' => 'warning'
        ];
        $actions[] = [
            'icon' => 'x-circle',
            'title' => 'Reject',
            'action' => 'reject',
            'color' => 'danger'
        ];
    }
    
    $actions[] = [
        'icon' => 'archive',
        'title' => 'Archive',
        'action' => 'archive',
        'color' => 'danger'
    ];
    
    foreach ($actions as $action) {
        echo "<button type=\"button\" 
                class=\"btn btn-sm btn-soft-{$action['color']} btn-icon rounded-pill\" 
                data-bs-toggle=\"tooltip\" 
                title=\"{$action['title']}\"
                data-action=\"{$action['action']}\"
                data-course-id=\"{$course['course_id']}\">
                <i class=\"bi-{$action['icon']}\"></i>
            </button>";
    }
}
?>