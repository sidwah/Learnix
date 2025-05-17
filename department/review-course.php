<?php
// department/review-course.php
include '../includes/department/header.php';
require_once '../backend/config.php';
require_once '../includes/department/course_modals.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    header('Location: ../admin/departments.php');
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
    header('Location: ../admin/departments.php');
    exit;
}

$department = $dept_result->fetch_assoc();
$department_id = $department['department_id'];

// Check if course_id is provided
if (!isset($_GET['course_id']) || empty($_GET['course_id'])) {
    header('Location: courses.php');
    exit;
}

$course_id = intval($_GET['course_id']);

// Fetch course details and validate department access
$course_query = "SELECT c.*, cat.name as category_name, sub.name as subcategory_name 
                FROM courses c
                JOIN subcategories sub ON c.subcategory_id = sub.subcategory_id
                JOIN categories cat ON sub.category_id = cat.category_id
                WHERE c.course_id = ? AND c.department_id = ? AND c.deleted_at IS NULL";
$course_stmt = $conn->prepare($course_query);
$course_stmt->bind_param("ii", $course_id, $department_id);
$course_stmt->execute();
$course_result = $course_stmt->get_result();

if ($course_result->num_rows === 0) {
    header('Location: courses.php');
    exit;
}

$course = $course_result->fetch_assoc();

// Verify this course is in a reviewable state
$is_reviewable = in_array($course['approval_status'], ['submitted_for_review', 'under_review']);
if (!$is_reviewable) {
    header('Location: courses.php');
    exit;
}

// Update course status to "under_review" if it's just "submitted_for_review"
if ($course['approval_status'] === 'submitted_for_review') {
    $update_query = "UPDATE courses SET approval_status = 'under_review', updated_at = CURRENT_TIMESTAMP WHERE course_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $course_id);
    $update_stmt->execute();
    
    $course['approval_status'] = 'under_review';
    
    $log_query = "INSERT INTO course_review_history 
                  (course_id, reviewed_by, previous_status, new_status, review_date) 
                  VALUES (?, ?, 'submitted_for_review', 'under_review', CURRENT_TIMESTAMP)";
    $log_stmt = $conn->prepare($log_query);
    $log_stmt->bind_param("ii", $course_id, $_SESSION['user_id']);
    $log_stmt->execute();
}

// Updated Course Structure Query
// Updated Course Structure Query
$course_structure_query = "
WITH course_structure AS (
    SELECT 
        s.section_id, 
        s.title AS section_title, 
        s.position AS section_position,
        t.topic_id, 
        t.title AS topic_title, 
        t.position AS topic_position,
        sq.quiz_id,
        sq.quiz_title,
        sq.description,
        sq.time_limit,
        sq.pass_mark,
        sq.randomize_questions,
        sq.is_required,
        sq.attempts_allowed,
        sq.show_correct_answers,
        sq.shuffle_answers,
        sq.instruction,
        sq.reset_cooldown_minutes,
        qq.question_id,
        qq.question_text,
        qq.question_type,
        qq.points AS question_points,
        qq.difficulty,
        qq.explanation AS question_explanation,
        qq.image_path AS question_image,
        qa.answer_id,
        qa.answer_text,
        qa.is_correct,
        qa.explanation AS answer_explanation,
        qa.is_partially_correct,
        qa.partial_points
    FROM 
        course_sections s
    LEFT JOIN 
        section_quizzes sq ON sq.section_id = s.section_id
    LEFT JOIN 
        section_topics t ON s.section_id = t.section_id AND sq.topic_id = t.topic_id
    LEFT JOIN 
        quiz_questions qq ON sq.quiz_id = qq.quiz_id AND qq.deleted_at IS NULL
    LEFT JOIN 
        quiz_answers qa ON qq.question_id = qa.question_id AND qa.deleted_at IS NULL
    WHERE 
        s.course_id = ? AND 
        s.deleted_at IS NULL
)
SELECT * FROM course_structure
ORDER BY 
    section_position, 
    topic_position, 
    quiz_id,
    question_id,
    answer_id";

$structure_stmt = $conn->prepare($course_structure_query);
$structure_stmt->bind_param("i", $course_id);
$structure_stmt->execute();
$structure_result = $structure_stmt->get_result();

// Organize course structure
$sections = [];
$current_section = null;
$current_topic = null;

while ($row = $structure_result->fetch_assoc()) {
    // New Section
    if (!$current_section || $current_section['section_id'] != $row['section_id']) {
        if ($current_section) {
            $sections[] = $current_section;
        }
        $current_section = [
            'section_id' => $row['section_id'],
            'title' => $row['section_title'],
            'position' => $row['section_position'],
            'topics' => []
        ];
        $current_topic = null;
    }

    // New Topic
    if (!$current_topic || $current_topic['topic_id'] != $row['topic_id']) {
        if ($current_topic) {
            $current_section['topics'][] = $current_topic;
        }
        $current_topic = [
            'topic_id' => $row['topic_id'],
            'title' => $row['topic_title'],
            'position' => $row['topic_position'],
            'contents' => [],
            'quizzes' => [],
            'assignments' => []
        ];
    }

    // Quizzes
    if ($row['quiz_id']) {
        $quiz = [
            'quiz_id' => $row['quiz_id'],
            'title' => $row['quiz_title'],
            'time_limit' => $row['time_limit'],
            'pass_mark' => $row['pass_mark'],
            'randomize_questions' => $row['randomize_questions'] ? true : false,
            'is_required' => $row['is_required'] ? true : false,
            'attempts_allowed' => $row['attempts_allowed'],
            'show_correct_answers' => $row['show_correct_answers'] ? true : false,
            'shuffle_answers' => $row['shuffle_answers'] ? true : false,
            'instruction' => $row['instruction'],
            'reset_cooldown_minutes' => $row['reset_cooldown_minutes'],
            'questions' => []
        ];

        // Check if quiz already exists in the topic
        $quiz_exists = false;
        foreach ($current_topic['quizzes'] as &$existing_quiz) {
            if ($existing_quiz['quiz_id'] == $row['quiz_id']) {
                $quiz_exists = true;
                $quiz = &$existing_quiz;
                break;
            }
        }
        unset($existing_quiz);

        // Questions and Answers
        if ($row['question_id']) {
            $question_exists = false;
            foreach ($quiz['questions'] as &$existing_question) {
                if ($existing_question['question_id'] == $row['question_id']) {
                    $question_exists = true;
                    if ($row['answer_id']) {
                        $existing_question['answers'][] = [
                            'answer_id' => $row['answer_id'],
                            'answer_text' => $row['answer_text'],
                            'is_correct' => $row['is_correct'] ? true : false,
                            'explanation' => $row['answer_explanation'],
                            'is_partially_correct' => $row['is_partially_correct'] ? true : false,
                            'partial_points' => $row['partial_points']
                        ];
                    }
                    break;
                }
            }
            unset($existing_question);

            if (!$question_exists) {
                $new_question = [
                    'question_id' => $row['question_id'],
                    'question_text' => $row['question_text'],
                    'question_type' => $row['question_type'],
                    'points' => $row['question_points'],
                    'difficulty' => $row['difficulty'],
                    'explanation' => $row['question_explanation'],
                    'image_path' => $row['question_image'],
                    'answers' => []
                ];

                if ($row['answer_id']) {
                    $new_question['answers'][] = [
                        'answer_id' => $row['answer_id'],
                        'answer_text' => $row['answer_text'],
                        'is_correct' => $row['is_correct'] ? true : false,
                        'explanation' => $row['answer_explanation'],
                        'is_partially_correct' => $row['is_partially_correct'] ? true : false,
                        'partial_points' => $row['partial_points']
                    ];
                }

                $quiz['questions'][] = $new_question;
            }
        }

        // Add quiz to topic if it doesn't exist
        if (!$quiz_exists) {
            $current_topic['quizzes'][] = $quiz;
        }
    }
}

