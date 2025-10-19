<?php
// Simple index with manual includes for debugging
define('ROOT_DIR', dirname(__FILE__));

// Manually include required files
require_once ROOT_DIR . '/core/Database.php';
require_once ROOT_DIR . '/models/Property.php';
require_once ROOT_DIR . '/models/User.php';
require_once ROOT_DIR . '/core/Auth.php';
require_once ROOT_DIR . '/core/Validator.php';

// Initialize database
try {
    $db = Database::getInstance();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Application Configuration
define('BASE_URL', 'http://localhost/propertyhub/');

// Helper functions
function asset_url($path) {
    return BASE_URL . 'assets/' . ltrim($path, '/');
}

function view_url($path) {
    return BASE_URL . 'views/' . ltrim($path, '/');
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get properties
$propertyModel = new Property();
$properties = $propertyModel->getAll([], 6);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PropertyHub - Find Your Perfect Property</title>
    <link rel="stylesheet" href="<?php echo asset_url('css/style.css'); ?>">
</head>
<body>
    <header class="header">
        <nav class="navbar container">
            <div class="logo">
                <a href="<?php echo BASE_URL; ?>">
                    <h2>PropertyHub</h2>
                </a>
            </div>
            
            <ul class="nav-links">
                <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
                <li><a href="<?php echo view_url('properties/list.php'); ?>">Properties</a></li>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="<?php echo view_url('dashboard.php'); ?>">Dashboard</a></li>
                    <li class="user-menu">
                        <span>Welcome, <?php echo $_SESSION['first_name']; ?></span>
                        <div class="dropdown">
                            <a href="<?php echo view_url('profile.php'); ?>">Profile</a>
                            <form action="<?php echo BASE_URL; ?>controllers/AuthController.php" method="POST">
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
        <!-- Hero Section -->
        <div class="hero-section">
            <div class="container">
                <div class="hero-content">
                    <h1>Find Your Perfect Property</h1>
                    <p>Discover thousands of properties for rent and sale across the country</p>
                    
                    <div class="search-box">
                        <form action="<?php echo view_url('properties/list.php'); ?>" method="GET">
                            <div class="search-filters">
                                <input type="text" name="search" placeholder="Enter city, address, or ZIP code">
                                <select name="type">
                                    <option value="">All Types</option>
                                    <option value="apartment">Apartment</option>
                                    <option value="house">House</option>
                                    <option value="condo">Condo</option>
                                    <option value="commercial">Commercial</option>
                                </select>
                                <button type="submit" class="btn-primary">Search</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="features-section">
            <div class="container">
                <h2>Why Choose PropertyHub?</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <h3>Easy Search</h3>
                        <p>Find properties with advanced filters and detailed search options</p>
                    </div>
                    <div class="feature-card">
                        <h3>Secure Payments</h3>
                        <p>Safe and secure payment processing for all transactions</p>
                    </div>
                    <div class="feature-card">
                        <h3>Property Management</h3>
                        <p>Comprehensive tools for landlords and property managers</p>
                    </div>
                    <div class="feature-card">
                        <h3>24/7 Support</h3>
                        <p>Round-the-clock customer support for all your needs</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Properties -->
        <div class="recent-properties">
            <div class="container">
                <h2>Recent Properties</h2>
                <div class="properties-grid">
                    <?php if (empty($properties)): ?>
                        <div class="no-properties">
                            <p>No properties available at the moment.</p>
                            <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_type'] === 'landlord' || $_SESSION['user_type'] === 'property_manager')): ?>
                                <p><a href="<?php echo view_url('properties/add.php'); ?>" class="btn-primary">Add the first property</a></p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($properties as $property): ?>
                        <div class="property-card">
                            <div class="property-image">
                                <img src="<?php echo asset_url('images/default-property.jpg'); ?>" alt="<?php echo htmlspecialchars($property['title']); ?>">
                            </div>
                            <div class="property-info">
                                <h3><?php echo htmlspecialchars($property['title']); ?></h3>
                                <p class="property-address"><?php echo htmlspecialchars($property['address']); ?></p>
                                <p class="property-price">$<?php echo number_format($property['price']); ?></p>
                                <div class="property-features">
                                    <span class="feature"><?php echo $property['bedrooms']; ?> Beds</span>
                                    <span class="feature"><?php echo $property['bathrooms']; ?> Baths</span>
                                    <span class="feature"><?php echo number_format($property['area_sqft']); ?> Sq Ft</span>
                                </div>
                                <a href="<?php echo view_url('properties/view.php?id=' . $property['id']); ?>" class="btn-secondary">View Details</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="text-center">
                    <a href="<?php echo view_url('properties/list.php'); ?>" class="btn-primary">View All Properties</a>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>PropertyHub</h3>
                    <p>Your trusted partner in real estate management and property transactions.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
                        <li><a href="<?php echo view_url('properties/list.php'); ?>">Properties</a></li>
                        <li><a href="<?php echo view_url('login.php'); ?>">Login</a></li>
                        <li><a href="<?php echo view_url('register.php'); ?>">Register</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> PropertyHub. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="<?php echo asset_url('js/main.js'); ?>"></script>
</body>
</html>