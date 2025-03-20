<?php
// Turn off error display for production
ini_set('display_errors', 0);
error_reporting(0);

// Increase limits
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '16M');

// Debug mode (only with special parameter)
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    header('Content-Type: text/plain');
    echo "DEBUG MODE\n\nPOST Data:\n";
    print_r($_POST);
    echo "\n\nFILES Data:\n";
    print_r($_FILES);
    exit;
}

// Test request handler
if (isset($_SERVER['HTTP_X_TEST_REQUEST']) && $_SERVER['HTTP_X_TEST_REQUEST'] == '1') {
    header('Content-Type: application/json');
    echo json_encode([
        "success" => true, 
        "message" => "Test successful",
        "post_data" => $_POST
    ]);
    exit;
}

// Continue with regular processing
require '../session_start.php';
require '../config.php';

header('Content-Type: application/json');
$response = [];

// Log all incoming data
error_log("Received form data: " . json_encode($_POST));

// Session check
if (!isset($_SESSION['user_id'])) {
    $response["success"] = false;
    $response["message"] = "Session expired. Please login again.";
    echo json_encode($response);
    exit;
}


$userId = $_SESSION['user_id'];
$stmtInstructor = $conn->prepare("SELECT instructor_id FROM instructors WHERE user_id = ?");
$stmtInstructor->bind_param("i", $userId);
$stmtInstructor->execute();
$stmtInstructor->bind_result($instructorId);
$stmtInstructor->fetch();
$stmtInstructor->close();

if (!$instructorId) {
    error_log("Instructor ID not found for user_id: " . $userId);
    $response["success"] = false;
    $response["message"] = "Instructor not found.";
    echo json_encode($response);
    exit;
}

