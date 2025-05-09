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
    <div class="navbar-sidebar-aside-content content-space-1 content-space-md-2 px-lg-5 px-xl-10">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="page-header-title">Contact Support</h1>
                </div>
                <div class="col-auto">
                    <a class="btn btn-primary" href="#" data-bs-toggle="modal" data-bs-target="#newTicketModal">
                        <i class="bi-plus me-1"></i> New Support Ticket
                    </a>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Stats Overview -->
        <div class="row mb-4">
            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Open Tickets</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <span class="h2 text-primary"><?php echo getOpenTicketsCount(); ?></span>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-soft-primary text-primary p-2">
                                    <i class="bi-ticket"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">In Progress</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <span class="h2 text-warning"><?php echo getInProgressTicketsCount(); ?></span>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-soft-warning text-warning p-2">
                                    <i class="bi-hourglass-split"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Resolved</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <span class="h2 text-success"><?php echo getResolvedTicketsCount(); ?></span>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-soft-success text-success p-2">
                                    <i class="bi-check-circle"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">Average Response</h6>
                        <div class="row align-items-center">
                            <div class="col">
                                <span class="h2"><?php echo getAverageResponseTime(); ?> hrs</span>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-soft-info text-info p-2">
                                    <i class="bi-clock"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Stats -->

        <!-- Tabs -->
        <div class="mb-4">
            <ul class="nav nav-tabs" id="supportTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="all-tickets-tab" data-bs-toggle="tab" href="#all-tickets" role="tab">
                        All Tickets <span class="badge bg-primary rounded-pill ms-1"><?php echo getAllTicketsCount(); ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="open-tickets-tab" data-bs-toggle="tab" href="#open-tickets" role="tab">
                        Open <span class="badge bg-soft-primary text-primary rounded-pill ms-1"><?php echo getOpenTicketsCount(); ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="in-progress-tab" data-bs-toggle="tab" href="#in-progress" role="tab">
                        In Progress <span class="badge bg-soft-warning text-warning rounded-pill ms-1"><?php echo getInProgressTicketsCount(); ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="resolved-tab" data-bs-toggle="tab" href="#resolved" role="tab">
                        Resolved <span class="badge bg-soft-success text-success rounded-pill ms-1"><?php echo getResolvedTicketsCount(); ?></span>
                    </a>
                </li>
            </ul>
        </div>
        <!-- End Tabs -->

        <!-- Tab Content -->
        <div class="tab-content" id="supportTabsContent">
            <!-- All Tickets Tab -->
            <div class="tab-pane fade show active" id="all-tickets" role="tabpanel" aria-labelledby="all-tickets-tab">
                <!-- Search Form -->
                <div class="row mb-4">
                    <div class="col-lg-6 mb-3 mb-lg-0">
                        <form>
                            <div class="input-group input-group-merge">
                                <input type="text" class="form-control" placeholder="Search tickets..." aria-label="Search tickets">
                                <div class="input-group-append input-group-text">
                                    <i class="bi-search"></i>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-lg-6">
                        <div class="d-sm-flex justify-content-sm-end align-items-sm-center">
                            <!-- Filter -->
                            <div class="dropdown me-2">
                                <button type="button" class="btn btn-white btn-sm dropdown-toggle" id="ticketFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi-filter me-1"></i> Filter
                                </button>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="ticketFilterDropdown">
                                    <a class="dropdown-item" href="#">
                                        <i class="bi-sort-down dropdown-item-icon"></i> Newest
                                    </a>
                                    <a class="dropdown-item" href="#">
                                        <i class="bi-sort-up dropdown-item-icon"></i> Oldest
                                    </a>
                                    <a class="dropdown-item" href="#">
                                        <i class="bi-arrow-up dropdown-item-icon"></i> High priority
                                    </a>
                                    <a class="dropdown-item" href="#">
                                        <i class="bi-arrow-down dropdown-item-icon"></i> Low priority
                                    </a>
                                </div>
                            </div>
                            <!-- End Filter -->

                            <!-- Export -->
                            <div class="dropdown">
                                <button type="button" class="btn btn-white btn-sm dropdown-toggle" id="ticketExportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi-download me-1"></i> Export
                                </button>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="ticketExportDropdown">
                                    <a class="dropdown-item" href="#">
                                        <i class="bi-file-earmark-excel dropdown-item-icon"></i> Excel
                                    </a>
                                    <a class="dropdown-item" href="#">
                                        <i class="bi-file-earmark-pdf dropdown-item-icon"></i> PDF
                                    </a>
                                    <a class="dropdown-item" href="#">
                                        <i class="bi-file-earmark-text dropdown-item-icon"></i> CSV
                                    </a>
                                </div>
                            </div>
                            <!-- End Export -->
                        </div>
                    </div>
                </div>
                <!-- End Search Form -->

                <!-- Tickets Table -->
                <div class="table-responsive datatable-custom">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Date</th>
                                <th>Last Update</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            // Get all tickets for the current admin
                            $tickets = getAdminTickets();

                            if ($tickets && count($tickets) > 0) {
                                foreach ($tickets as $ticket) {
                                    $statusClass = '';
                                    $statusBadge = '';

                                    switch ($ticket['status']) {
                                        case 'open':
                                            $statusClass = 'bg-soft-primary text-primary';
                                            $statusBadge = 'Open';
                                            break;
                                        case 'in-progress':
                                            $statusClass = 'bg-soft-warning text-warning';
                                            $statusBadge = 'In Progress';
                                            break;
                                        case 'resolved':
                                            $statusClass = 'bg-soft-success text-success';
                                            $statusBadge = 'Resolved';
                                            break;
                                        default:
                                            $statusClass = 'bg-soft-secondary text-secondary';
                                            $statusBadge = 'Unknown';
                                    }

                                    $priorityClass = '';
                                    $priorityBadge = '';

                                    switch ($ticket['priority']) {
                                        case 'high':
                                            $priorityClass = 'bg-soft-danger text-danger';
                                            $priorityBadge = 'High';
                                            break;
                                        case 'medium':
                                            $priorityClass = 'bg-soft-warning text-warning';
                                            $priorityBadge = 'Medium';
                                            break;
                                        case 'low':
                                            $priorityClass = 'bg-soft-info text-info';
                                            $priorityBadge = 'Low';
                                            break;
                                        default:
                                            $priorityClass = 'bg-soft-secondary text-secondary';
                                            $priorityBadge = 'Normal';
                                    }
                            ?>
                                    <tr>
                                        <td>#<?php echo $ticket['id']; ?></td>
                                        <td>
                                            <a href="ticket-details.php?id=<?php echo $ticket['id']; ?>" class="text-dark">
                                                <?php echo htmlspecialchars($ticket['subject']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $statusClass; ?>"><?php echo $statusBadge; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $priorityClass; ?>"><?php echo $priorityBadge; ?></span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($ticket['created_at'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($ticket['updated_at'])); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="ticket-details.php?id=<?php echo $ticket['id']; ?>" class="btn btn-white btn-sm">
                                                    <i class="bi-eye"></i> View
                                                </a>
                                                <button type="button" class="btn btn-white btn-sm dropdown-toggle" id="ticketActionDropdown<?php echo $ticket['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false"></button>
                                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="ticketActionDropdown<?php echo $ticket['id']; ?>">
                                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#updateStatusModal" data-ticket-id="<?php echo $ticket['id']; ?>">
                                                        <i class="bi-arrow-clockwise dropdown-item-icon"></i> Update Status
                                                    </a>
                                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#replyTicketModal" data-ticket-id="<?php echo $ticket['id']; ?>">
                                                        <i class="bi-reply dropdown-item-icon"></i> Reply
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#closeTicketModal" data-ticket-id="<?php echo $ticket['id']; ?>">
                                                        <i class="bi-x-circle dropdown-item-icon text-danger"></i> Close
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="p-4">
                                            <img class="mb-3" src="../assets/svg/illustrations/oc-empty.svg" alt="Image Description" style="width: 10rem;">
                                            <p class="mb-0">No tickets found</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <!-- End Tickets Table -->

                <!-- Pagination -->
                <div class="d-flex justify-content-center justify-content-sm-end mt-4">
                    <nav id="datatablePagination" aria-label="Activity pagination"></nav>
                </div>
                <!-- End Pagination -->
            </div>
            <!-- End All Tickets Tab -->

            <!-- Open Tickets Tab -->
            <div class="tab-pane fade" id="open-tickets" role="tabpanel" aria-labelledby="open-tickets-tab">
                <!-- Similar structure to All Tickets Tab, but filtered for open tickets -->
                <div class="table-responsive datatable-custom">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Subject</th>
                                <th>Priority</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Get open tickets for the current admin
                            $openTickets = getAdminTicketsByStatus('open');

                            if ($openTickets && count($openTickets) > 0) {
                                foreach ($openTickets as $ticket) {
                                    $priorityClass = '';
                                    $priorityBadge = '';

                                    switch ($ticket['priority']) {
                                        case 'high':
                                            $priorityClass = 'bg-soft-danger text-danger';
                                            $priorityBadge = 'High';
                                            break;
                                        case 'medium':
                                            $priorityClass = 'bg-soft-warning text-warning';
                                            $priorityBadge = 'Medium';
                                            break;
                                        case 'low':
                                            $priorityClass = 'bg-soft-info text-info';
                                            $priorityBadge = 'Low';
                                            break;
                                        default:
                                            $priorityClass = 'bg-soft-secondary text-secondary';
                                            $priorityBadge = 'Normal';
                                    }
                            ?>
                                    <tr>
                                        <td>#<?php echo $ticket['id']; ?></td>
                                        <td>
                                            <a href="ticket-details.php?id=<?php echo $ticket['id']; ?>" class="text-dark">
                                                <?php echo htmlspecialchars($ticket['subject']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $priorityClass; ?>"><?php echo $priorityBadge; ?></span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($ticket['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="ticket-details.php?id=<?php echo $ticket['id']; ?>" class="btn btn-white btn-sm">
                                                    <i class="bi-eye"></i> View
                                                </a>
                                                <button type="button" class="btn btn-white btn-sm dropdown-toggle" id="openTicketActionDropdown<?php echo $ticket['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false"></button>
                                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="openTicketActionDropdown<?php echo $ticket['id']; ?>">
                                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#updateStatusModal" data-ticket-id="<?php echo $ticket['id']; ?>">
                                                        <i class="bi-arrow-clockwise dropdown-item-icon"></i> Update Status
                                                    </a>
                                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#replyTicketModal" data-ticket-id="<?php echo $ticket['id']; ?>">
                                                        <i class="bi-reply dropdown-item-icon"></i> Reply
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <div class="p-4">
                                            <img class="mb-3" src="../assets/svg/illustrations/oc-empty.svg" alt="Image Description" style="width: 10rem;">
                                            <p class="mb-0">No open tickets found</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- End Open Tickets Tab -->

            <!-- In Progress Tab -->
            <div class="tab-pane fade" id="in-progress" role="tabpanel" aria-labelledby="in-progress-tab">
                <!-- Similar structure to All Tickets Tab, but filtered for in-progress tickets -->
                <div class="table-responsive datatable-custom">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Subject</th>
                                <th>Priority</th>
                                <th>Date</th>
                                <th>Last Update</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Get in-progress tickets for the current admin
                            $inProgressTickets = getAdminTicketsByStatus('in-progress');

                            if ($inProgressTickets && count($inProgressTickets) > 0) {
                                // Similar loop to show tickets as in the All Tickets tab
                                foreach ($inProgressTickets as $ticket) {
                                    // Display ticket details
                                    // Similar to the All Tickets tab
                                }
                            } else {
                            ?>
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="p-4">
                                            <img class="mb-3" src="../assets/svg/illustrations/oc-empty.svg" alt="Image Description" style="width: 10rem;">
                                            <p class="mb-0">No in-progress tickets found</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- End In Progress Tab -->

            <!-- Resolved Tab -->
            <div class="tab-pane fade" id="resolved" role="tabpanel" aria-labelledby="resolved-tab">
                <!-- Similar structure to All Tickets Tab, but filtered for resolved tickets -->
                <div class="table-responsive datatable-custom">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Subject</th>
                                <th>Priority</th>
                                <th>Date</th>
                                <th>Resolved Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Get resolved tickets for the current admin
                            $resolvedTickets = getAdminTicketsByStatus('resolved');

                            if ($resolvedTickets && count($resolvedTickets) > 0) {
                                // Similar loop to show tickets as in the All Tickets tab
                                foreach ($resolvedTickets as $ticket) {
                                    // Display ticket details
                                    // Similar to the All Tickets tab
                                }
                            } else {
                            ?>
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="p-4">
                                            <img class="mb-3" src="../assets/svg/illustrations/oc-empty.svg" alt="Image Description" style="width: 10rem;">
                                            <p class="mb-0">No resolved tickets found</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- End Resolved Tab -->
        </div>
        <!-- End Tab Content -->
    </div>
    <!-- End Content -->

    <!-- New Ticket Modal -->
    <div class="modal fade" id="newTicketModal" tabindex="-1" role="dialog" aria-labelledby="newTicketModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newTicketModalLabel">Create New Support Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="newTicketForm" action="process-ticket.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="create">

                        <div class="mb-3">
                            <label for="ticketSubject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="ticketSubject" name="subject" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="ticketCategory" class="form-label">Category</label>
                                <select class="form-select" id="ticketCategory" name="category" required>
                                    <option value="">Select a category</option>
                                    <option value="technical">Technical Issue</option>
                                    <option value="billing">Billing</option>
                                    <option value="account">Account</option>
                                    <option value="feature">Feature Request</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="ticketPriority" class="form-label">Priority</label>
                                <select class="form-select" id="ticketPriority" name="priority" required>
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="ticketDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="ticketDescription" name="description" rows="5" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="ticketAttachments" class="form-label">Attachments (optional)</label>
                            <input type="file" class="form-control" id="ticketAttachments" name="attachments[]" multiple>
                            <small class="form-text text-muted">Max 5 files. Allowed file types: jpg, png, pdf, doc, docx, xls, xlsx (Max 5MB each)</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="newTicketForm" class="btn btn-primary">Submit Ticket</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End New Ticket Modal -->

    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1" role="dialog" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateStatusModalLabel">Update Ticket Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateStatusForm" action="process-ticket.php" method="post">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="ticket_id" id="statusTicketId" value="">

                        <div class="mb-3">
                            <label for="newStatus" class="form-label">New Status</label>
                            <select class="form-select" id="newStatus" name="status" required>
                                <option value="open">Open</option>
                                <option value="in-progress">In Progress</option>
                                <option value="resolved">Resolved</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="statusComment" class="form-label">Comment (optional)</label>
                            <textarea class="form-control" id="statusComment" name="comment" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="updateStatusForm" class="btn btn-primary">Update Status</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Update Status Modal -->

    <!-- Reply Ticket Modal -->
    <div class="modal fade" id="replyTicketModal" tabindex="-1" role="dialog" aria-labelledby="replyTicketModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="replyTicketModalLabel">Reply to Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="replyTicketForm" action="process-ticket.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="reply">
                        <input type="hidden" name="ticket_id" id="replyTicketId" value="">

                        <div class="mb-3">
                            <label for="replyMessage" class="form-label">Message</label>
                            <textarea class="form-control" id="replyMessage" name="message" rows="5" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="replyAttachments" class="form-label">Attachments (optional)</label>
                            <input type="file" class="form-control" id="replyAttachments" name="attachments[]" multiple>
                            <small class="form-text text-muted">Max 5 files. Allowed file types: jpg, png, pdf, doc, docx, xls, xlsx (Max 5MB each)</small>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="updateTicketStatus" name="update_status" value="1" checked>
                            <label class="form-check-label" for="updateTicketStatus">Update ticket status</label>
                        </div>

                        <div id="statusUpdateContainer" class="mb-3">
                            <label for="replyNewStatus" class="form-label">New Status</label>
                            <select class="form-select" id="replyNewStatus" name="status">
                                <option value="open">Open</option>
                                <option value="in-progress" selected>In Progress</option>
                                <option value="resolved">Resolved</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="replyTicketForm" class="btn btn-primary">Send Reply</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Reply Ticket Modal -->

    <!-- Close Ticket Modal -->
    <div class="modal fade" id="closeTicketModal" tabindex="-1" role="dialog" aria-labelledby="closeTicketModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="closeTicketModalLabel">Close Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="closeTicketForm" action="process-ticket.php" method="post">
                        <input type="hidden" name="action" value="close">
                        <input type="hidden" name="ticket_id" id="closeTicketId" value="">

                        <p>Are you sure you want to close this ticket? This action will mark the ticket as resolved.</p>

                        <div class="mb-3">
                            <label for="closeComment" class="form-label">Closing Comment (optional)</label>
                            <textarea class="form-control" id="closeComment" name="comment" rows="3"></textarea>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="sendClosingNotification" name="send_notification" value="1" checked>
                            <label class="form-check-label" for="sendClosingNotification">
                                Send notification email to ticket creator
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="closeTicketForm" class="btn btn-danger">Close Ticket</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Close Ticket Modal -->

    <!-- Knowledge Base Section -->
    <div class="modal fade" id="knowledgeBaseModal" tabindex="-1" role="dialog" aria-labelledby="knowledgeBaseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="knowledgeBaseModalLabel">Knowledge Base</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <form class="input-group input-group-merge">
                            <input type="text" class="form-control" placeholder="Search knowledge base..." aria-label="Search knowledge base">
                            <div class="input-group-append input-group-text">
                                <i class="bi-search"></i>
                            </div>
                        </form>
                    </div>

                    <div class="row mb-4">
                        <div class="col-sm-6 col-lg-4 mb-3 mb-lg-0">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5>Getting Started</h5>
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <a href="#" class="text-decoration-none text-body">
                                                <i class="bi-file-text me-1 text-primary"></i> LMS Overview
                                            </a>
                                        </li>
                                        <li class="mb-2">
                                            <a href="#" class="text-decoration-none text-body">
                                                <i class="bi-file-text me-1 text-primary"></i> Account Setup
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" class="text-decoration-none text-body">
                                                <i class="bi-file-text me-1 text-primary"></i> First Course Creation
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-4 mb-3 mb-lg-0">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5>Common Issues</h5>
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <a href="#" class="text-decoration-none text-body">
                                                <i class="bi-file-text me-1 text-primary"></i> Video Upload Troubleshooting
                                            </a>
                                        </li>
                                        <li class="mb-2">
                                            <a href="#" class="text-decoration-none text-body">
                                                <i class="bi-file-text me-1 text-primary"></i> Student Access Issues
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" class="text-decoration-none text-body">
                                                <i class="bi-file-text me-1 text-primary"></i> Payment Processing
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5>Advanced Features</h5>
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <a href="#" class="text-decoration-none text-body">
                                                <i class="bi-file-text me-1 text-primary"></i> API Integration
                                            </a>
                                        </li>
                                        <li class="mb-2">
                                            <a href="#" class="text-decoration-none text-body">
                                                <i class="bi-file-text me-1 text-primary"></i> Custom Reporting
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" class="text-decoration-none text-body">
                                                <i class="bi-file-text me-1 text-primary"></i> White Labeling
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <a href="knowledge-base.php" class="btn btn-outline-primary">View Full Knowledge Base</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Knowledge Base Modal -->

</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- JS Support Functions -->
<script>
    // Support functions to get ticket counts
    <?php
    function getOpenTicketsCount()
    {
        // In a real application, this would query the database to count open tickets
        // For demonstration purposes, we'll return a placeholder value
        return 5;
    }

    function getInProgressTicketsCount()
    {
        // In a real application, this would query the database to count in-progress tickets
        // For demonstration purposes, we'll return a placeholder value
        return 3;
    }

    function getResolvedTicketsCount()
    {
        // In a real application, this would query the database to count resolved tickets
        // For demonstration purposes, we'll return a placeholder value
        return 12;
    }

    function getAllTicketsCount()
    {
        // Sum of all ticket counts
        return getOpenTicketsCount() + getInProgressTicketsCount() + getResolvedTicketsCount();
    }

    function getAverageResponseTime()
    {
        // In a real application, this would calculate the average response time from the database
        // For demonstration purposes, we'll return a placeholder value
        return 4.2;
    }

    function getAdminTickets()
    {
        // In a real application, this would fetch tickets from the database
        // For demonstration purposes, we'll create sample data
        return [
            [
                'id' => 1001,
                'subject' => 'Unable to upload video lessons',
                'status' => 'open',
                'priority' => 'high',
                'created_at' => '2025-04-20 10:30:45',
                'updated_at' => '2025-04-20 10:30:45'
            ],
            [
                'id' => 1002,
                'subject' => 'Integration with payment gateway',
                'status' => 'in-progress',
                'priority' => 'medium',
                'created_at' => '2025-04-18 14:22:10',
                'updated_at' => '2025-04-19 09:15:33'
            ],
            [
                'id' => 1003,
                'subject' => 'Student enrollment issue',
                'status' => 'resolved',
                'priority' => 'high',
                'created_at' => '2025-04-15 08:45:00',
                'updated_at' => '2025-04-17 16:20:12'
            ],
            [
                'id' => 1004,
                'subject' => 'Certificate generation not working',
                'status' => 'open',
                'priority' => 'medium',
                'created_at' => '2025-04-22 11:05:38',
                'updated_at' => '2025-04-22 11:05:38'
            ],
            [
                'id' => 1005,
                'subject' => 'Quiz timer malfunction',
                'status' => 'in-progress',
                'priority' => 'high',
                'created_at' => '2025-04-21 15:30:22',
                'updated_at' => '2025-04-22 09:45:10'
            ]
        ];
    }

    function getAdminTicketsByStatus($status)
    {
        // In a real application, this would fetch tickets with specific status
        // For demonstration, we'll filter the sample data
        $allTickets = getAdminTickets();
        $filteredTickets = [];

        foreach ($allTickets as $ticket) {
            if ($ticket['status'] === $status) {
                $filteredTickets[] = $ticket;
            }
        }

        return $filteredTickets;
    }
    ?>

    // Initialize event handlers when the document is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Update Status Modal
        var updateStatusModal = document.getElementById('updateStatusModal');
        if (updateStatusModal) {
            updateStatusModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var ticketId = button.getAttribute('data-ticket-id');
                document.getElementById('statusTicketId').value = ticketId;
            });
        }

        // Initialize Reply Ticket Modal
        var replyTicketModal = document.getElementById('replyTicketModal');
        if (replyTicketModal) {
            replyTicketModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var ticketId = button.getAttribute('data-ticket-id');
                document.getElementById('replyTicketId').value = ticketId;
            });
        }

        // Initialize Close Ticket Modal
        var closeTicketModal = document.getElementById('closeTicketModal');
        if (closeTicketModal) {
            closeTicketModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var ticketId = button.getAttribute('data-ticket-id');
                document.getElementById('closeTicketId').value = ticketId;
            });
        }

        // Toggle status update in reply modal
        var updateTicketStatusCheckbox = document.getElementById('updateTicketStatus');
        var statusUpdateContainer = document.getElementById('statusUpdateContainer');

        if (updateTicketStatusCheckbox && statusUpdateContainer) {
            updateTicketStatusCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    statusUpdateContainer.style.display = 'block';
                } else {
                    statusUpdateContainer.style.display = 'none';
                }
            });
        }
    });
</script>

<?php include '../includes/department/footer.php'; ?>