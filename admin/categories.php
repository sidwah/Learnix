<?php include '../includes/admin-header.php'; ?>
<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main">
    <!-- Navbar -->
    <nav class="js-nav-scroller navbar navbar-expand-lg navbar-sidebar navbar-vertical navbar-light bg-white border-end" data-hs-nav-scroller-options='{
            "type": "vertical",
            "target": ".navbar-nav .active",
            "offset": 80
           }'>

        <?php include '../includes/admin-sidebar.php'; ?>
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
            // Show alert notification function
            function showAlert(type, message) {
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
                alertDiv.setAttribute('role', 'alert');
                alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
                // Position the alert
                alertDiv.style.position = 'fixed';
                alertDiv.style.top = '20px';
                alertDiv.style.left = '50%';
                alertDiv.style.transform = 'translateX(-50%)';
                alertDiv.style.zIndex = '9999';
                alertDiv.style.minWidth = '300px';
                alertDiv.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
                document.body.appendChild(alertDiv);
                // Auto-dismiss after 5 seconds
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.classList.remove('show');
                        setTimeout(() => {
                            if (alertDiv.parentNode) {
                                alertDiv.parentNode.removeChild(alertDiv);
                            }
                        }, 300);
                    }
                }, 5000);
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
                                showAlert('danger', response.error);
                            } else {
                                showAlert('success', response.success);
                                $('#addCategoryModal').modal('hide'); // Hide modal on success

                                // Keep overlay during page reload
                                setTimeout(function() {
                                    window.location.reload(true); // Refresh the page after success
                                }, 2000);
                            }
                        },
                        error: function(xhr, status, error) {
                            removeOverlay();
                            showAlert('danger', 'Error adding category: ' + error);
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

        <!-- JavaScript (AJAX) -->
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                loadCategories();
                // loadAddCategoryForm();

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

                function formatDate(dateStr) {
                    let date = new Date(dateStr);
                    return date.toLocaleDateString('en-US', {
                        month: 'long',
                        day: 'numeric',
                        year: 'numeric'
                    });
                }




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

                        let updateBtn = document.querySelector("#editCategoryForm button[type='submit']");
                        // updateBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Updating...`;
                        // updateBtn.disabled = true;
                        $('#loadingSpinner').css('display', 'block'); // Show spinner before the AJAX request
                        setTimeout(function() {
                            window.location.reload(true); // Refresh the page 2 seconds after the modal is hidden
                        }, 2000);


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
                                    alert(data.error); // Show error message if there is a problem
                                    $('#loadingSpinner').css('display', 'none'); // Hide spinner on error

                                } else {
                                    new bootstrap.Modal(document.getElementById("editCategoryModal")).hide();
                                    setTimeout(function() {
                                        window.location.reload(true); // Refresh the page 2 seconds after the modal is hidden
                                    }, 2000);
                                    location.reload(); // Refresh the page to show updated data
                                }
                                updateBtn.innerHTML = "Update Category";
                                updateBtn.disabled = false;
                            })
                            .catch(error => {
                                console.error('Error updating category: ', error);
                                alert('Failed to update category. Please try again.');
                                updateBtn.innerHTML = "Update Category";
                                updateBtn.disabled = false;
                            });
                    });

                    document.querySelectorAll(".delete-btn").forEach(button => {
                        button.addEventListener("click", function() {
                            let categoryId = this.getAttribute("data-id");
                            if (confirm("Are you sure you want to delete this category?")) {
                                let deleteBtn = this;
                                // deleteBtn.innerHTML = `<span class="spinner-border spinner-border-sm text-danger"></span>`;
                                // deleteBtn.disabled = true;
                                $('#loadingSpinner').css('display', 'block'); // Show spinner before the AJAX request
                                setTimeout(function() {
                                    window.location.reload(true); // Refresh the page 2 seconds after the modal is hidden
                                }, 2000);

                                fetch("../backend/courses/category_actions.php", {
                                    method: "POST",
                                    body: new URLSearchParams({
                                        action: "delete",
                                        id: categoryId
                                    }),
                                }).then(() => {
                                    loadCategories();
                                }).catch(error => {
                                    $('#loadingSpinner').css('display', 'none'); // Hide spinner on error

                                    console.error('Error deleting category: ', error);
                                    alert('Failed to delete category. Please try again.');
                                    deleteBtn.innerHTML = '<i class="bi bi-trash"></i>';
                                    deleteBtn.disabled = false;
                                });
                            }
                        });
                    });
                }

                function generateSlug(text) {
                    return text.toLowerCase().trim().replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "");
                }
            });
        </script>




    </div>
    <!-- End Content -->





</main>
<!-- ========== END MAIN CONTENT ========== -->

<?php include '../includes/admin-footer.php'; ?>