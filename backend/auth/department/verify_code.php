<?php
session_start();
header('Content-Type: application/json');
include '../../config.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Check if all required fields are provided
if (!isset($_POST['email']) || !isset($_POST['code'])) {
    echo json_encode(['status' => 'error', 'message' => 'Required fields are missing']);
    exit;
}

$email = trim($_POST['email']);
$code = trim($_POST['code']);
$maxAttempts = 5; // Maximum verification attempts

// Validate the verification code format
if (!preg_match('/^\d{6}$/', $code)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid verification code format']);
    exit;
}

// Check for verification attempt limits (if we're tracking them)
$checkAttempts = $conn->prepare("CREATE TABLE IF NOT EXISTS department_verification_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    attempts INT NOT NULL DEFAULT 0,
    last_attempt DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
$checkAttempts->execute();

// Get current attempts
$getAttempts = $conn->prepare("SELECT attempts FROM department_verification_attempts WHERE email = ?");
$getAttempts->bind_param("s", $email);
$getAttempts->execute();
$attemptsResult = $getAttempts->get_result();

$attempts = ($attemptsResult->num_rows > 0) ? $attemptsResult->fetch_assoc()['attempts'] : 0;

// Check for lockout
if ($attempts >= $maxAttempts) {
    $timeQuery = $conn->prepare("SELECT TIMESTAMPDIFF(MINUTE, last_attempt, NOW()) as time_since_last FROM department_verification_attempts WHERE email = ?");
    $timeQuery->bind_param("s", $email);
    $timeQuery->execute();
    $timeResult = $timeQuery->get_result();
    $timeSinceLast = $timeResult->fetch_assoc()['time_since_last'];
    
    // If less than 15 minutes have passed, keep lockout
    if ($timeSinceLast < 15) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Too many failed verification attempts. Please try again later.', 
            'locked' => true
        ]);
        exit;
    } else {
        // Reset attempts after 15 minutes
        $resetAttempts = $conn->prepare("UPDATE department_verification_attempts SET attempts = 0 WHERE email = ?");
        $resetAttempts->bind_param("s", $email);
        $resetAttempts->execute();
        $attempts = 0;
    }
}

// Get user data from users table
$checkUser = $conn->prepare("SELECT u.user_id, u.first_name, u.last_name, u.role, 
                                  d.staff_id, d.department_id, dept.name as department_name 
                             FROM users u
                             JOIN department_staff d ON u.user_id = d.user_id 
                             JOIN departments dept ON d.department_id = dept.department_id
                             WHERE u.email = ? AND (u.role = 'department_head' OR u.role = 'department_secretary')");
$checkUser->bind_param("s", $email);
$checkUser->execute();
$userResult = $checkUser->get_result();

if ($userResult->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}

$userData = $userResult->fetch_assoc();

// Verify the code
$verifyCode = $conn->prepare("SELECT * FROM department_verification_codes 
                             WHERE email = ? AND code = ? AND expiry_time > NOW()");
$verifyCode->bind_param("ss", $email, $code);
$verifyCode->execute();
$result = $verifyCode->get_result();

if ($result->num_rows > 0) {
    // Code is valid, delete it from the database
    $deleteCode = $conn->prepare("DELETE FROM department_verification_codes WHERE email = ?");
    $deleteCode->bind_param("s", $email);
    $deleteCode->execute();
    
    // Reset verification attempts
    $resetAttempts = $conn->prepare("DELETE FROM department_verification_attempts WHERE email = ?");
    $resetAttempts->bind_param("s", $email);
    $resetAttempts->execute();
    
    // Create session for the user
    $_SESSION['user_id'] = $userData['user_id'];
    $_SESSION['email'] = $email;
    $_SESSION['first_name'] = $userData['first_name'];
    $_SESSION['last_name'] = $userData['last_name'];
    $_SESSION['role'] = $userData['role'];
    $_SESSION['staff_id'] = $userData['staff_id'];
    $_SESSION['department_id'] = $userData['department_id'];
    $_SESSION['department_name'] = $userData['department_name'];
    $_SESSION['signin'] = true;
    
    // Clear temp user ID
    unset($_SESSION['temp_user_id']);
    
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    // Set last login timestamp
    $updateLogin = $conn->prepare("UPDATE users SET updated_at = NOW() WHERE user_id = ?");
    $updateLogin->bind_param("i", $userData['user_id']);
    $updateLogin->execute();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Verification successful'
    ]);
} else {
    // Increment attempt count
    if ($attemptsResult->num_rows > 0) {
        $updateAttempts = $conn->prepare("UPDATE department_verification_attempts 
                                         SET attempts = attempts + 1, last_attempt = NOW() 
                                         WHERE email = ?");
    } else {
        $updateAttempts = $conn->prepare("INSERT INTO department_verification_attempts 
                                         (email, attempts, last_attempt) VALUES (?, 1, NOW())");
    }
    $updateAttempts->bind_param("s", $email);
    $updateAttempts->execute();
    
    $attempts++;
    $attemptsRemaining = $maxAttempts - $attempts;
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid verification code',
        'attempts_remaining' => ($attemptsRemaining > 0) ? $attemptsRemaining : 0
    ]);
}

$conn->close();
?>