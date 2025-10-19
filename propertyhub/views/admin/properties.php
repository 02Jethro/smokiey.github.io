<?php
require_once '../../config.php';
require_auth();
require_role([USER_ADMIN]);

require_once '../../models/Property.php';
require_once '../../models/User.php';

$propertyModel = new Property();
$userModel = new User();

$properties = $propertyModel->getAll();
$landlords = $userModel->getAll(USER_LANDLORD);

$page_title = "Manage Properties";
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
            <h1>Manage Properties</h1>
            <p>View and manage all properties in the system</p>
        </div>

        <div class="admin-content">
            <div class="properties-management">
                <div class="management-header">
                    <div class="search-box">
                        <input type="text" id="propertySearch" placeholder="Search properties...">
                    </div>
                    <div class="filter-options">
                        <select id="statusFilter">
                            <option value="">All Status</option>
                            <option value="available">Available</option>
                            <option value="rented">Rented</option>
                            <option value="sold">Sold</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="properties-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Owner</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Location</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($properties as $property): ?>
                            <tr>
                                <td><?php echo $property['id']; ?></td>
                                <td>
                                    <div class="property-title">
                                        <strong><?php echo htmlspecialchars($property['title']); ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    $owner = $userModel->getById($property['owner_id']);
                                    echo $owner ? htmlspecialchars($owner['first_name'] . ' ' . $owner['last_name']) : 'N/A';
                                    ?>
                                </td>
                                <td>
                                    <span class="property-type"><?php echo ucfirst($property['type']); ?></span>
                                </td>
                                <td>$<?php echo number_format($property['price']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $property['status']; ?>">
                                        <?php echo ucfirst($property['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($property['city'] . ', ' . $property['state']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($property['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="<?php echo view_url('properties/view.php?id=' . $property['id']); ?>" class="btn-small btn-view">View</a>
                                        <a href="<?php echo view_url('properties/add.php?edit=' . $property['id']); ?>" class="btn-small btn-edit">Edit</a>
                                        <form action="<?php echo BASE_URL; ?>controllers/PropertyController.php" method="POST" class="inline-form">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $property['id']; ?>">
                                            <button type="submit" class="btn-small btn-delete" onclick="return confirm('Are you sure you want to delete this property?')">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (empty($properties)): ?>
                    <div class="no-properties">
                        <h3>No Properties Found</h3>
                        <p>There are no properties in the system yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    // Search functionality
    document.getElementById('propertySearch').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('.properties-table tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Filter functionality
    document.getElementById('statusFilter').addEventListener('change', function() {
        const status = this.value;
        const rows = document.querySelectorAll('.properties-table tbody tr');
        
        rows.forEach(row => {
            if (!status) {
                row.style.display = '';
                return;
            }
            
            const statusCell = row.querySelector('.status-badge');
            if (statusCell && statusCell.classList.contains('status-' + status)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    </script>

    <style>
    .properties-management {
        background: #fff;
        padding: 1.5rem;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .management-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        gap: 1rem;
    }

    .search-box input {
        padding: 0.5rem 1rem;
        border: 1px solid #ddd;
        border-radius: 5px;
        width: 300px;
    }

    .filter-options select {
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .properties-table {
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

    .property-title {
        max-width: 200px;
    }

    .property-type {
        padding: 0.25rem 0.5rem;
        background: #e8f4fd;
        color: #3498db;
        border-radius: 4px;
        font-size: 0.8rem;
    }

    .status-badge {
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

    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .btn-view {
        background: #3498db;
        color: white;
    }

    .btn-edit {
        background: #f39c12;
        color: white;
    }

    .btn-delete {
        background: #e74c3c;
        color: white;
    }

    .no-properties {
        text-align: center;
        padding: 3rem;
        color: #666;
    }

    @media (max-width: 768px) {
        .management-header {
            flex-direction: column;
            align-items: stretch;
        }
        
        .search-box input {
            width: 100%;
        }
        
        .properties-table {
            font-size: 0.8rem;
        }
        
        .action-buttons {
            flex-direction: column;
        }
    }
    </style>

    <?php include '../includes/footer.php'; ?>
</body>
</html>