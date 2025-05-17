<?php
// ajax/department/course_action_handler.php
require_once '../../backend/session_start.php';
require_once '../../backend/config.php';

// Check if user is logged in and is a department head
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get department ID for the current user
$dept_query = "SELECT ds.department_id 
               FROM department_staff ds 
               WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' AND ds.deleted_at IS NULL
               LIMIT 1";
$dept_stmt = $conn->prepare($dept_query);
$dept_stmt->bind_param("i", $_SESSION['user_id']);
$dept_stmt->execute();
$dept_result = $dept_stmt->get_result();

if ($dept_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Department access error']);
    exit;
}

$department = $dept_result->fetch_assoc();
$department_id = $department['department_id'];

// Get action and course ID from request
$action = isset($_POST['action']) ? $_POST['action'] : '';
$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;

// Check if course exists and belongs to the department
if ($course_id > 0) {
    $course_query = "SELECT * FROM courses 
                    WHERE course_id = ? AND department_id = ? AND deleted_at IS NULL";
    $course_stmt = $conn->prepare($course_query);
    $course_stmt->bind_param("ii", $course_id, $department_id);
    $course_stmt->execute();
    $course_result = $course_stmt->get_result();
    
    if ($course_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Course not found or access denied']);
        exit;
    }
    
    $course = $course_result->fetch_assoc();
}

// Process different actions
switch ($action) {
    case 'view_details':
        // Load and return course details 
        loadCourseDetails($course_id, $department_id);
        break;
        
    case 'publish':
        // Publish a course
        publishCourse($course_id, $department_id);
        break;
        
    case 'unpublish':
        // Unpublish a course
        unpublishCourse($course_id, $department_id);
        break;
        
    case 'archive':
        // Archive a course
        archiveCourse($course_id, $department_id);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
        break;
}

/**
 * Load course details
 */
function loadCourseDetails($course_id, $department_id) {
    global $conn;
    
    // Get detailed course information
    $query = "SELECT c.*, 
                   cat.name AS category_name,
                   sub.name AS subcategory_name,
                   (SELECT COUNT(*) FROM enrollments WHERE course_id = c.course_id AND status = 'Active') AS student_count,
                   (SELECT AVG(rating) FROM course_ratings WHERE course_id = c.course_id) AS avg_rating
              FROM courses c
              JOIN subcategories sub ON c.subcategory_id = sub.subcategory_id
              JOIN categories cat ON sub.category_id = cat.category_id
              WHERE c.course_id = ? AND c.department_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $course_id, $department_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Course not found']);
        exit;
    }
    
    $course = $result->fetch_assoc();
    
    // Get instructors
    $instructors_query = "SELECT 
                             u.user_id,
                             u.first_name,
                             u.last_name,
                             u.email,
                             u.profile_pic,
                             ci.is_primary
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
    
    // Get sections and topics count
    $content_query = "SELECT 
                         COUNT(DISTINCT cs.section_id) AS section_count,
                         COUNT(DISTINCT st.topic_id) AS topic_count
                     FROM courses c
                     LEFT JOIN course_sections cs ON c.course_id = cs.course_id AND cs.deleted_at IS NULL
                     LEFT JOIN section_topics st ON cs.section_id = st.section_id
                     WHERE c.course_id = ?";
    
    $content_stmt = $conn->prepare($content_query);
    $content_stmt->bind_param("i", $course_id);
    $content_stmt->execute();
    $content_result = $content_stmt->get_result();
    $content_data = $content_result->fetch_assoc();
    
    // Build HTML for the modal
    ob_start();
    ?>
    <div class="row">
        <div class="col-md-8">
            <h4><?php echo htmlspecialchars($course['title']); ?></h4>
            <div class="d-flex gap-2 mb-3">
                <span class="badge bg-soft-primary text-primary">
                    <?php echo htmlspecialchars($course['category_name']); ?>
                </span>
                <span class="badge bg-soft-secondary text-secondary">
                    <?php echo htmlspecialchars($course['subcategory_name']); ?>
                </span>
                <span class="badge bg-soft-info text-info">
                    <?php echo htmlspecialchars($course['course_level']); ?>
                </span>
            </div>
            
            <?php if (!empty($course['short_description'])): ?>
                <div class="mb-3">
                    <p><?php echo htmlspecialchars($course['short_description']); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="mb-4">
                <h5>Course Content</h5>
                <div class="d-flex gap-4">
                    <div>
                        <span class="fw-bold text-dark"><?php echo $content_data['section_count']; ?></span>
                        <p class="text-muted mb-0">Sections</p>
                    </div>
                    <div>
                        <span class="fw-bold text-dark"><?php echo $content_data['topic_count']; ?></span>
                        <p class="text-muted mb-0">Topics</p>
                    </div>
                    <div>
                        <span class="fw-bold text-dark"><?php echo $course['student_count']; ?></span>
                        <p class="text-muted mb-0">Students</p>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($course['full_description'])): ?>
                <div class="mb-3">
                    <h5>Description</h5>
                    <div><?php echo nl2br(htmlspecialchars($course['full_description'])); ?></div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header bg-light py-2">
                    <h6 class="mb-0">Course Status</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <span class="text-muted">Status:</span>
                        <span class="badge bg-<?php echo getCourseStatusClass($course['status'], $course['approval_status']); ?>">
                            <?php echo getStatusLabel($course['status'], $course['approval_status']); ?>
                        </span>
                    </div>
                    
                    <div class="mb-2">
                        <span class="text-muted">Created:</span>
                        <span><?php echo date('M j, Y', strtotime($course['created_at'])); ?></span>
                    </div>
                    
                    <div>
                        <span class="text-muted">Last Updated:</span>
                        <span><?php echo date('M j, Y', strtotime($course['updated_at'])); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-light py-2">
                    <h6 class="mb-0">Instructors</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($instructors)): ?>
                        <p class="text-muted">No instructors assigned</p>
                    <?php else: ?>
                        <ul class="list-unstyled">
                            <?php foreach ($instructors as $instructor): ?>
                                <li class="d-flex align-items-center mb-2">
                                    <div class="avatar avatar-sm me-2">
                                        <?php if (!empty($instructor['profile_pic']) && $instructor['profile_pic'] !== 'default.png'): ?>
                                            <img src="../uploads/profile/<?php echo htmlspecialchars($instructor['profile_pic']); ?>" alt="Profile" class="avatar-img rounded-circle">
                                        <?php else: ?>
                                            <span class="avatar-initial rounded-circle bg-soft-primary text-primary">
                                                <?php echo substr($instructor['first_name'], 0, 1) . substr($instructor['last_name'], 0, 1); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <span class="d-block">
                                            <?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?>
                                            <?php if ($instructor['is_primary']): ?>
                                                <span class="badge bg-soft-success text-success ms-1">Primary</span>
                                            <?php endif; ?>
                                        </span>
                                        <small class="text-muted"><?php echo htmlspecialchars($instructor['email']); ?></small>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
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
}

