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

// Include database configuration
require_once '../backend/config.php';

// Get instructor ID from session
$instructor_id = $_SESSION['instructor_id'];

/**
 * Get available balance for an instructor
 * 
 * @param int $instructor_id The instructor's ID
 * @return float The available balance
 */
function getAvailableBalance($instructor_id)
{
    global $conn;

    // Calculate available earnings (those marked as 'Available' minus already withdrawn amounts)
    $sql = "SELECT 
                COALESCE(SUM(instructor_share), 0) - (
                    SELECT COALESCE(SUM(amount), 0) 
                    FROM instructor_payouts 
                    WHERE instructor_id = ? AND status = 'Completed'
                ) AS available_balance
            FROM instructor_earnings
            WHERE instructor_id = ? AND status = 'Available'";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $instructor_id, $instructor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row['available_balance'] ?? 0;
}

/**
 * Get pending earnings for an instructor
 * 
 * @param int $instructor_id The instructor's ID
 * @return float The pending earnings
 */
function getPendingEarnings($instructor_id)
{
    global $conn;

    $sql = "SELECT COALESCE(SUM(instructor_share), 0) AS pending_earnings
            FROM instructor_earnings
            WHERE instructor_id = ? AND status = 'Pending'";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $instructor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row['pending_earnings'] ?? 0;
}

/**
 * Get lifetime earnings for an instructor
 * 
 * @param int $instructor_id The instructor's ID
 * @return float The lifetime earnings
 */
function getLifetimeEarnings($instructor_id)
{
    global $conn;

    $sql = "SELECT COALESCE(SUM(instructor_share), 0) AS lifetime_earnings
            FROM instructor_earnings
            WHERE instructor_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $instructor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row['lifetime_earnings'] ?? 0;
}


/**
 * Get daily earnings for the past 30 days
 * 
 * @param int $instructor_id The instructor's ID
 * @return array Array of daily earnings data
 */
function getDailyEarnings($instructor_id)
{
    global $conn;

    $sql = "SELECT 
                DATE(created_at) AS day_date,
                DATE_FORMAT(created_at, '%b %d') AS day_label,
                COALESCE(SUM(instructor_share), 0) AS daily_earnings
            FROM instructor_earnings
            WHERE instructor_id = ? 
                AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
            GROUP BY DATE(created_at), DATE_FORMAT(created_at, '%b %d')
            ORDER BY day_date ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $instructor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $earnings_data = [];
    while ($row = $result->fetch_assoc()) {
        $earnings_data[] = $row;
    }

    return $earnings_data;
}

/**
 * Get monthly earnings for the past 12 months
 * 
 * @param int $instructor_id The instructor's ID
 * @return array Array of monthly earnings data
 */
function getMonthlyEarnings($instructor_id)
{
    global $conn;

    $sql = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') AS month_year,
                DATE_FORMAT(created_at, '%b') AS month_name,
                COALESCE(SUM(instructor_share), 0) AS monthly_earnings
            FROM instructor_earnings
            WHERE instructor_id = ? 
                AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%b')
            ORDER BY month_year ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $instructor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $earnings_data = [];
    while ($row = $result->fetch_assoc()) {
        $earnings_data[] = $row;
    }

    return $earnings_data;
}

/**
 * Get top performing courses by earnings
 * 
 * @param int $instructor_id The instructor's ID
 * @param int $limit The number of courses to return
 * @return array Array of top course data
 */
function getTopPerformingCourses($instructor_id, $limit = 5)
{
    global $conn;

    $sql = "SELECT 
                c.course_id,
                c.title,
                c.thumbnail,
                COALESCE(SUM(ie.instructor_share), 0) AS total_earnings,
                COUNT(DISTINCT cp.payment_id) AS sale_count
            FROM instructor_earnings ie
            JOIN courses c ON ie.course_id = c.course_id
            JOIN course_payments cp ON ie.payment_id = cp.payment_id
            WHERE ie.instructor_id = ?
            GROUP BY c.course_id, c.title, c.thumbnail
            ORDER BY total_earnings DESC
            LIMIT ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $instructor_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }

    return $courses;
}

/**
 * Get recent transactions
 * 
 * @param int $instructor_id The instructor's ID
 * @param int $limit The number of transactions to return
 * @return array Array of transaction data
 */
function getRecentTransactions($instructor_id, $limit = 5)
{
    global $conn;

    $sql = "SELECT 
                ie.instructor_share AS amount,
                ie.created_at AS transaction_date,
                c.title AS course_title,
                u.first_name, 
                u.last_name,
                u.profile_pic AS profile_image,
                CASE 
                    WHEN ie.status = 'Available' OR ie.status = 'Pending' THEN 'Incoming' 
                    ELSE ie.status 
                END AS transaction_type
            FROM instructor_earnings ie
            JOIN courses c ON ie.course_id = c.course_id
            JOIN course_payments cp ON ie.payment_id = cp.payment_id
            JOIN enrollments e ON cp.enrollment_id = e.enrollment_id
            JOIN users u ON e.user_id = u.user_id
            WHERE ie.instructor_id = ?
            ORDER BY ie.created_at DESC
            LIMIT ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $instructor_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }

    return $transactions;
}

