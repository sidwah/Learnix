<?php
// includes/department/courses_functions.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function getCoursesByDepartment($department_id, $filters = []) {
    global $conn;
    
    $where_conditions = ["c.department_id = ?"];
    $params = [$department_id];
    $param_types = "i";
    
    // Add search condition
    if (!empty($filters['search'])) {
        $where_conditions[] = "(c.title LIKE ? OR c.short_description LIKE ?)";
        $params[] = "%{$filters['search']}%";
        $params[] = "%{$filters['search']}%";
        $param_types .= "ss";
    }
    
    // Add status condition
    if (!empty($filters['status'])) {
        if ($filters['status'] === 'pending') {
            $where_conditions[] = "c.approval_status IN ('pending', 'revisions_requested')";
        } else {
            $where_conditions[] = "c.status = ?";
            $params[] = $filters['status'];
            $param_types .= "s";
        }
    }
    
    // Add category condition
    if (!empty($filters['category'])) {
        $where_conditions[] = "cat.name = ?";
        $params[] = $filters['category'];
        $param_types .= "s";
    }
    
    // Add level condition
    if (!empty($filters['level'])) {
        $where_conditions[] = "c.course_level = ?";
        $params[] = $filters['level'];
        $param_types .= "s";
    }
    
    // Build ORDER BY clause
    $order_by = "c.created_at DESC";
    if (isset($filters['sort'])) {
        switch ($filters['sort']) {
            case 'oldest':
                $order_by = "c.created_at ASC";
                break;
            case 'name':
                $order_by = "c.title ASC";
                break;
            case 'updated':
                $order_by = "c.updated_at DESC";
                break;
        }
    }
    
    $sql = "SELECT 
                c.*,
                cat.name as category_name,
                sub.name as subcategory_name,
                (SELECT COUNT(*) FROM enrollments WHERE course_id = c.course_id AND status = 'Active') as student_count,
                (SELECT AVG(rating) FROM course_ratings WHERE course_id = c.course_id) as average_rating
            FROM courses c
            JOIN subcategories sub ON c.subcategory_id = sub.subcategory_id
            JOIN categories cat ON sub.category_id = cat.category_id
            WHERE " . implode(" AND ", $where_conditions) . "
            ORDER BY $order_by";
    
    if (isset($filters['limit'])) {
        $sql .= " LIMIT ?";
        $params[] = $filters['limit'];
        $param_types .= "i";
        
        if (isset($filters['offset'])) {
            $sql .= " OFFSET ?";
            $params[] = $filters['offset'];
            $param_types .= "i";
        }
    }
    
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($param_types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        // Get instructors for each course
        $row['instructors'] = getInstructorsForCourse($row['course_id']);
        $courses[] = $row;
    }
    
    return $courses;
}

function getCourseStats($department_id) {
    global $conn;
    
    $stats_query = "SELECT 
                       COUNT(*) as total_courses,
                       SUM(CASE WHEN c.status = 'Published' THEN 1 ELSE 0 END) as published_courses,
                       SUM(CASE WHEN c.status = 'Draft' OR c.approval_status = 'pending' THEN 1 ELSE 0 END) as draft_pending_courses,
                       SUM(CASE WHEN c.approval_status = 'under_review' THEN 1 ELSE 0 END) as under_review_courses,
                       SUM(CASE WHEN c.approval_status = 'rejected' THEN 1 ELSE 0 END) as rejected_courses
                   FROM courses c
                   WHERE c.department_id = ? AND c.deleted_at IS NULL";
    
    $stats_stmt = $conn->prepare($stats_query);
    $stats_stmt->bind_param("i", $department_id);
    $stats_stmt->execute();
    $stats_result = $stats_stmt->get_result();
    $stats = $stats_result->fetch_assoc();
    
    // Calculate percentages
    $total = $stats['total_courses'];
    $stats['published_percentage'] = $total > 0 ? round(($stats['published_courses'] / $total) * 100) : 0;
    $stats['pending_percentage'] = $total > 0 ? round(($stats['draft_pending_courses'] / $total) * 100) : 0;
    $stats['review_percentage'] = $total > 0 ? round(($stats['under_review_courses'] / $total) * 100) : 0;
    
    return $stats;
}

function getInstructorsForCourse($course_id) {
    global $conn;
    
    $instructors_query = "SELECT 
                             u.user_id,
                             u.first_name,
                             u.last_name,
                             u.profile_pic,
                             ci.is_primary
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
    
    return $instructors;
}

