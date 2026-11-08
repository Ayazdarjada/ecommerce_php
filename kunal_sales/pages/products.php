<?php
include '../header.php';
include '../config/db.php';

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];
$types = "";

if (!empty($_GET['search'])) {
    $sql .= " AND (product_name LIKE ? OR brand LIKE ?)";
    $search = "%" . $_GET['search'] . "%";
    $params[] = $search; $params[] = $search;
    $types .= "ss";
}
if (!empty($_GET['category'])) {
    $sql .= " AND category_id = ?";
    $params[] = (int)$_GET['category'];
    $types .= "i";
}
if (!empty($_GET['brand'])) {
    $sql .= " AND brand = ?";
    $params[] = $_GET['brand'];
    $types .= "s";
}
if (!empty($_GET['sort'])) {
    $sql .= ($_GET['sort'] === 'high') ? " ORDER BY price DESC" : " ORDER BY price ASC";
}

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$q = mysqli_stmt_get_result($stmt);

$cats = mysqli_query($conn, "SELECT * FROM categories WHERE status=1");
$brands = mysqli_query($conn, "SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL AND brand != ''");
?>

<div class="container mt-4">
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-2"><input type="text" name="search" class="form-control" placeholder="Search..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"></div>
        <div class="col-md-2">
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                <?php while($c = mysqli_fetch_assoc($cats)) echo "<option value='{$c['category_id']}' ".((($_GET['category']??0)==$c['category_id'])?'selected':'').">".htmlspecialchars($c['category_name'])."</option>"; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="brand" class="form-select">
                <option value="">All Brands</option>
                <?php while($b = mysqli_fetch_assoc($brands)) echo "<option value='".htmlspecialchars($b['brand'])."' ".(isset($_GET['brand']) && $_GET['brand']==$b['brand']?'selected':'').">".htmlspecialchars($b['brand'])."</option>"; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="sort" class="form-select">
                <option value="">Sort</option>
                <option value="low" <?= (($_GET['sort']??'')=='low'?'selected':'') ?>>Low to High</option>
                <option value="high" <?= (($_GET['sort']??'')=='high'?'selected':'') ?>>High to Low</option>
            </select>
        </div>
        <div class="col-md-2"><button type="submit" class="btn btn-primary">Filter</button></div>
    </form>

    <div class="product-grid">
    <?php if (mysqli_num_rows($q) > 0) { ?>
        <?php while ($p = mysqli_fetch_assoc($q)) { ?>
            <div class="product-card">
                <a href="product.php?id=<?= (int)$p['product_id'] ?>">
                    <img src="<?= htmlspecialchars(productImage($p['image'], $basePath)) ?>" alt="<?= htmlspecialchars($p['product_name']) ?>" class="product-img" style="width:100%; height:180px; object-fit:cover;">
                    <h3><?= htmlspecialchars($p['product_name']) ?></h3>
                </a>
                <p>Price: ₹<?= htmlspecialchars(number_format($p['price'], 2)) ?></p>
                <p>Status: <?= $p['stock'] > 0 ? 'In stock' : 'Out of stock' ?></p>
                <?php if ($p['stock'] > 0) { ?>
                    <form action="../cart/add_to_cart.php" method="POST">
                        <input type="hidden" name="id" value="<?= (int)$p['product_id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= generateToken(); ?>">
                        <button type="submit" class="add-btn">Add to Cart</button>
                    </form>
                <?php } else { ?>
                    <button class="btn btn-secondary" disabled>Out of stock</button>
                <?php } ?>
            </div>
        <?php } ?>
    <?php } else { ?>
        <p class="text-center">No products found.</p>
    <?php } ?>
    </div>
</div>
<?php include '../footer.php'; ?>
