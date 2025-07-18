<?php
// load_courses.php
// Include database connection
require_once('../../backend/config.php');

// Check if this is an AJAX request
header('Content-Type: application/json');

// Get filter parameters
$sort = isset($_POST['sort']) ? $_POST['sort'] : 'newest';
$price_filter = isset($_POST['price']) ? $_POST['price'] : 'all';
$level_filter = isset($_POST['level']) ? $_POST['level'] : 'all';
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$search = isset($_POST['search']) ? $_POST['search'] : '';
$subcategories = isset($_POST['subcategories']) ? json_decode($_POST['subcategories'], true) : [];
$certificate = isset($_POST['certificate']) ? json_decode($_POST['certificate'], true) : [];

// Custom price range filter
$price_min = isset($_POST['price_min']) ? (float)$_POST['price_min'] : 0;
$price_max = isset($_POST['price_max']) ? (float)$_POST['price_max'] : 1000;

// Items per page
$items_per_page = 9; // Always show 9 items in grid view (3x3)
$offset = ($page - 1) * $items_per_page;

// Build the query - fixed for GROUP BY compatibility and removed deleted_at from section_quizzes
$query = "SELECT c.*, 
          (SELECT i.instructor_id FROM course_instructors ci 
           JOIN instructors i ON ci.instructor_id = i.instructor_id 
           WHERE ci.course_id = c.course_id AND ci.is_primary = 1 AND ci.deleted_at IS NULL 
           LIMIT 1) as primary_instructor_id,
          (SELECT COUNT(*) FROM course_sections WHERE course_id = c.course_id AND deleted_at IS NULL) as section_count,
          (SELECT COUNT(*) FROM section_quizzes sq JOIN course_sections cs ON sq.section_id = cs.section_id WHERE cs.course_id = c.course_id AND cs.deleted_at IS NULL) as quiz_count,
          AVG(cr.rating) as avg_rating,
          COUNT(DISTINCT cr.rating_id) as rating_count,
          cat.name as category_name,
          sub.name as subcategory_name
          FROM courses c
          LEFT JOIN course_ratings cr ON c.course_id = cr.course_id
          LEFT JOIN subcategories sub ON c.subcategory_id = sub.subcategory_id AND sub.deleted_at IS NULL
          LEFT JOIN categories cat ON sub.category_id = cat.category_id AND cat.deleted_at IS NULL
          WHERE c.status = 'Published' AND c.approval_status = 'Approved'
          AND c.deleted_at IS NULL";

// Add filters
if ($price_filter == 'free') {
    $query .= " AND c.price = 0";
} else if ($price_filter == 'paid') {
    $query .= " AND c.price > 0";
}

// Add price range filter
if ($price_min > 0 || $price_max < 1000) {
    $query .= " AND c.price BETWEEN $price_min AND $price_max";
}

if ($level_filter != 'all') {
    $query .= " AND c.course_level = '$level_filter'";
}

// Add certificate filter
if (!empty($certificate)) {
    $certificate_values = implode(',', array_map('intval', $certificate));
    $query .= " AND c.certificate_enabled IN ($certificate_values)";
}

// Add search filter
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $query .= " AND (c.title LIKE '%$search%' OR c.short_description LIKE '%$search%' OR c.full_description LIKE '%$search%'
                OR cat.name LIKE '%$search%' OR sub.name LIKE '%$search%')";
}

// Add subcategory filter
if (!empty($subcategories)) {
    $subcategory_ids = implode(',', array_map('intval', $subcategories));
    $query .= " AND c.subcategory_id IN ($subcategory_ids)";
}

// Group by to avoid duplicates
$query .= " GROUP BY c.course_id";

// Add sorting
switch ($sort) {
    case 'highest_rated':
        $query .= " ORDER BY avg_rating DESC, c.created_at DESC";
        break;
    case 'lowest_price':
        $query .= " ORDER BY c.price ASC, c.created_at DESC";
        break;
    case 'highest_price':
        $query .= " ORDER BY c.price DESC, c.created_at DESC";
        break;
    case 'newest':
    default:
        $query .= " ORDER BY c.created_at DESC";
        break;
}

// Count total for pagination
$count_query = "SELECT COUNT(*) as total FROM ($query) as counted_courses";
try {
    $count_result = $conn->query($count_query);
    $total_courses = $count_result->fetch_assoc()['total'];
} catch (Exception $e) {
    // Error handling
    echo json_encode([
        'success' => false,
        'message' => 'Error counting courses: ' . $e->getMessage()
    ]);
    exit;
}
$total_pages = ceil($total_courses / $items_per_page);

// Add pagination LIMIT
$query .= " LIMIT $offset, $items_per_page";

// Execute query
try {
    $result = $conn->query($query);
} catch (Exception $e) {
    // Error handling
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving courses: ' . $e->getMessage()
    ]);
    exit;
}

// Start building the HTML output
$html = '';

