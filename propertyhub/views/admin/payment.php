<?php
require_once '../../config.php';
require_auth();
require_role([USER_ADMIN]);

require_once '../../models/Payment.php';
require_once '../../models/User.php';
require_once '../../models/Property.php';

$paymentModel = new Payment();
$userModel = new User();
$propertyModel = new Property();

// Get filter parameters
$status = $_GET['status'] ?? '';
$payment_type = $_GET['payment_type'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build filter conditions
$filters = [];
if (!empty($status)) {
    $filters['status'] = $status;
}
if (!empty($payment_type)) {
    $filters['payment_type'] = $payment_type;
}
if (!empty($date_from)) {
    $filters['date_from'] = $date_from;
}
if (!empty($date_to)) {
    $filters['date_to'] = $date_to;
}

// Get all payments with filters
$payments = $paymentModel->getAllPayments($filters);

// Get statistics
$totalPayments = count($payments);
$totalRevenue = array_sum(array_column($payments, 'amount'));
$completedPayments = array_filter($payments, function($payment) {
    return $payment['status'] === 'completed';
});
$pendingPayments = array_filter($payments, function($payment) {
    return $payment['status'] === 'pending';
});

// Get payment statistics by type
$paymentStats = [];
foreach ($payments as $payment) {
    $type = $payment['payment_type'];
    if (!isset($paymentStats[$type])) {
        $paymentStats[$type] = ['count' => 0, 'amount' => 0];
    }
    $paymentStats[$type]['count']++;
    $paymentStats[$type]['amount'] += $payment['amount'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments - PropertyHub Admin</title>
    <link rel="stylesheet" href="<?php echo asset_url('css/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    .admin-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        text-align: center;
    }

    .stat-card .stat-number {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }

    .stat-card .stat-label {
        color: #666;
        font-size: 0.9rem;
    }

    .stat-revenue { border-left: 4px solid #27ae60; }
    .stat-completed { border-left: 4px solid #3498db; }
    .stat-pending { border-left: 4px solid #f39c12; }
    .stat-total { border-left: 4px solid #9b59b6; }

    .filters-card {
        background: white;
        padding: 1.5rem;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
    }

    .filter-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .payments-table {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .table-responsive {
        overflow-x: auto;
    }

    .payments-table table {
        width: 100%;
        border-collapse: collapse;
    }

    .payments-table th,
    .payments-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .payments-table th {
        background: #f8f9fa;
        font-weight: 600;
        color: #2c3e50;
    }

    .payment-status {
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: bold;
        text-transform: uppercase;
    }

    .status-completed { background: #d4edda; color: #155724; }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-failed { background: #f8d7da; color: #721c24; }
    .status-refunded { background: #e2e3e5; color: #383d41; }

    .payment-type {
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.8rem;
        background: #e8f4fd;
        color: #3498db;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .btn-small {
        padding: 0.25rem 0.75rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.8rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .btn-view { background: #3498db; color: white; }
    .btn-refund { background: #f39c12; color: white; }
    .btn-delete { background: #e74c3c; color: white; }

    .payment-details-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background: white;
        padding: 2rem;
        border-radius: 10px;
        max-width: 500px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
    }

    .modal-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid #eee;
        padding-bottom: 1rem;
    }

    .close-modal {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #666;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid #f8f9fa;
    }

    .detail-label {
        font-weight: 600;
        color: #666;
    }

    .export-options {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
    }

    .chart-container {
        background: white;
        padding: 1.5rem;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
    }

    .chart-placeholder {
        height: 300px;
        background: #f8f9fa;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #666;
    }

    @media (max-width: 768px) {
        .filter-row {
            grid-template-columns: 1fr;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .payments-table {
            font-size: 0.9rem;
        }
        
        .payments-table th,
        .payments-table td {
            padding: 0.5rem;
        }
    }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="admin-header">
        <div class="container">
            <h1><i class="fas fa-credit-card"></i> Payment Management</h1>
            <p>Manage all payments and transactions in the system</p>
        </div>
    </div>

    <div class="container">
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-revenue">
                <div class="stat-number">$<?php echo number_format($totalRevenue, 2); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
            <div class="stat-card stat-total">
                <div class="stat-number"><?php echo $totalPayments; ?></div>
                <div class="stat-label">Total Payments</div>
            </div>
            <div class="stat-card stat-completed">
                <div class="stat-number"><?php echo count($completedPayments); ?></div>
                <div class="stat-label">Completed</div>
            </div>
            <div class="stat-card stat-pending">
                <div class="stat-number"><?php echo count($pendingPayments); ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>

        <!-- Payment Statistics Chart -->
        <div class="chart-container">
            <h3>Payment Statistics</h3>
            <div class="chart-placeholder">
                <i class="fas fa-chart-pie fa-3x"></i>
                <div style="margin-left: 1rem;">
                    <h4>Payment Distribution</h4>
                    <p>Total payments by type</p>
                </div>
            </div>
            <div style="margin-top: 1rem;">
                <?php foreach ($paymentStats as $type => $stats): ?>
                <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                    <span style="text-transform: capitalize;"><?php echo $type; ?></span>
                    <span>
                        <strong><?php echo $stats['count']; ?></strong> payments 
                        ($<?php echo number_format($stats['amount'], 2); ?>)
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-card">
            <h3>Filter Payments</h3>
            <form method="GET" action="">
                <div class="filter-row">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="">All Statuses</option>
                            <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="failed" <?php echo $status === 'failed' ? 'selected' : ''; ?>>Failed</option>
                            <option value="refunded" <?php echo $status === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Payment Type</label>
                        <select name="payment_type">
                            <option value="">All Types</option>
                            <option value="rent" <?php echo $payment_type === 'rent' ? 'selected' : ''; ?>>Rent</option>
                            <option value="deposit" <?php echo $payment_type === 'deposit' ? 'selected' : ''; ?>>Deposit</option>
                            <option value="maintenance" <?php echo $payment_type === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                            <option value="purchase" <?php echo $payment_type === 'purchase' ? 'selected' : ''; ?>>Purchase</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>From Date</label>
                        <input type="date" name="date_from" value="<?php echo $date_from; ?>">
                    </div>
                    <div class="form-group">
                        <label>To Date</label>
                        <input type="date" name="date_to" value="<?php echo $date_to; ?>">
                    </div>
                </div>
                <div class="filter-row">
                    <div class="form-group">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <a href="payments.php" class="btn-secondary">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Payments Table -->
        <div class="payments-table">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tenant</th>
                            <th>Landlord</th>
                            <th>Property</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payments)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 2rem;">
                                    <i class="fas fa-search fa-2x" style="color: #bdc3c7; margin-bottom: 1rem;"></i>
                                    <p>No payments found matching your criteria</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td>#<?php echo $payment['id']; ?></td>
                                <td>
                                    <?php 
                                    $tenant = $userModel->getById($payment['tenant_id']);
                                    echo $tenant ? $tenant['first_name'] . ' ' . $tenant['last_name'] : 'N/A';
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    $landlord = $userModel->getById($payment['landlord_id']);
                                    echo $landlord ? $landlord['first_name'] . ' ' . $landlord['last_name'] : 'N/A';
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    $property = $propertyModel->getById($payment['property_id']);
                                    echo $property ? $property['title'] : 'N/A';
                                    ?>
                                </td>
                                <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                                <td>
                                    <span class="payment-type">
                                        <?php echo ucfirst($payment['payment_type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="payment-status status-<?php echo $payment['status']; ?>">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    if ($payment['payment_date']) {
                                        echo date('M j, Y', strtotime($payment['payment_date']));
                                    } else {
                                        echo 'Pending';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-small btn-view view-payment" data-payment-id="<?php echo $payment['id']; ?>">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <?php if ($payment['status'] === 'completed'): ?>
                                            <button class="btn-small btn-refund refund-payment" data-payment-id="<?php echo $payment['id']; ?>">
                                                <i class="fas fa-undo"></i> Refund
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn-small btn-delete delete-payment" data-payment-id="<?php echo $payment['id']; ?>">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Export Options -->
        <div class="export-options">
            <button class="btn-primary" onclick="exportPayments('csv')">
                <i class="fas fa-file-csv"></i> Export as CSV
            </button>
            <button class="btn-primary" onclick="exportPayments('pdf')">
                <i class="fas fa-file-pdf"></i> Export as PDF
            </button>
            <button class="btn-primary" onclick="exportPayments('excel')">
                <i class="fas fa-file-excel"></i> Export as Excel
            </button>
        </div>
    </div>

    <!-- Payment Details Modal -->
    <div class="payment-details-modal" id="paymentModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Payment Details</h3>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <div id="paymentDetails">
                <!-- Payment details will be loaded here -->
            </div>
        </div>
    </div>

    <script>
    // View payment details
    document.querySelectorAll('.view-payment').forEach(button => {
        button.addEventListener('click', function() {
            const paymentId = this.dataset.paymentId;
            viewPaymentDetails(paymentId);
        });
    });

    // Refund payment
    document.querySelectorAll('.refund-payment').forEach(button => {
        button.addEventListener('click', function() {
            const paymentId = this.dataset.paymentId;
            if (confirm('Are you sure you want to refund this payment?')) {
                refundPayment(paymentId);
            }
        });
    });

    // Delete payment
    document.querySelectorAll('.delete-payment').forEach(button => {
        button.addEventListener('click', function() {
            const paymentId = this.dataset.paymentId;
            if (confirm('Are you sure you want to delete this payment? This action cannot be undone.')) {
                deletePayment(paymentId);
            }
        });
    });

    function viewPaymentDetails(paymentId) {
        // In a real application, this would fetch payment details via AJAX
        const modal = document.getElementById('paymentModal');
        const details = document.getElementById('paymentDetails');
        
        // Simulate loading payment details
        details.innerHTML = `
            <div style="text-align: center; padding: 2rem;">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p>Loading payment details...</p>
            </div>
        `;
        
        modal.style.display = 'flex';
        
        // Simulate API call delay
        setTimeout(() => {
            details.innerHTML = `
                <div class="detail-row">
                    <span class="detail-label">Payment ID:</span>
                    <span>#${paymentId}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Amount:</span>
                    <span>$1,200.00</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Type:</span>
                    <span>Rent</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="payment-status status-completed">Completed</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Date:</span>
                    <span>Dec 15, 2024</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Transaction ID:</span>
                    <span>TXN_123456789</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method:</span>
                    <span>EcoCash</span>
                </div>
            `;
        }, 1000);
    }

    function closeModal() {
        document.getElementById('paymentModal').style.display = 'none';
    }

    function refundPayment(paymentId) {
        // Simulate refund process
        alert(`Refund initiated for payment #${paymentId}. This would connect to the payment gateway in production.`);
    }

    function deletePayment(paymentId) {
        // Simulate delete process
        alert(`Payment #${paymentId} deleted. In production, this would make an API call to delete the payment.`);
    }

    function exportPayments(format) {
        alert(`Exporting payments as ${format.toUpperCase()}. In production, this would generate and download the file.`);
    }

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('paymentModal');
        if (event.target === modal) {
            closeModal();
        }
    });
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>