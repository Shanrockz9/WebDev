<?php
// ==========================================================
// S PARFUM - SITE NAVIGATION HEADER (includes/navbar.php)
// Purpose: Displays the brand logo, navigation links, user
// account dropdown, shopping bag counter, and flash alerts.
// ==========================================================

// Get dynamic cart count and currently authenticated user
$cart_count = cart_total_items();
$currentUser = current_user();

// Adjust path prefix if inside /admin/
$root = defined('IN_ADMIN') ? '../' : '';
?>
<header class="site-header">
    <div class="container">
        <div class="nav-container">
            <!-- Brand Logo with SParfum Logo -->
            <a href="<?= $root ?>index.php" class="brand-logo">
                <img src="<?= $root ?>assets/images/SParfumLogo.png" alt="S Parfum" class="brand-logo-img">
                <span class="brand-text">S Parfum</span>
            </a>

            <!-- Navigation Links -->
            <nav>
                <ul class="nav-links">
                    <li><a href="<?= $root ?>index.php" class="nav-link">Home</a></li>
                    <li><a href="<?= $root ?>collection.php" class="nav-link">Collection</a></li>
                    <li><a href="<?= $root ?>index.php#story" class="nav-link">Our Story</a></li>
                    <li><a href="<?= $root ?>index.php#experience" class="nav-link">Experience</a></li>
                    <li><a href="<?= $root ?>index.php#contact" class="nav-link">Contact</a></li>
                </ul>
            </nav>

            <!-- Header Right: User Account & Cart -->
            <div class="nav-actions">
                <!-- User Account Dropdown -->
                <div class="user-dropdown-container">
                    <button type="button" class="action-icon-btn" id="userDropdownBtn" title="Account">
                        <i class="fa-regular fa-user"></i>
                    </button>
                    <div class="user-dropdown-menu">
                        <?php if ($currentUser): ?>
                            <div class="dropdown-header-info">
                                <strong><?= e($currentUser['name']) ?></strong>
                                <span class="text-muted"><?= e($currentUser['email']) ?></span>
                                <?php if ($currentUser['role'] === 'admin'): ?>
                                    <div class="mt-1"><span class="admin-badge">Administrator</span></div>
                                <?php endif; ?>
                            </div>
                            <a href="<?= $root ?>orders.php" class="user-dropdown-item">
                                <i class="fa-solid fa-receipt"></i> My Orders
                            </a>
                            <?php if ($currentUser['role'] === 'admin'): ?>
                                <a href="<?= $root ?>admin/index.php" class="user-dropdown-item">
                                    <i class="fa-solid fa-chart-line"></i> Admin Dashboard
                                </a>
                                <a href="<?= $root ?>admin/products.php" class="user-dropdown-item">
                                    <i class="fa-solid fa-boxes-stacked"></i> Inventory & Stocks
                                </a>
                            <?php endif; ?>
                            <a href="<?= $root ?>logout.php" class="user-dropdown-item text-danger">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i> Sign Out
                            </a>
                        <?php else: ?>
                            <div class="dropdown-header-info">
                                <strong>Welcome to S Parfum</strong>
                                <span class="text-muted">Sign in to save your cart & track orders</span>
                            </div>
                            <a href="<?= $root ?>login.php" class="user-dropdown-item">
                                <i class="fa-solid fa-arrow-right-to-bracket"></i> Sign In
                            </a>
                            <a href="<?= $root ?>register.php" class="user-dropdown-item">
                                <i class="fa-solid fa-user-plus"></i> Create Account
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Shopping Bag / Cart -->
                <a href="<?= $root ?>cart.php" class="action-icon-btn" title="Shopping Bag">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-badge"><?= $cart_count ?></span>
                    <?php endif; ?>
                </a>

                <!-- Mobile Hamburger Toggle -->
                <button type="button" class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle navigation">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>
    </div>
</header>

<!-- Global Flash Messaging -->
<?php render_flash(); ?>