/**
 * Get revenue breakdown by category
 * 
 * @param int $instructor_id The instructor's ID
 * @return array Array of category revenue data
 */
function getRevenueByCategory($instructor_id)
{
    global $conn;

    $sql = "SELECT 
                cat.name AS category_name,
                COALESCE(SUM(ie.instructor_share), 0) AS category_earnings
            FROM instructor_earnings ie
            JOIN courses c ON ie.course_id = c.course_id
            JOIN subcategories subcat ON c.subcategory_id = subcat.subcategory_id
            JOIN categories cat ON subcat.category_id = cat.category_id
            WHERE ie.instructor_id = ?
            GROUP BY cat.category_id, cat.name
            ORDER BY category_earnings DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $instructor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $categories = [];
    $total_earnings = 0;

    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
        $total_earnings += $row['category_earnings'];
    }

    // Calculate percentages
    foreach ($categories as &$category) {
        $category['percentage'] = ($total_earnings > 0)
            ? round(($category['category_earnings'] / $total_earnings) * 100, 1)
            : 0;
    }

    return $categories;
}

/**
 * Get current month statistics
 * 
 * @param int $instructor_id The instructor's ID
 * @return array Array of current month statistics
 */
function getCurrentMonthStats($instructor_id)
{
    global $conn;

    // Current month earnings and enrollment count
    $sql = "SELECT 
                COALESCE(SUM(ie.instructor_share), 0) AS current_month_earnings,
                COUNT(DISTINCT cp.payment_id) AS enrollment_count
            FROM instructor_earnings ie
            JOIN course_payments cp ON ie.payment_id = cp.payment_id
            WHERE ie.instructor_id = ? 
                AND MONTH(ie.created_at) = MONTH(CURRENT_DATE())
                AND YEAR(ie.created_at) = YEAR(CURRENT_DATE())";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $instructor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_stats = $result->fetch_assoc();

    // Previous month earnings
    $sql = "SELECT 
                COALESCE(SUM(instructor_share), 0) AS previous_month_earnings
            FROM instructor_earnings
            WHERE instructor_id = ? 
                AND MONTH(created_at) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
                AND YEAR(created_at) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $instructor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $previous_stats = $result->fetch_assoc();

    // Calculate average per course
    $sql = "SELECT 
                COUNT(DISTINCT ie.course_id) AS course_count
            FROM instructor_earnings ie
            WHERE ie.instructor_id = ? 
                AND MONTH(ie.created_at) = MONTH(CURRENT_DATE())
                AND YEAR(ie.created_at) = YEAR(CURRENT_DATE())";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $instructor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $course_stats = $result->fetch_assoc();

    $current_month_earnings = $current_stats['current_month_earnings'] ?? 0;
    $previous_month_earnings = $previous_stats['previous_month_earnings'] ?? 0;
    $enrollment_count = $current_stats['enrollment_count'] ?? 0;
    $course_count = $course_stats['course_count'] ?? 1; // Avoid division by zero

    // Calculate percentage change
    $percentage_change = 0;
    if ($previous_month_earnings > 0) {
        $percentage_change = (($current_month_earnings - $previous_month_earnings) / $previous_month_earnings) * 100;
    }

    return [
        'current_month_earnings' => $current_month_earnings,
        'previous_month_earnings' => $previous_month_earnings,
        'enrollment_count' => $enrollment_count,
        'avg_per_course' => ($course_count > 0) ? ($current_month_earnings / $course_count) : 0,
        'avg_per_student' => ($enrollment_count > 0) ? ($current_month_earnings / $enrollment_count) : 0,
        'percentage_change' => round($percentage_change, 1)
    ];
}

/**
 * Get instructor payment method information
 * 
 * @param int $instructor_id The instructor's ID
 * @return array|null Payment method data or null if none exists
 */
function getPaymentMethod($instructor_id)
{
    global $conn;

    $sql = "SELECT 
                provider,
                last_four,
                card_type,
                expiry_date,
                status
            FROM instructor_payment_methods
            WHERE instructor_id = ? AND is_default = 1
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $instructor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return null;
}

/**
 * Get pending payout release date
 * 
 * @param int $instructor_id The instructor's ID
 * @return string|null The estimated release date for pending earnings
 */
function getPendingPayoutDate($instructor_id)
{
    global $conn;

    // Get the earliest pending earnings that are not yet available
    $sql = "SELECT 
                DATE_ADD(created_at, INTERVAL 32 DAY) AS available_date
            FROM instructor_earnings
            WHERE instructor_id = ? AND status = 'Pending'
            ORDER BY available_date ASC
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $instructor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['available_date'];
    }

    return null;
}

/**
 * Get earnings summary (for money history widget)
 * 
 * @param int $instructor_id The instructor's ID
 * @return array Array with income, expenses, and transfers
 */
