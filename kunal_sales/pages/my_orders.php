<?php
session_start();
include '../config/db.php';
include '../header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$orders = mysqli_query($conn, "
    SELECT * FROM orders
    WHERE user_id = '$user_id'
    ORDER BY order_date DESC
");
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
        $count = 1; // ✅ ORDER COUNTER START
        while ($o = mysqli_fetch_assoc($orders)) { 
        ?>

            <div class="order-card">

                <div class="order-top">
                    <div>
                        <!-- ✅ ORDER NUMBER ADDED -->
                        <strong>Order <?php echo $count; ?></strong><br>
                        <span>Order ID #<?php echo $o['order_id']; ?></span><br>
                        <span><?php echo date("d M Y", strtotime($o['order_date'])); ?></span>
                    </div>

                    <div class="order-amount">
                        ₹<?php echo $o['total_amount']; ?>
                    </div>
                </div>

                <?php
                $items = mysqli_query($conn, "
                    SELECT order_items.*, products.product_name
                    FROM order_items
                    JOIN products ON order_items.product_id = products.product_id
                    WHERE order_items.order_id = '{$o['order_id']}'
                ");
                ?>

                <div class="order-items">
                    <?php while ($i = mysqli_fetch_assoc($items)) { ?>
                        <div class="order-item-row">
                            <span><?php echo htmlspecialchars($i['product_name']); ?></span>
                            <span><?php echo $i['quantity']; ?> × ₹<?php echo $i['price']; ?></span>
                        </div>
                    <?php } ?>
                </div>

            </div>

        <?php 
        $count++; // ✅ INCREMENT ORDER NUMBER
        } 
        ?>

    <?php } ?>

  </div>
</section>

<?php include '../footer.php'; ?>
