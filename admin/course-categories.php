<?php
// Authentication check
require_once '../backend/auth/admin/admin-auth-check.php';

// Set page title
$pageTitle = "Course Categories - Admin | Learnix";

include_once '../includes/admin/header.php';

// <!-- Menu -->
include_once '../includes/admin/sidebar.php';
// <!-- / Menu -->

// <!-- Navbar -->
include_once '../includes/admin/navbar.php';
// <!-- / Navbar -->

// Get data from database
require_once '../backend/config.php';

// Fetch all categories
$categoryQuery = "SELECT c.category_id, c.name, c.slug, c.created_at, 
                 (SELECT COUNT(*) FROM subcategories s WHERE s.category_id = c.category_id AND s.deleted_at IS NULL) as subcategory_count,
                 (SELECT COUNT(*) FROM courses co 
                  JOIN subcategories s ON co.subcategory_id = s.subcategory_id 
                  WHERE s.category_id = c.category_id AND co.deleted_at IS NULL) as course_count,
                 GROUP_CONCAT(DISTINCT d.name SEPARATOR '|') as department_names,
                 GROUP_CONCAT(DISTINCT d.department_id SEPARATOR ',') as department_ids
                 FROM categories c
                 LEFT JOIN department_category_mapping dcm ON c.category_id = dcm.category_id AND dcm.deleted_at IS NULL
                 LEFT JOIN departments d ON dcm.department_id = d.department_id AND d.deleted_at IS NULL
                 WHERE c.deleted_at IS NULL
                 GROUP BY c.category_id
                 ORDER BY c.name ASC";

$categoryResult = mysqli_query($conn, $categoryQuery);
$categories = [];
$totalCategories = 0;

if ($categoryResult && mysqli_num_rows($categoryResult) > 0) {
    while ($row = mysqli_fetch_assoc($categoryResult)) {
        $categories[] = $row;
        $totalCategories++;
    }
}

// Fetch all subcategories
$subcategoryQuery = "SELECT s.subcategory_id, s.name, s.slug, s.category_id, s.created_at, 
                    c.name as category_name,
                    (SELECT COUNT(*) FROM courses co WHERE co.subcategory_id = s.subcategory_id AND co.deleted_at IS NULL) as course_count
                    FROM subcategories s
                    JOIN categories c ON s.category_id = c.category_id
                    WHERE s.deleted_at IS NULL
                    ORDER BY c.name ASC, s.name ASC";

$subcategoryResult = mysqli_query($conn, $subcategoryQuery);
$subcategories = [];
$totalSubcategories = 0;

if ($subcategoryResult && mysqli_num_rows($subcategoryResult) > 0) {
    while ($row = mysqli_fetch_assoc($subcategoryResult)) {
        $subcategories[] = $row;
        $totalSubcategories++;
    }
}

// Fetch all departments for dropdown
$departmentQuery = "SELECT department_id, name FROM departments WHERE deleted_at IS NULL ORDER BY name ASC";
$departmentResult = mysqli_query($conn, $departmentQuery);
$departments = [];

if ($departmentResult && mysqli_num_rows($departmentResult) > 0) {
    while ($row = mysqli_fetch_assoc($departmentResult)) {
        $departments[] = $row;
    }
}
?>

<!-- Toast Notification -->
<div class="bs-toast toast toast-placement-ex m-2 fade bg-danger top-0 end-0" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000" id="errorToast" style="z-index: 9999; position: fixed;">
  <div class="toast-header">
    <i class="bx bx-bell me-2"></i>
    <div class="me-auto fw-semibold">Error</div>
    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
  </div>
  <div class="toast-body" id="errorToastMessage"></div>
</div>

<div class="bs-toast toast toast-placement-ex m-2 fade bg-success top-0 end-0" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000" id="successToast" style="z-index: 9999; position: fixed;">
  <div class="toast-header">
    <i class="bx bx-check me-2"></i>
    <div class="me-auto fw-semibold">Success</div>
    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
  </div>
  <div class="toast-body" id="successToastMessage"></div>
</div>
<!-- /Toast Notification -->

<!-- Loading Overlay -->
<div class="custom-overlay" id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9997; align-items: center; justify-content: center; flex-direction: column;">
  <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
    <span class="visually-hidden">Loading...</span>
  </div>
  <div class="text-white mt-3" id="loading-message">Processing...</div>
</div>

