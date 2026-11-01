<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Auto base path detection */
$basePath = '';
$path = $_SERVER['PHP_SELF'];
if (preg_match('#/(auth|pages|cart|admin)/#', $path)) {
    $basePath = '../';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Kunal Sales & Services</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap (utility only, not design) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CLIENT BRAND CSS -->
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
            <a href="<?php echo $basePath; ?>cart/view_cart.php">Cart</a>

            <?php if (!isset($_SESSION['user_id'])) { ?>
                <a href="<?php echo $basePath; ?>auth/login.php" class="nav-cta">Login</a>
            <?php } else { ?>
                <a href="<?php echo $basePath; ?>pages/my_orders.php">My Orders</a>
                <span class="nav-user">
                    <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                </span>
                <a href="<?php echo $basePath; ?>auth/logout.php" class="nav-cta">Logout</a>
            <?php } ?>
        </nav>

    </div>
</header>
