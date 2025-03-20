<?php
require '../backend/session_start.php'; // Ensure session is started
require '../backend/config.php'; // Ensure connection file is correct

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    // Log unauthorized access attempt for security auditing
    error_log("Unauthorized access attempt detected: " . json_encode($_SERVER));

    // Redirect unauthorized users to a custom unauthorized access page or login page
    header('Location: landing.php');
    exit;
}
?>



<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="utf-8" />
    <title>Instructor | Learnix - Empowering Education</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="An intuitive instructor dashboard to manage courses, track student progress, and enhance the learning experience." name="description" />
    <meta content="Learnix Development Team" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- third party css -->
    <link href="assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <!-- third party css end -->

    <!-- App css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <style>
        .badge {
            padding: 5px 10px;
            color: #fff;
            font-size: 0.875em;
            border-radius: 5px;
            text-align: center;
        }

        .badge-draft {
            background-color: #f0ad4e;
            /* Bootstrap warning color */
        }

        .badge-published {
            background-color: #5cb85c;
            /* Bootstrap success color */
        }

        .badge-pending {
            background-color: #d9534f;
            /* Bootstrap danger color */
        }
    </style>

</head>

<body class="loading" data-layout-color="light" data-leftbar-theme="dark" data-layout-mode="fluid" data-rightbar-onstart="true">
    <!-- Begin page -->
    <div class="wrapper">
        <!-- ========== Left Sidebar Start ========== -->
        <?php
        include '../includes/instructor-sidebar.php';
        ?>

        <!-- Left Sidebar End -->

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="content-page">
            <div class="content">
                <!-- Topbar Start -->
                <?php
                include '../includes/instructor-topnavbar.php';
                ?>
                <!-- end Topbar -->

                <!-- Start Content-->
                <div class="container-fluid">

                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Learnix</a></li>
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Instructor</a></li>
                                        <li class="breadcrumb-item active">Dashboard</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Dashboard</h4>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    <?php

                    // Assuming database connection is already established
                    if (!isset($_SESSION['user_id'])) {
                        die("User not logged in.");
                    }
                    $user_id = $_SESSION['user_id'];

                    // Fetch instructor ID using user ID
                    $query = "SELECT instructor_id FROM instructors WHERE user_id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $stmt->store_result();

                    if ($stmt->num_rows > 0) {
                        $stmt->bind_result($instructor_id);
                        $stmt->fetch();
                        $stmt->close();
                    } else {
                        die("Instructor not found.");
                    }

                    // Fetch Total Courses
                    $total_courses_sql = "SELECT COUNT(*) AS total_courses FROM courses WHERE instructor_id = '$instructor_id'"; // Change instructor_id dynamically
                    $total_courses = $conn->query($total_courses_sql)->fetch_assoc()['total_courses'];

                    // Fetch Total Students
                    // $total_students_sql = "SELECT COUNT(*) AS total_students FROM users WHERE role = 'student'";
                    // $total_students = $conn->query($total_students_sql)->fetch_assoc()['total_students'];

                    // Fetch Active Courses
                    $active_courses_sql = "SELECT COUNT(*) AS active_courses FROM courses WHERE status = 'published' AND instructor_id = $instructor_id"; // Change dynamically
                    $active_courses = $conn->query($active_courses_sql)->fetch_assoc()['active_courses'];

                    // Performance Rating (Placeholder)
                    $performance_rating = 0.0; // You can define logic for this
                    $total_students = 0;

                    $conn->close();
                    ?>

                    <div class="row">
                        <div class="col-12">
                            <div class="card widget-inline">
                                <div class="card-body p-0">
                                    <div class="row g-0">
                                        <div class="col-sm-6 col-lg-3">
                                            <div class="card shadow-none m-0">
                                                <div class="card-body text-center">
                                                    <i class="dripicons-view-list  text-muted" style="font-size: 24px;"></i>
                                                    <h3><span><?php echo $total_courses; ?></span></h3>
                                                    <p class="text-muted font-15 mb-0">Total Courses</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-6 col-lg-3">
                                            <div class="card shadow-none m-0 border-start">
                                                <div class="card-body text-center">
                                                    <i class="dripicons-user text-muted" style="font-size: 24px;"></i>
                                                    <h3><span><?php echo $total_students; ?></span></h3>
                                                    <p class="text-muted font-15 mb-0">Total Students</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-6 col-lg-3">
                                            <div class="card shadow-none m-0 border-start">
                                                <div class="card-body text-center">
                                                    <i class="dripicons-graduation text-muted" style="font-size: 24px;"></i>
                                                    <h3><span><?php echo $active_courses; ?></span></h3>
                                                    <p class="text-muted font-15 mb-0">Active Courses</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-6 col-lg-3">
                                            <div class="card shadow-none m-0 border-start">
                                                <div class="card-body text-center">
                                                    <i class="dripicons-star text-muted" style="font-size: 24px;"></i>
                                                    <h3><span><?php echo $performance_rating; ?></span> <i class="mdi mdi-arrow-up text-success"></i></h3>
                                                    <p class="text-muted font-15 mb-0">Performance Rating</p>
                                                </div>
                                            </div>
                                        </div>

                                    </div> <!-- end row -->
                                </div>
                            </div> <!-- end card-box-->
                        </div> <!-- end col-->
                    </div>
                    <!-- end row-->



                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card">
                            <div class="card-body">
    <div class="d-flex justify-content-between align-items-center">
        <h4 class="header-title">Course Progress Overview</h4>
        <div class="dropdown">
            <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="mdi mdi-dots-vertical"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-end">
                <a href="javascript:void(0);" class="dropdown-item">View Course Analytics</a>
                <a href="javascript:void(0);" class="dropdown-item">Export Report</a>
                <a href="javascript:void(0);" class="dropdown-item">Manage Courses</a>
                <a href="javascript:void(0);" class="dropdown-item">Settings</a>
            </div>
        </div>
    </div>

    <div id="chart-container" class="mt-3 mb-4 chartjs-chart" style="height: 330px;">
        <canvas id="course-status-chart"></canvas>
        <div id="no-courses-message" class="text-center d-none">
            <i class="mdi mdi-information-outline text-info h1 mb-3"></i>
            <h4 class="text-muted">No Courses Available</h4>
            <p class="text-muted">You haven't created any courses yet. Start by creating your first course!</p>
            <a href="create-course.php" class="btn btn-primary mt-2">Create New Course</a>
        </div>
    </div>

    <div class="row text-center mt-2 py-2">
        <div class="col-sm-4">
            <div class="my-2 my-sm-0">
                <i class="mdi mdi-book-check text-success mt-3 h3" aria-label="Published"></i>
                <h3 class="fw-normal">
                    <span id="published-count">0</span>
                </h3>
                <p class="text-muted mb-0">Published</p>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="my-2 my-sm-0">
                <i class="mdi mdi-pencil text-primary mt-3 h3" aria-label="Draft"></i>
                <h3 class="fw-normal">
                    <span id="draft-count">0</span>
                </h3>
                <p class="text-muted mb-0">Draft</p>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="my-2 my-sm-0">
                <i class="mdi mdi-timer-sand text-danger mt-3 h3" aria-label="Pending Approval"></i>
                <h3 class="fw-normal">
                    <span id="pending-count">0</span>
                </h3>
                <p class="text-muted mb-0">Pending Approval</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            fetch("http://localhost:8888/Learnix/backend/courses/get_course_status.php")
                .then(response => response.text())
                .then(text => {
                    console.log("Raw Response:", text);
                    try {
                        let data = JSON.parse(text);
                        console.log("Parsed JSON:", data);
                        if (data.error) {
                            console.error("API Error:", data.error);
                            return;
                        }

                        // Update counts
                        const publishedCount = data.published || 0;
                        const draftCount = data.draft || 0;
                        const pendingCount = data.pending || 0;

                        document.getElementById("published-count").textContent = publishedCount;
                        document.getElementById("draft-count").textContent = draftCount;
                        document.getElementById("pending-count").textContent = pendingCount;

                        // Check if all counts are zero
                        if (publishedCount === 0 && draftCount === 0 && pendingCount === 0) {
                            // Hide chart and show no courses message
                            document.getElementById("course-status-chart").style.display = 'none';
                            document.getElementById("no-courses-message").classList.remove('d-none');
                        } else {
                            // Render chart normally
                            var ctx = document.getElementById("course-status-chart").getContext("2d");
                            new Chart(ctx, {
                                type: "doughnut",
                                data: {
                                    labels: ["Published", "Draft", "Pending Approval"],
                                    datasets: [{
                                        data: [publishedCount, draftCount, pendingCount],
                                        backgroundColor: ["#0acf97", "#727cf5", "#fa5c7c"],
                                        hoverBackgroundColor: ["#0acf97cc", "#727cf5cc", "#fa5c7ccc"],
                                        borderWidth: 2,
                                    }],
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    cutout: "55%",
                                    plugins: {
                                        legend: {
                                            position: "bottom",
                                            labels: {
                                                color: "#6c757d",
                                                font: {
                                                    size: 14
                                                },
                                            },
                                        },
                                        tooltip: {
                                            enabled: true,
                                            callbacks: {
                                                label: function(tooltipItem) {
                                                    return `${tooltipItem.label}: ${tooltipItem.raw}`;
                                                },
                                            },
                                        },
                                    },
                                },
                            });
                        }
                    } catch (error) {
                        console.error("JSON Parse Error:", error);
                    }
                })
                .catch(error => console.error("Error fetching course data:", error));
        });
    </script>
