<?php
require_once '../../config.php';
require_auth();
require_role([USER_ADMIN]);

$page_title = "System Settings";
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
            <h1>System Settings</h1>
            <p>Configure system-wide settings and preferences</p>
        </div>

        <div class="settings-container">
            <div class="settings-tabs">
                <button class="tab-btn active" data-tab="general">General</button>
                <button class="tab-btn" data-tab="payment">Payment</button>
                <button class="tab-btn" data-tab="email">Email</button>
                <button class="tab-btn" data-tab="security">Security</button>
            </div>

            <div class="settings-content">
                <!-- General Settings -->
                <div id="general" class="tab-content active">
                    <form class="settings-form">
                        <div class="form-section">
                            <h3>General Settings</h3>
                            
                            <div class="form-group">
                                <label>Site Name</label>
                                <input type="text" name="site_name" value="PropertyHub" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Site Description</label>
                                <textarea name="site_description" rows="3">Your trusted partner in real estate management and property transactions.</textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Contact Email</label>
                                <input type="email" name="contact_email" value="info@propertyhub.com" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Contact Phone</label>
                                <input type="tel" name="contact_phone" value="+1 (555) 123-4567">
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Business Information</h3>
                            
                            <div class="form-group">
                                <label>Company Name</label>
                                <input type="text" name="company_name" value="PropertyHub Inc.">
                            </div>
                            
                            <div class="form-group">
                                <label>Address</label>
                                <textarea name="company_address" rows="2">123 Real Estate St, City, State, ZIP</textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Business Registration</label>
                                <input type="text" name="business_reg" placeholder="Business registration number">
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-primary">Save General Settings</button>
                        </div>
                    </form>
                </div>

                <!-- Payment Settings -->
                <div id="payment" class="tab-content">
                    <form class="settings-form">
                        <div class="form-section">
                            <h3>Payment Gateway Settings</h3>
                            
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="paypal_enabled" checked>
                                    <span>Enable PayPal Payments</span>
                                </label>
                            </div>
                            
                            <div class="form-group">
                                <label>PayPal Client ID</label>
                                <input type="text" name="paypal_client_id" placeholder="Your PayPal Client ID">
                            </div>
                            
                            <div class="form-group">
                                <label>PayPal Client Secret</label>
                                <input type="password" name="paypal_client_secret" placeholder="Your PayPal Client Secret">
                            </div>
                            
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="ecocash_enabled">
                                    <span>Enable EcoCash Payments</span>
                                </label>
                            </div>
                            
                            <div class="form-group">
                                <label>EcoCash Merchant Code</label>
                                <input type="text" name="ecocash_merchant_code" placeholder="Your EcoCash Merchant Code">
                            </div>
                            
                            <div class="form-group">
                                <label>EcoCash Merchant Key</label>
                                <input type="password" name="ecocash_merchant_key" placeholder="Your EcoCash Merchant Key">
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Payment Preferences</h3>
                            
                            <div class="form-group">
                                <label>Default Currency</label>
                                <select name="default_currency">
                                    <option value="USD" selected>US Dollar (USD)</option>
                                    <option value="EUR">Euro (EUR)</option>
                                    <option value="GBP">British Pound (GBP)</option>
                                    <option value="ZAR">South African Rand (ZAR)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Payment Timeout (minutes)</label>
                                <input type="number" name="payment_timeout" value="30" min="5" max="120">
                            </div>
                            
                            <div class="form-group">
                                <label>Auto-refund Failed Payments</label>
                                <select name="auto_refund">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-primary">Save Payment Settings</button>
                        </div>
                    </form>
                </div>

                <!-- Email Settings -->
                <div id="email" class="tab-content">
                    <form class="settings-form">
                        <div class="form-section">
                            <h3>Email Configuration</h3>
                            
                            <div class="form-group">
                                <label>SMTP Host</label>
                                <input type="text" name="smtp_host" placeholder="smtp.gmail.com">
                            </div>
                            
                            <div class="form-group">
                                <label>SMTP Port</label>
                                <input type="number" name="smtp_port" value="587">
                            </div>
                            
                            <div class="form-group">
                                <label>SMTP Username</label>
                                <input type="text" name="smtp_username" placeholder="your-email@gmail.com">
                            </div>
                            
                            <div class="form-group">
                                <label>SMTP Password</label>
                                <input type="password" name="smtp_password" placeholder="Your email password">
                            </div>
                            
                            <div class="form-group">
                                <label>From Email</label>
                                <input type="email" name="from_email" value="noreply@propertyhub.com">
                            </div>
                            
                            <div class="form-group">
                                <label>From Name</label>
                                <input type="text" name="from_name" value="PropertyHub">
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Email Templates</h3>
                            
                            <div class="form-group">
                                <label>User Registration Email</label>
                                <textarea name="registration_email" rows="4">
