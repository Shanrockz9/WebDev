<?php
// ==========================================================
// S PARFUM - AUTHENTICATION & SESSIONS (includes/auth.php)
// Purpose: Handles user login, registration, password
// hashing, session guards, and role-based permissions.
// ==========================================================

if (session_status() === PHP_SESSION_NONE) {
    // Configure secure session cookie attributes (prevents JavaScript access via HttpOnly)
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

/**
 * is_logged_in()
 * Returns true if a user ID is currently set in the session.
 */
function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * is_admin()
 * Returns true if the logged-in user has the 'admin' role.
 */
function is_admin(): bool {
    return is_logged_in() && !empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * current_user()
 * Fetches the active user's database record using their session ID.
 */
function current_user(): ?array {
    if (!is_logged_in()) {
        return null;
    }

    $db = get_db_connection();
    $stmt = $db->prepare("SELECT id, name, email, phone, address, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        // User record no longer exists
        logout_user();
        return null;
    }

    return $user;
}

/**
 * login_user()
 * Authenticates user credentials, verifies bcrypt hash, and starts user session.
 */
function login_user(string $email, string $password): array {
    $email = trim($email);
    $password = trim($password);

    if (empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Please enter both your email and password.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Please provide a valid email address format.'];
    }

    $db = get_db_connection();
    $stmt = $db->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid email or password. Please try again.'];
    }

    // Regenerate session ID to prevent session fixation attacks
    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];

    return ['success' => true, 'message' => 'Welcome back, ' . $user['name'] . '!', 'role' => $user['role']];
}

/**
 * register_user()
 * Validates inputs, hashes password with PASSWORD_DEFAULT, and inserts customer record.
 */
function register_user(array $data): array {
    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $confirmPassword = $data['confirm_password'] ?? '';
    $phone = trim($data['phone'] ?? '');
    $address = trim($data['address'] ?? '');

    // Form input validation
    if (empty($name) || empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Name, email, and password are required.'];
    }

    if (strlen($name) < 2 || strlen($name) > 100) {
        return ['success' => false, 'message' => 'Name must be between 2 and 100 characters.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Please enter a valid email address.'];
    }

    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters long for security.'];
    }

    // Password rules: Minimum 1 and Maximum 3 uppercase letters
    $upperCount = preg_match_all('/[A-Z]/', $password);
    if ($upperCount < 1 || $upperCount > 3) {
        return ['success' => false, 'message' => 'Password must contain between 1 and 3 capital (uppercase) letters. (Currently: ' . $upperCount . ')'];
    }

    // Password rules: Minimum 1 and Maximum 3 numbers (digits)
    $numCount = preg_match_all('/[0-9]/', $password);
    if ($numCount < 1 || $numCount > 3) {
        return ['success' => false, 'message' => 'Password must contain between 1 and 3 numbers. (Currently: ' . $numCount . ')'];
    }

    if ($password !== $confirmPassword) {
        return ['success' => false, 'message' => 'Passwords do not match. Please verify your password.'];
    }

    $db = get_db_connection();

    // Check if email is already taken
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'This email address is already registered. Please log in.'];
    }

    // Hash password securely with bcrypt
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare("
        INSERT INTO users (name, email, password, phone, address, role)
        VALUES (?, ?, ?, ?, ?, 'customer')
    ");
    $stmt->execute([$name, $email, $hashedPassword, $phone, $address]);

    $newUserId = (int)$db->lastInsertId();

    // Auto-login new user immediately upon registration
    session_regenerate_id(true);
    $_SESSION['user_id'] = $newUserId;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_role'] = 'customer';

    return ['success' => true, 'message' => 'Your S Parfum account has been created successfully!'];
}

/**
 * logout_user()
 * Clears user authentication session keys while preserving shopping cart contents.
 */
function logout_user(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_role']);
}

/**
 * require_login()
 * Session guard: redirects unauthenticated guests to login page with return URL.
 */
function require_login(): void {
    if (!is_logged_in()) {
        set_flash('warning', 'Please sign in to access your account and checkout.');
        header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

/**
 * require_admin()
 * Role guard: blocks non-administrators from viewing admin pages.
 */
function require_admin(): void {
    if (!is_admin()) {
        set_flash('error', 'Access restricted. Administrator privileges required.');
        header('Location: ../login.php');
        exit;
    }
}

