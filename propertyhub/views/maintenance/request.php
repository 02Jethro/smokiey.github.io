<?php
require_once '../../config.php';
require_auth();
require_role([USER_TENANT]);

require_once '../../models/Maintenance.php';
require_once '../../models/Property.php';

$maintenanceModel = new Maintenance();
$propertyModel = new Property();

// Get tenant's properties
$properties = $propertyModel->getByOwner($_SESSION['user_id']); // This should be getByTenant - need to implement
$requests = $maintenanceModel->getByTenant($_SESSION['user_id']);

$page_title = "Request Maintenance";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - PropertyHub</title>
    <link rel="stylesheet" href="<?php echo asset_url('css/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    /* Maintenance Page Specific Styles */
    .maintenance-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-top: 2rem;
}

    .maintenance-form-section {
        background: #ffffff;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        border: 1px solid #e9ecef;
    }

    .maintenance-history {
        background: #ffffff;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        border: 1px solid #e9ecef;
        max-height: 800px;
        overflow-y: auto;
    }

    .maintenance-form-section h3,
    .maintenance-history h3 {
        color: #2c3e50;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 3px solid #3498db;
        font-size: 1.5rem;
        font-weight: 600;
    }

    /* Form Styles */
    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #2c3e50;
        font-size: 0.95rem;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #ffffff;
        color: #2c3e50;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #3498db;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 120px;
        font-family: inherit;
    }

    /* Request Items */
    .requests-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .request-item {
        background: #ffffff;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        border-left: 5px solid #ddd;
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        position: relative;
        overflow: hidden;
    }

    .request-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }

    .request-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: #3498db;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .request-item:hover::before {
        opacity: 1;
    }

    .request-item.status-pending {
        border-left-color: #f39c12;
        background: #ffffff;
    }

    .request-item.status-in_progress {
        border-left-color: #3498db;
        background: #ffffff;
    }

    .request-item.status-completed {
        border-left-color: #27ae60;
        background: #ffffff;
    }

    .request-item.status-cancelled {
        border-left-color: #e74c3c;
        background: #ffffff;
    }

    .request-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
        gap: 1rem;
    }

    .request-header h4 {
        color: #2c3e50;
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
        flex: 1;
        line-height: 1.4;
    }

    .request-status {
        padding: 0.4rem 1rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    .status-pending .request-status { 
        background: linear-gradient(135deg, #f39c12, #e67e22); 
        color: white; 
        box-shadow: 0 2px 8px rgba(243, 156, 18, 0.3);
    }
    .status-in_progress .request-status { 
        background: linear-gradient(135deg, #3498db, #2980b9); 
        color: white; 
        box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
    }
    .status-completed .request-status { 
        background: linear-gradient(135deg, #27ae60, #229954); 
        color: white; 
        box-shadow: 0 2px 8px rgba(39, 174, 96, 0.3);
    }
    .status-cancelled .request-status { 
        background: linear-gradient(135deg, #e74c3c, #c0392b); 
        color: white; 
        box-shadow: 0 2px 8px rgba(231, 76, 60, 0.3);
    }

    .request-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .request-details p {
        margin: 0;
        font-size: 0.9rem;
        color: #555;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .request-details strong {
        color: #2c3e50;
        font-weight: 600;
    }

    .request-description {
        background: #f8f9fa;
        padding: 1.25rem;
        border-radius: 8px;
        border-left: 3px solid #3498db;
    }

    .request-description p {
        margin: 0;
        color: #555;
        line-height: 1.6;
        font-size: 0.95rem;
    }

    /* Priority Badges */
    .priority-low { 
        color: #27ae60; 
        font-weight: 600;
        background: #e8f6f3;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.8rem;
    }
    .priority-medium { 
        color: #f39c12; 
        font-weight: 600;
        background: #fef9e7;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.8rem;
    }
    .priority-high { 
        color: #e67e22; 
        font-weight: 600;
        background: #fdebd0;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.8rem;
    }
    .priority-urgent { 
        color: #e74c3c; 
        font-weight: bold;
        background: #fdedec;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.8rem;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    /* No Requests State */
    .no-requests {
        text-align: center;
        padding: 3rem 2rem;
        color: #6c757d;
    }

    .no-requests i {
        font-size: 4rem;
        color: #bdc3c7;
        margin-bottom: 1rem;
        display: block;
    }

    .no-requests h4 {
        color: #6c757d;
        margin-bottom: 0.5rem;
    }

    /* Button Styles - KEEPING SUBMIT BUTTON AS IS */
    .btn-primary {
        background: linear-gradient(135deg, #3498db, #2980b9);
        color: white;
        padding: 14px 28px;
        border: none;
        border-radius: 10px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(52, 152, 219, 0.3);
        background: linear-gradient(135deg, #2980b9, #2471a3);
    }

    .btn-primary:active {
        transform: translateY(0);
    }

    /* Form Enhancements */
    .form-group select {
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 16px center;
        background-size: 16px;
        padding-right: 48px;
    }

    /* Scrollbar Styling for History */
    .maintenance-history::-webkit-scrollbar {
        width: 6px;
    }

    .maintenance-history::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .maintenance-history::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .maintenance-history::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .maintenance-container {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        .maintenance-history {
            max-height: none;
            order: -1;
        }
    }

    @media (max-width: 768px) {
        .container {
            padding: 0 1rem;
        }
        
        .maintenance-form-section,
        .maintenance-history {
            padding: 1.5rem;
        }
        
        .request-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }
        
        .request-details {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }
        
        .request-status {
            align-self: flex-start;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 10px 14px;
            font-size: 16px; /* Prevents zoom on iOS */
        }
        
        .btn-primary {
            padding: 16px 28px; /* Larger touch target */
        }
    }

    @media (max-width: 480px) {
        .page-header h1 {
            font-size: 1.75rem;
        }
        
        .maintenance-form-section h3,
        .maintenance-history h3 {
            font-size: 1.25rem;
        }
        
        .request-item {
            padding: 1.25rem;
        }
        
        .request-description {
            padding: 1rem;
        }
        
        .request-details {
            padding: 0.75rem;
        }
    }

    /* Loading States */
    .form-loading {
        opacity: 0.7;
        pointer-events: none;
    }

    .form-loading .btn-primary {
        background: #bdc3c7;
    }

    /* Success/Error States */
    .form-success {
        border-left-color: #27ae60 !important;
    }

    .form-error {
        border-left-color: #e74c3c !important;
    }

    /* Print Styles */
    @media print {
        .maintenance-form-section {
            display: none;
        }
        
        .maintenance-history {
            box-shadow: none;
            border: 1px solid #ddd;
        }
        
        .request-item {
            break-inside: avoid;
            box-shadow: none;
            border: 1px solid #ddd;
        }
    }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-tools"></i> Maintenance Requests</h1>
            <p>Report and track maintenance issues for your properties</p>
        </div>

        <div class="maintenance-container">
            <!-- Request History Section -->
            <div class="maintenance-history">
                <h3><i class="fas fa-history"></i> Your Maintenance Requests</h3>
                
                <?php if (empty($requests)): ?>
                    <div class="no-requests">
                        <i class="fas fa-clipboard-list"></i>
                        <h4>No Maintenance Requests</h4>
                        <p>You haven't submitted any maintenance requests yet.</p>
                        <p>Use the form to report your first issue.</p>
                    </div>
                <?php else: ?>
                    <div class="requests-list">
                        <?php foreach ($requests as $request): ?>
                        <div class="request-item status-<?php echo $request['status']; ?>">
                            <div class="request-header">
                                <h4><?php echo htmlspecialchars($request['title']); ?></h4>
                                <span class="request-status">
                                    <i class="fas fa-<?php 
                                        switch($request['status']) {
                                            case 'completed': echo 'check-circle'; break;
                                            case 'in_progress': echo 'sync-alt'; break;
                                            case 'cancelled': echo 'times-circle'; break;
                                            default: echo 'clock';
                                        }
                                    ?>"></i>
                                    <?php echo ucfirst(str_replace('_', ' ', $request['status'])); ?>
                                </span>
                            </div>
                            
                            <div class="request-details">
                                <p>
                                    <i class="fas fa-home"></i>
                                    <strong>Property:</strong> 
                                    <?php echo htmlspecialchars($request['property_title'] ?? 'N/A'); ?>
                                </p>
                                <p>
                                    <i class="fas fa-exclamation-circle"></i>
                                    <strong>Priority:</strong> 
                                    <span class="priority-<?php echo $request['priority']; ?>">
                                        <i class="fas fa-<?php 
                                            switch($request['priority']) {
                                                case 'urgent': echo 'exclamation-triangle'; break;
                                                case 'high': echo 'arrow-up'; break;
                                                case 'medium': echo 'minus'; break;
                                                default: echo 'arrow-down';
                                            }
                                        ?>"></i>
                                        <?php echo ucfirst($request['priority']); ?>
                                    </span>
                                </p>
                                <p>
                                    <i class="fas fa-calendar"></i>
                                    <strong>Submitted:</strong> 
                                    <?php echo date('M j, Y g:i A', strtotime($request['created_at'])); ?>
                                </p>
                            </div>
                            
                            <div class="request-description">
                                <p><?php echo htmlspecialchars($request['description']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- New Request Form Section -->
            <div class="maintenance-form-section">
                <h3><i class="fas fa-plus-circle"></i> New Maintenance Request</h3>
                <form action="<?php echo BASE_URL; ?>controllers/MaintenanceController.php" method="POST" id="maintenanceForm">
                    <input type="hidden" name="action" value="create_request">
                    
                    <div class="form-group">
                        <label for="property_id">
                            <i class="fas fa-building"></i> Select Property
                        </label>
                        <select name="property_id" id="property_id" required>
                            <option value="">Choose a Property</option>
                            <?php foreach ($properties as $property): ?>
                            <option value="<?php echo $property['id']; ?>">
                                <?php echo htmlspecialchars($property['title']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="title">
                            <i class="fas fa-heading"></i> Issue Title
                        </label>
                        <input type="text" name="title" id="title" required 
                               placeholder="Brief description of the issue (e.g., Leaky faucet in kitchen)">
                    </div>

                    <div class="form-group">
                        <label for="priority">
                            <i class="fas fa-flag"></i> Priority Level
                        </label>
                        <select name="priority" id="priority" required>
                            <option value="low">🟢 Low - Minor issue, not urgent</option>
                            <option value="medium" selected>🟡 Medium - Needs attention soon</option>
                            <option value="high">🟠 High - Important issue affecting use</option>
                            <option value="urgent">🔴 Urgent - Requires immediate attention</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description">
                            <i class="fas fa-align-left"></i> Detailed Description
                        </label>
                        <textarea name="description" id="description" rows="5" required 
                                  placeholder="Please describe the issue in detail. Include location, when it started, and any other relevant information..."></textarea>
                    </div>

                    <button type="submit" class="btn-primary" id="submitBtn">
                        <i class="fas fa-paper-plane"></i> Submit Maintenance Request
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Form enhancement and validation
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('maintenanceForm');
        const submitBtn = document.getElementById('submitBtn');
        
        form.addEventListener('submit', function(e) {
            // Basic validation
            const title = document.getElementById('title').value.trim();
            const description = document.getElementById('description').value.trim();
            const property = document.getElementById('property_id').value;
            
            if (!property) {
                e.preventDefault();
                alert('Please select a property.');
                return;
            }
            
            if (title.length < 5) {
                e.preventDefault();
                alert('Please provide a more descriptive title (at least 5 characters).');
                return;
            }
            
            if (description.length < 10) {
                e.preventDefault();
                alert('Please provide a more detailed description of the issue.');
                return;
            }
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            submitBtn.disabled = true;
            form.classList.add('form-loading');
        });
        
        // Character counters
        const titleInput = document.getElementById('title');
        const descInput = document.getElementById('description');
        
        titleInput.addEventListener('input', function() {
            const length = this.value.length;
            if (length > 0 && length < 5) {
                this.style.borderColor = '#e74c3c';
            } else if (length >= 5) {
                this.style.borderColor = '#27ae60';
            } else {
                this.style.borderColor = '#e9ecef';
            }
        });
        
        descInput.addEventListener('input', function() {
            const length = this.value.length;
            if (length > 0 && length < 10) {
                this.style.borderColor = '#e74c3c';
            } else if (length >= 10) {
                this.style.borderColor = '#27ae60';
            } else {
                this.style.borderColor = '#e9ecef';
            }
        });
    });
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>