<?php
// ==========================================================
// S PARFUM - ADMIN ORDERS MANAGEMENT
// ==========================================================

define('IN_ADMIN', true);
$page_title = "Manage Customer Orders | S Parfum Admin";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$db = get_db_connection();

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security token expired.');
        header('Location: orders.php');
        exit;
    }

    $orderId   = (int)($_POST['order_id'] ?? 0);
    $newStatus = trim($_POST['status'] ?? 'Processing');

    if (in_array($newStatus, ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'])) {
        $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $orderId]);
        set_flash('success', "Order #{$orderId} status updated to {$newStatus}.");
    }

    header('Location: orders.php');
    exit;
}

// Fetch all orders
$stmt = $db->query("SELECT * FROM orders ORDER BY id DESC");
$orders = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="page-header">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h1 class="page-title" style="text-align: left;">Customer Orders</h1>
                <p class="page-subtitle" style="text-align: left;">Track client orders, dispatch statuses, and print receipts</p>
            </div>
            <div>
                <a href="index.php" class="btn-secondary-action" style="margin-top: 0; width: auto; padding: 10px 18px;">
                    &larr; Admin Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container" style="margin-bottom: 80px;">
    <?php if (empty($orders)): ?>
        <div style="text-align: center; padding: 60px; background: #fff; border: 1px solid var(--gold-border); border-radius: var(--radius-md);">
            <i class="fa-solid fa-inbox text-gold" style="font-size: 40px; margin-bottom: 15px;"></i>
            <h3>No Orders Received Yet</h3>
            <p class="text-muted">Once clients complete checkout, their orders will appear here.</p>
        </div>
    <?php else: ?>
        <div class="cart-table-wrapper">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Client Details</th>
                        <th>Address</th>
                        <th>Payment</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $ord): ?>
                        <tr>
                            <td>
                                <strong><?= e($ord['order_number']) ?></strong>
                                <div style="font-size: 11px; color: var(--text-muted);"><?= date('M d, Y h:i A', strtotime($ord['created_at'])) ?></div>
                            </td>
                            <td>
                                <strong><?= e($ord['customer_name']) ?></strong>
                                <div style="font-size: 11px; color: var(--text-muted);"><?= e($ord['customer_email']) ?></div>
                                <div style="font-size: 11px; color: var(--text-muted);"><?= e($ord['customer_phone']) ?></div>
                            </td>
                            <td style="max-width: 220px; font-size: 12px; color: #5a5047;">
                                <?= e($ord['shipping_address']) ?>
                            </td>
                            <td>
                                <span style="font-size: 12px; font-weight: 500;"><?= e($ord['payment_method']) ?></span>
                            </td>
                            <td style="font-weight: 600; font-family: var(--font-heading); font-size: 1.2rem; color: var(--gold-dark);">
                                <?= format_price($ord['total_amount']) ?>
                            </td>
                            <td>
                                <form method="POST" action="orders.php" style="display: flex; gap: 5px;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <select name="status" class="form-control" style="width: auto; padding: 4px 8px; font-size: 11px;" onchange="this.form.submit()">
                                        <?php foreach (['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'] as $st): ?>
                                            <option value="<?= $st ?>" <?= $ord['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <a href="../receipt.php?order=<?= urlencode($ord['order_number']) ?>" target="_blank" class="btn-shop-now" style="padding: 6px 12px; font-size: 11px; display: inline-flex; align-items: center; gap: 4px;">
                                    <i class="fa-solid fa-print"></i> Invoice
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

