<?php
include 'admin_header.php';
include '../config/db.php';
include '../config/csrf.php';

$q = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_id DESC");
?>

<h2 class="mb-4">Manage Categories</h2>

<div class="admin-table-wrapper">
  <div class="card">
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Category Name</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>

      <tbody>
        <?php if (mysqli_num_rows($q) > 0) { ?>
          <?php while ($c = mysqli_fetch_assoc($q)) { ?>
            <tr>
              <td><?= (int)$c['category_id'] ?></td>
              <td><?= htmlspecialchars($c['category_name']) ?></td>
              <td>
                <?= $c['status'] 
                    ? '<span class="badge bg-success">Active</span>' 
                    : '<span class="badge bg-secondary">Deactive</span>' ?>
              </td>
              <td>
                <!-- Toggle Form -->
                <form action="toggle_category.php" method="POST" style="display:inline;">
                    <input type="hidden" name="id" value="<?= (int)$c['category_id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= generateToken() ?>">
                    <button type="submit" class="btn btn-sm btn-warning">Toggle</button>
                </form>

                <!-- Delete Button (breakRow) -->
                <a href="#"
                   class="btn btn-sm btn-danger btn-delete"
                   data-id="<?= (int)$c['category_id'] ?>"
                   data-url="delete_category.php"
                   data-csrf="<?= generateToken() ?>"
                   onclick="return breakRow(this)">
                  Delete
                </a>
              </td>
            </tr>
          <?php } ?>
        <?php } else { ?>
          <tr>
            <td colspan="4" class="text-muted text-center">
              No categories found
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'admin_footer.php'; ?>
