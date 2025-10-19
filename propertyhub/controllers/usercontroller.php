<?php
require_once '../config.php';
require_once '../core/Validator.php';
require_once '../models/User.php';

class UserController {
    private $validator;
    private $userModel;

    public function __construct() {
        $this->validator = new Validator();
        $this->userModel = new User();
    }

    public function updateProfile() {
        require_auth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'first_name' => $this->validator->sanitize($_POST['first_name']),
                'last_name' => $this->validator->sanitize($_POST['last_name']),
                'phone' => $this->validator->sanitize($_POST['phone'])
            ];

            if ($this->userModel->update($_SESSION['user_id'], $data)) {
                // Update session data
                $_SESSION['first_name'] = $data['first_name'];
                $_SESSION['last_name'] = $data['last_name'];
                
                $_SESSION['success'] = 'Profile updated successfully!';
            } else {
                $_SESSION['error'] = 'Failed to update profile.';
            }

            redirect('views/profile.php');
        }
    }

    public function changePassword() {
        require_auth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPassword = $_POST['current_password'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];

            // Validate passwords
            if ($newPassword !== $confirmPassword) {
                $_SESSION['error'] = 'New passwords do not match.';
                redirect('views/profile.php');
            }

            if (strlen($newPassword) < 8) {
                $_SESSION['error'] = 'New password must be at least 8 characters long.';
                redirect('views/profile.php');
            }

            // Get user and verify current password
            $user = $this->userModel->getById($_SESSION['user_id']);
            if (!$user || !password_verify($currentPassword, $user['password'])) {
                $_SESSION['error'] = 'Current password is incorrect.';
                redirect('views/profile.php');
            }

            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $db = Database::getInstance();
            
            if ($db->query($sql, [$hashedPassword, $_SESSION['user_id']])) {
                $_SESSION['success'] = 'Password changed successfully!';
            } else {
                $_SESSION['error'] = 'Failed to change password.';
            }

            redirect('views/profile.php');
        }
    }
}

// Handle requests
if (isset($_POST['action'])) {
    $controller = new UserController();
    
    switch ($_POST['action']) {
        case 'update_profile':
            $controller->updateProfile();
            break;
        case 'change_password':
            $controller->changePassword();
            break;
    }
}
?>