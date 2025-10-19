<?php
require_once '../bootstrap.php';
require_once '../core/Auth.php';
require_once '../core/validator.php';
require_once '../models/user.php';

class AuthController {
    private $auth;
    private $validator;
    private $userModel;

    public function __construct() {
        $this->auth = new Auth();
        $this->validator = new Validator();
        $this->userModel = new User();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $this->validator->sanitize($_POST['email']);
            $password = $_POST['password'];

            if ($this->auth->login($email, $password)) {
                redirect('views/dashboard.php');
            } else {
                $_SESSION['error'] = 'Invalid email or password.';
                redirect('views/login.php');
            }
        }
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'username' => $this->validator->sanitize($_POST['username']),
                'email' => $this->validator->sanitize($_POST['email']),
                'password' => $_POST['password'],
                'confirm_password' => $_POST['confirm_password'],
                'first_name' => $this->validator->sanitize($_POST['first_name']),
                'last_name' => $this->validator->sanitize($_POST['last_name']),
                'phone' => $this->validator->sanitize($_POST['phone']),
                'user_type' => $_POST['user_type']
            ];

            $errors = $this->validator->validateRegistration($data);

            if (empty($errors)) {
                // Check if email already exists
                if ($this->userModel->getByEmail($data['email'])) {
                    $_SESSION['error'] = 'Email already registered.';
                    redirect('views/register.php');
                }

                if ($this->auth->register($data)) {
                    $_SESSION['success'] = 'Registration successful! Please login.';
                    redirect('views/login.php');
                } else {
                    $_SESSION['error'] = 'Registration failed. Please try again.';
                }
            } else {
                $_SESSION['errors'] = $errors;
            }

            redirect('views/register.php');
        }
    }

    public function logout() {
        $this->auth->logout();
    }
}

// Handle requests
if (isset($_POST['action'])) {
    $controller = new AuthController();
    
    switch ($_POST['action']) {
        case 'login':
            $controller->login();
            break;
        case 'register':
            $controller->register();
            break;
        case 'logout':
            $controller->logout();
            break;
    }
}
?>