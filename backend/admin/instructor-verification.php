<?php
// Include database connection
require_once '../config.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Check if user is admin (implement your own auth check here)
// if (!isAdmin()) {
//     http_response_code(403);
//     echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
//     exit;
// }

// Get the action parameter
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle different actions
switch ($action) {
    case 'get_verification_requests':
        getVerificationRequests();
        break;
    case 'update_status':
        updateVerificationStatus();
        break;
    case 'get_request_details':
        getRequestDetails();
        break;
    case 'test_file_paths':
        getFilePathTest();
        break;
    case 'download_document':
        downloadDocument();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

/**
 * Get verification requests with pagination, sorting, and filtering
 */
function getVerificationRequests() {
    global $conn;
    
    // Get pagination parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
    $offset = ($page - 1) * $perPage;
    
    // Get filter parameters
    $status = isset($_GET['status']) ? $_GET['status'] : 'all';
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    
    // Get sorting parameters
    $sortColumn = isset($_GET['sort_column']) ? $_GET['sort_column'] : 'submitted_at';
    $sortDirection = isset($_GET['sort_direction']) ? $_GET['sort_direction'] : 'desc';
    
    // Validate sort column to prevent SQL injection
    $allowedColumns = ['name', 'status', 'submitted_at', 'document_count'];
    if (!in_array($sortColumn, $allowedColumns)) {
        $sortColumn = 'submitted_at';
    }
    
    // Validate sort direction
    if ($sortDirection !== 'asc' && $sortDirection !== 'desc') {
        $sortDirection = 'desc';
    }
    
    // Build the WHERE clause for filtering
    $whereClause = "1=1"; // Always true condition to start
    
    if ($status !== 'all') {
        $whereClause .= " AND vr.status = '" . mysqli_real_escape_string($conn, $status) . "'";
    }
    
    if (!empty($search)) {
        $searchTerm = mysqli_real_escape_string($conn, $search);
        $whereClause .= " AND (u.first_name LIKE '%$searchTerm%' OR u.last_name LIKE '%$searchTerm%' OR u.email LIKE '%$searchTerm%')";
    }
    
    // Count total records for pagination
    $countQuery = "SELECT COUNT(*) as total FROM instructor_verification_requests vr
                  JOIN instructors i ON vr.instructor_id = i.instructor_id
                  JOIN users u ON i.user_id = u.user_id
                  WHERE $whereClause";
    
    $countResult = mysqli_query($conn, $countQuery);
    $totalCount = mysqli_fetch_assoc($countResult)['total'];
    
    // Determine the sort column in the SQL query
    $sqlSortColumn = 'vr.submitted_at';
    switch ($sortColumn) {
        case 'name':
            $sqlSortColumn = "CONCAT(u.first_name, ' ', u.last_name)";
            break;
        case 'status':
            $sqlSortColumn = 'vr.status';
            break;
        case 'document_count':
            $sqlSortColumn = 'document_count';
            break;
    }
    
    // Main query to get verification requests
    $query = "SELECT vr.verification_id, vr.instructor_id, vr.credentials, vr.status, 
              vr.submitted_at, vr.reviewed_at, vr.rejection_reason,
              u.user_id, u.first_name, u.last_name, u.email, u.profile_pic, u.created_at as join_date,
              (SELECT COUNT(*) FROM instructor_verification_documents vd WHERE vd.verification_id = vr.verification_id) as document_count
              FROM instructor_verification_requests vr
              JOIN instructors i ON vr.instructor_id = i.instructor_id
              JOIN users u ON i.user_id = u.user_id
              WHERE $whereClause
              ORDER BY $sqlSortColumn $sortDirection
              LIMIT $offset, $perPage";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
        return;
    }
    
    $requests = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $requests[] = [
            'id' => (int)$row['verification_id'],
            'instructorId' => (int)$row['instructor_id'],
            'userId' => (int)$row['user_id'],
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'email' => $row['email'],
            'status' => $row['status'],
            'submitted' => $row['submitted_at'],
            'reviewed' => $row['reviewed_at'],
            'rejectionReason' => $row['rejection_reason'],
            'documentCount' => (int)$row['document_count'],
            'profilePic' => $row['profile_pic'],
            'joinDate' => $row['join_date'],
            'credentials' => htmlspecialchars_decode($row['credentials'])
        ];
    }
    
    // Get summary counts
    $summaryQuery = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN vr.status = 'approved' THEN 1 ELSE 0 END) as verified,
                    SUM(CASE WHEN vr.status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN vr.status = 'rejected' THEN 1 ELSE 0 END) as rejected
                    FROM instructor_verification_requests vr";
    
    $summaryResult = mysqli_query($conn, $summaryQuery);
    $summary = mysqli_fetch_assoc($summaryResult);
    
    echo json_encode([
        'success' => true,
        'data' => $requests,
        'pagination' => [
            'total' => (int)$totalCount,
            'per_page' => $perPage,
            'current_page' => $page,
            'total_pages' => ceil($totalCount / $perPage)
        ],
        'summary' => [
            'total' => (int)$summary['total'],
            'verified' => (int)$summary['verified'],
            'pending' => (int)$summary['pending'],
            'rejected' => (int)$summary['rejected']
        ]
    ]);
}

