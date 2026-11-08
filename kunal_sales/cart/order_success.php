<?php
require_once __DIR__ . '/../config/csrf.php'; // starts session

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

include '../header.php';
?>

<div class="success-page">
  <div class="success-card">

    <div class="success-icon">✓</div>

    <h1>Order Placed Successfully</h1>

    <p>
      Thank you for shopping with
      <strong>Kunal Sales & Services</strong>.
      <br>
      Your order has been placed and is being processed.
    </p>

    <div class="success-actions">
      <a href="../pages/my_orders.php" class="success-btn primary">
        View My Orders
      </a>
      <a href="../pages/products.php" class="success-btn">
        Continue Shopping
      </a>
    </div>

  </div>
</div>

<?php include '../footer.php'; ?>
