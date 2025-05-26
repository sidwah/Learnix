<?php
include '../includes/department/header.php';

// Check if user is logged in and has proper role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    header('Location: ../signin.php');
    exit();
}

// Get course ID from URL
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

if (!$course_id) {
    header('Location: courses.php');
    exit();
}

try {
    $user_id = $_SESSION['user_id'];

    // Get department info for the logged-in user
    $dept_query = "SELECT d.department_id, d.name as department_name 
                   FROM departments d 
                   INNER JOIN department_staff ds ON d.department_id = ds.department_id 
                   WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' AND ds.deleted_at IS NULL";

    $dept_stmt = $conn->prepare($dept_query);
    $dept_stmt->bind_param("i", $user_id);
    $dept_stmt->execute();
    $dept_result = $dept_stmt->get_result();

    if ($dept_result->num_rows === 0) {
        header('Location: ../auth/login.php');
        exit();
    }

    $department = $dept_result->fetch_assoc();
    $department_id = $department['department_id'];

    // Get course details with instructor information
    $course_query = "SELECT 
                    c.*,
                    sub.name as subcategory_name,
                    cat.name as category_name,
                    GROUP_CONCAT(
                        CONCAT(u.first_name, ' ', u.last_name) 
                        ORDER BY ci.is_primary DESC, u.first_name ASC 
                        SEPARATOR ', '
                    ) as instructor_names,
                    GROUP_CONCAT(
                        u.user_id 
                        ORDER BY ci.is_primary DESC, u.first_name ASC 
                        SEPARATOR ','
                    ) as instructor_ids,
                    COUNT(DISTINCT ci.instructor_id) as instructor_count,
                    GROUP_CONCAT(t.tag_name SEPARATOR ', ') as tags
                 FROM courses c
                 LEFT JOIN subcategories sub ON c.subcategory_id = sub.subcategory_id
                 LEFT JOIN categories cat ON sub.category_id = cat.category_id
                 LEFT JOIN course_instructors ci ON c.course_id = ci.course_id AND ci.deleted_at IS NULL
                 LEFT JOIN instructors i ON ci.instructor_id = i.instructor_id AND i.deleted_at IS NULL
                 LEFT JOIN users u ON i.user_id = u.user_id AND u.deleted_at IS NULL
                 LEFT JOIN course_tag_mapping ctm ON c.course_id = ctm.course_id
                 LEFT JOIN tags t ON ctm.tag_id = t.tag_id
                 WHERE c.course_id = ? AND c.department_id = ? AND c.deleted_at IS NULL
                 GROUP BY c.course_id";

    $course_stmt = $conn->prepare($course_query);
    $course_stmt->bind_param("ii", $course_id, $department_id);
    $course_stmt->execute();
    $course_result = $course_stmt->get_result();

    if ($course_result->num_rows === 0) {
        header('Location: courses.php');
        exit();
    }

    $course = $course_result->fetch_assoc();

    // Check if course can be reviewed
    if (!in_array($course['approval_status'], ['submitted_for_review', 'under_review']) || empty($course['financial_approval_date'])) {
        header('Location: courses.php');
        exit();
    }

    // Get course sections and topics with quizzes
    $sections_query = "SELECT 
                          cs.section_id,
                          cs.title as section_title,
                          cs.position as section_position,
                          COUNT(DISTINCT st.topic_id) as topic_count,
                          COUNT(DISTINCT sq.quiz_id) as quiz_count,
                          GROUP_CONCAT(
                              DISTINCT JSON_OBJECT(
                                  'topic_id', st.topic_id,
                                  'title', st.title,
                                  'position', st.position,
                                  'is_previewable', st.is_previewable,
                                  'type', 'topic'
                              )
                              ORDER BY st.position ASC
                          ) as topics,
                          GROUP_CONCAT(
                              DISTINCT JSON_OBJECT(
                                  'quiz_id', sq.quiz_id,
                                  'title', sq.quiz_title,
                                  'type', 'quiz'
                              )
                              ORDER BY sq.quiz_id ASC
                          ) as quizzes
                       FROM course_sections cs
                       LEFT JOIN section_topics st ON cs.section_id = st.section_id 
                       LEFT JOIN section_quizzes sq ON cs.section_id = sq.section_id  
                       WHERE cs.course_id = ? AND cs.deleted_at IS NULL
                       GROUP BY cs.section_id
                       ORDER BY cs.position ASC";

    $sections_stmt = $conn->prepare($sections_query);
    $sections_stmt->bind_param("i", $course_id);
    $sections_stmt->execute();
    $sections_result = $sections_stmt->get_result();
    $sections = $sections_result->fetch_all(MYSQLI_ASSOC);

    // Get learning outcomes
    $outcomes_query = "SELECT outcome_text FROM course_learning_outcomes WHERE course_id = ? AND deleted_at IS NULL ORDER BY outcome_id";
    $outcomes_stmt = $conn->prepare($outcomes_query);
    $outcomes_stmt->bind_param("i", $course_id);
    $outcomes_stmt->execute();
    $outcomes_result = $outcomes_stmt->get_result();
    $outcomes = $outcomes_result->fetch_all(MYSQLI_ASSOC);

    // Get course requirements
    $requirements_query = "SELECT requirement_text FROM course_requirements WHERE course_id = ? AND deleted_at IS NULL ORDER BY requirement_id";
    $requirements_stmt = $conn->prepare($requirements_query);
    $requirements_stmt->bind_param("i", $course_id);
    $requirements_stmt->execute();
    $requirements_result = $requirements_stmt->get_result();
    $requirements = $requirements_result->fetch_all(MYSQLI_ASSOC);

    // Get course detailed description
    // Get course detailed description from the courses table (full_description)
    $course_description = [
        'description_content' => $course['full_description'] ?? ''
    ];

    // Calculate review progress
    $total_sections = count($sections);
    $reviewed_sections = 0; // This would be calculated based on review tracking
    $progress_percentage = $total_sections > 0 ? ($reviewed_sections / $total_sections) * 100 : 0;

    // Get instructor share for financial display
    $financial_query = "SELECT instructor_share FROM course_financial_history WHERE course_id = ? ORDER BY change_date DESC LIMIT 1";
    $financial_stmt = $conn->prepare($financial_query);
    $financial_stmt->bind_param("i", $course_id);
    $financial_stmt->execute();
    $financial_result = $financial_stmt->get_result();
    $financial_data = $financial_result->fetch_assoc();
    $instructor_share = $financial_data ? $financial_data['instructor_share'] : 70;
} catch (Exception $e) {
    error_log("Error fetching course review data: " . $e->getMessage());
    header('Location: courses.php');
    exit();
}

// Helper functions
function getCourseThumbnail($thumbnail)
{
    if (!empty($thumbnail) && file_exists("../uploads/thumbnails/" . $thumbnail)) {
        return "../uploads/thumbnails/" . $thumbnail;
    }
    return "../uploads/thumbnails/default.jpg";
}

function formatDuration($minutes)
{
    if ($minutes < 60) {
        return $minutes . " mins";
    }
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    return $hours . "h " . $mins . "m";
}
?>

