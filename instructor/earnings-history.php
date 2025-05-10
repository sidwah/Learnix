<?php
require '../backend/session_start.php';

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    error_log("Unauthorized access attempt detected: " . json_encode($_SERVER));
    header('Location: landing.php');
    exit;
}

require_once '../backend/config.php';

// Get instructor ID from session
$instructor_id = $_SESSION['instructor_id'];

// Default filter values
$default_start_date = date('Y-m-d', strtotime('-3 months'));
$default_end_date = date('Y-m-d');
$default_status = 'all';
$default_course = 'all';
$default_type = 'all';
$default_sort = 'newest';
$default_limit = 20;

// Get filter parameters from URL or use defaults
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : $default_start_date;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : $default_end_date;
$status = isset($_GET['status']) ? $_GET['status'] : $default_status;
$course_filter = isset($_GET['course']) ? $_GET['course'] : $default_course;
$transaction_type = isset($_GET['type']) ? $_GET['type'] : $default_type;
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : $default_sort;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : $default_limit;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate offset for pagination
$offset = ($page - 1) * $limit;

// If AJAX request, process only data
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

/**
 * Get instructor courses for filter dropdown
 */
function getInstructorCourses($instructor_id)
{
    global $conn;

    $sql = "SELECT c.course_id, c.title 
            FROM courses c
            JOIN course_instructors ci ON c.course_id = ci.course_id
            WHERE ci.instructor_id = ?
            ORDER BY c.title ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $instructor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }

    $stmt->close();
    return $courses;
}

/**
 * Get transaction history with filters
 */
function getTransactionHistory($instructor_id, $filters)
{
    global $conn;

    // Extract filter parameters
    $start_date = $filters['start_date'];
    $end_date = $filters['end_date'];
    $status = $filters['status'];
    $course_id = $filters['course_id'];
    $type = $filters['type'];
    $sort_by = $filters['sort_by'];
    $limit = $filters['limit'];
    $offset = $filters['offset'];

    // Build the base query
    $sql = "SELECT 
                ie.earning_id,
                ie.instructor_share AS amount,
                ie.platform_fee,
                ie.status,
                ie.created_at AS transaction_date,
                ie.available_at,
                c.course_id,
                c.title AS course_title,
                c.thumbnail,
                u.user_id,
                u.first_name, 
                u.last_name,
                u.profile_pic AS profile_image,
                cp.payment_id,
                CASE 
                    WHEN ie.status = 'Available' THEN 'Available'
                    WHEN ie.status = 'Pending' THEN 'Pending Release' 
                    WHEN ie.status = 'Refunded' THEN 'Refunded'
                END AS status_label,
                CASE 
                    WHEN ie.status IN ('Available', 'Pending') THEN 'Incoming' 
                    WHEN ie.status = 'Refunded' THEN 'Refund'
                END AS transaction_type
            FROM instructor_earnings ie
            JOIN courses c ON ie.course_id = c.course_id
            JOIN course_payments cp ON ie.payment_id = cp.payment_id
            JOIN enrollments e ON cp.enrollment_id = e.enrollment_id
            JOIN users u ON e.user_id = u.user_id
            JOIN course_instructors ci ON c.course_id = ci.course_id
            WHERE ci.instructor_id = ? AND ie.status IN ('Available', 'Pending', 'Refunded')";

    // Add filters
    if ($start_date && $end_date) {
        $sql .= " AND DATE(ie.created_at) BETWEEN ? AND ?";
    }

    if ($status !== 'all') {
        $sql .= " AND ie.status = ?";
    }

    if ($course_id !== 'all') {
        $sql .= " AND c.course_id = ?";
    }

    if ($type !== 'all') {
        if ($type === 'income') {
            $sql .= " AND ie.status IN ('Available', 'Pending')";
        } elseif ($type === 'refund') {
            $sql .= " AND ie.status = 'Refunded'";
        }
    }

    // Add sorting
    if ($sort_by === 'newest') {
        $sql .= " ORDER BY ie.created_at DESC";
    } elseif ($sort_by === 'oldest') {
        $sql .= " ORDER BY ie.created_at ASC";
    } elseif ($sort_by === 'highest') {
        $sql .= " ORDER BY ie.instructor_share DESC";
    } elseif ($sort_by === 'lowest') {
        $sql .= " ORDER BY ie.instructor_share ASC";
    }

    // Add pagination
    $sql .= " LIMIT ? OFFSET ?";

    // Prepare statement
    $stmt = $conn->prepare($sql);

    // Create parameter array and types string
    $params = [$instructor_id];
    $types = "i";

    if ($start_date && $end_date) {
        $params[] = $start_date;
        $params[] = $end_date;
        $types .= "ss";
    }

    if ($status !== 'all') {
        $params[] = $status;
        $types .= "s";
    }

    if ($course_id !== 'all') {
        $params[] = $course_id;
        $types .= "i";
    }

    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    // Bind parameters
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }

    $stmt->close();
    return $transactions;
}

