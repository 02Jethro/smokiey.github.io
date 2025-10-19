<?php
require_once '../../config.php';
require_auth();

$propertyId = $_GET['id'] ?? 0;
$propertyModel = new Property();
$property = $propertyModel->getById($propertyId);

// Check ownership
if (!$property || $property['owner_id'] != $_SESSION['user_id']) {
    $_SESSION['error'] = 'Property not found or access denied.';
    redirect('views/properties/manage.php');
}

$propertyImages = $propertyModel->getImages($propertyId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Property - PropertyHub</title>
    <link rel="stylesheet" href="<?php echo asset_url('css/style.css'); ?>">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1>Edit Property</h1>
            <p>Manage your property details and images</p>
        </div>

        <div class="property-edit-container">
            <!-- Image Management Section -->
            <div class="image-management-section">
                <h3>Property Images</h3>
                <p>Upload up to 4 images for your property. First image will be used as primary.</p>
                
                <div class="current-images">
                    <?php if (empty($propertyImages)): ?>
                        <div class="no-images">
                            <i class="fas fa-images fa-3x"></i>
                            <p>No images uploaded yet</p>
                        </div>
                    <?php else: ?>
                        <div class="images-grid">
                            <?php foreach ($propertyImages as $image): ?>
                            <div class="image-item <?php echo $image['is_primary'] ? 'primary' : ''; ?>" data-image-id="<?php echo $image['id']; ?>">
                                <img src="<?php echo $image['image_url']; ?>" alt="Property Image">
                                <div class="image-actions">
                                    <?php if (!$image['is_primary']): ?>
                                        <button class="btn-set-primary" data-image-id="<?php echo $image['id']; ?>">
                                            <i class="fas fa-star"></i> Set Primary
                                        </button>
                                    <?php else: ?>
                                        <span class="primary-badge"><i class="fas fa-star"></i> Primary</span>
                                    <?php endif; ?>
                                    <button class="btn-delete-image" data-image-id="<?php echo $image['id']; ?>">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (count($propertyImages) < 4): ?>
                <div class="upload-section">
                    <form action="<?php echo BASE_URL; ?>controllers/PropertyImageController.php" method="POST" enctype="multipart/form-data" class="upload-form">
                        <input type="hidden" name="action" value="upload_images">
                        <input type="hidden" name="property_id" value="<?php echo $propertyId; ?>">
                        
                        <div class="form-group">
                            <label>Upload Images (<?php echo 4 - count($propertyImages); ?> remaining)</label>
                            <input type="file" name="property_images[]" multiple accept="image/*" 
                                   max="<?php echo 4 - count($propertyImages); ?>">
                            <small>Supported formats: JPG, PNG, GIF. Max file size: 5MB</small>
                        </div>
                        
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-upload"></i> Upload Images
                        </button>
                    </form>
                </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Maximum of 4 images reached. Delete some images to upload new ones.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Property Details Form -->
            <div class="property-form-section">
                <h3>Property Details</h3>
                <form action="<?php echo BASE_URL; ?>controllers/PropertyController.php" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?php echo $propertyId; ?>">
                    
                    <!-- Your existing property form fields here -->
                    <!-- ... -->
                    
                </form>
            </div>
        </div>
    </div>

    <script>
    // Image management JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        // Set primary image
        document.querySelectorAll('.btn-set-primary').forEach(btn => {
            btn.addEventListener('click', function() {
                const imageId = this.dataset.imageId;
                setPrimaryImage(imageId);
            });
        });

        // Delete image
        document.querySelectorAll('.btn-delete-image').forEach(btn => {
            btn.addEventListener('click', function() {
                const imageId = this.dataset.imageId;
                if (confirm('Are you sure you want to delete this image?')) {
                    deleteImage(imageId);
                }
            });
        });

        function setPrimaryImage(imageId) {
            const formData = new FormData();
            formData.append('action', 'set_primary_image');
            formData.append('property_id', <?php echo $propertyId; ?>);
            formData.append('image_id', imageId);

            fetch('<?php echo BASE_URL; ?>controllers/PropertyImageController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to set primary image: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while setting primary image.');
            });
        }

        function deleteImage(imageId) {
            const formData = new FormData();
            formData.append('action', 'delete_image');
            formData.append('property_id', <?php echo $propertyId; ?>);
            formData.append('image_id', imageId);

            fetch('<?php echo BASE_URL; ?>controllers/PropertyImageController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to delete image: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting image.');
            });
        }
    });
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>