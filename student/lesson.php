<?php include '../includes/student-header.php'; ?>

<!-- Skip navigation for accessibility -->
<a href="#content" class="visually-hidden-focusable p-3 bg-white text-primary position-absolute">Skip to main content</a>

<!-- ========== END HEADER ========== -->
<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main">

    <!-- Course Header with Background Image -->
    <div class="position-relative">

        <!-- Hero with improved spacing -->
        <div class="gradient-y-overlay-lg-white bg-img-start content-space-2" style="background-image: url(../assets/img/1920x800/img6.jpg);">
            <div class="container">
                <!-- Breadcrumb Navigation with improved accessibility -->
                <nav aria-label="breadcrumb" class="mt-2 mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Python</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Python Setup</li>
                    </ol>
                </nav>
                <div class="row">
                    <div class="col-md-10 col-lg-10">
                        <small class="badge bg-success rounded-pill">Bestseller</small>
                        <h1 class="mb-3">Complete Python Bootcamp: Go from zero to hero in Python 3</h1>
                        <p class="lead mb-4">Learn Python like a Professional! Start from the basics and go all the way to creating your own applications and games!</p>

                        <div class="d-flex align-items-center flex-wrap">
                            <!-- Instructor Media with better spacing -->
                            <div class="d-flex align-items-center me-4 mb-2">
                                <div class="flex-shrink-0 avatar-group avatar-group-xs">
                                    <span class="avatar avatar-xs avatar-circle">
                                        <img class="avatar-img" src="../assets/img/160x160/img10.jpg" alt="Instructor Christina Kray">
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="ps-2">Created by <a class="link" href="author-profile.php">Christina Kray</a></span>
                                </div>
                            </div> 
                            <!-- End Instructor Media -->

                            <!-- Rating with better alignment and accessibility -->
                            <div class="d-flex align-items-center flex-wrap mb-2">
                                <div class="d-flex gap-1 me-2" aria-hidden="true">
                                    <img src="../assets/svg/illustrations/star.svg" alt="" width="16">
                                    <img src="../assets/svg/illustrations/star.svg" alt="" width="16">
                                    <img src="../assets/svg/illustrations/star.svg" alt="" width="16">
                                    <img src="../assets/svg/illustrations/star.svg" alt="" width="16">
                                    <img src="../assets/svg/illustrations/star.svg" alt="" width="16">
                                </div>
                                <div class="ms-1">
                                    <span class="fw-semi-bold text-dark me-1">4.91</span>
                                    <span class="visually-hidden">out of 5 stars</span>
                                    <span>(1.5k+ reviews)</span>
                                </div>
                            </div>
                            <!-- End Rating -->
                        </div>

                        <!-- Streak and Achievement badges with improved positioning and accessibility -->
                        <div class="position-absolute" style="top: var(--spacing-md); right: var(--spacing-lg);">
                            <!-- Study Streak Badge -->
                            <div class="d-inline-block me-3">
                                <div class="badge bg-light text-primary border border-primary p-2 px-3">
                                    <i class="bi-fire text-warning me-1" aria-hidden="true"></i> <span>5 Day Streak</span>
                                </div>
                            </div>

                            <!-- Achievements Badge -->
                            <div class="d-inline-block">
                                <div class="badge bg-light text-primary border border-primary p-2 px-3">
                                    <i class="bi-trophy-fill me-1" aria-hidden="true"></i> <span>3 Achievements</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Hero -->

        <!-- Progress Bar Overlay with improved spacing and accessibility -->
        <div class="container position-relative" style="margin-top: -30px; z-index: 1;">
            <div class="row">
                <div class="col-md-8">
                    <div class="card p-3 shadow-sm">
                        <div class="d-flex align-items-center">
                            <div style="width: 100%;">
                                <div class="progress" style="height: 10px; border-radius: var(--radius-sm);" aria-labelledby="progress-label">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 35%;"
                                        aria-valuenow="35" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <p id="progress-label" class="small text-muted mb-0"><strong>35% Complete</strong></p>
                                    <p class="small text-muted mb-0">Estimated completion: May 12, 2025</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Dashboard Content with better spacing -->
    <div class="container mt-5">
        <div class="row g-4"> <!-- Added gutter spacing between columns -->
            <!-- Left Sidebar with improved spacing -->
            <div class="col-md-3">
                <div class="bg-light border-end rounded-lg shadow-sm p-4" style="min-height: 700px;">
                    <h6 class="fw-bold text-uppercase mb-4">Course Content</h6>

                    <!-- Module List with improved spacing and accessibility -->
                    <div class="accordion" id="courseModules">
                        <div class="card border-0 mb-3 bg-primary-soft border-primary" style="border-radius: var(--radius-md);">
                            <div class="card-body p-3">
                                <a href="#" class="d-flex justify-content-between align-items-center text-decoration-none text-primary fw-bold" aria-current="true">
                                    <span>Course Overview</span>
                                    <span class="text-primary">100%</span>
                                </a>
                            </div>
                        </div>

                        <div class="card border-0 mb-3 bg-primary-soft border-primary" style="border-radius: var(--radius-md);">
                            <div class="card-body p-3">
                                <a href="#" class="d-flex justify-content-between align-items-center text-decoration-none text-primary fw-bold" aria-current="true">
                                    <span>Python Setup</span>
                                    <span class="text-primary">75%</span>
                                </a>
                            </div>
                        </div>

                        <div class="card border-0 mb-3" style="border-radius: var(--radius-md);">
                            <div class="card-body p-3">
                                <a href="#" class="d-flex justify-content-between align-items-center text-decoration-none">
                                    <span>Python Object and Data Structure</span>
                                    <span class="text-muted">0%</span>
                                </a>
                            </div>
                        </div>

                        <div class="card border-0 mb-3" style="border-radius: var(--radius-md);">
                            <div class="card-body p-3">
                                <a href="#" class="d-flex justify-content-between align-items-center text-decoration-none">
                                    <span>Python Comparison Operators</span>
                                    <span class="text-muted">0%</span>
                                </a>
                            </div>
                        </div>

                        <div class="card border-0 mb-3" style="border-radius: var(--radius-md);">
                            <div class="card-body p-3">
                                <a href="#" class="d-flex justify-content-between align-items-center text-decoration-none">
                                    <span>Python Statements</span>
                                    <span class="text-muted">0%</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Resources Accordion for reducing information density -->
                    <div class="accordion mt-4" id="additionalResources">
                        <div class="accordion-item border-0 bg-transparent">
                            <h2 class="accordion-header" id="resourcesHeading">
                                <button class="accordion-button collapsed p-0 bg-transparent shadow-none fw-bold" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#resourcesCollapse"
                                    aria-expanded="false" aria-controls="resourcesCollapse">
                                    <span>Additional Resources</span>
                                </button>
                            </h2>
                            <div id="resourcesCollapse" class="accordion-collapse collapse" aria-labelledby="resourcesHeading">
                                <div class="accordion-body px-0 pt-3">
                                    <!-- Study Schedule -->
                                    <div class="card bg-primary-soft mb-3 border-0" style="border-radius: var(--radius-md);">
                                        <div class="card-body p-3">
                                            <h6 class="fw-bold mb-2">
                                                <i class="bi-calendar-check me-2" aria-hidden="true"></i> Study Schedule
                                            </h6>
                                            <p class="small mb-1">Next session: Today @ 7:00 PM</p>
                                            <p class="small mb-0">
                                                <a href="#" class="text-primary">Manage schedule <i class="bi-chevron-right small" aria-hidden="true"></i></a>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Quiz Performance Summary -->
                                    <div class="card bg-success-soft mb-3 border-0" style="border-radius: var(--radius-md);">
                                        <div class="card-body p-3">
                                            <h6 class="fw-bold mb-2">
                                                <i class="bi-graph-up me-2" aria-hidden="true"></i> Quiz Performance
                                            </h6>
                                            <p class="small mb-1">Avg. Score: 92%</p>
                                            <p class="small mb-0">Completed: 2/8 Quizzes</p>
                                        </div>
                                    </div>

                                    <!-- Course Resources Summary -->
                                    <div class="card bg-light mb-3 border-0" style="border-radius: var(--radius-md);">
                                        <div class="card-body p-3">
                                            <h6 class="fw-bold mb-2">
                                                <i class="bi-file-earmark-text me-2" aria-hidden="true"></i> Course Resources
                                            </h6>
                                            <p class="small mb-1">• 77 Articles</p>
                                            <p class="small mb-1">• 85 Downloadable resources</p>
                                            <p class="small mb-0">• Certificate of Completion</p>
                                        </div>
                                    </div>

                                    <!-- Learning Community -->
                                    <div class="card bg-info-soft border-0" style="border-radius: var(--radius-md);">
                                        <div class="card-body p-3">
                                            <h6 class="fw-bold mb-2">
                                                <i class="bi-people-fill me-2" aria-hidden="true"></i> Learning Community
                                            </h6>
                                            <p class="small mb-1">• 3,452 students enrolled</p>
                                            <p class="small mb-1">• 35 active discussions</p>
                                            <p class="small mb-0">
                                                <a href="#" class="text-info">Join conversation <i class="bi-chevron-right small" aria-hidden="true"></i></a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area - Wider column with better spacing -->
            <div class="col-md-9">
                <!-- Module Card with improved spacing -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center p-4 border-0">
                        <h2 class="h3 mb-0">Python Setup</h2>

                        <!-- Section Progress with improved styling and accessibility -->
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="progress" style="height: 10px; width: 100px; border-radius: var(--radius-sm);" aria-labelledby="section-progress-label">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 75%;"
                                        aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <p id="section-progress-label" class="small text-muted mb-0">9/12 Lectures</p>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <!-- Tab Navigation with accessibility improvements -->
                        <ul class="nav nav-tabs mb-4" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-semibold" id="lectures-tab" data-bs-toggle="tab"
                                    data-bs-target="#lectures-content" type="button" role="tab"
                                    aria-controls="lectures-content" aria-selected="true">
                                    <i class="bi-play-btn me-1" aria-hidden="true"></i> Lectures
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="resources-tab" data-bs-toggle="tab"
                                    data-bs-target="#resources-content" type="button" role="tab"
                                    aria-controls="resources-content" aria-selected="false">
                                    <i class="bi-file-earmark-text me-1" aria-hidden="true"></i> Resources
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="quizzes-tab" data-bs-toggle="tab"
                                    data-bs-target="#quizzes-content" type="button" role="tab"
                                    aria-controls="quizzes-content" aria-selected="false">
                                    <i class="bi-check2-square me-1" aria-hidden="true"></i> Quizzes
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="discussion-tab" data-bs-toggle="tab"
                                    data-bs-target="#discussion-content" type="button" role="tab"
                                    aria-controls="discussion-content" aria-selected="false">
                                    <i class="bi-chat-left-text me-1" aria-hidden="true"></i> Discussion
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="notes-tab" data-bs-toggle="tab"
                                    data-bs-target="#notes-content" type="button" role="tab"
                                    aria-controls="notes-content" aria-selected="false">
                                    <i class="bi-journal-text me-1" aria-hidden="true"></i> Notes
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content Panels with accessibility improvements -->
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="lectures-content" role="tabpanel" aria-labelledby="lectures-tab">
                                <!-- Achievement Notification with improved design and accessibility -->
                                <div class="alert alert-warning d-flex align-items-center alert-dismissible fade show" role="alert"
                                    style="border-radius: var(--radius-md); background-color: var(--warning-soft); border-left: 4px solid var(--warning);">
                                    <i class="bi-trophy-fill text-warning fs-4 me-3" aria-hidden="true"></i>
                                    <div class="flex-grow-1">
                                        <strong>Achievement Unlocked: Python Pilot</strong>
                                        <span class="ms-3 text-muted">Complete your first 3 Python lectures</span>
                                    </div>
                                    <a href="#" class="btn btn-sm btn-outline-warning rounded-pill me-2">View All</a>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>

                                <!-- Learning Path Visual with better accessibility and collapsible section -->
                                <div class="card bg-light p-4 rounded-lg mb-4 mt-4">
                                    <div class="card-header bg-transparent border-0 p-0 d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="fw-bold mb-0" id="learning-path-heading">Learning Path:</h6>
                                        <div class="d-flex align-items-center">
                                            <small class="text-muted me-2">Your progress:</small>
                                            <span class="badge bg-primary rounded-pill px-3">4/7 Steps</span>
                                            <button class="btn btn-sm btn-link ms-2" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#learningPathDetail" aria-expanded="true"
                                                aria-controls="learningPathDetail" aria-label="Toggle learning path details">
                                                <i class="bi-chevron-down" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div id="learningPathDetail" class="collapse show" aria-labelledby="learning-path-heading">
                                        <div class="d-flex align-items-center position-relative p-4" style="border: 1px dashed #ccc; border-radius: var(--radius-md);">
                                            <!-- Container to ensure proper alignment -->
                                            <div class="d-flex align-items-center" style="width: 100%;" aria-label="Learning path progress visualization">
                                                <!-- Step 1 (completed) -->
                                                <div class="position-relative">
                                                    <div class="rounded-circle bg-success d-flex align-items-center justify-content-center"
                                                        style="width: 24px; height: 24px; z-index: 2;" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Course Introduction - Completed">
                                                    </div>
                                                    <div class="position-absolute" style="top: 30px; left: -25px; width: 75px; text-align: center;">
                                                        <span class="badge bg-success rounded-pill">Intro</span>
                                                    </div>
                                                </div>

                                                <!-- Line 1-2 with improved styling -->
                                                <div class="bg-success" style="height: 4px; flex-grow: 1; margin: 0;"></div>

                                                <!-- Step 2 (completed) -->
                                                <div class="position-relative">
                                                    <div class="rounded-circle bg-success d-flex align-items-center justify-content-center"
                                                        style="width: 24px; height: 24px; z-index: 2;" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Command Line - Completed">
                                                    </div>
                                                    <div class="position-absolute" style="top: 30px; left: -25px; width: 75px; text-align: center;">
                                                        <span class="badge bg-success rounded-pill">CLI</span>
                                                    </div>
                                                </div>

                                                <!-- Line 2-3 with improved styling -->
                                                <div class="bg-success" style="height: 4px; flex-grow: 1; margin: 0;"></div>

                                                <!-- Step 3 (completed) -->
                                                <div class="position-relative">
                                                    <div class="rounded-circle bg-success d-flex align-items-center justify-content-center"
                                                        style="width: 24px; height: 24px; z-index: 2;" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Installation - Completed">
                                                    </div>
                                                    <div class="position-absolute" style="top: 30px; left: -25px; width: 75px; text-align: center;">
                                                        <span class="badge bg-success rounded-pill">Install</span>
                                                    </div>
                                                </div>

                                                <!-- Line 3-4 with improved styling -->
                                                <div class="bg-primary" style="height: 4px; flex-grow: 1; margin: 0;"></div>

                                                <!-- Step 4 (current) with improved styling -->
                                                <div class="position-relative">
                                                    <div class="rounded-circle bg-primary border-2 border-white d-flex align-items-center justify-content-center"
                                                        style="width: 28px; height: 28px; z-index: 2;" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Jupyter Notebook - In Progress">
                                                        <i class="bi-play-fill text-white" aria-hidden="true"></i>
                                                    </div>
                                                    <div class="position-absolute" style="top: 30px; left: -25px; width: 75px; text-align: center;">
                                                        <span class="badge bg-primary rounded-pill">Jupyter</span>
                                                    </div>
                                                </div>

                                                <!-- Line 4-5 with improved styling -->
                                                <div class="bg-secondary-soft" style="height: 4px; flex-grow: 1; margin: 0;"></div>

                                                <!-- Step 5 (upcoming) with improved styling -->
                                                <div class="position-relative">
                                                    <div class="rounded-circle bg-light border border-secondary d-flex align-items-center justify-content-center"
                                                        style="width: 24px; height: 24px; z-index: 2;" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Python Packages - Not Started">
                                                    </div>
                                                    <div class="position-absolute" style="top: 30px; left: -25px; width: 75px; text-align: center;">
                                                        <span class="badge bg-light text-muted border border-secondary rounded-pill">Packages</span>
                                                    </div>
                                                </div>

                                                <!-- Line 5-6 with improved styling -->
                                                <div class="bg-secondary-soft" style="height: 4px; flex-grow: 1; margin: 0;"></div>

                                                <!-- Step 6 (upcoming) with improved styling -->
                                                <div class="position-relative">
                                                    <div class="rounded-circle bg-light border border-secondary d-flex align-items-center justify-content-center"
                                                        style="width: 24px; height: 24px; z-index: 2;" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Setup Quiz - Not Started">
                                                    </div>
                                                    <div class="position-absolute" style="top: 30px; left: -25px; width: 75px; text-align: center;">
                                                        <span class="badge bg-light text-muted border border-secondary rounded-pill">Quiz</span>
                                                    </div>
                                                </div>

                                                <!-- Line 6-7 with improved styling -->
                                                <div class="bg-secondary-soft" style="height: 4px; flex-grow: 1; margin: 0;"></div>

                                                <!-- Step 7 (upcoming) with improved styling -->
                                                <div class="position-relative">
                                                    <div class="rounded-circle bg-light border border-secondary d-flex align-items-center justify-content-center"
                                                        style="width: 24px; height: 24px; z-index: 2;" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Practice Project - Not Started">
                                                    </div>
                                                    <div class="position-absolute" style="top: 30px; left: -35px; width: 90px; text-align: center;">
                                                        <span class="badge bg-light text-muted border border-secondary rounded-pill">Project</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Next Steps Guidance with improved spacing -->
                                        <div class="d-flex align-items-center mt-4">
                                            <div class="small text-muted me-2">
                                                <i class="bi-info-circle me-1" aria-hidden="true"></i> Next milestone:
                                            </div>
                                            <div class="small fw-semibold">
                                                Complete Jupyter Notebook lessons to unlock Python Packages module
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Lecture List with improved accessibility -->
                                <div class="lecture-list" aria-label="Course lectures">
                                    <!-- Lecture 1 (Completed) with improved styling and accessibility -->
                                    <div class="card mb-3 border-0 shadow-sm" style="border-radius: var(--radius-md);">
                                        <div class="card-body p-3">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center"
                                                        style="width: 36px; height: 36px;" aria-hidden="true">
                                                        <i class="bi-check-lg"></i>
                                                    </div>
                                                </div>

                                                <div class="col">
                                                    <h5 id="lecture1-title" class="mb-1">Python 2 versus Python 3</h5>
                                                    <p class="small text-muted mb-0" id="lecture1-desc">
                                                        <i class="bi-camera-video me-1" aria-hidden="true"></i>
                                                        <span>Video • 06:39 min • Completed</span>
                                                    </p>
                                                </div>

                                                <div class="col-auto d-flex">
                                                    <a href="#" class="btn btn-sm btn-light rounded-pill px-3"
                                                        aria-labelledby="lecture1-title lecture1-desc">Review</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Lecture 2 (Completed with score) with improved styling and accessibility -->
                                    <div class="card mb-3 border-0 shadow-sm" style="border-radius: var(--radius-md);">
                                        <div class="card-body p-3">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center"
                                                        style="width: 36px; height: 36px;" aria-hidden="true">
                                                        <i class="bi-check-lg"></i>
                                                    </div>
                                                </div>

                                                <div class="col">
                                                    <h5 id="lecture2-title" class="mb-1">Command Line Basics</h5>
                                                    <p class="small text-muted mb-0" id="lecture2-desc">
                                                        <i class="bi-camera-video me-1" aria-hidden="true"></i>
                                                        <span>Video • 09:45 min • Completed</span>
                                                    </p>
                                                </div>

                                                <div class="col-auto d-flex align-items-center">
                                                    <div class="badge bg-success-soft text-success me-3 py-2 px-3">
                                                        <i class="bi-award me-1" aria-hidden="true"></i> <span>90%</span>
                                                    </div>
                                                    <a href="#" class="btn btn-sm btn-light rounded-pill px-3"
                                                        aria-labelledby="lecture2-title lecture2-desc">Review</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Lecture 3 (Completed with score) with improved styling and accessibility -->
                                    <div class="card mb-3 border-0 shadow-sm" style="border-radius: var(--radius-md);">
                                        <div class="card-body p-3">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center"
                                                        style="width: 36px; height: 36px;" aria-hidden="true">
                                                        <i class="bi-check-lg"></i>
                                                    </div>
                                                </div>

                                                <div class="col">
                                                    <h5 id="lecture3-title" class="mb-1">Installing Python (Windows)</h5>
                                                    <p class="small text-muted mb-0" id="lecture3-desc">
                                                        <i class="bi-camera-video me-1" aria-hidden="true"></i>
                                                        <span>Video • 11:30 min • Completed</span>
                                                    </p>
                                                </div>

                                                <div class="col-auto d-flex align-items-center">
                                                    <div class="badge bg-success-soft text-success me-3 py-2 px-3">
                                                        <i class="bi-award me-1" aria-hidden="true"></i> <span>95%</span>
                                                    </div>
                                                    <a href="#" class="btn btn-sm btn-light rounded-pill px-3"
                                                        aria-labelledby="lecture3-title lecture3-desc">Review</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Lecture 4 (In Progress) with improved styling and accessibility -->
                                    <div class="card mb-3 border-2 border-primary shadow-sm" style="border-radius: var(--radius-md);">
                                        <div class="card-body p-3">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                                        style="width: 36px; height: 36px;" aria-hidden="true">
                                                        <i class="bi-play-fill"></i>
                                                    </div>
                                                </div>

                                                <div class="col">
                                                    <h5 id="lecture4-title" class="mb-1 fw-bold">Jupyter Notebook Overview</h5>
                                                    <p class="small text-muted mb-0" id="lecture4-desc">
                                                        <i class="bi-camera-video me-1" aria-hidden="true"></i>
                                                        <span>Video • 15:20 min • In Progress (8:15 remaining)</span>
                                                    </p>
                                                </div>

                                                <div class="col-auto">
                                                    <a href="#" class="btn btn-primary rounded-pill px-4"
                                                        aria-labelledby="lecture4-title lecture4-desc"
                                                        aria-describedby="continue-desc">
                                                        Continue
                                                    </a>
                                                    <span id="continue-desc" class="visually-hidden">Continue watching Jupyter Notebook Overview lecture</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Lecture 5 (Not Started) with improved styling and accessibility -->
                                    <div class="card mb-3 border-0 shadow-sm" style="border-radius: var(--radius-md);">
                                        <div class="card-body p-3">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <div class="rounded-circle bg-light text-muted d-flex align-items-center justify-content-center"
                                                        style="width: 36px; height: 36px; border: 1px solid #ccc;" aria-hidden="true">
                                                        <i class="bi-circle"></i>
                                                    </div>
                                                </div>

                                                <div class="col">
                                                    <h5 id="lecture5-title" class="mb-1">Jupyter Notebook Installation</h5>
                                                    <p class="small text-muted mb-0" id="lecture5-desc">
                                                        <i class="bi-camera-video me-1" aria-hidden="true"></i>
                                                        <span>Video • 12:15 min • Not Started</span>
                                                    </p>
                                                </div>

                                                <div class="col-auto d-flex align-items-center">
                                                    <div class="badge bg-light text-muted border border-secondary me-3 py-2 px-3" aria-label="Quiz available for this lecture">
                                                        <i class="bi-pencil-square me-1" aria-hidden="true"></i> Quiz Available
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Continue Learning and Actions Row with improved spacing and accessibility -->
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div>
                                        <a href="#" class="btn btn-sm btn-outline-secondary me-2 rounded-pill px-3" aria-label="Save this course for later">
                                            <i class="bi-bookmark-plus me-1" aria-hidden="true"></i> Save for Later
                                        </a>
                                        <a href="#" class="btn btn-sm btn-outline-secondary rounded-pill px-3" aria-label="Download course materials">
                                            <i class="bi-download me-1" aria-hidden="true"></i> Download Materials
                                        </a>
                                    </div>

                                    <a href="#" class="btn btn-primary rounded-pill px-4" aria-label="Continue learning current lecture">
                                        <i class="bi-play-fill me-1" aria-hidden="true"></i> Continue Learning
                                    </a>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="resources-content" role="tabpanel" aria-labelledby="resources-tab">
                                <p class="text-muted">Resource content will appear here.</p>
                            </div>

                            <div class="tab-pane fade" id="quizzes-content" role="tabpanel" aria-labelledby="quizzes-tab">
                                <p class="text-muted">Quiz content will appear here.</p>
                            </div>

                            <div class="tab-pane fade" id="discussion-content" role="tabpanel" aria-labelledby="discussion-tab">
                                <p class="text-muted">Discussion forum will appear here.</p>
                            </div>

                            <div class="tab-pane fade" id="notes-content" role="tabpanel" aria-labelledby="notes-tab">
                                <p class="text-muted">Your course notes will appear here.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Weekly Progress Stats Card moved outside the columns for full width -->
        <div class="card shadow-sm mb-5">
            <div class="card-header bg-white d-flex justify-content-between align-items-center p-4 border-0">
                <h5 class="mb-0 fw-bold">Your Weekly Learning Stats</h5>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle rounded-pill px-3" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Select time period">
                        This Week
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li><a class="dropdown-item" href="#">This Week</a></li>
                        <li><a class="dropdown-item" href="#">Last Week</a></li>
                        <li><a class="dropdown-item" href="#">Last Month</a></li>
                    </ul>
                </div>
            </div>

            <div class="card-body p-4">
                <!-- Stats with improved card design and accessibility -->
                <div class="row g-3"> <!-- Increased gutter spacing -->
                    <div class="col-md-3">
                        <div class="card h-100 border-0 shadow-sm" style="border-radius: var(--radius-md);">
                            <div class="card-body p-4 bg-primary-soft text-center">
                                <h3 class="display-5 fw-bold text-primary mb-1" aria-labelledby="time-studied-label">3.5h</h3>
                                <p id="time-studied-label" class="mb-0">Time Studied</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card h-100 border-0 shadow-sm" style="border-radius: var(--radius-md);">
                            <div class="card-body p-4 bg-success-soft text-center">
                                <h3 class="display-5 fw-bold text-success mb-1" aria-labelledby="lessons-completed-label">5</h3>
                                <p id="lessons-completed-label" class="mb-0">Lessons Completed</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card h-100 border-0 shadow-sm" style="border-radius: var(--radius-md);">
                            <div class="card-body p-4 bg-info-soft text-center">
                                <h3 class="display-5 fw-bold text-info mb-1" aria-labelledby="quiz-average-label">92%</h3>
                                <p id="quiz-average-label" class="mb-0">Quiz Average</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card h-100 border-0 shadow-sm" style="border-radius: var(--radius-md);">
                            <div class="card-body p-4 bg-warning-soft text-center">
                                <h3 class="display-5 fw-bold text-warning mb-1" aria-labelledby="day-streak-label">5</h3>
                                <p id="day-streak-label" class="mb-0">Day Streak</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- View details button with better positioning -->
                <div class="d-flex justify-content-center mt-4">
                    <a href="#" class="btn btn-outline-primary rounded-pill px-4" aria-label="View detailed learning statistics">
                        <i class="bi-bar-chart-line me-1" aria-hidden="true"></i> View Detailed Statistics
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- Custom CSS with improved animations, styles, and design system variables -->
<style>
    /* Design System Variables */
    :root {
        /* Colors */
        --primary: #4361ee;
        --primary-dark: #3a56d4;
        --primary-soft: rgba(67, 97, 238, 0.1);
        --success: #2e9d4c;
        --success-soft: rgba(46, 157, 76, 0.1);
        --warning: #f5a623;
        --warning-soft: rgba(245, 166, 35, 0.1);
        --info: #17a2b8;
        --info-soft: rgba(23, 162, 184, 0.1);
        --danger: #dc3545;
        --danger-soft: rgba(220, 53, 69, 0.1);
        --secondary: #6c757d;
        --secondary-soft: rgba(108, 117, 125, 0.1);

        /* Spacing */
        --spacing-xs: 0.5rem;
        --spacing-sm: 1rem;
        --spacing-md: 1.5rem;
        --spacing-lg: 2rem;

        /* Border Radius */
        --radius-sm: 6px;
        --radius-md: 10px;
        --radius-lg: 16px;

        /* Shadows */
        --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
    }

    /* Accessibility Improvements */
    /* Skip to main content link */
    .visually-hidden-focusable:not(:focus):not(:focus-within) {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important;
    }

    /* Enhanced focus styles for accessibility */
    :focus-visible {
        outline: 3px solid var(--primary);
        outline-offset: 2px;
    }

    /* High contrast focus style for dark mode or high contrast mode */
    @media (prefers-contrast: high) {
        :focus-visible {
            outline: 3px solid #fff;
            outline-offset: 2px;
        }
    }

    /* Improved flame animation for streak badge */
    @keyframes flicker {
        0% {
            opacity: 0.8;
            transform: scale(1.0);
        }

        25% {
            opacity: 1;
            transform: scale(1.1);
        }

        50% {
            opacity: 0.8;
            transform: scale(1.0);
        }

        75% {
            opacity: 1;
            transform: scale(1.05);
        }

        100% {
            opacity: 0.8;
            transform: scale(1.0);
        }
    }

    .bi-fire {
        animation: flicker 1.5s infinite;
        transform-origin: center;
        display: inline-block;
        color: #FF9800;
        font-size: 1.1em;
    }

    /* Enhanced text colors */
    .text-primary {
        color: var(--primary) !important;
    }

    .text-success {
        color: var(--success) !important;
    }

    .text-warning {
        color: var(--warning) !important;
    }

    .text-info {
        color: var(--info) !important;
    }

    /* Standardized card styles */
    .card {
        border-radius: var(--radius-md);
        border: none;
        box-shadow: var(--shadow-sm);
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
    }

    /* Standardized padding */
    .card-body {
        padding: var(--spacing-md);
    }

    /* Consistent badges */
    .badge {
        padding: 0.5em 0.75em;
        border-radius: 30px;
        font-weight: 500;
    }

    /* Enhanced hover effects for interactive elements */
    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: var(--primary-dark);
        border-color: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 5px 10px rgba(67, 97, 238, 0.3);
    }

    .btn-outline-primary {
        color: var(--primary);
        border-color: var(--primary);
        transition: all 0.3s ease;
    }

    .btn-outline-primary:hover {
        color: #fff;
        background-color: var(--primary);
        border-color: var(--primary);
        transform: translateY(-2px);
        box-shadow: 0 5px 10px rgba(67, 97, 238, 0.2);
    }

    /* Module list item hover effects */
    #courseModules .card:hover {
        border-left: 3px solid var(--primary);
    }

    /* Progress bar animation */
    .progress-bar {
        transition: width 1.5s ease;
        animation: progress-animation 1.5s;
        background-color: var(--primary);
    }

    @keyframes progress-animation {
        0% {
            width: 0%;
        }
    }

    /* Better contrast for text colors to improve readability */
    .text-muted {
        color: #6c757d !important;
        /* Ensure WCAG AA compliance for text contrast */
    }

    /* Accessibility improvements for interactive elements */
    .nav-link {
        transition: all 0.3s ease;
        position: relative;
    }

    .nav-link.active {
        color: var(--primary) !important;
        font-weight: 600;
    }

    .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 100%;
        height: 2px;
        background-color: var(--primary);
    }

    /* Standardized section margins */
    section,
    .section {
        margin-bottom: var(--spacing-lg);
    }

    /* Improve tab panel focus and hover states */
    .nav-tabs .nav-link:focus,
    .nav-tabs .nav-link:hover {
        border-color: #e9ecef #e9ecef #dee2e6;
    }

    /* Animation for tab transition */
    .tab-pane.fade {
        transition: opacity 0.15s linear;
    }

    /* Animation for elements that enter viewport */
    .animate-in {
        animation: fadeInUp 0.5s ease forwards;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translate3d(0, 20px, 0);
        }

        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    /* Collapsible section animations */
    .collapse {
        transition: all 0.35s ease;
    }

    /* High contrast mode adjustments */
    @media (prefers-contrast: more) {
        :root {
            --primary: #0000ff;
            --success: #008000;
            --warning: #ff8c00;
            --info: #0078d4;
            --danger: #ff0000;
        }

        .bg-primary-soft,
        .bg-success-soft,
        .bg-warning-soft,
        .bg-info-soft,
        .bg-danger-soft,
        .bg-secondary-soft {
            background-color: #ffffff !important;
            border: 2px solid currentColor !important;
        }

        .text-muted {
            color: #000000 !important;
        }
    }

    /* Reduced motion preferences */
    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: 0.001ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.001ms !important;
        }
    }
