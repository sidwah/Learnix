<?php
// Include database connection
require_once('../backend/config.php');

// Check if this is an AJAX request
header('Content-Type: application/json');

// Get filter parameters
$sort = isset($_POST['sort']) ? $_POST['sort'] : 'newest';
$price_filter = isset($_POST['price']) ? $_POST['price'] : 'all';
$level_filter = isset($_POST['level']) ? $_POST['level'] : 'all';
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$search = isset($_POST['search']) ? $_POST['search'] : '';
$subcategories = isset($_POST['subcategories']) ? json_decode($_POST['subcategories'], true) : [];

// Items per page and offset - changed to 6
$items_per_page = 6;
$offset = ($page - 1) * $items_per_page;

// Rest of the file remains the same...

// Build the query
$query = "SELECT c.*, i.user_id, u.first_name, u.last_name, u.profile_pic, 
          COUNT(DISTINCT cs.section_id) as total_sections,
          AVG(cr.rating) as avg_rating,
          COUNT(DISTINCT cr.rating_id) as total_ratings,
          cat.name as category_name,
          sub.name as subcategory_name
          FROM courses c
          LEFT JOIN instructors i ON c.instructor_id = i.instructor_id
          LEFT JOIN users u ON i.user_id = u.user_id
          LEFT JOIN course_sections cs ON c.course_id = cs.course_id
          LEFT JOIN course_ratings cr ON c.course_id = cr.course_id
          LEFT JOIN subcategories sub ON c.subcategory_id = sub.subcategory_id
          LEFT JOIN categories cat ON sub.category_id = cat.category_id
          WHERE c.status = 'Published'";

// Add filters
if ($price_filter == 'free') {
    $query .= " AND c.price = 0";
} else if ($price_filter == 'paid') {
    $query .= " AND c.price > 0";
}

if ($level_filter != 'all') {
    $query .= " AND c.course_level = '$level_filter'";
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
        $query .= " ORDER BY avg_rating DESC";
        break;
    case 'lowest_price':
        $query .= " ORDER BY c.price ASC";
        break;
    case 'highest_price':
        $query .= " ORDER BY c.price DESC";
        break;
    case 'newest':
    default:
        $query .= " ORDER BY c.updated_at DESC";
        break;
}

// Count total for pagination
$count_query = "SELECT COUNT(*) as total FROM ($query) as counted_courses";
$count_result = $conn->query($count_query);
$total_courses = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_courses / $items_per_page);

// Add pagination LIMIT
$query .= " LIMIT $offset, $items_per_page";

// Execute query
$result = $conn->query($query);

// Start building the HTML output
$html = '';

