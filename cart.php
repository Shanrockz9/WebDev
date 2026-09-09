<?php
// ==========================================================
// S PARFUM - SHOPPING CART
// ==========================================================

$page_title = "Your Shopping Bag | S Parfum";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Handle Cart Actions (update, remove, clear)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security token expired. Please reload.');
        header('Location: cart.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'update_qty') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty       = (int)($_POST['quantity'] ?? 1);
        $res = cart_update_item($productId, $qty);
        if ($res['success']) {
            set_flash('success', $res['message']);
        } else {
            set_flash('warning', $res['message']);
        }
    } elseif ($action === 'remove_item') {
        $productId = (int)($_POST['product_id'] ?? 0);
        cart_remove_item($productId);
        set_flash('info', 'Fragrance removed from your bag.');
    } elseif ($action === 'clear_cart') {
        cart_clear();
        set_flash('info', 'Your shopping bag has been cleared.');
    }

    header('Location: cart.php');
    exit;
}

$cart = get_cart_details();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="page-header">
    <div class="container">
        <h1 class="page-title">Your Shopping Bag</h1>
        <p class="page-subtitle">Review your selected luxury fragrances before proceeding to checkout</p>
    </div>
</div>

<div class="container" style="margin-bottom: 80px;">
    <?php if (empty($cart['items'])): ?>
        <div style="text-align: center; padding: 70px 20px; background: #fff; border: 1px solid var(--gold-border); border-radius: var(--radius-md); box-shadow: var(--shadow-soft);">
            <div class="scent-icon-wrapper" style="margin-bottom: 20px;">
                <i class="fa-solid fa-bag-shopping"></i>
            </div>
            <h2 style="font-size: 2rem; margin-bottom: 10px;">Your Bag is Empty</h2>
            <p class="text-muted" style="max-width: 480px; margin: 0 auto 30px;">
                Discover our curated perfume collection and wrap yourself in timeless elegance.
            </p>
            <a href="collection.php" class="hero-cta-btn">
                Discover The Collection
            </a>
        </div>
    <?php else: ?>

        <?php if ($cart['has_stock_issue']): ?>
            <div class="custom-alert alert-danger" style="margin-bottom: 25px;">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                <span>One or more items in your cart exceed available stock. Please adjust quantities before checkout.</span>
            </div>
        <?php endif; ?>

        <div class="cart-layout">
            <!-- Cart Items Table -->
            <div class="cart-table-wrapper">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Fragrance</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart['items'] as $item): ?>
                            <tr>
                                <td>
                                    <div class="cart-item-info">
                                        <img src="<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>" class="cart-item-thumb">
                                        <div>
                                            <a href="product-detail.php?id=<?= $item['product_id'] ?>" class="cart-item-name">
                                                <?= e($item['name']) ?>
                                            </a>
                                            <div class="cart-item-subtitle"><?= e($item['subtitle']) ?></div>
                                            <?php if ($item['out_of_stock']): ?>
                                                <span class="stock-badge stock-out" style="display: inline-block; margin-top: 5px;">Out of Stock</span>
                                            <?php elseif ($item['over_stock']): ?>
                                                <span class="stock-badge stock-low" style="display: inline-block; margin-top: 5px;">Only <?= $item['stock'] ?> in stock</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 500;"><?= format_price($item['price']) ?></div>
                                </td>
                                <td>
                                    <!-- Update Quantity Form -->
                                    <form method="POST" action="cart.php" style="display: inline-flex; align-items: center; gap: 6px;">
                                        <input type="hidden" name="action" value="update_qty">
                                        <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        
                                        <div class="qty-control">
                                            <button type="submit" name="quantity" value="<?= max(1, $item['quantity'] - 1) ?>" class="qty-btn" title="Decrease">-</button>
                                            <span class="qty-input" style="display: flex; align-items: center; justify-content: center;"><?= $item['quantity'] ?></span>
                                            <button type="submit" name="quantity" value="<?= $item['quantity'] + 1 ?>" class="qty-btn" title="Increase" <?= $item['quantity'] >= $item['stock'] ? 'disabled' : '' ?>>+</button>
                                        </div>
                                    </form>
                                </td>
                                <td>
                                    <div style="font-weight: 600; font-family: var(--font-heading); font-size: 1.2rem; color: var(--gold-dark);">
                                        <?= format_price($item['line_total']) ?>
                                    </div>
                                </td>
                                <td>
                                    <!-- Remove Item Form -->
                                    <form method="POST" action="cart.php" onsubmit="return confirm('Remove <?= e($item['name']) ?> from your bag?');">
                                        <input type="hidden" name="action" value="remove_item">
                                        <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <button type="submit" class="btn-remove-item" title="Remove item">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div style="padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; background: #faf5ee; border-top: 1px solid var(--gold-border);">
                    <a href="collection.php" style="font-size: 13px; font-weight: 600; color: var(--text-main);">
                        <i class="fa-solid fa-arrow-left me-1"></i> Continue Shopping
                    </a>
                    <form method="POST" action="cart.php" onsubmit="return confirm('Are you sure you want to empty your bag?');">
                        <input type="hidden" name="action" value="clear_cart">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <button type="submit" style="background: none; border: none; font-size: 12px; color: #a33b3b; cursor: pointer; text-decoration: underline;">
                            Empty Bag
                        </button>
                    </form>
                </div>
            </div>

            <!-- Order Summary Card -->
            <div class="summary-card">
                <h3 class="summary-title">Order Summary</h3>
                <div class="summary-row">
                    <span class="text-muted">Subtotal:</span>
                    <span style="font-weight: 600;"><?= format_price($cart['subtotal']) ?></span>
                </div>
                <div class="summary-row">
                    <span class="text-muted">Shipping Fee:</span>
                    <span>
                        <?php if ($cart['shipping'] == 0): ?>
                            <span style="color: #276729; font-weight: 600;">FREE</span>
                        <?php else: ?>
                            <?= format_price($cart['shipping']) ?>
                        <?php endif; ?>
                    </span>
                </div>
                <?php if ($cart['subtotal'] < 5000): ?>
                    <p style="font-size: 11px; color: var(--gold-dark); margin-bottom: 15px;">
                        <i class="fa-solid fa-circle-info me-1"></i> Add <?= format_price(5000 - $cart['subtotal']) ?> more to qualify for <strong>FREE Shipping</strong>!
                    </p>
                <?php endif; ?>

                <div class="summary-row summary-total">
                    <span>Estimated Total:</span>
                    <span><?= format_price($cart['total']) ?></span>
                </div>

                <a href="checkout.php" class="btn-primary-action <?= $cart['has_stock_issue'] ? 'disabled' : '' ?>" style="<?= $cart['has_stock_issue'] ? 'pointer-events: none; opacity: 0.6;' : '' ?>">
                    Proceed to Checkout <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>

                <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #f2e9de; font-size: 12px; color: var(--text-muted); text-align: center;">
                    <i class="fa-solid fa-lock text-gold me-1"></i> Encrypted & Secure Checkout
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