</style>

<!-- Required JavaScript with modernized event handling and accessibility enhancements -->
<script>
    // Initialize Bootstrap components
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips with enhanced configuration
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                delay: {
                    show: 100,
                    hide: 100
                },
                animation: true,
                boundary: 'window'
            });
        });

        // Initialize popovers with enhanced configuration
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl, {
                trigger: 'hover focus',
                placement: 'auto'
            });
        });

        // Add smooth scrolling to all links with enhanced behavior
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();

                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);

                if (targetElement) {
                    // Smooth scroll with easing
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });

                    // Update URL without page reload
                    history.pushState(null, null, targetId);
                }
            });
        });

        // Add active class to current module
        const currentModule = document.querySelector('.card.border-primary');
        if (currentModule) {
            currentModule.classList.add('active-module');
        }

        // Animate stats on scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1
        });

        document.querySelectorAll('.card h3.display-5').forEach(item => {
            observer.observe(item);
        });

        // Ensure keyboard navigation works for custom components
        document.querySelectorAll('.card[role="button"]').forEach(card => {
            card.setAttribute('tabindex', '0');
            card.addEventListener('keydown', function(e) {
                // Activate on Enter or Space key
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    card.click();
                }
            });
        });

        // Announce progress updates to screen readers
        const progressUpdate = document.querySelector('.progress-update');
        if (progressUpdate) {
            // Use aria-live to announce progress changes
            progressUpdate.setAttribute('aria-live', 'polite');
        }

        // Make collapsible elements more accessible
        document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(collapseToggle => {
            // Check expanded state on page load
            const targetId = collapseToggle.getAttribute('data-bs-target') ||
                collapseToggle.getAttribute('href');
            const targetElement = document.querySelector(targetId);

            if (targetElement) {
                const isExpanded = targetElement.classList.contains('show');
                collapseToggle.setAttribute('aria-expanded', isExpanded.toString());

                // Update icon based on state
                const icon = collapseToggle.querySelector('.bi-chevron-down, .bi-chevron-up');
                if (icon) {
                    icon.classList.toggle('bi-chevron-up', isExpanded);
                    icon.classList.toggle('bi-chevron-down', !isExpanded);
                }

                // Listen for bootstrap collapse events to update ARIA and icons
                targetElement.addEventListener('shown.bs.collapse', function() {
                    collapseToggle.setAttribute('aria-expanded', 'true');
                    if (icon) {
                        icon.classList.remove('bi-chevron-down');
                        icon.classList.add('bi-chevron-up');
                    }
                });

                targetElement.addEventListener('hidden.bs.collapse', function() {
                    collapseToggle.setAttribute('aria-expanded', 'false');
                    if (icon) {
                        icon.classList.remove('bi-chevron-up');
                        icon.classList.add('bi-chevron-down');
                    }
                });
            }
        });
    });
</script>

<?php include '../includes/student-footer.php'; ?>