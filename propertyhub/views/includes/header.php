<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>PropertyHub</title>
    <link rel="stylesheet" href="<?php echo asset_url('css/style.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/responsive.css'); ?>">
</head>
<body>
    <header class="header">
        <nav class="navbar container">
            <div class="logo">
                <a href="<?php echo BASE_URL; ?>">
                    <h2>PropertyHub</h2>
                </a>
            </div>
            
            <button class="mobile-menu-btn" id="mobileMenuBtn">☰</button>
            
            <ul class="nav-links" id="navLinks">
                <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
                <li><a href="<?php echo view_url('properties/list.php'); ?>">Properties</a></li>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="<?php echo view_url('dashboard.php'); ?>">Dashboard</a></li>
                    <li><a href="<?php echo view_url('messages/inbox.php'); ?>">Messages</a></li>
                    <li class="user-menu">
                        <span>Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?></span>
                        <div class="dropdown">
                            <a href="<?php echo view_url('profile.php'); ?>">Profile</a>
                            <form action="<?php echo BASE_URL; ?>controllers/AuthController.php" method="POST" class="logout-form">
                                <input type="hidden" name="action" value="logout">
                                <button type="submit" class="logout-btn">Logout</button>
                            </form>
                        </div>
                    </li>
                <?php else: ?>
                    <li><a href="<?php echo view_url('login.php'); ?>">Login</a></li>
                    <li><a href="<?php echo view_url('register.php'); ?>">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main class="main-content">
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

        <?php if (isset($_SESSION['errors']) && is_array($_SESSION['errors'])): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($_SESSION['errors'] as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; unset($_SESSION['errors']); ?>
                </ul>
            </div>
        <?php endif; ?>