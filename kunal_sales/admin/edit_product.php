<?php
include 'admin_header.php';
include '../config/db.php';
include '../config/csrf.php';

$id = (int)($_GET['id'] ?? 0);
if (isset($_POST['update_product'])) {
    if (!isset($_POST['csrf_token']) || !verifyToken($_POST['csrf_token'])) die("CSRF failure.");

    $name = $_POST['product_name'];
    $cat_id = (int)$_POST['category_id'];
    $price = (float)$_POST['price'];
    $brand = $_POST['brand'];
    $warranty = (int)$_POST['warranty_months'];
    $stock = (int)$_POST['stock'];
    $specs = $_POST['specs'];
    $desc = $_POST['description'];

    $image_new = $_POST['old_image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $image_new = uniqid() . "." . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/" . $image_new);
            if (!empty($_POST['old_image']) && file_exists("../uploads/".$_POST['old_image'])) unlink("../uploads/".$_POST['old_image']);
        }
    }

    $stmt = mysqli_prepare($conn, "UPDATE products SET product_name=?, category_id=?, price=?, brand=?, warranty_months=?, stock=?, specs=?, description=?, image=? WHERE product_id=?");
    mysqli_stmt_bind_param($stmt, 'sidsiisssi', $name, $cat_id, $price, $brand, $warranty, $stock, $specs, $desc, $image_new, $id);
    mysqli_stmt_execute($stmt);
    header("Location: manage_products.php"); exit;
}

$stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE product_id=?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$p = mysqli_stmt_get_result($stmt)->fetch_assoc();
$cat_q = mysqli_query($conn, "SELECT category_id, category_name FROM categories");
?>
<h2>Edit Product</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= generateToken(); ?>">
    <input type="hidden" name="old_image" value="<?= htmlspecialchars($p['image']) ?>">
    <input type="text" name="product_name" value="<?= htmlspecialchars($p['product_name']) ?>" class="form-control mb-2" required>
    <select name="category_id" class="form-select mb-2">
        <?php while ($cat = mysqli_fetch_assoc($cat_q)) echo "<option value='{$cat['category_id']}' ".($p['category_id']==$cat['category_id']?'selected':'').">".htmlspecialchars($cat['category_name'])."</option>"; ?>
    </select>
    <input type="number" step="0.01" name="price" value="<?= (float)$p['price'] ?>" class="form-control mb-2" required>
    <input type="text" name="brand" value="<?= htmlspecialchars($p['brand']) ?>" class="form-control mb-2">
    <input type="number" name="warranty_months" value="<?= (int)$p['warranty_months'] ?>" class="form-control mb-2">
    <input type="number" name="stock" value="<?= (int)$p['stock'] ?>" class="form-control mb-2" required>
    <textarea name="specs" class="form-control mb-2"><?= htmlspecialchars($p['specs']) ?></textarea>
    <textarea name="description" class="form-control mb-2"><?= htmlspecialchars($p['description']) ?></textarea>
    <input type="file" name="image" class="form-control mb-2">
    <button type="submit" name="update_product" class="btn btn-primary">Update</button>
</form>
<?php include 'admin_footer.php'; ?>
