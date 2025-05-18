<?php
// student/payment-success.php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include header
include '../includes/student-header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Check if course_id is provided
if (!isset($_GET['course_id']) || !is_numeric($_GET['course_id'])) {
    header("Location: courses.php");
    exit();
}

$course_id = intval($_GET['course_id']);
$user_id = $_SESSION['user_id'];

// Connect to database
require_once '../backend/config.php';

// Get course details - using correct query with course_instructors table
$sql = "SELECT c.title, c.thumbnail
        FROM courses c
        WHERE c.course_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: courses.php");
    exit();
}

$course = $result->fetch_assoc();
$course_title = htmlspecialchars($course['title']);
$thumbnail = !empty($course['thumbnail']) ? htmlspecialchars($course['thumbnail']) : 'default-course.jpg';

// Get all instructors for this course, with primary instructor first
$sql = "SELECT u.first_name, u.last_name, ci.is_primary
        FROM course_instructors ci
        JOIN instructors i ON ci.instructor_id = i.instructor_id
        JOIN users u ON i.user_id = u.user_id
        WHERE ci.course_id = ?
        ORDER BY ci.is_primary DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$instructors_result = $stmt->get_result();

$instructors = [];
$primary_instructor_name = '';

while ($instructor = $instructors_result->fetch_assoc()) {
    $name = htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']);
    $instructors[] = $name;
    
    if ($instructor['is_primary'] == 1 && empty($primary_instructor_name)) {
        $primary_instructor_name = $name;
    }
}

// If we somehow don't have a primary instructor marked, just use the first one
if (empty($primary_instructor_name) && !empty($instructors)) {
    $primary_instructor_name = $instructors[0];
}

// Get enrollment details
$sql = "SELECT e.enrolled_at, cp.payment_method, cp.amount
        FROM enrollments e
        JOIN course_payments cp ON e.enrollment_id = cp.enrollment_id
        WHERE e.user_id = ? AND e.course_id = ? AND e.status = 'Active'
        ORDER BY e.enrolled_at DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$enrollment_result = $stmt->get_result();

if ($enrollment_result->num_rows === 0) {
    header("Location: courses.php");
    exit();
}

$enrollment = $enrollment_result->fetch_assoc();
$enrolled_date = date('F j, Y', strtotime($enrollment['enrolled_at']));
$payment_method = $enrollment['payment_method'];
$amount = number_format($enrollment['amount'], 2);