/**
 * Publish a course
 */
function publishCourse($course_id, $department_id) {
    global $conn;
    
    // Check if course is approved and can be published
    $check_query = "SELECT * FROM courses 
                   WHERE course_id = ? AND department_id = ? 
                   AND approval_status = 'approved' AND status != 'Published'";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ii", $course_id, $department_id);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows === 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'Course cannot be published. Make sure it is approved first.'
        ]);
        return;
    }
    
    // Update course status
    $update_query = "UPDATE courses SET status = 'Published', updated_at = NOW() WHERE course_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $course_id);
    
    if ($update_stmt->execute()) {
        // Get primary instructor to send notification (optional)
        $instructor_query = "SELECT u.user_id, u.email
                            FROM course_instructors ci
                            JOIN instructors i ON ci.instructor_id = i.instructor_id
                            JOIN users u ON i.user_id = u.user_id
                            WHERE ci.course_id = ? AND ci.is_primary = 1
                            LIMIT 1";
        $instructor_stmt = $conn->prepare($instructor_query);
        $instructor_stmt->bind_param("i", $course_id);
        $instructor_stmt->execute();
        $instructor_result = $instructor_stmt->get_result();
        
        if ($instructor_result->num_rows > 0) {
            $instructor = $instructor_result->fetch_assoc();
            
            // Create notification for instructor
            $notification_query = "INSERT INTO user_notifications 
                                 (user_id, type, title, message, related_id, related_type)
                                 VALUES (?, 'course_published', 'Course Published', 
                                        'Your course has been published and is now available to students.', 
                                        ?, 'course')";
            $notification_stmt = $conn->prepare($notification_query);
            $notification_stmt->bind_param("ii", $instructor['user_id'], $course_id);
            $notification_stmt->execute();
            
            // You could add email notification here if needed
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Course has been published successfully!'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to publish course. Please try again.'
        ]);
    }
}

/**
 * Unpublish a course
 */
function unpublishCourse($course_id, $department_id) {
    global $conn;
    
    // Check if course is published
    $check_query = "SELECT * FROM courses 
                   WHERE course_id = ? AND department_id = ? AND status = 'Published'";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ii", $course_id, $department_id);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows === 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'Course is not currently published.'
        ]);
        return;
    }
    
    // Update course status
    $update_query = "UPDATE courses SET status = 'Draft', updated_at = NOW() WHERE course_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $course_id);
    
    if ($update_stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Course has been unpublished successfully!'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to unpublish course. Please try again.'
        ]);
    }
}

/**
 * Archive a course
 */
function archiveCourse($course_id, $department_id) {
    global $conn;
    
    // Soft delete the course
    $archive_query = "UPDATE courses SET deleted_at = NOW() WHERE course_id = ? AND department_id = ?";
    $archive_stmt = $conn->prepare($archive_query);
    $archive_stmt->bind_param("ii", $course_id, $department_id);
    
    if ($archive_stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Course has been archived successfully!'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to archive course. Please try again.'
        ]);
    }
}

/**
 * Helper function to get course status class for badges
 */
function getCourseStatusClass($status, $approval_status) {
    if ($status === 'Published') {
        return 'success';
    }
    
    switch ($approval_status) {
        case 'approved':
            return 'success';
        case 'pending':
            return 'warning';
        case 'submitted_for_review':
        case 'under_review':
            return 'info';
        case 'revisions_requested':
            return 'warning';
        case 'rejected':
            return 'danger';
        default:
            return 'secondary';
    }
}

/**
 * Helper function to get status label
 */
function getStatusLabel($status, $approval_status) {
    if ($status === 'Published') {
        return 'Published';
    }
    
    switch ($approval_status) {
        case 'approved':
            return 'Approved';
        case 'pending':
            return 'Draft';
        case 'submitted_for_review':
            return 'Submitted';
        case 'under_review':
            return 'Under Review';
        case 'revisions_requested':
            return 'Revisions Needed';
        case 'rejected':
            return 'Rejected';
        default:
            return 'Draft';
    }
}