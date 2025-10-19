<?php
class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function login($email, $password) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->db->query($sql, [$email]);
        
        if ($stmt) {
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                return true;
            }
        }
        return false;
    }

    public function register($data) {
        $sql = "INSERT INTO users (username, email, password, first_name, last_name, phone, user_type) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $result = $this->db->query($sql, [
            $data['username'],
            $data['email'],
            $hashed_password,
            $data['first_name'],
            $data['last_name'],
            $data['phone'],
            $data['user_type']
        ]);

        return $result ? $this->db->lastInsertId() : false;
    }

    public function logout() {
        session_destroy();
        redirect('');
    }

    public function check() {
        return isset($_SESSION['user_id']);
    }

    public function user() {
        if ($this->check()) {
            return [
                'id' => $_SESSION['user_id'],
                'user_type' => $_SESSION['user_type'],
                'username' => $_SESSION['username'],
                'email' => $_SESSION['email'],
                'first_name' => $_SESSION['first_name'],
                'last_name' => $_SESSION['last_name']
            ];
        }
        return null;
    }

    public function isAdmin() {
        return $this->check() && $_SESSION['user_type'] === USER_ADMIN;
    }

    public function isLandlord() {
        return $this->check() && $_SESSION['user_type'] === USER_LANDLORD;
    }

    public function isTenant() {
        return $this->check() && $_SESSION['user_type'] === USER_TENANT;
    }
}
?>