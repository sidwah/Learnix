<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include Stripe configuration
require_once '../backend/stripe-config.php';
// Include Paystack configuration
require_once '../backend/paystack-config.php';

// Include header
include '../includes/student-header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Save destination in session and redirect to login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: index.php");
    exit();
}

// Check if course_id is provided in the URL
if (!isset($_GET['course_id']) || !is_numeric($_GET['course_id'])) {
    // Redirect to courses page if no valid ID is provided
    header("Location: courses.php");
    exit();
}

// Get course ID from URL
$course_id = intval($_GET['course_id']);
$user_id = $_SESSION['user_id'];

// Connect to database
require_once '../backend/config.php';

// Check if already enrolled
$sql = "SELECT * FROM enrollments WHERE user_id = ? AND course_id = ? AND status = 'Active'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$enrollment_result = $stmt->get_result();

if ($enrollment_result->num_rows > 0) {
    // Already enrolled, redirect to learning page
    $_SESSION['info_message'] = "You are already enrolled in this course.";
    header("Location: course-materials.php?course_id=" . $course_id);
    exit();
}

// Fetch course details
$sql = "SELECT c.*, u.first_name, u.last_name, u.profile_pic, 
               cat.name AS category_name, cat.slug AS category_slug,
               sub.name AS subcategory_name,
               d.name AS department_name
        FROM courses c
        JOIN course_instructors ci ON c.course_id = ci.course_id AND ci.is_primary = 1
        JOIN instructors i ON ci.instructor_id = i.instructor_id
        JOIN users u ON i.user_id = u.user_id
        JOIN subcategories sub ON c.subcategory_id = sub.subcategory_id
        JOIN categories cat ON sub.category_id = cat.category_id
        JOIN departments d ON c.department_id = d.department_id
        WHERE c.course_id = ? AND c.status = 'Published'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if course exists and is published
if ($result->num_rows === 0) {
    // Redirect to courses page if course not found
    $_SESSION['error_message'] = "Course not found or not available.";
    header("Location: courses.php");
    exit();
}

// Get course data
$course = $result->fetch_assoc();

// Check if course is free
if ($course['price'] == 0) {
    // Redirect to enroll page for free courses
    header("Location: ../backend/student/enroll.php?course_id=" . $course_id);
    exit();
}

// Get user data for auto-filling
$sql = "SELECT first_name, last_name, email, phone FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Close database connection
$stmt->close();
$conn->close();

// Format instructor name
$instructor_name = htmlspecialchars($course['first_name'] . ' ' . $course['last_name']);
$course_title = htmlspecialchars($course['title']);
$course_price = $course['price'];
$formatted_price = number_format($course_price, 2);

// Get course level text and badge color
$level_badge_color = "primary";
switch ($course['course_level']) {
    case 'Beginner':
        $level_badge_color = "success";
        break;
    case 'Intermediate':
        $level_badge_color = "warning";
        break;
    case 'Advanced':
        $level_badge_color = "danger";
        break;
}

// Get reviews/ratings
$course_rating = 4.8; // In a real implementation, you'd calculate this from the database
$review_count = 124; // Example count
?>


<!-- Stripe JS -->
<script src="https://js.stripe.com/v3/"></script>

<!-- Paystack JS -->
<script src="https://js.paystack.co/v1/inline.js"></script>

