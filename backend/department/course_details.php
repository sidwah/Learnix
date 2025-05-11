<?php
// backend/department/course_details.php
// NOTE: session_start() removed - handled by including file
require_once '../../backend/config.php';

// Check if user is logged in and has department head role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get user's department
$dept_query = "SELECT ds.department_id 
               FROM department_staff ds 
               WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' AND ds.deleted_at IS NULL
               LIMIT 1";
$dept_stmt = $conn->prepare($dept_query);
$dept_stmt->bind_param("i", $_SESSION['user_id']);
$dept_stmt->execute();
$dept_result = $dept_stmt->get_result();

if ($dept_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Department not found']);
    exit;
}

$department = $dept_result->fetch_assoc();
$department_id = $department['department_id'];

// Get course ID from POST
$course_id = $_POST['course_id'] ?? 0;

if (!$course_id) {
    echo json_encode(['success' => false, 'message' => 'Course ID required']);
    exit;
}

// Verify course belongs to department and get details
$course_query = "SELECT 
                    c.*,
                    cat.name as category_name,
                    sub.name as subcategory_name,
                    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.course_id AND status = 'Active') as student_count,
                    (SELECT AVG(rating) FROM course_ratings WHERE course_id = c.course_id) as average_rating,
                    (SELECT COUNT(*) FROM course_ratings WHERE course_id = c.course_id) as rating_count
                FROM courses c
                JOIN subcategories sub ON c.subcategory_id = sub.subcategory_id
                JOIN categories cat ON sub.category_id = cat.category_id
                WHERE c.course_id = ? AND c.department_id = ? AND c.deleted_at IS NULL";

$course_stmt = $conn->prepare($course_query);
$course_stmt->bind_param("ii", $course_id, $department_id);
$course_stmt->execute();
$course_result = $course_stmt->get_result();

if ($course_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Course not found']);
    exit;
}

$course = $course_result->fetch_assoc();

// Get course instructors
$instructors_query = "SELECT 
                         u.user_id,
                         u.first_name,
                         u.last_name,
                         u.email,
                         u.profile_pic,
                         i.instructor_id,
                         ci.is_primary,
                         ci.assigned_at
                     FROM course_instructors ci
                     JOIN instructors i ON ci.instructor_id = i.instructor_id
                     JOIN users u ON i.user_id = u.user_id
                     WHERE ci.course_id = ? AND ci.deleted_at IS NULL
                     ORDER BY ci.is_primary DESC, u.first_name";

$inst_stmt = $conn->prepare($instructors_query);
$inst_stmt->bind_param("i", $course_id);
$inst_stmt->execute();
$inst_result = $inst_stmt->get_result();

$instructors = [];
while ($instructor = $inst_result->fetch_assoc()) {
    $instructors[] = $instructor;
}

// Get course sections and structure
$sections_query = "SELECT 
                      s.section_id,
                      s.title as section_title,
                      s.position,
                      COUNT(t.topic_id) as topic_count
                  FROM course_sections s
                  LEFT JOIN section_topics t ON s.section_id = t.section_id
                  WHERE s.course_id = ? AND s.deleted_at IS NULL
                  GROUP BY s.section_id
                  ORDER BY s.position";

$sections_stmt = $conn->prepare($sections_query);
$sections_stmt->bind_param("i", $course_id);
$sections_stmt->execute();
$sections_result = $sections_stmt->get_result();

$sections = [];
while ($section = $sections_result->fetch_assoc()) {
    $sections[] = $section;
}

// Generate HTML without course_review_history since table doesn't exist
ob_start();
?>
<div class="row">
    <div class="col-md-8">
        <!-- Course Overview -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="card-title mb-1"><?php echo htmlspecialchars($course['title']); ?></h6>
                        <div class="d-flex gap-2 mb-2">
                            <span class="badge bg-soft-primary"><?php echo htmlspecialchars($course['category_name']); ?></span>
                            <span class="badge bg-soft-secondary"><?php echo htmlspecialchars($course['course_level']); ?></span>
                            <?php if ($course['status'] === 'Published'): ?>
                                <span class="badge bg-success">Published</span>
                            <?php elseif ($course['approval_status'] === 'pending'): ?>
                                <span class="badge bg-warning">Pending</span>
                            <?php elseif ($course['approval_status'] === 'under_review'): ?>
                                <span class="badge bg-info">Under Review</span>
                            <?php elseif ($course['approval_status'] === 'rejected'): ?>
                                <span class="badge bg-danger">Rejected</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="text-end">
                        <a href="manage-course.php?course_id=<?php echo $course_id; ?>" class="btn btn-outline-primary btn-sm">
                            <i class="bi-gear me-1"></i> Manage Course
                        </a>
                    </div>
                </div>
                
                <p class="text-muted mb-3"><?php echo htmlspecialchars($course['short_description']); ?></p>
                
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="h6 text-primary"><?php echo $course['student_count']; ?></div>
                            <small class="text-muted">Students</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="h6 text-success">
                                <?php echo $course['average_rating'] ? round($course['average_rating'], 1) : 'N/A'; ?>
                            </div>
                            <small class="text-muted">Rating (<?php echo $course['rating_count']; ?>)</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="h6 text-info"><?php echo count($sections); ?></div>
                            <small class="text-muted">Sections</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="h6 text-warning"><?php echo array_sum(array_column($sections, 'topic_count')); ?></div>
                            <small class="text-muted">Topics</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Course Structure -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Course Structure</h6>
            </div>
            <div class="card-body">
                <?php if (empty($sections)): ?>
                    <p class="text-muted text-center py-3">No sections added yet</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($sections as $section): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($section['section_title']); ?></h6>
                                    <small class="text-muted"><?php echo $section['topic_count']; ?> topics</small>
                                </div>
                                <i class="bi-chevron-right text-muted"></i>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Instructors -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Instructors</h6>
            </div>
            <div class="card-body">
                <?php if (empty($instructors)): ?>
                    <p class="text-muted text-center">No instructors assigned</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($instructors as $instructor): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-xs me-2">
                                        <span class="avatar-initial bg-soft-primary text-primary rounded-circle">
                                            <?php echo substr($instructor['first_name'], 0, 1) . substr($instructor['last_name'], 0, 1); ?>
                                        </span>
                                    </div>
                                    <div>
                                        <div class="fw-medium"><?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?></div>
                                        <small class="text-muted"><?php echo $instructor['is_primary'] ? 'Primary Instructor' : 'Co-instructor'; ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Status Info -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Course Status</h6>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-6">Current Status:</dt>
                    <dd class="col-sm-6">
                        <span class="badge bg-soft-<?php echo $course['approval_status'] === 'approved' ? 'success' : ($course['approval_status'] === 'rejected' ? 'danger' : 'warning'); ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $course['approval_status'])); ?>
                        </span>
                    </dd>
                    
                    <dt class="col-sm-6">Last Updated:</dt>
                    <dd class="col-sm-6"><?php echo date('M j, Y', strtotime($course['updated_at'])); ?></dd>
                    
                    <dt class="col-sm-6">Created:</dt>
                    <dd class="col-sm-6"><?php echo date('M j, Y', strtotime($course['created_at'])); ?></dd>
                </dl>
            </div>
        </div>
    </div>
</div>
<?php
$html = ob_get_clean();

echo json_encode([
    'success' => true,
    'html' => $html
]);

$conn->close();
?>