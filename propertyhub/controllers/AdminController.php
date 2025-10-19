<?php
require_once '../config.php';
require_once '../models/User.php';
require_once '../models/Property.php';
require_once '../models/Payment.php';
require_once '../models/Report.php';

class AdminController {
    private $userModel;
    private $propertyModel;
    private $paymentModel;
    private $reportModel;

    public function __construct() {
        $this->userModel = new User();
        $this->propertyModel = new Property();
        $this->paymentModel = new Payment();
        $this->reportModel = new Report();
    }

    // User Management Methods
    public function changePassword() {
        require_auth();
        require_role([USER_ADMIN]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];

            // Validate passwords
            if ($newPassword !== $confirmPassword) {
                $_SESSION['error'] = 'Passwords do not match.';
                redirect('views/admin/users.php');
            }

            if (strlen($newPassword) < 8) {
                $_SESSION['error'] = 'Password must be at least 8 characters long.';
                redirect('views/admin/users.php');
            }

            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $db = Database::getInstance();
            
            if ($db->query($sql, [$hashedPassword, $userId])) {
                $_SESSION['success'] = 'Password updated successfully.';
            } else {
                $_SESSION['error'] = 'Failed to update password.';
            }

            redirect('views/admin/users.php');
        }
    }

    public function deleteUser() {
        require_auth();
        require_role([USER_ADMIN]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'];

            // Prevent admin from deleting themselves
            if ($userId == $_SESSION['user_id']) {
                $_SESSION['error'] = 'You cannot delete your own account.';
                redirect('views/admin/users.php');
            }

            if ($this->userModel->delete($userId)) {
                $_SESSION['success'] = 'User deleted successfully.';
            } else {
                $_SESSION['error'] = 'Failed to delete user.';
            }

            redirect('views/admin/users.php');
        }
    }

    public function updateUserRole() {
        require_auth();
        require_role([USER_ADMIN]);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'];
            $newRole = $_POST['user_type'];

            $sql = "UPDATE users SET user_type = ? WHERE id = ?";
            $db = Database::getInstance();
            
            if ($db->query($sql, [$newRole, $userId])) {
                $_SESSION['success'] = 'User role updated successfully!';
            } else {
                $_SESSION['error'] = 'Failed to update user role.';
            }

            redirect('views/admin/users.php');
        }
    }

    public function toggleUserStatus() {
        require_auth();
        require_role([USER_ADMIN]);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'];
            $currentStatus = $_POST['current_status'];
            $newStatus = $currentStatus === 'active' ? 'suspended' : 'active';

            $sql = "UPDATE users SET status = ? WHERE id = ?";
            $db = Database::getInstance();
            
            if ($db->query($sql, [$newStatus, $userId])) {
                $_SESSION['success'] = "User {$newStatus} successfully!";
            } else {
                $_SESSION['error'] = 'Failed to update user status.';
            }

            redirect('views/admin/users.php');
        }
    }

    // Property Management Methods
    public function deleteProperty() {
        require_auth();
        require_role([USER_ADMIN, USER_PROPERTY_MANAGER]);
        
        if (isset($_POST['property_id'])) {
            if ($this->propertyModel->delete($_POST['property_id'])) {
                $_SESSION['success'] = 'Property deleted successfully!';
            } else {
                $_SESSION['error'] = 'Failed to delete property.';
            }
        }

        redirect('views/admin/properties.php');
    }

    public function updatePropertyStatus() {
        require_auth();
        require_role([USER_ADMIN, USER_PROPERTY_MANAGER]);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $propertyId = $_POST['property_id'];
            $newStatus = $_POST['status'];

            if ($this->propertyModel->update($propertyId, ['status' => $newStatus])) {
                $_SESSION['success'] = 'Property status updated successfully!';
            } else {
                $_SESSION['error'] = 'Failed to update property status.';
            }

            redirect('views/admin/properties.php');
        }
    }

    // Payment Management Methods
    public function updatePaymentStatus() {
        require_auth();
        require_role([USER_ADMIN]);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $paymentId = $_POST['payment_id'];
            $newStatus = $_POST['status'];

            if ($this->paymentModel->updateStatus($paymentId, $newStatus)) {
                $_SESSION['success'] = 'Payment status updated successfully!';
            } else {
                $_SESSION['error'] = 'Failed to update payment status.';
            }

            redirect('views/admin/payments.php');
        }
    }

    public function refundPayment() {
        require_auth();
        require_role([USER_ADMIN]);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $paymentId = $_POST['payment_id'];
            $refundAmount = $_POST['refund_amount'];
            $reason = $_POST['refund_reason'];

            // Implement refund logic here
            // This would typically integrate with your payment gateway
            // For now, we'll just update the payment status
            if ($this->paymentModel->updateStatus($paymentId, 'refunded')) {
                
                // Log refund activity
                $this->logAdminActivity(
                    $_SESSION['user_id'],
                    'payment_refund',
                    "Refunded payment #{$paymentId} - Amount: {$refundAmount}, Reason: {$reason}"
                );
                
                $_SESSION['success'] = 'Payment refunded successfully!';
            } else {
                $_SESSION['error'] = 'Failed to process refund.';
            }

            redirect('views/admin/payments.php');
        }
    }

    // Reporting Methods
    public function getDashboardStats() {
        require_auth();
        require_role([USER_ADMIN]);
        
        $stats = [
            'total_users' => $this->userModel->getAll() ? count($this->userModel->getAll()) : 0,
            'total_properties' => $this->propertyModel->getAll() ? count($this->propertyModel->getAll()) : 0,
            'total_landlords' => $this->userModel->countByType(USER_LANDLORD),
            'total_tenants' => $this->userModel->countByType(USER_TENANT),
            'total_agents' => $this->userModel->countByType(USER_PROPERTY_MANAGER),
            'recent_payments' => $this->paymentModel->getByUser(1, 'landlord') ? count($this->paymentModel->getByUser(1, 'landlord')) : 0,
            'user_stats' => $this->reportModel->getUserStats(),
            'revenue_stats' => $this->reportModel->getRevenueStats(),
            'property_stats' => $this->reportModel->getPropertyStats()
        ];

        header('Content-Type: application/json');
        echo json_encode($stats);
        exit;
    }

    public function generateReport() {
        require_auth();
        require_role([USER_ADMIN]);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reportType = $_POST['report_type'];
            $startDate = $_POST['start_date'];
            $endDate = $_POST['end_date'];
            $format = $_POST['format'] ?? 'html';

            $data = [];

            switch ($reportType) {
                case 'user_activity':
                    $data = $this->reportModel->getUserActivityReport($startDate, $endDate);
                    break;
                case 'financial':
                    $data = $this->reportModel->getFinancialReport(null, 'admin', $startDate, $endDate);
                    break;
                case 'property_performance':
                    $data = $this->reportModel->getPropertyPerformance(null);
                    break;
                case 'maintenance':
                    $data = $this->reportModel->getMaintenanceReport(null, 'admin', $startDate, $endDate);
                    break;
            }

            if ($format === 'json') {
                header('Content-Type: application/json');
                echo json_encode($data);
                exit;
            } else {
                // For HTML format, store in session and redirect to report view
                $_SESSION['report_data'] = $data;
                $_SESSION['report_type'] = $reportType;
                $_SESSION['report_period'] = "{$startDate} to {$endDate}";
                redirect('views/admin/reports/view.php');
            }
        }
    }

    // System Management Methods
    public function updateSystemSettings() {
        require_auth();
        require_role([USER_ADMIN]);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $settings = [
                'site_name' => $_POST['site_name'] ?? 'PropertyHub',
                'site_email' => $_POST['site_email'] ?? 'info@propertyhub.co.zw',
                'currency' => $_POST['currency'] ?? 'USD',
                'payment_gateway' => $_POST['payment_gateway'] ?? 'ecocash',
                'maintenance_mode' => isset($_POST['maintenance_mode']) ? 1 : 0
            ];

            // Save settings to database or configuration file
            // This is a simplified implementation
            foreach ($settings as $key => $value) {
                $this->saveSystemSetting($key, $value);
            }

            $_SESSION['success'] = 'System settings updated successfully!';
            redirect('views/admin/settings.php');
        }
    }

    public function sendBulkNotification() {
        require_auth();
        require_role([USER_ADMIN]);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userType = $_POST['user_type'] ?? 'all';
            $subject = $_POST['subject'];
            $message = $_POST['message'];

            // Get users based on type
            $users = [];
            if ($userType === 'all') {
                $users = $this->userModel->getAll();
            } else {
                $users = $this->userModel->getAll($userType);
            }

            $sentCount = 0;
            foreach ($users as $user) {
                if ($this->sendEmail($user['email'], $subject, $message)) {
                    $sentCount++;
                }
            }

            $_SESSION['success'] = "Notification sent to {$sentCount} users successfully!";
            redirect('views/admin/notifications.php');
        }
    }

    // Utility Methods
    private function logAdminActivity($adminId, $action, $details) {
        $sql = "INSERT INTO admin_activities (admin_id, action, details, ip_address) 
                VALUES (?, ?, ?, ?)";
        $db = Database::getInstance();
        $db->query($sql, [
            $adminId,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR']
        ]);
    }

    private function saveSystemSetting($key, $value) {
        $sql = "INSERT INTO system_settings (setting_key, setting_value) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = ?";
        $db = Database::getInstance();
        return $db->query($sql, [$key, $value, $value]);
    }

    private function sendEmail($to, $subject, $message) {
        // Implement email sending logic
        // This could use PHPMailer, SwiftMailer, or your preferred email service
        // For now, return true for simulation
        error_log("Email sent to: {$to}, Subject: {$subject}");
        return true;
    }

    // Bulk Operations
    public function bulkUserAction() {
        require_auth();
        require_role([USER_ADMIN]);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userIds = $_POST['user_ids'] ?? [];
            $action = $_POST['bulk_action'];

            if (empty($userIds)) {
                $_SESSION['error'] = 'No users selected.';
                redirect('views/admin/users.php');
            }

            $successCount = 0;
            $db = Database::getInstance();

            foreach ($userIds as $userId) {
                switch ($action) {
                    case 'delete':
                        if ($userId != $_SESSION['user_id'] && $this->userModel->delete($userId)) {
                            $successCount++;
                        }
                        break;
                    case 'activate':
                        $db->query("UPDATE users SET status = 'active' WHERE id = ?", [$userId]);
                        $successCount++;
                        break;
                    case 'suspend':
                        if ($userId != $_SESSION['user_id']) {
                            $db->query("UPDATE users SET status = 'suspended' WHERE id = ?", [$userId]);
                            $successCount++;
                        }
                        break;
                }
            }

            $_SESSION['success'] = "Bulk action completed: {$successCount} users affected.";
            redirect('views/admin/users.php');
        }
    }
}

// Handle requests
if (isset($_POST['action'])) {
    $controller = new AdminController();
    
    switch ($_POST['action']) {
        case 'change_password':
            $controller->changePassword();
            break;
        case 'delete_user':
            $controller->deleteUser();
            break;
        case 'update_user_role':
            $controller->updateUserRole();
            break;
        case 'toggle_user_status':
            $controller->toggleUserStatus();
            break;
        case 'delete_property':
            $controller->deleteProperty();
            break;
        case 'update_property_status':
            $controller->updatePropertyStatus();
            break;
        case 'update_payment_status':
            $controller->updatePaymentStatus();
            break;
        case 'refund_payment':
            $controller->refundPayment();
            break;
        case 'generate_report':
            $controller->generateReport();
            break;
        case 'update_system_settings':
            $controller->updateSystemSettings();
            break;
        case 'send_bulk_notification':
            $controller->sendBulkNotification();
            break;
        case 'bulk_user_action':
            $controller->bulkUserAction();
            break;
    }
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    $controller = new AdminController();
    
    switch ($_GET['action']) {
        case 'get_dashboard_stats':
            $controller->getDashboardStats();
            break;
    }
}
?>