<?php include '../includes/department/header.php'; ?>
<?php include '../includes/toast.php'; ?>

<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main">
    <!-- Navbar -->
    <nav class="js-nav-scroller navbar navbar-expand-lg navbar-sidebar navbar-vertical navbar-light bg-white border-end" data-hs-nav-scroller-options='{
            "type": "vertical",
            "target": ".navbar-nav .active",
            "offset": 80
           }'>

        <?php include '../includes/department/sidebar.php'; ?>
    </nav>
    <!-- End Navbar -->


    <!-- Content -->
    <div class="navbar-sidebar-aside-content content-space-1 content-space-md-2 px-lg-5 px-xl-10">
        <!-- Page Header -->
        <div class="docs-page-header">
            <div class="row align-items-center">
                <h1 class="docs-page-header-title">Course Categories</h1>
                <div class="col-sm">
                    <p class="docs-page-header-text">
                        Organize your courses efficiently by grouping them into categories, making it easier for learners to find what they need.
                        <a href="#" class="text-primary fw-bold" data-bs-toggle="modal" data-bs-target="#addCategoryModal">Create a new category</a> to keep your course structure clear and accessible.
                    </p>

                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Nav -->
        <div class="text-center">
            <ul class="nav nav-segment nav-pills mb-2" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="category-tab" href="#category" data-bs-toggle="pill" data-bs-target="#category" role="tab" aria-controls="category" aria-selected="true">Category</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="sub-category-tab" href="#sub-category" data-bs-toggle="pill" data-bs-target="#sub-category" role="tab" aria-controls="sub-category" aria-selected="false">Sub Category</a>
                </li>
            </ul>
        </div>
        <!-- End Nav -->

        <!-- Tab Content -->
        <div class="tab-content">
            <div class="tab-pane fade show active" id="category" role="tabpanel" aria-labelledby="category-tab">
                <!-- Categories Table -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Categories List</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Category Name</th>
                                    <th>Slug</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="categoriesTableBody">
                                <!-- Data will be loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- End Categories Table -->
            </div>

            <div class="tab-pane fade" id="sub-category" role="tabpanel" aria-labelledby="sub-category-tab">
                <!-- Subcategories Table -->
                <div class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0 me-3">Subcategories List</h5>
                            <div class="dropdown ms-3">
                                <div class="d-flex align-items-center me-3">
                                    <select id="categoryFilter" class="form-select form-select-sm" style="width: auto;">
                                        <option value="all">All Categories</option>
                                        <!-- Categories will be populated dynamically -->
                                    </select>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSubcategoryModal">
                            <i class="bi bi-plus-lg"></i> Add Subcategory
                        </button>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <!-- <th>Parent Category</th> -->
                                    <th>Subcategory Name</th>
                                    <th>Slug</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="subcategoriesTableBody">
                                <!-- Data will be loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- End Subcategories Table -->
            </div>
        </div>
        <!-- End Tab Content -->


        <!-- Add Category Modal -->
        <!-- Spinner HTML -->
        <div id="loadingSpinner" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1050;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>


        <!-- Add Category Modal -->
        <div class="modal fade" id="addCategoryModal" data-bs-backdrop="static" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addCategoryForm">
                            <div class="mb-3">
                                <label class="form-label">Category Name</label>
                                <input type="text" class="form-control" id="add_category_name" name="name" required oninput="generateAndSetSlug()">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Slug</label>
                                <input type="text" class="form-control" id="add_category_slug" name="slug" disabled>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-white" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Add Category</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Add Category Modal -->


        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script>
            // Function to update and show the toast
            function updateAndShowToast(type, message) {
                // Get the toast element
                const toastElement = document.getElementById('liveToast');

                // Ensure toast appears above overlay
                toastElement.style.zIndex = '10000';

                // Update the toast body message
                toastElement.querySelector('.toast-body').textContent = message;

                // Show the toast with a standard duration
                const toast = new bootstrap.Toast(toastElement);
                toast.show();
            }
            // Create and apply page overlay for loading effect
            function createOverlay() {
                const overlay = document.createElement('div');
                overlay.id = 'pageOverlay';
                overlay.style.position = 'fixed';
                overlay.style.top = '0';
                overlay.style.left = '0';
                overlay.style.width = '100%';
                overlay.style.height = '100%';
                overlay.style.backgroundColor = 'rgba(255, 255, 255, 0.7)';
                overlay.style.backdropFilter = 'blur(5px)';
                overlay.style.zIndex = '9998';
                overlay.style.display = 'flex';
                overlay.style.justifyContent = 'center';
                overlay.style.alignItems = 'center';

                // Add a loading spinner
                const spinner = document.createElement('div');
                spinner.className = 'spinner-border text-primary';
                spinner.setAttribute('role', 'status');
                spinner.innerHTML = '<span class="visually-hidden">Loading...</span>';

                overlay.appendChild(spinner);
                document.body.appendChild(overlay);
            }

            // Remove overlay
            function removeOverlay() {
                const overlay = document.getElementById('pageOverlay');
                if (overlay) {
                    document.body.removeChild(overlay);
                }
            }

            function generateSlug(text) {
                return text.toLowerCase().trim().replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "");
            }

            function generateAndSetSlug() {
                var nameInput = document.getElementById('add_category_name');
                var slugInput = document.getElementById('add_category_slug');
                slugInput.value = generateSlug(nameInput.value);
            }

            $(document).ready(function() {
                $('#addCategoryForm').on('submit', function(e) {
                    e.preventDefault(); // Prevent the default form submission

                    var categoryName = $('#add_category_name').val();
                    var categorySlug = $('#add_category_slug').val(); // Directly use the generated slug from the disabled input

                    // Hide existing spinner and show overlay instead
                    $('#loadingSpinner').css('display', 'none');
                    createOverlay();

                    $.ajax({
                        type: "POST",
                        url: "../backend/courses/add_category.php",
                        data: {
                            name: categoryName,
                            slug: categorySlug
                        },
                        dataType: 'json', // Expecting JSON response from the server
                        success: function(response) {
                            if (response.error) {
                                removeOverlay();
                                updateAndShowToast('danger', response.error);
                            } else {
                                updateAndShowToast('success', response.success);
                                $('#addCategoryModal').modal('hide'); // Hide modal on success

                                // Keep overlay during page reload
                                setTimeout(function() {
                                    window.location.reload(true); // Refresh the page after success
                                }, 2000);
                            }
                        },
                        error: function(xhr, status, error) {
                            removeOverlay();
                            updateAndShowToast('danger', 'Error adding category: ' + error);
                        }
                    });
                });
            });
        </script>


        <!-- End Add Category Modal -->

        <!-- Edit Category Modal -->
        <div class="modal fade" id="editCategoryModal" data-bs-backdrop="static" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editCategoryForm">
                            <input type="hidden" id="edit_category_id">
                            <div class="mb-3">
                                <label class="form-label">Category Name</label>
                                <input type="text" class="form-control" id="edit_category_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Slug</label>
                                <input type="text" class="form-control" id="edit_category_slug">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-white" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Update Category</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Edit Category Modal -->

        <!-- Add Subcategory Modal -->
        <div class="modal fade" id="addSubcategoryModal" data-bs-backdrop="static" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Subcategory</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addSubcategoryForm">
                            <div class="mb-3">
                                <label class="form-label">Parent Category</label>
                                <select class="form-select" id="add_subcategory_category" name="category_id" required>
                                    <option value="">Select Parent Category</option>
                                    <!-- Categories will be loaded dynamically -->
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Subcategory Name</label>
                                <input type="text" class="form-control" id="add_subcategory_name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Slug</label>
                                <input type="text" class="form-control" id="add_subcategory_slug" name="slug" readonly>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-white" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Add Subcategory</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Add Subcategory Modal -->

        <!-- Edit Subcategory Modal -->
        <div class="modal fade" id="editSubcategoryModal" data-bs-backdrop="static" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Subcategory</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editSubcategoryForm">
                            <input type="hidden" id="edit_subcategory_id">
                            <div class="mb-3">
                                <label class="form-label">Parent Category</label>
                                <select class="form-select" id="edit_subcategory_category" required>
                                    <option value="">Select Parent Category</option>
                                    <!-- Categories will be loaded dynamically -->
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Subcategory Name</label>
                                <input type="text" class="form-control" id="edit_subcategory_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Slug</label>
                                <input type="text" class="form-control" id="edit_subcategory_slug">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-white" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Update Subcategory</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Edit Subcategory Modal -->

        <!-- JavaScript (AJAX) -->
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                loadCategories();

                // Add this: Check if hash is #sub-category and load subcategories
                if (window.location.hash === "#sub-category") {
                    // Activate the tab
                    document.getElementById('sub-category-tab').click();
                }

                // Make formatDate and generateSlug functions globally available
                window.formatDate = function(dateStr) {
                    let date = new Date(dateStr);
                    return date.toLocaleDateString('en-US', {
                        month: 'long',
                        day: 'numeric',
                        year: 'numeric'
                    });
                };

                window.generateSlug = function(text) {
                    return text.toLowerCase().trim().replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "");
                };

                // Load subcategories when tab is clicked
                document.getElementById('sub-category-tab').addEventListener('click', function() {
                    loadSubcategories();
                    loadCategoriesForDropdown();
                });

                // Function to load categories
                function loadCategories() {
                    fetch("../backend/courses/fetch_categories.php")
                        .then(response => response.json())
                        .then(data => {
                            let tableBody = document.getElementById("categoriesTableBody");
                            tableBody.innerHTML = "";
                            data.forEach(category => {
                                tableBody.innerHTML += `
                <tr>
                    <td>${category.name}</td>
                    <td>${category.slug}</td>
                    <td>${formatDate(category.created_at)}</td>
                    <td>
                        <a href="#" class="px-2 edit-btn" data-bs-toggle="modal" data-bs-target="#editCategoryModal"
                            data-id="${category.category_id}" data-name="${category.name}" data-slug="${category.slug}">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="#" class="text-danger px-2 delete-btn" data-id="${category.category_id}">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                `;
                            });
                            attachEventListeners();
                        })
                        .catch(error => {
                            console.error('Failed to fetch categories: ', error);
                        });
                }
                // Store all subcategories for filtering
                let allSubcategories = [];

                // Function to load subcategories
                function loadSubcategories() {
                    fetch("../backend/courses/fetch_subcategories.php")
                        .then(response => response.json())
                        .then(data => {
                            // Store all subcategories for filtering
                            allSubcategories = data;

                            // Populate the table with all subcategories
                            populateSubcategoriesTable(allSubcategories);

                            // Attach event listeners
                            attachSubcategoryEventListeners();
                        })
                        .catch(error => {
                            console.error('Failed to fetch subcategories: ', error);
                        });
                }

                // Function to populate subcategories table
                function populateSubcategoriesTable(subcategories) {
                    let tableBody = document.getElementById("subcategoriesTableBody");
                    tableBody.innerHTML = "";

                    if (subcategories.length === 0) {
                        tableBody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center">No subcategories found</td>
            </tr>
        `;
                        return;
                    }

                    subcategories.forEach(subcategory => {
                        tableBody.innerHTML += `
        <tr>
          <!--  <td>${subcategory.category_name}</td> -->
            <td>${subcategory.name}</td>
            <td>${subcategory.slug}</td>
            <td>${formatDate(subcategory.created_at)}</td>
            <td>
                <a href="#" class="px-2 edit-subcategory-btn" data-bs-toggle="modal" data-bs-target="#editSubcategoryModal"
                    data-id="${subcategory.subcategory_id}" data-name="${subcategory.name}" 
                    data-slug="${subcategory.slug}" data-category="${subcategory.category_id}">
                    <i class="bi bi-pencil"></i>
                </a>
                <a href="#" class="text-danger px-2 delete-subcategory-btn" data-id="${subcategory.subcategory_id}">
                    <i class="bi bi-trash"></i>
                </a>
            </td>
        </tr>
        `;
                    });
                }

                // Function to load categories for dropdowns and filters
                // Function to load categories for dropdowns and filters
                function loadCategoriesForDropdown() {
                    fetch("../backend/courses/fetch_categories.php")
                        .then(response => response.json())
                        .then(data => {
                            // Populate add subcategory dropdown
                            let addDropdown = document.getElementById("add_subcategory_category");
                            if (addDropdown) {
                                addDropdown.innerHTML = '<option value="">Select Parent Category</option>';
                                data.forEach(category => {
                                    addDropdown.innerHTML += `<option value="${category.category_id}">${category.name}</option>`;
                                });
                            }

                            // Populate edit subcategory dropdown
                            let editDropdown = document.getElementById("edit_subcategory_category");
                            if (editDropdown) {
                                editDropdown.innerHTML = '<option value="">Select Parent Category</option>';
                                data.forEach(category => {
                                    editDropdown.innerHTML += `<option value="${category.category_id}">${category.name}</option>`;
                                });
                            }

                            // Populate filter dropdown
                            let filterDropdown = document.getElementById("categoryFilter");
                            if (filterDropdown) {
                                // Keep the "All Categories" option and add the rest
                                let html = '<option value="all">All Categories</option>';
                                data.forEach(category => {
                                    html += `<option value="${category.category_id}">${category.name}</option>`;
                                });
                                filterDropdown.innerHTML = html;

                                // Add change event listener to filter
                                filterDropdown.addEventListener('change', function() {
                                    const categoryId = this.value;
                                    filterSubcategories(categoryId);
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Failed to fetch categories for dropdown: ', error);
                        });
                }

                // Function to filter subcategories by category
                function filterSubcategories(categoryId) {
                    if (categoryId === 'all') {
                        // Show all subcategories
                        populateSubcategoriesTable(allSubcategories);
                    } else {
                        // Filter subcategories by category
                        const filteredSubcategories = allSubcategories.filter(subcategory =>
                            subcategory.category_id === categoryId
                        );
                        populateSubcategoriesTable(filteredSubcategories);
                    }

                    // Reattach event listeners after updating the table
                    attachSubcategoryEventListeners();
                }
                // Event listeners for category actions
                function attachEventListeners() {
                    document.querySelectorAll(".edit-btn").forEach(button => {
                        button.addEventListener("click", function() {
                            let id = this.getAttribute("data-id");
                            let name = this.getAttribute("data-name");
                            let slug = this.getAttribute("data-slug");

                            let editNameInput = document.getElementById("edit_category_name");
                            let editSlugInput = document.getElementById("edit_category_slug");
                            let updateBtn = document.querySelector("#editCategoryForm button[type='submit']");

                            // Set initial values
                            document.getElementById("edit_category_id").value = id;
                            editNameInput.value = name;
                            editSlugInput.value = slug;
                            editSlugInput.disabled = true; // Disable slug input
                            updateBtn.disabled = true;

                            // Enable update button only if changes are made
                            editNameInput.addEventListener("input", () => {
                                editSlugInput.value = generateSlug(editNameInput.value);
                                updateBtn.disabled = editNameInput.value.trim() === name.trim();
                            });
                        });
                    });

                    document.getElementById("editCategoryForm").addEventListener("submit", function(e) {
                        e.preventDefault();
                        let id = document.getElementById("edit_category_id").value;
                        let name = document.getElementById("edit_category_name").value.trim();
                        let slug = document.getElementById("edit_category_slug").value;

                        createOverlay();

                        fetch("../backend/courses/category_actions.php", {
                                method: "POST",
                                body: new URLSearchParams({
                                    action: "edit",
                                    id,
                                    name,
                                    slug
                                }),
                            }).then(response => response.json())
                            .then(data => {
                                if (data.error) {
                                    removeOverlay();
                                    updateAndShowToast('danger', data.error);
                                } else {
                                    updateAndShowToast('success', 'Category updated successfully');
                                    new bootstrap.Modal(document.getElementById("editCategoryModal")).hide();
                                    setTimeout(function() {
                                        window.location.reload(true);
                                    }, 2000);
                                }
                            })
                            .catch(error => {
                                removeOverlay();
                                console.error('Error updating category: ', error);
                                updateAndShowToast('danger', 'Failed to update category. Please try again.');
                            });
                    });

                    document.querySelectorAll(".delete-btn").forEach(button => {
                        button.addEventListener("click", function() {
                            let categoryId = this.getAttribute("data-id");
                            if (confirm("Are you sure you want to delete this category?")) {
                                createOverlay();

                                fetch("../backend/courses/category_actions.php", {
                                        method: "POST",
                                        body: new URLSearchParams({
                                            action: "delete",
                                            id: categoryId
                                        }),
                                    }).then(response => response.json())
                                    .then(data => {
                                        if (data.error) {
                                            removeOverlay();
                                            updateAndShowToast('danger', data.error);
                                        } else {
                                            updateAndShowToast('success', 'Category deleted successfully');
                                            setTimeout(function() {
                                                window.location.reload(true);
                                            }, 2000);
                                        }
                                    })
                                    .catch(error => {
                                        removeOverlay();
                                        console.error('Error deleting category: ', error);
                                        updateAndShowToast('danger', 'Failed to delete category. Please try again.');
                                    });
                            }
                        });
                    });
                }

                // Event listeners for subcategory actions
                function attachSubcategoryEventListeners() {
                    // Edit subcategory button click
                    document.querySelectorAll(".edit-subcategory-btn").forEach(button => {
                        button.addEventListener("click", function() {
                            let id = this.getAttribute("data-id");
                            let name = this.getAttribute("data-name");
                            let slug = this.getAttribute("data-slug");
                            let categoryId = this.getAttribute("data-category");

                            let editNameInput = document.getElementById("edit_subcategory_name");
                            let editSlugInput = document.getElementById("edit_subcategory_slug");
                            let editCategorySelect = document.getElementById("edit_subcategory_category");
                            let updateBtn = document.querySelector("#editSubcategoryForm button[type='submit']");

                            // Set initial values
                            document.getElementById("edit_subcategory_id").value = id;
                            editNameInput.value = name;
                            editSlugInput.value = slug;
                            editCategorySelect.value = categoryId;
                            editSlugInput.disabled = true; // Disable slug input
                            updateBtn.disabled = true;

                            // Enable update button if subcategory name is changed
                            editNameInput.addEventListener("input", () => {
                                editSlugInput.value = generateSlug(editNameInput.value);
                                updateBtn.disabled = editNameInput.value.trim() === name.trim() &&
                                    editCategorySelect.value === categoryId;
                            });

                            // Enable update button if category is changed
                            editCategorySelect.addEventListener("change", () => {
                                updateBtn.disabled = editNameInput.value.trim() === name.trim() &&
                                    editCategorySelect.value === categoryId;
                            });
                        });
                    });

                    // Edit subcategory form submission
                    document.getElementById("editSubcategoryForm").addEventListener("submit", function(e) {
                        e.preventDefault();
                        let id = document.getElementById("edit_subcategory_id").value;
                        let name = document.getElementById("edit_subcategory_name").value.trim();
                        let slug = document.getElementById("edit_subcategory_slug").value;
                        let categoryId = document.getElementById("edit_subcategory_category").value;

                        createOverlay();

                        fetch("../backend/courses/subcategory_actions.php", {
                                method: "POST",
                                body: new URLSearchParams({
                                    action: "edit",
                                    id,
                                    name,
                                    slug,
                                    category_id: categoryId
                                }),
                            }).then(response => response.json())
                            .then(data => {
                                if (data.error) {
                                    removeOverlay();
                                    updateAndShowToast('danger', data.error);
                                } else {
                                    updateAndShowToast('success', 'Subcategory updated successfully');
                                    new bootstrap.Modal(document.getElementById("editSubcategoryModal")).hide();
                                    setTimeout(function() {
                                        window.location.reload(true);
                                    }, 2000);
                                }
                            })
                            .catch(error => {
                                removeOverlay();
                                console.error('Error updating subcategory: ', error);
                                updateAndShowToast('danger', 'Failed to update subcategory. Please try again.');
                            });
                    });

                    // Delete subcategory button click
                    document.querySelectorAll(".delete-subcategory-btn").forEach(button => {
                        button.addEventListener("click", function() {
                            let subcategoryId = this.getAttribute("data-id");
                            if (confirm("Are you sure you want to delete this subcategory?")) {
                                createOverlay();

                                fetch("../backend/courses/subcategory_actions.php", {
                                        method: "POST",
                                        body: new URLSearchParams({
                                            action: "delete",
                                            id: subcategoryId
                                        }),
                                    }).then(response => response.json())
                                    .then(data => {
                                        if (data.error) {
                                            removeOverlay();
                                            updateAndShowToast('danger', data.error);
                                        } else {
                                            updateAndShowToast('success', 'Subcategory deleted successfully');
                                            setTimeout(function() {
                                                window.location.reload(true);
                                            }, 2000);
                                        }
                                    })
                                    .catch(error => {
                                        removeOverlay();
                                        console.error('Error deleting subcategory: ', error);
                                        updateAndShowToast('danger', 'Failed to delete subcategory. Please try again.');
                                    });
                            }
                        });
                    });
                }

                // Initialize subcategory form handlers
                if (document.getElementById("add_subcategory_name")) {
                    document.getElementById("add_subcategory_name").addEventListener("input", function() {
                        if (document.getElementById("add_subcategory_slug")) {
                            document.getElementById("add_subcategory_slug").value = generateSlug(this.value);
                        }
                    });
                }

                if (document.getElementById("addSubcategoryForm")) {
                    document.getElementById("addSubcategoryForm").addEventListener("submit", function(e) {
                        e.preventDefault();

                        const subcategoryName = document.getElementById("add_subcategory_name").value;
                        const subcategorySlug = document.getElementById("add_subcategory_slug").value;
                        const categoryId = document.getElementById("add_subcategory_category").value;

                        if (!subcategoryName || !categoryId) {
                            updateAndShowToast('danger', 'Please fill in all required fields');
                            return;
                        }

                        createOverlay();

                        $.ajax({
                            type: "POST",
                            url: "../backend/courses/add_subcategory.php",
                            data: {
                                name: subcategoryName,
                                slug: subcategorySlug,
                                category_id: categoryId
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.error) {
                                    removeOverlay();
                                    updateAndShowToast('danger', response.error);
                                } else {
                                    updateAndShowToast('success', response.success);
                                    $('#addSubcategoryModal').modal('hide');
                                    setTimeout(function() {
                                        window.location.reload(true);
                                    }, 2000);
                                }
                            },
                            error: function(xhr, status, error) {
                                removeOverlay();
                                updateAndShowToast('danger', 'Error adding subcategory: ' + error);
                            }
                        });
                    });
                }
            });
        </script>

    </div>
    <!-- End Content -->
</main>
<!-- ========== END MAIN CONTENT ========== -->


<?php include '../includes/department/footer.php'; ?>