<?php
// load_subcategories.php
require_once('../backend/config.php');

// Check if this is an AJAX request
header('Content-Type: application/json');

// Get category ID
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

if ($category_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid category ID'
    ]);
    exit;
}

// Fetch all subcategories for this category with course count
$query = "SELECT s.subcategory_id, s.name, COUNT(c.course_id) as course_count
          FROM subcategories s
          LEFT JOIN courses c ON s.subcategory_id = c.subcategory_id AND c.status = 'Published'
          WHERE s.category_id = $category_id
          GROUP BY s.subcategory_id
          ORDER BY course_count DESC, s.name";

$result = $conn->query($query);

$html = '<div class="mb-3">
            <input type="text" class="form-control" id="modal-subcategory-search" placeholder="Search subcategories">
        </div>';

if ($result && $result->num_rows > 0) {
    // Get category name
    $cat_query = "SELECT name FROM categories WHERE category_id = $category_id";
    $cat_result = $conn->query($cat_query);
    $category_name = $cat_result->fetch_assoc()['name'];
    
    // Add select all checkbox
    $html .= '<div class="d-flex justify-content-between align-items-center mb-3">
                <span class="fw-semibold">' . htmlspecialchars($category_name) . ' Subcategories</span>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="select-all-subcategories">
                    <label class="form-check-label" for="select-all-subcategories">Select All</label>
                </div>
              </div>';
    
    // Count total subcategories
    $total_subcategories = $result->num_rows;
    
    // Organize subcategories into columns if there are many
    if ($total_subcategories > 12) {
        $html .= '<div class="row" style="max-height: 400px; overflow-y: auto;">';
        
        // Create 2 columns if there are many subcategories
        $html .= '<div class="col-md-6">';
        
        $count = 0;
        $halfway = ceil($total_subcategories / 2);
        
        $result->data_seek(0); // Reset result pointer
        
        while ($subcategory = $result->fetch_assoc()) {
            if ($count == $halfway) {
                $html .= '</div><div class="col-md-6">'; // Switch to second column
            }
            
            $html .= '<div class="form-check mb-2">
                        <input class="form-check-input modal-subcategory-checkbox" type="checkbox" 
                            id="modal-sub-' . $subcategory['subcategory_id'] . '" 
                            data-subcategory="' . $subcategory['subcategory_id'] . '">
                        <label class="form-check-label" for="modal-sub-' . $subcategory['subcategory_id'] . '">
                            ' . htmlspecialchars($subcategory['name']) . '
                            <span class="text-muted small">(' . $subcategory['course_count'] . ')</span>
                        </label>
                      </div>';
            
            $count++;
        }
        
        $html .= '</div></div>'; // Close column and row
    } else {
        // For fewer subcategories, use a simple scrollable container
        $html .= '<div style="max-height: 400px; overflow-y: auto;">';
        
        while ($subcategory = $result->fetch_assoc()) {
            $html .= '<div class="form-check mb-2">
                        <input class="form-check-input modal-subcategory-checkbox" type="checkbox" 
                            id="modal-sub-' . $subcategory['subcategory_id'] . '" 
                            data-subcategory="' . $subcategory['subcategory_id'] . '">
                        <label class="form-check-label" for="modal-sub-' . $subcategory['subcategory_id'] . '">
                            ' . htmlspecialchars($subcategory['name']) . '
                            <span class="text-muted small">(' . $subcategory['course_count'] . ')</span>
                        </label>
                      </div>';
        }
        
        $html .= '</div>';
    }
    
    // Add search and select all functionality
    $html .= '<script>
                // Search functionality
                document.getElementById("modal-subcategory-search").addEventListener("input", function() {
                    const searchTerm = this.value.toLowerCase();
                    document.querySelectorAll(".modal-subcategory-checkbox").forEach(checkbox => {
                        const label = checkbox.nextElementSibling.textContent.toLowerCase();
                        const checkboxDiv = checkbox.closest(".form-check");
                        
                        if (label.includes(searchTerm)) {
                            checkboxDiv.style.display = "";
                        } else {
                            checkboxDiv.style.display = "none";
                        }
                    });
                });
                
                // Select all functionality
                document.getElementById("select-all-subcategories").addEventListener("change", function() {
                    document.querySelectorAll(".modal-subcategory-checkbox").forEach(checkbox => {
                        if (checkbox.closest(".form-check").style.display !== "none") {
                            checkbox.checked = this.checked;
                        }
                    });
                });
              </script>';
} else {
    $html = '<div class="alert alert-info">No subcategories found for this category.</div>';
}

echo json_encode([
    'success' => true,
    'subcategories' => $html
]);