Welcome to PropertyHub!

Thank you for registering with us. Your account has been successfully created.

Best regards,
PropertyHub Team
                                </textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Payment Confirmation Email</label>
                                <textarea name="payment_email" rows="4">
Payment Confirmed!

Your payment of {amount} has been successfully processed.

Transaction ID: {transaction_id}
Payment Date: {payment_date}

Thank you for your business!
PropertyHub Team
                                </textarea>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-primary">Save Email Settings</button>
                        </div>
                    </form>
                </div>

                <!-- Security Settings -->
                <div id="security" class="tab-content">
                    <form class="settings-form">
                        <div class="form-section">
                            <h3>Security Settings</h3>
                            
                            <div class="form-group">
                                <label>Minimum Password Length</label>
                                <input type="number" name="min_password_length" value="8" min="6" max="20">
                            </div>
                            
                            <div class="form-group">
                                <label>Password Expiry (days)</label>
                                <input type="number" name="password_expiry" value="90" min="30" max="365">
                            </div>
                            
                            <div class="form-group">
                                <label>Max Login Attempts</label>
                                <input type="number" name="max_login_attempts" value="5" min="3" max="10">
                            </div>
                            
                            <div class="form-group">
                                <label>Session Timeout (minutes)</label>
                                <input type="number" name="session_timeout" value="60" min="15" max="480">
                            </div>
                            
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="two_factor_auth">
                                    <span>Enable Two-Factor Authentication</span>
                                </label>
                            </div>
                            
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="ip_whitelist">
                                    <span>Enable IP Whitelisting</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>API Security</h3>
                            
                            <div class="form-group">
                                <label>API Rate Limit (requests per minute)</label>
                                <input type="number" name="api_rate_limit" value="100" min="10" max="1000">
                            </div>
                            
                            <div class="form-group">
                                <label>JWT Token Expiry (hours)</label>
                                <input type="number" name="jwt_expiry" value="24" min="1" max="720">
                            </div>
                            
                            <div class="form-group">
                                <label>Allowed CORS Origins</label>
                                <textarea name="cors_origins" rows="3" placeholder="https://yourdomain.com&#10;https://app.yourdomain.com"></textarea>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-primary">Save Security Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Tab functionality
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all tabs and contents
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            const tabId = this.dataset.tab;
            document.getElementById(tabId).classList.add('active');
        });
    });

    // Settings form submission
    document.querySelectorAll('.settings-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            // Simulate saving settings
            const formData = new FormData(this);
            console.log('Saving settings:', Object.fromEntries(formData));
            
            // Show success message
            alert('Settings saved successfully!');
        });
    });
    </script>

    <style>
    .settings-container {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .settings-tabs {
        display: flex;
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
    }

    .tab-btn {
        padding: 1rem 2rem;
        background: none;
        border: none;
        border-bottom: 3px solid transparent;
        cursor: pointer;
        font-weight: 500;
        color: #666;
        transition: all 0.3s ease;
    }

    .tab-btn:hover {
        background: #e9ecef;
        color: #333;
    }

    .tab-btn.active {
        border-bottom-color: #3498db;
        color: #3498db;
        background: #fff;
    }

    .settings-content {
        padding: 2rem;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .settings-form .form-section {
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #eee;
    }

    .settings-form .form-section:last-child {
        border-bottom: none;
    }

    .settings-form .form-section h3 {
        margin-bottom: 1.5rem;
        color: #2c3e50;
        border-bottom: 2px solid #3498db;
        padding-bottom: 0.5rem;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        font-weight: normal;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #eee;
    }

    @media (max-width: 768px) {
        .settings-tabs {
            flex-direction: column;
        }
        
        .tab-btn {
            border-bottom: 1px solid #e9ecef;
            border-right: 3px solid transparent;
        }
        
        .tab-btn.active {
            border-right-color: #3498db;
            border-bottom-color: #e9ecef;
        }
        
        .settings-content {
            padding: 1rem;
        }
    }
    </style>

    <?php include '../includes/footer.php'; ?>
</body>
</html>