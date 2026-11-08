<?php
include '../header.php';
include '../config/db.php';

// Lookup category_id by name
$stmt = mysqli_prepare($conn, "SELECT category_id FROM categories WHERE category_name = ?");
$cat_name = 'Inverter';
mysqli_stmt_bind_param($stmt, 's', $cat_name);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
$cat_id = $row ? $row['category_id'] : 0;

$stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE category_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $cat_id);
mysqli_stmt_execute($stmt);
$q = mysqli_stmt_get_result($stmt);
?>

<div class="product-grid">
<?php while ($p = mysqli_fetch_assoc($q)) { if ($p['stock'] <= 0) continue; ?>
  <div class="product-card">

    <img src="<?php echo htmlspecialchars(productImage($p['image'], $basePath)); ?>"
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
