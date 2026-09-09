<?php
// ==========================================================
// S PARFUM - SECURE CHECKOUT & ORDER PROCESSING
// Web Development 1 Midterm Project
// ==========================================================

$page_title = "Checkout | S Parfum";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$cart = get_cart_details();

// If cart is empty, redirect to cart page
if (empty($cart['items'])) {
    set_flash('warning', 'Your shopping bag is empty. Please select a fragrance first.');
    header('Location: collection.php');
    exit;
}

// If stock issues exist, prevent checkout
if ($cart['has_stock_issue']) {
    set_flash('error', 'Please resolve inventory stock issues in your bag before completing checkout.');
    header('Location: cart.php');
    exit;
}

$currentUser = current_user();
$errors = [];

// Handle Order Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_order') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security validation failed. Please try submitting again.';
    }

    $customerName    = trim($_POST['customer_name'] ?? '');
    $customerEmail   = trim($_POST['customer_email'] ?? '');
    $customerPhone   = trim($_POST['customer_phone'] ?? '');
    $shippingAddress = trim($_POST['shipping_address'] ?? '');
    $paymentMethod   = trim($_POST['payment_method'] ?? 'Cash on Delivery');
    $orderNotes      = trim($_POST['notes'] ?? '');

    // Server-side validation
    if (empty($customerName) || strlen($customerName) < 2) {
        $errors[] = 'Please enter your full recipient name.';
    }
    if (empty($customerEmail) || !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address for your order confirmation.';
    }
    if (empty($customerPhone) || strlen($customerPhone) < 7) {
        $errors[] = 'Please enter a valid contact phone number.';
    }
    if (empty($shippingAddress) || strlen($shippingAddress) < 8) {
        $errors[] = 'Please enter a complete delivery address (street, city, province).';
    }

    if (empty($errors)) {
        $db = get_db_connection();

        try {
            // Begin Transaction for Stock Consistency
            $db->beginTransaction();

            // 1. Re-verify live stock inside transaction
            foreach ($cart['items'] as $item) {
                $checkStmt = $db->prepare("SELECT id, name, stock FROM products WHERE id = ? FOR UPDATE");
                $checkStmt->execute([$item['product_id']]);
                $prod = $checkStmt->fetch();

                if (!$prod || $prod['stock'] < $item['quantity']) {
                    $available = $prod ? $prod['stock'] : 0;
                    throw new Exception('Insufficient stock for "' . $item['name'] . '". Only ' . $available . ' available.');
                }
            }

            // 2. Generate unique order number
            $orderNumber = 'SP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
            $userId = $currentUser ? $currentUser['id'] : null;

            // 3. Insert Order record
            $orderStmt = $db->prepare("
                INSERT INTO orders (
                    order_number, user_id, customer_name, customer_email, 
                    customer_phone, shipping_address, payment_method, notes, 
                    subtotal, shipping_fee, total_amount, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Processing')
            ");
            $orderStmt->execute([
                $orderNumber,
                $userId,
                $customerName,
                $customerEmail,
                $customerPhone,
                $shippingAddress,
                $paymentMethod,
                $orderNotes,
                $cart['subtotal'],
                $cart['shipping'],
                $cart['total']
            ]);

            $orderId = (int)$db->lastInsertId();

            // 4. Insert Order Items & Atomically Deduct Stock
            $itemStmt = $db->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, price, quantity, total)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stockStmt = $db->prepare("
                UPDATE products SET stock = stock - ? WHERE id = ?
            ");

            foreach ($cart['items'] as $item) {
                // Insert line item
                $itemStmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['name'],
                    $item['price'],
                    $item['quantity'],
                    $item['line_total']
                ]);

                // Deduct stock
                $stockStmt->execute([
                    $item['quantity'],
                    $item['product_id']
                ]);
            }

            // Commit Transaction
            $db->commit();

            // Clear Shopping Bag
            cart_clear();

            set_flash('success', 'Thank you! Your order ' . $orderNumber . ' has been successfully confirmed.');
            header('Location: receipt.php?order=' . urlencode($orderNumber));
            exit;

        } catch (Exception $e) {
            $db->rollBack();
            $errors[] = 'Checkout failed: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="page-header">
    <div class="container">
        <h1 class="page-title">Checkout</h1>
        <p class="page-subtitle">Complete your fragrance acquisition with bespoke delivery</p>
    </div>
</div>

<div class="container" style="margin-bottom: 80px;">
    <?php if (!empty($errors)): ?>
        <div class="custom-alert alert-danger" style="margin-bottom: 25px;">
            <i class="fa-solid fa-circle-exclamation me-2"></i>
            <div>
                <?php foreach ($errors as $err): ?>
                    <div><?= e($err) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="cart-layout">
        <!-- Delivery & Payment Form -->
        <div class="form-card">
            <h3 style="font-size: 1.5rem; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 25px; padding-bottom: 12px; border-bottom: 1px solid var(--gold-border);">
                1. Delivery Information
            </h3>

            <form method="POST" action="checkout.php" id="checkoutForm">
                <input type="hidden" name="action" value="place_order">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                <div class="form-group">
                    <label class="form-label" for="customer_name">Recipient Full Name *</label>
                    <input type="text" id="customer_name" name="customer_name" class="form-control" required
                           value="<?= e($_POST['customer_name'] ?? ($currentUser['name'] ?? '')) ?>"
                           placeholder="e.g. Maria Santos">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label class="form-label" for="customer_email">Email Address *</label>
                        <input type="email" id="customer_email" name="customer_email" class="form-control" required
                               value="<?= e($_POST['customer_email'] ?? ($currentUser['email'] ?? '')) ?>"
                               placeholder="e.g. maria@gmail.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="customer_phone">Phone Number *</label>
                        <input type="tel" id="customer_phone" name="customer_phone" class="form-control" required
                               value="<?= e($_POST['customer_phone'] ?? ($currentUser['phone'] ?? '')) ?>"
                               placeholder="e.g. +63 917 123 4567">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="shipping_address">Complete Delivery Address *</label>
                    <textarea id="shipping_address" name="shipping_address" class="form-control" rows="3" required
                              placeholder="House / Unit No., Street, Barangay, City, Province, Postal Code"><?= e($_POST['shipping_address'] ?? ($currentUser['address'] ?? '')) ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="notes">Delivery Notes (Optional)</label>
                    <input type="text" id="notes" name="notes" class="form-control"
                           value="<?= e($_POST['notes'] ?? '') ?>"
                           placeholder="Special delivery instructions or gift note...">
                </div>

                <!-- Payment Method Section -->
                <h3 style="font-size: 1.5rem; letter-spacing: 2px; text-transform: uppercase; margin: 35px 0 20px; padding-bottom: 12px; border-bottom: 1px solid var(--gold-border);">
                    2. Select Payment Method
                </h3>

                <div class="payment-methods-grid">
                    <!-- COD -->
                    <label class="payment-option active">
                        <input type="radio" name="payment_method" value="Cash on Delivery" checked>
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                        <span>Cash on Delivery</span>
                    </label>

                    <!-- GCash -->
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="GCash / E-Wallet">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                        <span>GCash / Maya</span>
                    </label>

                    <!-- Card -->
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="Credit / Debit Card">
                        <i class="fa-regular fa-credit-card"></i>
                        <span>Credit / Debit Card</span>
                    </label>
                </div>

                <button type="submit" class="btn-primary-action" style="margin-top: 35px;">
                    <i class="fa-solid fa-lock me-2"></i> Confirm Order &bull; <?= format_price($cart['total']) ?>
                </button>
            </form>
        </div>

        <!-- Order Items Summary Column -->
        <div>
            <div class="summary-card">
                <h3 class="summary-title">Items in Order (<?= cart_total_items() ?>)</h3>
                
                <div style="max-height: 280px; overflow-y: auto; margin-bottom: 20px; padding-right: 5px;">
                    <?php foreach ($cart['items'] as $item): ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 15px; padding-bottom: 12px; border-bottom: 1px solid #f2eae0;">
                            <img src="<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>" style="width: 45px; height: 45px; object-fit: contain; background: #f8f3ed; border-radius: 4px; padding: 2px;">
                            <div style="flex-grow: 1;">
                                <div style="font-weight: 600; font-size: 13px;"><?= e($item['name']) ?></div>
                                <div style="font-size: 11px; color: var(--text-muted);"><?= $item['quantity'] ?> &times; <?= format_price($item['price']) ?></div>
                            </div>
                            <div style="font-weight: 600; font-size: 13px; color: var(--gold-dark);">
                                <?= format_price($item['line_total']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

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
                <div class="summary-row summary-total">
                    <span>Grand Total:</span>
                    <span><?= format_price($cart['total']) ?></span>
                </div>

                <div style="margin-top: 20px; padding: 15px; background: #fbf8f5; border-radius: var(--radius-sm); border: 1px solid #f0e6da; font-size: 12px; color: #6b5d52;">
                    <div style="font-weight: 600; color: var(--gold-dark); margin-bottom: 5px;">
                        <i class="fa-solid fa-feather me-1"></i> S Parfum Concierge Guarantee
                    </div>
                    Each bottle is carefully inspected, nestled in velvet, and accompanied by a numbered certificate of authenticity.
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

