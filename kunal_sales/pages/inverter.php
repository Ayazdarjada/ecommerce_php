<?php
include '../header.php';
include '../config/db.php';

// Assuming category_id 1 = Inverter
$stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE category_id = ?");
$cat_id = 1;
mysqli_stmt_bind_param($stmt, 'i', $cat_id);
mysqli_stmt_execute($stmt);
$q = mysqli_stmt_get_result($stmt);
?>

<div class="product-grid">
<?php while ($p = mysqli_fetch_assoc($q)) { ?>
  <div class="product-card">

    <img src="../uploads/<?php echo htmlspecialchars($p['image']); ?>"
         style="width:100%; height:180px; object-fit:cover; border-radius:12px;">

    <h3 class="mt-3"><?php echo htmlspecialchars($p['product_name']); ?></h3>
    <p class="price">₹<?php echo htmlspecialchars($p['price']); ?></p>

    <form action="../cart/add_to_cart.php" method="POST">
        <input type="hidden" name="id" value="<?php echo (int)$p['product_id']; ?>">
        <input type="hidden" name="csrf_token" value="<?= generateToken(); ?>">
        <button type="submit" class="add-btn">Add to Cart</button>
    </form>
  </div>
<?php } ?>
</div>

<?php include '../footer.php'; ?>
