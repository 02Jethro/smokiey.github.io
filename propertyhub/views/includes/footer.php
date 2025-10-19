    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>PropertyHub</h3>
                    <p>Your trusted partner in real estate management and property transactions.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
                        <li><a href="<?php echo view_url('properties/list.php'); ?>">Properties</a></li>
                        <li><a href="<?php echo view_url('login.php'); ?>">Login</a></li>
                        <li><a href="<?php echo view_url('register.php'); ?>">Register</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <p>Email: info@propertyhub.com</p>
                    <p>Phone: +1 (555) 123-4567</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> PropertyHub. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="<?php echo asset_url('js/main.js'); ?>"></script>
</body>
</html>