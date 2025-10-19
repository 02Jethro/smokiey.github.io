<?php
class EcoCash {
    private $merchantCode;
    private $merchantKey;
    private $merchantPin;
    private $baseUrl;

    public function __construct() {
        $this->merchantCode = ECOCASH_MERCHANT_CODE;
        $this->merchantKey = ECOCASH_MERCHANT_KEY;
        $this->merchantPin = ECOCASH_MERCHANT_PIN;
        $this->baseUrl = ECOCASH_BASE_URL;
    }

    public function initiatePayment($phoneNumber, $amount, $currency = 'USD', $description = 'Property Payment') {
        // Generate transaction reference
        $transactionRef = 'ECOCASH_' . uniqid() . '_' . time();
        
        $data = [
            'merchantCode' => $this->merchantCode,
            'merchantKey' => $this->merchantKey,
            'merchantPin' => $this->merchantPin,
            'customerPhoneNumber' => $this->formatPhoneNumber($phoneNumber),
            'amount' => number_format($amount, 2, '.', ''),
            'currency' => $currency,
            'transactionReference' => $transactionRef,
            'description' => $description,
            'callbackUrl' => WEBHOOK_URL . '?gateway=ecocash'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/transactions/initiate');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $result = json_decode($response, true);
            if ($result['status'] === 'success') {
                return [
                    'success' => true,
                    'transaction_reference' => $transactionRef,
                    'status' => 'pending',
                    'message' => 'Payment initiated. Please check your phone to complete the transaction.'
                ];
            }
        }

        error_log("EcoCash Payment Initiation Failed: " . $response);
        return ['success' => false, 'error' => 'Failed to initiate EcoCash payment'];
    }

    public function checkPaymentStatus($transactionReference) {
        $data = [
            'merchantCode' => $this->merchantCode,
            'merchantKey' => $this->merchantKey,
            'transactionReference' => $transactionReference
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/transactions/status');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $result = json_decode($response, true);
            return [
                'success' => true,
                'status' => $result['transactionStatus'],
                'transaction_id' => $result['transactionId'] ?? null,
                'amount' => $result['amount'] ?? null
            ];
        }

        return ['success' => false, 'error' => 'Failed to check payment status'];
    }

    public function refundPayment($transactionId, $amount) {
        $data = [
            'merchantCode' => $this->merchantCode,
            'merchantKey' => $this->merchantKey,
            'originalTransactionId' => $transactionId,
            'amount' => number_format($amount, 2, '.', ''),
            'reason' => 'Customer refund'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/transactions/refund');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $result = json_decode($response, true);
            if ($result['status'] === 'success') {
                return ['success' => true, 'refund_id' => $result['refundId']];
            }
        }

        return ['success' => false, 'error' => 'Failed to process refund'];
    }

    private function formatPhoneNumber($phone) {
        // Format phone number for EcoCash (Zimbabwe format: 26377xxxxxxx)
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strpos($phone, '0') === 0) {
            $phone = '263' . substr($phone, 1);
        } elseif (strpos($phone, '263') !== 0) {
            $phone = '263' . $phone;
        }
        
        return $phone;
    }
}
?>