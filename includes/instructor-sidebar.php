<!-- ========== Left Sidebar Start ========== -->
<div class="leftside-menu">
    
    <!-- LOGO -->
    <a href="index.php" class="logo text-center logo-light">
        <span class="logo-lg">
            <img src="assets/images/logo.png" alt="" height="16">
        </span>
        <span class="logo-sm">
            <img src="assets/images/logo_sm.png" alt="" height="16">
        </span>
    </a>

    <!-- LOGO -->
    <a href="index.php" class="logo text-center logo-dark">
        <span class="logo-lg">
            <img src="assets/images/logo-dark.png" alt="" height="16">
        </span>
        <span class="logo-sm">
            <img src="assets/images/logo_sm_dark.png" alt="" height="16">
        </span>
    </a>

    <div class="h-100" id="leftside-menu-container" data-simplebar>

        <!--- Sidemenu -->
        <ul class="side-nav">

            <li class="side-nav-title side-nav-item">Navigation</li>

            <li class="side-nav-item">
                <a href="index.php" class="side-nav-link">
                    <i class="uil-home-alt"></i>
                    <span> Dashboard </span>
                </a>
            </li>

            <li class="side-nav-title side-nav-item">Courses</li>

            <li class="side-nav-item">
                <a href="courses.php" class="side-nav-link">
                    <i class="mdi mdi-view-list-outline"></i>
                    <span> All Courses </span>
                </a>
            </li>


            <li class="side-nav-item">
            <a href="all-students.php" class="side-nav-link">                    
                <i class="uil-users-alt"></i>
                    <span> Students </span>
                </a>
            </li>

            <li class="side-nav-item">
                <a href="annoucements.php" class="side-nav-link">
                    <i class="mdi mdi-bullhorn-variant-outline"></i>
                    <span> Annoucements </span>
                </a>
            </li>

           

            <li class="side-nav-title side-nav-item">Analytics</li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarEarnings" aria-expanded="false" aria-controls="sidebarEarnings" class="side-nav-link">
                    <i class="uil-dollar-alt"></i>
                    <span> Earnings </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarEarnings">
                    <ul class="side-nav-second-level">
                        <li>
                            <a href="earnings.php">Earnings Overview</a>
                        </li>
                        <li>
                            <a href="earnings-history.php">Earnings History</a>
                        </li>
                        <!-- <li>
                            <a href="payout-settings.php">Payout Settings</a>
                        </li> -->
                    </ul>
                </div>
            </li>
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarPerformance" aria-expanded="false" aria-controls="sidebarPerformance" class="side-nav-link">
                    <i class="uil-graph-bar"></i>
                    <span> Performance </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarPerformance">
                    <ul class="side-nav-second-level">
                        <li>
                            <a href="course-performance.php">Courses</a>
                        </li>
                        <li>
                            <a href="student-engagement.php">Student Engagement</a>
                        </li>
                        <li>
                            <a href="quiz-analytics.php">Quiz Analytics </a>
                        </li>
                        <li>
                            <a href="revenue-insights.php">Revenue Insights </a>
                        </li>
                        <li>
                            <a href="custom-reports.php">Custom Reports </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-title side-nav-item">Custom</li>

            <li class="side-nav-item">
                <a href="profile.php" class="side-nav-link">
                    <i class="uil-user-circle"></i>
                    <span> Profile </span>
                </a>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarHelp" aria-expanded="false" aria-controls="sidebarHelp" class="side-nav-link">
                    <i class="mdi mdi-chat-question-outline"></i>
                    <span> Help & Support </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarHelp">
                    <ul class="side-nav-second-level">
                        <li>
                            <a href="FAQs.php">FAQs</a>
                        </li>
                        <li>
                            <a href="tutorials.php">Tutorials</a>
                        </li>
                        <li>
                            <a href="contact-support.php">Contact Support</a>
                        </li>
                    </ul>
                </div>
            </li>


            <li class="side-nav-item">
                <a href="../backend/signout.php" class="side-nav-link">
                    <i class="uil uil-exit"></i>
                    <span> Sign Out </span>
                </a>
            </li>

            
        </ul>

        <!-- end Help Box -->
        <!-- End Sidebar -->

        <div class="clearfix"></div>

    </div>
    <!-- Sidebar -left -->

</div>
<!-- Left Sidebar End -->