<?php
// ==========================================================
// S PARFUM - HOMEPAGE
// Faithful implementation of the PDF Design Mockup
// ==========================================================

$page_title = "S Parfum | The Essence of Elegance";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Fetch featured products from database
$db = get_db_connection();
$stmt = $db->query("SELECT * FROM products WHERE featured = 1 ORDER BY id ASC LIMIT 3");
$featuredProducts = $stmt->fetchAll();

// Handle quick add-to-cart from homepage
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'quick_add') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security verification failed. Please try again.');
        header('Location: index.php');
        exit;
    }

    $productId = (int)($_POST['product_id'] ?? 0);
    $result = cart_add_item($productId, 1);
    
    if ($result['success']) {
        set_flash('success', $result['message']);
    } else {
        set_flash('warning', $result['message']);
    }

    header('Location: index.php#collection');
    exit;
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<!-- ==========================================================
     HERO SECTION
     ========================================================== -->
<section class="hero-section">
    <div class="container">
        <div class="hero-grid">
            <!-- Hero Left Content -->
            <div class="hero-content">
                <h1 class="hero-brand">S PARFUM</h1>
                <div class="hero-subtitle">The Essence of Elegance</div>
                <p class="hero-copy">
                    "Crafted to express individuality, evoke confidence, and leave a lasting impression of timeless beauty."
                </p>
                <a href="#collection" class="hero-cta-btn">
                    Discover The Collection
                </a>

                <!-- Carousel Controls -->
                <div class="hero-controls">
                    <button type="button" class="hero-arrow" id="heroPrevBtn" aria-label="Previous">
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>
                    <div class="hero-dots">
                        <span class="hero-dot active"></span>
                        <span class="hero-dot"></span>
                        <span class="hero-dot"></span>
                    </div>
                    <button type="button" class="hero-arrow" id="heroNextBtn" aria-label="Next">
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Hero Right Visual (Luxury Perfume Bottle) -->
            <div class="hero-visual">
                <div class="hero-image-wrapper">
                    <img src="assets/images/PerfumeGold.png" alt="S Parfum Signature Bottle - The Essence of Elegance">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================================
     THE COLLECTION SECTION
     ========================================================== -->
<section class="section-padding collection-section" id="collection">
    <div class="container">
        <h2 class="section-title">The Collection</h2>
        <p class="section-subtitle">
            Discover fragrances created to express individuality, elegance, and timeless beauty.
        </p>

        <div class="collection-grid">
            <?php foreach ($featuredProducts as $product): ?>
                <?php
                    $isOutOfStock = $product['stock'] <= 0;
                    $isLowStock   = $product['stock'] > 0 && $product['stock'] <= 5;
                    $stockClass   = $isOutOfStock ? 'stock-out' : ($isLowStock ? 'stock-low' : 'stock-in');
                    $stockText    = $isOutOfStock ? 'Out of Stock' : ($isLowStock ? "Only {$product['stock']} Left" : "{$product['stock']} in Stock");
                ?>
                <div class="product-card">
                    <!-- Product Image & Live Stock Badge -->
                    <div class="product-image-container">
                        <span class="stock-badge <?= $stockClass ?>"><?= $stockText ?></span>
                        <a href="product-detail.php?id=<?= $product['id'] ?>">
                            <img src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>">
                        </a>
                    </div>

                    <!-- Product Info -->
                    <div class="product-info">
                        <h3 class="product-title"><?= e($product['name']) ?></h3>
                        <div class="product-notes"><?= e($product['subtitle']) ?></div>
                        <p class="product-desc"><?= e($product['description']) ?></p>
                        <div class="product-price"><?= format_price($product['price']) ?></div>

                        <div class="product-actions">
                            <a href="product-detail.php?id=<?= $product['id'] ?>" class="btn-shop-now">
                                View Details
                            </a>
                            <form method="POST" action="index.php" style="flex: 1;">
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

        <div class="text-center" style="text-align: center; margin-top: 40px;">
            <a href="collection.php" class="hero-cta-btn" style="border-color: var(--gold-primary); color: var(--gold-dark);">
                Explore Full S Parfum Collection <i class="fa-solid fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- ==========================================================
     THE ESSENCE SECTION
     ========================================================== -->
