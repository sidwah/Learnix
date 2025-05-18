<?php
// load_all_categories.php
require_once('../../backend/config.php');

// Check if this is an AJAX request
header('Content-Type: application/json');

// Fetch all categories and count their courses, respecting soft deletion
$query = "SELECT c.category_id, c.name, COUNT(DISTINCT co.course_id) as course_count 
          FROM categories c
          LEFT JOIN subcategories s ON c.category_id = s.category_id AND s.deleted_at IS NULL
          LEFT JOIN courses co ON s.subcategory_id = co.subcategory_id 
                              AND co.status = 'Published' 
                              AND co.approval_status = 'Approved'
                              AND co.deleted_at IS NULL
          WHERE c.deleted_at IS NULL
          GROUP BY c.category_id
          ORDER BY c.name";

$result = $conn->query($query);

$html = '';

if ($result && $result->num_rows > 0) {
    // Create a 2-column layout
    $html = '<div class="row">';
    $count = 0;
    
    while ($category = $result->fetch_assoc()) {
        // Each category gets its own card
        if ($count % 2 == 0 && $count > 0) {
            $html .= '</div><div class="row mt-3">'; // Start a new row every 2 cards
        }
        
        // Get a color for the category card
        $colors = ['primary', 'success', 'info', 'warning', 'danger', 'dark'];
        $color = $colors[$count % count($colors)];
        $html .= '<div class="col-md-6 mb-3">
                    <div class="card category-card">
                        <div class="card-header bg-soft-' . $color . ' d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">' . htmlspecialchars($category['name']) . '</h5>
                            <div class="form-check">
                                <input class="form-check-input category-main-checkbox" type="checkbox" id="cat-' . $category['category_id'] . '" data-category="' . $category['category_id'] . '">
                                <label class="form-check-label" for="cat-' . $category['category_id'] . '">All</label>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-1">';
        
        // For each category, fetch its subcategories, respecting soft deletion
        $subcategory_query = "SELECT s.subcategory_id, s.name, COUNT(DISTINCT c.course_id) as course_count
                             FROM subcategories s
                             LEFT JOIN courses c ON s.subcategory_id = c.subcategory_id 
                                                AND c.status = 'Published' 
                                                AND c.approval_status = 'Approved'
                                                AND c.deleted_at IS NULL
                             WHERE s.category_id = {$category['category_id']}
                             AND s.deleted_at IS NULL
                             GROUP BY s.subcategory_id
                             ORDER BY s.name";
        
        $subcategory_result = $conn->query($subcategory_query);
        
        if ($subcategory_result && $subcategory_result->num_rows > 0) {
            // Create a container with max-height for scrolling if many subcategories
            $html .= '<div style="max-height: 200px; overflow-y: auto; padding-right: 10px;">';
            
            while ($subcategory = $subcategory_result->fetch_assoc()) {
                $html .= '<div class="form-check mb-2">
                            <input class="form-check-input subcategory-checkbox" type="checkbox" 
                                id="all-sub-' . $subcategory['subcategory_id'] . '" 
                                data-category="' . $category['category_id'] . '" 
                                data-subcategory="' . $subcategory['subcategory_id'] . '">
                            <label class="form-check-label" for="all-sub-' . $subcategory['subcategory_id'] . '">
                                ' . htmlspecialchars($subcategory['name']) . '
                                <span class="text-muted small">(' . $subcategory['course_count'] . ')</span>
                            </label>
                          </div>';
            }
            
            $html .= '</div>';
        } else {
            $html .= '<p class="text-muted small">No subcategories found</p>';
        }
        
        $html .= '</div>
                </div>
            </div>
        </div>';
        
        $count++;
    }
    
    $html .= '</div>'; // Close the final row
} else {
    $html = '<div class="col-12">
                <div class="alert alert-info">
                    <i class="bi-info-circle me-2"></i> No categories found
                </div>
             </div>';
}

echo json_encode([
    'success' => true,
    'categories' => $html
]);