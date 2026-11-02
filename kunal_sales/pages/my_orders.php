<?php
include '../config/db.php';
include '../header.php'; // Includes session and CSRF

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = mysqli_prepare($conn, "
    SELECT * FROM orders
    WHERE user_id = ?
    ORDER BY order_date DESC
");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$orders = mysqli_stmt_get_result($stmt);
?>

<section class="cart-page">
  <div class="cart-box">

    <div class="orders-header">
      <h2>My Orders</h2>
      <p>Track your past purchases and order details</p>
    </div>

    <?php if (mysqli_num_rows($orders) == 0) { ?>

        <p class="empty-cart-text">You haven’t placed any orders yet.</p>
        <a href="products.php" class="hero-btn">Start Shopping</a>

    <?php } else { ?>

        <?php 
        $count = 1; 
        while ($o = mysqli_fetch_assoc($orders)) { 
        ?>

            <div class="order-card">

                <div class="order-top">
                    <div>
                        <strong>Order <?php echo $count; ?></strong><br>
                        <span>Order ID #<?php echo (int)$o['order_id']; ?></span><br>
                        <span><?php echo date("d M Y", strtotime($o['order_date'])); ?></span>
                    </div>

                    <div class="order-amount">
                        ₹<?php echo htmlspecialchars($o['total_amount']); ?>
                    </div>
                </div>

                <?php
                $stmt_items = mysqli_prepare($conn, "
                    SELECT order_items.*, products.product_name
                    FROM order_items
                    JOIN products ON order_items.product_id = products.product_id
                    WHERE order_items.order_id = ?
                ");
                mysqli_stmt_bind_param($stmt_items, 'i', $o['order_id']);
                mysqli_stmt_execute($stmt_items);
                $items = mysqli_stmt_get_result($stmt_items);
                ?>

                <div class="order-items">
                    <?php while ($i = mysqli_fetch_assoc($items)) { ?>
                        <div class="order-item-row">
                            <span><?php echo htmlspecialchars($i['product_name']); ?></span>
                            <span><?php echo (int)$i['quantity']; ?> × ₹<?php echo htmlspecialchars($i['price']); ?></span>
                        </div>
                    <?php } ?>
                </div>

            </div>

        <?php 
        $count++;
        } 
        ?>

    <?php } ?>

  </div>
</section>

<?php include '../footer.php'; ?>
