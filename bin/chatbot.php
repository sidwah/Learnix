<?php
// chatbot.php

// Security and authentication check
session_start();
// Check if the user is signed in and is a department staff member
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || !isset($_SESSION['department_id']) || !isset($_SESSION['role']) || !in_array($_SESSION['role'], ['department_head', 'department_secretary'])) {
    // Log unauthorized access attempt for security auditing
    error_log("Unauthorized access attempt to protected page: " . $_SERVER['REQUEST_URI'] . " | IP: " . $_SERVER['REMOTE_ADDR']);

    // Redirect unauthorized users to the sign-in page
    header('Location: signin.php');
    exit;
}

include '../includes/department/header.php';
include '../backend/config.php';
?>
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
        <div class="docs-page-header">
            <div class="row align-items-center">
                <div class="col-sm">
                    <h1 class="docs-page-header-title">Chatbot Responses</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center mb-3">
                <h4 class="card-header-title">Chatbot Responses Table</h4>
                <div>
                    <input type="text" id="searchInput" class="form-control form-control-sm w-auto d-inline-block me-2" placeholder="🔍 Search...">
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addResponseModal">
                        <i class="bi bi-plus"></i> Add Response
                    </button>
                </div>
            </div>

            <!-- Chatbot Responses Table -->
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th class="sortable" data-sort="id">ID <span class="sort-icon">⇅</span></th>
                            <th class="sortable" data-sort="user_query">User Query <span class="sort-icon">⇅</span></th>
                            <th class="sortable" data-sort="bot_response">Bot Response <span class="sort-icon">⇅</span></th>
                            <th class="sortable" data-sort="suggestions">Suggestions <span class="sort-icon">⇅</span></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="chatbotTableBody">
                        <!-- Data will be injected here -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
            <div class="d-flex justify-content-between align-items-center mt-3 mb-4">
                <button id="prevPage" class="btn btn-sm btn-outline-primary">Previous</button>
                <span id="paginationNumbers"></span>
                <button id="nextPage" class="btn btn-sm btn-outline-primary">Next</button>
            </div>
        </div>
        <!-- End Card -->
    </div>
    <!-- End Content -->

    <!-- Add Response Modal -->
    <div class="modal fade" id="addResponseModal" tabindex="-1" aria-labelledby="addResponseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addResponseModalLabel">Add New Chatbot Response</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addResponseForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="userQuery" class="form-label">User Query</label>
                            <input type="text" class="form-control" id="userQuery" name="user_query" required>
                        </div>
                        <div class="mb-3">
                            <label for="botResponse" class="form-label">Bot Response</label>
                            <textarea class="form-control" id="botResponse" name="bot_response" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="suggestions" class="form-label">Suggestions (Comma-separated)</label>
                            <input type="text" class="form-control" id="suggestions" name="suggestions">
                            <small class="form-text text-muted">Separate multiple suggestions with commas</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Response</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Response Modal -->
    <div class="modal fade" id="editResponseModal" tabindex="-1" aria-labelledby="editResponseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editResponseModalLabel">Edit Chatbot Response</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editResponseForm">
                    <input type="hidden" id="editResponseId" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editUserQuery" class="form-label">User Query</label>
                            <input type="text" class="form-control" id="editUserQuery" name="user_query" required>
                        </div>
                        <div class="mb-3">
                            <label for="editBotResponse" class="form-label">Bot Response</label>
                            <textarea class="form-control" id="editBotResponse" name="bot_response" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editSuggestions" class="form-label">Suggestions (Comma-separated)</label>
                            <input type="text" class="form-control" id="editSuggestions" name="suggestions">
                            <small class="form-text text-muted">Separate multiple suggestions with commas</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- Inline Styles -->
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

    .table-responsive {
        max-height: 600px;
        overflow-y: auto;
    }
</style>

