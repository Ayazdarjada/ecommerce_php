<?php
include 'admin_header.php';
include '../config/db.php';
include '../config/csrf.php';

if (isset($_POST['add_category'])) {
    if (!isset($_POST['csrf_token']) || !verifyToken($_POST['csrf_token'])) die("CSRF failure.");

    $category_name = trim($_POST['category_name'] ?? '');
    $status = (($_POST['status'] ?? '1') === '0') ? 0 : 1;

    if ($category_name === '') {
        echo "<div class='alert alert-danger'>Category Name Required</div>";
    } else {
        $chk = mysqli_prepare($conn, "SELECT category_id FROM categories WHERE category_name = ?");
        mysqli_stmt_bind_param($chk, 's', $category_name);
        mysqli_stmt_execute($chk);
        if (mysqli_stmt_get_result($chk)->num_rows > 0) {
            echo "<div class='alert alert-warning'>That category already exists</div>";
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO categories (category_name, status) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt, 'si', $category_name, $status);
            mysqli_stmt_execute($stmt);
            echo "<div class='alert alert-success'>Category Added Successfully</div>";
        }
    }
}
?>

<h2 class="mb-4">Add Category</h2>

<div class="admin-form">
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= generateToken(); ?>">

    <div class="mb-3">
        <label class="form-label">Category Name</label>
        <input type="text" name="category_name" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="1">Active</option>
            <option value="0">Deactive</option>
        </select>
    </div>

    <button type="submit" name="add_category" class="btn btn-primary">
        Add Category
    </button>

</form>
</div>

<?php include 'admin_footer.php'; ?>
