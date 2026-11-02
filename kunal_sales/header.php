<?php
require_once __DIR__ . '/config/csrf.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$basePath = '';
$currentPath = $_SERVER['PHP_SELF'] ?? '';
if (preg_match('#/(auth|pages|cart|admin)/#', $currentPath)) { $basePath = '../'; }
$cartCount = 0;
if (isset($_SESSION['user_id'])) {
    include_once __DIR__ . '/config/db.php';
    $stmt = mysqli_prepare($conn, "SELECT COALESCE(SUM(quantity),0) FROM cart WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_array(mysqli_stmt_get_result($stmt));
    $cartCount = (int)($row[0] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#050B18">
    <meta name="description" content="Kunal Sales & Services — inverter, battery and RO water purifier solutions for your home.">
    <title>Kunal Sales & Services | Power & Pure Water</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath) ?>assets/css/client.css">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a class="brand" href="<?= htmlspecialchars($basePath) ?>index.php" aria-label="Kunal Sales & Services home">
            <span class="brand-mark" aria-hidden="true"></span>
            <span class="brand-text">KUNAL <small>SALES &amp; SERVICES</small></span>
        </a>
        <nav class="main-nav" aria-label="Main navigation">
            <a href="<?= htmlspecialchars($basePath) ?>index.php">Home</a>
            <a href="<?= htmlspecialchars($basePath) ?>pages/products.php">Explore Products</a>
            <a href="<?= htmlspecialchars($basePath) ?>pages/products.php">Power &amp; Water</a>
            <a href="<?= htmlspecialchars($basePath) ?>cart/view_cart.php">Cart <span class="badge rounded-pill"><?= $cartCount ?></span></a>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="<?= htmlspecialchars($basePath) ?>auth/login.php" class="nav-cta">Sign in</a>
            <?php else: ?>
                <a href="<?= htmlspecialchars($basePath) ?>pages/my_orders.php">My Orders</a>
                <span class="nav-user"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Customer') ?></span>
                <a href="<?= htmlspecialchars($basePath) ?>auth/logout.php" class="nav-cta">Logout</a>
            <?php endif; ?>
        </nav>
    </div>
</header>