/**
 * Update verification status (approve/reject)
 */
function updateVerificationStatus() {
    global $conn;
    
    // Get data from POST
    $verificationId = isset($_POST['verification_id']) ? (int)$_POST['verification_id'] : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $rejectionReason = isset($_POST['rejection_reason']) ? $_POST['rejection_reason'] : null;
    
    // Validate status
    if ($status !== 'approved' && $status !== 'rejected') {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        return;
    }
    
    // Start a transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Update the verification request status
        $updateQuery = "UPDATE instructor_verification_requests 
                      SET status = ?, 
                          reviewed_at = NOW(), 
                          rejection_reason = ? 
                      WHERE verification_id = ?";
        
        $stmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($stmt, 'ssi', $status, $rejectionReason, $verificationId);
        $requestResult = mysqli_stmt_execute($stmt);
        
        if (!$requestResult) {
            throw new Exception('Failed to update verification request');
        }
        
        // Get the instructor ID from the verification request
        $instructorQuery = "SELECT instructor_id FROM instructor_verification_requests WHERE verification_id = ?";
        $stmt = mysqli_prepare($conn, $instructorQuery);
        mysqli_stmt_bind_param($stmt, 'i', $verificationId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $instructorId = mysqli_fetch_assoc($result)['instructor_id'];
        
        // Update the instructor's verification status
        $instructorStatus = ($status === 'approved') ? 'verified' : 'unverified';
        $updateInstructorQuery = "UPDATE instructors SET verification_status = ? WHERE instructor_id = ?";
        $stmt = mysqli_prepare($conn, $updateInstructorQuery);
        mysqli_stmt_bind_param($stmt, 'si', $instructorStatus, $instructorId);
        $instructorResult = mysqli_stmt_execute($stmt);
        
        if (!$instructorResult) {
            throw new Exception('Failed to update instructor status');
        }
        
        // Commit the transaction
        mysqli_commit($conn);
        
        echo json_encode(['success' => true, 'message' => 'Verification status updated successfully']);
    } catch (Exception $e) {
        // Rollback the transaction on error
        mysqli_rollback($conn);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

/**
 * Get details for a specific verification request
 */
function getRequestDetails() {
    global $conn;
    
    $verificationId = isset($_GET['verification_id']) ? (int)$_GET['verification_id'] : 0;
    
    // Get the verification request details
    $query = "SELECT vr.verification_id, vr.instructor_id, vr.credentials, vr.status, 
             vr.submitted_at, vr.reviewed_at, vr.rejection_reason,
             u.user_id, u.first_name, u.last_name, u.email, u.profile_pic, u.created_at as join_date,
             i.bio
             FROM instructor_verification_requests vr
             JOIN instructors i ON vr.instructor_id = i.instructor_id
             JOIN users u ON i.user_id = u.user_id
             WHERE vr.verification_id = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $verificationId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$result || mysqli_num_rows($result) === 0) {
        echo json_encode(['success' => false, 'message' => 'Verification request not found']);
        return;
    }
    
    $requestData = mysqli_fetch_assoc($result);
    
    // Get documents for this verification request
    $docsQuery = "SELECT document_id, document_path, uploaded_at
                 FROM instructor_verification_documents
                 WHERE verification_id = ?
                 ORDER BY uploaded_at";
    
    $stmt = mysqli_prepare($conn, $docsQuery);
    mysqli_stmt_bind_param($stmt, 'i', $verificationId);
    mysqli_stmt_execute($stmt);
    $docsResult = mysqli_stmt_get_result($stmt);
    
    $documents = [];
    while ($doc = mysqli_fetch_assoc($docsResult)) {
        // Define possible file paths to check
        $possiblePaths = [
            '../uploads/verification-docs/' . $doc['document_path'],
            './uploads/verification-docs/' . $doc['document_path'],
            '../../uploads/verification-docs/' . $doc['document_path'],
            '../uploads/instructor-documents/' . $doc['document_path'],
            $_SERVER['DOCUMENT_ROOT'] . '/uploads/verification-docs/' . $doc['document_path'],
            $_SERVER['DOCUMENT_ROOT'] . '/uploads/instructor-documents/' . $doc['document_path'],
            'uploads/verification-docs/' . $doc['document_path']
        ];
        
        // Find the first path that exists
        $filePath = '../uploads/verification-docs/' . $doc['document_path']; // Default path
        $fileExists = false;
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $filePath = $path;
                $fileExists = true;
                error_log("Found document file at: " . $path);
                break;
            }
        }
        
        if (!$fileExists) {
            error_log("File not found: " . $doc['document_path'] . ". Checked paths: " . implode(", ", $possiblePaths));
        }
        
        $fileSize = $fileExists ? filesize($filePath) : 0;
        
        // Get file type based on extension since mime_content_type may not be available on all servers
        $fileExt = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $fileType = '';
        
        // Map common extensions to mime types
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'txt' => 'text/plain'
        ];
        
        if (isset($mimeTypes[$fileExt])) {
            $fileType = $mimeTypes[$fileExt];
        } else if (function_exists('mime_content_type') && $fileExists) {
            // Use mime_content_type as fallback if available
            $fileType = mime_content_type($filePath);
        } else {
            $fileType = 'application/octet-stream'; // Default mime type
        }
        
        $documents[] = [
            'id' => (int)$doc['document_id'],
            'path' => $doc['document_path'],
            'uploaded' => $doc['uploaded_at'],
            'exists' => $fileExists,
            'size' => $fileSize,
            'type' => $fileType
        ];
    }
    
    // Make sure credentials are properly formatted
    $credentials = htmlspecialchars_decode($requestData['credentials']);
    
    $response = [
        'success' => true,
        'data' => [
            'id' => (int)$requestData['verification_id'],
            'instructorId' => (int)$requestData['instructor_id'],
            'userId' => (int)$requestData['user_id'],
            'name' => $requestData['first_name'] . ' ' . $requestData['last_name'],
            'email' => $requestData['email'],
            'status' => $requestData['status'],
            'submitted' => $requestData['submitted_at'],
            'reviewed' => $requestData['reviewed_at'],
            'rejectionReason' => $requestData['rejection_reason'],
            'profilePic' => $requestData['profile_pic'],
            'joinDate' => $requestData['join_date'],
            'credentials' => $credentials,
            'bio' => $requestData['bio'],
            'documents' => $documents
        ]
    ];
    
    echo json_encode($response);
}

