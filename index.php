<?php
require_once 'bootstrap.php';

// Simple routing
$request = $_SERVER['REQUEST_URI'];
$base_path = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
$route = str_replace($base_path, '', $request);
$route = explode('?', $route)[0]; // Remove query string

// Route definitions
$routes = [
    '' => 'home.php',
    'login' => 'views/auth/login.php',
    'register' => 'views/auth/register.php',
    'dashboard' => 'views/users/dashboard.php',
    'properties' => 'views/properties/list.php',
    'messages' => 'views/messages/inbox.php',
];

// Handle the route
if ($route === '' || $route === '/') {
    // Home page
    $page_title = "Home - Find Your Perfect Property";
    include_header($page_title);

    require_model('Property');
    $propertyModel = new Property();
    $recentProperties = $propertyModel->getAllProperties([], 6);
    ?>
    <div class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1>Find Your Perfect Property</h1>
                <p>Discover thousands of properties for rent and sale across the country</p>
                
                <div class="search-box">
                    <form action="<?php echo url('views/properties/list.php'); ?>" method="GET">
                        <div class="search-filters">
                            <input type="text" name="search" placeholder="Enter city, address, or ZIP code">
                            <select name="type">
                                <option value="">All Types</option>
                                <option value="apartment">Apartment</option>
                                <option value="house">House</option>
                                <option value="condo">Condo</option>
                                <option value="commercial">Commercial</option>
                            </select>
                            <select name="price_range">
                                <option value="">Any Price</option>
                                <option value="0-100000">Under $100,000</option>
                                <option value="100000-300000">$100,000 - $300,000</option>
                                <option value="300000-500000">$300,000 - $500,000</option>
                                <option value="500000-1000000">$500,000 - $1,000,000</option>
                                <option value="1000000-0">Over $1,000,000</option>
                            </select>
                            <button type="submit" class="btn-primary">Search</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

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

    <div class="recent-properties">
        <div class="container">
            <h2>Recent Properties</h2>
            <div class="properties-grid">
                <?php if (empty($recentProperties)): ?>
                    <div class="no-properties">
                        <p>No properties available at the moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentProperties as $property): ?>
                    <div class="property-card">
                        <img src="<?php echo $property['primary_image'] ?? asset_url('images/default-property.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($property['title']); ?>" class="property-image">
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
                <a href="<?php echo url('properties'); ?>" class="btn-primary">View All Properties</a>
            </div>
        </div>
    </div>
    <?php
    include_footer();
} else {
    // For other routes, you can implement proper routing
    // For now, let's handle the common case
    http_response_code(404);
    echo "Page not found";
}
?>