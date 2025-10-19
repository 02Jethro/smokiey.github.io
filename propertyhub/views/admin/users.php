<?php
require_once '../../config.php';
require_auth();
require_role([USER_ADMIN]);

require_once '../../models/User.php';
$userModel = new User();
$users = $userModel->getAll();

$page_title = "Manage Users";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="<?php echo asset_url('css/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    .users-management {
        background: #fff;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
    }

    .management-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f8f9fa;
    }

    .header-actions {
        display: flex;
        gap: 1rem;
    }

    .stats-overview {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #667eea 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .stat-card h3 {
        font-size: 2rem;
        margin: 0 0 0.5rem 0;
        font-weight: bold;
    }

    .stat-card p {
        margin: 0;
        opacity: 0.9;
        font-size: 0.9rem;
    }

    .table-container {
        background: #f8f9fa;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .users-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
    }

    .users-table th,
    .users-table td {
        padding: 1.25rem 1rem;
        text-align: left;
        border-bottom: 1px solid #e9ecef;
    }

    .users-table th {
        background: #2c3e50;
        color: white;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .users-table tbody tr {
        transition: all 0.3s ease;
    }

    .users-table tbody tr:hover {
        background: #f8f9fa;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .user-type-badge {
        padding: 0.4rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .type-admin { background: #e74c3c; color: white; }
    .type-property_manager { background: #3498db; color: white; }
    .type-landlord { background: #27ae60; color: white; }
    .type-tenant { background: #f39c12; color: white; }
    .type-buyer { background: #9b59b6; color: white; }
    .type-seller { background: #34495e; color: white; }

    .status-badge {
        padding: 0.4rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: bold;
        text-transform: uppercase;
    }

    .status-active { background: #27ae60; color: white; }
    .status-inactive { background: #95a5a6; color: white; }
    .status-suspended { background: #e74c3c; color: white; }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .btn-action {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-edit {
        background: #3498db;
        color: white;
    }

    .btn-edit:hover {
        background: #2980b9;
        transform: translateY(-1px);
    }

    .btn-delete {
        background: #e74c3c;
        color: white;
    }

    .btn-delete:hover {
        background: #c0392b;
        transform: translateY(-1px);
    }

    .btn-password {
        background: #f39c12;
        color: white;
    }

    .btn-password:hover {
        background: #e67e22;
        transform: translateY(-1px);
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        backdrop-filter: blur(5px);
    }

    .modal-content {
        background-color: #fff;
        margin: 5% auto;
        padding: 0;
        border-radius: 15px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-header {
        padding: 1.5rem 2rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px 15px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 1.3rem;
    }

    .close {
        color: white;
        font-size: 1.5rem;
        font-weight: bold;
        cursor: pointer;
        background: none;
        border: none;
    }

    .close:hover {
        opacity: 0.7;
    }

    .modal-body {
        padding: 2rem;
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

    .form-group input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }

    .form-group input:focus {
        outline: none;
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }

    .password-strength {
        margin-top: 0.5rem;
        font-size: 0.8rem;
    }

    .strength-weak { color: #e74c3c; }
    .strength-medium { color: #f39c12; }
    .strength-strong { color: #27ae60; }

    .modal-footer {
        padding: 1.5rem 2rem;
        background: #f8f9fa;
        border-radius: 0 0 15px 15px;
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .users-table {
            font-size: 0.9rem;
        }
        
        .users-table th,
        .users-table td {
            padding: 1rem 0.75rem;
        }
    }

    @media (max-width: 768px) {
        .users-management {
            padding: 1rem;
        }
        
        .management-header {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
        }
        
        .header-actions {
            width: 100%;
            justify-content: space-between;
        }
        
        .stats-overview {
            grid-template-columns: 1fr;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .users-table {
            min-width: 800px;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .btn-action {
            justify-content: center;
        }
        
        .modal-content {
            margin: 10% auto;
            width: 95%;
        }
    }

    @media (max-width: 480px) {
        .management-header h1 {
            font-size: 1.5rem;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .modal-footer {
            padding: 1rem 1.5rem;
            flex-direction: column;
        }
    }

    /* Search and Filter */
    .search-filter-bar {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }

    .search-box {
        flex: 1;
        min-width: 250px;
    }

    .search-box input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 1rem;
    }

    .filter-select {
        padding: 0.75rem 1rem;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        background: white;
        font-size: 1rem;
        min-width: 150px;
    }

    .no-users {
        text-align: center;
        padding: 3rem;
        color: #666;
    }

    .no-users i {
        font-size: 4rem;
        margin-bottom: 1rem;
        color: #bdc3c7;
    }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1>Manage Users</h1>
            <p>View and manage all system users</p>
        </div>

        <div class="admin-content">
            <div class="users-management">
                <!-- Stats Overview -->
                <div class="stats-overview">
                    <?php
                    $userTypes = ['tenant', 'landlord', 'property_manager', 'admin', 'buyer', 'seller'];
                    foreach ($userTypes as $type):
                        $count = $userModel->countByType($type);
                    ?>
                    <div class="stat-card">
                        <h3><?php echo $count; ?></h3>
                        <p><?php echo ucfirst(str_replace('_', ' ', $type)); ?>s</p>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="management-header">
                    <h2>All Users (<?php echo count($users); ?>)</h2>
                    <div class="header-actions">
                        <div class="search-box">
                            <input type="text" id="userSearch" placeholder="Search users...">
                        </div>
                        <select class="filter-select" id="typeFilter">
                            <option value="">All Types</option>
                            <option value="admin">Admin</option>
                            <option value="property_manager">Property Manager</option>
                            <option value="landlord">Landlord</option>
                            <option value="tenant">Tenant</option>
                            <option value="buyer">Buyer</option>
                            <option value="seller">Seller</option>
                        </select>
                    </div>
                </div>

                <div class="table-container">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Username</th>
                                <th>Type</th>
                                <th>Phone</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="8" class="no-users">
                                        <i class="fas fa-users"></i>
                                        <h3>No Users Found</h3>
                                        <p>There are no users in the system yet.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                <tr data-type="<?php echo $user['user_type']; ?>" data-name="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>">
                                    <td><?php echo $user['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td>
                                        <span class="user-type-badge type-<?php echo $user['user_type']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $user['user_type'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="<?php echo view_url('admin/user_edit.php?id=' . $user['id']); ?>" class="btn-action btn-edit">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button class="btn-action btn-password" onclick="openPasswordModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>')">
                                                <i class="fas fa-key"></i> Password
                                            </button>
                                            <form action="<?php echo BASE_URL; ?>controllers/AdminController.php" method="POST" class="inline-form">
                                                <input type="hidden" name="action" value="delete_user">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete <?php echo htmlspecialchars($user['first_name']); ?>? This action cannot be undone.')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Password Change Modal -->
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Change User Password</h3>
                <button class="close">&times;</button>
            </div>
            <form id="passwordForm" action="<?php echo BASE_URL; ?>controllers/AdminController.php" method="POST">
                <input type="hidden" name="action" value="change_password">
                <input type="hidden" name="user_id" id="modalUserId">
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="userName">User</label>
                        <input type="text" id="userName" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="newPassword">New Password</label>
                        <input type="password" name="new_password" id="newPassword" required 
                               placeholder="Enter new password" minlength="8">
                        <div class="password-strength" id="passwordStrength"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <input type="password" name="confirm_password" id="confirmPassword" required 
                               placeholder="Confirm new password">
                        <div id="passwordMatch" style="font-size: 0.8rem; margin-top: 0.5rem;"></div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closePasswordModal()">Cancel</button>
                    <button type="submit" class="btn-primary" id="submitPassword">Change Password</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Modal functionality
    const modal = document.getElementById('passwordModal');
    const closeBtn = document.querySelector('.close');
    const passwordForm = document.getElementById('passwordForm');
    const newPassword = document.getElementById('newPassword');
    const confirmPassword = document.getElementById('confirmPassword');

    function openPasswordModal(userId, userName) {
        document.getElementById('modalUserId').value = userId;
        document.getElementById('userName').value = userName;
        modal.style.display = 'block';
    }

    function closePasswordModal() {
        modal.style.display = 'none';
        passwordForm.reset();
        document.getElementById('passwordStrength').textContent = '';
        document.getElementById('passwordMatch').textContent = '';
    }

    closeBtn.onclick = closePasswordModal;

    window.onclick = function(event) {
        if (event.target == modal) {
            closePasswordModal();
        }
    }

    // Password strength checker
    newPassword.addEventListener('input', function() {
        const password = this.value;
        const strength = checkPasswordStrength(password);
        const strengthElement = document.getElementById('passwordStrength');
        
        strengthElement.textContent = strength.message;
        strengthElement.className = 'password-strength ' + strength.class;
    });

    // Password confirmation check
    confirmPassword.addEventListener('input', function() {
        const matchElement = document.getElementById('passwordMatch');
        if (this.value === newPassword.value) {
            matchElement.textContent = '✓ Passwords match';
            matchElement.style.color = '#27ae60';
        } else {
            matchElement.textContent = '✗ Passwords do not match';
            matchElement.style.color = '#e74c3c';
        }
    });

    function checkPasswordStrength(password) {
        let strength = 0;
        let messages = [];

        if (password.length >= 8) strength++;
        else messages.push('at least 8 characters');

        if (password.match(/[a-z]+/)) strength++;
        else messages.push('one lowercase letter');

        if (password.match(/[A-Z]+/)) strength++;
        else messages.push('one uppercase letter');

        if (password.match(/[0-9]+/)) strength++;
        else messages.push('one number');

        if (password.match(/[!@#$%^&*(),.?":{}|<>]/)) strength++;
        else messages.push('one special character');

        if (strength === 5) {
            return { message: '✓ Strong password', class: 'strength-strong' };
        } else if (strength >= 3) {
            return { message: '✓ Medium password', class: 'strength-medium' };
        } else {
            return { message: 'Weak password - needs: ' + messages.join(', '), class: 'strength-weak' };
        }
    }

    // Search and filter functionality
    document.getElementById('userSearch').addEventListener('input', function() {
        filterUsers();
    });

    document.getElementById('typeFilter').addEventListener('change', function() {
        filterUsers();
    });

    function filterUsers() {
        const searchTerm = document.getElementById('userSearch').value.toLowerCase();
        const typeFilter = document.getElementById('typeFilter').value;
        const rows = document.querySelectorAll('#usersTableBody tr');

        rows.forEach(row => {
            const name = row.dataset.name.toLowerCase();
            const type = row.dataset.type;
            const email = row.cells[2].textContent.toLowerCase();
            const username = row.cells[3].textContent.toLowerCase();

            const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm) || username.includes(searchTerm);
            const matchesType = !typeFilter || type === typeFilter;

            row.style.display = matchesSearch && matchesType ? '' : 'none';
        });
    }

    // Form validation
    passwordForm.addEventListener('submit', function(e) {
        if (newPassword.value !== confirmPassword.value) {
            e.preventDefault();
            alert('Passwords do not match!');
            return;
        }

        if (newPassword.value.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long!');
            return;
        }

        const strength = checkPasswordStrength(newPassword.value);
        if (strength.class === 'strength-weak') {
            e.preventDefault();
            alert('Please choose a stronger password!');
            return;
        }
    });
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>