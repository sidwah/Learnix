<?php include '../includes/student-header.php'; ?>


<!-- Main Content -->
<!-- Fixed layout with preserved UI style -->
<main id="content" role="main" class="bg-light">
    <!-- Keep existing breadcrumb and notification banner -->
    <!-- Breadcrumb -->
    <div class="container content-space-t-1 pb-3 ">
        <div class="row align-items-lg-center">
            <div class="col-lg mb-2 mb-lg-0">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb bg-primary ">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="courses.php">Courses</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($course['title']); ?></li>
                        <li class="breadcrumb-item active" aria-current="page"> <!-- Module title> --></li>
                    </ol>
                </nav>
                <!-- End Breadcrumb -->
            </div>
            <!-- End Col -->
        </div>
        <!-- End Row -->
    </div>
    <!-- End Breadcrumb -->

    <!-- Main container with consistent layout -->
    <div class="container mb-9">
        <div class="row d-flex justify-content-between">
            <!-- Left Sidebar with improved cursor styling for collapsible elements -->
            <div class="col-md-3" style="padding-right: 40px; margin-left: -20px;">
                <!-- Course Navigation with custom cursor on clickable elements -->
                <div class="sidebar-module mb-4">
                    <div class="navbar-expand-lg">
                        <div class="collapse navbar-collapse show">
                            <div class="nav nav-pills nav-vertical w-100">
                                <!-- Clickable header with pointer cursor -->
                                <a class="nav-link dropdown-toggle d-flex justify-content-between align-items-center clickable-header" data-bs-toggle="collapse" data-bs-target="#moduleAssessment">
                                    <div>
                                        <i class="bi-award nav-icon"></i>
                                        <span>Intro to Python</span>
                                    </div>
                                    <i class="bi-chevron-down small ms-2"></i>
                                </a>

                                <div id="moduleAssessment" class="nav-collapse collapse show w-100">
                                    <div class="ps-3">
                                        <!-- Navigation items with proper wrapping -->
                                        <a class="nav-link" href="#">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 me-2">
                                                    <i class="bi-check-circle-fill text-success"></i>
                                                </div>
                                                <div class="flex-grow-1 text-wrap">
                                                    <span class="fw-bold">Video:</span> Recap Programming with JavaScript
                                                    <span class="d-block text-muted small">4 min</span>
                                                </div>
                                            </div>
                                        </a>

                                        <a class="nav-link" href="#">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 me-2">
                                                    <i class="bi-check-circle-fill text-success"></i>
                                                </div>
                                                <div class="flex-grow-1 text-wrap">
                                                    <span class="fw-bold">Reading:</span> About the Little Lemon receipt maker exercise
                                                    <span class="d-block text-muted small">10 min</span>
                                                </div>
                                            </div>
                                        </a>

                                        <a class="nav-link active" href="#">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 me-2">
                                                    <i class="bi-play-circle-fill"></i>
                                                </div>
                                                <div class="flex-grow-1 text-wrap">
                                                    <span class="fw-bold">Video:</span> Introduction to JavaScript Arrays
                                                    <span class="d-block text-muted small">8 min</span>
                                                </div>
                                            </div>
                                        </a>

                                        <a class="nav-link disabled" href="#">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 me-2">
                                                    <i class="bi-lock-fill"></i>
                                                </div>
                                                <div class="flex-grow-1 text-wrap">
                                                    <span class="fw-bold">Programming Assignment:</span> Little Lemon Receipt Maker
                                                    <span class="d-block text-muted small">3h</span>
                                                </div>
                                            </div>
                                        </a>

                                        <a class="nav-link disabled" href="#">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 me-2">
                                                    <i class="bi-lock-fill"></i>
                                                </div>
                                                <div class="flex-grow-1 text-wrap">
                                                    <span class="fw-bold">Practice Assignment:</span> Self review: Little Lemon receipt maker
                                                    <span class="d-block text-muted small">5 min</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Learning Progress with pointer cursor -->
                <div class="sidebar-module mb-4">
                    <div class="navbar-expand-lg">
                        <div class="collapse navbar-collapse show">
                            <div class="nav nav-pills nav-vertical w-100">
                                <!-- Clickable header with pointer cursor -->
                                <a class="nav-link dropdown-toggle d-flex justify-content-between align-items-center clickable-header" data-bs-toggle="collapse" data-bs-target="#learningProgress">
                                    <div>
                                        <i class="bi-graph-up nav-icon"></i>
                                        <span>Learning Progress</span>
                                    </div>
                                    <i class="bi-chevron-down small ms-2"></i>
                                </a>

                                <div id="learningProgress" class="nav-collapse collapse  w-100">
                                    <div class="card border-0 bg-light w-100">
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>Current Module</span>
                                                    <span class="text-muted">3/5 Items</span>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar bg-primary" style="width: 60%"></div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>Overall Course</span>
                                                    <span class="text-muted">78% Complete</span>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar bg-primary" style="width: 78%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quiz Performance with pointer cursor -->
                <div class="sidebar-module">
                    <div class="navbar-expand-lg">
                        <div class="collapse navbar-collapse show">
                            <div class="nav nav-pills nav-vertical w-100">
                                <!-- Clickable header with pointer cursor -->
                                <a class="nav-link dropdown-toggle d-flex justify-content-between align-items-center clickable-header" data-bs-toggle="collapse" data-bs-target="#quizPerformance">
                                    <div>
                                        <i class="bi-award nav-icon"></i>
                                        <span>Quiz Performance</span>
                                    </div>
                                    <i class="bi-chevron-down small ms-2"></i>
                                </a>

                                <div id="quizPerformance" class="nav-collapse collapse  w-100">
                                    <div class="card border-0 bg-light w-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span>Average Score</span>
                                                <h4 class="mb-0 text-success">92%</h4>
                                            </div>
                                            <div class="progress mb-3">
                                                <div class="progress-bar bg-success" style="width: 92%"></div>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Quizzes Completed</span>
                                                <span class="text-muted">4 of 6</span>
                                            </div>
                                            <button class="btn btn-success w-100 mt-3">
                                                <i class="bi-list-check me-2"></i> View Quiz Results
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Updated CSS styles with text color hover instead of background -->
            <style>
                /* Remove the default caret from the dropdown-toggle class */
                .dropdown-toggle::after {
                    display: none;
                }

                /* Add pointer cursor to clickable header elements */
                .clickable-header {
                    cursor: pointer;
                }

                /* Change text color on hover instead of background */
                .clickable-header:hover {
                    background-color: transparent;
                    /* Remove background hover effect */
                    color: var(--bs-primary) !important;
                    /* Change text to primary color */
                }

                /* Also change the icon color on hover */
                .clickable-header:hover .nav-icon,
                .clickable-header:hover .bi-chevron-down {
                    color: var(--bs-primary);
                }

                /* Ensure elements take up full width */
                .w-100 {
                    width: 100% !important;
                }



                /* Add a little more space for the arrows */
                .nav-vertical .nav-link {
                    padding-right: 0.75rem;
                }

                /* Make sure nav pills wrap properly */
                .nav-vertical {
                    flex-direction: column;
                    width: 100%;
                }

                /* Add slight transition for smoother interaction */
                .clickable-header .bi-chevron-down {
                    transition: transform 0.2s ease;
                }

                /* Rotate arrow when expanded */
                .clickable-header[aria-expanded="true"] .bi-chevron-down {
                    transform: rotate(180deg);
                }

                /* Add transition for text color change */
                .clickable-header,
                .clickable-header .nav-icon,
                .clickable-header .bi-chevron-down {
                    transition: color 0.2s ease;
                }
            </style>

            <!-- Main Content Area - maintaining the right-side content -->
            <div class="col-md-9" style="padding-left: 30px;">
                <div class="card mb-4">
                    <div class="card-body p-0">
                        <div id="youTubeVideoPlayer" class="video-player video-player-inline-btn">
                            <img class="img-fluid video-player-preview" src="../assets/img/1920x800/img6.jpg" alt="Image">
                            <a class="js-inline-video-player video-player-btn video-player-centered" href="javascript:;" data-hs-video-player-options='{"videoId": "d4eDWc8g0e0", "isAutoplay": true}'>
                                <span class="video-player-icon shadow-sm">
                                    <i class="bi-play-fill"></i>
                                </span>
                            </a>
                            <div class="ratio ratio-16x9">
                                <div id="youTubeVideoIframe"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Navigation System - simplified to match your screenshot -->
                <div class="course-content-tabs">
                    <!-- Tab Navigation - simpler style matching your screenshot -->
                    <ul class="nav nav-tabs" id="courseContentTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button">Description</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="transcript-tab" data-bs-toggle="tab" data-bs-target="#transcript" type="button">Transcript</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes" type="button">Notes</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="resources-tab" data-bs-toggle="tab" data-bs-target="#resources" type="button">Resources</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="discussion-tab" data-bs-toggle="tab" data-bs-target="#discussion" type="button">Discussion</button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content p-3 border border-top-0 rounded-bottom" id="courseContentTabsContent">
                        <!-- Description Tab -->
                        <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
                            <h5>Introduction to JavaScript Arrays</h5>
                            <p>This video introduces the fundamentals of JavaScript arrays, a powerful data structure that allows you to store multiple values in a single variable. You'll learn how to create, access, and manipulate arrays through practical examples.</p>
                            <div class="mt-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi-clock me-2"></i>
                                    <span>Duration: 8 minutes</span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi-person-fill me-2"></i>
                                    <span>Instructor: John Smith</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="bi-tag-fill me-2"></i>
                                    <span>Topics: JavaScript Fundamentals, Arrays, Data Structures</span>
                                </div>
                            </div>
                        </div>

                        <!-- Transcript Tab -->
                        <div class="tab-pane fade" id="transcript" role="tabpanel" aria-labelledby="transcript-tab">
                            <div class="transcript-container">
                                <div class="d-flex justify-content-end mb-3">
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="bi-download me-1"></i> Download Transcript
                                    </button>
                                </div>
                                <div class="transcript-content">
                                    <p><strong>00:00</strong> - Welcome to this lesson on JavaScript arrays.</p>
                                    <p><strong>00:15</strong> - An array in JavaScript is a special variable that can hold more than one value at a time.</p>
                                    <p><strong>00:32</strong> - To create an array, we use square brackets and separate the items with commas.</p>
                                    <p><strong>01:05</strong> - Let's look at some examples of how to create and use arrays in our code.</p>
                                    <!-- More transcript content would go here -->
                                </div>
                            </div>
                        </div>

                        <!-- Notes Tab -->
                        <div class="tab-pane fade" id="notes" role="tabpanel" aria-labelledby="notes-tab">
                            <div class="notes-container">
                                <div class="mb-3">
                                    <textarea class="form-control" id="personalNotes" rows="8" placeholder="Take notes on this lesson..."></textarea>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <button class="btn btn-primary">
                                        <i class="bi-save me-1"></i> Save Notes
                                    </button>
                                    <button class="btn btn-outline-secondary">
                                        <i class="bi-printer me-1"></i> Print Notes
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Resources Tab -->
                        <div class="tab-pane fade" id="resources" role="tabpanel" aria-labelledby="resources-tab">
                            <div class="resources-container">
                                <h5>Additional Learning Materials</h5>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex align-items-center">
                                        <i class="bi-file-pdf me-3 text-danger fs-4"></i>
                                        <div>
                                            <p class="mb-0 fw-medium">JavaScript Arrays Cheat Sheet</p>
                                            <small class="text-muted">PDF, 256KB</small>
                                        </div>
                                        <a href="#" class="btn btn-sm btn-outline-primary ms-auto">Download</a>
                                    </li>
                                    <li class="list-group-item d-flex align-items-center">
                                        <i class="bi-file-code me-3 text-primary fs-4"></i>
                                        <div>
                                            <p class="mb-0 fw-medium">Array Practice Exercises</p>
                                            <small class="text-muted">JS, 124KB</small>
                                        </div>
                                        <a href="#" class="btn btn-sm btn-outline-primary ms-auto">Download</a>
                                    </li>
                                    <li class="list-group-item d-flex align-items-center">
                                        <i class="bi-link-45deg me-3 text-success fs-4"></i>
                                        <div>
                                            <p class="mb-0 fw-medium">MDN Web Docs: JavaScript Arrays</p>
                                            <small class="text-muted">External Resource</small>
                                        </div>
                                        <a href="https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array" target="_blank" class="btn btn-sm btn-outline-primary ms-auto">Visit</a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Discussion Tab -->
                        <div class="tab-pane fade" id="discussion" role="tabpanel" aria-labelledby="discussion-tab">
                            <div class="discussion-container">
                                <div class="mb-4">
                                    <h5>Discussion Forum</h5>
                                    <p class="text-muted">Join the conversation about this lesson with other students.</p>
                                    <div class="d-grid gap-2 d-md-flex">
                                        <button class="btn btn-primary">
                                            <i class="bi-plus-circle me-1"></i> New Discussion
                                        </button>
                                        <button class="btn btn-outline-secondary">
                                            <i class="bi-filter me-1"></i> Filter
                                        </button>
                                    </div>
                                </div>

                                <!-- Sample discussion threads -->
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <img src="https://via.placeholder.com/36" class="rounded-circle me-2" alt="User">
                                            <div>
                                                <h6 class="mb-0">Samantha Lee</h6>
                                                <small class="text-muted">2 days ago</small>
                                            </div>
                                            <span class="ms-auto badge bg-primary">3 replies</span>
                                        </div>
                                        <h6>Question about array methods</h6>
                                        <p class="mb-0">Is there a difference between using push() and directly assigning a value to an array using the index?</p>
                                    </div>
                                </div>

                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <img src="https://via.placeholder.com/36" class="rounded-circle me-2" alt="User">
                                            <div>
                                                <h6 class="mb-0">Michael Johnson</h6>
                                                <small class="text-muted">3 days ago</small>
                                            </div>
                                            <span class="ms-auto badge bg-primary">5 replies</span>
                                        </div>
                                        <h6>Arrays vs Objects - when to use which?</h6>
                                        <p class="mb-0">I'm confused about when I should use an array versus when I should use an object in JavaScript...</p>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-center mt-4">
                                    <nav aria-label="Discussion pagination">
                                        <ul class="pagination">
                                            <li class="page-item disabled">
                                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                                            </li>
                                            <li class="page-item active" aria-current="page">
                                                <a class="page-link" href="#">1</a>
                                            </li>
                                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                                            <li class="page-item">
                                                <a class="page-link" href="#">Next</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Navigation Controls -->
                    <div class="d-flex justify-content-between p-4 bg-light">
                        <button class="btn btn-outline-secondary">
                            <i class="bi-chevron-left me-2"></i> Previous Lecture
                        </button>
                        <button class="btn btn-primary">Mark as Completed</button>
                        <button class="btn btn-outline-secondary">
                            Next Lecture <i class="bi-chevron-right ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<!-- Custom CSS to fix the specific issues -->
