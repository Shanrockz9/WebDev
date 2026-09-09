<?php
// ==========================================================
// Site Footer Template
// Matches PDF Mockup: Quick Links, Customer Service, Contacts, Dumaguete Philippines
// ==========================================================

$root = defined('IN_ADMIN') ? '../' : '';
$asset_path = defined('IN_ADMIN') ? '../assets/' : 'assets/';
?>
<footer class="site-footer" id="contact">
    <div class="container">
        <div class="footer-grid">
            <!-- Brand Column -->
            <div class="footer-brand">
                <div class="brand-logo" style="margin-bottom: 15px;">
                    <img src="<?= $root ?>assets/images/SParfumLogo.png" alt="S Parfum" class="brand-logo-img">
                    <span class="brand-text">S Parfum</span>
                </div>
                <p class="footer-tagline">"Elegance, Captured in a scent."</p>
                <p style="font-size: 13px; color: #6b5d52; line-height: 1.6;">
                    Crafted to express individuality, evoke confidence, and leave a lasting impression of timeless beauty.
                </p>
                <div class="social-links">
                    <a href="#" class="social-icon" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="social-icon" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="social-icon" title="Pinterest"><i class="fa-brands fa-pinterest-p"></i></a>
                    <a href="#" class="social-icon" title="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="footer-heading">Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="<?= $root ?>index.php">Home</a></li>
                    <li><a href="<?= $root ?>collection.php">Collection</a></li>
                    <li><a href="<?= $root ?>index.php#story">Our Story</a></li>
                    <li><a href="<?= $root ?>index.php#experience">Experience</a></li>
                    <li><a href="<?= $root ?>index.php#contact">Contact</a></li>
                </ul>
            </div>

            <!-- Customer Service -->
            <div>
                <h4 class="footer-heading">Customer Service</h4>
                <ul class="footer-links">
                    <li><a href="#">FAQs</a></li>
                    <li><a href="#">Shipping & Delivery</a></li>
                    <li><a href="#">Returns & Exchanges</a></li>
                    <li><a href="#">Warranty</a></li>
                    <li><a href="<?= $root ?>orders.php">Track Order</a></li>
                </ul>
            </div>

            <!-- Contacts -->
            <div>
                <h4 class="footer-heading">Contacts</h4>
                <div class="footer-contact-item">
                    <i class="fa-solid fa-phone"></i>
                    <span>+63 912 345 6789</span>
                </div>
                <div class="footer-contact-item">
                    <i class="fa-solid fa-envelope"></i>
                    <span>Sparfums@gmail.com</span>
                </div>
                <div class="footer-contact-item">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Dumaguete, Philippines</span>
                </div>
                <div style="margin-top: 15px; font-size: 12px; color: #73675c;">
                    <i class="fa-regular fa-clock me-1"></i> Mon - Sat: 9:00 AM - 8:00 PM
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div>Elegance, Captured in a scent.</div>
            <div>&copy; 2026 S PARFUM. All rights reserved.</div>
        </div>
    </div>
</footer>

<!-- Interactive Scripts -->
<script src="<?= $asset_path ?>js/main.js"></script>
</body>
</html>

