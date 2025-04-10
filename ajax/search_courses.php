<?php
require '../backend/config.php'; // Include database connection

// Get search query from request
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Validate and sanitize input
$searchTerm = trim($searchTerm);
$searchTerm = htmlspecialchars($searchTerm);

// Initialize response array
$response = [
    'success' => false,
    'courses' => [],
    'message' => ''
];

if (empty($searchTerm)) {
    $response['message'] = 'No search term provided';
    echo json_encode($response);
    exit;
}

// Prepare search query with wildcards for partial matching
$searchPattern = "%$searchTerm%";

// SQL query to search courses by title, description, and tags
$sql = "SELECT c.course_id, c.title, c.short_description, c.thumbnail, c.price,
               u.first_name, u.last_name, c.course_level,
               AVG(cr.rating) as average_rating,
               COUNT(DISTINCT cr.rating_id) as rating_count
        FROM courses c
        LEFT JOIN instructors i ON c.instructor_id = i.instructor_id
        LEFT JOIN users u ON i.user_id = u.user_id
        LEFT JOIN course_ratings cr ON c.course_id = cr.course_id
        LEFT JOIN course_tag_mapping ctm ON c.course_id = ctm.course_id
        LEFT JOIN tags t ON ctm.tag_id = t.tag_id
        WHERE c.status = 'Published' 
        AND (
            c.title LIKE ? OR 
            c.short_description LIKE ? OR 
            c.full_description LIKE ? OR
            t.tag_name LIKE ?
        )
        GROUP BY c.course_id
        ORDER BY c.course_id DESC
        LIMIT 15";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $searchPattern, $searchPattern, $searchPattern, $searchPattern);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Format price
            if ($row['price'] == 0 || $row['price'] == 0.00) {
                $row['formatted_price'] = 'Free';
            } else {
                $row['formatted_price'] = '₵' . number_format($row['price'], 2);
            }
            
            // Format rating
            $row['average_rating'] = round($row['average_rating'], 1);
            
            $response['courses'][] = $row;
        }
        $response['success'] = true;
    } else {
        $response['message'] = 'No courses found for "' . $searchTerm . '"';
    }
    
} catch (Exception $e) {
    $response['message'] = 'An error occurred while searching';
    // Log error for debugging
    error_log("Search error: " . $e->getMessage());
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);