<?php
require_once __DIR__ . '/config/csrf.php';
require_once __DIR__ . '/config/helpers.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

/* Auto base path detection */
$basePath = '';
$path = $_SERVER['PHP_SELF'];
if (preg_match('#/(auth|pages|cart|admin)/#', $path)) {
    $basePath = '../';
}

$cartCount = 0;
if (isset($_SESSION['user_id'])) {
    include_once __DIR__ . '/config/db.php';
    $stmt = mysqli_prepare($conn, "SELECT SUM(quantity) FROM cart WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_array($res);
    $cartCount = $row[0] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Kunal Sales & Services</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/client.css">
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <div class="brand">
            <span class="brand-mark"></span>
            <span class="brand-text">Kunal Sales & Services</span>
        </div>
        <nav class="main-nav">
            <a href="<?php echo $basePath; ?>index.php">Home</a>
            <a href="<?php echo $basePath; ?>pages/products.php">Products</a>
            <a href="<?php echo $basePath; ?>cart/view_cart.php">Cart 
                <?php if ($cartCount > 0): ?>
                    <span class="badge bg-primary"><?= (int)$cartCount ?></span>
                <?php endif; ?>
            </a>
            <?php if (!isset($_SESSION['user_id'])) { ?>
                <a href="<?php echo $basePath; ?>auth/login.php" class="nav-cta">Login</a>
            <?php } else { ?>
                <a href="<?php echo $basePath; ?>pages/my_orders.php">My Orders</a>
                <span class="nav-user">
                    <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>
                </span>
                <a href="<?php echo $basePath; ?>auth/logout.php" class="nav-cta">Logout</a>
            <?php } ?>
        </nav>
    </div>
</header>
