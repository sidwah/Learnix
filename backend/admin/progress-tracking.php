<?php
header('Content-Type: application/json');
require_once '../config.php'; // Use the provided config.php with MySQLi connection

$action = isset($_GET['action']) ? $_GET['action'] : '';

function sendResponse($success, $data = [], $message = '') {
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit;
}

switch ($action) {
    case 'get_courses':
        try {
            $query = "SELECT course_id, title FROM courses WHERE status = 'published' ORDER BY title";
            $result = mysqli_query($conn, $query);
            if (!$result) {
                throw new Exception(mysqli_error($conn));
            }
            $courses = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $courses[] = $row;
            }
            sendResponse(true, ['courses' => $courses]);
        } catch (Exception $e) {
            sendResponse(false, [], 'Error fetching courses: ' . $e->getMessage());
        }
        break;

    case 'get_enrollments':
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
            $sortColumn = isset($_GET['sort_column']) ? $_GET['sort_column'] : 'enrolled_at';
            $sortDirection = isset($_GET['sort_direction']) ? $_GET['sort_direction'] : 'desc';
            $status = isset($_GET['status']) ? $_GET['status'] : 'all';
            $courseId = isset($_GET['course_id']) ? $_GET['course_id'] : 'all';
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';

            $offset = ($page - 1) * $perPage;

            $allowedColumns = ['student', 'course', 'progress', 'quiz_score', 'certificate', 'enrolled_at'];
            $sortColumn = in_array($sortColumn, $allowedColumns) ? $sortColumn : 'enrolled_at';
            $sortDirection = strtoupper($sortDirection) === 'ASC' ? 'ASC' : 'DESC';

            $query = "SELECT e.enrollment_id, u.username AS student_name, u.email AS student_email, 
                            u.profile_pic, c.title AS course_title, e.completion_percentage, 
                            e.enrolled_at, cert.certificate_id,
                            AVG(qa.score) AS avg_quiz_score
                      FROM enrollments e
                      JOIN users u ON e.user_id = u.user_id
                      JOIN courses c ON e.course_id = c.course_id
                      LEFT JOIN certificates cert ON e.enrollment_id = cert.enrollment_id
                      LEFT JOIN student_quiz_attempts qa ON e.enrollment_id = qa.enrollment_id
                      WHERE 1=1";
            
            $params = [];
            $types = '';
            if ($status !== 'all') {
                if ($status === 'completed') {
                    $query .= " AND e.completion_percentage >= 100";
                } else {
                    $query .= " AND e.completion_percentage < 100";
                }
            }
            if ($courseId !== 'all') {
                $query .= " AND e.course_id = ?";
                $params[] = $courseId;
                $types .= 'i';
            }
            if ($search) {
                $query .= " AND (u.username LIKE ? OR u.email LIKE ? OR c.title LIKE ?)";
                $searchTerm = "%$search%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $types .= 'sss';
            }

            $query .= " GROUP BY e.enrollment_id";
            
            $columnMap = [
                'student' => 'u.username',
                'course' => 'c.title',
                'progress' => 'e.completion_percentage',
                'quiz_score' => 'avg_quiz_score',
                'certificate' => 'cert.certificate_id',
                'enrolled_at' => 'e.enrolled_at'
            ];
            $query .= " ORDER BY " . $columnMap[$sortColumn] . " $sortDirection";
            $query .= " LIMIT ? OFFSET ?";
            $params[] = $perPage;
            $params[] = $offset;
            $types .= 'ii';

            if (!empty($params)) {
                $stmt = mysqli_prepare($conn, $query);
                if (!$stmt) {
                    throw new Exception(mysqli_error($conn));
                }
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
            } else {
                $result = mysqli_query($conn, $query);
                if (!$result) {
                    throw new Exception(mysqli_error($conn));
                }
            }

            $enrollments = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $enrollments[] = $row;
            }

            // Summary statistics
            $summaryQuery = "SELECT 
                            COUNT(*) AS total,
                            SUM(CASE WHEN e.completion_percentage >= 100 THEN 1 ELSE 0 END) AS completed,
                            SUM(CASE WHEN e.completion_percentage < 100 THEN 1 ELSE 0 END) AS active,
                            COUNT(cert.certificate_id) AS certificates
                            FROM enrollments e
                            LEFT JOIN certificates cert ON e.enrollment_id = cert.enrollment_id";
            $summaryResult = mysqli_query($conn, $summaryQuery);
            if (!$summaryResult) {
                throw new Exception(mysqli_error($conn));
            }
            $summary = mysqli_fetch_assoc($summaryResult);

            $countResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM enrollments");
            $totalEnrollments = mysqli_fetch_assoc($countResult)['total'];
            $totalPages = ceil($totalEnrollments / $perPage);

            sendResponse(true, [
                'data' => $enrollments,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages
                ],
                'summary' => $summary
            ]);
        } catch (Exception $e) {
            sendResponse(false, [], 'Error fetching enrollments: ' . $e->getMessage());
        }
        break;

    case 'get_progress_details':
        try {
            $enrollmentId = isset($_GET['enrollment_id']) ? (int)$_GET['enrollment_id'] : 0;
            if (!$enrollmentId) {
                sendResponse(false, [], 'Invalid enrollment ID');
            }

            $query = "SELECT e.enrollment_id, u.username AS student_name, u.email AS student_email, 
                            u.profile_pic, c.title AS course_title, i.bio AS instructor_name, 
                            e.completion_percentage, e.enrolled_at, cert.certificate_id
                      FROM enrollments e
                      JOIN users u ON e.user_id = u.user_id
                      JOIN courses c ON e.course_id = c.course_id
                      JOIN instructors i ON c.instructor_id = i.instructor_id
                      LEFT JOIN certificates cert ON e.enrollment_id = cert.enrollment_id
                      WHERE e.enrollment_id = ?";
            $stmt = mysqli_prepare($conn, $query);
            if (!$stmt) {
                throw new Exception(mysqli_error($conn));
            }
            mysqli_stmt_bind_param($stmt, 'i', $enrollmentId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $enrollment = mysqli_fetch_assoc($result);

            if (!$enrollment) {
                sendResponse(false, [], 'Enrollment not found');
            }

            // Fetch topic progress
            $topicQuery = "SELECT st.title, p.completion_status
                          FROM progress p
                          JOIN section_topics st ON p.topic_id = st.topic_id
                          WHERE p.enrollment_id = ?";
            $topicStmt = mysqli_prepare($conn, $topicQuery);
            if (!$topicStmt) {
                throw new Exception(mysqli_error($conn));
            }
            mysqli_stmt_bind_param($topicStmt, 'i', $enrollmentId);
            mysqli_stmt_execute($topicStmt);
            $topicResult = mysqli_stmt_get_result($topicStmt);
            $enrollment['topics'] = [];
            while ($row = mysqli_fetch_assoc($topicResult)) {
                $enrollment['topics'][] = $row;
            }

            // Fetch quiz attempts
            $quizQuery = "SELECT q.quiz_title, qa.score, qa.passed
                         FROM student_quiz_attempts qa
                         JOIN quizzes q ON qa.quiz_id = q.quiz_id
                         WHERE qa.enrollment_id = ?";
            $quizStmt = mysqli_prepare($conn, $quizQuery);
            if (!$quizStmt) {
                throw new Exception(mysqli_error($conn));
            }
            mysqli_stmt_bind_param($quizStmt, 'i', $enrollmentId);
            mysqli_stmt_execute($quizStmt);
            $quizResult = mysqli_stmt_get_result($quizStmt);
            $enrollment['quiz_attempts'] = [];
            while ($row = mysqli_fetch_assoc($quizResult)) {
                $enrollment['quiz_attempts'][] = $row;
            }

            sendResponse(true, ['data' => $enrollment]);
        } catch (Exception $e) {
            sendResponse(false, [], 'Error fetching progress details: ' . $e->getMessage());
        }
        break;

    case 'issue_certificate':
        try {
            $enrollmentId = isset($_POST['enrollment_id']) ? (int)$_POST['enrollment_id'] : 0;
            if (!$enrollmentId) {
                sendResponse(false, [], 'Invalid enrollment ID');
            }

            // Check if enrollment is eligible
            $query = "SELECT completion_percentage, certificate_id 
                     FROM enrollments 
                     WHERE enrollment_id = ?";
            $stmt = mysqli_prepare($conn, $query);
            if (!$stmt) {
                throw new Exception(mysqli_error($conn));
            }
            mysqli_stmt_bind_param($stmt, 'i', $enrollmentId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $enrollment = mysqli_fetch_assoc($result);

            if (!$enrollment) {
                sendResponse(false, [], 'Enrollment not found');
            }
            if ($enrollment['completion_percentage'] < 100) {
                sendResponse(false, [], 'Enrollment not eligible for certificate');
            }
            if ($enrollment['certificate_id']) {
                sendResponse(false, [], 'Certificate already issued');
            }

            // Generate certificate
            $certificateHash = hash('sha256', $enrollmentId . time());
            $issueDate = date('Y-m-d H:i:s');
            $insertQuery = "INSERT INTO certificates (enrollment_id, issue_date, certificate_hash, download_count) 
                           VALUES (?, ?, ?, 0)";
            $insertStmt = mysqli_prepare($conn, $insertQuery);
            if (!$insertStmt) {
                throw new Exception(mysqli_error($conn));
            }
            mysqli_stmt_bind_param($insertStmt, 'iss', $enrollmentId, $issueDate, $certificateHash);
            mysqli_stmt_execute($insertStmt);

            sendResponse(true, [], 'Certificate issued successfully');
        } catch (Exception $e) {
            sendResponse(false, [], 'Error issuing certificate: ' . $e->getMessage());
        }
        break;

    case 'download_certificate':
        try {
            $certificateId = isset($_GET['certificate_id']) ? (int)$_GET['certificate_id'] : 0;
            if (!$certificateId) {
                sendResponse(false, [], 'Invalid certificate ID');
            }

            $query = "SELECT c.certificate_hash, e.user_id, e.course_id 
                     FROM certificates c 
                     JOIN enrollments e ON c.enrollment_id = e.enrollment_id 
                     WHERE c.certificate_id = ?";
            $stmt = mysqli_prepare($conn, $query);
            if (!$stmt) {
                throw new Exception(mysqli_error($conn));
            }
            mysqli_stmt_bind_param($stmt, 'i', $certificateId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $certificate = mysqli_fetch_assoc($result);

            if (!$certificate) {
                sendResponse(false, [], 'Certificate not found');
            }

            // Update download count
            $updateQuery = "UPDATE certificates SET download_count = download_count + 1 
                           WHERE certificate_id = ?";
            $updateStmt = mysqli_prepare($conn, $updateQuery);
            if (!$updateStmt) {
                throw new Exception(mysqli_error($conn));
            }
            mysqli_stmt_bind_param($updateStmt, 'i', $certificateId);
            mysqli_stmt_execute($updateStmt);

            // For simplicity, return a JSON response (in a real system, generate a PDF)
            sendResponse(true, ['hash' => $certificate['certificate_hash']], 'Certificate download initiated');
        } catch (Exception $e) {
            sendResponse(false, [], 'Error downloading certificate: ' . $e->getMessage());
        }
        break;

    default:
        sendResponse(false, [], 'Invalid action');
}
?>