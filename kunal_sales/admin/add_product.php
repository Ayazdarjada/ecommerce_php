<?php
include 'admin_header.php';
include '../config/db.php';
include '../config/csrf.php';

/* FETCH ACTIVE CATEGORIES */
$cat_q = mysqli_query($conn, "SELECT category_id, category_name FROM categories WHERE status = 1
    AND (LOWER(category_name) LIKE '%inverter%' OR LOWER(category_name) LIKE '%battery%'
    OR LOWER(category_name) LIKE '%ro%' OR LOWER(category_name) LIKE '%water%')
    ORDER BY category_name ASC");

if (isset($_POST['add_product'])) {
    if (!isset($_POST['csrf_token']) || !verifyToken($_POST['csrf_token'])) die("CSRF failure.");

    $name        = $_POST['product_name'];
    $category_id = (int) $_POST['category_id'];
    $price       = (float) $_POST['price'];
    $brand       = $_POST['brand'];
    $warranty    = (int) $_POST['warranty_months'];
    $stock       = (int) $_POST['stock'];
    $specs       = $_POST['specs'];
    $desc        = $_POST['description'];

    // IMAGE UPLOAD
    $upload_dir = "../uploads/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    
    $image_new = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp']) && $_FILES['image']['size'] <= 2 * 1024 * 1024) {
            $image_new = uniqid() . "." . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_new);
        }
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO products (product_name, category_id, price, brand, warranty_months, stock, specs, description, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sidsiisss', $name, $category_id, $price, $brand, $warranty, $stock, $specs, $desc, $image_new);
    
    if (mysqli_stmt_execute($stmt)) echo "<div class='alert alert-success'>Added!</div>";
    else echo "<div class='alert alert-danger'>Failed!</div>";
}
?>
<h2 class="mb-4">Add Product</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= generateToken(); ?>">
    <input type="text" name="product_name" class="form-control mb-2" placeholder="Product Name" required>
    <select name="category_id" class="form-select mb-2" required>
        <?php while ($cat = mysqli_fetch_assoc($cat_q)) echo "<option value='{$cat['category_id']}'>".htmlspecialchars($cat['category_name'])."</option>"; ?>
    </select>
    <input type="number" step="0.01" name="price" class="form-control mb-2" placeholder="Price" required>
    <input type="text" name="brand" class="form-control mb-2" placeholder="Brand">
    <input type="number" name="warranty_months" class="form-control mb-2" placeholder="Warranty (months)">
    <input type="number" name="stock" class="form-control mb-2" placeholder="Stock" required>
    <textarea name="specs" class="form-control mb-2" placeholder="Specs"></textarea>
    <textarea name="description" class="form-control mb-2" placeholder="Description"></textarea>
    <input type="file" name="image" class="form-control mb-2">
    <button type="submit" name="add_product" class="btn btn-primary">Add</button>
</form>
<?php include 'admin_footer.php'; ?>
