<?php
// ==========================================================
// S PARFUM - ADMIN DASHBOARD (admin/index.php)
// Purpose: Calculates key business metrics (revenue, orders,
// inventory) and displays the 5 most recent customer orders.
// ==========================================================

// Informs included templates that we are inside the /admin/ folder
define('IN_ADMIN', true);

$page_title = "Admin Dashboard | S Parfum";

// Include database connection, helper functions, and auth functions
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Access control: ensures only logged-in admins can view this page
require_admin();

// Get active PDO database connection
$db = get_db_connection();

// 1. Calculate Dashboard Metrics
// Total revenue: sums total_amount of non-cancelled orders
$totalSales = (float)$db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status != 'Cancelled'")->fetchColumn();

// Total orders: counts all rows in the orders table
$totalOrders = (int)$db->query("SELECT COUNT(*) FROM orders")->fetchColumn();

// Total products: counts all perfumes in the catalog
$totalProducts = (int)$db->query("SELECT COUNT(*) FROM products")->fetchColumn();

// Low stock count: counts perfumes with 5 or fewer bottles remaining
$lowStockCount = (int)$db->query("SELECT COUNT(*) FROM products WHERE stock <= 5")->fetchColumn();

// 2. Fetch recent orders: gets the 5 newest customer orders
$recentOrdersStmt = $db->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5");
$recentOrders = $recentOrdersStmt->fetchAll();

// 3. Fetch low-stock products to show in the restock alert banner
$lowStockStmt = $db->query("SELECT id, name, stock, price FROM products WHERE stock <= 5 ORDER BY stock ASC");
$lowStockProducts = $lowStockStmt->fetchAll();

// Load site header and navigation bar
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<!-- Page Header Banner (Title & Navigation to Inventory CRUD) -->
<div class="page-header">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h1 class="page-title" style="text-align: left;">S Parfum Management</h1>
                <p class="page-subtitle" style="text-align: left;">Overview of fragrance sales, inventory stocks, and customer orders</p>
            </div>
            <div>
                <!-- Direct link to product management (CRUD: Create, Read, Update, Delete) -->
                <a href="products.php" class="btn-primary-action" style="margin-top: 0; padding: 10px 20px; font-size: 15px; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-boxes-stacked"></i> Manage Inventory & Stocks
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container" style="margin-bottom: 80px;">
    <!-- Metric summary cards -->
    <div class="admin-stats-grid">
        <!-- Card 1: Total revenue -->
        <div class="stat-box">
            <div class="stat-icon"><i class="fa-solid fa-sack-dollar"></i></div>
            <div>
                <div class="stat-val"><?= format_price($totalSales) ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>

        <!-- Card 2: Total orders -->
        <div class="stat-box">
            <div class="stat-icon"><i class="fa-solid fa-cart-flatbed-suitcase"></i></div>
            <div>
                <div class="stat-val"><?= $totalOrders ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>

        <!-- Card 3: Perfumes in catalog -->
        <div class="stat-box">
            <div class="stat-icon"><i class="fa-solid fa-bottle-droplet"></i></div>
            <div>
                <div class="stat-val"><?= $totalProducts ?></div>
                <div class="stat-label">Perfumes in Catalog</div>
            </div>
        </div>

        <!-- Card 4: Low stock alert (turns red if items have stock <= 5) -->
        <div class="stat-box" style="<?= $lowStockCount > 0 ? 'border-color: #e57373;' : '' ?>">
            <div class="stat-icon" style="<?= $lowStockCount > 0 ? 'color: #c62828; background: #ffebee;' : '' ?>">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <div class="stat-val" style="<?= $lowStockCount > 0 ? 'color: #c62828;' : '' ?>"><?= $lowStockCount ?></div>
                <div class="stat-label">Low Stock Alerts</div>
            </div>
        </div>
    </div>

    <!-- Warning banner: shown only when items need restocking -->
    <?php if (!empty($lowStockProducts)): ?>
        <div class="custom-alert alert-warning" style="margin-bottom: 30px;">
            <div style="flex-grow: 1;">
                <strong><i class="fa-solid fa-triangle-exclamation me-1"></i> Inventory Attention Needed:</strong>
                The following perfumes have low or depleted stock:
                <?php foreach ($lowStockProducts as $lp): ?>
                    <span style="font-weight: 600; margin-left: 8px;"><?= e($lp['name']) ?> (<?= $lp['stock'] ?> left)</span>;
                <?php endforeach; ?>
            </div>
            <a href="products.php" class="btn-shop-now" style="background: #fff; padding: 6px 14px; font-size: 11px;">Restock Now</a>
        </div>
    <?php endif; ?>

    <!-- Recent orders table -->
    <div style="background: #fff; border: 1px solid var(--gold-border); border-radius: var(--radius-md); padding: 30px; box-shadow: var(--shadow-soft); margin-bottom: 40px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid var(--gold-border);">
            <h3 style="font-size: 1.4rem; text-transform: uppercase; letter-spacing: 2px;">Recent Orders</h3>
            <a href="orders.php" style="font-size: 12px; color: var(--gold-dark); font-weight: 600;">View All Orders &rarr;</a>
        </div>

        <?php if (empty($recentOrders)): ?>
            <p class="text-muted" style="text-align: center; padding: 20px;">No client orders recorded yet.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Payment</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Loop through each recent order -->
                        <?php foreach ($recentOrders as $ro): ?>
                            <tr>
                                <td><strong><?= e($ro['order_number']) ?></strong></td>
                                <td>
                                    <div><?= e($ro['customer_name']) ?></div>
                                    <div style="font-size: 11px; color: var(--text-muted);"><?= e($ro['customer_email']) ?></div>
                                </td>
                                <td><?= date('M d, Y', strtotime($ro['created_at'])) ?></td>
                                <td><?= e($ro['payment_method']) ?></td>
                                <td style="font-weight: 600; font-family: var(--font-heading); font-size: 1.1rem; color: var(--gold-dark);">
                                    <?= format_price($ro['total_amount']) ?>
                                </td>
                                <td>
                                    <span style="display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; background: #eef7ee; color: #2e6930;">
                                        <?= e($ro['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <!-- Link to printable order receipt -->
                                    <a href="../receipt.php?order=<?= urlencode($ro['order_number']) ?>" target="_blank" class="btn-shop-now" style="padding: 5px 10px; font-size: 10px;">
                                        Receipt
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Load site footer -->
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

