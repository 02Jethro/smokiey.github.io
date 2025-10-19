<?php
require_once '../../config.php';
require_auth();
require_role([USER_LANDLORD, USER_PROPERTY_MANAGER]);

require_once '../../models/Property.php';
require_once '../../models/User.php';

$propertyModel = new Property();
$userModel = new User();

$userId = $_SESSION['user_id'];
$properties = $propertyModel->getByOwner($userId);

// Handle property deletion
if (isset($_POST['delete_property'])) {
    $propertyId = $_POST['property_id'];
    if ($propertyModel->delete($propertyId)) {
        $_SESSION['success'] = 'Property deleted successfully!';
        header('Location: manage.php');
        exit;
    } else {
        $_SESSION['error'] = 'Failed to delete property.';
    }
}

// Handle status updates
if (isset($_POST['update_status'])) {
    $propertyId = $_POST['property_id'];
    $newStatus = $_POST['status'];
    
    if ($propertyModel->update($propertyId, ['status' => $newStatus])) {
        $_SESSION['success'] = 'Property status updated successfully!';
        header('Location: manage.php');
        exit;
    } else {
        $_SESSION['error'] = 'Failed to update property status.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Properties - PropertyHub</title>
    <link rel="stylesheet" href="<?php echo asset_url('css/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    .manage-properties {
        padding: 2rem 0;
    }

    .page-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .page-header h1 {
        margin: 0;
        color: #2c3e50;
    }

    .properties-table {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .properties-table table {
        width: 100%;
        border-collapse: collapse;
    }

    .properties-table th,
    .properties-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .properties-table th {
        background: #f8f9fa;
        font-weight: 600;
        color: #2c3e50;
    }

    .property-image-small {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        object-fit: cover;
    }

    .property-title {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.25rem;
    }

    .property-address {
        color: #666;
        font-size: 0.9rem;
    }

    .property-price {
        font-weight: bold;
        color: #27ae60;
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: bold;
        text-transform: uppercase;
    }

    .status-available { background: #d4edda; color: #155724; }
    .status-rented { background: #fff3cd; color: #856404; }
    .status-sold { background: #f8d7da; color: #721c24; }
    .status-maintenance { background: #d1ecf1; color: #0c5460; }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .btn-small {
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
        border-radius: 5px;
        text-decoration: none;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        transition: all 0.3s ease;
    }

    .btn-view { background: #3498db; color: white; }
    .btn-edit { background: #f39c12; color: white; }
    .btn-delete { background: #e74c3c; color: white; }

    .btn-small:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    .status-form {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .status-select {
        padding: 0.4rem;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 0.8rem;
    }

    .update-btn {
        background: #27ae60;
        color: white;
        border: none;
        padding: 0.4rem 0.8rem;
        border-radius: 5px;
        cursor: pointer;
        font-size: 0.8rem;
    }

    .no-properties {
        text-align: center;
        padding: 3rem;
        color: #666;
    }

    .no-properties i {
        font-size: 4rem;
        color: #bdc3c7;
        margin-bottom: 1rem;
    }

    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: #fff;
        padding: 1.5rem;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        text-align: center;
    }

    .stat-card h3 {
        font-size: 2rem;
        color: #3498db;
        margin: 0 0 0.5rem 0;
    }

    .stat-card p {
        margin: 0;
        color: #666;
        font-weight: 500;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }

    .modal-content {
        background-color: #fff;
        margin: 15% auto;
        padding: 2rem;
        border-radius: 10px;
        width: 90%;
        max-width: 400px;
        text-align: center;
    }

    .modal-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 1.5rem;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .properties-table th,
        .properties-table td {
            padding: 0.75rem 0.5rem;
            font-size: 0.9rem;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn-small {
            justify-content: center;
        }

        .status-form {
            flex-direction: column;
            align-items: stretch;
        }

        .stats-cards {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .properties-table {
            font-size: 0.8rem;
        }

        .property-image-small {
            width: 40px;
            height: 40px;
        }

        .modal-content {
            margin: 10% auto;
            padding: 1.5rem;
        }
    }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <div class="manage-properties">
            <div class="page-header">
                <h1>Manage Properties</h1>
                <a href="<?php echo view_url('properties/add.php'); ?>" class="btn-primary">
                    <i class="fas fa-plus"></i> Add New Property
                </a>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-cards">
                <div class="stat-card">
                    <h3><?php echo count($properties); ?></h3>
                    <p>Total Properties</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo count(array_filter($properties, fn($p) => $p['status'] === 'available')); ?></h3>
                    <p>Available</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo count(array_filter($properties, fn($p) => $p['status'] === 'rented')); ?></h3>
                    <p>Rented</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo count(array_filter($properties, fn($p) => $p['status'] === 'sold')); ?></h3>
                    <p>Sold</p>
                </div>
            </div>

            <?php if (empty($properties)): ?>
                <div class="no-properties">
                    <i class="fas fa-home"></i>
                    <h3>No Properties Found</h3>
                    <p>You haven't added any properties yet. Start by adding your first property.</p>
                    <a href="<?php echo view_url('properties/add.php'); ?>" class="btn-primary">
                        <i class="fas fa-plus"></i> Add Your First Property
                    </a>
                </div>
            <?php else: ?>
                <div class="properties-table">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Property</th>
                                    <th>Location</th>
                                    <th>Type</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($properties as $property): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 1rem;">
                                            <img src="<?php echo asset_url('images/default-property.jpg'); ?>" 
                                                 alt="<?php echo htmlspecialchars($property['title']); ?>" 
                                                 class="property-image-small">
                                            <div>
                                                <div class="property-title"><?php echo htmlspecialchars($property['title']); ?></div>
                                                <div class="property-features" style="font-size: 0.8rem; color: #666;">
                                                    <?php echo $property['bedrooms']; ?> beds • <?php echo $property['bathrooms']; ?> baths • <?php echo number_format($property['area_sqft']); ?> sq ft
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="property-address">
                                            <?php echo htmlspecialchars($property['city'] . ', ' . $property['state']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span style="text-transform: capitalize;"><?php echo $property['type']; ?></span>
                                    </td>
                                    <td>
                                        <div class="property-price">$<?php echo number_format($property['price']); ?></div>
                                    </td>
                                    <td>
                                        <form method="POST" class="status-form">
                                            <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                                            <select name="status" class="status-select" onchange="this.form.submit()">
                                                <option value="available" <?php echo $property['status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                                                <option value="rented" <?php echo $property['status'] === 'rented' ? 'selected' : ''; ?>>Rented</option>
                                                <option value="sold" <?php echo $property['status'] === 'sold' ? 'selected' : ''; ?>>Sold</option>
                                                <option value="maintenance" <?php echo $property['status'] === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="<?php echo view_url('properties/view.php?id=' . $property['id']); ?>" 
                                               class="btn-small btn-view">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="<?php echo view_url('properties/edit.php?id=' . $property['id']); ?>" 
                                               class="btn-small btn-edit">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button class="btn-small btn-delete" 
                                                    onclick="confirmDelete(<?php echo $property['id']; ?>, '<?php echo htmlspecialchars($property['title']); ?>')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3>Confirm Delete</h3>
            <p>Are you sure you want to delete "<span id="propertyName"></span>"?</p>
            <p class="text-warning"><small>This action cannot be undone.</small></p>
            <form id="deleteForm" method="POST">
                <input type="hidden" name="property_id" id="deletePropertyId">
                <input type="hidden" name="delete_property" value="1">
                <div class="modal-buttons">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-primary btn-delete">Delete Property</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function confirmDelete(propertyId, propertyName) {
        document.getElementById('propertyName').textContent = propertyName;
        document.getElementById('deletePropertyId').value = propertyId;
        document.getElementById('deleteModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('deleteModal');
        if (event.target === modal) {
            closeModal();
        }
    }

    // Auto-submit status forms when selection changes
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', function() {
            // Add loading state
            const originalText = this.nextElementSibling?.value;
            if (this.nextElementSibling && this.nextElementSibling.type === 'submit') {
                this.nextElementSibling.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                this.nextElementSibling.disabled = true;
            }
            this.form.submit();
        });
    });
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>