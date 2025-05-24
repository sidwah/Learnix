<?php include '../includes/department/header.php'; ?>
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
    <div class="navbar-sidebar-aside-content content-space-1 content-space-md-2 px-lg-5 px-xl-5">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Frequently Asked Questions</h1>
            <div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFAQModal">
                    <i class="bi-plus-circle me-1"></i> Add New FAQ
                </button>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Nav Scroller -->
        <div class="js-nav-scroller hs-nav-scroller-horizontal mb-5">
            <span class="hs-nav-scroller-arrow-prev" style="display: none;">
                <a class="hs-nav-scroller-arrow-link" href="javascript:;">
                    <i class="bi-chevron-left"></i>
                </a>
            </span>

            <span class="hs-nav-scroller-arrow-next" style="display: none;">
                <a class="hs-nav-scroller-arrow-link" href="javascript:;">
                    <i class="bi-chevron-right"></i>
                </a>
            </span>

            <ul class="nav nav-tabs align-items-center">
                <li class="nav-item">
                    <a class="nav-link active" href="#">All FAQs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">For Students</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">For Instructors</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Technical Issues</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Account Management</a>
                </li>
            </ul>
        </div>
        <!-- End Nav Scroller -->

        <!-- Search -->
        <div class="card mb-4">
            <div class="card-body">
                <form>
                    <div class="input-group input-group-merge">
                        <input type="text" class="form-control" placeholder="Search FAQs..." aria-label="Search FAQs">
                        <button type="button" class="input-group-append input-group-text">
                            <i class="bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!-- End Search -->

        <!-- FAQ Accordion -->
        <div class="accordion" id="faqAccordion">
            <!-- Students FAQ Section -->
            <div class="card card-lg mb-3">
                <div class="card-header bg-light">
                    <h4 class="card-header-title">
                        <i class="bi-people-fill text-primary me-2"></i> For Students
                    </h4>
                </div>
                <div class="card-body p-0">
                    <div class="accordion" id="studentsAccordion">
                        <!-- Student FAQ Item 1 -->
                        <div class="accordion-item">
                            <div class="accordion-header" id="studentHeadingOne">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#studentCollapseOne" aria-expanded="true" aria-controls="studentCollapseOne">
                                    How do I enroll in a course?
                                </button>
                            </div>
                            <div id="studentCollapseOne" class="accordion-collapse collapse show" aria-labelledby="studentHeadingOne" data-bs-parent="#studentsAccordion">
                                <div class="accordion-body">
                                    <p>To enroll in a course:</p>
                                    <ol>
                                        <li>Browse our course catalog and select the course you're interested in</li>
                                        <li>Click the "Enroll Now" button on the course page</li>
                                        <li>Complete the payment process if the course isn't free</li>
                                        <li>You'll receive immediate access to the course materials</li>
                                    </ol>
                                    <div class="d-flex justify-content-end">
                                        <button class="btn btn-sm btn-outline-secondary me-2" data-bs-toggle="modal" data-bs-target="#editFAQModal">
                                            <i class="bi-pencil"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="bi-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Student FAQ Item 1 -->

                        <!-- Student FAQ Item 2 -->
                        <div class="accordion-item">
                            <div class="accordion-header" id="studentHeadingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#studentCollapseTwo" aria-expanded="false" aria-controls="studentCollapseTwo">
                                    Can I get a refund if I'm not satisfied with a course?
                                </button>
                            </div>
                            <div id="studentCollapseTwo" class="accordion-collapse collapse" aria-labelledby="studentHeadingTwo" data-bs-parent="#studentsAccordion">
                                <div class="accordion-body">
                                    <p>We offer a 30-day money-back guarantee for all courses. To request a refund:</p>
                                    <ol>
                                        <li>Go to your "My Courses" page</li>
                                        <li>Find the course you want a refund for</li>
                                        <li>Click "Request Refund" and follow the instructions</li>
                                    </ol>
                                    <p>Refunds are typically processed within 5-7 business days.</p>
                                    <div class="d-flex justify-content-end">
                                        <button class="btn btn-sm btn-outline-secondary me-2" data-bs-toggle="modal" data-bs-target="#editFAQModal">
                                            <i class="bi-pencil"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="bi-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Student FAQ Item 2 -->

                        <!-- Student FAQ Item 3 -->
                        <div class="accordion-item">
                            <div class="accordion-header" id="studentHeadingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#studentCollapseThree" aria-expanded="false" aria-controls="studentCollapseThree">
                                    How do I access my course after enrollment?
                                </button>
                            </div>
                            <div id="studentCollapseThree" class="accordion-collapse collapse" aria-labelledby="studentHeadingThree" data-bs-parent="#studentsAccordion">
                                <div class="accordion-body">
                                    <p>After enrolling in a course, you can access it anytime by:</p>
                                    <ol>
                                        <li>Logging into your account</li>
                                        <li>Going to "My Courses" in the dashboard</li>
                                        <li>Clicking on the course you want to access</li>
                                    </ol>
                                    <p>Your courses remain accessible indefinitely after purchase.</p>
                                    <div class="d-flex justify-content-end">
                                        <button class="btn btn-sm btn-outline-secondary me-2" data-bs-toggle="modal" data-bs-target="#editFAQModal">
                                            <i class="bi-pencil"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="bi-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Student FAQ Item 3 -->
                    </div>
                </div>
            </div>
            <!-- End Students FAQ Section -->

            <!-- Instructors FAQ Section -->
            <div class="card card-lg mb-3">
                <div class="card-header bg-light">
                    <h4 class="card-header-title">
                        <i class="bi-person-badge text-warning me-2"></i> For Instructors
                    </h4>
                </div>
                <div class="card-body p-0">
                    <div class="accordion" id="instructorsAccordion">
                        <!-- Instructor FAQ Item 1 -->
                        <div class="accordion-item">
                            <div class="accordion-header" id="instructorHeadingOne">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#instructorCollapseOne" aria-expanded="true" aria-controls="instructorCollapseOne">
                                    How do I create and publish a course?
                                </button>
                            </div>
                            <div id="instructorCollapseOne" class="accordion-collapse collapse show" aria-labelledby="instructorHeadingOne" data-bs-parent="#instructorsAccordion">
                                <div class="accordion-body">
                                    <p>To create and publish a course:</p>
                                    <ol>
                                        <li>Go to your Instructor Dashboard</li>
                                        <li>Click "Create New Course"</li>
                                        <li>Fill out the course details and curriculum</li>
                                        <li>Upload your course materials (videos, PDFs, etc.)</li>
                                        <li>Submit your course for review</li>
                                        <li>Once approved, set your pricing and publish</li>
                                    </ol>
                                    <div class="d-flex justify-content-end">
                                        <button class="btn btn-sm btn-outline-secondary me-2" data-bs-toggle="modal" data-bs-target="#editFAQModal">
                                            <i class="bi-pencil"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="bi-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Instructor FAQ Item 1 -->

                        <!-- Instructor FAQ Item 2 -->
                        <div class="accordion-item">
                            <div class="accordion-header" id="instructorHeadingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#instructorCollapseTwo" aria-expanded="false" aria-controls="instructorCollapseTwo">
                                    How are payments processed for my courses?
                                </button>
                            </div>
                            <div id="instructorCollapseTwo" class="accordion-collapse collapse" aria-labelledby="instructorHeadingTwo" data-bs-parent="#instructorsAccordion">
                                <div class="accordion-body">
                                    <p>Payments are processed as follows:</p>
                                    <ul>
                                        <li>We handle all payment processing and security</li>
                                        <li>You receive 70% of the course sale price</li>
                                        <li>Payments are made monthly via PayPal or bank transfer</li>
                                        <li>Minimum payout threshold is $100</li>
                                    </ul>
                                    <p>You can view your earnings in real-time in your Instructor Dashboard.</p>
                                    <div class="d-flex justify-content-end">
                                        <button class="btn btn-sm btn-outline-secondary me-2" data-bs-toggle="modal" data-bs-target="#editFAQModal">
                                            <i class="bi-pencil"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="bi-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Instructor FAQ Item 2 -->

                        <!-- Instructor FAQ Item 3 -->
                        <div class="accordion-item">
                            <div class="accordion-header" id="instructorHeadingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#instructorCollapseThree" aria-expanded="false" aria-controls="instructorCollapseThree">
                                    Can I update my course after it's published?
                                </button>
                            </div>
                            <div id="instructorCollapseThree" class="accordion-collapse collapse" aria-labelledby="instructorHeadingThree" data-bs-parent="#instructorsAccordion">
                                <div class="accordion-body">
                                    <p>Yes, you can update your course at any time:</p>
                                    <ol>
                                        <li>Go to your Instructor Dashboard</li>
                                        <li>Select the course you want to edit</li>
                                        <li>Make your changes to content, curriculum, or settings</li>
                                        <li>Save your changes</li>
                                    </ol>
                                    <p>Note: Major changes may require re-approval by our team.</p>
                                    <div class="d-flex justify-content-end">
                                        <button class="btn btn-sm btn-outline-secondary me-2" data-bs-toggle="modal" data-bs-target="#editFAQModal">
                                            <i class="bi-pencil"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="bi-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Instructor FAQ Item 3 -->
                    </div>
                </div>
            </div>
            <!-- End Instructors FAQ Section -->

            <!-- Technical Issues Section -->
            <div class="card card-lg mb-3">
                <div class="card-header bg-light">
                    <h4 class="card-header-title">
                        <i class="bi-tools text-danger me-2"></i> Technical Issues
                    </h4>
                </div>
                <div class="card-body p-0">
                    <div class="accordion" id="technicalAccordion">
                        <!-- Technical FAQ Item 1 -->
                        <div class="accordion-item">
                            <div class="accordion-header" id="technicalHeadingOne">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#technicalCollapseOne" aria-expanded="true" aria-controls="technicalCollapseOne">
                                    What should I do if a video won't play?
                                </button>
                            </div>
                            <div id="technicalCollapseOne" class="accordion-collapse collapse show" aria-labelledby="technicalHeadingOne" data-bs-parent="#technicalAccordion">
                                <div class="accordion-body">
                                    <p>If you're having trouble playing videos:</p>
                                    <ol>
                                        <li>Check your internet connection</li>
                                        <li>Try refreshing the page</li>
                                        <li>Clear your browser cache and cookies</li>
                                        <li>Try using a different browser (we recommend Chrome or Firefox)</li>
                                        <li>Make sure your browser is up to date</li>
                                    </ol>
                                    <p>If the issue persists, contact our support team with details about your device and browser.</p>
                                    <div class="d-flex justify-content-end">
                                        <button class="btn btn-sm btn-outline-secondary me-2" data-bs-toggle="modal" data-bs-target="#editFAQModal">
                                            <i class="bi-pencil"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="bi-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Technical FAQ Item 1 -->
                    </div>
                </div>
            </div>
            <!-- End Technical Issues Section -->
        </div>
        <!-- End FAQ Accordion -->
    </div>
    <!-- End Content -->

    <!-- Add FAQ Modal -->
    <div class="modal fade" id="addFAQModal" tabindex="-1" role="dialog" aria-labelledby="addFAQModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addFAQModalLabel">Add New FAQ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addFAQForm">
                        <div class="mb-4">
                            <label for="faqCategory" class="form-label">Category</label>
                            <select class="form-select" id="faqCategory" required>
                                <option value="" selected disabled>Select category</option>
                                <option value="students">For Students</option>
                                <option value="instructors">For Instructors</option>
                                <option value="technical">Technical Issues</option>
                                <option value="account">Account Management</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="faqQuestion" class="form-label">Question</label>
                            <input type="text" class="form-control" id="faqQuestion" placeholder="Enter the question" required>
                        </div>
                        <div class="mb-4">
                            <label for="faqAnswer" class="form-label">Answer</label>
                            <textarea class="form-control" id="faqAnswer" rows="5" placeholder="Enter the detailed answer" required></textarea>
                            <small class="form-text">You can use HTML formatting in your answer.</small>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Status</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="faqStatus" id="faqActive" value="active" checked>
                                <label class="form-check-label" for="faqActive">Active</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="faqStatus" id="faqDraft" value="draft">
                                <label class="form-check-label" for="faqDraft">Draft</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveFAQ">Save FAQ</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Add FAQ Modal -->

    <!-- Edit FAQ Modal -->
    <div class="modal fade" id="editFAQModal" tabindex="-1" role="dialog" aria-labelledby="editFAQModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editFAQModalLabel">Edit FAQ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editFAQForm">
                        <div class="mb-4">
                            <label for="editFaqCategory" class="form-label">Category</label>
                            <select class="form-select" id="editFaqCategory" required>
                                <option value="students" selected>For Students</option>
                                <option value="instructors">For Instructors</option>
                                <option value="technical">Technical Issues</option>
                                <option value="account">Account Management</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="editFaqQuestion" class="form-label">Question</label>
                            <input type="text" class="form-control" id="editFaqQuestion" value="How do I enroll in a course?" required>
                        </div>
                        <div class="mb-4">
                            <label for="editFaqAnswer" class="form-label">Answer</label>
                            <textarea class="form-control" id="editFaqAnswer" rows="5" required>To enroll in a course:
1. Browse our course catalog and select the course you're interested in
2. Click the "Enroll Now" button on the course page
3. Complete the payment process if the course isn't free
4. You'll receive immediate access to the course materials</textarea>
                            <small class="form-text">You can use HTML formatting in your answer.</small>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Status</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="editFaqStatus" id="editFaqActive" value="active" checked>
                                <label class="form-check-label" for="editFaqActive">Active</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="editFaqStatus" id="editFaqDraft" value="draft">
                                <label class="form-check-label" for="editFaqDraft">Draft</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateFAQ">Update FAQ</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Edit FAQ Modal -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<?php include '../includes/department/footer.php'; ?>

<!-- JS Implementing Plugins -->
<script src="../assets/js/vendor.min.js"></script>

<!-- JS Front -->
<script src="../assets/js/theme.min.js"></script>

<!-- JS Plugins Init. -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tab functionality
        const tabEls = document.querySelectorAll('a[data-bs-toggle="tab"]');
        tabEls.forEach(tabEl => {
            new bootstrap.Tab(tabEl);
        });

        // Add FAQ form submission
        document.getElementById('saveFAQ').addEventListener('click', function() {
            const form = document.getElementById('addFAQForm');
            if (form.checkValidity()) {
                // Here you would typically make an AJAX call to save the FAQ
                // For demo purposes, we'll just log the form data and close the modal
                const formData = {
                    category: document.getElementById('faqCategory').value,
                    question: document.getElementById('faqQuestion').value,
                    answer: document.getElementById('faqAnswer').value,
                    status: document.querySelector('input[name="faqStatus"]:checked').value
                };
                console.log('Adding new FAQ:', formData);

                // Close the modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('addFAQModal'));
                modal.hide();

                // Show success message
                alert('FAQ added successfully!');
            } else {
                form.reportValidity();
            }
        });

        // Edit FAQ form submission
        document.getElementById('updateFAQ').addEventListener('click', function() {
            const form = document.getElementById('editFAQForm');
            if (form.checkValidity()) {
                // Here you would typically make an AJAX call to update the FAQ
                // For demo purposes, we'll just log the form data and close the modal
                const formData = {
                    category: document.getElementById('editFaqCategory').value,
                    question: document.getElementById('editFaqQuestion').value,
                    answer: document.getElementById('editFaqAnswer').value,
                    status: document.querySelector('input[name="editFaqStatus"]:checked').value
                };
                console.log('Updating FAQ:', formData);

                // Close the modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('editFAQModal'));
                modal.hide();

                // Show success message
                alert('FAQ updated successfully!');
            } else {
                form.reportValidity();
            }
        });

        // Delete FAQ button handler
        document.querySelectorAll('.btn-outline-danger').forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this FAQ?')) {
                    // Here you would typically make an AJAX call to delete the FAQ
                    // For demo purposes, we'll just log the action
                    console.log('FAQ deleted');

                    // Show success message
                    alert('FAQ deleted successfully!');
                }
            });
        });
    });
</script>