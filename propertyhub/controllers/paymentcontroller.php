<?php
require_once '../config.php';
require_once '../core/PayPal.php';
require_once '../core/EcoCash.php';
require_once '../models/Payment.php';
require_once '../models/Property.php';

class PaymentController {
    private $paypal;
    private $ecocash;
    private $paymentModel;
    private $propertyModel;

    public function __construct() {
        $this->paypal = new PayPal();
        $this->ecocash = new EcoCash();
        $this->paymentModel = new Payment();
        $this->propertyModel = new Property();
    }

     public function getTenantProperties($tenantId) {
        $sql = "SELECT p.*, t.rent_amount, t.start_date, t.end_date 
                FROM properties p
                INNER JOIN tenancies t ON p.id = t.property_id
                WHERE t.tenant_id = ? AND t.status = 'active'
                ORDER BY p.created_at DESC";
        $stmt = $this->db->query($sql, [$tenantId]);
        return $stmt ? $stmt->fetchAll() : [];
    }

    public function initiatePayment() {
        require_auth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $paymentMethod = $_POST['payment_method'];
            $paymentData = [
                'tenant_id' => $_SESSION['user_id'],
                'landlord_id' => $_POST['landlord_id'],
                'property_id' => $_POST['property_id'],
                'amount' => $_POST['amount'],
                'payment_type' => $_POST['payment_type'],
                'payment_method' => $paymentMethod,
                'status' => 'pending',
                'due_date' => $_POST['due_date']
            ];

            // Create payment record
            $paymentId = $this->paymentModel->create($paymentData);
            
            if (!$paymentId) {
                $_SESSION['error'] = 'Failed to create payment record.';
                redirect('views/payments/make_payment.php');
            }

            switch ($paymentMethod) {
                case 'paypal':
                    return $this->initiatePayPalPayment($paymentId, $paymentData);
                    
                case 'ecocash':
                    return $this->initiateEcoCashPayment($paymentId, $paymentData);
                    
                default:
                    $_SESSION['error'] = 'Invalid payment method selected.';
                    redirect('views/payments/make_payment.php');
            }
        }
    }

    private function initiatePayPalPayment($paymentId, $paymentData) {
        $property = $this->propertyModel->getById($paymentData['property_id']);
        $description = "Payment for {$property['title']} - {$paymentData['payment_type']}";

        $result = $this->paypal->createOrder(
            $paymentData['amount'],
            'USD',
            $description
        );

        if ($result['success']) {
            // Update payment with gateway reference
            $this->paymentModel->updateStatus(
                $paymentId, 
                'initiated', 
                $result['order_id']
            );

            // Redirect to PayPal
            header('Location: ' . $result['approval_url']);
            exit;
        } else {
            $_SESSION['error'] = $result['error'];
            redirect('views/payments/make_payment.php');
        }
    }

    private function initiateEcoCashPayment($paymentId, $paymentData) {
        $phoneNumber = $_POST['ecocash_phone'] ?? '';
        
        if (empty($phoneNumber)) {
            $_SESSION['error'] = 'Phone number is required for EcoCash payments.';
            redirect('views/payments/make_payment.php');
        }

        $property = $this->propertyModel->getById($paymentData['property_id']);
        $description = "Payment for {$property['title']}";

        $result = $this->ecocash->initiatePayment(
            $phoneNumber,
            $paymentData['amount'],
            'USD',
            $description
        );

        if ($result['success']) {
            // Update payment with gateway reference
            $this->paymentModel->updateStatus(
                $paymentId, 
                'initiated', 
                $result['transaction_reference']
            );

            $_SESSION['success'] = $result['message'];
            redirect('views/payments/status.php?reference=' . $result['transaction_reference']);
        } else {
            $_SESSION['error'] = $result['error'];
            redirect('views/payments/make_payment.php');
        }
    }

    public function handlePayPalCallback() {
        if (isset($_GET['token']) && isset($_GET['PayerID'])) {
            $orderId = $_GET['token'];
            
            // Capture the payment
            $result = $this->paypal->captureOrder($orderId);
            
            if ($result['success']) {
                // Update payment status
                $payment = $this->paymentModel->getByGatewayReference($orderId);
                if ($payment) {
                    $this->paymentModel->updateStatus(
                        $payment['id'],
                        'completed',
                        $orderId,
                        $result['transaction_id']
                    );
                    
                    $_SESSION['success'] = 'Payment completed successfully!';
                    redirect('views/payments/success.php?id=' . $payment['id']);
                }
            }
        }
        
        $_SESSION['error'] = 'Payment failed or was cancelled.';
        redirect('views/payments/cancel.php');
    }

    public function checkEcoCashStatus() {
        if (isset($_GET['reference'])) {
            $reference = $_GET['reference'];
            $result = $this->ecocash->checkPaymentStatus($reference);
            
            if ($result['success']) {
                $payment = $this->paymentModel->getByGatewayReference($reference);
                
                if ($payment && $result['status'] === 'completed') {
                    $this->paymentModel->updateStatus(
                        $payment['id'],
                        'completed',
                        $reference,
                        $result['transaction_id']
                    );
                    
                    $_SESSION['success'] = 'Payment completed successfully!';
                    redirect('views/payments/success.php?id=' . $payment['id']);
                } elseif ($result['status'] === 'failed') {
                    $_SESSION['error'] = 'Payment failed. Please try again.';
                    redirect('views/payments/cancel.php');
                } else {
                    // Payment still pending, show status page
                    $paymentStatus = $result['status'];
                    include '../views/payments/ecocash_status.php';
                    exit;
                }
            }
        }
        
        $_SESSION['error'] = 'Invalid payment reference.';
        redirect('views/payments/cancel.php');
    }
}

