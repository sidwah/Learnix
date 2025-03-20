<form id="createCourseForm" enctype="multipart/form-data">
    <div id="progressbarwizard">
        <!-- Navigation Bar -->
        <ul class="nav nav-pills nav-justified form-wizard-header mb-3">
            <li class="nav-item">
                <a href="#basic-details" data-bs-toggle="tab" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                    <i class="mdi mdi-account-circle me-1"></i>
                    <span class="d-none d-sm-inline">Basic Details</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#content-upload" data-bs-toggle="tab" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                    <i class="mdi mdi-upload me-1"></i>
                    <span class="d-none d-sm-inline">Content Upload</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#pricing-access" data-bs-toggle="tab" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                    <i class="mdi mdi-currency-usd me-1"></i>
                    <span class="d-none d-sm-inline">Pricing & Access</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#additional-settings" data-bs-toggle="tab" data-toggle="tab"
                    class="nav-link rounded-0 pt-2 pb-2">
                    <i class="mdi mdi-settings me-1"></i>
                    <span class="d-none d-sm-inline">Additional Settings</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#review" data-bs-toggle="tab" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                    <i class="mdi mdi-checkbox-marked-circle-outline me-1"></i>
                    <span class="d-none d-sm-inline">Review</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#finish-2" data-bs-toggle="tab" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
                    <i class="mdi mdi-checkbox-marked-circle-outline me-1"></i>
                    <span class="d-none d-sm-inline">Finish</span>
                </a>
            </li>
        </ul>

        <div class="tab-content b-0 mb-0">
            <!-- Progress Bar -->
            <div id="bar" class="progress mb-3" style="height: 7px;">
                <div class="bar progress-bar progress-bar-striped progress-bar-animated bg-success"></div>
            </div>

            <!-- Step 1: Basic Details -->
            <div class="tab-pane" id="basic-details">
                <div class="row">
                    <div class="col-12">
                        <!-- Course Title -->
                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label" for="courseTitle">Course Title <span class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" id="courseTitle" name="courseTitle"
                                    placeholder="Enter Course Title" required>
                            </div>
                        </div>

                        <!-- Short Description -->
                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label" for="shortDescription">Short Description <span class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" id="shortDescription" name="shortDescription"
                                    placeholder="Enter short description (max 150 characters)" maxlength="150" required>
                                <small class="form-text text-muted">A short course description that helps students
                                    understand what they will learn.</small>
                            </div>
                        </div>

                        <!-- Full Description -->
                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label" for="fullDescription">Full Description <span class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <textarea class="form-control" id="fullDescription" name="fullDescription" rows="4"
                                    placeholder="Enter full course description" required></textarea>
                            </div>
                        </div>

                        <!-- What You'll Learn -->
                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label" for="learningOutcomes">What You'll Learn <span class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <div id="learningOutcomesContainer">
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="learningOutcomes[]"
                                            placeholder="Enter a learning outcome" required>
                                        <button type="button" class="btn btn-success"
                                            onclick="addLearningOutcome()">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                            function addLearningOutcome() {
                                const container = document.getElementById('learningOutcomesContainer');
                                const inputGroup = document.createElement('div');
                                inputGroup.classList.add('input-group', 'mb-2');

                                const input = document.createElement('input');
                                input.type = 'text';
                                input.name = 'learningOutcomes[]';
                                input.classList.add('form-control');
                                input.placeholder = 'Enter a learning outcome';
                                input.required = true;

                                const removeButton = document.createElement('button');
                                removeButton.type = 'button';
                                removeButton.classList.add('btn', 'btn-danger');
                                removeButton.textContent = '−';
                                removeButton.onclick = function() {
                                    container.removeChild(inputGroup);
                                };

                                inputGroup.appendChild(input);
                                inputGroup.appendChild(removeButton);
                                container.appendChild(inputGroup);
                            }
                        </script>


                        <!-- Category -->
                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label" for="category">Category <span class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <select class="form-control" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="technology">Technology</option>
                                    <option value="business">Business</option>
                                    <option value="design">Design</option>
                                    <!-- Add more categories as needed -->
                                </select>
                            </div>
                        </div>

                        <!-- Thumbnail Image -->
                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label" for="thumbnailImage">Thumbnail Image <span class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <div class="thumbnail-upload-container"
                                    onclick="document.getElementById('thumbnailImage').click()">
                                    <input type="file" class="form-control d-none" id="thumbnailImage"
                                        name="thumbnailImage" accept="image/*" onchange="previewThumbnail(event)" required>
                                    <img id="thumbnailPreview"
                                        src="https://via.placeholder.com/600x300?text=Upload+Image"
                                        alt="Thumbnail Preview">
                                </div>
                                <p id="uploadInstruction" class="text-center mt-2">Click to upload an image</p>
                            </div>
                        </div>

                        <style>
                            .thumbnail-upload-container {
                                width: 100%;
                                /* Full width */
                                height: 450px;
                                /* Increased height */
                                border: 2px dashed #ccc;
                                border-radius: 8px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                overflow: hidden;
                                cursor: pointer;
                                background-color: #f8f9fa;
                            }

                            .thumbnail-upload-container img {
                                width: 100%;
                                height: 100%;
                                object-fit: cover;
                            }

                            #uploadInstruction {
                                font-size: 14px;
                                color: #666;
                            }
                        </style>

                        <script>
                            function previewThumbnail(event) {
                                const file = event.target.files[0];
                                const instruction = document.getElementById('uploadInstruction');
                                if (file) {
                                    const reader = new FileReader();
                                    reader.onload = function(e) {
                                        document.getElementById('thumbnailPreview').src = e.target.result;
                                        instruction.textContent = "Click to change image";
                                    };
                                    reader.readAsDataURL(file);
                                }
                            }
                        </script>



                    </div> <!-- end col -->
                </div> <!-- end row -->
            </div> <!-- end tab-pane for Step 1 -->

            <!-- Step 2: Content Upload -->
            <div class="tab-pane" id="content-upload">
                <div class="row">
                    <div class="col-12">
                        <!-- Add Sections -->
                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label"><strong>Add Sections </strong><span class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <div id="sectionsContainer">
                                    <!-- Initial Section (Always Present) -->
                                    <div class="section-item">
                                        <input type="text" class="form-control" name="sections[]"
                                            placeholder="Enter section name (e.g., Module 1)" required>
                                        <button type="button" class="btn btn-secondary btn-sm toggle-content"
                                            onclick="toggleSectionContent(this)">↓</button>
                                    </div>
                                    <div class="section-content">
                                        <div class="content-container"></div>
                                        <div class="button-group">
                                            <button type="button" class="btn btn-success btn-sm add-course"
                                                onclick="addCourse(this)">+ Add Course</button>
                                            <button type="button" class="btn btn-primary btn-sm add-quiz"
                                                onclick="addQuiz(this)">+ Add Quiz</button>
                                        </div>

                                    </div>
                                </div>
                                <button type="button" class="btn btn-primary mt-2" onclick="addSection()">+ Add
                                    Section</button>
                            </div>
                        </div>

                        <style>
                            .section-item {
                                display: flex;
                                align-items: center;
                                margin-bottom: 10px;
                            }

                            .section-item input {
                                flex: 1;
                            }

                            .button-group {
                                display: flex;
                                gap: 10px;
                                margin-bottom: 15px;
                            }

                            .remove-section {
                                margin-left: 10px;
                                width: 35px;
                                height: 35px;
                                border-radius: 50%;
                                font-size: 18px;
                                text-align: center;
                                line-height: 20px;
                                border: none;
                                background: red;
                                color: white;
                                cursor: pointer;
                            }

                            .toggle-content {
                                margin-left: 10px;
                                width: 35px;
                                height: 35px;
                                border-radius: 50%;
                                font-size: 18px;
                                text-align: center;
                                line-height: 20px;
                                border: none;
                                background: #6c757d;
                                color: white;
                                cursor: pointer;
                            }

                            .section-content {
                                display: none;
                                margin-left: 20px;
                                padding: 10px;
                                background: #f8f9fa;
                                border-radius: 5px;
                            }

                            .course-item {
                                padding: 20px;
                                margin-top: 15px;
                                background: white;
                                border-radius: 8px;
                                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                                border-left: 5px solid #007bff;
                            }

                            .quiz-item {
                                padding: 15px;
                                margin-top: 15px;
                                background: #f8d7da;
                                border-radius: 5px;
                                border-left: 5px solid #dc3545;
                            }

                            .content-container {
                                display: flex;
                                flex-direction: column;
                                gap: 15px;
                            }

                            .course-item label,
                            .quiz-item label {
                                font-weight: bold;
                                margin-bottom: 5px;
                                display: block;
                            }

                            .course-item input,
                            .course-item select,
                            .course-item textarea,
                            .quiz-item input {
                                width: 100%;
                                padding: 10px;
                                margin-bottom: 15px;
                                border-radius: 6px;
                                border: 1px solid #ccc;
                            }

                            .remove-course,
                            .remove-quiz {
                                background: red;
                                color: white;
                                border: none;
                                padding: 8px 12px;
                                border-radius: 5px;
                                cursor: pointer;
                                display: block;
                                margin-top: 10px;
                            }

                            .remove-course:hover,
                            .remove-quiz:hover {
                                background: darkred;
                            }
                        </style>

                        <script>
                            function addSection() {
                                const sectionsContainer = document.getElementById('sectionsContainer');
                                const sectionCount = document.querySelectorAll('.section-item').length;

                                const sectionItem = document.createElement('div');
                                sectionItem.classList.add('section-item');

                                sectionItem.innerHTML = `
                                                                            <input type="text" class="form-control" name="sections[]" placeholder="Enter section name (e.g., Module ${sectionCount + 1})" required>
                                                                            <button type="button" class="btn btn-danger btn-sm remove-section" onclick="removeSection(this)">−</button>
                                                                            <button type="button" class="btn btn-secondary btn-sm toggle-content" onclick="toggleSectionContent(this)">↓</button>
                                                                        `;

                                const sectionContent = document.createElement('div');
                                sectionContent.classList.add('section-content');
                                sectionContent.innerHTML = `
                                                                            <div class="content-container"></div>
                                                                            <div class="button-group" style="margin-top: 20px;">
                                                                                <button type="button" class="btn btn-success btn-sm add-course" onclick="addCourse(this)">+ Add Course</button>
                                                                                <button type="button" class="btn btn-primary btn-sm add-quiz" onclick="addQuiz(this)">+ Add Quiz</button>
                                                                            </div>
                                                                            
                                                                        `;

                                sectionsContainer.appendChild(sectionItem);
                                sectionsContainer.appendChild(sectionContent);
                            }

                            function addQuiz(button) {
                                const contentContainer = button.closest('.section-content').querySelector('.content-container');
                                const quizContainer = document.createElement('div');
                                quizContainer.classList.add('quiz-item');
                                quizContainer.innerHTML = `
                                                                            <input type="text" class="form-control" name="quiz_titles[]" placeholder="Quiz Title" required>
                                                                            <label>Randomize Questions:</label>
                                                                            <input type="checkbox" name="quiz_random[]">
                                                                            <label>Pass Mark (%): </label>
                                                                            <input type="number" class="form-control" name="quiz_pass_marks[]" min="0" max="100" placeholder="Enter Pass Mark" required>
                                                                            <button type="button" class="btn btn-danger btn-sm remove-quiz" onclick="removeQuiz(this)">− Remove Quiz</button>
                                                                        `;
                                contentContainer.appendChild(quizContainer);
                            }

                            function addCourse(button) {
                                const contentContainer = button.closest('.section-content').querySelector('.content-container');
                                const courseContainer = document.createElement('div');
                                courseContainer.classList.add('course-item');
                                courseContainer.innerHTML = `
                                                                            <input type="text" class="form-control" name="course_titles[]" placeholder="Course Title" required>
                                                                            
                                                                            <label>Video Type:</label>
                                                                            <select class="form-control video-type" onchange="toggleVideoInput(this)">
                                                                                <option value="" disabled selected>Choose Video Type</option>
                                                                                <option value="upload">Upload Video</option>
                                                                                <option value="youtube">YouTube Link</option>
                                                                                <option value="external">External Link</option>
                                                                            </select>

                                                                            <input type="file" class="form-control video-upload" accept="video/*" required>
                                                                            <input type="text" class="form-control video-url d-none" placeholder="Enter Video URL" required>

                                                                            <label>Description:</label>
                                                                            <textarea class="form-control" name="course_descriptions[]" rows="3" placeholder="Enter course description"></textarea>

                                                                            <label>Resources (PDF, DOC, ZIP, etc.):</label>
                                                                            <input type="file" class="form-control" name="course_resources[]" accept=".pdf,.doc,.docx,.zip">

                                                                            <button type="button" class="btn btn-danger btn-sm remove-course" onclick="removeCourse(this)">− Remove Course</button>
                                                                        `;
                                contentContainer.appendChild(courseContainer);
                            }

                            function removeSection(button) {
                                const sections = document.querySelectorAll('.section-item');
                                if (sections.length > 1) {
                                    button.parentElement.nextElementSibling.remove(); // Remove section content
                                    button.parentElement.remove(); // Remove section
                                } else {
                                    alert("At least one section is required.");
                                }
                            }

                            function removeQuiz(button) {
                                button.parentElement.remove();
                            }

                            function removeCourse(button) {
                                button.parentElement.remove();
                            }

                            function toggleSectionContent(button) {
                                const content = button.parentElement.nextElementSibling;
                                if (content.style.display === "none" || content.style.display === "") {
                                    content.style.display = "block";
                                    button.textContent = "↑";
                                } else {
                                    content.style.display = "none";
                                    button.textContent = "↓";
                                }
                            }

                            function toggleVideoInput(select) {
                                const uploadInput = select.parentElement.querySelector('.video-upload');
                                const urlInput = select.parentElement.querySelector('.video-url');

                                if (select.value === 'upload') {
                                    uploadInput.classList.remove('d-none');
                                    urlInput.classList.add('d-none');
                                } else {
                                    uploadInput.classList.add('d-none');
                                    urlInput.classList.remove('d-none');
                                }
                            }
                        </script>

                        <!-- Organize Content -->
                        <div class="row mb-3">

                            <!-- Organize Content -->
                            <div class="row mb-3">
                                <label class="col-md-3 col-form-label" for="organizeContent"><strong>Organize
                                        Content</strong> </label>
                                <div class="col-md-9">
                                    <p class="text-muted">Drag and drop items below to change the order of your modules.
                                    </p>
                                    <ul id="organizeContent" class="list-group" ondrop="drop(event)"
                                        ondragover="allowDrop(event)"></ul>
                                </div>
                            </div>

                            <script>
                                document.addEventListener("DOMContentLoaded", function() {
                                    initializeFirstSection();
                                });

                                function initializeFirstSection() {
                                    const firstSectionInput = document.querySelector('.section-item input');
                                    if (firstSectionInput) {
                                        firstSectionInput.classList.add('section-title');
                                        addToOrganizeList(1, firstSectionInput.value || "Module 1");

                                        firstSectionInput.addEventListener('input', function() {
                                            updateOrganizeList();
                                        });
                                    }
                                }

                                function addSection() {
                                    const sectionsContainer = document.getElementById('sectionsContainer');
                                    const sectionCount = document.querySelectorAll('.section-item').length + 1;

                                    const sectionItem = document.createElement('div');
                                    sectionItem.classList.add('section-item');

                                    sectionItem.innerHTML = `
                                                                                <input type="text" class="form-control section-title" name="sections[]" placeholder="Enter section name (e.g., Module ${sectionCount})" required oninput="updateOrganizeList()" required>
                                                                                <button type="button" class="btn btn-danger btn-sm remove-section" onclick="removeSection(this)">−</button>
                                                                                <button type="button" class="btn btn-secondary btn-sm toggle-content" onclick="toggleSectionContent(this)">↓</button>
                                                                            `;

                                    const sectionContent = document.createElement('div');
                                    sectionContent.classList.add('section-content');
                                    sectionContent.innerHTML = `
                                                                                <div class="content-container"></div>
                                                                                <div class="button-group" style="margin-top: 20px;">
                                                                                    <button type="button" class="btn btn-success btn-sm add-course" onclick="addCourse(this)">+ Add Course</button>
                                                                                    <button type="button" class="btn btn-primary btn-sm add-quiz" onclick="addQuiz(this)">+ Add Quiz</button>
                                                                                </div>
                                                                            `;

                                    sectionsContainer.appendChild(sectionItem);
                                    sectionsContainer.appendChild(sectionContent);

                                    addToOrganizeList(sectionCount, `Module ${sectionCount}`);
                                }

                                function addToOrganizeList(index, title) {
                                    const organizeContent = document.getElementById('organizeContent');
                                    const existingItem = organizeContent.querySelector(`[data-index="${index}"]`);

                                    if (existingItem) {
                                        existingItem.textContent = title;
                                    } else {
                                        const listItem = document.createElement('li');
                                        listItem.classList.add('list-group-item');
                                        listItem.setAttribute('draggable', 'true');
                                        listItem.setAttribute('ondragstart', 'drag(event)');
                                        listItem.setAttribute('data-index', index);
                                        listItem.textContent = title;
                                        organizeContent.appendChild(listItem);
                                    }
                                }

                                function removeSection(button) {
                                    const sections = document.querySelectorAll('.section-item');
                                    if (sections.length > 1) {
                                        const sectionItem = button.parentElement;
                                        const sectionContent = sectionItem.nextElementSibling;
                                        const index = Array.from(sections).indexOf(sectionItem) + 1;

                                        // Remove section and its content
                                        sectionContent.remove();
                                        sectionItem.remove();

                                        // Remove from organize list
                                        removeFromOrganizeList(index);

                                        // Re-index organize list to match new section order
                                        updateOrganizeList();
                                    } else {
                                        alert("At least one section is required.");
                                    }
                                }

                                function removeFromOrganizeList(index) {
                                    const organizeContent = document.getElementById('organizeContent');
                                    const items = organizeContent.children;

                                    for (let i = 0; i < items.length; i++) {
                                        if (parseInt(items[i].getAttribute('data-index')) === index) {
                                            organizeContent.removeChild(items[i]);
                                            break;
                                        }
                                    }

                                    // Re-index remaining items
                                    updateOrganizeList();
                                }

                                function updateOrganizeList() {
                                    const sectionTitles = document.querySelectorAll('.section-title');
                                    const organizeContent = document.getElementById('organizeContent');

                                    // Clear organize list
                                    organizeContent.innerHTML = "";

                                    // Re-add sections in the updated order
                                    sectionTitles.forEach((input, index) => {
                                        addToOrganizeList(index + 1, input.value || `Module ${index + 1}`);
                                    });
                                }

                                function allowDrop(event) {
                                    event.preventDefault();
                                }

                                function drag(event) {
                                    event.dataTransfer.setData("text", event.target.dataset.index);
                                }

                                function drop(event) {
                                    event.preventDefault();
                                    const data = event.dataTransfer.getData("text");
                                    const draggedItem = document.querySelector(`[data-index="${data}"]`);
                                    const targetItem = event.target;

                                    if (targetItem.tagName === 'LI' && targetItem !== draggedItem) {
                                        const organizeContent = document.getElementById('organizeContent');
                                        organizeContent.insertBefore(draggedItem, targetItem.nextSibling);
                                    }
                                }
                            </script>

                        </div>


                        <div class="row mb-3">
                            <!-- Instructions for Instructors -->
                            <div class="alert alert-info" role="alert">
                                <h5><strong>📖 Instructions: How to Structure Your Course</strong></h5>
                                <ul>
                                    <li><strong>Add Sections:</strong> Click "<b>+ Add Section</b>" to create a new
                                        module (e.g., "Module 1"). Each section represents a major topic.</li>
                                    <li><strong>Rename Sections:</strong> Click inside a section name field to modify
                                        it. The list below will update automatically.</li>
                                    <li><strong>Add Course Content:</strong> Inside each section, click "<b>+ Add
                                            Course</b>" to upload a video, external link, or add documents.</li>
                                    <li><strong>Add Quizzes:</strong> Click "<b>+ Add Quiz</b>" to create a quiz. Set a
                                        title, pass mark, and decide whether to randomize questions.</li>
                                    <li><strong>Organize Sections:</strong> Drag & drop sections in the list below to
                                        rearrange your course structure.</li>
                                </ul>
                            </div>
                        </div>


                    </div> <!-- end col -->
                </div> <!-- end row -->
            </div> <!-- end tab-pane for Step 2 -->


            <!-- Step 3: Pricing & Access -->
            <div class="tab-pane" id="pricing-access">
                <div class="row">
                    <!-- Course Pricing -->
                    <div class="col-12">
                        <!-- Pricing Options -->
                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label" for="pricingOptions"><strong>Pricing Options</strong> <span class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <select class="form-control" id="pricingOptions" name="pricingOptions" required onchange="togglePricingFields()">
                                    <option value="one-time">One-time Purchase</option>
                                    <option value="free">Free Course</option>
                                </select>
                                <small class="form-text text-muted">Choose how you want to charge for this course.</small>
                            </div>
                        </div>

                        <!-- Course Price (Only for One-Time Purchase) -->
                        <div id="coursePriceContainer">
                            <div class="row mb-3">
                                <label class="col-md-3 col-form-label" for="coursePrice"><strong>Course Price</strong> <span class="text-danger">*</span></label>
                                <div class="col-md-9">
                                    <input id="coursePrice" data-toggle="touchspin" placeholder="0.99" type="text" data-step="0.01" data-decimals="2" data-bts-prefix="$" class="form-control" name="coursePrice" required>
                                    <small class="form-text text-muted">Set the price for the course if it's a one-time purchase.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        function togglePricingFields() {
                            const pricingOption = document.getElementById('pricingOptions').value;
                            const coursePriceContainer = document.getElementById('coursePriceContainer');
                            const coursePriceInput = document.getElementById('coursePrice');

                            if (pricingOption === "one-time") {
                                coursePriceContainer.style.display = "block";
                                coursePriceInput.value = ""; // Allow user input
                                coursePriceInput.removeAttribute("disabled");
                            } else {
                                coursePriceContainer.style.display = "block"; // Keep visible but set value to 0
                                coursePriceInput.value = "0.00";
                                coursePriceInput.setAttribute("disabled", "true");
                            }
                        }

                        document.addEventListener("DOMContentLoaded", function() {
                            togglePricingFields(); // Ensure correct state on page load
                        });
                    </script>
                </div>

                <!-- end col -->
            </div> <!-- end tab-pane for Step 4 -->

            <!-- Step 4: Additional Settings -->
            <div class="tab-pane" id="additional-settings">
                <div class="row">
                    <div class="col-12">
                        <!-- Course Level -->
                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label" for="courseLevel">Course Level <span class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <select class="form-control" id="courseLevel" name="courseLevel" required>
                                    <option value="beginner">Beginner</option>
                                    <option value="intermediate">Intermediate</option>
                                    <option value="advanced">Advanced</option>
                                    <option value="all-levels">All Levels</option>
                                </select>
                                <small class="form-text text-muted">Select the appropriate difficulty level.</small>
                            </div>
                        </div>

                        <!-- Add Tags -->
                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label" for="tags">Add Tags <span class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" id="tags" name="tags"
                                    placeholder="e.g., programming, JavaScript, web development" required>
                                <small class="form-text text-muted">Separate multiple tags with commas.</small>
                            </div>
                        </div>


                        <!-- Course Requirements -->
                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label" for="courseRequirements">Course Requirements <span class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <div id="requirementsContainer">
                                    <div class="requirement-item">
                                        <textarea class="form-control" id="courseRequirements" name="courseRequirements"
                                            rows="4"
                                            placeholder="Enter a course requirement (e.g., Basic Python Knowledge)"
                                            required></textarea>
                                        <!-- <input type="text" class="form-control" name="courseRequirements" placeholder="Enter a course requirement (e.g., Basic Python Knowledge)" required> -->
                                    </div>
                                </div>
                                <small class="form-text text-muted">List any prerequisites for this course.</small>
                            </div>
                        </div>

                        <!-- Certificates (Switch) -->
                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label">Certificate</label>
                            <div class="col-md-9 d-flex align-items-center">
                                <input type="checkbox" id="certificates" name="certificates" data-toggle="switch">
                                <label class="ms-2" for="certificates">Enable Certificate Upon Completion</label>
                            </div>
                        </div>


                    </div>

                    <!-- end col -->
                </div> <!-- end row -->
            </div> <!-- end tab-pane for Step 5 -->

            <!-- Step 5: Review -->
            <div class="tab-pane" id="review">
                <div class="row">
                    <div class="col-12">
                        <h4 class="text-center">Review and Your Course</h4>
                        <p>Ensure all the details are correct before submitting your course for review.</p>
                    </div> <!-- end col -->
                </div> <!-- end row -->
            </div> <!-- end tab-pane for Step 6 -->

            <!-- Step 6: Finish -->
            <div class="tab-pane" id="finish-2">
                <div class="row">
                    <div class="col-12 text-center">
                        <h3>Course Creation Complete!</h3>
                        <p>Your course has been successfully created. You can now review and publish it.</p>
                        <button type="submit" class="btn btn-success" id="finishCourse" name="finishCourse">Save to Draft</button>
                    </div>
                </div> <!-- end row -->
            </div> <!-- end tab-pane for Step 6 -->

            <!-- Navigation Buttons -->
            <ul class="list-inline mb-0 wizard">
                <li class="previous list-inline-item float-start">
                    <a href="javascript:void(0);" class="btn btn-info">Previous</a>
                </li>
                <li class="next list-inline-item float-end">
                    <a href="javascript:void(0);" class="btn btn-info" id="nextButton">Next</a>
                </li>
            </ul>
        </div> <!-- end tab-content -->
    </div> <!-- end #progressbarwizard-->
