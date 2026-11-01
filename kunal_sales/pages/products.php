<?php
include '../header.php';
include '../config/db.php';

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;

if ($category_id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE category_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $category_id);
    mysqli_stmt_execute($stmt);
    $q = mysqli_stmt_get_result($stmt);
} else {
    $q = mysqli_query($conn, "SELECT * FROM products");
}
?>

<section class="products-section">
  <div class="product-grid">

  <?php while ($p = mysqli_fetch_assoc($q)) { ?>
    <div class="product-card">

      <img src="../uploads/<?php echo htmlspecialchars($p['image']); ?>"
     alt="<?php echo htmlspecialchars($p['product_name']); ?>"
     class="product-img">

      <h3><?php echo htmlspecialchars($p['product_name']); ?></h3>

      <div class="price">₹<?php echo htmlspecialchars($p['price']); ?></div>

      <form action="../cart/add_to_cart.php" method="POST">
        <input type="hidden" name="id" value="<?php echo (int)$p['product_id']; ?>">
        <input type="hidden" name="csrf_token" value="<?= generateToken(); ?>">
        <button type="submit" class="add-btn">Add to Cart</button>
      </form>

    </div>
  <?php } ?>

  </div>
</section>

<?php include '../footer.php'; ?>