<section class="essence-section">
    <div class="container">
        <div class="essence-grid">
            <div class="essence-content">
                <h2 class="essence-title">The Essence</h2>
                <div class="essence-quote">
                    "A fragrance is more than a scent. It is an expression of character, memory, and individuality. It speaks without words and leaves a trace of who you are."
                </div>
            </div>
            <div class="essence-image-box">
                <img src="assets/images/PerfumeGold.png" alt="The Essence of S Parfum">
            </div>
        </div>
    </div>
</section>

<!-- ==========================================================
     SIGNATURE SCENTS (Dark Luxury Section)
     ========================================================== -->
<section class="signature-scents-section">
    <div class="container">
        <h2 class="section-title">Signature Scents</h2>
        <p class="section-subtitle">
            Curated fragrance families composed of the world's rarest natural essences.
        </p>

        <div class="scents-grid">
            <!-- FLORAL -->
            <div class="scent-column">
                <div class="scent-icon-wrapper">
                    <i class="fa-solid fa-spa"></i>
                </div>
                <h3 class="scent-name">Floral</h3>
                <p class="scent-desc">
                    Elegant blooms that inspire timeless beauty and romantic whisper.
                </p>
            </div>

            <!-- WARM -->
            <div class="scent-column">
                <div class="scent-icon-wrapper">
                    <i class="fa-solid fa-fire-flame-curved"></i>
                </div>
                <h3 class="scent-name">Warm</h3>
                <p class="scent-desc">
                    Cozy notes of golden amber and vanilla that wrap you in comfort and warmth.
                </p>
            </div>

            <!-- FRESH -->
            <div class="scent-column">
                <div class="scent-icon-wrapper">
                    <i class="fa-solid fa-seedling"></i>
                </div>
                <h3 class="scent-name">Fresh</h3>
                <p class="scent-desc">
                    Crisp and airy aromas for a refreshing escape and pure vitality.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================================
     OUR STORY SECTION
     ========================================================== -->
<section class="story-section" id="story">
    <div class="container">
        <div class="story-card">
            <h2 class="story-title">Our Story</h2>
            <p class="story-text">
                "A fragrance is more than a scent. It is an expression of character, memory, and individuality. It speaks without words and leaves a trace of who you are."
            </p>
            <div style="margin-top: 25px;">
                <span style="font-size: 15px; letter-spacing: 3px; text-transform: uppercase; color: var(--gold-primary); font-weight: 700;">
                    S Parfum &bull; Dumaguete, Philippines
                </span>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================================
     THE S PARFUM EXPERIENCE SECTION
     ========================================================== -->
<section class="experience-section" id="experience">
    <div class="container">
        <h2 class="section-title">The S Parfum Experience</h2>
        <p class="section-subtitle">A journey of sensory revelation crafted for the distinguished connoisseur.</p>

        <div class="experience-grid">
            <!-- Step 01 -->
            <div class="exp-step">
                <div class="exp-badge">
                    <i class="fa-solid fa-compass"></i>
                </div>
                <span class="exp-step-num">Step 01</span>
                <h3 class="exp-step-title">Discover</h3>
                <p class="exp-step-desc">
                    Find a fragrance that reflects you and mirrors your deepest character.
                </p>
            </div>

            <!-- Step 02 -->
            <div class="exp-step">
                <div class="exp-badge">
                    <i class="fa-solid fa-gem"></i>
                </div>
                <span class="exp-step-num">Step 02</span>
                <h3 class="exp-step-title">Experience</h3>
                <p class="exp-step-desc">
                    Immerse yourself in its multi-layered character, rich notes, and captivating essence.
                </p>
            </div>

            <!-- Step 03 -->
            <div class="exp-step">
                <div class="exp-badge">
                    <i class="fa-solid fa-signature"></i>
                </div>
                <span class="exp-step-num">Step 03</span>
                <h3 class="exp-step-title">Express</h3>
                <p class="exp-step-desc">
                    Make the fragrance part of your unique identity and unforgettable presence.
                </p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