<!-- Page-Specific JavaScript -->
<script>
    // Notification Function (similar to other pages in the system)
    function showNotification(message, type = 'success') {
        // Create toast container if it doesn't exist
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }

        // Create toast element
        const toastDiv = document.createElement('div');
        toastDiv.className = `toast align-items-center text-bg-${type === 'success' ? 'success' : 'danger'} border-0`;
        toastDiv.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

        // Add to container and show
        toastContainer.appendChild(toastDiv);
        const toast = new bootstrap.Toast(toastDiv);
        toast.show();

        // Remove toast after it's hidden
        toastDiv.addEventListener('hidden.bs.toast', () => {
            toastDiv.remove();
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        let chatbotResponses = [];
        let filteredResponses = [];
        let currentPage = 1;
        const responsesPerPage = 15;
        let sortColumn = null;
        let sortDirection = "asc";

        // Fetch Chatbot Responses
        function fetchChatbotResponses() {
            fetch("../backend/admin/fetch-chatbot-responses.php")
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error("API Error:", data.error);
                        return;
                    }
                    chatbotResponses = data;
                    filteredResponses = [...chatbotResponses];
                    displayResponses();
                    createPagination();
                })
                .catch(error => console.error("Fetch Error:", error));
        }

        // Display Responses
        function displayResponses() {
            const tableBody = document.getElementById("chatbotTableBody");
            tableBody.innerHTML = "";

            const startIndex = (currentPage - 1) * responsesPerPage;
            const endIndex = startIndex + responsesPerPage;
            const paginatedResponses = filteredResponses.slice(startIndex, endIndex);

            if (paginatedResponses.length === 0) {
                tableBody.innerHTML =
                    `<tr><td colspan="5" class="text-center text-muted">No responses found.</td></tr>`;
                return;
            }

            paginatedResponses.forEach(response => {
                const row = generateResponseRow(response);
                tableBody.innerHTML += row;
            });

            attachEventListeners();
            updatePaginationButtons();
        }

        // Generate Table Row
        function generateResponseRow(response) {
            return `
            <tr id="row-${response.id}">
                <td>${response.id}</td>
                <td>
                    <div class="text-truncate" style="max-width: 200px;" title="${response.user_query}">
                        ${response.user_query}
                    </div>
                </td>
                <td>
                    <div class="text-truncate" style="max-width: 300px;" title="${response.bot_response}">
                        ${response.bot_response}
                    </div>
                </td>
                <td>
                    <div class="text-truncate" style="max-width: 150px;" title="${response.suggestions || ''}">
                        ${response.suggestions || 'N/A'}
                    </div>
                </td>
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-info edit-btn" data-id="${response.id}" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-danger delete-btn" data-id="${response.id}" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
        }

        // Attach Event Listeners
        function attachEventListeners() {
            // Edit Button Listeners
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const responseId = this.getAttribute('data-id');
                    const response = chatbotResponses.find(r => r.id == responseId);

                    // Check if response exists
                    if (!response) {
                        showNotification('No response found with the given ID', 'danger');
                        return;
                    }

                    // Populate Edit Modal
                    document.getElementById('editResponseId').value = response.id;
                    document.getElementById('editUserQuery').value = response.user_query;
                    document.getElementById('editBotResponse').value = response.bot_response;
                    document.getElementById('editSuggestions').value = response.suggestions || '';

                    // Show Edit Modal
                    new bootstrap.Modal(document.getElementById('editResponseModal')).show();
                });
            });

            // Delete Button Listeners
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const responseId = this.getAttribute('data-id');
                    if (confirm('Are you sure you want to delete this response?')) {
                        deleteResponse(responseId);
                    }
                });
            });
        }

        // Delete Response
        function deleteResponse(responseId) {
            fetch('../backend/admin/delete-chatbot-response.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${responseId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove from local array
                        chatbotResponses = chatbotResponses.filter(r => r.id != responseId);
                        filteredResponses = filteredResponses.filter(r => r.id != responseId);

                        // Refresh display
                        displayResponses();
                        createPagination();

                        // Show success notification
                        showNotification('Chatbot response deleted successfully', 'success');
                    } else {
                        // Show error notification
                        showNotification(data.error || 'Failed to delete chatbot response', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while deleting the response', 'danger');
                });
        }

        // Add Response Form Submission
        document.getElementById('addResponseForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('../backend/admin/add-chatbot-response.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Close modal
                        bootstrap.Modal.getInstance(document.getElementById('addResponseModal')).hide();

                        // Reset form
                        this.reset();

                        // Refresh responses
                        fetchChatbotResponses();

                        // Show success notification
                        showNotification('Chatbot response added successfully', 'success');
                    } else {
                        // Show error notification
                        showNotification(data.error || 'Failed to add chatbot response', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while adding the response', 'danger');
                });
        });

        // Edit Response Form Submission
        document.getElementById('editResponseForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('../backend/department/update-chatbot-response.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Close modal
                        bootstrap.Modal.getInstance(document.getElementById('editResponseModal')).hide();

                        // Refresh responses
                        fetchChatbotResponses();

                        // Show success notification
                        showNotification('Chatbot response updated successfully', 'success');
                    } else {
                        // Show error notification
                        showNotification(data.error || 'Failed to update chatbot response', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while updating the response', 'danger');
                });
        });

        // Pagination and Sorting Functions
        function createPagination() {
            const paginationNumbers = document.getElementById("paginationNumbers");
            paginationNumbers.innerHTML = "";

            const totalPages = Math.ceil(filteredResponses.length / responsesPerPage);
            for (let i = 1; i <= totalPages; i++) {
                const pageButton = document.createElement("button");
                pageButton.className = `btn btn-sm ${i === currentPage ? 'btn-primary' : 'btn-outline-primary'} mx-1`;
                pageButton.innerText = i;
                pageButton.onclick = () => {
                    currentPage = i;
                    displayResponses();
                };
                paginationNumbers.appendChild(pageButton);
            }
        }

        // Update Pagination Buttons
        function updatePaginationButtons() {
            const totalPages = Math.ceil(filteredResponses.length / responsesPerPage);
            document.getElementById("prevPage").disabled = (currentPage === 1);
            document.getElementById("nextPage").disabled = (currentPage === totalPages);
        }

        // Previous Page Event Listener
        document.getElementById("prevPage").addEventListener("click", function() {
            if (currentPage > 1) {
                currentPage--;
                displayResponses();
            }
        });

        // Next Page Event Listener
        document.getElementById("nextPage").addEventListener("click", function() {
            const totalPages = Math.ceil(filteredResponses.length / responsesPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                displayResponses();
            }
        });

        // Search Functionality
        document.getElementById("searchInput").addEventListener("input", function() {
            const query = this.value.toLowerCase();
            filteredResponses = chatbotResponses.filter(response =>
                Object.values(response).some(value =>
                    String(value).toLowerCase().includes(query)
                )
            );
            currentPage = 1;
            createPagination();
            displayResponses();
        });

        // Sorting Functionality
        document.querySelectorAll(".sortable").forEach(header => {
            header.addEventListener("click", function() {
                const column = this.dataset.sort;

                // Toggle sort direction
                if (sortColumn === column) {
                    sortDirection = sortDirection === "asc" ? "desc" : "asc";
                } else {
                    sortColumn = column;
                    sortDirection = "asc";
                }

                // Reset sort icons
                document.querySelectorAll(".sort-icon").forEach(icon => {
                    icon.innerHTML = "⇅";
                });

                // Update current column's sort icon
                this.querySelector(".sort-icon").innerHTML = sortDirection === "asc" ? "↑" : "↓";

                // Sort the responses
                filteredResponses.sort((a, b) => {
                    let valA = a[column];
                    let valB = b[column];

                    // Handle different types of sorting
                    if (typeof valA === 'string') {
                        return sortDirection === "asc" ?
                            valA.localeCompare(valB) :
                            valB.localeCompare(valA);
                    }

                    // Numeric sorting
                    return sortDirection === "asc" ?
                        (valA - valB) :
                        (valB - valA);
                });

                // Reset to first page and display
                currentPage = 1;
                createPagination();
                displayResponses();
            });
        });

        // Initial fetch of chatbot responses
        fetchChatbotResponses();
    });
</script>

<?php include '../includes/department/footer.php'; ?>