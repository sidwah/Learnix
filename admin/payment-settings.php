<?php
// Authentication check
require_once '../backend/auth/admin/admin-auth-check.php';

// Set page title
$pageTitle = "Payment Settings - Admin | Learnix";

include_once '../includes/admin/header.php';

// <!-- Menu -->
include_once '../includes/admin/sidebar.php';
// <!-- / Menu -->

// <!-- Navbar -->
include_once '../includes/admin/navbar.php';
// <!-- / Navbar -->

// Get data from database
require_once '../backend/config.php';

// Fetch current revenue settings
$query = "SELECT * FROM revenue_settings";
$result = mysqli_query($conn, $query);
$settings = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $settings[$row['setting_name']] = $row;
    }
}

// Check for flash messages
// session_start();
$alertMessage = null;
$alertType = 'info';

if (isset($_SESSION['payment_settings_message'])) {
    $alertMessage = $_SESSION['payment_settings_message']['message'];
    $alertType = $_SESSION['payment_settings_message']['status'];
    unset($_SESSION['payment_settings_message']);
}
?>

<!-- Toast Notification -->
<?php if ($alertMessage): ?>
<div class="bs-toast toast toast-placement-ex m-2 fade bg-<?php echo $alertType == 'error' ? 'danger' : ($alertType == 'success' ? 'success' : 'info'); ?> top-0 end-0 show" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000" id="notificationToast" style="z-index: 9999; position: fixed;">
  <div class="toast-header">
    <i class="bx <?php echo $alertType == 'error' ? 'bx-bell' : ($alertType == 'success' ? 'bx-check' : 'bx-info-circle'); ?> me-2"></i>
    <div class="me-auto fw-semibold"><?php echo ucfirst($alertType); ?></div>
    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
  </div>
  <div class="toast-body"><?php echo $alertMessage; ?></div>
</div>
<?php endif; ?>
<!-- /Toast Notification -->