// Handle requests
if (isset($_POST['action'])) {
    $controller = new PaymentController();
    
    switch ($_POST['action']) {
        case 'initiate_payment':
            $controller->initiatePayment();
            break;
    }
}

// Handle callbacks
if (isset($_GET['gateway'])) {
    $controller = new PaymentController();
    
    switch ($_GET['gateway']) {
        case 'paypal_callback':
            $controller->handlePayPalCallback();
            break;
        case 'ecocash_status':
            $controller->checkEcoCashStatus();
            break;
    }
}

public function getTenantProperties($tenantId) {
        $sql = "SELECT p.*, t.rent_amount, t.start_date, t.end_date 
                FROM properties p
                INNER JOIN tenancies t ON p.id = t.property_id
                WHERE t.tenant_id = ? AND t.status = 'active'
                ORDER BY p.created_at DESC";
        $stmt = $this->db->query($sql, [$tenantId]);
        return $stmt ? $stmt->fetchAll() : [];
    }

    // ... rest of your existing Payment model methods
    public function create($data) {
        $sql = "INSERT INTO payments (tenant_id, landlord_id, property_id, amount, payment_type, payment_method, payment_gateway, gateway_reference, status, due_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->query($sql, [
            $data['tenant_id'],
            $data['landlord_id'],
            $data['property_id'],
            $data['amount'],
            $data['payment_type'],
            $data['payment_method'],
            $data['payment_gateway'] ?? null,
            $data['gateway_reference'] ?? null,
            $data['status'],
            $data['due_date']
        ]) ? $this->db->lastInsertId() : false;
    }

    public function getPending($userId, $userType) {
        if ($userType == 'tenant') {
            $sql = "SELECT * FROM payments WHERE tenant_id = ? AND status = 'pending'";
        } else {
            $sql = "SELECT * FROM payments WHERE landlord_id = ? AND status = 'pending'";
        }

        $stmt = $this->db->query($sql, [$userId]);
        return $stmt ? $stmt->fetchAll() : [];
    }
?>