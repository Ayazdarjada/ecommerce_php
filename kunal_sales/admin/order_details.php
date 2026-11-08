<?php
include 'admin_header.php';
include '../config/db.php';
include '../config/csrf.php';

$order_id = (int)($_GET['id'] ?? 0);
$allowed_statuses = ['Pending', 'Confirmed', 'Shipped', 'Delivered', 'Cancelled'];
$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && verifyToken($_POST['csrf_token'])) {
    $new_status = $_POST['status'] ?? '';

    $stmt_curr = mysqli_prepare($conn, "SELECT status FROM orders WHERE order_id = ?");
    mysqli_stmt_bind_param($stmt_curr, 'i', $order_id);
    mysqli_stmt_execute($stmt_curr);
    $cur = mysqli_stmt_get_result($stmt_curr)->fetch_assoc();

    if (!$cur) {
        $flash = ['danger', 'Order not found.'];
    } elseif (!in_array($new_status, $allowed_statuses, true)) {
        $flash = ['danger', 'Invalid status.'];
    } elseif ($cur['status'] === 'Cancelled') {
        // Cancelled is final: re-opening it would require re-deducting stock
        // (and toggling back and forth used to inflate stock).
        $flash = ['warning', 'A cancelled order cannot be changed.'];
    } elseif ($new_status === 'Cancelled' && !in_array($cur['status'], ['Pending', 'Confirmed'], true)) {
        $flash = ['warning', 'Only Pending or Confirmed orders can be cancelled.'];
    } elseif ($new_status !== $cur['status']) {
        mysqli_begin_transaction($conn);
        try {
            // Compare-and-set on the old status so two admins can't both restore stock
            $stmt_upd = mysqli_prepare($conn, "UPDATE orders SET status = ? WHERE order_id = ? AND status = ?");
            mysqli_stmt_bind_param($stmt_upd, 'sis', $new_status, $order_id, $cur['status']);
            mysqli_stmt_execute($stmt_upd);

            if (mysqli_stmt_affected_rows($stmt_upd) === 1) {
                if ($new_status === 'Cancelled') {
                    $stmt_items = mysqli_prepare($conn, "SELECT product_id, quantity FROM order_items WHERE order_id = ?");
                    mysqli_stmt_bind_param($stmt_items, 'i', $order_id);
                    mysqli_stmt_execute($stmt_items);
                    $items = mysqli_stmt_get_result($stmt_items);

                    $stmt_stock = mysqli_prepare($conn, "UPDATE products SET stock = stock + ? WHERE product_id = ?");
                    while ($i = mysqli_fetch_assoc($items)) {
                        mysqli_stmt_bind_param($stmt_stock, 'ii', $i['quantity'], $i['product_id']);
                        mysqli_stmt_execute($stmt_stock);
                    }
                }
                mysqli_commit($conn);
                $flash = ['success', 'Status updated to ' . $new_status . '.'];
            } else {
                mysqli_rollback($conn);
                $flash = ['warning', 'Order was changed by someone else. Reloaded.'];
            }
        } catch (Exception $e) {
            mysqli_rollback($conn);
            error_log('Order status update failed: ' . $e->getMessage());
            $flash = ['danger', 'Could not update the order.'];
        }
    }
}

$stmt = mysqli_prepare($conn, "SELECT orders.*, users.name FROM orders JOIN users ON orders.user_id = users.user_id WHERE orders.order_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $order_id);
mysqli_stmt_execute($stmt);
$o = mysqli_stmt_get_result($stmt)->fetch_assoc();

if (!$o) {
    echo "<div class='alert alert-warning'>Order not found.</div><a href='view_orders.php' class='btn btn-primary'>Back to orders</a>";
    include 'admin_footer.php';
    exit;
}
?>
<div class="admin-content">
    <h2>Order #<?= (int)$o['order_id'] ?></h2>
    <?php if ($flash) echo "<div class='alert alert-{$flash[0]}'>".htmlspecialchars($flash[1])."</div>"; ?>
    <p>Customer: <?= htmlspecialchars($o['name']) ?> | Phone: <?= htmlspecialchars($o['phone']) ?></p>
    <p>Address: <?= htmlspecialchars($o['address'].', '.$o['city'].'-'.$o['pincode']) ?> | Payment: <?= htmlspecialchars($o['payment_method']) ?></p>
    
    <form method="POST" class="mb-3">
        <input type="hidden" name="csrf_token" value="<?= generateToken(); ?>">
        <select name="status" class="form-select d-inline-block w-auto"<?= $o['status']==='Cancelled' ? ' disabled' : '' ?>>
            <?php foreach(['Pending','Confirmed','Shipped','Delivered','Cancelled'] as $s) echo "<option ".($o['status']==$s?'selected':'').">$s</option>"; ?>
        </select>
        <button type="submit" class="btn btn-primary"<?= $o['status']==='Cancelled' ? ' disabled' : '' ?>>Update Status</button>
    </form>
    
    <table class="table">
        <?php
        $stmt_items = mysqli_prepare($conn, "SELECT oi.*, p.product_name FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
        mysqli_stmt_bind_param($stmt_items, 'i', $order_id);
        mysqli_stmt_execute($stmt_items);
        $items = mysqli_stmt_get_result($stmt_items);
        while ($i = mysqli_fetch_assoc($items)) echo "<tr><td>".htmlspecialchars($i['product_name'])."</td><td>".(int)$i['quantity']."</td><td>₹".number_format((float)$i['price'], 2)."</td></tr>";
        ?>
    </table>
    <h3>Total: ₹<?= number_format((float)$o['total_amount'], 2) ?></h3>
</div>
<?php include 'admin_footer.php'; ?>
