<?php
// backend/department/manage_instructors.php
session_start();
require_once '../config.php';

// Check if user is logged in and has department head role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get user's department
$dept_query = "SELECT ds.department_id 
               FROM department_staff ds 
               WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' AND ds.deleted_at IS NULL
               LIMIT 1";
$dept_stmt = $conn->prepare($dept_query);
$dept_stmt->bind_param("i", $_SESSION['user_id']);
$dept_stmt->execute();
$dept_result = $dept_stmt->get_result();

if ($dept_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Department not found']);
    exit;
}

$department = $dept_result->fetch_assoc();
$department_id = $department['department_id'];

// Get action
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_course_instructors':
            $course_id = $_GET['course_id'] ?? 0;
            
            // Verify course belongs to department
            $verify_query = "SELECT c.course_id, c.title 
                           FROM courses c
                           WHERE c.course_id = ? AND c.department_id = ? AND c.deleted_at IS NULL";
            $verify_stmt = $conn->prepare($verify_query);
            $verify_stmt->bind_param("ii", $course_id, $department_id);
            $verify_stmt->execute();
            $verify_result = $verify_stmt->get_result();
            
            if ($verify_result->num_rows === 0) {
                throw new Exception('Course not found');
            }
            
            // Get current instructors
            $instructors_query = "SELECT 
                                     u.user_id,
                                     u.first_name,
                                     u.last_name,
                                     u.email,
                                     u.profile_pic,
                                     i.instructor_id,
                                     ci.is_primary,
                                     ci.assigned_at
                                 FROM course_instructors ci
                                 JOIN instructors i ON ci.instructor_id = i.instructor_id
                                 JOIN users u ON i.user_id = u.user_id
                                 WHERE ci.course_id = ? AND ci.deleted_at IS NULL
                                 ORDER BY ci.is_primary DESC, u.first_name";
            
            $inst_stmt = $conn->prepare($instructors_query);
            $inst_stmt->bind_param("i", $course_id);
            $inst_stmt->execute();
            $inst_result = $inst_stmt->get_result();
            
            $instructors = [];
            while ($instructor = $inst_result->fetch_assoc()) {
                $instructors[] = $instructor;
            }
            
            echo json_encode([
                'success' => true,
                'instructors' => $instructors
            ]);
            break;
            
        case 'get_available_instructors':
            $course_id = $_GET['course_id'] ?? 0;
            
            // Get instructors that belong to the department but are not assigned to this course
            $available_query = "SELECT 
                                   u.user_id,
                                   u.first_name,
                                   u.last_name,
                                   u.email,
                                   u.profile_pic,
                                   i.instructor_id
                               FROM department_instructors di
                               JOIN instructors i ON di.instructor_id = i.instructor_id
                               JOIN users u ON i.user_id = u.user_id
                               WHERE di.department_id = ? 
                                   AND di.status = 'active' 
                                   AND di.deleted_at IS NULL
                                   AND i.instructor_id NOT IN (
                                       SELECT instructor_id 
                                       FROM course_instructors 
                                       WHERE course_id = ? AND deleted_at IS NULL
                                   )
                               ORDER BY u.first_name";
            
            $avail_stmt = $conn->prepare($available_query);
            $avail_stmt->bind_param("ii", $department_id, $course_id);
            $avail_stmt->execute();
            $avail_result = $avail_stmt->get_result();
            
            $available_instructors = [];
            while ($instructor = $avail_result->fetch_assoc()) {
                $available_instructors[] = $instructor;
            }
            
            echo json_encode([
                'success' => true,
                'instructors' => $available_instructors
            ]);
            break;
            
        case 'assign_instructor':
            $course_id = $_POST['course_id'] ?? 0;
            $instructor_id = $_POST['instructor_id'] ?? 0;
            $is_primary = $_POST['is_primary'] ?? 0;
            
            // Verify course belongs to department
            $verify_query = "SELECT c.course_id 
                           FROM courses c
                           WHERE c.course_id = ? AND c.department_id = ? AND c.deleted_at IS NULL";
            $verify_stmt = $conn->prepare($verify_query);
            $verify_stmt->bind_param("ii", $course_id, $department_id);
            $verify_stmt->execute();
            $verify_result = $verify_stmt->get_result();
            
            if ($verify_result->num_rows === 0) {
                throw new Exception('Course not found');
            }
            
            // Verify instructor belongs to department
            $inst_verify_query = "SELECT di.instructor_id 
                                 FROM department_instructors di
                                 WHERE di.instructor_id = ? AND di.department_id = ? 
                                     AND di.status = 'active' AND di.deleted_at IS NULL";
            $inst_verify_stmt = $conn->prepare($inst_verify_query);
            $inst_verify_stmt->bind_param("ii", $instructor_id, $department_id);
            $inst_verify_stmt->execute();
            $inst_verify_result = $inst_verify_stmt->get_result();
            
            if ($inst_verify_result->num_rows === 0) {
                throw new Exception('Instructor not found or not in department');
            }
            
            $conn->begin_transaction();
            
            // If making this instructor primary, remove primary status from others
            if ($is_primary) {
                $remove_primary_sql = "UPDATE course_instructors 
                                      SET is_primary = 0 
                                      WHERE course_id = ?";
                $remove_primary_stmt = $conn->prepare($remove_primary_sql);
                $remove_primary_stmt->bind_param("i", $course_id);
                $remove_primary_stmt->execute();
            }
            
            // Check if instructor is already assigned
            $check_sql = "SELECT assignment_id FROM course_instructors 
                         WHERE course_id = ? AND instructor_id = ? AND deleted_at IS NULL";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ii", $course_id, $instructor_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                // Update existing assignment
                $update_sql = "UPDATE course_instructors 
                              SET is_primary = ?, assigned_at = CURRENT_TIMESTAMP
                              WHERE course_id = ? AND instructor_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("iii", $is_primary, $course_id, $instructor_id);
                $update_stmt->execute();
            } else {
                // Create new assignment
                $assign_sql = "INSERT INTO course_instructors 
                              (course_id, instructor_id, assigned_by, is_primary, assigned_at)
                              VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
                $assign_stmt = $conn->prepare($assign_sql);
                $assign_stmt->bind_param("iiii", $course_id, $instructor_id, $_SESSION['user_id'], $is_primary);
                $assign_stmt->execute();
            }
            
            $conn->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Instructor assigned successfully'
            ]);
            break;
            
        case 'remove_instructor':
            $course_id = $_POST['course_id'] ?? 0;
            $instructor_id = $_POST['instructor_id'] ?? 0;
            
            // Verify course belongs to department
            $verify_query = "SELECT c.course_id 
                           FROM courses c
                           WHERE c.course_id = ? AND c.department_id = ? AND c.deleted_at IS NULL";
            $verify_stmt = $conn->prepare($verify_query);
            $verify_stmt->bind_param("ii", $course_id, $department_id);
            $verify_stmt->execute();
            $verify_result = $verify_stmt->get_result();
            
            if ($verify_result->num_rows === 0) {
                throw new Exception('Course not found');
            }
            
            // Check if this is the last instructor
            $count_sql = "SELECT COUNT(*) as count FROM course_instructors 
                         WHERE course_id = ? AND deleted_at IS NULL";
            $count_stmt = $conn->prepare($count_sql);
            $count_stmt->bind_param("i", $course_id);
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $count_data = $count_result->fetch_assoc();
            
            if ($count_data['count'] <= 1) {
                throw new Exception('Cannot remove the last instructor from a course');
            }
            
            // Soft delete the assignment
            $remove_sql = "UPDATE course_instructors 
                          SET deleted_at = CURRENT_TIMESTAMP 
                          WHERE course_id = ? AND instructor_id = ?";
            $remove_stmt = $conn->prepare($remove_sql);
            $remove_stmt->bind_param("ii", $course_id, $instructor_id);
            $remove_stmt->execute();
            
            // If this was the primary instructor, make another one primary
            $was_primary_query = "SELECT is_primary FROM course_instructors 
                                 WHERE course_id = ? AND instructor_id = ? AND deleted_at IS NOT NULL
                                 ORDER BY deleted_at DESC LIMIT 1";
            $was_primary_stmt = $conn->prepare($was_primary_query);
            $was_primary_stmt->bind_param("ii", $course_id, $instructor_id);
            $was_primary_stmt->execute();
            $was_primary_result = $was_primary_stmt->get_result();
            $was_primary_data = $was_primary_result->fetch_assoc();
            
            if ($was_primary_data && $was_primary_data['is_primary']) {
                $make_primary_sql = "UPDATE course_instructors 
                                    SET is_primary = 1 
                                    WHERE course_id = ? AND deleted_at IS NULL 
                                    ORDER BY assigned_at ASC LIMIT 1";
                $make_primary_stmt = $conn->prepare($make_primary_sql);
                $make_primary_stmt->bind_param("i", $course_id);
                $make_primary_stmt->execute();
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Instructor removed successfully'
            ]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    if (isset($conn) && $conn->autocommit(false)) {
        $conn->rollback();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>