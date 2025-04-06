<?php
// Include database connection
require_once '../../backend/config.php';
// Initialize response array
$response = [
    'status' => 'success',
    'courses' => []
];
try {
    // Query to fetch courses with instructor details, category info, and enrollment counts
    $query = "
       SELECT
 c.course_id,
 c.title,
 c.short_description,
 c.thumbnail,
 c.status,
 c.approval_status,
 c.created_at,
 CONCAT(u.first_name, ' ', u.last_name) AS instructor_name,
 u.user_id AS instructor_user_id,
 cat.name AS category_name,
 sub.name AS subcategory_name,
 (SELECT COUNT(*) FROM enrollments WHERE course_id = c.course_id) AS enrollment_count,
 crr.request_id,
 crr.status AS review_status,
 crr.review_notes,
 crr.requested_by,
 crr.created_at AS review_requested_at,
 CASE
 WHEN c.status = 'Published' AND c.approval_status = 'Pending' THEN 1
 WHEN c.status = 'Published' AND c.approval_status = 'Approved' THEN 2
 WHEN c.status = 'Published' AND c.approval_status = 'Rejected' THEN 3
 ELSE 0
 END AS course_state_priority
FROM
 courses c
 JOIN
 instructors i ON c.instructor_id = i.instructor_id
 JOIN
 users u ON i.user_id = u.user_id
 JOIN
 subcategories sub ON c.subcategory_id = sub.subcategory_id
 JOIN
 categories cat ON sub.category_id = cat.category_id
 LEFT JOIN
 course_review_requests crr ON c.course_id = crr.course_id
WHERE
 c.status = 'Published' AND c.approval_status IN ('Pending', 'Approved', 'Rejected')
ORDER BY
 course_state_priority,
 crr.created_at DESC,
 c.created_at DESC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Format the date to "10 Feb 2021" format
        $createdDate = new DateTime($row['created_at']);
        $row['formatted_date'] = $createdDate->format('j M Y');
        
        // Add the row to the response
        $response['courses'][] = $row;
    }
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = 'Error fetching courses: ' . $e->getMessage();
}
// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>