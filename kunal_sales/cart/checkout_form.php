<?php
session_start();
include '../header.php';
?>

<div class="container mt-5">
    <h2>Checkout</h2>
    <form action="checkout.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= generateToken(); ?>">
        <input type="text" name="name" class="form-control mb-2" placeholder="Full Name" required>
        <input type="text" name="phone" class="form-control mb-2" placeholder="Phone" required>
        <textarea name="address" class="form-control mb-2" placeholder="Address" required></textarea>
        <input type="text" name="city" class="form-control mb-2" placeholder="City" required>
        <input type="text" name="pincode" class="form-control mb-2" placeholder="Pincode" required>
        <select name="payment_method" class="form-select mb-2">
            <option value="COD">Cash on Delivery</option>
        </select>
        <button type="submit" class="btn btn-primary">Place Order</button>
    </form>
</div>
<?php include '../footer.php'; ?>
