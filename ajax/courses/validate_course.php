<?php
require_once '../../backend/config.php';
require_once '../../backend/session_start.php';

// Check if user is signed in and is an instructor
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get course_id from POST
$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;

// Validate course ownership
$stmt = $conn->prepare("SELECT course_id FROM courses WHERE course_id = ? AND instructor_id = ?");
$stmt->bind_param("ii", $course_id, $_SESSION['instructor_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Course not found or you do not have permission']);
    exit;
}

// Initialize validation response
$validation = [
    'passed' => true,
    'basicInfo' => ['passed' => true, 'issues' => []],
    'description' => ['passed' => true, 'issues' => []],
    'outcomes' => ['passed' => true, 'issues' => []],
    'curriculum' => ['passed' => true, 'issues' => []],
    'assessments' => ['passed' => true, 'issues' => []]
];

// Get course data for validation
$stmt = $conn->prepare("
    SELECT c.*, COUNT(DISTINCT cs.section_id) as section_count
    FROM courses c
    LEFT JOIN course_sections cs ON c.course_id = cs.course_id
    WHERE c.course_id = ?
    GROUP BY c.course_id
");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 1. Validate Basic Information
if (empty($course['title']) || strlen($course['title']) < 5) {
    $validation['basicInfo']['passed'] = false;
    $validation['passed'] = false;
    $validation['basicInfo']['issues'][] = [
        'message' => 'Course title is missing or too short (minimum 5 characters required)',
        'step' => 1
    ];
}

if (empty($course['short_description']) || strlen($course['short_description']) < 10) {
    $validation['basicInfo']['passed'] = false;
    $validation['passed'] = false;
    $validation['basicInfo']['issues'][] = [
        'message' => 'Course short description is missing or too short (minimum 10 characters required)',
        'step' => 1
    ];
}

if (empty($course['thumbnail'])) {
    $validation['basicInfo']['passed'] = false;
    $validation['passed'] = false;
    $validation['basicInfo']['issues'][] = [
        'message' => 'Course thumbnail image is missing',
        'step' => 1
    ];
}

// 2. Validate Course Description
if (empty($course['full_description']) || strlen($course['full_description']) < 50) {
    $validation['description']['passed'] = false;
    $validation['passed'] = false;
    $validation['description']['issues'][] = [
        'message' => 'Course full description is missing or too short (minimum 50 characters required)',
        'step' => 2
    ];
}

// 3. Validate Learning Outcomes & Requirements
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM course_learning_outcomes WHERE course_id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$outcomes_count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

if ($outcomes_count < 1) {
    $validation['outcomes']['passed'] = false;
    $validation['passed'] = false;
    $validation['outcomes']['issues'][] = [
        'message' => 'Course must have at least one learning outcome',
        'step' => 3
    ];
}

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM course_requirements WHERE course_id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$requirements_count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

if ($requirements_count < 1) {
    $validation['outcomes']['passed'] = false;
    $validation['passed'] = false;
    $validation['outcomes']['issues'][] = [
        'message' => 'Course must have at least one requirement',
        'step' => 3
    ];
}

// 4. Validate Curriculum Structure
if ($course['section_count'] < 1) {
    $validation['curriculum']['passed'] = false;
    $validation['passed'] = false;
    $validation['curriculum']['issues'][] = [
        'message' => 'Course must have at least one section',
        'step' => 6
    ];
}

// Check for empty sections (sections without topics or quizzes)
if ($course['section_count'] > 0) {
    $stmt = $conn->prepare("
        SELECT cs.section_id, cs.title, 
               COUNT(DISTINCT st.topic_id) as topic_count, 
               COUNT(DISTINCT sq.quiz_id) as quiz_count
        FROM course_sections cs
        LEFT JOIN section_topics st ON cs.section_id = st.section_id
        LEFT JOIN section_quizzes sq ON cs.section_id = sq.section_id
        WHERE cs.course_id = ?
        GROUP BY cs.section_id
    ");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $sections = $stmt->get_result();
    $stmt->close();

    while ($section = $sections->fetch_assoc()) {
        if ($section['topic_count'] == 0 && $section['quiz_count'] == 0) {
            $validation['curriculum']['passed'] = false;
            $validation['passed'] = false;
            $validation['curriculum']['issues'][] = [
                'message' => 'Section "' . htmlspecialchars($section['title']) . '" has no content. Add at least one topic or quiz.',
                'step' => 6
            ];
        }
    }
}

// 5. Validate Topics have content
$stmt = $conn->prepare("
    SELECT st.topic_id, st.title, cs.title as section_title, 
           COUNT(tc.content_id) as content_count
    FROM section_topics st
    JOIN course_sections cs ON st.section_id = cs.section_id
    LEFT JOIN topic_content tc ON st.topic_id = tc.topic_id
    WHERE cs.course_id = ?
    GROUP BY st.topic_id
");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$topics = $stmt->get_result();
$stmt->close();

$empty_topics = [];
while ($topic = $topics->fetch_assoc()) {
    if ($topic['content_count'] == 0) {
        $empty_topics[] = [
            'topic_id' => $topic['topic_id'],
            'title' => $topic['title'],
            'section_title' => $topic['section_title']
        ];
    }
}

if (count($empty_topics) > 0) {
    $validation['curriculum']['passed'] = false;
    $validation['passed'] = false;
    
    foreach ($empty_topics as $topic) {
        $validation['curriculum']['issues'][] = [
            'message' => 'Topic "' . htmlspecialchars($topic['title']) . '" in section "' . htmlspecialchars($topic['section_title']) . '" has no content',
            'step' => 6,
            'fixUrl' => 'javascript:openTopicContent(' . $topic['topic_id'] . ')'
        ];
    }
}

// 6. Validate Quizzes have questions
$stmt = $conn->prepare("
    SELECT sq.quiz_id, sq.quiz_title, cs.title as section_title,
           (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = sq.quiz_id) as question_count
    FROM section_quizzes sq
    JOIN course_sections cs ON sq.section_id = cs.section_id
    WHERE cs.course_id = ?
");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$quizzes = $stmt->get_result();
$stmt->close();

$empty_quizzes = [];
while ($quiz = $quizzes->fetch_assoc()) {
    if ($quiz['question_count'] == 0) {
        $empty_quizzes[] = [
            'quiz_id' => $quiz['quiz_id'],
            'title' => $quiz['quiz_title'],
            'section_title' => $quiz['section_title']
        ];
    }
}

if (count($empty_quizzes) > 0) {
    $validation['assessments']['passed'] = false;
    $validation['passed'] = false;
    
    foreach ($empty_quizzes as $quiz) {
        $validation['assessments']['issues'][] = [
            'message' => 'Quiz "' . htmlspecialchars($quiz['title']) . '" in section "' . htmlspecialchars($quiz['section_title']) . '" has no questions',
            'step' => 6,
            'fixUrl' => 'quiz-builder.php?course_id=' . $course_id . '&quiz_id=' . $quiz['quiz_id']
        ];
    }
}

// Log validation results for debugging
error_log('Course Validation Results: ' . json_encode($validation));

// Return validation results
echo json_encode($validation);