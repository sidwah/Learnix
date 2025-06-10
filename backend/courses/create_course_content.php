<?php
// create_course_content.php - Handles saving course content (Step 3)

// Start session and include config
require '../session_start.php';
require '../config.php';

// Set response header
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'topic_ids' => [],
    'quiz_ids' => []
];

// Function to sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Session expired. Please login again.';
    echo json_encode($response);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

// Validate course ID
if (!isset($_POST['course_id']) || empty($_POST['course_id'])) {
    $response['message'] = 'Course ID is required.';
    echo json_encode($response);
    exit;
}

$courseId = intval($_POST['course_id']);

// Validate section IDs
if (!isset($_POST['section_ids']) || !is_array($_POST['section_ids']) || count($_POST['section_ids']) === 0) {
    $response['message'] = 'Section IDs are required.';
    echo json_encode($response);
    exit;
}

// Get instructor ID for the logged-in user
$userId = $_SESSION['user_id'];
$stmtInstructor = $conn->prepare("SELECT instructor_id FROM instructors WHERE user_id = ?");
$stmtInstructor->bind_param("i", $userId);
$stmtInstructor->execute();
$stmtInstructor->bind_result($instructorId);
$stmtInstructor->fetch(); 
$stmtInstructor->close();

if (!$instructorId) {
    $response['message'] = 'Instructor not found. Please make sure your account has instructor privileges.';
    echo json_encode($response);
    exit;
}

// Verify course belongs to this instructor
$stmtVerifyCourse = $conn->prepare("SELECT course_id FROM courses WHERE course_id = ? AND instructor_id = ?");
$stmtVerifyCourse->bind_param("ii", $courseId, $instructorId);
$stmtVerifyCourse->execute();
$stmtVerifyCourse->store_result();

if ($stmtVerifyCourse->num_rows === 0) {
    $response['message'] = 'You do not have permission to modify this course.';
    echo json_encode($response);
    exit;
}
$stmtVerifyCourse->close();

// Start transaction
$conn->begin_transaction();