function getEarningsSummary($instructor_id)
{
    global $conn;

    // Total income (all completed payments)
    $sql = "SELECT 
                COALESCE(SUM(instructor_share), 0) AS total_income
            FROM instructor_earnings
            WHERE instructor_id = ? AND status IN ('Available', 'Withdrawn')";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $instructor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $income = $result->fetch_assoc()['total_income'] ?? 0;

    // Total withdrawals (all completed payouts)
    $sql = "SELECT 
                COALESCE(SUM(amount), 0) AS total_withdrawn
            FROM instructor_payouts
            WHERE instructor_id = ? AND status = 'Completed'";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $instructor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $withdrawals = $result->fetch_assoc()['total_withdrawn'] ?? 0;

    // Platform fees (estimated at 20% of total earnings)
    $sql = "SELECT 
                COALESCE(SUM(platform_fee), 0) AS total_fees
            FROM instructor_earnings
            WHERE instructor_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $instructor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $fees = $result->fetch_assoc()['total_fees'] ?? 0;

    return [
        'income' => $income,
        'fees' => $fees,
        'withdrawals' => $withdrawals
    ];
}

// Fetch all the required data for the earnings overview page
$available_balance = getAvailableBalance($instructor_id);
$pending_earnings = getPendingEarnings($instructor_id);
$lifetime_earnings = getLifetimeEarnings($instructor_id);
$daily_earnings = getDailyEarnings($instructor_id);
$monthly_earnings = getMonthlyEarnings($instructor_id);
$top_courses = getTopPerformingCourses($instructor_id, 5);
$category_breakdown = getRevenueByCategory($instructor_id);
$current_month_stats = getCurrentMonthStats($instructor_id);
$payment_method = getPaymentMethod($instructor_id);
$pending_payout_date = getPendingPayoutDate($instructor_id);
$recent_transactions = getRecentTransactions($instructor_id, 5);
$earnings_summary = getEarningsSummary($instructor_id);

// Format currency values
function formatCurrency($amount)
{
    return '₵' . number_format($amount, 2);
}

// Prepare chart data for daily earnings
$days = [];
$daily_amounts = [];
foreach ($daily_earnings as $data) {
    $days[] = $data['day_label'];
    $daily_amounts[] = $data['daily_earnings'];
}
// Convert to JSON for use in JavaScript charts
$days_json = json_encode($days);
$daily_amounts_json = json_encode($daily_amounts);

// Prepare chart data for monthly earnings
$months = [];
$earnings = [];
foreach ($monthly_earnings as $data) {
    $months[] = $data['month_name'];
    $earnings[] = $data['monthly_earnings'];
}

// Convert to JSON for use in JavaScript charts
$months_json = json_encode($months);
$earnings_json = json_encode($earnings);

// Prepare category data for pie chart
$category_names = [];
$category_values = [];
$category_percentages = [];
foreach ($category_breakdown as $category) {
    $category_names[] = $category['category_name'];
    $category_values[] = $category['category_earnings'];
    $category_percentages[] = $category['percentage'];
}

