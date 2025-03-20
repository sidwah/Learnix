<?php include '../includes/account-header.php'; ?>

<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main">
    <!-- Banner -->
    <div class="container content-space-t-1">
        <div class="bg-primary rounded-2" style="background: url(../assets/svg/illustrations/master-adobe-ai-book.svg) right bottom no-repeat;">
            <div class="w-lg-50 py-8 px-6">
                <h1 class="display-4 text-white">Course catalog</h1>
                <p class="lead text-white mb-0">Learnix includes over <span class="fw-semi-bold">1,200</span> courses to help you excel.</p>
            </div>
        </div>
    </div>
    <!-- End Banner -->

    <!-- Course Catalog Content -->
    <div class="container content-space-2 content-space-b-lg-3">
        <!-- Search Bar -->
        <div class="container mt-4">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="input-group mb-4">
                        <input type="text" class="form-control form-control-lg" id="search-input" placeholder="Search for courses, skills, or subjects" aria-label="Search for courses">
                        <button class="btn btn-primary" type="button" id="search-button">
                            <i class="bi-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Search Bar -->
        
        <div class="row">
            <!-- Left Sidebar: Categories -->
            <div class="col-lg-3 mb-5 mb-lg-0">
                <!-- Navbar -->
                <div class="navbar-expand-lg">
                    <!-- Navbar Toggle for Mobile -->
                    <div class="d-grid d-lg-none mb-3">
                        <button type="button" class="navbar-toggler btn btn-white" data-bs-toggle="collapse" data-bs-target="#categoryNavMenu" aria-label="Toggle categories">
                            <span class="d-flex justify-content-between align-items-center">
                                <span class="text-dark">Categories</span>
                                <i class="bi-chevron-down"></i>
                            </span>
                        </button>
                    </div>
                    <!-- End Navbar Toggle -->

                    <!-- Categories Navigation -->
                    <div id="categoryNavMenu" class="collapse navbar-collapse">
                        <div class="d-grid gap-4 flex-grow-1" id="category-sidebar">
                            <!-- Loading indicator -->
                            <div class="text-center py-4">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="small text-muted mt-1 mb-0">Loading categories...</p>
                            </div>
                        </div>
                    </div>
                    <!-- End Categories Navigation -->
                    
                    <!-- View All Categories -->
                    <!-- <div class="mt-2 pt-3 border-top">
                        <a href="#" class="btn btn-soft-primary w-100" data-bs-toggle="modal" data-bs-target="#allCategoriesModal">
                            <i class="bi-grid me-1"></i> View All Categories
                        </a>
                    </div> -->
                </div>
                <!-- End Navbar -->
            </div>
            <!-- End Left Sidebar -->

            <!-- Course Listings Column -->
            <div class="col-lg-9">
                <!-- Filter Bar -->
                <div class="border-bottom pb-3 mb-5">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="me-2">
                            <select class="form-select form-select-sm" id="sort-select">
                                <option value="newest">Newest</option>
                                <option value="highest_rated">Highest rated</option>
                                <option value="lowest_price">Lowest price</option>
                                <option value="highest_price">Highest price</option>
                            </select>
                        </div>
                        <div class="me-2">
                            <select class="form-select form-select-sm" id="price-select">
                                <option value="all">All Prices</option>
                                <option value="paid">Paid</option>
                                <option value="free">Free</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-select form-select-sm" id="level-select">
                                <option value="all">All Levels</option>
                                <option value="Beginner">Beginner</option>
                                <option value="Intermediate">Intermediate</option>
                                <option value="Advanced">Advanced</option>
                            </select>
                        </div>
                    </div>
                </div>
                <!-- End Filter Bar -->

                <!-- Course Cards Container -->
                <div id="course-container" class="d-grid gap-5 mb-10">
                    <!-- Course cards will be loaded here via AJAX -->
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading courses...</p>
                    </div>
                </div>
                <!-- End Course Cards -->

                <!-- Pagination -->
                <div id="pagination-container" class="d-flex justify-content-between align-items-center">
                    <!-- Pagination will be loaded here via AJAX -->
                </div>
                <!-- End Pagination -->
            </div>
            <!-- End Course Listings Column -->
        </div>
    </div>
    <!-- End Course Catalog Content -->