// Close last sets
if ($current_topic) {
    $current_section['topics'][] = $current_topic;
}
if ($current_section) {
    $sections[] = $current_section;
}

// Fetch course instructors
$instructors_query = "SELECT 
                    u.user_id,
                    u.first_name,
                    u.last_name,
                    u.profile_pic,
                    i.bio,
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
$instructors = $inst_result->fetch_all(MYSQLI_ASSOC);

// Fetch course requirements and learning outcomes in a single query
$additional_details_query = "
    (SELECT 'requirement' as type, requirement_text as text FROM course_requirements WHERE course_id = ?)
    UNION
    (SELECT 'outcome' as type, outcome_text as text FROM course_learning_outcomes WHERE course_id = ?)
";
$details_stmt = $conn->prepare($additional_details_query);
$details_stmt->bind_param("ii", $course_id, $course_id);
$details_stmt->execute();
$details_result = $details_stmt->get_result();

$requirements = [];
$outcomes = [];

while ($detail = $details_result->fetch_assoc()) {
    if ($detail['type'] == 'requirement') {
        $requirements[] = $detail['text'];
    } else {
        $outcomes[] = $detail['text'];
    }
}
?>

<!-- Include jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main">
    <!-- Navbar -->
    <nav class="js-nav-scroller navbar navbar-expand-lg navbar-sidebar navbar-vertical navbar-light bg-white border-end" data-hs-nav-scroller-options='{
            "type": "vertical",
            "target": ".navbar-nav .active",
            "offset": 80
           }'>
        <?php include '../includes/department/sidebar.php'; ?>
    </nav>
    <!-- End Navbar -->

    <!-- Content -->
    <div class="navbar-sidebar-aside-content content-space-1 content-space-md-2 px-lg-5 px-xl-10">
        <!-- Approval Bar -->
        <div class="review-top-bar card border-top-primary border-width-5 shadow-sm sticky-top bg-white p-3 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="badge bg-primary-soft p-2 me-3">
                        <i class="bi-eye-fill fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Reviewing: <?php echo htmlspecialchars($course['title']); ?></h5>
                        <p class="text-muted mb-0 small">
                            <span class="badge bg-info me-2">Under Review</span>
                            Submitted by: <?php echo $instructors[0]['first_name'] . ' ' . $instructors[0]['last_name']; ?>
                        </p>
                    </div>
                </div>
                <div class="review-actions d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-soft-success" 
                            data-action="approve" 
                            data-course-id="<?php echo $course['course_id']; ?>">
                        <i class="bi-check-circle me-1"></i> Approve
                    </button>
                    <button type="button" class="btn btn-sm btn-soft-warning" 
                            data-action="request_revisions" 
                            data-course-id="<?php echo $course['course_id']; ?>">
                        <i class="bi-arrow-counterclockwise me-1"></i> Request Revisions
                    </button>
                    <button type="button" class="btn btn-sm btn-soft-danger" 
                            data-action="reject" 
                            data-course-id="<?php echo $course['course_id']; ?>">
                        <i class="bi-x-circle me-1"></i> Reject
                    </button>
                    <a href="courses.php" class="btn btn-sm btn-outline-secondary">
                        <i class="bi-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <!-- Course Preview -->
        <div class="card mb-4">
            <div class="card-header bg-dark p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="text-white mb-2"><?php echo htmlspecialchars($course['title']); ?></h2>
                        <p class="text-white-50 mb-2"><?php echo htmlspecialchars($course['short_description']); ?></p>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="badge bg-primary"><?php echo htmlspecialchars($course['course_level']); ?></span>
                            <span class="badge bg-info"><?php echo htmlspecialchars($course['category_name']); ?></span>
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($course['subcategory_name']); ?></span>
                        </div>
                    </div>
                    <div class="col-md-4 mt-3 mt-md-0 text-md-end">
                        <div class="d-flex justify-content-md-end">
                            <?php foreach ($instructors as $idx => $instructor): ?>
                                <?php if ($idx < 3): ?>
                                    <div class="avatar avatar-circle" 
                                         data-bs-toggle="tooltip" 
                                         data-bs-placement="top" 
                                         title="<?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?>">
                                        <?php if (!empty($instructor['profile_pic']) && $instructor['profile_pic'] !== 'default.png'): ?>
                                            <img class="avatar-img" 
                                                 src="../assets/img/profiles/<?php echo htmlspecialchars($instructor['profile_pic']); ?>" 
                                                 alt="<?php echo htmlspecialchars($instructor['first_name']); ?>">
                                        <?php else: ?>
                                            <span class="avatar-initials bg-primary text-white">
                                                <?php echo substr($instructor['first_name'], 0, 1) . substr($instructor['last_name'], 0, 1); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($instructor['is_primary']): ?>
                                            <span class="avatar-status avatar-sm-status avatar-status-success"></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <?php if (count($instructors) > 3): ?>
                                <div class="avatar avatar-circle avatar-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo count($instructors) - 3; ?> more instructors">
                                    <span class="avatar-initials bg-secondary text-white">+<?php echo count($instructors) - 3; ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Course Preview Navigation -->
            <div class="course-nav">
                <ul class="nav nav-tabs nav-justified" id="courseTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">
                            <i class="bi-info-circle me-1"></i> Overview
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="curriculum-tab" data-bs-toggle="tab" data-bs-target="#curriculum" type="button" role="tab" aria-controls="curriculum" aria-selected="false">
                            <i class="bi-journal-text me-1"></i> Curriculum
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="instructors-tab" data-bs-toggle="tab" data-bs-target="#instructors" type="button" role="tab" aria-controls="instructors" aria-selected="false">
                            <i class="bi-person-badge me-1"></i> Instructors
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button" role="tab" aria-controls="settings" aria-selected="false">
                            <i class="bi-gear me-1"></i> Course Settings
                        </button>
                    </li>
                </ul>
            </div>
            
            <div class="card-body p-0">
                <div class="tab-content" id="courseTabContent">
                    <!-- Overview Tab -->
                    <div class="tab-pane fade show active p-4" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="course-description mb-4">
                                    <h3 class="h5 mb-3">About This Course</h3>
                                    <div class="bg-soft-primary rounded p-4">
                                        <?php echo nl2br(htmlspecialchars($course['full_description'] ?? 'No description available.')); ?>
                                    </div>
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="card h-100 border-0 shadow-sm">
                                            <div class="card-header bg-success-soft">
                                                <h5 class="h6 mb-0 text-success">
                                                    <i class="bi-check-circle me-1"></i> What You'll Learn
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <?php if (empty($outcomes)): ?>
                                                    <p class="text-muted">No learning outcomes listed for this course.</p>
                                                <?php else: ?>
                                                    <ul class="list-checked">
                                                        <?php foreach ($outcomes as $outcome): ?>
                                                            <li><?php echo htmlspecialchars($outcome); ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="card h-100 border-0 shadow-sm">
                                            <div class="card-header bg-warning-soft">
                                                <h5 class="h6 mb-0 text-warning">
                                                    <i class="bi-exclamation-triangle me-1"></i> Requirements
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <?php if (empty($requirements)): ?>
                                                    <p class="text-muted">No specific requirements listed for this course.</p>
                                                <?php else: ?>
                                                    <ul class="list-checked list-checked-warning">
                                                        <?php foreach ($requirements as $requirement): ?>
                                                            <li><?php echo htmlspecialchars($requirement); ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-4">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-dark text-white">
                                        <h5 class="h6 mb-0">Course Details</h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item px-0 d-flex justify-content-between">
                                                <span>Level:</span>
                                                <span class="fw-semibold"><?php echo htmlspecialchars($course['course_level']); ?></span>
                                            </li>
                                            <li class="list-group-item px-0 d-flex justify-content-between">
                                                <span>Sections:</span>
                                                <span class="fw-semibold"><?php echo count($sections); ?></span>
                                            </li>
                                            <li class="list-group-item px-0 d-flex justify-content-between">
                                                <span>Topics:</span>
                                                <span class="fw-semibold"><?php echo countTotalTopics($sections); ?></span>
                                            </li>
                                            <li class="list-group-item px-0 d-flex justify-content-between">
                                                <span>Content Items:</span>
                                                <span class="fw-semibold"><?php echo countTotalContentItems($sections); ?></span>
                                            </li>
                                            <li class="list-group-item px-0 d-flex justify-content-between">
                                                <span>Quizzes:</span>
                                                <span class="fw-semibold"><?php echo countTotalQuizzes($sections); ?> (<?php echo countTotalQuizQuestions($sections); ?> questions)</span>
                                            </li>
                                            <li class="list-group-item px-0 d-flex justify-content-between">
                                                <span>Assignments:</span>
                                                <span class="fw-semibold"><?php echo countTotalAssignments($sections); ?></span>
                                            </li>
                                            <li class="list-group-item px-0 d-flex justify-content-between">
                                                <span>Certificate:</span>
                                                <span class="fw-semibold"><?php echo $course['certificate_enabled'] ? 'Available' : 'Not Available'; ?></span>
                                            </li>
                                            <li class="list-group-item px-0 d-flex justify-content-between">
                                                <span>Created:</span>
                                                <span class="fw-semibold"><?php echo date('M d, Y', strtotime($course['created_at'])); ?></span>
                                            </li>
                                            <li class="list-group-item px-0 d-flex justify-content-between">
                                                <span>Last Updated:</span>
                                                <span class="fw-semibold"><?php echo date('M d, Y', strtotime($course['updated_at'])); ?></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Curriculum Tab -->
                    <div class="tab-pane fade p-4" id="curriculum" role="tabpanel" aria-labelledby="curriculum-tab">
                        <!-- Sticky Section Navigation -->
                        <div class="sticky-top bg-white py-3 mb-4" style="top: 70px; z-index: 1000;">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Course Curriculum</h5>
                                <button class="btn btn-sm btn-soft-primary toggle-all-sections">
                                    <i class="bi-arrows-expand me-1"></i> Toggle All Sections
                                </button>
                            </div>
                            <div class="section-nav mt-2">
                                <ul class="nav nav-pills nav-fill">
                                    <?php foreach ($sections as $index => $section): ?>
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo $index === 0 ? 'active' : ''; ?>" href="#section-<?php echo $section['section_id']; ?>">
                                                Section <?php echo $index + 1; ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>

                        <?php if (empty($sections)): ?>
                            <div class="alert alert-info">
                                <i class="bi-info-circle me-2"></i>
                                This course doesn't have any content sections yet.
                            </div>
                        <?php else: ?>
                            <div class="curriculum-content">
                                <?php foreach ($sections as $sectionIndex => $section): ?>
                                    <div class="card mb-3 border-0 shadow-sm" id="section-<?php echo $section['section_id']; ?>">
                                        <div class="card-header bg-light">
                                            <h5 class="h6 mb-0 d-flex align-items-center">
                                                <span class="section-number bg-primary rounded-circle text-white d-flex align-items-center justify-content-center me-3" 
                                                      style="width: 28px; height: 28px;">
                                                    <?php echo $sectionIndex + 1; ?>
                                                </span>
                                                <?php echo htmlspecialchars($section['title']); ?>
                                                <span class="ms-auto small text-muted">
                                                    <?php echo count($section['topics']); ?> topics • <?php echo countSectionContentItems($section); ?> items
                                                </span>
                                            </h5>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="accordion" id="sectionAccordion-<?php echo $section['section_id']; ?>">
                                                <?php foreach ($section['topics'] as $topicIndex => $topic): ?>
                                                    <div class="accordion-item border-0">
                                                        <h2 class="accordion-header" id="topic-heading-<?php echo $topic['topic_id']; ?>">
                                                            <button class="accordion-button <?php echo $topicIndex !== 0 ? 'collapsed' : ''; ?>" 
                                                                    type="button" 
                                                                    data-bs-toggle="collapse" 
                                                                    data-bs-target="#topic-collapse-<?php echo $topic['topic_id']; ?>" 
                                                                    aria-expanded="<?php echo $topicIndex === 0 ? 'true' : 'false'; ?>" 
                                                                    aria-controls="topic-collapse-<?php echo $topic['topic_id']; ?>">
                                                                <div class="d-flex align-items-center w-100">
                                                                    <span class="topic-icon bg-soft-<?php echo getTopicIconColor($topic); ?> text-<?php echo getTopicIconColor($topic); ?> rounded-circle p-2 me-3">
                                                                        <i class="bi-<?php echo getTopicIcon($topic); ?>"></i>
                                                                    </span>
                                                                    <span class="fw-medium"><?php echo htmlspecialchars($topic['title']); ?></span>
                                                                </div>
                                                            </button>
                                                        </h2>
                                                        <div id="topic-collapse-<?php echo $topic['topic_id']; ?>" 
                                                             class="accordion-collapse collapse <?php echo $topicIndex === 0 ? 'show' : ''; ?>" 
                                                             aria-labelledby="topic-heading-<?php echo $topic['topic_id']; ?>" 
                                                             data-bs-parent="#sectionAccordion-<?php echo $section['section_id']; ?>">
                                                            <div class="accordion-body">
                                                                <?php if (!empty($topic['contents']) || !empty($topic['quizzes']) || !empty($topic['assignments'])): ?>
                                                                    <!-- Content Items -->
                                                                    <?php foreach ($topic['contents'] as $contentIndex => $content): ?>
                                                                        <div class="content-item card mb-2 border-0 shadow-sm">
                                                                            <div class="card-body">
                                                                                <div class="d-flex align-items-center">
                                                                                    <i class="bi-<?php echo getContentTypeIcon($content['content_type']); ?> text-<?php echo getContentTypeColor($content['content_type']); ?> me-3"></i>
                                                                                    <div>
                                                                                        <h6 class="mb-1"><?php echo htmlspecialchars($content['title'] ?? 'Untitled Content'); ?></h6>
                                                                                        <p class="text-muted small mb-0"><?php echo ucfirst($content['content_type']); ?> Content</p>
                                                                                    </div>
                                                                                </div>
                                                                                <?php if ($content['content_type'] === 'video' && !empty($content['file_path'])): ?>
                                                                                    <video controls class="w-100 mt-2 rounded" style="max-height: 200px;">
                                                                                        <source src="../assets/uploads/<?php echo htmlspecialchars($content['file_path']); ?>" type="video/mp4">
                                                                                        Your browser does not support the video tag.
                                                                                    </video>
                                                                                <?php elseif ($content['content_type'] === 'text' && !empty($content['content_text'])): ?>
                                                                                    <div class="text-preview bg-light rounded p-2 mt-2">
                                                                                        <?php echo nl2br(substr(htmlspecialchars($content['content_text']), 0, 150)) . (strlen($content['content_text']) > 150 ? '...' : ''); ?>
                                                                                    </div>
                                                                                <?php elseif ($content['content_type'] === 'document'): ?>
                                                                                    <div class="document-preview bg-light rounded p-2 mt-2 d-flex align-items-center">
                                                                                        <i class="bi-file-earmark-text fs-4 text-primary me-2"></i>
                                                                                        <span><?php echo !empty($content['file_path']) ? htmlspecialchars(basename($content['file_path'])) : 'Document unavailable'; ?></span>
                                                                                    </div>
                                                                                <?php elseif ($content['content_type'] === 'link'): ?>
                                                                                    <div class="link-preview bg-light rounded p-2 mt-2 d-flex align-items-center">
                                                                                        <i class="bi-link-45deg fs-4 text-primary me-2"></i>
                                                                                        <span><?php echo !empty($content['external_url']) ? htmlspecialchars($content['external_url']) : 'Link unavailable'; ?></span>
                                                                                    </div>
                                                                                <?php endif; ?>
                                                                                <?php if (!empty($content['description'])): ?>
                                                                                    <div class="small text-muted mt-2"><?php echo substr(htmlspecialchars($content['description']), 0, 100) . (strlen($content['description']) > 100 ? '...' : ''); ?></div>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                    
                                                                    <!-- Quizzes -->
                                                                    <?php foreach ($topic['quizzes'] as $quiz): ?>
                                                                        <div class="quiz-item card mb-2 border-0 shadow-sm">
                                                                            <div class="card-body">
                                                                                <div class="d-flex align-items-center mb-2">
                                                                                    <i class="bi-question-diamond-fill text-warning me-3"></i>
                                                                                    <h6 class="mb-0"><?php echo htmlspecialchars($quiz['quiz_title']); ?></h6>
                                                                                </div>
                                                                                <div class="d-flex flex-wrap gap-2 mb-2">
                                                                                    <span class="badge bg-soft-warning text-warning">
                                                                                        <i class="bi-question-circle me-1"></i> <?php echo count($quiz['questions']); ?> Questions
                                                                                    </span>
                                                                                    <?php if ($quiz['time_limit']): ?>
                                                                                        <span class="badge bg-soft-primary text-primary">
                                                                                            <i class="bi-clock me-1"></i> <?php echo $quiz['time_limit']; ?> min
                                                                                        </span>
                                                                                    <?php endif; ?>
                                                                                    <?php if ($quiz['pass_mark']): ?>
                                                                                        <span class="badge bg-soft-success text-success">
                                                                                            <i class="bi-check2-circle me-1"></i> <?php echo $quiz['pass_mark']; ?>% to pass
                                                                                        </span>
                                                                                    <?php endif; ?>
                                                                                </div>
                                                                                <button class="btn btn-sm btn-soft-primary mt-2 preview-quiz-btn" 
                                                                                        data-quiz-id="<?php echo $quiz['quiz_id']; ?>" 
                                                                                        data-bs-toggle="modal" 
                                                                                        data-bs-target="#quizPreviewModal">
                                                                                    <i class="bi-eye me-1"></i> Preview Quiz
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                    
                                                                    <!-- Assignments -->
                                                                    <?php foreach ($topic['assignments'] as $assignment): ?>
                                                                        <div class="assignment-item card mb-2 border-0 shadow-sm">
                                                                            <div class="card-body">
                                                                                <div class="d-flex align-items-center mb-2">
                                                                                    <i class="bi-file-text-fill text-info me-3"></i>
                                                                                    <h6 class="mb-0"><?php echo htmlspecialchars($assignment['title']); ?></h6>
                                                                                </div>
                                                                                <div class="d-flex flex-wrap gap-2 mb-2">
                                                                                    <span class="badge bg-soft-info text-info">
                                                                                        <i class="bi-journal-text me-1"></i> Assignment
                                                                                    </span>
                                                                                    <span class="badge bg-soft-primary text-primary">
                                                                                        <i class="bi-award me-1"></i> <?php echo $assignment['max_points']; ?> points
                                                                                    </span>
                                                                                    <?php if ($assignment['due_days']): ?>
                                                                                        <span class="badge bg-soft-danger text-danger">
                                                                                            <i class="bi-alarm me-1"></i> Due in <?php echo $assignment['due_days']; ?> days
                                                                                        </span>
                                                                                    <?php endif; ?>
                                                                                </div>
                                                                                <button class="btn btn-sm btn-soft-info mt-2" 
                                                                                        data-bs-toggle="modal" 
                                                                                        data-bs-target="#assignmentPreviewModal-<?php echo $assignment['assignment_id']; ?>">
                                                                                    <i class="bi-eye me-1"></i> Preview Assignment
                                                                                </button>
                                                                                
                                                                                <!-- Assignment Preview Modal -->
                                                                                <div class="modal fade" id="assignmentPreviewModal-<?php echo $assignment['assignment_id']; ?>" tabindex="-1" aria-hidden="true">
                                                                                    <div class="modal-dialog modal-lg">
                                                                                        <div class="modal-content">
                                                                                            <div class="modal-header">
                                                                                                <h5 class="modal-title">Assignment: <?php echo htmlspecialchars($assignment['title']); ?></h5>
                                                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                            </div>
                                                                                            <div class="modal-body">
                                                                                                <div class="row mb-4">
                                                                                                    <div class="col-md-6">
                                                                                                        <div class="d-flex align-items-center mb-3">
                                                                                                            <i class="bi-award fs-4 text-primary me-2"></i>
                                                                                                            <div>
                                                                                                                <div class="fw-semibold"><?php echo $assignment['max_points']; ?> points</div>
                                                                                                                <div class="small text-muted">Maximum Score</div>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="col-md-6">
                                                                                                        <div class="d-flex align-items-center mb-3">
                                                                                                            <i class="bi-alarm fs-4 text-primary me-2"></i>
                                                                                                            <div>
                                                                                                                <div class="fw-semibold">
                                                                                                                    <?php echo $assignment['due_days'] ? 'Due in ' . $assignment['due_days'] . ' days after enrollment' : 'No due date'; ?>
                                                                                                                </div>
                                                                                                                <div class="small text-muted">Deadline</div>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div class="assignment-details mb-4">
                                                                                                    <h6>Assignment Description</h6>
                                                                                                    <div class="p-3 bg-light rounded">
                                                                                                        <?php if (!empty($assignment['description'])): ?>
                                                                                                            <?php echo nl2br(htmlspecialchars($assignment['description'])); ?>
                                                                                                        <?php else: ?>
                                                                                                            <p class="text-muted">No description provided.</p>
                                                                                                        <?php endif; ?>
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div class="submission-requirements mb-4">
                                                                                                    <h6>Submission Requirements</h6>
                                                                                                    <div class="d-flex mb-2">
                                                                                                        <div class="me-3 text-primary">
                                                                                                            <i class="bi-upload"></i>
                                                                                                        </div>
                                                                                                        <div>
                                                                                                            <span class="fw-medium">Submission Type:</span>
                                                                                                            <span class="ms-2"><?php echo $assignment['submission_type']; ?></span>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <?php if ($assignment['submission_type'] === 'File' || $assignment['submission_type'] === 'Multiple Files'): ?>
                                                                                                        <div class="d-flex mb-2">
                                                                                                            <div class="me-3 text-primary">
                                                                                                                <i class="bi-file-earmark"></i>
                                                                                                            </div>
                                                                                                            <div>
                                                                                                                <span class="fw-medium">Allowed File Types:</span>
                                                                                                                <span class="ms-2">
                                                                                                                    <?php echo !empty($assignment['file_types_allowed']) ? htmlspecialchars($assignment['file_types_allowed']) : 'All file types'; ?>
                                                                                                                </span>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                        <div class="d-flex">
                                                                                                            <div class="me-3 text-primary">
                                                                                                                <i class="bi-hdd"></i>
                                                                                                            </div>
                                                                                                            <div>
                                                                                                                <span class="fw-medium">Max File Size:</span>
                                                                                                                <span class="ms-2"><?php echo $assignment['max_file_size_mb']; ?> MB</span>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    <?php endif; ?>
                                                                                                </div>
                                                                                                <div class="assignment-submission-area bg-soft-primary p-3 rounded">
                                                                                                    <h6 class="text-primary">Student Submission Area</h6>
                                                                                                    <?php if ($assignment['submission_type'] === 'Text'): ?>
                                                                                                        <div class="form-group">
                                                                                                            <textarea class="form-control" rows="4" placeholder="Students will type their answers here" disabled></textarea>
                                                                                                        </div>
                                                                                                    <?php elseif ($assignment['submission_type'] === 'File' || $assignment['submission_type'] === 'Multiple Files'): ?>
                                                                                                        <div class="file-upload-placeholder p-4 bg-white rounded border border-dashed d-flex flex-column align-items-center justify-content-center">
                                                                                                            <i class="bi-cloud-arrow-up fs-3 text-muted mb-2"></i>
                                                                                                            <p class="text-muted mb-0">Students will upload their files here</p>
                                                                                                        </div>
                                                                                                    <?php elseif ($assignment['submission_type'] === 'Link'): ?>
                                                                                                        <div class="form-group">
                                                                                                            <input type="text" class="form-control" placeholder="Students will paste a URL here" disabled>
                                                                                                        </div>
                                                                                                    <?php endif; ?>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="modal-footer">
                                                                                                <button type="button" class="btn btn-soft-secondary" data-bs-dismiss="modal">Close</button>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                    <?php if (empty($topic['contents']) && empty($topic['quizzes']) && empty($topic['assignments'])): ?>
                                                                        <div class="text-muted">No content available for this topic.</div>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Instructors Tab -->
                    <div class="tab-pane fade p-4" id="instructors" role="tabpanel" aria-labelledby="instructors-tab">
                        <div class="row">
                            <?php foreach ($instructors as $instructor): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card instructor-card h-100 border-0 shadow-sm">
                                        <div class="card-body">
                                            <div class="text-center mb-3">
                                                <?php if (!empty($instructor['profile_pic']) && $instructor['profile_pic'] !== 'default.png'): ?>
                                                    <img src="../assets/img/profiles/<?php echo htmlspecialchars($instructor['profile_pic']); ?>" 
                                                         class="avatar avatar-lg avatar-circle mb-3" 
                                                         alt="<?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?>">
                                                <?php else: ?>
                                                    <div class="avatar avatar-lg avatar-circle avatar-soft-primary mb-3">
                                                        <span class="avatar-initials">
                                                            <?php echo substr($instructor['first_name'], 0, 1) . substr($instructor['last_name'], 0, 1); ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                                <h5 class="card-title mb-1">
                                                    <?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?>
                                                    <?php if ($instructor['is_primary']): ?>
                                                        <span class="badge bg-primary ms-1">Primary</span>
                                                    <?php endif; ?>
                                                </h5>
                                                <p class="text-muted small">Instructor</p>
                                            </div>
                                            <?php if (!empty($instructor['bio'])): ?>
                                                <div class="instructor-bio">
                                                    <p class="small"><?php echo nl2br(htmlspecialchars($instructor['bio'])); ?></p>
                                                </div>
                                            <?php else: ?>
                                                <p class="text-muted text-center small">No biography available.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Settings Tab -->
                    <div class="tab-pane fade p-4" id="settings" role="tabpanel" aria-labelledby="settings-tab">
                        <div class="row">
                            <div class="col-lg-6 mb-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-dark text-white">
                                        <h5 class="h6 mb-0">Course Settings</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <h6>Course Access</h6>
                                            <div class="d-flex mb-2">
                                                <div class="me-3 text-primary">
                                                    <i class="bi-eye"></i>
                                                </div>
                                                <div>
                                                    <span class="fw-medium">Visibility:</span>
                                                    <span class="ms-2"><?php echo $course['access_level']; ?></span>
                                                </div>
                                            </div>
                                            <div class="d-flex mb-2">
                                                <div class="me-3 text-primary">
                                                    <i class="bi-currency-dollar"></i>
                                                </div>
                                                <div>
                                                    <span class="fw-medium">Price:</span>
                                                    <span class="ms-2">
                                                        <?php echo $course['price'] > 0 ? number_format($course['price'], 2) : 'Free'; ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="d-flex">
                                                <div class="me-3 text-primary">
                                                    <i class="bi-award"></i>
                                                </div>
                                                <div>
                                                    <span class="fw-medium">Certificate:</span>
                                                    <span class="ms-2"><?php echo $course['certificate_enabled'] ? 'Enabled' : 'Disabled'; ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div>
                                            <h6>Technical Information</h6>
                                            <div class="d-flex mb-2">
                                                <div class="me-3 text-primary">
                                                    <i class="bi-hash"></i>
                                                </div>
                                                <div>
                                                    <span class="fw-medium">Course ID:</span>
                                                    <span class="ms-2"><?php echo $course['course_id']; ?></span>
                                                </div>
                                            </div>
                                            <div class="d-flex mb-2">
                                                <div class="me-3 text-primary">
                                                    <i class="bi-calendar3"></i>
                                                </div>
                                                <div>
                                                    <span class="fw-medium">Created:</span>
                                                    <span class="ms-2"><?php echo date('F j, Y', strtotime($course['created_at'])); ?></span>
                                                </div>
                                            </div>
                                            <div class="d-flex">
                                                <div class="me-3 text-primary">
                                                    <i class="bi-calendar3-week"></i>
                                                </div>
                                                <div>
                                                    <span class="fw-medium">Last Updated:</span>
                                                    <span class="ms-2"><?php echo date('F j, Y', strtotime($course['updated_at'])); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 mb-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-dark text-white">
                                        <h5 class="h6 mb-0">Review History</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php
                                        $history_query = "SELECT crh.*, u.first_name, u.last_name
                                                         FROM course_review_history crh
                                                         JOIN users u ON crh.reviewed_by = u.user_id
                                                         WHERE crh.course_id = ?
                                                         ORDER BY crh.review_date DESC";
                                        $history_stmt = $conn->prepare($history_query);
                                        $history_stmt->bind_param("i", $course_id);
                                        $history_stmt->execute();
                                        $history_result = $history_stmt->get_result();
                                        if ($history_result->num_rows === 0):
                                        ?>
                                            <div class="text-center py-3">
                                                <div class="avatar avatar-lg avatar-soft-secondary avatar-circle mb-3">
                                                    <i class="bi-clock-history fs-2"></i>
                                                </div>
                                                <p class="text-muted mb-0">No review history available yet.</p>
                                            </div>
                                        <?php else: ?>
                                            <div class="timeline">
                                                <?php while ($history = $history_result->fetch_assoc()): ?>
                                                    <div class="timeline-item mb-3">
                                                        <div class="d-flex">
                                                            <div class="timeline-icon me-3">
                                                                <div class="avatar avatar-xs avatar-circle bg-soft-<?php echo getStatusColor($history['new_status']); ?> text-<?php echo getStatusColor($history['new_status']); ?>">
                                                                    <i class="bi-<?php echo getStatusIcon($history['new_status']); ?> small"></i>
                                                                </div>
                                                            </div>
                                                            <div class="timeline-body">
                                                                <div class="d-flex justify-content-between">
                                                                    <h6 class="mb-1 small"><?php echo ucfirst(str_replace('_', ' ', $history['new_status'])); ?></h6>
                                                                    <small class="text-muted"><?php echo date('M j, Y', strtotime($history['review_date'])); ?></small>
                                                                </div>
                                                                <p class="small text-muted mb-0">
                                                                    <?php echo htmlspecialchars($history['first_name'] . ' ' . $history['last_name']); ?> 
                                                                    changed status from 
                                                                    <span class="fw-medium"><?php echo ucfirst(str_replace('_', ' ', $history['previous_status'])); ?></span>
                                                                    to 
                                                                    <span class="fw-medium"><?php echo ucfirst(str_replace('_', ' ', $history['new_status'])); ?></span>
                                                                </p>
                                                                <?php if (!empty($history['comments'])): ?>
                                                                    <div class="bg-light p-2 rounded mt-2 small">
                                                                        <?php echo nl2br(htmlspecialchars($history['comments'])); ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endwhile; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bottom Action Panel -->
            <div class="card-footer bg-dark sticky-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="courses.php" class="btn btn-soft-secondary">
                        <i class="bi-arrow-left me-1"></i> Back to Courses
                    </a>
                    <div class="review-actions d-flex gap-2">
                        <button type="button" class="btn btn-soft-success" 
                                data-action="approve" 
                                data-course-id="<?php echo $course['course_id']; ?>">
                            <i class="bi-check-circle me-1"></i> Approve Course
                        </button>
                        <button type="button" class="btn btn-soft-warning" 
                                data-action="request_revisions" 
                                data-course-id="<?php echo $course['course_id']; ?>">
                            <i class="bi-arrow-counterclockwise me-1"></i> Request Revisions
                        </button>
                        <button type="button" class="btn btn-soft-danger" 
                                data-action="reject" 
                                data-course-id="<?php echo $course['course_id']; ?>">
                            <i class="bi-x-circle me-1"></i> Reject
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Content -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- Modals for approval actions -->
<?php renderRevisionRequestModal(); ?>
<?php renderRejectCourseModal(); ?>
<?php renderConfirmationModal(); ?>