$category_names_json = json_encode($category_names);
$category_values_json = json_encode($category_values);
$category_percentages_json = json_encode($category_percentages);
?>
<!-- earnings.php --> 
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
                                <h4 class="page-title">Earnings</h4>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    <div class="row">
                        <div class="col-xxl-9">
                            <div class="row">
                                <!-- Available Balance Card -->
                                <div class="col-xl-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="dropdown float-end">
                                                <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="mdi mdi-dots-horizontal"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <!-- item-->
                                                    <a href="javascript:void(0);" class="dropdown-item"><i class="mdi mdi-cached me-1"></i>Refresh</a>
                                                </div>
                                            </div>
                                            <div class="d-flex">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar-sm rounded">
                                                        <span class="avatar-title bg-primary-lighten h3 my-0 text-primary rounded">
                                                            <i class="mdi mdi-wallet-outline"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h4 class="mt-0 mb-1 font-20"><?php echo formatCurrency($available_balance); ?></h4>
                                                    <p class="mb-0 text-muted">
                                                        <?php if ($current_month_stats['percentage_change'] > 0): ?>
                                                            <i class="mdi mdi-arrow-up-bold text-success"></i>
                                                        <?php else: ?>
                                                            <i class="mdi mdi-arrow-down-bold text-danger"></i>
                                                        <?php endif; ?>
                                                        <?php echo abs($current_month_stats['percentage_change']); ?>% This Month
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="row align-items-end justify-content-between mt-3">
                                                <div class="col-sm-6">
                                                    <h4 class="mt-0 text-muted fw-semibold mb-1">Available Balance</h4>
                                                    <p class="text-muted mb-0">Ready to withdraw</p>
                                                </div>
                                                <div class="col-sm-5">
                                                    <div class="text-end">
                                                        <a href="payout-settings.php" class="btn btn-sm btn-primary">Withdraw</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pending Earnings Card -->
                                <div class="col-xl-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="dropdown float-end">
                                                <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="mdi mdi-dots-horizontal"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <!-- item-->
                                                    <a href="javascript:void(0);" class="dropdown-item"><i class="mdi mdi-cached me-1"></i>Refresh</a>
                                                </div>
                                            </div>
                                            <div class="d-flex">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar-sm rounded">
                                                        <span class="avatar-title bg-primary-lighten h3 my-0 text-primary rounded">
                                                            <i class="mdi mdi-clock-outline"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h4 class="mt-0 mb-1 font-20"><?php echo formatCurrency($pending_earnings); ?></h4>
                                                    <p class="mb-0 text-muted">In holding period</p>
                                                </div>
                                            </div>

                                            <div class="row align-items-end justify-content-between mt-3">
                                                <div class="col-sm-8">
                                                    <h4 class="mt-0 text-muted fw-semibold mb-1">Pending Earnings</h4>
                                                    <?php if ($pending_payout_date): ?>
                                                        <p class="text-muted mb-0">Available on <?php echo date('M d, Y', strtotime($pending_payout_date)); ?></p>
                                                    <?php else: ?>
                                                        <p class="text-muted mb-0">No pending payouts</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Lifetime Earnings Card -->
                                <div class="col-xl-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="dropdown float-end">
                                                <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="mdi mdi-dots-horizontal"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <!-- item-->
                                                    <a href="javascript:void(0);" class="dropdown-item"><i class="mdi mdi-cached me-1"></i>Refresh</a>
                                                </div>
                                            </div>
                                            <div class="d-flex">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar-sm rounded">
                                                        <span class="avatar-title bg-primary-lighten h3 my-0 text-primary rounded">
                                                            ₵
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h4 class="mt-0 mb-1 font-20"><?php echo formatCurrency($lifetime_earnings); ?></h4>
                                                    <p class="mb-0 text-muted">Total earnings to date</p>
                                                </div>
                                            </div>

                                            <div class="row align-items-end justify-content-between mt-3">
                                                <div class="col-sm-6">
                                                    <h4 class="mt-0 text-muted fw-semibold mb-1">Lifetime Earnings</h4>
                                                    <p class="text-muted mb-0">Since you joined</p>
                                                </div>
                                                <div class="col-sm-5">
                                                    <div class="text-end">
                                                        <a href="earnings-history.php" class="btn btn-sm btn-outline-primary">View History</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Monthly Earnings Chart -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="align-items-center d-sm-flex justify-content-sm-between mb-3">
                                                <h4 class="header-title mb-0">Earnings Trend</h4>
                                                <ul class="nav nav-pills bg-nav-pills p-1 rounded" id="pills-tab" role="tablist">
                                                    <li class="nav-item" role="presentation">
                                                        <a href="#day-view" data-bs-toggle="tab" aria-expanded="false" class="nav-link py-1 active">
                                                            <span class="">Daily</span>
                                                        </a>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <a href="#month-view" data-bs-toggle="tab" aria-expanded="false" class="nav-link py-1">
                                                            <span class="">Monthly</span>
                                                        </a>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <a href="#quarter-view" data-bs-toggle="tab" aria-expanded="false" class="nav-link py-1">
                                                            <span class="">Quarterly</span>
                                                        </a>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <a href="#year-view" data-bs-toggle="tab" aria-expanded="false" class="nav-link py-1">
                                                            <span class="">Yearly</span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>

                                            <div class="tab-content" id="pills-tabContent">
                                                <div class="tab-pane fade show active" id="day-view" role="tabpanel">
                                                    <div dir="ltr">
                                                        <div id="daily-earnings-chart" class="apex-charts" data-colors="#0acf97"></div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane fade" id="month-view" role="tabpanel">
                                                    <div dir="ltr">
                                                        <div id="monthly-earnings-chart" class="apex-charts" data-colors="#0acf97"></div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane fade" id="quarter-view" role="tabpanel">
                                                    <div dir="ltr">
                                                        <div id="quarterly-earnings-chart" class="apex-charts" data-colors="#0acf97"></div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane fade" id="year-view" role="tabpanel">
                                                    <div dir="ltr">
                                                        <div id="yearly-earnings-chart" class="apex-charts" data-colors="#0acf97"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Rows for Money History and Transactions -->
                            <div class="row">
                                <div class="col-md-6 col-xxl-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h4 class="header-title">Earnings Summary</h4>
                                                <div class="dropdown">
                                                    <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="mdi mdi-dots-vertical"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a href="javascript:void(0);" class="dropdown-item"><i class="mdi mdi-cached me-1"></i>Refresh</a>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="border border-light p-3 rounded mb-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <p class="font-18 mb-1">Income</p>
                                                        <h3 class="text-primary my-0"><?php echo formatCurrency($earnings_summary['income']); ?></h3>
                                                    </div>
                                                    <div class="avatar-sm">
                                                        <span class="avatar-title bg-primary rounded-circle h3 my-0">
                                                            <i class="mdi mdi-arrow-up-bold-outline"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="border border-light p-3 rounded mb-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <p class="font-18 mb-1">Platform Fees</p>
                                                        <h3 class="text-danger my-0"><?php echo formatCurrency($earnings_summary['fees']); ?></h3>
                                                    </div>
                                                    <div class="avatar-sm">
                                                        <span class="avatar-title bg-danger rounded-circle h3 my-0">
                                                            <i class="mdi mdi-arrow-down-bold-outline"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="border border-light p-3 rounded">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <p class="font-18 mb-1">Withdrawals</p>
                                                        <h3 class="text-success my-0"><?php echo formatCurrency($earnings_summary['withdrawals']); ?></h3>
                                                    </div>
                                                    <div class="avatar-sm">
                                                        <span class="avatar-title bg-success rounded-circle h3 my-0">
                                                            <i class="mdi mdi-swap-horizontal"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-xxl-8">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h4 class="header-title mb-0">Recent Transactions</h4>
                                                <div>
                                                    <select class="form-select form-select-sm" aria-label="Time period select">
                                                        <option selected>This Month</option>
                                                        <option value="1">Last Month</option>
                                                        <option value="2">Last 3 Months</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-centered table-nowrap mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">Student</th>
                                                            <th scope="col">Course</th>
                                                            <th scope="col">Date</th>
                                                            <th scope="col">Status</th>
                                                            <th scope="col">Amount</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (!empty($recent_transactions)): ?>
                                                            <?php foreach ($recent_transactions as $transaction): ?>
                                                                <tr>
                                                                    <td>
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="flex-shrink-0">
                                                                                <?php if ($transaction['profile_image']): ?>
                                                                                    <img class="rounded-circle" src="../uploads/profile/<?php echo htmlspecialchars($transaction['profile_image']); ?>" alt="Student image" width="33">
                                                                                <?php else: ?>
                                                                                    <div class="avatar-sm">
                                                                                        <span class="avatar-title rounded-circle bg-light text-primary">
                                                                                            <?php echo substr($transaction['first_name'], 0, 1) . substr($transaction['last_name'], 0, 1); ?>
                                                                                        </span>
                                                                                    </div>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                            <div class="flex-grow-1 ms-2">
                                                                                <?php echo htmlspecialchars($transaction['first_name'] . ' ' . $transaction['last_name']); ?>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td><?php echo htmlspecialchars(substr($transaction['course_title'], 0, 25) . (strlen($transaction['course_title']) > 25 ? '...' : '')); ?></td>
                                                                    <td><i class="uil uil-calender me-1"></i><?php echo date('M d, Y', strtotime($transaction['transaction_date'])); ?></td>
                                                                    <td>
                                                                        <?php if ($transaction['transaction_type'] == 'Incoming'): ?>
                                                                            <span class="badge bg-success-lighten text-success">Incoming</span>
                                                                        <?php elseif ($transaction['transaction_type'] == 'Refunded'): ?>
                                                                            <span class="badge bg-danger-lighten text-danger">Refunded</span>
                                                                        <?php else: ?>
                                                                            <span class="badge bg-warning-lighten text-warning">Pending</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td>
                                                                        <?php if ($transaction['transaction_type'] == 'Incoming'): ?>
                                                                            <span class="text-success fw-semibold">+ <?php echo formatCurrency($transaction['amount']); ?></span>
                                                                        <?php elseif ($transaction['transaction_type'] == 'Refunded'): ?>
                                                                            <span class="text-danger fw-semibold">- <?php echo formatCurrency($transaction['amount']); ?></span>
                                                                        <?php else: ?>
                                                                            <span class="text-warning fw-semibold"><?php echo formatCurrency($transaction['amount']); ?></span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <tr>
                                                                <td colspan="5" class="text-center">No recent transactions found</td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <?php if (!empty($recent_transactions)): ?>
                                                <div class="text-center mt-3">
                                                    <a href="earnings-history.php" class="btn btn-sm btn-link">View All Transactions <i class="mdi mdi-arrow-right ms-1"></i></a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </div>

                        <div class="col-xxl-3">
                            <div class="row">
                                <div class="col-md-6 col-xxl-12">
                                    <!-- Payment Method Card -->
                                    <?php if ($payment_method): ?>
                                        <div class="card bg-primary card-bg-img" style="background-image: url(assets/images/bg-pattern.png);">
                                            <div class="card-body">
                                                <span class="float-end text-white-50 display-5 mt-n1">
                                                    <?php if ($payment_method['provider'] == 'Stripe'): ?>
                                                        <i class="mdi mdi-credit-card"></i>
                                                    <?php elseif ($payment_method['provider'] == 'PayPal'): ?>
                                                        <i class="mdi mdi-paypal"></i>
                                                    <?php else: ?>
                                                        <i class="mdi mdi-bank"></i>
                                                    <?php endif; ?>
                                                </span>
                                                <h4 class="text-white">Payment Method</h4>

                                                <div class="row align-items-center mt-4">
                                                    <div class="col-12 text-white font-12">
                                                        <?php if ($payment_method['last_four']): ?>
                                                            <span class="me-1">••••</span>
                                                            <span class="me-1">••••</span>
                                                            <span class="me-1">••••</span>
                                                            <span class="fw-bold"><?php echo $payment_method['last_four']; ?></span>
                                                        <?php else: ?>
                                                            <?php echo $payment_method['provider']; ?> Account
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <div class="row mt-4">
                                                    <?php if ($payment_method['expiry_date']): ?>
                                                        <div class="col-4">
                                                            <p class="text-white-50 font-16 mb-1">Expiry Date</p>
                                                            <h4 class="text-white my-0"><?php echo $payment_method['expiry_date']; ?></h4>
                                                        </div>
                                                    <?php endif; ?>

                                                    <div class="col-4">
                                                        <p class="text-white-50 font-16 mb-1">Type</p>
                                                        <h4 class="text-white my-0"><?php echo $payment_method['card_type'] ?? $payment_method['provider']; ?></h4>
                                                    </div>
                                                    <div class="col-4">
                                                        <p class="text-white-50 font-16 mb-1">Status</p>
                                                        <h4 class="text-white my-0"><?php echo $payment_method['status']; ?></h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="text-center">
                                                    <i class="mdi mdi-credit-card-outline text-muted font-24"></i>
                                                    <h4>No Payment Method</h4>
                                                    <p class="text-muted">Add a payment method to receive your earnings</p>
                                                    <a href="payout-settings.php" class="btn btn-primary">Add Payment Method</a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6 col-xxl-12">
                                    <!-- Current Month Stats -->
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h4 class="header-title">This Month's Stats</h4>
                                            </div>

                                            <div class="d-flex align-items-center mb-3">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar-sm rounded">
                                                        <span class="avatar-title bg-success-lighten text-success border border-success rounded-circle h3 my-0">
                                                            <i class="mdi mdi-calendar-check"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h4 class="mt-0 mb-1 font-16 fw-semibold">Revenue</h4>
                                                    <p class="mb-0"><?php echo formatCurrency($current_month_stats['current_month_earnings']); ?></p>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-center mb-3">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar-sm rounded">
                                                        <span class="avatar-title bg-info-lighten text-info border border-info rounded-circle h3 my-0">
                                                            <i class="mdi mdi-account-multiple"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h4 class="mt-0 mb-1 font-16 fw-semibold">Enrollments</h4>
                                                    <p class="mb-0"><?php echo $current_month_stats['enrollment_count']; ?></p>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-center mb-3">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar-sm rounded">
                                                        <span class="avatar-title bg-warning-lighten text-warning border border-warning rounded-circle h3 my-0">
                                                            <i class="mdi mdi-book-education-outline"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h4 class="mt-0 mb-1 font-16 fw-semibold">Avg. per Course</h4>
                                                    <p class="mb-0"><?php echo formatCurrency($current_month_stats['avg_per_course']); ?></p>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar-sm rounded">
                                                        <span class="avatar-title bg-primary-lighten text-primary border border-primary rounded-circle h3 my-0">
                                                            <i class="mdi mdi-account"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h4 class="mt-0 mb-1 font-16 fw-semibold">Avg. per Student</h4>
                                                    <p class="mb-0"><?php echo formatCurrency($current_month_stats['avg_per_student']); ?></p>
                                                </div>
                                            </div>

                                            <hr class="my-3">

                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">
                                                    <h4 class="mt-0 mb-1 font-16 fw-semibold">vs. Last Month</h4>
                                                </div>
                                                <div class="text-end">
                                                    <?php if ($current_month_stats['percentage_change'] >= 0): ?>
                                                        <span class="badge bg-success rounded-pill"><?php echo '+' . $current_month_stats['percentage_change']; ?>%</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger rounded-pill"><?php echo $current_month_stats['percentage_change']; ?>%</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Revenue Breakdown by Category -->
                                <div class="col-md-6 col-xxl-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h4 class="header-title">Revenue Breakdown</h4>
                                            </div>

                                            <?php if (!empty($category_breakdown)): ?>
                                                <!-- Just keep ONE div for the chart -->
                                                <div class="text-center mb-3">
                                                    <div id="revenue-breakdown-chart" style="height: 200px;"></div>
                                                </div>

                                                <div class="chart-widget-list">
                                                    <?php foreach ($category_breakdown as $index => $category):
                                                        $colors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary'];
                                                        $color = $colors[$index % count($colors)];
                                                    ?>
                                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                                            <div>
                                                                <i class="mdi mdi-square text-<?php echo $color; ?>"></i>
                                                                <span class="ms-1"><?php echo htmlspecialchars($category['category_name']); ?></span>
                                                            </div>
                                                            <div>
                                                                <span><?php echo formatCurrency($category['category_earnings']); ?></span>
                                                                <small class="ms-2"><?php echo $category['percentage']; ?>%</small>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-center p-4">
                                                    <i class="mdi mdi-chart-pie text-muted font-24"></i>
                                                    <p class="mt-2">No category data available yet</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>




                    <!-- Top Performing Courses -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h4 class="header-title">Top Performing Courses</h4>
                                        <a href="earnings-history.php" class="btn btn-sm btn-link">View All</a>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-centered table-nowrap table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Course</th>
                                                    <th>Sales</th>
                                                    <th>Earnings</th>
                                                    <th>Performance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $highest_earnings = 0;
                                                if (!empty($top_courses)) {
                                                    $highest_earnings = $top_courses[0]['total_earnings'];
                                                }

                                                foreach ($top_courses as $course):
                                                    $percentage = ($highest_earnings > 0) ? ($course['total_earnings'] / $highest_earnings) * 100 : 0;
                                                ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <?php if ($course['thumbnail']): ?>
                                                                    <img src="../uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>" alt="course image" class="rounded me-3" height="48">
                                                                <?php else: ?>
                                                                    <div class="avatar-sm me-3">
                                                                        <span class="avatar-title bg-light text-primary rounded">
                                                                            <i class="mdi mdi-book-education-outline font-16"></i>
                                                                        </span>
                                                                    </div>
                                                                <?php endif; ?>
                                                                <div>
                                                                    <h5 class="font-14 mb-1"><?php echo htmlspecialchars($course['title']); ?></h5>
                                                                    <span class="text-muted font-13"><?php echo $course['sale_count']; ?> enrollments</span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td><?php echo $course['sale_count']; ?></td>
                                                        <td><?php echo formatCurrency($course['total_earnings']); ?></td>
                                                        <td>
                                                            <div class="progress" style="height: 5px;">
                                                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $percentage; ?>%" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>

                                                <?php if (empty($top_courses)): ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center">No course data available yet</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Include Chart.js or ApexCharts for rendering charts -->

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // Monthly earnings chart data
                            var months = <?php echo $months_json; ?>;
                            var earnings = <?php echo $earnings_json; ?>;

                            // Category breakdown data
                            var categoryNames = <?php echo $category_names_json; ?>;
                            var categoryValues = <?php echo $category_values_json; ?>;

                            // Add this to your chart initialization script
                            // Daily earnings chart data
                            var days = <?php echo $days_json; ?>;
                            var dailyAmounts = <?php echo $daily_amounts_json; ?>;

                            // Initialize daily earnings chart
                            if (document.getElementById('daily-earnings-chart')) {
                                var options = {
                                    chart: {
                                        height: 350,
                                        type: 'area',
                                        toolbar: {
                                            show: false
                                        }
                                    },
                                    dataLabels: {
                                        enabled: false
                                    },
                                    stroke: {
                                        curve: 'smooth',
                                        width: 2
                                    },
                                    series: [{
                                        name: 'Daily Earnings',
                                        data: dailyAmounts
                                    }],
                                    xaxis: {
                                        categories: days,
                                        tickAmount: 10
                                    },
                                    tooltip: {
                                        y: {
                                            formatter: function(val) {
                                                return '₵' + val.toFixed(2);
                                            }
                                        }
                                    },
                                    colors: ['#0acf97'],
                                    fill: {
                                        type: 'gradient',
                                        gradient: {
                                            shadeIntensity: 1,
                                            opacityFrom: 0.7,
                                            opacityTo: 0.3,
                                            stops: [0, 90, 100]
                                        }
                                    }
                                };

                                var chart = new ApexCharts(
                                    document.getElementById('daily-earnings-chart'),
                                    options
                                );
                                chart.render();
                            }

                            // Initialize monthly earnings chart
                            if (document.getElementById('monthly-earnings-chart')) {
                                var options = {
                                    chart: {
                                        height: 350,
                                        type: 'area',
                                        toolbar: {
                                            show: false
                                        }
                                    },
                                    dataLabels: {
                                        enabled: false
                                    },
                                    stroke: {
                                        curve: 'smooth',
                                        width: 2
                                    },
                                    series: [{
                                        name: 'Earnings',
                                        data: earnings
                                    }],
                                    xaxis: {
                                        categories: months
                                    },
                                    tooltip: {
                                        y: {
                                            formatter: function(val) {
                                                return '₵' + val.toFixed(2);
                                            }
                                        }
                                    },
                                    colors: ['#0acf97'],
                                    fill: {
                                        type: 'gradient',
                                        gradient: {
                                            shadeIntensity: 1,
                                            opacityFrom: 0.7,
                                            opacityTo: 0.3,
                                            stops: [0, 90, 100]
                                        }
                                    }
                                };

                                var chart = new ApexCharts(
                                    document.getElementById('monthly-earnings-chart'),
                                    options
                                );
                                chart.render();
                            }

                            // Initialize quarterly earnings chart
                            if (document.getElementById('quarterly-earnings-chart')) {
                                // Group monthly data into quarters based on actual quarters
                                var quarterlyData = [0, 0, 0, 0]; // Initialize with zeros for Q1, Q2, Q3, Q4
                                var quarterlyLabels = ['Q1', 'Q2', 'Q3', 'Q4'];

                                // Map month names to quarter indices (0-based)
                                var monthToQuarter = {
                                    'Jan': 0,
                                    'Feb': 0,
                                    'Mar': 0, // Q1
                                    'Apr': 1,
                                    'May': 1,
                                    'Jun': 1, // Q2
                                    'Jul': 2,
                                    'Aug': 2,
                                    'Sep': 2, // Q3
                                    'Oct': 3,
                                    'Nov': 3,
                                    'Dec': 3 // Q4
                                };

                                // Aggregate earnings by actual quarter
                                for (var i = 0; i < months.length; i++) {
                                    var quarter = monthToQuarter[months[i]];
                                    if (quarter !== undefined) {
                                        quarterlyData[quarter] += earnings[i];
                                    }
                                }

                                var options = {
                                    chart: {
                                        height: 350,
                                        type: 'area',
                                        toolbar: {
                                            show: false
                                        }
                                    },
                                    dataLabels: {
                                        enabled: false
                                    },
                                    stroke: {
                                        curve: 'smooth',
                                        width: 2
                                    },
                                    series: [{
                                        name: 'Quarterly Earnings',
                                        data: quarterlyData
                                    }],
                                    xaxis: {
                                        categories: quarterlyLabels
                                    },
                                    tooltip: {
                                        y: {
                                            formatter: function(val) {
                                                return '₵' + val.toFixed(2);
                                            }
                                        }
                                    },
                                    colors: ['#0acf97'],
                                    fill: {
                                        type: 'gradient',
                                        gradient: {
                                            shadeIntensity: 1,
                                            opacityFrom: 0.7,
                                            opacityTo: 0.3,
                                            stops: [0, 90, 100]
                                        }
                                    }
                                };

                                var quarterlyChart = new ApexCharts(
                                    document.getElementById('quarterly-earnings-chart'),
                                    options
                                );
                                quarterlyChart.render();
                            }
                            // Initialize yearly earnings chart
                            if (document.getElementById('yearly-earnings-chart')) {
                                // For yearly, we can sum all monthly earnings for demo purposes
                                var yearlyData = [earnings.reduce((a, b) => a + b, 0)];
                                var yearlyLabels = [new Date().getFullYear().toString()];

                                var options = {
                                    chart: {
                                        height: 350,
                                        type: 'area',
                                        toolbar: {
                                            show: false
                                        }
                                    },
                                    dataLabels: {
                                        enabled: false
                                    },
                                    stroke: {
                                        curve: 'smooth',
                                        width: 2
                                    },
                                    series: [{
                                        name: 'Yearly Earnings',
                                        data: yearlyData
                                    }],
                                    xaxis: {
                                        categories: yearlyLabels
                                    },
                                    tooltip: {
                                        y: {
                                            formatter: function(val) {
                                                return '₵' + val.toFixed(2);
                                            }
                                        }
                                    },
                                    colors: ['#0acf97'],
                                    fill: {
                                        type: 'gradient',
                                        gradient: {
                                            shadeIntensity: 1,
                                            opacityFrom: 0.7,
                                            opacityTo: 0.3,
                                            stops: [0, 90, 100]
                                        }
                                    }
                                };

                                var yearlyChart = new ApexCharts(
                                    document.getElementById('yearly-earnings-chart'),
                                    options
                                );
                                yearlyChart.render();
                            }

                            // Initialize revenue breakdown chart - make sure this is only called once
                            if (document.getElementById('revenue-breakdown-chart') && categoryNames && categoryNames.length > 0) {
                                var options = {
                                    chart: {
                                        type: 'donut',
                                        height: 200
                                    },
                                    series: categoryValues,
                                    labels: categoryNames,
                                    legend: {
                                        show: false
                                    },
                                    dataLabels: {
                                        enabled: false
                                    },
                                    colors: ['#727cf5', '#0acf97', '#6c757d', '#fa5c7c', '#ffbc00', '#39afd1'],
                                    tooltip: {
                                        y: {
                                            formatter: function(val) {
                                                return '₵' + val.toFixed(2);
                                            }
                                        }
                                    }
                                };

                                var chart = new ApexCharts(
                                    document.getElementById('revenue-breakdown-chart'),
                                    options
                                );
                                chart.render();
                            }
                        });
                    </script>


                </div>
                <!-- container -->

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


    <?php include '../includes/instructor-darkmode.php'; ?>

    <!-- bundle -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.min.js"></script>

    <!-- third party js -->
    <script src="assets/js/vendor/apexcharts.min.js"></script>
    <script src="assets/js/vendor/jquery-jvectormap-1.2.2.min.js"></script>
    <script src="assets/js/vendor/jquery-jvectormap-world-mill-en.js"></script>
    <!-- third party js ends -->
</body>


</html>