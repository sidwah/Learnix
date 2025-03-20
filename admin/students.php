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
                <div class="col-sm">
                    <h1 class="docs-page-header-title">Student Tables</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Heading -->
        <h2 id="component-1" class="hs-docs-heading">
            Students <a class="anchorjs-link" href="#component-1" aria-label="Anchor" data-anchorjs-icon="#"></a>
        </h2>
        <!-- End Heading -->


        <!-- Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center mb-3">
                <h4 class="card-header-title">Student table</h4>
                <input type="text" id="searchInput" class="form-control form-control-sm w-auto" placeholder="🔍 Search Email...">

            </div>

            <!-- Student Table -->


            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th class="sortable" data-sort="name">Name <span class="sort-icon">⇅</span></th>
                            <th class="sortable" data-sort="courses">Courses Enrolled <span class="sort-icon">⇅</span></th>
                            <th class="sortable" data-sort="status">Status <span class="sort-icon">⇅</span></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="studentTableBody">
                        <!-- Data will be injected here -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <button id="prevPage" class="btn btn-sm btn-outline-primary">Previous</button>
                <span id="paginationNumbers"></span>
                <button id="nextPage" class="btn btn-sm btn-outline-primary">Next</button>
            </div>

            <style>
                .sortable {
                    cursor: pointer;
                    user-select: none;
                }

                .sort-icon {
                    font-size: 14px;
                    margin-left: 5px;
                    color: gray;
                }

                #searchInput {
                    max-width: 200px;
                }
            </style>

            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    let students = [];
                    let filteredStudents = [];
                    let currentPage = 1;
                    let studentsPerPage = 15;
                    let sortColumn = null;
                    let sortDirection = "asc";

                    function fetchStudents() {
                        fetch("../backend/admin/fetch-students.php")
                            .then(response => response.json())
                            .then(data => {
                                if (data.error) {
                                    console.error("API Error:", data.error);
                                    return;
                                }
                                students = data;
                                filteredStudents = [...students];
                                displayStudents();
                                createPagination();
                            })
                            .catch(error => console.error("Fetch Error:", error));
                    }

                    function displayStudents() {
                        let tableBody = document.getElementById("studentTableBody");
                        tableBody.innerHTML = "";

                        let startIndex = (currentPage - 1) * studentsPerPage;
                        let endIndex = startIndex + studentsPerPage;
                        let paginatedStudents = filteredStudents.slice(startIndex, endIndex);

                        if (paginatedStudents.length === 0) {
                            tableBody.innerHTML = `<tr><td colspan="4" class="text-center text-muted">No students found.</td></tr>`;
                            return;
                        }

                        paginatedStudents.forEach(student => {
                            let row = generateStudentRow(student);
                            tableBody.innerHTML += row;
                        });

                        attachEventListeners();
                        updateButtons();
                    }

                    function generateStudentRow(student) {
                        let statusIcon = student.status === "active" ? "🟢 Active" :
                            student.status === "suspended" ? "🔴 Suspended" : "🚫 Banned";

                        let actionButtons = "";
                        if (student.status === "active") {
                            actionButtons = `
                <a class="text-danger mx-2 suspend-btn" href="javascript:;" data-id="${student.id}" title="Suspend member">
                    <i class="bi-x-lg"></i>
                </a>
                <a class="text-danger mx-2 ban-btn" href="javascript:;" data-id="${student.id}" title="Ban member">
                    <i class="bi-trash"></i>
                </a>
            `;
                        } else if (student.status === "suspended") {
                            actionButtons = `
                <a class="text-success mx-2 activate-btn" href="javascript:;" data-id="${student.id}" title="Activate member">
                    <i class="bi-check-lg"></i>
                </a>
                <a class="text-danger mx-2 ban-btn" href="javascript:;" data-id="${student.id}" title="Ban member">
                    <i class="bi-trash"></i>
                </a>
            `;
                        } else { // Banned
                            actionButtons = `
                <a class="text-success mx-2 activate-btn" href="javascript:;" data-id="${student.id}" title="Activate member">
                    <i class="bi-check-lg"></i>
                </a>
               <a class="text-danger mx-2 suspend-btn" href="javascript:;" data-id="${student.id}" title="Suspend member">
                    <i class="bi-x-lg"></i>
                </a>
            `;
                        }

                        return `<tr id="row-${student.id}">
            <td>
                <div class="d-flex align-items-center">
                    <span class="avatar avatar-sm avatar-warning avatar-circle">
                        ${student.profile_pic ? 
                            `<img src="../uploads/profile/${student.profile_pic}" alt="Profile Picture" class="avatar-img">` : 
                            `<span class="avatar-initials">${student.name.charAt(0)}</span>`
                        }
                    </span>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">${student.name}</h6>
                        <small class="d-block text-muted">${student.email}</small>
                    </div>
                </div>
            </td>
            <td><span class="badge bg-primary">${student.courses_enrolled} Courses</span></td>
            <td class="status-label" data-id="${student.id}">${statusIcon}</td>
            <td>${actionButtons}</td>
        </tr>`;
                    }

                    function attachEventListeners() {
                        document.querySelectorAll(".suspend-btn, .ban-btn, .activate-btn").forEach(button => {
                            button.addEventListener("click", function() {
                                let studentId = this.getAttribute("data-id");
                                let action = this.classList.contains("suspend-btn") ? "suspended" :
                                    this.classList.contains("activate-btn") ? "active" : "banned";
                                updateStudentStatus(studentId, action);
                            });
                        });
                    }

                    function updateStudentStatus(studentId, newStatus) {
                        fetch("../backend/admin/update-student-status.php", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/x-www-form-urlencoded"
                                },
                                body: `id=${studentId}&status=${newStatus}`
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Update only the affected row
                                    let student = students.find(s => s.id == studentId);
                                    if (student) {
                                        student.status = data.newStatus;
                                    }

                                    let row = document.getElementById(`row-${studentId}`);
                                    if (row) {
                                        row.innerHTML = generateStudentRow(student);
                                        attachEventListeners(); // Reattach event listeners for new buttons
                                    }
                                } else {
                                    alert("Error updating student status");
                                }
                            })
                            .catch(error => console.error("Error:", error));
                    }

                    function createPagination() {
                        let paginationNumbers = document.getElementById("paginationNumbers");
                        paginationNumbers.innerHTML = "";

                        let totalPages = Math.ceil(filteredStudents.length / studentsPerPage);
                        for (let i = 1; i <= totalPages; i++) {
                            let pageButton = document.createElement("button");
                            pageButton.className = `btn btn-sm ${i === currentPage ? 'btn-primary' : 'btn-outline-primary'} mx-1`;
                            pageButton.innerText = i;
                            pageButton.onclick = function() {
                                currentPage = i;
                                displayStudents();
                            };
                            paginationNumbers.appendChild(pageButton);
                        }
                    }

                    function updateButtons() {
                        document.getElementById("prevPage").disabled = (currentPage === 1);
                        document.getElementById("nextPage").disabled = (currentPage === Math.ceil(filteredStudents.length / studentsPerPage));
                    }

                    document.getElementById("prevPage").addEventListener("click", function() {
                        if (currentPage > 1) {
                            currentPage--;
                            displayStudents();
                        }
                    });

                    document.getElementById("nextPage").addEventListener("click", function() {
                        if (currentPage < Math.ceil(filteredStudents.length / studentsPerPage)) {
                            currentPage++;
                            displayStudents();
                        }
                    });

                    // Real-time Search by Email
                    document.getElementById("searchInput").addEventListener("input", function() {
                        let query = this.value.toLowerCase();
                        filteredStudents = students.filter(student => student.email.toLowerCase().includes(query));
                        currentPage = 1;
                        createPagination();
                        displayStudents();
                    });

                    // Sorting Functionality
                    document.querySelectorAll(".sortable").forEach(header => {
                        header.addEventListener("click", function() {
                            let column = this.dataset.sort;
                            if (sortColumn === column) {
                                sortDirection = sortDirection === "asc" ? "desc" : "asc";
                            } else {
                                sortColumn = column;
                                sortDirection = "asc";
                            }

                            filteredStudents.sort((a, b) => {
                                let valA = a[column];
                                let valB = b[column];

                                if (column === "name") {
                                    return sortDirection === "asc" ? valA.localeCompare(valB) : valB.localeCompare(valA);
                                } else if (column === "courses") {
                                    return sortDirection === "asc" ? valA - valB : valB - valA;
                                } else if (column === "status") {
                                    let order = {
                                        "active": 1,
                                        "suspended": 2,
                                        "banned": 3
                                    };
                                    return sortDirection === "asc" ? order[valA] - order[valB] : order[valB] - order[valA];
                                }
                            });

                            document.querySelectorAll(".sort-icon").forEach(icon => {
                                icon.innerHTML = "⇅";
                            });

                            this.querySelector(".sort-icon").innerHTML = sortDirection === "asc" ? "↑" : "↓";

                            displayStudents();
                        });
                    });

                    fetchStudents();
                });
            </script>

            <!-- End Student Table -->


        </div>
        <!-- End Card -->
    </div>
    <!-- End Content -->

</main>
<!-- ========== END MAIN CONTENT ========== -->

<?php include '../includes/admin-footer.php'; ?>cc