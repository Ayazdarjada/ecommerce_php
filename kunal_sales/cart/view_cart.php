<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/csrf.php'; // starts session

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

$stmt = mysqli_prepare($conn, "
    SELECT cart.*, products.product_name, products.price, products.stock
    FROM cart
    JOIN products ON cart.product_id = products.product_id
    WHERE cart.user_id = ?
    ORDER BY cart.cart_id ASC
");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$q = mysqli_stmt_get_result($stmt);

$total = 0;
$has_stock_problem = false;

include '../header.php';
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
            if ($c['quantity'] > $c['stock']) $has_stock_problem = true;
        ?>
            <div class="cart-item">
              <div class="cart-item-info">
                <strong><?php echo htmlspecialchars($c['product_name']); ?></strong>
                <?php if ($c['quantity'] > $c['stock']) { ?>
                    <div class="text-danger small">Only <?= (int)$c['stock'] ?> in stock &mdash; please reduce quantity.</div>
                <?php } ?>
              </div>
              <div class="cart-price">
                ₹<?= number_format($subtotal, 2) ?>
              </div>

              <form action="update_cart.php" method="POST" class="cart-actions">
                  <input type="hidden" name="csrf_token" value="<?= generateToken(); ?>">
                  <input type="hidden" name="id" value="<?= (int)$c['product_id'] ?>">
                  <button type="submit" name="action" value="decrease">-</button>
                  <span>Qty: <?= (int)$c['quantity'] ?></span>
                  <button type="submit" name="action" value="increase">+</button>
                  <button type="submit" name="action" value="remove" class="btn btn-danger btn-sm">Remove</button>
              </form>
            </div>
        <?php } ?>

        <div class="cart-total">
            Total: ₹<?= number_format($total, 2) ?>
        </div>

        <?php if ($has_stock_problem) { ?>
            <p class="text-danger">Fix the quantities above before checking out.</p>
        <?php } else { ?>
            <!-- Goes to the address form first; checkout.php needs those details -->
            <a href="checkout_form.php" class="hero-btn">Proceed to Checkout</a>
        <?php } ?>

    <?php } ?>

  </div>
</div>

<?php include '../footer.php'; ?>
