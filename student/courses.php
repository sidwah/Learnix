<?php include '../includes/student-header.php'; ?>

<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main">
 

    <!-- Filter Form -->
    <div class="container content-space-t-3 content-space-t-lg-2">
        <form>
            <div class="row gx-2">
                <div class="col-lg mb-2 mb-lg-0">
                    <!-- Form -->
                    <label for="searchPropertyFilterForm" class="visually-hidden form-label">Search courses</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-prepend input-group-text">
                            <i class="bi-search"></i>
                        </span>
                        <input type="text" class="form-control form-control-sm" id="search-input" placeholder="Search for courses, skills, or subjects" aria-label="Search courses">
                    </div>
                    <!-- End Form -->
                </div>
                <!-- End Col -->

                <div class="col-auto mb-2 mb-lg-0">
                    <!-- Dropdown -->
                    <div class="dropdown">
                        <a class="btn btn-white btn-sm dropdown-toggle" href="#" id="priceFilterFormDropdown" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">Price</a>

                        <div class="dropdown-menu dropdown-menu-end dropdown-card" aria-labelledby="priceFilterFormDropdown" style="min-width: 21rem;">
                            <!-- Card -->
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="row justify-content-center mt-5">
                                        <div class="col">
                                            <span class="d-block small mb-1">Min price:</span>
                                            <input type="text" class="form-control form-control-sm" id="rangeSliderExampleDouble4MinResult">
                                        </div>
                                        <!-- End Col -->

                                        <div class="col">
                                            <span class="d-block small mb-1">Max price:</span>
                                            <input type="text" class="form-control form-control-sm" id="rangeSliderExampleDouble4MaxResult">
                                        </div>
                                        <!-- End Col -->
                                    </div>
                                    <!-- End Row -->
                                </div>

                                <div class="card-footer border-top">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a class="btn btn-white btn-sm" href="#" id="clear-price-filter">Clear</a>
                                        <a class="btn btn-primary btn-sm" href="#" id="apply-price-filter">Apply</a>
                                    </div>
                                </div>
                            </div>
                            <!-- End Card -->
                        </div>
                    </div>
                    <!-- End Dropdown -->
                </div>
                <!-- End Col -->

                <div class="col-auto mb-2 mb-lg-0">
                    <!-- Dropdown -->
                    <div class="dropdown">
                        <a class="btn btn-white btn-sm dropdown-toggle" href="#" id="levelFilterFormDropdown" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">Course Level</a>

                        <div class="dropdown-menu dropdown-menu-end dropdown-card" aria-labelledby="levelFilterFormDropdown" style="min-width: 25rem;">
                            <!-- Card -->
                            <div class="card card-sm">
                                <div class="card-body">
                                    <!-- Radio Button Group -->
                                    <div class="btn-group btn-group-segment d-flex" role="group" aria-label="Course level radio button group">
                                        <input type="radio" class="btn-check flex-fill" name="levelBtnRadio" id="levelBtnRadioAll" autocomplete="off" checked>
                                        <label class="btn btn-sm" for="levelBtnRadioAll">All</label>

                                        <input type="radio" class="btn-check flex-fill" name="levelBtnRadio" id="levelBtnRadioBeginner" autocomplete="off">
                                        <label class="btn btn-sm" for="levelBtnRadioBeginner">Beginner</label>

                                        <input type="radio" class="btn-check flex-fill" name="levelBtnRadio" id="levelBtnRadioIntermediate" autocomplete="off">
                                        <label class="btn btn-sm" for="levelBtnRadioIntermediate">Intermediate</label>

                                        <input type="radio" class="btn-check flex-fill" name="levelBtnRadio" id="levelBtnRadioAdvanced" autocomplete="off">
                                        <label class="btn btn-sm" for="levelBtnRadioAdvanced">Advanced</label>
                                    </div>
                                    <!-- End Radio Button Group -->
                                </div>
                                <div class="card-footer border-top">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a class="btn btn-white btn-sm" href="#" id="clear-level-filter">Clear</a>
                                        <a class="btn btn-primary btn-sm" href="#" id="apply-level-filter">Apply</a>
                                    </div>
                                </div>
                            </div>
                            <!-- End Card -->
                        </div>
                    </div>
                    <!-- End Dropdown -->
                </div>
                <!-- End Col -->

                <div class="col-auto mb-2 mb-lg-0">
                    <!-- Dropdown -->
                    <div class="dropdown">
                        <a class="btn btn-white btn-sm dropdown-toggle" href="#" id="certificateFilterFormDropdown" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">Certificate</a>

                        <div class="dropdown-menu dropdown-menu-end dropdown-card" aria-labelledby="certificateFilterFormDropdown" style="min-width: 20rem;">
                            <!-- Card -->
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <!-- Check -->
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1" id="certificateEnabledCheckbox">
                                            <label class="form-check-label" for="certificateEnabledCheckbox">
                                                Certificate Enabled
                                                <span class="d-block small text-muted">Courses with certificates upon completion</span>
                                            </label>
                                        </div>
                                        <!-- End Check -->

                                        <!-- Check -->
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="0" id="noCertificateCheckbox">
                                            <label class="form-check-label" for="noCertificateCheckbox">
                                                No Certificate
                                                <span class="d-block small text-muted">Courses without certificates</span>
                                            </label>
                                        </div>
                                        <!-- End Check -->
                                    </div>
                                </div>

                                <div class="card-footer border-top">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a class="btn btn-white btn-sm" href="#" id="clear-certificate-filter">Clear</a>
                                        <a class="btn btn-primary btn-sm" href="#" id="apply-certificate-filter">Apply</a>
                                    </div>
                                </div>
                            </div>
                            <!-- End Card -->
                        </div>
                    </div>
                    <!-- End Dropdown -->
                </div>
                <!-- End Col -->

                <div class="col-auto mb-2 mb-lg-0">
                    <a class="btn btn-white btn-sm" href="#" data-bs-toggle="modal" data-bs-target="#allCategoriesModal">
                        <i class="bi-sliders me-2"></i> More
                    </a>
                </div>
                <!-- End Col -->
            </div>
            <!-- End Row -->
        </form>
    </div>
    <!-- End Filter Form -->

    <!-- Card Grid -->
    <div class="container content-space-t-1 content-space-b-2 content-space-b-lg-3">
        <!-- Heading -->
        <div class="mb-5">
            <div class="row align-items-center">
                <div class="col-sm mb-3 mb-sm-0">
                    <span class="d-block">1,200+ courses</span>
                    <h1 class="h2 mb-0">Explore our course catalog</h1>
                </div>
                <!-- End Col -->

                <div class="col-sm-auto">
                    <!-- Select -->
                    <select class="form-select form-select-sm" id="sort-select">
                        <option value="newest" selected>Most recent</option>
                        <option value="highest_rated">Highest rated</option>
                        <option value="lowest_price">Lowest price</option>
                        <option value="highest_price">Highest price</option>
                    </select>
                    <!-- End Select -->
                </div>
                <!-- End Col -->
            </div>
            <!-- End Row -->
        </div>
        <!-- End Heading -->

        <!-- Course Cards Container -->
        <div id="course-container" class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 mb-5">
            <!-- Course cards will be loaded here via AJAX -->
            <div class="col-12 text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading courses...</p>
            </div>
        </div>
        <!-- End Course Cards -->

        <!-- Pagination -->
        <div id="pagination-container" class="d-flex justify-content-center">
            <!-- Pagination will be loaded here via AJAX -->
        </div>
        <!-- End Pagination -->
    </div>
    <!-- End Card Grid -->

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
<script>
   document.addEventListener('DOMContentLoaded', function() {
    // Current filter state
    let currentFilters = {
        sort: 'newest',
        price: 'all',
        level: 'all',
        page: 1,
        search: '',
        subcategories: [],
        certificate: []
    };

    // Min and max price range
    let priceRange = {
        min: 0,
        max: 1000
    };

    // Load courses function
    function loadCourses() {
        // Show loading indicator
        document.getElementById('course-container').innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading courses...</p>
            </div>
        `;

        // Prepare data for the fetch request
        const data = new URLSearchParams();
        for (const key in currentFilters) {
            if (key === 'subcategories' || key === 'certificate') {
                data.append(key, JSON.stringify(currentFilters[key]));
            } else {
                data.append(key, currentFilters[key]);
            }
        }

        // Add price range if set
        if (priceRange.min > 0 || priceRange.max < 1000) {
            data.append('price_min', priceRange.min);
            data.append('price_max', priceRange.max);
        }

        // Make AJAX request
        fetch('../ajax/students/load_courses.php', {
                method: 'POST',
                body: data,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the course container with the HTML from the server
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
                                document.getElementById('course-container').scrollIntoView({
                                    behavior: 'smooth'
                                });
                            }
                        });
                    });
                } else {
                    document.getElementById('course-container').innerHTML = `
                        <div class="col-12 text-center p-5">
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
                    <div class="col-12 text-center p-5">
                        <i class="bi-exclamation-triangle display-4 text-danger mb-3"></i>
                        <h3>Something went wrong</h3>
                        <p>We couldn't load the courses. Please try again later.</p>
                    </div>
                `;
            });
    }

    // Load categories for the all categories modal
    function loadAllCategories() {
        fetch('../ajax/students/load_all_categories.php')
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

        // Get all checked subcategory checkboxes from the "all categories" modal
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

    // Update certificate filter
    function updateCertificateFilter() {
        currentFilters.certificate = [];

        if (document.getElementById('certificateEnabledCheckbox').checked) {
            currentFilters.certificate.push(1);
        }

        if (document.getElementById('noCertificateCheckbox').checked) {
            currentFilters.certificate.push(0);
        }
    }

    // Helper function to close a dropdown
    function closeDropdown(dropdownSelector) {
        const dropdownElement = document.querySelector(dropdownSelector);
        if (dropdownElement) {
            const dropdown = bootstrap.Dropdown.getInstance(dropdownElement);
            if (dropdown) {
                dropdown.hide();
            } else {
                // If the dropdown instance is not found, remove the 'show' class manually
                dropdownElement.classList.remove('show');
                
                // Also find and hide the dropdown menu
                const dropdownMenu = dropdownElement.nextElementSibling;
                if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                    dropdownMenu.classList.remove('show');
                }
            }
        }
    }

    // Event listeners
    
    // Sort dropdown
    document.getElementById('sort-select').addEventListener('change', function() {
        currentFilters.sort = this.value;
        currentFilters.page = 1; // Reset to first page
        loadCourses();
    });

    // Search input
    document.getElementById('search-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            currentFilters.search = this.value;
            currentFilters.page = 1;
            loadCourses();
        }
    });

    // Price filter handlers
    document.getElementById('apply-price-filter').addEventListener('click', function(e) {
        e.preventDefault();
        priceRange.min = parseInt(document.getElementById('rangeSliderExampleDouble4MinResult').value) || 0;
        priceRange.max = parseInt(document.getElementById('rangeSliderExampleDouble4MaxResult').value) || 1000;
        currentFilters.page = 1;

        // Close the dropdown automatically after applying
        closeDropdown('#priceFilterFormDropdown');

        loadCourses();
    });

    document.getElementById('clear-price-filter').addEventListener('click', function(e) {
        e.preventDefault();
        priceRange.min = 0;
        priceRange.max = 1000;
        document.getElementById('rangeSliderExampleDouble4MinResult').value = 0;
        document.getElementById('rangeSliderExampleDouble4MaxResult').value = 1000;
        currentFilters.page = 1;

        // Close the dropdown automatically after clearing
        closeDropdown('#priceFilterFormDropdown');

        loadCourses();
    });

    // Level filter handlers
    document.getElementById('apply-level-filter').addEventListener('click', function(e) {
        e.preventDefault();

        // Get the selected level
        if (document.getElementById('levelBtnRadioAll').checked) {
            currentFilters.level = 'all';
        } else if (document.getElementById('levelBtnRadioBeginner').checked) {
            currentFilters.level = 'Beginner';
        } else if (document.getElementById('levelBtnRadioIntermediate').checked) {
            currentFilters.level = 'Intermediate';
        } else if (document.getElementById('levelBtnRadioAdvanced').checked) {
            currentFilters.level = 'Advanced';
        }

        currentFilters.page = 1;

        // Close the dropdown automatically after applying
        closeDropdown('#levelFilterFormDropdown');

        loadCourses();
    });

    document.getElementById('clear-level-filter').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('levelBtnRadioAll').checked = true;
        currentFilters.level = 'all';
        currentFilters.page = 1;

        // Close the dropdown automatically after clearing
        closeDropdown('#levelFilterFormDropdown');

        loadCourses();
    });

    // Certificate filter handlers
    document.getElementById('apply-certificate-filter').addEventListener('click', function(e) {
        e.preventDefault();
        updateCertificateFilter();
        currentFilters.page = 1;

        // Close the dropdown automatically after applying
        closeDropdown('#certificateFilterFormDropdown');

        loadCourses();
    });

    document.getElementById('clear-certificate-filter').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('certificateEnabledCheckbox').checked = false;
        document.getElementById('noCertificateCheckbox').checked = false;
        currentFilters.certificate = [];
        currentFilters.page = 1;

        // Close the dropdown automatically after clearing
        closeDropdown('#certificateFilterFormDropdown');

        loadCourses();
    });

    // Apply subcategories button in modal
    document.getElementById('apply-subcategories').addEventListener('click', function() {
        // Update filters from modal checkboxes
        document.querySelectorAll('#categoryModalBody .form-check-input').forEach(checkbox => {
            const subcatId = parseInt(checkbox.getAttribute('data-subcategory'));

            // Handle subcategories selection
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
        var categoryModal = bootstrap.Modal.getInstance(document.getElementById('categoryModal'));
        if (categoryModal) {
            categoryModal.hide();
        }

        // Reset to page 1 and load courses
        currentFilters.page = 1;
        loadCourses();
    });

    // Apply all filters button
    document.getElementById('apply-all-filters').addEventListener('click', function() {
        updateCategoryFilters();
        var allCategoriesModal = bootstrap.Modal.getInstance(document.getElementById('allCategoriesModal'));
        if (allCategoriesModal) {
            allCategoriesModal.hide();
        }
        currentFilters.page = 1;
        loadCourses();
    });

    // Categories modal open
    document.querySelector('[data-bs-target="#allCategoriesModal"]').addEventListener('click', function() {
        loadAllCategories();
    });

    // Initial load
    loadCourses();
});
</script>
<?php include '../includes/student-footer.php'; ?>