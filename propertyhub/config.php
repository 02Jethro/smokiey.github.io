<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'PropertyHub');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application Configuration
define('APP_NAME', 'PropertyHub');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/propertyhub/');

// File Upload Configuration
define('UPLOAD_PATH', dirname(__DIR__) . '/uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// User Types
define('USER_ADMIN', 'admin');
define('USER_PROPERTY_MANAGER', 'property_manager');
define('USER_LANDLORD', 'landlord');
define('USER_TENANT', 'tenant');
define('USER_BUYER', 'buyer');
define('USER_SELLER', 'seller');

// Property Status
define('PROPERTY_AVAILABLE', 'available');
define('PROPERTY_RENTED', 'rented');
define('PROPERTY_SOLD', 'sold');
define('PROPERTY_MAINTENANCE', 'maintenance');

// Payment Status
define('PAYMENT_PENDING', 'pending');
define('PAYMENT_COMPLETED', 'completed');
define('PAYMENT_FAILED', 'failed');

// Payment Gateway Configuration
define('PAYPAL_CLIENT_ID', 'your_paypal_client_id');
define('PAYPAL_CLIENT_SECRET', 'your_paypal_client_secret');
define('PAYPAL_ENVIRONMENT', 'sandbox');
define('PAYPAL_BASE_URL', PAYPAL_ENVIRONMENT === 'sandbox' ? 
    'https://api.sandbox.paypal.com' : 'https://api.paypal.com');

define('ECOCASH_MERCHANT_CODE', 'your_ecocash_merchant_code');
define('ECOCASH_MERCHANT_KEY', 'your_ecocash_merchant_key');
define('ECOCASH_MERCHANT_PIN', 'your_ecocash_merchant_pin');
define('ECOCASH_BASE_URL', 'https://api.ecocash.com/v1');

// Application URLs
define('SUCCESS_URL', BASE_URL . 'views/payments/success.php');
define('CANCEL_URL', BASE_URL . 'views/payments/cancel.php');
define('WEBHOOK_URL', BASE_URL . 'controllers/PaymentWebhookController.php');

// Session Configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the root directory
define('ROOT_DIR', dirname(__DIR__));

// First, manually include the Database class since we need it for autoloader
require_once ROOT_DIR . '/propertyhub/core/Database.php';

// Autoload Classes with absolute paths
spl_autoload_register(function ($class_name) {
    $directories = [
        ROOT_DIR . '/core/',
        ROOT_DIR . '/models/',
        ROOT_DIR . '/controllers/'
    ];
    
    foreach ($directories as $directory) {
        $file = $directory . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    
    // If class not found, log error but don't throw exception
    error_log("Class {$class_name} not found in: " . implode(', ', $directories));
});

// Helper Functions
function asset_url($path) {
    return BASE_URL . 'assets/' . ltrim($path, '/');
}

function view_url($path) {
    return BASE_URL . 'views/' . ltrim($path, '/');
}

function redirect($path) {
    header('Location: ' . BASE_URL . ltrim($path, '/'));
    exit;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_auth() {
    if (!is_logged_in()) {
        redirect('views/login.php');
    }
}

function require_role($allowed_roles) {
    require_auth();
    if (!in_array($_SESSION['user_type'], (array)$allowed_roles)) {
        $_SESSION['error'] = 'Access denied. Insufficient permissions.';
        redirect('views/dashboard.php');
    }
}
?>