try {
    // Process each section
    $sectionIds = $_POST['section_ids'];
    
    foreach ($sectionIds as $sectionId) {
        $sectionId = intval($sectionId);
        
        // Verify section belongs to this course
        $stmtVerifySection = $conn->prepare("SELECT section_id FROM course_sections WHERE section_id = ? AND course_id = ?");
        $stmtVerifySection->bind_param("ii", $sectionId, $courseId);
        $stmtVerifySection->execute();
        $stmtVerifySection->store_result();
        
        if ($stmtVerifySection->num_rows === 0) {
            throw new Exception("Section ID {$sectionId} does not belong to this course.");
        }
        $stmtVerifySection->close();
        
        // First, delete existing topics and quizzes for this section
        // We'll use TRUNCATE for performance, but only if we're certain these tables have proper foreign keys
        $stmtDeleteTopics = $conn->prepare("DELETE FROM section_topics WHERE section_id = ?");
        $stmtDeleteTopics->bind_param("i", $sectionId);
        $stmtDeleteTopics->execute();
        $stmtDeleteTopics->close();
        
        $stmtDeleteQuizzes = $conn->prepare("DELETE FROM section_quizzes WHERE section_id = ?");
        $stmtDeleteQuizzes->bind_param("i", $sectionId);
        $stmtDeleteQuizzes->execute();
        $stmtDeleteQuizzes->close();
        
        // Process topics for this section
        if (isset($_POST['topic_titles'][$sectionId]) && is_array($_POST['topic_titles'][$sectionId])) {
            // Prepare statement for inserting topics
            $stmtInsertTopic = $conn->prepare("
                INSERT INTO section_topics (section_id, title, position)
                VALUES (?, ?, ?)
            ");
            
            foreach ($_POST['topic_titles'][$sectionId] as $index => $title) {
                $topicTitle = sanitizeInput($title);
                $position = $index;
                
                // Insert the topic
                $stmtInsertTopic->bind_param("isi", $sectionId, $topicTitle, $position);
                $stmtInsertTopic->execute();
                $topicId = $stmtInsertTopic->insert_id;
                
                // Store topic ID for the response
                $response['topic_ids'][$sectionId][$index] = $topicId;
                
                // Process content based on type
                if (isset($_POST['content_type'][$sectionId][$index])) {
                    $contentType = sanitizeInput($_POST['content_type'][$sectionId][$index]);
                    $description = isset($_POST['topic_descriptions'][$sectionId][$index]) ? 
                        sanitizeInput($_POST['topic_descriptions'][$sectionId][$index]) : '';
                    
                    // Initialize content variables
                    $contentText = null;
                    $videoUrl = null;
                    $externalUrl = null;
                    $filePath = null;
                    
                    // Process different content types
                    if ($contentType === 'text' && isset($_POST['topic_text_content'][$sectionId][$index])) {
                        $contentText = sanitizeInput($_POST['topic_text_content'][$sectionId][$index]);
                    } else if ($contentType === 'video') {
                        if (isset($_POST['video_type'][$sectionId][$index])) {
                            $videoType = sanitizeInput($_POST['video_type'][$sectionId][$index]);
                            
                            if (($videoType === 'youtube' || $videoType === 'external') && 
                                isset($_POST['topic_video_links'][$sectionId][$index])) {
                                $videoUrl = sanitizeInput($_POST['topic_video_links'][$sectionId][$index]);
                            }
                        }
                    } else if ($contentType === 'link' && isset($_POST['topic_external_links'][$sectionId][$index])) {
                        $externalUrl = sanitizeInput($_POST['topic_external_links'][$sectionId][$index]);
                        
                        // Add link description if available
                        if (isset($_POST['topic_link_descriptions'][$sectionId][$index])) {
                            $linkDescription = sanitizeInput($_POST['topic_link_descriptions'][$sectionId][$index]);
                            // Note: You may want to store this in a separate field or column
                        }
                    }
                    
                    // Insert topic content
                    $stmtInsertContent = $conn->prepare("
                        INSERT INTO topic_content (
                            topic_id, content_type, title, content_text, 
                            video_url, external_url, file_path, description, position
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    
                    $stmtInsertContent->bind_param(
                        "isssssssi",
                        $topicId,
                        $contentType,
                        $topicTitle,
                        $contentText,
                        $videoUrl,
                        $externalUrl,
                        $filePath,
                        $description,
                        $position
                    );
                    
                    $stmtInsertContent->execute();
                    $stmtInsertContent->close();
                }
            }
            
            $stmtInsertTopic->close();
        }
        
        // Process quizzes for this section
        if (isset($_POST['quiz_titles'][$sectionId]) && is_array($_POST['quiz_titles'][$sectionId])) {
            // Prepare statement for inserting quizzes
            $stmtInsertQuiz = $conn->prepare("
                INSERT INTO section_quizzes (
                    section_id, quiz_title, randomize_questions, pass_mark
                ) VALUES (?, ?, ?, ?)
            ");
            
            foreach ($_POST['quiz_titles'][$sectionId] as $index => $title) {
                $quizTitle = sanitizeInput($title);
                $randomize = isset($_POST['quiz_random'][$sectionId][$index]) && 
                             $_POST['quiz_random'][$sectionId][$index] == '1' ? 1 : 0;
                $passMark = isset($_POST['quiz_pass_marks'][$sectionId][$index]) ? 
                            intval($_POST['quiz_pass_marks'][$sectionId][$index]) : 70;
                
                // Insert the quiz
                $stmtInsertQuiz->bind_param("isii", $sectionId, $quizTitle, $randomize, $passMark);
                $stmtInsertQuiz->execute();
                $quizId = $stmtInsertQuiz->insert_id;
                
                // Store quiz ID for the response
                $response['quiz_ids'][$sectionId][$index] = $quizId;
            }
            
            $stmtInsertQuiz->close();
        }
    }
    
    // Update course creation step
    $stmtStep = $conn->prepare("UPDATE courses SET creation_step = 3 WHERE course_id = ?");
    $stmtStep->bind_param("i", $courseId);
    $stmtStep->execute();
    $stmtStep->close();
    
    // Commit transaction
    $conn->commit();
    
    // Return success
    $response['success'] = true;
    $response['message'] = 'Course content saved successfully.';
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    
    // Add detailed error info for debugging
    if (isset($_GET['debug']) && $_GET['debug'] === '1') {
        $response['debug'] = [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'post' => $_POST
        ];
    }
} finally {
    // Close connection
    $conn->close();
}

// Send JSON response
echo json_encode($response);
exit;