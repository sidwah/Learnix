<?php include '../includes/student-header.php'; ?>


<!-- Main Content -->
<main id="content" role="main">
    <!-- Achievement Notification Banner -->

    <!-- Course Header with Background Image -->
    <div class="position-relative">

    </div>
    <!-- Breadcrumb Navigation -->
    <nav aria-label="breadcrumb" class="bg-light py-2 border-bottom">
        <div class="container-fluid">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="enrolled-courses.php" class="text-decoration-none">My Courses</a></li>
                <li class="breadcrumb-item"><a href="course-overview.php?course=python-bootcamp" class="text-decoration-none">Complete Python Bootcamp</a></li>
                <li class="breadcrumb-item"><a href="course-section.php?section=python-setup" class="text-decoration-none">Python Setup</a></li>
                <li class="breadcrumb-item active" aria-current="page">Jupyter Notebook Overview</li>
            </ol>
        </div>
    </nav>

    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center mx-4 mt-3" role="alert">
        <div class="d-flex align-items-center">
            <span class="badge bg-warning text-dark me-3">
                <i class="bi-trophy-fill"></i>
            </span>
            <span>
                <strong>You're making great progress!</strong> 3 more lectures to complete the Python Setup module.
            </span>
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <div class="row">
        <!-- Left Sidebar -->
        <div class="col-md-3">
            <div class="p-3">
                <!-- Course Navigation Card -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Course Navigation</span>
                        <span class="badge bg-light text-primary rounded-pill">4/12</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="bi-check-circle text-success me-2"></i>
                                    Course Overview
                                </span>
                                <span class="badge bg-success rounded-pill">100%</span>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center active">
                                <span>
                                    <i class="bi-play-circle text-white me-2"></i>
                                    Python Setup
                                </span>
                                <span class="badge bg-light text-primary rounded-pill">75%</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Lecture Details Card -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-info text-white">
                        <i class="bi-list-ol me-2"></i> Lecture Contents
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                <i class="bi-check-circle text-success me-2"></i>
                                Python 2 vs Python 3
                            </span>
                            <span class="badge bg-success rounded-pill">Completed</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                <i class="bi-check-circle text-success me-2"></i>
                                Command Line Basics
                            </span>
                            <span class="badge bg-success rounded-pill">Completed</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                <i class="bi-check-circle text-success me-2"></i>
                                Installing Python
                            </span>
                            <span class="badge bg-success rounded-pill">Completed</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center active">
                            <span>
                                <i class="bi-play-circle text-white me-2"></i>
                                Jupyter Notebook Overview
                            </span>
                            <span class="badge bg-light text-primary rounded-pill">In Progress</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center text-muted">
                            <span>
                                <i class="bi-lock text-muted me-2"></i>
                                Jupyter Notebook Installation
                            </span>
                            <span class="badge bg-secondary rounded-pill">Locked</span>
                        </li>
                    </ul>
                </div>

                <!-- Progress Tracking Card -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-warning">
                        <i class="bi-graph-up me-2"></i> Learning Progress
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Section Progress</span>
                                <span class="text-muted">4/6 Lectures</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: 65%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Overall Course</span>
                                <span class="text-muted">35% Complete</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: 35%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quiz Performance Card -->
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <i class="bi-award me-2"></i> Quiz Performance
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>Average Score</span>
                            <h4 class="mb-0 text-success">92%</h4>
                        </div>
                        <div class="progress mb-3" style="height: 10px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 92%"></div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Quizzes Completed</span>
                            <span class="text-muted">2 of 8</span>
                        </div>
                        <button class="btn btn-success w-100 mt-3">
                            <i class="bi-list-check me-2"></i> View Quiz Results
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="col-md-9">
            <!-- Lecture Header Card -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h4 mb-1">Jupyter Notebook Overview</h2>
                        <p class="text-muted mb-0">Lecture 4 of 12 • Duration: 15:20 min</p>
                    </div>
                    <div class="text-end">
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-warning me-2">
                                <i class="bi-star-fill"></i>
                                <i class="bi-star-fill"></i>
                                <i class="bi-star-fill"></i>
                                <i class="bi-star-fill"></i>
                                <i class="bi-star-half"></i>
                            </span>
                            <span class="small text-muted">(4.5)</span>
                        </div>
                        <div class="btn-group" role="group">
                            <a href="#" class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="Ask Instructor">
                                <i class="bi-chat-left-text"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="Instructor Profile">
                                <i class="bi-person"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body bg-light">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <img src="../assets/img/160x160/img10.jpg" class="avatar avatar-md rounded-circle" alt="Instructor">
                                </div>
                                <div>
                                    <h6 class="mb-1">Christina Kray</h6>
                                    <p class="text-muted small mb-0">Lead Python Instructor</p>
                                </div>
                            </div>
                        </div

                            <!-- Rest of the content remains the same as in the previous version -->
                        <div class="video-container position-relative bg-dark">
                            <!-- Placeholder for actual video -->
                            <div class="d-flex justify-content-center align-items-center" style="height: 500px;">
                                <div class="text-center text-white">
                                    <i class="bi-play-circle display-1 mb-3"></i>
                                    <h3>Jupyter Notebook Video</h3>
                                    <p>Click to play the lecture video</p>
                                </div>
                            </div>
                        </div>

                        <!-- Video Controls Placeholder -->
                        <div class="video-controls bg-dark p-3 d-flex align-items-center">
                            <button class="btn btn-link text-white me-3">
                                <i class="bi-play-fill"></i>
                            </button>
                            <div class="flex-grow-1">
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar" role="progressbar" style="width: 35%"></div>
                                </div>
                            </div>
                            <span class="text-white ms-3">05:34 / 15:20</span>
                        </div>

                        <!-- Tabs Navigation -->
                        <nav class="nav nav-tabs nav-fill bg-white">
                            <a class="nav-link active" href="#description" data-bs-toggle="tab">Description</a>
                            <a class="nav-link" href="#transcript" data-bs-toggle="tab">Transcript</a>
                            <a class="nav-link" href="#resources" data-bs-toggle="tab">Resources</a>
                            <a class="nav-link" href="#discussion" data-bs-toggle="tab">Discussion</a>
                            <a class="nav-link" href="#notes" data-bs-toggle="tab">Notes</a>
                        </nav>

                        <!-- Tab Content -->
                        <div class="tab-content">
                            <div class="tab-pane fade show active p-4" id="description">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h4 class="mb-3">About this lecture</h4>
                                        <p>This lecture introduces Jupyter Notebook, an open-source web application that allows you to create and share documents that contain live code, equations, visualizations, and narrative text. Perfect for data analysis and machine learning projects.</p>
                                        <p>By the end of this lecture, you'll understand how to create, edit, and run code cells in Jupyter Notebook, as well as how to use markdown for documentation and explanations alongside your code.</p>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card h-100 border-primary">
                                            <div class="card-header bg-primary text-white">
                                                <i class="bi-info-circle me-2"></i> Lecture Highlights
                                            </div>
                                            <div class="card-body">
                                                <ul class="list-unstyled">
                                                    <li class="mb-2">
                                                        <i class="bi-check-circle text-success me-2"></i> Jupyter Notebook Basics
                                                    </li>
                                                    <li class="mb-2">
                                                        <i class="bi-code-slash text-primary me-2"></i> Live Code Cells
                                                    </li>
                                                    <li class="mb-2">
                                                        <i class="bi-markdown text-info me-2"></i> Markdown Documentation
                                                    </li>
                                                    <li>
                                                        <i class="bi-graph-up text-warning me-2"></i> Data Visualization
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-4">
                                        <div class="card h-100 hover-lift">
                                            <div class="card-body">
                                                <h5 class="card-title">
                                                    <i class="bi-book text-primary me-2"></i> Prerequisites
                                                </h5>
                                                <p class="card-text">Basic understanding of Python syntax and programming concepts is recommended.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card h-100 hover-lift">
                                            <div class="card-body">
                                                <h5 class="card-title">
                                                    <i class="bi-tools text-success me-2"></i> Tools You'll Need
                                                </h5>
                                                <ul class="list-unstyled">
                                                    <li>Python 3.x</li>
                                                    <li>Anaconda Distribution (Recommended)</li>
                                                    <li>Internet Connection</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card h-100 hover-lift">
                                            <div class="card-body">
                                                <h5 class="card-title">
                                                    <i class="bi-patch-question text-warning me-2"></i> Learning Outcomes
                                                </h5>
                                                <ul class="list-unstyled">
                                                    <li>Create Jupyter Notebooks</li>
                                                    <li>Use Code and Markdown Cells</li>
                                                    <li>Run and Share Notebooks</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade p-4" id="transcript">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Lecture Transcript</h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Transcript content will be added here. This will include a detailed text version of the lecture for accessibility and review purposes.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade p-4" id="resources">
                                <div class="row ">
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-header bg-primary text-white">
                                                <i class="bi-file-earmark-text me-2"></i> Lecture Materials
                                            </div>
                                            <div class="card-body">
                                                <ul class="list-unstyled">
                                                    <li class="mb-2">
                                                        <a href="#" class="text-decoration-none">
                                                            <i class="bi-file-pdf text-danger me-2"></i> Lecture Slides (PDF)
                                                        </a>
                                                    </li>
                                                    <li class="mb-2">
                                                        <a href="#" class="text-decoration-none">
                                                            <i class="bi-code-slash text-primary me-2"></i> Sample Notebook
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#" class="text-decoration-none">
                                                            <i class="bi-download text-success me-2"></i> Resource Pack
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-header bg-success text-white">
                                                <i class="bi-link-45deg me-2"></i> External Resources
                                            </div>
                                            <div class="card-body">
                                                <ul class="list-unstyled">
                                                    <li class="mb-2">
                                                        <a href="#" class="text-decoration-none">
                                                            <i class="bi-book text-primary me-2"></i> Jupyter Notebook Documentation
                                                        </a>
                                                    </li>
                                                    <li class="mb-2">
                                                        <a href="#" class="text-decoration-none">
                                                            <i class="bi-youtube text-danger me-2"></i> Additional Video Tutorials
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#" class="text-decoration-none">
                                                            <i class="bi-globe text-info me-2"></i> Online Jupyter Notebook
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade p-4" id="discussion">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Course Discussion</h5>
                                        <button class="btn btn-sm btn-primary">
                                            <i class="bi-plus me-2"></i>Start a Discussion
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted">No discussions have been started for this lecture yet. Be the first to ask a question!</p>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade p-4" id="notes">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Your Notes</h5>
                                        <button class="btn btn-sm btn-success">
                                            <i class="bi-save me-2"></i>Save Notes
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="form-control" rows="6" placeholder="Take your notes here..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Controls -->
                        <div class="d-flex justify-content-between p-4 bg-white">
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
    </div>
</main>

<?php include '../includes/student-footer.php'; ?>

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