/**
 * Get total count of transactions (for pagination)
 */
function getTransactionCount($instructor_id, $filters)
{
    global $conn;

    // Extract filter parameters
    $start_date = $filters['start_date'];
    $end_date = $filters['end_date'];
    $status = $filters['status'];
    $course_id = $filters['course_id'];
    $type = $filters['type'];

    // Build the base query
    $sql = "SELECT COUNT(*) as total
            FROM instructor_earnings ie
            JOIN courses c ON ie.course_id = c.course_id
            JOIN course_payments cp ON ie.payment_id = cp.payment_id
            JOIN enrollments e ON cp.enrollment_id = e.enrollment_id
            JOIN users u ON e.user_id = u.user_id
            JOIN course_instructors ci ON c.course_id = ci.course_id
            WHERE ci.instructor_id = ? AND ie.status IN ('Available', 'Pending', 'Refunded')";

    // Add filters
    if ($start_date && $end_date) {
        $sql .= " AND DATE(ie.created_at) BETWEEN ? AND ?";
    }

    if ($status !== 'all') {
        $sql .= " AND ie.status = ?";
    }

    if ($course_id !== 'all') {
        $sql .= " AND c.course_id = ?";
    }

    if ($type !== 'all') {
        if ($type === 'income') {
            $sql .= " AND ie.status IN ('Available', 'Pending')";
        } elseif ($type === 'refund') {
            $sql .= " AND ie.status = 'Refunded'";
        }
    }

    // Prepare statement
    $stmt = $conn->prepare($sql);

    // Create parameter array and types string
    $params = [$instructor_id];
    $types = "i";

    if ($start_date && $end_date) {
        $params[] = $start_date;
        $params[] = $end_date;
        $types .= "ss";
    }

    if ($status !== 'all') {
        $params[] = $status;
        $types .= "s";
    }

    if ($course_id !== 'all') {
        $params[] = $course_id;
        $types .= "i";
    }

    // Bind parameters
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $stmt->close();
    return $row['total'] ?? 0;
}

/**
 * Get summary statistics for filtered transactions
 */
function getTransactionSummary($instructor_id, $filters)
{
    global $conn;

    // Extract filter parameters
    $start_date = $filters['start_date'];
    $end_date = $filters['end_date'];
    $status = $filters['status'];
    $course_id = $filters['course_id'];
    $type = $filters['type'];

    // Build the base query
    $sql = "SELECT 
                SUM(CASE WHEN ie.status IN ('Available', 'Pending') THEN ie.instructor_share ELSE 0 END) as total_income,
                SUM(CASE WHEN ie.status = 'Refunded' THEN ie.instructor_share ELSE 0 END) as total_refunds,
                SUM(ie.platform_fee) as total_fees,
                COUNT(DISTINCT ie.earning_id) as transaction_count,
                COUNT(DISTINCT c.course_id) as course_count
            FROM instructor_earnings ie
            JOIN courses c ON ie.course_id = c.course_id
            JOIN course_payments cp ON ie.payment_id = cp.payment_id
            JOIN enrollments e ON cp.enrollment_id = e.enrollment_id
            JOIN users u ON e.user_id = u.user_id
            JOIN course_instructors ci ON c.course_id = ci.course_id
            WHERE ci.instructor_id = ? AND ie.status IN ('Available', 'Pending', 'Refunded')";

    // Add filters
    if ($start_date && $end_date) {
        $sql .= " AND DATE(ie.created_at) BETWEEN ? AND ?";
    }

    if ($status !== 'all') {
        $sql .= " AND ie.status = ?";
    }

    if ($course_id !== 'all') {
        $sql .= " AND c.course_id = ?";
    }

    if ($type !== 'all') {
        if ($type === 'income') {
            $sql .= " AND ie.status IN ('Available', 'Pending')";
        } elseif ($type === 'refund') {
            $sql .= " AND ie.status = 'Refunded'";
        }
    }

    // Prepare statement
    $stmt = $conn->prepare($sql);

    // Create parameter array and types string
    $params = [$instructor_id];
    $types = "i";

    if ($start_date && $end_date) {
        $params[] = $start_date;
        $params[] = $end_date;
        $types .= "ss";
    }

    if ($status !== 'all') {
        $params[] = $status;
        $types .= "s";
    }

    if ($course_id !== 'all') {
        $params[] = $course_id;
        $types .= "i";
    }

    // Bind parameters
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize default values to avoid null errors
    $summary = [
        'total_income' => 0,
        'total_refunds' => 0,
        'total_fees' => 0,
        'transaction_count' => 0,
        'course_count' => 0
    ];

    // Merge with any results from the database
    if ($row = $result->fetch_assoc()) {
        foreach ($row as $key => $value) {
            $summary[$key] = $value !== null ? $value : 0;
        }
    }

    $stmt->close();
    return $summary;
}

