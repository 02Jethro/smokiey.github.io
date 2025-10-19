<?php
// Define root directory first


// Load configuration FIRST
require_once 'config.php';

// Now manually include required files
require_once ROOT_DIR . '/propertyhub/core/Database.php';
require_once ROOT_DIR . '/propertyhub/models/Property.php';
require_once ROOT_DIR . '/propertyhub/models/User.php';
require_once ROOT_DIR . '/propertyhub/core/Auth.php';
require_once ROOT_DIR . '/propertyhub/core/Validator.php';

// Initialize database
try {
    $db = Database::getInstance();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get only 8 properties for homepage
$propertyModel = new Property();
$properties = $propertyModel->getAll([], 8); // Limit to 8 properties

// Get property counts by province from database
$provinceCounts = [];
$zimbabweProvinces = [
    'harare' => 'Harare',
    'bulawayo' => 'Bulawayo', 
    'manicaland' => 'Manicaland',
    'mashonaland_central' => 'Mashonaland Central',
    'mashonaland_east' => 'Mashonaland East',
    'mashonaland_west' => 'Mashonaland West',
    'masvingo' => 'Masvingo',
    'matabeleland_north' => 'Matabeleland North',
    'matabeleland_south' => 'Matabeleland South',
    'midlands' => 'Midlands'
];

foreach ($zimbabweProvinces as $key => $name) {
    $sql = "SELECT COUNT(*) as count FROM properties WHERE state = ?";
    $stmt = $db->query($sql, [$name]);
    $result = $stmt->fetch();
    $provinceCounts[$key] = [
        'name' => $name,
        'properties' => $result['count']
    ];
}

// Get total statistics
$totalProperties = $db->query("SELECT COUNT(*) as count FROM properties")->fetch()['count'];
$totalUsers = $db->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
$totalAgents = $db->query("SELECT COUNT(*) as count FROM users WHERE user_type IN ('property_manager', 'admin')")->fetch()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PropertyHub Zimbabwe - Find Your Perfect Property</title>
    <link rel="stylesheet" href="<?php echo asset_url('css/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    /* Keep all the existing CSS styles from your previous version */
    .map-section {
        background: #f8f9fa;
        padding: 4rem 0;
    }

    .map-container {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
        margin: 2rem 0;
    }

    .zimbabwe-map {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        text-align: center;
    }

    .zimbabwe-map svg {
        max-width: 100%;
        height: auto;
    }

    .province {
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .province:hover {
        filter: brightness(1.2);
        transform: scale(1.02);
    }

    .province.capital {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    .province-label {
        font-size: 10px;
        font-weight: bold;
        fill: #333;
        pointer-events: none;
    }

    .capital-label {
        font-size: 8px;
        fill: white;
        font-weight: bold;
    }

    .province-info {
        display: flex;
        align-items: flex-start;
    }

    .info-card {
        background: white;
        padding: 1.5rem;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        width: 100%;
    }

    .default-message {
        text-align: center;
        color: #666;
        padding: 2rem;
    }

    .default-message i {
        font-size: 3rem;
        color: #3498db;
        margin-bottom: 1rem;
    }

    .selected-province h4 {
        color: #2c3e50;
        margin-bottom: 1rem;
        border-bottom: 2px solid #3498db;
        padding-bottom: 0.5rem;
    }

    .province-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin: 1rem 0;
    }

    .province-stats .stat {
        text-align: center;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .province-stats .stat strong {
        display: block;
        font-size: 1.5rem;
        color: #3498db;
    }

    .province-stats .stat span {
        font-size: 0.8rem;
        color: #666;
    }

    .popular-areas {
        background: #e8f4fd;
        padding: 1rem;
        border-radius: 8px;
        margin: 1rem 0;
        border-left: 4px solid #3498db;
    }

    /* Provinces Grid */
    .provinces-grid {
        margin-top: 3rem;
    }

    .provinces-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin-top: 1.5rem;
    }

    .province-item {
        background: white;
        border-radius: 10px;
        padding: 1rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 1rem;
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .province-item:hover {
        transform: translateY(-5px);
    }

    .province-color {
        width: 20px;
        height: 20px;
        border-radius: 50%;
    }

    .province-content h4 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
    }

    .province-content p {
        margin: 0;
        color: #666;
        font-size: 0.9rem;
    }

    .btn-small {
        padding: 0.5rem 1rem;
        background: #3498db;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-size: 0.8rem;
        margin-top: 0.5rem;
        display: inline-block;
    }

    /* Hero Stats */
    .hero-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 2rem;
        margin-top: 3rem;
        text-align: center;
    }

    .hero-stats .stat h3 {
        font-size: 2.5rem;
        color: #3498db;
        margin: 0;
    }

    .hero-stats .stat p {
        margin: 0;
        color: white;
        font-weight: 500;
    }

    /* Enhanced Features Section */
    .features-section {
        padding: 4rem 0;
        background: white;
    }

    .section-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .section-header h2 {
        color: #2c3e50;
        margin-bottom: 1rem;
    }

    .section-header p {
        color: #666;
        font-size: 1.1rem;
    }

    .feature-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #3498db, #2980b9);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        color: white;
        font-size: 2rem;
    }

    .feature-card h3 {
        text-align: center;
        margin-bottom: 1rem;
        color: #2c3e50;
    }

    .feature-card p {
        text-align: center;
        color: #666;
        line-height: 1.6;
    }

    /* Enhanced Property Cards */
    .property-badge {
        position: absolute;
        top: 1rem;
        left: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .property-type {
        background: #3498db;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: bold;
    }

    .property-location {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #666;
        margin: 0.5rem 0;
    }

    .property-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1rem;
    }

    .btn-icon {
        background: none;
        border: 2px solid #e9ecef;
        color: #666;
        padding: 0.5rem;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-icon:hover {
        border-color: #e74c3c;
        color: #e74c3c;
    }

    /* Team Section */
    .team-section {
        background: #f8f9fa;
        padding: 4rem 0;
    }

    .team-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
    }

    .team-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }

    .team-card:hover {
        transform: translateY(-10px);
    }

    .team-image {
        position: relative;
        height: 250px;
        overflow: hidden;
    }

    .team-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .team-social {
        position: absolute;
        bottom: 1rem;
        left: 0;
        right: 0;
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .team-card:hover .team-social {
        opacity: 1;
    }

    .team-social a {
        width: 40px;
        height: 40px;
        background: #3498db;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: background 0.3s ease;
    }

    .team-social a:hover {
        background: #2980b9;
    }

    .team-info {
        padding: 1.5rem;
    }

    .team-info h3 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
    }

    .team-role {
        color: #3498db;
        font-weight: 600;
        margin: 0 0 0.5rem 0;
    }

    .team-location, .team-experience {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #666;
        margin: 0.25rem 0;
        font-size: 0.9rem;
    }

    .team-stats {
        display: flex;
        justify-content: space-between;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #eee;
    }

    .team-stats span {
        background: #f8f9fa;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.8rem;
        color: #666;
    }

    /* Testimonials Section */
    .testimonials-section {
        padding: 4rem 0;
        background: white;
    }

    .testimonials-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
    }

    .testimonial-card {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 2rem;
        position: relative;
    }

    .testimonial-content {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .testimonial-content i {
        position: absolute;
        top: -10px;
        left: -10px;
        font-size: 2rem;
        color: #3498db;
        opacity: 0.3;
    }

    .testimonial-content p {
        font-style: italic;
        color: #555;
        line-height: 1.6;
        margin: 0;
    }

    .testimonial-author {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .testimonial-author img {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
    }

    .author-info h4 {
        margin: 0 0 0.25rem 0;
        color: #2c3e50;
    }

    .author-info p {
        margin: 0 0 0.5rem 0;
        color: #666;
        font-size: 0.9rem;
    }

    .rating {
        color: #f39c12;
    }

    /* CTA Section */
    .cta-section {
        background: linear-gradient(135deg, #3498db, #2980b9);
        padding: 4rem 0;
        color: white;
        text-align: center;
    }

    .cta-content h2 {
        margin: 0 0 1rem 0;
        font-size: 2.5rem;
    }

    .cta-content p {
        font-size: 1.2rem;
        margin-bottom: 2rem;
        opacity: 0.9;
    }

    .cta-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-large {
        padding: 1rem 2rem;
        font-size: 1.1rem;
    }

    /* Enhanced Footer */
    .footer {
        background: #2c3e50;
        color: white;
        padding: 3rem 0 1rem;
    }

    .footer-content {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1.5fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .footer-section h3, .footer-section h4 {
        color: white;
        margin-bottom: 1rem;
    }

    .footer-section p {
        color: #bdc3c7;
        line-height: 1.6;
    }

    .footer-section ul {
        list-style: none;
        padding: 0;
    }

    .footer-section ul li {
        margin-bottom: 0.5rem;
    }

    .footer-section ul li a {
        color: #bdc3c7;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .footer-section ul li a:hover {
        color: #3498db;
    }

    .social-links {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
    }

    .social-links a {
        width: 40px;
        height: 40px;
        background: #34495e;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: background 0.3s ease;
    }

    .social-links a:hover {
        background: #3498db;
    }

    .contact-info p {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .footer-bottom {
        border-top: 1px solid #34495e;
        padding-top: 1rem;
        text-align: center;
        color: #bdc3c7;
    }

    /* Province Colors */
    :root {
        --province-harare: #E91E63;
        --province-bulawayo: #795548;
        --province-manicaland: #8BC34A;
        --province-mashonaland_central: #FFEB3B;
        --province-mashonaland_east: #00BCD4;
        --province-mashonaland_west: #F44336;
        --province-masvingo: #9C27B0;
        --province-matabeleland_north: #4CAF50;
        --province-matabeleland_south: #2196F3;
        --province-midlands: #FF9800;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .map-container {
            grid-template-columns: 1fr;
        }
        
        .hero-stats {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        
        .footer-content {
            grid-template-columns: 1fr;
        }
        
        .cta-buttons {
            flex-direction: column;
            align-items: center;
        }
        
        .team-grid,
        .testimonials-grid {
            grid-template-columns: 1fr;
        }
        
        .provinces-list {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .hero-stats {
            grid-template-columns: 1fr;
        }
        
        .search-filters {
            flex-direction: column;
        }
        
        .property-actions {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .btn-icon {
            align-self: flex-end;
        }
    }
    </style>
</head>
<body>
    <header class="header">
        <nav class="navbar container">
            <div class="logo">
                <a href="<?php echo BASE_URL; ?>">
                    <h2><i class="fas fa-home"></i> PropertyHub </h2>
                </a>
            </div>
            
            <ul class="nav-links">
                <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
                <li><a href="<?php echo view_url('properties/list.php'); ?>">Properties</a></li>
                <li><a href="#zimbabwe-map">Locations</a></li>
                <li><a href="#agents">Agents</a></li>
                <li><a href="#services">Services</a></li>
                
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
                    <h1>Find Your Perfect Property in Zimbabwe</h1>
                    <p>Discover thousands of properties for rent and sale across all 10 provinces</p>
                    
                    <div class="search-box">
                        <form action="<?php echo view_url('properties/list.php'); ?>" method="GET">
                            <div class="search-filters">
                                <input type="text" name="search" placeholder="Enter city, suburb, or area">
                                <select name="type">
                                    <option value="">All Types</option>
                                    <option value="apartment">Apartment</option>
                                    <option value="house">House</option>
                                    <option value="stand">Stand</option>
                                    <option value="commercial">Commercial</option>
                                    <option value="farm">Farm</option>
                                </select>
                                <select name="province">
                                    <option value="">All Provinces</option>
                                    <?php foreach ($provinceCounts as $key => $province): ?>
                                        <option value="<?php echo $key; ?>"><?php echo $province['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn-primary"><i class="fas fa-search"></i> Search</button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="hero-stats">
                        <div class="stat">
                            <h3><?php echo $totalProperties; ?>+</h3>
                            <p>Properties Listed</p>
                        </div>
                        <div class="stat">
                            <h3>10</h3>
                            <p>Provinces Covered</p>
                        </div>
                        <div class="stat">
                            <h3><?php echo $totalAgents; ?>+</h3>
                            <p>Trusted Agents</p>
                        </div>
                        <div class="stat">
                            <h3>95%</h3>
                            <p>Client Satisfaction</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Zimbabwe Map Section -->
        <section id="zimbabwe-map" class="map-section">
            <div class="container">
                <div class="section-header">
                    <h2>Explore Properties by Province</h2>
                    <p>Click on any province to view available properties in that area</p>
                </div>
                
                <div class="map-container">
                    <div class="zimbabwe-map">
                        <!-- Zimbabwe Map with clickable provinces -->
                        <svg viewBox="0 0 800 600" xmlns="http://www.w3.org/2000/svg">
                            <!-- Simplified Zimbabwe map with provinces -->
                            <g class="provinces">
                                <!-- Matabeleland North -->
                                <path id="matabeleland_north" class="province" d="M200,100 L300,150 L250,250 L150,200 Z" 
                                      data-province="matabeleland_north" data-name="Matabeleland North"
                                      fill="#4CAF50" stroke="#2E7D32" stroke-width="2"/>
                                
                                <!-- Matabeleland South -->
                                <path id="matabeleland_south" class="province" d="M250,250 L350,300 L300,400 L200,350 Z" 
                                      data-province="matabeleland_south" data-name="Matabeleland South"
                                      fill="#2196F3" stroke="#1565C0" stroke-width="2"/>
                                
                                <!-- Midlands -->
                                <path id="midlands" class="province" d="M350,300 L450,250 L500,350 L400,400 Z" 
                                      data-province="midlands" data-name="Midlands"
                                      fill="#FF9800" stroke="#EF6C00" stroke-width="2"/>
                                
                                <!-- Masvingo -->
                                <path id="masvingo" class="province" d="M500,350 L600,300 L550,450 L450,500 Z" 
                                      data-province="masvingo" data-name="Masvingo"
                                      fill="#9C27B0" stroke="#6A1B9A" stroke-width="2"/>
                                
                                <!-- Mashonaland West -->
                                <path id="mashonaland_west" class="province" d="M300,150 L400,100 L450,250 L350,300 Z" 
                                      data-province="mashonaland_west" data-name="Mashonaland West"
                                      fill="#F44336" stroke="#C62828" stroke-width="2"/>
                                
                                <!-- Mashonaland Central -->
                                <path id="mashonaland_central" class="province" d="M400,100 L500,50 L550,200 L450,250 Z" 
                                      data-province="mashonaland_central" data-name="Mashonaland Central"
                                      fill="#FFEB3B" stroke="#F9A825" stroke-width="2"/>
                                
                                <!-- Mashonaland East -->
                                <path id="mashonaland_east" class="province" d="M500,50 L600,100 L650,250 L550,200 Z" 
                                      data-province="mashonaland_east" data-name="Mashonaland East"
                                      fill="#00BCD4" stroke="#00838F" stroke-width="2"/>
                                
                                <!-- Harare -->
                                <circle id="harare" class="province capital" cx="450" cy="200" r="25" 
                                        data-province="harare" data-name="Harare"
                                        fill="#E91E63" stroke="#AD1457" stroke-width="2"/>
                                
                                <!-- Manicaland -->
                                <path id="manicaland" class="province" d="M600,100 L700,150 L650,300 L550,250 Z" 
                                      data-province="manicaland" data-name="Manicaland"
                                      fill="#8BC34A" stroke="#558B2F" stroke-width="2"/>
                                
                                <!-- Bulawayo -->
                                <circle id="bulawayo" class="province capital" cx="280" cy="280" r="20" 
                                        data-province="bulawayo" data-name="Bulawayo"
                                        fill="#795548" stroke="#4E342E" stroke-width="2"/>
                            </g>
                            
                            <!-- Province labels -->
                            <g class="province-labels">
                                <text x="220" y="140" class="province-label">Mat North</text>
                                <text x="280" y="320" class="province-label">Mat South</text>
                                <text x="420" y="340" class="province-label">Midlands</text>
                                <text x="520" y="400" class="province-label">Masvingo</text>
                                <text x="350" y="200" class="province-label">Mash West</text>
                                <text x="450" y="150" class="province-label">Mash Central</text>
                                <text x="550" y="120" class="province-label">Mash East</text>
                                <text x="440" y="190" class="province-label capital-label">Harare</text>
                                <text x="620" y="200" class="province-label">Manicaland</text>
                                <text x="260" y="270" class="province-label capital-label">Bulawayo</text>
                            </g>
                        </svg>
                    </div>
                    
                    <div class="province-info">
                        <div class="info-card">
                            <h3>Select a Province</h3>
                            <p>Click on any province on the map to view properties in that area</p>
                            <div class="province-details" id="provinceDetails">
                                <div class="default-message">
                                    <i class="fas fa-mouse-pointer"></i>
                                    <p>Click on a province to see available properties</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- In the provinces grid section -->
                <div class="provinces-grid">
                    <h3>Browse by Province</h3>
                    <div class="provinces-list">
                        <?php foreach ($provinceCounts as $key => $province): ?>
                        <div class="province-item" data-province="<?php echo $key; ?>">
                            <div class="province-color" style="background-color: var(--province-<?php echo $key; ?>)"></div>
                            <div class="province-content">
                                <h4><?php echo $province['name']; ?></h4>
                                <p><?php echo $province['properties']; ?> Properties</p>
                                <a href="<?php echo view_url('properties/list.php?province=' . $key); ?>" class="btn-small">View Properties</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features-section" id="services">
            <div class="container">
                <div class="section-header">
                    <h2>Why Choose PropertyHub Zimbabwe?</h2>
                    <p>Comprehensive real estate services across all provinces</p>
                </div>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-search-location"></i>
                        </div>
                        <h3>Province-Wide Search</h3>
                        <p>Find properties across all 10 provinces of Zimbabwe with detailed location-based filtering</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Secure Payments</h3>
                        <p>Safe and secure payment processing with EcoCash, ZIPIT, and bank transfers</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h3>Verified Agents</h3>
                        <p>Work with trusted, verified real estate agents across Zimbabwe</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <h3>Property Management</h3>
                        <p>Comprehensive tools for landlords and property managers in every province</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <h3>Legal Support</h3>
                        <p>Get assistance with property documentation and legal requirements</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <h3>24/7 Support</h3>
                        <p>Round-the-clock customer support for all your real estate needs</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Recent Properties -->
        <section class="recent-properties">
            <div class="container">
                <div class="section-header">
                    <h2>Featured Properties</h2>
                    <p>Discover our latest property listings across Zimbabwe</p>
                <div class="text-center">
                    <a href="<?php echo view_url('properties/list.php'); ?>" class="btn-primary">
                        <i class="fas fa-list"></i> View All Properties
                    </a>
                </div>
            </div>
        </section>

        <!-- Team Section -->
        <section id="agents" class="team-section">
            <div class="container">
                <div class="section-header">
                    <h2>Meet Our Trusted Agents</h2>
                    <p>Professional real estate agents serving all provinces of Zimbabwe</p>
                </div>
                <div class="team-grid">
                    <?php
                    // Get agents from database
                    $agents = $db->query("SELECT * FROM users WHERE user_type IN ('property_manager', 'admin') LIMIT 3")->fetchAll();
                    $agentImages = ['agent1.jpg', 'agent2.jpg', 'agent3.jpg'];
                    $agentIndex = 0;
                    
                    foreach ($agents as $agent): 
                    ?>
                    <div class="team-card">
                        <div class="team-image">
                            <img src="<?php echo asset_url('images/' . $agentImages[$agentIndex]); ?>" alt="<?php echo $agent['first_name'] . ' ' . $agent['last_name']; ?>">
                            <div class="team-social">
                                <a href="https://wa.me/<?php echo $agent['phone']; ?>"><i class="fab fa-whatsapp"></i></a>
                                <a href="#"><i class="fab fa-facebook"></i></a>
                                <a href="tel:<?php echo $agent['phone']; ?>"><i class="fas fa-phone"></i></a>
                            </div>
                        </div>
                        <div class="team-info">
                            <h3><?php echo $agent['first_name'] . ' ' . $agent['last_name']; ?></h3>
                            <p class="team-role">
                                <?php echo $agent['user_type'] === 'admin' ? 'Senior Property Consultant' : 'Property Consultant'; ?>
                            </p>
                            <p class="team-location"><i class="fas fa-map-marker-alt"></i> 
                                <?php 
                                $locations = ['Harare & Mashonaland', 'Bulawayo & Matabeleland', 'Manicaland & Masvingo'];
                                echo $locations[$agentIndex] ?? 'Nationwide';
                                ?>
                            </p>
                            <p class="team-experience">
                                <i class="fas fa-briefcase"></i>
                                <?php echo rand(3, 8); ?>+ years experience
                            </p>
                            <div class="team-stats">
                                <span><?php echo rand(20, 50); ?>+ Properties Sold</span>
                                <span><?php echo rand(90, 98); ?>% Client Rating</span>
                            </div>
                        </div>
                    </div>
                    <?php 
                    $agentIndex++;
                    endforeach; 
                    ?>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="testimonials-section">
            <div class="container">
                <div class="section-header">
                    <h2>What Our Clients Say</h2>
                    <p>Real stories from satisfied customers across Zimbabwe</p>
                </div>
                <div class="testimonials-grid">
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <i class="fas fa-quote-left"></i>
                            <p>"PropertyHub helped me find my dream home in Harare. The process was smooth and the agents were very professional. Highly recommended!"</p>
                        </div>
                        <div class="testimonial-author">
                            <img src="<?php echo asset_url('images/client1.jpg'); ?>" alt="Grace Ndlovu">
                            <div class="author-info">
                                <h4>Grace Ndlovu</h4>
                                <p>Home Owner, Harare</p>
                                <div class="rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <i class="fas fa-quote-left"></i>
                            <p>"As a landlord in Bulawayo, PropertyHub has made property management so much easier. The payment system is reliable and the support team is always available."</p>
                        </div>
                        <div class="testimonial-author">
                            <img src="<?php echo asset_url('images/client2.jpg'); ?>" alt="John Sibanda">
                            <div class="author-info">
                                <h4>John Sibanda</h4>
                                <p>Property Investor, Bulawayo</p>
                                <div class="rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <i class="fas fa-quote-left"></i>
                            <p>"Found a perfect commercial space in Mutare through PropertyHub. The platform connects you with genuine sellers and the verification process is thorough."</p>
                        </div>
                        <div class="testimonial-author">
                            <img src="<?php echo asset_url('images/client3.jpg'); ?>" alt="Lisa Changwa">
                            <div class="author-info">
                                <h4>Lisa Changwa</h4>
                                <p>Business Owner, Mutare</p>
                                <div class="rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="cta-section">
            <div class="container">
                <div class="cta-content">
                    <h2>Ready to Find Your Perfect Property?</h2>
                    <p>Join thousands of satisfied customers across Zimbabwe</p>
                    <div class="cta-buttons">
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <a href="<?php echo view_url('register.php'); ?>" class="btn-primary btn-large">
                                <i class="fas fa-user-plus"></i> Get Started Free
                            </a>
                            <a href="<?php echo view_url('properties/list.php'); ?>" class="btn-secondary btn-large">
                                <i class="fas fa-search"></i> Browse Properties
                            </a>
                        <?php else: ?>
                            <a href="<?php echo view_url('properties/add.php'); ?>" class="btn-primary btn-large">
                                <i class="fas fa-plus"></i> List Your Property
                            </a>
                            <a href="<?php echo view_url('properties/list.php'); ?>" class="btn-secondary btn-large">
                                <i class="fas fa-search"></i> Find More Properties
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><i class="fas fa-home"></i> PropertyHub Zimbabwe</h3>
                    <p>Your trusted partner for real estate across all 10 provinces of Zimbabwe. Connecting buyers, sellers, and renters nationwide.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-whatsapp"></i></a>
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
                        <li><a href="<?php echo view_url('properties/list.php'); ?>">Properties</a></li>
                        <li><a href="#zimbabwe-map">Browse by Province</a></li>
                        <li><a href="#agents">Our Agents</a></li>
                        <li><a href="#services">Services</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Provinces</h4>
                    <ul>
                        <?php foreach ($provinceCounts as $key => $province): ?>
                            <li><a href="<?php echo view_url('properties/list.php?province=' . $key); ?>"><?php echo $province['name']; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <div class="contact-info">
                        <p><i class="fas fa-phone"></i> +263 77 123 4567</p>
                        <p><i class="fas fa-envelope"></i> info@propertyhub.co.zw</p>
                        <p><i class="fas fa-map-marker-alt"></i> Harare Central, Zimbabwe</p>
                        <p><i class="fas fa-clock"></i> Mon - Fri: 8AM - 5PM</p>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> PropertyHub Zimbabwe. All rights reserved. | Proudly Zimbabwean</p>
            </div>
        </div>
    </footer>

    <script src="<?php echo asset_url('js/main.js'); ?>"></script>
    <script>
    // Zimbabwe Map Interaction
    document.addEventListener('DOMContentLoaded', function() {
        const provinces = document.querySelectorAll('.province');
        const provinceDetails = document.getElementById('provinceDetails');
        
        // Province data with real counts from database
        const provinceData = {
            <?php foreach ($provinceCounts as $key => $province): ?>
            '<?php echo $key; ?>': {
                name: '<?php echo $province['name']; ?>',
                properties: <?php echo $province['properties']; ?>,
                description: 'Explore <?php echo $province['properties']; ?> properties in <?php echo $province['name']; ?> province. Find your perfect home, commercial space, or investment opportunity.',
                averagePrice: '<?php 
                    // Get average price for province
                    $avgSql = "SELECT AVG(price) as avg_price FROM properties WHERE state = ?";
                    $avgStmt = $db->query($avgSql, [$province['name']]);
                    $avgResult = $avgStmt->fetch();
                    echo '$' . number_format($avgResult['avg_price'] ?? 0);
                ?>',
                popularAreas: '<?php
                    // Get popular areas
                    $areasSql = "SELECT city, COUNT(*) as count FROM properties WHERE state = ? GROUP BY city ORDER BY count DESC LIMIT 3";
                    $areasStmt = $db->query($areasSql, [$province['name']]);
                    $areas = $areasStmt->fetchAll();
                    $areaNames = array_column($areas, 'city');
                    echo implode(', ', $areaNames);
                ?>'
            },
            <?php endforeach; ?>
        };

        provinces.forEach(province => {
            province.addEventListener('click', function() {
                const provinceId = this.id;
                const data = provinceData[provinceId];
                
                if (data) {
                    provinceDetails.innerHTML = `
                        <div class="selected-province">
                            <h4>${data.name}</h4>
                            <div class="province-stats">
                                <div class="stat">
                                    <strong>${data.properties}</strong>
                                    <span>Properties</span>
                                </div>
                                <div class="stat">
                                    <strong>${data.averagePrice}</strong>
                                    <span>Avg Price</span>
                                </div>
                            </div>
                            <p>${data.description}</p>
                            <div class="popular-areas">
                                <strong>Popular Areas:</strong> ${data.popularAreas}
                            </div>
                            <a href="<?php echo view_url('properties/list.php?province='); ?>${provinceId}" class="btn-primary">
                                <i class="fas fa-search"></i> View Properties in ${data.name}
                            </a>
                        </div>
                    `;
                }
            });
        });

        // Province list items click
        document.querySelectorAll('.province-item').forEach(item => {
            item.addEventListener('click', function() {
                const provinceId = this.dataset.province;
                const data = provinceData[provinceId];
                
                if (data) {
                    provinceDetails.innerHTML = `
                        <div class="selected-province">
                            <h4>${data.name}</h4>
                            <div class="province-stats">
                                <div class="stat">
                                    <strong>${data.properties}</strong>
                                    <span>Properties</span>
                                </div>
                                <div class="stat">
                                    <strong>${data.averagePrice}</strong>
                                    <span>Avg Price</span>
                                </div>
                            </div>
                            <p>${data.description}</p>
                            <div class="popular-areas">
                                <strong>Popular Areas:</strong> ${data.popularAreas}
                            </div>
                            <a href="<?php echo view_url('properties/list.php?province='); ?>${provinceId}" class="btn-primary">
                                <i class="fas fa-search"></i> View Properties in ${data.name}
                            </a>
                        </div>
                    `;
                }
            });
        });
    });
    </script>
</body>
</html>