<?php
session_start();
include '../config/db.php';
include '../header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* ✅ RUN QUERY */
$stmt = mysqli_prepare($conn, "
    SELECT cart.*, products.product_name, products.price 
    FROM cart
    JOIN products ON cart.product_id = products.product_id
    WHERE cart.user_id = ?
");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$q = mysqli_stmt_get_result($stmt);

/* ❌ If query fails */
if (!$q) {
    die("Cart Query Failed: " . mysqli_error($conn));
}

$total = 0;
?>

<div class="cart-page">
  <div class="cart-box">

    <h2>Your Shopping Cart</h2>

    <?php if (mysqli_num_rows($q) == 0) { ?>
        <p class="empty-cart-text">Your cart is empty.</p>
        <a href="../pages/products.php" class="hero-btn">Shop Now</a>
    <?php } else { ?>

        <?php while ($c = mysqli_fetch_assoc($q)) {
            $subtotal = $c['price'] * $c['quantity'];
            $total += $subtotal;
        ?>
            <div class="cart-item">
              <div class="cart-item-info">
                <strong><?php echo htmlspecialchars($c['product_name']); ?></strong>
                <p>Qty: <?php echo htmlspecialchars($c['quantity']); ?></p>
              </div>
              <div class="cart-price">
                ₹<?php echo htmlspecialchars($subtotal); ?>
              </div>
            </div>
        <?php } ?>

        <div class="cart-total">
            Total: ₹<?php echo htmlspecialchars($total); ?>
        </div>

        <a href="checkout.php" class="hero-btn">Checkout</a>

    <?php } ?>

  </div>
</div>

<?php include '../footer.php'; ?>