<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Admin /</span> Payment Settings
  </h4>

  <div class="row">
    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header">Revenue Configuration</h5>
        <div class="card-body">
          <form method="POST" action="../backend/admin/update-revenue-settings.php" id="revenueSettingsForm">
            <div class="row mb-4">
              <div class="col-md-6">
                <div class="card bg-primary text-white">
                  <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                      <div class="me-3">
                        <i class="bx bx-money bx-lg"></i>
                      </div>
                      <div>
                        <h5 class="card-title text-white mb-0">Revenue Sharing</h5>
                        <p class="card-text mb-0">Define how course revenue is split between instructors and the platform</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card bg-secondary text-white">
                  <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                      <div class="me-3">
                        <i class="bx bx-time bx-lg"></i>
                      </div>
                      <div>
                        <h5 class="card-title text-white mb-0">Payout Configuration</h5>
                        <p class="card-text mb-0">Set holding periods and minimum payout thresholds</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row mb-4">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="instructorSplit" class="form-label">Instructor Revenue Share (%)</label>
                  <div class="input-group">
                    <input type="number" class="form-control" id="instructorSplit" name="instructor_split" 
                           value="<?php echo isset($settings['instructor_split']) ? $settings['instructor_split']['setting_value'] : 80; ?>"
                           min="0" max="100" step="0.01" required>
                    <span class="input-group-text">%</span>
                  </div>
                  <div class="form-text">Percentage of course revenue paid to instructors</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="platformFee" class="form-label">Platform Fee (%)</label>
                  <div class="input-group">
                    <input type="number" class="form-control" id="platformFee" name="platform_fee" 
                           value="<?php echo isset($settings['platform_fee']) ? $settings['platform_fee']['setting_value'] : 20; ?>"
                           min="0" max="100" step="0.01" required>
                    <span class="input-group-text">%</span>
                  </div>
                  <div class="form-text">Percentage of course revenue retained by the platform</div>
                </div>
              </div>
            </div>

            <div class="alert alert-info" id="percentageAlert">
              <div class="d-flex">
                <div class="me-3">
                  <i class="bx bx-info-circle bx-sm mt-1"></i>
                </div>
                <div>
                  <h6 class="alert-heading mb-1">Revenue Split Information</h6>
                  <p class="mb-0">The instructor share and platform fee must total exactly 100%. This will be the default split offered when initiating courses.</p>
                </div>
              </div>
            </div>

            <div class="row mt-4">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="holdingPeriod" class="form-label">Holding Period (Days)</label>
                  <div class="input-group">
                    <input type="number" class="form-control" id="holdingPeriod" name="holding_period" 
                           value="<?php echo isset($settings['holding_period']) ? $settings['holding_period']['setting_value'] : 30; ?>"
                           min="0" step="1" required>
                    <span class="input-group-text">days</span>
                  </div>
                  <div class="form-text">Number of days before earnings become available for withdrawal</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="minimumPayout" class="form-label">Minimum Payout Amount</label>
                  <div class="input-group">
                    <span class="input-group-text">GHS</span>
                    <input type="number" class="form-control" id="minimumPayout" name="minimum_payout" 
                           value="<?php echo isset($settings['minimum_payout']) ? $settings['minimum_payout']['setting_value'] : 50; ?>"
                           min="0" step="0.01" required>
                  </div>
                  <div class="form-text">Minimum balance required for instructor withdrawal</div>
                </div>
              </div>
            </div>
            
            <div class="row mt-3">
              <div class="col-12">
                <div class="mb-3">
                  <label for="changeReason" class="form-label">Reason for Changes (Optional)</label>
                  <textarea class="form-control" id="changeReason" name="change_reason" rows="2" placeholder="Briefly explain why you're changing these settings"></textarea>
                  <div class="form-text">This will be recorded in the change history for auditing purposes</div>
                </div>
              </div>
            </div>

            <div class="row mt-4">
              <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                  <p class="mb-0 text-muted">Last updated: 
                    <span class="fw-semibold">
                      <?php echo isset($settings['instructor_split']) ? date('F d, Y H:i', strtotime($settings['instructor_split']['updated_at'])) : 'Never'; ?>
                    </span>
                  </p>
                  <button type="submit" class="btn btn-primary">
                    <i class="bx bx-save me-1"></i> Save Changes
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header">Revenue Settings History</h5>
        <div class="table-responsive text-nowrap">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Date & Time</th>
                <th>Setting</th>
                <th>Previous Value</th>
                <th>New Value</th>
                <th>Changed By</th>
                <th>Reason</th>
              </tr>
            </thead>
            <tbody class="table-border-bottom-0">
              <?php 
              // Fetch history from the dedicated history table
              $history_query = "SELECT h.*, u.first_name, u.last_name 
                               FROM revenue_settings_history h
                               JOIN users u ON h.changed_by = u.user_id
                               ORDER BY h.change_date DESC
                               LIMIT 15";
              $history_result = mysqli_query($conn, $history_query);
              
              if ($history_result && mysqli_num_rows($history_result) > 0) {
                  while ($history = mysqli_fetch_assoc($history_result)) {
                      $setting_display_name = ucwords(str_replace('_', ' ', $history['setting_name']));
                      echo '<tr>';
                      echo '<td>' . date('M d, Y H:i', strtotime($history['change_date'])) . '</td>';
                      echo '<td>' . htmlspecialchars($setting_display_name) . '</td>';
                      
                      // Format display based on setting type
                      if ($history['setting_name'] == 'holding_period') {
                          $previous = (int)$history['previous_value'] . ' days';
                          $new = (int)$history['new_value'] . ' days';
                      } else if ($history['setting_name'] == 'minimum_payout') {
                          $previous = 'GHS ' . number_format($history['previous_value'], 2);
                          $new = 'GHS ' . number_format($history['new_value'], 2);
                      } else {
                          $previous = number_format($history['previous_value'], 2) . '%';
                          $new = number_format($history['new_value'], 2) . '%';
                      }
                      
                      echo '<td>' . $previous . '</td>';
                      echo '<td>' . $new . '</td>';
                      echo '<td>' . htmlspecialchars($history['first_name'] . ' ' . $history['last_name']) . '</td>';
                      echo '<td>' . (empty($history['change_reason']) ? '<em class="text-muted">No reason provided</em>' : htmlspecialchars($history['change_reason'])) . '</td>';
                      echo '</tr>';
                  }
              } else {
                  echo '<tr><td colspan="6" class="text-center">No history available</td></tr>';
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- / Content -->

<script>
  document.addEventListener('DOMContentLoaded', function() {
    <?php if ($alertMessage): ?>
    // Auto hide the toast after 5 seconds
    setTimeout(function() {
      const toast = document.getElementById('notificationToast');
      const bsToast = bootstrap.Toast.getInstance(toast);
      if (bsToast) {
        bsToast.hide();
      }
    }, 5000);
    <?php endif; ?>
    
    // Validation for instructor split and platform fee
    const instructorSplitInput = document.getElementById('instructorSplit');
    const platformFeeInput = document.getElementById('platformFee');
    const percentageAlert = document.getElementById('percentageAlert');
    
    function validatePercentages() {
      const instructorSplit = parseFloat(instructorSplitInput.value) || 0;
      const platformFee = parseFloat(platformFeeInput.value) || 0;
      const total = instructorSplit + platformFee;
      
      if (Math.abs(total - 100) > 0.01) { // Allow small floating point error
        percentageAlert.classList.remove('alert-info');
        percentageAlert.classList.add('alert-danger');
        percentageAlert.querySelector('p').textContent = `The total must be exactly 100%. Current total: ${total.toFixed(2)}%`;
        return false;
      } else {
        percentageAlert.classList.remove('alert-danger');
        percentageAlert.classList.add('alert-info');
        percentageAlert.querySelector('p').textContent = 'The instructor share and platform fee must total exactly 100%. This will be the default split offered when initiating courses.';
        return true;
      }
    }
    
    // Auto-calculate platform fee when instructor split changes
    instructorSplitInput.addEventListener('input', function() {
      const instructorSplit = parseFloat(this.value) || 0;
      platformFeeInput.value = (100 - instructorSplit).toFixed(2);
      validatePercentages();
    });
    
    // Auto-calculate instructor split when platform fee changes
    platformFeeInput.addEventListener('input', function() {
      const platformFee = parseFloat(this.value) || 0;
      instructorSplitInput.value = (100 - platformFee).toFixed(2);
      validatePercentages();
    });
    
    // Form validation
    document.getElementById('revenueSettingsForm').addEventListener('submit', function(e) {
      if (!validatePercentages()) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        // Create a toast message for the error
        const toast = document.createElement('div');
        toast.className = 'bs-toast toast toast-placement-ex m-2 fade bg-danger top-0 end-0 show';
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        toast.setAttribute('data-delay', '5000');
        toast.style.zIndex = '9999';
        toast.style.position = 'fixed';
        
        toast.innerHTML = `
          <div class="toast-header">
            <i class="bx bx-bell me-2"></i>
            <div class="me-auto fw-semibold">Error</div>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
          <div class="toast-body">Total percentage must be exactly 100%</div>
        `;
        
        document.body.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        setTimeout(() => {
          bsToast.hide();
          setTimeout(() => {
            document.body.removeChild(toast);
          }, 500);
        }, 5000);
      }
    });
    
    // Initialize validation on page load
    validatePercentages();
  });
</script>

<?php include_once '../includes/admin/footer.php'; ?>