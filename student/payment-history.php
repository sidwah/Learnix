<?php include '../includes/student-header.php'; ?>

<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main" class="bg-gray-50">
  <!-- Breadcrumb -->
  <?php include '../includes/student-breadcrumb.php'; ?>
  <!-- End Breadcrumb -->

  <!-- Content -->
  <div class="container content-space-1 content-space-t-lg-0 content-space-b-lg-2 mt-lg-n10">
    <div class="row">
      <div class="col-lg-3">
        <!-- Navbar -->
        <div class="navbar-expand-lg navbar-light">
          <div id="sidebarNav" class="collapse navbar-collapse navbar-vertical">
            <!-- Card -->
            <div class="card flex-grow-1 mb-5 shadow-sm">
              <div class="card-body">
                <!-- Avatar -->
                <div class="d-none d-lg-block text-center mb-5">
                  <div class="avatar avatar-xxl avatar-circle mb-3">
                    <div class="flex-shrink-0">
                      <img class="avatar avatar-xl avatar-circle"
                        src="../Uploads/profile/<?php echo $row['profile_pic'] ?>"
                        alt="Profile">
                    </div>
                  </div>
                  <h4 class="card-title mb-0"><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></h4>
                  <p class="card-text small text-muted"><?php echo $row['email']; ?></p>
                </div>
                <!-- End Avatar -->

                <!-- Sidebar Content -->
                <span class="text-cap text-primary">Overview</span>
                <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                  <li class="nav-item">
                    <a class="nav-link" href="account-overview.php">
                      <i class="bi-person-circle nav-icon"></i> Account Overview
                    </a>
                  </li>
                </ul>

                <span class="text-cap text-primary">Account</span>
                <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                  <li class="nav-item">
                    <a class="nav-link" href="account-profile.php">
                      <i class="bi-person-badge nav-icon"></i> Personal info
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="account-security.php">
                      <i class="bi-shield-shaded nav-icon"></i> Security
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="account-notifications.php">
                      <i class="bi-bell nav-icon"></i> Notifications
                      <!-- <span class="badge bg-soft-dark text-dark rounded-pill nav-link-badge">0</span> -->
                    </a>
                  </li>
                </ul>

                <span class="text-cap text-primary">My Courses</span>
                <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                  <li class="nav-item">
                    <a class="nav-link" href="my-courses.php">
                      <i class="bi-person-badge nav-icon"></i> Enrolled Courses
                    </a>
                  </li>
                  <!-- <li class="nav-item">
                    <a class="nav-link" href="my-badges.php">
                      <i class="bi-chat-dots nav-icon"></i> Badges
                    </a>
                  </li> -->
                  <li class="nav-item">
                    <a class="nav-link" href="my-certifications.php">
                      <i class="bi-award nav-icon"></i> Certifications
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="my-notes.php">
                      <i class="bi-journal-text nav-icon"></i> Notes
                    </a>
                  </li>
                </ul>

                <span class="text-cap text-primary">Payments</span>
                <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                  <li class="nav-item">
                    <a class="nav-link active" href="payment-history.php">
                      <i class="bi-credit-card nav-icon"></i> Payment History
                    </a>
                  </li>
                </ul>

                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'instructor'): ?>
                  <span class="text-cap text-primary">Instructor</span>
                  <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                    <li class="nav-item">
                      <a class="nav-link" href="instructor-courses.php">
                        <i class="bi-person-badge nav-icon"></i> My Courses
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="create-course.php">
                        <i class="bi-file-earmark-plus nav-icon"></i> Create Course
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="manage-students.php">
                        <i class="bi-person-check nav-icon"></i> Manage Students
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="course-feedback.php">
                        <i class="bi-chat-dots nav-icon"></i> Course Feedback
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="instructor-withdrawal.php">
                        <i class="bi-wallet nav-icon"></i> Withdrawal
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="instructor-analytics.php">
                        <i class="bi-gear nav-icon"></i> Analytics
                      </a>
                    </li>
                  </ul>
                <?php endif; ?>

                <span class="text-cap text-primary">---</span>
                <ul class="nav nav-sm nav-tabs nav-vertical">
                  <li class="nav-item">
                    <a class="nav-link" href="account-help.php">
                      <i class="bi-question-circle nav-icon"></i> Help
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="FAQ.php">
                      <i class="bi-card-list nav-icon"></i> FAQ's
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="report.php">
                      <i class="bi-exclamation-triangle nav-icon"></i> Report Issues
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="../backend/signout.php">
                      <i class="bi-box-arrow-right nav-icon"></i> Sign out
                    </a>
                  </li>
                </ul>
              </div>
            </div>
            <!-- End Card -->
          </div>
        </div>
        <!-- End Navbar -->
      </div>
      <!-- End Col -->

      <div class="col-lg-9">
        <div class="d-grid gap-4">
          <!-- Summary Card -->
          <?php
          require_once '../backend/config.php';
          $user_id = $_SESSION['user_id'];
          $summary_query = "SELECT COUNT(*) as total_transactions, SUM(amount) as total_spent
                              FROM course_payments cp
                              JOIN enrollments e ON cp.enrollment_id = e.enrollment_id
                              WHERE e.user_id = ?";
          $stmt = $conn->prepare($summary_query);
          $stmt->bind_param("i", $user_id);
          $stmt->execute();
          $summary_result = $stmt->get_result();
          $summary = $summary_result->fetch_assoc();
          $total_spent = $summary['total_spent'] ? number_format($summary['total_spent'], 2) : '0.00';
          $total_transactions = $summary['total_transactions'] ? $summary['total_transactions'] : '0';
          ?>
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <h5 class="card-title mb-4">Payment Summary</h5>
              <div class="flex justify-between items-center">
                <div>
                  <p class="text-gray-600">Total Spent</p>
                  <h3 id="totalSpent" class="text-2xl font-semibold">GHS <?php echo $total_spent; ?></h3>
                </div>
                <div>
                  <p class="text-gray-600">Total Transactions</p>
                  <h3 id="totalTransactions" class="text-2xl font-semibold"><?php echo $total_transactions; ?></h3>
                </div>
              </div>
            </div>
          </div>

          <!-- Payment History Card -->
          <div id="paymentHistoryCard" class="card shadow-sm border-0">
            <div class="card-header bg-white py-4 border-bottom">
              <h4 class="card-header-title mb-0">Payment History</h4>
            </div>

            <!-- Body -->
            <div class="card-body">
              <!-- Payment Controls -->
              <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4 w-full md:w-auto">
                  <div class="relative w-full md:w-64">
                    <input type="text" class="form-control" id="searchPayments" placeholder="Search by course or transaction ID..." oninput="filterPayments()">
                  </div>
                  <select class="form-select w-full md:w-40" id="filterStatus" onchange="filterPayments()">
                    <option value="all">All Status</option>
                    <option value="Completed">Completed</option>
                    <option value="Pending">Pending</option>
                    <option value="Failed">Failed</option>
                  </select>
                  <select class="form-select w-full md:w-64" id="sortBy" onchange="filterPayments()">
                    <option value="date-desc">Date (Newest First)</option>
                    <option value="date-asc">Date (Oldest First)</option>
                    <option value="amount-desc">Amount (High to Low)</option>
                    <option value="amount-asc">Amount (Low to High)</option>
                  </select>
                  <button class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">Reset</button>
                </div>
              </div>

              <!-- Payment List -->
              <div id="paymentList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php
                $query = "SELECT cp.payment_id, cp.amount, cp.currency, cp.payment_date, cp.payment_method, 
                                 cp.transaction_id, cp.status, c.title as course_title, c.thumbnail,
                                 e.enrollment_id, c.course_id
                          FROM course_payments cp
                          JOIN enrollments e ON cp.enrollment_id = e.enrollment_id
                          JOIN courses c ON e.course_id = c.course_id
                          WHERE e.user_id = ?
                          ORDER BY cp.payment_date DESC";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $payment_count = $result->num_rows;

                if ($payment_count > 0):
                  while ($row = $result->fetch_assoc()):
                ?>
                    <div class="payment-item bg-white rounded-lg shadow-sm hover:shadow-lg transition-shadow p-4 h-48 flex flex-col justify-between"
                      data-status="<?php echo strtolower($row['status']); ?>"
                      data-date="<?php echo date('Y-m-d', strtotime($row['payment_date'])); ?>"
                      data-method="<?php echo $row['payment_method']; ?>"
                      data-amount="<?php echo $row['amount']; ?>"
                      data-course="<?php echo htmlspecialchars($row['course_title']); ?>"
                      data-transaction-id="<?php echo $row['transaction_id']; ?>">
                      <div>
                        <div class="flex items-center space-x-3 mb-2">
                          <?php if ($row['thumbnail']): ?>
                            <img class="w-10 h-10 rounded object-cover" src="../Uploads/thumbnails/<?php echo $row['thumbnail']; ?>" alt="Course Thumbnail">
                          <?php else: ?>
                            <div class="w-10 h-10 rounded bg-primary bg-opacity-10 flex items-center justify-center">
                              <span class="text-primary font-semibold"><?php echo substr($row['course_title'], 0, 1); ?></span>
                            </div>
                          <?php endif; ?>
                          <h5 class="text-base font-semibold truncate" title="<?php echo htmlspecialchars($row['course_title']); ?>"><?php echo htmlspecialchars($row['course_title']); ?></h5>
                        </div>
                        <div class="flex justify-between items-center">
                          <span class="badge text-xs bg-<?php echo $row['status'] === 'Completed' ? 'success' : ($row['status'] === 'Pending' ? 'warning' : 'danger'); ?> bg-opacity-10 text-<?php echo $row['status'] === 'Completed' ? 'success' : ($row['status'] === 'Pending' ? 'warning' : 'danger'); ?> rounded-pill px-2 py-1">
                            <?php echo $row['status']; ?>
                          </span>
                          <p class="text-sm text-gray-600">GHS <?php echo number_format($row['amount'], 2); ?></p>
                        </div>
                      </div>
                      <div>
                        <div class="flex justify-between items-center">
                          <button class="btn btn-sm btn-soft-primary" onclick="viewReceipt(this)"
                            data-id="<?php echo $row['payment_id']; ?>"
                            data-course="<?php echo htmlspecialchars($row['course_title']); ?>"
                            data-transaction-id="<?php echo $row['transaction_id']; ?>"
                            data-date="<?php echo date('M d, Y', strtotime($row['payment_date'])); ?>"
                            data-amount="GHS <?php echo number_format($row['amount'], 2); ?>"
                            data-method="<?php echo $row['payment_method']; ?>"
                            data-status="<?php echo $row['status']; ?>"
                            data-time="<?php echo date('h:i A', strtotime($row['payment_date'])); ?>"
                            data-thumbnail="<?php echo $row['thumbnail'] ? '../Uploads/thumbnails/' . $row['thumbnail'] : ''; ?>">
                            View Details
                          </button>
                          <!-- Comment out refund button for future implementation
                          <button class="btn btn-sm btn-soft-warning" onclick="requestRefund(this)" 
                                  data-id="<?php echo $row['payment_id']; ?>" 
                                  data-course="<?php echo htmlspecialchars($row['course_title']); ?>" 
                                  data-transaction-id="<?php echo $row['transaction_id']; ?>">
                            Request Refund
                          </button>
                          -->
                        </div>
                      </div>
                    </div>
                  <?php endwhile; ?>
                <?php else: ?>
                  <div id="emptyPayments" class="col-span-full text-center py-12 bg-white rounded-lg shadow-sm">
                    <i class="bi bi-credit-card-2-front text-gray-400 text-6xl mb-4"></i>
                    <h5 class="text-lg font-semibold text-gray-700 mb-2">No Payments Yet</h5>
                    <p class="text-gray-600 mb-4">It looks like you haven't made any payments yet. Enroll in a course to get started!</p>
                    <a href="../courses.php" class="btn btn-primary">Explore Courses</a>
                  </div>
                <?php endif; ?>
              </div>

              <!-- Pagination (shown only if 9 or more items) -->
              <?php if ($payment_count >= 9): ?>
                <div class="flex justify-center mt-6" id="pagination">
                  <nav aria-label="Page navigation">
                    <ul class="pagination">
                      <li class="page-item"><a class="page-link" href="#" onclick="changePage(currentPage - 1)">Previous</a></li>
                      <li class="page-item"><a class="page-link active" href="#" onclick="changePage(1)">1</a></li>
                      <li class="page-item"><a class="page-link" href="#" onclick="changePage(2)">2</a></li>
                      <li class="page-item"><a class="page-link" href="#" onclick="changePage(3)">3</a></li>
                      <li class="page-item"><a class="page-link" href="#" onclick="changePage(currentPage + 1)">Next</a></li>
                    </ul>
                  </nav>
                </div>
              <?php endif; ?>
            </div>
            <!-- End Body -->
          </div>
          <!-- End Card -->

          <!-- Receipt Details Modal -->
          <div class="modal fade" id="receiptDetailsModal" tabindex="-1" aria-labelledby="receiptDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
              <div class="modal-content border-0 shadow-lg rounded-lg">
                <div class="modal-header bg-gray-100 border-bottom-0">
                  <h5 class="modal-title font-semibold text-lg" id="receiptDetailsModalLabel">Payment Details</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-6">
                  <div class="flex items-center space-x-4 mb-4">
                    <div id="modalThumbnail" class="w-16 h-16 rounded bg-primary bg-opacity-10 flex items-center justify-center text-primary font-semibold"></div>
                    <div>
                      <h3 id="modalCourse" class="text-xl font-semibold mb-1"></h3>
                      <span id="modalStatus" class="badge rounded-pill px-3 py-1"></span>
                    </div>
                  </div>
                  <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <h6 class="font-semibold mb-3">Transaction Details</h6>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <p class="text-sm text-gray-600 mb-2"><strong>Transaction ID:</strong> <span id="modalTransactionId"></span></p>
                        <p class="text-sm text-gray-600 mb-2"><strong>Date:</strong> <span id="modalDate"></span></p>
                        <p class="text-sm text-gray-600 mb-2"><strong>Time:</strong> <span id="modalTime"></span></p>
                      </div>
                      <div>
                        <p class="text-sm text-gray-600 mb-2"><strong>Amount:</strong> <span id="modalAmount"></span></p>
                        <p class="text-sm text-gray-600 mb-2"><strong>Payment Method:</strong> <span id="modalMethod"></span></p>
                      </div>
                    </div>
                  </div>
                  <div class="flex justify-end">
                    <button id="modalDownloadLink" class="btn btn-primary" onclick="printReceipt()"><i class="bi bi-download me-2"></i>Download Receipt</button>
                  </div>
                </div>
                <div class="modal-footer bg-gray-100 border-top-0">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
              </div>
            </div>
          </div>

          <!-- Hidden Printable Receipt -->
          <div id="printableReceipt" style="display: none;">
            <div style="padding: 20px; font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
              <h2 style="text-align: center; margin-bottom: 20px;">Payment Receipt</h2>
              <div style="display: flex; align-items: center; margin-bottom: 20px;">
                <img id="printThumbnail" style="width: 60px; height: 60px; border-radius: 8px; margin-right: 10px; display: none;" src="" alt="Course Thumbnail">
                <div>
                  <h3 id="printCourse" style="margin: 0; font-size: 18px;"></h3>
                  <span id="printStatus" style="display: inline-block; padding: 5px 10px; border-radius: 15px; font-size: 12px; margin-top: 5px;"></span>
                </div>
              </div>
              <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                <h4 style="margin-bottom: 10px;">Transaction Details</h4>
                <p style="margin: 5px 0;"><strong>Transaction ID:</strong> <span id="printTransactionId"></span></p>
                <p style="margin: 5px 0;"><strong>Date:</strong> <span id="printDate"></span></p>
                <p style="margin: 5px 0;"><strong>Time:</strong> <span id="printTime"></span></p>
                <p style="margin: 5px 0;"><strong>Amount:</strong> <span id="printAmount"></span></p>
                <p style="margin: 5px 0;"><strong>Payment Method:</strong> <span id="printMethod"></span></p>
              </div>
              <p style="text-align: center; margin-top: 20px; font-size: 12px; color: #666;">Thank you for your payment!</p>
            </div>
          </div>
        </div>
      </div>
      <!-- End Col -->
    </div>
    <!-- End Row -->
  </div>
  <!-- End Content -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- Tailwind CSS CDN -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

