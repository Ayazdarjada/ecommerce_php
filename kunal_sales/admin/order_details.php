<?php
include 'admin_header.php';
include '../config/db.php';
include '../config/csrf.php';

$order_id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && verifyToken($_POST['csrf_token'])) {
    $new_status = $_POST['status'];
    
    // Get current status
    $stmt_curr = mysqli_prepare($conn, "SELECT status FROM orders WHERE order_id = ?");
    mysqli_stmt_bind_param($stmt_curr, 'i', $order_id);
    mysqli_stmt_execute($stmt_curr);
    $old_status = mysqli_stmt_get_result($stmt_curr)->fetch_assoc()['status'];

    mysqli_begin_transaction($conn);
    try {
        $stmt_upd = mysqli_prepare($conn, "UPDATE orders SET status = ? WHERE order_id = ?");
        mysqli_stmt_bind_param($stmt_upd, 'si', $new_status, $order_id);
        mysqli_stmt_execute($stmt_upd);

        // If moved to Cancelled from Pending/Confirmed, restore stock
        if ($new_status == 'Cancelled' && ($old_status == 'Pending' || $old_status == 'Confirmed')) {
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
    } catch (Exception $e) { mysqli_rollback($conn); }
}

$stmt = mysqli_prepare($conn, "SELECT orders.*, users.name FROM orders JOIN users ON orders.user_id = users.user_id WHERE orders.order_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $order_id);
mysqli_stmt_execute($stmt);
$o = mysqli_stmt_get_result($stmt)->fetch_assoc();
?>
<div class="admin-content">
    <h2>Order #<?= (int)$o['order_id'] ?></h2>
    <p>Customer: <?= htmlspecialchars($o['name']) ?> | Phone: <?= htmlspecialchars($o['phone']) ?></p>
    <p>Address: <?= htmlspecialchars($o['address'].', '.$o['city'].'-'.$o['pincode']) ?> | Payment: <?= htmlspecialchars($o['payment_method']) ?></p>
    
    <form method="POST" class="mb-3">
        <input type="hidden" name="csrf_token" value="<?= generateToken(); ?>">
        <select name="status" class="form-select d-inline-block w-auto">
            <?php foreach(['Pending','Confirmed','Shipped','Delivered','Cancelled'] as $s) echo "<option ".($o['status']==$s?'selected':'').">$s</option>"; ?>
        </select>
        <button type="submit" class="btn btn-primary">Update Status</button>
    </form>
    
    <table class="table">
        <?php
        $stmt_items = mysqli_prepare($conn, "SELECT oi.*, p.product_name FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
        mysqli_stmt_bind_param($stmt_items, 'i', $order_id);
        mysqli_stmt_execute($stmt_items);
        $items = mysqli_stmt_get_result($stmt_items);
        while ($i = mysqli_fetch_assoc($items)) echo "<tr><td>{$i['product_name']}</td><td>{$i['quantity']}</td><td>{$i['price']}</td></tr>";
        ?>
    </table>
    <h3>Total: ₹<?= htmlspecialchars($o['total_amount']) ?></h3>
</div>
<?php include 'admin_footer.php'; ?>