if ($result && $result->num_rows > 0) {
    while ($course = $result->fetch_assoc()) {
        // Determine badge based on how recent the course is
        $badgeHtml = '';
        $created_date = new DateTime($course['created_at']);
        $now = new DateTime();
        $days_diff = $now->diff($created_date)->days;
        
        if ($days_diff <= 7) {
            $badgeHtml = '<span class="badge bg-success">New</span>';
        } else if ($days_diff <= 30) {
            $badgeHtml = '<span class="badge bg-primary">New</span>';
        }
        
        // Format price
        $price = (float)$course['price'];
        if ($price > 0) {
            $priceHtml = '<h3 class="card-title text-primary">₵' . number_format($price, 2) . '</h3>';
        } else {
            $priceHtml = '<h3 class="card-title text-success">Free</h3>';
        }
        
        // Get all instructors for this course - new code to display multiple instructors
        $instructors_query = "SELECT u.first_name, u.last_name, u.profile_pic 
                             FROM course_instructors ci 
                             JOIN instructors i ON ci.instructor_id = i.instructor_id 
                             JOIN users u ON i.user_id = u.user_id 
                             WHERE ci.course_id = " . $course['course_id'] . " 
                             AND ci.deleted_at IS NULL
                             ORDER BY ci.is_primary DESC
                             LIMIT 3"; // Limit to 3 instructors to prevent overcrowding
        
        $instructors_result = $conn->query($instructors_query);
        $instructors_html = '<div class="avatar-group avatar-group-xs">';
        
        if ($instructors_result && $instructors_result->num_rows > 0) {
            while ($instructor = $instructors_result->fetch_assoc()) {
                $profile_pic = !empty($instructor['profile_pic']) ? $instructor['profile_pic'] : 'default.png';
                $instructors_html .= '<span class="avatar avatar-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="' . 
                                   htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']) . '">' .
                                   '<img class="avatar-img" src="../uploads/instructor-profile/' . $profile_pic . '" alt="Instructor">' .
                                   '</span>';
            }
        } else {
            // Fallback if no instructors found
            $instructors_html .= '<span class="avatar avatar-circle">' .
                              '<span class="avatar-initials">?</span>' .
                              '</span>';
        }
        
        $instructors_html .= '</div>';
        
        // Format the HTML in the same style as the real estate listings
        $html .= '
        <div class="col mb-3">
            <!-- Card -->
            <div class="card card-flush shadow-none h-100" style="height: 350px;">
                <a class="card-pinned" href="course-overview.php?id=' . $course['course_id'] . '">
                    <img class="card-img" src="../uploads/thumbnails/' . htmlspecialchars($course['thumbnail']) . '" alt="' . htmlspecialchars($course['title']) . '" style="height: 200px; object-fit: cover;">
        ';
        
        // Add badge if needed
        if (!empty($badgeHtml)) {
            $html .= '
                    <div class="card-pinned-top-start">
                        ' . $badgeHtml . '
                    </div>
            ';
        }
        
        $html .= '
                    
                </a>

                <!-- Body -->
<a class="card-body d-flex flex-column position-relative" href="course-overview.php?id=' . $course['course_id'] . '" style="height: 200px; overflow: hidden;">
    <!-- Category and price at the top -->
    <div class="d-flex justify-content-between align-items-center mb-1">
        <span class="card-subtitle text-body small">' . htmlspecialchars($course['category_name'] ?? 'Category') . '</span>
        <div>' . $priceHtml . '</div>
    </div>
    
    <!-- Course title -->
    <h4 class="card-title text-inherit mb-2" style="font-size: 1rem; height: 40px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
        ' . htmlspecialchars($course['title']) . '
    </h4>
    
    <!-- Instructors - positioned after title -->
    <div class="mt-auto mb-4">
        ' . $instructors_html . '
    </div>
    
    <!-- Bottom stats - absolute positioned at bottom -->
    <ul class="list-inline list-separator text-body small m-0 p-0 position-absolute" style="font-size: 0.75rem; bottom: 12px; left: 20px; right: 20px;">
        <li class="list-inline-item">
            <i class="bi-collection text-muted me-1"></i>
            ' . intval($course['section_count']) . ' sections
        </li>
        <li class="list-inline-item">
            <i class="bi-question-circle text-muted me-1"></i>
            ' . intval($course['quiz_count']) . ' quizzes
        </li>
        <li class="list-inline-item">
            <i class="bi-' . ($course['certificate_enabled'] ? 'patch-check-fill text-primary' : 'patch-check text-muted') . ' me-1"></i> 
            ' . ($course['certificate_enabled'] ? 'Certificate' : 'No Certificate') . '
        </li>
    </ul>
</a>
<!-- End Body -->
            </div>
            <!-- End Card -->
        </div>
        ';
    }
} else {
    $html = '
    <div class="col-12 text-center">
        <div class="p-5">
            <i class="bi-search display-4 text-muted mb-3"></i>
            <h3>No courses found</h3>
            <p>Try adjusting your search or filter criteria</p>
        </div>
    </div>
    ';
}

// Build pagination
$pagination = '';
if ($total_pages > 1) {
    $pagination = '
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
    ';
    
    // Previous button
    $pagination .= '
            <li class="page-item ' . ($page <= 1 ? 'disabled' : '') . '">
                <a class="page-link" href="#" aria-label="Previous" ' . ($page > 1 ? 'data-page="' . ($page - 1) . '"' : '') . '>
                    <span aria-hidden="true">
                        <i class="bi-chevron-double-left small"></i>
                    </span>
                </a>
            </li>
    ';
    
    // Page numbers
    $start_page = max(1, $page - 2);
    $end_page = min($total_pages, $page + 2);
    
    for ($i = $start_page; $i <= $end_page; $i++) {
        $pagination .= '
            <li class="page-item ' . ($i == $page ? 'active' : '') . '">
                <a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a>
            </li>
        ';
    }
    
    // Next button
    $pagination .= '
            <li class="page-item ' . ($page >= $total_pages ? 'disabled' : '') . '">
                <a class="page-link" href="#" aria-label="Next" ' . ($page < $total_pages ? 'data-page="' . ($page + 1) . '"' : '') . '>
                    <span aria-hidden="true">
                        <i class="bi-chevron-double-right small"></i>
                    </span>
                </a>
            </li>
    ';
    
    $pagination .= '
        </ul>
    </nav>
    ';
}

// Return the response
echo json_encode([
    'success' => true,
    'courses' => $html,
    'pagination' => $pagination,
    'total_courses' => $total_courses,
    'total_pages' => $total_pages
]);