<?php
include '../header.php';
include '../config/db.php';

$sql = "SELECT p.* FROM products p
        INNER JOIN categories c ON c.category_id = p.category_id
        WHERE c.status = 1
          AND (LOWER(c.category_name) LIKE '%inverter%'
            OR LOWER(c.category_name) LIKE '%battery%'
            OR (LOWER(TRIM(c.category_name)) = 'ro' OR LOWER(TRIM(c.category_name)) LIKE 'ro %' OR LOWER(TRIM(c.category_name)) LIKE '% ro' OR LOWER(TRIM(c.category_name)) LIKE '%ro system%' OR LOWER(TRIM(c.category_name)) LIKE '%water purifier%' OR LOWER(TRIM(c.category_name)) = 'water' OR LOWER(TRIM(c.category_name)) LIKE '%water system%')
            OR LOWER(c.category_name) LIKE '%water%')";
$params = [];
$types = "";

if (!empty($_GET['search'])) {
    $sql .= " AND (p.product_name LIKE ? OR p.brand LIKE ?)";
    $search = "%" . $_GET['search'] . "%";
    $params[] = $search; $params[] = $search;
    $types .= "ss";
}
if (!empty($_GET['category'])) {
    $sql .= " AND p.category_id = ?";
    $params[] = (int)$_GET['category'];
    $types .= "i";
}
if (!empty($_GET['brand'])) {
    $sql .= " AND p.brand = ?";
    $params[] = $_GET['brand'];
    $types .= "s";
}
$sort = $_GET['sort'] ?? '';
if ($sort === 'high') {
    $sql .= " ORDER BY p.price DESC";
} elseif ($sort === 'low') {
    $sql .= " ORDER BY p.price ASC";
} else {
    $sql .= " ORDER BY p.product_id DESC";
}

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$q = mysqli_stmt_get_result($stmt);

$cats = mysqli_query($conn, "SELECT category_id, category_name FROM categories
    WHERE status=1 AND (LOWER(category_name) LIKE '%inverter%' OR LOWER(category_name) LIKE '%battery%'
    OR (LOWER(TRIM(category_name)) = 'ro' OR LOWER(TRIM(category_name)) LIKE 'ro %' OR LOWER(TRIM(category_name)) LIKE '% ro' OR LOWER(TRIM(category_name)) LIKE '%ro system%' OR LOWER(TRIM(category_name)) LIKE '%water purifier%' OR LOWER(TRIM(category_name)) = 'water' OR LOWER(TRIM(category_name)) LIKE '%water system%') OR LOWER(category_name) LIKE '%water%') ORDER BY category_name");
$brands = mysqli_query($conn, "SELECT DISTINCT p.brand FROM products p
    INNER JOIN categories c ON c.category_id=p.category_id
    WHERE c.status=1 AND p.brand IS NOT NULL AND p.brand != ''
    AND (LOWER(c.category_name) LIKE '%inverter%' OR LOWER(c.category_name) LIKE '%battery%'
    OR (LOWER(TRIM(c.category_name)) = 'ro' OR LOWER(TRIM(c.category_name)) LIKE 'ro %' OR LOWER(TRIM(c.category_name)) LIKE '% ro' OR LOWER(TRIM(c.category_name)) LIKE '%ro system%' OR LOWER(TRIM(c.category_name)) LIKE '%water purifier%' OR LOWER(TRIM(c.category_name)) = 'water' OR LOWER(TRIM(c.category_name)) LIKE '%water system%') OR LOWER(c.category_name) LIKE '%water%')
    ORDER BY p.brand");
?>
<main class="products-section">
<div class="container">
    <div class="section-heading">
        <div><span class="eyebrow">THE KUNAL COLLECTION</span><h2>Find your essential.</h2><p>Inverters, batteries and RO water purifiers — all in one place.</p></div>
    </div>
    <form method="GET" class="row g-3 mb-4">
        <div class="col-lg-4 col-md-6"><label class="form-label" for="product-search">Search products</label><input id="product-search" type="text" name="search" class="form-control" placeholder="Search product or brand..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"></div>
        <div class="col-lg-3 col-md-6"><label class="form-label" for="category-filter">Category</label><select id="category-filter" name="category" class="form-select"><option value="">All power &amp; water products</option>
            <?php while($c = mysqli_fetch_assoc($cats)): ?><option value="<?= (int)$c['category_id'] ?>" <?= (($_GET['category'] ?? '') == $c['category_id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['category_name']) ?></option><?php endwhile; ?>
        </select></div>
        <div class="col-lg-2 col-md-6"><label class="form-label" for="brand-filter">Brand</label><select id="brand-filter" name="brand" class="form-select"><option value="">All brands</option>
            <?php while($b = mysqli_fetch_assoc($brands)): ?><option value="<?= htmlspecialchars($b['brand']) ?>" <?= (($_GET['brand'] ?? '') === $b['brand']) ? 'selected' : '' ?>><?= htmlspecialchars($b['brand']) ?></option><?php endwhile; ?>
        </select></div>
        <div class="col-lg-2 col-md-4"><label class="form-label" for="sort-filter">Sort by</label><select id="sort-filter" name="sort" class="form-select"><option value="">Latest</option><option value="low" <?= (($_GET['sort'] ?? '') === 'low') ? 'selected' : '' ?>>Price: Low to high</option><option value="high" <?= (($_GET['sort'] ?? '') === 'high') ? 'selected' : '' ?>>Price: High to low</option></select></div>
        <div class="col-lg-1 col-md-2 d-flex align-items-end"><button type="submit" class="btn btn-primary w-100">Go</button></div>
    </form>
    <p class="eyebrow mb-2"><?= mysqli_num_rows($q) ?> PRODUCTS FOUND</p>
    <div class="product-grid">
    <?php if (mysqli_num_rows($q) > 0): ?>
        <?php while ($p = mysqli_fetch_assoc($q)): ?>
            <article class="product-card">
                <a href="product.php?id=<?= (int)$p['product_id'] ?>">
                    <img src="../uploads/<?= htmlspecialchars($p['image'] ?? '') ?>" class="product-img" alt="<?= htmlspecialchars($p['product_name']) ?>" loading="lazy">
                    <h3><?= htmlspecialchars($p['product_name']) ?></h3>
                </a>
                <?php if (!empty($p['brand'])): ?><p><?= htmlspecialchars($p['brand']) ?></p><?php endif; ?>
                <p class="price">₹<?= number_format((float)$p['price'], 2) ?></p>
                <p><?= (int)$p['stock'] > 0 ? '● In stock' : 'Out of stock' ?></p>
                <?php if ((int)$p['stock'] > 0): ?>
                    <form action="../cart/add_to_cart.php" method="POST">
                        <input type="hidden" name="id" value="<?= (int)$p['product_id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= generateToken(); ?>">
                        <button type="submit" class="add-btn">Add to cart <span aria-hidden="true">↗</span></button>
                    </form>
                <?php else: ?><button class="btn btn-secondary w-100" disabled>Out of stock</button><?php endif; ?>
            </article>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="category-tile"><h3>No matching products</h3><p style="color:var(--muted)">Try another search or category filter.</p><a class="text-link" href="products.php">Clear filters ↗</a></div>
    <?php endif; ?>
    </div>
</div>
</main>
<?php include '../footer.php'; ?>