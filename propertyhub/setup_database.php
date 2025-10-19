<?php
// Database setup script
try {
    // Connect to MySQL without selecting database
    $pdo = new PDO("mysql:host=localhost", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS PropertyHub");
    $pdo->exec("USE PropertyHub");

    echo "Database created successfully!<br>";

    // Create users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            phone VARCHAR(20),
            user_type ENUM('admin', 'property_manager', 'landlord', 'tenant', 'buyer', 'seller') NOT NULL,
            profile_image VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    echo "Users table created successfully!<br>";

    // Create properties table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS properties (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            type ENUM('apartment', 'house', 'condo', 'townhouse', 'commercial', 'land') NOT NULL,
            price DECIMAL(12,2) NOT NULL,
            address TEXT NOT NULL,
            city VARCHAR(100) NOT NULL,
            state VARCHAR(100) NOT NULL,
            zip_code VARCHAR(20) NOT NULL,
            bedrooms INT,
            bathrooms INT,
            area_sqft INT,
            status ENUM('available', 'rented', 'sold', 'maintenance') DEFAULT 'available',
            owner_id INT,
            manager_id INT,
            featured BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (owner_id) REFERENCES users(id),
            FOREIGN KEY (manager_id) REFERENCES users(id)
        )
    ");
    echo "Properties table created successfully!<br>";

    // Create payments table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT,
            landlord_id INT,
            property_id INT,
            amount DECIMAL(10,2) NOT NULL,
            payment_type ENUM('rent', 'deposit', 'maintenance', 'purchase') NOT NULL,
            payment_method ENUM('credit_card', 'bank_transfer', 'paypal', 'ecocash', 'cash') DEFAULT 'bank_transfer',
            payment_gateway VARCHAR(50),
            gateway_reference VARCHAR(255),
            transaction_id VARCHAR(255),
            status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
            payment_date TIMESTAMP NULL,
            due_date DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (tenant_id) REFERENCES users(id),
            FOREIGN KEY (landlord_id) REFERENCES users(id),
            FOREIGN KEY (property_id) REFERENCES properties(id)
        )
    ");
    echo "Payments table created successfully!<br>";

    // Create messages table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sender_id INT NOT NULL,
            receiver_id INT NOT NULL,
            property_id INT,
            subject VARCHAR(255),
            message TEXT NOT NULL,
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (sender_id) REFERENCES users(id),
            FOREIGN KEY (receiver_id) REFERENCES users(id),
            FOREIGN KEY (property_id) REFERENCES properties(id)
        )
    ");
    echo "Messages table created successfully!<br>";

    // Create maintenance_requests table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS maintenance_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            property_id INT,
            tenant_id INT,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
            status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
            assigned_to INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (property_id) REFERENCES properties(id),
            FOREIGN KEY (tenant_id) REFERENCES users(id),
            FOREIGN KEY (assigned_to) REFERENCES users(id)
        )
    ");
    echo "Maintenance requests table created successfully!<br>";

    // Create sample admin user
    $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, email, password, first_name, last_name, user_type) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['admin', 'admin@propertyhub.com', $hashed_password, 'System', 'Administrator', 'admin']);
    echo "Admin user created successfully!<br>";

    echo "<h3>Database setup completed successfully!</h3>";
    echo "<p>You can now <a href='index.php'>access the application</a></p>";

} catch(PDOException $e) {
    die("Database setup failed: " . $e->getMessage());
}
?>