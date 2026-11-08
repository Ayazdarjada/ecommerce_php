<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/csrf.php'; // starts session

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

/* Cancel a pending order (restores stock) */
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['action'] ?? '') === 'cancel'
    && verifyToken($_POST['csrf_token'] ?? null)) {

    $order_id = (int)($_POST['order_id'] ?? 0);
    mysqli_begin_transaction($conn);
    try {
        // Only works while the order is still Pending and belongs to this user
        $stmt_upd = mysqli_prepare($conn, "UPDATE orders SET status = 'Cancelled' WHERE order_id = ? AND user_id = ? AND status = 'Pending'");
        mysqli_stmt_bind_param($stmt_upd, 'ii', $order_id, $user_id);
        mysqli_stmt_execute($stmt_upd);

        if (mysqli_stmt_affected_rows($stmt_upd) == 1) {
            $stmt_items = mysqli_prepare($conn, "SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            mysqli_stmt_bind_param($stmt_items, 'i', $order_id);
            mysqli_stmt_execute($stmt_items);
            $items = mysqli_stmt_get_result($stmt_items);

            $stmt_stock = mysqli_prepare($conn, "UPDATE products SET stock = stock + ? WHERE product_id = ?");
            while ($i = mysqli_fetch_assoc($items)) {
                mysqli_stmt_bind_param($stmt_stock, 'ii', $i['quantity'], $i['product_id']);
                mysqli_stmt_execute($stmt_stock);
            }
            mysqli_commit($conn);
        } else {
            mysqli_rollback($conn);
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log('Cancel order failed: ' . $e->getMessage());
    }
    // Post/Redirect/Get: a refresh must not re-submit the cancel
    header("Location: my_orders.php");
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$orders = mysqli_stmt_get_result($stmt);

$stmt_items = mysqli_prepare($conn, "SELECT oi.*, p.product_name FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");

include '../header.php';
?>

<section class="cart-page">
  <div class="cart-box">
    <h2>My Orders</h2>

    <?php if (mysqli_num_rows($orders) === 0) { ?>
        <p class="empty-cart-text">You haven't placed any orders yet.</p>
        <a href="products.php" class="hero-btn">Shop Now</a>
    <?php } ?>

    <?php while ($o = mysqli_fetch_assoc($orders)) {
        $badge_class = ($o['status'] == 'Pending') ? 'bg-warning' : (($o['status'] == 'Confirmed') ? 'bg-primary' : (($o['status'] == 'Shipped') ? 'bg-info' : (($o['status'] == 'Delivered') ? 'bg-success' : 'bg-danger')));
    ?>
        <div class="order-card border p-3 mb-3">
            <p><strong>Order ID #<?= (int)$o['order_id'] ?></strong> | Status: <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($o['status']) ?></span></p>
            <p>Total: ₹<?= number_format((float)$o['total_amount'], 2) ?> | Date: <?= htmlspecialchars($o['order_date']) ?></p>

            <h6>Items:</h6>
            <?php
            mysqli_stmt_bind_param($stmt_items, 'i', $o['order_id']);
            mysqli_stmt_execute($stmt_items);
            $items = mysqli_stmt_get_result($stmt_items);
            while ($i = mysqli_fetch_assoc($items)) { ?>
                <p><?= htmlspecialchars($i['product_name']) ?> - <?= (int)$i['quantity'] ?> x ₹<?= number_format((float)$i['price'], 2) ?></p>
            <?php } ?>

            <?php if ($o['status'] == 'Pending') { ?>
                <form method="POST" onsubmit="return confirm('Cancel this order?');">
                    <input type="hidden" name="csrf_token" value="<?= generateToken(); ?>">
                    <input type="hidden" name="action" value="cancel">
                    <input type="hidden" name="order_id" value="<?= (int)$o['order_id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">Cancel Order</button>
                </form>
            <?php } ?>
        </div>
    <?php } ?>
  </div>
</section>
<?php include '../footer.php'; ?>
