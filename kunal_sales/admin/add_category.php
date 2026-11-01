<?php
include 'admin_header.php';
include '../config/db.php';

if (isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name']);
    $status = $_POST['status'];

    if ($category_name != '') {
        mysqli_query($conn, "
            INSERT INTO categories (category_name, status)
            VALUES ('$category_name', '$status')
        ");

        echo "<div class='alert alert-success'>Category Added Successfully</div>";
    } else {
        echo "<div class='alert alert-danger'>Category Name Required</div>";
    }
}
?>

<h2 class="mb-4">Add Category</h2>

<div class="admin-form">
<form method="POST">

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
