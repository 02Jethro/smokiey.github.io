<?php
require_once '../config.php';
require_auth();

require_once '../models/Property.php';
require_once '../models/Payment.php';
require_once '../models/Message.php';
require_once '../models/Maintenance.php';
require_once '../models/User.php';

$propertyModel = new Property();
$paymentModel = new Payment();
$messageModel = new Message();
$maintenanceModel = new Maintenance();
$userModel = new User();

$userType = $_SESSION['user_type'];
$userId = $_SESSION['user_id'];

// Get data based on user type
if ($userType == USER_LANDLORD || $userType == USER_PROPERTY_MANAGER) {
    $properties = $propertyModel->getByOwner($userId);
    $activeTenants = 0;
    $monthlyRevenue = 0;
    $occupiedProperties = 0;
    
    foreach ($properties as $property) {
        $monthlyRevenue += $property['price'];
        if ($property['status'] == 'rented' || $property['status'] == 'sold') {
            $occupiedProperties++;
        }
    }
    
    $maintenanceStats = $maintenanceModel->getStats($userId);
    
    // Get tenant count for landlord properties
    $sql = "SELECT COUNT(DISTINCT t.tenant_id) as tenant_count 
            FROM tenancies t 
            JOIN properties p ON t.property_id = p.id 
            WHERE p.owner_id = ? AND t.status = 'active'";
    $stmt = Database::getInstance()->query($sql, [$userId]);
    $tenantResult = $stmt ? $stmt->fetch() : ['tenant_count' => 0];
    $activeTenants = $tenantResult['tenant_count'];
    
} elseif ($userType == USER_TENANT) {
    // Get tenant's rented properties
    $sql = "SELECT p.*, t.rent_amount, t.start_date, t.end_date 
            FROM properties p 
            JOIN tenancies t ON p.id = t.property_id 
            WHERE t.tenant_id = ? AND t.status = 'active'";
    $stmt = Database::getInstance()->query($sql, [$userId]);
    $rentals = $stmt ? $stmt->fetchAll() : [];
    
    $currentRent = 0;
    foreach ($rentals as $rental) {
        $currentRent += $rental['rent_amount'];
    }
    
    $nextPayment = $paymentModel->getPending($userId, 'tenant');
    
} elseif ($userType == USER_BUYER) {
    // Get buyer's purchased properties
    $sql = "SELECT p.*, ps.sale_price, ps.sale_date 
            FROM properties p 
            JOIN property_sales ps ON p.id = ps.property_id 
            WHERE ps.buyer_id = ?";
    $stmt = Database::getInstance()->query($sql, [$userId]);
    $purchasedProperties = $stmt ? $stmt->fetchAll() : [];
    
} elseif ($userType == USER_SELLER) {
    // Get seller's sold properties
    $sql = "SELECT p.*, ps.sale_price, ps.sale_date 
            FROM properties p 
            JOIN property_sales ps ON p.id = ps.property_id 
            WHERE ps.seller_id = ?";
    $stmt = Database::getInstance()->query($sql, [$userId]);
    $soldProperties = $stmt ? $stmt->fetchAll() : [];
}