</form>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const nextButton = document.getElementById('nextButton');
        const finishTab = document.getElementById('finish-2');
        const wizard = document.getElementById('progressbarwizard');

        // Function to check if the current tab is the last one
        function isLastTab() {
            const activeTab = wizard.querySelector('.tab-pane.active');
            return activeTab && activeTab.id === 'finish-2';
        }

        // Function to update the "Next" button state
        function updateNextButton() {
            if (isLastTab()) {
                nextButton.style.display = 'none'; // Hide the button
                // OR
                // nextButton.disabled = true; // Disable the button
            } else {
                nextButton.style.display = 'inline-block'; // Show the button
                // OR
                // nextButton.disabled = false; // Enable the button
            }
        }

        // Listen for tab changes (assuming you're using Bootstrap's tab functionality)
        wizard.addEventListener('shown.bs.tab', function(event) {
            updateNextButton();
        });

        // Initial check on page load
        updateNextButton();
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.getElementById('createCourseForm');
        const finishButton = document.getElementById('finishCourse');

        // Map input names to user-friendly labels
        const fieldLabels = {
            courseTitle: "Course Title",
            shortDescription: "Short Description",
            fullDescription: "Full Description",
            "learningOutcomes[]": "What You'll Learn",
            category: "Category",
            thumbnailImage: "Thumbnail Image",
            "sections[]": "Sections",
            tags: "Tags",
            coursePrice: "Course Price",
            courseRequirements: "Course Requirements"
        };

        finishButton.addEventListener('click', function(event) {
            event.preventDefault(); // Prevent the form from submitting

            let isValid = true;
            let errorMessage = '';

            // Check all required fields
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    const fieldName = field.name || field.id; // Use name or id as fallback
                    const friendlyName = fieldLabels[fieldName] || fieldName; // Get user-friendly name or fallback to raw name
                    errorMessage += `Please fill out the ${friendlyName} field.\n`;
                }
            });

            // Check if the form is valid
            if (isValid) {
                form.submit(); // Submit the form if all fields are filled
            } else {
                alert(errorMessage); // Show an alert with the error message
            }
        });
    });

</script>