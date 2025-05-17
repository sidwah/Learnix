<?php
// includes/department/course_table_row.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function renderCourseTableRow($course) {
    // Validate course data
    if (!isset($course['course_id'], $course['title'], $course['status'], $course['category_name'], $course['course_level'])) {
        return '<tr><td colspan="5">Invalid course data</td></tr>';
    }

    // Format course data
    $status_info = formatCourseStatus($course['status'] ?? 'unknown', $course['approval_status'] ?? 'unknown');
    $student_count = $course['student_count'] ?? 0;
    $category_color = getCategoryColor($course['category_name'] ?? 'Unknown');
    $level_color = getLevelColor($course['course_level'] ?? 'Unknown');

    // Format instructors
    $instructors_html = '<div class="avatar-stack avatar-group-hover">';
    $instructors = $course['instructors'] ?? [];
    if (empty($instructors)) {
        $instructors_html .= '<span class="text-muted">No instructors</span>';
    } else {
        foreach ($instructors as $instructor) {
            $initials = substr($instructor['first_name'] ?? '', 0, 1) . substr($instructor['last_name'] ?? '', 0, 1);
            $full_name = ($instructor['first_name'] ?? '') . ' ' . ($instructor['last_name'] ?? '');
            $color = getInitialColor($instructor['user_id'] ?? 0);
            $instructors_html .= "
                <span class=\"avatar avatar-xs\" data-bs-toggle=\"tooltip\" data-bs-placement=\"top\" title=\"" . htmlspecialchars($full_name) . "\">
                    <span class=\"avatar-initial bg-soft-{$color} text-{$color} rounded-circle\">" . htmlspecialchars($initials) . "</span>
                </span>
            ";
        }
    }
    $instructors_html .= '</div>';

    ob_start();
    ?>
    <tr data-course-id="<?php echo htmlspecialchars($course['course_id']); ?>">
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
                        <span class="badge badge-pill bg-soft-<?php echo htmlspecialchars($category_color); ?> text-<?php echo htmlspecialchars($category_color); ?>">
                            <?php echo htmlspecialchars($course['category_name']); ?>
                        </span>
                        <span class="badge badge-pill bg-soft-<?php echo htmlspecialchars($level_color); ?> text-<?php echo htmlspecialchars($level_color); ?>">
                            <?php echo htmlspecialchars($course['course_level']); ?>
                        </span>
                    </div>
                </div>
            </div>
        </td>
        <td>
            <span class="badge <?php echo htmlspecialchars($status_info['class']); ?>"><?php echo htmlspecialchars($status_info['label']); ?></span>
        </td>
        <td>
            <?php echo $instructors_html; ?>
        </td>
        <td>
            <?php if ($course['status'] === 'Published'): ?>
                <span class="text-success fw-medium"><?php echo htmlspecialchars($student_count); ?></span>
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
    
    // Replace multiple review actions with a single "Review Course" button
    // for courses that are in the review pipeline
    if ($course['approval_status'] === 'submitted_for_review' || $course['approval_status'] === 'under_review') {
        $actions[] = [
            'icon' => 'check-square',
            'title' => 'Review Course',
            'action' => 'review_course',
            'color' => 'success'
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