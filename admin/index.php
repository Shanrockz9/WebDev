<?php
// ==========================================================
// S PARFUM - ADMIN DASHBOARD
// ==========================================================

define('IN_ADMIN', true);
$page_title = "Admin Dashboard | S Parfum";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$db = get_db_connection();

// 1. Calculate metrics
$totalSales = (float)$db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status != 'Cancelled'")->fetchColumn();
$totalOrders = (int)$db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalProducts = (int)$db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$lowStockCount = (int)$db->query("SELECT COUNT(*) FROM products WHERE stock <= 5")->fetchColumn();

// 2. Fetch recent orders
$recentOrdersStmt = $db->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5");
$recentOrders = $recentOrdersStmt->fetchAll();

// 3. Fetch low stock items for alert
$lowStockStmt = $db->query("SELECT id, name, stock, price FROM products WHERE stock <= 5 ORDER BY stock ASC");
$lowStockProducts = $lowStockStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="page-header">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h1 class="page-title" style="text-align: left;">S Parfum Management</h1>
                <p class="page-subtitle" style="text-align: left;">Overview of fragrance sales, inventory stocks, and customer orders</p>
            </div>
            <div>
                <a href="products.php" class="btn-primary-action" style="margin-top: 0; padding: 10px 20px; font-size: 15px; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-boxes-stacked"></i> Manage Inventory & Stocks
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container" style="margin-bottom: 80px;">
    <!-- Metric Cards Grid -->
    <div class="admin-stats-grid">
        <div class="stat-box">
            <div class="stat-icon"><i class="fa-solid fa-sack-dollar"></i></div>
            <div>
                <div class="stat-val"><?= format_price($totalSales) ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-icon"><i class="fa-solid fa-cart-flatbed-suitcase"></i></div>
            <div>
                <div class="stat-val"><?= $totalOrders ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-icon"><i class="fa-solid fa-bottle-droplet"></i></div>
            <div>
                <div class="stat-val"><?= $totalProducts ?></div>
                <div class="stat-label">Perfumes in Catalog</div>
            </div>
        </div>

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

    <!-- Low Stock Warning Banner -->
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

    <!-- Recent Orders Section -->
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

