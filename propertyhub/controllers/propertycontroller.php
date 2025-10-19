<?php
require_once '../config.php';
require_once '../core/Validator.php';
require_once '../models/Property.php';

class PropertyController {
    private $validator;
    private $propertyModel;

    public function __construct() {
        $this->validator = new Validator();
        $this->propertyModel = new Property();
    }

    public function create() {
        require_auth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $this->validator->sanitize($_POST['title']),
                'description' => $this->validator->sanitize($_POST['description']),
                'type' => $_POST['type'],
                'price' => $_POST['price'],
                'address' => $this->validator->sanitize($_POST['address']),
                'city' => $this->validator->sanitize($_POST['city']),
                'state' => $_POST['state'],
                'zip_code' => $this->validator->sanitize($_POST['zip_code']),
                'bedrooms' => $_POST['bedrooms'] ?? 0,
                'bathrooms' => $_POST['bathrooms'] ?? 0,
                'area_sqft' => $_POST['area_sqft'] ?? 0,
                'owner_id' => $_SESSION['user_id'],
                'status' => $_POST['status'] ?? 'available'
            ];

            $errors = $this->validator->validateProperty($data);

            if (empty($errors)) {
                if ($this->propertyModel->create($data)) {
                    $_SESSION['success'] = 'Property added successfully!';
                    redirect('views/properties/list.php');
                } else {
                    $_SESSION['error'] = 'Failed to add property.';
                }
            } else {
                $_SESSION['errors'] = $errors;
            }

            redirect('views/properties/add.php');
        }
    }

    public function update() {
        require_auth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $data = [
                'title' => $this->validator->sanitize($_POST['title']),
                'description' => $this->validator->sanitize($_POST['description']),
                'type' => $_POST['type'],
                'price' => $_POST['price'],
                'address' => $this->validator->sanitize($_POST['address']),
                'city' => $this->validator->sanitize($_POST['city']),
                'state' => $_POST['state'],
                'zip_code' => $this->validator->sanitize($_POST['zip_code']),
                'bedrooms' => $_POST['bedrooms'] ?? 0,
                'bathrooms' => $_POST['bathrooms'] ?? 0,
                'area_sqft' => $_POST['area_sqft'] ?? 0,
                'status' => $_POST['status']
            ];

            $errors = $this->validator->validateProperty($data);

            if (empty($errors)) {
                if ($this->propertyModel->update($id, $data)) {
                    $_SESSION['success'] = 'Property updated successfully!';
                } else {
                    $_SESSION['error'] = 'Failed to update property.';
                }
            } else {
                $_SESSION['errors'] = $errors;
            }

            redirect('views/properties/list.php');
        }
    }

    public function delete() {
        require_auth();
        
        if (isset($_POST['id'])) {
            if ($this->propertyModel->delete($_POST['id'])) {
                $_SESSION['success'] = 'Property deleted successfully!';
            } else {
                $_SESSION['error'] = 'Failed to delete property.';
            }
        }

        redirect('views/properties/list.php');
    }
}

// Handle requests
if (isset($_POST['action'])) {
    $controller = new PropertyController();
    
    switch ($_POST['action']) {
        case 'create':
            $controller->create();
            break;
        case 'update':
            $controller->update();
            break;
        case 'delete':
            $controller->delete();
            break;
    }
}
?>