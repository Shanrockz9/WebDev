<?php
// ==========================================================
// S PARFUM - PRODUCT DETAIL PAGE
// ==========================================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$productId = (int)($_GET['id'] ?? 0);
$db = get_db_connection();

$stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('error', 'The requested fragrance was not found.');
    header('Location: collection.php');
    exit;
}

$page_title = e($product['name']) . " | S Parfum";

// Handle Add to Cart with custom quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security verification failed.');
        header('Location: product-detail.php?id=' . $productId);
        exit;
    }

    $quantity = max(1, (int)($_POST['quantity'] ?? 1));
    $result = cart_add_item($productId, $quantity);

    if ($result['success']) {
        set_flash('success', $result['message']);
        if (isset($_POST['buy_now'])) {
            header('Location: checkout.php');
            exit;
        }
    } else {
        set_flash('error', $result['message']);
    }

    header('Location: product-detail.php?id=' . $productId);
    exit;
}

$isOutOfStock = $product['stock'] <= 0;
$isLowStock   = $product['stock'] > 0 && $product['stock'] <= 5;
$stockClass   = $isOutOfStock ? 'stock-out' : ($isLowStock ? 'stock-low' : 'stock-in');
$stockText    = $isOutOfStock ? 'Out of Stock' : ($isLowStock ? "Low Stock: Only {$product['stock']} units left!" : "In Stock ({$product['stock']} units available)");

// Split scent notes if available
$notesList = [];
if (!empty($product['scent_notes'])) {
    $notesList = explode('|', $product['scent_notes']);
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container section-padding">
    <div style="margin-bottom: 25px;">
        <a href="collection.php" class="text-muted" style="font-size: 18px;">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Collection
        </a>
    </div>

    <div class="product-detail-card" style="display: grid; grid-template-columns: 1fr 1.1fr; gap: 55px; align-items: start; background: #fff; padding: 40px; border-radius: var(--radius-md); border: 1px solid var(--gold-border); box-shadow: var(--shadow-soft);">
        
        <!-- Product Image Gallery -->
        <div style="background: #fbf8f5; border-radius: var(--radius-md); padding: 30px; text-align: center; border: 1px solid #f2e9de;">
            <img src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>" style="max-height: 440px; margin: 0 auto; object-fit: contain;">
        </div>

        <!-- Product Details -->
        <div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                <span style="font-size: 15px; letter-spacing: 2px; text-transform: uppercase; color: var(--gold-primary); font-weight: 700;">
                    Family: <?= e($product['category']) ?>
                </span>
                <span class="stock-badge <?= $stockClass ?>"><?= $stockText ?></span>
            </div>

            <h1 style="font-size: 2.6rem; letter-spacing: 2px; margin-bottom: 5px;"><?= e($product['name']) ?></h1>
            <p style="font-size: 18px; letter-spacing: 2px; text-transform: uppercase; color: var(--gold-dark); margin-bottom: 20px;">
                <?= e($product['subtitle']) ?>
            </p>

            <div style="font-family: var(--font-heading); font-size: 2.2rem; font-weight: 600; color: var(--text-main); margin-bottom: 25px; border-bottom: 1px solid var(--gold-border); padding-bottom: 15px;">
                <?= format_price($product['price']) ?>
                <span style="font-size: 18px; font-weight: 400; color: var(--text-muted); font-family: var(--font-body); margin-left: 10px;">
                    50ml / 1.7 fl. oz. Eau de Parfum
                </span>
            </div>

            <p style="color: #4a423c; line-height: 1.8; margin-bottom: 25px; font-size: 20px;">
                <?= nl2br(e($product['description'])) ?>
            </p>

            <!-- Olfactory Notes Pyramid -->
            <?php if (!empty($notesList)): ?>
                <div style="background: #fdfaf6; border: 1px solid #ebd9c8; border-radius: var(--radius-sm); padding: 18px 22px; margin-bottom: 30px;">
                    <h4 style="font-size: 16px; letter-spacing: 2px; text-transform: uppercase; color: var(--gold-dark); margin-bottom: 10px; font-weight: 700;">
                        <i class="fa-solid fa-gem me-1"></i> Olfactory Notes Breakdown:
                    </h4>
                    <ul style="list-style: none; padding-left: 0; font-size: 18px; line-height: 1.8;">
                        <?php foreach ($notesList as $note): ?>
                            <li style="color: #4e443d;">
                                <i class="fa-solid fa-circle-dot text-gold me-2" style="font-size: 8px;"></i>
                                <?= e(trim($note)) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Add to Cart Form -->
            <form method="POST" action="product-detail.php?id=<?= $productId ?>">
                <input type="hidden" name="action" value="add_to_cart">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 25px;">
                    <label style="font-size: 16px; letter-spacing: 1px; text-transform: uppercase; font-weight: 600;">Quantity:</label>
                    <div class="qty-control">
                        <button type="button" class="qty-btn" onclick="adjustQuantity('detailQtyInput', -1, <?= $product['stock'] ?>)">-</button>
                        <input type="number" id="detailQtyInput" name="quantity" class="qty-input" value="1" min="1" max="<?= $product['stock'] ?>" <?= $isOutOfStock ? 'disabled' : '' ?>>
                        <button type="button" class="qty-btn" onclick="adjustQuantity('detailQtyInput', 1, <?= $product['stock'] ?>)">+</button>
                    </div>
                    <span style="font-size: 16px; color: var(--text-muted);">
                        (Max available: <?= $product['stock'] ?>)
                    </span>
                </div>

                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <button type="submit" class="btn-primary-action" style="flex: 1; margin-top: 0;" <?= $isOutOfStock ? 'disabled' : '' ?>>
                        <i class="fa-solid fa-bag-shopping me-2"></i> Add to Bag
                    </button>
                    <button type="submit" name="buy_now" value="1" class="btn-secondary-action" style="flex: 1; margin-top: 0; border-color: var(--gold-primary); color: var(--gold-dark);" <?= $isOutOfStock ? 'disabled' : '' ?>>
                        Buy Now
                    </button>
                </div>
            </form>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #f2e9de; font-size: 12px; color: var(--text-muted);">
                <div><i class="fa-solid fa-truck-fast text-gold me-2"></i> Complimentary Express Shipping above ₱5,000</div>
                <div><i class="fa-solid fa-shield-halved text-gold me-2"></i> 100% Guaranteed Authentic Essence</div>
                <div><i class="fa-solid fa-gift text-gold me-2"></i> Signature S Parfum Gift Packaging</div>
                <div><i class="fa-solid fa-rotate-left text-gold me-2"></i> 7-Day Replacement Guarantee</div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

