<?php
require_once '../config.php';
require_once '../core/EcoCash.php';
require_once '../models/Payment.php';

class PaymentWebhookController {
    private $ecocash;
    private $paymentModel;

    public function __construct() {
        $this->ecocash = new EcoCash();
        $this->paymentModel = new Payment();
    }

    public function handleEcoCashWebhook() {
        // Get the raw POST data
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        // Verify webhook signature (implement based on EcoCash documentation)
        if (!$this->verifyWebhookSignature($data)) {
            http_response_code(401);
            exit;
        }

        $transactionRef = $data['transactionReference'] ?? '';
        $status = $data['transactionStatus'] ?? '';
        $transactionId = $data['transactionId'] ?? '';

        if ($transactionRef && $status === 'completed') {
            $payment = $this->paymentModel->getByGatewayReference($transactionRef);
            
            if ($payment && $payment['status'] === 'initiated') {
                $this->paymentModel->updateStatus(
                    $payment['id'],
                    'completed',
                    $transactionRef,
                    $transactionId
                );

                // Send notification to user
                $this->sendPaymentNotification($payment);
            }
        }

        http_response_code(200);
    }

    private function verifyWebhookSignature($data) {
        // Implement webhook signature verification
        // This should follow EcoCash's webhook security guidelines
        // Typically involves verifying a signature header
        
        // For now, return true (implement proper verification in production)
        return true;
    }

    private function sendPaymentNotification($payment) {
        // Send email or SMS notification to user
        // You can integrate with your notification system here
        error_log("Payment completed: Payment ID " . $payment['id']);
    }
}

// Handle webhook
if ($_POST) {
    $controller = new PaymentWebhookController();
    $controller->handleEcoCashWebhook();
}
?>