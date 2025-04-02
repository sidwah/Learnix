<?php include '../includes/admin-header.php'; ?>
<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main">
    <!-- Navbar -->
    <nav class="js-nav-scroller navbar navbar-expand-lg navbar-sidebar navbar-vertical navbar-light bg-white border-end" data-hs-nav-scroller-options='{
            "type": "vertical",
            "target": ".navbar-nav .active",
            "offset": 80
           }'>

        <?php include '../includes/admin-sidebar.php'; ?>
    </nav>


    <!-- Content -->
    <div class="navbar-sidebar-aside-content content-space-t-3 content-space-b-2 px-lg-5 px-xl-10">
        <div class="row justify-content-md-between align-items-md-center mb-10">
            <div class="col-md-6 col-xl-5">
                <div class="mb-4">
                    <h1 class="display-5 mb-3">Manage <span class="text-primary text-highlight-warning">Learnix</span> with ease.</h1>
                    <p class="lead">A simple and powerful dashboard for managing users, courses, and reports.</p>
                </div>
            </div>
            <!-- End Col -->

            <div class="col-md-6 col-xl-6">
                <img class="img-fluid" src="../assets/svg/illustrations/oc-building-apps.svg" alt="Admin Dashboard Image">
            </div>
            <!-- End Col -->
        </div>
        <!-- End Row -->

        <?php
        include '../backend/config.php';

        // Query to fetch the counts for each category
        $query = "SELECT 
              (SELECT COUNT(*) FROM users ) AS user_count,
              (SELECT COUNT(*) FROM courses) AS course_count,
              (SELECT COUNT(*) FROM categories) AS categories_count,
              (SELECT COUNT(*) FROM subcategories) AS subcategories_count,
              (SELECT COUNT(*) FROM issue_reports) AS issues,
              (SELECT COUNT(*) FROM chatbot_responses) AS chatbot_responses,
              (SELECT COUNT(*) FROM users WHERE role = 'student') AS students,
              (SELECT COUNT(*) FROM issue_reports WHERE status = 'pending') AS recent_reports,
              (SELECT COUNT(*) FROM users WHERE role = 'instructor') AS instructors,
              (SELECT COUNT(*) FROM users WHERE role = 'admin') AS admins";

        // Execute the query
        $result = mysqli_query($conn, $query);

        // Check if the query was successful and fetch the data
        if ($result) {
            $data = mysqli_fetch_assoc($result);
            $users_count = $data['user_count'];
            $course_count = $data['course_count'];
            $categories_count = $data['categories_count'];
            $subcategories_count = $data['subcategories_count'];
            $complaints_count = $data['issues'];
            $chatbot_interactions = $data['chatbot_responses'];
            $students = $data['students'];
            $instructors = $data['instructors'];
            $admins = $data['admins'];
            $recent_reports = $data['recent_reports'];
        } else {
            // Handle the error if the query fails
            die("Error executing query: " . mysqli_error($conn));
        }


        // Sample numbers for dashboard statistics
        // $user_count = 1200;
        // $instructor_count = 75;
        $enrollment_count = 0;


        

        // Sample numbers for course management statistics
        $approved_courses = 0;
        $pending_courses = 0;
        $rejected_courses = 0;


        // Sample numbers for quiz & performance analytics
        $total_quizzes = 0;
        $most_attempted_quiz = "Intro to Programming";
        $average_quiz_score = 0;



        // Sample numbers for system notifications & reports
        // $recent_reports = 12;
        $recent_logs = 0;

        // Sample numbers for help center & chatbot stats
        $open_tickets = 0;
        $resolved_tickets = 0;

        ?>


        <hr class="mb-5">

        <!-- Dashboard Summary -->
        <div class="row">
            <!-- Card for Dashboard Summary Title -->
            <div class="col-sm-3 mb-5 mb-sm-0">
                <h4>Dashboard Summary</h4>
            </div>

            <div class="col-sm-9">
                <div class="row row-cols-sm-2 row-cols-md-3">
                    <!-- Total Users Card -->
                    <div class="col mb-4">
                        <a class="card card-sm card-transition h-100" href="#" data-aos="fade-up">
                            <div class="card-body text-center">
                                <h5 class="card-title text-inherit">Total Students</h5>
                                <h1 class="card-text large text-body"><?php echo number_format($students); ?></h1>
                            </div>
                        </a>
                    </div>

                    <!-- Total Instructors Card -->
                    <div class="col mb-4">
                        <a class="card card-sm card-transition h-100" href="#" data-aos="fade-up">
                            <div class="card-body text-center">
                                <h5 class="card-title text-inherit">Total Instructors</h5>
                                <h1 class="card-text large text-body"><?php echo number_format($instructors); ?></h1>
                            </div>
                        </a>
                    </div>

                    <!-- Total Courses Card -->
                    <div class="col mb-4">
                        <a class="card card-sm card-transition h-100" href="#" data-aos="fade-up">
                            <div class="card-body text-center">
                                <h5 class="card-title text-inherit">Total Courses</h5>
                                <h1 class="card-text large text-body"><?php echo number_format($course_count); ?></h1>
                            </div>
                        </a>
                    </div>

                    <!-- Total Enrollments Card -->
                    <div class="col mb-4">
                        <a class="card card-sm card-transition h-100" href="#" data-aos="fade-up">
                            <div class="card-body text-center">
                                <h5 class="card-title text-inherit">Total Enrollments</h5>
                                <h1 class="card-text large text-body"><?php echo number_format($enrollment_count); ?></h1>
                            </div>
                        </a>
                    </div>

                    <!-- Total Complaints Card -->
                    <div class="col mb-4">
                        <a class="card card-sm card-transition h-100" href="#" data-aos="fade-up">
                            <div class="card-body text-center">
                                <h5 class="card-title text-inherit">Total Complaints</h5>
                                <h1 class="card-text large text-body"><?php echo number_format($complaints_count); ?></h1>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <hr class="mb-5">

        <!-- User Management -->
        <div class="row">
            <!-- Card for User Management Title -->
            <div class="col-sm-3 mb-5 mb-sm-0">
                <h4>User Management</h4>
            </div>

            <div class="col-sm-9">
                <div class="row row-cols-sm-2 row-cols-md-3">
                    <!-- Active & Inactive Students Card -->
                    <div class="col mb-4">
                        <a class="card card-sm card-transition h-100" href="#" data-aos="fade-up">
                            <div class="card-body text-center">
                                <h5 class="card-title text-inherit">Students</h5>
                                <h1 class="card-text large text-body"> <?php echo number_format($students); ?></h1>
                            </div>
                        </a>
                    </div>

                    <!-- Active & Pending Instructors Card -->
                    <div class="col mb-4">
                        <a class="card card-sm card-transition h-100" href="#" data-aos="fade-up">
                            <div class="card-body text-center">
                                <h5 class="card-title text-inherit">Instructors</h5>
                                <h1 class="card-text large text-body"> <?php echo number_format($instructors); ?></h1>
                            </div>
                        </a>
                    </div>

                    <!-- Super Admins & Staff Admins Card -->
                    <div class="col mb-4">
                        <a class="card card-sm card-transition h-100" href="#" data-aos="fade-up">
                            <div class="card-body text-center">
                                <h5 class="card-title text-inherit">Admins</h5>
                                <h1 class="card-text large text-body"><?php echo number_format($admins); ?></h1>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Row -->

        <hr class="mb-5">

        <!-- Course Management -->
        <div class="row mt-5">
            <!-- Card for Course Management Title -->
            <div class="col-sm-3 mb-5 mb-sm-0">
                <h4>Course Management</h4>
            </div>

            <div class="col-sm-9">
                <div class="row row-cols-sm-2 row-cols-md-3">
                    <!-- Approved Courses Card -->
                    <div class="col mb-4">
                        <a class="card card-sm card-transition h-100" href="#" data-aos="fade-up">
                            <div class="card-body text-center">
                                <h5 class="card-title text-inherit">Approved Courses</h5>
                                <h1 class="card-text large text-body"><?php echo number_format($approved_courses); ?></h1>
                            </div>
                        </a>
                    </div>

                    <!-- Pending Approval Courses Card -->
                    <div class="col mb-4">
                        <a class="card card-sm card-transition h-100" href="#" data-aos="fade-up">
                            <div class="card-body text-center">
                                <h5 class="card-title text-inherit">Pending Approval</h5>
                                <h1 class="card-text large text-body"><?php echo number_format($pending_courses); ?></h1>
                            </div>
                        </a>
                    </div>

                    <!-- Rejected Courses Card -->
                    <div class="col mb-4">
                        <a class="card card-sm card-transition h-100" href="#" data-aos="fade-up">
                            <div class="card-body text-center">
                                <h5 class="card-title text-inherit">Rejected Courses</h5>
                                <h1 class="card-text large text-body"><?php echo number_format($rejected_courses); ?></h1>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Row -->

        <hr class="mb-5">

        <!-- Quiz & Performance Analytics -->
        <div class="row mt-5">
            <!-- Card for Quiz & Performance Analytics Title -->
            <div class="col-sm-3 mb-5 mb-sm-0">
                <h4>Quiz & Performance Analytics</h4>
            </div>

            <div class="col-sm-9">
                <div class="row row-cols-sm-2 row-cols-md-3">
                    <!--  Total Quizzes Created Card -->
                    <div class="col mb-4">
                        <a class="card card-sm card-transition h-100" href="#" data-aos="fade-up">
                            <div class="card-body text-center">
                                <h5 class="card-title text-inherit"> Total Quizzes Created</h5>
                                <h1 class="card-text large text-body"><?php echo number_format($total_quizzes); ?></h1>
                            </div>
                        </a>
                    </div>

                    <!-- Most Attempted Quiz Card -->
                    <div class="col mb-4">
                        <a class="card card-sm card-transition h-100" href="#" data-aos="fade-up">
                            <div class="card-body text-center">
                                <h5 class="card-title text-inherit">Most Attempted Quiz</h5>
                                <h1 class="card-text large text-body"><?php echo ($most_attempted_quiz); ?></h1>
                            </div>
                        </a>
                    </div>

                    <!-- Average Quiz Score Card -->
                    <div class="col mb-4">
                        <a class="card card-sm card-transition h-100" href="#" data-aos="fade-up">
                            <div class="card-body text-center">
                                <h5 class="card-title text-inherit">Average Quiz Score</h5>
                                <h1 class="card-text large text-body"><?php echo number_format($average_quiz_score); ?></h1>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Row -->

        <hr class="mb-5">

        <!-- Category Management -->
        <div class="row mt-5">
            <!-- Card for Category Management Title -->
            <div class="col-sm-3 mb-5 mb-sm-0">
                <h4>Category Management</h4>
            </div>

            <div class="col-sm-9">
                <div class="row row-cols-sm-2">
                    <!--  Total QTotal Categories Card -->
                    <div class="col mb-4">
                        <a class="card card-sm card-transition h-100" href="#" data-aos="fade-up">
                            <div class="card-body text-center">
                                <h5 class="card-title text-inherit"> Total Categories</h5>
                                <h1 class="card-text large text-body"><?php echo number_format($categories_count); ?></h1>
                            </div>
                        </a>
                    </div>

                    <!-- Total Active Categories Card -->
                    <div class="col mb-4">
                        <a class="card card-sm card-transition h-100" href="#" data-aos="fade-up">
                            <div class="card-body text-center">
                                <h5 class="card-title text-inherit">Total Subcategories</h5>
                                <h1 class="card-text large text-body"><?php echo number_format($subcategories_count); ?></h1>
                            </div>
                        </a>
                    </div>

                
                </div>
            </div>
        </div>
        <!-- End Row -->

        <hr class="mb-5">


        <!-- System Notifications & Reports -->
        <div class="row mt-5">
            <!-- Card for System Notifications & Reports Title -->
            <div class="col-sm-3 mb-5 mb-sm-0">
                <h4>System Notifications & Reports</h4>
            </div>

            <div class="col-sm-9">
                <div class="row row-cols-sm-2">
                    <!-- Recent Reports Card -->
                    <div class="col mb-4">
                        <a class="card card-sm card-transition h-100" href="#" data-aos="fade-up">
                            <div class="card-body text-center">
                                <h5 class="card-title text-inherit">Recent Reports/Complaints</h5>
                                <h1 class="card-text large text-body"><?php echo number_format($recent_reports); ?></h1>
                            </div>
                        </a>
                    </div>

                    <!-- Recent System Logs Card -->
                    <div class="col mb-4">
                        <a class="card card-sm card-transition h-100" href="#" data-aos="fade-up">
                            <div class="card-body text-center">
                                <h5 class="card-title text-inherit">Recent System Logs</h5>
                                <h1 class="card-text large text-body"><?php echo number_format($recent_logs); ?></h1>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>


        <hr class="mb-5">

        <div class="row mt-5">
            <!-- Card for Help Center & Chatbot Stats Title -->
            <div class="col-sm-3 mb-5 mb-sm-0">
                <h4>Help Center & Chatbot Stats</h4>
            </div>

            <div class="col-sm-9">
                <div class="row row-cols-sm-2 row-cols-md-3">
                    <!-- Total  Reports Card -->
                    <div class="col mb-4">
                        <a class="card card-sm card-transition h-100" href="#" data-aos="fade-up">
                            <div class="card-body text-center">
                                <h5 class="card-title text-inherit">Total Reports</h5>
                                <h1 class="card-text large text-body"> <?php echo number_format($open_tickets); ?></h1>
                            </div>
                        </a>
                    </div>

                    <!-- Total Resolved Tickets Card -->
                    <div class="col mb-4">
                        <a class="card card-sm card-transition h-100" href="#" data-aos="fade-up">
                            <div class="card-body text-center">
                                <h5 class="card-title text-inherit">Total Resolved Issues</h5>
                                <h1 class="card-text large text-body"> <?php echo number_format($resolved_tickets); ?></h1>
                            </div>
                        </a>
                    </div>

                    <!-- Chatbot Interactions Card -->
                    <div class="col mb-4">
                        <a class="card card-sm card-transition h-100" href="#" data-aos="fade-up">
                            <div class="card-body text-center">
                                <h5 class="card-title text-inherit">Chatbot Interactions</h5>
                                <h1 class="card-text large text-body"><?php echo number_format($chatbot_interactions); ?></h1>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>


    </div>
    <!-- End Content -->
</main>
<!-- ========== END MAIN CONTENT ========== -->