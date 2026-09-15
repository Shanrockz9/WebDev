<?php
// ==========================================================
// S PARFUM - MY ORDERS & ORDER TRACKER (orders.php)
// Purpose: Allows customers to view their previous orders
// and track any order by entering an order reference number.
// ==========================================================

$page_title = "My Orders & Receipts | S Parfum";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$db = get_db_connection();
$currentUser = current_user();
$orders = [];
$searchOrderNumber = trim($_GET['search_order'] ?? '');

// 1. Search by specific order number if provided in the search input
if (!empty($searchOrderNumber)) {
    $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ?");
    $stmt->execute([$searchOrderNumber]);
    $found = $stmt->fetch();
    if ($found) {
        $orders = [$found];
    } else {
        set_flash('warning', 'No order found with number: ' . e($searchOrderNumber));
    }
// 2. Otherwise, load all orders matching the logged-in user
} elseif ($currentUser) {
    $stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? OR customer_email = ? ORDER BY id DESC");
    $stmt->execute([$currentUser['id'], $currentUser['email']]);
    $orders = $stmt->fetchAll();
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="page-header">
    <div class="container">
        <h1 class="page-title">Order History & Receipts</h1>
        <p class="page-subtitle">Track your bespoke perfume orders and print official receipts</p>
    </div>
</div>

<div class="container" style="margin-bottom: 80px;">
    <!-- Order Number Quick Search Bar -->
    <div style="background: #fff; border: 1px solid var(--gold-border); border-radius: var(--radius-md); padding: 25px; margin-bottom: 35px; box-shadow: var(--shadow-soft);">
        <form method="GET" action="orders.php" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 1; min-width: 250px;">
                <label class="form-label" for="search_order">Track Specific Order by Order Number</label>
                <input type="text" id="search_order" name="search_order" class="form-control" 
                       placeholder="e.g. SP-20260909-ABCD" value="<?= e($searchOrderNumber) ?>">
            </div>
            <button type="submit" class="btn-primary-action" style="width: auto; padding: 12px 24px; margin-top: 0;">
                <i class="fa-solid fa-magnifying-glass me-1"></i> Look Up Order
            </button>
            <?php if (!empty($searchOrderNumber)): ?>
                <a href="orders.php" class="btn-secondary-action" style="width: auto; padding: 12px 20px; margin-top: 0;">
                    Show All
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Orders List -->
    <?php if (empty($orders)): ?>
        <div style="text-align: center; padding: 60px 20px; background: #fff; border: 1px solid var(--gold-border); border-radius: var(--radius-md);">
            <i class="fa-solid fa-receipt text-gold" style="font-size: 40px; margin-bottom: 15px;"></i>
            <h3>No Orders Found</h3>
            <p class="text-muted" style="margin-bottom: 25px;">
                <?= $currentUser ? "You haven't placed any orders with this account yet." : "Please sign in or enter an order number above to view your order receipt." ?>
            </p>
            <?php if (!$currentUser): ?>
                <a href="login.php" class="hero-cta-btn">Sign In to Your Account</a>
            <?php else: ?>
                <a href="collection.php" class="hero-cta-btn">Discover The Collection</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 25px;">
            <?php foreach ($orders as $ord): ?>
                <?php
                    // Fetch items for this order
                    $itemsStmt = $db->prepare("SELECT product_name, quantity, total FROM order_items WHERE order_id = ?");
                    $itemsStmt->execute([$ord['id']]);
                    $orderItems = $itemsStmt->fetchAll();
                ?>
                <div style="background: #fff; border: 1px solid var(--gold-border); border-radius: var(--radius-md); padding: 25px; box-shadow: var(--shadow-soft);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f0e6da;">
                        <div>
                            <span style="font-size: 11px; letter-spacing: 1px; text-transform: uppercase; color: var(--gold-dark); font-weight: 700;">
                                Order Reference
                            </span>
                            <h3 style="font-size: 1.3rem; margin-top: 2px;"><?= e($ord['order_number']) ?></h3>
                            <div style="font-size: 12px; color: var(--text-muted);">
                                Placed on <?= date('M d, Y \a\t h:i A', strtotime($ord['created_at'])) ?>
                            </div>
                        </div>

                        <div style="text-align: right;">
                            <span style="display: inline-block; padding: 4px 12px; background: #eaf6ea; color: #276729; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; border: 1px solid #b7e3b8;">
                                <?= e($ord['status']) ?>
                            </span>
                            <div style="font-family: var(--font-heading); font-size: 1.5rem; font-weight: 600; color: var(--gold-dark); margin-top: 5px;">
                                <?= format_price($ord['total_amount']) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Line items list -->
                    <div style="margin-bottom: 20px; font-size: 13px;">
                        <strong>Fragrances:</strong>
                        <ul style="margin: 6px 0 0 20px; color: #4e443d;">
                            <?php foreach ($orderItems as $oi): ?>
                                <li><?= e($oi['product_name']) ?> &times; <?= $oi['quantity'] ?> (<?= format_price($oi['total']) ?>)</li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; padding-top: 15px; border-top: 1px solid #f4eee7;">
                        <div style="font-size: 12px; color: var(--text-muted);">
                            <div><i class="fa-solid fa-location-dot text-gold me-1"></i> Delivery to: <?= e($ord['customer_name']) ?> (<?= e($ord['payment_method']) ?>)</div>
                            <?php if (!empty($ord['delivery_date'])): ?>
                                <div style="margin-top: 4px; color: var(--gold-dark); font-weight: 600;">
                                    <i class="fa-regular fa-calendar-check me-1"></i> Scheduled Arrival: <?= date('M d, Y', strtotime($ord['delivery_date'])) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <a href="receipt.php?order=<?= urlencode($ord['order_number']) ?>" class="btn-shop-now" style="padding: 8px 18px; font-size: 11px; display: inline-flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-print"></i> View & Print Receipt
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

