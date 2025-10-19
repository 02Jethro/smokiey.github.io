<?php
require_once '../../config.php';
require_once ROOT_DIR . '/propertyhub/core/Database.php';
require_once ROOT_DIR . '/propertyhub/models/Property.php';

$propertyModel = new Property();

// Get filters from URL
$filters = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}
if (isset($_GET['type']) && !empty($_GET['type'])) {
    $filters['type'] = $_GET['type'];
}
if (isset($_GET['province']) && !empty($_GET['province'])) {
    // Convert province key to full name for database query
    $provinceMapping = [
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
    
    $filters['province'] = $provinceMapping[$_GET['province']] ?? $_GET['province'];
}
if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
    $filters['min_price'] = $_GET['min_price'];
}
if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
    $filters['max_price'] = $_GET['max_price'];
}

// Debug: Check what filters are being applied
error_log("Applied filters: " . print_r($filters, true));

// Get properties based on filters
if (isset($filters['search'])) {
    $properties = $propertyModel->search($filters['search']);
} else {
    $properties = $propertyModel->getAll($filters);
}

// Get unique provinces for filter
$db = Database::getInstance();
$provinces = $db->query("SELECT DISTINCT state FROM properties ORDER BY state")->fetchAll();

// Province mapping for display
$provinceMapping = [
    'Harare' => 'harare',
    'Bulawayo' => 'bulawayo',
    'Manicaland' => 'manicaland',
    'Mashonaland Central' => 'mashonaland_central',
    'Mashonaland East' => 'mashonaland_east',
    'Mashonaland West' => 'mashonaland_west',
    'Masvingo' => 'masvingo',
    'Matabeleland North' => 'matabeleland_north',
    'Matabeleland South' => 'matabeleland_south',
    'Midlands' => 'midlands'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Properties - PropertyHub Zimbabwe</title>
    <link rel="stylesheet" href="<?php echo asset_url('css/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    .properties-filters {
        background: #fff;
        padding: 2rem;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
    }

    .filter-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .properties-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 10px;
    }

    .sort-options select {
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 5px;
        background: white;
    }

    .active-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin: 1rem 0;
    }

    .filter-tag {
        background: #3498db;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filter-tag .remove {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        font-weight: bold;
    }

    .no-properties {
        text-align: center;
        padding: 3rem;
        color: #666;
        grid-column: 1 / -1;
    }

    .no-properties i {
        font-size: 4rem;
        margin-bottom: 1rem;
        color: #bdc3c7;
    }

    .province-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        background: #e8f4fd;
        color: #3498db;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: bold;
        margin-left: 0.5rem;
    }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1>Properties for Sale & Rent</h1>
            <p>Find your perfect property across Zimbabwe</p>
        </div>

        <div class="properties-filters">
            <form method="GET" action="">
                <div class="filter-row">
                    <div class="form-group">
                        <input type="text" name="search" placeholder="Search by location, address, or description" 
                               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <select name="type">
                            <option value="">All Types</option>
                            <option value="apartment" <?php echo ($_GET['type'] ?? '') == 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                            <option value="house" <?php echo ($_GET['type'] ?? '') == 'house' ? 'selected' : ''; ?>>House</option>
                            <option value="stand" <?php echo ($_GET['type'] ?? '') == 'stand' ? 'selected' : ''; ?>>Stand</option>
                            <option value="commercial" <?php echo ($_GET['type'] ?? '') == 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                            <option value="farm" <?php echo ($_GET['type'] ?? '') == 'farm' ? 'selected' : ''; ?>>Farm</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <select name="province">
                            <option value="">All Provinces</option>
                            <?php foreach ($provinces as $province): 
                                $provinceKey = $provinceMapping[$province['state']] ?? strtolower(str_replace(' ', '_', $province['state']));
                            ?>
                                <option value="<?php echo $provinceKey; ?>" 
                                    <?php echo ($_GET['province'] ?? '') == $provinceKey ? 'selected' : ''; ?>>
                                    <?php echo $province['state']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="filter-row">
                    <div class="form-group">
                        <input type="number" name="min_price" placeholder="Min Price ($)" 
                               value="<?php echo htmlspecialchars($_GET['min_price'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <input type="number" name="max_price" placeholder="Max Price ($)" 
                               value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-search"></i> Apply Filters
                        </button>
                        <a href="list.php" class="btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>

            <!-- Active Filters Display -->
            <?php if (!empty($_GET)): ?>
            <div class="active-filters">
                <strong>Active Filters:</strong>
                <?php foreach ($_GET as $key => $value): ?>
                    <?php if (!empty($value) && $key !== 'page'): ?>
                        <?php 
                        $displayValue = $value;
                        if ($key === 'province') {
                            // Convert province key back to display name
                            $reverseMapping = array_flip($provinceMapping);
                            $displayValue = $reverseMapping[$value] ?? $value;
                        }
                        ?>
                        <div class="filter-tag">
                            <?php echo ucfirst($key); ?>: <?php echo htmlspecialchars($displayValue); ?>
                            <a href="?<?php echo http_build_query(array_diff_key($_GET, [$key => ''])); ?>" class="remove">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="properties-header">
            <h2>
                <?php echo count($properties); ?> Properties Found
                <?php if (isset($filters['province'])): ?>
                    <span class="province-badge">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php 
                        $reverseMapping = array_flip($provinceMapping);
                        echo $reverseMapping[$filters['province']] ?? $filters['province']; 
                        ?>
                    </span>
                <?php endif; ?>
            </h2>
            <div class="sort-options">
                <select id="sortProperties">
                    <option value="newest">Newest First</option>
                    <option value="price_low">Price: Low to High</option>
                    <option value="price_high">Price: High to Low</option>
                </select>
            </div>
        </div>

        <div class="properties-grid" id="propertiesGrid">
            <?php if (empty($properties)): ?>
                <div class="no-properties">
                    <i class="fas fa-search fa-3x"></i>
                    <h3>No properties found</h3>
                    <p>Try adjusting your search criteria or <a href="list.php">browse all properties</a>.</p>
                    
                    <?php if (!empty($filters)): ?>
                        <div style="margin-top: 2rem;">
                            <p>Suggestions:</p>
                            <ul style="text-align: left; display: inline-block;">
                                <li>Remove some filters to see more results</li>
                                <li>Try a different province or property type</li>
                                <li>Check your price range</li>
                                <li>Use broader search terms</li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($properties as $property): ?>
                <div class="property-card" data-price="<?php echo $property['price']; ?>" data-date="<?php echo strtotime($property['created_at']); ?>">
                    <div class="property-image">
                        <img src="<?php echo asset_url('images/default-property.jpg'); ?>" alt="<?php echo htmlspecialchars($property['title']); ?>">
                        <div class="property-badge">
                            <span class="property-type"><?php echo ucfirst($property['type']); ?></span>
                            <span class="property-status status-<?php echo $property['status']; ?>">
                                <?php echo ucfirst($property['status']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="property-info">
                        <h3><?php echo htmlspecialchars($property['title']); ?></h3>
                        <p class="property-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($property['address'] . ', ' . $property['city'] . ', ' . $property['state']); ?>
                        </p>
                        <p class="property-price">$<?php echo number_format($property['price']); ?></p>
                        <div class="property-features">
                            <span class="feature">
                                <i class="fas fa-bed"></i>
                                <?php echo $property['bedrooms']; ?> Beds
                            </span>
                            <span class="feature">
                                <i class="fas fa-bath"></i>
                                <?php echo $property['bathrooms']; ?> Baths
                            </span>
                            <span class="feature">
                                <i class="fas fa-vector-square"></i>
                                <?php echo number_format($property['area_sqft']); ?> Sq Ft
                            </span>
                        </div>
                        <div class="property-actions">
                            <a href="view.php?id=<?php echo $property['id']; ?>" class="btn-secondary">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                            <button class="btn-icon favorite-btn" data-property-id="<?php echo $property['id']; ?>">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
    // Property sorting functionality
    document.getElementById('sortProperties').addEventListener('change', function() {
        const sortBy = this.value;
        const grid = document.getElementById('propertiesGrid');
        const properties = Array.from(grid.getElementsByClassName('property-card'));
        
        properties.sort((a, b) => {
            switch(sortBy) {
                case 'price_low':
                    return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
                case 'price_high':
                    return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
                case 'newest':
                default:
                    return parseFloat(b.dataset.date) - parseFloat(a.dataset.date);
            }
        });
        
        // Re-append sorted properties
        properties.forEach(property => grid.appendChild(property));
    });

    // Auto-submit form when province changes (optional enhancement)
    document.querySelector('select[name="province"]').addEventListener('change', function() {
        if (this.value) {
            this.form.submit();
        }
    });

    // Display loading state when form is submitted
    document.querySelector('form').addEventListener('submit', function() {
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
        submitBtn.disabled = true;
    });
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>