<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main" class="main-content">

    <!-- Main Container -->
    <div class="container-fluid px-0">

        <!-- Course Header Section -->
        <div class="d-flex align-items-center bg-white position-relative course-hero-section pt-5" style="min-height: 320px; margin-top: 5rem;">
            <div class="container-fluid px-5">
                <div class="row">
                    <div class="col-md-7 py-4">
                        <!-- Breadcrumb / Badges -->
                        <div class="mb-3">
                            <span class="badge bg-soft-primary text-primary me-2">
                                <i class="bi-tag me-1"></i> <?php echo htmlspecialchars($course['category_name'] ?? 'Uncategorized'); ?>
                            </span>
                            <span class="badge bg-soft-success text-success me-2">
                                <i class="bi-signal me-1"></i> <?php echo htmlspecialchars($course['course_level']); ?>
                            </span>
                            <span class="badge bg-soft-info text-info">
                                <i class="bi-clock me-1"></i> <?php echo count($sections); ?> Sections
                            </span>
                        </div>

                        <!-- Title -->
                        <h1 class="h3 fw-bold text-dark mb-3"><?php echo htmlspecialchars($course['title']); ?></h1>

                        <!-- Description -->
                        <p class="text-muted mb-4" style="font-size: 0.95rem; line-height: 1.6;">
                            <?php echo htmlspecialchars($course['short_description'] ?? 'No description available'); ?>
                        </p>

                        <!-- Review Status -->
                        <div class="d-md-flex align-items-md-center text-center text-md-start mb-4">
                            <span class="fw-medium me-md-3 mb-2 mb-md-0 d-block">Status:</span>
                            <div>
                                <?php if ($course['approval_status'] === 'submitted_for_review'): ?>
                                    <span class="badge bg-soft-info text-info me-2">
                                        <i class="bi-inbox me-1"></i>Submitted for Review
                                    </span>
                                <?php elseif ($course['approval_status'] === 'under_review'): ?>
                                    <span class="badge bg-soft-warning text-warning me-2">
                                        <i class="bi-eye me-1"></i>Under Review
                                    </span>
                                <?php endif; ?>
                                <span class="badge bg-soft-success text-success">
                                    <i class="bi-currency-dollar me-1"></i>Financially Approved
                                </span>
                            </div>
                        </div>
                        <!-- Tags -->
                        <div class="mt-2">
                            <span class="fw-medium me-2">Tags:</span>
                            <?php if (!empty($course['tags'])): ?>
                                <?php foreach (explode(', ', $course['tags']) as $tag): ?>
                                    <span class="badge bg-soft-secondary text-secondary me-1"><?php echo htmlspecialchars($tag); ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted">No tags assigned</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side Info Panel -->
            <div class="col-md-5 d-none d-md-inline-block bg-primary position-absolute top-0 end-0 bottom-0">
                <div class="position-absolute top-50 translate-middle-y p-4 w-100">
                    <div class="text-white">
                        <h5 class="text-white mb-4 text-center">Course Information</h5>
                        <div class="row g-3 text-sm">
                            <div class="col-12 d-flex align-items-center text-white-70">
                                <i class="bi-person-circle me-3"></i>
                                <span>Instructor: <strong class="text-white"><?php echo htmlspecialchars($course['instructor_names'] ?? 'Not assigned'); ?></strong></span>
                            </div>
                            <div class="col-12 d-flex align-items-center text-white-70">
                                <i class="bi-calendar-event me-3"></i>
                                <span>Created: <strong class="text-white"><?php echo date('M d, Y', strtotime($course['created_at'])); ?></strong></span>
                            </div>
                            <div class="col-12 d-flex align-items-center text-white-70">
                                <i class="bi-collection me-3"></i>
                                <span>Content: <strong class="text-white"><?php echo count($sections); ?> sections, <?php echo array_sum(array_column($sections, 'topic_count')); ?> topics</strong></span>
                            </div>
                            <div class="col-12 d-flex align-items-center text-white-70">
                                <i class="bi-award me-3"></i>
                                <span>Certificate: <strong class="text-white"><?php echo $course['certificate_enabled'] ? 'Enabled' : 'Disabled'; ?></strong></span>
                            </div>
                            <div class="col-12 d-flex align-items-center text-white-70">
                                <i class="bi-currency-dollar me-3"></i>
                                <span>Price: <strong class="text-white"><?php echo $course['price'] > 0 ? '$' . number_format($course['price'], 2) : 'Free'; ?></strong></span>
                            </div>
                            <div class="col-12 d-flex align-items-center text-white-70">
                                <i class="bi-percent me-3"></i>
                                <span>Instructor Share: <strong class="text-white"><?php echo $instructor_share; ?>%</strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Progress & Stats -->
        <div class="bg-light py-4">
            <div class="container-fluid px-4">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">Review Progress</h6>
                                    <small class="text-muted"><?php echo $reviewed_sections; ?> of <?php echo $total_sections; ?> sections reviewed</small>
                                </div>
                                <div class="progress mb-3" style="height: 12px;">
                                    <div class="progress-bar bg-gradient-primary" role="progressbar" style="width: <?php echo $progress_percentage; ?>%" aria-valuenow="<?php echo $progress_percentage; ?>" aria-valuemin="0" aria-valuemax="100">
                                        <span class="visually-hidden"><?php echo number_format($progress_percentage, 1); ?>% complete</span>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-auto">
                                        <span class="badge bg-soft-success text-success">
                                            <i class="bi-check-circle me-1"></i><?php echo $reviewed_sections; ?> Sections Reviewed
                                        </span>
                                    </div>
                                    <div class="col-auto">
                                        <span class="badge bg-soft-secondary text-secondary">
                                            <i class="bi-clock me-1"></i><?php echo ($total_sections - $reviewed_sections); ?> Pending
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="icon icon-xl icon-soft-info mb-3 mx-auto">
                                    <i class="bi-clipboard-data"></i>
                                </div>
                                <h6 class="mb-2">Review Notes</h6>
                                <div class="h4 text-primary mb-2" id="noteCount">0</div>
                                <small class="text-muted">Total notes added</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Content Navigation -->
        <div class="container-fluid px-4 py-4">
            <!-- Nav Pills -->
            <ul class="nav nav-segment nav-pills nav-fill mx-auto mb-4" id="courseContentTabs" role="tablist" style="max-width: 60rem;">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" href="#content-panel" id="content-tab" data-bs-toggle="tab" data-bs-target="#content-panel" role="tab" aria-controls="content-panel" aria-selected="true">
                        <i class="bi-play-circle me-2"></i>Course Content
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" href="#description-panel" id="description-tab" data-bs-toggle="tab" data-bs-target="#description-panel" role="tab" aria-controls="description-panel" aria-selected="false">
                        <i class="bi-file-text me-2"></i>Course Description
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" href="#outcomes-panel" id="outcomes-tab" data-bs-toggle="tab" data-bs-target="#outcomes-panel" role="tab" aria-controls="outcomes-panel" aria-selected="false">
                        <i class="bi-bullseye me-2"></i>Learning Outcomes
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" href="#notes-panel" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes-panel" role="tab" aria-controls="notes-panel" aria-selected="false">
                        <i class="bi-journal-text me-2"></i>Review Notes <span class="badge bg-primary ms-1" id="notesBadge">0</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Tab Content -->
        <div class="tab-content" id="courseContentTabsContent">
            <!-- Course Content Tab -->
            <div class="tab-pane fade show active" id="content-panel" role="tabpanel">
                <div class="container-fluid px-0">
                    <div class="row g-0">
                        <!-- Enhanced Sticky Course Navigation Sidebar -->
                        <div class="col-lg-4">
                            <div class="course-sidebar bg-white border-end" style="position: sticky; top: 20px; height: calc(100vh - 40px); overflow-y: auto;">
                                <div class="sidebar-header p-4 border-bottom bg-gradient-primary text-white">
                                    <h6 class="mb-1 text-white">
                                        <i class="bi-list-ul me-2"></i>Course Sections
                                    </h6>
                                    <small class="text-white-75">Navigate through course content</small>
                                </div>
                                <div class="accordion" id="sectionsAccordion">
                                    <?php foreach ($sections as $index => $section):
                                        $topics = !empty($section['topics']) ? json_decode('[' . $section['topics'] . ']', true) : [];
                                        $quizzes = !empty($section['quizzes']) ? json_decode('[' . $section['quizzes'] . ']', true) : [];
                                        $section_status = 'pending'; // This would be determined by review tracking
                                        $is_expanded = $index === 0; // First section expanded by default
                                        $total_items = count($topics) + count($quizzes);
                                    ?>
                                        <div class="accordion-item border-0 border-bottom">
                                            <h2 class="accordion-header" id="heading<?php echo $section['section_id']; ?>">
                                                <button class="accordion-button <?php echo $is_expanded ? '' : 'collapsed'; ?> section-header" type="button" data-bs-toggle="collapse" data-bs-target="#section<?php echo $section['section_id']; ?>" aria-expanded="<?php echo $is_expanded ? 'true' : 'false'; ?>" aria-controls="section<?php echo $section['section_id']; ?>">
                                                    <div class="d-flex align-items-center w-100">
                                                        <?php if ($section_status === 'completed'): ?>
                                                            <span class="badge bg-soft-success text-success me-3">
                                                                <i class="bi-check-circle"></i>
                                                            </span>
                                                        <?php elseif ($section_status === 'current'): ?>
                                                            <span class="badge bg-primary me-3">
                                                                <i class="bi-eye"></i>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-soft-secondary text-secondary me-3">
                                                                <i class="bi-clock"></i>
                                                            </span>
                                                        <?php endif; ?>
                                                        <span class="flex-grow-1 fw-medium"><?php echo htmlspecialchars($section['section_title']); ?></span>
                                                        <small class="text-muted"><?php echo $total_items; ?> items</small>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="section<?php echo $section['section_id']; ?>" class="accordion-collapse collapse <?php echo $is_expanded ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $section['section_id']; ?>" data-bs-parent="#sectionsAccordion">
                                                <div class="accordion-body pt-0">
                                                    <div class="topic-list">
                                                        <?php
                                                        // Separate topics and quizzes, sort each by created_at, then merge (topics first)
                                                        $sorted_topics = [];
                                                        $sorted_quizzes = [];

                                                        if (is_array($topics) && count($topics) > 0) {
                                                            // Sort topics by created_at ASC
                                                            usort($topics, function($a, $b) {
                                                                $a_time = isset($a['created_at']) ? strtotime($a['created_at']) : 0;
                                                                $b_time = isset($b['created_at']) ? strtotime($b['created_at']) : 0;
                                                                return $a_time - $b_time;
                                                            });
                                                            foreach ($topics as $t) {
                                                                if ($t !== null) $sorted_topics[] = $t;
                                                            }
                                                        }
                                                        if (is_array($quizzes) && count($quizzes) > 0) {
                                                            // Sort quizzes by created_at ASC
                                                            usort($quizzes, function($a, $b) {
                                                                $a_time = isset($a['created_at']) ? strtotime($a['created_at']) : 0;
                                                                $b_time = isset($b['created_at']) ? strtotime($b['created_at']) : 0;
                                                                return $a_time - $b_time;
                                                            });
                                                            foreach ($quizzes as $q) {
                                                                if ($q !== null) $sorted_quizzes[] = $q;
                                                            }
                                                        }

                                                        // Merge: topics first, then quizzes
                                                        $all_items = array_merge($sorted_topics, $sorted_quizzes);

                                                        if (empty($all_items)) {
                                                            echo '<div class="text-muted text-center py-3">No topics or quizzes in this section.</div>';
                                                        } else {
                                                            foreach ($all_items as $item):
                                                                if ($item === null) continue;
                                                                $is_quiz = isset($item['type']) && $item['type'] === 'quiz';
                                                                $item_id = $is_quiz ? ($item['quiz_id'] ?? null) : ($item['topic_id'] ?? null);
                                                                $item_title = $item['title'] ?? ($is_quiz ? ($item['quiz_title'] ?? 'Untitled Quiz') : 'Untitled Topic');
                                                                $click_function = $is_quiz
                                                                    ? ($item_id !== null ? "loadQuiz({$item_id})" : '')
                                                                    : ($item_id !== null ? "loadTopic({$item_id})" : '');
                                                                $icon = $is_quiz ? 'bi-question-circle' : 'bi-play';

                                                                // If item_id is null, skip rendering this item
                                                                if ($item_id === null) continue;
                                                        ?>
                                                                <div class="topic-item" data-item-id="<?php echo $item_id; ?>" data-item-type="<?php echo $is_quiz ? 'quiz' : 'topic'; ?>" <?php if ($click_function) echo 'onclick="' . $click_function . '"'; ?>>
                                                                    <div class="d-flex align-items-center">
                                                                        <i class="<?php echo $icon; ?> me-2"></i>
                                                                        <span class="flex-grow-1"><?php echo htmlspecialchars($item_title); ?></span>
                                                                        <?php if ($is_quiz): ?>
                                                                            <span class="badge bg-soft-info text-info me-2 small">Quiz</span>
                                                                        <?php endif; ?>
                                                                        <span class="topic-status"><i class="bi-circle text-muted"></i></span>
                                                                    </div>
                                                                </div>
                                                        <?php
                                                            endforeach;
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Main Content Area -->
                        <div class="col-lg-8">
                            <div class="content-main">
                                <!-- Content will be loaded here via AJAX -->
                                <div class="content-placeholder text-center py-5">
                                    <div class="icon icon-xl icon-soft-primary mb-3 mx-auto">
                                        <i class="bi-play-circle"></i>
                                    </div>
                                    <h5 class="mb-2">Select a Topic or Quiz to Begin Review</h5>
                                    <p class="text-muted">Choose a topic or quiz from the sidebar to start reviewing the course content.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Course Description Tab -->
            <div class="tab-pane fade" id="description-panel" role="tabpanel">
                <div class="container-fluid px-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi-file-text me-2"></i>Course Description
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($course_description['description_content'])): ?>
                                <div class="course-description-content">
                                    <?php echo $course_description['description_content']; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="bi-file-text text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <h6 class="text-muted mt-2">No detailed description available</h6>
                                    <p class="text-muted small">The instructor hasn't provided a detailed course description yet.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Learning Outcomes & Requirements Tab -->
            <div class="tab-pane fade" id="outcomes-panel" role="tabpanel">
                <div class="container-fluid px-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-soft-success text-success">
                                    <h5 class="mb-0">
                                        <i class="bi-bullseye me-2"></i>Learning Outcomes
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-3">What students will achieve after completing this course:</p>
                                    <?php if (!empty($outcomes)): ?>
                                        <ul class="list-unstyled">
                                            <?php foreach ($outcomes as $outcome): ?>
                                                <li class="mb-2">
                                                    <i class="bi-check-circle text-success me-2"></i>
                                                    <?php echo htmlspecialchars($outcome['outcome_text']); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <div class="text-center py-3">
                                            <i class="bi-bullseye text-muted" style="font-size: 2rem; opacity: 0.3;"></i>
                                            <p class="text-muted mt-2">No learning outcomes specified for this course.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-soft-warning text-warning">
                                    <h5 class="mb-0">
                                        <i class="bi-list-check me-2"></i>Requirements
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-3">Prerequisites and requirements for this course:</p>
                                    <?php if (!empty($requirements)): ?>
                                        <ul class="list-unstyled">
                                            <?php foreach ($requirements as $requirement): ?>
                                                <li class="mb-2">
                                                    <i class="bi-arrow-right text-warning me-2"></i>
                                                    <?php echo htmlspecialchars($requirement['requirement_text']); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <div class="text-center py-3">
                                            <i class="bi-list-check text-muted" style="font-size: 2rem; opacity: 0.3;"></i>
                                            <p class="text-muted mt-2">No specific requirements listed for this course.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Review Notes Tab -->
            <div class="tab-pane fade" id="notes-panel" role="tabpanel">
                <div class="container-fluid px-4">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header">
                                    <h5 class="mb-0">Review Notes & Feedback</h5>
                                    <p class="text-muted mb-0 mt-1">Add notes for sections and topics during your review</p>
                                </div>
                                <div class="card-body">
                                    <div id="reviewNotesList">
                                        <!-- Notes will be loaded here -->
                                        <div class="text-center py-4">
                                            <i class="bi-journal-text text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                                            <h6 class="text-muted mt-2">No review notes yet</h6>
                                            <p class="text-muted small">Start reviewing content to add notes</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header">
                                    <h6 class="mb-0">Add Review Note</h6>
                                </div>
                                <div class="card-body">
                                    <form id="addNoteForm">
                                        <div class="mb-3">
                                            <label class="form-label">Note Type</label>
                                            <select class="form-select" id="noteType">
                                                <option value="general">General Note</option>
                                                <option value="suggestion">Suggestion</option>
                                                <option value="concern">Concern</option>
                                                <option value="positive">Positive Feedback</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Note</label>
                                            <textarea class="form-control" id="noteContent" rows="4" placeholder="Enter your review note..."></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="bi-plus-circle me-1"></i>Add Note
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Back to Courses Button - Bottom Left -->
    <div class="position-fixed bottom-0 start-0 mb-4 ms-4" style="z-index: 1030;">
        <button class="btn btn-dark btn-lg rounded-circle shadow-lg d-flex align-items-center justify-content-center"
            style="width: 60px; height: 60px;"
            onclick="exitReview()"
            data-bs-toggle="tooltip"
            data-bs-placement="right"
            title="Exit Review Mode">
            <i class="bi-arrow-left fs-4"></i>
        </button>
    </div>

    <!-- Floating Review Action Button -->
    <div id="floatingReviewButton" class="position-fixed bottom-0 end-0 mb-4 me-4" style="z-index: 1030;">
        <button class="btn btn-warning btn-lg rounded-circle shadow-lg d-flex align-items-center justify-content-center floating-btn"
            style="width: 60px; height: 60px;"
            data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi-clipboard-check fs-4"></i>
        </button>

        <ul class="dropdown-menu dropdown-menu-end p-2 mb-2 fade-in-up" style="min-width: 250px;">
            <li class="dropdown-header">
                <strong>Review Actions</strong>
            </li>
            <li>
                <hr class="dropdown-divider">
            </li>

            <li class="mb-2">
                <a class="dropdown-item rounded-3 d-flex align-items-center text-success" href="#" onclick="approveCourse()">
                    <span class="dropdown-item-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 32px; height: 32px;">
                        <i class="bi-check-circle-fill"></i>
                    </span>
                    <span class="ms-2"><strong>Approve Course</strong></span>
                </a>
            </li>

            <li class="mb-2">
                <a class="dropdown-item rounded-3 d-flex align-items-center text-warning" href="#" onclick="requestRevisions()">
                    <span class="dropdown-item-icon bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 32px; height: 32px;">
                        <i class="bi-arrow-clockwise"></i>
                    </span>
                    <span class="ms-2"><strong>Request Revisions</strong></span>
                </a>
            </li>

            <li>
                <a class="dropdown-item rounded-3 d-flex align-items-center text-danger" href="#" onclick="rejectCourse()">
                    <span class="dropdown-item-icon bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 32px; height: 32px;">
                        <i class="bi-x-circle-fill"></i>
                    </span>
                    <span class="ms-2"><strong>Reject Course</strong></span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Simple Footer -->
    <footer class="bg-white border-top mt-5">
        <div class="container-fluid px-4 py-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">&copy; 2025 Learnix. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 text-muted">
                        <i class="bi-shield-check me-1"></i>Secure Course Review System
                    </p>
                </div>
            </div>
        </div>
    </footer>

