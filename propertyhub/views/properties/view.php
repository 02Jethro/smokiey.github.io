<?php
require_once '../../config.php';
require_once '../../models/Property.php';
require_once '../../models/User.php';

if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'Property not found.';
    redirect('properties/list.php');
}

$propertyModel = new Property();
$userModel = new User();

$property = $propertyModel->getById($_GET['id']);
if (!$property) {
    $_SESSION['error'] = 'Property not found.';
    redirect('properties/list.php');
}

// Get property images
$propertyImages = $propertyModel->getImages($property['id']);
$primaryImage = !empty($propertyImages) ? $propertyImages[0]['image_url'] : asset_url('images/default-property.jpg');

// Get property owner
$owner = $propertyModel->getPropertyOwner($property['id']);

// Get current tenant if property is rented
$tenant = null;
if ($property['status'] == 'rented') {
    $tenant = $propertyModel->getPropertyTenant($property['id']);
}

$page_title = $property['title'] . " - PropertyHub";
include '../includes/header.php';
?>

<div class="container">
    <div class="property-detail">
        <!-- Property Images -->
        <div class="property-gallery">
            <div class="main-image">
                <img src="<?php echo $primaryImage; ?>" alt="<?php echo htmlspecialchars($property['title']); ?>" id="mainImage">
            </div>
            <?php if (count($propertyImages) > 1): ?>
            <div class="image-thumbnails">
                <?php foreach ($propertyImages as $index => $image): ?>
                <img src="<?php echo $image['image_url']; ?>" 
                     alt="Property image <?php echo $index + 1; ?>" 
                     class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>"
                     onclick="changeImage('<?php echo $image['image_url']; ?>', this)">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Property Details -->
        <div class="property-info">
            <h1><?php echo htmlspecialchars($property['title']); ?></h1>
            <p class="property-address">
                <i class="fas fa-map-marker-alt"></i>
                <?php echo htmlspecialchars($property['address'] . ', ' . $property['city'] . ', ' . $property['state']); ?>
            </p>
            
            <div class="property-price">$<?php echo number_format($property['price']); ?></div>
            
            <div class="property-badges">
                <span class="property-type"><?php echo ucfirst($property['type']); ?></span>
                <span class="property-status status-<?php echo $property['status']; ?>">
                    <?php echo ucfirst($property['status']); ?>
                </span>
            </div>

            <div class="property-features">
                <div class="feature">
                    <i class="fas fa-bed"></i>
                    <span><?php echo $property['bedrooms']; ?> Bedrooms</span>
                </div>
                <div class="feature">
                    <i class="fas fa-bath"></i>
                    <span><?php echo $property['bathrooms']; ?> Bathrooms</span>
                </div>
                <div class="feature">
                    <i class="fas fa-vector-square"></i>
                    <span><?php echo number_format($property['area_sqft']); ?> Sq Ft</span>
                </div>
            </div>

            <div class="property-description">
                <h3>Description</h3>
                <p><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
            </div>

            <!-- Contact Information -->
            <div class="contact-section">
                <h3>Contact Information</h3>
                <?php if ($owner): ?>
                <div class="contact-info">
                    <p><strong>Property Owner:</strong> <?php echo htmlspecialchars($owner['first_name'] . ' ' . $owner['last_name']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($owner['phone']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($owner['email']); ?></p>
                </div>
                <?php endif; ?>

                <?php if (is_logged_in()): ?>
                <div class="contact-actions">
                    <a href="<?php echo view_url('messages/compose.php?to=' . $owner['id'] . '&property=' . $property['id']); ?>" class="btn-primary">
                        <i class="fas fa-envelope"></i> Send Message
                    </a>
                    <button class="btn-secondary favorite-btn" data-property-id="<?php echo $property['id']; ?>">
                        <i class="far fa-heart"></i> Add to Favorites
                    </button>
                </div>
                <?php else: ?>
                <div class="login-prompt">
                    <p><a href="<?php echo view_url('login.php'); ?>">Login</a> to contact the owner or save this property.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function changeImage(src, element) {
    document.getElementById('mainImage').src = src;
    
    // Update active thumbnail
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
    });
    element.classList.add('active');
}
</script>

<style>
.property-detail {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    margin-top: 2rem;
}

.property-gallery {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.main-image {
    width: 100%;
    height: 400px;
    border-radius: 10px;
    overflow: hidden;
}

.main-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-thumbnails {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.thumbnail {
    width: 80px;
    height: 80px;
    border-radius: 5px;
    cursor: pointer;
    opacity: 0.7;
    transition: opacity 0.3s ease;
    object-fit: cover;
}

.thumbnail.active,
.thumbnail:hover {
    opacity: 1;
    border: 2px solid #3498db;
}

.property-info h1 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.property-address {
    color: #666;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.property-price {
    font-size: 2rem;
    font-weight: bold;
    color: #27ae60;
    margin-bottom: 1rem;
}

.property-badges {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.property-type,
.property-status {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: bold;
    font-size: 0.9rem;
}

.property-type {
    background: #3498db;
    color: white;
}

.property-features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 10px;
}

.feature {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #2c3e50;
    font-weight: 500;
}

.feature i {
    color: #3498db;
    width: 20px;
}

.property-description {
    margin-bottom: 2rem;
}

.property-description h3 {
    color: #2c3e50;
    margin-bottom: 1rem;
}

.contact-section {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 10px;
}

.contact-section h3 {
    color: #2c3e50;
    margin-bottom: 1rem;
}

.contact-info p {
    margin: 0.5rem 0;
    color: #555;
}

.contact-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
    flex-wrap: wrap;
}

.login-prompt {
    text-align: center;
    padding: 1rem;
    background: #e8f4fd;
    border-radius: 5px;
    color: #3498db;
}

@media (max-width: 768px) {
    .property-detail {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .main-image {
        height: 300px;
    }
    
    .property-features {
        grid-template-columns: 1fr;
    }
    
    .contact-actions {
        flex-direction: column;
    }
}
</style>

<?php include '../includes/footer.php'; ?>