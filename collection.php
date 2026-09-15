<?php
// ==========================================================
// S PARFUM - FULL COLLECTION PAGE (collection.php)
// Purpose: Displays the full fragrance catalog with category
// filtering (Floral, Warm, Fresh) and sorting options.
// ==========================================================

$page_title = "The Collection | S Parfum";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Read category filter and sort preferences from URL query string
$category = trim($_GET['category'] ?? 'All');
$sort     = trim($_GET['sort'] ?? 'featured');

// 1. Handle quick add-to-cart form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'quick_add') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security token invalid.');
        header('Location: collection.php');
        exit;
    }

    $productId = (int)($_POST['product_id'] ?? 0);
    $result = cart_add_item($productId, 1);
    
    if ($result['success']) {
        set_flash('success', $result['message']);
    } else {
        set_flash('warning', $result['message']);
    }

    header('Location: collection.php?category=' . urlencode($category) . '&sort=' . urlencode($sort));
    exit;
}

$db = get_db_connection();

// 2. Build dynamic SQL query based on active filter and sort
$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

// Filter by fragrance family if a specific category is selected
if ($category !== 'All' && in_array($category, ['Floral', 'Warm', 'Fresh', 'Signature'])) {
    $sql .= " AND category = ?";
    $params[] = $category;
}

// Apply selected sort ordering
switch ($sort) {
    case 'price_asc':
        $sql .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY price DESC";
        break;
    case 'name_asc':
        $sql .= " ORDER BY name ASC";
        break;
    default:
        $sql .= " ORDER BY featured DESC, id ASC";
        break;
}

// Execute query using prepared statements
$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="page-header">
    <div class="container">
        <h1 class="page-title">The Collection</h1>
        <p class="page-subtitle">A curated symphony of artisanal fragrances for every aura and occasion</p>
    </div>
</div>

<div class="container mb-5" style="margin-bottom: 70px;">
    <!-- Filters & Sorting Bar -->
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 15px; margin-bottom: 35px; padding-bottom: 20px; border-bottom: 1px solid var(--gold-border);">
        <!-- Category Tabs -->
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <?php
            $cats = ['All', 'Floral', 'Warm', 'Fresh'];
            foreach ($cats as $cat):
                $isActive = $category === $cat;
            ?>
                <a href="collection.php?category=<?= urlencode($cat) ?>&sort=<?= urlencode($sort) ?>" 
                   class="btn-shop-now" 
                   style="<?= $isActive ? 'background: var(--text-main); color: #fff;' : '' ?> padding: 8px 18px; border-radius: 20px;">
                    <?= $cat ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Sort Select -->
        <form method="GET" action="collection.php" style="display: flex; align-items: center; gap: 10px;">
            <input type="hidden" name="category" value="<?= e($category) ?>">
            <label for="sortSelect" style="font-size: 12px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Sort By:</label>
            <select name="sort" id="sortSelect" class="form-control" style="width: auto; padding: 6px 12px; font-size: 13px;" onchange="this.form.submit()">
                <option value="featured" <?= $sort === 'featured' ? 'selected' : '' ?>>Featured</option>
                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name: A to Z</option>
            </select>
        </form>
    </div>

    <!-- Product Grid -->
    <?php if (empty($products)): ?>
        <div style="text-align: center; padding: 60px 20px; background: #fff; border: 1px solid var(--gold-border); border-radius: var(--radius-md);">
            <i class="fa-solid fa-magnifying-glass text-gold" style="font-size: 40px; margin-bottom: 15px;"></i>
            <h3>No Fragrances Found</h3>
            <p class="text-muted">There are no perfumes currently matching your filter.</p>
            <a href="collection.php" class="btn-shop-now mt-3" style="display: inline-block;">Reset Filters</a>
        </div>
    <?php else: ?>
        <div class="collection-grid">
            <?php foreach ($products as $product): ?>
                <?php
                    $isOutOfStock = $product['stock'] <= 0;
                    $isLowStock   = $product['stock'] > 0 && $product['stock'] <= 5;
                    $stockClass   = $isOutOfStock ? 'stock-out' : ($isLowStock ? 'stock-low' : 'stock-in');
                    $stockText    = $isOutOfStock ? 'Out of Stock' : ($isLowStock ? "Only {$product['stock']} Left" : "{$product['stock']} in Stock");
                ?>
                <div class="product-card">
                    <div class="product-image-container">
                        <span class="stock-badge <?= $stockClass ?>"><?= $stockText ?></span>
                        <a href="product-detail.php?id=<?= $product['id'] ?>">
                            <img src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>">
                        </a>
                    </div>

                    <div class="product-info">
                        <h3 class="product-title"><?= e($product['name']) ?></h3>
                        <div class="product-notes"><?= e($product['subtitle']) ?></div>
                        <p class="product-desc"><?= e($product['description']) ?></p>
                        <div class="product-price"><?= format_price($product['price']) ?></div>

                        <div class="product-actions">
                            <a href="product-detail.php?id=<?= $product['id'] ?>" class="btn-shop-now">
                                View Details
                            </a>
                            <form method="POST" action="collection.php?category=<?= urlencode($category) ?>&sort=<?= urlencode($sort) ?>" style="flex: 1;">
                                <input type="hidden" name="action" value="quick_add">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <button type="submit" class="btn-add-cart w-100" style="width: 100%;" <?= $isOutOfStock ? 'disabled' : '' ?>>
                                    <i class="fa-solid fa-bag-shopping"></i> Add
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

