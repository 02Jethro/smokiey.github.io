<?php
require_once '../../config.php';
require_auth();

if (!isset($_GET['reference'])) {
    redirect('views/payments/make_payment.php');
}

$reference = $_GET['reference'];

$page_title = "Payment Status";
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
        <div class="payment-status-container">
            <div class="status-header">
                <div class="status-icon loading">
                    <div class="spinner"></div>
                </div>
                <h1>Checking Payment Status</h1>
                <p>Please wait while we verify your payment...</p>
            </div>

            <div class="payment-details">
                <div class="detail-row">
                    <span>Reference:</span>
                    <strong><?php echo $reference; ?></strong>
                </div>
                <div class="detail-row">
                    <span>Status:</span>
                    <span id="paymentStatus">Processing...</span>
                </div>
            </div>

            <div class="status-actions">
                <button id="refreshStatus" class="btn-primary">Refresh Status</button>
                <a href="<?php echo view_url('payments/make_payment.php'); ?>" class="btn-secondary">Make Another Payment</a>
            </div>
        </div>
    </div>

    <script>
    function checkPaymentStatus() {
        fetch(`<?php echo BASE_URL; ?>controllers/PaymentController.php?gateway=ecocash_status&reference=<?php echo $reference; ?>`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('paymentStatus').textContent = data.status;
                    
                    if (data.status === 'completed') {
                        document.querySelector('.status-icon').className = 'status-icon success';
                        document.querySelector('.status-icon').innerHTML = '✓';
                        document.querySelector('.status-header h1').textContent = 'Payment Completed!';
                        document.querySelector('.status-header p').textContent = 'Your payment has been successfully processed.';
                    } else if (data.status === 'failed') {
                        document.querySelector('.status-icon').className = 'status-icon error';
                        document.querySelector('.status-icon').innerHTML = '✕';
                        document.querySelector('.status-header h1').textContent = 'Payment Failed';
                        document.querySelector('.status-header p').textContent = 'Your payment could not be processed. Please try again.';
                    }
                }
            })
            .catch(error => {
                console.error('Error checking payment status:', error);
            });
    }

    // Check status immediately
    checkPaymentStatus();

    // Check every 5 seconds
    const statusInterval = setInterval(checkPaymentStatus, 5000);

    // Stop checking after 5 minutes
    setTimeout(() => {
        clearInterval(statusInterval);
    }, 300000);

    document.getElementById('refreshStatus').addEventListener('click', checkPaymentStatus);
    </script>

    <style>
    .payment-status-container {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 3rem;
        text-align: center;
        max-width: 500px;
        margin: 2rem auto;
    }

    .status-header {
        margin-bottom: 2rem;
    }

    .status-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .status-icon.success {
        color: #27ae60;
    }

    .status-icon.error {
        color: #e74c3c;
    }

    .status-icon.loading {
        color: #3498db;
    }

    .spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .payment-details {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
        margin: 2rem 0;
        text-align: left;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid #e9ecef;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .status-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
    }

    @media (max-width: 768px) {
        .status-actions {
            flex-direction: column;
        }
        
        .payment-status-container {
            padding: 2rem 1rem;
        }
    }
    </style>

    <?php include '../includes/footer.php'; ?>
</body>
</html>