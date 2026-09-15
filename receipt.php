<?php
// ==========================================================
// S PARFUM - PRINTABLE RECEIPT & INVOICE (receipt.php)
// Purpose: Displays the official sales invoice for an order
// with itemized breakdown and print-optimized (@media print) layout.
// ==========================================================

$page_title = "Official Order Receipt | S Parfum";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// 1. Retrieve order reference from query parameters
$orderNumber = trim($_GET['order'] ?? '');
$orderId     = (int)($_GET['id'] ?? 0);

if (empty($orderNumber) && $orderId <= 0) {
    set_flash('error', 'Receipt identifier is missing.');
    header('Location: index.php');
    exit;
}

$db = get_db_connection();

// 2. Look up the order record
if (!empty($orderNumber)) {
    $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ?");
    $stmt->execute([$orderNumber]);
} else {
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
}

$order = $stmt->fetch();

if (!$order) {
    set_flash('error', 'The requested order receipt was not found.');
    header('Location: index.php');
    exit;
}

// 3. Fetch all purchased line items for this order
$itemStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC");
$itemStmt->execute([$order['id']]);
$items = $itemStmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container" style="padding: 40px 20px;">
    <!-- Top Actions Toolbar (Hidden on Print) -->
    <div class="receipt-actions" style="margin-bottom: 25px;">
        <button type="button" class="btn-print" onclick="window.print();">
            <i class="fa-solid fa-print"></i> Print Official Receipt
        </button>
        <a href="collection.php" class="btn-secondary-action" style="width: auto; padding: 12px 24px; margin-top: 0;">
            <i class="fa-solid fa-bag-shopping me-1"></i> Continue Shopping
        </a>
        <a href="orders.php" class="btn-secondary-action" style="width: auto; padding: 12px 24px; margin-top: 0; border-color: var(--gold-primary); color: var(--gold-dark);">
            <i class="fa-solid fa-receipt me-1"></i> My Orders
        </a>
    </div>

    <!-- Official Printable Receipt Container -->
    <div class="receipt-wrapper" id="receiptContent">
        <!-- Receipt Header -->
        <div class="receipt-header">
            <div class="brand-logo" style="justify-content: center; margin-bottom: 8px;">
                <img src="assets/images/SParfumLogo.png" alt="S Parfum" class="brand-logo-img" style="height: 48px;">
                <span class="brand-text" style="font-size: 2.2rem;">S Parfum</span>
            </div>
            <div style="font-family: var(--font-heading); font-style: italic; font-size: 1.1rem; color: #55493f; margin-bottom: 6px;">
                The Essence of Elegance &bull; Official Sales Invoice
            </div>
            <div style="font-size: 15px; color: #786d63;">
                S Parfum, Dumaguete City, Negros Oriental, Philippines &bull; +63 912 345 6789 &bull; Sparfums@gmail.com
            </div>
        </div>

        <!-- Receipt Metadata Grid -->
        <div class="receipt-meta">
            <div class="receipt-meta-box">
                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--gold-dark); font-weight: 700; margin-bottom: 6px;">
                    Order Details
                </div>
                <div><strong>Order Number:</strong> <?= e($order['order_number']) ?></div>
                <div><strong>Order Date:</strong> <?= date('F d, Y - h:i A', strtotime($order['created_at'])) ?></div>
                <div><strong>Payment Method:</strong> <?= e($order['payment_method']) ?></div>
                <div><strong>Status:</strong> <span style="font-weight: 600; color: #2e6930;"><?= e($order['status']) ?></span></div>
            </div>

            <div class="receipt-meta-box">
                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--gold-dark); font-weight: 700; margin-bottom: 6px;">
                    Recipient & Delivery
                </div>
                <div><strong>Name:</strong> <?= e($order['customer_name']) ?></div>
                <div><strong>Email:</strong> <?= e($order['customer_email']) ?></div>
                <div><strong>Phone:</strong> <?= e($order['customer_phone']) ?></div>
                <div><strong>Address:</strong> <?= nl2br(e($order['shipping_address'])) ?></div>
                <?php if (!empty($order['delivery_date'])): ?>
                    <div style="margin-top: 4px; color: var(--gold-dark);">
                        <strong><i class="fa-regular fa-calendar-check me-1"></i> Scheduled Delivery:</strong> 
                        <?= date('F d, Y', strtotime($order['delivery_date'])) ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($order['notes'])): ?>
                    <div style="margin-top: 4px;"><strong>Notes:</strong> <?= e($order['notes']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Itemized Table -->
        <table class="receipt-table">
            <thead>
                <tr>
                    <th style="text-align: left;">Item Description</th>
                    <th style="text-align: right; width: 120px;">Unit Price</th>
                    <th style="text-align: center; width: 80px;">Qty</th>
                    <th style="text-align: right; width: 130px;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <strong style="font-family: var(--font-heading); font-size: 1.1rem;"><?= e($item['product_name']) ?></strong>
                            <div style="font-size: 11px; color: #7a7066;">Artisanal Haute Parfumerie (50ml)</div>
                        </td>
                        <td style="text-align: right;"><?= format_price($item['price']) ?></td>
                        <td style="text-align: center; font-weight: 600;"><?= $item['quantity'] ?></td>
                        <td style="text-align: right; font-weight: 600; font-family: var(--font-heading); font-size: 1.1rem; color: var(--gold-dark);">
                            <?= format_price($item['total']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totals Breakdown -->
        <div class="receipt-totals">
            <div class="summary-row">
                <span class="text-muted">Subtotal:</span>
                <span style="font-weight: 600;"><?= format_price($order['subtotal']) ?></span>
            </div>
            <div class="summary-row">
                <span class="text-muted">Shipping Fee:</span>
                <span>
                    <?= $order['shipping_fee'] == 0 ? '<span style="color: #2e6930; font-weight: 600;">FREE</span>' : format_price($order['shipping_fee']) ?>
                </span>
            </div>
            <div class="summary-row summary-total" style="font-size: 1.4rem;">
                <span>Total Paid:</span>
                <span style="color: var(--gold-dark);"><?= format_price($order['total_amount']) ?></span>
            </div>
        </div>

        <!-- Receipt Watermark & Official Note -->
        <div style="border-top: 1px dashed var(--gold-border); padding-top: 20px; text-align: center; font-size: 12px; color: #7a7066;">
            <p style="font-family: var(--font-heading); font-style: italic; font-size: 1.15rem; color: #4a3e35; margin-bottom: 5px;">
                "Thank you for welcoming S Parfum into your sacred moments of beauty."
            </p>
            <p>This is a computer-generated electronic invoice and valid receipt for your records.</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

