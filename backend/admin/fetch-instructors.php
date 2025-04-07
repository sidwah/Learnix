<?php
include("../config.php");

// Query to get complete instructor information
$sql = "SELECT
    u.user_id AS id,
    CONCAT(u.first_name, ' ', u.last_name) AS name,
    u.email,
    u.status,
    u.profile_pic,
    u.phone,
    u.location,
    u.created_at AS join_date,
    i.verification_status,
    i.bio,
    
    /* Count courses */
    (SELECT COUNT(*) FROM courses c WHERE c.instructor_id = i.instructor_id) AS courses_count,
    
    /* Count students */
    (SELECT COUNT(DISTINCT e.user_id) 
     FROM enrollments e 
     JOIN courses c ON e.course_id = c.course_id 
     WHERE c.instructor_id = i.instructor_id) AS students_count,
    
    /* Calculate revenue */
    (SELECT IFNULL(SUM(cp.amount), 0) 
     FROM course_payments cp 
     JOIN enrollments e ON cp.enrollment_id = e.enrollment_id 
     JOIN courses c ON e.course_id = c.course_id 
     WHERE c.instructor_id = i.instructor_id AND cp.status = 'Completed') AS total_revenue,
    
    /* Get status change reason if any */
    (SELECT reason 
     FROM user_status_logs 
     WHERE user_id = u.user_id 
     ORDER BY change_date DESC LIMIT 1) AS status_reason
    
FROM users u 
INNER JOIN instructors i ON u.user_id = i.user_id 
WHERE u.role = 'instructor'";

$result = $conn->query($sql);
$instructors = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Get specializations (tags from courses)
        $specializations_sql = "SELECT DISTINCT t.tag_name 
            FROM course_tag_mapping ctm
            JOIN tags t ON ctm.tag_id = t.tag_id
            JOIN courses c ON ctm.course_id = c.course_id
            WHERE c.instructor_id = (SELECT instructor_id FROM instructors WHERE user_id = ?)
            LIMIT 10";

        $stmt = $conn->prepare($specializations_sql);
        $stmt->bind_param("i", $row['id']);
        $stmt->execute();
        $spec_result = $stmt->get_result();

        $specializations = [];
        while ($spec_row = $spec_result->fetch_assoc()) {
            $specializations[] = $spec_row['tag_name'];
        }

        // Get course list
        $courses_sql = "SELECT 
            c.course_id,
            c.title,
            c.price,
            c.status,
            c.approval_status,
            (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.course_id) AS students,
            (SELECT AVG(rating) FROM course_ratings cr WHERE cr.course_id = c.course_id) AS rating
            FROM courses c
            WHERE c.instructor_id = (SELECT instructor_id FROM instructors WHERE user_id = ?)
            ";

        $stmt = $conn->prepare($courses_sql);
        $stmt->bind_param("i", $row['id']);
        $stmt->execute();
        $courses_result = $stmt->get_result();

        $courses = [];
        while ($course_row = $courses_result->fetch_assoc()) {
            $courses[] = [
                'id' => $course_row['course_id'],
                'title' => $course_row['title'],
                'price' => floatval($course_row['price']),
                'students' => intval($course_row['students']),
                'rating' => $course_row['rating'] ? floatval($course_row['rating']) : 0,
                'status' => $course_row['status'] === 'Published' && $course_row['approval_status'] === 'Approved' ? 'published' : 'draft'
            ];
        }

        $row['specializations'] = $specializations;
        $row['courses_list'] = $courses;
        $instructors[] = $row;

        
    }
}

if (empty($instructors)) {
    echo json_encode(["error" => "No Instructor records found"]);
} else {
    echo json_encode($instructors, JSON_PRETTY_PRINT);
}

$conn->close();
