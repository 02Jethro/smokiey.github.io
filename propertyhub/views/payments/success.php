<?php
require_once '../../config.php';
require_auth();

if (isset($_GET['id'])) {
    require_once '../../models/Payment.php';
    $paymentModel = new Payment();
    $payment = $paymentModel->getById($_GET['id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - PropertyHub</title>
    <link rel="stylesheet" href="<?php echo asset_url('css/style.css'); ?>">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <div class="payment-success">
            <div class="success-icon">✓</div>
            <h1>Payment Successful!</h1>
            <p>Your payment has been processed successfully.</p>
            
            <?php if (isset($payment)): ?>
            <div class="payment-details">
                <h3>Payment Details</h3>
                <div class="detail-row">
                    <span>Amount:</span>
                    <strong>$<?php echo number_format($payment['amount'], 2); ?></strong>
                </div>
                <div class="detail-row">
                    <span>Payment Type:</span>
                    <span><?php echo ucfirst($payment['payment_type']); ?></span>
                </div>
                <div class="detail-row">
                    <span>Transaction ID:</span>
                    <span><?php echo $payment['transaction_id']; ?></span>
                </div>
                <div class="detail-row">
                    <span>Date:</span>
                    <span><?php echo date('F j, Y g:i A', strtotime($payment['payment_date'])); ?></span>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="action-buttons">
                <a href="<?php echo view_url('payments/history.php'); ?>" class="btn-primary">View Payment History</a>
                <a href="<?php echo view_url('dashboard.php'); ?>" class="btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>