<!-- FAQ Section -->
<div class="container content-space-b-2">
    <div class="text-center bg-img-start py-6" style="background: url(../assets/svg/components/shape-6.svg) center no-repeat;">
        <div class="mb-5">
            <h2>Still curious?</h2>
        </div>
        <div class="w-lg-65 mx-lg-auto">
            <!-- Accordion -->
            <div class="accordion accordion-flush accordion-lg" id="accordionFAQ">
                <!-- Accordion Item 1 -->
                <div class="accordion-item">
                    <div class="accordion-header" id="headingCuriousOne">
                        <a class="accordion-button" role="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            What types of free courses are available online?
                        </a>
                    </div>
                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingCuriousOne" data-bs-parent="#accordionFAQ">
                        <div class="accordion-body">
                            There are a wide variety of free courses available online across numerous fields and subjects. These include courses in programming, digital marketing, business management, generative AI, and data science. Learners can also find free courses in creative fields such as graphic design, music production, and writing. Additionally, there are free courses covering personal development topics like time management, communication skills, and emotional intelligence. Many of these courses are offered by prestigious universities and institutions, providing high-quality education at no cost.
                        </div>
                    </div>
                </div>
                <!-- End Accordion Item 1 -->

                <!-- Accordion Item 2 -->
                <div class="accordion-item">
                    <div class="accordion-header" id="headingCuriousTwo">
                        <a class="accordion-button collapsed" role="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            How can I access these free courses?
                        </a>
                    </div>
                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingCuriousTwo" data-bs-parent="#accordionFAQ">
                        <div class="accordion-body">
                            Free courses can typically be accessed through various online platforms and educational websites. Most platforms allow you to enroll without any fees, with optional paid certificates available for those who require formal recognition. Just sign up for an account, search for your subject of interest, and start learning at your own pace.
                        </div>
                    </div>
                </div>
                <!-- End Accordion Item 2 -->

                <!-- Accordion Item 3 -->
                <div class="accordion-item">
                    <div class="accordion-header" id="headingCuriousThree">
                        <a class="accordion-button collapsed" role="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            Are there any drawbacks to free online courses?
                        </a>
                    </div>
                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingCuriousThree" data-bs-parent="#accordionFAQ">
                        <div class="accordion-body">
                            While free online courses offer significant benefits such as flexibility and no cost, they may sometimes lack the comprehensive support, interactive feedback, and accreditation that traditional classroom settings provide. Depending on your learning goals, you might also consider supplementing free courses with other resources to ensure a complete educational experience.
                        </div>
                    </div>
                </div>
                <!-- End Accordion Item 3 -->

            </div>
            <!-- End Accordion -->
        </div>
    </div>
</div>
<!-- End FAQ Section -->

</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- Category Modal - Will be dynamically populated -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalLabel">Category Subcategories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="categoryModalBody">
                <!-- Will be populated via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="apply-subcategories">Apply</button>
            </div>
        </div>
    </div>
</div>

<!-- All Categories Modal -->
<div class="modal fade" id="allCategoriesModal" tabindex="-1" aria-labelledby="allCategoriesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="allCategoriesModalLabel">All Categories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="category-search" placeholder="Search categories and subcategories">
                </div>

                <div class="row" id="all-categories-container">
                    <!-- Will be populated via AJAX -->
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading categories...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="apply-all-filters">Apply Filters</button>
            </div>
        </div>
    </div>
</div>

