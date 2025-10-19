<?php
require_once '../../config.php';
require_auth();

require_once '../../models/Payment.php';
$paymentModel = new Payment();

$payments = $paymentModel->getByUser($_SESSION['user_id'], $_SESSION['user_type']);

$page_title = "Payment History";
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
            <h1>Payment History</h1>
            <p>View your payment transactions and history</p>
        </div>

        <div class="payments-history">
            <?php if (empty($payments)): ?>
                <div class="no-payments">
                    <h3>No payment history found</h3>
                    <p>You haven't made any payments yet.</p>
                    <?php if ($_SESSION['user_type'] == 'tenant'): ?>
                        <a href="<?php echo view_url('payments/make_payment.php'); ?>" class="btn-primary">Make Your First Payment</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="payments-list">
                    <?php foreach ($payments as $payment): ?>
                    <div class="payment-item">
                        <div class="payment-main">
                            <div class="payment-info">
                                <h4><?php echo ucfirst($payment['payment_type']); ?> Payment</h4>
                                <p class="property-name"><?php echo $payment['property_title']; ?></p>
                                <p class="payment-date"><?php echo date('F j, Y', strtotime($payment['payment_date'] ?: $payment['created_at'])); ?></p>
                            </div>
                            <div class="payment-amount">
                                <strong>$<?php echo number_format($payment['amount'], 2); ?></strong>
                            </div>
                        </div>
                        <div class="payment-details">
                            <div class="payment-method">
                                <span>Method: <?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></span>
                            </div>
                            <div class="payment-status">
                                <span class="status-<?php echo $payment['status']; ?>"><?php echo ucfirst($payment['status']); ?></span>
                            </div>
                            <?php if ($payment['transaction_id']): ?>
                            <div class="transaction-id">
                                <span>Transaction: <?php echo $payment['transaction_id']; ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <style>
    .payments-history {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 2rem;
    }

    .no-payments {
        text-align: center;
        padding: 3rem;
        color: #666;
    }

    .payments-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .payment-item {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 1.5rem;
        transition: box-shadow 0.3s ease;
    }

    .payment-item:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .payment-main {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .payment-info h4 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
    }

    .property-name {
        color: #666;
        margin: 0 0 0.5rem 0;
        font-size: 0.9rem;
    }

    .payment-date {
        color: #999;
        margin: 0;
        font-size: 0.8rem;
    }

    .payment-amount {
        font-size: 1.5rem;
        font-weight: bold;
        color: #27ae60;
    }

    .payment-details {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 1rem;
        border-top: 1px solid #f8f9fa;
        font-size: 0.9rem;
        color: #666;
    }

    .payment-status span {
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: bold;
        text-transform: uppercase;
    }

    .status-completed { background: #27ae60; color: white; }
    .status-pending { background: #f39c12; color: white; }
    .status-failed { background: #e74c3c; color: white; }

    @media (max-width: 768px) {
        .payment-main {
            flex-direction: column;
            gap: 1rem;
        }
        
        .payment-details {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
    }
    </style>

    <?php include '../includes/footer.php'; ?>
</body>
</html>