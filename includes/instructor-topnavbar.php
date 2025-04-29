<?php
include("../backend/config.php");
// Near the top of your file where you fetch user data
$user_id = $_SESSION['user_id'];

// Get user profile pic, name and verification status
$query = "SELECT u.profile_pic, u.first_name, u.last_name, i.verification_status 
                  FROM users u 
                  LEFT JOIN instructors i ON u.user_id = i.user_id 
                  WHERE u.user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$userData = mysqli_fetch_assoc($result);

// Set profile image path
$defaultImage = "default.png";
$profileImage = $userData['profile_pic'] ? "../uploads/instructor-profile/" . $userData['profile_pic'] : $defaultImage;

// Store user name for display
$userName = $userData['first_name'] . ' ' . $userData['last_name'];

// Determine verification status
$isVerified = isset($userData['verification_status']) && $userData['verification_status'] === 'verified';
?>
<div class="navbar-custom">
    <ul class="list-unstyled topbar-menu float-end mb-0">

        <li class="dropdown notification-list">
            <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                <i class="dripicons-bell noti-icon"></i>
                <span class="noti-icon-badge"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated dropdown-lg">

                <!-- item-->
                <div class="dropdown-item noti-title px-3">
                    <h5 class="m-0">
                        <span class="float-end">
                            <a href="javascript: void(0);" class="text-dark">
                                <small>Clear All</small>
                            </a>
                        </span>Notification
                    </h5>
                </div>

                <div class="px-3" style="max-height: 300px;" data-simplebar>

                    <h5 class="text-muted font-13 fw-normal mt-0">Today</h5>
                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item p-0 notify-item card unread-noti shadow-none mb-2">
                        <div class="card-body">
                            <span class="float-end noti-close-btn text-muted"><i class="mdi mdi-close"></i></span>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="notify-icon bg-primary">
                                        <i class="mdi mdi-comment-account-outline"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 text-truncate ms-2">
                                    <h5 class="noti-item-title fw-semibold font-14">Datacorp <small class="fw-normal text-muted ms-1">1 min ago</small></h5>
                                    <small class="noti-item-subtitle text-muted">Caleb Flakelar commented on Admin</small>
                                </div>
                            </div>
                        </div>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item p-0 notify-item card read-noti shadow-none mb-2">
                        <div class="card-body">
                            <span class="float-end noti-close-btn text-muted"><i class="mdi mdi-close"></i></span>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="notify-icon bg-info">
                                        <i class="mdi mdi-account-plus"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 text-truncate ms-2">
                                    <h5 class="noti-item-title fw-semibold font-14">Admin <small class="fw-normal text-muted ms-1">1 hours ago</small></h5>
                                    <small class="noti-item-subtitle text-muted">New user registered</small>
                                </div>
                            </div>
                        </div>
                    </a>

                    <h5 class="text-muted font-13 fw-normal mt-0">Yesterday</h5>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item p-0 notify-item card read-noti shadow-none mb-2">
                        <div class="card-body">
                            <span class="float-end noti-close-btn text-muted"><i class="mdi mdi-close"></i></span>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="notify-icon">
                                        <img src="assets/images/users/avatar-2.jpg" class="img-fluid rounded-circle" alt="" />
                                    </div>
                                </div>
                                <div class="flex-grow-1 text-truncate ms-2">
                                    <h5 class="noti-item-title fw-semibold font-14">Cristina Pride <small class="fw-normal text-muted ms-1">1 day ago</small></h5>
                                    <small class="noti-item-subtitle text-muted">Hi, How are you? What about our next meeting</small>
                                </div>
                            </div>
                        </div>
                    </a>

                    <h5 class="text-muted font-13 fw-normal mt-0">30 Dec 2021</h5>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item p-0 notify-item card read-noti shadow-none mb-2">
                        <div class="card-body">
                            <span class="float-end noti-close-btn text-muted"><i class="mdi mdi-close"></i></span>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="notify-icon bg-primary">
                                        <i class="mdi mdi-comment-account-outline"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 text-truncate ms-2">
                                    <h5 class="noti-item-title fw-semibold font-14">Datacorp</h5>
                                    <small class="noti-item-subtitle text-muted">Caleb Flakelar commented on Admin</small>
                                </div>
                            </div>
                        </div>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item p-0 notify-item card read-noti shadow-none mb-2">
                        <div class="card-body">
                            <span class="float-end noti-close-btn text-muted"><i class="mdi mdi-close"></i></span>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="notify-icon">
                                        <img src="assets/images/users/avatar-4.jpg" class="img-fluid rounded-circle" alt="" />
                                    </div>
                                </div>
                                <div class="flex-grow-1 text-truncate ms-2">
                                    <h5 class="noti-item-title fw-semibold font-14">Karen Robinson</h5>
                                    <small class="noti-item-subtitle text-muted">Wow ! this admin looks good and awesome design</small>
                                </div>
                            </div>
                        </div>
                    </a>

                    <div class="text-center">
                        <i class="mdi mdi-dots-circle mdi-spin text-muted h3 mt-0"></i>
                    </div>
                </div>

                <!-- All-->
                <a href="notifications.php" class="dropdown-item text-center text-primary notify-item border-top border-light py-2">
                    View All
                </a>

            </div>
        </li>

        <li class="notification-list">
            <a class="nav-link end-bar-toggle" href="javascript: void(0);">
                <i class="dripicons-gear noti-icon"></i>
            </a>
        </li>


        <li class="dropdown notification-list">
            <a class="nav-link dropdown-toggle nav-user arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                <span class="account-user-avatar">

                    <!-- Profile image with verification badge -->
                    <div style="position: relative; display: inline-block;">
                        <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="user-image" class="rounded-circle avatar-lg img-thumbnail">

                        <?php if ($isVerified): ?>
                            <div style="position: absolute; bottom: 0; left: 0; width: 16px; height: 16px; background-color: white; border-radius: 50%; display: flex; justify-content: center; align-items: center; box-shadow: 0 0 3px rgba(0,0,0,0.4);">
                                <img src="../assets/svg/illustrations/top-vendor.svg"
                                    alt="Verified"
                                    data-bs-toggle="tooltip"
                                    title="Verified Instructor"
                                    style="width: 12px; height: 12px;">
                            </div>
                        <?php endif; ?>
                    </div>
                </span>
                <span>
                    <span class="account-user-name" id="instructorName">
                        <?php echo htmlspecialchars($userName); ?>
                    </span>
                    <span class="account-position">Instructor</span>
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated topbar-dropdown-menu profile-dropdown">
                <!-- item-->
                <div class=" dropdown-header noti-title">
                    <h6 class="text-overflow m-0">Welcome !</h6>
                </div>

                <!-- item-->
                <a href="profile.php" class="dropdown-item notify-item">
                    <i class="mdi mdi-account-circle me-1"></i>
                    <span>My Account</span>
                </a>

                <!-- item-->
                <a href="../backend/signout.php" class="dropdown-item notify-item">
                    <i class="mdi mdi-logout me-1"></i>
                    <span>Sign Out</span>

                </a>
            </div>
        </li>

    </ul>
    <button class="button-menu-mobile open-left">
        <i class="mdi mdi-menu"></i>
    </button>
</div>


<script>
    document.addEventListener("DOMContentLoaded", async function() {
        try {
            const response = await fetch("../backend/instructor/get_instructor_details.php");
            const data = await response.json();

            if (data.status === "success") {
                document.getElementById("instructorName").textContent = data.full_name;
            } else {
                document.getElementById("instructorName").textContent = "Instructor";
            }
        } catch (error) {
            console.error("Error fetching instructor details:", error);
        }

        // Auto-fade success message after 5 seconds
        setTimeout(() => {
            const successAlert = document.querySelector('.verification-success');
            if (successAlert) {
                const bsAlert = new bootstrap.Alert(successAlert);
                bsAlert.close();
            }
        }, 5000);
    });
</script>