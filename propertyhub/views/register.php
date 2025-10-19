<?php
require_once '../bootstrap.php';

if (is_logged_in()) {
    redirect('views/dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - PropertyHub</title>
    <link rel="stylesheet" href="<?php echo asset_url('css/style.css'); ?>">
        <style>
    * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.auth-container {
    width: 100%;
    max-width: 440px;
    margin: 0 auto;
}

.auth-form {
    background: #ffffff;
    padding: 2.5rem;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    border: 1px solid #e9ecef;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.auth-form:hover {
    transform: translateY(-5px);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
}

.logo {
    text-align: center;
    margin-bottom: 2rem;
}

.logo h2 {
    color: #2c3e50;
    font-size: 2.2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.logo p {
    color: #7f8c8d;
    font-size: 1rem;
    font-weight: 500;
}

.alert {
    padding: 1rem;
    border-radius: 10px;
    margin-bottom: 1.5rem;
    font-weight: 500;
    border-left: 4px solid;
    animation: slideIn 0.3s ease;
}

.alert-error {
    background: #fee;
    border-left-color: #e74c3c;
    color: #c0392b;
}

.alert-success {
    background: #efe;
    border-left-color: #27ae60;
    color: #27ae60;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-group {
    margin-bottom: 1.5rem;
    position: relative;
}

.form-group input {
    width: 100%;
    padding: 1rem 1rem 1rem 3rem;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #ffffff;
    color: #2c3e50;
}

.form-group input:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    transform: translateY(-2px);
}

.form-group input::placeholder {
    color: #95a5a6;
}

.form-group:before {
    content: '';
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    color: #bdc3c7;
    z-index: 2;
}

.form-group:nth-child(1):before {
    content: '\f0e0'; /* envelope icon */
}

.form-group:nth-child(2):before {
    content: '\f023'; /* lock icon */
}

/* SUBMIT BUTTON - KEEPING ORIGINAL STYLES */
.btn-primary {
    width: 100%;
    padding: 1rem;
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(52, 152, 219, 0.3);
}

.btn-primary:active {
    transform: translateY(0);
}

.btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.btn-primary:hover::before {
    left: 100%;
}

.auth-links {
    text-align: center;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #ecf0f1;
}

.auth-links p {
    color: #7f8c8d;
    margin-bottom: 0.5rem;
}

.auth-links a {
    color: #3498db;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.auth-links a:hover {
    color: #2980b9;
    text-decoration: underline;
}

.password-toggle {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #bdc3c7;
    cursor: pointer;
    padding: 0.5rem;
    transition: color 0.3s ease;
}

.password-toggle:hover {
    color: #3498db;
}

/* Additional Features */
.feature-list {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 12px;
    margin-top: 2rem;
    border-left: 4px solid #3498db;
}

.feature-list h4 {
    color: #2c3e50;
    margin-bottom: 1rem;
    font-size: 1rem;
}

.feature-list ul {
    list-style: none;
    padding: 0;
}

.feature-list li {
    padding: 0.5rem 0;
    color: #7f8c8d;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.feature-list li i {
    color: #3498db;
    font-size: 0.8rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    body {
        padding: 15px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .auth-container {
        max-width: 100%;
    }

    .auth-form {
        padding: 2rem 1.5rem;
        margin: 0;
        border-radius: 15px;
    }

    .logo h2 {
        font-size: 1.8rem;
    }

    .logo p {
        font-size: 0.9rem;
    }

    .form-group input {
        padding: 0.875rem 0.875rem 0.875rem 2.5rem;
        font-size: 0.9rem;
    }

    .btn-primary {
        padding: 0.875rem;
        font-size: 1rem;
    }

    .feature-list {
        padding: 1rem;
    }

    .feature-list h4 {
        font-size: 0.9rem;
    }

    .feature-list li {
        font-size: 0.85rem;
    }
}

@media (max-width: 480px) {
    .auth-form {
        padding: 1.5rem 1rem;
        border-radius: 12px;
    }

    .logo h2 {
        font-size: 1.6rem;
    }

    .form-group input {
        padding: 0.75rem 0.75rem 0.75rem 2.25rem;
    }

    .btn-primary {
        padding: 0.75rem;
    }

    .auth-links {
        margin-top: 1.5rem;
        padding-top: 1rem;
    }

    .feature-list {
        margin-top: 1.5rem;
        padding: 1rem 0.75rem;
    }
}

@media (max-height: 600px) {
    body {
        align-items: flex-start;
        padding-top: 2rem;
        padding-bottom: 2rem;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .auth-form {
        background: #ffffff;
        color: #2c3e50;
    }

    .logo h2 {
        color: #2c3e50;
    }

    .logo p {
        color: #7f8c8d;
    }

    .form-group input {
        background: #ffffff;
        border-color: #e9ecef;
        color: #2c3e50;
    }

    .form-group input:focus {
        border-color: #3498db;
        background: #ffffff;
    }

    .form-group input::placeholder {
        color: #95a5a6;
    }

    .feature-list {
        background: #f8f9fa;
        border-left-color: #3498db;
    }

    .feature-list h4 {
        color: #2c3e50;
    }

    .feature-list li {
        color: #7f8c8d;
    }

    .auth-links p {
        color: #7f8c8d;
    }
}

/* Loading state */
.btn-primary.loading {
    pointer-events: none;
    opacity: 0.8;
}

.btn-primary.loading::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    top: 50%;
    left: 50%;
    margin: -10px 0 0 -10px;
    border: 2px solid transparent;
    border-top: 2px solid #ffffff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* Focus styles for keyboard navigation */
.btn-primary:focus,
.form-group input:focus,
.auth-links a:focus {
    outline: 2px solid #3498db;
    outline-offset: 2px;
}
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-form">
            <div class="logo">
                <h2>PropertyHub</h2>
                <p>Create your account</p>
            </div>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['errors'])): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($_SESSION['errors'] as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; unset($_SESSION['errors']); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?php echo BASE_URL; ?>controllers/AuthController.php" method="POST">
                <input type="hidden" name="action" value="register">
                
                <div class="form-row">
                    <div class="form-group">
                        <input type="text" name="first_name" placeholder="First Name" required 
                               value="<?php echo $_POST['first_name'] ?? ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <input type="text" name="last_name" placeholder="Last Name" required 
                               value="<?php echo $_POST['last_name'] ?? ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <input type="text" name="username" placeholder="Username" required 
                           value="<?php echo $_POST['username'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email Address" required 
                           value="<?php echo $_POST['email'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <input type="tel" name="phone" placeholder="Phone Number" 
                           value="<?php echo $_POST['phone'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <select name="user_type" required>
                        <option value="">Select User Type</option>
                        <option value="tenant" <?php echo ($_POST['user_type'] ?? '') == 'tenant' ? 'selected' : ''; ?>>Tenant</option>
                        <option value="landlord" <?php echo ($_POST['user_type'] ?? '') == 'landlord' ? 'selected' : ''; ?>>Landlord</option>
                        <option value="buyer" <?php echo ($_POST['user_type'] ?? '') == 'buyer' ? 'selected' : ''; ?>>Buyer</option>
                        <option value="seller" <?php echo ($_POST['user_type'] ?? '') == 'seller' ? 'selected' : ''; ?>>Seller</option>
                        <option value="property_manager" <?php echo ($_POST['user_type'] ?? '') == 'property_manager' ? 'selected' : ''; ?>>Property Manager</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                
                <div class="form-group">
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                </div>
                
                <button type="submit" class="btn-primary">Create Account</button>
            </form>
            
            <div class="auth-links">
                <p>Already have an account? <a href="<?php echo view_url('login.php'); ?>">Sign in here</a></p>
            </div>
        </div>
    </div>
</body>
</html>