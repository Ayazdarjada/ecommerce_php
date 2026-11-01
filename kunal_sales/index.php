<?php 
include 'header.php';
include 'config/db.php';

/* Fetch active categories */
$categories = mysqli_query($conn, "
    SELECT * FROM categories 
    WHERE status = 1 
    ORDER BY category_id ASC
");
?>

<section class="hero-advanced">
  <div class="hero-overlay"></div>

  <div class="hero-content">
    <h1>
      Power & Water<br>
      <span>Solutions You Can Trust</span>
    </h1>

    <p>
      Inverter • Battery • RO • Stabilizer
    </p>

    <div class="hero-actions">
      <a href="pages/products.php" class="hero-main-btn">
        Explore Products
      </a>
      <a href="#services" class="hero-secondary-btn">
        Our Services
      </a>
    </div>
  </div>

  <div class="hero-glow"></div>
</section>

<!-- ================= CATEGORIES ================= -->
<section class="categories" id="services">

<?php 
$index = 1;
if (mysqli_num_rows($categories) > 0) {
    while ($cat = mysqli_fetch_assoc($categories)) { 
?>
    <a href="pages/products.php?category=<?= (int)$cat['category_id']; ?>" class="card" style="text-decoration: none; color: inherit;">
        <span class="cat-index">
            <?= str_pad($index, 2, '0', STR_PAD_LEFT); ?>
        </span>
        <h3><?= htmlspecialchars($cat['category_name']); ?></h3>
        <p>Explore premium <?= htmlspecialchars($cat['category_name']); ?> solutions</p>
    </a>
<?php 
        $index++;
    }
} else { 
?>
    <div class="card">
        <h3>No Categories Found</h3>
        <p>Please add categories from admin panel</p>
    </div>
<?php } ?>

</section>

<?php include 'footer.php'; ?>