<!-- Custom CSS for enhanced checkout -->
<style>
    :root {
        --primary-color: #3a66db;
        --primary-hover: #2c51b0;
        --accent-color: #ff6b6b;
        --light-bg: #f8f9fa;
        --dark-bg: #343a40;
        --success-color: #28a745;
        --momo-color: #f26522;
        --momo-hover: #d65214;
    }

    body {
        background-color: #f8f9fa;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .checkout-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px 15px;
    }

    .checkout-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s, box-shadow 0.3s;
        overflow: hidden;
    }

    .checkout-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
    }

    .card-header {
        border-bottom: none;
        background: linear-gradient(135deg, var(--primary-color) 0%, #5f85e5 100%);
        color: white;
        font-weight: 600;
        padding: 20px 25px;
    }

    .momo-header {
        background: linear-gradient(135deg, var(--momo-color) 0%, #ff8b59 100%);
    }

    .summary-card {
        position: sticky;
        top: 20px;
    }

    .course-image-container {
        position: relative;
        overflow: hidden;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .course-image {
        width: 100%;
        height: 180px;
        object-fit: cover;
        transform: scale(1);
        transition: transform 0.4s;
    }

    .course-image:hover {
        transform: scale(1.05);
    }

    .secure-badge {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 20px;
        padding: 12px;
        background-color: #eef5ff;
        border-radius: 8px;
        color: #3a66db;
        font-size: 14px;
    }

    .guarantee-badge {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 15px;
        padding: 10px;
        background-color: #f0fff4;
        border-radius: 8px;
        color: #28a745;
        font-size: 14px;
    }

    .checkout-btn {
        font-weight: 600;
        letter-spacing: 0.5px;
        padding: 12px 20px;
        border-radius: 6px;
        transition: all 0.3s;
        background: linear-gradient(135deg, var(--primary-color) 0%, #5f85e5 100%);
        border: none;
        box-shadow: 0 4px 10px rgba(58, 102, 219, 0.25);
    }

    .checkout-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(58, 102, 219, 0.35);
        background: linear-gradient(135deg, #3a66db 0%, #4372e8 100%);
    }

    .momo-btn {
        background: linear-gradient(135deg, var(--momo-color) 0%, #ff8b59 100%);
        box-shadow: 0 4px 10px rgba(242, 101, 34, 0.25);
    }

    .momo-btn:hover {
        box-shadow: 0 6px 15px rgba(242, 101, 34, 0.35);
        background: linear-gradient(135deg, #e55a1a 0%, #ff7a40 100%);
    }

    .course-details {
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .course-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 15px;
    }

    .course-meta-item {
        display: flex;
        align-items: center;
        font-size: 14px;
        color: #6c757d;
    }

    .course-meta-item i {
        margin-right: 5px;
        color: var(--primary-color);
    }

    .payment-method-selector {
        display: flex;
        margin-bottom: 20px;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #dee2e6;
    }

    .payment-method-option {
        flex: 1;
        text-align: center;
        padding: 15px;
        cursor: pointer;
        background-color: #f8f9fa;
        font-weight: 500;
        transition: all 0.2s;
    }

    .payment-method-option.active {
        background-color: #fff;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        color: var(--primary-color);
        border-bottom: 2px solid var(--primary-color);
    }

    .payment-method-option.momo-option.active {
        color: var(--momo-color);
        border-bottom: 2px solid var(--momo-color);
    }

    .payment-method-option:hover:not(.active) {
        background-color: #f1f3f5;
    }

    .input-icon-group {
        position: relative;
    }

    .input-icon-group i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #adb5bd;
    }

    .input-icon-group input {
        padding-left: 40px;
    }

    #card-element {
        padding: 15px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        background-color: white;
        transition: all 0.2s;
    }

    #card-element:focus {
        box-shadow: 0 0 0 3px rgba(58, 102, 219, 0.15);
        border-color: var(--primary-color);
        outline: none;
    }

    .form-floating>label {
        padding-left: 40px;
    }

    .discount-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: linear-gradient(135deg, #ff6b6b 0%, #ff8e8e 100%);
        color: white;
        padding: 5px 10px;
        border-radius: 20px;
        font-weight: bold;
        z-index: 1;
    }

    .checkout-progress {
        display: flex;
        margin-bottom: 30px;
        justify-content: space-between;
    }

    .checkout-step {
        flex: 1;
        text-align: center;
        padding: 10px;
        position: relative;
    }

    .checkout-step::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 60%;
        right: 0;
        height: 2px;
        background-color: #dee2e6;
        z-index: 0;
    }

    .checkout-step:last-child::after {
        display: none;
    }

    .step-number {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: #dee2e6;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        position: relative;
        z-index: 1;
    }

    .checkout-step.active .step-number {
        background-color: var(--primary-color);
        color: white;
    }

    .checkout-step.completed .step-number {
        background-color: var(--success-color);
        color: white;
    }

    .step-label {
        font-size: 14px;
        color: #6c757d;
    }

    .checkout-step.active .step-label {
        color: var(--primary-color);
        font-weight: 600;
    }

    .checkout-step.completed .step-label {
        color: var(--success-color);
    }

    .promo-banner {
        background: linear-gradient(135deg, #4a66d9 0%, #5d85f3 100%);
        border-radius: 10px;
        color: white;
        padding: 15px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .promo-banner-icon {
        background-color: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .promo-banner-text {
        flex: 1;
        font-size: 14px;
    }

    .promo-banner-title {
        font-weight: bold;
        margin-bottom: 3px;
    }

    .counter-container {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-top: 5px;
        background: rgba(255, 255, 255, 0.2);
        padding: 5px;
        border-radius: 5px;
        justify-content: center;
        width: fit-content;
    }

    .counter-box {
        background: rgba(0, 0, 0, 0.1);
        border-radius: 3px;
        padding: 2px 5px;
        font-size: 12px;
        font-weight: bold;
        min-width: 20px;
        text-align: center;
    }

    .mobile-money-logos {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin-bottom: 20px;
    }

    .mobile-money-logo {
        background-color: white;
        border-radius: 8px;
        padding: 8px 12px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        width: 70px;
        height: 50px;
    }

    .mobile-money-logo img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    /* Mobile Money Modal */
    #momoModal .modal-header {
        background: linear-gradient(135deg, var(--momo-color) 0%, #ff8b59 100%);
        color: white;
    }

    #momoModal .modal-footer .btn-primary {
        background: linear-gradient(135deg, var(--momo-color) 0%, #ff8b59 100%);
        border: none;
    }

    #momoModal .network-selector {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    #momoModal .network-option {
        flex: 1;
        margin: 0 5px;
        text-align: center;
        padding: 10px;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.2s;
    }

    #momoModal .network-option.active {
        border-color: var(--momo-color);
        background-color: #fff4f0;
    }

    #momoModal .network-option img {
        max-width: 100%;
        height: 40px;
        object-fit: contain;
    }

    /* Animation */
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(58, 102, 219, 0.4);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(58, 102, 219, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(58, 102, 219, 0);
        }
    }

    .pulse-animation {
        animation: pulse 2s infinite;
    }

    /* Mobile responsiveness */
    @media (max-width: 767px) {
        .checkout-progress {
            display: none;
        }

        .course-meta {
            flex-direction: column;
            gap: 10px;
        }
    }
</style>

<body>
    <!-- ========== MAIN CONTENT ========== -->
    <main id="content" role="main">
        <!-- Breadcrumb -->
        <div class="bg-light py-3">
            <div class="container">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-no-gutter mb-0">
                        <li class="breadcrumb-item"><a class="breadcrumb-link" href="../index.php">Home</a></li>
                        <li class="breadcrumb-item"><a class="breadcrumb-link" href="courses.php">Courses</a></li>
                        <li class="breadcrumb-item"><a class="breadcrumb-link" href="course-overview.php?id=<?php echo $course_id; ?>"><?php echo $course_title; ?></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Checkout</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- End Breadcrumb -->

        <!-- Checkout Section -->
        <div class="container checkout-container">
            <!-- Display any error messages -->
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi-exclamation-triangle-fill flex-shrink-0 me-2"></i>
                    <div>
                        <?php
                        echo $_SESSION['error_message'];
                        unset($_SESSION['error_message']);
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Checkout Progress -->
            <div class="checkout-progress mb-4">
                <div class="checkout-step completed">
                    <div class="step-number">
                        <i class="bi-check"></i>
                    </div>
                    <div class="step-label">Course Selected</div>
                </div>
                <div class="checkout-step active">
                    <div class="step-number">2</div>
                    <div class="step-label">Payment Details</div>
                </div>
                <div class="checkout-step">
                    <div class="step-number">3</div>
                    <div class="step-label">Confirmation</div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8 mb-4 mb-lg-0">
                    <h1 class="mb-4">Complete your purchase</h1>

                    <!-- Special Offer Banner -->
                    <div class="promo-banner mb-4">
                        <div class="promo-banner-icon">
                            <i class="bi-alarm"></i>
                        </div>
                        <div class="promo-banner-text">
                            <div class="promo-banner-title">Special offer ends soon!</div>
                            <div>Enroll now and get lifetime access to all future updates.</div>
                            <div class="counter-container">
                                <div class="counter-box">23</div>:
                                <div class="counter-box">59</div>:
                                <div class="counter-box">42</div>
                            </div>
                        </div>
                    </div>

                    <!-- Course Details Overview -->
                    <div class="course-details mb-4">
                        <div class="course-meta">
                            <div class="course-meta-item">
                                <i class="bi-book"></i>
                                <span><?php echo $course['subcategory_name']; ?></span>
                            </div>
                            <div class="course-meta-item">
                                <i class="bi-star-fill text-warning"></i>
                                <span><?php echo $course_rating; ?> (<?php echo $review_count; ?> reviews)</span>
                            </div>
                            <div class="course-meta-item">
                                <i class="bi-bar-chart"></i>
                                <span><?php echo $course['course_level']; ?> Level</span>
                            </div>
                            <div class="course-meta-item">
                                <i class="bi-building"></i>
                                <span><?php echo $course['department_name']; ?></span>
                            </div>
                            <div class="course-meta-item">
                                <i class="bi-infinity"></i>
                                <span>Lifetime Access</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method Selection -->
                    <div class="payment-method-selector mb-4">
                        <div class="payment-method-option active" data-method="card">
                            <i class="bi-credit-card me-2"></i> Credit Card
                        </div>
                        <div class="payment-method-option momo-option" data-method="momo">
                            <i class="bi-phone me-2"></i> Mobile Money
                        </div>
                    </div>

                    <!-- Credit Card Payment Form -->
                    <div class="checkout-card mb-4" id="card-payment-form">
                        <div class="card-header">
                            <h4 class="card-header-title m-0"><i class="bi-lock-fill me-2"></i> Secure Payment Information</h4>
                        </div>
                        <div class="card-body p-4">
                            <form action="../backend/student/process-payment.php" method="post" id="payment-form">
                                <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                                <input type="hidden" name="payment_method" value="card">

                                <!-- Name -->
                                <div class="mb-4">
                                    <label class="form-label" for="cardName">Name on card</label>
                                    <div class="input-icon-group">
                                        <i class="bi-person"></i>
                                        <input type="text" class="form-control form-control-lg" id="cardName" name="cardName"
                                            placeholder="John Smith" required
                                            value="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>">
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="mb-4">
                                    <label class="form-label" for="email">Email (for receipt)</label>
                                    <div class="input-icon-group">
                                        <i class="bi-envelope"></i>
                                        <input type="email" class="form-control form-control-lg" id="email" name="email"
                                            placeholder="email@example.com" required
                                            value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                    </div>
                                </div>

                                <!-- Stripe Elements placeholder -->
                                <div class="mb-4">
                                    <label class="form-label" for="card-element">Card details</label>
                                    <div id="card-element" class="form-control form-control-lg">
                                        <!-- Stripe Element will be inserted here. -->
                                    </div>
                                    <!-- Used to display form errors. -->
                                    <div id="card-errors" role="alert" class="text-danger mt-2"></div>
                                </div>

                                <button type="submit" class="btn checkout-btn btn-lg w-100 pulse-animation text-white" id="submit-button">
                                    <i class="bi-lock-fill me-2"></i> Pay Securely ₵<?php echo $formatted_price; ?>
                                </button>

                                <div class="secure-badge">
                                    <i class="bi-shield-lock-fill me-2"></i> Your payment information is securely processed through Stripe
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- End Credit Card Payment Form -->

                    <!-- Mobile Money Payment Form -->
                    <div class="checkout-card mb-4" id="momo-payment-form" style="display: none;">
                        <div class="card-header momo-header">
                            <h4 class="card-header-title m-0"><i class="bi-phone me-2"></i> Pay with Mobile Money</h4>
                        </div>
                        <div class="card-body p-4">
                            <div class="mobile-money-logos mb-4">
                                <div class="mobile-money-logo">
                                    <img src="../assets/img/momo/mtn-momo.png" alt="MTN Mobile Money">
                                </div>
                                <div class="mobile-money-logo">
                                    <img src="../assets/img/momo/vodafone-cash.png" alt="Vodafone Cash">
                                </div>
                                <div class="mobile-money-logo">
                                    <img src="../assets/img/momo/airtel-tigo.png" alt="AirtelTigo Money">
                                </div>
                            </div>

                            <p class="text-center mb-4">
                                Pay securely using your mobile money account. Select your provider and enter your phone number.
                            </p>

                            <button type="button" class="btn checkout-btn momo-btn btn-lg w-100 pulse-animation text-white" id="momo-proceed-button" data-bs-toggle="modal" data-bs-target="#momoModal">
                                <i class="bi-phone me-2"></i> Pay with Mobile Money ₵<?php echo $formatted_price; ?>
                            </button>

                            <div class="secure-badge">
                                <i class="bi-shield-lock-fill me-2"></i> Your mobile money payment is securely processed through Paystack
                            </div>
                        </div>
                    </div>
                    <!-- End Mobile Money Payment Form -->

                    <div class="guarantee-badge">
                        <i class="bi-shield-check me-2"></i>
                        30-day money-back guarantee. Full refund if you're not satisfied.
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="checkout-card summary-card">
                        <div class="card-header">
                            <h4 class="card-header-title m-0">Order Summary</h4>
                        </div>

                        <div class="card-body p-4">
                            <!-- Course Image -->
                            <div class="course-image-container">
                                <?php if (!empty($course['thumbnail'])): ?>
                                    <img class="course-image" src="../uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>" alt="<?php echo $course_title; ?>">
                                    <div class="discount-badge">20% OFF</div>
                                <?php else: ?>
                                    <img class="course-image" src="../assets/img/default-course.jpg" alt="<?php echo $course_title; ?>">
                                <?php endif; ?>
                            </div>

                            <div class="mb-4">
                                <h5 class="mb-1"><?php echo $course_title; ?></h5>
                                <p class="text-muted mb-2">
                                    By <?php echo $instructor_name; ?>
                                </p>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="bi-star-fill text-warning"></i>
                                        <i class="bi-star-fill text-warning"></i>
                                        <i class="bi-star-fill text-warning"></i>
                                        <i class="bi-star-fill text-warning"></i>
                                        <i class="bi-star-half text-warning"></i>
                                    </div>
                                    <span class="small text-muted">(<?php echo $review_count; ?> reviews)</span>
                                </div>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Original price</span>
                                    <span class="text-decoration-line-through">₵<?php echo number_format($course_price * 1.25, 2); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Course price</span>
                                    <span>₵<?php echo $formatted_price; ?></span>
                                </div>
                            </div>

                            <!-- Coupon Code Input -->
                            <!-- <div class="mb-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Promo code" aria-label="Promo code">
                                    <button class="btn btn-outline-primary" type="button">Apply</button>
                                </div>
                            </div> -->

                            <hr>

                            <div class="d-flex justify-content-between mb-4">
                                <span class="h5 mb-0">Total</span>
                                <span class="h5 mb-0 text-primary">₵<?php echo $formatted_price; ?></span>
                            </div>

                            <!-- What you get -->
                            <div class="mb-4">
                                <h6 class="mb-3">What you get:</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="d-flex align-items-center mb-2">
                                        <i class="bi-check-circle-fill text-success me-2"></i>
                                        Full lifetime access
                                    </li>
                                    <li class="d-flex align-items-center mb-2">
                                        <i class="bi-check-circle-fill text-success me-2"></i>
                                        Access on mobile and TV
                                    </li>
                                    <li class="d-flex align-items-center mb-2">
                                        <i class="bi-check-circle-fill text-success me-2"></i>
                                        Certificate of completion
                                    </li>
                                    <li class="d-flex align-items-center mb-2">
                                        <i class="bi-check-circle-fill text-success me-2"></i>
                                        All course materials
                                    </li>
                                    <li class="d-flex align-items-center">
                                        <i class="bi-check-circle-fill text-success me-2"></i>
                                        Regular updates
                                    </li>
                                </ul>
                            </div>

                            <div class="small text-muted">
                                By completing your purchase you agree to our
                                <a href="#">Terms of Service</a> and
                                <a href="#">Privacy Policy</a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Order Summary -->
            </div>
        </div>
        <!-- End Checkout Section -->
    </main>
    <!-- ========== END MAIN CONTENT ========== -->

    <!-- Mobile Money Modal -->
    <div class="modal fade" id="momoModal" tabindex="-1" aria-labelledby="momoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="momoModalLabel">Mobile Money Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="momo-details-form">
                        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                        <input type="hidden" name="amount" value="<?php echo $course_price; ?>">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">

                        <!-- Mobile Money Provider Selection -->
                        <div class="mb-4">
                            <label class="form-label">Select your mobile money provider</label>
                            <div class="network-selector">
                                <div class="network-option active" data-provider="mtn">
                                    <img src="../assets/img/momo/mtn-momo.png" alt="MTN Mobile Money">
                                    <div class="small mt-1">MTN</div>
                                </div>
                                <div class="network-option" data-provider="vodafone">
                                    <img src="../assets/img/momo/vodafone-cash.png" alt="Vodafone Cash">
                                    <div class="small mt-1">Vodafone</div>
                                </div>
                                <div class="network-option" data-provider="airtel">
                                    <img src="../assets/img/momo/airtel-tigo.png" alt="AirtelTigo Money">
                                    <div class="small mt-1">AirtelTigo</div>
                                </div>
                            </div>
                            <input type="hidden" id="selectedProvider" name="provider" value="mtn">
                        </div>

                        <!-- Phone Number -->
                        <div class="mb-4">
                            <label class="form-label" for="momoPhone">Mobile Money Number</label>
                            <div class="input-icon-group">
                                <i class="bi-phone"></i>
                                <input type="tel" class="form-control form-control-lg" id="momoPhone" name="phone"
                                    placeholder="0201234567" required pattern="[0-9]{10}"
                                    value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            <div class="form-text">Enter your 10-digit mobile money number (Test: 0551234987)</div>
                        </div>

                        <!-- Name -->
                        <div class="mb-4">
                            <label class="form-label" for="momoName">Full Name</label>
                            <div class="input-icon-group">
                                <i class="bi-person"></i>
                                <input type="text" class="form-control form-control-lg" id="momoName" name="name"
                                    placeholder="John Smith" required
                                    value="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>">
                            </div>
                        </div>
                    </form>

                    <div class="alert alert-info">
                        <small>
                            <i class="bi-info-circle me-2"></i>
                            This is a test environment. For testing, you can use any valid phone number format.
                            You will receive a success prompt without an actual mobile money charge.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="payWithPaystack">
                        Pay ₵<?php echo $formatted_price; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Initialize Scripts -->
    <script>
        // Stripe setup
        var stripe = Stripe('<?php echo STRIPE_PUBLISHABLE_KEY; ?>');
        var elements = stripe.elements();

        // Custom styling for Stripe Elements
        var style = {
            base: {
                color: '#495057',
                fontFamily: 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                    color: '#adb5bd'
                },
                ':-webkit-autofill': {
                    color: '#495057'
                }
            },
            invalid: {
                color: '#dc3545',
                iconColor: '#dc3545',
                ':-webkit-autofill': {
                    color: '#dc3545'
                }
            }
        };

        // Create an instance of the card Element.
        var card = elements.create('card', {
            style: style,
            hidePostalCode: false
        });

        // Add an instance of the card Element into the `card-element` div.
        card.mount('#card-element');

        // Handle real-time validation errors from the card Element.
        card.on('change', function(event) {
            var displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });

        // Handle form submission for Stripe.
        var form = document.getElementById('payment-form');
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            // Disable the submit button to prevent repeated clicks
            document.getElementById('submit-button').disabled = true;
            document.getElementById('submit-button').innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...';

            stripe.createToken(card).then(function(result) {
                if (result.error) {
                    // Inform the user if there was an error.
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                    document.getElementById('submit-button').disabled = false;
                    document.getElementById('submit-button').innerHTML = '<i class="bi-lock-fill me-2"></i> Pay Securely ₵<?php echo $formatted_price; ?>';
                } else {
                    // Send the token to your server.
                    stripeTokenHandler(result.token);
                }
            });
        });

        // Submit the form with the token ID.
        function stripeTokenHandler(token) {
            // Insert the token ID into the form so it gets submitted to the server
            var form = document.getElementById('payment-form');
            var hiddenInput = document.createElement('input');
            hiddenInput.setAttribute('type', 'hidden');
            hiddenInput.setAttribute('name', 'stripeToken');
            hiddenInput.setAttribute('value', token.id);
            form.appendChild(hiddenInput);

            // Submit the form
            form.submit();
        }

        // Mobile Money provider selector in modal
        document.querySelectorAll('.network-option').forEach(function(option) {
            option.addEventListener('click', function() {
                // Remove active class from all options
                document.querySelectorAll('.network-option').forEach(function(opt) {
                    opt.classList.remove('active');
                });

                // Add active class to clicked option
                this.classList.add('active');

                // Update hidden input with selected provider
                document.getElementById('selectedProvider').value = this.getAttribute('data-provider');
            });
        });

        // Payment method selector tabs
        document.querySelectorAll('.payment-method-option').forEach(function(option) {
            option.addEventListener('click', function() {
                // Remove active class from all options
                document.querySelectorAll('.payment-method-option').forEach(function(opt) {
                    opt.classList.remove('active');
                });

                // Add active class to clicked option
                this.classList.add('active');

                // Show/hide payment forms based on selection
                var method = this.getAttribute('data-method');
                if (method === 'card') {
                    document.getElementById('card-payment-form').style.display = 'block';
                    document.getElementById('momo-payment-form').style.display = 'none';
                } else if (method === 'momo') {
                    document.getElementById('card-payment-form').style.display = 'none';
                    document.getElementById('momo-payment-form').style.display = 'block';
                }
            });
        });

        // Paystack Integration
        document.getElementById('payWithPaystack').addEventListener('click', function(e) {
            e.preventDefault();

            // Get form data
            var phoneNumber = document.getElementById('momoPhone').value;
            var fullName = document.getElementById('momoName').value;
            var provider = document.getElementById('selectedProvider').value;

            // Basic validation
            if (!phoneNumber || phoneNumber.length !== 10 || !/^\d+$/.test(phoneNumber)) {
                alert('Please enter a valid 10-digit mobile number');
                return;
            }

            if (!fullName) {
                alert('Please enter your full name');
                return;
            }

            // Initialize Paystack payment
            var handler = PaystackPop.setup({
                key: '<?php echo $PublicKey; ?>', // Your public key from paystack-config.php
                email: '<?php echo htmlspecialchars($user['email']); ?>',
                amount: <?php echo $course_price * 100; ?>, // Amount in kobo
                currency: 'GHS',
                ref: 'LRN' + Math.floor((Math.random() * 1000000000) + 1), // Generate a random reference
                metadata: {
                    custom_fields: [{
                            display_name: "Mobile Number",
                            variable_name: "mobile_number",
                            value: phoneNumber
                        },
                        {
                            display_name: "Course ID",
                            variable_name: "course_id",
                            value: "<?php echo $course_id; ?>"
                        }
                    ]
                },
                callback: function(response) {
                    // After successful payment, submit form to backend
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '../backend/student/process-momo-payment.php';

                    // Add necessary form fields
                    var fields = {
                        'course_id': '<?php echo $course_id; ?>',
                        'payment_method': 'momo',
                        'transaction_id': response.reference,
                        'phone': phoneNumber,
                        'provider': provider,
                        'amount': '<?php echo $course_price; ?>'
                    };

                    for (var key in fields) {
                        var hiddenField = document.createElement('input');
                        hiddenField.setAttribute('type', 'hidden');
                        hiddenField.setAttribute('name', key);
                        hiddenField.setAttribute('value', fields[key]);
                        form.appendChild(hiddenField);
                    }

                    document.body.appendChild(form);
                    form.submit();
                },
                onClose: function() {
                    // Handle when user closes the payment modal
                    console.log('Payment window closed');
                }
            });

            handler.openIframe();
        });

        // Countdown timer with localStorage persistence
        function initializeCountdown() {
            // Check if we already have an end time saved
            let endTime = localStorage.getItem('promoEndTime');

            // If no end time is saved, or it has already passed, set a new one (24 hours from now)
            if (!endTime || new Date(parseInt(endTime)) <= new Date()) {
                endTime = new Date().getTime() + (24 * 60 * 60 * 1000); // 24 hours from now
                localStorage.setItem('promoEndTime', endTime);
            }

            // Start the countdown
            updateCountdown(endTime);
        }

        function updateCountdown(endTime) {
            // Get the counter elements
            const counterBoxes = document.querySelectorAll('.counter-container .counter-box');

            // Check if we have the right number of elements
            if (counterBoxes.length !== 3) {
                console.log("Expected 3 counter boxes, found " + counterBoxes.length);
                return;
            }

            const hours = counterBoxes[0];
            const minutes = counterBoxes[1];
            const seconds = counterBoxes[2];

            // Calculate remaining time
            const now = new Date().getTime();
            const distance = parseInt(endTime) - now;

            // Time calculations
            let h = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            let m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            let s = Math.floor((distance % (1000 * 60)) / 1000);

            // If countdown is finished
            if (distance < 0) {
                // Reset for a new countdown or handle expiration
                localStorage.removeItem('promoEndTime');
                hours.textContent = "00";
                minutes.textContent = "00";
                seconds.textContent = "00";
                return;
            }

            // Display the time
            hours.textContent = h.toString().padStart(2, '0');
            minutes.textContent = m.toString().padStart(2, '0');
            seconds.textContent = s.toString().padStart(2, '0');

            // Update every second
            setTimeout(() => updateCountdown(endTime), 1000);
        }

        // Make sure DOM is fully loaded before starting the countdown
        document.addEventListener('DOMContentLoaded', function() {
            initializeCountdown();
        });
    </script>

    <?php include '../includes/student-footer.php'; ?>