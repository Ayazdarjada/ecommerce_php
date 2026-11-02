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
?>

<div class="container mt-4">
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3"><input type="text" name="search" class="form-control" placeholder="Search products..."></div>
        <div class="col-md-2"><input type="text" name="brand" class="form-control" placeholder="Brand"></div>
        <div class="col-md-2">
            <select name="sort" class="form-select">
                <option value="">Sort by Price</option>
                <option value="low">Low to High</option>
                <option value="high">High to Low</option>
            </select>
        </div>
        <div class="col-md-2"><button type="submit" class="btn btn-primary">Filter</button></div>
    </form>

    <div class="product-grid">
    <?php if (mysqli_num_rows($q) > 0) { ?>
        <?php while ($p = mysqli_fetch_assoc($q)) { ?>
            <div class="product-card">
                <a href="product.php?id=<?= (int)$p['product_id'] ?>">
                    <img src="../uploads/<?= htmlspecialchars($p['image']) ?>" class="product-img" style="width:100%; height:180px; object-fit:cover;">
                    <h3><?= htmlspecialchars($p['product_name']) ?></h3>
                </a>
                <p>Price: ₹<?= htmlspecialchars(number_format($p['price'], 2)) ?></p>
            </div>
        <?php } ?>
    <?php } else { ?>
        <p class="text-center">No products found.</p>
    <?php } ?>
    </div>
</div>
<?php include '../footer.php'; ?>
