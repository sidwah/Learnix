<?php
require '../backend/session_start.php';

// Check if user is signed in as instructor
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    header('Location: landing.php');
    exit;
}

// Get course ID from URL
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// If no course ID is provided, redirect to dashboard
if ($course_id === 0) {
    header('Location: dashboard.php');
    exit;
}

// Include database connection
require_once '../backend/config.php';

// Fetch course details
$query = "SELECT * FROM courses WHERE course_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();

// If course not found or doesn't belong to this instructor
if (!$course || $course['instructor_id'] != $_SESSION['instructor_id']) {
    header('Location: dashboard.php');
    exit;
}

// Set page title
$page_title = "Preview: " . htmlspecialchars($course['title']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title><?php echo $page_title; ?> | Learnix</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Preview your course as students will see it" name="description" />
    
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- App CSS -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style"/>
    
    <!-- Custom CSS for preview mode -->
    <style>
        .preview-banner {
            background-color: #ff9800;
            color: white;
            padding: 8px 15px;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 1030;
        }
        
        .course-content-nav .nav-link.active {
            background-color: #3e7bfa;
            color: white !important;
        }
        
        .content-viewer {
            min-height: 400px;
            border: 1px solid #e2e8f0;
            border-radius: 0.25rem;
            padding: 20px;
        }
        
        .resource-item {
            border-left: 3px solid #3e7bfa;
        }
        
        .course-section {
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 15px;
        }
        
        .course-section:last-child {
            border-bottom: none;
        }
        
        .topic-item {
            cursor: pointer;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 5px;
            transition: all 0.2s;
        }
        
        .topic-item:hover {
            background-color: #f0f4ff;
        }
        
        .topic-item.active {
            background-color: #e6ecff;
            border-left: 3px solid #3e7bfa;
        }
        
        .quiz-item {
            border-left: 3px solid #3e7bfa;
        }
         /* Video container and alert styles */
    .video-container {
        border-radius: 8px;
        overflow: hidden;
        background-color: #f8f9fa;
        padding: 15px;
    }
    
    .preview-alert {
        border-radius: 4px;
    }
    
    /* Hide the preview alert by default, it will be shown when video fails to load */
    .preview-alert {
        display: none;
    }
    
    /* YouTube iframe styles */
    .youtube-player {
        border: none;
    }
        /* Video container styling */
.embed-responsive-container {
    border: 1px solid #eee;
    border-radius: 8px;
    overflow: hidden;
    background-color: #f8f9fa;
    padding: 15px;
}

/* Error message styling for video */
.video-error-message {
    display: none;
    padding: 20px;
    text-align: center;
    background-color: #f8f9fa;
}

/* Style for video fallback options */
.fallback-options {
    border-top: 1px solid #eee;
    padding-top: 15px;
    margin-top: 15px;
}
/* Add these styles to your CSS */
.form-check.text-success {
    background-color: rgba(40, 167, 69, 0.1);
    padding: 10px;
    border-radius: 4px;
    border-left: 3px solid #28a745;
}

.form-check.text-danger {
    background-color: rgba(220, 53, 69, 0.1);
    padding: 10px;
    border-radius: 4px;
    border-left: 3px solid #dc3545;
}

.answer-feedback {
    border-top: 1px solid rgba(0, 0, 0, 0.125);
}

/* Add a visual cue for unanswered questions */
.question-card.unanswered {
    border-left: 3px solid #ffc107;
}
    </style>
    <style>
    /* Answer styling for quiz feedback */
    .selected-correct {
        background-color: rgba(25, 135, 84, 0.1);
        padding: 10px 15px;
        border-radius: 4px;
        border-left: 4px solid #198754;
        margin-bottom: 10px !important;
    }
    
    .selected-incorrect {
        background-color: rgba(220, 53, 69, 0.1);
        padding: 10px 15px;
        border-radius: 4px;
        border-left: 4px solid #dc3545;
        margin-bottom: 10px !important;
    }
    
    .correct-answer {
        background-color: rgba(25, 135, 84, 0.05);
        padding: 10px 15px;
        border-radius: 4px;
        border-left: 4px solid #198754;
        margin-bottom: 10px !important;
    }
    
    /* Question card styling */
    .question-card {
        transition: all 0.3s ease;
    }
    
    .question-card.border-success {
        border-left: 5px solid #198754 !important;
    }
    
    .question-card.border-danger {
        border-left: 5px solid #dc3545 !important;
    }
    
    .question-card.border-warning {
        border-left: 5px solid #ffc107 !important;
    }
    
    /* Make disabled radio buttons look better */
    input[type="radio"]:disabled + label {
        cursor: default;
        opacity: 0.8;
    }
</style>
</head>

<body class="loading" data-layout-color="light" data-leftbar-theme="dark" data-layout-mode="fluid">
    <!-- Preview banner -->
    <div class="preview-banner">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <strong>PREVIEW MODE:</strong> This is how students will see your course
                </div>
                <div class="col-md-4 text-end">
                    <a href="course-creator.php?course_id=<?php echo $course_id; ?>&step=6" class="btn btn-light btn-sm">
                        <i class="mdi mdi-pencil"></i> Exit Preview & Edit
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Begin page -->
    <div class="wrapper mt-3">
        <div class="container-fluid">
            <!-- Course Header -->
            <!-- Course Header -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h2><?php echo htmlspecialchars($course['title']); ?></h2>
                        <p class="text-muted"><?php echo htmlspecialchars($course['short_description']); ?></p>
                        <div class="mt-3">
                            <!-- Add the price display here -->
                            <div class="mb-2">
                                <h3 class="text-primary">
                                    <?php if ($course['price'] > 0): ?>
                                        $<?php echo number_format($course['price'], 2); ?>
                                    <?php else: ?>
                                        Free
                                    <?php endif; ?>
                                </h3>
                            </div>
                            <span class="badge bg-primary me-1">
                                <?php echo htmlspecialchars($course['course_level']); ?>
                            </span>
                            <?php if ($course['certificate_enabled']): ?>
                            <span class="badge bg-success">Certificate Available</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <?php if ($course['thumbnail']): ?>
                        <img src="../uploads/thumbnails/<?php echo $course['thumbnail']; ?>" 
                             alt="Course Thumbnail" class="img-fluid rounded" style="max-height: 180px;">
                        <?php else: ?>
                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                             style="height: 180px; width: 100%;">
                            <span class="text-muted">No thumbnail</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

            <!-- Main Content Area -->
            <div class="row">
                <!-- Course Navigation Sidebar -->
                <div class="col-md-4 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-grid">
                                <button class="btn btn-primary" disabled>Enrolled</button>
                            </div>
                            
                            <!-- Course Navigation Tabs -->
                            <ul class="nav nav-pills nav-fill bg-light rounded mt-3 mb-3 course-content-nav" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#curriculum-tab" role="tab">
                                        <i class="mdi mdi-book-outline me-1"></i> Curriculum
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" data-bs-toggle="tab" href="#overview-tab" role="tab">
                                        <i class="mdi mdi-information-outline me-1"></i> Overview
                                    </a>
                                </li>
                            </ul>
                            
                            <!-- Tab Content -->
                            <div class="tab-content">
                                <!-- Curriculum Tab -->
                                <div class="tab-pane fade show active" id="curriculum-tab" role="tabpanel">
                                    <?php
                                    // Fetch sections for this course
                                    $sections_query = "SELECT * FROM course_sections WHERE course_id = ? ORDER BY position ASC";
                                    $stmt = $conn->prepare($sections_query);
                                    $stmt->bind_param("i", $course_id);
                                    $stmt->execute();
                                    $sections_result = $stmt->get_result();
                                    
                                    if ($sections_result->num_rows === 0) {
                                        echo '<div class="alert alert-info">No content has been added to this course yet.</div>';
                                    } else {
                                        while ($section = $sections_result->fetch_assoc()):
                                    ?>
                                    <div class="course-section">
                                        <h5><?php echo htmlspecialchars($section['title']); ?></h5>
                                        
                                        <div class="topics-list">
                                        <?php
                                        // Fetch topics for this section
                                        $topics_query = "SELECT * FROM section_topics WHERE section_id = ? ORDER BY position ASC";
                                        $topics_stmt = $conn->prepare($topics_query);
                                        $topics_stmt->bind_param("i", $section['section_id']);
                                        $topics_stmt->execute();
                                        $topics_result = $topics_stmt->get_result();
                                        
                                        // Fetch quizzes for this section
                                        $quizzes_query = "SELECT * FROM section_quizzes WHERE section_id = ?";
                                        $quizzes_stmt = $conn->prepare($quizzes_query);
                                        $quizzes_stmt->bind_param("i", $section['section_id']);
                                        $quizzes_stmt->execute();
                                        $quizzes_result = $quizzes_stmt->get_result();
                                        
                                        $has_content = false;
                                        
                                        // Output topics
                                        while ($topic = $topics_result->fetch_assoc()):
                                            $has_content = true;
                                        ?>
                                            <div class="topic-item" data-topic-id="<?php echo $topic['topic_id']; ?>">
                                                <div class="d-flex align-items-center">
                                                    <i class="mdi mdi-file-document-outline me-2"></i>
                                                    <div>
                                                        <?php echo htmlspecialchars($topic['title']); ?>
                                                        <?php if ($topic['is_previewable']): ?>
                                                        <span class="badge bg-success ms-1">Preview</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                        endwhile;
                                        
                                        // Output quizzes
                                        while ($quiz = $quizzes_result->fetch_assoc()):
                                            $has_content = true;
                                        ?>
                                            <div class="topic-item quiz-item" data-quiz-id="<?php echo $quiz['quiz_id']; ?>">
                                                <div class="d-flex align-items-center">
                                                    <i class="mdi mdi-help-circle-outline me-2 text-primary"></i>
                                                    <div>
                                                        <?php echo htmlspecialchars($quiz['quiz_title']); ?>
                                                        <span class="badge bg-primary ms-1">Quiz</span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                        endwhile;
                                        
                                        // Close statement for topics and quizzes
                                        $topics_stmt->close();
                                        $quizzes_stmt->close();
                                        
                                        if (!$has_content) {
                                            echo '<div class="alert alert-warning">No content in this section yet.</div>';
                                        }
                                        ?>
                                        </div>
                                    </div>
                                    <?php
                                        endwhile;
                                    }
                                    $stmt->close();
                                    ?>
                                </div>
                                
                                <!-- Overview Tab -->
                                <div class="tab-pane fade" id="overview-tab" role="tabpanel">
                                    <div class="overview-content">
                                        <h5>About This Course</h5>
                                        <div class="mb-3">
                                            <?php echo $course['full_description'] ?? 'No description available.'; ?>
                                        </div>
                                        
                                        <h5>What You'll Learn</h5>
                                        <ul class="list-group mb-3">
                                            <?php
                                            // Fetch learning outcomes
                                            $outcomes_query = "SELECT * FROM course_learning_outcomes WHERE course_id = ?";
                                            $stmt = $conn->prepare($outcomes_query);
                                            $stmt->bind_param("i", $course_id);
                                            $stmt->execute();
                                            $outcomes_result = $stmt->get_result();
                                            
                                            if ($outcomes_result->num_rows === 0) {
                                                echo '<li class="list-group-item">No learning outcomes specified.</li>';
                                            } else {
                                                while ($outcome = $outcomes_result->fetch_assoc()) {
                                                    echo '<li class="list-group-item">';
                                                    echo '<i class="mdi mdi-check-circle text-success me-2"></i>';
                                                    echo htmlspecialchars($outcome['outcome_text']);
                                                    echo '</li>';
                                                }
                                            }
                                            $stmt->close();
                                            ?>
                                        </ul>
                                        
                                        <h5>Prerequisites</h5>
                                        <ul class="list-group mb-3">
                                            <?php
                                            // Fetch course requirements
                                            $requirements_query = "SELECT * FROM course_requirements WHERE course_id = ?";
                                            $stmt = $conn->prepare($requirements_query);
                                            $stmt->bind_param("i", $course_id);
                                            $stmt->execute();
                                            $requirements_result = $stmt->get_result();
                                            
                                            if ($requirements_result->num_rows === 0) {
                                                echo '<li class="list-group-item">No prerequisites specified.</li>';
                                            } else {
                                                while ($requirement = $requirements_result->fetch_assoc()) {
                                                    echo '<li class="list-group-item">';
                                                    echo '<i class="mdi mdi-circle-medium text-primary me-2"></i>';
                                                    echo htmlspecialchars($requirement['requirement_text']);
                                                    echo '</li>';
                                                }
                                            }
                                            $stmt->close();
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Content Display Area -->
                <div class="col-md-8 col-lg-9">
                    <div class="card">
                        <div class="card-body">
                            <div id="content-viewer" class="content-viewer">
                                <div class="text-center py-5">
                                    <i class="mdi mdi-play-circle-outline" style="font-size: 64px; color: #3e7bfa;"></i>
                                    <h4 class="mt-3">Start Learning</h4>
                                    <p class="text-muted">Select a topic from the curriculum to begin.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Bundle -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.min.js"></script>

    <script>
    $(document).ready(function() {
        // Handle topic selection
        $('.topic-item').click(function() {
            // Remove active class from all topics
            $('.topic-item').removeClass('active');
            
            // Add active class to selected topic
            $(this).addClass('active');
            
            // Show loading in content viewer
            $('#content-viewer').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading content...</p>
                </div>
            `);
            
            // Determine if this is a topic or quiz
            const topicId = $(this).data('topic-id');
            const quizId = $(this).data('quiz-id');
            
            if (topicId) {
                // Load topic content
                $.ajax({
                    url: '../ajax/preview/load_topic_content.php',
                    type: 'GET',
                    data: {
                        topic_id: topicId
                    },
                    success: function(response) {
                        $('#content-viewer').html(response);
                    },
                    error: function() {
                        $('#content-viewer').html(`
                            <div class="alert alert-danger">
                                <h5 class="alert-heading">Error Loading Content</h5>
                                <p class="mb-0">There was a problem loading this topic's content.</p>
                            </div>
                        `);
                    }
                });
            } else if (quizId) {
                // Load quiz preview
                $.ajax({
                    url: '../ajax/preview/load_quiz_preview.php',
                    type: 'GET',
                    data: {
                        quiz_id: quizId
                    },
                    success: function(response) {
                        $('#content-viewer').html(response);
                    },
                    error: function() {
                        $('#content-viewer').html(`
                            <div class="alert alert-danger">
                                <h5 class="alert-heading">Error Loading Quiz</h5>
                                <p class="mb-0">There was a problem loading this quiz preview.</p>
                            </div>
                        `);
                    }
                });
            }
        });
    });

    // Handle video iframe errors
$(document).ready(function() {
    // Function to check if iframe loaded properly
    function checkIframeLoaded() {
        $('iframe').each(function() {
            // Save iframe reference
            var iframe = this;
            var iframeLoaded = false;
            
            // Check if iframe is loaded
            $(iframe).on('load', function() {
                console.log("Iframe loaded successfully");
                iframeLoaded = true;
            });
            
            // Set timeout to check if iframe failed to load
            setTimeout(function() {
                // Only check for local iframes, not YouTube/Vimeo
                if (!iframe.src.includes('youtube.com') && 
                    !iframe.src.includes('vimeo.com') && 
                    !iframeLoaded) {
                    console.log("Iframe failed to load properly");
                    
                    // Show error message for local videos only
                    $(iframe).closest('.ratio').prepend(`
                        <div class="video-error-message" style="display:block; position:absolute; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.05); z-index:1;">
                            <div class="alert alert-warning m-3">
                                <h5><i class="mdi mdi-alert-circle-outline"></i> Video Playback Issue</h5>
                                <p>The video could not be loaded in preview mode. Please use the alternative links below.</p>
                            </div>
                        </div>
                    `);
                }
            }, 3000); // Check after 3 seconds
        });
    }
    
    // Run iframe check when each topic is loaded
    $('.topic-item').on('click', function() {
        // Wait for content to load before checking iframes
        setTimeout(checkIframeLoaded, 1500);
    });
});
    </script>
</body>
</html>