</div><!-- end card body-->
                            </div> <!-- end card -->
                        </div><!-- end col-->

                        <?php


                        // Database connection
                        $host = "localhost";
                        $user = "root"; // Change if needed
                        $pass = "root"; // Change if needed
                        $dbname = "learnix_db";

                        $conn = new mysqli($host, $user, $pass, $dbname);

                        // Check connection
                        if ($conn->connect_error) {
                            die("Connection failed: " . $conn->connect_error);
                        }

                        // Ensure user session is set
                        if (!isset($_SESSION['user_id'])) {
                            die("User not authenticated.");
                        }

                        $user_id = $_SESSION['user_id'];

                        // Get instructor ID
                        $query = "SELECT instructor_id FROM instructors WHERE user_id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $stmt->bind_result($instructor_id);
                        $stmt->fetch();
                        $stmt->close();

                        if (!$instructor_id) {
                            die("Instructor not found.");
                        }

                        // Fetch latest 9 courses with category
// Fetch latest 9 courses with subcategory
$sql = "SELECT c.course_id, c.title, c.price, c.status, c.created_at, 
               COALESCE(sub.name, 'Uncategorized') AS subcategory
        FROM courses c
        LEFT JOIN subcategories sub ON c.subcategory_id = sub.subcategory_id
        WHERE c.instructor_id = ?
        ORDER BY c.created_at DESC
        LIMIT 9";

                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $instructor_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if (!$result) {
                            die("Error fetching courses: " . $conn->error);
                        }
                        ?>

                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h4 class="header-title">Latest 9 Courses</h4>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-centered table-nowrap table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Title</th>
                                                    <th>Category</th>
                                                    <th>Price</th>
                                                    <th>Status</th>
                                                    <th>Created At</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if ($result->num_rows > 0) { ?>
                                                    <?php
                                                    while ($row = $result->fetch_assoc()) {
                                                    ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['subcategory']); ?></td>
                                                            <td>$<?php echo number_format($row['price'], 2); ?></td>
                                                            <td>
                                                                <?php
                                                                $status = $row['status'] ?? 'Draft'; // Default to 'Draft' if null
                                                                $badgeClass = 'badge-draft'; // Default class
                                                                switch ($status) {
                                                                    case 'Published':
                                                                        $badgeClass = 'badge-published';
                                                                        break;
                                                                    case 'Draft':
                                                                        $badgeClass = 'badge-draft';
                                                                        break;
                                                                    case 'Pending':
                                                                        $badgeClass = 'badge-pending';
                                                                        break;
                                                                }
                                                                ?>
                                                                <span class="badge <?php echo $badgeClass; ?>">
                                                                    <?php echo htmlspecialchars($status); ?>
                                                                </span>
                                                            </td>
                                                            <td><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
                                                        </tr>
                                                    <?php
                                                    }
                                                    ?>


                                                <?php } else { ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center">No courses found.</td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div> <!-- end table-responsive -->
                                </div> <!-- end card body -->
                            </div> <!-- end card -->
                        </div><!-- end col-->

                        <?php
                        $conn->close();
                        ?>


                    </div>
                    <!-- end row-->



                </div>
                <!-- content -->

                <!-- Footer Start -->
                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6">
                                © Learnix. <script>
                                    document.write(new Date().getFullYear())
                                </script> All rights reserved.
                            </div>
                        </div>
                    </div>
                </footer>
                <!-- end Footer -->

            </div>

            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->


        </div>
        <!-- END wrapper -->

        <!-- bundle -->
        <script src="assets/js/vendor.min.js"></script>
        <script src="assets/js/app.min.js"></script>

        <!-- third party js -->
        <script src="assets/js/vendor/chart.min.js"></script>
        <!-- third party js ends -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


        <!-- demo app -->
        <script src="assets/js/pages/demo.dashboard-projects.js"></script>
        <!-- end demo js-->
</body>

<!-- Mirrored from coderthemes.com/hyper/saas/index.php by HTTrack Website Copier/3.x [XR&CO'2014], Fri, 29 Jul 2022 10:20:07 GMT -->

</html>