// Close database connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful | Learnix</title>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        .success-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 15px;
        }
        
        .success-card {
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            background-color: white;
        }
        
        .success-header {
            background: linear-gradient(135deg, #3a66db 0%, #5f85e5 100%);
            padding: 30px;
            text-align: center;
            color: white;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background-color: white;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .success-icon i {
            font-size: 40px;
            color: #3a66db;
        }
        
        .success-body {
            padding: 30px;
        }
        
        .course-preview {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
        }
        
        .course-image {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .course-info h3 {
            margin: 0 0 5px 0;
            font-size: 18px;
            color: #333;
        }
        
        .course-info p {
            margin: 0;
            font-size: 14px;
            color: #666;
            line-height: 1.4;
        }
        
        .success-detail {
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .success-detail h4 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #333;
            font-size: 18px;
        }
        
        .success-detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 15px;
        }
        
        .success-detail-row:last-of-type {
            margin-bottom: 0;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            font-weight: bold;
        }
        
        .success-detail-row span:last-child {
            text-align: right;
            max-width: 60%;
        }
        
        .success-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .success-btn {
            flex: 1;
            padding: 14px 0;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .success-btn i {
            margin-right: 8px;
        }
        
        .success-btn-primary {
            background-color: #3a66db;
            color: white;
            border: none;
        }
        
        .success-btn-outline {
            background-color: white;
            color: #3a66db;
            border: 1px solid #3a66db;
        }
        
        .success-btn-primary:hover {
            background-color: #2c51b0;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            color: white;
            text-decoration: none;
        }
        
        .success-btn-outline:hover {
            background-color: #f0f4ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            color: #3a66db;
            text-decoration: none;
        }
        
        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            background-color: #ffd700;
            animation: confetti-fall 5s ease-in-out infinite;
            z-index: 9999;
        }
        
        @keyframes confetti-fall {
            0% {
                transform: translateY(-100px) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(100vh) rotate(360deg);
                opacity: 0;
            }
        }
        
        @media (max-width: 767px) {
            .success-actions {
                flex-direction: column;
            }
            
            .course-preview {
                flex-direction: column;
                text-align: center;
            }
            
            .course-image {
                margin-right: 0;
                margin-bottom: 15px;
                width: 120px;
                height: 120px;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <div class="success-card">
            <div class="success-header">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h1>Payment Successful!</h1>
                <p class="mb-0">Your enrollment has been confirmed</p>
            </div>
            
            <div class="success-body">
                <h2>Thank you for your purchase</h2>
                <p>You now have full access to your course. We're excited to have you on board and hope you enjoy the learning journey!</p>
                
                <div class="course-preview">
                    <img src="../uploads/thumbnails/<?php echo $thumbnail; ?>" alt="<?php echo $course_title; ?>" class="course-image">
                    <div class="course-info">
                        <h3><?php echo $course_title; ?></h3>
                        <?php if (!empty($instructors)): ?>
                            <p>
                                <strong>Instructor<?php echo count($instructors) > 1 ? 's' : ''; ?>:</strong> 
                                <?php 
                                    // Display primary instructor with asterisk if there are multiple instructors
                                    if (count($instructors) > 1) {
                                        echo "$primary_instructor_name* (Primary)";
                                        
                                        // Display other instructors
                                        $other_instructors = array_filter($instructors, function($name) use ($primary_instructor_name) {
                                            return $name !== $primary_instructor_name;
                                        });
                                        
                                        if (!empty($other_instructors)) {
                                            echo "<br><span class='small text-muted'>Co-instructors: " . implode(", ", $other_instructors) . "</span>";
                                        }
                                    } else {
                                        echo $primary_instructor_name;
                                    }
                                ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="success-detail">
                    <h4>Purchase Details</h4>
                    <div class="success-detail-row">
                        <span>Course:</span>
                        <span><?php echo $course_title; ?></span>
                    </div>
                    <div class="success-detail-row">
                        <span>Instructor<?php echo count($instructors) > 1 ? 's' : ''; ?>:</span>
                        <span>
                            <?php 
                            if (count($instructors) > 1) {
                                echo $primary_instructor_name . " (Primary)<br>";
                                
                                // Display other instructors
                                $other_instructors = array_filter($instructors, function($name) use ($primary_instructor_name) {
                                    return $name !== $primary_instructor_name;
                                });
                                
                                if (!empty($other_instructors)) {
                                    echo "<small>" . implode("<br>", $other_instructors) . "</small>";
                                }
                            } else {
                                echo $primary_instructor_name;
                            }
                            ?>
                        </span>
                    </div>
                    <div class="success-detail-row">
                        <span>Enrollment Date:</span>
                        <span><?php echo $enrolled_date; ?></span>
                    </div>
                    <div class="success-detail-row">
                        <span>Payment Method:</span>
                        <span><?php echo $payment_method; ?></span>
                    </div>
                    <div class="success-detail-row">
                        <span>Amount Paid:</span>
                        <span>₵<?php echo $amount; ?></span>
                    </div>
                </div>
                
                <p>An email confirmation has been sent to your registered email address. If you have any questions, please contact our support team.</p>
                
                <div class="success-actions">
                    <a href="course-materials.php?course_id=<?php echo $course_id; ?>" class="success-btn success-btn-primary">
                        <i class="fas fa-play-circle"></i> Start Learning Now
                    </a>
                    <a href="my-courses.php" class="success-btn success-btn-outline">
                        <i class="fas fa-graduation-cap"></i> My Courses
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Confetti animation script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            createConfetti();
            
            function createConfetti() {
                const confettiCount = 150;
                const container = document.body;
                
                const colors = ['#3a66db', '#ff6b6b', '#7bed9f', '#70a1ff', '#ff7f50', '#9370db'];
                const shapes = ['circle', 'square', 'triangle'];
                
                for (let i = 0; i < confettiCount; i++) {
                    const confetti = document.createElement('div');
                    confetti.className = 'confetti';
                    
                    // Randomize confetti appearance
                    const size = Math.random() * 10 + 5;
                    confetti.style.width = size + 'px';
                    confetti.style.height = size + 'px';
                    
                    const shape = shapes[Math.floor(Math.random() * shapes.length)];
                    if (shape === 'circle') {
                        confetti.style.borderRadius = '50%';
                    } else if (shape === 'triangle') {
                        confetti.style.width = '0px';
                        confetti.style.height = '0px';
                        confetti.style.backgroundColor = 'transparent';
                        confetti.style.borderLeft = (size/2) + 'px solid transparent';
                        confetti.style.borderRight = (size/2) + 'px solid transparent';
                        confetti.style.borderBottom = size + 'px solid ' + colors[Math.floor(Math.random() * colors.length)];
                    } else {
                        confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                    }
                    
                    if (shape !== 'triangle') {
                        confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                    }
                    
                    confetti.style.left = Math.random() * 100 + 'vw';
                    
                    // Randomize animation properties
                    confetti.style.animationDuration = Math.random() * 3 + 2 + 's';
                    confetti.style.animationDelay = Math.random() * 5 + 's';
                    
                    container.appendChild(confetti);
                    
                    // Remove confetti after animation completes
                    setTimeout(() => {
                        confetti.remove();
                    }, 7000);
                }
            }
        });
    </script>

<?php include '../includes/student-footer.php'; ?>