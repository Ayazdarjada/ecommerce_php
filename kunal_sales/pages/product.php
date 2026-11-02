<?php
include '../header.php';
include '../config/db.php';

$product_id = (int)($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE product_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $product_id);
mysqli_stmt_execute($stmt);
$p = mysqli_stmt_get_result($stmt)->fetch_assoc();

if (!$p) die("Product not found");
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <img src="../uploads/<?= htmlspecialchars($p['image']) ?>" class="img-fluid" alt="<?= htmlspecialchars($p['product_name']) ?>">
        </div>
        <div class="col-md-6">
            <h1><?= htmlspecialchars($p['product_name']) ?></h1>
            <p><strong>Brand:</strong> <?= htmlspecialchars($p['brand'] ?? 'N/A') ?></p>
            <p><strong>Price:</strong> ₹<?= htmlspecialchars(number_format($p['price'], 2)) ?></p>
            <p><strong>Warranty:</strong> <?= (int)($p['warranty_months'] ?? 0) ?> months</p>
            <p><strong>Description:</strong> <?= htmlspecialchars($p['description'] ?? '') ?></p>
            <p><strong>Specs:</strong> <?= htmlspecialchars($p['specs'] ?? '') ?></p>
            
            <?php if ($p['stock'] > 0) { ?>
                <form action="../cart/add_to_cart.php" method="POST">
                    <input type="hidden" name="id" value="<?= (int)$p['product_id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= generateToken(); ?>">
                    <button type="submit" class="hero-btn">Add to Cart</button>
                </form>
            <?php } else { ?>
                <button class="btn btn-secondary" disabled>Out of stock</button>
            <?php } ?>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
