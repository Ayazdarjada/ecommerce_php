<?php
session_start();
include '../config/db.php';
include '../config/csrf.php';
include '../header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel' && verifyToken($_POST['csrf_token'])) {
    $order_id = (int)$_POST['order_id'];
    mysqli_begin_transaction($conn);
    try {
        // Try to update status only if currently Pending
        $stmt_upd = mysqli_prepare($conn, "UPDATE orders SET status = 'Cancelled' WHERE order_id = ? AND user_id = ? AND status = 'Pending'");
        mysqli_stmt_bind_param($stmt_upd, 'ii', $order_id, $user_id);
        mysqli_stmt_execute($stmt_upd);
        
        if (mysqli_stmt_affected_rows($stmt_upd) == 1) {
            // Restore stock
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
    } catch (Exception $e) { mysqli_rollback($conn); }
}

$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$orders = mysqli_stmt_get_result($stmt);
?>

<section class="cart-page">
  <div class="cart-box">
    <h2>My Orders</h2>
    <?php while ($o = mysqli_fetch_assoc($orders)) { 
        $badge_class = ($o['status'] == 'Pending') ? 'bg-warning' : (($o['status'] == 'Confirmed') ? 'bg-primary' : (($o['status'] == 'Shipped') ? 'bg-info' : (($o['status'] == 'Delivered') ? 'bg-success' : 'bg-danger')));
    ?>
        <div class="order-card border p-3 mb-3">
            <p><strong>Order ID #<?= (int)$o['order_id'] ?></strong> | Status: <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($o['status']) ?></span></p>
            <p>Total: ₹<?= htmlspecialchars($o['total_amount']) ?> | Date: <?= htmlspecialchars($o['order_date']) ?></p>
            
            <h6>Items:</h6>
            <?php
            $stmt_items = mysqli_prepare($conn, "SELECT oi.*, p.product_name FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
            mysqli_stmt_bind_param($stmt_items, 'i', $o['order_id']);
            mysqli_stmt_execute($stmt_items);
            $items = mysqli_stmt_get_result($stmt_items);
            while ($i = mysqli_fetch_assoc($items)) {
                echo "<p>{$i['product_name']} - {$i['quantity']} x ₹{$i['price']}</p>";
            }
            ?>
            
            <?php if ($o['status'] == 'Pending') { ?>
                <form method="POST">
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
