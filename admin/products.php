<?php
// ==========================================================
// S PARFUM - PRODUCT & INVENTORY MANAGEMENT (CRUD)
// Web Development 1 Midterm Project
// ==========================================================

define('IN_ADMIN', true);
$page_title = "Manage Inventory & Products | S Parfum Admin";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$db = get_db_connection();
$editProduct = null;
$errors = [];

// Handle Edit Fetch
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$editId]);
    $editProduct = $stmt->fetch();
}

// Handle Form Actions (Add, Update, Quick Restock, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security session expired.');
        header('Location: products.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    // 1. QUICK STOCK UPDATE
    if ($action === 'quick_stock') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $newStock  = max(0, (int)($_POST['stock'] ?? 0));

        $stmt = $db->prepare("UPDATE products SET stock = ? WHERE id = ?");
        $stmt->execute([$newStock, $productId]);

        set_flash('success', 'Stock level updated successfully.');
        header('Location: products.php');
        exit;
    }

    // 2. DELETE PRODUCT
    if ($action === 'delete') {
        $productId = (int)($_POST['product_id'] ?? 0);
        
        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$productId]);

        set_flash('info', 'Product removed from catalog.');
        header('Location: products.php');
        exit;
    }

    // 3. CREATE OR UPDATE PRODUCT
    if ($action === 'save_product') {
        $id          = (int)($_POST['product_id'] ?? 0);
        $name        = trim($_POST['name'] ?? '');
        $subtitle    = trim($_POST['subtitle'] ?? '');
        $category    = trim($_POST['category'] ?? 'Floral');
        $description = trim($_POST['description'] ?? '');
        $scentNotes  = trim($_POST['scent_notes'] ?? '');
        $price       = (float)($_POST['price'] ?? 0);
        $stock       = (int)($_POST['stock'] ?? 0);
        $image       = trim($_POST['image'] ?? 'assets/images/PerfumeGold.png');
        $featured    = isset($_POST['featured']) ? 1 : 0;

        // Validation
        if (empty($name)) {
            $errors[] = 'Product name is required.';
        }
        if ($price <= 0) {
            $errors[] = 'Price must be greater than zero.';
        }
        if ($stock < 0) {
            $errors[] = 'Stock quantity cannot be negative.';
        }

        if (empty($errors)) {
            if ($id > 0) {
                // Update
                $stmt = $db->prepare("
                    UPDATE products 
                    SET name = ?, subtitle = ?, category = ?, description = ?, scent_notes = ?, price = ?, stock = ?, image = ?, featured = ?
                    WHERE id = ?
                ");
                $stmt->execute([$name, $subtitle, $category, $description, $scentNotes, $price, $stock, $image, $featured, $id]);
                set_flash('success', 'Product "' . $name . '" updated successfully.');
            } else {
                // Insert
                $stmt = $db->prepare("
                    INSERT INTO products (name, subtitle, category, description, scent_notes, price, stock, image, featured)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $subtitle, $category, $description, $scentNotes, $price, $stock, $image, $featured]);
                set_flash('success', 'New fragrance "' . $name . '" added to the catalog.');
            }

            header('Location: products.php');
            exit;
        }
    }
}