<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Admin /</span> Course Categories
  </h4>

  <!-- Tabs Nav -->
  <ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item">
      <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#categories-tab" aria-controls="categories-tab" aria-selected="true">
        <i class="bx bx-category-alt me-1"></i> Categories (<?php echo $totalCategories; ?>)
      </button>
    </li>
    <li class="nav-item">
      <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#subcategories-tab" aria-controls="subcategories-tab" aria-selected="false">
        <i class="bx bx-collection me-1"></i> Subcategories (<?php echo $totalSubcategories; ?>)
      </button>
    </li>
  </ul>

  <!-- Tab content -->
  <div class="tab-content">
    
    <!-- Categories Tab -->
    <div class="tab-pane fade show active" id="categories-tab" role="tabpanel">
      <!-- Categories Card -->
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Categories</h5>
          <div class="d-flex align-items-center">
            <div class="me-3">
              <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bx bx-search"></i></span>
                <input type="text" class="form-control" id="categorySearch" placeholder="Search categories..." aria-label="Search">
              </div>
            </div>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
              <i class="bx bx-plus me-1"></i> Add Category
            </button>
          </div>
        </div>

        <?php if (count($categories) > 0): ?>
          <div class="table-responsive text-nowrap">
            <table class="table" id="categoriesTable">
              <thead>
                <tr>
                  <th>Category</th>
                  <th>Departments</th>
                  <th>Subcategories</th>
                  <th>Courses</th>
                  <th>Created</th>
                  <th class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody class="table-border-bottom-0">
                <?php foreach ($categories as $category): 
                  $departmentNames = $category['department_names'] ? explode('|', $category['department_names']) : [];
                  $departmentIds = $category['department_ids'] ? explode(',', $category['department_ids']) : [];
                  ?>
                  <tr class="category-row"
                    data-id="<?php echo $category['category_id']; ?>"
                    data-name="<?php echo htmlspecialchars($category['name']); ?>"
                    data-slug="<?php echo htmlspecialchars($category['slug']); ?>"
                    data-department-ids="<?php echo htmlspecialchars(implode(',', $departmentIds)); ?>"
                    data-department-names="<?php echo htmlspecialchars(implode(', ', $departmentNames)); ?>"
                    data-subcategory-count="<?php echo $category['subcategory_count']; ?>"
                    data-course-count="<?php echo $category['course_count']; ?>"
                    data-created="<?php echo date('F d, Y', strtotime($category['created_at'])); ?>">
                    <td>
                      <div class="d-flex justify-content-start align-items-center">
                        <div class="d-flex flex-column">
                          <span class="fw-semibold"><?php echo htmlspecialchars($category['name']); ?></span>
                          <small class="text-muted"><?php echo htmlspecialchars($category['slug']); ?></small>
                        </div>
                      </div>
                    </td>
                    <td>
                      <?php if (!empty($departmentNames)): ?>
                        <?php 
                          $displayDepts = array_slice($departmentNames, 0, 2);
                          echo htmlspecialchars(implode(', ', $displayDepts));
                          if (count($departmentNames) > 2) {
                            echo ' <span class="badge bg-light text-dark">+' . (count($departmentNames) - 2) . ' more</span>';
                          }
                        ?>
                      <?php else: ?>
                        <span class="text-muted">None assigned</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <span class="badge bg-label-primary"><?php echo $category['subcategory_count']; ?></span>
                    </td>
                    <td>
                      <span class="badge bg-label-info"><?php echo $category['course_count']; ?></span>
                    </td>
                    <td>
                      <span class="text-muted"><?php echo date('M d, Y', strtotime($category['created_at'])); ?></span>
                    </td>
                    <td class="text-center">
                      <div class="d-inline-block">
                        <button type="button" class="btn btn-sm btn-icon btn-outline-primary rounded-pill btn-icon edit-category" title="Edit Category">
                          <i class="bx bx-edit-alt"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-pill btn-icon delete-category" title="Delete Category">
                          <i class="bx bx-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>

            <!-- Empty search results state -->
            <div id="empty-category-results" class="text-center py-5 d-none">
              <div class="empty-state">
                <div class="empty-state-icon mb-4">
                  <i class="bx bx-search" style="font-size: 4rem; color: #dfe3e7;"></i>
                </div>
                <h5 class="mb-2">No Categories Found</h5>
                <p class="mb-0 text-muted">No categories match your search.</p>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div class="card-footer">
            <div class="row">
              <div class="col-sm-12 col-md-5">
                <div class="dataTables_info" id="category-pagination-info" role="status" aria-live="polite">
                  Showing <span id="category-showing-start">1</span> to <span id="category-showing-end"><?php echo min(10, count($categories)); ?></span> of <span id="category-total-entries"><?php echo $totalCategories; ?></span> entries
                </div>
              </div>
              <div class="col-sm-12 col-md-7">
                <div class="dataTables_paginate paging_simple_numbers" id="category-pagination-container">
                  <ul class="pagination pagination-sm justify-content-end mb-0">
                    <li class="paginate_button page-item previous disabled" id="category-pagination-previous">
                      <a href="#" class="page-link">Previous</a>
                    </li>
                    <li class="paginate_button page-item active">
                      <a href="#" class="page-link">1</a>
                    </li>
                    <?php if ($totalCategories > 10): ?>
                      <li class="paginate_button page-item">
                        <a href="#" class="page-link">2</a>
                      </li>
                    <?php endif; ?>
                    <?php if ($totalCategories > 20): ?>
                      <li class="paginate_button page-item">
                        <a href="#" class="page-link">3</a>
                      </li>
                    <?php endif; ?>
                    <li class="paginate_button page-item next<?php echo ($totalCategories <= 10) ? ' disabled' : ''; ?>" id="category-pagination-next">
                      <a href="#" class="page-link">Next</a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

        <?php else: ?>
          <!-- Empty state -->
          <div class="card-body text-center py-5">
            <div class="empty-state">
              <div class="empty-state-icon mb-4">
                <i class="bx bx-category-alt" style="font-size: 6rem; color: #dfe3e7;"></i>
              </div>
              <h4 class="mb-2">No Categories Found</h4>
              <p class="mb-4 text-muted">There are no categories available on the platform yet.</p>
              <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="bx bx-plus me-1"></i> Add Category
              </button>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
    
    <!-- Subcategories Tab -->
    <div class="tab-pane fade" id="subcategories-tab" role="tabpanel">
      <!-- Subcategories Card -->
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Subcategories</h5>
          <div class="d-flex align-items-center">
            <div class="me-3">
              <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bx bx-search"></i></span>
                <input type="text" class="form-control" id="subcategorySearch" placeholder="Search subcategories..." aria-label="Search">
              </div>
            </div>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSubcategoryModal">
              <i class="bx bx-plus me-1"></i> Add Subcategory
            </button>
          </div>
        </div>

        <?php if (count($subcategories) > 0): ?>
          <div class="table-responsive text-nowrap">
            <table class="table" id="subcategoriesTable">
              <thead>
                <tr>
                  <th>Subcategory</th>
                  <th>Parent Category</th>
                  <th>Courses</th>
                  <th>Created</th>
                  <th class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody class="table-border-bottom-0">
                <?php foreach ($subcategories as $subcategory): ?>
                  <tr class="subcategory-row"
                    data-id="<?php echo $subcategory['subcategory_id']; ?>"
                    data-name="<?php echo htmlspecialchars($subcategory['name']); ?>"
                    data-slug="<?php echo htmlspecialchars($subcategory['slug']); ?>"
                    data-category-id="<?php echo $subcategory['category_id']; ?>"
                    data-category-name="<?php echo htmlspecialchars($subcategory['category_name']); ?>"
                    data-course-count="<?php echo $subcategory['course_count']; ?>"
                    data-created="<?php echo date('F d, Y', strtotime($subcategory['created_at'])); ?>">
                    <td>
                      <div class="d-flex justify-content-start align-items-center">
                        <div class="d-flex flex-column">
                          <span class="fw-semibold"><?php echo htmlspecialchars($subcategory['name']); ?></span>
                          <small class="text-muted"><?php echo htmlspecialchars($subcategory['slug']); ?></small>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="fw-medium"><?php echo htmlspecialchars($subcategory['category_name']); ?></span>
                    </td>
                    <td>
                      <span class="badge bg-label-info"><?php echo $subcategory['course_count']; ?></span>
                    </td>
                    <td>
                      <span class="text-muted"><?php echo date('M d, Y', strtotime($subcategory['created_at'])); ?></span>
                    </td>
                    <td class="text-center">
                      <div class="d-inline-block">
                        <button type="button" class="btn btn-sm btn-icon btn-outline-primary rounded-pill btn-icon edit-subcategory" title="Edit Subcategory">
                          <i class="bx bx-edit-alt"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-pill btn-icon delete-subcategory" title="Delete Subcategory">
                          <i class="bx bx-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>

            <!-- Empty search results state -->
            <div id="empty-subcategory-results" class="text-center py-5 d-none">
              <div class="empty-state">
                <div class="empty-state-icon mb-4">
                  <i class="bx bx-search" style="font-size: 4rem; color: #dfe3e7;"></i>
                </div>
                <h5 class="mb-2">No Subcategories Found</h5>
                <p class="mb-0 text-muted">No subcategories match your search.</p>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div class="card-footer">
            <div class="row">
              <div class="col-sm-12 col-md-5">
                <div class="dataTables_info" id="subcategory-pagination-info" role="status" aria-live="polite">
                  Showing <span id="subcategory-showing-start">1</span> to <span id="subcategory-showing-end"><?php echo min(10, count($subcategories)); ?></span> of <span id="subcategory-total-entries"><?php echo $totalSubcategories; ?></span> entries
                </div>
              </div>
              <div class="col-sm-12 col-md-7">
                <div class="dataTables_paginate paging_simple_numbers" id="subcategory-pagination-container">
                  <ul class="pagination pagination-sm justify-content-end mb-0">
                    <li class="paginate_button page-item previous disabled" id="subcategory-pagination-previous">
                      <a href="#" class="page-link">Previous</a>
                    </li>
                    <li class="paginate_button page-item active">
                      <a href="#" class="page-link">1</a>
                    </li>
                    <?php if ($totalSubcategories > 10): ?>
                      <li class="paginate_button page-item">
                        <a href="#" class="page-link">2</a>
                      </li>
                    <?php endif; ?>
                    <?php if ($totalSubcategories > 20): ?>
                      <li class="paginate_button page-item">
                        <a href="#" class="page-link">3</a>
                      </li>
                    <?php endif; ?>
                    <li class="paginate_button page-item next<?php echo ($totalSubcategories <= 10) ? ' disabled' : ''; ?>" id="subcategory-pagination-next">
                      <a href="#" class="page-link">Next</a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

        <?php else: ?>
          <!-- Empty state -->
          <div class="card-body text-center py-5">
            <div class="empty-state">
              <div class="empty-state-icon mb-4">
                <i class="bx bx-collection" style="font-size: 6rem; color: #dfe3e7;"></i>
              </div>
              <h4 class="mb-2">No Subcategories Found</h4>
              <p class="mb-4 text-muted">There are no subcategories available on the platform yet.</p>
              <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubcategoryModal">
                <i class="bx bx-plus me-1"></i> Add Subcategory
              </button>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Add Category Modal -->
  <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="addCategoryForm" action="../backend/admin/add-category.php" method="POST">
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="categoryName" class="form-label">Category Name</label>
                <input type="text" class="form-control" id="categoryName" name="name" placeholder="Enter category name" required>
                <div class="form-text">The name will be displayed in menus and course creation forms.</div>
              </div>
              <div class="col-md-6 mb-3">
                <label for="categorySlug" class="form-label">Category Slug</label>
                <input type="text" class="form-control" id="categorySlug" name="slug" placeholder="Enter category slug" required>
                <div class="form-text">The slug is used in URLs and should contain only lowercase letters, numbers, and hyphens.</div>
              </div>
            </div>
            
            <div class="mb-3">
              <label for="departmentMultiselect" class="form-label">Associated Departments</label>
              <select id="departmentMultiselect" name="departments[]" class="form-select" multiple>
                <?php foreach($departments as $department): ?>
                  <option value="<?php echo $department['department_id']; ?>"><?php echo htmlspecialchars($department['name']); ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">Select which departments can use this category. Hold Ctrl/Cmd to select multiple.</div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Create Category</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Category Modal -->
  <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="editCategoryForm" action="../backend/admin/update-category.php" method="POST">
          <div class="modal-body">
            <input type="hidden" id="editCategoryId" name="category_id" value="">
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="editCategoryName" class="form-label">Category Name</label>
                <input type="text" class="form-control" id="editCategoryName" name="name" placeholder="Enter category name" required>
                <div class="form-text">The name will be displayed in menus and course creation forms.</div>
              </div>
              <div class="col-md-6 mb-3">
                <label for="editCategorySlug" class="form-label">Category Slug</label>
                <input type="text" class="form-control" id="editCategorySlug" name="slug" placeholder="Enter category slug" required>
                <div class="form-text">The slug is used in URLs and should contain only lowercase letters, numbers, and hyphens.</div>
              </div>
            </div>
            
            <div class="mb-3">
              <label for="editDepartmentMultiselect" class="form-label">Associated Departments</label>
              <select id="editDepartmentMultiselect" name="departments[]" class="form-select" multiple>
                <?php foreach($departments as $department): ?>
                  <option value="<?php echo $department['department_id']; ?>"><?php echo htmlspecialchars($department['name']); ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">Select which departments can use this category. Hold Ctrl/Cmd to select multiple.</div>
            </div>
            
            <div class="alert alert-info">
              <div class="d-flex align-items-start">
                <i class="bx bx-info-circle mt-1 me-2"></i>
                <div>
                  <p class="mb-0">This category has <strong id="editSubcategoryCount">0</strong> subcategories and is used by <strong id="editCourseCount">0</strong> courses.</p>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Update Category</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Delete Category Modal -->
  <div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Delete Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="deleteCategoryForm" action="../backend/admin/delete-category.php" method="POST">
          <div class="modal-body">
            <input type="hidden" id="deleteCategoryId" name="category_id" value="">

            <div class="text-center mb-4">
              <i class="bx bx-error-circle text-danger" style="font-size: 6rem;"></i>
            </div>

            <p class="mb-0 text-center">Are you sure you want to delete <strong id="delete-category-name"></strong>?</p>

            <div id="delete-category-warning" class="alert alert-warning mt-3">
              <div class="d-flex">
                <i class="bx bx-error me-2 mt-1"></i>
                <div>
                  <p class="mb-0">This category contains <strong id="delete-subcategory-count">0</strong> subcategories and <strong id="delete-course-count">0</strong> courses.</p>
                  <p class="mb-0 mt-2">Deleting this category will make those subcategories and courses inaccessible until they are reassigned to another category.</p>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">Delete Category</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Add Subcategory Modal -->
  <div class="modal fade" id="addSubcategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Subcategory</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="addSubcategoryForm" action="../backend/admin/add-subcategory.php" method="POST">
          <div class="modal-body">
           <div class="row">
             <div class="col-md-6 mb-3">
               <label for="subcategoryName" class="form-label">Subcategory Name</label>
               <input type="text" class="form-control" id="subcategoryName" name="name" placeholder="Enter subcategory name" required>
               <div class="form-text">The name will be displayed in menus and course creation forms.</div>
             </div>
             <div class="col-md-6 mb-3">
               <label for="subcategorySlug" class="form-label">Subcategory Slug</label>
               <input type="text" class="form-control" id="subcategorySlug" name="slug" placeholder="Enter subcategory slug" required>
               <div class="form-text">The slug is used in URLs and should contain only lowercase letters, numbers, and hyphens.</div>
             </div>
           </div>
           
           <div class="mb-3">
             <label for="parentCategorySelect" class="form-label">Parent Category</label>
             <select id="parentCategorySelect" name="category_id" class="form-select" required>
               <option value="" disabled selected>Select a parent category</option>
               <?php foreach($categories as $category): ?>
                 <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
               <?php endforeach; ?>
             </select>
             <div class="form-text">Select the parent category for this subcategory.</div>
           </div>
         </div>
         <div class="modal-footer">
           <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
           <button type="submit" class="btn btn-primary">Create Subcategory</button>
         </div>
       </form>
     </div>
   </div>
 </div>

 <!-- Edit Subcategory Modal -->
 <div class="modal fade" id="editSubcategoryModal" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog modal-lg" role="document">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title">Edit Subcategory</h5>
         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
       </div>
       <form id="editSubcategoryForm" action="../backend/admin/update-subcategory.php" method="POST">
         <div class="modal-body">
           <input type="hidden" id="editSubcategoryId" name="subcategory_id" value="">
           
           <div class="row">
             <div class="col-md-6 mb-3">
               <label for="editSubcategoryName" class="form-label">Subcategory Name</label>
               <input type="text" class="form-control" id="editSubcategoryName" name="name" placeholder="Enter subcategory name" required>
               <div class="form-text">The name will be displayed in menus and course creation forms.</div>
             </div>
             <div class="col-md-6 mb-3">
               <label for="editSubcategorySlug" class="form-label">Subcategory Slug</label>
               <input type="text" class="form-control" id="editSubcategorySlug" name="slug" placeholder="Enter subcategory slug" required>
               <div class="form-text">The slug is used in URLs and should contain only lowercase letters, numbers, and hyphens.</div>
             </div>
           </div>
           
           <div class="mb-3">
             <label for="editParentCategorySelect" class="form-label">Parent Category</label>
             <select id="editParentCategorySelect" name="category_id" class="form-select" required>
               <option value="" disabled>Select a parent category</option>
               <?php foreach($categories as $category): ?>
                 <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
               <?php endforeach; ?>
             </select>
             <div class="form-text">Select the parent category for this subcategory.</div>
           </div>
           
           <div class="alert alert-info">
             <div class="d-flex align-items-start">
               <i class="bx bx-info-circle mt-1 me-2"></i>
               <div>
                 <p class="mb-0">This subcategory is used by <strong id="editSubcategoryCourseCount">0</strong> courses.</p>
               </div>
             </div>
           </div>
         </div>
         <div class="modal-footer">
           <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
           <button type="submit" class="btn btn-primary">Update Subcategory</button>
         </div>
       </form>
     </div>
   </div>
 </div>

 <!-- Delete Subcategory Modal -->
 <div class="modal fade" id="deleteSubcategoryModal" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered" role="document">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title">Delete Subcategory</h5>
         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
       </div>
       <form id="deleteSubcategoryForm" action="../backend/admin/delete-subcategory.php" method="POST">
         <div class="modal-body">
           <input type="hidden" id="deleteSubcategoryId" name="subcategory_id" value="">

           <div class="text-center mb-4">
             <i class="bx bx-error-circle text-danger" style="font-size: 6rem;"></i>
           </div>

           <p class="mb-0 text-center">Are you sure you want to delete <strong id="delete-subcategory-name"></strong>?</p>

           <div id="delete-subcategory-warning" class="alert alert-warning mt-3">
             <div class="d-flex">
               <i class="bx bx-error me-2 mt-1"></i>
               <div>
                 <p class="mb-0">This subcategory is used by <strong id="delete-subcategory-course-count">0</strong> courses.</p>
                 <p class="mb-0 mt-2">Deleting this subcategory will make those courses inaccessible until they are reassigned to another subcategory.</p>
               </div>
             </div>
           </div>
         </div>
         <div class="modal-footer">
           <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
           <button type="submit" class="btn btn-danger">Delete Subcategory</button>
         </div>
       </form>
     </div>
   </div>
 </div>
