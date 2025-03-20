<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include header
include '../includes/student-header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Save destination in session and redirect to login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
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
    header("Location: learn.php?course_id=" . $course_id);
    exit();
}

// Fetch course details
$sql = "SELECT c.*, u.first_name, u.last_name, u.profile_pic, 
               cat.name AS category_name, cat.slug AS category_slug 
        FROM courses c
        JOIN instructors i ON c.instructor_id = i.instructor_id
        JOIN users u ON i.user_id = u.user_id
        JOIN subcategories sub ON c.subcategory_id = sub.subcategory_id
        JOIN categories cat ON sub.category_id = cat.category_id
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
    header("Location: enroll.php?course_id=" . $course_id);
    exit();
}

// Process payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real implementation, you'd integrate with a payment gateway here
    // For demonstration, we'll just simulate a successful payment
    
    // Start a transaction
    $conn->begin_transaction();
    
    try {
        // Create enrollment record
        $sql = "INSERT INTO enrollments (user_id, course_id, enrolled_at, status) 
                VALUES (?, ?, NOW(), 'Active')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $course_id);
        $stmt->execute();
        $enrollment_id = $stmt->insert_id;
        
        // Record payment
        $sql = "INSERT INTO course_payments (enrollment_id, amount, currency, payment_date, payment_method, status) 
                VALUES (?, ?, 'USD', NOW(), 'Credit Card', 'Completed')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("id", $enrollment_id, $course['price']);
        $stmt->execute();
        
        // Initialize progress records
        $sql = "SELECT st.topic_id 
                FROM section_topics st 
                JOIN course_sections cs ON st.section_id = cs.section_id 
                WHERE cs.course_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $topics_result = $stmt->get_result();
        
        while ($topic = $topics_result->fetch_assoc()) {
            $sql = "INSERT INTO progress (enrollment_id, topic_id, completion_status) 
                    VALUES (?, ?, 'Not Started')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $enrollment_id, $topic['topic_id']);
            $stmt->execute();
        }
        
        // Commit transaction
        $conn->commit();
        
        // Redirect to learning page
        $_SESSION['success_message'] = "Payment successful! You are now enrolled in the course.";
        header("Location: learn.php?course_id=" . $course_id);
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error_message'] = "Payment processing failed. Please try again.";
    }
}

// Close database connection
$stmt->close();
$conn->close();
?>

<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main">
    <!-- Breadcrumb -->
    <div class="bg-light">
        <div class="container py-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-no-gutter mb-0">
                    <li class="breadcrumb-item"><a class="breadcrumb-link" href="../index.php">Home</a></li>
                    <li class="breadcrumb-item"><a class="breadcrumb-link" href="courses.php">Courses</a></li>
                    <li class="breadcrumb-item"><a class="breadcrumb-link" href="course-overview.php?id=<?php echo $course_id; ?>"><?php echo htmlspecialchars($course['title']); ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Checkout</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Checkout Section -->
    <div class="container content-space-2">
        <!-- Display any error messages -->
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php 
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8 mb-5 mb-lg-0">
                <h1 class="mb-4">Checkout</h1>
                
                <!-- Payment Form -->
                <div class="card mb-5">
                    <div class="card-header bg-light py-3">
                        <h4 class="card-header-title">Payment information</h4>
                    </div>
                    <div class="card-body">
                        <form action="" method="post" id="payment-form">
                            <!-- Credit Card Information -->
                            <div class="mb-4">
                                <label class="form-label" for="cardName">Name on card</label>
                                <input type="text" class="form-control" id="cardName" name="cardName" placeholder="John Smith" required>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label" for="cardNumber">Card number</label>
                                <input type="text" class="form-control" id="cardNumber" name="cardNumber" placeholder="1234 1234 1234 1234" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label" for="cardExpiry">Expiration date</label>
                                    <input type="text" class="form-control" id="cardExpiry" name="cardExpiry" placeholder="MM/YY" required>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <label class="form-label" for="cardCVC">CVC</label>
                                    <input type="text" class="form-control" id="cardCVC" name="cardCVC" placeholder="123" required>
                                </div>
                            </div>
                            
                            <!-- Billing Address -->
                            <div class="mb-4">
                                <label class="form-label" for="billingAddress">Billing address</label>
                                <input type="text" class="form-control" id="billingAddress" name="billingAddress" placeholder="123 Main St" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-5 mb-4">
                                    <label class="form-label" for="billingCity">City</label>
                                    <input type="text" class="form-control" id="billingCity" name="billingCity" placeholder="San Francisco" required>
                                </div>
                                
                                <div class="col-md-4 mb-4">
                                    <label class="form-label" for="billingState">State</label>
                                    <input type="text" class="form-control" id="billingState" name="billingState" placeholder="CA" required>
                                </div>
                                
                                <div class="col-md-3 mb-4">
                                    <label class="form-label" for="billingZip">Zip code</label>
                                    <input type="text" class="form-control" id="billingZip" name="billingZip" placeholder="94110" required>
                                </div>
                            </div>
                            
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="saveCard" name="saveCard">
                                <label class="form-check-label" for="saveCard">
                                    Save this card for future purchases
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                Pay Now $<?php echo number_format($course['price'], 2); ?>
                            </button>
                            
                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    <i class="bi-lock me-1"></i> Your payment information is secure.
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- End Payment Form -->
                
                <div class="text-center">
                    <p class="small mb-0">
                        <i class="bi-shield-check me-1"></i> 
                        30-day money-back guarantee. Full refund if you're not satisfied.
                    </p>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card position-sticky top-0">
                    <div class="card-header bg-light py-3">
                        <h4 class="card-header-title">Order summary</h4>
                    </div>
                    
                    <div class="card-body">
                        <div class="d-flex mb-4">
                            <?php if (!empty($course['thumbnail'])): ?>
                                <div class="flex-shrink-0">
                                    <img class="avatar avatar-xl" src="../uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                                </div>
                            <?php endif; ?>
                            
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1"><?php echo htmlspecialchars($course['title']); ?></h5>
                                <p class="small text-muted mb-0">
                                    By <?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?>
                                </p>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Course price</span>
                                <span>$<?php echo number_format($course['price'], 2); ?></span>
                            </div>
                        </div>
                        
                        <!-- Coupon Code Input -->
                        <div class="mb-4">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Discount code" aria-label="Discount code">
                                <button class="btn btn-outline-primary" type="button">Apply</button>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <span class="h5">Total</span>
                            <span class="h5">$<?php echo number_format($course['price'], 2); ?></span>
                        </div>
                        
                        <p class="small text-muted mb-0">
                            By completing your purchase you agree to the 
                            <a href="#">Terms of Service</a> and 
                            <a href="#">Privacy Policy</a>
                        </p>
                    </div>
                </div>
            </div>
            <!-- End Order Summary -->
        </div>
    </div>
    <!-- End Checkout Section -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<?php include '../includes/student-footer.php'; ?>