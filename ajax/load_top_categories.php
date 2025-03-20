<?php
// load_top_categories.php
require_once('../backend/config.php');

// Check if this is an AJAX request
header('Content-Type: application/json');

// Get top 6 categories with the most courses
$query = "SELECT c.category_id, c.name, c.slug, COUNT(co.course_id) as course_count 
          FROM categories c
          LEFT JOIN subcategories s ON c.category_id = s.category_id
          LEFT JOIN courses co ON s.subcategory_id = co.subcategory_id AND co.status = 'Published'
          GROUP BY c.category_id
          ORDER BY course_count DESC, c.name
          LIMIT 6";  // Changed from 5 to 6

$result = $conn->query($query);

$html = '';

if ($result && $result->num_rows > 0) {
    while ($category = $result->fetch_assoc()) {
        // For each top category, fetch its top 4 subcategories (changed from 5 to 4)
        $subcategory_query = "SELECT s.subcategory_id, s.name, COUNT(c.course_id) as course_count
                             FROM subcategories s
                             LEFT JOIN courses c ON s.subcategory_id = c.subcategory_id AND c.status = 'Published'
                             WHERE s.category_id = {$category['category_id']}
                             GROUP BY s.subcategory_id
                             ORDER BY course_count DESC, s.name
                             LIMIT 4";  // Changed from 5 to 4
        
        $subcategory_result = $conn->query($subcategory_query);
        
        $html .= '<div class="d-grid">';
        $html .= '<h5 class="dropdown-header">' . htmlspecialchars($category['name']) . '</h5>';
        
        if ($subcategory_result && $subcategory_result->num_rows > 0) {
            while ($subcategory = $subcategory_result->fetch_assoc()) {
                $html .= '<div class="form-check mb-2">';
                $html .= '<input class="form-check-input category-checkbox" type="checkbox" id="sub-' . $subcategory['subcategory_id'] . '" 
                         data-category="' . $category['category_id'] . '" data-subcategory="' . $subcategory['subcategory_id'] . '">';
                $html .= '<label class="form-check-label" for="sub-' . $subcategory['subcategory_id'] . '">' . 
                         htmlspecialchars($subcategory['name']) . 
                         ' <span class="text-muted small">(' . $subcategory['course_count'] . ')</span></label>';
                $html .= '</div>';
            }
        } else {
            $html .= '<p class="text-muted small">No subcategories found</p>';
        }
        
        // Get total subcategory count for this category
        $count_query = "SELECT COUNT(*) as total FROM subcategories WHERE category_id = {$category['category_id']}";
        $count_result = $conn->query($count_query);
        $total_subcategories = $count_result->fetch_assoc()['total'];
        
        // Add "See more" button if there are more subcategories
        if ($total_subcategories > 4) {  // Changed from 5 to 4
            $html .= '<button type="button" class="btn btn-link btn-xs text-primary ps-0 see-more-btn" 
                     data-category-id="' . $category['category_id'] . '" data-category-name="' . htmlspecialchars($category['name']) . '">
                     See all ' . $total_subcategories . ' subcategories</button>';
        }
        
        $html .= '</div>';
    }
    
    // Count total categories
    $count_query = "SELECT COUNT(*) as total FROM categories";
    $count_result = $conn->query($count_query);
    $total_categories = $count_result->fetch_assoc()['total'];
    
    // Only add "Other Categories" section if there are more than 6 categories
    if ($total_categories > 6) {
        $html .= '<div class="d-grid">';
        $html .= '<h5 class="dropdown-header">Other Categories</h5>';
        
        // Get next 3 categories
        $other_query = "SELECT c.category_id, c.name, COUNT(co.course_id) as course_count 
                       FROM categories c
                       LEFT JOIN subcategories s ON c.category_id = s.category_id
                       LEFT JOIN courses co ON s.subcategory_id = co.subcategory_id AND co.status = 'Published'
                       GROUP BY c.category_id
                       ORDER BY course_count DESC, c.name
                       LIMIT 6, 3";  // Changed to start after the 6th category
        
        $other_result = $conn->query($other_query);
        
        if ($other_result && $other_result->num_rows > 0) {
            while ($other_category = $other_result->fetch_assoc()) {
                $html .= '<div class="mb-2">';
                $html .= '<a href="#" class="text-body category-link" data-category-id="' . $other_category['category_id'] . '" 
                         data-category-name="' . htmlspecialchars($other_category['name']) . '">' . 
                         htmlspecialchars($other_category['name']) . 
                         ' <span class="text-muted small">(' . $other_category['course_count'] . ')</span></a>';
                $html .= '</div>';
            }
            
            // If there are more categories, add a "View All" button
            if ($total_categories > 9) {  // 6 + 3
                $remaining = $total_categories - 9;
                $html .= '<div class="mb-2">';
                $html .= '<a href="#" class="btn btn-link btn-xs text-primary ps-0" data-bs-toggle="modal" data-bs-target="#allCategoriesModal">
                          View ' . $remaining . ' more categories</a>';
                $html .= '</div>';
            }
        }
        
        $html .= '</div>';
    }
} else {
    $html = '<div class="alert alert-info">No categories found</div>';
}

echo json_encode([
    'success' => true,
    'html' => $html
]);