function sanitizeInput($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Log raw request for debugging
    error_log("Received POST request with data: " . json_encode(array_keys($_POST)));
    
    // Fix for empty courseTitle - check if it exists in POST data
    if (empty($_POST['courseTitle']) && isset($_POST['courseTitle_hidden'])) {
        $_POST['courseTitle'] = $_POST['courseTitle_hidden'];
    }
    
    $requiredFields = ['courseTitle', 'shortDescription', 'fullDescription', 'subcategory', 'tags', 'courseLevel', 'coursePrice'];
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        $response["success"] = false;
        $response["message"] = "Missing or empty required field(s): " . implode(", ", $missingFields);
        $response["debug"] = [
            "post_keys" => array_keys($_POST),
            "missing_fields" => $missingFields,
            "courseTitle_exists" => isset($_POST['courseTitle']),
            "courseTitle_value" => isset($_POST['courseTitle']) ? $_POST['courseTitle'] : 'NOT SET'
        ];
        echo json_encode($response);
        exit;
    }

    $courseTitle = sanitizeInput($_POST['courseTitle']);
    $shortDescription = sanitizeInput($_POST['shortDescription']);
    $fullDescription = sanitizeInput($_POST['fullDescription']);
    $subCategoryName = sanitizeInput($_POST['subcategory']);
    $tags = sanitizeInput($_POST['tags']);
    $courseLevel = sanitizeInput($_POST['courseLevel']);
    $price = sanitizeInput($_POST['coursePrice']);
    $certificateEnabled = isset($_POST['certificates']) ? 1 : 0;

    $stmtCategory = $conn->prepare("SELECT subcategory_id FROM subcategories WHERE subcategory_id = ?");
    $stmtCategory->bind_param("s", $subCategoryName);
    $stmtCategory->execute();
    $stmtCategory->bind_result($subCategoryId);
    $stmtCategory->fetch();
    $stmtCategory->close();

    if (!$subCategoryId) {
        $response["success"] = false;
        $response["message"] = "Invalid subcategory selected.";
        echo json_encode($response);
        exit;
    }

    $conn->begin_transaction();

    try {
        // Step 1: Insert course details
        $stmt = $conn->prepare("INSERT INTO courses (title, short_description, full_description, subcategory_id, price, certificate_enabled, course_level, instructor_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssisdsi", $courseTitle, $shortDescription, $fullDescription, $subCategoryId, $price, $certificateEnabled, $courseLevel, $instructorId);

        if (!$stmt->execute()) {
            throw new Exception("Failed to insert course: " . $stmt->error);
        }

        $courseId = $stmt->insert_id;
        $stmt->close();

        // Step 2: Insert learning outcomes
        if (isset($_POST['learningOutcomes']) && is_array($_POST['learningOutcomes'])) {
            foreach ($_POST['learningOutcomes'] as $outcome) {
                $outcome = sanitizeInput($outcome);
                if (!empty($outcome)) {
                    $stmtOutcome = $conn->prepare("INSERT INTO course_learning_outcomes (course_id, outcome_text) VALUES (?, ?)");
                    $stmtOutcome->bind_param("is", $courseId, $outcome);
                    $stmtOutcome->execute();
                    $stmtOutcome->close();
                }
            }
        }

        // Step 3: Insert requirements
        if (!empty($_POST['courseRequirements'])) {
            $requirements = sanitizeInput($_POST['courseRequirements']);
            $stmtRequirement = $conn->prepare("INSERT INTO course_requirements (course_id, requirement_text) VALUES (?, ?)");
            $stmtRequirement->bind_param("is", $courseId, $requirements);
            $stmtRequirement->execute();
            $stmtRequirement->close();
        }

        // Step 4: Insert sections and their content
        if (isset($_POST['sections']) && is_array($_POST['sections'])) {
            $sectionIds = []; // To store section IDs for later use

            foreach ($_POST['sections'] as $index => $sectionTitle) {
                $sectionTitle = sanitizeInput($sectionTitle);

                // Use the position from section_positions array if it exists
                $position = isset($_POST['section_positions'][$index]) ?
                    intval($_POST['section_positions'][$index]) : ($index + 1); // Fallback to index+1 if position not provided

                $stmtSection = $conn->prepare("INSERT INTO course_sections (course_id, title, position) VALUES (?, ?, ?)");
                $stmtSection->bind_param("isi", $courseId, $sectionTitle, $position);

                if (!$stmtSection->execute()) {
                    throw new Exception("Failed to insert section: " . $stmtSection->error);
                }

                $sectionId = $stmtSection->insert_id;
                $sectionIds[$index] = $sectionId; // Store section ID by index
                $stmtSection->close();
            }

            // Step 5: Process topics
            if (isset($_POST['topic_titles']) && is_array($_POST['topic_titles'])) {
                $topicCount = count($_POST['topic_titles']);

                for ($i = 0; $i < $topicCount; $i++) {
                    $topicTitle = sanitizeInput($_POST['topic_titles'][$i]);

                    // Get the section index for this topic
                    $sectionIndex = isset($_POST['topic_section_index'][$i]) ?
                        intval($_POST['topic_section_index'][$i]) : 0;

                    // Get the position for this topic
                    $topicPosition = isset($_POST['topic_positions'][$i]) ?
                        intval($_POST['topic_positions'][$i]) : $i;

                    // Get the section ID
                    $topicSectionId = $sectionIds[$sectionIndex] ?? null;

                    if (!$topicSectionId) {
                        error_log("Section ID not found for topic index: $i, section index: $sectionIndex");
                        continue;
                    }

                    // Insert topic
                    $stmtTopic = $conn->prepare("INSERT INTO section_topics (section_id, title, position) VALUES (?, ?, ?)");
                    $stmtTopic->bind_param("isi", $topicSectionId, $topicTitle, $topicPosition);

                    if (!$stmtTopic->execute()) {
                        throw new Exception("Failed to insert topic: " . $stmtTopic->error);
                    }

                    $topicId = $stmtTopic->insert_id;
                    $stmtTopic->close();

                    // Now process content based on type
                    // Check if content type is set for this topic
                    if (isset($_POST['content_type'][$i])) {
                        $contentType = sanitizeInput($_POST['content_type'][$i]);
                        $description = isset($_POST['topic_descriptions'][$i]) ?
                            sanitizeInput($_POST['topic_descriptions'][$i]) : '';

                        // Initialize content data
                        $contentText = null;
                        $videoUrl = null;
                        $externalUrl = null;
                        $filePath = null;

                        if ($contentType === 'text' && isset($_POST['topic_text_content'][$i])) {
                            // Text content
                            $contentText = sanitizeInput($_POST['topic_text_content'][$i]);
                        } else if ($contentType === 'video') {
                            // Video content
                            $videoType = isset($_POST['video_type'][$i]) ? sanitizeInput($_POST['video_type'][$i]) : '';

                            if ($videoType === 'upload' && isset($_FILES['topic_videos']['name'][$i])) {
                                // Handle video upload
                                if ($_FILES['topic_videos']['error'][$i] === 0) {
                                    $videoDir = '../../uploads/videos/';
                                    if (!is_dir($videoDir)) {
                                        mkdir($videoDir, 0755, true);
                                    }

                                    $ext = pathinfo($_FILES['topic_videos']['name'][$i], PATHINFO_EXTENSION);
                                    $videoFileName = "video_topic_" . $topicId . "_" . time() . ".$ext";
                                    $videoPath = $videoDir . $videoFileName;

                                    if (move_uploaded_file($_FILES['topic_videos']['tmp_name'][$i], $videoPath)) {
                                        $filePath = $videoFileName;
                                    }
                                }
                            } else if (in_array($videoType, ['youtube', 'external']) && isset($_POST['topic_video_links'][$i])) {
                                // Handle video URL
                                $videoUrl = sanitizeInput($_POST['topic_video_links'][$i]);
                            }
                        } else if ($contentType === 'link' && isset($_POST['topic_external_links'][$i])) {
                            // External link content
                            $externalUrl = sanitizeInput($_POST['topic_external_links'][$i]);
                        }

                        // Insert topic content
                        $stmtContent = $conn->prepare("INSERT INTO topic_content (
                            topic_id, content_type, title, content_text, video_url, 
                            external_url, file_path, description, position
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

                        $stmtContent->bind_param(
                            "isssssssi",
                            $topicId,
                            $contentType,
                            $topicTitle,
                            $contentText,
                            $videoUrl,
                            $externalUrl,
                            $filePath,
                            $description,
                            $i
                        );

                        if (!$stmtContent->execute()) {
                            throw new Exception("Failed to insert topic content: " . $stmtContent->error);
                        }

                        $stmtContent->close();
                    }

                    // Process any resource attachments for this topic
                    if (isset($_FILES['topic_resources']['name'][$i]) && !empty($_FILES['topic_resources']['name'][$i])) {
                        // Check if it's a single file or an array of files
                        if (is_array($_FILES['topic_resources']['name'][$i])) {
                            foreach ($_FILES['topic_resources']['name'][$i] as $resourceKey => $resourceName) {
                                if ($_FILES['topic_resources']['error'][$i][$resourceKey] === 0) {
                                    // Handle the resource file upload
                                    $resourceDir = '../../uploads/resources/';
                                    if (!is_dir($resourceDir)) {
                                        mkdir($resourceDir, 0755, true);
                                    }

                                    $ext = pathinfo($resourceName, PATHINFO_EXTENSION);
                                    $resourceFileName = "resource_topic_" . $topicId . "_" . time() . "_" . $resourceKey . ".$ext";
                                    $resourcePath = $resourceDir . $resourceFileName;

                                    if (move_uploaded_file($_FILES['topic_resources']['tmp_name'][$i][$resourceKey], $resourcePath)) {
                                        // Insert resource record (you might need a new table for topic resources)
                                        $stmtResource = $conn->prepare("INSERT INTO topic_resources (topic_id, resource_path) VALUES (?, ?)");
                                        $stmtResource->bind_param("is", $topicId, $resourceFileName);
                                        $stmtResource->execute();
                                        $stmtResource->close();
                                    }
                                }
                            }
                        } else if ($_FILES['topic_resources']['error'][$i] === 0) {
                            // Single file upload
                            $resourceDir = '../../uploads/resources/';
                            if (!is_dir($resourceDir)) {
                                mkdir($resourceDir, 0755, true);
                            }

                            $ext = pathinfo($_FILES['topic_resources']['name'][$i], PATHINFO_EXTENSION);
                            $resourceFileName = "resource_topic_" . $topicId . "_" . time() . ".$ext";
                            $resourcePath = $resourceDir . $resourceFileName;

                            if (move_uploaded_file($_FILES['topic_resources']['tmp_name'][$i], $resourcePath)) {
                                // Insert resource record
                                $stmtResource = $conn->prepare("INSERT INTO topic_resources (topic_id, resource_path) VALUES (?, ?)");
                                $stmtResource->bind_param("is", $topicId, $resourceFileName);
                                $stmtResource->execute();
                                $stmtResource->close();
                            }
                        }
                    }
                }
            }

            // Step 6: Process quizzes
            if (isset($_POST['quiz_titles']) && is_array($_POST['quiz_titles'])) {
                $quizCount = count($_POST['quiz_titles']);

                for ($i = 0; $i < $quizCount; $i++) {
                    $quizTitle = sanitizeInput($_POST['quiz_titles'][$i]);

                    // Get the section index for this quiz
                    $sectionIndex = isset($_POST['quiz_section_index'][$i]) ?
                        intval($_POST['quiz_section_index'][$i]) : 0;

                    // Get the section ID
                    $quizSectionId = $sectionIds[$sectionIndex] ?? null;

                    if (!$quizSectionId) {
                        error_log("Section ID not found for quiz index: $i, section index: $sectionIndex");
                        continue;
                    }

                    // Process randomize option
                    $randomize = isset($_POST['quiz_random'][$i]) ? 1 : 0;

                    // Process pass mark
                    $passMark = isset($_POST['quiz_pass_marks'][$i]) ?
                        intval($_POST['quiz_pass_marks'][$i]) : 70; // Default 70%

                    // Insert the quiz
                    $stmtQuiz = $conn->prepare("INSERT INTO section_quizzes (
                        section_id, quiz_title, randomize_questions, pass_mark
                    ) VALUES (?, ?, ?, ?)");

                    $stmtQuiz->bind_param("isii", $quizSectionId, $quizTitle, $randomize, $passMark);

                    if (!$stmtQuiz->execute()) {
                        throw new Exception("Failed to insert quiz: " . $stmtQuiz->error);
                    }

                    $stmtQuiz->close();
                }
            }
        }

        // Step 7: Upload and save course thumbnail
        $thumbnailPath = null;
        if (!empty($_FILES['thumbnailImage']['name']) && $_FILES['thumbnailImage']['error'] == 0) {
            $thumbnailDir = '../../uploads/thumbnails/';
            if (!is_dir($thumbnailDir)) {
                mkdir($thumbnailDir, 0755, true);
            }
            $ext = pathinfo($_FILES['thumbnailImage']['name'], PATHINFO_EXTENSION);
            $thumbnailFile = $thumbnailDir . "thumbnail_" . $courseId . "_" . time() . ".$ext";
            if (move_uploaded_file($_FILES['thumbnailImage']['tmp_name'], $thumbnailFile)) {
                $thumbnailPath = basename($thumbnailFile);

                $stmt = $conn->prepare("UPDATE courses SET thumbnail = ? WHERE course_id = ?");
                $stmt->bind_param("si", $thumbnailPath, $courseId);
                $stmt->execute();
                $stmt->close();
            }
        }

        // Step 8: Process tags
        if (!empty($tags)) {
            $tagNames = explode(',', $tags);

            foreach ($tagNames as $tagName) {
                $tagName = trim($tagName);
                if (empty($tagName)) continue;

                // Check if tag exists
                $stmtCheckTag = $conn->prepare("SELECT tag_id FROM tags WHERE tag_name = ?");
                $stmtCheckTag->bind_param("s", $tagName);
                $stmtCheckTag->execute();
                $stmtCheckTag->store_result();

                if ($stmtCheckTag->num_rows > 0) {
                    // Tag exists, get its ID
                    $stmtCheckTag->bind_result($tagId);
                    $stmtCheckTag->fetch();
                    $stmtCheckTag->close();
                } else {
                    // Create new tag
                    $stmtCheckTag->close();
                    $stmtNewTag = $conn->prepare("INSERT INTO tags (tag_name) VALUES (?)");
                    $stmtNewTag->bind_param("s", $tagName);
                    $stmtNewTag->execute();
                    $tagId = $stmtNewTag->insert_id;
                    $stmtNewTag->close();
                }

                // Map tag to course
                $stmtTagMap = $conn->prepare("INSERT INTO course_tag_mapping (course_id, tag_id) VALUES (?, ?)");
                $stmtTagMap->bind_param("ii", $courseId, $tagId);
                $stmtTagMap->execute();
                $stmtTagMap->close();
            }
        }
        // Commit the transaction if everything is successful
        $conn->commit();

        $response["success"] = true;
        $response["message"] = "Course created successfully!";
        $response["course_id"] = $courseId;
    } catch (Exception $e) {
        // Roll back the transaction if something failed
        $conn->rollback();
        error_log("Course creation error: " . $e->getMessage());

        $response["success"] = false;
        $response["message"] = "Error creating course: " . $e->getMessage();
    }

    $conn->close();
    echo json_encode($response);
    exit;
}

// If we got here, it means the request method wasn't POST
$response["success"] = false;
$response["message"] = "Invalid request method.";
echo json_encode($response);
exit;