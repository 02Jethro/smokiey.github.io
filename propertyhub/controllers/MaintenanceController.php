<?php
require_once '../config.php';
require_once '../models/Maintenance.php';

class MaintenanceController {
    private $maintenanceModel;

    public function __construct() {
        $this->maintenanceModel = new Maintenance();
    }

    public function createRequest() {
        require_auth();
        require_role([USER_TENANT]);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'property_id' => $_POST['property_id'],
                'tenant_id' => $_SESSION['user_id'],
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'priority' => $_POST['priority']
            ];

            if ($this->maintenanceModel->create($data)) {
                $_SESSION['success'] = 'Maintenance request submitted successfully!';
            } else {
                $_SESSION['error'] = 'Failed to submit maintenance request.';
            }

            redirect('views/maintenance/request.php');
        }
    }

    public function updateStatus() {
        require_auth();
        require_role([USER_LANDLORD, USER_PROPERTY_MANAGER]);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $requestId = $_POST['request_id'];
            $status = $_POST['status'];
            $assignedTo = $_POST['assigned_to'] ?? null;

            if ($this->maintenanceModel->updateStatus($requestId, $status, $assignedTo)) {
                $_SESSION['success'] = 'Maintenance request updated successfully!';
            } else {
                $_SESSION['error'] = 'Failed to update maintenance request.';
            }

            redirect('views/maintenance/manage.php');
        }
    }
}

// Handle requests
if (isset($_POST['action'])) {
    $controller = new MaintenanceController();
    
    switch ($_POST['action']) {
        case 'create_request':
            $controller->createRequest();
            break;
        case 'update_status':
            $controller->updateStatus();
            break;
    }
}
?>