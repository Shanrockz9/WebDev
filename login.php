<?php
// ==========================================================
// S PARFUM - USER SIGN IN (login.php)
// Purpose: Authenticates returning clients and administrators,
// validates CSRF tokens, and redirects to target destination.
// ==========================================================

$page_title = "Sign In | S Parfum";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$redirect = $_GET['redirect'] ?? 'index.php';
$error = null;

// Handle Sign In form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please refresh.';
    } else {
        $email    = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $result = login_user($email, $password);
        if ($result['success']) {
            set_flash('success', $result['message']);
            // If admin logs in without specific redirect, route to Admin Dashboard
            if ($result['role'] === 'admin' && empty($_GET['redirect'])) {
                header('Location: admin/index.php');
            } else {
                header('Location: ' . $redirect);
            }
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
        <h1 class="page-title">Client Sign In</h1>
        <p class="page-subtitle">Access your fragrance history, saved bag, and bespoke orders</p>
    </div>
</div>

<div class="container-narrow" style="margin-bottom: 80px;">
    <div class="form-card" style="max-width: 500px; margin: 0 auto;">
        <?php if ($error): ?>
            <div class="custom-alert alert-danger" style="margin-bottom: 20px;">
                <i class="fa-solid fa-circle-exclamation me-2"></i>
                <span><?= e($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php<?= !empty($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required
                       value="<?= e($_POST['email'] ?? '') ?>"
                       placeholder="you@example.com">
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required
                       placeholder="••••••••">
            </div>

            <button type="submit" class="btn-primary-action">
                Sign In
            </button>
        </form>

        <div style="text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid #f0e6da; font-size: 13px;">
            Don't have an account yet? 
            <a href="register.php" style="color: var(--gold-dark); font-weight: 600; text-decoration: underline;">
                Create Account
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