</div>
<!-- / Content -->

<script>
 document.addEventListener('DOMContentLoaded', function() {
   // **Initialize Tooltips**
   const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
   tooltipTriggerList.forEach(function(tooltipTriggerEl) {
     new bootstrap.Tooltip(tooltipTriggerEl);
   });

   // **Toast Notification Function**
   function showToast(message, type = 'success') {
     const toastEl = document.getElementById(type === 'success' ? 'successToast' : 'errorToast');
     const toastMessageEl = document.getElementById(type === 'success' ? 'successToastMessage' : 'errorToastMessage');

     if (toastEl && toastMessageEl) {
       toastMessageEl.textContent = message;
       const toast = new bootstrap.Toast(toastEl);
       toast.show();
     }
   }

   // **Show/Hide Loading Overlay**
   function showOverlay(message = 'Processing...') {
     const overlay = document.getElementById('loadingOverlay');
     const messageEl = document.getElementById('loading-message');

     if (messageEl) {
       messageEl.textContent = message;
     }

     overlay.style.display = 'flex';
     overlay.dataset.startTime = Date.now();
   }

   function removeOverlay() {
     const overlay = document.getElementById('loadingOverlay');
     const startTime = parseInt(overlay.dataset.startTime || 0);
     const currentTime = Date.now();
     const elapsedTime = currentTime - startTime;

     if (elapsedTime >= 1000) {
       overlay.style.display = 'none';
     } else {
       setTimeout(() => {
         overlay.style.display = 'none';
       }, 1000 - elapsedTime);
     }
   }

   // **Slug Generator**
   function generateSlug(text) {
     return text
       .toLowerCase()
       .replace(/[^\w\s-]/g, '') // Remove non-word chars
       .replace(/[\s_-]+/g, '-') // Replace spaces and underscores with hyphens
       .replace(/^-+|-+$/g, ''); // Remove leading/trailing hyphens
   }

   // Auto-generate slug on name input
   document.getElementById('categoryName')?.addEventListener('input', function() {
     document.getElementById('categorySlug').value = generateSlug(this.value);
   });

   document.getElementById('subcategoryName')?.addEventListener('input', function() {
     document.getElementById('subcategorySlug').value = generateSlug(this.value);
   });

   // **Category Pagination Functionality**
   const ITEMS_PER_PAGE = 10; // Max number of items per page
   let currentCategoryPage = 1;
   let currentSubcategoryPage = 1;

   function setupCategoryPagination() {
     const visibleRows = Array.from(document.querySelectorAll('.category-row'))
       .filter(row => row.style.display !== 'none');

     const totalItems = visibleRows.length;
     const totalPages = Math.ceil(totalItems / ITEMS_PER_PAGE);

     document.getElementById('category-showing-start').textContent =
       totalItems > 0 ? ((currentCategoryPage - 1) * ITEMS_PER_PAGE + 1) : 0;
     document.getElementById('category-showing-end').textContent =
       Math.min(currentCategoryPage * ITEMS_PER_PAGE, totalItems);
     document.getElementById('category-total-entries').textContent = totalItems;

     visibleRows.forEach(row => row.classList.add('d-none'));

     const startIndex = (currentCategoryPage - 1) * ITEMS_PER_PAGE;
     const endIndex = Math.min(startIndex + ITEMS_PER_PAGE, totalItems);

     for (let i = startIndex; i < endIndex; i++) {
       visibleRows[i].classList.remove('d-none');
     }

     updateCategoryPaginationUI(totalPages);
   }

   function updateCategoryPaginationUI(totalPages) {
     const paginationContainer = document.querySelector('#category-pagination-container ul');
     const pageItems = document.querySelectorAll('#category-pagination-container ul li:not(.previous):not(.next)');
     pageItems.forEach(item => item.remove());

     const prevButton = document.getElementById('category-pagination-previous');
     prevButton.classList.toggle('disabled', currentCategoryPage === 1);

     const nextButton = document.getElementById('category-pagination-next');
     nextButton.classList.toggle('disabled', currentCategoryPage === totalPages || totalPages === 0);

     const maxVisiblePages = 5;
     let startPage = Math.max(1, currentCategoryPage - Math.floor(maxVisiblePages / 2));
     let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

     if (endPage - startPage + 1 < maxVisiblePages && startPage > 1) {
       startPage = Math.max(1, endPage - maxVisiblePages + 1);
     }

     for (let i = startPage; i <= endPage; i++) {
       const li = document.createElement('li');
       li.className = `paginate_button page-item ${i === currentCategoryPage ? 'active' : ''}`;
       li.innerHTML = `<a href="#" class="page-link">${i}</a>`;
       li.addEventListener('click', function(e) {
         e.preventDefault();
         if (i !== currentCategoryPage) {
           currentCategoryPage = i;
           setupCategoryPagination();
         }
       });
       paginationContainer.insertBefore(li, nextButton);
     }

     if (startPage > 1) {
       const ellipsisStart = document.createElement('li');
       ellipsisStart.className = 'paginate_button page-item disabled';
       ellipsisStart.innerHTML = '<a href="#" class="page-link">...</a>';
       paginationContainer.insertBefore(ellipsisStart, paginationContainer.querySelector(`li:nth-child(${2})`));
     }

     if (endPage < totalPages) {
       const ellipsisEnd = document.createElement('li');
       ellipsisEnd.className = 'paginate_button page-item disabled';
       ellipsisEnd.innerHTML = '<a href="#" class="page-link">...</a>';
       paginationContainer.insertBefore(ellipsisEnd, nextButton);
     }
   }

   document.getElementById('category-pagination-previous')?.addEventListener('click', function(e) {
     e.preventDefault();
     if (currentCategoryPage > 1) {
       currentCategoryPage--;
       setupCategoryPagination();
     }
   });

   document.getElementById('category-pagination-next')?.addEventListener('click', function(e) {
     e.preventDefault();
     const visibleRows = document.querySelectorAll('.category-row:not([style*="display: none"])');
     const totalPages = Math.ceil(visibleRows.length / ITEMS_PER_PAGE);
     if (currentCategoryPage < totalPages) {
       currentCategoryPage++;
       setupCategoryPagination();
     }
   });

   // **Subcategory Pagination Functionality**
   function setupSubcategoryPagination() {
     const visibleRows = Array.from(document.querySelectorAll('.subcategory-row'))
       .filter(row => row.style.display !== 'none');

     const totalItems = visibleRows.length;
     const totalPages = Math.ceil(totalItems / ITEMS_PER_PAGE);

     document.getElementById('subcategory-showing-start').textContent =
       totalItems > 0 ? ((currentSubcategoryPage - 1) * ITEMS_PER_PAGE + 1) : 0;
     document.getElementById('subcategory-showing-end').textContent =
       Math.min(currentSubcategoryPage * ITEMS_PER_PAGE, totalItems);
     document.getElementById('subcategory-total-entries').textContent = totalItems;

     visibleRows.forEach(row => row.classList.add('d-none'));

     const startIndex = (currentSubcategoryPage - 1) * ITEMS_PER_PAGE;
     const endIndex = Math.min(startIndex + ITEMS_PER_PAGE, totalItems);

     for (let i = startIndex; i < endIndex; i++) {
       visibleRows[i].classList.remove('d-none');
     }

     updateSubcategoryPaginationUI(totalPages);
   }

   function updateSubcategoryPaginationUI(totalPages) {
     const paginationContainer = document.querySelector('#subcategory-pagination-container ul');
     const pageItems = document.querySelectorAll('#subcategory-pagination-container ul li:not(.previous):not(.next)');
     pageItems.forEach(item => item.remove());

     const prevButton = document.getElementById('subcategory-pagination-previous');
     prevButton.classList.toggle('disabled', currentSubcategoryPage === 1);

     const nextButton = document.getElementById('subcategory-pagination-next');
     nextButton.classList.toggle('disabled', currentSubcategoryPage === totalPages || totalPages === 0);

     const maxVisiblePages = 5;
     let startPage = Math.max(1, currentSubcategoryPage - Math.floor(maxVisiblePages / 2));
     let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

     if (endPage - startPage + 1 < maxVisiblePages && startPage > 1) {
       startPage = Math.max(1, endPage - maxVisiblePages + 1);
     }

     for (let i = startPage; i <= endPage; i++) {
       const li = document.createElement('li');
       li.className = `paginate_button page-item ${i === currentSubcategoryPage ? 'active' : ''}`;
       li.innerHTML = `<a href="#" class="page-link">${i}</a>`;
       li.addEventListener('click', function(e) {
         e.preventDefault();
         if (i !== currentSubcategoryPage) {
           currentSubcategoryPage = i;
           setupSubcategoryPagination();
         }
       });
       paginationContainer.insertBefore(li, nextButton);
     }

     if (startPage > 1) {
       const ellipsisStart = document.createElement('li');
       ellipsisStart.className = 'paginate_button page-item disabled';
       ellipsisStart.innerHTML = '<a href="#" class="page-link">...</a>';
       paginationContainer.insertBefore(ellipsisStart, paginationContainer.querySelector(`li:nth-child(${2})`));
     }

     if (endPage < totalPages) {
       const ellipsisEnd = document.createElement('li');
       ellipsisEnd.className = 'paginate_button page-item disabled';
       ellipsisEnd.innerHTML = '<a href="#" class="page-link">...</a>';
       paginationContainer.insertBefore(ellipsisEnd, nextButton);
     }
   }

   document.getElementById('subcategory-pagination-previous')?.addEventListener('click', function(e) {
     e.preventDefault();
     if (currentSubcategoryPage > 1) {
       currentSubcategoryPage--;
       setupSubcategoryPagination();
     }
   });

   document.getElementById('subcategory-pagination-next')?.addEventListener('click', function(e) {
     e.preventDefault();
     const visibleRows = document.querySelectorAll('.subcategory-row:not([style*="display: none"])');
     const totalPages = Math.ceil(visibleRows.length / ITEMS_PER_PAGE);
     if (currentSubcategoryPage < totalPages) {
       currentSubcategoryPage++;
       setupSubcategoryPagination();
     }
   });

   // **Search Functionality**
   document.getElementById('categorySearch')?.addEventListener('keyup', filterCategories);
   
   function filterCategories() {
     const searchTerm = document.getElementById('categorySearch').value.toLowerCase();
     const rows = document.querySelectorAll('.category-row');
     const emptySearchResults = document.getElementById('empty-category-results');
     let visibleCount = 0;

     rows.forEach(row => {
       const name = row.getAttribute('data-name').toLowerCase();
       const slug = row.getAttribute('data-slug').toLowerCase();
       const departmentNames = row.getAttribute('data-department-names').toLowerCase();

       if (name.includes(searchTerm) || slug.includes(searchTerm) || departmentNames.includes(searchTerm)) {
         row.style.display = '';
         visibleCount++;
       } else {
         row.style.display = 'none';
       }
     });

     if (visibleCount === 0 && rows.length > 0) {
       if (emptySearchResults) emptySearchResults.classList.remove('d-none');
     } else {
       if (emptySearchResults) emptySearchResults.classList.add('d-none');
       currentCategoryPage = 1;
       setupCategoryPagination();
     }
   }
   
   document.getElementById('subcategorySearch')?.addEventListener('keyup', filterSubcategories);
   
   function filterSubcategories() {
     const searchTerm = document.getElementById('subcategorySearch').value.toLowerCase();
     const rows = document.querySelectorAll('.subcategory-row');
     const emptySearchResults = document.getElementById('empty-subcategory-results');
     let visibleCount = 0;

     rows.forEach(row => {
       const name = row.getAttribute('data-name').toLowerCase();
       const slug = row.getAttribute('data-slug').toLowerCase();
       const categoryName = row.getAttribute('data-category-name').toLowerCase();

       if (name.includes(searchTerm) || slug.includes(searchTerm) || categoryName.includes(searchTerm)) {
         row.style.display = '';
         visibleCount++;
       } else {
         row.style.display = 'none';
       }
     });

     if (visibleCount === 0 && rows.length > 0) {
       if (emptySearchResults) emptySearchResults.classList.remove('d-none');
     } else {
       if (emptySearchResults) emptySearchResults.classList.add('d-none');
       currentSubcategoryPage = 1;
       setupSubcategoryPagination();
     }
   }

   // **Edit Category** 
   document.querySelectorAll('.edit-category').forEach(btn => {
     btn.addEventListener('click', function() {
       const row = this.closest('tr');
       const categoryId = row.getAttribute('data-id');
       const categoryName = row.getAttribute('data-name');
       const categorySlug = row.getAttribute('data-slug');
       const departmentIds = row.getAttribute('data-department-ids');
       const subcategoryCount = row.getAttribute('data-subcategory-count');
       const courseCount = row.getAttribute('data-course-count');
       
       // Set values in modal
       document.getElementById('editCategoryId').value = categoryId;
       document.getElementById('editCategoryName').value = categoryName;
       document.getElementById('editCategorySlug').value = categorySlug;
       document.getElementById('editSubcategoryCount').textContent = subcategoryCount;
       document.getElementById('editCourseCount').textContent = courseCount;
       
       // Set department multiselect
       const departmentSelect = document.getElementById('editDepartmentMultiselect');
       const departmentIdList = departmentIds ? departmentIds.split(',') : [];
       
       for (let i = 0; i < departmentSelect.options.length; i++) {
         departmentSelect.options[i].selected = departmentIdList.includes(departmentSelect.options[i].value);
       }
       
       // Show the modal
       const modal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
       modal.show();
     });
   });

   // **Delete Category**
   document.querySelectorAll('.delete-category').forEach(btn => {
     btn.addEventListener('click', function() {
       const row = this.closest('tr');
       const categoryId = row.getAttribute('data-id');
       const categoryName = row.getAttribute('data-name');
       const subcategoryCount = row.getAttribute('data-subcategory-count');
       const courseCount = row.getAttribute('data-course-count');
       
       // Set values in modal
       document.getElementById('deleteCategoryId').value = categoryId;
       document.getElementById('delete-category-name').textContent = categoryName;
       document.getElementById('delete-subcategory-count').textContent = subcategoryCount;
       document.getElementById('delete-course-count').textContent = courseCount;
       
       // Show the modal
       const modal = new bootstrap.Modal(document.getElementById('deleteCategoryModal'));
       modal.show();
     });
   });

   // **Edit Subcategory**
   document.querySelectorAll('.edit-subcategory').forEach(btn => {
     btn.addEventListener('click', function() {
       const row = this.closest('tr');
       const subcategoryId = row.getAttribute('data-id');
       const subcategoryName = row.getAttribute('data-name');
       const subcategorySlug = row.getAttribute('data-slug');
       const categoryId = row.getAttribute('data-category-id');
       const courseCount = row.getAttribute('data-course-count');
       
       // Set values in modal
       document.getElementById('editSubcategoryId').value = subcategoryId;
       document.getElementById('editSubcategoryName').value = subcategoryName;
       document.getElementById('editSubcategorySlug').value = subcategorySlug;
       document.getElementById('editSubcategoryCourseCount').textContent = courseCount;
       
       // Set parent category select
       const categorySelect = document.getElementById('editParentCategorySelect');
       for (let i = 0; i < categorySelect.options.length; i++) {
         categorySelect.options[i].selected = categorySelect.options[i].value === categoryId;
       }
       
       // Show the modal
       const modal = new bootstrap.Modal(document.getElementById('editSubcategoryModal'));
       modal.show();
     });
   });

   // **Delete Subcategory**
   document.querySelectorAll('.delete-subcategory').forEach(btn => {
     btn.addEventListener('click', function() {
       const row = this.closest('tr');
       const subcategoryId = row.getAttribute('data-id');
       const subcategoryName = row.getAttribute('data-name');
       const courseCount = row.getAttribute('data-course-count');
       
       // Set values in modal
       document.getElementById('deleteSubcategoryId').value = subcategoryId;
       document.getElementById('delete-subcategory-name').textContent = subcategoryName;
       document.getElementById('delete-subcategory-course-count').textContent = courseCount;
       
       // Show the modal
       const modal = new bootstrap.Modal(document.getElementById('deleteSubcategoryModal'));
       modal.show();
     });
   });

   // **AJAX Form Submission Handler**
   function handleFormSubmit(formId, url) {
     const form = document.getElementById(formId);
     if (!form) return;

     form.addEventListener('submit', function(e) {
       e.preventDefault();
       
       showOverlay('Processing...');

       fetch(url, {
           method: 'POST',
           body: new FormData(form)
         })
         .then(response => {
           if (!response.ok) throw new Error('Network response was not ok');
           return response.json();
         })
         .then(data => {
           removeOverlay();
           if (data.status === 'success') {
             showToast(data.message, 'success');
             const modal = bootstrap.Modal.getInstance(form.closest('.modal'));
             if (modal) modal.hide();
             setTimeout(() => window.location.reload(), 1500);
           } else {
             showToast(data.message || 'An error occurred', 'error');
           }
         })
         .catch(error => {
           removeOverlay();
           console.error('Error:', error);
           showToast('An unexpected error occurred. Please try again.', 'error');
         });
     });
   }

   handleFormSubmit('addCategoryForm', '../backend/admin/add-category.php');
   handleFormSubmit('editCategoryForm', '../backend/admin/update-category.php');
   handleFormSubmit('deleteCategoryForm', '../backend/admin/delete-category.php');
   handleFormSubmit('addSubcategoryForm', '../backend/admin/add-subcategory.php');
   handleFormSubmit('editSubcategoryForm', '../backend/admin/update-subcategory.php');
   handleFormSubmit('deleteSubcategoryForm', '../backend/admin/delete-subcategory.php');

   // **Initialize Pagination**
   setupCategoryPagination();
   setupSubcategoryPagination();
   
   // Check URL for success message
   const urlParams = new URLSearchParams(window.location.search);
   const successMsg = urlParams.get('success');
   const errorMsg = urlParams.get('error');
   
   if (successMsg) {
     showToast(decodeURIComponent(successMsg), 'success');
   }
   
   if (errorMsg) {
     showToast(decodeURIComponent(errorMsg), 'error');
   }
 });
</script>

<?php include_once '../includes/admin/footer.php'; ?>