<!-- Bootstrap JS for Modal -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom CSS -->
<style>
  .card {
    border-radius: 0.75rem;
    overflow: hidden;
  }

  .payment-item {
    transition: all 0.3s ease;
    height: 12rem;
    /* Fixed height for uniformity */
  }

  .payment-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }

  .form-control,
  .form-select {
    border-radius: 0.5rem;
    padding: 0.5rem 1rem;
    transition: all 0.2s ease;
  }

  .form-control:placeholder-shown {
    background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg>') no-repeat 10px center;
    background-size: 16px;
    padding-left: 2.5rem;
  }

  .btn {
    border-radius: 0.5rem;
    transition: all 0.2s ease;
  }

  .badge {
    padding: 0.4em 0.8em;
    font-weight: 500;
    font-size: 0.75rem;
  }

  .modal-content {
    border-radius: 0.75rem;
  }

  @media (max-width: 767.98px) {
    #paymentList {
      grid-template-columns: 1fr !important;
    }

    .payment-item {
      height: auto;
      /* Allow flexibility on mobile */
    }
  }

  @media print {
    body * {
      visibility: hidden;
    }

    #printableReceipt,
    #printableReceipt * {
      visibility: visible;
    }

    #printableReceipt {
      display: block !important;
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
    }

    #printThumbnail {
      display: block !important;
    }
  }
