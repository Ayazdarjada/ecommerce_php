<?php
include 'admin_header.php';
include 'admin_auth.php';
include '../config/db.php';
include '../config/csrf.php';

/* FETCH ACTIVE CATEGORIES */
$cat_q = mysqli_query($conn, "
    SELECT category_id, category_name FROM categories 
    WHERE status = 1 
    ORDER BY category_name ASC
");

if (isset($_POST['add_product'])) {
    if (!isset($_POST['csrf_token']) || !verifyToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    // 1️⃣ Get form data
    $name        = $_POST['product_name'];
    $category_id = (int) $_POST['category_id'];
    $price       = (float) $_POST['price'];
    $description = $_POST['description'];

    // 2️⃣ IMAGE UPLOAD
    $upload_dir = "../uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $image = $_FILES['image'];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
    $extension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
    
    if (!in_array($extension, $allowed_extensions)) {
        die("Invalid file type.");
    }
    
    if ($image['size'] > 2 * 1024 * 1024) {
        die("File too large (max 2MB).");
    }

    $check = getimagesize($image['tmp_name']);
    if ($check === false) {
        die("File is not an image.");
    }

    // Rename image to avoid duplicates
    $image_new = uniqid() . "_" . bin2hex(random_bytes(5)) . "." . $extension;

    if (move_uploaded_file($image['tmp_name'], $upload_dir . $image_new)) {
        // 3️⃣ Insert into database
        $stmt = mysqli_prepare($conn, "
            INSERT INTO products 
            (product_name, category_id, price, image, description)
            VALUES (?, ?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param($stmt, 'sidss', $name, $category_id, $price, $image_new, $description);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<div class='alert alert-success'>Product Added Successfully</div>";
        } else {
            echo "<div class='alert alert-danger'>Failed to Add Product: " . mysqli_error($conn) . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Failed to upload image.</div>";
    }
}
?>

<h2 class="mb-4">Add Product</h2>

<div class="admin-form">
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= generateToken(); ?>">

    <!-- PRODUCT NAME -->
    <div class="mb-3">
        <label class="form-label">Product Name</label>
        <input type="text" name="product_name" class="form-control" required>
    </div>

    <!-- CATEGORY DROPDOWN -->
    <div class="mb-3">
        <label class="form-label">Category</label>
        <select name="category_id" class="form-select" required>
            <option value="">-- Select Category --</option>
            <?php while ($cat = mysqli_fetch_assoc($cat_q)) { ?>
                <option value="<?= (int)$cat['category_id']; ?>">
                    <?= htmlspecialchars($cat['category_name']); ?>
                </option>
            <?php } ?>
        </select>
    </div>

    <!-- PRICE -->
    <div class="mb-3">
        <label class="form-label">Price (₹)</label>
        <input type="number" step="0.01" name="price" class="form-control" required>
    </div>

    <!-- DESCRIPTION -->
    <div class="mb-3">
        <label class="form-label">Product Description</label>
        <textarea 
            name="description" 
            class="form-control" 
            rows="4" 
            required
        ></textarea>
    </div>

    <!-- IMAGE -->
    <div class="mb-3">
        <label class="form-label">Product Image (JPG/PNG/WEBP, Max 2MB)</label>
        <input type="file" name="image" class="form-control" required>
    </div>

    <!-- SUBMIT -->
    <button type="submit" name="add_product" class="btn btn-primary">
        Add Product
    </button>
</form>
</div>

<?php include 'admin_footer.php'; ?>