<!-- AJAX and Filtering Logic -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Current filter state
        let currentFilters = {
            sort: 'newest',
            price: 'all',
            level: 'all',
            page: 1,
            search: '',
            subcategories: []
        };
        
        // Load top categories for sidebar
        function loadSidebarCategories() {
            fetch('../ajax/load_top_categories.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('category-sidebar').innerHTML = data.html;
                    
                    // Add event listeners to category checkboxes
                    document.querySelectorAll('.category-checkbox').forEach(checkbox => {
                        checkbox.addEventListener('change', function() {
                            updateCategoryFilters();
                            currentFilters.page = 1;
                            loadCourses();
                        });
                    });
                    
                    // Add event listeners to "See more" buttons
                    document.querySelectorAll('.see-more-btn').forEach(button => {
                        button.addEventListener('click', function() {
                            const categoryId = this.getAttribute('data-category-id');
                            const categoryName = this.getAttribute('data-category-name');
                            
                            // Update modal title
                            document.getElementById('categoryModalLabel').textContent = categoryName + ' Subcategories';
                            
                            // Show loading in modal
                            document.getElementById('categoryModalBody').innerHTML = `
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Loading subcategories...</p>
                                </div>
                            `;
                            
                            // Show the modal
                            new bootstrap.Modal(document.getElementById('categoryModal')).show();
                            
                            // Load subcategories via AJAX
                            fetch('../ajax/load_subcategories.php?category_id=' + categoryId)
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    document.getElementById('categoryModalBody').innerHTML = data.subcategories;
                                    
                                    // Check the checkboxes that are already selected
                                    currentFilters.subcategories.forEach(subId => {
                                        const checkbox = document.getElementById('modal-sub-' + subId);
                                        if (checkbox) {
                                            checkbox.checked = true;
                                        }
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error loading subcategories:', error);
                                document.getElementById('categoryModalBody').innerHTML = `
                                    <div class="alert alert-danger" role="alert">
                                        Failed to load subcategories. Please try again.
                                    </div>
                                `;
                            });
                        });
                    });
                } else {
                    document.getElementById('category-sidebar').innerHTML = `
                        <div class="alert alert-info">
                            <i class="bi-exclamation-circle me-2"></i>
                            No categories found
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading categories:', error);
                document.getElementById('category-sidebar').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi-exclamation-triangle me-2"></i>
                        Failed to load categories
                    </div>
                `;
            });
        }
        
        // Load courses function
        function loadCourses() {
            // Show loading indicator
            document.getElementById('course-container').innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading courses...</p>
                </div>
            `;
            
            // Prepare data for the fetch request
            const data = new URLSearchParams();
            for (const key in currentFilters) {
                if (key === 'subcategories') {
                    data.append(key, JSON.stringify(currentFilters[key]));
                } else {
                    data.append(key, currentFilters[key]);
                }
            }
            
            // Make AJAX request
            fetch('../ajax/load_courses.php', {
                method: 'POST',
                body: data,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update course container
                    document.getElementById('course-container').innerHTML = data.courses;
                    
                    // Update pagination
                    document.getElementById('pagination-container').innerHTML = data.pagination;
                    
                    // Add event listeners to pagination links
                    document.querySelectorAll('.page-link').forEach(link => {
                        link.addEventListener('click', function(e) {
                            e.preventDefault();
                            const page = this.getAttribute('data-page');
                            if (page) {
                                currentFilters.page = page;
                                loadCourses();
                                // Scroll to top of course container
                                document.getElementById('course-container').scrollIntoView({ behavior: 'smooth' });
                            }
                        });
                    });
                } else {
                    document.getElementById('course-container').innerHTML = `
                        <div class="text-center p-5">
                            <i class="bi-exclamation-triangle display-4 text-warning mb-3"></i>
                            <h3>Error Loading Courses</h3>
                            <p>${data.message || 'Please try again later.'}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading courses:', error);
                document.getElementById('course-container').innerHTML = `
                    <div class="text-center p-5">
                        <i class="bi-exclamation-triangle display-4 text-danger mb-3"></i>
                        <h3>Something went wrong</h3>
                        <p>We couldn't load the courses. Please try again later.</p>
                    </div>
                `;
            });
        }
        
        // Load categories for the all categories modal
        function loadAllCategories() {
            fetch('../ajax/load_all_categories.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('all-categories-container').innerHTML = data.categories;
                    
                    // Add event listeners to category checkboxes
                    document.querySelectorAll('#all-categories-container .form-check-input').forEach(checkbox => {
                        checkbox.addEventListener('change', function() {
                            // Check if the checkbox is checked
                            if (this.checked) {
                                // If it's a main category checkbox, check/uncheck all its subcategories
                                if (this.classList.contains('category-main-checkbox')) {
                                    const categoryId = this.getAttribute('data-category');
                                    document.querySelectorAll(`.subcategory-checkbox[data-category="${categoryId}"]`).forEach(subCheckbox => {
                                        subCheckbox.checked = this.checked;
                                    });
                                }
                            }
                        });
                    });
                    
                    // Add search functionality
                    document.getElementById('category-search').addEventListener('input', function() {
                        const searchTerm = this.value.toLowerCase();
                        
                        // Search in category cards
                        document.querySelectorAll('#all-categories-container .category-card').forEach(card => {
                            const categoryName = card.querySelector('.card-header').textContent.toLowerCase();
                            const subcategoryNames = Array.from(card.querySelectorAll('.form-check-label')).map(label => label.textContent.toLowerCase());
                            
                            // Check if either category name or any subcategory names match the search
                            const categoryMatches = categoryName.includes(searchTerm);
                            const subcategoryMatches = subcategoryNames.some(name => name.includes(searchTerm));
                            
                            if (categoryMatches || subcategoryMatches) {
                                card.style.display = '';
                                
                                // If searching, show/hide individual subcategories
                                if (searchTerm) {
                                    card.querySelectorAll('.form-check').forEach(check => {
                                        const checkLabel = check.querySelector('.form-check-label').textContent.toLowerCase();
                                        if (checkLabel.includes(searchTerm) || categoryMatches) {
                                            check.style.display = '';
                                        } else {
                                            check.style.display = 'none';
                                        }
                                    });
                                } else {
                                    // If not searching, show all subcategories
                                    card.querySelectorAll('.form-check').forEach(check => {
                                        check.style.display = '';
                                    });
                                }
                            } else {
                                card.style.display = 'none';
                            }
                        });
                    });
                }
            })
            .catch(error => {
                console.error('Error loading categories:', error);
                document.getElementById('all-categories-container').innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-danger">
                            <i class="bi-exclamation-triangle me-2"></i>
                            Failed to load categories. Please try again.
                        </div>
                    </div>
                `;
            });
        }
        
        // Update subcategory filters when checkboxes change
        function updateCategoryFilters() {
            currentFilters.subcategories = [];
            
            // Get all checked subcategory checkboxes
            document.querySelectorAll('.category-checkbox:checked').forEach(checkbox => {
                currentFilters.subcategories.push(parseInt(checkbox.getAttribute('data-subcategory')));
            });
            
            // Also check the "all categories" modal
            document.querySelectorAll('#all-categories-container .subcategory-checkbox:checked').forEach(checkbox => {
                const subcatId = parseInt(checkbox.getAttribute('data-subcategory'));
                if (!currentFilters.subcategories.includes(subcatId)) {
                    currentFilters.subcategories.push(subcatId);
                }
            });
            
            // Also check the category modal
            document.querySelectorAll('#categoryModalBody .form-check-input:checked').forEach(checkbox => {
                const subcatId = parseInt(checkbox.getAttribute('data-subcategory'));
                if (!currentFilters.subcategories.includes(subcatId)) {
                    currentFilters.subcategories.push(subcatId);
                }
            });
        }
        
        // Event listeners for filters
        document.getElementById('sort-select').addEventListener('change', function() {
            currentFilters.sort = this.value;
            currentFilters.page = 1; // Reset to first page
            loadCourses();
        });
        
        document.getElementById('price-select').addEventListener('change', function() {
            currentFilters.price = this.value;
            currentFilters.page = 1;
            loadCourses();
        });
        
        document.getElementById('level-select').addEventListener('change', function() {
            currentFilters.level = this.value;
            currentFilters.page = 1;
            loadCourses();
        });
        
        // Search button event listener
        document.getElementById('search-button').addEventListener('click', function() {
            currentFilters.search = document.getElementById('search-input').value;
            currentFilters.page = 1;
            loadCourses();
        });
        
        // Search input enter key event
        document.getElementById('search-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                currentFilters.search = this.value;
                currentFilters.page = 1;
                loadCourses();
            }
        });
        
        // Apply subcategories button in modal
        document.getElementById('apply-subcategories').addEventListener('click', function() {
            // Update filters from modal checkboxes
            document.querySelectorAll('#categoryModalBody .form-check-input').forEach(checkbox => {
                const subcatId = parseInt(checkbox.getAttribute('data-subcategory'));
                
                // Find equivalent checkbox in sidebar
                const sidebarCheckbox = document.querySelector(`.category-checkbox[data-subcategory="${subcatId}"]`);
                
                if (sidebarCheckbox) {
                    sidebarCheckbox.checked = checkbox.checked;
                }
                
                // Handle subcategories that aren't in the sidebar
                if (checkbox.checked && !currentFilters.subcategories.includes(subcatId)) {
                    currentFilters.subcategories.push(subcatId);
                } else if (!checkbox.checked) {
                    const index = currentFilters.subcategories.indexOf(subcatId);
                    if (index > -1) {
                        currentFilters.subcategories.splice(index, 1);
                    }
                }
            });
            
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('categoryModal')).hide();
            
            // Reset to page 1 and load courses
            currentFilters.page = 1;
            loadCourses();
        });
        
        // Apply all filters button
        document.getElementById('apply-all-filters').addEventListener('click', function() {
            updateCategoryFilters();
            bootstrap.Modal.getInstance(document.getElementById('allCategoriesModal')).hide();
            currentFilters.page = 1;
            loadCourses();
        });
        
        // Initial load
        loadSidebarCategories();
        loadCourses();
        loadAllCategories();
    });
</script>

<?php include '../includes/student-footer.php'; ?>