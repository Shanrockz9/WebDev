<?php
// ==========================================================
// S PARFUM - PAGE HEADER TEMPLATE (includes/header.php)
// Purpose: Defines HTML head, meta tags, font links, and CSS.
// Included at the top of every page.
// ==========================================================

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

// Set fallback page title if not defined by the caller page
$page_title = $page_title ?? 'S Parfum - The Essence of Elegance';

// Adjust relative path if called from inside /admin/ folder
$asset_path = defined('IN_ADMIN') ? '../assets/' : 'assets/';
$root_path  = defined('IN_ADMIN') ? '../' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?></title>
    
    <!-- Google Fonts Fallbacks: Bodoni Moda, Cormorant Garamond, Playfair Display -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@0,6..96,400..900;1,6..96,400..900&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <!-- Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Main Luxury Stylesheet -->
    <link rel="stylesheet" href="<?= $asset_path ?>css/style.css">
</head>
<body>