if ($result && $result->num_rows > 0) {
    while ($course = $result->fetch_assoc()) {
        $html .= '<a class="card card-flush" href="course-overview.php?id=' . $course['course_id'] . '">
                <div class="row align-items-md-center">
                    <div class="col-sm-5 mb-3 mb-sm-0">
                        <!-- Card Pinned -->
                        <div class="card-pinned">';
        
        // Course thumbnail
        $thumbnailPath = "../uploads/thumbnails/" . $course['thumbnail'];
        if (file_exists($thumbnailPath) && !empty($course['thumbnail'])) {
            $html .= '<img class="card-img" src="' . $thumbnailPath . '" alt="' . htmlspecialchars($course['title']) . '" style="height: 200px; object-fit: cover;">';
        } else {
            $html .= '<img class="card-img" src="../assets/svg/components/card-12.svg" alt="Image Description" style="height: 200px; object-fit: cover;">';
        }
        
        // Bestseller badge
        if ($course['avg_rating'] > 4.5) {
            $html .= '<div class="card-pinned-top-start">
                        <small class="badge bg-success">Bestseller</small>
                      </div>';
        }
        
        // Rating stars
        $html .= '<div class="card-pinned-bottom-start">
                    <div class="d-flex align-items-center flex-wrap">
                        <div class="d-flex gap-1">';
        
        $rating = round($course['avg_rating'] ?? 0, 1);
        $fullStars = floor($rating);
        $halfStar = $rating - $fullStars >= 0.5;
        
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $fullStars) {
                $html .= '<i class="bi-star-fill text-warning"></i>';
            } elseif ($i == $fullStars + 1 && $halfStar) {
                $html .= '<i class="bi-star-half text-warning"></i>';
            } else {
                $html .= '<i class="bi-star text-warning"></i>';
            }
        }
        
        $html .= '</div>
                <div class="ms-1">
                    <span class="fw-semi-bold text-white small me-1">' . number_format($rating, 1) . '</span>
                    <span class="text-white-70 small">(' . ($course['total_ratings'] > 0 ? number_format($course['total_ratings']) . '+ reviews' : 'No reviews yet') . ')</span>
                </div>
            </div>
        </div>';
        
        $html .= '</div>
                </div>
                <!-- End Col -->

                <div class="col-sm-7">
                    <div class="row mb-3">
                        <div class="col">
                            <small class="card-subtitle text-body">' . htmlspecialchars($course['category_name'] ?? 'Uncategorized') . ' › ' . htmlspecialchars($course['subcategory_name'] ?? '') . '</small>
                            <h3 class="card-title text-inherit">' . htmlspecialchars($course['title']) . '</h3>
                        </div>
                        <!-- End Col -->

                        <div class="col-auto">
                            <div class="text-end">';
        
        if ($course['price'] > 0) {
            $html .= '<h5 class="card-title text-primary">$' . number_format($course['price'], 2) . '</h5>';
        } else {
            $html .= '<h5 class="card-title text-success">Free</h5>';
        }
        
        $html .= '</div>
                </div>
                <!-- End Col -->
            </div>
            <!-- End Row -->

            <div class="row align-items-center mb-2">
                <div class="col">
                    <div class="avatar-group avatar-group-xs">
                        <span class="avatar avatar-xs avatar-circle">';
        
        // Instructor profile picture
        $profilePicPath = "../uploads/instructor-profile/" . $course['profile_pic'];
        if (file_exists($profilePicPath) && $course['profile_pic'] != 'default.png') {
            $html .= '<img class="avatar-img" src="' . $profilePicPath . '" alt="Instructor">';
        } else {
            $html .= '<img class="avatar-img" src="../assets/img/160x160/img1.jpg" alt="Default Avatar">';
        }$html .= '</span>
        <span class="ms-1 small">' . htmlspecialchars($course['first_name'] . ' ' . $course['last_name']) . '</span>
    </div>
</div>
<!-- End Col -->

<div class="col-auto">
    <ul class="list-inline list-separator text-body small">
        <li class="list-inline-item">
            <i class="bi-book me-1"></i> ' . $course['total_sections'] . ' sections
        </li>
        <li class="list-inline-item">
            <i class="bi-bar-chart-steps me-1"></i> ' . $course['course_level'] . '
        </li>
    </ul>
</div>
<!-- End Col -->
</div>
<!-- End Row -->

<p class="card-text text-body">';

// Display short description with character limit
$short_desc = $course['short_description'] ?? '';
$html .= htmlspecialchars(strlen($short_desc) > 120 ? substr($short_desc, 0, 120) . '...' : $short_desc);

$html .= '</p>
</div>
<!-- End Col -->
</div>
<!-- End Row -->
</a>';
}
} else {
$html = '<div class="text-center p-5">
<i class="bi-search display-4 text-muted mb-3"></i>
<h3>No courses found</h3>
<p>Try adjusting your filters or search for something else.</p>
</div>';
}

// Build pagination
$pagination = '';
if ($total_pages > 1) {
$pagination = '<small class="d-none d-sm-inline-block text-body">Page ' . $page . ' out of ' . $total_pages . '</small>
  <nav aria-label="Page navigation">
      <ul class="pagination justify-content-center">';

// First page button
$pagination .= '<li class="page-item ' . ($page <= 1 ? 'disabled' : '') . '">
        <a class="page-link" href="#" data-page="1" aria-label="First">
            <span aria-hidden="true">
                <i class="bi-chevron-double-left small"></i>
            </span>
        </a>
    </li>';

// Page numbers
$range = 2;
$start_page = max(1, $page - $range);
$end_page = min($total_pages, $page + $range);

for ($i = $start_page; $i <= $end_page; $i++) {
$pagination .= '<li class="page-item ' . (($i == $page) ? 'active' : '') . '">
          <a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a>
        </li>';
}

// Last page button
$pagination .= '<li class="page-item ' . ($page >= $total_pages ? 'disabled' : '') . '">
        <a class="page-link" href="#" data-page="' . $total_pages . '" aria-label="Last">
            <span aria-hidden="true">
                <i class="bi-chevron-double-right small"></i>
            </span>
        </a>
    </li>';

$pagination .= '</ul></nav>';
}

// Return JSON response
echo json_encode([
'success' => true,
'courses' => $html,
'pagination' => $pagination,
'total_courses' => $total_courses,
'total_pages' => $total_pages
]);