<?php
require_once '../../config.php';
require_auth();
require_role([USER_ADMIN]);

require_once '../../models/Report.php';
require_once '../../models/User.php';
require_once '../../models/Property.php';
require_once '../../models/Payment.php';

$reportModel = new Report();
$userModel = new User();
$propertyModel = new Property();
$paymentModel = new Payment();

// Get report data
$userStats = $reportModel->getUserStats();
$revenueStats = $reportModel->getRevenueStats();
$propertyPerformance = $reportModel->getPropertyPerformance();

// Date range for reports
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');

$page_title = "Reports & Analytics";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="<?php echo asset_url('css/style.css'); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1>Reports & Analytics</h1>
            <p>System-wide analytics and performance reports</p>
        </div>

        <div class="reports-container">
            <!-- Date Range Filter -->
            <div class="report-filters">
                <form method="GET" class="filter-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" name="start_date" value="<?php echo $startDate; ?>">
                        </div>
                        <div class="form-group">
                            <label>End Date</label>
                            <input type="date" name="end_date" value="<?php echo $endDate; ?>">
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn-primary">Apply Filter</button>
                            <a href="<?php echo view_url('admin/reports.php'); ?>" class="btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Key Metrics -->
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-icon">👥</div>
                    <div class="metric-info">
                        <h3><?php echo count($userModel->getAll()); ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon">🏠</div>
                    <div class="metric-info">
                        <h3><?php echo count($propertyModel->getAll()); ?></h3>
                        <p>Total Properties</p>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon">💰</div>
                    <div class="metric-info">
                        <h3>
                            $<?php 
                            $totalRevenue = 0;
                            foreach ($revenueStats as $stat) {
                                $totalRevenue += $stat['daily_revenue'];
                            }
                            echo number_format($totalRevenue);
                            ?>
                        </h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon">📊</div>
                    <div class="metric-info">
                        <h3><?php echo count($paymentModel->getByUser(1, 'landlord')); ?></h3>
                        <p>Transactions</p>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-grid">
                <div class="chart-card">
                    <h3>User Registration Trends</h3>
                    <div class="chart-container">
                        <canvas id="userRegistrationsChart" width="400" height="200"></canvas>
                    </div>
                </div>
                
                <div class="chart-card">
                    <h3>Revenue Overview</h3>
                    <div class="chart-container">
                        <canvas id="revenueChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Property Performance -->
            <div class="report-section">
                <h2>Property Performance</h2>
                <div class="table-responsive">
                    <table class="performance-table">
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Status</th>
                                <th>Price</th>
                                <th>Tenants</th>
                                <th>Maintenance</th>
                                <th>Revenue</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($propertyPerformance as $property): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($property['title']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $property['status']; ?>">
                                        <?php echo ucfirst($property['status']); ?>
                                    </span>
                                </td>
                                <td>$<?php echo number_format($property['price']); ?></td>
                                <td><?php echo $property['total_tenants']; ?></td>
                                <td><?php echo $property['maintenance_requests']; ?></td>
                                <td>$<?php echo number_format($property['total_income']); ?></td>
                                <td>
                                    <?php
                                    $performance = 0;
                                    if ($property['price'] > 0) {
                                        $performance = ($property['total_income'] / $property['price']) * 100;
                                    }
                                    ?>
                                    <div class="performance-bar">
                                        <div class="performance-fill" style="width: <?php echo min($performance, 100); ?>%"></div>
                                        <span class="performance-text"><?php echo number_format($performance, 1); ?>%</span>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Export Options -->
            <div class="report-section">
                <h2>Export Reports</h2>
                <div class="export-options">
                    <button class="export-btn" onclick="exportReport('pdf')">
                        <span class="export-icon">📄</span>
                        Export as PDF
                    </button>
                    <button class="export-btn" onclick="exportReport('excel')">
                        <span class="export-icon">📊</span>
                        Export as Excel
                    </button>
                    <button class="export-btn" onclick="exportReport('csv')">
                        <span class="export-icon">📝</span>
                        Export as CSV
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    // User Registrations Chart
    const userCtx = document.getElementById('userRegistrationsChart').getContext('2d');
    const userChart = new Chart(userCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'User Registrations',
                data: [12, 19, 15, 22, 18, 25],
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true
                }
            }
        }
    });

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Monthly Revenue',
                data: [12000, 19000, 15000, 22000, 18000, 25000],
                backgroundColor: '#27ae60',
                borderColor: '#27ae60',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    function exportReport(format) {
        alert(`Exporting report as ${format.toUpperCase()}...`);
        // In a real application, this would make an API call to generate the report
    }
    </script>

    <style>
    .reports-container {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 2rem;
    }

    .report-filters {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 2rem;
    }

    .filter-form .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr auto;
        gap: 1rem;
        align-items: end;
    }

    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .metric-card {
        background: #fff;
        padding: 1.5rem;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 1rem;
        border-left: 4px solid #3498db;
    }

    .metric-icon {
        font-size: 2rem;
    }

    .metric-info h3 {
        font-size: 1.8rem;
        margin: 0;
        color: #2c3e50;
    }

    .metric-info p {
        margin: 0;
        color: #666;
        font-weight: 500;
    }

    .charts-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .chart-card {
        background: #fff;
        padding: 1.5rem;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .chart-card h3 {
        margin-bottom: 1rem;
        color: #2c3e50;
    }

    .chart-container {
        height: 250px;
        position: relative;
    }

    .report-section {
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #eee;
    }

    .report-section:last-child {
        border-bottom: none;
    }

    .performance-table {
        width: 100%;
        border-collapse: collapse;
    }

    .performance-table th,
    .performance-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .performance-table th {
        background: #f8f9fa;
        font-weight: 600;
        color: #2c3e50;
    }

    .performance-bar {
        position: relative;
        background: #f0f0f0;
        border-radius: 10px;
        height: 20px;
        overflow: hidden;
    }

    .performance-fill {
        background: linear-gradient(90deg, #27ae60, #2ecc71);
        height: 100%;
        border-radius: 10px;
        transition: width 0.3s ease;
    }

    .performance-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 0.8rem;
        font-weight: bold;
        color: #2c3e50;
    }

    .export-options {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .export-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 1rem 1.5rem;
        background: #3498db;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .export-btn:hover {
        background: #2980b9;
        transform: translateY(-2px);
    }

    .export-icon {
        font-size: 1.2rem;
    }

    @media (max-width: 768px) {
        .filter-form .form-row {
            grid-template-columns: 1fr;
        }
        
        .charts-grid {
            grid-template-columns: 1fr;
        }
        
        .metrics-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .export-options {
            flex-direction: column;
        }
    }
    </style>

    <?php include '../includes/footer.php'; ?>
</body>
</html>