// Format currency values
function formatCurrency($amount)
{
    $amount = is_null($amount) ? 0 : (float)$amount;
    return '₵' . number_format($amount, 2);
}

// Get instructor courses for filter dropdown
$instructor_courses = getInstructorCourses($instructor_id);

// Prepare filter array
$filters = [
    'start_date' => $start_date,
    'end_date' => $end_date,
    'status' => $status,
    'course_id' => $course_filter,
    'type' => $transaction_type,
    'sort_by' => $sort_by,
    'limit' => $limit,
    'offset' => $offset
];

// Get transactions based on filters
$transactions = getTransactionHistory($instructor_id, $filters);

// Get total count for pagination
$total_transactions = getTransactionCount($instructor_id, $filters);

// Calculate total pages
$total_pages = ceil($total_transactions / $limit);

// Get summary statistics
$summary = getTransactionSummary($instructor_id, $filters);

// Build the URL for pagination and filtering
function buildUrl($params = [])
{
    global $start_date, $end_date, $status, $course_filter, $transaction_type, $sort_by, $limit;

    $url = '?';
    $query_params = [
        'start_date' => $params['start_date'] ?? $start_date,
        'end_date' => $params['end_date'] ?? $end_date,
        'status' => $params['status'] ?? $status,
        'course' => $params['course'] ?? $course_filter,
        'type' => $params['type'] ?? $transaction_type,
        'sort' => $params['sort'] ?? $sort_by,
        'limit' => $params['limit'] ?? $limit,
        'page' => $params['page'] ?? 1
    ];

    return $url . http_build_query($query_params);
}

// Check if we need to show an alert based on filter selection
$show_alert = false;
$alert_type = '';
$alert_message = '';

if (count($transactions) === 0) {
    $show_alert = true;
    $alert_type = 'info';
    $alert_message = 'No transactions found for the selected filters. Try adjusting your filter criteria.';
}

