<?php
session_start();
include '../config/db.php';
include '../config/csrf.php';
include '../header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel' && verifyToken($_POST['csrf_token'])) {
    $order_id = (int)$_POST['order_id'];
    mysqli_begin_transaction($conn);
    try {
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
        
        $stmt_upd = mysqli_prepare($conn, "UPDATE orders SET status = 'Cancelled' WHERE order_id = ? AND status = 'Pending'");
        mysqli_stmt_bind_param($stmt_upd, 'i', $order_id);
        mysqli_stmt_execute($stmt_upd);
        mysqli_commit($conn);
    } catch (Exception $e) { mysqli_rollback($conn); }
}

$user_id = $_SESSION['user_id'];
$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$orders = mysqli_stmt_get_result($stmt);
?>

<section class="cart-page">
  <div class="cart-box">
    <h2>My Orders</h2>
    <?php while ($o = mysqli_fetch_assoc($orders)) { 
        $badge_class = ($o['status'] == 'Pending') ? 'bg-warning' : (($o['status'] == 'Delivered') ? 'bg-success' : 'bg-info');
    ?>
        <div class="order-card border p-3 mb-3">
            <p><strong>Order ID #<?= (int)$o['order_id'] ?></strong> | Status: <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($o['status']) ?></span></p>
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