<!-- Quiz Preview Modal -->
<div class="modal fade" id="quizPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quiz Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="quizPreviewContent">
                <!-- Quiz preview content will be loaded here via AJAX -->
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-soft-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add custom styles -->
<style>
    /* General Styles */
    .card {
        border-radius: 0.75rem;
        overflow: hidden;
    }
    
    /* Avatar */
    .avatar-circle {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    
    .avatar-lg {
        width: 5rem;
        height: 5rem;
    }
    
    .avatar-xs {
        width: 1.5rem;
        height: 1.5rem;
    }
    
    .avatar-initials {
        font-weight: 600;
    }
    
    .avatar-status {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 1rem;
        height: 1rem;
        border: 2px solid #fff;
        border-radius: 50%;
    }
    
    .avatar-status-success {
        background-color: #28a745;
    }
    
    /* Review Top Bar */
    .review-top-bar {
        z-index: 1020;
    }
    
    /* Course Nav Tabs */
    .course-nav .nav-link {
        padding: 1rem 1.5rem;
        color: #677788;
        font-weight: 500;
    }
    
    .course-nav .nav-link.active {
        color: #377dff;
        border-bottom: 2px solid #377dff;
    }
    
    /* Soft Backgrounds */
    .bg-primary-soft {
        background-color: rgba(55, 125, 255, 0.1) !important;
    }
    
    .bg-success-soft {
        background-color: rgba(0, 201, 167, 0.1) !important;
    }
    
    .bg-warning-soft {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }
    
    .bg-danger-soft {
        background-color: rgba(220, 53, 69, 0.1) !important;
    }
    
    .bg-info-soft {
        background-color: rgba(23, 162, 184, 0.1) !important;
    }
    
    .bg-dark-soft {
        background-color: rgba(33, 37, 41, 0.1) !important;
    }
    
    .bg-secondary-soft {
        background-color: rgba(108, 117, 125, 0.1) !important;
    }
    
    /* Soft Buttons */
    .btn-soft-primary {
        background-color: rgba(55, 125, 255, 0.1);
        color: #377dff;
        border-color: transparent;
    }
    .btn-soft-primary:hover {
        background-color: #377dff;
        color: #fff;
    }
    
    .btn-soft-success {
        background-color: rgba(0, 201, 167, 0.1);
        color: #00c9a7;
        border-color: transparent;
    }
    .btn-soft-success:hover {
        background-color: #00c9a7;
        color: #fff;
    }
    
    .btn-soft-warning {
        background-color: rgba(255, 193, 7, 0.1);
        color: #ffc107;
        border-color: transparent;
    }
    .btn-soft-warning:hover {
        background-color: #ffc107;
        color: #fff;
    }
    
    .btn-soft-danger {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
        border-color: transparent;
    }
    .btn-soft-danger:hover {
        background-color: #dc3545;
        color: #fff;
    }
    
    .btn-soft-secondary {
        background-color: rgba(108, 117, 125, 0.1);
        color: #6c757d;
        border-color: transparent;
    }
    .btn-soft-secondary:hover {
        background-color: #6c757d;
        color: #fff;
    }
    
    /* Topic Preview */
    .topic-icon {
        width: 1.75rem;
        height: 1.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* List Checked */
    .list-checked {
        list-style: none;
        padding-left: 0;
    }
    
    .list-checked li {
        position: relative;
        padding-left: 1.75rem;
        margin-bottom: 0.5rem;
    }
    
    .list-checked li::before {
        content: "\F26A";
        font-family: "bootstrap-icons";
        position: absolute;
        left: 0;
        color: #28a745;
    }
    
    .list-checked-warning li::before {
        color: #ffc107;
    }
    
    /* Timeline */
    .timeline-item:not(:last-child) {
        position: relative;
    }
    
    .timeline-item:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 24px;
        bottom: -12px;
        left: 12px;
        width: 1px;
        background: #e9ecef;
    }
    
    /* Border */
    .border-dashed {
        border-style: dashed !important;
    }
    
    /* Sticky Elements */
    .sticky-bottom {
        position: sticky;
        bottom: 0;
        z-index: 1020;
    }
    
    .section-nav .nav-link {
        font-size: 0.9rem;
        padding: 0.5rem;
        border-radius: 0.5rem;
    }
    
    .section-nav .nav-link.active {
        background-color: #377dff;
        color: #fff;
    }
    
    /* Quiz Preview Styles */
    .quiz-preview-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .question-card {
        transition: all 0.3s ease;
    }

    .question-card.border-success {
        border-left: 4px solid #198754 !important;
    }

    .question-card.border-danger {
        border-left: 4px solid #dc3545 !important;
    }

    .question-card.border-warning {
        border-left: 4px solid #ffc107 !important;
    }

    .answer-option {
        border-radius: 0.375rem;
        padding: 0.75rem;
        transition: all 0.2s ease;
    }

    .answer-option:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }

    .form-check-input:checked + .form-check-label {
        font-weight: 500;
    }

    .selected-correct {
        background-color: rgba(25, 135, 84, 0.1);
        border-left: 3px solid #198754;
    }

    .selected-incorrect {
        background-color: rgba(220, 53, 69, 0.1);
        border-left: 3px solid #dc3545;
    }

    .correct-answer {
        background-color: rgba(25, 135, 84, 0.05);
        border-left: 3px solid #198754;
    }

    .bg-primary-soft {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }

    .progress {
        border-radius: 1rem;
        overflow: hidden;
    }

    .progress-bar {
        background-color: #0d6efd;
        transition: width 0.3s ease;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .section-nav .nav-link {
            font-size: 0.8rem;
            padding: 0.4rem;
        }
        .review-top-bar .btn {
            font-size: 0.8rem;
        }
        .card-body {
            padding: 1rem;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize bootstrap components
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Toggle all sections
    document.querySelector('.toggle-all-sections').addEventListener('click', function() {
        const isExpanded = this.innerHTML.includes('Expand');
        const accordions = document.querySelectorAll('.accordion-collapse');
        accordions.forEach(accordion => {
            if (isExpanded) {
                accordion.classList.add('show');
            } else {
                accordion.classList.remove('show');
            }
        });
        this.innerHTML = isExpanded ? 
            '<i class="bi-arrows-collapse me-1"></i> Collapse All Sections' : 
            '<i class="bi-arrows-expand me-1"></i> Expand All Sections';
    });
    
    // Smooth scroll for section navigation
    document.querySelectorAll('.section-nav .nav-link').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            document.querySelector(targetId).scrollIntoView({ behavior: 'smooth' });
            // Update active state
            document.querySelectorAll('.section-nav .nav-link').forEach(link => link.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Handle approval actions
    document.addEventListener('click', function(e) {
        const actionElement = e.target.closest('[data-action]');
        if (!actionElement) return;
        
        e.preventDefault();
        const action = actionElement.dataset.action;
        const courseId = actionElement.dataset.courseId;
        
        switch (action) {
            case 'approve':
                confirmAction('Approve Course', 'Are you sure you want to approve this course? Once approved, it can be published by the instructors.', 
                    () => performCourseAction(courseId, 'approve'));
                break;
            case 'request_revisions':
                showRevisionRequestForm(courseId);
                break;
            case 'reject':
                showRejectCourseForm(courseId);
                break;
        }
    });
    
    // Handle quiz preview button click
    document.addEventListener('click', function(e) {
        const previewBtn = e.target.closest('.preview-quiz-btn');
        if (!previewBtn) return;

        const quizId = previewBtn.dataset.quizId;
        const modal = new bootstrap.Modal(document.getElementById('quizPreviewModal'));
        const previewContent = document.getElementById('quizPreviewContent');

        // Show loading spinner
        previewContent.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;

        // Fetch quiz preview content
        fetch(`../ajax/department/preview_quiz.php?quiz_id=${quizId}`, {
            method: 'GET',
            headers: {
                'Accept': 'text/html'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            previewContent.innerHTML = html;
            modal.show();

            // Initialize any required Bootstrap components within the loaded content
            const tooltipTriggerList = previewContent.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltipTriggerList.forEach(tooltipTriggerEl => {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
        })
        .catch(error => {
            previewContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi-exclamation-triangle me-2"></i>
                    Error loading quiz preview: ${error.message}
                </div>
            `;
            modal.show();
        });
    });
    
    function confirmAction(title, message, callback, type = 'primary') {
        const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
        document.getElementById('confirmationModalLabel').textContent = title;
        document.getElementById('confirmationMessage').textContent = message;
        
        const confirmBtn = document.getElementById('confirmActionBtn');
        confirmBtn.className = `btn btn-soft-${type}`;
        confirmBtn.textContent = 'Confirm';
        
        confirmBtn.onclick = function() {
            modal.hide();
            callback();
        };
        
        modal.show();
    }
    
    function performCourseAction(courseId, action, additionalData = {}) {
        showOverlay('Processing...');
        
        const formData = new FormData();
        formData.append('action', action);
        formData.append('course_id', courseId);
        
        Object.keys(additionalData).forEach(key => {
            formData.append(key, additionalData[key]);
        });
        
        fetch('../ajax/department/course_action_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            removeOverlay();
            if (data.success) {
                showToast(data.message, 'success');
                if (data.redirect) {
                    setTimeout(() => window.location.href = data.redirect, 1500);
                }
            } else {
                showToast(data.message, 'danger');
            }
        })
        .catch(error => {
            removeOverlay();
            showToast('Error performing action: ' + error.message, 'danger');
        });
    }
    
    function showRevisionRequestForm(courseId) {
        const modal = new bootstrap.Modal(document.getElementById('revisionRequestModal'));
        document.getElementById('revisionCourseId').value = courseId;
        document.getElementById('revisionComments').value = '';
        modal.show();
        
        document.getElementById('revisionRequestForm').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('../ajax/department/course_action_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modal.hide();
                    showToast(data.message, 'success');
                    if (data.redirect) {
                        setTimeout(() => window.location.href = data.redirect, 1500);
                    }
                } else {
                    showToast(data.message, 'danger');
                }
            })
            .catch(error => {
                showToast('Error sending revision request: ' + error.message, 'danger');
            });
        };
    }
    
    function showRejectCourseForm(courseId) {
        const modal = new bootstrap.Modal(document.getElementById('rejectCourseModal'));
        document.getElementById('rejectCourseId').value = courseId;
        document.getElementById('rejectComments').value = '';
        modal.show();
        
        document.getElementById('rejectCourseForm').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('../ajax/department/course_action_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modal.hide();
                    showToast(data.message, 'success');
                    if (data.redirect) {
                        setTimeout(() => window.location.href = data.redirect, 1500);
                    }
                } else {
                    showToast(data.message, 'danger');
                }
            })
            .catch(error => {
                showToast('Error rejecting course: ' + error.message, 'danger');
            });
        };
    }
    
    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '1050';
            document.body.appendChild(toastContainer);
        }
        
        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast, { delay: 5000 });
        bsToast.show();
        toast.addEventListener('hidden.bs.toast', () => toast.remove());
    }
    
    function showOverlay(message = null) {
        if (typeof window.showOverlay === 'function') {
            window.showOverlay(message);
            return;
        }
        const overlay = document.createElement('div');
        overlay.id = 'loadingOverlay';
        overlay.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center';
        overlay.style.backgroundColor = 'rgba(255, 255, 255, 0.8)';
        overlay.style.zIndex = '9999';
        let content = `
            <div class="d-flex align-items-center">
                <div class="spinner-border text-primary me-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
        `;
        if (message) content += `<div>${message}</div>`;
        content += '</div>';
        overlay.innerHTML = content;
        document.body.appendChild(overlay);
    }
    
    function removeOverlay() {
        if (typeof window.removeOverlay === 'function') {
            window.removeOverlay();
            return;
        }
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) overlay.remove();
    }
});
</script>
<?php
// Helper functions
function getTopicIcon($topic) {
    if (!empty($topic['quizzes'])) return 'question-diamond';
    elseif (!empty($topic['assignments'])) return 'file-earmark-text';
    elseif (!empty($topic['contents'])) {
        foreach ($topic['contents'] as $content) {
            if ($content['content_type'] === 'video') return 'camera-video';
        }
    }
    return 'file-earmark';
}

function getTopicIconColor($topic) {
    if (!empty($topic['quizzes'])) return 'warning';
    elseif (!empty($topic['assignments'])) return 'info';
    elseif (!empty($topic['contents'])) {
        foreach ($topic['contents'] as $content) {
            if ($content['content_type'] === 'video') return 'danger';
        }
    }
    return 'primary';
}

function getContentTypeIcon($type) {
    switch ($type) {
        case 'video': return 'camera-video-fill';
        case 'text': return 'file-text-fill';
        case 'document': return 'file-earmark-pdf-fill';
        case 'link': return 'link-45deg';
        default: return 'file-earmark';
    }
}

function getContentTypeColor($type) {
    switch ($type) {
        case 'video': return 'danger';
        case 'text': return 'dark';
        case 'document': return 'primary';
        case 'link': return 'info';
        default: return 'secondary';
    }
}

function getStatusIcon($status) {
    switch ($status) {
        case 'pending': return 'hourglass-split';
        case 'submitted_for_review': return 'arrow-right-circle';
        case 'under_review': return 'eye';
        case 'revisions_requested': return 'arrow-counterclockwise';
        case 'approved': return 'check-circle';
        case 'rejected': return 'x-circle';
        default: return 'circle';
    }
}

function getStatusColor($status) {
    switch ($status) {
        case 'pending': return 'warning';
        case 'submitted_for_review': return 'info';
        case 'under_review': return 'primary';
        case 'revisions_requested': return 'warning';
        case 'approved': return 'success';
        case 'rejected': return 'danger';
        default: return 'secondary';
    }
}

function countTotalTopics($sections) {
    $total = 0;
    foreach ($sections as $section) {
        $total += count($section['topics']);
    }
    return $total;
}

function countTotalContentItems($sections) {
    $total = 0;
    foreach ($sections as $section) {
        foreach ($section['topics'] as $topic) {
            $total += count($topic['contents']);
        }
    }
    return $total;
}

function countSectionContentItems($section) {
    $total = 0;
    foreach ($section['topics'] as $topic) {
        $total += count($topic['contents']);
        $total += count($topic['quizzes']);
        $total += count($topic['assignments']);
    }
    return $total;
}

function countTotalQuizzes($sections) {
    $total = 0;
    foreach ($sections as $section) {
        foreach ($section['topics'] as $topic) {
            $total += count($topic['quizzes']);
        }
    }
    return $total;
}

function countTotalQuizQuestions($sections) {
    $total = 0;
    foreach ($sections as $section) {
        foreach ($section['topics'] as $topic) {
            foreach ($topic['quizzes'] as $quiz) {
                $total += count($quiz['questions']);
            }
        }
    }
    return $total;
}

function countTotalAssignments($sections) {
    $total = 0;
    foreach ($sections as $section) {
        foreach ($section['topics'] as $topic) {
            $total += count($topic['assignments']);
        }
    }
    return $total;
}
?>

<?php include '../includes/department/footer.php'; ?>