<?php
// ==========================================================
// S PARFUM - LOGOUT HANDLER (logout.php)
// Purpose: Clears user session keys, sets a departure flash
// message, and redirects back to the homepage.
// ==========================================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Destroy user session authentication state
logout_user();

set_flash('info', 'You have been signed out safely. Come back soon!');
header('Location: index.php');
exit;

