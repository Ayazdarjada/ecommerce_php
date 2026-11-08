<?php
include 'admin_auth.php';              // session + admin check, no output
include '../config/db.php';
include '../config/csrf.php';

$id = (int)($_GET['id'] ?? 0);

/* Load product first so we can 404/redirect before any HTML is printed */
$stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE product_id=?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$p = mysqli_stmt_get_result($stmt)->fetch_assoc();
if (!$p) {
    header("Location: manage_products.php");
    exit;
}

$error = null;
if (isset($_POST['update_product'])) {
    if (!isset($_POST['csrf_token']) || !verifyToken($_POST['csrf_token'])) die("CSRF failure.");

    $name     = trim($_POST['product_name'] ?? '');
    $cat_id   = (int)($_POST['category_id'] ?? 0);
    $price    = (float)($_POST['price'] ?? 0);
    $brand    = trim($_POST['brand'] ?? '');
    $warranty = (int)($_POST['warranty_months'] ?? 0);
    $stock    = max(0, (int)($_POST['stock'] ?? 0));
    $specs    = $_POST['specs'] ?? '';
    $desc     = $_POST['description'] ?? '';

    if ($name === '' || $price < 0) {
        $error = "Product name is required and price can't be negative.";
    } else {
        // Keep the DB's current image unless a valid new one is uploaded
        // (never trust the old_image value coming from the form)
        $image_new = $p['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp']) && $_FILES['image']['size'] <= 2 * 1024 * 1024) {
                $candidate = uniqid() . "." . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/" . $candidate)) {
                    $old = basename((string)$p['image']);
                    if ($old !== '' && is_file("../uploads/" . $old)) unlink("../uploads/" . $old);
                    $image_new = $candidate;
                }
            }
        }

        $stmt = mysqli_prepare($conn, "UPDATE products SET product_name=?, category_id=?, price=?, brand=?, warranty_months=?, stock=?, specs=?, description=?, image=? WHERE product_id=?");
        mysqli_stmt_bind_param($stmt, 'sidsiisssi', $name, $cat_id, $price, $brand, $warranty, $stock, $specs, $desc, $image_new, $id);
        mysqli_stmt_execute($stmt);
        header("Location: manage_products.php");
        exit;
    }
}

$cat_q = mysqli_query($conn, "SELECT category_id, category_name FROM categories ORDER BY category_name ASC");
include 'admin_header.php';             // HTML output starts only now
?>
<h2>Edit Product</h2>
<?php if ($error) echo "<div class='alert alert-danger'>".htmlspecialchars($error)."</div>"; ?>
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= generateToken(); ?>">
    <input type="text" name="product_name" value="<?= htmlspecialchars($p['product_name']) ?>" class="form-control mb-2" required>
    <select name="category_id" class="form-select mb-2">
        <?php while ($cat = mysqli_fetch_assoc($cat_q)) echo "<option value='{$cat['category_id']}' ".($p['category_id']==$cat['category_id']?'selected':'').">".htmlspecialchars($cat['category_name'])."</option>"; ?>
    </select>
    <input type="number" step="0.01" name="price" value="<?= (float)$p['price'] ?>" class="form-control mb-2" required>
    <input type="text" name="brand" value="<?= htmlspecialchars((string)$p['brand']) ?>" class="form-control mb-2">
    <input type="number" name="warranty_months" value="<?= (int)$p['warranty_months'] ?>" class="form-control mb-2">
    <input type="number" name="stock" value="<?= (int)$p['stock'] ?>" class="form-control mb-2" required>
    <textarea name="specs" class="form-control mb-2"><?= htmlspecialchars((string)$p['specs']) ?></textarea>
    <textarea name="description" class="form-control mb-2"><?= htmlspecialchars((string)$p['description']) ?></textarea>
    <input type="file" name="image" class="form-control mb-2">
    <button type="submit" name="update_product" class="btn btn-primary">Update</button>
</form>
<?php include 'admin_footer.php'; ?>
