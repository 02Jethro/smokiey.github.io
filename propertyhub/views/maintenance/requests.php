<?php
require_once '../../config.php';
require_auth();
require_role([USER_LANDLORD, USER_PROPERTY_MANAGER]);

require_once '../../models/Maintenance.php';
require_once '../../models/User.php';

$maintenanceModel = new Maintenance();
$userModel = new User();

$requests = $maintenanceModel->getByLandlord($_SESSION['user_id']);
$maintenanceStaff = $userModel->getAll('property_manager');

$page_title = "Manage Maintenance";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="<?php echo asset_url('css/style.css'); ?>">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1>Manage Maintenance Requests</h1>
            <p>Review and manage maintenance requests from tenants</p>
        </div>

        <div class="maintenance-management">
            <?php if (empty($requests)): ?>
                <div class="no-requests">
                    <h3>No maintenance requests</h3>
                    <p>There are no maintenance requests for your properties at this time.</p>
                </div>
            <?php else: ?>
                <div class="requests-grid">
                    <?php foreach ($requests as $request): ?>
                    <div class="request-card status-<?php echo $request['status']; ?>">
                        <div class="request-header">
                            <h4><?php echo htmlspecialchars($request['title']); ?></h4>
                            <span class="request-status"><?php echo ucfirst(str_replace('_', ' ', $request['status'])); ?></span>
                        </div>
                        
                        <div class="request-details">
                            <p><strong>Property:</strong> <?php echo htmlspecialchars($request['property_title']); ?></p>
                            <p><strong>Tenant:</strong> <?php echo htmlspecialchars($request['tenant_first_name'] . ' ' . $request['tenant_last_name']); ?></p>
                            <p><strong>Priority:</strong> 
                                <span class="priority-<?php echo $request['priority']; ?>">
                                    <?php echo ucfirst($request['priority']); ?>
                                </span>
                            </p>
                            <p><strong>Submitted:</strong> <?php echo date('M j, Y g:i A', strtotime($request['created_at'])); ?></p>
                        </div>
                        
                        <div class="request-description">
                            <p><?php echo htmlspecialchars($request['description']); ?></p>
                        </div>
                        
                        <?php if ($request['status'] == 'pending' || $request['status'] == 'in_progress'): ?>
                        <div class="request-actions">
                            <form action="<?php echo BASE_URL; ?>controllers/MaintenanceController.php" method="POST" class="status-form">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Update Status:</label>
                                        <select name="status" required>
                                            <option value="in_progress" <?php echo $request['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="completed" <?php echo $request['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Assign To:</label>
                                        <select name="assigned_to">
                                            <option value="">Not Assigned</option>
                                            <?php foreach ($maintenanceStaff as $staff): ?>
                                                <option value="<?php echo $staff['id']; ?>" <?php echo $request['assigned_to'] == $staff['id'] ? 'selected' : ''; ?>>
                                                    <?php echo $staff['first_name'] . ' ' . $staff['last_name']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn-primary">Update Request</button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <style>
    .maintenance-management {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 2rem;
    }

    .requests-grid {
        display: grid;
        gap: 1.5rem;
    }

    .request-card {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 1.5rem;
        border-left: 4px solid #ddd;
    }

    .request-card.status-pending {
        border-left-color: #f39c12;
    }

    .request-card.status-in_progress {
        border-left-color: #3498db;
    }

    .request-card.status-completed {
        border-left-color: #27ae60;
    }

    .request-card.status-cancelled {
        border-left-color: #e74c3c;
    }

    .request-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .request-status {
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: bold;
        text-transform: uppercase;
    }

    .status-pending .request-status { background: #f39c12; color: white; }
    .status-in_progress .request-status { background: #3498db; color: white; }
    .status-completed .request-status { background: #27ae60; color: white; }
    .status-cancelled .request-status { background: #e74c3c; color: white; }

    .request-details {
        margin-bottom: 1rem;
    }

    .request-details p {
        margin: 0.25rem 0;
        font-size: 0.9rem;
    }

    .priority-low { color: #27ae60; }
    .priority-medium { color: #f39c12; }
    .priority-high { color: #e67e22; }
    .priority-urgent { color: #e74c3c; font-weight: bold; }

    .request-description {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 5px;
        margin-bottom: 1rem;
    }

    .request-actions {
        border-top: 1px solid #e9ecef;
        padding-top: 1rem;
    }

    .status-form .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .status-form .form-group {
        margin-bottom: 0;
    }

    @media (max-width: 768px) {
        .status-form .form-row {
            grid-template-columns: 1fr;
        }
        
        .request-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
    }
    </style>

    <?php include '../includes/footer.php'; ?>
</body>
</html>