<?php
// ==========================================================
// S PARFUM - HELPER & UTILITY FUNCTIONS (includes/functions.php)
// Purpose: Provides reusable utility functions for input
// sanitization, formatting, flash messages, CSRF, and cart.
// ==========================================================

require_once __DIR__ . '/../config/db.php';

/**
 * e()
 * Escapes special characters to HTML entities to prevent XSS (Cross-Site Scripting).
 */
function e(?string $string): string {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

/**
 * format_price()
 * Formats a number into Philippine Peso currency format (e.g., ₱3,500.00).
 */
function format_price(float|int|string $amount): string {
    return '₱' . number_format((float)$amount, 2);
}

// ==========================================================
// FLASH MESSAGING SYSTEM (One-time alerts shown on next page load)
// ==========================================================

/**
 * set_flash()
 * Stores a temporary status alert in $_SESSION (e.g., 'success' or 'error').
 */
function set_flash(string $type, string $message): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash_message'] = [
        'type' => $type, // 'success', 'error', 'info', 'warning'
        'text' => $message,
    ];
}

/**
 * get_flash()
 * Retrieves the stored flash message and deletes it from the session immediately.
 */
function get_flash(): ?array {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * render_flash()
 * Outputs the flash message alert HTML if one exists.
 */
function render_flash(): void {
    $flash = get_flash();
    if ($flash):
        $typeClass = $flash['type'] === 'error' ? 'alert-danger' : 
                    ($flash['type'] === 'success' ? 'alert-success' : 'alert-warning');
        $icon = $flash['type'] === 'error' ? 'fa-circle-exclamation' : 
               ($flash['type'] === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation');
    ?>
        <div class="container mt-3">
            <div class="custom-alert <?= $typeClass ?>" role="alert">
                <i class="fa-solid <?= $icon ?> me-2"></i>
                <span><?= e($flash['text']) ?></span>
                <button type="button" class="alert-close-btn" onclick="this.parentElement.remove();">&times;</button>
            </div>
        </div>
    <?php
    endif;
}

// ==========================================================
// CSRF PROTECTION (Cross-Site Request Forgery Prevention)
// ==========================================================

/**
 * generate_csrf_token()
 * Generates a random cryptographic token stored in $_SESSION for form verification.
 */
function generate_csrf_token(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * verify_csrf_token()
 * Compares submitted form token with session token using timing-attack safe comparison.
 */
function verify_csrf_token(?string $token): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

// ==========================================================
// SHOPPING CART FUNCTIONS (Session-based cart storage)
// ==========================================================

/**
 * init_cart()
 * Ensures the shopping cart session array exists.
 */
function init_cart(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

/**
 * cart_total_items()
 * Returns the sum of all quantities in the cart.
 */
function cart_total_items(): int {
    init_cart();
    $total = 0;
    foreach ($_SESSION['cart'] as $qty) {
        $total += (int)$qty;
    }
    return $total;
}

/**
 * cart_add_item()
 * Adds a product to the cart with database stock validation.
 */
function cart_add_item(int $productId, int $qty = 1): array {
    init_cart();
    $db = get_db_connection();
    
    $stmt = $db->prepare("SELECT id, name, stock, price FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        return ['success' => false, 'message' => 'Product not found.'];
    }

    $currentInCart = $_SESSION['cart'][$productId] ?? 0;
    $newTotal = $currentInCart + $qty;

    if ($product['stock'] <= 0) {
        return ['success' => false, 'message' => 'Sorry, "' . $product['name'] . '" is currently out of stock.'];
    }

    if ($newTotal > $product['stock']) {
        return [
            'success' => false, 
            'message' => 'Only ' . $product['stock'] . ' unit(s) available in stock for "' . $product['name'] . '".'
        ];
    }

    $_SESSION['cart'][$productId] = $newTotal;
    return ['success' => true, 'message' => '"' . $product['name'] . '" added to your collection bag.'];
}

/**
 * cart_update_item()
 * Updates the quantity of a specific item, adjusting if it exceeds stock.
 */
function cart_update_item(int $productId, int $qty): array {
    init_cart();
    $db = get_db_connection();

    if ($qty <= 0) {
        unset($_SESSION['cart'][$productId]);
        return ['success' => true, 'message' => 'Item removed from bag.'];
    }

    $stmt = $db->prepare("SELECT id, name, stock FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        unset($_SESSION['cart'][$productId]);
        return ['success' => false, 'message' => 'Product no longer available.'];
    }

    if ($qty > $product['stock']) {
        $_SESSION['cart'][$productId] = $product['stock'];
        return [
            'success' => false, 
            'message' => 'Requested quantity exceeds available stock. Adjusted to ' . $product['stock'] . ' unit(s).'
        ];
    }

    $_SESSION['cart'][$productId] = $qty;
    return ['success' => true, 'message' => 'Cart updated successfully.'];
}

/**
 * cart_remove_item()
 * Removes a specific product from the cart session.
 */
function cart_remove_item(int $productId): void {
    init_cart();
    unset($_SESSION['cart'][$productId]);
}

/**
 * cart_clear()
 * Empties the entire shopping cart session.
 */
function cart_clear(): void {
    init_cart();
    $_SESSION['cart'] = [];
}

/**
 * get_cart_details()
 * Joins session cart IDs with database records to compute pricing, shipping, and stock flags.
 */
function get_cart_details(): array {
    init_cart();
    $items = [];
    $subtotal = 0.0;

    if (empty($_SESSION['cart'])) {
        return [
            'items' => [],
            'subtotal' => 0.0,
            'shipping' => 0.0,
            'total' => 0.0,
            'has_stock_issue' => false,
        ];
    }

    $db = get_db_connection();
    $productIds = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));

    $stmt = $db->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($productIds);
    $products = $stmt->fetchAll();

    $productsById = [];
    foreach ($products as $p) {
        $productsById[$p['id']] = $p;
    }

    $hasStockIssue = false;

    foreach ($_SESSION['cart'] as $productId => $qty) {
        if (!isset($productsById[$productId])) {
            // Product deleted from DB
            unset($_SESSION['cart'][$productId]);
            continue;
        }

        $p = $productsById[$productId];
        $lineTotal = (float)$p['price'] * $qty;
        $subtotal += $lineTotal;

        $isOutOfStock = $p['stock'] <= 0;
        $isOverStock = $qty > $p['stock'];
        if ($isOutOfStock || $isOverStock) {
            $hasStockIssue = true;
        }

        $items[] = [
            'product_id' => $p['id'],
            'name' => $p['name'],
            'subtitle' => $p['subtitle'],
            'price' => (float)$p['price'],
            'image' => $p['image'],
            'stock' => (int)$p['stock'],
            'quantity' => (int)$qty,
            'line_total' => $lineTotal,
            'out_of_stock' => $isOutOfStock,
            'over_stock' => $isOverStock,
        ];
    }

    // Free shipping promo for orders over ₱5,000, otherwise standard ₱150
    $shipping = ($subtotal >= 5000 || $subtotal == 0) ? 0.00 : 150.00;
    $total = $subtotal + $shipping;

    return [
        'items' => $items,
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'total' => $total,
        'has_stock_issue' => $hasStockIssue,
    ];
}