function updateCourseStatus($course_id, $status, $approval_status = null) {
    global $conn;
    
    try {
        $conn->begin_transaction();
        
        $update_fields = ["status = ?"];
        $update_values = [$status];
        $param_types = "s";
        
        if ($approval_status !== null) {
            $update_fields[] = "approval_status = ?";
            $update_values[] = $approval_status;
            $param_types .= "s";
        }
        
        $update_fields[] = "updated_at = CURRENT_TIMESTAMP";
        $update_values[] = $course_id;
        $param_types .= "i";
        
        $update_sql = "UPDATE courses SET " . implode(", ", $update_fields) . " WHERE course_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param($param_types, ...$update_values);
        $update_stmt->execute();
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

function assignInstructorToCourse($course_id, $instructor_id, $is_primary = false, $assigned_by = null) {
    global $conn;
    
    try {
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
            $assigned_by = $assigned_by ?? $_SESSION['user_id'];
            $assign_stmt->bind_param("iiii", $course_id, $instructor_id, $assigned_by, $is_primary);
            $assign_stmt->execute();
        }
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

function removeInstructorFromCourse($course_id, $instructor_id) {
    global $conn;
    
    try {
        // Check if this is the last instructor
        $count_sql = "SELECT COUNT(*) as count FROM course_instructors 
                     WHERE course_id = ? AND deleted_at IS NULL";
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bind_param("i", $course_id);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $count_data = $count_result->fetch_assoc();
        
        if ($count_data['count'] <= 1) {
            return false; // Cannot remove the last instructor
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
        
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function formatCourseStatus($status, $approval_status = null) {
    $statuses = [
        'Draft' => ['label' => 'Draft', 'class' => 'bg-warning'],
        'Published' => ['label' => 'Published', 'class' => 'bg-success'],
        'Archived' => ['label' => 'Archived', 'class' => 'bg-secondary'],
    ];
    
    $approval_statuses = [
        'pending' => ['label' => 'Pending', 'class' => 'bg-warning'],
        'submitted_for_review' => ['label' => 'Submitted', 'class' => 'bg-info'],
        'under_review' => ['label' => 'Under Review', 'class' => 'bg-info'],
        'approved' => ['label' => 'Approved', 'class' => 'bg-success'],
        'rejected' => ['label' => 'Rejected', 'class' => 'bg-danger'],
        'revisions_requested' => ['label' => 'Revisions Needed', 'class' => 'bg-warning'],
    ];
    
    if ($approval_status && isset($approval_statuses[$approval_status])) {
        return $approval_statuses[$approval_status];
    }
    
    return $statuses[$status] ?? ['label' => $status, 'class' => 'bg-secondary'];
}

function getCourseThumbnail($course_id, $default = 'default.png') {
    global $conn;
    
    $thumbnail_query = "SELECT file_path FROM course_media 
                       WHERE course_id = ? AND media_type = 'thumbnail' 
                       ORDER BY display_order ASC LIMIT 1";
    $thumbnail_stmt = $conn->prepare($thumbnail_query);
    $thumbnail_stmt->bind_param("i", $course_id);
    $thumbnail_stmt->execute();
    $thumbnail_result = $thumbnail_stmt->get_result();
    
    if ($thumbnail_result->num_rows > 0) {
        $thumbnail = $thumbnail_result->fetch_assoc();
        return $thumbnail['file_path'];
    }
    
    return "../assets/img/course-thumbnails/{$default}";
}

// includes/department/courses_functions.php

// Replace the getCourseProgress function with this corrected version:
function getCourseProgress($course_id) {
    global $conn;
    
    $progress_query = "SELECT 
                          COUNT(s.section_id) as total_sections,
                          COUNT(t.topic_id) as total_topics,
                          COUNT(tc.content_id) as total_content,
                          CASE 
                              WHEN c.creation_step >= 4 THEN 100
                              WHEN c.creation_step = 3 THEN 75
                              WHEN c.creation_step = 2 THEN 50
                              WHEN c.creation_step = 1 THEN 25
                              ELSE 0
                          END as progress_percentage
                      FROM courses c
                      LEFT JOIN course_sections s ON c.course_id = s.course_id AND s.deleted_at IS NULL
                      LEFT JOIN section_topics t ON s.section_id = t.section_id
                      LEFT JOIN topic_content tc ON t.topic_id = tc.topic_id AND tc.deleted_at IS NULL
                      WHERE c.course_id = ?
                      GROUP BY c.course_id";
    
    $progress_stmt = $conn->prepare($progress_query);
    $progress_stmt->bind_param("i", $course_id);
    $progress_stmt->execute();
    $progress_result = $progress_stmt->get_result();
    
    if ($progress_result->num_rows > 0) {
        return $progress_result->fetch_assoc();
    }
    
    return [
        'total_sections' => 0,
        'total_topics' => 0,
        'total_content' => 0,
        'progress_percentage' => 0
    ];
}
?>