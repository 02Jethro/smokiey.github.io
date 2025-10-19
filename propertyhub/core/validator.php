<?php
class Validator {
    
    public function validateRegistration($data) {
        $errors = [];

        // Required fields
        $required = ['username', 'email', 'password', 'confirm_password', 'first_name', 'last_name', 'user_type'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
            }
        }

        // Email validation
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        // Username validation
        if (!empty($data['username']) && !preg_match('/^[a-zA-Z0-9_]{3,20}$/', $data['username'])) {
            $errors['username'] = 'Username must be 3-20 characters and contain only letters, numbers, and underscores.';
        }

        // Password strength
        if (!empty($data['password'])) {
            if (strlen($data['password']) < 8) {
                $errors['password'] = 'Password must be at least 8 characters long.';
            } elseif (!preg_match('/[A-Z]/', $data['password'])) {
                $errors['password'] = 'Password must contain at least one uppercase letter.';
            } elseif (!preg_match('/[a-z]/', $data['password'])) {
                $errors['password'] = 'Password must contain at least one lowercase letter.';
            } elseif (!preg_match('/[0-9]/', $data['password'])) {
                $errors['password'] = 'Password must contain at least one number.';
            }
        }

        // Password confirmation
        if ($data['password'] !== $data['confirm_password']) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }

        return $errors;
    }

    public function validateProperty($data) {
        $errors = [];

        $required = ['title', 'type', 'price', 'address', 'city', 'state', 'zip_code'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
            }
        }

        if (!empty($data['price']) && (!is_numeric($data['price']) || $data['price'] <= 0)) {
            $errors['price'] = 'Price must be a valid positive number.';
        }

        if (!empty($data['bedrooms']) && (!is_numeric($data['bedrooms']) || $data['bedrooms'] < 0)) {
            $errors['bedrooms'] = 'Bedrooms must be a valid non-negative number.';
        }

        return $errors;
    }

    public function sanitize($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitize'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function validatePhone($phone) {
        return preg_match('/^\+?[0-9\s\-\(\)]{10,}$/', $phone);
    }
}
?>