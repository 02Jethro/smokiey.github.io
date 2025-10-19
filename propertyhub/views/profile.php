<?php
require_once '../config.php';
require_auth();

require_once '../models/User.php';
$userModel = new User();
$user = $userModel->getById($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - PropertyHub</title>
    <link rel="stylesheet" href="<?php echo asset_url('css/style.css'); ?>">
    <style>
    .profile-container {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 2rem;
        margin-top: 2rem;
    }

    .profile-sidebar {
        background: #fff;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        height: fit-content;
        position: sticky;
        top: 2rem;
    }

    .profile-avatar {
        text-align: center;
        padding-bottom: 2rem;
        border-bottom: 2px solid #f8f9fa;
        margin-bottom: 2rem;
    }

    .avatar-placeholder {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #3498db, #2980b9);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        color: white;
        font-size: 1.5rem;
        font-weight: bold;
    }

    .profile-avatar h3 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
    }

    .user-role {
        color: #7f8c8d;
        margin: 0;
        font-size: 0.9rem;
        text-transform: capitalize;
    }

    .profile-nav {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .nav-item {
        padding: 1rem 1.5rem;
        text-decoration: none;
        color: #555;
        border-radius: 10px;
        transition: all 0.3s ease;
        font-weight: 500;
        border: 2px solid transparent;
    }

    .nav-item:hover {
        background: #f8f9fa;
        color: #3498db;
    }

    .nav-item.active {
        background: #3498db;
        color: white;
        border-color: #3498db;
    }

    .profile-content {
        background: #fff;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .profile-section {
        display: none;
    }

    .profile-section.active {
        display: block;
        animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .profile-section h2 {
        color: #2c3e50;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f8f9fa;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #2c3e50;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }

    .form-group input:disabled {
        background: #f8f9fa;
        color: #6c757d;
        cursor: not-allowed;
    }

    .form-group small {
        display: block;
        margin-top: 0.5rem;
        color: #6c757d;
        font-size: 0.875rem;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        font-weight: normal;
        margin-bottom: 0;
    }

    .checkbox-label input[type="checkbox"] {
        width: auto;
        margin: 0;
    }

    .btn-primary {
        background: #3498db;
        color: white;
        padding: 0.75rem 2rem;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: #2980b9;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
    }

    /* Success/Error Messages */
    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 2rem;
        border-left: 4px solid;
    }

    .alert-success {
        background: #d4edda;
        border-color: #28a745;
        color: #155724;
    }

    .alert-error {
        background: #f8d7da;
        border-color: #dc3545;
        color: #721c24;
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .profile-container {
            grid-template-columns: 250px 1fr;
            gap: 1.5rem;
        }
        
        .profile-sidebar,
        .profile-content {
            padding: 1.5rem;
        }
    }

    @media (max-width: 768px) {
        .profile-container {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .profile-sidebar {
            position: static;
            order: 2;
        }
        
        .profile-content {
            order: 1;
        }
        
        .profile-nav {
            flex-direction: row;
            overflow-x: auto;
            padding-bottom: 0.5rem;
        }
        
        .nav-item {
            white-space: nowrap;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
        }
        
        .form-row {
            grid-template-columns: 1fr;
            gap: 0;
        }
        
        .avatar-placeholder {
            width: 60px;
            height: 60px;
            font-size: 1.2rem;
        }
        
        .profile-avatar h3 {
            font-size: 1.2rem;
        }
    }

    @media (max-width: 480px) {
        .container {
            padding: 0 1rem;
        }
        
        .profile-sidebar,
        .profile-content {
            padding: 1rem;
            border-radius: 10px;
        }
        
        .page-header h1 {
            font-size: 1.5rem;
        }
        
        .profile-section h2 {
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
        }
        
        .btn-primary {
            width: 100%;
            padding: 1rem;
        }
        
        .form-group input,
        .form-group select {
            padding: 0.875rem;
        }
    }

    /* Dark mode support */
    @media (prefers-color-scheme: dark) {
        .profile-sidebar,
        .profile-content {
            background: #2c3e50;
            color: #ecf0f1;
        }
        
        .profile-avatar h3 {
            color: #ecf0f1;
        }
        
        .user-role {
            color: #bdc3c7;
        }
        
        .nav-item {
            color: #bdc3c7;
        }
        
        .nav-item:hover {
            background: #34495e;
            color: #3498db;
        }
        
        .nav-item.active {
            background: #3498db;
            color: white;
        }
        
        .profile-section h2 {
            color: #ecf0f1;
            border-bottom-color: #34495e;
        }
        
        .form-group label {
            color: #ecf0f1;
        }
        
        .form-group input,
        .form-group select {
            background: #34495e;
            border-color: #4a5f7a;
            color: #ecf0f1;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        .form-group input:disabled {
            background: #2c3e50;
            color: #7f8c8d;
        }
        
        .form-group small {
            color: #bdc3c7;
        }
    }

    /* Loading states */
    .loading {
        opacity: 0.6;
        pointer-events: none;
    }

    .spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-right: 0.5rem;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Form validation styles */
    .form-group.error input {
        border-color: #e74c3c;
        box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
    }

    .error-message {
        color: #e74c3c;
        font-size: 0.875rem;
        margin-top: 0.5rem;
        display: block;
    }

    /* Success state */
    .form-group.success input {
        border-color: #27ae60;
        box-shadow: 0 0 0 3px rgba(39, 174, 96, 0.1);
    }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1>My Profile</h1>
            <p>Manage your account information and preferences</p>
        </div>

        <!-- Flash Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="profile-container">
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <div class="avatar-placeholder">
                        <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                    </div>
                    <h3><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                    <p class="user-role"><?php echo ucfirst(str_replace('_', ' ', $user['user_type'])); ?></p>
                </div>
                
                <nav class="profile-nav">
                    <a href="#personal-info" class="nav-item active">Personal Information</a>
                    <a href="#change-password" class="nav-item">Change Password</a>
                    <a href="#preferences" class="nav-item">Preferences</a>
                </nav>
            </div>

            <div class="profile-content">
                <!-- Personal Information -->
                <div id="personal-info" class="profile-section active">
                    <h2>Personal Information</h2>
                    <form id="profileForm" action="<?php echo BASE_URL; ?>controllers/UserController.php" method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            <small>Email cannot be changed</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>User Type</label>
                            <input type="text" value="<?php echo ucfirst(str_replace('_', ' ', $user['user_type'])); ?>" disabled>
                        </div>
                        
                        <button type="submit" class="btn-primary" id="profileSubmit">
                            Update Profile
                        </button>
                    </form>
                </div>

                <!-- Change Password -->
                <div id="change-password" class="profile-section">
                    <h2>Change Password</h2>
                    <form id="passwordForm" action="<?php echo BASE_URL; ?>controllers/UserController.php" method="POST">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" class="btn-primary" id="passwordSubmit">
                            Change Password
                        </button>
                    </form>
                </div>

                <!-- Preferences -->
                <div id="preferences" class="profile-section">
                    <h2>Preferences</h2>
                    <form id="preferencesForm">
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="email_notifications" checked>
                                <span>Email Notifications</span>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="sms_notifications">
                                <span>SMS Notifications</span>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label>Preferred Language</label>
                            <select name="language">
                                <option value="en">English</option>
                                <option value="es">Spanish</option>
                                <option value="fr">French</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Time Zone</label>
                            <select name="timezone">
                                <option value="UTC-5">Eastern Time (ET)</option>
                                <option value="UTC-6">Central Time (CT)</option>
                                <option value="UTC-7">Mountain Time (MT)</option>
                                <option value="UTC-8">Pacific Time (PT)</option>
                            </select>
                        </div>
                        
                        <button type="button" class="btn-primary" id="preferencesSubmit">
                            Save Preferences
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Tab navigation
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all
            document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
            document.querySelectorAll('.profile-section').forEach(section => section.classList.remove('active'));
            
            // Add active class to clicked
            this.classList.add('active');
            const target = this.getAttribute('href').substring(1);
            document.getElementById(target).classList.add('active');
        });
    });

    // Form submission handling
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('profileSubmit');
        submitBtn.innerHTML = '<span class="spinner"></span> Updating...';
        submitBtn.disabled = true;
    });

    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('passwordSubmit');
        submitBtn.innerHTML = '<span class="spinner"></span> Changing...';
        submitBtn.disabled = true;
    });

    document.getElementById('preferencesSubmit').addEventListener('click', function() {
        const submitBtn = this;
        submitBtn.innerHTML = '<span class="spinner"></span> Saving...';
        submitBtn.disabled = true;
        
        // Simulate API call
        setTimeout(() => {
            submitBtn.innerHTML = 'Save Preferences';
            submitBtn.disabled = false;
            alert('Preferences saved successfully!');
        }, 1000);
    });

    // Form validation
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            let valid = true;
            const inputs = this.querySelectorAll('input[required]');
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    valid = false;
                    input.classList.add('error');
                } else {
                    input.classList.remove('error');
                }
            });
            
            if (!valid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });

    // Input validation styling
    document.querySelectorAll('input[required]').forEach(input => {
        input.addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('error');
                this.classList.add('success');
            } else {
                this.classList.remove('success');
            }
        });
    });

    // Mobile menu for profile navigation (for very small screens)
    function initMobileMenu() {
        if (window.innerWidth <= 480) {
            const profileNav = document.querySelector('.profile-nav');
            if (!profileNav.querySelector('.mobile-toggle')) {
                const toggle = document.createElement('button');
                toggle.className = 'mobile-toggle';
                toggle.innerHTML = '☰ Menu';
                toggle.style.cssText = `
                    width: 100%;
                    padding: 1rem;
                    background: #3498db;
                    color: white;
                    border: none;
                    border-radius: 8px;
                    margin-bottom: 1rem;
                    font-size: 1rem;
                    cursor: pointer;
                `;
                
                profileNav.parentNode.insertBefore(toggle, profileNav);
                profileNav.style.display = 'none';
                
                toggle.addEventListener('click', function() {
                    const isVisible = profileNav.style.display === 'flex';
                    profileNav.style.display = isVisible ? 'none' : 'flex';
                    toggle.innerHTML = isVisible ? '☰ Menu' : '✕ Close';
                });
            }
        } else {
            const toggle = document.querySelector('.mobile-toggle');
            const profileNav = document.querySelector('.profile-nav');
            if (toggle) {
                toggle.remove();
                profileNav.style.display = 'flex';
            }
        }
    }

    // Initialize mobile menu
    initMobileMenu();
    window.addEventListener('resize', initMobileMenu);
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>