// If this is an AJAX request, return only the data
if ($is_ajax) {
    $response = [
        'transactions' => $transactions,
        'summary' => $summary,
        'total_transactions' => $total_transactions,
        'total_pages' => $total_pages,
        'current_page' => $page,
        'show_alert' => $show_alert,
        'alert_type' => $alert_type,
        'alert_message' => $alert_message
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Earnings History | Learnix - Instructor Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Track your course earnings history with detailed transaction data." />
    <meta name="author" content="Learnix Team" />
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <link href="assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/vendor/dataTables.bootstrap5.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/vendor/responsive.bootstrap5.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/vendor/buttons.bootstrap5.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/vendor/select.bootstrap5.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/vendor/fixedHeader.bootstrap5.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/vendor/daterangepicker.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <style>
        .custom-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        .status-tooltip {
            cursor: pointer;
        }
        #content-area .card {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="loading" data-layout-color="light" data-leftbar-theme="dark" data-layout-mode="fluid" data-rightbar-onstart="true">
    <div class="wrapper">
        <?php include '../includes/instructor-sidebar.php'; ?>
        <div class="content-page">
            <div class="content">
                <?php include '../includes/instructor-topnavbar.php'; ?>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="earnings.php">Earnings</a></li>
                                        <li class="breadcrumb-item active">History</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Earnings History</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <form id="filter-form">
                                        <div class="row g-2">
                                            <div class="col-lg-3 col-md-6">
                                                <div class="mb-3">
                                                    <label for="daterange" class="form-label">Date Range</label>
                                                    <input type="text" class="form-control date" id="daterange" name="daterange"
                                                        data-toggle="date-picker" data-cancel-class="btn-light"
                                                        value="<?php echo date('m/d/Y', strtotime($start_date)) . ' - ' . date('m/d/Y', strtotime($end_date)); ?>">
                                                    <input type="hidden" name="start_date" id="start_date" value="<?php echo $start_date; ?>">
                                                    <input type="hidden" name="end_date" id="end_date" value="<?php echo $end_date; ?>">
                                                </div>
                                            </div>
                                            <div class="col-lg-2 col-md-6">
                                                <div class="mb-3">
                                                    <label for="status" class="form-label">Transaction Status</label>
                                                    <select class="form-select filter-select" id="status" name="status">
                                                        <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>All Statuses</option>
                                                        <option value="Available" <?php echo $status == 'Available' ? 'selected' : ''; ?>>Available</option>
                                                        <option value="Pending" <?php echo $status == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="Refunded" <?php echo $status == 'Refunded' ? 'selected' : ''; ?>>Refunded</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 col-md-6">
                                                <div class="mb-3">
                                                    <label for="course" class="form-label">Course</label>
                                                    <select class="form-select filter-select" id="course" name="course">
                                                        <option value="all" <?php echo $course_filter == 'all' ? 'selected' : ''; ?>>All Courses</option>
                                                        <?php foreach ($instructor_courses as $course): ?>
                                                            <option value="<?php echo $course['course_id']; ?>"
                                                                <?php echo $course_filter == $course['course_id'] ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($course['title']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-2 col-md-6">
                                                <div class="mb-3">
                                                    <label for="type" class="form-label">Transaction Type</label>
                                                    <select class="form-select filter-select" id="type" name="type">
                                                        <option value="all" <?php echo $transaction_type == 'all' ? 'selected' : ''; ?>>All Types</option>
                                                        <option value="income" <?php echo $transaction_type == 'income' ? 'selected' : ''; ?>>Income</option>
                                                        <option value="refund" <?php echo $transaction_type == 'refund' ? 'selected' : ''; ?>>Refunds</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-2 col-md-12 d-flex align-items-end">
                                                <div class="mb-3 w-100">
                                                    <button type="button" id="filter-button" class="btn btn-primary w-100">Apply Filters</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" id="summary-stats">
                        <div class="col-xl-4 col-md-6">
                            <div class="card bg-primary">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="text-white fw-normal mt-0">Total Transactions</h5>
                                            <h3 class="my-2 text-white" id="total-transactions"><?php echo number_format($summary['transaction_count']); ?></h3>
                                            <p class="mb-0 text-white-50">
                                                <span class="text-nowrap">Across <span id="course-count"><?php echo $summary['course_count']; ?></span> courses</span>
                                            </p>
                                        </div>
                                        <div class="avatar-sm">
                                            <span class="avatar-title bg-white rounded">
                                                <span class="text-primary font-20">₵</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6">
                            <div class="card bg-success">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="text-white fw-normal mt-0">Total Income</h5>
                                            <h3 class="my-2 text-white" id="total-income"><?php echo formatCurrency($summary['total_income']); ?></h3>
                                            <p class="mb-0 text-white-50">
                                                <span class="text-nowrap">
                                                    <i class="mdi mdi-arrow-up-bold"></i>
                                                    Income for period
                                                </span>
                                            </p>
                                        </div>
                                        <div class="avatar-sm">
                                            <span class="avatar-title bg-white rounded">
                                                <i class="mdi mdi-arrow-up-bold text-success font-20"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6">
                            <div class="card bg-danger">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="text-white fw-normal mt-0">Platform Fees</h5>
                                            <h3 class="my-2 text-white" id="total-fees"><?php echo formatCurrency($summary['total_fees']); ?></h3>
                                            <p class="mb-0 text-white-50">
                                                <span class="text-nowrap">
                                                    <i class="mdi mdi-arrow-down-bold"></i>
                                                    Fees paid
                                                </span>
                                            </p>
                                        </div>
                                        <div class="avatar-sm">
                                            <span class="avatar-title bg-white rounded">
                                                <i class="mdi mdi-percent text-danger font-20"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h4 class="header-title">Transactions</h4>
                                        <div class="dropdown">
                                            <button class="btn btn-light dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                Export <i class="mdi mdi-download ms-1"></i>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                                <li><a class="dropdown-item" href="javascript:void(0);" id="export-csv">CSV</a></li>
                                                <li><a class="dropdown-item" href="javascript:void(0);" id="export-excel">Excel</a></li>
                                                <li><a class="dropdown-item" href="javascript:void(0);" id="export-pdf">PDF</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="table-responsive" id="content-area">
                                        <table id="transactions-table" class="table table-striped dt-responsive nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Date</th>
                                                    <th>Student</th>
                                                    <th>Course</th>
                                                    <th>Type</th>
                                                    <th data-bs-toggle="tooltip" data-bs-placement="top" title="Transaction status: Available (earned and available), Pending (in holding period), Refunded (returned to student)">Status <i class="mdi mdi-information-outline"></i></th>
                                                    <th>Platform Fee</th>
                                                    <th>Amount</th>
                                                    <th>Available On</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (count($transactions) > 0): ?>
                                                    <?php foreach ($transactions as $transaction): ?>
                                                        <tr>
                                                            <td><?php echo $transaction['payment_id']; ?></td>
                                                            <td><?php echo date('M d, Y', strtotime($transaction['transaction_date'])); ?></td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <?php if ($transaction['profile_image']): ?>
                                                                        <img src="../Uploads/profile/<?php echo htmlspecialchars($transaction['profile_image']); ?>" alt="Profile" class="rounded-circle me-2" width="32">
                                                                    <?php else: ?>
                                                                        <div class="avatar-sm me-2">
                                                                            <span class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                                                <?php echo strtoupper(substr($transaction['first_name'], 0, 1) . substr($transaction['last_name'], 0, 1)); ?>
                                                                            </span>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                    <div>
                                                                        <?php echo htmlspecialchars($transaction['first_name'] . ' ' . $transaction['last_name']); ?>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <?php if ($transaction['thumbnail']): ?>
                                                                        <img src="../Uploads/thumbnails/<?php echo htmlspecialchars($transaction['thumbnail']); ?>" alt="Course" class="rounded me-2" width="32">
                                                                    <?php else: ?>
                                                                        <div class="avatar-sm me-2">
                                                                            <span class="avatar-title rounded-circle bg-soft-info text-info">
                                                                                <i class="mdi mdi-book-education-outline"></i>
                                                                            </span>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                    <span><?php echo htmlspecialchars(substr($transaction['course_title'], 0, 30) . (strlen($transaction['course_title']) > 30 ? '...' : '')); ?></span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <?php if ($transaction['transaction_type'] == 'Incoming'): ?>
                                                                    <span class="badge bg-success-lighten text-success">Income</span>
                                                                <?php elseif ($transaction['transaction_type'] == 'Refund'): ?>
                                                                    <span class="badge bg-danger-lighten text-danger">Refund</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php if ($transaction['status'] == 'Available'): ?>
                                                                    <span class="badge bg-success status-tooltip" data-bs-toggle="tooltip" title="Funds are earned and available">Available</span>
                                                                <?php elseif ($transaction['status'] == 'Pending'): ?>
                                                                    <span class="badge bg-warning status-tooltip" data-bs-toggle="tooltip" title="Funds are in the holding period">Pending</span>
                                                                <?php elseif ($transaction['status'] == 'Refunded'): ?>
                                                                    <span class="badge bg-danger status-tooltip" data-bs-toggle="tooltip" title="Transaction has been refunded to the student">Refunded</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?php echo formatCurrency($transaction['platform_fee']); ?></td>
                                                            <td class="fw-bold">
                                                                <?php if ($transaction['transaction_type'] == 'Incoming'): ?>
                                                                    <span class="text-success">+<?php echo formatCurrency($transaction['amount']); ?></span>
                                                                <?php elseif ($transaction['transaction_type'] == 'Refund'): ?>
                                                                    <span class="text-danger">-<?php echo formatCurrency($transaction['amount']); ?></span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php if ($transaction['status'] == 'Pending' && $transaction['available_at']): ?>
                                                                    <?php echo date('M d, Y', strtotime($transaction['available_at'])); ?>
                                                                <?php elseif ($transaction['status'] == 'Available'): ?>
                                                                    <span class="text-success">Now</span>
                                                                <?php else: ?>
                                                                    <span class="text-muted">-</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="9" class="text-center">No transactions found matching your filters</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php if ($total_pages > 1): ?>
                                        <div class="row mt-3" id="pagination-container">
                                            <div class="col-12">
                                                <nav>
                                                    <ul class="pagination pagination-rounded justify-content-center">
                                                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                                            <a class="page-link pagination-link" href="javascript:void(0);" data-page="<?php echo $page - 1; ?>" aria-label="Previous">
                                                                <span aria-hidden="true">«</span>
                                                            </a>
                                                        </li>
                                                        <?php
                                                        $start_page = max(1, $page - 2);
                                                        $end_page = min($total_pages, $page + 2);
                                                        if ($start_page > 1): ?>
                                                            <li class="page-item">
                                                                <a class="page-link pagination-link" href="javascript:void(0);" data-page="1">1</a>
                                                            </li>
                                                            <?php if ($start_page > 2): ?>
                                                                <li class="page-item disabled">
                                                                    <a class="page-link" href="javascript:void(0);">...</a>
                                                                </li>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                                                <a class="page-link pagination-link" href="javascript:void(0);" data-page="<?php echo $i; ?>"><?php echo $i; ?></a>
                                                            </li>
                                                        <?php endfor; ?>
                                                        <?php if ($end_page < $total_pages): ?>
                                                            <?php if ($end_page < $total_pages - 1): ?>
                                                                <li class="page-item disabled">
                                                                    <a class="page-link" href="javascript:void(0);">...</a>
                                                                </li>
                                                            <?php endif; ?>
                                                            <li class="page-item">
                                                                <a class="page-link pagination-link" href="javascript:void(0);" data-page="<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a>
                                                            </li>
                                                        <?php endif; ?>
                                                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                                            <a class="page-link pagination-link" href="javascript:void(0);" data-page="<?php echo $page + 1; ?>" aria-label="Next">
                                                                <span aria-hidden="true">»</span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </nav>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                            © Learnix. <script>document.write(new Date().getFullYear())</script> All rights reserved.
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <?php include '../includes/instructor-darkmode.php'; ?>
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.min.js"></script>
    <script src="assets/js/vendor/jquery.dataTables.min.js"></script>
    <script src="assets/js/vendor/dataTables.bootstrap5.js"></script>
    <script src="assets/js/vendor/dataTables.responsive.min.js"></script>
    <script src="assets/js/vendor/responsive.bootstrap5.min.js"></script>
    <script src="assets/js/vendor/dataTables.buttons.min.js"></script>
    <script src="assets/js/vendor/buttons.bootstrap5.min.js"></script>
    <script src="assets/js/vendor/buttons.html5.min.js"></script>
    <script src="assets/js/vendor/jszip.min.js"></script>
    <script src="assets/js/vendor/pdfmake.min.js"></script>
    <script src="assets/js/vendor/vfs_fonts.js"></script>
    <script src="assets/js/vendor/buttons.flash.min.js"></script>
    <script src="assets/js/vendor/buttons.print.min.js"></script>
    <script src="assets/js/vendor/dataTables.keyTable.min.js"></script>
    <script src="assets/js/vendor/moment.min.js"></script>
    <script src="assets/js/vendor/daterangepicker.js"></script>
    <script src="assets/js/vendor/jquery-jvectormap-1.2.2.min.js"></script>
    <script src="assets/js/vendor/jquery-jvectormap-world-mill-en.js"></script>
    <script>
        function showAlert(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type === 'success' ? 'success' : type === 'info' ? 'info' : 'danger'} alert-dismissible fade show`;
            alertDiv.setAttribute('role', 'alert');
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            alertDiv.style.position = 'fixed';
            alertDiv.style.top = '20px';
            alertDiv.style.left = '50%';
            alertDiv.style.transform = 'translateX(-50%)';
            alertDiv.style.zIndex = '9999';
            alertDiv.style.minWidth = '300px';
            alertDiv.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
            document.body.appendChild(alertDiv);
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
        }

        function showOverlay(message = null) {
            const existingOverlay = document.querySelector('.custom-overlay');
            if (existingOverlay) {
                existingOverlay.remove();
            }
            const overlay = document.createElement('div');
            overlay.className = 'custom-overlay';
            overlay.innerHTML = `
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                ${message ? `<div class="text-white ms-3">${message}</div>` : ''}
            `;
            document.body.appendChild(overlay);
        }

        function removeOverlay() {
            const overlay = document.querySelector('.custom-overlay');
            if (overlay) {
                overlay.remove();
            }
        }
    </script>
    <script>
        $(document).ready(function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            <?php if ($show_alert): ?>
                showAlert('<?php echo $alert_type; ?>', '<?php echo $alert_message; ?>');
            <?php endif; ?>

            var table = $('#transactions-table').DataTable({
                lengthChange: false,
                searching: true,
                ordering: true,
                paging: false,
                info: false,
                buttons: [{
                    extend: 'copy',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] }
                }, {
                    extend: 'csv',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] }
                }, {
                    extend: 'excel',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] }
                }, {
                    extend: 'pdf',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] },
                    orientation: 'landscape'
                }, {
                    extend: 'print',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] }
                }]
            });

            $('#transactions-table_filter').addClass('d-none');
            $('<div class="row mb-3">' +
                '<div class="col-md-6 d-flex align-items-center">' +
                '<span class="me-2">Showing ' + <?php echo count($transactions); ?> + ' of ' + <?php echo $total_transactions; ?> + ' transactions</span>' +
                '</div>' +
                '<div class="col-md-6">' +
                '<input type="text" class="form-control form-control-sm" placeholder="Search within results..." id="custom-search">' +
                '</div>' +
                '</div>').insertBefore('#transactions-table');

            $('#custom-search').on('keyup', function() {
                table.search(this.value).draw();
            });

            $('#daterange').daterangepicker({
                opens: 'left',
                startDate: moment('<?php echo $start_date; ?>'),
                endDate: moment('<?php echo $end_date; ?>'),
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'This Year': [moment().startOf('year'), moment().endOf('year')]
                }
            }, function(start, end, label) {
                $('#start_date').val(start.format('YYYY-MM-DD'));
                $('#end_date').val(end.format('YYYY-MM-DD'));
            });

            $('#filter-button').on('click', function() {
                loadData();
            });

            $(document).on('click', '.pagination-link', function(e) {
                e.preventDefault();
                var page = $(this).data('page');
                loadData(page);
            });

            $('#export-csv').on('click', function() {
                showOverlay('Generating CSV file...');
                setTimeout(function() {
                    table.button(1).trigger();
                    removeOverlay();
                }, 500);
            });

            $('#export-excel').on('click', function() {
                showOverlay('Generating Excel file...');
                setTimeout(function() {
                    table.button(2).trigger();
                    removeOverlay();
                }, 500);
            });

            $('#export-pdf').on('click', function() {
                showOverlay('Generating PDF file...');
                setTimeout(function() {
                    table.button(3).trigger();
                    removeOverlay();
                }, 500);
            });

            function loadData(page = 1) {
                showOverlay('Loading data...');
                var startDate = $('#start_date').val();
                var endDate = $('#end_date').val();
                var status = $('#status').val();
                var course = $('#course').val();
                var type = $('#type').val();
                var data = {
                    start_date: startDate,
                    end_date: endDate,
                    status: status,
                    course: course,
                    type: type,
                    page: page,
                    limit: <?php echo $limit; ?>
                };
                $.ajax({
                    url: 'earnings-history.php',
                    type: 'GET',
                    data: data,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function(response) {
                        updateUI(response);
                        var queryString = '?';
                        Object.keys(data).forEach(function(key, index) {
                            queryString += (index > 0 ? '&' : '') + key + '=' + data[key];
                        });
                        window.history.pushState({}, '', queryString);
                        removeOverlay();
                        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                            return new bootstrap.Tooltip(tooltipTriggerEl);
                        });
                        if (response.show_alert) {
                            showAlert(response.alert_type, response.alert_message);
                        }
                    },
                    error: function(xhr, status, error) {
                        removeOverlay();
                        showAlert('danger', 'Error loading data: ' + error);
                    }
                });
            }

            function updateUI(response) {
                $('#total-transactions').text(response.summary.transaction_count.toLocaleString());
                $('#course-count').text(response.summary.course_count);
                $('#total-income').text('₵' + parseFloat(response.summary.total_income).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#total-fees').text('₵' + parseFloat(response.summary.total_fees).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                var tableBody = $('#transactions-table tbody');
                tableBody.empty();
                if (response.transactions.length > 0) {
                    response.transactions.forEach(function(transaction) {
                        var row = `
                            <tr>
                                <td>${transaction.payment_id}</td>
                                <td>${formatDate(transaction.transaction_date)}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        ${transaction.profile_image ? 
                                            `<img src="../Uploads/profile/${transaction.profile_image}" alt="Profile" class="rounded-circle me-2" width="32">` : 
                                            `<div class="avatar-sm me-2">
                                                <span class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                    ${(transaction.first_name.charAt(0) + transaction.last_name.charAt(0)).toUpperCase()}
                                                </span>
                                            </div>`
                                        }
                                        <div>${transaction.first_name} ${transaction.last_name}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        ${transaction.thumbnail ? 
                                            `<img src="../Uploads/thumbnails/${transaction.thumbnail}" alt="Course" class="rounded me-2" width="32">` : 
                                            `<div class="avatar-sm me-2">
                                                <span class="avatar-title rounded-circle bg-soft-info text-info">
                                                    <i class="mdi mdi-book-education-outline"></i>
                                                </span>
                                            </div>`
                                        }
                                        <span>${truncateText(transaction.course_title, 30)}</span>
                                    </div>
                                </td>
                                <td>
                                    ${transaction.transaction_type === 'Incoming' ? 
                                        `<span class="badge bg-success-lighten text-success">Income</span>` : 
                                        `<span class="badge bg-danger-lighten text-danger">Refund</span>`
                                    }
                                </td>
                                <td>
                                    ${getStatusBadge(transaction.status)}
                                </td>
                                <td>₵${parseFloat(transaction.platform_fee).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td class="fw-bold">
                                    ${getAmountWithColor(transaction.transaction_type, transaction.amount)}
                                </td>
                                <td>
                                    ${getAvailabilityDate(transaction.status, transaction.available_at)}
                                </td>
                            </tr>
                        `;
                        tableBody.append(row);
                    });
                } else {
                    tableBody.html('<tr><td colspan="9" class="text-center">No transactions found matching your filters</td></tr>');
                }
                updatePagination(response.current_page, response.total_pages);
                if ($.fn.DataTable.isDataTable('#transactions-table')) {
                    $('#transactions-table').DataTable().destroy();
                }
                var table = $('#transactions-table').DataTable({
                    lengthChange: false,
                    searching: true,
                    ordering: true,
                    paging: false,
                    info: false,
                    buttons: [{
                        extend: 'copy',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] }
                    }, {
                        extend: 'csv',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] }
                    }, {
                        extend: 'excel',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] }
                    }, {
                        extend: 'pdf',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] },
                        orientation: 'landscape'
                    }, {
                        extend: 'print',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] }
                    }]
                });
                $('.col-md-6 .me-2').text('Showing ' + response.transactions.length + ' of ' + response.total_transactions + ' transactions');
            }

            function updatePagination(currentPage, totalPages) {
                var paginationContainer = $('#pagination-container');
                if (totalPages <= 1) {
                    paginationContainer.hide();
                    return;
                }
                paginationContainer.show();
                var pagination = paginationContainer.find('ul.pagination');
                pagination.empty();
                pagination.append(`
                    <li class="page-item ${currentPage <= 1 ? 'disabled' : ''}">
                        <a class="page-link pagination-link" href="javascript:void(0);" data-page="${currentPage - 1}" aria-label="Previous">
                            <span aria-hidden="true">«</span>
                        </a>
                    </li>
                `);
                var startPage = Math.max(1, currentPage - 2);
                var endPage = Math.min(totalPages, currentPage + 2);
                if (startPage > 1) {
                    pagination.append(`<li class="page-item"><a class="page-link pagination-link" href="javascript:void(0);" data-page="1">1</a></li>`);
                    if (startPage > 2) {
                        pagination.append(`<li class="page-item disabled"><a class="page-link" href="javascript:void(0);">...</a></li>`);
                    }
                }
                for (var i = startPage; i <= endPage; i++) {
                    pagination.append(`
                        <li class="page-item ${i == currentPage ? 'active' : ''}">
                            <a class="page-link pagination-link" href="javascript:void(0);" data-page="${i}">${i}</a>
                        </li>
                    `);
                }
                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        pagination.append(`<li class="page-item disabled"><a class="page-link" href="javascript:void(0);">...</a></li>`);
                    }
                    pagination.append(`<li class="page-item"><a class="page-link pagination-link" href="javascript:void(0);" data-page="${totalPages}">${totalPages}</a></li>`);
                }
                pagination.append(`
                    <li class="page-item ${currentPage >= totalPages ? 'disabled' : ''}">
                        <a class="page-link pagination-link" href="javascript:void(0);" data-page="${currentPage + 1}" aria-label="Next">
                            <span aria-hidden="true">»</span>
                        </a>
                    </li>
                `);
            }

            function formatDate(dateString) {
                var date = new Date(dateString);
                var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                return months[date.getMonth()] + ' ' + date.getDate() + ', ' + date.getFullYear();
            }

            function truncateText(text, maxLength) {
                if (text.length <= maxLength) return text;
                return text.substring(0, maxLength) + '...';
            }

            function getStatusBadge(status) {
                var badgeHtml = '';
                switch (status) {
                    case 'Available':
                        badgeHtml = '<span class="badge bg-success status-tooltip" data-bs-toggle="tooltip" title="Funds are earned and available">Available</span>';
                        break;
                    case 'Pending':
                        badgeHtml = '<span class="badge bg-warning status-tooltip" data-bs-toggle="tooltip" title="Funds are in the holding period">Pending</span>';
                        break;
                    case 'Refunded':
                        badgeHtml = '<span class="badge bg-danger status-tooltip" data-bs-toggle="tooltip" title="Transaction has been refunded to the student">Refunded</span>';
                        break;
                    default:
                        badgeHtml = '<span class="badge bg-secondary">' + status + '</span>';
                }
                return badgeHtml;
            }

            function getAmountWithColor(transactionType, amount) {
                var formattedAmount = '₵' + parseFloat(amount).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                switch (transactionType) {
                    case 'Incoming':
                        return '<span class="text-success">+' + formattedAmount + '</span>';
                    case 'Refund':
                        return '<span class="text-danger">-' + formattedAmount + '</span>';
                    default:
                        return formattedAmount;
                }
            }

            function getAvailabilityDate(status, availableAt) {
                if (status === 'Pending' && availableAt) {
                    return formatDate(availableAt);
                } else if (status === 'Available') {
                    return '<span class="text-success">Now</span>';
                } else {
                    return '<span class="text-muted">-</span>';
                }
            }
        });
    </script>
</body>
</html>