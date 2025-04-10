<?php
require '../backend/session_start.php'; // Ensure session is started

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
    <title>Instructor | Learnix - Create and Manage Courses</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Intuitive dashboard for instructors to create, manage courses, track student progress, and engage learners effectively." />
    <meta name="author" content="Learnix Team" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- third party css -->
    <link href="assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <!-- third party css end -->

    <!-- App css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <meta name="sourcemap" content="off">
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
                                        <li class="breadcrumb-item active">Profile</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Profile Overview</h4>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    <div class="row">
                        <?php
                        // Start session if not already started
                        if (session_status() === PHP_SESSION_NONE) {
                            session_start();
                        }

                        // Check if user is logged in
                        if (!isset($_SESSION['user_id'])) {
                            echo "User not logged in!";
                            exit;
                        }

                        $user_id = $_SESSION['user_id'];

                        // Database connection
                        require_once '../backend/config.php'; // Path to your config file

                        // Fetch user and instructor data
                        $query = "
                        SELECT u.*, i.instructor_id, i.bio, i.verification_status 
                        FROM users u 
                        LEFT JOIN instructors i ON u.user_id = i.user_id 
                        WHERE u.user_id = ?
                        ";

                        $stmt = mysqli_prepare($conn, $query);
                        mysqli_stmt_bind_param($stmt, "i", $user_id);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);

                        if (mysqli_num_rows($result) > 0) {
                            $userData = mysqli_fetch_assoc($result);
                        } else {
                            echo "User not found!";
                            exit;
                        }

                        // Get instructor_id if exists
                        $instructor_id = $userData['instructor_id'];

                        // Fetch social links if instructor exists
                        $socialLinks = [];
                        if ($instructor_id) {
                            $query = "SELECT * FROM instructor_social_links WHERE instructor_id = ?";
                            $stmt = mysqli_prepare($conn, $query);
                            mysqli_stmt_bind_param($stmt, "i", $instructor_id);
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);

                            if (mysqli_num_rows($result) > 0) {
                                $socialLinks = mysqli_fetch_assoc($result);
                            }
                        }

                        // Fetch experience entries if instructor exists
                        $experiences = [];
                        if ($instructor_id) {
                            $query = "SELECT * FROM instructor_experience WHERE instructor_id = ? ORDER BY years_worked DESC";
                            $stmt = mysqli_prepare($conn, $query);
                            mysqli_stmt_bind_param($stmt, "i", $instructor_id);
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);

                            while ($row = mysqli_fetch_assoc($result)) {
                                $experiences[] = $row;
                            }
                        }

                        // Prepare profile image path
                        $profileImage = "../uploads/instructor-profile/" . ($userData['profile_pic'] ?: 'default.png');

                        // Helper function to check if social link exists
                        function hasSocialLink($linkData)
                        {
                            return isset($linkData) && !empty($linkData);
                        }
                        ?>

                        <div class="col-xl-4 col-lg-5">
                            <div class="card text-center">
                                <div class="card-body">
                                    <img src="<?= htmlspecialchars($profileImage) ?>" class="rounded-circle avatar-lg img-thumbnail"
                                        alt="profile-image">

                                    <h4 class="mb-0 mt-2" id="instructorName">
                                        <?= htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']) ?>
                                        <!-- Verification icon with conditional styling -->
                                        <img
                                            src="../assets/svg/illustrations/top-vendor.svg"
                                            alt="Verification Status"
                                            data-bs-toggle="tooltip"
                                            style="width: 16px; height: 16px; margin-left: 5px; opacity: <?php
                                                                                                            if (!isset($userData['verification_status']) || $userData['verification_status'] === 'unverified') {
                                                                                                                echo '0.25';
                                                                                                            } elseif ($userData['verification_status'] === 'pending') {
                                                                                                                echo '0.5';
                                                                                                            } else {
                                                                                                                echo '1';
                                                                                                            }
                                                                                                            ?>;"
                                            title="<?php
                                                    if (!isset($userData['verification_status']) || $userData['verification_status'] === 'unverified') echo 'Unverified';
                                                    elseif ($userData['verification_status'] === 'pending') echo 'Verification Pending';
                                                    else echo 'Verified';
                                                    ?>">
                                    </h4>

                                    <p class="text-muted font-14">Instructor</p>

                                    <?php
