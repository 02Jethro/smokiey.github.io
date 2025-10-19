<?php
require_once '../../config.php';
require_auth();
require_role([USER_LANDLORD, USER_PROPERTY_MANAGER, USER_ADMIN]);

$page_title = "Add New Property";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="<?php echo asset_url('css/style.css'); ?>">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1>Add New Property</h1>
            <p>List your property for rent or sale</p>
        </div>

        <form action="<?php echo BASE_URL; ?>controllers/PropertyController.php" method="POST" class="property-form">
            <input type="hidden" name="action" value="create">
            
            <div class="form-section">
                <h3>Basic Information</h3>
                
                <div class="form-group">
                    <label>Property Title *</label>
                    <input type="text" name="title" required maxlength="255" 
                           placeholder="e.g., Beautiful 3-Bedroom Apartment in Downtown">
                </div>

                <div class="form-group">
                    <label>Description *</label>
                    <textarea name="description" rows="5" required 
                              placeholder="Describe your property in detail..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Property Type *</label>
                        <select name="type" required>
                            <option value="">Select Type</option>
                            <option value="apartment">Apartment</option>
                            <option value="house">House</option>
                            <option value="condo">Condo</option>
                            <option value="commercial">Commercial</option>
                            <option value="land">Land</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Price *</label>
                        <input type="number" name="price" step="0.01" min="0" required 
                               placeholder="0.00">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3>Location Details</h3>
                
                <div class="form-group">
                    <label>Address *</label>
                    <input type="text" name="address" required 
                           placeholder="Street address">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>City *</label>
                        <input type="text" name="city" required>
                    </div>

                    <div class="form-group">
                        <label>State *</label>
                        <input type="text" name="state" required>
                    </div>

                    <div class="form-group">
                        <label>ZIP Code *</label>
                        <input type="text" name="zip_code" required 
                               placeholder="12345">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3>Property Details</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Bedrooms</label>
                        <input type="number" name="bedrooms" min="0" max="20" 
                               placeholder="0">
                    </div>

                    <div class="form-group">
                        <label>Bathrooms</label>
                        <input type="number" name="bathrooms" min="0" max="20" step="0.5"
                               placeholder="0">
                    </div>

                    <div class="form-group">
                        <label>Area (sq ft)</label>
                        <input type="number" name="area_sqft" min="0" 
                               placeholder="Square footage">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Add Property</button>
                <a href="<?php echo view_url('properties/list.php'); ?>" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>