<style>
    /* Fix text wrapping in sidebar */
    .text-wrap {
        word-break: break-word;
        overflow-wrap: break-word;
        width: 100%;
    }

    /* Ensure icon alignment */
    .nav-icon {
        width: 1.25rem;
        text-align: center;
        display: inline-block;
    }

    /* Adjust spacing in sidebar navigation */
    .nav-vertical .nav-link {
        padding: 0.5rem;
        white-space: normal;
    }

    /* Fix the flex layout to ensure proper wrapping */
    .d-flex .flex-grow-1 {
        min-width: 0;
    }

    /* Prevent content from breaking out of containers */
    .navbar-sidebar-aside-content {
        max-width: 100%;
        overflow-wrap: break-word;
    }

    /* Ensure proper spacing between sidebar and main content on mobile */
    @media (max-width: 767.98px) {
        .col-md-3 {
            margin-bottom: 2rem;
        }
    }

    /* Match the tab style in the screenshot */
    .nav-tabs .nav-link {
        border-radius: 0;
        padding: 0.5rem 1rem;
    }
</style>


<!-- Custom CSS for hover effects -->
<style>
    .hover-lift {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .hover-lift:hover {
        transform: translateY(-10px);
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, .175);
    }
</style>


<?php include '../includes/student-footer.php'; ?>