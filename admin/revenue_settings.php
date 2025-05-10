<?php
require '../backend/session_start.php';

// Check if the user is signed in and has the 'admin' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'admin') {
    error_log("Unauthorized access attempt to revenue_settings.php: " . json_encode($_SERVER));
    header('Location: landing.php');
    exit;
}

require_once '../backend/config.php';

// Fetch all revenue settings
function getRevenueSettings() {
    global $conn;
    $sql = "SELECT setting_id, setting_name, setting_value, description FROM revenue_settings ORDER BY setting_id";
    $result = $conn->query($sql);
    $settings = [];
    while ($row = $result->fetch_assoc()) {
        $settings[] = $row;
    }
    return $settings;
}

$settings = getRevenueSettings();

// Handle AJAX save request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_setting') {
    header('Content-Type: application/json');
    $setting_id = intval($_POST['setting_id']);
    $setting_value = floatval($_POST['setting_value']);
    
    // Validate input
    $errors = [];
    if ($setting_id <= 0) {
        $errors[] = "Invalid setting ID";
    }
    
    // Fetch setting name for specific validation
    $sql = "SELECT setting_name FROM revenue_settings WHERE setting_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $setting_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $setting = $result->fetch_assoc();
    $stmt->close();
    
    if (!$setting) {
        $errors[] = "Setting not found";
    } else {
        switch ($setting['setting_name']) {
            case 'instructor_split':
            case 'platform_fee':
                if ($setting_value < 0 || $setting_value > 100) {
                    $errors[] = "Percentage must be between 0 and 100";
                }
                break;
            case 'holding_period':
                if ($setting_value < 1 || $setting_value != floor($setting_value)) {
                    $errors[] = "Holding period must be a positive integer";
                }
                break;
            case 'minimum_payout':
                if ($setting_value < 0) {
                    $errors[] = "Minimum payout cannot be negative";
                }
                break;
        }
    }
    
    if (empty($errors)) {
        $sql = "UPDATE revenue_settings SET setting_value = ?, updated_at = NOW() WHERE setting_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $setting_value, $setting_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Setting updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update setting']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    }
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Revenue Settings | Learnix - Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Admin dashboard for managing revenue settings in Learnix." />
    <meta name="author" content="Learnix Team" />
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <link href="assets/css/vendor/dataTables.bootstrap5.css" rel="stylesheet" type="text/css" />
</head>
<body class="loading" data-layout-color="light" data-leftbar-theme="dark" data-layout-mode="fluid">
    <div class="wrapper">
        <?php include '../includes/admin-sidebar.php'; // Assume an admin sidebar exists ?>
        <div class="content-page">
            <div class="content">
                <?php include '../includes/admin-topnavbar.php'; // Assume an admin navbar exists ?>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <h4 class="page-title">Revenue Settings</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Manage Revenue Settings</h5>
                                    <div class="table-responsive">
                                        <table id="settings-table" class="table table-centered table-striped dt-responsive nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>Setting</th>
                                                    <th>Value</th>
                                                    <th>Description</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($settings as $setting): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $setting['setting_name']))); ?></td>
                                                        <td>
                                                            <?php 
                                                            if ($setting['setting_name'] === 'instructor_split' || $setting['setting_name'] === 'platform_fee') {
                                                                echo number_format($setting['setting_value'], 2) . '%';
                                                            } elseif ($setting['setting_name'] === 'holding_period') {
                                                                echo intval($setting['setting_value']) . ' days';
                                                            } else {
                                                                echo '₵' . number_format($setting['setting_value'], 2);
                                                            }
                                                            ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($setting['description']); ?></td>
                                                        <td>
                                                            <button class="btn btn-sm btn-primary edit-setting" 
                                                                    data-id="<?php echo $setting['setting_id']; ?>" 
                                                                    data-name="<?php echo htmlspecialchars($setting['setting_name']); ?>" 
                                                                    data-value="<?php echo $setting['setting_value']; ?>"
                                                                    data-bs-toggle="modal" data-bs-target="#editSettingModal">
                                                                <i class="mdi mdi-pencil"></i> Edit
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                            © Learnix. <script>document.write(new Date().getFullYear())</script> All rights reserved.
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Edit Setting Modal -->
    <div class="modal fade" id="editSettingModal" tabindex="-1" aria-labelledby="editSettingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSettingModalLabel">Edit Setting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="edit-setting-form">
                        <input type="hidden" id="setting-id" name="setting_id">
                        <div class="mb-3">
                            <label for="setting-name" class="form-label">Setting Name</label>
                            <input type="text" class="form-control" id="setting-name" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="setting-value" class="form-label">Value</label>
                            <input type="number" class="form-control" id="setting-value" name="setting_value" step="0.01" required>
                            <small id="value-hint" class="form-text text-muted"></small>
                        </div>
                        <div id="error-message" class="text-danger" style="display: none;"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="save-setting">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bundle -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.min.js"></script>
    <!-- DataTables -->
    <script src="assets/js/vendor/jquery.dataTables.min.js"></script>
    <script src="assets/js/vendor/dataTables.bootstrap5.js"></script>
    <script src="assets/js/vendor/dataTables.responsive.min.js"></script>
    <script src="assets/js/vendor/responsive.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#settings-table').DataTable({
                dom: 'Bfrtip',
                buttons: [],
                pageLength: 10,
                lengthChange: false,
                order: [[0, 'asc']],
                language: {
                    emptyTable: "No settings found"
                }
            });

            // Populate modal on edit button click
            $('.edit-setting').on('click', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const value = $(this).data('value');
                
                $('#setting-id').val(id);
                $('#setting-name').val(name.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()));
                $('#setting-value').val(value);
                $('#error-message').hide();
                
                // Update hint based on setting
                if (name === 'instructor_split' || name === 'platform_fee') {
                    $('#value-hint').text('Enter a percentage between 0 and 100');
                    $('#setting-value').attr('step', '0.01').attr('min', '0').attr('max', '100');
                } else if (name === 'holding_period') {
                    $('#value-hint').text('Enter a positive integer (days)');
                    $('#setting-value').attr('step', '1').attr('min', '1');
                } else {
                    $('#value-hint').text('Enter a monetary amount');
                    $('#setting-value').attr('step', '0.01').attr('min', '0');
                }
            });

            // Save setting via AJAX
            $('#save-setting').on('click', function() {
                const formData = {
                    action: 'save_setting',
                    setting_id: $('#setting-id').val(),
                    setting_value: $('#setting-value').val()
                };

                $.ajax({
                    url: 'revenue_settings.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    beforeSend: function() {
                        $('#save-setting').prop('disabled', true).text('Saving...');
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            location.reload(); // Refresh to show updated values
                        } else {
                            $('#error-message').text(response.message).show();
                        }
                    },
                    error: function() {
                        $('#error-message').text('An error occurred while saving. Please try again.').show();
                    },
                    complete: function() {
                        $('#save-setting').prop('disabled', false).text('Save changes');
                    }
                });
            });
        });
    </script>
</body>
</html>