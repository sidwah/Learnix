<?php
//department/invite-instructor.php
// Set page title for the header
$pageTitle = "Invite Instructor";
$currentPage = "invite-instructor";

// Include header with navigation
include '../includes/department/header.php';

// Get department info from session
$departmentId = $_SESSION['department_id'];
$departmentName = $_SESSION['department_name'];

// Connect to database to get additional info
require_once '../backend/config.php';
$conn = new mysqli($host, $username, $password, $db_name);

// Get pending invitations count
$pendingCount = 0;
$stmt = $conn->prepare("SELECT COUNT(*) FROM instructor_invitations 
                        WHERE department_id = ? AND is_used = 0 AND expiry_time > NOW()");
$stmt->bind_param("i", $departmentId);
$stmt->execute();
$stmt->bind_result($pendingCount);
$stmt->fetch();
$stmt->close();

// Get active instructors count
$activeInstructorsCount = 0;
$stmt = $conn->prepare("SELECT COUNT(*) FROM department_instructors di 
                        INNER JOIN instructors i ON di.instructor_id = i.instructor_id 
                        INNER JOIN users u ON i.user_id = u.user_id 
                        WHERE di.department_id = ? AND di.status = 'active' AND di.deleted_at IS NULL
                        AND u.status = 'active' AND u.deleted_at IS NULL");
$stmt->bind_param("i", $departmentId);
$stmt->execute();
$stmt->bind_result($activeInstructorsCount);
$stmt->fetch();
$stmt->close();

// Get pending invitations for display
$pendingInvitations = [];
$stmt = $conn->prepare("SELECT ii.id, ii.email, ii.invited_by, ii.created_at, ii.expiry_time, 
                       CONCAT(u.first_name, ' ', u.last_name) as invited_by_name 
                       FROM instructor_invitations ii 
                       INNER JOIN users u ON ii.invited_by = u.user_id 
                       WHERE ii.department_id = ? AND ii.is_used = 0 AND ii.expiry_time > NOW()
                       ORDER BY ii.created_at DESC");
$stmt->bind_param("i", $departmentId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $pendingInvitations[] = $row;
}

$invitationExpiryHours = 48; // Default fallback

// Get department settings for invitation expiry
$stmt = $conn->prepare("SELECT invitation_expiry_hours FROM department_settings WHERE department_id = ?");
$stmt->bind_param("i", $departmentId);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $invitationExpiryHours = $row['invitation_expiry_hours'] ?? 48;
}
$stmt->close();

$conn->close();
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
        <!-- Toast -->
        <div id="liveToast" class="position-fixed toast hide" role="alert" aria-live="assertive" aria-atomic="true" style="top: 20px; right: 20px; z-index: 1000;">
            <div class="toast-header">
                <div class="d-flex align-items-center flex-grow-1">
                    <div id="toastIcon" class="flex-shrink-0 rounded-circle bg-success bg-opacity-10 text-success p-2 d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px;">
                        <i class="bi bi-check-lg fs-6"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 id="toastTitle" class="mb-0">System Notification</h5>
                        <small id="toastTime">Just Now</small>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            </div>
            <div id="toastBody" class="toast-body">
                Hello, world! This is a toast message.
            </div>
        </div>
        <!-- End Toast -->

        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="page-header-title">Invite Instructor</h1>
                    <p class="page-header-text">Invite a new instructor to join your department: <?php echo htmlspecialchars($departmentName); ?></p>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row">
            <!-- Invite Form -->
            <div class="col-lg-7 mb-5 mb-lg-0">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-header-title">New Instructor Invitation</h4>
                    </div>
                    <div class="card-body">
                        <!-- Form -->
                        <form id="inviteInstructorForm">
                            <!-- Email -->
                            <div class="mb-4">
                                <label for="instructorEmail" class="form-label">Email address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="instructorEmail" name="email" placeholder="instructor@example.com" required>
                                <div class="form-text">An invitation will be sent to this email address.</div>
                            </div>

                            <!-- First Name -->
                            <div class="mb-4">
                                <label for="instructorFirstName" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="instructorFirstName" name="firstName" required>
                            </div>

                            <!-- Last Name -->
                            <div class="mb-4">
                                <label for="instructorLastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="instructorLastName" name="lastName" required>
                            </div>

                            <!-- Additional Notes -->
                            <div class="mb-4">
                                <label for="additionalNotes" class="form-label">Additional Notes</label>
                                <textarea class="form-control" id="additionalNotes" name="notes" rows="3" placeholder="Any specific details or instructions"></textarea>
                            </div>

                            <!-- Buttons -->
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-white me-2" id="cancelButton">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-envelope me-1"></i> Send Invitation
                                </button>
                            </div>
                        </form>
                        <!-- End Form -->
                    </div>
                </div>
            </div>
            <!-- End Invite Form -->

            <!-- Information Column -->
            <div class="col-lg-5">
                <!-- Info Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="card-header-title">About Instructor Invitations</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-soft-info mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-info-circle"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5>Invitation Process</h5>
                                    <p class="mb-0">Instructors will receive an email with temporary login credentials. The invitation expires after <?php echo $invitationExpiryHours; ?> hours.</p>
                                </div>
                            </div>
                        </div>

                        <div class="list-group list-group-bordered">
                            <div class="list-group-item">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-1-circle text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <span class="fw-bold">Send invitation</span> to the instructor's email (expires in <?php echo $invitationExpiryHours; ?> hours)
                                    </div>
                                </div>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-2-circle text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <span class="fw-bold">Instructor logs in</span> with temporary credentials
                                    </div>
                                </div>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-3-circle text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <span class="fw-bold">Instructor completes profile</span> with professional information
                                    </div>
                                </div>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-4-circle text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <span class="fw-bold">Start assigning courses</span> to the instructor
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Info Card -->

                <!-- Stats Card -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-header-title">Department Statistics</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <span class="avatar avatar-sm avatar-soft-primary avatar-circle">
                                            <span class="avatar-initials">I</span>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="mb-0"><?php echo $activeInstructorsCount; ?></h5>
                                        <p class="card-text text-muted small">Active Instructors</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <span class="avatar avatar-sm avatar-soft-warning avatar-circle">
                                            <span class="avatar-initials">P</span>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="mb-0"><?php echo $pendingCount; ?></h5>
                                        <p class="card-text text-muted small">Pending Invitations</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Stats Card -->
            </div>

            <!-- Confirmation Modal -->
            <div id="confirmationModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="confirmationModalLabel">Confirm Invitation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to send an invitation to <span id="confirmEmail" class="fw-bold"></span>?</p>
                            <p class="text-muted">The instructor will receive an email with instructions to create their account. The invitation will expire after <?php echo $invitationExpiryHours; ?> hours.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" id="confirmSendBtn" class="btn btn-primary">Send Invitation</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Confirmation Modal -->

            <!-- Pending Invitations -->
            <div class="card mt-5">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-header-title">Pending Invitations</h4>
                        <button id="refreshInvitations" class="btn btn-icon btn-sm btn-ghost-secondary" data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh">
                            <i class="bi bi-arrow-repeat"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($pendingInvitations)): ?>
                        <div class="text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-envelope text-muted" style="font-size: 2rem;"></i>
                            </div>
                            <h5>No pending invitations</h5>
                            <p class="text-muted">When you invite instructors, they will appear here until they accept the invitation.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Email</th>
                                        <th>Invited By</th>
                                        <th>Date Sent</th>
                                        <th>Expires</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingInvitations as $invitation):
                                        // Calculate expiration status
                                        $expiryTime = new DateTime($invitation['expiry_time']);
                                        $now = new DateTime();
                                        $diff = $now->diff($expiryTime);
                                        $hoursLeft = ($diff->days * 24) + $diff->h;

                                        $statusClass = 'bg-soft-warning text-warning';
                                        $statusText = 'Pending';

                                        if ($hoursLeft < 6) {
                                            $statusClass = 'bg-soft-danger text-danger';
                                            $statusText = 'Expiring soon';
                                        }

                                        // Generate avatar initials from email
                                        $emailParts = explode('@', $invitation['email']);
                                        $nameParts = explode('.', $emailParts[0]);
                                        $initials = '';
                                        if (count($nameParts) >= 2) {
                                            $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
                                        } else {
                                            $initials = strtoupper(substr($emailParts[0], 0, 2));
                                        }

                                        // Generate a color class based on email for avatar
                                        $colors = ['primary', 'info', 'success', 'warning', 'danger'];
                                        $colorIndex = crc32($invitation['email']) % count($colors);
                                        $avatarColorClass = 'avatar-soft-' . $colors[$colorIndex];
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm avatar-circle">
                                                        <div class="<?php echo $avatarColorClass; ?> avatar-circle">
                                                            <span class="avatar-initials"><?php echo htmlspecialchars($initials); ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="ms-3">
                                                        <span class="d-block h5 text-inherit mb-0"><?php echo htmlspecialchars($invitation['email']); ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($invitation['invited_by_name']); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($invitation['created_at'])); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($invitation['expiry_time'])); ?></td>
                                            <td>
                                                <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-soft-warning ms-2 btn-resend"
                                                        data-id="<?php echo $invitation['id']; ?>"
                                                        data-email="<?php echo htmlspecialchars($invitation['email']); ?>"
                                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Resend">
                                                        <i class="bi bi-arrow-repeat"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-soft-danger btn-cancel"
                                                        data-id="<?php echo $invitation['id']; ?>"
                                                        data-email="<?php echo htmlspecialchars($invitation['email']); ?>"
                                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Cancel">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- End Pending Invitations -->
        </div>
        <!-- End Content -->

        <style>
            .custom-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1050;
                display: flex;
                justify-content: center;
                align-items: center;
                color: white;
            }
        </style>

        <!-- Cancel Invitation Modal -->
        <div id="cancelInvitationModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="cancelInvitationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cancelInvitationModalLabel">Cancel Invitation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to cancel the invitation sent to <span id="cancelEmail" class="fw-bold"></span>?</p>
                        <p class="text-muted">This action cannot be undone. The invitation link will be invalidated.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">No, Keep It</button>
                        <button type="button" id="confirmCancelBtn" class="btn btn-danger">Yes, Cancel Invitation</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Cancel Invitation Modal -->

        <!-- Resend Invitation Modal -->
        <div id="resendInvitationModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="resendInvitationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="resendInvitationModalLabel">Resend Invitation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to resend the invitation to <span id="resendEmail" class="fw-bold"></span>?</p>
                        <p class="text-muted">A new email will be sent with a fresh invitation link valid for <?php echo $invitationExpiryHours; ?> hours.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="confirmResendBtn" class="btn btn-primary">Resend Invitation</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Resend Invitation Modal -->

        <!-- Loading Overlay -->
        <div id="loadingOverlay" class="custom-overlay" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="ms-3" id="loadingMessage">Processing...</div>
        </div>

        <script src="../assets/js/department/invite-instructor.js"></script>

        <script>
            const INVITATION_EXPIRY_HOURS = <?php echo $invitationExpiryHours; ?>;

            // Additional script for handling resend and cancel actions
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize resend and cancel modals
                const resendModal = new bootstrap.Modal(document.getElementById('resendInvitationModal'));
                const cancelModal = new bootstrap.Modal(document.getElementById('cancelInvitationModal'));

                // Resend buttons
                document.querySelectorAll('.btn-resend').forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.dataset.id;
                        const email = this.dataset.email;

                        document.getElementById('resendEmail').textContent = email;
                        document.getElementById('confirmResendBtn').dataset.id = id;

                        resendModal.show();
                    });
                });

                // Cancel buttons
                document.querySelectorAll('.btn-cancel').forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.dataset.id;
                        const email = this.dataset.email;

                        document.getElementById('cancelEmail').textContent = email;
                        document.getElementById('confirmCancelBtn').dataset.id = id;

                        cancelModal.show();
                    });
                });

                // Confirm resend button handler
                document.getElementById('confirmResendBtn').addEventListener('click', function() {
                    const id = this.dataset.id;

                    // Hide modal and show loading
                    resendModal.hide();
                    showOverlay('Resending invitation...');

                    // AJAX call to resend invitation would go here
                    // For now, just simulate with a timeout
                    setTimeout(function() {
                        removeOverlay();
                        showToast('success', 'Invitation Resent', 'The invitation has been resent successfully.');

                        // Refresh the page to show updated list
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }, 1500);
                });

                // Confirm cancel button handler
                document.getElementById('confirmCancelBtn').addEventListener('click', function() {
                    const id = this.dataset.id;

                    // Hide modal and show loading
                    cancelModal.hide();
                    showOverlay('Cancelling invitation...');

                    // AJAX call to cancel invitation would go here
                    // For now, just simulate with a timeout
                    setTimeout(function() {
                        removeOverlay();
                        showToast('success', 'Invitation Cancelled', 'The invitation has been cancelled successfully.');

                        // Refresh the page to show updated list
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }, 1500);
                });

                // Refresh button handler
                document.getElementById('refreshInvitations').addEventListener('click', function() {
                    showOverlay('Refreshing invitations...');

                    // Simply reload the page
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                });
            });

            // Function to refresh pending invitations - used by main script
            function refreshPendingInvitations() {
                // In a real implementation, this would fetch fresh data via AJAX
                // For now, just reload the page after a delay
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        </script>
</main>
<!-- ========== END MAIN CONTENT ========== -->

<?php include '../includes/department/footer.php'; ?>