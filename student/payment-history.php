<?php include '../includes/student-header.php'; ?>

<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main" class="bg-light">
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
            <div class="card flex-grow-1 mb-5">
              <div class="card-body">
                <!-- Avatar -->
                <div class="d-none d-lg-block text-center mb-5">
                  <div class="avatar avatar-xxl avatar-circle mb-3">
                    <div class="flex-shrink-0">
                      <img class="avatar avatar-xl avatar-circle"
                        src="../uploads/profile/<?php echo $row['profile_pic'] ?>"
                        alt="Profile">
                    </div>
                  </div>
                  <h4 class="card-title mb-0"><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></h4>
                  <p class="card-text small"><?php echo $row['email']; ?></p>
                </div>
                <!-- End Avatar -->

                <!-- Sidebar Content -->

                <!-- Overview Section -->
                <span class="text-cap">Overview</span>
                <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                  <li class="nav-item">
                    <a class="nav-link" href="account-overview.php">
                      <i class="bi-person-circle nav-icon"></i> Account Overview
                    </a>
                  </li>
                </ul>

                <!-- Account Section -->
                <span class="text-cap">Account</span>
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
                      <span class="badge bg-soft-dark text-dark rounded-pill nav-link-badge">0</span>
                    </a>
                  </li>
                </ul>

                <!-- Student-Specific Section -->
                <span class="text-cap">My Courses</span>
                <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                  <li class="nav-item">
                    <a class="nav-link" href="my-courses.php">
                      <i class="bi-person-badge nav-icon"></i> Enrolled Courses
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="my-badges.php">
                      <i class="bi-chat-dots nav-icon"></i> Badges
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="my-certifications.php">
                      <i class="bi-award nav-icon"></i> Certifications
                    </a>
                  </li>
                  <!-- <li class="nav-item">
                    <a class="nav-link" href="course-progress.php">
                      <i class="bi-bar-chart-line nav-icon"></i> Course Progress
                    </a>
                  </li> -->
                </ul>

                <!-- Payment Section for Students -->
                <span class="text-cap">Payments</span>
                <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                  <li class="nav-item">
                    <a class="nav-link active" href="payment-history.php">
                      <i class="bi-credit-card nav-icon"></i> Payment History
                    </a>
                  </li>
                  <!-- <li class="nav-item">
                    <a class="nav-link" href="payment-method.php">
                      <i class="bi-wallet nav-icon"></i> Payment Methods
                    </a>
                  </li> -->
                </ul>

                <!-- Instructor/Admin Section (Dynamic Role Check) -->
                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'instructor'): ?>
                  <span class="text-cap">Instructor</span>
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

                <!-- Sign-out & Help Section -->
                <span class="text-cap">---</span>
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

                <!-- End of Sidebar -->

              </div>
            </div>
            <!-- End Card -->
          </div>
        </div>
        <!-- End Navbar -->
      </div>
      <!-- End Col -->

      <div class="col-lg-9">
        <div class="d-grid gap-3 gap-lg-5">
          <!-- Card -->
          <div class="card">
            <div class="card-header border-bottom">
              <div class="row justify-content-between align-items-center flex-grow-1">
                <div class="col-md">
                  <h4 class="card-header-title">Payment History</h4>
                </div>

                <div class="col-auto">
                  <!-- Date Filter -->
                  <div class="d-flex align-items-center gap-2">
                    <label for="dateFilter" class="form-label mb-0">Filter by:</label>
                    <select class="form-select form-select-sm" id="dateFilter">
                      <option value="all">All time</option>
                      <option value="today">Today</option>
                      <option value="week">This week</option>
                      <option value="month">This month</option>
                      <option value="year">This year</option>
                    </select>
                  </div>
                  <!-- End Date Filter -->
                </div>
              </div>
            </div>

            <!-- Table -->
            <div class="table-responsive datatable-custom">
              <?php
              // Database connection
              require_once '../backend/config.php';

              // Get current user ID from session
              $user_id = $_SESSION['user_id'];

              // Query to get payment history for the current user
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
              ?>

              <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table" id="paymentsTable">
                <thead class="thead-light">
                  <tr>
                    <th>Course</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Actions</th>
                  </tr>
                </thead>

                <tbody>
                  <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                      <tr data-date="<?php echo date('Y-m-d', strtotime($row['payment_date'])); ?>">
                        <td>
                          <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                              <?php if ($row['thumbnail']): ?>
                                <img class="avatar avatar-lg" src="../uploads/thumbnails/<?php echo $row['thumbnail']; ?>" alt="Course Thumbnail">
                              <?php else: ?>
                                <div class="avatar avatar-soft-primary avatar-lg">
                                  <span class="avatar-initials"><?php echo substr($row['course_title'], 0, 1); ?></span>
                                </div>
                              <?php endif; ?>
                            </div>
                            <div class="ms-3">
                              <span class="d-block h5 mb-0 text-truncate" style="max-width: 250px;" title="<?php echo $row['course_title']; ?>"><?php echo $row['course_title']; ?></span>
                              <span class="d-block fs-6 text-body">Purchase #<?php echo $row['payment_id']; ?></span>
                            </div>
                          </div>
                        </td>
                        <td>
                          <?php echo date('M d, Y', strtotime($row['payment_date'])); ?>
                          <span class="d-block fs-6 text-body"><?php echo date('h:i A', strtotime($row['payment_date'])); ?></span>
                        </td>
                        <td>
                          <span class="d-block h5 mb-0"><?php echo $row['currency']; ?> <?php echo number_format($row['amount'], 2); ?></span>
                        </td>
                        <td>
                          <div class="dropdown">
                            <button type="button" class="btn btn-ghost-secondary btn-icon btn-sm rounded-circle" id="paymentRow<?php echo $row['payment_id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                              <i class="bi-three-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="paymentRow<?php echo $row['payment_id']; ?>">
                              <a class="dropdown-item" href="#" data-payment-id="<?php echo $row['payment_id']; ?>" data-bs-toggle="modal" data-bs-target="#viewReceiptModal">
                                <i class="bi-file-earmark-text dropdown-item-icon"></i> View receipt
                              </a>
                              <?php if ($row['status'] === 'Completed'): ?>
                                <a class="dropdown-item" href="course-materials.php?course_id=<?php echo $row['course_id']; ?>">
                                  <i class="bi-play-circle dropdown-item-icon"></i> Go to course
                                </a>
                              <?php endif; ?>
                            </div>
                          </div>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="4" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center">
                          <img class="mb-3" src="../assets/svg/illustrations/oc-money-profits.svg" alt="Empty Payments" width="200">
                          <h4 class="mb-2">No payment records found</h4>
                          <p class="mb-3">You haven't made any payments yet. Check out our course catalog to find courses that interest you.</p>
                          <a href="../courses.php" class="btn btn-primary">Browse Courses</a>
                        </div>
                      </td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
            <!-- End Table -->

            <!-- Footer -->
            <?php if ($result->num_rows > 10): ?>
            <div class="card-footer">
              <div class="d-flex justify-content-center">
                <nav aria-label="Page navigation">
                  <ul class="pagination">
                    <li class="page-item disabled">
                      <a class="page-link" href="#" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                      </a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                      <a class="page-link" href="#" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                      </a>
                    </li>
                  </ul>
                </nav>
              </div>
            </div>
            <?php endif; ?>
            <!-- End Footer -->
          </div>
          <!-- End Card -->

          <!-- Receipt Modal -->
          <div class="modal fade" id="viewReceiptModal" tabindex="-1" aria-labelledby="viewReceiptModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="viewReceiptModalLabel">Payment Receipt</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div id="receiptContent" class="p-4">
                    <!-- Receipt content will be loaded here via AJAX -->
                    <div class="text-center">
                      <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                      </div>
                      <p>Loading receipt...</p>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-white" data-bs-dismiss="modal">Close</button>
                  <button type="button" class="btn btn-primary" id="printReceiptBtn"><i class="bi-printer me-1"></i> Print</button>
                </div>
              </div>
            </div>
          </div>
          <!-- End Receipt Modal -->

        </div>
      </div>
      <!-- End Col -->

    </div>
    <!-- End Row -->
  </div>
  <!-- End Content -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- JS for Receipt Viewing and Date Filtering -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Date Filter Functionality
    const dateFilter = document.getElementById('dateFilter');
    const table = document.getElementById('paymentsTable');
    const tableRows = table.querySelectorAll('tbody tr');
    
    dateFilter.addEventListener('change', function() {
      const filterValue = this.value;
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      
      const weekStart = new Date(today);
      weekStart.setDate(today.getDate() - today.getDay());
      
      const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
      
      const yearStart = new Date(today.getFullYear(), 0, 1);
      
      let hasVisibleRows = false;
      
      tableRows.forEach(row => {
        if (!row.dataset.date) {
          row.style.display = '';
          return;
        }
        
        const rowDate = new Date(row.dataset.date);
        rowDate.setHours(0, 0, 0, 0);
        
        let showRow = false;
        
        switch(filterValue) {
          case 'all':
            showRow = true;
            break;
          case 'today':
            showRow = rowDate.getTime() === today.getTime();
            break;
          case 'week':
            showRow = rowDate >= weekStart;
            break;
          case 'month':
            showRow = rowDate >= monthStart;
            break;
          case 'year':
            showRow = rowDate >= yearStart;
            break;
        }
        
        row.style.display = showRow ? '' : 'none';
        if (showRow) hasVisibleRows = true;
      });
      
      // Show empty state message if no matching rows
      const emptyRow = document.querySelector('.empty-filter-result');
      if (!hasVisibleRows && !emptyRow && tableRows.length > 0) {
        const tbody = table.querySelector('tbody');
        const newRow = document.createElement('tr');
        newRow.className = 'empty-filter-result';
        newRow.innerHTML = `
          <td colspan="4" class="text-center py-5">
            <div class="d-flex flex-column align-items-center">
              <img class="mb-3" src="../assets/svg/illustrations/oc-money-profits.svg" alt="No Results" width="200">
              <h4 class="mb-2">No payments found for this time period</h4>
              <p class="mb-0">Try selecting a different time range or view all payments.</p>
            </div>
          </td>
        `;
        tbody.appendChild(newRow);
      } else if (hasVisibleRows && emptyRow) {
        emptyRow.remove();
      }
    });
    
    // View Receipt Modal
    var viewReceiptModal = document.getElementById('viewReceiptModal');
    if (viewReceiptModal) {
      viewReceiptModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var paymentId = button.getAttribute('data-payment-id');
        
        // Load receipt content via AJAX
        // In a real implementation, this would be an AJAX call to load receipt details
        // For demo purposes, we're simulating with setTimeout
        setTimeout(function() {
          document.getElementById('receiptContent').innerHTML = `
            <div class="text-center mb-4">
              <h3 class="mb-0">Learnix</h3>
              <p class="fs-5">Payment Receipt</p>
            </div>
            
            <div class="row mb-4">
              <div class="col-6">
                <p class="mb-1"><strong>Receipt #:</strong> ${paymentId}</p>
                <p class="mb-1"><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
                <p class="mb-0"><strong>Payment Method:</strong> Credit Card</p>
              </div>
              <div class="col-6 text-end">
                <p class="mb-1"><strong>Learnix Inc.</strong></p>
                <p class="mb-1">123 Education Lane</p>
                <p class="mb-0">Learning City, ED 12345</p>
              </div>
            </div>
            
            <hr>
            
            <div class="row mb-4">
              <div class="col-12">
                <h5>Item Details</h5>
                <table class="table">
                  <thead>
                    <tr>
                      <th>Description</th>
                      <th class="text-end">Amount</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>Course: Introduction to Web Development</td>
                      <td class="text-end">$49.99</td>
                    </tr>
                    <tr>
                      <td class="text-end"><strong>Total</strong></td>
                      <td class="text-end"><strong>$49.99</strong></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            
            <div class="text-center mt-4 pt-4 border-top">
              <p class="mb-0">Thank you for your purchase!</p>
              <p class="small text-muted">For any questions, please contact support@learnix.com</p>
            </div>
          `;
        }, 1000);
      });
    }
    
    // Print Receipt Button with fixed modal close issue
    document.getElementById('printReceiptBtn').addEventListener('click', function() {
      var printContents = document.getElementById('receiptContent').innerHTML;
      var originalContents = document.body.innerHTML;
      var modalElement = document.getElementById('viewReceiptModal');
      
      // Store modal instance before destroying the DOM
      var modalInstance = bootstrap.Modal.getInstance(modalElement);
      
      // Hide modal before printing to avoid issues
      modalInstance.hide();
      
      // Set print content
      document.body.innerHTML = `
        <div style="max-width: 800px; margin: 0 auto; padding: 20px;">
          ${printContents}
        </div>
      `;
      
      // Print
      window.print();
      
      // Restore original content
      document.body.innerHTML = originalContents;
      
      // Re-initialize all elements and event listeners
      var bsModal = new bootstrap.Modal(document.getElementById('viewReceiptModal'));
      bsModal.show();
      
      // Re-attach this event listener since the DOM was rebuilt
      document.getElementById('printReceiptBtn').addEventListener('click', arguments.callee);
      
      // Re-attach date filter event listener
      const newDateFilter = document.getElementById('dateFilter');
      if (newDateFilter) {
        newDateFilter.addEventListener('change', function() {
          // Re-implement the date filter logic
          // This is a simplified version - in production, you might want to extract this to a separate function
          const filterValue = this.value;
          const tableRows = document.querySelectorAll('#paymentsTable tbody tr:not(.empty-filter-result)');
          
          tableRows.forEach(row => {
            if (filterValue === 'all' || !row.dataset.date) {
              row.style.display = '';
            } else {
              // Simple check - in production implement full date comparison logic
              row.style.display = (filterValue === 'all') ? '' : 'none';
            }
          });
        });
      }
    });
    
    // Custom Alert function
    window.showAlert = function(type, message) {
      const alertDiv = document.createElement('div');
      alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
      alertDiv.setAttribute('role', 'alert');
      alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      `;
      
      // Style the alert
      alertDiv.style.top = '20px';
      alertDiv.style.right = '20px';
      alertDiv.style.zIndex = '9999';
      alertDiv.style.minWidth = '300px';
      alertDiv.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
      
      document.body.appendChild(alertDiv);
      
      // Auto-dismiss after 5 seconds
      setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alertDiv);
        bsAlert.close();
      }, 5000);
    };
  });
</script>

<!-- ========== FOOTER ========== -->
<?php include '../includes/student-footer.php'; ?>
<!-- ========== END FOOTER ========== -->