</style>

<!-- JavaScript for Payment Interactivity -->
<script>
  let currentPage = 1;
  const paymentsPerPage = 9;

  // Update summary
  function updateSummary(filteredPayments) {
    const totalSpent = filteredPayments.reduce((sum, p) => sum + parseFloat(p.dataset.amount), 0);
    const totalTransactions = filteredPayments.length;
    document.getElementById('totalSpent').textContent = `GHS ${totalSpent.toFixed(2)}`;
    document.getElementById('totalTransactions').textContent = totalTransactions;
  }

  // Render payments
  function renderPayments(filteredPayments) {
    const paymentList = document.getElementById('paymentList');
    const emptyPayments = document.getElementById('emptyPayments');

    // Pagination logic
    const startIndex = (currentPage - 1) * paymentsPerPage;
    const paginatedPayments = filteredPayments.slice(startIndex, startIndex + paymentsPerPage);

    if (paginatedPayments.length === 0 && filteredPayments.length === 0) {
      emptyPayments.classList.remove('hidden');
      paymentList.classList.add('hidden');
      updateSummary([]);
      return;
    }

    emptyPayments.classList.add('hidden');
    paymentList.classList.remove('hidden');

    // Update pagination and summary
    updatePagination(filteredPayments.length);
    updateSummary(filteredPayments);
  }

  // Update pagination
  function updatePagination(totalPayments) {
    const paginationContainer = document.getElementById('pagination');
    if (totalPayments < 9) {
      paginationContainer.style.display = 'none';
      return;
    }
    paginationContainer.style.display = 'block';

    const totalPages = Math.ceil(totalPayments / paymentsPerPage);
    const pagination = document.querySelector('.pagination');
    pagination.innerHTML = `
      <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="changePage(currentPage - 1)">Previous</a>
      </li>
    `;
    for (let i = 1; i <= totalPages; i++) {
      pagination.innerHTML += `
        <li class="page-item ${i === currentPage ? 'active' : ''}">
          <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
        </li>
      `;
    }
    pagination.innerHTML += `
      <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="changePage(currentPage + 1)">Next</a>
      </li>
    `;
  }

  // Change page
  function changePage(page) {
    const totalPayments = document.querySelectorAll('#paymentList .payment-item:not([style*="display: none"])').length;
    const totalPages = Math.ceil(totalPayments / paymentsPerPage);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    filterPayments();
  }

  // Filter and sort payments
  function filterPayments() {
    const searchQuery = document.getElementById('searchPayments').value.toLowerCase();
    const filterStatus = document.getElementById('filterStatus').value.toLowerCase();
    const sortBy = document.getElementById('sortBy').value;

    const paymentItems = Array.from(document.querySelectorAll('#paymentList .payment-item'));
    let filteredPayments = paymentItems;

    // Filtering
    filteredPayments = paymentItems.filter(item => {
      const matchesStatus = filterStatus === 'all' || item.dataset.status === filterStatus;
      const matchesSearch = !searchQuery ||
        item.dataset.course.toLowerCase().includes(searchQuery) ||
        item.dataset.transactionId.toLowerCase().includes(searchQuery);
      return matchesStatus && matchesSearch;
    });

    // Sorting
    filteredPayments.sort((a, b) => {
      if (sortBy === 'date-desc') return new Date(b.dataset.date) - new Date(a.dataset.date);
      if (sortBy === 'date-asc') return new Date(a.dataset.date) - new Date(b.dataset.date);
      if (sortBy === 'amount-desc') return parseFloat(b.dataset.amount) - parseFloat(a.dataset.amount);
      if (sortBy === 'amount-asc') return parseFloat(a.dataset.amount) - parseFloat(b.dataset.amount);
      return 0;
    });

    // Show/hide payments
    paymentItems.forEach(item => {
      item.style.display = 'none';
    });

    const startIndex = (currentPage - 1) * paymentsPerPage;
    const paginatedPayments = filteredPayments.slice(startIndex, startIndex + paymentsPerPage);
    paginatedPayments.forEach(item => {
      item.style.display = 'block';
    });

    renderPayments(filteredPayments);
  }

  // Reset filters
  function resetFilters() {
    document.getElementById('searchPayments').value = '';
    document.getElementById('filterStatus').value = 'all';
    document.getElementById('sortBy').value = 'date-desc';
    currentPage = 1;
    const paymentItems = document.querySelectorAll('#paymentList .payment-item');
    paymentItems.forEach(item => {
      item.style.display = 'block';
    });
    renderPayments(paymentItems);
  }

  // View receipt in modal and populate printable receipt
  function viewReceipt(button) {
    const id = button.dataset.id;
    const thumbnail = button.dataset.thumbnail;
    const thumbnailDiv = document.getElementById('modalThumbnail');
    const printThumbnail = document.getElementById('printThumbnail');

    // Populate modal
    if (thumbnail) {
      thumbnailDiv.innerHTML = `<img class="w-16 h-16 rounded object-cover" src="${thumbnail}" alt="Course Thumbnail">`;
      printThumbnail.src = thumbnail;
      printThumbnail.style.display = 'block';
    } else {
      thumbnailDiv.innerHTML = `<span class="text-xl">${button.dataset.course.charAt(0)}</span>`;
      printThumbnail.style.display = 'none';
    }

    document.getElementById('modalCourse').textContent = button.dataset.course;
    document.getElementById('modalTransactionId').textContent = button.dataset.transactionId;
    document.getElementById('modalDate').textContent = button.dataset.date;
    document.getElementById('modalTime').textContent = button.dataset.time;
    document.getElementById('modalAmount').textContent = button.dataset.amount;
    document.getElementById('modalMethod').textContent = button.dataset.method;

    const statusBadge = document.getElementById('modalStatus');
    statusBadge.className = `badge bg-${button.dataset.status === 'Completed' ? 'success' : (button.dataset.status === 'Pending' ? 'warning' : 'danger')} bg-opacity-10 text-${button.dataset.status === 'Completed' ? 'success' : (button.dataset.status === 'Pending' ? 'warning' : 'danger')} rounded-pill`;
    statusBadge.textContent = button.dataset.status;

    // Populate printable receipt
    document.getElementById('printCourse').textContent = button.dataset.course;
    document.getElementById('printTransactionId').textContent = button.dataset.transactionId;
    document.getElementById('printDate').textContent = button.dataset.date;
    document.getElementById('printTime').textContent = button.dataset.time;
    document.getElementById('printAmount').textContent = button.dataset.amount;
    document.getElementById('printMethod').textContent = button.dataset.method;
    const printStatus = document.getElementById('printStatus');
    printStatus.className = `badge bg-${button.dataset.status === 'Completed' ? 'success' : (button.dataset.status === 'Pending' ? 'warning' : 'danger')} bg-opacity-10 text-${button.dataset.status === 'Completed' ? 'success' : (button.dataset.status === 'Pending' ? 'warning' : 'danger')} rounded-pill`;
    printStatus.textContent = button.dataset.status;

    const modal = new bootstrap.Modal(document.getElementById('receiptDetailsModal'));
    modal.show();
  }

  // Print receipt
  function printReceipt() {
    // Ensure printable receipt is populated and visible for printing
    const printableReceipt = document.getElementById('printableReceipt');
    printableReceipt.style.display = 'block';

    // Trigger print
    window.print();

    // Hide printable receipt after printing
    printableReceipt.style.display = 'none';
  }

  /* Comment out refund functionality for future implementation
  // Request refund
  function requestRefund(button) {
    const id = button.dataset.id;
    const course = button.dataset.course;
    const transactionId = button.dataset.transactionId;
    window.showAlert('warning', `Refund request initiated for ${course} (Transaction ID: ${transactionId}).`);
    // Implement AJAX call to backend endpoint for refund request
  }
  */

  // Custom Alert function
  window.showAlert = function(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.setAttribute('role', 'alert');
    alertDiv.innerHTML = `
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '300px';
    alertDiv.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
    document.body.appendChild(alertDiv);
    setTimeout(() => {
      const bsAlert = new bootstrap.Alert(alertDiv);
      bsAlert.close();
    }, 5000);
  };

  // Initial render
  document.addEventListener('DOMContentLoaded', function() {
    const paymentItems = document.querySelectorAll('#paymentList .payment-item');
    renderPayments(Array.from(paymentItems));
  });
</script>

<!-- ========== FOOTER ========== -->
<?php include '../includes/student-footer.php'; ?>
<!-- ========== END FOOTER ========== -->