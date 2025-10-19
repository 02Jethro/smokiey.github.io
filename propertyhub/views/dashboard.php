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
    
    foreach ($properties as $property) {
        // Get active tenants count for each property
        $tenantsCount = $propertyModel->getActiveTenantsCount($property['id']);
        $activeTenants += $tenantsCount;
        
        // Calculate monthly revenue from active tenancies
        if ($property['status'] == 'rented') {
            $monthlyRevenue += $property['price'] * 0.01; // 1% of property value as rent
        }
    }
    
    $maintenanceStats = $maintenanceModel->getStats($userId);
    
} elseif ($userType == USER_TENANT) {
    $rentals = $propertyModel->getByTenant($userId);
    $currentRent = 0;
    
    if (!empty($rentals)) {
        $currentRent = $rentals[0]['rent_amount'] ?? $rentals[0]['price'] * 0.01;
    }
    
    $nextPayments = $paymentModel->getPending($userId, 'tenant');
    
} elseif ($userType == USER_BUYER) {
    // Get properties the buyer has shown interest in or purchased
    $interestedProperties = $propertyModel->getAll(['status' => 'available'], 3);
    
} elseif ($userType == USER_SELLER) {
    $properties = $propertyModel->getByOwner($userId);
    $soldProperties = array_filter($properties, function($prop) {
        return $prop['status'] == 'sold';
    });
}