</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- Modals remain the same as before... -->
<!-- Approval Confirmation Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-success" id="approvalModalLabel">
                    <i class="bi-check-circle-fill me-2"></i>Approve Course
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="icon icon-xl icon-soft-success mx-auto mb-3">
                        <i class="bi-check-circle-fill"></i>
                    </div>
                    <h6>Confirm Course Approval</h6>
                    <p class="text-muted">Are you sure you want to approve this course? This action will notify the instructor and move the course forward in the approval process.</p>
                </div>
                <div class="alert alert-info border-0">
                    <h6 class="alert-heading"><?php echo htmlspecialchars($course['title']); ?></h6>
                    <p class="mb-0">Instructor: <?php echo htmlspecialchars($course['instructor_names'] ?? 'Not assigned'); ?></p>
                </div>
                <div class="mb-3">
                    <label for="approvalComments" class="form-label">Approval Comments (Optional)</label>
                    <textarea class="form-control" id="approvalComments" rows="3" placeholder="Add any comments about the approval..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="confirmApproval()">
                    <i class="bi-check-circle me-1"></i>Approve Course
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Revision Request Modal -->
<div class="modal fade" id="revisionModal" tabindex="-1" aria-labelledby="revisionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-warning" id="revisionModalLabel">
                    <i class="bi-arrow-clockwise me-2"></i>Request Revisions
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="icon icon-xl icon-soft-warning mx-auto mb-3">
                        <i class="bi-arrow-clockwise"></i>
                    </div>
                    <h6>Request Course Revisions</h6>
                    <p class="text-muted">Provide detailed feedback for the instructor to improve the course content.</p>
                </div>
                <form>
                    <div class="mb-3">
                        <label for="revisionFeedback" class="form-label">Revision Feedback <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="revisionFeedback" rows="5" placeholder="Provide detailed feedback about what needs to be revised..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="revisionPriority" class="form-label">Priority Level</label>
                        <select class="form-select" id="revisionPriority">
                            <option value="medium" selected>Medium - Should be addressed</option>
                            <option value="high">High - Must be fixed before resubmission</option>
                            <option value="low">Low - Minor improvements suggested</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="confirmRevisionRequest()">
                    <i class="bi-send me-1"></i>Send Revision Request
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Confirmation Modal -->
<div class="modal fade" id="rejectionModal" tabindex="-1" aria-labelledby="rejectionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-danger" id="rejectionModalLabel">
                    <i class="bi-x-circle-fill me-2"></i>Reject Course
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="icon icon-xl icon-soft-danger mx-auto mb-3">
                        <i class="bi-x-circle-fill"></i>
                    </div>
                    <h6>Reject Course Submission</h6>
                    <p class="text-muted">This action will reject the course and require the instructor to start over with significant changes.</p>
                </div>
                <form>
                    <div class="mb-3">
                        <label for="rejectionReason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectionReason" rows="4" placeholder="Provide clear reasons for rejection..."></textarea>
                    </div>
                    <div class="alert alert-danger border-0">
                        <h6 class="alert-heading">Warning</h6>
                        <p class="mb-0">Course rejection is a final action. The instructor will need to make substantial changes before resubmission.</p>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmRejection()">
                    <i class="bi-x-circle me-1"></i>Reject Course
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .main-content {
        background-color: #f8f9fc;
        min-height: 100vh;
    }

    .course-hero-section {
        min-height: 250px;
        border-bottom: 1px solid #eef2f7;
    }

    .text-white-70 {
        color: rgba(255, 255, 255, 0.7) !important;
    }

    .text-white-75 {
        color: rgba(255, 255, 255, 0.75) !important;
    }

    .btn-xs {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        border-radius: 0.375rem;
    }

    .course-sidebar {
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
    }

    .sidebar-header {
        background: linear-gradient(135deg, #377dff 0%, #5a8dee 100%);
    }

    .section-header {
        background: transparent !important;
        border: none !important;
        padding: 1rem !important;
        font-weight: 500 !important;
        color: #495057 !important;
        box-shadow: none !important;
    }

    .section-header:not(.collapsed) {
        background-color: rgba(55, 125, 255, 0.05) !important;
        color: #377dff !important;
    }

    .section-header:focus {
        box-shadow: none !important;
    }

    .topic-list {
        padding: 0 1rem 1rem 1rem;
    }

    .topic-item {
        padding: 0.75rem 1rem;
        margin-bottom: 0.5rem;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.875rem;
    }

    .topic-item:hover:not(.disabled):not(.current) {
        background-color: #f8f9fa;
        transform: translateX(3px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .topic-item.current {
        background-color: #377dff;
        color: white;
        font-weight: 500;
        box-shadow: 0 4px 8px rgba(55, 125, 255, 0.3);
    }

    .topic-item.current:hover {
        background-color: #2968ff;
        transform: translateX(2px);
        color: white;
    }

    .topic-item.completed {
        background-color: rgba(0, 201, 167, 0.1);
        color: #00c9a7;
    }

    .topic-item.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .content-main {
        background-color: white;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
    }

    .content-placeholder {
        background-color: #f8f9fa;
    }

    .floating-btn {
        animation: pulse-warning 2s infinite;
        transition: all 0.3s ease;
    }

    .floating-btn:hover {
        transform: scale(1.1);
        animation: none;
    }

    .fade-in-up {
        animation: fadeInUp 0.3s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes pulse-warning {
        0% {
            box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(255, 193, 7, 0);
        }
    }

    .dropdown-item-icon {
        flex-shrink: 0;
    }

    .dropdown-item:hover .dropdown-item-icon {
        transform: scale(1.1);
        transition: transform 0.2s ease;
    }

    .nav-segment {
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        padding: 0.25rem;
    }

    .nav-segment .nav-link {
        border: none;
        color: #677788;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        border-radius: 0.375rem;
        transition: all 0.2s ease;
    }

    .nav-segment .nav-link.active {
        background-color: white;
        color: #377dff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .nav-segment .nav-link:hover:not(.active) {
        color: #377dff;
        background-color: rgba(255, 255, 255, 0.5);
    }

    .progress-bar {
        background: linear-gradient(90deg, #377dff 0%, #5a8dee 100%);
    }

    .bg-soft-info {
        background-color: rgba(54, 162, 235, 0.1) !important;
    }

    .bg-soft-success {
        background-color: rgba(0, 201, 167, 0.1) !important;
    }

    .bg-soft-danger {
        background-color: rgba(255, 107, 107, 0.1) !important;
    }

    .bg-soft-warning {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }

    .bg-soft-primary {
        background-color: rgba(55, 125, 255, 0.1) !important;
    }

    .bg-soft-secondary {
        background-color: rgba(108, 117, 125, 0.1) !important;
    }

    .icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }

    .icon-sm {
        width: 2rem;
        height: 2rem;
        font-size: 0.875rem;
    }

    .icon-md {
        width: 3rem;
        height: 3rem;
        font-size: 1.25rem;
    }

    .icon-xl {
        width: 4rem;
        height: 4rem;
        font-size: 1.5rem;
    }

    .icon-soft-warning {
        background-color: rgba(255, 193, 7, 0.1);
        color: #ffc107;
    }

    .icon-soft-info {
        background-color: rgba(54, 162, 235, 0.1);
        color: #36a2eb;
    }

    .icon-soft-danger {
        background-color: rgba(255, 107, 107, 0.1);
        color: #ff6b6b;
    }

    .icon-soft-primary {
        background-color: rgba(55, 125, 255, 0.1);
        color: #377dff;
    }

    .icon-soft-success {
        background-color: rgba(0, 201, 167, 0.1);
        color: #00c9a7;
    }

    .accordion-item {
        transition: background-color 0.3s ease;
    }

    .accordion-button {
        transition: all 0.3s ease;
        padding: 1rem 1.5rem !important;
    }

    .accordion-button:not(.collapsed) {
        background-color: rgba(55, 125, 255, 0.1) !important;
        color: #377dff !important;
    }

    .accordion-button:focus {
        box-shadow: none !important;
        outline: none;
    }

    /* Course Description Content */
    .course-description-content {
        font-size: 1rem;
        line-height: 1.6;
        color: #495057;
    }

    .course-description-content h1,
    .course-description-content h2,
    .course-description-content h3,
    .course-description-content h4,
    .course-description-content h5,
    .course-description-content h6 {
        color: #1e2022;
        margin-top: 1.5rem;
        margin-bottom: 1rem;
    }

    .course-description-content p {
        margin-bottom: 1rem;
    }

    .course-description-content ul,
    .course-description-content ol {
        margin-bottom: 1rem;
        padding-left: 1.5rem;
    }

    .course-description-content blockquote {
        border-left: 4px solid #377dff;
        padding-left: 1rem;
        margin: 1rem 0;
        font-style: italic;
        color: #6c757d;
    }

    .course-description-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 1rem 0;
    }

    .course-description-content code {
        background-color: #f8f9fa;
        padding: 0.2rem 0.4rem;
        border-radius: 3px;
        font-size: 0.9em;
        color: #e83e8c;
    }

    .course-description-content pre {
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        overflow-x: auto;
        margin: 1rem 0;
    }

    .course-description-content table {
        width: 100%;
        border-collapse: collapse;
        margin: 1rem 0;
    }

    .course-description-content th,
    .course-description-content td {
        border: 1px solid #dee2e6;
        padding: 0.75rem;
        text-align: left;
    }

    .course-description-content th {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    /* Content Styles */
    .content-header {
        border-bottom: 2px solid #f8f9fa;
    }

    .video-container {
        position: relative;
        background: #000;
    }

    .video-player {
        border-radius: 0;
        width: 100%;
        height: 100%;
    }

    .content-tabs .nav-tabs {
        border-bottom: 2px solid #f8f9fa;
        background-color: #fafbfc;
        margin: 0;
    }

    .content-tabs .nav-link {
        border: none;
        color: #677788;
        padding: 1rem 1.5rem;
        font-weight: 500;
        background-color: transparent;
    }

    .content-tabs .nav-link.active {
        background-color: white;
        color: #377dff;
        border-bottom: 3px solid #377dff;
    }

    .content-tabs .nav-link:hover:not(.active) {
        color: #377dff;
        background-color: rgba(255, 255, 255, 0.7);
    }

    .content-heading {
        color: #1e2022;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .content-subheading {
        color: #495057;
        font-weight: 500;
        margin-bottom: 0.75rem;
    }

    .content-section {
        border-bottom: 1px solid #eef2f7;
        padding-bottom: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .content-section:last-child {
        border-bottom: none;
        padding-bottom: 0;
        margin-bottom: 0;
    }

    .code-example {
        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
        font-size: 0.875rem;
        position: relative;
    }

    .code-example pre {
        margin: 0;
        padding: 0;
    }

    .resource-card {
        transition: all 0.2s ease;
    }

    .resource-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }

    .quiz-content {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        border: 2px solid #dee2e6;
    }

    .quiz-header {
        background: linear-gradient(135deg, #377dff 0%, #5a8dee 100%);
        color: white;
        border-radius: 10px 10px 0 0;
    }

    /* Text Content Styling */
    .text-content {
        background-color: white;
    }

    .topic-text-content {
        font-size: 1rem;
        line-height: 1.8;
        color: #495057;
    }

    .topic-text-content h1,
    .topic-text-content h2,
    .topic-text-content h3,
    .topic-text-content h4,
    .topic-text-content h5,
    .topic-text-content h6 {
        color: #1e2022;
        margin-top: 1.5rem;
        margin-bottom: 1rem;
        font-weight: 600;
    }

    .topic-text-content p {
        margin-bottom: 1rem;
    }

    .topic-text-content ul,
    .topic-text-content ol {
        margin-bottom: 1rem;
        padding-left: 1.5rem;
    }

    .topic-text-content li {
        margin-bottom: 0.5rem;
    }

    .topic-text-content blockquote {
        border-left: 4px solid #377dff;
        padding-left: 1rem;
        margin: 1rem 0;
        font-style: italic;
        color: #6c757d;
    }

    .topic-text-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 1rem 0;
    }

    .topic-text-content code {
        background-color: #f8f9fa;
        padding: 0.2rem 0.4rem;
        border-radius: 3px;
        font-size: 0.9em;
        color: #e83e8c;
    }

    .topic-text-content pre {
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        overflow-x: auto;
        margin: 1rem 0;
    }

    .topic-text-content strong {
        font-weight: 600;
    }

    .topic-text-content em {
        font-style: italic;
    }

    .topic-text-content table {
        width: 100%;
        border-collapse: collapse;
        margin: 1rem 0;
    }

    .topic-text-content th,
    .topic-text-content td {
        border: 1px solid #dee2e6;
        padding: 0.75rem;
        text-align: left;
    }

    .topic-text-content th {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    /* Responsive Design */
    @media (max-width: 992px) {
        .course-sidebar {
            position: relative !important;
            top: auto !important;
            height: auto !important;
            margin-bottom: 1rem;
        }

        .floating-btn {
            width: 50px !important;
            height: 50px !important;
        }

        .floating-btn .fs-4 {
            font-size: 1rem !important;
        }

        .course-hero-section {
            min-height: auto;
            padding: 2rem 0;
        }
    }

    @media (max-width: 768px) {
        .course-hero-section .col-md-5 {
            display: none !important;
        }

        .course-hero-section .col-md-7 {
            width: 100%;
        }

        .nav-segment .nav-link {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        .h3 {
            font-size: 1.5rem !important;
        }
    }

    html {
        scroll-behavior: smooth;
    }

    .course-sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .course-sidebar::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .course-sidebar::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .course-sidebar::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>

<script>
    const courseId = <?php echo $course_id; ?>;
    let currentTopicId = null;
    let currentQuizId = null;
    let reviewNotes = [];

    function exitReview() {
        window.location.href = 'courses.php';
    }

    function loadTopic(topicId) {
        const topicElement = document.querySelector(`.topic-item[data-item-id="${topicId}"][data-item-type="topic"]`);
        if (topicElement && topicElement.classList.contains('disabled')) {
            showToast('This topic is locked and cannot be accessed yet.', 'warning');
            return;
        }

        // Update active topic indicators
        document.querySelectorAll('.topic-item').forEach(item => {
            item.classList.remove('current');
        });
        if (topicElement) {
            topicElement.classList.add('current');
        }

        currentTopicId = topicId;
        currentQuizId = null;

        // Show loading
        showOverlay('Loading topic content...');

        // Load topic content via AJAX
        fetch('../backend/department/get_topic_content.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    topic_id: topicId,
                    course_id: courseId
                })
            })
            .then(response => response.json())
            .then(data => {
                removeOverlay();

                if (data.success) {
                    displayTopicContent(data.topic);
                    showToast('Topic loaded successfully', 'success');
                } else {
                    showToast(data.message || 'Failed to load topic', 'error');
                }
            })
            .catch(error => {
                removeOverlay();
                console.error('Error:', error);
                showToast('An error occurred while loading the topic', 'error');
            });
    }

    function loadQuiz(quizId) {
        const quizElement = document.querySelector(`.topic-item[data-item-id="${quizId}"][data-item-type="quiz"]`);
        if (quizElement && quizElement.classList.contains('disabled')) {
            showToast('This quiz is locked and cannot be accessed yet.', 'warning');
            return;
        }

        // Update active item indicators
        document.querySelectorAll('.topic-item').forEach(item => {
            item.classList.remove('current');
        });
        if (quizElement) {
            quizElement.classList.add('current');
        }

        currentQuizId = quizId;
        currentTopicId = null;

        // Show loading
        showOverlay('Loading quiz content...');

        // Load quiz content via AJAX
        fetch('../backend/department/get_quiz_content.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    quiz_id: quizId,
                    course_id: courseId
                })
            })
            .then(response => response.json())
            .then(data => {
                removeOverlay();

                if (data.success) {
                    displayQuizContent(data.quiz);
                    showToast('Quiz loaded successfully', 'success');
                } else {
                    showToast(data.message || 'Failed to load quiz', 'error');
                }
            })
            .catch(error => {
                removeOverlay();
                console.error('Error:', error);
                showToast('An error occurred while loading the quiz', 'error');
            });
    }

    function displayTopicContent(topic) {
        const contentMain = document.querySelector('.content-main');

        let contentHtml = `
        <div class="content-header bg-white p-4 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">${topic.title || 'Untitled Topic'}</h4>
                    <div class="d-flex align-items-center text-muted">
                        <small class="me-3">Section ${topic.section_position || 'N/A'}, Topic ${topic.position || 'N/A'}</small>
                        <span class="badge bg-soft-info text-info me-2">
                            <i class="bi-${getContentTypeIcon(topic.content_type)} me-1"></i>${topic.content_type || 'Content'}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    `;

        // Main content area based on type
        if (topic.content_type === 'video') {
            if (topic.video_url) {
                // Convert YouTube URLs to embed format
                let embedUrl = convertToEmbedUrl(topic.video_url);

                contentHtml += `
                <div class="video-container">
                    <div class="ratio ratio-16x9">
                        <iframe src="${embedUrl}" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                allowfullscreen>
                        </iframe>
                    </div>
                </div>
            `;
            } else if (topic.video_file) {
                // For uploaded video files
                contentHtml += `
                <div class="video-container">
                    <div class="ratio">
                        <video controls class="video-player">
                            <source src="../uploads/videos/${topic.video_file}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                </div>
            `;
            }
        } else if (topic.content_type === 'document') {
            if (topic.file_path) {
                contentHtml += `
                <div class="document-container p-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="icon icon-xl icon-soft-primary mb-3 mx-auto">
                                <i class="bi-file-earmark-text"></i>
                            </div>
                            <h5 class="mb-2">Document Content</h5>
                            <p class="text-muted mb-3">Click to view or download the document</p>
                            <a href="../uploads/documents/${topic.file_path}" target="_blank" class="btn btn-primary">
                                <i class="bi-download me-2"></i>View Document
                            </a>
                        </div>
                    </div>
                </div>
            `;
            }
        } else if (topic.content_type === 'link') {
            if (topic.external_url) {
                contentHtml += `
                <div class="link-container p-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="icon icon-xl icon-soft-info mb-3 mx-auto">
                                <i class="bi-link-45deg"></i>
                            </div>
                            <h5 class="mb-2">External Link</h5>
                            <p class="text-muted mb-3">Click to visit the external resource</p>
                            <a href="${topic.external_url}" target="_blank" class="btn btn-info">
                                <i class="bi-box-arrow-up-right me-2"></i>Visit Link
                            </a>
                        </div>
                    </div>
                </div>
            `;
            }
        } else if (topic.content_type === 'text') {
            // For text content, display it as the main content
            contentHtml += `
            <div class="text-content p-4">
                <div class="content-body">
                    ${topic.content_text ? `<div class="topic-text-content">${topic.content_text}</div>` : '<p class="text-muted">No text content available for this topic.</p>'}
                </div>
            </div>
        `;
        }

        // Add content tabs
        contentHtml += `
        <div class="content-tabs">
            <ul class="nav nav-tabs" id="contentTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab">
                        <i class="bi-info-circle me-2"></i>Additional Info
                    </button>
                </li>
                ${topic.resources && topic.resources.length > 0 ? `
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="resources-tab" data-bs-toggle="tab" data-bs-target="#resources" type="button" role="tab">
                        <i class="bi-download me-2"></i>Resources
                    </button>
                </li>
                ` : ''}
            </ul>
        </div>

        <div class="tab-content p-4" id="contentTabsContent">
            <div class="tab-pane fade show active" id="description" role="tabpanel">
                <div class="content-section">
                    ${topic.description ? `<div class="additional-description"><h6>Additional Information:</h6><p>${topic.description}</p></div>` : '<p class="text-muted">No additional information available for this topic.</p>'}
                </div>
            </div>
            ${topic.resources && topic.resources.length > 0 ? `
            <div class="tab-pane fade" id="resources" role="tabpanel">
                <div class="content-section">
                    <h6 class="content-subheading">Additional Resources</h6>
                    <div class="row g-3">
                        ${topic.resources.map(resource => `
                            <div class="col-md-6">
                                <div class="card resource-card h-100 border">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="icon icon-md icon-soft-primary me-3">
                                            <i class="bi-file-earmark"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">${resource.name}</h6>
                                            <small class="text-muted">Resource file</small>
                                        </div>
                                        <button class="btn btn-sm btn-outline-primary" onclick="window.open('../uploads/resources/${resource.name}', '_blank')">
                                            <i class="bi-download"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
            ` : ''}
        </div>
    `;

        contentMain.innerHTML = contentHtml;
    }

    // Add this function to convert YouTube URLs to embed format
    function convertToEmbedUrl(url) {
        // Handle different YouTube URL formats
        let embedUrl = url;

        // Standard YouTube watch URL
        if (url.includes('youtube.com/watch?v=')) {
            const videoId = url.split('v=')[1].split('&')[0];
            embedUrl = `https://www.youtube.com/embed/${videoId}`;
        }
        // YouTube short URL
        else if (url.includes('youtu.be/')) {
            const videoId = url.split('youtu.be/')[1].split('?')[0];
            embedUrl = `https://www.youtube.com/embed/${videoId}`;
        }
        // Vimeo URL
        else if (url.includes('vimeo.com/')) {
            const videoId = url.split('vimeo.com/')[1].split('/')[0];
            embedUrl = `https://player.vimeo.com/video/${videoId}`;
        }

        return embedUrl;
    }

    function displayQuizContent(quiz) {
        const contentMain = document.querySelector('.content-main');

        let contentHtml = `
            <div class="content-header bg-white p-4 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">${quiz.quiz_title || 'Untitled Quiz'}</h4>
                        <div class="d-flex align-items-center text-muted">
                            <small class="me-3">Section Quiz</small>
                            <span class="badge bg-soft-warning text-warning me-2">
                                <i class="bi-question-circle me-1"></i>Quiz Content
                            </span>
                            <span class="badge bg-soft-info text-info">
                                <i class="bi-clock me-1"></i>${quiz.time_limit ? quiz.time_limit + ' minutes' : 'No time limit'}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="quiz-content m-4">
                <div class="quiz-header p-4 text-center">
                    <div class="icon icon-xl icon-soft-light mb-3 mx-auto" style="background-color: rgba(255,255,255,0.2); color: white;">
                        <i class="bi-question-circle-fill"></i>
                    </div>
                    <h5 class="text-white mb-2">${quiz.quiz_title}</h5>
                    <p class="text-white-75 mb-0">${quiz.description || 'Test your knowledge with this quiz'}</p>
                </div>
                
                <div class="quiz-body p-4">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Questions</h6>
                                <div class="h4 text-primary">${quiz.question_count || 0}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Pass Mark</h6>
                                <div class="h4 text-success">${quiz.pass_mark || 0}%</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Time Limit</h6>
                                <div class="h4 text-warning">${quiz.time_limit ? quiz.time_limit + ' min' : 'Unlimited'}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-muted mb-1">Attempts</h6>
                                <div class="h4 text-info">${quiz.attempts_allowed || 'Unlimited'}</div>
                            </div>
                        </div>
                    </div>

                    ${quiz.instruction ? `
                    <div class="alert alert-info border-0 mb-4">
                        <h6 class="alert-heading">Instructions:</h6>
                        <p class="mb-0">${quiz.instruction}</p>
                    </div>
                    ` : ''}

                    <div class="quiz-settings">
                        <h6 class="mb-3">Quiz Settings:</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi-${quiz.randomize_questions == 1 ? 'check-circle text-success' : 'x-circle text-danger'} me-2"></i>
                                    <span>Randomize Questions</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi-${quiz.shuffle_answers == 1 ? 'check-circle text-success' : 'x-circle text-danger'} me-2"></i>
                                    <span>Shuffle Answers</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi-${quiz.show_correct_answers == 1 ? 'check-circle text-success' : 'x-circle text-danger'} me-2"></i>
                                    <span>Show Correct Answers</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi-${quiz.is_required == 1 ? 'exclamation-triangle text-warning' : 'info-circle text-info'} me-2"></i>
                                    <span>${quiz.is_required == 1 ? 'Required Quiz' : 'Optional Quiz'}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    ${quiz.questions && quiz.questions.length > 0 ? `
                    <div class="quiz-questions mt-4">
                        <h6 class="mb-3">Sample Questions Preview:</h6>
                        ${quiz.questions.slice(0, 3).map((question, index) => `
                            <div class="question-preview card mb-3">
                                <div class="card-body">
                                    <div class="d-flex align-items-start">
                                        <span class="badge bg-primary me-3">${index + 1}</span>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-2">${question.question_text}</h6>
                                            <small class="text-muted">Type: ${question.question_type} | Points: ${question.points || 1}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                        ${quiz.questions.length > 3 ? `
                        <div class="text-center">
                            <small class="text-muted">... and ${quiz.questions.length - 3} more questions</small>
                        </div>
                        ` : ''}
                    </div>
                    ` : ''}
                </div>
            </div>
        `;

        contentMain.innerHTML = contentHtml;
    }

    function getContentTypeIcon(type) {
        const icons = {
            'video': 'play-circle',
            'text': 'file-text',
            'link': 'link-45deg',
            'document': 'file-earmark'
        };
        return icons[type] || 'file-text';
    }

    function approveCourse() {
        const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
        modal.show();
    }

    function confirmApproval() {
        const comments = document.getElementById('approvalComments').value;

        showOverlay('Approving course...');

        fetch('../backend/department/review_course.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    course_id: courseId,
                    action: 'approve',
                    comments: comments
                })
            })
            .then(response => response.json())
            .then(data => {
                removeOverlay();
                bootstrap.Modal.getInstance(document.getElementById('approvalModal')).hide();

                if (data.success) {
                    showToast('Course approved successfully! Instructor has been notified.', 'success');
                    setTimeout(() => {
                        window.location.href = 'courses.php';
                    }, 2000);
                } else {
                    showToast(data.message || 'Failed to approve course', 'error');
                }
            })
            .catch(error => {
                removeOverlay();
                console.error('Error:', error);
                showToast('An error occurred while approving the course', 'error');
            });
    }

    function requestRevisions() {
        const modal = new bootstrap.Modal(document.getElementById('revisionModal'));
        modal.show();
    }

    function confirmRevisionRequest() {
        const feedback = document.getElementById('revisionFeedback').value;
        const priority = document.getElementById('revisionPriority').value;

        if (!feedback.trim()) {
            showToast('Please provide revision feedback before submitting.', 'warning');
            return;
        }

        showOverlay('Sending revision request...');

        fetch('../backend/department/review_course.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    course_id: courseId,
                    action: 'request_revisions',
                    feedback: feedback,
                    priority: priority
                })
            })
            .then(response => response.json())
            .then(data => {
                removeOverlay();
                bootstrap.Modal.getInstance(document.getElementById('revisionModal')).hide();

                if (data.success) {
                    showToast('Revision request sent to instructor with your feedback.', 'info');

                    // Clear form
                    document.getElementById('revisionFeedback').value = '';

                    setTimeout(() => {
                        window.location.href = 'courses.php';
                    }, 2000);
                } else {
                    showToast(data.message || 'Failed to send revision request', 'error');
                }
            })
            .catch(error => {
                removeOverlay();
                console.error('Error:', error);
                showToast('An error occurred while sending revision request', 'error');
            });
    }

    function rejectCourse() {
        const modal = new bootstrap.Modal(document.getElementById('rejectionModal'));
        modal.show();
    }

    function confirmRejection() {
        const reason = document.getElementById('rejectionReason').value;

        if (!reason.trim()) {
            showToast('Please provide a reason for rejection before proceeding.', 'warning');
            return;
        }

        showOverlay('Rejecting course...');

        fetch('../backend/department/review_course.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    course_id: courseId,
                    action: 'reject',
                    reason: reason
                })
            })
            .then(response => response.json())
            .then(data => {
                removeOverlay();
                bootstrap.Modal.getInstance(document.getElementById('rejectionModal')).hide();

                if (data.success) {
                    showToast('Course has been rejected. Instructor has been notified.', 'error');

                    // Clear form
                    document.getElementById('rejectionReason').value = '';

                    setTimeout(() => {
                        window.location.href = 'courses.php';
                    }, 2000);
                } else {
                    showToast(data.message || 'Failed to reject course', 'error');
                }
            })
            .catch(error => {
                removeOverlay();
                console.error('Error:', error);
                showToast('An error occurred while rejecting the course', 'error');
            });
    }

    function loadReviewNotes() {
        fetch('../backend/department/get_review_notes.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    course_id: courseId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    reviewNotes = data.notes;
                    displayReviewNotes();
                    updateNoteCount();
                }
            })
            .catch(error => {
                console.error('Error loading review notes:', error);
            });
    }

    function displayReviewNotes() {
        const notesList = document.getElementById('reviewNotesList');

        if (reviewNotes.length === 0) {
            notesList.innerHTML = `
                <div class="text-center py-4">
                    <i class="bi-journal-text text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                    <h6 class="text-muted mt-2">No review notes yet</h6>
                    <p class="text-muted small">Start reviewing content to add notes</p>
                </div>
            `;
            return;
        }

        const notesHtml = reviewNotes.map(note => `
            <div class="review-note-item mb-3 p-3 border rounded">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-soft-${getNoteTypeColor(note.note_type)} text-${getNoteTypeColor(note.note_type)}">
                        ${note.note_type}
                    </span>
                    <small class="text-muted">${formatDate(note.created_at)}</small>
                </div>
                <p class="mb-0">${note.note_content}</p>
                ${note.topic_title ? `<small class="text-muted">Topic: ${note.topic_title}</small>` : ''}
            </div>
        `).join('');

        notesList.innerHTML = notesHtml;
    }

    function getNoteTypeColor(type) {
        const colors = {
            'general': 'primary',
            'suggestion': 'info',
            'concern': 'warning',
            'positive': 'success'
        };
        return colors[type] || 'primary';
    }

    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString();
    }

    function updateNoteCount() {
        document.getElementById('noteCount').textContent = reviewNotes.length;
        document.getElementById('notesBadge').textContent = reviewNotes.length;
    }

    // Add note form handler
    document.getElementById('addNoteForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const noteType = document.getElementById('noteType').value;
        const noteContent = document.getElementById('noteContent').value;

        if (!noteContent.trim()) {
            showToast('Please enter a note before submitting.', 'warning');
            return;
        }

        fetch('../backend/department/add_review_note.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    course_id: courseId,
                    topic_id: currentTopicId,
                    quiz_id: currentQuizId,
                    note_type: noteType,
                    note_content: noteContent
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Review note added successfully', 'success');

                    // Clear form
                    document.getElementById('noteContent').value = '';

                    // Reload notes
                    loadReviewNotes();
                } else {
                    showToast(data.message || 'Failed to add note', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while adding the note', 'error');
            });
    });

    function showToast(message, type = 'info') {
        // Create toast element dynamically
        const toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '1100';

        const toastElement = document.createElement('div');
        toastElement.className = 'toast show';
        toastElement.setAttribute('role', 'alert');

        const typeColors = {
            success: 'bg-success',
            error: 'bg-danger',
            warning: 'bg-warning',
            info: 'bg-primary'
        };

        const typeIcons = {
            success: 'bi-check-circle-fill',
            error: 'bi-exclamation-circle-fill',
            warning: 'bi-exclamation-triangle-fill',
            info: 'bi-info-circle-fill'
        };

        toastElement.innerHTML = `
            <div class="toast-header ${typeColors[type]} text-white">
                <i class="${typeIcons[type]} me-2"></i>
                <strong class="me-auto">${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
                <small>just now</small>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">${message}</div>
        `;

        toastContainer.appendChild(toastElement);
        document.body.appendChild(toastContainer);

        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: 4000
        });

        toast.show();

        // Remove toast container after it's hidden
        toastElement.addEventListener('hidden.bs.toast', () => {
            document.body.removeChild(toastContainer);
        });
    }

    function showOverlay(message = null) {
        const existingOverlay = document.querySelector('.custom-overlay');
        if (existingOverlay) {
            existingOverlay.remove();
        }

        const overlay = document.createElement('div');
        overlay.className = 'custom-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        `;
        overlay.innerHTML = `
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            ${message ? `<div class="text-white ms-3">${message}</div>` : ''}
        `;

        document.body.appendChild(overlay);
    }

    function removeOverlay() {
        const overlay = document.querySelector('.custom-overlay');
        if (overlay) {
            overlay.remove();
        }
    }

    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Load review notes
        loadReviewNotes();

        // Show welcome message
        setTimeout(() => {
            showToast('Review mode activated. You can now experience this course as a student would.', 'info');
        }, 1000);
    });
</script>

<?php include '../includes/department/footer.php'; ?>