<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/csrf.php'; // starts session

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

$stmt = mysqli_prepare($conn, "
    SELECT COUNT(*) AS items, COALESCE(SUM(cart.quantity * products.price), 0) AS total
    FROM cart JOIN products ON cart.product_id = products.product_id
    WHERE cart.user_id = ?
");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$summary = mysqli_stmt_get_result($stmt)->fetch_assoc();

if ((int)$summary['items'] === 0) {
    header("Location: view_cart.php");
    exit;
}

include '../header.php';
?>

<div class="container mt-5" style="max-width:600px;">
    <h2>Checkout</h2>
    <p class="text-muted">Order total: <strong>₹<?= number_format((float)$summary['total'], 2) ?></strong></p>

    <form action="checkout.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= generateToken(); ?>">
        <input type="text" name="name" class="form-control mb-2" placeholder="Full Name"
               value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>" required>
        <input type="tel" name="phone" class="form-control mb-2" placeholder="Phone (10 digits)"
               pattern="[0-9+\-\s]{7,15}" required>
        <textarea name="address" class="form-control mb-2" placeholder="Address" required></textarea>
        <input type="text" name="city" class="form-control mb-2" placeholder="City" required>
        <input type="text" name="pincode" class="form-control mb-2" placeholder="Pincode (6 digits)"
               pattern="[0-9]{6}" maxlength="6" required>
        <select name="payment_method" class="form-select mb-3">
            <option value="COD">Cash on Delivery</option>
        </select>
        <button type="submit" class="btn btn-primary">Place Order</button>
        <a href="view_cart.php" class="btn btn-link">Back to cart</a>
    </form>
</div>
<?php include '../footer.php'; ?>