// Get recent messages/conversations
function getRecentConversations($messageModel, $userId) {
    // This is a simplified version - you'll need to implement the actual method
    $sql = "SELECT DISTINCT 
            CASE 
                WHEN sender_id = ? THEN receiver_id 
                ELSE sender_id 
            END as other_user_id,
            MAX(created_at) as last_message_time
            FROM messages 
            WHERE sender_id = ? OR receiver_id = ?
            GROUP BY other_user_id 
            ORDER BY last_message_time DESC 
            LIMIT 5";
    
    // This would need to be implemented in the Message model
    return [];
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
    /* Dashboard Specific Styles */
    .dashboard-header {
        text-align: center;
        margin-bottom: 3rem;
        padding: 2rem 0;
    }

    .dashboard-header h1 {
        color: #2c3e50;
        margin-bottom: 0.5rem;
        font-size: 2.5rem;
    }

    .dashboard-header p {
        color: #666;
        font-size: 1.2rem;
    }

    .dashboard-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 3rem;
    }

    .stat-card {
        background: #fff;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 1.5rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-left: 4px solid #3498db;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }

    .stat-icon {
        font-size: 2.5rem;
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #3498db, #2980b9);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .stat-info h3 {
        font-size: 2.2rem;
        margin: 0;
        color: #2c3e50;
        font-weight: 700;
    }

    .stat-info p {
        margin: 0.5rem 0 0 0;
        color: #666;
        font-weight: 500;
        font-size: 1rem;
    }

    .dashboard-content {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .dashboard-section {
        background: #fff;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .dashboard-section h2 {
        color: #2c3e50;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #3498db;
        font-size: 1.5rem;
    }

    .action-buttons {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .action-btn {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border: 2px solid transparent;
        border-radius: 10px;
        text-decoration: none;
        color: #333;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .action-btn:hover {
        background: #3498db;
        color: white;
        transform: translateY(-2px);
        border-color: #3498db;
        box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
    }

    .action-icon {
        font-size: 1.5rem;
        width: 40px;
        height: 40px;
        background: #3498db;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .action-btn:hover .action-icon {
        background: white;
        color: #3498db;
    }

    .properties-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .property-card {
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }

    .property-card:hover {
        transform: translateY(-5px);
    }

    .property-image {
        width: 100%;
        height: 200px;
        overflow: hidden;
        position: relative;
    }

    .property-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .property-card:hover .property-image img {
        transform: scale(1.05);
    }

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

    .property-status {
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: bold;
        text-transform: uppercase;
    }

    .status-available { background: #27ae60; color: white; }
    .status-rented { background: #e67e22; color: white; }
    .status-sold { background: #e74c3c; color: white; }
    .status-maintenance { background: #f39c12; color: white; }

    .property-info {
        padding: 1.5rem;
    }

    .property-info h3 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
        font-size: 1.2rem;
    }

    .property-address {
        color: #666;
        margin: 0 0 0.5rem 0;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .property-price {
        font-size: 1.3rem;
        font-weight: bold;
        color: #27ae60;
        margin: 0 0 0.5rem 0;
    }

    .property-features {
        display: flex;
        gap: 1rem;
        margin: 1rem 0;
    }

    .feature {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #666;
        font-size: 0.9rem;
    }

    .text-center {
        text-align: center;
    }

    .messages-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .message-item {
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 10px;
        border-left: 4px solid #3498db;
        transition: background-color 0.3s ease;
    }

    .message-item:hover {
        background: #e8f4fd;
    }

    .message-sender {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .message-sender strong {
        color: #2c3e50;
    }

    .message-time {
        color: #666;
        font-size: 0.9rem;
    }

    .message-preview {
        color: #555;
        margin: 0;
        line-height: 1.5;
    }

    /* Status Colors for Stats */
    .stat-card:nth-child(1) { border-left-color: #3498db; }
    .stat-card:nth-child(2) { border-left-color: #e67e22; }
    .stat-card:nth-child(3) { border-left-color: #27ae60; }
    .stat-card:nth-child(4) { border-left-color: #e74c3c; }
    .stat-card:nth-child(5) { border-left-color: #9b59b6; }

    .stat-card:nth-child(1) .stat-icon { background: linear-gradient(135deg, #3498db, #2980b9); }
    .stat-card:nth-child(2) .stat-icon { background: linear-gradient(135deg, #e67e22, #d35400); }
    .stat-card:nth-child(3) .stat-icon { background: linear-gradient(135deg, #27ae60, #229954); }
    .stat-card:nth-child(4) .stat-icon { background: linear-gradient(135deg, #e74c3c, #c0392b); }
    .stat-card:nth-child(5) .stat-icon { background: linear-gradient(135deg, #9b59b6, #8e44ad); }

    /* Empty State Styles */
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #666;
    }

    .empty-state i {
        font-size: 4rem;
        color: #bdc3c7;
        margin-bottom: 1rem;
    }

    .empty-state h3 {
        color: #7f8c8d;
        margin-bottom: 0.5rem;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .dashboard-stats {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .action-buttons {
            grid-template-columns: 1fr;
        }
        
        .properties-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .dashboard-stats {
            grid-template-columns: 1fr;
        }
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
                        <h3>$<?php echo number_format($monthlyRevenue, 2); ?></h3>
                        <p>Monthly Revenue</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🔧</div>
                    <div class="stat-info">
                        <h3><?php echo $maintenanceStats['pending_requests'] ?? 0; ?></h3>
                        <p>Pending Requests</p>
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
                        <h3>$<?php echo number_format($currentRent, 2); ?></h3>
                        <p>Monthly Rent</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <div class="stat-info">
                        <h3><?php echo count($nextPayments); ?></h3>
                        <p>Pending Payments</p>
                    </div>
                </div>
                
            <?php elseif ($userType == USER_BUYER): ?>
                <div class="stat-card">
                    <div class="stat-icon">🔍</div>
                    <div class="stat-info">
                        <h3><?php echo count($interestedProperties); ?></h3>
                        <p>Properties Viewed</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💼</div>
                    <div class="stat-info">
                        <h3>0</h3>
                        <p>Offers Made</p>
                    </div>
                </div>
                
            <?php elseif ($userType == USER_SELLER): ?>
                <div class="stat-card">
                    <div class="stat-icon">🏠</div>
                    <div class="stat-info">
                        <h3><?php echo count($properties); ?></h3>
                        <p>Listed Properties</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <h3><?php echo count($soldProperties); ?></h3>
                        <p>Properties Sold</p>
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
                        
                    <?php elseif ($userType == USER_TENANT): ?>
                        <a href="<?php echo view_url('payments/make_payment.php'); ?>" class="action-btn">
                            <span class="action-icon">💰</span>
                            <span>Make Payment</span>
                        </a>
                        <a href="<?php echo view_url('maintenance/request.php'); ?>" class="action-btn">
                            <span class="action-icon">🔧</span>
                            <span>Request Maintenance</span>
                        </a>
                        <a href="<?php echo view_url('properties/list.php'); ?>" class="action-btn">
                            <span class="action-icon">🔍</span>
                            <span>Browse Properties</span>
                        </a>
                        
                    <?php elseif ($userType == USER_BUYER): ?>
                        <a href="<?php echo view_url('properties/list.php'); ?>" class="action-btn">
                            <span class="action-icon">🔍</span>
                            <span>Browse Properties</span>
                        </a>
                        <a href="<?php echo view_url('profile.php'); ?>" class="action-btn">
                            <span class="action-icon">👤</span>
                            <span>My Profile</span>
                        </a>
                        
                    <?php elseif ($userType == USER_SELLER): ?>
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

            <!-- Recent Properties -->
            <?php if (in_array($userType, [USER_LANDLORD, USER_PROPERTY_MANAGER, USER_SELLER])): ?>
            <div class="dashboard-section">
                <h2>Your Properties</h2>
                <?php if (empty($properties)): ?>
                    <div class="empty-state">
                        <i class="fas fa-home"></i>
                        <h3>No Properties Yet</h3>
                        <p class="text-muted">Start by adding your first property to manage</p>
                        <a href="<?php echo view_url('properties/add.php'); ?>" class="btn-primary mt-2">
                            <i class="fas fa-plus"></i> Add Your First Property
                        </a>
                    </div>
                <?php else: ?>
                    <div class="properties-grid">
                        <?php foreach (array_slice($properties, 0, 3) as $property): 
                            $propertyImages = $propertyModel->getImages($property['id']);
                            $primaryImage = !empty($propertyImages) ? $propertyImages[0]['image_url'] : asset_url('images/default-property.jpg');
                        ?>
                        <div class="property-card">
                            <div class="property-image">
                                <img src="<?php echo $primaryImage; ?>" alt="<?php echo htmlspecialchars($property['title']); ?>">
                                <div class="property-badge">
                                    <span class="property-type"><?php echo ucfirst($property['type']); ?></span>
                                    <span class="property-status status-<?php echo $property['status']; ?>">
                                        <?php echo ucfirst($property['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="property-info">
                                <h3><?php echo htmlspecialchars($property['title']); ?></h3>
                                <p class="property-address">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($property['address']); ?>
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
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Rented Properties for Tenants -->
            <?php if ($userType == USER_TENANT && !empty($rentals)): ?>
            <div class="dashboard-section">
                <h2>Your Rented Properties</h2>
                <div class="properties-grid">
                    <?php foreach (array_slice($rentals, 0, 3) as $property): 
                        $propertyImages = $propertyModel->getImages($property['id']);
                        $primaryImage = !empty($propertyImages) ? $propertyImages[0]['image_url'] : asset_url('images/default-property.jpg');
                    ?>
                    <div class="property-card">
                        <div class="property-image">
                            <img src="<?php echo $primaryImage; ?>" alt="<?php echo htmlspecialchars($property['title']); ?>">
                            <div class="property-badge">
                                <span class="property-type"><?php echo ucfirst($property['type']); ?></span>
                                <span class="property-status status-rented">Rented</span>
                            </div>
                        </div>
                        <div class="property-info">
                            <h3><?php echo htmlspecialchars($property['title']); ?></h3>
                            <p class="property-address">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($property['address']); ?>
                            </p>
                            <p class="property-price">$<?php echo number_format($property['rent_amount'] ?? $property['price'] * 0.01, 2); ?>/month</p>
                            <div class="property-features">
                                <span class="feature">
                                    <i class="fas fa-bed"></i>
                                    <?php echo $property['bedrooms']; ?> Beds
                                </span>
                                <span class="feature">
                                    <i class="fas fa-bath"></i>
                                    <?php echo $property['bathrooms']; ?> Baths
                                </span>
                            </div>
                            <a href="<?php echo view_url('properties/view.php?id=' . $property['id']); ?>" class="btn-secondary">View Details</a>
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
                    <div class="message-item">
                        <div class="message-sender">
                            <strong><?php echo htmlspecialchars($message['sender_first_name'] . ' ' . $message['sender_last_name']); ?></strong>
                            <span class="message-time"><?php echo date('M j, g:i A', strtotime($message['created_at'])); ?></span>
                        </div>
                        <p class="message-preview"><?php echo htmlspecialchars(substr($message['message'], 0, 100)); ?>...</p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center">
                    <a href="<?php echo view_url('messages/inbox.php'); ?>" class="btn-primary">View All Messages</a>
                </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <h3>No Messages</h3>
                        <p class="text-muted">You don't have any messages yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>