// Fetch all products
$stmt = $db->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="page-header">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h1 class="page-title" style="text-align: left;">Perfume Catalog & Stocks</h1>
                <p class="page-subtitle" style="text-align: left;">Live inventory tracking, price adjustment, and catalog management</p>
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

    <div style="display: grid; grid-template-columns: 1.8fr 1.2fr; gap: 35px; align-items: start;">
        <!-- Products Table -->
        <div class="cart-table-wrapper">
            <div style="padding: 18px 22px; background: #faf5ee; border-bottom: 1px solid var(--gold-border); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 1.2rem; text-transform: uppercase; letter-spacing: 1.5px; margin: 0;">
                    Inventory List (<?= count($products) ?> items)
                </h3>
            </div>

            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Live Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <?php
                            $isLow = $p['stock'] <= 5;
                            $isOut = $p['stock'] <= 0;
                        ?>
                        <tr>
                            <td>
                                <div class="cart-item-info">
                                    <img src="../<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" class="cart-item-thumb">
                                    <div>
                                        <strong style="font-size: 14px;"><?= e($p['name']) ?></strong>
                                        <div style="font-size: 11px; color: var(--text-muted);"><?= e($p['subtitle']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--gold-dark); font-weight: 600;">
                                    <?= e($p['category']) ?>
                                </span>
                            </td>
                            <td style="font-weight: 600; font-family: var(--font-heading); font-size: 1.15rem;">
                                <?= format_price($p['price']) ?>
                            </td>
                            <td>
                                <!-- Inline Quick Restock Form -->
                                <form method="POST" action="products.php" style="display: flex; align-items: center; gap: 6px;">
                                    <input type="hidden" name="action" value="quick_stock">
                                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="number" name="stock" value="<?= $p['stock'] ?>" min="0" 
                                           style="width: 55px; padding: 4px 6px; font-size: 12px; border: 1px solid <?= $isOut ? '#e57373' : ($isLow ? '#ffb74d' : '#dcd4cc') ?>; border-radius: 4px; text-align: center; font-weight: 600;">
                                    <button type="submit" class="btn-shop-now" style="padding: 4px 8px; font-size: 10px;" title="Save stock">
                                        Save
                                    </button>
                                </form>
                                <?php if ($isOut): ?>
                                    <span class="stock-badge stock-out" style="display: block; margin-top: 4px; font-size: 9px; width: fit-content;">Depleted</span>
                                <?php elseif ($isLow): ?>
                                    <span class="stock-badge stock-low" style="display: block; margin-top: 4px; font-size: 9px; width: fit-content;">Low</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="products.php?edit=<?= $p['id'] ?>" class="btn-shop-now" style="padding: 6px 10px; font-size: 11px;" title="Edit details">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form method="POST" action="products.php" onsubmit="return confirm('Delete product <?= e($p['name']) ?>?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <button type="submit" class="btn-remove-item" style="padding: 6px;" title="Delete product">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Add / Edit Product Form -->
        <div class="form-card">
            <h3 style="font-size: 1.3rem; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--gold-border);">
                <?= $editProduct ? 'Edit Fragrance Details' : 'Add New Fragrance' ?>
            </h3>

            <form method="POST" action="products.php">
                <input type="hidden" name="action" value="save_product">
                <input type="hidden" name="product_id" value="<?= $editProduct ? $editProduct['id'] : 0 ?>">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                <div class="form-group">
                    <label class="form-label" for="prod_name">Perfume Name *</label>
                    <input type="text" id="prod_name" name="name" class="form-control" required
                           value="<?= e($editProduct['name'] ?? '') ?>" placeholder="e.g. S PARFUM IV">
                </div>

                <div class="form-group">
                    <label class="form-label" for="prod_subtitle">Subtitle / Character *</label>
                    <input type="text" id="prod_subtitle" name="subtitle" class="form-control" required
                           value="<?= e($editProduct['subtitle'] ?? '') ?>" placeholder="e.g. Woody | Mysterious | Noble">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label" for="prod_category">Olfactory Family</label>
                        <select id="prod_category" name="category" class="form-control">
                            <?php foreach (['Floral', 'Warm', 'Fresh', 'Signature'] as $c): ?>
                                <option value="<?= $c ?>" <?= ($editProduct['category'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="prod_price">Price (₱) *</label>
                        <input type="number" step="0.01" id="prod_price" name="price" class="form-control" required
                               value="<?= e($editProduct['price'] ?? '2500.00') ?>">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label" for="prod_stock">Initial Stock *</label>
                        <input type="number" id="prod_stock" name="stock" class="form-control" required min="0"
                               value="<?= e($editProduct['stock'] ?? '10') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="prod_image">Image Path</label>
                        <select id="prod_image" name="image" class="form-control">
                            <option value="assets/images/PerfumeGold.png" <?= ($editProduct['image'] ?? '') === 'assets/images/PerfumeGold.png' ? 'selected' : '' ?>>Perfume Gold (Signature)</option>
                            <option value="assets/images/PerfumeShine.jpg" <?= ($editProduct['image'] ?? '') === 'assets/images/PerfumeShine.jpg' ? 'selected' : '' ?>>S Parfum I (Perfume Shine - Floral)</option>
                            <option value="assets/images/PerfumeGold.png" <?= ($editProduct['image'] ?? '') === 'assets/images/PerfumeGold.png' ? 'selected' : '' ?>>S Parfum II (Perfume Gold - Warm)</option>
                            <option value="assets/images/PerfumeBlack.png" <?= ($editProduct['image'] ?? '') === 'assets/images/PerfumeBlack.png' ? 'selected' : '' ?>>S Parfum III (Perfume Black - Fresh)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="prod_notes">Notes Breakdown</label>
                    <input type="text" id="prod_notes" name="scent_notes" class="form-control"
                           value="<?= e($editProduct['scent_notes'] ?? '') ?>" placeholder="Top: Bergamot | Heart: Rose | Base: Amber">
                </div>

                <div class="form-group">
                    <label class="form-label" for="prod_desc">Description</label>
                    <textarea id="prod_desc" name="description" class="form-control" rows="3" required
                              placeholder="Artisanal description of the scent aura..."><?= e($editProduct['description'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                        <input type="checkbox" name="featured" value="1" <?= ($editProduct['featured'] ?? 1) ? 'checked' : '' ?>>
                        Feature on Homepage Collection
                    </label>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn-primary-action" style="flex: 1; margin-top: 10px;">
                        <?= $editProduct ? 'Update Fragrance' : 'Add to Catalog' ?>
                    </button>
                    <?php if ($editProduct): ?>
                        <a href="products.php" class="btn-secondary-action" style="flex: 1; margin-top: 10px;">
                            Cancel
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