// Get property images for display
function getPropertyImage($propertyId) {
    $sql = "SELECT image_url FROM property_images WHERE property_id = ? AND is_primary = 1 LIMIT 1";
    $stmt = Database::getInstance()->query($sql, [$propertyId]);
    $result = $stmt ? $stmt->fetch() : null;
    return $result ? $result['image_url'] : asset_url('images/default-property.jpg');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PropertyHub</title>
    <link rel="stylesheet" href="<?php echo asset_url('css/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    /* Keep all the existing CSS styles from your dashboard */
    /* They remain the same, just adding a few new ones below */
    
    .property-image-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 2px;
        height: 120px;
        margin-bottom: 1rem;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .property-image-main {
        grid-column: 1 / -1;
        height: 70px;
    }
    
    .property-image-secondary {
        height: 50px;
    }
    
    .property-image-grid img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .tenant-info, .buyer-info {
        background: #f8f9fa;
        padding: 0.5rem;
        border-radius: 5px;
        margin-top: 0.5rem;
        font-size: 0.8rem;
    }
    
    .property-meta {
        display: flex;
        justify-content: space-between;
        font-size: 0.8rem;
        color: #666;
        margin: 0.5rem 0;
    }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="dashboard-header">
            <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</h1>
            <p>Here's what's happening with your properties today.</p>
        </div>

        <div class="dashboard-stats">
            <?php if ($userType == USER_LANDLORD || $userType == USER_PROPERTY_MANAGER): ?>
                <div class="stat-card">
                    <div class="stat-icon">🏠</div>
                    <div class="stat-info">
                        <h3><?php echo count($properties); ?></h3>
                        <p>Total Properties</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <h3><?php echo $activeTenants; ?></h3>
                        <p>Active Tenants</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <h3>$<?php echo number_format($monthlyRevenue); ?></h3>
                        <p>Monthly Revenue</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-info">
                        <h3><?php echo $occupiedProperties; ?></h3>
                        <p>Occupied Units</p>
                    </div>
                </div>
            <?php elseif ($userType == USER_TENANT): ?>
                <div class="stat-card">
                    <div class="stat-icon">🏠</div>
                    <div class="stat-info">
                        <h3><?php echo count($rentals); ?></h3>
                        <p>Rented Properties</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <h3>$<?php echo number_format($currentRent); ?></h3>
                        <p>Monthly Rent</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <div class="stat-info">
                        <h3><?php echo count($nextPayment); ?></h3>
                        <p>Pending Payments</p>
                    </div>
                </div>
            <?php elseif ($userType == USER_BUYER): ?>
                <div class="stat-card">
                    <div class="stat-icon">🏠</div>
                    <div class="stat-info">
                        <h3><?php echo count($purchasedProperties); ?></h3>
                        <p>Owned Properties</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <h3>$<?php 
                            $totalInvestment = 0;
                            foreach ($purchasedProperties as $property) {
                                $totalInvestment += $property['sale_price'];
                            }
                            echo number_format($totalInvestment);
                        ?></h3>
                        <p>Total Investment</p>
                    </div>
                </div>
            <?php elseif ($userType == USER_SELLER): ?>
                <div class="stat-card">
                    <div class="stat-icon">🏠</div>
                    <div class="stat-info">
                        <h3><?php echo count($soldProperties); ?></h3>
                        <p>Properties Sold</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <h3>$<?php 
                            $totalSales = 0;
                            foreach ($soldProperties as $property) {
                                $totalSales += $property['sale_price'];
                            }
                            echo number_format($totalSales);
                        ?></h3>
                        <p>Total Sales</p>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="stat-card">
                <div class="stat-icon">✉️</div>
                <div class="stat-info">
                    <h3><?php echo $messageModel->getUnreadCount($userId); ?></h3>
                    <p>Unread Messages</p>
                </div>
            </div>
        </div>

        <div class="dashboard-content">
            <!-- Quick Actions -->
            <div class="dashboard-section">
                <h2>Quick Actions</h2>
                <div class="action-buttons">
                    <?php if ($userType == USER_LANDLORD || $userType == USER_PROPERTY_MANAGER): ?>
                        <a href="<?php echo view_url('properties/add.php'); ?>" class="action-btn">
                            <span class="action-icon">➕</span>
                            <span>Add New Property</span>
                        </a>
                        <a href="<?php echo view_url('properties/manage.php'); ?>" class="action-btn">
                            <span class="action-icon">🏠</span>
                            <span>Manage Properties</span>
                        </a>
                        <a href="<?php echo view_url('payments/history.php'); ?>" class="action-btn">
                            <span class="action-icon">💰</span>
                            <span>View Payments</span>
                        </a>
                        <a href="<?php echo view_url('maintenance/requests.php'); ?>" class="action-btn">
                            <span class="action-icon">🔧</span>
                            <span>Maintenance</span>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($userType == USER_TENANT): ?>
                        <a href="<?php echo view_url('payments/make_payment.php'); ?>" class="action-btn">
                            <span class="action-icon">💰</span>
                            <span>Make Payment</span>
                        </a>
                        <a href="<?php echo view_url('maintenance/request.php'); ?>" class="action-btn">
                            <span class="action-icon">🔧</span>
                            <span>Request Maintenance</span>
                        </a>
                        <a href="<?php echo view_url('messages/inbox.php'); ?>" class="action-btn">
                            <span class="action-icon">✉️</span>
                            <span>Contact Landlord</span>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($userType == USER_BUYER): ?>
                        <a href="<?php echo view_url('properties/list.php'); ?>" class="action-btn">
                            <span class="action-icon">🔍</span>
                            <span>Browse Properties</span>
                        </a>
                        <a href="<?php echo view_url('messages/inbox.php'); ?>" class="action-btn">
                            <span class="action-icon">✉️</span>
                            <span>Contact Agents</span>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($userType == USER_SELLER): ?>
                        <a href="<?php echo view_url('properties/add.php'); ?>" class="action-btn">
                            <span class="action-icon">➕</span>
                            <span>List Property</span>
                        </a>
                        <a href="<?php echo view_url('properties/manage.php'); ?>" class="action-btn">
                            <span class="action-icon">🏠</span>
                            <span>My Listings</span>
                        </a>
                    <?php endif; ?>
                    
                    <a href="<?php echo view_url('messages/inbox.php'); ?>" class="action-btn">
                        <span class="action-icon">✉️</span>
                        <span>View Messages</span>
                    </a>
                    <a href="<?php echo view_url('profile.php'); ?>" class="action-btn">
                        <span class="action-icon">👤</span>
                        <span>Update Profile</span>
                    </a>
                </div>
            </div>

            <!-- Recent Properties for Landlords/Managers -->
            <?php if (($userType == USER_LANDLORD || $userType == USER_PROPERTY_MANAGER) && !empty($properties)): ?>
            <div class="dashboard-section">
                <h2>Your Properties</h2>
                <div class="properties-grid">
                    <?php foreach (array_slice($properties, 0, 3) as $property): 
                        $propertyImages = $propertyModel->getPropertyImages($property['id']);
                        $primaryImage = !empty($propertyImages) ? $propertyImages[0]['image_url'] : asset_url('images/default-property.jpg');
                    ?>
                    <div class="property-card">
                        <div class="property-image">
                            <img src="<?php echo $primaryImage; ?>" alt="<?php echo htmlspecialchars($property['title']); ?>">
                        </div>
                        <div class="property-info">
                            <h3><?php echo htmlspecialchars($property['title']); ?></h3>
                            <p class="property-address"><?php echo htmlspecialchars($property['address']); ?></p>
                            <div class="property-meta">
                                <span>$<?php echo number_format($property['price']); ?></span>
                                <span class="status-<?php echo $property['status']; ?>"><?php echo ucfirst($property['status']); ?></span>
                            </div>
                            
                            <?php if ($property['status'] == 'rented'): 
                                // Get tenant info for rented properties
                                $sql = "SELECT u.first_name, u.last_name, t.rent_amount 
                                        FROM tenancies t 
                                        JOIN users u ON t.tenant_id = u.id 
                                        WHERE t.property_id = ? AND t.status = 'active' 
                                        LIMIT 1";
                                $stmt = Database::getInstance()->query($sql, [$property['id']]);
                                $tenant = $stmt ? $stmt->fetch() : null;
                            ?>
                                <?php if ($tenant): ?>
                                <div class="tenant-info">
                                    <strong>Tenant:</strong> <?php echo $tenant['first_name'] . ' ' . $tenant['last_name']; ?><br>
                                    <strong>Rent:</strong> $<?php echo number_format($tenant['rent_amount']); ?>/month
                                </div>
                                <?php endif; ?>
                            <?php elseif ($property['status'] == 'sold'): 
                                // Get buyer info for sold properties
                                $sql = "SELECT u.first_name, u.last_name, ps.sale_price 
                                        FROM property_sales ps 
                                        JOIN users u ON ps.buyer_id = u.id 
                                        WHERE ps.property_id = ? 
                                        LIMIT 1";
                                $stmt = Database::getInstance()->query($sql, [$property['id']]);
                                $buyer = $stmt ? $stmt->fetch() : null;
                            ?>
                                <?php if ($buyer): ?>
                                <div class="buyer-info">
                                    <strong>Buyer:</strong> <?php echo $buyer['first_name'] . ' ' . $buyer['last_name']; ?><br>
                                    <strong>Sale Price:</strong> $<?php echo number_format($buyer['sale_price']); ?>
                                </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <a href="<?php echo view_url('properties/view.php?id=' . $property['id']); ?>" class="btn-secondary">View Details</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($properties) > 3): ?>
                    <div class="text-center">
                        <a href="<?php echo view_url('properties/manage.php'); ?>" class="btn-primary">View All Properties</a>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Rented Properties for Tenants -->
            <?php if ($userType == USER_TENANT && !empty($rentals)): ?>
            <div class="dashboard-section">
                <h2>Your Rented Properties</h2>
                <div class="properties-grid">
                    <?php foreach (array_slice($rentals, 0, 3) as $rental): 
                        $primaryImage = getPropertyImage($rental['id']);
                    ?>
                    <div class="property-card">
                        <div class="property-image">
                            <img src="<?php echo $primaryImage; ?>" alt="<?php echo htmlspecialchars($rental['title']); ?>">
                        </div>
                        <div class="property-info">
                            <h3><?php echo htmlspecialchars($rental['title']); ?></h3>
                            <p class="property-address"><?php echo htmlspecialchars($rental['address']); ?></p>
                            <div class="property-meta">
                                <span>$<?php echo number_format($rental['rent_amount']); ?>/month</span>
                                <span>Lease: <?php echo date('M Y', strtotime($rental['end_date'])); ?></span>
                            </div>
                            <a href="<?php echo view_url('properties/view.php?id=' . $rental['id']); ?>" class="btn-secondary">View Details</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Purchased Properties for Buyers -->
            <?php if ($userType == USER_BUYER && !empty($purchasedProperties)): ?>
            <div class="dashboard-section">
                <h2>Your Purchased Properties</h2>
                <div class="properties-grid">
                    <?php foreach (array_slice($purchasedProperties, 0, 3) as $property): 
                        $primaryImage = getPropertyImage($property['id']);
                    ?>
                    <div class="property-card">
                        <div class="property-image">
                            <img src="<?php echo $primaryImage; ?>" alt="<?php echo htmlspecialchars($property['title']); ?>">
                        </div>
                        <div class="property-info">
                            <h3><?php echo htmlspecialchars($property['title']); ?></h3>
                            <p class="property-address"><?php echo htmlspecialchars($property['address']); ?></p>
                            <div class="property-meta">
                                <span>Purchased: $<?php echo number_format($property['sale_price']); ?></span>
                                <span><?php echo date('M Y', strtotime($property['sale_date'])); ?></span>
                            </div>
                            <a href="<?php echo view_url('properties/view.php?id=' . $property['id']); ?>" class="btn-secondary">View Property</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Recent Messages -->
            <div class="dashboard-section">
                <h2>Recent Messages</h2>
                <?php
                $recentMessages = $messageModel->getRecentMessages($userId, 5);
                if (!empty($recentMessages)):
                ?>
                <div class="messages-list">
                    <?php foreach ($recentMessages as $message): ?>
                    <div class="message-item <?php echo !$message['is_read'] ? 'unread' : ''; ?>">
                        <div class="message-sender">
                            <strong><?php echo htmlspecialchars($message['sender_first_name'] . ' ' . $message['sender_last_name']); ?></strong>
                            <span class="message-time"><?php echo date('M j, g:i A', strtotime($message['created_at'])); ?></span>
                        </div>
                        <p class="message-preview"><?php echo htmlspecialchars(substr($message['message'], 0, 100)); ?>...</p>
                        <?php if (!empty($message['property_title'])): ?>
                            <span class="property-badge"><?php echo htmlspecialchars($message['property_title']); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center">
                    <a href="<?php echo view_url('messages/inbox.php'); ?>" class="btn-primary">View All Messages</a>
                </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <h3>No Messages Yet</h3>
                        <p class="text-muted">Start a conversation with property owners or agents</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add interactive functionality
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach(card => {
            card.addEventListener('click', function() {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });

        // Auto-refresh every 30 seconds for real-time updates
        setInterval(() => {
            // Could implement AJAX refresh here for live updates
            console.log('Dashboard auto-refresh check');
        }, 30000);
    });
    </script>
</body>
</html>