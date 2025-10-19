<?php
require_once '../../config.php';
require_auth();
require_role([USER_TENANT]);

require_once '../../models/Payment.php';
require_once '../../models/Property.php';

$paymentModel = new Payment();
$propertyModel = new Property();

// Get properties that the tenant is actually renting
$properties = $paymentModel->getTenantProperties($_SESSION['user_id']);
$pendingPayments = $paymentModel->getPending($_SESSION['user_id'], 'tenant');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make Payment - PropertyHub Zimbabwe</title>
    <link rel="stylesheet" href="<?php echo asset_url('css/style.css'); ?>">
    <style>
    .payment-container {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 2rem;
        margin-top: 2rem;
    }

    .payment-summary {
        background: #fff;
        padding: 1.5rem;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        height: fit-content;
    }

    .payment-form-container {
        background: #fff;
        padding: 1.5rem;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .pending-payment {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        border-left: 4px solid #3498db;
    }

    .payment-amount {
        font-size: 1.5rem;
        font-weight: bold;
        color: #27ae60;
        margin: 0.5rem 0;
    }

    .no-payments {
        text-align: center;
        padding: 2rem;
        color: #666;
    }

    .payment-options {
        display: grid;
        gap: 1rem;
        margin: 1rem 0;
    }

    .payment-option {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .payment-option:hover {
        border-color: #3498db;
    }

    .payment-option input[type="radio"] {
        display: none;
    }

    .payment-option input[type="radio"]:checked + label {
        color: #3498db;
    }

    .payment-option input[type="radio"]:checked + label .payment-option-content {
        border-color: #3498db;
        background: #e8f4fd;
    }

    .payment-option label {
        display: block;
        cursor: pointer;
        margin: 0;
    }

    .payment-option-content {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border: 2px solid transparent;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .payment-option img {
        height: 30px;
        object-fit: contain;
    }

    .ecocash-fields {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        margin-top: 1rem;
        border: 1px solid #e9ecef;
    }

    .property-card-mini {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        border-left: 4px solid #3498db;
    }

    .property-card-mini h4 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
    }

    .property-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
        font-size: 0.9rem;
        color: #666;
    }

    .rent-amount {
        font-size: 1.2rem;
        font-weight: bold;
        color: #27ae60;
        text-align: center;
        margin-top: 0.5rem;
    }

    .no-properties {
        text-align: center;
        padding: 2rem;
        color: #666;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .btn-pay {
        width: 100%;
        padding: 1rem;
        font-size: 1.1rem;
        font-weight: bold;
    }

    .payment-instructions {
        background: #e8f4fd;
        padding: 1rem;
        border-radius: 8px;
        margin: 1rem 0;
        border-left: 4px solid #3498db;
    }

    .payment-instructions h4 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
    }

    .payment-instructions ul {
        margin: 0;
        padding-left: 1.5rem;
    }

    .payment-instructions li {
        margin-bottom: 0.5rem;
    }

    @media (max-width: 768px) {
        .payment-container {
            grid-template-columns: 1fr;
        }
        
        .payment-option-content {
            flex-direction: column;
            text-align: center;
        }
    }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-credit-card"></i> Make Rental Payment</h1>
            <p>Pay your rent securely using EcoCash or PayPal</p>
        </div>

        <div class="payment-container">
            <div class="payment-summary">
                <h3><i class="fas fa-clock"></i> Pending Payments</h3>
                <?php if (empty($pendingPayments)): ?>
                    <div class="no-payments">
                        <i class="fas fa-check-circle fa-2x" style="color: #27ae60;"></i>
                        <p>All payments are up to date!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($pendingPayments as $payment): ?>
                    <div class="pending-payment">
                        <div class="payment-info">
                            <h4><?php echo ucfirst($payment['payment_type']); ?> Payment</h4>
                            <p><i class="fas fa-calendar"></i> Due: <?php echo date('M j, Y', strtotime($payment['due_date'])); ?></p>
                            <p class="payment-amount">$<?php echo number_format($payment['amount'], 2); ?></p>
                            <p><small>Status: <span class="status-pending">Pending</span></small></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Your Rented Properties -->
                <div style="margin-top: 2rem;">
                    <h3><i class="fas fa-home"></i> Your Properties</h3>
                    <?php if (empty($properties)): ?>
                        <div class="no-properties">
                            <i class="fas fa-home fa-2x"></i>
                            <p>You are not currently renting any properties.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($properties as $property): ?>
                        <div class="property-card-mini">
                            <h4><?php echo htmlspecialchars($property['title']); ?></h4>
                            <div class="property-details">
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($property['city']); ?></span>
                                <span><i class="fas fa-bed"></i> <?php echo $property['bedrooms']; ?> beds</span>
                            </div>
                            <div class="rent-amount">
                                $<?php echo number_format($property['rent_amount'] ?? $property['price'], 2); ?>/month
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="payment-form-container">
                <div class="payment-form">
                    <h3><i class="fas fa-money-bill-wave"></i> New Payment</h3>
                    
                    <?php if (empty($properties)): ?>
                        <div class="no-properties">
                            <i class="fas fa-home fa-3x"></i>
                            <h3>No Rental Properties</h3>
                            <p>You need to be renting a property to make payments.</p>
                            <a href="<?php echo view_url('properties/list.php'); ?>" class="btn-primary">
                                <i class="fas fa-search"></i> Find Properties to Rent
                            </a>
                        </div>
                    <?php else: ?>
                        <form action="<?php echo BASE_URL; ?>controllers/PaymentController.php" method="POST" id="paymentForm">
                            <input type="hidden" name="action" value="initiate_payment">
                            
                            <div class="form-group">
                                <label><i class="fas fa-building"></i> Select Property</label>
                                <select name="property_id" required id="propertySelect">
                                    <option value="">Choose Property to Pay For</option>
                                    <?php foreach ($properties as $property): ?>
                                    <option value="<?php echo $property['id']; ?>" data-rent="<?php echo $property['rent_amount'] ?? $property['price']; ?>">
                                        <?php echo htmlspecialchars($property['title']); ?> - $<?php echo number_format($property['rent_amount'] ?? $property['price']); ?>/month
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-receipt"></i> Payment Type</label>
                                <select name="payment_type" required id="paymentType">
                                    <option value="rent">Monthly Rent</option>
                                    <option value="deposit">Security Deposit</option>
                                    <option value="maintenance">Maintenance Fee</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-dollar-sign"></i> Amount ($)</label>
                                <input type="number" name="amount" step="0.01" min="1" required 
                                       placeholder="Enter amount" id="amountInput">
                                <small>Enter the payment amount in US Dollars</small>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-calendar-day"></i> Due Date</label>
                                <input type="date" name="due_date" required 
                                       value="<?php echo date('Y-m-d', strtotime('+1 month')); ?>" id="dueDate">
                            </div>

                            <input type="hidden" name="landlord_id" id="landlordId" value="<?php echo $properties[0]['owner_id'] ?? ''; ?>">

                            <div class="payment-method">
                                <h4><i class="fas fa-mobile-alt"></i> Payment Method</h4>
                                
                                <div class="payment-instructions">
                                    <h4><i class="fas fa-info-circle"></i> Important Information</h4>
                                    <ul>
                                        <li>Payments are processed securely</li>
                                        <li>EcoCash: Enter your registered phone number</li>
                                        <li>PayPal: You'll be redirected to PayPal</li>
                                        <li>Receipts will be emailed to you</li>
                                    </ul>
                                </div>
                                
                                <div class="payment-options">
                                    <div class="payment-option">
                                        <input type="radio" name="payment_method" value="paypal" id="paypal" required>
                                        <label for="paypal">
                                            <div class="payment-option-content">
                                                <img src="<?php echo asset_url('images/paypal-logo.png'); ?>" alt="PayPal" width="80">
                                                <div>
                                                    <strong>Pay with PayPal</strong>
                                                    <p>Secure online payment</p>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                    
                                    <div class="payment-option">
                                        <input type="radio" name="payment_method" value="ecocash" id="ecocash">
                                        <label for="ecocash">
                                            <div class="payment-option-content">
                                                <img src="<?php echo asset_url('images/ecocash-logo.png'); ?>" alt="EcoCash" width="80">
                                                <div>
                                                    <strong>Pay with EcoCash</strong>
                                                    <p>Mobile money payment</p>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div class="ecocash-fields" id="ecocashFields" style="display: none;">
                                    <div class="form-group">
                                        <label><i class="fas fa-phone"></i> EcoCash Phone Number</label>
                                        <input type="tel" name="ecocash_phone" 
                                               placeholder="0771234567" 
                                               pattern="[0-9]{9,10}"
                                               id="ecocashPhone">
                                        <small>Enter your EcoCash registered phone number (e.g., 0771234567)</small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn-primary btn-pay" id="submitBtn">
                                    <i class="fas fa-lock"></i> Proceed to Secure Payment
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const propertySelect = document.getElementById('propertySelect');
        const amountInput = document.getElementById('amountInput');
        const paymentType = document.getElementById('paymentType');
        const ecocashFields = document.getElementById('ecocashFields');
        const ecocashPhone = document.getElementById('ecocashPhone');
        const dueDate = document.getElementById('dueDate');
        const submitBtn = document.getElementById('submitBtn');
        const paymentForm = document.getElementById('paymentForm');

        // Show/hide EcoCash phone field
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'ecocash') {
                    ecocashFields.style.display = 'block';
                    ecocashPhone.required = true;
                } else {
                    ecocashFields.style.display = 'none';
                    ecocashPhone.required = false;
                }
            });
        });

        // Auto-fill amount when property is selected
        propertySelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const rentAmount = selectedOption.dataset.rent;
            
            if (rentAmount && paymentType.value === 'rent') {
                amountInput.value = rentAmount;
            }
        });

        // Auto-fill amount when payment type changes to rent
        paymentType.addEventListener('change', function() {
            if (this.value === 'rent' && propertySelect.value) {
                const selectedOption = propertySelect.options[propertySelect.selectedIndex];
                const rentAmount = selectedOption.dataset.rent;
                if (rentAmount) {
                    amountInput.value = rentAmount;
                }
            }
        });

        // Set minimum due date to today
        const today = new Date().toISOString().split('T')[0];
        dueDate.min = today;

        // Form validation
        paymentForm.addEventListener('submit', function(e) {
            let isValid = true;
            const selectedPaymentMethod = document.querySelector('input[name="payment_method"]:checked');

            // Check if payment method is selected
            if (!selectedPaymentMethod) {
                alert('Please select a payment method');
                isValid = false;
            }

            // Validate EcoCash phone number
            if (selectedPaymentMethod && selectedPaymentMethod.value === 'ecocash') {
                const phone = ecocashPhone.value.trim();
                if (!phone.match(/^07[0-9]{8}$/)) {
                    alert('Please enter a valid EcoCash phone number (e.g., 0771234567)');
                    isValid = false;
                }
            }

            // Validate amount
            if (amountInput.value <= 0) {
                alert('Please enter a valid payment amount');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            } else {
                // Show loading state
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                submitBtn.disabled = true;
            }
        });

        // Format phone number input
        ecocashPhone.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.startsWith('0')) {
                value = value.substring(0, 10);
            }
            e.target.value = value;
        });
    });
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>