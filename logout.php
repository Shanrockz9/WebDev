<?php
// ==========================================================
// S PARFUM - LOGOUT HANDLER
// ==========================================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

logout_user();
set_flash('info', 'You have been signed out safely. Come back soon!');
header('Location: index.php');
exit;

