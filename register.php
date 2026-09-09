<?php
// ==========================================================
// S PARFUM - CLIENT REGISTRATION
// ==========================================================

$page_title = "Create Account | S Parfum";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please reload.';
    } else {
        $result = register_user($_POST);
        if ($result['success']) {
            set_flash('success', $result['message']);
            header('Location: index.php');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="page-header">
    <div class="container">
        <h1 class="page-title">Client Registration</h1>
        <p class="page-subtitle">Join the exclusive circle of S Parfum and discover bespoke fragrances</p>
    </div>
</div>

<div class="container-narrow" style="margin-bottom: 80px;">
    <div class="form-card" style="max-width: 600px; margin: 0 auto;">
        <?php if ($error): ?>
            <div class="custom-alert alert-danger" style="margin-bottom: 20px;">
                <i class="fa-solid fa-circle-exclamation me-2"></i>
                <span><?= e($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

            <div class="form-group">
                <label class="form-label" for="name">Full Name *</label>
                <input type="text" id="name" name="name" class="form-control" required
                       value="<?= e($_POST['name'] ?? '') ?>" placeholder="e.g. Maria Santos">
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email Address *</label>
                <input type="email" id="email" name="email" class="form-control" required
                       value="<?= e($_POST['email'] ?? '') ?>" placeholder="you@example.com">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label" for="password">Password *</label>
                    <input type="password" id="password" name="password" class="form-control" required
                           placeholder="At least 6 characters">
                </div>
                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required
                           placeholder="Repeat password">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="phone">Phone Number (Optional)</label>
                <input type="tel" id="phone" name="phone" class="form-control"
                       value="<?= e($_POST['phone'] ?? '') ?>" placeholder="+63 917 123 4567">
            </div>

            <div class="form-group">
                <label class="form-label" for="address">Delivery Address (Optional)</label>
                <textarea id="address" name="address" class="form-control" rows="2"
                          placeholder="Your preferred delivery address in the Philippines..."><?= e($_POST['address'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn-primary-action">
                Create My Account
            </button>
        </form>

        <div style="text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid #f0e6da; font-size: 13px;">
            Already have an account? 
            <a href="login.php" style="color: var(--gold-dark); font-weight: 600; text-decoration: underline;">
                Sign In
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