$status = $userData['verification_status'] ?? 'unverified';

// Display different messages based on verification status
if (strtolower($status) === 'unverified'):
?>
    <a href="" class="text font-14 p-0 text-danger" data-bs-toggle="modal" data-bs-target="#verificationModal">
        Click here to verify
    </a>
<?php elseif (strtolower($status) === 'pending'): ?>
    <span class="text font-14 p-0 text-primary">
        <i class="mdi mdi-clock-outline me-1"></i> Verification pending
    </span>
<?php elseif (strtolower($status) === 'rejected'): ?>
    <a href="" class="text font-14 p-0 text-danger" data-bs-toggle="modal" data-bs-target="#verificationModal">
        <i class="mdi mdi-alert-circle me-1"></i> Verification rejected - Click to resubmit
    </a>
<?php elseif (strtolower($status) === 'verified'): ?>
    <span class="text font-14 p-0 text-success">
        <i class="mdi mdi-check-circle me-1"></i> Verified instructor
    </span>
<?php endif; ?>

<!-- Verification Modal -->
<div class="modal fade" id="verificationModal" tabindex="-1" aria-labelledby="verificationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verificationModalLabel">
                    <?php echo (strtolower($status) === 'rejected') ? 'Resubmit Verification' : 'Instructor Verification'; ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (strtolower($status) === 'rejected'): ?>
                <div class="alert alert-warning">
                    <i class="mdi mdi-alert-circle me-1"></i>
                    Your previous verification submission was rejected. Please review and resubmit your credentials.
                </div>
                <?php endif; ?>
                
                <form id="verificationForm" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="verification">
                    <div class="mb-3">
                        <label for="credentials" class="form-label">Professional Credentials</label>
                        <textarea class="form-control" id="credentials" name="credentials" rows="3"
                            placeholder="Describe your professional background and qualifications"></textarea>
                        <small class="text-muted">Include details about your expertise, experience, and qualifications (minimum 50 characters)</small>
                    </div>
                    <div class="mb-3">
                        <label for="verificationDocs" class="form-label">Upload Documents</label>
                        <input type="file" class="form-control" id="verificationDocs" name="verification_docs" multiple>
                        <small class="text-muted">Upload certificates, diplomas, or other supporting documents (PDF, JPG, PNG)</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="submitVerification()">
                    <?php echo (strtolower($status) === 'rejected') ? 'Resubmit for Verification' : 'Submit for Verification'; ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function submitVerification() {
        // Basic validation
        const credentials = document.getElementById('credentials').value.trim();
        const docs = document.getElementById('verificationDocs').files;

        // Check if credentials are provided
        if (credentials.length < 50) {
            showAlert('error', 'Please provide detailed credentials (at least 50 characters)');
            return;
        }

        // Check if documents are uploaded
        if (docs.length === 0) {
            showAlert('error', 'Please upload at least one supporting document');
            return;
        }

        // Get form data
        const formData = new FormData(document.getElementById('verificationForm'));

        // Add the verification docs files explicitly to ensure they're added correctly
        for (let i = 0; i < docs.length; i++) {
            formData.append('verification_docs[]', docs[i]);
        }

        // Show loading overlay
        const loadingOverlay = showLoading();

        // Send AJAX request
        fetch('../backend/instructor/verification.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                // Get the raw text first to see what's being returned
                return response.text().then(text => {
                    console.log("Raw server response:", text);
                    try {
                        // Try to parse as JSON
                        return JSON.parse(text);
                    } catch (error) {
                        // If parsing fails, throw a more descriptive error
                        throw new Error(`Server returned invalid JSON: ${text}`);
                    }
                });
            })
            .then(data => {
                // Hide loading overlay
                hideLoading(loadingOverlay);

                // Close modal
                document.querySelector('#verificationModal .btn-close').click();

                if (data.status === 'success') {
                    showAlert('success', 'Verification request submitted successfully. We will review your credentials shortly.');

                    // Add a slight delay before refreshing the page
                    setTimeout(() => {
                        // Refresh the page
                        window.location.reload();
                    }, 1500); // 1.5 seconds delay to allow the user to see the success message
                } else {
                    showAlert('error', data.message || 'An error occurred during submission');
                }
            })
            .catch(error => {
                hideLoading(loadingOverlay);
                showAlert('error', 'An error occurred: ' + error.message);
                console.error('Error details:', error);
            });
    }
