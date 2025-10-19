<?php
require_once '../../config.php';
require_auth();
require_role([USER_ADMIN]);

require_once '../../models/User.php';
$userModel = new User();

if (!isset($_GET['id'])) {
    redirect('views/admin/users.php');
}

$user = $userModel->getById($_GET['id']);
if (!$user) {
    $_SESSION['error'] = 'User not found.';
    redirect('views/admin/users.php');
}

$page_title = "Edit User";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="<?php echo asset_url('css/style.css'); ?>">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1>Edit User</h1>
            <p>Update user information and permissions</p>
        </div>

        <div class="admin-content">
            <div class="user-edit-form">
                <form action="<?php echo BASE_URL; ?>controllers/AdminController.php" method="POST">
                    <input type="hidden" name="action" value="update_user_role">
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                    
                    <div class="form-section">
                        <h3>Basic Information</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" value="<?php echo htmlspecialchars($user['first_name']); ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" value="<?php echo htmlspecialchars($user['last_name']); ?>" disabled>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>User Role & Permissions</h3>
                        
                        <div class="form-group">
                            <label>User Type</label>
                            <select name="user_type" required>
                                <option value="tenant" <?php echo $user['user_type'] == 'tenant' ? 'selected' : ''; ?>>Tenant</option>
                                <option value="landlord" <?php echo $user['user_type'] == 'landlord' ? 'selected' : ''; ?>>Landlord</option>
                                <option value="property_manager" <?php echo $user['user_type'] == 'property_manager' ? 'selected' : ''; ?>>Property Manager</option>
                                <option value="buyer" <?php echo $user['user_type'] == 'buyer' ? 'selected' : ''; ?>>Buyer</option>
                                <option value="seller" <?php echo $user['user_type'] == 'seller' ? 'selected' : ''; ?>>Seller</option>
                                <option value="admin" <?php echo $user['user_type'] == 'admin' ? 'selected' : ''; ?>>Administrator</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Update User</button>
                        <a href="<?php echo view_url('admin/users.php'); ?>" class="btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
    .user-edit-form {
        background: #fff;
        padding: 2rem;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        max-width: 600px;
        margin: 0 auto;
    }

    .form-section {
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #eee;
    }

    .form-section:last-child {
        border-bottom: none;
    }

    .form-section h3 {
        margin-bottom: 1.5rem;
        color: #2c3e50;
        border-bottom: 2px solid #3498db;
        padding-bottom: 0.5rem;
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        margin-top: 2rem;
    }
    </style>

    <?php include '../includes/footer.php'; ?>
</body>
</html>