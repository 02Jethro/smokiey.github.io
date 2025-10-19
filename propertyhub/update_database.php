<?php
// Database update script for PropertyHub Zimbabwe
require_once 'config.php';
require_once ROOT_DIR . '/propertyhub/core/Database.php';
require_once ROOT_DIR . '/propertyhub/models/Property.php';
require_once ROOT_DIR . '/propertyhub/models/User.php';

try {
    $db = Database::getInstance();
    echo "Starting database updates...<br>";
    
    // 1. Create property_images table
    echo "Creating property_images table...<br>";
    $sql = "CREATE TABLE IF NOT EXISTS property_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        property_id INT NOT NULL,
        image_url VARCHAR(500) NOT NULL,
        is_primary BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
        INDEX idx_property_id (property_id)
    )";
    
    if ($db->query($sql)) {
        echo "✓ property_images table created successfully<br>";
    } else {
        echo "✗ Failed to create property_images table<br>";
    }
    
    // 2. Add default property images
    echo "Adding default property images...<br>";
    $propertyModel = new Property();
    $properties = $propertyModel->getAll();
    
    foreach ($properties as $property) {
        // Add 1-4 random images for each property
        $imageCount = rand(1, 4);
        for ($i = 0; $i < $imageCount; $i++) {
            $isPrimary = ($i === 0) ? 1 : 0;
            $imageUrl = asset_url("images/properties/property-" . rand(1, 10) . ".jpg");
            
            $sql = "INSERT INTO property_images (property_id, image_url, is_primary) VALUES (?, ?, ?)";
            $db->query($sql, [$property['id'], $imageUrl, $isPrimary]);
        }
        echo "✓ Added {$imageCount} images for property ID {$property['id']}<br>";
    }
    
    // 3. Create tenancies table for rented properties
    echo "Creating tenancies table...<br>";
    $sql = "CREATE TABLE IF NOT EXISTS tenancies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        property_id INT NOT NULL,
        tenant_id INT NOT NULL,
        landlord_id INT NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        rent_amount DECIMAL(10,2) NOT NULL,
        payment_due_day INT DEFAULT 1,
        status ENUM('active', 'ended', 'pending') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (property_id) REFERENCES properties(id),
        FOREIGN KEY (tenant_id) REFERENCES users(id),
        FOREIGN KEY (landlord_id) REFERENCES users(id),
        INDEX idx_property_tenant (property_id, tenant_id)
    )";
    
    if ($db->query($sql)) {
        echo "✓ tenancies table created successfully<br>";
    } else {
        echo "✗ Failed to create tenancies table<br>";
    }
    
    // 4. Create property_sales table for sold properties
    echo "Creating property_sales table...<br>";
    $sql = "CREATE TABLE IF NOT EXISTS property_sales (
        id INT AUTO_INCREMENT PRIMARY KEY,
        property_id INT NOT NULL,
        buyer_id INT NOT NULL,
        seller_id INT NOT NULL,
        sale_price DECIMAL(12,2) NOT NULL,
        sale_date DATE NOT NULL,
        transaction_id VARCHAR(100),
        status ENUM('completed', 'pending', 'cancelled') DEFAULT 'completed',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (property_id) REFERENCES properties(id),
        FOREIGN KEY (buyer_id) REFERENCES users(id),
        FOREIGN KEY (seller_id) REFERENCES users(id),
        INDEX idx_property_buyer (property_id, buyer_id)
    )";
    
    if ($db->query($sql)) {
        echo "✓ property_sales table created successfully<br>";
    } else {
        echo "✗ Failed to create property_sales table<br>";
    }
    
    // 5. Assign rented properties to tenants
    echo "Assigning rented properties to tenants...<br>";
    $userModel = new User();
    $tenants = $userModel->getAll('tenant');
    $landlords = $userModel->getAll('landlord');
    
    // Get all rented properties
    $sql = "SELECT * FROM properties WHERE status = 'rented'";
    $stmt = $db->query($sql);
    $rentedProperties = $stmt ? $stmt->fetchAll() : [];
    
    foreach ($rentedProperties as $property) {
        if (!empty($tenants) && !empty($landlords)) {
            $tenant = $tenants[array_rand($tenants)];
            $landlord = $landlords[array_rand($landlords)];
            
            $startDate = date('Y-m-d', strtotime('-' . rand(1, 12) . ' months'));
            $endDate = date('Y-m-d', strtotime('+' . rand(6, 24) . ' months'));
            $rentAmount = $property['price'] * 0.01; // 1% of property value as monthly rent
            
            $sql = "INSERT INTO tenancies (property_id, tenant_id, landlord_id, start_date, end_date, rent_amount) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            if ($db->query($sql, [$property['id'], $tenant['id'], $landlord['id'], $startDate, $endDate, $rentAmount])) {
                echo "✓ Assigned property ID {$property['id']} to tenant ID {$tenant['id']}<br>";
            }
        }
    }
    
    // 6. Assign sold properties to buyers
    echo "Assigning sold properties to buyers...<br>";
    $buyers = $userModel->getAll('buyer');
    
    // Get all sold properties
    $sql = "SELECT * FROM properties WHERE status = 'sold'";
    $stmt = $db->query($sql);
    $soldProperties = $stmt ? $stmt->fetchAll() : [];
    
    foreach ($soldProperties as $property) {
        if (!empty($buyers) && !empty($landlords)) {
            $buyer = $buyers[array_rand($buyers)];
            $seller = $landlords[array_rand($landlords)];
            
            $saleDate = date('Y-m-d', strtotime('-' . rand(1, 6) . ' months'));
            $salePrice = $property['price'] * (0.9 + (rand(0, 20) / 100)); // 90-110% of listed price
            
            $sql = "INSERT INTO property_sales (property_id, buyer_id, seller_id, sale_price, sale_date) 
                    VALUES (?, ?, ?, ?, ?)";
            
            if ($db->query($sql, [$property['id'], $buyer['id'], $seller['id'], $salePrice, $saleDate])) {
                echo "✓ Sold property ID {$property['id']} to buyer ID {$buyer['id']}<br>";
            }
        }
    }
    
    // 7. Update property statistics for homepage
    echo "Updating property statistics...<br>";
    
    // Get actual counts from database
    $sql = "SELECT COUNT(*) as total FROM properties";
    $stmt = $db->query($sql);
    $totalProperties = $stmt ? $stmt->fetch()['total'] : 0;
    
    $sql = "SELECT COUNT(DISTINCT tenant_id) as active_tenants FROM tenancies WHERE status = 'active'";
    $stmt = $db->query($sql);
    $activeTenants = $stmt ? $stmt->fetch()['active_tenants'] : 0;
    
    $sql = "SELECT COUNT(*) as sold FROM property_sales WHERE status = 'completed'";
    $stmt = $db->query($sql);
    $soldProperties = $stmt ? $stmt->fetch()['sold'] : 0;
    
    echo "<br><strong>Current Statistics:</strong><br>";
    echo "Total Properties: {$totalProperties}<br>";
    echo "Active Tenants: {$activeTenants}<br>";
    echo "Sold Properties: {$soldProperties}<br>";
    
    echo "<br><strong>Database updates completed successfully! 🎉</strong><br>";
    
} catch (Exception $e) {
    echo "Error during database update: " . $e->getMessage() . "<br>";
}
?>