</script>

                                    <div class="text-start mt-3">
                                        <h4 class="font-13 text-uppercase">About Me : </h4>
                                        <p class="text-muted font-13 mb-3">
                                            <?= htmlspecialchars($userData['bio'] ?? 'No bio available') ?>
                                        </p>
                                        <p class="text-muted mb-2 font-13">
                                            <strong>Full Name :</strong>
                                            <span class="ms-2"><?= htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']) ?></span>
                                        </p>

                                        <p class="text-muted mb-2 font-13">
                                            <strong>Mobile :</strong>
                                            <span class="ms-2"><?= htmlspecialchars($userData['phone'] ?? 'Not provided') ?></span>
                                        </p>

                                        <p class="text-muted mb-2 font-13">
                                            <strong>Email :</strong>
                                            <span class="ms-2"><?= htmlspecialchars($userData['email']) ?></span>
                                        </p>

                                        <p class="text-muted mb-1 font-13">
                                            <strong>Location :</strong>
                                            <span class="ms-2"><?= htmlspecialchars($userData['location'] ?? 'Not provided') ?></span>
                                        </p>
                                    </div>

                                    <ul class="social-list list-inline mt-3 mb-0">
                                        <?php if (hasSocialLink($socialLinks['facebook'] ?? '')): ?>
                                            <li class="list-inline-item">
                                                <a href="<?= htmlspecialchars($socialLinks['facebook']) ?>" target="_blank" class="social-list-item border-primary text-primary">
                                                    <i class="mdi mdi-facebook"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php if (hasSocialLink($socialLinks['instagram'] ?? '')): ?>
                                            <li class="list-inline-item">
                                                <a href="<?= htmlspecialchars($socialLinks['instagram']) ?>" target="_blank" class="social-list-item border-danger text-danger">
                                                    <i class="mdi mdi-instagram"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php if (hasSocialLink($socialLinks['twitter'] ?? '')): ?>
                                            <li class="list-inline-item">
                                                <a href="<?= htmlspecialchars($socialLinks['twitter']) ?>" target="_blank" class="social-list-item border-info text-info">
                                                    <i class="mdi mdi-twitter"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php if (hasSocialLink($socialLinks['github'] ?? '')): ?>
                                            <li class="list-inline-item">
                                                <a href="<?= htmlspecialchars($socialLinks['github']) ?>" target="_blank" class="social-list-item border-secondary text-secondary">
                                                    <i class="mdi mdi-github"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php if (hasSocialLink($socialLinks['linkedin'] ?? '')): ?>
                                            <li class="list-inline-item">
                                                <a href="<?= htmlspecialchars($socialLinks['linkedin']) ?>" target="_blank" class="social-list-item border-primary text-primary">
                                                    <i class="mdi mdi-linkedin"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>

                                </div> <!-- end card-body -->
                            </div> <!-- end card -->

                            <!-- Experience Section -->
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="text-uppercase"><i class="mdi mdi-briefcase me-1"></i>
                                        Experience</h5>

                                    <div class="timeline-alt pb-0">
                                        <?php if (empty($experiences)): ?>
                                            <p class="text-muted">No experience entries added yet.</p>
                                        <?php else: ?>
                                            <?php foreach ($experiences as $exp): ?>
                                                <div class="timeline-item">
                                                    <i class="mdi mdi-circle bg-info-lighten text-info timeline-icon"></i>
                                                    <div class="timeline-item-info">
                                                        <h5 class="mt-0 mb-1"><?= htmlspecialchars($exp['job_title']) ?></h5>
                                                        <p class="font-14">
                                                            <?= htmlspecialchars($exp['company_name']) ?>
                                                            <span class="ms-2 font-12">Year: <?= htmlspecialchars($exp['years_worked']) ?></span>
                                                        </p>
                                                        <p class="text-muted mt-2 mb-0 pb-3">
                                                            <?= htmlspecialchars($exp['job_description']) ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <!-- end timeline -->
                                </div> <!-- end card-body-->
                            </div> <!-- end card-->

                        </div> <!-- end col-->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card">
                                <div class="card-body">
                                    <ul class="nav nav-pills bg-nav-pills nav-justified mb-3">
                                        <li class="nav-item">
                                            <a href="#settings" data-bs-toggle="tab" aria-expanded="true" class="nav-link rounded-0 active">
                                                Settings
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#experience" data-bs-toggle="tab" aria-expanded="false" class="nav-link rounded-0">
                                                Experience
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#social" data-bs-toggle="tab" aria-expanded="false" class="nav-link rounded-0">
                                                Social Links
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#password" data-bs-toggle="tab" aria-expanded="false" class="nav-link rounded-0">
                                                Change Password
                                            </a>
                                        </li>
                                    </ul>

                                    <div class="tab-content">
                                        <!-- Settings Tab -->
                                        <div class="tab-pane show active" id="settings">
                                            <form id="personalInfoForm" enctype="multipart/form-data">
                                                <input type="hidden" name="action" value="personal_info">
                                                <h5 class="mb-4 text-uppercase"><i class="mdi mdi-account-circle me-1"></i> Personal Info</h5>
                                                <!-- Profile Photo Upload -->
                                                <div class="row">
                                                    <div class="col-sm-9">
                                                        <div class="d-flex align-items-center">
                                                            <!-- Profile Image Box -->
                                                            <div class="position-relative">
                                                                <label class="avatar avatar-xl avatar-circle" for="avatarUploader">
                                                                    <img id="avatarImg" class="avatar-img rounded-circle border border-2"
                                                                        src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Photo"
                                                                        style="width: 100px; height: 100px; object-fit: cover;">
                                                                </label>
                                                                <!-- Hidden File Input -->
                                                                <input type="file" id="avatarUploader" name="avatar" accept=".png, .jpeg, .jpg"
                                                                    class="d-none" onchange="previewImage(event)">
                                                            </div>

                                                            <!-- Buttons -->
                                                            <div class="d-grid d-sm-flex gap-2 ms-4">
                                                                <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('avatarUploader').click();">
                                                                    Upload Photo
                                                                </button>
                                                                <button type="button" class="btn btn-white btn-sm" onclick="resetImage();">
                                                                    Delete
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="firstname" class="form-label">First Name</label>
                                                            <input type="text" class="form-control" id="firstname" name="firstname"
                                                                value="<?php echo htmlspecialchars($userData['first_name'] ?? ''); ?>"
                                                                placeholder="Enter first name">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="lastname" class="form-label">Last Name</label>
                                                            <input type="text" class="form-control" id="lastname" name="lastname"
                                                                value="<?php echo htmlspecialchars($userData['last_name'] ?? ''); ?>"
                                                                placeholder="Enter last name">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="mobile" class="form-label">Mobile</label>
                                                            <input type="text" class="form-control" id="mobile" name="mobile"
                                                                value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>"
                                                                placeholder="Enter mobile number">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="location" class="form-label">Location</label>
                                                            <input type="text" class="form-control" id="location" name="location"
                                                                value="<?php echo htmlspecialchars($userData['location'] ?? ''); ?>"
                                                                placeholder="Enter location">
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- About Section -->
                                                <h5 class="mb-4 text-uppercase bg-light p-2"><i class="mdi mdi-information-outline me-1"></i> About</h5>
                                                <div class="mb-3">
                                                    <label for="userbio" class="form-label">Bio</label>
                                                    <textarea class="form-control" id="userbio" name="userbio" rows="4"
                                                        placeholder="Write something about yourself..."><?php echo htmlspecialchars($userData['bio'] ?? ''); ?></textarea>
                                                </div>

                                                <div class="text-end">
                                                    <button type="submit" class="btn btn-success mt-2"><i class="mdi mdi-content-save"></i> Save</button>
                                                </div>
                                            </form>
                                        </div>

                                        <!-- Experience Tab -->
                                        <div class="tab-pane" id="experience">
                                            <form id="experienceForm">
                                                <input type="hidden" name="action" value="experience">
                                                <h5 class="mb-4 text-uppercase"><i class="mdi mdi-briefcase me-1"></i> Experience</h5>

                                                <div id="experience-container">
                                                    <?php if (empty($experiences)): ?>
                                                        <!-- Default empty experience entry if no existing entries -->
                                                        <div class="experience-entry">
                                                            <div class="mb-3">
                                                                <label class="form-label">Job Title</label>
                                                                <input type="text" class="form-control" name="job_titles[]" placeholder="Enter job title">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Company Name</label>
                                                                <input type="text" class="form-control" name="company_names[]" placeholder="Enter company name">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Years Worked</label>
                                                                <input type="text" class="form-control" name="years_worked[]" placeholder="e.g. 2015 - 2018">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Job Description</label>
                                                                <textarea class="form-control" name="job_descriptions[]" rows="3" placeholder="Describe your role and responsibilities"></textarea>
                                                            </div>
                                                            <hr>
                                                        </div>
                                                    <?php else: ?>
                                                        <!-- Display existing experience entries -->
                                                        <?php foreach ($experiences as $index => $exp): ?>
                                                            <div class="experience-entry">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Job Title</label>
                                                                    <input type="text" class="form-control" name="job_titles[]"
                                                                        value="<?php echo htmlspecialchars($exp['job_title']); ?>"
                                                                        placeholder="Enter job title">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Company Name</label>
                                                                    <input type="text" class="form-control" name="company_names[]"
                                                                        value="<?php echo htmlspecialchars($exp['company_name']); ?>"
                                                                        placeholder="Enter company name">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Years Worked</label>
                                                                    <input type="text" class="form-control" name="years_worked[]"
                                                                        value="<?php echo htmlspecialchars($exp['years_worked']); ?>"
                                                                        placeholder="e.g. 2015 - 2018">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Job Description</label>
                                                                    <textarea class="form-control" name="job_descriptions[]" rows="3"
                                                                        placeholder="Describe your role and responsibilities"><?php echo htmlspecialchars($exp['job_description']); ?></textarea>
                                                                </div>
                                                                <?php if ($index > 0): ?>
                                                                    <button type="button" class="btn btn-danger btn-sm removeExperience">Remove</button>
                                                                <?php endif; ?>
                                                                <hr>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </div>

                                                <button type="button" class="btn btn-outline-primary mb-3" id="addExperience">
                                                    <i class="mdi mdi-plus"></i> Add Experience
                                                </button>

                                                <div class="text-end">
                                                    <button type="submit" class="btn btn-success mt-2">
                                                        <i class="mdi mdi-content-save"></i> Save
                                                    </button>
                                                </div>
                                            </form>
                                        </div>

                                        <!-- Social Links Tab -->
                                        <div class="tab-pane" id="social">
                                            <form id="socialLinksForm">
                                                <input type="hidden" name="action" value="social_links">
                                                <h5 class="mb-4 text-uppercase"><i class="mdi mdi-earth me-1"></i> Social Links</h5>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="social-fb" class="form-label">Facebook</label>
                                                            <input type="text" class="form-control" id="social-fb" name="social-fb"
                                                                value="<?php echo htmlspecialchars($socialLinks['facebook'] ?? ''); ?>"
                                                                placeholder="Enter Facebook URL">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="social-tw" class="form-label">Twitter</label>
                                                            <input type="text" class="form-control" id="social-tw" name="social-tw"
                                                                value="<?php echo htmlspecialchars($socialLinks['twitter'] ?? ''); ?>"
                                                                placeholder="Enter Twitter handle">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="social-ig" class="form-label">Instagram</label>
                                                            <input type="text" class="form-control" id="social-ig" name="social-ig"
                                                                value="<?php echo htmlspecialchars($socialLinks['instagram'] ?? ''); ?>"
                                                                placeholder="Enter Instagram username">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="social-li" class="form-label">LinkedIn</label>
                                                            <input type="text" class="form-control" id="social-li" name="social-li"
                                                                value="<?php echo htmlspecialchars($socialLinks['linkedin'] ?? ''); ?>"
                                                                placeholder="Enter LinkedIn URL">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="social-gh" class="form-label">GitHub</label>
                                                            <input type="text" class="form-control" id="social-gh" name="social-gh"
                                                                value="<?php echo htmlspecialchars($socialLinks['github'] ?? ''); ?>"
                                                                placeholder="Enter GitHub username">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="text-end">
                                                    <button type="submit" class="btn btn-success mt-2"><i class="mdi mdi-content-save"></i> Save</button>
                                                </div>
                                            </form>
                                        </div>

                                        <!-- Change Password Tab -->
                                        <div class="tab-pane" id="password">
                                            <form id="passwordForm">
                                                <input type="hidden" name="action" value="change_password">
                                                <h5 class="mb-4 text-uppercase"><i class="mdi mdi-lock me-1"></i> Change Password</h5>
                                                <div class="mb-3">
                                                    <label for="currentpassword" class="form-label">Current Password</label>
                                                    <input type="password" class="form-control" id="currentpassword" name="currentpassword" placeholder="Enter current password">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="newpassword" class="form-label">New Password</label>
                                                    <input type="password" class="form-control" id="newpassword" name="newpassword" placeholder="Enter new password">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="confirmpassword" class="form-label">Confirm Password</label>
                                                    <input type="password" class="form-control" id="confirmpassword" name="confirmpassword" placeholder="Confirm new password">
                                                </div>
                                                <div class="text-end">
                                                    <button type="submit" class="btn btn-success mt-2"><i class="mdi mdi-content-save"></i> Change Password</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- JavaScript remains the same -->
                                    <script>
                                        function previewImage(event) {
                                            const reader = new FileReader();
                                            reader.onload = function() {
                                                document.getElementById('avatarImg').src = reader.result;
                                            };
                                            reader.readAsDataURL(event.target.files[0]);
                                        }

                                        function resetImage() {
                                            document.getElementById('avatarImg').src = "../uploads/profile/default.png"; // Reset to default image
                                            document.getElementById('avatarUploader').value = ""; // Clear file input
                                        }

                                        // Initialize form handlers when document is ready
                                        document.addEventListener('DOMContentLoaded', function() {
                                            // Setup experience entry add functionality
                                            document.getElementById('addExperience').addEventListener('click', function() {
                                                let experienceContainer = document.getElementById('experience-container');
                                                let newExperience = document.createElement('div');
                                                newExperience.classList.add('experience-entry');
                                                newExperience.innerHTML = `
<div class="mb-3">
    <label class="form-label">Job Title</label>
    <input type="text" class="form-control" name="job_titles[]" placeholder="Enter job title">
</div>
<div class="mb-3">
    <label class="form-label">Company Name</label>
    <input type="text" class="form-control" name="company_names[]" placeholder="Enter company name">
</div>
<div class="mb-3">
    <label class="form-label">Years Worked</label>
    <input type="text" class="form-control" name="years_worked[]" placeholder="e.g. 2015 - 2018">
</div>
<div class="mb-3">
    <label class="form-label">Job Description</label>
    <textarea class="form-control" name="job_descriptions[]" rows="3" placeholder="Describe your role and responsibilities"></textarea>
</div>
<button type="button" class="btn btn-danger btn-sm removeExperience">Remove</button>
<hr>
`;
                                                experienceContainer.appendChild(newExperience);
                                            });

                                            // Setup experience entry remove functionality
                                            document.addEventListener('click', function(event) {
                                                if (event.target.classList.contains('removeExperience')) {
                                                    event.target.parentElement.remove();
                                                }
                                            });

                                            // Set up form submissions with AJAX
                                            const forms = [{
                                                    id: 'personalInfoForm',
                                                    url: '../backend/instructor/update_profile.php'
                                                },
                                                {
                                                    id: 'experienceForm',
                                                    url: '../backend/instructor/update_profile.php'
                                                },
                                                {
                                                    id: 'socialLinksForm',
                                                    url: '../backend/instructor/update_profile.php'
                                                },
                                                {
                                                    id: 'passwordForm',
                                                    url: '../backend/instructor/update_profile.php'
                                                }
                                            ];

                                            forms.forEach(form => {
                                                const formElement = document.getElementById(form.id);
                                                if (formElement) {
                                                    formElement.addEventListener('submit', function(e) {
                                                        e.preventDefault();

                                                        // Special handling for password form
                                                        if (form.id === 'passwordForm') {
                                                            const newPass = document.getElementById('newpassword').value;
                                                            const confirmPass = document.getElementById('confirmpassword').value;

                                                            if (newPass !== confirmPass) {
                                                                showAlert('error', 'New passwords do not match!');
                                                                return;
                                                            }
                                                        }

                                                        // Show loading overlay
                                                        const loadingOverlay = showLoading();

                                                        // Create FormData object for the form
                                                        const formData = new FormData(this);

                                                        // Send AJAX request
                                                        fetch(form.url, {
                                                                method: 'POST',
                                                                body: formData
                                                            })
                                                            .then(response => response.json())
                                                            .then(data => {
                                                                // Hide loading overlay
                                                                hideLoading(loadingOverlay);

                                                                if (data.status === 'success') {
                                                                    showAlert('success', data.message);

                                                                    // Reload page after a short delay
                                                                    setTimeout(() => {
                                                                        window.location.reload();
                                                                    }, 1500);
                                                                } else {
                                                                    showAlert('error', data.message || 'An error occurred');
                                                                }
                                                            })
                                                            .catch(error => {
                                                                // Hide loading overlay
                                                                hideLoading(loadingOverlay);
                                                                showAlert('error', 'An error occurred: ' + error.message);
                                                            });
                                                    });
                                                }
                                            });
                                        });

                                        // Show loading overlay function
                                        function showLoading() {
                                            const overlay = document.createElement('div');
                                            overlay.className = 'loading-overlay';
                                            overlay.style.position = 'fixed';
                                            overlay.style.top = '0';
                                            overlay.style.left = '0';
                                            overlay.style.width = '100%';
                                            overlay.style.height = '100%';
                                            overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
                                            overlay.style.display = 'flex';
                                            overlay.style.justifyContent = 'center';
                                            overlay.style.alignItems = 'center';
                                            overlay.style.zIndex = '9999';

                                            const spinner = document.createElement('div');
                                            spinner.className = 'spinner-border text-light';
                                            spinner.setAttribute('role', 'status');
                                            spinner.style.width = '3rem';
                                            spinner.style.height = '3rem';

                                            const spinnerText = document.createElement('span');
                                            spinnerText.className = 'visually-hidden';
                                            spinnerText.textContent = 'Loading...';

                                            spinner.appendChild(spinnerText);
                                            overlay.appendChild(spinner);
                                            document.body.appendChild(overlay);

                                            // Disable scrolling on body
                                            document.body.style.overflow = 'hidden';

                                            return overlay;
                                        }

                                        // Hide loading overlay function
                                        function hideLoading(overlay) {
                                            if (overlay && overlay.parentNode) {
                                                overlay.parentNode.removeChild(overlay);
                                                // Re-enable scrolling
                                                document.body.style.overflow = '';
                                            }
                                        }

                                        // Show alert notification function
                                        function showAlert(type, message) {
                                            const alertDiv = document.createElement('div');
                                            alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
                                            alertDiv.setAttribute('role', 'alert');

                                            alertDiv.innerHTML = `
${message}
<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
`;

                                            // Position the alert
                                            alertDiv.style.position = 'fixed';
                                            alertDiv.style.top = '20px';
                                            alertDiv.style.left = '50%';
                                            alertDiv.style.transform = 'translateX(-50%)';
                                            alertDiv.style.zIndex = '9999';
                                            alertDiv.style.minWidth = '300px';
                                            alertDiv.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';

                                            document.body.appendChild(alertDiv);

                                            // Auto-dismiss after 5 seconds
                                            setTimeout(() => {
                                                if (alertDiv.parentNode) {
                                                    alertDiv.classList.remove('show');
                                                    setTimeout(() => {
                                                        if (alertDiv.parentNode) {
                                                            alertDiv.parentNode.removeChild(alertDiv);
                                                        }
                                                    }, 300);
                                                }
                                            }, 5000);
                                        };
                                    </script>
                                </div>
                            </div> <!-- end card -->

                        </div> <!-- end col -->
                    </div>
                    <!-- end row-->

                </div>
                <!-- container -->

            </div>
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

    <?php include '../includes/instructor-darkmode.php'; ?>


    <!-- bundle -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.min.js"></script>

    <!-- third party js -->
    <script src="assets/js/vendor/apexcharts.min.js"></script>
    <script src="assets/js/vendor/jquery-jvectormap-1.2.2.min.js"></script>
    <script src="assets/js/vendor/jquery-jvectormap-world-mill-en.js"></script>
    <!-- third party js ends -->

    <!-- demo app -->
    <script src="assets/js/pages/demo.dashboard.js"></script>
    <!-- end demo js-->
</body>



</html>