/**
 * Get file path information to help troubleshoot document loading issues
 */
function getFilePathTest() {
    // Check for a specific file to find
    $fileToFind = isset($_GET['find_file']) ? $_GET['find_file'] : null;
    $fileFound = false;
    $fileLocations = [];
    
    // Get server file paths for debugging
    $testPaths = [
        'absolute_path' => realpath('../uploads/verification-docs'),
        'document_root' => $_SERVER['DOCUMENT_ROOT'],
        'script_dir' => dirname(__FILE__),
        'relative_path_up' => realpath('../uploads'),
        'relative_path_current' => realpath('./uploads'),
        'current_working_dir' => getcwd(),
        'exists_check' => [
            '../uploads/verification-docs' => is_dir('../uploads/verification-docs'),
            './uploads/verification-docs' => is_dir('./uploads/verification-docs'),
            '/uploads/verification-docs' => is_dir('/uploads/verification-docs'),
            $_SERVER['DOCUMENT_ROOT'] . '/uploads/verification-docs' => 
                is_dir($_SERVER['DOCUMENT_ROOT'] . '/uploads/verification-docs'),
            '../uploads/instructor-documents' => is_dir('../uploads/instructor-documents'),
            './uploads/instructor-documents' => is_dir('./uploads/instructor-documents'),
            '/uploads/instructor-documents' => is_dir('/uploads/instructor-documents'),
            $_SERVER['DOCUMENT_ROOT'] . '/uploads/instructor-documents' => 
                is_dir($_SERVER['DOCUMENT_ROOT'] . '/uploads/instructor-documents'),
            '../uploads/' => is_dir('../uploads/'),
            './uploads/' => is_dir('./uploads/'),
            '/uploads/' => is_dir('/uploads/'),
            $_SERVER['DOCUMENT_ROOT'] . '/uploads/' => 
                is_dir($_SERVER['DOCUMENT_ROOT'] . '/uploads/')
        ]
    ];
    
    // If a filename is provided, search for it in various directories
    if ($fileToFind) {
        $possibleFilePaths = [
            '../uploads/verification-docs/' . $fileToFind,
            './uploads/verification-docs/' . $fileToFind,
            '../../uploads/verification-docs/' . $fileToFind,
            '../uploads/' . $fileToFind,
            './uploads/' . $fileToFind,
            '../../uploads/' . $fileToFind,
            '../uploads/instructor-documents/' . $fileToFind,
            './uploads/instructor-documents/' . $fileToFind,
            '../../uploads/instructor-documents/' . $fileToFind,
            $_SERVER['DOCUMENT_ROOT'] . '/uploads/verification-docs/' . $fileToFind,
            $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $fileToFind,
            $_SERVER['DOCUMENT_ROOT'] . '/uploads/instructor-documents/' . $fileToFind,
            'uploads/verification-docs/' . $fileToFind,
            'uploads/' . $fileToFind,
            'uploads/instructor-documents/' . $fileToFind
        ];
        
        foreach ($possibleFilePaths as $path) {
            if (file_exists($path)) {
                $fileFound = true;
                $fileLocations[] = [
                    'path' => $path,
                    'size' => filesize($path),
                    'last_modified' => date('Y-m-d H:i:s', filemtime($path)),
                    'readable' => is_readable($path)
                ];
            }
        }
    }
    
    // Try to create a sample test file to check write permissions
    $writeTest = [];
    $testDirs = [
        'documents' => '../uploads/verification-docs/',
        'profiles' => '../uploads/profile-pics/'
    ];
    
    foreach ($testDirs as $key => $dir) {
        if (is_dir($dir)) {
            $testFile = $dir . 'test_write_' . time() . '.txt';
            $success = @file_put_contents($testFile, 'Write test');
            $writeTest[$key] = [
                'dir' => $dir,
                'can_write' => $success !== false,
                'error' => $success === false ? error_get_last()['message'] : null
            ];
            
            // Clean up test file
            if ($success !== false) {
                @unlink($testFile);
            }
        } else {
            $writeTest[$key] = [
                'dir' => $dir,
                'can_write' => false,
                'error' => 'Directory does not exist'
            ];
        }
    }
    
    // List existing files
    $documentFiles = [];
    // Check various possible paths
    $possibleDocDirs = [
        '../uploads/verification-docs/',
        './uploads/verification-docs/',
        '../../uploads/verification-docs/',
        'uploads/verification-docs/',
        $_SERVER['DOCUMENT_ROOT'] . '/uploads/verification-docs/'
    ];
    
    $documentDir = '../uploads/verification-docs/';
    
    foreach ($possibleDocDirs as $dir) {
        if (is_dir($dir)) {
            $documentDir = $dir;
            error_log("Found documents directory at: " . $dir);
            break;
        }
    }
    
    error_log("Using documents directory: " . $documentDir);
    if (is_dir($documentDir)) {
        $files = scandir($documentDir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $fullPath = $documentDir . $file;
                $documentFiles[] = [
                    'name' => $file,
                    'size' => filesize($fullPath),
                    'modified' => date("Y-m-d H:i:s", filemtime($fullPath)),
                    'is_readable' => is_readable($fullPath)
                ];
            }
        }
    }
    
    // Check server configuration
    $serverInfo = [
        'php_version' => phpversion(),
        'open_basedir' => ini_get('open_basedir'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'max_file_uploads' => ini_get('max_file_uploads'),
        'post_max_size' => ini_get('post_max_size'),
        'memory_limit' => ini_get('memory_limit'),
        'file_uploads' => ini_get('file_uploads'),
        'upload_tmp_dir' => ini_get('upload_tmp_dir')
    ];
    
    // Do a recursive directory scan to find all files
    $uploadsDirScan = [];
    $uploadsDirs = ['../uploads/', './uploads/', '../../uploads/', $_SERVER['DOCUMENT_ROOT'] . '/uploads/'];
    
    foreach ($uploadsDirs as $dir) {
        if (is_dir($dir)) {
            $uploadsDirScan[] = [
                'base_dir' => $dir,
                'subdirectories' => array_filter(scandir($dir) ?: [], function($item) use ($dir) {
                    return $item !== '.' && $item !== '..' && is_dir($dir . $item);
                })
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'paths' => $testPaths,
        'write_test' => $writeTest,
        'document_files' => $documentFiles,
        'server_info' => $serverInfo,
        'file_search' => [
            'query' => $fileToFind,
            'found' => $fileFound,
            'locations' => $fileLocations
        ],
        'uploads_directory_scan' => $uploadsDirScan
    ]);
}

/**
 * Download a document directly through PHP (useful for access control)
 */
function downloadDocument() {
    $documentPath = isset($_GET['path']) ? $_GET['path'] : '';
    
    if (empty($documentPath)) {
        echo json_encode(['success' => false, 'message' => 'No document path specified']);
        return;
    }
    
    // Log the request for debugging
    error_log("Document download requested: " . $documentPath);
    
    // Security check - ensure only files with known extensions are downloaded
    $fileExt = strtolower(pathinfo($documentPath, PATHINFO_EXTENSION));
    $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];
    
    if (!in_array($fileExt, $allowedExtensions)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file extension']);
        return;
    }
    
    // Define possible file paths to check
    $possiblePaths = [
        '../uploads/verification-docs/' . $documentPath,
        './uploads/verification-docs/' . $documentPath,
        '../../uploads/verification-docs/' . $documentPath,
        '../uploads/instructor-documents/' . $documentPath,
        $_SERVER['DOCUMENT_ROOT'] . '/uploads/verification-docs/' . $documentPath,
        $_SERVER['DOCUMENT_ROOT'] . '/uploads/instructor-documents/' . $documentPath,
        'uploads/verification-docs/' . $documentPath
    ];
    
    // Find the first path that exists
    $filePath = null;
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            $filePath = $path;
            break;
        }
    }
    
    // If no valid path found, return error
    if ($filePath === null) {
        // Log the paths we checked for debugging
        error_log("File not found. Checked paths: " . implode(", ", $possiblePaths));
        echo json_encode([
            'success' => false, 
            'message' => 'File not found: ' . $documentPath,
            'paths_checked' => $possiblePaths
        ]);
        return;
    }
    
    // Determine the MIME type
    $mimeTypes = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'txt' => 'text/plain'
    ];
    
    $mimeType = isset($mimeTypes[$fileExt]) ? $mimeTypes[$fileExt] : 'application/octet-stream';
    
    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Set the appropriate headers
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: attachment; filename="' . basename($documentPath) . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    // Read the file and output it
    readfile($filePath);
    exit;
}